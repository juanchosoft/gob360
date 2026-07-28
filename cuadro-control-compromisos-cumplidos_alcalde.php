<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Identificar tipo de usuario
$userType = SessionData::getUserType();
$tiposUsuarioMunicipal = ['Alcalde', 'Auxiliar_Alcalde', 'Secretario_Despacho', 'Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);
$municipioUsuario = $isUsuarioMunicipal ? SessionData::getCodigoMunicipio() : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --au-primary:#20427F;
      --au-primary-dark:#132b52;
      --au-accent:#2e58a8;

      --au-radius-xl:22px;
      --au-radius-lg:16px;

      --shadow-soft: 0 10px 30px rgba(0,0,0,.25);
      --shadow-mid:  0 18px 45px rgba(0,0,0,.35);

      --border-w: 1px solid rgba(255,255,255,.12);
      --ring: 0 0 0 .25rem rgba(46,88,168,.35);

      --w95: rgba(255,255,255,.95);
      --w90: rgba(255,255,255,.90);
      --w80: rgba(255,255,255,.80);
      --w70: rgba(255,255,255,.70);
      --w60: rgba(255,255,255,.60);

      /* ✅ evita que header fijo tape títulos */
      --safe-top: 96px;
    }

    /* ===== Fondo Dark Gradient ===== */
    body.dashboard-body{
      background:
        radial-gradient(900px 360px at 50% 115%, rgba(12, 35, 39, .95) 0%, rgba(12, 35, 39, 0) 55%),
        linear-gradient(135deg,
          #0b1221 0%,
          #0a1b24 35%,
          #0c2327 50%,
          #0b1321 75%,
          #0a1121 100%
        ) !important;
      color: var(--w90);
    }

    .pcoded-content{
      padding: calc(var(--safe-top) + 16px) 16px 18px !important;
    }
    @media (min-width:768px){
      :root{ --safe-top: 112px; }
      .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; }
    }
    @media (min-width:1200px){
      :root{ --safe-top: 120px; }
      .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; }
    }

    /* ===== breadcrumb pro (dark glass) ===== */
    .page-header .page-block{
      background: rgba(255,255,255,.06) !important;
      border: var(--border-w) !important;
      border-radius: var(--au-radius-xl) !important;
      box-shadow: var(--shadow-soft) !important;
      padding: 16px 16px;
      backdrop-filter: blur(10px);
    }
    .page-header h5{
      font-weight: 1000 !important;
      color: var(--w95) !important;
      margin: 0;
    }
    .breadcrumb{
      background: transparent !important;
      padding: 0;
      margin: .35rem 0 0 !important;
    }
    .breadcrumb a,
    .breadcrumb-item,
    .breadcrumb-item a{ color: var(--w80) !important; }
    .breadcrumb-item.active{ color: var(--w60) !important; }

    /* ===== Card SaaS (dark glass) ===== */
    .card.au-card{
      background: rgba(255,255,255,.06) !important;
      border: var(--border-w) !important;
      border-radius: var(--au-radius-xl) !important;
      box-shadow: var(--shadow-mid) !important;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    .card.au-card .card-header{
      background: rgba(255,255,255,.06) !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
      padding: 14px 16px !important;
      position: relative;
    }
    .card.au-card .card-header::before{
      content:"";
      position:absolute;
      top:0;left:0;
      width:100%;
      height:4px;
      background: linear-gradient(90deg, var(--au-primary), rgba(46,88,168,.35));
    }
    .card.au-card .card-header h5{
      margin:0;
      font-weight: 1000;
      color: var(--w95) !important;
    }

    /* menu 3 puntos (pcoded) */
    .card-header-right .btn.btn-icon{
      background: rgba(255,255,255,.08) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      color: var(--w90) !important;
      border-radius: 12px !important;
    }
    .dropdown-menu{
      background: rgba(10,17,33,.96) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      box-shadow: 0 18px 45px rgba(0,0,0,.45) !important;
    }
    .dropdown-item,
    .dropdown-item a,
    .dropdown-menu a{ color: var(--w90) !important; }
    .dropdown-item:hover{ background: rgba(255,255,255,.08) !important; }

    /* ===== Tabs PRO (dark) ===== */
    .nav-tabs{
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
      gap: 10px;
      margin-bottom: 14px;
    }
    .nav-tabs .nav-link{
      border: 1px solid rgba(255,255,255,.14) !important;
      border-bottom: none !important;
      border-radius: 999px !important;
      padding: 10px 14px !important;
      font-weight: 950;
      color: var(--w70) !important;
      background: rgba(255,255,255,.06) !important;
      transition: all .18s ease;
      white-space: nowrap;
    }
    .nav-tabs .nav-link:hover{
      transform: translateY(-1px);
      color: var(--w95) !important;
      background: rgba(255,255,255,.08) !important;
      box-shadow: 0 10px 22px rgba(0,0,0,.20);
    }
    .nav-tabs .nav-link.active{
      color: var(--w95) !important;
      background: rgba(32,66,127,.25) !important;
      border-color: rgba(46,88,168,.35) !important;
      box-shadow: 0 10px 22px rgba(32,66,127,.18);
    }
    @media (max-width:576px){
      .nav-tabs{ overflow-x:auto; flex-wrap: nowrap; padding-bottom: 6px; justify-content:flex-start !important; }
      .nav-tabs .nav-link{ font-size: 13px; padding: 9px 12px !important; }
    }

    /* ===== Filtros (dark glass) ===== */
    .filters-panel{
      border: var(--border-w);
      border-radius: var(--au-radius-lg);
      background: rgba(255,255,255,.06);
      box-shadow: var(--shadow-soft);
      padding: 14px;
      margin: 14px 0 14px;
      backdrop-filter: blur(10px);
    }
    .filters-panel label{
      font-size: .86rem;
      font-weight: 900;
      color: var(--w90);
      margin-bottom: 6px;
    }
    .filters-panel .form-control{
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,.14) !important;
      padding: .6rem .75rem;
      height: auto;
      background: rgba(10,17,33,.35) !important;
      color: var(--w95) !important;
    }
    .filters-panel .form-control::placeholder{ color: rgba(255,255,255,.55) !important; }
    .filters-panel .form-control:focus{
      border-color: rgba(46,88,168,.45) !important;
      box-shadow: var(--ring) !important;
      outline: none !important;
    }
    .filters-panel option{ color:#0f172a; } /* dropdown nativo */

    /* ===== Tabla (dark) ===== */
    .table-responsive{ overflow-x:auto; width:100%; }
    #dynamictable{ width:100% !important; table-layout:auto; white-space:normal; }
    html, body{ overflow-x:hidden !important; }
 /* ===== Tabla (FORZAR TODO A NEGRO) ===== */
#dynamictable{
  width: 100% !important;
  table-layout: auto;
  white-space: normal;
}

