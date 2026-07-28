<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
$isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
 if (!$isAdmin && !$isAlcalde) {
    require 'permiso_denegado.php';
}
?>

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
      --safe-top: 96px;
    }

    body.dashboard-body{
      background:
        radial-gradient(900px 360px at 50% 115%, rgba(12, 35, 39, .95) 0%, rgba(12, 35, 39, 0) 55%),
        linear-gradient(135deg, #0b1221 0%, #0a1b24 35%, #0c2327 50%, #0b1321 75%, #0a1121 100%) !important;
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
    .filters-panel option{ color:#0f172a; }

    .table-responsive{
      overflow-x: auto;
      width: 100%;
    }

    #dynamictable{
      width: 100% !important;
      table-layout: auto;
      white-space: normal;
    }

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

    #dynamictable tbody tr{ background: transparent !important; }
    #dynamictable tbody tr.even,
    #dynamictable tbody tr.odd{ background: transparent !important; }
    #dynamictable tbody tr:hover{ background: rgba(255,255,255,.04) !important; }
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

    div.dataTables_wrapper{ background: transparent !important; }
    div.dataTables_wrapper .dataTables_filter{ display:none !important; }
    div.dataTables_wrapper .dataTables_length,
    div.dataTables_wrapper .dataTables_info{
      color: rgba(255,255,255,.7) !important;
      font-weight: 700 !important;
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
      border: 1px solid rgba(255,255,255,.18) !important;
      margin: 0 3px !important;
      background: rgba(255,255,255,.10) !important;
      color: #fff !important;
      font-weight: 800 !important;
      padding: 0.4em 0.9em !important;
    }
    div.dataTables_wrapper .dataTables_paginate .paginate_button:hover{
      background: rgba(255,255,255,.18) !important;
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(0,0,0,.2);
    }
    div.dataTables_wrapper .dataTables_paginate .paginate_button.current{
      background: linear-gradient(135deg, #3b82f6, #4f46e5) !important;
      border-color: rgba(255,255,255,.25) !important;
      color: #fff !important;
      font-weight: 900 !important;
      box-shadow: 0 4px 16px rgba(59,130,246,.3);
    }
    div.dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
      opacity:.35 !important;
      cursor:not-allowed !important;
      background: transparent !important;
      border-color: transparent !important;
    }

    .modal{ z-index: 20060 !important; }
    .modal-backdrop{ z-index: 20040 !important; }
    .swal2-container{ z-index: 20080 !important; }

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
    .modal-title{
      font-weight: 1000;
      color: var(--w95);
    }
    .modal-body{
      background: transparent;
      color: var(--w90);
    }
    .modal .close{
      color: var(--w90) !important;
      opacity: .9 !important;
      text-shadow: none !important;
    }

    html, body { overflow-x: hidden !important; }
    .gap-2{ gap:.5rem; }
    .gap-3{ gap:1rem; }

    /* Modal fix: inner card / inputs con fondo oscuro */
    #modalCompromisoObservaciones .card{
      background: rgba(255,255,255,.06) !important;
      border: 1px solid rgba(255,255,255,.12);
    }
    #modalCompromisoObservaciones .card-body{
      background: transparent !important;
      color: var(--w90);
    }
    #modalCompromisoObservaciones .card-body p,
    #modalCompromisoObservaciones .card-body strong{
      color: var(--w90);
    }
    #modalCompromisoObservaciones .bg-light{
      background: rgba(10,17,33,.55) !important;
      color: var(--w90);
    }
    #modalCompromisoObservaciones .form-control{
      background: rgba(10,17,33,.55) !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      color: var(--w95) !important;
    }
    #modalCompromisoObservaciones .form-control option{
      background: #0a1121;
      color: #fff;
    }
    #modalCompromisoObservaciones label{
      color: var(--w90);
    }

    #dynamictable tbody td{
      font-size: 0.82rem !important;
      line-height: 1.35 !important;
      padding: 8px 10px !important;
    }

    #dynamictable tbody td small,
    #dynamictable tbody td .text-muted{
      font-size: 0.75rem !important;
    }

    #dynamictable tbody td i,
    #dynamictable tbody td svg{
      font-size: 0.9rem !important;
    }

    @media (min-width:1200px){
      #dynamictable tbody td{
        font-size: 0.8rem !important;
      }
    }
  </style>

  <div class="pcoded-main-container">
    <div class="pcoded-content">
      <!-- [ breadcrumb ] start -->
      <div class="page-header mb-3">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h5 class="m-b-10 mb-0">Aprobación cuadro control municipios - Alcalde</h5>
                  <div style="color:var(--w70); font-size:.9rem; margin-top:6px;">
                    Aprobación de compromisos por municipio, vereda y componente.
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
              <ul class="breadcrumb mt-2">
                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Aprobación compromisos Alcalde / Cuadro Control municipios</a></li>
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
              <h5 class="mb-0">Listado de compromisos Alcalde - Estado En Espera</h5>
              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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

              <!-- filtros -->
              <div class="filters-panel">
                <div class="row g-3">

                  <div class="col-12 col-md-6 col-xl-4">
                    <label for="secretariaIdFiltro">Seleccionar Secretaría</label>
                    <select name="secretariaIdFiltro" id="secretariaIdFiltro" class="form-control" onchange="filtrarTablaEnEspera()">
                      <option value="">Seleccione</option>
                    </select>
                    <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="68">
                  </div>

                  <div class="col-12 col-md-6 col-xl-4">
                    <label for="municipioFiltro">Seleccionar Municipio</label>
                    <select name="municipioFiltro" id="municipioFiltro" class="form-control" onchange="cargarVeredas(); filtrarTablaEnEspera();">
                    </select>
                  </div>

                  <div class="col-12 col-md-6 col-xl-4">
                    <label for="veredaFiltro">Seleccionar Vereda</label>
                    <select name="veredaFiltro" id="veredaFiltro" class="form-control" onchange="filtrarTablaEnEspera()">
                      <option value="">Seleccione primero un municipio</option>
                    </select>
                  </div>

                  <div class="col-12 col-md-6 col-xl-4">
                    <label for="componenteFiltro">Componente</label>
                    <select class="form-control" id="componenteFiltro" name="componenteFiltro" onchange="filtrarTablaEnEspera()">
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

              <!-- Tabla -->
              <div class="tabla-shell">
                <div class="table-responsive tabla-informacion tabla-scroll">
                  <table class="table table-hover mb-0" id="dynamictable">
                    <thead>
                      <tr class="border-1">
                        <th>Item</th>
                        <th>Secretaria</th>
                        <th>Compromiso</th>
                        <th>Consecuencia</th>
                        <th>Respuesta</th>
                        <th>Estado</th>
                        <th>Municipio</th>
                        <th>Vereda</th>
                        <th>Componente</th>
                        <th>Tipo ejec.</th>
                        <th>Fecha</th>
                        <th>Validar</th>
                        <th>Ver</th>
                        <th>Autorizado por</th>
                        <?php if ($isAdmin) : ?>
                          <th>Traslado</th> 
                        <?php endif; ?>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>

            </div><!-- card-body -->
          </div><!-- card -->
        </div>
      </div>
    </div>

    <!-- Modal Agregar Observaciones -->
    <div class="modal fade" id="modalCompromisoObservaciones" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoObservacionesLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Agregue una observación para el Compromiso</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding: 20px;">
            <div id="contenidoCompromiso"></div>

             <input type="hidden" id="idCompromisoGuardarObser" name="idCompromisoGuardarObser">
             <input type="hidden" id="municipioCodigo" name="municipioCodigo">
             <input type="hidden" id="secretariaIdObs" name="secretariaIdObs">
             <input type="hidden" id="estadoParaAprobar" name="estadoParaAprobar">

          <div class="form-group mt-2">
                <label for="tipo">Aprobar<span class="text-danger mb-1">*</span></label>
                <select class="form-control" id="aprobacion" name="aprobacion">

                    <option value="no">No</option>
                    <option value="si">Si</option>
                </select>
            </div>

            <div class="form-group mt-3">
              <label for="observacionCompromiso" class="form-label">Observación:</label>
              <textarea class="form-control" id="observacionCompromiso" name="observacionCompromiso" rows="4" placeholder="Ingrese aquí la observación para el compromiso..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarObservacion">Guardar Observación</button>
          </div>
        </div>
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
          <div class="modal-body text-center" id="contenidoAdjunto" style="padding: 20px;">
          </div>
        </div>
      </div>
    </div>


  <div class="modal fade" id="modalTrasladoCompetencia" tabindex="-1" role="dialog" aria-labelledby="modalTrasladoCompetenciaLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header bg-warning text-white">
                  <h5 class="modal-title" id="modalTrasladoCompetenciaLabel">Traslado por Competencia</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body" style="padding: 20px;">
                  <h6 class="mb-3 text-danger">Compromiso a Trasladar: <span id="nombreCompromisoTraslado" class="font-weight-bold"></span></h6>
                  <p><strong>Usuario realizando el traslado:</strong> <?= SessionData::getUserFullName() ?></p>
                  <p><strong>Secretaría Actual:</strong> <span id="secretariaInicialTraslado"></span></p>

                  <div id="logCompromisoOriginal" class="alert alert-info py-2 my-2 small">
                      <p class="m-0">
                          <strong>Creado originalmente por:</strong> 
                          <span id="usuarioCreadorOriginal"></span>
                      </p>
                      <p class="m-0">
                          <strong>Registrado el:</strong> 
                          <span id="fechaCreacionOriginal"></span>
                      </p>
                  </div>

                  
                  <hr>

                  <div id="contenedor-secretarias-destino">
                      </div>
                  
                  <button type="button" class="btn btn-sm btn-success mt-3" id="btnAddSecretaria">
                      <i class="feather icon-plus"></i> Añadir Secretaría Destino
                  </button>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-warning text-white" id="btnEjecutarTraslado">Ejecutar Traslado(s)</button>
              </div>
          </div>
      </div>
  </div>

    <!-- [ Footer Content ] start -->
    <?php include 'admin/include/footer.php'; ?>
    <!-- [ Footer Content ] end -->

    <!-- [ Main Content ] end -->
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script>
    const IS_ADMIN_USER = <?= $isAdmin ? 'true' : 'false'; ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="text/javascript" src="<?php echo Util::versionar('./admin/js/control-municipio-aprobacion-alcalde.js'); ?>"></script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />


</body>

</html>