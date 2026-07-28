<?php

require_once __DIR__ . '/SecretariasMunicipios.php';
require_once __DIR__ . '/DesarrolloAlcalde.php';
require_once __DIR__ . '/ComponenteMunicipios.php';
require_once __DIR__ . '/Ingreso_proyectos_secretarias.php';

class DashboardAlcalde
{
    public static function getNombreMunicipio($codigoMunicipio)
    {
        if (empty($codigoMunicipio)) return '';
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $stmt = $pdo->prepare("SELECT municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE CAST(codigo_muncipio AS CHAR) = :c LIMIT 1");
            $stmt->execute([':c' => (string)$codigoMunicipio]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? (string)$res['municipio'] : '';
        } catch (Exception $e) {
            return '';
        } finally {
            $db->closeConect();
        }
    }

    public static function getTodasSecretarias($codigoMunicipio)
    {
        if (empty($codigoMunicipio)) return [];
        $arr = SecretariasMunicipios::getByMunicipio(['codigo_municipio' => $codigoMunicipio]);
        return $arr['output']['response'] ?? [];
    }

    public static function getResumenProyectos($codigoMunicipio)
    {
        if (empty($codigoMunicipio)) {
            return ['total' => 0, 'valor_total' => 0, 'por_estado' => ['estudios'=>0,'formulacion'=>0,'ejecucion'=>0,'terminados'=>0,'entregados'=>0]];
        }
        try {
            $proyectos = Proyectos_Secretarias::getProyectosByMunicipio($codigoMunicipio);
            $total = count($proyectos);
            $valor = 0;
            $estados = ['estudios'=>0,'formulacion'=>0,'ejecucion'=>0,'terminados'=>0,'entregados'=>0];

            foreach ($proyectos as $p) {
                $valor += (float)($p['valor_proyecto'] ?? 0);
                $nombre = strtolower($p['estado_proyecto'] ?? '');
                if (strpos($nombre, 'estudio') !== false || strpos($nombre, 'previo') !== false) $estados['estudios']++;
                elseif (strpos($nombre, 'formulaci') !== false) $estados['formulacion']++;
                elseif (strpos($nombre, 'ejecuci') !== false || strpos($nombre, 'trámite') !== false) $estados['ejecucion']++;
                elseif (strpos($nombre, 'terminado') !== false) $estados['terminados']++;
                elseif (strpos($nombre, 'entregado') !== false) $estados['entregados']++;
                else $estados['ejecucion']++;
            }
            return ['total' => $total, 'valor_total' => $valor, 'por_estado' => $estados];
        } catch (Throwable $e) {
            return ['total' => 0, 'valor_total' => 0, 'por_estado' => ['estudios'=>0,'formulacion'=>0,'ejecucion'=>0,'terminados'=>0,'entregados'=>0]];
        }
    }

