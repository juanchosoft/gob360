<?php

class PlanDesarrollo
{

    public function __construct() {}
    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $avance = isset($rqst['avance']) ? floatval($rqst['avance']) : 0;

        // Validaciones iniciales
        if ($id <= 0) {
            return Util::error_general('Item no encontrado');
        }

        if ($avance < 0 || $avance > 100) {
            return Util::error_general('El avance debe ser un número entre 0 y 100.');
        }

        try {
            $db = new DbConection();
            $pdo = $db->openConect();
            $pdo->beginTransaction(); // Iniciar la transacción

            // Verificar si el registro existe y obtener el valor actual de avance_2025
            $querySelect = "SELECT avance_2025 FROM " . $db->getTable('tbl_plandesarrollo') . " WHERE id = :id";


        
            $stmt = $pdo->prepare($querySelect);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $pdo->rollBack(); // Revertir transacción si no se encuentra el registro
                return Util::error_general('El registro no existe en la base de datos.');
            }

            $avanceActual = floatval($record['avance_2025']); // Valor actual en la base de datos

            // Validar que el avance combinado no supere 100
            if (($avanceActual + $avance) > 100) {
                $pdo->rollBack(); // Revertir transacción si no pasa la validación
                return Util::error_general("El avance combinado no puede exceder 100. Avance actual: $avanceActual.");
            }

            // Validar que el nuevo avance no reduzca el actual
            // if ($avance < $avanceActual) {
            //     $pdo->rollBack(); // Revertir transacción si no pasa la validación
            //     return Util::error_general("El nuevo avance ($avance) no puede ser menor al avance actual ($avanceActual).");
            // }

            // Actualizar los campos necesarios
            $table = $db->getTable('tbl_plandesarrollo');
            $arrfieldscomma = [
                'user_id' => $_SESSION['session_user']['id'],
                'avance_2025' => $avance
            ];
            $arrfieldsnocomma = array('dtupdate' => Util::date_now_server());
            $queryUpdate = Util::make_query_update($table, "id = :id", $arrfieldscomma, $arrfieldsnocomma);
            $stmtUpdate = $pdo->prepare($queryUpdate);
            $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmtUpdate->execute();

            if (!$result) {
                $pdo->rollBack(); 
                return Util::error_general('Error al actualizar el registro.');
            }
            $pdo->commit();

            // Respuesta exitosa con el valor actual de avance_2025
            return [
                'output' => [
                    'valid' => true,
                    'id' => $id,
                    'avance_actual' => $avanceActual,
                    'avance_nuevo' => $avance
                ]
            ];
        } catch (Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack(); 
            }
            return Util::error_general($e->getMessage());
        } finally {
            if (isset($db)) {
                $db->closeConect(); 
            }
        }
    }
}
