<?php
/**
 * 007: tabla asignaciones + permisos assign / view_all_alcaldia / informes
 * Uso: php admin/db/run_007_proyectos_planeacion_asignaciones.php
 */
require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/PermissionCatalog.php';

$db = new DbConection();
$pdo = $db->openConect();
$tAsig = $db->getTable('tbl_proyectos_planeacion_asignaciones');

$statements = [
    "CREATE TABLE IF NOT EXISTS {$tAsig} (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      proyecto_id INT UNSIGNED NOT NULL,
      usuario_id INT UNSIGNED NOT NULL,
      asignado_por_id INT UNSIGNED NOT NULL,
      activo TINYINT(1) NOT NULL DEFAULT 1,
      observacion VARCHAR(500) NULL,
      dtcreated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uq_proyecto_usuario (proyecto_id, usuario_id),
      INDEX idx_usuario_activo (usuario_id, activo),
      INDEX idx_proyecto_activo (proyecto_id, activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($statements as $i => $sql) {
    try {
        $pdo->exec($sql);
        echo ($i + 1) . ") OK\n";
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'already exists') !== false) {
            echo ($i + 1) . ") SKIP\n";
            continue;
        }
        echo ($i + 1) . ") ERROR: {$msg}\n";
        exit(1);
    }
}

$permTable = $db->getTable('tbl_permissions');
$rolesTable = $db->getTable('tbl_roles');
$rolePermTable = $db->getTable('tbl_role_has_permissions');

$newKeys = [
    'proyectos.alcaldias.planeacion.view_all_alcaldia',
    'proyectos.alcaldias.planeacion.assign',
    'proyectos.alcaldias.planeacion.informes',
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
        echo "WARN: key no en catálogo: {$key}\n";
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

// Asegurar view_all / manage / dashboard existentes
foreach ([
    'proyectos.alcaldias.planeacion.view_all',
    'proyectos.alcaldias.planeacion.manage',
    'proyectos.alcaldias.planeacion.dashboard',
    'proyectos.alcaldias.planeacion.view',
    'proyectos.alcaldias.planeacion.detail',
] as $k) {
    $st = $pdo->prepare("SELECT id FROM {$permTable} WHERE permission_key = :k LIMIT 1");
    $st->execute([':k' => $k]);
    $id = (int) $st->fetchColumn();
    if ($id > 0) {
        $keyToId[$k] = $id;
    }
}

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

$deptKeys = [
    'proyectos.alcaldias.planeacion.view_all',
    'proyectos.alcaldias.planeacion.informes',
    'proyectos.alcaldias.planeacion.assign',
    'proyectos.alcaldias.planeacion.view_all_alcaldia',
];
$alcaldiaKeys = [
    'proyectos.alcaldias.planeacion.view_all_alcaldia',
    'proyectos.alcaldias.planeacion.assign',
    'proyectos.alcaldias.planeacion.informes',
];

foreach (['super_administrador', 'administrador', 'gobernador', 'secretario_despacho', 'secretaria_despacho_gobernacion'] as $rk) {
    $grant($rk, $deptKeys);
}
foreach (['alcalde', 'auxiliar_alcalde'] as $rk) {
    $grant($rk, $alcaldiaKeys);
    // Alcaldía nunca debe tener vista departamental global
    $revoke($rk, ['proyectos.alcaldias.planeacion.view_all']);
}
foreach (['auxiliar_secretario', 'auxiliar_secret_gob'] as $rk) {
    $grant($rk, ['proyectos.alcaldias.planeacion.informes', 'proyectos.alcaldias.planeacion.view_all']);
}

echo "Done.\n";
