<?php

$pdo = new PDO("mysql:host=localhost;dbname=santaok", "root", "");

// Archivo SVG/HTML
$svg = file_get_contents(__DIR__ . "/../mapa santander.html");

// Buscar cada bloque <g ...>...</g>
preg_match_all('/<g\s+id="([^"]+)"[^>]*>(.*?)<\/g>/si', $svg, $groups);

$g_ids   = $groups[1]; // nombres del <g id="">
$blocks  = $groups[2]; // contenido del <g>

echo "<h2>Actualizando municipios…</h2>";

for ($i = 0; $i < count($g_ids); $i++) {

    $nombre_g = $g_ids[$i];   // ej: "coromoro"
    $block    = $blocks[$i];

    // obtener el path real "path1234"
    if (!preg_match('/<path[^>]+id="(path\d+)/i', $block, $p1)) {
        continue;
    }
    $path_id = $p1[1];

    // obtener el atributo d="Mxxx..."
    if (!preg_match('/d="([^"]+)"/i', $block, $p2)) {
        continue;
    }
    $d_value = $p2[1];

    // actualizar
    $sql = "UPDATE tbl_ciudades_accion_unificada
            SET d = :d
            WHERE path = :pid";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':d'  => $d_value,
        ':pid' => $path_id
    ]);

    echo "✔️ " . strtoupper($nombre_g) . " ($path_id)<br>";
}

echo "<br><strong>LISTO</strong>";
