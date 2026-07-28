<?php
/**
 * Aplica 006_proyectos_planeacion_gestion.sql + seed de permisos planeación.
 * Uso: php admin/db/run_006_proyectos_planeacion_gestion.php
 */
require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/PermissionCatalog.php';

$db = new DbConection();
$pdo = $db->openConect();
$tProy = $db->getTable('tbl_proyectos_planeacion_alcaldia');
$tAdj = $db->getTable('tbl_proyectos_planeacion_gestion_adjuntos');

$statements = [
    "ALTER TABLE {$tProy} ADD COLUMN gestion_nota TEXT NULL COMMENT 'Última nota de gestión' AFTER secretario_planeacion",
    "ALTER TABLE {$tProy} ADD COLUMN gestion_usuario_id INT UNSIGNED NULL AFTER gestion_nota",
    "ALTER TABLE {$tProy} ADD COLUMN gestion_at DATETIME NULL AFTER gestion_usuario_id",
    "ALTER TABLE {$tProy} ADD COLUMN fecha_limite DATE NULL COMMENT 'Para retrasos en dashboard' AFTER fecha",
    "CREATE TABLE IF NOT EXISTS {$tAdj} (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      proyecto_id INT UNSIGNED NOT NULL,
      log_id BIGINT UNSIGNED NULL,
      usuario_id INT UNSIGNED NOT NULL,
      ruta VARCHAR(255) NOT NULL,
      nombre_original VARCHAR(255) NULL,
      dtcreated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_proyecto (proyecto_id),
      INDEX idx_log (log_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($statements as $i => $sql) {
    try {
        $pdo->exec($sql);
        echo ($i + 1) . ") OK\n";
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate column') !== false || stripos($msg, 'already exists') !== false) {
            echo ($i + 1) . ") SKIP\n";
            continue;
        }
        echo ($i + 1) . ") ERROR: " . $msg . "\n";
        exit(1);
    }
}

// Seed permisos del catálogo (upsert por key)
$permTable = $db->getTable('tbl_permissions');
$rolesTable = $db->getTable('tbl_roles');
$rolePermTable = $db->getTable('tbl_role_has_permissions');

$newKeys = [
    'proyectos.alcaldias.planeacion.create',
    'proyectos.alcaldias.planeacion.detail',
    'proyectos.alcaldias.planeacion.manage',
    'proyectos.alcaldias.planeacion.reopen',
    'proyectos.alcaldias.planeacion.view_all',
    'proyectos.alcaldias.planeacion.dashboard',
];

$catalogByKey = [];
foreach (PermissionCatalog::definitions() as $def) {
    $catalogByKey[$def['key']] = $def;
}

$stmtUpsert = $pdo->prepare(
    "INSERT INTO {$permTable} (permission_key, module, action, name, legacy_id, is_active)
     VALUES (:k, :m, :a, :n, NULL, 1)
     ON DUPLICATE KEY UPDATE name = VALUES(name), module = VALUES(module), action = VALUES(action), is_active = 1"
);

$keyToId = [];
foreach ($newKeys as $key) {
    $def = $catalogByKey[$key] ?? null;
    if (!$def) {
        echo "WARN: key no está en PermissionCatalog: {$key}\n";
        continue;
    }
    $stmtUpsert->execute([
        ':k' => $def['key'],
        ':m' => $def['module'],
        ':a' => $def['action'],
        ':n' => $def['name'],
    ]);
    $idStmt = $pdo->prepare("SELECT id FROM {$permTable} WHERE permission_key = :k LIMIT 1");
    $idStmt->execute([':k' => $key]);
    $pid = (int) $idStmt->fetchColumn();
    if ($pid > 0) {
        $keyToId[$key] = $pid;
        echo "PERM OK: {$key} (#{$pid})\n";
    }
}

// También asegurar view existente
$viewStmt = $pdo->prepare("SELECT id FROM {$permTable} WHERE permission_key = :k LIMIT 1");
$viewStmt->execute([':k' => 'proyectos.alcaldias.planeacion.view']);
$viewId = (int) $viewStmt->fetchColumn();
if ($viewId > 0) {
    $keyToId['proyectos.alcaldias.planeacion.view'] = $viewId;
}

$roleKeysAllPlaneacion = [
    'super_administrador',
    'administrador',
    'gobernador',
    'secretario_despacho',
    'secretaria_despacho_gobernacion',
];
$roleKeysAlcaldia = ['alcalde', 'auxiliar_alcalde'];
$roleKeysSecretarios = ['secretario_despacho', 'secretaria_despacho_gobernacion', 'auxiliar_secretario', 'auxiliar_secret_gob'];

$grant = function (string $roleKey, array $permKeys) use ($pdo, $rolesTable, $rolePermTable, $keyToId) {
    $stmt = $pdo->prepare("SELECT id FROM {$rolesTable} WHERE role_key = :rk LIMIT 1");
    $stmt->execute([':rk' => $roleKey]);
    $roleId = (int) $stmt->fetchColumn();
    if ($roleId <= 0) {
        echo "SKIP role {$roleKey}\n";
        return;
    }
    $ins = $pdo->prepare("INSERT IGNORE INTO {$rolePermTable} (role_id, permission_id) VALUES (:rid, :pid)");
    foreach ($permKeys as $pk) {
        if (!isset($keyToId[$pk])) {
            continue;
        }
        $ins->execute([':rid' => $roleId, ':pid' => $keyToId[$pk]]);
    }
    echo "GRANT {$roleKey}: " . implode(', ', $permKeys) . "\n";
};

$revoke = function (string $roleKey, array $permKeys) use ($pdo, $rolesTable, $rolePermTable, $keyToId) {
    $stmt = $pdo->prepare("SELECT id FROM {$rolesTable} WHERE role_key = :rk LIMIT 1");
    $stmt->execute([':rk' => $roleKey]);
    $roleId = (int) $stmt->fetchColumn();
    if ($roleId <= 0) {
        return;
    }
    $del = $pdo->prepare("DELETE FROM {$rolePermTable} WHERE role_id = :rid AND permission_id = :pid");
    foreach ($permKeys as $pk) {
        if (!isset($keyToId[$pk])) {
            continue;
        }
        $del->execute([':rid' => $roleId, ':pid' => $keyToId[$pk]]);
    }
    echo "REVOKE {$roleKey}: " . implode(', ', $permKeys) . "\n";
};

$baseAlcaldia = [
    'proyectos.alcaldias.planeacion.view',
    'proyectos.alcaldias.planeacion.create',
    'proyectos.alcaldias.planeacion.detail',
    'proyectos.alcaldias.planeacion.dashboard',
];
$manageKeys = ['proyectos.alcaldias.planeacion.manage'];
$globalKeys = [
    'proyectos.alcaldias.planeacion.view_all',
    'proyectos.alcaldias.planeacion.reopen',
    'proyectos.alcaldias.planeacion.manage',
    'proyectos.alcaldias.planeacion.detail',
    'proyectos.alcaldias.planeacion.dashboard',
    'proyectos.alcaldias.planeacion.create',
    'proyectos.alcaldias.planeacion.view',
];

foreach ($roleKeysAllPlaneacion as $rk) {
    $grant($rk, $globalKeys);
}
foreach ($roleKeysAlcaldia as $rk) {
    $grant($rk, array_merge($baseAlcaldia, $manageKeys));
    $revoke($rk, ['proyectos.alcaldias.planeacion.view_all', 'proyectos.alcaldias.planeacion.reopen']);
}
foreach ($roleKeysSecretarios as $rk) {
    // Planeación gobernación (secre 28) opera con estos roles tipicamente
    $grant($rk, $globalKeys);
}

echo "Done.\n";
