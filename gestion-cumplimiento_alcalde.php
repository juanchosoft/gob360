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

  <style>
    :root{
      --au-primary:#20427F;
      --au-primary-dark:#132b52;
      --au-accent:#2e58a8;

      --ink:#0f172a;
      --muted:#64748b;

      --radius-xl:22px;
      --radius-lg:16px;

      --shadow-soft: 0 10px 30px rgba(0,0,0,.25);
      --shadow-mid:  0 18px 45px rgba(0,0,0,.35);

      --border-w: 1px solid rgba(255,255,255,.12);
      --ring: 0 0 0 .25rem rgba(46,88,168,.35);

      --w95: rgba(255,255,255,.95);
      --w90: rgba(255,255,255,.90);
      --w80: rgba(255,255,255,.80);
      --w70: rgba(255,255,255,.70);
      --w60: rgba(255,255,255,.60);

      /* evita que header fijo tape títulos */
      --safe-top: 96px;
    }

    /* ✅ FONDO con gradiente premium (como tu imagen) */
    body{
      background:
        radial-gradient(900px 360px at 50% 115%, rgba(12, 35, 39, .95) 0%, rgba(12, 35, 39, 0) 55%),
        linear-gradient(135deg,
          #0b1221 0%,
          #0a1b24 35%,
          #0c2327 50%,
          #0b1321 75%,
          #0a1121 100%
        ) !important;
      min-height: 100vh;
    }

    /* ✅ Padding seguro para header fijo */
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

    /* ===== Breadcrumb glass (alto contraste) ===== */
    .page-header .page-block{
      background: rgba(255,255,255,.06) !important;
      border: var(--border-w) !important;
      border-radius: var(--radius-xl) !important;
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

    /* ===== Card glass ===== */
    .card{
      background: rgba(255,255,255,.06) !important;
      border: var(--border-w) !important;
      border-radius: var(--radius-xl) !important;
      box-shadow: var(--shadow-mid) !important;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    .card-header{
      background: rgba(255,255,255,.06) !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
      position: relative;
    }
    .card-header::before{
      content:"";
      position:absolute;
      top:0;left:0;
      width:100%;
      height:4px;
      background: linear-gradient(90deg, var(--au-primary), rgba(46,88,168,.35));
    }
    .card-header h5{
      margin:0;
      font-weight: 1000;
      color: var(--w95) !important;
    }

    /* ===== KPI en blanco (para que contraste y se lea perfecto) ===== */
    .kpi{
      position: relative;
      border-radius: 18px;
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(2,6,23,.10);
      box-shadow: 0 10px 30px rgba(0,0,0,.12);
      padding: 10px 12px;
      transition: transform .15s ease, box-shadow .15s ease;
      cursor: pointer;
    }
    .kpi:hover{
      transform: translateY(-2px);
      box-shadow: 0 18px 45px rgba(0,0,0,.18);
    }
    .kpi .kpi-value{
      font-weight: 1000;
      letter-spacing: .2px;
      margin: 0;
      color: #0f172a;
      line-height: 1.05;
    }
    .kpi .kpi-label{
      margin: 4px 0 0;
      font-size: .75rem;
      letter-spacing: .6px;
      text-transform: uppercase;
      color: #475569;
      font-weight: 900;
    }
    .kpi .kpi-sub{
      margin: 0;
      font-size: .78rem;
      font-weight: 900;
      color:#111827;
    }
    .kpi .kpi-dot{
      position:absolute;
      top: 12px;
      right: 12px;
      width: 10px; height: 10px;
      border-radius: 999px;
      box-shadow: 0 0 0 5px rgba(0,0,0,.04);
      background:#94a3b8;
    }
    .kpi-success{ background: linear-gradient(135deg, rgba(16,185,129,.14), rgba(255,255,255,.98)); }
    .kpi-success .kpi-dot{ background:#10b981; }
    .kpi-warning{ background: linear-gradient(135deg, rgba(245,158,11,.18), rgba(255,255,255,.98)); }
    .kpi-warning .kpi-dot{ background:#f59e0b; }
    .kpi-danger{ background: linear-gradient(135deg, rgba(239,68,68,.16), rgba(255,255,255,.98)); }
    .kpi-danger .kpi-dot{ background:#ef4444; }
    .kpi-wait{ background: linear-gradient(135deg, rgba(100,116,139,.16), rgba(255,255,255,.98)); }
    .kpi-wait .kpi-dot{ background:#64748b; }
    .kpi-primary{ background: linear-gradient(135deg, rgba(32,66,127,.16), rgba(255,255,255,.98)); }
    .kpi-primary .kpi-dot{ background: var(--au-primary); }

    /* ===== Cards de filtros (blancas para contraste en inputs) ===== */
    .filter-card{
      border-radius: 18px !important;
      background: rgba(255,255,255,.92) !important;
      border: 1px solid rgba(2,6,23,.10) !important;
      box-shadow: 0 10px 30px rgba(0,0,0,.12) !important;
    }
    .filter-card label{
      font-size: .86rem;
      font-weight: 900;
      color: #0f172a;
      margin-bottom: 6px;
    }
    .filter-card .form-control, .filter-card .form-select{
      border-radius: 14px;
      border: 1px solid rgba(2,6,23,.12);
      padding: .6rem .75rem;
      height: auto;
      background: #fff !important;
      color: #0f172a !important;
    }
    .filter-card .form-control:focus, .filter-card .form-select:focus{
      border-color: rgba(32,66,127,.35);
      box-shadow: 0 0 0 .2rem rgba(32,66,127,.12);
      outline:none;
    }

    /* ===== MAPA: textos negros legibles (con borde blanco sutil) ===== */
    #contenido-mapa svg text,
    #contenido-mapa svg text tspan{
      fill:#000000 !important;
      font-weight: 800;
      font-size: 11px;
      paint-order: stroke;
      stroke: rgba(255,255,255,.85);
      stroke-width: 1px;
    }

    /* ===== TABLAS: TD NEGROS + más pequeños (como pediste) ===== */
    table td, table td *{
      color:#000 !important;
      font-size: 12px !important;  /* 👈 más pequeño */
      font-weight: 600 !important;
    }
    table td i, table td svg{
      color:#000 !important;
      fill:#000 !important;
    }
    table thead th{
      color:#000 !important;
      font-weight: 1000 !important;
      white-space: nowrap;
    }
    .table-hover tbody tr:hover{
      background: rgba(0,0,0,.03) !important;
    }

    /* ===== MODALES: fondo claro para máximo contraste ===== */
    .modal-content{
      border: none;
      border-radius: 18px;
      box-shadow: 0 25px 80px rgba(0,0,0,.55);
      overflow:hidden;
      background: #fff !important;
      color:#000 !important;
    }
    .modal-header{
      background: linear-gradient(135deg, rgba(32,66,127,.12), rgba(255,255,255,.95)) !important;
      border-bottom: 1px solid rgba(2,6,23,.10);
    }
    .modal-title{
      font-weight: 1000;
      color:#0f172a !important;
    }
    .modal-body{
      background: #fff !important;
      color:#000 !important;
    }
    .modal .close{
      color:#0f172a !important;
      opacity: .9 !important;
      text-shadow:none !important;
    }

    /* ===== Responsive tabla ===== */
    .table-responsive{ overflow-x:auto; width:100%; }
    #dynamictable{ width:100% !important; table-layout:auto; white-space:normal; }
    html, body{ overflow-x:hidden !important; }
    /* ===== FIX Chart Veredas (evita que se estire) ===== */
.chart-fixed{
  height: 260px;              /* alto estable */
  min-height: 260px;
  max-height: 260px;
  position: relative;
}

.chart-fixed canvas{
  width: 100% !important;
  height: 100% !important;    /* obliga a ocupar el alto fijo */
  display: block;
}

@media (max-width: 576px){
  .chart-fixed{ height: 230px; min-height:230px; max-height:230px; }
}
/* ===== BLINDAJE TOTAL: evita estiramiento Chart.js ===== */

/* fija altura del body del card (porque a veces se estira por flex/grid) */
.chart-body-fixed{
  height: 290px !important;     /* header + padding ya están arriba */
  max-height: 290px !important;
  overflow: hidden !important;
}

/* contenedor real del canvas */
.chart-fixed{
  height: 260px !important;
  max-height: 260px !important;
  min-height: 260px !important;
  position: relative !important;
  overflow: hidden !important;
}

/* canvas: NO puede crecer */
.chart-fixed canvas{
  width: 100% !important;
  height: 260px !important;
  max-height: 260px !important;
  display: block !important;
}

/* en móvil un poquito menos */
@media (max-width:576px){
  .chart-body-fixed{ height: 260px !important; max-height:260px !important; }
  .chart-fixed{ height: 230px !important; max-height:230px !important; min-height:230px !important; }
  .chart-fixed canvas{ height: 230px !important; max-height:230px !important; }
}


  </style>
</head>

<body class="">
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

              <!-- [ breadcrumb ] start -->
              <div class="page-header mb-3">
                <div class="page-block">
                  <div class="row align-items-center">
                    <div class="col-md-12">
                      <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
                        <div>
                          <h5 class="m-b-10 mb-0">Gestión Cumplimiento Alcalde</h5>
                          <div style="color:var(--w70); font-size:.9rem; margin-top:6px;">
                            Estado de compromisos por vereda/municipio con filtros y reporte visual.
                          </div>
                        </div>
                        <?php include './admin/include/btn_back.php'; ?>
                      </div>
                      <ul class="breadcrumb mt-2">
                        <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Mapa visitas</a></li>
                        <li class="breadcrumb-item"><a href="#!">Gestión cumplimiento Alcalde</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              <!-- [ breadcrumb ] end -->

              <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                  <h5 class="mb-0 text-center w-100">Gestión cumplimiento Alcalde</h5>

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

                    <!-- Indicadores IZQUIERDA -->
                    <div class="col-12 col-md-4 col-xl-3">
                      <div class="kpi kpi-primary mb-3" onclick="filtrarPorEstado('todos')">
                        <span class="kpi-dot"></span>
                        <h3 class="kpi-value" id="total-compromisos">0</h3>
                        <p class="kpi-label">Compromisos</p>
                      </div>

                      <div class="kpi kpi-success mb-3" onclick="filtrarPorEstado('Cumplido')">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="compromisos-cumplidos">0</h4>
                        <p class="kpi-sub" id="porcentaje-cumplidos">Cumplidos (0%)</p>
                      </div>

                      <div class="kpi kpi-warning mb-3" onclick="filtrarPorEstado('En Trámite')">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="compromisos-tramite">0</h4>
                        <p class="kpi-sub" id="porcentaje-tramite">En trámite (0%)</p>
                      </div>

                      <div class="kpi kpi-danger mb-3" onclick="filtrarPorEstado('Sin Cumplir')">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="compromisos-sincumplir">0</h4>
                        <p class="kpi-sub" id="porcentaje-sincumplir">Sin cumplir (0%)</p>
                      </div>

                      <div class="kpi kpi-wait mb-3" onclick="filtrarPorEstado('En Espera')">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="compromisos-enEspera">0</h4>
                        <p class="kpi-sub">En espera</p>
                      </div>

                      <div class="kpi mb-3" style="cursor:default;">
                        <span class="kpi-dot"></span>
                        <h5 class="kpi-value mb-0" id="total-veredas">0</h5>
                        <p class="kpi-label">Veredas</p>
                      </div>

                      <div class="kpi mb-3" style="cursor:default;">
                        <span class="kpi-dot"></span>
                        <h5 class="kpi-value mb-0" id="total-municipios">0</h5>
                        <p class="kpi-label">Municipios</p>
                      </div>

                      <div class="kpi kpi-primary mb-3" id="card-cumplimiento" style="cursor:default;">
                        <span class="kpi-dot"></span>
                        <h4 class="kpi-value mb-0" id="nivel-cumplimiento">0.00%</h4>
                        <p class="kpi-label">Nivel de cumplimiento</p>
                      </div>

                      <div class="kpi mb-0" style="cursor:default;">
                        <span class="kpi-dot"></span>
                        <h5 class="kpi-value mb-0" id="porcentaje-total-compromisos">0</h5>
                        <p class="kpi-label">Total compromisos</p>
                      </div>
                    </div>

                    <!-- MAPA CENTRO -->
                    <div class="col-12 col-md-8 col-xl-6">
                      <div class="card">
                        <div class="card-body text-center" style="background: rgba(255,255,255,.92); border-radius: 18px;">
                          <?php if ($isUsuarioMunicipal && !empty($municipioUsuario)): ?>
                            <?php
                              $municipioInfo = Ciudad::getInformacionCiudad(['codigo_muncipio' => $municipioUsuario]);
                              $informacionMunicipio = $municipioInfo['output']['response'][0] ?? null;
                              $nombreMunicipio = !empty($informacionMunicipio['municipio']) ? strtoupper($informacionMunicipio['municipio']) : 'MUNICIPIO';
                              $viewBoxActual = !empty($informacionMunicipio['viewbox_svg']) ? $informacionMunicipio['viewbox_svg'] : '0 45 1518.36 900';
                            ?>
                            <h5 class="mb-3" style="font-weight:1000; color:var(--au-primary); letter-spacing:.3px;">
                              <?= htmlspecialchars($nombreMunicipio) ?>
                            </h5>
                          <?php else: ?>
                            <h5 class="mb-3" style="font-weight:1000; color:var(--au-primary); letter-spacing:.3px;">SANTANDER</h5>
                          <?php endif; ?>

                          <div class="cuerpoMapa w-12">
                            <div id="contenido-mapa" class="cuerpoMapa w-12">

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
                    </div>

                    <!-- Indicadores DERECHA -->
                    <div class="col-12 col-xl-3">
                      <div class="card filter-card mb-3">
                        <div class="card-body py-2 px-3">
                          <label for="tbl_secretarias_id" class="form-label fw-bold mb-1">Seleccionar Secretaría</label>
                          <select name="tbl_secretarias_id" id="tbl_secretarias_id" class="form-select form-control">
                            <option value="">Seleccione</option>
                          </select>
                        </div>
                      </div>

                      <div class="card filter-card mb-3">
                        <div class="card-body py-2 px-3">
                          <label for="tipo_ejecucion">Tipo ejecución<span class="text-danger mb-1">*</span></label>
                          <select class="form-control" id="tipo_ejecucion" name="tipo_ejecucion">
                            <option value="" selected>Todas</option>
                            <option value="GESTIÓN">GESTIÓN</option>
                            <option value="INVERSIÓN">INVERSIÓN</option>
                          </select>
                        </div>
                      </div>

                      <div class="card filter-card mb-3">
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

                      <div class="card filter-card mb-0">
                        <div class="card-body p-2">
                          <div class="text-center mb-2" style="font-weight:1000; color:#0f172a;">Compromisos por Vereda</div>
                          <canvas id="graficoVeredas" height="260"></canvas>
                        </div>
                      </div>

                    </div>

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
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalMunicipioLabel">Compromisos por Municipio y Secretaría</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalmodalMunicipio()">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="padding: 15px;">
          <div class="row g-3">

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
