<?php

class FactoresInestabilidadGeneral
{
    private const CODIGO_TODOS_INESTABILIDAD = 10000;
    public const TIPO_PUNTAJE_INICIAL = 1;
    public const TIPO_PUNTAJE_FINAL = 2;

    public function __construct() {}

    private static function esTodosLosFactores(int $inestabilidadId): bool
    {
        return $inestabilidadId === self::CODIGO_TODOS_INESTABILIDAD;
    }

    private static function dbGetRows(string $query, bool $single = false): array
    {
        $result = Util::sb_db_get($query, $single);
        if (isset($result['output']['valid']) && $result['output']['valid'] === false) {
            return [];
        }
        return is_array($result) ? $result : [];
    }

    private static function sqlCondicionInestabilidad(int $inestabilidadId, string $alias = 'tbl_factores'): string
    {
        if (self::esTodosLosFactores($inestabilidadId)) {
            return "$alias.tbl_factor_inestabilidad_id IS NOT NULL";
        }
        return "$alias.tbl_factor_inestabilidad_id = $inestabilidadId";
    }

    private static function sqlPuntajeEfectivoActual(
        string $aliasIngreso = 't_ingreso',
        string $aliasFactor = 't_fact',
        string $aliasActual = 't_actual'
    ): string {
        return "CASE
            WHEN $aliasIngreso.valor IS NULL OR $aliasIngreso.valor = 0 THEN 0
            ELSE (COALESCE($aliasActual.valor_actualizacion, $aliasIngreso.valor) / $aliasIngreso.valor)
                 * COALESCE($aliasFactor.puntaje, 0)
        END";
    }

    private static function colorNeutroDesdePuntajes(array $puntajes, string $colorDefecto): string
    {
        foreach ($puntajes as $puntaje) {
            $nombre = strtolower(trim($puntaje['name'] ?? ''));
            if ($nombre === 'neutro') {
                return $puntaje['color'] ?? $colorDefecto;
            }
        }
        foreach ($puntajes as $puntaje) {
            $desde = (float) ($puntaje['rango_desde'] ?? 0);
            $hasta = (float) ($puntaje['rango_hasta'] ?? 0);
            if ($desde <= 0 && $hasta <= 0) {
                return $puntaje['color'] ?? $colorDefecto;
            }
        }
        return $colorDefecto;
    }

    private static function rangosActivosPuntajes(array $puntajes): array
    {
        $rangos = [];
        foreach ($puntajes as $puntaje) {
            $nombre = strtolower(trim($puntaje['name'] ?? ''));
            if ($nombre === 'neutro') {
                continue;
            }
            $desde = (float) ($puntaje['rango_desde'] ?? 0);
            $hasta = (float) ($puntaje['rango_hasta'] ?? 0);
            if ($desde <= 0 && $hasta <= 0) {
                continue;
            }
            $rangos[] = [
                'desde' => $desde,
                'hasta' => $hasta,
                'color' => $puntaje['color'] ?? Util::getColorNeutroMapa(),
            ];
        }
        usort($rangos, fn($a, $b) => $a['desde'] <=> $b['desde']);
        return $rangos;
    }

    private static function colorDesdePuntajes(float $cantidad, array $puntajes, string $colorDefecto): string
    {
        $cantidad = round($cantidad, 4);

        if ($cantidad <= 0) {
            return self::colorNeutroDesdePuntajes($puntajes, $colorDefecto);
        }

        foreach ($puntajes as $puntaje) {
            $nombre = strtolower(trim($puntaje['name'] ?? ''));
            if ($nombre === 'neutro') {
                continue;
            }
            $desde = (float) ($puntaje['rango_desde'] ?? 0);
            $hasta = (float) ($puntaje['rango_hasta'] ?? 0);
            if ($cantidad >= $desde && $cantidad <= $hasta) {
                return $puntaje['color'];
            }
        }

        $rangos = self::rangosActivosPuntajes($puntajes);
        if (empty($rangos)) {
            return self::colorNeutroDesdePuntajes($puntajes, $colorDefecto);
        }

        $ultimo = $rangos[count($rangos) - 1];
        if ($cantidad > $ultimo['hasta']) {
            return $ultimo['color'];
        }

        foreach ($rangos as $i => $rango) {
            $siguienteDesde = isset($rangos[$i + 1]) ? $rangos[$i + 1]['desde'] : null;
            if ($cantidad >= $rango['desde'] && ($siguienteDesde === null || $cantidad < $siguienteDesde)) {
                return $rango['color'];
            }
        }

        return self::colorNeutroDesdePuntajes($puntajes, $colorDefecto);
    }

