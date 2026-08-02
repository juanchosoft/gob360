<?php

// require_once 'DbConection.php';
// require_once 'Util.php';
//require_once __DIR__ . '/MailSenderProyectosSecretarias.php'; 


class Proyectos_Secretarias {

    public static function saveProyectoSecretarias($request, $files) {
        $db = new DbConection();
        $pdo = $db->openConect();

        $usuario_creador_id = $_SESSION['session_user']['id'] ?? null; 


        $secretariasIds = isset($request['tbl_secretarias_id']) ? (array)$request['tbl_secretarias_id'] : [];
        $metasIds       = isset($request['tbl_meta_id'])       ? (array)$request['tbl_meta_id']       : [];

        // Filtrar valores vacíos y convertir a int
        $secretariasIds = array_values(array_filter(array_map('intval', $secretariasIds)));
        $metasIds       = array_values(array_filter(array_map('intval', $metasIds)));

        if (empty($request['date']) || empty($request['tbl_municipio_id']) || empty($request['proyecto']) || empty($secretariasIds) || empty($metasIds) || empty($request['valor_proyecto'])) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error: Faltan campos obligatorios.']]];
        }

        try {
            $pdo->beginTransaction();

            $upload_dir = __DIR__ . '/../../uploads/proyectos_secretarias/';

            $web_path = '/uploads/proyectos_secretarias/';


            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $foto_path = null;
            $documento_path = null;

            // Subir foto
            if (isset($files['foto2']) && $files['foto2']['error'] == 0) {
                $extension = pathinfo($files['foto2']['name'], PATHINFO_EXTENSION);
                $filename = 'foto_' . uniqid() . '.' . $extension;
                $destination = $upload_dir . $filename;
                
                if (move_uploaded_file($files['foto2']['tmp_name'], $destination)) {
                    $foto_path = $web_path . $filename;
                } else {
                    throw new Exception("No tienes permiso de escritura al directorio de subidas.");
                }
            } else {
                // Si el archivo es obligatorio pero no se subió, error
                if ($request['op'] == 'proyectos_secretaria_save' && empty($files['foto2'])) {
                    throw new Exception("Error: El archivo de foto es obligatorio.");
                }
            }

            // Subir documento principal
            if (isset($files['documento2']) && $files['documento2']['error'] == 0) {
                $extension = pathinfo($files['documento2']['name'], PATHINFO_EXTENSION);
                $filename = 'documento_' . uniqid() . '.' . $extension;
                $destination = $upload_dir . $filename;

                if (move_uploaded_file($files['documento2']['tmp_name'], $destination)) {
                    $documento_path = $web_path . $filename;
                } else {
                    throw new Exception("Error al mover el documento al directorio de subidas.");
                }
            } else {
                if ($request['op'] == 'proyectos_secretaria_save' && empty($files['documento2'])) {
                    throw new Exception("Error: El archivo de documento es obligatorio.");
                }
            }

            // Subir documentos adicionales (documento3 – documento6)
            $docs_extra = [];
            foreach ([3, 4, 5, 6] as $n) {
                $key = 'documento' . $n;
                $docs_extra[$key] = null;
                if (isset($files[$key]) && $files[$key]['error'] == 0) {
                    $ext = pathinfo($files[$key]['name'], PATHINFO_EXTENSION);
                    $fn  = 'documento_' . uniqid() . '.' . $ext;
                    $dst = $upload_dir . $fn;
                    if (move_uploaded_file($files[$key]['tmp_name'], $dst)) {
                        $docs_extra[$key] = $web_path . $fn;
                    } else {
                        throw new Exception("Error al mover {$key} al directorio de subidas.");
                    }
                }
            }

            // Primer valor para columnas legacy (compatibilidad con queries existentes)
            $primeraSecretaria = $secretariasIds[0];
            $primeraMeta       = $metasIds[0];

            $sql = "INSERT INTO " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " (
                `fecha`, `tbl_municipio_id`, `proyecto`, `tbl_secretarias_id`, `tbl_meta_id`, `usuario_creador_id`, `valor_proyecto`, `observaciones`, `foto2`, `documento2`, `documento3`, `documento4`, `documento5`, `documento6`
            ) VALUES (
                :fecha, :tbl_municipio_id, :proyecto, :tbl_secretarias_id, :tbl_meta_id, :usuario_creador_id, :valor_proyecto, :observaciones, :foto2, :documento2, :documento3, :documento4, :documento5, :documento6
            )";

            $stmt = $pdo->prepare($sql);

            $date_from_form = $request['date'];

            $stmt->bindParam(':fecha', $date_from_form);
            $stmt->bindParam(':tbl_municipio_id', $request['tbl_municipio_id']);
            $stmt->bindParam(':proyecto', $request['proyecto']);
            $stmt->bindParam(':tbl_secretarias_id', $primeraSecretaria, PDO::PARAM_INT);
            $stmt->bindParam(':tbl_meta_id', $primeraMeta, PDO::PARAM_INT);
            $stmt->bindParam(':usuario_creador_id', $usuario_creador_id);
            $stmt->bindParam(':valor_proyecto', $request['valor_proyecto']);
            $stmt->bindParam(':observaciones', $request['observaciones']);
            $stmt->bindParam(':foto2', $foto_path);
            $stmt->bindParam(':documento2', $documento_path);
            $stmt->bindParam(':documento3', $docs_extra['documento3']);
            $stmt->bindParam(':documento4', $docs_extra['documento4']);
            $stmt->bindParam(':documento5', $docs_extra['documento5']);
            $stmt->bindParam(':documento6', $docs_extra['documento6']);

            $stmt->execute();
            $proyectoId = (int)$pdo->lastInsertId();

            // Insertar en tablas pivote (ignora duplicados con INSERT IGNORE)
            $stmtSec = $pdo->prepare(
                "INSERT IGNORE INTO " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " (tbl_proyecto_id, tbl_secretarias_id) VALUES (:pid, :sid)"
            );
            foreach ($secretariasIds as $sid) {
                $stmtSec->execute([':pid' => $proyectoId, ':sid' => $sid]);
            }

            $stmtMeta = $pdo->prepare(
                "INSERT IGNORE INTO " . $db->getTable('tbl_proyectos_planeacion_metas') . " (tbl_proyecto_id, tbl_meta_id) VALUES (:pid, :mid)"
            );
            foreach ($metasIds as $mid) {
                $stmtMeta->execute([':pid' => $proyectoId, ':mid' => $mid]);
            }

            // Log de creación
            self::insertLog($pdo, $db, $proyectoId, (int)$usuario_creador_id, 'Creado', 'Proyecto creado y enviado a Planeación.', null);

            // Auto-asignar al creador
            if ($usuario_creador_id) {
                try {
                    $pdo->prepare(
                        "INSERT IGNORE INTO " . $db->getTable('tbl_proyectos_planeacion_asignaciones') . "
                         (proyecto_id, usuario_id, asignado_por_id, activo, observacion)
                         VALUES (:pid, :uid, :uid2, 1, 'Auto-asignado al crear')"
                    )->execute([
                        ':pid' => $proyectoId,
                        ':uid' => (int)$usuario_creador_id,
                        ':uid2' => (int)$usuario_creador_id,
                    ]);
                } catch (Throwable $e) {
                    error_log('auto-assign create: ' . $e->getMessage());
                }
            }

            $pdo->commit();

            // Notificar Planeación (secre 28) fuera de la transacción
            try {
                self::notifyPlaneacion28($proyectoId, 'nuevo');
            } catch (Throwable $mailEx) {
                error_log('notifyPlaneacion28 create: ' . $mailEx->getMessage());
            }

