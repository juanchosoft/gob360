<?php

function getClaseColorVeredas($color) {
    $clases = [
        "#f76060" => 'critico',
        "#d4dc27" => 'alto',
        "#33c1ff" => 'bajo',
        "#b8ff33" => 'estable'
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
    } elseif ($num >= 1 && $num <= 400) {
        return "#6079f7"; // Rojo
    } elseif ($num >= 401 && $num <= 999999) {
        return "#f76069"; // Naranja    
    }

    return ""; // Color predeterminado si no coincide con ningún rango
}

function getColorByNumSecretaria($num) {
    if ($num >= 0 && $num <= 0) {
        return "#f7f3f2"; // Color neutro
    } elseif ($num >= 1 && $num <= 2) {
        return "#DC143C"; // Rojo
    } elseif ($num >= 3 && $num <= 4) {
        return "#FFA500"; // Naranja
    } elseif ($num >= 5 && $num <= 6) {
        return "#4169E1"; // Azul
    } elseif ($num >= 7 && $num <= 99999) {
        return '#62af0a'; // Verde
    }

    return ""; // Color predeterminado si no coincide con ningún rango
}

/**
 * GOA Aprehensiones: 0 / 1-2 / 3-4 / 5-6 / 7+
 * Paleta SaaS consistente con mapaPalette()
 */
function getColorByNumGOA($num) {
    $n = intval($num);
    if ($n <= 0)  return '#ffffff'; // neutro
    if ($n <= 2)  return '#E53935'; // critico
    if ($n <= 4)  return '#FB8C00'; // alto
    if ($n <= 6)  return '#1E66F5'; // info
    return '#2E7D32';               // estable
}

/**
 * GOA Visitas a establecimientos: 0 / 1-28 / 29-56 / 57-84 / 85+
 */
function getColorByNumGOAVisitas($num) {
    $n = intval($num);
    if ($n <= 0)  return '#ffffff'; // neutro
    if ($n <= 28) return '#E53935'; // critico
    if ($n <= 56) return '#FB8C00'; // alto
    if ($n <= 84) return '#1E66F5'; // info
    return '#2E7D32';               // estable
}