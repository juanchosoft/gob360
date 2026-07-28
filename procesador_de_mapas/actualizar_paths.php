<?php
header("Content-Type: text/html; charset=utf-8");

// ------------------------------
// CONFIGURACIÓN
// ------------------------------
$pdo = new PDO("mysql:host=localhost;dbname=santaok;charset=utf8", "root", "");

// *** AQUÍ LA RUTA REAL DEL ARCHIVO ***
$svg_path = __DIR__ . "/mapa santander.html";

// Verificar que el archivo exista
if (!file_exists($svg_path)) {
    die("<h3 style='color:red'>ERROR: No se encontró el archivo: $svg_path</h3>");
}

// Cargar el contenido del archivo
$svg = file_get_contents($svg_path);

echo "<h2>ACTUALIZANDO PATHS…</h2>";

// ------------------------------
// EXTRAER <g id=""> Y <path id="">
// ------------------------------
preg_match_all(
    '/<g id="([^"]+)".*?<path[^>]+id="([^"_]+)[^"]*"/si',
    $svg,
    $matches
);

$grupos  = $matches[1];   
$paths   = $matches[2];  

$total = count($grupos);
echo "<p>Total encontrados en SVG: <strong>$total</strong></p><hr>";

for ($i = 0; $i < $total; $i++) {

    $grupo_id = $grupos[$i];    
    $path_id  = $paths[$i];     

    // Normalización del nombre para buscarlo en la columna municipio
    $municipio = strtoupper(str_replace('_', ' ', $grupo_id));

    // UPDATE directo por municipio
    $sql = "UPDATE tbl_ciudades_accion_unificada
            SET path = :path
            WHERE UPPER(municipio) = :mun";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':path' => $path_id,
        ':mun'  => $municipio
    ]);

    if ($stmt->rowCount() > 0) {
        echo "PATH actualizado para: <strong>$municipio</strong> → $path_id<br>";
    } else {
        echo "No encontrado en DB: <strong>$municipio</strong><br>";
    }
}

echo "<br><hr><h2>FINALIZADO</h2>";
?>
