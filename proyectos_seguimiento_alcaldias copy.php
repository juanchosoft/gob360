<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';


// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
/* if (!$view) {
    require 'permiso_denegado.php';
} */

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
                                <h5 class="m-b-10">Detalle Proyectos Alcaldías con ayuda de Secretarias Gobernación</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Proyectos Alcaldías / Seguimiento
                                        Proyectos</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-xl-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Detalle Proyectos por Alcaldía</h5>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> maximize</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> remove</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="col-lg-12">
                                <div class="card-body table-border-style">
                                    <!-- Tabla de datos -->
                                    <div class="table-responsive">
                                        <div class="contenedor">
                                            <div class="contenido">
                                                <div class="card table-card-l">
                                                    <h5 class="card-header">Listado Ingreso Información</h5>
                                                    <div class="card-body table-border-style">
                                                        <div>
                                                            <div class="table-responsive tabla-informacion tabla-scroll">
                                                                <table class="table table-hover mb-0" id="dynamictable">
                                                                    <thead>
                                                                        <tr class="border-1 listado">
                                                                            <th>Detalles</th>
                                                                            <th>Eliminar</th>
                                                                            <th>Fecha</th>
                                                                            <th>Departamento</th>
                                                                            <th>Municipio</th>
                                                                            <th>Vereda</th>
                                                                            <th>Valor</th>
                                                                            <th>Actor</th>
                                                                            <th>Secretaria</th>
                                                                            <th>Obs.</th>
                                                                            <th>Archivo</th>
                                                                        </tr>
                                                                    </thead>
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

        <!-- [ sample-page ] end -->
    </div>

    <!-- Modal para Editar Información -->
    <div class="modal fade" id="modalEditarInformacion" tabindex="-1" role="dialog"
        aria-labelledby="modalEditarInformacionLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div style="height:600px;width: 600px; margin: auto; display: flex; justify-content: center;"
                class="modal-content">
                <div style="background-color:rgb(15, 122, 27) !important;" class="modal-header bg-primary text-white">
                    <h5 id="modalEditarInformacionLabel"
                        style="COLOR: WHITE; width: 100%; font-size: 18px; font-weight: bold; margin: 0;">
                        Editar Información
                        <i class="fa-solid fa-pen" style="margin-left: 8px; cursor: pointer;"></i>
                    </h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="alerta-modal"></div>

                <div style="margin-top: 20px;" class="modal-body">
                    <form id="formEditarInformacion">
                        <input type="hidden" id="modalId" name="id">

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalFecha">Fecha</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <i class="fa-solid fa-calendar-alt icono-input"></i> <!-- Icono de calendario -->
                                <input style="text-align: center;width:100%" type="text" class="form-control input-icon"
                                    id="modalFecha" name="modalFecha" readonly>
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 270px;" for="modalDepartamento">Departamento</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <i class="fa-solid fa-landmark icono-input"></i> <!-- Icono de departamento/gobierno -->
                                <input style="text-align: center;" type="text" class="form-control input-icon"
                                    id="modalDepartamento" name="modalDepartamento" readonly>
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 280px;" for="modalMunicipio">Municipio</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <i class="fa-solid fa-city icono-input"></i> <!-- Icono de ciudad/municipio -->
                                <input style="text-align: center; " type="text" class="form-control input-icon"
                                    id="modalMunicipio" name="modalMunicipio" readonly>
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalVereda">Vereda</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <i class="fa-solid fa-map-marker-alt icono-input"></i>
                                <!-- Icono de ubicación/vereda -->
                                <input style="text-align: center;" type="text" class="form-control input-icon"
                                    id="modalVereda" name="modalVereda" readonly>
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalNacion">Aporte Nación</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <input style="text-align: center" type="number" class="form-control input-icon"
                                    id="modalNacion" name="modalNacion">
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalAporteDepartamento">Aporte departamento</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <input style="text-align: center" type="number" class="form-control input-icon"
                                    id="modalAporteDepartamento" name="modalAporteDepartamento">
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalAporteMunicipio">Aporte municipio</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <input style="text-align: center" type="number" class="form-control input-icon"
                                    id="modalAporteMunicipio" name="modalAporteMunicipio">
                            </div>
                        </div>


                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalOtrosAportes">Otros aportes</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <input style="text-align: center" type="number" class="form-control input-icon"
                                    id="modalOtrosAportes" name="modalOtrosAportes">
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalSecretaria">Actores</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <select class="form-control" id="actores_id" name="actores_id">

                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="tbl_municipio_id" id="tbl_municipio_id">

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalSecretaria">Secretaria</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id">

                                </select>
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="modalValor">Valor total</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <input style="text-align: center" type="text" class="form-control input-icon"
                                    id="modalValor" name="modalValor" readonly>
                            </div>
                        </div>

                        <div class="form-group position-relative">
                            <label style="margin-left: 290px;" for="observaciones">Observaciones</label>
                            <div style="margin-left: 120px;" class="position-relative">
                                <textarea name="observaciones" id="observaciones" rows="2" class="form-control input-icon"></textarea>
                            </div>
                        </div>



                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEdicion()">Guardar Cambios</button>
                </div>
            </div>
        </div>

    </div>



    <!-- Modal para mostrar observaciones -->
    <div class="modal fade" id="modalObservaciones" style="background: black;" tabindex="-1" role="dialog" aria-labelledby="modalObservacionesLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 400px;">
            <div class="modal-content" role="document">
                <div class="modal-header bg-primary text-white" style="background-color: rgb(15, 122, 27) !important;">
                    <h5 class="modal-title" id="modalObservacionesLabel">Observaciones </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalObservacionesBody" style="padding: 20px;">
                    <!-- Aquí se insertará la observación -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar imágenes -->
    <div class="modal fade" id="imageModal" tabindex="-1" style="background: black;" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: rgb(15, 122, 27) !important;">
                    <h5 class="modal-title" id="imageModalLabel">Imágenes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-2" id="imageContainer" style="padding: 10px;">
                        <!-- Aquí van las imágenes -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
    </div>
    </div>

    <!-- Warning Section Ends -->

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <?php include './admin/include/generic_dataTables_segAlcaldia.php'; ?>
    <script type="text/javascript" src="admin/js/ministerios_proyectos.js"></script>


    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5/css/bootstrap.min.css">
    <!-- Iconos de caja -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</body>

</html>