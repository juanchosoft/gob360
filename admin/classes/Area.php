<?php
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Area
{

    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $pilarId = isset($rqst['pilarId']) ? intval($rqst['pilarId']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();
        $arrjson = [];

        try {
            // Construcción de la consulta dinámica
            $q = "SELECT * FROM " . $db->getTable('tbl_area');
            $params = [];

            if ($id > 0) {
                $q .= " WHERE id = :id";
                $params[':id'] = $id;
            } elseif ($pilarId > 0) {
                $q .= " WHERE tbl_pilar_id = :pilarId";
                $params[':pilarId'] = $pilarId;
            }
            $stmt = $pdo->prepare($q);
            $stmt->execute($params);

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                $arrjson = ['output' => ['valid' => true, 'response' => $result]];
            } else {
                $arrjson = Util::error_no_result();
            }
        } catch (Exception $e) {
            $arrjson = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }


    public static function save($rqst)
    {
        // Inicialización de parámetros
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $pilarId = isset($rqst['pilarId']) ? intval($rqst['pilarId']) : 0;
        $descripcion = $rqst['descripcion'] ?? '';
        $nombre = $rqst['nombre'] ?? '';
        $icono = $rqst['icono'] ?? '';
        $enable = $rqst['enable'] ?? '';
        $tec_usuario_id = intval($_SESSION['session_user']['id']);

        $db = new DbConection();
        $pdo = $db->openConect();
        $arrjson = [];

        try {
            // Inicia la transacción
            $pdo->beginTransaction();

            if ($id > 0) {
                // Verifica si el registro existe antes de actualizar
                $q = "SELECT id FROM " . $db->getTable('tbl_area') . " WHERE id = :id";
                $stmt = $pdo->prepare($q);
                $stmt->execute([':id' => $id]);
                if ($stmt->rowCount() > 0) {
                    // Actualización del registro
                    $table = $db->getTable('tbl_area');
                    $arrfieldscomma = [
                        'tbl_pilar_id' => $pilarId,
                        'icono' => $icono,
                        'nombre' => $nombre,
                        'tec_usuario_id' => $tec_usuario_id,
                        'enable' => $enable,
                        'descripcion' => $descripcion
                    ];
                    $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
                    $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                    $result = $pdo->query($q);
                    if (!$result) {
                        throw new Exception('Error al actualizar el área.');
                    } else {
                        $arrjson = ['output' => ['valid' => true, 'id' => $id]];
                    }
                } else {
                    throw new Exception('El área no existe.');
                }
            } else {
                // Inserción de un nuevo registro
                if ($pilarId > 0 && $nombre !== '') {
                    $q = "INSERT INTO " . $db->getTable('tbl_area') . " 
                        (dtcreate, descripcion, enable, tbl_pilar_id, tec_usuario_id, nombre, icono)
                        VALUES (:dtcreate, :descripcion, :enable, :tbl_pilar_id, :tec_usuario_id, :nombre, :icono)";
                    $stmt = $pdo->prepare($q);
                    $arrparam = [
                        ':dtcreate' => Util::date_now_server(),
                        ':descripcion' => $descripcion,
                        ':enable' => $enable,
                        ':tbl_pilar_id' => $pilarId,
                        ':tec_usuario_id' => $tec_usuario_id,
                        ':nombre' => $nombre,
                        ':icono' => $icono
                    ];
                    if ($stmt->execute($arrparam)) {
                        $arrjson = ['output' => ['valid' => true, 'response' => $pdo->lastInsertId()]];
                    } else {
                        throw new Exception('Error al guardar el área.');
                    }
                } else {
                    throw new Exception('Faltan datos obligatorios para guardar el área.');
                }
            }

            // Confirma la transacción
            $pdo->commit();
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $pdo->rollBack();
            $arrjson = Util::error_general($e->getMessage());
        } finally {
            // Cierra la conexión
            $db->closeConect();
        }

        return $arrjson;
    }
}
