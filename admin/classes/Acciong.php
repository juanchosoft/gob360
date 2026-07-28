<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Acciong
{

    public function __construct() {}

    public static function getAll($rqst)
    {

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_acciong');
        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_acciong') . " WHERE id = " . $id;
        }
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = Util::error_no_result();
        }
        $db->closeConect();
        return $arrjson;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $accion = isset($rqst['accion']) ? ($rqst['accion']) : '';
        $tbl_usuario_id = $_SESSION['session_user']['id'];

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            // Actualiza la información
            $q = "SELECT id FROM " . $db->getTable('tbl_acciong') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_acciong');
                $arrfieldscomma = array(
                    'accion' => $accion,
                    'tbl_usuario_id' => $tbl_usuario_id
                );
                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if (!$result) {
                    $arrjson = Util::error_general('Actualizando los datos de las Acciones');
                } else {
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            if ($accion > 0) {
                $q = "INSERT INTO " . $db->getTable('tbl_acciong') . " (dtcreate, accion, tbl_usuario_id)
                VALUES (" . Util::date_now_server() . ", :accion, :tbl_usuario_id)";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    ':accion' => $accion,
                    ':tbl_usuario_id' => $tbl_usuario_id
                );
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general(' Al guardar los datos de la Accion');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
        }
        $db->closeConect();
        return $arrjson;
    }

    public static function delete($rqst)
    {

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "DELETE FROM " . $db->getTable('tbl_acciong') . " WHERE id = " . $id;
        $result = $pdo->query($q);
        if ($result) {
            $arrjson = array('output' => array('valid' => true, 'error' => $pdo->errorInfo()));
        } else {
            $arrjson = Util::error_no_result();
        }
        $db->closeConect();

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
                'accion',
            ];

            $orderColumn = isset($data['order']) ? $columns[$orderColumnIndex] ?? 'id' : 'id';
            $orderDirection = isset($data['order']) ? $orderDirection : 'DESC';


            //$orderColumn = $columns[$orderColumnIndex] ?? 'id';

            $where = '';
            if (!empty($searchValue)) {
                $where = " WHERE accion LIKE :search";
            }

            $table = $db->getTable('tbl_acciong');

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

    public function getAccionG($data)
    {
        try {

            $db = new DbConection();
            $pdo = $db->openConect();

            $q = "SELECT * FROM " . $db->getTable('tbl_acciong') . " WHERE id = :id";
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

    public function updateAccionG($data)
    {
        try {
            $id = $data['id'] ?? '';
            $accion = $data['accion'] ?? '';
            session_start();
            $tbl_usuario_id = $_SESSION['session_user']['id'];

            if (empty($id) || empty($accion)) {
                return [
                    "state" => false,
                    "message" => "Todos los campos son obligatorios"
                ];
            }

            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "UPDATE " . $db->getTable('tbl_acciong') . "
                SET accion = :accion
                WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':accion', $accion);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $success = $stmt->execute();

            if ($success) {
                return [
                    "state" => true,
                    "message" => "Tipo de acción actualizado correctamente"
                ];
            } else {
                return [
                    "state" => false,
                    "message" => "No se pudo actualizar el tipo de acción"
                ];
            }
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    public function createAccionG($data)
    {
        try {
            $accion = $data['accion'] ?? '';
            session_start();
            $tbl_usuario_id = $_SESSION['session_user']['id'];

            if (empty($accion)) {
                return [
                    'state' => false,
                    'message' => 'El campo tipo de accion es obligatorio.'
                ];
            }


            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "INSERT INTO " . $db->getTable('tbl_acciong') . " (accion, tbl_usuario_id, dtcreate) 
                VALUES (:accion, :tbl_usuario_id, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':accion', $accion);
            $stmt->bindParam(':tbl_usuario_id', $tbl_usuario_id);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Tipo de accion creado correctamente.'
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }
}
