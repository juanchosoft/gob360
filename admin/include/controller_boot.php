<?php

/**
 * Bootstrap de autenticación/autorización para admin/controllers.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/Util.php';
require_once __DIR__ . '/../classes/SessionData.php';
require_once __DIR__ . '/../classes/Authorization.php';
require_once __DIR__ . '/../classes/PermissionGate.php';
require_once __DIR__ . '/../classes/ControllerGate.php';
