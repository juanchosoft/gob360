<?php
require_once __DIR__ . '/../include/controller_boot.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
header('Content-Type: application/json; charset=utf-8');

$json = ControllerGate::jsonBody();
$data = !empty($_POST) ? $_POST : $json;

ControllerGate::authorizeScript(__FILE__, $data['method'] ?? '');

if (!isset($data['method'])) {
    echo json_encode(['error' => 'Opción no válida.']);
    exit;
}

require_once '../classes/ApiPolicia.php';
$user = new ApiPolicia();

switch ($data['method']) {
    case 'cargaHurto':
        echo json_encode($user->cargaHurto($data));
        break;
    case 'cargaCategoria':
        echo json_encode($user->cargaCategoria($data));
        break;
    case 'cargaCategoriaGrafico':
        echo json_encode($user->cargaCategoriaGrafico($data));
        break;
    default:
        echo json_encode(['error' => 'Método no válido.']);
        break;
}
