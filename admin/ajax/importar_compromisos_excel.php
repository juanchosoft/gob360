<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../classes/SessionData.php';
require_once '../classes/Util.php';
require_once '../classes/Visitas.php';

$userType = SessionData::getUserType();
if (!in_array($userType, [Util::Administrador(), Util::SuperAdministrador()], true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado. No tiene permisos para importar compromisos.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

$response = (new Visitas())->importarCompromisosExcel($_POST, $_FILES);

echo json_encode($response);
