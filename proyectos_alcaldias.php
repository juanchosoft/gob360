<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';


// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
}
 */
include './admin/classes/Proyectos.php';
include './admin/classes/Departamento.php';
include './admin/classes/Ministerios.php';
$modulo = 'Banco Proyectos';

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
$option = '<option value="">Seleccione</option>';
foreach ($arrDep as $val) {
    $option .= "<option value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de Ministerios: Mostrar la columna 'ministerio'
$arrMin = Ministerios::getAll(null);
$arrMin = $arrMin['output']['response'];
$optionMin = "";
foreach ($arrMin as $val) {
    // Usar la columna 'ministerio' como valor y texto visible
    $optionMin .= "<option value='" . $val['ministerio'] . "'>" . $val['ministerio'] . "</option>";
}



// Información de secretarias
$arr = Ministerios::getAllproyectos(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
?>
<style>
    .controls {
        margin-top: 10px;
        font-family: Arial, sans-serif;
        font-size: 16px;
    }
</style>

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
                                <h5 class="m-b-10">Detalle Proyectos Alcaldías con ayuda de Secretarias Gobernación </h5>
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
            <!-- [ Main Content ] start -->

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header text-center py-4">
                            <h4 class="mb-0 d-flex justify-content-center align-items-center gap-3 fw-bold" style="font-size: 18px; ">
                                <!-- <i class="feather icon-briefcase" style="font-size: 2rem;"></i> -->
                                Ingreso Información Proyectos Alcaldías con ayuda de Secretarías Gobernación
                            </h4>
                                                   <br>
                            <br>
                            <h6 class="mb-0 d-flex justify-content-center align-items-center gap-3 fw-bold" style="font-size: 18px; "> Aclaración Jurídica:</h6>
                           

         <h6 class="mb-0 d-flex justify-content-center align-items-center gap-3 fw-bold" style="font-size: 12px; ">La presentación de un proyecto a través de este medio no constituye, en ningún caso, garantía de aprobación, asignación de código BIPIN, ni se entenderá como la radicación de una Petición, Queja o Reclamo (PQR). Esta herramienta tiene como único propósito facilitar el acercamiento institucional entre las alcaldías municipales y la Gobernación, con el fin de optimizar y agilizar los procesos de articulación y gestión administrativa. </h6>
                        </div>

                        <div class="card-body">
                            <form id="formalcaldias" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Fecha<span
                                                class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="date" class="form-control" id="date" name="date"
                                            required>

                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom02">Provincia<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="provincia" name="provincia">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Soto_Norte">Soto Norte</option>
                                            <option value="Guanenta">Guanentá</option>
                                            <option value="Garcia_Rovira">García Rovira</option>
                                            <option value="Comunera">Comunera</option>
                                            <option value="Velez">Velez</option>
                                            <option value="Metropolitana">Metropolitana</option>
                                            <option value="Yariguíes">Yariguíes</option>
                                        </select>

                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Departamento<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" style="width: 100%;"
                                            onchange="DEPARTAMENTO.getMunicipios();" id="tbl_departamento_id"
                                            name="tbl_departamento_id" disabled>
                                            <?php echo $option; ?>
                                        </select>

                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Alcaldía<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" style="width: 100%;"
                                            onchange="getActores()" id="tbl_municipio_id"
                                            name="tbl_municipio_id[]">
                                            <option value="Seleccione" selected>Seleccione</option>
                                        </select>

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Objeto del proyecto<span
                                                class="text-danger mb-1">*</span></label>
                                        <input autocomplete="false" type="text"
                                            placeholder="Describa el objeto del proyecto brevemente"
                                            class="form-control" id="proyecto" name="proyecto">

                                    </div>

                                    <div class="form-group col-md-4" id="container_secretaria">
                                        <label for="validationCustom01">Seleccione la Secretaria<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id">

                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Aportes Municipio</label>
                                        <!-- <input autocomplete="false" type="number"
                                            placeholder="Ingrese el aporte del municipio"
                                            class="form-control" id="aporteMunicipio" name="aporteMunicipio"> -->
                                        <input type="text" class="form-control bg-aporte-municipio" id="aporteMunicipio" name="aporteMunicipio" placeholder="Ingrese el aporte del municipio">

                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Aportes Departamento</label>
                                        <input type="text" class="form-control bg-aporte-departamento" id="aporteDepartamento" name="aporteDepartamento" placeholder="Ingrese el aporte del municipio">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Aportes Nación</label>
                                        <input type="text" class="form-control bg-aporte-nacion" id="aporteNacion" name="aporteNacion" placeholder="Ingrese el aporte del municipio">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Otros Aportes</label>
                                        <input type="text" class="form-control bg-aporte-otros" id="aporteOtrosProyectos" name="aporteOtrosProyectos" placeholder="Ingrese el aporte del municipio">
                                    </div>

                                    <div class="form-group col-md-4" id="container_actores" style="display: none;">
                                        <label for="validationCustom01">Seleccione Actores de otros aportes</label>
                                        <select class="form-control bg-aporte-total" id="actores_id" name="actores_id">

                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Total Inversión</label>
                                        <input type="text" class="form-control bg-aporte-total"
                                            id="valor_proyecto"
                                            name="valor_proyecto"
                                            placeholder=""
                                            value="$ 0">
                                    </div>

                                    <div class="form-group col-md-8">

                                        <label>Observaciones <span class="text-danger mb-1">*</span> </label>
                                        <div>
                                            <textarea required="" placeholder="Ingrese observaciones de la obra"
                                                type="text" class="form-control" id="observaciones"
                                                name="observaciones"></textarea>
                                        </div>

                                    </div>


                                    <div class="form-group col-md-2">
                                        <div class="form-group col-md-3">
                                            <label for="inversion">Subir Foto</label>
                                            <div class="controls">
                                                <iframe id='ifm1' name='ifm1' src="upload.php" width="80"
                                                    height="60" scrolling="no" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row m-6">
                                        <div class="form-group col-md-3">
                                            <label for="inversion">Subir PDF</label>
                                            <div class="controls">
                                                <iframe id='ifmPdf' name='ifmPdf' src="upload_pdf.php" width="80"
                                                    height="60" scrolling="no" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-row text-center">

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">

                                        <button style=" border-radius: 12px;" type="button" type="button" onclick="MINISTERIOSPRO.clearForm();"

                                            class="btn btn-danger">Cancelar</button>

                                        <button type="button" onclick="MINISTERIOSPRO.validateData();" class="btn btn-primary">Ingresar Proyecto</button>


                                    </div>
                                </div>

                            </form>
                            <style>
                                /*  .btn-primary {
                                    color: rgb(255, 255, 255);
                                    background-color: rgb(26, 188, 156);
                                    border-color: rgb(26, 188, 156);
                                } */

                                .bg-aporte-municipio {
                                    background-color: #d7eae5 !important;
                                    color: #1b5e20;

                                }


                                .bg-aporte-departamento {
                                    background-color: #d6e4f0 !important;
                                    color: #0d47a1;

                                }


                                .bg-aporte-nacion {
                                    background-color: #e3d8ec !important;
                                    color: #4a148c;

                                }


                                .bg-aporte-otros {
                                    background-color: #f7efd2 !important;
                                    color: #8d6e00;

                                }


                                .bg-aporte-total {
                                    background-color: #dde1e3 !important;
                                    color: #263238;

                                }
                            </style>
                        </div>
                    </div>
                </div>
            </div>

            <!-- [ Main Content ] end -->
        </div>
    </div>

    <!-- Warning Section Ends -->

    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/ministerios_proyectos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <?php include './admin/include/generic_dataTables.php'; ?>

    <script>
        setTimeout(function() {
            $("#tbl_departamento_id").val('68')
        }, 100);
        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 100);
    </script>

</body>

</html>