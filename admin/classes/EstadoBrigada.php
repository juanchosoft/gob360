<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class EstadoBrigada
{

  /**
   * Metodo para listar los municipios de cada Brigada
   */
  public static function getMunicipiosXBrigada($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : '';

    if ($id > 0) {

      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "SELECT       
      tbl_brigadas_x_municipio.id, 
      tbl_brigadas_x_municipio.tbl_brigada_id, 
      tbl_brigadas_x_municipio.tbl_ciudad_id, 
      tbl_brigadas_x_municipio.carpeta_svg, 
      tbl_brigadas_x_municipio.nombre_svg, tbl_brigadas.sigla, tbl_ciudades.codigo_departamento, tbl_ciudades.codigo_muncipio, tbl_ciudades.municipio, tbl_ciudades.color, tbl_ciudades.puntaje
          FROM " . $db->getTable('tbl_brigadas_x_municipio') . "," . $db->getTable('tbl_brigadas') . "," . $db->getTable('tbl_ciudades') . " 
          WHERE 
          tbl_brigadas_x_municipio.tbl_ciudad_id = tbl_ciudades.id AND
          tbl_brigadas.id = tbl_brigadas_x_municipio.tbl_brigada_id AND tbl_brigadas_x_municipio.tbl_brigada_id = $id ";
      $result = $pdo->query($q);
      $arr = array();
      $arr1 = array();
      $arr12021 = array();
      $arr2 = array();
      $critico = 0;
      $alto = 0;
      $medio = 0;
      $bajo = 0;
      $estable = 0;

      $criticoEstado = "Igual";
      $altoEstado = "Igual";
      $medioEstado = "Igual";
      $bajoEstado = "Igual";
      $estableEstado = "Igual";
      if ($result) {

        foreach ($result as $valor) {
          $arr[] = $valor;
        }

        // Cantidad de veredas por color de la brigada
        $q1 = "SELECT tbl_vereda.color, COUNT(tbl_vereda.color) AS cuenta, tbl_brigadas.id AS tbl_brigada_id  
              FROM " . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
              WHERE tbl_vereda.tbl_brigada_id = $id AND NOT tbl_vereda.color ='0' GROUP BY tbl_vereda.color ORDER BY cuenta ASC";
        $result1 = $pdo->query($q1);
        if ($result1) {
          foreach ($result1 as $valor1) {
            $arr1[] = $valor1;
          }
        }
        // Cantidad de veredas por color de la brigada AÑO 2021
        $q2021 = "SELECT tbl_vereda.color2021, COUNT(tbl_vereda.color2021) AS cuenta, tbl_brigadas.id AS tbl_brigada_id  
              FROM " . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
              WHERE tbl_vereda.tbl_brigada_id = $id AND NOT tbl_vereda.color2021 ='0' GROUP BY tbl_vereda.color2021 ORDER BY cuenta ASC";
        $result2021 = $pdo->query($q2021);
        if ($result2021) {
          foreach ($result2021 as $valor2021) {
            $arr12021[] = $valor2021;
          }
        }

        // Se valida el porcentaje de por color del Año 2021 y 2022
        foreach ($arr12021 as $valor2021) {
          foreach ($arr1 as $valor2022) {

            $color2021 = $valor2021['color2021'];
            $color2022 = $valor2022['color'];

            // Estable
            if( $color2021 == "#387905" && $color2021 == $color2022){
                $estable =round(( ($valor2021['cuenta']-$valor2022['cuenta']) / $valor2022['cuenta']) * 100, 2);
                $estableEstado = Util::validarEstadoColor($estable);
            }
            // Bajo
            if($color2021 == "#0041FE" && $color2021 == $color2022){
              $bajo = round((($valor2021['cuenta']-$valor2022['cuenta']) / $valor2022['cuenta']) * 100, 2);
              $bajoEstado = Util::validarEstadoColorV2($valor2021['cuenta'], $valor2022['cuenta']);
            }
            // Medio
            if($color2021 == "#FEE300" &&  $color2021 == $color2022){
              $medio = round(( ($valor2021['cuenta']-$valor2022['cuenta']) / $valor2022['cuenta']) * 100, 2);
              $medioEstado = Util::validarEstadoColorV2($valor2021['cuenta'], $valor2022['cuenta']);
            }
            // Alto
            if($color2021 == "#F2860D" &&  $color2021 == $color2022){
              $alto = round(( ($valor2021['cuenta']-$valor2022['cuenta']) / $valor2022['cuenta']) * 100, );
              $altoEstado = Util::validarEstadoColorV2($valor2021['cuenta'], $valor2022['cuenta']);
            }
            // Critico
            if($color2021 == "#FC0707" && $color2021 == $color2022){
              $critico = round (( ($valor2021['cuenta']-$valor2022['cuenta']) / $valor2022['cuenta']) * 100 , 2);
              $criticoEstado = Util::validarEstadoColorV2($valor2021['cuenta'], $valor2022['cuenta']);
            }
          }
        }
        // Fin Se valida el porcentaje de por color del Año 2021 y 2022

        // Informacion de vereda y municipio de cada brigada
        $q2 = "SELECT tbl_brigadas.id, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_ciudades.municipio, Count(tbl_vereda.id) AS cantidad
              FROM ((" . $db->getTable('tbl_ciudades') . " 
              INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_ciudades.codigo_muncipio = tbl_vereda.municipio_id) 
              INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
              INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
              WHERE tbl_brigadas.id = $id
              GROUP BY tbl_brigadas.id, tbl_brigadas.sigla, tbl_batallones.sigla, tbl_ciudades.municipio";
        $result2 = $pdo->query($q2);
        if ($result2) {
          foreach ($result2 as $valor2) {
            $arr2[] = $valor2;
          }
        }
        $arrjson = array('output' => array(
          'valid' => true,
          'response' => $arr,
          'cantidadesPorDivision' => $arr1,
          'cantidadesPorDivision2021' => $arr12021,
          'veredas_municipio_x_brigada' => $arr2,
          'estable' => $estable,
          'bajo' => $bajo,
          'medio' => $medio,
          'alto' => $alto,
          'critico' => $critico,
          'estableEstado' => $estableEstado,
          'bajoEstado' => $bajoEstado,
          'medioEstado' => $medioEstado,
          'altoEstado' => $altoEstado,
          'criticoEstado' => $criticoEstado,
        ));
      } else {
        $arrjson = Util::error_no_result();
      }
      $db->closeConect();
      return $arrjson;
    } else {
      return  Util::error_missing_data();
    }
  }

  /**
   * Metodo para calcular los puntajes y color del  DEPARTAMENTO
   */
  public static function getPuntajeByBrigadaId($rqst)
  {

    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : '';

    if ($tbl_brigada_id > 0) {

      $db = new DbConection();
      $pdo = $db->openConect();

      $q0 = "SELECT id, puntaje, color FROM " . $db->getTable('tbl_veredas') . "  WHERE tbl_brigada_id = '$tbl_brigada_id'";
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
      $qFinalEconomico = "SELECT tbl_economico.id as tbl_economico_id, tbl_economico.nombre, tbl_economico.factor, tbl_economico.puntaje, tbl_economico.tipo, sum(tbl_resultados_x_tbl_economico.cantidad) AS anterior, tbl_vereda.municipio_id, tbl_vereda.tbl_brigada_id
          FROM ((" . $db->getTable('tbl_economico') . " INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . " ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
          INNER JOIN " . $db->getTable('tbl_resultados_economico') . " ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id)
          INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_resultados_economico.vereda_id = tbl_vereda.id
          WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id
          GROUP BY tbl_economico.factor";
      $resultFinalEcon = $pdo->query($qFinalEconomico);
      $arrFinalEconomico = array();
      $arrFinalEconomicoTemporal = array();
      $puntajeEconomicoFinalAcumulador = 0;
      $puntajeFactorEcon = 0;
      if ($resultFinalEcon) {
        foreach ($resultFinalEcon as $valor) {

          $id = $valor['tbl_economico_id'];

          // Verificamos que hay en Actualmente
          $qFinalEconomicoActual = "SELECT tbl_economico.id, tbl_economico.nombre, tbl_economico.tipo, tbl_vereda.tbl_brigada_id, tbl_vereda.id, tbl_resultados_economico_final.resultado AS actual, tbl_economico.factor
                    FROM (" . $db->getTable('tbl_economico') . " INNER JOIN " . $db->getTable('tbl_resultados_economico_final') . " ON tbl_economico.id = tbl_resultados_economico_final.tbl_economico_id)
                    INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_economico_final.vereda_id = tbl_vereda.id
                    WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id  AND tbl_economico.id = $id
                    GROUP BY tbl_economico.factor";
          $resultActualEcon = $pdo->query($qFinalEconomicoActual);
          $actual =  $valor['anterior'];
          if ($resultActualEcon) {
            foreach ($resultActualEcon as $valor1) {
              $actual = intval($valor1['actual']);
            }
          }
          $factor = $valor['factor'];
          if ($actual >= 2) {
            if ($factor == 'NAR') {
              $puntajeFactorEcon = 200;
            }
            if ($factor == 'MINA') {
              $puntajeFactorEcon = 200;
            }
            if ($factor == 'SEC') {
              $puntajeFactorEcon = 100;
            }
          } else {
            $puntajeFactorEcon = $valor['puntaje'];
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
      $puntajeEconomicoFinal = Util::calcularPuntajeByFactor(array('factor' => 'economico', 'puntaje' => $puntajeEconomicoFinalAcumulador));


      $qFinalSocial = "SELECT tbl_sociales.id as tbl_social_id, tbl_sociales.tipo, tbl_sociales.puntaje, tbl_resultados_x_tbl_social.cantidad AS anterior, tbl_vereda.municipio_id, tbl_resultados_social.vereda_id, tbl_vereda.tbl_brigada_id
          FROM ((" . $db->getTable('tbl_sociales') . " INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_social') . "  ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
          INNER JOIN " . $db->getTable('tbl_resultados_social') . "   ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id)
          INNER JOIN " . $db->getTable('tbl_vereda') . "   ON tbl_resultados_social.vereda_id = tbl_vereda.id
          WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id GROUP BY tbl_sociales.tipo";
      $resultFinalSocial = $pdo->query($qFinalSocial);
      $arrFinalSocial = array();
      $arrFinalSocialTemporal = array();
      $acumuladorFinalSocial = 0;
      if ($resultFinalSocial) {
        foreach ($resultFinalSocial as $valor) {
          $id = $valor['tbl_social_id'];

          // Verificamos que hay en Actualmente
          $qFinalSocialActual = "SELECT tbl_sociales.id, tbl_sociales.tipo, tbl_vereda.tbl_brigada_id, tbl_resultados_social_final.resultado AS actual, tbl_vereda.id AS vereda_id
                    FROM (" . $db->getTable('tbl_sociales') . " INNER JOIN " . $db->getTable('tbl_resultados_social_final') . " ON tbl_sociales.id = tbl_resultados_social_final.tbl_social_id)
                    INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_resultados_social_final.vereda_id = tbl_vereda.id
                    WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id AND tbl_sociales.id = $id
                    GROUP BY tbl_sociales.id ";
          $resultActualSocial = $pdo->query($qFinalSocialActual);
          $actual =  $valor['anterior'];
          if ($resultActualSocial) {
            foreach ($resultActualSocial as $valor1) {
              $actual = floatval($valor1['actual']);
            }
          }

          if ($actual > 0) {
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
      $numeroDeVeredas = "SELECT Count(tbl_resultados_social.vereda_id) AS resultado, tbl_resultados_social.municipio_id, tbl_vereda.tbl_brigada_id
          FROM ((" . $db->getTable('tbl_resultados_social') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_social') . "   ON tbl_resultados_social.id = tbl_resultados_x_tbl_social.tbl_resultados_social_id)
          INNER JOIN " . $db->getTable('tbl_sociales') . "  ON tbl_resultados_x_tbl_social.tbl_social_id = tbl_sociales.id)
          INNER JOIN " . $db->getTable('tbl_vereda') . "   ON tbl_resultados_social.vereda_id = tbl_vereda.id
          WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id
          GROUP BY tbl_vereda.tbl_brigada_id";
      $resultNumeroDeVeredas = $pdo->query($numeroDeVeredas);
      $cantidadVeredasFactSocial = 0;
      if ($resultNumeroDeVeredas) {
        foreach ($resultNumeroDeVeredas as $valor1) {
          $cantidadVeredasFactSocial = $valor1['resultado'];
        }
      }

      $promedioSocial = 0;
      if ($cantidadVeredasFactSocial > 0 && $acumuladorFinalSocial > 0) {
        $promedioSocial = ($cantidadVeredasFactSocial / $acumuladorFinalSocial);
      } else {
        $promedioSocial = $acumuladorFinalSocial;
      }

      $puntajeSocialFinal = Util::calcularPuntajeByFactor(array('factor' => 'social', 'puntaje' => round($promedioSocial, 2)));

      $qFinalArmado = "SELECT tbl_armado.id as tbl_armado_id, tbl_armado.nombre, tbl_armado.frente, tbl_resultados_armado.puntaje, tbl_resultados_armado.municipio_id, tbl_resultados_x_tbl_armado.cantidad AS anterior, tbl_vereda.tbl_brigada_id
          FROM (" . $db->getTable('tbl_resultados_armado') . " INNER JOIN (" . $db->getTable('tbl_armado') . "
          INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . " ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id) ON tbl_resultados_armado.id = tbl_resultados_x_tbl_armado.tbl_resultados_armado_id)
          INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_resultados_armado.vereda_id = tbl_vereda.id
          WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id GROUP BY tbl_armado.nombre,tbl_armado.frente";

      $resultFinalArmado = $pdo->query($qFinalArmado);
      $arrFinalArmado = array();
      $arrFinalArmadoTemporal = array();
      $acumuladorFinalArmado = 0;
      if ($resultFinalArmado) {
        foreach ($resultFinalArmado as $valor) {

          $id = $valor['tbl_armado_id'];

          // Verificamos que hay en Actualmente
          $qFinalArmadoActual = "SELECT tbl_armado.id, tbl_armado.nombre, tbl_armado.frente, tbl_resultados_armado_final.resultado AS actual, tbl_vereda.id, tbl_vereda.tbl_brigada_id, tbl_armado.id AS armado_id
                  FROM (" . $db->getTable('tbl_armado') . " INNER JOIN " . $db->getTable('tbl_resultados_armado_final') . "  ON tbl_armado.id = tbl_resultados_armado_final.tbl_armado_id)
                  INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_resultados_armado_final.vereda_id = tbl_vereda.id
                  WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id  AND tbl_armado.id = $id GROUP BY tbl_armado.nombre";

          $resultActualArmado = $pdo->query($qFinalArmadoActual);
          $actual =  $valor['anterior'];
          if ($resultActualArmado) {
            foreach ($resultActualArmado as $valor1) {
              $actual = intval($valor1['actual']);
            }
          }

          if ($actual > 0) {
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

      $puntajeArmadoFinal = Util::calcularPuntajeByFactor(array('factor' => 'armado', 'puntaje' => $acumuladorFinalArmado));

      /**========================================================================
       * !                      FIN  --- FINALES INFORMACION ---
       *========================================================================**/

      $puntaje = floatval($puntajeEconomicoFinal) + floatval($puntajeArmadoFinal) + floatval($puntajeSocialFinal);
      $puntaje = $puntaje > 1000 ? 1000 : $puntaje;
      $color = Util::getColorByPuntaje($puntaje);

      $arrjson = array('output' => array(
        'valid' => true,
        'puntaje' => $puntaje,
        'color' => $color
      ));

      $q1 = "UPDATE  " . $db->getTable('tbl_brigadas') . "
          SET
          color='" . $color . "' ,
          puntaje ='" .  $puntaje . "'
          WHERE id = $tbl_brigada_id ";
      $result = $pdo->query($q1);

      $db->closeConect();
      return $arrjson;
    } else {
      return  Util::error_missing_data();
    }
  }
}