/* ✅ CABECERAS en negro */
#dynamictable thead th{
  color: rgba(255,255,255,.9) !important;
}

#dynamictable tbody td,
#dynamictable tbody td *{
  color: rgba(255,255,255,.86) !important;
}

#dynamictable tbody td a,
#dynamictable tbody td a *,
#dynamictable tbody td button,
#dynamictable tbody td button *{
  color: rgba(255,255,255,.86) !important;
}

#dynamictable tbody td i,
#dynamictable tbody td .feather,
#dynamictable tbody td svg,
#dynamictable tbody td svg *{
  fill: rgba(255,255,255,.8) !important;
  stroke: rgba(255,255,255,.8) !important;
  color: rgba(255,255,255,.8) !important;


}

/* tu layout */
#dynamictable tbody tr{ background: transparent !important; }
#dynamictable td{
  white-space: normal !important;
  word-break: break-word !important;
  max-width: 360px;
  vertical-align: top;
  border-bottom: 1px solid rgba(255,255,255,.08) !important;
}


    .tabla-shell{
      border: var(--border-w);
      border-radius: var(--au-radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-soft);
      background: rgba(255,255,255,.06);
      backdrop-filter: blur(10px);
    }
    #dynamictable thead th{
      position: sticky;
      top: 0;
      z-index: 2;
      background: rgba(255,255,255,.08) !important;
      backdrop-filter: blur(10px);
      color: var(--w95) !important;
      font-weight: 1000;
      border-bottom: 1px solid rgba(255,255,255,.14) !important;
      white-space: nowrap;
    }

    /* ===== DataTables (texto/paginación dark) ===== */
    div.dataTables_wrapper .dataTables_filter{ display:none !important; }
    div.dataTables_wrapper .dataTables_length,
    div.dataTables_wrapper .dataTables_info{
      color: var(--w70) !important;
    }
    div.dataTables_wrapper .dataTables_length select{
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,.14);
      padding: 6px 10px;
      background: rgba(10,17,33,.55);
      color: var(--w90);
    }
    div.dataTables_wrapper .dataTables_paginate .paginate_button{
      border-radius: 999px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      margin: 0 3px !important;
      background: rgba(255,255,255,.06) !important;
      color: var(--w80) !important;
    }
    div.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
      background: rgba(255,255,255,.10) !important;
      color: var(--w95) !important;
    }
    div.dataTables_wrapper .dataTables_paginate .paginate_button.current{
      background: rgba(32,66,127,.30) !important;
      border-color: rgba(46,88,168,.35) !important;
      color: var(--w95) !important;
      font-weight: 1000;
    }
    div.dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
      opacity:.45 !important;
      cursor:not-allowed !important;
    }

    /* ===== Card indicadores (tab 2) ===== */
    .card.au-subcard{
      border: var(--border-w);
      border-radius: var(--au-radius-xl);
      box-shadow: var(--shadow-soft);
      overflow: hidden;
      background: rgba(255,255,255,.06);
      backdrop-filter: blur(10px);
    }
    .card.au-subcard .card-header{
      background: linear-gradient(135deg, var(--au-primary), var(--au-primary-dark));
      color: #fff;
      border-bottom: none;
    }
    .card.au-subcard .card-header h5{
      color:#fff;
      font-weight:1000;
      margin:0;
    }
    .card.au-subcard .card-body{ color: var(--w90); }

    /* ===== Modales dark + FIX z-index ===== */
    .modal{ z-index: 20060 !important; }
    .modal-backdrop{ z-index: 20040 !important; }

    .modal-content{
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 18px;
      box-shadow: 0 25px 80px rgba(0,0,0,.55);
      overflow:hidden;
      background: rgba(10,17,33,.92);
      color: var(--w90);
      backdrop-filter: blur(10px);
    }
    .modal-header{
      background: rgba(255,255,255,.06);
      border-bottom: 1px solid rgba(255,255,255,.10);
    }
    .modal-title{ font-weight:1000; color: var(--w95); }
    .modal-body{ background: transparent; color: var(--w90); }
    .modal .close{
      color: var(--w90) !important;
      opacity: .9 !important;
      text-shadow: none !important;
    }
    /* ===== Ajuste fino de tipografía en TD ===== */
