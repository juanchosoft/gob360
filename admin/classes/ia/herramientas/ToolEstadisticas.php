<?php

/**
 * Herramienta IA: estadísticas globales del sistema.
 * Solo disponible para roles con vista completa del departamento.
 */
final class ToolEstadisticas
{
    /**
     * Devuelve conteos globales del sistema para contexto general.
     * Tool: estadisticas_sistema
     */
    public static function estadisticasSistema(array $input): array
    {
        $scope = IaScope::actual();

        $db  = new DbConection();
        $pdo = $db->openConect();

        $anio = (int) Util::getAnioActual();
        $stats = ['anio_referencia' => $anio, 'alcance' => $scope['rol']];

        if ($scope['municipio_id'] !== null) {
            // Vista alcalde: estadísticas de su municipio
            $stats = array_merge($stats, self::statsMunicipio($pdo, $scope['municipio_id']));
        } elseif ($scope['secretaria_id'] !== null && $scope['secretaria_id'] > 0) {
            // Vista secretaría
            $stats = array_merge($stats, self::statsSecretaria($pdo, $scope['secretaria_id']));
        } else {
            // Vista global
            $stats = array_merge($stats, self::statsGlobal($pdo, $anio));
        }

        $db->closeConect();
        return $stats;
    }

    private static function statsGlobal(\PDO $pdo, int $anio): array
    {
        // Queries sin parámetros
        $staticQueries = [
            'total_municipios_activos' =>
                "SELECT COUNT(DISTINCT tbl_municipio_id) FROM tbl_compromisos",
            'total_compromisos' =>
                "SELECT COUNT(*) FROM tbl_compromisos",
            'compromisos_cumplidos' =>
                "SELECT COUNT(*) FROM tbl_compromisos WHERE cumplimiento LIKE '%Cumplido%' AND cumplimiento NOT LIKE '%Sin Cumplir%'",
            'total_proyectos_alcaldias' =>
                "SELECT COUNT(*) FROM tbl_proyectos_alcaldias",
            'total_proyectos_secretarias' =>
                "SELECT COUNT(*) FROM tbl_proyectos",
        ];

        // Queries con año como parámetro (prepared statements)
        $anioQueries = [
            'visitas_anio' =>
                "SELECT COUNT(*) FROM tbl_visitas WHERE YEAR(created_at) = ? AND estado != 'Seleccione'",
            'sedes_pae' =>
                "SELECT COUNT(*) FROM tbl_pae WHERE ano = ?",
            'actividades_gestion_social' =>
                "SELECT COUNT(*) FROM tbl_gestora_aspas WHERE YEAR(dtcreate) = ?",
        ];

        $resultado = [];
        foreach ($staticQueries as $clave => $sql) {
            try {
                $resultado[$clave] = (int) $pdo->query($sql)->fetchColumn();
            } catch (\Throwable $e) {
                $resultado[$clave] = null;
            }
        }
        foreach ($anioQueries as $clave => $sql) {
            try {
                $st = $pdo->prepare($sql);
                $st->execute([$anio]);
                $resultado[$clave] = (int) $st->fetchColumn();
            } catch (\Throwable $e) {
                $resultado[$clave] = null;
            }
        }

        // Porcentaje de cumplimiento
        if ($resultado['total_compromisos'] > 0) {
            $resultado['porcentaje_cumplimiento'] = round(
                ($resultado['compromisos_cumplidos'] / $resultado['total_compromisos']) * 100, 1
            );
        }

        return $resultado;
    }

    private static function statsMunicipio(\PDO $pdo, string $municipioId): array
    {
        $queries = [
            // cumplimiento es texto libre en tbl_compromisos — comparar con LIKE
            'compromisos_cumplidos' =>
                "SELECT COUNT(*) FROM tbl_compromisos WHERE tbl_municipio_id = ? AND cumplimiento LIKE '%Cumplido%' AND cumplimiento NOT LIKE '%Sin Cumplir%'",
            'compromisos_en_tramite' =>
                "SELECT COUNT(*) FROM tbl_compromisos WHERE tbl_municipio_id = ? AND cumplimiento LIKE '%En Trámite%'",
            'compromisos_sin_cumplir' =>
                "SELECT COUNT(*) FROM tbl_compromisos WHERE tbl_municipio_id = ? AND cumplimiento LIKE '%Sin Cumplir%'",
            'proyectos_alcaldia' =>
                "SELECT COUNT(*) FROM tbl_proyectos_alcaldias WHERE tbl_municipio_id = ?",
        ];

        $resultado = [];
        foreach ($queries as $clave => $sql) {
            try {
                $st = $pdo->prepare($sql);
                $st->execute([$municipioId]);
                $resultado[$clave] = (int) $st->fetchColumn();
            } catch (\Throwable $e) {
                $resultado[$clave] = null;
            }
        }
        return $resultado;
    }

    private static function statsSecretaria(\PDO $pdo, int $secretariaId): array
    {
        $queries = [
            'proyectos_secretaria' =>
                "SELECT COUNT(*) FROM tbl_proyectos WHERE tbl_secretarias_id = ?",
            'compromisos_secretaria' =>
                "SELECT COUNT(*) FROM tbl_compromisos WHERE tbl_secretarias_id = ?",
        ];

        $resultado = [];
        foreach ($queries as $clave => $sql) {
            try {
                $st = $pdo->prepare($sql);
                $st->execute([$secretariaId]);
                $resultado[$clave] = (int) $st->fetchColumn();
            } catch (\Throwable $e) {
                $resultado[$clave] = null;
            }
        }
        return $resultado;
    }
}
