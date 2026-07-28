<?php

class SeguimientoAlcaldias
{
    public static function getResumenMunicipios()
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        $cau = $db->getTable('tbl_ciudades_accion_unificada');
        $proy = $db->getTable('tbl_proyectos_planeacion_alcaldia');
        $plan = $db->getTable('tbl_plandesarrollo_alcalde');
        $visitas = $db->getTable('tbl_visitas_alcalde');
        $compromisos = $db->getTable('tbl_compromisos_alcalde');
        $componentes = $db->getTable('tbl_componente_municipios');
        $secretarias = $db->getTable('tbl_secretarias_municipios');

        try {
            $q = "SELECT
                    cau.codigo_muncipio,
                    cau.municipio,
                    cau.d,
                    cau.path,
                    cau.nombre_mapa,
                    cau.color as color_default,
                    COALESCE(p.total_proyectos, 0) as total_proyectos,
                    COALESCE(pl.total_metas, 0) as total_metas,
                    COALESCE(v.total_visitas, 0) as total_visitas,
                    COALESCE(c.total_compromisos, 0) as total_compromisos,
                    COALESCE(cmp.total_componentes, 0) as total_componentes,
                    COALESCE(s.total_secretarias, 0) as total_secretarias
                FROM {$cau} cau
                LEFT JOIN (SELECT CAST(tbl_municipio_id AS CHAR) as m, COUNT(*) as total_proyectos FROM {$proy} GROUP BY CAST(tbl_municipio_id AS CHAR)) p ON CAST(cau.codigo_muncipio AS CHAR) = p.m
                LEFT JOIN (SELECT tbl_municipio_id as m, COUNT(*) as total_metas FROM {$plan} GROUP BY tbl_municipio_id) pl ON CAST(cau.codigo_muncipio AS CHAR) = CAST(pl.m AS CHAR)
                LEFT JOIN (SELECT CAST(tbl_municipio_id AS CHAR) as m, COUNT(*) as total_visitas FROM {$visitas} WHERE tipo_registro='Visita' GROUP BY CAST(tbl_municipio_id AS CHAR)) v ON CAST(cau.codigo_muncipio AS CHAR) = v.m
                LEFT JOIN (SELECT CAST(tbl_municipio_id AS CHAR) as m, COUNT(*) as total_compromisos FROM {$compromisos} GROUP BY CAST(tbl_municipio_id AS CHAR)) c ON CAST(cau.codigo_muncipio AS CHAR) = c.m
                LEFT JOIN (SELECT codigo_municipio as m, COUNT(*) as total_componentes FROM {$componentes} WHERE habilitado='si' GROUP BY codigo_municipio) cmp ON CAST(cau.codigo_muncipio AS CHAR) = CAST(cmp.m AS CHAR)
                LEFT JOIN (SELECT CAST(codigo_municipio AS CHAR) as m, COUNT(*) as total_secretarias FROM {$secretarias} WHERE habilitado='si' GROUP BY CAST(codigo_municipio AS CHAR)) s ON CAST(cau.codigo_muncipio AS CHAR) = s.m
                WHERE cau.codigo_departamento = '68'
                ORDER BY cau.municipio ASC";

            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$r) {
                $total = (int)$r['total_proyectos'] + (int)$r['total_metas'] + (int)$r['total_visitas']
                       + (int)$r['total_compromisos'] + (int)$r['total_componentes'] + (int)$r['total_secretarias'];
                $r['total_general'] = $total;

                if ($total >= 30)       { $r['color'] = '#2E7D32'; $r['clase'] = 'estable'; }
                elseif ($total >= 15)   { $r['color'] = '#1E66F5'; $r['clase'] = 'info'; }
                elseif ($total >= 5)    { $r['color'] = '#FB8C00'; $r['clase'] = 'alto'; }
                elseif ($total > 0)     { $r['color'] = '#E53935'; $r['clase'] = 'critico'; }
                else                    { $r['color'] = '#334155'; $r['clase'] = 'neutro'; }
            }

            $db->closeConect();
            return ['output' => ['valid' => true, 'response' => $rows]];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general($e->getMessage());
        }
    }

    public static function getTotales($rows)
    {
        $r = ['municipios' => 0, 'con_datos' => 0, 'proyectos' => 0, 'metas' => 0, 'visitas' => 0, 'compromisos' => 0, 'componentes' => 0, 'secretarias' => 0];
        foreach ($rows as $m) {
            $r['municipios']++;
            $r['proyectos'] += (int)$m['total_proyectos'];
            $r['metas'] += (int)$m['total_metas'];
            $r['visitas'] += (int)$m['total_visitas'];
            $r['compromisos'] += (int)$m['total_compromisos'];
            $r['componentes'] += (int)$m['total_componentes'];
            $r['secretarias'] += (int)$m['total_secretarias'];
            if ((int)$m['total_general'] > 0) $r['con_datos']++;
        }
        return $r;
    }
}
