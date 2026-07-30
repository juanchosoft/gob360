<?php
$isAjaxHacienda = isset($_GET['ajax']) && $_GET['ajax'] === 'hacienda';

if (!$isAjaxHacienda) {
    include './admin/include/head.php';
}

require_once './admin/include/generic_classes.php';
include './admin/classes/Secretarias.php';
include './admin/classes/Colombia.php';
include './admin/classes/AccionSecretaria.php';
include './admin/db/coloress.php';
include './admin/classes/Main.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Departamento.php';

$haciendaId = Util::getSecretariaIdHacienda();
$GOALicores  = 'GOA Aprehensiones de Licores';
$GOACigarrillos = 'GOA Aprehensión de Cigarrillos';
$GOACervezas = 'GOA Aprehensión de Cervezas';
$GOATabaco  = 'GOA Aprehensión de Tabaco y Otros';
$registroVisitas = 'Registro de Visitas a Establecimientos Comerciales';

$accionHacienda = $_REQUEST['accion'] ?? 'GOA - Aprehensiones';

$accionHaciendaConsulta = ($accionHacienda === 'GOA - Aprehensiones')
    ? 'GOA Aprehensiones de Licores'
    : $accionHacienda;

$arrEjecucionHacienda = [
    'codigoMunicipio' => Util::getDepartamentoPrincipal(),
    'secretariaId' => $haciendaId,
    'accion' => $accionHaciendaConsulta
];
$responseTotalEjecucionSecretarias = Secretarias::getTotalEjecucionSecretaria($arrEjecucionHacienda);
$haciendaDatos = $responseTotalEjecucionSecretarias['output']['response'] ?? [];
$datosHac = $haciendaDatos[0] ?? [];

$GOALicores_arr = $responseTotalEjecucionSecretarias['output']['GOALicores'][0] ?? [];
$GOALicores_cantidad = $GOALicores_arr['cantidad_aprehendida'] ?? 0;
$GOALicores_valor   = $GOALicores_arr['avaluo_comercial'] ?? 0;

$GOACigarrillos_arr = $responseTotalEjecucionSecretarias['output']['GOACigarrillos'][0] ?? [];
$GOACigarrillos_cantidad = $GOACigarrillos_arr['cantidad_aprehendida'] ?? 0;
$GOACigarrillos_valor    = $GOACigarrillos_arr['avaluo_comercial'] ?? 0;

$GOACervezas_arr = $responseTotalEjecucionSecretarias['output']['GOACervezas'][0] ?? [];
$GOACervezas_cantidad = $GOACervezas_arr['cantidad_aprehendida'] ?? 0;
$GOACervezas_valor    = $GOACervezas_arr['avaluo_comercial'] ?? 0;

$GOATabaco_arr = $responseTotalEjecucionSecretarias['output']['GOATabaco'][0] ?? [];
$GOATabaco_cantidad  = $GOATabaco_arr['cantidad_aprehendida'] ?? 0;
$GOATabaco_valor     = $GOATabaco_arr['avaluo_comercial'] ?? 0;

$GOATotal_cantidad_aprehendida = $GOALicores_cantidad + $GOACigarrillos_cantidad + $GOACervezas_cantidad + $GOATabaco_cantidad;
$GOATotal_avaluo_comercial = $GOALicores_valor + $GOACigarrillos_valor + $GOACervezas_valor + $GOATabaco_valor;

$registroVisitas_arr = $responseTotalEjecucionSecretarias['output']['registroVisitas'][0] ?? [];
$GOAcantidad_visitas_al_municipio = $registroVisitas_arr['cantidad_visitas_al_municipio'] ?? 0;

$goaJuridico_arr = $responseTotalEjecucionSecretarias['output']['GOAJuridico'][0] ?? [];
$goaJuridicoCustodiaValorTotal          = $goaJuridico_arr['goa_juridico_custodia_valor_total'] ?? 0;
$goaJuridicoCustodiaCantidadProcesos    = $goaJuridico_arr['goa_juridico_custodia_cantidad_procesos'] ?? 0;
$goaJuridicoCustodiaCantidadUnidades    = $goaJuridico_arr['goa_juridico_custodia_cantidad_unidades'] ?? 0;
$goaJuridicoDestruccionCantidadUnidades = $goaJuridico_arr['goa_juridico_destruccion_cantidad_unidades'] ?? 0;
$goaJuridicoDestruccionValorTotal       = $goaJuridico_arr['goa_juridico_destruccion_valor_total'] ?? 0;

$vehicular_total_recaudo                  = $datosHac['TOTAL_RECAUDO_IMPUESTO_VEHICULAR'] ?? 0;
$vehicular_total_tramites                 = $datosHac['TOTAL_TRAMITES_IMPUESTO_VEHICULAR'] ?? 0;
$vehicular_total_recaudo_y_tramite        = $datosHac['IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE'] ?? 0;
$vehicular_total_operativos               = $datosHac['TOTAL_VEHICULAR_OPERATIVOS'] ?? 0;
$vehicular_total_emplazados               = $datosHac['TOTAL_VEHICULAR_EMPLAZADOS'] ?? 0;
$vehicular_total_placas_consultadas       = $datosHac['TOTAL_VEHICULAR_PLACAS_CONSULTADAS'] ?? 0;
$vehicular_total_campanas_sensibilizacion = $datosHac['TOTAL_VEHICULAR_CAMPANAS_SENSIBILIZACION'] ?? 0;

$ESTAMPILLAS = $responseTotalEjecucionSecretarias['output']['estampillas'] ?? [];

