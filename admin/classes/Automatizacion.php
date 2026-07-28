<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Automatizacion {

    public function __construct(){}

      /**
       * Funcion para validar y Eliminar Información de Municipio Automatizada
       */
    public static function eliminarInfoDeMunicioPorFactores($rqst){

        $tbl_batallon_id = isset($rqst['batallon_id']) ? intval($rqst['batallon_id']) : 0;
        $tbl_municipio_id = isset($rqst['municipio_id']) ? intval($rqst['municipio_id']) : 0;
        $factor_social = isset($rqst['factor_social']) ? ($rqst['factor_social']) : 'false';
        $factor_armado = isset($rqst['factor_armado']) ? ($rqst['factor_armado']) : 'false';
        $factor_economico = isset($rqst['factor_economico']) ? ($rqst['factor_economico']) : 'false';

        if ($tbl_municipio_id == 0 ) {
          return Util::error_general('Debe seleccionar un municipio');
        }
        if ($factor_social == 'false' && $factor_armado == 'false' && $factor_economico == 'false'  ) {
          return Util::error_general('Debe seleccionar al menos un factor');
        }
          
        $db = new DbConection();
        $pdo = $db->openConect();

        // Factor Armado
        if($factor_armado == 'true'){
          $qArmado1 = "DELETE FROM " . $db->getTable('tbl_resultados_armado') . " WHERE municipio_id =$tbl_municipio_id";
          $resultArmado1 = $pdo->query($qArmado1);

          $qArmado2 = "DELETE FROM " . $db->getTable('tbl_resultados_armado_final') . " WHERE municipio_id = $tbl_municipio_id";
          $resultArmado2 = $pdo->query($qArmado2);

          $qArmado3 = "DELETE FROM " . $db->getTable('tbl_resultados_armado_actualizacion') . " WHERE municipio_id = $tbl_municipio_id";
          $resultArmado3 = $pdo->query($qArmado3);
        }

        // Factor Social
        if($factor_social == 'true'){
          $qSocial1 = "DELETE FROM " . $db->getTable('tbl_resultados_social') . " WHERE municipio_id = '" . $tbl_municipio_id . "'";
          $resultArmado1 = $pdo->query($qSocial1);

          $qSocial2 = "DELETE FROM " . $db->getTable('tbl_resultados_social_final') . " WHERE municipio_id = '" . $tbl_municipio_id . "'";
          $resultArmado2 = $pdo->query($qSocial2);

          $qSocial3 = "DELETE FROM " . $db->getTable('tbl_resultados_social_actualizacion') . " WHERE municipio_id = '" . $tbl_municipio_id . "'";
          $resultArmado3 = $pdo->query($qSocial3);
        }

        // Factor Economico
        if($factor_economico == 'true'){
          $qEconomico1 = "DELETE FROM " . $db->getTable('tbl_resultados_economico') . " WHERE municipio_id = '" . $tbl_municipio_id . "'";
          $resultArmado1 = $pdo->query($qEconomico1);

          $qEconomico2 = "DELETE FROM " . $db->getTable('tbl_resultados_economico_final') . " WHERE municipio_id = '" . $tbl_municipio_id . "'";
          $resultArmado2 = $pdo->query($qEconomico2);

          $qEconomico3 = "DELETE FROM " . $db->getTable('tbl_resultados_economico_actualizacion') . " WHERE municipio_id = '" . $tbl_municipio_id . "'";
          $resultArmado3 = $pdo->query($qEconomico3);
        }

        $arrjson = array('output' => array('valid' => true));

        $db->closeConect();
        return $arrjson;
    }

      /**
     * Funcion para validar t Eliminar Información de Vereda Automatizada
     */
    public static function eliminarInfoDVeredaPorFactores($rqst){
        $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : '';
        $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : '';
        $vereda = isset($rqst['vereda']) ? ($rqst['vereda']) : '';

        $factor_social = isset($rqst['factor_social']) ? ($rqst['factor_social']) : 'false';
        $factor_armado = isset($rqst['factor_armado']) ? ($rqst['factor_armado']) : 'false';
        $factor_economico = isset($rqst['factor_economico']) ? ($rqst['factor_economico']) : 'false';

        if( $codigo_departamento =="" || $codigo_muncipio ==""  || $vereda ==""){
          return Util::error_general('Debe seleccionar un departamento, municipio y/o vereda');
        }
        if ($factor_social == 'false' && $factor_armado == 'false' && $factor_economico == 'false'  ) {
          return Util::error_general('Debe seleccionar al menos un factor');
        }
          
        $db = new DbConection();
        $pdo = $db->openConect();

        $q01 = "SELECT * FROM " .$db->getTable('tbl_vereda') . "  
        WHERE nombre_vereda = '$vereda'  AND departamento_id = '$codigo_departamento'  AND municipio_id = '$codigo_muncipio' ";
        $result01 = $pdo->query($q01);

        if ($result01) {
            foreach ($result01 as $valor01) {

              $tbl_vereda_id = $valor01['id'];

               // Factor Armado
              if($factor_armado == 'true'){
                $qArmado1 = "DELETE FROM " . $db->getTable('tbl_resultados_armado') . " WHERE vereda_id =$tbl_vereda_id";
                $resultArmado1 = $pdo->query($qArmado1);

                $qArmado2 = "DELETE FROM " . $db->getTable('tbl_resultados_armado_final') . " WHERE vereda_id = $tbl_vereda_id";
                $resultArmado2 = $pdo->query($qArmado2);

                $qArmado3 = "DELETE FROM " . $db->getTable('tbl_resultados_armado_actualizacion') . " WHERE vereda_id = $tbl_vereda_id";
                $resultArmado3 = $pdo->query($qArmado3);
              }

              // Factor Social
              if($factor_social == 'true'){
                $qSocial1 = "DELETE FROM " . $db->getTable('tbl_resultados_social') . " WHERE vereda_id = '" . $tbl_vereda_id . "'";
                $resultArmado1 = $pdo->query($qSocial1);

                $qSocial2 = "DELETE FROM " . $db->getTable('tbl_resultados_social_final') . " WHERE vereda_id = '" . $tbl_vereda_id . "'";
                $resultArmado2 = $pdo->query($qSocial2);

                $qSocial3 = "DELETE FROM " . $db->getTable('tbl_resultados_social_actualizacion') . " WHERE vereda_id = '" . $tbl_vereda_id . "'";
                $resultArmado3 = $pdo->query($qSocial3);
              }

              // Factor Economico
              if($factor_economico == 'true'){
                $qEconomico1 = "DELETE FROM " . $db->getTable('tbl_resultados_economico') . " WHERE vereda_id = '" . $tbl_vereda_id . "'";
                $resultArmado1 = $pdo->query($qEconomico1);

                $qEconomico2 = "DELETE FROM " . $db->getTable('tbl_resultados_economico_final') . " WHERE vereda_id = '" . $tbl_vereda_id . "'";
                $resultArmado2 = $pdo->query($qEconomico2);

                $qEconomico3 = "DELETE FROM " . $db->getTable('tbl_resultados_economico_actualizacion') . " WHERE vereda_id = '" . $tbl_vereda_id . "'";
                $resultArmado3 = $pdo->query($qEconomico3);
              }

            }
        }
        $arrjson = array('output' => array('valid' => true));
        $db->closeConect();
        return $arrjson;
    }

    /**
     * Metodo para obetener la informacion por batallon de la operatividad
     */
    public static function obtenerVeredasPorBatallonOperatividad($rqst){

      $tbl_batallon_id = isset($rqst['tbl_batallon_id']) ? intval($rqst['tbl_batallon_id']) : 0;
    
      if( $tbl_batallon_id > 0){
  
          $db = new DbConection();
          $pdo = $db->openConect();
  
          $q =" SELECT 
          tbl_vereda.nombre_vereda, 
          tbl_vereda.puntaje, 
          tbl_vereda.codigo_vereda, 
          tbl_vereda.id AS tbl_vereda_id,
          tbl_brigadas.sigla AS brigada, 
          tbl_batallones.sigla AS batallon, 
          tbl_batallones.id AS batallon_id, 
          tbl_brigadas.id AS brigada_id,
          tbl_operatividad.*
          FROM (( " . $db->getTable('tbl_operatividad') . "  INNER JOIN  " . $db->getTable('tbl_vereda') . " ON tbl_operatividad.tbl_vereda_id = tbl_vereda.id) 
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
          INNER JOIN " . $db->getTable('tbl_brigadas') . "   ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
          WHERE tbl_vereda.tbl_batallon_id = $tbl_batallon_id AND tbl_operatividad.created_at >= '2022-01-01 00:00:01' AND tbl_operatividad.created_at <= '2022-12-31 23:59:59' ORDER BY id DESC";

          $result = $pdo->query($q);
          $arr = array();
          if ($result) {
              foreach ($result as $valor) {
                  $arr[] = $valor;
              }

              // Suma Total del Batallon 
              $q1="SELECT  tbl_batallones.sigla, tbl_municipio_id,
                Sum(tbl_operatividad.presentaciones) AS suma_presentaciones,
                Sum(tbl_operatividad.mdom) AS suma_mdom,
                Sum(tbl_operatividad.mercurio) AS suma_mercurio,
                Sum(tbl_operatividad.sometimiento) AS suma_sometimiento,
                Sum(tbl_operatividad.capturas_gao) AS suma_capturas_gao,
                Sum(tbl_operatividad.capturas_gdo) AS suma_capturas_gdo,
                Sum(tbl_operatividad.capturas_delco) AS suma_capturas_delco,
                Sum(tbl_operatividad.menores) AS suma_menores,
                Sum(tbl_operatividad.bajas_delco) AS suma_bajas_delco,
                Sum(tbl_operatividad.upm) AS suma_upm,
                Sum(tbl_operatividad.combates) AS suma_combates,
                Sum(tbl_operatividad.total) AS suma_total,
                Sum(tbl_operatividad.dragas) AS suma_dragas,
                Sum(tbl_operatividad.proveedores) AS suma_proveedores,
                Sum(tbl_operatividad.motores) AS suma_motores,
                Sum(tbl_operatividad.explosivos) AS suma_explosivos,
                Sum(tbl_operatividad.armas_cortas) AS suma_armas_cortas,
                Sum(tbl_operatividad.municiones) AS suma_municiones,
                Sum(tbl_operatividad.comunicaciones) AS suma_comunicaciones,
                Sum(tbl_operatividad.intendencia) AS suma_intendencia,
                Sum(tbl_operatividad.lab_ch) AS suma_lab_ch,
                Sum(tbl_operatividad.semilleros) AS suma_semilleros,
                Sum(tbl_operatividad.depositos) AS suma_depositos,
                Sum(tbl_operatividad.campamentos) AS suma_campamentos,
                Sum(tbl_operatividad.lab_pbc) AS suma_lab_pbc,
                Sum(tbl_operatividad.pasta_coca) AS suma_pasta_coca,
                Sum(tbl_operatividad.capturas_soc) AS suma_capturas_soc,
                Sum(tbl_operatividad.madera) AS suma_madera,
                Sum(tbl_operatividad.siembra) AS suma_siembra,
                Sum(tbl_operatividad.armas_largas) AS suma_armas_largas,
                Sum(tbl_operatividad.erradicacion) AS suma_erradicacion,
                Sum(tbl_operatividad.mariguana) AS suma_mariguana,
                Sum(tbl_operatividad.pasta_proceso) AS suma_pasta_proceso,
                Sum(tbl_operatividad.cloridrato) AS suma_cloridrato,
                Sum(tbl_operatividad.liquidos) AS suma_liquidos,
                Sum(tbl_operatividad.otras_sustancias) AS suma_otras_sustancias,
                Sum(tbl_operatividad.otras_maq) AS suma_otras_maq,
                Sum(tbl_operatividad.vehiculos) AS suma_vehiculos,
                Sum(tbl_operatividad.hoja) AS suma_hoja,
                Sum(tbl_operatividad.dinero) AS suma_dinero,
                Sum(tbl_operatividad.fauna) AS suma_fauna,
                Sum(tbl_operatividad.dominio) AS suma_dominio,
                Sum(tbl_operatividad.retro) AS suma_retro,
                Sum(tbl_operatividad.solidos) AS suma_solidos,
                Sum(tbl_operatividad.opsic) AS suma_opsic
                FROM (" . $db->getTable('tbl_operatividad') . "  INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_operatividad.tbl_vereda_id = tbl_vereda.id) 
                INNER JOIN " . $db->getTable('tbl_batallones') . "   ON tbl_vereda.tbl_batallon_id = tbl_batallones.id
                where tbl_batallones.id =$tbl_batallon_id  AND tbl_operatividad.created_at >= '2022-01-01 00:00:01' AND tbl_operatividad.created_at <= '2022-12-31 23:59:59'  GROUP BY tbl_batallones.sigla";
                $result1 = $pdo->query($q1);
                $arr1 = array();
                if ($result) {
                    foreach ($result1 as $valor1) {
                        $arr1[] = $valor1;
                    }
                  }
              $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'total' => $arr1));
            } else {
              $arrjson = Util::error_no_result();
          }
          $db->closeConect();
          return $arrjson;
      }else{
        return  Util::error_missing_data();
      }
  }
}
