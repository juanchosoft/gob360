<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Identificar tipo de usuario
$userType = SessionData::getUserType();
$tiposUsuarioMunicipal = ['Alcalde', 'Auxiliar_Alcalde', 'Secretario_Despacho', 'Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);
$municipioUsuario = $isUsuarioMunicipal ? SessionData::getCodigoMunicipio() : '';

$municipioVisible = !empty($municipioUsuario)
    ? 'Municipio ' . $municipioUsuario
    : 'Cobertura general';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  
  <link rel="stylesheet" href="assets/css/control_compromisos_cumplidos_alcalde_gob360_premium.css">

</head>

<body class="dashboard-body gob360-completed-control-page">
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

      <section class="g360-completed-hero" aria-label="Compromisos cumplidos del alcalde GOB360">
        <div class="g360-completed-hero__grid">

          <aside class="g360-completed-brand">
            <span class="g360-completed-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-completed-brand__logo"
            >

            <span class="g360-completed-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-completed-brand__status">
              <span></span>
              Resultados municipales consolidados
            </div>
          </aside>

          <div class="g360-completed-hero__content">
            <div class="g360-completed-hero__top">
              <div>
                <div class="g360-completed-hero__eyebrow">
                  <i class="feather icon-check-circle"></i>
                  Seguimiento de resultados
                </div>

                <h1 class="g360-completed-hero__title">
                  Compromisos Cumplidos
                </h1>

                <p class="g360-completed-hero__description">
                  Consulta los compromisos finalizados por la administración
                  municipal, filtra los resultados por secretaría y territorio,
                  y revisa sus respuestas, componentes y evidencias.
                </p>
              </div>

              <div class="g360-completed-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button"
                  onclick="cargarCompromiso()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar resultados
                </button>

                <div class="g360-completed-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-completed-summary">
              <article>
                <span class="g360-completed-summary__icon">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Ámbito territorial</small>
                  <strong>
                    <?= htmlspecialchars($municipioVisible, ENT_QUOTES, 'UTF-8') ?>
                  </strong>
                  <p>Territorio disponible para el usuario</p>
                </div>
              </article>

              <article>
                <span class="g360-completed-summary__icon g360-completed-summary__icon--status">
                  <i class="feather icon-check-square"></i>
                </span>

                <div>
                  <small>Estado consultado</small>
                  <strong>Cumplidos</strong>
                  <p>Compromisos finalizados y registrados</p>
                </div>
              </article>

              <article>
                <span class="g360-completed-summary__icon g360-completed-summary__icon--filters">
                  <i class="feather icon-filter"></i>
                </span>

                <div>
                  <small>Filtros</small>
                  <strong>4 criterios</strong>
                  <p>Secretaría, municipio, vereda y componente</p>
                </div>
              </article>

              <article>
                <span class="g360-completed-summary__icon g360-completed-summary__icon--analytics">
                  <i class="feather icon-bar-chart-2"></i>
                </span>

                <div>
                  <small>Analítica</small>
                  <strong>Por secretaría</strong>
                  <p>Indicadores institucionales dinámicos</p>
                </div>
              </article>
            </div>

            <div class="g360-completed-capabilities" aria-hidden="true">
              <span><i class="feather icon-briefcase"></i> Secretarías</span>
              <span><i class="feather icon-map"></i> Municipio y vereda</span>
              <span><i class="feather icon-grid"></i> Componentes</span>
              <span><i class="feather icon-message-square"></i> Respuestas</span>
              <span><i class="feather icon-image"></i> Evidencias</span>
            </div>
          </div>

        </div>
      </section>

      <!-- [ Main Content ] start -->
      <div class="row">
        <div class="col-sm-12">
          <div class="card au-card g360-completed-card">

            <div class="card-header">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-award"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">
                    Resultados alcanzados
                  </span>

                  <h5 class="mb-0">
                    Listado de compromisos cumplidos
                  </h5>

                  <p>
                    Consulta resultados, responsables, territorios,
                    respuestas y evidencias de cumplimiento.
                  </p>
                </div>
              </div>

              <div class="g360-card-header-actions">
                <span class="g360-live-status">
                  <span></span>
                  Información disponible
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
              <ul class="nav nav-tabs g360-completed-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
                    role="tab" aria-controls="home" aria-selected="true" onclick="cargarCompromiso()">
                    <span class="g360-tab-icon">
                      <i class="feather icon-check-circle"></i>
                    </span>
                    <span>
                      <small>Resultados registrados</small>
                      <strong>Compromisos cumplidos</strong>
                    </span>
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
                    role="tab" aria-controls="profile" aria-selected="false" onclick="indicadores()">
                    <span class="g360-tab-icon">
                      <i class="feather icon-bar-chart-2"></i>
                    </span>
                    <span>
                      <small>Análisis institucional</small>
                      <strong>Indicadores por secretaría</strong>
                    </span>
                  </button>
                </li>
              </ul>

              <div class="tab-content" id="myTabContent">

                <!-- TAB 1 -->
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                  <section class="filters-panel g360-filter-section">
                    <div class="g360-filter-heading">
                      <span class="g360-filter-heading__icon">
                        <i class="feather icon-filter"></i>
                      </span>

                      <div>
                        <small>Consulta avanzada</small>
                        <h6>Filtros de resultados cumplidos</h6>
                        <p>
                          Combina los criterios disponibles para localizar
                          rápidamente un compromiso.
                        </p>
                      </div>

                      <button
                        type="button"
                        class="g360-filter-button"
                        onclick="filtrarTabla()"
                      >
                        <i class="feather icon-search"></i>
                        Aplicar filtros
                      </button>
                    </div>

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

                  

                  <div class="tabla-shell g360-completed-table">
                    <div class="table-responsive tabla-informacion tabla-scroll">
                      <table class="table table-hover mb-0" id="dynamictable" aria-label="Listado de compromisos cumplidos del alcalde">
                        <thead>
                          <tr class="border-1">
                            <th>Ítem</th>
                            <th>Secretaría</th>
                            <th>Compromiso</th>
                            <th>Compromiso pactado</th>
                            <th>Consecuencia</th>
                            <th>Respuesta</th>
                            <th>Estado</th>
                            <th>Municipio</th>
                            <th>Vereda</th>
                            <th>Componente</th>
                            <th>Tipo de ejecución</th>
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
                  <div class="card au-subcard mt-3 g360-indicator-card">
                    <div class="card-header">
                      <div class="g360-card-heading">
                        <span class="g360-card-heading__icon g360-card-heading__icon--indicator">
                          <i class="feather icon-pie-chart"></i>
                        </span>

                        <div>
                          <span class="g360-card-heading__eyebrow">
                            Analítica de cumplimiento
                          </span>

                          <h5 class="mb-0">
                            Indicadores por secretaría
                          </h5>

                          <p>
                            Distribución de resultados cumplidos por dependencia.
                          </p>
                        </div>
                      </div>

                      <span class="g360-live-status">
                        <span></span>
                        Gráfico dinámico
                      </span>
                    </div>
                    <div class="card-body">
                      <div class="g360-indicator-intro">
                        <span>
                          <i class="feather icon-trending-up"></i>
                        </span>

                        <div>
                          <strong>Resultados consolidados por dependencia</strong>
                          <p>
                            La gráfica se construye con los compromisos
                            cumplidos disponibles para la sesión.
                          </p>
                        </div>
                      </div>

                      <div id="indicadoresContainer" class="text-center"></div>
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
      <div class="modal-content g360-completed-modal">
        <div class="modal-header">
          <div class="g360-modal-heading">
            <span>
              <i class="feather icon-file-text"></i>
            </span>

            <div>
              <small>Información del resultado</small>
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
      <div class="modal-content g360-completed-modal">
        <div class="modal-header">
          <div class="g360-modal-heading">
            <span class="g360-modal-heading__attachment">
              <i class="feather icon-image"></i>
            </span>

            <div>
              <small>Evidencia de cumplimiento</small>
              <h5 class="modal-title" id="modalAdjuntoLabel">
                Visualización del adjunto
              </h5>
            </div>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center" id="contenidoAdjunto"></div>
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
