<?php
/**
 * Script de prueba para verificar la conexión a BD y obtención de municipios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test: Obtener Municipio por Código</h1>";

require_once './admin/classes/DbConection.php';

$codigo = '50006'; // ABREGO según la URL de tu screenshot

echo "<h2>Test 1: Conexión a Base de Datos</h2>";

try {
    $db = new DbConection();
    $pdo = $db->openConect();
    echo "<p style='color: green;'>✅ Conexión establecida correctamente</p>";

    // Verificar que la base de datos esté seleccionada
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    echo "<p><strong>Base de datos activa:</strong> " . ($dbName ?: '<em>NINGUNA</em>') . "</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al conectar: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

echo "<h2>Test 2: Buscar Municipio con Código " . htmlspecialchars($codigo) . "</h2>";

try {
    $stmt = $pdo->prepare("SELECT codigo_muncipio, municipio FROM tbl_ciudades_accion_unificada WHERE codigo_muncipio = :codigo LIMIT 1");
    $stmt->execute([':codigo' => $codigo]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        echo "<p style='color: green;'>✅ Municipio encontrado:</p>";
        echo "<ul>";
        echo "<li><strong>Código:</strong> " . htmlspecialchars($resultado['codigo_muncipio']) . "</li>";
        echo "<li><strong>Nombre:</strong> " . htmlspecialchars($resultado['municipio']) . "</li>";

        $nombreNormalizado = strtoupper(str_replace(' ', '_', $resultado['municipio']));
        echo "<li><strong>Nombre Normalizado (para ArcGIS):</strong> <code>" . htmlspecialchars($nombreNormalizado) . "</code></li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ No se encontró municipio con código: " . htmlspecialchars($codigo) . "</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error PDO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>Test 3: Listar Todos los Municipios de Santander</h2>";

try {
    $stmt = $pdo->query("SELECT codigo_muncipio, municipio FROM tbl_ciudades_accion_unificada WHERE tbl_departamento_id = 1 ORDER BY municipio LIMIT 10");
    $municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<p>Mostrando primeros 10 municipios:</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Código</th><th>Nombre</th><th>Normalizado</th></tr>";

    foreach ($municipios as $mun) {
        $normalizado = strtoupper(str_replace(' ', '_', $mun['municipio']));
        echo "<tr>";
        echo "<td>" . htmlspecialchars($mun['codigo_muncipio']) . "</td>";
        echo "<td>" . htmlspecialchars($mun['municipio']) . "</td>";
        echo "<td><code>" . htmlspecialchars($normalizado) . "</code></td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$db->closeConect();

echo "<hr>";
echo "<p><a href='pae_arcgis_dash.php?mun=todos'>← Volver al Dashboard PAE ArcGIS</a></p>";
?>
