<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';

/**
 * Operaciones CRUD sobre tbl_puntajes (configuración de rangos por factor de inestabilidad).
 * tipo: 1 = Inicial (mapa inicial), 2 = Final (mapa actual).
 */
class Configuracion_Puntaje
{
    public const TIPO_INICIAL = 1;
    public const TIPO_FINAL = 2;

    public function __construct() {}

    private static function joinFactoresInestabilidad($db)
    {
        return "FROM " . $db->getTable('tbl_puntajes') . " p
                LEFT JOIN " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " g
                    ON p.tbl_factores_gobernacion_id = g.id";
    }

    private static function categoriaSelectSql()
    {
        return "COALESCE(g.nombre_categoria, IF(p.tbl_factores_gobernacion_id = 10000, 'General (Todos)', 'Sin categoría')) AS categoria";
    }

    private static function normalizarTipo($tipo)
    {
        $tipo = intval($tipo);
        return ($tipo === self::TIPO_FINAL) ? self::TIPO_FINAL : self::TIPO_INICIAL;
    }

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tipo = isset($rqst['tipo']) ? self::normalizarTipo($rqst['tipo']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            if ($id > 0) {
                $query = "SELECT p.*, " . self::categoriaSelectSql() . "
                        " . self::joinFactoresInestabilidad($db) . "
                        WHERE p.id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            } else {
                $whereTipo = $tipo > 0 ? " WHERE p.tipo = :tipo" : "";
                $query = "SELECT p.*, " . self::categoriaSelectSql() . "
                        " . self::joinFactoresInestabilidad($db) . "
                        $whereTipo
                        ORDER BY p.tipo ASC, categoria ASC, p.rango_desde ASC";
                $stmt = $pdo->prepare($query);
                if ($tipo > 0) {
                    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_INT);
                }
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $response = ['output' => ['valid' => true, 'response' => $results]];
            } else {
                $response = Util::error_no_result();
            }
        } catch (Exception $e) {
            $response = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $name = isset($rqst['name']) ? trim($rqst['name']) : '';
        $factorGobernacionId = isset($rqst['factorGobernacionId']) ? intval($rqst['factorGobernacionId']) : 0;
        $desde = isset($rqst['desde']) ? intval($rqst['desde']) : null;
        $hasta = isset($rqst['hasta']) ? intval($rqst['hasta']) : null;
        $tipo_medicion = isset($rqst['tipo_medicion']) ? trim($rqst['tipo_medicion']) : '';
        $color = isset($rqst['color']) ? trim($rqst['color']) : '';
        $tipo = self::normalizarTipo($rqst['tipo'] ?? self::TIPO_INICIAL);
        $tbl_usuario_id = SessionData::getUserId() ?: 2;

        if (
            $name === '' ||
            $factorGobernacionId <= 0 ||
            $tipo_medicion === '' ||
            $color === '' ||
            $color === 'Seleccione' ||
            $desde === null ||
            $hasta === null ||
            $desde > $hasta
        ) {
            return Util::error_missing_data();
        }

        if (!Configuracion_Puntaje::validarRango($desde, $hasta, $factorGobernacionId, $tipo_medicion, $tipo, $id)) {
            return Util::error_general('El rango se cruza con un rango existente para la misma categoría, tipo de mapa y tipo de medición.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            $q = "SELECT id FROM " . $db->getTable('tbl_puntajes') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_puntajes');
                $arrfieldscomma = [
                    'name' => $name,
                    'tbl_factores_gobernacion_id' => $factorGobernacionId,
                    'rango_desde' => $desde,
                    'rango_hasta' => $hasta,
                    'tipo_medicion' => $tipo_medicion,
                    'tbl_usuario_id' => $tbl_usuario_id,
                    'color' => $color,
                    'tipo' => $tipo,
                ];
                $arrfieldsnocomma = ['dtcreate' => Util::date_now_server()];
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                $arrjson = $result
                    ? ['output' => ['valid' => true, 'id' => $id]]
                    : Util::error_general();
            } else {
                $arrjson = Util::error_general();
            }
        } else {
            $q = "INSERT INTO " . $db->getTable('tbl_puntajes') . "
                    (dtcreate, name, tbl_factores_gobernacion_id, rango_desde, rango_hasta, tbl_usuario_id, tipo_medicion, color, tipo)
                    VALUES (" . Util::date_now_server() . ", :name, :tbl_factores_gobernacion_id, :rango_desde, :rango_hasta, :tbl_usuario_id, :tipo_medicion, :color, :tipo)";
            $result = $pdo->prepare($q);
            $arrparam = [
                ':name' => $name,
                ':tbl_factores_gobernacion_id' => $factorGobernacionId,
                ':rango_desde' => $desde,
                ':rango_hasta' => $hasta,
                ':tbl_usuario_id' => $tbl_usuario_id,
                ':tipo_medicion' => $tipo_medicion,
                ':color' => $color,
                ':tipo' => $tipo,
            ];
            $arrjson = $result->execute($arrparam)
                ? ['output' => ['valid' => true, 'response' => $pdo->lastInsertId()]]
                : Util::error_general(' Al guardar ingreso de configuración');
        }

        $db->closeConect();
        return $arrjson;
    }

    public static function validarRango($desde, $hasta, $factorGobernacionId, $tipo_medicion, $tipo, $idExcluir = null)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        $tipo = self::normalizarTipo($tipo);

        $query = "SELECT id FROM " . $db->getTable('tbl_puntajes') . "
                        WHERE tbl_factores_gobernacion_id = :factorGobernacionId
                        AND tipo_medicion = :tipo_medicion
                        AND tipo = :tipo
                        AND (
                            (rango_desde BETWEEN :desde AND :hasta)
                            OR (rango_hasta BETWEEN :desde AND :hasta)
                            OR (:desde BETWEEN rango_desde AND rango_hasta)
                            OR (:hasta BETWEEN rango_desde AND rango_hasta)
                        )";
        if ($idExcluir) {
            $query .= " AND id != :idExcluir";
        }

        $stmt = $pdo->prepare($query);
        $params = [
            ':factorGobernacionId' => $factorGobernacionId,
            ':tipo_medicion' => $tipo_medicion,
            ':tipo' => $tipo,
            ':desde' => $desde,
            ':hasta' => $hasta,
        ];

        if ($idExcluir) {
            $params[':idExcluir'] = $idExcluir;
        }

        $stmt->execute($params);
        $db->closeConect();

        return $stmt->rowCount() === 0;
    }

    public static function load($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $draw = $data['draw'] ?? 1;
            $start = $data['start'] ?? 0;
            $length = $data['length'] ?? 10;
            $searchValue = $data['search']['value'] ?? '';
            $filtroTipo = isset($data['filtroTipo']) ? intval($data['filtroTipo']) : 0;

            $columns = [
                'p.id',
                'p.name',
                'categoria',
                'p.tipo',
                'p.tipo_medicion',
                'p.rango_desde',
                'p.rango_hasta',
                'p.color',
            ];

            $orderColumnIndex = $data['order'][0]['column'] ?? 0;
            $orderColumn = $columns[$orderColumnIndex] ?? 'p.id';
            $orderDir = $data['order'][0]['dir'] ?? 'asc';

            $join = self::joinFactoresInestabilidad($db);
            $categoriaExpr = "COALESCE(g.nombre_categoria, IF(p.tbl_factores_gobernacion_id = 10000, 'General (Todos)', 'Sin categoría'))";

            $whereParts = [];
            $params = [];

            if ($filtroTipo === self::TIPO_INICIAL || $filtroTipo === self::TIPO_FINAL) {
                $whereParts[] = "p.tipo = :filtroTipo";
                $params[':filtroTipo'] = $filtroTipo;
            }

            if (!empty($searchValue)) {
                $whereParts[] = "(
                p.name LIKE :search OR
                {$categoriaExpr} LIKE :search OR
                p.tipo_medicion LIKE :search OR
                p.rango_desde LIKE :search OR
                p.rango_hasta LIKE :search OR
                p.color LIKE :search OR
                CASE p.tipo WHEN 1 THEN 'Inicial' WHEN 2 THEN 'Final' ELSE '' END LIKE :search
                )";
                $params[':search'] = '%' . $searchValue . '%';
            }

            $where = !empty($whereParts) ? 'WHERE ' . implode(' AND ', $whereParts) : '';

            $stmtTotal = $pdo->query("SELECT COUNT(*) FROM " . $db->getTable('tbl_puntajes'));
            $recordsTotal = $stmtTotal->fetchColumn();

            if ($where) {
                $stmtFiltered = $pdo->prepare("SELECT COUNT(*) $join $where");
                $stmtFiltered->execute($params);
                $recordsFiltered = $stmtFiltered->fetchColumn();
            } else {
                $recordsFiltered = $recordsTotal;
            }

            $query = "
            SELECT
                p.*,
                " . self::categoriaSelectSql() . "
            $join
            $where
            ORDER BY $orderColumn $orderDir
            LIMIT :start, :length";

            $stmt = $pdo->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':start', (int) $start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int) $length, PDO::PARAM_INT);

            $stmt->execute();
            $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'draw' => (int) $draw,
                'recordsTotal' => (int) $recordsTotal,
                'recordsFiltered' => (int) $recordsFiltered,
                'data' => $dataList,
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage(),
            ];
        }
    }

    public function editPuntaje($data)
    {
        try {
            $id = isset($data) ? intval($data) : 0;

            if ($id <= 0) {
                return [
                    'state' => false,
                    'message' => 'ID inválido',
                ];
            }

            $db = new DbConection();
            $pdo = $db->openConect();

            $q = "SELECT p.*, " . self::categoriaSelectSql() . "
                  " . self::joinFactoresInestabilidad($db) . "
                  WHERE p.id = :id";
            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataList && count($dataList) > 0) {
                return [
                    'state' => true,
                    'data' => $dataList,
                ];
            }

            return [
                'state' => false,
                'message' => 'No se encontró el registro',
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage(),
            ];
        }
    }
}
