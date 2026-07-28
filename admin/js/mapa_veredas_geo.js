let map;
let trafficLayer, transitLayer, bicycleLayer;
var informacionMapaFactores = [];
let infoWindow;

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

function initMap(longitud, latitud) {
  if (typeof google !== "undefined" && google.maps) {
    // Coordenadas por defecto
    const defaultLocation = {
      lat: 7.0830880750303935,
      lng: -73.02794598535458,
    };

    // Si las coordenadas están definidas, usarlas; sino, usar las coordenadas por defecto
    const initialLocation = {
      lat: latitud ? parseFloat(latitud) : defaultLocation.lat,
      lng: longitud ? parseFloat(longitud) : defaultLocation.lng,
    };
    // Crear el mapa
    map = new google.maps.Map(document.getElementById("map"), {
      center: initialLocation,
      zoom: 10,
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
    // Agregar marcadores — omitir puntos sin coordenadas válidas
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
    });
  } else {
    console.error("Google Maps API no está disponible.");
  }
}

/**
 * Metodo para mostrar la informacion de pilares por vereda id
 * @param {*} longitud
 * @param {*} latitude
 */
function mostrarInformacionPilarByVereda(longitud, latitude, factor_id) {
  q = {};
  q.op = "getmapapilaresbymunicipioId";
  q.pilarId = $("#pilarId").val();
  q.codigoMunicipio = $("#tbl_municipio_id").val();
  q.veredaId = $("#veredaId").val();
  q.factor_id = factor_id;
  UTIL.cursorBusy();
  $.ajax({
    data: q,
    type: "GET",
    dataType: "json",
    url: "admin/ajax/rqst.php",
    success: function (data) {
      q = {};
      UTIL.cursorNormal();
      if (data.output.valid) {
        let res = data.output.response || [];
        informacionMapaFactores = res;
        if (informacionMapaFactores.length > 0) {
          $("#nombrePilar").empty().append(informacionMapaFactores[0]["pilar"]);
        }

        // Inicializar mapa cuando el modal esté visible (Bootstrap 4)
        $("#modalGeocalizacion").one("shown.bs.modal", function () {
          // Limpiar mapa anterior si existe
          if (map) {
            document.getElementById("map").innerHTML = "";
            map = null;
          }
          initMap(longitud, latitude);
        });

        $("#modalGeocalizacion").modal("show");

      } else {
        UTIL.mostrarMensajeError(data.output.response.content);
      }
    },
  });
}
