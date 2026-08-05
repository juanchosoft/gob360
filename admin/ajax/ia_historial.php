<?php
/**
 * Endpoint del historial de conversaciones IA.
 *
 * POST op=listar     → devuelve las últimas 30 conversaciones del usuario (canal widget)
 * POST op=cargar      → devuelve los mensajes de una conversación (conversacion_id requerido)
 * POST op=nueva       → crea una conversación vacía y devuelve su ID (canal widget)
 * POST op=saludo_voz  → saludo inicial de la sesión diaria de voz de gobia.php: lo genera y
 *                       persiste el servidor (con hora de Colombia y nombre real de sesión)
 *                       para poder sintetizarlo con ia_tts.php como cualquier otro mensaje
 * POST op=frase_espera → frase corta de espera para reproducir apenas termina la grabación,
 *                        antes de que STT/Claude respondan (ver gobia_voice_assistant_gob360.js)
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

// Libera el lock de sesión cuanto antes para no encolarse detrás de ia_stt.php (ver
// comentario equivalente ahí) — el saludo, la frase de espera y la de interrupción deben
// poder generarse en paralelo con una transcripción/consulta en curso.
session_write_close();

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

    case 'saludo_voz':
        $conversacionId = IaConversacion::obtenerOCrearSesionDiaria('voz_gobia');

        $hora = (int) (new DateTime('now', new DateTimeZone('America/Bogota')))->format('G');
        $saludoHora = match (true) {
            $hora >= 5 && $hora < 12  => 'Buenos días',
            $hora >= 12 && $hora < 19 => 'Buenas tardes',
            default                   => 'Buenas noches',
        };

        $nombreCompleto = trim((string) SessionData::getNombreUsuario());
        $primerNombre   = $nombreCompleto !== '' ? explode(' ', $nombreCompleto)[0] : 'Usuario';
        $primerNombre   = mb_convert_case(mb_strtolower($primerNombre, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        $texto = "{$saludoHora}, {$primerNombre}. Soy ALMA. ¿En qué puedo ayudarte?";

        $mensajeId = IaConversacion::guardarMensaje(
            conversacionId: $conversacionId,
            rol: 'assistant',
            contenido: $texto,
            contenidoApi: [['type' => 'text', 'text' => $texto]],
            tokensEntrada: 0,
            tokensSalida: 0,
            origen: 'voz'
        );

        echo json_encode([
            'output' => [
                'valid'           => true,
                'response'        => $texto,
                'conversacion_id' => $conversacionId,
                'mensaje_id'      => $mensajeId,
            ],
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'frase_interrupcion':
        // Frase corta y amable que ALMA dice al ser interrumpida por voz (barge-in) mientras
        // está hablando, antes de volver a escuchar. Se persiste igual que el saludo para
        // poder sintetizarla con ia_tts.php (que solo trabaja con mensajes ya guardados).
        $conversacionId = IaConversacion::obtenerOCrearSesionDiaria('voz_gobia');

        $frases = ['Dime, te escucho.', 'Claro, adelante.', 'Perdón, continúa.', 'Te escucho.'];
        $texto  = $frases[array_rand($frases)];

        $mensajeId = IaConversacion::guardarMensaje(
            conversacionId: $conversacionId,
            rol: 'assistant',
            contenido: $texto,
            contenidoApi: [['type' => 'text', 'text' => $texto]],
            tokensEntrada: 0,
            tokensSalida: 0,
            origen: 'voz'
        );

        echo json_encode([
            'output' => [
                'valid'           => true,
                'response'        => $texto,
                'conversacion_id' => $conversacionId,
                'mensaje_id'      => $mensajeId,
            ],
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'frase_espera':
        // Frase corta que ALMA dice apenas termina de escuchar, mientras la transcripción y
        // la consulta real siguen en curso por detrás — igual que 'frase_interrupcion', se
        // persiste para poder sintetizarla con ia_tts.php.
        $conversacionId = IaConversacion::obtenerOCrearSesionDiaria('voz_gobia');

        $frases = ['Dame un momento, ya lo reviso.', 'Un segundo, ya te ayudo.', 'Claro, dame un instante.', 'Ya te cuento, un momento.'];
        $texto  = $frases[array_rand($frases)];

        $mensajeId = IaConversacion::guardarMensaje(
            conversacionId: $conversacionId,
            rol: 'assistant',
            contenido: $texto,
            contenidoApi: [['type' => 'text', 'text' => $texto]],
            tokensEntrada: 0,
            tokensSalida: 0,
            origen: 'voz'
        );

        echo json_encode([
            'output' => [
                'valid'           => true,
                'response'        => $texto,
                'conversacion_id' => $conversacionId,
                'mensaje_id'      => $mensajeId,
            ],
        ], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['output' => ['valid' => false, 'response' => "Operación '{$op}' no reconocida."]]);
        break;
}
