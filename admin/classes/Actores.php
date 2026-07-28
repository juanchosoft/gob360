<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Actores
{

    public function __construct() {}

    public static function getAll($rqst)
    {

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT actores.*, municipios.municipio FROM " . $db->getTable('tbl_actores_mapa') . " AS actores
                JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS municipios ON actores.municipio_id = municipios.codigo_muncipio";

        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_actores_mapa') . " WHERE id = " . $id;
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

    /**
     * metodo que obtiene los actores por alcaldia
     * @param array $rqst
     */
    public static function getByAlcaldia($rqst)
    {
        $id = isset($rqst['alcaldia_id']) ? intval($rqst['alcaldia_id']) : 0;
        $arrjson = Util::error_missing_data();

        if ($id > 0) {
            $db = new DbConection();
            $pdo = $db->openConect();

            $q = "SELECT actores.*, municipios.municipio 
                FROM " . $db->getTable('tbl_actores_mapa') . " AS actores
                    JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS municipios 
                    ON actores.municipio_id = municipios.codigo_muncipio 
                    WHERE actores.municipio_id = :id";

            $stmt = $pdo->prepare($q);
            if ($stmt->execute(['id' => $id])) {
                $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $arrjson = array('output' => array('valid' => true, 'response' => $arr));
            } else {
                $arrjson = Util::error_no_result();
            }
            $db->closeConect();
        }

        return $arrjson;
    }


    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $nombre = isset($rqst['nombre']) ? ($rqst['nombre']) : '';
        $pertenece =  isset($rqst['pertenece']) ? ($rqst['pertenece']) : '';
        $tbl_usuario_id =  $_SESSION['session_user']['id'];
        $municipio_id = isset($rqst['alcaldia_id']) ? intval($rqst['alcaldia_id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            //actualiza la informacion
            $q = "SELECT id FROM " . $db->getTable('tbl_actores_mapa') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_actores_mapa');
                $arrfieldscomma = array(
                    'nombre' => $nombre,
                    'pertenece' => $pertenece,
                    'tbl_usuario_id' => $tbl_usuario_id,
                    'municipio_id' => $municipio_id
                );
                $arrfieldsnocomma = array('dtcreate_at' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if (!$result) {
                    $arrjson = Util::error_general('Actualizando los datos de inestabilidad armada');
                } else {
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {

            if ($nombre != "" && $pertenece != "" && $pertenece != "Seleccione") {
                $q = "INSERT INTO " . $db->getTable('tbl_actores_mapa') . " (dtcreate_at, nombre, pertenece, tbl_usuario_id, municipio_id) " .
                    "
                VALUES ( " . Util::date_now_server() . ", :nombre, :pertenece,  :tbl_usuario_id, :municipio_id)";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    'nombre' => $nombre,
                    'pertenece' => $pertenece,
                    'tbl_usuario_id' => $tbl_usuario_id,
                    'municipio_id' => $municipio_id

                );
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general(' Al guardar los datos de brigada');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
        }
        $db->closeConect();
        return $arrjson;
    }
}
