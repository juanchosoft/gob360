<?php
error_reporting(E_ERROR | E_PARSE);

// ✅ RUTA BASE ABSOLUTA /admin
$ADMIN_BASE = realpath(__DIR__ . '/..'); // __DIR__ = /admin/include  => /admin

if (!$ADMIN_BASE) {
  die('No se pudo resolver la ruta base /admin');
}

// ✅ Requires con rutas absolutas (ya no depende del CWD)
require_once $ADMIN_BASE . '/classes/Util.php';
require_once $ADMIN_BASE . '/classes/DbConection.php';
require_once $ADMIN_BASE . '/include/generic_validate_session.php';
require_once $ADMIN_BASE . '/classes/SessionData.php';
require_once $ADMIN_BASE . '/include/require_permission.php';
require_once $ADMIN_BASE . '/classes/PagePermissions.php';

PagePermissions::guardCurrentRequest();

function base_url(){
  $port = $_SERVER["SERVER_PORT"];
  $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'].":".$port : $_SERVER['SERVER_NAME'];

  $url = sprintf(
    "%s://%s%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $nameServer,
    $_SERVER['REQUEST_URI']
  );

  $url = str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
  return $url;
}