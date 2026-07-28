<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Departamento.php';
include './admin/classes/Proyectos.php';
include './admin/classes/MunicipioSecretaria.php';
require './admin/include/georeferenciacion.php';
// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
/* if (!$view) {
    require 'permiso_denegado.php';
    exit;
} */

// Validar los parámetros "mun" y "dep"
if (isset($_REQUEST['mun'], $_REQUEST['dep'], $_REQUEST['pilar']) && !empty(trim($_REQUEST['mun'])) && !empty(trim($_REQUEST['dep'])) && !empty(trim($_REQUEST['pilar']))) {
    $municipio = trim($_REQUEST['mun']);
    $departamento = trim($_REQUEST['dep']);
    $pilar = trim($_REQUEST['pilar']);

    // Información de secretarias y proyectos del municipio
    $secretariasMunicipioProyectos = MunicipioSecretaria::getProyectosPorSecretariaByMunicipioId(array('municipioId' => $municipio));
} else { ?>
    <script type='text/javascript'>
        alert('Información enviada no es correcta');
        window.location =
            'municipios_secretarias.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&dep=<?php echo Util::getDepartamentoPrincipal(); ?>';
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
                                <h5 class="m-b-10">Municipio secretaría</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Resumen secretaría / Municipio secretaría</a>
                                </li>
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

                            <div class="col-sm-12">
                                <div class="card-body">

                                    <input type="hidden" name="op" id="op" />
                                    <input type="hidden" name="id" id="id" />
                                    <input type="hidden" name="filtro" id="filtro" value="no" />
                                    <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="no" />
                                    <div class="row">

                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <h5>Departamento</h5>
                                                <select onchange=" DEPARTAMENTO.getMunicipios()" class="form-control"
                                                    id="tbl_departamento_id" name="tbl_departamento_id">
                                                    <?php echo $optionDep; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
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
                                                    class="feather icon-trash"></i> Remover</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12" id="divConsolidado">
                            <h5 class="mb-3">Secretarias</h5>

                            <?php if ($secretariasMunicipioProyectos['output']['valid']): ?>
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                                            <?php
                                            $secretarias = $secretariasMunicipioProyectos['output']['response']['secretarias'];
                                            foreach ($secretarias as $index => $secretaria): ?>
                                                <li class="nav-item">
                                                    <a class="nav-link <?= $index === 0 ? 'active' : '' ?>"
                                                        id="tab-<?= htmlspecialchars($secretaria['id']) ?>" data-toggle="pill"
                                                        href="#content-<?= htmlspecialchars($secretaria['id']) ?>" role="tab"
                                                        aria-controls="content-<?= htmlspecialchars($secretaria['id']) ?>"
                                                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                                        <?= htmlspecialchars($secretaria['nombre']) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>

                                        <div class="tab-content" id="myTabContent">
                                            <?php
                                            $proyectos = $secretariasMunicipioProyectos['output']['response']['proyectos'];
                                            foreach ($secretarias as $index => $secretaria):
                                                // Filtrar los proyectos por la secretaría actual
                                                $filteredProyectos = array_filter($proyectos, fn($p) => $p['tbl_secretarias_id'] == $secretaria['id']);
                                            ?>
                                                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>"
                                                    id="content-<?= htmlspecialchars($secretaria['id']) ?>" role="tabpanel"
                                                    aria-labelledby="tab-<?= htmlspecialchars($secretaria['id']) ?>">

                                                    <?php if (!empty($filteredProyectos)): ?>
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Editar</th>
                                                                    <th>ID Proyecto</th>
                                                                    <th>Nombre del Proyecto</th>
                                                                    <th>Valor</th>
                                                                    <th>Porcentaje de Ejecución</th>
                                                                    <th>Fecha de Entrega</th>
                                                                    <th>Estado</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($filteredProyectos as $proyecto): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                                title="Editar"
                                                                                onclick="location.href='detalle_proyectos_Secretarias.php?id=<?= htmlspecialchars($proyecto['tbl_proyecto_id'], ENT_QUOTES, 'UTF-8') ?>&nombre=<?= htmlspecialchars($proyecto['proyecto'], ENT_QUOTES, 'UTF-8') ?>'">
                                                                                <i class="feather icon-edit"></i>
                                                                            </button>
                                                                        </td>
                                                                        <td><?= htmlspecialchars($proyecto['tbl_proyecto_id']) ?></td>
                                                                        <td><?= htmlspecialchars($proyecto['proyecto']) ?></td>
                                                                        <td><?= htmlspecialchars(number_format($proyecto['valor_proyecto'], 2)) ?>
                                                                        </td>
                                                                        <td><?= htmlspecialchars($proyecto['porcentaje_ejecucion']) ?>%
                                                                        </td>
                                                                        <td><?= htmlspecialchars($proyecto['fecha_entrega']) ?></td>
                                                                        <td><?= htmlspecialchars($proyecto['estado']) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>

                                                    <?php else: ?>
                                                        <p>No hay proyectos disponibles para esta secretaría.</p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p>No hay datos disponibles.</p>
                            <?php endif; ?>

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