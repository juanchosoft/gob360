<?php

include './admin/include/head.php';
require './admin/include/generic_classes.php';

function getUrl()
{
    $port = $_SERVER["SERVER_PORT"];
    $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
    $url = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $nameServer,
        $_SERVER['REQUEST_URI']
    );
    $final = str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists !== false) {
        $final = substr($final, 0, $exists);
    }
    return $final;
}

// ------------------------- MAPA 1: GestoraSocial -------------------------
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
include './admin/classes/Departamento.php';
include './admin/db/coloresg.php';
include './admin/classes/Maing.php';
include './admin/classes/Detalle.php';
include './admin/classes/Cuenta.php';
include './admin/classes/Cuentapro.php';
include './admin/classes/Secreinversion.php';
include './admin/classes/Munnovisitados.php';
include './admin/classes/GestoraSocial.php';
include './admin/classes/Colombia.php';

$permissions = PagePermissions::crudForCurrentPage();

/* if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
} */

// Obtener datos de GESTORA SOCIAL
$datosGestora = Maing::getDataMain(['modulo' => 'gestora']);
$validGestora = $datosGestora['output']['valid'];

$visitasGestora   = $validGestora ? intval($datosGestora['output']['visitas']) : 0;
$impactadaGestora = $validGestora ? intval($datosGestora['output']['impactada']) : 0;
$inversionGestora = $validGestora ? floatval($datosGestora['output']['inversion']) : 0;

// Obtener datos de ASPAS
$datosAspas = Maing::getDataMain(['modulo' => 'aspas']);
$validAspas = $datosAspas['output']['valid'];

$visitasAspas   = $validAspas ? intval($datosAspas['output']['visitas']) : 0;
$impactadaAspas = $validAspas ? intval($datosAspas['output']['impactada']) : 0;
$inversionAspas = $validAspas ? floatval($datosAspas['output']['inversion']) : 0;

// Sumar ambos
$visitas   = $visitasGestora + $visitasAspas;
$impactada = $impactadaGestora + $impactadaAspas;
$inversion = $inversionGestora + $inversionAspas;

$arrgestora = array('codigo' => Util::getDepartamentoPrincipal());

$datagestora = Colombia::getInformacionParaMapaGestoraSocial($arrgestora);
$isvalid = $datagestora['output']['valid'];
$santandergestora =  $datagestora['output']['response'];
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

<script src="https://cdn.datatables.net/select/2.0.0/js/select.bootstrap4.min.js"></script>

<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="assets/css/gestion_cumplimiento_gob360_final.css" rel="stylesheet">

