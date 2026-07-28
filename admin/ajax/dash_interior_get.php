<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../include/generic_classes.php';
require_once __DIR__ . '/../include/require_permission.php';
requirePermission('interior.boletin.view');
require_once __DIR__ . '/../classes/DashInterior.php';

try {
  $boletinId = isset($_GET['boletin_id']) ? (int)$_GET['boletin_id'] : 0;
  $anio      = isset($_GET['anio']) ? (int)$_GET['anio'] : 0;

  if ($boletinId > 0) {
    $payload = DashInterior::getPayloadForBulletin($boletinId);
  } elseif ($anio > 0) {
    $payload = DashInterior::getPayloadForYear($anio);
  } else {
    $payload = DashInterior::getPayloadForYear(2026);
  }

  echo json_encode($payload);
} catch (Throwable $e) {
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
