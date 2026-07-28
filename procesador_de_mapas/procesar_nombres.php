<?php
/**
 * PROCESADOR AUTOMÁTICO DE NOMBRES / COORDENADAS
 * ----------------------------------------------
 * Extrae:
 *  - Texto del municipio desde <text>
 *  - Coordenadas X, Y
 *  - Rotación
 *  - Tamaño de fuente
 *  - Nombre final (limpio)
 *
 * Actualiza:
 *  name, x_text, y_text, rotate_text, font_size
 *
 */

$pdo = new PDO("mysql:host=localhost;dbname=santaok", "root", "");

// Ruta al mapa
$svg_file = "";


if (!file_exists($svg_file)) {
    die("<b>ERROR:</b> No existe el archivo SVG: $svg_file");
}

$svg = file_get_contents($svg_file);

echo "<h2>Procesando nombres…</h2>";


preg_match_all('/<g id="([^"]+)".*?<\/g>/si', $svg, $groups);

$municipios = $groups[1]; 
$bloques    = $groups[0];   

$total = count($bloques);

for ($i = 0; $i < $total; $i++) {
    $gid    = $municipios[$i];   
    $bloque = $bloques[$i];

    
    if (!preg_match('/<text\s+([^>]+)>(.*?)<\/text>/si', $bloque, $text_match)) {
        echo "⛔ No se encontró texto para g=$gid<br>";
        continue;
    }

    $atributos   = $text_match[1];   
    $contenido   = strip_tags($text_match[2]); 
    $nombre      = trim($contenido);

   
    if (strpos($nombre, "\n") !== false) {
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre));
    }

    $x = $y = $rot = 0;

    if (preg_match('/matrix\([^)]*?\s([\d\.\-]+)\s([\d\.\-]+)\)/', $atributos, $m)) {
        $x = $m[1];
        $y = $m[2];
    }
    elseif (preg_match('/translate\(\s*([\d\.]+)\s+([\d\.]+)\s*\)/', $atributos, $t)) {
        $x = $t[1];
        $y = $t[2];
    }

    // Rotación
    if (preg_match('/rotate\(([\d\.\-]+)\)/', $atributos, $r)) {
        $rot = $r[1];
    }

    // Tamaño de fuente
    $fontsize = 8;
    if (preg_match('/font-size="([\d\.]+)"/', $atributos, $fs)) {
        $fontsize = $fs[1];
    }

    $nombre_clean = strtoupper( normalizar($nombre) );

    $sql = "
        UPDATE tbl_ciudades_accion_unificada
        SET name = :n,
            x_text = :x,
            y_text = :y,
            rotate_text = :rot,
            font_size = :fs
        WHERE UPPER(municipio) = :n2
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':n'   => $nombre,
        ':x'   => $x,
        ':y'   => $y,
        ':rot' => $rot,
        ':fs'  => $fontsize,
        ':n2'  => $nombre_clean,
    ]);

    echo "✔ <b>$nombre</b> actualizado (x=$x, y=$y, rot=$rot)<br>";
}

echo "<br><h3>LISTO ✔</h3>";


// ---------------------------------------------------------------------
// FUNCIONES
// ---------------------------------------------------------------------
function normalizar($texto) {
    $texto = htmlentities($texto, ENT_QUOTES, 'UTF-8');
    $texto = preg_replace('/&([a-zA-Z])(?:acute|grave|circ|tilde|uml);/', '$1', $texto);
    $texto = preg_replace('/&([a-zA-Z])(?:cedil|lig);/', '$1', $texto);
    return strtoupper($texto);
}
?>
