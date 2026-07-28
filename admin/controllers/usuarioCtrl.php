<?php
ob_start();
session_start();

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../include/controller_boot.php';

$jsonData = ControllerGate::jsonBody();
$method = '';
if (!empty($_POST['method'])) {
    $method = (string) $_POST['method'];
} elseif (!empty($jsonData['method'])) {
    $method = (string) $jsonData['method'];
}

ControllerGate::authorizeScript(__FILE__, $method);

require_once __DIR__ . '/../classes/Usuario.php';

switch ($method) {
    case 'ingresaUsuario':
        $_POST['tbl_secretarias_id'] = (empty($_POST['tbl_secretarias_id']) || $_POST['tbl_secretarias_id'] == 'Seleccione') ? 0 : $_POST['tbl_secretarias_id'];
        $_POST['tbl_municipio_id'] = (empty($_POST['tbl_municipio_id']) || $_POST['tbl_municipio_id'] == 'Seleccione') ? 0 : $_POST['tbl_municipio_id'];
        $user = new Usuario();
        echo json_encode($user->save($_POST, $_FILES));
        break;

    case 'editUserSave':
        $_POST['tbl_secretarias_id'] = (empty($_POST['tbl_secretarias_id']) || $_POST['tbl_secretarias_id'] == 'Seleccione') ? 0 : $_POST['tbl_secretarias_id'];
        $_POST['tbl_municipio_id'] = (empty($_POST['tbl_municipio_id']) || $_POST['tbl_municipio_id'] == 'Seleccione') ? 0 : $_POST['tbl_municipio_id'];
        $user = new Usuario();
        echo json_encode($user->editUserSave($_POST, $_FILES));
        break;

    case 'load':
        $user = new Usuario();
        echo json_encode($user->getAll($jsonData['data'] ?? []));
        break;

    case 'editUser':
        $user = new Usuario();
        echo json_encode($user->editUser($jsonData['data'] ?? []));
        break;

    case 'RestablecerContrasena':
        $user = new Usuario();
        echo json_encode($user->RestablecerContrasena($jsonData['data'] ?? []));
        break;

    case 'actualizaContrasena':
        $user = new Usuario();
        echo json_encode($user->actualizaContrasena($jsonData['data'] ?? []));
        break;

    case 'getAllInicioSession':
        $user = new Usuario();
        echo json_encode($user->getAllInicioSession($jsonData['data'] ?? []));
        break;

    case 'deleteUser':
        echo json_encode(Usuario::deleteUser($jsonData['data'] ?? []));
        break;

    case 'getDeletedUsers':
        echo json_encode(Usuario::getDeletedUsers($jsonData['data'] ?? []));
        break;

    case 'getDuplicatedUsers':
        echo json_encode(Usuario::getDuplicatedUsers($jsonData['data'] ?? []));
        break;

    default:
        echo json_encode(Util::error_general('Operación no válida.'));
        break;
}
