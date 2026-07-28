<?php
require_once 'DbConection.php';
require_once 'Util.php';

class EstadisticasBD
{
    public static function get()
    {
        $departamento = Util::getDepartamentoPrincipal();
        $db = new DbConection();
        try {
            $pdo = $db->openConect();

            $ing = $db->getTable('tbl_ingreso_informacion');
            $ingAct = $db->getTable('tbl_ingreso_informacion_x_actualizacion');
            $fact = $db->getTable('tbl_factores');
            $area = $db->getTable('tbl_area');
            $pilar = $db->getTable('tbl_pilar');
            $vereda = $db->getTable('tbl_vereda');
            $ciu = $db->getTable('tbl_ciudades_accion_unificada');
            $dep = $db->getTable('tbl_departamentos');

            $qTotales = "
                SELECT 'tbl_ingreso_informacion' AS tabla, COUNT(*) AS total FROM $ing
                UNION ALL SELECT 'tbl_ingreso_informacion_x_actualizacion', COUNT(*) FROM $ingAct
                UNION ALL SELECT 'tbl_factores', COUNT(*) FROM $fact
                UNION ALL SELECT 'tbl_area', COUNT(*) FROM $area
                UNION ALL SELECT 'tbl_pilar', COUNT(*) FROM $pilar
                UNION ALL SELECT 'tbl_vereda', COUNT(*) FROM $vereda
                UNION ALL SELECT 'tbl_ciudades_accion_unificada', COUNT(*) FROM $ciu
                UNION ALL SELECT 'tbl_departamentos', COUNT(*) FROM $dep";
            $stmt = $pdo->prepare($qTotales);
            $stmt->execute();
            $totales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $qPorMunicipio = "
                SELECT c.codigo_muncipio, c.municipio,
                       COUNT(DISTINCT i.id) AS total_ingresos,
                       COUNT(DISTINCT a.id) AS total_actualizaciones,
                       COUNT(DISTINCT f.id) AS total_factores_distintos
                FROM $ciu c
                LEFT JOIN $vereda v ON c.codigo_muncipio = v.municipio_id
                LEFT JOIN $ing i ON v.id = i.tbl_vereda_id
                LEFT JOIN $ingAct a ON a.tbl_ingreso_informacion_id = i.id
                LEFT JOIN $fact f ON i.tbl_factor_id = f.id
                WHERE c.codigo_departamento = $departamento
                GROUP BY c.codigo_muncipio, c.municipio
                ORDER BY c.municipio ASC";

            $stmt = $pdo->prepare($qPorMunicipio);
            $stmt->execute();
            $porMunicipio = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $granTotal = array_sum(array_column($totales, 'total'));
            $totalIngresos = array_sum(array_column($porMunicipio, 'total_ingresos'));
            $totalActualizaciones = array_sum(array_column($porMunicipio, 'total_actualizaciones'));
            $municipiosConDatos = count(array_filter($porMunicipio, fn($m) => $m['total_ingresos'] > 0));

            return [
                'valid' => true,
                'totales' => $totales,
                'por_municipio' => $porMunicipio,
                'resumen' => [
                    'gran_total' => $granTotal,
                    'total_ingresos_departamento' => $totalIngresos,
                    'total_actualizaciones_departamento' => $totalActualizaciones,
                    'municipios_con_datos' => $municipiosConDatos,
                    'total_municipios' => count($porMunicipio)
                ]
            ];
        } catch (Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        } finally {
            $db->closeConect();
        }
    }
}
