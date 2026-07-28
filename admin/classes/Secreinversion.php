<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Secreinversion
{  

  public function __construct()
  {
  }

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $secretaria = isset($rqst['secretaria']) ? ($rqst['secretaria']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q="SELECT tbl_secretarias.secretaria, Sum(tbl_proyectos.valor_proyecto) AS sumaproyectos
    FROM " . $db->getTable('tbl_proyectos') . "
    INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
    GROUP BY tbl_secretarias.secretaria";
    
 
    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_secretarias') . " WHERE id = " . $id;
    }

    if ($secretaria != "") {
      $q = "SELECT * FROM " . $db->getTable('tbl_secretarias') . " WHERE tbl_secretarias.secretaria = '$secretaria' ";
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