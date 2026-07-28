<?php
// Configuración de base de datos
$pdo = new PDO("mysql:host=localhost;dbname=santandernew;charset=utf8", "root", "");

// Consulta segura
$stmt = $pdo->query("
    SELECT tbl_plandesarrollo.*, tbl_secretarias.secretaria, tbl_secretarias.secretario
    FROM tbl_plandesarrollo
    INNER JOIN tbl_secretarias ON tbl_plandesarrollo.tbl_secretaria_id = tbl_secretarias.id
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar resultados internamente
$totalProductos = count($data);
$productos = array_column($data, 'producto_servicio_pdd');

$resumen = "Hay un total de $totalProductos productos en el plan de desarrollo. Algunos de ellos son: " . implode(", ", array_slice($productos, 0, 3)) . ".";

// Guardar para TTS y para OpenAI
file_put_contents("respuesta.txt", $resumen);
echo $resumen;
