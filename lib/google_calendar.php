<?php

declare(strict_types=1);

const GOOGLE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
const GOOGLE_CALENDAR_API = 'https://www.googleapis.com/calendar/v3';
const GOOGLE_CALENDAR_SCOPE = 'https://www.googleapis.com/auth/calendar.events';

function calendarConfig(): array
{
    $file = __DIR__ . '/../config/google.php';
    if (!is_file($file)) {
        throw new RuntimeException('Falta configurar config/google.php');
    }
    return require $file;
}

function calendarJsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function calendarHttp(string $method, string $url, ?array $body = null, ?string $accessToken = null): array
{
    $headers = ['Accept: application/json'];
    if ($accessToken) {
        $headers[] = 'Authorization: Bearer ' . $accessToken;
    }
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_POSTFIELDS => $body === null ? null : json_encode($body, JSON_UNESCAPED_UNICODE),
    ]);
    $raw = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($raw === false) {
        throw new RuntimeException('No fue posible comunicarse con Google: ' . $error);
    }
    $decoded = $raw === '' ? [] : json_decode($raw, true);
    if ($status < 200 || $status >= 300) {
        $message = $decoded['error']['message'] ?? $decoded['error_description'] ?? 'Error de Google Calendar';
        throw new RuntimeException((string) $message, $status);
    }
    return is_array($decoded) ? $decoded : [];
}

function calendarRefreshAccessToken(): string
{
    if (empty($_SESSION['google_calendar_token'])) {
        throw new RuntimeException('Google Calendar no está conectado', 401);
    }
    $token = $_SESSION['google_calendar_token'];
    if (($token['expires_at'] ?? 0) > time() + 60) {
        return $token['access_token'];
    }
    if (empty($token['refresh_token'])) {
        unset($_SESSION['google_calendar_token']);
        throw new RuntimeException('La conexión con Google expiró', 401);
    }

    $config = calendarConfig();
    $curl = curl_init(GOOGLE_TOKEN_URL);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $token['refresh_token'],
            'grant_type' => 'refresh_token',
        ]),
    ]);
    $raw = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    $newToken = json_decode((string) $raw, true);
    if ($status !== 200 || empty($newToken['access_token'])) {
        unset($_SESSION['google_calendar_token']);
        throw new RuntimeException('No fue posible renovar la conexión con Google', 401);
    }
    $_SESSION['google_calendar_token'] = array_merge($token, $newToken, [
        'expires_at' => time() + (int) ($newToken['expires_in'] ?? 3600),
    ]);
    return $newToken['access_token'];
}

function calendarApiUrl(string $eventId = '', array $query = []): string
{
    $config = calendarConfig();
    $url = GOOGLE_CALENDAR_API . '/calendars/' . rawurlencode($config['calendar_id'] ?? 'primary') . '/events';
    if ($eventId !== '') {
        $url .= '/' . rawurlencode($eventId);
    }
    return $url . ($query ? '?' . http_build_query($query) : '');
}

function calendarEventPayload(array $input): array
{
    $title = trim((string) ($input['title'] ?? ''));
    $start = (string) ($input['start'] ?? '');
    $end = (string) ($input['end'] ?? '');
    if ($title === '' || $start === '') {
        throw new InvalidArgumentException('El título y la fecha inicial son obligatorios');
    }
    $timezone = calendarConfig()['timezone'] ?? 'America/Bogota';
    $allDay = !empty($input['allDay']);
    $payload = [
        'summary' => mb_substr($title, 0, 250),
        'description' => mb_substr(trim((string) ($input['description'] ?? '')), 0, 8000),
        'location' => mb_substr(trim((string) ($input['location'] ?? '')), 0, 1000),
    ];
    if ($allDay) {
        $startDate = substr($start, 0, 10);
        $endDate = substr($end ?: $start, 0, 10);
        if ($endDate <= $startDate) {
            $endDate = (new DateTimeImmutable($startDate))->modify('+1 day')->format('Y-m-d');
        }
        $payload['start'] = ['date' => $startDate];
        // Google usa una fecha final exclusiva para los eventos de todo el día.
        $payload['end'] = ['date' => $endDate];
    } else {
        $payload['start'] = ['dateTime' => $start, 'timeZone' => $timezone];
        $payload['end'] = ['dateTime' => $end ?: $start, 'timeZone' => $timezone];
    }
    return $payload;
}
