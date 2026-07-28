<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Comentarios
{

  public function __construct()
  {
  }

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT comentarios.*, 
        tbl_departamentos.departamento, 
        tbl_ciudades.municipio, 
        tbl_vereda.nombre_vereda
        FROM ((" . $db->getTable('comentarios') . " INNER JOIN " . $db->getTable('tbl_departamentos') . " ON comentarios.departamento_id = tbl_departamentos.id)
        INNER JOIN  " . $db->getTable('tbl_ciudades') . "  ON comentarios.municipio_id = tbl_ciudades.id) 
        LEFT JOIN  " . $db->getTable('tbl_vereda') . "  ON comentarios.vereda_id = tbl_vereda.id ORDER BY comentarios.id DESC";

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

  public static function actualizarVerificacion($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $verificado = isset($rqst['verificado']) ? ($rqst['verificado']) : 'no';

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($id > 0 &&  $verificado != "") {

      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "UPDATE  " . $db->getTable('comentarios') . "
        SET verificado ='" .  $verificado . "'
        WHERE id = $id ";
      $result = $pdo->query($q);
      if ($result) {
        $arrjson = array('output' => array('valid' => true));
      } else {
        $arrjson = Util::error_no_result();
      }
      $db->closeConect();
      return $arrjson;
    }else{
      Util::error_missing_data();
    }
  }


  public static function descargar($rqst)
  {

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT comentarios.*, 
    tbl_departamentos.departamento, 
    tbl_ciudades.municipio, 
    tbl_vereda.nombre_vereda
    FROM ((" . $db->getTable('comentarios') . " INNER JOIN " . $db->getTable('tbl_departamentos') . " ON comentarios.departamento_id = tbl_departamentos.id)
    INNER JOIN  " . $db->getTable('tbl_ciudades') . "  ON comentarios.municipio_id = tbl_ciudades.id) 
    LEFT JOIN  " . $db->getTable('tbl_vereda') . "  ON comentarios.vereda_id = tbl_vereda.id
     WHERE comentarios.autorizo_comunicados = 'si' ";



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
}
