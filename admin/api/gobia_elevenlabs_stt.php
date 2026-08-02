<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Método no permitido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$apiKey = 'sk_4f966546bece67b42738d70deef3f6c5a369b4568a3cc3dc';

if ($apiKey === '') {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'La configuración segura de ElevenLabs no está disponible en el servidor.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_FILES['audio']) || !is_array($_FILES['audio'])) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'No se recibió el audio del micrófono.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$file = $_FILES['audio'];
$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
$tmpPath = (string) ($file['tmp_name'] ?? '');
$originalName = basename((string) ($file['name'] ?? 'gobia-command.webm'));
$fileSize = (int) ($file['size'] ?? 0);

if ($errorCode !== UPLOAD_ERR_OK || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'El archivo de audio no pudo cargarse correctamente.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($fileSize <= 0 || $fileSize > 20 * 1024 * 1024) {
    http_response_code(413);
    echo json_encode([
        'ok' => false,
        'message' => 'El audio está vacío o supera el límite permitido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$mimeType = 'application/octet-stream';
$finfo = new finfo(FILEINFO_MIME_TYPE);
$detectedMime = $finfo->file($tmpPath);

if (is_string($detectedMime) && $detectedMime !== '') {
    $mimeType = $detectedMime;
}

$allowedMimeTypes = [
    'audio/webm',
    'video/webm',
    'audio/ogg',
    'audio/mp4',
    'video/mp4',
    'audio/mpeg',
    'audio/wav',
    'audio/x-wav',
    'application/octet-stream',
];

if (!in_array($mimeType, $allowedMimeTypes, true)) {
    http_response_code(415);
    echo json_encode([
        'ok' => false,
        'message' => 'El formato de audio no es compatible.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$curlFile = new CURLFile($tmpPath, $mimeType, $originalName);

$curl = curl_init('https://api.elevenlabs.io/v1/speech-to-text');

if ($curl === false) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'No fue posible iniciar la transcripción.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'file' => $curlFile,
        'model_id' => 'scribe_v2',
        'language_code' => 'es',
        'diarize' => 'false',
        'tag_audio_events' => 'false',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 12,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'xi-api-key: ' . $apiKey,
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$response = curl_exec($curl);
$httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

if (!is_string($response) || $response === '') {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'message' => $curlError !== ''
            ? $curlError
            : 'ElevenLabs no devolvió la transcripción.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = json_decode($response, true);

if ($httpCode >= 400 || !is_array($payload)) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'message' => (string) (
            $payload['detail']['message']
            ?? $payload['detail']
            ?? $payload['message']
            ?? 'ElevenLabs no pudo transcribir el audio.'
        ),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$text = trim((string) ($payload['text'] ?? ''));

if ($text === '') {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'No se detectó una instrucción de voz clara.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'ok' => true,
    'text' => $text,
    'language_code' => $payload['language_code'] ?? 'es',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
