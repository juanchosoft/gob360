<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/Util.php';
require_once __DIR__ . '/../classes/Inversion.php';

$result = Inversion::getDashboardData();

if ($result['output']['valid']) {
    $data = $result['output']['response'];
    echo json_encode([
        'status'    => true,
        'kpi'       => $data['kpi'],
        'tipo'      => $data['tipo'],
        'municipio' => $data['municipio'],
    ]);
} else {
    echo json_encode([
        'status' => false,
        'error'  => $result['output']['response'] ?? 'Error',
    ]);
}
