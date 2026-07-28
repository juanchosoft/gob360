<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
require_once './admin/classes/ImagenesHistorial.php';

$filtros = [
    'departamento_id' => $_GET['departamento_id'] ?? null,
    'municipio_id' => $_GET['municipio_id'] ?? null,
    'factor_id' => $_GET['factor_id'] ?? null,
    'fecha_inicial' => $_GET['fecha_inicial'] ?? null,
    'fecha_final' => $_GET['fecha_final'] ?? null,
];

$optionDep = ImagenesHistorial::getDepartamentos();
$santanderId = ImagenesHistorial::getSantanderId();
$defaultMunicipios = $santanderId ? ImagenesHistorial::getMunicipiosByDepartamento($santanderId) : [];
$optionFactores = ImagenesHistorial::getFactores();

$selectedDepartamento = $filtros['departamento_id'] ?? $santanderId;
$selectedMunicipio = $filtros['municipio_id'] ?? null;

$recordsAntes = ImagenesHistorial::getAntes($filtros);
$recordsDespues = ImagenesHistorial::getDespues($filtros);
?>


<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ navigation  ] start -->
    <?php
    include './admin/include/navbar.php';
    ?>
    <!-- [ navigation  ] end -->
    <!-- [ Header ] start -->
    <?php
    include './admin/include/header.php';
    ?>
    <!-- [ Header ] end -->
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
                                <h5 class="m-b-10">Historial Imagénes</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Historial Imagénes</a></li>
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

                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                            <h5 class="mb-0 text-center w-100">Historial de imágenes</h5>
                            <div class="card-header-right ml-auto">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card">
                                            <a href="#!">
                                                <span><i class="feather icon-maximize"></i> Maximizar</span>
                                                <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                                            </a>
                                        </li>
                                        <li class="dropdown-item minimize-card">
                                            <a href="#!">
                                                <span><i class="feather icon-minus"></i> Colapsar</span>
                                                <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                                            </a>
                                        </li>
                                        <li class="dropdown-item reload-card">
                                            <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                                        </li>
                                        <li class="dropdown-item close-card">
                                            <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mx-auto" style="max-width: 1000px;">
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Departamento</label>
                                        <select class="form-control" name="departamento_id" id="departamentoSelect" onchange="cargarMunicipios()">
                                            <option value="">Seleccione un departamento</option>
                                            <?php foreach ($optionDep as $dep): ?>
                                                <option value="<?= $dep['id']; ?>"
                                                    <?= $dep['id'] == $selectedDepartamento ? 'selected' : ''; ?>>
                                                    <?= $dep['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Municipio</label>
                                        <select class="form-control" name="municipio_id" id="municipioSelect">
                                            <option value="">Seleccione un municipio</option>
                                            <?php if (!empty($defaultMunicipios) && $selectedDepartamento == $santanderId): ?>
                                                <?php foreach ($defaultMunicipios as $municipio): ?>
                                                    <option value="<?= $municipio['codigo_muncipio']; ?>"
                                                        <?= $municipio['codigo_muncipio'] == $selectedMunicipio ? 'selected' : ''; ?>>
                                                        <?= $municipio['municipio']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Factor</label>
                                        <select class="form-control" name="factor_id">
                                            <option value="">Seleccione un factor</option>
                                            <?php foreach ($optionFactores as $factor): ?>
                                                <option value="<?= $factor['id']; ?>"
                                                    <?= isset($filtros['factor_id']) && $filtros['factor_id'] == $factor['id'] ? 'selected' : ''; ?>>
                                                    <?= $factor['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Fecha Inicial</label>
                                        <input type="date" class="form-control" name="fecha_inicial" value="<?= htmlspecialchars($_GET['fecha_inicial'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Fecha Final</label>
                                        <input type="date" class="form-control" name="fecha_final" value="<?= htmlspecialchars($_GET['fecha_final'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-12 text-center">
                                        <button type="submit" class="btn btn-primary">Filtrar</button>
                                    </div>
                                </div>
                            </form>





                            <div class="card mt-4">
                                <div class="card-header text-center">
                                    <h4>
                                        <?= (!empty($recordsAntes) && !empty($recordsAntes[0]['municipio']))
                                            ? htmlspecialchars($recordsAntes[0]['municipio']) . " - Antes"
                                            : "Municipio No Definido - Antes"; ?>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($recordsAntes)): ?>
                                        <div id="carouselAntes" class="carousel slide mx-auto" style="max-width: 600px;" data-bs-ride="carousel">
                                            <div class="carousel-inner">
                                                <?php foreach ($recordsAntes as $index => $record): ?>
                                                    <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                                                        <div class="text-center">
                                                            <p><strong>Vereda:</strong> <?= htmlspecialchars($record['vereda'] ?? 'No definida'); ?></p>
                                                            <p><strong>Fecha de Creación:</strong> <?= htmlspecialchars($record['dtcreate']); ?></p>
                                                            <!-- Cuadro de imágenes -->
                                                            <div class="d-flex flex-wrap justify-content-center gap-2 mx-auto" style="width: 200px; height: 200px; overflow: hidden;">
                                                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                                                    <?php if (!empty($record["foto$i"])): ?>
                                                                        <div style="width: 48%; height: 48%;">
                                                                            <img src="<?= htmlspecialchars($record["foto$i"]); ?>"
                                                                                alt="Foto <?= $i; ?>"
                                                                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 3px;">
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endfor; ?>
                                                            </div>
                                                            <p class="mt-3"><strong>Observaciones:</strong> <?= htmlspecialchars($record['observaciones']); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <!-- Controles del carrusel -->
                                            <button class="carousel-control-prev custom-carousel-control" type="button" data-bs-target="#carouselAntes" data-bs-slide="prev">
                                                <div class="control-box">
                                                    <span>&larr;</span>
                                                    <p>Anterior</p>
                                                </div>
                                            </button>
                                            <button class="carousel-control-next custom-carousel-control" type="button" data-bs-target="#carouselAntes" data-bs-slide="next">
                                                <div class="control-box">
                                                    <span>&rarr;</span>
                                                    <p>Siguiente</p>
                                                </div>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <p>No se encontraron imágenes de antes.</p>
                                    <?php endif; ?>
                                </div>
                            </div>


                            <!-- Estilos personalizados -->
                            <style>
                                /* Botones del carrusel personalizados */
                                .custom-carousel-control {
                                    background-color: transparent !important;
                                    border: none !important;
                                    width: auto;
                                    height: auto;
                                }

                                .custom-carousel-control .control-box {
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    justify-content: center;
                                    width: 50px;
                                    height: 50px;
                                    border: 1px solid black;
                                    border-radius: 5px;
                                    background-color: rgba(255, 255, 255, 0.8);
                                    color: black;
                                    font-size: 14px;
                                    font-weight: bold;
                                    cursor: pointer;
                                }

                                .custom-carousel-control .control-box:hover {
                                    background-color: rgba(0, 0, 0, 0.1);
                                }

                                .custom-carousel-control .control-box span {
                                    font-size: 18px;
                                }

                                .custom-carousel-control .control-box p {
                                    margin: 0;
                                    font-size: 12px;
                                }
                            </style>





                            <div class="card mt-4">
                                <div class="card-header text-center">
                                    <h4>
                                        <?= (!empty($recordsDespues) && !empty($recordsDespues[0]['municipio']))
                                            ? htmlspecialchars($recordsDespues[0]['municipio']) . " - Después"
                                            : "Municipio No Definido - Después"; ?>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($recordsDespues)): ?>
                                        <div id="carouselDespues" class="carousel slide mx-auto" style="max-width: 600px;" data-bs-ride="carousel">
                                            <div class="carousel-inner">
                                                <?php foreach ($recordsDespues as $index => $record): ?>
                                                    <div class="carousel-item <?= $index === 0 ? 'active' : ''; ?>">
                                                        <div class="text-center">
                                                            <p><strong>Fecha de Actualización:</strong> <?= htmlspecialchars($record['fecha_actualizacion'] ?? 'No disponible'); ?></p>
                                                            <!-- Contenedor para centrar imágenes -->
                                                            <div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mx-auto" style="width: 220px; height: 220px; overflow: hidden; position: relative;">
                                                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                                                    <?php if (!empty($record["foto_actualizada_$i"])): ?>
                                                                        <div style="width: 48%; height: 48%;">
                                                                            <img src="<?= htmlspecialchars($record["foto_actualizada_$i"]); ?>"
                                                                                alt="Foto Actualizada <?= $i; ?>"
                                                                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 3px;">
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endfor; ?>
                                                            </div>
                                                            <!-- Observaciones debajo de las imágenes -->
                                                            <p class="mt-3"><strong>Observaciones:</strong> <?= htmlspecialchars($record['observaciones_actualizacion'] ?? 'Sin observaciones'); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <!-- Controles del carrusel -->
                                            <button class="carousel-control-prev custom-carousel-control" type="button" data-bs-target="#carouselDespues" data-bs-slide="prev">
                                                <div class="control-box">
                                                    <span>&larr;</span>
                                                    <p>Anterior</p>
                                                </div>
                                            </button>
                                            <button class="carousel-control-next custom-carousel-control" type="button" data-bs-target="#carouselDespues" data-bs-slide="next">
                                                <div class="control-box">
                                                    <span>&rarr;</span>
                                                    <p>Siguiente</p>
                                                </div>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <p>No se encontraron imágenes de después.</p>
                                    <?php endif; ?>
                                </div>
                            </div>



                            <!-- Estilos personalizados -->
                            <style>
                                /* Botones del carrusel personalizados */
                                .custom-carousel-control {
                                    background-color: transparent !important;
                                    border: none !important;
                                    width: auto;
                                    height: auto;
                                }

                                .custom-carousel-control .control-box {
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    justify-content: center;
                                    width: 50px;
                                    height: 50px;
                                    border: 1px solid black;
                                    border-radius: 5px;
                                    background-color: rgba(255, 255, 255, 0.8);
                                    color: black;
                                    font-size: 14px;
                                    font-weight: bold;
                                    cursor: pointer;
                                }

                                .custom-carousel-control .control-box:hover {
                                    background-color: rgba(0, 0, 0, 0.1);
                                }

                                .custom-carousel-control .control-box span {
                                    font-size: 18px;
                                }

                                .custom-carousel-control .control-box p {
                                    margin: 0;
                                    font-size: 12px;
                                }
                            </style>









                            </>
                        </div>

                        <script>
                            function cargarMunicipios() {
                                const departamentoId = document.getElementById('departamentoSelect').value;
                                const municipioSelect = document.getElementById('municipioSelect');
                                municipioSelect.innerHTML = '<option value="">Cargando...</option>';

                                fetch(`getMunicipios.php?departamento_id=${departamentoId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                                        if (data.output.valid) {
                                            data.output.response.forEach(municipio => {
                                                municipioSelect.innerHTML += `<option value="${municipio.id}">${municipio.municipio}</option>`;
                                            });
                                        } else {
                                            municipioSelect.innerHTML = '<option value="">No se encontraron municipios</option>';
                                        }
                                    })
                                    .catch(() => {
                                        municipioSelect.innerHTML = '<option value="">Error al cargar municipios</option>';
                                    });
                            }
                        </script>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const carouselElement = document.querySelector("#carouselAntes");
                                if (carouselElement) {
                                    const carousel = new bootstrap.Carousel(carouselElement, {
                                        interval: 5000,
                                        ride: false
                                    });

                                    document.querySelector(".carousel-control-prev").addEventListener("click", () => {
                                        carousel.prev();
                                    });
                                    document.querySelector(".carousel-control-next").addEventListener("click", () => {
                                        carousel.next();
                                    });
                                }
                            });
                        </script>
                        <script src="assets/js/vendor-all.min.js"></script>
                        <script src="assets/js/plugins/bootstrap.min.js"></script>
                        <script src="assets/js/pcoded.min.js"></script>



</body>

</html>