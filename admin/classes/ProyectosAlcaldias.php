<?php

/**
 * Clase para gestionar proyectos de alcaldías municipales
 *
 * Esta clase maneja todas las operaciones CRUD relacionadas con los proyectos
 * registrados por las alcaldías, incluyendo consultas, guardado, actualización
 * y generación de reportes.
 *
 * @author SPIDERSOFTWARE
 * @version 1.0
 */
class ProyectosAlcaldias
{
    /**
     * Obtiene todos los proyectos o filtra por criterios específicos
     *
     * @param array $rqst Array con parámetros de búsqueda:
     *                    - id: ID del proyecto específico
     *                    - tbl_municipio_id: Código del municipio
     *                    - tbl_secretarias_id: ID de la secretaría
     *                    - tbl_vereda_id: ID de la vereda
     *                    - estado: Estado del proyecto
     *
     * @return array Array con la respuesta en formato estándar
     */
    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0;
        $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : 0;
        $estado = isset($rqst['estado']) ? trim($rqst['estado']) : '';
        $provincia = isset($rqst['provincia']) ? trim($rqst['provincia']) : '';

        $db = new DbConection();
        $pdo = $db->openConect();
        $observaciones = [];
        $resultados = [];

        try {
            // Construcción de la consulta base con JOIN a tbl_ciudades_accion_unificada para obtener la provincia
            // IMPORTANTE: Se usa tbl_secretarias_municipios porque los proyectos de alcaldías están asociados a secretarías municipales
            $query = "SELECT
                        p.*,
                        c.municipio,
                        s.secretaria,
                        d.departamento,
                        v.nombre_vereda as vereda,
                        cau.subregion as provincia
                    FROM " . $db->getTable('tbl_proyectos_alcaldias') . " p
                    INNER JOIN " . $db->getTable('tbl_ciudades') . " c
                        ON p.tbl_municipio_id = c.codigo_muncipio
                    INNER JOIN " . $db->getTable('tbl_secretarias_municipios') . " s
                        ON p.tbl_secretarias_id = s.id
                    INNER JOIN " . $db->getTable('tbl_departamentos') . " d
                        ON p.tbl_departamento_id = d.codigo_departamento
                    LEFT JOIN " . $db->getTable('tbl_vereda') . " v
                        ON p.tbl_vereda_id = v.id
                    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " cau
                        ON p.tbl_municipio_id = cau.codigo_muncipio
                    WHERE 1=1";

            $params = [];

            // Aplicar filtros según los parámetros recibidos
            if ($id > 0) {
                $query .= " AND p.id = :id";
                $params[':id'] = $id;
            }

            if ($tbl_municipio_id > 0) {
                $query .= " AND p.tbl_municipio_id = :tbl_municipio_id";
                $params[':tbl_municipio_id'] = $tbl_municipio_id;
            }

            if ($tbl_secretarias_id > 0) {
                $query .= " AND p.tbl_secretarias_id = :tbl_secretarias_id";
                $params[':tbl_secretarias_id'] = $tbl_secretarias_id;
            }

            if ($tbl_vereda_id > 0) {
                $query .= " AND p.tbl_vereda_id = :tbl_vereda_id";
                $params[':tbl_vereda_id'] = $tbl_vereda_id;
            }

            if (!empty($estado)) {
                $query .= " AND p.estado = :estado";
                $params[':estado'] = $estado;
            }

            if (!empty($provincia)) {
                $query .= " AND cau.subregion = :provincia";
                $params[':provincia'] = $provincia;
            }

            // Ordenar por fecha de creación descendente
            $query .= " ORDER BY p.dtcreate DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si se consulta un proyecto específico, obtener sus observaciones
            if ($id > 0 && !empty($resultados)) {
                $observaciones = self::getObservacionesByProyectoId($id);
            }

            // Crear respuesta
            // Siempre retornar valid: true, incluso si no hay resultados (array vacío)
            return [
                'output' => [
                    'valid' => true,
                    'response' => $resultados,
                    'observaciones' => $observaciones
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Obtiene las observaciones de un proyecto específico
     *
     * @param int $id ID del proyecto
     * @return array Array con las observaciones del proyecto
     */
    public static function getObservacionesByProyectoId($id)
    {
        if ($id <= 0) {
            return [];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $query = "SELECT o.*, u.nombre as nombre_usuario
                     FROM " . $db->getTable('tbl_proyectos_alcaldias_observaciones') . " o
                     LEFT JOIN " . $db->getTable('tbl_usuarios') . " u
                        ON o.tbl_usuario_id = u.id
                     WHERE o.tbl_proyecto_alcaldia_id = :id
                     ORDER BY o.dtcreate DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute([':id' => $id]);
            $observaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $observaciones;
        } catch (Exception $e) {
            return [];
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Obtiene estadísticas de inversión total por municipio
     *
     * @param array $rqst Array con parámetros:
     *                    - mun: Código del municipio
     *
     * @return array Array con la suma de inversiones
     */
    public static function getInversionTotal($rqst)
    {
        $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

        if ($mun <= 0) {
            return Util::error_general('Debe especificar un municipio válido.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $query = "SELECT
                        p.tbl_municipio_id,
                        SUM(p.valor_proyecto) AS total_inversion,
                        SUM(p.aporte_municipio) AS total_aporte_municipio,
                        SUM(p.aporte_gobernacion) AS total_aporte_gobernacion,
                        SUM(p.aporte_nacion) AS total_aporte_nacion,
                        SUM(p.otros_aportes) AS total_otros_aportes,
                        COUNT(p.id) AS total_proyectos
                    FROM " . $db->getTable('tbl_proyectos_alcaldias') . " p
                    WHERE p.tbl_municipio_id = :municipio_id
                    GROUP BY p.tbl_municipio_id";

            $stmt = $pdo->prepare($query);
            $stmt->execute([':municipio_id' => $mun]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                return [
                    'output' => [
                        'valid' => true,
                        'response' => $resultado
                    ]
                ];
            } else {
                return Util::error_no_result();
            }
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Obtiene la inversión agrupada por secretaría para un municipio
     *
     * @param array $rqst Array con parámetros:
     *                    - mun: Código del municipio
     *
     * @return array Array con inversiones por secretaría
     */
    public static function getInversionBySecretaria($rqst)
    {
        $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

        if ($mun <= 0) {
            return Util::error_general('Debe especificar un municipio válido.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // IMPORTANTE: Se usa tbl_secretarias_municipios porque los proyectos de alcaldías están asociados a secretarías municipales
            $query = "SELECT
                        s.secretaria,
                        SUM(p.valor_proyecto) as total,
                        COUNT(p.id) as cantidad_proyectos
                    FROM " . $db->getTable('tbl_secretarias_municipios') . " s
                    LEFT JOIN " . $db->getTable('tbl_proyectos_alcaldias') . " p
                        ON p.tbl_secretarias_id = s.id
                    WHERE p.tbl_municipio_id = :municipio_id
                    GROUP BY s.id, s.secretaria
                    ORDER BY total DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute([':municipio_id' => $mun]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];

            foreach ($result as $valor) {
                $labels[] = $valor["secretaria"];
                $data[] = floatval($valor["total"]);
            }

            return [
                'output' => [
                    'valid' => true,
                    'response' => compact("labels", "data")
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Guarda o actualiza un proyecto de alcaldía
     *
     * @param array $rqst Array con todos los datos del proyecto
     *
     * @return array Array con la respuesta del guardado
     */
    public static function save($rqst)
    {
        // Obtener y validar datos del request
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
        $proyecto = isset($rqst['proyecto']) ? trim($rqst['proyecto']) : '';
        $date = isset($rqst['date']) ? trim($rqst['date']) : '';
        $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : 0;
        $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0;
        $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? $rqst['tbl_vereda_id'] : [];
        $valor_proyecto = isset($rqst['valor_proyecto']) ? floatval($rqst['valor_proyecto']) : 0;
        $nit = isset($rqst['nit']) && $rqst['nit'] != "" ? trim($rqst['nit']) : null;
        $contratista = isset($rqst['contratista']) && $rqst['contratista'] != "" ? trim($rqst['contratista']) : null;
        $interventoria = isset($rqst['interventoria']) && $rqst['interventoria'] != "" ? trim($rqst['interventoria']) : null;
        $adicion = isset($rqst['adicion']) && $rqst['adicion'] != "" ? floatval($rqst['adicion']) : null;
        $date_inicio = isset($rqst['date_inicio']) && $rqst['date_inicio'] != "" ? trim($rqst['date_inicio']) : null;
        $dias_prorroga = isset($rqst['dias_prorroga']) && $rqst['dias_prorroga'] != "" ? intval($rqst['dias_prorroga']) : null;
        $date_prorroga = isset($rqst['date_prorroga']) && $rqst['date_prorroga'] != "" ? trim($rqst['date_prorroga']) : null;
        $plazo_construccion = isset($rqst['plazo_construccion']) && $rqst['plazo_construccion'] != "" ? intval($rqst['plazo_construccion']) : null;
        $fecha_entrega = isset($rqst['fecha_entrega']) && $rqst['fecha_entrega'] != "" ? trim($rqst['fecha_entrega']) : null;
        $estado = isset($rqst['estado']) && $rqst['estado'] != "" ? trim($rqst['estado']) : 'En Formulación';
        $porcentaje_ejecucion = isset($rqst['porcentaje_ejecucion']) && $rqst['porcentaje_ejecucion'] != "" ? floatval($rqst['porcentaje_ejecucion']) : 0;
        $observaciones = isset($rqst['observaciones']) && $rqst['observaciones'] != "" ? trim($rqst['observaciones']) : null;
        $tbl_usuario_id = intval($_SESSION['session_user']['id']);
        $entidad_otros = isset($rqst['entidad_otros']) && $rqst['entidad_otros'] != "" ? trim($rqst['entidad_otros']) : null;
        $otros_aportes = isset($rqst['otros_aportes']) && $rqst['otros_aportes'] != "" ? floatval($rqst['otros_aportes']) : 0;
        $aporte_municipio = isset($rqst['aporte_municipio']) ? floatval($rqst['aporte_municipio']) : 0;
        $aporte_gobernacion = isset($rqst['aporte_gobernacion']) ? floatval($rqst['aporte_gobernacion']) : 0;
        $aporte_nacion = isset($rqst['aporte_nacion']) ? floatval($rqst['aporte_nacion']) : 0;
        $porcentaje_financiero = isset($rqst['porcentaje_financiero']) ? floatval($rqst['porcentaje_financiero']) : 0;
        $valor_ejecutado = isset($rqst['valor_ejecutado']) ? floatval($rqst['valor_ejecutado']) : 0;

        // Obtener tipo de usuario y código de municipio
        $tipoUsuario = SessionData::getUserType();
        $codigoMunicipio = SessionData::getCodigoMunicipio();

        // Validaciones de permisos
        // Si es alcalde o auxiliar de alcalde, solo puede registrar proyectos de su municipio
        if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario) {
            if (intval($tbl_municipio_id) !== intval($codigoMunicipio)) {
                return Util::error_general("Debe seleccionar el municipio correspondiente al cual pertenece.");
            }
        }

        // Convertir tbl_vereda_id a array si no lo es
        if (!is_array($tbl_vereda_id)) {
            $tbl_vereda_id = $tbl_vereda_id ? [$tbl_vereda_id] : [];
        }

        // Validación para secretarios de despacho
        if (Util::Secretario_Despacho() == $tipoUsuario) {
            if (SessionData::getSecretaria() !== $tbl_secretarias_id) {
                return Util::error_general('Debe ingresar solo información de la secretaría o despacho al que pertenece.');
            }
        }

        // Validaciones de datos obligatorios
        if ($id == 0 && $tbl_secretarias_id == 0) {
            return Util::error_general('Debe seleccionar la secretaría o despacho encargado.');
        }

        if (empty($proyecto)) {
            return Util::error_general('Debe ingresar la descripción del proyecto.');
        }

        if ($valor_proyecto <= 0) {
            return Util::error_general('Debe ingresar un valor de proyecto válido.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            if ($id > 0) {
                // Actualización de proyecto existente
                $query = "SELECT id FROM " . $db->getTable('tbl_proyectos_alcaldias') . " WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->execute([':id' => $id]);
                $result = $stmt->fetch();

                if ($result) {
                    $table = $db->getTable('tbl_proyectos_alcaldias');
                    $arrfieldscomma = [
                        'proyecto' => $proyecto,
                        'valor_proyecto' => $valor_proyecto,
                        'plazo_construccion' => $plazo_construccion,
                        'fecha_entrega' => $fecha_entrega,
                        'estado' => $estado,
                        'porcentaje_ejecucion' => $porcentaje_ejecucion,
                        'porcentaje_financiero' => $porcentaje_financiero,
                        'dias_prorroga' => $dias_prorroga,
                        'date_prorroga' => $date_prorroga,
                        'adicion' => $adicion,
                        'otros_aportes' => $otros_aportes,
                        'entidad_otros' => $entidad_otros,
                        'observaciones' => $observaciones,
                        'aporte_municipio' => $aporte_municipio,
                        'aporte_gobernacion' => $aporte_gobernacion,
                        'aporte_nacion' => $aporte_nacion,
                        'interventoria' => $interventoria,
                        'contratista' => $contratista,
                        'nit' => $nit,
                        'tbl_vereda_id' => $tbl_vereda_id,
                        'valor_ejecutado' => $valor_ejecutado
                    ];

                    $arrfieldsnocomma = ['dtupdate' => Util::date_now_server()];
                    $updateQuery = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);

                    $updateResult = $pdo->query($updateQuery);

                    if (!$updateResult) {
                        return Util::error_general('Error al actualizar los datos del proyecto.');
                    }

                    // Si hay observaciones nuevas, registrarlas
                    if (!empty($observaciones)) {
                        $insertObsQuery = "INSERT INTO " . $db->getTable('tbl_proyectos_alcaldias_observaciones') . "
                                          (dtcreate, tbl_proyecto_alcaldia_id, observaciones, tbl_usuario_id)
                                          VALUES (NOW(), :tbl_proyecto_id, :observaciones, :tbl_usuario_id)";

                        $stmtObs = $pdo->prepare($insertObsQuery);
                        $stmtObs->execute([
                            ':tbl_proyecto_id' => $id,
                            ':observaciones' => $observaciones,
                            ':tbl_usuario_id' => $tbl_usuario_id
                        ]);
                    }

                    return ['output' => ['valid' => true, 'id' => $id, 'message' => 'Proyecto actualizado correctamente']];
                } else {
                    return Util::error_general('El proyecto especificado no existe.');
                }
            } else {
                // Inserción de nuevo proyecto
                if (empty($tbl_municipio_id) || empty($date_inicio) || empty($fecha_entrega)) {
                    return Util::error_missing_data();
                }

                // Validar que hay al menos una vereda seleccionada
                if (empty($tbl_vereda_id) || !is_array($tbl_vereda_id)) {
                    return Util::error_general('Debe seleccionar al menos una vereda.');
                }

                $insertedIds = [];

                // Iterar sobre las veredas seleccionadas
                foreach ($tbl_vereda_id as $key => $vereda_id) {
                    // Validar que la vereda no esté vacía
                    if (empty($vereda_id)) {
                        continue; // Saltar veredas vacías
                    }

                    $insertQuery = "INSERT INTO " . $db->getTable('tbl_proyectos_alcaldias') . "
                        (dtcreate, tbl_secretarias_id, tbl_departamento_id, tbl_municipio_id, tbl_vereda_id,
                         proyecto, date, otros_aportes, entidad_otros, valor_proyecto, contratista, nit,
                         interventoria, adicion, date_inicio, dias_prorroga, plazo_construccion, fecha_entrega,
                         estado, date_prorroga, porcentaje_ejecucion, porcentaje_financiero, observaciones,
                         tbl_usuario_id, aporte_municipio, aporte_gobernacion, aporte_nacion, valor_ejecutado)
                        VALUES
                        (NOW(), :tbl_secretarias_id, :tbl_departamento_id, :tbl_municipio_id, :tbl_vereda_id,
                         :proyecto, :date, :otros_aportes, :entidad_otros, :valor_proyecto, :contratista, :nit,
                         :interventoria, :adicion, :date_inicio, :dias_prorroga, :plazo_construccion, :fecha_entrega,
                         :estado, :date_prorroga, :porcentaje_ejecucion, :porcentaje_financiero, :observaciones,
                         :tbl_usuario_id, :aporte_municipio, :aporte_gobernacion, :aporte_nacion, :valor_ejecutado)";

                    $stmt = $pdo->prepare($insertQuery);
                    $arrparam = [
                        ':tbl_secretarias_id' => $tbl_secretarias_id,
                        ':proyecto' => $proyecto,
                        ':date' => $date,
                        ':tbl_departamento_id' => $tbl_departamento_id,
                        ':tbl_municipio_id' => $tbl_municipio_id, // Municipio único
                        ':tbl_vereda_id' => $vereda_id, // Vereda iterada
                        ':valor_proyecto' => $valor_proyecto,
                        ':contratista' => $contratista,
                        ':nit' => $nit,
                        ':interventoria' => $interventoria,
                        ':adicion' => $adicion,
                        ':otros_aportes' => $otros_aportes,
                        ':entidad_otros' => $entidad_otros,
                        ':date_inicio' => $date_inicio,
                        ':dias_prorroga' => $dias_prorroga,
                        ':date_prorroga' => $date_prorroga,
                        ':plazo_construccion' => $plazo_construccion,
                        ':fecha_entrega' => $fecha_entrega,
                        ':estado' => $estado,
                        ':porcentaje_ejecucion' => $porcentaje_ejecucion,
                        ':porcentaje_financiero' => $porcentaje_financiero,
                        ':observaciones' => $observaciones,
                        ':tbl_usuario_id' => $tbl_usuario_id,
                        ':aporte_municipio' => $aporte_municipio,
                        ':aporte_gobernacion' => $aporte_gobernacion,
                        ':aporte_nacion' => $aporte_nacion,
                        ':valor_ejecutado' => $valor_ejecutado
                    ];

                    if ($stmt->execute($arrparam)) {
                        $insertedIds[] = $pdo->lastInsertId();
                        error_log("ProyectosAlcaldias::save - Insertado proyecto para vereda ID: " . $vereda_id);
                    } else {
                        error_log("ProyectosAlcaldias::save - Error al insertar proyecto para vereda ID: " . $vereda_id);
                        return Util::error_general('Error al guardar el proyecto para la vereda ID: ' . $vereda_id);
                    }
                }

                return [
                    'output' => [
                        'valid' => true,
                        'response' => $insertedIds,
                        'message' => 'Proyecto(s) guardado(s) correctamente'
                    ]
                ];
            }
        } catch (Exception $e) {
            return Util::error_general('Error en la operación: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Elimina un proyecto (soft delete o hard delete según requerimientos)
     *
     * @param array $rqst Array con el ID del proyecto a eliminar
     * @return array Array con la respuesta de la eliminación
     */
    public static function delete($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        if ($id <= 0) {
            return Util::error_general('Debe especificar un proyecto válido.');
        }

        // Validar permisos de usuario
        $tipoUsuario = SessionData::getUserType();
        if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Auxiliar() == $tipoUsuario) {
            return Util::error_general('No tiene permisos para eliminar proyectos.');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Verificar que el proyecto existe
            $checkQuery = "SELECT id, tbl_municipio_id FROM " . $db->getTable('tbl_proyectos_alcaldias') . " WHERE id = :id";
            $stmt = $pdo->prepare($checkQuery);
            $stmt->execute([':id' => $id]);
            $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$proyecto) {
                return Util::error_general('El proyecto especificado no existe.');
            }

            // Si es alcalde, verificar que sea de su municipio
            if (Util::Alcalde() == $tipoUsuario) {
                $codigoMunicipio = SessionData::getCodigoMunicipio();
                if (intval($proyecto['tbl_municipio_id']) !== intval($codigoMunicipio)) {
                    return Util::error_general('Solo puede eliminar proyectos de su municipio.');
                }
            }

            // Eliminar proyecto (hard delete - ajustar según requerimientos)
            $deleteQuery = "DELETE FROM " . $db->getTable('tbl_proyectos_alcaldias') . " WHERE id = :id";
            $stmtDelete = $pdo->prepare($deleteQuery);
            $result = $stmtDelete->execute([':id' => $id]);

            if ($result) {
                // También eliminar observaciones relacionadas
                $deleteObsQuery = "DELETE FROM " . $db->getTable('tbl_proyectos_alcaldias_observaciones') . "
                                  WHERE tbl_proyecto_alcaldia_id = :id";
                $stmtDeleteObs = $pdo->prepare($deleteObsQuery);
                $stmtDeleteObs->execute([':id' => $id]);

                return [
                    'output' => [
                        'valid' => true,
                        'message' => 'Proyecto eliminado correctamente'
                    ]
                ];
            } else {
                return Util::error_general('Error al eliminar el proyecto.');
            }
        } catch (Exception $e) {
            return Util::error_general('Error en la operación: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Actualiza un proyecto de alcaldía existente
     *
     * @param array $rqst Array con los datos del proyecto:
     *                    - id: ID del proyecto
     *                    - fecha_entrega: Nueva fecha de entrega
     *                    - estado: Nuevo estado del proyecto
     *                    - porcentaje_ejecucion: Nuevo porcentaje de ejecución
     *                    - porcentaje_financiero: Nuevo porcentaje financiero
     *                    - observaciones: Observaciones del cambio
     *
     * @return array Array con la respuesta en formato estándar
     */
    public static function updateProyecto($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $fecha_entrega = isset($rqst['fecha_entrega']) ? trim($rqst['fecha_entrega']) : '';
        $estado = isset($rqst['estado']) ? trim($rqst['estado']) : '';
        $porcentaje_ejecucion = isset($rqst['porcentaje_ejecucion']) ? floatval($rqst['porcentaje_ejecucion']) : 0;
        $porcentaje_financiero = isset($rqst['porcentaje_financiero']) ? floatval($rqst['porcentaje_financiero']) : 0;
        $observaciones = isset($rqst['observaciones']) ? trim($rqst['observaciones']) : '';

        if ($id <= 0) {
            return Util::error_general('ID de proyecto inválido');
        }

        if (empty($observaciones)) {
            return Util::error_general('Debe proporcionar observaciones');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            // Actualizar el proyecto
            $updateQuery = "UPDATE " . $db->getTable('tbl_proyectos_alcaldias') . "
                           SET fecha_entrega = :fecha_entrega,
                               estado = :estado,
                               porcentaje_ejecucion = :porcentaje_ejecucion,
                               porcentaje_financiero = :porcentaje_financiero
                           WHERE id = :id";

            $stmt = $pdo->prepare($updateQuery);
            $result = $stmt->execute([
                ':fecha_entrega' => $fecha_entrega,
                ':estado' => $estado,
                ':porcentaje_ejecucion' => $porcentaje_ejecucion,
                ':porcentaje_financiero' => $porcentaje_financiero,
                ':id' => $id
            ]);

            if (!$result) {
                return Util::error_general('Error al actualizar el proyecto');
            }

            // Guardar la observación en la tabla de observaciones
            $obsQuery = "INSERT INTO " . $db->getTable('tbl_proyectos_alcaldias_observaciones') . "
                        (tbl_proyecto_alcaldia_id, observaciones, dtcreate, tbl_usuario_id)
                        VALUES (:proyecto_id, :observaciones, NOW(), :usuario_id)";

            $usuario_id = SessionData::getUserId();
            $obsStmt = $pdo->prepare($obsQuery);
            $obsStmt->execute([
                ':proyecto_id' => $id,
                ':observaciones' => $observaciones,
                ':usuario_id' => $usuario_id
            ]);

            return [
                'output' => [
                    'valid' => true,
                    'response' => [
                        'message' => 'Proyecto actualizado correctamente'
                    ]
                ]
            ];
        } catch (Exception $e) {
            return Util::error_general('Error al actualizar el proyecto: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Obtiene los logs de un proyecto por su ID
     * Consulta la tabla tbl_logs_proyectos_planeacion_alcaldia
     *
     * @param int $proyectoId ID del proyecto
     * @return array Array con la respuesta en formato estándar
     */
    public static function getLogsByProyectoId($proyectoId)
    {
        $proyectoId = intval($proyectoId);

        if ($proyectoId <= 0) {
            return [
                'output' => [
                    'valid' => false,
                    'response' => ['content' => 'ID de proyecto inválido']
                ]
            ];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $dbName = $db->getDbName();

            $query = "SELECT
                        tlp.id,
                        tlp.proyecto_id,
                        tlp.dtcreated,
                        tlp.accion,
                        tlp.observacion,
                        tlp.documento_ruta,
                        tlp.porcentaje_ejecucion,
                        u.nombre AS usuario_nombre,
                        u.apellido AS usuario_apellido,
                        CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, '')) AS usuario
                    FROM " . $db->getTable('tbl_logs_proyectos_planeacion_alcaldia') . " tlp
                    LEFT JOIN {$dbName}.tbl_usuarios u ON tlp.usuario_id = u.id
                    WHERE tlp.proyecto_id = :id
                    ORDER BY tlp.dtcreated DESC";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $proyectoId, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'output' => [
                    'valid' => true,
                    'response' => $logs
                ]
            ];

        } catch (PDOException $e) {
            error_log("Error al obtener logs por proyecto ID: " . $e->getMessage());
            return [
                'output' => [
                    'valid' => false,
                    'response' => ['content' => 'Error al obtener los logs: ' . $e->getMessage()]
                ]
            ];
        } finally {
            $db->closeConect();
        }
    }
}
