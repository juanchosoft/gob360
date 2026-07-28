<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class EstadoMunicipio
{

    public function __construct()
    {
    }

    /**
     * 
     */
    public static function getEstadoMunicipio($rqst)
    {

        $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
        $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : 0;


        if ($codigo_departamento != "" && $codigo_muncipio != "") {

            $db = new DbConection();
            $pdo = $db->openConect();


            $q0 = "SELECT * FROM " . $db->getTable('tbl_ciudades') . "  WHERE codigo_muncipio = '$codigo_muncipio'";
            $result0 = $pdo->query($q0);
            $tbl_municipio_id = 0;
            $nombre_municipio = 0;
            if ($result0) {
                foreach ($result0 as $valor0) {
                    $tbl_municipio_id = $valor0['id'];
                    $nombre_municipio = $valor0['municipio'];
                }
            }


            $q00 = "SELECT tbl_vereda.id, tbl_brigadas.sigla as brigada, tbl_batallones.sigla as batallon FROM " . $db->getTable('tbl_vereda') . ", " . $db->getTable('tbl_brigadas') . "  ," . $db->getTable('tbl_batallones') . "
        WHERE
        tbl_vereda.tbl_brigada_id = tbl_brigadas.id AND
        tbl_vereda.tbl_batallon_id = tbl_batallones.id AND tbl_vereda.municipio_id = $codigo_muncipio LIMIT 1 ";
            $result00 = $pdo->query($q00);
            $vereda_id = 0;
            $batallon = "";
            $brigada = "";
            if ($result00) {
                foreach ($result00 as $valor00) {
                    $vereda_id = $valor00['id'];
                    $batallon = $valor00['batallon'];
                    $brigada = $valor00['brigada'];
                }
            }


            /**========================================================================
             * !                             FINALES INFORMACION
             *========================================================================**/

            $qFinalEconomico = "SELECT tbl_economico.id as tbl_economico_id, tbl_economico.nombre,  tbl_economico.puntaje, tbl_economico.factor,
        tbl_economico.tipo, SUM(tbl_resultados_x_tbl_economico.cantidad) AS anterior, tbl_resultados_economico.vereda_id, tbl_resultados_economico.municipio_id
        FROM (" . $db->getTable('tbl_economico') . " INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . " ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
        INNER JOIN " . $db->getTable('tbl_resultados_economico') . " ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id
        WHERE  tbl_resultados_economico.municipio_id = $tbl_municipio_id GROUP BY tbl_economico.tipo";


  /*       print_r($qFinalEconomico);
        exit(); */

            $resultFinalEcon = $pdo->query($qFinalEconomico);
            $arrFinalEconomico = array();
            $arrFinalEconomicoTemporal = array();
            $puntajeEconomicoFinalAcumulador = 0;
            $puntajeFactorEcon = 0;
            $contadorNAR = 0;
            $contadorSEC = 0;
            $contadorMINA = 0;
            if ($resultFinalEcon) {
                foreach ($resultFinalEcon as $valor) {

                    $id = $valor['tbl_economico_id'];
                    $factor = $valor['factor'];

                    // Verificamos que hay en Actualmente
                    $qFinalEconomicoActual = "SELECT tbl_economico.id, tbl_economico.nombre, tbl_economico.tipo, tbl_resultados_economico_final.vereda_id,
                  tbl_resultados_economico_final.municipio_id, tbl_resultados_economico_final.resultado AS actual
                  FROM " . $db->getTable('tbl_economico') . "
                  INNER JOIN " . $db->getTable('tbl_resultados_economico_final') . "  ON tbl_economico.id = tbl_resultados_economico_final.tbl_economico_id
                  WHERE
                  tbl_resultados_economico_final.municipio_id = $tbl_municipio_id AND
                  tbl_economico.id = $id GROUP BY tbl_economico.tipo";

                    /*                  print_r($qFinalEconomicoActual);
                  exit();   */

                    $resultActualEcon = $pdo->query($qFinalEconomicoActual);
                    $actual =  $valor['anterior'];
                    if ($resultActualEcon) {
                        foreach ($resultActualEcon as $valor1) {
                            $actual = intval($valor1['actual']);
                        }
                    }

                    // if( $actual > 0 ){
                    $arrFinalEconomicoTemporal['tbl_economico_id'] = $id;
                    $arrFinalEconomicoTemporal['puntaje'] = $valor['puntaje'];
                    $arrFinalEconomicoTemporal['tipo'] = $valor['tipo'];
                    $arrFinalEconomicoTemporal['anterior'] =  $valor['anterior'];
                    $arrFinalEconomicoTemporal['actual'] =  $actual;
                    $arrFinalEconomico[] = $arrFinalEconomicoTemporal;
                    // }
                }
            }


            if (count($arrFinalEconomico) > 0) {

                foreach ($arrFinalEconomico as $value) {

                    $idEcono = $value["tbl_economico_id"];
                    if ($value["actual"] > 0) {
                        $qFinalEconomicoActualValidacion = "SELECT tbl_economico.factor, tbl_economico.tipo, tbl_economico.nombre, sum(tbl_economico.puntaje) as actual, tbl_resultados_economico.municipio_id          
                  FROM (" . $db->getTable('tbl_economico') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id) 
                  INNER JOIN " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id
                  WHERE  
                  tbl_resultados_economico.municipio_id = $tbl_municipio_id AND
                  tbl_economico.id =  $idEcono AND
                  tbl_economico.factor IN( 'MINA', 'SEC', 'NAR' )
                  GROUP BY tbl_economico.factor";

                        $resultActualEconValidacion = $pdo->query($qFinalEconomicoActualValidacion);
                        $arrayValidacion  = array();
                        $puntajeFactorEcon = 0;
                        if ($resultActualEconValidacion) {
                            foreach ($resultActualEconValidacion as $valor2) {
                                if ($valor2['actual'] >= 2) {
                                    if ($valor2['factor'] == 'NAR') {
                                        $puntajeFactorEcon = 200;
                                    }
                                    if ($valor2['factor'] == 'MINA') {
                                        $puntajeFactorEcon = 100;
                                    }
                                    if ($valor2['factor'] == 'SEC') {
                                        $puntajeFactorEcon = 100;
                                    }
                                } else {
                                    $puntajeFactorEcon = $valor2['puntaje'];
                                }

                                $puntajeEconomicoFinalAcumulador += $puntajeFactorEcon;
                            }
                        }
                    }
                }
            }

            $puntajeEconomicoFinal = Util::calcularPuntajeByFactor(array('factor' => 'economico', 'puntaje' => $puntajeEconomicoFinalAcumulador));
            /*         print_r($puntajeEconomicoFinal);
        exit();  */

            $qFinalSocial = "SELECT tbl_sociales.id AS tbl_social_id, tbl_sociales.tipo, tbl_sociales.puntaje, SUM(tbl_resultados_x_tbl_social.cantidad) AS anterior, tbl_resultados_social.vereda_id, tbl_resultados_social.municipio_id
        FROM (" . $db->getTable('tbl_sociales') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_social') . "  ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
        INNER JOIN " . $db->getTable('tbl_resultados_social') . "  ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id
        WHERE tbl_resultados_social.municipio_id =  $tbl_municipio_id
        GROUP BY tbl_sociales.tipo ";
            $resultFinalSocial = $pdo->query($qFinalSocial);
            $arrFinalSocial = array();
            $arrFinalSocialTemporal = array();
            $acumuladorFinalSocial = 0;
            if ($resultFinalSocial) {
                foreach ($resultFinalSocial as $valor) {
                    $id = $valor['tbl_social_id'];

                    // Verificamos que hay en Actualmente
                    $qFinalSocialActual = "SELECT tbl_sociales.id, tbl_resultados_social_final.resultado AS actual,
                tbl_resultados_social_final.vereda_id, tbl_resultados_social_final.municipio_id
                FROM " . $db->getTable('tbl_sociales') . " INNER JOIN " . $db->getTable('tbl_resultados_social_final') . "  ON tbl_sociales.id = tbl_resultados_social_final.tbl_social_id
                WHERE tbl_resultados_social_final.municipio_id = $tbl_municipio_id AND
                tbl_sociales.id = $id ";

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
                    /* echo "-----";
                print_r($actual);
                echo "-----";
                exit(); */
                }
            }
            $puntajeSocialFinal = Util::calcularPuntajeByFactor(array('factor' => 'social', 'puntaje' => $acumuladorFinalSocial));

            /**========================================================================
             * !                     Factor Armado
             *========================================================================**/
            $qFinalArmado = "SELECT tbl_armado.id AS tbl_armado_id, tbl_armado.nombre, tbl_armado.puntaje, tbl_armado.frente, tbl_resultados_armado.vereda_id, tbl_resultados_armado.municipio_id,
        SUM(tbl_resultados_x_tbl_armado.cantidad) AS anterior
        FROM ( " . $db->getTable('tbl_armado') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . "   ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id)
        INNER JOIN " . $db->getTable('tbl_resultados_armado') . "  ON tbl_resultados_x_tbl_armado.tbl_resultados_armado_id = tbl_resultados_armado.id
        WHERE tbl_resultados_armado.vereda_id = $vereda_id and
        tbl_resultados_armado.municipio_id = $tbl_municipio_id
        GROUP BY tbl_armado.nombre";
            $resultFinalArmado = $pdo->query($qFinalArmado);
            $arrFinalArmado = array();
            $arrFinalArmadoTemporal = array();
            $acumuladorFinalArmado = 0;
            if ($resultFinalArmado) {
                foreach ($resultFinalArmado as $valor) {

                    $id = $valor['tbl_armado_id'];

                    // Verificamos que hay en Actualmente
                    $qFinalArmadoActual = "SELECT tbl_armado.id, tbl_armado.nombre, tbl_armado.comision, tbl_resultados_armado_final.resultado AS actual, tbl_resultados_armado_final.vereda_id, tbl_resultados_armado_final.municipio_id
                FROM " . $db->getTable('tbl_armado') . " INNER JOIN " . $db->getTable('tbl_resultados_armado_final') . "  ON tbl_armado.id = tbl_resultados_armado_final.tbl_armado_id
                WHERE
                tbl_resultados_armado_final.municipio_id = $tbl_municipio_id AND
                tbl_armado.id = $id";
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

                    if ($valor['anterior'] > 0 && $actual > 0) {
                        $arrFinalArmadoTemporal['tbl_armado_id'] = $id;
                        $arrFinalArmadoTemporal['puntaje'] = $valor['puntaje'];
                        $arrFinalArmadoTemporal['nombre'] = $valor['nombre'];
                        $arrFinalArmadoTemporal['frente'] = $valor['frente'];
                        $arrFinalArmadoTemporal['anterior'] =  $valor['anterior'];
                        $arrFinalArmadoTemporal['actual'] =  $actual;
                        $arrFinalArmado[] = $arrFinalArmadoTemporal;
                    }
                }
            }
            $puntajeArmadoFinal = Util::calcularPuntajeByFactor(array('factor' => 'armado', 'puntaje' => $acumuladorFinalArmado));
            /**========================================================================
             * !                      FIN  --- FINALES INFORMACION ---
             *========================================================================**/


            /**========================================================================
             * !                     Informacion Final de la Vereda
             *========================================================================**/
            $puntajeVeredaFinal = floatval($puntajeEconomicoFinal) + floatval($puntajeArmadoFinal) + floatval($puntajeSocialFinal);
            $puntajeVeredaFinal = $puntajeVeredaFinal > 1000 ? 1000 : $puntajeVeredaFinal;
            $color = Util::getColorByPuntaje($puntajeVeredaFinal);



            /* echo "economico";
print_r($puntajeEconomicoFinal);
echo "<br>";
echo "puntajeArmadoFinal";
echo "<br>";
print_r($puntajeArmadoFinal);
echo "<br>";
echo "puntajeSocialFinal";
echo "<br>";
print_r($puntajeSocialFinal);
echo "<br>";
print_r($color);
echo "<br>";
exit();     */

            // Se consulta la información de actores Asignados MAPA DE ACTORES COMPROMETIDOS EN EJECUTAR LAS CARENCIAS SOCIALES
            $q1 = "SELECT * FROM " . $db->getTable('tbl_actores') . "  WHERE municipio_id = $codigo_muncipio";
            $arrassigned = array();
            $result1 = $pdo->query($q1);
            foreach ($result1 as $valor2) {
                $arrassigned[] = $valor2;
            }

            $arrjson = array('output' => array(
                'valid' => true,
                'puntaje' => $puntajeVeredaFinal,
                'color' => $color,
                'armadoResultadoFinal' => $arrFinalArmado,
                'socialResultadoFinal' => $arrFinalSocial,
                'economicoResultadoFinal' => $arrFinalEconomico,
                'nombre_municipio' => $nombre_municipio,
                'batallon' => $batallon,
                'brigada' => $brigada,
                'mapa_actores_asignados' => $arrassigned,
                'puntajeEconomico' => $puntajeEconomicoFinal,
                'puntajeArmado' => $puntajeArmadoFinal,
                'puntajeSocial' => $puntajeSocialFinal,
            ));

            $db->closeConect();
            return $arrjson;
        } else {
            return  Util::error_missing_data();
        }
    }
}
