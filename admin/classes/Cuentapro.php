<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Cuentapro
{  

  public function __construct()
  {
  }

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

        $q= "SELECT Count(tbl_visitas.id) AS Cuentap, tbl_visitas.provincia
        FROM " . $db->getTable('tbl_visitas') . " 
        INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_visitas.tbl_municipio_id = tbl_ciudades.codigo_muncipio
        GROUP BY tbl_visitas.provincia ORDER BY Cuentap" ;     

 
    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_visitas') . " WHERE id = " . $id;
    }

    if ($tipo != "") {
      $q = "SELECT * FROM " . $db->getTable('tbl_visitas') . " WHERE tipo = '$tipo' AND habilitado = 'si'";
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