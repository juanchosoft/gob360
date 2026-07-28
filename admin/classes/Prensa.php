<?php

class Prensa
{

  public function __construct()
  {
  }

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT * FROM " . $db->getTable('tbl_prensa');
    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_prensa') . " WHERE id = " . $id;
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

  public static function loadPdf($documento)
  {
    include_once "../../contants.php";
    if ($documento['size'] > 0) {
      if ($documento['error'] < 1) {
        $name_file = explode(".", $documento['name']);

        if (mb_strtolower($name_file[count($name_file) - 1]) == 'pdf') {
          $ruta_pdf = WWW_ROOT_PRENSA;
          if (!file_exists($ruta_pdf)) {
            mkdir($ruta_pdf, 0777, true);
          }
          $titulo_archivo = rand() . '.' . $name_file[count($name_file) - 1];
          if (move_uploaded_file($documento['tmp_name'], $ruta_pdf . $titulo_archivo)) {
            return $titulo_archivo;
          } else {
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


  public static function save($rqst, $files = [])
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $titulo = isset($rqst['titulo']) ? ($rqst['titulo']) : '';
    $descripcion =  isset($rqst['descripcion']) ? ($rqst['descripcion']) : '';
    $enable =  isset($rqst['enable']) ? ($rqst['enable']) : '';
    $pdf = isset($files['pdf']) ? Prensa::loadPdf($files['pdf']) : null;
    $tbl_usuario_id =  $_SESSION['session_user']['id'];

    if ($id == 0 && ($pdf == "" || $pdf == null)) {
      return Util::error_general('El pdf es requerido');
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($id > 0) {
      //actualiza la informacion
      $q = "SELECT id FROM " . $db->getTable('tbl_prensa') . " WHERE id = " . $id;
      $result = $pdo->query($q);
      if ($result) {
        $table = $db->getTable('tbl_prensa');
        $arrfieldscomma = array(
          'titulo' => $titulo,
          'descripcion' => $descripcion,
          'enable' => $enable,
          'pdf' => $pdf,
          'tbl_usuario_id' => $tbl_usuario_id,
        );
        $arrfieldsnocomma = array('created_at' => Util::date_now_server());
        $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
        $result = $pdo->query($q);
        if (!$result) {
          $arrjson = Util::error_general();
        } else {
          $arrjson = array('output' => array('valid' => true, 'id' => $id));
        }
      } else {
        $arrjson = Util::error_general();
      }
    } else {
      if ($titulo != "") {
        $q = "INSERT INTO " . $db->getTable('tbl_prensa') . " (created_at,  titulo, descripcion, pdf, enable, tbl_usuario_id)
                VALUES ( " . Util::date_now_server() . ",  :titulo, :descripcion, :pdf, :enable, :tbl_usuario_id)";
        $result = $pdo->prepare($q);
        $arrparam = array(
          ':titulo' => $titulo,
          ':descripcion' => $descripcion,
          ':pdf' => $pdf,
          ':enable' => $enable,
          ':tbl_usuario_id' => $tbl_usuario_id,
        );
        if ($result->execute($arrparam)) {
          $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
        } else {
          $arrjson = Util::error_general();
        }
      } else {
        $arrjson = Util::error_missing_data();
      }
    }
    $db->closeConect();
    return $arrjson;
  }
}
