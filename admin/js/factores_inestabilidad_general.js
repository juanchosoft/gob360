let informacionMapaFactores = [];
let mapInstance = null;
let markers = [];

function initMap() {
    const santander = { lat: 7.083, lng: -73.028 };
    mapInstance = new google.maps.Map(document.getElementById("map"), {
        zoom: 8,
        center: santander,
        mapTypeId: "roadmap",
    });
}

function mostrarMapaConInfoFactores() {
    const municipioId = document.getElementById("mun").value;
    const factorId = document.getElementById("factor").value;

    if (!municipioId || !factorId) {
        Swal.fire({ icon: "warning", title: "Debe seleccionar municipio y factor" });
        return;
    }

    if (markers.length > 0) {
        markers.forEach(m => m.setMap(null));
        markers = [];
    }

    $.ajax({
        data: { op: "getmapafactores", municipioId, factorId },
        type: "POST",
        dataType: "json",
        url: "admin/ajax/rqst.php",
        success: function(data) {
            if (data.output.valid && data.output.response.length > 0) {
                const geo = data.output.response;
                geo.forEach(item => {
                    if (item.lat && item.lng) {
                        const marker = new google.maps.Marker({
                            position: { lat: parseFloat(item.lat), lng: parseFloat(item.lng) },
                            map: mapInstance,
                            title: item.vereda,
                        });
                        const infoContent = `
                            <div class="infowindow-mini">
                                <strong>${item.municipio}</strong><br>
                                Vereda: ${item.vereda}<br>
                                Factor: ${item.tipo}<br>
                                Valor: ${item.valor}<br>
                                Obs: ${item.observaciones || "N/A"}
                            </div>
                        `;
                        const infowindow = new google.maps.InfoWindow({ content: infoContent });
                        marker.addListener("click", () => infowindow.open(mapInstance, marker));
                        markers.push(marker);
                    }
                });
                if (markers.length > 0) {
                    const bounds = new google.maps.LatLngBounds();
                    markers.forEach(m => bounds.extend(m.getPosition()));
                    mapInstance.fitBounds(bounds);
                }
            } else {
                Swal.fire({ icon: "info", title: "No se encontraron datos de geolocalización" });
            }
        },
        error: function() {
            Swal.fire({ icon: "error", title: "Error al consultar datos de geolocalización" });
        },
    });
}

function updateUrlInestabilidad(select) {
    const url = new URL(window.location.href);
    url.searchParams.set('inestabilidad', select.value);
    window.location.href = url.href;
}

$(".mapaClick").on("click", function() {
    const url = $(this).data("url");
    if (url) {
        window.location.href = url;
    }
});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".municipios").forEach(function(el) {
        const fill = el.getAttribute("fill");
        if (fill && (fill === "#ff0000" || fill === "#ff4500" || fill === "#ff6347" || fill === "#ffa500")) {
            el.classList.add("titila");
        }
    });
});
