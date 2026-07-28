<?php

function getClaseColorVeredas($color) {
    $clases = [
        "#FC0707" => 'critico',
        "#15DA01" => 'alto',
        "#FEE300" => 'bajo',
        "#15DA01" => 'estable'
    ];

    return $clases[$color] ?? ''; // Retorna la clase correspondiente o una cadena vacía si no coincide
}

function getClasePorcentaje($porcentaje) {
    if ($porcentaje > 0 && $porcentaje <= 0.25) {
        return "medio";
    } elseif ($porcentaje >= 0.26 && $porcentaje <= 0.5) {
        return "bajo";
    } elseif ($porcentaje > 0.51) {
        return "estable";
    }

    return "neutro"; // Valor predeterminado
}



function getColorByNum($num) {
    if ($num >= 0 && $num <= 0) {
        return "#FFFFFF"; // Blanco
    } elseif ($num >= 1 && $num <= 50) {
        return "#f7c5ae"; // Verde agua marina Claro
    } elseif ($num >= 51 && $num <= 100) {
        return "#ffa5ae"; // Verde agua marina más oscuro
    } elseif ($num >= 101 && $num <= 99999999) {
        return "#ea9abd"; // Verde agua marina
    }
    return ""; // Color predeterminado si no coincide con ningún rango
}

/**
 * Escala de colores para Gestora Social según personas impactadas:
 *   0        → Gris   (#BDBDBD)
 *   1–49     → Rojo   (#e53935)
 *   50–99    → Amarillo (#FDD835)
 *   100–149  → Azul   (#1565C0)
 *   150+     → Verde  (#2E7D32)
 */
function getColorByNumGestoraSocial($num) {
    if ($num == 0) {
        return "#BDBDBD"; // GRIS: sin personas impactadas
    } elseif ($num >= 1 && $num <= 49) {
        return "#e53935"; // ROJO: 1–49
    } elseif ($num >= 50 && $num <= 99) {
        return "#FDD835"; // AMARILLO: 50–99
    } elseif ($num >= 100 && $num <= 149) {
        return "#1565C0"; // AZUL: 100–149
    } else {
        return "#2E7D32"; // VERDE: 150+
    }
}

?>
