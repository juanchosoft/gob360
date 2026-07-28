<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';

/**
 * Clase para gestión de secretarías municipales
 * @author SPIDERSOFTWARE
 */
class SecretariasMunicipios
{
    public function __construct() {}

    /**
     * Obtener todas las secretarías municipales
     */
    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $where = [];
            $params = [];

            if ($id > 0) {
                $where[] = "id = :id";
                $params[':id'] = $id;
            }

            $whereClause = count($where) > 0 ? " WHERE " . implode(' AND ', $where) : "";

            $query = "SELECT sm.*, c.municipio
                      FROM " . $db->getTable('tbl_secretarias_municipios') . " sm
                      LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                        ON sm.codigo_municipio COLLATE utf8mb3_unicode_ci = c.codigo_muncipio COLLATE utf8mb3_unicode_ci" .
                      $whereClause .
                      " ORDER BY c.municipio ASC, sm.secretaria ASC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = $data
                ? ['output' => ['valid' => true, 'response' => $data]]
                : Util::error_no_result();
        } catch (Exception $e) {
            $response = Util::error_general("Error al obtener secretarías: " . $e->getMessage());
        }

        if (isset($db)) {
            $db->closeConect();
        }

        return $response;
    }

    /**
     * Obtener secretarías por código de municipio
     */
    public static function getByMunicipio($rqst)
    {
        $codigo_municipio = isset($rqst['codigo_municipio']) ? trim($rqst['codigo_municipio']) : '';

        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $query = "SELECT sm.*, c.municipio
                      FROM " . $db->getTable('tbl_secretarias_municipios') . " sm
                      LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                        ON sm.codigo_municipio COLLATE utf8mb3_unicode_ci = c.codigo_muncipio COLLATE utf8mb3_unicode_ci
                      WHERE sm.codigo_municipio = :codigo_municipio
                        AND sm.habilitado = 'si'
                      ORDER BY sm.secretaria ASC";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':codigo_municipio', $codigo_municipio);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = ['output' => ['valid' => true, 'response' => $data]];
        } catch (Exception $e) {
            $response = Util::error_general("Error al obtener secretarías: " . $e->getMessage());
        }

        if (isset($db)) {
            $db->closeConect();
        }

        return $response;
    }

    /**
     * Cargar datos para DataTables
     */
    public static function load($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $draw = $data['draw'] ?? 1;
            $start = $data['start'] ?? 0;
            $length = $data['length'] ?? 10;
            $searchValue = $data['search']['value'] ?? '';
            $orderColumnIndex = $data['order'][0]['column'] ?? 0;
            $orderDirection = $data['order'][0]['dir'] ?? 'asc';
            $municipioFiltro = $data['municipio_filtro'] ?? ''; // Filtro por municipio para Alcaldes

            $columns = [
                'sm.secretaria',
                'sm.secretario',
                'sm.correo',
                'sm.habilitado'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'sm.id';

            // Construir WHERE clause
            $whereConditions = [];
            $params = [];

            // Filtro por municipio (para Alcaldes)
            if (!empty($municipioFiltro)) {
                $whereConditions[] = "sm.codigo_municipio = :municipio_filtro";
                $params[':municipio_filtro'] = $municipioFiltro;
            }

            // Filtro de búsqueda
            if (!empty($searchValue)) {
                $whereConditions[] = "(sm.secretaria LIKE :search OR sm.correo LIKE :search OR sm.secretario LIKE :search OR c.municipio LIKE :search)";
                $params[':search'] = '%' . $searchValue . '%';
            }

            $where = !empty($whereConditions) ? " WHERE " . implode(' AND ', $whereConditions) : '';

            $table = $db->getTable('tbl_secretarias_municipios');
            $tableCiudades = $db->getTable('tbl_ciudades_accion_unificada');

            $from = "FROM $table sm LEFT JOIN $tableCiudades c ON sm.codigo_municipio COLLATE utf8mb3_unicode_ci = c.codigo_muncipio COLLATE utf8mb3_unicode_ci";

            // Total de registros sin filtro (pero con filtro de municipio si aplica)
            $whereBasico = !empty($municipioFiltro) ? " WHERE sm.codigo_municipio = :municipio_filtro" : '';
            $stmt = $pdo->prepare("SELECT COUNT(*) $from $whereBasico");
            if (!empty($municipioFiltro)) {
                $stmt->bindValue(':municipio_filtro', $municipioFiltro);
            }
            $stmt->execute();
            $recordsTotal = $stmt->fetchColumn();

            // Total con filtro (si aplica)
            $stmt = $pdo->prepare("SELECT COUNT(*) $from $where");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $recordsFiltered = $stmt->fetchColumn();

            // Consulta de datos con orden y paginación
            $sql = "SELECT sm.*, c.municipio $from $where ORDER BY $orderColumn $orderDirection LIMIT :start, :length";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "draw" => intval($draw),
                "recordsTotal" => intval($recordsTotal),
                "recordsFiltered" => intval($recordsFiltered),
                "data" => $data,
            ];
        } catch (PDOException $th) {
            // Devolver formato compatible con DataTables para evitar error "d is undefined"
            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => $th->getMessage()
            ];
        }
    }

    /**
     * Crear nueva secretaría municipal
     */
    public static function newSecretaria($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Validaciones
            $secretaria = trim($data['secretaria'] ?? '');
            $secretario = trim($data['secretario'] ?? '');
            $correo     = trim($data['correo'] ?? '');
            $codigo_departamento = trim($data['codigo_departamento'] ?? '');
            $codigo_municipio = trim($data['codigo_municipio'] ?? '');
            $habilitado = trim($data['mostrar'] ?? 'si');

            if (empty($secretaria) || empty($secretario) || empty($correo) || empty($codigo_departamento) || empty($codigo_municipio)) {
                return [
                    'state' => false,
                    'message' => 'Todos los campos obligatorios deben ser completados.'
                ];
            }

            // Validación de permisos: Usuarios municipales solo pueden crear secretarías de su municipio
            $userType = SessionData::getUserType();
            $tiposUsuarioMunicipal = [
                'Alcalde',
                'Auxiliar Alcalde',
                'Secretario Despacho Alcalde',
                'Auxiliar Secretario Despacho Alcalde'
            ];
            $isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);

            if ($isUsuarioMunicipal) {
                $municipioUsuario = SessionData::getCodigoMunicipio();
                if ($codigo_municipio !== $municipioUsuario) {
                    return [
                        'state' => false,
                        'message' => 'No tienes permisos para crear secretarías de otros municipios.'
                    ];
                }
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return [
                    'state' => false,
                    'message' => 'El correo electrónico no es válido.'
                ];
            }

            // Verificar si ya existe
            $check = $pdo->prepare("SELECT COUNT(*) FROM " . $db->getTable('tbl_secretarias_municipios') . "
                                    WHERE secretaria = :secretaria AND codigo_municipio = :codigo_municipio");
            $check->bindParam(':secretaria', $secretaria);
            $check->bindParam(':codigo_municipio', $codigo_municipio);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return [
                    'state' => false,
                    'message' => 'Ya existe una secretaría con ese nombre en este municipio.'
                ];
            }

            // Insertar
            $stmt = $pdo->prepare("INSERT INTO " . $db->getTable('tbl_secretarias_municipios') . "
                                   (secretaria, secretario, correo, codigo_departamento, codigo_municipio, habilitado)
                                   VALUES (:secretaria, :secretario, :correo, :codigo_departamento, :codigo_municipio, :habilitado)");
            $stmt->bindParam(':secretaria', $secretaria);
            $stmt->bindParam(':secretario', $secretario);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':codigo_departamento', $codigo_departamento);
            $stmt->bindParam(':codigo_municipio', $codigo_municipio);
            $stmt->bindParam(':habilitado', $habilitado);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Secretaría registrada correctamente.'
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener datos de una secretaría para editar
     */
    public static function editSecretaria($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $id = $data ?? null;

            $sql = "SELECT * FROM " . $db->getTable('tbl_secretarias_municipios') . "
                WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
     * Actualizar secretaría municipal
     */
    public static function updateSecretaria($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $id = intval($data['id'] ?? 0);
            $secretaria = trim($data['secretaria'] ?? '');
            $secretario = trim($data['secretario'] ?? '');
            $correo     = trim($data['correo'] ?? '');
            $codigo_departamento = trim($data['codigo_departamento'] ?? '');
            $codigo_municipio = trim($data['codigo_municipio'] ?? '');
            $habilitado = trim($data['mostrar'] ?? 'si');

            if ($id === 0 || empty($secretaria) || empty($secretario) || empty($correo) || empty($codigo_departamento) || empty($codigo_municipio)) {
                return [
                    'state' => false,
                    'message' => 'Todos los campos obligatorios deben ser completados.'
                ];
            }

            // Validación de permisos: Usuarios municipales solo pueden editar secretarías de su municipio
            $userType = SessionData::getUserType();
            $tiposUsuarioMunicipal = [
                'Alcalde',
                'Auxiliar Alcalde',
                'Secretario Despacho Alcalde',
                'Auxiliar Secretario Despacho Alcalde'
            ];
            $isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);

            if ($isUsuarioMunicipal) {
                $municipioUsuario = SessionData::getCodigoMunicipio();
                if ($codigo_municipio !== $municipioUsuario) {
                    return [
                        'state' => false,
                        'message' => 'No tienes permisos para editar secretarías de otros municipios.'
                    ];
                }
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return [
                    'state' => false,
                    'message' => 'El correo electrónico no es válido.'
                ];
            }

            // Verificar duplicados
            $check = $pdo->prepare("SELECT COUNT(*) FROM " . $db->getTable('tbl_secretarias_municipios') . "
                                    WHERE secretaria = :secretaria AND codigo_municipio = :codigo_municipio AND id != :id");
            $check->bindParam(':secretaria', $secretaria);
            $check->bindParam(':codigo_municipio', $codigo_municipio);
            $check->bindParam(':id', $id, PDO::PARAM_INT);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return [
                    'state' => false,
                    'message' => 'Ya existe otra secretaría con ese nombre en este municipio.'
                ];
            }

            // Actualizar
            $stmt = $pdo->prepare("UPDATE " . $db->getTable('tbl_secretarias_municipios') . "
                                   SET secretaria = :secretaria,
                                       secretario = :secretario,
                                       correo = :correo,
                                       codigo_departamento = :codigo_departamento,
                                       codigo_municipio = :codigo_municipio,
                                       habilitado = :habilitado,
                                       updated_at = NOW()
                                   WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':secretaria', $secretaria);
            $stmt->bindParam(':secretario', $secretario);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':codigo_departamento', $codigo_departamento);
            $stmt->bindParam(':codigo_municipio', $codigo_municipio);
            $stmt->bindParam(':habilitado', $habilitado);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Secretaría actualizada correctamente.'
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
