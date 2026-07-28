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
            require_once '../classes/Acciong.php';
            $user = new Acciong();
            echo json_encode($user->load($data['data']));
            break;
        case 'getAccionG':
            require_once '../classes/Acciong.php';
            $user = new Acciong();
            echo json_encode($user->getAccionG($data['data']));
            break;
        case 'updateAccionG':
            require_once '../classes/Acciong.php';
            $user = new Acciong();
            echo json_encode($user->updateAccionG($data['data']));
            break;
        case 'createAccionG':
            require_once '../classes/Acciong.php';
            $user = new Acciong();
            echo json_encode($user->createAccionG($data['data']));
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
