<?php

class ActualizacionEstrategicos
{

    public function __construct() {}

    public static function save($rqst)
    {
        $id = $rqst['id'] ?? 0;
        $tbl_ingreso_informacion_id = $rqst['tbl_ingreso_informacion_id'] ?? 0;
        $actoresId = $rqst['actoresId'] ?? 0;
        $factorId = $rqst['factorId'] ?? 0;
        $valor_actualizacion = $rqst['valor_actualizacion'] ?? 0.0;
        $fotos = [
            $rqst['foto1'] ?? '',
            $rqst['foto2'] ?? '',
            $rqst['foto3'] ?? '',
            $rqst['foto4'] ?? ''
        ];
        $tec_usuario_id = $_SESSION['session_user']['id'] ?? 0;

        $codDepartamento_id = $rqst['codDepartamento_id'] ?? Util::getDepartamentoPrincipal();
        $codMunicipio_id = $rqst['codMunicipio_id'] ?? 0;
        $tbl_vereda_id = $rqst['vereda_id'] ?? 0;

        $db = new DbConection();
        $pdo = $db->openConect();
        $arrjson = [];

        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            if ($id > 0) {
                // Actualización de registro existente
                self::updateRegistroExistente($pdo, $db, $id, $factorId, $actoresId, $valor_actualizacion, $tbl_vereda_id, $tec_usuario_id, $fotos);
                $arrjson = ['output' => ['valid' => true, 'id' => $id]];
            } else {
                // Validar existencia de información previa
                $infoIngreso = self::verificarSiExisteIngresoInformacion($pdo, $db, $factorId, $codMunicipio_id, $codDepartamento_id, $tbl_vereda_id);
                if (!$infoIngreso) {
                    throw new Exception('No existe un registro de ese factor para el departamento, municipio y vereda seleccionados.');
                }

                // Insertar nuevo registro y actualizar valor
                $tbl_ingreso_informacion_id = $infoIngreso['id'];
                $valor = intval($infoIngreso['valor']);
                if ($valor < $valor_actualizacion) {
                    throw new Exception('El valor de actualización no puede ser mayor al valor actual ' . $valor);
                }

                self::insertarNuevoRegistroActualizacion($pdo, $db, $valor_actualizacion, $tbl_ingreso_informacion_id, $actoresId, $tec_usuario_id, $fotos);
                self::actualizarValorIngresoInformacion($pdo, $db, $tbl_ingreso_informacion_id, $valor_actualizacion);

                $arrjson = ['output' => ['valid' => true, 'response' => $pdo->lastInsertId()]];
            }

            // Confirmar transacción
            $pdo->commit();
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $pdo->rollBack();
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    private static function updateRegistroExistente($pdo, $db, $id, $factorId, $actoresId, $valor_actualizacion, $tbl_vereda_id, $tec_usuario_id, $fotos)
    {
        $table = $db->getTable('tbl_ingreso_informacion_x_actualizacion_estrategicos');
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
        $query = "SELECT id, valor FROM " . $db->getTable('tbl_ingreso_informacion_estrategicos') . " 
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

    private static function insertarNuevoRegistroActualizacion($pdo, $db, $valor_actualizacion, $tbl_ingreso_informacion_id, $actoresId, $tec_usuario_id, $fotos)
    {
        $query = "INSERT INTO " . $db->getTable('tbl_ingreso_informacion_x_actualizacion_estrategicos') . " 
                    (dtcreate, valor_actualizacion, tbl_ingreso_informacion_id, tbl_actor_id, tec_usuario_id, foto_actualizada_1, foto_actualizada_2, foto_actualizada_3, foto_actualizada_4)
                    VALUES 
                    (:dtcreate, :valor_actualizacion, :tbl_ingreso_informacion_id, :tbl_actor_id, :tec_usuario_id, :foto_actualizada_1, :foto_actualizada_2, :foto_actualizada_3, :foto_actualizada_4)";

        $stmt = $pdo->prepare($query);
        if (!$stmt->execute([
            ':dtcreate' => Util::date(),
            ':valor_actualizacion' => $valor_actualizacion,
            ':tbl_ingreso_informacion_id' => $tbl_ingreso_informacion_id,
            ':tbl_actor_id' => $actoresId,
            ':tec_usuario_id' => $tec_usuario_id,
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
        $query = "UPDATE " . $db->getTable('tbl_ingreso_informacion_estrategicos') . " 
                    SET valor = valor - :valor_actualizacion 
                    WHERE id = :id";

        $stmt = $pdo->prepare($query);
        if (!$stmt->execute([
            ':valor_actualizacion' => $valor_actualizacion,
            ':id' => $tbl_ingreso_informacion_id,
        ])) {
            throw new Exception('Error al actualizar el valor de ingreso.');
        }
    }
}
