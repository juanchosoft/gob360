<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

/*
if (!$view) {
    require 'permiso_denegado.php';
}
*/

include './admin/classes/Visitas.php';

// Información de visitas
$responseVisitas = Visitas::getAll(null);
$isvalid = $responseVisitas['output']['valid'] ?? false;
$arr = $responseVisitas['output']['response'] ?? [];
$arr = is_array($arr) ? $arr : [];
$modulo = 'Registro Visitas';

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Indicadores de la vista: se calculan con la información ya cargada.
$totalVisitas = count($arr);
$municipiosUnicos = [];
$provinciasUnicas = [];
$totalConFoto = 0;
$ultimaFecha = null;

foreach ($arr as $visita) {
    $municipio = trim((string)($visita['municipio'] ?? ''));
    $provincia = trim((string)($visita['provincia'] ?? ''));
    $fecha = trim((string)($visita['date'] ?? ''));

    if ($municipio !== '') {
        $municipiosUnicos[mb_strtolower($municipio, 'UTF-8')] = true;
    }

    if ($provincia !== '') {
        $provinciasUnicas[mb_strtolower($provincia, 'UTF-8')] = true;
    }

    if (!empty($visita['img'])) {
        $totalConFoto++;
    }

    if ($fecha !== '' && strtotime($fecha) !== false) {
        $timestamp = strtotime($fecha);
        if ($ultimaFecha === null || $timestamp > $ultimaFecha) {
            $ultimaFecha = $timestamp;
        }
    }
}

$totalMunicipios = count($municipiosUnicos);
$totalProvincias = count($provinciasUnicas);
$porcentajeEvidencia = $totalVisitas > 0 ? round(($totalConFoto / $totalVisitas) * 100) : 0;
$ultimaFechaTexto = $ultimaFecha ? date('d/m/Y', $ultimaFecha) : 'Sin registros';
?>

