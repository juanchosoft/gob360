<!DOCTYPE html>
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Registro de visitas</title>
 

  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="dist/css/ionicons.min.css">
   <!-- Bootstrap4 Duallistbox -->
   <link rel="stylesheet" href="plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- Select2 -->
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- style principal -->
  <link rel="stylesheet" href="dist/css/stylePrin.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link  rel="icon"   href="./assets/img/favicon.png" type="image" />
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
 <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">

  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="plugins/sweetalert2/sweetalert2.min.css">

  <!-- Google Font: Source Sans Pro -->
  <link href="dist/css/ionicons.min.css" rel="stylesheet">
</head>
<style>
  /* ===== MAPA SaaS PRO (clases del fill) ===== */
.municipios,
.municipios-nuevo{
  transition: filter .12s ease, transform .12s ease, stroke .12s ease;
  stroke: rgba(15,23,42,.55);
  stroke-width: .35px;
}

.municipios:hover,
.municipios-nuevo:hover{
  filter: brightness(1.06) saturate(1.05);
  transform: translateY(-1px);
  cursor: pointer;
  stroke: rgba(15,23,42,.85);
}

/* Paleta por estado (coherente con PHP) */
.neutro{ fill:#EEF2F7 !important; }
.critico{ fill:#E53935 !important; }
.alto{ fill:#FB8C00 !important; }
.medio{ fill:#F6C026 !important; }
.estable{ fill:#2E7D32 !important; }
.info{ fill:#1E66F5 !important; }

/* Si un municipio está deshabilitado */
.disabled-map,
.mapaDisabled,
.deshabilitado,
.<?= isset($claseDeshabilitada) ? $claseDeshabilitada : 'claseDeshabilitada' ?>{
  pointer-events: none;
  opacity: .55;
  filter: grayscale(.15);
}

</style>

<div id="preloader">
  <div id="loader"></div>
</div>