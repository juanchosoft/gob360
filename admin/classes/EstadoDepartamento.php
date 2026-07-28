<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class EstadoDepartamento {

    /**
     * Metodo para calcular los puntajes y color del  DEPARTAMENTO
     */
    public static function getPuntajeDepartamento($rqst){

        $departamento_id = isset($rqst['departamento_id']) ? intval($rqst['departamento_id']) : 0;
        $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : '';
  
        if( $departamento_id !=""){
  
          $db = new DbConection();
          $pdo = $db->openConect();
  
          $q0 = "SELECT id, puntaje, color FROM " .$db->getTable('tbl_ciudades') . "  WHERE codigo_departamento = '$codigo_departamento'";
          $result0 = $pdo->query($q0);
          $puntajeMunicipio = 0;
          $colorMunicipio = "";
          $color = "";
          if ($result0) {
              foreach ($result0 as $valor0) {
                $puntajeMunicipio = $valor0['puntaje'];
                $colorMunicipio = $valor0['color'];
                $tbl_municipio_id = $valor0['id'];
              }
          }

      
  
          /**========================================================================
           * !                             FINALES INFORMACION
           *========================================================================**/
          $qFinalEconomico ="SELECT tbl_economico.id as tbl_economico_id, tbl_economico.nombre,  tbl_economico.puntaje, tbl_economico.factor, tbl_economico.tipo, sum(tbl_resultados_x_tbl_economico.cantidad) AS anterior,  tbl_resultados_economico.municipio_id
          FROM (" . $db->getTable('tbl_economico') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . " ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
          INNER JOIN " . $db->getTable('tbl_resultados_economico') . " ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id
          WHERE tbl_resultados_economico.departamento_id = $departamento_id GROUP BY tbl_economico.factor";
          $resultFinalEcon = $pdo->query($qFinalEconomico);
          $arrFinalEconomico = array();
          $arrFinalEconomicoTemporal = array();
          $puntajeEconomicoFinalAcumulador = 0;
          $puntajeFactorEcon = 0;
          if ($resultFinalEcon) {
              foreach ($resultFinalEcon as $valor) {
  
                    $id = $valor['tbl_economico_id'];
  
                    // Verificamos que hay en Actualmente
                    $qFinalEconomicoActual ="SELECT tbl_economico.id, tbl_economico.nombre, tbl_economico.tipo, tbl_resultados_economico_final.vereda_id,
                    tbl_resultados_economico_final.municipio_id, sum(tbl_resultados_economico_final.resultado) AS actual
                    FROM " . $db->getTable('tbl_economico') . "
                    INNER JOIN " . $db->getTable('tbl_resultados_economico_final') . "  ON tbl_economico.id = tbl_resultados_economico_final.tbl_economico_id
                    WHERE  tbl_resultados_economico_final.departamento_id = $departamento_id AND tbl_economico.id = $id GROUP BY tbl_economico.factor";
                    $resultActualEcon = $pdo->query($qFinalEconomicoActual);
                    $actual =  $valor['anterior'];
                    if ($resultActualEcon) {
                        foreach ($resultActualEcon as $valor1) {
                          $actual = intval( $valor1['actual'] );
                        }
                    }
  
                  $factor = $valor['factor'];
  
                  if ($actual >= 2){
                      if( $factor == 'NAR' ){
                        $puntajeFactorEcon = 200;
                      }
                      if( $factor == 'MINA'){
                        $puntajeFactorEcon = 200;
                      }
                      if( $factor == 'SEC'){
                        $puntajeFactorEcon = 100;
                      }
                  }
  
                  $puntajeEconomicoFinalAcumulador += $puntajeFactorEcon;
  
                  $arrFinalEconomicoTemporal['tbl_economico_id'] = $id;
                  $arrFinalEconomicoTemporal['puntaje'] = $valor['puntaje'];
                  $arrFinalEconomicoTemporal['tipo'] = $valor['tipo'];
                  $arrFinalEconomicoTemporal['anterior'] =  $valor['anterior'];
                  $arrFinalEconomicoTemporal['actual'] =  $actual;
                  $arrFinalEconomico[] = $arrFinalEconomicoTemporal;
              }
          }
          $puntajeEconomicoFinal = Util::calcularPuntajeByFactor( array('factor' => 'economico', 'puntaje' => $puntajeEconomicoFinalAcumulador));
  
          $qFinalSocial = "SELECT tbl_sociales.id AS tbl_social_id, tbl_sociales.tipo, tbl_sociales.puntaje, sum(tbl_resultados_x_tbl_social.cantidad) AS anterior,  tbl_resultados_social.municipio_id
          FROM (" . $db->getTable('tbl_sociales') . " INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_social') . "  ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
          INNER JOIN " . $db->getTable('tbl_resultados_social') . "  ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id
          WHERE  tbl_resultados_social.departamento_id =  $departamento_id  GROUP BY tbl_sociales.tipo ";
  
          $resultFinalSocial = $pdo->query($qFinalSocial);
          $arrFinalSocial = array();
          $arrFinalSocialTemporal = array();
          $acumuladorFinalSocial = 0;
          if ($resultFinalSocial) {
              foreach ($resultFinalSocial as $valor) {
                  $id = $valor['tbl_social_id'];
  
                  // Verificamos que hay en Actualmente
                   $qFinalSocialActual ="SELECT tbl_sociales.id, sum(tbl_resultados_social_final.resultado) AS actual, tbl_resultados_social_final.vereda_id, tbl_resultados_social_final.municipio_id,tbl_sociales.tipo
                   FROM " . $db->getTable('tbl_sociales') . " INNER JOIN " . $db->getTable('tbl_resultados_social_final') . "  ON tbl_sociales.id = tbl_resultados_social_final.tbl_social_id
                   WHERE tbl_resultados_social_final.departamento_id = $departamento_id AND tbl_sociales.id = $id
                   GROUP BY  tbl_sociales.id";
                  $resultActualSocial = $pdo->query($qFinalSocialActual);
                  $actual =  $valor['anterior'];
                  if ($resultActualSocial) {
                    foreach ($resultActualSocial as $valor1) {
                      $actual = floatval( $valor1['actual'] );
                    }
                  }
  
                  if($actual > 0){
                    $acumuladorFinalSocial += $valor['puntaje'];
                  }
  
                  $arrFinalSocialTemporal['tbl_social_id'] = $id;
                  $arrFinalSocialTemporal['puntaje'] = $valor['puntaje'];
                  $arrFinalSocialTemporal['tipo'] = $valor['tipo'];
                  $arrFinalSocialTemporal['anterior'] =  $valor['anterior'];
                  $arrFinalSocialTemporal['actual'] =  $actual;
                  $arrFinalSocial[] = $arrFinalSocialTemporal;
              }
          }
  
          // Numero de Veredas que se tiene para calcular el promedio
          $numeroDeVeredas = "SELECT Count(tbl_resultados_social.vereda_id) AS resultado,  tbl_resultados_social.municipio_id
          FROM (" . $db->getTable('tbl_sociales') . " INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_social') . " ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
          INNER JOIN " . $db->getTable('tbl_resultados_social') . " ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id
          WHERE tbl_resultados_social.departamento_id = $departamento_id
          GROUP BY  tbl_resultados_social.municipio_id ";
          $resultNumeroDeVeredas = $pdo->query($numeroDeVeredas);
          $cantidadVeredasFactSocial = 0;
          if ($resultNumeroDeVeredas) {
            foreach ($resultNumeroDeVeredas as $valor1) {
              $cantidadVeredasFactSocial = $valor1['resultado'];
            }
          }
  
          $promedioSocial = 0;
          if($cantidadVeredasFactSocial > 0){
            $promedioSocial = ($acumuladorFinalSocial / $cantidadVeredasFactSocial) * $acumuladorFinalSocial;
          }else{
            $promedioSocial = $acumuladorFinalSocial;
          }
  
          $puntajeSocialFinal = Util::calcularPuntajeByFactor( array('factor' => 'social', 'puntaje' => round($promedioSocial,2) ));
  
  
          $qFinalArmado = "SELECT tbl_armado.id AS tbl_armado_id, tbl_armado.nombre, tbl_armado.comision, tbl_armado.puntaje, tbl_armado.frente, tbl_resultados_armado.municipio_id,tbl_resultados_x_tbl_armado.cantidad AS anterior
          FROM ( " . $db->getTable('tbl_armado') . " INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . " ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id)
          INNER JOIN  " . $db->getTable('tbl_resultados_armado') . " ON tbl_resultados_x_tbl_armado.tbl_resultados_armado_id = tbl_resultados_armado.id
          WHERE tbl_resultados_armado.departamento_id = $departamento_id
          GROUP BY tbl_armado.nombre,tbl_armado.comision";
          $resultFinalArmado = $pdo->query($qFinalArmado);
          $arrFinalArmado = array();
          $arrFinalArmadoTemporal = array();
          $acumuladorFinalArmado = 0;
          if ($resultFinalArmado) {
              foreach ($resultFinalArmado as $valor) {
  
                  $id = $valor['tbl_armado_id'];
  
                  // Verificamos que hay en Actualmente
                  $qFinalArmadoActual ="SELECT tbl_armado.id, tbl_armado.nombre, tbl_armado.comision, tbl_resultados_armado_final.resultado AS actual, tbl_resultados_armado_final.vereda_id, tbl_resultados_armado_final.municipio_id
                  FROM " . $db->getTable('tbl_armado') . " INNER JOIN " . $db->getTable('tbl_resultados_armado_final') . " ON tbl_armado.id = tbl_resultados_armado_final.tbl_armado_id
                  WHERE tbl_resultados_armado_final.departamento_id = $departamento_id AND tbl_armado.id = $id GROUP BY tbl_armado.comision";
                  $resultActualArmado = $pdo->query($qFinalArmadoActual);
                  $actual =  $valor['anterior'];
                  if ($resultActualArmado) {
                      foreach ($resultActualArmado as $valor1) {
                        $actual = intval( $valor1['actual'] );
                      }
                  }
  
                  if($actual > 0){
                    $acumuladorFinalArmado += $valor['puntaje'];
                  }
  
                  $arrFinalArmadoTemporal['tbl_armado_id'] = $id;
                  $arrFinalArmadoTemporal['puntaje'] = $valor['puntaje'];
                  $arrFinalArmadoTemporal['nombre'] = $valor['nombre'];
                  $arrFinalArmadoTemporal['frente'] = $valor['frente'];
                  $arrFinalArmadoTemporal['anterior'] =  $valor['anterior'];
                  $arrFinalArmadoTemporal['actual'] =  $actual;
                  $arrFinalArmado[] = $arrFinalArmadoTemporal;
              }
          }
  
          $puntajeArmadoFinal = Util::calcularPuntajeByFactor( array('factor' => 'armado', 'puntaje' => $acumuladorFinalArmado));
  
          /**========================================================================
           * !                      FIN  --- FINALES INFORMACION ---
           *========================================================================**/
  
          $puntaje = floatval($puntajeEconomicoFinal) + floatval($puntajeArmadoFinal) + floatval($puntajeSocialFinal);
          $puntaje = $puntaje > 1000 ? 1000 : $puntaje;
          $color = Util::getColorByPuntaje($puntaje);
  
          $arrjson = array('output' => array('valid' => true,
            'puntaje' => $puntaje,
            'color' => $color
          ));

          $q1 = "UPDATE  " . $db->getTable('tbl_departamentos') . "
          SET 
          color='" . $color . "' ,
          puntaje ='" .  $puntaje . "'
          WHERE id = $departamento_id ";
          $result = $pdo->query($q1);
        
          $db->closeConect();
          return $arrjson;
  
        }else{
          return  Util::error_missing_data();
        }
      }
      
}