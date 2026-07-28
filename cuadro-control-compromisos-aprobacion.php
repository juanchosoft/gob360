<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
if (!$isAdmin) {
  require 'permiso_denegado.php';
  exit;
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<body class="gob360-approval-control">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <link href="assets/css/aprobacion_compromisos_gob360.css" rel="stylesheet">


  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h5 class="m-b-10 mb-0">Aprobación cuadro control municipios</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Aprobación compromisos / Cuadro Control municipios</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- HERO VISUAL GOB360 -->
      <section class="g360-approval-hero" aria-label="Aprobación de compromisos GOB360">
        <div class="g360-approval-hero__grid">

          <div>
            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-approval-hero__logo"
            >
          </div>

          <div>
            <div class="g360-approval-hero__eyebrow">
              <i class="feather icon-shield"></i>
              Validación institucional
            </div>

            <h1 class="g360-approval-hero__title">
              Aprobación de compromisos
            </h1>

            <p class="g360-approval-hero__description">
              Revisa los compromisos en espera, agrega observaciones, aprueba su
              estado y realiza traslados por competencia con el flujo
              administrativo existente.
            </p>

            <div class="g360-approval-hero__chips">
              <span class="g360-chip g360-chip--success">
                <i class="feather icon-lock"></i>
                Acceso administrativo
              </span>

              <span class="g360-chip g360-chip--warning">
                <i class="feather icon-clock"></i>
                Estado en espera
              </span>

              <span class="g360-chip">
                <i class="feather icon-share-2"></i>
                Traslado por competencia
              </span>
            </div>
          </div>

          <div class="g360-approval-hero__visual" aria-hidden="true">
            <div class="g360-mini-card">
              <i class="feather icon-check-circle"></i>
              <span>Validar</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-message-square"></i>
              <span>Observar</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-share-2"></i>
              <span>Trasladar</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-eye"></i>
              <span>Consultar</span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-12">
          <div class="card saas-card g360-approval-card">

            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <h5>Compromisos en espera de aprobación</h5>
                <p>Validación administrativa, observaciones y traslado por competencia.</p>
              </div>

              <div class="small">
                <i class="feather icon-check-circle mr-1"></i>
                Validación / aprobación
              </div>
            </div>

            <div class="card-body">
              <div class="col-lg-12">

                <div class="filter-panel">
                  <div class="g360-filter-heading">
                    <div class="g360-filter-heading__title">
                      <span class="g360-filter-heading__icon">
                        <i class="feather icon-filter"></i>
                      </span>
                      Filtros de aprobación
                    </div>

                    <span class="g360-filter-heading__hint">
                      Los controles mantienen la consulta existente
                    </span>
                  </div>

                  <div class="row g-3">

                    <div class="col-12 col-md-3">
                      <label for="idFiltro">ID del Compromiso</label>
                      <input type="number" name="idFiltro" id="idFiltro" class="form-control"
                        placeholder="Buscar por ID" onkeyup="filtrarTablaEnEspera()">
                    </div>

                    <div class="col-12 col-md-3">
                      <label for="secretariaIdFiltro">Seleccionar Secretaría</label>
                      <select name="secretariaIdFiltro" id="secretariaIdFiltro" class="form-control"
                        onchange="filtrarTablaEnEspera()">
                        <option value="">Seleccione</option>
                      </select>
                      <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="68">
                    </div>

                    <div class="col-12 col-md-3">
                      <label for="municipioFiltro">Seleccionar Municipio</label>
                      <select name="municipioFiltro" id="municipioFiltro" class="form-control"
                        onchange="filtrarTablaEnEspera()"></select>
                    </div>

                    <div class="col-12 col-md-3">
                      <label for="componenteFiltro">Componente</label>
                      <select class="form-control" id="componenteFiltro" name="componenteFiltro"
                        onchange="filtrarTablaEnEspera()">
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

                <div class="card-body table-border-style px-0">
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
                          <th>Provincia</th>
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

              </div>
            </div>

          </div><!-- saas-card -->
        </div>
      </div>
    </div>

    <!-- Modal Agregar Observaciones -->
    <div class="modal fade" id="modalCompromisoObservaciones" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoObservacionesLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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
              <label for="aprobacion">Aprobar<span class="text-danger mb-1">*</span></label>
              <select class="form-control" id="aprobacion" name="aprobacion">
                <option value="no">No</option>
                <option value="si">Si</option>
              </select>
            </div>

            <div class="form-group mt-2">
              <label for="estado_modal">Estado<span class="text-danger mb-1">*</span></label>
              <select class="form-control" id="estado_modal" name="estado_modal">
                <option value="">Seleccione</option>
                <option value="Sin Cumplir">SIN CUMPLIR</option>
                <option value="Cumplido">CUMPLIDO</option>
                <option value="En Trámite">EN TRÁMITE</option>
              </select>
            </div>

            <div class="form-group mt-3">
              <label for="observacionCompromiso" class="form-label">Observación:</label>
              <textarea class="form-control" id="observacionCompromiso" name="observacionCompromiso"
                rows="4" placeholder="Ingrese aquí la observación para el compromiso..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              <i class="feather icon-x-circle mr-1"></i>
              Cancelar
            </button>

            <button type="button" class="btn btn-primary" id="btnGuardarObservacion">
              <i class="feather icon-save mr-1"></i>
              Guardar observación
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal compromiso -->
    <div class="modal fade" id="modalCompromiso" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalle del Compromiso</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding: 20px;">
            <p id="contenidoCompromiso" style="white-space: pre-wrap; margin:0;"></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para adjuntos -->
    <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="modalAdjuntoLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
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

    <!-- Modal Traslado Competencia -->
    <div class="modal fade" id="modalTrasladoCompetencia" tabindex="-1" role="dialog" aria-labelledby="modalTrasladoCompetenciaLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTrasladoCompetenciaLabel">Traslado por Competencia</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body" style="padding: 20px;">
            <h6 class="mb-3" style="color:#b91c1c;">
              Compromiso a Trasladar:
              <span id="nombreCompromisoTraslado" class="font-weight-bold"></span>
            </h6>

            <div class="p-3" style="border:1px solid rgba(15,23,42,.10); border-radius:16px; background:rgba(245,158,11,.06);">
              <p class="mb-1"><strong>Usuario realizando el traslado:</strong> <?= h(SessionData::getUserFullName()) ?></p>
              <p class="mb-1"><strong>Secretaría Actual:</strong> <span id="secretariaInicialTraslado"></span></p>
            </div>

            <div id="logCompromisoOriginal" class="alert alert-info py-2 my-3 small">
              <p class="m-0"><strong>Creado originalmente por:</strong> <span id="usuarioCreadorOriginal"></span></p>
              <p class="m-0"><strong>Registrado el:</strong> <span id="fechaCreacionOriginal"></span></p>
            </div>

            <hr>

            <div id="contenedor-secretarias-destino"></div>

            <button type="button" class="btn btn-sm btn-success mt-3" id="btnAddSecretaria">
              <i class="feather icon-plus"></i> Añadir Secretaría Destino
            </button>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              <i class="feather icon-x-circle mr-1"></i>
              Cancelar
            </button>

            <button type="button" class="btn btn-warning" id="btnEjecutarTraslado">
              <i class="feather icon-share-2 mr-1"></i>
              Ejecutar traslado(s)
            </button>
          </div>

        </div>
      </div>
    </div>

    <?php include 'admin/include/footer.php'; ?>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script>
    const IS_ADMIN_USER = <?= $isAdmin ? 'true' : 'false'; ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="text/javascript" src="admin/js/control-municipio-aprobacion.js"></script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
</body>
</html>
