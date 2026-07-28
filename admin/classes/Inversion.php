<?php

/**
 * Gestión de inversiones en seguridad por municipio.
 */
class Inversion
{
    public function __construct() {}

    /**
     * Guarda una inversión con sus municipios relacionados.
     * Recibe $_POST y $_FILES directamente.
     */
    public static function save(array $post, array $files): array
    {
        $tipo_seccion = $post['tipo_seccion'] ?? null;
        $titulo       = $post['titulo']       ?? null;
        $descripcion  = $post['descripcion']  ?? null;
        $cantidad     = $post['cantidad']     ?? null;
        $valor        = $post['valor']        ?? 0;
        $fecha        = $post['fecha']        ?? null;
        $institucion  = $post['institucion']  ?? null;
        $direccion    = $post['direccion']    ?? null;
        $usuario_id   = $post['usuario_id']   ?? 1;

        if (!$tipo_seccion || !$titulo || !$fecha) {
            return Util::error_missing_data_description('tipo_seccion, titulo y fecha son obligatorios.');
        }

        if (!$institucion) {
            return Util::error_missing_data_description('Institución requerida.');
        }

        if (!$direccion) {
            return Util::error_missing_data_description('Dirección requerida.');
        }

        $municipios = isset($post['municipios']) ? (array)$post['municipios'] : [];
        if (empty($municipios)) {
            return Util::error_missing_data_description('Debe seleccionar al menos un municipio.');
        }

        $valor = (int) str_replace('.', '', (string) $valor);

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            $imagenNombre = self::subirImagen($files['imagen'] ?? []);

            $nombresMunicipios = self::obtenerNombresMunicipios($municipios);

            $stmt = $pdo->prepare("
                INSERT INTO " . $db->getTable('tbl_inversion_seguridad') . "
                    (departamento_id, tipo_seccion, titulo, institucion, direccion,
                     descripcion, cantidad, valor, fecha, tbl_usuario_id,
                     imagen, estado, created_at, municipio)
                VALUES (68, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), ?)
            ");

            $stmt->execute([
                $tipo_seccion,
                $titulo,
                $institucion,
                $direccion,
                $descripcion,
                $cantidad,
                $valor,
                $fecha,
                $usuario_id,
                $imagenNombre,
                $nombresMunicipios,
            ]);

            $inversion_id = $pdo->lastInsertId();

            $stmtPivot = $pdo->prepare("
                INSERT INTO " . $db->getTable('tbl_inversion_municipios') . "
                    (inversion_id, municipio_id)
                VALUES (?, ?)
            ");
            foreach ($municipios as $codigo) {
                $stmtPivot->execute([$inversion_id, $codigo]);
            }

            $pdo->commit();
            $db->closeConect();

            return ['output' => ['valid' => true, 'response' => ['id' => $inversion_id]]];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $db->closeConect();
            return Util::error_general($e->getMessage());
        }
    }

    private static function obtenerNombresMunicipios(array $codigos): string
    {
        $db  = new DbConection();
        $pdo = $db->openConect();
        $placeholders = implode(',', array_fill(0, count($codigos), '?'));
        $stmt = $pdo->prepare("SELECT municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio IN ($placeholders)");
        $stmt->execute($codigos);
        $nombres = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $db->closeConect();
        return implode(', ', $nombres);
    }

