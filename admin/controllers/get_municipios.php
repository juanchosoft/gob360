<?php
require_once __DIR__ . '/../include/controller_boot.php';
ControllerGate::authorizeScript(__FILE__, '_entrypoint');
require_once '../classes/DbConection.php';

header('Content-Type: application/json');

try {

    $db = new DbConection();
    $pdo = $db->openConect();

    // Siempre Santander
    $dep = '68';

    $stmt = $pdo->prepare("
        SELECT municipio 
        FROM tbl_ciudades_accion_unificada 
        WHERE LEFT(codigo_municipio, 2) = ?
        ORDER BY municipio ASC
    ");

    $stmt->execute([$dep]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}