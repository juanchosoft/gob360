<?php

/**
 * Clase para integración con API JSON-RPC de Proyectos externos
 *
 * Consume datos de proyectos desde un sistema externo de rendición de cuentas
 * mediante protocolo JSON-RPC 2.0 sobre HTTP POST.
 * Registra automáticamente cada petición en tbl_log_api_rpc.
 *
 * Documentación API: Documentación API v1.01.2026.pdf
 * Método soportado: consultarProyectos
 * Filtros: vigencia (año), bpin (código o array)
 *
 * @author SPIDERSOFTWARE
 * @version 1.1 - Con registro de logs
 */
class ProyectosRpc
{
    /** Ruta al archivo de configuración */
    private static $configPath = __DIR__ . '/../../config.ini';

    /**
     * Obtiene la configuración de la API desde config.ini
     *
     * @return array|false Configuración o false si no existe
     */
    private static function getConfig()
    {
        if (!file_exists(self::$configPath)) {
            error_log("[ProyectosRpc] Archivo config.ini no encontrado: " . self::$configPath);
            return false;
        }

        $config = parse_ini_file(self::$configPath, true);

        if (!$config || !isset($config['proyectos_rpc'])) {
            error_log("[ProyectosRpc] Sección 'proyectos_rpc' no encontrada en config.ini");
            return false;
        }

        $section = $config['proyectos_rpc'];

        // Validar campos requeridos
        if (empty($section['api_url']) || empty($section['username']) || empty($section['password'])) {
            error_log("[ProyectosRpc] Faltan campos requeridos en config.ini (api_url, username, password)");
            return false;
        }

        return $section;
    }

