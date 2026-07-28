<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';

class Empresas
{
    public function __construct() {}

    private static function joinMunicipio($db): string
    {
        return 'FROM ' . $db->getTable('tbl_empresas') . ' e
                LEFT JOIN ' . $db->getTable('tbl_ciudades_accion_unificada') . ' c
                    ON e.codigo_muncipio = c.codigo_muncipio';
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $nombreEmpresa = isset($rqst['nombre_empresa']) ? trim($rqst['nombre_empresa']) : '';
        $nit = isset($rqst['nit']) ? trim($rqst['nit']) : '';
        $nombreContacto = isset($rqst['nombre_contacto']) ? trim($rqst['nombre_contacto']) : '';
        $telefonoContacto = isset($rqst['telefono_contacto']) ? trim($rqst['telefono_contacto']) : '';
        $emailContacto = isset($rqst['email_contacto']) ? trim($rqst['email_contacto']) : '';
        $codigoMunicipio = isset($rqst['codigo_muncipio']) ? intval($rqst['codigo_muncipio']) : 0;
        $userId = SessionData::getUserId() ?: 2;

        if ($nombreEmpresa === '' || $nombreContacto === '' || $telefonoContacto === '' || $codigoMunicipio <= 0) {
            return Util::error_missing_data();
        }

        if ($emailContacto !== '' && !filter_var($emailContacto, FILTER_VALIDATE_EMAIL)) {
            return Util::error_general('El correo electrónico no es válido.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            if ($id > 0) {
                $stmtCheck = $pdo->prepare('SELECT id FROM ' . $db->getTable('tbl_empresas') . ' WHERE id = :id LIMIT 1');
                $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtCheck->execute();
                if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                    return Util::error_general('No se encontró la empresa a actualizar.');
                }

                $table = $db->getTable('tbl_empresas');
                $arrfieldscomma = [
                    'nombre_empresa' => $nombreEmpresa,
                    'nit' => $nit !== '' ? $nit : null,
                    'nombre_contacto' => $nombreContacto,
                    'telefono_contacto' => $telefonoContacto,
                    'email_contacto' => $emailContacto !== '' ? $emailContacto : null,
                    'codigo_muncipio' => $codigoMunicipio,
                    'user_id' => $userId,
                ];
                $arrfieldsnocomma = ['dt_update' => Util::date_now_server()];
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);

                return $result
                    ? ['output' => ['valid' => true, 'id' => $id]]
                    : Util::error_general();
            }

            $q = 'INSERT INTO ' . $db->getTable('tbl_empresas') . '
                  (user_id, dt_create, dt_update, nombre_empresa, nit, nombre_contacto, telefono_contacto, email_contacto, codigo_muncipio)
                  VALUES (:user_id, ' . Util::date_now_server() . ', NULL, :nombre_empresa, :nit, :nombre_contacto, :telefono_contacto, :email_contacto, :codigo_muncipio)';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':nombre_empresa', $nombreEmpresa, PDO::PARAM_STR);
            $stmt->bindValue(':nit', $nit !== '' ? $nit : null, $nit !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':nombre_contacto', $nombreContacto, PDO::PARAM_STR);
            $stmt->bindValue(':telefono_contacto', $telefonoContacto, PDO::PARAM_STR);
            $stmt->bindValue(':email_contacto', $emailContacto !== '' ? $emailContacto : null, $emailContacto !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':codigo_muncipio', $codigoMunicipio, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return ['output' => ['valid' => true, 'response' => $pdo->lastInsertId()]];
            }

            return Util::error_general('No fue posible guardar la empresa.');
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function getByCodigoMunicipio($codigoMunicipio)
    {
        $codigoMunicipio = intval($codigoMunicipio);
        if ($codigoMunicipio <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = 'SELECT e.id, e.nombre_empresa, e.nit, e.nombre_contacto, e.telefono_contacto, e.email_contacto, e.dt_create
                  ' . self::joinMunicipio($db) . '
                  WHERE e.codigo_muncipio = :codigo_muncipio
                  ORDER BY e.nombre_empresa ASC';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':codigo_muncipio', $codigoMunicipio, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['output' => ['valid' => true, 'response' => $data]];
        } catch (Exception $e) {
            return Util::error_general('Error al obtener empresas: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        if ($id <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $stmt = $pdo->prepare('DELETE FROM ' . $db->getTable('tbl_empresas') . ' WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0
                ? ['output' => ['valid' => true, 'response' => $id]]
                : Util::error_general('No se encontró la empresa a eliminar.');
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
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
            $municipioFiltro = isset($data['municipio_filtro']) ? intval($data['municipio_filtro']) : 0;

            $columns = [
                'e.id',
                'c.municipio',
                'e.nombre_empresa',
                'e.nit',
                'e.nombre_contacto',
                'e.telefono_contacto',
                'e.email_contacto',
                'e.dt_create',
            ];

            $orderColumnIndex = $data['order'][0]['column'] ?? 1;
            $orderColumn = $columns[$orderColumnIndex] ?? 'e.nombre_empresa';
            $orderDir = $data['order'][0]['dir'] ?? 'asc';

            $join = self::joinMunicipio($db);
            $whereParts = [];
            $params = [];

            if ($municipioFiltro > 0) {
                $whereParts[] = 'e.codigo_muncipio = :municipioFiltro';
                $params[':municipioFiltro'] = $municipioFiltro;
            }

            if ($searchValue !== '') {
                $whereParts[] = '(
                    e.nombre_empresa LIKE :search OR
                    e.nit LIKE :search OR
                    e.nombre_contacto LIKE :search OR
                    e.telefono_contacto LIKE :search OR
                    e.email_contacto LIKE :search OR
                    c.municipio LIKE :search
                )';
                $params[':search'] = '%' . $searchValue . '%';
            }

            $where = !empty($whereParts) ? 'WHERE ' . implode(' AND ', $whereParts) : '';

            $stmtTotal = $pdo->query('SELECT COUNT(*) FROM ' . $db->getTable('tbl_empresas'));
            $recordsTotal = $stmtTotal->fetchColumn();

            if ($where) {
                $stmtFiltered = $pdo->prepare('SELECT COUNT(*) ' . $join . ' ' . $where);
                $stmtFiltered->execute($params);
                $recordsFiltered = $stmtFiltered->fetchColumn();
            } else {
                $recordsFiltered = $recordsTotal;
            }

            $query = 'SELECT e.*, c.municipio
                      ' . $join . '
                      ' . $where . '
                      ORDER BY ' . $orderColumn . ' ' . $orderDir . '
                      LIMIT :start, :length';

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

    public static function editEmpresa($data)
    {
        try {
            $id = isset($data) ? intval($data) : 0;
            if ($id <= 0) {
                return ['state' => false, 'message' => 'ID inválido'];
            }

            $db = new DbConection();
            $pdo = $db->openConect();

            $q = 'SELECT e.*, c.municipio
                  ' . self::joinMunicipio($db) . '
                  WHERE e.id = :id';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($dataList) {
                return ['state' => true, 'data' => $dataList];
            }

            return ['state' => false, 'message' => 'No se encontró el registro'];
        } catch (PDOException $th) {
            return ['state' => false, 'message' => $th->getMessage()];
        }
    }
}
