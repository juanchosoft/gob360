<?php

/**
 * Herramientas IA: proyectos de secretarías y alcaldías.
 *
 * tbl_proyectos: porcentaje_financiero, adicion, dias_prorroga, date_prorroga y
 * plazo_construccion están vacíos en la práctica totalidad de los registros — no se
 * exponen aquí porque no aportan información real. valor_ejecutado sí tiene datos
 * en una parte relevante de los registros y se expone.
 * tbl_proyectos_alcaldias: dataset muy pequeño; porcentaje_financiero y adicion sí
 * están poblados y se exponen.
 */
final class ToolProyectos
{
    private const MAX_FILAS = 250;

    /**
     * Consulta proyectos de secretarías departamentales.
     * Tool: consultar_proyectos_secretarias
     */
    public static function consultarProyectosSecretarias(array $input): array
    {
        $secretariaId = isset($input['secretaria_id']) ? (int) $input['secretaria_id'] : null;
        $estado       = $input['estado'] ?? null;
        $limite       = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        [$sqlSec, $paramsSec] = IaScope::filtroSecretaria($secretariaId, 'p.tbl_secretarias_id');

        $condiciones = [];
        $paramsExtra = [];
        if ($estado !== null && $estado !== '') {
            $condiciones[] = 'p.estado = :estado';
            $paramsExtra[':estado'] = $estado;
        }
        $sqlWhere = $condiciones ? ' AND ' . implode(' AND ', $condiciones) : '';

        $db  = new DbConection();
        $pdo = $db->openConect();

        $sql = "SELECT
                    p.id,
                    p.proyecto,
                    p.estado,
                    p.valor_proyecto,
                    p.valor_ejecutado,
                    p.porcentaje_ejecucion,
                    p.date AS fecha,
                    mun.municipio  AS municipio,
                    sec.secretaria AS secretaria
                FROM tbl_proyectos p
                LEFT JOIN tbl_ciudades_accion_unificada mun ON mun.codigo_muncipio = p.tbl_municipio_id
                LEFT JOIN tbl_secretarias sec ON sec.id = p.tbl_secretarias_id
                WHERE 1=1
                {$sqlSec}
                {$sqlWhere}
                ORDER BY p.dtcreate DESC
                LIMIT :limite";

        $params = array_merge($paramsSec, $paramsExtra, [':limite' => $limite]);
        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':limite', $limite, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // Total real (sin LIMIT) con los mismos filtros — con 2.777 proyectos en total,
        // el LIMIT de 250 trunca con frecuencia si no se filtra por secretaría.
        $sqlCount = "SELECT COUNT(*) FROM tbl_proyectos p WHERE 1=1 {$sqlSec} {$sqlWhere}";
        $stCount = $pdo->prepare($sqlCount);
        foreach (array_merge($paramsSec, $paramsExtra) as $k => $v) {
            $stCount->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stCount->execute();
        $totalReal = (int) $stCount->fetchColumn();

        // Inversión total (calculada solo sobre las filas mostradas, no sobre el total real)
        $inversionTotal = 0.0;
        $resumen = [];
        foreach ($rows as $row) {
            $inversionTotal += (float) ($row['valor_proyecto'] ?? 0);
            $e = $row['estado'] ?? 'Sin estado';
            $resumen[$e] = ($resumen[$e] ?? 0) + 1;
        }

        $db->closeConect();

        $resultado = [
            'total_mostrado'  => count($rows),
            'total_real'      => $totalReal,
            'resumen_estados' => $resumen,
            'inversion_total' => number_format($inversionTotal, 2, '.', ''),
            'proyectos'       => $rows,
        ];
        if ($totalReal > count($rows)) {
            $resultado['nota'] = "Se muestran " . count($rows) . " de {$totalReal} proyectos que cumplen los filtros. inversion_total solo suma las filas mostradas, NO el total_real — filtra por secretaría o estado para un análisis financiero completo.";
        }
        return $resultado;
    }

    /**
     * Consulta proyectos de alcaldías con scoping por municipio.
     * Tool: consultar_proyectos_alcaldias
     */
    public static function consultarProyectosAlcaldias(array $input): array
    {
        $municipioCodigo = $input['municipio_codigo'] ?? null;
        $estado          = $input['estado'] ?? null;
        $limite          = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        [$sqlMun, $paramsMun] = IaScope::filtroMunicipio($municipioCodigo, 'p.tbl_municipio_id');

        $condiciones = [];
        $paramsExtra = [];
        if ($estado !== null && $estado !== '') {
            $condiciones[] = 'p.estado = :estado';
            $paramsExtra[':estado'] = $estado;
        }
        $sqlWhere = $condiciones ? ' AND ' . implode(' AND ', $condiciones) : '';

        $db  = new DbConection();
        $pdo = $db->openConect();

        $sql = "SELECT
                    p.id,
                    p.proyecto,
                    p.estado,
                    p.valor_proyecto,
                    p.aporte_gobernacion,
                    p.porcentaje_ejecucion,
                    p.porcentaje_financiero,
                    p.adicion,
                    p.date AS fecha,
                    mun.municipio  AS municipio,
                    sec.secretaria AS secretaria
                FROM tbl_proyectos_alcaldias p
                LEFT JOIN tbl_ciudades_accion_unificada mun ON mun.codigo_muncipio = p.tbl_municipio_id
                LEFT JOIN tbl_secretarias sec ON sec.id = p.tbl_secretarias_id
                WHERE 1=1
                {$sqlMun}
                {$sqlWhere}
                ORDER BY p.dtcreate DESC
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
        $sqlCount = "SELECT COUNT(*) FROM tbl_proyectos_alcaldias p WHERE 1=1 {$sqlMun} {$sqlWhere}";
        $stCount = $pdo->prepare($sqlCount);
        foreach (array_merge($paramsMun, $paramsExtra) as $k => $v) {
            $stCount->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stCount->execute();
        $totalReal = (int) $stCount->fetchColumn();

        $inversionTotal = 0.0;
        $resumen = [];
        foreach ($rows as $row) {
            $inversionTotal += (float) ($row['valor_proyecto'] ?? 0);
            $e = $row['estado'] ?? 'Sin estado';
            $resumen[$e] = ($resumen[$e] ?? 0) + 1;
        }

        $db->closeConect();

        $resultado = [
            'total_mostrado'  => count($rows),
            'total_real'      => $totalReal,
            'resumen_estados' => $resumen,
            'inversion_total' => number_format($inversionTotal, 2, '.', ''),
            'proyectos'       => $rows,
        ];

        // tbl_proyectos_alcaldias tiene muy pocos registros en el sistema actual y
        // pueden incluir datos de prueba/carga inicial (valores sin sentido como
        // porcentajes >100%). Advertir para que no se presenten como muestra real.
        if (count($rows) > 0 && count($rows) <= 5) {
            $resultado['advertencia'] = 'Esta tabla tiene muy pocos registros en el sistema. Verifica que los datos sean reales antes de presentarlos como representativos (pueden ser registros de prueba con valores no realistas).';
        }

        return $resultado;
    }
}
