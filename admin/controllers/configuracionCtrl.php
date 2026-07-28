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
            require_once '../classes/Configuracion_Puntaje.php';
            $user = new Configuracion_Puntaje();
            echo json_encode($user->load($data['data']), JSON_UNESCAPED_UNICODE);
            break;
        case 'ejes':
            require_once '../classes/Ejes.php';
            $user = new Ejes();
            echo json_encode($user->getAll($data));
            break;
        case 'categoriasInestabilidad':
            require_once '../classes/FactoresInestabilidadGobernacion.php';
            echo json_encode(FactoresInestabilidadGobernacion::getAll([]), JSON_UNESCAPED_UNICODE);
            break;
        case 'insertPuntaje':
            require_once '../classes/Configuracion_Puntaje.php';
            $user = new Configuracion_Puntaje();
            echo json_encode($user->insertPuntaje($data['data']));
            break;
        case 'editPuntaje':
            require_once '../classes/Configuracion_Puntaje.php';
            $user = new Configuracion_Puntaje();
            echo json_encode($user->editPuntaje($data['data']), JSON_UNESCAPED_UNICODE);
            break;
        case 'loadConfigSecretaria':
            require_once '../classes/ConfiguracionPuntajeSecretaria.php';
            $user = new ConfiguracionPuntajeSecretaria();
            echo json_encode($user->loadConfigSecretaria($data));
            break;
        case 'editConfigSecretaria':
            require_once '../classes/ConfiguracionPuntajeSecretaria.php';
            $user = new ConfiguracionPuntajeSecretaria();
            echo json_encode($user->editConfigSecretaria($data['data']));
            break;
        case 'configuracionSecretariaPuntajeSave':
            require_once '../classes/ConfiguracionPuntajeSecretaria.php';
            $user = new ConfiguracionPuntajeSecretaria();
            echo json_encode($user->configuracionSecretariaPuntajeSave($data['data']));
            break;
        case 'editConfiguracionSecretariaPuntajeSave':
            require_once '../classes/ConfiguracionPuntajeSecretaria.php';
            $user = new ConfiguracionPuntajeSecretaria();
            echo json_encode($user->editConfiguracionSecretariaPuntajeSave($data['data']));
            break;

        default:
            echo 'ninguna opción valida.';
            break;
    }
} else {
    echo 'ninguna opción valida.';
}
