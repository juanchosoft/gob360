<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class IngresoActores {

    public function __construct(){}

    public static function getAll($rqst){

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT * FROM " . $db->getTable('tbl_actores_mapa');
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


    public static function save($rqst, $files = []) {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $departamento_id = isset($rqst['departamento_id']) ? ($rqst['departamento_id']) : '';
        $municipio_id =  isset($rqst['municipio_id']) ? ($rqst['municipio_id']) : '';
        $vereda_id = isset($rqst['vereda_id']) ? ($rqst['vereda_id']) : '';
        $tipo_actor = isset($rqst['tipo_actor']) ? ($rqst['tipo_actor']) : '';
        $actor = isset($rqst['actor']) ? ($rqst['actor']) : '';
        $tbl_usuario_id =  $_SESSION['session_user']['id'];
        $logo = isset($files["logo"]) ? IngresoActores::loadPhoto($files["logo"]) : null;

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            //actualiza la informacion
            $q = "SELECT id FROM " . $db->getTable('tbl_mapa_actores') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_mapa_actores');
                $arrfieldscomma = array(
                    'departamento_id' => $departamento_id,
                    'municipio_id' => $municipio_id,
                    'vereda_id' => $vereda_id,
                    'tipo_actor' => $tipo_actor,
                    'logo' => $logo,
                    'actor' => $actor,
                    'tbl_usuario_id' => $tbl_usuario_id);
                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if(!$result){
                    $arrjson = Util::error_general();
                }else{
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            if ($departamento_id != "" && $tipo_actor  != "") {
                $q = "INSERT INTO " . $db->getTable('tbl_mapa_actores') . " (dtcreate_at, tipo_actor, departamento_id, municipio_id, vereda_id, tbl_usuario_id, actor, logo)
                VALUES ( " . Util::date_now_server() . ", :tipo_actor, :departamento_id, :municipio_id, :vereda_id, :tbl_usuario_id, :actor, :logo)";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    ':tipo_actor' => $tipo_actor,
                    ':departamento_id' => $departamento_id,
                    ':municipio_id' => $municipio_id,
                    ':vereda_id' => $vereda_id,
                    ':tbl_usuario_id' => $tbl_usuario_id,
                    ':actor' => $actor,
                    ':logo' => $logo);
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general(' Al guardar ingreso de actores');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
        }
        $db->closeConect();
        return $arrjson;
    }

    /**
   * Carga de Foto
   */
  public static function loadPhoto($imagen){
        include_once "../../contants_actores.php";
        if ($imagen['size'] > 0) {
            if ($imagen['error'] < 1) {
                $type_file = explode("/",$imagen['type']);
                if ($type_file['0'] == 'image') {
                    $ruta_img = WWW_ROOT_ACTORES;
                    if (!file_exists($ruta_img)) {
                        mkdir($ruta_img, 0777, true);
                    }
                    $nombre_archivo = rand().'.'.$type_file['1'];
                    if(move_uploaded_file($imagen['tmp_name'], $ruta_img.$nombre_archivo)) {
                        return $nombre_archivo;
                    } else{
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
