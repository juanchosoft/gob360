<?php

require_once __DIR__ . '/SessionData.php';
require_once __DIR__ . '/PermissionLegacyMap.php';
require_once __DIR__ . '/PermissionCatalog.php';
require_once __DIR__ . '/Util.php';

/**
 * Núcleo de autorización: roles + permisos por KEY (tablas RBAC nuevas).
 * SuperAdministrador tiene bypass total (decisión aprobada).
 */
final class Authorization
{
    /**
     * Carga permisos del usuario desde su rol en tbl_roles / tbl_role_has_permissions.
     *
     * @return array{
     *   permission_keys: string[],
     *   legacy_ids: int[],
     *   role: array{id: int, key: string, name: string}|null
     * }
     */
    public static function loadUserAuth(PDO $pdo, DbConection $db, int $userId, string $userType): array
    {
        $permissionKeys = [];
        $roleData = null;

        if (self::tablesExist($pdo, $db)) {
            $roleData = self::loadRoleForUser($pdo, $db, $userId);
            if ($roleData !== null) {
                $permissionKeys = self::loadPermissionKeysByRoleId($pdo, $db, (int) $roleData['id']);
            }
        }

        $permissionKeys = array_values(array_unique($permissionKeys));

        // Bridge opcional: algunos puntos legacy aún leen IDs numéricos derivados de KEY.
        $legacyIds = PermissionLegacyMap::keysToLegacyIds($permissionKeys);

        return [
            'permission_keys' => $permissionKeys,
            'legacy_ids' => $legacyIds,
            'role' => $roleData,
        ];
    }

    public static function can(string $permissionKey): bool
    {
        if (SessionData::superAdministrador()) {
            return true;
        }

        return SessionData::hasPermissionKey($permissionKey);
    }

    /** @param string[] $permissionKeys */
    public static function canAny(array $permissionKeys): bool
    {
        foreach ($permissionKeys as $key) {
            if (self::can($key)) {
                return true;
            }
        }
        return false;
    }

    /** @param string[] $permissionKeys */
    public static function canAll(array $permissionKeys): bool
    {
        foreach ($permissionKeys as $key) {
            if (!self::can($key)) {
                return false;
            }
        }
        return true;
    }

    private static function tablesExist(PDO $pdo, DbConection $db): bool
    {
        try {
            // No usar SHOW TABLES LIKE con schema calificado (db.tabla): siempre fallaba.
            $stmt = $pdo->query('SELECT 1 FROM ' . $db->getTable('tbl_roles') . ' LIMIT 1');
            return $stmt !== false;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** @return array{id: int, key: string, name: string}|null */
    private static function loadRoleForUser(PDO $pdo, DbConection $db, int $userId): ?array
    {
        $usersTable = $db->getTable('tbl_usuarios');
        $rolesTable = $db->getTable('tbl_roles');

        $sql = "SELECT r.id, r.role_key, r.name
                FROM {$usersTable} u
                INNER JOIN {$rolesTable} r ON r.id = u.role_id
                WHERE u.id = :userId AND u.role_id IS NOT NULL
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'key' => $row['role_key'],
            'name' => $row['name'],
        ];
    }

    /** @return string[] */
    private static function loadPermissionKeysByRoleId(PDO $pdo, DbConection $db, int $roleId): array
    {
        $sql = "SELECT p.permission_key
                FROM " . $db->getTable('tbl_role_has_permissions') . " rp
                INNER JOIN " . $db->getTable('tbl_permissions') . " p ON p.id = rp.permission_id
                WHERE rp.role_id = :roleId AND p.is_active = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':roleId' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}
