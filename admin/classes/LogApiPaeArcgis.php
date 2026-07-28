<?php

/**
 * Clase para gestionar los logs de la API REST de ArcGIS Online - Módulo PAE
 *
 * Registra cada petición (request/response) a la API de ArcGIS
 * y permite consultarlos desde el panel de administración.
 *
 * @author SPIDERSOFTWARE
 * @version 1.0
 */
class LogApiPaeArcgis
{
    /** Nombre de la tabla de logs */
    private static $tabla = 'tbl_log_api_pae_arcgis';

    /**
     * Registra un log de petición a la API de ArcGIS
     *
     * @param array $data Datos del log:
     *   - tipo_consulta (string): Tipo de consulta (getDataFromArcgis, getMunicipios)
     *   - endpoint_url (string): URL completa del endpoint
     *   - where_clause (string): Cláusula WHERE enviada
     *   - municipio_codigo (string): Código DANE del municipio
     *   - municipio_nombre (string): Nombre normalizado para ArcGIS
     *   - http_code (int): Código HTTP de respuesta
     *   - response_size (int): Tamaño de la respuesta en bytes
     *   - total_features (int): Cantidad de features retornados
     *   - tiempo_respuesta (float): Tiempo en segundos
     *   - estado (string): OK, ERROR_CURL, ERROR_HTTP, ERROR_JSON, ERROR_API, ERROR_BD
     *   - error_detalle (string): Detalle del error
     *   - origen (string): dashboard, ajax, test
     * @return int|false ID del log insertado o false en error
     */
    public static function registrar($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Obtener IP y usuario de sesión
            $ip = self::getClientIp();
            $usuario = isset($_SESSION['session_user']['nombre'])
                ? $_SESSION['session_user']['nombre']
                : (isset($_SESSION['session_user']['user']) ? $_SESSION['session_user']['user'] : 'sistema');

            $sql = "INSERT INTO " . self::$tabla . " (
                        fecha, tipo_consulta, endpoint_url, where_clause,
                        municipio_codigo, municipio_nombre, request_url, response_body,
                        http_code, response_size, total_features, tiempo_respuesta,
                        estado, error_detalle, ip_solicitante, usuario, origen
                    ) VALUES (
                        NOW(), :tipo_consulta, :endpoint_url, :where_clause,
                        :municipio_codigo, :municipio_nombre, :request_url, :response_body,
                        :http_code, :response_size, :total_features, :tiempo_respuesta,
                        :estado, :error_detalle, :ip_solicitante, :usuario, :origen
                    )";

