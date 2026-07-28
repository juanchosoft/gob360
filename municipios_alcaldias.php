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
    $final =  str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists == !false) {
        $final =  substr($final, 0, $exists);
        return $final;
    } else {
        return $final;
    }
}

require_once './admin/include/generic_classes.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Pilar.php';
require './admin/classes/Departamento.php';
include './admin/db/coloress.php';
include './admin/classes/Maing.php';
include './admin/classes/Secretarias.php';

// Obtener permisos
$permissions = PagePermissions::crudForCurrentPage();

// Validación de permiso de visualización
/* if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
} */


// Informacion del Main
$arr = Maing::getDataMain(null);
$isvalid = $arr['output']['valid'];
$visitas = $arr['output']['visitas'];
$impactada = $arr['output']['impactada'];
$inversion = $arr['output']['inversion'];


$departamento = new Departamento();
$santander = $departamento->getAll(["id" => Util::getIdentificadorDepartamentoPrincipal()]);
$santander = $santander["output"]["response"]["0"];
$code = null;
$mapa = null;

// Informacion de los pilares
$arr = Secretarias::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$optionSecretarias = "";
foreach ($arr as $val) {
    $optionSecretarias .= "<option value='" . $val['id'] . "'>" . $val['secretaria'] . " </option>";
}

