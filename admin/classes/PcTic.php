<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class PcTic
{

  public function __construct() {}

    public static function getAll($rqst)
{
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

   $q = "SELECT 
    tbl_pctic.id,
    tbl_pctic.date,
    tbl_pctic.provincia,
    tbl_pctic.tbl_departamento_id,
    tbl_pctic.tbl_municipio_id,
    tbl_pctic.tbl_vereda_id,
    COALESCE(tbl_pctic.nombre_vereda, tbl_vereda.nombre_vereda) AS nombre_vereda,
    tbl_pctic.tbl_sede_educativa_id,
    tbl_pctic.zona,
    tbl_pctic.robotica,
    tbl_pctic.computadores_institucion,
    tbl_pctic.computador_alumno,
    tbl_pctic.observaciones,
    tbl_pctic.img,
    tbl_pctic.laboratorio_innovacion,
    tbl_pctic.dtcreate,
    tbl_pctic.tbl_usuario_id,
    tbl_ciudades_accion_unificada.municipio,
    tbl_instituciones_educativas.nombre_institucion,
    tbl_sede_educativa.codigo_sede,
    tbl_sede_educativa.nombre AS sede
    FROM " . $db->getTable('tbl_pctic') . "
    INNER JOIN " . $db->getTable('tbl_sede_educativa') . " ON tbl_pctic.tbl_sede_educativa_id = tbl_sede_educativa.id
    INNER JOIN " . $db->getTable('tbl_instituciones_educativas') . " ON tbl_sede_educativa.tbl_instituciones_educativas_id = tbl_instituciones_educativas.id
    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " ON tbl_pctic.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
    LEFT JOIN " . $db->getTable('tbl_vereda') . " ON tbl_pctic.tbl_vereda_id = tbl_vereda.id";


    if ($id > 0) {
        $q .= " WHERE tbl_pctic.id = " . $id;
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



  public static function save($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $date = isset($rqst['date']) ? trim($rqst['date']) : '';
    $provincia = isset($rqst['provincia']) ? trim($rqst['provincia']) : '';
    $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? trim($rqst['tbl_departamento_id']) : '';
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? trim($rqst['tbl_municipio_id']) : '';
    $tbl_vereda_id = null;
    $nombre_vereda = isset($rqst['tbl_vereda_id']) ? trim($rqst['tbl_vereda_id']) : null;
    $tbl_sede_educativa_id = isset($rqst['tbl_sede_educativa_id']) ? trim($rqst['tbl_sede_educativa_id']) : '';
    $zona = isset($rqst['zona']) ? trim($rqst['zona']) : '';
    $robotica = isset($rqst['robotica']) ? trim($rqst['robotica']) : '';
    $computadores_institucion = isset($rqst['computadores_institucion']) ? trim($rqst['computadores_institucion']) : '';
    $computador_alumno = isset($rqst['computador_alumno']) ? trim($rqst['computador_alumno']) : '';
    $observaciones = isset($rqst['observaciones']) ? trim($rqst['observaciones']) : '';
    $laboratorio_innovacion = isset($rqst['laboratorio_innovacion']) ? trim($rqst['laboratorio_innovacion']) : '';
    $tbl_usuario_id =  intval($_SESSION['session_user']['id']);
    $img = isset($_SESSION['file']['nombrearchivo']) ? ($_SESSION['file']['nombrearchivo']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();
    
        if ($id > 0) {
          $q0 = "SELECT * FROM " . $db->getTable('tbl_pctic') . " WHERE id = " . $id;
          $result0 = $pdo->query($q0);
          if ($result0) {
            $table = $db->getTable('tbl_pctic');
            $arrfieldscomma = array(
          'date' => $date,
          'tbl_departamento_id' => $tbl_departamento_id,
          'tbl_municipio_id' => $tbl_municipio_id,
          'nombre_vereda' => $nombre_vereda,


              'provincia' => $provincia,
              'tbl_sede_educativa_id' => $tbl_sede_educativa_id,
              'zona' => $zona,
              'robotica' => $robotica,
              'computadores_institucion' => $computadores_institucion,
              'computador_alumno' => $computador_alumno,
              'observaciones' => $observaciones,
              'img' => $img,
              'laboratorio_innovacion' => $laboratorio_innovacion
           
            );
            $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
            $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
            $result = $pdo->query($q);
    
            // Obtemos el valor de la imagen del producto
            $file = "";
            foreach ($result0 as $valor0) {
              $file = $valor0['img'];
            }
    
            if (!$result) {
              $arrjson = Util::error_general('Actualizando los datos');
            } else {
              $arrjson = array('output' => array('valid' => true, 'id' => $id, 'img' => $file));
            }
          } else {
            $arrjson = Util::error_general();
          }
        } else {
          if ($date != "" || $provincia != "" || $tbl_departamento_id > 0 || $zona != "") {
            $q = "INSERT INTO " . $db->getTable('tbl_pctic') . " (
                dtcreate, date, provincia, tbl_vereda_id, nombre_vereda, tbl_sede_educativa_id, zona, observaciones, 
                robotica, computadores_institucion, computador_alumno, img, laboratorio_innovacion, 
                tbl_departamento_id, tbl_municipio_id, tbl_usuario_id
            ) VALUES (
                " . Util::date_now_server() . ", :date, :provincia, :tbl_vereda_id, :nombre_vereda, :tbl_sede_educativa_id, 
                :zona, :observaciones, :robotica, :computadores_institucion, :computador_alumno, :img, 
                :laboratorio_innovacion, :tbl_departamento_id, :tbl_municipio_id, :tbl_usuario_id
            )";
                                    $result = $pdo->prepare($q);
                                  $arrparam = array(
              ':date' => $date,
              ':provincia' => $provincia,
              ':tbl_vereda_id' => $tbl_vereda_id,
              ':nombre_vereda' => $nombre_vereda,
              ':tbl_sede_educativa_id' => $tbl_sede_educativa_id,
              ':zona' => $zona,
              ':observaciones' => $observaciones,
              ':robotica' => $robotica,
              ':computadores_institucion' => $computadores_institucion,
              ':computador_alumno' => $computador_alumno,
              ':img' => $img,
              ':laboratorio_innovacion' => $laboratorio_innovacion,
              ':tbl_departamento_id' => $tbl_departamento_id,
              ':tbl_municipio_id' => $tbl_municipio_id,
              ':tbl_usuario_id' => $tbl_usuario_id,
            );

    
            //   print_r($q);
            // print_r($arrparam);
            // exit(); 
    
            if ($result->execute($arrparam)) {
              $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
            } else {
              $arrjson = Util::error_general('Ingresando los datos de la visita');
            }
          } else {
            $arrjson = Util::error_missing_data();
          }
        }
        $db->closeConect();
        return $arrjson;
      }
    }
    