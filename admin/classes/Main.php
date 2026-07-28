<?php
// Obteniendo la fecha actual con hora, minutos y segundos en PHP
$fechaActual = date('d-m-Y H:i:s');
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Main
{
    public function __construct() {}

    /**
     * Metodo para recuperar todos los registros
     * (NO SE MODIFICA)
     */
    public static function getDataMain($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        // Inicialización de variables
        $lideres = 0;
        $visitas = 0;
        $municipios = 0;
        $veredas = 0;
        $visitasmun = 0;
        $provincia = 0;
        $inversionsec = 0;
        $valorproyectos = 0;
        $compromiso = 0;

        // Consulta 1: Total de líderes
        $q1 = "SELECT COUNT(id) AS cuenta_lideres FROM " . $db->getTable('tbl_lideres');
        $lideres = $pdo->query($q1)->fetchColumn();

        // Consulta 2: Total de visitas realizadas
        $q2 = "SELECT COUNT(*) AS cuenta_visitas FROM " . $db->getTable('tbl_visitas') . " WHERE tipo_registro = 'visita' ;";
        $visitas = $pdo->query($q2)->fetchColumn();
        $compromiso = $visitas;

        // Consulta 3: Total de municipios
        $q3 = "SELECT COUNT(DISTINCT tbl_municipio_id) AS total_municipios FROM " . $db->getTable('tbl_visitas');
        $municipios = $pdo->query($q3)->fetchColumn();

        // Consulta 4: Provincias
        $q4 = "SELECT COUNT(DISTINCT provincia) AS total_provincias FROM " . $db->getTable('tbl_visitas');
        $provincia = $pdo->query($q4)->fetchColumn();

        // Consulta 5: Inversión secretaría (promedio por secretaría activa)
        $q5 = "SELECT COALESCE(SUM(valor_proyecto) / NULLIF(COUNT(DISTINCT tbl_secretarias_id), 0), 0) AS inversionsec
                FROM " . $db->getTable('tbl_proyectos') . " 
                INNER JOIN " . $db->getTable('tbl_secretarias') . " 
                ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id";
        $inversionsec = $pdo->query($q5)->fetchColumn();

        // Consulta 6: Valor total proyectos
        $q6 = "SELECT SUM(valor_proyecto) AS valorproyectos FROM " . $db->getTable('tbl_proyectos');
        $valorproyectos = $pdo->query($q6)->fetchColumn();

        // Consulta 7: Secretarías activas (con proyectos)
        $q7 = "SELECT COUNT(DISTINCT tbl_secretarias_id) AS secretarias_activas
               FROM " . $db->getTable('tbl_proyectos') . "
               WHERE tbl_secretarias_id IS NOT NULL AND tbl_secretarias_id > 0";
        $secretaria = (int)$pdo->query($q7)->fetchColumn();

        // Porcentajes
        $porcentaje_veredas = ($veredas * 100 / 34792);
        $porcentaje_municipios = ($municipios * 100 / 1103);

        $arrjson = array(
        'output' => array(
            'valid' => true,
            'lideres' => $lideres,
            'visitas' => $visitas,
            'visitasmun' => $visitasmun,
            'provincia' => $provincia,
            'inversionsec' => $inversionsec,
            'valorproyectos' => $valorproyectos,
            'secretaria' => $secretaria,
            'compromiso' => $compromiso,
            'municipios' => $municipios,
            'veredas' => $veredas,
            'porcentaje_veredas' => $porcentaje_veredas,
            'porcentaje_municipios' => $porcentaje_municipios
        ));

        $db->closeConect();
        return $arrjson;
    }

    //Total solo de visitas
    public static function getSoloVisitas($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT COUNT(*) FROM " . $db->getTable('tbl_visitas') . " WHERE tipo_registro='visita'";
        $total = $pdo->query($q)->fetchColumn();

        $arrjson = [
            'output' => [
                'valid' => true,
                'total_visitas' => (int)$total
            ]
        ];

        $db->closeConect();
        return $arrjson;
    }

    //Total de municipios visitados solo con visitas 
    public static function getSoloMunicipiosVisitados($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT COUNT(DISTINCT tbl_municipio_id) 
              FROM " . $db->getTable('tbl_visitas') . " 
              WHERE tipo_registro='visita'";

        $total = $pdo->query($q)->fetchColumn();

        $arrjson = [
            'output' => [
                'valid' => true,
                'municipios_visitados' => (int)$total
            ]
        ];

        $db->closeConect();
        return $arrjson;
    }

    //Visitas por mes solo con visitas 
    public static function getSoloVisitasPorMes($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT 
                MONTHNAME(date) AS mes,
                COUNT(id) AS total
              FROM " . $db->getTable('tbl_visitas') . "
              WHERE tipo_registro='visita'
              GROUP BY MONTH(date)
              ORDER BY MONTH(date)";

        $data = $pdo->query($q)->fetchAll(PDO::FETCH_ASSOC);

        $arrjson = [
            'output' => [
                'valid' => true,
                'response' => $data
            ]
        ];

        $db->closeConect();
        return $arrjson;
    }

    // Visitas agrupadas por año-mes (últimos meses con datos reales)
    public static function getVisitasUltimosMeses($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT DATE_FORMAT(STR_TO_DATE(date, '%Y-%m-%d'), '%Y-%m') AS mes,
                     COUNT(id) AS total
              FROM " . $db->getTable('tbl_visitas') . "
              WHERE tipo_registro = 'visita'
                AND date IS NOT NULL AND date != ''
              GROUP BY DATE_FORMAT(STR_TO_DATE(date, '%Y-%m-%d'), '%Y-%m')
              ORDER BY MIN(STR_TO_DATE(date, '%Y-%m-%d')) ASC";

        $data = $pdo->query($q)->fetchAll(PDO::FETCH_ASSOC);

        $meses = [];
        $valores = [];
        foreach ($data as $r) {
            $meses[] = $r['mes'];
            $valores[] = (int)$r['total'];
        }

        $offset = max(0, count($meses) - 6);
        $visitasSerie = array_slice($valores, $offset);
        $mesesSerie = array_map(function($m) {
            $parts = explode('-', $m);
            $anio = substr($parts[0], 2);
            $mesNum = (int)$parts[1];
            $nombres = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            return $nombres[$mesNum] . "'" . $anio;
        }, array_slice($meses, $offset));

        $db->closeConect();

        return [
            'output' => [
                'valid' => true,
                'valores' => $visitasSerie,
                'etiquetas' => $mesesSerie
            ]
        ];
    }

    public static function getTotalVisitasPorMesAMunicipios($rqst)
    {
        $departamentoCodigo = Util::getDepartamentoPrincipal();
        $years = [2024, 2025];

        $db = new DbConection();
        $pdo = $db->openConect();

        $dataByYear = [];
        $monthsInSpanish = [
        "January" => "Enero",
        "February" => "Febrero",
        "March" => "Marzo",
        "April" => "Abril",
        "May" => "Mayo",
        "June" => "Junio",
        "July" => "Julio",
        "August" => "Agosto",
        "September" => "Septiembre",
        "October" => "Octubre",
        "November" => "Noviembre",
        "December" => "Diciembre"
        ];

        foreach ($years as $year) {
            $dataByYear[$year] = [];
            foreach ($monthsInSpanish as $monthEnglish => $monthSpanish) {
                $dataByYear[$year][$monthSpanish] = [
                    'mes' => $monthSpanish,
                    'total_visitas' => 0
                ];
            }
        }

        $q = "SELECT 
                DATE_FORMAT(v.date, '%M') AS mes, 
                YEAR(v.date) AS anio, 
                COUNT(v.id) AS total_visitas
            FROM " . $db->getTable('tbl_visitas') . " v
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c 
            ON c.codigo_muncipio = v.tbl_municipio_id
            WHERE c.codigo_departamento = :departamentoCodigo 
                AND YEAR(v.date) IN (" . implode(',', $years) . ")
                AND LOWER(v.tipo_registro) = 'visita'
            GROUP BY YEAR(v.date), MONTH(v.date)
            ORDER BY YEAR(v.date), MONTH(v.date) ASC";

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
        $stmt->execute();
        $arrTotalVisitasPorMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($arrTotalVisitasPorMes as $row) {
            $anio = $row['anio'];
            $mesInEnglish = $row['mes'];
            $totalVisitas = (int)$row['total_visitas'];

            $mesEnEspañol = isset($monthsInSpanish[$mesInEnglish]) ? $monthsInSpanish[$mesInEnglish] : $mesInEnglish;
            $dataByYear[$anio][$mesEnEspañol]['total_visitas'] = $totalVisitas;
        }

        $response = [];
        foreach ($years as $year) {
            $response[$year] = array_values($dataByYear[$year]);
        }

        $arrjson = [
            'output' => [
                'valid' => true,
                'response' => $response,
            ]
        ];

        $db->closeConect();
        return $arrjson;
    }


    public static function getTotalVisitasPorProvincias($rqst)
    {
        $departamentoCodigo = Util::getDepartamentoPrincipal();
        $year = Util::getAnioActual();

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT 
            DATE_FORMAT(v.date, '%M') AS mes, 
            v.provincia, 
            COUNT(v.id) AS total_visitas
        FROM " . $db->getTable('tbl_visitas') . " v
        WHERE v.tbl_departamento_id = :departamentoCodigo 
            AND YEAR(v.date) = :year
        GROUP BY v.provincia
        ORDER BY v.provincia";

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        $arrTotalVisitasProvincia = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $arrjson = [
            'output' => [
                'valid' => true,
                'response' => $arrTotalVisitasProvincia,
            ]
        ];

        $db->closeConect();
        return $arrjson;
    }


    public static function getTotalVisitasPorProvinciasPorAnios($rqst)
    {
        $departamentoCodigo = Util::getDepartamentoPrincipal();
        $db = new DbConection();
        $pdo = $db->openConect();

        $qProvincias = "SELECT DISTINCT provincia 
                        FROM " . $db->getTable('tbl_visitas') . " 
                        WHERE tbl_departamento_id = :departamentoCodigo";
        $stmtProvincias = $pdo->prepare($qProvincias);
        $stmtProvincias->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
        $stmtProvincias->execute();
        $provincias = $stmtProvincias->fetchAll(PDO::FETCH_COLUMN);

        $years = [2024, 2025];
        $dataByYear = [];

        foreach ($years as $year) {
            $dataByYear[$year] = [];
            foreach ($provincias as $provincia) {
                $dataByYear[$year][$provincia] = [
                    'provincia' => $provincia,
                    'total_visitas' => 0
                ];
            }
        }

        $q = "SELECT 
                v.provincia, 
                YEAR(v.date) AS anio,
                COUNT(v.id) AS total_visitas
            FROM " . $db->getTable('tbl_visitas') . " v
            WHERE v.tbl_departamento_id = :departamentoCodigo 
              AND v.provincia IS NOT NULL
              AND YEAR(v.date) IN (" . implode(',', $years) . ")
              AND LOWER(v.tipo_registro) = 'visita'
            GROUP BY v.provincia, YEAR(v.date)
            ORDER BY v.provincia, YEAR(v.date)";

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
        $stmt->execute();
        $arrTotalVisitasProvincia = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($arrTotalVisitasProvincia as $row) {
            $provincia = $row['provincia'];
            $anio = $row['anio'];
            if (isset($dataByYear[$anio][$provincia])) {
                $dataByYear[$anio][$provincia]['total_visitas'] = (int)$row['total_visitas'];
            }
        }

        $response = [];
        foreach ($years as $year) {
            $response[$year] = array_values($dataByYear[$year]);
        }

        foreach ($response as &$yearData) {
            foreach ($yearData as &$provinciaData) {
                $nombre_provincia = $provinciaData['provincia'];
                $nombre_provincia_corregido = iconv('UTF-8', 'ISO-8859-1//IGNORE', $nombre_provincia);
                $nombre_provincia_corregido = iconv('ISO-8859-1', 'UTF-8//IGNORE', $nombre_provincia_corregido);
                $provinciaData['provincia'] = str_replace('_', ' ', $nombre_provincia_corregido);
            }
        }

        $arrjson = [
            'output' => [
                'valid' => true,
                'response' => $response
            ]
        ];

        $db->closeConect();
        return $arrjson;
    }
    /**
     * Retorna los 4 indicadores PAE desde ArcGIS Online para todos los municipios:
     * - Sedes       = total de caracterizaciones (features)
     * - Rural       = sedes en zona rural
     * - Urbana      = sedes en zona urbana
     * - Niños       = total niños focalizados (Total_Alimentacion)
     *
     * @param  mixed $rqst  (no requerido para esta consulta)
     * @return array         JSON estándar con 'sedes', 'rural', 'urbana', 'ninos'
     */
    public static function getResumenPaeArcgis($rqst)
    {
        try {
            if (!class_exists('PaeArcgis')) {
                require_once __DIR__ . '/PaeArcgis.php';
            }

            $arr = PaeArcgis::getDataFromArcgis(['codigoMunicipio' => 'todos']);

            if (empty($arr['output']['valid'])) {
                return [
                    'output' => [
                        'valid'  => false,
                        'sedes'  => 0,
                        'rural'  => 0,
                        'urbana' => 0,
                        'ninos'  => 0,
                    ]
                ];
            }

            return [
                'output' => [
                    'valid'  => true,
                    'sedes'  => (int)($arr['output']['caracterizaciones'] ?? 0),
                    'rural'  => (int)($arr['output']['zona_rural']        ?? 0),
                    'urbana' => (int)($arr['output']['zona_urbana']       ?? 0),
                    'ninos'  => (int)($arr['output']['ninos_foc']         ?? 0),
                ]
            ];

        } catch (Exception $e) {
            error_log('[Main::getResumenPaeArcgis] ' . $e->getMessage());
            return [
                'output' => [
                    'valid'  => false,
                    'sedes'  => 0,
                    'rural'  => 0,
                    'urbana' => 0,
                    'ninos'  => 0,
                ]
            ];
        }
    }

    /**
     * Retorna los valores de los factores de seguridad calculados igual que dash_interior.php:
     * - Sicariato      = S/DER POLITICO + DESAN  (serie_anio_2 del dataset 'sicariato')
     * - Intolerancia   = S/DER POLITICO           (serie_anio_2 del dataset 'intolerancia')
     * - Sin homicidios = valor directo de tbl_dash_factor
     *
     * @param  mixed $rqst  (no requerido para esta consulta)
     * @return array         JSON estándar con 'sicariato', 'intolerancia', 'sin_homicidios'
     */
    public static function getFactoresSeguridad($rqst)
    {
        $db  = new DbConection();
        $pdo = $db->openConect();

        // Año activo
        $anio = (int)$pdo->query(
            "SELECT anio_2 FROM " . $db->getTable('tbl_dash_interior_meta') . " WHERE id=1 LIMIT 1"
        )->fetchColumn();
        if (!$anio) $anio = (int)date('Y');

        // Helper: busca el índice de una categoría por nombre (coincidencia normalizada)
        $findIdx = function(array $cats, string $needle): int {
            $norm = function($s) {
                $s = mb_strtolower(trim($s), 'UTF-8');
                return str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
            };
            $n = $norm($needle);
            foreach ($cats as $i => $c) {
                if ($norm($c) === $n) return $i;
            }
            foreach ($cats as $i => $c) {
                if (strpos($norm($c), $n) !== false) return $i;
            }
            return -1;
        };

        // Helper: recupera categorías y serie_anio_2 de un boletin_key
        $getSerieAnio2 = function(string $cardKey) use ($pdo, $db, $anio): array {
            $stmt = $pdo->prepare(
                "SELECT id FROM " . $db->getTable('tbl_dash_boletin') . " WHERE card_key = ? AND activo = 1 LIMIT 1"
            );
            $stmt->execute([$cardKey]);
            $boletinId = (int)$stmt->fetchColumn();
            if (!$boletinId) return ['cats' => [], 'serie' => []];

            $cats = $pdo->prepare(
                "SELECT id, nombre FROM " . $db->getTable('tbl_dash_boletin_categoria') . "
                 WHERE boletin_id = ? ORDER BY orden ASC, id ASC"
            );
            $cats->execute([$boletinId]);
            $catRows = $cats->fetchAll(PDO::FETCH_ASSOC);

            $catIds   = array_map(fn($c) => (int)$c['id'],     $catRows);
            $catNames = array_map(fn($c) => (string)$c['nombre'], $catRows);
            $serie    = array_fill(0, count($catIds), 0);

            if (!empty($catIds)) {
                $in   = implode(',', array_fill(0, count($catIds), '?'));
                $vals = $pdo->prepare(
                    "SELECT categoria_id, valor
                     FROM " . $db->getTable('tbl_dash_boletin_valor') . "
                     WHERE boletin_id = ? AND anio = ? AND categoria_id IN ($in)"
                );
                $vals->execute(array_merge([$boletinId, $anio], $catIds));
                $map = [];
                foreach ($vals->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $map[(int)$r['categoria_id']] = (int)$r['valor'];
                }
                foreach ($catIds as $i => $cid) { $serie[$i] = $map[$cid] ?? 0; }
            }

            return ['cats' => $catNames, 'serie' => $serie];
        };

        // --- Sicariato = S/DER POLITICO + DESAN ---
        $dsSic  = $getSerieAnio2('sicariato');
        $iPol   = $findIdx($dsSic['cats'], 'S/DER POLITICO');
        $iDes   = $findIdx($dsSic['cats'], 'DESAN');
        $valSic = ($iPol >= 0 ? (int)($dsSic['serie'][$iPol] ?? 0) : 0)
                + ($iDes >= 0 ? (int)($dsSic['serie'][$iDes] ?? 0) : 0);

        // --- Intolerancia = S/DER POLITICO ---
        $dsInt  = $getSerieAnio2('intolerancia');
        $iPolI  = $findIdx($dsInt['cats'], 'S/DER POLITICO');
        $valInt = $iPolI >= 0 ? (int)($dsInt['serie'][$iPolI] ?? 0) : 0;

        // --- Sin homicidios = campo municipios_sin_homicidios de tbl_dash_interior_meta ---
        $valSH = (int)($pdo->query(
            "SELECT municipios_sin_homicidios FROM " . $db->getTable('tbl_dash_interior_meta') . " WHERE id=1 LIMIT 1"
        )->fetchColumn() ?: 0);

        $db->closeConect();

        return [
            'output' => [
                'valid'          => true,
                'anio'           => $anio,
                'sicariato'      => $valSic,
                'intolerancia'   => $valInt,
                'sin_homicidios' => $valSH,
            ]
        ];
    }

    /**
     * Retorna el total de registros con tipo_registro = 'COMPROMISO'.
     *
     * @param  mixed $rqst  (no requerido para esta consulta)
     * @return array         JSON estándar con 'total_compromisos'
     */
    public static function getTotalCompromisos($rqst)
    {
        $db  = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT COUNT(*) AS total_compromisos
              FROM " . $db->getTable('tbl_visitas') . "
              WHERE tipo_registro = 'COMPROMISO'";

        $total = (int) $pdo->query($q)->fetchColumn();

        $db->closeConect();

        return [
            'output' => [
                'valid'             => true,
                'total_compromisos' => $total,
            ]
        ];
    }

    /**
     * Retorna el total de compromisos con estado 'SIN CUMPLIR'
     * registrados en tbl_visitas con tipo_registro = 'COMPROMISO'.
     *
     * @param  mixed $rqst  (no requerido para esta consulta)
     * @return array         JSON estándar con 'total_sin_cumplir'
     */
    public static function getTotalCompromisosSinCumplir($rqst)
    {
        $db  = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT COUNT(*) AS total_sin_cumplir
              FROM " . $db->getTable('tbl_visitas') . "
              WHERE tipo_registro = 'COMPROMISO'
                AND estado = 'SIN CUMPLIR'";

        $total = (int) $pdo->query($q)->fetchColumn();

        $db->closeConect();

        return [
            'output' => [
                'valid'              => true,
                'total_sin_cumplir'  => $total,
            ]
        ];
    }

    public static function getPromedioPs2025PorSecretaria($rqst)
    {
        $departamentoCodigo = Util::getDepartamentoPrincipal();
        $db = new DbConection();
        $pdo = $db->openConect();

        $qSecretarias = "SELECT DISTINCT s.secretaria 
                        FROM " . $db->getTable('tbl_secretarias') . " s
                        JOIN " . $db->getTable('tbl_plandesarrollo') . " p 
                        ON p.tbl_secretaria_id = s.id";
        $stmtSecretarias = $pdo->prepare($qSecretarias);
        $stmtSecretarias->execute();
        $secretarias = $stmtSecretarias->fetchAll(PDO::FETCH_COLUMN);

        $dataBySecretaria = [];
        foreach ($secretarias as $secretaria) {
            $dataBySecretaria[$secretaria] = [
                'nombre_secretaria' => $secretaria,
                'total_ps2025' => 0,
                'promedio_ps2025' => 0
            ];
        }

        $q = "SELECT 
                s.secretaria AS nombre_secretaria,
                SUM(COALESCE(NULLIF(p.ps2025, ''), 0)) AS total_ps2025,
                ROUND(AVG(COALESCE(NULLIF(p.ps2025, ''), 0)), 2) AS promedio_ps2025
            FROM " . $db->getTable('tbl_plandesarrollo') . " p
            JOIN " . $db->getTable('tbl_secretarias') . " s 
            ON p.tbl_secretaria_id = s.id
            GROUP BY s.secretaria
            ORDER BY promedio_ps2025 DESC";

        $stmt = $pdo->prepare($q);
        $stmt->execute();
        $arrPromedioPs2025 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($arrPromedioPs2025 as $row) {
            $secretaria = $row['nombre_secretaria'];
            if (isset($dataBySecretaria[$secretaria])) {
                $dataBySecretaria[$secretaria]['total_ps2025'] = (int)$row['total_ps2025'];
                $dataBySecretaria[$secretaria]['promedio_ps2025'] = (float)$row['promedio_ps2025'];
            }
        }

        $response = array_values($dataBySecretaria);

        $arrjson = [
            'output' => [
                'valid' => true,
                'response' => $response
            ]
        ];

        $db->closeConect();
        return $arrjson;
    }
    
    
}
