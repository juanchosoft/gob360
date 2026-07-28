<?php

//MANTENER LA SESION ABIERTA CON COOKIES
ini_set('session.cache_expire', 200000);
ini_set('session.cache_limiter', 'none');
ini_set('session.cookie_lifetime', 2000000);
ini_set('session.gc_maxlifetime', 200000); //el mas importante


session_start();

if (!isset($_SESSION['session_user']) && !isset($_REQUEST["route_map"])) {
   header('Location: logout.php');
}
