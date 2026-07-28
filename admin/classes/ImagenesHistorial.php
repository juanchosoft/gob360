<?php
require_once 'DbConection.php';

class ImagenesHistorial
{
    public static function getAntes($filtros)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $ii = $db->getTable('tbl_ingreso_informacion');
            $ciu = $db->getTable('tbl_ciudades');
            $ver = $db->getTable('tbl_vereda');

            $sql = "SELECT $ii.foto1, $ii.foto2, $ii.foto3, $ii.foto4,
                           $ii.dtcreate, $ii.observaciones,
                           $ciu.municipio, $ii.tbl_vereda_id AS vereda
                    FROM $ii
                    INNER JOIN $ciu ON $ii.codigo_municipio = $ciu.codigo_muncipio
                    LEFT JOIN $ver ON $ii.tbl_vereda_id = $ver.id
                    WHERE 1=1";
            $params = [];

            if (empty($filtros['departamento_id']) && empty($filtros['municipio_id']) && empty($filtros['factor_id']) && empty($filtros['fecha_inicial']) && empty($filtros['fecha_final'])) {
                $sql .= " AND $ciu.municipio = ?";
                $params[] = 'BUCARAMANGA';
            } else {
                if (!empty($filtros['departamento_id'])) {
                    $sql .= " AND $ciu.codigo_departamento = ?";
                    $params[] = $filtros['departamento_id'];
                }
                if (!empty($filtros['municipio_id'])) {
                    $sql .= " AND $ii.codigo_municipio = ?";
                    $params[] = $filtros['municipio_id'];
                }
                if (!empty($filtros['fecha_inicial']) && !empty($filtros['fecha_final'])) {
                    $sql .= " AND $ii.dtcreate BETWEEN ? AND ?";
                    $params[] = $filtros['fecha_inicial'];
                    $params[] = $filtros['fecha_final'];
                }
            }

            $sql .= " ORDER BY $ii.dtcreate DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            $db->closeConect();
        }
    }

    public static function getDespues($filtros)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $iia = $db->getTable('tbl_ingreso_informacion_x_actualizacion');
            $ii = $db->getTable('tbl_ingreso_informacion');
            $ciu = $db->getTable('tbl_ciudades');

            $sql = "SELECT $iia.foto_actualizada_1, $iia.foto_actualizada_2,
                           $iia.foto_actualizada_3, $iia.foto_actualizada_4,
                           $iia.dtcreate AS fecha_actualizacion,
                           $iia.observaciones_actualizacion,
                           $ciu.municipio
                    FROM $iia
                    INNER JOIN $ii ON $iia.tbl_ingreso_informacion_id = $ii.id
                    INNER JOIN $ciu ON $ii.codigo_municipio = $ciu.codigo_muncipio
                    WHERE 1=1";
            $params = [];

            if (!empty($filtros['departamento_id'])) {
                $sql .= " AND $ciu.codigo_departamento = ?";
                $params[] = $filtros['departamento_id'];
            }
            if (!empty($filtros['municipio_id'])) {
                $sql .= " AND $ciu.codigo_muncipio = ?";
                $params[] = $filtros['municipio_id'];
            }
            if (!empty($filtros['fecha_inicial']) && !empty($filtros['fecha_final'])) {
                $sql .= " AND $iia.dtcreate BETWEEN ? AND ?";
                $params[] = $filtros['fecha_inicial'];
                $params[] = $filtros['fecha_final'];
            }

            $sql .= " ORDER BY $iia.dtcreate DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            $db->closeConect();
        }
    }

    public static function getDepartamentos()
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $dep = $db->getTable('tbl_departamentos');
            $stmt = $pdo->query("SELECT codigo_departamento AS id, departamento AS nombre FROM $dep");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            $db->closeConect();
        }
    }

    public static function getFactores()
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $fact = $db->getTable('tbl_factores');
            $stmt = $pdo->query("SELECT id, tipo AS nombre FROM $fact");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            $db->closeConect();
        }
    }

    public static function getSantanderId()
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $dep = $db->getTable('tbl_departamentos');
            $stmt = $pdo->prepare("SELECT codigo_departamento FROM $dep WHERE departamento = ?");
            $stmt->execute(['Santander']);
            return $stmt->fetchColumn();
        } finally {
            $db->closeConect();
        }
    }

    public static function getMunicipiosByDepartamento($departamentoId)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $ciu = $db->getTable('tbl_ciudades');
            $stmt = $pdo->prepare("SELECT codigo_muncipio, municipio FROM $ciu WHERE codigo_departamento = ?");
            $stmt->execute([$departamentoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            $db->closeConect();
        }
    }
}
