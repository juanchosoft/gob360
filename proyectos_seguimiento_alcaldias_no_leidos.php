<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';

require_once './admin/include/generic_classes.php';
include './admin/classes/Ministeriospro.php';
include './admin/classes/Secretarias.php';
include './admin/classes/Actores.php';

// Parametros recibidos
$codigoMunicipio = isset($_REQUEST['mun']) ? trim($_REQUEST['mun']) : null;
$secretariaId = isset($_REQUEST['secretaria']) ? trim($_REQUEST['secretaria']) : null;
$codigoDepartamento = Util::getDepartamentoPrincipal();

$requestParams = [
    'secretariaId' => $secretariaId,
    'codigoMunicipio' => $codigoMunicipio,
    'codigoDepartamento' => $codigoDepartamento
];

// Obtener proyectos no leídos
$result = Ministeriospro::getAllProyectosSinLeer($requestParams);
$isvalid = isset($result['output']['valid']) ? $result['output']['valid'] : false;
$arr = isset($result['output']['response']) ? $result['output']['response'] : [];
$modulo = 'Banco Proyectos no leidos';


// Informacion de los Secretarias
$arrSecretaria = Secretarias::getAll(null);
$isvalidSecr = $arrSecretaria['output']['valid'];
$arrSecretaria = $arrSecretaria['output']['response'];
$optionSecretarias = "";
foreach ($arrSecretaria as $val) {
    $optionSecretarias .= "<option value='" . $val['id'] . "'>" . $val['secretaria'] . "</option>";
}

