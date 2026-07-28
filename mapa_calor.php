<!DOCTYPE html>
<html>
<head>
    <title>Mapa de Calor con Puntajes</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&libraries=visualization"></script>
    <style>
        #map {
            height: 100vh;
            width: 100%;
        }
        .info-burbuja h4,
        .info-burbuja h6,
        .info-burbuja p {
            margin: 4px 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <script>
    function getColorByScore(score) {
        const r = Math.floor((score / 100) * 255);
        const g = Math.floor(255 - (score / 100) * 255);
        return `rgb(${r},${g},0)`;
    }

    function initMap() {
        const map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 6.25184, lng: -75.56359 },
            zoom: 15,
            mapTypeId: 'roadmap'
        });

        const infoWindow = new google.maps.InfoWindow();

        fetch('heatmap_data.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(point => {
                    const circle = new google.maps.Circle({
                        strokeWeight: 0,
                        fillColor: getColorByScore(point.score),
                        fillOpacity: 0.6,
                        map: map,
                        center: { lat: point.lat, lng: point.lng },
                        radius: 20 + (point.score * 1.5)
                    });

                    // Definir contenido del InfoWindow
                    let content = '';

                    if (point.mensaje) {
                        content = `<div class="info-burbuja"><p><strong>${point.mensaje}</strong></p></div>`;
                    } else {
                        content = `
                            <div class="info-burbuja">
                                <h4><strong>${point.titulo ? point.titulo : ''}</strong></h4>
                                <h6><strong>${point.subtitulo ? point.subtitulo : ''}</strong></h6>
                                <p>${point.vereda ? point.vereda : ''}</p>
                                <p>${point.municipio ? point.municipio : ''}, ${point.departamento ? point.departamento : ''}</p>
                            </div>
                        `;
                    }

                    circle.addListener('click', function(ev) {
                        infoWindow.setContent(content);
                        infoWindow.setPosition(ev.latLng);
                        infoWindow.open(map);
                    });
                });
            })
            .catch(error => {
                console.error('Error cargando los datos:', error);
                alert('Hubo un problema cargando los puntos del mapa.');
            });
    }

    window.onload = initMap;
    </script>
</body>
</html>
