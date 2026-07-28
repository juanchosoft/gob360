<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Compromisos
{

  public function __construct()
  {
  }

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q="SELECT tbl_compromisos.date, tbl_ciudades.municipio, tbl_compromisos.compromisos, tbl_compromisos.provincia, tbl_compromisos.cumplimiento, tbl_compromisos.respuesta, tbl_secretarias.secretaria AS secretaria, tbl_compromisos.img, tbl_compromisos.id
     FROM " . $db->getTable('tbl_compromisos') . "
    INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_compromisos.tbl_secretarias_id = tbl_secretarias.id 
    INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_compromisos.tbl_municipio_id = tbl_ciudades.codigo_muncipio";



   

    if ($tipo != "") {
      $q = "SELECT * FROM " . $db->getTable('tbl_compromisos') . " ";
    }
    if ($tbl_municipio_id != "") {
      $q = "SELECT * FROM " . $db->getTable('tbl_compromisos') . " WHERE tbl_municipio_id=".$tbl_municipio_id;
    }

    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_compromisos') . " WHERE id = " . $id;
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

  public static function getAlltram($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';
  

    $db = new DbConection();
    $pdo = $db->openConect();

    $q="SELECT tbl_compromisos.date, tbl_ciudades.municipio, tbl_compromisos.compromisos, tbl_compromisos.cumplimiento, tbl_compromisos.respuesta, tbl_secretarias.secretaria, tbl_compromisos.img, tbl_ciudades.id
     FROM " . $db->getTable('tbl_compromisos') . "
    INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_compromisos.tbl_secretarias_id = tbl_secretarias.id 
    INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_compromisos.tbl_municipio_id = tbl_ciudades.codigo_muncipio
    WHERE tbl_compromisos.cumplimiento = 'En Trámite'";
    
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

  public static function getAllsinc($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q="SELECT tbl_compromisos.date, tbl_ciudades.municipio, tbl_compromisos.compromisos, tbl_compromisos.cumplimiento, tbl_compromisos.respuesta, tbl_secretarias.secretaria, tbl_compromisos.img, tbl_ciudades.id
     FROM " . $db->getTable('tbl_compromisos') . "
    INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_compromisos.tbl_secretarias_id = tbl_secretarias.id 
    INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_compromisos.tbl_municipio_id = tbl_ciudades.codigo_muncipio
    WHERE tbl_compromisos.cumplimiento = 'Sin Cumplir'";
    
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

  public static function getAllcum($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q="SELECT tbl_compromisos.date, tbl_ciudades.municipio, tbl_compromisos.compromisos, tbl_compromisos.cumplimiento, tbl_compromisos.respuesta, tbl_secretarias.secretaria, tbl_compromisos.img, tbl_ciudades.id
     FROM " . $db->getTable('tbl_compromisos') . "
    INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_compromisos.tbl_secretarias_id = tbl_secretarias.id 
    INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_compromisos.tbl_municipio_id = tbl_ciudades.codigo_muncipio
    WHERE tbl_compromisos.cumplimiento = 'Cumplido'";
      

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
   * Funcion para validar si la cedula ingresa ya existe
   * [available description]
   * @param  [type] $rqst [description]
   * @return [type]       [description]
   */

  public static function save($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : 0;
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0;
    $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
    $date = isset($rqst['date']) ? ($rqst['date']) : '';
    $cumplimiento = isset($rqst['cumplimiento']) ? ($rqst['cumplimiento']) : '';
    $respuesta = isset($rqst['respuesta']) ? ($rqst['respuesta']) : '';
    $compromisos = isset($rqst['compromisos']) ? ($rqst['compromisos']) : '';
    $provincia = isset($rqst['provincia']) ? ($rqst['provincia']) : '';
    $tbl_usuario_id =  intval($_SESSION['session_user']['id']);
    $img = isset($_SESSION['file']['nombrearchivo']) ? ($_SESSION['file']['nombrearchivo']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($id > 0) {
      $q0 = "SELECT * FROM " . $db->getTable('tbl_compromisos') . " WHERE id = " . $id;
      $result0 = $pdo->query($q0);
      if ($result0) {
        $table = $db->getTable('tbl_compromisos');
        $arrfieldscomma = array(
          'date' => $date,
          'cumplimiento' => $cumplimiento,
          'provincia' => $provincia,
          'respuesta' => $respuesta,
          'compromisos' => $compromisos,
          'tbl_secretarias_id' => $tbl_secretarias_id,
          'img' => $img,
          'tbl_departamento_id' => $tbl_departamento_id,
          'tbl_municipio_id' => $tbl_municipio_id
        
     
        );
        $arrfieldsnocomma = array('created_at' => Util::date_now_server());
        $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
        $result = $pdo->query($q);

        // Obtemos el valor de la imagen del producto
        $file = "";
        foreach ($result0 as $valor0) {
          $file = $valor0['img'];
        }

        if (!$result) {
          $arrjson = Util::error_general('Actualizando los datos de los compromisos');
        } else {
          $arrjson = array('output' => array('valid' => true, 'id' => $id, 'img' => $file));
        }
      } else {
        $arrjson = Util::error_general();
      }
    } else {
      if ($date != "" || $provincia != "" || $tbl_departamento_id > 0 || $respuesta != "") {
        $q = "INSERT INTO " . $db->getTable('tbl_compromisos') . " (created_at, date, cumplimiento, compromisos, respuesta, provincia, img, tbl_secretarias_id, tbl_departamento_id, tbl_municipio_id,  tbl_usuario_id) VALUES
                                              ( " . Util::date_now_server() . ", :date, :cumplimiento, :compromisos, :respuesta, :provincia,  :img, :tbl_secretarias_id, :tbl_departamento_id, :tbl_municipio_id, :tbl_usuario_id)";
        $result = $pdo->prepare($q);
        $arrparam = array(
          ':date' => $date,
          ':cumplimiento' => $cumplimiento,
          ':compromisos' => $compromisos,
          ':respuesta' => $respuesta,
          ':provincia' => $provincia,
          ':img' => $img,
          ':tbl_secretarias_id' => $tbl_secretarias_id,
          ':tbl_departamento_id' => $tbl_departamento_id,
          ':tbl_municipio_id' => $tbl_municipio_id,
           ':tbl_usuario_id' => $tbl_usuario_id,
        
        );

        //        print_r($q);
        // print_r($arrparam);
        // exit(); 

        if ($result->execute($arrparam)) {
          $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
        } else {
          $arrjson = Util::error_general('Ingresando los datos del lider');
        }
      } else {
        $arrjson = Util::error_missing_data();
      }
    }
    $db->closeConect();
    return $arrjson;
  }
}
