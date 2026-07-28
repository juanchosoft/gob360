<?php

require_once __DIR__ . '/PermissionGate.php';
require_once __DIR__ . '/Util.php';

/**
 * Autorización fail-closed para admin/controllers/*.php
 */
final class ControllerGate
{
    private static ?string $rawInput = null;
    /** @var array<string, mixed>|null */
    private static ?array $jsonBody = null;

    public static function rawInput(): string
    {
        if (self::$rawInput === null) {
            self::$rawInput = (string) file_get_contents('php://input');
        }
        return self::$rawInput;
    }

    /** @return array<string, mixed> */
    public static function jsonBody(): array
    {
        if (self::$jsonBody === null) {
            $decoded = json_decode(self::rawInput(), true);
            self::$jsonBody = is_array($decoded) ? $decoded : [];
        }
        return self::$jsonBody;
    }

    /**
     * Resuelve method / op / entrypoint.
     */
    public static function resolveMethod(): string
    {
        if (!empty($_POST['method'])) {
            return (string) $_POST['method'];
        }
        if (!empty($_GET['method'])) {
            return (string) $_GET['method'];
        }
        if (!empty($_POST['op'])) {
            return (string) $_POST['op'];
        }
        if (!empty($_GET['accion'])) {
            // hacienda plantilla usa accion=tipo plantilla; tratar como entrypoint
            return '_entrypoint';
        }

        $json = self::jsonBody();
        if (!empty($json['method'])) {
            return (string) $json['method'];
        }

        return '';
    }

    /**
     * Autoriza el script actual según controller_permissions_map.php
     */
    public static function authorizeScript(string $scriptPath, ?string $method = null): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $script = basename($scriptPath);
        $method = $method !== null ? $method : self::resolveMethod();

        $mapFile = __DIR__ . '/../config/controller_permissions_map.php';
        if (!is_file($mapFile)) {
            self::deny();
        }

        /** @var array<string, array<string, mixed>> $map */
        $map = require $mapFile;

        if (!isset($map[$script]) || !is_array($map[$script])) {
            self::deny();
        }

        $scriptMap = $map[$script];

        if ($method === '' && array_key_exists('_entrypoint', $scriptMap)) {
            $method = '_entrypoint';
        }

        if ($method === '' || !array_key_exists($method, $scriptMap)) {
            self::deny();
        }

        $required = $scriptMap[$method];

        // Solo sesión
        if ($required === '' || $required === null) {
            if (!isset($_SESSION['session_user'])) {
                self::deny();
            }
            return;
        }

        if (is_array($required) && isset($required['create'], $required['update'])) {
            $id = self::resolveEntityId($required['id_param'] ?? 'id');
            PermissionGate::authorizeCreateOrUpdate($required['create'], $required['update'], $id);
            return;
        }

        if (is_array($required)) {
            $keys = array_values(array_filter($required, 'is_string'));
            if ($keys === []) {
                self::deny();
            }
            PermissionGate::authorizeAny($keys);
            return;
        }

        if (!is_string($required) || $required === '') {
            self::deny();
        }

        PermissionGate::authorize($required);
    }

    private static function resolveEntityId(string $idParam): int
    {
        if (isset($_REQUEST[$idParam]) && $_REQUEST[$idParam] !== '') {
            return (int) $_REQUEST[$idParam];
        }

        $json = self::jsonBody();
        if (isset($json[$idParam])) {
            return (int) $json[$idParam];
        }
        if (isset($json['data']) && is_array($json['data']) && isset($json['data'][$idParam])) {
            return (int) $json['data'][$idParam];
        }
        if (isset($json['data']) && (is_int($json['data']) || (is_string($json['data']) && ctype_digit($json['data'])))) {
            return (int) $json['data'];
        }

        return 0;
    }

    private static function deny(): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
        }
        echo json_encode(Util::error_general('No tiene permisos para realizar esta operación.'));
        exit;
    }
}
