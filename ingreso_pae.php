<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

$modulo = 'Banco Proyectos';


include './admin/classes/Departamento.php';
include './admin/classes/SedesEducativas.php';
date_default_timezone_set('America/Bogota');
$fecha_actual = date("Y-m-d H:i:s");
$script_tz = date_default_timezone_get();


// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}


?>




<link href="assets/css/caracterizacion_pae_gob360.css" rel="stylesheet">

<body class="gob360-pae-characterization">
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
                                <h5 class="m-b-10">Secretaría de Educación</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Secretaría de Educación / Caracterización
                                        PAE</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- HERO VISUAL GOB360 -->
            <section class="g360-pae-hero" aria-label="Caracterización PAE GOB360">
                <div class="g360-pae-hero__grid">

                    <div>
                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-pae-hero__logo"
                        >
                    </div>

                    <div>
                        <div class="g360-pae-hero__eyebrow">
                            <i class="feather icon-check-square"></i>
                            Programa de Alimentación Escolar
                        </div>

                        <h1 class="g360-pae-hero__title">
                            Caracterización PAE
                        </h1>

                        <p class="g360-pae-hero__description">
                            Registra la información territorial, institucional y
                            operativa de cada sede educativa, incluyendo utensilios,
                            cocina, almacenamiento, servicios públicos, comedor,
                            evidencias, firma y geolocalización.
                        </p>

                        <div class="g360-pae-hero__chips">
                            <span class="g360-chip g360-chip--success">
                                <i class="feather icon-shield"></i>
                                Formulario validado
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-map-pin"></i>
                                Registro territorial
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-camera"></i>
                                Evidencia y firma
                            </span>
                        </div>
                    </div>

                    <div class="g360-pae-hero__visual" aria-hidden="true">
                        <div class="g360-mini-card">
                            <i class="feather icon-home"></i>
                            <span>Sede</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-package"></i>
                            <span>Dotación</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-droplet"></i>
                            <span>Servicios</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-edit-3"></i>
                            <span>Firma</span>
                        </div>
                    </div>

                </div>
            </section>

            <!-- [ Main Content ] start -->

            <div class="row">
                <div class="col-sm-12">                    <div class="g360-pae-shell">
                      <div class="card g360-pae-form-card">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div>
                                        <h5>Formulario de caracterización PAE</h5>
                                        <p>Completa la información de la sede educativa y sus condiciones de operación.</p>
                                    </div>

                                    <span class="pae-chip">
                                        <i class="bi bi-shield-check"></i>
                                        Registro institucional
                                    </span>
                                </div>
                            </div>

                            <div class="card-body">

                        <div class="card-body m-4">
                            <form id="formsecretaria" class="needs-validation" novalidate>
                                <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="si" />
                                <input type="hidden" name="filtro" id="filtro" value="vereda" />

                                <section class="g360-school-section">
                                    <div class="g360-section-heading">
                                        <span class="g360-section-heading__icon">
                                            <i class="feather icon-map-pin"></i>
                                        </span>

                                        <div>
                                            <h3>Identificación y ubicación de la sede</h3>
                                            <p>Fecha, territorio, institución, sede, responsable y tipo de visita.</p>
                                        </div>
                                    </div>

                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Fecha<span
                                                class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="date" class="form-control" id="date" name="date"
                                            required>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom02">Provincia</label>
                                        <select class="form-control" style="width: 100%;" id="provincia"
                                            name="provincia">
                                            <option value="Soto_Norte">Soto Norte</option>
                                            <option value="Guanenta">Guanentá</option>
                                            <option value="Garcia_Rovira">García Rovira</option>
                                            <option value="Comunera">Comunera</option>
                                            <option value="Velez">Velez</option>
                                            <option value="Metropolitana">Metropolitana</option>
                                            <option value="Yariguíes">Yariguíes</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Departamento<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" style="width: 100%;"
                                            onchange="DEPARTAMENTO.getMunicipios();" id="tbl_departamento_id"
                                            name="tbl_departamento_id" disabled> <?php echo $optionDep; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="validationCustom05">Municipio<span
                                                    class="text-danger mb-1">*</span></label>
                                           <select class="form-control" style="width: 100%;" id="tbl_municipio_id"
                                                onchange="DEPARTAMENTO.getVeredasByMunicipioId(); INGRESOPAE.getSedesEducativasByCodigoMunicipio(this.value); INGRESOPAE.getDatosByInstitucionEducativaId(this.value);"
                                                name="tbl_municipio_id">
                                            </select>

                                        </div>
                                    </div>
                                    
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="validationCustom05">Vereda<span
                                                    class="text-danger mb-1">*</span></label>
                                            <select class="form-control" style="width: 100%;" id="tbl_vereda_id"
                                                name="tbl_vereda_id">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="institucion_educativa">Institución Educativa<span class="text-danger mb-1">*</span></label>
                                            <select onchange="INGRESOPAE.setInstitucion(this.value);" class="form-control" id="institucion_educativa" name="institucion_educativa">
                                                <option value="">Seleccione una institución</option>
                                            </select>

                                            <input type="hidden" id="tbl_instituciones_educativas_id" name="tbl_instituciones_educativas_id" />
                                    </div>



                                    <div class="form-group col-md-3">
                                        <label for="validationCustom05">Sede Educativa<span
                                                class="text-danger mb-1">*</span></label>
                                        <select onchange="INGRESOPAE.getDatosBySedeEducativaId(this.value);"
                                            class="form-control" id="tbl_sede_educativa_id"
                                            name="tbl_sede_educativa_id">
                                            </select>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Nombre del Rector de la Sede Educativa<span
                                                class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="text" class="form-control" id="rector" name="rector"
                                            required>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Cédula
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control" id="cc" name="cc"
                                            pattern="[0-9]" required>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Teléfono
                                            <span class="text-danger mb-1">*</span>
                                        </label>
                                        <input placeholder="" type="number" class="form-control" id="tel" name="tel"
                                            pattern="[0-9]" required>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Tipo de Zona<span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="sector" name="sector" disabled>
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Urbano">Urbano</option>
                                            <option value="Rural">Rural</option>
                                        </select>
                                    </div>

                                    <!-- <div class="form-group col-md-2">
                                        <label for="validationCustom01">Tipo recolección Información
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="cargue_info" name="cargue_info">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Campo">En Campo</option>
                                            <option value="Historico">Histórico</option>
                                        </select>
                                    </div> -->

                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Actualizar Visita
                                            <span class="text-danger mb-1">*</span>
                                        </label>
                                        <select class="form-control font-weight-bold" id="visita" name="visita"
                                            style="background-color: yellow; color: black; font-weight: bold;">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Primera_Vez">Primera Vez</option>
                                            <option value="Seguimiento_1">Seguimiento 1</option>
                                            <option value="Seguimiento_2">Seguimiento 2</option>
                                            <option value="Seguimiento_3">Seguimiento 3</option>
                                            <option value="Seguimiento_4">Seguimiento 4</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Población Mayoritaria indígena<span
                                                class="text-danger mb-1"></span></label>
                                        <select class="form-control" id="atencion_mayor_indigena"
                                            name="atencion_mayor_indigena">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>

                                </section>

                                <div class="pae-section">
                                    <div class="pae-section-title">
                                        <p class="ttl mb-0"><i class="bi bi-basket2-fill me-2"></i>Utensilios Restaurante</p>
                                        <span class="pae-chip"><i class="bi bi-check2-circle"></i> Sección</span>
                                    </div>

                                    <div class="p-3">
                                        <div class="row pae-form-grid">

                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿La Sede Educativa cuenta con cucharones y
                                            cucharas para
                                            servir exclusivas para el uso del PAE?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="posee_cucharones_pae"
                                            name="posee_cucharones_pae">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿La Sede Educativa cuenta con cuchillos
                                            exclusivos para
                                            el uso del PAE?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="posee_cuchillos_pae"
                                            name="posee_cuchillos_pae">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿La Sede Educativa cuenta con ollas, olletas o
                                            pailas
                                            exclusivas para el uso del PAE?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="posee_ollas_pae" name="posee_ollas_pae">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿De cuántos platos dispone para el consumo de
                                            alimentos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control" id="cant_platos_pae"
                                            name="cant_platos_pae" pattern="[0-9]" required>

                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿De cuántos pocillos dispone para el consumo de
                                            alimentos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control" id="pocillos_pae"
                                            name="pocillos_pae" pattern="[0-9]" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿De cuántos tenedores dispone para el consumo de
                                            alimentos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control" id="tenedores_pae"
                                            name="tenedores_pae" pattern="[0-9]" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿De cuántas cucharas dispone para el consumo de
                                            alimentos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control" id="cucharas_pae"
                                            name="cucharas_pae" pattern="[0-9]" required>
                                    </div>
                                </div>
                                <div class="card-header">
                                    <h6> <b> Elementos Cocina</b></h6>
                                </div>
                                <br>

                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Posee Neveras de almacenamiento
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="neveras_almacenamiento"
                                            name="neveras_almacenamiento">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="cant_neveras">¿Cuántas neveras tiene para almacenamiento? <span
                                                class="text-danger mb-1">*</span></label>
                                        <input type="number" class="form-control solo-numeros" id="cant_neveras"
                                            name="cant_neveras" required inputmode="numeric" pattern="[0-9]*"
                                            maxlength="4" autocomplete="off">
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántas neveras para almacenamiento funcionan?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input type="number" class="form-control solo-numeros" id="neveras_funcionando"
                                            name="neveras_funcionando" pattern="[0-9]" required inputmode="numeric"
                                            pattern="[0-9]*" maxlength="4" autocomplete="off">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">¿Cuál de los siguientes tamaños corresponde a la
                                            mayoría
                                            de las neveras que funcionan?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="tamano_neveras_principales"
                                            name="tamano_neveras_principales">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Nevera_domestica_vertical_400_800L">Nevera domestica vertical
                                                400
                                                800L</option>
                                            <option value="Nevera_domestica_vertical_menor_400L">Nevera domestica
                                                vertical menor
                                                400L</option>
                                            <option value="Nevera_vertical_1200_70L">Nevera vertical 1200 1600LL
                                            </option>
                                            <option value="Nevera_vertical_1600_2200L">Nevera vertical 1600 2200L
                                            </option>
                                            <option value="Otro_Tamano_nevera">Otro Tamaño de nevera</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Tiene congeladores Para almacenamiento
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="congeladores" name="congeladores">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántos congeladores tiene Para Almacenamiento?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="cantidad_congeladores" name="cantidad_congeladores" pattern="[0-9]"
                                            required inputmode="numeric" pattern="[0-9]*" maxlength="4"
                                            autocomplete="off">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántos congeladores Para almacenamiento
                                            funcionan ?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="congeladores_funcionando" name="congeladores_funcionando"
                                            pattern="[0-9]" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">¿Cuál de los siguientes tamaños corresponde a la
                                            mayoría
                                            de los congeladores que funcionan?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="tamano_congelador" name="tamano_congelador">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Congelador_Grande_1400_1600L">Congelador_Grande_1400_1600L
                                            </option>
                                            <option value="Congelador_Mediano_400_800L">Nevera domestica vertical menor
                                                400L
                                            </option>
                                            <option value="Congelador_Pequeño_Menor_400L">Congelador Pequeño Menor_400L
                                            </option>
                                            <option value="Otro_Tamano_congelador">Otro Tamaño de congelador</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Tienen Estufas Para preparación PAE
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estufa" name="estufa">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántas estufas tiene solo-números?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="cant_estufas" name="cant_estufas" pattern="[0-9]" required
                                            inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántos quemadores o fogones tienen en total
                                            sus
                                            estufas?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="cant_quemadores" name="cant_quemadores" pattern="[0-9]" required
                                            inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántos de esos quemadores funcionan
                                            correctamente?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="cant_quemadores_buenos" name="cant_quemadores_buenos" pattern="[0-9]"
                                            required inputmode="numeric" pattern="[0-9]*" maxlength="4"
                                            autocomplete="off">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Tiene Licuadoras Para preparación PAE
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="licuadora" name="licuadora">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántas licuadoras tiene Para preparación PAE?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="cant_licuadora" name="cant_licuadora" pattern="[0-9]" required
                                            inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántas de estas licuadoras funcionan?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="cant_licuadoras_buenas" name="cant_licuadoras_buenas" pattern="[0-9]"
                                            required inputmode="numeric" pattern="[0-9]*" maxlength="4"
                                            autocomplete="off">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Cuántas de las licuadoras que funcionan son
                                            industriales?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="cant_licuadoras_ind" name="cant_licuadoras_ind" pattern="[0-9]" required
                                            inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off">
                                    </div>
                                </div>
                                <div class="card-header">
                                    <h6> <b> Estado lugares de preparación y almacenamiento PAE</b></h6>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Tienen un espacio dedicado al almacenamiento de
                                            alimentos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="espacio_almacenamiento"
                                            name="espacio_almacenamiento">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Almuerzo PAE es Preparado en sitio
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="preparado_sitio" name="preparado_sitio">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Posee espacio único para
                                            preparación de alimentos
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="espacio_dedicado" name="espacio_dedicado">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿tienen un espacio dedicado al
                                            almacenamiento de alimentos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="espacio_almacenamiento"
                                            name="espacio_almacenamiento">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="Intermitente">Intermitente</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Almacena alimentos en tarimas o estibas
                                            elevados del suelo?

                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="almacena_alto_suelo"
                                            name="almacena_alto_suelo">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Qué elementos utiliza para el almacenamiento de
                                            alimentos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="elementos_almacenamiento"
                                            name="elementos_almacenamiento">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Balde">Balde</option>
                                            <option value="Caja">Caja</option>
                                            <option value="Cajaninguno">Cajaninguno</option>
                                            <option value="Canastilla">Canastilla</option>
                                            <option value="Estante">Estante</option>
                                            <option value="Ninguno">Ninguno</option>
                                            <option value="No_aplica">No aplica</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Estado Techo de Lugar de Almacenamiento
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estado_techo_almacenamiento"
                                            name="estado_techo_almacenamiento">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Primera_Vez">Bueno</option>
                                            <option value="Malo">Malo</option>
                                            <option value="Regular">Regular</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Estado Piso de Lugar de Almacenamiento
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estado_piso_almacenamiento"
                                            name="estado_piso_almacenamiento">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bueno">Bueno</option>
                                            <option value="Malo">Malo</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Estado Paredes de Lugar de Almacenamiento
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estado_paredes_almacenamiento"
                                            name="estado_paredes_almacenamiento">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bueno">Bueno</option>
                                            <option value="Malo">Malo</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Estado Paredes de Lugar de Preparación
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estado_paredes_preparacion"
                                            name="estado_paredes_preparacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bueno">Bueno</option>
                                            <option value="Malo">Malo</option>
                                            <option value="Regular">Regular</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Estado Techo de Lugar de Preparación
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estado_techo_preparacion"
                                            name="estado_techo_preparacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bueno">Bueno</option>
                                            <option value="Malo">Malo</option>
                                            <option value="Regular">Regular</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Estado Piso de Lugar de Preparación
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estado_piso_preparacion"
                                            name="estado_piso_preparacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bueno">Bueno</option>
                                            <option value="Malo">Malo</option>
                                            <option value="Regular">Regular</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Estado Paredes de Lugar de Preparación
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="material_paredes_preparacion"
                                            name="material_paredes_preparacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bueno">Bueno</option>
                                            <option value="Malo">Malo</option>
                                            <option value="Regular">Regular</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-header">
                                    <h6> <b> Tipo de materiales de preparación y almacenamiento PAE</b></h6>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Material predominante del techo en el
                                            almacenamiento en ese lugar es:
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="material_techo" name="material_techo">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Techo_Concreto">Techo Concreto</option>
                                            <option value="Cemento_gravilla">Otro Tipo de Techo</option>
                                            <option value="Techo_Metal_Acero">Techo Metal Acero</option>
                                            <option value="Techo_paja_madera">Techo paja madera</option>
                                            <option value="Tejas_barro_arcilla">Tejas barro arcilla</option>
                                            <option value="Tejas_plastico">Tejas plástico</option>
                                            <option value="Zinc">Zinc</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Material predominante del piso en el
                                            almacenamiento en ese lugar es:
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="material_piso" name="material_piso">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Baldosa">Baldosa</option>
                                            <option value="Cemento_Gravilla">Cemento Gravilla</option>
                                            <option value="Ladrillo">Ladrillo</option>
                                            <option value="Otro_alm_piso">Otro tipo de piso</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Material predominante de paredes en el
                                            almacenamiento en ese lugar es:
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="material_paredes" name="material_paredes">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bahareque_Madera_Tabla_Tablon">Bahareque Madera Tabla Tablón
                                            </option>
                                            <option value="Bloque_Ladrillo_Piedra_Adobe">Bloque Ladrillo Piedra Adobe
                                            </option>
                                            <option value="Material_prefabricado_Drywall_Aglomerado_Lamina_Polietileno">
                                                Material
                                                prefabricado Drywall Aglomerado Lamina Polietileno</option>
                                            <option value="Otro_alm_paredes">Otro tipo de pared</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Material predominante del techo de la
                                            preparación en ese lugar es:
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="material_techo_preparacion"
                                            name="material_techo_prepareacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Otro_prep_techo">Otro prep techo</option>
                                            <option value="Sin_Techo">Sin techo</option>
                                            <option value="Techo_Concreto">Techo Concreto</option>
                                            <option value="Techo_Metal_Acero">Techo Metal Acero</option>
                                            <option value="Techo_paja_madera">Techo paja madera</option>
                                            <option value="Teja_Eternit">Teja Eternit</option>
                                            <option value="Tejas_barro_arcilla">Tejas_barro arcilla</option>
                                            <option value="Tejas_plastico">Tejas plástico</option>
                                            <option value="Zinc">Zinc</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Material predominante del piso de la preparación
                                            en ese lugar es:
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="material_piso_preparacion"
                                            name="material_piso_preparacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Baldosa">Baldosa</option>
                                            <option value="Cemento_Gravilla">Cemento Gravilla</option>
                                            <option value="Ladrillo">Ladrillo</option>
                                            <option value="Madera_Tabla_Tablon">Madera tabla tablón</option>
                                            <option value="Otro_prep_piso">Otro tipo de preparación de piso</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Material predominante de paredes de la
                                            preparación en ese lugar es:
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="material_paredes_preparacion"
                                            name="material_paredes_preparacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bahareque_Madera_Tabla_Tablon">Bahareque Madera Tabla Tablón
                                            </option>
                                            <option value="Bloque_Ladrillo_Piedra_Adobe">Bloque Ladrillo Piedra Adobe
                                            </option>
                                            <option value="Material_prefabricado_Drywall_Aglomerado_Lamina_Polietileno">
                                                Material
                                                prefabricado Drywall Aglomerado Lamina Polietileno</option>
                                            <option
                                                value="MATERIAL PREFABRICADO (DRYWALL, AGLOMERADO, LÁMINAS EN POLIETILENO)">
                                                MATERIAL PREFABRICADO (DRYWALL, AGLOMERADO, LÁMINAS EN POLIETILENO)
                                            </option>
                                            <option value="Salon_clases">Otro tipo de preparación de pared</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-header">
                                    <h6> <b> Acceso a Servicios Públicos</b> </h6>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Tiene Acceso y a una buena calidad de Agua
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="acceso_agua" name="acceso_agua">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="Intermitente">Intermitente</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿De dónde obtiene principalmente el agua para el
                                            PAE?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="tipo_agua" name="tipo_agua">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Acueducto">Acueducto</option>
                                            <option value="Agua_botella_bolsa">Agua_botella_bolsa</option>
                                            <option value="Agua_Lluvia">Agua_Lluvia</option>
                                            <option value="Carrotanque">Carrotanque</option>
                                            <option value="Cuerpos_aguas_Rios_Quebradas">Cuerpos aguas rios quebradas
                                            </option>
                                            <option value="Otro_obtiene_agua">Otro obtiene agua</option>
                                            <option value="Pozo">Pozo</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Tiene Acceso y a una buena calidad de Energía
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="acceso_electricidad"
                                            name="acceso_electricidad">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="Intermitente">Intermitente</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">¿De dónde obtiene principalmente la energía para
                                            cocinar los alimentos del PAE?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="tipo_gas" name="tipo_gas">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Electricidad">Electricidad</option>
                                            <option value="Gas_Natural_Pipeta">Gas Natural_Pipeta</option>
                                            <option value="Leña_Madera_Carbon_leña_Carbon_mineral">Leña Madera Carbon
                                                leña
                                                Carbon mineral</option>
                                            <option value="Materiales_desecho">Materiales desecho</option>
                                            <option value="No_aplica">No aplica</option>
                                            <option value="Petroleo_Gasolina_Kerosene_alcohol">Petroleo Gasolina
                                                Kerosene_
                                                alcohol</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿Se cuenta con el servicio de recolección
                                            de basuras (aseo)?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="tiene_recoleccion_basura"
                                            name="tiene_recoleccion_basura">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">¿La sede cuenta con el servicio de
                                            alcantarillado?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="sede_alcantarillado"
                                            name="sede_alcantarillado">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿Realizan algún tipo de clasificación de
                                            residuos
                                            sólidos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="clasificacion_residuos"
                                            name="clasificacion_residuos">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Como se realiza la disposición de los residuos
                                            orgánicos (provenientes de los restos de alimentos)
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="disposicion_derechos_pae"
                                            name="disposicion_derechos_pae">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Lo_Entierran">Lo_Entierran</option>
                                            <option value="Lo_Queman">Lo_Queman</option>
                                            <option value="Lo_Reciclan">Lo_Reciclan</option>
                                            <option value="Lo_tiran_patio_Lote_Zanja_Baldio">Lo Tiran Patio Lote Zanja
                                                Baldio
                                            </option>
                                            <option value="Uso_alimento_animales">Uso alimento animales</option>
                                            <option value="Uso_compost_Lombricultura">Uso Compost Lombricultura</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">¿Como se realiza la disposición de los residuos
                                            no
                                            orgánicos (plástico, metal, vidrio, papel, cartón) ?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="disposicion_no_organicos_pae"
                                            name="disposicion_no_organicos_pae">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Lo_Entierran">Lo Entierran</option>
                                            <option value="Lo_Queman">Lo Queman</option>
                                            <option value="Lo_Reciclan">Lo Reciclan</option>
                                            <option value="Lo_tiran_patio_Lote_Zanja_Baldio">Lo Tiran Patio Lote Zanja
                                                Baldio
                                            </option>
                                            <option value="Uso_alimento_animales">Uso alimento animales</option>
                                            <option value="Uso_compost_Lombricultura">Uso Compost Lombricultura</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿Como se realiza la disposición de los desechos
                                            del
                                            pae (plástico, metal, vidrio, papel, cartón)?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="disposicion_desechos_pae"
                                            name="disposicion_desechos_pae">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Lo_Entierran">Lo Entierran</option>
                                            <option value="Lo_Queman">Lo Queman</option>
                                            <option value="Lo_Reciclan">Lo Reciclan</option>
                                            <option value="Lo_tiran_patio_Lote_Zanja_Baldio">Lo Tiran Patio Lote Zanja
                                                Baldío
                                            </option>
                                            <option value="Uso_alimento_animales">Uso alimento animales</option>
                                            <option value="Uso_compost_Lombricultura">Uso Compost Lombricultura</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿De cuántas canecas dispone para el desecho de
                                            alimentos?
                                            <span class="text-danger mb-1">*</span></label>
                                        <input placeholder="" type="number" class="form-control" id="cant_canecas"
                                            name="cant_canecas" pattern="[0-9]" required>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Posee orinales o inodoros exclusivo
                                            para el personal manipulador de alimentos
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="posee_sanitario_exclusivo_pae"
                                            name="posee_sanitario_exclusivo_pae">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Posee lavamanos exclusivos para el personal
                                            manipulador
                                            de alimentos
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="posee_lavamanos_pae"
                                            name="posee_lavamanos_pae">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-header">
                                    <h6> <b> Restaurante PAE</b></h6>
                                </div>
                                <br>
                                <div class="row">

                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Complemento Am/Pm (Preparado en sitio)<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="complemento_ampm" name="complemento_ampm">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>

                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Complemento Am/Pm (Industrializado) <span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="complemento_ampm_indus"
                                            name="complemento_ampm_indus">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Almuerzo (Modalidad comida caliente
                                            transportada)
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="comida_transportada"
                                            name="comida_transportada">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Sede Educativa cuenta con comedor escolar
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="comedor_escolar" name="comedor_escolar">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Tipo de espacio que se utiliza para el consumo
                                            de
                                            alimentos
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="espacio_consumo" name="espacio_consumo">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Espacio_cerrado_exclusivo_comedor">Espacio cerrado exclusivo
                                                comedor
                                            </option>
                                            <option value="Espacio_con_techo_compartido_areas_comunes">Espacio con techo
                                                compartido areas comunes</option>
                                            <option value="Espacio_sin_techo">Espacio sin_techo</option>
                                            <option value="Otro_espacio_consumo">Otro espacio consumo</option>
                                            <option value="salon_clases">Salon de clases</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom02">Tiene con un concepto sanitario
                                            emitido por una autoridad
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="concepto_sanitario" name="concepto_sanitario">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">fecha de expedición del concepto higiénico
                                            sanitario (CHS)?
                                        </label>
                                        <input type="date" class="form-control" placeholder="0%" id="fecha_expedicion"
                                            name="fecha_expedicion" required>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Está cerca de potenciales fuentes de
                                            contaminación (basureros, mataderos, pantanos)?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="cerca_a_contaminacion"
                                            name="cerca_a_contaminacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Esta Ubicada zona de
                                            conflicto armado e inestabilidad social?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="sede_conflicto_armado"
                                            name="sede_conflicto_armado">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Si">Si</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">¿Con qué frecuencia el conflicto armado o la
                                            inestabilidad social afectan la entrega del PAE?
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="frecuencia_conflicto"
                                            name="frecuencia_conflicto">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="No_Aplica">No Aplica</option>
                                            <option value="Algo_Frecuente">Algo Frecuente</option>
                                            <option value="Poco_Frecuente">Poco Frecuente</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="validationCustom01">Cuántos niños caben
                                            sentados en el comedor al tiempo
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="cant_ninos_pae_sentados"
                                            name="cant_ninos_pae_sentados">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="La_mitad_50%">La mitad 50%</option>
                                            <option value="Menos_mitad_25%">Menos mitad 25%</option>
                                            <option value="Todos_100%">Todos 100%</option>
                                            <option value="Un_poco_mas_mitad_75%">Un poco mas mitad 75%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-header">
                                    <h6> <b>Estado Sedes Educativas</b></h6>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Estado Sede Educativa
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="estado_sede" name="estado_sede">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Antiguo_Activo">Antiguo Activo</option>
                                            <option value="Nuevo_Activo">Nuevo Activo</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Clasificacion
                                            <span class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="clasificacion" name="clasificacion">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Bueno">Bueno</option>
                                            <option value="Malo">Malo</option>
                                            <option value="Regular">Regular</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-header">
                                    <h6> <b>Otros</b></h6>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Total Niños Focalizados
                                        </label>
                                        <input placeholder="" type="number" class="form-control solo-numeros"
                                            id="ninos_focalizados" name="ninos_focalizados" pattern="[0-9]" required
                                            inputmode="numeric" pattern="[0-9]*" maxlength="4" autocomplete="off">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Año<span
                                                class="text-danger mb-1 ">*</span></label>
                                        <select class="form-control" id="ano" name="ano" disabled>
                                            <option value="2025" selected>2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>
                                            <option value="2031">2031</option>
                                            <option value="2032">2032</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-1">
                                        <input type="hidden" id="latitud" name="latitud">
                                    </div>
                                    <div class="form-group col-md-1">
                                        <input type="hidden" id="longitud" name="longitud">
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label>Observaciones</label>
                                        <div>
                                            <textarea required="" placeholder="Ingrese observaciones de la obra"
                                                type="text" class="form-control" id="observaciones"
                                                name="observaciones">
                                            </textarea>
                                        </div>

                                    </div>
                                </div>
                        </div>
                        <!-- ///////////////////////////////////////////////////////// INICIO CAMARA//////// /////////////////////////////////////////////////////////////-->

                        <section class="g360-media-section">
                            <div class="g360-section-heading">
                                <span class="g360-section-heading__icon">
                                    <i class="feather icon-camera"></i>
                                </span>

                                <div>
                                    <h3>Evidencias, firma y geolocalización</h3>
                                    <p>Captura fotografías y registra la firma que respalda la caracterización.</p>
                                </div>
                            </div>

                        <div class="container-fluid p-0">
                            <div class="row justify-content-center">
                                <!-- Radios -->
                                <div class="col-md-6 mb-4">
                                    <div class="card shadow-sm g360-camera-card">
                                        <div class="card-body text-center">
                                            <p id="estado" class="mb-3 fw-bold text-primary">Configura la evidencia fotográfica</p>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="radio_select"
                                                    id="radiosfoto" value="1" checked>
                                                <label class="form-check-label" for="radiosfoto">Continuar sin fotografía</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="radio_select"
                                                    id="radiotfoto" value="0">
                                                <label class="form-check-label" for="radiotfoto">Tomar fotografía</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cámara -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm g360-camera-card">
                                        <div class="card-body text-center">
                                            <!-- Video en vivo -->
                                            <video id="video" class="video-preview rounded border mb-3" autoplay playsinline style="display: none;"></video>


                                            <!-- Selección de dispositivo -->
                                            <div id="selectcamdevice" style="display: none;">
                                                <h5>Seleccione la cámara</h5>
                                                <select name="listaDeDispositivos" id="listaDeDispositivos"
                                                    class="form-control mb-3"></select>
                                            </div>

                                            <!-- Canvas para captura -->
                                            <canvas id="canvas" class="w-100 border rounded mb-3"
                                                style="display: none;"></canvas>


                                            <input type="hidden" id="fotos" name="fotos">
                                           <button id="boton" type="button" class="btn btn-primary w-100">
                                            <i class="bi bi-camera-fill mr-2"></i>Capturar fotografía
                                            </button>

                                            <div id="estadoFotosTomada"></div>
                                            <div id="contenedorFotosTomadas"></div>

                                        </div>
                                    </div>
                                </div>
                                <!-- Firma -->
                                <div class="col-12 col-lg-6">
                                    <div class="signature-wrap">
                                        <div class="signature-head">
                                        <div>
                                            <p class="ttl mb-1"><i class="bi bi-pen-fill"></i> Firma</p>
                                            <p class="sub mb-0">Usa tu dedo en celular o el mouse en PC.</p>
                                        </div>
                                        <span class="pae-chip"><i class="bi bi-phone"></i> Responsive</span>
                                        </div>

                                        <div class="signature-body">
                                        <canvas id="signature-pad"></canvas>
                                        <input type="hidden" name="signature" id="signature">

                                        <div class="signature-actions">
                                            <button type="button" id="clear" class="btn btn-outline-dark">
                                            <i class="bi bi-eraser-fill me-2"></i>Limpiar firma
                                            </button>
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                            </div>
                        </div>
                        </section>

                        <!-- ESTA LINEA ESTÁ DUPLICADA CON LA INFORMACION DE LAS FOTOS  -->
                        <!--   <input type="hidden" id="tbl_id_pae" value="123456789" >Este debe ser dinámico -->
                        <!-- <div id="contenedor-video-preview" class="text-center" style="display: none;">
                            <video id="video" autoplay playsinline class="w-100 rounded mb-3"></video>
                            <canvas id="canvas" class="w-100 rounded border mb-3"></canvas>
                            <img id="foto-preview" src="#" alt="Vista previa" class="w-100 rounded border mb-3">
                        </div> -->


                        <!-- ///////////////////////////////////////////////////////// FIN CAMARA//////// /////////////////////////////////////////////////////////////-->
                        <br>

                        <div class="pae-actions">
                        <div class="row g-2">
                            <div class="col-12 col-lg-3">
                            <button type="button" onclick="UTIL.clearForm('formsecretaria');" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle mr-2"></i>Cancelar
                            </button>
                            </div>

                            <div class="col-12 col-lg-9">
                            <button type="button" class="btn btn-primary w-100" onclick="capturarUbicacionAntesDeGuardar()">
                                <i class="bi bi-save2 mr-2"></i>Guardar caracterización
                            </button>
                            <div class="hint">
                                Se capturará geolocalización antes de guardar (si el navegador lo permite).
                            </div>
                            </div>
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
</div>

    
    <script>
