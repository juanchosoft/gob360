let map;
let trafficLayer, transitLayer, bicycleLayer;
var informacionSedes = [];

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
        const data = informacionSedes;
        data.forEach(point => {
            const marker = new google.maps.Marker({
                position: {
                    lat: parseFloat(point.latitud),
                    lng: parseFloat(point.longitud)
                },
                map: map,
                icon: {
                    url: point.icono ? point.icono : "assets/iconos/maps/pae.png",
                    scaledSize: new google.maps.Size(24, 24)
                },
                title: `${point.municipio}${point.nombre_vereda && point.nombre_vereda !== 'null' && point.nombre_vereda !== 'undefined' ? ' - ' + point.nombre_vereda : ''}`
            });
            const infoWindow = new google.maps.InfoWindow({
                content: `
                <div>
                    <h3>${point.municipio}</h3>
                    ${point.nombre_vereda && point.nombre_vereda !== 'null' && point.nombre_vereda !== 'undefined' ? `<p><strong>Vereda:</strong> ${point.nombre_vereda}</p>` : ''}
                    <p><strong>Nombre:</strong> ${point.nombre}</p>
                    <p><strong>Institución:</strong> ${point.nombre_institucion}</p>
                </div>
                `
            });

            marker.addListener("click", () => {
                infoWindow.open(map, marker);
            });
        });

        // Inicializar las capas
        trafficLayer = new google.maps.TrafficLayer(); // Capa de tráfico
        transitLayer = new google.maps.TransitLayer(); // Capa de transporte público
        bicycleLayer = new google.maps.BicyclingLayer(); // Capa de bicicletas
    } else {
        console.error('Google Maps API no está disponible.');
    }
}
function mostrarMapa() {
    q = {};
    q.op = "getSedesInformacionMapaPae";
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
                informacionSedes = res;
                initMap();
            } else {
                UTIL.mostrarMensajeError(data.output.response.content);
            }
        },
    });
}

// Inicializar el mapa cuando se abre el modal
$('#modalGeocalizacion').on('shown.bs.modal', function () {
    mostrarMapa();
});



