<?php
require_once __DIR__ . '/../include/controller_boot.php';
error_reporting(0);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

require_once '../classes/DbConection.php';
require_once '../classes/ComponenteMunicipios.php';

$data = ControllerGate::jsonBody();
ControllerGate::authorizeScript(__FILE__, $data['method'] ?? '');

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'load':
            $componenteMun = new ComponenteMunicipios();
            echo json_encode($componenteMun->load($data['data']));
            break;

        case 'getById':
            if (isset($data['id'])) {
                echo json_encode(ComponenteMunicipios::getById($data['id']));
            } else {
                echo json_encode(['state' => false, 'message' => 'ID no proporcionado']);
            }
            break;

        case 'editComponente':
            if (isset($data['data'])) {
                $result = ComponenteMunicipios::getById($data['data']);
                if ($result['output']['valid']) {
                    echo json_encode([
                        'state' => true,
                        'data' => [$result['output']['response']]
                    ]);
                } else {
                    echo json_encode(['state' => false, 'message' => 'Componente no encontrado']);
                }
            } else {
                echo json_encode(['state' => false, 'message' => 'ID no proporcionado']);
            }
            break;

        case 'newComponente':
            if (isset($data['data'])) {
                echo json_encode(ComponenteMunicipios::save($data['data']));
            } else {
                echo json_encode(['state' => false, 'message' => 'Datos no proporcionados']);
            }
            break;

        case 'updateComponente':
            if (isset($data['data'])) {
                echo json_encode(ComponenteMunicipios::update($data['data']));
            } else {
                echo json_encode(['state' => false, 'message' => 'Datos no proporcionados']);
            }
            break;

        case 'delete':
            if (isset($data['id'])) {
                echo json_encode(ComponenteMunicipios::delete($data['id']));
            } else {
                echo json_encode(['state' => false, 'message' => 'ID no proporcionado']);
            }
            break;

        default:
            echo json_encode(['state' => false, 'message' => 'Método no válido: ' . $data['method']]);
            break;
    }
} else {
    echo json_encode(['state' => false, 'message' => 'Método no especificado']);
}
