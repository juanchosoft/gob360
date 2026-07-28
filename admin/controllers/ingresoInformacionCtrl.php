<?php
require_once __DIR__ . '/../include/controller_boot.php';
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');


$data = ControllerGate::jsonBody();
ControllerGate::authorizeScript(__FILE__, $data['method'] ?? '');
/**
 * controller ingresoInformacion
 */

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'load':
            require_once '../classes/IngresoInformacion.php';
            $user = new IngresoInformacion();
            $user->getAll($data['data']);
            //echo json_encode($user->getAll($data['data']));
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