$arrMapaHac = [
    'codigoMunicipio' => Util::getDepartamentoPrincipal(),
    'secretariaId' => $haciendaId,
    'accion' => $accionHaciendaConsulta
];
$mapData = Colombia::getInformacionSecretariaColoresMapa($arrMapaHac);
$santander = $mapData['output']['response'] ?? [];
$puntajes = $mapData['output']['puntajes'] ?? '';

if ($isAjaxHacienda) {
    include 'hacienda_section.php';
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Centro Ejecutivo de Hacienda | GOB360</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/dashboard_hacienda_ejecutivo_gob360.css">

  
</head>

<body class="dashboard-body gob360-hacienda-executive">
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Centro Ejecutivo de Hacienda</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Secretaría de Hacienda</a></li>
                <li class="breadcrumb-item active">Control fiscal y financiero</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <section class="g360-hacienda-hero" aria-label="Centro Ejecutivo de Hacienda">
        <div class="g360-hacienda-hero__grid">

          <aside class="g360-hacienda-brand">
            <span class="g360-hacienda-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-hacienda-brand__logo"
            >

            <span class="g360-hacienda-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-hacienda-brand__status">
              <span></span>
              Sistema operativo
            </div>
          </aside>

          <div class="g360-hacienda-hero__content">
            <div class="g360-hacienda-hero__top">
              <div>
                <div class="g360-hacienda-hero__eyebrow">
                  <i class="feather icon-shield"></i>
                  Analítica fiscal, tributaria y territorial
                </div>

                <h1 class="g360-hacienda-hero__title">
                  Centro Ejecutivo de Hacienda
                </h1>

                <p class="g360-hacienda-hero__description">
                  Monitorea recaudo, fiscalización, operativos GOA,
                  impuesto vehicular, procesos jurídicos, estampillas,
                  visitas comerciales y resultados municipales desde
                  una sola vista institucional.
                </p>
              </div>

              <div class="g360-hacienda-current-action">
                <span>Consulta activa</span>
                <strong><?php echo htmlspecialchars($accionHacienda, ENT_QUOTES, 'UTF-8'); ?></strong>
              </div>
            </div>

            <div class="g360-hacienda-executive-grid">
              <article class="g360-executive-kpi g360-executive-kpi--money">
                <span class="g360-executive-kpi__icon">
                  <i class="feather icon-dollar-sign"></i>
                </span>

                <div>
                  <small>Avalúo comercial GOA</small>
                  <strong>
                    $ <?php echo number_format((float)$GOATotal_avaluo_comercial, 0, ',', '.'); ?>
                  </strong>
                  <p>Valor total de elementos aprehendidos</p>
                </div>
              </article>

              <article class="g360-executive-kpi">
                <span class="g360-executive-kpi__icon">
                  <i class="feather icon-package"></i>
                </span>

                <div>
                  <small>Unidades aprehendidas</small>
                  <strong>
                    <?php echo number_format((float)$GOATotal_cantidad_aprehendida, 0, ',', '.'); ?>
                  </strong>
                  <p>Licores, cigarrillos, cervezas y otros</p>
                </div>
              </article>

              <article class="g360-executive-kpi g360-executive-kpi--success">
                <span class="g360-executive-kpi__icon">
                  <i class="feather icon-trending-up"></i>
                </span>

                <div>
                  <small>Recaudo vehicular</small>
                  <strong>
                    $ <?php echo number_format((float)$vehicular_total_recaudo, 0, ',', '.'); ?>
                  </strong>
                  <p>Consolidado del impuesto vehicular</p>
                </div>
              </article>

              <article class="g360-executive-kpi g360-executive-kpi--territory">
                <span class="g360-executive-kpi__icon">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Visitas comerciales</small>
                  <strong>
                    <?php echo number_format((float)$GOAcantidad_visitas_al_municipio, 0, ',', '.'); ?>
                  </strong>
                  <p>Acciones de control en el territorio</p>
                </div>
              </article>
            </div>

            <div class="g360-hacienda-capabilities">
              <span>
                <i class="feather icon-shield"></i>
                Fiscalización GOA
              </span>

              <span>
                <i class="feather icon-truck"></i>
                Impuesto vehicular
              </span>

              <span>
                <i class="feather icon-file-text"></i>
                Procesos jurídicos
              </span>

              <span>
                <i class="feather icon-map"></i>
                Analítica municipal
              </span>
            </div>
          </div>

        </div>
      </section>
        </div>
      </div>

      <section class="g360-hacienda-workspace" aria-label="Indicadores detallados de Hacienda">
        <header class="g360-hacienda-workspace__header">
          <div class="g360-hacienda-workspace__heading">
            <span class="g360-hacienda-workspace__icon">
              <i class="feather icon-activity"></i>
            </span>

            <div>
              <span class="g360-hacienda-workspace__eyebrow">
                Tablero operativo
              </span>

              <h2>Gestión fiscal y territorial</h2>

              <p>
                Selecciona el componente que deseas analizar.
                Los indicadores y el mapa se actualizan según la acción consultada.
              </p>
            </div>
          </div>

          <div class="g360-hacienda-workspace__meta">
            <span class="g360-live-status">
              <span></span>
              Información consolidada
            </span>

            <span class="g360-data-badge">
              <i class="feather icon-database"></i>
              Datos institucionales
            </span>
          </div>
        </header>

        <div class="g360-hacienda-workspace__body">
          <?php include 'hacienda_section.php'; ?>
        </div>
      </section>

    </div>
  </div>

  <?php include './admin/include/footer.php'; ?>
  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
</body>
</html>
