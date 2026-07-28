<?php

class Factores
{

    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            if ($id > 0) {
                $query = "SELECT tbl_factores.*, tbl_area.nombre AS area, tbl_pilar.nombre AS pilar, tbl_ejes.nombre AS eje, tbl_factores_inestabilidad_gobernacion.nombre_categoria AS inestabilidad, tbl_factores_inestabilidad_gobernacion.icono AS inestabilidad_icono, tbl_secretarias.secretaria AS secretaria_nombre
                    FROM " . $db->getTable('tbl_factores') . "
                    INNER JOIN " . $db->getTable('tbl_area') . " ON tbl_factores.tec_area_id = tbl_area.id
                    INNER JOIN " . $db->getTable('tbl_pilar') . " ON tbl_factores.tec_pilar_id = tbl_pilar.id
                    INNER JOIN " . $db->getTable('tbl_ejes') . " ON tbl_factores.tbl_eje_id = tbl_ejes.id
                    LEFT JOIN " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " ON tbl_factores.tbl_factor_inestabilidad_id = tbl_factores_inestabilidad_gobernacion.id
                    LEFT JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_factores.tbl_secretaria_id = tbl_secretarias.id
                    WHERE tbl_factores.id = :id"; 

                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            } else {
                $query = "SELECT tbl_factores.*, tbl_area.nombre AS area, tbl_pilar.nombre AS pilar, tbl_ejes.nombre AS eje, tbl_factores_inestabilidad_gobernacion.nombre_categoria AS inestabilidad, tbl_factores_inestabilidad_gobernacion.icono AS inestabilidad_icono, tbl_secretarias.secretaria AS secretaria_nombre
                        FROM " . $db->getTable('tbl_factores') . "
                        INNER JOIN " . $db->getTable('tbl_area') . " ON tbl_factores.tec_area_id = tbl_area.id
                        INNER JOIN " . $db->getTable('tbl_pilar') . " ON tbl_factores.tec_pilar_id = tbl_pilar.id
                        INNER JOIN " . $db->getTable('tbl_ejes') . " ON tbl_factores.tbl_eje_id = tbl_ejes.id
                        LEFT JOIN " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " ON tbl_factores.tbl_factor_inestabilidad_id = tbl_factores_inestabilidad_gobernacion.id
                        LEFT JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_factores.tbl_secretaria_id = tbl_secretarias.id
                        ORDER BY tbl_factores.tipo ASC"; 

                $stmt = $pdo->prepare($query);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $response = array('output' => array('valid' => true, 'response' => $results));
            } else {
                $response = Util::error_no_result();
            }
        } catch (Exception $e) {
            $response = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $response;
    }

    public static function getInestabilidadOptions()
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $query = "SELECT id, nombre_categoria, icono FROM " . $db->getTable('tbl_factores_inestabilidad_gobernacion') . " ORDER BY id ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $response = array('output' => array('valid' => true, 'response' => $results));
            } else {
                $response = Util::error_no_result();
            }
        } catch (Exception $e) {
            $response = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
        return $response;
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $requiredFields = ['ejeId', 'pilarId', 'areaId', 'puntaje', 'tipo', 'tipo_medicion', 'icono'];

        if ($id != 0) {
            $requiredFields = array_diff($requiredFields, ['icono']);
        }
        foreach ($requiredFields as $field) {
            if (empty($rqst[$field]) && $rqst[$field] !== '0') {
                return Util::error_missing_data();
            }
        }
        $ejeId = intval($rqst['ejeId']);
        $pilarId = intval($rqst['pilarId']);
        $areaId = intval($rqst['areaId']);
        $puntaje = intval($rqst['puntaje']);
        $tipo = $rqst['tipo'];
        $tipo_medicion = $rqst['tipo_medicion'];
        $icono = $rqst['icono'];
        $tbl_factor_inestabilidad_id = isset($rqst['tbl_factor_inestabilidad_id']) && $rqst['tbl_factor_inestabilidad_id'] != '' && $rqst['tbl_factor_inestabilidad_id'] != 'seleccione' ? intval($rqst['tbl_factor_inestabilidad_id']) : null;
        $tbl_secretaria_id = isset($rqst['tbl_secretaria_id']) && $rqst['tbl_secretaria_id'] != '' && $rqst['tbl_secretaria_id'] != 'seleccione' ? intval($rqst['tbl_secretaria_id']) : null;
        $tec_usuario_id =  intval($_SESSION['session_user']['id']);

        $db = new DbConection();
        $pdo = $db->openConect();

        if ($id > 0) {
            //actualiza la informacion
            $q = "SELECT id FROM " . $db->getTable('tbl_factores') . " WHERE id = " . $id;
            $result = $pdo->query($q);
            if ($result) {
                $table = $db->getTable('tbl_factores');
                $arrfieldscomma = array(
                    'tbl_eje_id' => $ejeId,
                    'tec_area_id' => $areaId,
                    'tbl_factor_inestabilidad_id' => $tbl_factor_inestabilidad_id,
                    'tbl_secretaria_id' => $tbl_secretaria_id,
                    'puntaje' => $puntaje,
                    'tec_pilar_id' => $pilarId,
                    'tipo' => $tipo,
                    'icono' => $icono,
                    'tipo_medicion' => $tipo_medicion,
                    'tec_usuario_id' => $tec_usuario_id
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
            if ($ejeId > 0 && $pilarId  > 0 && $tipo != '' && $areaId > 0 && $tipo_medicion != '') {
                $q = "INSERT INTO " . $db->getTable('tbl_factores') . " (dtcreate, tipo, tbl_eje_id, tec_area_id, tec_pilar_id, tec_usuario_id, tipo_medicion, icono, puntaje, tbl_factor_inestabilidad_id, tbl_secretaria_id)
                VALUES ( " . Util::date_now_server() . ", :tipo, :tbl_eje_id, :tec_area_id, :tec_pilar_id, :tec_usuario_id, :tipo_medicion, :icono, :puntaje, :tbl_factor_inestabilidad_id, :tbl_secretaria_id)";
                $result = $pdo->prepare($q);
                $arrparam = array(
                    ':tipo' => $tipo,
                    ':tbl_eje_id' => $ejeId,
                    ':tec_area_id' => $areaId,
                    ':tec_pilar_id' => $pilarId,
                    ':tec_usuario_id' => $tec_usuario_id,
                    ':tipo_medicion' => $tipo_medicion,
                    ':icono' => $icono,
                    ':puntaje' => $puntaje,
                    ':tbl_factor_inestabilidad_id' => $tbl_factor_inestabilidad_id,
                    ':tbl_secretaria_id' => $tbl_secretaria_id
                );
                if ($result->execute($arrparam)) {
                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    $arrjson = Util::error_general(' Al guardar ingreso de factores');
                }
            } else {
                $arrjson = Util::error_missing_data();
            }
        }
        $db->closeConect();
        return $arrjson;
    }

    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "DELETE FROM " . $db->getTable('tbl_factores') . " WHERE id = " . $id;
        $result = $pdo->query($q);

        if ($result) {
            $arrjson = array('output' => array('valid' => true, 'error' => $pdo->errorInfo()));
        } else {
            $arrjson = Util::error_generaldelete();
        }
        $db->closeConect();
        return $arrjson;

    }

    public static function massUpdateInestabilidad($rqst)
    {
        $ids = isset($rqst['ids']) ? $rqst['ids'] : [];
        $tbl_factor_inestabilidad_id = isset($rqst['tbl_factor_inestabilidad_id']) && $rqst['tbl_factor_inestabilidad_id'] != '' && $rqst['tbl_factor_inestabilidad_id'] != 'seleccione' ? intval($rqst['tbl_factor_inestabilidad_id']) : null;

        if (empty($ids) || !is_array($ids)) {
            return Util::error_missing_data();
        }

        $total = count($ids);

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $placeholders = implode(',', array_fill(0, $total, '?'));
            $q = "UPDATE " . $db->getTable('tbl_factores') . " SET tbl_factor_inestabilidad_id = ? WHERE id IN ($placeholders)";
            $stmt = $pdo->prepare($q);
            $params = array_merge([$tbl_factor_inestabilidad_id], $ids);
            if ($stmt->execute($params)) {
                $arrjson = array('output' => array('valid' => true, 'updated' => $total));
            } else {
                $arrjson = Util::error_general(' Error en actualización masiva');
            }
        } catch (Exception $e) {
            $arrjson = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    public static function massUpdateSecretaria($rqst)
    {
        $ids = isset($rqst['ids']) ? $rqst['ids'] : [];
        $tbl_secretaria_id = isset($rqst['tbl_secretaria_id']) && $rqst['tbl_secretaria_id'] != '' && $rqst['tbl_secretaria_id'] != 'seleccione' ? intval($rqst['tbl_secretaria_id']) : null;

        if (empty($ids) || !is_array($ids)) {
            return Util::error_missing_data();
        }

        $total = count($ids);

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $placeholders = implode(',', array_fill(0, $total, '?'));
            $q = "UPDATE " . $db->getTable('tbl_factores') . " SET tbl_secretaria_id = ? WHERE id IN ($placeholders)";
            $stmt = $pdo->prepare($q);
            $params = array_merge([$tbl_secretaria_id], $ids);
            if ($stmt->execute($params)) {
                $arrjson = array('output' => array('valid' => true, 'updated' => $total));
            } else {
                $arrjson = Util::error_general(' Error en actualización masiva de secretaría');
            }
        } catch (Exception $e) {
            $arrjson = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }
}