    /**
     * Ejecuta una petición JSON-RPC al endpoint externo y registra el log
     *
     * @param string $method Método RPC (ej: 'consultarProyectos')
     * @param array $params Parámetros del método (vigencia, bpin)
     * @param string $origen Origen de la petición (dashboard, ajax, test)
     * @return array|false Resultado decodificado o false en error
     */
    private static function callRpc($method, $params = [], $origen = 'dashboard')
    {
        $config = self::getConfig();
        if (!$config) {
            // Registrar error de configuración
            self::guardarLog([
                'metodo_rpc'      => $method,
                'endpoint_url'    => 'N/A',
                'request_payload' => json_encode(['params' => $params]),
                'request_params'  => json_encode($params),
                'estado'          => 'ERROR_CONFIG',
                'error_detalle'   => 'No se pudo leer la configuración de config.ini',
                'origen'          => $origen
            ]);
            return false;
        }

        // Construir payload JSON-RPC 2.0
        $payload = [
            'jsonrpc' => '2.0',
            'method'  => $method,
            'login'   => [
                'username' => $config['username'],
                'password' => $config['password']
            ],
            'id' => uniqid('rpc_')
        ];

        // Solo agregar params si hay filtros
        if (!empty($params)) {
            $payload['params'] = $params;
        }

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        // Payload para log (SIN credenciales)
        $payloadLog = $payload;
        $payloadLog['login'] = ['username' => $config['username'], 'password' => '***OCULTO***'];
        $jsonPayloadLog = json_encode($payloadLog, JSON_UNESCAPED_UNICODE);

        error_log("[ProyectosRpc] Llamando API: " . $config['api_url']);
        error_log("[ProyectosRpc] Método: " . $method . " | Params: " . json_encode($params));

        // Medir tiempo de respuesta
        $tiempoInicio = microtime(true);

        // Ejecutar petición cURL
        $ch = curl_init($config['api_url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Content-Length: ' . strlen($jsonPayload)
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => intval($config['timeout'] ?? 30),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $tiempoRespuesta = round(microtime(true) - $tiempoInicio, 3);

        // Verificar errores de cURL
        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
            curl_close($ch);
            error_log("[ProyectosRpc] Error cURL: " . $curlError);

            self::guardarLog([
                'metodo_rpc'       => $method,
                'endpoint_url'     => $config['api_url'],
                'request_payload'  => $jsonPayloadLog,
                'request_params'   => json_encode($params),
                'http_code'        => $httpCode,
                'response_body'    => $response ?: '',
                'total_registros'  => 0,
                'tiempo_respuesta' => $tiempoRespuesta,
                'estado'           => 'ERROR_CURL',
                'error_detalle'    => 'cURL Error: ' . $curlError,
                'origen'           => $origen
            ]);

            return false;
        }

        curl_close($ch);

        // Verificar código HTTP
        if ($httpCode !== 200) {
            $errorMsg = "HTTP $httpCode";
            if ($httpCode === 401) $errorMsg = 'HTTP 401 - Credenciales inválidas';
            if ($httpCode === 400) $errorMsg = 'HTTP 400 - Estructura JSON inválida o método no soportado';

            error_log("[ProyectosRpc] $errorMsg | Response: " . substr($response, 0, 500));

            self::guardarLog([
                'metodo_rpc'       => $method,
                'endpoint_url'     => $config['api_url'],
                'request_payload'  => $jsonPayloadLog,
                'request_params'   => json_encode($params),
                'http_code'        => $httpCode,
                'response_body'    => substr($response, 0, 65000),
                'total_registros'  => 0,
                'tiempo_respuesta' => $tiempoRespuesta,
                'estado'           => 'ERROR_HTTP',
                'error_detalle'    => $errorMsg,
                'origen'           => $origen
            ]);

            return false;
        }

        // Decodificar respuesta JSON
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            error_log("[ProyectosRpc] Error JSON decode: " . $jsonError);

            self::guardarLog([
                'metodo_rpc'       => $method,
                'endpoint_url'     => $config['api_url'],
                'request_payload'  => $jsonPayloadLog,
                'request_params'   => json_encode($params),
                'http_code'        => $httpCode,
                'response_body'    => substr($response, 0, 65000),
                'total_registros'  => 0,
                'tiempo_respuesta' => $tiempoRespuesta,
                'estado'           => 'ERROR_JSON',
                'error_detalle'    => 'JSON decode error: ' . $jsonError,
                'origen'           => $origen
            ]);

            return false;
        }

        // Verificar error JSON-RPC
        if (isset($decoded['error'])) {
            $rpcError = json_encode($decoded['error']);
            error_log("[ProyectosRpc] Error RPC: " . $rpcError);

            self::guardarLog([
                'metodo_rpc'       => $method,
                'endpoint_url'     => $config['api_url'],
                'request_payload'  => $jsonPayloadLog,
                'request_params'   => json_encode($params),
                'http_code'        => $httpCode,
                'response_body'    => substr($response, 0, 65000),
                'total_registros'  => 0,
                'tiempo_respuesta' => $tiempoRespuesta,
                'estado'           => 'ERROR_RPC',
                'error_detalle'    => 'RPC Error: ' . $rpcError,
                'origen'           => $origen
            ]);

            return false;
        }

        $result = $decoded['result'] ?? [];
        $totalRegistros = is_array($result) ? count($result) : 0;

        error_log("[ProyectosRpc] Respuesta exitosa. Registros: " . $totalRegistros);

        // Log exitoso
        self::guardarLog([
            'metodo_rpc'       => $method,
            'endpoint_url'     => $config['api_url'],
            'request_payload'  => $jsonPayloadLog,
            'request_params'   => json_encode($params),
            'http_code'        => $httpCode,
            'response_body'    => substr($response, 0, 65000),
            'total_registros'  => $totalRegistros,
            'tiempo_respuesta' => $tiempoRespuesta,
            'estado'           => 'OK',
            'origen'           => $origen
        ]);

        return $result;
    }

    /**
     * Guarda un registro en la tabla de logs
     * Carga LogApiRpc solo cuando se necesita para no afectar rendimiento
     *
     * @param array $data Datos del log
     */
    private static function guardarLog($data)
    {
        try {
            $logClassPath = __DIR__ . '/LogApiRpc.php';
            if (file_exists($logClassPath)) {
                if (!class_exists('LogApiRpc')) {
                    include_once $logClassPath;
                }
                if (!class_exists('DbConection')) {
                    include_once __DIR__ . '/DbConection.php';
                }
                LogApiRpc::registrar($data);
            }
        } catch (Exception $e) {
            // Si falla el log, no interrumpir la operación principal
            error_log("[ProyectosRpc] Error al guardar log: " . $e->getMessage());
        }
    }

