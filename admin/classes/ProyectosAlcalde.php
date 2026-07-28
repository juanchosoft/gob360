<?php

/**
 * Clase para gestión de proyectos del Alcalde
 * Reutiliza la tabla tbl_proyectos (compartida con Gobernador)
 * @author SPIDERSOFTWARE
 */
class ProyectosAlcalde
{
    /**
     * Obtiene la inversión total de un municipio
     * @param array $rqst ['mun' => codigo_municipio]
     * @return array
     */
    public static function getInversiontotal($rqst)
    {
        $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    tbl_municipio_id,
                    SUM(valor_proyecto) AS SumaDevalor_proyecto
                  FROM " . $db->getTable('tbl_proyectos') . "
                  WHERE tbl_municipio_id = :mun
                  GROUP BY tbl_municipio_id";

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':mun', $mun, PDO::PARAM_INT);
            $stmt->execute();

            $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($arr)) {
                $arrjson = array('output' => array('valid' => true, 'response' => $arr));
            } else {
                $arrjson = Util::error_no_result();
            }
        } catch (PDOException $e) {
            error_log("Error en getInversiontotal: " . $e->getMessage());
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene la inversión agrupada por secretaría para un municipio
     * @param array $rqst ['mun' => codigo_municipio]
     * @return array Con labels (secretarias) y data (montos)
     */
    public static function getInversionBySecre($rqst)
    {
        $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    s.secretaria,
                    SUM(p.valor_proyecto) as total
                  FROM " . $db->getTable('tbl_secretarias') . " s
                  LEFT JOIN " . $db->getTable('tbl_proyectos') . " p
                    ON p.tbl_secretarias_id = s.id
                  WHERE p.tbl_municipio_id = :mun
                  GROUP BY s.id, s.secretaria
                  ORDER BY total DESC";

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':mun', $mun, PDO::PARAM_INT);
            $stmt->execute();

            $labels = [];
            $data = [];

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $valor) {
                $labels[] = $valor["secretaria"];
                $data[] = intval($valor["total"]);
            }

            $arrjson = array('output' => array('valid' => true, 'response' => compact("labels", "data")));
        } catch (PDOException $e) {
            error_log("Error en getInversionBySecre: " . $e->getMessage());
            $arrjson = array('output' => array('valid' => false, 'response' => compact("labels", "data")));
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene todos los proyectos de un municipio agrupados por secretaría
     * @param array $rqst ['mun' => codigo_municipio]
     * @return array
     */
    public static function getAllproyectosxsecre($rqst)
    {
        $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    p.id,
                    s.secretaria,
                    p.tbl_secretarias_id,
                    p.valor_proyecto,
                    p.proyecto,
                    p.porcentaje_ejecucion,
                    p.fecha_entrega,
                    p.estado
                  FROM " . $db->getTable('tbl_proyectos') . " p
                  INNER JOIN " . $db->getTable('tbl_secretarias') . " s
                    ON p.tbl_secretarias_id = s.id
                  WHERE p.tbl_municipio_id = :mun
                  ORDER BY s.secretaria, p.proyecto";

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':mun', $mun, PDO::PARAM_INT);
            $stmt->execute();

            $arrpro = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($arrpro)) {
                $arrjson = array('output' => array('valid' => true, 'response' => $arrpro));
            } else {
                $arrjson = Util::error_no_result();
            }
        } catch (PDOException $e) {
            error_log("Error en getAllproyectosxsecre: " . $e->getMessage());
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();
        return $arrjson;
    }
}
