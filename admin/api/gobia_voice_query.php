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

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody ?: '', true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => 'La solicitud de voz no contiene JSON válido.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$chatInput = trim((string) ($input['chatInput'] ?? ''));
$sessionId = trim((string) ($input['sessionId'] ?? ''));
$userName = trim((string) ($input['userName'] ?? 'Usuario'));
$assistantName = trim((string) ($input['assistantName'] ?? 'ALMA'));

if ($userName === '') {
    $userName = 'Usuario';
}

if ($assistantName === '') {
    $assistantName = 'ALMA';
}

if ($chatInput === '') {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'La instrucción está vacía.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($sessionId === '') {
    $sessionId = 'gobia-' . bin2hex(random_bytes(12));
}

$webhookUrl = trim((string) getenv('GOBIA_N8N_WEBHOOK_URL'));

if ($webhookUrl === '') {
    $webhookUrl = 'https://n8n.spidersoftwareia.com/webhook/informe-barrancabermeja/chat';
}

if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'La URL del agente GOBIA no es válida.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$responseInstruction = <<<PROMPT
INSTRUCCIONES OBLIGATORIAS PARA {$assistantName}:
- Responde en español de Colombia.
- Responde únicamente lo que el usuario preguntó.
- Usa la extensión mínima necesaria para contestar correctamente.
- No agregues saludos, introducciones, contexto no solicitado, conclusiones, recomendaciones, ofrecimientos ni preguntas de seguimiento.
- No repitas la pregunta.
- No digas frases como "claro", "con gusto", "por supuesto" o "espero haberte ayudado".
- No anuncies que vas a revisar ni que vas a informar; la interfaz de voz administra ese aviso.
- Cuando una sola oración sea suficiente, responde con una sola oración.
- Si no cuentas con el dato solicitado, dilo directamente y sin inventar información.
PROMPT;

$agentInput = $responseInstruction
    . "\n\nNOMBRE DEL USUARIO: "
    . mb_substr($userName, 0, 150)
    . "\nPREGUNTA EXACTA DEL USUARIO: "
    . mb_substr($chatInput, 0, 5000);

$requestPayload = json_encode([
    'action' => 'sendMessage',
    'sessionId' => $sessionId,
    'chatInput' => $agentInput,
    'metadata' => [
        'channel' => 'gobia_voice',
        'module' => 'ALMA Asistente IA',
        'language' => 'es-CO',
        'timeZone' => 'America/Bogota',
        'userName' => mb_substr($userName, 0, 150),
        'assistantName' => mb_substr($assistantName, 0, 50),
        'responseMode' => 'strict_concise',
        'originalQuestion' => mb_substr($chatInput, 0, 5000),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$curl = curl_init($webhookUrl);

if ($curl === false) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'No fue posible iniciar la consulta a GOBIA.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $requestPayload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 150,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, text/plain;q=0.9, */*;q=0.8',
        'Content-Type: application/json',
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
            : 'El agente GOBIA no devolvió una respuesta.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$decoded = json_decode($response, true);
$answer = extractAssistantText($decoded ?? $response);

if ($httpCode >= 400) {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'message' => $answer !== ''
            ? $answer
            : 'El flujo de GOBIA respondió con un error.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($answer === '') {
    http_response_code(502);
    echo json_encode([
        'ok' => false,
        'message' => 'No se encontró texto utilizable en la respuesta del flujo n8n.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'ok' => true,
    'response' => $answer,
    'sessionId' => $sessionId,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

function extractAssistantText(mixed $value, int $depth = 0): string
{
    if ($depth > 10 || $value === null) {
        return '';
    }

    if (is_string($value)) {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        $nested = json_decode($trimmed, true);
        if (is_array($nested)) {
            $nestedText = extractAssistantText($nested, $depth + 1);
            if ($nestedText !== '') {
                return $nestedText;
            }
        }

        return strip_tags($trimmed);
    }

    if (!is_array($value)) {
        return '';
    }

    $preferredKeys = [
        'output',
        'response',
        'answer',
        'text',
        'message',
        'content',
        'completion',
        'result',
    ];

    foreach ($preferredKeys as $key) {
        if (array_key_exists($key, $value)) {
            $candidate = extractAssistantText($value[$key], $depth + 1);
            if ($candidate !== '') {
                return $candidate;
            }
        }
    }

    foreach ($value as $candidateValue) {
        $candidate = extractAssistantText($candidateValue, $depth + 1);
        if ($candidate !== '') {
            return $candidate;
        }
    }

    return '';
}
