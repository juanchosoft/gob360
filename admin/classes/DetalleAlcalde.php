<?php

/**
 * Clase DetalleAlcalde
 * Adaptación de la clase Detalle para trabajar con visitas del Alcalde
 * Usa tbl_visitas_alcalde y muestra veredas en lugar de provincias
 * @author Adaptado para sistema Alcalde
 */
class DetalleAlcalde
{
    public function __construct()
    {
    }

    /**
     * Obtiene municipios visitados por el Alcalde con sus veredas
     * @param array $rqst Parámetros de consulta
     * @return array JSON con municipios y veredas visitadas
     */
    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        // Consulta que agrupa por municipio y vereda
        $q = "SELECT
                COALESCE(v.nombre_vereda, 'Sin vereda') AS vereda,
                c.municipio
              FROM " . $db->getTable('tbl_visitas_alcalde') . " va
              INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                ON va.tbl_municipio_id = c.codigo_muncipio
              LEFT JOIN " . $db->getTable('tbl_vereda') . " v
                ON va.tbl_vereda_id = v.id
              WHERE va.tipo_registro = 'Visita'
              GROUP BY c.municipio, COALESCE(v.nombre_vereda, 'Sin vereda')
              ORDER BY c.municipio ASC, vereda ASC";

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
