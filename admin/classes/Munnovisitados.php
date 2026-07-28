<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Munnovisitados
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

    $q = "SELECT tbl_ciudades.codigo_departamento, tbl_ciudades.codigo_muncipio, tbl_ciudades.municipio
          FROM " . $db->getTable('tbl_ciudades') . "  
          LEFT JOIN " . $db->getTable('tbl_visitas') . " ON tbl_ciudades.codigo_muncipio = tbl_visitas.tbl_municipio_id 
             AND tbl_visitas.tipo_registro = 'Visita'
          WHERE 
          tbl_ciudades.codigo_departamento = '68' 
          AND tbl_visitas.tbl_municipio_id IS NULL"; 

    if ($id > 0) {
      $q = "SELECT tbl_ciudades.codigo_departamento, tbl_ciudades.codigo_muncipio, tbl_ciudades.municipio
            FROM " . $db->getTable('tbl_ciudades') . "  
            LEFT JOIN " . $db->getTable('tbl_visitas') . " ON tbl_ciudades.codigo_muncipio = tbl_visitas.tbl_municipio_id 
               AND tbl_visitas.tipo_registro = 'Visita'
            WHERE 
            tbl_ciudades.codigo_departamento = '68' 
            AND tbl_visitas.tbl_municipio_id IS NULL 
            AND tbl_ciudades.codigo_muncipio = " . $id;
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