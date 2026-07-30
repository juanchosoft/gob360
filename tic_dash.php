<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
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
require './admin/classes/Departamento.php';
include './admin/db/colores.php';
include './admin/classes/MainTic.php';

// Obtener permisos
/* $permissions = PagePermissions::crudForCurrentPage();

// Validación de permiso de visualización
if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
} */


// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}


$codigoMunicipio = $_REQUEST['mun'];
$opcionFiltro = $_REQUEST['opcion'];
$parametrosTic = ['codigoMunicipio' => $codigoMunicipio, 'departamentoId' => Util::getDepartamentoPrincipal(), 'opcion' => $opcionFiltro];

//informacion del main
$arr = MainTic::getDataMain($parametrosTic);
$isvalid = $arr['output']['valid'];
$robotica = $arr['output']['robotica'];
$institucion = $arr['output']['institucion'];
$alumno = $arr['output']['alumno'];
$laboratorio = $arr['output']['laboratorio'];
$inversionsec = $arr['output']['inversionsec'];
$valorproyectos = $arr['output']['valorproyectos'];
$secretaria = $arr['output']['secretaria'];
$sumaproyectos = $arr['output']['sumaproyectos'];
$santander =  $arr['output']['response'];
?>

<link href="assets/css/dashboard_tic_gob360.css" rel="stylesheet">

