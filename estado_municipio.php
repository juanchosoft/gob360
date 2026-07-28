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
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Estados municipios</h5>
<?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">mapa</a></li>
                                                <li class="breadcrumb-item"><a href="#!">estado municipio</a></li>
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
                                        <h5 class="card-header">Visitas realizadas</h5>
                                        <div class="card-body table-border-style">
                                            <!-- Controles superiores: paginación y búsqueda -->
                                            <div class="row justify-content-between mb-3">
                                                <!-- Selector de cantidad de entradas por página -->
                                                <div class="col-md-8">
                                                    <label for="entries-select" class="form-label">Mostrar:</label>
                                                    <select id="entries-select" class="form-select form-select-sm">
                                                        <option value="10">10</option>
                                                        <option value="25">25</option>
                                                        <option value="50">50</option>
                                                        <option value="100">100</option>
                                                    </select>
                                                    <span>entradas por página</span>
                                                </div>
                                                <!-- Barra de búsqueda -->
                                                <div class="col-md-2">
                                                    <label for="search-input" class="form-label">Buscar:</label>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Escribe aquí...">
                                                </div>
                                            </div>
                                            <div class="col-12 ">
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
                                                                <th data-dt-column="0" rowspan="1" colspan="1" class="dt-type-numeric dt-orderable-asc dt-orderable-desc dt-ordering-asc" aria-sort="ascending" aria-label="Ver: Activate to invert sorting" tabindex="0"><span class="dt-column-title" role="button">imagen</span><span class="dt-column-order"></span></th>
                                                                <th data-dt-column="1" rowspan="1" colspan="1" class="dt-type-date dt-orderable-asc dt-orderable-desc" aria-label="Fecha: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Fecha</span><span class="dt-column-order"></span></th>
                                                                <th data-dt-column="2" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc" aria-label="Provincia: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Provincia</span><span class="dt-column-order"></span></th>
                                                                <th data-dt-column="3" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc" aria-label="Municipio: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">responsable</span><span class="dt-column-order"></span></th>
                                                                <th data-dt-column="4" rowspan="1" colspan="1" class="dt-orderable-asc dt-orderable-desc" aria-label="Motivo Visita: Activate to sort" tabindex="0"><span class="dt-column-title" role="button">Motivo Visita</span><span class="dt-column-order"></span></th>
                                                               
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                    <a href="reporte_visitag.php?reporte=2" target="_blank" title="reloj">
                                                                        <i class="fas fa-clock"></i> </a>
                                                                </td>
                                                                <td class="dt-type-date">2024-02-01</td>
                                                                <td>Yariguíes</td>
                                                                <td>Gobernación de Santander</td>
                                                                <td>Revisión del estado actual del Hospital Regional del Magdalena Medio, acciones para garantizar que el hospital sea de tercer nivel. Consejo de Seguridad: evaluación de las estrategias de seguridad, panorama actual de orden público.</td>
                                                               
                                                            </tr>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                    <a href="reporte_visitag.php?reporte=2" target="_blank" title="reloj">
                                                                        <i class="fas fa-clock"></i> </a>
                                                                </td>
                                                                <td class="dt-type-date">2024-02-01</td>
                                                                <td>Yariguíes</td>
                                                                <td>Gobernación de Santander</td>
                                                                <td>Revisión del estado actual del Hospital Regional del Magdalena Medio, acciones para garantizar que el hospital sea de tercer nivel. Consejo de Seguridad: evaluación de las estrategias de seguridad, panorama actual de orden público.</td>
                                                               
                                                            </tr>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                    <a href="reporte_visitag.php?reporte=2" target="_blank" title="reloj">
                                                                        <i class="fas fa-clock"></i> </a>
                                                                </td>
                                                                <td class="dt-type-date">2024-02-01</td>
                                                                <td>Comunera</td>
                                                                <td>Gobernación de Santander</td>
                                                                <td>	Visita al Hospital Nuestra Señora de Guadalupe, evaluamos las necesidades del centro de salud.</td>
                                                               
                                                            </tr>
                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                    <a href="reporte_visitag.php?reporte=2" target="_blank" title="reloj">
                                                                        <i class="fas fa-clock"></i> </a>
                                                                </td>
                                                                <td class="dt-type-date">2024-02-01</td>
                                                                <td>García Rovira</td>
                                                                <td>Gobernación de Santander</td>
                                                                <td>Participación Ferias y Fiestas de San Jerónimo 2024 y feria empresarial. 06/01/2024 Identifiación de las necesidades de los usuarios, verifiación del estado actual de las obras en desarrollo. Viabilidades para que el hospital se convierta en uno de tercer nivel.</td>
                                                               
                                                            </tr>

                                                            <tr>
                                                                <td class="dt-type-numeric sorting_1">
                                                                    <a href="reporte_visitag.php?reporte=2" target="_blank" title="reloj">
                                                                        <i class="fas fa-clock"></i> </a>
                                                                </td>
                                                                <td class="dt-type-date">2024-02-01</td>
                                                                <td>Vélez</td>
                                                                <td>Gobernación de Santander</td>
                                                                <td>Nos comprometimos a mejorar la vía que comunica con Santa Helena del Opón, proyecto que iniciará a partir del 22 enero. 8/01/2024 - Posesión simbólica como Gobernador del departamento.</td>
                                                               
                                                            </tr>
                                                            
                                                        </tbody>
                                                        <tfoot></tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row justify-content-between">
                                            <div class="col-md-auto mr-auto ">
                                                <div class="dt-info" aria-live="polite" id="example_info" role="status">Mostrando 1 to 10 of 116 entries</div>
                                            </div>
                                            <div class="col-md-auto ml-auto ">
                                                <div class="dt-paging paging_full_numbers">
                                                    <ul class="pagination">
                                                        <li class="dt-paging-button page-item disabled"><a class="page-link first" aria-controls="example" aria-disabled="true" aria-label="First" data-dt-idx="first" tabindex="-1">«</a></li>
                                                        <li class="dt-paging-button page-item disabled"><a class="page-link previous" aria-controls="example" aria-disabled="true" aria-label="Previous" data-dt-idx="previous" tabindex="-1">‹</a></li>
                                                        <li class="dt-paging-button page-item active"><a href="#" class="page-link" aria-controls="example" aria-current="page" data-dt-idx="0" tabindex="0">1</a></li>
                                                        <li class="dt-paging-button page-item"><a href="#" class="page-link" aria-controls="example" data-dt-idx="1" tabindex="0">2</a></li>
                                                        <li class="dt-paging-button page-item"><a href="#" class="page-link" aria-controls="example" data-dt-idx="2" tabindex="0">3</a></li>
                                                        <li class="dt-paging-button page-item"><a href="#" class="page-link" aria-controls="example" data-dt-idx="3" tabindex="0">4</a></li>
                                                        <li class="dt-paging-button page-item"><a href="#" class="page-link" aria-controls="example" data-dt-idx="4" tabindex="0">5</a></li>
                                                        <li class="dt-paging-button page-item disabled"><a class="page-link ellipsis" aria-controls="example" aria-disabled="true" data-dt-idx="ellipsis" tabindex="-1">…</a></li>
                                                        <li class="dt-paging-button page-item"><a href="#" class="page-link" aria-controls="example" data-dt-idx="11" tabindex="0">12</a></li>
                                                        <li class="dt-paging-button page-item"><a href="#" class="page-link next" aria-controls="example" aria-label="Next" data-dt-idx="next" tabindex="0">›</a></li>
                                                        <li class="dt-paging-button page-item"><a href="#" class="page-link last" aria-controls="example" aria-label="Last" data-dt-idx="last" tabindex="0">»</a></li>
                                                    </ul>
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