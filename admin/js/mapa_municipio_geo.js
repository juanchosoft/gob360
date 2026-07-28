let map;
let trafficLayer, transitLayer, bicycleLayer;
let informacionMapaFactores = [];
let infoWindow;
let markers = []; // Array para almacenar todos los marcadores y poder eliminarlos


/* ---------------------------------------------------
   FUNCIONES UTILITARIAS
--------------------------------------------------- */

function getMunicipioSeguro() {
  // Solución directa al problema de producción
  let m = $("#tbl_municipio_id").val();

  // Cuando no ha cargado aún, intenta recuperar del select
  if (!m || m === "" || m === null) {
    let seleccionado = $("#tbl_municipio_id option[selected]").val();
    if (seleccionado) return seleccionado;
  }

  // Segundo intento: si mun viene por GET
  const munGET = $("#tbl_municipio_id").data("mun_default");
  if (!m && munGET) return munGET;

  return m;
}

function mostrarInfoWindow(marker, contenido) {
  if (!infoWindow) {
    infoWindow = new google.maps.InfoWindow();
  }
  infoWindow.setContent(contenido);
  infoWindow.open({
    anchor: marker,
    map: map,
    shouldFocus: false,
  });
}


/* ---------------------------------------------------
   MAPA
--------------------------------------------------- */

function initMap() {
  if (typeof google !== "undefined" && google.maps) {
    var longitud = $("#longitud").val();
    var latitud = $("#latitud").val();

    const defaultLocation = {
      lat: 7.0830880750303935,
      lng: -73.02794598535458,
    };

    const initialLocation = {
      lat: latitud ? parseFloat(latitud) : defaultLocation.lat,
      lng: longitud ? parseFloat(longitud) : defaultLocation.lng,
    };

    map = new google.maps.Map(document.getElementById("map"), {
      center: initialLocation,
      zoom: 10,
    });

    infoWindow = new google.maps.InfoWindow();

    map.addListener("click", (event) => {
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();
      document.getElementById("lat").innerText = lat.toFixed(6);
      document.getElementById("lng").innerText = lng.toFixed(6);

      new google.maps.Marker({
        position: event.latLng,
        map: map,
      });
    });

    // Marcadores — omitir puntos sin coordenadas válidas
    informacionMapaFactores.forEach((point) => {
      const lat = parseFloat(point.latitud);
      const lng = parseFloat(point.longitud);
      if (isNaN(lat) || isNaN(lng)) return;

      const marker = new google.maps.Marker({
        position: { lat, lng },
        map: map,
        icon: {
          url: point.icono ? point.icono : "assets/iconos/maps/geo.png",
          scaledSize: new google.maps.Size(24, 24),
        },
        title: `${point.municipio}${point.nombre_vereda && point.nombre_vereda !== 'null' && point.nombre_vereda !== 'undefined' ? ' - ' + point.nombre_vereda : ''}`,
      });

      const contenido = `
        <div style="max-width:220px;font-size:12px;padding:5px;color:#111111;background:#ffffff;">
          <h4 style="margin:0 0 5px 0;font-size:14px;color:#111111;">${point.municipio}</h4>
          ${point.nombre_vereda && point.nombre_vereda !== 'null' && point.nombre_vereda !== 'undefined' ? `<p style="margin:2px 0;color:#111111;"><strong>Vereda:</strong> ${point.nombre_vereda}</p>` : ''}
          <p style="margin:2px 0;color:#111111;"><strong>Tipo:</strong> ${point.tipo}</p>
          <p style="margin:2px 0;color:#111111;"><strong>Cantidad:</strong> ${point.valor}</p>
          <p style="margin:2px 0;color:#111111;"><strong>Observaciones:</strong> ${point.observaciones}</p>
        </div>`;

      marker.addListener("click", () => {
        mostrarInfoWindow(marker, contenido);
      });

      markers.push(marker);
    });

    // Capas de Google Maps
    trafficLayer = new google.maps.TrafficLayer();
    transitLayer = new google.maps.TransitLayer();
    bicycleLayer = new google.maps.BicyclingLayer();

  } else {
    console.error("Google Maps API no está disponible.");
  }
}


/* ---------------------------------------------------
   RESET DEL MAPA
--------------------------------------------------- */

function resetMapa() {
  if (markers.length > 0) {
    markers.forEach(marker => marker.setMap(null));
    markers = [];
  }

  if (infoWindow) infoWindow.close();

  if (map) {
    if (trafficLayer) trafficLayer.setMap(null);
    if (transitLayer) transitLayer.setMap(null);
    if (bicycleLayer) bicycleLayer.setMap(null);
  }

  const mapContainer = document.getElementById("map");
  if (mapContainer) mapContainer.innerHTML = "";

  map = null;
  trafficLayer = null;
  transitLayer = null;
  bicycleLayer = null;
  infoWindow = null;
}


/* ---------------------------------------------------
   CONSULTAR PILAR POR MUNICIPIO
--------------------------------------------------- */

function mostrarInformacionPilarByMunicipio(factor_id) {
  let municipio = getMunicipioSeguro();

  if (!municipio || municipio === "") {
    UTIL.mostrarMensajeError("Debe seleccionar un municipio.");
    return;
  }

  let q = {
    op: "getmapapilaresbymunicipioId",
    pilarId: $("#pilarId").val(),
    codigoMunicipio: municipio,
    factor_id: factor_id
  };

  UTIL.cursorBusy();

  $.ajax({
    data: q,
    type: "GET",
    dataType: "json",
    url: "admin/ajax/rqst.php",
    success: function (data) {
      console.log(data);
      UTIL.cursorNormal();

      if (data.output.valid) {
        informacionMapaFactores = data.output.response || [];

        if (informacionMapaFactores.length > 0) {
          $("#nombrePilar").empty().append(informacionMapaFactores[0]["pilar"]);
        }

        // Inicializar mapa cuando el modal esté visible (Bootstrap 4)
        $("#modalGeocalizacion").one("shown.bs.modal", function () {
          resetMapa();
          initMap();
        });

        $("#modalGeocalizacion").modal("show");

      } else {
        UTIL.mostrarMensajeError(data.output.response.content);
      }
    },
  });
}


/* ---------------------------------------------------
   CONSULTAR TODO POR MUNICIPIO
--------------------------------------------------- */

function getAllByMunicipio(municipio) {

  let m = municipio || getMunicipioSeguro();

  if (!m || m === "") {
    UTIL.mostrarMensajeError("Debe seleccionar un municipio.");
    return;
  }

  let q = {
    op: "getAllByMunicipio",
    codigoMunicipio: m,
    pilarId: $("#pilarId").val()
  };

  UTIL.cursorBusy();

  $.ajax({
    data: q,
    type: "GET",
    dataType: "json",
    url: "admin/ajax/rqst.php",
    success: function (data) {
      console.log(data);
      UTIL.cursorNormal();

      if (data.output.valid) {
        informacionMapaFactores = data.output.response || [];

        if (informacionMapaFactores.length > 0) {
          $("#nombrePilar").empty().append(informacionMapaFactores[0]["pilar"]);
        }

        // Inicializar mapa cuando el modal esté visible (Bootstrap 4)
        $("#modalGeocalizacion").one("shown.bs.modal", function () {
          resetMapa();
          initMap();
        });

        $("#modalGeocalizacion").modal("show");

      } else {
        UTIL.mostrarMensajeError(data.output.response.content);
      }
    },
  });
}
