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
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">configuración de puntajes </h5>
<?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Configuracion general</a></li>
                                                <li class="breadcrumb-item"><a href="#!">tabla</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <style>
                                .btn-primary {
                                    color: rgb(255, 255, 255);
                                    background-color: rgb(26, 188, 156);
                                    border-color: rgb(26, 188, 156);
                                }
                            </style>


                            <div class="contenedor">
                                <div class="contenido">
                                    <div class="card">
                                        <h5 class="card-header">configuración de puntajes</h5>
                                        <div class="card-body table-border-style">
                                            <!-- Controles superiores: paginación y búsqueda -->
                                         
                                            <div class="col-8 ">
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <colgroup>
                                                            <col style="width: 72px;">
                                                            <col style="width: 101.438px;">
                                                            <col style="width: 117.125px;">
                                                            <col style="width: 194.656px;">
                                                            <col style="width: 150.719px;">
                                                            <col style="width: 207.562px;">
                                                            <col style="width: 273.062px;">
                                                            <col style="width: 103.438px;">
                                                        </colgroup>
                                                        <thead>
                                                            <tr role="row">
                                                                <th data-dt-column="0" rowspan="1" colspan="1" class="dt-type-numeric dt-orderable-asc dt-orderable-desc dt-ordering-asc" aria-sort="ascending" aria-label="Ver: Activate to invert sorting" tabindex="0"><span class="dt-column-title" role="button">Seguridad multidimencional</span><span class="dt-column-order"></span></th>
                                                                <th data-dt-column="1" rowspan="1" colspan="1" class="dt-type-date dt-orderable-asc dt-orderable-desc" aria-label="Fecha: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">color</span><span class="dt-column-order"></span></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                <a  target="_blank" title="justiciayderecho">
                                                                        <i class="fas fa-eye"></i> justicia y derecho</a>
                                                                </td>
                                                                <td class="dt-type-date" style="background-color: red;"></td>
                                                               
                                                               
                                                            </tr>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                <a  target="_blank" title="saludyproteccionsocial">
                                                                        <i class="fas fa-heart"> </i> salud y proteccion social</a>
                                                                </td>
                                                                <td class="dt-type-date" style="background-color: orange;"></td>
                                                               
                                                               
                                                            </tr>

                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                <a  target="_blank" title="educacion">
                                                                        <i class="fas fa-book"> </i> Educacion</a>
                                                                </td>
                                                                <td class="dt-type-date" style="background-color: blue;"></td>
                                                               
                                                               
                                                            </tr>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                <a  target="_blank" title="trabajo">
                                                                        <i class="fas fa-briefcase"> </i> trabajo</a>
                                                                </td>
                                                                <td class="dt-type-date" style="background-color: orange;"></td>
                                                               
                                                               
                                                            </tr>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                <a  target="_blank" title="viviendaciudadyterritorio">
                                                                        <i class="fas fa-home"> </i> vivienda ciudad y territorio</a>
                                                                </td>
                                                                <td class="dt-type-date" style="background-color: red;"></td>
                                                               
                                                               
                                                            </tr>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                <a  target="_blank" title="deporteyrecreacion">
                                                                        <i class="fas fa-user"> </i> deporte y recreacion</a>
                                                                </td>
                                                                <td class="dt-type-date" style="background-color: red;"></td>
                                                               
                                                               
                                                            </tr>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                <a  target="_blank" title="gobiernoterritorial">
                                                                        <i class="fas fa-link"> </i> gobierno territorial</a>
                                                                </td>
                                                                <td class="dt-type-date" style="background-color: orange;"></td>
                                                               
                                                               
                                                            </tr>
                                                        </tbody>
                                                        <tfoot></tfoot>
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

        </div>
    </div>
    </div>
    </div>
    </div>


    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- prism Js -->
    <script src="assets/js/plugins/prism.js"></script>
</body>

</html>