            // Truncar response_body a 65000 caracteres para evitar exceder LONGTEXT
            $responseBody = $data['response_body'] ?? '';
            if (strlen($responseBody) > 65000) {
                $responseBody = substr($responseBody, 0, 65000) . '... [TRUNCADO]';
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tipo_consulta'    => $data['tipo_consulta'] ?? '',
                ':endpoint_url'     => $data['endpoint_url'] ?? '',
                ':where_clause'     => $data['where_clause'] ?? '',
                ':municipio_codigo' => $data['municipio_codigo'] ?? null,
                ':municipio_nombre' => $data['municipio_nombre'] ?? null,
                ':request_url'      => $data['request_url'] ?? '',
                ':response_body'    => $responseBody,
                ':http_code'        => $data['http_code'] ?? null,
                ':response_size'    => $data['response_size'] ?? 0,
                ':total_features'   => $data['total_features'] ?? 0,
                ':tiempo_respuesta' => $data['tiempo_respuesta'] ?? null,
                ':estado'           => $data['estado'] ?? 'OK',
                ':error_detalle'    => $data['error_detalle'] ?? null,
                ':ip_solicitante'   => $ip,
                ':usuario'          => $usuario,
                ':origen'           => $data['origen'] ?? 'dashboard'
            ]);

            $logId = $pdo->lastInsertId();
            $db->closeConect();

            return $logId;
        } catch (Exception $e) {
            error_log("[LogApiPaeArcgis] Error al registrar log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los logs con paginación y filtros
     *
     * @param array $rqst Parámetros:
     *   - fecha_desde (string): Fecha inicio (Y-m-d)
     *   - fecha_hasta (string): Fecha fin (Y-m-d)
     *   - estado (string): Filtrar por estado (OK, ERROR_*)
     *   - municipio_codigo (string): Filtrar por municipio
     *   - limit (int): Cantidad de registros (default 50)
     *   - offset (int): Desplazamiento (default 0)
     * @return array Formato estándar con logs en response
     */
    public static function getLogs($rqst)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $where = " WHERE 1=1 ";
            $params = [];

            // Filtro por fecha desde
            if (!empty($rqst['fecha_desde'])) {
                $where .= " AND fecha >= :fecha_desde ";
                $params[':fecha_desde'] = $rqst['fecha_desde'] . ' 00:00:00';
            }

            // Filtro por fecha hasta
            if (!empty($rqst['fecha_hasta'])) {
                $where .= " AND fecha <= :fecha_hasta ";
                $params[':fecha_hasta'] = $rqst['fecha_hasta'] . ' 23:59:59';
            }

            // Filtro por estado
            if (!empty($rqst['estado']) && $rqst['estado'] !== 'Todos') {
                $where .= " AND estado = :estado ";
                $params[':estado'] = $rqst['estado'];
            }

            // Filtro por municipio
            if (!empty($rqst['municipio_codigo']) && $rqst['municipio_codigo'] !== 'Todos') {
                $where .= " AND municipio_codigo = :municipio_codigo ";
                $params[':municipio_codigo'] = $rqst['municipio_codigo'];
            }

            // Contar total
            $sqlCount = "SELECT COUNT(*) as total FROM " . self::$tabla . $where;
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute($params);
            $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

            // Obtener registros con paginación
            $limit  = intval($rqst['limit'] ?? 50);
            $offset = intval($rqst['offset'] ?? 0);

            $sql = "SELECT id, fecha, tipo_consulta, endpoint_url, where_clause,
                           municipio_codigo, municipio_nombre, http_code, response_size,
                           total_features, tiempo_respuesta, estado, error_detalle,
                           ip_solicitante, usuario, origen
                    FROM " . self::$tabla . $where . "
                    ORDER BY fecha DESC
                    LIMIT " . $limit . " OFFSET " . $offset;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid'    => true,
                    'response' => [
                        'logs'  => $logs,
                        'total' => intval($total)
                    ]
                ]
            ];
        } catch (Exception $e) {
            error_log("[LogApiPaeArcgis] Error al obtener logs: " . $e->getMessage());
            return [
                'output' => [
                    'valid' => false,
                    'error' => 'Error al consultar los logs: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Obtiene el detalle completo de un log específico
     *
     * @param array $rqst Parámetros: id (int)
     * @return array Formato estándar con log completo
     */
    public static function getLogDetalle($rqst)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "SELECT id, fecha, tipo_consulta, endpoint_url, where_clause,
                           municipio_codigo, municipio_nombre, http_code, response_size,
                           total_features, tiempo_respuesta, estado, error_detalle,
                           ip_solicitante, usuario, origen, response_body
                    FROM " . self::$tabla . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => intval($rqst['id'] ?? 0)]);
            $log = $stmt->fetch(PDO::FETCH_ASSOC);

            $db->closeConect();

            if (!$log) {
                return [
                    'output' => [
                        'valid' => false,
                        'error' => 'Log no encontrado'
                    ]
                ];
            }

            return [
                'output' => [
                    'valid'    => true,
                    'response' => $log
                ]
            ];
        } catch (Exception $e) {
            return [
                'output' => [
                    'valid' => false,
                    'error' => 'Error: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Obtiene estadísticas resumidas de los logs de ArcGIS PAE
     *
     * @param array $rqst Parámetros opcionales de filtro
     * @return array Estadísticas: total, por estado, promedio tiempo, últimas 24h
     */
    public static function getEstadisticas($rqst)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Estadísticas generales
            $sql = "SELECT
                        COUNT(*) as total_logs,
                        SUM(CASE WHEN estado = 'OK' THEN 1 ELSE 0 END) as total_ok,
                        SUM(CASE WHEN estado != 'OK' THEN 1 ELSE 0 END) as total_errores,
                        ROUND(AVG(tiempo_respuesta), 3) as promedio_tiempo,
                        ROUND(MAX(tiempo_respuesta), 3) as max_tiempo,
                        ROUND(MIN(tiempo_respuesta), 3) as min_tiempo,
                        SUM(total_features) as total_features_api
                    FROM " . self::$tabla;
            $stats = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

            // Últimas 24 horas
            $sql24h = "SELECT
                        COUNT(*) as total_24h,
                        SUM(CASE WHEN estado = 'OK' THEN 1 ELSE 0 END) as ok_24h,
                        SUM(CASE WHEN estado != 'OK' THEN 1 ELSE 0 END) as errores_24h
                       FROM " . self::$tabla . "
                       WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stats24h = $pdo->query($sql24h)->fetch(PDO::FETCH_ASSOC);

            // Distribución por estado
            $sqlEstados = "SELECT estado, COUNT(*) as cantidad
                          FROM " . self::$tabla . "
                          GROUP BY estado ORDER BY cantidad DESC";
            $estadosResult = $pdo->query($sqlEstados)->fetchAll(PDO::FETCH_ASSOC);

            // Top municipios consultados
            $sqlMunicipios = "SELECT municipio_nombre, municipio_codigo, COUNT(*) as cantidad
                             FROM " . self::$tabla . "
                             WHERE municipio_nombre IS NOT NULL
                             GROUP BY municipio_nombre, municipio_codigo
                             ORDER BY cantidad DESC LIMIT 10";
            $topMunicipios = $pdo->query($sqlMunicipios)->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid'    => true,
                    'response' => [
                        'general'        => $stats,
                        'ultimas_24h'    => $stats24h,
                        'por_estado'     => $estadosResult,
                        'top_municipios' => $topMunicipios
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'output' => [
                    'valid' => false,
                    'error' => 'Error: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Elimina logs antiguos (limpieza)
     *
     * @param array $rqst Parámetros: dias (int) - eliminar logs más antiguos que X días
     * @return array Cantidad de registros eliminados
     */
    public static function limpiarLogs($rqst)
    {
        try {
            $dias = intval($rqst['dias'] ?? 90);

            $db = new DbConection();
            $pdo = $db->openConect();

            if ($dias === 0) {
                $sql = "DELETE FROM " . self::$tabla;
            } else {
                if ($dias < 7) $dias = 7;
                $sql = "DELETE FROM " . self::$tabla . " WHERE fecha < DATE_SUB(NOW(), INTERVAL " . intval($dias) . " DAY)";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $eliminados = $stmt->rowCount();

            $db->closeConect();

            return [
                'output' => [
                    'valid'    => true,
                    'response' => [
                        'eliminados' => $eliminados,
                        'dias'       => $dias
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'output' => [
                    'valid' => false,
                    'error' => 'Error: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Obtiene la IP del cliente
     *
     * @return string IP del cliente
     */
    private static function getClientIp()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
