<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

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
    if ($exists == !false) { // (dejo igual tu lógica)
        $final =  substr($final, 0, $exists);
        return $final;
    } else {
        return $final;
    }
}

require_once './admin/include/generic_classes.php';
include './admin/classes/DesarrolloAlcalde.php';

// Obtener permisos
$permissions = PagePermissions::crudForCurrentPage();

$modulo = 'Metas Plan de Desarrollo - Alcalde';

// filtra POR municipio
$rol_usuario = SessionData::getUserType();
$esAdminDelete = in_array($rol_usuario, ['SuperAdministrador', 'Administrador']);

// Obtener sectores distintos para filtros
try {
    $dbSectores = new DbConection();
    $pdoS = $dbSectores->openConect();
    $tableS = $dbSectores->getTable('tbl_plandesarrollo_alcalde');
    $sectoresPDD = $pdoS->query("SELECT DISTINCT sector_pdd FROM {$tableS} WHERE sector_pdd IS NOT NULL AND sector_pdd != '' ORDER BY sector_pdd")->fetchAll(PDO::FETCH_COLUMN);
    $sectoresCatalogo = $pdoS->query("SELECT DISTINCT sector_catalogo FROM {$tableS} WHERE sector_catalogo IS NOT NULL AND sector_catalogo != '' ORDER BY sector_catalogo")->fetchAll(PDO::FETCH_COLUMN);
    $dbSectores->closeConect();
} catch (Exception $e) {
    $sectoresPDD = [];
    $sectoresCatalogo = [];
}
?>

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

    <!-- Manejo de mensajes de sesión -->
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['success_message'])) {
        echo '<script>Swal.fire({icon: "success", title: "Éxito", text: "' . addslashes($_SESSION['success_message']) . '"});</script>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<script>Swal.fire({icon: "error", title: "Error", text: "' . addslashes($_SESSION['error_message']) . '"});</script>';
        unset($_SESSION['error_message']);
    }
    ?>

    <style>
        :root{
            --nav-blue:#20427F;
            --nav-blue-2:#132b52;
            --nav-blue-3:#2e58a8;

            --bg0:#0b1220;
            --bg1:#0e1830;

            --glass: rgba(255,255,255,.06);
            --glass2: rgba(255,255,255,.08);
            --line: rgba(255,255,255,.10);

            --paper:#ffffff;
            --ink:#0f172a;
            --muted:#94a3b8;

            --shadow: 0 18px 55px rgba(0,0,0,.38);
            --shadow2: 0 12px 30px rgba(0,0,0,.22);
            --radius:18px;

            --focus: rgba(34,193,255,.18);
        }

        /* Fondo SaaS */
        .pcoded-main-container{
            background:
                radial-gradient(900px 600px at 18% 10%, rgba(120,88,255,.18), transparent 55%),
                radial-gradient(900px 600px at 85% 18%, rgba(0,187,255,.14), transparent 55%),
                linear-gradient(180deg, var(--bg0), var(--bg1));
            min-height: 100vh;
        }

        /* HERO */
        .au-hero{
            position: relative;
            border-radius: 22px;
            overflow: hidden;
            margin: 12px 0 18px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.05);
        }
        .au-hero__bg{
            position:absolute; inset:0;
            background:
                radial-gradient(900px 520px at 18% 18%, rgba(0,187,255,.16), transparent 62%),
                radial-gradient(900px 520px at 82% 18%, rgba(120,88,255,.18), transparent 62%),
                linear-gradient(135deg, rgba(32,62,92,.92), rgba(47,63,110,.82));
            filter: saturate(1.1) contrast(1.05);
        }
        .au-hero__content{
            position: relative;
            padding: 18px 18px 16px;
            color: rgba(255,255,255,.92);
        }
        .au-kicker{
            display:inline-flex; align-items:center; gap:8px;
            font-weight: 1000; font-size: 12px;
            letter-spacing: .3px; text-transform: uppercase;
            color: rgba(255,255,255,.72);
            margin-bottom: 6px;
        }
        .au-dot{
            width: 8px; height: 8px; border-radius: 999px;
            background: linear-gradient(135deg, #22c1ff, #7b61ff);
            box-shadow: 0 0 0 4px rgba(255,255,255,.08);
        }
        .au-title{
            margin:0;
            font-weight: 1000;
            letter-spacing: .2px;
            color: rgba(226,232,240,.96);
            text-shadow: 0 10px 26px rgba(0,0,0,.35);
        }
        .au-subtitle{
            color: rgba(255,255,255,.72);
            font-size: 13px;
            margin-top: 2px;
        }

        /* Cards glass */
        .card{
            border-radius: var(--radius);
            border: 1px solid var(--line);
            box-shadow: var(--shadow2);
            overflow: hidden;
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.05));
            backdrop-filter: blur(10px);
        }
        .card-header{
            background: linear-gradient(135deg, rgba(32,66,127,.25), rgba(19,43,82,.18)) !important;
            border-bottom: 1px solid rgba(255,255,255,.10) !important;
            font-weight: 1000 !important;
            color: rgba(255,255,255,.92) !important;
        }
        .card-header h5, .card-header h4{
            color: rgba(255,255,255,.92) !important;
        }

        /* Breadcrumb contrast */
        .page-header h5{ color: rgba(255,255,255,.92) !important; }
        .page-header .breadcrumb,
        .page-header .breadcrumb a{ color: rgba(255,255,255,.75) !important; }

        /* Sub-card upload mejora */
        .excel-shell{
            border-radius: 18px;
            border: 1px dashed rgba(255,255,255,.20);
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.05));
        }
        .excel-shell .card-header{
            padding: 18px !important;
        }
        .excel-shell .card-body{
            padding: 18px !important;
        }
        .excel-shell .form-control{
            border-radius: 14px !important;
            border: 1px solid rgba(255,255,255,.18) !important;
            background: rgba(2,6,23,.30) !important;
            color: rgba(255,255,255,.92) !important;
        }
        .excel-shell .form-control:focus{
            outline:none !important;
            box-shadow: 0 0 0 .2rem var(--focus) !important;
            border-color: rgba(34,193,255,.45) !important;
        }

        /* Botones */
        .btn{
            border-radius: 14px !important;
            font-weight: 1000 !important;
            letter-spacing: .2px;
        }
        .btn-primary{
            background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
            border: none !important;
            box-shadow: 0 10px 22px rgba(32,66,127,.20);
        }
        .btn-primary:hover{ transform: translateY(-1px); filter: brightness(1.04); }
        .btn-secondary{
            border: 1px solid rgba(255,255,255,.18) !important;
            background: rgba(255,255,255,.08) !important;
            color: rgba(255,255,255,.92) !important;
            box-shadow: 0 10px 22px rgba(2,6,23,.10);
        }
        .btn-success, .btn-info{
            border: none !important;
            box-shadow: 0 10px 22px rgba(2,6,23,.14);
            font-weight: 1000 !important;
        }
        .btn-danger{
            background: linear-gradient(135deg, #ef4444, #b91c1c) !important;
            border: 1px solid rgba(255,255,255,.14) !important;
            color: #fff !important;
        }

        /* Truncado */
        td.truncado {
            max-width: 240px;
            white-space: normal;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 12px;
            vertical-align: top;
        }
        td.truncado a {
            color: #1e40af;
            text-decoration: underline;
            cursor: pointer;
            font-weight: 900;
        }
        td.truncado a:hover { color: #0b3aa6; }

        /* =========================
           FILTROS "BRUTAL" (mejorados)
        ========================== */
        .filters-wrap {
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.05));
            box-shadow: var(--shadow2);
            padding: 14px;
            backdrop-filter: blur(10px);
        }
        .filters-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 1000;
            letter-spacing: .2px;
            color: rgba(255,255,255,.92);
            margin-bottom: 10px;
        }
        .filters-title i {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(34,193,255,.12);
            color: #22c1ff;
            font-size: 16px;
        }
        .filter-card {
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(2,6,23,.18);
            padding: 12px;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .filter-card:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 3px;
            width: 100%;
            background: linear-gradient(90deg, rgba(34,193,255,.85), rgba(120,88,255,.80));
        }
        .filter-label {
            font-size: .78rem;
            font-weight: 1000;
            color: rgba(255,255,255,.72);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 8px;
        }
        .filter-select {
            border-radius: 12px !important;
            border: 1px solid rgba(255,255,255,.18) !important;
            height: 40px; /* más compacto */
            padding: .40rem .80rem;
            font-weight: 800;
            color: rgba(255,255,255,.92) !important;
            background: rgba(2,6,23,.35) !important;
            transition: all .18s ease;
            box-shadow: 0 8px 18px rgba(0,0,0,.18);
        }
        .filter-select:focus {
            border-color: rgba(34,193,255,.55) !important;
            box-shadow: 0 0 0 .2rem var(--focus) !important;
            transform: translateY(-1px);
        }

        .btn-brutal {
            border-radius: 14px !important;
            padding: .62rem 1rem !important;
            font-weight: 1000 !important;
            letter-spacing: .2px;
            box-shadow: 0 12px 28px rgba(2, 6, 23, .14);
            transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
            border: 1px solid rgba(255, 255, 255, .14) !important;
        }
        .btn-outline-brutal {
            border-radius: 14px !important;
            padding: .62rem 1rem !important;
            font-weight: 1000 !important;
            border: 1px solid rgba(255,255,255,.18) !important;
            background: rgba(255,255,255,.08) !important;
            color: rgba(255,255,255,.92) !important;
            box-shadow: 0 10px 22px rgba(2,6,23,.10);
            transition: transform .16s ease, box-shadow .16s ease;
        }
        .btn-outline-brutal:hover, .btn-brutal:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
            box-shadow: 0 16px 34px rgba(2, 6, 23, .18);
        }
        .button-stack {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }
        .button-stack--filters {
            gap: 14px;
        }

        /* =========================
           TABLA: SIEMPRE BLANCA + TEXTO NEGRO
        ========================== */
        .table-wrap{
            display: flex;
            justify-content: center;
            padding: 8px 0 2px;
        }
        .table-shell{
            width: min(100%,1520px);
            background: rgba(255,255,255,.06) !important;
            border-radius:24px; overflow:hidden;
            border:1px solid rgba(255,255,255,.12);
            box-shadow: 0 22px 70px rgba(0,0,0,.34);
        }
        .table-shell__top{
            display:flex; align-items:center; justify-content:space-between; gap:18px;
            padding:20px 24px 16px;
            border-bottom:1px solid rgba(255,255,255,.10);
            background:rgba(0,0,0,.14);
        }
        .table-shell__eyebrow{
            display:inline-flex; align-items:center; gap:8px; margin-bottom:6px;
            color:rgba(255,255,255,.7); font-size:11px; font-weight:1000;
            letter-spacing:.14em; text-transform:uppercase;
        }
        .table-shell__eyebrow:before{
            content:""; width:9px; height:9px; border-radius:999px;
            background:linear-gradient(135deg,#22c1ff,#20427F);
            box-shadow:0 0 0 5px rgba(34,193,255,.12);
        }
        .table-shell__title{ margin:0; color:#fff; font-size:1.3rem; font-weight:1000; letter-spacing:-.02em; }
        .table-shell__subtitle{ margin-top:4px; color:rgba(255,255,255,.6); font-size:.92rem; line-height:1.45; }
        .table-shell__badge{
            display:inline-flex; align-items:center; justify-content:center;
            min-width:92px; padding:.7rem 1rem; border-radius:16px;
            background:linear-gradient(135deg,#203e5c,#2f3f6e); color:#fff;
            font-size:.78rem; font-weight:1000; letter-spacing:.06em; text-transform:uppercase;
            box-shadow:0 16px 36px rgba(32,62,92,.20);
        }
        .table-shell__body{ padding:18px 18px 14px; }
        .table-responsive--premium{ border-radius:18px; border:1px solid rgba(255,255,255,.10); overflow:auto; }

        #dynamictable{ margin:0 !important; font-size:11px !important; width:100% !important; }
        #dynamictable thead th{
            color:#fff !important;
            background: linear-gradient(135deg, #203e5c, #2f3f6e) !important;
            text-transform:uppercase; letter-spacing:.1px;
            font-size:10px !important; white-space:nowrap;
            text-align:center; vertical-align:middle !important;
            padding:8px 5px !important;
            border-color:rgba(255,255,255,.06) !important;
        }
        #dynamictable tbody tr{ background:transparent !important; }
        #dynamictable tbody td{
            color:rgba(255,255,255,.86) !important;
            background:transparent !important;
            border-top:1px solid rgba(255,255,255,.06) !important;
            vertical-align:middle; padding:6px 4px !important;
            line-height:1.25; font-size:10.5px !important; font-weight:700 !important;
        }
        #dynamictable tbody tr:nth-child(even) td{ background:rgba(255,255,255,.03) !important; }
        #dynamictable tbody tr:hover td{ background:rgba(255,255,255,.06) !important; }
        #dynamictable tbody td:first-child,
        #dynamictable tbody td:nth-child(7),
        #dynamictable tbody td:nth-child(8){
            text-align:center; font-weight:900;
        }
        #dynamictable tbody td:last-child{ vertical-align:middle; }
        #dynamictable .btn-sm{ border-radius:8px !important; padding:4px 8px !important; min-width:32px; font-size:10px !important; }
        #dynamictable .btn-sm i{ color:#fff !important; }

        /* DataTables UI */
        .dataTables_wrapper{ padding:4px 4px 0; }
        .dataTables_wrapper .row:first-child,
        .dataTables_wrapper .row:last-child{ margin-left:0; margin-right:0; }
        .dataTables_wrapper .row:first-child{ padding:0 2px 14px; align-items:center; }
        .dataTables_wrapper .row:last-child{ padding:14px 2px 2px; align-items:center; }
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select{
            border-radius:14px !important;
            border:1px solid rgba(255,255,255,.14) !important;
            padding:9px 12px !important; font-size:12.5px !important;
            outline:none !important;
            background:rgba(255,255,255,.06) !important;
            color:#fff !important;
        }
        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label{ color:#fff !important; font-weight:800; font-size:12.5px; margin-bottom:0; }
        .dataTables_wrapper .dataTables_filter{ text-align:right; }
        .dataTables_wrapper .dataTables_info{ font-size:12.5px; color:#fff !important; padding:10px 6px; font-weight:700; }
        .dataTables_wrapper .dataTables_paginate .paginate_button{
            border-radius:12px !important;
            color:rgba(255,255,255,.86) !important;
            border:1px solid rgba(255,255,255,.14) !important;
            background:rgba(255,255,255,.06) !important;
            padding:0.4em 0.9em !important;
            font-weight:800 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
            background:linear-gradient(135deg,#203e5c,#2f3f6e) !important;
            color:#fff !important;
            border:1px solid rgba(255,255,255,.20) !important;
            box-shadow:0 10px 24px rgba(32,62,92,.18);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
            background:rgba(255,255,255,.10) !important;
            color:#fff !important;
            border:1px solid rgba(255,255,255,.20) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover{
            color:rgba(255,255,255,.30) !important;
            background:transparent !important;
            border:1px solid transparent !important;
        }
        .table-empty{
            padding:26px 12px !important; text-align:center;
            color:rgba(255,255,255,.6) !important; font-weight:800;
        }

        /* Compacto en móvil */
        @media (max-width: 576px) {
            .filters-wrap { padding: 12px; }
            .filter-card { padding: 10px; }
            .filter-select { height: 42px; }
            td.truncado { max-width: 200px; }
            .button-stack > .btn,
            .button-stack > a.btn {
                width: 100%;
                justify-content: center;
            }
            .table-shell__top{
                padding: 16px;
            }
            .table-shell__body{
                padding: 12px;
            }
            .table-shell__badge{
                width: 100%;
            }
            .dataTables_wrapper .dataTables_filter{
                text-align: left;
                margin-top: 10px;
            }
        }
    </style>

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!-- HERO (reemplaza la sensación "plana" del breadcrumb) -->
            <div class="au-hero">
                <div class="au-hero__bg"></div>
                <div class="au-hero__content">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="au-kicker"><span class="au-dot"></span><span>ALCALDÍA • PLAN DE DESARROLLO</span></div>
                            <h2 class="au-title mb-1"><i data-feather="target"></i> Metas Plan de Desarrollo</h2>
                            <div class="au-subtitle">Gestión municipal: carga masiva por Excel y filtros por sector.</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php include './admin/include/btn_back.php'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de carga de archivo Excel -->
            <div class="row">
                <div class="col-12 col-xl-12">
                    <div class="card excel-shell my-4">
                        <div class="card-header">
                            <h5 class="mb-0">Creación de Metas del Plan de Desarrollo</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            if ($rol_usuario == 'Alcalde' || $rol_usuario == 'Auxiliar' || $rol_usuario == 'Auxiliar_Alcalde' || $rol_usuario === 'SuperAdministrador' || $rol_usuario === 'Gobernador' || $rol_usuario === 'Secretario_Gobernacion') {
                            ?>
                                <form id="formExcelPlan" name="formExcelPlan" method="post" enctype="multipart/form-data"
                                    action="admin/controllers/planDesarrolloAlcaldeCtrl.php?method=uploadExcel">
                                    <div class="mb-3">
                                        <label for="excelFile" class="form-label" style="color:rgba(255,255,255,.88); font-weight:1000;">
                                            Subir archivo de Excel <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="file" id="excelFile" name="file" accept=".xlsx,.xls" required />
                                        <div style="color:rgba(255,255,255,.70); font-size:12px; margin-top:6px;">
                                            Usa la plantilla oficial para evitar errores de estructura.
                                        </div>
                                    </div>

<!--                                     <div class="mb-3">
                                        <label for="replace_mode" style="display:flex; align-items:flex-start; gap:10px; background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.35); border-radius:8px; padding:12px 14px; cursor:pointer; margin:0;">
                                            <input type="checkbox" id="replace_mode" name="replace_mode" value="1" style="flex-shrink:0; margin-top:2px; width:16px; height:16px; cursor:pointer;" />
                                            <div>
                                                <div style="color:rgba(255,255,255,.92); font-weight:700; font-size:13px;">
                                                    <i class="feather icon-alert-triangle mr-1" style="color:#f87171;"></i>
                                                    Reemplazar datos existentes
                                                </div>
                                                <div style="color:rgba(255,200,200,.75); font-size:11px; margin-top:3px;">
                                                    Si marcas esta opción, se eliminarán todas las metas actuales del municipio antes de cargar el nuevo archivo.
                                                </div>
                                            </div>
                                        </label>
                                    </div> -->

                                    <div class="button-stack mt-3">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="feather icon-upload-cloud mr-2"></i> Subir Plan y Procesar
                                        </button>
                                        <a href="admin/controllers/planDesarrolloAlcaldeCtrl.php?method=downloadTemplate" class="btn btn-secondary px-4">
                                            <i class="feather icon-download mr-2"></i> Descargar plantilla Plan de desarrollo
                                        </a>
                                    </div>
                                </form>
                            <?php } else { ?>
                                <div style="color:rgba(255,255,255,.78); font-size:12.5px;">
                                    Tu rol no tiene permisos para carga masiva. Puedes consultar la información y aplicar filtros.
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de datos -->
            <div class="row">
                <div class="col-xl-12 col-md-12">
                    <div class="card table-card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Metas Plan de Desarrollo</h5>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> maximize</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            <!-- ✅ FILTROS POR SECTOR (MEJOR DISEÑO, MISMO JS/IDs) -->
                            <div class="filters-wrap mb-3">
                                <div class="filters-title">
                                    <i class="feather icon-filter"></i>
                                    <div>
                                        Filtros por Sector
                                        <div style="color:rgba(255,255,255,.68); font-weight:700; font-size:.82rem; margin-top:2px;">
                                            Aplica filtros exactos por <b>Sector PDD</b> y <b>Sector Catálogo</b>.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-md-5">
                                        <div class="filter-card">
                                            <div class="filter-label">Sector PDD</div>
                                            <select id="filtroSectorPDD" class="form-control filter-select">
                                                <option value="">Todos</option>
                                                <?php foreach ($sectoresPDD as $s): ?>
                                                <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-5">
                                        <div class="filter-card">
                                            <div class="filter-label">Sector Catálogo</div>
                                            <select id="filtroSectorCatalogo" class="form-control filter-select">
                                                <option value="">Todos</option>
                                                <?php foreach ($sectoresCatalogo as $s): ?>
                                                <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-2 d-grid">
                                        <button type="button" id="btnLimpiarFiltrosSector" class="btn btn-outline-brutal">
                                            <i class="feather icon-x-circle"></i> Limpiar
                                        </button>
                                    </div>

                                    <div class="col-12">
                                        <div class="button-stack button-stack--filters mt-2">
                                            <button type="button" id="btnAplicarAmbos" class="btn btn-primary btn-brutal">
                                                <i class="feather icon-check-circle"></i> Aplicar filtros
                                            </button>
                                            <button type="button" id="btnSoloPDD" class="btn btn-success btn-brutal">
                                                <i class="feather icon-layers"></i> Solo Sector PDD
                                            </button>
                                            <button type="button" id="btnSoloCatalogo" class="btn btn-info btn-brutal">
                                                <i class="feather icon-grid"></i> Solo Sector Catálogo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TABLA CENTRADA + BLANCA -->
                            <div class="table-wrap">
                                <div class="table-shell">
                                    <div class="table-shell__top">
                                        <div>
                                            <div class="table-shell__eyebrow">Panel central</div>
                                            <h3 class="table-shell__title">Tabla de metas organizada y ampliada</h3>
                                            <div class="table-shell__subtitle">Vista más limpia, centrada y cómoda para revisar sectores, productos, avances y responsables.</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-shell__badge">Vista Pro</div>
                                        </div>
                                    </div>
                                    <div class="table-shell__body">
                                    <div class="table-responsive table-responsive--premium p-0">
                                        <?php if ($esAdminDelete): ?>
                                        <div class="d-flex align-items-center gap-3 px-3 py-2" style="background:linear-gradient(135deg,#fef2f2,#fff5f5);border-bottom:1px solid #fecaca;">
                                            <button type="button" id="btnEliminarSeleccionados" class="btn btn-danger btn-sm" style="font-weight:700;font-size:12px;">
                                                <i class="feather icon-trash-2 mr-1"></i> Eliminar seleccionados (<span id="contSeleccionados">0</span>)
                                            </button>
                                            <small style="color:#991b1b;font-weight:600;">Marca los checkboxes y presiona Eliminar</small>
                                        </div>
                                        <?php endif; ?>
                                        <table id="dynamictable" class="table table-hover table-bordered table-sm w-100">
                                            <thead>
                                                <tr>
                                                    <?php if ($esAdminDelete): ?>
                                                        <th style="width:36px;"><input type="checkbox" id="chkTodos" title="Seleccionar todos" style="width:16px;height:16px;cursor:pointer;"></th>
                                                    <?php endif; ?>
                                                    <th>ID</th>
                                                    <th>EJE ESTRATÉGICO</th>
                                                    <th>SECTOR PDD</th>
                                                    <th>SECTOR CATÁLOGO DE PRODUCTOS</th>
                                                    <th>PRODUCTO, BIEN O SERVICIO PDD</th>
                                                    <th>2024</th>
                                                    <th>AVANCE 2024</th>
                                                    <th>AVANCE 2025</th>
                                                    <th>2025</th>
                                                    <th>2026</th>
                                                    <th>2027</th>
                                                    <th>SECRETARÍA RESPONSABLE</th>
                                                    <?php if ($esAdminDelete): ?>
                                                        <th>Acciones</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- card-body -->
                    </div><!-- card -->
                </div>
            </div>

        </div>
    </div>

    <?php include './admin/include/footer.php'; ?>
    <?php include 'admin/include/gerenic_script.php'; ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="./admin/js/datatables/jquery.dataTables.min.css">
    <script src="./admin/js/datatables/jquery.dataTables.min.js"></script>
    <style>
      .dataTables_wrapper .dataTables_paginate .paginate_button{
        color:rgba(255,255,255,.86) !important;
        background:rgba(255,255,255,.06) !important;
        border:1px solid rgba(255,255,255,.14) !important;
        border-radius:12px !important;
        padding:0.4em 0.9em !important;
        font-weight:800 !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.current,
      .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
        background:linear-gradient(135deg,#203e5c,#2f3f6e) !important;
        color:#fff !important;
        border:1px solid rgba(255,255,255,.20) !important;
        box-shadow:0 10px 24px rgba(32,62,92,.18);
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        background:rgba(255,255,255,.10) !important;
        color:#fff !important;
        border:1px solid rgba(255,255,255,.20) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
      .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover{
        color:rgba(255,255,255,.30) !important;
        background:transparent !important;
        border:1px solid transparent !important;
      }
      .dataTables_wrapper .dataTables_info,
      .dataTables_wrapper .dataTables_length label,
      .dataTables_wrapper .dataTables_filter label{ color:#fff !important; font-weight:800; }
      .dataTables_wrapper .dataTables_info{ font-size:12.5px; padding:10px 6px; }
      .dataTables_wrapper .dataTables_filter input,
      .dataTables_wrapper .dataTables_length select{
        border-radius:14px !important;
        border:1px solid rgba(255,255,255,.14) !important;
        padding:9px 12px !important;
        background:rgba(255,255,255,.06) !important;
        color:#fff !important;
      }
    </style>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="<?php echo Util::versionar('./admin/js/control-plan-desarrollo-alcalde.js'); ?>"></script>

    <!-- Filtros: recargan la tabla vía AJAX con los valores seleccionados -->
    <script>
    $(function() {
        var esAdminDel = <?= json_encode($esAdminDelete) ?>;
        var colOffset = esAdminDel ? 1 : 0;

        $(document).on('filtrosCambiados', function() {
            if ($.fn.dataTable && $.fn.dataTable.isDataTable('#dynamictable')) {
                $('#dynamictable').DataTable().ajax.reload();
            }
        });

        function aplicarFiltros() {
            $(document).trigger('filtrosCambiados');
        }

        $('#filtroSectorPDD, #filtroSectorCatalogo').on('change', aplicarFiltros);
        $('#btnAplicarAmbos').on('click', aplicarFiltros);
        $('#btnSoloPDD').on('click', function() {
            $('#filtroSectorCatalogo').val('');
            aplicarFiltros();
        });
        $('#btnSoloCatalogo').on('click', function() {
            $('#filtroSectorPDD').val('');
            aplicarFiltros();
        });
        $('#btnLimpiarFiltrosSector').on('click', function() {
            $('#filtroSectorPDD').val('');
            $('#filtroSectorCatalogo').val('');
            if ($.fn.dataTable && $.fn.dataTable.isDataTable('#dynamictable')) {
                $('#dynamictable').DataTable().search('').ajax.reload();
            }
        });
    });
    </script>

    <script>
        $(document).ready(function() {
            $('#formExcelPlan').on('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Cargando datos...',
                    text: 'Estamos procesando el archivo Excel.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                var formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.output && response.output.valid) {

                            let inserted = response.output.inserted;
                            let skipped = response.output.skipped;
                            let errors = response.output.errors || [];

                            let htmlContent = `Se han insertado <b>${inserted}</b> registros.<br>`;
                            if (skipped > 0) {
                                htmlContent += `<small>Se omitieron ${skipped} filas (ejemplos, incompletas o inválidas).</small><br>`;
                            }
                            if (errors.length > 0) {
                                htmlContent += `<small>Errores encontrados:<br>${errors.join('<br>')}</small>`;
                            }

                            if (errors.length > 0 || skipped > 0) {
                                Swal.fire({
                                    title: '¡Resultados del procesamiento de datos!',
                                    html: htmlContent,
                                    icon: 'warning',
                                    confirmButtonText: 'Aceptar'
                                }).then((result) => {
                                    if (result.isConfirmed) window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: '¡Proceso exitoso!',
                                    html: htmlContent,
                                    icon: 'success',
                                    confirmButtonText: 'Aceptar'
                                }).then((result) => {
                                    if (result.isConfirmed) window.location.reload();
                                });
                            }
                        } else {
                            Swal.fire('Error', (response.output && response.output.message) ? response.output.message : 'Error desconocido al procesar', 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error("Respuesta bruta del servidor:", xhr.responseText);
                        Swal.fire('Error de Formato', 'Error desconocido al procesar...', 'error');
                    }
                });
            });
        });
    </script>

</body>
</html>