// Informacion de los Actores
$parametros = ['alcaldia_id' => $codigoMunicipio];
$arrActores = Actores::getByAlcaldia($parametros);
$isvalidSecr = $arrActores['output']['valid'];
$arrActores = $arrActores['output']['response'];
$optionActores = "";
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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Detalle Proyectos Alcaldías con ayuda de Secretarias Gobernación
                                    Ledos</h5>
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
                            <div class="col-lg-12">
                                <!-- Tabla de datos -->
                                <div class="table-responsive">
                                    <div class="contenedor">
                                        <div class="contenido">
                                            <h5 class="card-header">Listado</h5>
                                            <div class="card-body table-border-style">
                                                <div>
                                                    <div class="table-responsive tabla-informacion tabla-scroll">
                                                        <table class="table table-hover mb-0" id="dynamictable">
                                                            <thead>
                                                                <tr class="border-1 listado">
                                                                    <th>Estado Proyecto</th>
                                                                    <th>Ver Proyecto</th>
                                                                    <th>Fecha</th>
                                                                    <th>Estado</th>
                                                                    <th>Leído</th>
                                                                    <th>Proyecto</th>
                                                                    <th>Municipio</th>
                                                                    <th>Valor</th>
                                                                    <th>Secretaría</th>
                                                                    <th>Imagen</th>
                                                                    <th>Pdf</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if ($isvalid && !empty($arr)): ?>
                                                                <?php foreach ($arr as $item): ?>
                                                                <tr id="fila_<?= htmlspecialchars($item['id']) ?>">
                                                                    <td>
                                                                        <div class="input-group input-group-sm">
                                                                            <select class="form-control border-primary"
                                                                                style="min-width: 150px; font-weight: 500;"
                                                                                onchange="MINISTERIOSPRO.saveActualizarEstado(<?= htmlspecialchars($item['id']) ?>, this.value)">
                                                                                <option value="Proyecto no leido"
                                                                                    <?= $item['estado'] === 'Proyecto no leido' ? 'selected' : '' ?>>
                                                                                    Proyecto no leído</option>
                                                                                <option value="Proyecto leido"
                                                                                    <?= $item['estado'] === 'Proyecto leido' ? 'selected' : '' ?>>
                                                                                    Proyecto leído</option>
                                                                                <option value="Proyecto actualizado"
                                                                                    <?= $item['estado'] === 'Proyecto actualizado' ? 'selected' : '' ?>>
                                                                                    Proyecto actualizado</option>
                                                                            </select>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-info"
                                                                            title="Ver Proyecto"
                                                                            onclick="MINISTERIOSPRO.showProyecto(<?= htmlspecialchars($item['id']) ?>)">
                                                                            <i class="feather icon-eye"></i>
                                                                        </button>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($item['date']) ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                                $estado = htmlspecialchars($item['estado']);
                                                                                $badgeClass = 'badge-secondary';
                                                                                if ($estado === 'Proyecto no leido') {
                                                                                    $badgeClass = 'badge-danger';
                                                                                } elseif ($estado === 'Proyecto leido') {
                                                                                    $badgeClass = 'badge-success';
                                                                                }elseif ($estado === 'Proyecto actualizado') {
                                                                                    $badgeClass = 'badge-success';
                                                                                }
                                                                                ?>
                                                                        <span class="badge <?= $badgeClass ?>">
                                                                            <?= $estado ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                                $leido = htmlspecialchars($item['leido']);
                                                                                $badgeClass = 'badge-danger';
                                                                                if ($leido === 'si') {
                                                                                    $badgeClass = 'badge-success';
                                                                                }
                                                                                ?>
                                                                        <span class="badge <?= $badgeClass ?>">
                                                                            <?= $leido ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($item['proyecto']) ?>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($item['municipio']) ?>
                                                                    </td>
                                                                    <td>$
                                                                        <?= number_format($item['valor_proyecto'], 0, ',', '.') ?>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($item['secretaria']) ?>
                                                                    </td>

                                                                    </td>
                                                                    <td>
                                                                        <?php if (!empty($item['archivo'])): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-primary"
                                                                            title="Ver imagen"
                                                                            onclick="window.open('<?= htmlspecialchars($item['archivo']) ?>', '_blank')">
                                                                            <i class="feather icon-image"></i>
                                                                        </button>
                                                                        <?php else: ?>
                                                                        <span class="badge badge-secondary">Sin
                                                                            archivo</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if (!empty($item['pdf'])): ?>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-primary"
                                                                            title="Ver pdf"
                                                                            onclick="window.open('<?= htmlspecialchars($item['pdf']) ?>', '_blank')">
                                                                            <i class="feather icon-file-text"></i>
                                                                        </button>
                                                                        <?php else: ?>
                                                                        <span class="badge badge-secondary">Sin
                                                                            pdf</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
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
                </div>
            </div>
        </div>

        <!-- [ sample-page ] end -->

        <!-- Modal del formulario de proyectos -->
        <div class="modal fade" id="modalFormularioProyectos" tabindex="-1" role="dialog"
            aria-labelledby="modalFormularioProyectosLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalFormularioProyectosLabel">Lectura Proyecto con Alcaldía -
                            Proyecto Leído</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form id="formalcaldias" class="needs-validation" novalidate>
                            <input type="hidden" id="modalId" name="modalId">
                            <div class="row">
                                <!-- Fecha -->
                                <div class="form-group col-md-3">
                                    <label for="date">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>

                                <!-- Provincia -->
                                <div class="form-group col-md-3">
                                    <label for="provincia">Provincia <span class="text-danger">*</span></label>
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

                                <!-- Alcaldía -->
                                <div class="form-group col-md-3">
                                    <label for="tbl_municipio_id">Alcaldía <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="tbl_municipio_id"
                                        name="tbl_municipio_id">
                                </div>

                                <!-- Objeto del Proyecto -->
                                <div class="form-group col-md-4">
                                    <label for="proyecto">Objeto del proyecto <span class="text-danger">*</span></label>
                                    <input type="text" placeholder="Describa el objeto del proyecto brevemente"
                                        class="form-control" id="proyecto" name="proyecto">
                                </div>

                                <!-- Secretaria -->
                                <div class="form-group col-md-4" id="container_secretaria">
                                    <label for="tbl_secretarias_id">Seleccione la Secretaria <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id">
                                        <?php echo $optionSecretarias; ?>
                                    </select>
                                </div>

                                <!-- Aportes -->
                                <div class="form-group col-md-4">
                                    <label for="modalAporteMunicipio">Aportes Municipio</label>
                                    <input type="text" class="form-control" id="modalAporteMunicipio" name="modalAporteMunicipio"
                                        placeholder="Ingrese el aporte del municipio">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="modalAporteDepartamento">Aportes Departamento</label>
                                    <input type="text" class="form-control" id="modalAporteDepartamento"
                                        name="modalAporteDepartamento" placeholder="Ingrese el aporte del departamento">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="modalNacion">Aportes Nación</label>
                                    <input type="text" class="form-control" id="modalNacion" name="modalNacion"
                                        placeholder="Ingrese el aporte de la nación">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="modalOtrosAportes">Otros Aportes</label>
                                    <input type="text" class="form-control" id="modalOtrosAportes"
                                        name="modalOtrosAportes" placeholder="Ingrese otros aportes">
                                </div>

                                <!-- Actores -->
                                <div class="form-group col-md-4">
                                    <label for="actores_id">Actor</label>
                                    <select class="form-control" id="actores_id" name="actores_id">
                                        <?php echo $optionActores; ?>
                                    </select>

                                </div>

                                <!-- Total Inversión -->
                                <div class="form-group col-md-4">
                                    <label for="valor_proyecto">Total Inversión</label>
                                    <input type="text" class="form-control" id="valor_proyecto" name="valor_proyecto"
                                        value="" disabled>
                                </div>

                                <!-- Observaciones -->
                                <div class="form-group col-md-8">
                                    <label for="observaciones">Observaciones <span class="text-danger">*</span></label>
                                    <textarea required placeholder="Ingrese una nueva observación del proyecto"
                                        class="form-control" id="observaciones" name="observaciones"></textarea>
                                </div>
                            </div>

                            <div class="form-row text-center">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                    <button style="border-radius: 12px;" type="button" class="btn btn-danger"
                                        data-dismiss="modal" onclick="location.reload();">Cancelar</button>
                                    <button type="button" class="btn btn-primary" onclick="guardarEdicion()">Guardar Cambios</button>
                                </div>
                            </div>
                        </form>
                        
                        <div id="contenedorObservaciones" name="contenedorObservaciones"></div>

                    </div>

                </div>
            </div>
        </div>

    </div>
    <?php include 'admin/include/gerenic_script.php'; ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script type="text/javascript" src="admin/js/ministerios_proyectos.js"></script>
    <?php include './admin/include/generic_dataTables.php'; ?>

</body>

</html>