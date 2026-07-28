<?php
require_once __DIR__ . '/../include/controller_boot.php';
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');


$data = ControllerGate::jsonBody();
ControllerGate::authorizeScript(__FILE__, $data['method'] ?? '');

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'load':
            require_once '../classes/Linea.php';
            $user = new Linea();
            echo json_encode($user->load($data['data']));
            break;
        case 'getLinea':
            require_once '../classes/Linea.php';
            $user = new Linea();
            echo json_encode($user->getLinea($data['data']));
            break;
        case 'updateLinea':
            require_once '../classes/Linea.php';
            $user = new Linea();
            echo json_encode($user->updateLinea($data['data']));
            break;
        case 'createLinea':
            require_once '../classes/Linea.php';
            $user = new Linea();
            echo json_encode($user->createLinea($data['data']));
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