<body class="gob360-compliance-dashboard">

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
      <div class="pcoded-wrapper">
          <div class="pcoded-content">
              <div class="pcoded-inner-content">
                  <div class="main-body">
                      <div class="page-wrapper">

                          <div class="page-header">
                              <div class="page-block">
                                  <div class="row align-items-center">
                                      <div class="col-md-12">
                                          <div class="d-flex justify-content-between align-items-center">
                                              <h5 class="m-b-10">Gestión Cumplimiento</h5>
                                              <?php include './admin/include/btn_back.php'; ?>
                                          </div>
                                          <ul class="breadcrumb">
                                              <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                              <li class="breadcrumb-item"><a href="#!">Mapa visitas</a></li>
                                              <li class="breadcrumb-item"><a href="#!">Gestión cumplimiento</a></li>
                                          </ul>
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <!-- HERO VISUAL GOB360 -->
                          <section class="g360-compliance-hero" aria-label="Gestión de cumplimiento GOB360">
                            <div class="g360-compliance-hero__grid">

                              <div>
                                <img
                                  src="assets/img/gob360l.png"
                                  alt="Logo GOB360"
                                  class="g360-compliance-hero__logo"
                                >
                              </div>

                              <div>
                                <div class="g360-compliance-hero__eyebrow">
                                  <i class="feather icon-trending-up"></i>
                                  Seguimiento territorial
                                </div>

                                <h1 class="g360-compliance-hero__title">
                                  Gestión de cumplimiento
                                </h1>

                                <p class="g360-compliance-hero__description">
                                  Analiza el estado de los compromisos institucionales mediante
                                  indicadores, mapa territorial, filtros dinámicos y gráfica por
                                  provincias, conservando la lógica actual del sistema.
                                </p>

                                <div class="g360-compliance-hero__chips">
                                  <span class="g360-chip g360-chip--success">
                                    <i class="feather icon-check-circle"></i>
                                    Seguimiento activo
                                  </span>

                                  <span class="g360-chip">
                                    <i class="feather icon-map-pin"></i>
                                    Santander
                                  </span>

                                  <span class="g360-chip">
                                    <i class="feather icon-bar-chart-2"></i>
                                    Indicadores dinámicos
                                  </span>
                                </div>
                              </div>

                              <div class="g360-compliance-hero__visual" aria-hidden="true">
                                <div class="g360-mini-card">
                                  <i class="feather icon-map"></i>
                                  <span>Mapa</span>
                                </div>

                                <div class="g360-mini-card">
                                  <i class="feather icon-check-square"></i>
                                  <span>Cumplimiento</span>
                                </div>

                                <div class="g360-mini-card">
                                  <i class="feather icon-filter"></i>
                                  <span>Filtros</span>
                                </div>

                                <div class="g360-mini-card">
                                  <i class="feather icon-pie-chart"></i>
                                  <span>Gráficas</span>
                                </div>
                              </div>

                            </div>
                          </section>

                          <div class="card saas-card g360-compliance-card">
                              <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                                  <div>
                                    <h5>Panel territorial de cumplimiento</h5>
                                    <p>Indicadores, mapa, filtros y análisis por provincia.</p>
                                  </div>

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

                              <div class="card-body">
                                  <div class="row g-3">

                                      <!-- IZQUIERDA KPIs -->
                                      <div class="col-12 col-lg-3">
                                          <div class="sticky-panel">

                                              <div class="kpi-card p-3 mb-3" style="cursor:pointer" onclick="filtrarPorEstado('todos')">
                                                  <div class="d-flex align-items-center justify-content-between">
                                                      <span class="kpi-chip"><span class="kpi-dot"></span> Total</span>
                                                      <i class="feather icon-grid" style="font-size:18px;color:rgba(15,23,42,.55)"></i>
                                                  </div>
                                                  <div class="kpi-num mt-2" id="total-compromisos">0</div>
                                                  <div class="kpi-label">Compromisos</div>
                                              </div>

                                              <div class="kpi-card p-3 mb-3" style="cursor:pointer" onclick="filtrarPorEstado('Cumplido')">
                                                  <div class="d-flex align-items-center justify-content-between">
                                                      <span class="kpi-chip"><span class="kpi-dot success"></span> Cumplidos</span>
                                                      <i class="feather icon-check-circle" style="font-size:18px;color:#22c55e"></i>
                                                  </div>
                                                  <div class="kpi-num mt-2" id="compromisos-cumplidos">0</div>
                                                  <div class="kpi-label" id="porcentaje-cumplidos">Cumplidos (0%)</div>
                                              </div>

                                              <div class="kpi-card p-3 mb-3" style="cursor:pointer" onclick="filtrarPorEstado('En Trámite')">
                                                  <div class="d-flex align-items-center justify-content-between">
                                                      <span class="kpi-chip"><span class="kpi-dot warning"></span> En trámite</span>
                                                      <i class="feather icon-loader" style="font-size:18px;color:#f59e0b"></i>
                                                  </div>
                                                  <div class="kpi-num mt-2" id="compromisos-tramite">0</div>
                                                  <div class="kpi-label" id="porcentaje-tramite">En trámite (0%)</div>
                                              </div>

                                              <div class="kpi-card p-3 mb-3" style="cursor:pointer" onclick="filtrarPorEstado('Sin Cumplir')">
                                                  <div class="d-flex align-items-center justify-content-between">
                                                      <span class="kpi-chip"><span class="kpi-dot danger"></span> Sin cumplir</span>
                                                      <i class="feather icon-alert-triangle" style="font-size:18px;color:#ef4444"></i>
                                                  </div>
                                                  <div class="kpi-num mt-2" id="compromisos-sincumplir">0</div>
                                                  <div class="kpi-label" id="porcentaje-sincumplir">Sin cumplir (0%)</div>
                                              </div>

                                              <div class="kpi-card p-3 mb-3" style="cursor:pointer" onclick="filtrarPorEstado('En Espera')">
                                                  <div class="d-flex align-items-center justify-content-between">
                                                      <span class="kpi-chip"><span class="kpi-dot wait"></span> En espera</span>
                                                      <i class="feather icon-clock" style="font-size:18px;color:#64748b"></i>
                                                  </div>
                                                  <div class="kpi-num mt-2" id="compromisos-enEspera">0</div>
                                                  <div class="kpi-label">En espera</div>
                                              </div>

                                              <div class="kpi-card p-3 mb-3">
                                                  <div class="d-flex justify-content-between align-items-center">
                                                      <div>
                                                          <div class="kpi-num" style="font-size:18px" id="total-provincias">0</div>
                                                          <div class="kpi-label">Provincias</div>
                                                      </div>
                                                      <i class="feather icon-map" style="font-size:18px;color:rgba(15,23,42,.55)"></i>
                                                  </div>
                                              </div>

                                              <div class="kpi-card p-3 mb-3">
                                                  <div class="d-flex justify-content-between align-items-center">
                                                      <div>
                                                          <div class="kpi-num" style="font-size:18px" id="total-municipios">0</div>
                                                          <div class="kpi-label">Municipios</div>
                                                      </div>
                                                      <i class="feather icon-navigation" style="font-size:18px;color:rgba(15,23,42,.55)"></i>
                                                  </div>
                                              </div>

                                              <div class="kpi-card p-3 mb-3" id="card-cumplimiento">
                                                  <div class="d-flex justify-content-between align-items-center">
                                                      <div>
                                                          <div class="kpi-num" style="font-size:20px" id="nivel-cumplimiento">0.00%</div>
                                                          <div class="kpi-label">Nivel de cumplimiento</div>
                                                      </div>
                                                      <i class="feather icon-trending-up" style="font-size:18px;color:rgba(32,66,127,.85)"></i>
                                                  </div>
                                              </div>

                                              <div class="kpi-card p-3">
                                                  <div class="d-flex justify-content-between align-items-center">
                                                      <div>
                                                          <div class="kpi-num" style="font-size:18px" id="porcentaje-total-compromisos">0</div>
                                                          <div class="kpi-label">Total compromisos</div>
                                                      </div>
                                                      <i class="feather icon-layers" style="font-size:18px;color:rgba(15,23,42,.55)"></i>
                                                  </div>
                                              </div>

                                          </div>
                                      </div>

                                      <!-- CENTRO MAPA -->
                                      <div class="col-12 col-lg-6">
                                          <div class="map-wrap">
                                              <div class="map-head">
                                                  <div>
                                                      <p class="map-title m-0">
                                                          <i class="feather icon-map-pin"></i>
                                                          Mapa de Compromisos (Santander)
                                                      </p>
                                                      <p class="map-sub m-0">Selecciona un municipio para ver compromisos detallados</p>
                                                  </div>
                                                  <span class="kpi-chip">
                                                      <span class="kpi-dot" style="background:rgba(32,66,127,.85)"></span>
                                                      Vista interactiva
                                                  </span>
                                              </div>

                                              <div class="map-body">
                                                  <div class="cuerpoMapa w-12">
                                                      <div id="contenido-mapa" class="cuerpoMapa w-12">
                                                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="50 40 1000 1200">
                                                              <?php foreach ($santandergestora as $key => $value) : ?>
                                                                  <g id="<?php echo strtoupper($value['path']); ?>">
                                                                      <path id="<?php echo strtoupper($value['path']); ?>"
                                                                          d="<?php echo $value['d']; ?>"
                                                                          fill="#f7fbff"
                                                                          class="municipios"
                                                                          data-name="<?php echo strtolower($value['municipio']); ?>"
                                                                          data-id="<?php echo $value['codigo_muncipio']; ?>"
                                                                          data-secretaria="<?php echo htmlspecialchars($value['secretaria'] ?? ''); ?>"
                                                                          stroke="#000" stroke-miterlimit="10" stroke-width="0.1px">
                                                                      </path>
                                                                      <text transform="translate(264.48 382.8)" font-family="IBM Plex Sans" font-size="10" font-weight="500"></text>
                                                                  </g>
                                                              <?php endforeach; ?>
                                                              <?php require_once 'nombres_mapa_santander.php' ?>
                                                          </svg>
                                                      </div>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>

                                      <!-- DERECHA FILTROS + CHART -->
                                      <div class="col-12 col-lg-3">
                                          <div class="sticky-panel">

                                              <div class="filter-card mb-3">
                                                  <div class="fc-head"><i class="feather icon-filter"></i> Filtros</div>
                                                  <div class="fc-body">
                                                      <div class="mb-3">
                                                          <label for="tbl_secretarias_id">Seleccionar Secretaría</label>
                                                          <select name="tbl_secretarias_id" id="tbl_secretarias_id" class="form-select form-control">
                                                              <option value="">Seleccione</option>
                                                          </select>
                                                      </div>

                                                      <div class="mb-3">
                                                          <label for="tipo_ejecucion">Tipo ejecución</label>
                                                          <select class="form-control" id="tipo_ejecucion" name="tipo_ejecucion">
                                                              <option value="" selected>Todas</option>
                                                              <option value="GESTIÓN">GESTIÓN</option>
                                                              <option value="INVERSIÓN">INVERSIÓN</option>
                                                          </select>
                                                      </div>

                                                      <div>
                                                          <label for="componente">Componente</label>
                                                          <select class="form-control" id="componente" name="componente">
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
                                                              <option value="COMPROMISOS NUEVOS">COMPROMISOS NUEVOS</option>
                                                          </select>
                                                      </div>
                                                  </div>
                                              </div>

                                              <div class="card chart-card mb-3">
                                                  <div class="card-header text-center">
                                                      Compromisos por Provincia
                                                  </div>
                                                  <div class="card-body p-2">
                                                      <canvas id="graficoProvincias" height="260"></canvas>
                                                  </div>
                                              </div>

                                          </div>
                                      </div>

                                  </div><!-- row -->
                              </div><!-- card-body -->
                          </div><!-- card -->

                      </div>
                  </div>
              </div>
          </div>
      </div>

      <?php include 'admin/include/footer.php'; ?>
  </div>

  <!-- Modal Municipios -->
  <div class="modal fade" id="modalMunicipio" tabindex="-1" aria-labelledby="modalMunicipioLabel" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen modal-dialog-centered" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalMunicipioLabel">Compromisos por Municipio y Secretaría</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalmodalMunicipio()">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>

              <div class="modal-body" style="padding: 15px;">
                  <div class="row g-2">
                      <div class="col-12 col-md-4">
                          <label for="tbl_secretarias_id_modal">Seleccionar Secretaría</label>
                          <select name="tbl_secretarias_id_modal" id="tbl_secretarias_id_modal" class="form-control" onchange="filtraModal()">
                              <option value="">Seleccione</option>
                          </select>
                          <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="68">
                      </div>

                      <div class="col-12 col-md-4">
                          <label for="tbl_municipio_id">Seleccionar Municipio</label>
                          <select name="tbl_municipio_id" id="tbl_municipio_id" class="form-control" onchange="filtraModal()"></select>
                      </div>

                      <div class="col-12 col-md-4">
                          <label for="provinciaFiltro">Seleccionar Provincia</label>
                          <select name="provinciaFiltro" id="provinciaFiltro" class="form-control" onchange="filtraModal()">
                              <option value="">Seleccione</option>
                              <option value="Comunera">Comunera</option>
                              <option value="García Rovira">García Rovira</option>
                              <option value="Guanentá">Guanentá</option>
                              <option value="Metropolitana">Metropolitana</option>
                              <option value="Soto Norte">Soto Norte</option>
                              <option value="Vélez">Vélez</option>
                              <option value="Yariguíes">Yariguíes</option>
                          </select>
                      </div>

                      <div class="col-12 col-md-4">
                          <label for="componente_modal">Componente</label>
                          <select class="form-control" id="componente_modal" name="componente_modal" onchange="filtraModal()">
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

                  <div class="table-responsive mt-3">
                      <table id="dynamictable" class="table table-bordered table-hover" width="100%">
                          <thead>
                              <tr>
                                  <th>id</th>
                                  <th>Secretaría</th>
                                  <th>Compromiso PAC</th>
                                  <th>Consecuencia</th>
                                  <th>Respuesta</th>
                                  <th>Estado</th>
                                  <th>Municipio</th>
                                  <th>Provincia</th>
                                  <th>Componente</th>
                                  <th>Adjunto</th>
                                  <th>Fecha</th>
                                  <th>Fecha act.</th>
                                  <th>Ver</th>
                              </tr>
                          </thead>
                      </table>
                  </div>

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
                  <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalCompromiso()">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body" style="padding: 20px;">
                  <p id="contenidoCompromiso" style="white-space: pre-wrap;"></p>
              </div>
          </div>
      </div>
  </div>

  <!-- Modal de archivos -->
  <div class="modal fade" id="archivoModal" tabindex="-1" aria-labelledby="archivoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="archivoModalLabel">Adjuntos</h5>
                  <button type="button" onclick="cerrarModalArchivo();" class="close text-white" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body" id="archivoModalBody"></div>
          </div>
      </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>

  <!-- Required JS -->
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <!-- Prism -->
  <script src="assets/js/plugins/prism.js"></script>

  <!-- ApexCharts -->
  <script src="assets/js/plugins/apexcharts.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="stylesheet" href="admin/js/datatables/jquery.dataTables.min.css">
  <script src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <script src="admin/js/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Bootstrap 5 Bundle-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
      $("img").each(function(index, el) {
          $(this).attr("data-bs-toggle", "tooltip");
          $(this).attr("data-bs-placement", "left");
          tooltip = new bootstrap.Tooltip($(this)[0], {});
      });
      $(".mapaClick").click(function(event) {
          location.href = $(this).data("url");
      });
  </script>
    <script src="<?php echo Util::versionar('./admin/js/gestion-cumplimiento.js'); ?>"></script>
    <script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
  <script>
      $(() => {
          $("#tbl_departamento_id").val(68);
          DEPARTAMENTO.getMunicipios();
      });
  </script>

</body>
</html>
