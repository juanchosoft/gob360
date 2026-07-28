<?php

include './admin/include/head.php';

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
    $final =  str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists !== false) {
        $final =  substr($final, 0, $exists);
    }
    return $final;
}

require_once './admin/include/generic_classes.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
include './admin/db/colores.php';
include './admin/classes/MainProyectosAlcalde.php';
include './admin/classes/SecretariasMunicipio.php';

// Identificar tipo de usuario
$userType = SessionData::getUserType();
$tiposUsuarioMunicipal = ['Alcalde', 'Auxiliar_Alcalde', 'Secretario_Despacho', 'Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);
$municipioUsuario = $isUsuarioMunicipal ? SessionData::getCodigoMunicipio() : '';

// Si es usuario municipal, pasamos su código de municipio para filtrar
$filtroMunicipio = $municipioUsuario ?: null;

// Obtener parámetros de URL
$secretariaSeleccionada = isset($_REQUEST['secretaria']) ? intval($_REQUEST['secretaria']) : 0;
$veredaSeleccionada = isset($_REQUEST['vereda']) ? intval($_REQUEST['vereda']) : 0;

// DEBUG
error_log("===== DEBUG PARAMS =====");
error_log("URL: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
error_log("Secretaría seleccionada: " . $secretariaSeleccionada);
error_log("Vereda seleccionada: " . $veredaSeleccionada);
error_log("Municipio usuario: " . $municipioUsuario);

// Construir filtros para las consultas
$filtros = [];
if ($filtroMunicipio) $filtros['codigo_municipio'] = $filtroMunicipio;
if ($secretariaSeleccionada > 0) $filtros['tbl_secretarias_id'] = $secretariaSeleccionada;
if ($veredaSeleccionada > 0) $filtros['tbl_vereda_id'] = $veredaSeleccionada;

// Obtener estadísticas de proyectos
error_log("Filtros aplicados: " . print_r($filtros, true));
$arrTotalProyectos = MainProyectosAlcalde::getTotalProyectos(!empty($filtros) ? $filtros : null);
$totalProyectos = $arrTotalProyectos['output']['total_proyectos'] ?? 0;
error_log("Total proyectos calculado: " . $totalProyectos);

$arrValorTotal = MainProyectosAlcalde::getValorTotalInversion(!empty($filtros) ? $filtros : null);
$valorTotalInversion = $arrValorTotal['output']['valor_total'] ?? 0;
error_log("Valor total inversión: " . $valorTotalInversion);

$arrProyectosPorEstado = MainProyectosAlcalde::getProyectosPorEstado(!empty($filtros) ? $filtros : null);
$proyectosPorEstado = $arrProyectosPorEstado['output']['proyectos_por_estado'] ?? [];
error_log("Estados de proyectos: " . print_r($proyectosPorEstado, true));

$arrProyectosPorSecretaria = MainProyectosAlcalde::getProyectosPorSecretaria($filtroMunicipio ? ['codigo_municipio' => $filtroMunicipio] : null);
$proyectosPorSecretaria = $arrProyectosPorSecretaria['output']['proyectos_por_secretaria'] ?? [];

// Obtener datos del departamento
$departamento = new Departamento();
$santander = $departamento->getAll(["id" => 21]);
$santander = $santander["output"]["response"]["0"];
$code = null;
$mapa = null;

if (isset($_GET['depto_id']) && in_array($_GET['depto_id'], [1, 12, 21])) {
    switch ($_GET['depto_id']) {
        case '21':
            $code = $santander["codigo_departamento"];
            $mapa = "admin/mapa-santander/mapa.php";
            break;
    }
}

if (!is_null($code)) {
    $arr = Ciudad::getAll(array('codigo_departamento' => $code));
    $finalMunicipios = $arr['output']['response'];
    $arrApoyoDep = Ciudad::getApoyoByCodigoDepartamento(array('codigo_departamento' => $code));
}

// Calcular totales por estado
$totalEstudiosPrevios = 0;
$totalEnFormulacion = 0;
$totalEnEjecucion = 0;
$totalTerminados = 0;
$totalEntregado = 0;

foreach ($proyectosPorEstado as $estado) {
    $estadoNombre = strtolower($estado['estado']);
    if (strpos($estadoNombre, 'estudio') !== false || strpos($estadoNombre, 'previo') !== false) {
        $totalEstudiosPrevios += $estado['total'];
    } elseif (strpos($estadoNombre, 'formulaci') !== false) {
        $totalEnFormulacion += $estado['total'];
    } elseif (strpos($estadoNombre, 'ejecuci') !== false || strpos($estadoNombre, 'trámite') !== false) {
        $totalEnEjecucion += $estado['total'];
    } elseif (strpos($estadoNombre, 'terminado') !== false) {
        $totalTerminados += $estado['total'];
    } elseif (strpos($estadoNombre, 'entregado') !== false) {
        $totalEntregado += $estado['total'];
    }
}

?>

<body class="dashboard-body au-dark">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <!-- ✅ ESTILO WOW (solo diseño) -->
    <style>
      :root{
        --ink:#EAF0FF;
        --muted:rgba(234,240,255,.72);
        --glass: rgba(255,255,255,.08);
        --glass2: rgba(255,255,255,.06);
        --stroke: rgba(255,255,255,.12);
        --stroke2: rgba(255,255,255,.08);
        --shadow: 0 22px 70px rgba(0,0,0,.34);
        --shadow2: 0 14px 40px rgba(0,0,0,.28);
        --r-xl: 22px;
        --r-lg: 16px;

        --brand:#60A5FA;
        --violet:#A78BFA;
        --ok:#34D399;
        --warn:#FBBF24;
        --danger:#FB7185;
        --cyan:#22D3EE;

        --safe-top: 96px;
      }

      body.au-dark{
        color: var(--ink);
        background:
          radial-gradient(1200px 600px at 10% 10%, rgba(96,165,250,.22), transparent 55%),
          radial-gradient(900px 500px at 90% 15%, rgba(167,139,250,.18), transparent 55%),
          radial-gradient(900px 520px at 70% 90%, rgba(52,211,153,.14), transparent 55%),
          linear-gradient(135deg, #081226 0%, #0B1B38 45%, #070B16 100%);
        min-height:100vh;
      }

      /* ✅ safe top (header fijo) */
      .pcoded-content{
        padding: calc(var(--safe-top) + 16px) 16px 18px !important;
      }
      @media(min-width:768px){
        :root{ --safe-top: 112px; }
        .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; }
      }
      @media(min-width:1200px){
        :root{ --safe-top: 120px; }
        .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; }
      }

      /* Header / breadcrumb modo dark */
      .page-header .page-block{
        background: linear-gradient(135deg, rgba(255,255,255,.10), rgba(255,255,255,.06)) !important;
        border: 0.1px solid rgba(255,255,255,.12) !important;
        border-radius: var(--r-xl) !important;
        box-shadow: var(--shadow2);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        overflow:hidden;
      }
      .page-header-title h5{
        color:#fff !important;
        font-weight:1000 !important;
        letter-spacing:.2px;
      }
      .breadcrumb{
        background: transparent !important;
      }
      .breadcrumb-item a, .breadcrumb-item{
        color: rgba(255,255,255,.78) !important;
        font-weight: 800;
      }
      .breadcrumb-item.active{
        color: rgba(255,255,255,.92) !important;
      }

      /* Cards wow (override bootstrap/tema) */
      .card{
        background: linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.06)) !important;
        border: 1px solid rgba(255,255,255,.12) !important;
        border-radius: var(--r-xl) !important;
        box-shadow: var(--shadow);
        overflow:hidden;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
      }
      .card .card-header{
        background: linear-gradient(180deg, rgba(0,0,0,.20), rgba(0,0,0,0)) !important;
        border-bottom: 1px solid rgba(255,255,255,.10) !important;
        color:#fff !important;
      }
      .card .card-header h5{
        color:#fff !important;
        font-weight:1000 !important;
        margin:0;
      }
      .card .card-body{
        color: rgba(255,255,255,.86);
      }

      /* Botones */
      .btn{
        border-radius: 14px !important;
        font-weight: 900 !important;
      }
      .btn-light{
        background: rgba(255,255,255,.92) !important;
        border: 1px solid rgba(255,255,255,.22) !important;
      }

      /* Select premium */
      .form-control, select.form-control{
        background: rgba(255,255,255,.06) !important;
        border: 1px solid rgba(255,255,255,.14) !important;
        color: #fff !important;
        border-radius: 16px !important;
        font-weight: 800 !important;
        box-shadow: 0 10px 30px rgba(0,0,0,.18);
        padding: .65rem .95rem !important;
      }
      .form-control:focus{
        outline: none !important;
        box-shadow: 0 0 0 .20rem rgba(96,165,250,.22) !important;
        border-color: rgba(96,165,250,.45) !important;
      }
      select.form-control{
        appearance: none;
        background-image:
          linear-gradient(45deg, transparent 50%, rgba(255,255,255,.85) 50%),
          linear-gradient(135deg, rgba(255,255,255,.85) 50%, transparent 50%),
          linear-gradient(to right, rgba(255,255,255,.14), rgba(255,255,255,.08));
        background-position:
          calc(100% - 18px) calc(1em + 3px),
          calc(100% - 13px) calc(1em + 3px),
          calc(100% - 2.2em) .55em;
        background-size: 5px 5px, 5px 5px, 1px 1.8em;
        background-repeat: no-repeat;
        padding-right: 2.8em !important;
      }
      select.form-control option{
        color:#0B1B38;
      }

      /* Panel “Resumen” más ejecutivo */
      .card-body h6, .card-body small{
        color: rgba(255,255,255,.75) !important;
        font-weight: 900 !important;
      }
      .card-body hr{
        border-top: 1px solid rgba(255,255,255,.12) !important;
      }

      /* Bloques de estado (estudios, ejecución, etc.) */
      .card-body .border.rounded{
        border: 1px solid rgba(255,255,255,.12) !important;
        background: rgba(255,255,255,.06) !important;
        border-radius: 16px !important;
      }

      /* Mapa frame */
      #map-container{
        border: 1px solid rgba(255,255,255,.12);
        border-radius: var(--r-xl);
        overflow:hidden;
        box-shadow: var(--shadow);
        background: rgba(255,255,255,.05);
      }
      #contenido-mapa{
        background:
          radial-gradient(800px 220px at 10% 0%, rgba(96,165,250,.14), transparent 60%),
          radial-gradient(800px 220px at 90% 0%, rgba(167,139,250,.12), transparent 60%),
          rgba(0,0,0,.10);
      }

      /* SVG interacciones bonitas */
      svg polygon, svg path{
        transition: transform .15s ease, filter .15s ease, opacity .15s ease;
        cursor: pointer;
      }
      svg polygon:hover, svg path:hover{
        transform: translateY(-1px);
        filter: drop-shadow(0 10px 18px rgba(0,0,0,.35));
        opacity: .95;
      }
      /* Labels del SVG (cuando vienen como tspan) */
      svg text, svg tspan{
        fill: rgba(255,255,255,.85) !important;
        font-weight: 900 !important;
      }

      /* Indicadores derecha: cards compactas y premium */
      #indicadoresSecretaria .card{
        box-shadow: var(--shadow2);
      }

      /* Ajustes cards de color bootstrap para que se vean pro en dark */
      .bg-primary, .bg-success, .bg-info, .bg-secondary, .bg-warning{
        border: 1px solid rgba(255,255,255,.14) !important;
        box-shadow: var(--shadow2) !important;
      }
      .bg-warning{ color:#0B1B38 !important; }

      /* Modal pro */
      .modal-content{
        background: linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.06)) !important;
        border: 1px solid rgba(255,255,255,.14) !important;
        border-radius: 22px !important;
        box-shadow: var(--shadow);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
      }
      .modal-header{
        border-bottom: 1px solid rgba(255,255,255,.10) !important;
      }
      .modal-footer{
        border-top: 1px solid rgba(255,255,255,.10) !important;
      }
      .modal-body{
        background: rgba(0,0,0,.10);
      }

      /* Responsive: mapa sin scroll lateral */
      @media(max-width: 576px){
        #map-container{ height: 460px !important; }
        svg{ height: 460px !important; }
      }
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10">Informes Secretarias</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Información Secretarías Alcaldías</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <!-- Columna 1 -->
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="feather icon-info"></i> Resumen Información - Todas las Provincias</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3">$ Valores Financieros</h6>
                            <div class="mb-3">
                                <small>Valor Proyecto Total:</small>
                                <h5 style="color: var(--brand); font-weight:1000;">
                                  $<?php echo number_format($valorTotalInversion, 2, ',', '.'); ?>
                                </h5>
                            </div>

                            <hr>

                            <h6 class="mb-3"><i class="feather icon-list"></i> Estados de Proyectos</h6>

                            <?php if ($totalEstudiosPrevios > 0): ?>
                            <div class="text-center mb-3 p-2 border rounded">
                                <i class="feather icon-file-text" style="font-size: 20px; color: rgba(255,255,255,.75);"></i>
                                <h6 class="mt-1 mb-0">Estudios Previos</h6>
                                <h4 class="mb-0" style="color: rgba(255,255,255,.92); font-weight:1000;"><?php echo $totalEstudiosPrevios; ?></h4>
                            </div>
                            <?php endif; ?>

                            <?php if ($totalEnFormulacion > 0): ?>
                            <div class="text-center mb-3 p-2 border rounded">
                                <i class="feather icon-edit" style="font-size: 20px; color: var(--warn);"></i>
                                <h6 class="mt-1 mb-0">En Formulación</h6>
                                <h4 class="mb-0" style="color: var(--warn); font-weight:1000;"><?php echo $totalEnFormulacion; ?></h4>
                            </div>
                            <?php endif; ?>

                            <?php if ($totalEnEjecucion > 0): ?>
                            <div class="text-center mb-3 p-2 border rounded">
                                <i class="feather icon-settings" style="font-size: 20px; color: var(--cyan);"></i>
                                <h6 class="mt-1 mb-0">En Ejecución</h6>
                                <h4 class="mb-0" style="color: var(--cyan); font-weight:1000;"><?php echo $totalEnEjecucion; ?></h4>
                            </div>
                            <?php endif; ?>

                            <?php if ($totalTerminados > 0): ?>
                            <div class="text-center mb-3 p-2 border rounded">
                                <i class="feather icon-check" style="font-size: 20px; color: var(--brand);"></i>
                                <h6 class="mt-1 mb-0">Terminados</h6>
                                <h4 class="mb-0" style="color: var(--brand); font-weight:1000;"><?php echo $totalTerminados; ?></h4>
                            </div>
                            <?php endif; ?>

                            <?php if ($totalEntregado > 0): ?>
                            <div class="text-center p-2 border rounded">
                                <i class="feather icon-check-circle" style="font-size: 20px; color: var(--ok);"></i>
                                <h6 class="mt-1 mb-0">Entregado</h6>
                                <h4 class="mb-0" style="color: var(--ok); font-weight:1000;"><?php echo $totalEntregado; ?></h4>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna 2: Mapa -->
                <div class="col-xl-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between w-100">
                              <h5 class="mb-0"><i class="feather icon-map"></i> Mapa de Actividades - Todas las Provincias</h5>
                              <div class="card-header-right">
                                  <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#modalGeocalizacion">
                                    <i class="feather icon-map-pin"></i> Geolocalización
                                  </button>
                              </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($isUsuarioMunicipal && !empty($municipioUsuario)): ?>
                                <div id="map-container" style="height: 600px; position: relative;">
                                    <?php
                                    include_once "admin/include/georeferenciacion.php";
                                    include_once './admin/classes/Colombia.php';

                                    $codigoTodos = Util::codigoTodos();
                                    $departamentoEstatico = Util::getDepartamentoPrincipal();

                                    $municipioInfo = Ciudad::getInformacionCiudad(['codigo_muncipio' => $municipioUsuario]);
                                    $informacionMunicipio = $municipioInfo['output']['response'][0] ?? null;
                                    $nombreMunicipio = $informacionMunicipio['municipio'] ?? 'Municipio';

                                    error_log("===== DEBUG MAPA =====");
                                    error_log("Secretaría en mapa: " . $secretariaSeleccionada);

                                    if ($secretariaSeleccionada > 0) {
                                        $arr = [
                                            'codigo_departamento' => $departamentoEstatico,
                                            'codigo_municipio' => $municipioUsuario,
                                            'tbl_secretarias_id' => $secretariaSeleccionada
                                        ];
                                        $dataVeredas = Colombia::calcularColoresDeProyectosPorveredasDeUnaAlcaldia($arr);
                                    } else {
                                        $arr = ['codigo_departamento' => $departamentoEstatico, 'codigo_municipio' => $municipioUsuario];
                                        $dataVeredas = Colombia::calcularColoresDeVisitasPorveredasDeUnaAlcaldia($arr);
                                    }
                                    $municipiosDepartamento = $dataVeredas['output']['response'] ?? [];
                                    ?>

                                    <div id="contenido-mapa" style="width: 100%; overflow-x: hidden; text-align: center; padding: 10px; box-sizing: border-box;">
                                        <?php $viewBoxActual = !empty($informacionMunicipio['viewbox_svg']) ? $informacionMunicipio['viewbox_svg'] : '0 45 1518.36 900'; ?>

                                        <svg id="b"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="<?= htmlspecialchars($viewBoxActual) ?>"
                                            stroke-width="1.2px"
                                            stroke="rgba(255,255,255,.18)"
                                            style="width: 100%; max-width: 1100px; height: 600px; display: block; margin: 0 auto;"
                                            preserveAspectRatio="xMidYMid meet">

                                            <?php foreach ($municipiosDepartamento as $value): ?>
                                                <g id="<?= $value['nombre_svg'] ?? $value['nombre_vereda'] ?>">
                                                    <?php if (!empty($value['points'])): ?>
                                                        <polygon points="<?= strtoupper($value['points']) ?>"
                                                            fill="<?= strtolower($value['color_calculado']) ?>"
                                                            fill-rule="evenodd"
                                                            data-name="<?= strtolower($value['nombre_vereda']) ?>"
                                                            data-tooltip-id="my-tooltip"
                                                            data-tippy-content="<?= strtolower($value['nombre_vereda']) ?>"
                                                            onClick="handleVeredaClick(this)"
                                                            data-vereda-id="<?= $value['vereda_id'] ?? $value['id'] ?>"
                                                            stroke-miterlimit="10" stroke-width="0.1px" />
                                                    <?php elseif (!empty($value['path'])): ?>
                                                        <path d="<?= $value['path'] ?>"
                                                            title="<?= strtoupper(str_replace("-", " ", $value['nombre_vereda'])) ?>"
                                                            style="fill:<?= strtolower($value['color_calculado']) ?>;"
                                                            data-tooltip-id="my-tooltip"
                                                            data-tippy-content="<?= strtolower($value['nombre_vereda']) ?>"
                                                            onClick="handleVeredaClick(this)"
                                                            data-vereda-id="<?= $value['vereda_id'] ?? $value['id'] ?>"
                                                            stroke-miterlimit="10" stroke-width="0.1px" />
                                                    <?php endif; ?>
                                                </g>
                                            <?php endforeach; ?>

                                            <?php foreach ($municipiosDepartamento as $value2): ?>
                                                <?php
                                                if (!empty($value2['tspan'])) {
                                                    echo str_replace(
                                                        '<tspan',
                                                        '<tspan style="fill: rgba(255,255,255,.88); font-family:IBM Plex Sans; font-weight:900; stroke-width:0.1px;"',
                                                        $value2['tspan']
                                                    );
                                                }
                                                ?>
                                            <?php endforeach; ?>
                                        </svg>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div id="map-container" style="height: 600px; position: relative;">
                                    <iframe
                                        src="<?php echo $mapa; ?>?source=proyectos"
                                        width="100%"
                                        height="100%"
                                        frameborder="0"
                                        style="border: 0;">
                                    </iframe>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna 3 -->
                <div class="col-xl-3 col-md-6">

                    <div class="card">
                        <div class="card-header">
                            <h5>Seleccionar Secretaría</h5>
                        </div>
                        <div class="card-body">
                            <select id="selectSecretaria" class="form-control" onchange="updateUrlSecretaria(this)">
                                <option value="">Seleccione una secretaría</option>
                                <?php foreach ($proyectosPorSecretaria as $sec): ?>
                                    <option value="<?php echo $sec['secretaria_id']; ?>" <?php echo ($secretariaSeleccionada == $sec['secretaria_id']) ? 'selected' : ''; ?>>
                                        <?php echo $sec['secretaria']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($isUsuarioMunicipal): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5>Seleccionar Vereda</h5>
                        </div>
                        <div class="card-body">
                            <select id="selectVereda" class="form-control" onchange="cambiarVereda(this.value)">
                                <option value="">Todas</option>
                                <?php
                                include_once './admin/classes/Vereda.php';
                                $veredasArr = Vereda::getAll(['municipio_id' => $municipioUsuario]);
                                if ($veredasArr['output']['valid']) {
                                    foreach ($veredasArr['output']['response'] as $vereda) {
                                        $selected = ($veredaSeleccionada == $vereda['id']) ? 'selected' : '';
                                        echo '<option value="' . $vereda['id'] . '" ' . $selected . '>' . htmlspecialchars($vereda['nombre_vereda']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h5>Seleccionar Provincia</h5>
                        </div>
                        <div class="card-body">
                            <select id="selectProvincia" class="form-control">
                                <option value="">Todos</option>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div id="indicadoresSecretaria">
                        <?php if ($secretariaSeleccionada > 0): ?>
                            <?php
                            $porcentajeEstudiosPrevios = $totalProyectos > 0 ? round(($totalEstudiosPrevios / $totalProyectos) * 100, 1) : 0;
                            $porcentajeFormulacion = $totalProyectos > 0 ? round(($totalEnFormulacion / $totalProyectos) * 100, 1) : 0;
                            $porcentajeEjecucion = $totalProyectos > 0 ? round(($totalEnEjecucion / $totalProyectos) * 100, 1) : 0;
                            $porcentajeTerminados = $totalProyectos > 0 ? round(($totalTerminados / $totalProyectos) * 100, 1) : 0;
                            $porcentajeEntregado = $totalProyectos > 0 ? round(($totalEntregado / $totalProyectos) * 100, 1) : 0;
                            ?>

                            <div class="card mb-3" style="background: linear-gradient(135deg, rgba(96,165,250,.22), rgba(167,139,250,.16)) !important;">
                                <div class="card-body p-2 text-center">
                                    <h4 class="mb-0" style="color:#fff; font-weight:1000;"><?php echo number_format($totalProyectos); ?></h4>
                                    <small style="color: rgba(255,255,255,.88); font-weight:900;">TOTAL PROYECTOS</small>
                                    <br><small style="color: rgba(255,255,255,.70);"><?php echo $veredaSeleccionada > 0 ? 'Vereda seleccionada' : 'Todas las veredas'; ?></small>
                                </div>
                            </div>

                            <?php if ($totalTerminados > 0 || $totalEntregado > 0): ?>
                            <div class="card mb-3" style="background: linear-gradient(135deg, rgba(52,211,153,.22), rgba(34,211,238,.10)) !important;">
                                <div class="card-body p-2 text-center">
                                    <h4 class="mb-0" style="color:#fff; font-weight:1000;"><?php echo number_format($totalTerminados + $totalEntregado); ?></h4>
                                    <small style="color: rgba(255,255,255,.88); font-weight:900;">Terminados (<?php echo $porcentajeTerminados + $porcentajeEntregado; ?>%)</small>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($totalEnEjecucion > 0): ?>
                            <div class="card mb-3" style="background: linear-gradient(135deg, rgba(251,191,36,.30), rgba(96,165,250,.10)) !important;">
                                <div class="card-body p-2 text-center">
                                    <h4 class="mb-0" style="color:#0B1B38; font-weight:1000;"><?php echo number_format($totalEnEjecucion); ?></h4>
                                    <small style="color:#0B1B38; font-weight:1000;">En ejecución (<?php echo $porcentajeEjecucion; ?>%)</small>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="card mb-3" style="background: linear-gradient(135deg, rgba(34,211,238,.22), rgba(96,165,250,.12)) !important;">
                                <div class="card-body p-2 text-center">
                                    <h4 class="mb-0" style="color:#fff; font-weight:1000;"><?php echo number_format($totalEnFormulacion); ?></h4>
                                    <small style="color: rgba(255,255,255,.88); font-weight:900;">En formulación (<?php echo $porcentajeFormulacion; ?>%)</small>
                                </div>
                            </div>

                            <?php if ($totalEstudiosPrevios > 0): ?>
                            <div class="card mb-3" style="background: linear-gradient(135deg, rgba(255,255,255,.16), rgba(167,139,250,.10)) !important;">
                                <div class="card-body p-2 text-center">
                                    <h4 class="mb-0" style="color:#fff; font-weight:1000;"><?php echo number_format($totalEstudiosPrevios); ?></h4>
                                    <small style="color: rgba(255,255,255,.88); font-weight:900;">Estudios Previos (<?php echo $porcentajeEstudiosPrevios; ?>%)</small>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="card text-center mb-3" style="background: linear-gradient(135deg, rgba(52,211,153,.22), rgba(52,211,153,.10)) !important;">
                                <div class="card-body p-2">
                                    <h5 class="mb-0" style="color:#fff; font-weight:1000;">
                                      $<?php echo number_format($valorTotalInversion, 0, ',', '.'); ?>
                                    </h5>
                                    <small style="color: rgba(255,255,255,.85); font-weight:900;">Inversión Total</small>
                                </div>
                            </div>

                        <?php else: ?>
                            <div class="text-center py-5" style="color: rgba(255,255,255,.70);">
                                <i class="feather icon-info" style="font-size: 48px; color: rgba(255,255,255,.75);"></i>
                                <p class="mt-3" style="font-weight:900;">Seleccione una secretaría para ver los indicadores</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <?php include 'admin/include/footer.php'; ?>
    <?php include 'admin/include/gerenic_script.php'; ?>

    <script src="assets/js/pcoded.min.js"></script>

    <script>
        $(window).on('load', function() {
            $('.loader-bg').fadeOut('slow', function() { $(this).remove(); });
        });
        setTimeout(function() {
            $('.loader-bg').fadeOut('slow', function() { $(this).remove(); });
        }, 2000);

        $(document).ready(function() {
            $('.dropdown-toggle').dropdown();
            $('.drp-user .dropdown-toggle').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).next('.dropdown-menu').toggle();
            });
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.drp-user').length) {
                    $('.drp-user .dropdown-menu').hide();
                }
            });
        });

        const proyectosPorSecretaria = <?php echo json_encode($proyectosPorSecretaria); ?>;
        const isUsuarioMunicipal = <?php echo $isUsuarioMunicipal ? 'true' : 'false'; ?>;
        const municipioUsuario = '<?php echo $municipioUsuario; ?>';
        const departamentoUsuario = '<?php echo $departamentoEstatico ?? "68"; ?>';

        function cambiarProvincia(provincia) {
            const url = new URL(window.location.href);
            url.searchParams.set('provincia', provincia);
            window.location.href = url.toString();
        }

        function updateUrlSecretaria(select) {
            const secretariaId = select.value;
            const url = new URL(window.location.href);
            url.searchParams.set('secretaria', secretariaId);
            url.searchParams.delete('vereda');
            window.location.href = url.toString();
        }

        function cambiarVereda(veredaId) {
            const url = new URL(window.location.href);
            const secretariaId = document.getElementById('selectSecretaria').value;

            if (secretariaId) url.searchParams.set('secretaria', secretariaId);
            if (veredaId) url.searchParams.set('vereda', veredaId);
            else url.searchParams.delete('vereda');

            window.location.href = url.toString();
        }

        function handleVeredaClick(element) {
            const veredaId = element.getAttribute('data-vereda-id');
            const secretariaId = document.getElementById('selectSecretaria')?.value || '';

            if (veredaId && secretariaId) {
                const url = `municipios_secretaria_informacion_alcalde.php?mun=${municipioUsuario}&dep=${departamentoUsuario}&secretaria=${secretariaId}&vereda=${veredaId}`;
                window.location.href = url;
            } else if (veredaId) {
                cambiarVereda(veredaId);
            }
        }
    </script>

    <!-- Modal de Geolocalización -->
    <div class="modal fade" id="modalGeocalizacion" tabindex="-1" role="dialog" aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, rgba(96,165,250,.22), rgba(167,139,250,.16)) !important;">
                    <h5 class="modal-title text-white" id="modalGeocalizacionTitle" style="font-weight:1000;">
                        <i class="feather icon-map-pin"></i> Geolocalización - <?php echo $nombreMunicipio ?? 'Municipio'; ?>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div id="map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="feather icon-x"></i> Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Maps JavaScript API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMapAlcalde"></script>

    <script>
        let mapAlcalde;
        let informacionMapaAlcaldias = [];

        function initMapAlcalde() {
            if (typeof google !== 'undefined' && google.maps) {
                const initialLocation = { lat: 7.0830880750303935, lng: -73.02794598535458 };

                mapAlcalde = new google.maps.Map(document.getElementById("map"), {
                    center: initialLocation,
                    zoom: 12,
                });

                if (informacionMapaAlcaldias.length > 0) {
                    const bounds = new google.maps.LatLngBounds();

                    informacionMapaAlcaldias.forEach(point => {
                        if (point.latitud && point.longitud) {
                            const position = { lat: parseFloat(point.latitud), lng: parseFloat(point.longitud) };

                            const marker = new google.maps.Marker({
                                position,
                                map: mapAlcalde,
                                icon: {
                                    url: point.icono ? point.icono : "assets/iconos/maps/geo.png",
                                    scaledSize: new google.maps.Size(50, 50)
                                },
                                title: `${point.nombre_vereda || 'Vereda'} - ${point.proyecto || 'Proyecto'}`
                            });

                            bounds.extend(position);

                            const infoWindow = new google.maps.InfoWindow({
                                content: `
                                    <div style="max-width: 300px;">
                                        <h5 style="margin-bottom: 10px; color: #1a5f2a;">${point.proyecto || 'Proyecto'}</h5>
                                        <p><strong>Vereda:</strong> ${point.nombre_vereda || 'N/A'}</p>
                                        <p><strong>Secretaría:</strong> ${point.secretaria || 'N/A'}</p>
                                        <p><strong>Estado:</strong> ${point.estado || 'N/A'}</p>
                                        <p><strong>Valor:</strong> $${parseFloat(point.valor_proyecto || 0).toLocaleString('es-CO')}</p>
                                        <p><strong>Observaciones:</strong> ${point.observaciones || 'Sin observaciones'}</p>
                                    </div>
                                `
                            });

                            marker.addListener("click", () => infoWindow.open(mapAlcalde, marker));
                        }
                    });

                    if (informacionMapaAlcaldias.length > 1) mapAlcalde.fitBounds(bounds);
                }
            } else {
                console.error('Google Maps API no está disponible.');
            }
        }

        function mostrarMapaConInfoAlcaldia() {
            const secretariaId = document.getElementById('selectSecretaria')?.value || '';

            $.ajax({
                data: {
                    op: 'getmapainformacionalcaldia',
                    municipioId: municipioUsuario,
                    secretariaId: secretariaId
                },
                type: "GET",
                dataType: "json",
                url: "admin/ajax/rqst.php",
                beforeSend: function() {
                    $('#map').html('<div class="text-center p-5" style="color:#fff;"><i class="feather icon-loader fa-spin"></i> Cargando mapa...</div>');
                },
                success: function(data) {
                    if (data.output && data.output.valid) {
                        informacionMapaAlcaldias = data.output.response || [];
                        initMapAlcalde();
                    } else {
                        informacionMapaAlcaldias = [];
                        initMapAlcalde();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar datos del mapa:', error);
                    informacionMapaAlcaldias = [];
                    initMapAlcalde();
                }
            });
        }

        $('#modalGeocalizacion').on('shown.bs.modal', function() {
            mostrarMapaConInfoAlcaldia();
        });
    </script>

</body>
</html>
