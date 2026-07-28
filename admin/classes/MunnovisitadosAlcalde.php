<?php

/**
 * Clase MunnovisitadosAlcalde
 * Gestiona veredas visitadas y no visitadas del municipio del Alcalde
 * Usa tbl_visitas_alcalde y tbl_vereda
 * @author Adaptado para sistema Alcalde
 */
class MunnovisitadosAlcalde
{
    public function __construct()
    {
    }

    /**
     * Obtiene veredas del municipio del Alcalde que NO han sido visitadas
     * @param array $rqst Parámetros de consulta
     * @return array JSON con veredas no visitadas
     */
    public static function getAll($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once 'SessionData.php';
        $municipioAlcalde = SessionData::getCodigoMunicipio();

        // Log para depuración
        error_log("MunnovisitadosAlcalde::getAll - Municipio del alcalde: " . var_export($municipioAlcalde, true));

        // Si no hay municipio del alcalde, retornar array vacío
        if (empty($municipioAlcalde)) {
            return array('output' => array('valid' => true, 'response' => []));
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        // Consulta que encuentra veredas sin visitas del Alcalde
        $q = "SELECT
                v.id,
                v.nombre_vereda,
                v.municipio_id
              FROM " . $db->getTable('tbl_vereda') . " v
              LEFT JOIN " . $db->getTable('tbl_visitas_alcalde') . " va
                ON v.id = va.tbl_vereda_id
                AND va.tipo_registro = 'Visita'
              WHERE v.municipio_id = :municipio_id
                AND va.tbl_vereda_id IS NULL
              ORDER BY v.nombre_vereda ASC";

        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':municipio_id', $municipioAlcalde);
        $stmt->execute();

        $arr = array();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $arr = $result;
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = array('output' => array('valid' => true, 'response' => []));
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene veredas visitadas del municipio del Alcalde con detalle de visitas
     * @param array $rqst Parámetros de consulta
     * @return array JSON con veredas visitadas y número de visitas
     */
    public static function getVeredasVisitadas($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once 'SessionData.php';
        $municipioAlcalde = SessionData::getCodigoMunicipio();

        // Log para depuración
        error_log("MunnovisitadosAlcalde::getVeredasVisitadas - Municipio del alcalde: " . var_export($municipioAlcalde, true));

        // Si no hay municipio del alcalde, retornar array vacío
        if (empty($municipioAlcalde)) {
            return array('output' => array('valid' => true, 'response' => []));
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        // Consulta que encuentra veredas visitadas con número de visitas
        $q = "SELECT
                v.id,
                v.nombre_vereda,
                v.municipio_id,
                COUNT(va.id) as veces_visitado
              FROM " . $db->getTable('tbl_vereda') . " v
              INNER JOIN " . $db->getTable('tbl_visitas_alcalde') . " va
                ON v.id = va.tbl_vereda_id
                AND va.tipo_registro = 'Visita'
              WHERE v.municipio_id = :municipio_id
              GROUP BY v.id, v.nombre_vereda, v.municipio_id
              ORDER BY veces_visitado DESC, v.nombre_vereda ASC";

        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':municipio_id', $municipioAlcalde);
        $stmt->execute();

        $arr = array();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $arr = $result;
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = array('output' => array('valid' => true, 'response' => []));
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene municipios visitados del departamento para SuperAdmin
     * @param array $rqst Parámetros de consulta
     * @return array JSON con municipios visitados y número de visitas
     */
    public static function getMunicipiosVisitados($rqst)
    {
        require_once 'Util.php';
        $departamentoCodigo = Util::getDepartamentoPrincipal();

        // Log para depuración
        error_log("MunnovisitadosAlcalde::getMunicipiosVisitados - Departamento: " . var_export($departamentoCodigo, true));

        $db = new DbConection();
        $pdo = $db->openConect();

        // Consulta que encuentra municipios visitados con número de visitas
        $q = "SELECT
                c.codigo_muncipio,
                c.municipio,
                COUNT(va.id) as veces_visitado
              FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " c
              INNER JOIN " . $db->getTable('tbl_visitas_alcalde') . " va
                ON c.codigo_muncipio = va.tbl_municipio_id
                AND va.tipo_registro = 'Visita'
              WHERE c.codigo_departamento = :codigo_departamento
              GROUP BY c.codigo_muncipio, c.municipio
              ORDER BY veces_visitado DESC, c.municipio ASC";

        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':codigo_departamento', $departamentoCodigo);
        $stmt->execute();

        $arr = array();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $arr = $result;
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = array('output' => array('valid' => true, 'response' => []));
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene municipios NO visitados del departamento para SuperAdmin
     * @param array $rqst Parámetros de consulta
     * @return array JSON con municipios no visitados
     */
    public static function getMunicipiosNoVisitados($rqst)
    {
        require_once 'Util.php';
        $departamentoCodigo = Util::getDepartamentoPrincipal();

        // Log para depuración
        error_log("MunnovisitadosAlcalde::getMunicipiosNoVisitados - Departamento: " . var_export($departamentoCodigo, true));

        $db = new DbConection();
        $pdo = $db->openConect();

        // Consulta que encuentra municipios sin visitas
        $q = "SELECT
                c.codigo_muncipio,
                c.municipio
              FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " c
              LEFT JOIN " . $db->getTable('tbl_visitas_alcalde') . " va
                ON c.codigo_muncipio = va.tbl_municipio_id
                AND va.tipo_registro = 'Visita'
              WHERE c.codigo_departamento = :codigo_departamento
                AND va.tbl_municipio_id IS NULL
              ORDER BY c.municipio ASC";

        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':codigo_departamento', $departamentoCodigo);
        $stmt->execute();

        $arr = array();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $arr = $result;
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = array('output' => array('valid' => true, 'response' => []));
        }

        $db->closeConect();
        return $arrjson;
    }
}
