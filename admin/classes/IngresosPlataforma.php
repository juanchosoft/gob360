<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class IngresosPlataforma {

    public function __construct(){}

    public static function getAllEconomico($rqst)
    {

        $db = new DbConection();
        $pdo = $db->openConect();

        $fecha = Util::getCurrentYear();

        // Información de Factor Economico
        $q="SELECT tbl_brigadas.sigla AS brigada, 
        tbl_batallones.sigla AS batallon, 
        tbl_economico.tipo, tbl_resultados_x_tbl_economico.cantidad, 
        tbl_departamentos.departamento, tbl_ciudades.municipio, 
        tbl_vereda.nombre_vereda as vereda, 
        tbl_resultados_x_tbl_economico.created_at
        FROM (((((( " . $db->getTable('tbl_resultados_economico') . "   
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_economico.batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_resultados_economico.brigada_id = tbl_brigadas.id) 
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_resultados_economico.id = tbl_resultados_x_tbl_economico.tbl_resultados_economico_id) 
        INNER JOIN " . $db->getTable('tbl_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_economico_id = tbl_economico.id) 
        INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_resultados_economico.vereda_id = tbl_vereda.id) 
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_resultados_economico.municipio_id = tbl_ciudades.id) 
        INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_resultados_economico.departamento_id = tbl_departamentos.id
        WHERE tbl_resultados_x_tbl_economico.created_at >= '$fecha-01-01 00:00:01' AND tbl_resultados_x_tbl_economico.created_at <=  '$fecha-12-31 23:59:59'
        ORDER BY tbl_resultados_x_tbl_economico.created_at DESC";
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valorEcon) {
                $arr[] = $valorEcon;
            }
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        $db->closeConect();
        return $arrjson;
    }

    public static function getAllArmado($rqst)
    {

        $db = new DbConection();
        $pdo = $db->openConect();

        $fecha = Util::getCurrentYear();

        $q="SELECT tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_armado.tipo, tbl_resultados_x_tbl_armado.cantidad, 
        tbl_departamentos.departamento, tbl_ciudades.municipio, 
        tbl_vereda.nombre_vereda as vereda, 
        tbl_resultados_x_tbl_armado.created_at
        FROM ((((((" . $db->getTable('tbl_resultados_armado') . "  
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_armado.batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_resultados_armado.brigada_id = tbl_brigadas.id) 
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . "  ON tbl_resultados_armado.id = tbl_resultados_x_tbl_armado.tbl_resultados_armado_id) 
        INNER JOIN " . $db->getTable('tbl_armado') . "  ON tbl_resultados_x_tbl_armado.tbl_armado_id = tbl_armado.id) 
        INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_resultados_armado.vereda_id = tbl_vereda.id) 
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_resultados_armado.municipio_id = tbl_ciudades.id) 
        INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_resultados_armado.departamento_id = tbl_departamentos.id
        WHERE tbl_resultados_x_tbl_armado.created_at >= '$fecha-01-01 00:00:01' AND tbl_resultados_x_tbl_armado.created_at <=  '$fecha-12-31 23:59:59'
        ORDER BY tbl_resultados_x_tbl_armado.created_at DESC";
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        $db->closeConect();
        return $arrjson;
    }

    public static function getAllSocial($rqst)
    {

        $db = new DbConection();
        $pdo = $db->openConect();

        $fecha = Util::getCurrentYear();

        $q="SELECT tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_sociales.tipo, tbl_resultados_x_tbl_social.cantidad, tbl_departamentos.departamento, 
        tbl_ciudades.municipio, 
        tbl_vereda.nombre_vereda as vereda, 
        tbl_resultados_x_tbl_social.created_at
        FROM ((((((" . $db->getTable('tbl_resultados_social') . "  
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_social.batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_resultados_social.brigada_id = tbl_brigadas.id) 
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_social') . "  ON tbl_resultados_social.id = tbl_resultados_x_tbl_social.tbl_resultados_social_id)
        INNER JOIN " . $db->getTable('tbl_sociales') . "   ON tbl_resultados_x_tbl_social.tbl_social_id = tbl_sociales.id) 
        INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_resultados_social.vereda_id = tbl_vereda.id) 
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_resultados_social.municipio_id = tbl_ciudades.id) 
        INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_resultados_social.departamento_id = tbl_departamentos.id
        WHERE tbl_resultados_x_tbl_social.created_at >= '$fecha-01-01 00:00:01' AND tbl_resultados_x_tbl_social.created_at <=  '$fecha-12-31 23:59:59'
        ORDER BY tbl_resultados_x_tbl_social.created_at DESC";
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        $db->closeConect();
        return $arrjson;
    }


    public static function getAllSocialActualizacion($rqst)
    {

        $db = new DbConection();
        $pdo = $db->openConect();

        $fecha = Util::getCurrentYear();

        $q="SELECT tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_sociales.tipo, tbl_resultados_x_tbl_social_actualizacion.cantidad, 
        tbl_ciudades.municipio, tbl_vereda.nombre_vereda as vereda, 
        tbl_resultados_x_tbl_social_actualizacion.created_at, tbl_resultados_x_tbl_social_actualizacion.tbl_resultados_social_id, 
        tbl_resultados_x_tbl_social_actualizacion.id
        FROM (( " . $db->getTable('tbl_resultados_social_actualizacion') . "    
        INNER JOIN (( " . $db->getTable('tbl_vereda') . "  
        INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) ON tbl_resultados_social_actualizacion.vereda_id = tbl_vereda.id) 
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_resultados_social_actualizacion.municipio_id = tbl_ciudades.id)
        INNER JOIN ( " . $db->getTable('tbl_resultados_x_tbl_social_actualizacion') . "  
        INNER JOIN " . $db->getTable('tbl_sociales') . "    ON tbl_resultados_x_tbl_social_actualizacion.tbl_social_id = tbl_sociales.id) ON tbl_resultados_social_actualizacion.id = tbl_resultados_x_tbl_social_actualizacion.tbl_resultados_social_id        
        WHERE tbl_resultados_x_tbl_social_actualizacion.created_at >= '$fecha-01-01 00:00:01' AND tbl_resultados_x_tbl_social_actualizacion.created_at <=  '$fecha-12-31 23:59:59'
        ORDER BY tbl_resultados_x_tbl_social_actualizacion.created_at DESC";
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        $db->closeConect();
        return $arrjson;
    }

    
    public static function getAllArmadoActualizacion($rqst)
    {

        $db = new DbConection();
        $pdo = $db->openConect();

        $fecha = Util::getCurrentYear();

        $q="SELECT tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_armado.nombre, tbl_resultados_x_tbl_armado_actualizacion.cantidad_bajas, 
        tbl_resultados_x_tbl_armado_actualizacion.cantidad_capturas, tbl_ciudades.municipio,
        tbl_vereda.nombre_vereda as vereda, 
        tbl_resultados_x_tbl_armado_actualizacion.created_at, 
        tbl_resultados_x_tbl_armado_actualizacion.tbl_resultados_armado_id, 
        tbl_resultados_x_tbl_armado_actualizacion.id
        FROM ((  " . $db->getTable('tbl_resultados_armado_actualizacion') . " 
        INNER JOIN ((" . $db->getTable('tbl_vereda') . "  INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id) 
        INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) ON tbl_resultados_armado_actualizacion.vereda_id = tbl_vereda.id) 
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_resultados_armado_actualizacion.municipio_id = tbl_ciudades.id) 
        INNER JOIN (" . $db->getTable('tbl_resultados_x_tbl_armado_actualizacion') . "  
        INNER JOIN " . $db->getTable('tbl_armado') . "  ON tbl_resultados_x_tbl_armado_actualizacion.tbl_armado_id = tbl_armado.id) ON tbl_resultados_armado_actualizacion.id = tbl_resultados_x_tbl_armado_actualizacion.tbl_resultados_armado_id
        WHERE tbl_resultados_x_tbl_armado_actualizacion.created_at >= '$fecha-01-01 00:00:01' AND tbl_resultados_x_tbl_armado_actualizacion.created_at <=  '$fecha-12-31 23:59:59'
        ORDER BY tbl_resultados_x_tbl_armado_actualizacion.created_at DESC";
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        $db->closeConect();
        return $arrjson;
    }


    public static function getAllEconomicoActualizacion($rqst)
    {

        $db = new DbConection();
        $pdo = $db->openConect();

        $fecha = Util::getCurrentYear();

        $q="SELECT tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_economico.tipo, tbl_resultados_x_tbl_economico_actualizacion.cantidad, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_resultados_x_tbl_economico_actualizacion.created_at, tbl_resultados_x_tbl_economico_actualizacion.tbl_resultados_economico_id, tbl_resultados_x_tbl_economico_actualizacion.id
        FROM (( " . $db->getTable('tbl_resultados_economico_actualizacion') . "   
        INNER JOIN ((" . $db->getTable('tbl_vereda') . "  INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id) 
        INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) ON tbl_resultados_economico_actualizacion.vereda_id = tbl_vereda.id) 
        INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_economico_actualizacion.municipio_id = tbl_ciudades.id) 
        INNER JOIN ( " . $db->getTable('tbl_resultados_x_tbl_economico_actualizacion') . "  
        INNER JOIN " . $db->getTable('tbl_economico') . "  ON tbl_resultados_x_tbl_economico_actualizacion.tbl_economico_id = tbl_economico.id) ON tbl_resultados_economico_actualizacion.id = tbl_resultados_x_tbl_economico_actualizacion.tbl_resultados_economico_id
        WHERE tbl_resultados_x_tbl_economico_actualizacion.created_at >= '$fecha-01-01 00:00:01' AND tbl_resultados_x_tbl_economico_actualizacion.created_at <=  '$fecha-12-31 23:59:59'
        ORDER BY tbl_resultados_x_tbl_economico_actualizacion.created_at DESC";
        $result = $pdo->query($q);
        $arr = array();
        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        $db->closeConect();
        return $arrjson;
    }


}
