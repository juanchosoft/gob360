<?php
/**
 * STT — Reconocimiento de voz.
 * Recibe el audio grabado, lo transcribe con ElevenLabs Scribe, envía la transcripción a
 * Claude y devuelve texto + respuesta.
 *
 * Para canal=voz_gobia (interfaz de voz de gobia.php) la respuesta se entrega en streaming
 * como líneas NDJSON: una línea final {"tipo":"final",...} (o {"tipo":"error",...}). La frase
 * de espera que el usuario escucha apenas termina de hablar NO sale de aquí — la dispara el
 * cliente en paralelo contra ia_historial.php/ia_tts.php, antes incluso de que este endpoint
 * responda. El widget de chat (canal=widget, valor por defecto) sigue recibiendo un único
 * objeto JSON, sin cambios de contrato.
 *
 * POST params (multipart/form-data):
 *   audio           file   Audio grabado (webm / mp4 / ogg / wav)
 *   conversacion_id int    0 = crear nueva conversación (ignorado si canal=voz_gobia:
 *                          ese canal siempre usa/crea la sesión diaria del usuario)
 *   canal           string 'widget' (default) | 'voz_gobia'
 */

// Desactivar buffering ANTES que cualquier otra cosa para que el aviso rápido del canal de
// voz llegue al navegador en cuanto se genera, sin esperar a que termine todo el turno.
// zlib.output_compression debe desactivarse aquí mismo: si está activo en php.ini, PHP
// junta toda la salida al final sin importar los flush() manuales.
@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', '0');
while (ob_get_level() > 0) {
    @ob_end_clean();
}
ob_implicit_flush(true);

session_start();

$canal = in_array($_POST['canal'] ?? '', ['widget', 'voz_gobia'], true) ? $_POST['canal'] : 'widget';

header('Content-Type: ' . ($canal === 'voz_gobia' ? 'application/x-ndjson; charset=utf-8' : 'application/json; charset=utf-8'));
header('Cache-Control: no-store, no-cache');
header('X-Accel-Buffering: no');

set_time_limit(120);

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

// Libera el lock del archivo de sesión apenas termina de leerlo: con el manejador de
// sesiones por defecto, session_start() bloquea cualquier otra petición con el mismo
// PHPSESSID hasta que el script termina — y este puede tardar varios segundos en STT/Claude.
// Sin este cierre, la frase de espera (ia_tts.php) y el saludo/aviso (ia_historial.php)
// quedan esperando en fila detrás de esta petición en vez de sonar en paralelo.
session_write_close();

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
require_once __DIR__ . '/../classes/ia/herramientas/ToolReportes.php';
require_once __DIR__ . '/../classes/ia/IaReporte.php';
require_once __DIR__ . '/../classes/ia/IaToolRegistry.php';
require_once __DIR__ . '/../classes/ia/AsistenteIA.php';

// ── Transcribir ───────────────────────────────────────────────────────────────
try {
    $transcripcion = ElevenLabsService::transcribir($tmpPath);
} catch (RuntimeException $e) {
    @unlink($tmpPath);
    echo json_encode(['output' => ['valid' => false, 'response' => $e->getMessage()]]);
    exit;
}

@unlink($tmpPath);

// ── Canal voz_gobia: sesión diaria + streaming del resultado final ────────────
if ($canal === 'voz_gobia') {
    // Envuelve toda la rama para que un error inesperado (BD, config, etc.) llegue como
    // línea 'error' legible en vez de cortar el stream a medias con un fatal error de PHP.
    try {
        $conversacionId = IaConversacion::obtenerOCrearSesionDiaria('voz_gobia');

        $asistente = new AsistenteIA();
        $resultado = $asistente->chat($transcripcion, $conversacionId, 'voz', 'voz_gobia');

        if (!($resultado['output']['valid'] ?? false)) {
            echo json_encode([
                'tipo'  => 'error',
                'texto' => $resultado['output']['response'] ?? 'Error desconocido.',
            ], JSON_UNESCAPED_UNICODE) . "\n";
            exit;
        }

        $textoFinal = (string) $resultado['output']['response'];

        // Si la respuesta incluye la URL de un informe PDF (tool generar_reporte_pdf), se
        // extrae aparte para que el cliente pueda mostrar un panel/enlace sin parsear texto.
        $pdfUrl = null;
        if (preg_match('#https?://\S*ia_reporte_pdf\.php\?id=\d+#', $textoFinal, $m)) {
            $pdfUrl = $m[0];
        }

        echo json_encode([
            'tipo'            => 'final',
            'texto'           => $textoFinal,
            'transcripcion'   => $transcripcion,
            'conversacion_id' => $resultado['output']['conversacion_id'],
            'mensaje_id'      => $resultado['output']['mensaje_id'] ?? 0,
            'pdf_url'         => $pdfUrl,
        ], JSON_UNESCAPED_UNICODE) . "\n";
    } catch (\Throwable $e) {
        error_log('[ALMA voz_gobia] ' . get_class($e) . ': ' . $e->getMessage());
        echo json_encode([
            'tipo'  => 'error',
            'texto' => 'Ocurrió un error inesperado en el servidor. Intenta de nuevo.',
        ], JSON_UNESCAPED_UNICODE) . "\n";
    }
    exit;
}

// ── Canal widget (comportamiento existente, sin cambios de contrato) ──────────
$conversacionId = (int) ($_POST['conversacion_id'] ?? 0);

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
