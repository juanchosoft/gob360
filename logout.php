<?php
session_start();
session_destroy();
$_SESSION = [];

// Prevenir que el navegador cachee la página anterior
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: login.php');
exit();
