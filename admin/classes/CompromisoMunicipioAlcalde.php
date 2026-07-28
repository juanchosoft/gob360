<?php
require_once 'DbConection.php';
require_once 'Util.php';
require_once 'SessionData.php';

/**
 * Clase para gestionar compromisos de municipios por Alcalde
 * Diferencia con Compromisos: usa vereda en lugar de provincia
 *
 * @author SPIDERSOFTWARE
 */
class CompromisoMunicipioAlcalde
{
    public function __construct() {}

    /**
     * Obtener todos los compromisos con filtros
     *
     * @param array $rqst Parámetros de la petición
     * @return array JSON con los resultados
     */
    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';
        $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : 0;
        $componente = isset($rqst['componente']) ? ($rqst['componente']) : '';
        $cumplimiento = isset($rqst['cumplimiento']) ? trim($rqst['cumplimiento']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    c.*,
                    m.municipio,
                    v.nombre_vereda AS vereda,
                    COALESCE(s.secretaria, sg.secretaria) AS secretaria
                FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                LEFT JOIN " . $db->getTable('tbl_secretarias_municipios') . " s ON c.tbl_secretarias_id = s.id
                LEFT JOIN " . $db->getTable('tbl_secretarias') . " sg ON c.tbl_secretarias_id = sg.id
                INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " m
                  ON CAST(c.tbl_municipio_id AS CHAR) = CAST(m.codigo_muncipio AS CHAR)
                LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id";

            $where = [];
            $params = [];

            if ($id > 0) {
                $where[] = "c.id = :id";
                $params[':id'] = $id;
            }

            if ($tbl_municipio_id != '') {
                $munName = '';
                try {
                    $stmtMun = $pdo->prepare("SELECT LOWER(TRIM(municipio)) FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE CAST(codigo_muncipio AS CHAR) = :c LIMIT 1");
                    $stmtMun->execute([':c' => (string)$tbl_municipio_id]);
                    $munName = (string)$stmtMun->fetchColumn();
                } catch (Exception $e) {}
                $where[] = "(CAST(c.tbl_municipio_id AS CHAR) = :municipio_id OR (LOWER(TRIM(c.tbl_municipio_id)) = :mun_nombre AND :mun_nombre != ''))";
                $params[':municipio_id'] = (string)$tbl_municipio_id;
                $params[':mun_nombre'] = $munName;
            }

            if ($tbl_secretarias_id > 0) {
                $where[] = "c.tbl_secretarias_id = :secretaria_id";
                $params[':secretaria_id'] = $tbl_secretarias_id;
            }

            if ($tbl_vereda_id > 0) {
                $where[] = "c.tbl_vereda_id = :vereda_id";
                $params[':vereda_id'] = $tbl_vereda_id;
            }

            if ($componente != '') {
                $where[] = "c.componente = :componente";
                $params[':componente'] = $componente;
            }

            if ($cumplimiento != '') {
                $where[] = "c.cumplimiento = :cumplimiento";
                $params[':cumplimiento'] = $cumplimiento;
            }

            // Agregar WHERE si hay filtros
            if (count($where) > 0) {
                $q .= " WHERE " . implode(" AND ", $where);
            }

            $q .= " ORDER BY c.date DESC";

            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = array('output' => array('valid' => true, 'response' => $result));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al obtener compromisos: ' . $e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtener compromisos en trámite
     */
    public static function getAllEnTramite($rqst)
    {
        $rqst['cumplimiento'] = 'En Trámite';
        return self::getAllByEstado($rqst);
    }

    /**
     * Obtener compromisos sin cumplir
     */
    public static function getAllSinCumplir($rqst)
    {
        $rqst['cumplimiento'] = 'Sin Cumplir';
        return self::getAllByEstado($rqst);
    }

    /**
     * Obtener compromisos cumplidos con paginación para DataTables
     */
    public static function getAllCumplidos($rqst)
    {
        // Parámetros de DataTables
        $draw = isset($rqst['draw']) ? intval($rqst['draw']) : 1;
        $start = isset($rqst['start']) ? intval($rqst['start']) : 0;
        $length = isset($rqst['length']) ? intval($rqst['length']) : 10;
        $search = isset($rqst['search']['value']) ? $rqst['search']['value'] : '';

        // Parámetros de ordenamiento
        $orderColumnIndex = isset($rqst['order'][0]['column']) ? intval($rqst['order'][0]['column']) : 0;
        $orderDir = isset($rqst['order'][0]['dir']) ? $rqst['order'][0]['dir'] : 'desc';

        // Columnas disponibles para ordenar
        $columns = ['c.id', 's.secretaria', 'c.compromisos', 'c.cumplimiento', 'm.municipio',
                    'v.nombre_vereda', 'c.componente', 'c.tipo_ejecucion', 'c.img', 'c.date'];
        $orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'c.date';

        // Filtros adicionales
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? $rqst['tbl_municipio_id'] : '';
        $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : 0;
        $componente = isset($rqst['componente']) ? $rqst['componente'] : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Construcción de WHERE
            $where = ["c.cumplimiento = 'Cumplido'"];
            $params = [];

            if ($tbl_municipio_id != '') {
                $munName = '';
                try {
                    $stmtMun = $pdo->prepare("SELECT LOWER(TRIM(municipio)) FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE CAST(codigo_muncipio AS CHAR) = :c LIMIT 1");
                    $stmtMun->execute([':c' => (string)$tbl_municipio_id]);
                    $munName = (string)$stmtMun->fetchColumn();
                } catch (Exception $e) {}
                $where[] = "(CAST(c.tbl_municipio_id AS CHAR) = :municipio_id OR (LOWER(TRIM(c.tbl_municipio_id)) = :mun_nombre AND :mun_nombre != ''))";
                $params[':municipio_id'] = (string)$tbl_municipio_id;
                $params[':mun_nombre'] = $munName;
            }

            if ($tbl_secretarias_id > 0) {
                $where[] = "c.tbl_secretarias_id = :secretaria_id";
                $params[':secretaria_id'] = $tbl_secretarias_id;
            }

            if ($tbl_vereda_id > 0) {
                $where[] = "c.tbl_vereda_id = :vereda_id";
                $params[':vereda_id'] = $tbl_vereda_id;
            }

            if ($componente != '') {
                $where[] = "c.componente = :componente";
                $params[':componente'] = $componente;
            }

            // Búsqueda global
            if ($search != '') {
                $where[] = "(c.compromisos LIKE :search OR c.compromiso_pactado LIKE :search OR
                            c.consecuencia LIKE :search OR c.respuesta LIKE :search OR
                            m.municipio LIKE :search OR v.nombre_vereda LIKE :search OR
                            COALESCE(s.secretaria, sg.secretaria) LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            $whereClause = implode(' AND ', $where);

            // Consulta para contar total de registros filtrados
            $sqlCount = "SELECT COUNT(*) as total
                        FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                        LEFT JOIN " . $db->getTable('tbl_secretarias_municipios') . " s ON c.tbl_secretarias_id = s.id
                        LEFT JOIN " . $db->getTable('tbl_secretarias') . " sg ON c.tbl_secretarias_id = sg.id
                        INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " m ON CAST(c.tbl_municipio_id AS CHAR) = CAST(m.codigo_muncipio AS CHAR)
                        LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                        WHERE $whereClause";

            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute($params);
            $totalFiltered = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

            // Consulta para contar total de registros sin filtros (solo cumplidos)
            $sqlTotal = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_compromisos_alcalde') . " WHERE cumplimiento = 'Cumplido'";
            $stmtTotal = $pdo->prepare($sqlTotal);
            $stmtTotal->execute();
            $totalRecords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

            // Consulta principal con paginación
            $sqlData = "SELECT
                        c.id,
                        c.compromisos,
                        c.compromiso_pactado,
                        c.consecuencia,
                        c.respuesta,
                        c.cumplimiento,
                        c.componente,
                        c.tipo_ejecucion,
                        c.img,
                        c.date,
                        m.municipio,
                        v.nombre_vereda AS vereda,
                        COALESCE(s.secretaria, sg.secretaria) AS secretaria,
                        c.tbl_municipio_id,
                        c.tbl_vereda_id,
                        c.tbl_secretarias_id
                    FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                    LEFT JOIN " . $db->getTable('tbl_secretarias_municipios') . " s ON c.tbl_secretarias_id = s.id
                    LEFT JOIN " . $db->getTable('tbl_secretarias') . " sg ON c.tbl_secretarias_id = sg.id
                    INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " m ON CAST(c.tbl_municipio_id AS CHAR) = CAST(m.codigo_muncipio AS CHAR)
                    LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                    WHERE $whereClause
                    ORDER BY $orderBy $orderDir
                    LIMIT :start, :length";

            $stmtData = $pdo->prepare($sqlData);
            foreach ($params as $key => $value) {
                $stmtData->bindValue($key, $value);
            }
            $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
            $stmtData->bindValue(':length', $length, PDO::PARAM_INT);
            $stmtData->execute();
            $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            // Formato de respuesta para DataTables
            $arrjson = [
                'draw' => $draw,
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data
            ];
        } catch (PDOException $e) {
            $arrjson = [
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error al obtener compromisos: ' . $e->getMessage()
            ];
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtener compromisos por estado
     */
    private static function getAllByEstado($rqst)
    {
        $cumplimiento = isset($rqst['cumplimiento']) ? $rqst['cumplimiento'] : '';
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    c.id,
                    c.compromisos,
                    c.compromiso_pactado,
                    c.consecuencia,
                    c.respuesta,
                    c.cumplimiento,
                    c.componente,
                    c.tipo_ejecucion,
                    c.img,
                    c.date,
                    m.municipio,
                    v.nombre_vereda AS vereda,
                    s.secretaria
                FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON c.tbl_secretarias_id = s.id
                INNER JOIN " . $db->getTable('tbl_ciudades') . " m ON c.tbl_municipio_id COLLATE utf8mb3_unicode_ci = m.codigo_muncipio
                LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                WHERE c.cumplimiento = :cumplimiento";

            $params = [':cumplimiento' => $cumplimiento];

            if ($tbl_municipio_id != '') {
                $where[] = "CAST(c.tbl_municipio_id AS CHAR) = :municipio_id";
                $params[':municipio_id'] = (string)$tbl_municipio_id;
            }

            $q .= " ORDER BY c.date DESC";

            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = array('output' => array('valid' => true, 'response' => $result));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error: ' . $e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Guardar o actualizar un compromiso
     */
    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $compromisos = isset($rqst['compromisos']) ? trim($rqst['compromisos']) : '';
        $compromiso_pactado = isset($rqst['compromiso_pactado']) ? trim($rqst['compromiso_pactado']) : '';
        $consecuencia = isset($rqst['consecuencia']) ? trim($rqst['consecuencia']) : '';
        $respuesta = isset($rqst['respuesta']) ? trim($rqst['respuesta']) : '';
        $cumplimiento = isset($rqst['cumplimiento']) ? $rqst['cumplimiento'] : 'En Trámite';
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? $rqst['tbl_municipio_id'] : '';
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : null;
        $componente = isset($rqst['componente']) ? trim($rqst['componente']) : '';
        $tipo_ejecucion = isset($rqst['tipo_ejecucion']) ? $rqst['tipo_ejecucion'] : 'GESTIÓN';
        $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
        $img = isset($_SESSION['file']['nombrearchivo']) ? $_SESSION['file']['nombrearchivo'] : '';

        // Validaciones
        if (empty($compromisos) || empty($tbl_municipio_id) || $tbl_secretarias_id == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            if ($id > 0) {
                // Actualizar
                $q = "UPDATE " . $db->getTable('tbl_compromisos_alcalde') . "
                      SET compromisos = :compromisos,
                          compromiso_pactado = :compromiso_pactado,
                          consecuencia = :consecuencia,
                          respuesta = :respuesta,
                          cumplimiento = :cumplimiento,
                          tbl_municipio_id = :tbl_municipio_id,
                          tbl_vereda_id = :tbl_vereda_id,
                          componente = :componente,
                          tipo_ejecucion = :tipo_ejecucion,
                          tbl_secretarias_id = :tbl_secretarias_id,
                          updated_at = CURRENT_TIMESTAMP";

                if ($img != '') {
                    $q .= ", img = :img";
                }

                $q .= " WHERE id = :id";

                $stmt = $pdo->prepare($q);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            } else {
                // Insertar
                $q = "INSERT INTO " . $db->getTable('tbl_compromisos_alcalde') . "
                      (compromisos, compromiso_pactado, consecuencia, respuesta, cumplimiento,
                       tbl_municipio_id, tbl_vereda_id, componente, tipo_ejecucion, tbl_secretarias_id, img, date)
                      VALUES (:compromisos, :compromiso_pactado, :consecuencia, :respuesta, :cumplimiento,
                              :tbl_municipio_id, :tbl_vereda_id, :componente, :tipo_ejecucion, :tbl_secretarias_id, :img, " . Util::date_now_server() . ")";

                $stmt = $pdo->prepare($q);
            }

            // Bind de parámetros
            $stmt->bindValue(':compromisos', $compromisos);
            $stmt->bindValue(':compromiso_pactado', $compromiso_pactado);
            $stmt->bindValue(':consecuencia', $consecuencia);
            $stmt->bindValue(':respuesta', $respuesta);
            $stmt->bindValue(':cumplimiento', $cumplimiento);
            $stmt->bindValue(':tbl_municipio_id', $tbl_municipio_id);
            $stmt->bindValue(':tbl_vereda_id', $tbl_vereda_id, PDO::PARAM_INT);
            $stmt->bindValue(':componente', $componente);
            $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion);
            $stmt->bindValue(':tbl_secretarias_id', $tbl_secretarias_id, PDO::PARAM_INT);

            if ($img != '') {
                $stmt->bindValue(':img', $img);
            }

            $stmt->execute();

            $arrjson = array('output' => array('valid' => true, 'response' => 'Compromiso guardado correctamente'));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al guardar: ' . $e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Eliminar un compromiso
     */
    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        if ($id == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "DELETE FROM " . $db->getTable('tbl_compromisos_alcalde') . " WHERE id = :id";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $arrjson = array('output' => array('valid' => true, 'response' => 'Compromiso eliminado'));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al eliminar: ' . $e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Actualizar un compromiso (con manejo de archivos)
     */
    public static function actualizarCompromiso($rqst, $files)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $compromisos = isset($rqst['compromisos']) ? trim($rqst['compromisos']) : '';
        $compromiso_pactado = isset($rqst['compromiso_pactado']) ? trim($rqst['compromiso_pactado']) : '';
        $consecuencia = isset($rqst['consecuencia']) ? trim($rqst['consecuencia']) : '';
        $respuesta = isset($rqst['respuesta']) ? trim($rqst['respuesta']) : '';
        $cumplimiento = isset($rqst['cumplimiento']) ? $rqst['cumplimiento'] : 'En Trámite';
        $estado = isset($rqst['estado']) ? trim($rqst['estado']) : '';
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? $rqst['tbl_municipio_id'] : '';
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : null;
        $componente = isset($rqst['componente']) ? trim($rqst['componente']) : '';
        $tipo_ejecucion = isset($rqst['tipo_ejecucion']) ? $rqst['tipo_ejecucion'] : 'GESTIÓN';
        $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;

        // Validaciones
        if ($id === 0) {
            return array('output' => array('valid' => false, 'response' => 'Debe seleccionar el compromiso a actualizar.'));
        }

        if (empty($compromisos) || empty($tbl_municipio_id) || $tbl_secretarias_id == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "UPDATE " . $db->getTable('tbl_compromisos_alcalde') . "
                  SET compromisos = :compromisos,
                      compromiso_pactado = :compromiso_pactado,
                      consecuencia = :consecuencia,
                      respuesta = :respuesta,
                      cumplimiento = :cumplimiento,";
            if (!empty($estado)) {
                $q .= " estado = :estado,";
            }
            $q .= "   tbl_municipio_id = :tbl_municipio_id,
                      tbl_vereda_id = :tbl_vereda_id,
                      componente = :componente,
                      tipo_ejecucion = :tipo_ejecucion,
                      tbl_secretarias_id = :tbl_secretarias_id,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':compromisos', $compromisos);
            $stmt->bindValue(':compromiso_pactado', $compromiso_pactado);
            $stmt->bindValue(':consecuencia', $consecuencia);
            $stmt->bindValue(':respuesta', $respuesta);
            $stmt->bindValue(':cumplimiento', $cumplimiento);
            if (!empty($estado)) {
                $stmt->bindValue(':estado', $estado);
            }
            $stmt->bindValue(':tbl_municipio_id', $tbl_municipio_id);
            $stmt->bindValue(':tbl_vereda_id', $tbl_vereda_id, PDO::PARAM_INT);
            $stmt->bindValue(':componente', $componente);
            $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion);
            $stmt->bindValue(':tbl_secretarias_id', $tbl_secretarias_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Manejar actualización de imagen si se envió
                if (isset($files['imagen']) && $files['imagen']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../../assets/img/admin/';
                    $imgName = uniqid() . '_' . basename($files['imagen']['name']);
                    $imgTarget = $uploadDir . $imgName;

                    if (move_uploaded_file($files['imagen']['tmp_name'], $imgTarget)) {
                        $qImg = "UPDATE " . $db->getTable('tbl_compromisos_alcalde') . " SET img = :img WHERE id = :id";
                        $stmtImg = $pdo->prepare($qImg);
                        $stmtImg->bindValue(':img', $imgName, PDO::PARAM_STR);
                        $stmtImg->bindValue(':id', $id, PDO::PARAM_INT);
                        $stmtImg->execute();
                    }
                }

                $arrjson = array('output' => array('valid' => true, 'response' => 'Compromiso actualizado correctamente'));
            } else {
                $arrjson = Util::error_general('Error al actualizar el compromiso.');
            }
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error al actualizar: ' . $e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtener indicadores por secretaría
     */
    public static function getIndicadoresPorSecretaria($rqst)
    {
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? $rqst['tbl_municipio_id'] : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT
                    COALESCE(s.secretaria, sg.secretaria) AS secretaria,
                    COUNT(*) as total,
                    SUM(CASE WHEN c.cumplimiento = 'Cumplido' THEN 1 ELSE 0 END) as cumplidos,
                    SUM(CASE WHEN c.cumplimiento = 'En Trámite' THEN 1 ELSE 0 END) as en_tramite,
                    SUM(CASE WHEN c.cumplimiento = 'Sin Cumplir' THEN 1 ELSE 0 END) as sin_cumplir
                FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                LEFT JOIN " . $db->getTable('tbl_secretarias_municipios') . " s ON c.tbl_secretarias_id = s.id
                LEFT JOIN " . $db->getTable('tbl_secretarias') . " sg ON c.tbl_secretarias_id = sg.id";

            $params = [];
            if ($tbl_municipio_id != '') {
                $munName = '';
                try {
                    $stmtMun = $pdo->prepare("SELECT LOWER(TRIM(municipio)) FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE CAST(codigo_muncipio AS CHAR) = :c LIMIT 1");
                    $stmtMun->execute([':c' => (string)$tbl_municipio_id]);
                    $munName = (string)$stmtMun->fetchColumn();
                } catch (Exception $e) {}
                $q .= " WHERE (CAST(c.tbl_municipio_id AS CHAR) = :municipio_id OR (LOWER(TRIM(c.tbl_municipio_id)) = :mun_nombre AND :mun_nombre != ''))";
                $params[':municipio_id'] = (string)$tbl_municipio_id;
                $params[':mun_nombre'] = $munName;
            }

            $q .= " GROUP BY COALESCE(s.id, sg.id), COALESCE(s.secretaria, sg.secretaria) ORDER BY total DESC";

            $stmt = $pdo->prepare($q);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = array('output' => array('valid' => true, 'response' => $result));
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error: ' . $e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtener todos los compromisos en estado "En Espera" para aprobación
     * Con paginación para DataTables
     */
    public static function getAllCompromiseEnEspera($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $userType = SessionData::getUserType();
        $municipioUsuario = SessionData::getCodigoMunicipio();
        $isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

        // Parámetros de DataTables
        $draw = isset($rqst['draw']) ? intval($rqst['draw']) : 1;
        $start = isset($rqst['start']) ? intval($rqst['start']) : 0;
        $length = isset($rqst['length']) ? intval($rqst['length']) : 10;
        $search = isset($rqst['search']['value']) ? $rqst['search']['value'] : '';

        $orderColumnIndex = isset($rqst['order'][0]['column']) ? intval($rqst['order'][0]['column']) : 0;
        $orderDir = isset($rqst['order'][0]['dir']) ? $rqst['order'][0]['dir'] : 'desc';

        $columns = ['c.id', 's.secretaria', 'c.compromisos', 'c.consecuencia', 'c.respuesta',
                    'c.estado_autorizar', 'm.municipio', 'v.nombre_vereda', 'c.componente',
                    'c.tipo_ejecucion', 'c.date'];
        $orderBy = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'c.date';

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $where = ["c.cumplimiento IN ('En Trámite', 'Cumplido', 'Sin Cumplir')"];
            $params = [];

            if ($isAlcaldeOAuxiliar) {
                $where[] = "c.tbl_municipio_id = :municipioId";
                $params[':municipioId'] = $municipioUsuario;
            }

            if ($search != '') {
                $where[] = "(c.compromisos LIKE :search OR c.compromiso_pactado LIKE :search OR
                            c.consecuencia LIKE :search OR c.respuesta LIKE :search OR
                            m.municipio LIKE :search OR v.nombre_vereda LIKE :search OR
                            s.secretaria LIKE :search OR c.componente LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            $whereBase = $where;
            if ($search != '') {
                $whereBase = array_filter($whereBase, function($cond) {
                    return strpos($cond, ':search') === false;
                });
            }
            $whereBaseClause = !empty($whereBase) ? implode(' AND ', $whereBase) : '1=1';

            $whereClause = !empty($where) ? implode(' AND ', $where) : '1=1';

            // Total de registros (sin filtro de búsqueda)
            $sqlCount = "SELECT COUNT(*) as total
                        FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                        INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON c.tbl_secretarias_id = s.id
                        INNER JOIN " . $db->getTable('tbl_ciudades') . " m ON c.tbl_municipio_id COLLATE utf8mb3_unicode_ci = m.codigo_muncipio
                        LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                        WHERE $whereBaseClause";
            $stmtCount = $pdo->prepare($sqlCount);
            $paramsCount = array_filter($params, function($key) {
                return strpos($key, ':search') === false;
            }, ARRAY_FILTER_USE_KEY);
            $stmtCount->execute($paramsCount);
            $totalRecords = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

            // Total filtrado (con todos los filtros)
            $sqlFiltered = "SELECT COUNT(*) as total
                           FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                           INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON c.tbl_secretarias_id = s.id
                           INNER JOIN " . $db->getTable('tbl_ciudades') . " m ON c.tbl_municipio_id COLLATE utf8mb3_unicode_ci = m.codigo_muncipio
                           LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                           WHERE $whereClause";
            $stmtFiltered = $pdo->prepare($sqlFiltered);
            $stmtFiltered->execute($params);
            $totalFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

            // Datos con paginación
            $sqlData = "SELECT
                        c.id,
                        c.compromisos,
                        c.compromiso_pactado,
                        c.consecuencia,
                        c.respuesta,
                        c.cumplimiento,
                        c.estado_autorizar,
                        c.componente,
                        c.tipo_ejecucion,
                        c.img,
                        c.date,
                        c.estado,
                        m.municipio,
                        v.nombre_vereda AS vereda,
                        s.secretaria,
                        c.tbl_municipio_id,
                        c.tbl_vereda_id,
                        c.tbl_secretarias_id,
                        CONCAT(IFNULL(u.nombre, ''), ' ', IFNULL(u.apellido, '')) AS aprobador_observacion
                    FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                    INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON c.tbl_secretarias_id = s.id
                    INNER JOIN " . $db->getTable('tbl_ciudades') . " m ON c.tbl_municipio_id COLLATE utf8mb3_unicode_ci = m.codigo_muncipio
                    LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                    LEFT JOIN " . $db->getTable('tbl_usuarios') . " u ON c.usuario_aprobador_id = u.id
                    WHERE $whereClause
                    ORDER BY $orderBy $orderDir
                    LIMIT :start, :length";

            $stmtData = $pdo->prepare($sqlData);
            foreach ($params as $key => $value) {
                $stmtData->bindValue($key, $value);
            }
            $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
            $stmtData->bindValue(':length', $length, PDO::PARAM_INT);
            $stmtData->execute();
            $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = [
                'draw' => $draw,
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data
            ];
        } catch (PDOException $e) {
            $arrjson = [
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error: ' . $e->getMessage()
            ];
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtener compromisos en estado "En Espera" con filtros
     */
    public static function getAllCompromiseFiltrosSelectEnEstadoEspera($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $userType = SessionData::getUserType();
        $municipioUsuario = SessionData::getCodigoMunicipio();
        $isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

        $draw = isset($rqst['draw']) ? intval($rqst['draw']) : 1;
        $start = isset($rqst['start']) ? intval($rqst['start']) : 0;
        $length = isset($rqst['length']) ? intval($rqst['length']) : 10;
        $secretaria = isset($rqst['secretaria']) ? $rqst['secretaria'] : '';
        $componente = isset($rqst['componente']) ? $rqst['componente'] : '';
        $municipio = isset($rqst['municipio']) ? $rqst['municipio'] : '';
        $vereda = isset($rqst['vereda']) ? intval($rqst['vereda']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $where = ["c.cumplimiento IN ('En Trámite', 'Cumplido', 'Sin Cumplir')"];
            $params = [];

            if ($isAlcaldeOAuxiliar) {
                $where[] = "c.tbl_municipio_id = :municipioId";
                $params[':municipioId'] = $municipioUsuario;
            }

            if ($secretaria != '') {
                $where[] = "c.tbl_secretarias_id = :secretaria";
                $params[':secretaria'] = $secretaria;
            }

            if ($componente != '') {
                $where[] = "c.componente = :componente";
                $params[':componente'] = $componente;
            }

            if ($municipio != '') {
                $where[] = "c.tbl_municipio_id = :municipio";
                $params[':municipio'] = $municipio;
            }

            if ($vereda > 0) {
                $where[] = "c.tbl_vereda_id = :vereda";
                $params[':vereda'] = $vereda;
            }

            $whereClause = !empty($where) ? implode(' AND ', $where) : '1=1';

            // Total sin filtros de interfaz (pero con filtro de municipio si aplica)
            $baseCountWhere = ["c.cumplimiento IN ('En Trámite', 'Cumplido', 'Sin Cumplir')"];
            $baseCountParams = [];
            if ($isAlcaldeOAuxiliar) {
                $baseCountWhere[] = "c.tbl_municipio_id = :municipioIdCount";
                $baseCountParams[':municipioIdCount'] = $municipioUsuario;
            }
            $joinClause = "INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON c.tbl_secretarias_id = s.id
                          INNER JOIN " . $db->getTable('tbl_ciudades') . " m ON c.tbl_municipio_id COLLATE utf8mb3_unicode_ci = m.codigo_muncipio
                          LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id";

            $baseCountClause = !empty($baseCountWhere) ? implode(' AND ', $baseCountWhere) : '1=1';
            $sqlCount = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_compromisos_alcalde') . " c $joinClause WHERE $baseCountClause";
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute($baseCountParams);
            $totalRecords = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

            // Total con filtros
            $sqlFiltered = "SELECT COUNT(*) as total
                           FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                           $joinClause
                           WHERE $whereClause";
            $stmtFiltered = $pdo->prepare($sqlFiltered);
            $stmtFiltered->execute($params);
            $totalFiltered = $stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

            // Datos
            $sqlData = "SELECT
                        c.id,
                        c.compromisos,
                        c.compromiso_pactado,
                        c.consecuencia,
                        c.respuesta,
                        c.cumplimiento,
                        c.estado_autorizar,
                        c.componente,
                        c.tipo_ejecucion,
                        c.img,
                        c.date,
                        c.estado,
                        m.municipio,
                        v.nombre_vereda AS vereda,
                        s.secretaria,
                        c.tbl_municipio_id,
                        c.tbl_vereda_id,
                        c.tbl_secretarias_id,
                        CONCAT(IFNULL(u.nombre, ''), ' ', IFNULL(u.apellido, '')) AS aprobador_observacion
                    FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                    INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON c.tbl_secretarias_id = s.id
                    INNER JOIN " . $db->getTable('tbl_ciudades') . " m ON c.tbl_municipio_id COLLATE utf8mb3_unicode_ci = m.codigo_muncipio
                    LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                    LEFT JOIN " . $db->getTable('tbl_usuarios') . " u ON c.usuario_aprobador_id = u.id
                    WHERE $whereClause
                    ORDER BY c.date DESC
                    LIMIT :start, :length";

            $stmtData = $pdo->prepare($sqlData);
            foreach ($params as $key => $value) {
                $stmtData->bindValue($key, $value);
            }
            $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
            $stmtData->bindValue(':length', $length, PDO::PARAM_INT);
            $stmtData->execute();
            $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = [
                'draw' => $draw,
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data
            ];
        } catch (PDOException $e) {
            $arrjson = [
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error: ' . $e->getMessage()
            ];
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtener un compromiso por ID
     */
    public static function getCompromisoId($data)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        // Obtener ID desde el array o directamente si es entero
        $id = is_array($data) ? (isset($data['id']) ? intval($data['id']) : 0) : intval($data);

        try {
            $q = "SELECT c.*,
                         m.municipio,
                         v.nombre_vereda AS vereda,
                         s.secretaria
                  FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                  LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " m ON c.tbl_municipio_id COLLATE utf8mb3_spanish_ci = m.codigo_muncipio COLLATE utf8mb3_spanish_ci
                  LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                  INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON c.tbl_secretarias_id = s.id
                  WHERE c.id = :id";

            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Obtener observaciones si existen
            $observaciones = [];
            if ($result) {
                $qObs = "SELECT * FROM " . $db->getTable('tbl_compromisos_alcalde_observaciones') . "
                         WHERE tbl_compromiso_id = :id
                         ORDER BY fecha DESC";
                $stmtObs = $pdo->prepare($qObs);
                $stmtObs->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtObs->execute();
                $observaciones = $stmtObs->fetchAll(PDO::FETCH_ASSOC);
            }

            if ($result) {
                $arrjson = [
                    'state' => true,
                    'data' => [$result],
                    'observaciones' => $observaciones
                ];
            } else {
                $arrjson = ['state' => false, 'message' => 'Compromiso no encontrado'];
            }
        } catch (PDOException $e) {
            $arrjson = ['state' => false, 'message' => 'Error: ' . $e->getMessage()];
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Aprobar un compromiso
     */
    public static function aprobarCompromiso($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $aprobacion = isset($rqst['aprobacion']) ? $rqst['aprobacion'] : 'no';
        $observacion = isset($rqst['observacion']) ? trim($rqst['observacion']) : '';
        $estadoParaAprobar = isset($rqst['estadoParaAprobar']) ? $rqst['estadoParaAprobar'] : '';
        $usuarioId = isset($_SESSION['session_user']['id']) ? $_SESSION['session_user']['id'] : 0;

        if ($id === 0) {
            return ['output' => ['valid' => false, 'response' => 'ID de compromiso inválido']];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            if ($aprobacion === 'si') {
                // Aprobar: cambiar estado a aprobado y actualizar estado_autorizar
                $q = "UPDATE " . $db->getTable('tbl_compromisos_alcalde') . "
                      SET estado = 'Aprobado',
                          estado_autorizar = :estado_autorizar,
                          usuario_aprobador_id = :usuario_id,
                          fecha_aprobacion = NOW()
                      WHERE id = :id";

                $stmt = $pdo->prepare($q);
                $stmt->bindValue(':estado_autorizar', $estadoParaAprobar);
                $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                $message = 'Compromiso aprobado correctamente';
            } else {
                // No aprobar: mantener en espera o rechazar según lógica de negocio
                $q = "UPDATE " . $db->getTable('tbl_compromisos_alcalde') . "
                      SET estado = 'Rechazado',
                          usuario_aprobador_id = :usuario_id,
                          fecha_aprobacion = NOW()
                      WHERE id = :id";

                $stmt = $pdo->prepare($q);
                $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                $message = 'Compromiso rechazado';
            }

            // Guardar observación si existe
            if (!empty($observacion)) {
                $qObs = "INSERT INTO " . $db->getTable('tbl_compromisos_alcalde_observaciones') . "
                         (tbl_compromiso_id, tbl_usuario_id, observacion, fecha)
                         VALUES (:compromiso_id, :usuario_id, :observacion, NOW())";

                $stmtObs = $pdo->prepare($qObs);
                $stmtObs->bindValue(':compromiso_id', $id, PDO::PARAM_INT);
                $stmtObs->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
                $stmtObs->bindValue(':observacion', $observacion);
                $stmtObs->execute();
            }

            $arrjson = ['output' => ['valid' => true, 'response' => $message]];
        } catch (PDOException $e) {
            $arrjson = Util::error_general('Error: ' . $e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Ejecutar traslado por competencia
     */
    public static function ejecutarTrasladoPorCompetencia($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $compromisoId = isset($rqst['compromiso_original_id']) ? intval($rqst['compromiso_original_id']) : 0;
        $secretariasDestino = isset($rqst['secretarias_destino']) ? $rqst['secretarias_destino'] : [];
        $usuarioId = isset($_SESSION['session_user']['id']) ? $_SESSION['session_user']['id'] : 0;

        if ($compromisoId === 0 || empty($secretariasDestino)) {
            return ['output' => ['valid' => false, 'response' => 'Datos inválidos para traslado']];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener datos del compromiso original
            $q = "SELECT * FROM " . $db->getTable('tbl_compromisos_alcalde') . " WHERE id = :id";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $compromisoId, PDO::PARAM_INT);
            $stmt->execute();
            $original = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$original) {
                return ['output' => ['valid' => false, 'response' => 'Compromiso no encontrado']];
            }

            // Iniciar transacción
            $pdo->beginTransaction();

            // Duplicar el compromiso para cada secretaría destino
            foreach ($secretariasDestino as $secretariaId) {
                $qInsert = "INSERT INTO " . $db->getTable('tbl_compromisos_alcalde') . "
                           (compromisos, compromiso_pactado, consecuencia, respuesta, cumplimiento,
                            tbl_municipio_id, tbl_vereda_id, componente, tipo_ejecucion, tbl_secretarias_id,
                            img, date, estado, usuario_traslado_id, compromiso_original_id)
                           VALUES (:compromisos, :compromiso_pactado, :consecuencia, :respuesta, :cumplimiento,
                                   :municipio_id, :vereda_id, :componente, :tipo_ejecucion, :secretaria_id,
                                   :img, :date, 'En Espera', :usuario_id, :original_id)";

                $stmtInsert = $pdo->prepare($qInsert);
                $stmtInsert->bindValue(':compromisos', $original['compromisos']);
                $stmtInsert->bindValue(':compromiso_pactado', $original['compromiso_pactado']);
                $stmtInsert->bindValue(':consecuencia', $original['consecuencia']);
                $stmtInsert->bindValue(':respuesta', $original['respuesta']);
                $stmtInsert->bindValue(':cumplimiento', $original['cumplimiento']);
                $stmtInsert->bindValue(':municipio_id', $original['tbl_municipio_id']);
                $stmtInsert->bindValue(':vereda_id', $original['tbl_vereda_id'], PDO::PARAM_INT);
                $stmtInsert->bindValue(':componente', $original['componente']);
                $stmtInsert->bindValue(':tipo_ejecucion', $original['tipo_ejecucion']);
                $stmtInsert->bindValue(':secretaria_id', $secretariaId, PDO::PARAM_INT);
                $stmtInsert->bindValue(':img', $original['img']);
                $stmtInsert->bindValue(':date', $original['date']);
                $stmtInsert->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
                $stmtInsert->bindValue(':original_id', $compromisoId, PDO::PARAM_INT);
                $stmtInsert->execute();
            }

            $pdo->commit();
            $arrjson = ['output' => ['valid' => true, 'response' => 'Traslado ejecutado correctamente']];
        } catch (PDOException $e) {
            $pdo->rollBack();
            $arrjson = Util::error_general('Error: ' . $e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene datos para el gráfico de seguimiento por vereda (adaptado para Alcalde)
     */
    public static function graficoSeguimiento($idSecretaria = null, $componente = null, $tipo_ejecucion = null, $codigo_municipio = null)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $where = "1=1";

            if ($idSecretaria !== "" && $idSecretaria !== null) {
                $where .= " AND c.tbl_secretarias_id = :idSecretaria";
            }
            if ($componente !== "" && $componente !== null) {
                $where .= " AND c.componente = :componente";
            }
            if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
                $where .= " AND c.tipo_ejecucion = :tipo_ejecucion";
            }
            if ($codigo_municipio !== "" && $codigo_municipio !== null) {
                $where .= " AND c.tbl_municipio_id = :codigo_municipio";
            }

            // Consulta adaptada para veredas en lugar de provincias
            $q = "SELECT
                    COALESCE(v.nombre_vereda, 'Sin vereda') AS vereda,
                    COUNT(*) AS total
                FROM
                    " . $db->getTable('tbl_compromisos_alcalde') . " c
                LEFT JOIN " . $db->getTable('tbl_vereda') . " v ON c.tbl_vereda_id = v.id
                WHERE $where
                GROUP BY
                    v.nombre_vereda
                ORDER BY
                    total DESC
                LIMIT 10";

            $stmt = $pdo->prepare($q);

            if ($idSecretaria !== "" && $idSecretaria !== null) {
                $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
            }
            if ($componente !== "" && $componente !== null) {
                $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
            }
            if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
                $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
            }
            if ($codigo_municipio !== "" && $codigo_municipio !== null) {
                $stmt->bindValue(':codigo_municipio', $codigo_municipio, PDO::PARAM_STR);
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'state' => true,
                'data' => $result
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    /**
     * Obtiene porcentajes y estadísticas de compromisos (adaptado para Alcalde)
     */
    public static function porcentaje($idSecretaria = null, $componente = null, $tipo_ejecucion = null, $codigo_municipio = null)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $filtros = "";
            if (!empty($idSecretaria)) {
                $filtros .= " AND c.tbl_secretarias_id = :idSecretaria";
            }
            if (!empty($componente)) {
                $filtros .= " AND c.componente = :componente";
            }
            if (!empty($tipo_ejecucion)) {
                $filtros .= " AND c.tipo_ejecucion = :tipo_ejecucion";
            }
            if (!empty($codigo_municipio)) {
                $filtros .= " AND c.tbl_municipio_id = :codigo_municipio";
            }

            // Consulta consolidada para todos los conteos
            $qConsolidado = "
                SELECT
                    COUNT(c.id) AS total_compromisos,
                    SUM(CASE WHEN TRIM(UPPER(c.cumplimiento)) = 'CUMPLIDO' THEN 1 ELSE 0 END) AS cumplidos,
                    SUM(CASE WHEN TRIM(UPPER(c.cumplimiento)) IN ('EN TRÁMITE', 'EN TRAMITE') THEN 1 ELSE 0 END) AS en_tramite,
                    SUM(CASE WHEN TRIM(UPPER(c.estado)) = 'EN ESPERA' THEN 1 ELSE 0 END) AS en_espera,
                    SUM(CASE
                        WHEN TRIM(UPPER(c.cumplimiento)) IN ('SIN CUMPLIR', 'POR CUMPLIR')
                        OR c.cumplimiento IS NULL
                        OR TRIM(c.cumplimiento) = ''
                        THEN 1
                        ELSE 0
                    END) AS sin_cumplir
                FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                WHERE 1=1
                $filtros
            ";

            $stmt = $pdo->prepare($qConsolidado);
            if (!empty($idSecretaria)) $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
            if (!empty($componente)) $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
            if (!empty($tipo_ejecucion)) $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
            if (!empty($codigo_municipio)) $stmt->bindValue(':codigo_municipio', $codigo_municipio, PDO::PARAM_STR);
            $stmt->execute();
            $conteo = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalCompromisos = (int)$conteo['total_compromisos'];
            $cumplidos = (int)$conteo['cumplidos'];
            $enTramite = (int)$conteo['en_tramite'];
            $enEspera = (int)$conteo['en_espera'];
            $sinCumplir = (int)$conteo['sin_cumplir'];

            // Meta global
            $qMetaGlobal = "
                SELECT
                    COUNT(c.id) AS meta_global
                FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                WHERE 1=1
            ";

            $stmtMeta = $pdo->prepare($qMetaGlobal);
            $stmtMeta->execute();
            $meta = $stmtMeta->fetch(PDO::FETCH_ASSOC);

            $metaOficial = (int)$meta['meta_global'];
            if ($metaOficial < 1) $metaOficial = 1;

            // Veredas y municipios (adaptado para usar tbl_vereda)
            $qUbicaciones = "
                SELECT
                    COUNT(DISTINCT c.tbl_vereda_id) AS total_veredas,
                    COUNT(DISTINCT c.tbl_municipio_id) AS total_municipios
                FROM " . $db->getTable('tbl_compromisos_alcalde') . " c
                WHERE 1=1
                $filtros
            ";
            $stmt = $pdo->prepare($qUbicaciones);
            if (!empty($idSecretaria)) $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
            if (!empty($componente)) $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
            if (!empty($tipo_ejecucion)) $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
            if (!empty($codigo_municipio)) $stmt->bindValue(':codigo_municipio', $codigo_municipio, PDO::PARAM_STR);
            $stmt->execute();
            $ubicaciones = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalVeredas = (int)$ubicaciones['total_veredas'];
            $totalMunicipios = (int)$ubicaciones['total_municipios'];

            $db->closeConect();

            return [
                'state' => true,
                'data' => [
                    'total_compromisos' => $totalCompromisos,
                    'cumplidos'         => $cumplidos,
                    'en_tramite'        => $enTramite,
                    'en_espera'         => $enEspera,
                    'sin_cumplir'       => $sinCumplir,
                    'total_veredas'     => $totalVeredas,
                    'total_municipios'  => $totalMunicipios,
                    'meta_oficial'      => $metaOficial
                ]
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    /**
     * Obtiene datos para pintar el mapa (adaptado para Alcalde)
     */
    public static function dataMapa($idSecretaria = null, $componente = null, $tipo_ejecucion = null, $codigo_municipio = null)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $where = "1=1";
            if ($idSecretaria !== "" && $idSecretaria !== null) {
                $where .= " AND c.tbl_secretarias_id = :idSecretaria";
            }
            if ($componente !== "" && $componente !== null) {
                $where .= " AND c.componente = :componente";
            }
            if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
                $where .= " AND c.tipo_ejecucion = :tipo_ejecucion";
            }
            if ($codigo_municipio !== "" && $codigo_municipio !== null) {
                $where .= " AND c.tbl_municipio_id = :codigo_municipio";
            }

            $q = "SELECT DISTINCT
                    c.tbl_municipio_id,
                    m.municipio
                FROM
                    " . $db->getTable('tbl_compromisos_alcalde') . " c
                    INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " m
                        ON c.tbl_municipio_id COLLATE utf8mb3_unicode_ci = m.codigo_muncipio COLLATE utf8mb3_unicode_ci
                WHERE
                    $where";
            $stmt = $pdo->prepare($q);
            if ($idSecretaria !== "" && $idSecretaria !== null) {
                $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
            }
            if ($componente !== "" && $componente !== null) {
                $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
            }
            if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
                $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
            }
            if ($codigo_municipio !== "" && $codigo_municipio !== null) {
                $stmt->bindValue(':codigo_municipio', $codigo_municipio, PDO::PARAM_STR);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'state' => true,
                'data' => $result
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    /**
     * Obtiene datos paginados por municipio y secretaría para DataTables (adaptado para Alcalde)
     */
    public static function dataPorMunicipioSecretaria($data)
    {
        $draw = intval($data['draw'] ?? 1);
        $start = intval($data['start'] ?? 0);
        $length = intval($data['length'] ?? 10);
        $searchValue = $data['search']['value'] ?? '';
        $orderDir = 'desc';

        $municipioId = trim($data['data']['municipio'] ?? '');
        $secretariaId = trim($data['data']['secretaria'] ?? '');
        $componente = trim($data['data']['componente'] ?? '');
        $tipo_ejecucion = trim($data['data']['tipo_ejecucion'] ?? '');
        $estado = trim($data['data']['estado'] ?? '');
        $vereda = trim($data['data']['vereda'] ?? '');

        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $tableCompromisos = $db->getTable('tbl_compromisos_alcalde') . " AS c";
            $tableSecretarias = $db->getTable('tbl_secretarias') . " AS s";
            $tableCiudades = $db->getTable('tbl_ciudades_accion_unificada') . " AS m";
            $tableVeredas = $db->getTable('tbl_vereda') . " AS v";

            $where = "1=1";
            $params = [];

            if ($municipioId !== '') {
                $where .= " AND c.tbl_municipio_id = :municipio";
                $params[':municipio'] = $municipioId;
            }

            if ($secretariaId !== '') {
                $where .= " AND c.tbl_secretarias_id = :secretaria";
                $params[':secretaria'] = $secretariaId;
            }

            if ($componente !== '') {
                $where .= " AND c.componente = :componente";
                $params[':componente'] = $componente;
            }

            if ($tipo_ejecucion !== '') {
                $where .= " AND c.tipo_ejecucion = :tipo_ejecucion";
                $params[':tipo_ejecucion'] = $tipo_ejecucion;
            }

            if (!empty($vereda)) {
                $where .= " AND c.tbl_vereda_id = :vereda";
                $params[':vereda'] = $vereda;
            }

            if (!empty($estado)) {
                if (strtoupper($estado) === 'SIN CUMPLIR') {
                    $where .= " AND (
                        UPPER(TRIM(c.cumplimiento)) LIKE 'SIN CUMPLIR%'
                        OR UPPER(TRIM(c.cumplimiento)) LIKE 'POR CUMPLIR%'
                    )";
                } else {
                    $where .= " AND UPPER(TRIM(c.cumplimiento)) LIKE CONCAT(UPPER(TRIM(:estado)), '%')";
                    $params[':estado'] = $estado;
                }
            }

            if (!empty($searchValue)) {
                $where .= " AND (
                    c.date LIKE :search OR
                    m.municipio LIKE :search OR
                    v.nombre_vereda LIKE :search OR
                    s.secretaria LIKE :search OR
                    c.componente LIKE :search OR
                    c.updated_at LIKE :search
                )";
                $params[':search'] = "%$searchValue%";
            }

            $sqlFiltered = "SELECT COUNT(*) FROM $tableCompromisos
                INNER JOIN $tableSecretarias ON c.tbl_secretarias_id = s.id
                LEFT JOIN $tableCiudades ON c.tbl_municipio_id COLLATE utf8mb3_spanish_ci = m.codigo_muncipio COLLATE utf8mb3_spanish_ci
                LEFT JOIN $tableVeredas ON c.tbl_vereda_id = v.id
                WHERE $where";

            $stmtFiltered = $pdo->prepare($sqlFiltered);
            foreach ($params as $key => $val) {
                $stmtFiltered->bindValue($key, $val);
            }
            $stmtFiltered->execute();
            $recordsFiltered = $stmtFiltered->fetchColumn();

            $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
                INNER JOIN $tableSecretarias ON c.tbl_secretarias_id = s.id
                LEFT JOIN $tableCiudades ON c.tbl_municipio_id COLLATE utf8mb3_spanish_ci = m.codigo_muncipio COLLATE utf8mb3_spanish_ci
                LEFT JOIN $tableVeredas ON c.tbl_vereda_id = v.id";
            $recordsTotal = $pdo->query($sqlTotal)->fetchColumn();

            $sqlData = "SELECT
                c.date,
                c.updated_at,
                m.municipio,
                c.compromisos,
                COALESCE(v.nombre_vereda, 'Sin vereda') AS vereda,
                c.respuesta,
                s.secretaria,
                c.img,
                c.id,
                c.cumplimiento AS estado,
                c.consecuencia,
                c.compromiso_pactado,
                c.componente
            FROM $tableCompromisos
                INNER JOIN $tableSecretarias ON c.tbl_secretarias_id = s.id
                LEFT JOIN $tableCiudades ON c.tbl_municipio_id COLLATE utf8mb3_spanish_ci = m.codigo_muncipio COLLATE utf8mb3_spanish_ci
                LEFT JOIN $tableVeredas ON c.tbl_vereda_id = v.id
            WHERE $where
            ORDER BY c.id $orderDir
            LIMIT :start, :length";

            $stmtData = $pdo->prepare($sqlData);
            foreach ($params as $key => $val) {
                $stmtData->bindValue($key, $val);
            }
            $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
            $stmtData->bindValue(':length', $length, PDO::PARAM_INT);
            $stmtData->execute();

            $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($result as $row) {
                if (!empty($row['img'])) {
                    $imgSrc = 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
                    $foto = '<img src="' . $imgSrc . '" alt="Imagen" style="max-width:100px; height:auto;" onclick="mostrarArchivoModal(\'' . $imgSrc . '\')">';
                } else {
                    $foto = null;
                }

                $data[] = [
                    'id_compromiso' => $row['id'],
                    'secretaria' => $row['secretaria'],
                    'compromisos' => $row['compromisos'],
                    'consecuencia' => $row['consecuencia'],
                    'respuesta' => $row['respuesta'],
                    'estado' => $row['estado'],
                    'municipio' => $row['municipio'],
                    'vereda' => $row['vereda'],
                    'componente' => $row['componente'],
                    'foto' => $foto,
                    'date' => $row['date'],
                    'created_at' => $row['updated_at'] ?? '-',
                ];
            }

            $db->closeConect();

            return [
                'draw' => $draw,
                'recordsTotal' => (int)$recordsTotal,
                'recordsFiltered' => (int)$recordsFiltered,
                'data' => $data
            ];
        } catch (PDOException $e) {
            return [
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene datos paginados con todos los estados para DataTables (adaptado para Alcalde)
     */
    public static function dataPorMunicipioSecretariaTodosLosEstados($data)
    {
        // Reutilizar la misma función pero sin filtrar por estado
        $data['data']['estado'] = '';
        return self::dataPorMunicipioSecretaria($data);
    }

    /**
     * Obtiene el visor de gestión de compromisos del Alcalde agrupados por secretaría
     * @param int|null $secretariaIdToFilter - ID de secretaría para filtrar (null = todas)
     * @return array - Array con estadísticas de cumplimiento por secretaría
     */
    public static function getVisorGestionDeCompromiso($secretariaIdToFilter = null)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Preparar la variable de filtro
            $secretariaIdFilter = intval($secretariaIdToFilter);

            $tableCompromisos = $db->getTable('tbl_compromisos_alcalde') . " AS c";
            $tableSecretarias = $db->getTable('tbl_secretarias') . " AS s";

            // Condición WHERE base - mostrar compromisos que ya están en gestión
            // Incluir: estado = 'Aprobado' O estado IS NULL (compromisos antiguos que ya están siendo gestionados)
            // Excluir: estado = 'En Espera' (aún no aprobados) y estado = 'Rechazado'
            $where = "(c.estado = 'Aprobado' OR c.estado IS NULL)";

            // Añadir la condición WHERE si hay un filtro (Solo para Secretarios)
            $bindParams = [];
            if ($secretariaIdFilter > 0) {
                // Si recibimos un ID válido, filtramos por la secretaría responsable del compromiso
                $where .= " AND c.tbl_secretarias_id = :secretariaIdFilter";
                $bindParams[':secretariaIdFilter'] = $secretariaIdFilter;
            }

            // Query que agrupa por secretaría y cuenta estados de cumplimiento
            $q = "SELECT
                      s.secretaria AS entidad,
                      COUNT(c.id) AS total_compromisos,
                      SUM(CASE WHEN c.cumplimiento = 'En Trámite' THEN 1 ELSE 0 END) AS en_tramite,
                      SUM(CASE WHEN c.cumplimiento = 'Cumplido' THEN 1 ELSE 0 END) AS cumplido,
                      SUM(CASE WHEN c.cumplimiento = 'Sin Cumplir' THEN 1 ELSE 0 END) AS sin_cumplir,
                      -- Cálculo de calificación (porcentaje de cumplimiento)
                      (CASE
                          WHEN COUNT(c.id) > 0 THEN (SUM(CASE WHEN c.cumplimiento = 'Cumplido' THEN 1 ELSE 0 END) * 100.0 / COUNT(c.id))
                          ELSE 0.0
                      END) AS calificacion_porcentaje
                  FROM
                      {$tableCompromisos}
                  INNER JOIN
                      {$tableSecretarias} ON c.tbl_secretarias_id = s.id
                  WHERE
                      {$where}
                  GROUP BY
                      s.secretaria, s.id
                  ORDER BY
                      total_compromisos DESC;";

            $stmt = $pdo->prepare($q);

            // Bindear los parámetros si existen
            if (!empty($bindParams)) {
                foreach ($bindParams as $key => $value) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar formato de porcentaje y color de calificación
            foreach ($results as &$row) {
                $calificacionNumerica = round((float)$row['calificacion_porcentaje'], 2);
                $row['calificacion_porcentaje'] = $calificacionNumerica . '%';

                // Lógica de colores según el porcentaje de cumplimiento
                if ($calificacionNumerica < 50) {
                    $row['color_calificacion']  = '#DC143C'; // Rojo - Bajo cumplimiento
                } else if ($calificacionNumerica >= 50 && $calificacionNumerica < 100) {
                    $row['color_calificacion']  = '#f1c40f'; // Amarillo - Cumplimiento medio
                } else {
                    $row['color_calificacion']  = '#2ecc71'; // Verde - Cumplimiento total
                }
            }

            $db->closeConect();

            return array('output' => array('valid' => true, 'response' => $results));
        } catch (PDOException $th) {
            return Util::error_no_result();
        }
    }

    /**
     * Obtiene el visor de gestión de compromisos del Alcalde agrupados por secretaría
     * Filtrado por código de municipio
     * @param string|null $codigo_municipio - Código del municipio para filtrar (null = todos)
     * @return array - Array con estadísticas de cumplimiento por secretaría
     */
    public static function getVisorGestionDeCompromisoPorAlcaldia($codigo_municipio = null)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $tableCompromisos = $db->getTable('tbl_compromisos_alcalde') . " AS c";
            $tableSecMun = $db->getTable('tbl_secretarias_municipios') . " AS s";
            $tableSecGen = $db->getTable('tbl_secretarias') . " AS sg";

            $where = "1=1";
            $bindParams = [];

            if (!empty($codigo_municipio)) {
                $munName = '';
                try {
                    $stmtMun = $pdo->prepare("SELECT LOWER(TRIM(municipio)) FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE CAST(codigo_muncipio AS CHAR) = :c LIMIT 1");
                    $stmtMun->execute([':c' => (string)$codigo_municipio]);
                    $munName = (string)$stmtMun->fetchColumn();
                } catch (Exception $e) {}
                $where .= " AND (CAST(c.tbl_municipio_id AS CHAR) = :codigoMunicipio" . ($munName ? " OR LOWER(TRIM(c.tbl_municipio_id)) = :munNombre" : "") . ")";
                $bindParams[':codigoMunicipio'] = (string)$codigo_municipio;
                if ($munName) $bindParams[':munNombre'] = $munName;
            }

            $q = "SELECT
                    COALESCE(s.secretaria, sg.secretaria) AS entidad,
                    COUNT(c.id) AS total_compromisos,
                    SUM(CASE WHEN c.cumplimiento = 'En Trámite' THEN 1 ELSE 0 END) AS en_tramite,
                    SUM(CASE WHEN c.cumplimiento = 'Cumplido' THEN 1 ELSE 0 END) AS cumplido,
                    SUM(CASE WHEN c.cumplimiento = 'Sin Cumplir' THEN 1 ELSE 0 END) AS sin_cumplir,
                    (CASE
                        WHEN COUNT(c.id) > 0 THEN (SUM(CASE WHEN c.cumplimiento = 'Cumplido' THEN 1 ELSE 0 END) * 100.0 / COUNT(c.id))
                        ELSE 0.0
                    END) AS calificacion_porcentaje
                FROM
                    {$tableCompromisos}
                LEFT JOIN
                    {$tableSecMun} ON c.tbl_secretarias_id = s.id
                LEFT JOIN
                    {$tableSecGen} ON c.tbl_secretarias_id = sg.id
                WHERE
                    {$where}
                GROUP BY
                    COALESCE(s.id, sg.id), COALESCE(s.secretaria, sg.secretaria)
                ORDER BY
                    total_compromisos DESC;";

            $stmt = $pdo->prepare($q);

            if (!empty($bindParams)) {
                foreach ($bindParams as $key => $value) {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                $calificacionNumerica = round((float)$row['calificacion_porcentaje'], 2);
                $row['calificacion_porcentaje'] = $calificacionNumerica . '%';

                if ($calificacionNumerica < 50) {
                    $row['color_calificacion']  = '#DC143C'; 
                } else if ($calificacionNumerica >= 50 && $calificacionNumerica < 100) {
                    $row['color_calificacion']  = '#f1c40f'; 
                } else {
                    $row['color_calificacion']  = '#2ecc71'; 
                }
            }

            $db->closeConect();
            return array('output' => array('valid' => true, 'response' => $results));

        } catch (PDOException $th) {
            return Util::error_no_result();
        }
    }
}
