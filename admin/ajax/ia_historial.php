<?php
/**
 * Endpoint del historial de conversaciones IA.
 *
 * POST op=listar  → devuelve las últimas 30 conversaciones del usuario
 * POST op=cargar  → devuelve los mensajes de una conversación (conversacion_id requerido)
 * POST op=nueva   → crea una conversación vacía y devuelve su ID
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache');

if (empty($_SESSION['session_user'])) {
    http_response_code(401);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Sesión expirada.']]);
    exit;
}

require_once __DIR__ . '/../classes/SessionData.php';
if (!SessionData::hasPermission('asistente_ia.chat.use')) {
    http_response_code(403);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Sin permiso para usar el asistente IA.']]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['output' => ['valid' => false, 'response' => 'Método no permitido.']]);
    exit;
}

require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/Util.php';
require_once __DIR__ . '/../classes/ia/IaConversacion.php';

$op = trim($_POST['op'] ?? '');

switch ($op) {

    case 'listar':
        $conversaciones = IaConversacion::listarPropias();
        echo json_encode([
            'output' => [
                'valid'    => true,
                'response' => $conversaciones,
            ],
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'cargar':
        $conversacionId = (int) ($_POST['conversacion_id'] ?? 0);
        if ($conversacionId <= 0) {
            echo json_encode(['output' => ['valid' => false, 'response' => 'ID de conversación inválido.']]);
            break;
        }
        try {
            $mensajes = IaConversacion::cargarMensajes($conversacionId);
            echo json_encode([
                'output' => [
                    'valid'    => true,
                    'response' => $mensajes,
                    'conversacion_id' => $conversacionId,
                ],
            ], JSON_UNESCAPED_UNICODE);
        } catch (RuntimeException $e) {
            echo json_encode(['output' => ['valid' => false, 'response' => $e->getMessage()]]);
        }
        break;

    case 'nueva':
        $conversacionId = IaConversacion::crear();
        echo json_encode([
            'output' => [
                'valid'           => true,
                'response'        => 'Conversación creada.',
                'conversacion_id' => $conversacionId,
            ],
        ]);
        break;

    default:
        echo json_encode(['output' => ['valid' => false, 'response' => "Operación '{$op}' no reconocida."]]);
        break;
}
