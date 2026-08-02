<?php

require_once __DIR__ . '/DbConection.php';
require_once __DIR__ . '/Util.php';

/**
 * CRUD de roles y asignación de permisos (estilo Spatie, rol único por usuario).
 */
class Role
{
  /** Mapeo tipo legacy → role_key (misma convención que migración SQL). */
    private const TIPO_TO_ROLE_KEY = [
        'SuperAdministrador' => 'super_administrador',
        'Administrador' => 'administrador',
        'Gobernador' => 'gobernador',
        'Secretario_Despacho' => 'secretario_despacho',
        'Secretaria_Despacho_Gobernacion' => 'secretaria_despacho_gobernacion',
        'Auxiliar' => 'auxiliar_secretario',
        'Auxiliar_secret_gob' => 'auxiliar_secret_gob',
        'Alcalde' => 'alcalde',
        'Auxiliar_Alcalde' => 'auxiliar_alcalde',
    ];

    /** Roles sistema no eliminables */
    private const SYSTEM_ROLE_KEYS = [
        'super_administrador',
        'administrador',
        'gobernador',
        'secretario_despacho',
        'secretaria_despacho_gobernacion',
        'auxiliar_secretario',
        'auxiliar_secret_gob',
        'alcalde',
        'auxiliar_alcalde',
    ];

