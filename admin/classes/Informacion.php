<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Informacion
{

  public function __construct()
  {
  }

  public static function save($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_municipio_id =  isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : ''; // llega el codigo del municipio
    // $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : 0;
    $tbl_vereda_ids = isset($rqst['tbl_vereda_id']) ? ($rqst['tbl_vereda_id']) : '';
    $observacionesSoci = isset($rqst['observacionesSoci']) ? ($rqst['observacionesSoci']) : '';
    $observacionesEcon = isset($rqst['observacionesEcon']) ? ($rqst['observacionesEcon']) : '';
    $observacionesArm = isset($rqst['observacionesArm']) ? ($rqst['observacionesArm']) : '';
    $factoresSociales = isset($rqst['factoresSociales']) ? ($rqst['factoresSociales']) : '';
    $factoresEcon = isset($rqst['factoresEcon']) ? ($rqst['factoresEcon']) : '';
    $factoresArmad = isset($rqst['factoresArmad']) ? ($rqst['factoresArmad']) : '';
    $tbl_usuario_id =  $_SESSION['session_user']['id'];

    $hr1 = isset($rqst['hr1']) ? ($rqst['hr1']) : '';
    $fecha_hr1 = isset($rqst['fecha_hr1']) ? ($rqst['fecha_hr1']) : '';
    $hr2 = isset($rqst['hr2']) ? ($rqst['hr2']) : '';
    $fecha_hr2 = isset($rqst['fecha_hr2']) ? ($rqst['fecha_hr2']) : '';
    $hr3 = isset($rqst['hr3']) ? ($rqst['hr3']) : '';
    $fecha_hr3 = isset($rqst['fecha_hr3']) ? ($rqst['fecha_hr3']) : '';
    $hr4 = isset($rqst['hr4']) ? ($rqst['hr4']) : '';
    $fecha_hr4 = isset($rqst['fecha_hr4']) ? ($rqst['fecha_hr4']) : '';
    $hr5 = isset($rqst['hr5']) ? ($rqst['hr5']) : '';
    $fecha_hr5 = isset($rqst['fecha_hr5']) ? ($rqst['fecha_hr5']) : '';


    if ($tbl_municipio_id != "" &&  $tbl_vereda_ids != "" && $hr1 != "") {
      try {

        $mensajeHrs = "RECUERDE QUE DEBE INGRESEAR LOS ULTIMOS 5 DIGITOS DEL HR";
        if (!Util::isValidHr($hr1)) {
          return Util::info_general($mensajeHrs . " El hr 1");
        }
        if (!Util::isValidHr($hr2)) {
          return Util::info_general($mensajeHrs . " El hr 2");
        }
        if (!Util::isValidHr($hr3)) {
          return Util::info_general($mensajeHrs . " El hr 3");
        }
        if (!Util::isValidHr($hr4)) {
          return Util::info_general($mensajeHrs . " El hr 4");
        }
        if (!Util::isValidHr($hr5)) {
          return Util::info_general($mensajeHrs . " El hr 5");
        }

        if (empty($factoresSociales) && empty($factoresEcon) && empty($factoresArmad)) {
          return Util::info_general('Debe ingresar información en los factores de social, económico y/o armado ');
        }

        $veredasIds =  (explode(",", $tbl_vereda_ids));
        $veredasIdsCount =  count($veredasIds);

        $db = new DbConection();
        $pdo = $db->openConect();

        for ($x = 0; $x < $veredasIdsCount; $x++) {

          $pdo->beginTransaction();
          $tbl_vereda_id = $veredasIds[$x];

          $tbl_resultados_social_id = 0;
          $tbl_resultados_economico_id = 0;
          $tbl_resultados_armado_id = 0;
          $puntaje = 10;
          $porcentaje = 10;

          //Consultamos los datos de la vereda
          $tbl_brigada_id =  0;
          $tbl_batallon_id = 0;
          $nombre_vereda = "";
          $q = "SELECT * FROM " . $db->getTable('tbl_vereda') . " WHERE id = " . $tbl_vereda_id;
          $result = $pdo->query($q);
          $arr = array();
          if ($result) {
            foreach ($result as $valor) {
              $nombre_vereda = $valor['nombre_vereda'];
              $tbl_brigada_id = $valor['tbl_brigada_id'];
              $tbl_batallon_id = $valor['tbl_batallon_id'];
            }
          }

          if ($tbl_brigada_id  == 0 || $tbl_batallon_id == 0) {
            $db->closeConect();
            return Util::error_general('La vereda seleccionada no tiene una brigada y/o batallón asociada, Favor comunicarse con el administrador.');
          }
          if (isset($_SESSION['session_user']) && ($_SESSION['session_user']['es_gaula']) == 'no') {
            if (!SessionData::superAdministrador()) {
              if (intval($_SESSION['session_user']['tbl_batallon_id']) != $tbl_batallon_id) {
                $db->closeConect();
                return Util::error_general("Su Batallón no corresponde a la información que va a ingresar");
              }
            }
          } 

          //Consultamos los datos del municipio
          $codigo_municipio = 0;
          $q = "SELECT id, codigo_departamento, codigo_muncipio FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_muncipio = " . $tbl_municipio_id;
          $result = $pdo->query($q);
          $arr = array();
          if ($result) {
            foreach ($result as $valor) {
              $tbl_municipio_id = $valor['id']; // Remplazamos el codigo del municpio y ponemos el Id
              $codigo_departamento = $valor['codigo_departamento'];
              $codigo_municipio = $valor['codigo_muncipio'];
            }
          }

          $q0 = "SELECT id FROM " . $db->getTable('tbl_departamentos') . " WHERE codigo_departamento = " . $codigo_departamento;
          $result0 = $pdo->query($q0);
          if ($result0) {
            foreach ($result0 as $valor0) {
              $departamento_id = intval($valor0['id']); // Remplazamos el codigo del municpio y ponemos el Id
            }
          }

          /**-------------------------------------------------------------
           * *    Ingresamos los datos sociales
           *------------------------------------------------------------**/
          $q = "INSERT INTO " . $db->getTable('tbl_resultados_social') . " (created_at, batallon_id, brigada_id, departamento_id, municipio_id, vereda_id, puntaje, porcentaje, observaciones, tbl_usuario_id)
          VALUES ( " . Util::date_now_server() . ", :batallon_id, :brigada_id, :departamento_id, :municipio_id, :vereda_id, :puntaje, :porcentaje, :observaciones, :tbl_usuario_id)";
          $result = $pdo->prepare($q);
          $arrparam = array(
            ':batallon_id' => $tbl_batallon_id,
            ':brigada_id' => $tbl_brigada_id,
            ':departamento_id' =>  $departamento_id,
            ':municipio_id' => $tbl_municipio_id,
            ':vereda_id' =>  $tbl_vereda_id,
            ':puntaje' => $puntaje,
            ':porcentaje' => $porcentaje,
            ':observaciones' => $observacionesSoci,
            ':tbl_usuario_id' => $tbl_usuario_id
          );
          if ($result->execute($arrparam)) {
            $tbl_resultados_social_id =  $pdo->lastInsertId();

            if (!empty($factoresSociales)) {

              $arrayFactoresSociales = json_decode($factoresSociales);
              $c = count($arrayFactoresSociales);
              foreach ($arrayFactoresSociales as $i => $v) {

                $factor_social_id = $v->id;

                if ($factor_social_id > 0) {
                  //Ingresamos las Cantidad de Carencias Sociales
                  $q0 = "INSERT INTO " . $db->getTable('tbl_resultados_x_tbl_social') . " (created_at, tbl_social_id, tbl_resultados_social_id, cantidad)
                          VALUES ( " . Util::date_now_server() . ", :tbl_social_id, :tbl_resultados_social_id, :cantidad)";
                  $result0 = $pdo->prepare($q0);
                  $arrparam0 = array(
                    ':tbl_social_id' =>   $factor_social_id,
                    ':tbl_resultados_social_id' => $tbl_resultados_social_id,
                    ':cantidad' => $v->cantidad
                  );
                  if (!$result0->execute($arrparam0)) {
                    $db->closeConect();
                    return Util::error_general('Registrando cantidades de carencias sociales');
                    break;
                  } else {
                    // Sumamos las cantidad que se tiene más las que se van a ingresar
                    $q0 = "UPDATE  " . $db->getTable('tbl_resultados_social_final') . "
                              SET resultado = (resultado + $v->cantidad) ,
                              resultado_municipio = (resultado_municipio + $v->cantidad)
                              WHERE tbl_social_id = $factor_social_id AND
                              municipio_id = $tbl_municipio_id AND
                              vereda_id = $tbl_vereda_id";
                    $result0 = $pdo->query($q0);
                  }
                }
              }
            }
          } else {
            $db->closeConect();
            return Util::error_general('Registrando resultados sociales');
          }

          /**-------------------------------------------------------------
           * *    Ingresamos los datos economicos
           *------------------------------------------------------------**/
          $q2 = "INSERT INTO " . $db->getTable('tbl_resultados_economico') . " (created_at, brigada_id, batallon_id, departamento_id, municipio_id, vereda_id, puntaje, porcentaje, observaciones, tbl_usuario_id)
          VALUES ( " . Util::date_now_server() . ", :brigada_id, :batallon_id, :departamento_id, :municipio_id, :vereda_id, :puntaje, :porcentaje, :observaciones, :tbl_usuario_id)";
          $result2 = $pdo->prepare($q2);
          $arrparam2 = array(
            ':brigada_id' => $tbl_brigada_id,
            ':batallon_id' => $tbl_batallon_id,
            ':departamento_id' =>  $departamento_id,
            ':municipio_id' => $tbl_municipio_id,
            ':vereda_id' =>  $tbl_vereda_id,
            ':puntaje' => $puntaje,
            ':porcentaje' => $porcentaje,
            ':observaciones' => $observacionesEcon,
            ':tbl_usuario_id' => $tbl_usuario_id
          );

          if ($result2->execute($arrparam2)) {
            $tbl_resultados_economico_id =  $pdo->lastInsertId();

            if (!empty($factoresEcon)) {

              $arrayFactoresEcon = json_decode($factoresEcon);

              $c = count($arrayFactoresEcon);
              foreach ($arrayFactoresEcon as $i => $v) {

                $tbl_economico_id = $v->id;

                if ($tbl_economico_id  > 0) {
                  //Ingresamos las Cantidad de Carencias Sociales
                  $q3 = "INSERT INTO " . $db->getTable('tbl_resultados_x_tbl_economico') . " (created_at, tbl_economico_id, tbl_resultados_economico_id, cantidad)
                        VALUES ( " . Util::date_now_server() . ", :tbl_economico_id, :tbl_resultados_economico_id, :cantidad)";
                  $result3 = $pdo->prepare($q3);
                  $arrparam3 = array(
                    ':tbl_economico_id' =>  $tbl_economico_id,
                    ':tbl_resultados_economico_id' => $tbl_resultados_economico_id,
                    ':cantidad' => $v->cantidad
                  );
                  if (!$result3->execute($arrparam3)) {
                    $db->closeConect();
                    return Util::error_general('Registrando cantidades de carencias economicas');
                    break;
                  } else {
                    // Sumamos las cantidad que se tiene más las que se van a ingresar
                    $q0 = "UPDATE  " . $db->getTable('tbl_resultados_economico_final') . "
                              SET resultado = (resultado + $v->cantidad) ,
                              resultado_municipio = (resultado_municipio + $v->cantidad)
                              WHERE tbl_economico_id = $tbl_economico_id AND
                              municipio_id = $tbl_municipio_id AND
                              vereda_id = $tbl_vereda_id";
                    $result0 = $pdo->query($q0);
                  }
                }
              }
            }
          } else {
            $db->closeConect();
            return Util::error_general('Registrando resultados economicos');
          }

          /**-------------------------------------------------------------
           * *    Ingresamos los datos armados
           *------------------------------------------------------------**/
          $q3 = "INSERT INTO " . $db->getTable('tbl_resultados_armado') . " (created_at, brigada_id, batallon_id, departamento_id, municipio_id, vereda_id, puntaje, porcentaje, observaciones, tbl_usuario_id)
          VALUES ( " . Util::date_now_server() . ", :brigada_id, :batallon_id, :departamento_id, :municipio_id, :vereda_id, :puntaje, :porcentaje, :observaciones, :tbl_usuario_id)";
          $result3 = $pdo->prepare($q3);
          $arrparam3 = array(
            ':brigada_id' => $tbl_brigada_id,
            ':batallon_id' => $tbl_batallon_id,
            ':departamento_id' =>  $departamento_id,
            ':municipio_id' => $tbl_municipio_id,
            ':vereda_id' =>  $tbl_vereda_id,
            ':puntaje' => $puntaje,
            ':porcentaje' => $porcentaje,
            ':observaciones' => $observacionesArm,
            ':tbl_usuario_id' => $tbl_usuario_id
          );

          if ($result3->execute($arrparam3)) {
            $tbl_resultados_armado_id =  $pdo->lastInsertId();

            if (!empty($factoresArmad)) {
              $arrayfactoresArmad = json_decode($factoresArmad);
              $c = count($arrayfactoresArmad);
              foreach ($arrayfactoresArmad as $i => $v) {

                $tbl_armado_id =  $v->id;

                if ($tbl_armado_id > 0) {

                  //Ingresamos las Cantidad de Erradicaciones
                  $q3 = "INSERT INTO " . $db->getTable('tbl_resultados_x_tbl_armado') . " (created_at, tbl_armado_id, tbl_resultados_armado_id, cantidad)
                        VALUES ( " . Util::date_now_server() . ", :tbl_armado_id, :tbl_resultados_armado_id, :cantidad)";
                  $result3 = $pdo->prepare($q3);
                  $arrparam3 = array(
                    ':tbl_armado_id' =>  $tbl_armado_id,
                    ':tbl_resultados_armado_id' => $tbl_resultados_armado_id,
                    ':cantidad' => $v->cantidad
                  );
                  if (!$result3->execute($arrparam3)) {

                    $db->closeConect();
                    return Util::error_general('Registrando cantidades armadas');
                    break;
                  } else {

                    // Sumamos las cantidad que se tiene más las que se van a ingresar
                    $q0 = "UPDATE  " . $db->getTable('tbl_resultados_armado_final') . "
                            SET resultado = (resultado + $v->cantidad) ,
                            resultado_municipio = (resultado_municipio + $v->cantidad)
                            WHERE tbl_armado_id = $tbl_armado_id AND
                            municipio_id = $tbl_municipio_id AND
                            vereda_id = $tbl_vereda_id";
                    $result0 = $pdo->query($q0);
                  }
                }
              }
            }
          } else {
            $db->closeConect();
            return Util::error_general('Registrando resultados armados');
          }

          //Ingresamos los datos de informacion general
          $qInfo = "INSERT INTO " . $db->getTable('tbl_informacion') . " (created_at, tbl_resultados_armado_id, tbl_resultados_economico_id, tbl_resultados_social_id, tbl_usuario_id)
          VALUES ( " . Util::date_now_server() . ", :tbl_resultados_armado_id, :tbl_resultados_economico_id, :tbl_resultados_social_id, :tbl_usuario_id)";
          $resultInfo = $pdo->prepare($qInfo);
          $arrparamInfo = array(
            ':tbl_resultados_armado_id' =>  $tbl_resultados_armado_id,
            ':tbl_resultados_economico_id' => $tbl_resultados_economico_id,
            ':tbl_resultados_social_id' => $tbl_resultados_social_id,
            ':tbl_usuario_id' => $tbl_usuario_id,
          );
          if (!$resultInfo->execute($arrparamInfo)) {
            $db->closeConect();
            return Util::error_general('Registrando información general');
          } else {

            $tbl_informacion_id = $pdo->lastInsertId();
            $pdo->commit();

            // Ingreso de información de Hrs
            $qHrs = "INSERT INTO " . $db->getTable('tbl_hrs_ingreso_informacion') . "  (created_at, tbl_informacion_id, hr1, fecha_hr1, hr2, fecha_hr2, hr3, fecha_hr3, hr4, fecha_hr4, hr5, fecha_hr5) VALUES ( " . Util::date_now_server() . ", :tbl_informacion_id, :hr1, :fecha_hr1, :hr2, :fecha_hr2, :hr3, :fecha_hr3, :hr4, :fecha_hr4, :hr5, :fecha_hr5)";
            $resultHr = $pdo->prepare($qHrs);
            $arrparamHr = array(
              ':tbl_informacion_id' => $tbl_informacion_id,
              ':hr1' => $hr1,
              ':fecha_hr1' =>  $fecha_hr1,
              ':hr2' => $hr2,
              ':fecha_hr2' =>  $fecha_hr2,
              ':hr3' => $hr3,
              ':fecha_hr3' => $fecha_hr3,
              ':hr4' => $hr4,
              ':fecha_hr4' => $fecha_hr4,
              ':hr5' => $hr5,
              ':fecha_hr5' => $fecha_hr5
            );

            if (!$resultHr->execute($arrparamHr)) {
              $db->closeConect();
              return Util::error_general('Ingresando información de hrs');
            }
            // FIN Ingreso de información de Hrs

            $arrjson = array('output' => array('valid' => true, 'response' => $tbl_informacion_id));
          }

          $params =  array(
            'tbl_departamento_id' => $departamento_id,
            'codigo_departamento' => $codigo_departamento,
            'tbl_municipio_id' => $tbl_municipio_id,
            'codigo_muncipio' => $codigo_municipio,
            'vereda' => $nombre_vereda,
            'tbl_vereda_id' => $tbl_vereda_id,
            'tbl_brigada_id' => $tbl_brigada_id,
          );

          Estado::actualizarPuntajesVerMunicBrigDepart($params);
          $db->closeConect();
        }
        return $arrjson;
      } catch (Exception $e) {
        $pdo->rollback();
        $db->closeConect();
        return Util::error_general('En el proceso de ingreso... ');
      }
    } else {
      return Util::error_missing_data();
    }
  }
}
