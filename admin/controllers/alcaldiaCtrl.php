<?php
require_once __DIR__ . '/../include/controller_boot.php';
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$data = ControllerGate::jsonBody();
ControllerGate::authorizeScript(__FILE__, $data['method'] ?? '');
/* controller alcaldias */
if (isset($data['method'])) {
    switch ($data['method']) {
        case 'getAllproyectos':
            require_once '../classes/Ministeriospro.php';
            $user = new Ministeriospro();
            echo json_encode($user->getAllproyectos($data['data']));
            break;
        case 'delete':
            require_once '../classes/Ministeriospro.php';
            $user = new Ministeriospro();
            echo json_encode($user->deleteProyecto($data['data']));
            break;
        case 'editProyecto':
            require_once '../classes/Ministeriospro.php';
            $user = new Ministeriospro();
            header('Content-Type: application/json');
            echo json_encode($user->editProyecto($data['data']));
            break;
        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
