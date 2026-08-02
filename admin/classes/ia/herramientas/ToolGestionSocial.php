<?php

/**
 * Herramienta IA: Gestión Social (actividades de la Gestora Social / ASPAS).
 */
final class ToolGestionSocial
{
    private const MAX_FILAS = 250;

    /**
     * Consulta actividades de gestión social por municipio.
     * Tool: consultar_gestion_social
     */
    public static function consultarGestionSocial(array $input): array
    {
        $municipioCodigo = $input['municipio_codigo'] ?? null;
        $anio            = isset($input['anio']) ? (int) $input['anio'] : null;
        $limite          = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        [$sqlMun, $paramsMun] = IaScope::filtroMunicipio($municipioCodigo, 'g.tbl_municipio_id');

        $condiciones = [];
        $paramsExtra = [];
        if ($anio !== null && $anio > 2000) {
            $condiciones[] = 'YEAR(g.dtcreate) = :anio';
            $paramsExtra[':anio'] = $anio;
        }
        $sqlWhere = $condiciones ? ' AND ' . implode(' AND ', $condiciones) : '';

        $db  = new DbConection();
        $pdo = $db->openConect();

        $sql = "SELECT
                    g.id,
                    ac.accion                AS tipo_accion,
                    g.desc_actividad,
                    g.date                   AS fecha,
                    g.provincia,
                    g.poblacion,
                    g.impactada,
                    g.inversion,
                    mun.municipio            AS municipio
                FROM tbl_gestora_aspas g
                LEFT JOIN tbl_acciong ac ON ac.id = g.tbl_acciong_id
                LEFT JOIN tbl_ciudades_accion_unificada mun ON mun.codigo_muncipio = g.tbl_municipio_id
                WHERE 1=1
                {$sqlMun}
                {$sqlWhere}
                ORDER BY g.dtcreate DESC
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
        $sqlCount = "SELECT COUNT(*) FROM tbl_gestora_aspas g WHERE 1=1 {$sqlMun} {$sqlWhere}";
        $stCount = $pdo->prepare($sqlCount);
        foreach (array_merge($paramsMun, $paramsExtra) as $k => $v) {
            $stCount->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stCount->execute();
        $totalReal = (int) $stCount->fetchColumn();

        // Resumen por tipo de acción
        $resumen = [];
        foreach ($rows as $row) {
            $tipo = $row['tipo_accion'] ?? 'Sin tipo';
            $resumen[$tipo] = ($resumen[$tipo] ?? 0) + 1;
        }

        $db->closeConect();

        $resultado = [
            'total_mostrado'   => count($rows),
            'total_real'       => $totalReal,
            'resumen_por_tipo' => $resumen,
            'actividades'      => $rows,
        ];
        if ($totalReal > count($rows)) {
            $resultado['nota'] = "Se muestran " . count($rows) . " de {$totalReal} actividades que cumplen los filtros.";
        }
        return $resultado;
    }
}
