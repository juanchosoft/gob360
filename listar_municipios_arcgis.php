<?php
/**
 * Script para obtener la lista de municipios disponibles en ArcGIS API
 * y compararlos con los municipios de la base de datos local
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Municipios Disponibles en ArcGIS API - PAE</h1>";

// URL de la API
$apiBase = 'https://services7.arcgis.com/EUSBYj1PuyMxVZIb/arcgis/rest/services/service_5c644d64f8cf402f90600c6021e4b182/FeatureServer/0';

// Método 1: Obtener valores únicos de municipios directamente de la API
echo "<h2>Método 1: Consulta de valores únicos (returnDistinctValues)</h2>";

$params = [
    'where' => '1=1',
    'outFields' => 'Municipio',
    'returnDistinctValues' => 'true',
    'returnGeometry' => 'false',
    'f' => 'json'
];

$url = $apiBase . '/query?' . http_build_query($params);

echo "<p><strong>URL:</strong> <code>" . htmlspecialchars($url) . "</code></p>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$executionTime = round(($endTime - $startTime) * 1000, 2);

echo "<p><strong>Tiempo de ejecución:</strong> {$executionTime} ms</p>";
echo "<p><strong>HTTP Code:</strong> {$httpCode}</p>";

if ($httpCode !== 200) {
    echo "<p style='color: red;'>❌ Error HTTP {$httpCode}</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    exit;
}

$apiData = json_decode($response, true);

if (!$apiData || !isset($apiData['features'])) {
    echo "<p style='color: red;'>❌ Error al decodificar JSON o estructura inválida</p>";
    exit;
}

// Extraer municipios únicos
$municipiosArcgis = [];
foreach ($apiData['features'] as $feature) {
    if (isset($feature['attributes']['Municipio'])) {
        $municipio = $feature['attributes']['Municipio'];
        if (!in_array($municipio, $municipiosArcgis)) {
            $municipiosArcgis[] = $municipio;
        }
    }
}

sort($municipiosArcgis);

echo "<h3>✅ Municipios encontrados en ArcGIS: " . count($municipiosArcgis) . "</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin-top: 10px;'>";
echo "<tr style='background-color: #f0f0f0;'><th>#</th><th>Nombre en ArcGIS</th><th>Nombre Normalizado</th></tr>";

foreach ($municipiosArcgis as $index => $municipio) {
    $nombreNormalizado = str_replace('_', ' ', $municipio);
    $nombreNormalizado = ucwords(strtolower($nombreNormalizado));

    echo "<tr>";
    echo "<td>" . ($index + 1) . "</td>";
    echo "<td><code>" . htmlspecialchars($municipio) . "</code></td>";
    echo "<td>" . htmlspecialchars($nombreNormalizado) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Método 2: Comparar con base de datos local
echo "<hr>";
echo "<h2>Método 2: Comparación con Base de Datos Local</h2>";

// Conectar a base de datos
require_once 'admin/classes/DbConection.php';

$db = new DbConection();
$pdo = $db->openConect();

$stmt = $pdo->query("SELECT codigo_muncipio, municipio FROM tbl_ciudades_accion_unificada WHERE tbl_departamento_id = 1 ORDER BY municipio");
$municipiosLocal = $stmt->fetchAll(PDO::FETCH_ASSOC);

$db->closeConect();

echo "<h3>Municipios en Base de Datos Local: " . count($municipiosLocal) . "</h3>";

// Comparar
$coincidencias = [];
$noEncontrados = [];
$soloEnArcgis = array_map('strtoupper', array_map(function($m) { return str_replace('_', ' ', $m); }, $municipiosArcgis));

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin-top: 10px;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Código DANE</th><th>Nombre Local</th><th>Nombre ArcGIS</th><th>Estado</th>";
echo "</tr>";

foreach ($municipiosLocal as $munLocal) {
    $nombreLocalNormalizado = strtoupper(str_replace(' ', '_', $munLocal['municipio']));

    if (in_array($nombreLocalNormalizado, $municipiosArcgis)) {
        $coincidencias[] = $munLocal;
        echo "<tr style='background-color: #d4edda;'>";
        echo "<td>" . htmlspecialchars($munLocal['codigo_muncipio']) . "</td>";
        echo "<td>" . htmlspecialchars($munLocal['municipio']) . "</td>";
        echo "<td><code>" . htmlspecialchars($nombreLocalNormalizado) . "</code></td>";
        echo "<td>✅ Coincide</td>";
        echo "</tr>";
    } else {
        $noEncontrados[] = $munLocal;
        echo "<tr style='background-color: #f8d7da;'>";
        echo "<td>" . htmlspecialchars($munLocal['codigo_muncipio']) . "</td>";
        echo "<td>" . htmlspecialchars($munLocal['municipio']) . "</td>";
        echo "<td><em>No encontrado en ArcGIS</em></td>";
        echo "<td>❌ No disponible</td>";
        echo "</tr>";
    }
}

echo "</table>";

// Resumen
echo "<hr>";
echo "<h2>Resumen de Comparación</h2>";
echo "<ul>";
echo "<li><strong>Total municipios en ArcGIS:</strong> " . count($municipiosArcgis) . "</li>";
echo "<li><strong>Total municipios en BD Local:</strong> " . count($municipiosLocal) . "</li>";
echo "<li><strong style='color: green;'>✅ Coincidencias:</strong> " . count($coincidencias) . "</li>";
echo "<li><strong style='color: red;'>❌ No encontrados en ArcGIS:</strong> " . count($noEncontrados) . "</li>";
echo "</ul>";

if (count($noEncontrados) > 0) {
    echo "<h3>Municipios en BD Local pero NO en ArcGIS:</h3>";
    echo "<ul>";
    foreach ($noEncontrados as $mun) {
        echo "<li>" . htmlspecialchars($mun['municipio']) . " (Código: " . htmlspecialchars($mun['codigo_muncipio']) . ")</li>";
    }
    echo "</ul>";
}

// Generar código SQL para actualizar
echo "<hr>";
echo "<h2>Código de Integración Sugerido</h2>";

echo "<h3>Opción 1: Array PHP para validación</h3>";
echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars("<?php\n");
echo "// Municipios disponibles en ArcGIS API\n";
echo "\$municipiosArcgis = [\n";
foreach ($municipiosArcgis as $mun) {
    echo "    '" . $mun . "',\n";
}
echo "];\n";
echo htmlspecialchars("?>");
echo "</pre>";

echo "<h3>Opción 2: Query SQL para filtrar municipios en dropdown</h3>";
echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px;'>";
$municipiosParaSQL = array_map(function($m) use ($municipiosArcgis) {
    $normalizado = strtoupper(str_replace(' ', '_', $m['municipio']));
    return in_array($normalizado, $municipiosArcgis) ? $m['codigo_muncipio'] : null;
}, $municipiosLocal);
$municipiosParaSQL = array_filter($municipiosParaSQL);

echo "SELECT codigo_muncipio, municipio \n";
echo "FROM tbl_ciudades_accion_unificada \n";
echo "WHERE codigo_muncipio IN (\n";
echo "    '" . implode("',\n    '", $municipiosParaSQL) . "'\n";
echo ")\n";
echo "ORDER BY municipio;";
echo "</pre>";

echo "<hr>";
echo "<p><a href='pae_arcgis_dash.php?mun=todos'>← Volver al Dashboard PAE ArcGIS</a></p>";
?>
