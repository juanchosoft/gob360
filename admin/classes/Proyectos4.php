<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Proyectos4
{

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();
    $observaciones = array();

    $q = "SELECT * FROM " . $db->getTable('tbl_proyectos_d4');
    if ($id > 0) {
      $q = "SELECT tbl_proyectos_d4.*, tbl_batallones.sigla as batallon 
      FROM " . $db->getTable('tbl_proyectos_d4') . "," . $db->getTable('tbl_batallones') . " 
      WHERE tbl_proyectos_d4.tbl_batallon_id = tbl_batallones.id AND
      tbl_proyectos_d4.id = " . $id;
      $observaciones = Proyectos4::getObservacionesByProyectoId($id);
    }
    if ($tbl_brigada_id > 0) {
      $q = "SELECT tbl_proyectos_d4.*, tbl_batallones.sigla as batallon 
      FROM " . $db->getTable('tbl_proyectos_d4') . "," . $db->getTable('tbl_batallones') . " 
      WHERE tbl_proyectos_d4.tbl_batallon_id = tbl_batallones.id AND
      tbl_proyectos_d4.tbl_brigada_id = " . $tbl_brigada_id;
    }
    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {
        $arr[] = $valor;
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'observaciones' => $observaciones));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

  public static function getObservacionesByProyectoId($id)
  {

    if ($id > 0) {
      
      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "SELECT * FROM " . $db->getTable('tbl_proyectos_d4_x_observaciones') . " WHERE tbl_proyecto_id = " . $id;
      $result = $pdo->query($q);
      $observaciones = array();
      if($result){
        foreach ($result as $valor) {
          $observaciones[] = $valor;
        }
      }
      $db->closeConect();
      return $observaciones;
    } else {
      return array();
    }
  }


  public static function getProyectosPorBrigada($rqst)
  {

    $db = new DbConection();
    $pdo = $db->openConect();

    $q =  "SELECT tbl_brigadas.id as tbl_brigada_id, tbl_brigadas.sigla, SUM(tbl_proyectos_d4.valor_proyecto) as valor 
    FROM " . $db->getTable('tbl_proyectos_d4') . "  INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_proyectos_d4.tbl_brigada_id = tbl_brigadas.id 
    GROUP BY tbl_brigadas.sigla";
    $result = $pdo->query($q);
    $arr = array();
    $arrTemporal = array();

    if ($result) {
      foreach ($result as $valor) {

        $tbl_brigada_id = $valor['tbl_brigada_id'];
        $arrTemporal['tbl_brigada_id'] = $tbl_brigada_id;
        $arrTemporal['sigla'] = $valor['sigla'];
        $arrTemporal['valor'] = $valor['valor'];

        // Se Busca los objetivos
        $q0 = "SELECT tbl_brigadas.sigla,  tbl_proyectos_d4.objetivo_proyecto, COUNT(tbl_proyectos_d4.objetivo_proyecto) AS cantidad
        FROM " . $db->getTable('tbl_proyectos_d4') . " INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_proyectos_d4.tbl_brigada_id = tbl_brigadas.id
        WHERE tbl_proyectos_d4.tbl_brigada_id = $tbl_brigada_id 
        GROUP BY tbl_proyectos_d4.objetivo_proyecto, tbl_brigadas.sigla ORDER BY tbl_brigadas.sigla";
        $result0 = $pdo->query($q0);
        $cantidadFinal = 0;
        if ($result0) {
          foreach ($result0 as $valor0) {


            $proyectos_beneficio_fuerza = 'PROYECTOS EN BENEFICIO DE LA FUERZA';
     

            $cantidad = intval($valor0['cantidad']);

            switch ($valor0['objetivo_proyecto']) {

              case $proyectos_beneficio_fuerza:
                $arrTemporal[$proyectos_beneficio_fuerza] = $cantidad;
                break;

             }
            $cantidadFinal +=  $cantidad;
          }
          $arrTemporal['total'] = $cantidadFinal;
        }
        $arr[] = $arrTemporal;
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
    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : 0;
    $proyecto =  isset($rqst['proyecto']) ? ($rqst['proyecto']) : '';
    $tbl_batallon_id = isset($rqst['tbl_batallon_id']) ? intval($rqst['tbl_batallon_id']) : 0;
    $tbl_usuario_id =  intval($_SESSION['session_user']['id']);

    $financiacion = isset($rqst['financiacion']) && $rqst['financiacion'] !="" ? ($rqst['financiacion']) : null;
    $financiacion1 = isset($rqst['financiacion1']) && $rqst['financiacion1'] !="" ? ($rqst['financiacion1']) : null;
    $valor_proyecto = isset($rqst['valor_proyecto']) ? floatval($rqst['valor_proyecto']) : 0;
    $contratista = isset($rqst['contratista']) && $rqst['contratista'] !="" ? ($rqst['contratista']) : null;
    $interventoria = isset($rqst['interventoria']) && $rqst['interventoria'] !=""  ? ($rqst['interventoria']) : null;
    $objetivo_proyecto = isset($rqst['objetivo_proyecto']) && $rqst['objetivo_proyecto'] !="" ? ($rqst['objetivo_proyecto']) : null;
    $plazo_construccion = isset($rqst['plazo_construccion']) && $rqst['plazo_construccion'] !="" ? ($rqst['plazo_construccion']) : null;
    $fecha_entrega = isset($rqst['fecha_entrega']) && $rqst['fecha_entrega'] !="" ? ($rqst['fecha_entrega']) : null;
    $estado = isset($rqst['estado']) && $rqst['estado'] !="" ? ($rqst['estado']) : null;
    $porcentaje_ejecucion = isset($rqst['porcentaje_ejecucion']) && $rqst['porcentaje_ejecucion'] !="" ? ($rqst['porcentaje_ejecucion']) : null;
    $observaciones = isset($rqst['observaciones']) && $rqst['observaciones'] !="" ? ($rqst['observaciones']) : null;


    $db = new DbConection();
    $pdo = $db->openConect();

    if ($id > 0) {
      //actualiza la informacion
      $q = "SELECT id FROM " . $db->getTable('tbl_proyectos_d4') . " WHERE id = " . $id;
      $result = $pdo->query($q);
      if ($result) {
        $table = $db->getTable('tbl_proyectos_d4');
        $arrfieldscomma = array(
          'proyecto' => $proyecto,
          'valor_proyecto' => $valor_proyecto,
          'objetivo_proyecto' => $objetivo_proyecto,
          'plazo_construccion' => $plazo_construccion,
          'fecha_entrega' => $fecha_entrega,
          'estado' => $estado,
          'porcentaje_ejecucion' => $porcentaje_ejecucion,
          'observaciones' => $observaciones,
        );

        $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
        $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);

        $result = $pdo->query($q);
        if (!$result) {
          $arrjson = Util::error_general(' Al actualizar los datos de proyectos....');
        } else {
          $qInsert = "INSERT INTO " . $db->getTable('tbl_proyectos_d4_x_observaciones') . " (dtcreate, tbl_proyecto_id, observaciones, tbl_usuario_id) VALUES ( " . Util::date_now_server() . ", :tbl_proyecto_id, :observaciones, :tbl_usuario_id)";
          $resultInsert = $pdo->prepare($qInsert);
          $arrparamInsert = array(
            ':tbl_proyecto_id' => $id,
            ':observaciones' => $observaciones,
            ':tbl_usuario_id' => $tbl_usuario_id
          );
          if ($resultInsert->execute($arrparamInsert)) {
            $arrjson = array('output' => array('valid' => true, 'id' => $id));
          } else {
            $arrjson = Util::error_general(' Al actualizar las observaciones de proyectos.');
          }
        }
      } else {
        $arrjson = Util::error_general();
      }
    } else {
      if ($proyecto != "") {
        $q = "INSERT INTO " . $db->getTable('tbl_proyectos_d4') . " (dtcreate, tbl_brigada_id, proyecto, tbl_batallon_id, financiacion, financiacion1, valor_proyecto, objetivo_proyecto, contratista, interventoria, plazo_construccion, fecha_entrega, estado, porcentaje_ejecucion, observaciones, tbl_usuario_id)
                VALUES ( " . Util::date_now_server() . ", :tbl_brigada_id, :proyecto, :tbl_batallon_id, :financiacion, :financiacion1, :valor_proyecto, :objetivo_proyecto, :contratista, :interventoria, :plazo_construccion, :fecha_entrega, :estado, :porcentaje_ejecucion, :observaciones, :tbl_usuario_id)";
        $result = $pdo->prepare($q);
        $arrparam = array(
          ':tbl_brigada_id' => $tbl_brigada_id,
          ':proyecto' => $proyecto,
          ':tbl_batallon_id' => $tbl_batallon_id,
          ':financiacion' => $financiacion,
          ':financiacion1' => $financiacion1,
          ':valor_proyecto' => $valor_proyecto,
          ':objetivo_proyecto' => $objetivo_proyecto,
          ':contratista' => $contratista,
          ':interventoria' => $interventoria,
          ':plazo_construccion' => $plazo_construccion,
          ':fecha_entrega' => $fecha_entrega,
          ':estado' => $estado,
          ':porcentaje_ejecucion' => $porcentaje_ejecucion,
          ':observaciones' => $observaciones,
          ':tbl_usuario_id' => $tbl_usuario_id
        );
        
        
  


        if ($result->execute($arrparam)) {
          $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
        } else {
          $arrjson = Util::error_general(' Al guardar los datos de proyectos');
        }
      } else {
        $arrjson = Util::error_missing_data();
      }
    }
    $db->closeConect();
    return $arrjson;
  }
}
