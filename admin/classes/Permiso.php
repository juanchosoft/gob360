<?php

require_once __DIR__ . '/Util.php';

/**
 * @deprecated Los permisos por usuario se gestionan con roles RBAC (roles_permisos.php).
 */
class Permiso
{
    public function __construct()
    {
    }

    public static function permisos($rqst)
    {
        return Util::error_general(
            'La asignación individual de permisos fue reemplazada por roles. '
            . 'Gestione los permisos en Configuración → Roles y Permisos.'
        );
    }

    public static function savePermisos($rqst)
    {
        return Util::error_general(
            'La asignación individual de permisos fue reemplazada por roles. '
            . 'Asigne un rol al usuario según su tipo o edite el rol en Roles y Permisos.'
        );
    }
}
