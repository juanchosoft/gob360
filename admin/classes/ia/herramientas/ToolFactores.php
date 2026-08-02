<?php

/**
 * Herramientas IA: factores de inestabilidad territorial y semáforo del mapa.
 *
 * La columna cacheada tbl_ciudades_accion_unificada.puntaje/color no se mantiene
 * (siempre 0/NULL). El mapa real se calcula en vivo sumando tbl_ingreso_informacion +
 * tbl_factores con la lógica de FactoresInestabilidadGeneral, así que esta tool
 * reutiliza esas mismas funciones (calcularColorMapaActual/Inicial,
 * calcularColorVeredasActual/Inicial) en vez de leer la columna vacía.
 *
 * inestabilidadId = 10000 significa "todas las categorías de factores".
 */
final class ToolFactores
{
    private const TODAS_LAS_CATEGORIAS = 10000;
    private const CODIGO_DEPARTAMENTO_SANTANDER = 68;

    private static function cargarDependencias(): void
    {
        if (!class_exists('FactoresInestabilidadGeneral')) {
            require_once __DIR__ . '/../../FactoresInestabilidadGeneral.php';
        }
    }

    /**
     * Consulta factores de inestabilidad de un municipio con su semáforo real
     * (mapa inicial vs actual, calculado en vivo) y el detalle por categoría.
     * Tool: consultar_factores_municipio
     */
    public static function consultarFactoresMunicipio(array $input): array
    {
        self::cargarDependencias();

        $municipioCodigo = $input['municipio_codigo'] ?? null;

        // REGLA DE ORO: el alcalde solo ve su municipio
        $scope = IaScope::actual();
        if ($scope['municipio_id'] !== null) {
            $municipioCodigo = $scope['municipio_id'];
        }
        if ($municipioCodigo === null || $municipioCodigo === '') {
            return ['error' => 'Debes especificar un municipio. Usa buscar_municipio para obtener el código DANE.'];
        }

        // Semáforo real: se calcula para TODO el departamento y se filtra el
        // municipio pedido, reutilizando la lógica oficial del mapa (no la
        // columna cacheada, que está vacía).
        $paramsMapa = ['codigo_departamento' => self::CODIGO_DEPARTAMENTO_SANTANDER, 'inestabilidadId' => self::TODAS_LAS_CATEGORIAS];
        $mapaActual  = FactoresInestabilidadGeneral::calcularColorMapaActual($paramsMapa);
        $mapaInicial = FactoresInestabilidadGeneral::calcularColorMapaInicial($paramsMapa);

        if (empty($mapaActual['output']['valid']) || empty($mapaInicial['output']['valid'])) {
            return ['error' => 'No fue posible calcular el semáforo de inestabilidad en este momento.'];
        }

        $filaActual = null;
        foreach ($mapaActual['output']['response'] as $row) {
            if ((string) $row['codigo_muncipio'] === (string) $municipioCodigo) {
                $filaActual = $row;
                break;
            }
        }
        $filaInicial = null;
        foreach ($mapaInicial['output']['response'] as $row) {
            if ((string) $row['codigo_muncipio'] === (string) $municipioCodigo) {
                $filaInicial = $row;
                break;
            }
        }

        if ($filaActual === null) {
            return ['error' => "No se encontró el municipio con código {$municipioCodigo} en Santander."];
        }

        $puntajeActual  = (float) $filaActual['suma'];
        $puntajeInicial = (float) ($filaInicial['suma'] ?? 0);
        $variacion      = $puntajeActual - $puntajeInicial;

        $db  = new DbConection();
        $pdo = $db->openConect();

        // Detalle informativo de factores por categoría (registros reales de campo).
        // Es un desglose de apoyo; el puntaje OFICIAL del semáforo es el de arriba.
        $st2 = $pdo->prepare(
            "SELECT
                fi.nombre_categoria           AS categoria,
                f.tipo                        AS factor,
                f.puntaje                     AS puntaje_factor,
                f.puntaje_inicial,
                COUNT(DISTINCT i.tbl_vereda_id) AS veredas_afectadas,
                SUM(i.valor)                  AS cantidad_actual,
                SUM(i.valor_inicial)          AS cantidad_inicial
             FROM tbl_ingreso_informacion i
             JOIN tbl_factores f ON f.id = i.tbl_factor_id
             JOIN tbl_factores_inestabilidad_gobernacion fi ON fi.id = f.tbl_factor_inestabilidad_id
             WHERE i.codigo_municipio = :c
             GROUP BY fi.nombre_categoria, f.tipo, f.puntaje, f.puntaje_inicial
             ORDER BY f.puntaje DESC"
        );
        $st2->execute([':c' => $municipioCodigo]);
        $factores = $st2->fetchAll(PDO::FETCH_ASSOC);

        // Veredas del municipio con su semáforo real (actual vs inicial), calculado en vivo.
        $paramsVereda = ['codigo_municipio' => $municipioCodigo, 'inestabilidadId' => self::TODAS_LAS_CATEGORIAS];
        $veredasActual  = FactoresInestabilidadGeneral::calcularColorVeredasActual($paramsVereda);
        $veredasInicial = FactoresInestabilidadGeneral::calcularColorVeredasInicial($paramsVereda);

        $inicialPorVereda = [];
        if (!empty($veredasInicial['output']['valid'])) {
            foreach ($veredasInicial['output']['response'] as $v) {
                $inicialPorVereda[$v['id']] = (float) ($v['cantidad'] ?? 0);
            }
        }

        $veredas = [];
        if (!empty($veredasActual['output']['valid'])) {
            foreach ($veredasActual['output']['response'] as $v) {
                $cantidadActual = (float) ($v['cantidad'] ?? 0);
                if ($cantidadActual <= 0) {
                    continue;
                }
                $veredas[] = [
                    'nombre_vereda'   => $v['nombre_vereda'] ?? $v['name'] ?? '(sin nombre)',
                    'puntaje_actual'  => $cantidadActual,
                    'color_actual'    => $v['color_calculado'] ?? null,
                    'puntaje_inicial' => $inicialPorVereda[$v['id']] ?? 0,
                ];
            }
            usort($veredas, fn($a, $b) => $b['puntaje_actual'] <=> $a['puntaje_actual']);
            $veredas = array_slice($veredas, 0, 50);
        }

        // Rangos del semáforo (todas las categorías, mapa actual) para interpretar
        $rangos = FactoresInestabilidadGeneral::getPuntajes(
            self::TODAS_LAS_CATEGORIAS,
            FactoresInestabilidadGeneral::TIPO_PUNTAJE_FINAL
        );

        $db->closeConect();

        return [
            'municipio'          => $filaActual['municipio'],
            'semaforo'           => [
                'puntaje_actual'  => $puntajeActual,
                'color_actual'    => $filaActual['color'],
                'puntaje_inicial' => $puntajeInicial,
                'color_inicial'   => $filaInicial['color'] ?? null,
                'variacion'       => $variacion,
                'tendencia'       => $variacion > 0 ? 'EMPEORÓ' : ($variacion < 0 ? 'MEJORÓ' : 'SIN CAMBIO'),
            ],
            'rangos_semaforo'    => array_map(
                fn($r) => ['nivel' => $r['name'], 'desde' => $r['rango_desde'], 'hasta' => $r['rango_hasta'], 'color' => $r['color']],
                $rangos
            ),
            'factores_por_categoria' => $factores,
            'veredas_con_afectacion' => $veredas,
            'nota' => 'El puntaje del semáforo (semaforo.puntaje_actual/inicial) es el cálculo OFICIAL del mapa, idéntico al que ve el usuario en pantalla. factores_por_categoria es un desglose informativo adicional y puede no sumar exactamente igual al semáforo oficial. Comparar puntaje_inicial vs puntaje_actual muestra si el territorio mejoró o empeoró.',
        ];
    }

