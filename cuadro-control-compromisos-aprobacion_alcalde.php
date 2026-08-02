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

$perfilAprobacion = $isAdmin
    ? 'Administrador'
    : 'Alcaldía municipal';

$trasladoDisponible = $isAdmin
    ? 'Disponible'
    : 'Restringido';
?>

<body class="dashboard-body gob360-approval-control-page">
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
  
  <link rel="stylesheet" href="assets/css/aprobacion_compromisos_alcalde_gob360_premium.css">


  <div class="pcoded-main-container">
    <div class="pcoded-content">
      <section class="g360-approval-hero" aria-label="Aprobación de compromisos del alcalde GOB360">
        <div class="g360-approval-hero__grid">

          <aside class="g360-approval-brand">
            <span class="g360-approval-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-approval-brand__logo"
            >

            <span class="g360-approval-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-approval-brand__status">
              <span></span>
              Flujo de validación activo
            </div>
          </aside>

          <div class="g360-approval-hero__content">
            <div class="g360-approval-hero__top">
              <div>
                <div class="g360-approval-hero__eyebrow">
                  <i class="feather icon-shield"></i>
                  Control y aprobación municipal
                </div>

                <h1 class="g360-approval-hero__title">
                  Aprobación de Compromisos
                </h1>

                <p class="g360-approval-hero__description">
                  Revisa los compromisos que se encuentran en espera,
                  consulta sus respuestas y territorios, registra observaciones,
                  aprueba su avance o realiza traslados por competencia.
                </p>
              </div>

              <div class="g360-approval-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button"
                  onclick="filtrarTablaEnEspera()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar pendientes
                </button>

                <div class="g360-approval-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-approval-summary">
              <article>
                <span class="g360-approval-summary__icon">
                  <i class="feather icon-user-check"></i>
                </span>

                <div>
                  <small>Perfil de validación</small>
                  <strong>
                    <?= htmlspecialchars($perfilAprobacion, ENT_QUOTES, 'UTF-8') ?>
                  </strong>
                  <p>Rol autorizado para revisar compromisos</p>
                </div>
              </article>

              <article>
                <span class="g360-approval-summary__icon g360-approval-summary__icon--waiting">
                  <i class="feather icon-clock"></i>
                </span>

                <div>
                  <small>Estado consultado</small>
                  <strong>En espera</strong>
                  <p>Registros pendientes de decisión</p>
                </div>
              </article>

              <article>
                <span class="g360-approval-summary__icon g360-approval-summary__icon--approval">
                  <i class="feather icon-check-square"></i>
                </span>

                <div>
                  <small>Acción principal</small>
                  <strong>Aprobar</strong>
                  <p>Validación con observación institucional</p>
                </div>
              </article>

              <article>
                <span class="g360-approval-summary__icon g360-approval-summary__icon--transfer">
                  <i class="feather icon-share-2"></i>
                </span>

                <div>
                  <small>Traslado por competencia</small>
                  <strong>
                    <?= htmlspecialchars($trasladoDisponible, ENT_QUOTES, 'UTF-8') ?>
                  </strong>
                  <p>Función disponible para administradores</p>
                </div>
              </article>
            </div>

            <div class="g360-approval-capabilities" aria-hidden="true">
              <span><i class="feather icon-filter"></i> Filtros territoriales</span>
              <span><i class="feather icon-message-square"></i> Observaciones</span>
              <span><i class="feather icon-check-circle"></i> Aprobación</span>
              <span><i class="feather icon-eye"></i> Consulta detallada</span>
              <span><i class="feather icon-repeat"></i> Traslado por competencia</span>
            </div>
          </div>

        </div>
      </section>

      <!-- [ Main Content ] start -->
      <div class="row">
        <div class="col-sm-12">
          <div class="card au-card g360-approval-card">

            <div class="card-header">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-clipboard"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">
                    Bandeja de validación
                  </span>

                  <h5 class="mb-0">
                    Compromisos del alcalde en espera
                  </h5>

                  <p>
                    Filtra, revisa y valida cada compromiso antes de continuar
                    con el flujo institucional.
                  </p>
                </div>
              </div>

              <div class="g360-card-header-actions">
                <span class="g360-live-status">
                  <span></span>
                  Pendientes disponibles
                </span>

                <div class="card-header-right">
                  <div class="btn-group card-option">
                    <button
                      type="button"
                      class="btn dropdown-toggle btn-icon"
                      data-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
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
                        <a href="#!">
                          <i class="feather icon-refresh-cw"></i>
                          Recargar
                        </a>
                      </li>

                      <li class="dropdown-item close-card">
                        <a href="#!">
                          <i class="feather icon-trash"></i>
                          Eliminar
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-body">

              <!-- filtros -->
              <section class="filters-panel g360-filter-section">
                <div class="g360-filter-heading">
                  <span class="g360-filter-heading__icon">
                    <i class="feather icon-filter"></i>
                  </span>

                  <div>
                    <small>Consulta avanzada</small>
                    <h6>Filtros de compromisos en espera</h6>
                    <p>
                      Combina secretaría, municipio, vereda y componente
                      para localizar registros pendientes.
                    </p>
                  </div>

                  <button
                    type="button"
                    class="g360-filter-button"
                    onclick="filtrarTablaEnEspera()"
                  >
                    <i class="feather icon-search"></i>
                    Aplicar filtros
                  </button>
                </div>

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


                  <div class="col-12 col-md-6 col-xl-4">
                    <label for="customSearch">Búsqueda rápida</label>
                    <input
                      type="search"
                      id="customSearch"
                      class="form-control"
                      placeholder="Buscar compromiso, respuesta o territorio..."
                    >
                  </div>

                </div>
              </section>

              

              <!-- Tabla -->
              <div class="tabla-shell g360-approval-table">
                <div class="table-responsive tabla-informacion tabla-scroll">
                  <table class="table table-hover mb-0" id="dynamictable" aria-label="Compromisos pendientes de aprobación">
                    <thead>
                      <tr class="border-1">
                        <th>Ítem</th>
                        <th>Secretaría</th>
                        <th>Compromiso</th>
                        <th>Consecuencia</th>
                        <th>Respuesta</th>
                        <th>Estado</th>
                        <th>Municipio</th>
                        <th>Vereda</th>
                        <th>Componente</th>
                        <th>Tipo de ejecución</th>
                        <th>Fecha</th>
                        <th>Aprobar</th>
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
        <div class="modal-content g360-approval-modal g360-approval-modal--decision">
          <div class="modal-header">
            <div class="g360-modal-heading">
              <span>
                <i class="feather icon-check-square"></i>
              </span>

              <div>
                <small>Decisión institucional</small>
                <h5 class="modal-title" id="modalCompromisoObservacionesLabel">
                  Aprobar compromiso
                </h5>
              </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="g360-modal-intro">
              <i class="feather icon-info"></i>
              <span>
                Revisa la información del compromiso y registra la decisión
                junto con la observación correspondiente.
              </span>
            </div>

            <div id="contenidoCompromiso" class="g360-modal-record"></div>

             <input type="hidden" id="idCompromisoGuardarObser" name="idCompromisoGuardarObser">
             <input type="hidden" id="municipioCodigo" name="municipioCodigo">
             <input type="hidden" id="secretariaIdObs" name="secretariaIdObs">
             <input type="hidden" id="estadoParaAprobar" name="estadoParaAprobar">

          <div class="form-group mt-2">
                <label for="aprobacion">Decisión de aprobación <span class="text-danger">*</span></label>
                <select class="form-control" id="aprobacion" name="aprobacion">

                    <option value="no">No aprobar</option>
                    <option value="si">Aprobar</option>
                </select>
            </div>

            <div class="form-group mt-3">
              <label for="observacionCompromiso" class="form-label">Observación institucional</label>
              <textarea class="form-control" id="observacionCompromiso" name="observacionCompromiso" rows="4" placeholder="Ingrese aquí la observación para el compromiso..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarObservacion">
              <i class="feather icon-save"></i>
              Guardar decisión
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal compromiso -->
    <div class="modal fade" id="modalCompromiso" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content g360-approval-modal">
          <div class="modal-header">
            <div class="g360-modal-heading">
              <span>
                <i class="feather icon-file-text"></i>
              </span>

              <div>
                <small>Consulta del registro</small>
                <h5 class="modal-title" id="modalCompromisoLabel">
                  Detalle del compromiso
                </h5>
              </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="g360-detail-content">
              <i class="feather icon-align-left"></i>
              <p id="contenidoCompromiso"></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para adjuntos -->
    <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="modalAdjuntoLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content g360-approval-modal">
          <div class="modal-header">
            <div class="g360-modal-heading">
              <span class="g360-modal-heading__attachment">
                <i class="feather icon-image"></i>
              </span>

              <div>
                <small>Evidencia del compromiso</small>
                <h5 class="modal-title" id="modalAdjuntoLabel">
                  Visualización del adjunto
                </h5>
              </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center" id="contenidoAdjunto">
          </div>
        </div>
      </div>
    </div>


  <div class="modal fade" id="modalTrasladoCompetencia" tabindex="-1" role="dialog" aria-labelledby="modalTrasladoCompetenciaLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content g360-approval-modal g360-transfer-modal">
              <div class="modal-header">
                <div class="g360-modal-heading">
                  <span class="g360-modal-heading__transfer">
                    <i class="feather icon-repeat"></i>
                  </span>

                  <div>
                    <small>Asignación institucional</small>
                    <h5 class="modal-title" id="modalTrasladoCompetenciaLabel">
                      Traslado por competencia
                    </h5>
                  </div>
                </div>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <div class="g360-transfer-highlight">
                    <small>Compromiso a trasladar</small>
                    <strong id="nombreCompromisoTraslado"></strong>
                  </div>
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
                  
                  <button type="button" class="btn btn-sm btn-success mt-3 g360-add-secretariat" id="btnAddSecretaria">
                      <i class="feather icon-plus"></i> Añadir Secretaría Destino
                  </button>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-warning text-white" id="btnEjecutarTraslado">
                    <i class="feather icon-send"></i>
                    Ejecutar traslado(s)
                  </button>
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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const customSearch = document.getElementById('customSearch');

      if (!customSearch) {
        return;
      }

      customSearch.addEventListener('input', function () {
        const value = this.value || '';

        if (
          window.jQuery
          && $.fn.DataTable
          && $.fn.DataTable.isDataTable('#dynamictable')
        ) {
          $('#dynamictable').DataTable().search(value).draw();
          return;
        }

        const nativeSearch = document.querySelector(
          '.dataTables_filter input'
        );

        if (nativeSearch) {
          nativeSearch.value = value;
          nativeSearch.dispatchEvent(
            new Event('keyup', { bubbles: true })
          );
        }
      });
    });
  </script>


  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />


</body>

</html>