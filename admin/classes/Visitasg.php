<?php

/**
 * Operaciones sobre tbl_gestora (Red de Valor Social 1 y 2).
 * tipo_actividad: primera_dama | aspas
 */
class Visitasg
{
    public const TIPO_PRIMERA_DAMA = 'primera_dama';
    public const TIPO_ASPAS = 'aspas';

    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $filtroMunicipio = isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0;
        $tipoActividad = self::normalizeTipo($rqst['tipo_actividad'] ?? '');
        $tipoUsuario = SessionData::getUserType();
        $codigoMunicipio = SessionData::getCodigoMunicipio();

        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT tbl_gestora.*, tbl_ciudades.municipio, tbl_acciong.accion,
                     tbl_linea.nombre AS linea_nombre, tbl_estrategia.nombre AS estrategia_nombre
              FROM " . $db->getTable('tbl_gestora') . "
              INNER JOIN " . $db->getTable('tbl_ciudades') . "
                ON tbl_gestora.tbl_municipio_id = tbl_ciudades.codigo_muncipio
              INNER JOIN " . $db->getTable('tbl_acciong') . "
                ON tbl_gestora.tbl_acciong_id = tbl_acciong.id
              LEFT JOIN " . $db->getTable('tbl_linea') . "
                ON tbl_gestora.tbl_linea_id = tbl_linea.id
              LEFT JOIN " . $db->getTable('tbl_estrategia') . "
                ON tbl_gestora.tbl_estrategia_id = tbl_estrategia.id";

        $params = [];

        if ($id > 0) {
            $q .= " WHERE tbl_gestora.id = :id";
            $params[':id'] = $id;
        }

        if ($filtroMunicipio > 0) {
            $q .= empty($params) ? " WHERE" : " AND";
            $q .= " tbl_gestora.tbl_municipio_id = :filtro_municipio";
            $params[':filtro_municipio'] = $filtroMunicipio;
        }

        if ($tipoActividad !== null) {
            $q .= empty($params) ? " WHERE" : " AND";
            $q .= " tbl_gestora.tipo_actividad = :tipo_actividad";
            $params[':tipo_actividad'] = $tipoActividad;
        }

        if ($filtroMunicipio === 0 && (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario)) {
            $q .= empty($params) ? " WHERE" : " AND";
            $q .= " tbl_ciudades.codigo_muncipio = :codigo_muncipio";
            $params[':codigo_muncipio'] = $codigoMunicipio;
        }

        $q .= " ORDER BY tbl_gestora.id DESC";

        $stmt = $pdo->prepare($q);
        $stmt->execute($params);
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();

        if ($arr) {
            return ['output' => ['valid' => true, 'response' => $arr]];
        }

        return Util::error_no_result();
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : 0;
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0;
        $date = $rqst['date'] ?? Util::date();
        $desc_actividad = $rqst['desc_actividad'] ?? '';
        $inversion = $rqst['inversion'] ?? '';
        $poblacion = $rqst['poblacion'] ?? '';
        $tbl_acciong_id = $rqst['tbl_acciong_id'] ?? 22;
        $provincia = $rqst['provincia'] ?? '';
        $tbl_usuario_id = intval($_SESSION['session_user']['id']);
        $foto1 = $rqst['foto1'] ?? null;
        $foto2 = $rqst['foto2'] ?? null;
        $foto3 = $rqst['foto3'] ?? null;
        $foto4 = $rqst['foto4'] ?? null;

        $linea = $rqst['linea'] ?? '';
        $estrategia = $rqst['estrategia'] ?? '';
        $campana = $rqst['campana'] ?? '';
        $actividad = $rqst['actividad'] ?? '';
        $link = $rqst['link'] ?? '';
        $tbl_linea_id = isset($rqst['tbl_linea']) ? intval($rqst['tbl_linea']) : null;
        $tbl_estrategia_id = isset($rqst['tbl_estrategia']) ? intval($rqst['tbl_estrategia']) : null;
        $tipo_actividad = self::normalizeTipo($rqst['tipo_actividad'] ?? '') ?? self::TIPO_PRIMERA_DAMA;

        $tipoUsuario = SessionData::getUserType();
        $codigoMunicipio = SessionData::getCodigoMunicipio();

