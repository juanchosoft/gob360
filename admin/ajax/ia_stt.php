<?php
/**
 * STT — Reconocimiento de voz.
 * Recibe el audio grabado por el widget, lo transcribe con ElevenLabs Scribe,
 * envía la transcripción a Claude y devuelve texto + respuesta en un solo viaje.
 *
 * POST params (multipart/form-data):
 *   audio           file   Audio grabado (webm / mp4 / ogg / wav)
 *   conversacion_id int    0 = crear nueva conversación
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache');

set_time_limit(90);

// ── Sesión ────────────────────────────────────────────────────────────────────
if (empty($_SESSION['session_user'])) {
    http_response_code(401);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Sesión expirada.']]);
    exit;
}

// ── Permiso de voz ────────────────────────────────────────────────────────────
require_once __DIR__ . '/../classes/SessionData.php';
if (!SessionData::hasPermission('asistente_ia.voz.use')) {
    http_response_code(403);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Sin permiso para el modo de voz.']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Método no permitido.']]);
    exit;
}

// ── Validar archivo de audio ──────────────────────────────────────────────────
$fileError = $_FILES['audio']['error'] ?? UPLOAD_ERR_NO_FILE;
if (empty($_FILES['audio']) || $fileError !== UPLOAD_ERR_OK) {
    $errMsg = match ($fileError) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El audio supera el tamaño máximo permitido por el servidor.',
        UPLOAD_ERR_NO_FILE                         => 'No se recibió el archivo de audio.',
        default                                    => 'Error al recibir el audio (código ' . $fileError . ').',
    };
    echo json_encode(['output' => ['valid' => false, 'response' => $errMsg]]);
    exit;
}

// Validar tamaño máximo (25 MB — límite conservador por debajo del máximo de ElevenLabs)
$maxBytes = 25 * 1024 * 1024;
if (($_FILES['audio']['size'] ?? 0) > $maxBytes) {
    echo json_encode(['output' => ['valid' => false, 'response' => 'El audio supera el límite de 25 MB.']]);
    exit;
}

// Validar MIME real (no confiar en el nombre de archivo del cliente)
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeReal = finfo_file($finfo, $_FILES['audio']['tmp_name']);
finfo_close($finfo);

$mimePermitidos = ['audio/webm', 'video/webm', 'audio/mp4', 'audio/ogg', 'audio/mpeg', 'audio/wav'];
if (!in_array($mimeReal, $mimePermitidos, true)) {
    echo json_encode(['output' => ['valid' => false, 'response' => 'Formato de audio no soportado: ' . $mimeReal]]);
    exit;
}

// ── Guardar en directorio temporal con UUID ───────────────────────────────────
$ext = match ($mimeReal) {
    'audio/mp4'  => 'mp4',
    'audio/ogg'  => 'ogg',
    'audio/mpeg' => 'mp3',
    'audio/wav'  => 'wav',
    default      => 'webm',  // audio/webm y video/webm
};
$uuid    = bin2hex(random_bytes(16));
$tmpPath = __DIR__ . '/../../uploads/ia_tmp/' . $uuid . '.' . $ext;

if (!move_uploaded_file($_FILES['audio']['tmp_name'], $tmpPath)) {
    echo json_encode(['output' => ['valid' => false, 'response' => 'Error interno al guardar el audio.']]);
    exit;
}

// ── Cargar dependencias ───────────────────────────────────────────────────────
require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/Util.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../classes/ia/ElevenLabsService.php';
require_once __DIR__ . '/../classes/ia/IaScope.php';
require_once __DIR__ . '/../classes/ia/IaConversacion.php';
require_once __DIR__ . '/../classes/ia/ClaudeService.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolMaestros.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolCompromisos.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolDashboard.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolVisitas.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolProyectos.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolPae.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolHacienda.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolFactores.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolDesarrollo.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolGestionSocial.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolEstadisticas.php';
require_once __DIR__ . '/../classes/ia/herramientas/ToolBaseDeDatos.php';
require_once __DIR__ . '/../classes/ia/IaToolRegistry.php';
require_once __DIR__ . '/../classes/ia/AsistenteIA.php';

$conversacionId = (int) ($_POST['conversacion_id'] ?? 0);

// ── Transcribir ───────────────────────────────────────────────────────────────
try {
    $transcripcion = ElevenLabsService::transcribir($tmpPath);
} catch (RuntimeException $e) {
    @unlink($tmpPath);
    echo json_encode(['output' => ['valid' => false, 'response' => $e->getMessage()]]);
    exit;
}

@unlink($tmpPath);

// ── Enviar transcripción a Claude ─────────────────────────────────────────────
$asistente = new AsistenteIA();
$resultado = $asistente->chat($transcripcion, $conversacionId, 'voz');

if (!($resultado['output']['valid'] ?? false)) {
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Respuesta enriquecida para el widget ──────────────────────────────────────
echo json_encode([
    'output' => [
        'valid'          => true,
        'response'       => $resultado['output']['response'],
        'transcripcion'  => $transcripcion,
        'respuesta'      => $resultado['output']['response'],
        'conversacion_id'=> $resultado['output']['conversacion_id'],
        'mensaje_id'     => $resultado['output']['mensaje_id'] ?? 0,
    ],
], JSON_UNESCAPED_UNICODE);
