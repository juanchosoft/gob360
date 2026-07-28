<?php
include './admin/include/head.php';
include './admin/classes/Departamento.php';
include './admin/classes/Factores.php';
include './admin/classes/Actores.php';
require './admin/include/generic_classes.php';
// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de Factores
$arrFactores = Factores::getAll(null);
$isvalid = $arrFactores['output']['valid'];
$arrFactores = $arrFactores['output']['response'];
$optionFactores = '<option value="seleccione">Seleccione...</option>';
foreach ($arrFactores as $val) {
    $optionFactores .= "<option value='" . $val['id'] . "'>" . $val['tipo'] . "</option>";
}
// Información de Actores
$arrActores = Actores::getAll(null);
$isvalid = $arrActores['output']['valid'];
$arrActores = $arrActores['output']['response'];
$optionActores = '<option value="seleccione">Seleccione...</option>';
foreach ($arrActores as $val) {
    $optionActores .= "<option value='" . $val['id'] . "'>" . $val['nombre'] . "</option>";
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

    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div style="margin-top: -20px;"class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Actualización Información</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Configuración Acción Unificada / Actualización
                                        Información</a></li>
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
                        <h5><i style="font-size:18px"class="feather icon-refresh-cw"></i> Actualización de información</h5>

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
                            <div class="col-sm-12">
                                
                                    <div class="card-body m-4">
                                        <form id="formactualizarinformacion" autocomplete="off">

                                            <input type="hidden" name="op" id="op" />
                                            <input type="hidden" name="id" id="id" />
                                            <input type="hidden" name="filtro" id="filtro" value="vereda" />
                                            <input type="hidden" name="filtroVeredaById" id="filtroVeredaById"
                                                value="si" />
                                            <div class="row">

                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label for="validationCustom05">Departamento<span
                                                                class="text-danger mb-1">*</span></label>
                                                        <select onchange="DEPARTAMENTO.getMunicipios();"
                                                            class="form-control" id="tbl_departamento_id"
                                                            name="tbl_departamento_id">
                                                            <?php echo $optionDep; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label for="validationCustom05">Municipio<span
                                                                class="text-danger mb-1">*</span></label>
                                                        <select class="form-control" id="tbl_municipio_id"
                                                            onchange="DEPARTAMENTO.getVeredasByMunicipioId();"
                                                            name="tbl_municipio_id">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label for="exampleFormControlSelect1">Vereda<span
                                                                class="text-danger mb-1">*</span></label>
                                                        <select class="form-control" id="tbl_vereda_id"
                                                            name="tbl_vereda_id">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label for="exampleFormControlSelect1">Factores<span
                                                                class="text-danger mb-1">*</span></label>
                                                        <select class="form-control" id="factorId" name="factorId"
                                                            onchange="INGRESO_INFORMACION.showInfoGetFactores();">
                                                            <?php echo $optionFactores; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label class="floating-label" for="Text">Cantidad Nueva<span
                                                                class="text-danger mb-1">*</span></label>
                                                        <input type="text" class="form-control" id="cantidad_nueva"
                                                            onKeyPress="return soloNumeros(event);"
                                                            name="cantidad_nueva" placeholder="123">
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label for="exampleFormControlSelect1">Actores
                                                            Responsables<span class="text-danger mb-1">*</span></label>
                                                        <select class="form-control" id="actoresId" name="actoresId">
                                                            <?php echo $optionActores; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div id="divInformacion" class="card-body" style="display:none;">
                                                    <h5>Información ingresada con los parametros seleccionados</h5>
                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <label class="floating-label" for="Text">Eje</label>
                                                            <div class="form-group">
                                                                <input id="eje" name="eje" class="form-control"
                                                                    type="text" placeholder="" readonly="">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label class="floating-label" for="Text">Pilar</label>
                                                            <div class="form-group">
                                                                <input id="pilar" name="pilar" class="form-control"
                                                                    type="text" placeholder="" readonly="">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label class="floating-label" for="Text">Area</label>
                                                            <div class="form-group">
                                                                <input id="area" name="area" class="form-control"
                                                                    type="text" placeholder="" readonly="">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label class="floating-label" for="Text">Tipo
                                                                Medición</label>
                                                            <div class="form-group">
                                                                <input id="tipo_medicion" name="tipo_medicion"
                                                                    class="form-control" type="text" placeholder=""
                                                                    readonly="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label for="exampleFormControlTextarea1">Acción
                                                            Realizada<span class="text-danger mb-1">*</span></label>
                                                        <textarea id="accion_realizada" name="accion_realizada"
                                                            placeholder="Ingrese las acciones realizadas"
                                                            class="form-control" id="exampleFormControlTextarea1"
                                                            rows="5"></textarea>
                                                    </div>
                                                </div>


                                                <label class="floating-label" for="Text"></label>
                                                <div class="form-row m-4">
                                                    <div class="form-group col-md-3">
                                                        <label for="inversion">Foto 1</label>
                                                        <div class="controls">
                                                            <iframe id='ifm1' name='ifm' src="upload.php" width="200"
                                                                height="60" scrolling="no" frameborder="0"></iframe>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="inversion">Foto 2</label>
                                                        <div class="controls">
                                                            <iframe id='ifm2' name='ifm' src="upload.php" width="200"
                                                                height="60" scrolling="no" frameborder="0"></iframe>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="inversion">Foto 3</label>
                                                        <div class="controls">
                                                            <iframe id='ifm3' name='ifm' src="upload.php" width="200"
                                                                height="60" scrolling="no" frameborder="0"></iframe>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-3">
                                                        <label for="inversion">Foto 4</label>
                                                        <div class="controls">
                                                            <iframe id='ifm4' name='ifm' src="upload.php" width="200"
                                                                height="60" scrolling="no" frameborder="0"></iframe>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <button type="button" onclick="UTIL.clearForm('formactualizarinformacion');"
                                                class="btn  btn-danger">Cancelar</button>
                                            <button onclick="ACTUALIZACION_INFORMACION.save();" type="button"
                                                class="btn btn-primary"><i
                                                    class="feather mr-2 icon-check-circle"></i>Guardar</button>
                                    </div>
                                
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
    <!-- [ sample-page ] end -->
    </div>
    <!-- [ Main Content ] end -->
    </div>
    </div>
    <!-- [ Main Content ] end -->

    </div>
    <?php include 'admin/include/gerenic_script.php'; ?>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/actualizacion_estrategicos.js"></script>
    <script>
    setTimeout(function() {
        DEPARTAMENTO.getMunicipios();
    }, 1000);
    </script>

</body>

</html>