    public static function getTopSecretariasInversion($codigoMunicipio, $limit = 8)
    {
        if (empty($codigoMunicipio)) return [];
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $tp = $db->getTable('tbl_proyectos_planeacion_alcaldia');
            $ts = $db->getTable('tbl_secretarias_municipios');
            $c = (string)$codigoMunicipio;
            $stmt = $pdo->prepare("
                SELECT s.id, s.secretaria,
                       COUNT(p.id) as total_proyectos,
                       COALESCE(SUM(p.valor_proyecto), 0) as valor_total
                FROM {$ts} s
                INNER JOIN {$tp} p ON p.tbl_secretarias_id = s.id AND CAST(p.tbl_municipio_id AS CHAR) = :c
                WHERE CAST(s.codigo_municipio AS CHAR) = :c2 AND s.habilitado = 'si'
                GROUP BY s.id, s.secretaria
                ORDER BY valor_total DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':c', $c);
            $stmt->bindValue(':c2', $c);
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        } finally {
            $db->closeConect();
        }
    }

    public static function getResumenVisitas($codigoMunicipio)
    {
        $r = ['total' => 0, 'veredas_visitadas' => 0, 'veredas_totales' => 0, 'veredas_restantes' => 0];
        if (empty($codigoMunicipio)) return $r;
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $tv = $db->getTable('tbl_visitas_alcalde');
            $tve = $db->getTable('tbl_vereda');
            $c = (string)$codigoMunicipio;

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$tv} WHERE CAST(tbl_municipio_id AS CHAR) = :c AND tipo_registro = 'Visita'");
            $stmt->execute([':c' => $c]);
            $r['total'] = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT va.tbl_vereda_id) FROM {$tv} va INNER JOIN {$tve} v ON va.tbl_vereda_id = v.id WHERE va.tipo_registro='Visita' AND va.tbl_vereda_id IS NOT NULL AND CAST(v.municipio_id AS CHAR) = :c");
            $stmt->execute([':c' => $c]);
            $r['veredas_visitadas'] = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$tve} WHERE CAST(municipio_id AS CHAR) = :c");
            $stmt->execute([':c' => $c]);
            $r['veredas_totales'] = (int)$stmt->fetchColumn();
            $r['veredas_restantes'] = $r['veredas_totales'] - $r['veredas_visitadas'];
        } catch (Exception $e) {
        } finally {
            $db->closeConect();
        }
        return $r;
    }

    public static function getTotalCompromisos($codigoMunicipio)
    {
        if (empty($codigoMunicipio)) return 0;
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . $db->getTable('tbl_compromisos_alcalde') . " WHERE CAST(tbl_municipio_id AS CHAR) = :c");
            $stmt->execute([':c' => (string)$codigoMunicipio]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        } finally {
            $db->closeConect();
        }
    }

    public static function getResumenPlanDesarrollo($codigoMunicipio)
    {
        $arr = DesarrolloAlcalde::getAll([])['output']['response'] ?? [];
        $secretarias = [];
        foreach ($arr as $m) {
            $secName = $m['secretaria'] ?? 'Sin secretaría';
            if (!isset($secretarias[$secName])) {
                $secretarias[$secName] = ['metas' => 0, 'secretaria_id' => (int)($m['tbl_secretaria_id'] ?? 0)];
            }
            $secretarias[$secName]['metas']++;
        }
        return ['total_metas' => count($arr), 'total_secretarias' => count($secretarias), 'secretarias' => $secretarias];
    }

    public static function getComponentes($codigoMunicipio)
    {
        try {
            $arr = ComponenteMunicipios::getComponentesPorMunicipio($codigoMunicipio)['output']['response'] ?? [];
            return is_array($arr) ? $arr : [];
        } catch (Throwable $e) { return []; }
    }

    public static function getProyectosPorSecretaria($codigoMunicipio)
    {
        if (empty($codigoMunicipio)) return [];
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $tp = $db->getTable('tbl_proyectos_planeacion_alcaldia');
            $ts = $db->getTable('tbl_secretarias_municipios');
            $c = (string)$codigoMunicipio;
            $stmt = $pdo->prepare("
                SELECT s.id as secretaria_id, s.secretaria,
                       COUNT(p.id) as total_proyectos,
                       COALESCE(SUM(p.valor_proyecto), 0) as valor_total,
                       GROUP_CONCAT(p.estado_proyecto SEPARATOR '|') as estados
                FROM {$ts} s
                LEFT JOIN {$tp} p ON p.tbl_secretarias_id = s.id AND CAST(p.tbl_municipio_id AS CHAR) = :c
                WHERE CAST(s.codigo_municipio AS CHAR) = :c2 AND s.habilitado = 'si'
                GROUP BY s.id, s.secretaria
                ORDER BY s.secretaria ASC
            ");
            $stmt->bindValue(':c', $c);
            $stmt->bindValue(':c2', $c);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        } finally {
            $db->closeConect();
        }
    }

    public static function getProyectosConSecretaria($codigoMunicipio)
    {
        if (empty($codigoMunicipio)) return [];
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $tp = $db->getTable('tbl_proyectos_planeacion_alcaldia');
            $ts = $db->getTable('tbl_secretarias_municipios');
            $c = (string)$codigoMunicipio;
            $stmt = $pdo->prepare("
                SELECT p.id, p.proyecto, p.valor_proyecto, p.estado_proyecto, p.fecha,
                       s.secretaria, s.id as secretaria_id
                FROM {$tp} p
                INNER JOIN {$ts} s ON p.tbl_secretarias_id = s.id AND CAST(s.codigo_municipio AS CHAR) = :c
                WHERE CAST(p.tbl_municipio_id AS CHAR) = :c2
                ORDER BY s.secretaria ASC, p.valor_proyecto DESC
            ");
            $stmt->bindValue(':c', $c);
            $stmt->bindValue(':c2', $c);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        } finally {
            $db->closeConect();
        }
    }

    public static function getTopProyectos($codigoMunicipio, $limit = 5)
    {
        if (empty($codigoMunicipio)) return [];
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $tp = $db->getTable('tbl_proyectos_planeacion_alcaldia');
            $c = (string)$codigoMunicipio;
            $stmt = $pdo->prepare("
                SELECT id, proyecto, valor_proyecto, estado_proyecto, fecha
                FROM {$tp} p
                WHERE CAST(p.tbl_municipio_id AS CHAR) = :c
                ORDER BY valor_proyecto DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':c', $c);
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        } finally {
            $db->closeConect();
        }
    }

    public static function getVisitasList($codigoMunicipio)
    {
        if (empty($codigoMunicipio)) return [];
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $tv = $db->getTable('tbl_visitas_alcalde');
            $tve = $db->getTable('tbl_vereda');
            $c = (string)$codigoMunicipio;
            $stmt = $pdo->prepare("
                SELECT v.id, v.date, v.tipo_visita, v.descripcion_hecho, v.compromisos,
                       v.consecuencia, ve.nombre_vereda, v.img
                FROM {$tv} v
                LEFT JOIN {$tve} ve ON v.tbl_vereda_id = ve.id
                WHERE CAST(v.tbl_municipio_id AS CHAR) = :c AND v.tipo_registro = 'Visita'
                ORDER BY v.date DESC
            ");
            $stmt->execute([':c' => $c]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        } finally {
            $db->closeConect();
        }
    }
}
