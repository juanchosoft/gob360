<?php

/**
 * Herramienta IA: Programa de Alimentación Escolar (PAE).
 */
final class ToolPae
{
    private const MAX_FILAS = 250;

    /**
     * Consulta registros del PAE por municipio y/o año.
     * Tool: consultar_pae
     */
    public static function consultarPae(array $input): array
    {
        $municipioCodigo = $input['municipio_codigo'] ?? null;
        $anio            = isset($input['anio']) ? (int) $input['anio'] : null;
        $limite          = min((int) ($input['limite'] ?? 50), self::MAX_FILAS);

        [$sqlMun, $paramsMun] = IaScope::filtroMunicipio($municipioCodigo, 'p.tbl_municipio_id');

        $condiciones = [];
        $paramsExtra = [];
        if ($anio !== null && $anio > 2000) {
            $condiciones[] = 'p.ano = :anio';
            $paramsExtra[':anio'] = $anio;
        }
        $sqlWhere = $condiciones ? ' AND ' . implode(' AND ', $condiciones) : '';

        $db  = new DbConection();
        $pdo = $db->openConect();

        // Contar sedes por municipio con indicadores básicos
        $sql = "SELECT
                    mun.municipio                     AS municipio,
                    p.provincia,
                    p.ano,
                    COUNT(p.id)                       AS total_sedes,
                    SUM(COALESCE(p.ninos_focalizados,0)) AS ninos_focalizados,
                    SUM(CASE WHEN p.complemento_ampm = 'Si' THEN 1 ELSE 0 END) AS con_complemento_ampm,
                    SUM(CASE WHEN p.comedor_escolar  = 'Si' THEN 1 ELSE 0 END) AS con_comedor,
                    SUM(CASE WHEN p.acceso_agua      = 'Si' THEN 1 ELSE 0 END) AS con_agua_potable,
                    SUM(CASE WHEN p.acceso_electricidad = 'Si' THEN 1 ELSE 0 END) AS con_electricidad
                FROM tbl_pae p
                LEFT JOIN tbl_ciudades_accion_unificada mun ON mun.codigo_muncipio = p.tbl_municipio_id
                WHERE 1=1
                {$sqlMun}
                {$sqlWhere}
                GROUP BY mun.municipio, p.provincia, p.ano
                ORDER BY total_sedes DESC
                LIMIT :limite";

        $params = array_merge($paramsMun, $paramsExtra, [':limite' => $limite]);
        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->bindValue(':limite', $limite, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();

        if (empty($rows)) {
            return ['nota' => 'No se encontraron registros PAE para los filtros aplicados.', 'registros' => []];
        }

        $totalSedes = array_sum(array_column($rows, 'total_sedes'));
        $totalNinos = array_sum(array_column($rows, 'ninos_focalizados'));

        return [
            'total_municipios'   => count($rows),
            'total_sedes'        => $totalSedes,
            'total_ninos_focalizados' => $totalNinos,
            'registros'          => $rows,
        ];
    }
}
