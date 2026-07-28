<?php
/**
 * Clase que contiene toda la informacion para utilizar
 * durante una sesion de session_user activo
 */
require_once __DIR__ . '/PermissionLegacyMap.php';

class SessionData {

    public static function getKey() {
        return 'e1ca41c9c29a354fea64d33228f45503';
    }

    public static function getRandom() {
        if (!isset($_SESSION['random'])) {
            $_SESSION['random'] = sha1(rand(100, 2000));
        }
        return $_SESSION['random'];
    }

    /**
     * @deprecated Usar hasPermission('modulo.accion') o PagePermissions::crudFlags().
     * Resuelve ID legacy → KEY RBAC en sesión.
     */
    public static function getPermission($id) {
        if (!isset($_SESSION['session_user'])) {
            return false;
        }

        if (self::superAdministrador()) {
            return true;
        }

        $key = PermissionLegacyMap::idToKey((int) $id);
        if ($key === null) {
            return false;
        }

        return self::hasPermissionKey($key);
    }

    /**
     * Valida permiso por KEY del nuevo sistema RBAC.
     */
    public static function hasPermissionKey(string $permissionKey): bool
    {
        if (!isset($_SESSION['session_user']['permission_keys'])) {
            return false;
        }

        return in_array($permissionKey, $_SESSION['session_user']['permission_keys'], true);
    }

    /**
     * Valida permiso por KEY (recomendado en vistas nuevas).
     */
    public static function hasPermission(string $permissionKey): bool
    {
        if (self::superAdministrador()) {
            return true;
        }

        return self::hasPermissionKey($permissionKey);
    }

    /** @return string[] */
    public static function permissionKeys(): array
    {
        return $_SESSION['session_user']['permission_keys'] ?? [];
    }

    /** @return array{id:int,key:string,name:string}|null */
    public static function getRole(): ?array
    {
        return $_SESSION['session_user']['role'] ?? null;
    }

    public static function getUserId() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['id'];
        } else {
            return sha1(rand(100, 2000));
        }
    }
    public static function getUserType() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['tipo'];
        }
        return "";
    }

    public static function getKeyUser() {
        if (isset($_SESSION['session_user'])) {
            $userid = $_SESSION['session_user']['id'];
            return md5($userid . SessionData::getKey() . SessionData::getRandom());
        } else {
            return md5(rand(100, 2000));
        }
    }

    public static function getKeyGeneric() {
        return md5(SessionData::getKey() . SessionData::getRandom());
    }

    public static function getUserFullName() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['nombre'] . ' ' . $_SESSION['session_user']['apellido'];
        } else {
            return "";
        }
    }
    public static function getNombreUsuario() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['nombre'];
        } else {
            return "";
        }
    }
    public static function getFotoUsuario() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['img'];
        } else {
            return "";
        }
    }

    public static function getCodigoMunicipio() {
        if (!isset($_SESSION['session_user'])) {
            return '';
        }
        return $_SESSION['session_user']['tbl_municipio_id'] ?? '';
    }

    public static function getSecretaria() {
        if (!isset($_SESSION['session_user'])) {
            return 0;
        }
        return intval($_SESSION['session_user']['tbl_secretarias_id'] ?? 0);
    }

    public static function getAvatar() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['img'] != "" ? 'assets/img/admin/usuarios/'.$_SESSION['session_user']['img'] : 'dist/img/logoblanco.png';
        }
    }

    public static function superAdministrador() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['tipo'] == "SuperAdministrador" ? true : false;
        }
    }

    public static function getNombreCaja() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['caja'][0]['codigo'] ;
        } else {
            return "";
        }
    }

    public static function getIdCaja() {
        if (isset($_SESSION['session_user'])) {
            return $_SESSION['session_user']['caja'][0]['id'];
        } else {
            return "";
        }
    }

    public static function getAvatarGeneric() {
        return 'dist/img/user.svg';
    }

    public static function getImageProduct($img) {
        if ($img !="" && file_exists("assets/img/admin/" . $img)) {
            return 'assets/img/admin/'.$img;
        }else{
            return 'assets/img/spider-logo.jpg';
        }
    }

    /**
     * CONFIGURACION DEL SISTEMA DE VARIABLES IMPORTANTES
     */

    public static function getConfigSistema() {
        return isset($_SESSION['session_user']) ? $_SESSION['session_user']['config'][0] : "";
    }

    public static function getConfigPrecioProd() {
        return isset($_SESSION['session_user']) ? $_SESSION['session_user']['config'][0]['config_precio_productos'] : "1";
    }

    public static function getConfigImpresionPOS() {
        return isset($_SESSION['session_user']) && $_SESSION['session_user']['config'][0]['impresion_termica'] == 'si'
        ? 'si' : 'no';
    }
}
