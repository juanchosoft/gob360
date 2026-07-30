<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

$modulo = 'Tic';

include './admin/classes/Departamento.php';
include './admin/classes/SedesEducativas.php';
include './admin/classes/PcTic.php';


// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de sedes educativas
$arr = SedesEducativas::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$optionSed = "";
foreach ($arr as $val) {
    $optionSed .= "<option value='" . $val['id'] . "'>" . $val['nombre'] . " </option>";
}

// Informacion de los tic
$arrtic = PcTic::getAll(null);
$isvalid = $arrtic['output']['valid'];
$arrtic = $arrtic['output']['response'];
?>

<link href="assets/css/entregas_tecnologia_tic_gob360.css" rel="stylesheet">

<body class="gob360-tic-deliveries">
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
                                <h5 class="m-b-10">Secretaría TIC</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Secretaría TIC / Entregas tecnológicas</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- HERO VISUAL GOB360 -->
            <section class="g360-tic-hero" aria-label="Entregas tecnológicas GOB360">
                <div class="g360-tic-hero__grid">

                    <div>
                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-tic-hero__logo"
                        >
                    </div>

                    <div>
                        <div class="g360-tic-hero__eyebrow">
                            <i class="feather icon-cpu"></i>
                            Transformación digital educativa
                        </div>

                        <h1 class="g360-tic-hero__title">
                            Entregas tecnológicas
                        </h1>

                        <p class="g360-tic-hero__description">
                            Registra y consulta las entregas de kits de robótica,
                            computadores y laboratorios de innovación realizadas
                            en las instituciones y sedes educativas del territorio.
                        </p>

                        <div class="g360-tic-hero__chips">
                            <span class="g360-chip g360-chip--success">
                                <i class="feather icon-check-circle"></i>
                                Registro institucional
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-map-pin"></i>
                                Trazabilidad territorial
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-image"></i>
                                Evidencia fotográfica
                            </span>
                        </div>
                    </div>

                    <div class="g360-tic-hero__visual" aria-hidden="true">
                        <div class="g360-mini-card">
                            <i class="feather icon-cpu"></i>
                            <span>Robótica</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-monitor"></i>
                            <span>Equipos</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-zap"></i>
                            <span>Innovación</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-map"></i>
                            <span>Territorio</span>
                        </div>
                    </div>

                </div>
            </section>

            <!-- [ Main Content ] start -->

            <div class="row">
                <div class="col-sm-12">
                    <div class="card g360-tic-form-card">
                        <div class="card-header">
                            <div>
                                <h5>Registro de entrega tecnológica</h5>
                                <p>Completa la ubicación, sede educativa, dotación entregada y evidencia del registro.</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="formsecretaria" class="needs-validation" novalidate>
                                <input type="hidden" id="filtro" name="filtro" value="vereda">
                                <input type="hidden" id="cod_dane" name="cod_dane">

                                <section class="g360-tic-section">
                                    <div class="g360-tic-section__header">
                                        <span class="g360-tic-section__icon">
                                            <i class="feather icon-map-pin"></i>
                                        </span>

                                        <div>
                                            <h3>Ubicación y dotación educativa</h3>
                                            <p>Información territorial, sede beneficiada y cantidades entregadas.</p>
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
                                        <select class="form-control" style="width: 100%;" id="provincia" name="provincia">
                                            <option value="Seleccione..." selected>Seleccione...</option>
                                            <option value="Soto_Norte">Soto Norte</option>
                                            <option value="Guanenta">Guanentá</option>
                                            <option value="Garcia_Rovira">García Rovira</option>
                                            <option value="Comunera">Comunera</option>
                                            <option value="Velez">Velez</option>
                                            <option value="Metropolitana">Metropolitana</option>
                                            <option value="Yariguíes">Yariguíes</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="validationCustom05">Departamento<span
                                                    class="text-danger mb-1">*</span></label>
                                            <select onchange="DEPARTAMENTO.getMunicipios();"
                                                class="form-control" style="width: 100%;" id="tbl_departamento_id"
                                                name="tbl_departamento_id">
                                                <?php echo $optionDep; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for="validationCustom05">Municipio<span
                                                    class="text-danger mb-1">*</span></label>
                                            <select class="form-control" style="width: 100%;" id="tbl_municipio_id" name="tbl_municipio_id">

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="exampleFormControlSelect1">Vereda<span
                                                    class="text-danger mb-1">*</span></label>
                                            <select class="form-control" style="width: 100%;" id="tbl_vereda_id"
                                                name="tbl_vereda_id">
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom05">Sede Educativa<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="tbl_sede_educativa_id"
                                            name="tbl_sede_educativa_id"><?php echo $optionSed; ?></select>

                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Institución Educativa<span
                                                class="text-danger mb-1">*</span></label>
                                        <input autocomplete="false" type="text"

                                            class="form-control" id="tbl_instituciones_educativas_id" name="tbl_instituciones_educativas_id" disabled>

                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Sector<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" id="zona" name="zona">
                                            <option value="Seleccione">Seleccione</option>
                                            <option value="Urbano">Urbano</option>
                                            <option value="Rural">Rural</option>

                                        </select>

                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Kits de robótica entregados
                                        </label>
                                        <input type="number" class="form-control" placeholder=""
                                            id="robotica" name="robotica" required>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Computadores entregados a instituciones
                                        </label>
                                        <input type="number" class="form-control" placeholder=""
                                            id="computadores_institucion" name="computadores_institucion">
                                    </div>


                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Computadores entregados a estudiantes
                                        </label>
                                        <input type="number" class="form-control" placeholder=""
                                            id="computador_alumno" name="computador_alumno">
                                    </div>


                                    <div class="form-group col-md-2">
                                        <label for="validationCustom01">Laboratorios de innovación entregados
                                        </label>
                                        <input type="number" class="form-control" placeholder=""
                                            id="laboratorio_innovacion" name="laboratorio_innovacion">
                                    </div>




                                    <div class="form-group col-md-6">
                                        <label>Observaciones</label>
                                        <div>
                                            <textarea required="" placeholder="Ingrese observaciones de la entrega tecnológica"
                                                type="text" class="form-control" id="observaciones"
                                                name="observaciones"></textarea>
                                        </div>

                                    </div>
                                </div>
                                </section>

                                <?php if ($create && $edit) { ?>
                                    <div class="form-group g360-photo-upload">
                                        <label class="control-label" for="exampleInputName2"><i class="feather icon-camera mr-2"></i>Evidencia fotográfica</label>
                                        <div class="col-sm-12 p-0">
                                            <div class="controls">
                                                <iframe id='ifm' name='ifm' src="upload.php" width="200" height="60"
                                                    scrolling="no" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                <?php

                                }
                                ?>


                                <div class="form-row g360-form-actions">

                                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 d-flex justify-content-end flex-wrap">
                                        <button type="button" onclick="UTIL.clearForm('formsecretaria');"
                                            class="btn btn-danger mr-2">
                                            <i class="feather icon-x-circle"></i>
                                            Cancelar
                                        </button>

                                        <button type="button" onclick="TIC.validateData();"
                                            class="btn btn-primary">
                                            <i class="feather icon-save"></i>
                                            Guardar entrega
                                        </button>


                                    </div>
                                </div>
                        </div>
                    </div>

                    </form>
                    <br>
                    <div class="contenedor">
                        <div class="contenido">
                            <div class="card g360-tic-list-card">
                                <div class="card-header">
                                    <div>
                                        <h5>Historial de entregas TIC</h5>
                                        <p>Consulta, edita y revisa la evidencia fotográfica de cada entrega tecnológica.</p>
                                    </div>
                                </div>

                                <div class="card-body table-border-style">

                                    <!-- Tabla de datos -->
                                    <div class="table-responsive">
                                        <table id="dynamictable" class="table table-hover mb-0">
                                            <thead>
                                                <tr class="border-1">
                                                    <th>Editar</th>
                                                    <th>Fecha</th>
                                                    <th>Provincia</th>
                                                    <th>Municipio</th>
                                                    <th>Vereda</th>
                                                    <th>Establecimiento Educativo</th>
                                                    <th>Zona</th>
                                                    <th>Kits de robótica</th>
                                                    <th>Equipos para instituciones</th>
                                                    <th>Equipos para estudiantes</th>
                                                    <th>Laboratorios de innovación</th>
                                                    <th>Observaciones</th>
                                                    <th>Foto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($isvalid && !empty($arrtic)): ?>
                                                    <?php foreach ($arrtic as $item): ?>
                                                        <?php
                                                        $img = !empty($item["img"]) ? "assets/img/admin/" . htmlspecialchars($item["img"]) : 'assets/img/santander.png';
                                                        ?>
                                                        <tr>
                                                            <!-- Botón Editar -->
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-primary" title="Editar dato"
                                                                    onclick="TIC.editData(<?= htmlspecialchars($item['id']) ?>)">
                                                                    <i class="feather icon-edit"></i>
                                                                </button>
                                                            </td>

                                                            <!-- Datos del tic -->
                                                            <td><?= htmlspecialchars($item['date']) ?></td>
                                                            <td><?= htmlspecialchars($item['provincia']) ?></td>
                                                            <td><?= htmlspecialchars($item['municipio']) ?></td>
                                                            <td><?= htmlspecialchars($item['nombre_vereda'] ?? 'N/A') ?></td>
                                                            <td><?= htmlspecialchars($item['sede']) ?></td>
                                                            <td><?= htmlspecialchars($item['zona']) ?></td>
                                                            <td><?= htmlspecialchars($item['robotica']) ?></td>
                                                            <td><?= htmlspecialchars($item['computadores_institucion']) ?></td>
                                                            <td><?= htmlspecialchars($item['computador_alumno']) ?></td>
                                                            <td><?= htmlspecialchars($item['laboratorio_innovacion']) ?></td>
                                                            <td><?= htmlspecialchars($item['observaciones']) ?></td>
                                                            <!-- Imagen -->
                                                            <td class="text-primary text-center">
                                                                <img
                                                                    width="60"
                                                                    height="60"
                                                                    src="<?= $img ?>"
                                                                    alt="Foto evidencia"
                                                                    data-toggle="modal"
                                                                    data-target="#imageModal<?= $item['id']; ?>"
                                                                    style="cursor: pointer;"
                                                                    class="rounded border object-fit-cover">

                                                                <!-- Modal -->
                                                                <div class="modal fade" id="imageModal<?= $item['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel<?= $item['id']; ?>" aria-hidden="true">
                                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="imageModalLabel<?= $item['id']; ?>">
                                                                                    Foto de la entrega TIC en <?= htmlspecialchars($item['nombre_vereda'] ?? 'vereda desconocida'); ?>
                                                                                </h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body text-center">
                                                                                <img src="<?= $img ?>" alt="Imagen evidencia" class="img-fluid">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>

                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="13" class="text-center text-muted">No se encontraron registros.</td>
                                                    </tr>
                                                <?php endif; ?>
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






    <!-- Required Js -->
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <?php include 'admin/include/gerenic_script.php'; ?>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script type="text/javascript" src="admin/js/tic.js"></script>
    <?php include './admin/include/generic_dataTables.php'; ?>
    <script>
        //SOBREESCRIBIR EL JS DE DEPARTAMENTOS Y VEREDAS PARA QUE EL EDITAR PUEDA FUNCIONAR
        let waitMunInterval = null;
        let waitVerInterval = null;

        TIC.editHandler = function(data) {
            UTIL.cursorNormal();
            if (!data.output.valid) {
                UTIL.mostrarMensajeError(data.output.response.content);
                return;
            }

            const res = data.output.response[0];
            const safe = val => (val === null || val === undefined || val === "null") ? "" : val;
            const normalize = val => (typeof val === "string") ? val.trim().toLowerCase().replace(/_/g, " ") : safe(val);

            // Asignar datos simples
            $("#id").val(safe(res.id));
            $("#date").val(safe(res.date));
            $("#robotica").val(safe(res.robotica));
            $("#computadores_institucion").val(safe(res.computadores_institucion));
            $("#computador_alumno").val(safe(res.computador_alumno));
            $("#laboratorio_innovacion").val(safe(res.laboratorio_innovacion));
            $("#observaciones").val(safe(res.observaciones));
            $("#cod_dane").val(safe(res.cod_dane));
            $("#tbl_sede_educativa_id").val(safe(res.tbl_sede_educativa_id)).trigger("change");

            // Provincia y zona (sector)
            const provinciaVal = normalize(res.provincia);
            const zonaVal = normalize(res.zona);
            $("#provincia_hidden").val(provinciaVal);
            $("#zona_hidden").val(zonaVal);

            if (!$("#provincia option[value='" + provinciaVal + "']").length && provinciaVal !== "") {
                $("#provincia").append(`<option value="${provinciaVal}" selected hidden>${provinciaVal}</option>`);
            }
            $("#provincia").val(provinciaVal);

            if (!$("#zona option[value='" + zonaVal + "']").length && zonaVal !== "") {
                $("#zona").append(`<option value="${zonaVal}" selected hidden>${zonaVal}</option>`);
            }
            $("#zona").val(zonaVal);

            // Departamento, municipio y vereda
            const depVal = safe(res.tbl_departamento_id);
            const municipioVal = safe(res.tbl_municipio_id);
            const veredaVal = safe(res.tbl_vereda_id);
            const municipioNombre = safe(res.municipio);
            const veredaNombre = safe(res.nombre_vereda);

            window.__edicionForzada = true;

            $("#tbl_municipio_id").html('');
            $("#tbl_vereda_id").html('');
            $("#tbl_departamento_id").val(depVal);

            if (waitMunInterval) clearInterval(waitMunInterval);
            if (waitVerInterval) clearInterval(waitVerInterval);

            DEPARTAMENTO.getMunicipios();

            let tries = 0;
            waitMunInterval = setInterval(() => {
                const $mun = $("#tbl_municipio_id");
                if ($mun.children().length > 0 || tries > 10) {
                    clearInterval(waitMunInterval);

                    $mun.val(municipioVal);
                    if (!$mun.find(`option[value="${municipioVal}"]`).length) {
                        $mun.append(`<option value="${municipioVal}" selected hidden>${municipioNombre}</option>`);
                    }

                    DEPARTAMENTO.getVeredasByMunicipioId();

                    let triesVer = 0;
                    waitVerInterval = setInterval(() => {
                        const $ver = $("#tbl_vereda_id");
                        if ($ver.children().length > 0 || triesVer > 10) {
                            clearInterval(waitVerInterval);

                            $ver.val(veredaVal);
                            if (!$ver.find(`option[value="${veredaVal}"]`).length) {
                                $ver.append(`<option value="${veredaVal}" selected hidden>${veredaNombre}</option>`);
                            }

                            window.__edicionForzada = false;
                        }
                        triesVer++;
                    }, 300);
                }
                tries++;
            }, 300);

            // Imagen previa
            if (res.img && res.img !== "") {
                const imgPath = "assets/img/admin/" + res.img;
                if ($("#preview-img").length === 0) {
                    $("#formsecretaria").append(`
                <div class="form-group">
                    <label>Vista previa imagen</label><br>
                    <img id="preview-img" src="" width="100" class="border rounded mt-1" />
                </div>
            `);
                }
                $("#preview-img").attr("src", imgPath);
            }
        };
    </script>
    <script>
        $(document).ready(function() {
            $("#tbl_municipio_id").on("change", function() {
                if (!window.__edicionForzada) {
                    DEPARTAMENTO.getVeredasByMunicipioId();
                }
            });

            const departamentoInicial = $("#tbl_departamento_id").val();
            if (departamentoInicial) {
                DEPARTAMENTO.getMunicipios();
            }
        });
    </script>


</body>

</html>