<body class="gob360-list-body">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --g360-bg-0: #050914;
            --g360-bg-1: #081426;
            --g360-bg-2: #0b2028;
            --g360-surface: rgba(255, 255, 255, .065);
            --g360-surface-strong: rgba(255, 255, 255, .095);
            --g360-border: rgba(255, 255, 255, .13);
            --g360-border-soft: rgba(255, 255, 255, .08);
            --g360-text: rgba(255, 255, 255, .96);
            --g360-muted: rgba(255, 255, 255, .66);
            --g360-muted-2: rgba(255, 255, 255, .48);
            --g360-cyan: #29d8ff;
            --g360-blue: #4580ff;
            --g360-violet: #8d65ff;
            --g360-green: #25e78b;
            --g360-orange: #ffbf69;
            --g360-danger: #ff647d;
            --g360-shadow: 0 24px 70px rgba(0, 0, 0, .42);
            --g360-shadow-soft: 0 14px 38px rgba(0, 0, 0, .28);
            --g360-radius-xl: 24px;
            --g360-radius-lg: 18px;
            --g360-radius-md: 14px;
            --g360-safe-top: 104px;
        }

        html,
        body {
            overflow-x: hidden !important;
        }

        body.gob360-list-body {
            min-height: 100vh;
            color: var(--g360-text);
            background:
                radial-gradient(900px 430px at 8% 8%, rgba(69, 128, 255, .25), transparent 62%),
                radial-gradient(780px 390px at 88% 12%, rgba(141, 101, 255, .18), transparent 60%),
                radial-gradient(900px 500px at 45% 110%, rgba(37, 231, 139, .10), transparent 58%),
                linear-gradient(145deg, var(--g360-bg-0), var(--g360-bg-1) 46%, var(--g360-bg-2));
            background-attachment: fixed;
        }

        body.gob360-list-body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            opacity: .12;
            background-image:
                linear-gradient(rgba(255, 255, 255, .13) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .13) 1px, transparent 1px);
            background-size: 74px 74px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .9), transparent 88%);
        }

        .pcoded-main-container {
            background: transparent !important;
        }

        .pcoded-main-container .pcoded-content {
            padding: calc(var(--g360-safe-top) + 10px) 16px 34px !important;
        }

        @media (min-width: 768px) {
            :root { --g360-safe-top: 112px; }

            .pcoded-main-container .pcoded-content {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
        }

        @media (min-width: 1200px) {
            :root { --g360-safe-top: 118px; }

            .pcoded-main-container .pcoded-content {
                max-width: 1500px;
                margin: 0 auto;
                padding-left: 34px !important;
                padding-right: 34px !important;
            }
        }

        /* Cabecera superior */
        .page-header .page-block {
            padding: 14px 18px;
            border: 1px solid var(--g360-border);
            border-radius: var(--g360-radius-xl);
            background: rgba(255, 255, 255, .055) !important;
            box-shadow: var(--g360-shadow-soft);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .page-header h5 {
            margin: 0;
            color: var(--g360-text) !important;
            font-weight: 900;
            letter-spacing: -.2px;
        }

        .breadcrumb {
            padding: 0;
            margin: 6px 0 0;
            background: transparent !important;
        }

        .breadcrumb-item,
        .breadcrumb-item a {
            color: var(--g360-muted) !important;
        }

        .breadcrumb-item.active {
            color: var(--g360-text) !important;
        }

        /* Hero */
        .visits-hero {
            position: relative;
            overflow: hidden;
            padding: clamp(22px, 3vw, 34px);
            margin-bottom: 18px;
            border: 1px solid var(--g360-border);
            border-radius: 28px;
            background:
                linear-gradient(135deg, rgba(69, 128, 255, .18), rgba(141, 101, 255, .09) 55%, rgba(37, 231, 139, .07)),
                rgba(255, 255, 255, .045);
            box-shadow: var(--g360-shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .visits-hero::before {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            right: -250px;
            top: -310px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(41, 216, 255, .24), transparent 68%);
            pointer-events: none;
        }

        .visits-hero__layout {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 26px;
        }

        .visits-hero__content {
            flex: 1 1 650px;
            min-width: 0;
        }

        .visits-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding: 7px 12px;
            border: 1px solid rgba(41, 216, 255, .24);
            border-radius: 999px;
            background: rgba(41, 216, 255, .08);
            color: #9cecff;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .visits-hero__title {
            margin: 0;
            max-width: 880px;
            color: #fff;
            font-size: clamp(27px, 4vw, 48px);
            font-weight: 950;
            line-height: 1.04;
            letter-spacing: -1.4px;
        }

        .visits-hero__title span {
            color: var(--g360-cyan);
            text-shadow: 0 0 28px rgba(41, 216, 255, .22);
        }

        .visits-hero__text {
            max-width: 760px;
            margin: 12px 0 0;
            color: var(--g360-muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .visits-hero__logo-wrap {
            flex: 0 0 auto;
            width: clamp(190px, 20vw, 310px);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .visits-hero__logo-wrap::before {
            content: "";
            position: absolute;
            inset: 15% 2%;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(41, 216, 255, .22), rgba(69, 128, 255, .09) 45%, transparent 72%);
            filter: blur(18px);
        }

        .visits-hero__logo {
            position: relative;
            z-index: 1;
            width: 100%;
            height: auto;
            max-height: 150px;
            object-fit: contain;
            border-radius: 16px;
            filter:
                drop-shadow(0 16px 28px rgba(0, 0, 0, .42))
                drop-shadow(0 0 20px rgba(41, 216, 255, .18));
        }

        @media (max-width: 767.98px) {
            .visits-hero__layout {
                flex-direction: column-reverse;
                align-items: flex-start;
            }

            .visits-hero__logo-wrap {
                width: min(260px, 78vw);
                align-self: center;
            }
        }

        /* KPIs */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            min-height: 130px;
            padding: 18px;
            border: 1px solid var(--g360-border);
            border-radius: 20px;
            background: var(--g360-surface);
            box-shadow: var(--g360-shadow-soft);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            transition: transform .18s ease, border-color .18s ease, background .18s ease;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            border-color: rgba(41, 216, 255, .28);
            background: var(--g360-surface-strong);
        }

        .metric-card::after {
            content: "";
            position: absolute;
            width: 170px;
            height: 170px;
            right: -90px;
            bottom: -110px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--metric-glow, rgba(69, 128, 255, .20)), transparent 68%);
        }

        .metric-card--cyan { --metric-glow: rgba(41, 216, 255, .25); }
        .metric-card--green { --metric-glow: rgba(37, 231, 139, .23); }
        .metric-card--violet { --metric-glow: rgba(141, 101, 255, .24); }
        .metric-card--orange { --metric-glow: rgba(255, 191, 105, .22); }

        .metric-card__top {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .metric-card__icon {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--g360-border);
            border-radius: 15px;
            background: rgba(0, 0, 0, .20);
            color: var(--g360-cyan);
            font-size: 20px;
        }

        .metric-card__label {
            margin: 0;
            color: var(--g360-muted);
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .metric-card__value {
            margin: 7px 0 0;
            color: #fff;
            font-size: clamp(27px, 3vw, 38px);
            font-weight: 950;
            line-height: 1;
            letter-spacing: -.8px;
        }

        .metric-card__hint {
            position: relative;
            z-index: 1;
            margin: 14px 0 0;
            color: var(--g360-muted-2);
            font-size: 12px;
        }

        @media (max-width: 1199.98px) {
            .metrics-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 575.98px) {
            .metrics-grid { grid-template-columns: 1fr; }
        }

        /* Tabla SaaS */
        .table-panel {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--g360-border);
            border-radius: 26px;
            background: rgba(255, 255, 255, .052);
            box-shadow: var(--g360-shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .table-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 20px 22px;
            border-bottom: 1px solid var(--g360-border-soft);
            background: linear-gradient(135deg, rgba(69, 128, 255, .12), rgba(141, 101, 255, .06));
        }

        .table-panel__title-wrap {
            min-width: 0;
        }

        .table-panel__title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            color: #fff;
            font-size: 19px;
            font-weight: 950;
        }

        .table-panel__title i {
            color: var(--g360-cyan);
        }

        .table-panel__subtitle {
            margin: 5px 0 0;
            color: var(--g360-muted);
            font-size: 12px;
        }

        .table-panel__badge {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid rgba(37, 231, 139, .25);
            border-radius: 999px;
            background: rgba(37, 231, 139, .08);
            color: #a6f7ce;
            font-size: 12px;
            font-weight: 850;
            white-space: nowrap;
        }

        .table-panel__body {
            padding: 16px;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 18px;
            scrollbar-width: thin;
            scrollbar-color: rgba(41, 216, 255, .35) rgba(255, 255, 255, .04);
        }

        #dynamictable {
            width: 100% !important;
            min-width: 1040px;
            margin: 0 !important;
            table-layout: auto;
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            color: var(--g360-text) !important;
            background: transparent !important;
        }

        #dynamictable thead th {
            padding: 13px 14px !important;
            border: 0 !important;
            background: rgba(255, 255, 255, .075) !important;
            color: rgba(255, 255, 255, .74) !important;
            font-size: 11px !important;
            font-weight: 900 !important;
            letter-spacing: .08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        #dynamictable thead th:first-child {
            border-radius: 13px 0 0 13px;
        }

        #dynamictable thead th:last-child {
            border-radius: 0 13px 13px 0;
        }

        #dynamictable tbody tr {
            background: rgba(255, 255, 255, .046) !important;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .16);
            transition: transform .15s ease, background .15s ease;
        }

        #dynamictable tbody tr:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, .075) !important;
        }

        #dynamictable tbody td {
            padding: 12px 14px !important;
            border-top: 1px solid var(--g360-border-soft) !important;
            border-bottom: 1px solid var(--g360-border-soft) !important;
            border-left: 0 !important;
            border-right: 0 !important;
            color: rgba(255, 255, 255, .88) !important;
            font-size: 13px;
            vertical-align: middle !important;
            white-space: normal !important;
            word-break: break-word !important;
        }

        #dynamictable tbody td:first-child {
            border-left: 1px solid var(--g360-border-soft) !important;
            border-radius: 14px 0 0 14px;
        }

        #dynamictable tbody td:last-child {
            max-width: 400px;
            border-right: 1px solid var(--g360-border-soft) !important;
            border-radius: 0 14px 14px 0;
        }

        .visit-id {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            padding: 6px 9px;
            border: 1px solid rgba(69, 128, 255, .24);
            border-radius: 10px;
            background: rgba(69, 128, 255, .10);
            color: #b8ccff;
            font-size: 12px;
            font-weight: 900;
        }

        .visit-view-btn {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            border: 1px solid rgba(41, 216, 255, .28) !important;
            border-radius: 13px !important;
            background: linear-gradient(135deg, rgba(41, 216, 255, .18), rgba(69, 128, 255, .16)) !important;
            color: #d6f7ff !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, .22);
            transition: transform .15s ease, filter .15s ease;
        }

        .visit-view-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.12);
        }

        .visit-photo {
            width: 64px;
            height: 64px;
            display: block;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, .16);
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .30);
            cursor: pointer;
            transition: transform .18s ease, border-color .18s ease;
        }

        .visit-photo:hover {
            transform: scale(1.05);
            border-color: rgba(41, 216, 255, .48);
        }

        .visit-date {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #dce7ff;
            font-weight: 800;
            white-space: nowrap;
        }

        .visit-date i { color: var(--g360-cyan); }

        .location-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border: 1px solid var(--g360-border-soft);
            border-radius: 999px;
            background: rgba(0, 0, 0, .18);
            color: rgba(255, 255, 255, .82);
            font-size: 12px;
            font-weight: 750;
        }

        .location-pill i {
            color: var(--g360-green);
        }

        .visit-reason {
            display: -webkit-box;
            max-width: 440px;
            overflow: hidden;
            color: rgba(255, 255, 255, .75);
            line-height: 1.55;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .empty-state {
            padding: 45px 20px;
            text-align: center;
            color: var(--g360-muted);
        }

        .empty-state i {
            display: block;
            margin-bottom: 12px;
            color: var(--g360-cyan);
            font-size: 42px;
        }

        /* DataTables */
        .dataTables_wrapper {
            color: var(--g360-muted) !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--g360-muted) !important;
            font-size: 12px;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            color: var(--g360-muted) !important;
            font-weight: 750;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            min-height: 40px;
            margin-left: 8px;
            border: 1px solid var(--g360-border) !important;
            border-radius: 12px !important;
            background: rgba(5, 9, 20, .72) !important;
            color: #fff !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            min-width: 230px;
            padding: 8px 12px !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: rgba(41, 216, 255, .48) !important;
            box-shadow: 0 0 0 4px rgba(41, 216, 255, .10) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button,
        .dataTables_wrapper .dataTables_paginate .paginate_button:link,
        .dataTables_wrapper .dataTables_paginate .paginate_button:visited {
            min-width: 38px;
            margin: 0 3px !important;
            padding: 7px 11px !important;
            border: 1px solid var(--g360-border-soft) !important;
            border-radius: 10px !important;
            background: rgba(255, 255, 255, .05) !important;
            color: rgba(255, 255, 255, .74) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            border-color: rgba(41, 216, 255, .34) !important;
            background: linear-gradient(135deg, rgba(41, 216, 255, .22), rgba(69, 128, 255, .20)) !important;
            color: #fff !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            border-color: rgba(41, 216, 255, .25) !important;
            background: rgba(255, 255, 255, .09) !important;
            color: #fff !important;
        }

        @media (max-width: 767.98px) {
            .table-panel__header {
                align-items: flex-start;
                flex-direction: column;
            }

            .table-panel__body {
                padding: 10px;
            }

            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length {
                float: none !important;
                width: 100%;
                text-align: left !important;
                margin-bottom: 10px;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100%;
                min-width: 0;
                margin: 6px 0 0 !important;
            }
        }

        /* Modal de imagen */
        .visit-modal .modal-content {
            overflow: hidden;
            border: 1px solid var(--g360-border);
            border-radius: 22px;
            background: #0b1424;
            color: #fff;
            box-shadow: var(--g360-shadow);
        }

        .visit-modal .modal-header {
            align-items: center;
            border-bottom: 1px solid var(--g360-border-soft);
            background: linear-gradient(135deg, rgba(69, 128, 255, .18), rgba(141, 101, 255, .10));
        }

        .visit-modal .modal-title {
            color: #fff;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.45;
        }

        .visit-modal .close {
            color: #fff;
            text-shadow: none;
            opacity: .85;
        }

        .visit-modal .modal-body {
            padding: 16px;
            background: rgba(255, 255, 255, .035);
        }

        .visit-modal .modal-body img {
            max-height: 72vh;
            object-fit: contain;
            border-radius: 16px;
            box-shadow: 0 16px 44px rgba(0, 0, 0, .34);
        }
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                                <h5>Control de visitas territoriales</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Registro de visitas</a></li>
                                <li class="breadcrumb-item active">Cuadro de control</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <section class="visits-hero">
                <div class="visits-hero__layout">
                    <div class="visits-hero__content">
                        <div class="visits-hero__eyebrow">
                            <i class="bi bi-broadcast-pin"></i>
                            Seguimiento territorial en tiempo real
                        </div>
                        <h1 class="visits-hero__title">
                            Historial ejecutivo de <span>visitas municipales</span>
                        </h1>
                        <p class="visits-hero__text">
                            Consulta los desplazamientos registrados, la evidencia fotográfica, la cobertura por provincia
                            y los motivos de cada visita desde una sola vista clara y preparada para la toma de decisiones.
                        </p>
                    </div>

                    <div class="visits-hero__logo-wrap" aria-hidden="true">
                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="visits-hero__logo"
                        >
                    </div>
                </div>
            </section>

            <section class="metrics-grid" aria-label="Resumen de visitas">
                <article class="metric-card metric-card--cyan">
                    <div class="metric-card__top">
                        <div>
                            <p class="metric-card__label">Registros totales</p>
                            <p class="metric-card__value"><?= number_format($totalVisitas, 0, ',', '.') ?></p>
                        </div>
                        <span class="metric-card__icon"><i class="bi bi-journal-check"></i></span>
                    </div>
                    <p class="metric-card__hint">Visitas disponibles en el historial.</p>
                </article>

                <article class="metric-card metric-card--green">
                    <div class="metric-card__top">
                        <div>
                            <p class="metric-card__label">Municipios cubiertos</p>
                            <p class="metric-card__value"><?= number_format($totalMunicipios, 0, ',', '.') ?></p>
                        </div>
                        <span class="metric-card__icon"><i class="bi bi-geo-alt-fill"></i></span>
                    </div>
                    <p class="metric-card__hint">Municipios únicos con actividad registrada.</p>
                </article>

                <article class="metric-card metric-card--violet">
                    <div class="metric-card__top">
                        <div>
                            <p class="metric-card__label">Provincias</p>
                            <p class="metric-card__value"><?= number_format($totalProvincias, 0, ',', '.') ?></p>
                        </div>
                        <span class="metric-card__icon"><i class="bi bi-map"></i></span>
                    </div>
                    <p class="metric-card__hint">Cobertura territorial consolidada.</p>
                </article>

                <article class="metric-card metric-card--orange">
                    <div class="metric-card__top">
                        <div>
                            <p class="metric-card__label">Evidencia fotográfica</p>
                            <p class="metric-card__value"><?= number_format($porcentajeEvidencia, 0, ',', '.') ?>%</p>
                        </div>
                        <span class="metric-card__icon"><i class="bi bi-camera-fill"></i></span>
                    </div>
                    <p class="metric-card__hint">Último registro: <?= h($ultimaFechaTexto) ?>.</p>
                </article>
            </section>

            <section class="table-panel">
                <div class="table-panel__header">
                    <div class="table-panel__title-wrap">
                        <h2 class="table-panel__title">
                            <i class="bi bi-table"></i>
                            Listado de visitas
                        </h2>
                        <p class="table-panel__subtitle">
                            Usa el buscador y la paginación para localizar rápidamente cualquier registro.
                        </p>
                    </div>

                    <span class="table-panel__badge">
                        <i class="bi bi-shield-check"></i>
                        Información consolidada
                    </span>
                </div>

                <div class="table-panel__body">
                    <?php if ($isvalid && !empty($arr)): ?>
                        <div class="table-responsive">
                            <table id="dynamictable" class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Acción</th>
                                        <th>Evidencia</th>
                                        <th>Fecha</th>
                                        <th>Provincia</th>
                                        <th>Municipio</th>
                                        <th>Motivo de la visita</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $imgBasePath = 'assets/img/admin/';
                                    foreach ($arr as $item):
                                        $id = $item['id'] ?? '';
                                        $provincia = $item['provincia'] ?? '';
                                        $municipio = $item['municipio'] ?? '';
                                        $fechaOriginal = $item['date'] ?? '';
                                        $fechaTexto = ($fechaOriginal && strtotime($fechaOriginal) !== false)
                                            ? date('d/m/Y', strtotime($fechaOriginal))
                                            : $fechaOriginal;
                                        $motivo = $item['compromisos'] ?? '';
                                        $img = !empty($item['img'])
                                            ? $imgBasePath . h($item['img'])
                                            : 'dist/img/santander.png';
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="visit-id">#<?= h($id) ?></span>
                                            </td>

                                            <td>
                                                <form action="reporte_visita.php" method="POST" class="mb-0">
                                                    <input type="hidden" name="reporte" value="<?= h($id) ?>">
                                                    <button type="submit" class="btn visit-view-btn" title="Abrir reporte de visita" aria-label="Abrir reporte de visita">
                                                        <i class="feather icon-eye"></i>
                                                    </button>
                                                </form>
                                            </td>

                                            <td>
                                                <img
                                                    src="<?= $img ?>"
                                                    alt="Evidencia de la visita en <?= h($municipio) ?>"
                                                    class="visit-photo"
                                                    loading="lazy"
                                                    data-toggle="modal"
                                                    data-target="#imageModal<?= h($id) ?>"
                                                >

                                                <div class="modal fade visit-modal" id="imageModal<?= h($id) ?>" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel<?= h($id) ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="imageModalLabel<?= h($id) ?>">
                                                                    Evidencia de <?= h($municipio) ?> · Provincia <?= h($provincia) ?>
                                                                </h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="<?= $img ?>" alt="Evidencia ampliada de la visita" class="img-fluid">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td data-order="<?= h($fechaOriginal) ?>">
                                                <span class="visit-date">
                                                    <i class="bi bi-calendar3"></i>
                                                    <?= h($fechaTexto) ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="location-pill">
                                                    <i class="bi bi-diagram-3-fill"></i>
                                                    <?= h($provincia ?: 'Sin provincia') ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="location-pill">
                                                    <i class="bi bi-geo-fill"></i>
                                                    <?= h($municipio ?: 'Sin municipio') ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="visit-reason" title="<?= h($motivo) ?>">
                                                    <?= h($motivo ?: 'Sin detalle registrado') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <strong>No hay visitas registradas.</strong>
                            <div class="mt-1">Cuando existan registros aparecerán automáticamente en esta tabla.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </div>

    <?php include 'admin/include/gerenic_script.php'; ?>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/detalle_visitas.js"></script>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <?php include './admin/include/generic_dataTables.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Traduce de manera visual los controles más comunes de DataTables
            // sin reemplazar la configuración existente del proyecto.
            const observer = new MutationObserver(function () {
                const wrapper = document.querySelector('#dynamictable_wrapper');
                if (!wrapper) return;

                const searchInput = wrapper.querySelector('.dataTables_filter input');
                if (searchInput) {
                    searchInput.setAttribute('placeholder', 'Buscar visita, municipio o provincia');
                    searchInput.setAttribute('aria-label', 'Buscar en el listado de visitas');
                }

                const filterLabel = wrapper.querySelector('.dataTables_filter label');
                if (filterLabel && filterLabel.childNodes.length) {
                    for (const node of filterLabel.childNodes) {
                        if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '') {
                            node.textContent = 'Buscar: ';
                            break;
                        }
                    }
                }

                observer.disconnect();
            });

            observer.observe(document.body, { childList: true, subtree: true });
            setTimeout(function () { observer.disconnect(); }, 5000);
        });
    </script>
</body>
</html>
