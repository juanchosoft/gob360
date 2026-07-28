<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';


// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
//Validación
/* if (!$view) {
    require 'permiso_denegado.php';
} */

include './admin/classes/VisitasgAspas.php';
include './admin/classes/Departamento.php';
include './admin/classes/Linea.php';
include './admin/classes/Estrategia.php';

//Información de Vistas
$arr = VisitasgAspas::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$modulo = 'Primera Dama - Aspas';

// Información de línea select
$lineas = Linea::getAll(null);
$isValidLineas = $lineas['output']['valid'] ?? false;
$lineasResponse = $lineas['output']['response'] ?? [];
$optionLineas = "";
foreach ($lineasResponse as $linea) {
    $optionLineas .= "<option value='" . $linea['id'] . "'>" . $linea['nombre'] . "</option>";
}

// Información de estrategia select
$estrategias = Estrategia::getAll(null);
$isValidEstrategias = $estrategias['output']['valid'] ?? false;
$estrategiasResponse = $estrategias['output']['response'] ?? [];
$optionEstrategias = "";
foreach ($estrategiasResponse as $estrategia) {
    $optionEstrategias .= "<option value='" . $estrategia['id'] . "'>" . $estrategia['nombre'] . "</option>";
}

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
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
                                                <h5 class="m-b-10">Cuadro detalle visitas gestora social en los
                                                    municipios - Aspas </h5>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Primera dama - Aspas</a></li>
                                                <li class="breadcrumb-item"><a href="#!">Cuadro control actividades</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="contenedor">
                                <div class="contenido">
                                    <div class="card">
                                        <h5 style="color:#374d5a; font-size: 16px " class="card-header"><i
                                                class="feather icon-list"></i> Tabla de Acciones</h5>

                                        <div class="card-body table-border-style">
                                            <div class="col-12 ">
                                                <div class="table-responsive">
                                                    <table id="dynamictable" class="table table-striped table-bordered"
                                                        style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th>Ver</th>
                                                                <th>Provincia</th>
                                                                <th>Municipio</th>
                                                                <th>Población Impactada</th>
                                                                <th>Inversión</th>
                                                                <th>Linea</th>
                                                                <th>Estrategia</th>
                                                                <th>Nombre</th>
                                                                <th>Actividad</th>
                                                                <th>Fecha</th>
                                                                <th>Link</th>
                                                                <th>Imagen</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if ($isvalid && !empty($arr)): ?>
                                                                <?php foreach ($arr as $item): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <form action="reporte_visitag_aspas.php" method="POST"
                                                                                target="_blank" style="display:inline;">
                                                                                <input type="hidden" id="reporte" name="reporte"
                                                                                    value="<?= htmlspecialchars($item['id']); ?>">
                                                                                <button type="submit"
                                                                                    class="btn btn-sm btn-primary" title="Ver">
                                                                                    <i class="feather icon-eye"></i>
                                                                                </button>
                                                                                <button style="margin-top: 10px;"
                                                                                    type="button"
                                                                                    class="btn btn-sm btn-warning"
                                                                                    title="Editar"
                                                                                    onclick="VISITASG.editData(<?= $item['id'] ?>)">
                                                                                    <i class="feather icon-edit"></i>
                                                                                </button>

                                                                            </form>
                                                                        </td>

                                                                        <td><?= htmlspecialchars($item['provincia']); ?></td>
                                                                        <td><?= htmlspecialchars($item['municipio']); ?></td>
                                                                        <td><?= htmlspecialchars($item['poblacion']); ?></td>
                                                                        <td><?= htmlspecialchars($item['inversion']); ?></td>
                                                                        <td><?= htmlspecialchars($item['linea_nombre'] ?? ''); ?></td>
                                                                        <td><?= htmlspecialchars($item['estrategia_nombre'] ?? ''); ?></td>
                                                                        <td><?= htmlspecialchars($item['campana']); ?></td>
                                                                        <td><?= htmlspecialchars($item['actividad']); ?></td>
                                                                        <td><?= htmlspecialchars($item['date']); ?></td>
                                                                        <td><?php if (!empty($item['link'])): ?>
                                                                                <button type="button" class="btn btn-sm btn-danger"
                                                                                    title="Ver"
                                                                                    onclick="window.open('<?= htmlspecialchars($item['link']); ?>', '_blank')">
                                                                                    <i class="fas fa-eye"></i>
                                                                                </button>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td class="text-primary">
                                                                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                                                                <?php if (!empty($item["foto$i"])): ?>
                                                                                    <a href="<?= htmlspecialchars($baseImgUrl . $item["foto$i"]) ?>"
                                                                                        target="_blank" title="Imagen <?= $i ?>">
                                                                                        <i class="fas fa-images"></i>
                                                                                    </a>
                                                                                <?php endif; ?>
                                                                            <?php endfor; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <tr>
                                                                    <td colspan="8" class="text-center">No hay datos
                                                                        disponibles</td>
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

            </div>
            <!-- Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <form id="editForm" class="w-100">
                        <div class="modal-content" style="    width: 60%; margin: 0 auto;">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel" style="display: flex; align-items: center; gap: 10px; color:rgb(255, 255, 255);">
                                    <i class="fas fa-edit" style="color:rgb(255, 255, 255);"></i> Editar Visita
                                </h5>

                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" id="id" name="id">

                                <div class="form-group" style="text-align: center; margin-top: 20px;">
                                    <label id="tbl_departamento_id-label" for="tbl_departamento_id">
                                        <i class="fas fa-map-marked-alt"></i> Departamento
                                    </label>
                                    <select class="form-control ocultar-select" id="tbl_departamento_id" name="tbl_departamento_id">
                                        <?php echo $optionDep; ?>
                                    </select>
                                </div>


                                <div class="form-group" style="text-align: center;">
                                    <label id="tbl_municipio_id-label" for="tbl_municipio_id">
                                        <i class="fas fa-map-pin"></i> Municipio
                                    </label>
                                    <select
                                        id="tbl_municipio_id"
                                        name="tbl_municipio_id"
                                        onchange="DEPARTAMENTO.getVeredasByMunicipioId();">
                                    </select>
                                </div>


                                <div class="form-group" style="text-align: center;">
                                    <label id="provincia-label" for="provincia">
                                        <i class="fas fa-map"></i> Provincia
                                    </label>
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


                                <div class="form-group" style="text-align: center;">
                                    <label id="poblacion-label" for="poblacion">
                                        <i class="fas fa-users"></i> Población Impactada
                                    </label>
                                    <input type="text" class="form-control" id="poblacion" name="poblacion">
                                </div>


                                <div class="form-group text-center" id="desc_actividad-container">
                                    <label id="desc_actividad-label" for="desc_actividad">
                                        <i class="fas fa-align-left"></i> Descripción Actividad
                                    </label>
                                    <textarea class="form-control form-control-desc" id="desc_actividad" name="desc_actividad" rows="5"></textarea>
                                </div>

                                <div class="form-group" style="text-align: center;">
                                    <label id="inversion-label" for="inversion">
                                        <i class="fas fa-dollar-sign"></i> Inversión Estimada
                                    </label>
                                    <input type="text" class="form-control" id="inversion" name="inversion">
                                </div>

                                <div class="form-group" style="text-align: center;">
                                    <label id="linea-label" for="linea">
                                        <i class="fas fa-stream"></i> Línea
                                    </label>
                                    <select class="form-control" id="tbl_linea_id" name="tbl_linea_id" style="width: 220px; margin: 0 auto;">
                                        <option value="">Seleccione</option>
                                        <?php echo $optionLineas; ?>
                                    </select>
                                </div>


                                <div class="form-group" style="text-align: center;">
                                    <label id="estrategia-label" for="estrategia">
                                        <i class="fas fa-lightbulb"></i> Estrategia
                                    </label>
                                    <select class="form-control" id="tbl_estrategia_id" name="tbl_estrategia_id" style="width: 220px; margin: 0 auto;">
                                        <option value="">Seleccione</option>
                                        <?php echo $optionEstrategias; ?>
                                    </select>
                                </div>


                                <div class="form-group" style="text-align: center;">
                                    <label id="campana-label" for="campana">
                                        <i class="fas fa-bullhorn"></i> Nombre
                                    </label>
                                    <select class="form-control" id="campana" name="campana">
                                        <option value="Seleccione">Seleccione</option>
                                        <option value="Niños al estadio">Niños al estadio</option>
                                        <option value="Niños al cine">Niños al cine</option>
                                        <option value="Niños al teatro">Niños al teatro</option>
                                        <option value="Es tiempo de aprender">Es tiempo de aprender</option>
                                        <option value="Niños al estadio - Optometría">Niños al estadio - Optometría</option>
                                        <option value="Metale mente">Metale mente</option>
                                    </select>
                                </div>


                                <div class="form-group" style="text-align: center;">
                                    <label id="actividad-label" for="actividad">
                                        <i class="fas fa-tasks"></i> Actividad
                                    </label>
                                    <input type="text" class="form-control" id="actividad" name="actividad">
                                </div>

                                <div class="form-group" style="text-align: center;">
                                    <label id="link-label" for="link">
                                        <i class="fas fa-link"></i> Link Mediático
                                    </label>
                                    <input type="text" class="form-control" id="link" name="link">
                                </div>


                                <div class="form-row" style="margin-left: 120px;">
                                    <div class="form-group col-md-6">
                                        <label for="foto1">Editar foto 1</label><br>
                                        <img id="preview-foto1" src="" alt="Foto 1" style="max-width: 100px; max-height: 100px; display: none;">
                                        <iframe id="ifm1" name="ifm1" src="upload.php?foto=foto1" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="foto2">Editar foto 2</label><br>
                                        <img id="preview-foto2" src="" alt="Foto 2" style="max-width: 100px; max-height: 100px; display: none;">
                                        <iframe id="ifm2" name="ifm2" src="upload.php?foto=foto2" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="foto3">Editar foto 3</label><br>
                                        <img id="preview-foto3" src="" alt="Foto 3" style="max-width: 100px; max-height: 100px; display: none;">
                                        <iframe id="ifm3" name="ifm3" src="upload.php?foto=foto3" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="foto4">Editar foto 4</label><br>
                                        <img id="preview-foto4" src="" alt="Foto 4" style="max-width: 100px; max-height: 100px; display: none;">
                                        <iframe id="ifm4" name="ifm4" src="upload.php?foto=foto4" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                                    </div>
                                </div>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-primary" onclick="VISITASG.saveData();">Guardar</button>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'admin/include/gerenic_script.php'; ?>
    <!-- Required Js -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="admin/js/cuadro_control_visitasg_aspas.js"></script>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <!-- prism Js -->
    <script src="assets/js/plugins/prism.js"></script>
    <script src="admin/js/departamentoDama.js"></script>
    <script src="admin/js/detalle_visitasg_aspas.js"></script>
    <script>
        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 1000);
    </script>
    <?php include './admin/include/generic_dataTables.php'; ?>

</body>

</html>