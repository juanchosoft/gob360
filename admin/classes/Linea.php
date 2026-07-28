<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Linea
{

    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $query = "SELECT * FROM " . $db->getTable('tbl_linea') . ($id > 0 ? " WHERE id = :id" : "");

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

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $nombre = isset($rqst['nombre']) ? trim($rqst['nombre']) : '';
        $descripcion = isset($rqst['descripcion']) ? trim($rqst['descripcion']) : '';
        $tec_usuario_id = $_SESSION['session_user']['id'] ?? 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                // Verifica si el registro existe antes de actualizar
                $q = "SELECT id FROM " . $db->getTable('tbl_linea') . " WHERE id = :id";
                $stmt = $pdo->prepare($q);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $table = $db->getTable('tbl_linea');
                    $arrfieldscomma = array(
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'tec_usuario_id' => $tec_usuario_id
                    );
                    $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                    $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);

                    $result = $pdo->query($q);
                    if (!$result) {
                        throw new Exception('Error actualizando los datos del linea');
                    }

                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                } else {
                    throw new Exception('El registro no existe');
                }
            } else {
                if (!empty($nombre)) {
                    // Inserta un nuevo registro
                    $q = "INSERT INTO " . $db->getTable('tbl_linea') . " (dtcreate, nombre, descripcion, tec_usuario_id)
                        VALUES (:dtcreate, :nombre, :descripcion, :tec_usuario_id)";
                    $stmt = $pdo->prepare($q);
                    $stmt->execute([
                        ':dtcreate' => Util::date_now_server(),
                        ':nombre' => $nombre,
                        ':descripcion' => $descripcion,
                        ':tec_usuario_id' => $tec_usuario_id
                    ]);
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    throw new Exception('Faltan datos obligatorios');
                }
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $arrjson = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    public function load($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $draw = $data['draw'] ?? 1;
            $start = $data['start'] ?? 0;
            $length = $data['length'] ?? 10;
            $searchValue = $data['search']['value'] ?? '';
            $orderColumnIndex = $data['order'][0]['column'] ?? 0;
            $orderDirection = $data['order'][0]['dir'] ?? 'asc';

            $columns = [
                'id',
                'nombre',
                'descripcion'
            ];

            $orderColumn = isset($data['order']) ? $columns[$orderColumnIndex] ?? 'id' : 'id';
            $orderDirection = isset($data['order']) ? $orderDirection : 'DESC';


            //$orderColumn = $columns[$orderColumnIndex] ?? 'id';

            $where = '';
            if (!empty($searchValue)) {
                $where = " WHERE nombre LIKE :search OR descripcion LIKE :search";
            }

            $table = $db->getTable('tbl_linea');

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();
            $recordsTotal = $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table $where");
            if (!empty($searchValue)) {
                $stmt->bindValue(':search', '%' . $searchValue . '%');
            }
            $stmt->execute();
            $recordsFiltered = $stmt->fetchColumn();

            $sql = "SELECT * FROM $table $where ORDER BY $orderColumn $orderDirection LIMIT :start, :length";
            $stmt = $pdo->prepare($sql);
            if (!empty($searchValue)) {
                $stmt->bindValue(':search', '%' . $searchValue . '%');
            }
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "draw" => intval($draw),
                "recordsTotal" => intval($recordsTotal),
                "recordsFiltered" => intval($recordsFiltered),
                "data" => $data,
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    public function getLinea($data)
    {
        try {

            $db = new DbConection();
            $pdo = $db->openConect();

            $q = "SELECT * FROM " . $db->getTable('tbl_linea') . " WHERE id = :id";
            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "state" => true,
                "data" => $result
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    public function updateLinea($data)
    {
        try {
            $id = $data['id'] ?? '';
            $nombre = $data['nombre'] ?? '';
            $descripcion = $data['descripcion'] ?? '';
            session_start();
            $tbl_usuario_id = $_SESSION['session_user']['id'];

            if (empty($id) || empty($nombre) || empty($descripcion)) {
                return [
                    "state" => false,
                    "message" => "Todos los campos son obligatorios"
                ];
            }


            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "UPDATE " . $db->getTable('tbl_linea') . "
                SET nombre = :nombre, descripcion = :descripcion, tec_usuario_id = :tbl_usuario_id
                WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':tbl_usuario_id', $tbl_usuario_id);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $success = $stmt->execute();

            if ($success) {
                return [
                    "state" => true,
                    "message" => "Linea actualizada correctamente"
                ];
            } else {
                return [
                    "state" => false,
                    "message" => "No se pudo actualizar la linea"
                ];
            }
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    public function createLinea($data)
    {
        try {
            $nombre = $data['nombre'] ?? '';
            $descripcion = $data['descripcion'] ?? '';
            session_start();
            $tbl_usuario_id = $_SESSION['session_user']['id'];

            if (empty($nombre) || empty($descripcion)) {
                return [
                    'state' => false,
                    'message' => 'Todos los campos son obligatorios.'
                ];
            }


            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "INSERT INTO " . $db->getTable('tbl_linea') . " (nombre, descripcion, tec_usuario_id, dtcreate) 
                VALUES (:nombre, :descripcion, :tec_usuario_id, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':tec_usuario_id', $tbl_usuario_id);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Linea creada correctamente.'
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }
}
