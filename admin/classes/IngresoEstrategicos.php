<?php

class IngresoEstrategicos
{

    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            if ($id > 0) {
                // Consulta específica por ID
                $q = "SELECT * FROM " . $db->getTable('tbl_ingreso_informacion_estrategicos') . " WHERE id = :id";
                $stmt = $pdo->prepare($q);
                $stmt->execute([':id' => $id]);
            } else {

                $q = "SELECT tbl_ingreso_informacion_estrategicos.*, 
                            tbl_vereda.nombre_vereda AS vereda, 
                            tbl_ciudades.municipio AS municipio, 
                            tbl_departamentos.departamento AS departamento, 
                            tbl_factores.tipo AS factor
                    FROM " . $db->getTable('tbl_ingreso_informacion_estrategicos') . "
                        INNER JOIN " . $db->getTable('tbl_departamentos') . " 
                        ON tbl_ingreso_informacion_estrategicos.codigo_departamento = tbl_departamentos.codigo_departamento
                        INNER JOIN " . $db->getTable('tbl_ciudades') . " 
                        ON tbl_ingreso_informacion_estrategicos.codigo_municipio = tbl_ciudades.codigo_muncipio
                        INNER JOIN " . $db->getTable('tbl_vereda') . " 
                        ON tbl_ingreso_informacion_estrategicos.tbl_vereda_id = tbl_vereda.id
                        INNER JOIN " . $db->getTable('tbl_factores') . " 
                        ON tbl_ingreso_informacion_estrategicos.tbl_factor_id = tbl_factores.id
                    ORDER BY tbl_departamentos.departamento, 
                            tbl_ciudades.municipio, 
                            tbl_vereda.nombre_vereda, 
                            tbl_factores.tipo";
                $stmt = $pdo->prepare($q);
                $stmt->execute();
            }

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
    }
    public static function getMunicipiosPorDepartamento($codigoDepartamento) {
        $db = new DbConection();
        $pdo = $db->openConect();
    
        try {
            $query = "
                SELECT DISTINCT tbl_ciudades.codigo_muncipio, tbl_ciudades.municipio 
                FROM tbl_ingreso_informacion_estrategicos
                INNER JOIN tbl_ciudades 
                ON tbl_ingreso_informacion_estrategicos.codigo_municipio = tbl_ciudades.codigo_muncipio
                WHERE tbl_ingreso_informacion_estrategicos.codigo_departamento = :codigo_departamento
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
                tbl_ingreso_informacion_estrategicos.id, 
                tbl_ingreso_informacion_estrategicos.dtcreate, 
                tbl_ingreso_informacion_estrategicos.codigo_municipio, 
                tbl_ingreso_informacion_estrategicos.foto1, 
                tbl_ingreso_informacion_estrategicos.foto2, 
                tbl_ingreso_informacion_estrategicos.foto3, 
                tbl_ingreso_informacion_estrategicos.foto4, 
                tbl_factores.tipo, 
                tbl_ingreso_informacion_estrategicos_x_actualizacion.foto_actualizada_1, 
                tbl_ingreso_informacion_estrategicos_x_actualizacion.foto_actualizada_2, 
                tbl_ingreso_informacion_estrategicos_x_actualizacion.foto_actualizada_3, 
                tbl_ingreso_informacion_estrategicos_x_actualizacion.foto_actualizada_4, 
                tbl_ingreso_informacion_estrategicos.observaciones, 
                tbl_ingreso_informacion_estrategicos_x_actualizacion.observaciones_actualizacion, 
                tbl_ciudades.municipio
            FROM 
                tbl_ingreso_informacion_estrategicos
            INNER JOIN 
                tbl_ciudades ON tbl_ingreso_informacion_estrategicos.codigo_municipio = tbl_ciudades.codigo_muncipio
            INNER JOIN 
                tbl_factores ON tbl_ingreso_informacion_estrategicos.tbl_factor_id = tbl_factores.id
            LEFT JOIN 
                tbl_ingreso_informacion_estrategicos_x_actualizacion ON tbl_ingreso_informacion_estrategicos.id = tbl_ingreso_informacion_estrategicos_x_actualizacion.tbl_ingreso_informacion_estrategicos_id
            WHERE 1=1
        ";

        // Aplicar filtros dinámicos si están definidos.
        $params = [];
        if (!empty($filtros['departamento_id'])) {
            $query .= " AND tbl_ciudades.codigo_departamento = ?";
            $params[] = $filtros['departamento_id'];
        }
        if (!empty($filtros['municipio_id'])) {
            $query .= " AND tbl_ingreso_informacion_estrategicos.codigo_municipio = ?";
            $params[] = $filtros['municipio_id'];
        }
        if (!empty($filtros['factor_id'])) {
            $query .= " AND tbl_ingreso_informacion_estrategicos.tbl_factor_id = ?";
            $params[] = $filtros['factor_id'];
        }
        if (!empty($filtros['fecha_inicial']) && !empty($filtros['fecha_final'])) {
            $query .= " AND tbl_ingreso_informacion_estrategicos.dtcreate BETWEEN ? AND ?";
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
        $valor = isset($rqst['valor']) ? ($rqst['valor']) : '';
        $longitud = isset($rqst['longitud']) ? ($rqst['longitud']) : '';
        $latitud = isset($rqst['latitud']) ? ($rqst['latitud']) : '';
        $observaciones = isset($rqst['observaciones']) ? ($rqst['observaciones']) : '';
        $foto1 = $rqst['foto1'] ?? '';
        $foto2 = $rqst['foto2'] ?? '';
        $foto3 = $rqst['foto3'] ?? '';
        $foto4 = $rqst['foto4'] ?? '';
        $codDepartamento_id = isset($rqst['codDepartamento_id']) ? intval($rqst['codDepartamento_id']) : Util::getDepartamentoPrincipal();
        $codMunicipio_id = isset($rqst['codMunicipio_id']) ? intval($rqst['codMunicipio_id']) : 0;
        $tbl_vereda_id = isset($rqst['vereda_id']) ? intval($rqst['vereda_id']) : 0;
        $tec_usuario_id =  intval($_SESSION['session_user']['id']);

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            // Actualiza la información
            $q = "SELECT id FROM " . $db->getTable('tbl_ingreso_informacion_estrategicos') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_ingreso_informacion_estrategicos');
                $arrfieldscomma = array(
                    'tbl_factor_id' => $factorId,
                    'codigo_departamento' => $codDepartamento_id,
                    'codigo_municipio' => $codMunicipio_id,
                    'valor' => $valor,
                    'longitud' => $longitud,
                    'latitud' => $latitud,
                    'tbl_vereda_id' => $tbl_vereda_id,
                    'tec_usuario_id' => $tec_usuario_id,
                    'foto1' => $foto1,
                    'foto2' => $foto2,
                    'foto3' => $foto3,
                    'foto4' => $foto4,
                );
                $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                $result = $pdo->query($q);
                if (!$result) {
                    $arrjson = Util::error_general();
                } else {
                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                }
            } else {
                $arrjson = Util::error_general();
            }
        } else {

            // Consulta para verificar existencia
            $qCheck = "SELECT COUNT(*) as count FROM " . $db->getTable('tbl_ingreso_informacion_estrategicos') . "
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

            if ($row['count'] > 0) {
                $db->closeConect();
                return Util::error_general('Ya existe un registro de ese factor, para el departamento, municipio y vereda seleccionada. Por favor verifique.');
            }

            // Inserción de nueva información
            if ($factorId > 0 && $codDepartamento_id > 0 && $codMunicipio_id > 0 && !empty($valor)) {
                $q = "INSERT INTO " . $db->getTable('tbl_ingreso_informacion_estrategicos') . " 
                        (dtcreate, valor, tbl_factor_id, codigo_municipio, codigo_departamento, tbl_vereda_id, longitud, latitud, observaciones, tec_usuario_id, foto1, foto2, foto3, foto4)
                        VALUES 
                        (" . Util::date_now_server() . ", :valor, :tbl_factor_id, :codigo_municipio, :codigo_departamento, :tbl_vereda_id, :longitud, :latitud, :observaciones, :tec_usuario_id, :foto1, :foto2, :foto3, :foto4)";

                $result = $pdo->prepare($q);

                $arrparam = array(
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
                );

                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general('Error al guardar la información.');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
        }

        $db->closeConect();
        return $arrjson;
    }
}