<body class="gob360-tic-dashboard">

    
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
                                <h5 class="m-b-10">Analítica de la Secretaría TIC</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!"> Información general de la Secretaría TIC</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HERO VISUAL GOB360 -->
            <section class="g360-tic-dashboard-hero" aria-label="Dashboard TIC GOB360">
                <div class="g360-tic-dashboard-hero__grid">

                    <div>
                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-tic-dashboard-hero__logo"
                        >
                    </div>

                    <div>
                        <div class="g360-tic-dashboard-hero__eyebrow">
                            <i class="feather icon-cpu"></i>
                            Analítica de transformación digital
                        </div>

                        <h1 class="g360-tic-dashboard-hero__title">
                            Dashboard TIC
                        </h1>

                        <p class="g360-tic-dashboard-hero__description">
                            Consulta la distribución territorial de kits de
                            robótica, computadores, laboratorios de innovación,
                            inversión y proyectos tecnológicos de la Secretaría TIC.
                        </p>

                        <div class="g360-tic-dashboard-hero__chips">
                            <span class="g360-chip g360-chip--success">
                                <i class="feather icon-check-circle"></i>
                                Información consolidada
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-filter"></i>
                                Filtro municipal
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-map"></i>
                                Mapa interactivo
                            </span>
                        </div>
                    </div>

                    <div class="g360-tic-dashboard-hero__visual" aria-hidden="true">
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
                            <i class="feather icon-dollar-sign"></i>
                            <span>Inversión</span>
                        </div>
                    </div>

                </div>
            </section>

            <div class="row">
                <div class="col-12">
                    <div class="card g360-filter-card">
                        <div class="card-header">
                            <div>
                                <h4><i class="feather icon-filter mr-2"></i>Filtro territorial</h4>
                                <p>Selecciona departamento y municipio para actualizar los indicadores.</p>
                            </div>
                        </div>

                        <div class="card-body">
                            <input type="hidden" name="op" id="op" />
                            <input type="hidden" name="id" id="id" />
                            <input type="hidden" name="filtro" id="filtro" value="vereda" />
                            <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="si" />

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                                        <select onchange="DEPARTAMENTO.getMunicipios();" class="form-control"
                                            style="width: 100%;" id="tbl_departamento_id"
                                            name="tbl_departamento_id">
                                            <?php echo $optionDep; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                                        <select class="form-control" style="width: 100%;" id="tbl_municipio_id"
                                            onchange="TIC_DASHBOARD.updateUrlMunicipio(this);"
                                            name="tbl_municipio_id">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-xl-12">
                    <center>
                        <div>
                            <div class="d-flex justify-content-center" id="containerDataTic" name="containerDataTic">
                                <div class="card g360-dashboard-card">
                                    <div class="card-header">
                                        <div>
                                            <h4><i class="feather icon-bar-chart-2 mr-2"></i>Resumen general TIC</h4>
                                            <p>Indicadores consolidados de dotación, conectividad, proyectos e inversión.</p>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center justify-content-center kpi-tic">
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/robot.png" alt="" width="60px">
                                                <h6 class="mt-2">Kits de robótica entregados</h6>
                                                <h4><?php echo number_format($robotica, 0); ?></h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/computadoresi.png" alt="" width="60px">
                                                <h6 class="mt-2">Computadores para instituciones</h6>
                                                <h4><?php echo number_format($institucion, 0); ?></h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/computadoresa.png" alt="" width="60px">
                                                <h6 class="mt-2">Computadores para estudiantes</h6>
                                                <h4><?php echo number_format($alumno, 0); ?></h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/laboratoriosi.png" alt="" width="60px">
                                                <h6 class="mt-2">Laboratorios de innovación</h6>
                                                <h4><?php echo number_format($laboratorio, 0); ?></h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/optica.png" alt="" width="60px">
                                                <h6 class="mt-2">Kilómetros de fibra óptica</h6>
                                                <h4>0</h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/internet.png" alt="" width="60px">
                                                <h6 class="mt-2">Instituciones con Internet</h6>
                                                <h4>0</h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/proyectospen.png" alt="" width="60px">
                                                <h6 class="mt-2">Proyectos Pendientes</h6>
                                                <h4>0</h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/contratoseje.png" alt="" width="60px">
                                                <h6 class="mt-2">Contratos en Ejecución</h6>
                                                <h4>0</h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/inversion.png" alt="" width="60px">
                                                <h6 class="mt-2">Inversión de la Secretaría</h6>
                                                <h4><?php echo number_format($inversionsec, 0); ?></h4>
                                            </div>
                                            <div class="col-md-3 mb-4">
                                                <img src="assets/img/totalpro.png" alt="" width="60px">
                                                <h6 class="mt-2">Valor total de proyectos</h6>
                                                <h4><?php echo number_format($valorproyectos, 0); ?></h4>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <br>
                    </center>
                    <!-- Opciones de filtro -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card g360-options-card">
                                <div class="card-header">
                                    <div>
                                        <h5><i class="feather icon-sliders mr-2"></i>Visualización territorial</h5>
                                        <p>Selecciona el indicador representado en el mapa o abre la geolocalización.</p>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="g360-options-layout">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalGeocalizacion">
                                            <i class="feather icon-map-pin"></i>
                                            Geolocalización
                                        </button>

                                        <div class="g360-options-select">
                                            <label for="opcion">Indicador</label>
                                            <select onchange="TIC_DASHBOARD.updateUrlOpcion(this)" class="form-control" id="opcion" name="opcion" style="width:250px;">
                                                <option value="robotica">Robótica</option>
                                                <option value="computadores_institucion">Computadores Institución</option>
                                                <option value="computador_alumno">Computador Alumno</option>
                                                <option value="laboratorio_innovacion">Laboratorio Innovación</option>
                                                <option value="contratos">Contratos</option>
                                                <option value="todos">Todos</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mapa Santander -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card g360-map-card">
                                <div class="card-header">
                                    <div>
                                        <h5><i class="feather icon-map mr-2"></i>Distribución territorial TIC</h5>
                                        <p>Selecciona un municipio para consultar su información tecnológica detallada.</p>
                                    </div>
                                </div>

                                <div class="card-body">
                            <div id="contenido-mapa" class="santander munis">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 788.66 885.68" style="width:100%;height:auto;display:block;">

                                    <?php foreach ($santander as $key => $value) : ?>
                                        <path id="<?php echo strtoupper($value['path']); ?>"
                                            d="<?php echo $value['d']; ?>"
                                            fill="<?php echo getColorOpcion($value["cantidad_mostrar"]) ?>"
                                            class="municipios mapaClick"
                                            data-mun="<?= htmlspecialchars($value['codigo_muncipio'] ?? '') ?>"
                                            data-dep="<?= htmlspecialchars($value['codigo_departamento'] ?? '') ?>"
                                            data-url="<?php echo getUrl() . 'municipios_secretarias_tic.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento']; ?>"
                                            data-name="<?php echo strtolower($value['municipio']); ?>"
                                            title="<?php echo strtoupper(str_replace("-", " ", $value['nombre_mapa'])); ?>"
                                            stroke="rgba(255,255,255,.15)" stroke-miterlimit="10" stroke-width="0.5">
                                        </path>
                                    <?php endforeach; ?>

                                    <?php require_once 'nombres_mapa_santander.php' ?>
                                </svg>
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-body">
                <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
                    aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalGeocalizacionTitle"><i class="feather icon-map-pin mr-2"></i>Geolocalización <span
                                        id="nombrePilar"></span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span
                                        aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div id="map" style="height: 600px; width: 100%;"></div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <?php include 'admin/include/footer.php'; ?>
        <?php include 'admin/include/gerenic_script.php'; ?>
        <script src="./assets/js/vendor-all.min.js"></script>
        <script src="./assets/js/plugins/bootstrap.min.js"></script>
        <script src="./assets/js/pcoded.min.js"></script>

        <!-- prism Js -->
        <script src="./assets/js/plugins/prism.js"></script>
        <script src="./assets/js/plugins/apexcharts.min.js"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

        <!-- Cargar Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- Google Maps JavaScript API -->
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
        </script>
        <script type="text/javascript" src="admin/js/tic_dash.js"></script>
        <script type="text/javascript" src="admin/js/departamento.js"></script>
        <script>
            setTimeout(function() {
                DEPARTAMENTO.getMunicipiosOpcionSelectTodos();
            }, 500);
            $("img").each(function(index, el) {
                $(this).attr("data-bs-toggle", "tooltip");
                $(this).attr("data-bs-placement", "left");
                tooltip = new bootstrap.Tooltip($(this)[0], {})
            });
            $(document).off("click", ".mapaClick").on("click", ".mapaClick", function() {
                window.location.href = $(this).data("url");
            });

            function initMap() {
                if (typeof google !== 'undefined' && google.maps) {
                    // Coordenadas iniciales
                    const initialLocation = {
                        lat: 7.0830880750303935,
                        lng: -73.02794598535458
                    };
                    // Crear el mapa
                    map = new google.maps.Map(document.getElementById("map"), {
                        center: initialLocation,
                        zoom: 12,
                    });
                    // Agregar evento para capturar clic en el mapa
                    map.addListener("click", (event) => {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();
                        // Mostrar las coordenadas en pantalla
                        document.getElementById("lat").innerText = lat.toFixed(6);
                        document.getElementById("lng").innerText = lng.toFixed(6);
                        // Agregar un marcador en el punto seleccionado
                        new google.maps.Marker({
                            position: event.latLng,
                            map: map,
                        });
                    });
                    // Agregar marcadores para los puntos del objeto
                    const data = [];
                    data.forEach(point => {
                        const marker = new google.maps.Marker({
                            position: {
                                lat: parseFloat(point.latitud),
                                lng: parseFloat(point.longitud)
                            },
                            map: map,
                            icon: {
                                url: point.icono ? point.icono : "assets/iconos/maps/geo.png",
                                scaledSize: new google.maps.Size(60, 60) // Ajusta el tamaño del icono
                            },
                            title: `${point.municipio} - ${point.nombre_vereda}`
                        });
                        const infoWindow = new google.maps.InfoWindow({
                            content: `
                <div>
                    <h3>${point.municipio}</h3>
                    <p><strong>Vereda:</strong> ${point.nombre_vereda}</p>
                    <p><strong>Tipo:</strong> ${point.tipo}</p>
                    <p><strong>Cantidad:</strong> ${point.valor}</p>
                    <p><strong>Observaciones:</strong> ${point.observaciones}</p>
                </div>
                `
                        });

                        marker.addListener("click", () => {
                            infoWindow.open(map, marker);
                        });
                    });
                } else {
                    console.error('Google Maps API no está disponible.');
                }
            }
        </script>
</body>

</html>

