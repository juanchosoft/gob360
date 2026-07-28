<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
?>
<body class="">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <?php
    include './admin/include/navbar.php';
    ?>
    <?php
    include './admin/include/header.php';
    ?>
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Acción unificada </h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Configuración Acción Unificada / Listado
                                Ingreso
                                Información</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="contenedor">
                <div class="contenido">
                    <div class="col-sm-12">
                <div class="card">
                        <div class="card-body table-border-style">
                            <div class="navbar-form buscador-2">
                                <div class="input-group input-primary">
                                    <input type="text" id="customSearch" class="form-control" placeholder="Buscar">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="feather icon-edit"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="table-responsive tabla-informacion tabla-scroll">
                                    <table class="table table-hover mb-0" id="dynamictable">
                                        <thead>
                                            <tr class="border-1 listado">
                                                <th>Editar</th>
                                                <th>Eliminar</th>
                                                <th>Fecha</th>
                                                <th>Departamento</th>
                                                <th>Municipio</th>
                                                <th>Vereda</th>
                                                <th>Latitud</th>
                                                <th>Longitud</th>
                                                <th>Factor</th>
                                                <th>Valor</th>
                                                <th>Obs.</th>
                                                <th>Fotos</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Modal para Editar Información -->
<div class="modal fade" id="modalEditarInformacion" tabindex="-1" role="dialog"
    aria-labelledby="modalEditarInformacionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content" style="height: 60vh";>

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalEditarInformacionLabel">
          Editar Información <i class="feather icon-edit" style="margin-left: 8px; cursor: pointer;"></i>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div id="alerta-modal"></div>

      <div class="modal-body" style="padding: 40px;">
        <form id="formEditarInformacion" class="needs-validation" novalidate>
          <input type="hidden" id="modalId" name="id">
<input type="hidden" id="modalIconoFactor">

          <div class="row">
            <div class="form-group col-md-4">
              <label for="modalFecha">Fecha</label>
              <input type="text" class="form-control" id="modalFecha" name="modalFecha" readonly>
            </div>

            <div class="form-group col-md-4">
              <label for="modalDepartamento">Departamento</label>
              <input type="text" class="form-control" id="modalDepartamento" name="modalDepartamento" readonly>
            </div>

            <div class="form-group col-md-4">
              <label for="modalMunicipio">Municipio</label>
              <input type="text" class="form-control" id="modalMunicipio" name="modalMunicipio" readonly>
            </div>

            <div class="form-group col-md-4">
              <label for="modalVereda">Vereda</label>
              <input type="text" class="form-control" id="modalVereda" name="modalVereda" readonly>
            </div>

            <div class="form-group col-md-4">
              <label for="modalFactor">Factor</label>
              <input type="text" class="form-control" id="modalFactor" name="modalFactor" readonly>
            </div>

            <div class="form-group col-md-4">
              <label for="modalValor">Valor</label>
              <input type="number" class="form-control" id="modalValor" name="modalValor">
            </div>

            <div class="form-group col-md-4">
              <label for="modalLogitud">Longitud</label>
              <input type="text" class="form-control" id="modalLogitud" name="modalLogitud">
            </div>

            <div class="form-group col-md-4">
              <label for="modalLatitud">Latitud</label>
              <input type="text" class="form-control" id="modalLatitud" name="modalLatitud">
            </div>

             <div class="form-group col-md-4">
                <button type="button" class="btn btn-primary" onclick="abrirModal();" style="width: 60px; height: 60px; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <img src="assets/images/geoloca.png" alt="Geolocalización" style="width: 100%; height: 100%; object-fit: cover;">
                </button>
            </div>

          </div>
        </form>
      </div>

      <div class="modal-footer text-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarEdicion()">Guardar Cambios</button>
      </div>

    </div>
  </div>
</div>


    <!-- Modal para mostrar observaciones -->
    <div class="modal fade" id="modalObservaciones" style="background: black;" tabindex="-1" role="dialog" aria-labelledby="modalObservacionesLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 400px;">
            <div class="modal-content" role="document">
                <div class="modal-header bg-primary text-white" >
                    <h5 class="modal-title" id="modalObservacionesLabel">Observaciones </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalObservacionesBody" style="padding: 20px;">
                    <!-- Aquí se insertará la observación -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar imágenes -->
    <div class="modal fade" id="imageModal" tabindex="-1" style="background: black;" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" >
                    <h5 class="modal-title" id="imageModalLabel">Imágenes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-2" id="imageContainer" style="padding: 10px;">
                        <!-- Aquí van las imágenes -->
                    </div>
                </div>
            </div>
        </div>
    </div>
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
<!-- LOADER OVERLAY -->
<div id="geoLoader" 
     style="
        position:absolute;
        top:0; left:0;
        width:100%;
        height:100%;
        background:rgba(255,255,255,0.8);
        display:flex;
        justify-content:center;
        align-items:center;
        z-index:9999;
     ">
    <div class="spinner-border text-primary" 
         role="status" 
         style="width:80px; height:80px;">
    </div>