            return ['output' => ['valid' => true, 'response' => ['content' => 'Proyecto de secretaría guardado exitosamente.', 'id' => $proyectoId]]];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en saveProyectoSecretarias: " . $e->getMessage());
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error en la base de datos: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }


    public static function getAllProyectos($request = []) {
        $db = new DbConection();
        $pdo = $db->openConect();

        $dbName = $db->getDbName();

        $municipio_id_filter = $request['municipio_id_search'] ?? 0;
        $user_rol = $request['user_rol'] ?? '';

        $scope = self::resolveListScope($municipio_id_filter, $user_rol);
        if (($scope['mode'] ?? '') === 'empty') {
            return [];
        }
        $scopeSql = self::buildScopeWhere($scope, 'p');
        $where_clause = $scopeSql['sql'];
        $params = $scopeSql['params'];

        // Filtro opcional por usuarios (asignados o creadores)
        $usuarioIds = isset($request['usuario_ids']) ? (array)$request['usuario_ids'] : [];
        $usuarioIds = array_values(array_unique(array_filter(array_map('intval', $usuarioIds))));
        if (!empty($usuarioIds)) {
            $tAsig = $db->getTable('tbl_proyectos_planeacion_asignaciones');
            $inSql = implode(',', $usuarioIds); // ints ya saneados
            $userFilter = " (
                p.usuario_creador_id IN ($inSql)
                OR EXISTS (
                    SELECT 1 FROM {$tAsig} fa
                    WHERE fa.proyecto_id = p.id
                      AND fa.activo = 1
                      AND fa.usuario_id IN ($inSql)
                )
            )";
            if (trim($where_clause) === '') {
                $where_clause = ' WHERE ' . $userFilter . ' ';
            } else {
                $where_clause .= ' AND ' . $userFilter . ' ';
            }
        }

        try {
            $query = "SELECT
                        p.*,
                        c.municipio AS nombre_municipio,
                        COALESCE(
                            (
                                SELECT GROUP_CONCAT(sm2.secretaria ORDER BY sm2.secretaria SEPARATOR ', ')
                                FROM " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " ps2
                                JOIN " . $db->getTable('tbl_secretarias_municipios') . " sm2 ON ps2.tbl_secretarias_id = sm2.id
                                WHERE ps2.tbl_proyecto_id = p.id
                            ),
                            (SELECT sm.secretaria FROM " . $db->getTable('tbl_secretarias_municipios') . " sm WHERE sm.id = p.tbl_secretarias_id)
                        ) AS nombre_secretaria,
                        COALESCE(
                            (
                                SELECT GROUP_CONCAT(pd2.eje_estrategico ORDER BY pd2.eje_estrategico SEPARATOR ', ')
                                FROM " . $db->getTable('tbl_proyectos_planeacion_metas') . " pm2
                                JOIN " . $db->getTable('tbl_plandesarrollo_alcalde') . " pd2 ON pm2.tbl_meta_id = pd2.id
                                WHERE pm2.tbl_proyecto_id = p.id
                            ),
                            (SELECT pd.eje_estrategico FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . " pd WHERE pd.id = p.tbl_meta_id)
                        ) AS nombre_meta
                    FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " p
                    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c ON p.tbl_municipio_id = c.codigo_muncipio"
                    . $where_clause .
                    " ORDER BY p.dtcreatedatetime DESC";

            $stmt = $pdo->prepare($query);

            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }

            $stmt->execute();
            $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Enriquecer con asignados (nombres)
            if (!empty($proyectos)) {
                $ids = array_column($proyectos, 'id');
                $mapAsig = self::mapAsignadosPorProyectos($ids);
                foreach ($proyectos as &$proy) {
                    $proy['asignados'] = $mapAsig[(int)$proy['id']] ?? [];
                }
                unset($proy);
            }

            return $proyectos;
        } catch (PDOException $e) {
            error_log("Error al obtener los proyectos: " . $e->getMessage());
            return [];
        } finally {
            $db->closeConect();
        }
    }

    // Obtiene el nombre y id del 
    public static function getAprobadorByProyectoId($proyectoId) {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT u.nombre, u.apellido
            FROM " . $db->getTable('tbl_asignaciones_proyectos') . " ap
            JOIN " . $db->getTable('tbl_usuarios') . " u ON ap.aprobador_id = u.id
            WHERE ap.proyecto_id = :proyecto_id
            LIMIT 1"; 

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
        $stmt->execute();
        
        $aprobador = $stmt->fetch(PDO::FETCH_ASSOC);
        $db->closeConect();
        
        return $aprobador; 
    }


    public static function getProyectosBySecretaria($secretariaId) {
        
        $db = new DbConection();
        $pdo = $db->openConect();

        $dbName = $db->getDbName();

        try {
            $query = "SELECT
                        p.*,
                        c.municipio AS nombre_municipio,
                        (
                            SELECT GROUP_CONCAT(s2.secretaria ORDER BY s2.secretaria SEPARATOR ', ')
                            FROM " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " ps2
                            JOIN " . $db->getTable('tbl_secretarias') . " s2 ON ps2.tbl_secretarias_id = s2.id
                            WHERE ps2.tbl_proyecto_id = p.id
                        ) AS nombre_secretaria,
                        (
                            SELECT GROUP_CONCAT(pd2.eje_estrategico ORDER BY pd2.eje_estrategico SEPARATOR ', ')
                            FROM " . $db->getTable('tbl_proyectos_planeacion_metas') . " pm2
                            JOIN " . $db->getTable('tbl_plandesarrollo') . " pd2 ON pm2.tbl_meta_id = pd2.id
                            WHERE pm2.tbl_proyecto_id = p.id
                        ) AS nombre_meta
                    FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " p
                    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c ON p.tbl_municipio_id = c.codigo_muncipio
                    WHERE EXISTS (
                        SELECT 1 FROM " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " ps
                        WHERE ps.tbl_proyecto_id = p.id AND ps.tbl_secretarias_id = :secretaria_id
                    )
                    ORDER BY p.dtcreatedatetime DESC";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':secretaria_id', $secretariaId, PDO::PARAM_INT);
            $stmt->execute();
            
            $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $proyectos;

        } catch (PDOException $e) {
            error_log("Error al obtener proyectos por secretaria: " . $e->getMessage());
            return [];
        } finally {
            $db->closeConect();
        }
    }


    public static function aprobarProyecto($proyectoId, $bpin, $observaciones, $usuarioId) {
        return self::gestionarProyecto([
            'id' => $proyectoId,
            'decision' => 'Aprobar',
            'bpin' => $bpin,
            'nota' => $observaciones,
            'usuario_id' => $usuarioId,
        ], []);
    }

    public static function rechazarProyecto($proyectoId, $observaciones, $usuarioId) {
        return self::gestionarProyecto([
            'id' => $proyectoId,
            'decision' => 'Rechazar',
            'nota' => $observaciones,
            'usuario_id' => $usuarioId,
        ], []);
    }

    //para guardar las anotaciones del secretario de planeacion
    public static function guardarAnotacionSecretaria($id, $observaciones, $usuario_id) {
        $db = new DbConection();
        $pdo = $db->openConect();

        // actualiza la columna secretario_planeacion en la tabla de proyectos_secretaria
        $sql_update_proyecto = "UPDATE " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " SET secretario_planeacion = :observaciones WHERE id = :id";
        $stmt_update = $pdo->prepare($sql_update_proyecto);
        $stmt_update->bindParam(':observaciones', $observaciones);
        $stmt_update->bindParam(':id', $id, PDO::PARAM_INT);

        try {
            $pdo->beginTransaction();

            $stmt_update->execute();

            // iinserta la anotación en la tabla de logs para el historial
            $sql_insert_log = "INSERT INTO " . $db->getTable('tbl_observaciones_log') . " (proyecto_id, observacion, usuario_id) VALUES (:proyecto_id, :observacion, :usuario_id)";
            $stmt_insert = $pdo->prepare($sql_insert_log);
            $stmt_insert->bindParam(':proyecto_id', $id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':observacion', $observaciones);
            $stmt_insert->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            
            $stmt_insert->execute();

            $pdo->commit();

            return ['output' => ['valid' => true, 'response' => ['content' => 'Observación guardada y proyecto actualizado correctamente.']]];
        } catch (PDOException $e) {
            $pdo->rollBack();
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error al guardar la anotación: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }


    public static function getDetallesProyecto($proyectoId) {
        $db = new DbConection();
        $pdo = $db->openConect();

        if (!$pdo) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error de conexión a la base de datos.']]];
        }

        try {
            $sql = "SELECT
                        tps.*,
                        tm.municipio AS nombre_municipio,
                        (
                            SELECT GROUP_CONCAT(sm2.secretaria ORDER BY sm2.secretaria SEPARATOR ', ')
                            FROM " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " ps2
                            JOIN " . $db->getTable('tbl_secretarias_municipios') . " sm2 ON ps2.tbl_secretarias_id = sm2.id
                            WHERE ps2.tbl_proyecto_id = tps.id
                        ) AS nombre_secretaria,
                        (
                            SELECT GROUP_CONCAT(pd2.eje_estrategico ORDER BY pd2.eje_estrategico SEPARATOR ', ')
                            FROM " . $db->getTable('tbl_proyectos_planeacion_metas') . " pm2
                            JOIN " . $db->getTable('tbl_plandesarrollo_alcalde') . " pd2 ON pm2.tbl_meta_id = pd2.id
                            WHERE pm2.tbl_proyecto_id = tps.id
                        ) AS nombre_meta
                    FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " tps
                    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " tm ON tps.tbl_municipio_id = tm.codigo_muncipio
                    WHERE tps.id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $proyectoId, PDO::PARAM_INT);
            $stmt->execute();
            $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($proyecto) {

                $proyecto['municipio'] = $proyecto['nombre_municipio'] ?? '';
                $proyecto['secretaria'] = $proyecto['nombre_secretaria'] ?? '';
                $proyecto['meta_relacionada'] = $proyecto['nombre_meta'] ?? 'N/A';

                // IDs pivote para precargar el formulario create/edit
                $stmtSec = $pdo->prepare(
                    "SELECT tbl_secretarias_id FROM " . $db->getTable('tbl_proyectos_planeacion_secretarias') . "
                     WHERE tbl_proyecto_id = :id"
                );
                $stmtSec->execute([':id' => $proyectoId]);
                $secIds = array_map('intval', $stmtSec->fetchAll(PDO::FETCH_COLUMN) ?: []);
                if (empty($secIds) && !empty($proyecto['tbl_secretarias_id'])) {
                    $secIds = [(int)$proyecto['tbl_secretarias_id']];
                }
                $proyecto['secretarias_ids'] = $secIds;

                $stmtMeta = $pdo->prepare(
                    "SELECT tbl_meta_id FROM " . $db->getTable('tbl_proyectos_planeacion_metas') . "
                     WHERE tbl_proyecto_id = :id"
                );
                $stmtMeta->execute([':id' => $proyectoId]);
                $metaIds = array_map('intval', $stmtMeta->fetchAll(PDO::FETCH_COLUMN) ?: []);
                if (empty($metaIds) && !empty($proyecto['tbl_meta_id'])) {
                    $metaIds = [(int)$proyecto['tbl_meta_id']];
                }
                $proyecto['metas_ids'] = $metaIds;

                return ['output' => ['valid' => true, 'response' => $proyecto]];
            } else {
                return ['output' => ['valid' => false, 'response' => ['content' => 'Proyecto no encontrado.']]];
            }
        } catch (PDOException $e) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error en la consulta: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }


    public static function asignarProyectosACambios($proyectoIds, $aprobadorId, $asignadoPorId, $observaciones) {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $query = "INSERT INTO " . $db->getTable('tbl_asignaciones_proyectos') . " (proyecto_id, aprobador_id, asignado_por_id, observaciones, estado, dtcreated) VALUES (:proyecto_id, :aprobador_id, :asignado_por_id, :observaciones, 'Asignado', NOW())";
            
            $stmt = $pdo->prepare($query);
            
            $pdo->beginTransaction();
            foreach ($proyectoIds as $proyectoId) {
                $stmt->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
                $stmt->bindParam(':aprobador_id', $aprobadorId, PDO::PARAM_INT);
                $stmt->bindParam(':asignado_por_id', $asignadoPorId, PDO::PARAM_INT);
                $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR); 
                $stmt->execute();
            }
            $pdo->commit();
            
            return ['output' => ['valid' => true, 'response' => ['content' => 'Proyectos asignados correctamente.']]];
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error al asignar los proyectos: " . $e->getMessage());
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error al asignar los proyectos: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }


    // contar proeyctos en la seccion de aprobador
    public static function countAll() {
        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT COUNT(*) FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia');
        $count = $pdo->query($q)->fetchColumn();
        $db->closeConect();
        return $count;
    }
    public static function countSinAsignar() {
        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT COUNT(*) FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " WHERE estado_proyecto = 'Enviado'";
        $count = $pdo->query($q)->fetchColumn();
        $db->closeConect();
        return $count;
    }

    // conteo de Asignaciones
    public static function countAsignados() {
        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT COUNT(DISTINCT proyecto_id) FROM " . $db->getTable('tbl_asignaciones_proyectos');
        $count = $pdo->query($q)->fetchColumn();
        $db->closeConect();
        return $count;
    }

    // avisar por correo

    public static function getCreatorEmailAndDetails($proyectoId) {
        $db = new DbConection();
        $pdo = $db->openConect();   
        $q = "SELECT 
                p.proyecto,
                u.nickname AS creador_email
            FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " p
            JOIN " . $db->getTable('tbl_usuarios') . " u ON p.usuario_creador_id = u.id 
            WHERE p.id = :proyecto_id
            LIMIT 1";
        

        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
        $stmt->execute();
            
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $db->closeConect();    
        return $result;

    }


    public static function editarProyectoRechazado($data) {
        $db = new DbConection();
        $pdo = $db->openConect();

        $proyectoId = (int)($data['id'] ?? 0);
        $date_from_form = trim((string)($data['date'] ?? $data['edit_date'] ?? ''));
        $fecha = null;
        if ($date_from_form !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from_form)) {
                $fecha = $date_from_form;
            } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date_from_form, $m)) {
                $fecha = sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
            } else {
                $ts = strtotime($date_from_form);
                $fecha = $ts ? date('Y-m-d', $ts) : null;
            }
        }
        $observaciones = $data['observaciones'] ?? '';
        $valorProyecto = $data['valor_proyecto'] ?? null;
        $proyectoNombre = trim((string)($data['proyecto'] ?? ''));
        $municipioId = $data['tbl_municipio_id'] ?? null;

        $secretariasIds = isset($data['tbl_secretarias_id']) ? (array)$data['tbl_secretarias_id'] : [];
        $metasIds = isset($data['tbl_meta_id']) ? (array)$data['tbl_meta_id'] : [];
        $secretariasIds = array_values(array_filter(array_map('intval', $secretariasIds)));
        $metasIds = array_values(array_filter(array_map('intval', $metasIds)));

        if ($proyectoId <= 0) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'ID del proyecto no encontrado.']]];
        }

        $guard = self::assertPuedeVerProyecto($proyectoId);
        if (!$guard['ok']) {
            return ['output' => ['valid' => false, 'response' => ['content' => $guard['message']]]];
        }

        $row = self::fetchProyectoRow($proyectoId);
        if (!$row || ($row['estado_proyecto'] ?? '') !== 'Rechazado') {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Solo se pueden editar proyectos en estado Rechazado.']]];
        }

        if ($proyectoNombre === '' || empty($secretariasIds) || empty($metasIds) || $valorProyecto === null || $valorProyecto === '') {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Faltan campos obligatorios para reenviar el proyecto.']]];
        }

        $revisorAnterior = self::getRevisorUltimo($proyectoId);
        $upload_dir = __DIR__ . '/../../uploads/proyectos_secretarias/';
        $web_path = '/uploads/proyectos_secretarias/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $log_documento_ruta = null;
        $setClauses = "observaciones = :observaciones,
                       valor_proyecto = :valor_proyecto,
                       proyecto = :proyecto,
                       estado_proyecto = 'Enviado',
                       tbl_secretarias_id = :primera_sec,
                       tbl_meta_id = :primera_meta";
        $params = [
            ':observaciones' => $observaciones,
            ':valor_proyecto' => $valorProyecto,
            ':proyecto' => $proyectoNombre,
            ':primera_sec' => $secretariasIds[0],
            ':primera_meta' => $metasIds[0],
            ':id' => $proyectoId,
        ];

        if (!empty($fecha)) {
            $setClauses .= ", fecha = :fecha";
            $params[':fecha'] = $fecha;
        }
        if (!empty($municipioId)) {
            $setClauses .= ", tbl_municipio_id = :municipio";
            $params[':municipio'] = $municipioId;
        }

        $fileFields = ['foto2', 'documento2', 'documento3', 'documento4', 'documento5', 'documento6'];
        foreach ($fileFields as $field) {
            $file = $_FILES[$field] ?? null;
            if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fn = ($field === 'foto2' ? 'foto_' : 'documento_') . uniqid() . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $fn)) {
                return ['output' => ['valid' => false, 'response' => ['content' => "Error al subir {$field}."]]];
            }
            $ruta = ($field === 'foto2' ? $web_path : 'uploads/proyectos_secretarias/') . $fn;
            // Normalizar a ruta web relativa
            $ruta = 'uploads/proyectos_secretarias/' . $fn;
            $setClauses .= ", {$field} = :{$field}";
            $params[":{$field}"] = $ruta;
            if ($field === 'documento2') {
                $log_documento_ruta = $ruta;
            }
        }

        try {
            $pdo->beginTransaction();

            $sql = "UPDATE " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " SET " . $setClauses . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->execute();

            // Reemplazar pivotes
            $pdo->prepare(
                "DELETE FROM " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " WHERE tbl_proyecto_id = :pid"
            )->execute([':pid' => $proyectoId]);
            $pdo->prepare(
                "DELETE FROM " . $db->getTable('tbl_proyectos_planeacion_metas') . " WHERE tbl_proyecto_id = :pid"
            )->execute([':pid' => $proyectoId]);

            $stmtSec = $pdo->prepare(
                "INSERT IGNORE INTO " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " (tbl_proyecto_id, tbl_secretarias_id) VALUES (:pid, :sid)"
            );
            foreach ($secretariasIds as $sid) {
                $stmtSec->execute([':pid' => $proyectoId, ':sid' => $sid]);
            }
            $stmtMeta = $pdo->prepare(
                "INSERT IGNORE INTO " . $db->getTable('tbl_proyectos_planeacion_metas') . " (tbl_proyecto_id, tbl_meta_id) VALUES (:pid, :mid)"
            );
            foreach ($metasIds as $mid) {
                $stmtMeta->execute([':pid' => $proyectoId, ':mid' => $mid]);
            }

            $usuarioId = (int)($_SESSION['session_user']['id'] ?? 0);
            self::insertLog(
                $pdo,
                $db,
                $proyectoId,
                $usuarioId,
                'Reenviado/Editado',
                'Proyecto editado y reenviado a revisión. Observaciones: ' . $observaciones,
                $log_documento_ruta
            );

            $pdo->commit();

            try {
                self::notifyPlaneacion28($proyectoId, 'reenvio', $revisorAnterior);
            } catch (Throwable $mailEx) {
                error_log('notify reenvio: ' . $mailEx->getMessage());
            }

            return [
                'output' => [
                    'valid' => true,
                    'response' => ['content' => 'Proyecto editado y reenviado correctamente.']
                ]
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error al actualizar el proyecto: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }


    /**
     * acá obtiene todos los logs para un proyecto específico
     * @param int $proyectoId El ID del proyecto
     * @return array Los logs del proyecto
     */
    public static function obtenerLogsProyecto($proyectoId) {
        $db = new DbConection();
        $pdo = $db->openConect();

        if (!$pdo) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error de conexión a la base de datos.']]];
        }

        try {
            $dbName = $db->getDbName();
            
            //LEFT JOIN para obtener el nombre del usuario
            $logSql = "SELECT 
                        tlp.dtcreated, 
                        tlp.accion, 
                        tlp.observacion,
                        tlp.documento_ruta,
                        u.nickname AS usuario 
                    FROM " . $db->getTable('tbl_logs_proyectos_planeacion_alcaldia') . " tlp
                    LEFT JOIN {$dbName}.tbl_usuarios u ON tlp.usuario_id = u.id
                    WHERE tlp.proyecto_id = :id
                    ORDER BY tlp.dtcreated DESC";
            
            $stmt = $pdo->prepare($logSql);
            $stmt->bindParam(':id', $proyectoId, \PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Si no hay nada se trae vacio
            return ['output' => ['valid' => true, 'response' => $logs]];

        } catch (\PDOException $e) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error al obtener los logs: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Obtiene los logs de un proyecto por su ID (para descarga de logs)
     * @param int $proyectoId ID del proyecto
     * @return array Los logs del proyecto
     */
    public static function getLogsByProyectoId($proyectoId) {
        $db = new DbConection();
        $pdo = $db->openConect();

        if (!$pdo) {
            return [];
        }

        try {
            $dbName = $db->getDbName();

            $logSql = "SELECT
                        tlp.id,
                        tlp.proyecto_id,
                        tlp.dtcreated AS fecha,
                        tlp.accion,
                        tlp.observacion,
                        tlp.documento_ruta,
                        u.nombre AS usuario_nombre,
                        u.apellido AS usuario_apellido,
                        u.nickname AS usuario_email
                    FROM " . $db->getTable('tbl_logs_proyectos_planeacion_alcaldia') . " tlp
                    LEFT JOIN {$dbName}.tbl_usuarios u ON tlp.usuario_id = u.id
                    WHERE tlp.proyecto_id = :id
                    ORDER BY tlp.dtcreated DESC";

            $stmt = $pdo->prepare($logSql);
            $stmt->bindParam(':id', $proyectoId, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $logs;

        } catch (PDOException $e) {
            error_log("Error al obtener logs por proyecto ID: " . $e->getMessage());
            return [];
        } finally {
            $db->closeConect();
        }
    }


    public static function countByEstado($estado) {
        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT COUNT(*) FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " WHERE estado_proyecto = :estado";
        $stmt = $pdo->prepare($q);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $db->closeConect();
        return $count;
    }


    public static function countProyectosConMetaAsignada() {
        $db = new DbConection();
        $pdo = $db->openConect();

        $q = "SELECT COUNT(*) FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " WHERE tbl_meta_id IS NOT NULL AND tbl_meta_id > 0";
        $count = $pdo->query($q)->fetchColumn();
        $db->closeConect();
        return $count;
    }


    public static function countProyectosModificados() {
        $db = new DbConection();
        $pdo = $db->openConect();
        $q = "SELECT COUNT(DISTINCT proyecto_id) FROM " . $db->getTable('tbl_logs_proyectos_planeacion_alcaldia') . " WHERE accion = 'Reenviado/Editado'";
        $count = $pdo->query($q)->fetchColumn();
        $db->closeConect();
        return $count;
    }


    /**
     * obtiene el conteo de proyectos por estado para una secretaria específica.
     * @param int $secretariaId es ID de la secretaría
     * @return array Un arreglo con los conteos  ['Aprobado' => 10, 'Rechazado' => 5])
     */
    public static function getCountBySecretaria($secretariaId) {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {

            $q = "SELECT
                    estado_proyecto,
                    COUNT(*) as count
                FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " p
                WHERE EXISTS (
                    SELECT 1 FROM " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " ps
                    WHERE ps.tbl_proyecto_id = p.id AND ps.tbl_secretarias_id = :secretaria_id
                )
                GROUP BY estado_proyecto";


            $stmt = $pdo->prepare($q);
            $stmt->bindParam(':secretaria_id', $secretariaId, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Esto crea un array ['Estado' => 'Cantidad']

            // inicia el conteo
            $conteo = [
                'Enviado' => 0, // 'Enviado' es el estado inicial
                'Aprobado' => 0,
                'Rechazado' => 0,
                'Total' => 0
            ];
            
            foreach ($results as $estado => $count) {
                if (isset($conteo[$estado])) {
                    $conteo[$estado] = (int)$count;
                }
                $conteo['Total'] += (int)$count;
            }

            return ['output' => ['valid' => true, 'response' => $conteo]];

        }catch (PDOException $e) {
            error_log("Error en getCountBySecretaria: " . $e->getMessage());
            return [
                'output' => [
                    'valid' => false, 
                    'response' => ['content' => 'Error de BD al contar proyectos: ' . $e->getMessage()]
                ]
            ];
        } finally {
            $db->closeConect();
        }
    }


    //ceunta en tiempo desde que se repartió el proyecto
    public static function getReporteAsignacionesTiempo() {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $query = "
                SELECT 
                    ap.proyecto_id, 
                    ap.dtcreated, 
                    p.proyecto AS proyecto_nombre, 
                    p.estado_proyecto, 
                    u.nombre AS aprobador_nombre, 
                    u.apellido AS aprobador_apellido,
                    s.secretaria AS secretaria_nombre
                FROM " . $db->getTable('tbl_asignaciones_proyectos') . " ap
                JOIN " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " p ON ap.proyecto_id = p.id
                JOIN " . $db->getTable('tbl_usuarios') . " u ON ap.aprobador_id = u.id
                LEFT JOIN " . $db->getTable('tbl_secretarias_municipios') . " s ON p.tbl_secretarias_id = s.id
                ORDER BY ap.dtcreated DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            foreach ($results as &$row) {
                $row['aprobador_nombre'] = $row['aprobador_nombre'] . ' ' . $row['aprobador_apellido'];
                unset($row['aprobador_apellido']);
            }
            
            return ['output' => ['valid' => true, 'response' => $results]];

        } catch (PDOException $e) {
            error_log("Error al obtener reporte de asignaciones: " . $e->getMessage());
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error de BD: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }


    /**
     * Obtiene solo las secretarías que tienen proyectos registrados para un municipio específico.
     * @param int $municipioId ID del municipio del usuario en sesión.
     * @return array La lista de secretarías (id, secretaria).
     */
    /**
     * Obtiene los proyectos de un municipio específico (para usuarios tipo Alcalde).
     * Se unen con las secretarías municipales (tbl_secretarias_municipios) en lugar de las generales.
     * @param string $codigoMunicipio Código del municipio
     * @return array Lista de proyectos del municipio
     */
    public static function getProyectosByMunicipio($codigoMunicipio) {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $query = "SELECT
                        p.*,
                        c.municipio AS nombre_municipio,
                        COALESCE(
                            (
                                SELECT GROUP_CONCAT(sm2.secretaria ORDER BY sm2.secretaria SEPARATOR ', ')
                                FROM " . $db->getTable('tbl_proyectos_planeacion_secretarias') . " ps2
                                JOIN " . $db->getTable('tbl_secretarias_municipios') . " sm2 ON ps2.tbl_secretarias_id = sm2.id
                                WHERE ps2.tbl_proyecto_id = p.id
                            ),
                            (SELECT sm.secretaria FROM " . $db->getTable('tbl_secretarias_municipios') . " sm WHERE sm.id = p.tbl_secretarias_id)
                        ) AS nombre_secretaria,
                        COALESCE(
                            (
                                SELECT GROUP_CONCAT(pda2.eje_estrategico ORDER BY pda2.eje_estrategico SEPARATOR ', ')
                                FROM " . $db->getTable('tbl_proyectos_planeacion_metas') . " pm2
                                JOIN " . $db->getTable('tbl_plandesarrollo_alcalde') . " pda2 ON pm2.tbl_meta_id = pda2.id
                                WHERE pm2.tbl_proyecto_id = p.id
                            ),
                            (SELECT pd.eje_estrategico FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . " pd WHERE pd.id = p.tbl_meta_id)
                        ) AS nombre_meta
                    FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " p
                    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                        ON p.tbl_municipio_id = c.codigo_muncipio
                    WHERE p.tbl_municipio_id = :codigo_municipio
                    ORDER BY p.dtcreatedatetime DESC";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':codigo_municipio', $codigoMunicipio, PDO::PARAM_STR);
            $stmt->execute();

            $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $proyectos;

        } catch (PDOException $e) {
            error_log("Error al obtener proyectos por municipio: " . $e->getMessage());
            return [];
        } finally {
            $db->closeConect();
        }
    }


    public static function getSecretariasByMunicipio($municipioId) {
        $db = new DbConection();
        $pdo = $db->openConect();

        try {

            $query = "
                SELECT DISTINCT
                    ts.id AS tbl_secretarias_id, 
                    ts.secretaria
                FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " tps
                JOIN " . $db->getTable('tbl_secretarias') . " ts ON tps.tbl_secretarias_id = ts.id
                JOIN " . $db->getTable('tbl_usuarios') . " tu ON tps.usuario_creador_id = tu.id -- NUEVA TABLA
                WHERE tu.tbl_municipio_id = :municipio_id -- FILTRO POR EL MUNICIPIO DEL CREADOR
                ORDER BY ts.secretaria ASC";



            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':municipio_id', $municipioId, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


            return ['output' => ['valid' => true, 'response' => $results]];

        } catch (PDOException $e) {
            error_log("Error al obtener secretarias por municipio: " . $e->getMessage());
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error de BD: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Gestión / alcance / notificaciones / dashboard (plan 2026-07)
    // ─────────────────────────────────────────────────────────────────────

    public static function canViewAllDepartamento(): bool
    {
        require_once __DIR__ . '/Authorization.php';
        return Authorization::can('proyectos.alcaldias.planeacion.view_all');
    }

    /** @deprecated usar canViewAllDepartamento */
    public static function canViewAll(): bool
    {
        return self::canViewAllDepartamento();
    }

    public static function canViewAllAlcaldia(): bool
    {
        require_once __DIR__ . '/Authorization.php';
        return Authorization::can('proyectos.alcaldias.planeacion.view_all_alcaldia');
    }

    /**
     * True si el usuario tiene un código de municipio de alcaldía real.
     * 0 / "0" / vacío NO cuentan (común en SuperAdmin / gobernación).
     */
    public static function sessionHasMunicipio($munSesion = null): bool
    {
        require_once __DIR__ . '/SessionData.php';
        if ($munSesion === null) {
            $munSesion = SessionData::getCodigoMunicipio();
        }
        $s = trim((string) $munSesion);
        return $s !== '' && $s !== '0';
    }

    /**
     * Vista departamental (puede filtrar por cualquier municipio del depto).
     */
    public static function isVistaDepartamental(): bool
    {
        require_once __DIR__ . '/SessionData.php';
        require_once __DIR__ . '/Authorization.php';
        $tipo = $_SESSION['session_user']['tipo'] ?? '';
        return SessionData::superAdministrador()
            || self::canViewAllDepartamento()
            || in_array($tipo, ['Administrador', 'Gobernador'], true);
    }

    /**
     * Alcance de listado/dashboard/informes.
     * Regla dura: si el usuario tiene municipio en sesión, NUNCA ve fuera de esa alcaldía.
     *
     * @return array{
     *   mode: 'all'|'municipio'|'asignados'|'empty',
     *   municipio_id: string|int|null,
     *   usuario_id: int|null
     * }
     */
    public static function resolveListScope($municipioFilter = 0, string $userRol = ''): array
    {
        require_once __DIR__ . '/Authorization.php';
        require_once __DIR__ . '/SessionData.php';

        $usuarioId = (int) SessionData::getUserId();
        $munSesion = SessionData::getCodigoMunicipio();
        $tieneMunicipio = self::sessionHasMunicipio($munSesion);
        $tipo = $_SESSION['session_user']['tipo'] ?? $userRol;

        // SuperAdmin: vista departamental completa (salvo filtro explícito)
        if (SessionData::superAdministrador()) {
            if (self::sessionHasMunicipio($municipioFilter)) {
                return ['mode' => 'municipio', 'municipio_id' => $municipioFilter, 'usuario_id' => $usuarioId];
            }
            return ['mode' => 'all', 'municipio_id' => null, 'usuario_id' => $usuarioId];
        }

        // 1) Usuario de alcaldía: candado duro a su municipio
        if ($tieneMunicipio) {
            $mun = $munSesion;
            if (self::sessionHasMunicipio($municipioFilter) && (string)$municipioFilter !== (string)$mun) {
                // Intento de ver otra alcaldía → denegar
                return ['mode' => 'empty', 'municipio_id' => null, 'usuario_id' => $usuarioId];
            }
            // Ver todos de la alcaldía (o manage: necesitan listar para asignar/gestionar)
            if (
                self::canViewAllAlcaldia()
                || Authorization::can('proyectos.alcaldias.planeacion.manage')
                || Authorization::can('proyectos.alcaldias.planeacion.assign')
            ) {
                return ['mode' => 'municipio', 'municipio_id' => $mun, 'usuario_id' => $usuarioId];
            }
            // Solo asignados (+ creados por él, aplicado en SQL)
            return ['mode' => 'asignados', 'municipio_id' => $mun, 'usuario_id' => $usuarioId];
        }

        // 2) Sin municipio (gobernación / admin): vista departamental
        if (
            SessionData::superAdministrador()
            || self::canViewAllDepartamento()
            || in_array($tipo, ['Administrador', 'Gobernador'], true)
        ) {
            if (self::sessionHasMunicipio($municipioFilter)) {
                return ['mode' => 'municipio', 'municipio_id' => $municipioFilter, 'usuario_id' => $usuarioId];
            }
            return ['mode' => 'all', 'municipio_id' => null, 'usuario_id' => $usuarioId];
        }

        if ($userRol === 'Alcalde' && self::sessionHasMunicipio($municipioFilter)) {
            return ['mode' => 'municipio', 'municipio_id' => $municipioFilter, 'usuario_id' => $usuarioId];
        }

        return ['mode' => 'empty', 'municipio_id' => null, 'usuario_id' => $usuarioId];
    }

    /**
     * Fragmento SQL + params según alcance (alias tabla proyectos = p).
     * @return array{sql:string, params:array}
     */
    public static function buildScopeWhere(array $scope, string $alias = 'p'): array
    {
        $params = [];
        if (($scope['mode'] ?? '') === 'empty') {
            return ['sql' => ' WHERE 1=0 ', 'params' => []];
        }
        if (($scope['mode'] ?? '') === 'all') {
            return ['sql' => '', 'params' => []];
        }

        $sql = " WHERE {$alias}.tbl_municipio_id = :scope_municipio ";
        $params[':scope_municipio'] = $scope['municipio_id'];

        if (($scope['mode'] ?? '') === 'asignados') {
            $tAsig = (new DbConection())->getTable('tbl_proyectos_planeacion_asignaciones');
            $sql .= " AND (
                {$alias}.usuario_creador_id = :scope_usuario
                OR EXISTS (
                    SELECT 1 FROM {$tAsig} a
                    WHERE a.proyecto_id = {$alias}.id
                      AND a.usuario_id = :scope_usuario2
                      AND a.activo = 1
                )
            )";
            $params[':scope_usuario'] = (int)($scope['usuario_id'] ?? 0);
            $params[':scope_usuario2'] = (int)($scope['usuario_id'] ?? 0);
        }

        return ['sql' => $sql, 'params' => $params];
    }

    public static function fetchProyectoRow(int $proyectoId): ?array
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " WHERE id = :id LIMIT 1"
            );
            $stmt->execute([':id' => $proyectoId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } finally {
            $db->closeConect();
        }
    }

    public static function usuarioTieneAsignacion(int $proyectoId, int $usuarioId): bool
    {
        if ($proyectoId <= 0 || $usuarioId <= 0) {
            return false;
        }
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $stmt = $pdo->prepare(
                "SELECT 1 FROM " . $db->getTable('tbl_proyectos_planeacion_asignaciones') . "
                 WHERE proyecto_id = :pid AND usuario_id = :uid AND activo = 1 LIMIT 1"
            );
            $stmt->execute([':pid' => $proyectoId, ':uid' => $usuarioId]);
            return (bool) $stmt->fetchColumn();
        } finally {
            $db->closeConect();
        }
    }

    /** @return array{ok:bool, message:string} */
    public static function assertPuedeVerProyecto(int $proyectoId): array
    {
        require_once __DIR__ . '/Authorization.php';
        require_once __DIR__ . '/SessionData.php';

        $row = self::fetchProyectoRow($proyectoId);
        if (!$row) {
            return ['ok' => false, 'message' => 'Proyecto no encontrado.'];
        }

        $usuarioId = (int) SessionData::getUserId();
        $munSesion = (string) SessionData::getCodigoMunicipio();
        $tipo = $_SESSION['session_user']['tipo'] ?? '';

        if (SessionData::superAdministrador()) {
            return ['ok' => true, 'message' => ''];
        }

        // Candado alcaldía: nunca fuera de su municipio (0 no cuenta)
        if (self::sessionHasMunicipio($munSesion) && (string)($row['tbl_municipio_id'] ?? '') !== $munSesion) {
            return ['ok' => false, 'message' => 'No tiene alcance sobre este proyecto (municipio).'];
        }

        // Vista departamental (solo sin candado de municipio, o ya pasó el candado)
        if (!self::sessionHasMunicipio($munSesion) && (self::canViewAllDepartamento() || in_array($tipo, ['Administrador', 'Gobernador'], true))) {
            return ['ok' => true, 'message' => ''];
        }

        if (self::sessionHasMunicipio($munSesion)) {
            if (
                self::canViewAllAlcaldia()
                || Authorization::can('proyectos.alcaldias.planeacion.manage')
                || Authorization::can('proyectos.alcaldias.planeacion.assign')
            ) {
                return ['ok' => true, 'message' => ''];
            }
            if ((int)($row['usuario_creador_id'] ?? 0) === $usuarioId) {
                return ['ok' => true, 'message' => ''];
            }
            if (self::usuarioTieneAsignacion($proyectoId, $usuarioId)) {
                return ['ok' => true, 'message' => ''];
            }
            return ['ok' => false, 'message' => 'Proyecto no asignado a su usuario.'];
        }

        // Sin municipio y sin view_all
        if (self::canViewAllDepartamento() || in_array($tipo, ['Administrador', 'Gobernador'], true)) {
            return ['ok' => true, 'message' => ''];
        }

        return ['ok' => false, 'message' => 'Sin alcance para ver el proyecto.'];
    }

    /** @return array{ok:bool, message:string} */
    public static function assertPuedeGestionar(int $proyectoId): array
    {
        require_once __DIR__ . '/Authorization.php';
        if (!Authorization::can('proyectos.alcaldias.planeacion.manage')
            && !Authorization::can('secretarias.proyectos.approve')) {
            return ['ok' => false, 'message' => 'Sin permiso para gestionar proyectos.'];
        }
        return self::assertPuedeVerProyecto($proyectoId);
    }

    /** @return array{ok:bool, message:string} */
    public static function assertPuedeReabrir(int $proyectoId): array
    {
        require_once __DIR__ . '/Authorization.php';
        if (!Authorization::can('proyectos.alcaldias.planeacion.reopen')) {
            return ['ok' => false, 'message' => 'Sin permiso para reabrir proyectos.'];
        }
        return self::assertPuedeVerProyecto($proyectoId);
    }

    /** @return array{ok:bool, message:string} */
    public static function assertPuedeAsignar(int $proyectoId): array
    {
        require_once __DIR__ . '/Authorization.php';
        require_once __DIR__ . '/SessionData.php';
        if (!Authorization::can('proyectos.alcaldias.planeacion.assign')) {
            return ['ok' => false, 'message' => 'Sin permiso para asignar proyectos.'];
        }
        $mun = (string) SessionData::getCodigoMunicipio();
        if ($mun === '' && !SessionData::superAdministrador() && !self::canViewAllDepartamento()) {
            return ['ok' => false, 'message' => 'Solo usuarios de alcaldía (o con vista departamental) pueden asignar.'];
        }
        return self::assertPuedeVerProyecto($proyectoId);
    }

    public static function insertLog($pdo, DbConection $db, int $proyectoId, int $usuarioId, string $accion, string $obs, $docRuta = null): int
    {
        $sql = "INSERT INTO " . $db->getTable('tbl_logs_proyectos_planeacion_alcaldia') . "
            (proyecto_id, usuario_id, accion, observacion, documento_ruta, dtcreated)
            VALUES (:pid, :uid, :accion, :obs, :doc, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':pid' => $proyectoId,
            ':uid' => $usuarioId,
            ':accion' => $accion,
            ':obs' => $obs,
            ':doc' => $docRuta,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** Destinatarios Planeación: tbl_secretarias_id = 28 */
    public static function getDestinatariosPlaneacion28(): array
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        $tipos = [
            'Secretario_Despacho',
            'Secretaria_Despacho_Gobernacion',
            'Auxiliar',
            'Auxiliar_secret_gob',
        ];
        try {
            $in = implode(',', array_fill(0, count($tipos), '?'));
            $sql = "SELECT id, nickname, nombre, apellido, tipo
                    FROM " . $db->getTable('tbl_usuarios') . "
                    WHERE tbl_secretarias_id = 28
                      AND tipo IN ($in)
                      AND (habilitado = 1 OR habilitado = '1' OR habilitado IS NULL OR habilitado = '')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($tipos);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Filtrar emails válidos
            return array_values(array_filter($rows, static function ($r) {
                $email = trim((string)($r['nickname'] ?? ''));
                return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);
            }));
        } catch (Throwable $e) {
            error_log('getDestinatariosPlaneacion28: ' . $e->getMessage());
            return [];
        } finally {
            $db->closeConect();
        }
    }

    public static function getRevisorUltimo(int $proyectoId): ?array
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $sql = "SELECT l.usuario_id, u.nickname, u.nombre, u.apellido, l.accion, l.dtcreated
                    FROM " . $db->getTable('tbl_logs_proyectos_planeacion_alcaldia') . " l
                    LEFT JOIN " . $db->getTable('tbl_usuarios') . " u ON u.id = l.usuario_id
                    WHERE l.proyecto_id = :pid
                      AND l.accion IN ('Aprobado', 'Rechazado', 'Reabierto')
                    ORDER BY l.dtcreated DESC
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':pid' => $proyectoId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } finally {
            $db->closeConect();
        }
    }

    public static function buildDetalleUrl(int $proyectoId): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        // Desde admin/ajax → subir dos niveles
        if (strpos($base, '/admin/') !== false || substr($base, -6) === '/admin') {
            $base = preg_replace('#/admin(/ajax)?$#', '', $base);
        }
        return $scheme . '://' . $host . $base . '/reporte-proyecto-planeacion-alcaldia.php?id=' . $proyectoId;
    }

    public static function notifyPlaneacion28(int $proyectoId, string $tipo = 'nuevo', ?array $revisorExtra = null): void
    {
        require_once __DIR__ . '/EnvioCorreo.php';

        $det = self::getDetallesProyecto($proyectoId);
        $p = $det['output']['response'] ?? null;
        if (!$p || empty($det['output']['valid'])) {
            return;
        }

        $link = self::buildDetalleUrl($proyectoId);
        $titulo = $tipo === 'reenvio' ? 'Proyecto reenviado a revisión' : 'Nuevo proyecto de planeación alcaldía';
        $nombre = htmlspecialchars((string)($p['proyecto'] ?? ''), ENT_QUOTES, 'UTF-8');
        $muni = htmlspecialchars((string)($p['municipio'] ?? $p['nombre_municipio'] ?? ''), ENT_QUOTES, 'UTF-8');
        $valor = number_format((float)($p['valor_proyecto'] ?? 0), 0, ',', '.');

        $mensaje = "<p><strong>{$titulo}</strong></p>"
            . "<p>Proyecto: <b>{$nombre}</b><br>Municipio: <b>{$muni}</b><br>Valor: \$ {$valor}</p>"
            . "<p><a href=\"{$link}\">Ver detalle / gestionar</a></p>";

        $destinos = self::getDestinatariosPlaneacion28();
        $emails = [];
        foreach ($destinos as $d) {
            $emails[strtolower($d['nickname'])] = $d['nickname'];
        }
        if ($revisorExtra && !empty($revisorExtra['nickname']) && filter_var($revisorExtra['nickname'], FILTER_VALIDATE_EMAIL)) {
            $emails[strtolower($revisorExtra['nickname'])] = $revisorExtra['nickname'];
        }

        foreach ($emails as $email) {
            EnvioCorreo::enviarCorreo([
                'email' => $email,
                'subject' => $titulo . ' #' . $proyectoId,
                'mensaje' => $mensaje,
            ]);
        }
    }

    public static function notifyCreadorRechazo(int $proyectoId, string $nota): void
    {
        require_once __DIR__ . '/EnvioCorreo.php';
        $details = self::getCreatorEmailAndDetails($proyectoId);
        if (!$details || empty($details['creador_email']) || !filter_var($details['creador_email'], FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $link = self::buildDetalleUrl($proyectoId);
        $nombre = htmlspecialchars((string)($details['proyecto'] ?? ''), ENT_QUOTES, 'UTF-8');
        $notaH = nl2br(htmlspecialchars($nota, ENT_QUOTES, 'UTF-8'));
        $mensaje = "<p>Su proyecto <b>{$nombre}</b> fue <b>rechazado</b>.</p>"
            . "<p>Motivo:</p><p>{$notaH}</p>"
            . "<p><a href=\"{$link}\">Ver detalle y corregir</a></p>";
        EnvioCorreo::enviarCorreo([
            'email' => $details['creador_email'],
            'subject' => 'Proyecto rechazado #' . $proyectoId,
            'mensaje' => $mensaje,
        ]);
    }

    /**
     * Gestionar aprobar/rechazar con nota, BPIN, adjuntos y logs.
     * @param array $rqst
     * @param array $files
     */
    public static function gestionarProyecto(array $rqst, array $files = [])
    {
        $proyectoId = (int)($rqst['id'] ?? 0);
        $decision = trim((string)($rqst['decision'] ?? ''));
        $nota = trim((string)($rqst['nota'] ?? $rqst['observaciones'] ?? ''));
        $bpin = trim((string)($rqst['bpin'] ?? ''));
        $usuarioId = (int)($rqst['usuario_id'] ?? ($_SESSION['session_user']['id'] ?? 0));

        if ($proyectoId <= 0) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'ID inválido.']]];
        }

        $guard = self::assertPuedeGestionar($proyectoId);
        if (!$guard['ok']) {
            return ['output' => ['valid' => false, 'response' => ['content' => $guard['message']]]];
        }

        if (!in_array($decision, ['Aprobar', 'Rechazar'], true)) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Decisión inválida.']]];
        }
        if ($nota === '') {
            return ['output' => ['valid' => false, 'response' => ['content' => 'La nota de gestión es obligatoria.']]];
        }
        if ($decision === 'Aprobar' && $bpin === '') {
            return ['output' => ['valid' => false, 'response' => ['content' => 'El código BPIN es obligatorio al aprobar.']]];
        }

        $row = self::fetchProyectoRow($proyectoId);
        if (!$row || ($row['estado_proyecto'] ?? '') !== 'Enviado') {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Solo se pueden gestionar proyectos en estado Enviado.']]];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            $nuevoEstado = $decision === 'Aprobar' ? 'Aprobado' : 'Rechazado';
            $sql = "UPDATE " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . " SET
                        estado_proyecto = :estado,
                        secretario_planeacion = :nota,
                        gestion_nota = :nota2,
                        gestion_usuario_id = :uid,
                        gestion_at = NOW()"
                . ($decision === 'Aprobar' ? ", bpin = :bpin" : "")
                . " WHERE id = :id";

            $params = [
                ':estado' => $nuevoEstado,
                ':nota' => $nota,
                ':nota2' => $nota,
                ':uid' => $usuarioId,
                ':id' => $proyectoId,
            ];
            if ($decision === 'Aprobar') {
                $params[':bpin'] = $bpin;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $docRuta = self::storeGestionAdjuntos($pdo, $db, $proyectoId, $usuarioId, $files, 0);
            $logId = self::insertLog(
                $pdo,
                $db,
                $proyectoId,
                $usuarioId,
                $nuevoEstado,
                $nota . ($decision === 'Aprobar' ? ' | BPIN: ' . $bpin : ''),
                $docRuta
            );

            // Vincular adjuntos al log si se subieron después
            if ($logId > 0 && !empty($files)) {
                $pdo->prepare(
                    "UPDATE " . $db->getTable('tbl_proyectos_planeacion_gestion_adjuntos') . "
                     SET log_id = :lid WHERE proyecto_id = :pid AND log_id IS NULL AND usuario_id = :uid"
                )->execute([':lid' => $logId, ':pid' => $proyectoId, ':uid' => $usuarioId]);
            }

            $pdo->commit();

            if ($decision === 'Rechazar') {
                try {
                    self::notifyCreadorRechazo($proyectoId, $nota);
                } catch (Throwable $e) {
                    error_log('notify rechazo: ' . $e->getMessage());
                }
            }

            return [
                'output' => [
                    'valid' => true,
                    'response' => [
                        'content' => $decision === 'Aprobar'
                            ? 'Proyecto aprobado y cerrado.'
                            : 'Proyecto rechazado.',
                        'estado' => $nuevoEstado,
                    ],
                ],
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error al gestionar: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

    /** @return string|null primera ruta para el log */
    private static function storeGestionAdjuntos($pdo, DbConection $db, int $proyectoId, int $usuarioId, array $files, int $logId): ?string
    {
        $upload_dir = __DIR__ . '/../../uploads/proyectos_planeacion_gestion/';
        $web_path = 'uploads/proyectos_planeacion_gestion/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $first = null;
        $list = [];

        // Soporta gestion_adjunto[] o adjunto único
        if (!empty($files['gestion_adjuntos']['name']) && is_array($files['gestion_adjuntos']['name'])) {
            $n = count($files['gestion_adjuntos']['name']);
            for ($i = 0; $i < $n; $i++) {
                if (($files['gestion_adjuntos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }
                $list[] = [
                    'name' => $files['gestion_adjuntos']['name'][$i],
                    'tmp' => $files['gestion_adjuntos']['tmp_name'][$i],
                ];
            }
        } elseif (!empty($files['gestion_adjunto']) && ($files['gestion_adjunto']['error'] ?? 1) === UPLOAD_ERR_OK) {
            $list[] = [
                'name' => $files['gestion_adjunto']['name'],
                'tmp' => $files['gestion_adjunto']['tmp_name'],
            ];
        }

        $ins = $pdo->prepare(
            "INSERT INTO " . $db->getTable('tbl_proyectos_planeacion_gestion_adjuntos') . "
             (proyecto_id, log_id, usuario_id, ruta, nombre_original)
             VALUES (:pid, :lid, :uid, :ruta, :nom)"
        );

        foreach ($list as $f) {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                continue;
            }
            $fn = 'gest_' . uniqid() . '.' . $ext;
            if (!move_uploaded_file($f['tmp'], $upload_dir . $fn)) {
                continue;
            }
            $ruta = $web_path . $fn;
            $ins->execute([
                ':pid' => $proyectoId,
                ':lid' => $logId > 0 ? $logId : null,
                ':uid' => $usuarioId,
                ':ruta' => $ruta,
                ':nom' => $f['name'],
            ]);
            if ($first === null) {
                $first = $ruta;
            }
        }

        return $first;
    }

    public static function reabrirProyecto(array $rqst)
    {
        $proyectoId = (int)($rqst['id'] ?? 0);
        $nota = trim((string)($rqst['nota'] ?? 'Proyecto reabierto para nueva gestión.'));
        $usuarioId = (int)($_SESSION['session_user']['id'] ?? 0);

        if ($proyectoId <= 0) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'ID inválido.']]];
        }

        $guard = self::assertPuedeReabrir($proyectoId);
        if (!$guard['ok']) {
            return ['output' => ['valid' => false, 'response' => ['content' => $guard['message']]]];
        }

        $row = self::fetchProyectoRow($proyectoId);
        if (!$row || ($row['estado_proyecto'] ?? '') !== 'Aprobado') {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Solo se pueden reabrir proyectos Aprobados.']]];
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "UPDATE " . $db->getTable('tbl_proyectos_planeacion_alcaldia') . "
                 SET estado_proyecto = 'Enviado',
                     gestion_nota = :nota,
                     gestion_usuario_id = :uid,
                     gestion_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([':nota' => $nota, ':uid' => $usuarioId, ':id' => $proyectoId]);
            self::insertLog($pdo, $db, $proyectoId, $usuarioId, 'Reabierto', $nota, null);
            $pdo->commit();
            return ['output' => ['valid' => true, 'response' => ['content' => 'Proyecto reabierto (estado Enviado).']]];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error al reabrir: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

    public static function getGestionAdjuntos(int $proyectoId): array
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM " . $db->getTable('tbl_proyectos_planeacion_gestion_adjuntos') . "
                 WHERE proyecto_id = :pid ORDER BY dtcreated DESC"
            );
            $stmt->execute([':pid' => $proyectoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        } finally {
            $db->closeConect();
        }
    }

    /** KPIs dashboard respetando alcance view_all / municipio / asignados */
    public static function getDashboardStats(array $rqst = []): array
    {
        $scope = self::resolveListScope($rqst['municipio_id'] ?? 0);
        if (($scope['mode'] ?? '') === 'empty') {
            return ['output' => ['valid' => true, 'response' => self::emptyDashboard()]];
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        $t = $db->getTable('tbl_proyectos_planeacion_alcaldia');
        $tLog = $db->getTable('tbl_logs_proyectos_planeacion_alcaldia');
        $tMun = $db->getTable('tbl_ciudades_accion_unificada');
        $scopeSql = self::buildScopeWhere($scope, 'p');
        $where = $scopeSql['sql'];
        $params = $scopeSql['params'];

        try {
            $sqlEstados = "SELECT p.estado_proyecto, COUNT(*) AS total, COALESCE(SUM(p.valor_proyecto),0) AS valor
                           FROM {$t} p {$where}
                           GROUP BY p.estado_proyecto";
            $stmt = $pdo->prepare($sqlEstados);
            $stmt->execute($params);
            $byEstado = [];
            $total = 0;
            $valorTotal = 0.0;
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $byEstado[$r['estado_proyecto']] = [
                    'total' => (int)$r['total'],
                    'valor' => (float)$r['valor'],
                ];
                $total += (int)$r['total'];
                $valorTotal += (float)$r['valor'];
            }

            $dias = (int)($rqst['dias_sin_gestion'] ?? 7);
            $wSin = $where
                ? $where . " AND p.estado_proyecto = 'Enviado' AND p.dtcreatedatetime < DATE_SUB(NOW(), INTERVAL :dias DAY)"
                : " WHERE p.estado_proyecto = 'Enviado' AND p.dtcreatedatetime < DATE_SUB(NOW(), INTERVAL :dias DAY)";
            $stmtSin = $pdo->prepare("SELECT COUNT(*) FROM {$t} p {$wSin}");
            foreach ($params as $k => $v) {
                $stmtSin->bindValue($k, $v);
            }
            $stmtSin->bindValue(':dias', $dias, PDO::PARAM_INT);
            $stmtSin->execute();
            $sinGestion = (int) $stmtSin->fetchColumn();

            $wRet = $where
                ? $where . " AND p.estado_proyecto = 'Enviado'"
                : " WHERE p.estado_proyecto = 'Enviado'";
            $stmtRet = $pdo->prepare(
                "SELECT p.id, p.proyecto, p.tbl_municipio_id, c.municipio, p.dtcreatedatetime, p.valor_proyecto,
                        DATEDIFF(NOW(), p.dtcreatedatetime) AS dias
                 FROM {$t} p
                 LEFT JOIN {$tMun} c ON p.tbl_municipio_id = c.codigo_muncipio
                 {$wRet}
                 ORDER BY p.dtcreatedatetime ASC
                 LIMIT 15"
            );
            $stmtRet->execute($params);
            $retrasos = $stmtRet->fetchAll(PDO::FETCH_ASSOC);

            $stmtMun = $pdo->prepare(
                "SELECT c.municipio, COUNT(*) AS total,
                        SUM(p.estado_proyecto = 'Enviado') AS enviados,
                        SUM(p.estado_proyecto = 'Aprobado') AS aprobados,
                        SUM(p.estado_proyecto = 'Rechazado') AS rechazados
                 FROM {$t} p
                 LEFT JOIN {$tMun} c ON p.tbl_municipio_id = c.codigo_muncipio
                 {$where}
                 GROUP BY p.tbl_municipio_id, c.municipio
                 ORDER BY total DESC
                 LIMIT 20"
            );
            $stmtMun->execute($params);
            $porMunicipio = $stmtMun->fetchAll(PDO::FETCH_ASSOC);

            $joinRe = "INNER JOIN {$t} p ON p.id = l.proyecto_id";
            $wReenv = $where
                ? str_replace(' WHERE ', ' WHERE l.accion = \'Reenviado/Editado\' AND ', $where)
                : " WHERE l.accion = 'Reenviado/Editado'";
            // rebuild cleanly
            if ($where === '') {
                $wReenv = " WHERE l.accion = 'Reenviado/Editado'";
            } else {
                $wReenv = $where . " AND l.accion = 'Reenviado/Editado'";
            }
            $stmtRe = $pdo->prepare(
                "SELECT COUNT(DISTINCT l.proyecto_id) FROM {$tLog} l
                 {$joinRe}
                 {$wReenv}"
            );
            $stmtRe->execute($params);
            $reenvios = (int) $stmtRe->fetchColumn();

            return [
                'output' => [
                    'valid' => true,
                    'response' => [
                        'total' => $total,
                        'valor_total' => $valorTotal,
                        'por_estado' => $byEstado,
                        'sin_gestion' => $sinGestion,
                        'dias_umbral' => $dias,
                        'retrasos' => $retrasos,
                        'por_municipio' => $porMunicipio,
                        'reenvios' => $reenvios,
                        'scope' => $scope['mode'],
                    ],
                ],
            ];
        } catch (Throwable $e) {
            return ['output' => ['valid' => false, 'response' => ['content' => $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

    private static function emptyDashboard(): array
    {
        return [
            'total' => 0,
            'valor_total' => 0,
            'por_estado' => [],
            'sin_gestion' => 0,
            'dias_umbral' => 7,
            'retrasos' => [],
            'por_municipio' => [],
            'reenvios' => 0,
            'scope' => 'empty',
        ];
    }

    /** @param int[] $proyectoIds */
    public static function mapAsignadosPorProyectos(array $proyectoIds): array
    {
        $proyectoIds = array_values(array_filter(array_map('intval', $proyectoIds)));
        if (empty($proyectoIds)) {
            return [];
        }
        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $in = implode(',', array_fill(0, count($proyectoIds), '?'));
            $sql = "SELECT a.proyecto_id, a.usuario_id, u.nombre, u.apellido, u.nickname
                    FROM " . $db->getTable('tbl_proyectos_planeacion_asignaciones') . " a
                    INNER JOIN " . $db->getTable('tbl_usuarios') . " u ON u.id = a.usuario_id
                    WHERE a.activo = 1 AND a.proyecto_id IN ($in)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($proyectoIds);
            $map = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $pid = (int)$r['proyecto_id'];
                $map[$pid][] = [
                    'usuario_id' => (int)$r['usuario_id'],
                    'nombre' => trim(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? '')),
                    'nickname' => $r['nickname'] ?? '',
                ];
            }
            return $map;
        } catch (Throwable $e) {
            return [];
        } finally {
            $db->closeConect();
        }
    }

    /**
     * Fragmento SQL: usuario habilitado.
     * En esta BD el campo suele ser 'si'/'no' (no solo 1/0).
     */
    private static function sqlUsuarioHabilitado(string $alias = 'u'): string
    {
        return "(
            {$alias}.habilitado = 1
            OR {$alias}.habilitado = '1'
            OR LOWER(TRIM({$alias}.habilitado)) IN ('si', 's', 'yes', 'true')
        )";
    }

    /** Usuarios de la alcaldía con permiso manage (candidatos a asignar) */
    public static function getUsuariosAsignables(array $rqst = []): array
    {
        require_once __DIR__ . '/Authorization.php';
        require_once __DIR__ . '/SessionData.php';

        if (!Authorization::can('proyectos.alcaldias.planeacion.assign')) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Sin permiso para asignar.']]];
        }

        $mun = SessionData::getCodigoMunicipio();
        $proyectoId = (int)($rqst['proyecto_id'] ?? 0);
        if ($proyectoId > 0) {
            $guard = self::assertPuedeAsignar($proyectoId);
            if (!$guard['ok']) {
                return ['output' => ['valid' => false, 'response' => ['content' => $guard['message']]]];
            }
            $row = self::fetchProyectoRow($proyectoId);
            $mun = $row['tbl_municipio_id'] ?? $mun;
        }

        if ($mun === null || $mun === '' || $mun === false) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Debe pertenecer a una alcaldía para listar asignables.']]];
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $habSql = self::sqlUsuarioHabilitado('u');
            $sql = "SELECT DISTINCT u.id, u.nombre, u.apellido, u.nickname, u.tipo
                    FROM " . $db->getTable('tbl_usuarios') . " u
                    INNER JOIN " . $db->getTable('tbl_role_has_permissions') . " rhp ON rhp.role_id = u.role_id
                    INNER JOIN " . $db->getTable('tbl_permissions') . " p ON p.id = rhp.permission_id
                    WHERE u.tbl_municipio_id = :mun
                      AND p.permission_key = 'proyectos.alcaldias.planeacion.manage'
                      AND p.is_active = 1
                      AND {$habSql}
                    ORDER BY u.nombre, u.apellido";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':mun' => $mun]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $asignados = [];
            if ($proyectoId > 0) {
                $st2 = $pdo->prepare(
                    "SELECT usuario_id FROM " . $db->getTable('tbl_proyectos_planeacion_asignaciones') . "
                     WHERE proyecto_id = :pid AND activo = 1"
                );
                $st2->execute([':pid' => $proyectoId]);
                $asignados = array_map('intval', $st2->fetchAll(PDO::FETCH_COLUMN) ?: []);
            }

            foreach ($rows as &$r) {
                $r['asignado'] = in_array((int)$r['id'], $asignados, true);
            }
            unset($r);

            return ['output' => ['valid' => true, 'response' => $rows]];
        } catch (Throwable $e) {
            return ['output' => ['valid' => false, 'response' => ['content' => $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

    public static function getAsignacionesProyecto(int $proyectoId): array
    {
        $guard = self::assertPuedeVerProyecto($proyectoId);
        if (!$guard['ok']) {
            return ['output' => ['valid' => false, 'response' => ['content' => $guard['message']]]];
        }
        $map = self::mapAsignadosPorProyectos([$proyectoId]);
        return ['output' => ['valid' => true, 'response' => $map[$proyectoId] ?? []]];
    }

    /** Asigna o desasigna usuarios (lista completa de IDs activos) */
    public static function asignarUsuariosProyecto(array $rqst): array
    {
        require_once __DIR__ . '/SessionData.php';

        $proyectoId = (int)($rqst['proyecto_id'] ?? $rqst['id'] ?? 0);
        $usuarioIds = isset($rqst['usuario_ids']) ? (array)$rqst['usuario_ids'] : [];
        if (isset($rqst['usuario_id'])) {
            $usuarioIds[] = $rqst['usuario_id'];
        }
        $usuarioIds = array_values(array_unique(array_filter(array_map('intval', $usuarioIds))));
        $accion = ($rqst['accion'] ?? 'set') === 'add' ? 'add' : (($rqst['accion'] ?? '') === 'remove' ? 'remove' : 'set');

        $guard = self::assertPuedeAsignar($proyectoId);
        if (!$guard['ok']) {
            return ['output' => ['valid' => false, 'response' => ['content' => $guard['message']]]];
        }

        $row = self::fetchProyectoRow($proyectoId);
        $munProy = (string)($row['tbl_municipio_id'] ?? '');
        $asignadoPor = (int) SessionData::getUserId();

        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            // Validar que candidatos tengan manage y mismo municipio
            $validIds = [];
            if (!empty($usuarioIds)) {
                $in = implode(',', array_fill(0, count($usuarioIds), '?'));
                $habSql = self::sqlUsuarioHabilitado('u');
                $sqlVal = "SELECT DISTINCT u.id
                           FROM " . $db->getTable('tbl_usuarios') . " u
                           INNER JOIN " . $db->getTable('tbl_role_has_permissions') . " rhp ON rhp.role_id = u.role_id
                           INNER JOIN " . $db->getTable('tbl_permissions') . " p ON p.id = rhp.permission_id
                           WHERE u.id IN ($in)
                             AND u.tbl_municipio_id = ?
                             AND p.permission_key = 'proyectos.alcaldias.planeacion.manage'
                             AND {$habSql}";
                $params = $usuarioIds;
                $params[] = $munProy;
                $st = $pdo->prepare($sqlVal);
                $st->execute($params);
                $validIds = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN) ?: []);
            }

            $pdo->beginTransaction();
            $t = $db->getTable('tbl_proyectos_planeacion_asignaciones');

            if ($accion === 'set') {
                $pdo->prepare("UPDATE {$t} SET activo = 0 WHERE proyecto_id = :pid")->execute([':pid' => $proyectoId]);
                $ins = $pdo->prepare(
                    "INSERT INTO {$t} (proyecto_id, usuario_id, asignado_por_id, activo, observacion)
                     VALUES (:pid, :uid, :by, 1, :obs)
                     ON DUPLICATE KEY UPDATE activo = 1, asignado_por_id = VALUES(asignado_por_id), observacion = VALUES(observacion)"
                );
                foreach ($validIds as $uid) {
                    $ins->execute([
                        ':pid' => $proyectoId,
                        ':uid' => $uid,
                        ':by' => $asignadoPor,
                        ':obs' => 'Asignación manual',
                    ]);
                }
            } elseif ($accion === 'add') {
                $ins = $pdo->prepare(
                    "INSERT INTO {$t} (proyecto_id, usuario_id, asignado_por_id, activo, observacion)
                     VALUES (:pid, :uid, :by, 1, :obs)
                     ON DUPLICATE KEY UPDATE activo = 1, asignado_por_id = VALUES(asignado_por_id)"
                );
                foreach ($validIds as $uid) {
                    $ins->execute([':pid' => $proyectoId, ':uid' => $uid, ':by' => $asignadoPor, ':obs' => 'Asignación manual']);
                }
            } else { // remove
                if (!empty($usuarioIds)) {
                    $in = implode(',', array_fill(0, count($usuarioIds), '?'));
                    $params = $usuarioIds;
                    array_unshift($params, $proyectoId);
                    $pdo->prepare("UPDATE {$t} SET activo = 0 WHERE proyecto_id = ? AND usuario_id IN ($in)")->execute($params);
                }
            }

            self::insertLog(
                $pdo,
                $db,
                $proyectoId,
                $asignadoPor,
                'Asignacion',
                'Usuarios asignados: ' . (empty($validIds) && $accion !== 'remove' ? '(ninguno)' : implode(',', $accion === 'remove' ? $usuarioIds : $validIds)),
                null
            );
            $pdo->commit();

            return ['output' => ['valid' => true, 'response' => ['content' => 'Asignación actualizada.', 'usuario_ids' => $validIds]]];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['output' => ['valid' => false, 'response' => ['content' => 'Error al asignar: ' . $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

    /** Panel informes de gestión */
    public static function getInformesGestion(array $rqst = []): array
    {
        require_once __DIR__ . '/Authorization.php';
        require_once __DIR__ . '/SessionData.php';
        if (!Authorization::can('proyectos.alcaldias.planeacion.informes')) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Sin permiso de informes.']]];
        }

        $munSesion = SessionData::getCodigoMunicipio();
        $tieneMunicipio = self::sessionHasMunicipio($munSesion);
        $esVistaDepto = self::isVistaDepartamental();
        $munFiltro = $rqst['municipio_id'] ?? 0;
        if (!$esVistaDepto) {
            $munFiltro = $tieneMunicipio ? $munSesion : 0;
        }
        $munFiltro = self::sessionHasMunicipio($munFiltro) ? $munFiltro : 0;

        $scope = self::resolveListScope($munFiltro);
        if (($scope['mode'] ?? '') === 'empty') {
            return ['output' => ['valid' => true, 'response' => ['scope' => 'empty', 'kpis' => [], 'por_usuario' => [], 'acciones' => [], 'tendencia' => []]]];
        }

        $desde = trim((string)($rqst['fecha_desde'] ?? ''));
        $hasta = trim((string)($rqst['fecha_hasta'] ?? ''));
        if ($desde === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $desde = date('Y-m-d', strtotime('-30 days'));
        }
        if ($hasta === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $hasta = date('Y-m-d');
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        $t = $db->getTable('tbl_proyectos_planeacion_alcaldia');
        $tLog = $db->getTable('tbl_logs_proyectos_planeacion_alcaldia');
        $tAsig = $db->getTable('tbl_proyectos_planeacion_asignaciones');
        $tUsr = $db->getTable('tbl_usuarios');

        $scopeSql = self::buildScopeWhere($scope, 'p');
        $where = $scopeSql['sql'];
        $params = $scopeSql['params'];

        // Filtro opcional por usuarios (creador o asignado del proyecto)
        $usuarioIds = isset($rqst['usuario_ids']) ? (array)$rqst['usuario_ids'] : [];
        $usuarioIds = array_values(array_unique(array_filter(array_map('intval', $usuarioIds))));
        if (!empty($usuarioIds)) {
            $inSql = implode(',', $usuarioIds);
            $userFilter = " (
                p.usuario_creador_id IN ($inSql)
                OR EXISTS (
                    SELECT 1 FROM {$tAsig} fa
                    WHERE fa.proyecto_id = p.id
                      AND fa.activo = 1
                      AND fa.usuario_id IN ($inSql)
                )
            )";
            if (trim($where) === '') {
                $where = ' WHERE ' . $userFilter . ' ';
            } else {
                $where .= ' AND ' . $userFilter . ' ';
            }
        }

        try {
            // KPIs por estado (alcance)
            $st = $pdo->prepare("SELECT estado_proyecto, COUNT(*) c FROM {$t} p {$where} GROUP BY estado_proyecto");
            $st->execute($params);
            $porEstado = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $porEstado[$r['estado_proyecto']] = (int)$r['c'];
            }

            // Gestiones en rango (Aprobado/Rechazado/Reabierto/Asignacion)
            $wLog = $where === ''
                ? " WHERE DATE(l.dtcreated) BETWEEN :desde AND :hasta"
                : $where . " AND DATE(l.dtcreated) BETWEEN :desde AND :hasta";
            $paramsRango = $params;
            $paramsRango[':desde'] = $desde;
            $paramsRango[':hasta'] = $hasta;

            $stAcc = $pdo->prepare(
                "SELECT l.accion, COUNT(*) AS total
                 FROM {$tLog} l
                 INNER JOIN {$t} p ON p.id = l.proyecto_id
                 {$wLog}
                 GROUP BY l.accion
                 ORDER BY total DESC"
            );
            $stAcc->execute($paramsRango);
            $acciones = $stAcc->fetchAll(PDO::FETCH_ASSOC);

            $stUsr = $pdo->prepare(
                "SELECT u.id, CONCAT(COALESCE(u.nombre,''),' ',COALESCE(u.apellido,'')) AS usuario, u.nickname,
                        SUM(l.accion = 'Aprobado') AS aprobados,
                        SUM(l.accion = 'Rechazado') AS rechazados,
                        SUM(l.accion IN ('Aprobado','Rechazado','Reabierto','Asignacion','Reenviado/Editado')) AS gestiones,
                        COUNT(*) AS acciones_total
                 FROM {$tLog} l
                 INNER JOIN {$t} p ON p.id = l.proyecto_id
                 LEFT JOIN {$tUsr} u ON u.id = l.usuario_id
                 {$wLog}
                 GROUP BY u.id, u.nombre, u.apellido, u.nickname
                 ORDER BY gestiones DESC
                 LIMIT 30"
            );
            $stUsr->execute($paramsRango);
            $porUsuario = $stUsr->fetchAll(PDO::FETCH_ASSOC);

            $stTend = $pdo->prepare(
                "SELECT DATE(l.dtcreated) AS dia,
                        SUM(l.accion = 'Aprobado') AS aprobados,
                        SUM(l.accion = 'Rechazado') AS rechazados,
                        SUM(l.accion = 'Creado') AS creados,
                        COUNT(*) AS total
                 FROM {$tLog} l
                 INNER JOIN {$t} p ON p.id = l.proyecto_id
                 {$wLog}
                 GROUP BY DATE(l.dtcreated)
                 ORDER BY dia ASC"
            );
            $stTend->execute($paramsRango);
            $tendencia = $stTend->fetchAll(PDO::FETCH_ASSOC);

            $stDet = $pdo->prepare(
                "SELECT l.dtcreated, l.accion, l.observacion, l.proyecto_id, p.proyecto,
                        CONCAT(COALESCE(u.nombre,''),' ',COALESCE(u.apellido,'')) AS usuario
                 FROM {$tLog} l
                 INNER JOIN {$t} p ON p.id = l.proyecto_id
                 LEFT JOIN {$tUsr} u ON u.id = l.usuario_id
                 {$wLog}
                 ORDER BY l.dtcreated DESC
                 LIMIT 100"
            );
            $stDet->execute($paramsRango);
            $detalle = $stDet->fetchAll(PDO::FETCH_ASSOC);

            if ($where === '') {
                $sqlAsig = "SELECT COUNT(*) FROM {$tAsig} a INNER JOIN {$t} p ON p.id = a.proyecto_id WHERE a.activo = 1";
            } else {
                $sqlAsig = "SELECT COUNT(*) FROM {$tAsig} a INNER JOIN {$t} p ON p.id = a.proyecto_id {$where} AND a.activo = 1";
            }
            $stAsig = $pdo->prepare($sqlAsig);
            $stAsig->execute($params);
            $totalAsignaciones = (int) $stAsig->fetchColumn();

            $gestionesRango = 0;
            foreach ($acciones as $a) {
                if (in_array($a['accion'], ['Aprobado', 'Rechazado', 'Reabierto', 'Asignacion'], true)) {
                    $gestionesRango += (int)$a['total'];
                }
            }

            return [
                'output' => [
                    'valid' => true,
                    'response' => [
                        'scope' => $scope['mode'],
                        'rango' => ['desde' => $desde, 'hasta' => $hasta],
                        'filtros' => [
                            'municipio_id' => $munFiltro ?: null,
                            'usuario_ids' => $usuarioIds,
                        ],
                        'kpis' => [
                            'enviados' => (int)($porEstado['Enviado'] ?? 0),
                            'aprobados' => (int)($porEstado['Aprobado'] ?? 0),
                            'rechazados' => (int)($porEstado['Rechazado'] ?? 0),
                            'total_proyectos' => array_sum($porEstado),
                            'gestiones_rango' => $gestionesRango,
                            'asignaciones_activas' => $totalAsignaciones,
                        ],
                        'por_estado' => $porEstado,
                        'acciones' => $acciones,
                        'por_usuario' => $porUsuario,
                        'tendencia' => $tendencia,
                        'detalle' => $detalle,
                    ],
                ],
            ];
        } catch (Throwable $e) {
            return ['output' => ['valid' => false, 'response' => ['content' => $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

    /** Listado unificado para la vista PHP (acepta filtros GET) */
    public static function getProyectosParaListado(array $filters = []): array
    {
        require_once __DIR__ . '/SessionData.php';
        require_once __DIR__ . '/Authorization.php';

        $munSesion = SessionData::getCodigoMunicipio();
        $tieneMunicipio = self::sessionHasMunicipio($munSesion);
        $tipo = $_SESSION['session_user']['tipo'] ?? '';
        $esVistaDepto = self::isVistaDepartamental();

        $munFiltro = $filters['municipio_id'] ?? 0;
        // Candado duro solo para usuarios de alcaldía (no vista departamental)
        if (!$esVistaDepto) {
            $munFiltro = $tieneMunicipio ? $munSesion : 0;
        }

        $usuarioIds = isset($filters['usuario_ids']) ? (array)$filters['usuario_ids'] : [];

        return self::getAllProyectos([
            'municipio_id_search' => self::sessionHasMunicipio($munFiltro) ? $munFiltro : 0,
            'usuario_ids' => $usuarioIds,
            'user_rol' => $tipo,
        ]);
    }

    /**
     * Usuarios para Select2 de filtro del listado.
     * - Vista departamental: por municipio (opcional) o todos los del depto.
     * - Alcaldía: solo su municipio.
     */
    public static function getUsuariosFiltroListado(array $rqst = []): array
    {
        require_once __DIR__ . '/Authorization.php';
        require_once __DIR__ . '/SessionData.php';

        if (
            !Authorization::can('proyectos.alcaldias.planeacion.manage')
            && !Authorization::can('proyectos.alcaldias.planeacion.assign')
            && !self::canViewAllDepartamento()
            && !self::canViewAllAlcaldia()
            && !SessionData::superAdministrador()
        ) {
            return ['output' => ['valid' => false, 'response' => ['content' => 'Sin permiso.']]];
        }

        $munSesion = SessionData::getCodigoMunicipio();
        $tieneMunicipio = self::sessionHasMunicipio($munSesion);
        $esVistaDepto = self::isVistaDepartamental();

        $munFiltro = $rqst['municipio_id'] ?? '';
        // Candado: alcaldía solo ve usuarios de su municipio
        if ($tieneMunicipio && !$esVistaDepto) {
            $munFiltro = $munSesion;
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            // Creadores (create) + gestores (manage) del módulo
            $sql = "SELECT DISTINCT u.id, u.nombre, u.apellido, u.nickname, u.tipo, u.tbl_municipio_id,
                           c.municipio AS nombre_municipio
                    FROM " . $db->getTable('tbl_usuarios') . " u
                    INNER JOIN " . $db->getTable('tbl_role_has_permissions') . " rhp ON rhp.role_id = u.role_id
                    INNER JOIN " . $db->getTable('tbl_permissions') . " p ON p.id = rhp.permission_id
                    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                      ON c.codigo_muncipio = u.tbl_municipio_id
                    WHERE p.permission_key IN (
                        'proyectos.alcaldias.planeacion.manage',
                        'proyectos.alcaldias.planeacion.create',
                        'secretarias.proyectos.create'
                      )
                      AND p.is_active = 1
                      AND " . self::sqlUsuarioHabilitado('u');
            $params = [];
            if (self::sessionHasMunicipio($munFiltro)) {
                $sql .= " AND u.tbl_municipio_id = :mun";
                $params[':mun'] = $munFiltro;
            } else {
                // Vista departamental sin municipio: limitar al departamento principal
                $depto = Util::getDepartamentoPrincipal();
                $sql .= " AND (c.codigo_departamento = :depto OR u.tbl_municipio_id IS NULL OR u.tbl_municipio_id = '' OR u.tbl_municipio_id = '0' OR u.tbl_municipio_id = 0)";
                $params[':depto'] = $depto;
            }
            $sql .= " ORDER BY c.municipio, u.nombre, u.apellido";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return ['output' => ['valid' => true, 'response' => $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []]];
        } catch (Throwable $e) {
            return ['output' => ['valid' => false, 'response' => ['content' => $e->getMessage()]]];
        } finally {
            $db->closeConect();
        }
    }

}