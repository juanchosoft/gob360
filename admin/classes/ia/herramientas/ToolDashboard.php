<?php

/**
 * Herramienta IA: resumen del dashboard según el rol del usuario.
 */
final class ToolDashboard
{
    /**
     * Devuelve indicadores clave del dashboard adaptados al rol del usuario.
     * Tool: resumen_dashboard
     */
    public static function resumenDashboard(array $input): array
    {
        $scope = IaScope::actual();
        $db    = new DbConection();
        $pdo   = $db->openConect();

        $resultado = ['rol' => $scope['rol']];

        try {
            if ($scope['municipio_id'] !== null) {
                // Vista alcalde
                $resultado = array_merge($resultado, self::datosAlcalde($pdo, $scope['municipio_id']));
            } elseif ($scope['secretaria_id'] !== null && $scope['secretaria_id'] > 0) {
                // Vista secretaría
                $resultado = array_merge($resultado, self::datosSecretaria($pdo, $scope['secretaria_id']));
            } else {
                // Vista admin/gobernador: totales departamentales
                $resultado = array_merge($resultado, self::datosAdmin($pdo));
            }
        } catch (\Throwable $e) {
            $resultado['nota'] = 'No se pudo obtener todos los indicadores del dashboard.';
        }

        $db->closeConect();
        return $resultado;
    }

    private static function datosAlcalde(\PDO $pdo, string $municipioId): array
    {
        // Compromisos del gobernador con el municipio (agrupados por cumplimiento)
        $st = $pdo->prepare(
            "SELECT cumplimiento, COUNT(*) AS total
               FROM tbl_compromisos
              WHERE tbl_municipio_id = :mid
              GROUP BY cumplimiento"
        );
        $st->execute([':mid' => $municipioId]);
        $compromisos = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $compromisos[$row['cumplimiento']] = (int) $row['total'];
        }

        // Proyectos del municipio
        $st2 = $pdo->prepare(
            "SELECT COUNT(*) AS total FROM tbl_proyectos_alcaldias WHERE tbl_municipio_id = :mid"
        );
        $st2->execute([':mid' => $municipioId]);
        $proyectos = (int) ($st2->fetchColumn() ?: 0);

        return [
            'compromisos' => $compromisos,
            'proyectos_alcaldia' => $proyectos,
        ];
    }

    private static function datosSecretaria(\PDO $pdo, int $secretariaId): array
    {
        // Proyectos de la secretaría
        $st = $pdo->prepare(
            "SELECT COUNT(*) AS total FROM tbl_proyectos WHERE tbl_secretarias_id = :sid"
        );
        $st->execute([':sid' => $secretariaId]);
        $proyectos = (int) ($st->fetchColumn() ?: 0);

        return ['proyectos_secretaria' => $proyectos];
    }

    private static function datosAdmin(\PDO $pdo): array
    {
        // Total compromisos por cumplimiento
        $st = $pdo->prepare(
            "SELECT cumplimiento, COUNT(*) AS total FROM tbl_compromisos GROUP BY cumplimiento"
        );
        $st->execute();
        $compromisos = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $compromisos[$row['cumplimiento']] = (int) $row['total'];
        }

        // Total municipios con seguimiento
        $st2 = $pdo->prepare(
            "SELECT COUNT(DISTINCT tbl_municipio_id) AS total FROM tbl_compromisos"
        );
        $st2->execute();
        $municipios = (int) ($st2->fetchColumn() ?: 0);

        // Total visitas del año (excluyendo el valor basura 'Seleccione')
        $anio = Util::getAnioActual();
        $st3  = $pdo->prepare(
            "SELECT COUNT(*) AS total FROM tbl_visitas WHERE YEAR(created_at) = :anio AND estado != 'Seleccione'"
        );
        $st3->execute([':anio' => $anio]);
        $visitas = (int) ($st3->fetchColumn() ?: 0);

        return [
            'compromisos'            => $compromisos,
            'municipios_con_seguimiento' => $municipios,
            'visitas_anio_actual'    => $visitas,
            'anio'                   => $anio,
        ];
    }
}
