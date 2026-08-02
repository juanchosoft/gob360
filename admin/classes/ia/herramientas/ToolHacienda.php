<?php

/**
 * Herramienta IA: Secretaría de Hacienda (fiscalización, recaudo, operatividad).
 *
 * Algunos registros tienen `tbl_municipio_id` con el código DANE de un municipio de
 * otro departamento (error de captura preexistente en la aplicación). El JOIN filtra
 * explícitamente por codigo_departamento='68' para que esos registros salgan con
 * municipio=NULL en vez de mostrar por error el nombre de un municipio ajeno a Santander.
 */
final class ToolHacienda
{
    private const MAX_FILAS = 250;

    /**
     * Consulta registros de hacienda con scoping territorial.
     * Tool: consultar_hacienda
     */
    public static function consultarHacienda(array $input): array
    {
        $municipioCodigo = $input['municipio_codigo'] ?? null;
        $tipo            = $input['tipo'] ?? null;
        $anio            = isset($input['anio']) ? (int) $input['anio'] : null;
        $limite          = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        [$sqlMun, $paramsMun] = IaScope::filtroMunicipio($municipioCodigo, 'h.tbl_municipio_id');

        $condiciones = [];
        $paramsExtra = [];
        if ($tipo !== null && $tipo !== '') {
            $condiciones[] = 'h.tipo = :tipo';
            $paramsExtra[':tipo'] = $tipo;
        }
        if ($anio !== null && $anio > 2000) {
            $condiciones[] = 'YEAR(h.date) = :anio';
            $paramsExtra[':anio'] = $anio;
        }
        $sqlWhere = $condiciones ? ' AND ' . implode(' AND ', $condiciones) : '';

        $db  = new DbConection();
        $pdo = $db->openConect();

        // Resumen financiero por municipio
        $sqlResumen = "SELECT
                        mun.municipio                  AS municipio,
                        h.tipo,
                        COUNT(h.id)                    AS registros,
                        SUM(COALESCE(h.valor_recaudo,0)) AS total_recaudo,
                        SUM(COALESCE(h.valor_total,0))   AS valor_total,
                        MAX(h.date)                    AS ultima_fecha
                     FROM tbl_hacienda h
                     LEFT JOIN tbl_ciudades_accion_unificada mun
                            ON mun.codigo_muncipio = h.tbl_municipio_id
                           AND mun.codigo_departamento = '68'
                     WHERE 1=1
                     {$sqlMun}
                     {$sqlWhere}
                     GROUP BY mun.municipio, h.tipo
                     ORDER BY total_recaudo DESC
                     LIMIT :limite";

        $params = array_merge($paramsMun, $paramsExtra, [':limite' => $limite]);
        $st = $pdo->prepare($sqlResumen);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':limite', $limite, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $db->closeConect();

        if (empty($rows)) {
            return ['nota' => 'No se encontraron registros de hacienda para los filtros aplicados.', 'registros' => []];
        }

        $totalRecaudo = array_sum(array_column($rows, 'total_recaudo'));

        $resultado = [
            'total_grupos'   => count($rows),
            'recaudo_global' => number_format($totalRecaudo, 2, '.', ''),
            'registros'      => $rows,
        ];

        $conMunicipioInvalido = count(array_filter($rows, fn($r) => $r['municipio'] === null));
        if ($conMunicipioInvalido > 0) {
            $resultado['advertencia'] = "{$conMunicipioInvalido} grupo(s) corresponden a registros con un código de municipio que no pertenece a Santander (error de captura en el origen de los datos) — no los presentes como si fueran de un municipio específico.";
        }

        return $resultado;
    }
}
