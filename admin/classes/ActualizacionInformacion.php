<?php

class ActualizacionInformacion
{

    public function __construct() {}
public static function save($rqst)
{
    $id = $rqst['id'] ?? 0;
    $tbl_ingreso_informacion_id = $rqst['tbl_ingreso_informacion_id'] ?? 0;

    $actoresId = $rqst['actoresId'] ?? 0;
    $factorId = $rqst['factorId'] ?? 0;
    $valor_actualizacion = $rqst['valor_actualizacion'] ?? 0.0;
    $valor_actual = $rqst['valor_actual'] ?? 0.0;
    $accion_realizada = trim($rqst['accion_realizada'] ?? '');

    $fotos = [
        $rqst['foto1'] ?? '',
        $rqst['foto2'] ?? '',
        $rqst['foto3'] ?? '',
        $rqst['foto4'] ?? ''
    ];

    $tec_usuario_id = $_SESSION['session_user']['id'] ?? 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    try {

        if ($tbl_ingreso_informacion_id <= 0) {
            throw new Exception("ID de ingreso inválido. No se puede actualizar.");
        }

        if ($valor_actualizacion > $valor_actual) {
            throw new Exception("El valor de actualización no puede ser mayor al valor actual.");
        }

        $pdo->beginTransaction();

        // 1️⃣ Insertar historial SIEMPRE
        self::insertarNuevoRegistroActualizacion(
            $pdo,
            $db,
            $valor_actualizacion,
            $tbl_ingreso_informacion_id,
            $actoresId,
            $tec_usuario_id,
            $fotos,
            $accion_realizada
        );

        // 2️⃣ Actualizar el valor actual del registro original
        self::actualizarValorIngresoInformacion(
            $pdo,
            $db,
            $tbl_ingreso_informacion_id,
            $valor_actualizacion
        );

        $pdo->commit();

        return [
            'output' => [
                'valid' => true,
                'id' => $tbl_ingreso_informacion_id
            ]
        ];

    } catch (Exception $e) {

        $pdo->rollBack();
        return Util::error_general($e->getMessage());

    } finally {
        $db->closeConect();
    }
}


    private static function updateRegistroExistente($pdo, $db, $id, $factorId, $actoresId, $valor_actualizacion, $tbl_vereda_id, $tec_usuario_id, $fotos)
    {
        $table = $db->getTable('tbl_ingreso_informacion_x_actualizacion');
        $fields = [
            'tbl_factor_id' => $factorId,
            'tbl_actor_id' => $actoresId,
            'valor_actualizacion' => $valor_actualizacion,
            'tbl_vereda_id' => $tbl_vereda_id,
            'tec_usuario_id' => $tec_usuario_id,
            'foto_actualizada_1' => $fotos[0],
            'foto_actualizada_2' => $fotos[1],
            'foto_actualizada_3' => $fotos[2],
            'foto_actualizada_4' => $fotos[3],
        ];
        $fieldsNoComma = ['dtcreate' => Util::date_now_server()];

        $query = Util::make_query_update($table, 'id = :id', $fields, $fieldsNoComma);
        $stmt = $pdo->prepare($query);
        if (!$stmt->execute([':id' => $id])) {
            throw new Exception('Error al actualizar el registro.');
        }
    }

