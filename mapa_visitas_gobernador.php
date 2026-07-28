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
        return $final;
    } else {
        return $final;
    }
}

require_once './admin/include/generic_classes.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
include './admin/db/colores.php';
include './admin/classes/Main.php';
include './admin/classes/Detalle.php';
include './admin/classes/Cuenta.php';
include './admin/classes/Cuentapro.php';
include './admin/classes/Secreinversion.php';
include './admin/classes/Munnovisitados.php';

// Obtener permisos
$permissions = PagePermissions::crudForCurrentPage();

//Obtener solo VISITAS
$arrVisitas = Main::getSoloVisitas(null);
$visitas = $arrVisitas['output']['total_visitas'] ?? 0;

//Obtener solo municipios visitados
$arrMunicipios = Main::getSoloMunicipiosVisitados(null);
$municipios = $arrMunicipios['output']['municipios_visitados'] ?? 0;

//Calcular los restantes
$visitarpendiente = 87 - $municipios;

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

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<body class="dashboard-premium">
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

    <style>
        :root{
            --gob-bg-0:#030814;
            --gob-bg-1:#071426;
            --gob-bg-2:#0a2232;
            --gob-panel:rgba(8,20,39,.78);
            --gob-panel-soft:rgba(255,255,255,.055);
            --gob-line:rgba(139,208,255,.14);
            --gob-line-strong:rgba(33,207,255,.30);
            --gob-text:#f6fbff;
            --gob-muted:rgba(225,239,255,.68);
            --gob-muted-2:rgba(225,239,255,.48);
            --gob-cyan:#24d7ff;
            --gob-blue:#2188ff;
            --gob-green:#38efb1;
            --gob-yellow:#ffc857;
            --gob-red:#ff6685;
            --gob-radius-xl:26px;
            --gob-radius-lg:20px;
            --gob-radius-md:15px;
            --gob-shadow:0 22px 70px rgba(0,0,0,.42);
            --gob-shadow-soft:0 14px 38px rgba(0,0,0,.30);
            --safe-top:104px;
        }

        html{ scroll-behavior:smooth; }
        html,body{ overflow-x:hidden !important; }

        body.dashboard-premium{
            min-height:100vh;
            color:var(--gob-text);
            background:
                radial-gradient(950px 500px at 4% 4%, rgba(33,136,255,.22), transparent 62%),
                radial-gradient(780px 430px at 96% 8%, rgba(36,215,255,.13), transparent 60%),
                radial-gradient(900px 520px at 50% 108%, rgba(56,239,177,.09), transparent 64%),
                linear-gradient(145deg,var(--gob-bg-0) 0%,var(--gob-bg-1) 47%,var(--gob-bg-2) 100%) !important;
            background-attachment:fixed !important;
        }

        body.dashboard-premium::before{
            content:"";
            position:fixed;
            inset:0;
            pointer-events:none;
            z-index:-1;
            opacity:.13;
            background-image:
                linear-gradient(rgba(255,255,255,.06) 1px,transparent 1px),
                linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px);
            background-size:56px 56px;
            mask-image:linear-gradient(to bottom,rgba(0,0,0,.85),transparent 88%);
        }

        .pcoded-main-container{ background:transparent !important; }
        .pcoded-main-container .pcoded-content{
            padding:calc(var(--safe-top) + 14px) 16px 28px !important;
        }

        @media (min-width:768px){
            :root{ --safe-top:112px; }
            .pcoded-main-container .pcoded-content{
                padding:calc(var(--safe-top) + 16px) 24px 34px !important;
            }
        }

        @media (min-width:1200px){
            :root{ --safe-top:118px; }
            .pcoded-main-container .pcoded-content{
                width:min(100%,1500px);
                margin:0 auto;
                padding:calc(var(--safe-top) + 18px) 36px 42px !important;
            }
        }

        /* Encabezado compacto */
        .page-header{
            margin-bottom:14px !important;
        }

        .page-header .page-block{
            padding:0 !important;
            border:0 !important;
            background:transparent !important;
            box-shadow:none !important;
        }

        .page-header h5{
            color:var(--gob-text) !important;
            font-size:13px;
            font-weight:900 !important;
            letter-spacing:.14em;
            text-transform:uppercase;
        }

        .breadcrumb{
            margin:4px 0 0;
            padding:0;
            background:transparent !important;
        }

        .breadcrumb-item,
        .breadcrumb-item a{
            color:var(--gob-muted) !important;
            font-size:12px;
        }

        .breadcrumb-item.active{ color:var(--gob-muted-2) !important; }

        /* Hero GOB360 */
        .gob360-dashboard-hero{
            position:relative;
            overflow:hidden;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:22px;
            min-height:205px;
            margin-bottom:18px;
            padding:24px clamp(18px,3vw,34px);
            border:1px solid var(--gob-line);
            border-radius:var(--gob-radius-xl);
            background:
                linear-gradient(120deg,rgba(9,26,51,.95),rgba(7,18,37,.74)),
                radial-gradient(580px 260px at 18% 10%,rgba(33,136,255,.24),transparent 65%);
            box-shadow:var(--gob-shadow);
            backdrop-filter:blur(18px);
        }

        .gob360-dashboard-hero::before{
            content:"";
            position:absolute;
            inset:0;
            pointer-events:none;
            background:
                radial-gradient(420px 210px at 86% 22%,rgba(36,215,255,.20),transparent 64%),
                linear-gradient(115deg,transparent 62%,rgba(255,255,255,.035));
        }

        .gob360-dashboard-hero::after{
            content:"";
            position:absolute;
            width:350px;
            height:350px;
            right:-150px;
            bottom:-210px;
            border-radius:50%;
            border:1px solid rgba(36,215,255,.18);
            box-shadow:
                0 0 0 45px rgba(36,215,255,.025),
                0 0 0 90px rgba(33,136,255,.018);
        }

        .gob360-dashboard-hero__content,
        .gob360-dashboard-hero__aside{
            position:relative;
            z-index:2;
        }

        .gob360-dashboard-hero__content{
            display:flex;
            align-items:center;
            gap:22px;
            min-width:0;
        }

        .gob360-dashboard-hero__logo{
            width:clamp(185px,19vw,285px);
            max-width:42vw;
            height:auto;
            flex:0 0 auto;
            display:block;
            border-radius:18px;
            filter:
                drop-shadow(0 16px 30px rgba(0,0,0,.42))
                drop-shadow(0 0 22px rgba(36,215,255,.24));
        }

        .gob360-dashboard-hero__eyebrow{
            display:inline-flex;
            align-items:center;
            gap:8px;
            margin:0 0 8px;
            color:var(--gob-cyan);
            font-size:12px;
            font-weight:950;
            letter-spacing:.16em;
            text-transform:uppercase;
        }

        .gob360-dashboard-hero__eyebrow::before{
            content:"";
            width:28px;
            height:2px;
            border-radius:999px;
            background:linear-gradient(90deg,var(--gob-cyan),var(--gob-blue));
            box-shadow:0 0 12px rgba(36,215,255,.55);
        }

        .gob360-dashboard-hero h1{
            margin:0;
            max-width:720px;
            color:#fff;
            font-size:clamp(25px,3.2vw,44px);
            line-height:1.05;
            font-weight:950;
            letter-spacing:-.035em;
        }

        .gob360-dashboard-hero p{
            max-width:720px;
            margin:10px 0 0;
            color:var(--gob-muted);
            font-size:clamp(13px,1.15vw,16px);
            line-height:1.55;
        }

        .gob360-dashboard-hero__aside{
            display:grid;
            gap:10px;
            width:min(260px,100%);
            flex:0 0 auto;
        }

        .hero-status{
            display:flex;
            align-items:center;
            gap:11px;
            padding:12px 14px;
            border:1px solid rgba(255,255,255,.11);
            border-radius:16px;
            background:rgba(1,8,21,.38);
            color:var(--gob-muted);
            font-size:12px;
            box-shadow:0 12px 25px rgba(0,0,0,.22);
        }

        .hero-status i{
            display:grid;
            place-items:center;
            width:35px;
            height:35px;
            border-radius:12px;
            color:var(--gob-cyan);
            background:rgba(36,215,255,.10);
            border:1px solid rgba(36,215,255,.18);
            font-size:16px;
        }

        .hero-status strong{
            display:block;
            color:#fff;
            font-size:14px;
            margin-bottom:1px;
        }

        /* KPI */
        .kpi-row{ margin-bottom:18px; }

        .kpi-card{
            --card-accent:var(--gob-cyan);
            position:relative;
            height:100%;
            overflow:hidden;
            border:1px solid var(--gob-line);
            border-radius:var(--gob-radius-lg);
            background:
                linear-gradient(145deg,rgba(255,255,255,.075),rgba(255,255,255,.025));
            box-shadow:var(--gob-shadow-soft);
            backdrop-filter:blur(14px);
            transition:transform .20s ease,border-color .20s ease,box-shadow .20s ease;
        }

        .kpi-row > div:nth-child(2) .kpi-card{ --card-accent:var(--gob-green); }
        .kpi-row > div:nth-child(3) .kpi-card{ --card-accent:var(--gob-blue); }
        .kpi-row > div:nth-child(4) .kpi-card{ --card-accent:var(--gob-yellow); }

        .kpi-card::before{
            content:"";
            position:absolute;
            inset:0 auto 0 0;
            width:3px;
            background:linear-gradient(to bottom,transparent,var(--card-accent),transparent);
            box-shadow:0 0 20px var(--card-accent);
            opacity:.9;
        }

        .kpi-card::after{
            content:"";
            position:absolute;
            width:180px;
            height:180px;
            right:-90px;
            top:-105px;
            border-radius:50%;
            background:var(--card-accent);
            opacity:.085;
            filter:blur(2px);
        }

        .kpi-card:hover{
            transform:translateY(-4px);
            border-color:color-mix(in srgb,var(--card-accent) 42%,transparent);
            box-shadow:0 24px 58px rgba(0,0,0,.42);
        }

        .kpi-body{
            position:relative;
            z-index:2;
            display:grid;
            grid-template-columns:62px minmax(0,1fr);
            align-items:center;
            gap:14px;
            min-height:132px;
            padding:18px;
        }

        .kpi-ico{
            width:62px;
            height:62px;
            display:grid;
            place-items:center;
            border-radius:20px;
            color:var(--card-accent) !important;
            background:color-mix(in srgb,var(--card-accent) 11%,transparent);
            border:1px solid color-mix(in srgb,var(--card-accent) 25%,transparent);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.10),
                0 12px 24px rgba(0,0,0,.24);
            cursor:pointer;
            transition:transform .2s ease,background .2s ease;
        }

        .kpi-ico:hover{ transform:scale(1.05); }

        .kpi-ico i{
            color:inherit !important;
            font-size:27px !important;
        }

        .kpi-meta{ min-width:0; }

        .kpi-title{
            margin:0;
            color:var(--gob-muted);
            font-size:11px;
            font-weight:900;
            letter-spacing:.10em;
            line-height:1.35;
            text-transform:uppercase;
        }

        .kpi-value{
            margin:6px 0 5px;
            color:#fff;
            font-size:clamp(28px,2.5vw,38px);
            font-weight:950;
            line-height:1;
            letter-spacing:-.035em;
        }

        .kpi-hint{
            margin:0;
            color:var(--gob-muted-2);
            font-size:12px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        /* Secciones */
        .section-card{
            height:100%;
            overflow:hidden;
            border:1px solid var(--gob-line);
            border-radius:var(--gob-radius-xl);
            background:var(--gob-panel);
            box-shadow:var(--gob-shadow);
            backdrop-filter:blur(16px);
        }

        .section-card .card-header{
            position:relative;
            padding:17px 20px;
            color:#fff !important;
            border-bottom:1px solid var(--gob-line);
            background:
                linear-gradient(135deg,rgba(33,136,255,.20),rgba(36,215,255,.06)) !important;
        }

        .section-card .card-header::after{
            content:"";
            position:absolute;
            left:20px;
            bottom:-1px;
            width:92px;
            height:2px;
            border-radius:999px;
            background:linear-gradient(90deg,var(--gob-cyan),var(--gob-blue));
            box-shadow:0 0 15px rgba(36,215,255,.50);
        }

        .section-title{
            display:flex;
            align-items:center;
            justify-content:flex-start;
            gap:11px;
        }

        .section-title i{
            display:grid;
            place-items:center;
            width:40px;
            height:40px;
            border-radius:14px;
            color:var(--gob-cyan) !important;
            background:rgba(36,215,255,.10);
            border:1px solid rgba(36,215,255,.17);
            font-size:19px !important;
        }

        .section-title h5{
            margin:0;
            color:#fff !important;
            font-size:17px !important;
            font-weight:950;
            letter-spacing:-.01em;
        }

        .section-card .card-body{ padding:18px; }

        .map-wrap{
            position:relative;
            min-height:585px;
            overflow:hidden;
            border:1px solid rgba(255,255,255,.13);
            border-radius:20px;
            padding:10px;
            background:
                radial-gradient(500px 260px at 50% 5%,rgba(33,136,255,.07),transparent 70%),
                linear-gradient(180deg,#f8fbff,#edf5fb);
            box-shadow:inset 0 1px 0 rgba(255,255,255,.8),0 18px 42px rgba(0,0,0,.28);
        }

        .map-wrap::before{
            content:"Mapa territorial interactivo";
            position:absolute;
            top:12px;
            left:14px;
            z-index:8;
            padding:7px 10px;
            border-radius:999px;
            color:#153153;
            background:rgba(255,255,255,.88);
            border:1px solid rgba(10,62,105,.12);
            font-size:10px;
            font-weight:900;
            letter-spacing:.08em;
            text-transform:uppercase;
            box-shadow:0 8px 18px rgba(9,37,71,.12);
            pointer-events:none;
        }

        .map-wrap .content,
        .map-wrap .container-fluid,
        .map-wrap .row,
        .map-wrap .col-12,
        .map-wrap #contenidoTransformado,
        .map-wrap .cuerpoMapa{
            min-height:555px;
        }

        .chart-block{
            min-height:286px;
            overflow:hidden;
            padding:14px;
            border:1px solid var(--gob-line);
            border-radius:19px;
            background:
                radial-gradient(340px 180px at 5% 0%,rgba(33,136,255,.12),transparent 65%),
                rgba(3,12,27,.62);
            box-shadow:inset 0 1px 0 rgba(255,255,255,.04),0 15px 30px rgba(0,0,0,.22);
        }

        .chart-block h6{
            margin:0 0 8px;
            color:#fff !important;
            font-size:13px;
            font-weight:900 !important;
            letter-spacing:.02em;
        }

        #containerProvincias,
        #containerMunicipios{
            overflow:hidden;
            border-radius:14px;
        }

        .highcharts-background{ fill:transparent !important; }
        .highcharts-title,
        .highcharts-subtitle,
        .highcharts-axis-title,
        .highcharts-axis-labels text,
        .highcharts-legend-item text,
        .highcharts-data-label text{
            fill:#eaf6ff !important;
            color:#eaf6ff !important;
            font-weight:700 !important;
        }
        .highcharts-grid-line{ stroke:rgba(255,255,255,.10) !important; }
        .highcharts-axis-line,
        .highcharts-tick{ stroke:rgba(255,255,255,.16) !important; }
        .highcharts-tooltip-box{
            fill:rgba(4,13,28,.96) !important;
            stroke:rgba(36,215,255,.30) !important;
        }
        .highcharts-tooltip text,
        .highcharts-tooltip span{
            fill:#fff !important;
            color:#fff !important;
        }

        /* Modales */
        .modal-backdrop.show{ opacity:.76; }
        .modal-content{
            overflow:hidden;
            color:var(--gob-text);
            border:1px solid rgba(36,215,255,.18) !important;
            border-radius:24px !important;
            background:linear-gradient(160deg,#0a1930,#06101f) !important;
            box-shadow:0 32px 100px rgba(0,0,0,.68);
        }

        .modal-header{
            padding:17px 20px;
            color:#fff;
            border-bottom:1px solid var(--gob-line);
            background:linear-gradient(135deg,rgba(33,136,255,.28),rgba(36,215,255,.08));
        }

        .modal-title{ font-weight:950; }
        .modal .btn-close{
            filter:invert(1) brightness(2);
            opacity:.9;
        }

        .modal-body{ padding:18px; background:transparent !important; }

        .modal .table{
            overflow:hidden;
            margin:0;
            color:var(--gob-text) !important;
            border-color:rgba(255,255,255,.10) !important;
            border-radius:14px;
        }

        .modal .table thead th{
            color:#fff !important;
            background:rgba(36,215,255,.09) !important;
            border-color:rgba(255,255,255,.10) !important;
            font-size:12px;
            font-weight:900;
            letter-spacing:.05em;
            text-transform:uppercase;
        }

        .modal .table td{
            color:rgba(244,250,255,.86) !important;
            background:rgba(255,255,255,.025) !important;
            border-color:rgba(255,255,255,.08) !important;
            font-size:13px !important;
        }

        .modal .table tbody tr:hover td{
            background:rgba(36,215,255,.055) !important;
        }

        .pagination{
            gap:6px;
            flex-wrap:wrap;
        }

        .pagination .page-link{
            min-width:38px;
            text-align:center;
            color:var(--gob-muted) !important;
            background:rgba(255,255,255,.05) !important;
            border:1px solid var(--gob-line) !important;
            border-radius:11px !important;
            font-weight:900;
        }

        .pagination .page-item.active .page-link{
            color:#03101d !important;
            background:linear-gradient(135deg,var(--gob-cyan),#79ecff) !important;
            border-color:transparent !important;
            box-shadow:0 10px 20px rgba(36,215,255,.20);
        }

        .pagination .page-link:focus{
            box-shadow:0 0 0 .22rem rgba(36,215,255,.16) !important;
        }

        /* Mapa: no deformar polígonos */
        .santander path:hover,
        .santander polygon:hover{
            transform:none !important;
            filter:none !important;
            stroke:none !important;
            fill:inherit !important;
            pointer-events:auto !important;
        }

        @media (max-width:1199.98px){
            .gob360-dashboard-hero{
                align-items:flex-start;
            }
            .gob360-dashboard-hero__aside{
                width:220px;
            }
            .map-wrap{ min-height:530px; }
        }

        @media (max-width:991.98px){
            .gob360-dashboard-hero{
                flex-direction:column;
                align-items:stretch;
            }
            .gob360-dashboard-hero__aside{
                width:100%;
                grid-template-columns:repeat(2,minmax(0,1fr));
            }
            .map-wrap{ min-height:520px; }
        }

        @media (max-width:767.98px){
            .gob360-dashboard-hero{
                min-height:unset;
                padding:21px 18px;
            }
            .gob360-dashboard-hero__content{
                flex-direction:column;
                align-items:flex-start;
                gap:16px;
            }
            .gob360-dashboard-hero__logo{
                width:min(250px,78vw);
                max-width:100%;
            }
            .gob360-dashboard-hero__aside{
                grid-template-columns:1fr;
            }
            .kpi-body{
                grid-template-columns:56px minmax(0,1fr);
                min-height:116px;
                padding:15px;
            }
            .kpi-ico{
                width:56px;
                height:56px;
                border-radius:17px;
            }
            .map-wrap{ min-height:470px; }
        }

        @media (max-width:575.98px){
            .pcoded-main-container .pcoded-content{
                padding-left:12px !important;
                padding-right:12px !important;
            }
            .section-card .card-body{ padding:12px; }
            .chart-block{ min-height:265px; padding:10px; }
            .map-wrap{
                min-height:420px;
                padding:6px;
            }
            .map-wrap .content,
            .map-wrap .container-fluid,
            .map-wrap .row,
            .map-wrap .col-12,
            .map-wrap #contenidoTransformado,
            .map-wrap .cuerpoMapa{
                min-height:395px;
            }
        }
    </style>


    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!-- Encabezado de navegación -->
            <div class="page-header">
                <div class="page-block">
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <div>
                            <h5 class="mb-0">GOB360 · Gestión territorial</h5>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Información general</a></li>
                                <li class="breadcrumb-item active">Visitas del gobernador</li>
                            </ul>
                        </div>
                        <?php include './admin/include/btn_back.php'; ?>
                    </div>
                </div>
            </div>

            <!-- Hero GOB360 -->
            <section class="gob360-dashboard-hero" aria-labelledby="tituloDashboardVisitas">
                <div class="gob360-dashboard-hero__content">
                    <img
                        src="assets/img/gob360l.png"
                        alt="Logo GOB360"
                        class="gob360-dashboard-hero__logo"
                    >
                    <div>
                        <p class="gob360-dashboard-hero__eyebrow">Plataforma institucional</p>
                        <h1 id="tituloDashboardVisitas">Gestión territorial y visitas</h1>
                        <p>
                            Seguimiento ejecutivo de los recorridos del gobernador, la cobertura municipal
                            y el avance territorial del departamento de Santander.
                        </p>
                    </div>
                </div>

                <div class="gob360-dashboard-hero__aside">
                    <div class="hero-status">
                        <i class="feather icon-map-pin"></i>
                        <div>
                            <strong><?= (int)$municipios ?> municipios</strong>
                            Visitados en Santander
                        </div>
                    </div>
                    <div class="hero-status">
                        <i class="feather icon-navigation"></i>
                        <div>
                            <strong><?= (int)$visitarpendiente ?> pendientes</strong>
                            Cobertura por completar
                        </div>
                    </div>
                </div>
            </section>

            <!-- KPI ROW -->
            <div class="row g-3 mt-1 mb-3 kpi-row">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-ico" role="button" data-bs-toggle="modal" data-bs-target="#modalMunicipiosVisitados" title="Ver municipios visitados">
                                <i class="feather icon-map-pin"></i>
                            </div>
                            <div class="kpi-meta">
                                <p class="kpi-title">Visitas registradas</p>
                                <p class="kpi-value"><?php echo (int)$visitas; ?></p>
                                <p class="kpi-hint">Consulta el detalle territorial</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-ico" role="button" data-bs-toggle="modal" data-bs-target="#exampleModalLong1" title="Ver visitas a municipios">
                                <i class="feather icon-check-circle"></i>
                            </div>
                            <div class="kpi-meta">
                                <p class="kpi-title">Municipios visitados</p>
                                <p class="kpi-value"><?php echo (int)$municipios; ?></p>
                                <p class="kpi-hint">Cobertura territorial alcanzada</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-ico" role="button" data-bs-toggle="modal" data-bs-target="#exampleModalLong3" title="Ver municipios restantes">
                                <i class="feather icon-navigation"></i>
                            </div>
                            <div class="kpi-meta">
                                <p class="kpi-title">Municipios pendientes</p>
                                <p class="kpi-value"><?php echo (int)$visitarpendiente; ?></p>
                                <p class="kpi-hint">Cobertura aún por completar</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="kpi-card">
                        <div class="kpi-body">
                            <a class="kpi-ico" href="plan_desarrollo.php" title="Ir a Plan de Desarrollo" style="text-decoration:none;">
                                <i class="feather icon-file-text"></i>
                            </a>
                            <div class="kpi-meta">
                                <p class="kpi-title">Plan de Desarrollo</p>
                                <p class="kpi-value">&nbsp;</p>
                                <p class="kpi-hint">Consultar metas e indicadores</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENT ROW -->
            <div class="row g-3">
                <!-- MAPA -->
                <div class="col-12 col-lg-7">
                    <div class="card section-card">
                        <div class="card-header">
                            <div class="section-title">
                                <i class="feather icon-map"></i>
                                <h5>Mapa de cobertura territorial</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="map-wrap">
                                <section class="content" style="<?php echo isset($_GET["route_map"]) ? "padding:0rem !important" : "" ?>">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-12" style="position: static;">
                                                <div id="contenidoTransformado" class="contenido-transformado">
                                                    <div class="cuerpoMapa w-12">
                                                        <?php include_once "admin/classes/rango_colores_visita_departamento.php"; ?>
                                                        <?php if (!is_null($mapa)) : ?>
                                                            <?php if ($_GET['depto_id'] == 1) : ?>
                                                                <div class="antioquia munis"><?php include_once "admin/mapa/mapa.php"; ?></div>
                                                            <?php elseif ($_GET['depto_id'] == 12) : ?>
                                                                <div class="choco munis"><?php include_once "admin/mapa-choco/choco.php"; ?></div>
                                                            <?php else : ?>
                                                                <div class="santander munis"><?php require_once "admin/mapa-santander/mapa_gobernador_visita.php"; ?></div>
                                                            <?php endif ?>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GRAFICAS -->
                <div class="col-12 col-lg-5">
                    <div class="card section-card">
                        <div class="card-header">
                            <div class="section-title">
                                <i class="feather icon-bar-chart-2"></i>
                                <h5>Analítica de visitas</h5>
                            </div>
                        </div>
                        <div class="card-body d-grid gap-3">
                            <div class="chart-block">
                                <h6 class="text-center mb-2">Distribución de visitas por provincia</h6>
                                <div id="containerProvincias" style="height: 260px; width: 100%;"></div>
                            </div>
                            <div class="chart-block">
                                <h6 class="text-center mb-2">Evolución mensual de visitas</h6>
                                <div id="containerMunicipios" style="height: 260px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Municipios Visitados -->
            <div id="modalMunicipiosVisitados" class="modal fade" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Municipios Visitados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <?php
                        $arr = Detalle::getAll(null);
                        $isvalid = $arr['output']['valid'] ?? false;
                        $data = $arr['output']['response'] ?? [];
                        ?>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Provincia</th>
                                            <th>Municipio</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyMunicipiosVisitados">
                                        <?php if ($isvalid && !empty($data)): ?>
                                            <?php foreach ($data as $item): ?>
                                                <tr>
                                                    <td><?= h($item['provincia'] ?? ''); ?></td>
                                                    <td><?= h($item['municipio'] ?? ''); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2">No hay datos disponibles.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <nav class="mt-3">
                                <ul class="pagination justify-content-center mb-0" id="paginacionMunicipios"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- pcoded-content -->
    </div><!-- pcoded-main-container -->

    <!-- Modal de Cantidad de Visitas -->
    <div id="exampleModalLong1" class="modal fade" tabindex="-1" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Cantidad de visitas a municipios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <?php
                $arrVisitasMunicipios = Cuenta::getAll(null);
                $isvalid = $arrVisitasMunicipios['output']['valid'] ?? false;
                $arrVisitasMunicipios = $arrVisitasMunicipios['output']['response'] ?? [];
                ?>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Municipio</th>
                                    <th scope="col">Veces Visitado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($isvalid && !empty($arrVisitasMunicipios)): ?>
                                    <?php foreach ($arrVisitasMunicipios as $item): ?>
                                        <tr>
                                            <td><?php echo h($item['municipio'] ?? ''); ?></td>
                                            <td><?php echo h($item['CuentaDeid'] ?? '0'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2">No hay datos disponibles.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Municipios pendientes -->
    <div id="exampleModalLong3" class="modal fade" tabindex="-1" aria-labelledby="exampleModalLongTitle3" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle3">Municipios pendientes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $arrMunicipiosRestante = Munnovisitados::getAll(null);
                    $isvalid = $arrMunicipiosRestante['output']['valid'] ?? false;
                    $arrMunicipiosRestante = $arrMunicipiosRestante['output']['response'] ?? [];
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Municipio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($isvalid && !empty($arrMunicipiosRestante)): ?>
                                    <?php foreach ($arrMunicipiosRestante as $item): ?>
                                        <tr>
                                            <td><?php echo h($item['municipio'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td>No hay datos disponibles.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/plugins/prism.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>
    <script src="admin/js/estado_general.js"></script>
    <script>
        // Paginación simple del modal "Municipios Visitados"
        document.addEventListener("DOMContentLoaded", function() {
            const rowsPerPage = 10;
            const tbody = document.getElementById("tbodyMunicipiosVisitados");
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll("tr"));
            const pagination = document.getElementById("paginacionMunicipios");
            if (!pagination) return;

            let currentPage = 1;

            function displayPage(page) {
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                rows.forEach((row, i) => {
                    row.style.display = (i >= start && i < end) ? "" : "none";
                });

                const pageLinks = pagination.querySelectorAll(".page-item");
                pageLinks.forEach(li => li.classList.remove("active"));
                if (pageLinks[page - 1]) pageLinks[page - 1].classList.add("active");
            }

            function setupPagination() {
                pagination.innerHTML = "";
                const pageCount = Math.ceil(rows.length / rowsPerPage);
                for (let i = 1; i <= pageCount; i++) {
                    const li = document.createElement("li");
                    li.classList.add("page-item");
                    li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                    li.addEventListener("click", e => {
                        e.preventDefault();
                        currentPage = i;
                        displayPage(currentPage);
                    });
                    pagination.appendChild(li);
                }
            }

            setupPagination();
            displayPage(currentPage);
        });
    </script>

</body>
</html>
