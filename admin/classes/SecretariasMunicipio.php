<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class SecretariasMunicipio {

    public function __construct(){}

        /**
     * Obtiene todas las secretarías municipales con filtros opcionales
     *
     * Este método filtra automáticamente las secretarías según el tipo de usuario:
     * - Alcaldes y auxiliares: Solo secretarías de su municipio
     * - Otros roles: Todas las secretarías habilitadas
     *
     * @param array $rqst Array con parámetros opcionales:
     *                    - id: ID específico de la secretaría
     *                    - codigo_municipio: Filtrar por código de municipio
     *
     * @return array Array con secretarías en formato estándar
     */
    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $codigo_municipio = isset($rqst['codigo_municipio']) ? trim($rqst['codigo_municipio']) : null;

        $db = null;
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Construcción dinámica de condiciones WHERE
            $where = ["1 = 1"];
            $params = [];

            // Filtro solo por habilitado = 'si' (lo haremos opcional)
            $where[] = "habilitado = 'si'";

            // Filtro por ID específico
            if ($id > 0) {
                $where[] = "id = :id";
                $params[':id'] = $id;
            }

            // Obtener tipo de usuario y código de municipio de la sesión
            $tipoUsuario = SessionData::getUserType();
            $codigoMunicipioUsuario = SessionData::getCodigoMunicipio();

            // Filtro automático para alcaldes y auxiliares de alcalde
            if (Util::Alcalde() == $tipoUsuario || Util::Auxiliar_Alcalde() == $tipoUsuario) {
                if (!empty($codigoMunicipioUsuario)) {
                    // Asegurar que se compare correctamente (puede ser string o int)
                    $where[] = "CAST(codigo_municipio AS CHAR) = :codigo_municipio_usuario";
                    $params[':codigo_municipio_usuario'] = strval($codigoMunicipioUsuario);
                }
            } elseif ($codigo_municipio !== null) {
                // Filtro opcional por código de municipio para otros usuarios
                $where[] = "CAST(codigo_municipio AS CHAR) = :codigo_municipio";
                $params[':codigo_municipio'] = strval($codigo_municipio);
            }

            // Construcción de la consulta
            $query = "SELECT
                        id,
                        secretaria,
                        secretario,
                        correo,
                        codigo_departamento,
                        codigo_municipio,
                        habilitado,
                        created_at,
                        updated_at
                    FROM " . $db->getTable('tbl_secretarias_municipios') . "
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY secretaria ASC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Construir respuesta
            if (!empty($data)) {
                $response = [
                    'output' => [
                        'valid' => true,
                        'response' => $data,
                        'total' => count($data)
                    ]
                ];
            } else {
                // DEBUG: Consultar si hay datos en la tabla sin filtros estrictos
                $checkQuery = "SELECT COUNT(*) as total FROM " . $db->getTable('tbl_secretarias_municipios');
                $checkStmt = $pdo->query($checkQuery);
                $totalRows = $checkStmt->fetch(PDO::FETCH_ASSOC);

                $response = Util::error_no_result();
            }
        } catch (Exception $e) {

            $response = Util::error_general("Error al obtener secretarías: " . $e->getMessage());
        } finally {
            if ($db !== null) {
                $db->closeConect();
            }
        }

        return $response;
    }

    /**
     * Obtiene información de una secretaría municipal específica por código de municipio
     * NOTA: Esta función ahora consulta tbl_secretarias_municipios para el módulo de alcaldías
     *
     * @param array $rqst Array con parámetros:
     *                    - secretariaId: ID de la secretaría municipal
     *                    - codigoMunicipio: Código del municipio
     * @return array Array con información de la secretaría
     */
    public static function getAllSecretariaByCodigoMunicipio($rqst){

        $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;
        $codigo_municipio = isset($rqst['codigoMunicipio']) ? ($rqst['codigoMunicipio']) : 0;

        if ($secretariaId > 0 && $codigo_municipio > 0) {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Consulta usando tbl_secretarias_municipios para alcaldías
            $q = "
            SELECT
                tbl_proyectos_alcaldias.id AS tbl_proyecto_id,
                tbl_proyectos_alcaldias.proyecto,
                tbl_proyectos_alcaldias.valor_proyecto,
                tbl_proyectos_alcaldias.porcentaje_ejecucion,
                tbl_proyectos_alcaldias.fecha_entrega,
                tbl_proyectos_alcaldias.estado,
                tbl_proyectos_alcaldias.observaciones,
                tbl_secretarias_municipios.id AS secretaria_id,
                tbl_secretarias_municipios.secretaria,
                tbl_secretarias_municipios.secretario,
                tbl_ciudades_accion_unificada.municipio,
                tbl_vereda.nombre_vereda,
                tbl_vereda.id AS vereda_id
            FROM " . $db->getTable('tbl_proyectos_alcaldias') . "
            INNER JOIN " . $db->getTable('tbl_secretarias_municipios') . "
                ON tbl_proyectos_alcaldias.tbl_secretarias_id = tbl_secretarias_municipios.id
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "
                ON tbl_proyectos_alcaldias.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
            LEFT JOIN " . $db->getTable('tbl_vereda') . "
                ON tbl_proyectos_alcaldias.tbl_vereda_id = tbl_vereda.id
            WHERE
                tbl_secretarias_municipios.id = $secretariaId
                AND tbl_proyectos_alcaldias.tbl_municipio_id = $codigo_municipio
            ORDER BY
                tbl_vereda.nombre_vereda ASC,
                tbl_proyectos_alcaldias.proyecto ASC";

            $result = $pdo->query($q);
            $proyectos = array();
            if ($result) {
                foreach ($result as $valor) {
                    $proyectos[] = $valor;
                }

                // Construir la respuesta
                if (!empty($proyectos)) {
                    $arrjson = [
                        'output' => [
                            'valid' => true,
                            'response' => [
                                'proyectos' => $proyectos
                            ]
                        ]
                    ];
                } else {
                    $arrjson = Util::error_no_result();
                }

            } else {
                $arrjson = Util::error_no_result();
            }
            $db->closeConect();
            return $arrjson;

        } else {
            return Util::error_missing_data();
        }
    }

    /**
     * Obtiene todas las secretarías municipales con la suma de proyectos de alcaldías
     * Similar a Secretarias::getAllproyectos() pero para alcaldías
     *
     * @param array $rqst Array con filtros opcionales
     * @return array Array con secretarías y suma de proyectos
     */
    public static function getAllProyectos($rqst = null)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener tipo de usuario y código de municipio
            $tipoUsuario = SessionData::getUserType();
            $codigoMunicipioUsuario = SessionData::getCodigoMunicipio();

            $where = [];
            $params = [];

            // Filtro automático para alcaldes y auxiliares
            if (Util::Alcalde() == $tipoUsuario || Util::Auxiliar_Alcalde() == $tipoUsuario) {
                if (!empty($codigoMunicipioUsuario)) {
                    $where[] = "s.codigo_municipio = :codigo_municipio";
                    $params[':codigo_municipio'] = $codigoMunicipioUsuario;
                    error_log("SecretariasMunicipio::getAllProyectos - FILTRO APLICADO para municipio: " . $codigoMunicipioUsuario);
                } else {
                    error_log("SecretariasMunicipio::getAllProyectos - ADVERTENCIA: Código de municipio vacío para alcalde");
                }
            } else {
                error_log("SecretariasMunicipio::getAllProyectos - Usuario no es alcalde, no se aplica filtro de municipio");
            }

            $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

            // IMPORTANTE: Filtrar proyectos por municipio también
            $query = "SELECT
                        s.id as tbl_secretarias_id,
                        s.secretaria,
                        s.secretario,
                        s.codigo_municipio,
                        'si' as mostrar,
                        COALESCE(SUM(p.valor_proyecto), 0) as sumaproyectos,
                        COUNT(p.id) as total_proyectos
                    FROM " . $db->getTable('tbl_secretarias_municipios') . " s
                    LEFT JOIN " . $db->getTable('tbl_proyectos_alcaldias') . " p
                        ON s.id = p.tbl_secretarias_id
                        AND CAST(p.tbl_municipio_id AS CHAR) = CAST(s.codigo_municipio AS CHAR)
                    $whereClause
                    GROUP BY s.id, s.secretaria, s.secretario, s.codigo_municipio
                    HAVING sumaproyectos > 0
                    ORDER BY sumaproyectos DESC, s.secretaria ASC";


            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultados
                ]
            ];
        } catch (Exception $e) {
            
            return Util::error_general("Error al obtener secretarías con proyectos: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Obtiene todos los proyectos de alcaldías filtrados por secretaría municipal
     * Similar a Proyectos::getAllproyectosxsecre() pero para alcaldías
     *
     * @param array $rqst Array con parámetros:
     *                    - id o secretaria: ID de la secretaría municipal
     * @return array Array con proyectos de la secretaría
     */
    public static function getAllProyectosxSecre($rqst)
    {
        $secretaria_id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        if ($secretaria_id == 0 && isset($rqst['secretaria'])) {
            $secretaria_id = intval($rqst['secretaria']);
        }

        if ($secretaria_id <= 0) {
            return Util::error_general('Debe especificar una secretaría válida.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Obtener tipo de usuario y código de municipio
            $tipoUsuario = SessionData::getUserType();
            $codigoMunicipioUsuario = SessionData::getCodigoMunicipio();

            // IMPORTANTE: Verificar que la secretaría pertenece al municipio del alcalde
            // Esto previene que un alcalde acceda a secretarías de otros municipios
            if (Util::Alcalde() == $tipoUsuario || Util::Auxiliar_Alcalde() == $tipoUsuario) {
                if (!empty($codigoMunicipioUsuario)) {
                    // Primero ver qué municipio tiene la secretaría
                    $secretariaQuery = "SELECT codigo_municipio FROM " . $db->getTable('tbl_secretarias_municipios') . " WHERE id = :secretaria_id";
                    $secStmt = $pdo->prepare($secretariaQuery);
                    $secStmt->execute([':secretaria_id' => $secretaria_id]);
                    $secResult = $secStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$secResult) {
                        return Util::error_general("Secretaría no encontrada");
                    }
                    // Convertir ambos a string para comparación
                    $codigoSecretaria = strval($secResult['codigo_municipio']);
                    $codigoAlcalde = strval($codigoMunicipioUsuario);

                    if ($codigoSecretaria !== $codigoAlcalde) {
                        return [
                            'output' => [
                                'valid' => true,
                                'response' => []
                            ]
                        ];
                    }
                }
            }

            // Filtrar solo por secretaría (ya verificamos que pertenece al municipio del alcalde)
            $where = ["p.tbl_secretarias_id = :secretaria_id"];
            $params = [':secretaria_id' => $secretaria_id];

            $where = ["p.tbl_secretarias_id = :secretaria_id"];
            $params = [':secretaria_id' => $secretaria_id];

            $whereClause = "WHERE " . implode(' AND ', $where);

            $query = "SELECT
                        p.id, p.proyecto, p.valor_proyecto, p.estado_proyecto as estado,
                        p.dtcreatedatetime as dtcreate, p.fecha as fecha_entrega,
                        p.tbl_municipio_id, p.tbl_secretarias_id, p.tbl_vereda_id,
                        COALESCE(cau.municipio, c.municipio) as municipio,
                        COALESCE(s.secretaria, sg.secretaria) as secretaria,
                        p.proyecto as nombre
                    FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " p
                    LEFT JOIN " . $db->getTable('tbl_ciudades') . " c
                        ON CAST(p.tbl_municipio_id AS CHAR) = CAST(c.codigo_muncipio AS CHAR)
                    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " cau
                        ON CAST(p.tbl_municipio_id AS CHAR) = CAST(cau.codigo_muncipio AS CHAR)
                    LEFT JOIN " . $db->getTable('tbl_secretarias_municipios') . " s
                        ON p.tbl_secretarias_id = s.id
                    LEFT JOIN " . $db->getTable('tbl_secretarias') . " sg
                        ON p.tbl_secretarias_id = sg.id
                    $whereClause
                    ORDER BY p.dtcreatedatetime DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si no hay resultados, verificar si existen proyectos para esta secretaría
            if (empty($resultados)) {
                $checkQuery = "SELECT COUNT(*) as total
                              FROM " . $db->getTable('tbl_proyectos_alcaldias') . "
                              WHERE tbl_secretarias_id = :secretaria_id";
                $checkStmt = $pdo->prepare($checkQuery);
                $checkStmt->execute([':secretaria_id' => $secretaria_id]);
                $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
            }

            $response = [
                'output' => [
                    'valid' => true,
                    'response' => $resultados
                ]
            ];

            return $response;

        } catch (Exception $e) {
            return Util::error_general("Error al obtener proyectos por secretaría: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }


}