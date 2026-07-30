<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Departamento.php';
include './admin/classes/Bienes.php';
require './admin/include/georeferenciacion.php';

// Validar los parámetros "mun" y "dep"
if (isset($_REQUEST['mun'], $_REQUEST['dep']) && !empty(trim($_REQUEST['mun'])) && !empty(trim($_REQUEST['dep']))) {
    $municipio = trim($_REQUEST['mun']);
    $departamento = trim($_REQUEST['dep']);
    // Información de secretarias Administrativa
    $secretariasMunicipioProyectos = Bienes::getAll(array('municipioId' => $municipio));
    $arrData = $secretariasMunicipioProyectos['output']['response'];
    $isvalidBienes = $secretariasMunicipioProyectos['output']['valid'];
} else { ?>
<script type='text/javascript'>
    alert('Información enviada no es correcta');
    window.location = 'dash_adminitrativa.php';
</script>
<?php


}

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

?>

<link href="assets/css/bienes_municipio_administrativa_gob360.css" rel="stylesheet">

<body class="gob360-municipal-assets">
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
                                <h5 class="m-b-10">Bienes Administrativos por Municipio</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Información administrativa / Inventario municipal</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- HERO VISUAL GOB360 -->
            <section class="g360-municipal-assets-hero" aria-label="Bienes administrativos por municipio GOB360">
                <div class="g360-municipal-assets-hero__grid">

                    <div>
                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-municipal-assets-hero__logo"
                        >
                    </div>

                    <div>
                        <div class="g360-municipal-assets-hero__eyebrow">
                            <i class="feather icon-map-pin"></i>
                            Inventario administrativo territorial
                        </div>

                        <h1 class="g360-municipal-assets-hero__title">
                            Bienes por Municipio
                        </h1>

                        <p class="g360-municipal-assets-hero__description">
                            Consulta el inventario administrativo del municipio,
                            incluyendo código de control, artículo, valor unitario,
                            dependencia, responsable y evidencias fotográficas.
                        </p>

                        <div class="g360-municipal-assets-hero__chips">
                            <span class="g360-chip g360-chip--success">
                                <i class="feather icon-check-circle"></i>
                                Consulta territorial
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-archive"></i>
                                Inventario detallado
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-image"></i>
                                Evidencias fotográficas
                            </span>
                        </div>
                    </div>

                    <div class="g360-municipal-assets-hero__visual" aria-hidden="true">
                        <div class="g360-mini-card">
                            <i class="feather icon-map"></i>
                            <span>Municipio</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-tag"></i>
                            <span>Control</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-user-check"></i>
                            <span>Responsable</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-camera"></i>
                            <span>Evidencia</span>
                        </div>
                    </div>

                </div>
            </section>

            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card g360-municipal-assets-card">

                        <div class="card-header">

                            <div class="flex-grow-1">
                                <div>
                                    <h5>Consulta municipal de bienes</h5>
                                    <p>Selecciona el territorio para actualizar el inventario administrativo.</p>
                                </div>
                            </div>

                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i
                                                        class="feather icon-maximize"></i> Maximizar</span><span
                                                    style="display:none"><i class="feather icon-minimize"></i>
                                                    Restaurar</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                        class="feather icon-minus"></i> Colapsar</span><span
                                                    style="display:none"><i class="feather icon-plus"></i>
                                                    Expandir</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i
                                                    class="feather icon-refresh-cw"></i> Recargar</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i
                                                    class="feather icon-trash"></i> Eliminar</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="g360-municipal-filter">
                                <div class="g360-municipal-filter__header">
                                    <span class="g360-municipal-filter__icon">
                                        <i class="feather icon-filter"></i>
                                    </span>

                                    <div>
                                        <h6>Filtro territorial</h6>
                                        <p>Selecciona departamento y municipio para cambiar la consulta.</p>
                                    </div>
                                </div>

                                <input type="hidden" name="op" id="op" />
                                <input type="hidden" name="id" id="id" />
                                <input type="hidden" name="filtro" id="filtro" value="no" />
                                <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="no" />

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <h5>Departamento</h5>
                                            <select onchange="DEPARTAMENTO.getMunicipios()" class="form-control"
                                                id="tbl_departamento_id" name="tbl_departamento_id">
                                                <?php echo $optionDep; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <h5>Municipio</h5>
                                            <select onchange="MUNICIPIO.updateUrlMunicipio(this)"
                                                class="form-control" id="tbl_municipio_id"
                                                name="tbl_municipio_id"></select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12" id="divConsolidado">
                            <div class="g360-assets-table-heading">
                                <div class="g360-assets-table-heading__content">
                                    <span class="g360-assets-table-heading__icon">
                                        <i class="feather icon-list"></i>
                                    </span>

                                    <div>
                                        <h5>Inventario administrativo municipal</h5>
                                        <p>Bienes, responsables, valores y fotografías disponibles.</p>
                                    </div>
                                </div>

                                <span class="g360-assets-table-heading__badge">
                                    <i class="feather icon-check-square"></i>
                                    Registros del municipio
                                </span>
                            </div>

                            <!-- Tabla de datos -->
                            <div class="table-responsive tabla-informacion tabla-scroll">
                                <table id="dynamictable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr class="border-1">
                                            <th>Municipio</th>
                                            <th>Secretaría</th>
                                            <th>Código de Control</th>
                                            <th>Calcomanía</th>
                                            <th>Nombre del Artículo</th>
                                            <th>Costo unitario</th>
                                            <th>Dependencia</th>
                                            <th>Cédula o NIT</th>
                                            <th>Responsable</th>
                                            <th>Evidencias</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        <?php if ($isvalidBienes && count($arrData) > 0): ?>
                                        <?php foreach ($arrData as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['nombre_municipio']) ?></td>
                                            <td><?= htmlspecialchars($item['nombre_secretaria']) ?></td>
                                            <td><?= htmlspecialchars($item['codigo_control']) ?></td>
                                            <td><?= htmlspecialchars($item['calcomania']) ?></td>
                                            <td><?= htmlspecialchars($item['nombre_articulo']) ?></td>
                                            <td><?= htmlspecialchars(number_format($item['costo_unitario'], 2)) ?></td>
                                            <td><?= htmlspecialchars($item['dependencia']) ?></td>
                                            <td><?= htmlspecialchars($item['cedula_o_nit']) ?></td>
                                            <td><?= htmlspecialchars($item['responsable']) ?></td>
                                            <td class="text-primary">
                                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                                <?php if (!empty($item["img$i"])): ?>
                                                <a href="<?= htmlspecialchars($item["img$i"]) ?>" target="_blank"
                                                    rel="noopener noreferrer"
                                                    title="Imagen <?= $i ?>"
                                                    aria-label="Abrir imagen <?= $i ?>">
                                                    <i class="feather icon-image"></i>

                                                </a>
                                                <?php endif; ?>
                                                <?php endfor; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="10">
                                                <div class="g360-empty-assets">
                                                    <i class="feather icon-inbox"></i>
                                                    <strong>No se encontraron bienes registrados</strong>
                                                    <span>Selecciona otro municipio o verifica la información disponible.</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                            </div>

                        </div>

                    </div>
                </div>
                <!-- [ sample-page ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>

    <!-- Warning Section Ends -->

    <?php include 'admin/include/gerenic_script.php'; ?>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/municipios.js"></script>
    <script>
        MUNICIPIO.init();

        function handlePolygonClick(element) {
            const url = element.getAttribute('data-url'); // Obtén la URL del atributo data-url
            if (url) {
                window.location.href = url; // Redirige al enlace
            } else {
                console.error('No se encontró una URL válida.');
            }
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
            tabLinks.forEach(tab => {
                tab.addEventListener('click', function(event) {
                    event.preventDefault();
                    tabLinks.forEach(link => link.classList.remove('active'));
                    const tabPanes = document.querySelectorAll('.tab-pane');
                    tabPanes.forEach(pane => pane.classList.remove('show', 'active'));
                    this.classList.add('active');
                    const targetPane = document.querySelector(this.getAttribute('href'));
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }
                });
            });
        });
    </script>
</body>

</html>