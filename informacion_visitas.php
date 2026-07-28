<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

// Validación
/* if (!$view) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Departamento.php';
include './admin/classes/Provincias.php';
include './admin/classes/Secretarias.php';

$modulo = 'Registro Visitas';

// Información de secretarías
$arrSec = Secretarias::getAll(null);
$isvalid = $arrSec['output']['valid'] ?? false;
$arrSec = $arrSec['output']['response'] ?? [];
$optionSec = "<option value=''>Seleccione una secretaría o dependencia</option>";
foreach ($arrSec as $val) {
    $optionSec .= "<option value='" . htmlspecialchars($val['id']) . "'>" . htmlspecialchars($val['secretaria']) . "</option>";
}

// Información de departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'] ?? false;
$arrDep = $arrDep['output']['response'] ?? [];
$departamentoPrincipal = (string) Util::getDepartamentoPrincipal();
$optionDep = "";
foreach ($arrDep as $val) {
    $codigoDepartamento = (string)($val['codigo_departamento'] ?? '');
    $selected = ($codigoDepartamento === $departamentoPrincipal) ? ' selected' : '';
    $optionDep .= "<option value='" . htmlspecialchars($codigoDepartamento) . "'{$selected}>" .
        htmlspecialchars($codigoDepartamento) . " - " . htmlspecialchars($val['departamento']) . "</option>";
}

// Información de provincias
$arrProv = Provincias::getProvinciasByDepartamento('68');
$isvalidProv = $arrProv['output']['valid'] ?? false;
$arrProv = $arrProv['output']['response'] ?? [];
$optionProv = "<option value=''>Seleccione una provincia</option>";

if ($isvalidProv && !empty($arrProv)) {
    foreach ($arrProv as $val) {
        $optionProv .= "<option value='" . htmlspecialchars($val['provincia']) . "'>" . htmlspecialchars($val['provincia']) . "</option>";
    }
} else {
    $optionProv .= "<option value=''>Error cargando provincias</option>";
}

function h($s){
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>

<body class="gob360-form-page">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <style>
        :root{
            --g360-bg-0:#050a14;
            --g360-bg-1:#081426;
            --g360-panel:rgba(12,25,45,.74);
            --g360-panel-strong:rgba(9,20,38,.92);
            --g360-field:rgba(5,15,30,.78);
            --g360-stroke:rgba(255,255,255,.11);
            --g360-stroke-strong:rgba(70,216,255,.28);
            --g360-text:#f5f9ff;
            --g360-muted:rgba(226,235,248,.66);
            --g360-muted-2:rgba(226,235,248,.46);
            --g360-cyan:#22d3ee;
            --g360-blue:#268cff;
            --g360-indigo:#6366f1;
            --g360-green:#35f29a;
            --g360-yellow:#ffd166;
            --g360-red:#ff6b85;
            --g360-radius-xl:28px;
            --g360-radius-lg:20px;
            --g360-radius-md:15px;
            --g360-shadow:0 28px 80px rgba(0,0,0,.44);
            --g360-shadow-soft:0 16px 44px rgba(0,0,0,.28);
            --g360-safe-top:104px;
        }

        *{ box-sizing:border-box; }
        html, body{ overflow-x:hidden !important; }

        body.gob360-form-page{
            min-height:100vh;
            color:var(--g360-text);
            background:
                linear-gradient(rgba(255,255,255,.018) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.018) 1px, transparent 1px),
                radial-gradient(900px 500px at 12% 8%, rgba(38,140,255,.24), transparent 64%),
                radial-gradient(760px 480px at 88% 20%, rgba(34,211,238,.15), transparent 62%),
                radial-gradient(900px 520px at 50% 110%, rgba(53,242,154,.09), transparent 62%),
                linear-gradient(145deg, var(--g360-bg-0), var(--g360-bg-1));
            background-size:72px 72px, 72px 72px, auto, auto, auto, auto;
            background-attachment:fixed;
        }

        .pcoded-main-container{ background:transparent !important; }
        .pcoded-main-container .pcoded-content{
            padding:calc(var(--g360-safe-top) + 18px) 18px 52px !important;
        }

        @media (min-width:768px){
            :root{ --g360-safe-top:112px; }
            .pcoded-main-container .pcoded-content{ padding-left:26px !important; padding-right:26px !important; }
        }

        @media (min-width:1200px){
            :root{ --g360-safe-top:118px; }
            .pcoded-main-container .pcoded-content{
                max-width:1480px;
                margin:0 auto;
                padding-left:38px !important;
                padding-right:38px !important;
            }
        }

        /* Cabecera compacta */
        .page-header{ margin-bottom:16px; }
        .page-header .page-block{
            padding:14px 18px;
            border:1px solid var(--g360-stroke);
            border-radius:20px;
            background:rgba(8,19,36,.64);
            box-shadow:var(--g360-shadow-soft);
            backdrop-filter:blur(16px);
        }
        .page-header h5{ color:var(--g360-text) !important; font-weight:900; margin:0; }
        .breadcrumb{ padding:0; margin:7px 0 0; background:transparent !important; }
        .breadcrumb a, .breadcrumb-item, .breadcrumb-item a{ color:var(--g360-muted) !important; }
        .breadcrumb-item.active{ color:var(--g360-muted-2) !important; }

        /* Hero SaaS */
        .form-hero{
            position:relative;
            overflow:hidden;
            border:1px solid var(--g360-stroke);
            border-radius:var(--g360-radius-xl);
            background:
                radial-gradient(650px 300px at 10% 0%, rgba(38,140,255,.27), transparent 62%),
                radial-gradient(520px 280px at 95% 15%, rgba(34,211,238,.17), transparent 62%),
                linear-gradient(135deg, rgba(16,35,63,.92), rgba(7,19,37,.86));
            box-shadow:var(--g360-shadow);
            padding:24px;
            margin-bottom:18px;
            isolation:isolate;
        }
        .form-hero::before{
            content:"";
            position:absolute;
            inset:auto -120px -180px auto;
            width:420px;
            height:420px;
            border-radius:50%;
            border:1px solid rgba(34,211,238,.15);
            box-shadow:0 0 0 45px rgba(34,211,238,.025), 0 0 0 90px rgba(38,140,255,.018);
            z-index:-1;
        }
        .hero-layout{
            display:grid;
            grid-template-columns:minmax(0,1fr) auto;
            gap:24px;
            align-items:center;
        }
        .hero-brand{
            display:flex;
            align-items:center;
            gap:18px;
            min-width:0;
        }
        .hero-logo{
            width:clamp(130px, 13vw, 200px);
            height:auto;
            display:block;
            border-radius:15px;
            filter:drop-shadow(0 12px 30px rgba(0,0,0,.38)) drop-shadow(0 0 18px rgba(34,211,238,.22));
        }
        .hero-kicker{
            margin:0 0 7px;
            color:var(--g360-cyan);
            font-size:12px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:2.5px;
        }
        .hero-title{
            margin:0;
            color:var(--g360-text);
            font-size:clamp(25px,3vw,42px);
            line-height:1.04;
            font-weight:950;
            letter-spacing:-.8px;
        }
        .hero-copy{
            max-width:720px;
            margin:10px 0 0;
            color:var(--g360-muted);
            font-size:14px;
            line-height:1.65;
        }
        .hero-status{
            min-width:220px;
            display:grid;
            gap:9px;
        }
        .status-pill{
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px 13px;
            border:1px solid var(--g360-stroke);
            border-radius:999px;
            background:rgba(3,12,25,.48);
            color:var(--g360-muted);
            font-size:12px;
            font-weight:800;
            white-space:nowrap;
        }
        .status-pill i{ color:var(--g360-cyan); font-size:16px; }
        .status-dot{
            width:9px;
            height:9px;
            border-radius:50%;
            background:var(--g360-green);
            box-shadow:0 0 0 6px rgba(53,242,154,.11), 0 0 14px rgba(53,242,154,.55);
        }

        /* Layout principal */
        .form-workspace{
            display:grid;
            grid-template-columns:minmax(0,1fr) 320px;
            gap:18px;
            align-items:start;
        }
        .saas-form-card,
        .assistant-card{
            border:1px solid var(--g360-stroke);
            background:var(--g360-panel);
            border-radius:var(--g360-radius-xl);
            box-shadow:var(--g360-shadow-soft);
            backdrop-filter:blur(16px);
        }
        .saas-form-card{ overflow:hidden; }
        .form-card-header{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            padding:18px 20px;
            border-bottom:1px solid var(--g360-stroke);
            background:linear-gradient(90deg, rgba(38,140,255,.11), rgba(34,211,238,.04));
        }
        .form-card-header h2{
            margin:0;
            color:var(--g360-text);
            font-size:18px;
            font-weight:900;
        }
        .form-card-header p{
            margin:4px 0 0;
            color:var(--g360-muted);
            font-size:12px;
        }
        .form-id-chip{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:8px 11px;
            border:1px solid var(--g360-stroke);
            border-radius:999px;
            color:var(--g360-muted);
            background:rgba(4,14,28,.44);
            font-size:11px;
            font-weight:850;
            white-space:nowrap;
        }
        .form-id-chip i{ color:var(--g360-green); }
        .form-card-body{ padding:20px; }

        /* Secciones */
        .form-section{
            position:relative;
            padding:18px;
            border:1px solid rgba(255,255,255,.085);
            border-radius:var(--g360-radius-lg);
            background:linear-gradient(145deg, rgba(255,255,255,.045), rgba(255,255,255,.018));
            margin-bottom:16px;
        }
        .form-section:last-of-type{ margin-bottom:0; }
        .section-head{
            display:flex;
            align-items:flex-start;
            gap:12px;
            margin-bottom:15px;
        }
        .section-number{
            width:36px;
            height:36px;
            flex:0 0 auto;
            display:grid;
            place-items:center;
            border-radius:12px;
            border:1px solid rgba(34,211,238,.25);
            background:linear-gradient(145deg, rgba(38,140,255,.22), rgba(34,211,238,.10));
            color:#fff;
            font-weight:950;
            box-shadow:0 10px 22px rgba(0,0,0,.22);
        }
        .section-head h3{
            margin:0;
            color:var(--g360-text);
            font-size:15px;
            font-weight:900;
        }
        .section-head p{
            margin:4px 0 0;
            color:var(--g360-muted-2);
            font-size:12px;
        }

        .form-grid{
            display:grid;
            grid-template-columns:repeat(12,minmax(0,1fr));
            gap:14px;
        }
        .field-span-12{ grid-column:span 12; }
        .field-span-8{ grid-column:span 8; }
        .field-span-6{ grid-column:span 6; }
        .field-span-4{ grid-column:span 4; }

        .form-group{ margin:0 !important; min-width:0; }
        .field-label-row{
            min-height:21px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:8px;
            margin-bottom:7px;
        }
        .form-group label{
            margin:0;
            color:rgba(245,249,255,.86) !important;
            font-size:12px;
            font-weight:850;
            letter-spacing:.15px;
        }
        .required-badge{
            display:inline-flex;
            align-items:center;
            gap:4px;
            padding:2px 7px;
            border-radius:999px;
            color:#ffdce3;
            border:1px solid rgba(255,107,133,.24);
            background:rgba(255,107,133,.08);
            font-size:9px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:.5px;
        }
        .optional-badge{
            color:var(--g360-muted-2);
            font-size:9px;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.5px;
        }

        .field-control{ position:relative; }
        .field-control > i{
            position:absolute;
            left:14px;
            top:50%;
            transform:translateY(-50%);
            z-index:3;
            color:var(--g360-cyan);
            font-size:17px;
            pointer-events:none;
            opacity:.9;
        }
        .field-control.textarea-control > i{
            top:17px;
            transform:none;
        }

        .form-control,
        .custom-select,
        select.form-control,
        input.form-control,
        textarea.form-control{
            width:100%;
            border:1px solid rgba(255,255,255,.13) !important;
            border-radius:var(--g360-radius-md) !important;
            background:var(--g360-field) !important;
            color:var(--g360-text) !important;
            font-size:14px;
            font-weight:650;
            outline:none;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.025) !important;
            transition:border-color .18s ease, box-shadow .18s ease, background .18s ease, transform .18s ease;
        }
        input.form-control,
        select.form-control{
            height:50px !important;
            padding:0 14px 0 43px !important;
        }
        textarea.form-control{
            min-height:118px;
            padding:14px 14px 14px 43px !important;
            resize:vertical;
            line-height:1.55;
        }
        .form-control:hover,
        .custom-select:hover{
            border-color:rgba(255,255,255,.20) !important;
        }
        .form-control:focus,
        .custom-select:focus{
            border-color:rgba(34,211,238,.50) !important;
            background:rgba(7,19,37,.93) !important;
            box-shadow:0 0 0 4px rgba(34,211,238,.10), 0 10px 24px rgba(0,0,0,.18) !important;
            transform:translateY(-1px);
        }
        .form-control::placeholder{ color:rgba(226,235,248,.38) !important; }
        .form-control:disabled{
            opacity:1;
            background:rgba(255,255,255,.045) !important;
            color:rgba(245,249,255,.65) !important;
            cursor:not-allowed;
        }
        select.form-control option{
            color:#eaf4ff;
            background:#0b172a;
        }
        input[type="date"]{ color-scheme:dark; }
        .ocultar-select{ display:none !important; }

        .field-help{
            display:flex;
            align-items:center;
            gap:6px;
            margin:6px 2px 0;
            color:var(--g360-muted-2);
            font-size:10.5px;
        }
        .field-help i{ color:var(--g360-blue); }

        /* Carga de archivo */
        .upload-box{
            position:relative;
            min-height:118px;
            display:flex;
            align-items:center;
            gap:14px;
            padding:15px;
            border:1px dashed rgba(34,211,238,.32);
            border-radius:var(--g360-radius-lg);
            background:linear-gradient(145deg, rgba(34,211,238,.055), rgba(38,140,255,.025));
            transition:border-color .18s ease, background .18s ease, transform .18s ease;
        }
        .upload-box:hover,
        .upload-box.is-dragging{
            border-color:rgba(34,211,238,.65);
            background:rgba(34,211,238,.075);
            transform:translateY(-1px);
        }
        .upload-icon{
            width:54px;
            height:54px;
            flex:0 0 auto;
            display:grid;
            place-items:center;
            border-radius:17px;
            color:var(--g360-cyan);
            background:rgba(34,211,238,.09);
            border:1px solid rgba(34,211,238,.20);
            font-size:24px;
        }
        .upload-copy{ min-width:0; flex:1; }
        .upload-copy strong{ display:block; color:var(--g360-text); font-size:13px; }
        .upload-copy span{ display:block; margin-top:4px; color:var(--g360-muted-2); font-size:11px; }
        .form-control-file{
            width:100%;
            margin-top:9px;
            color:var(--g360-muted);
            font-size:11px;
        }
        .form-control-file::file-selector-button{
            margin-right:10px;
            padding:7px 11px;
            border:1px solid rgba(34,211,238,.28);
            border-radius:10px;
            color:#eefdff;
            background:rgba(34,211,238,.10);
            cursor:pointer;
            font-weight:800;
        }
        #previewImage{ margin-top:12px; }
        #previewImage img{
            width:100%;
            max-width:320px;
            max-height:220px;
            object-fit:cover;
            display:block;
            border-radius:17px;
            border:1px solid var(--g360-stroke);
            box-shadow:0 18px 44px rgba(0,0,0,.34);
        }
        .preview-meta{
            margin-top:8px;
            color:var(--g360-muted);
            font-size:11px;
        }

        /* Sidebar */
        .assistant-card{
            position:sticky;
            top:calc(var(--g360-safe-top) + 18px);
            overflow:hidden;
        }
        .assistant-head{
            padding:18px;
            border-bottom:1px solid var(--g360-stroke);
            background:linear-gradient(135deg, rgba(38,140,255,.16), rgba(34,211,238,.06));
        }
        .assistant-head__top{
            display:flex;
            align-items:center;
            gap:11px;
        }
        .assistant-icon{
            width:42px;
            height:42px;
            display:grid;
            place-items:center;
            border-radius:14px;
            background:rgba(34,211,238,.10);
            border:1px solid rgba(34,211,238,.22);
            color:var(--g360-cyan);
            font-size:20px;
        }
        .assistant-head h3{ margin:0; color:var(--g360-text); font-size:15px; font-weight:900; }
        .assistant-head p{ margin:3px 0 0; color:var(--g360-muted); font-size:11px; }
        .assistant-body{ padding:17px; }
        .completion-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:8px;
            color:var(--g360-muted);
            font-size:11px;
            font-weight:800;
        }
        .completion-row strong{ color:var(--g360-text); font-size:14px; }
        .completion-track{
            height:9px;
            overflow:hidden;
            border-radius:999px;
            border:1px solid rgba(255,255,255,.08);
            background:rgba(255,255,255,.055);
        }
        .completion-fill{
            display:block;
            width:0;
            height:100%;
            border-radius:inherit;
            background:linear-gradient(90deg, var(--g360-blue), var(--g360-cyan), var(--g360-green));
            box-shadow:0 0 15px rgba(34,211,238,.30);
            transition:width .25s ease;
        }
        .assistant-divider{ height:1px; margin:17px 0; background:var(--g360-stroke); }
        .type-summary{
            padding:12px;
            border:1px solid var(--g360-stroke);
            border-radius:15px;
            background:rgba(4,14,28,.42);
        }
        .type-summary small{ color:var(--g360-muted-2); font-size:9px; text-transform:uppercase; letter-spacing:.8px; font-weight:900; }
        .type-summary strong{ display:block; margin-top:5px; color:var(--g360-text); font-size:13px; }
        .tips-list{ display:grid; gap:10px; margin-top:15px; }
        .tip-item{
            display:grid;
            grid-template-columns:28px minmax(0,1fr);
            gap:9px;
            align-items:start;
        }
        .tip-index{
            width:28px;
            height:28px;
            display:grid;
            place-items:center;
            border-radius:10px;
            color:var(--g360-cyan);
            background:rgba(34,211,238,.07);
            border:1px solid rgba(34,211,238,.15);
            font-size:10px;
            font-weight:950;
        }
        .tip-item p{ margin:1px 0 0; color:var(--g360-muted); font-size:11px; line-height:1.45; }
        .security-note{
            display:flex;
            gap:9px;
            margin-top:16px;
            padding:11px;
            border-radius:14px;
            border:1px solid rgba(53,242,154,.16);
            background:rgba(53,242,154,.055);
            color:rgba(224,255,241,.74);
            font-size:10.5px;
            line-height:1.45;
        }
        .security-note i{ color:var(--g360-green); font-size:16px; flex:0 0 auto; }

        /* Acciones */
        .action-bar{
            position:sticky;
            bottom:14px;
            z-index:999;
            margin-top:18px;
            display:flex;
            justify-content:center;
            pointer-events:none;
        }
        .action-inner{
            width:min(760px,100%);
            display:flex;
            justify-content:flex-end;
            gap:10px;
            padding:10px;
            border:1px solid rgba(255,255,255,.15);
            border-radius:19px;
            background:rgba(6,16,31,.82);
            box-shadow:0 22px 65px rgba(0,0,0,.42);
            backdrop-filter:blur(18px);
            pointer-events:auto;
        }
        .btn-saas{
            min-height:46px;
            display:inline-flex !important;
            align-items:center;
            justify-content:center;
            gap:8px;
            padding:0 17px !important;
            border-radius:13px !important;
            font-size:13px;
            font-weight:900 !important;
            transition:transform .18s ease, filter .18s ease, box-shadow .18s ease !important;
        }
        .btn-saas:hover{ transform:translateY(-1px); }
        .btn-saas-secondary{
            color:var(--g360-text) !important;
            border:1px solid var(--g360-stroke) !important;
            background:rgba(255,255,255,.055) !important;
        }
        .btn-saas-primary{
            min-width:170px;
            color:#03111d !important;
            border:0 !important;
            background:linear-gradient(135deg, var(--g360-cyan), #5be6ff 48%, var(--g360-green)) !important;
            box-shadow:0 14px 30px rgba(34,211,238,.19), inset 0 1px 0 rgba(255,255,255,.50);
        }
        .btn-saas-primary:hover{ filter:brightness(1.04); box-shadow:0 18px 36px rgba(34,211,238,.25); }

        .campo-visita,
        .campo-compromiso{
            transition:opacity .18s ease, transform .18s ease;
        }

        @media (max-width:1199.98px){
            .form-workspace{ grid-template-columns:minmax(0,1fr) 280px; }
        }
        @media (max-width:991.98px){
            .hero-layout{ grid-template-columns:1fr; }
            .hero-status{ grid-template-columns:repeat(2,minmax(0,1fr)); min-width:0; }
            .form-workspace{ grid-template-columns:1fr; }
            .assistant-card{ position:relative; top:auto; }
            .field-span-4{ grid-column:span 6; }
        }
        @media (max-width:767.98px){
            .form-hero{ padding:20px; }
            .hero-brand{ align-items:flex-start; flex-direction:column; }
            .hero-logo{ width:165px; }
            .hero-status{ grid-template-columns:1fr; }
            .form-card-header{ align-items:flex-start; flex-direction:column; }
            .form-card-body{ padding:14px; }
            .form-section{ padding:15px; }
            .field-span-8,.field-span-6,.field-span-4{ grid-column:span 12; }
            .action-inner{ justify-content:stretch; }
            .action-inner .btn-saas{ flex:1; }
        }
        @media (max-width:575.98px){
            .pcoded-main-container .pcoded-content{ padding-left:12px !important; padding-right:12px !important; }
            .hero-title{ font-size:27px; }
            .upload-box{ align-items:flex-start; flex-direction:column; }
            .action-inner{ flex-direction:column-reverse; border-radius:17px; }
            .btn-saas-primary{ width:100%; }
        }
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="page-header">
                <div class="page-block">
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <div>
                            <h5>Registro de gestión territorial</h5>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Visitas y compromisos</a></li>
                                <li class="breadcrumb-item active">Nuevo registro</li>
                            </ul>
                        </div>
                        <?php include './admin/include/btn_back.php'; ?>
                    </div>
                </div>
            </div>

            <section class="form-hero">
                <div class="hero-layout">
                    <div class="hero-brand">
                        <img src="assets/img/gob360l.png" alt="Logo GOB360" class="hero-logo">
                        <div>
                            <p class="hero-kicker">Módulo territorial · GOB360</p>
                            <h1 class="hero-title">Nueva visita o compromiso</h1>
                            <p class="hero-copy">
                                Registra la gestión realizada en territorio, asigna responsables y conserva evidencia para asegurar seguimiento, trazabilidad y toma de decisiones.
                            </p>
                        </div>
                    </div>

                    <div class="hero-status">
                        <div class="status-pill"><span class="status-dot"></span> Sistema disponible</div>
                        <div class="status-pill"><i class="feather icon-shield"></i> Registro protegido</div>
                    </div>
                </div>
            </section>

            <div class="form-workspace">
                <main class="saas-form-card">
                    <div class="form-card-header">
                        <div>
                            <h2>Información del registro</h2>
                            <p>Completa los datos obligatorios y verifica la información antes de guardar.</p>
                        </div>
                        <span class="form-id-chip"><i class="feather icon-activity"></i> Trazabilidad activa</span>
                    </div>

                    <div class="form-card-body">
                        <form class="needs-validation" novalidate id="ingresoVisita">

                            <section class="form-section">
                                <div class="section-head">
                                    <span class="section-number">01</span>
                                    <div>
                                        <h3>Datos generales</h3>
                                        <p>Define la fecha y el tipo de registro que deseas crear.</p>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group field-span-6">
                                        <div class="field-label-row">
                                            <label for="date">Fecha del registro</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-calendar"></i>
                                            <input type="date" class="form-control" id="date" name="date" required>
                                        </div>
                                        <div class="field-help"><i class="feather icon-info"></i> Fecha en la que se realizó la gestión territorial.</div>
                                    </div>

                                    <div class="form-group field-span-6">
                                        <div class="field-label-row">
                                            <label for="tipo_registro">Tipo de registro</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-layers"></i>
                                            <select class="form-control" id="tipo_registro" name="tipo_registro" required>
                                                <option value="">Seleccione una opción</option>
                                                <option value="Visita">Visita</option>
                                                <option value="Compromiso">Compromiso</option>
                                            </select>
                                        </div>
                                        <div class="field-help"><i class="feather icon-zap"></i> El formulario se ajustará automáticamente a tu selección.</div>
                                    </div>
                                </div>
                            </section>

                            <section class="form-section">
                                <div class="section-head">
                                    <span class="section-number">02</span>
                                    <div>
                                        <h3>Ubicación territorial</h3>
                                        <p>Relaciona la gestión con la provincia y el municipio correspondiente.</p>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group field-span-4">
                                        <div class="field-label-row">
                                            <label for="provincia">Provincia</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-map"></i>
                                            <select class="form-control" id="provincia" name="provincia" onchange="DEPARTAMENTO.onProvinciaChange();" required>
                                                <?php echo $optionProv; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group field-span-4">
                                        <div class="field-label-row">
                                            <label for="tbl_departamento_id">Departamento</label>
                                            <span class="required-badge">Fijo</span>
                                        </div>
                                        <select class="form-control ocultar-select" onchange="DEPARTAMENTO.getMunicipios();" id="tbl_departamento_id" name="tbl_departamento_id">
                                            <?php echo $optionDep; ?>
                                        </select>
                                        <div class="field-control">
                                            <i class="feather icon-map-pin"></i>
                                            <input type="text" class="form-control" value="68 - Santander" disabled aria-label="Departamento Santander">
                                        </div>
                                    </div>

                                    <div class="form-group field-span-4">
                                        <div class="field-label-row">
                                            <label for="tbl_municipio_id">Municipio</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-navigation"></i>
                                            <select class="form-control" onchange="DEPARTAMENTO.getVeredasByMunicipioId();" id="tbl_municipio_id" name="tbl_municipio_id" required>
                                                <option value="">Seleccione un municipio</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="form-section">
                                <div class="section-head">
                                    <span class="section-number">03</span>
                                    <div>
                                        <h3>Descripción y responsables</h3>
                                        <p>Los campos visibles dependen de si registras una visita o un compromiso.</p>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group field-span-6 campo-visita">
                                        <div class="field-label-row">
                                            <label for="tipo_visita">Tipo de visita</label>
                                            <span class="optional-badge">Visita</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-briefcase"></i>
                                            <select class="form-control" id="tipo_visita" name="tipo_visita">
                                                <option value="">Seleccione el tipo de visita</option>
                                                <option value="Reunión">Reunión</option>
                                                <option value="en Trámite">Ruta 25</option>
                                                <option value="Brigada Civico Social">Brigada Cívico Social</option>
                                                <option value="Consejo de Seguridad">Consejo de Seguridad</option>
                                                <option value="Concejos y/o Juntas Directivas">Consejos y/o Juntas Directivas</option>
                                                <option value="Inauguración de festividades">Inauguración de festividades</option>
                                                <option value="Seguimiento de Obras">Seguimiento de Obras</option>
                                                <option value="Seguimiento de Planes, Programas y Proyectos">Seguimiento de Planes, Programas y Proyectos</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group field-span-6 campo-compromiso">
                                        <div class="field-label-row">
                                            <label for="tbl_secretarias_id">Secretaría o dependencia encargada</label>
                                            <span class="optional-badge">Compromiso</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-briefcase"></i>
                                            <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id"><?php echo $optionSec; ?></select>
                                        </div>
                                    </div>

                                    <div class="form-group field-span-6 campo-compromiso">
                                        <div class="field-label-row">
                                            <label for="requiere_respuesta">¿Requiere respuesta?</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-message-circle"></i>
                                            <select class="form-control" id="requiere_respuesta" name="requiere_respuesta">
                                                <option value="">Seleccione una opción</option>
                                                <option value="Si">Sí</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group field-span-6 campo-compromiso">
                                        <div class="field-label-row">
                                            <label for="tipo_ejecucion">Tipo de ejecución</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-trending-up"></i>
                                            <select class="form-control" id="tipo_ejecucion" name="tipo_ejecucion">
                                                <option value="">Seleccione una opción</option>
                                                <option value="GESTIÓN">GESTIÓN</option>
                                                <option value="INVERSIÓN">INVERSIÓN</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group field-span-12 campo-compromiso">
                                        <div class="field-label-row">
                                            <label for="componente">Componente</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control">
                                            <i class="feather icon-grid"></i>
                                            <select class="form-control" id="componente" name="componente">
                                                <option value="">Seleccione un componente</option>
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
                                                <option value="INFRAESTRUCTURA ESCOLAR">INFRAESTRUCTURA ESCOLAR</option>
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

                                    <div class="form-group field-span-12 campo-compromiso">
                                        <div class="field-label-row">
                                            <label for="compromisopac">Compromiso pactado</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control textarea-control">
                                            <i class="feather icon-edit-3"></i>
                                            <textarea required placeholder="Describe claramente el compromiso, alcance, responsables y resultado esperado" class="form-control" id="compromisopac" name="compromisopac" rows="4"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group field-span-12 campo-visita">
                                        <div class="field-label-row">
                                            <label for="compromisos">Detalles de la visita</label>
                                            <span class="required-badge">Obligatorio</span>
                                        </div>
                                        <div class="field-control textarea-control">
                                            <i class="feather icon-file-text"></i>
                                            <textarea required placeholder="Describe el objetivo, participantes, actividades realizadas y conclusiones de la visita" class="form-control" id="compromisos" name="compromisos" rows="4"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="form-section">
                                <div class="section-head">
                                    <span class="section-number">04</span>
                                    <div>
                                        <h3>Evidencia del registro</h3>
                                        <p>Adjunta una fotografía clara que permita comprobar la gestión realizada.</p>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group field-span-12">
                                        <div class="field-label-row">
                                            <label for="img">Imagen de evidencia</label>
                                            <span class="optional-badge">JPG, PNG o WEBP</span>
                                        </div>

                                        <div class="upload-box" id="uploadBox">
                                            <div class="upload-icon"><i class="feather icon-image"></i></div>
                                            <div class="upload-copy">
                                                <strong>Selecciona o arrastra una fotografía</strong>
                                                <span>Utiliza una imagen nítida, bien iluminada y relacionada directamente con la visita o compromiso.</span>
                                                <input type="file" class="form-control-file" id="img" accept="image/*">
                                                <div id="previewImage"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <div class="action-bar">
                                <div class="action-inner">
                                    <button type="button" class="btn btn-saas btn-saas-secondary" id="btnResetForm">
                                        <i class="feather icon-refresh-cw"></i> Limpiar
                                    </button>
                                    <button type="button" class="btn btn-primary btn-saas btn-saas-primary" id="guardaVisita" onclick="VISITAS.validateData();">
                                        <i class="feather icon-save"></i> Guardar registro
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </main>

                <aside class="assistant-card">
                    <div class="assistant-head">
                        <div class="assistant-head__top">
                            <div class="assistant-icon"><i class="feather icon-check-square"></i></div>
                            <div>
                                <h3>Asistente de registro</h3>
                                <p>Validación visual en tiempo real</p>
                            </div>
                        </div>
                    </div>

                    <div class="assistant-body">
                        <div class="completion-row">
                            <span>Formulario completado</span>
                            <strong id="completionValue">0%</strong>
                        </div>
                        <div class="completion-track">
                            <span class="completion-fill" id="completionFill"></span>
                        </div>

                        <div class="assistant-divider"></div>

                        <div class="type-summary">
                            <small>Tipo seleccionado</small>
                            <strong id="typeSummary">Sin seleccionar</strong>
                        </div>

                        <div class="tips-list">
                            <div class="tip-item">
                                <span class="tip-index">01</span>
                                <p>Selecciona primero el tipo de registro para ver únicamente los campos necesarios.</p>
                            </div>
                            <div class="tip-item">
                                <span class="tip-index">02</span>
                                <p>Verifica provincia y municipio antes de registrar la descripción.</p>
                            </div>
                            <div class="tip-item">
                                <span class="tip-index">03</span>
                                <p>Describe hechos verificables y evita textos demasiado generales.</p>
                            </div>
                            <div class="tip-item">
                                <span class="tip-index">04</span>
                                <p>La evidencia fotográfica fortalece el seguimiento y la trazabilidad.</p>
                            </div>
                        </div>

                        <div class="security-note">
                            <i class="feather icon-shield"></i>
                            <span>La información registrada queda asociada al usuario y al proceso institucional correspondiente.</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <?php include 'admin/include/gerenic_script.php'; ?>
    <script type="text/javascript" src="admin/js/detalle_visitas.js"></script>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('ingresoVisita');
            const dateInput = document.getElementById('date');
            const tipoRegistro = document.getElementById('tipo_registro');
            const completionValue = document.getElementById('completionValue');
            const completionFill = document.getElementById('completionFill');
            const typeSummary = document.getElementById('typeSummary');
            const resetButton = document.getElementById('btnResetForm');
            const fileInput = document.getElementById('img');
            const preview = document.getElementById('previewImage');
            const uploadBox = document.getElementById('uploadBox');

            // Fecha actual como valor inicial cuando el formulario está vacío.
            if (dateInput && !dateInput.value) {
                const now = new Date();
                const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                    .toISOString()
                    .slice(0, 10);
                dateInput.value = localDate;
            }

            // Prefijar Santander y cargar municipios sin alterar la lógica existente.
            setTimeout(function () {
                if (window.jQuery) {
                    $('#tbl_departamento_id').val('68');
                    const provinciaSeleccionada = $('#provincia').val();
                    if (window.DEPARTAMENTO) {
                        if (provinciaSeleccionada) {
                            DEPARTAMENTO.onProvinciaChange();
                        } else {
                            DEPARTAMENTO.getMunicipios();
                        }
                    }
                }
                updateCompletion();
            }, 120);

            function isElementVisible(element) {
                if (!element) return false;
                const parent = element.closest('.campo-visita, .campo-compromiso, .form-group');
                if (!parent) return true;
                return window.getComputedStyle(parent).display !== 'none' && !parent.hidden;
            }

            function getRequiredFields() {
                if (!form) return [];
                const baseIds = ['date', 'tipo_registro', 'provincia', 'tbl_municipio_id'];
                const type = tipoRegistro ? tipoRegistro.value : '';

                if (type === 'Visita') {
                    baseIds.push('tipo_visita', 'compromisos');
                } else if (type === 'Compromiso') {
                    baseIds.push('tbl_secretarias_id', 'requiere_respuesta', 'componente', 'tipo_ejecucion', 'compromisopac');
                }

                return baseIds
                    .map(id => document.getElementById(id))
                    .filter(field => field && isElementVisible(field));
            }

            function updateCompletion() {
                const requiredFields = getRequiredFields();
                const completed = requiredFields.filter(field => String(field.value || '').trim() !== '').length;
                const percent = requiredFields.length ? Math.round((completed / requiredFields.length) * 100) : 0;

                if (completionValue) completionValue.textContent = percent + '%';
                if (completionFill) completionFill.style.width = percent + '%';
                if (typeSummary) typeSummary.textContent = tipoRegistro && tipoRegistro.value ? tipoRegistro.value : 'Sin seleccionar';
            }

            if (form) {
                form.addEventListener('input', updateCompletion);
                form.addEventListener('change', function () {
                    setTimeout(updateCompletion, 50);
                });
            }

            if (resetButton && form) {
                resetButton.addEventListener('click', function () {
                    form.reset();
                    if (preview) preview.innerHTML = '';
                    if (dateInput) {
                        const now = new Date();
                        dateInput.value = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                            .toISOString()
                            .slice(0, 10);
                    }
                    if (window.jQuery) {
                        $('#tbl_departamento_id').val('68');
                    }
                    if (window.DEPARTAMENTO) {
                        DEPARTAMENTO.getMunicipios();
                    }
                    setTimeout(updateCompletion, 100);
                });
            }

            function renderPreview(file) {
                if (!preview) return;
                preview.innerHTML = '';
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    preview.innerHTML = "<div class='text-danger mt-2'>Archivo no válido. Selecciona una imagen.</div>";
                    return;
                }

                const image = document.createElement('img');
                image.alt = 'Vista previa de la evidencia';
                image.loading = 'lazy';
                image.src = URL.createObjectURL(file);

                const meta = document.createElement('div');
                meta.className = 'preview-meta';
                meta.textContent = file.name + ' · ' + Math.max(1, Math.round(file.size / 1024)) + ' KB';

                preview.appendChild(image);
                preview.appendChild(meta);
            }

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    renderPreview(this.files && this.files[0]);
                });
            }

            if (uploadBox && fileInput) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    uploadBox.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        uploadBox.classList.add('is-dragging');
                    });
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    uploadBox.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        uploadBox.classList.remove('is-dragging');
                    });
                });

                uploadBox.addEventListener('drop', function (event) {
                    const files = event.dataTransfer && event.dataTransfer.files;
                    if (!files || !files.length) return;

                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(files[0]);
                        fileInput.files = dataTransfer.files;
                    } catch (error) {
                        // Algunos navegadores no permiten modificar fileInput.files.
                    }
                    renderPreview(files[0]);
                });
            }

            updateCompletion();
        });
    </script>
</body>
</html>
