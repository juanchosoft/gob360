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
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
include './admin/db/coloresg.php';
include './admin/classes/Maing.php';
include './admin/classes/Detalle.php';
include './admin/classes/Cuenta.php';
include './admin/classes/Cuentapro.php';
include './admin/classes/Secreinversion.php';
include './admin/classes/Munnovisitados.php';
include './admin/classes/GestoraSocialAspas.php';

// Obtener permisos
$permissions = PagePermissions::crudForCurrentPage();

// Validación de permiso de visualización
/* if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
} */


//informacion del mail
$arr = Maing::getDataMain(null);
$isvalid = $arr['output']['valid'];
$visitas = $arr['output']['visitas'];
$impactada = $arr['output']['impactada'];
$inversion = $arr['output']['inversion'];
$modulo = 'Primera Dama';

$departamento = new Departamento();
$santander = $departamento->getAll(["id" => Util::getIdentificadorDepartamentoPrincipal()]);
$santander = $santander["output"]["response"]["0"];
$code = null;
$mapa = null;

if (isset($_GET['depto_id']) && in_array($_GET['depto_id'], [1, 12, 21])) {
    switch ($_GET['depto_id']) {

        case Util::getIdentificadorDepartamentoPrincipal():
            $code = $santander["codigo_departamento"];
            $mapa = "admin/mapa-santander/mapa_gestora_social_aspas.php";
            break;
    }
}


