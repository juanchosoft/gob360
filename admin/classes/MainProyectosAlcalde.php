<?php

/**
 * Clase para obtener estadísticas principales de proyectos de alcaldías
 * @author SPIDERSOFTWARE
 */
class MainProyectosAlcalde
{
    public function __construct() {}

    /**
     * Obtiene el total de proyectos por municipio
     *
     * @param array $rqst Array con filtros opcionales (codigo_municipio, tbl_secretarias_id, tbl_vereda_id)
     * @return array Respuesta con total de proyectos
     */
    public static function getTotalProyectos($rqst = null)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $codigo_municipio = isset($rqst['codigo_municipio']) ? $rqst['codigo_municipio'] : null;
            $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? $rqst['tbl_secretarias_id'] : null;
            $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? $rqst['tbl_vereda_id'] : null;

            $query = "SELECT COUNT(*) as total_proyectos
                     FROM " . $db->getTable('tbl_proyectos_alcaldias') . "
                     WHERE 1=1";

            $params = [];

            if ($codigo_municipio) {
                $query .= " AND tbl_municipio_id = :codigo_municipio";
                $params[':codigo_municipio'] = $codigo_municipio;
            }

            if ($tbl_secretarias_id) {
                $query .= " AND tbl_secretarias_id = :tbl_secretarias_id";
                $params[':tbl_secretarias_id'] = $tbl_secretarias_id;
            }

            if ($tbl_vereda_id) {
                $query .= " AND tbl_vereda_id = :tbl_vereda_id";
                $params[':tbl_vereda_id'] = $tbl_vereda_id;
            }

            error_log("getTotalProyectos - Query: $query");
            error_log("getTotalProyectos - Params: " . print_r($params, true));

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("getTotalProyectos - Result: " . print_r($result, true));

