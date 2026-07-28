<?php
require_once 'DbConection.php';
require_once 'Util.php';

/**
 * Clase para gestionar componentes municipales
 * Permite CRUD de componentes asociados a municipios específicos
 */
class ComponenteMunicipios
{
    /**
     * Carga datos para DataTables con paginación del lado del servidor
     * @param array $request - Parámetros de DataTables
     * @return array - Respuesta en formato DataTables
     */
    public function load($request)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Parámetros de paginación
            $start = isset($request['start']) ? (int)$request['start'] : 0;
            $length = isset($request['length']) ? (int)$request['length'] : 10;
            $searchValue = isset($request['search']['value']) ? $request['search']['value'] : '';
            $draw = isset($request['draw']) ? (int)$request['draw'] : 1;

            // Filtro opcional por municipio (para alcaldes)
            $municipio_filtro = isset($request['municipio_filtro']) ? $request['municipio_filtro'] : '';

            // Query base
            $sql = "SELECT
                        cm.*,
                        cau.municipio,
                        cau.nombre_mapa
                    FROM " . $db->getTable('tbl_componente_municipios') . " cm
                    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " cau
                        ON cm.codigo_municipio = cau.codigo_muncipio
                    WHERE 1=1";

            $params = [];

            // Filtro por municipio del usuario
            if (!empty($municipio_filtro)) {
                $munName = '';
                try {
                    $stmtMun = $pdo->prepare("SELECT LOWER(TRIM(municipio)) FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE CAST(codigo_muncipio AS CHAR) = :c LIMIT 1");
                    $stmtMun->execute([':c' => (string)$municipio_filtro]);
                    $munName = (string)$stmtMun->fetchColumn();
                } catch (Exception $e) {}
                $sql .= " AND (CAST(cm.codigo_municipio AS CHAR) = :municipio_filtro" . ($munName ? " OR LOWER(TRIM(cm.codigo_municipio)) = :munNombre" : "") . ")";
                $params[':municipio_filtro'] = (string)$municipio_filtro;
                if ($munName) $params[':munNombre'] = $munName;
            }

