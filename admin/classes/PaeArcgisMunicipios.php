<?php
/**
 * Clase para obtener la lista de municipios disponibles en ArcGIS API
 * y compararlos con la base de datos local
 */

class PaeArcgisMunicipios
{
    private static $apiBase = 'https://services7.arcgis.com/EUSBYj1PuyMxVZIb/arcgis/rest/services/service_5c644d64f8cf402f90600c6021e4b182/FeatureServer/0';

    /**
     * Lista estática de municipios disponibles en ArcGIS (82 municipios)
     * Esta lista se actualizó el 2026-02-17
     */
    private static $municipiosArcgisEstaticos = [
        'AGUADA', 'ALBANIA', 'ARATOCA', 'BARBOSA', 'BARICHARA', 'BETULIA', 'BOLIVAR',
        'CABRERA', 'CALIFORNIA', 'CAPITANEJO', 'CARCASI', 'CEPITA', 'CERRITO', 'CHARALA',
        'CHARTA', 'CHIMA', 'CHIPATA', 'CIMITARRA', 'CONCEPCION', 'CONFINES', 'CONTRATACION',
        'COROMORO', 'CURITI', 'EL_CARMEN_DE_CHUCURI', 'EL_GUACAMAYO', 'EL_PENON', 'EL_PLAYON',
        'ENCINO', 'ENCISO', 'FLORIAN', 'GALAN', 'GAMBITA', 'GUACA', 'GUADALUPE', 'GUAPOTA',
        'GUAVATA', 'GUEPSA', 'HATO', 'JESUS_MARIA', 'JORDAN', 'LANDAZURI', 'LA_BELLEZA',
        'LA_PAZ', 'LEBRIJA', 'LOS_SANTOS', 'MACARAVITA', 'MALAGA', 'MATANZA', 'MOGOTES',
        'MOLAGAVITA', 'OCAMONTE', 'OIBA', 'ONZAGA', 'PALMAR', 'PALMAS_DEL_SOCORRO', 'PARAMO',
        'PINCHOTE', 'PUENTE_NACIONAL', 'PUERTO_PARRA', 'PUERTO_WILCHES', 'RIONEGRO',
        'SABANA_DE_TORRES', 'SANTA_BARBARA', 'SANTA_HELENA_DEL_OPON', 'SAN_ANDRES',
        'SAN_BENITO', 'SAN_GIL', 'SAN_JOAQUIN', 'SAN_JOSE_DE_MIRANDA', 'SAN_MIGUEL',
        'SAN_VICENTE_DE_CHUCURI', 'SIMACOTA', 'SOCORRO', 'SUAITA', 'SUCRE', 'SURATA',
        'TONA', 'VALLE_DE_SAN_JOSE', 'VELEZ', 'VETAS', 'VILLANUEVA', 'ZAPATOCA'
    ];

    /**
     * Obtiene la lista de municipios disponibles en ArcGIS API
     * @return array Lista de municipios normalizados (ej: "AGUADA", "SAN_GIL")
     */
    public static function getMunicipiosFromArcgis()
    {
        try {
            $params = [
                'where' => '1=1',
                'outFields' => 'Municipio',
                'returnDistinctValues' => 'true',
                'returnGeometry' => 'false',
                'f' => 'json'
            ];

            $url = self::$apiBase . '/query?' . http_build_query($params);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode !== 200) {
                error_log("PaeArcgisMunicipios: Error HTTP {$httpCode} al obtener municipios");
                return [];
            }

            $apiData = json_decode($response, true);

            if (!$apiData || !isset($apiData['features'])) {
                error_log("PaeArcgisMunicipios: Respuesta inválida de ArcGIS");
                return [];
            }

            // Extraer municipios únicos
            $municipios = [];
            foreach ($apiData['features'] as $feature) {
                if (isset($feature['attributes']['Municipio'])) {
                    $municipio = $feature['attributes']['Municipio'];
                    if (!in_array($municipio, $municipios)) {
                        $municipios[] = $municipio;
                    }
                }
            }

            sort($municipios);
            return $municipios;

        } catch (Exception $e) {
            error_log("PaeArcgisMunicipios Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene municipios de la BD local que coincidan con ArcGIS
     * @param bool $usarEstaticos Si es true, usa la lista estática (más rápido). Default: true
     * @return array Formato: ['output' => ['valid' => true, 'municipios' => [...], 'total' => N]]
     */
    public static function getMunicipiosDisponibles($usarEstaticos = true)
    {
        try {
            // Obtener municipios de ArcGIS (usar lista estática por defecto)
            $municipiosArcgis = $usarEstaticos ? self::$municipiosArcgisEstaticos : self::getMunicipiosFromArcgis();

            if (empty($municipiosArcgis)) {
                return [
                    'output' => [
                        'valid' => false,
                        'error' => 'No se pudieron obtener municipios de ArcGIS',
                        'municipios' => [],
                        'total' => 0
                    ]
                ];
            }

            // Obtener municipios de BD local
            $db = new DbConection();
            $pdo = $db->openConect();

            $stmt = $pdo->query("
                SELECT codigo_muncipio, municipio
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
                WHERE codigo_departamento = '68'
                ORDER BY municipio
            ");
            $municipiosLocal = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            // Filtrar solo municipios que existan en ArcGIS
            $municipiosValidos = [];
            $municipiosNoDisponibles = [];

            foreach ($municipiosLocal as $munLocal) {
                $nombreNormalizado = strtoupper(str_replace(' ', '_', $munLocal['municipio']));

                if (in_array($nombreNormalizado, $municipiosArcgis)) {
                    $municipiosValidos[] = [
                        'codigo' => $munLocal['codigo_muncipio'],
                        'nombre' => $munLocal['municipio'],
                        'nombre_arcgis' => $nombreNormalizado,
                        'disponible' => true
                    ];
                } else {
                    $municipiosNoDisponibles[] = [
                        'codigo' => $munLocal['codigo_muncipio'],
                        'nombre' => $munLocal['municipio'],
                        'disponible' => false
                    ];
                }
            }

            return [
                'output' => [
                    'valid' => true,
                    'municipios' => $municipiosValidos,
                    'municipios_no_disponibles' => $municipiosNoDisponibles,
                    'total' => count($municipiosValidos),
                    'total_arcgis' => count($municipiosArcgis),
                    'total_local' => count($municipiosLocal)
                ]
            ];

        } catch (Exception $e) {
            error_log("PaeArcgisMunicipios Error: " . $e->getMessage());
            return [
                'output' => [
                    'valid' => false,
                    'error' => $e->getMessage(),
                    'municipios' => [],
                    'total' => 0
                ]
            ];
        }
    }

    /**
     * Verifica si un municipio específico está disponible en ArcGIS
     * @param string $codigoMunicipio Código DANE del municipio
     * @return bool
     */
    public static function esMunicipioDisponible($codigoMunicipio)
    {
        if ($codigoMunicipio === 'todos') {
            return true;
        }

        $resultado = self::getMunicipiosDisponibles();

        if (!$resultado['output']['valid']) {
            return false;
        }

        foreach ($resultado['output']['municipios'] as $municipio) {
            if ($municipio['codigo'] === $codigoMunicipio) {
                return true;
            }
        }

        return false;
    }
}
