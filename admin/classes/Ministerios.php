<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Ministerios
{

    public function __construct() {}

    public static function getAll($rqst)
    {

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_ministerios');
        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_ministerios') . " WHERE id = " . $id;
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


    public static function getAllproyectos($rqst)
    {

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT  tbl_ministerios.id as tbl_ministerios_id, tbl_ministerios.ministerio, Sum(tbl_ministerios_proyectos.valor_proyecto) AS sumaproyectos
        FROM " . $db->getTable('tbl_ministerios_proyectos') . "
        INNER JOIN " . $db->getTable('tbl_ministerios') . " ON tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id
        GROUP BY tbl_ministerios.ministerio";


        if ($id > 0) {
            $q = "SELECT tbl_ministerios.id as tbl_ministerios_id, tbl_ministerios.ministerio, Sum(tbl_ministerios_proyectos.valor_proyecto) AS sumaproyectos
            FROM " . $db->getTable('tbl_ministerios_proyectos') . "
            INNER JOIN " . $db->getTable('tbl_ministerios') . " ON tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id
            WHERE id = " . $id . "
            GROUP BY tbl_ministerios.ministerio";
        }
        $result = $pdo->query($q);
        $arrpro = array();
        if ($result) {
            foreach ($result as $valor) {
                $arrpro[] = $valor;
            }
            $arrjson = array('output' => array('valid' => true, 'response' => $arrpro));
        } else {
            $arrjson = Util::error_no_result();
        }
        $db->closeConect();
        return $arrjson;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $ministerio = isset($rqst['ministerio']) ? ($rqst['ministerio']) : '';
        $ministro =  isset($rqst['ministro']) ? ($rqst['ministro']) : '';
        $correo = isset($rqst['correo']) ? ($rqst['correo']) : '';
        $tbl_usuario_id = $_SESSION['session_user']['id'];
        $image = isset($_SESSION['file']['nombrearchivo']) ? ($_SESSION['file']['nombrearchivo']) : '';

        if (!Util::validate_email($correo)) {
            return Util::error_general('El email no es correcto');
        }

        $db = new DbConection();
        $pdo = $db->openConect();


        if ($id > 0) {
            //actualiza la informacion
            $q = "SELECT id FROM " . $db->getTable('tbl_ministerios') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_ministerios');
                $arrfieldscomma = array(
                    'ministerio' => $ministerio,
                    'ministro' => $ministro,
                    'correo' => $correo,
                    'tbl_usuario_id' => $tbl_usuario_id,
                    'image' => $image
                );
                $arrfieldsnocomma = array('update_at' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if (!$result) {
                    $arrjson = Util::error_general('Actualizando los datos del batallon');
                } else {
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            if ($ministerio > 0) {
                $q = "INSERT INTO " . $db->getTable('tbl_ministerios') . " (created_at, ministerio, ministro, correo,  tbl_usuario_id, image)
                VALUES ( " . Util::date_now_server() . ", :ministerio, :ministro, :correo, :tbl_usuario_id, :image)";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    ':ministerio' => $ministerio,
                    ':ministerio' => $ministerio,
                    ':ministro' => $ministro,
                    ':correo' => $correo,
                    ':tbl_usuario_id' => $tbl_usuario_id,
                    ':image' => $image
                );
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general(' Al guardar los datos de batallon');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
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
                'ministerio',
                'ministro',
                'correo'
            ];

            $orderColumn = isset($data['order']) ? $columns[$orderColumnIndex] ?? 'id' : 'id';
            $orderDirection = isset($data['order']) ? $orderDirection : 'DESC';


            //$orderColumn = $columns[$orderColumnIndex] ?? 'id';

            $where = '';
            if (!empty($searchValue)) {
                $where = " WHERE ministerio LIKE :search OR ministro LIKE :search OR correo LIKE :search";
            }

            $table = $db->getTable('tbl_ministerios');

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

    public function getMinisterio($data)
    {
        try {

            $db = new DbConection();
            $pdo = $db->openConect();

            $q = "SELECT * FROM " . $db->getTable('tbl_ministerios') . " WHERE id = :id";
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

    public function updateMinisterio($data)
    {
        try {
            $id = $data['id'] ?? '';
            $ministerio = $data['ministerio'] ?? '';
            $ministro = $data['ministro'] ?? '';
            $correo = $data['correo'] ?? '';
            session_start();
            $tbl_usuario_id = $_SESSION['session_user']['id'];

            if (empty($id) || empty($ministerio) || empty($ministro) || empty($correo)) {
                return [
                    "state" => false,
                    "message" => "Todos los campos son obligatorios"
                ];
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return [
                    'state' => false,
                    'message' => 'El formato del correo electrónico no es válido.'
                ];
            }

            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "UPDATE " . $db->getTable('tbl_ministerios') . "
                SET ministerio = :ministerio, ministro = :ministro, correo = :correo, tbl_usuario_id = :tbl_usuario_id, update_at = NOW()
                WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':ministerio', $ministerio);
            $stmt->bindParam(':ministro', $ministro);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':tbl_usuario_id', $tbl_usuario_id);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $success = $stmt->execute();

            if ($success) {
                return [
                    "state" => true,
                    "message" => "Ministerio actualizado correctamente"
                ];
            } else {
                return [
                    "state" => false,
                    "message" => "No se pudo actualizar el ministerio"
                ];
            }
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    public function createMinisterio($data)
    {
        try {
            $ministerio = $data['ministerio'] ?? '';
            $ministro = $data['ministro'] ?? '';
            $correo = $data['correo'] ?? '';
            session_start();
            $tbl_usuario_id = $_SESSION['session_user']['id'];

            if (empty($ministerio) || empty($ministro) || empty($correo)) {
                return [
                    'state' => false,
                    'message' => 'Todos los campos son obligatorios.'
                ];
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return [
                    'state' => false,
                    'message' => 'El formato del correo electrónico no es válido.'
                ];
            }

            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "INSERT INTO " . $db->getTable('tbl_ministerios') . " (ministerio, ministro, correo, tbl_usuario_id, created_at) 
                VALUES (:ministerio, :ministro, :correo, :tbl_usuario_id, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':ministerio', $ministerio);
            $stmt->bindParam(':ministro', $ministro);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':tbl_usuario_id', $tbl_usuario_id);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Ministerio creado correctamente.'
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }
}
