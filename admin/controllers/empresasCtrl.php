<?php
require_once __DIR__ . '/../include/controller_boot.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');
header('Content-Type: application/json; charset=utf-8');

$data = ControllerGate::jsonBody();
ControllerGate::authorizeScript(__FILE__, $data['method'] ?? '');

if (!isset($data['method'])) {
    echo json_encode(['state' => false, 'message' => 'Método no especificado.']);
    exit;
}

switch ($data['method']) {
    case 'load':
        require_once '../classes/Empresas.php';
        echo json_encode(Empresas::load($data['data'] ?? []), JSON_UNESCAPED_UNICODE);
        break;
    case 'editEmpresa':
        require_once '../classes/Empresas.php';
        echo json_encode(Empresas::editEmpresa($data['data'] ?? 0), JSON_UNESCAPED_UNICODE);
        break;
    default:
        echo json_encode(['state' => false, 'message' => 'Opción no válida.']);
        break;
}
