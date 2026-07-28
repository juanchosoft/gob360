<?php
ob_start();
session_start();
require_once __DIR__ . '/../include/controller_boot.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['op'])) {
    ControllerGate::authorizeScript(__FILE__, (string) $_POST['op']);
    require_once '../classes/Inversion.php';
    $inversion = new Inversion();
    
    switch ($_POST['op']) {
        case 'inversion_save':
            $_POST['usuario_id'] = $_SESSION['session_user']['id'] ?? 1;
            echo json_encode($inversion->save($_POST, $_FILES));
            break;
        case 'inversion_update':
            $_POST['usuario_id'] = $_SESSION['session_user']['id'] ?? 1;
            echo json_encode($inversion->update($_POST, $_FILES));
            break;
        case 'inversion_delete':
            echo json_encode($inversion->delete($_POST));
            break;
        case 'inversion_get':
            echo json_encode($inversion->getById($_POST));
            break;
        default:
            echo json_encode(['output' => ['valid' => false, 'response' => 'Operación no válida']]);
            break;
    }
    exit;
}

$data = ControllerGate::jsonBody();
ControllerGate::authorizeScript(__FILE__, $data['method'] ?? '');

if (isset($data['method'])) {
    switch ($data['method']) {
        case 'inversion_list':
            require_once '../classes/DbConection.php';
            require_once '../classes/Inversion.php';
            $inversion = new Inversion();
            $result = $inversion->getAllServerSide($data['data'] ?? []);
            echo json_encode($result);
            break;
        default:
            echo json_encode([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Método no válido'
            ]);
            break;
    }
} else {
    echo json_encode([
        'draw' => 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Datos inválidos'
    ]);
}