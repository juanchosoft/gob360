<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
$permissions = PagePermissions::crudForCurrentPage();
include './admin/classes/Departamento.php';
include './admin/classes/Proyectos.php';
include './admin/classes/MunicipioSecretaria.php';
require './admin/include/georeferenciacion.php';
include './admin/classes/SecretariasMunicipio.php';
include './admin/classes/AccionSecretaria.php';

// Permisos
if (!$view) {
    require 'permiso_denegado.php';
    exit;
} */

// Validar los parámetros "mun" y "dep"
if (isset($_REQUEST['mun'], $_REQUEST['dep'], $_REQUEST['secretaria']) && !empty(trim($_REQUEST['mun'])) && !empty(trim($_REQUEST['dep'])) && !empty(trim($_REQUEST['secretaria']))) {
    $municipio = trim($_REQUEST['mun']);
    $departamento = trim($_REQUEST['dep']);
    $secretaria = trim($_REQUEST['secretaria']);

    // Información de secretarias y proyectos del municipio
    $secretariasMunicipioProyectos = MunicipioSecretaria::getProyectosPorSecretariaByMunicipioId(array('municipioId' => $municipio, 'secretariaId' => $secretaria));


    $informacionPorSecretaria = AccionSecretaria::getAll(array('id' => $secretaria, 'municipio' => $municipio));
} else { ?>
    <!-- <script type='text/javascript'>
    alert('Información enviada no es correcta');
    window.location =
        'municipios_secretarias.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&dep=<?php echo Util::getDepartamentoPrincipal(); ?>';
</script> -->
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
    <style>
        .table-responsive {
            display: flex;
            justify-content: center;
        }

        .table {
            width: auto;
            max-width: 90%;
            min-width: 1000px;
        }

        .table td:last-child {
            white-space: normal !important;
            word-wrap: break-word;
            max-width: 400px;
        }
    </style>
    <?php
    if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(Android|iPhone|iPad|iPod|Windows Phone)/i', $_SERVER['HTTP_USER_AGENT'])) {
        include './admin/include/menu_movil.php';
    } else {
        echo '<style>.menu-movil-container { display: none !important; }</style>';
    }
    ?>
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
                                                <h5 for="tbl_departamento_id">Departamento</h5>
                                                <!-- <label for="tbl_departamento_id">Departamento</label> -->
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
                                                        <div class="table-responsive px-3">
                                                            <table class="table table-bordered table-hover">

                                                                <thead class="thead-dark">
                                                                    <tr>
                                                                        <th>Editar</th>
                                                                        <th>ID Proyecto</th>
                                                                        <th>Nombre del Proyecto</th>
                                                                        <th>Observaciones</th>
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
                                                                            <td><?= htmlspecialchars($proyecto['observaciones']) ?></td>
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
                                                        </div>
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

                            <?php
                            $responseAccionSecretarias = $informacionPorSecretaria['output']['response'];
                            $isAccionSecretaria = $informacionPorSecretaria['output']['valid'];
                            ?>
                            <div>
                                <h5 class="mb-3">Acciones de la Secretaría</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">Ciudad</th>
                                                <th scope="col">Vereda</th>
                                                <th scope="col">Eje</th>
                                                <th scope="col">Factor de Inestabiliad</th>
                                                <th scope="col">Cantidad</th>
                                                <th scope="col">Medida</th>
                                                <th scope="col">Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($isAccionSecretaria && !empty($responseAccionSecretarias)): ?>
                                                <?php foreach ($responseAccionSecretarias as $item): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($item['id']) ?></td>
                                                        <td><?= htmlspecialchars($item['municipio']) ?></td>
                                                        <td><?= htmlspecialchars($item['nombre_vereda']) ?></td>
                                                        <td><?= htmlspecialchars($item['nombre_eje']) ?></td>
                                                        <td><?= htmlspecialchars($item['tipo']) ?></td>
                                                        <td><?= htmlspecialchars($item['valor']) ?></td>
                                                        <td><?= htmlspecialchars($item['tipo_medicion']) ?></td>
                                                        <td><?= htmlspecialchars($item['observaciones']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No hay acciones registradas para esta secretaría.</td>
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
    <style>
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        /* Hacer que la tabla use todo el ancho posible */
        .table {
            width: 100% !important;
            table-layout: fixed;
        }

        /* Ajuste de columnas */
        .table th,
        .table td {
            font-size: 0.75rem;
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;
            vertical-align: middle;
        }

        /* Evita que columnas como Nombre del Proyecto se desborden */
        .table th:nth-child(3),
        .table td:nth-child(3) {
            min-width: 200px;
            max-width: 350px;
        }

        /* Asegura que la tabla ocupe todo el ancho visible dentro del card-body */
        .card-body {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .table {
            table-layout: fixed;
            width: 100%;
        }

        .table th,
        .table td {
            word-break: break-word;
            white-space: normal;
            font-size: 0.75rem;
        }
    </style>


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