    /**
     * Retorna KPIs, distribución por tipo y top municipios para el dashboard.
     */
    public static function getDashboardData(): array
    {
        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $t = $db->getTable('tbl_inversion_seguridad');

            $pm = $db->getTable('tbl_inversion_municipios');
            $cu = $db->getTable('tbl_ciudades_accion_unificada');
            $kpi = $pdo->query("
                SELECT
                    COUNT(*)                  AS total_registros,
                    SUM(valor)                AS total_inversion,
                    (SELECT COUNT(DISTINCT municipio_id) FROM $pm pm2 JOIN $t i2 ON pm2.inversion_id = i2.id WHERE i2.estado = 1) AS municipios
                FROM $t
                WHERE estado = 1
            ")->fetch(PDO::FETCH_ASSOC);

            $tipo = $pdo->query("
                SELECT tipo_seccion, SUM(valor) AS total
                FROM $t
                WHERE estado = 1
                GROUP BY tipo_seccion
            ")->fetchAll(PDO::FETCH_ASSOC);

            $municipio = $pdo->query("
                SELECT cu.municipio, SUM(i.valor) AS total
                FROM $t i
                JOIN $pm pm ON pm.inversion_id = i.id
                JOIN $cu cu ON pm.municipio_id = cu.codigo_muncipio
                WHERE i.estado = 1
                GROUP BY cu.municipio
                ORDER BY total DESC
                LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid'    => true,
                    'response' => [
                        'kpi'       => $kpi,
                        'tipo'      => $tipo,
                        'municipio' => $municipio,
                    ],
                ],
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general($e->getMessage());
        }
    }

    /**
     * Lista todas las inversiones activas (estado=1), paginadas.
     */
    public static function getAllServerSide(array $params = []): array
    {
        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $t = $db->getTable('tbl_inversion_seguridad');

            $draw    = intval($params['draw'] ?? 1);
            $start   = intval($params['start'] ?? 0);
            $length  = intval($params['length'] ?? 10);
            $search  = $params['search']['value'] ?? '';

            $orderColIndex = intval($params['order'][0]['column'] ?? 1);
            $orderDir      = ($params['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

            $columns = ['id', 'id', 'fecha', 'tipo_seccion', 'titulo', 'institucion', 'municipios_str', 'direccion', 'valor'];
            $orderCol = $columns[$orderColIndex] ?? 'id';

            $where = "WHERE i.estado = 1";
            if ($search) {
                $search = addslashes($search);
                $where .= " AND (i.id LIKE '%$search%' OR i.titulo LIKE '%$search%' OR i.institucion LIKE '%$search%' OR i.direccion LIKE '%$search%' OR i.tipo_seccion LIKE '%$search%' OR i.fecha LIKE '%$search%')";
            }

            $totalStmt = $pdo->query("SELECT COUNT(*) FROM $t i WHERE i.estado = 1");
            $recordsTotal = intval($totalStmt->fetchColumn());

            $filteredStmt = $pdo->query("SELECT COUNT(*) FROM $t i $where");
            $recordsFiltered = intval($filteredStmt->fetchColumn());

            $pm = $db->getTable('tbl_inversion_municipios');
            $cu = $db->getTable('tbl_ciudades_accion_unificada');
            $sql = "SELECT i.id, i.tipo_seccion, i.titulo, i.institucion, i.direccion, i.valor, i.fecha, i.descripcion, i.cantidad, i.imagen, i.created_at, i.municipio,
                           (SELECT GROUP_CONCAT(cu2.municipio ORDER BY cu2.municipio SEPARATOR ', ')
                            FROM {$pm} pm2
                            LEFT JOIN {$cu} cu2 ON pm2.municipio_id = cu2.codigo_muncipio
                            WHERE pm2.inversion_id = i.id) AS municipios_str
                    FROM $t i $where ORDER BY $orderCol $orderDir LIMIT $length OFFSET $start";
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'draw'              => $draw,
                'recordsTotal'      => $recordsTotal,
                'recordsFiltered'   => $recordsFiltered,
                'data'              => $rows
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return [
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Retorna un registro por ID con sus municipios relacionados.
     */
    public static function getById(array $post): array
    {
        $id = isset($post['id']) ? (int)$post['id'] : 0;
        if (!$id) return Util::error_missing_data_description('ID requerido.');

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $t = $db->getTable('tbl_inversion_seguridad');
            $stmt = $pdo->prepare("SELECT * FROM $t WHERE id = ? AND estado = 1 LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $db->closeConect();
                return Util::error_general('Registro no encontrado.');
            }

            $pivot = $db->getTable('tbl_inversion_municipios');
            $stm2 = $pdo->prepare("SELECT municipio_id FROM $pivot WHERE inversion_id = ?");
            $stm2->execute([$id]);
            $row['municipios'] = $stm2->fetchAll(PDO::FETCH_COLUMN);

            $db->closeConect();

            return ['output' => ['valid' => true, 'response' => $row]];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general($e->getMessage());
        }
    }

    /**
     * Actualiza una inversión existente.
     */
    public static function update(array $post, array $files): array
    {
        $id          = isset($post['id']) ? (int)$post['id'] : 0;
        $tipo_seccion = $post['tipo_seccion'] ?? null;
        $titulo       = $post['titulo']       ?? null;
        $descripcion  = $post['descripcion']  ?? null;
        $cantidad     = $post['cantidad']     ?? null;
        $valor        = $post['valor']        ?? 0;
        $fecha        = $post['fecha']        ?? null;
        $institucion  = $post['institucion']  ?? null;
        $direccion    = $post['direccion']    ?? null;

        if (!$id)           return Util::error_missing_data_description('ID requerido.');
        if (!$tipo_seccion) return Util::error_missing_data_description('tipo_seccion requerido.');
        if (!$titulo)       return Util::error_missing_data_description('titulo requerido.');
        if (!$fecha)        return Util::error_missing_data_description('fecha requerida.');
        if (!$institucion)  return Util::error_missing_data_description('Institución requerida.');
        if (!$direccion)    return Util::error_missing_data_description('Dirección requerida.');

        $municipios = isset($post['municipios']) ? (array)$post['municipios'] : [];
        if (empty($municipios)) {
            return Util::error_missing_data_description('Debe seleccionar al menos un municipio.');
        }

        $valor = (int) str_replace('.', '', (string) $valor);

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            $t           = $db->getTable('tbl_inversion_seguridad');
            $imagenNueva = self::subirImagen($files['imagen'] ?? []);

            $nombresMunicipios = self::obtenerNombresMunicipios($municipios);

            if ($imagenNueva) {
                $stmt = $pdo->prepare("
                    UPDATE $t SET tipo_seccion=?, titulo=?, institucion=?, direccion=?,
                        descripcion=?, cantidad=?, valor=?, fecha=?, imagen=?, municipio=?
                    WHERE id=? AND estado=1
                ");
                $stmt->execute([
                    $tipo_seccion, $titulo, $institucion, $direccion,
                    $descripcion, $cantidad, $valor, $fecha, $imagenNueva, $nombresMunicipios, $id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE $t SET tipo_seccion=?, titulo=?, institucion=?, direccion=?,
                        descripcion=?, cantidad=?, valor=?, fecha=?, municipio=?
                    WHERE id=? AND estado=1
                ");
                $stmt->execute([
                    $tipo_seccion, $titulo, $institucion, $direccion,
                    $descripcion, $cantidad, $valor, $fecha, $nombresMunicipios, $id
                ]);
            }

            $pivot = $db->getTable('tbl_inversion_municipios');
            $pdo->prepare("DELETE FROM $pivot WHERE inversion_id = ?")->execute([$id]);

            $stmtPivot = $pdo->prepare("INSERT INTO $pivot (inversion_id, municipio_id) VALUES (?, ?)");
            foreach ($municipios as $codigo) {
                $stmtPivot->execute([$id, $codigo]);
            }

            $pdo->commit();
            $db->closeConect();
            return ['output' => ['valid' => true, 'response' => ['id' => $id]]];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $db->closeConect();
            return Util::error_general($e->getMessage());
        }
    }

    /**
     * Eliminación lógica (estado=0).
     */
    public static function delete(array $post): array
    {
        $id = isset($post['id']) ? (int)$post['id'] : 0;
        if (!$id) return Util::error_missing_data_description('ID requerido.');

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $t    = $db->getTable('tbl_inversion_seguridad');
            $stmt = $pdo->prepare("UPDATE $t SET estado=0 WHERE id=?");
            $stmt->execute([$id]);

            $pivot = $db->getTable('tbl_inversion_municipios');
            $pdo->prepare("DELETE FROM $pivot WHERE inversion_id = ?")->execute([$id]);

            $db->closeConect();
            return ['output' => ['valid' => true, 'response' => ['id' => $id]]];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general($e->getMessage());
        }
    }

    // -------------------------------------------------------------------------

    /**
     * Mueve la imagen subida a uploads/inversiones/ y retorna el nombre guardado.
     * Retorna null si no se envió imagen.
     */
    /**
     * Retorna conteo y valor total agrupado por institución beneficiada.
     * Soporta filtro de fechas opcional via $params['fecha_desde'] y $params['fecha_hasta'].
     */
    public static function getByInstitucion(array $params = []): array
    {
        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $t           = $db->getTable('tbl_inversion_seguridad');
            $where       = [];
            $bindings    = [];
            $dateColumn  = 'fecha';

            if (!empty($params['fecha_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['fecha_desde'])) {
                $where[]                   = "DATE({$dateColumn}) >= :fecha_desde";
                $bindings[':fecha_desde']  = $params['fecha_desde'];
            }

            if (!empty($params['fecha_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['fecha_hasta'])) {
                $where[]                   = "DATE({$dateColumn}) <= :fecha_hasta";
                $bindings[':fecha_hasta']  = $params['fecha_hasta'];
            }

            $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $stmt = $pdo->prepare("
                SELECT
                    COALESCE(NULLIF(TRIM(institucion), ''), 'Sin institución') AS institucion,
                    COUNT(*)                  AS total_registros,
                    COALESCE(SUM(valor), 0)   AS total_valor
                FROM {$t}
                {$whereSql}
                GROUP BY COALESCE(NULLIF(TRIM(institucion), ''), 'Sin institución')
                ORDER BY total_registros DESC
            ");
            $stmt->execute($bindings);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid'    => true,
                    'response' => $rows,
                ],
            ];

        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general($e->getMessage());
        }
    }

    public static function getByProvincia(array $params = []): array
    {
        $db   = new DbConection();
        $pdo  = $db->openConect();
        $t    = $db->getTable('tbl_inversion_seguridad');

        $fecha_desde = $params['fecha_desde'] ?? '';
        $fecha_hasta = $params['fecha_hasta'] ?? '';

        $where = "WHERE i.estado = 1";
        $binds = [];

        if ($fecha_desde !== '' && $fecha_hasta !== '') {
            $where .= " AND DATE(i.fecha) BETWEEN :desde AND :hasta";
            $binds[':desde'] = $fecha_desde;
            $binds[':hasta'] = $fecha_hasta;
        }

        try {
            $pm = $db->getTable('tbl_inversion_municipios');
            $cu = $db->getTable('tbl_ciudades_accion_unificada');
            $q = "SELECT
                    cau.subregion AS provincia,
                    COUNT(DISTINCT i.id) AS total_registros,
                    COALESCE(SUM(i.valor), 0) AS total_valor
                FROM {$t} i
                INNER JOIN {$pm} pm ON pm.inversion_id = i.id
                INNER JOIN {$cu} cau ON pm.municipio_id = cau.codigo_muncipio
                {$where}
                GROUP BY cau.subregion
                ORDER BY total_valor DESC";

            $stmt = $pdo->prepare($q);
            $stmt->execute($binds);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => $rows,
                ],
            ];
        } catch (Exception $e) {
            $db->closeConect();
            return Util::error_general($e->getMessage());
        }
    }

    private static function subirImagen(array $file): ?string
    {
        if (empty($file['name'])) {
            return null;
        }

        $folder = __DIR__ . '/../uploads/inversiones/';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $ext    = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $nombre = 'inv_' . time() . '.' . $ext;

        move_uploaded_file($file['tmp_name'], $folder . $nombre);

        return $nombre;
    }
}
