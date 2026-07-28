<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * para el módulo de Deportistas
 * 
 * @author SPIDERSOFTWARE
 */

require_once __DIR__ . '/DbConection.php';

class Deportistas
{
    public function __construct() {}

    /**
     * Obtener todos los deportistas o uno por ID
     */
    public static function getAll($rqst = null)
    {
        $id = isset($rqst['id']) ? (int)$rqst['id'] : 0;

        try {
            $db  = new DbConection();
            $pdo = $db->openConect();

            $sql = "
                SELECT 
                    d.id,
                    d.tipo_documento,
                    d.cc,
                    d.nombre AS deportista_nombre,
                    d.contacto,
                    d.nacimiento,
                    d.tbl_disciplina_id,
                    d.tbl_liga_id,
                    d.valor,
                    d.plazo,
                    d.tipo_deportista,
                    d.img,
                    d.tbl_usuario_id,
                    d.dtcreate,
                    dis.disciplina,
                    l.liga,
                    u.nombre AS usuario_nombre
                FROM " . $db->getTable('tbl_deportistas') . " d
                INNER JOIN " . $db->getTable('tbl_disciplina') . " dis 
                    ON d.tbl_disciplina_id = dis.id
                INNER JOIN " . $db->getTable('tbl_ligas') . " l 
                    ON d.tbl_liga_id = l.id
                LEFT JOIN " . $db->getTable('tbl_usuarios') . " u 
                    ON d.tbl_usuario_id = u.id
            ";

            if ($id > 0) {
                $sql .= " WHERE d.id = :id ";
            }

            $sql .= " ORDER BY d.id DESC ";

            $stmt = $pdo->prepare($sql);

            if ($id > 0) {
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            }

            $stmt->execute();
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            if ($arr && count($arr) > 0) {
                return [
                    'output' => [
                        'valid'    => true,
                        'response' => $arr
                    ]
                ];
            }

            return [
                'output' => [
                    'valid'    => false,
                    'response' => []
                ]
            ];

        } catch (Throwable $e) {
            if (isset($db)) {
                $db->closeConect();
            }

            return [
                'output' => [
                    'valid'    => false,
                    'response' => [],
                    'message'  => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Guardar o actualizar deportista
     */
    public static function save($rqst, $files = [])
    {
        try {
            date_default_timezone_set('America/Bogota');

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $db  = new DbConection();
            $pdo = $db->openConect();

            $id                = isset($rqst['id']) ? (int)$rqst['id'] : 0;
            $tipo_documento    = trim($rqst['tipo_documento'] ?? '');
            $cc                = trim($rqst['cc'] ?? '');
            $nombre            = trim($rqst['nombre'] ?? '');
            $tbl_disciplina_id = isset($rqst['tbl_disciplina_id']) ? (int)$rqst['tbl_disciplina_id'] : 0;
            $contacto          = trim($rqst['contacto'] ?? ($rqst['cel'] ?? ''));
            $nacimiento        = trim($rqst['nacimiento'] ?? '');
            $tbl_liga_id       = isset($rqst['tbl_liga_id']) ? (int)$rqst['tbl_liga_id'] : 0;
            $valor             = isset($rqst['valor']) ? (float) preg_replace('/[^\d]/', '', (string)$rqst['valor']) : 0;
            $plazo             = isset($rqst['plazo']) ? (int)$rqst['plazo'] : 0;
            $tipo_deportista   = trim($rqst['tipo_deportista'] ?? '');
            $tbl_usuario_id    = self::getCurrentUserId($rqst);
            $dtcreate          = date('Y-m-d H:i:s');

            // =========================
            // VALIDACIONES
            // =========================
            if ($tipo_documento === '') {
                throw new Exception('Debe seleccionar el tipo de documento.');
            }

            if ($cc === '') {
                throw new Exception('Debe ingresar la identificación.');
            }

            if (!preg_match('/^[0-9]+$/', $cc)) {
                throw new Exception('La identificación debe contener solo números.');
            }

            if ($nombre === '') {
                throw new Exception('Debe ingresar el nombre del deportista.');
            }

            if ($tbl_disciplina_id <= 0) {
                throw new Exception('Debe seleccionar una disciplina.');
            }

            if ($contacto === '') {
                throw new Exception('Debe ingresar el número de contacto.');
            }

            if (!preg_match('/^[0-9]+$/', $contacto)) {
                throw new Exception('El número de contacto debe contener solo números.');
            }

            if ($nacimiento === '') {
                throw new Exception('Debe seleccionar la fecha de nacimiento.');
            }

            if ($tbl_liga_id <= 0) {
                throw new Exception('Debe seleccionar una liga.');
            }

            if ($valor <= 0) {
                throw new Exception('Debe ingresar un valor válido.');
            }

            if ($plazo <= 0) {
                throw new Exception('Debe ingresar un plazo válido.');
            }

            if ($tipo_deportista === '') {
                throw new Exception('Debe seleccionar el tipo de deportista.');
            }

            if ($tbl_usuario_id <= 0) {
                throw new Exception('No fue posible identificar el usuario que realiza el registro.');
            }

            // =========================
            // VALIDAR DUPLICADO POR CC
            // =========================
            $sqlExiste = "SELECT id 
                          FROM " . $db->getTable('tbl_deportistas') . " 
                          WHERE cc = :cc";

            if ($id > 0) {
                $sqlExiste .= " AND id <> :id";
            }

            $stmtExiste = $pdo->prepare($sqlExiste);
            $stmtExiste->bindValue(':cc', $cc, PDO::PARAM_STR);

            if ($id > 0) {
                $stmtExiste->bindValue(':id', $id, PDO::PARAM_INT);
            }

            $stmtExiste->execute();
            $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                throw new Exception('Ya existe un deportista registrado con esa identificación.');
            }

            // =========================
            // SUBIR IMAGEN
            // =========================
            $rutaImagen = null;

            if (isset($files['img']) && !empty($files['img']['name'])) {
                $rutaImagen = self::uploadImage($files['img']);
            }

            // =========================
            // SI ES EDICIÓN, CONSERVAR IMAGEN ANTERIOR
            // =========================
            if ($id > 0) {
                $sqlOld = "SELECT img 
                           FROM " . $db->getTable('tbl_deportistas') . " 
                           WHERE id = :id";
                $stmtOld = $pdo->prepare($sqlOld);
                $stmtOld->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtOld->execute();
                $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

                if (!$rutaImagen && $old) {
                    $rutaImagen = $old['img'] ?? null;
                }
            }

            // =========================
            // INSERT / UPDATE
            // =========================
            if ($id > 0) {
                $sql = "UPDATE " . $db->getTable('tbl_deportistas') . " SET
                            tipo_documento    = :tipo_documento,
                            cc                = :cc,
                            nombre            = :nombre,
                            tbl_disciplina_id = :tbl_disciplina_id,
                            contacto          = :contacto,
                            nacimiento        = :nacimiento,
                            tbl_liga_id       = :tbl_liga_id,
                            valor             = :valor,
                            plazo             = :plazo,
                            tipo_deportista   = :tipo_deportista,
                            img               = :img,
                            tbl_usuario_id    = :tbl_usuario_id
                        WHERE id = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $msg = 'Deportista actualizado correctamente.';
            } else {
                $sql = "INSERT INTO " . $db->getTable('tbl_deportistas') . " (
                            tipo_documento,
                            cc,
                            nombre,
                            tbl_disciplina_id,
                            contacto,
                            nacimiento,
                            tbl_liga_id,
                            valor,
                            plazo,
                            tipo_deportista,
                            img,
                            tbl_usuario_id,
                            dtcreate
                        ) VALUES (
                            :tipo_documento,
                            :cc,
                            :nombre,
                            :tbl_disciplina_id,
                            :contacto,
                            :nacimiento,
                            :tbl_liga_id,
                            :valor,
                            :plazo,
                            :tipo_deportista,
                            :img,
                            :tbl_usuario_id,
                            :dtcreate
                        )";

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':dtcreate', $dtcreate, PDO::PARAM_STR);
                $msg = 'Deportista guardado correctamente.';
            }

            $stmt->bindValue(':tipo_documento', $tipo_documento, PDO::PARAM_STR);
            $stmt->bindValue(':cc', $cc, PDO::PARAM_STR);
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':tbl_disciplina_id', $tbl_disciplina_id, PDO::PARAM_INT);
            $stmt->bindValue(':contacto', $contacto, PDO::PARAM_STR);
            $stmt->bindValue(':nacimiento', $nacimiento, PDO::PARAM_STR);
            $stmt->bindValue(':tbl_liga_id', $tbl_liga_id, PDO::PARAM_INT);
            $stmt->bindValue(':valor', $valor);
            $stmt->bindValue(':plazo', $plazo, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_deportista', $tipo_deportista, PDO::PARAM_STR);
            $stmt->bindValue(':img', $rutaImagen, PDO::PARAM_STR);
            $stmt->bindValue(':tbl_usuario_id', $tbl_usuario_id, PDO::PARAM_INT);

            $stmt->execute();

            if ($id <= 0) {
                $id = (int)$pdo->lastInsertId();
            }

            $db->closeConect();

            return [
                'ok'   => true,
                'msg'  => $msg,
                'id'   => $id,
                'data' => [
                    'tbl_usuario_id' => $tbl_usuario_id,
                    'dtcreate'       => $dtcreate
                ]
            ];

        } catch (Throwable $e) {
            if (isset($db)) {
                $db->closeConect();
            }

            return [
                'ok'   => false,
                'msg'  => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtener id del usuario actual
     */
    private static function getCurrentUserId($rqst = [])
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (isset($_SESSION['session_user']['id']) && (int)$_SESSION['session_user']['id'] > 0) {
            return (int) $_SESSION['session_user']['id'];
        }

        if (isset($_SESSION['id']) && (int)$_SESSION['id'] > 0) {
            return (int) $_SESSION['id'];
        }

        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
            return (int) $_SESSION['user_id'];
        }

        if (isset($_SESSION['tbl_usuario_id']) && (int)$_SESSION['tbl_usuario_id'] > 0) {
            return (int) $_SESSION['tbl_usuario_id'];
        }

        if (isset($rqst['tbl_usuario_id']) && (int)$rqst['tbl_usuario_id'] > 0) {
            return (int) $rqst['tbl_usuario_id'];
        }

        return 0;
    }

    /**
     * Subir imagen
     */
    private static function uploadImage($file)
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return null;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $name    = $file['name'];
        $tmp     = $file['tmp_name'];
        $size    = (int)$file['size'];

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            throw new Exception('Formato de imagen no permitido. Use JPG, JPEG, PNG o WEBP.');
        }

        if ($size > 5 * 1024 * 1024) {
            throw new Exception('La imagen no debe superar los 5 MB.');
        }

        $folderAbsolute = __DIR__ . '/../uploads/deportistas/';
        $folderRelative = 'admin/uploads/deportistas/';

        if (!is_dir($folderAbsolute)) {
            if (!mkdir($folderAbsolute, 0777, true)) {
                throw new Exception('No fue posible crear la carpeta de imágenes.');
            }
        }

        date_default_timezone_set('America/Bogota');

        $fileName        = 'deportista_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
        $destinoAbsolute = $folderAbsolute . $fileName;

        if (!move_uploaded_file($tmp, $destinoAbsolute)) {
            throw new Exception('No fue posible guardar la imagen.');
        }

        return $folderRelative . $fileName;
    }
}

/**
 * Endpoint directo para AJAX
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $op = trim($_POST['op'] ?? 'save');

    if ($op === 'save') {
        echo json_encode(Deportistas::save($_POST, $_FILES), JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'ok'   => false,
        'msg'  => 'Operación no válida.',
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}