            $response = [
                'output' => [
                    'valid' => true,
                    'total_proyectos' => intval($result['total_proyectos'])
                ]
            ];
        } catch (Exception $e) {
            error_log("MainProyectosAlcalde::getTotalProyectos - ERROR: " . $e->getMessage());
            $response = Util::error_general("Error al obtener total de proyectos: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    /**
     * Obtiene el valor total de inversión en proyectos
     *
     * @param array $rqst Array con filtros opcionales (codigo_municipio, tbl_secretarias_id, tbl_vereda_id)
     * @return array Respuesta con valor total de inversión
     */
    public static function getValorTotalInversion($rqst = null)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $codigo_municipio = isset($rqst['codigo_municipio']) ? $rqst['codigo_municipio'] : null;
            $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? $rqst['tbl_secretarias_id'] : null;
            $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? $rqst['tbl_vereda_id'] : null;

            $query = "SELECT SUM(valor_proyecto) as valor_total
                     FROM " . $db->getTable('tbl_proyectos_alcaldias') . "
                     WHERE 1=1";

            $params = [];

            if ($codigo_municipio) {
                $query .= " AND tbl_municipio_id = :codigo_municipio";
                $params[':codigo_municipio'] = $codigo_municipio;
            }

            if ($tbl_secretarias_id) {
                $query .= " AND tbl_secretarias_id = :tbl_secretarias_id";
                $params[':tbl_secretarias_id'] = $tbl_secretarias_id;
            }

            if ($tbl_vereda_id) {
                $query .= " AND tbl_vereda_id = :tbl_vereda_id";
                $params[':tbl_vereda_id'] = $tbl_vereda_id;
            }

            error_log("getValorTotalInversion - Query: $query");
            error_log("getValorTotalInversion - Params: " . print_r($params, true));

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("getValorTotalInversion - Result: " . print_r($result, true));

            $response = [
                'output' => [
                    'valid' => true,
                    'valor_total' => floatval($result['valor_total'] ?? 0)
                ]
            ];
        } catch (Exception $e) {
            error_log("MainProyectosAlcalde::getValorTotalInversion - ERROR: " . $e->getMessage());
            $response = Util::error_general("Error al obtener valor total: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    /**
     * Obtiene conteo de proyectos por estado
     *
     * @param array $rqst Array con filtros opcionales (codigo_municipio, tbl_secretarias_id, tbl_vereda_id)
     * @return array Respuesta con conteo por estado
     */
    public static function getProyectosPorEstado($rqst = null)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $codigo_municipio = isset($rqst['codigo_municipio']) ? $rqst['codigo_municipio'] : null;
            $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? $rqst['tbl_secretarias_id'] : null;
            $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? $rqst['tbl_vereda_id'] : null;

            $query = "SELECT
                        estado,
                        COUNT(*) as total
                     FROM " . $db->getTable('tbl_proyectos_alcaldias') . "
                     WHERE 1=1";

            $params = [];

            if ($codigo_municipio) {
                $query .= " AND tbl_municipio_id = :codigo_municipio";
                $params[':codigo_municipio'] = $codigo_municipio;
            }

            if ($tbl_secretarias_id) {
                $query .= " AND tbl_secretarias_id = :tbl_secretarias_id";
                $params[':tbl_secretarias_id'] = $tbl_secretarias_id;
            }

            if ($tbl_vereda_id) {
                $query .= " AND tbl_vereda_id = :tbl_vereda_id";
                $params[':tbl_vereda_id'] = $tbl_vereda_id;
            }

            $query .= " GROUP BY estado ORDER BY total DESC";

            error_log("getProyectosPorEstado - Query: $query");
            error_log("getProyectosPorEstado - Params: " . print_r($params, true));

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("getProyectosPorEstado - Results: " . print_r($results, true));

            $response = [
                'output' => [
                    'valid' => true,
                    'proyectos_por_estado' => $results
                ]
            ];
        } catch (Exception $e) {
            error_log("MainProyectosAlcalde::getProyectosPorEstado - ERROR: " . $e->getMessage());
            $response = Util::error_general("Error al obtener proyectos por estado: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    /**
     * Obtiene proyectos por secretaría
     *
     * @param array $rqst Array con filtros opcionales (codigo_municipio)
     * @return array Respuesta con proyectos por secretaría
     */
    public static function getProyectosPorSecretaria($rqst = null)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $codigo_municipio = isset($rqst['codigo_municipio']) ? $rqst['codigo_municipio'] : null;

            $query = "SELECT
                        s.secretaria,
                        s.id as secretaria_id,
                        s.codigo_municipio,
                        COUNT(p.id) as total_proyectos,
                        SUM(p.valor_proyecto) as valor_total
                     FROM " . $db->getTable('tbl_secretarias_municipios') . " s
                     LEFT JOIN " . $db->getTable('tbl_proyectos_alcaldias') . " p
                        ON s.id = p.tbl_secretarias_id";

            $where = [];
            $params = [];

            if ($codigo_municipio) {
                $where[] = "s.codigo_municipio = :codigo_municipio";
                $where[] = "(p.tbl_municipio_id = :codigo_municipio_p OR p.tbl_municipio_id IS NULL)";
                $params[':codigo_municipio'] = $codigo_municipio;
                $params[':codigo_municipio_p'] = $codigo_municipio;
            }

            if (!empty($where)) {
                $query .= " WHERE " . implode(' AND ', $where);
            }

            $query .= " GROUP BY s.id, s.secretaria, s.codigo_municipio
                       ORDER BY total_proyectos DESC, s.secretaria ASC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'output' => [
                    'valid' => true,
                    'proyectos_por_secretaria' => $results
                ]
            ];
        } catch (Exception $e) {
            error_log("MainProyectosAlcalde::getProyectosPorSecretaria - ERROR: " . $e->getMessage());
            $response = Util::error_general("Error al obtener proyectos por secretaría: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    /**
     * Obtiene las provincias únicas de los municipios con proyectos
     *
     * @param array $rqst Array con filtros opcionales (codigo_municipio)
     * @return array Respuesta con las provincias
     */
    public static function getProvincias($rqst = null)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $codigo_municipio = isset($rqst['codigo_municipio']) ? $rqst['codigo_municipio'] : null;

            $query = "SELECT DISTINCT cau.subregion as provincia
                     FROM " . $db->getTable('tbl_proyectos_alcaldias') . " p
                     INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " cau
                        ON p.tbl_municipio_id = cau.codigo_muncipio
                     WHERE cau.subregion IS NOT NULL
                     AND cau.subregion != ''";

            $params = [];

            if ($codigo_municipio) {
                $query .= " AND p.tbl_municipio_id = :codigo_municipio";
                $params[':codigo_municipio'] = $codigo_municipio;
            }

            $query .= " ORDER BY cau.subregion ASC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'output' => [
                    'valid' => true,
                    'response' => $results
                ]
            ];
        } catch (Exception $e) {
            error_log("MainProyectosAlcalde::getProvincias - ERROR: " . $e->getMessage());
            $response = Util::error_general("Error al obtener provincias: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }
}
