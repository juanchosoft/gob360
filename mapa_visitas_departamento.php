<?php
include './admin/include/head.php';
function getUrl()
{
    $port = $_SERVER["SERVER_PORT"];
    $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
    $url = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $nameServer,
        $_SERVER['REQUEST_URI']
    );
    $final =  str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists == !false) {
        $final =  substr($final, 0, $exists);
        return $final;
    } else {
        return $final;
    }
}

require_once './admin/include/generic_classes.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
include './admin/db/colores.php';
include './admin/classes/Main.php';
include './admin/classes/Detalle.php';
include './admin/classes/Cuenta.php';
include './admin/classes/Cuentapro.php';
include './admin/classes/Secreinversion.php';
include './admin/classes/Munnovisitados.php';




// Obtener permisos
$permissions = PagePermissions::crudForCurrentPage();

// Validación de permiso de visualización
/* if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
} */

//informacion del mail
$arr = Main::getDataMain(null);
$isvalid = $arr['output']['valid'];
$visitas = $arr['output']['visitas'];
$apoyos = $arr['output']['apoyos'];
$municipios = $arr['output']['municipios'];
$veredas = $arr['output']['veredas'];
$provincia = $arr['output']['provincia'];
$porcentaje_veredas = $arr['output']['porcentaje_veredas'];
$porcentaje_municipios = $arr['output']['porcentaje_municipios'];
$inversionsec = $arr['output']['inversionsec'];
$valorproyectos = $arr['output']['valorproyectos'];
$secretaria = $arr['output']['secretaria'];
$sumaproyectos = $arr['output']['sumaproyectos'];
$visitarpendiente = 87 - $municipios;


$departamento = new Departamento();
$santander = $departamento->getAll(["id" => 21]);
$santander = $santander["output"]["response"]["0"];
$code = null;
$mapa = null;

if (isset($_GET['depto_id']) && in_array($_GET['depto_id'], [1, 12, 21])) {
    switch ($_GET['depto_id']) {

        case '21':
            $code = $santander["codigo_departamento"];
            $mapa = "admin/mapa-santander/mapa.php";
            break;
    }
}
if (!is_null($code)) {
    $arr = Ciudad::getAll(array('codigo_departamento' => $code));
    $finalMunicipios = $arr['output']['response'];
    $arrApoyoDep = Ciudad::getApoyoByCodigoDepartamento(array('codigo_departamento' => $code));
}
?>

