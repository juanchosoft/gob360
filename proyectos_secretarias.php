<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

$modulo = 'Banco Proyectos';

include './admin/classes/Proyectos.php';
include './admin/classes/Departamento.php';
include './admin/classes/Secretarias.php';

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de secretarias
$arr = Secretarias::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$optionSec = "";
foreach ($arr as $val) {
    $optionSec .= "<option value='" . $val['id'] . "'>" . $val['secretaria'] . " </option>";
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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">

                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Proyectos Secretarías </h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Proyectos Secretaría / Seguimiento
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
                        <div class="card-header">
                            <h5>Ingreso Información Secretarias</h5>
                        </div>
                        <div class="card-body m-4">
                            <form id="formsecretaria" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Fecha<span
                                                class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="date" class="form-control" id="date" name="date"
                                            required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom02">Provincia<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="provincia" name="provincia">ç
                                            <option value="Comunera">Comunera</option>
                                            <option value="Garcia_Rovira">García Rovira</option>
                                            <option value="Guanenta">Guanentá</option>
                                            <option value="Metropolitana">Metropolitana</option>
                                            <option value="Soto_Norte">Soto Norte</option>
                                            <option value="Velez">Velez</option>
                                            <option value="Yariguíes">Yariguíes</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Departamento<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" style="width: 100%;"
                                            onchange="DEPARTAMENTO.getMunicipios();" id="tbl_departamento_id"
                                            name="tbl_departamento_id" disabled> <?php echo $optionDep; ?>
                                        </select>

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Municipio Beneficiado<span
                                                class="text-danger mb-1">*</span></label>
                                        <select multiple class="form-control"
                                            onchange="DEPARTAMENTO.getVeredasByMunicipioId();" id="tbl_municipio_id"
                                            name="tbl_municipio_id[]"> </select>

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="proyecto">Objeto del proyecto<span class="text-danger mb-1">*</span></label>
                                        <textarea
                                            class="form-control"
                                            id="proyecto"
                                            name="proyecto"
                                            placeholder="Describa el objeto del proyecto brevemente"
                                            rows="4"
                                            autocomplete="off"></textarea>


                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom05">Secretaria o Dependencia Encargada<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="tbl_secretarias_id"
                                            name="tbl_secretarias_id"><?php echo $optionSec; ?></select>

                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Aporte Municipio</label>
                                        <input type="number" class="form-control"
                                            onKeyPress="return soloNumeros(event);" id="aporte_municipio"
                                            name="aporte_municipio" placeholder="0" value="">

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Aporte Departamento</label>
                                        <input type="number" class="form-control"
                                            onKeyPress="return soloNumeros(event);" id="aporte_gobernacion"
                                            name="aporte_gobernacion" placeholder="0" value="">

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Aporte Nacion</label>
                                        <input type="number" class="form-control"
                                            onKeyPress="return soloNumeros(event);" id="aporte_nacion"
                                            name="aporte_nacion" placeholder="0" value="">

                                    </div>
                                     <div class="form-group col-md-4">
                                        <label for="validationCustom01">Otros Aportes</label>
                                        <input type="number" class="form-control"
                                            onKeyPress="return soloNumeros(event);" id="otros_aportes"
                                            name="otros_aportes" placeholder="0" value="">

                                    </div>
                                     <div class="form-group col-md-4">
                                        <label for="validationCustom01">Entidad Otros Aportes</label>
                                        <input type="text" class="form-control"
                                            id="entidad_otros"
                                            name="entidad_otros" placeholder="Nombre de la Entidad" value="">

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Total Inversión<span
                                                class="text-danger mb-1">*</span></label>
                                        <input type="number" class="form-control"
                                            onKeyPress="return soloNumeros(event);" id="valor_proyecto"
                                            name="valor_proyecto" placeholder="0" value="">

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Fecha Inicio<span
                                                class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="date" class="form-control" id="date_inicio"
                                            name="date_inicio" required>

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Fecha Entrega<span
                                                class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="date" class="form-control" id="fecha_entrega"
                                            name="fecha_entrega">

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Contratista</label>
                                        <input autocomplete="false"
                                            placeholder="Ingrese el nombre de o los contratistas" type="text"
                                            class="form-control" id="contratista" name="contratista">

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Nit Contratista</label>
                                        <input autocomplete="false"
                                            placeholder="Ingrese el nit del contratista, si es mas de uno separelo con guión"
                                            type="text" class="form-control" id="nit" name="nit">

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Interventoria</label>
                                        <input autocomplete="false"
                                            placeholder="Ingrese el nombre de o los Interventores" type="text"
                                            class="form-control" id="interventoria" name="interventoria">

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Plazo meses</label>
                                        <input autocomplete="false" placeholder="tiempo de proyecto en meses"
                                            type="number" class="form-control" id="plazo_construccion"
                                            name="plazo_construccion">

                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom02">Estado Proyecto<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estado" name="estado">
                                            <option value="En Formulación">En Formulación</option>
                                            <option value="En Actualización">Actualizaciòn</option>
                                            <option value="Radicación">Radicado en Banco de Proyectos</option>
                                            <option value="Aprobado Banco de Proyectos">Aprobado Banco de Proyectos</option>
                                            <option value="Estudios Previos">Estudios Previos</option>
                                            <option value="En proceso de Giro">Proceso de Giro</option>                                         
                                            <option value="En subsanación por parte del DAF">En subsanación por parte del DAF</option>
                                            <option value="En subsanación por parte del Municipio">En subsanación por parte del Municipio</option>
                                            <option value="En Contrataciòn">En Contratación</option>
                                            <option value="Sin Iniciar">Sin Iniciar</option>
                                            <option value="En Ejecución">Ejecución</option>
                                            <option value="Pendiente Recursos">Pendiente Recursos</option>
                                            <option value="Ejecutado">Ejecutado</option>
                                            <option value="Terminado">Terminado</option>
                                            <option value="Terminado - NO liquidado">Terminado - Sin Liquidar</option>
                                            <option value="Liquidado">Terminado - Liquidado</option>
                                            <option value="En liquidacion">Liquidación</option>
                                            <option value="Entregado">Entregado</option>
                                            <option value="Finalizado">Finalizado</option>
                                            <option value="Suspendido">Suspendido</option>
                                            <option value="A la espera por parte del Municipio">A la espera por parte del Municipio</option>
                                            <option value="Proyectos viabilizados en min salud">Proyecto viabilidad en MINSALUD</option>
                                            <option value="Desistio del convenio">Desistió del convenio</option>
                                            <option value="Proceso Precontractual">Proceso precontractual</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Ejecución de la obra Física</label>
                                        <input type="text" class="form-control" placeholder="0%"
                                            id="porcentaje_ejecucion" name="porcentaje_ejecucion" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Ejecución Financiera</label>
                                        <input type="text" class="form-control" placeholder="0%"
                                            id="porcentaje_financiero" name="porcentaje_financiero" required>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Fecha Prorroga</label>
                                        <input placeholder="" type="date" class="form-control" id="date_prorroga"
                                            name="date_prorroga">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Dias Prorroga</label>
                                        <input placeholder="" type="number" class="form-control" id="dias_prorroga"
                                            name="dias_prorroga">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Adición Presupuestal</label>
                                        <input autocomplete="false" placeholder="Ingrese el Valor de la adición"
                                            type="number" class="form-control" id="adicion" name="adicion">

                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Observaciones</label>
                                        <div>
                                            <textarea required="" placeholder="Ingrese observaciones de la obra"
                                                type="text" class="form-control" id="observaciones"
                                                name="observaciones"></textarea>
                                        </div>

                                    </div>
                                </div>

                                <div class="form-row">

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                        <button type="button" onclick="UTIL.clearForm('formsecretaria');"
                                            class="btn  btn-danger">Cancelar</button>

                                        <button type="button" onclick="PROYECTOS.validateData();"
                                            class="btn btn-primary">Ingresar Proyecto</button>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>

    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>



    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/proyectos.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const municipioSelect = document.getElementById('tbl_municipio_id');

            //ESTE ES EL SCRIPT DE SELECCION MULTIPLE Y LOS COLORES QUE SE LE APLICA
            function updateOptionColors() {
                const selectedOptions = municipioSelect.selectedOptions;
                const colors = ['#FFD700', '#FF4500', '#32CD32', '#1E90FF', '#FF1493'];


                Array.from(selectedOptions).forEach((option, index) => {
                    if (index < colors.length) {
                        option.style.backgroundColor = colors[index];
                        option.style.color = '#FFFFFF';
                    } else {
                        option.style.backgroundColor = '#808080';
                        option.style.color = '#FFFFFF';
                    }
                });


                Array.from(municipioSelect.options).forEach(option => {
                    if (!option.selected) {
                        option.style.backgroundColor = '';
                        option.style.color = '';
                        l
                    }
                });
            }


            municipioSelect.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const option = e.target;
                if (option.tagName === 'OPTION') {
                    option.selected = !option.selected;
                    updateOptionColors();
                }
            });
        });
    </script>


    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script>
        setTimeout(function() {
            $("#tbl_departamento_id").val(UTIL.getDepartamentoPrincipal())
        }, 500);
        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 1000);
    </script>

</body>

</html>