    private static function verificarSiExisteIngresoInformacion($pdo, $db, $factorId, $codMunicipio_id, $codDepartamento_id, $tbl_vereda_id)
    {
        $query = "SELECT id, valor FROM " . $db->getTable('tbl_ingreso_informacion') . " 
                    WHERE tbl_factor_id = :tbl_factor_id 
                    AND codigo_municipio = :codigo_municipio 
                    AND codigo_departamento = :codigo_departamento 
                    AND tbl_vereda_id = :tbl_vereda_id 
                    LIMIT 1";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':tbl_factor_id' => $factorId,
            ':codigo_municipio' => $codMunicipio_id,
            ':codigo_departamento' => $codDepartamento_id,
            ':tbl_vereda_id' => $tbl_vereda_id,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private static function insertarNuevoRegistroActualizacion($pdo, $db, $valor_actualizacion, $tbl_ingreso_informacion_id, $actoresId, $tec_usuario_id, $fotos, $accion_realizada = '')
    {
        $query = "INSERT INTO " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " 
                    (dtcreate, valor_actualizacion, tbl_ingreso_informacion_id, tbl_actor_id, tec_usuario_id, observaciones_actualizacion, 
                    foto_actualizada_1, foto_actualizada_2, foto_actualizada_3, foto_actualizada_4)
                    VALUES 
                    (:dtcreate, :valor_actualizacion, :tbl_ingreso_informacion_id, :tbl_actor_id, :tec_usuario_id, :observaciones_actualizacion, 
                    :foto_actualizada_1, :foto_actualizada_2, :foto_actualizada_3, :foto_actualizada_4)";

        $stmt = $pdo->prepare($query);
        if (!$stmt->execute([
            ':dtcreate' => Util::date(),
            ':valor_actualizacion' => $valor_actualizacion,
            ':tbl_ingreso_informacion_id' => $tbl_ingreso_informacion_id,
            ':tbl_actor_id' => $actoresId,
            ':tec_usuario_id' => $tec_usuario_id,
            ':observaciones_actualizacion' => $accion_realizada,
            ':foto_actualizada_1' => $fotos[0],
            ':foto_actualizada_2' => $fotos[1],
            ':foto_actualizada_3' => $fotos[2],
            ':foto_actualizada_4' => $fotos[3],
        ])) {
            throw new Exception('Error al guardar la actualización de información.');
        }
    }


    private static function actualizarValorIngresoInformacion($pdo, $db, $tbl_ingreso_informacion_id, $valor_actualizacion)
    {
        $query = "UPDATE " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " 
                    SET valor_actualizacion = :valor_actualizacion 
                    WHERE id = :id";

        $stmt = $pdo->prepare($query);
        if (!$stmt->execute([
            ':valor_actualizacion' => $valor_actualizacion,
            ':id' => $tbl_ingreso_informacion_id,
        ])) {
            throw new Exception('Error al actualizar el valor de ingreso.');
        }
    }
    
    public static function obtenerRegistros($rqst)
    {
        $tbl_vereda_id = $rqst['vereda_id'] ?? $rqst['tbl_vereda_id'] ?? 0; 
        $codMunicipio_id = $rqst['codMunicipio_id'] ?? 0;   
        $factorId = $rqst['factorId'] ?? 0;

        $db = new DbConection();
        $pdo = $db->openConect();
        $arrjson = [];

        if ((int)$tbl_vereda_id === 0) {
            $db->closeConect();
            return ['output' => ['valid' => true, 'data' => []]];
        }
        
        try {
            $tbl_ingreso = $db->getTable('tbl_ingreso_informacion');
            $tbl_factores = $db->getTable('tbl_factores');
            $tbl_actualizacion = $db->getTable('tbl_ingreso_informacion_x_actualizacion');


            $subquery_max_id = "
                SELECT 
                    tbl_ingreso_informacion_id, 
                    MAX(id) AS max_actualizacion_id
                FROM {$tbl_actualizacion}
                GROUP BY tbl_ingreso_informacion_id
            ";


            $query = "SELECT 
                        t1.id, 
                        t1.valor AS valor_inicial,            
                        
                        COALESCE(t5.valor_actualizacion, t1.valor) AS valor, 
                        
                        t1.dtcreate, 
                        t2.tipo_medicion, 
                        t2.tipo AS nombre_factor
                    FROM {$tbl_ingreso} t1
                    INNER JOIN {$tbl_factores} t2 
                        ON t1.tbl_factor_id = t2.id 
                    

                    LEFT JOIN ({$subquery_max_id}) t4 
                        ON t4.tbl_ingreso_informacion_id = t1.id
                    

                    LEFT JOIN {$tbl_actualizacion} t5 
                        ON t5.id = t4.max_actualizacion_id
                        
                    WHERE t1.codigo_municipio = :codigo_municipio 
                    AND t1.tbl_vereda_id = :tbl_vereda_id"; 

            $params = [
                ':codigo_municipio' => (int)$codMunicipio_id,
                ':tbl_vereda_id' => (int)$tbl_vereda_id 
            ];
            

            $factor_id_int = (int)$factorId; 
            
            if ($factor_id_int > 0) {
                $query .= " AND t1.tbl_factor_id = :tbl_factor_id";
                $params[':tbl_factor_id'] = $factor_id_int;
            }

            $query .= " ORDER BY t2.tipo, t1.dtcreate DESC"; 

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


            $arrjson = ['output' => ['valid' => true, 'data' => $data]]; 
        } catch (Exception $e) {
            error_log("Error en obtenerRegistros: " . $e->getMessage()); 
            $arrjson = ['output' => ['valid' => false, 'error' => "Error interno del servidor."]];
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Obtiene el valor total acumulado (tbl_ingreso_informacion.valor) 
     * por municipio para un factor y departamento dados, para pintar el mapa inicial.
     * @param int $codDepartamento_id El código del departamento a consultar.
     * @param int $factorId El ID del factor a usar para la suma.
     * @return array Un array de municipios con su valor total actual.
     */
    public static function obtenerDatosMapaInicial($codDepartamento_id, $factorId)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        
        try {
            // Suma el valor (cantidad) de todas las veredas para obtener el total por municipio
            $query = "SELECT codigo_municipio, SUM(valor) AS valor_total 
                      FROM " . $db->getTable('tbl_ingreso_informacion') . " 
                      WHERE codigo_departamento = :codigo_departamento 
                      AND tbl_factor_id = :tbl_factor_id
                      GROUP BY codigo_municipio"; // La clave es agrupar por municipio

            $params = [
                ':codigo_departamento' => (int)$codDepartamento_id,
                ':tbl_factor_id' => (int)$factorId,
            ];

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['output' => ['valid' => true, 'data' => $data]];

        } catch (Exception $e) {
            // Devolver un error controlado si la consulta falla
            return ['output' => ['valid' => false, 'error' => $e->getMessage()]];
        } finally {
            $db->closeConect();
        }
    }



    public static function obtenerRegistrosActualizadosPorMunicipio($codDepartamento_id, $codMunicipio_id, $secretariaId, $accionNombre)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        
        try {

            $queryFactor = "SELECT id FROM " . $db->getTable('tbl_factores') . " WHERE tipo = :accionNombre LIMIT 1";
            $stmtFactor = $pdo->prepare($queryFactor);
            $stmtFactor->execute([':accionNombre' => $accionNombre]);
            $factor = $stmtFactor->fetch(PDO::FETCH_ASSOC);

            if (!$factor) {
                return ['output' => ['valid' => true, 'response' => [], 'mensaje' => 'No se encontró el factor asociado a la acción.']];
            }
            $factorId = $factor['id'];


            $queryIngresoIds = "SELECT id FROM " . $db->getTable('tbl_ingreso_informacion') . " 
                                WHERE codigo_departamento = :codigo_departamento 
                                AND codigo_municipio = :codigo_municipio 
                                AND tbl_factor_id = :tbl_factor_id";

            $stmtIngresoIds = $pdo->prepare($queryIngresoIds);
            $stmtIngresoIds->execute([
                ':codigo_departamento' => $codDepartamento_id,
                ':codigo_municipio' => $codMunicipio_id,
                ':tbl_factor_id' => $factorId,
            ]);
            $ingresoIds = $stmtIngresoIds->fetchAll(PDO::FETCH_COLUMN, 0);

            if (empty($ingresoIds)) {
                return ['output' => ['valid' => true, 'response' => [], 'mensaje' => 'No hay registros de ingreso para este filtro.']];
            }
            
            $placeholders = implode(',', array_fill(0, count($ingresoIds), '?'));
            $query = "SELECT t1.*, t3.nombre AS nombre_vereda
                      FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " t1
                      INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " t2 ON t1.tbl_ingreso_informacion_id = t2.id
                      INNER JOIN " . $db->getTable('tbl_vereda') . " t3 ON t2.tbl_vereda_id = t3.id
                      WHERE t1.tbl_ingreso_informacion_id IN ($placeholders)
                      ORDER BY t1.dtcreate DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($ingresoIds);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['output' => ['valid' => true, 'response' => $data]];

        } catch (Exception $e) {
            return ['output' => ['valid' => false, 'error' => $e->getMessage()]];
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Factores del municipio que tienen al menos una fila en x_actualizacion.
     * @return array{output: array{valid: bool, response: array<int, int>}} map factor_id => cantidad
     */
    public static function getFactoresConActualizacionPorMunicipio($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? (int) $rqst['codigo_municipio'] : 0;
        $inestabilidadId = isset($rqst['inestabilidadId']) ? (int) $rqst['inestabilidadId'] : 10000;
        $codigoTodos = 10000;

        if ($codigoMunicipio <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $whereInest = ($inestabilidadId !== $codigoTodos)
                ? ' AND f.tbl_factor_inestabilidad_id = :inestabilidadId'
                : ' AND f.tbl_factor_inestabilidad_id IS NOT NULL';

            $q = 'SELECT i.tbl_factor_id, COUNT(a.id) AS total
                  FROM ' . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . ' a
                  INNER JOIN ' . $db->getTable('tbl_ingreso_informacion') . ' i
                      ON i.id = a.tbl_ingreso_informacion_id
                  INNER JOIN ' . $db->getTable('tbl_factores') . ' f
                      ON f.id = i.tbl_factor_id
                  WHERE i.codigo_municipio = :codigo_municipio
                    ' . $whereInest . '
                  GROUP BY i.tbl_factor_id';
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':codigo_municipio', $codigoMunicipio, PDO::PARAM_INT);
            if ($inestabilidadId !== $codigoTodos) {
                $stmt->bindValue(':inestabilidadId', $inestabilidadId, PDO::PARAM_INT);
            }
            $stmt->execute();

            $map = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $map[(int) $row['tbl_factor_id']] = (int) $row['total'];
            }

            return ['output' => ['valid' => true, 'response' => $map]];
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Detalle de actualizaciones (observaciones + fotos) para un factor en un municipio.
     */
    public static function getDetalleActualizacionFactorMunicipio($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? (int) $rqst['codigo_municipio'] : 0;
        $factorId = isset($rqst['tbl_factor_id']) ? (int) $rqst['tbl_factor_id'] : 0;

        if ($codigoMunicipio <= 0 || $factorId <= 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $q = "SELECT a.id, a.dtcreate, a.valor_actualizacion, a.observaciones_actualizacion,
                         a.foto_actualizada_1, a.foto_actualizada_2, a.foto_actualizada_3, a.foto_actualizada_4,
                         i.valor AS valor_inicial, i.id AS ingreso_id,
                         v.nombre_vereda,
                         f.tipo AS nombre_factor,
                         COALESCE(act.nombre, '') AS nombre_actor
                  FROM " . $db->getTable('tbl_ingreso_informacion_x_actualizacion') . " a
                  INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . " i
                      ON i.id = a.tbl_ingreso_informacion_id
                  LEFT JOIN " . $db->getTable('tbl_vereda') . " v
                      ON v.id = i.tbl_vereda_id
                  INNER JOIN " . $db->getTable('tbl_factores') . " f
                      ON f.id = i.tbl_factor_id
                  LEFT JOIN " . $db->getTable('tbl_actores_mapa') . " act
                      ON act.id = a.tbl_actor_id
                  WHERE i.codigo_municipio = :codigo_municipio
                    AND i.tbl_factor_id = :factor_id
                  ORDER BY a.dtcreate DESC, a.id DESC";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':codigo_municipio', $codigoMunicipio, PDO::PARAM_INT);
            $stmt->bindValue(':factor_id', $factorId, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$row) {
                $fotos = [];
                for ($i = 1; $i <= 4; $i++) {
                    $f = trim((string) ($row['foto_actualizada_' . $i] ?? ''));
                    if ($f !== '' && strcasecmp($f, 'null') !== 0) {
                        $fotos[] = $f;
                    }
                    unset($row['foto_actualizada_' . $i]);
                }
                $row['fotos'] = $fotos;
            }
            unset($row);

            return ['output' => ['valid' => true, 'response' => $rows]];
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

}
