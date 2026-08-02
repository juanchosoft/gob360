<?php

/**
 * Herramientas IA: visitas municipales del gobernador.
 *
 * Algunos registros tienen tbl_municipio_id con código DANE de un municipio fuera de
 * Santander (error de captura preexistente). El JOIN filtra por codigo_departamento='68'
 * para que esos registros salgan con municipio=NULL en vez de mostrar un nombre incorrecto.
 */
final class ToolVisitas
{
    private const MAX_FILAS = 250;

    /**
     * Consulta visitas con scoping territorial.
     * Tool: consultar_visitas
     */
    public static function consultarVisitas(array $input): array
    {
        $municipioCodigo = $input['municipio_codigo'] ?? null;
        $tipoVisita      = $input['tipo_visita'] ?? null;
        $anio            = isset($input['anio']) ? (int) $input['anio'] : null;
        $limite          = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        [$sqlMun, $paramsMun] = IaScope::filtroMunicipio($municipioCodigo, 'v.tbl_municipio_id');

        $condiciones   = [];
        $paramsExtra   = [];

        if ($tipoVisita !== null && $tipoVisita !== '') {
            $condiciones[] = 'v.tipo_visita = :tipo_visita';
            $paramsExtra[':tipo_visita'] = $tipoVisita;
        }
        if ($anio !== null && $anio > 2000) {
            $condiciones[] = 'YEAR(v.created_at) = :anio';
            $paramsExtra[':anio'] = $anio;
        }

        $sqlWhere = $condiciones ? ' AND ' . implode(' AND ', $condiciones) : '';

        $db  = new DbConection();
        $pdo = $db->openConect();

        $sql = "SELECT
                    v.id,
                    v.tipo_visita,
                    v.provincia,
                    v.estado,
                    v.date AS fecha,
                    v.componente,
                    mun.municipio AS municipio
                FROM tbl_visitas v
                LEFT JOIN tbl_ciudades_accion_unificada mun
                       ON mun.codigo_muncipio = v.tbl_municipio_id
                      AND mun.codigo_departamento = '68'
                WHERE v.estado != 'Seleccione'
                {$sqlMun}
                {$sqlWhere}
                ORDER BY v.created_at DESC
                LIMIT :limite";

        $params = array_merge($paramsMun, $paramsExtra, [':limite' => $limite]);
        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':limite', $limite, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // Total real (sin LIMIT) con los mismos filtros
        $sqlCount = "SELECT COUNT(*) FROM tbl_visitas v WHERE v.estado != 'Seleccione' {$sqlMun} {$sqlWhere}";
        $stCount = $pdo->prepare($sqlCount);
        foreach (array_merge($paramsMun, $paramsExtra) as $k => $v) {
            $stCount->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stCount->execute();
        $totalReal = (int) $stCount->fetchColumn();

        // Resumen por tipo
        $resumen = [];
        foreach ($rows as $row) {
            $tipo = $row['tipo_visita'] ?? 'Sin tipo';
            $resumen[$tipo] = ($resumen[$tipo] ?? 0) + 1;
        }

        $db->closeConect();

        $resultado = ['total_mostrado' => count($rows), 'total_real' => $totalReal, 'resumen_por_tipo' => $resumen, 'visitas' => $rows];
        if ($totalReal > count($rows)) {
            $resultado['nota'] = "Se muestran " . count($rows) . " de {$totalReal} visitas que cumplen los filtros. Usa filtros adicionales de municipio, tipo o año, o usa total_real para el conteo exacto.";
        }
        return $resultado;
    }

    /**
     * Resumen de visitas agrupadas por provincia para el año actual.
     * Tool: resumen_visitas_por_provincia
     */
    public static function resumenVisitasPorProvincia(array $input): array
    {
        $anio = isset($input['anio']) ? (int) $input['anio'] : (int) Util::getAnioActual();

        $scope = IaScope::actual();
        $db    = new DbConection();
        $pdo   = $db->openConect();

        $sqlMun = '';
        $params = [':anio' => $anio];

        if ($scope['municipio_id'] !== null) {
            $sqlMun = ' AND v.tbl_municipio_id = :mid';
            $params[':mid'] = $scope['municipio_id'];
        }

        $st = $pdo->prepare(
            "SELECT
                v.provincia,
                COUNT(*) AS total_visitas,
                SUM(CASE WHEN v.estado = 'Cumplido' THEN 1 ELSE 0 END) AS cumplidas,
                SUM(CASE WHEN v.estado = 'En Trámite' THEN 1 ELSE 0 END) AS en_tramite,
                SUM(CASE WHEN v.estado = 'Sin Cumplir' THEN 1 ELSE 0 END) AS sin_cumplir
             FROM tbl_visitas v
             WHERE YEAR(v.created_at) = :anio
               AND v.estado != 'Seleccione'
             {$sqlMun}
             GROUP BY v.provincia
             ORDER BY total_visitas DESC"
        );
        $st->execute($params);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();

        return [
            'anio'         => $anio,
            'total_global' => array_sum(array_column($rows, 'total_visitas')),
            'por_provincia'=> $rows,
        ];
    }
}
