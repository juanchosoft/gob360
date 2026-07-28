<?php

/**
 * Paleta SaaS premium (consistente en todo el mapa)
 * neutro  -> gris muy claro
 * critico -> rojo
 * alto    -> naranja
 * medio   -> amarillo
 * estable -> verde
 * info    -> azul (si lo necesitas para rangos específicos)
 */
function mapaPalette($key) {
  $map = [
    'neutro'  => '#ffffff',
    'critico' => '#E53935',
    'alto'    => '#FB8C00',
    'medio'   => '#F6C026',
    'estable' => '#2E7D32',
    'info'    => '#1E66F5', // azul pro
  ];
  return $map[$key] ?? $map['neutro'];
}

/**
 * Clase por color exacto (cuando ya vienes con hex)
 */
function getClaseColorVeredas($color) {
  $clases = [
    '#DC143C' => 'critico',
    '#FFA500' => 'alto',
    '#FEE300' => 'medio',   // antes "bajo", lo dejamos coherente con amarillo
    '#62af0a' => 'estable',
    '#4169E1' => 'info',    // por si llega azul
  ];
  return $clases[strtoupper($color)] ?? $clases[strtolower($color)] ?? '';
}

/**
 * Clase por porcentaje (0..1)
 * Reglas claras:
 * 0 => neutro
 * 0.01 - 0.25 => medio
 * 0.26 - 0.50 => alto
 * >0.50 => estable
 */
function getClasePorcentaje($porcentaje) {
  $p = floatval($porcentaje);

  if ($p <= 0) return 'neutro';
  if ($p <= 0.25) return 'medio';
  if ($p <= 0.50) return 'alto';
  return 'estable';
}

/**
 * Color por número (caso general)
 * 0 => neutro (NO blanco puro, mejor neutro)
 * 1-2 => critico
 * 3-4 => alto
 * 5-6 => info
 * >=7 => estable
 */
function getColorByNum($num) {
  $n = intval($num);

  if ($n <= 0) return mapaPalette('neutro');
  if ($n <= 2) return mapaPalette('critico');
  if ($n <= 4) return mapaPalette('alto');
  if ($n <= 6) return mapaPalette('info');
  return mapaPalette('estable');
}

/**
 * Opción simple
 */
function getColorOpcion($num) {
  return intval($num) > 0 ? mapaPalette('estable') : (method_exists('Util','getColorNeutroMapa') ? Util::getColorNeutroMapa() : mapaPalette('neutro'));
}

/**
 * PAE: escala por rangos (como la tenías)
 */
function getColorByNumPAE($num) {
  $n = intval($num);

  if ($n <= 0) return (method_exists('Util','getColorNeutroMapa') ? Util::getColorNeutroMapa() : mapaPalette('neutro'));
  if ($n <= 3) return mapaPalette('estable'); // verde
  if ($n <= 6) return mapaPalette('info');    // azul
  if ($n <= 10) return mapaPalette('medio');  // amarillo
  if ($n <= 20) return mapaPalette('alto');   // naranja
  return mapaPalette('critico');              // rojo
}

/**
 * GOA Visitas a establecimientos: 0 / 1-28 / 29-56 / 57-84 / 85+
 */
function getColorByNumGOAVisitas($num) {
  $n = intval($num);

  if ($n <= 0)  return mapaPalette('neutro');
  if ($n <= 28) return mapaPalette('critico');
  if ($n <= 56) return mapaPalette('alto');
  if ($n <= 84) return mapaPalette('info');
  return mapaPalette('estable');
}

/**
 * PAE ArcGIS: escala por rangos para el mapa de ArcGIS
 * Colores según cantidad de sedes desde la API
 */
function getColorByNumPAEArcgis($num) {
  $n = intval($num);

  if ($n <= 0) return '#EEF2F7';   // Gris - sin datos
  if ($n <= 3) return '#10b981';   // Verde - 1-3 sedes
  if ($n <= 6) return '#3b82f6';  // Azul - 4-6 sedes
  if ($n <= 10) return '#f59e0b'; // Amarillo - 7-10 sedes
  if ($n <= 20) return '#f97316'; // Naranja - 11-20 sedes
  return '#ef4444';                 // Rojo - +20 sedes
}

?>
