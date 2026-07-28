<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pdo = new PDO("mysql:host=localhost;dbname=santaok", "root", "");

$svg_file = __DIR__ . "/mapa santander.html";
if (!file_exists($svg_file)) {
    die("ERROR: No se encontró el archivo SVG");
}

$svg = file_get_contents($svg_file);

// Captura TODOS los grupos <g> y su contenido
preg_match_all('/<g[^>]*id="([^"]+)"[^>]*>(.*?)<\/g>/si', $svg, $groups, PREG_SET_ORDER);

echo "<h2>Procesando textos…</h2>";

foreach ($groups as $g) {

    $group_id = $g[1];
    $content = $g[2];

    // Capturar TODOS los text dentro del grupo
    preg_match_all('/<text\b([^>]*)>(.*?)<\/text>/si', $content, $texts, PREG_SET_ORDER);

    if (empty($texts)) continue;


    $full_text = "";
    $x = null;
    $y = null;
    $rot = 0;
    $class = "";

    foreach ($texts as $t) {

        $attrs = $t[1];
        $inner = trim(strip_tags($t[2])); // texto

        if ($inner === "") continue;

        
        $full_text .= " " . $inner;

        
        if ($class === "" && preg_match('/class="([^"]+)"/i', $attrs, $c)) {
            $class = trim($c[1]);
        }

        if (preg_match('/matrix\([^)]* ([\d\.-]+) ([\d\.-]+)\)/i', $attrs, $mm)) {
            if ($x === null) $x = $mm[1];
            if ($y === null) $y = $mm[2];
        }

        
        if (preg_match('/translate\(([\d\.-]+)\s+([\d\.-]+)\)/i', $attrs, $tt)) {
            if ($x === null) $x = $tt[1];
            if ($y === null) $y = $tt[2];
        }

       
        if (preg_match('/rotate\(([\d\.-]+)/i', $attrs, $rr)) {
            $rot = $rr[1];
        }
    }

    // limpiar texto final
    $full_text = trim($full_text);
    $full_clean = strtolower($full_text);
    $full_clean = str_replace(
        ["á","é","í","ó","ú","ü"],
        ["a","e","i","o","u","u"],
        $full_clean
    );

    // Buscar municipio
    $sql = $pdo->prepare("
        SELECT id, municipio 
        FROM tbl_ciudades_accion_unificada 
        WHERE LOWER(municipio) LIKE :m
          AND codigo_departamento='68'
        LIMIT 1
    ");
    $sql->execute([':m' => "%$full_clean%"]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo " No encontrado en DB → <strong>$full_text</strong><br>";
        continue;
    }

    // Guardar
    $up = $pdo->prepare("
        UPDATE tbl_ciudades_accion_unificada
        SET x_text=:x, y_text=:y, rotate_text=:r, text_class=:c
        WHERE id=:id
    ");

    $up->execute([
        ':x' => $x,
        ':y' => $y,
        ':r' => $rot,
        ':c' => $class,
        ':id' => $row['id']
    ]);

    echo " {$row['municipio']} actualizado → ($full_text), x=$x, y=$y, rot=$rot<br>";
}

echo "<br><strong>LISTO ✔</strong>";