    public static function resolverColorPuntaje(float $cantidad, array $puntajes, ?string $colorDefecto = null): string
    {
        return self::colorDesdePuntajes($cantidad, $puntajes, $colorDefecto ?? Util::getColorNeutroMapa());
    }

    public static function etiquetasPorColorDesdeBadges(array $badges): array
    {
        $map = [strtolower(trim(Util::getColorNeutroMapa())) => 'Neutro'];
        foreach ($badges as $nombre => $cfg) {
            $color = strtolower(trim($cfg['bg'] ?? ''));
            if ($color !== '') {
                $map[$color] = $nombre;
            }
        }
        return $map;
    }

    public static function resumenColoresMapa(array $items, string $campoColor, array $etiquetasPorColor = []): array
    {
        $conteo = [];
        $total = 0;
        $neutro = strtolower(trim(Util::getColorNeutroMapa()));

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $color = strtolower(trim($item[$campoColor] ?? ''));
            if ($color === '') {
                $color = $neutro;
            }
            if (!isset($conteo[$color])) {
                $conteo[$color] = 0;
            }
            $conteo[$color]++;
            $total++;
        }

        $grupos = [];
        foreach ($conteo as $color => $cantidad) {
            $grupos[] = [
                'color' => $color,
                'etiqueta' => $etiquetasPorColor[$color] ?? 'Otros',
                'cantidad' => $cantidad,
                'porcentaje' => $total > 0 ? round($cantidad * 100 / $total, 1) : 0,
            ];
        }

        usort($grupos, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

        return ['total' => $total, 'grupos' => $grupos];
    }

    public static function renderResumenColoresMapa(array $resumen, string $unidad): string
    {
        if (($resumen['total'] ?? 0) <= 0 || empty($resumen['grupos'])) {
            return '';
        }

        $unidad = htmlspecialchars($unidad, ENT_QUOTES, 'UTF-8');
        $html = '<div class="mapa-resumen-colores">';
        $html .= '<div class="mapa-resumen-colores-total">Total: ' . intval($resumen['total']) . ' ' . $unidad . '</div>';
        $html .= '<div class="mapa-resumen-colores-grid">';

        foreach ($resumen['grupos'] as $grupo) {
            $color = htmlspecialchars($grupo['color'], ENT_QUOTES, 'UTF-8');
            $etiqueta = htmlspecialchars($grupo['etiqueta'], ENT_QUOTES, 'UTF-8');
            $cantidad = intval($grupo['cantidad']);
            $porcentaje = number_format((float) $grupo['porcentaje'], 1, '.', '');

            $html .= '<div class="mapa-resumen-colores-item">';
            $html .= '<span class="mapa-resumen-dot" style="background:' . $color . ';"></span>';
            $html .= '<span class="mapa-resumen-etiqueta">' . $etiqueta . '</span>';
            $html .= '<span class="mapa-resumen-valor">' . $cantidad . ' <small>(' . $porcentaje . '%)</small></span>';
            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }

    private static function sqlJoinActualizacionIngreso($db): string
    {
        return "LEFT JOIN (
                    SELECT act.tbl_ingreso_informacion_id, act.valor_actualizacion
                    FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " act
                    INNER JOIN (
                        SELECT MAX(id) AS max_id
                        FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . "
                        GROUP BY tbl_ingreso_informacion_id
                    ) max_act ON act.id = max_act.max_id
                ) AS t_actual ON t_actual.tbl_ingreso_informacion_id = t_ingreso.id";
    }

    public static function calcularColorDelDepartamentoTodos($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : Util::getDepartamentoPrincipal();

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);
            $colorDefecto = Util::getColorNeutroMapa();

