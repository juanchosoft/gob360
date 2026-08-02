<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../lib/google_calendar.php';

if (!isset($_GET['state'], $_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], (string) $_GET['state'])) {
    http_response_code(400);
    exit('Solicitud OAuth inválida.');
}
unset($_SESSION['google_oauth_state']);
if (empty($_GET['code'])) {
    header('Location: ../calendario.php?google=cancelado');
    exit;
}
$config = calendarConfig();
$curl = curl_init(GOOGLE_TOKEN_URL);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => 25,
    CURLOPT_POSTFIELDS => http_build_query([
        'code' => (string) $_GET['code'],
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code',
    ]),
]);
$raw = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
curl_close($curl);
$token = json_decode((string) $raw, true);
if ($status !== 200 || empty($token['access_token'])) {
    http_response_code(502);
    exit('Google no pudo completar la conexión.');
}
$token['expires_at'] = time() + (int) ($token['expires_in'] ?? 3600);
$_SESSION['google_calendar_token'] = $token;
header('Location: ../calendario.php?google=conectado');
exit;
