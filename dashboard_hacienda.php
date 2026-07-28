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
  <title>Dashboard Hacienda</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <style>
    :root{
      --bg0:#070A12;
      --bg1:#0B1222;
      --card: rgba(255,255,255,.06);
      --stroke: rgba(255,255,255,.10);
      --stroke2: rgba(255,255,255,.14);
      --txt: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);
      --muted2: rgba(255,255,255,.50);
      --good:#18ff6d;
      --warn:#ffd166;
      --bad:#ff5b7a;
      --info:#56ccff;
      --brand:#4f7cff;
      --brand2:#9b5cff;
      --shadow: 0 20px 60px rgba(0,0,0,.35);
    }

    body.dashboard-body{
      background:
        radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
        radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
        radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
        linear-gradient(180deg, var(--bg0), var(--bg1));
      color: var(--txt);
      overflow-x:hidden;
    }

    .pcoded-main-container{ background: transparent !important; }
    .pcoded-content{ padding-bottom: 2rem; }

    .hero-gov{
      border: 1px solid var(--stroke);
      background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04));
      border-radius: 20px;
      padding: 18px;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
    }
    .hero-gov:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(300px 180px at 10% 10%, rgba(79,124,255,.35), transparent 65%),
        radial-gradient(300px 180px at 90% 20%, rgba(155,92,255,.25), transparent 65%),
        radial-gradient(500px 220px at 50% 120%, rgba(24,255,109,.10), transparent 60%);
      pointer-events:none;
    }
    .hero-gov > *{ position:relative; z-index:1; }

    .hero-title{
      font-weight: 900;
      letter-spacing: .2px;
      font-size: clamp(18px, 2.6vw, 30px);
      line-height: 1.15;
      margin:0;
      color: var(--txt);
    }
    .hero-sub{
      color: var(--muted);
      margin: 6px 0 0 0;
      font-size: 13px;
    }

    .chip{
      display:inline-flex;
      align-items:center;
      gap:.45rem;
      padding:.35rem .65rem;
      border-radius: 999px;
      border:1px solid var(--stroke);
      background: rgba(0,0,0,.20);
      color: var(--muted);
      font-size: 12px;
      white-space:nowrap;
    }
    .chip b{ color: var(--txt); font-weight: 800; }

    .btn-wow{
      border:1px solid var(--stroke2);
      background: rgba(255,255,255,.06);
      color: var(--txt);
      border-radius: 12px;
      padding: .6rem .85rem;
      font-weight: 900;
      transition: .2s ease;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }
    .btn-wow:hover{ transform: translateY(-1px); background: rgba(255,255,255,.10); color: var(--txt); }
    .btn-wow.primary{ background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)); border-color: rgba(79,124,255,.45); }

    .bento-grid{
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 14px;
      margin-top: 14px;
    }
    @media (max-width: 1199px){ .bento-grid{ grid-template-columns: repeat(8, 1fr); } }
    @media (max-width: 767px){ .bento-grid{ grid-template-columns: repeat(4, 1fr); gap: 12px; } }

    .kpi-card{
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.06);
      border-radius: 18px;
      padding: 14px;
      height: 100%;
      box-shadow: 0 14px 40px rgba(0,0,0,.25);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    .kpi-card:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(260px 140px at 10% 0%, rgba(79,124,255,.18), transparent 60%),
        radial-gradient(260px 140px at 100% 20%, rgba(155,92,255,.12), transparent 60%);
      opacity:.85;
      pointer-events:none;
    }
    .kpi-card > *{ position:relative; z-index:1; }

    .kpi-card.feature{
      background: linear-gradient(135deg, rgba(79,124,255,.18), rgba(155,92,255,.10));
      border-color: rgba(79,124,255,.25);
      box-shadow: 0 22px 70px rgba(0,0,0,.45);
    }

    .kpi-top{ display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
    .kpi-ico{
      width: 44px; height:44px; border-radius: 16px;
      display:flex; align-items:center; justify-content:center;
      border:1px solid var(--stroke);
      background: rgba(0,0,0,.18);
      font-size: 18px;
      flex: 0 0 auto;
    }
    .kpi-label{ color: var(--muted); font-size: 12px; margin: 0; }
    .kpi-value{ font-weight: 950; font-size: 24px; margin: 2px 0 0 0; letter-spacing:.2px; }
    .kpi-meta{
      margin-top: 10px;
      display:flex; align-items:center; justify-content:space-between; gap:10px;
      color: var(--muted2);
      font-size: 12px;
    }

    .trend{
      display:inline-flex; align-items:center; gap:.35rem;
      padding:.2rem .55rem;
      border-radius: 999px;
      border: 1px solid var(--stroke);
      background: rgba(0,0,0,.18);
      font-weight: 950;
      color: var(--txt);
    }

    .span-12{ grid-column: span 12; }
    .span-8{ grid-column: span 8; }
    .span-6{ grid-column: span 6; }
    .span-4{ grid-column: span 4; }
    .span-3{ grid-column: span 3; }
    .span-2{ grid-column: span 2; }

    @media (max-width: 1199px){
      .span-12{ grid-column: span 8; }
      .span-8{ grid-column: span 8; }
      .span-6{ grid-column: span 4; }
      .span-4{ grid-column: span 4; }
      .span-3{ grid-column: span 4; }
      .span-2{ grid-column: span 4; }
    }
    @media (max-width: 767px){
      .span-12,.span-8,.span-6,.span-4,.span-3,.span-2{ grid-column: span 4; }
    }

    .panel-card{
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.06);
      border-radius: 18px;
      padding: 14px;
      box-shadow: 0 14px 40px rgba(0,0,0,.25);
      height: 100%;
      backdrop-filter: blur(10px);
    }
    .panel-title{
      display:flex; align-items:center; justify-content:space-between; gap:10px;
      margin-bottom: 10px;
    }
    .panel-title h6{ margin:0; font-weight: 950; letter-spacing:.2px; color: var(--txt); }
    .panel-title small{ color: var(--muted); }

    .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }
    .breadcrumb .breadcrumb-item.active{ color: var(--txt) !important; }

    .hz-group{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:14px; }
    .hz-group select{ background:rgba(255,255,255,.06); border:1px solid var(--stroke); border-radius:14px; padding:10px 14px; color:#fff; font-weight:700; font-size:13px; }
    .hz-group select option{ background:#1e293b; color:#fff; }
    .hz-kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-bottom:14px; }
    .hz-kpi{ background:rgba(255,255,255,.04); border:1px solid var(--stroke); border-radius:16px; padding:14px; }
    .hz-kpi small{ color:var(--muted); font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.3px; }
    .hz-kpi strong{ display:block; color:#fff; font-size:22px; font-weight:950; margin-top:4px; }
    .hz-kpi .sub{ color:var(--muted2); font-size:12px; }
    .hz-map-wrap{ background:rgba(255,255,255,.04); border:1px solid var(--stroke); border-radius:22px; padding:16px; margin-top:14px; }
    #contenido-mapa svg{ width:100%; height:auto; max-height:480px; }
    #contenido-mapa .municipios{ cursor:pointer; transition:opacity .15s; }
    #contenido-mapa .municipios:hover{ opacity:.75; }

    .status-dot{
      width:10px; height:10px; border-radius:50%;
      display:inline-block;
    }
    .status-dot.good{ background:var(--good); }
  </style>
</head>

<body class="dashboard-body">
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
                <h5 class="m-b-10">Dashboard Hacienda</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Secretaría de Hacienda</a></li>
                <li class="breadcrumb-item active">Dashboard Hacienda</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="hero-gov">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <span class="chip"><i class="bi bi-shield-check"></i> Secretaría de Hacienda</span>
                  <span class="chip"><i class="bi bi-diagram-3"></i> GOA · Ingresos · Tesorería · Automotores</span>
                </div>
                <h1 class="hero-title">Ejecución Secretaría de Hacienda</h1>
                <p class="hero-sub">Indicadores de gestión, recaudo y operativos en tiempo real.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php include 'hacienda_section.php'; ?>

    </div>
  </div>

  <?php include './admin/include/footer.php'; ?>
  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
</body>
</html>
