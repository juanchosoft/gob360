<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

// Permisos
requirePermission('configuracion.sesiones.view');
$view = SessionData::hasPermission('configuracion.sesiones.view');
$create = false;
$edit = false;
$permits = false;

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    /* ==========================================
       GOVTECH WOW – DARK GLASS (SOLO DISEÑO)
       REGLA: TBODY NEGRO + HOVER BLANCO
       ========================================== */
    :root{
      --bg0:#070A12;
      --bg1:#0B1222;

      --card: rgba(255,255,255,.06);
      --stroke: rgba(255,255,255,.10);
      --stroke2: rgba(255,255,255,.14);

      --txt: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);

      --brand:#4f7cff;
      --brand2:#9b5cff;

      --r-xl:18px;
      --shadow: 0 20px 60px rgba(0,0,0,.35);
      --shadow2: 0 14px 40px rgba(0,0,0,.25);
    }

    body{
      background:
        radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
        radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
        radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
        linear-gradient(180deg, var(--bg0), var(--bg1)) !important;
      color: var(--txt);
      overflow-x:hidden;
    }

    .pcoded-main-container{ background: transparent !important; }
    .pcoded-content{ padding: 16px 16px !important; }
    @media(min-width:768px){ .pcoded-content{ padding: 24px 24px !important; } }
    @media(min-width:1200px){ .pcoded-content{ padding: 34px 42px !important; } }

    /* Breadcrumb readable */
    .page-header h5, .breadcrumb .breadcrumb-item, .breadcrumb .breadcrumb-item a{
      color: var(--txt) !important;
    }
    .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }

    /* Header block premium */
    .page-header .page-block{
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.05);
      border-radius: 16px;
      padding: 14px 14px;
      box-shadow: var(--shadow2);
      overflow:hidden;
      position: relative;
    }
    .page-header .page-block:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.25), transparent 65%),
        radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.18), transparent 65%);
      pointer-events:none;
    }
    .page-header .page-block > *{ position:relative; z-index:1; }

    /* Card pro */
    .card{
      border: 1px solid var(--stroke) !important;
      border-radius: var(--r-xl) !important;
      background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04)) !important;
      box-shadow: var(--shadow);
      overflow: hidden;
      position: relative;
    }
    .card:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.35), transparent 65%),
        radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.25), transparent 65%);
      pointer-events:none;
    }
    .card > *{ position:relative; z-index:1; }

    .card-header{
      background: rgba(0,0,0,.14) !important;
      border-bottom: 1px solid var(--stroke) !important;
      padding: 18px 18px !important;
    }
    .card-header h5{
      font-weight: 900 !important;
      letter-spacing: .2px;
      color: var(--txt) !important;
      margin:0 !important;
    }

    /* card option button */
    .btn-group.card-option .btn{
      border-radius: 12px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.20) !important;
      color: var(--txt) !important;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }

    .card-body{ padding: 18px !important; }
    @media(min-width:768px){ .card-body{ padding: 22px !important; } }

    /* Search */
    #customSearch{
      border-radius: 14px 0 0 14px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.22) !important;
      color: var(--txt) !important;
      min-height: 44px;
    }
    #customSearch::placeholder{ color: rgba(255,255,255,.50) !important; }
    .buscador-2 .input-group-text{
      border-radius: 0 14px 14px 0 !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.30) !important;
      color: var(--txt) !important;
      min-height: 44px;
    }

    /* Table wrapper */
    .table-responsive{
      border-radius: 16px;
      border: 1px solid var(--stroke) !important;
      background: rgba(0,0,0,.16);
      overflow:auto;
      margin-top: 14px;
    }

    /* ====== TU REGLA: TBODY NEGRO + HOVER BLANCO ====== */
    .table{
      margin-bottom: 0 !important;
    }
    .table thead th{
      background: rgba(255,255,255,.06) !important;
      color: rgba(255,255,255,.88) !important;
      border-bottom: 1px solid var(--stroke) !important;
      white-space: nowrap;
    }

    /* cuerpo: SIEMPRE letra negra */
    .table tbody td{
      border-top: 1px solid rgba(255,255,255,.06) !important;
      vertical-align: middle !important;
      color: #0b0f1a !important;
      font-weight: 700;
    }

    /* para que se lea con letra negra, el row queda claro */
    .table tbody tr{
      background: rgba(255,255,255,.92) !important;
      transition: background .15s ease, color .15s ease;
    }

    /* hover: fondo oscuro y letra blanca */
    .table-hover tbody tr:hover{
      background: rgba(0,0,0,.55) !important;
    }
    .table-hover tbody tr:hover td{
      color: #ffffff !important;
    }

    /* DataTables controls */
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_info{
      color: var(--muted) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button{
      color: var(--txt) !important;
      border-radius: 12px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.20) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current{
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      border-color: rgba(79,124,255,.45) !important;
    }
  </style>
</head>

<body class="">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Usuarios información de inicio de sesión</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Configuración General / Información inicio session</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="contenedor">
        <div class="contenido">
          <div class="card table-card-u">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <h5 class="mb-0 text-center w-100">Información inicio de session</h5>

              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card">
                      <a href="#!">
                        <span><i class="feather icon-maximize"></i> Maximizar</span>
                        <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                      </a>
                    </li>
                    <li class="dropdown-item minimize-card">
                      <a href="#!">
                        <span><i class="feather icon-minus"></i> Colapsar</span>
                        <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                      </a>
                    </li>
                    <li class="dropdown-item reload-card">
                      <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                    </li>
                    <li class="dropdown-item close-card">
                      <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                    </li>
                  </ul>
                </div>
              </div>

            </div>

            <div class="card-body table-border-style">
              <div class="navbar-form buscador-2">
                <div class="input-group input-primary">
                  <input type="text" id="customSearch" class="form-control" placeholder="Buscar">
                  <div class="input-group-append">
                    <span class="input-group-text">
                      <i class="feather icon-edit"></i>
                    </span>
                  </div>
                </div>
              </div>

              <div class="table-responsive tabla-informacion tabla-scroll">
                <table class="table table-hover mb-0" id="dynamictable">
                  <thead>
                    <tr class="border-1">
                      <th>Usuario id</th>
                      <th>Fecha</th>
                      <th>Nickname</th>
                      <th>Usuario</th>
                      <th>Ip</th>
                      <th>Navegador</th>
                    </tr>
                  </thead>
                </table>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/session-usuario.js"></script>
  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
</body>
</html>
