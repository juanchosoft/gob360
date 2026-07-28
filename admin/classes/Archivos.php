<?php
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Archivos {

    public function __construct(){}


    public static function loadDocumentPdf($documento){
      include_once "../../constants_actualizacion.php";
      if ($documento['size'] > 0) {
          if ($documento['error'] < 1) {
              $name_file = explode(".",$documento['name']);
              if (mb_strtolower($name_file[count($name_file) - 1]) == 'pdf') {
                  $ruta_img = WWW_ROOT_ACTUALIZACION;
                  if (!file_exists($ruta_img)) {
                      mkdir($ruta_img, 0777, true);
                  }
                  $nombre_archivo = rand().'.'.$name_file[count($name_file) - 1];
                  if(move_uploaded_file($documento['tmp_name'], $ruta_img.$nombre_archivo)) {
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


  public static function loadPhoto($imagen){
      include_once "../../constants_actualizacion.php";
      if ($imagen['size'] > 0) {
          if ($imagen['error'] < 1) {
              $type_file = explode("/",$imagen['type']);
              if ($type_file['0'] == 'image') {
                  $ruta_img = WWW_ROOT_ACTUALIZACION;
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
