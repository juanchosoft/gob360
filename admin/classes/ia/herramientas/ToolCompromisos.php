<?php

/**
 * Herramientas IA: compromisos del gobernador y de alcaldes.
 *
 * tbl_compromisos: descripción en `compromisos`, estado en `cumplimiento` (varchar
 * texto libre, ej. 'Sin Cumplir'). No existen columnas `nombre`, `estado` ni `fecha`.
 * tbl_compromisos_alcalde: `cumplimiento` enum es el avance real; `estado` enum es el
 * flujo de aprobación (En Espera/Aprobado/Rechazado). Para informes de cumplimiento
 * usar siempre `cumplimiento`.
 * Join municipal: c.codigo_muncipio (typo real de la BD) = t.tbl_municipio_id.
 */
final class ToolCompromisos
{
    private const MAX_FILAS = 250;

    /**
     * Consulta compromisos del gobernador con municipios.
     * Tool: consultar_compromisos
     */
    public static function consultarCompromisos(array $input): array
    {
        $municipioCodigo = $input['municipio_codigo'] ?? null;
        $cumplimiento    = $input['estado'] ?? ($input['cumplimiento'] ?? null);
        $secretariaId    = isset($input['secretaria_id']) ? (int) $input['secretaria_id'] : null;
        $limite          = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        [$sqlMun, $paramsMun] = IaScope::filtroMunicipio($municipioCodigo, 'c.tbl_municipio_id');
        [$sqlSec, $paramsSec] = IaScope::filtroSecretaria($secretariaId, 'c.tbl_secretarias_id');

        $sqlCumpl = '';
        $paramsCumpl = [];
        $valoresValidos = ['Cumplido', 'En Trámite', 'Sin Cumplir'];
        if ($cumplimiento !== null && in_array($cumplimiento, $valoresValidos, true)) {
            // cumplimiento es texto libre en esta tabla — usar LIKE para tolerar variaciones
            $sqlCumpl = ' AND c.cumplimiento LIKE :cumplimiento';
            $paramsCumpl = [':cumplimiento' => '%' . $cumplimiento . '%'];
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        $sql = "SELECT
                    c.id,
                    c.compromisos   AS compromiso,
                    c.cumplimiento,
                    c.respuesta,
                    c.date          AS fecha,
                    c.created_at,
                    c.provincia,
                    mun.municipio   AS municipio,
                    sec.secretaria  AS secretaria
                FROM tbl_compromisos c
                LEFT JOIN tbl_ciudades_accion_unificada mun ON mun.codigo_muncipio = c.tbl_municipio_id
                LEFT JOIN tbl_secretarias sec ON sec.id = c.tbl_secretarias_id
                WHERE 1=1
                {$sqlMun}
                {$sqlSec}
                {$sqlCumpl}
                ORDER BY c.created_at DESC
                LIMIT :limite";

        $params = array_merge($paramsMun, $paramsSec, $paramsCumpl, [':limite' => $limite]);
        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':limite', $limite, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // Total real (sin LIMIT) con los mismos filtros, para no subestimar conteos
        $sqlCount = "SELECT COUNT(*) FROM tbl_compromisos c WHERE 1=1 {$sqlMun} {$sqlSec} {$sqlCumpl}";
        $stCount = $pdo->prepare($sqlCount);
        foreach (array_merge($paramsMun, $paramsSec, $paramsCumpl) as $k => $v) {
            $stCount->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stCount->execute();
        $totalReal = (int) $stCount->fetchColumn();

        // Resumen por cumplimiento
        $resumen = self::calcularResumen($rows, 'cumplimiento');

        $db->closeConect();

        $resultado = [
            'total_mostrado'       => count($rows),
            'total_real'           => $totalReal,
            'resumen_cumplimiento' => $resumen,
            'compromisos'          => $rows,
        ];
        if ($totalReal > count($rows)) {
            $resultado['nota'] = "Se muestran " . count($rows) . " de {$totalReal} compromisos que cumplen los filtros. Refina la consulta con filtros adicionales de municipio o cumplimiento para ver más detalle, o usa total_real para el conteo exacto.";
        }
        return $resultado;
    }

    /**
     * Consulta compromisos de alcaldes (scoping forzado al municipio del alcalde).
     * Tool: consultar_compromisos_alcalde
     */
    public static function consultarCompromisosAlcalde(array $input): array
    {
        $scope  = IaScope::actual();
        $limite = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        // Para alcaldes el municipio siempre es el suyo; para roles que ven todo,
        // se puede pasar un municipio_codigo como filtro (o consultar todos).
        $municipioCodigo = $scope['municipio_id'] ?? ($input['municipio_codigo'] ?? null);
        $cumplimiento    = $input['estado'] ?? ($input['cumplimiento'] ?? null);

        $db  = new DbConection();
        $pdo = $db->openConect();

        $condiciones = [];
        $params      = [];

        if ($municipioCodigo !== null && $municipioCodigo !== '') {
            $condiciones[] = 'cm.tbl_municipio_id = :municipio';
            $params[':municipio'] = $municipioCodigo;
        }
        if ($cumplimiento !== null && in_array($cumplimiento, ['Cumplido', 'En Trámite', 'Sin Cumplir'], true)) {
            $condiciones[] = 'cm.cumplimiento = :cumplimiento';
            $params[':cumplimiento'] = $cumplimiento;
        }

        $sqlWhere = $condiciones ? ' WHERE ' . implode(' AND ', $condiciones) : '';

        $sql = "SELECT
                    cm.id,
                    cm.compromisos    AS compromiso,
                    cm.cumplimiento,
                    cm.estado         AS estado_aprobacion,
                    cm.tipo_ejecucion,
                    cm.date           AS fecha,
                    mun.municipio     AS municipio,
                    sec.secretaria    AS secretaria
                FROM tbl_compromisos_alcalde cm
                LEFT JOIN tbl_ciudades_accion_unificada mun
                       ON CAST(mun.codigo_muncipio AS UNSIGNED) = CAST(cm.tbl_municipio_id AS UNSIGNED)
                LEFT JOIN tbl_secretarias sec ON sec.id = cm.tbl_secretarias_id
                {$sqlWhere}
                ORDER BY cm.date DESC
                LIMIT :limite";

        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':limite', $limite, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // Total real (sin LIMIT) con los mismos filtros
        $sqlCount = "SELECT COUNT(*) FROM tbl_compromisos_alcalde cm {$sqlWhere}";
        $stCount = $pdo->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stCount->bindValue($k, $v);
        }
        $stCount->execute();
        $totalReal = (int) $stCount->fetchColumn();

        $resumenCumplimiento = self::calcularResumen($rows, 'cumplimiento');
        $resumenAprobacion   = [];
        foreach ($rows as $row) {
            $e = $row['estado_aprobacion'] ?? 'Sin estado';
            $resumenAprobacion[$e] = ($resumenAprobacion[$e] ?? 0) + 1;
        }

        $db->closeConect();

        return [
            'total_mostrado'       => count($rows),
            'total_real'           => $totalReal,
            'resumen_cumplimiento' => $resumenCumplimiento,
            'resumen_aprobacion'   => $resumenAprobacion,
            'nota_semantica'       => 'cumplimiento = avance real del compromiso; estado_aprobacion = flujo interno de aprobación. Para informes de cumplimiento usar cumplimiento.',
            'compromisos'          => $rows,
        ];
    }

    private static function calcularResumen(array $rows, string $campo): array
    {
        $resumen = ['Cumplido' => 0, 'En Trámite' => 0, 'Sin Cumplir' => 0, 'Otro' => 0];
        foreach ($rows as $row) {
            $valor = trim($row[$campo] ?? '');
            if (isset($resumen[$valor])) {
                $resumen[$valor]++;
            } else {
                $resumen['Otro']++;
            }
        }
        if ($resumen['Otro'] === 0) {
            unset($resumen['Otro']);
        }
        return $resumen;
    }
}
