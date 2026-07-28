<?php
/**
 * Script de prueba para verificar conexión con API de ArcGIS
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Conexión API ArcGIS - PAE</h1>";

// URL de la API
$apiBase = 'https://services7.arcgis.com/EUSBYj1PuyMxVZIb/arcgis/rest/services/service_5c644d64f8cf402f90600c6021e4b182/FeatureServer/0';

// Parámetros de consulta
$params = [
    'where' => '1=1',
    'outFields' => '*',
    'returnGeometry' => 'false',
    'f' => 'json',
    'resultRecordCount' => 2
];

$url = $apiBase . '/query?' . http_build_query($params);

echo "<h2>1. URL de Consulta:</h2>";
echo "<pre>" . htmlspecialchars($url) . "</pre>";

echo "<h2>2. Ejecutando petición cURL...</h2>";

// Ejecutar petición con cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_errno($ch);
$curlErrorMsg = curl_error($ch);

curl_close($ch);

$executionTime = round(($endTime - $startTime) * 1000, 2);

echo "<h2>3. Información de la Petición:</h2>";
echo "<ul>";
echo "<li><strong>HTTP Code:</strong> " . $httpCode . "</li>";
echo "<li><strong>Tiempo de ejecución:</strong> " . $executionTime . " ms</li>";
echo "<li><strong>cURL Error:</strong> " . ($curlError ? $curlErrorMsg : "Ninguno") . "</li>";
echo "<li><strong>Tamaño respuesta:</strong> " . strlen($response) . " bytes</li>";
echo "</ul>";

if ($httpCode !== 200) {
    echo "<h2 style='color: red;'>❌ ERROR: HTTP Code " . $httpCode . "</h2>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    exit;
}

if ($curlError) {
    echo "<h2 style='color: red;'>❌ ERROR cURL: " . $curlErrorMsg . "</h2>";
    exit;
}

echo "<h2>4. Respuesta Cruda (primeros 2000 caracteres):</h2>";
echo "<pre>" . htmlspecialchars(substr($response, 0, 2000)) . "...</pre>";

// Decodificar JSON
$apiData = json_decode($response, true);

if (!$apiData) {
    echo "<h2 style='color: red;'>❌ ERROR: No se pudo decodificar JSON</h2>";
    echo "<pre>json_last_error: " . json_last_error_msg() . "</pre>";
    exit;
}

echo "<h2>5. Estructura de la Respuesta:</h2>";
echo "<ul>";
echo "<li><strong>Claves principales:</strong> " . implode(', ', array_keys($apiData)) . "</li>";

if (isset($apiData['features'])) {
    echo "<li><strong>Total de features:</strong> " . count($apiData['features']) . "</li>";
}

if (isset($apiData['fields'])) {
    echo "<li><strong>Total de campos definidos:</strong> " . count($apiData['fields']) . "</li>";
}

echo "</ul>";

echo "<h2>6. Campos Disponibles en la API:</h2>";
if (isset($apiData['fields'])) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Nombre</th><th>Tipo</th><th>Alias</th></tr>";
    foreach ($apiData['fields'] as $field) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($field['name']) . "</td>";
        echo "<td>" . htmlspecialchars($field['type']) . "</td>";
        echo "<td>" . htmlspecialchars($field['alias']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>7. Primer Registro (Feature):</h2>";
if (isset($apiData['features'][0])) {
    $firstFeature = $apiData['features'][0];
    echo "<h3>Attributes del primer registro:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";

    if (isset($firstFeature['attributes'])) {
        foreach ($firstFeature['attributes'] as $key => $value) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
            echo "<td>" . htmlspecialchars(is_null($value) ? 'NULL' : $value) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No se encontraron features en la respuesta</p>";
}

echo "<h2>8. Test de Filtro por Municipio (BUCARAMANGA):</h2>";

$paramsTest = [
    'where' => "Municipio='BUCARAMANGA'",
    'outFields' => 'Municipio,Sector,Cuantas_Neveras_tiene',
    'returnGeometry' => 'false',
    'f' => 'json',
    'resultRecordCount' => 5
];

$urlTest = $apiBase . '/query?' . http_build_query($paramsTest);
echo "<p>URL: <code>" . htmlspecialchars($urlTest) . "</code></p>";

$chTest = curl_init($urlTest);
curl_setopt($chTest, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chTest, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($chTest, CURLOPT_TIMEOUT, 30);

$responseTest = curl_exec($chTest);
$httpCodeTest = curl_getinfo($chTest, CURLINFO_HTTP_CODE);
curl_close($chTest);

$apiDataTest = json_decode($responseTest, true);

if ($httpCodeTest === 200 && isset($apiDataTest['features'])) {
    echo "<p style='color: green;'>✅ Se encontraron <strong>" . count($apiDataTest['features']) . "</strong> registros para BUCARAMANGA</p>";

    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Municipio</th><th>Sector</th><th>Neveras</th></tr>";
    foreach ($apiDataTest['features'] as $feat) {
        $attr = $feat['attributes'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($attr['Municipio'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($attr['Sector'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($attr['Cuantas_Neveras_tiene'] ?? '0') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error en filtro por municipio (HTTP: " . $httpCodeTest . ")</p>";
}

echo "<h2>9. Resumen:</h2>";
echo "<ul>";
echo "<li>✅ Conexión exitosa a ArcGIS API</li>";
echo "<li>✅ Respuesta válida en formato JSON</li>";
echo "<li>✅ Features disponibles: " . (isset($apiData['features']) ? count($apiData['features']) : 0) . "</li>";
echo "<li>✅ Campos definidos: " . (isset($apiData['fields']) ? count($apiData['fields']) : 0) . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='pae_arcgis_dash.php?mun=todos'>← Volver al Dashboard PAE ArcGIS</a></p>";
?>
