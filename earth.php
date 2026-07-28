<?php
$kml_url = 'https://localhost/gober_santa/kml/medellin_puntos.kml'; // cambia esto por la URL real en tu servidor
$earth_url = "https://earth.google.com/web/?filename=" . urlencode($kml_url);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Abrir puntos Medellín en Google Earth</title>
</head>
<body style="text-align:center; margin-top:100px; font-family:Arial">
  <h2>Visualizar Puntos Geolocalizados en Google Earth</h2>
  <button onclick="window.open('<?php echo $earth_url; ?>', '_blank')" style="padding:15px 30px; font-size:18px; background-color:#3f51b5; color:white; border:none; border-radius:6px;">
    🌍 Abrir en Google Earth
  </button>
</body>
</html>