(function () {
  function resizeSignatureCanvas() {
    const canvas = document.getElementById('signature-pad');
    if (!canvas) return;

    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const rect = canvas.getBoundingClientRect();

    // fija tamaño real para buena nitidez
    canvas.width = Math.floor(rect.width * ratio);
    canvas.height = Math.floor(rect.height * ratio);

    const ctx = canvas.getContext('2d');
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

    // Si tu signature.js ya inicializó pad, esto igual ayuda (no lo rompe)
  }

  window.addEventListener('load', resizeSignatureCanvas);
  window.addEventListener('resize', () => {
    // throttle básico
    clearTimeout(window.__sigResizeT);
    window.__sigResizeT = setTimeout(resizeSignatureCanvas, 180);
  });
})();
</script>


    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script>
        function capturarUbicacionAntesDeGuardar() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        document.getElementById('latitud').value = position.coords.latitude;
                        document.getElementById('longitud').value = position.coords.longitude;
                        INGRESOPAE.validateData(); // Solo se ejecuta si se tiene ubicación
                    },
                    function(error) {
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                alert("❌ Permiso de geolocalización denegado.");
                                break;
                            case error.POSITION_UNAVAILABLE:
                                alert("❌ La información de ubicación no está disponible.");
                                break;
                            case error.TIMEOUT:
                                alert("⏱️ La solicitud para obtener la ubicación expiró.");
                                break;
                            default:
                                alert("⚠️ Error desconocido al obtener la ubicación.");
                                break;
                        }
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                alert("⚠️ Tu navegador no soporta geolocalización.");
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            $("input[name='radio_select']").change(function() {
                if ($("#radiotfoto").is(":checked")) {
                    $("#video").show();
                    $("#btn_activar").show();
                } else {
                    $("#video, #canvas, #selectcamdevice").hide();
                }
            });
            $("#btn_activar").click(function() {
                activarCamara();
            });
            $("#btn_tomar").click(function() {
                tomarFoto();
            });
        });
    </script>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <!-- Script de Signature Pad -->
    <script type="text/javascript" src="./admin/js/lib/signature_pad.umd.min.js"></script>
    <script>
        // Solo permitir números en tiempo real
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.solo-numeros').forEach(input => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/[^0-9]/g, '');
                });
            });
        });
    </script>

    <script type="text/javascript" src="./admin/js/signature.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="admin/js/ingreso_pae.js"></script>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script type="text/javascript" src="./admin/js/script_camara.js"></script>

    <script>
        setTimeout(function() {
            $("#tbl_departamento_id").val('68')
        }, 500);
        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 1000);
    </script>

</body>

</html>