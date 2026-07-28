<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../include/generic_classes.php';
require_once __DIR__ . '/../include/require_permission.php';
requirePermission('interior.formulario.update');
require_once __DIR__ . '/../classes/DashInterior.php';

try {
  $action = trim((string)($_POST['action'] ?? ''));

  if ($action === 'save_boletin') {
    $cardKey = $_POST['card_key'] ?? '';
    $anio    = (int)($_POST['anio'] ?? 0);
    $boletinId = isset($_POST['boletin_id']) ? (int)$_POST['boletin_id'] : 0;
    $values  = $_POST['values'] ?? '';
    $factor  = trim((string)($_POST['factor_atencion'] ?? ''));

    if (is_array($cardKey)) $cardKey = (string)($cardKey[0] ?? '');
    $cardKey = trim((string)$cardKey);

    if (is_string($values)) {
      $arr = json_decode($values, true);
      if (!is_array($arr)) {
        echo json_encode(['ok'=>false,'msg'=>'Values inválidos (JSON)']);
        exit;
      }
    } elseif (is_array($values)) {
      $arr = $values;
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Values inválidos']);
      exit;
    }

    // Guardar en boletín diario o en año global
    if ($boletinId > 0) {
      $res = DashInterior::saveBulletinDailyValues($boletinId, $cardKey, $arr, $factor);
    } elseif ($anio > 0 && $cardKey !== '') {
      $res = DashInterior::saveBoletinValues($cardKey, $anio, $arr, $factor);
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Datos incompletos (boletin_id o card_key/anio)']);
      exit;
    }

    echo json_encode($res);
    exit;
  }

  if ($action === 'create_boletin') {
    $fecha = trim((string)($_POST['fecha'] ?? date('Y-m-d')));
    $res = DashInterior::createBulletin($fecha);
    echo json_encode($res);
    exit;
  }

  if ($action === 'list_boletines') {
    $res = DashInterior::getBulletins(500);
    echo json_encode($res);
    exit;
  }

  if ($action === 'set_active_boletin') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['ok'=>false,'msg'=>'ID inválido']); exit; }
    $res = DashInterior::setActiveBulletin($id);
    echo json_encode($res);
    exit;
  }

  echo json_encode(['ok'=>false,'msg'=>'Acción no válida']);
} catch (Throwable $e) {
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