    /**
     * Resumen del semáforo de inestabilidad departamental: mapa actual vs inicial
     * (calculado en vivo, idéntico al mapa real), municipios más críticos y
     * distribución por color.
     * Tool: consultar_puntajes_departamento
     */
    public static function consultarPuntajesDepartamento(array $input): array
    {
        self::cargarDependencias();

        $scope = IaScope::actual();
        if (!$scope['ve_todo']) {
            return ['error' => 'Esta consulta requiere acceso departamental. Usa consultar_factores_municipio para tu municipio.'];
        }

        $paramsMapa = ['codigo_departamento' => self::CODIGO_DEPARTAMENTO_SANTANDER, 'inestabilidadId' => self::TODAS_LAS_CATEGORIAS];
        $mapaActual  = FactoresInestabilidadGeneral::calcularColorMapaActual($paramsMapa);
        $mapaInicial = FactoresInestabilidadGeneral::calcularColorMapaInicial($paramsMapa);

        if (empty($mapaActual['output']['valid'])) {
            return ['error' => 'No fue posible calcular el semáforo departamental en este momento.'];
        }

        $inicialPorMunicipio = [];
        if (!empty($mapaInicial['output']['valid'])) {
            foreach ($mapaInicial['output']['response'] as $row) {
                $inicialPorMunicipio[$row['codigo_muncipio']] = $row;
            }
        }

        $municipios = [];
        $distribucion = [];
        foreach ($mapaActual['output']['response'] as $row) {
            $suma = (float) $row['suma'];
            $ini  = (float) ($inicialPorMunicipio[$row['codigo_muncipio']]['suma'] ?? 0);
            $municipios[] = [
                'municipio'       => $row['municipio'],
                'codigo_dane'     => $row['codigo_muncipio'],
                'puntaje_actual'  => $suma,
                'color_actual'    => $row['color'],
                'puntaje_inicial' => $ini,
                'color_inicial'   => $inicialPorMunicipio[$row['codigo_muncipio']]['color'] ?? null,
                'variacion'       => $suma - $ini,
            ];
            $color = $row['color'] ?: 'sin_color';
            $distribucion[$color] = ($distribucion[$color] ?? 0) + 1;
        }

        usort($municipios, fn($a, $b) => $b['puntaje_actual'] <=> $a['puntaje_actual']);
        $topMunicipios = array_slice($municipios, 0, 30);

        arsort($distribucion);
        $distribucionFormateada = [];
        foreach ($distribucion as $color => $cantidad) {
            $distribucionFormateada[] = ['color' => $color, 'municipios' => $cantidad];
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        // Resumen informativo por categoría de factor a nivel departamental
        $st3 = $pdo->prepare(
            "SELECT
                fi.nombre_categoria             AS categoria,
                COUNT(DISTINCT i.codigo_municipio) AS municipios_afectados,
                COUNT(DISTINCT i.tbl_vereda_id) AS veredas_afectadas,
                SUM(i.valor)                    AS cantidad_actual,
                SUM(i.valor_inicial)            AS cantidad_inicial
             FROM tbl_ingreso_informacion i
             JOIN tbl_factores f ON f.id = i.tbl_factor_id
             JOIN tbl_factores_inestabilidad_gobernacion fi ON fi.id = f.tbl_factor_inestabilidad_id
             GROUP BY fi.nombre_categoria
             ORDER BY cantidad_actual DESC"
        );
        $st3->execute();
        $porCategoria = $st3->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();

        $rangos = FactoresInestabilidadGeneral::getPuntajes(
            self::TODAS_LAS_CATEGORIAS,
            FactoresInestabilidadGeneral::TIPO_PUNTAJE_FINAL
        );

        return [
            'rangos_semaforo' => array_map(
                fn($r) => ['nivel' => $r['name'], 'desde' => $r['rango_desde'], 'hasta' => $r['rango_hasta'], 'color' => $r['color']],
                $rangos
            ),
            'distribucion_por_color'      => $distribucionFormateada,
            'top_municipios_por_puntaje'  => $topMunicipios,
            'resumen_por_categoria'       => $porCategoria,
            'nota' => 'Puntajes calculados en vivo con la misma lógica del mapa oficial de la aplicación. Variación positiva = empeoró, negativa = mejoró.',
        ];
    }
}
