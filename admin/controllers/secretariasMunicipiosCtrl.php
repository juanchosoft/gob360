<?php
session_start();
require_once __DIR__ . '/../include/controller_boot.php';

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
header('Content-Type: application/json; charset=utf-8');

$data = ControllerGate::jsonBody();
ControllerGate::authorizeScript(__FILE__, $data['method'] ?? '');

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'load':
            require_once '../classes/SecretariasMunicipios.php';
            $secretariasMun = new SecretariasMunicipios();
            echo json_encode($secretariasMun->load($data['data']));
            break;
        case 'newSecretaria':
            require_once '../classes/SecretariasMunicipios.php';
            $secretariasMun = new SecretariasMunicipios();
            echo json_encode($secretariasMun->newSecretaria($data['data']));
            break;
        case 'editSecretaria':
            require_once '../classes/SecretariasMunicipios.php';
            $secretariasMun = new SecretariasMunicipios();
            echo json_encode($secretariasMun->editSecretaria($data['data']));
            break;
        case 'updateSecretaria':
            require_once '../classes/SecretariasMunicipios.php';
            $secretariasMun = new SecretariasMunicipios();
            echo json_encode($secretariasMun->updateSecretaria($data['data']));
            break;
        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
