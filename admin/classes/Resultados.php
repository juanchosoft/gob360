<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Resultados {

    public function __construct(){}


    /**
     * Metodo para obtener los resultados estadisticos por Municipio
     */
    public static function getAll($rqst){
      $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
      $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : 0;
      $fecha_inicio = isset($rqst['fecha_inicio']) ? ($rqst['fecha_inicio']) : '';
      $fecha_fin = isset($rqst['fecha_fin']) ? ($rqst['fecha_fin']) : '';

      if( $codigo_departamento !="" && $codigo_muncipio !="" &&  $codigo_departamento !="" && $codigo_muncipio !="" ){

        $db = new DbConection();
        $pdo = $db->openConect();

        /**===================================================================================================================
         * !            INFORMACION DE ACTUALIZACION
         *
         *==================================================================================================================**/
        // Informacion Armado
        $q="SELECT tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, sum(tbl_resultados_x_tbl_armado_actualizacion.cantidad_bajas) cant_baja, sum(tbl_resultados_x_tbl_armado_actualizacion.cantidad_capturas) cant_capturas, sum(tbl_resultados_x_tbl_armado_actualizacion.cantidad_rat_capturas) rat_capturas,sum( tbl_resultados_x_tbl_armado_actualizacion.cantidad_rat_bajas) rat_bajas, tbl_resultados_x_tbl_armado_actualizacion.created_at, tbl_armado.nombre, tbl_armado.comision, tbl_armado.id as tbl_armado_id
        FROM " . $db->getTable('tbl_armado') . "
        INNER JOIN (( " . $db->getTable('tbl_ciudades') . "
        INNER JOIN (" . $db->getTable('tbl_departamentos') . "
        INNER JOIN (" . $db->getTable('tbl_vereda') . "
        INNER JOIN " . $db->getTable('tbl_resultados_armado_actualizacion') . "  ON tbl_vereda.id = tbl_resultados_armado_actualizacion.vereda_id) ON tbl_departamentos.id = tbl_resultados_armado_actualizacion.departamento_id) ON tbl_ciudades.id = tbl_resultados_armado_actualizacion.municipio_id)
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado_actualizacion') . "  ON tbl_resultados_armado_actualizacion.id = tbl_resultados_x_tbl_armado_actualizacion.tbl_resultados_armado_id) ON tbl_armado.id = tbl_resultados_x_tbl_armado_actualizacion.tbl_armado_id
        WHERE
        tbl_resultados_armado_actualizacion.created_at >= '$fecha_inicio 00:00:01' AND  tbl_resultados_armado_actualizacion.created_at <= '$fecha_fin 23:59:59' AND
        tbl_departamentos.codigo_departamento = '$codigo_departamento' AND
        tbl_ciudades.codigo_muncipio = '$codigo_muncipio'
        GROUP BY tbl_armado.nombre";
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
        }

        // Informacion Sociales
        $q1 = "SELECT tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_batallones.sigla, tbl_sociales.tipo, sum(tbl_resultados_x_tbl_social_actualizacion.cantidad) as cantidad, tbl_sociales.id as tbl_social_id
        FROM " . $db->getTable('tbl_sociales') . "
        INNER JOIN (( " . $db->getTable('tbl_batallones') . "   INNER JOIN (" . $db->getTable('tbl_ciudades') . "  INNER JOIN (" . $db->getTable('tbl_departamentos') . "   INNER JOIN (" . $db->getTable('tbl_vereda') . "  INNER JOIN " . $db->getTable('tbl_resultados_social_actualizacion') . "  ON tbl_vereda.id = tbl_resultados_social_actualizacion.vereda_id) ON tbl_departamentos.id = tbl_resultados_social_actualizacion.departamento_id) ON tbl_ciudades.id = tbl_resultados_social_actualizacion.municipio_id) ON tbl_batallones.id = tbl_resultados_social_actualizacion.batallon_id)
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_social_actualizacion') . "  ON tbl_resultados_social_actualizacion.id = tbl_resultados_x_tbl_social_actualizacion.tbl_resultados_social_id) ON tbl_sociales.id = tbl_resultados_x_tbl_social_actualizacion.tbl_social_id
        WHERE tbl_resultados_social_actualizacion.created_at >= '$fecha_inicio 00:00:01' AND  tbl_resultados_social_actualizacion.created_at <= '$fecha_fin 23:59:59' AND
        tbl_departamentos.codigo_departamento = '$codigo_departamento' AND
        tbl_ciudades.codigo_muncipio = '$codigo_muncipio'
        GROUP BY tbl_sociales.tipo";
        $result1 = $pdo->query($q1);
        $arr1 = array();
        if ($result1) {
            foreach ($result1 as $valor) {
                $arr1[] = $valor;
            }
        }

        // Informacion Economico
        $q2 = "SELECT tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_batallones.sigla, tbl_economico.tipo, SUM(tbl_resultados_x_tbl_economico_actualizacion.cantidad) AS cantidad, tbl_economico.id as tbl_economico_id
        FROM (" . $db->getTable('tbl_ciudades') . " INNER JOIN (" . $db->getTable('tbl_batallones') . " INNER JOIN (" . $db->getTable('tbl_departamentos') . " INNER JOIN (" . $db->getTable('tbl_vereda') . "  INNER JOIN " . $db->getTable('tbl_resultados_economico_actualizacion') . "   ON tbl_vereda.id = tbl_resultados_economico_actualizacion.vereda_id) ON tbl_departamentos.id = tbl_resultados_economico_actualizacion.departamento_id) ON tbl_batallones.id = tbl_resultados_economico_actualizacion.batallon_id) ON tbl_ciudades.id = tbl_resultados_economico_actualizacion.municipio_id)
        INNER JOIN (" . $db->getTable('tbl_economico') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico_actualizacion') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico_actualizacion.tbl_economico_id) ON tbl_resultados_economico_actualizacion.id = tbl_resultados_x_tbl_economico_actualizacion.tbl_resultados_economico_id
        WHERE tbl_resultados_x_tbl_economico_actualizacion.created_at >= '$fecha_inicio 00:00:01' AND  tbl_resultados_x_tbl_economico_actualizacion.created_at <= '$fecha_fin 23:59:59' AND
        tbl_departamentos.codigo_departamento = '$codigo_departamento' AND
        tbl_ciudades.codigo_muncipio = '$codigo_muncipio'
        GROUP BY tbl_economico.tipo";
        $result2 = $pdo->query($q2);
        $arr2 = array();
        if ($result2) {
            foreach ($result2 as $valor) {
                $arr2[] = $valor;
            }
        }
        /**===================================================================================================================
         * !            INFORMACION DE RESULTADOS FINALES POR FACTOR ECNOMICO, SOCIAL Y ARMADO
         *
         *==================================================================================================================**/

        //Consultamos los datos del municipio
        $codigo_departamento = 0;
        $tbl_municipio_id = 0;
        $q = "SELECT id, codigo_departamento FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_muncipio =  '$codigo_muncipio' ";
        $result = $pdo->query($q);
        if ($result) {
            foreach ($result as $valor) {
              $tbl_municipio_id = $valor['id']; // Remplazamos el codigo del municpio y ponemos el Id
              $codigo_departamento = $valor['codigo_departamento'];
            }
        }

         // Factor Armado
        $qResFinalArmadoByMunicipio = "SELECT tbl_armado.nombre, sum(tbl_resultados_armado_final.resultado_municipio) as resultado
        FROM " . $db->getTable('tbl_resultados_armado_final') .  "," . $db->getTable('tbl_armado') . "
        WHERE  tbl_resultados_armado_final.tbl_armado_id = tbl_armado.id  AND
        tbl_resultados_armado_final.municipio_id = $tbl_municipio_id GROUP BY nombre";
        $resultArmadoByMun = $pdo->query($qResFinalArmadoByMunicipio);
        $arrFinalArmadoByMunicipio = array();
        if ($resultArmadoByMun) {
            foreach ($resultArmadoByMun as $valor) {
                $arrFinalArmadoByMunicipio[] = $valor;
            }
        }

         // Factor Social
        $qResFinalSocialByMunicipio = "SELECT tbl_sociales.tipo, sum(tbl_resultados_social_final.resultado_municipio) as resultado
        FROM " . $db->getTable('tbl_resultados_social_final') .  "," . $db->getTable('tbl_sociales') . "
        WHERE  tbl_resultados_social_final.tbl_social_id = tbl_sociales.id  AND
        tbl_resultados_social_final.municipio_id = $tbl_municipio_id GROUP BY tipo";
        $resultSocialByMun = $pdo->query($qResFinalSocialByMunicipio);
        $arrFinalSocialByMunicipio = array();
        if ($resultSocialByMun) {
            foreach ($resultSocialByMun as $valor) {
                $arrFinalSocialByMunicipio[] = $valor;
            }
        }

         // Factor Economico
        $qResFinalEconoByMunicipio = "SELECT tbl_economico.tipo, sum(tbl_resultados_economico_final.resultado_municipio) as resultado
        FROM " . $db->getTable('tbl_resultados_economico_final') .  "," . $db->getTable('tbl_economico') . "
        WHERE  tbl_resultados_economico_final.tbl_economico_id = tbl_economico.id  AND
        tbl_resultados_economico_final.municipio_id = $tbl_municipio_id GROUP BY tipo";
        $resultEconoByMun = $pdo->query($qResFinalEconoByMunicipio);
        $arrFinalEconomicoByMunicipio = array();
        if ($resultEconoByMun) {
            foreach ($resultEconoByMun as $valor) {
                $arrFinalEconomicoByMunicipio[] = $valor;
            }
        }

        $arrjson = array('output' => array(
          'valid' => true,
          'armado' => $arr,
          'armadoResultadoFinal' => $arrFinalArmadoByMunicipio,
          'social' => $arr1,
          'socialResultadoFinal' => $arrFinalSocialByMunicipio,
          'economico' => $arr2,
          'economicoResultadoFinal' => $arrFinalEconomicoByMunicipio,
        ));

        $db->closeConect();
        return $arrjson;

      }else{
        return  Util::error_missing_data();
      }

    }

    public static function getAllVeredaId($rqst){
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? ($rqst['tbl_vereda_id']) : '';
        $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
        $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : 0;

        if( $tbl_vereda_id !="" ) {

            $db = new DbConection();
            $pdo = $db->openConect();

            $q = "SELECT tbl_vereda.id, tbl_vereda.nombre_vereda, tbl_vereda.codigo_vereda, tbl_ciudades.municipio
            FROM " . $db->getTable('tbl_vereda') . "," . $db->getTable('tbl_ciudades') . "
            WHERE tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio AND
            tbl_ciudades.codigo_muncipio = '$codigo_muncipio' AND
            tbl_vereda.nombre_vereda = '$tbl_vereda_id'  ";
            $result = $pdo->query($q);
            if ($result) {
                foreach ($result as $valor) {
                    $tbl_vereda_id = $valor['id'];
                }
            }

          /**===================================================================================================================
           * !            INFORMACION DE RESULTADOS FINALES POR FACTOR ECONOMICO, SOCIAL Y ARMADO
           *
           *==================================================================================================================**/
  
           // Factor Armado
          $qResFinalArmadoByMunicipio = "SELECT tbl_armado.nombre, sum(tbl_resultados_armado_final.resultado) as resultado
          FROM " . $db->getTable('tbl_resultados_armado_final') .  "," . $db->getTable('tbl_armado') . "
          WHERE  tbl_resultados_armado_final.tbl_armado_id = tbl_armado.id  AND
          tbl_resultados_armado_final.vereda_id = $tbl_vereda_id GROUP BY nombre";
          $resultArmadoByMun = $pdo->query($qResFinalArmadoByMunicipio);
          $arrFinalArmadoByMunicipio = array();
          if ($resultArmadoByMun) {
              foreach ($resultArmadoByMun as $valor) {
                  $arrFinalArmadoByMunicipio[] = $valor;
              }
          }
  
           // Factor Social
          $qResFinalSocialByMunicipio = "SELECT tbl_sociales.tipo, sum(tbl_resultados_social_final.resultado) as resultado
          FROM " . $db->getTable('tbl_resultados_social_final') .  "," . $db->getTable('tbl_sociales') . "
          WHERE  tbl_resultados_social_final.tbl_social_id = tbl_sociales.id  AND
          tbl_resultados_social_final.vereda_id = $tbl_vereda_id GROUP BY tipo";
          $resultSocialByMun = $pdo->query($qResFinalSocialByMunicipio);
          $arrFinalSocialByMunicipio = array();
          if ($resultSocialByMun) {
              foreach ($resultSocialByMun as $valor) {
                  $arrFinalSocialByMunicipio[] = $valor;
              }
          }
  
           // Factor Economico
          $qResFinalEconoByMunicipio = "SELECT tbl_economico.tipo, sum(tbl_resultados_economico_final.resultado) as resultado
          FROM " . $db->getTable('tbl_resultados_economico_final') .  "," . $db->getTable('tbl_economico') . "
          WHERE  tbl_resultados_economico_final.tbl_economico_id = tbl_economico.id  AND
          tbl_resultados_economico_final.vereda_id = $tbl_vereda_id GROUP BY tipo";
          $resultEconoByMun = $pdo->query($qResFinalEconoByMunicipio);
          $arrFinalEconomicoByMunicipio = array();
          if ($resultEconoByMun) {
              foreach ($resultEconoByMun as $valor) {
                  $arrFinalEconomicoByMunicipio[] = $valor;
              }
          }
  
          $arrjson = array('output' => array(
            'valid' => true,
            'armadoResultadoFinal' => $arrFinalArmadoByMunicipio,
            'socialResultadoFinal' => $arrFinalSocialByMunicipio,
            'economicoResultadoFinal' => $arrFinalEconomicoByMunicipio,
          ));
  
          $db->closeConect();
          return $arrjson;
  
        }else{
          return  Util::error_missing_data();
        }
  
      }


}
