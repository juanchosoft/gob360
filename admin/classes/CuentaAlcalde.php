<?php

/**
 * Clase CuentaAlcalde
 * Adaptación de la clase Cuenta para trabajar con visitas del Alcalde
 * Usa tbl_visitas_alcalde y cuenta visitas por municipio
 * @author Adaptado para sistema Alcalde
 */
class CuentaAlcalde
{
    public function __construct()
    {
    }

    /**
     * Obtiene el conteo de visitas por municipio del Alcalde
     * @param array $rqst Parámetros de consulta
     * @return array JSON con conteo de visitas por municipio
     */
    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        // Consulta que cuenta visitas por municipio
        $q = "SELECT
                COUNT(va.id) AS CuentaDeid,
                c.municipio
              FROM " . $db->getTable('tbl_visitas_alcalde') . " va
              INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                ON va.tbl_municipio_id = c.codigo_muncipio
              WHERE va.tipo_registro = 'Visita'
              GROUP BY c.municipio
              ORDER BY CuentaDeid ASC";

        if ($id > 0) {
            $q = "SELECT * FROM " . $db->getTable('tbl_visitas_alcalde') . " WHERE id = " . $id;
        }

        if ($tipo != "") {
            $q = "SELECT * FROM " . $db->getTable('tbl_visitas_alcalde') . "
                  WHERE tipo = '$tipo' AND habilitado = 'si'";
        }

        $result = $pdo->query($q);
        $arr = array();

        if ($result) {
            foreach ($result as $valor) {
                $arr[] = $valor;
            }
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }
}
