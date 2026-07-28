<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';

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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Departamento de Santander</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Departamento de Santander</a></li>
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
                            <h5>Accion Unificada por Pilares</h5>
                            <div class="col-sm-6">
                        <select class="custom-select" id="inputGroupSelect03">
                                        <option selected>Seleccione</option>
                                        <option value="Justicia y Derecho">Justicia y Derecho</option>
                                        <option value="Salud y Proteccion Social">Salud y Proteccion Social</option>
                                        <option value="Educación">Educación</option>
                                        <option value="Trabajo">Trabajo</option>
                                        <option value="Vivienda, Ciudad y Territorio">Vivienda, Ciudad y Territorio</option>
                                        <option value="Deporte y Recración">Deporte y Recración</option>
                                        <option value="Gobierno Territorial">Gobierno Territorial</option>
                                        <option value="Agricultura y Desarrollo Rural">Agricultura y Desarrollo Rural</option>
                                        <option value="Minas y Energia">Minas y Energia</option>
                                        <option value="Ambiente y Desarrollo Sostenible">Ambiente y Desarrollo Sostenible</option>
                                        <option value="Informacion y Estadistica">Informacion y Estadistica</option>
                                        <option value="Cultura">Cultura</option>
                                        <option value="Tecnologás de la Información">Tecnologás de la Información</option>
                                        <option value="Comercio, Industria y Turismo">Comercio, Industria y Turismo</option>                                      
                                    </select>
                                    </div>     
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i
                                                        class="feather icon-maximize"></i> Maximizar</span><span
                                                    style="display:none"><i class="feather icon-minimize"></i>
                                                    Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                        class="feather icon-minus"></i> Colapsar</span><span
                                                    style="display:none"><i class="feather icon-plus"></i>
                                                    expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i
                                                    class="feather icon-refresh-cw"></i> Recargar</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i
                                                    class="feather icon-trash"></i> Remover</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                       
                        <center>
                            <div class="card-body">
                                <img src="assets/images/mapas/mapamuestra.jpg" alt="">
                            </div>
                        </center>
                         </div>
                         
                    </div>
                </div>
                <!-- [ sample-page ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <!-- Warning Section Ends -->

    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

</body>

</html>