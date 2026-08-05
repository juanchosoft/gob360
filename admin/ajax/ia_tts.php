<?php
/**
 * TTS — Síntesis de voz.
 * Recibe el ID de un mensaje del asistente, verifica propiedad, sintetiza con
 * ElevenLabs y devuelve el audio MP3 como binario en la respuesta HTTP.
 *
 * POST params:
 *   mensaje_id  int  ID del mensaje en tbl_ia_mensajes
 */

session_start();

header('Cache-Control: no-store, no-cache');

set_time_limit(30);

// ── Sesión ────────────────────────────────────────────────────────────────────
if (empty($_SESSION['session_user'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Sesión expirada.']);
    exit;
}

// ── Permiso de voz ────────────────────────────────────────────────────────────
require_once __DIR__ . '/../classes/SessionData.php';
if (!SessionData::hasPermission('asistente_ia.voz.use')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Sin permiso para el modo de voz.']);
    exit;
}

// Libera el lock de sesión cuanto antes para no encolarse detrás de ia_stt.php (ver
// comentario equivalente ahí) — esta síntesis debe poder correr en paralelo.
session_write_close();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

$mensajeId = (int) ($_POST['mensaje_id'] ?? 0);
if ($mensajeId <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'mensaje_id inválido.']);
    exit;
}

// ── Cargar dependencias ───────────────────────────────────────────────────────
require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/Util.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../classes/ia/IaConversacion.php';
require_once __DIR__ . '/../classes/ia/ElevenLabsService.php';

// ── Obtener texto y sintetizar ────────────────────────────────────────────────
try {
    // obtenerTextoMensaje() verifica propiedad: solo devuelve mensajes del usuario en sesión
    $texto = IaConversacion::obtenerTextoMensaje($mensajeId);
    $texto = ElevenLabsService::limpiarMarkdown($texto);

    if (trim($texto) === '') {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'El mensaje no tiene texto para sintetizar.']);
        exit;
    }

    $audio = ElevenLabsService::sintetizar($texto);
} catch (RuntimeException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// ── Devolver audio MP3 ────────────────────────────────────────────────────────
header('Content-Type: audio/mpeg');
header('Content-Length: ' . strlen($audio));
header('X-Content-Type-Options: nosniff');
echo $audio;