    /**
     * Resuelve role_id en BD a partir del campo legacy `tipo` del usuario.
     */
    public static function resolveRoleIdByTipo(PDO $pdo, DbConection $db, string $tipo): ?int
    {
        $roleKey = self::TIPO_TO_ROLE_KEY[$tipo] ?? null;
        if ($roleKey === null) {
            return null;
        }

        $stmt = $pdo->prepare(
            'SELECT id FROM ' . $db->getTable('tbl_roles') . ' WHERE role_key = :role_key LIMIT 1'
        );
        $stmt->execute([':role_key' => $roleKey]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    /** Tipo legacy a partir del role_key (null si es rol personalizado). */
    public static function resolveTipoByRoleKey(string $roleKey): ?string
    {
        $flip = array_flip(self::TIPO_TO_ROLE_KEY);
        return $flip[$roleKey] ?? null;
    }

    /**
     * Datos del rol por id: id, role_key, name, tipo_legacy (nullable).
     * @return array{id:int,role_key:string,name:string,tipo_legacy:?string}|null
     */
    public static function resolveRoleById(PDO $pdo, DbConection $db, int $roleId): ?array
    {
        if ($roleId <= 0) {
            return null;
        }
        $stmt = $pdo->prepare(
            'SELECT id, role_key, name FROM ' . $db->getTable('tbl_roles') . ' WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $roleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $roleKey = (string) $row['role_key'];
        return [
            'id' => (int) $row['id'],
            'role_key' => $roleKey,
            'name' => (string) $row['name'],
            'tipo_legacy' => self::resolveTipoByRoleKey($roleKey),
        ];
    }

    /**
     * Resuelve role_id + tipo legacy desde el request de alta/edición de usuario.
     * Prioriza role_id; si no viene, usa tipo (compatibilidad).
     *
     * @return array{role_id:?int, tipo:string, role_key:?string}
     */
    public static function resolveRoleFromRequest(PDO $pdo, DbConection $db, array $rqst): array
    {
        $roleId = isset($rqst['role_id']) ? (int) $rqst['role_id'] : 0;
        $tipoReq = isset($rqst['tipo']) ? trim((string) $rqst['tipo']) : '';

        if ($roleId > 0) {
            $role = self::resolveRoleById($pdo, $db, $roleId);
            if ($role === null) {
                return ['role_id' => null, 'tipo' => '', 'role_key' => null];
            }
            // Mantener tipo legacy cuando exista; si no, usar role_key (roles personalizados)
            $tipo = $role['tipo_legacy'] ?? $role['role_key'];
            return [
                'role_id' => $role['id'],
                'tipo' => $tipo,
                'role_key' => $role['role_key'],
            ];
        }

        if ($tipoReq !== '' && $tipoReq !== 'Seleccione') {
            return [
                'role_id' => self::resolveRoleIdByTipo($pdo, $db, $tipoReq),
                'tipo' => $tipoReq,
                'role_key' => self::TIPO_TO_ROLE_KEY[$tipoReq] ?? null,
            ];
        }

        return ['role_id' => null, 'tipo' => '', 'role_key' => null];
    }

    /** HTML de <option> para selects de usuario (value = role_id). */
    public static function buildUsuarioRoleOptionsHtml(?int $selectedRoleId = null, ?string $selectedTipo = null): string
    {
        $html = '<option value="">Seleccione</option>';
        $res = self::getAll();
        $rows = ($res['output']['valid'] ?? false) ? ($res['output']['response'] ?? []) : [];
        foreach ($rows as $r) {
            $id = (int) ($r['id'] ?? 0);
            $key = (string) ($r['role_key'] ?? '');
            $name = (string) ($r['name'] ?? $key);
            $legacy = self::resolveTipoByRoleKey($key) ?? '';
            $sel = '';
            if ($selectedRoleId !== null && $selectedRoleId > 0 && $id === $selectedRoleId) {
                $sel = 'selected';
            } elseif ($selectedTipo && $legacy !== '' && $legacy === $selectedTipo) {
                $sel = 'selected';
            }
            $html .= '<option ' . $sel
                . ' value="' . $id . '"'
                . ' data-role-key="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '"'
                . ' data-legacy-tipo="' . htmlspecialchars($legacy, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
                . '</option>';
        }
        return $html;
    }

    public static function getAll($rqst = [])
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $sql = "SELECT r.*,
                    (SELECT COUNT(*) FROM " . $db->getTable('tbl_role_has_permissions') . " rp WHERE rp.role_id = r.id) AS permisos_count,
                    (SELECT COUNT(*) FROM " . $db->getTable('tbl_usuarios') . " u WHERE u.role_id = r.id) AS usuarios_count
                    FROM " . $db->getTable('tbl_roles') . " r
                    ORDER BY r.is_system DESC, r.name ASC";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            return ['output' => ['valid' => true, 'response' => $rows]];
        } catch (Throwable $e) {
            return Util::error_general('Error al listar roles: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function getById($rqst)
    {
        $id = isset($rqst['id']) ? (int) $rqst['id'] : 0;
        if ($id <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $stmt = $pdo->prepare("SELECT * FROM " . $db->getTable('tbl_roles') . " WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$role) {
                return Util::error_no_result();
            }

            $stmtPerm = $pdo->prepare(
                "SELECT permission_id FROM " . $db->getTable('tbl_role_has_permissions') . " WHERE role_id = :roleId"
            );
            $stmtPerm->execute([':roleId' => $id]);
            $permissionIds = array_map('intval', $stmtPerm->fetchAll(PDO::FETCH_COLUMN) ?: []);

            $role['permission_ids'] = $permissionIds;
            return ['output' => ['valid' => true, 'response' => $role]];
        } catch (Throwable $e) {
            return Util::error_general('Error al obtener rol: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function getPermissionsCatalog()
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Leer siempre desde BD (con id). No usar SHOW TABLES LIKE con schema calificado:
            // getTable() devuelve "db.tabla" y SHOW TABLES LIKE fallaba, cayendo a un
            // catálogo PHP sin id que dejaba los checkboxes sin marcar al editar.
            $table = $db->getTable('tbl_permissions');
            $sql = "SELECT id, permission_key AS `key`, module, action, name
                    FROM {$table}
                    WHERE is_active = 1
                    ORDER BY module ASC, name ASC";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                return Util::error_general('No hay permisos activos en tbl_permissions.');
            }

            $grouped = [];
            foreach ($rows as $row) {
                $row['id'] = (int) $row['id'];
                $grouped[$row['module']][] = $row;
            }
            return ['output' => ['valid' => true, 'response' => $grouped]];
        } catch (Throwable $e) {
            return Util::error_general('Error al cargar catálogo de permisos: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? (int) $rqst['id'] : 0;
        $roleKey = trim($rqst['role_key'] ?? '');
        $name = trim($rqst['name'] ?? '');
        $description = trim($rqst['description'] ?? '');
        $permissionIds = self::parsePermissionIds($rqst['permission_ids'] ?? '');

        if ($name === '' || $roleKey === '') {
            return Util::error_missing_data();
        }

        $roleKey = self::normalizeRoleKey($roleKey);

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                $stmt = $pdo->prepare("SELECT is_system, role_key FROM " . $db->getTable('tbl_roles') . " WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    $pdo->rollBack();
                    return Util::error_no_result();
                }

                // Evitar wipe accidental: editar sin IDs válidos borraba todos los permisos.
                if (empty($permissionIds)) {
                    $stmtCnt = $pdo->prepare(
                        "SELECT COUNT(*) FROM " . $db->getTable('tbl_role_has_permissions') . " WHERE role_id = :roleId"
                    );
                    $stmtCnt->execute([':roleId' => $id]);
                    if ((int) $stmtCnt->fetchColumn() > 0) {
                        $pdo->rollBack();
                        return Util::error_general(
                            'No se recibieron permisos válidos. Para no vaciar el rol, revise que el catálogo cargue IDs y vuelva a intentar.'
                        );
                    }
                }

                if ((int) $existing['is_system'] === 1) {
                    $roleKey = $existing['role_key'];
                }

                $stmtUp = $pdo->prepare(
                    "UPDATE " . $db->getTable('tbl_roles') . "
                     SET role_key = :role_key, name = :name, description = :description, dt_update = NOW()
                     WHERE id = :id"
                );
                $stmtUp->execute([
                    ':role_key' => $roleKey,
                    ':name' => $name,
                    ':description' => $description,
                    ':id' => $id,
                ]);
                $roleId = $id;
            } else {
                $stmtIns = $pdo->prepare(
                    "INSERT INTO " . $db->getTable('tbl_roles') . "
                    (role_key, name, description, is_system, dt_create)
                    VALUES (:role_key, :name, :description, 0, NOW())"
                );
                $stmtIns->execute([
                    ':role_key' => $roleKey,
                    ':name' => $name,
                    ':description' => $description,
                ]);
                $roleId = (int) $pdo->lastInsertId();
            }

            self::syncRolePermissions($pdo, $db, $roleId, $permissionIds);
            $pdo->commit();

            return ['output' => ['valid' => true, 'response' => ['id' => $roleId]]];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return Util::error_general('Error al guardar rol: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? (int) $rqst['id'] : 0;
        if ($id <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $stmt = $pdo->prepare("SELECT is_system FROM " . $db->getTable('tbl_roles') . " WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$role) {
                return Util::error_no_result();
            }
            if ((int) $role['is_system'] === 1) {
                return Util::error_general('No se puede eliminar un rol del sistema.');
            }

            $stmtUsers = $pdo->prepare(
                "SELECT COUNT(*) FROM " . $db->getTable('tbl_usuarios') . " WHERE role_id = :roleId"
            );
            $stmtUsers->execute([':roleId' => $id]);
            if ((int) $stmtUsers->fetchColumn() > 0) {
                return Util::error_general('El rol tiene usuarios asignados. Reasígnelos antes de eliminar.');
            }

            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM " . $db->getTable('tbl_role_has_permissions') . " WHERE role_id = :id")
                ->execute([':id' => $id]);
            $pdo->prepare("DELETE FROM " . $db->getTable('tbl_roles') . " WHERE id = :id")
                ->execute([':id' => $id]);
            $pdo->commit();

            return ['output' => ['valid' => true, 'response' => 'Rol eliminado']];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return Util::error_general('Error al eliminar rol: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /** @return int[] */
    private static function parsePermissionIds($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_unique(array_map('intval', $raw)));
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $parts = preg_split('/[,\-]+/', $raw) ?: [];
        $ids = [];
        foreach ($parts as $part) {
            $id = (int) trim($part);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }

    private static function normalizeRoleKey(string $key): string
    {
        $key = strtolower($key);
        $key = preg_replace('/[^a-z0-9_]+/', '_', $key) ?? $key;
        return trim($key, '_');
    }

    /** @param int[] $permissionIds */
    private static function syncRolePermissions(PDO $pdo, DbConection $db, int $roleId, array $permissionIds): void
    {
        $table = $db->getTable('tbl_role_has_permissions');
        $pdo->prepare("DELETE FROM {$table} WHERE role_id = :roleId")->execute([':roleId' => $roleId]);

        if (empty($permissionIds)) {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO {$table} (role_id, permission_id) VALUES (:roleId, :permissionId)");
        foreach ($permissionIds as $permissionId) {
            $stmt->execute([':roleId' => $roleId, ':permissionId' => $permissionId]);
        }
    }
}