            // Búsqueda
            if (!empty($searchValue)) {
                $sql .= " AND (cm.nombre_componente LIKE :search
                          OR cau.municipio LIKE :search2
                          OR cm.habilitado LIKE :search3)";
                $params[':search'] = "%{$searchValue}%";
                $params[':search2'] = "%{$searchValue}%";
                $params[':search3'] = "%{$searchValue}%";
            }

            // Contar total (sin filtros de búsqueda, solo municipio)
            $sqlTotal = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_componente_municipios') . " cm
                         LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " cau
                             ON cm.codigo_municipio = cau.codigo_muncipio
                         WHERE 1=1";
            $totalParams = [];
            if (!empty($municipio_filtro)) {
                $sqlTotal .= " AND (CAST(cm.codigo_municipio AS CHAR) = :municipio_total" . (!empty($munName) ? " OR LOWER(TRIM(cm.codigo_municipio)) = :munNombreTotal" : "") . ")";
                $totalParams[':municipio_total'] = (string)$municipio_filtro;
                if (!empty($munName)) $totalParams[':munNombreTotal'] = $munName;
            }
            $stmtTotal = $pdo->prepare($sqlTotal);
            $stmtTotal->execute($totalParams);
            $recordsTotal = (int)$stmtTotal->fetchColumn();

            // Contar registros filtrados (con búsqueda)
            $sqlCount = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_componente_municipios') . " cm
                         LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " cau
                             ON cm.codigo_municipio = cau.codigo_muncipio
                         WHERE 1=1";
            $filterParams = [];

            if (!empty($municipio_filtro)) {
                $sqlCount .= " AND (CAST(cm.codigo_municipio AS CHAR) = :municipio_filtro" . (!empty($munName) ? " OR LOWER(TRIM(cm.codigo_municipio)) = :munNombreFiltro" : "") . ")";
                $filterParams[':municipio_filtro'] = (string)$municipio_filtro;
                if (!empty($munName)) $filterParams[':munNombreFiltro'] = $munName;
            }
            if (!empty($searchValue)) {
                $sqlCount .= " AND (cm.nombre_componente LIKE :search
                              OR cau.municipio LIKE :search2
                              OR cm.habilitado LIKE :search3)";
                $filterParams[':search'] = "%{$searchValue}%";
                $filterParams[':search2'] = "%{$searchValue}%";
                $filterParams[':search3'] = "%{$searchValue}%";
            }

            $stmtFiltered = $pdo->prepare($sqlCount);
            foreach ($filterParams as $key => $value) {
                $stmtFiltered->bindValue($key, $value);
            }
            $stmtFiltered->execute();
            $recordsFiltered = (int)$stmtFiltered->fetchColumn();

            // Obtener datos con paginación
            $sql .= " ORDER BY cm.nombre_componente ASC, cau.municipio ASC LIMIT :start, :length";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':length', $length, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $db->closeConect();

            return [
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ];
        } catch (PDOException $e) {
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
     * Obtiene un componente municipal por ID
     * @param int $id - ID del componente
     * @return array - Respuesta con valid y response
     */
    public static function getById($id)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "SELECT * FROM " . $db->getTable('tbl_componente_municipios') . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $db->closeConect();

            if ($result) {
                return [
                    'output' => [
                        'valid' => true,
                        'response' => $result
                    ]
                ];
            } else {
                return Util::error_no_result();
            }
        } catch (PDOException $e) {
            return Util::error_no_result();
        }
    }

    /**
     * Guarda un nuevo componente municipal
     * @param array $data - Datos del componente
     * @return array - Respuesta con valid, message y id
     */
    public static function save($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Validar campos requeridos
            if (empty($data['nombre_componente']) || empty($data['codigo_departamento']) || empty($data['codigo_municipio'])) {
                return [
                    'state' => false,
                    'message' => 'Faltan campos requeridos'
                ];
            }

            // Verificar si ya existe el componente para ese municipio
            $sqlCheck = "SELECT id FROM " . $db->getTable('tbl_componente_municipios') . "
                         WHERE nombre_componente = :nombre_componente
                         AND codigo_municipio = :codigo_municipio";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':nombre_componente' => trim($data['nombre_componente']),
                ':codigo_municipio' => $data['codigo_municipio']
            ]);

            if ($stmtCheck->fetch()) {
                return [
                    'state' => false,
                    'message' => 'Este componente ya existe para el municipio seleccionado'
                ];
            }

            // Insertar nuevo componente
            $sql = "INSERT INTO " . $db->getTable('tbl_componente_municipios') . "
                    (nombre_componente, codigo_departamento, codigo_municipio, habilitado, created_at)
                    VALUES (:nombre_componente, :codigo_departamento, :codigo_municipio, :habilitado, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nombre_componente', trim($data['nombre_componente']), PDO::PARAM_STR);
            $stmt->bindValue(':codigo_departamento', $data['codigo_departamento'], PDO::PARAM_STR);
            $stmt->bindValue(':codigo_municipio', $data['codigo_municipio'], PDO::PARAM_STR);
            $stmt->bindValue(':habilitado', $data['habilitado'] ?? 'si', PDO::PARAM_STR);
            $stmt->execute();

            $lastId = $pdo->lastInsertId();
            $db->closeConect();

            return [
                'state' => true,
                'message' => 'Componente municipal guardado exitosamente',
                'id' => $lastId
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un componente municipal existente
     * @param array $data - Datos del componente incluyendo id
     * @return array - Respuesta con state y message
     */
    public static function update($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Validar campos requeridos
            if (empty($data['id']) || empty($data['nombre_componente']) || empty($data['codigo_departamento']) || empty($data['codigo_municipio'])) {
                return [
                    'state' => false,
                    'message' => 'Faltan campos requeridos'
                ];
            }

            // Verificar si ya existe otro componente con el mismo nombre para ese municipio
            $sqlCheck = "SELECT id FROM " . $db->getTable('tbl_componente_municipios') . "
                         WHERE nombre_componente = :nombre_componente
                         AND codigo_municipio = :codigo_municipio
                         AND id != :id";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':nombre_componente' => trim($data['nombre_componente']),
                ':codigo_municipio' => $data['codigo_municipio'],
                ':id' => $data['id']
            ]);

            if ($stmtCheck->fetch()) {
                return [
                    'state' => false,
                    'message' => 'Ya existe otro componente con este nombre para el municipio seleccionado'
                ];
            }

            // Actualizar componente
            $sql = "UPDATE " . $db->getTable('tbl_componente_municipios') . "
                    SET nombre_componente = :nombre_componente,
                        codigo_departamento = :codigo_departamento,
                        codigo_municipio = :codigo_municipio,
                        habilitado = :habilitado,
                        updated_at = NOW()
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
            $stmt->bindValue(':nombre_componente', trim($data['nombre_componente']), PDO::PARAM_STR);
            $stmt->bindValue(':codigo_departamento', $data['codigo_departamento'], PDO::PARAM_STR);
            $stmt->bindValue(':codigo_municipio', $data['codigo_municipio'], PDO::PARAM_STR);
            $stmt->bindValue(':habilitado', $data['habilitado'] ?? 'si', PDO::PARAM_STR);
            $stmt->execute();

            $db->closeConect();

            return [
                'state' => true,
                'message' => 'Componente municipal actualizado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un componente municipal
     * @param int $id - ID del componente a eliminar
     * @return array - Respuesta con state y message
     */
    public static function delete($id)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "DELETE FROM " . $db->getTable('tbl_componente_municipios') . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $db->closeConect();

            return [
                'state' => true,
                'message' => 'Componente municipal eliminado exitosamente'
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene lista de componentes únicos para un municipio
     * @param string $codigo_municipio - Código del municipio
     * @return array - Array de componentes habilitados
     */
    public static function getComponentesPorMunicipio($codigo_municipio)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $sql = "SELECT DISTINCT nombre_componente
                    FROM " . $db->getTable('tbl_componente_municipios') . "
                    WHERE codigo_municipio = :codigo_municipio
                    AND habilitado = 'si'
                    ORDER BY nombre_componente ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':codigo_municipio', $codigo_municipio, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $db->closeConect();

            return [
                'output' => [
                    'valid' => true,
                    'response' => $results
                ]
            ];
        } catch (PDOException $e) {
            return Util::error_no_result();
        }
    }
}