if (isset($_GET['depto_id']) && in_array($_GET['depto_id'], [1, 12, 21])) {
    switch ($_GET['depto_id']) {

        case '21':
            $code = $santander["codigo_departamento"];
            $mapa = "admin/mapa-santander/mapa_secretarias.php";
            break;
    }
}
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/2.0.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap4.min.js"></script>
<!-- DataTables Select -->
<script src="https://cdn.datatables.net/select/2.0.0/js/dataTables.select.min.js"></script>
<script src="https://cdn.datatables.net/select/2.0.0/js/select.bootstrap4.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ navigation menu ] start -->
    <?php
    include './admin/include/navbar.php';
    ?>
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->
    <?php
    include './admin/include/header.php';
    ?>
    <!-- [ Header ] end -->

    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ breadcrumb ] start -->
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Informes Secretarias</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Informe Secretaria </a></li>
                                                <li class="breadcrumb-item"><a href="#!">Actividades</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ breadcrumb ] end -->
                            <!-- [ Main Content ] start -->
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="floating-label" for="Eje">Secretaria<span
                                            class="text-danger mb-1">*</span></label>
                                    <select class="form-control" id="secretaria" name="secretaria">
                                        <?php echo $optionSecretarias; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="card-body">
                                <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active text-uppercase" id="Soto_Norte-tab"
                                            data-bs-toggle="tab" href="#Soto_Norte" role="tab"
                                            aria-controls="Soto_Norte" aria-selected="true">Soto Norte</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-uppercase" id="Guanenta-tab" data-bs-toggle="tab"
                                            href="#Guanenta" role="tab" aria-controls="Guanenta"
                                            aria-selected="false">Guanentá</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-uppercase" id="Garcia_Rovira-tab" data-bs-toggle="tab"
                                            href="#Garcia_Rovira" role="tab" aria-controls="Garcia_Rovira"
                                            aria-selected="false">García Rovira</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-uppercase" id="Comunera-tab" data-bs-toggle="tab"
                                            href="#Comunera" role="tab" aria-controls="Comunera"
                                            aria-selected="false">Comunera</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-uppercase" id="Velez-tab" data-bs-toggle="tab"
                                            href="#Velez" role="tab" aria-controls="Velez"
                                            aria-selected="false">Velez</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-uppercase" id="Metropolitana-tab" data-bs-toggle="tab"
                                            href="#Metropolitana" role="tab" aria-controls="Metropolitana"
                                            aria-selected="false">Metropolitana</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-uppercase" id="Yariguíes-tab" data-bs-toggle="tab"
                                            href="#Yariguíes" role="tab" aria-controls="Yariguíes"
                                            aria-selected="false">Yariguíes</a>
                                    </li>
                                </ul>

                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="Soto_Norte" role="tabpanel"
                                        aria-labelledby="Soto_Norte-tab">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Total Población Impactada Soto Norte</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div id="bar-chart-Soto_Norte"></div>
                                                    <div class="col-md-12 col-xl-12 specific-card">
                                                        <div class="card flat-card">
                                                            <div class="row-table">
                                                                <!-- Item 1 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-map-pin text-c-blue mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión Departamento</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">$0
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 2 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-users text-c-red mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Porcentaje Ejecución Presupuesto</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">0%
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 3 -->
                                                                <div class="col-sm-4 card-body">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-check-circle text-c-green mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">
                                                                                <?php echo "$ " . number_format($inversion, 0, '.', ','); ?>
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="Guanenta" role="tabpanel"
                                        aria-labelledby="Guanenta-tab">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Total Población Impactada Guanentá</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div id="bar-chart-Guanenta"></div>
                                                    <div class="col-md-12 col-xl-12 specific-card">
                                                        <div class="card flat-card">
                                                            <div class="row-table">
                                                                <!-- Item 1 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-map-pin text-c-blue mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión Departamento</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">$0
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 2 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-users text-c-red mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Porcentaje Ejecución Presupuesto</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">0%
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 3 -->
                                                                <div class="col-sm-4 card-body">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-check-circle text-c-green mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">
                                                                                <?php echo "$ " . number_format($inversion, 0, '.', ','); ?>
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="Garcia_Rovira" role="tabpanel"
                                        aria-labelledby="Garcia_Rovira-tab">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Total Población Impactada García Rovira</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div id="bar-chart-Garcia_Rovira"></div>
                                                    <div class="col-md-12 col-xl-12 specific-card">
                                                        <div class="card flat-card">
                                                            <div class="row-table">
                                                                <!-- Item 1 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-map-pin text-c-blue mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión Departamento</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">$0
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 2 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-users text-c-red mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Porcentaje Ejecución Presupuesto</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">0%
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 3 -->
                                                                <div class="col-sm-4 card-body">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-check-circle text-c-green mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">
                                                                                <?php echo "$ " . number_format($inversion, 0, '.', ','); ?>
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="Comunera" role="tabpanel"
                                        aria-labelledby="Comunera-tab">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Total Población Impactada Comunera</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div id="bar-chart-Comunera"></div>
                                                    <div class="col-md-12 col-xl-12 specific-card">
                                                        <div class="card flat-card">
                                                            <div class="row-table">
                                                                <!-- Item 1 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-map-pin text-c-blue mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión Departamento</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">$0
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 2 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-users text-c-red mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Porcentaje Ejecución Presupuesto</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">0%
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 3 -->
                                                                <div class="col-sm-4 card-body">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-check-circle text-c-green mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">
                                                                                <?php echo "$ " . number_format($inversion, 0, '.', ','); ?>
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="Velez" role="tabpanel" aria-labelledby="Velez-tab">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Total Población Impactada Velez</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div id="bar-chart-Velez"></div>
                                                    <div class="col-md-12 col-xl-12 specific-card">
                                                        <div class="card flat-card">
                                                            <div class="row-table">
                                                                <!-- Item 1 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-map-pin text-c-blue mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión Departamento</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">$0
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 2 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-users text-c-red mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Porcentaje Ejecución Presupuesto</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">0%
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 3 -->
                                                                <div class="col-sm-4 card-body">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-check-circle text-c-green mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">
                                                                                <?php echo "$ " . number_format($inversion, 0, '.', ','); ?>
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="Metropolitana" role="tabpanel"
                                        aria-labelledby="Metropolitana-tab">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Total Población Impactada Metropolitana</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div id="bar-chart-Metropolitana"></div>
                                                    <div class="col-md-12 col-xl-12 specific-card">
                                                        <div class="card flat-card">
                                                            <div class="row-table">
                                                                <!-- Item 1 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-map-pin text-c-blue mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión Departamento</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">$0
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 2 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-users text-c-red mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Porcentaje Ejecución Presupuesto</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">0%
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 3 -->
                                                                <div class="col-sm-4 card-body">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-check-circle text-c-green mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">
                                                                                <?php echo "$ " . number_format($inversion, 0, '.', ','); ?>
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="Yariguíes" role="tabpanel"
                                        aria-labelledby="Yariguíes-tab">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Total Población Impactada Yariguíes</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div id="bar-chart-Yariguíes"></div>
                                                    <div class="col-md-12 col-xl-12 specific-card">
                                                        <div class="card flat-card">
                                                            <div class="row-table">
                                                                <!-- Item 1 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-map-pin text-c-blue mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión Departamento</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">$0
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 2 -->
                                                                <div class="col-sm-4 card-body br">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-users text-c-red mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Porcentaje Ejecución Presupuesto</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">0%
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Item 3 -->
                                                                <div class="col-sm-4 card-body">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-sm-4 text-center">
                                                                            <i
                                                                                class="icon feather icon-check-circle text-c-green mb-1 d-block"></i>
                                                                        </div>
                                                                        <div class="col-sm-8 text-md-center">
                                                                            <h5>Total Inversión</h5>
                                                                            <h2 class="mb-0" style="font-size: 25px;">
                                                                                <?php echo "$ " . number_format($inversion, 0, '.', ','); ?>
                                                                            </h2>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="contenedor">
                                <div class="contenido">
                                    <div class="card">
                                        <h5 class="card-header">Tabla de Valores de Referencia</h5>
                                        <div class="card-body table-border-style">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Desde</th>
                                                            <th scope="col">Hasta</th>
                                                            <th scope="col">Color</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>0</td>
                                                            <td>0</td>
                                                            <td><span class="color-circle color-white"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>1</td>
                                                            <td>400</td>
                                                            <td><span class="color-circle color-pink"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>401</td>
                                                            <td>-></td>
                                                            <td><span class="color-circle color-green"></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- [ sample-page ] start -->
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Mapa</h5>
                                            <button type="button" class="btn  btn-primary" data-toggle="modal"
                                                data-target="#exampleModalCenter">Geolocalización</button>
                                            <div class="card-header-right">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle btn-icon"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="feather icon-more-horizontal"></i>
                                                    </button>
                                                    <ul
                                                        class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                                        <li class="dropdown-item full-card"><a href="#!"><span><i
                                                                        class="feather icon-maximize"></i>
                                                                    Maximizar</span><span style="display:none"><i
                                                                        class="feather icon-minimize"></i>
                                                                    Restore</span></a></li>
                                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                                        class="feather icon-minus"></i>
                                                                    Colapsar</span><span style="display:none"><i
                                                                        class="feather icon-plus"></i> expand</span></a>
                                                        </li>
                                                        <li class="dropdown-item reload-card"><a href="#!"><i
                                                                    class="feather icon-refresh-cw"></i> Recargar</a>
                                                        </li>
                                                        <li class="dropdown-item close-card"><a href="#!"><i
                                                                    class="feather icon-trash"></i> Remover</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <center>
                                            <div class="cuerpoMapa w-12">
                                                <?php if (!is_null($mapa)) : ?>
                                                    <div class="santander munis">
                                                        <?php echo require_once $mapa; ?>
                                                    </div>
                                                <?php endif ?>
                                            </div>
                                        </center>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="exampleModalCenter" class="modal fade" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalCenterTitle">Geolocalización</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div id="map" style="height: 600px; width: 100%;"></div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Maps JavaScript API -->
            <script async defer
                src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
            </script>
            <script>
                let map;
                let trafficLayer, transitLayer, bicycleLayer;

                function initMap() {
                    if (typeof google !== 'undefined' && google.maps) {
                        // Coordenadas iniciales
                        const initialLocation = {
                            lat: 7.0830880750303935,
                            lng: -73.02794598535458
                        };
                        // Crear el mapa
                        map = new google.maps.Map(document.getElementById("map"), {
                            center: initialLocation,
                            zoom: 12,
                        });
                        // Agregar evento para capturar clic en el mapa
                        map.addListener("click", (event) => {
                            const lat = event.latLng.lat();
                            const lng = event.latLng.lng();
                            // Mostrar las coordenadas en pantalla
                            document.getElementById("lat").innerText = lat.toFixed(6);
                            document.getElementById("lng").innerText = lng.toFixed(6);
                            // Agregar un marcador en el punto seleccionado
                            new google.maps.Marker({
                                position: event.latLng,
                                map: map,
                            });
                        });
                        // Inicializar las capas
                        trafficLayer = new google.maps.TrafficLayer(); // Capa de tráfico
                        transitLayer = new google.maps.TransitLayer(); // Capa de transporte público
                        bicycleLayer = new google.maps.BicyclingLayer(); // Capa de bicicletas
                        // Eventos para los checkboxes
                        document.getElementById("trafficLayerToggle").addEventListener("change", (e) => {
                            if (e.target.checked) {
                                trafficLayer.setMap(map);
                            } else {
                                trafficLayer.setMap(null);
                            }
                        });
                        document.getElementById("transitLayerToggle").addEventListener("change", (e) => {
                            if (e.target.checked) {
                                transitLayer.setMap(map);
                            } else {
                                transitLayer.setMap(null);
                            }
                        });
                        document.getElementById("bicycleLayerToggle").addEventListener("change", (e) => {
                            if (e.target.checked) {
                                bicycleLayer.setMap(map);
                            } else {
                                bicycleLayer.setMap(null);
                            }
                        });
                        document.getElementById("terrainToggle").addEventListener("change", (e) => {
                            if (e.target.checked) {
                                map.setMapTypeId("terrain"); // Cambia el tipo de mapa a terreno
                            } else {
                                map.setMapTypeId("roadmap"); // Cambia el tipo de mapa a carreteras
                            }
                        });
                    } else {
                        console.error('Google Maps API no está disponible.');
                    }
                }
                // Inicializar el mapa cuando se abre el modal
                $('#exampleModalCenter').on('shown.bs.modal', function() {
                    initMap();
                });
            </script>
        </div>

        <!--  Script -->
        <?php if (isset($_GET["route_map"])): ?>
        <?php endif ?>
        <?php include 'admin/include/footer.php'; ?>
        <script>
            document.getElementById("btnAumentar").onclick = function() {
                aumentarTransform();
            };
            document.getElementById("btnReducir").onclick = function() {
                reducirTransform();
            };

            function aumentarTransform() {
                var elemento = document.getElementById("contenidoTransformado");
                var escalaActual = parseFloat(window.getComputedStyle(elemento).getPropertyValue("transform").split(
                    ",")[3]);
                var nuevaEscala = escalaActual + 0.1; // Aumentar la escala en 0.1
                elemento.style.transform = "scale(" + nuevaEscala + ")";
            }

            function reducirTransform() {
                var elemento = document.getElementById("contenidoTransformado");
                var escalaActual = parseFloat(window.getComputedStyle(elemento).getPropertyValue("transform").split(
                    ",")[3]);
                var nuevaEscala = escalaActual - 0.1; // Reducir la escala en 0.1
                if (nuevaEscala >= 0.1) { // Evitar escala negativa
                    elemento.style.transform = "scale(" + nuevaEscala + ")";
                }
            }
        </script>

        <?php include 'admin/include/gerenic_script.php'; ?>

        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>
</body>

</html>