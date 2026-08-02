<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../lib/google_calendar.php';

try {
    $token = calendarRefreshAccessToken();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $input = json_decode((string) file_get_contents('php://input'), true) ?: [];
    if ($method !== 'GET') {
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($_SESSION['calendar_csrf']) || !hash_equals($_SESSION['calendar_csrf'], $csrf)) {
            calendarJsonResponse(['error' => 'Sesión inválida. Recargue la página.'], 419);
        }
    }

    if ($method === 'GET') {
        $result = calendarHttp('GET', calendarApiUrl('', [
            'singleEvents' => 'true',
            'orderBy' => 'startTime',
            'timeMin' => (string) ($_GET['start'] ?? gmdate('c', strtotime('-3 months'))),
            'timeMax' => (string) ($_GET['end'] ?? gmdate('c', strtotime('+6 months'))),
            'maxResults' => 2500,
        ]), null, $token);
        $events = array_map(static fn(array $event): array => [
            'id' => $event['id'],
            'title' => $event['summary'] ?? '(Sin título)',
            'start' => $event['start']['dateTime'] ?? $event['start']['date'] ?? null,
            'end' => $event['end']['dateTime'] ?? $event['end']['date'] ?? null,
            'allDay' => isset($event['start']['date']),
            'extendedProps' => [
                'description' => $event['description'] ?? '',
                'location' => $event['location'] ?? '',
                'htmlLink' => $event['htmlLink'] ?? '',
            ],
        ], $result['items'] ?? []);
        calendarJsonResponse($events);
    }

    if ($method === 'POST') {
        calendarJsonResponse(calendarHttp('POST', calendarApiUrl(), calendarEventPayload($input), $token), 201);
    }
    $eventId = trim((string) ($input['id'] ?? $_GET['id'] ?? ''));
    if ($eventId === '') {
        calendarJsonResponse(['error' => 'Falta el identificador del evento'], 422);
    }
    if ($method === 'PUT') {
        calendarJsonResponse(calendarHttp('PUT', calendarApiUrl($eventId), calendarEventPayload($input), $token));
    }
    if ($method === 'DELETE') {
        calendarHttp('DELETE', calendarApiUrl($eventId), null, $token);
        calendarJsonResponse(['ok' => true]);
    }
    calendarJsonResponse(['error' => 'Método no permitido'], 405);
} catch (InvalidArgumentException $e) {
    calendarJsonResponse(['error' => $e->getMessage()], 422);
} catch (Throwable $e) {
    $status = $e->getCode() === 401 ? 401 : 502;
    calendarJsonResponse(['error' => $e->getMessage()], $status);
}
