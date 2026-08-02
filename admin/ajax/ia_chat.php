<?php
/**
 * Endpoint dedicado del asistente IA (chat).
 * Separado de rqst.php por el tiempo extendido de las llamadas a Claude API.
 *
 * POST params:
 *   mensaje         string  Texto del usuario (requerido)
 *   conversacion_id int     0 = crear nueva conversación
 *   origen          string  'texto' | 'voz' (default 'texto')
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache');

// Tiempo extendido para llamadas a la API de Claude
set_time_limit(120);

// Verificar sesión activa
if (empty($_SESSION['session_user'])) {
    http_response_code(401);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Sesión expirada.']]);
    exit;
}

// Verificar permiso RBAC
require_once __DIR__ . '/../classes/SessionData.php';
if (!SessionData::hasPermission('asistente_ia.chat.use')) {
    http_response_code(403);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Sin permiso para usar el asistente IA.']]);
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Método no permitido.']]);
    exit;
}

$mensaje        = trim($_POST['mensaje'] ?? '');
$conversacionId = (int) ($_POST['conversacion_id'] ?? 0);
$origen         = in_array($_POST['origen'] ?? '', ['texto', 'voz'], true)
                  ? ($_POST['origen'] ?? 'texto')
                  : 'texto';

if ($mensaje === '') {
    echo json_encode(['output' => ['valid' => false, 'response' => 'El mensaje no puede estar vacío.']]);
    exit;
}

// Cargar dependencias
require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/Util.php';
require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../classes/ia/IaScope.php';
require_once __DIR__ . '/../classes/ia/IaConversacion.php';
require_once __DIR__ . '/../classes/ia/IaReporte.php';
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
require_once __DIR__ . '/../classes/ia/IaToolRegistry.php';
require_once __DIR__ . '/../classes/ia/AsistenteIA.php';

$asistente = new AsistenteIA();
$resultado = $asistente->chat($mensaje, $conversacionId, $origen);

echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
