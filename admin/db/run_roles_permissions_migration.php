<?php
/**
 * Migración: tablas RBAC + seed de permisos y roles sistema.
 * Ejecutar: php admin/db/run_roles_permissions_migration.php
 */
require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/PermissionCatalog.php';
require_once __DIR__ . '/../classes/PermissionLegacyMap.php';

$db = new DbConection();
$pdo = $db->openConect();

$permissionsTable = $db->getTable('tbl_permissions');
$rolesTable = $db->getTable('tbl_roles');
$rolePermTable = $db->getTable('tbl_role_has_permissions');
$usersTable = $db->getTable('tbl_usuarios');

echo "=== Migración Roles y Permisos ===\n";

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$permissionsTable} (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            permission_key VARCHAR(120) NOT NULL,
            module VARCHAR(80) NOT NULL,
            action VARCHAR(40) NOT NULL,
            name VARCHAR(150) NOT NULL,
            description VARCHAR(255) NULL,
            legacy_id INT UNSIGNED NULL,
            dt_create DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            UNIQUE KEY uq_permission_key (permission_key),
            KEY idx_module (module),
            KEY idx_legacy_id (legacy_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "[OK] Tabla {$permissionsTable}\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$rolesTable} (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            role_key VARCHAR(80) NOT NULL,
            name VARCHAR(120) NOT NULL,
            description VARCHAR(255) NULL,
            is_system TINYINT(1) NOT NULL DEFAULT 0,
            dt_create DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            dt_update DATETIME NULL,
            UNIQUE KEY uq_role_key (role_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "[OK] Tabla {$rolesTable}\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS {$rolePermTable} (
            role_id INT UNSIGNED NOT NULL,
            permission_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (role_id, permission_id),
            CONSTRAINT fk_rhp_role FOREIGN KEY (role_id) REFERENCES {$rolesTable}(id) ON DELETE CASCADE,
            CONSTRAINT fk_rhp_permission FOREIGN KEY (permission_id) REFERENCES {$permissionsTable}(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "[OK] Tabla {$rolePermTable}\n";

    $colCheck = $pdo->query("SHOW COLUMNS FROM {$usersTable} LIKE 'role_id'");
    if ($colCheck && $colCheck->rowCount() === 0) {
        $pdo->exec("ALTER TABLE {$usersTable} ADD COLUMN role_id INT UNSIGNED NULL AFTER tipo");
        $pdo->exec("ALTER TABLE {$usersTable} ADD KEY idx_role_id (role_id)");
        echo "[OK] Columna role_id en {$usersTable}\n";
    } else {
        echo "[SKIP] Columna role_id ya existe\n";
    }

    $stmtPerm = $pdo->prepare("
        INSERT INTO {$permissionsTable} (permission_key, module, action, name, legacy_id, dt_create, is_active)
        VALUES (:permission_key, :module, :action, :name, :legacy_id, NOW(), 1)
        ON DUPLICATE KEY UPDATE
            module = VALUES(module),
            action = VALUES(action),
            name = VALUES(name),
            legacy_id = VALUES(legacy_id),
            is_active = 1
    ");

    foreach (PermissionCatalog::definitions() as $def) {
        $stmtPerm->execute([
            ':permission_key' => $def['key'],
            ':module' => $def['module'],
            ':action' => $def['action'],
            ':name' => $def['name'],
            ':legacy_id' => $def['legacy_id'],
        ]);
    }
    echo "[OK] Permisos sincronizados: " . count(PermissionCatalog::definitions()) . "\n";

    $systemRoles = [
        ['super_administrador', 'Super Administrador', 'Acceso total (bypass en código)', 1],
        ['administrador', 'Administrador', 'Administración general del sistema', 1],
        ['gobernador', 'Gobernador', 'Rol gobernador', 1],
        ['secretario_despacho', 'Secretario de Despacho', 'Secretario de despacho', 1],
        ['secretaria_despacho_gobernacion', 'Secretaría Despacho Gobernación', 'Secretaría despacho gobernación', 1],
        ['auxiliar_secretario', 'Auxiliar Secretaría', 'Auxiliar de secretaría', 1],
        ['auxiliar_secret_gob', 'Auxiliar Secretaría Gob.', 'Auxiliar secretaría gobernación', 1],
        ['alcalde', 'Alcalde', 'Alcalde municipal', 1],
        ['auxiliar_alcalde', 'Auxiliar Alcalde', 'Auxiliar de alcalde', 1],
    ];

    $stmtRole = $pdo->prepare("
        INSERT INTO {$rolesTable} (role_key, name, description, is_system, dt_create)
        VALUES (:role_key, :name, :description, :is_system, NOW())
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            description = VALUES(description),
            is_system = VALUES(is_system)
    ");

    foreach ($systemRoles as $role) {
        $stmtRole->execute([
            ':role_key' => $role[0],
            ':name' => $role[1],
            ':description' => $role[2],
            ':is_system' => $role[3],
        ]);
    }
    echo "[OK] Roles sistema sincronizados\n";

    $allPermissionIds = $pdo->query("SELECT id FROM {$permissionsTable} WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
    $allPermissionIds = array_map('intval', $allPermissionIds);

    $legacyToPermId = [];
    $rows = $pdo->query("SELECT id, legacy_id FROM {$permissionsTable} WHERE legacy_id IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $legacyToPermId[(int) $row['legacy_id']] = (int) $row['id'];
    }

    $permIdsFromLegacyRange = static function (array $legacyIds) use ($legacyToPermId, $allPermissionIds): array {
        $ids = [];
        foreach ($legacyIds as $legacyId) {
            if (isset($legacyToPermId[$legacyId])) {
                $ids[] = $legacyToPermId[$legacyId];
            }
        }
        return array_values(array_unique($ids));
    };

    $roleAssignments = [
        'super_administrador' => $allPermissionIds,
        'administrador' => $allPermissionIds,
        'gobernador' => $permIdsFromLegacyRange(range(5, 79)),
        'secretario_despacho' => $permIdsFromLegacyRange(range(5, 79)),
        'secretaria_despacho_gobernacion' => $permIdsFromLegacyRange(range(5, 79)),
        'alcalde' => $permIdsFromLegacyRange(range(5, 79)),
        'auxiliar_secretario' => $permIdsFromLegacyRange(range(5, 79)),
        'auxiliar_secret_gob' => $permIdsFromLegacyRange([
            65, 66, 64, 51, 68, 69, 67, 53, 54, 52, 59, 60, 58, 62, 63, 61,
            56, 57, 55, 47, 48, 39, 42, 40, 41, 38, 36, 44, 43, 45, 46, 49,
            50, 37, 23, 25, 24, 71, 72, 70, 9, 10, 8, 12, 13, 11, 14, 15, 16,
        ]),
        'auxiliar_alcalde' => $permIdsFromLegacyRange([
            65, 66, 64, 51, 68, 69, 67, 53, 54, 52, 59, 60, 58, 62, 63, 61,
            56, 57, 55, 47, 48, 39, 42, 40, 41, 38, 36, 44, 43, 45, 46, 49,
            50, 37, 23, 25, 24, 71, 72, 70, 9, 10, 8, 12, 13, 11, 14, 15, 16,
        ]),
    ];

    $stmtRoleId = $pdo->prepare("SELECT id FROM {$rolesTable} WHERE role_key = :role_key LIMIT 1");
    $stmtClear = $pdo->prepare("DELETE FROM {$rolePermTable} WHERE role_id = :role_id");
    $stmtInsert = $pdo->prepare("INSERT IGNORE INTO {$rolePermTable} (role_id, permission_id) VALUES (:role_id, :permission_id)");

    foreach ($roleAssignments as $roleKey => $permissionIds) {
        $stmtRoleId->execute([':role_key' => $roleKey]);
        $roleId = (int) $stmtRoleId->fetchColumn();
        if ($roleId <= 0) {
            continue;
        }

        $stmtClear->execute([':role_id' => $roleId]);
        foreach ($permissionIds as $permissionId) {
            $stmtInsert->execute([':role_id' => $roleId, ':permission_id' => $permissionId]);
        }
        echo "[OK] Permisos asignados a rol {$roleKey}: " . count($permissionIds) . "\n";
    }

    $tipoToRole = [
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

    $stmtAssignUser = $pdo->prepare("
        UPDATE {$usersTable} u
        INNER JOIN {$rolesTable} r ON r.role_key = :role_key
        SET u.role_id = r.id
        WHERE u.tipo = :tipo AND (u.role_id IS NULL OR u.role_id = 0)
    ");

    foreach ($tipoToRole as $tipo => $roleKey) {
        $stmtAssignUser->execute([':role_key' => $roleKey, ':tipo' => $tipo]);
        echo "[OK] Usuarios tipo {$tipo} → role_id (filas: {$stmtAssignUser->rowCount()})\n";
    }

    // Permisos nuevos (sin legacy_id): ampliar roles según perfil anterior por tipo
    $extendedRolePrefixes = [
        'gobernador' => ['dashboard.', 'visitas.', 'compromisos.', 'gestion_social.', 'plan_desarrollo.', 'secretarias.', 'interior.', 'deportes.', 'proyectos.', 'accion_unificada.', 'policia.', 'estrategicos.', 'seguimiento.', 'ia.', 'estado.'],
        'secretario_despacho' => ['dashboard.', 'visitas.', 'compromisos.', 'gestion_social.', 'plan_desarrollo.', 'secretarias.', 'interior.', 'deportes.', 'proyectos.', 'accion_unificada.', 'policia.', 'estrategicos.', 'ia.', 'estado.'],
        'secretaria_despacho_gobernacion' => ['dashboard.', 'visitas.', 'compromisos.', 'gestion_social.', 'plan_desarrollo.', 'secretarias.', 'interior.', 'deportes.', 'proyectos.', 'accion_unificada.', 'policia.', 'estrategicos.', 'ia.', 'estado.'],
        'auxiliar_secretario' => ['dashboard.', 'visitas.', 'compromisos.', 'gestion_social.', 'plan_desarrollo.', 'secretarias.', 'proyectos.', 'accion_unificada.', 'policia.', 'estrategicos.', 'ia.', 'estado.'],
        'alcalde' => ['dashboard.', 'visitas.', 'compromisos.', 'plan_desarrollo.', 'proyectos.', 'secretarias.', 'accion_unificada.', 'ia.', 'estado.'],
        'auxiliar_alcalde' => ['dashboard.', 'visitas.', 'compromisos.', 'plan_desarrollo.', 'proyectos.', 'secretarias.', 'accion_unificada.', 'ia.', 'estado.'],
        'auxiliar_secret_gob' => ['dashboard.', 'visitas.', 'compromisos.', 'accion_unificada.', 'proyectos.', 'estado.'],
    ];

    $stmtPermByPrefix = $pdo->prepare(
        "SELECT id FROM {$permissionsTable} WHERE is_active = 1 AND permission_key LIKE :prefix"
    );
    $stmtInsertRp = $pdo->prepare(
        "INSERT IGNORE INTO {$rolePermTable} (role_id, permission_id) VALUES (:role_id, :permission_id)"
    );

    foreach ($extendedRolePrefixes as $roleKey => $prefixes) {
        $stmtRoleId->execute([':role_key' => $roleKey]);
        $roleId = (int) $stmtRoleId->fetchColumn();
        if ($roleId <= 0) {
            continue;
        }

        $added = 0;
        foreach ($prefixes as $prefix) {
            $stmtPermByPrefix->execute([':prefix' => $prefix . '%']);
            $permIds = $stmtPermByPrefix->fetchAll(PDO::FETCH_COLUMN) ?: [];
            foreach ($permIds as $permId) {
                $stmtInsertRp->execute([':role_id' => $roleId, ':permission_id' => (int) $permId]);
                $added++;
            }
        }
        echo "[OK] Permisos extendidos (sin legacy) para {$roleKey}: {$added} inserciones\n";
    }

    echo "=== Migración completada ===\n";
} catch (Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
} finally {
    $db->closeConect();
}
