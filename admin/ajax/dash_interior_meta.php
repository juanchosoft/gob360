<?php
// admin/ajax/dash_interior_meta.php
// GET  => obtiene la fila meta (id=1) de tbl_dash_interior_meta
// POST => guarda/actualiza la fila meta
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../include/generic_classes.php';
require_once __DIR__ . '/../include/require_permission.php';
require_once __DIR__ . '/../classes/DashInterior.php';

try {
  $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
  if ($method === 'POST') {
    requirePermission('interior.formulario.update');
  } else {
    requirePermission('interior.boletin.view');
  }
  $boletinId = isset($_GET['boletin_id']) ? (int)$_GET['boletin_id'] : (isset($_POST['boletin_id']) ? (int)$_POST['boletin_id'] : 0);

  if ($method === 'GET') {
    if ($boletinId > 0) {
      echo json_encode(DashInterior::getBulletinMeta($boletinId));
    } else {
      echo json_encode(DashInterior::getMeta());
    }
    exit;
  }

  if ($method === 'POST') {
    $data = [
      'anio_1'                   => $_POST['anio_1']                    ?? '',
      'anio_2'                   => $_POST['anio_2']                    ?? '',
      'boletin_no'               => $_POST['boletin_no']                ?? null,
      'fecha_cierre'             => $_POST['fecha_cierre']              ?? '',
      'fuente'                   => $_POST['fuente']                    ?? '',
      'tasa_homicidios'          => $_POST['tasa_homicidios']           ?? '',
      'municipios_sin_homicidios'=> $_POST['municipios_sin_homicidios'] ?? 0,
      'nota_html'                => $_POST['nota_html']                 ?? '',
    ];

    if ($boletinId > 0) {
      echo json_encode(DashInterior::saveBulletinMeta($boletinId, $data));
    } else {
      echo json_encode(DashInterior::saveMeta($data));
    }
    exit;
  }

  echo json_encode(['ok'=>false,'msg'=>'Método no permitido']);
} catch (Throwable $e) {
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
