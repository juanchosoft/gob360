<?php

class IngresoInformacion
{

    public function __construct() {}

    public static function getAll($rqst)
    {
        $data = $rqst ?? [];
        $draw = $data['draw'] ?? 1;
        $start = intval($data['start'] ?? 0);
        $length = intval($data['length'] ?? 10);
        $searchValue = $data['search']['value'] ?? '';

        try {
            session_start();
            require_once 'SessionData.php';
            require_once 'DbConection.php';
            require_once 'Util.php';

            $db = new DbConection();
            $pdo = $db->openConect();

            $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
            $userType = SessionData::getUserType();
            $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
            $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
            $isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

            $fromClause = "FROM " . $db->getTable('tbl_ingreso_informacion') . "
            INNER JOIN " . $db->getTable('tbl_departamentos') . " 
                ON tbl_ingreso_informacion.codigo_departamento = tbl_departamentos.codigo_departamento
            INNER JOIN " . $db->getTable('tbl_ciudades') . " 
                ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades.codigo_muncipio
            INNER JOIN " . $db->getTable('tbl_vereda') . " 
                ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id
            INNER JOIN " . $db->getTable('tbl_factores') . " 
                ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id";

            $where = [];
            $params = [];

            if ($isAlcalde) {
                $where[] = "tbl_ingreso_informacion.codigo_municipio = :municipio";
                $params[':municipio'] = $municipioUsuarioLogueado;
            }

            if ($isSecretario) {
                $where[] = "tbl_ingreso_informacion.codigo_departamento = :departamento";
                $params[':departamento'] = '68';
            }

            if (!empty($searchValue)) {
                $where[] = "(tbl_vereda.nombre_vereda LIKE :search 
                     OR tbl_ciudades.municipio LIKE :search 
                     OR tbl_departamentos.departamento LIKE :search 
                     OR tbl_factores.tipo LIKE :search)";
                $params[':search'] = '%' . $searchValue . '%';
            }

            $whereClause = (count($where) > 0) ? ' WHERE ' . implode(' AND ', $where) : '';

            $totalQuery = "SELECT COUNT(*) $fromClause";
            $stmtTotal = null;

            if ($isAlcalde) {
                $stmtTotal = $pdo->prepare($totalQuery . " WHERE tbl_ingreso_informacion.codigo_municipio = :municipio");
                $stmtTotal->execute([':municipio' => $municipioUsuarioLogueado]);
            } elseif ($isSecretario) {
                $stmtTotal = $pdo->prepare($totalQuery . " WHERE tbl_ingreso_informacion.codigo_departamento = :departamento");
                $stmtTotal->execute([':departamento' => '68']);
            } else {
                $stmtTotal = $pdo->query($totalQuery);
            }

            $recordsTotal = $stmtTotal->fetchColumn();

            $filteredQuery = "SELECT COUNT(*) $fromClause $whereClause";
            $stmtFiltered = $pdo->prepare($filteredQuery);
            $stmtFiltered->execute($params);
            $recordsFiltered = $stmtFiltered->fetchColumn();

            $selectClause = "SELECT tbl_ingreso_informacion.*, 
                    tbl_vereda.nombre_vereda AS vereda, 
                    tbl_ciudades.municipio AS municipio, 
                    tbl_departamentos.departamento AS departamento, 
                    tbl_factores.tipo AS factor,
                    tbl_factores.icono AS icono";


            $orderClause = " ORDER BY tbl_vereda.nombre_vereda ASC";

            $limitClause = " LIMIT :start, :length";

            $query = "$selectClause $fromClause $whereClause $orderClause $limitClause";
            $stmt = $pdo->prepare($query);

            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }

            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':length', $length, PDO::PARAM_INT);

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $arrjson = [
                'draw' => intval($draw),
                'recordsTotal' => intval($recordsTotal),
                'recordsFiltered' => intval($recordsFiltered),
                'data' => $result,
            ];
        } catch (Exception $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();

        echo json_encode($arrjson);
    }



    /* public static function getAll($rqst)
    {

        $limit = 30;
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();


        try {
            if ($id > 0) {
                // Consulta específica por ID
                $q = "SELECT * FROM " . $db->getTable('tbl_ingreso_informacion') . " WHERE id = :id ";
                $stmt = $pdo->prepare($q);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            } else {
                // Validamos para mostrar los datos según el rol del usuario
                $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
                $userType = SessionData::getUserType();
                $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

                $q = "SELECT tbl_ingreso_informacion.*, 
                                tbl_vereda.nombre_vereda AS vereda, 
                                tbl_ciudades.municipio AS municipio, 
                                tbl_departamentos.departamento AS departamento, 
                                tbl_factores.tipo AS factor
                        FROM " . $db->getTable('tbl_ingreso_informacion') . "
                        INNER JOIN " . $db->getTable('tbl_departamentos') . " 
                            ON tbl_ingreso_informacion.codigo_departamento = tbl_departamentos.codigo_departamento
                        INNER JOIN " . $db->getTable('tbl_ciudades') . " 
                            ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades.codigo_muncipio
                        INNER JOIN " . $db->getTable('tbl_vereda') . " 
                            ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id
                        INNER JOIN " . $db->getTable('tbl_factores') . " 
                            ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id";

                // Si no es administrador o superadministrador, filtrar por municipio
                if (!$isAdmin) {
                    $q .= " WHERE tbl_ingreso_informacion.codigo_municipio = :municipio";
                }

                $q .= " ORDER BY tbl_departamentos.departamento, 
                                tbl_ciudades.municipio, 
                                tbl_vereda.nombre_vereda, 
                                tbl_factores.tipo
                        LIMIT :limit";

                $stmt = $pdo->prepare($q);

                // Enlazar parámetros
                if (!$isAdmin) {
                    $stmt->bindParam(':municipio', $municipioUsuarioLogueado, PDO::PARAM_INT);
                }
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


            if ($result) {
                $arrjson = ['output' => ['valid' => true, 'response' => $result]];
            } else {
                $arrjson = Util::error_no_result();
            }
        } catch (Exception $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    } */

    public static function getMunicipiosPorDepartamento($codigoDepartamento)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $query = "
                SELECT DISTINCT tbl_ciudades.codigo_muncipio, tbl_ciudades.municipio 
                FROM " . $db->getTable('tbl_ingreso_informacion') . " 
                INNER JOIN " . $db->getTable('tbl_ciudades') . " 
                ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades.codigo_muncipio
                WHERE tbl_ingreso_informacion.codigo_departamento = :codigo_departamento
                ORDER BY tbl_ciudades.municipio";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':codigo_departamento' => $codigoDepartamento]);

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                return ['output' => ['valid' => true, 'response' => $result]];
            } else {
                return ['output' => ['valid' => false, 'response' => []]];
            }
        } catch (Exception $e) {
            return ['output' => ['valid' => false, 'error' => $e->getMessage()]];
        } finally {
            $db->closeConect();
        }
    }
    public static function getHistorialImagenes($filtros = [])
    {
        $db = Database::getConnection(); // Asegúrate de usar tu conexión a la base de datos.

        // Base de la consulta.
        $query = "
            SELECT 
                tbl_ingreso_informacion.id, 
                tbl_ingreso_informacion.dtcreate, 
                tbl_ingreso_informacion.codigo_municipio, 
                tbl_ingreso_informacion.foto1, 
                tbl_ingreso_informacion.foto2, 
                tbl_ingreso_informacion.foto3, 
                tbl_ingreso_informacion.foto4, 
                tbl_factores.tipo, 
                tbl_ingreso_informacion_x_actualizacion.foto_actualizada_1, 
                tbl_ingreso_informacion_x_actualizacion.foto_actualizada_2, 
                tbl_ingreso_informacion_x_actualizacion.foto_actualizada_3, 
                tbl_ingreso_informacion_x_actualizacion.foto_actualizada_4, 
                tbl_ingreso_informacion.observaciones, 
                tbl_ingreso_informacion_x_actualizacion.observaciones_actualizacion, 
                tbl_ciudades.municipio
            FROM 
                " . $db->getTable('tbl_ingreso_informacion') . "  
            INNER JOIN 
                " . $db->getTable('tbl_ciudades') . "    ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades.codigo_muncipio
            INNER JOIN 
                " . $db->getTable('tbl_factores') . "  ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
            LEFT JOIN 
                " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . "  ON tbl_ingreso_informacion.id = tbl_ingreso_informacion_x_actualizacion.tbl_ingreso_informacion_id
            WHERE 1=1
        ";
        // Aplicar filtros dinámicos si están definidos.
        $params = [];
        if (!empty($filtros['departamento_id'])) {
            $query .= " AND tbl_ciudades.codigo_departamento = ?";
            $params[] = $filtros['departamento_id'];
        }
        if (!empty($filtros['municipio_id'])) {
            $query .= " AND tbl_ingreso_informacion.codigo_municipio = ?";
            $params[] = $filtros['municipio_id'];
        }
        if (!empty($filtros['factor_id'])) {
            $query .= " AND tbl_ingreso_informacion.tbl_factor_id = ?";
            $params[] = $filtros['factor_id'];
        }
        if (!empty($filtros['fecha_inicial']) && !empty($filtros['fecha_final'])) {
            $query .= " AND tbl_ingreso_informacion.dtcreate BETWEEN ? AND ?";
            $params[] = $filtros['fecha_inicial'];
            $params[] = $filtros['fecha_final'];
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $factorId = isset($rqst['factorId']) ? intval($rqst['factorId']) : 0;
        $valor = isset($rqst['valor']) ? floatval($rqst['valor']) : 0;
        $longitud = isset($rqst['longitud']) ? ($rqst['longitud']) : null;
        $latitud = isset($rqst['latitud']) ? ($rqst['latitud']) : null;
        $observaciones = isset($rqst['observaciones']) ? ($rqst['observaciones']) : '';
        $foto1 = $rqst['foto1'] ?? '';
        $foto2 = $rqst['foto2'] ?? '';
        $foto3 = $rqst['foto3'] ?? '';
        $foto4 = $rqst['foto4'] ?? '';
        $codDepartamento_id = isset($rqst['codDepartamento_id']) ? intval($rqst['codDepartamento_id']) : Util::getDepartamentoPrincipal();
        $codMunicipio_id = isset($rqst['codMunicipio_id']) ? intval($rqst['codMunicipio_id']) : 0;
        $tbl_vereda_id = isset($rqst['vereda_id']) ? intval($rqst['vereda_id']) : 0;
        $tec_usuario_id = intval($_SESSION['session_user']['id']);

        if ($longitud === "" || $latitud === "") {
            return Util::info_general('Latitud y/o Longitud son campos obligatorios.');
        }
        if (!is_numeric($valor)) {
            return Util::info_general('El valor deber ser correcto');
        }
        if ($valor == 0) {
            return Util::info_general('El valor debe ser mayor a 1');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            // Obtener el valor actual antes de modificarlo
            $q = "SELECT valor, valor_inicial FROM " . $db->getTable('tbl_ingreso_informacion') . " WHERE id = :id";
            $stmt = $pdo->prepare($q);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $valorActual = $row['valor']; // Valor actual antes de modificarlo
                $valorInicial = $row['valor_inicial'];

                // Si "valor_inicial" es NULL, guardar el valor actual antes de la modificación
                if ($valorInicial === null) {
                    $valorInicial = $valorActual;
                }

                $table = $db->getTable('tbl_ingreso_informacion');
                $arrfieldscomma = array(
                    'longitud' => $longitud,
                    'latitud' => $latitud,
                    'valor' => $valor,  // **Solo se actualiza `valor`**
                    'valor_inicial' => $valorInicial, // **Se asegura que "valor_inicial" tenga el primer valor**
                    'fecha_modificacion' => date('Y-m-d H:i:s') // Establece la fecha actual en formato estándar
                );
                $arrfieldsnocomma = array();

                // Generar la consulta de actualización
                $q = Util::make_query_update($table, "id = :id", $arrfieldscomma, $arrfieldsnocomma);
                $stmt = $pdo->prepare($q);
                $stmt->bindParam(':id', $id);
                $result = $stmt->execute();

                $db->closeConect();

                if ($result) {
                    return array('output' => array('valid' => true, 'id' => $id, 'valor' => $valor, 'valor_inicial' => $valorInicial));
                } else {
                    return Util::error_general('Error al actualizar la información.');
                }
            } else {
                return Util::error_general('Registro no encontrado.');
            }
        } else {
            // Consulta para verificar existencia antes de insertar
            $qCheck = "SELECT COUNT(*) as count FROM " . $db->getTable('tbl_ingreso_informacion') . "
        WHERE tbl_factor_id = :tbl_factor_id 
        AND codigo_municipio = :codigo_municipio 
        AND codigo_departamento = :codigo_departamento 
        AND tbl_vereda_id = :tbl_vereda_id";

            $stmtCheck = $pdo->prepare($qCheck);
            $stmtCheck->execute([
                ':tbl_factor_id' => $factorId,
                ':codigo_municipio' => $codMunicipio_id,
                ':codigo_departamento' => $codDepartamento_id,
                ':tbl_vereda_id' => $tbl_vereda_id,
            ]);

            $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            // if ($row['count'] > 0) {
            //     $db->closeConect();
            //     return Util::error_general('Ya existe un registro de ese factor, para el departamento, municipio y vereda seleccionada. Por favor verifique.');
            // }

            // Inserción de nueva información
            if ($factorId > 0 && $codDepartamento_id > 0 && $codMunicipio_id > 0 && !empty($valor)) {
                $q = "INSERT INTO " . $db->getTable('tbl_ingreso_informacion') . " 
                    (dtcreate, valor, valor_inicial, tbl_factor_id, codigo_municipio, codigo_departamento, tbl_vereda_id, longitud, latitud, observaciones, tec_usuario_id, foto1, foto2, foto3, foto4)
                    VALUES 
                    (" . Util::date_now_server() . ", :valor, :valor, :tbl_factor_id, :codigo_municipio, :codigo_departamento, :tbl_vereda_id, :longitud, :latitud, :observaciones, :tec_usuario_id, :foto1, :foto2, :foto3, :foto4)";

                $stmt = $pdo->prepare($q);
                $stmt->execute([
                    ':valor' => $valor,
                    ':tbl_factor_id' => $factorId,
                    ':codigo_municipio' => $codMunicipio_id,
                    ':codigo_departamento' => $codDepartamento_id,
                    ':tbl_vereda_id' => $tbl_vereda_id,
                    ':longitud' => $longitud,
                    ':latitud' => $latitud,
                    ':observaciones' => $observaciones,
                    ':tec_usuario_id' => $tec_usuario_id,
                    ':foto1' => $foto1,
                    ':foto2' => $foto2,
                    ':foto3' => $foto3,
                    ':foto4' => $foto4
                ]);

                $db->closeConect();
                return array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
            } else {
                return Util::error_missing_data();
            }
        }
    }


    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        if ($id <= 0) {
            return Util::error_general('Item es requerido para eliminar.');
        }
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "DELETE FROM " . $db->getTable('tbl_ingreso_informacion') . " WHERE id = :id";
            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $arrjson = array('output' => array('valid' => true, 'response' =>  $id));
            } else {
                $arrjson = Util::error_general('Error al eliminar el registro.');
            }
        } catch (Exception $e) {
            $arrjson = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    public static function search($rqst)
    {

        $search = isset($rqst['search']) ? ($rqst['search']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();

        // Validamos para mostrar los datos según el rol del usuario
        $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
        $userType = SessionData::getUserType();
        $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

        $q = "SELECT tbl_ingreso_informacion.*, 
                        tbl_vereda.nombre_vereda AS vereda, 
                        tbl_ciudades.municipio AS municipio, 
                        tbl_departamentos.departamento AS departamento, 
                        tbl_factores.tipo AS factor
                FROM " . $db->getTable('tbl_ingreso_informacion') . "
                INNER JOIN " . $db->getTable('tbl_departamentos') . " 
                    ON tbl_ingreso_informacion.codigo_departamento = tbl_departamentos.codigo_departamento
                INNER JOIN " . $db->getTable('tbl_ciudades') . " 
                    ON tbl_ingreso_informacion.codigo_municipio = tbl_ciudades.codigo_muncipio
                INNER JOIN " . $db->getTable('tbl_vereda') . " 
                    ON tbl_ingreso_informacion.tbl_vereda_id = tbl_vereda.id
                INNER JOIN " . $db->getTable('tbl_factores') . " 
                    ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id";

        // Filtros de búsqueda
        $searchCondition = '';
        if (!empty($search)) {
            $searchCondition = " (tbl_departamentos.departamento LIKE :search 
                                            OR tbl_ciudades.municipio LIKE :search 
                                            OR tbl_vereda.nombre_vereda LIKE :search 
                                            OR tbl_factores.tipo LIKE :search)";
        }

        // Si no es administrador o superadministrador, filtrar por municipio
        if (!$isAdmin) {
            $q .= " WHERE tbl_ingreso_informacion.codigo_municipio = :municipio";
            if (!empty($searchCondition)) {
                $q .= " AND " . $searchCondition;
            }
        } else {
            if (!empty($searchCondition)) {
                $q .= " WHERE " . $searchCondition;
            }
        }

        $q .= " ORDER BY tbl_departamentos.departamento, 
                                    tbl_ciudades.municipio, 
                                    tbl_vereda.nombre_vereda, 
                                    tbl_factores.tipo";

        // Preparar la consulta
        $stmt = $pdo->prepare($q);

        // Asignar parámetros
        if (!$isAdmin) {
            $stmt->bindParam(':municipio', $municipioUsuarioLogueado, PDO::PARAM_STR);
        }
        if (!empty($search)) {
            $searchParam = "%$search%";
            $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
        }
        // $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);


        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $arrjson = ['output' => ['valid' => true, 'response' => $result]];
        } else {
            $arrjson = Util::error_no_result();
        }

        $db->closeConect();

        return $arrjson;
    }
}
