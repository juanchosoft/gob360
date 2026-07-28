<?php

require_once __DIR__ . '/Authorization.php';
require_once __DIR__ . '/Util.php';

/**
 * Validación de permisos para peticiones AJAX y APIs internas.
 * Política: fail-closed — toda operación debe estar en el mapa (salvo login).
 */
final class PermissionGate
{
    /**
     * Autoriza una operación o termina con JSON 403.
     */
    public static function authorize(string $permissionKey): void
    {
        if (!isset($_SESSION['session_user'])) {
            self::deny();
        }

        if (!Authorization::can($permissionKey)) {
            self::deny();
        }
    }

    /** @param string[] $permissionKeys */
    public static function authorizeAny(array $permissionKeys): void
    {
        if (!isset($_SESSION['session_user'])) {
            self::deny();
        }

        if (!Authorization::canAny($permissionKeys)) {
            self::deny();
        }
    }

    public static function authorizeCreateOrUpdate(string $createKey, string $updateKey, int $id): void
    {
        self::authorize($id > 0 ? $updateKey : $createKey);
    }

    /**
     * Valida según mapa de operaciones AJAX.
     *
     * Formatos de mapa:
     * - ''                    → solo sesión
     * - 'modulo.accion'       → un permiso
     * - ['a', 'b']            → cualquiera (OR) — evitar para create|update
     * - ['create'=>..,'update'=>..] → según id de la petición
     */
    public static function authorizeOperation(string $operation): void
    {
        if ($operation === '' || $operation === 'pms_usrlogin') {
            return;
        }

        if (!isset($_SESSION['session_user'])) {
            self::deny();
        }

        $mapFile = __DIR__ . '/../config/ajax_permissions_map.php';
        if (!is_file($mapFile)) {
            self::deny();
        }

        /** @var array<string, mixed> $map */
        $map = require $mapFile;

        // Fail-closed: operación desconocida = denegar
        if (!array_key_exists($operation, $map)) {
            self::deny();
        }

        $required = $map[$operation];

        // Solo sesión (lookups compartidos / perfil propio)
        if ($required === '' || $required === null) {
            return;
        }

        if (is_array($required)) {
            // Upsert: create vs update según id
            if (isset($required['create'], $required['update'])) {
                $idParam = $required['id_param'] ?? 'id';
                $id = (int) ($_REQUEST[$idParam] ?? 0);
                self::authorizeCreateOrUpdate($required['create'], $required['update'], $id);
                return;
            }

            // Lista OR (solo cuando realmente cualquiera basta)
            $keys = array_values(array_filter($required, 'is_string'));
            if ($keys === []) {
                self::deny();
            }
            self::authorizeAny($keys);
            return;
        }

        if (!is_string($required) || $required === '') {
            self::deny();
        }

        self::authorize($required);
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
