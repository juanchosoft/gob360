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

    $pos = strpos($final, "?");
    if ($pos !== false) $final = substr($final, 0, $pos);
    return $final;
}

require_once './admin/include/generic_classes.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
include './admin/db/colores.php';
include './admin/classes/MainAlcalde.php';
include './admin/classes/DetalleAlcalde.php';
include './admin/classes/CuentaAlcalde.php';
include './admin/classes/MunnovisitadosAlcalde.php';

// Obtener permisos
$permissions = PagePermissions::crudForCurrentPage();

// Identificar tipo de usuario
$userType = SessionData::getUserType();
$tiposUsuarioMunicipal = ['Alcalde', 'Auxiliar_Alcalde', 'Secretario_Despacho', 'Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);
$municipioUsuario = $isUsuarioMunicipal ? SessionData::getCodigoMunicipio() : '';

// Si es usuario municipal, pasamos su código de municipio para filtrar
$filtroMunicipio = $municipioUsuario ?: null;

// Obtener visitas
$arrVisitas = MainAlcalde::getSoloVisitas($filtroMunicipio ? ['codigo_municipio' => $filtroMunicipio] : null);
$visitas = $arrVisitas['output']['total_visitas'] ?? 0;

// Obtener veredas/municipios visitados/restantes
$arrVeredas = MainAlcalde::getSoloMunicipiosVisitados($filtroMunicipio ? ['codigo_municipio' => $filtroMunicipio] : null);
$veredasVisitadas = $arrVeredas['output']['veredas_visitadas'] ?? 0;
$veredasTotales = $arrVeredas['output']['veredas_totales'] ?? 0;
$veredasRestantes = $arrVeredas['output']['veredas_restantes'] ?? 0;

// Depto
$departamento = new Departamento();
$santander = $departamento->getAll(["id" => 21]);
$santander = $santander["output"]["response"]["0"];

$code = null;
$mapa = null;

if (!isset($_GET['depto_id'])) {
    $_GET['depto_id'] = Util::getIdentificadorDepartamentoPrincipal();
}

if (in_array($_GET['depto_id'], [1, 12, 21])) {
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        /* =====================================================
           GOVTECH WOW – DARK GLASS (igual que tus otras vistas)
           ===================================================== */
        :root{
            --bg0:#070A12;
            --bg1:#0B1222;

            --card: rgba(255,255,255,.06);
            --card2: rgba(255,255,255,.085);

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

            --r-xl:18px;
            --r-lg:16px;

            --shadow: 0 20px 60px rgba(0,0,0,.35);
            --shadow2: 0 14px 40px rgba(0,0,0,.25);

            /* safe header */
            --safe-top: 96px;
        }

        body.dashboard-body{
            background:
                radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
                radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
                radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
                linear-gradient(180deg, var(--bg0), var(--bg1)) !important;
            color: var(--txt);
            overflow-x:hidden;
        }

        .pcoded-main-container{ background: transparent !important; }
        .pcoded-content{
            padding: calc(var(--safe-top) + 16px) 16px 16px !important;
        }
        @media(min-width:768px){
            :root{ --safe-top:112px; }
            .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; }
        }
        @media(min-width:1200px){
            :root{ --safe-top:120px; }
            .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; }
        }

        /* breadcrumb readable */
        .page-header h5, .breadcrumb .breadcrumb-item, .breadcrumb .breadcrumb-item a{
            color: var(--txt) !important;
        }
        .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }

        /* TOP header premium */
        .page-header .page-block{
            border:1px solid var(--stroke);
            background: rgba(255,255,255,.05);
            border-radius: 16px;
            padding: 14px 14px;
            box-shadow: var(--shadow2);
            overflow:hidden;
            position: relative;
        }
        .page-header .page-block:before{
            content:"";
            position:absolute; inset:-2px;
            background:
                radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.25), transparent 65%),
                radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.18), transparent 65%);
            pointer-events:none;
        }
        .page-header .page-block > *{ position:relative; z-index:1; }

        /* Card pro (glass) */
        .card.au-card, .card.kpi-card{
            border: 1px solid var(--stroke) !important;
            border-radius: var(--r-xl) !important;
            background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04)) !important;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }
        .card.au-card:before, .card.kpi-card:before{
            content:"";
            position:absolute; inset:-2px;
            background:
                radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.25), transparent 65%),
                radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.18), transparent 65%),
                radial-gradient(520px 220px at 50% 120%, rgba(24,255,109,.08), transparent 60%);
            pointer-events:none;
        }
        .card.au-card > *, .card.kpi-card > *{ position:relative; z-index:1; }

        .card.au-card .card-header{
            background: rgba(0,0,0,.14) !important;
            border-bottom: 1px solid var(--stroke) !important;
            padding: 16px 18px !important;
            text-align:center;
        }
        .card.au-card .card-body{
            padding: 16px 18px !important;
        }

        .au-title-lg{
            font-size: 1.25rem;
            font-weight: 1000;
            letter-spacing: .2px;
            margin:0;
            color: var(--txt);
        }
        .au-sub{ color: var(--muted); font-size: .9rem; margin-top:6px; }

        .au-badge{
            padding: 6px 10px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 900;
            background: rgba(79,124,255,.14);
            color: rgba(255,255,255,.92);
            border: 1px solid rgba(79,124,255,.25);
            white-space: nowrap;
        }

        /* KPI */
        .kpi-card{ text-align:center; }
        .kpi-card .card-body{ padding: 16px 14px !important; }

        .kpi-ico{
            width: 54px; height:54px;
            border-radius: 16px;
            display: inline-flex;
            align-items:center;
            justify-content:center;
            border: 1px solid var(--stroke2);
            background: rgba(0,0,0,.22);
            user-select:none;
            box-shadow: 0 12px 26px rgba(0,0,0,.25);
        }
        .kpi-ico i{ font-size: 26px !important; color: var(--txt) !important; }

        .kpi-label{ font-size: .82rem; color: var(--muted); font-weight: 900; margin-top: 10px; }
        .kpi-value{ font-size: 1.55rem; font-weight: 1000; color: var(--txt); line-height:1.1; margin-top:4px; }
        .kpi-hint{ font-size: .78rem; color: var(--muted2); margin-top:6px; }

        .kpi-card{ position:relative; }
        .kpi-card::after{
            content:"";
            position:absolute; top:0; left:0;
            height:4px; width:100%;
            background: rgba(255,255,255,.08);
        }
        .kpi-blue::after{ background:#3B82F6; }
        .kpi-amber::after{ background:#F59E0B; }
        .kpi-purple::after{ background:#A855F7; }
        .kpi-green::after{ background:#22C55E; }

        .kpi-blue .kpi-ico{ border-color: rgba(59,130,246,.28); }
        .kpi-amber .kpi-ico{ border-color: rgba(245,158,11,.28); }
        .kpi-purple .kpi-ico{ border-color: rgba(168,85,247,.28); }
        .kpi-green .kpi-ico{ border-color: rgba(34,197,94,.28); }

        /* Contenedores mapa/charts (dark glass) */
        .map-shell{
            border-radius: var(--r-xl);
            border: 1px solid var(--stroke);
            background: rgba(0,0,0,.18);
        }
        #containerVeredas, #containerMunicipios{
            border-radius: var(--r-lg);
            border: 1px solid var(--stroke);
            background: rgba(0,0,0,.18);
        }

        /* Apex tooltip (oscuro, legible) */
        .apexcharts-tooltip{
            background: rgba(0,0,0,.88) !important;
            border: 1px solid rgba(255,255,255,.14) !important;
            color: rgba(255,255,255,.92) !important;
            box-shadow: 0 18px 40px rgba(0,0,0,.45) !important;
        }
        .apexcharts-tooltip-title{
            background: rgba(255,255,255,.06) !important;
            border-bottom: 1px solid rgba(255,255,255,.10) !important;
            color: rgba(255,255,255,.92) !important;
        }
        .apexcharts-xaxistooltip, .apexcharts-yaxistooltip{
            background: rgba(0,0,0,.88) !important;
            border: 1px solid rgba(255,255,255,.14) !important;
            color: rgba(255,255,255,.92) !important;
        }

        /* =====================================================
           MODALES (dark glass, lindos como las otras)
           ===================================================== */
        .modal-backdrop{ background:#000 !important; }
        .modal-backdrop.show{ opacity:.90 !important; }

        .modal-content{
            border-radius: 18px !important;
            border: 1px solid var(--stroke) !important;
            background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(0,0,0,.35)) !important;
            color: var(--txt) !important;
            box-shadow: var(--shadow);
            overflow:hidden;
        }
        .modal-header{
            background: linear-gradient(135deg, rgba(79,124,255,.55), rgba(155,92,255,.35)) !important;
            border-bottom: 1px solid rgba(255,255,255,.12) !important;
        }
        .modal-title{ font-weight: 1000 !important; color:#fff !important; }
        .btn-close{ filter: invert(1) grayscale(1); opacity:.95; }

        /* BS4 close fallback */
        .close, .close span{ color:#fff !important; opacity:1 !important; text-shadow:none !important; }

        .modal-footer{
            background: rgba(0,0,0,.18) !important;
            border-top: 1px solid rgba(255,255,255,.12) !important;
        }

        /* Table in modal: dark + hover */
        .modal .table{ color: var(--txt) !important; margin-bottom:0 !important; }
        .modal .table thead th{
            background: rgba(255,255,255,.06) !important;
            border-bottom: 1px solid rgba(255,255,255,.12) !important;
            color: rgba(255,255,255,.90) !important;
            white-space: nowrap;
        }
        .modal .table tbody td{
            border-top: 1px solid rgba(255,255,255,.08) !important;
            color: rgba(255,255,255,.88) !important;
        }
        .modal .table tbody tr:hover td{
            background: rgba(255,255,255,.06) !important;
        }

        .modal .btn{
            border-radius: 14px !important;
            padding: 10px 18px !important;
            font-weight: 900 !important;
            border: 1px solid var(--stroke2) !important;
            box-shadow: 0 10px 24px rgba(0,0,0,.25);
        }
        .modal .btn-secondary{
            background: rgba(255,255,255,.06) !important;
            color: var(--txt) !important;
        }

        /* evita hover raro en svg */
        .santander path:hover, .santander polygon:hover{
            transform: none !important;
            filter: none !important;
            stroke: none !important;
            fill: inherit !important;
            pointer-events: auto !important;
        }
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

            <div class="page-header mb-3">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h5 class="m-b-10 mb-0">Dashboard Alcalde</h5>
                                    <div class="small" style="color: var(--muted);">
                                        <?php echo $isUsuarioMunicipal ? 'Vista municipal (filtrada por tu alcaldía)' : 'Vista departamental (administración)'; ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="au-badge"><?php echo date('d/m/Y H:i'); ?></span>
                                    <?php include './admin/include/btn_back.php'; ?>
                                </div>
                            </div>

                            <ul class="breadcrumb mt-2">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Información General Alcalde</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card kpi-card kpi-blue h-100">
                        <div class="card-body">
                            <div class="kpi-ico" role="button"
                                 data-bs-toggle="modal" data-bs-target="#modalVeredasVisitadas"
                                 data-toggle="modal" data-target="#modalVeredasVisitadas"
                                 title="<?php echo $isUsuarioMunicipal ? 'Ver veredas visitadas' : 'Ver municipios visitados'; ?>">
                                <i class="feather icon-map-pin"></i>
                            </div>

                            <div class="kpi-label"><?php echo $isUsuarioMunicipal ? 'Visitas a Veredas' : 'Visitas a Municipios'; ?></div>
                            <div class="kpi-value"><?php echo (int)$veredasVisitadas; ?></div>
                            <div class="kpi-hint"><?php echo $isUsuarioMunicipal ? 'Dentro del municipio' : 'Dentro del departamento'; ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card kpi-card kpi-amber h-100">
                        <div class="card-body">
                            <div class="kpi-ico" role="button"
                                 data-bs-toggle="modal" data-bs-target="#modalVeredasRestantes"
                                 data-toggle="modal" data-target="#modalVeredasRestantes"
                                 title="<?php echo $isUsuarioMunicipal ? 'Ver veredas restantes' : 'Ver municipios restantes'; ?>">
                                <i class="feather icon-navigation"></i>
                            </div>

                            <div class="kpi-label"><?php echo $isUsuarioMunicipal ? 'Veredas Restantes' : 'Municipios Restantes'; ?></div>
                            <div class="kpi-value"><?php echo (int)$veredasRestantes; ?></div>
                            <div class="kpi-hint"><?php echo $isUsuarioMunicipal ? 'Por visitar' : 'Pendientes por visita'; ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card kpi-card kpi-purple h-100">
                        <div class="card-body">
                            <a href="plan_desarrollo_alcalde.php" class="text-decoration-none" title="Ir a Metas Plan Desarrollo" style="color:inherit;">
                                <div class="kpi-ico"><i class="feather icon-file-text"></i></div>
                            </a>
                            <div class="kpi-label">Metas Plan Desarrollo</div>
                            <div class="kpi-value">Ver</div>
                            <div class="kpi-hint">Seguimiento de metas y avances</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card kpi-card kpi-green h-100">
                        <div class="card-body">
                            <div class="kpi-ico"><i class="feather icon-activity"></i></div>
                            <div class="kpi-label">Total de visitas</div>
                            <div class="kpi-value"><?php echo (int)$visitas; ?></div>
                            <div class="kpi-hint">Visitas registradas en el sistema</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="row g-3">
                <!-- Mapa -->
                <div class="col-12 col-lg-7">
                    <div class="card au-card h-100">
                        <div class="card-header">
                            <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                <i class="feather icon-map" style="font-size: 34px; color: var(--info);"></i>
                                <h5 class="au-title-lg mt-2" style="font-size: 1.35rem;">
                                    <?php
                                    if ($isUsuarioMunicipal && !empty($municipioUsuario)) {
                                        $municipioInfo = Ciudad::getInformacionCiudad(['codigo_muncipio' => $municipioUsuario]);
                                        $infoMunicipio = $municipioInfo['output']['response'][0] ?? null;
                                        echo 'Mapa - ' . ($infoMunicipio['municipio'] ?? 'Municipio');
                                    } else {
                                        echo 'Mapa Santander';
                                    }
                                    ?>
                                </h5>
                                <div class="au-sub">
                                    <?php echo $isUsuarioMunicipal ? 'Haz clic en una vereda para ver su estado' : 'Haz clic en un municipio para ver su estado'; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <section class="content" style="<?php echo isset($_GET["route_map"]) ? "padding: 0rem !important" : "" ?>">
                                <div class="container-fluid px-0">
                                    <div class="row g-0">
                                        <div class="col-12" style="position: static; overflow-x: auto;">
                                            <div id="contenidoTransformado" class="contenido-transformado map-shell p-2 p-md-3">

                                                <div class="cuerpoMapa w-12">
                                                    <?php if ($isUsuarioMunicipal && !empty($municipioUsuario)) : ?>
                                                        <?php
                                                        include_once "admin/include/georeferenciacion.php";
                                                        include_once './admin/classes/Colombia.php';

                                                        $departamentoEstatico = Util::getDepartamentoPrincipal();

                                                        $municipioInfo = Ciudad::getInformacionCiudad(['codigo_muncipio' => $municipioUsuario]);
                                                        $informacionMunicipio = $municipioInfo['output']['response'][0] ?? null;

                                                        if (!isset($pilar)) { $pilar = null; }

                                                        $arr = ['codigo_departamento' => $departamentoEstatico, 'codigo_municipio' => $municipioUsuario, 'pilar' => $pilar];
                                                        $dataVeredas = Colombia::calcularColoresDeVisitasPorveredasDeUnaAlcaldia($arr);
                                                        $municipiosDepartamento = $dataVeredas['output']['response'] ?? [];
                                                        ?>

                                                        <div id="contenido-mapa" style="width: 100%; overflow-x: auto; text-align: center; padding: 6px;">
                                                            <?php include_once "admin/classes/rango_colores_visita_departamento_alcalde.php"; ?>

                                                            <?php $viewBoxActual = !empty($informacionMunicipio['viewbox_svg']) ? $informacionMunicipio['viewbox_svg'] : '0 45 1518.36 900'; ?>

                                                            <svg id="b"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="<?= htmlspecialchars($viewBoxActual) ?>"
                                                                stroke-width="1.2px"
                                                                stroke="#D3D3D3"
                                                                style="width: 100%; max-width: 900px; height: 520px; display: block; margin: 0 auto;"
                                                                preserveAspectRatio="xMidYMid meet">

                                                                <?php foreach ($municipiosDepartamento as $value): ?>
                                                                    <?php
                                                                    $departamento_codigo = $departamentoEstatico ?? '68';
                                                                    $municipio_codigo = $municipioUsuario ?? '';
                                                                    $url_vereda = getUrl() . "estado_municipios_alcalde.php?mun=" . $municipio_codigo . "&dep=" . $departamento_codigo;
                                                                    ?>
                                                                    <g id="<?= $value['nombre_svg'] ?>">
                                                                        <?php if (!empty($value['points'])): ?>
                                                                            <polygon points="<?= strtoupper($value['points']) ?>"
                                                                                fill="<?= strtolower($value['color_calculado']) ?>"
                                                                                fill-rule="evenodd"
                                                                                class="veredaClick"
                                                                                data-url="<?= $url_vereda ?>"
                                                                                data-name="<?= strtolower($value['nombre_vereda']) ?>"
                                                                                title="<?= strtoupper($value['nombre_vereda']) ?>"
                                                                                stroke-miterlimit="10" stroke-width="0.1px"
                                                                                style="cursor: pointer;" />
                                                                        <?php elseif (!empty($value['path'])): ?>
                                                                            <path d="<?= $value['path'] ?>"
                                                                                title="<?= strtoupper(str_replace("-", " ", $value['nombre_vereda'])) ?>"
                                                                                class="veredaClick"
                                                                                data-url="<?= $url_vereda ?>"
                                                                                data-name="<?= strtolower($value['nombre_vereda']) ?>"
                                                                                style="fill:<?= strtolower($value['color_calculado']) ?>; cursor: pointer;"
                                                                                stroke-miterlimit="10" stroke-width="0.1px" />
                                                                        <?php endif; ?>
                                                                    </g>
                                                                <?php endforeach; ?>

                                                                <?php foreach ($municipiosDepartamento as $value2): ?>
                                                                    <?php
                                                                    echo str_replace(
                                                                        '<tspan',
                                                                        '<tspan style="fill: black; font-family:IBM Plex Sans; stroke-width: 0.1px;"',
                                                                        $value2['tspan']
                                                                    );
                                                                    ?>
                                                                <?php endforeach; ?>
                                                            </svg>
                                                        </div>
                                                    <?php else : ?>
                                                        <?php include_once "admin/classes/rango_colores_visita_departamento_alcalde.php"; ?>
                                                        <?php if (!is_null($mapa)) : ?>
                                                            <?php if ($_GET['depto_id'] == 1) : ?>
                                                                <div class="antioquia munis"><?php include_once "admin/mapa/mapa.php"; ?></div>
                                                            <?php elseif ($_GET['depto_id'] == 12) : ?>
                                                                <div class="choco munis"><?php include_once "admin/mapa-choco/choco.php"; ?></div>
                                                            <?php else : ?>
                                                                <div class="santander munis"><?php require_once "admin/mapa-santander/mapa_alcalde_visita.php"; ?></div>
                                                            <?php endif ?>
                                                        <?php endif ?>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <!-- Gráficas -->
                <div class="col-12 col-lg-5">
                    <div class="card au-card h-100">
                        <div class="card-header">
                            <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                <i class="feather icon-bar-chart-2" style="font-size: 34px; color: var(--good);"></i>
                                <h5 class="au-title-lg mt-2" style="font-size: 1.35rem;">Estadísticas de Visitas</h5>
                                <div class="au-sub">Indicadores por periodo y cobertura</div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="mb-0" style="color: var(--txt); font-weight: 900;">
                                        <?php echo $isUsuarioMunicipal ? 'Visitas realizadas a veredas' : 'Visitas realizadas a municipios'; ?>
                                    </h6>
                                    <span class="au-badge">Resumen</span>
                                </div>
                                <div id="containerVeredas" style="height: 260px; width: 100%;"></div>
                            </div>

                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="mb-0" style="color: var(--txt); font-weight: 900;">
                                        <?php echo $isUsuarioMunicipal ? 'Visitas por mes a municipios' : 'Visitas por mes'; ?>
                                    </h6>
                                    <span class="au-badge">Mensual</span>
                                </div>
                                <div id="containerMunicipios" style="height: 260px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Modal Visitadas -->
    <div id="modalVeredasVisitadas" class="modal fade" tabindex="-1"
        aria-labelledby="modalVeredasVisitadasTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVeredasVisitadasTitle">
                        <?php echo $isUsuarioMunicipal ? 'Cantidad de visitas a veredas del municipio' : 'Cantidad de visitas a municipios del departamento'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <button type="button" class="close d-none" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body">
                    <?php
                    if ($isUsuarioMunicipal) {
                        $arrVisitadas = MunnovisitadosAlcalde::getVeredasVisitadas(null);
                        $isvalidVisitadas = $arrVisitadas['output']['valid'];
                        $arrVisitadas = $arrVisitadas['output']['response'];
                        $labelPrincipal = 'Vereda';
                    } else {
                        $arrVisitadas = MunnovisitadosAlcalde::getMunicipiosVisitados(null);
                        $isvalidVisitadas = $arrVisitadas['output']['valid'];
                        $arrVisitadas = $arrVisitadas['output']['response'];
                        $labelPrincipal = 'Municipio';
                    }
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col"><?php echo $labelPrincipal; ?></th>
                                    <th scope="col" class="text-center">Veces Visitado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($arrVisitadas) && isset($isvalidVisitadas) && $isvalidVisitadas && count($arrVisitadas) > 0) {
                                    foreach ($arrVisitadas as $item) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($isUsuarioMunicipal ? $item['nombre_vereda'] : $item['municipio']); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($item['veces_visitado']); ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <?php echo $isUsuarioMunicipal ? 'No hay veredas visitadas.' : 'No hay municipios visitados.'; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Restantes -->
    <div id="modalVeredasRestantes" class="modal fade" tabindex="-1"
        aria-labelledby="modalVeredasRestantesTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVeredasRestantesTitle">
                        <?php echo $isUsuarioMunicipal ? 'Veredas Restantes' : 'Municipios Restantes'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <button type="button" class="close d-none" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>

                <div class="modal-body">
                    <?php
                    if ($isUsuarioMunicipal) {
                        $arrRestantes = MunnovisitadosAlcalde::getAll(null);
                        $isvalidRestantes = $arrRestantes['output']['valid'];
                        $arrRestantes = $arrRestantes['output']['response'];
                        $labelRestantes = 'Vereda';
                    } else {
                        $arrRestantes = MunnovisitadosAlcalde::getMunicipiosNoVisitados(null);
                        $isvalidRestantes = $arrRestantes['output']['valid'];
                        $arrRestantes = $arrRestantes['output']['response'];
                        $labelRestantes = 'Municipio';
                    }
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col"><?php echo $labelRestantes; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($arrRestantes) && isset($isvalidRestantes) && $isvalidRestantes && count($arrRestantes) > 0) {
                                    foreach ($arrRestantes as $item) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($isUsuarioMunicipal ? $item['nombre_vereda'] : $item['municipio']); ?></td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php echo $isUsuarioMunicipal ? 'Todas las veredas han sido visitadas.' : 'Todos los municipios han sido visitados.'; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Variables de sesión para JS -->
    <input type="hidden" id="municipioUsuario" value="<?php echo $municipioUsuario; ?>">
    <input type="hidden" id="tipoUsuario" value="<?php echo $userType; ?>">
    <input type="hidden" id="isUsuarioMunicipal" value="<?php echo $isUsuarioMunicipal ? '1' : '0'; ?>">

    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script src="assets/js/plugins/prism.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>

    <script src="admin/js/estado_general_alcalde.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <script>
        // ✅ Modales: compatibilidad BS4/BS5 (abre/cierra siempre)
        (function () {
            function showModalByEl(el){
                if (!el) return;
                if (window.bootstrap && window.bootstrap.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(el, {backdrop:true, keyboard:true, focus:true}).show();
                    return;
                }
                if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
                    jQuery(el).modal('show');
                }
            }
            function hideModalByEl(el){
                if (!el) return;
                if (window.bootstrap && window.bootstrap.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(el).hide();
                    return;
                }
                if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
                    jQuery(el).modal('hide');
                }
            }

            document.addEventListener('click', function(e){
                const bs5 = e.target.closest('[data-bs-toggle="modal"][data-bs-target]');
                if (bs5 && !(window.bootstrap && window.bootstrap.Modal)) {
                    e.preventDefault();
                    const id = (bs5.getAttribute('data-bs-target') || '').replace('#','');
                    showModalByEl(document.getElementById(id));
                }

                const bs4 = e.target.closest('[data-toggle="modal"][data-target]');
                if (bs4 && !(window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function')) {
                    e.preventDefault();
                    const id = (bs4.getAttribute('data-target') || '').replace('#','');
                    showModalByEl(document.getElementById(id));
                }

                const closeBtn = e.target.closest('[data-bs-dismiss="modal"],[data-dismiss="modal"]');
                if (closeBtn && !(window.bootstrap && window.bootstrap.Modal) && !(window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function')) {
                    e.preventDefault();
                    hideModalByEl(closeBtn.closest('.modal'));
                }
            }, true);

            document.addEventListener('hidden.bs.modal', function () {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                const bd = document.querySelector('.modal-backdrop');
                if (bd) bd.remove();
            }, true);
        })();
    </script>

    <script>
        // Click en mapa veredas
        document.addEventListener("DOMContentLoaded", function() {
            const veredasElements = document.querySelectorAll(".veredaClick");
            veredasElements.forEach(function(el) {
                el.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const url = this.getAttribute("data-url");
                    if (url) window.location.href = url;
                });
            });
        });
    </script>

</body>
</html>
