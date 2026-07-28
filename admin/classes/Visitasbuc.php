<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Visitasbuc
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


    $q="SELECT tbl_gestora.id, tbl_gestora.entidad, tbl_gestora.barrio, tbl_ciudades.comuna, tbl_gestora.beneficiario, tbl_gestora.compromisos, tbl_gestora.img, tbl_gestora.date, tbl_gestora.observaciones, tbl_gestora.compromisos
    FROM " . $db->getTable('tbl_gestora') . "
    INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_gestora.tbl_municipio_id = tbl_ciudades.codigo_muncipio 
    ORDER BY tbl_gestora.date";
 

    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_gestora') . " WHERE id = " . $id;
    }

    if ($tipo != "") {
      $q = "SELECT * FROM " . $db->getTable('tbl_gestora') . " ";
    }
    if ($tbl_municipio_id != "") {
      $q = "SELECT * FROM " . $db->getTable('tbl_gestora') . " WHERE tbl_municipio_id=".$tbl_municipio_id;
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
    $date = isset($rqst['date']) ? ($rqst['date']) : '';
    $entidad = isset($rqst['entidad']) ? ($rqst['entidad']) : '';
    $cargo = isset($rqst['cargo']) ? ($rqst['cargo']) : '';
    $beneficiario = isset($rqst['beneficiario']) ? ($rqst['beneficiario']) : '';
    $provincia = isset($rqst['provincia']) ? ($rqst['provincia']) : '';
    $observaciones = isset($rqst['observaciones']) ? ($rqst['observaciones']) : '';
    $compromisos = isset($rqst['compromisos']) ? ($rqst['compromisos']) : '';
    $tbl_usuario_id =  intval($_SESSION['session_user']['id']);
    $img = isset($_SESSION['file']['nombrearchivo']) ? ($_SESSION['file']['nombrearchivo']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($id > 0) {
      $q0 = "SELECT * FROM " . $db->getTable('tbl_gestora') . " WHERE id = " . $id;
      $result0 = $pdo->query($q0);
      if ($result0) {
        $table = $db->getTable('tbl_gestora');
        $arrfieldscomma = array(
          'date' => $date,
          'entidad' => $entidad,
          'provincia' => $provincia,
          'cargo' => $cargo,
          'beneficiario' => $beneficiario,
          'observaciones' => $observaciones,
          'compromisos' => $compromisos,
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
          $arrjson = Util::error_general('Actualizando los datos del Lider');
        } else {
          $arrjson = array('output' => array('valid' => true, 'id' => $id, 'img' => $file));
        }
      } else {
        $arrjson = Util::error_general();
      }
    } else {
      if ($date != "" || $entidad != "" || $tbl_departamento_id > 0 || $beneficiario != "") {
        $q = "INSERT INTO " . $db->getTable('tbl_gestora') . " (created_at, date, entidad, cargo, beneficiario, provincia, observaciones, compromisos, img, tbl_departamento_id, tbl_municipio_id,  tbl_usuario_id) VALUES
                                              ( " . Util::date_now_server() . ", :date, :entidad, :cargo, :beneficiario, :provincia,  :observaciones,  :compromisos, :img, :tbl_departamento_id, :tbl_municipio_id,   :tbl_usuario_id)";
        $result = $pdo->prepare($q);
        $arrparam = array(
          ':date' => $date,
          ':entidad' => $entidad,
          ':cargo' => $cargo,
          ':beneficiario' => $beneficiario,
          ':provincia' => $provincia,
          ':compromisos' => $compromisos,         
          ':observaciones' => $observaciones,       
          ':img' => $img,
          ':tbl_departamento_id' => $tbl_departamento_id,
          ':tbl_municipio_id' => $tbl_municipio_id,
           ':tbl_usuario_id' => $tbl_usuario_id,
        
        );

        /*         print_r($q);
        print_r($arrparam);
        exit(); */

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
