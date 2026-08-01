<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Identificar tipo de usuario
$userType = SessionData::getUserType();
$tiposUsuarioMunicipal = ['Alcalde', 'Auxiliar_Alcalde', 'Secretario_Despacho', 'Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);
$municipioUsuario = $isUsuarioMunicipal ? SessionData::getCodigoMunicipio() : '';

$optionComponentes = '<option value="" selected>Todas</option>';
if (!empty($municipioUsuario)) {
    include_once './admin/classes/ComponenteMunicipios.php';
    $compArr = ComponenteMunicipios::getComponentesPorMunicipio($municipioUsuario)['output']['response'] ?? [];
    foreach ($compArr as $c) {
        $name = is_string($c) ? $c : ($c['nombre_componente'] ?? '');
        if (!empty($name)) {
            $optionComponentes .= '<option value="' . htmlspecialchars($name) . '">' . htmlspecialchars($name) . '</option>';
        }
    }
 }

$nombreMunicipioUsuario = '';
if (!empty($municipioUsuario)) {
    $db = new DbConection();
    $pdo = $db->openConect();
    $stmt = $pdo->prepare(
        "SELECT municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') .
        " WHERE CAST(codigo_muncipio AS CHAR) = :codigo LIMIT 1"
    );
    $stmt->execute([':codigo' => (string)$municipioUsuario]);
    $rowMunicipio = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombreMunicipioUsuario = $rowMunicipio ? (string)$rowMunicipio['municipio'] : '';
    $db->closeConect();
}
$municipioVisible = $nombreMunicipioUsuario !== ''
    ? $nombreMunicipioUsuario
    : (!empty($municipioUsuario) ? 'Municipio ' . $municipioUsuario : 'Cobertura general');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  
  <link rel="stylesheet" href="assets/css/control_compromisos_alcalde_gob360_premium.css">

</head>

<body class="dashboard-body gob360-commitment-control-page">
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

      <section class="g360-commitment-hero">
        <div class="g360-commitment-hero__grid">
          <aside class="g360-commitment-brand">
            <span class="g360-commitment-brand__eyebrow">Plataforma institucional</span>
            <img src="assets/img/gob360l.png" alt="Logo GOB360" class="g360-commitment-brand__logo">
            <span class="g360-commitment-brand__caption">Gestión pública inteligente y territorial</span>
            <div class="g360-commitment-brand__status"><span></span> Seguimiento territorial activo</div>
          </aside>

          <div class="g360-commitment-hero__content">
            <div class="g360-commitment-hero__top">
              <div>
                <div class="g360-commitment-hero__eyebrow"><i class="feather icon-check-square"></i> Gestión y seguimiento municipal</div>
                <h1 class="g360-commitment-hero__title">Compromisos del Alcalde</h1>
                <p class="g360-commitment-hero__description">Consulta compromisos, responsables, estados, municipios, veredas, componentes y evidencias desde un único centro de control territorial.</p>
              </div>
              <div class="g360-commitment-hero__actions">
                <button type="button" class="g360-hero-button" onclick="cargarCompromiso()"><i class="feather icon-refresh-cw"></i> Actualizar compromisos</button>
                <div class="g360-commitment-back"><?php include './admin/include/btn_back.php'; ?></div>
              </div>
            </div>

            <div class="g360-commitment-summary">
              <article><span class="g360-summary-icon"><i class="feather icon-map-pin"></i></span><div><small>Municipio activo</small><strong><?= htmlspecialchars($municipioVisible, ENT_QUOTES, 'UTF-8') ?></strong><p>Territorio asociado al usuario</p></div></article>
              <article><span class="g360-summary-icon g360-summary-icon--green"><i class="feather icon-filter"></i></span><div><small>Filtros disponibles</small><strong>5 criterios</strong><p>Dependencia, territorio y estado</p></div></article>
              <article><span class="g360-summary-icon g360-summary-icon--yellow"><i class="feather icon-bar-chart-2"></i></span><div><small>Analítica</small><strong>Por secretaría</strong><p>Indicadores de cumplimiento</p></div></article>
              <article><span class="g360-summary-icon g360-summary-icon--purple"><i class="feather icon-image"></i></span><div><small>Trazabilidad</small><strong>Evidencias</strong><p>Respuestas e imágenes</p></div></article>
            </div>

            <div class="g360-commitment-capabilities">
              <span><i class="feather icon-briefcase"></i> Secretarías</span>
              <span><i class="feather icon-map"></i> Municipio y vereda</span>
              <span><i class="feather icon-grid"></i> Componentes</span>
              <span><i class="feather icon-activity"></i> Estados</span>
              <span><i class="feather icon-pie-chart"></i> Indicadores</span>
            </div>
          </div>
        </div>
      </section>

      <!-- [ Main Content ] start -->
      <div class="row">
        <div class="col-sm-12">
          <div class="card au-card g360-commitment-card">

            <div class="card-header">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon"><i class="feather icon-list"></i></span>
                <div><small>Matriz de seguimiento municipal</small><h5>Listado de compromisos del alcalde</h5><p>Consulta responsables, estados, territorios y evidencias asociadas.</p></div>
              </div>
              <div class="g360-card-header-actions">
                <span class="g360-live-status"><span></span> Información disponible</span>
                <div class="card-header-right"><div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="feather icon-more-horizontal"></i></button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span></a></li>
                    <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> Expandir</span></a></li>
                    <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                    <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Eliminar</a></li>
                  </ul>
                </div></div>
              </div>
            </div>
            <div class="card-body">

              <ul class="nav nav-tabs g360-commitment-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
                    role="tab" aria-controls="home" aria-selected="true" onclick="cargarCompromiso()"><span class="g360-tab-icon"><i class="feather icon-check-square"></i></span><span><small>Seguimiento territorial</small><strong>Compromisos</strong></span></button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
                    role="tab" aria-controls="profile" aria-selected="false" onclick="indicadores()"><span class="g360-tab-icon"><i class="feather icon-bar-chart-2"></i></span><span><small>Análisis consolidado</small><strong>Indicadores por secretaría</strong></span></button>
                </li>
              </ul>

              <div class="tab-content" id="myTabContent">

                <!-- TAB 1 -->
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                  <!-- filtros PRO -->
                  <section class="filters-panel g360-filter-section">
                    <div class="g360-filter-heading">
                      <span class="g360-filter-heading__icon"><i class="feather icon-filter"></i></span>
                      <div><small>Consulta avanzada</small><h6>Filtros de compromisos</h6><p>Combina uno o varios criterios para reducir los resultados.</p></div>
                      <button type="button" class="g360-filter-button" onclick="filtrarTabla()"><i class="feather icon-search"></i> Aplicar filtros</button>
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
                        <select name="municipioFiltro" id="municipioFiltro" class="form-control" onchange="filtrarTabla()">
                        </select>
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
                          <?php echo $optionComponentes; ?>
                        </select>
                      </div>

                      <div class="col-12 col-md-6 col-xl-4">
                        <label for="estadoFiltro">Estado</label>
                        <select class="form-control" id="estadoFiltro" name="estadoFiltro" onchange="filtrarTabla()">
                          <option value="" selected>Todos</option>
                          <option value="Cumplido">Cumplido</option>
                          <option value="En Trámite">En Trámite</option>
                          <option value="Sin Cumplir">Sin Cumplir</option>
                          <option value="EN ESPERA">En Espera</option>
                        </select>
                      </div>
                      <div class="col-12 col-md-6 col-xl-4">
                        <label for="customSearch">Búsqueda rápida</label>
                        <input type="search" id="customSearch" class="form-control" placeholder="Buscar compromiso, respuesta o territorio...">
                      </div>

                    </div>
                  </section>

                  <!-- Tabla -->
                  <div class="tabla-shell g360-commitment-table">
                    <div class="table-responsive tabla-informacion tabla-scroll">
                      <table class="table table-hover mb-0" id="dynamictable" aria-label="Listado de compromisos del alcalde">
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
                  <div class="card au-subcard mt-3 g360-indicator-card">
                    <div class="card-header">
                      <div class="g360-card-heading"><span class="g360-card-heading__icon g360-card-heading__icon--indicator"><i class="feather icon-pie-chart"></i></span><div><small>Analítica institucional</small><h5>Indicadores por secretaría</h5><p>Distribución según la dependencia responsable.</p></div></div>
                      <span class="g360-live-status"><span></span> Gráfico dinámico</span>
                    </div>
                    <div class="card-body">
                      <div class="g360-indicator-intro"><span><i class="feather icon-activity"></i></span><div><strong>Seguimiento institucional consolidado</strong><p>La visualización utiliza la información disponible para la sesión activa.</p></div></div>
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
      <div class="modal-content g360-commitment-modal">
        <div class="modal-header">
          <div class="g360-modal-heading"><span><i class="feather icon-file-text"></i></span><div><small>Información del registro</small><h5 class="modal-title" id="modalCompromisoLabel">Detalle del compromiso</h5></div></div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body"><div class="g360-commitment-content"><i class="feather icon-align-left"></i><p id="contenidoCompromiso"></p></div></div>
      </div>
    </div>
  </div>

  <!-- Modal para adjuntos -->
  <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="modalAdjuntoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content g360-commitment-modal">
        <div class="modal-header">
          <div class="g360-modal-heading"><span class="g360-modal-heading__attachment"><i class="feather icon-image"></i></span><div><small>Evidencia del compromiso</small><h5 class="modal-title" id="modalAdjuntoLabel">Visualización del adjunto</h5></div></div>
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
  <script>
    window.munNombre = <?php echo json_encode($nombreMunicipioUsuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  </script>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="text/javascript" src="admin/js/control-compromisos-alcalde.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const input = document.getElementById('customSearch');
      if (!input) return;
      input.addEventListener('input', function () {
        const value = this.value || '';
        if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#dynamictable')) {
          $('#dynamictable').DataTable().search(value).draw();
          return;
        }
        const nativeInput = document.querySelector('.dataTables_filter input');
        if (nativeInput) {
          nativeInput.value = value;
          nativeInput.dispatchEvent(new Event('keyup', { bubbles: true }));
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
