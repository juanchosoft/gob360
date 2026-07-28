let map;
let trafficLayer, transitLayer, bicycleLayer;
var informacionMapaFactores = [];
let isUpdating = false; // Flag para evitar loop infinito
let updateHistoryDebounce;
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

function initMap() {
  if (typeof google !== "undefined" && google.maps) {
    // Coordenadas iniciales
    const initialLocation = {
      lat: 7.0830880750303935,
      lng: -73.02794598535458,
    };
    // Crear el mapa
    map = new google.maps.Map(document.getElementById("map"), {
      center: initialLocation,
      zoom: 8,
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
    const data = informacionMapaFactores;
    data.forEach((point) => {
      const marker = new google.maps.Marker({
        position: {
          lat: parseFloat(point.latitud),
          lng: parseFloat(point.longitud),
        },
        map: map,
        icon: {
          url: point.icono ? point.icono : "assets/iconos/maps/geo.png",
          scaledSize: new google.maps.Size(24, 24), // Ajusta el tamaño del icono
        },
        title: `${point.municipio}${point.nombre_vereda && point.nombre_vereda !== 'null' && point.nombre_vereda !== 'undefined' ? ' - ' + point.nombre_vereda : ''}`,
      });
      marker.addListener("click", () => {
        const contenido = `
          <div class="infowindow-mini">
            <h4>${point.municipio}</h4>
            ${point.nombre_vereda && point.nombre_vereda !== 'null' && point.nombre_vereda !== 'undefined' ? `<p><strong>Vereda:</strong> ${point.nombre_vereda}</p>` : ''}
            <p><strong>Tipo:</strong> ${point.tipo}</p>
            <p><strong>Cantidad:</strong> ${point.valor}</p>
            <p><strong>Observaciones:</strong> ${point.observaciones}</p>
          </div>`;
        mostrarInfoWindow(marker, contenido);
      });
    });

    // Inicializar las capas
    trafficLayer = new google.maps.TrafficLayer(); // Capa de tráfico
    transitLayer = new google.maps.TransitLayer(); // Capa de transporte público
    bicycleLayer = new google.maps.BicyclingLayer(); // Capa de bicicletas
    // Eventos para los checkboxes
    // document.getElementById("trafficLayerToggle").addEventListener("change", (e) => {
    //     if (e.target.checked) {
    //         trafficLayer.setMap(map);
    //     } else {
    //         trafficLayer.setMap(null);
    //     }
    // });
    // document.getElementById("transitLayerToggle").addEventListener("change", (e) => {
    //     if (e.target.checked) {
    //         transitLayer.setMap(map);
    //     } else {
    //         transitLayer.setMap(null);
    //     }
    // });
    // document.getElementById("bicycleLayerToggle").addEventListener("change", (e) => {
    //     if (e.target.checked) {
    //         bicycleLayer.setMap(map);
    //     } else {
    //         bicycleLayer.setMap(null);
    //     }
    // });
    // document.getElementById("terrainToggle").addEventListener("change", (e) => {
    //     if (e.target.checked) {
    //         map.setMapTypeId("terrain"); // Cambia el tipo de mapa a terreno
    //     } else {
    //         map.setMapTypeId("roadmap"); // Cambia el tipo de mapa a carreteras
    //     }
    // });
  } else {
    console.error("Google Maps API no está disponible.");
  }
}

function mostrarMapaConInfoFactores() {
  q = {};
  q.op = "getmapafactores";
  q.factorId = $("#factorId").val();
  q.pilarId = $("#pilarId").val();
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
        let res = data.output.response;
        informacionMapaFactores = res;
        if (informacionMapaFactores.length > 0) {
          $("#nombrePilar").empty().append(informacionMapaFactores[0]["pilar"]);
        }
        initMap();
      } else {
        UTIL.mostrarMensajeError(data.output.response.content);
      }
    },
  });
}

// Inicializar el mapa cuando se abre el modal
$("#modalGeocalizacion").on("shown.bs.modal", function () {
  mostrarMapaConInfoFactores();
});

$("img").each(function (index, el) {
  $(this).attr("data-bs-toggle", "tooltip");
  $(this).attr("data-bs-placement", "left");
  tooltip = new bootstrap.Tooltip($(this)[0], {});
});

$(".mapaClick").click(function (event) {
  const url = $(this).data("url");
  const permiso = $(this).data("permiso");
  const usuario = $(this).data("usuario");
  const queryString = url.split("?")[1];
  const param = new URLSearchParams(queryString);
  const mun = param.get("mun");
  console.log("Permiso:", permiso, "Usuario:", usuario, "Municipio:", mun);
  if (usuario === "Alcalde" || usuario === "Auxiliar_Alcalde") {
    if (String(permiso).trim() !== String(mun).trim()) {
      event.preventDefault();
      UTIL.mostrarMensajeError(
        "No tiene permiso para acceder a este municipio."
      );
      return;
    }
  }

  location.href = $(this).data("url");
});

function updateUrlPilar(item) {
  if (isUpdating) return; // Evitar que se ejecute mientras está en proceso de actualización
  const selectedPilar = item.value || 1;
  // Validar si el valor seleccionado ya está en la URL
  const currentUrl = new URL(window.location.href);
  const actualPilar = currentUrl.searchParams.get("pilar");
  // Actualizar el valor del select y evitar loop infinito
  updateSelectWithoutTrigger("#pilarId", selectedPilar);
  // Debounce para limitar llamadas a pushState
  clearTimeout(updateHistoryDebounce);
  updateHistoryDebounce = setTimeout(() => {
    currentUrl.searchParams.set("pilar", selectedPilar);
    window.history.pushState({}, "", currentUrl);
    $.ajax({
      url: currentUrl.toString(),
      type: "GET",
      success: function (response) {
        const updatedContent = $(response).find("#contenido-mapa").html();
        $("#contenido-mapa").html(updatedContent);
      },
      error: function (error) {
        console.error("Error al cargar contenido:", error);
      },
    });
  }, 500);
}

function updateSelectWithoutTrigger(selectId, value) {
  const selectElement = $(selectId);
  isUpdating = true;
  selectElement.off("change");
  selectElement.val(value);
  selectElement.trigger("change");
  setTimeout(() => {
    isUpdating = false;
  }, 300);
}

document.addEventListener("DOMContentLoaded", function () {
  function actualizarTitileo() {
    var municipios = document.querySelectorAll(".municipios");

    municipios.forEach(function (municipio) {
      var color = municipio.getAttribute("fill");

      // Definir los colores que deben titilar
      var coloresTitilar = ["#ff0000", "#cd162c", "#ffa500", "#cd7d16"];

      // Remover la clase "titila" antes de volver a evaluar
      municipio.classList.remove("titila");

      // Si el color está en la lista, añadir la clase "titila"
      if (coloresTitilar.includes(color.toLowerCase())) {
        municipio.classList.add("titila");
      }
    });
  }

  // Ejecutar la función al cargar la página
  actualizarTitileo();

  var selectPilar = document.getElementById("pilarId");

  if (selectPilar) {
    selectPilar.addEventListener("change", function () {
      setTimeout(actualizarTitileo, 1000);
    });
  }
});
