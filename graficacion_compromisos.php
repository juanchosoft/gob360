<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
//Validación
/* if (!$view) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Compromisos.php';
include './admin/classes/Departamento.php';


//Información de Vistas
$arr = Compromisos::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$modulo = 'Registro Visitas';


// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = "68";
foreach ($arrDep as $val) {
    $optionDep .= "<option value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
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
    <?php
    include './admin/include/navbar.php';
    ?>
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->
    <?php
    include './admin/include/header.php';
    ?>
    <!-- [ Header ] end -->
    <script src="https://cdn.datatables.net/select/2.0.0/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.0/js/select.bootstrap4.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <style>
        .my-custom-table {
            width: 100%;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .my-custom-table th,
        .my-custom-table td {
            white-space: normal !important;
            word-break: break-word;
            vertical-align: top;
        }

        .my-custom-table th:nth-child(3),
        .my-custom-table td:nth-child(3) {
            width: 40%;
        }
    </style>


    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Graficación de Compromisos</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Graficación de Compromisos</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-header">
                            <!-- Título general -->
                            <div class="col-12">
                                <h5 style="text-align: center">
                                    Total de compromisos adquiridos por la Gobernación a través de sus secretarías: TOTAL = 143
                                </h5>
                            </div>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i
                                                        class="feather icon-maximize"></i> maximize</span><span
                                                    style="display:none"><i class="feather icon-minimize"></i>
                                                    Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                        class="feather icon-minus"></i> collapse</span><span
                                                    style="display:none"><i class="feather icon-plus"></i>
                                                    expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i
                                                    class="feather icon-refresh-cw"></i> reload</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i
                                                    class="feather icon-trash"></i> remove</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">


                                <!-- Gráfico 1 -->
                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                    <figure class="highcharts-figure">
                                        <p style="text-align: center" class="highcharts-description">
                                            Secretarias y/o Entidades con compromisos
                                        </p>
                                        <div id="container"></div>
                                    </figure>
                                    <center>
                                        <a><button type="button" class="btn btn-info" data-toggle="modal"
                                                data-target="#exampleModalCenter"> Ver Compromisos</button></a>
                                    </center>
                                </div>

                                <!-- Gráfico 2 -->
                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                    <figure class="highcharts-figure">
                                        <p style="text-align: center" class="highcharts-description">
                                            Secretarias y/o Entidades con compromisos en estado sin cumplir
                                        </p>
                                        <div id="container1"></div>
                                    </figure>
                                    <center>
                                        <a><button type="button" class="btn btn-info" data-toggle="modal"
                                                data-target="#exampleModalCenter1"> Ver Compromisos</button></a>
                                    </center>
                                </div>

                                <!-- Gráfico 3 -->
                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 mt-4">
                                    <figure class="highcharts-figure">
                                        <p style="text-align: center" class="highcharts-description">
                                            Secretarias y/o Entidades con compromisos en estado En Trámite
                                        </p>
                                        <div id="container2"></div>
                                    </figure>
                                    <center>
                                        <a><button type="button" class="btn btn-info" data-toggle="modal"
                                                data-target="#exampleModalCenter2"> Ver Compromisos</button></a>
                                    </center>
                                </div>

                                <!-- Gráfico 4 -->
                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 mt-4">
                                    <figure class="highcharts-figure">
                                        <p style="text-align: center" class="highcharts-description">
                                            Secretarias y/o Entidades con compromisos en estado Cumplido
                                        </p>
                                        <div id="container3"></div>
                                    </figure>
                                    <center>
                                        <a><button type="button" class="btn btn-info mb-4" data-toggle="modal"
                                                data-target="#exampleModalCenter3"> Ver Compromisos</button></a>
                                    </center>
                                </div>
                            </div>
                        </div>


                        <!-- Modal -->
                        <!-- 
                    // ===================Información modal de total compromisos================= -->
                        <?php
                        $arr = Compromisos::getAll(null);
                        $isvalid = $arr['output']['valid'];
                        $arr = $arr['output']['response'];
                        ?>
                        <div class="modal fade bd-example-modal-lg" id="exampleModalCenter" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Cantidad de visitas a municipios
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <style>
                                            table {
                                                width: 100%;
                                                table-layout: fixed;
                                            }

                                            th,
                                            td {
                                                vertical-align: top;
                                                padding: 0.75rem;
                                                white-space: normal;
                                                word-wrap: break-word;
                                                word-break: break-word;
                                            }

                                            td:nth-child(3),
                                            th:nth-child(3) {
                                                width: 40%;
                                                /* puedes ajustar este ancho */
                                            }
                                        </style>
                                        <div style="overflow-x: auto;">
                                            <table class="table table-bordered my-custom-table">

                                                <thead>
                                                    <tr>
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">Municipio</th>
                                                        <th scope="col">Compromiso</th>
                                                        <th scope="col">Secretaria</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $c = count($arr);
                                                    if ($isvalid) {
                                                        for ($i = 0; $i < $c; $i++) { ?>
                                                            <tr>
                                                                <td><?php echo $arr[$i]['date']; ?></td>
                                                                <td><?php echo $arr[$i]['municipio']; ?></td>
                                                                <td><?php echo $arr[$i]['compromisos']; ?></td>
                                                                <td><?php echo $arr[$i]['secretaria']; ?></td>
                                                            </tr>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>




                        <!-- modal total de compromisos sin cumplir -->

                        <!-- // ===================Información modal de total compromisos================= -->
                        <?php
                        $arr = Compromisos::getAllsinc(null);
                        $isvalid = $arr['output']['valid'];
                        $arr = $arr['output']['response'];
                        ?>
                        <div class="modal fade bd-example-modal-lg" id="exampleModalCenter1" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Detalle Compromisos sin Cumplir
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <style>
                                            table {
                                                width: 100%;
                                                table-layout: fixed;
                                            }

                                            th,
                                            td {
                                                vertical-align: top;
                                                padding: 0.75rem;
                                                white-space: normal;
                                                word-wrap: break-word;
                                                word-break: break-word;
                                            }

                                            td:nth-child(3),
                                            th:nth-child(3) {
                                                width: 40%;
                                                /* puedes ajustar este ancho */
                                            }
                                        </style>
                                        <div style="overflow-x: auto;">
                                            <table class="table table-bordered my-custom-table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">Municipio</th>
                                                        <th scope="col">Compromiso</th>
                                                        <th scope="col">Secretaria</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $c = count($arr);
                                                    if ($isvalid) {
                                                        for ($i = 0; $i < $c; $i++) { ?>
                                                            <tr>

                                                                <td> <?php echo $arr[$i]['date']; ?></td>
                                                                <td> <?php echo $arr[$i]['municipio']; ?></td>
                                                                <td> <?php echo $arr[$i]['compromisos']; ?></td>
                                                                <td> <?php echo $arr[$i]['secretaria']; ?></td>

                                                            </tr>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- modal visitas realizadas fin -->

                        <!-- // ===================Información modal de total compromisos en tramite================= -->
                        <?php
                        $arr = Compromisos::getAlltram(null);
                        $isvalid = $arr['output']['valid'];
                        $arr = $arr['output']['response'];
                        ?>
                        <div class="modal fade bd-example-modal-lg" id="exampleModalCenter2" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Detalle Compromisos en Trámite
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <style>
                                            table {
                                                width: 100%;
                                                table-layout: fixed;
                                            }

                                            th,
                                            td {
                                                vertical-align: top;
                                                padding: 0.75rem;
                                                white-space: normal;
                                                word-wrap: break-word;
                                                word-break: break-word;
                                            }

                                            td:nth-child(3),
                                            th:nth-child(3) {
                                                width: 40%;
                                                /* puedes ajustar este ancho */
                                            }
                                        </style>
                                        <div style="overflow-x: auto;">
                                            <table class="table table-bordered my-custom-table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">Municipio</th>
                                                        <th scope="col">Compromiso</th>
                                                        <th scope="col">Secretaria</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $c = count($arr);
                                                    if ($isvalid) {
                                                        for ($i = 0; $i < $c; $i++) { ?>
                                                            <tr>

                                                                <td> <?php echo $arr[$i]['date']; ?></td>
                                                                <td> <?php echo $arr[$i]['municipio']; ?></td>
                                                                <td> <?php echo $arr[$i]['compromisos']; ?></td>
                                                                <td> <?php echo $arr[$i]['secretaria']; ?></td>

                                                            </tr>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- modal visitas realizadas fin -->

                        <!-- // ===================Información modal de total compromisos Cumplidos================= -->
                        <?php
                        $arr = Compromisos::getAllcum(null);
                        $isvalid = $arr['output']['valid'];
                        $arr = $arr['output']['response'];
                        ?>
                        <div class="modal fade bd-example-modal-lg" id="exampleModalCenter3" tabindex="-1" role="dialog"
                            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Detalle Compromisos Cumplidos
                                        </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <style>
                                            table {
                                                width: 100%;
                                                table-layout: fixed;
                                            }

                                            th,
                                            td {
                                                vertical-align: top;
                                                padding: 0.75rem;
                                                white-space: normal;
                                                word-wrap: break-word;
                                                word-break: break-word;
                                            }

                                            td:nth-child(3),
                                            th:nth-child(3) {
                                                width: 40%;
                                                /* puedes ajustar este ancho */
                                            }
                                        </style>
                                        <div style="overflow-x: auto;">
                                            <table class="table table-bordered my-custom-table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">Municipio</th>
                                                        <th scope="col">Compromiso</th>
                                                        <th scope="col">Secretaria</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $c = count($arr);
                                                    if ($isvalid) {
                                                        for ($i = 0; $i < $c; $i++) { ?>
                                                            <tr>

                                                                <td> <?php echo $arr[$i]['date']; ?></td>
                                                                <td> <?php echo $arr[$i]['municipio']; ?></td>
                                                                <td> <?php echo $arr[$i]['compromisos']; ?></td>
                                                                <td> <?php echo $arr[$i]['secretaria']; ?></td>

                                                            </tr>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- modal visitas realizadas fin -->

                        <!-- ============================================================== -->
                        <!-- footer -->
                        <!-- ============================================================== -->
                        <?php include 'admin/include/gerenic_script.php'; ?>
                        <script type="text/javascript" src="admin/js/departamento.js"></script>
                        <script type="text/javascript" src="admin/js/compromisos.js"></script>
                        <!-- Incluir la biblioteca de ApexCharts -->
                        <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>

                        <!-- Contenedor del gráfico -->
                        <!-- <div id="containerProvincias"></div> -->

                        <script>
                            // Función para crear el gráfico de ApexCharts
                            function createChart(container, title, seriesData) {
                                const options = {
                                    chart: {
                                        height: 350,
                                        type: 'bar',
                                    },
                                    plotOptions: {
                                        bar: {
                                            horizontal: false,
                                            columnWidth: '55%',
                                            endingShape: 'rounded',
                                        },
                                    },
                                    dataLabels: {
                                        enabled: true,
                                        formatter: (val) => val.toFixed(1), // Formato numérico legible
                                    },
                                    colors: ['#0e9e4a', '#1abc9c', '#e74c3c', '#3498db', '#9b59b6'],
                                    stroke: {
                                        show: true,
                                        width: 2,
                                        colors: ['transparent'],
                                    },
                                    series: [{
                                        name: title,
                                        data: seriesData.map(item => item.y),
                                    }],
                                    xaxis: {
                                        categories: seriesData.map(item => item.name),
                                    },
                                    yaxis: {
                                        title: {
                                            text: 'Total de Compromisos',
                                        },
                                    },
                                    fill: {
                                        opacity: 1,
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: (val) => val.toLocaleString(), // Formato numérico legible
                                        },
                                    },
                                };
                                const chart = new ApexCharts(document.querySelector(`#${container}`), options);
                                chart.render();
                            }
                            // Simulación de obtención de datos, podrías reemplazar esto con fetch() si obtienes datos de un API
                            function fetchData() {
                                // Datos simulados para el ejemplo
                                const seriesData = [{
                                        name: 'Ambiental',
                                        y: 0
                                    },
                                    {
                                        name: 'CAS',
                                        y: 0
                                    },
                                    {
                                        name: 'Competividad',
                                        y: 0
                                    },
                                    {
                                        name: 'Cultura y Turismo',
                                        y: 0
                                    },
                                    {
                                        name: 'Desarrollo Social',
                                        y: 0
                                    },
                                    {
                                        name: 'Educación',
                                        y: 0
                                    },
                                    {
                                        name: 'Esant',
                                        y: 0
                                    },
                                    {
                                        name: 'Gestión del Riesgo',
                                        y: 0
                                    },
                                    {
                                        name: 'InderSantader',
                                        y: 0
                                    },
                                    {
                                        name: 'Infraestructura',
                                        y: 0
                                    },
                                    {
                                        name: 'Interior',
                                        y: 0
                                    },
                                    {
                                        name: 'Mujer y Genero',
                                        y: 0
                                    },
                                    {
                                        name: 'Oficina Juridica',
                                        y: 0
                                    },
                                    {
                                        name: 'Privada',
                                        y: 0
                                    },
                                    {
                                        name: 'Salud',
                                        y: 0
                                    },
                                ];
                                return seriesData;
                            }
                            // Función para obtener y mostrar los datos del gráfico
                            function getCompromisosCumplidosPorSecretaria() {
                                const data = fetchData();
                                createChart("container3", "Compromisos pactados por Secretaria en estado Cumplido", data);
                            }
                            // Llama a la función al cargar la página

                            document.addEventListener('DOMContentLoaded', function() {
                                getCompromisosCumplidosPorSecretaria();
                            });
                        </script>

                        <script>
                            // Función para crear el gráfico de ApexCharts
                            function createChart(container, title, seriesData) {
                                const options = {
                                    chart: {
                                        height: 350,
                                        type: 'bar',
                                    },
                                    plotOptions: {
                                        bar: {
                                            horizontal: false,
                                            columnWidth: '55%',
                                            endingShape: 'rounded',
                                        },
                                    },
                                    dataLabels: {
                                        enabled: false,
                                    },
                                    colors: ['#0e9e4a', '#1abc9c', '#e74c3c'],
                                    stroke: {
                                        show: true,
                                        width: 2,
                                        colors: ['transparent'],
                                    },
                                    series: [{
                                        name: title,
                                        data: seriesData.map(item => item.y),
                                    }],
                                    xaxis: {
                                        categories: seriesData.map(item => item.name),
                                    },
                                    yaxis: {
                                        title: {
                                            text: 'Total de visitas',
                                        },
                                    },
                                    fill: {
                                        opacity: 1,
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: (val) => val.toLocaleString(), // Formato numérico legible
                                        },
                                    },
                                };
                                const chart = new ApexCharts(document.querySelector(`#${container}`), options);
                                chart.render();
                            }
                            // Simulación de obtención de datos, podrías reemplazar esto con fetch() si obtienes datos de un API
                            function fetchData() {
                                // Datos simulados para el ejemplo
                                const seriesData = [{
                                        name: 'Ambiental',
                                        y: 5
                                    },
                                    {
                                        name: 'CAS',
                                        y: 3
                                    },
                                    {
                                        name: 'Competividad',
                                        y: 8
                                    },
                                    {
                                        name: 'Cultura y Turismo',
                                        y: 2
                                    },
                                    {
                                        name: 'Desarrollo Social',
                                        y: 7
                                    },
                                    {
                                        name: 'Educación',
                                        y: 4
                                    },
                                    {
                                        name: 'Esant',
                                        y: 6
                                    },
                                    {
                                        name: 'Gestión del Riesgo',
                                        y: 1
                                    },
                                    {
                                        name: 'InderSantader',
                                        y: 9
                                    },
                                    {
                                        name: 'Infraestructura',
                                        y: 2
                                    },
                                    {
                                        name: 'Interior',
                                        y: 10
                                    },
                                    {
                                        name: 'Mujer y Genero',
                                        y: 3
                                    },
                                    {
                                        name: 'Oficina Juridica',
                                        y: 4
                                    },
                                    {
                                        name: 'Privada',
                                        y: 5
                                    },
                                    {
                                        name: 'Salud',
                                        y: 8
                                    },
                                ];
                                return seriesData;
                            }
                            // Función para obtener y mostrar los datos del gráfico
                            function getTotalVisitasPorProvincia() {
                                const data = fetchData();
                                createChart("containerProvincias", "Visitas realizadas a Provincias", data);
                            }
                            // Llama a la función al cargar la página
                            document.addEventListener('DOMContentLoaded', function() {
                                getTotalVisitasPorProvincia();
                            });
                        </script>

                        <script>
                            // Función para crear el gráfico de ApexCharts
                            function createChart(container, title, seriesData) {
                                const options = {
                                    chart: {
                                        height: 350,
                                        type: 'bar',
                                    },
                                    plotOptions: {
                                        bar: {
                                            horizontal: false,
                                            columnWidth: '55%',
                                            endingShape: 'rounded',
                                        },
                                    },
                                    dataLabels: {
                                        enabled: true,
                                        formatter: (val) => val.toFixed(1), // Formato numérico legible
                                    },
                                    colors: ['#e74c3c', '#f39c12', '#8e44ad', '#3498db', '#1abc9c'],
                                    stroke: {
                                        show: true,
                                        width: 2,
                                        colors: ['transparent'],
                                    },
                                    series: [{
                                        name: title,
                                        data: seriesData.map(item => item.y),
                                    }],
                                    xaxis: {
                                        categories: seriesData.map(item => item.name),
                                    },
                                    yaxis: {
                                        title: {
                                            text: 'Total de Compromisos',
                                        },
                                    },
                                    fill: {
                                        opacity: 1,
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: (val) => val.toLocaleString(), // Formato numérico legible
                                        },
                                    },
                                };
                                const chart = new ApexCharts(document.querySelector(`#${container}`), options);
                                chart.render();
                            }
                            // Simulación de obtención de datos, puedes reemplazar esto con fetch() si obtienes datos de un API
                            function fetchData() {
                                // Datos simulados para el ejemplo
                                const seriesData = [{
                                        name: 'Ambiental',
                                        y: 1
                                    },
                                    {
                                        name: 'CAS',
                                        y: 2
                                    },
                                    {
                                        name: 'Competividad',
                                        y: 3
                                    },
                                    {
                                        name: 'Cultura y Turismo',
                                        y: 11
                                    },
                                    {
                                        name: 'Desarrollo Social',
                                        y: 9
                                    },
                                    {
                                        name: 'Educación',
                                        y: 26
                                    },
                                    {
                                        name: 'Esant',
                                        y: 12
                                    },
                                    {
                                        name: 'Gestión del Riesgo',
                                        y: 2
                                    },
                                    {
                                        name: 'InderSantader',
                                        y: 26
                                    },
                                    {
                                        name: 'Infraestructura',
                                        y: 34
                                    },
                                    {
                                        name: 'Interior',
                                        y: 4
                                    },
                                    {
                                        name: 'Mujer y Genero',
                                        y: 9
                                    },
                                    {
                                        name: 'Oficina Juridica',
                                        y: 1
                                    },
                                    {
                                        name: 'Privada',
                                        y: 1
                                    },
                                    {
                                        name: 'Salud',
                                        y: 6
                                    },
                                ];
                                return seriesData;
                            }
                            // Función para obtener y mostrar los datos del gráfico
                            function getCompromisosSinCumplirPorSecretaria() {
                                const data = fetchData();
                                createChart("container1", "Compromisos pactados por Secretaria en estado Sin Cumplir",
                                    data);
                            }
                            // Llama a la función al cargar la página
                            document.addEventListener('DOMContentLoaded', function() {
                                getCompromisosSinCumplirPorSecretaria();
                            });
                        </script>

                        <script>
                            // Función para crear el gráfico de ApexCharts
                            function createChart(container, title, seriesData) {
                                const options = {
                                    chart: {
                                        height: 350,
                                        type: 'bar',
                                    },
                                    plotOptions: {
                                        bar: {
                                            horizontal: false,
                                            columnWidth: '55%',
                                            endingShape: 'rounded',
                                        },
                                    },
                                    dataLabels: {
                                        enabled: true,
                                        formatter: (val) => val.toFixed(1), // Formato numérico legible
                                    },
                                    colors: ['#0e9e4a', '#1abc9c', '#e74c3c', '#3498db', '#9b59b6'],
                                    stroke: {
                                        show: true,
                                        width: 2,
                                        colors: ['transparent'],
                                    },
                                    series: [{
                                        name: title,
                                        data: seriesData.map(item => item.y),
                                    }],
                                    xaxis: {
                                        categories: seriesData.map(item => item.name),
                                    },
                                    yaxis: {
                                        title: {
                                            text: 'Total de Compromisos',
                                        },
                                    },
                                    fill: {
                                        opacity: 1,
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: (val) => val.toLocaleString(), // Formato numérico legible
                                        },
                                    },
                                };
                                const chart = new ApexCharts(document.querySelector(`#${container}`), options);
                                chart.render();
                            }
                            // Simulación de obtención de datos, podrías reemplazar esto con fetch() si obtienes datos de un API
                            function fetchData() {
                                // Datos simulados para el ejemplo
                                const seriesData = [{
                                        name: 'Ambiental',
                                        y: 1
                                    },
                                    {
                                        name: 'CAS',
                                        y: 2
                                    },
                                    {
                                        name: 'Competividad',
                                        y: 3
                                    },
                                    {
                                        name: 'Cultura y Turismo',
                                        y: 11
                                    },
                                    {
                                        name: 'Desarrollo Social',
                                        y: 9
                                    },
                                    {
                                        name: 'Educación',
                                        y: 26
                                    },
                                    {
                                        name: 'Esant',
                                        y: 12
                                    },
                                    {
                                        name: 'Gestión del Riesgo',
                                        y: 2
                                    },
                                    {
                                        name: 'InderSantader',
                                        y: 26
                                    },
                                    {
                                        name: 'Infraestructura',
                                        y: 34
                                    },
                                    {
                                        name: 'Interior',
                                        y: 4
                                    },
                                    {
                                        name: 'Mujer y Genero',
                                        y: 9
                                    },
                                    {
                                        name: 'Oficina Juridica',
                                        y: 1
                                    },
                                    {
                                        name: 'Privada',
                                        y: 1
                                    },
                                    {
                                        name: 'Salud',
                                        y: 6
                                    },
                                ];
                                return seriesData;
                            }
                            // Función para obtener y mostrar los datos del gráfico
                            function getTotalCompromisosPorSecretaria() {
                                const data = fetchData();
                                createChart("container", "Total de Compromisos pactados por Secretaria", data);
                            }
                            // Llama a la función al cargar la página
                            document.addEventListener('DOMContentLoaded', function() {
                                getTotalCompromisosPorSecretaria();
                            });
                        </script>

                    </div>
                </div>
            </div>
            <!-- [ sample-page ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- Required Js -->
    <script src="../assets/vendor/jquery/jquery-3.3.1.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="../assets/vendor/slimscroll/jquery.slimscroll.js"></script>
    <script src="../assets/libs/js/main-js.js"></script>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

</body>

</html>