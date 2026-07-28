<?php
/**
 * Clase para manejar las provincias
 * @author SPIDERSOFTWARE
 */
class Provincias {

    public function __construct(){}

    /**
     * Obtiene todas las provincias de un departamento dado su código
     */
    public static function getProvinciasByDepartamento($codigo_departamento)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT DISTINCT subregion as provincia 
              FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " 
              WHERE codigo_departamento = :codigo_departamento 
              AND subregion IS NOT NULL 
              AND subregion != '' 
              ORDER BY subregion";

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':codigo_departamento', $codigo_departamento, PDO::PARAM_STR); 
        $stmt->execute();

        $arr = array();
        while ($valor = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arr[] = $valor;
        }

        if (!empty($arr)) {
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene los municipios por provincia (para departamento 68)
     * Si provincia es '__ALL__' retorna todos los municipios del dpto 68
     */
    public static function getMunicipiosByProvincia($rqst)
    {
        $provincia = isset($rqst['provincia']) ? $rqst['provincia'] : '';
        
        if (empty($provincia)) {
            return array('output' => array('valid' => false, 'response' => 'Provincia no especificada'));
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        $where = "codigo_departamento = '68'";
        if ($provincia !== '__ALL__') {
            $where .= " AND subregion = :provincia";
        }

        $q = "SELECT codigo_muncipio, municipio" . ($provincia === '__ALL__' ? ", subregion" : "") . " 
              FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " 
              WHERE $where
              ORDER BY municipio ASC";

        $stmt = $pdo->prepare($q);
        if ($provincia !== '__ALL__') {
            $stmt->bindParam(':provincia', $provincia, PDO::PARAM_STR);
        }
        $stmt->execute();

        $arr = array();
        while ($valor = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arr[] = $valor;
        }

        if (!empty($arr)) {
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }
}
?>