<?php

/**
 * Clase para integración con API REST de ArcGIS Online - Módulo PAE
 *
 * Consume datos del Programa de Alimentación Escolar desde ArcGIS
 * y los transforma al formato compatible con MainPae::getDataMain()
 *
 * @author SPIDERSOFTWARE
 * @version 1.1 - Agregado sistema de logs
 */
class PaeArcgis
{
    /**
     * Endpoint base de la API de ArcGIS Online
     */
    private static $apiBase = 'https://services7.arcgis.com/EUSBYj1PuyMxVZIb/arcgis/rest/services/service_5c644d64f8cf402f90600c6021e4b182/FeatureServer/0';

    /**
     * Método principal que obtiene datos de ArcGIS y los formatea
     * Compatible con el formato de MainPae::getDataMain()
     *
     * @param array $rqst Array con parámetros de consulta
     * @return array Formato: ['output' => ['valid' => true/false, 'neveras' => X, ...]]
     */
    public static function getDataFromArcgis($rqst)
    {
        try {
            // 1. Validar parámetro de municipio
            $codigoMunicipio = isset($rqst['codigoMunicipio']) ? trim($rqst['codigoMunicipio']) : '';

            if (empty($codigoMunicipio) || $codigoMunicipio == 'seleccione') {
                return Util::error_missing_data();
            }

            // 1b. Obtener y validar filtro de año (vigencia)
            $ano = isset($rqst['ano']) ? trim($rqst['ano']) : 'todos';
            // Solo permitir dígitos para evitar inyección en la query de ArcGIS
            if (!empty($ano) && $ano !== 'todos' && !preg_match('/^\d{4}$/', $ano)) {
                $ano = 'todos';
            }

            // 2. Construir cláusula WHERE
            $nombreMunicipio = null;
            if ($codigoMunicipio == "todos") {
                $whereClause = "1=1"; // Todos los registros
            } else {
                // Convertir código DANE a nombre de municipio
                $nombreMunicipio = self::getMunicipioNameByCode($codigoMunicipio);
                if (!$nombreMunicipio) {
                    // Log: municipio no encontrado en BD
                    self::guardarLog([
                        'tipo_consulta'    => 'getDataFromArcgis',
                        'endpoint_url'     => self::$apiBase,
                        'where_clause'     => '',
                        'municipio_codigo' => $codigoMunicipio,
                        'municipio_nombre' => null,
                        'estado'           => 'ERROR_BD',
                        'error_detalle'    => 'Municipio no encontrado en BD local: ' . $codigoMunicipio
                    ]);
                    return [
                        'output' => [
                            'valid' => false,
                            'error' => 'Municipio no encontrado: ' . $codigoMunicipio
                        ]
                    ];
                }
                $whereClause = "Municipio='" . $nombreMunicipio . "'";
            }

            // 2b. Agregar filtro de año a la cláusula WHERE si aplica
            if (!empty($ano) && $ano !== 'todos') {
                $whereClause .= ($whereClause === '1=1')
                    ? " AND Ano_Levantamiento='" . $ano . "'"
                    : " AND Ano_Levantamiento='" . $ano . "'";
            }

            // 3. Consultar API de ArcGIS (con logging integrado)
            $features = self::fetchArcgisData($whereClause, $codigoMunicipio, $nombreMunicipio);

            if ($features === false) {
                return [
                    'output' => [
                        'valid' => false,
                        'error' => 'Error al consultar API de ArcGIS'
                    ]
                ];
            }

            // 4. Agregar datos y calcular variables
            $data = self::aggregateFeatures($features);

            // 5. Retornar en formato compatible con MainPae
            return [
                'output' => array_merge(['valid' => true], $data)
            ];

        } catch (Exception $e) {
            error_log("PaeArcgis Error: " . $e->getMessage());
            return [
                'output' => [
                    'valid' => false,
                    'error' => 'Error en la integración con ArcGIS: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Ejecuta petición HTTP a ArcGIS REST API con logging integrado
     *
     * @param string $whereClause Cláusula WHERE para filtrar datos
     * @param string|null $municipioCodigo Código DANE del municipio (para log)
     * @param string|null $municipioNombre Nombre normalizado del municipio (para log)
     * @return array|false Array de features o false en caso de error
     */
    private static function fetchArcgisData($whereClause, $municipioCodigo = null, $municipioNombre = null)
    {
        // Medir tiempo de respuesta
        $startTime = microtime(true);

        try {
            // Construir parámetros de consulta
            $params = [
                'where' => $whereClause,
                'outFields' => '*',
                'returnGeometry' => 'false',
                'f' => 'json',
                'resultRecordCount' => 2000
            ];

            $url = self::$apiBase . '/query?' . http_build_query($params);

            // Ejecutar petición con cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $tiempoRespuesta = round(microtime(true) - $startTime, 3);

            // Error de cURL
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                error_log("cURL Error en ArcGIS: " . $error);

                self::guardarLog([
                    'tipo_consulta'    => 'getDataFromArcgis',
                    'endpoint_url'     => self::$apiBase,
                    'where_clause'     => $whereClause,
                    'municipio_codigo' => $municipioCodigo,
                    'municipio_nombre' => $municipioNombre,
                    'request_url'      => $url,
                    'response_body'    => '',
                    'http_code'        => $httpCode,
                    'response_size'    => 0,
                    'total_features'   => 0,
                    'tiempo_respuesta' => $tiempoRespuesta,
                    'estado'           => 'ERROR_CURL',
                    'error_detalle'    => 'cURL Error: ' . $error
                ]);

                return false;
            }

            curl_close($ch);

            $responseSize = strlen($response ?? '');

            // Error HTTP
            if ($httpCode !== 200) {
                error_log("HTTP Error en ArcGIS: " . $httpCode);

                self::guardarLog([
                    'tipo_consulta'    => 'getDataFromArcgis',
                    'endpoint_url'     => self::$apiBase,
                    'where_clause'     => $whereClause,
                    'municipio_codigo' => $municipioCodigo,
                    'municipio_nombre' => $municipioNombre,
                    'request_url'      => $url,
                    'response_body'    => $response ?? '',
                    'http_code'        => $httpCode,
                    'response_size'    => $responseSize,
                    'total_features'   => 0,
                    'tiempo_respuesta' => $tiempoRespuesta,
                    'estado'           => 'ERROR_HTTP',
                    'error_detalle'    => 'HTTP Code: ' . $httpCode
                ]);

                return false;
            }

            // Decodificar respuesta JSON
            $apiData = json_decode($response, true);

            // Error JSON
            if ($apiData === null) {
                error_log("Error JSON en ArcGIS: " . json_last_error_msg());

                self::guardarLog([
                    'tipo_consulta'    => 'getDataFromArcgis',
                    'endpoint_url'     => self::$apiBase,
                    'where_clause'     => $whereClause,
                    'municipio_codigo' => $municipioCodigo,
                    'municipio_nombre' => $municipioNombre,
                    'request_url'      => $url,
                    'response_body'    => $response ?? '',
                    'http_code'        => $httpCode,
                    'response_size'    => $responseSize,
                    'total_features'   => 0,
                    'tiempo_respuesta' => $tiempoRespuesta,
                    'estado'           => 'ERROR_JSON',
                    'error_detalle'    => 'JSON decode error: ' . json_last_error_msg()
                ]);

                return false;
            }

            // Error de la API (respuesta sin features)
            if (!isset($apiData['features']) || !is_array($apiData['features'])) {
                $errorMsg = $apiData['error']['message'] ?? 'Respuesta sin features';
                error_log("Respuesta inválida de ArcGIS: " . substr($response, 0, 500));

                self::guardarLog([
                    'tipo_consulta'    => 'getDataFromArcgis',
                    'endpoint_url'     => self::$apiBase,
                    'where_clause'     => $whereClause,
                    'municipio_codigo' => $municipioCodigo,
                    'municipio_nombre' => $municipioNombre,
                    'request_url'      => $url,
                    'response_body'    => $response,
                    'http_code'        => $httpCode,
                    'response_size'    => $responseSize,
                    'total_features'   => 0,
                    'tiempo_respuesta' => $tiempoRespuesta,
                    'estado'           => 'ERROR_API',
                    'error_detalle'    => $errorMsg
                ]);

                return false;
            }

            $totalFeatures = count($apiData['features']);

            // Guardar sample de los primeros campos para debug
            $sampleFields = [];
            if (!empty($apiData['features'])) {
                $firstFeature = $apiData['features'][0]['attributes'] ?? [];
                $sampleFields = array_slice($firstFeature, 0, 30, true);
            }
            error_log("[PaeArcgis] Sample fields: " . json_encode($sampleFields));

            // Log exitoso
            self::guardarLog([
                'tipo_consulta'    => 'getDataFromArcgis',
                'endpoint_url'     => self::$apiBase,
                'where_clause'     => $whereClause,
                'municipio_codigo' => $municipioCodigo,
                'municipio_nombre' => $municipioNombre,
                'request_url'      => $url,
                'response_body'    => $response,
                'http_code'        => $httpCode,
                'response_size'    => $responseSize,
                'total_features'   => $totalFeatures,
                'tiempo_respuesta' => $tiempoRespuesta,
                'estado'           => 'OK'
            ]);

            return $apiData['features'];

        } catch (Exception $e) {
            $tiempoRespuesta = round(microtime(true) - $startTime, 3);
            error_log("Excepción en fetchArcgisData: " . $e->getMessage());

            self::guardarLog([
                'tipo_consulta'    => 'getDataFromArcgis',
                'endpoint_url'     => self::$apiBase,
                'where_clause'     => $whereClause,
                'municipio_codigo' => $municipioCodigo,
                'municipio_nombre' => $municipioNombre,
                'tiempo_respuesta' => $tiempoRespuesta,
                'estado'           => 'ERROR_CURL',
                'error_detalle'    => 'Excepción: ' . $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Guarda un log de la petición a ArcGIS (lazy loading de LogApiPaeArcgis)
     *
     * @param array $data Datos del log
     * @return void
     */
    private static function guardarLog($data)
    {
        try {
            $logClassPath = __DIR__ . '/LogApiPaeArcgis.php';
            if (file_exists($logClassPath)) {
                if (!class_exists('LogApiPaeArcgis')) {
                    include_once $logClassPath;
                }
                if (!class_exists('DbConection')) {
                    include_once __DIR__ . '/DbConection.php';
                }
                LogApiPaeArcgis::registrar($data);
            }
        } catch (Exception $e) {
            error_log("[PaeArcgis] Error al guardar log: " . $e->getMessage());
        }
    }

    /**
     * Agrega features de ArcGIS y calcula todas las variables
     * Compatible con las 160+ variables de MainPae::getDataMain()
     *
     * @param array $features Array de features de ArcGIS
     * @return array Array con todas las variables agregadas
     */
    private static function aggregateFeatures($features)
    {
        // Inicializar todas las variables (igual que MainPae)
        $data = [
            // Equipamiento - Neveras
            'neveras' => 0,
            'neveras_fun' => 0,
            'neveras_buenas' => 0,
            'neveras_almacenamiento_si' => 0,
            'neveras_almacenamiento_no' => 0,

            // Equipamiento - Congeladores
            'congeladores' => 0,
            'congeladores_funcionando' => 0,

            // Equipamiento - Estufas
            'estufas' => 0,
            'estufas_gen' => 0,
            'quemadores_estufas' => 0,
            'quemadores_estufas_buenos' => 0,

            // Equipamiento - Licuadoras
            'licuadoras' => 0,
            'licuadoras_gen' => 0,
            'licuadoras_industriales' => 0,
            'licuadoras_total' => 0,

            // Menaje
            'cantidad_platos' => 0,
            'cantidad_cucharas' => 0,
            'cantidad_pocillos' => 0,
            'cantidad_tenedores' => 0,
            'cantidad_canecas' => 0,

            // Servicios públicos
            'acceso_alcantarillado_si' => 0,
            'acceso_alcantarillado_no' => 0,
            'recoleccion_basuras_si' => 0,
            'recoleccion_basuras_no' => 0,
            'acceso_agua_si' => 0,
            'acceso_agua_no' => 0,
            'acceso_agua_intermitente' => 0,
            'acceso_electricidad_si' => 0,
            'acceso_electricidad_no' => 0,
            'acceso_electricidad_intermitente' => 0,

            // Tipo de agua
            'acueducto' => 0,
            'embotellada' => 0,
            'lluvia' => 0,
            'carrotanque' => 0,
            'rios_quebradas' => 0,
            'otros_agua' => 0,
            'pozo_agua' => 0,

            // Tipo de energía
            'electricidad' => 0,
            'gas_natural' => 0,
            'lena' => 0,
            'carbon' => 0,
            'gasolina' => 0,
            'gas_propano' => 0,
            'otros_combustibles' => 0,

            // Espacios
            'espacio_preparacion_si' => 0,
            'espacio_preparacion_no' => 0,
            'espacio_almacenamiento_si' => 0,
            'espacio_almacenamiento_no' => 0,
            'espacio_consumo' => 0,

            // Zona y conflicto
            'zona_conflicto_si' => 0,
            'zona_conflicto_no' => 0,
            'cercania_contaminacion_si' => 0,
            'cercania_contaminacion_no' => 0,

            // Concepto sanitario
            'concepto_sanitario_si' => 0,
            'concepto_sanitario_no' => 0,

            // Modalidades PAE
            'complemento_preparado_sitio_si' => 0,
            'complemento_preparado_sitio_no' => 0,
            'complemento_industrializado_si' => 0,
            'complemento_industrializado_no' => 0,
            'almuerzo_preparado_sitio_si' => 0,
            'almuerzo_preparado_sitio_no' => 0,
            'almuerzo_trasportado_si' => 0,
            'almuerzo_trasportado_no' => 0,

            // Higiene personal
            'lavamanos_personal_si' => 0,
            'lavamanos_personal_no' => 0,
            'sanitario_personal_si' => 0,
            'sanitario_personal_no' => 0,
            'almacenamiento_personal_si' => 0,
            'almacenamiento_personal_no' => 0,

            // Contadores generales
            'caracterizaciones' => count($features),
            'zona_rural' => 0,
            'zona_urbana' => 0,
            'ninos_foc' => 0,

            // Almacenamiento
            'almacena_alto_suelo_si' => 0,
            'almacena_alto_suelo_no' => 0,
            'almacena_balde' => 0,
            'almacena_canasta' => 0,
            'almacena_estante' => 0,
            'almacena_ninguno' => 0,
            'almacena_na' => 0,
            'almacena_caja' => 0,

            // Comedor escolar
            'comedor_escolar_si' => 0,
            'comedor_escolar_no' => 0,

            // Estado de techos (infraestructura)
            'estado_techo_almacenamiento_bueno' => 0,
            'estado_techo_almacenamiento_regular' => 0,
            'estado_techo_almacenamiento_malo' => 0,
            'estado_techo_bueno' => 0,
            'estado_techo_regular' => 0,
            'estado_techo_malo' => 0,
            'estado_paredes_bueno' => 0,
            'estado_paredes_regular' => 0,
            'estado_paredes_malo' => 0,
            'estado_piso_bueno' => 0,
            'estado_piso_regular' => 0,
            'estado_piso_malo' => 0,

            // Campos NO disponibles en ArcGIS (se mantienen en 0)
            'disposicion_derechos_pae_enterrado' => 0,
            'disposicion_derechos_pae_quemado' => 0,
            'disposicion_derechos_pae_reciclan' => 0,
            'disposicion_derechos_pae_lombricultura' => 0,
            'disposicion_derechos_pae_tiran_lote' => 0,
            'disposicion_no_organicos_pae_enterrado' => 0,
            'disposicion_no_organicos_pae_quemado' => 0,
            'disposicion_no_organicos_pae_reciclan' => 0,
            'disposicion_no_organicos_pae_lombricultura' => 0,
            'disposicion_no_organicos_pae_tiran_lote' => 0,
            'disposicion_no_organicos_pae_otros' => 0,
            'algo_frecuente_conflicto' => 0,
            'poco_frecuente_conflicto' => 0,
            'no_frecuencia_conflicto' => 0,
            'posee_ollas_pae_si' => 0,
            'posee_ollas_pae_no' => 0,
            'posee_cuchillos_pae_si' => 0,
            'posee_cuchillos_pae_no' => 0,
            'posee_cucharones_pae_si' => 0,
            'posee_cucharones_pae_no' => 0,
            'tamano_neveras_principales_nevera_domestica_vertical_2200l' => 0,
            'tamano_neveras_principales_nevera_domestica_vertical_1200l' => 0,
            'tamano_neveras_principales_nevera_domestica_vertical_400_800L' => 0,
            'tamano_neveras_principales_nevera_domestica_vertical_menor_400L' => 0,
            'tamano_neveras_principales_nevera_domestica_vertical_otra' => 0,
            'tamano_congelador_Congelador_Grande_1400_1600L' => 0,
            'tamano_congelador_Congelador_Pequeño_Menor_400L' => 0,
        ];

        // Iterar features y agregar datos
        foreach ($features as $feature) {
            $attr = $feature['attributes'];

            // SUMAS DIRECTAS
            // Nombres reales de campos ArcGIS (verificados contra la API)
            $data['neveras'] += intval($attr['Cuantas_Neveras_tiene'] ?? 0);
            $data['neveras_fun'] += intval($attr['Cuantas_Neveras_funcionan'] ?? 0);
            $data['congeladores'] += intval($attr['Cuantos_Congeladores_tiene'] ?? 0);
            $data['congeladores_funcionando'] += intval($attr['Cuantos_Congel_funcionan_tiene'] ?? 0);
            $data['quemadores_estufas'] += intval($attr['Cuantos_quemadores_total_estufas'] ?? 0);
            $data['quemadores_estufas_buenos'] += intval($attr['Cuantos_funcionan_correctamente'] ?? 0);
            $data['cantidad_platos'] += intval($attr['Cuantos_Platos_dispone'] ?? 0);
            $data['cantidad_cucharas'] += intval($attr['Cuantas_Cucharas_dispone'] ?? 0);
            $data['cantidad_pocillos'] += intval($attr['Cuantos_Pocillos_dispone'] ?? 0);
            $data['cantidad_tenedores'] += intval($attr['Cuantos_Tenedores_dispone'] ?? 0);
            $data['cantidad_canecas'] += intval($attr['Cuantas_canecas_dispone'] ?? 0);
            $data['ninos_foc'] += intval($attr['Total_Alimentacion'] ?? 0);
            $data['licuadoras'] += intval($attr['Cuantas_Licuadoras_tiene'] ?? 0);
            $data['licuadoras_industriales'] += intval($attr['Cuantas_Licuad_func_industrial'] ?? 0);

            // CONTEOS CONDICIONALES - Sector
            if (($attr['Sector'] ?? '') == 'Rural') {
                $data['zona_rural']++;
            } elseif (($attr['Sector'] ?? '') == 'Urbano') {
                $data['zona_urbana']++;
            }

            // Estufas
            if (($attr['Tienen_Estufas'] ?? '') == 'Si') {
                $data['estufas_gen']++;
            }

            // Espacio de almacenamiento
            if (($attr['Espacio_almacenamiento_alim'] ?? '') == 'Si') {
                $data['espacio_almacenamiento_si']++;
            } else {
                $data['espacio_almacenamiento_no']++;
            }

            // Espacio de preparación — campo real: Espacio_preparacion_alimentos
            if (($attr['Espacio_preparacion_alimentos'] ?? '') == 'Si') {
                $data['espacio_preparacion_si']++;
            } else {
                $data['espacio_preparacion_no']++;
            }

            // Concepto sanitario
            $conceptoSanitario = $attr['Cuenta_concepto_sanitario'] ?? '';
            if (in_array($conceptoSanitario, ['Si_Favorable', 'Si_Favorable_requerimientos'])) {
                $data['concepto_sanitario_si']++;
            } else {
                $data['concepto_sanitario_no']++;
            }

            // Cercanía a contaminación
            if (($attr['Cercania_fuentes_contaminacion'] ?? '') == 'Si') {
                $data['cercania_contaminacion_si']++;
            } else {
                $data['cercania_contaminacion_no']++;
            }

            // Zona de conflicto — campo real: Ubicada_zona_conflicto
            if (($attr['Ubicada_zona_conflicto'] ?? '') == 'Si') {
                $data['zona_conflicto_si']++;
            } else {
                $data['zona_conflicto_no']++;
            }

            // Modalidades PAE
            if (($attr['Complemento_Preparado_sitio'] ?? '') == 'Si') {
                $data['complemento_preparado_sitio_si']++;
            } else {
                $data['complemento_preparado_sitio_no']++;
            }

            if (($attr['Complemento_Industrializado'] ?? '') == 'Si') {
                $data['complemento_industrializado_si']++;
            } else {
                $data['complemento_industrializado_no']++;
            }

            if (($attr['Almuerzo_Preparado_sitio'] ?? '') == 'Si') {
                $data['almuerzo_preparado_sitio_si']++;
            } else {
                $data['almuerzo_preparado_sitio_no']++;
            }

            if (($attr['Almuerzo_Modalidad_comida_transportada'] ?? '') == 'Si') {
                $data['almuerzo_trasportado_si']++;
            } else {
                $data['almuerzo_trasportado_no']++;
            }

            // Comedor escolar
            if (($attr['IE_cuenta_con_comedor_escolar'] ?? '') == 'Si') {
                $data['comedor_escolar_si']++;
            } else {
                $data['comedor_escolar_no']++;
            }

            // Acceso a agua
            $accesoAgua = $attr['Acceso_calidad_Agua'] ?? '';
            if ($accesoAgua == 'Si') {
                $data['acceso_agua_si']++;
            } elseif ($accesoAgua == 'Intermitente') {
                $data['acceso_agua_intermitente']++;
            } else {
                $data['acceso_agua_no']++;
            }

            // Tipo de agua — campo real: Donde_obtiene_agua_PAE
            $tipoAgua = $attr['Donde_obtiene_agua_PAE'] ?? '';
            if (strpos($tipoAgua, 'Acueducto') !== false) $data['acueducto']++;
            if (strpos($tipoAgua, 'Embotellada') !== false) $data['embotellada']++;
            if (strpos($tipoAgua, 'Lluvia') !== false || strpos($tipoAgua, 'Agua_Lluvia') !== false) $data['lluvia']++;
            if (strpos($tipoAgua, 'Carrotanque') !== false) $data['carrotanque']++;
            if (strpos($tipoAgua, 'Rios') !== false || strpos($tipoAgua, 'Quebrada') !== false || strpos($tipoAgua, 'Cuerpos_agua') !== false) $data['rios_quebradas']++;
            if (strpos($tipoAgua, 'Pozo') !== false) $data['pozo_agua']++;

            // Acceso a electricidad — campo real: Acceso_calidad_Electricidad
            $accesoElectricidad = $attr['Acceso_calidad_Electricidad'] ?? '';
            if ($accesoElectricidad == 'Si') {
                $data['acceso_electricidad_si']++;
            } elseif ($accesoElectricidad == 'Intermitente') {
                $data['acceso_electricidad_intermitente']++;
            } else {
                $data['acceso_electricidad_no']++;
            }

            // Tipo de energía — ArcGIS no tiene un campo único; se infiere de Acceso_calidad_Gas y Electricidad
            if ($accesoElectricidad == 'Si') $data['electricidad']++;
            $accesoGas = $attr['Acceso_calidad_Gas'] ?? '';
            if ($accesoGas == 'Si') $data['gas_natural']++;

            // Alcantarillado — campo real: Serv_Alcantarillado
            if (($attr['Serv_Alcantarillado'] ?? '') == 'Si') {
                $data['acceso_alcantarillado_si']++;
            } else {
                $data['acceso_alcantarillado_no']++;
            }

            // Recolección de basuras — campo real: Cuenta_serv_recoleccion_basuras
            if (($attr['Cuenta_serv_recoleccion_basuras'] ?? '') == 'Si') {
                $data['recoleccion_basuras_si']++;
            } else {
                $data['recoleccion_basuras_no']++;
            }

            // Lavamanos — campo real: Tiene_lavamanos_personal
            if (($attr['Tiene_lavamanos_personal'] ?? '') == 'Si') {
                $data['lavamanos_personal_si']++;
            } else {
                $data['lavamanos_personal_no']++;
            }

            // Sanitario — campo real: Tiene_bateria_sanitar_personal
            if (($attr['Tiene_bateria_sanitar_personal'] ?? '') == 'Si') {
                $data['sanitario_personal_si']++;
            } else {
                $data['sanitario_personal_no']++;
            }

            // Almacenamiento alto del suelo — campo real: Almacena_aliment_tarima_estibas
            if (($attr['Almacena_aliment_tarima_estibas'] ?? '') == 'Si') {
                $data['almacena_alto_suelo_si']++;
            } else {
                $data['almacena_alto_suelo_no']++;
            }

            // Neveras de almacenamiento (no hay campo directo; se usa Tiene_Neveras)
            if (($attr['Tiene_Neveras'] ?? '') == 'Si') {
                $data['neveras_almacenamiento_si']++;
            } else {
                $data['neveras_almacenamiento_no']++;
            }

            // Estado techo almacenamiento — campo real: Estado_Techo_almac
            $estadoTechoAlm = $attr['Estado_Techo_almac'] ?? '';
            if ($estadoTechoAlm == 'Bueno') {
                $data['estado_techo_almacenamiento_bueno']++;
            } elseif ($estadoTechoAlm == 'Regular') {
                $data['estado_techo_almacenamiento_regular']++;
            } elseif ($estadoTechoAlm == 'Malo') {
                $data['estado_techo_almacenamiento_malo']++;
            }

            // Estado techo preparación — campo real: Estado_Techo_Prepar
            $estadoTechoPrep = $attr['Estado_Techo_Prepar'] ?? '';
            if ($estadoTechoPrep == 'Bueno') {
                $data['estado_techo_bueno']++;
            } elseif ($estadoTechoPrep == 'Regular') {
                $data['estado_techo_regular']++;
            } elseif ($estadoTechoPrep == 'Malo') {
                $data['estado_techo_malo']++;
            }

            // Estado paredes almacenamiento — campo real: Estado_Paredes_almac
            $estadoParedes = $attr['Estado_Paredes_almac'] ?? '';
            if ($estadoParedes == 'Bueno') {
                $data['estado_paredes_bueno']++;
            } elseif ($estadoParedes == 'Regular') {
                $data['estado_paredes_regular']++;
            } elseif ($estadoParedes == 'Malo') {
                $data['estado_paredes_malo']++;
            }

            // Estado piso almacenamiento — campo real: Estado_Piso_almac
            $estadoPiso = $attr['Estado_Piso_almac'] ?? '';
            if ($estadoPiso == 'Bueno') {
                $data['estado_piso_bueno']++;
            } elseif ($estadoPiso == 'Regular') {
                $data['estado_piso_regular']++;
            } elseif ($estadoPiso == 'Malo') {
                $data['estado_piso_malo']++;
            }
        }

        // Calcular totales derivados
        $data['licuadoras_total'] = $data['licuadoras'] + $data['licuadoras_industriales'];
        $data['estufas'] = $data['estufas_gen'];
        $data['neveras_buenas'] = $data['neveras_fun']; // Asumiendo que funcionando = buenas

        return $data;
    }

    /**
     * Convierte código DANE de municipio a nombre normalizado para ArcGIS
     *
     * @param string $codigo Código DANE del municipio (ej: "68001")
     * @return string|false Nombre normalizado (ej: "BUCARAMANGA") o false si no existe
     */
    private static function getMunicipioNameByCode($codigo)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $stmt = $pdo->prepare("SELECT municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio = :codigo LIMIT 1");
            $stmt->execute([':codigo' => $codigo]);
            $nombre = $stmt->fetchColumn();

            $db->closeConect();

            if (!$nombre) {
                return false;
            }

            // Normalizar nombre para ArcGIS: "San Gil" -> "SAN_GIL"
            $nombreNormalizado = self::removeAccents($nombre);
            $nombreNormalizado = str_replace(' ', '_', $nombreNormalizado);
            $nombreNormalizado = strtoupper($nombreNormalizado);

            return $nombreNormalizado;

        } catch (PDOException $e) {
            error_log("[PaeArcgis] Error PDO en getMunicipioNameByCode: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("[PaeArcgis] Error genérico en getMunicipioNameByCode: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina tildes y caracteres especiales de un string
     *
     * @param string $str String con posibles tildes
     * @return string String sin tildes ni caracteres especiales
     */
    private static function removeAccents($str)
    {
        $search  = ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ','ü','Ü'];
        $replace = ['a','e','i','o','u','A','E','I','O','U','n','N','u','U'];
        return str_replace($search, $replace, $str);
    }

    /**
     * Obtiene el resumen de municipios con conteo de sedes desde ArcGIS
     * Para pintar el mapa de municipios
     *
     * @param array $rqst Parámetros: ano (vigencia)
     * @return array Resumen con municipios
     */
    public static function getResumenMunicipios($rqst)
    {
        try {
            $ano = isset($rqst['ano']) ? trim($rqst['ano']) : 'todos';
            if (!empty($ano) && $ano !== 'todos' && !preg_match('/^\d{4}$/', $ano)) {
                $ano = 'todos';
            }

            // Construir cláusula WHERE para todos los municipios
            $whereClause = "1=1";
            if (!empty($ano) && $ano !== 'todos') {
                $whereClause .= " AND Ano_Levantamiento='" . $ano . "'";
            }

            // Consultar API de ArcGIS
            $features = self::fetchArcgisData($whereClause, 'todos', null);

            if ($features === false) {
                return [
                    'output' => [
                        'valid' => false,
                        'error' => 'Error al consultar API de ArcGIS'
                    ]
                ];
            }

            // Contar sedes por municipio
            $municipiosConteo = [];
            $totalSedes = 0;

            foreach ($features as $feature) {
                $attr = $feature['attributes'];
                // Usar el nombre tal cual viene de la API para coincidir con nombre_api_rpc
                $municipio = trim($attr['Municipio'] ?? 'Sin municipio');
                
                $municipiosConteo[$municipio] = ($municipiosConteo[$municipio] ?? 0) + 1;
                $totalSedes++;
            }

            arsort($municipiosConteo);

            return [
                'output' => [
                    'valid' => true,
                    'response' => [
                        'municipios' => $municipiosConteo,
                        'total_sedes' => $totalSedes,
                        'total_municipios' => count($municipiosConteo)
                    ]
                ]
            ];

        } catch (Exception $e) {
            error_log("PaeArcgis getResumenMunicipios Error: " . $e->getMessage());
            return [
                'output' => [
                    'valid' => false,
                    'error' => $e->getMessage()
                ]
            ];
        }
    }
}
