<?php
declare(strict_types=1);

header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Método no permitido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$apiKey = 'sk_4f966546bece67b42738d70deef3f6c5a369b4568a3cc3dc';
$voiceId = 'a7NxsR5B1mpwgpVa10g3';
$modelId = 'eleven_flash_v2_5';

if ($modelId === '') {
    $modelId = 'eleven_flash_v2_5';
}

if ($apiKey === '' || $voiceId === '') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'La configuración segura de ElevenLabs no está disponible en el servidor.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody ?: '', true);
$text = trim((string) ($input['text'] ?? ''));

if ($text === '') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'El texto de respuesta está vacío.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($text) > 5000) {
    $text = mb_substr($text, 0, 5000);
}

$url = sprintf(
    'https://api.elevenlabs.io/v1/text-to-speech/%s?output_format=mp3_44100_128',
    rawurlencode($voiceId)
);

$payload = json_encode([
    'text' => $text,
    'model_id' => $modelId,
    'voice_settings' => [
        'stability' => 0.48,
        'similarity_boost' => 0.82,
        'style' => 0.35,
        'use_speaker_boost' => true,
        'speed' => 1.0,
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$curl = curl_init($url);

if ($curl === false) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'No fue posible iniciar la conexión con ElevenLabs.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 12,
    CURLOPT_TIMEOUT => 90,
    CURLOPT_HTTPHEADER => [
        'Accept: audio/mpeg',
        'Content-Type: application/json',
        'xi-api-key: ' . $apiKey,
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$response = curl_exec($curl);
$httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

if (!is_string($response) || $response === '' || $httpCode >= 400) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(502);

    $remoteMessage = '';
    if (is_string($response) && $response !== '') {
        $remotePayload = json_decode($response, true);
        $remoteMessage = (string) (
            $remotePayload['detail']['message']
            ?? $remotePayload['detail']
            ?? $remotePayload['message']
            ?? ''
        );
    }

    echo json_encode([
        'ok' => false,
        'message' => $remoteMessage !== ''
            ? $remoteMessage
            : ($curlError !== '' ? $curlError : 'ElevenLabs no generó el audio.'),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: audio/mpeg');
header('Content-Length: ' . strlen($response));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
echo $response;
