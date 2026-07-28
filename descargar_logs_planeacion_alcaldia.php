<?php
/**
 * Descarga de logs para proyectos de planeación de alcaldía
 * @author SPIDERSOFTWARE
 */
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require 'admin/classes/Util.php';
require 'admin/classes/DbConection.php';
require 'admin/classes/Ingreso_proyectos_secretarias.php';
require 'admin/classes/SessionData.php';

// Verificar sesión de usuario
if (!isset($_SESSION['session_user']['id'])) {
    http_response_code(401);
    die("Acceso no autorizado.");
}

// Verificar si es usuario tipo Alcalde (sin requerir permiso específico)
$user_rol = $_SESSION['session_user']['tipo'] ?? '';
$tiposUsuarioAlcalde = [
    'Alcalde',
    'Auxiliar_Alcalde',
    'Secretario_Despacho_Alcalde',
    'Auxiliar_Secretario_Despacho_Alcalde'
];

if (!in_array($user_rol, $tiposUsuarioAlcalde)) {
    http_response_code(403);
    die("Permiso denegado. Solo usuarios de alcaldía pueden acceder.");
}

$proyectoId = $_GET['id'] ?? null;
$nombreProyecto = $_GET['proyecto'] ?? 'Sin nombre';

if (!$proyectoId) {
    http_response_code(400);
    die("ID de proyecto no especificado.");
}

// Obtener logs usando el método de Proyectos_Secretarias
$logs = Proyectos_Secretarias::obtenerLogsProyecto($proyectoId);

// Cabeceras para descarga de archivo
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="logs_proyecto_' . $proyectoId . '_' . date('Y-m-d') . '.txt"');

if (isset($logs['output']['valid']) && $logs['output']['valid'] && !empty($logs['output']['response'])) {
    $fileContent = "========================================================================\n";
    $fileContent .= "REPORTE DE HISTORIAL - PROYECTO PLANEACIÓN ALCALDÍA\n";
    $fileContent .= "========================================================================\n\n";
    $fileContent .= "Proyecto: " . $nombreProyecto . "\n";
    $fileContent .= "ID Proyecto: " . $proyectoId . "\n";
    $fileContent .= "Fecha del reporte: " . date('Y-m-d H:i:s') . "\n";
    $fileContent .= "------------------------------------------------------------------------\n\n";

    foreach ($logs['output']['response'] as $log) {
        $fileContent .= "Fecha: " . ($log['dtcreated'] ?? 'N/A') . "\n";
        $fileContent .= "Acción: " . ($log['accion'] ?? 'N/A') . "\n";
        $fileContent .= "Observación: " . ($log['observacion'] ?? 'Sin observación') . "\n";
        $fileContent .= "Usuario: " . ($log['usuario'] ?? 'N/A') . "\n";
        if (!empty($log['documento_ruta'])) {
            $fileContent .= "Documento: " . $log['documento_ruta'] . "\n";
        }
        $fileContent .= "------------------------------------------------------------------------\n\n";
    }

    $fileContent .= "========================================================================\n";
    $fileContent .= "FIN DEL REPORTE\n";
    $fileContent .= "========================================================================\n";

    echo $fileContent;
} else {
    echo "No se encontraron registros de historial para este proyecto.";
}

exit;
?>
