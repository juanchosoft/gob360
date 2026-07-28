<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class GestoraSocial
{

    public function __construct() {}

    public static function getPoblacionImpactadaPorMunicipio($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT SUM(tbl_gestora.poblacion) AS total, tbl_ciudades.municipio, tbl_gestora.provincia
                FROM " . $db->getTable('tbl_gestora') . " 
                INNER JOIN " . $db->getTable('tbl_ciudades') . " 
                ON tbl_gestora.tbl_municipio_id = tbl_ciudades.codigo_muncipio
                WHERE tbl_gestora.poblacion != ''
                  AND (tbl_gestora.tipo_actividad = 'primera_dama' OR tbl_gestora.tipo_actividad IS NULL OR tbl_gestora.tipo_actividad = '')
                GROUP BY tbl_ciudades.municipio";

        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = (!empty($arr))
                ? array('output' => array('valid' => true, 'response' => $arr))
                : Util::error_no_result();
        } catch (PDOException $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    public static function getTotalDetalladoTipoActividad($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT Count(tbl_acciong.accion) AS total, tbl_acciong.accion as item
        FROM " . $db->getTable('tbl_gestora') . " INNER JOIN " . $db->getTable('tbl_acciong') . "   ON tbl_gestora.tbl_acciong_id = tbl_acciong.id
        WHERE (tbl_gestora.tipo_actividad = 'primera_dama' OR tbl_gestora.tipo_actividad IS NULL OR tbl_gestora.tipo_actividad = '')
        GROUP BY tbl_acciong.accion";

        try {
            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = (!empty($arr))
                ? array('output' => array('valid' => true, 'response' => $arr))
                : Util::error_no_result();
        } catch (PDOException $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }
}