if (!is_null($code)) {
    $arr = Ciudad::getAll(array('codigo_departamento' => $code));
    $finalMunicipios = $arr['output']['response'];
    $arrApoyoDep = Ciudad::getApoyoByCodigoDepartamento(array('codigo_departamento' => $code));
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
                                                <h5 class="m-b-10">Dashboard Gestiòn Social 2</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Gestiòn Social 2 </a></li>
                                                <li class="breadcrumb-item"><a href="#!">Actividades</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ breadcrumb ] end -->
                            <!-- [ Main Content ] start -->


                            <div class="row">
                                <div class="col-lg-6 col-xl-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Mapa</h5>
                                            <div class="card-header-right">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="feather icon-more-horizontal"></i>
                                                    </button>
                                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                                        <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                                        <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                                                        <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Remover</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <center>
                                            <div class="cuerpoMapa w-12">
                                                <?php if (!is_null($mapa)) : ?>
                                                    <div class="santander munis">
                                                        <?php echo require_once "admin/mapa-santander/mapa_gestora_social_aspas.php"; ?>
                                                    </div>
                                                <?php endif ?>
                                            </div>
                                        </center>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-6 mb-4">
                                    <div class="col-12 mb-4">
                                        <div class="card shadow-sm border-0">
                                            <div class="card-body">
                                                <div class="row text-center">
                                                    <!-- Item 1 -->
                                                    <div class="col-sm-4 card-body br">
                                                        <div class="row align-items-center">
                                                            <div class="col-sm-4 text-center">
                                                                <i class="icon feather icon-map-pin text-c-blue mb-1 d-block"></i>
                                                            </div>
                                                            <div class="col-sm-8 text-md-center">
                                                                <h5>Total Visitas Departamento</h5>
                                                                <h2 class="mb-0"> <?php echo $visitas; ?></h2>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Item 2 -->
                                                    <div class="col-sm-4 card-body br">
                                                        <div class="row align-items-center">
                                                            <div class="col-sm-4 text-center">
                                                                <i class="icon feather icon-users text-c-red mb-1 d-block"></i>
                                                            </div>
                                                            <div class="col-sm-8 text-md-center">
                                                                <h5>Total Población Impactada</h5>
                                                                <h2 class="mb-0"><?php echo $impactada; ?></h2>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Item 3 -->
                                                    <div class="col-sm-4 card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-sm-4 text-center">
                                                                <i class="icon feather icon-check-circle text-c-green mb-1 d-block"></i>
                                                            </div>
                                                            <div class="col-sm-8 text-md-center">
                                                                <h5>Total Inversión</h5>
                                                                <h2 class="mb-0"><?php echo "$ " . number_format($inversion, 0, '.', ','); ?></h2>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card ">
                                        <div class="card-body">
                                            <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active text-uppercase" id="Soto_Norte-tab" data-toggle="tab" href="#Soto_Norte" role="tab" aria-controls="Soto_Norte" aria-selected="true">Soto Norte</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link text-uppercase" id="Guanenta-tab" data-toggle="tab" href="#Guanenta" role="tab" aria-controls="Guanenta" aria-selected="false">Guanentá</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link text-uppercase" id="Garcia_Rovira-tab" data-toggle="tab" href="#Garcia_Rovira" role="tab" aria-controls="Garcia_Rovira" aria-selected="false">García Rovira</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link text-uppercase" id="Comunera-tab" data-toggle="tab" href="#Comunera" role="tab" aria-controls="Comunera" aria-selected="false">Comunera</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link text-uppercase" id="Velez-tab" data-toggle="tab" href="#Velez" role="tab" aria-controls="Velez" aria-selected="false">Velez</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link text-uppercase" id="Metropolitana-tab" data-toggle="tab" href="#Metropolitana" role="tab" aria-controls="Metropolitana" aria-selected="false">Metropolitana</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link text-uppercase" id="Yariguíes-tab" data-toggle="tab" href="#Yariguíes" role="tab" aria-controls="Yariguíes" aria-selected="false">Yariguíes</a>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="myTabContent">
                                                <div class="tab-pane fade show active" id="Soto_Norte" role="tabpanel" aria-labelledby="Soto_Norte-tab">
                                                    <div class="col-md-12">

                                                        <div class="card-header">
                                                            <h5>Total Población Impactada Soto Norte</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div id="bar-chart-Soto_Norte"></div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="Guanenta" role="tabpanel" aria-labelledby="Guanenta-tab">
                                                    <div class="col-md-12">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Total Población Impactada Guanentá</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div id="bar-chart-Guanenta"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="Garcia_Rovira" role="tabpanel" aria-labelledby="Garcia_Rovira-tab">
                                                    <div class="col-md-12">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Total Población Impactada García Rovira</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div id="bar-chart-Garcia_Rovira"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="Comunera" role="tabpanel" aria-labelledby="Comunera-tab">
                                                    <div class="col-md-12">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Total Población Impactada Comunera</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div id="bar-chart-Comunera"></div>
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
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="Metropolitana" role="tabpanel" aria-labelledby="Metropolitana-tab">
                                                    <div class="col-md-12">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Total Población Impactada Metropolitana</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div id="bar-chart-Metropolitana"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="Yariguíes" role="tabpanel" aria-labelledby="Yariguíes-tab">
                                                    <div class="col-md-12">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Total Población Impactada Yariguíes</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div id="bar-chart-Yariguíes"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="contenedor" style="margin-top: 0.5rem;">

                                        <div class="contenido">
                                            <div class="card">
                                                <h5 class="card-header">Tabla de Valores de Referencia</h5>
                                                <div class="card-body table-border-style">
                                                    <div class="table-responsive">
                                                        <table class="table tabla-estilizada">
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
                                                                    <td>
                                                                        <div class="color-circle" style="background-color: white;"></div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>50</td>
                                                                    <td>
                                                                        <div class="color-circle" style="background-color: #f7c5ae;"></div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>51</td>
                                                                    <td>100</td>
                                                                    <td>
                                                                        <div class="color-circle" style="background-color: #ffa5ae;"></div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>100</td>
                                                                    <td>----</td>
                                                                    <td>
                                                                        <div class="color-circle" style="background-color: #ea9abd;"></div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
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

                <!-- prism Js -->
                <script src="assets/js/plugins/prism.js"></script>
                <script src="assets/js/plugins/apexcharts.min.js"></script>

                <script src="admin/js/gestora_social_aspas.js"></script>
                <style>
                    .santander.munis path:hover,
                    .santander.munis polygon:hover {
                        transform: none !important;
                        filter: none !important;
                        stroke: none !important;
                        fill: inherit !important;
                        pointer-events: auto !important;
                    }
                </style>



</body>

</html>