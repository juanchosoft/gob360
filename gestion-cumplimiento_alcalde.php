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

// Identificar tipo de usuario
$userType = SessionData::getUserType();
$tiposUsuarioMunicipal = ['Alcalde', 'Auxiliar_Alcalde', 'Secretario_Despacho', 'Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);
$municipioUsuario = $isUsuarioMunicipal ? SessionData::getCodigoMunicipio() : '';

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

// ========== MAPA: Veredas para Alcalde, Santander completo para Admin ==========
$municipiosDepartamento = [];
$santandergestora = [];
$departamentoEstatico = '68';
$pilar = null;

if ($isUsuarioMunicipal && !empty($municipioUsuario)) {
    $arr = [
        'codigo_departamento' => $departamentoEstatico,
        'codigo_municipio' => $municipioUsuario,
        'pilar' => $pilar
    ];
    $dataVeredas = Colombia::calcularColoresDeCompromisosPorveredasDeUnaAlcaldia($arr);
    $municipiosDepartamento = $dataVeredas['output']['response'] ?? [];
} else {
    $arrgestora = array('codigo' => Util::getDepartamentoPrincipal());
    $datagestora = Colombia::getInformacionParaMapaGestoraSocial($arrgestora);
    $isvalid = $datagestora['output']['valid'];
    $santandergestora =  $datagestora['output']['response'];
}

$nombreTerritorio = 'SANTANDER';
$viewBoxActual = '0 45 1518.36 900';
$informacionMunicipio = null;

if ($isUsuarioMunicipal && !empty($municipioUsuario)) {
    $municipioInfo = Ciudad::getInformacionCiudad([
        'codigo_muncipio' => $municipioUsuario,
    ]);

    $informacionMunicipio = $municipioInfo['output']['response'][0] ?? null;

    if (!empty($informacionMunicipio['municipio'])) {
        $nombreTerritorio = strtoupper(
            (string) $informacionMunicipio['municipio']
        );
    } else {
        $nombreTerritorio = 'MUNICIPIO ' . $municipioUsuario;
    }

    if (!empty($informacionMunicipio['viewbox_svg'])) {
        $viewBoxActual = (string) $informacionMunicipio['viewbox_svg'];
    }
}

$ambitoTerritorial = $isUsuarioMunicipal
    ? 'Gestión municipal'
    : 'Cobertura departamental';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- jQuery (compatibilidad con tu vista) -->
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

  <!-- Highcharts módulos -->
  <script src="https://code.highcharts.com/modules/data.js"></script>
  <script src="https://code.highcharts.com/modules/drilldown.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>

  
  <link rel="stylesheet" href="assets/css/gestion_cumplimiento_alcalde_gob360_premium.css">

</head>

<body class="gob360-compliance-page">
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

  <!-- [ Main Content ] start -->
  <div class="pcoded-main-container">
    <div class="pcoded-wrapper">
      <div class="pcoded-content">
        <div class="pcoded-inner-content">
          <div class="main-body">
            <div class="page-wrapper">

              <section class="g360-compliance-hero" aria-label="Gestión y cumplimiento del alcalde">
                <div class="g360-compliance-hero__grid">

                  <aside class="g360-compliance-brand">
                    <span class="g360-compliance-brand__eyebrow">
                      Plataforma institucional
                    </span>

                    <img
                      src="assets/img/gob360l.png"
                      alt="Logo GOB360"
                      class="g360-compliance-brand__logo"
                    >

                    <span class="g360-compliance-brand__caption">
                      Gestión pública inteligente y territorial
                    </span>

                    <div class="g360-compliance-brand__status">
                      <span></span>
                      Analítica territorial activa
                    </div>
                  </aside>

                  <div class="g360-compliance-hero__content">
                    <div class="g360-compliance-hero__top">
                      <div>
                        <div class="g360-compliance-hero__eyebrow">
                          <i class="feather icon-activity"></i>
                          Seguimiento territorial municipal
                        </div>

                        <h1 class="g360-compliance-hero__title">
                          Gestión y Cumplimiento del Alcalde
                        </h1>

                        <p class="g360-compliance-hero__description">
                          Consolida el estado de los compromisos, la cobertura
                          territorial, las visitas institucionales y la inversión
                          ejecutada mediante mapas, indicadores y filtros dinámicos.
                        </p>
                      </div>

                      <div class="g360-compliance-hero__actions">
                        <button
                          type="button"
                          class="g360-hero-button"
                          onclick="window.location.reload()"
                        >
                          <i class="feather icon-refresh-cw"></i>
                          Actualizar tablero
                        </button>

                        <div class="g360-compliance-back">
                          <?php include './admin/include/btn_back.php'; ?>
                        </div>
                      </div>
                    </div>

                    <div class="g360-compliance-summary">
                      <article>
                        <span class="g360-compliance-summary__icon">
                          <i class="feather icon-map-pin"></i>
                        </span>

                        <div>
                          <small>Ámbito territorial</small>
                          <strong>
                            <?= htmlspecialchars(
                              $nombreTerritorio,
                              ENT_QUOTES,
                              'UTF-8'
                            ) ?>
                          </strong>
                          <p><?= htmlspecialchars($ambitoTerritorial, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                      </article>

                      <article>
                        <span class="g360-compliance-summary__icon g360-compliance-summary__icon--visits">
                          <i class="feather icon-navigation"></i>
                        </span>

                        <div>
                          <small>Visitas consolidadas</small>
                          <strong><?= number_format($visitas, 0, ',', '.') ?></strong>
                          <p>Gestora Social y ASPAS</p>
                        </div>
                      </article>

                      <article>
                        <span class="g360-compliance-summary__icon g360-compliance-summary__icon--people">
                          <i class="feather icon-users"></i>
                        </span>

                        <div>
                          <small>Población impactada</small>
                          <strong><?= number_format($impactada, 0, ',', '.') ?></strong>
                          <p>Beneficiarios registrados</p>
                        </div>
                      </article>

                      <article>
                        <span class="g360-compliance-summary__icon g360-compliance-summary__icon--investment">
                          <i class="feather icon-dollar-sign"></i>
                        </span>

                        <div>
                          <small>Inversión consolidada</small>
                          <strong>
                            $<?= number_format($inversion / 1000000, 1, ',', '.') ?>M
                          </strong>
                          <p>Valor expresado en millones</p>
                        </div>
                      </article>
                    </div>

                    <div class="g360-compliance-capabilities" aria-hidden="true">
                      <span><i class="feather icon-map"></i> Mapa territorial</span>
                      <span><i class="feather icon-check-circle"></i> Estados de cumplimiento</span>
                      <span><i class="feather icon-filter"></i> Filtros institucionales</span>
                      <span><i class="feather icon-bar-chart-2"></i> Análisis por vereda</span>
                      <span><i class="feather icon-file-text"></i> Consulta de compromisos</span>
                    </div>
                  </div>

                </div>
              </section>

              <div class="card g360-compliance-dashboard">
                <div class="card-header">
                  <div class="g360-card-heading">
                    <span class="g360-card-heading__icon">
                      <i class="feather icon-monitor"></i>
                    </span>

                    <div>
                      <span class="g360-card-heading__eyebrow">
                        Centro de control territorial
                      </span>

                      <h5>Balance de compromisos y cobertura</h5>

                      <p>
                        Selecciona un estado, territorio o criterio institucional
                        para actualizar el mapa y los resultados.
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
                  <div class="g360-dashboard-intro">
                    <span class="g360-dashboard-intro__icon">
                      <i class="feather icon-info"></i>
                    </span>

                    <div>
                      <strong>Explora el comportamiento territorial</strong>
                      <p>
                        Los indicadores de la izquierda filtran por estado.
                        El mapa permite seleccionar municipios o veredas y los
                        controles de la derecha refinan el análisis.
                      </p>
                    </div>
                  </div>

                  <div class="row g-3 g360-dashboard-grid">

                    <!-- Indicadores IZQUIERDA -->
                    <aside class="col-12 col-md-4 col-xl-3 g360-kpi-column">
                      <div class="kpi kpi-primary mb-3 g360-kpi-card" onclick="filtrarPorEstado('todos')">
                        <span class="kpi-dot"></span>
                        <h3 class="kpi-value" id="total-compromisos">0</h3>
                        <p class="kpi-label">Compromisos</p>
                      </div>

                      <div class="kpi kpi-success mb-3 g360-kpi-card" onclick="filtrarPorEstado('Cumplido')">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="compromisos-cumplidos">0</h4>
                        <p class="kpi-sub" id="porcentaje-cumplidos">Cumplidos (0%)</p>
                      </div>

                      <div class="kpi kpi-warning mb-3 g360-kpi-card" onclick="filtrarPorEstado('En Trámite')">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="compromisos-tramite">0</h4>
                        <p class="kpi-sub" id="porcentaje-tramite">En trámite (0%)</p>
                      </div>

                      <div class="kpi kpi-danger mb-3 g360-kpi-card" onclick="filtrarPorEstado('Sin Cumplir')">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="compromisos-sincumplir">0</h4>
                        <p class="kpi-sub" id="porcentaje-sincumplir">Sin cumplir (0%)</p>
                      </div>

                      <div class="kpi kpi-wait mb-3 g360-kpi-card" onclick="filtrarPorEstado('En Espera')">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="compromisos-enEspera">0</h4>
                        <p class="kpi-sub">En espera</p>
                      </div>

                      <div class="kpi mb-3 g360-kpi-card g360-kpi-card--neutral" style="cursor:default;">
                        <span class="kpi-dot"></span>
                        <h5 class="kpi-value mb-0" id="total-veredas">0</h5>
                        <p class="kpi-label">Veredas</p>
                      </div>

                      <div class="kpi mb-3 g360-kpi-card g360-kpi-card--neutral" style="cursor:default;">
                        <span class="kpi-dot"></span>
                        <h5 class="kpi-value mb-0" id="total-municipios">0</h5>
                        <p class="kpi-label">Municipios</p>
                      </div>

                      <div class="kpi kpi-primary mb-3 g360-kpi-card" id="card-cumplimiento" style="cursor:default;">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="nivel-cumplimiento">0.00%</h4>
                        <p class="kpi-label">Nivel de cumplimiento</p>
                      </div>

                      <div class="kpi mb-0 g360-kpi-card g360-kpi-card--total" style="cursor:default;">
                        <span class="kpi-dot"></span>
                        <h5 class="kpi-value mb-0" id="porcentaje-total-compromisos">0</h5>
                        <p class="kpi-label">Total compromisos</p>
                      </div>
                    </aside>

                    <!-- MAPA CENTRO -->
                    <section class="col-12 col-md-8 col-xl-6 g360-map-column">
                      <div class="card g360-map-card">
                        <div class="card-body text-center g360-map-card__body">
                          <div class="g360-map-title">
                            <span>
                              <i class="feather icon-map"></i>
                            </span>

                            <div>
                              <small>Territorio visualizado</small>
                              <h5>
                                <?= htmlspecialchars(
                                  $nombreTerritorio,
                                  ENT_QUOTES,
                                  'UTF-8'
                                ) ?>
                              </h5>
                            </div>
                          </div>

                          <div class="cuerpoMapa w-12">
                            <div id="contenido-mapa" class="cuerpoMapa w-12 g360-map-stage">

                              <?php if ($isUsuarioMunicipal && !empty($municipioUsuario)): ?>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     viewBox="<?= htmlspecialchars($viewBoxActual) ?>"
                                     width="100%" height="auto"
                                     preserveAspectRatio="xMidYMid meet"
                                     style="max-height: 700px; min-height: 520px;">
                                  <?php foreach ($municipiosDepartamento as $value): ?>
                                    <g id="<?= $value['nombre_svg'] ?>">
                                      <?php if (!empty($value['points'])): ?>
                                        <polygon points="<?= strtoupper($value['points']) ?>"
                                          fill="<?= strtolower($value['color_calculado'] ?? '#f7fbff') ?>"
                                          fill-rule="evenodd"
                                          class="vereda-click"
                                          data-name="<?= strtolower($value['nombre_vereda']) ?>"
                                          data-id="<?= $value['vereda_id'] ?>"
                                          title="<?= strtoupper($value['nombre_vereda']) ?>"
                                          stroke="#0b1220" stroke-miterlimit="10" stroke-width="0.6px"
                                          style="cursor:pointer;" />
                                      <?php elseif (!empty($value['path'])): ?>
                                        <path d="<?= $value['path'] ?>"
                                          title="<?= strtoupper(str_replace("-", " ", $value['nombre_vereda'])) ?>"
                                          class="vereda-click"
                                          data-name="<?= strtolower($value['nombre_vereda']) ?>"
                                          data-id="<?= $value['vereda_id'] ?>"
                                          style="fill:<?= strtolower($value['color_calculado'] ?? '#f7fbff') ?>; cursor:pointer;"
                                          stroke="#0b1220" stroke-miterlimit="10" stroke-width="0.6px" />
                                      <?php endif; ?>

                                      <?php if (!empty($value['tspan'])): ?>
                                        <?= $value['tspan']; ?>
                                      <?php endif; ?>
                                    </g>
                                  <?php endforeach; ?>
                                </svg>
                              <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     viewBox="50 40 1000 1200"
                                     width="100%" height="auto"
                                     preserveAspectRatio="xMidYMid meet"
                                     style="max-height: 700px; min-height: 520px;">
                                  <?php foreach ($santandergestora as $key => $value) : ?>
                                    <g id="<?php echo strtoupper($value['path']); ?>">
                                      <path id="<?php echo strtoupper($value['path']); ?>"
                                        d="<?php echo $value['d']; ?>"
                                        fill="#f7fbff"
                                        class="municipios"
                                        data-name="<?php echo strtolower($value['municipio']); ?>"
                                        data-id="<?php echo $value['codigo_muncipio']; ?>"
                                        data-secretaria="<?php echo htmlspecialchars($value['secretaria'] ?? ''); ?>"
                                        stroke="#0b1220" stroke-miterlimit="10" stroke-width="0.1px"
                                        style="cursor:pointer;">
                                      </path>
                                    </g>
                                  <?php endforeach; ?>
                                  <?php require_once 'nombres_mapa_santander.php' ?>
                                </svg>
                              <?php endif; ?>

                            </div>
                          </div>
                        </div>
                      </div>
                    </section>

                    <!-- Indicadores DERECHA -->
                    <aside class="col-12 col-xl-3 g360-filter-column">
                      <div class="card filter-card mb-3 g360-filter-card">
                        <div class="card-body py-2 px-3">
                          <label for="tbl_secretarias_id" class="form-label fw-bold mb-1">Seleccionar Secretaría</label>
                          <select name="tbl_secretarias_id" id="tbl_secretarias_id" class="form-select form-control">
                            <option value="">Seleccione</option>
                          </select>
                        </div>
                      </div>

                      <div class="card filter-card mb-3 g360-filter-card">
                        <div class="card-body py-2 px-3">
                          <label for="tipo_ejecucion">Tipo ejecución<span class="text-danger mb-1">*</span></label>
                          <select class="form-control" id="tipo_ejecucion" name="tipo_ejecucion">
                            <option value="" selected>Todas</option>
                            <option value="GESTIÓN">GESTIÓN</option>
                            <option value="INVERSIÓN">INVERSIÓN</option>
                          </select>
                        </div>
                      </div>

                      <div class="card filter-card mb-3 g360-filter-card">
                        <div class="card-body py-2 px-3">
                          <label for="componente">Componente<span class="text-danger mb-1">*</span></label>
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

                      <div class="card filter-card mb-0 g360-chart-card">
                        <div class="card-body p-2 chart-body-fixed">
                          <div class="text-center mb-2" style="font-weight:1000; color:#0f172a;">Compromisos por Vereda</div>
                          <div class="chart-fixed"><canvas id="graficoVeredas" height="260"></canvas></div>
                        </div>
                      </div>

                    </aside>

                  </div><!-- row -->
                </div><!-- body -->
              </div><!-- card -->

            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include 'admin/include/footer.php'; ?>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="modalMunicipio" tabindex="-1" aria-labelledby="modalMunicipioLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered" role="document">
      <div class="modal-content g360-territory-modal">
        <div class="modal-header">
          <div class="g360-modal-heading">
            <span>
              <i class="feather icon-map"></i>
            </span>

            <div>
              <small>Consulta territorial detallada</small>
              <h5 class="modal-title" id="modalMunicipioLabel">
                Compromisos por municipio y secretaría
              </h5>
            </div>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalmodalMunicipio()">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="g360-modal-intro">
            <i class="feather icon-info"></i>
            <span>
              Utiliza los filtros para consultar los compromisos asociados
              al territorio seleccionado.
            </span>
          </div>

          <div class="row g-3 g360-modal-filters">

            <div class="col-12 col-md-4">
              <label for="tbl_secretarias_id_modal">Seleccionar Secretaría</label>
              <select name="tbl_secretarias_id_modal" id="tbl_secretarias_id_modal" class="form-control" onchange="filtraModal()">
                <option value="">Seleccione</option>
              </select>
              <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="68">
            </div>

            <div class="col-12 col-md-4">
              <label for="tbl_municipio_id">Seleccionar Municipio</label>
              <select name="tbl_municipio_id" id="tbl_municipio_id" class="form-control" onchange="cargarVeredasModal(); filtraModal();"></select>
            </div>

            <div class="col-12 col-md-4">
              <label for="veredaFiltro">Seleccionar Vereda</label>
              <select name="veredaFiltro" id="veredaFiltro" class="form-control" onchange="filtraModal()">
                <option value="">Seleccione primero un municipio</option>
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
                <option value="COMPROMISOS NUEVOS">COMPROMISOS NUEVOS</option>
              </select>
            </div>

          </div>

          <div class="table-responsive mt-3 g360-modal-table">
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
                  <th>Vereda</th>
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

  <div class="modal fade" id="modalCompromiso" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content g360-detail-modal">
        <div class="modal-header">
          <div class="g360-modal-heading">
            <span>
              <i class="feather icon-file-text"></i>
            </span>

            <div>
              <small>Información del registro</small>
              <h5 class="modal-title" id="modalCompromisoLabel">
                Detalle del compromiso
              </h5>
            </div>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalCompromiso()">
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

  <!-- Modal de archivos -->
  <div class="modal fade" id="archivoModal" tabindex="-1" aria-labelledby="archivoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content g360-files-modal">
        <div class="modal-header">
          <div class="g360-modal-heading">
            <span class="g360-modal-heading__files">
              <i class="feather icon-paperclip"></i>
            </span>

            <div>
              <small>Evidencias del compromiso</small>
              <h5 class="modal-title" id="archivoModalLabel">
                Archivos adjuntos
              </h5>
            </div>
          </div>
          <button type="button" onclick="cerrarModalArchivo();" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="archivoModalBody"></div>
      </div>
    </div>
  </div>

  <!-- Variables de sesión para JavaScript -->
  <input type="hidden" id="municipioUsuario" value="<?php echo $municipioUsuario; ?>">
  <input type="hidden" id="tipoUsuario" value="<?php echo $userType; ?>">
  <input type="hidden" id="isUsuarioMunicipal" value="<?php echo $isUsuarioMunicipal ? '1' : '0'; ?>">

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

  <!-- DataTables -->
  <link rel="stylesheet" href="admin/js/datatables/jquery.dataTables.min.css">
  <script src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <script src="admin/js/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Bootstrap Bundle (tu proyecto mezcla, lo dejo por compatibilidad) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Tooltip (igual que tu vista original) -->
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

  <script src="admin/js/gestion-cumplimiento-alcalde.js"></script>
  <script>
(function forceFixGraficoVeredas(){
  const canvas = document.getElementById('graficoVeredas');
  if (!canvas || !window.Chart) return;

  const hardFix = () => {
    const chart = Chart.getChart(canvas);
    if (!chart) return setTimeout(hardFix, 250);

    // ✅ que use el tamaño del contenedor (fijo) y NO se autoestire
    chart.options.responsive = true;
    chart.options.maintainAspectRatio = false;

    // ✅ fuerza altura real del canvas (por si algún CSS externo lo pisa)
    canvas.style.height = '260px';
    canvas.style.maxHeight = '260px';

    // ✅ recalcula
    chart.resize();
    chart.update();
  };

  hardFix();
})();
</script>

  <script src="admin/js/departamento.js"></script>


  <script>
    $(() => {
      $("#tbl_departamento_id").val(68);
      DEPARTAMENTO.getMunicipios();
    });
  </script>

</body>
</html>
