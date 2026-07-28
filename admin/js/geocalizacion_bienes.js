
let map;
let trafficLayer, transitLayer, bicycleLayer;
let LATITUD = 7.10543;
let LONGITUD = -73.122234;
// Función para inicializar el mapa bienes
function initMap(lat, lng, icono = "assets/iconos/maps/geo.png") {
  if (typeof google !== "undefined" && google.maps) {
    if (lat !== undefined && lng !== undefined) {
      LATITUD = +lat;
      LONGITUD = +lng;
    }

    const initialLocation = {
      lat: LATITUD,
      lng: LONGITUD,
    };

    const map = new google.maps.Map(document.getElementById("map"), {
      center: initialLocation,
      zoom: 12,
    });

    let currentMarker = null;

    // Si se pasan coordenadas, marcar ese punto
    if (lat !== undefined && lng !== undefined) {
      currentMarker = new google.maps.Marker({
        position: { lat: +lat, lng: +lng },
        map: map,
        icon: icono,
      });

      // Mostrar coordenadas
      $("#latitud").val(lat);
      $("#longitud").val(lng);
      document.getElementById("lat").innerText = (+lat).toFixed(6);
      document.getElementById("lng").innerText = (+lng).toFixed(6);
    }

    // Evento click para mover marcador
    map.addListener("click", (event) => {
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();

      $("#latitud").val(lat);
      $("#longitud").val(lng);
      document.getElementById("lat").innerText = lat.toFixed(6);
      document.getElementById("lng").innerText = lng.toFixed(6);

      if (currentMarker) {
        currentMarker.setMap(null);
      }

      currentMarker = new google.maps.Marker({
        position: event.latLng,
        map: map,
        icon: icono,
      });
    });

    // Capas opcionales
    const trafficLayer = new google.maps.TrafficLayer();
    const transitLayer = new google.maps.TransitLayer();
    const bicycleLayer = new google.maps.BicyclingLayer();

    const toggleLayer = (layer, isChecked) => {
      layer.setMap(isChecked ? map : null);
    };

    document
      .getElementById("trafficLayerToggle")
      .addEventListener("change", (e) => {
        toggleLayer(trafficLayer, e.target.checked);
      });
    document
      .getElementById("transitLayerToggle")
      .addEventListener("change", (e) => {
        toggleLayer(transitLayer, e.target.checked);
      });
    document
      .getElementById("bicycleLayerToggle")
      .addEventListener("change", (e) => {
        toggleLayer(bicycleLayer, e.target.checked);
      });
    document.getElementById("terrainToggle").addEventListener("change", (e) => {
      map.setMapTypeId(e.target.checked ? "terrain" : "roadmap");
    });
  } else {
    UTIL.mostrarMensajeValidacion(
      "Google Maps API no está disponible. Por favor, recargue la página."
    );
  }
}


function abrirModal() {
    const msj = "Debes seleccionar el departamento y municipio para poder abrir la geocalización";
    // Validar campos obligatorios
    const camposRequeridos = ["#tbl_departamento_id", "#tbl_municipio_id"];

    if (!UTIL.validarCampos(camposRequeridos)) {
        UTIL.mostrarMensajeValidacion(msj);
        return;
    }
    if (informacionMunicipio.latitud && informacionMunicipio.longitud) {
        const latitud = informacionMunicipio.latitud === undefined ? LATITUD : informacionMunicipio.latitud;
        const longitud = informacionMunicipio.longitud === undefined ? LONGITUD : informacionMunicipio.longitud;
        initMap(latitud, longitud, null);
    }

    setTimeout(function() {
        $('#modalGeocalizacion').modal();
    }, 1000);
}