<?php
require_once __DIR__ . '/../classes/DbConection.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_FILES['archivo'])) {
    $proyecto_id = (int)($_POST['proyecto_id'] ?? 0);
    $fecha_avance = trim($_POST['fecha_avance'] ?? '');

    if ($proyecto_id <= 0) {
        echo json_encode(['state' => false, 'message' => 'Proyecto inválido.']);
        exit;
    }

    $uploadDir = __DIR__ . '/../../assets/img/proyectos/avances/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts)) {
        echo json_encode(['state' => false, 'message' => 'Formato de imagen no permitido.']);
        exit;
    }

    if ($_FILES['archivo']['size'] > 10 * 1024 * 1024) {
        echo json_encode(['state' => false, 'message' => 'La imagen supera el tamaño máximo (10MB).']);
        exit;
    }

    $imgName = 'avance_' . $proyecto_id . '_' . uniqid() . '.' . $ext;
    $imgTarget = $uploadDir . $imgName;

    if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $imgTarget)) {
        echo json_encode(['state' => false, 'message' => 'Error al guardar la imagen.']);
        exit;
    }

    $imgUrl = 'assets/img/proyectos/avances/' . $imgName;

    $db = new DbConection();
    $pdo = $db->openConect();

    $stmt = $pdo->prepare("INSERT INTO " . $db->getTable('au_proyectos_imagenes') . " (proyecto_id, imagen_url, fecha_avance) VALUES (:proyecto_id, :imagen_url, :fecha_avance)");
    $stmt->bindValue(':proyecto_id', $proyecto_id, PDO::PARAM_INT);
    $stmt->bindValue(':imagen_url', $imgUrl);
    $stmt->bindValue(':fecha_avance', $fecha_avance ?: null);

    if ($stmt->execute()) {
        $id = $pdo->lastInsertId();
        echo json_encode([
            'state' => true,
            'message' => 'Imagen subida correctamente.',
            'data' => [
                'id' => $id,
                'imagen_url' => $imgUrl,
                'fecha_avance' => $fecha_avance
            ]
        ]);
    } else {
        echo json_encode(['state' => false, 'message' => 'Error al guardar en la base de datos.']);
    }

    $db->closeConect();
    exit;
}

if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['state' => false, 'message' => 'ID inválido.']);
        exit;
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    $stmt = $pdo->prepare("SELECT imagen_url FROM " . $db->getTable('au_proyectos_imagenes') . " WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($img) {
        $filePath = __DIR__ . '/../../' . $img['imagen_url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $del = $pdo->prepare("DELETE FROM " . $db->getTable('au_proyectos_imagenes') . " WHERE id = :id");
        $del->bindValue(':id', $id, PDO::PARAM_INT);
        $del->execute();
    }

    echo json_encode(['state' => true, 'message' => 'Imagen eliminada.']);
    $db->closeConect();
    exit;
}

echo json_encode(['state' => false, 'message' => 'Solicitud inválida.']);
