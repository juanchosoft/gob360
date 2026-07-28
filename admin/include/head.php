<?php
if (!headers_sent()) {
    if (ob_get_level() === 0) { ob_start(); }
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

// Verificar sesión activa — redirigir al login si no hay usuario autenticado
if (!isset($_SESSION['session_user'])) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Location: login.php');
    exit();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title>Acción Unificada - Gobernación de Santander</title>

    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="Phoenixcoded">

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">


    <script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/stylenew.css">
    <link rel="stylesheet" href="assets/css/custom-styles.css">
 

    <!-- Plugins -->
    <link href="plugins/select2/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- ✅ Highcharts se carga SOLO en dashboards que lo requieran -->
    <style>
        @media (max-width: 1220px){
          .pcoded-header .mobile-menu{
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 44px !important;
            height: 44px !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, rgba(32,66,127,.95), rgba(19,43,82,.95)) !important;
            border: 1px solid rgba(255,255,255,.14) !important;
            box-shadow: 0 14px 30px rgba(0,0,0,.25) !important;
            color: #fff !important;
            cursor: pointer !important;
          }
          .pcoded-header .mobile-menu i{
            color:#fff !important;
            font-size: 18px !important;
          }
        }

        @media (min-width: 1221px){
          .pcoded-header .mobile-menu{ display:none !important; }
        }
    </style>
</head>