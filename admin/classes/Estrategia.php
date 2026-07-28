<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Estrategia
{

    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $query = "SELECT * FROM " . $db->getTable('tbl_estrategia') . ($id > 0 ? " WHERE id = :id" : "");

        $stmt = $pdo->prepare($query);
        if ($id > 0) {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = $data ? ['output' => ['valid' => true, 'response' => $data]] : Util::error_no_result();

        $db->closeConect();
        return $response;
    }

    public static function load($data)
    {
        $draw = $data['draw'] ?? 1;
        $start = $data['start'] ?? 0;
        $length = $data['length'] ?? 10;
        $searchValue = $data['search']['value'] ?? '';

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $table = $db->getTable('tbl_estrategia');

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();
            $recordsTotal = $stmt->fetchColumn();

            $where = "";
            if (!empty($searchValue)) {
                $where = " WHERE nombre LIKE :search OR descripcion LIKE :search ";
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table $where");
            if (!empty($searchValue)) {
                $stmt->bindValue(':search', '%' . $searchValue . '%');
            }
            $stmt->execute();
            $recordsFiltered = $stmt->fetchColumn();

            $sql = "SELECT * FROM $table $where ORDER BY dtcreate DESC LIMIT :start, :length";

            $stmt = $pdo->prepare($sql);

            if (!empty($searchValue)) {
                $stmt->bindValue(':search', '%' . $searchValue . '%');
            }
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'draw' => intval($draw),
                'recordsTotal' => intval($recordsTotal),
                'recordsFiltered' => intval($recordsFiltered),
                'data' => $result
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }


    public static function newEstrategia($data)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        session_start();
        $tec_usuario_id = $_SESSION['session_user']['id'] ?? 0;

        try {
            $q = "INSERT INTO " . $db->getTable('tbl_estrategia') . " (nombre, descripcion, tec_usuario_id, dtcreate) 
              VALUES (:nombre, :descripcion, :tec_usuario_id, NOW())";
            $stmt = $pdo->prepare($q);

            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':tec_usuario_id', $tec_usuario_id);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Estrategia registrada correctamente',
                'insert_id' => $pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    public static function editEstrategia($data)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $id = $data['id'] ?? 0;

            $q = "SELECT nombre, descripcion 
              FROM " . $db->getTable('tbl_estrategia') . " 
              WHERE id = :id";

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return [
                    'state' => true,
                    'data' => $result
                ];
            } else {
                return [
                    'state' => false,
                    'message' => 'Estrategia no encontrada.'
                ];
            }
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    public static function updateEstrategia($data)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        session_start();
        $tec_usuario_id = $_SESSION['session_user']['id'] ?? 0;

        $id = $data['id'] ?? null;
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');

        if (empty($id) || !is_numeric($id)) {
            return [
                'state' => false,
                'message' => 'ID inválido'
            ];
        }

        if (empty($nombre) || empty($descripcion)) {
            return [
                'state' => false,
                'message' => 'Nombre y descripción son obligatorios'
            ];
        }

        try {
            $q = "UPDATE " . $db->getTable('tbl_estrategia') . " 
              SET nombre = :nombre, descripcion = :descripcion 
              WHERE id = :id";

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Estrategia actualizada correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
