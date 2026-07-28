<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../classes/DbConection.php';

    $db = new DbConection();
    $pdo = $db->openConect();

    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos.');
    }

    // CAMBIA ESTE NOMBRE POR TU BASE DE DATOS REAL
    $databaseName = 'santaok';

    $pdo->exec("USE `{$databaseName}`");

    $sql = "SELECT 
                d.id,
                d.nombre,
                d.cc,
                d.tipo_deportista,
                d.valor,
                d.plazo,
                d.img,
                dis.disciplina,
                l.liga
            FROM tbl_deportistas d
            LEFT JOIN tbl_disciplina dis ON d.tbl_disciplina_id = dis.id
            LEFT JOIN tbl_ligas l ON d.tbl_liga_id = l.id
            ORDER BY d.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $row) {
        $valor = '$ ' . number_format((float)($row['valor'] ?? 0), 0, ',', '.');

        $foto = '<span class="sin-foto">Sin foto</span>';
        if (!empty($row['img'])) {
            $img = htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
            $foto = '<img src="' . $img . '" class="foto-mini" alt="Foto">';
        }

        $data[] = [
            'nombre'          => $row['nombre'] ?? '',
            'cc'              => $row['cc'] ?? '',
            'disciplina'      => $row['disciplina'] ?? '',
            'tipo_deportista' => $row['tipo_deportista'] ?? '',
            'liga'            => $row['liga'] ?? '',
            'valor'           => $valor,
            'plazo'           => $row['plazo'] ?? '',
            'foto'            => $foto
        ];
    }

    echo json_encode([
        'ok'   => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'   => false,
        'msg'  => $e->getMessage(),
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}