#dynamictable tbody td{
  font-size: 0.82rem !important;   /* más compacto y elegante */
  line-height: 1.35 !important;    /* mejor lectura */
  padding: 8px 10px !important;    /* reduce altura de filas */
}

/* Texto secundario dentro de TD */
#dynamictable tbody td small,
#dynamictable tbody td .text-muted{
  font-size: 0.75rem !important;
}

/* Íconos dentro de la tabla un poco más contenidos */
#dynamictable tbody td i,
#dynamictable tbody td svg{
  font-size: 0.9rem !important;
}

/* En pantallas grandes aún más pro */
@media (min-width:1200px){
  #dynamictable tbody td{
    font-size: 0.8rem !important;
  }
}

  </style>
</head>

<body class="dashboard-body">
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

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- [ breadcrumb ] start -->
      <div class="page-header mb-3">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
                <div>
                  <h5 class="m-b-10 mb-0">Cuadro control municipios - Alcalde</h5>
                  <div style="color:var(--w70); font-size:.9rem; margin-top:6px;">
                    Compromisos <b style="color:var(--w95);">cumplidos</b> filtrables por secretaría, municipio, vereda y componente.
                  </div>
                </div>
                <?php include './admin/include/btn_back.php'; ?>
              </div>

              <ul class="breadcrumb mt-2">
                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Compromisos Cumplidos Alcalde / Cuadro Control municipios</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="row">
        <div class="col-sm-12">
          <div class="card au-card">

            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
              <h5 class="mb-0">Listado de compromisos cumplidos - Alcalde</h5>

              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> maximize</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                    <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                    <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                    <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> remove</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body">
              <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
                    role="tab" aria-controls="home" aria-selected="true" onclick="cargarCompromiso()">Compromisos</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
                    role="tab" aria-controls="profile" aria-selected="false" onclick="indicadores()">Indicadores por secretaria</button>
                </li>
              </ul>

              <div class="tab-content" id="myTabContent">

                <!-- TAB 1 -->
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                  <div class="filters-panel">
                    <div class="row g-3">

                      <div class="col-12 col-md-6 col-xl-4">
                        <label for="secretariaIdFiltro">Seleccionar Secretaría</label>
                        <select name="secretariaIdFiltro" id="secretariaIdFiltro" class="form-control" onchange="filtrarTabla()">
                          <option value="">Seleccione</option>
                        </select>
                        <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="68">
                      </div>

                      <div class="col-12 col-md-6 col-xl-4">
                        <label for="municipioFiltro">Seleccionar Municipio</label>
                        <select name="municipioFiltro" id="municipioFiltro" class="form-control" onchange="filtrarTabla()"></select>
                      </div>

                      <div class="col-12 col-md-6 col-xl-4">
                        <label for="veredaFiltro">Seleccionar Vereda</label>
                        <select name="veredaFiltro" id="veredaFiltro" class="form-control" onchange="filtrarTabla()">
                          <option value="">Seleccione primero un municipio</option>
                        </select>
                      </div>

                      <div class="col-12 col-md-6 col-xl-4">
                        <label for="componenteFiltro">Componente</label>
                        <select class="form-control" id="componenteFiltro" name="componenteFiltro" onchange="filtrarTabla()">
                          <option value="" selected>Todas</option>
                          <option value="JURÍDICO">JURÍDICO</option>
                          <option value="MEJORAMIENTO SERVICIO DE SALUD">MEJORAMIENTO SERVICIO DE SALUD</option>
                          <option value="INFRAESTRUCTURA HOSPITALARIA">INFRAESTRUCTURA HOSPITALARIA</option>
                          <option value="DOTACIÓN EN SALUD">DOTACIÓN EN SALUD</option>
                          <option value="INFRAESTRUCTURA PARA CULTURA Y TURISMO">INFRAESTRUCTURA PARA CULTURA Y TURISMO</option>
                          <option value="ATENCIÓN POBLACIÓN VULNERABLE">ATENCIÓN POBLACIÓN VULNERABLE</option>
                          <option value="TRANSPORTE ESCOLAR">TRANSPORTE ESCOLAR</option>
                          <option value="INFRAESTRUCTURA EDUCATIVA">INFRAESTRUCTURA EDUCATIVA</option>
                          <option value="VÍAS SECUNDARIAS Y TERCIARIAS">VÍAS SECUNDARIAS Y TERCIARIAS</option>
                          <option value="INFRAESTRUCTURA INSTITUCIONES">INFRAESTRUCTURA INSTITUCIONES</option>
                          <option value="INFRAESTRUCTURA AEROPORTUARIA">INFRAESTRUCTURA AEROPORTUARIA</option>
                          <option value="AGUA POTABLE - ALCANTARILLADO - PTAR">AGUA POTABLE - ALCANTARILLADO - PTAR</option>
                          <option value="PROMOCIÓN DEL TURISMO">PROMOCIÓN DEL TURISMO</option>
                          <option value="MEJORAMIENTO SERVICIO EDUCATIVO">MEJORAMIENTO SERVICIO EDUCATIVO</option>
                          <option value="DOTACIÓN EDUCATIVA">DOTACIÓN EDUCATIVA</option>
                          <option value="PUENTES">PUENTES</option>
                          <option value="FORTALECIMIENTO INSTITUCIONAL">FORTALECIMIENTO INSTITUCIONAL</option>
                          <option value="GESTIÓN DE RIESGO">GESTIÓN DE RIESGO</option>
                          <option value="KIT HERRAMIENTAS">KIT HERRAMIENTAS</option>
                          <option value="PROTECCIÓN MEDIO AMBIENTE">PROTECCIÓN MEDIO AMBIENTE</option>
                          <option value="INSTRUMENTOS MUSICALES">INSTRUMENTOS MUSICALES</option>
                          <option value="MEJORAMIENTO VIVIENDA">MEJORAMIENTO VIVIENDA</option>
                          <option value="ESCENARIOS DEPORTIVOS">ESCENARIOS DEPORTIVOS</option>
                          <option value="TIC">TIC</option>
                          <option value="APOYO AL DEPORTE">APOYO AL DEPORTE</option>
                          <option value="MINERO - ENERGÉTICO">MINERO - ENERGÉTICO</option>
                          <option value="SEGURIDAD Y CONVIVENCIA">SEGURIDAD Y CONVIVENCIA</option>
                          <option value="APOYO AL AGRO">APOYO AL AGRO</option>
                          <option value="ELECTRIFICACIÓN RURAL">ELECTRIFICACIÓN RURAL</option>
                        </select>
                      </div>

                    </div>
                  </div>

                  <div class="tabla-shell">
                    <div class="table-responsive tabla-informacion tabla-scroll">
                      <table class="table table-hover mb-0" id="dynamictable">
                        <thead>
                          <tr class="border-1">
                            <th>Item</th>
                            <th>Secretaria</th>
                            <th>Compromiso</th>
                            <th>Compromiso Pact.</th>
                            <th>Consecuencia</th>
                            <th>Respuesta</th>
                            <th>Estado</th>
                            <th>Municipio</th>
                            <th>Vereda</th>
                            <th>Componente</th>
                            <th>Tipo ejec.</th>
                            <th>Imagen</th>
                            <th>Fecha</th>
                            <th>Editar</th>
                            <th>Ver</th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>

                </div>

                <!-- TAB 2 -->
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                  <div class="card au-subcard mt-3">
                    <div class="card-header">
                      <h5 class="mb-0">Indicadores por Secretaría</h5>
                    </div>
                    <div class="card-body">
                      <div class="col-sm-12">
                        <div id="indicadoresContainer" class="mt-4 text-center"></div>
                      </div>
                    </div>
                  </div>
                </div>

              </div><!-- tab-content -->
            </div><!-- card-body -->
          </div><!-- card -->
        </div>
      </div>
      <!-- [ Main Content ] end -->

    </div>
  </div>

  <!-- Modal compromiso -->
  <div class="modal fade" id="modalCompromiso" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalle del Compromiso</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="padding: 20px;">
          <p id="contenidoCompromiso" style="white-space: pre-wrap;"></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para adjuntos -->
  <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="modalAdjuntoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Adjunto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center" id="contenidoAdjunto" style="padding: 20px;"></div>
      </div>
    </div>
  </div>

  <!-- [ Footer Content ] start -->
  <?php include 'admin/include/footer.php'; ?>
  <!-- [ Footer Content ] end -->

  <!-- Variables de sesión para JavaScript -->
  <input type="hidden" id="municipioUsuario" value="<?php echo $municipioUsuario; ?>">
  <input type="hidden" id="tipoUsuario" value="<?php echo $userType; ?>">
  <input type="hidden" id="isUsuarioMunicipal" value="<?php echo $isUsuarioMunicipal ? '1' : '0'; ?>">

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="text/javascript" src="admin/js/control-municipio-cumplidos-alcalde.js"></script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />

</body>
</html>
