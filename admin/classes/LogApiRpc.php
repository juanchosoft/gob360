<?php

/**
 * Clase para gestionar los logs de la API JSON-RPC de Proyectos
 *
 * Registra cada petición (request/response) y permite consultarlos
 * desde el panel de administración.
 *
 * @author SPIDERSOFTWARE
 * @version 1.0
 */
class LogApiRpc
{
    /** Nombre de la tabla de logs */
    private static $tabla = 'tbl_log_api_rpc';

    /**
     * Registra un log de petición a la API
     *
     * @param array $data Datos del log:
     *   - metodo_rpc (string): Método invocado
     *   - endpoint_url (string): URL del endpoint
     *   - request_payload (string): JSON enviado (sin credenciales)
     *   - request_params (string): Parámetros de filtro
     *   - http_code (int): Código HTTP de respuesta
     *   - response_body (string): Respuesta completa
     *   - total_registros (int): Cantidad de registros
     *   - tiempo_respuesta (float): Tiempo en segundos
     *   - estado (string): OK, ERROR_CURL, ERROR_HTTP, ERROR_JSON, ERROR_RPC, ERROR_CONFIG
     *   - error_detalle (string): Detalle del error
     *   - origen (string): dashboard, ajax, cron, test
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
                        fecha, metodo_rpc, endpoint_url, request_payload, request_params,
                        http_code, response_body, total_registros, tiempo_respuesta,
                        estado, error_detalle, ip_solicitante, usuario, origen
                    ) VALUES (
                        NOW(), :metodo_rpc, :endpoint_url, :request_payload, :request_params,
                        :http_code, :response_body, :total_registros, :tiempo_respuesta,
                        :estado, :error_detalle, :ip_solicitante, :usuario, :origen
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':metodo_rpc'       => $data['metodo_rpc'] ?? '',
                ':endpoint_url'     => $data['endpoint_url'] ?? '',
                ':request_payload'  => $data['request_payload'] ?? '',
                ':request_params'   => $data['request_params'] ?? '',
                ':http_code'        => $data['http_code'] ?? null,
                ':response_body'    => $data['response_body'] ?? '',
                ':total_registros'  => $data['total_registros'] ?? 0,
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
            error_log("[LogApiRpc] Error al registrar log: " . $e->getMessage());
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
     *   - metodo_rpc (string): Filtrar por método
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

            // Filtro por método
            if (!empty($rqst['metodo_rpc']) && $rqst['metodo_rpc'] !== 'Todos') {
                $where .= " AND metodo_rpc = :metodo_rpc ";
                $params[':metodo_rpc'] = $rqst['metodo_rpc'];
            }

            // Contar total
            $sqlCount = "SELECT COUNT(*) as total FROM " . self::$tabla . $where;
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute($params);
            $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

            // Obtener registros con paginación
            $limit  = intval($rqst['limit'] ?? 50);
            $offset = intval($rqst['offset'] ?? 0);

            $sql = "SELECT id, fecha, metodo_rpc, endpoint_url,
                           request_params, http_code, total_registros,
                           tiempo_respuesta, estado, error_detalle,
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
            error_log("[LogApiRpc] Error al obtener logs: " . $e->getMessage());
            return [
                'output' => [
                    'valid' => false,
                    'error' => 'Error al consultar los logs: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Obtiene el detalle completo de un log específico (incluye request y response completos)
     *
     * @param array $rqst Parámetros: id (int)
     * @return array Formato estándar con log completo
     */
    public static function getLogDetalle($rqst)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "SELECT * FROM " . self::$tabla . " WHERE id = :id";
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
     * Obtiene estadísticas resumidas de los logs
     *
     * @param array $rqst Parámetros opcionales de filtro de fechas
     * @return array Estadísticas: total, por estado, promedio tiempo, últimas 24h
     */
    public static function getEstadisticas($rqst)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Total de logs
            $sql = "SELECT
                        COUNT(*) as total_logs,
                        SUM(CASE WHEN estado = 'OK' THEN 1 ELSE 0 END) as total_ok,
                        SUM(CASE WHEN estado != 'OK' THEN 1 ELSE 0 END) as total_errores,
                        ROUND(AVG(tiempo_respuesta), 3) as promedio_tiempo,
                        ROUND(MAX(tiempo_respuesta), 3) as max_tiempo,
                        ROUND(MIN(tiempo_respuesta), 3) as min_tiempo,
                        SUM(total_registros) as total_registros_api
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

            // Distribución por hora (últimas 24h)
            $sqlHora = "SELECT HOUR(fecha) as hora, COUNT(*) as cantidad
                       FROM " . self::$tabla . "
                       WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                       GROUP BY HOUR(fecha) ORDER BY hora";
            $porHora = $pdo->query($sqlHora)->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid'    => true,
                    'response' => [
                        'general'    => $stats,
                        'ultimas_24h' => $stats24h,
                        'por_estado' => $estadosResult,
                        'por_hora'   => $porHora
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

            // Si dias = 0 → eliminar TODOS los logs
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
