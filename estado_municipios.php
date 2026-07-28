<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

//Validación
/* if (!$create) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Visitas.php';
include './admin/classes/Visitasbuc.php';
include './admin/classes/Departamento.php';
include './admin/classes/Proyectos.php';
include './admin/classes/Compromisos.php';

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
    return str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
}

$mun = $_REQUEST["mun"] ?? null;

$arrVisitas = Visitas::getAll(["tbl_municipio_id" => $mun]);
$visitas = $arrVisitas["output"]["response"] ?? [];

$arrVisitasbuc = Visitasbuc::getAll(["tbl_municipio_id" => $mun]);
$visitasbuc = $arrVisitasbuc["output"]["response"] ?? [];

$arrCompromisos = Compromisos::getAll(["tbl_municipio_id" => $mun]);
$compromisos = $arrCompromisos["output"]["response"] ?? [];

$arrProyectos = Proyectos::getInversiontotal(["tbl_municipio_id" => $mun]);
$proyectos = $arrProyectos["output"]["response"] ?? [];

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalidDep = $arrDep['output']['valid'] ?? false;
$arrDep = $arrDep['output']['response'] ?? [];
$optionDep = Util::getDepartamentoPrincipal();

foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") .
        " value='" . htmlspecialchars($val['codigo_departamento']) . "'>" .
        htmlspecialchars($val['codigo_departamento']) . " - " . htmlspecialchars($val['departamento']) .
        "</option>";
}

// Información de compromisos
$arrCom = Compromisos::getAll($_REQUEST);
$isvalidCom = $arrCom['output']['valid'] ?? false;
$compromiso = $arrCom['output']['response'] ?? [];

// Información de secretarias
$arr = Proyectos::getAllproyectosxsecre($_REQUEST);
$isvalid = $arr['output']['valid'] ?? false;
$arr = $arr['output']['response'] ?? [];
$arrData = $arr;

// Promedio ejecución
$totalEjecutado = 0;
if (!empty($arrData)) {
    foreach ($arrData as $value) {
        $totalEjecutado += is_null($value["porcentaje_ejecucion"]) ? 0 : doubleval($value["porcentaje_ejecucion"]);
    }
    $totalEjecutado = $totalEjecutado == 0 ? 0 : round($totalEjecutado / count($arrData), 2);
}

$datosSecre = Proyectos::getInversionBySecre($_REQUEST);
$isvalidSecre = $datosSecre['output']['valid'] ?? false;
$arrSecre = $datosSecre['output']['response'] ?? ["labels" => [], "data" => []];

// =======================PROYECTOS========================
$arrTotal = Proyectos::getInversiontotal($_REQUEST);
$isvalidTotal = $arrTotal['output']['valid'] ?? false;
$arrTotalData = $arrTotal['output']['response'] ?? [];

$total_invertido = 0;
if (!empty($arrTotalData)) {
    foreach ($arrTotalData as $value) {
        $total_invertido += is_null($value["SumaDevalor_proyecto"]) ? 0 : doubleval($value["SumaDevalor_proyecto"]);
    }
    $total_invertido = $total_invertido == 0 ? 0 : round($total_invertido / count($arrTotalData), 2);
}

// Helpers
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<body class="dashboard-body">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <style>
        :root{
            --nav-blue:#20427F;
            --nav-blue-2:#132b52;
            --nav-blue-3:#2e58a8;

            --bg:#f6f8fc;
            --card:#ffffff;
            --ink:#0f172a;
            --muted:#64748b;

            --radius-xl:22px;
            --radius-lg:16px;
            --radius-md:14px;

            --shadow-soft: 0 12px 30px rgba(2, 6, 23, .12);
            --shadow-mid:  0 18px 45px rgba(2, 6, 23, .16);
            --ring: 0 0 0 .25rem rgba(46,88,168,.22);
        }

        body.dashboard-body{
            background: radial-gradient(1200px 500px at 10% -10%, rgba(46,88,168,.12), transparent 60%),
                        radial-gradient(900px 450px at 90% 0%, rgba(32,66,127,.10), transparent 65%),
                        var(--bg);
        }

        /* Contenedor más ancho en PC */
        .pcoded-main-container .pcoded-content{ padding-top: 18px !important; }
        @media (min-width: 1200px){
            .pcoded-main-container .pcoded-content{ max-width: 1400px; margin: 0 auto; }
        }

        /* Header premium */
        .page-header .page-block{
            background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(19,43,82,.06));
            border: 1px solid rgba(15,23,42,.08);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
            padding: 16px 18px;
        }
        .page-header h5{ color: var(--ink); font-weight: 900; letter-spacing: .2px; margin:0; }
        .breadcrumb{ margin-bottom:0; }

        /* Card principal */
        .saas-card{
            border-radius: var(--radius-xl);
            border: 1px solid rgba(15,23,42,.08);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            background: var(--card);
        }
        .saas-card .card-header{
            background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
            color:#fff;
            border-bottom: 1px solid rgba(255,255,255,.12);
            padding: 14px 16px;
        }
        .saas-card .card-header h5{
            color:#fff;
            font-weight: 900;
            margin:0;
            letter-spacing: .2px;
        }
        .saas-card .card-body{ padding: 16px; }

        /* Secciones */
        .section-block{
            margin: 18px 0 10px;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .section-title{
            margin:0;
            font-weight: 900;
            color: var(--ink);
            letter-spacing: .6px;
            text-transform: uppercase;
            background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(46,88,168,.06));
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 999px;
            padding: 10px 14px;
            box-shadow: 0 10px 24px rgba(2,6,23,.06);
            width: fit-content;
            max-width: 100%;
        }

        /* Form pro */
        .form-shell{
            border: 1px solid rgba(15,23,42,.08);
            border-radius: var(--radius-lg);
            padding: 14px;
            background: linear-gradient(180deg, rgba(246,248,252,.55), rgba(255,255,255,1));
            box-shadow: 0 10px 24px rgba(2,6,23,.06);
        }
        .form-group label{
            font-weight: 900;
            color: rgba(15,23,42,.78);
            margin-bottom: 6px;
        }
        .form-control{
            border-radius: 14px !important;
            border-color: rgba(15,23,42,.14) !important;
            padding: 10px 12px;
        }
        .form-control:focus{
            box-shadow: var(--ring) !important;
            border-color: rgba(46,88,168,.35) !important;
        }

        /* Tablas pro */
        .table-wrap{
            border: 1px solid rgba(15,23,42,.08);
            border-radius: var(--radius-xl);
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 24px rgba(2,6,23,.06);
        }
        .table-responsive{ overflow-x:auto; }
        table.w-100{ width: 100% !important; }

        .table{
            margin-bottom: 0;
            white-space: nowrap;
        }
        .table thead th{
            position: sticky;
            top: 0;
            z-index: 2;
            background: rgba(32,66,127,.08) !important;
            color: var(--ink);
            font-weight: 900;
            border-color: rgba(15,23,42,.10) !important;
        }
        .table td, .table th{
            vertical-align: middle;
            border-color: rgba(15,23,42,.10) !important;
        }

        /* Avatares */
        .avatar-mini{
            width: 36px;
            height: 36px;
            border-radius: 999px;
            object-fit: cover;
            border: 2px solid rgba(32,66,127,.18);
            box-shadow: 0 10px 18px rgba(2,6,23,.08);
        }

        /* Modales pro (sin romper bootstrap 4 data-toggle) */
        .modal-content{
            border-radius: var(--radius-xl) !important;
            overflow: hidden;
            border: 1px solid rgba(15,23,42,.10);
            box-shadow: var(--shadow-mid);
        }
        .modal-header{
            background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
            color:#fff;
            border-bottom: 1px solid rgba(255,255,255,.14);
        }
        .modal-title{ font-weight: 900; }
        .modal .close{ color:#fff; opacity: .95; text-shadow:none; }

        /* Gráficas cards */
        .chart-card{
            border-radius: var(--radius-xl);
            border: 1px solid rgba(15,23,42,.08);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }
        .chart-card .card-header{
            background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(46,88,168,.06));
            border-bottom: 1px solid rgba(15,23,42,.08);
            font-weight: 900;
        }

        /* Bucaramanga map */
        .mapa{
            width:100%;
            max-width: 820px;
            display:block;
            margin: 0 auto;
            border-radius: var(--radius-xl);
            border: 1px solid rgba(15,23,42,.08);
            box-shadow: 0 18px 40px rgba(2,6,23,.12);
        }

        @media (max-width: 575.98px){
            .saas-card .card-body{ padding: 14px; }
            .section-title{ font-size: 12px !important; }
        }
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!-- breadcrumb -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <h5 class="m-b-10 mb-0">Estado Municipios</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Estado Municipios</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="divEstadoMunicipio" class="row">
                <div class="col-12">
                    <div class="card saas-card">

                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5>Estado Municipios</h5>
                            <div class="small" style="opacity:.9;">
                                <i class="feather icon-map-pin me-1"></i>
                                <span>Vista de visitas, compromisos e inversión</span>
                            </div>
                        </div>

                        <div class="card-body">

                            <!-- filtros -->
                            <div class="form-shell mb-3">
                                <form id="formusuarios" role="form" autocomplete="false">
                                    <input type="hidden" name="op" id="op" />
                                    <input type="hidden" name="id" id="id" />

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="bmd-label-floating">Departamento</label>
                                                <select class="form-control" id="tbl_departamento_id" disabled name="tbl_departamento_id">
                                                    <?php echo $optionDep; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="bmd-label-floating">Municipio</label>
                                                <select onchange="ESTADO_MUN_GOBER.updateUrlMunicipio(this)"
                                                    class="form-control" id="tbl_municipio_id" name="tbl_municipio_id">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- VISITAS REALIZADAS -->
                            <div class="section-block">
                                <h3 class="section-title" style="font-size: 16px;">Visitas Realizadas</h3>
                            </div>

                            <div class="table-wrap mb-4">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th scope="col">Imagen</th>
                                                <th scope="col">Fecha</th>
                                                <th scope="col">Provincia</th>
                                                <th scope="col">Responsable</th>
                                                <th scope="col">Motivo Visita</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($visitas as $value) :
                                                $img = empty($value["img"]) ? 'dist/img/logorelsinf.png' : "assets/img/admin/usuarios/" . $value["img"];
                                            ?>
                                                <tr>
                                                    <td>
                                                        <img src="<?php echo h($img); ?>" alt="user"
                                                            class="avatar-mini"
                                                            style="cursor:pointer;"
                                                            data-toggle="modal"
                                                            data-target="#imageModal<?php echo h($value['id']); ?>">
                                                    </td>
                                                    <td><?php echo h($value["date"] ?? ''); ?></td>
                                                    <td><?php echo h($value["provincia"] ?? ''); ?></td>
                                                    <td><?php echo h($value["responsable"] ?? ''); ?></td>
                                                    <td><?php echo h($value["compromisos"] ?? ''); ?></td>
                                                </tr>

                                                <!-- Modal Imagen Visita -->
                                                <div class="modal fade" id="imageModal<?php echo h($value['id']); ?>" tabindex="-1" role="dialog"
                                                    aria-labelledby="imageModalLabel<?php echo h($value['id']); ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="imageModalLabel<?php echo h($value['id']); ?>">Imagen de Visita</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="<?php echo h($img); ?>" alt="user" class="img-fluid" style="border-radius:16px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- MAPA BUCARAMANGA -->
                            <div class="section-block">
                                <?php if ($mun === '68001'): ?>
                                    <h3 class="section-title">Mapa Bucaramanga</h3>
                                <?php endif; ?>
                            </div>

                            <?php if ($mun === '68001'): ?>
                                <div class="mb-4">
                                    <img src="assets/img/bucaramangaok.png" alt="Mapa Bucaramanga" class="mapa">
                                </div>
                            <?php endif; ?>

                            <!-- REGISTRO VISITA A COMUNA -->
                            <div class="section-block">
                                <h3 class="section-title text-center my-2" style="font-size:16px">Registro Visita a Comuna</h3>
                            </div>

                            <div class="table-wrap mb-4">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th>Imagen</th>
                                                <th>Fecha</th>
                                                <th>Comuna</th>
                                                <th>Barrio</th>
                                                <th>Beneficiario</th>
                                                <th>Responsable</th>
                                                <th>Motivo Visita</th>
                                                <th>Compromisos</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($visitasbuc as $value) : ?>
                                                <?php $img2 = (!empty($value["img"])) ? "assets/img/admin/usuarios/" . $value["img"] : 'dist/img/logorelsinf.png'; ?>
                                                <tr>
                                                    <td>
                                                        <img src="<?php echo h($img2); ?>" alt="user"
                                                            class="img-fluid"
                                                            style="width: 70px; border-radius: 16px; cursor:pointer; border:1px solid rgba(15,23,42,.10); box-shadow:0 10px 18px rgba(2,6,23,.08);"
                                                            data-toggle="modal"
                                                            data-target="#imgModal<?php echo h($value['id']); ?>">
                                                        <!-- Modal imagen grande -->
                                                        <div class="modal fade" id="imgModal<?php echo h($value['id']); ?>" tabindex="-1" role="dialog"
                                                            aria-labelledby="imgModalLabel<?php echo h($value['id']); ?>" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="imgModalLabel<?php echo h($value['id']); ?>">Imagen de la Visita</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body text-center">
                                                                        <img src="<?php echo h($img2); ?>" alt="user" class="img-fluid" style="border-radius:16px;">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo h($value["date"] ?? ''); ?></td>
                                                    <td><?php echo h($value["comuna"] ?? ''); ?></td>
                                                    <td><?php echo h($value["barrio"] ?? ''); ?></td>
                                                    <td><?php echo h($value["beneficiario"] ?? ''); ?></td>
                                                    <td><?php echo h($value["responsable"] ?? ''); ?></td>
                                                    <td><?php echo h($value["observaciones"] ?? ''); ?></td>
                                                    <td><?php echo h($value["compromisos"] ?? ''); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- COMPROMISOS PACTADOS -->
                            <div class="section-block">
                                <h3 class="section-title text-center" style="font-size:16px">Compromisos Pactados en el Municipio</h3>
                            </div>

                            <div class="table-wrap mb-4">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Compromiso</th>
                                                <th>Estado</th>
                                                <th>Respuesta</th>
                                                <th>Secretaría</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($compromisos as $value) : ?>
                                                <tr style="background-color: <?= htmlspecialchars($colorFila[$value['cumplimiento']] ?? 'transparent') ?>">
                                                    <td><?= h($value["date"] ?? '') ?></td>
                                                    <td><?= h($value["compromisos"] ?? '') ?></td>
                                                    <td><?= h($value["cumplimiento"] ?? '') ?></td>
                                                    <td><?= h($value["respuesta"] ?? '') ?></td>
                                                    <td><?= h($value["secretaria"] ?? '') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- GRAFICAS -->
                            <div class="row justify-content-center my-4 g-3">
                                <div class="col-12 col-lg-6">
                                    <div class="card chart-card h-100">
                                        <div class="card-header text-center" style="font-size:16px; color:#111; font-weight:900;">
                                            Estado de proyectos en general
                                        </div>
                                        <div class="card-body d-flex justify-content-center align-items-center">
                                            <div id="gender_donut" style="height: 250px; width: 100%; max-width: 420px;"></div>
                                        </div>
                                        <div class="card-footer d-flex justify-content-between">
                                            <div>
                                                <h2 class="mb-0" style="font-size:16px"><?= h($totalEjecutado) ?>%</h2>
                                                <p class="mb-0">Ejecutado</p>
                                            </div>
                                            <div class="text-end">
                                                <h2 class="mb-0" style="font-size:16px"><?= h(100 - $totalEjecutado) ?>%</h2>
                                                <p class="mb-0">Faltante</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <div class="card chart-card h-100">
                                        <div class="card-header text-center" style="font-size:16px; color:#111; font-weight:900;">
                                            Inversión por Secretarías
                                        </div>
                                        <div class="card-body">
                                            <div style="min-height: 280px;">
                                                <canvas id="chartjs_bar_horizontal" style="max-width: 100%;"></canvas>
                                            </div>
                                        </div>
                                        <div class="card-footer">&nbsp;</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inversión Detallada -->
                            <hr>
                            <h3 class="section-title text-center" style="font-size:16px">
                                Valor total inversión en el municipio en general:
                                <?= h('$ ' . number_format($total_invertido, 2, ',', '.')) ?>
                            </h3>
                            <hr>

                            <div class="section-block">
                                <h3 class="section-title text-center" style="font-size:16px">Inversión detallada por Secretaría</h3>
                            </div>

                            <div class="table-wrap">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th>Ver Detallado</th>
                                                <th>Secretaría</th>
                                                <th>Valor Proyecto</th>
                                                <th>Nombre Proyecto</th>
                                                <th>Porcentaje Ejecución</th>
                                                <th>Fecha Entrega</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($isvalid) : ?>
                                                <?php foreach ($arr as $item) : ?>
                                                    <tr>
                                                        <td>
                                                            <a href="reporte_secretarias.php?reporte=<?= h($item['id'] ?? '') ?>" target="_blank" title="Ver">
                                                                <i class="feather icon-eye"></i>
                                                            </a>
                                                        </td>
                                                        <td><?= h($item['secretaria'] ?? '') ?></td>
                                                        <td><?= h('$ ' . number_format((float)($item['valor_proyecto'] ?? 0), 2, ',', '.')) ?></td>
                                                        <td><?= h($item['proyecto'] ?? '') ?></td>
                                                        <td><?= h($item['porcentaje_ejecucion'] ?? 0) ?>%</td>
                                                        <td><?= h($item['fecha_entrega'] ?? '') ?></td>
                                                        <td><?= h($item['estado'] ?? '') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div><!-- card-body -->
                    </div><!-- saas-card -->
                </div><!-- col -->
            </div><!-- row -->

        </div><!-- pcoded-content -->
    </div><!-- pcoded-main-container -->

    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/estado_municipios_gobernador.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Morris.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>

    <script>
        const TOTAL_EJECUTADO = <?= json_encode($totalEjecutado) ?>;
        const TOTAL_POR_EJECUTAR = <?= json_encode(100 - $totalEjecutado) ?>;
        const LABELS_SECRETARIA = <?= json_encode($arrSecre["labels"] ?? []) ?>;
        const DATA_SECRETARIA = <?= json_encode($arrSecre["data"] ?? []) ?>;

        $(function() {
            "use strict";

            // Donut Morris
            if (document.getElementById('gender_donut')) {
                Morris.Donut({
                    element: 'gender_donut',
                    data: [{
                            value: parseFloat(TOTAL_EJECUTADO),
                            label: 'Ejecutado'
                        },
                        {
                            value: parseFloat(TOTAL_POR_EJECUTAR),
                            label: 'Por Ejecutar'
                        }
                    ],
                    labelColor: '#20427F',
                    colors: ['#20427F', '#ff407b'],
                    formatter: function(x) { return x + "%" }
                });
            }

            // ChartJS horizontal
            const el = document.getElementById("chartjs_bar_horizontal");
            if (el) {
                const ctx = el.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: LABELS_SECRETARIA,
                        datasets: [{
                            label: 'Total Invertido',
                            data: DATA_SECRETARIA,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const v = Number(context.raw || 0);
                                        return '$ ' + v.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) { return '$ ' + Number(value).toLocaleString(); }
                                }
                            },
                            y: { ticks: { font: { size: 12 } } }
                        }
                    }
                });
            }
        });

        // Select municipio
        const params = UTIL.getParamsFromUrlDepartamentoMunicipio();
        selectedMunicipio = params.mun;
        DEPARTAMENTO.getMunicipiosByDepartamentoIdV2SeteraCodigoMunicipio(UTIL.getDepartamentoPrincipal(), params.mun);

        function loadContentidoMapa() {
            const currentUrl = new URL(window.location.href);
            $.ajax({
                url: currentUrl.toString(),
                type: "GET",
                success: function(response) {
                    const updatedContent = $(response).find("#divEstadoMunicipio").html();
                    $("#divEstadoMunicipio").html(updatedContent);
                },
                error: function(error) {
                    console.error("Error al cargar contenido:", error);
                }
            });
        }
    </script>

</body>
</html>