            $q = "SELECT 
                    tbl_factores.tbl_factor_inestabilidad_id AS inestabilidad_id,
                    COALESCE(SUM(tbl_factores.puntaje), 0) AS cantidad,
                    tbl_ciudades_accion_unificada.*
                FROM 
                    " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
                LEFT JOIN 
                    " . $db->getTable('tbl_ingreso_informacion') . " AS tbl_ingreso_informacion 
                    ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_ingreso_informacion.codigo_municipio
                LEFT JOIN 
                    " . $db->getTable('tbl_factores') . " AS tbl_factores 
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id 
                    AND tbl_factores.tbl_factor_inestabilidad_id IS NOT NULL
                WHERE
                    tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                GROUP BY
                    tbl_ciudades_accion_unificada.id, 
                    tbl_ciudades_accion_unificada.path, 
                    tbl_ciudades_accion_unificada.name, 
                    tbl_ciudades_accion_unificada.class, 
                    tbl_ciudades_accion_unificada.d, 
                    tbl_ciudades_accion_unificada.codigo_departamento, 
                    tbl_factores.tbl_factor_inestabilidad_id
                ORDER BY cantidad ASC";
            $municipios = Util::sb_db_get($q, false);
            $resultado = [];

            foreach ($municipios as $municipio) {
                $cantidad = $municipio['cantidad'];
                $color = $colorDefecto;

                foreach ($puntajes as $puntaje) {
                    if ($cantidad >= $puntaje['rango_desde'] && $cantidad <= $puntaje['rango_hasta']) {
                        $color = $puntaje['color'];
                        break;
                    }
                }

                $municipio['color_calculado'] = $color;
                $resultado[] = $municipio;
            }

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function calcularColorDelDepartamentoByInestabilidadId($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : Util::getDepartamentoPrincipal();
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;

        if ($codigoDepartamento == 0 || $inestabilidadId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes'), false);
            $colorDefecto = Util::getColorNeutroMapa();

            $q = "SELECT 
                    tbl_factores.tbl_factor_inestabilidad_id AS inestabilidad_id,
                    COALESCE(SUM(tbl_factores.puntaje), 0) AS cantidad,
                    tbl_ciudades_accion_unificada.*
                FROM 
                    " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
                LEFT JOIN 
                    " . $db->getTable('tbl_ingreso_informacion') . " AS tbl_ingreso_informacion 
                    ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_ingreso_informacion.codigo_municipio
                LEFT JOIN 
                    " . $db->getTable('tbl_factores') . " AS tbl_factores 
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id 
                    AND tbl_factores.tbl_factor_inestabilidad_id = $inestabilidadId
                WHERE
                    tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                GROUP BY
                    tbl_ciudades_accion_unificada.id, 
                    tbl_ciudades_accion_unificada.path, 
                    tbl_ciudades_accion_unificada.name, 
                    tbl_ciudades_accion_unificada.class, 
                    tbl_ciudades_accion_unificada.d, 
                    tbl_ciudades_accion_unificada.codigo_departamento, 
                    tbl_factores.tbl_factor_inestabilidad_id
                ORDER BY cantidad ASC";
            $municipios = Util::sb_db_get($q, false);
            $resultado = [];

            foreach ($municipios as $municipio) {
                $cantidad = $municipio['cantidad'];
                $color = $colorDefecto;

                foreach ($puntajes as $puntaje) {
                    if ($municipio['inestabilidad_id'] > 0 && $cantidad >= $puntaje['rango_desde'] && $cantidad <= $puntaje['rango_hasta']) {
                        $color = $puntaje['color'];
                        break;
                    }
                }

                $municipio['color_calculado'] = $color;
                $resultado[] = $municipio;
            }

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function calcularColorMapaInicial($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : Util::getDepartamentoPrincipal();
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $colorDefecto = Util::getColorNeutroMapa();
            $puntajes = self::getPuntajes($inestabilidadId, self::TIPO_PUNTAJE_INICIAL);
            $condicion = self::sqlCondicionInestabilidad($inestabilidadId, 'tbl_factores');

            $q = "SELECT 
                    tbl_ciudades_accion_unificada.codigo_muncipio,
                    tbl_ciudades_accion_unificada.municipio,
                    tbl_ciudades_accion_unificada.d,
                    tbl_ciudades_accion_unificada.name,
                    tbl_ciudades_accion_unificada.class,
                    tbl_ciudades_accion_unificada.path,
                    tbl_ciudades_accion_unificada.nombre_mapa,
                    tbl_ciudades_accion_unificada.codigo_departamento,
                    COALESCE(SUM(
                        CASE WHEN $condicion
                        THEN COALESCE(tbl_factores.puntaje, 0)
                        ELSE 0 END
                    ), 0) AS suma
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion') . "
                    ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades_accion_unificada.codigo_muncipio
                LEFT JOIN " . $db->getTable('tbl_factores') . "
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                WHERE tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                GROUP BY
                    tbl_ciudades_accion_unificada.id,
                    tbl_ciudades_accion_unificada.codigo_muncipio,
                    tbl_ciudades_accion_unificada.municipio,
                    tbl_ciudades_accion_unificada.d,
                    tbl_ciudades_accion_unificada.name,
                    tbl_ciudades_accion_unificada.class,
                    tbl_ciudades_accion_unificada.path,
                    tbl_ciudades_accion_unificada.nombre_mapa,
                    tbl_ciudades_accion_unificada.codigo_departamento
                ORDER BY tbl_ciudades_accion_unificada.municipio ASC";

            $municipios = self::dbGetRows($q);
            $resultado = [];

            foreach ($municipios as $municipio) {
                $cantidad = (float) $municipio['suma'];
                $municipio['color'] = self::colorDesdePuntajes($cantidad, $puntajes, $colorDefecto);
                $resultado[] = $municipio;
            }

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error en calcularColorMapaInicial: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function calcularColorMapaActual($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : Util::getDepartamentoPrincipal();
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $colorDefecto = Util::getColorNeutroMapa();
            $puntajes = self::getPuntajes($inestabilidadId, self::TIPO_PUNTAJE_FINAL);
            $condicion = self::sqlCondicionInestabilidad($inestabilidadId, 't_fact');
            $puntajeEfectivo = self::sqlPuntajeEfectivoActual('t_ingreso', 't_fact', 't_actual');
            $joinActual = self::sqlJoinActualizacionIngreso($db);

            $q = "
                SELECT 
                    t_ciu.codigo_muncipio,
                    t_ciu.municipio,
                    t_ciu.d,
                    t_ciu.name,
                    t_ciu.class,
                    t_ciu.path,
                    t_ciu.nombre_mapa,
                    t_ciu.codigo_departamento,
                    COALESCE(SUM(
                        CASE WHEN $condicion THEN $puntajeEfectivo ELSE 0 END
                    ), 0) AS suma
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " AS t_ciu
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion') . " AS t_ingreso
                    ON t_ingreso.codigo_municipio = t_ciu.codigo_muncipio
                LEFT JOIN " . $db->getTable('tbl_factores') . " AS t_fact
                    ON t_ingreso.tbl_factor_id = t_fact.id
                $joinActual
                WHERE t_ciu.codigo_departamento = $codigoDepartamento
                GROUP BY
                    t_ciu.id,
                    t_ciu.codigo_muncipio,
                    t_ciu.municipio,
                    t_ciu.d,
                    t_ciu.name,
                    t_ciu.class,
                    t_ciu.path,
                    t_ciu.nombre_mapa,
                    t_ciu.codigo_departamento
                ORDER BY t_ciu.municipio ASC";

            $municipios = self::dbGetRows($q);
            $resultado = [];

            foreach ($municipios as $municipio) {
                $cantidad = (float) $municipio['suma'];
                $municipio['color'] = self::colorDesdePuntajes($cantidad, $puntajes, $colorDefecto);
                $resultado[] = $municipio;
            }

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error en calcularColorMapaActual: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function consultarConsolidadDepartamental($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;
        $codigoTodos = 10000;

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $whereInestabilidad = ($inestabilidadId != $codigoTodos)
                ? "AND tbl_factores.tbl_factor_inestabilidad_id = $inestabilidadId"
                : "AND tbl_factores.tbl_factor_inestabilidad_id IS NOT NULL";

            $q = "SELECT 
                    tbl_factores.tbl_factor_inestabilidad_id AS inestabilidad_id,
                    tbl_factores.id AS tbl_factor_id,
                    tbl_factores.tipo AS factor, 
                    tbl_factores.icono, 
                    tbl_factores.tec_area_id AS area_id,
                    tbl_factores.tipo_medicion,
                    SUM(tbl_ingreso_informacion.valor) AS total_cantidad
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
                INNER JOIN " . $db->getTable('tbl_vereda') . " AS tbl_vereda 
                    ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id 
                INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " AS tbl_ingreso_informacion 
                    ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id 
                INNER JOIN " . $db->getTable('tbl_factores') . " AS tbl_factores 
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                WHERE tbl_ciudades_accion_unificada.codigo_departamento = $codigoDepartamento
                    $whereInestabilidad
                GROUP BY
                    tbl_factores.tbl_factor_inestabilidad_id,
                    tbl_factores.id,
                    tbl_factores.tipo,
                    tbl_factores.icono,
                    tbl_factores.tec_area_id,
                    tbl_factores.tipo_medicion
                ORDER BY tbl_factores.tbl_factor_inestabilidad_id, tbl_factores.tipo";

            $consolidados = Util::sb_db_get($q, false);
            $resultado = [];
            foreach ($consolidados as $cantidad) {
                $resultado[] = $cantidad;
            }

            $inestabilidadIds = array_filter(array_unique(array_column($consolidados, 'inestabilidad_id')), function ($id) {
                return $id > 0;
            });

            $tabs = [];
            if (!empty($inestabilidadIds)) {
                $qTabs = "SELECT id, nombre_categoria AS nombre, icono, 'si' AS enable 
                         FROM " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " 
                         WHERE id IN (" . implode(',', $inestabilidadIds) . ")";
                $tabs = Util::sb_db_get($qTabs, false);
            }

            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                    'tabs' => $tabs,
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadDepartamental: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function consultarConsolidadActualesDepartamental($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;
        $codigoTodos = 10000;

        if ($codigoDepartamento == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            $pdo = $db->openConect();

            $whereInestabilidad = ($inestabilidadId != $codigoTodos)
                ? "AND t_fact.tbl_factor_inestabilidad_id = $inestabilidadId"
                : "AND t_fact.tbl_factor_inestabilidad_id IS NOT NULL";

            $q = "
                SELECT 
                    t_fact.tbl_factor_inestabilidad_id AS inestabilidad_id,
                    t_fact.id AS tbl_factor_id,
                    t_fact.tipo AS factor,
                    t_fact.icono,
                    t_fact.tec_area_id AS area_id,
                    t_fact.tipo_medicion,
                    SUM(COALESCE(t_actual.valor_actualizacion, t_ingreso.valor)) AS total_cantidad_actual
                FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " AS t_ciu
                INNER JOIN " . $db->getTable('tbl_vereda') . " AS t_v 
                    ON CAST(t_ciu.codigo_muncipio AS CHAR) = CAST(t_v.municipio_id AS CHAR)
                INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " AS t_ingreso
                    ON t_v.id = t_ingreso.tbl_vereda_id 
                INNER JOIN " . $db->getTable('tbl_factores') . " AS t_fact 
                    ON t_ingreso.tbl_factor_id = t_fact.id
                LEFT JOIN (
                    SELECT act.tbl_ingreso_informacion_id, act.valor_actualizacion
                    FROM ".$db->getTable('tbl_ingreso_informacion_x_actualizacion') ." act
                    INNER JOIN (
                        SELECT MAX(id) AS max_id
                        FROM ".$db->getTable('tbl_ingreso_informacion_x_actualizacion')."
                        GROUP BY tbl_ingreso_informacion_id
                    ) max_act ON act.id = max_act.max_id
                ) AS t_actual ON t_actual.tbl_ingreso_informacion_id = t_ingreso.id
                WHERE t_ciu.codigo_departamento = $codigoDepartamento
                    $whereInestabilidad
                GROUP BY
                    t_fact.tbl_factor_inestabilidad_id,
                    t_fact.id,
                    t_fact.tipo,
                    t_fact.icono,
                    t_fact.tec_area_id,
                    t_fact.tipo_medicion
                ORDER BY t_fact.tbl_factor_inestabilidad_id, t_fact.tipo";

            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultado,
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadActualesDepartamental: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function calcularColorVeredasInicial($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;

        if ($codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $puntajes = self::getPuntajes($inestabilidadId, self::TIPO_PUNTAJE_INICIAL);
            $colorDefecto = Util::getColorNeutroMapa();
            $condicion = self::sqlCondicionInestabilidad($inestabilidadId, 'tbl_factores');

            $q = "SELECT 
                    tbl_vereda.*,
                    COALESCE(SUM(
                        CASE WHEN $condicion
                        THEN COALESCE(tbl_factores.puntaje, 0)
                        ELSE 0 END
                    ), 0) AS cantidad
                FROM " . $db->getTable('tbl_vereda') . "
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion') . "
                    ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id
                LEFT JOIN " . $db->getTable('tbl_factores') . "
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
                WHERE tbl_vereda.municipio_id = $codigoMunicipio
                GROUP BY tbl_vereda.id
                ORDER BY tbl_vereda.nombre_vereda ASC";

            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $cantidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado = [];
            foreach ($cantidades as $cantidad) {
                $valorCantidad = (float) ($cantidad['cantidad'] ?? 0);
                $cantidad['color_calculado'] = self::colorDesdePuntajes($valorCantidad, $puntajes, $colorDefecto);
                $resultado[] = $cantidad;
            }

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error en calcularColorVeredasInicial: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function calcularColorVeredasActual($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;

        if ($codigoMunicipio == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $puntajes = self::getPuntajes($inestabilidadId, self::TIPO_PUNTAJE_FINAL);
            $colorDefecto = Util::getColorNeutroMapa();
            $condicion = self::sqlCondicionInestabilidad($inestabilidadId, 't_fact');
            $puntajeEfectivo = self::sqlPuntajeEfectivoActual('t_ingreso', 't_fact', 't_actual');
            $joinActual = self::sqlJoinActualizacionIngreso($db);

            $q = "SELECT 
                    t_v.*,
                    COALESCE(SUM(
                        CASE WHEN $condicion THEN $puntajeEfectivo ELSE 0 END
                    ), 0) AS cantidad
                FROM " . $db->getTable('tbl_vereda') . " AS t_v
                LEFT JOIN " . $db->getTable('tbl_ingreso_informacion') . " AS t_ingreso
                    ON t_v.id = t_ingreso.tbl_vereda_id
                LEFT JOIN " . $db->getTable('tbl_factores') . " AS t_fact
                    ON t_ingreso.tbl_factor_id = t_fact.id
                $joinActual
                WHERE t_v.municipio_id = $codigoMunicipio
                GROUP BY t_v.id
                ORDER BY t_v.nombre_vereda ASC";

            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $cantidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $resultado = [];
            foreach ($cantidades as $cantidad) {
                $valorCantidad = (float) ($cantidad['cantidad'] ?? 0);
                $cantidad['color_calculado'] = self::colorDesdePuntajes($valorCantidad, $puntajes, $colorDefecto);
                $resultado[] = $cantidad;
            }

            return ['output' => ['valid' => true, 'response' => $resultado]];
        } catch (Exception $e) {
            return Util::error_general("Error en calcularColorVeredasActual: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function getPuntajes($factorInestabilidadId, $tipo = self::TIPO_PUNTAJE_INICIAL)
    {
        $factorInestabilidadId = intval($factorInestabilidadId);
        $tipo = intval($tipo) === self::TIPO_PUNTAJE_FINAL
            ? self::TIPO_PUNTAJE_FINAL
            : self::TIPO_PUNTAJE_INICIAL;

        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT * FROM " . $db->getTable('tbl_puntajes') . "
              WHERE tbl_factores_gobernacion_id = :factorId
                AND tipo = :tipo
              ORDER BY rango_desde";
        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':factorId', $factorInestabilidadId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function consultarConsolidadMunicipioInicial($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;
        $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;
        $codigoTodos = 10000;

        if ($codigoMunicipio == 0) return Util::error_missing_data();

        $db = new DbConection();
        try {
            $whereInest = ($inestabilidadId != $codigoTodos)
                ? "AND f.tbl_factor_inestabilidad_id = $inestabilidadId"
                : "AND f.tbl_factor_inestabilidad_id IS NOT NULL";
            $whereVereda = ($veredaId > 0) ? "AND v.id = $veredaId" : "";

            $q = "SELECT 
                    f.tbl_factor_inestabilidad_id AS inestabilidad_id,
                    f.id AS tbl_factor_id,
                    f.tipo AS factor, f.icono, f.tec_area_id AS area_id,
                    f.tipo_medicion, f.tec_pilar_id AS pilar,
                    f.puntaje,
                    SUM(i.valor) AS total_cantidad
                FROM " . $db->getTable('tbl_ingreso_informacion') . " i
                INNER JOIN " . $db->getTable('tbl_vereda') . " v ON i.tbl_vereda_id = v.id
                LEFT JOIN " . $db->getTable('tbl_factores') . " f ON i.tbl_factor_id = f.id $whereInest
                WHERE v.municipio_id = $codigoMunicipio  $whereVereda
                GROUP BY f.tbl_factor_inestabilidad_id, f.id, f.tipo, f.icono, f.tec_area_id, f.tipo_medicion, f.tec_pilar_id, f.puntaje
                ORDER BY f.tbl_factor_inestabilidad_id, f.tipo";

            $consolidados = Util::sb_db_get($q, false);
            $resultado = [];
            foreach ($consolidados as $r) $resultado[] = $r;

            $ids = array_filter(array_unique(array_column($consolidados, 'inestabilidad_id')), fn($id) => $id > 0);
            $tabs = [];
            if (!empty($ids)) {
                $qTabs = "SELECT id, nombre_categoria AS nombre, icono, 'si' AS enable 
                         FROM " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " 
                         WHERE id IN (" . implode(',', $ids) . ")";
                $tabs = Util::sb_db_get($qTabs, false);
            }
           
            return ['output' => ['valid' => true, 'response' => $resultado, 'tabs' => $tabs]];
        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadMunicipioInicial: " . $e->getMessage());
        } finally { $db->closeConect(); }
    }

    public static function consultarConsolidadMunicipioActual($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $inestabilidadId = isset($rqst['inestabilidadId']) ? intval($rqst['inestabilidadId']) : 0;
        $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;
        $codigoTodos = 10000;

        if ($codigoMunicipio == 0) return Util::error_missing_data();

        $db = new DbConection();
        try {
            $pdo = $db->openConect();
            $whereInest = ($inestabilidadId != $codigoTodos)
                ? "AND f.tbl_factor_inestabilidad_id = $inestabilidadId"
                : "AND f.tbl_factor_inestabilidad_id IS NOT NULL";
            $whereVereda = ($veredaId > 0) ? "AND v.id = $veredaId" : "";

            $q = "SELECT 
                    f.tbl_factor_inestabilidad_id AS inestabilidad_id,
                    f.id AS tbl_factor_id, f.tipo AS factor,
                    f.icono, f.tec_area_id AS area_id,
                    f.tipo_medicion, f.tec_pilar_id AS pilar,
                    f.puntaje,
                    SUM(COALESCE(a.valor_actualizacion, i.valor)) AS total_cantidad_actual
                FROM " . $db->getTable('tbl_ingreso_informacion') . " i
                INNER JOIN " . $db->getTable('tbl_vereda') . " v ON i.tbl_vereda_id = v.id
                LEFT JOIN " . $db->getTable('tbl_factores') . " f ON i.tbl_factor_id = f.id $whereInest
                LEFT JOIN (
                    SELECT act.tbl_ingreso_informacion_id, act.valor_actualizacion
                    FROM ".$db->getTable('tbl_ingreso_informacion_x_actualizacion')." act
                    INNER JOIN (
                        SELECT MAX(id) AS max_id
                        FROM ".$db->getTable('tbl_ingreso_informacion_x_actualizacion')."
                        GROUP BY tbl_ingreso_informacion_id
                    ) max_act ON act.id = max_act.max_id
                ) a ON a.tbl_ingreso_informacion_id = i.id
                WHERE v.municipio_id = $codigoMunicipio  $whereVereda
                GROUP BY f.tbl_factor_inestabilidad_id, f.id, f.tipo, f.icono, f.tec_area_id, f.tipo_medicion, f.tec_pilar_id, f.puntaje
                ORDER BY f.tbl_factor_inestabilidad_id, f.tipo";

            $stmt = $pdo->prepare($q);
            $stmt->execute();
            return ['output' => ['valid' => true, 'response' => $stmt->fetchAll(PDO::FETCH_ASSOC)]];
        } catch (Exception $e) {
            return Util::error_general("Error en consultarConsolidadMunicipioActual: " . $e->getMessage());
        } finally { $db->closeConect(); }
    }
}
