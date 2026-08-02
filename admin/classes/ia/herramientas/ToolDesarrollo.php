<?php

/**
 * Herramienta IA: Plan de Desarrollo departamental.
 */
final class ToolDesarrollo
{
    private const MAX_FILAS = 250;

    /**
     * Consulta metas del Plan de Desarrollo con scoping por secretaría.
     * Tool: consultar_plan_desarrollo
     */
    public static function consultarPlanDesarrollo(array $input): array
    {
        $secretariaId = isset($input['secretaria_id']) ? (int) $input['secretaria_id'] : null;
        $eje          = $input['eje_estrategico'] ?? null;
        $anio         = $input['anio'] ?? null;  // '2024' | '2025' | '2026' | '2027'
        $limite       = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        [$sqlSec, $paramsSec] = IaScope::filtroSecretaria($secretariaId, 'p.tbl_secretaria_id');

        $condiciones = [];
        $paramsExtra = [];
        if ($eje !== null && $eje !== '') {
            $condiciones[] = 'p.eje_estrategico LIKE :eje';
            $paramsExtra[':eje'] = '%' . $eje . '%';
        }
        $sqlWhere = $condiciones ? ' AND ' . implode(' AND ', $condiciones) : '';

        // Columna de avance según año solicitado.
        // Solo existen avance_2024 y avance_2025 en la BD; para 2026/2027 solo hay programación (ps).
        $campoAvance = match ($anio) {
            '2024' => 'p.avance_2024',
            default => 'p.avance_2025',
        };

        $db  = new DbConection();
        $pdo = $db->openConect();

        $sql = "SELECT
                    p.id,
                    p.eje_estrategico,
                    p.sector_pdd           AS sector,
                    p.producto_servicio_pdd AS meta,
                    p.direccion_resp        AS direccion,
                    {$campoAvance}          AS avance_pct,
                    p.ps2024               AS programado_2024,
                    p.ps2025               AS programado_2025,
                    p.ps2026               AS programado_2026,
                    p.ps2027               AS programado_2027,
                    sec.secretaria         AS secretaria
                FROM tbl_plandesarrollo p
                LEFT JOIN tbl_secretarias sec ON sec.id = p.tbl_secretaria_id
                WHERE 1=1
                {$sqlSec}
                {$sqlWhere}
                ORDER BY p.eje_estrategico, p.sector_pdd
                LIMIT :limite";

        $params = array_merge($paramsSec, $paramsExtra, [':limite' => $limite]);
        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':limite', $limite, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // Total real (sin LIMIT) con los mismos filtros — tbl_plandesarrollo tiene 378
        // registros en total, puede truncarse con el límite por defecto de 50.
        $sqlCount = "SELECT COUNT(*) FROM tbl_plandesarrollo p WHERE 1=1 {$sqlSec} {$sqlWhere}";
        $stCount = $pdo->prepare($sqlCount);
        foreach (array_merge($paramsSec, $paramsExtra) as $k => $v) {
            $stCount->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stCount->execute();
        $totalReal = (int) $stCount->fetchColumn();

        // Estadísticas de avance (solo sobre las filas mostradas)
        $avances = array_filter(array_column($rows, 'avance_pct'), fn($v) => is_numeric($v));
        $promedioAvance = count($avances) > 0 ? round(array_sum($avances) / count($avances), 1) : 0;

        $db->closeConect();

        $resultado = [
            'total_mostrado'  => count($rows),
            'total_real'      => $totalReal,
            'anio_consultado' => $anio ?? '2025',
            'avance_promedio' => $promedioAvance,
            'metas'           => $rows,
        ];
        if ($totalReal > count($rows)) {
            $resultado['nota'] = "Se muestran " . count($rows) . " de {$totalReal} metas que cumplen los filtros. avance_promedio solo considera las filas mostradas — filtra por secretaría o eje estratégico para un promedio más representativo.";
        }
        return $resultado;
    }
}
