<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

$userCodigoMunicipio = SessionData::getCodigoMunicipio();
$userNombreMunicipio = '';

if (!empty($userCodigoMunicipio)) {
    $db = new DbConection();
    $pdo = $db->openConect();

    $stmt = $pdo->prepare(
        "SELECT municipio
         FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
         WHERE CAST(codigo_muncipio AS CHAR) = :c
         LIMIT 1"
    );

    $stmt->execute([
        ':c' => (string) $userCodigoMunicipio,
    ]);

    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    $userNombreMunicipio = $res
        ? (string) $res['municipio']
        : '';

    $db->closeConect();
}

$municipioVisible = $userNombreMunicipio !== ''
    ? $userNombreMunicipio
    : (
        !empty($userCodigoMunicipio)
            ? 'Municipio ' . $userCodigoMunicipio
            : 'Sin municipio asignado'
    );
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/control_visitas_alcalde_gob360_premium.css">

</head>

<body class="dashboard-body gob360-visit-control-page">
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

      <section class="g360-control-hero" aria-label="Control de visitas del alcalde GOB360">
        <div class="g360-control-hero__grid">

          <aside class="g360-control-brand">
            <span class="g360-control-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-control-brand__logo"
            >

            <span class="g360-control-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-control-brand__status">
              <span></span>
              Seguimiento territorial activo
            </div>
          </aside>

          <div class="g360-control-hero__content">
            <div class="g360-control-hero__top">
              <div>
                <div class="g360-control-hero__eyebrow">
                  <i class="feather icon-map"></i>
                  Control territorial municipal
                </div>

                <h1 class="g360-control-hero__title">
                  Visitas del Alcalde
                </h1>

                <p class="g360-control-hero__description">
                  Consulta las visitas realizadas en el territorio, revisa
                  compromisos, evidencias, municipios y veredas, y analiza
                  los indicadores consolidados por tipo de visita.
                </p>
              </div>

              <div class="g360-control-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--primary"
                  onclick="cargaData()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar visitas
                </button>

                <div class="g360-control-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-control-summary">
              <article>
                <span class="g360-control-summary__icon">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Municipio activo</small>
                  <strong>
                    <?= htmlspecialchars($municipioVisible, ENT_QUOTES, 'UTF-8') ?>
                  </strong>
                  <p>Territorio asociado a la sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-control-summary__icon g360-control-summary__icon--visits">
                  <i class="feather icon-navigation"></i>
                </span>

                <div>
                  <small>Consulta principal</small>
                  <strong>Visitas</strong>
                  <p>Listado, detalle y seguimiento</p>
                </div>
              </article>

              <article>
                <span class="g360-control-summary__icon g360-control-summary__icon--indicators">
                  <i class="feather icon-bar-chart-2"></i>
                </span>

                <div>
                  <small>Analítica</small>
                  <strong>Indicadores</strong>
                  <p>Distribución por tipo de visita</p>
                </div>
              </article>

              <article>
                <span class="g360-control-summary__icon g360-control-summary__icon--evidence">
                  <i class="feather icon-image"></i>
                </span>

                <div>
                  <small>Soportes</small>
                  <strong>Evidencias</strong>
                  <p>Consulta de imágenes y adjuntos</p>
                </div>
              </article>
            </div>

            <div class="g360-control-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-search"></i>
                Búsqueda territorial
              </span>

              <span>
                <i class="feather icon-file-text"></i>
                Detalle de compromisos
              </span>

              <span>
                <i class="feather icon-edit-3"></i>
                Edición de registros
              </span>

              <span>
                <i class="feather icon-eye"></i>
                Visualización detallada
              </span>

              <span>
                <i class="feather icon-pie-chart"></i>
                Indicadores dinámicos
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-12">
          <div class="card au-card g360-control-card">

            <div class="card-header">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-list"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">
                    Centro de seguimiento territorial
                  </span>

                  <h5 class="title">
                    Control de visitas del alcalde
                  </h5>

                  <p>
                    Consulta registros, compromisos, evidencias e indicadores
                    dentro del municipio asignado.
                  </p>
                </div>
              </div>

              <div class="g360-card-header-actions">
                <span class="g360-control-live">
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

              <nav class="g360-control-tabs" aria-label="Vistas del control de visitas">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button
                      class="nav-link active"
                      id="home-tab"
                      data-toggle="tab"
                      data-target="#home"
                      type="button"
                      role="tab"
                      aria-controls="home"
                      aria-selected="true"
                      onclick="cargaData()"
                    >
                      <span class="g360-tab-icon">
                        <i class="feather icon-list"></i>
                      </span>

                      <span>
                        <small>Consulta territorial</small>
                        <strong>Listado de visitas</strong>
                      </span>
                    </button>
                  </li>

                  <li class="nav-item" role="presentation">
                    <button
                      class="nav-link"
                      id="profile-tab"
                      data-toggle="tab"
                      data-target="#profile"
                      type="button"
                      role="tab"
                      aria-controls="profile"
                      aria-selected="false"
                      onclick="indicadores()"
                    >
                      <span class="g360-tab-icon">
                        <i class="feather icon-bar-chart-2"></i>
                      </span>

                      <span>
                        <small>Análisis consolidado</small>
                        <strong>Indicadores por tipo</strong>
                      </span>
                    </button>
                  </li>
                </ul>
              </nav>

              <div class="tab-content" id="myTabContent">

                <div
                  class="tab-pane fade show active"
                  id="home"
                  role="tabpanel"
                  aria-labelledby="home-tab"
                >
                  <section class="g360-control-tools" aria-label="Herramientas de consulta">
                    <div class="g360-control-search">
                      <span class="g360-control-search__icon">
                        <i class="feather icon-search"></i>
                      </span>

                      <div>
                        <label for="customSearch">Búsqueda rápida</label>

                        <input
                          type="search"
                          id="customSearch"
                          class="form-control"
                          placeholder="Buscar municipio, vereda, tipo de visita o fecha..."
                        >
                      </div>
                    </div>

                    <div class="g360-control-tools__message">
                      <i class="feather icon-info"></i>

                      <span>
                        Utiliza la tabla para consultar detalles, editar registros
                        o visualizar las evidencias asociadas.
                      </span>
                    </div>
                  </section>

                  <div class="table-responsive tabla-informacion tabla-scroll g360-control-table">
                    <table
                      class="table table-hover mb-0"
                      id="dynamictable"
                      aria-label="Listado de visitas del alcalde"
                    >
                      <thead>
                        <tr class="border-1">
                          <th>Detalles</th>
                          <th>Tipo de visita</th>
                          <th>Descripción del hecho</th>
                          <th>Consecuencia</th>
                          <th>Vereda</th>
                          <th>Municipio</th>
                          <th>Imagen</th>
                          <th>Fecha</th>
                          <th>Editar</th>
                          <th>Ver</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>

                <div
                  class="tab-pane fade"
                  id="profile"
                  role="tabpanel"
                  aria-labelledby="profile-tab"
                >
                  <div class="card au-ind-card g360-indicator-card">
                    <div class="card-header">
                      <div class="g360-card-heading">
                        <span class="g360-card-heading__icon g360-card-heading__icon--indicator">
                          <i class="feather icon-pie-chart"></i>
                        </span>

                        <div>
                          <span class="g360-card-heading__eyebrow">
                            Analítica territorial
                          </span>

                          <h5 class="mb-0">
                            Indicadores por tipo de visita
                          </h5>

                          <p>
                            Distribución consolidada de las actividades registradas.
                          </p>
                        </div>
                      </div>

                      <span class="g360-control-live">
                        <span></span>
                        Gráfico dinámico
                      </span>
                    </div>

                    <div class="card-body">
                      <div class="g360-indicator-intro">
                        <span class="g360-indicator-intro__icon">
                          <i class="feather icon-activity"></i>
                        </span>

                        <div>
                          <strong>Comportamiento de las visitas</strong>

                          <p>
                            La visualización se genera con los datos correspondientes
                            al municipio de la sesión.
                          </p>
                        </div>
                      </div>

                      <div id="indicadoresContainer"></div>
                    </div>
                  </div>
                </div>

              </div>

            </div>
          </div>

          <!-- Modal compromiso -->
          <div class="modal fade" id="modalCompromiso" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content g360-control-modal">
                <div class="modal-header">
                  <div class="g360-modal-heading">
                    <span class="g360-modal-heading__icon">
                      <i class="feather icon-file-text"></i>
                    </span>

                    <div>
                      <small>Información del registro</small>
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
                  <div class="g360-commitment-content">
                    <span>
                      <i class="feather icon-align-left"></i>
                    </span>

                    <p id="contenidoCompromiso"></p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal para adjuntos -->
          <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="modalAdjuntoLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
              <div class="modal-content g360-control-modal g360-control-modal--attachment">
                <div class="modal-header">
                  <div class="g360-modal-heading">
                    <span class="g360-modal-heading__icon g360-modal-heading__icon--attachment">
                      <i class="feather icon-image"></i>
                    </span>

                    <div>
                      <small>Evidencia del registro</small>
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

        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script>
    window.userMunicipioCodigo = '<?php echo $userCodigoMunicipio; ?>';
    window.userMunicipioNombre = '<?php echo $userNombreMunicipio; ?>';
  </script>
  <script type="text/javascript" src="admin/js/control-visitas-alcalde.js"></script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />

</body>
</html>
