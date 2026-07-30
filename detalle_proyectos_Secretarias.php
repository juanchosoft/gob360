<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

include './admin/classes/Proyectos.php';

// Información de Proyectos por Id
$arr = Proyectos::getAll(["id" => $_REQUEST["id"]]);
$isvalid = $arr['output']['valid'];
$proyecto = $arr['output']['response'][0];

//Información de Proyectos
$arrobser = Proyectos::getAllobser(null);
$isvalid = $arrobser['output']['valid'];
$arrobser = $arrobser['output']['response'];
?>

<link href="assets/css/actualizacion_proyecto_gob360.css" rel="stylesheet">

<body class="gob360-project-update">
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
                                <h5 class="m-b-10">Actualización de Proyecto</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Banco de Proyectos / Detalle / Actualización del proyecto </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- HERO VISUAL GOB360 -->
            <section class="g360-project-update-hero" aria-label="Actualización de proyecto GOB360">
                <div class="g360-project-update-hero__grid">

                    <div>
                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-project-update-hero__logo"
                        >
                    </div>

                    <div>
                        <div class="g360-project-update-hero__eyebrow">
                            <i class="feather icon-edit-3"></i>
                            Seguimiento y actualización institucional
                        </div>

                        <h1 class="g360-project-update-hero__title">
                            Actualización del Proyecto
                        </h1>

                        <p class="g360-project-update-hero__description">
                            Revisa la información general del proyecto y actualiza
                            aportes, contratistas, estado, prórrogas, ejecución física,
                            avance financiero y observaciones de seguimiento.
                        </p>

                        <div class="g360-project-update-hero__chips">
                            <span class="g360-chip g360-chip--success">
                                <i class="feather icon-check-circle"></i>
                                Expediente activo
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-activity"></i>
                                Seguimiento de ejecución
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-clock"></i>
                                Historial de observaciones
                            </span>
                        </div>

                        <div class="g360-project-summary">
                            <div class="g360-project-summary__item">
                                <span>Proyecto</span>
                                <strong>#<?php echo htmlspecialchars($proyecto["id"]); ?></strong>
                            </div>

                            <div class="g360-project-summary__item">
                                <span>Municipio</span>
                                <strong><?php echo htmlspecialchars($proyecto["municipio"]); ?></strong>
                            </div>

                            <div class="g360-project-summary__item">
                                <span>Estado actual</span>
                                <strong><?php echo htmlspecialchars($proyecto["estado"]); ?></strong>
                            </div>

                            <div class="g360-project-summary__item g360-project-summary__item--money">
                                <span>Inversión total</span>
                                <strong>$ <?php echo number_format((float)$proyecto["valor_proyecto"], 0); ?></strong>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card g360-project-form-card">
                        <div class="card-header">
                            <div>
                                <h5>Ficha de seguimiento del proyecto</h5>
                                <p>Los campos de identificación permanecen bloqueados; actualiza únicamente la información habilitada.</p>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <form class="needs-validation" novalidate>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="g360-form-section">
                                                    <div class="g360-form-section__content">
                                                        <span class="g360-form-section__icon"><i class="feather icon-file-text"></i></span>
                                                        <div>
                                                            <h6>Información general del proyecto</h6>
                                                            <p>Identificación, territorio, objeto y dependencia responsable.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Fecha</label>
                                                <input type="hidden" name="idProyecto" id="idProyecto" value="<?php echo $proyecto["id"] ?>">
                                                <input placeholder="" type="date" class="form-control" id="date"
                                                    name="date" value="<?php echo $proyecto["date"] ?>" readonly>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom02">Provincia</label>
                                                <input type="text" class="form-control" id="provincia" name="provincia"
                                                    value="<?php echo $proyecto["provincia"] ?>" readonly>

                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Departamento</label>
                                                <input type="text" class="form-control" id="tbl_departamento_id"
                                                    name="tbl_departamento_id" value="<?php echo $proyecto["tbl_departamento_id"] ?>" readonly>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Municipio beneficiado</label>
                                                <input type="trxt" class="form-control" id=""
                                                    name="" value="<?php echo $proyecto["municipio"] ?>" readonly>
                                                <input type="hidden" class="form-control" id="tbl_municipio_id"
                                                    name="tbl_municipio_id" value="<?php echo $proyecto["tbl_municipio_id"] ?>" readonly>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Objeto del proyecto</label>
                                                <input autocomplete="false" type="text"
                                                    placeholder="Describa el objeto del proyecto brevemente"
                                                    class="form-control" id="proyecto" name="proyecto"
                                                    value="<?php echo $proyecto["proyecto"] ?>"
                                                    readonly>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom05">Secretaria o Dependencia
                                                    Encargada</label>
                                                <input type="text" class="form-control" id=""
                                                    name="" value="<?php echo $proyecto["secretaria"] ?>" readonly>
                                                <input type="hidden" class="form-control" id="tbl_secretarias_id"
                                                    name="tbl_secretarias_id" value="<?php echo $proyecto["tbl_secretarias_id"] ?>" readonly>
                                            </div>

                                            <div class="col-12">
                                                <div class="g360-form-section">
                                                    <div class="g360-form-section__content">
                                                        <span class="g360-form-section__icon"><i class="feather icon-dollar-sign"></i></span>
                                                        <div>
                                                            <h6>Estructura financiera</h6>
                                                            <p>Aportes municipales, departamentales, nacionales y valor total de la inversión.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Aporte Municipio</label>
                                                <input type="number" class="form-control"
                                                    onKeyPress="return soloNumeros(event);" id="aporte_municipio"
                                                    name="aporte_municipio" placeholder="0" value="<?php echo $proyecto["aporte_municipio"] ?>">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Aporte Departamento</label>
                                                <input type="number" class="form-control"
                                                    onKeyPress="return soloNumeros(event);" id="aporte_gobernacion"
                                                    name="aporte_gobernacion" placeholder="0" value="<?php echo $proyecto["aporte_gobernacion"] ?>">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Aporte Nación</label>
                                                <input type="number" class="form-control"
                                                    onKeyPress="return soloNumeros(event);" id="aporte_nacion"
                                                    name="aporte_nacion" placeholder="0" value="<?php echo $proyecto["aporte_nacion"] ?>">
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Total Inversión</label>
                                                <input type="number" class="form-control"
                                                    onKeyPress="return soloNumeros(event);" id="valor_proyecto"
                                                    name="valor_proyecto" placeholder="" value="<?php echo $proyecto["valor_proyecto"] ?>" readonly>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Fecha Inicio</label>
                                                <input placeholder="" type="date" class="form-control" id="date_inicio"
                                                    name="date_inicio" value="<?php echo $proyecto["date_inicio"] ?>" readonly>
                                            </div>
                                            <div class="col-12">
                                                <div class="g360-form-section">
                                                    <div class="g360-form-section__content">
                                                        <span class="g360-form-section__icon"><i class="feather icon-briefcase"></i></span>
                                                        <div>
                                                            <h6>Contratación y cronograma</h6>
                                                            <p>Contratista, interventoría, plazo, inicio y fecha prevista de entrega.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Contratista</label>
                                                <input autocomplete="false"
                                                    placeholder="Ingrese el nombre de o los contratistas" type="text"
                                                    class="form-control" id="contratista" name="contratista" value="<?php echo $proyecto["contratista"] ?>">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Interventoría</label>
                                                <input autocomplete="false"
                                                    placeholder="Ingrese el nombre de o los Interventores" type="text"
                                                    class="form-control" id="interventoria" name="interventoria"
                                                    value="<?php echo $proyecto["interventoria"] ?>">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Plazo en meses</label>
                                                <input autocomplete="false" placeholder="tiempo de proyecto en meses"
                                                    type="number" class="form-control" id="plazo_construccion"
                                                    name="plazo_construccion" value="<?php echo $proyecto["plazo_construccion"] ?>" readonly>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Fecha Entrega</label>
                                                <input placeholder="" type="date" class="form-control"
                                                    id="fecha_entrega" name="fecha_entrega" value="<?php echo $proyecto["fecha_entrega"] ?>">
                                            </div>
                                            <div class="col-12">
                                                <div class="g360-form-section">
                                                    <div class="g360-form-section__content">
                                                        <span class="g360-form-section__icon"><i class="feather icon-activity"></i></span>
                                                        <div>
                                                            <h6>Estado y ejecución</h6>
                                                            <p>Actualiza el estado del proyecto y los porcentajes físico y financiero.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label for="validationCustom01">Estado Actual</label>
                                                <input placeholder="" type="text" class="form-control" id="estado_actual"
                                                    name="estado_actual" value="<?php echo $proyecto["estado"] ?>" readonly>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="validationCustom02">Nuevo Estado Proyecto</label>
                                                <select class="form-control" id="estado" name="estado">
                                                    <option value="Seleccione">Seleccione</option>
                                                    <option value="En Formulación">En Formulación</option>
                                                    <option value="Radicado en Banco de Proyectos">Radicado en Banco de Proyectos</option>
                                                    <option value="Aprobado Banco de Proyectos">Aprobado Banco de Proyectos</option>
                                                    <option value="Estudios Previos">Estudios Previos</option>
                                                    <option value="Proceso de Giro">Proceso de Giro</option>
                                                    <option value="Proceso de Giro">Proceso de Giro</option>
                                                    <option value="En subsanación por parte del DAF">En subsanación por parte del DAF</option>
                                                    <option value="En subsanación por parte del Municipio">En subsanación por parte del Municipio</option>
                                                    <option value="En Contrataciòn">En Contrataciòn</option>
                                                    <option value="Sin Iniciar">Sin Iniciar</option>
                                                    <option value="Ejecución">Ejecución</option>
                                                    <option value="Ejecutado">Ejecutado</option>
                                                    <option value="Terminado">Terminado</option>
                                                    <option value="Terminado - Sin Liquidar">Terminado - Sin Liquidar</option>
                                                    <option value="Terminado - Liquidado">Terminado - Liquidado</option>
                                                    <option value="Liquidación">Liquidación</option>
                                                    <option value="Entregado">Entregado</option>
                                                    <option value="Finalizado">Finalizado</option>
                                                    <option value="Suspendido">Suspendido</option>
                                                    <option value="A la espera por parte del Municipio">A la espera por parte del Municipio</option>
                                                    <option value="Proyecto viabilizado en MINSALUD">Proyecto viabilizado en MINSALUD</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="validationCustom01">Porcentaje de obra física o contrato actual</label>
                                                <input placeholder="" type="text" class="form-control"
                                                    id="porcentaje_ejecucion_actual" name="porcentaje_ejecucion_actual" value="<?php echo $proyecto["porcentaje_ejecucion"] ?>"
                                                    readonly>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="validationCustom01">Nuevo porcentaje de ejecución de la
                                                    obra física</label>
                                                <input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="0%"
                                                    id="porcentaje_ejecucion" name="porcentaje_ejecucion" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="validationCustom01">Porcentaje Ejecución Financiera actual</label>
                                                <input placeholder="" type="text" class="form-control"
                                                    id="porcentaje_financiero_actual" name="porcentaje_financiero_actual" value="<?php echo $proyecto["porcentaje_financiero"] ?>"
                                                    readonly>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="validationCustom01">Nuevo porcentaje de ejecución Ejecución Financiera</label>
                                                <input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="0%"
                                                    id="porcentaje_financiero" name="porcentaje_financiero" required>
                                            </div>
                                            <div class="col-12">
                                                <div class="g360-form-section">
                                                    <div class="g360-form-section__content">
                                                        <span class="g360-form-section__icon"><i class="feather icon-clock"></i></span>
                                                        <div>
                                                            <h6>Prórrogas y adiciones</h6>
                                                            <p>Registra ampliaciones de plazo y modificaciones presupuestales.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Fecha de prórroga</label>
                                                <input placeholder="" type="date" class="form-control"
                                                    id="date_prorroga" name="date_prorroga" value="">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Días de prórroga</label>
                                                <input onKeyPress="return soloNumeros(event);" placeholder="" type="number" class="form-control"
                                                    id="dias_prorroga" name="dias_prorroga">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="validationCustom01">Adición presupuestal</label>
                                                <input autocomplete="false" placeholder="Ingrese el Valor de la adición"
                                                    type="number" class="form-control" id="adicion" name="adicion">
                                            </div>

                                            <div class="col-12">
                                                <div class="g360-form-section">
                                                    <div class="g360-form-section__content">
                                                        <span class="g360-form-section__icon"><i class="feather icon-message-square"></i></span>
                                                        <div>
                                                            <h6>Observaciones de seguimiento</h6>
                                                            <p>Describe novedades, decisiones, compromisos o hechos relevantes del proyecto.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-12">
                                                <label>Observaciones</label>
                                                <div>
                                                    <textarea required="" placeholder="Ingrese observaciones de seguimiento del proyecto"
                                                        type="text" class="form-control" id="observaciones"
                                                        name="observaciones"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary" type="button" onclick="DETALLE_PROYECTO.updatedata();">Actualizar
                                            Información</button>
                                    </form>
                                    

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- [ sample-page ] start -->
                        <div class="col-sm-12">
                            <div class="card g360-observations-card">
                                <div class="card-header">
                                    <div>
                                        <h5>Historial de observaciones</h5>
                                        <p>Consulta las novedades registradas durante el seguimiento del proyecto.</p>
                                    </div>
                                </div>

                                <div class="col-md-12 p-0">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table id="example" class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Observación</th>
                                                        <th>Fecha</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($isvalid && !empty($arrobser)) {
                                                        foreach ($arrobser as $obs) {
                                                            if (!empty($obs['observaciones'])) {
                                                                echo '<tr>';
                                                                echo '<td>' . htmlspecialchars($obs['observaciones']) . '</td>';
                                                                echo '<td>' . htmlspecialchars($obs['dtcreate']) . '</td>';
                                                                echo '</tr>';
                                                            }
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='3'>No hay observaciones registradas para este proyecto.</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
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

    <?php include 'admin/include/gerenic_script.php'; ?>

    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>


    <script type="text/javascript" src="admin/js/detalle_proyectos.js"></script>

    <?php if (isset($_REQUEST["tec_proyecto_id"])) : ?>
        <script>
            var id = "<?php echo $_REQUEST["tec_proyecto_id"] ?>";
            DETALLE_PROYECTO.edit(id);
        </script>
    <?php endif ?>
    <script>
        new DataTable('#example', {
            select: true
        });
    </script>

</body>

</html>