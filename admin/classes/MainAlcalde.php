<?php

/**
 * Clase MainAlcalde
 * Adaptación de la clase Main para trabajar con visitas del Alcalde
 * Usa tbl_visitas_alcalde y trabaja con veredas en lugar de provincias
 */
class MainAlcalde
{
    /**
     * Obtiene el total de visitas del Alcalde
     */
    public static function getSoloVisitas($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT COUNT(*) FROM " . $db->getTable('tbl_visitas_alcalde') . " WHERE tipo_registro='Visita'";
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

    /**
     * Obtiene el total de municipios/veredas visitados
     * Para Alcalde: cuenta veredas de su municipio
     * Para SuperAdmin: cuenta municipios del departamento
     */
    public static function getSoloMunicipiosVisitados($rqst)
    {
        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once 'SessionData.php';
        require_once 'Util.php';

        $municipioAlcalde = SessionData::getCodigoMunicipio();
        $departamentoCodigo = Util::getDepartamentoPrincipal();

        // Log para depuración
        error_log("MainAlcalde::getSoloMunicipiosVisitados - Municipio del alcalde: " . var_export($municipioAlcalde, true));

        $db = new DbConection();
        $pdo = $db->openConect();

        // Si NO hay municipio del alcalde = SUPERADMIN: contar MUNICIPIOS del departamento
        if (empty($municipioAlcalde)) {
            error_log("MainAlcalde::getSoloMunicipiosVisitados - Modo SuperAdmin: contando municipios del departamento");

            // Contar municipios visitados del departamento
            $qVisitadas = "SELECT COUNT(DISTINCT va.tbl_municipio_id)
                           FROM " . $db->getTable('tbl_visitas_alcalde') . " va
                           INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                             ON va.tbl_municipio_id = c.codigo_muncipio
                           WHERE va.tipo_registro='Visita'
                             AND va.tbl_municipio_id IS NOT NULL
                             AND c.codigo_departamento = :codigo_departamento";

            $stmtVisitadas = $pdo->prepare($qVisitadas);
            $stmtVisitadas->bindValue(':codigo_departamento', $departamentoCodigo);
            $stmtVisitadas->execute();
            $municipiosVisitados = $stmtVisitadas->fetchColumn();

            error_log("MainAlcalde::getSoloMunicipiosVisitados - Municipios visitados del departamento: " . $municipiosVisitados);

            // Contar total de municipios del departamento
            $qTotal = "SELECT COUNT(*)
                       FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
                       WHERE codigo_departamento = :codigo_departamento";

            $stmt = $pdo->prepare($qTotal);
            $stmt->bindValue(':codigo_departamento', $departamentoCodigo);
            $stmt->execute();
            $totalMunicipios = $stmt->fetchColumn();

            error_log("MainAlcalde::getSoloMunicipiosVisitados - Total municipios del departamento: " . $totalMunicipios);

            // Calcular municipios restantes
            $municipiosRestantes = $totalMunicipios - $municipiosVisitados;

            error_log("MainAlcalde::getSoloMunicipiosVisitados - Municipios restantes: " . $municipiosRestantes);

            $arrjson = [
                'output' => [
                    'valid' => true,
                    'veredas_visitadas' => (int)$municipiosVisitados,  // Nota: mantener nombre de clave por compatibilidad
                    'veredas_totales' => (int)$totalMunicipios,
                    'veredas_restantes' => (int)$municipiosRestantes
                ]
            ];

            $db->closeConect();
            return $arrjson;
        }

        // Si SÍ hay municipio del alcalde = ALCALDE: contar VEREDAS de su municipio
        error_log("MainAlcalde::getSoloMunicipiosVisitados - Modo Alcalde: contando veredas del municipio");

        // Contar veredas visitadas DEL MUNICIPIO del alcalde (con filtro por municipio_id)
        $qVisitadas = "SELECT COUNT(DISTINCT va.tbl_vereda_id)
                       FROM " . $db->getTable('tbl_visitas_alcalde') . " va
                       INNER JOIN " . $db->getTable('tbl_vereda') . " v
                         ON va.tbl_vereda_id = v.id
                       WHERE va.tipo_registro='Visita'
                         AND va.tbl_vereda_id IS NOT NULL
                         AND v.municipio_id = :municipio_id";

        $stmtVisitadas = $pdo->prepare($qVisitadas);
        $stmtVisitadas->bindValue(':municipio_id', $municipioAlcalde);
        $stmtVisitadas->execute();
        $veredasVisitadas = $stmtVisitadas->fetchColumn();

        error_log("MainAlcalde::getSoloMunicipiosVisitados - Veredas visitadas del municipio: " . $veredasVisitadas);

        // Contar total de veredas del municipio del alcalde
        $qTotal = "SELECT COUNT(*)
                   FROM " . $db->getTable('tbl_vereda') . "
                   WHERE municipio_id = :municipio_id";

        $stmt = $pdo->prepare($qTotal);
        $stmt->bindValue(':municipio_id', $municipioAlcalde);
        $stmt->execute();
        $totalVeredas = $stmt->fetchColumn();

        error_log("MainAlcalde::getSoloMunicipiosVisitados - Total veredas del municipio: " . $totalVeredas);

        // Calcular veredas restantes
        $veredasRestantes = $totalVeredas - $veredasVisitadas;

        error_log("MainAlcalde::getSoloMunicipiosVisitados - Veredas restantes: " . $veredasRestantes);

        $arrjson = [
            'output' => [
                'valid' => true,
                'veredas_visitadas' => (int)$veredasVisitadas,
                'veredas_totales' => (int)$totalVeredas,
                'veredas_restantes' => (int)$veredasRestantes
            ]
        ];

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene visitas por mes del Alcalde
     */
    public static function getSoloVisitasPorMes($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT
                MONTHNAME(date) AS mes,
                COUNT(id) AS total
              FROM " . $db->getTable('tbl_visitas_alcalde') . "
              WHERE tipo_registro='Visita'
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

    /**
     * Obtiene el total de visitas por mes a municipios
     * Para Alcalde: filtra por su municipio
     * Para SuperAdmin: muestra todos los municipios del departamento
     * Retorna datos para 2024 y 2025
     */
    public static function getTotalVisitasPorMesAMunicipios($rqst)
    {
        $departamentoCodigo = Util::getDepartamentoPrincipal();
        $codigo_municipio = isset($rqst['codigo_municipio']) ? $rqst['codigo_municipio'] : null;
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

        // Inicializar estructura de datos
        foreach ($years as $year) {
            $dataByYear[$year] = [];
            foreach ($monthsInSpanish as $monthEnglish => $monthSpanish) {
                $dataByYear[$year][$monthSpanish] = [
                    'mes' => $monthSpanish,
                    'total_visitas' => 0
                ];
            }
        }

        // Construir consulta condicional según si hay municipio o no
        if (!empty($codigo_municipio)) {
            // ALCALDE: Filtrar por su municipio específico
            $q = "SELECT
                    DATE_FORMAT(v.date, '%M') AS mes,
                    YEAR(v.date) AS anio,
                    COUNT(v.id) AS total_visitas
                FROM " . $db->getTable('tbl_visitas_alcalde') . " v
                INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                ON c.codigo_muncipio = v.tbl_municipio_id
                WHERE c.codigo_departamento = :departamentoCodigo
                    AND v.tbl_municipio_id = :codigo_municipio
                    AND YEAR(v.date) IN (" . implode(',', $years) . ")
                    AND LOWER(v.tipo_registro) = 'visita'
                GROUP BY YEAR(v.date), MONTH(v.date)
                ORDER BY YEAR(v.date), MONTH(v.date) ASC";

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
            $stmt->bindParam(':codigo_municipio', $codigo_municipio, PDO::PARAM_STR);
        } else {
            // SUPERADMIN: Mostrar todos los municipios del departamento
            $q = "SELECT
                    DATE_FORMAT(v.date, '%M') AS mes,
                    YEAR(v.date) AS anio,
                    COUNT(v.id) AS total_visitas
                FROM " . $db->getTable('tbl_visitas_alcalde') . " v
                INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                ON c.codigo_muncipio = v.tbl_municipio_id
                WHERE c.codigo_departamento = :departamentoCodigo
                    AND YEAR(v.date) IN (" . implode(',', $years) . ")
                    AND LOWER(v.tipo_registro) = 'visita'
                GROUP BY YEAR(v.date), MONTH(v.date)
                ORDER BY YEAR(v.date), MONTH(v.date) ASC";

            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
        }

        $stmt->execute();
        $arrTotalVisitasPorMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Rellenar datos
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

    /**
     * Obtiene el total de visitas por veredas (en lugar de provincias)
     * Adaptado para el contexto de Alcalde
     */
    public static function getTotalVisitasPorVeredas($rqst)
    {
        $departamentoCodigo = Util::getDepartamentoPrincipal();
        $codigo_municipio = isset($rqst['codigo_municipio']) ? $rqst['codigo_municipio'] : null;
        $years = [2024, 2025];

        $db = new DbConection();
        $pdo = $db->openConect();

        $dataByYear = [];

        // Consultar visitas por vereda y año (LEFT JOIN para incluir visitas sin vereda)
        $q = "SELECT
                COALESCE(v.nombre_vereda, 'Sin vereda') AS vereda,
                YEAR(va.date) AS anio,
                COUNT(va.id) AS total_visitas
            FROM " . $db->getTable('tbl_visitas_alcalde') . " va
            LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON va.tbl_vereda_id = v.id
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                ON c.codigo_muncipio = va.tbl_municipio_id
            WHERE c.codigo_departamento = :departamentoCodigo AND
                va.tbl_municipio_id = :codigo_municipio AND
                YEAR(va.date) IN (" . implode(',', $years) . ")
                AND LOWER(va.tipo_registro) = 'visita'
            GROUP BY COALESCE(v.nombre_vereda, 'Sin vereda'), YEAR(va.date)
            ORDER BY vereda, YEAR(va.date)";

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
        $stmt->bindParam(':codigo_municipio', $codigo_municipio, PDO::PARAM_STR);
        $stmt->execute();
        $arrVisitasPorVereda = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener lista única de veredas
        $veredasUnicas = [];
        foreach ($arrVisitasPorVereda as $row) {
            $veredasUnicas[$row['vereda']] = true;
        }

        // Inicializar estructura por año con todas las veredas
        foreach ($years as $year) {
            $dataByYear[$year] = [];
            foreach (array_keys($veredasUnicas) as $vereda) {
                $dataByYear[$year][$vereda] = [
                    'vereda' => $vereda,
                    'total_visitas' => 0
                ];
            }
        }

        // Rellenar datos
        foreach ($arrVisitasPorVereda as $row) {
            $anio = $row['anio'];
            $vereda = $row['vereda'];
            $totalVisitas = (int)$row['total_visitas'];

            if (isset($dataByYear[$anio][$vereda])) {
                $dataByYear[$anio][$vereda]['total_visitas'] = $totalVisitas;
            }
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

    /**
     * Obtiene el total de visitas por veredas por años
     * Similar a getTotalVisitasPorProvinciasPorAnios pero adaptado para veredas
     */
    public static function getTotalVisitasPorVeredasPorAnios($rqst)
    {
        return self::getTotalVisitasPorVeredas($rqst);
    }

    /**
     * Obtiene el total de visitas por municipios (para SuperAdmin)
     * Retorna visitas agrupadas por municipio en lugar de veredas
     */
    public static function getTotalVisitasPorMunicipios($rqst)
    {
        $departamentoCodigo = Util::getDepartamentoPrincipal();
        $years = [2024, 2025];

        $db = new DbConection();
        $pdo = $db->openConect();

        $dataByYear = [];

        // Consultar visitas por municipio y año
        $q = "SELECT
                c.municipio,
                YEAR(va.date) AS anio,
                COUNT(va.id) AS total_visitas
            FROM " . $db->getTable('tbl_visitas_alcalde') . " va
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                ON c.codigo_muncipio = va.tbl_municipio_id
            WHERE c.codigo_departamento = :departamentoCodigo AND
                YEAR(va.date) IN (" . implode(',', $years) . ")
                AND LOWER(va.tipo_registro) = 'visita'
            GROUP BY c.municipio, YEAR(va.date)
            ORDER BY c.municipio, YEAR(va.date)";

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
        $stmt->execute();
        $arrVisitasPorMunicipio = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener lista única de municipios
        $municipiosUnicos = [];
        foreach ($arrVisitasPorMunicipio as $row) {
            $municipiosUnicos[$row['municipio']] = true;
        }

        // Inicializar estructura por año con todos los municipios
        foreach ($years as $year) {
            $dataByYear[$year] = [];
            foreach (array_keys($municipiosUnicos) as $municipio) {
                $dataByYear[$year][$municipio] = [
                    'municipio' => $municipio,
                    'total_visitas' => 0
                ];
            }
        }

        // Rellenar datos
        foreach ($arrVisitasPorMunicipio as $row) {
            $anio = $row['anio'];
            $municipio = $row['municipio'];
            $totalVisitas = (int)$row['total_visitas'];

            if (isset($dataByYear[$anio][$municipio])) {
                $dataByYear[$anio][$municipio]['total_visitas'] = $totalVisitas;
            }
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

    /**
     * Obtiene datos principales del dashboard del Alcalde
     */
    public static function getDataMain($rqst)
    {
        $totalVisitas = self::getSoloVisitas($rqst);
        $municipiosVisitados = self::getSoloMunicipiosVisitados($rqst);

        $arrjson = [
            'output' => [
                'valid' => true,
                'visitas' => $totalVisitas['output']['total_visitas'],
                'municipios_visitados' => $municipiosVisitados['output']['municipios_visitados'],
                'municipios_restantes' => 87 - $municipiosVisitados['output']['municipios_visitados']
            ]
        ];

        return $arrjson;
    }
}
