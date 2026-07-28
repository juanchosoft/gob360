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
            require_once '../classes/Ministerios.php';
            $user = new Ministerios();
            echo json_encode($user->load($data['data']));
            break;
        case 'getMinisterio':
            require_once '../classes/Ministerios.php';
            $user = new Ministerios();
            echo json_encode($user->getMinisterio($data['data']));
            break;
        case 'updateMinisterio':
            require_once '../classes/Ministerios.php';
            $user = new Ministerios();
            echo json_encode($user->updateMinisterio($data['data']));
            break;
        case 'createMinisterio':
            require_once '../classes/Ministerios.php';
            $user = new Ministerios();
            echo json_encode($user->createMinisterio($data['data']));
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
