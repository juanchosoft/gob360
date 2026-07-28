<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
include './admin/classes/Departamento.php';
include './admin/classes/Factores.php';

// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */


// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de Factores
$arrFactores = Factores::getAll(null);
$isvalid = $arrFactores['output']['valid'];
$arrFactores = $arrFactores['output']['response'];
$optionFactores = '<option value="seleccione">Seleccione...</option>';
foreach ($arrFactores as $val) {
    $optionFactores .= "<option  class='" . $val['icono'] . "'  value='" . $val['id'] . "'>" . $val['tipo'] . "</option>";
}
?>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY&callback=initMap"></script>
<style>
    .controls {
        margin-top: 10px;
        font-family: Arial, sans-serif;
        font-size: 16px;
    }
</style>

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
                                <h5 class="m-b-10">Proyectos Estratégicos</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Proyectos Estratégicos / Ingreso
                                        información</a></li>
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
                        <form id="formingresoinformacion" autocomplete="off">
                            <div class="card-header">
                                <h5><i class="feather icon-file"></i> Ingreso de información</h5>

                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle btn-icon"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="feather icon-more-horizontal"></i>
                                        </button>
                                        <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                            <li class="dropdown-item full-card"><a href="#!"><span><i
                                                            class="feather icon-maximize"></i> Maximizar</span><span
                                                        style="display:none"><i class="feather icon-minimize"></i>
                                                        Restaurar</span></a></li>
                                            <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                            class="feather icon-minus"></i> Colapsa</span><span
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
                            <div class="card-body">
                                <div class="col-sm-12">


                                    <div class="card-body">

                                        <input type="hidden" name="op" id="op" />
                                        <input type="hidden" name="id" id="id" />
                                        <input type="hidden" name="filtro" id="filtro" value="vereda" />
                                        <input type="hidden" name="filtroVeredaById" id="filtroVeredaById"
                                            value="si" />
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="validationCustom05">Departamento<span
                                                            class="text-danger mb-1">*</span></label>
                                                    <select onchange="DEPARTAMENTO.getMunicipios();"
                                                        class="form-control" id="tbl_departamento_id"
                                                        name="tbl_departamento_id">
                                                        <?php echo $optionDep; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="validationCustom05">Municipio<span
                                                            class="text-danger mb-1">*</span></label>
                                                    <select class="form-control" id="tbl_municipio_id"
                                                        onchange="DEPARTAMENTO.getVeredasByMunicipioId();"
                                                        name="tbl_municipio_id">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="exampleFormControlSelect1">Vereda<span
                                                            class="text-danger mb-1">*</span></label>
                                                    <select class="form-control" id="tbl_vereda_id"
                                                        name="tbl_vereda_id">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label for="exampleFormControlSelect1">Factores<span
                                                            class="text-danger mb-1">*</span></label>
                                                    <select class="form-control" id="factorId" name="factorId"
                                                        onchange="INGRESO_INFORMACION.showInfoGetFactores();">
                                                        <?php echo $optionFactores; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label class="floating-label" for="Text">Valor<span
                                                            class="text-danger mb-1">*</span></label>
                                                    <input type="text" class="form-control" id="valor" name="valor"
                                                        placeholder="Ingrese el valor">
                                                </div>
                                            </div>





                                            <div class="form-group col-md-2">
                                                <label for="validationCustom05">Longitud</label>
                                                <input type="email" class="form-control" id="longitud"
                                                    name="longitud" placeholder="" value="">
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label for="validationCustom05">Latitud</label>
                                                <input type="email" class="form-control" id="latitud" name="latitud"
                                                    placeholder="" value="">
                                            </div>

                                            <div class="card-body">
                                                <div id="divInformacion" class="card-body"
                                                    style="display: none; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                                    <div class="row"
                                                        style="display: flex; flex-wrap: wrap; gap: 10px;">
                                                        <div class="col-sm-3" style="flex: 1; min-width: 150px;">
                                                            <label class="floating-label" for="Text"
                                                                style="font-size: 14px; font-weight: bold;">Eje</label>
                                                            <div class="form-group">
                                                                <input id="eje" name="eje" class="form-control"
                                                                    type="text" placeholder="" readonly=""
                                                                    style="font-size: 14px; padding: 5px; border-radius: 4px;">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3" style="flex: 1; min-width: 150px;">
                                                            <label class="floating-label" for="Text"
                                                                style="font-size: 14px; font-weight: bold;">Pilar</label>
                                                            <div class="form-group">
                                                                <input id="pilar" name="pilar" class="form-control"
                                                                    type="text" placeholder="" readonly=""
                                                                    style="font-size: 14px; padding: 5px; border-radius: 4px;">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3" style="flex: 1; min-width: 150px;">
                                                            <label class="floating-label" for="Text"
                                                                style="font-size: 14px; font-weight: bold;">Área</label>
                                                            <div class="form-group">
                                                                <input id="area" name="area" class="form-control"
                                                                    type="text" placeholder="" readonly=""
                                                                    style="font-size: 14px; padding: 5px; border-radius: 4px;">
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3" style="flex: 1; min-width: 150px;">
                                                            <label class="floating-label" for="Text"
                                                                style="font-size: 14px; font-weight: bold;">Tipo
                                                                Medición</label>
                                                            <div class="form-group">
                                                                <input id="tipo_medicion" name="tipo_medicion"
                                                                    class="form-control" type="text" placeholder=""
                                                                    readonly=""
                                                                    style="font-size: 14px; padding: 5px; border-radius: 4px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="form-group col-12" style="text-align: center;">
                                                <label for="validationCustom05"
                                                    style="display: block; text-align: center;">Observaciones</label>
                                                <input type="email" class="form-control" id="observaciones"
                                                    name="observaciones" placeholder="" value=""
                                                    style="width: 100%; max-width: 800px; margin: 0 auto;">
                                            </div>
                                        </div>
                                        <div
                                            style="display: flex; align-items: center; justify-content: center; gap: 20px;">
                                            <button type="button"
                                                onclick="UTIL.clearForm('formingresoinformacion');"
                                                class="btn btn-danger">
                                                Cancelar
                                            </button>
                                            <!-- 
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalGeocalizacion" style="width: 80px; height: 80px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                    <img src="assets/images/geoloca.png" alt="Geolocalización" style="width: 100%; height: 100%; object-fit: cover;">
                                                </button> -->

                                            <button type="button" class="btn btn-primary" onclick="abrirModal();"
                                                style="width: 80px; height: 80px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                <img src="assets/images/geoloca.png" alt="Geolocalización"
                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                            </button>

                                            <button onclick="INGRESO_INFORMACION.save();" type="button"
                                                class="btn btn-primary">
                                                <i class="feather mr-2 icon-check-circle"></i>Guardar
                                            </button>
                                        </div>

                                    </div>

                                    <label class="floating-label" for="Text"></label>
                                    <div class="form-row m-4">

                                        <div class="form-group col-md-3">
                                            <label for="inversion">Foto 1</label>
                                            <div class="controls">
                                                <iframe id='ifm1' name='ifm' src="upload.php" width="200"
                                                    height="60" scrolling="no" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inversion">Foto 2</label>
                                            <div class="controls">
                                                <iframe id='ifm2' name='ifm' src="upload.php" width="200"
                                                    height="60" scrolling="no" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inversion">Foto 3</label>
                                            <div class="controls">
                                                <iframe id='ifm3' name='ifm' src="upload.php" width="200"
                                                    height="60" scrolling="no" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inversion">Foto 4</label>
                                            <div class="controls">
                                                <iframe id='ifm4' name='ifm' src="upload.php" width="200"
                                                    height="60" scrolling="no" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                    </div>

                                </div>







                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- [ sample-page ] end -->
    </div>
    <!-- [ Main Content ] end -->
    </div>
    </div>
    <!-- [ Main Content ] end -->

    <div class="card-body">
        <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
            aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div id="map" style="height: 550px; width: 100%;"></div>
                        <div class="controls">
                            <label>
                                <input type="checkbox" id="trafficLayerToggle"> Capa de Tráfico
                            </label>
                            <label>
                                <input type="checkbox" id="transitLayerToggle"> Capa de Transporte Público
                            </label>
                            <label>
                                <input type="checkbox" id="bicycleLayerToggle"> Capa de Bicicleta
                            </label>
                            <label>
                                <input type="checkbox" id="terrainToggle"> Mostrar Terreno
                            </label>
                        </div>
                        <div class="coordinates">
                            <strong>Latitud:</strong> <span id="lat">N/A</span> |
                            <strong>Longitud:</strong> <span id="lng">N/A</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Maps JavaScript API -->
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
        </script>
        <script>
            let map;
            let trafficLayer, transitLayer, bicycleLayer;
            let LATITUD = 7.10543;
            let LONGITUD = -73.122234;
            // Función para inicializar el mapa
            function initMap(lat, lng, icono = "assets/iconos/maps/geo.png") {
                if (typeof google !== 'undefined' && google.maps) {
                    if (lat !== undefined || lng !== undefined) {
                        LATITUD = +lat;
                        LONGITUD = +lng;
                    }

                    // Coordenadas iniciales
                    const initialLocation = {
                        lat: LATITUD,
                        lng: LONGITUD,
                    };
                    // Crear el mapa
                    const map = new google.maps.Map(document.getElementById("map"), {
                        center: initialLocation,
                        zoom: 12,
                    });
                    // Evento para capturar clic en el mapa
                    map.addListener("click", (event) => {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();

                        // Se lleno el input con el valor de latitude y longitude
                        $("#latitud").val(lat);
                        $("#longitud").val(lng);

                        // Mostrar las coordenadas en pantalla
                        document.getElementById("lat").innerText = lat.toFixed(6);
                        document.getElementById("lng").innerText = lng.toFixed(6);
                        // Obtener el icono del marcador desde la carpeta assets/iconos
                        const iconUrl = icono; // Cambia el nombre del archivo según tu necesidad
                        // Agregar un marcador en el punto seleccionado con el icono personalizado
                        new google.maps.Marker({
                            position: event.latLng,
                            map: map,
                            icon: iconUrl,
                        });
                    });
                    // Inicializar capas opcionales (tráfico, transporte público, bicicletas, terreno)
                    const trafficLayer = new google.maps.TrafficLayer();
                    const transitLayer = new google.maps.TransitLayer();
                    const bicycleLayer = new google.maps.BicyclingLayer();
                    // Funciones para manejar las capas opcionales
                    const toggleLayer = (layer, isChecked) => {
                        layer.setMap(isChecked ? map : null);
                    };
                    // Agregar eventos a los checkboxes
                    document.getElementById("trafficLayerToggle").addEventListener("change", (e) => {
                        toggleLayer(trafficLayer, e.target.checked);
                    });
                    document.getElementById("transitLayerToggle").addEventListener("change", (e) => {
                        toggleLayer(transitLayer, e.target.checked);
                    });
                    document.getElementById("bicycleLayerToggle").addEventListener("change", (e) => {
                        toggleLayer(bicycleLayer, e.target.checked);
                    });
                    document.getElementById("terrainToggle").addEventListener("change", (e) => {
                        map.setMapTypeId(e.target.checked ? "terrain" : "roadmap");
                    });



                } else {
                    console.error('Google Maps API no está disponible.');
                }
            }

            function abrirModal() {
                const msj = "Debes seleccionar todas la opciones para poder abrir la geocalización";
                // Validar campos obligatorios
                const camposRequeridos = ["#tbl_departamento_id", "#tbl_municipio_id", "#tbl_vereda_id", "#factorId"];

                if (!UTIL.validarCampos(camposRequeridos)) {
                    UTIL.mostrarMensajeValidacion(msj);
                    return;
                }
                if (informacionMunicipio.latitud && informacionMunicipio.longitud) {
                    const latitud = informacionMunicipio.latitud === undefined ? LATITUD : informacionMunicipio.latitud;
                    const longitud = informacionMunicipio.longitud === undefined ? LONGITUD : informacionMunicipio.longitud;
                    const factorClass = $("#factorId").find(":selected").attr("class");

                    initMap(latitud, longitud, factorClass);
                }

                setTimeout(function() {
                    $('#modalGeocalizacion').modal();
                }, 1000);
            }
        </script>
    </div>

    <?php include 'admin/include/gerenic_script.php'; ?>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/ingreso_estrategicos.js"></script>
    <script>
        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 1000);
    </script>
</body>

</html>