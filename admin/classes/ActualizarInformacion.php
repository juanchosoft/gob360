<?php
//error_reporting(E_ERROR |  E_PARSE);

require 'Estado.php';
require 'Archivos.php';
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class ActualizarInformacion
{

  public function __construct()
  {
  }

  /**
   * Metodo acttualizar la opeeratividad de forma manual
   */
  public static function actualizarOperatividadManual($rqst)
  {
    $codigo_departamento =  isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
    $codigo_municipio =  isset($rqst['codigo_municipio']) ? ($rqst['codigo_municipio']) : 0;
    $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? ($rqst['tbl_vereda_id']) : 0;
    $factoresEcon = isset($rqst['factoresEcon']) ? ($rqst['factoresEcon']) : null;
    $tbl_usuario_id = $_SESSION['session_user']['id'];

    $db = new DbConection();
    $pdo = $db->openConect();

    $lab_ch = 0;
    $lab_pbc = 0;
    $erradicacion = 0;
    $upm = 0;
    $idFacEconomico = 0;

    foreach ($factoresEcon as $i => $v) {
      $idFacEconomico = $v->id;
      $cantidad = $v->cantidad;

      if ($idFacEconomico == 1) {
        $erradicacion = $cantidad;
      }
      if ($idFacEconomico == 2) {
        $lab_pbc = $cantidad;
      }
      if ($idFacEconomico == 3) {
        $lab_ch = $cantidad;
      }
      if ($idFacEconomico == 4) {
        $upm = $cantidad;
      }
    }

    // Ingreso de Operatividad
    if ($idFacEconomico > 0) {
      $q = "INSERT INTO " . $db->getTable('tbl_operatividad') . "   (created_at, lab_ch, lab_pbc, erradicacion, upm, tbl_usuario_id, tbl_departamento_id, tbl_municipio_id, tbl_vereda_id) VALUES  ( " . Util::date_now_server() . ", :lab_ch, :lab_pbc, :erradicacion, :upm, :tbl_usuario_id, :tbl_departamento_id, :tbl_municipio_id, :tbl_vereda_id)";
      $result = $pdo->prepare($q);
      $arrparam = array(
        ':lab_ch' => $lab_ch,
        ':lab_pbc' => $lab_pbc,
        ':erradicacion' => $erradicacion,
        ':upm' => $upm,
        ':tbl_usuario_id' => $tbl_usuario_id,
        ':tbl_departamento_id' => $codigo_departamento,
        ':tbl_municipio_id' => $codigo_municipio,
        ':tbl_vereda_id' => $tbl_vereda_id
      );
      if ($result->execute($arrparam)) {
        $arrjson = true;
      } else {
        $arrjson = false;
      }
      $db->closeConect();
      return $arrjson;
    }
    return true;
  }

  public static function save($rqst, $files = [])
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_municipio_id =  isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0; // llega el codigo del municipio
    $tbl_vereda_ids = isset($rqst['tbl_vereda_id']) ? ($rqst['tbl_vereda_id']) : '';

    $tbl_departamento_id_jurisdiccion =  isset($rqst['tbl_departamento_id_jurisdiccion']) ? intval($rqst['tbl_departamento_id_jurisdiccion']) : 0; // llega el codigo del municipio
    $tbl_municipio_id_jurisdiccion =  isset($rqst['tbl_municipio_id_jurisdiccion']) ? intval($rqst['tbl_municipio_id_jurisdiccion']) : 0; // llega el codigo del municipio
    $tbl_vereda_id_jurisdiccion = isset($rqst['tbl_vereda_id_jurisdiccion']) ? intval($rqst['tbl_vereda_id_jurisdiccion']) : 0; // Id de la vereda
    $coordenadas = isset($rqst['coordenadas']) ? ($rqst['coordenadas']) : '';
    $resultado_jurisdiccion = isset($rqst['resultado_jurisdiccion']) ? ($rqst['resultado_jurisdiccion']) : 'si';


    $observacionesSoci = isset($rqst['observacionesSoci']) ? ($rqst['observacionesSoci']) : '';
    $observacionesEcon = isset($rqst['observacionesEcon']) ? ($rqst['observacionesEcon']) : '';
    $observacionesArm = isset($rqst['observacionesArm']) ? ($rqst['observacionesArm']) : '';
    $factoresSociales = isset($rqst['factoresSociales']) ? ($rqst['factoresSociales']) : null;
    $factoresEcon = isset($rqst['factoresEcon']) ? ($rqst['factoresEcon']) : null;
    $factoresArmad = isset($rqst['factoresArmad']) ? ($rqst['factoresArmad']) : null;

    $tbl_usuario_id =  $_SESSION['session_user']['id'];

    $docSocial = isset($files["docSocial"]) ? Archivos::loadDocumentPdf($files["docSocial"]) : '';
    $docEconomico = isset($files["docEconomico"]) ? Archivos::loadDocumentPdf($files["docEconomico"]) : '';
    $docArmado = isset($files["docArmado"]) ? Archivos::loadDocumentPdf($files["docArmado"]) : '';

    $imgSocial = isset($files["imgSocial"]) ? Archivos::loadPhoto($files["imgSocial"]) : '';
    $imgEco = isset($files["imgEco"]) ? Archivos::loadPhoto($files["imgEco"]) : '';
    $imgArm = isset($files["imgArm"]) ? Archivos::loadPhoto($files["imgArm"]) : '';

    $insertDoc = "no";
    if ($docSocial != '' || $docEconomico != '' || $docArmado != '' || $imgSocial != '' || $imgEco != '' || $imgArm != '') {
      $insertDoc = "si";
    }

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

    $factoresSociales = json_decode(json_encode($factoresSociales), true);
    $factoresEcon = json_decode(json_encode($factoresEcon), true);
    $factoresArmad = json_decode(json_encode($factoresArmad), true);

    if ($tbl_municipio_id > 0 &&  $tbl_vereda_ids != "") {

      $menssaje = "Debe ingresar información en los factores de social, económico y/o armado..";
      if ((empty(json_decode($factoresSociales)) && (empty(json_decode($factoresEcon))) && (empty(json_decode($factoresArmad))))) {
        return Util::info_general($menssaje);
      }
      if (is_null($factoresSociales) && is_null($factoresEcon)   && is_null($factoresArmad)) {
        return Util::info_general($menssaje);
      }

      $mensajeHrs = "RECUERDE QUE DEBE INGRESAR LOS ULTIMOS 5 DIGITOS DEL HR QUE ESTA REPORTANDO";
      if (!Util::isValidHr($hr1)) {
        return Util::info_general($mensajeHrs . " EN EL HR 1");
      }
      if (!Util::isValidHr($hr2)) {
        return Util::info_general($mensajeHrs . " EN EL HR 2");
      }
      if (!Util::isValidHr($hr3)) {
        return Util::info_general($mensajeHrs . " EN EL HR 3");
      }
      if (!Util::isValidHr($hr4)) {
        return Util::info_general($mensajeHrs . " EN EL HR 4");
      }
      if (!Util::isValidHr($hr5)) {
        return Util::info_general($mensajeHrs . " EN EL HR 5");
      }

      $veredasIds =  (explode(",", $tbl_vereda_ids));
      $veredasIdsCount =  count($veredasIds);



      try {
        for ($x = 0; $x < $veredasIdsCount; $x++) {

          $db = new DbConection();
          $pdo = $db->openConect();
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

          if ($tbl_brigada_id  == 0) {
            $db->closeConect();
            return Util::error_general('La vereda ' . $nombre_vereda . ' seleccionada no tiene una brigada asociada, Favor comunicarse con el administrador.');
          }
          if ($tbl_batallon_id == 0) {
            $db->closeConect();
            return Util::error_general('La vereda ' . $nombre_vereda . ' seleccionada no tiene batallón asociada, Favor comunicarse con el administrador.');
          }

          //Se valida que el usuario si sea el del batallon y la brigada
          if (isset($_SESSION['session_user']) && ($_SESSION['session_user']['es_gaula']) == 'no') {

            if (intval($_SESSION['session_user']['tbl_batallon_id']) != $tbl_batallon_id) {
              $db->closeConect();
              return Util::error_general("Su Batallón no corresponde a la información que va a ingresar");
            }
          }

          //Consultamos los datos del municipio
          $codigo_municipio = 0;
          $q = "SELECT id, codigo_departamento, codigo_muncipio FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_muncipio = " . $tbl_municipio_id;
          $result = $pdo->query($q);
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
              $departamento_id = $valor0['id']; // Remplazamos el codigo del municpio y ponemos el Id
            }
          }

          /**-------------------------------------------------------------
           * *    Ingresamos los datos sociales
           *------------------------------------------------------------**/
          $q = "INSERT INTO " . $db->getTable('tbl_resultados_social_actualizacion') . " (created_at, batallon_id, brigada_id, departamento_id, municipio_id, vereda_id, puntaje, porcentaje, observaciones, tbl_usuario_id)
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
              foreach ($arrayFactoresSociales as $i => $v) {

                $factor_social_id = $v->id;

                if ($factor_social_id != "") {

                  /**------------------------------------------------------------------------------------------------------------------------
                   * *    Factor Social (Validamos que si exista registro con la informacion que se va actualizar del factor social)
                   *------------------------------------------------------------------------------------------------------------------------**/
                  $qValidarFactSocial = "SELECT tbl_sociales.id as tbl_sociales_id, tbl_batallones.nombre, tbl_batallones.sigla, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_sociales.nombre, tbl_sociales.tipo, tbl_sociales.puntaje, tbl_resultados_x_tbl_social.cantidad, tbl_resultados_social.observaciones
                            FROM (" . $db->getTable('tbl_sociales') . "
                            INNER JOIN (" . $db->getTable('tbl_resultados_x_tbl_social') . "
                            INNER JOIN (((" . $db->getTable('tbl_resultados_social') . "
                            INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_social.batallon_id = tbl_batallones.id)
                            INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_social.municipio_id = tbl_ciudades.id)
                            INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_social.vereda_id = tbl_vereda.id) ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id) ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
                            INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_social.departamento_id = tbl_departamentos.id
                            WHERE
                            tbl_departamentos.id = $departamento_id AND
                            tbl_ciudades.id = $tbl_municipio_id AND
                            tbl_vereda.id  = $tbl_vereda_id AND
                            tbl_resultados_x_tbl_social.tbl_social_id = " . $factor_social_id;
                  $resultValidarFactSocial = $pdo->query($qValidarFactSocial);
                  $arrDatosSoc = array();
                  $cantidadSocialConsolidado = 0;
                  if ($resultValidarFactSocial) {
                    foreach ($resultValidarFactSocial as $val) {
                      $arrDatosSoc[] = $val;
                      $cantidadSocialConsolidado += $val['cantidad'];
                    }
                  }

                  // Se consulta cual factor social
                  $factor = "";
                  $q = "SELECT nombre, tipo FROM " . $db->getTable('tbl_sociales') . " WHERE id = " . $factor_social_id;
                  $result = $pdo->query($q);
                  foreach ($result as $valor) {
                    $factor = $valor['nombre'] . " " . $valor['tipo'];
                  }

                  if (count($arrDatosSoc) == 0) {
                    $db->closeConect();
                    return Util::error_general("El factor social '$factor' en la vereda '$nombre_vereda' no ha sido ingresado previamente al sistema en el departamento, municipio y/o vereda seleccionada");
                    break;
                  }

                  $cantidadIngresadaPorFactorSocial = $v->cantidad;

                  // Consultamos el resultado actual del factor SOCIAL
                  $paramSocial = array('factor' => 'social', 'tbl_municipio_id' => $tbl_municipio_id, 'tbl_vereda_id' => $tbl_vereda_id, 'tbl_social_id' => $factor_social_id);
                  $resultadoDatosActualSocial = Util::getResultFinalByMunByFactor($paramSocial);

                  $resultadoActualSocial = 0;
                  if (!empty($resultadoDatosActualSocial)) {
                    $resultadoActualSocial = $resultadoDatosActualSocial[0]['resultado'];
                    if ($resultadoActualSocial == 0) {
                      $db->closeConect();
                      return Util::error_general("La cantidad a ingresar para el factor social '$factor' la cantidad total correspondiente de carencias fue ingresada en la vereda '$nombre_vereda' ");
                      break;
                    }
                  }

                  if ($resultadoActualSocial > 0 && $cantidadIngresadaPorFactorSocial > $resultadoActualSocial) {
                    $db->closeConect();
                    return Util::error_general("La cantidad a ingresar para el factor social '$factor' no puede ser mayor a '$resultadoActualSocial' y ha ingresado '$cantidadIngresadaPorFactorSocial' en la vereda '$nombre_vereda' ");
                    break;
                  } else {

                    // Consolidado por Factor social del MUNICIPIO
                    $qConsolidadoSocialByMunicipio = "SELECT SUM(tbl_resultados_x_tbl_social.cantidad) as cantidad
                                FROM (" . $db->getTable('tbl_sociales') . "
                                INNER JOIN (" . $db->getTable('tbl_resultados_x_tbl_social') . "
                                INNER JOIN (((" . $db->getTable('tbl_resultados_social') . "
                                INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_social.batallon_id = tbl_batallones.id)
                                INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_social.municipio_id = tbl_ciudades.id)
                                INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_social.vereda_id = tbl_vereda.id) ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id) ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
                                INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_social.departamento_id = tbl_departamentos.id
                                WHERE
                                tbl_departamentos.id = $departamento_id AND
                                tbl_ciudades.id = $tbl_municipio_id AND
                                tbl_resultados_x_tbl_social.tbl_social_id = $factor_social_id
                                GROUP BY tbl_sociales.tipo";


                    $resultConsolidadoSocial = $pdo->query($qConsolidadoSocialByMunicipio);
                    $totalPorFactorByMunicipioId = 0;
                    if ($resultConsolidadoSocial) {
                      foreach ($resultConsolidadoSocial as $value) {
                        $totalPorFactorByMunicipioId += $value['cantidad'];
                      }
                    }

                    if ($resultadoActualSocial == 0) {
                      //Validamos el consolidado vs el ingresado por el usuario
                      if ($cantidadIngresadaPorFactorSocial > $cantidadSocialConsolidado) {
                        $db->closeConect();
                        return Util::error_general("La cantidad a ingresar para el factor social '$factor' en la vereda '$nombre_vereda' no puede ser mayor al consolidado '$cantidadSocialConsolidado' ");
                        break;
                      }

                      // Si no tiene informacioón se procede a guardar el dato de la cantidad actual
                      $q01 = "INSERT INTO " . $db->getTable('tbl_resultados_social_final') . " (created_at, tbl_social_id, tbl_usuario_id, resultado,resultado_municipio, municipio_id, vereda_id )
                                      VALUES ( " . Util::date_now_server() . ", :tbl_social_id, :tbl_usuario_id, :resultado, :resultado_municipio, :municipio_id, :vereda_id)";
                      $result01 = $pdo->prepare($q01);
                      $arrparam01 = array(
                        ':tbl_social_id' => $factor_social_id,
                        ':tbl_usuario_id' => $tbl_usuario_id,
                        ':resultado' =>  floatval($cantidadSocialConsolidado) - floatval($cantidadIngresadaPorFactorSocial),
                        ':resultado_municipio' =>  floatval($totalPorFactorByMunicipioId) - floatval($cantidadIngresadaPorFactorSocial),
                        ':municipio_id' =>  $tbl_municipio_id,
                        ':vereda_id' =>  $tbl_vereda_id
                      );
                      if (!$result01->execute($arrparam01)) {
                        $db->closeConect();
                        return Util::error_general("Registrando resultado actual del factor '$factor' en la vereda '$nombre_vereda' ");
                        break;
                      }
                    } else {

                      //Validamos el consolidado vs el ingresado por el usuario
                      if ($cantidadIngresadaPorFactorSocial > $resultadoActualSocial) {
                        $db->closeConect();
                        return Util::error_general("La cantidad a ingresar para el factor social '$factor' en la vereda '$nombre_vereda' no puede ser mayor a '$resultadoActualSocial' ");
                        break;
                      }

                      // Actualizamos la cantidad actual del factor
                      $cant =  floatVal($resultadoActualSocial) - floatVal($cantidadIngresadaPorFactorSocial);
                      $q1 = "UPDATE  " . $db->getTable('tbl_resultados_social_final') . "
                                      SET
                                      resultado = $cant,
                                      resultado_municipio = (resultado_municipio - $cantidadIngresadaPorFactorSocial),
                                      updated_at = " . Util::date_now_server() . "
                                      WHERE
                                      tbl_social_id = $factor_social_id AND
                                      municipio_id = $tbl_municipio_id AND
                                      vereda_id = $tbl_vereda_id ";
                      $result = $pdo->query($q1);
                    }
                  }

                  //Ingresamos las Cantidad de Carencias Sociales
                  $q0 = "INSERT INTO " . $db->getTable('tbl_resultados_x_tbl_social_actualizacion') . " (created_at, tbl_social_id, tbl_resultados_social_id, cantidad)
                              VALUES ( " . Util::date_now_server() . ", :tbl_social_id, :tbl_resultados_social_id, :cantidad)";
                  $result0 = $pdo->prepare($q0);
                  $arrparam0 = array(
                    ':tbl_social_id' => $factor_social_id,
                    ':tbl_resultados_social_id' => $tbl_resultados_social_id,
                    ':cantidad' => $v->cantidad,
                  );
                  if (!$result0->execute($arrparam0)) {
                    $db->closeConect();
                    return Util::error_general("Registrando cantidades de carencias sociales en la vereda '$nombre_vereda' ");
                    break;
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
          $q2 = "INSERT INTO " . $db->getTable('tbl_resultados_economico_actualizacion') . " (created_at, brigada_id, batallon_id, departamento_id, municipio_id, vereda_id, puntaje, porcentaje, observaciones, tbl_usuario_id)
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


              // Actaulizar Operatividad
              $params =  array(
                'codigo_departamento' => $codigo_departamento,
                'codigo_municipio' => $codigo_municipio,
                'tbl_vereda_id' => $tbl_vereda_id,
                'factoresEcon' => $arrayFactoresEcon
              );
              ActualizarInformacion::actualizarOperatividadManual($params);



              foreach ($arrayFactoresEcon as $i => $v) {

                $tbl_economico_id = $v->id;

                if ($tbl_economico_id != "" && $tbl_economico_id > 0) {

                  /**------------------------------------------------------------------------------------------------------------------------
                   * *    Factor Economico (Validamos que si exista registro con la informacion que se va actualizar del factor economico)
                   *------------------------------------------------------------------------------------------------------------------------**/
                  $qValidarFactEconomico = "SELECT tbl_economico.id as tbl_economico_id, tbl_batallones.sigla, tbl_departamentos.departamento, tbl_departamentos.id, tbl_ciudades.municipio,tbl_ciudades.id, tbl_vereda.nombre_vereda, tbl_vereda.id, tbl_economico.factor, tbl_economico.puntaje, tbl_economico.tipo, tbl_economico.id, tbl_resultados_x_tbl_economico.cantidad, tbl_resultados_economico.observaciones
                            FROM ((((((" . $db->getTable('tbl_economico') . "
                            INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
                            INNER JOIN  " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id)
                            INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_economico.batallon_id = tbl_batallones.id)
                            INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_resultados_economico.brigada_id = tbl_brigadas.id)
                            INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_economico.departamento_id = tbl_departamentos.id)
                            INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_economico.vereda_id = tbl_vereda.id)
                            INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_economico.municipio_id = tbl_ciudades.id
                            WHERE
                            tbl_departamentos.id = $departamento_id AND
                            tbl_ciudades.id = $tbl_municipio_id AND
                            tbl_vereda.id  = $tbl_vereda_id AND
                            tbl_resultados_x_tbl_economico.tbl_economico_id = " . $tbl_economico_id;
                  $resultValidarFactEcon = $pdo->query($qValidarFactEconomico);
                  $arrDatosEc = array();
                  $cantidadEconomicoConsolidado = 0;
                  if ($resultValidarFactEcon) {
                    foreach ($resultValidarFactEcon as $val) {
                      $arrDatosEc[] = $val;
                      $cantidadEconomicoConsolidado += $val['cantidad'];
                    }
                  }

                  // Se consulta cual factor economico
                  $q = "SELECT nombre, tipo FROM " . $db->getTable('tbl_economico') . " WHERE id = " . $tbl_economico_id;
                  $result = $pdo->query($q);
                  $factor = "";
                  foreach ($result as $valor) {
                    $factor = $valor['nombre'] . " " . $valor['tipo'];
                  }

                  if (count($arrDatosEc) == 0) {

/*                     print_r($qValidarFactEconomico);
exit(); */
                    $db->closeConect();
                    return Util::error_general("El factor economico '$factor' en la vereda '$nombre_vereda'  no ha sido ingresado previamente al sistema en el departamento, municipio y/o vereda seleccionada");
                    break;
                  }

                  // Cantidad Ingresada por el usuario
                  $cantidadIngresadaPorFactorEcono = $v->cantidad;

                  // Consultamos el resultado actual del factor
                  $paramEconomico = array('factor' => 'economico', 'tbl_municipio_id' => $tbl_municipio_id, 'tbl_vereda_id' => $tbl_vereda_id, 'tbl_economico_id' => $tbl_economico_id);
                  $resultadoDatosActualEconomico = Util::getResultFinalByMunByFactor($paramEconomico);

                  $resultadoActualEconomico = 0;
                  if (!empty($resultadoDatosActualEconomico)) {
                    $resultadoActualEconomico = $resultadoDatosActualEconomico[0]['resultado'];
                    if ($resultadoActualEconomico == 0) {
                      $db->closeConect();
                      return Util::error_general("La cantidad a ingresar para el factor economico '$factor' la cantidad total correspondiente de erradicaciones fue ingresada en la vereda '$nombre_vereda' ");
                      break;
                    }
                  }


                  if ($resultadoActualEconomico > 0 && $cantidadIngresadaPorFactorEcono > $resultadoActualEconomico) {
                    $db->closeConect();
                    return Util::error_general("La cantidad a ingresar para el factor economico '$factor' no puede ser mayor a '$resultadoActualEconomico' y ha ingresado '$cantidadIngresadaPorFactorEcono' en la vereda '$nombre_vereda'  ");
                    break;
                  } else {

                    // Consolidado por Factor Economico del MUNICIPIO
                    $qConsolidadoEconomico = "SELECT SUM(tbl_resultados_x_tbl_economico.cantidad) as cantidad
                              FROM ((((((" . $db->getTable('tbl_economico') . "
                              INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
                              INNER JOIN " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id)
                              INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_economico.batallon_id = tbl_batallones.id)
                              INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_resultados_economico.brigada_id = tbl_brigadas.id)
                              INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_economico.departamento_id = tbl_departamentos.id)
                              INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_economico.vereda_id = tbl_vereda.id)
                              INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_economico.municipio_id = tbl_ciudades.id
                              WHERE
                              tbl_departamentos.id = $departamento_id AND
                              tbl_ciudades.id = $tbl_municipio_id AND
                              tbl_resultados_x_tbl_economico.tbl_economico_id =  $tbl_economico_id
                              GROUP BY tbl_economico.tipo";
                    $resultConsolidadoEcon = $pdo->query($qConsolidadoEconomico);
                    $totalPorFactorByMunicipioIdEconomico = 0;
                    if ($resultConsolidadoEcon) {
                      foreach ($resultConsolidadoEcon as $value) {
                        $totalPorFactorByMunicipioIdEconomico += $value['cantidad'];
                      }
                    }



                    if (empty($resultadoActualEconomico) && $resultadoActualEconomico == 0) {
                      //Validamos el consolidado vs el ingresado por el usuario
                      if ($cantidadIngresadaPorFactorEcono > $cantidadEconomicoConsolidado) {
                        $db->closeConect();
                        return Util::error_general("La cantidad a ingresar para el factor economico '$factor' no puede ser mayor al consolidado '$cantidadEconomicoConsolidado' en la vereda '$nombre_vereda' ");
                        break;
                      }
                      // Si no tiene informacioón se procede a guardar el dato de la cantidad actual
                      $q01 = "INSERT INTO " . $db->getTable('tbl_resultados_economico_final') . " (created_at, tbl_economico_id, tbl_usuario_id, resultado, resultado_municipio, municipio_id, vereda_id )
                                    VALUES ( " . Util::date_now_server() . ", :tbl_economico_id, :tbl_usuario_id, :resultado, :resultado_municipio, :municipio_id, :vereda_id)";
                      $result01 = $pdo->prepare($q01);
                      $arrparam01 = array(
                        ':tbl_economico_id' => $tbl_economico_id,
                        ':tbl_usuario_id' => $tbl_usuario_id,
                        ':resultado' =>  floatval($cantidadEconomicoConsolidado) - floatval($cantidadIngresadaPorFactorEcono),
                        ':resultado_municipio' =>  floatval($totalPorFactorByMunicipioIdEconomico) - floatval($cantidadIngresadaPorFactorEcono),
                        ':municipio_id' =>  $tbl_municipio_id,
                        ':vereda_id' =>  $tbl_vereda_id
                      );
                      if (!$result01->execute($arrparam01)) {
                        $db->closeConect();
                        return Util::error_general("Registrando resultado actual del factor '$factor' ");
                        break;
                      }
                    } else {
                      //Validamos el consolidado vs el ingresado por el usuario
                      if ($cantidadIngresadaPorFactorEcono > $resultadoActualEconomico) {
                        $db->closeConect();
                        return Util::error_general("La cantidad a ingresar para el factor economico '$factor' no puede ser mayor a '$resultadoActualEconomico' ");
                        break;
                      }

                      // Actualizamos la cantidad actual del factor economico por Veredas y Municipio
                      $cant =  floatVal($resultadoActualEconomico) - floatVal($cantidadIngresadaPorFactorEcono);
                      $q1 = "UPDATE  " . $db->getTable('tbl_resultados_economico_final') . "
                                    SET resultado = $cant,
                                    resultado_municipio = (resultado_municipio - $cantidadIngresadaPorFactorEcono),
                                    updated_at = " . Util::date_now_server() . "
                                    WHERE
                                    tbl_economico_id = $tbl_economico_id AND
                                    municipio_id = $tbl_municipio_id AND
                                    vereda_id = $tbl_vereda_id ";
                      $result = $pdo->query($q1);
                    }
                  }

                  //Ingresamos las Cantidad de Carencias economicas
                  $q3 = "INSERT INTO " . $db->getTable('tbl_resultados_x_tbl_economico_actualizacion') . " (created_at, tbl_economico_id, tbl_resultados_economico_id, cantidad)
                            VALUES ( " . Util::date_now_server() . ", :tbl_economico_id, :tbl_resultados_economico_id, :cantidad)";
                  $result3 = $pdo->prepare($q3);
                  $arrparam3 = array(
                    ':tbl_economico_id' =>  $v->id,
                    ':tbl_resultados_economico_id' => $tbl_resultados_economico_id,
                    ':cantidad' => $v->cantidad
                  );
                  if (!$result3->execute($arrparam3)) {
                    $db->closeConect();
                    return Util::error_general('Registrando cantidades de carencias economicas');
                    break;
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
          $q3 = "INSERT INTO " . $db->getTable('tbl_resultados_armado_actualizacion') . " (created_at, brigada_id, batallon_id, departamento_id, municipio_id, vereda_id, puntaje, porcentaje, observaciones, tbl_usuario_id)
            VALUES ( " . Util::date_now_server() . ", :brigada_id, :batallon_id, :departamento_id, :municipio_id, :vereda_id, :puntaje, :porcentaje, :observaciones, :tbl_usuario_id)";
          $result3 = $pdo->prepare($q3);
          /*         $arrFinalArmadoActualizar = array(); */
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
              foreach ($arrayfactoresArmad as $i => $v) {

                $tbl_armado_id =  $v->id;

                if ($tbl_armado_id != "") {

                  /**------------------------------------------------------------------------------------------------------------------------
                   * *    Factor Armado (Validamos que si exista registro con la informacion que se va actualizar del factor armado)
                   *------------------------------------------------------------------------------------------------------------------------**/
                  $qValidarFactArm = "SELECT tbl_armado.nombre, tbl_resultados_x_tbl_armado.cantidad FROM (((((" . $db->getTable('tbl_armado') . "
                              INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . "  ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id)
                              INNER JOIN " . $db->getTable('tbl_resultados_armado') . "  ON tbl_resultados_x_tbl_armado.tbl_resultados_armado_id = tbl_resultados_armado.id)
                              INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_armado.batallon_id = tbl_batallones.id)
                              INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_armado.vereda_id = tbl_vereda.id)
                              INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_armado.municipio_id = tbl_ciudades.id)
                              INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_armado.departamento_id = tbl_departamentos.id
                              WHERE
                              tbl_ciudades.id = $tbl_municipio_id AND
                              tbl_vereda.id  = $tbl_vereda_id AND
                              tbl_resultados_x_tbl_armado.tbl_armado_id = " . $tbl_armado_id;
                  $resultValidarFactArm = $pdo->query($qValidarFactArm);
                  $arrDatos = array();
                  $cantidadArmadoConsolidado = 0;
                  if ($resultValidarFactArm) {
                    foreach ($resultValidarFactArm as $val) {
                      $arrDatos[] = $val;
                      $cantidadArmadoConsolidado += $val['cantidad'];
                    }
                  }

                  // Se consulta cual factor armado
                  $q = "SELECT nombre, tipo FROM " . $db->getTable('tbl_armado') . " WHERE id = " . $tbl_armado_id;
                  $result = $pdo->query($q);
                  $factor = "";
                  foreach ($result as $valor) {
                    $factor = $valor['nombre'];
                  }
                  if (count($arrDatos) == 0) {
                    $db->closeConect();
                    return Util::error_general("El factor armado '$factor' en la vereda '$nombre_vereda'  no ha sido ingresado previamente al sistema en el departamento, municipio y/o vereda seleccionada");
                    break;
                  }

                  // Cantidad Ingresada por el usuario
                  $cantidadIngresadaPorFactorArmado = floatval($v->bajas) + floatval($v->capturas);

                  // Consultamos el resultado actual del factor
                  $paramArmado = array('factor' => 'armado', 'tbl_municipio_id' => $tbl_municipio_id, 'tbl_vereda_id' => $tbl_vereda_id, 'tbl_armado_id' => $tbl_armado_id);
                  $resultadoDatosActualArmado = Util::getResultFinalByMunByFactor($paramArmado);

                  $resultadoActualArmado = 0;
                  if (!empty($resultadoDatosActualArmado)) {
                    $resultadoActualArmado = $resultadoDatosActualArmado[0]['resultado'];
                    if ($resultadoActualArmado == 0) {
                      $db->closeConect();
                      return Util::error_general("La cantidad a ingresar para el factor armado '$factor' en la vereda '$nombre_vereda'  la cantidad total correspondiente de bajas y capturas fue ingresada ");
                      break;
                    }
                  }

                  if ($resultadoActualArmado > 0 && $cantidadIngresadaPorFactorArmado > $resultadoActualArmado) {
                    $db->closeConect();
                    return Util::error_general("La cantidad a ingresar para el factor armado '$factor' en la vereda '$nombre_vereda'  no puede ser mayor a '$resultadoActualArmado' y ha ingresado '$cantidadIngresadaPorFactorArmado' ");
                    break;
                  } else {

                    // Consolidado por Factor Armado del MUNICIPIO
                    $qConsolidadoArmado = "SELECT tbl_armado.nombre, SUM(tbl_resultados_x_tbl_armado.cantidad) as cantidad
                                  FROM (((((" . $db->getTable('tbl_armado') . "
                                  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . "  ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id)
                                  INNER JOIN " . $db->getTable('tbl_resultados_armado') . "  ON tbl_resultados_x_tbl_armado.tbl_resultados_armado_id = tbl_resultados_armado.id)
                                  INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_armado.batallon_id = tbl_batallones.id)
                                  INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_armado.vereda_id = tbl_vereda.id)
                                  INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_armado.municipio_id = tbl_ciudades.id)
                                  INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_armado.departamento_id = tbl_departamentos.id
                                  WHERE
                                  tbl_departamentos.id = $departamento_id AND
                                  tbl_ciudades.id = $tbl_municipio_id AND
                                  tbl_vereda.id  = $tbl_vereda_id AND
                                  tbl_resultados_x_tbl_armado.tbl_armado_id = $tbl_armado_id
                                  GROUP BY tbl_armado.nombre";
                    $resultConsolidadoEcon = $pdo->query($qConsolidadoArmado);
                    $totalPorFactorByMunicipioIdArmado = 0;
                    if ($resultConsolidadoEcon) {
                      foreach ($resultConsolidadoEcon as $valor) {
                        $totalPorFactorByMunicipioIdArmado += $valor['cantidad'];
                      }
                    }

                    if (empty($resultadoActualArmado) && $resultadoActualArmado == 0) {
                      //Validamos el consolidado vs el ingresado por el usuario
                      if ($cantidadIngresadaPorFactorArmado > $cantidadArmadoConsolidado) {
                        $db->closeConect();
                        return Util::error_general("La cantidad a ingresar para el factor armado '$factor' en la vereda '$nombre_vereda'  no puede ser mayor al consolidado '$cantidadArmadoConsolidado' ");
                        break;
                      }
                      // Si no tiene informacioón se procede a guardar el dato de la cantidad actual
                      $q01 = "INSERT INTO " . $db->getTable('tbl_resultados_armado_final') . " (created_at, tbl_armado_id, tbl_usuario_id, resultado, resultado_municipio, municipio_id, vereda_id )
                                        VALUES ( " . Util::date_now_server() . ", :tbl_armado_id, :tbl_usuario_id, :resultado, :resultado_municipio, :municipio_id, :vereda_id)";
                      $result01 = $pdo->prepare($q01);
                      $arrparam01 = array(
                        ':tbl_armado_id' => $tbl_armado_id,
                        ':tbl_usuario_id' => $tbl_usuario_id,
                        ':resultado' =>  floatval($cantidadArmadoConsolidado) - floatval($cantidadIngresadaPorFactorArmado),
                        ':resultado_municipio' =>  floatval($totalPorFactorByMunicipioIdArmado) - floatval($cantidadIngresadaPorFactorArmado),
                        ':municipio_id' =>  $tbl_municipio_id,
                        ':vereda_id' =>  $tbl_vereda_id
                      );
                      if (!$result01->execute($arrparam01)) {
                        $db->closeConect();
                        return Util::error_general("Registrando resultado actual del factor '$factor' ");
                        break;
                      }
                    } else {
                      //Validamos el consolidado vs el ingresado por el usuario
                      if ($cantidadIngresadaPorFactorArmado > $resultadoActualArmado) {
                        $db->closeConect();
                        return Util::error_general("La cantidad a ingresar para el factor armado '$factor' en la vereda '$nombre_vereda'  no puede ser mayor a '$resultadoActualArmado' ");
                        break;
                      }

                      // Actualizamos la cantidad actual del factor armado
                      $cant =  floatVal($resultadoActualArmado) - floatVal($cantidadIngresadaPorFactorArmado);
                      $q1 = "UPDATE  " . $db->getTable('tbl_resultados_armado_final') . " SET resultado = $cant, updated_at = " . Util::date_now_server() . "
                                        WHERE
                                        tbl_armado_id = $tbl_armado_id AND
                                        municipio_id = $tbl_municipio_id AND
                                        vereda_id = $tbl_vereda_id ";
                      $result = $pdo->query($q1);
                    }
                  }

                  //Ingresamos las Cantidad de Carencias aRMADAS
                  $q3 = "INSERT INTO " . $db->getTable('tbl_resultados_x_tbl_armado_actualizacion') . " (created_at, tbl_armado_id, tbl_resultados_armado_id, cantidad_bajas, cantidad_capturas, cantidad_rat_capturas, cantidad_rat_bajas)
                              VALUES ( " . Util::date_now_server() . ", :tbl_armado_id, :tbl_resultados_armado_id, :cantidad_bajas, :cantidad_capturas, :cantidad_rat_capturas, :cantidad_rat_bajas)";
                  $result3 = $pdo->prepare($q3);
                  $arrparam3 = array(
                    ':tbl_armado_id' =>  $v->id,
                    ':tbl_resultados_armado_id' => $tbl_resultados_armado_id,
                    ':cantidad_bajas' => $v->bajas,
                    ':cantidad_capturas' => $v->capturas,
                    ':cantidad_rat_capturas' => $v->rat_bajas,
                    ':cantidad_rat_bajas' => $v->rat_capturas
                  );
                  if (!$result3->execute($arrparam3)) {
                    $db->closeConect();
                    return Util::error_general('Registrando cantidades armadas');
                    break;
                  }

                  /**========================================================================
                   * !      Generando Array para realizar Actualización Masiva
                   *========================================================================**/
                  /*                 $arrFinalArmadoTemporal = array();
                  $arrFinalArmadoTemporal['tbl_armado_id'] = $v->id;
                  $arrFinalArmadoTemporal['cantidad_actual'] =  $resultadoActualArmado;
                  $arrFinalArmadoActualizar[] = $arrFinalArmadoTemporal; */
                }
              }
            }
          } else {
            $db->closeConect();
            return Util::error_general('Registrando resultados armados');
          }

          //Ingresamos los datos de informacion general
          $qInfo = "INSERT INTO " . $db->getTable('tbl_informacion_actualizacion') . " (created_at, tbl_resultados_armado_actualizacion_id, tbl_resultados_economico_actualizacion_id, tbl_resultados_social_actualizacion_id, tbl_usuario_id)
            VALUES ( " . Util::date_now_server() . ", :tbl_resultados_armado_actualizacion_id, :tbl_resultados_economico_actualizacion_id, :tbl_resultados_social_actualizacion_id, :tbl_usuario_id)";
          $resultInfo = $pdo->prepare($qInfo);
          $arrparamInfo = array(
            ':tbl_resultados_armado_actualizacion_id' =>  $tbl_resultados_armado_id,
            ':tbl_resultados_economico_actualizacion_id' => $tbl_resultados_economico_id,
            ':tbl_resultados_social_actualizacion_id' => $tbl_resultados_social_id,
            ':tbl_usuario_id' => $tbl_usuario_id
          );
          if (!$resultInfo->execute($arrparamInfo)) {
            $db->closeConect();
            return Util::error_general('Registrando actualización de la información general ');
          } else {
            $lastInsertId = $pdo->lastInsertId();
            $pdo->commit();

            // Información de Jurisdiccion
            if ($resultado_jurisdiccion == 'no') {
              $qJurisdiccion = "INSERT INTO " . $db->getTable('tbl_jurisdiccion_actualizacion') . "  (created_at, tbl_informacion_actualizacion_id, departamento_id, municipio_id, vereda_id, coordenadas) VALUES ( " . Util::date_now_server() . ", :tbl_informacion_actualizacion_id, :departamento_id, :municipio_id, :vereda_id, :coordenadas)";
              $resultJurisdiccion = $pdo->prepare($qJurisdiccion);
              $arrparamJurisdiccion = array(
                ':tbl_informacion_actualizacion_id' => $lastInsertId,
                ':departamento_id' => $tbl_departamento_id_jurisdiccion,
                ':municipio_id' => $tbl_municipio_id_jurisdiccion,
                ':vereda_id' => $tbl_vereda_id_jurisdiccion,
                ':coordenadas' => $coordenadas
              );
              if (!$resultJurisdiccion->execute($arrparamJurisdiccion)) {
                $db->closeConect();
                return Util::error_general('Registrando informacion de Jurisdiccion');
              }
            }
            // Fin Ingreso de Información de Jurisdiccion

            // Ingreso de información de Hrs
            $qHrs = "INSERT INTO " . $db->getTable('tbl_hrs_actualizacion') . "  (created_at, tbl_informacion_actualizacion_id, hr1, fecha_hr1, hr2, fecha_hr2, hr3, fecha_hr3, hr4, fecha_hr4, hr5, fecha_hr5) VALUES ( " . Util::date_now_server() . ", :tbl_informacion_actualizacion_id, :hr1, :fecha_hr1, :hr2, :fecha_hr2, :hr3, :fecha_hr3, :hr4, :fecha_hr4, :hr5, :fecha_hr5)";
            $resultHr = $pdo->prepare($qHrs);
            $arrparamHr = array(
              ':tbl_informacion_actualizacion_id' => $lastInsertId,
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
              return Util::error_general('Ingresando información de hrs. En Actualización');
            }
            // FIN Ingreso de información de Hrs

            if ($insertDoc == 'si') {
              $qDocumentos = "INSERT INTO " . $db->getTable('tbl_documentos_actualizacion') . " (created_at, tbl_informacion_actualizacion_id, departamento_id, municipio_id, vereda_id, tbl_usuario_id, pdf_social, pdf_economico, pdf_armado, img_social, img_economico, img_armado) VALUES ( " . Util::date_now_server() . ", :tbl_informacion_actualizacion_id, :departamento_id, :municipio_id, :vereda_id, :tbl_usuario_id, :pdf_social, :pdf_economico, :pdf_armado, :img_social, :img_economico, :img_armado)";
              $resultDoc = $pdo->prepare($qDocumentos);
              $arrparamDoc = array(
                ':tbl_informacion_actualizacion_id' => $lastInsertId,
                ':departamento_id' => $departamento_id,
                ':municipio_id' => $tbl_municipio_id,
                ':vereda_id' => $tbl_vereda_id,
                ':tbl_usuario_id' => $tbl_usuario_id,
                ':pdf_social' => $docSocial,
                ':pdf_economico' => $docEconomico,
                ':pdf_armado' => $docArmado,
                ':img_social' => $imgSocial,
                ':img_economico' => $imgEco,
                ':img_armado' => $imgArm
              );
              if (!$resultDoc->execute($arrparamDoc)) {
                $db->closeConect();
                return Util::error_general('Registrando documentos');
              } else {
                $arrjson = array('output' => array('valid' => true, 'response' => $lastInsertId));
              }
            } else {
              $arrjson = array('output' => array('valid' => true, 'response' => $lastInsertId));
            }
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

          /*         // Actualizacion Masiva
          $paramsActualizacionMasivaVeredas = array(
            'data_factor_armado' =>  $arrFinalArmadoActualizar,
            'codigo_departamento' =>  $codigo_departamento,
            'codigo_municipio' => $codigo_municipio,
            'tbl_vereda_id' =>  $tbl_vereda_id
          );
          ActualizarInformacion::actualizarFormaMasivaVeredasFactorArmado($paramsActualizacionMasivaVeredas); */
        }
        $db->closeConect();
        return $arrjson;
      } catch (Exception $e) {
        $pdo->rollback();
        $db->closeConect();
        return Util::error_general('En el proceso de actualización... ' . $e);
      }
    } else {
      return Util::error_missing_data();
    }
  }


  /**
   * Metodo para realizar la validación de las Veredas para actualizar de forma Masiva
   */
  public static function actualizarFormaMasivaVeredasFactorArmado($rqst)
  {

    $data_factor_armado = isset($rqst['data_factor_armado']) ? ($rqst['data_factor_armado']) : null; // Ids del Factor y Puntaje del Factor
    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
    $codigo_municipio = isset($rqst['codigo_municipio']) ? ($rqst['codigo_municipio']) : 0;
    $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : '';

    if ($tbl_vereda_id > 0 && $codigo_municipio > 0) {

      $db = new DbConection();
      $pdo = $db->openConect();

      // Veredas del Municipio de la Veredas que se va actualizar el factor ARMADO, Para actualizacion MASIVA
      $qVeredasMismoMunicipio = "SELECT tbl_departamentos.departamento,
      tbl_ciudades.municipio, 
      tbl_ciudades.codigo_muncipio, 
      tbl_vereda.departamento_id, 
      tbl_batallones.sigla AS batallon, 
      tbl_vereda.nombre_vereda, tbl_vereda.id AS tbl_vereda_id
      FROM (((" . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) ) 
      INNER JOIN  " . $db->getTable('tbl_ciudades ') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio) 
      INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_vereda.departamento_id = tbl_departamentos.codigo_departamento
      WHERE tbl_vereda.municipio_id = $codigo_municipio AND
      tbl_departamentos.codigo_departamento = $codigo_departamento";
      $resultVeredasMismoMunicipio = $pdo->query($qVeredasMismoMunicipio);

/*       print_r($qVeredasMismoMunicipio); */

      if ($resultVeredasMismoMunicipio) {
        foreach ($resultVeredasMismoMunicipio as $valueMismoMunc) {

          $idVeredaMismoMun = $valueMismoMunc['tbl_vereda_id'];

          // Se recorre los Factores que fueron Actualizados de la Vereda 
          foreach ($data_factor_armado as $i => $v) {

            $tbl_armado_id =  $v['tbl_armado_id'];
            $cantidad_actual_vereda_actualizar =  $v['cantidad_actual'];

            // Verificamos que hay en Actualmente con el Id de la Vereda y Id del Factor Armado
            $qFinalArmadoActual = "SELECT tbl_armado.id, tbl_armado.nombre, tbl_armado.comision, tbl_resultados_armado_final.resultado AS actual, tbl_resultados_armado_final.vereda_id, tbl_resultados_armado_final.municipio_id
              FROM " . $db->getTable('tbl_armado') . " INNER JOIN " . $db->getTable('tbl_resultados_armado_final') . "  ON tbl_armado.id = tbl_resultados_armado_final.tbl_armado_id
              WHERE 
              tbl_resultados_armado_final.vereda_id = $idVeredaMismoMun AND
              tbl_armado.id = $tbl_armado_id AND
              tbl_resultados_armado_final.resultado = $cantidad_actual_vereda_actualizar";

/*             print_r($qFinalArmadoActual);
            exit(); */

            $resultActualArmado = $pdo->query($qFinalArmadoActual);
            $data = array();
            if ($resultActualArmado) {
              foreach ($resultActualArmado as $valor1) {
                $data[] = $valor1;
              }
              if (count($data) > 0) {
                // Actualizamos la cantidad actual del factor armado
                $q1 = "UPDATE  " . $db->getTable('tbl_resultados_armado_final') . " 
                                  SET resultado = $cantidad_actual_vereda_actualizar, 
                                  updated_at = " . Util::date_now_server() . "
                                  WHERE tbl_armado_id = $tbl_armado_id AND
                                  municipio_id = $codigo_municipio AND
                                  vereda_id = $tbl_vereda_id ";
                $result = $pdo->query($q1);

 /*                print_r($q1);
                exit(); */
              }
            }
          }
        }
      }
    } else {
      return  Util::error_missing_data();
    }
  }
}
