<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Barriosbuc
{
  

  public function __construct()
  {
  }

  /**
   * Metodo para obtener la informacion de los apotos totales por departamento, y poblacion habilitada para votar
   * @param string $
   */
 

  public static function getAll($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_comuna_id = isset($rqst['tbl_comuna_id']) ? ($rqst['tbl_comuna_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT * FROM " . $db->getTable('tbl_barriosbuc') . " ORDER BY nombre ASC";

    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_barriosbuc') . " WHERE id = " . $id;
    }

    if ($tbl_comuna_id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_barriosbuc') . " WHERE tbl_comuna_id = " . $tbl_comuna_id;
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


}