<body class="dashboard-body">
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
                                <h5 class="m-b-10">Dashboard </h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!"> Información General </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Indicadores uno al lado del otro - Estilo limpio y atractivo -->
            <div class="row mb-4 g-3">
                <!-- Total Visitas Departamento -->
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border h-100 text-center py-3">
                        <div class="card-body">
                            <i class="icon feather icon-server text-c-green mb-2"
                                style="cursor: pointer; font-size: 60px;"
                                data-bs-toggle="modal" data-bs-target="#modalMunicipiosVisitados"
                                title="Ver municipios visitados"></i>

                            <h5 class="fw-bold text-uppercase mb-1" style="font-size:14px;">Total Visitas Departamento</h5>

                            <h3 class="fw-bold mb-0"><?php echo $visitas; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Visitas realizadas a Municipios -->
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border h-100 text-center py-3">
                        <div class="card-body">
                            <i style="cursor: pointer; font-size: 60px;" class="icon feather icon-server text-c-red display-5 mb-2"
                                data-bs-toggle="modal" data-bs-target="#exampleModalLong1"
                                title="Ver visitas a municipios"></i>
                            <h5 class="fw-bold text-uppercase mb-1" style="font-size:14px;">Visitas a Municipios</h5>
                            <h3 class="fw-bold mb-0"><?php echo $municipios; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Municipios Restantes -->
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border h-100 text-center py-3">
                        <div class="card-body">
                            <i class="icon feather icon-navigation text-c-blue display-5 mb-2"
                                data-bs-toggle="modal" data-bs-target="#exampleModalLong3"
                                style="cursor: pointer; font-size: 60px;" title="Ver municipios restantes"></i>
                            <h5 class="fw-bold text-uppercase mb-1" style="font-size:14px;">Municipios Restantes</h5>
                            <h3 class="fw-bold mb-0"><?php echo $visitarpendiente; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Metas Plan Desarrollo -->
                <div class="col-md-6 col-xl-3">
                    <div class="card shadow-sm border h-100 text-center py-3">
                        <div class="card-body">
                            <a href="plan_desarrollo.php" title="Ir a Metas Plan Desarrollo">
                                <i style="cursor: pointer; font-size: 60px;" class="icon feather icon-file-text text-c-yellow display-5 mb-2 d-block"></i>
                            </a>
                            <h5 class="fw-bold text-uppercase mb-1" style="font-size:14px;">Metas Plan Desarrollo</h5>
                            <h3 class="fw-bold mb-0"></h3>
                        </div>
                    </div>
                </div>
            </div>



            <!-- NUEVO ROW CON TODO EL CONTENIDO -->
            <div class="row">
                <!-- Mapa -->
                <div class="col-md-7">
                    <div class="card h-100">
                        <div class="card-header text-center">
                            <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                <i class="icon feather icon-map text-primary mb-2" style="font-size: 36px;"></i>
                                <h5 class="fw-bold mb-0" style="font-size: 25px;">Mapa Santander</h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <section class="content" style="<?php echo isset($_GET["route_map"]) ? "padding: 0rem !important" : "" ?>">
                                <!-- <div class="bonotera mb-2">
                        <button id="btnAumentar" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-search-plus text-dark"></i>
                        </button>
                        <button id="btnReducir" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-search-minus text-dark"></i>
                        </button>
                    </div> -->
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12" style="position: static; overflow-x: auto;">
                                            <div id="contenidoTransformado" class="contenido-transformado">

                                                <div class="cuerpoMapa w-12">
                                                    <?php include_once "admin/classes/rango_colores_visita_departamento.php"; ?>
                                                    <?php if (!is_null($mapa)) : ?>

                                                        <?php if ($_GET['depto_id'] == 1) : ?>
                                                            <div class="antioquia munis"><?php include_once "admin/mapa/mapa.php"; ?></div>
                                                        <?php elseif ($_GET['depto_id'] == 12) : ?>
                                                            <div class="choco munis"><?php include_once "admin/mapa-choco/choco.php"; ?></div>
                                                        <?php else : ?>

                                                            <div class="santander munis"><?php require_once "admin/mapa-santander/mapa_gobernador.php"; ?></div>
                                                        <?php endif ?>

                                                    <?php endif ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <!-- Gráficas -->
                <div class="col-md-5">
                    <div class="card h-100">
                        <div class="card-header text-center">
                            <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                <i class="icon feather icon-bar-chart-2 text-primary mb-1" style="font-size: 36px;"></i>
                                <h5 class="fw-bold mb-0" style="font-size: 25px;">Estadísticas de Visitas</h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="text-center">Visitas realizadas a provincias</h6>
                                <div id="containerProvincias" style="height: 250px; width: 100%;"></div>
                            </div>
                            <div>
                                <h6 class="text-center">Visitas por mes a municipios</h6>
                                <div id="containerMunicipios" style="height: 250px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal Municipios Visitados -->
            <div id="modalMunicipiosVisitados" class="modal fade" tabindex="-1"
                aria-labelledby="modalTitle" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Municipios Visitados</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Cerrar"></button>
                        </div>
                        <?php
                        $arr = Detalle::getAll(null);
                        $isvalid = $arr['output']['valid'] ?? false;
                        $data = $arr['output']['response'] ?? [];
                        ?>
                        <div class="modal-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Provincia</th>
                                        <th>Municipio</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyMunicipiosVisitados">
                                    <?php if ($isvalid && !empty($data)): ?>
                                        <?php foreach ($data as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['provincia']); ?></td>
                                                <td><?= htmlspecialchars($item['municipio']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2">No hay datos disponibles.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <nav>
                                <ul class="pagination justify-content-center" id="paginacionMunicipios"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal de Cantidad de Visitas -->
        <div id="exampleModalLong1" class="modal fade" tabindex="-1"
            aria-labelledby="exampleModalLongTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Cantidad de
                            visitas a municipios</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <?php
                    //Información de visitas muncipios
                    $arrVisitasMunicipios = Cuenta::getAll(null);
                    $isvalid = $arrVisitasMunicipios['output']['valid'];
                    $arrVisitasMunicipios = $arrVisitasMunicipios['output']['response'];
                    ?>

                    <div class="modal-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Municipio</th>
                                    <th scope="col">Veces Visitado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (isset($arrVisitasMunicipios) && isset($isvalid) && $isvalid) {
                                    foreach ($arrVisitasMunicipios as $item) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['municipio']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['CuentaDeid']); ?>
                                            </td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="2">No hay datos disponibles.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    </div>

    <!-- Modal de Municipios Restantes -->
    <div id="exampleModalLong3" class="modal fade" tabindex="-1"
        aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Municipios
                        Restantes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    //Municipios Restantes por visitar
                    $arrMunicipiosRestante = Munnovisitados::getAll(null);
                    $isvalid = $arrMunicipiosRestante['output']['valid'];
                    $arrMunicipiosRestante = $arrMunicipiosRestante['output']['response'];
                    ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Municipio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($arrMunicipiosRestante) && isset($isvalid) && $isvalid) {
                                foreach ($arrMunicipiosRestante as $item) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['municipio']); ?>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td>No hay datos disponibles.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    </div>
    <!-- 


                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table tabla-colores">
                                <thead>
                                    <tr>
                                        <th>Desde</th>
                                        <th>hasta</th>
                                        <th>Color</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>
                                            <div class="color-circle"></div>
                                        </td> 
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>
                                            <div class="color-circle color-red"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>4</td>
                                        <td>
                                            <div class="color-circle color-yellow"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>6</td>
                                        <td>
                                            <div class="color-circle color-blue"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>7</td>
                                        <td>+</td>
                                        <td>
                                            <div class="color-circle color-green"></div>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> -->


    <!-- Warning Section Ends -->

    <script>
        document.getElementById("btnAumentar").onclick = function() {
            aumentarTransform();
        };
        document.getElementById("btnReducir").onclick = function() {
            reducirTransform();
        };

        function aumentarTransform() {
            var elemento = document.getElementById("contenidoTransformado");
            var escalaActual = parseFloat(window.getComputedStyle(elemento).getPropertyValue("transform").split(
                ",")[3]);
            var nuevaEscala = escalaActual + 0.1; // Aumentar la escala en 0.1
            elemento.style.transform = "scale(" + nuevaEscala + ")";
        }

        function reducirTransform() {
            var elemento = document.getElementById("contenidoTransformado");
            var escalaActual = parseFloat(window.getComputedStyle(elemento).getPropertyValue("transform").split(
                ",")[3]);
            var nuevaEscala = escalaActual - 0.1; // Reducir la escala en 0.1
            if (nuevaEscala >= 0.1) { // Evitar escala negativa
                elemento.style.transform = "scale(" + nuevaEscala + ")";
            }
        }
    </script>
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- prism Js -->
    <script src="assets/js/plugins/prism.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>

    <script type="text/javascript" src="admin/js/graficos_mapa.js"></script>
    <script src="admin/js/estado_general.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    </script>
    <style>
        .santander path:hover,
        .santander polygon:hover {
            transform: none !important;
            filter: none !important;
            stroke: none !important;
            fill: inherit !important;
            pointer-events: auto !important;
        }
    </style>


    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const rowsPerPage = 10; // 👉 cantidad de filas por página
        const tbody = document.getElementById("tbodyMunicipiosVisitados");
        if (!tbody) return; // seguridad por si el modal no está

        const rows = Array.from(tbody.querySelectorAll("tr"));
        const pagination = document.getElementById("paginacionMunicipios");
        let currentPage = 1;

        function displayPage(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.forEach((row, i) => {
                row.style.display = (i >= start && i < end) ? "" : "none";
            });

            // Actualiza estado activo de los botones
            const pageLinks = pagination.querySelectorAll(".page-item");
            pageLinks.forEach(li => li.classList.remove("active"));
            if (pageLinks[page - 1]) pageLinks[page - 1].classList.add("active");
        }

        function setupPagination() {
            pagination.innerHTML = "";
            const pageCount = Math.ceil(rows.length / rowsPerPage);
            for (let i = 1; i <= pageCount; i++) {
                const li = document.createElement("li");
                li.classList.add("page-item");
                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                li.addEventListener("click", e => {
                    e.preventDefault();
                    currentPage = i;
                    displayPage(currentPage);
                });
                pagination.appendChild(li);
            }
        }

        setupPagination();
        displayPage(currentPage);
    });
    </script>



</body>

</html>