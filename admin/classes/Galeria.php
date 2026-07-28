<?php

class Gellery {

    public function __construct(){}

    public static function deleteFile($rqst){
      $id    = isset($rqst['id']) ? intval($rqst['id']) : 0;
      $db    = new DbConection();
      $pdo   = $db->openConect();

      if ($id > 0) {
        $q = "SELECT id FROM " . $db->getTable('tbl_gallery') . " WHERE id = " . $id;
        $result = $pdo->query($q);

        if ($result) {
          $table = $db->getTable('tbl_gallery');
          $arrfieldscomma = array(
            'state' => -1,
          );

          $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
          $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
          $result = $pdo->query($q);
          if (!$result) {
            $arrjson = Util::error_general('Eliminar la imagen');
          } else {
            $arrjson = array('output' => array('valid' => true, 'id' => $id));
          }
        } else {
          $arrjson = Util::error_general();
        }

      }else {
        $arrjson = Util::error_general();
      }

      $db->closeConect();
      return $arrjson;

    }

    public static function getAll($rqst)
    {

      $id    = isset($rqst['id']) ? intval($rqst['id']) : 0;
      $state = isset($rqst['state']) ? intval($rqst['state']) : 0;

      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "SELECT * FROM " . $db->getTable('tbl_gallery') . " WHERE state = ".$state;
      if ($id > 0) {
        $q = "SELECT * FROM " . $db->getTable('tbl_gallery') . " WHERE id = " . $id;
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


    public static function save($rqst,$files = []){

      $id          = isset($rqst['id']) ? intval($rqst['id']) : 0;
      $titulo      = isset($rqst['titulo']) ? ($rqst['titulo']) : '';
      $descripcion = isset($rqst['descripcion']) ? ($rqst['descripcion']) : '';

      $db = new DbConection();
      $pdo = $db->openConect();

      if ($id > 0) {
        $q = "SELECT id FROM " . $db->getTable('tbl_gallery') . " WHERE id = " . $id;
        $result = $pdo->query($q);

        if ($result) {
          $table = $db->getTable('tbl_gallery');
          $arrfieldscomma = array(
            'titulo' => $titulo,
            'descripcion' => $descripcion,
          );

          if (isset($files["imagen"]) && isset($files["imagen"]) && isset($files["imagen"]["name"]) && !empty($files["imagen"]["name"]) ) {
            $imagen = Gellery::loadPhoto($files["imagen"]);
            $arrfieldscomma["imagen"] = $imagen;
          }

          $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
          $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
          $result = $pdo->query($q);
          if (!$result) {
            $arrjson = Util::error_general('Actualizando de la imagen');
          } else {
            $arrjson = array('output' => array('valid' => true, 'id' => $id));
          }
        } else {
          $arrjson = Util::error_general();
        }

      }else {
        if ($titulo != "" && $descripcion ) {

          $imagen = Gellery::loadPhoto($files["imagen"]);
          $arrfieldscomma["imagen"] = $imagen;

          $q = "INSERT INTO " . $db->getTable('tbl_gallery') . " (created_at, titulo, descripcion, imagen)
                  VALUES ( " . Util::date_now_server() . ", :titulo, :descripcion, :imagen)";
          $result = $pdo->prepare($q);
          $arrparam = array(
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':imagen' => $imagen,
          );
          if ($result->execute($arrparam)) {
            $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
          } else {
            $arrjson = Util::error_general(' Al guardar los datos de la imagen');
          }
        } else {
          $arrjson = Util::error_missing_data();
        }
    }

      $db->closeConect();
      return $arrjson;
  }


  public static function loadPhoto($imagen){
      include_once "../../constants_actualizacion.php";
      if ($imagen['size'] > 0) {
          if ($imagen['error'] < 1) {
              $type_file = explode("/",$imagen['type']);
              if ($type_file['0'] == 'image') {
                  $ruta_img = WWW_ROOT_GALERIA_DIR;
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
