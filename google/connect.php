<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../lib/google_calendar.php';

$config = calendarConfig();
$state = bin2hex(random_bytes(24));
$_SESSION['google_oauth_state'] = $state;
$query = http_build_query([
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'response_type' => 'code',
    'scope' => GOOGLE_CALENDAR_SCOPE,
    'access_type' => 'offline',
    'prompt' => 'consent',
    'include_granted_scopes' => 'true',
    'state' => $state,
]);
header('Location: ' . GOOGLE_AUTH_URL . '?' . $query);
exit;