    /**
     * Consulta proyectos con filtros opcionales
     *
     * @param array $rqst Parámetros de consulta
     * @return array Formato estándar
     */
    public static function consultarProyectos($rqst)
    {
        try {
            $params = [];

            // Vigencia (filtro API)
            $vigencia = isset($rqst['vigencia']) ? trim($rqst['vigencia']) : '';
            if (!empty($vigencia)) {
                $params['vigencia'] = $vigencia;
            }

            // BPIN (filtro API)
            if (!empty($rqst['bpin'])) {
                $bpin = $rqst['bpin'];
                $decoded = json_decode($bpin, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $params['bpin'] = $decoded;
                } else {
                    $params['bpin'] = $bpin;
                }
            }

            // Determinar origen
            $origen = $rqst['_origen'] ?? 'ajax';

            // Llamar a la API JSON-RPC
            $proyectos = self::callRpc('consultarProyectos', $params, $origen);

            if ($proyectos === false) {
                return [
                    'output' => [
                        'valid' => false,
                        'error' => 'Error al consultar la API de proyectos. Verifique la configuración en config.ini.'
                    ]
                ];
            }

            // Filtrar por dependencia (secretaría) en PHP
            if (!empty($rqst['dependencia']) && $rqst['dependencia'] !== 'Todas') {
                $dep = strtolower(trim($rqst['dependencia']));
                $proyectos = array_filter($proyectos, function ($p) use ($dep) {
                    return strtolower(trim($p['dependencia'] ?? '')) === $dep;
                });
                $proyectos = array_values($proyectos);
            }

            // Filtrar por provincia en PHP
            if (!empty($rqst['provincia']) && $rqst['provincia'] !== 'Todas') {
                $prov = strtolower(trim($rqst['provincia']));
                $proyectos = array_filter($proyectos, function ($p) use ($prov) {
                    foreach ($p['provincias_lista'] ?? [] as $provincia) {
                        if (strtolower(trim($provincia)) === $prov) {
                            return true;
                        }
                    }
                    return false;
                });
                $proyectos = array_values($proyectos);
            }

            // Filtrar por municipio en PHP (usa nombre_api_rpc que coincide con municipios_lista)
            if (!empty($rqst['municipio']) && $rqst['municipio'] !== 'Todos') {
                $munFiltro = strtolower(trim($rqst['municipio']));
                $proyectos = array_filter($proyectos, function ($p) use ($munFiltro) {
                    foreach ($p['municipios_lista'] ?? [] as $mun) {
                        if (strtolower(trim($mun)) === $munFiltro) {
                            return true;
                        }
                    }
                    return false;
                });
                $proyectos = array_values($proyectos);
            }

            return [
                'output' => [
                    'valid'    => true,
                    'response' => $proyectos
                ]
            ];
        } catch (Exception $e) {
            error_log("[ProyectosRpc] Excepción: " . $e->getMessage());
            return [
                'output' => [
                    'valid' => false,
                    'error' => 'Error interno: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Obtiene resumen/KPIs agrupados de los proyectos
     *
     * @param array $rqst Parámetros de filtro
     * @return array Formato estándar con resumen en response
     */
    public static function getResumenProyectos($rqst)
    {
        $response = self::consultarProyectos($rqst);

        if (!$response['output']['valid']) {
            return $response;
        }

        $proyectos = $response['output']['response'];
        $resumen = self::calcularResumen($proyectos);

        return [
            'output' => [
                'valid'    => true,
                'response' => $resumen
            ]
        ];
    }

    /**
     * Obtiene la lista de dependencias únicas disponibles en la API
     *
     * @param array $rqst Parámetros de filtro
     * @return array Lista de dependencias ordenadas
     */
    public static function getDependencias($rqst)
    {
        $response = self::consultarProyectos($rqst);

        if (!$response['output']['valid']) {
            return $response;
        }

        $dependencias = [];
        foreach ($response['output']['response'] as $p) {
            $dep = trim($p['dependencia'] ?? '');
            if (!empty($dep) && !in_array($dep, $dependencias)) {
                $dependencias[] = $dep;
            }
        }

        sort($dependencias);

        return [
            'output' => [
                'valid'    => true,
                'response' => $dependencias
            ]
        ];
    }

    /**
     * Calcula KPIs y agrupaciones a partir del array de proyectos
     *
     * @param array $proyectos Array de proyectos de la API
     * @return array Resumen con totales, estados, sumas financieras, promedios
     */
    private static function calcularResumen($proyectos)
    {
        $totalProyectos = count($proyectos);

        $estadosConteo      = [];
        $dependenciasConteo = [];
        $provinciasSet      = [];
        $municipiosConteo   = [];

        $totalValorEjecutado     = 0;
        $totalValorTotal         = 0;
        $totalAporteGobernacion  = 0;
        $totalAporteMunicipal    = 0;
        $totalAporteNacional     = 0;
        $sumaAvanceFisico        = 0;
        $sumaAvanceFinanciero    = 0;

        foreach ($proyectos as $p) {
            $estado = $p['estado'] ?? 'Sin Estado';
            $estadosConteo[$estado] = ($estadosConteo[$estado] ?? 0) + 1;

            $dep = $p['dependencia'] ?? 'Sin Dependencia';
            $dependenciasConteo[$dep] = ($dependenciasConteo[$dep] ?? 0) + 1;

            $totalValorEjecutado    += floatval($p['valor_ejecutado'] ?? 0);
            $totalValorTotal        += floatval($p['valor_total'] ?? 0);
            $totalAporteGobernacion += floatval($p['aporte_gobernacion'] ?? 0);
            $totalAporteMunicipal   += floatval($p['aporte_municipal'] ?? 0);
            $totalAporteNacional    += floatval($p['aporte_nacional'] ?? 0);

            $sumaAvanceFisico     += floatval($p['avance_fisico'] ?? 0);
            $sumaAvanceFinanciero += floatval($p['avance_financiero'] ?? 0);

            foreach ($p['provincias_lista'] ?? [] as $prov) {
                $provTrim = trim($prov);
                if (!empty($provTrim)) {
                    $provinciasSet[$provTrim] = true;
                }
            }

            // Conteo de proyectos por municipio (para colorear el mapa)
            foreach ($p['municipios_lista'] ?? [] as $mun) {
                $munTrim = trim($mun);
                if (!empty($munTrim) && $munTrim !== 'Todo el Departamento') {
                    $municipiosConteo[$munTrim] = ($municipiosConteo[$munTrim] ?? 0) + 1;
                }
            }
        }

        arsort($estadosConteo);
        ksort($dependenciasConteo);

        $provincias = array_keys($provinciasSet);
        sort($provincias);

        arsort($municipiosConteo);

        return [
            'total_proyectos'            => $totalProyectos,
            'estados'                    => $estadosConteo,
            'dependencias'               => $dependenciasConteo,
            'valor_ejecutado_total'      => $totalValorEjecutado,
            'valor_total'                => $totalValorTotal,
            'aporte_gobernacion'         => $totalAporteGobernacion,
            'aporte_municipal'           => $totalAporteMunicipal,
            'aporte_nacional'            => $totalAporteNacional,
            'promedio_avance_fisico'     => $totalProyectos > 0 ? round($sumaAvanceFisico / $totalProyectos, 2) : 0,
            'promedio_avance_financiero' => $totalProyectos > 0 ? round($sumaAvanceFinanciero / $totalProyectos, 2) : 0,
            'provincias'                 => $provincias,
            'municipios'                 => $municipiosConteo,
            'proyectos'                  => $proyectos
        ];
    }
}
