<?php

/**
 * Exige un permiso KEY; redirige a permiso_denegado.php si no lo tiene.
 */
function requirePermission(string $permissionKey): void
{
    if (!SessionData::hasPermission($permissionKey)) {
        require dirname(__DIR__, 2) . '/permiso_denegado.php';
        exit;
    }
}

/**
 * Exige al menos uno de los permisos indicados.
 * @param string[] $permissionKeys
 */
function requireAnyPermission(array $permissionKeys): void
{
    foreach ($permissionKeys as $key) {
        if (SessionData::hasPermission($key)) {
            return;
        }
    }
    require dirname(__DIR__, 2) . '/permiso_denegado.php';
    exit;
}