</div>

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
                    // Variable para almacenar el marcador actual
                    let currentMarker = null;

                    // Evento para capturar clic en el mapa
                    map.addListener("click", (event) => {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();

                        // Actualizar los inputs del modal de edición con los IDs correctos
                        $("#modalLatitud").val(lat);
                        $("#modalLogitud").val(lng);

                        // Mostrar las coordenadas en pantalla
                        document.getElementById("lat").innerText = lat.toFixed(6);
                        document.getElementById("lng").innerText = lng.toFixed(6);

                        // Eliminar marcador anterior si existe
                        if (currentMarker) {
                            currentMarker.setMap(null);
                        }

                        // Obtener el icono del marcador desde la carpeta assets/iconos
                        const iconUrl = icono || "assets/iconos/maps/geo.png";
                        // Agregar un marcador en el punto seleccionado con el icono personalizado
                        currentMarker = new google.maps.Marker({
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
    let latitud = $('#modalLatitud').val();
    let longitud = $('#modalLogitud').val();

    // Mostrar loader y ocultar mapa
    $("#geoLoader").show();
    $("#map").css("opacity", "0");

    var enforceFocus = $.fn.modal.Constructor.prototype._enforceFocus;
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};

    $('#modalGeocalizacion').modal('show');

    $('#modalGeocalizacion').one('shown.bs.modal', function () {

        setTimeout(function() {

initMap(latitud, longitud, $("#modalIconoFactor").val());

            // Esperar a que Google Maps termine de cargar
            setTimeout(function() {

                $("#geoLoader").fadeOut(200);  // Oculta loader suavemente
                $("#map").css("opacity", "1");

            }, 1000);
        }, 200);
    });

    $('#modalGeocalizacion').one('hidden.bs.modal', function () {
        $.fn.modal.Constructor.prototype._enforceFocus = enforceFocus;
    });
}


    </script>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <?php include 'admin/include/gerenic_script.php'; ?>
    <script type="text/javascript" src="admin/js/ingreso_informacion.js"></script>
    <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
    <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
    <style>
      .btn{
        border-radius: 14px !important;
        padding: 10px 22px !important;
        font-weight: 900 !important;
        border: 1px solid rgba(255,255,255,.14) !important;
        box-shadow: 0 10px 24px rgba(0,0,0,.25);
      }
      .btn-primary{
        border-color: rgba(79,124,255,.45) !important;
        background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
        color:#fff !important;
      }
      .btn-primary:hover{ filter: brightness(1.1); }
      .btn-secondary{
        background: rgba(255,255,255,.06) !important;
        color: rgba(255,255,255,.86) !important;
        border-color: rgba(255,255,255,.12) !important;
      }
      .btn-secondary:hover{ background: rgba(255,255,255,.12) !important; color: #fff !important; }
      .btn-danger{
        border-color: rgba(255,91,122,.45) !important;
        background: rgba(255,91,122,.15) !important;
        color: #ff5b7a !important;
      }
      .btn-danger:hover{ background: rgba(255,91,122,.30) !important; color: #fff !important; }
      .btn-sm{
        border-radius: 10px !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
      }
      table.dataTable tbody tr{
        background-color: transparent !important;
      }
      table.dataTable.stripe tbody tr.odd,
      table.dataTable.display tbody tr.odd{
        background-color: rgba(255,255,255,.03) !important;
      }
      table.dataTable tbody td{
        color: rgba(255,255,255,.86) !important;
      }
      table.dataTable tbody td a{
        color: rgba(255,255,255,.86) !important;
      }
      table.dataTable tbody td i.feather,
      table.dataTable tbody td i.bi{
        color: rgba(255,255,255,.86) !important;
      }
      #tblVeredas td i.feather{
        color: rgba(255,255,255,.86) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button{
        color: rgba(255,255,255,.86) !important;
        background: rgba(255,255,255,.06) !important;
        border: 1px solid rgba(255,255,255,.10) !important;
        border-radius: 8px !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.current,
      .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
        color: #fff !important;
        background: rgba(31,111,235,.35) !important;
        border: 1px solid rgba(31,111,235,.50) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        color: #fff !important;
        background: rgba(255,255,255,.12) !important;
        border: 1px solid rgba(255,255,255,.20) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
        color: rgba(255,255,255,.30) !important;
        background: transparent !important;
        border: 1px solid transparent !important;
      }
      .dataTables_wrapper .dataTables_info,
      .dataTables_wrapper .dataTables_length label{
        color: #fff !important;
      }
      table.dataTable tbody tr.selected{
        background-color: rgba(31,111,235,.25) !important;
      }
    </style>

</body>

</html>