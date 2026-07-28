<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require 'admin/classes/Util.php';
require 'admin/classes/DbConection.php';
require 'admin/classes/ProyectosAlcaldias.php';
require 'admin/classes/SessionData.php'; 


if (!$view) {
    http_response_code(403);
    die("Permiso denegado.");
}

if (!isset($_SESSION['session_user']['id'])) {
    http_response_code(401);
    die("Acceso no autorizado.");
}
 */
$proyectoId = $_GET['id'] ?? null;
$nombreprocto = $_GET['proyecto'] ?? null;

/* if (!$proyectoId) {
    http_response_code(400);
    die("ID de proyecto no especificado.");
} */

$logs = ProyectosAlcaldias::getLogsByProyectoId($proyectoId);



//cabecera
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="logs_proyecto_' . $proyectoId . '_' . date('Y-m-d') . '.txt"');

if (isset($logs['output']['valid']) && $logs['output']['valid']) {
    $fileContent = "Reporte de Logs para el Proyecto ID: " . $proyectoId . " - " .$nombreprocto . date('Y-m-d H:i:s') . "\n\n";
    $fileContent .= "---------------------------------------------------------------------------\n\n";

    foreach ($logs['output']['response'] as $log) {
        $fileContent .= "Fecha: " . $log['dtcreated'] . "\n";
        $fileContent .= "Porcentaje de Ejecución: " . $log['porcentaje_ejecucion'] . "%\n";
        $fileContent .= "Observación: " . $log['observacion'] . "\n";
        $fileContent .= "Usuario: " . $log['usuario'] . "\n";
        $fileContent .= "---------------------------------------------------------------------------\n\n";
    }
    echo $fileContent;
} else {
    echo "No se encontraron logs para este proyecto.";//no imprime nada
}
exit;