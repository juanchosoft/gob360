<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../include/generic_classes.php';
require_once __DIR__ . '/../include/require_permission.php';
requirePermission('interior.boletin.view');
require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/DashInterior.php';

$boletinId = isset($_GET['boletin_id']) ? (int)$_GET['boletin_id'] : 0;

/* ======================================================
   ✅ HELPERS PARA CALCULAR FACTORES DESDE LOS DATASETS
====================================================== */
function norm_key($s){
  $s = (string)$s;
  $s = mb_strtolower(trim($s), 'UTF-8');
  $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
  $s = preg_replace('/\s+/', ' ', $s);
  return $s;
}

function find_cat_index(array $cats, string $needle){
  $needleN = norm_key($needle);

  foreach ($cats as $i => $c){
    if (norm_key($c) === $needleN) return (int)$i;
  }
  foreach ($cats as $i => $c){
    if (strpos(norm_key($c), $needleN) !== false) return (int)$i;
  }
  return -1;
}

function serie_val(array $serie, int $idx): int {
  if ($idx < 0) return 0;
  return (int)($serie[$idx] ?? 0);
}

/* ======================================================
   ✅ CALCULA FACTORES AUTOMÁTICOS DESDE $res (payload)
====================================================== */
function apply_auto_factors(array $res): array {

  $meta     = (isset($res['meta']) && is_array($res['meta'])) ? $res['meta'] : [];
  $datasets = (isset($res['datasets']) && is_array($res['datasets'])) ? $res['datasets'] : [];
  $factors  = (isset($res['factors']) && is_array($res['factors'])) ? $res['factors'] : [];

  $anio2 = (int)($meta['anio_2'] ?? 2026);

  // --- 1) Sicariato = S/DER POLITICO del gráfico 7 (año 2) ---
  if(isset($datasets['sicariato']) && is_array($datasets['sicariato'])){

    $catsS = isset($datasets['sicariato']['cats']) ? (array)$datasets['sicariato']['cats'] : [];
    $s2S   = isset($datasets['sicariato']['serie_anio_2']) ? (array)$datasets['sicariato']['serie_anio_2'] : [];

    $iPol   = find_cat_index($catsS, 'S/DER POLITICO');
    $valSic = serie_val($s2S, $iPol);

    if(!isset($factors['sicariato']) || !is_array($factors['sicariato'])) $factors['sicariato'] = [];
    $factors['sicariato']['valor'] = $valSic;

    if(empty($factors['sicariato']['titulo_html'])){
      $factors['sicariato']['titulo_html'] =
        '”Homicidio por Sicariato”<br><span style=”color:#666”>S/DER POLITICO</span> <span style=”color:#d32f2f”>'.$anio2.'</span>';
    }

    if(empty($factors['sicariato']['texto_html'])){
      $factors['sicariato']['texto_html'] =
        'Cálculo automático '.$anio2.': <b>S/DER POLITICO</b> = <b>'.$valSic.'</b>.';
    }
  }

  // --- 2) Intolerancia = S/DER POLITICO del gráfico 8 (año 2) ---
  if(isset($datasets['intolerancia']) && is_array($datasets['intolerancia'])){

    $catsI = isset($datasets['intolerancia']['cats']) ? (array)$datasets['intolerancia']['cats'] : [];
    $s2I   = isset($datasets['intolerancia']['serie_anio_2']) ? (array)$datasets['intolerancia']['serie_anio_2'] : [];

    $iDes   = find_cat_index($catsI, 'S/DER POLITICO');
    $valInt = serie_val($s2I, $iDes);

    if(!isset($factors['intolerancia']) || !is_array($factors['intolerancia'])) $factors['intolerancia'] = [];
    $factors['intolerancia']['valor'] = $valInt;

    if(empty($factors['intolerancia']['titulo_html'])){
      $factors['intolerancia']['titulo_html'] =
        '“Homicidios por<br><span style="color:#d32f2f">Intolerancia</span>”<br><span style="color:#666">S/DER POLITICO</span> <span style="color:#d32f2f">'.$anio2.'</span>';
    }

    if(empty($factors['intolerancia']['texto_html'])){
      $factors['intolerancia']['texto_html'] =
        'Cálculo automático '.$anio2.': <b>S/DER POLITICO</b> = <b>'.$valInt.'</b>.';
    }
  }

  // Guardar cambios al payload
  $res['factors'] = $factors;
  return $res;
}

/* ======================================================
   ✅ MAIN
====================================================== */
try {

  $boletinId = isset($_GET['boletin_id']) ? (int)$_GET['boletin_id'] : 0;

  if ($boletinId > 0) {
    $res = DashInterior::getPayloadWithBulletin($boletinId);
  } else {
    $res = DashInterior::getPayload(); // comparativo (año1 vs año2)
  }

  if(!is_array($res)){
    echo json_encode(['ok'=>false,'msg'=>'Respuesta inválida']);
    exit;
  }

  if(empty($res['ok'])){
    echo json_encode($res);
    exit;
  }

  // ✅ Aplicar cálculo automático de factores basado en datasets
  $res = apply_auto_factors($res);

  echo json_encode($res);

} catch (Throwable $e) {
  echo json_encode([
    'ok'  => false,
    'msg' => 'Error en payload: ' . $e->getMessage()
  ]);
}
