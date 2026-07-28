<?php
/**
 * Controlador para gestionar el Plan de Desarrollo del Alcalde
 * Utiliza la clase DesarrolloAlcalde que trabaja con tbl_plandesarrollo_alcalde
 */

// Headers CORS
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization');

error_reporting(E_ALL);
ini_set('display_errors', 1); // Cambiado a 1 para ver errores

session_start();
require_once __DIR__ . '/../include/controller_boot.php';
ControllerGate::authorizeScript(__FILE__);

// Método GET para descarga de plantilla
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['method']) && $_GET['method'] === 'downloadTemplate') {
    require_once '../classes/DbConection.php';
    require_once '../classes/DesarrolloAlcalde.php';
    DesarrolloAlcalde::downloadTemplate();
    exit;
}

// Método POST con archivos (subir Excel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    if (ob_get_length()) ob_clean();
    try {
        require_once '../classes/DbConection.php';
        require_once '../classes/DesarrolloAlcalde.php';
        if (isset($_GET['method']) && $_GET['method'] === 'uploadExcel') {
            header('Content-Type: application/json; charset=utf-8');
            $resultado = DesarrolloAlcalde::processExcelFile($_POST, $_FILES);
            echo json_encode($resultado);
            exit;
        }
    } catch (Exception $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'output' => [
                'valid' => false,
                'message' => 'Error al procesar el archivo excel: '
            ]
        ]);
        exit;
    }
}

// Métodos JSON
$data = ControllerGate::jsonBody();

if (isset($data['method'])) {
    require_once '../classes/DbConection.php';
    require_once '../classes/DesarrolloAlcalde.php';

    switch ($data['method']) {
        case 'getAll':
            echo json_encode(DesarrolloAlcalde::getAll($data['data'] ?? []));
            break;

        case 'load':
            echo json_encode(DesarrolloAlcalde::getAllServerSide($data['data'] ?? []));
            break;

        case 'delete':
            echo json_encode(DesarrolloAlcalde::delete($data['data'] ?? []));
            break;

        case 'deleteMultiple':
            echo json_encode(DesarrolloAlcalde::deleteMultiple($data['data'] ?? []));
            break;

        default:
            echo json_encode(['error' => 'Ninguna opción válida.', 'method' => $data['method']]);
            break;
    }
} else {
    echo json_encode(['error' => 'Método no especificado']);
}
