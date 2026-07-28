<?php
require_once 'DbConection.php';
require_once 'Util.php';

class FactoresInestabilidadGobernacion
{

    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            if ($id > 0) {
                $query = "SELECT * FROM " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            } else {
                $query = "SELECT * FROM " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " ORDER BY id ASC";
                $stmt = $pdo->prepare($query);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $response = array('output' => array('valid' => true, 'response' => $results));
            } else {
                $response = Util::error_no_result();
            }
        } catch (Exception $e) {
            $response = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        if (empty($rqst['nombre_categoria'])) {
            return Util::error_missing_data();
        }

        $nombre_categoria = $rqst['nombre_categoria'];
        $icono = isset($rqst['icono']) ? $rqst['icono'] : null;
        if (empty($icono) && isset($_SESSION['file']['nombrearchivo']) && !empty($_SESSION['file']['nombrearchivo'])) {
            $icono = 'assets/img/admin/' . $_SESSION['file']['nombrearchivo'];
        }
        $tec_usuario_id = intval($_SESSION['session_user']['id']);

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            $q = "SELECT id FROM " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $iconoVal = ($icono !== null) ? "'" . $icono . "'" : "NULL";
                $q = "UPDATE " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " 
                      SET nombre_categoria = '" . $nombre_categoria . "',
                          icono = " . $iconoVal . ",
                          tec_usuario_id = " . $tec_usuario_id . ",
                          dtcreate = " . Util::date_now_server() . "
                      WHERE id = '$id'";
                $result = $pdo->query($q);
                if (!$result) {
                    $arrjson = Util::error_general();
                } else {
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            if ($nombre_categoria != '') {
                $q = "INSERT INTO " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " (dtcreate, nombre_categoria, icono, tec_usuario_id)
                VALUES (" . Util::date_now_server() . ", :nombre_categoria, :icono, :tec_usuario_id)";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    ':nombre_categoria' => $nombre_categoria,
                    ':icono' => $icono,
                    ':tec_usuario_id' => $tec_usuario_id
                );
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general(' Al guardar factor de inestabilidad');
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

        $q = "DELETE FROM " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " WHERE id = " . $id;
        $result = $pdo->query($q);

        if ($result) {
            $arrjson = array('output' => array('valid' => true, 'error' => $pdo->errorInfo()));
        } else {
            $arrjson = Util::error_generaldelete();
        }
        $db->closeConect();
        return $arrjson;
    }
}
