<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Grafico
{

  public function __construct()
  {
  }

  public static function getData($rqst)
  {

    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : 0;
    $tbl_batallon_id = isset($rqst['tbl_batallon_id']) ? intval($rqst['tbl_batallon_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    // Filtro por brigada
    if ($tbl_brigada_id  > 0) {
      $q = "SELECT tbl_brigadas.id , tbl_brigadas.sigla AS brigada, 
          Sum(tbl_operatividad.presentaciones) AS presentaciones,
          Sum(tbl_operatividad.mdom) AS mdom,
          Sum(tbl_operatividad.sometimiento) AS sometimiento,
          Sum(tbl_operatividad.capturas_gao) AS capturas_gao,
          Sum(tbl_operatividad.capturas_gdo) AS capturas_gdo,
          Sum(tbl_operatividad.capturas_delco) AS capturas_delco,
          Sum(tbl_operatividad.menores) AS menores,
          Sum(tbl_operatividad.bajas_delco) AS bajas_delco,
          Sum(tbl_operatividad.upm) AS upm,
          Sum(tbl_operatividad.dragas) AS dragas,
          Sum(tbl_operatividad.motores) AS motores,
          Sum(tbl_operatividad.explosivos) AS explosivos,
          Sum(tbl_operatividad.minas) AS minas,
          Sum(tbl_operatividad.armas_cortas) AS armas_cortas,
          Sum(tbl_operatividad.municiones) AS municiones,
          Sum(tbl_operatividad.comunicaciones) AS comunicaciones,
          Sum(tbl_operatividad.intendencia) AS intendencia,
          Sum(tbl_operatividad.lab_ch) AS lab_ch,
          Sum(tbl_operatividad.semilleros) AS semilleros,
          Sum(tbl_operatividad.semilleros_matas) AS semilleros_matas,
          Sum(tbl_operatividad.depositos) AS depositos,
          Sum(tbl_operatividad.campamentos) AS campamentos,
          Sum(tbl_operatividad.solidos) AS solidos,
          Sum(tbl_operatividad.lab_pbc) AS lab_pbc,
          Sum(tbl_operatividad.pasta_coca) AS pasta_coca,
          Sum(tbl_operatividad.capturas_soc) AS capturas_soc,
          Sum(tbl_operatividad.madera) AS madera,
          Sum(tbl_operatividad.combates) AS combates,
          Sum(tbl_operatividad.siembra) AS siembra,
          Sum(tbl_operatividad.armas_largas) AS armas_largas,
          Sum(tbl_operatividad.proveedores) AS proveedores,
          Sum(tbl_operatividad.erradicacion) AS erradicacion,
          Sum(tbl_operatividad.mercurio) AS mercurio,
          Sum(tbl_operatividad.mariguana) AS mariguana,
          Sum(tbl_operatividad.pasta_proceso) AS pasta_proceso,
          Sum(tbl_operatividad.cloridrato) AS cloridrato,
          Sum(tbl_operatividad.liquidos) AS liquidos,
          Sum(tbl_operatividad.otras_sustancias) AS otras_sustancias,
          SUM(tbl_operatividad.hoja) AS hoja, 
          SUM(tbl_operatividad.dinero) AS dinero,     
          Sum(tbl_operatividad.otras_maq) AS otras_maq,
          Sum(tbl_operatividad.vehiculos) AS vehiculos,
          Sum(tbl_operatividad.retro) AS retroescavadoras, 
          Sum(tbl_operatividad.opsic) AS opsic, 
          Sum(tbl_operatividad.dominio) AS dominio, 
          Sum(tbl_operatividad.fauna) AS fauna, 
          tbl_batallones.id AS batallon_id, 
          tbl_brigadas.id AS brigada_id
          FROM ((" . $db->getTable('tbl_operatividad') . " 
          INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_operatividad.tbl_vereda_id = tbl_vereda.id) 
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
          INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
          WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id  AND tbl_operatividad.created_at >= '2021-01-01 00:00:01' AND tbl_operatividad.created_at <= '2021-12-31 23:59:59'
           GROUP BY  tbl_brigadas.sigla,  tbl_brigadas.id";

      $q2 = "SELECT tbl_brigadas.id , tbl_brigadas.sigla AS brigada, 
           Sum(tbl_operatividad.presentaciones) AS presentaciones1,
           Sum(tbl_operatividad.mdom) AS mdom1,
           Sum(tbl_operatividad.sometimiento) AS sometimiento1,
           Sum(tbl_operatividad.capturas_gao) AS capturas_gao1,
           Sum(tbl_operatividad.capturas_gdo) AS capturas_gdo1,
           Sum(tbl_operatividad.capturas_delco) AS capturas_delco1,
           Sum(tbl_operatividad.menores) AS menores1,
           Sum(tbl_operatividad.bajas_delco) AS bajas_delco1,
           Sum(tbl_operatividad.upm) AS upm1,
           Sum(tbl_operatividad.dragas) AS dragas1,
           Sum(tbl_operatividad.motores) AS motores1,
           Sum(tbl_operatividad.explosivos) AS explosivos1,
           Sum(tbl_operatividad.minas) AS minas1,
           Sum(tbl_operatividad.armas_cortas) AS armas_cortas1,
           Sum(tbl_operatividad.municiones) AS municiones1,
           Sum(tbl_operatividad.comunicaciones) AS comunicaciones1,
           Sum(tbl_operatividad.intendencia) AS intendencia1,
           Sum(tbl_operatividad.lab_ch) AS lab_ch1,
           Sum(tbl_operatividad.semilleros) AS semilleros1,
           Sum(tbl_operatividad.semilleros_matas) AS semilleros_matas1,
           Sum(tbl_operatividad.depositos) AS depositos1,
           Sum(tbl_operatividad.campamentos) AS campamentos1,
           Sum(tbl_operatividad.solidos) AS solidos1,
           Sum(tbl_operatividad.lab_pbc) AS lab_pbc1,
           Sum(tbl_operatividad.pasta_coca) AS pasta_coca1,
           Sum(tbl_operatividad.capturas_soc) AS capturas_soc1,
           Sum(tbl_operatividad.madera) AS madera1,
           Sum(tbl_operatividad.combates) AS combates1,
           Sum(tbl_operatividad.siembra) AS siembra1,
           Sum(tbl_operatividad.armas_largas) AS armas_largas1,
           Sum(tbl_operatividad.proveedores) AS proveedores1,
           Sum(tbl_operatividad.erradicacion) AS erradicacion1,
           Sum(tbl_operatividad.mercurio) AS mercurio1,
           Sum(tbl_operatividad.mariguana) AS mariguana1,
           Sum(tbl_operatividad.pasta_proceso) AS pasta_proceso1,
           Sum(tbl_operatividad.cloridrato) AS cloridrato1,
           Sum(tbl_operatividad.liquidos) AS liquidos1,
           Sum(tbl_operatividad.otras_sustancias) AS otras_sustancias1,
           SUM(tbl_operatividad.hoja) AS hoja1, 
           SUM(tbl_operatividad.dinero) AS dinero1,     
           Sum(tbl_operatividad.otras_maq) AS otras_maq1,
           Sum(tbl_operatividad.vehiculos) AS vehiculos1,
           Sum(tbl_operatividad.retro) AS retroescavadoras1, 
           Sum(tbl_operatividad.opsic) AS opsic1, 
           Sum(tbl_operatividad.dominio) AS dominio1, 
           Sum(tbl_operatividad.fauna) AS fauna1, 
           tbl_batallones.id AS batallon_id, 
           tbl_brigadas.id AS brigada_id
           FROM ((" . $db->getTable('tbl_operatividad') . " 
           INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_operatividad.tbl_vereda_id = tbl_vereda.id) 
           INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
           INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
           WHERE tbl_vereda.tbl_brigada_id = $tbl_brigada_id  AND tbl_operatividad.created_at >= '2022-01-01 00:00:01' AND tbl_operatividad.created_at <= '2022-12-31 23:59:59'
          GROUP BY  tbl_brigadas.sigla,  tbl_brigadas.id";
    }


    
    // Filtro por batallon
    if ($tbl_batallon_id > 0) {
      $q = "SELECT 
          tbl_brigadas.sigla AS brigada, 
          tbl_batallones.sigla AS batallon, 
          tbl_batallones.id AS batallon_id, 
          tbl_brigadas.id AS brigada_id,
          SUM(tbl_operatividad.presentaciones) AS presentaciones, 
          SUM(tbl_operatividad.mdom) AS mdom, 
          SUM(tbl_operatividad.sometimiento) AS sometimiento, 
          SUM(tbl_operatividad.capturas_gao) AS capturas_gao, 
          SUM(tbl_operatividad.capturas_gdo) AS capturas_gdo, 
          SUM(tbl_operatividad.capturas_delco) AS capturas_delco, 
          SUM(tbl_operatividad.menores) AS menores, 
          SUM(tbl_operatividad.bajas_delco) AS bajas_delco, 
          SUM(tbl_operatividad.upm) AS upm, 
          SUM(tbl_operatividad.dragas) AS dragas, 
          Sum(tbl_operatividad.solidos) AS solidos,
          SUM(tbl_operatividad.motores) AS motores, 
          SUM(tbl_operatividad.explosivos) AS explosivos, 
          SUM(tbl_operatividad.minas) AS minas,
          SUM(tbl_operatividad.armas_cortas) AS armas_cortas, 
          SUM(tbl_operatividad.municiones) AS municiones, 
          SUM(tbl_operatividad.comunicaciones) AS comunicaciones, 
          SUM(tbl_operatividad.intendencia) AS intendencia, 
          SUM(tbl_operatividad.lab_ch) AS lab_ch, 
          SUM(tbl_operatividad.semilleros) AS semilleros, 
          SUM(tbl_operatividad.semilleros_matas) AS semilleros_matas, 
          SUM(tbl_operatividad.depositos) AS depositos, 
          SUM(tbl_operatividad.campamentos) AS campamentos, 
          SUM(tbl_operatividad.lab_pbc) AS lab_pbc, 
          SUM(tbl_operatividad.pasta_coca) AS pasta_coca, 
          SUM(tbl_operatividad.capturas_soc) AS capturas_soc,
          SUM(tbl_operatividad.madera) AS madera,
          Sum(tbl_operatividad.combates) AS combates, 
          SUM(tbl_operatividad.siembra) AS siembra, 
          SUM(tbl_operatividad.armas_largas) AS armas_largas, 
          Sum(tbl_operatividad.proveedores) AS proveedores,
          SUM(tbl_operatividad.erradicacion) AS erradicacion, 
          SUM(tbl_operatividad.mercurio) AS mercurio,
          SUM(tbl_operatividad.mariguana) AS mariguana, 
          SUM(tbl_operatividad.pasta_proceso) AS pasta_proceso, 
          SUM(tbl_operatividad.cloridrato) AS cloridrato, 
          SUM(tbl_operatividad.liquidos) AS liquidos, 
          SUM(tbl_operatividad.otras_sustancias) AS otras_sustancias, 
          SUM(tbl_operatividad.hoja) AS hoja, 
          SUM(tbl_operatividad.dinero) AS dinero, 
          SUM(tbl_operatividad.otras_maq) AS otras_maq, 
          SUM(tbl_operatividad.vehiculos) AS vehiculos, 
          SUM(tbl_operatividad.retro) AS retroescavadoras,
          Sum(tbl_operatividad.dominio) AS dominio, 
          Sum(tbl_operatividad.fauna) AS fauna, 
          SUM(tbl_operatividad.opsic) AS opsic
          FROM ((" . $db->getTable('tbl_operatividad') . " INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_operatividad.tbl_vereda_id = tbl_vereda.id) 
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
          INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
          WHERE tbl_vereda.tbl_batallon_id = $tbl_batallon_id
          AND tbl_operatividad.created_at >= '2021-01-01 00:00:01' AND tbl_operatividad.created_at <= '2021-12-31 23:59:59' 
          GROUP BY  tbl_batallones.sigla, tbl_batallones.id";

        

      $q2 = "SELECT 
          tbl_brigadas.sigla AS brigada, 
          tbl_batallones.sigla AS batallon, 
          tbl_batallones.id AS batallon_id, 
          tbl_brigadas.id AS brigada_id,
          SUM(tbl_operatividad.presentaciones) AS presentaciones1, 
          SUM(tbl_operatividad.mdom) AS mdom1, 
          SUM(tbl_operatividad.sometimiento) AS sometimiento1, 
          SUM(tbl_operatividad.capturas_gao) AS capturas_gao1, 
          SUM(tbl_operatividad.capturas_gdo) AS capturas_gdo1, 
          SUM(tbl_operatividad.capturas_delco) AS capturas_delco1, 
          SUM(tbl_operatividad.menores) AS menores1, 
          SUM(tbl_operatividad.bajas_delco) AS bajas_delco1, 
          SUM(tbl_operatividad.upm) AS upm1, 
          SUM(tbl_operatividad.dragas) AS dragas1, 
          Sum(tbl_operatividad.solidos) AS solidos1,
          SUM(tbl_operatividad.motores) AS motores1, 
          SUM(tbl_operatividad.explosivos) AS explosivos1, 
          SUM(tbl_operatividad.minas) AS minas1,
          SUM(tbl_operatividad.armas_cortas) AS armas_cortas1, 
          SUM(tbl_operatividad.municiones) AS municiones1, 
          SUM(tbl_operatividad.comunicaciones) AS comunicaciones1, 
          SUM(tbl_operatividad.intendencia) AS intendencia1, 
          SUM(tbl_operatividad.lab_ch) AS lab_ch1, 
          SUM(tbl_operatividad.semilleros) AS semilleros1, 
          SUM(tbl_operatividad.semilleros_matas) AS semilleros_matas1, 
          SUM(tbl_operatividad.depositos) AS depositos1, 
          SUM(tbl_operatividad.campamentos) AS campamentos1, 
          SUM(tbl_operatividad.lab_pbc) AS lab_pbc1, 
          SUM(tbl_operatividad.pasta_coca) AS pasta_coca1, 
          SUM(tbl_operatividad.capturas_soc) AS capturas_soc1,
          SUM(tbl_operatividad.madera) AS madera1,
          SUM(tbl_operatividad.combates) AS combates1, 
          SUM(tbl_operatividad.siembra) AS siembra1, 
          SUM(tbl_operatividad.armas_largas) AS armas_largas1, 
          Sum(tbl_operatividad.proveedores) AS proveedores1,
          SUM(tbl_operatividad.erradicacion) AS erradicacion1, 
          SUM(tbl_operatividad.mercurio) AS mercurio1,
          SUM(tbl_operatividad.mariguana) AS mariguana1, 
          SUM(tbl_operatividad.pasta_proceso) AS pasta_proceso1, 
          SUM(tbl_operatividad.cloridrato) AS cloridrato1, 
          SUM(tbl_operatividad.liquidos) AS liquidos1, 
          SUM(tbl_operatividad.otras_sustancias) AS otras_sustancias1, 
          SUM(tbl_operatividad.hoja) AS hoja1, 
          SUM(tbl_operatividad.dinero) AS dinero1, 
          SUM(tbl_operatividad.otras_maq) AS otras_maq1, 
          SUM(tbl_operatividad.vehiculos) AS vehiculos1, 
          SUM(tbl_operatividad.retro) AS retroescavadoras1,
          Sum(tbl_operatividad.dominio) AS dominio1, 
          Sum(tbl_operatividad.fauna) AS fauna1, 
          SUM(tbl_operatividad.opsic) AS opsic1
          FROM ((" . $db->getTable('tbl_operatividad') . " INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_operatividad.tbl_vereda_id = tbl_vereda.id) 
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
          INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
          WHERE tbl_vereda.tbl_batallon_id = $tbl_batallon_id
          AND tbl_operatividad.created_at >= '2022-01-01 00:00:01' AND tbl_operatividad.created_at <= '2022-12-31 23:59:59' 
          GROUP BY  tbl_batallones.sigla, tbl_batallones.id";


 

    }

    $result2 = $pdo->query($q2);
    $arr2 = array();

    $result = $pdo->query($q);
    $arr = array();
    if ($result) {

      foreach ($result as $valor) {
        $arr[] = $valor;
      }

      if ($result2) {
        foreach ($result2 as $valor2) {
          $arr2[] = $valor2;
        }
      }
      if (count($arr) > 0) {
        $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'response2' => $arr2));
      } else {
        $arrjson = Util::error_no_result();
      }
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }
}
