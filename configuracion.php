<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Secretarias.php';

// Permisos
requirePermission('configuracion.sistema.view');
$view = SessionData::hasPermission('configuracion.sistema.view');
$create = SessionData::hasPermission('configuracion.sistema.update');
$edit = SessionData::hasPermission('configuracion.sistema.update');
$permits = SessionData::hasPermission('configuracion.sistema.update');

$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

//Información de Secretarias
$arr = Secretarias::getAll(null);
$isvalid = $arr['output']['valid'] ?? false;
$arr = $arr['output']['response'] ?? [];
$modulo = 'Secretarias';
?>

<body class="dashboard-body gov-pro">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <!-- [ navigation menu ] start -->
  <?php include './admin/include/navbar.php'; ?>
  <!-- [ navigation menu ] end -->

  <!-- [ Header ] start -->
  <?php include './admin/include/header.php'; ?>
  <!-- [ Header ] end -->

  <style>
    :root{
      --bg0:#070A12;
      --bg1:#0B1222;
      --card: rgba(255,255,255,.06);
      --card2: rgba(255,255,255,.085);
      --stroke: rgba(255,255,255,.10);
      --stroke2: rgba(255,255,255,.14);
      --txt: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);
      --muted2: rgba(255,255,255,.50);
      --good:#18ff6d;
      --warn:#ffd166;
      --bad:#ff5b7a;
      --info:#56ccff;
      --brand:#4f7cff;
      --brand2:#9b5cff;
    }

    body.gov-pro{
      background:
        radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
        radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
        radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
        linear-gradient(180deg, var(--bg0), var(--bg1));
      color: var(--txt);
      overflow-x:hidden;
    }

    .pcoded-main-container{ background: transparent !important; }
    .pcoded-content{ padding-bottom: 2rem; }

    .page-header h5, .breadcrumb .breadcrumb-item, .breadcrumb .breadcrumb-item a{
      color: var(--txt) !important;
    }
    .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }

    /* Card Pro */
    .card.card-pro{
      border-radius: 18px !important;
      border: 1px solid var(--stroke) !important;
      background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04)) !important;
      box-shadow: 0 20px 60px rgba(0,0,0,.35);
      overflow: hidden;
      position: relative;
    }
    .card.card-pro:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.35), transparent 65%),
        radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.25), transparent 65%),
        radial-gradient(520px 220px at 50% 120%, rgba(24,255,109,.10), transparent 60%);
      pointer-events:none;
    }
    .card.card-pro > *{ position: relative; z-index: 1; }

    .card-pro .card-header{
      border-bottom: 1px solid var(--stroke) !important;
      background: rgba(0,0,0,.12) !important;
    }
    .card-pro .card-header h5{
      margin:0;
      font-weight: 900;
      letter-spacing: .2px;
      color: var(--txt);
      display:flex;
      align-items:center;
      gap:.6rem;
    }
    .card-pro .card-header h5 i{
      font-size: 18px;
      color: var(--info);
    }

    /* Mini header en el body */
    .section-top{
      border: 1px solid var(--stroke);
      background: rgba(255,255,255,.06);
      border-radius: 16px;
      padding: 12px 14px;
      box-shadow: 0 14px 40px rgba(0,0,0,.25);
    }
    .section-top .title{
      font-weight: 900;
      margin:0;
      font-size: 16px;
      letter-spacing: .2px;
      display:flex;
      align-items:center;
      gap:.55rem;
    }
    .section-top .sub{
      margin: 2px 0 0 0;
      color: var(--muted);
      font-size: 13px;
    }
    .chip{
      display:inline-flex;
      align-items:center;
      gap:.45rem;
      padding:.35rem .65rem;
      border-radius: 999px;
      border:1px solid var(--stroke);
      background: rgba(0,0,0,.20);
      color: var(--muted);
      font-size: 12px;
      white-space:nowrap;
    }
    .chip b{ color: var(--txt); font-weight: 900; }

    /* Form Pro */
    .form-control, .custom-select, select.form-control{
      border-radius: 14px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.22) !important;
      color: var(--txt) !important;
      box-shadow: none !important;
      min-height: 44px;
    }
    .form-control::placeholder{ color: rgba(255,255,255,.50) !important; }
    select.form-control option{ color: #0B1222; } /* lista desplegable legible */
    label{
      color: var(--muted) !important;
      font-weight: 800;
      letter-spacing: .1px;
      margin-bottom: .35rem;
    }
    .help-mini{
      font-size: 12px;
      color: var(--muted2);
      margin-top: .25rem;
    }

    /* Button Pro */
    .btn-wow{
      border:1px solid var(--stroke2);
      background: rgba(255,255,255,.06);
      color: var(--txt);
      border-radius: 14px;
      padding: .65rem 1rem;
      font-weight: 900;
      transition: .2s ease;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
      min-width: 150px;
    }
    .btn-wow:hover{
      transform: translateY(-1px);
      background: rgba(255,255,255,.10);
      color: var(--txt);
    }
    .btn-wow.primary{
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22));
      border-color: rgba(79,124,255,.45);
    }
    .btn-wow.ghost{
      background: rgba(0,0,0,.18);
    }

    /* Responsive spacing */
    @media (max-width: 575px){
      .btn-wow{ width:100%; min-width: unset; }
      .section-top{ padding: 12px; }
    }
  </style>

  <!-- [ Main Content ] start -->
  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-0">Configuración</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Configuración General / Configuración</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- Header mini pro -->
      <div class="row mb-3">
        <div class="col-12">
          <div class="section-top d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2">
            <div>
              <p class="title"><i class="bi bi-sliders"></i> Formulario de Configuración</p>
              <p class="sub">Ajustes globales del sistema • Control de reglas visuales y comentarios administrativos</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <span class="chip"><i class="bi bi-shield-check"></i> Acceso: <b><?= $isAdmin ? 'Admin' : 'No autorizado' ?></b></span>
              <span class="chip"><i class="bi bi-check2-circle"></i> Estado: <b><?= $isvalid ? 'OK' : 'Sin datos' ?></b></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Card Pro -->
      <div class="row">
        <div class="col-12">
          <div class="card card-pro">
            <div class="card-header">
              <h5><i class="bi bi-gear-wide-connected"></i> Configuración de colores y comentarios</h5>
            </div>

            <div class="card-body">
              <form id="formuconfiguracion" role="form" autocomplete="off">
                <input type="hidden" name="op" id="op" />
                <input type="hidden" name="idConfig" id="idConfig" />

                <div class="row g-3">
                  <div class="col-12 col-lg-6">
                    <label for="tipo_configuracion_colores">Tipo configuración colores <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipo_configuracion_colores" name="tipo_configuracion_colores">
                      <option value="Rango">Rango de Puntaje</option>
                      <option value="Puntaje">Puntaje</option>
                    </select>
                    <div class="help-mini">
                      Define cómo se aplican los colores: por rangos o por puntaje exacto.
                    </div>
                  </div>

                  <div class="col-12 col-lg-6">
                    <label for="comentarios">Comentarios</label>
                    <input type="text" class="form-control" id="comentarios" name="comentarios" placeholder="Escribe un comentario administrativo...">
                    <div class="help-mini">
                      Nota interna para el equipo (no afecta reportes públicos).
                    </div>
                  </div>
                </div>

                <div class="row mt-4">
                  <div class="col-12 d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <button type="button" onclick="CONFIGURACION.savedata();" class="btn btn-wow primary">
                      <i class="bi bi-save2"></i> Guardar cambios
                    </button>

                    <button type="button" class="btn btn-wow ghost" onclick="document.getElementById('formuconfiguracion').reset(); CONFIGURACION.editdata();">
                      <i class="bi bi-arrow-clockwise"></i> Recargar datos
                    </button>
                  </div>
                </div>

              </form>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>

  <!-- Required Js -->
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/configuracion.js"></script>

  <script>
    // Mantener tu flujo igual
    CONFIGURACION.editdata();
  </script>
</body>
</html>
