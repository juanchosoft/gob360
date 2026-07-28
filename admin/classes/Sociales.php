<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Sociales {

    public function __construct(){}

    public static function getAll($rqst){

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_sociales');
        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_sociales') . " WHERE id = " . $id;
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
        $nombre = isset($rqst['nombre']) ? ($rqst['nombre']) : '';
        $tipo =  isset($rqst['tipo']) ? ($rqst['tipo']) : '';
        $puntaje = isset($rqst['puntaje']) ? ($rqst['puntaje']) : '';
        $porcentaje = isset($rqst['porcentaje']) ? ($rqst['porcentaje']) : '';
        $tbl_usuario_id =  $_SESSION['session_user']['id'];

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            //actualiza la informacion
            $q = "SELECT id FROM " . $db->getTable('tbl_sociales') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_sociales');
                $arrfieldscomma = array(
                    'nombre' => $nombre,
                    'tipo' => $tipo,
                    'puntaje' => $puntaje,
                    'porcentaje' => $porcentaje,
                    'tbl_usuario_id' => $tbl_usuario_id);
                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if(!$result){
                    $arrjson = Util::error_general('Actualizando los datos de brigada');
                }else{
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            if ($nombre != "") {
                $q = "INSERT INTO " . $db->getTable('tbl_sociales') . " (created_at, nombre, tipo, puntaje, porcentaje,  tbl_usuario_id)
                VALUES ( " . Util::date_now_server() . ", :nombre, :tipo, :puntaje, :porcentaje, :tbl_usuario_id)";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    ':nombre' => $nombre,
                    ':tipo' => $tipo,
                    ':puntaje' => $puntaje,
                    ':porcentaje' => $porcentaje,
                    ':tbl_usuario_id' => $tbl_usuario_id);
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