        $mensaje = "Debe seleccionar el municipio correspondiente al cual pertenece.";
        if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario || Util::Secretario_Despacho() == $tipoUsuario) {
            if ($tbl_municipio_id !== $codigoMunicipio) {
                return Util::error_general($mensaje);
            }
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        $pdo->beginTransaction();

        try {
            if ($id > 0) {
                $q0 = "SELECT 1 FROM " . $db->getTable('tbl_gestora') . " WHERE id = :id";
                $stmt = $pdo->prepare($q0);
                $stmt->execute([':id' => $id]);

                if ($stmt->fetch()) {
                    $campos = [
                        'date = :date',
                        'desc_actividad = :desc_actividad',
                        'inversion = :inversion',
                        'poblacion = :poblacion',
                        'provincia = :provincia',
                        'tbl_acciong_id = :tbl_acciong_id',
                        'tbl_departamento_id = :tbl_departamento_id',
                        'tbl_municipio_id = :tbl_municipio_id',
                        'linea = :linea',
                        'estrategia = :estrategia',
                        'campana = :campana',
                        'actividad = :actividad',
                        'link = :link',
                        'tbl_linea_id = :tbl_linea_id',
                        'tbl_estrategia_id = :tbl_estrategia_id',
                        'tipo_actividad = :tipo_actividad',
                    ];

                    $params = [
                        ':date' => $date,
                        ':desc_actividad' => $desc_actividad,
                        ':inversion' => $inversion,
                        ':poblacion' => $poblacion,
                        ':provincia' => $provincia,
                        ':tbl_acciong_id' => $tbl_acciong_id,
                        ':tbl_departamento_id' => $tbl_departamento_id,
                        ':tbl_municipio_id' => $tbl_municipio_id,
                        ':linea' => $linea,
                        ':estrategia' => $estrategia,
                        ':campana' => $campana,
                        ':actividad' => $actividad,
                        ':link' => $link,
                        ':tbl_linea_id' => $tbl_linea_id,
                        ':tbl_estrategia_id' => $tbl_estrategia_id,
                        ':tipo_actividad' => $tipo_actividad,
                        ':id' => $id,
                    ];

                    if (!empty($foto1)) {
                        $campos[] = 'foto1 = :foto1';
                        $params[':foto1'] = $foto1;
                    }
                    if (!empty($foto2)) {
                        $campos[] = 'foto2 = :foto2';
                        $params[':foto2'] = $foto2;
                    }
                    if (!empty($foto3)) {
                        $campos[] = 'foto3 = :foto3';
                        $params[':foto3'] = $foto3;
                    }
                    if (!empty($foto4)) {
                        $campos[] = 'foto4 = :foto4';
                        $params[':foto4'] = $foto4;
                    }

                    $q = "UPDATE " . $db->getTable('tbl_gestora') . " SET " . implode(', ', $campos) . " WHERE id = :id";
                    $stmt = $pdo->prepare($q);
                    $stmt->execute($params);

                    $arrjson = ['output' => ['valid' => true, 'id' => $id]];
                } else {
                    throw new Exception('No se encontró el registro de la visita para actualizar.');
                }
            } else {
                if (!empty($date) && !empty($poblacion) && $tbl_departamento_id > 0 && !empty($desc_actividad) && $tbl_municipio_id > 0) {
                    $q = "INSERT INTO " . $db->getTable('tbl_gestora') . "
                          (dtcreate, date, poblacion, tbl_acciong_id, desc_actividad, provincia, inversion,
                          foto1, foto2, foto3, foto4, tbl_departamento_id, tbl_municipio_id, tbl_usuario_id,
                          linea, estrategia, campana, actividad, link, tbl_linea_id, tbl_estrategia_id, tipo_actividad)
                          VALUES (" . Util::date_now_server() . ", :date, :poblacion, :tbl_acciong_id, :desc_actividad, :provincia, :inversion,
                          :foto1, :foto2, :foto3, :foto4, :tbl_departamento_id, :tbl_municipio_id, :tbl_usuario_id,
                          :linea, :estrategia, :campana, :actividad, :link, :tbl_linea_id, :tbl_estrategia_id, :tipo_actividad)";

                    $params = [
                        ':date' => $date,
                        ':poblacion' => $poblacion,
                        ':tbl_acciong_id' => $tbl_acciong_id,
                        ':desc_actividad' => $desc_actividad,
                        ':provincia' => $provincia,
                        ':inversion' => $inversion,
                        ':tbl_departamento_id' => $tbl_departamento_id,
                        ':tbl_municipio_id' => $tbl_municipio_id,
                        ':tbl_usuario_id' => $tbl_usuario_id,
                        ':foto1' => $foto1,
                        ':foto2' => $foto2,
                        ':foto3' => $foto3,
                        ':foto4' => $foto4,
                        ':linea' => $linea,
                        ':estrategia' => $estrategia,
                        ':campana' => $campana,
                        ':actividad' => $actividad,
                        ':link' => $link,
                        ':tbl_linea_id' => $tbl_linea_id,
                        ':tbl_estrategia_id' => $tbl_estrategia_id,
                        ':tipo_actividad' => $tipo_actividad,
                    ];

                    $stmt = $pdo->prepare($q);
                    $stmt->execute($params);
                    $arrjson = ['output' => ['valid' => true, 'response' => $pdo->lastInsertId()]];
                } else {
                    throw new Exception('Faltan datos para insertar la visita.');
                }
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    /** @return string|null */
    private static function normalizeTipo($raw)
    {
        $raw = strtolower(trim((string) $raw));
        if ($raw === self::TIPO_PRIMERA_DAMA || $raw === self::TIPO_ASPAS) {
            return $raw;
        }
        return null;
    }
}
