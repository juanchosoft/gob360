<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Ministeriospro
{

  /**
   * Obtiene todos los proyectos sin leer para una secretaría, municipio y departamento específicos.
   * Utiliza consultas preparadas para evitar inyección SQL.
   *
   * @param array $rqst
   * @return array
   */
  /**
   * Obtiene todos los proyectos sin leer para una secretaría, municipio y departamento específicos.
   * Utiliza consultas preparadas y manejo de errores.
   *
   * @param array $rqst
   * @return array
   */
  /**
   * Obtiene todos los proyectos sin leer para una secretaría, municipio y departamento específicos.
   * Solo permite acceso a usuarios Secretario de Despacho o Auxiliar de la secretaría y municipio correspondientes.
   *
   * @param array $rqst
   * @return array
   */
  public static function getAllProyectosSinLeer($rqst)
  {

    $codigoDepartamentoSession = $_SESSION['session_user']['tbl_departamento_id'];

    $id = isset($rqst['id']) ? (int)$rqst['id'] : 0;
    $secretariaId = isset($rqst['secretariaId']) ? (int)$rqst['secretariaId'] : SessionData::getSecretaria();
    $codigoMunicipio = $rqst['codigoMunicipio'] ?? SessionData::getCodigoMunicipio();
    $codigoDepartamento = $rqst['codigoDepartamento'] ?? $codigoDepartamentoSession;

    $userType = SessionData::getUserType();
    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
    $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
    $isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    $secretariaUsuarioId = SessionData::getSecretaria();
    $codigoUsuarioMunicipio = SessionData::getCodigoMunicipio();
    $tbl_usuario_id = (int)($_SESSION['session_user']['id'] ?? 0);

    // Permisos: Admin ve todo, secretario/auxiliar solo su secretaría y municipio
    // Si se está consultando por un ID específico, no validar acceso (se asume que tiene acceso)
    /*     if ($id <= 0) {
      $hasAccess = $isAdmin || ($isSecretarioDespachoOAuxiliar && $codigoMunicipio == $codigoUsuarioMunicipio && $secretariaUsuarioId == $secretariaId);
      if (!$hasAccess) {
        return ['output' => ['valid' => true, 'response' => []]];
      }
    } */


    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      if ($id > 0) {
        $q = "SELECT p.*, c.municipio, s.secretaria, a.nombre AS actor
              FROM " . $db->getTable('tbl_ministerios_proyectos') . " p
              INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON s.id = p.tbl_secretaria_id
              INNER JOIN " . $db->getTable('tbl_actores_mapa') . " a ON a.id = p.actor_id
              INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c ON c.codigo_muncipio = p.tbl_municipio_id
              WHERE p.id = :id";

        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // Solo secretario/auxiliar marca como leído
        if ($isSecretario || $isAlcalde) {
          $updateProyecto = "UPDATE " . $db->getTable('tbl_ministerios_proyectos') . " SET leido = 'si', estado = 'Proyecto leido', fecha_leido = NOW(), tbl_usuario_id_leido = " . $tbl_usuario_id . " WHERE id = :id";
          $updateNotif = "UPDATE " . $db->getTable('tbl_notificaciones_secretaria') . " SET leido = 'si' WHERE tbl_ministerios_proyecto_id = :id";
          $pdo->prepare($updateProyecto)->execute([':id' => $id]);
          $pdo->prepare($updateNotif)->execute([':id' => $id]);
        }
      } else {
        $q = "SELECT p.*, c.municipio, s.secretaria
              FROM " . $db->getTable('tbl_ministerios_proyectos') . " p
              INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON s.id = p.tbl_secretaria_id
              INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c ON c.codigo_muncipio = p.tbl_municipio_id";
        // WHERE (p.leido IS NULL OR p.leido = 'no')";

        if ($isAlcalde) {
          $q .= " AND c.codigo_muncipio = :codigo_muncipio";
        }
        if ($isSecretario) {
          $q .= " AND c.codigo_departamento = :codigoDepartamento";
        }

        $stmt = $pdo->prepare($q);
        if ($isAlcalde) {
          $stmt->bindValue(':codigo_muncipio', $codigoMunicipio);
        }
        if ($isSecretario) {
          $stmt->bindValue(':codigoDepartamento', $codigoDepartamento);
        }
      }
      $stmt->execute();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Obtener observaciones para cada proyecto y agregarlas directamente en $arr
      if (!empty($arr)) {
        foreach ($arr as $k => $proyecto) {
          $proyectoId = $proyecto['id'] ?? null;

          if ($proyectoId) {

            $qObs = "SELECT * FROM " . $db->getTable('tbl_ministerios_proyectos_x_observaciones') . " WHERE tbl_proyecto_id = :proyecto_id";
            $stmtObs = $pdo->prepare($qObs);
            $stmtObs->execute([':proyecto_id' => $proyectoId]);
            $arr[$k]['observaciones'] = $stmtObs->fetchAll(PDO::FETCH_ASSOC);
          } else {
            $arr[$k]['observaciones'] = [];
          }
        }
      }
      $arrjson = ['output' => ['valid' => true, 'response' => $arr]];
    } catch (PDOException $e) {
      $arrjson = ['output' => ['valid' => false, 'error' => $e->getMessage()]];
    } finally {
      $db->closeConect();
    }
    return $arrjson;
  }

  public static function getAllProyectosTodos($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;
    $codigoMunicipio = $rqst['codigoMunicipio'] ?? null;
    $codigoDepartamento = $rqst['codigoDepartamento'] ?? null;

    $userType = SessionData::getUserType();
    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
    $isSecretarioDespachoOAuxiliar = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
    $secretariaUsuarioId = SessionData::getSecretaria();
    $codigoUsuarioMunicipio = SessionData::getCodigoMunicipio();
    $tbl_usuario_id = intval($_SESSION['session_user']['id']);

    // Permisos: Admin ve todo, secretario/auxiliar solo su secretaría y municipio
    // Si se está consultando por un ID específico, no validar acceso (se asume que tiene acceso)
    if ($id <= 0) {
      $hasAccess = $isAdmin || (
        $isSecretarioDespachoOAuxiliar &&
        $codigoMunicipio == $codigoUsuarioMunicipio &&
        $secretariaUsuarioId == $secretariaId
      );
      if (!$hasAccess) {
        return ['output' => ['valid' => true, 'response' => []]];
      }
    }

    $db = new DbConection();
    $pdo = $db->openConect();
    try {
      if ($id > 0) {
        $q = "SELECT p.*, c.municipio, s.secretaria, a.nombre AS actor
              FROM " . $db->getTable('tbl_ministerios_proyectos') . " p
              INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON s.id = p.tbl_secretaria_id
              INNER JOIN " . $db->getTable('tbl_actores_mapa') . " a ON a.id = p.actor_id
              INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c ON c.codigo_muncipio = p.tbl_municipio_id
              WHERE p.id = :id";
        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        // Solo secretario/auxiliar marca como leído
        if ($isSecretarioDespachoOAuxiliar) {
          $updateProyecto = "UPDATE " . $db->getTable('tbl_ministerios_proyectos') . " SET leido = 'si', estado = 'Proyecto leido', fecha_leido = NOW(), tbl_usuario_id_leido = " . $tbl_usuario_id . " WHERE id = :id";
          $updateNotif = "UPDATE " . $db->getTable('tbl_notificaciones_secretaria') . " SET leido = 'si' WHERE tbl_ministerios_proyecto_id = :id";
          $pdo->prepare($updateProyecto)->execute([':id' => $id]);
          $pdo->prepare($updateNotif)->execute([':id' => $id]);
        }
      } else {

        $q = "SELECT p.*, c.municipio, s.secretaria
              FROM " . $db->getTable('tbl_ministerios_proyectos') . " p
              INNER JOIN " . $db->getTable('tbl_secretarias') . " s ON s.id = p.tbl_secretaria_id
              INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c ON c.codigo_muncipio = p.tbl_municipio_id";
        // WHERE (p.leido IS NULL OR p.leido = 'no')";

        if (!$isAdmin) {
          $q .= " AND c.codigo_muncipio = :codigoMunicipio
                  AND c.codigo_departamento = :codigoDepartamento
                  AND s.id = :secretariaId";
        }
        $stmt = $pdo->prepare($q);
        if (!$isAdmin) {
          $stmt->bindValue(':codigoMunicipio', $codigoMunicipio);
          $stmt->bindValue(':codigoDepartamento', $codigoDepartamento);
          $stmt->bindValue(':secretariaId', $secretariaId, PDO::PARAM_INT);
        }
      }
      $stmt->execute();
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $arrjson = ['output' => ['valid' => true, 'response' => $arr]];
    } catch (PDOException $e) {
      $arrjson = ['output' => ['valid' => false, 'error' => $e->getMessage()]];
    } finally {
      $db->closeConect();
    }
    return $arrjson;
  }

  /**
   * Actualiza el estado de un proyecto por su ID.
   * Utiliza consultas preparadas para evitar inyección SQL y manejo adecuado de errores.
   *
   * @param array $rqst
   * @return array
   */
  public static function actualizarEstadoProyecto($rqst)
  {
    $id = isset($rqst['idEditar']) ? intval($rqst['idEditar']) : 0;
    $estado = isset($rqst['estado']) ? trim($rqst['estado']) : '';

    if ($estado === '' || $id <= 0) {
      return Util::error_missing_data();
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    try {
      $pdo->beginTransaction();

      // Si el estado es 'Proyecto leido', también actualiza el campo leido a 'si'
      if ($estado === 'Proyecto leido') {
        $q = "UPDATE " . $db->getTable('tbl_ministerios_proyectos') . " 
          SET estado = :estado, leido = 'si'
          WHERE id = :id";

        $stmt = $pdo->prepare($q);
        $success = $stmt->execute([
          ':estado' => $estado,
          ':id' => $id
        ]);

        // Actualiza la notificación
        $updateNotif = "UPDATE " . $db->getTable('tbl_notificaciones_secretaria') . " SET leido = 'si' WHERE tbl_ministerios_proyecto_id = :id";
        $stmtNotif = $pdo->prepare($updateNotif);
        $successNotif = $stmtNotif->execute([':id' => $id]);

        if ($success && $successNotif) {
          $pdo->commit();
          $arrjson = ['output' => ['valid' => true, 'message' => 'Estado actualizado correctamente.']];
        } else {
          $pdo->rollBack();
          $arrjson = Util::error_general('No se pudo actualizar el estado o la notificación.');
        }
      } else {

        // No leido
        $q = "UPDATE " . $db->getTable('tbl_ministerios_proyectos') . " 
          SET estado = :estado, leido = 'no', fecha_leido = NULL, tbl_usuario_id_leido = NULL
          WHERE id = :id";
        $stmt = $pdo->prepare($q);
        $success = $stmt->execute([
          ':estado' => $estado,
          ':id' => $id
        ]);

        $updateProyecto = "UPDATE " . $db->getTable('tbl_notificaciones_secretaria') . " SET leido = 'no' WHERE tbl_ministerios_proyecto_id = :id";
        $stmtProyecto = $pdo->prepare($updateProyecto);
        $stmtProyecto->execute([':id' => $id]);

        if ($success) {
          $pdo->commit();
          $arrjson = ['output' => ['valid' => true, 'response' => 'Estado actualizado correctamente.']];
        } else {
          $pdo->rollBack();
          $arrjson = Util::error_general('No se pudo actualizar el estado.');
        }
      }
    } catch (PDOException $e) {
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      $arrjson = ['output' => ['valid' => false, 'error' => $e->getMessage()]];
    } finally {
      $db->closeConect();
    }
    return $arrjson;
  }

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_ministerios_id = isset($rqst['tbl_ministerios_id']) ? intval($rqst['tbl_ministerios_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();
    $observaciones = array();

    $q = "SELECT tbl_ministerios_proyectos.*, 
      tbl_ministerios.ministerio as ministerio, tbl_ciudades_accion_unificada.municipio
      FROM " .
      $db->getTable('tbl_ministerios_proyectos') . "," .
      $db->getTable('tbl_ciudades_accion_unificada') . "
      WHERE tbl_ciudades_accion_unificada.codigo_muncipio = tbl_ministerios_proyectos.tbl_municipio_id ";

    if ($id > 0) {
      $q = "SELECT tbl_ministerios_proyectos.*, tbl_ministerios.ministerio as ministerio 
      FROM " . $db->getTable('tbl_ministerios_proyectos') . "," . $db->getTable('tbl_ministerios') . " 
      WHERE tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id AND
      tbl_ministerios_proyectos.id = " . $id;

      $observaciones = Ministeriospro::getObservacionesByProyectoId($id);
    } elseif ($tbl_ministerios_id > 0) {
      $q = "SELECT tbl_ministerios_proyectos.*, tbl_ministerios.ministerio as ministerios 
      FROM " . $db->getTable('tbl_ministerios_proyectos') . "," . $db->getTable('tbl_ministerios') . " 
      WHERE tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id AND
      tbl_ministerios_proyectos.tbl_ministerios_id = " . $tbl_ministerios_id;
    }
    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {
        $arr[] = $valor;
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'observaciones' => $observaciones));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

  //   ===================================================================================================================================

  public static function getAllobser($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    // Conexión a la base de datos
    $db = new DbConection();
    $pdo = $db->openConect();

    try {
      // Consulta base
      $q = "SELECT 
                obs.observaciones, 
                p.dtcreate, 
                p.id
              FROM " . $db->getTable('tbl_ministerios_proyectos') . " p
              INNER JOIN " . $db->getTable('tbl_ministerios_proyectos_x_observaciones') . " obs 
              ON p.id = obs.tbl_proyecto_id";

      // Agregar condición WHERE si se proporciona un ID
      if ($id > 0) {
        $q .= " WHERE p.id = :id";
      }

      // Preparar consulta
      $stmt = $pdo->prepare($q);

      // Enlazar parámetros si es necesario
      if ($id > 0) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      }

      // Ejecutar consulta
      $stmt->execute();

      // Obtener resultados
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Crear respuesta JSON
      if ($result) {
        $arrjson = array('output' => array('valid' => true, 'response' => $result));
      } else {
        $arrjson = Util::error_no_result(); // Error: sin resultados
      }
    } catch (PDOException $e) {
      // Manejar errores de la consulta
      $arrjson = array('output' => array('valid' => false, 'error' => $e->getMessage()));
    } finally {
      $db->closeConect();
    }

    return $arrjson;
  }





  public static function getAllobservacion($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_ministerios_id = isset($rqst['tbl_ministerios_id']) ? intval($rqst['tbl_ministerios_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();
    $observaciones = array();

    $q = "SELECT * FROM " . $db->getTable('tbl_ministerios_proyectos');
    if ($id > 0) {
      $q = "SELECT tbl_ministerios_proyectos.*, tbl_ministerios.ministerios as ministerios 
      FROM " . $db->getTable('tbl_ministerios_proyectos') . "," . $db->getTable('tbl_ministerios') . " 
      WHERE tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id AND
      tbl_ministerios_proyectos.id = " . $id;

      $observaciones = Proyectos::getObservacionesByProyectoId($id);
    } elseif ($tbl_ministerios_id > 0) {
      $q = "SELECT tbl_ministerios_proyectos.*, tbl_ministerios.ministerios as ministerios 
      FROM " . $db->getTable('tbl_ministerios_proyectos') . "," . $db->getTable('tbl_ministerios') . " 
      WHERE tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id AND
      tbl_ministerios_proyectos.tbl_ministerios_id = " . $tbl_ministerios_id;
    }
    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {
        $arr[] = $valor;
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'observaciones' => $observaciones));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

  


  public static function getObservacionesByProyectoId($id)
  {

    if ($id > 0) {

      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "SELECT * FROM " . $db->getTable('tbl_ministerios_proyectos_x_observaciones') . " WHERE tbl_proyecto_id = " . $id;
      $result = $pdo->query($q);
      $observaciones = array();
      if ($result) {
        foreach ($result as $valor) {
          $observaciones[] = $valor;
        }
      }
      $db->closeConect();
      return $observaciones;
    } else {
      return array();
    }
  }

  public static function getInversionBySecre($rqst)
  {
    $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT ministerio, SUM(tbl_ministerios_proyectos.valor_proyecto) total FROM " . $db->getTable('tbl_ministerios') . "
    LEFT JOIN " . $db->getTable('tbl_ministerios_proyectos') . " ON tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id
    WHERE tbl_ministerios_proyectos.tbl_municipio_id = " . $mun . "
    GROUP BY tbl_ministerios.id";

    $result = $pdo->query($q);
    $arrpro = array();
    $labels = [];
    $data = [];
    if ($result) {

      foreach ($result as $valor) {
        $labels[] = $valor["ministerio"];
        $data[] = intval($valor["total"]);
      }
    }

    $arrjson = array('output' => array('valid' => true, 'response' => compact("labels", "data")));
    $db->closeConect();
    return $arrjson;
  }

  public static function getAllproyectosxalcal($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_ministerios_proyectos.id,tbl_ministerios.ministerio, tbl_ministerios_proyectos.tbl_ministerios_id, tbl_ministerios_proyectos.valor_proyecto, tbl_ministerios_proyectos.proyecto
    FROM " . $db->getTable('tbl_ministerios_proyectos') . "
    INNER JOIN " . $db->getTable('tbl_ministerios') . " ON tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id
    GROUP BY tbl_ministerios.ministerio, tbl_ministerios_proyectos.valor_proyecto, tbl_ministerios_proyectos.proyecto";

    if ($id > 0) {
      $q = "SELECT tbl_ministerios_proyectos.id,tbl_ministerios.ministerio, tbl_ministerios_proyectos.tbl_ministerios_id, tbl_ministerios_proyectos.valor_proyecto, tbl_ministerios_proyectos.proyecto
      FROM " . $db->getTable('tbl_ministerios_proyectos') . "
      INNER JOIN " . $db->getTable('tbl_ministerios') . " ON tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id
      WHERE tbl_ministerios.id = " . $id . "
      GROUP BY tbl_ministerios.ministerio, tbl_ministerios_proyectos.valor_proyecto, tbl_ministerios_proyectos.proyecto";
    }

    if ($mun > 0) {
      $q = "SELECT tbl_ministerios_proyectos.id,tbl_ministerios.ministerio, tbl_ministerios_proyectos.tbl_ministerios_id, tbl_ministerios_proyectos.valor_proyecto, tbl_ministerios_proyectos.proyecto
      FROM " . $db->getTable('tbl_ministerios_proyectos') . "
      INNER JOIN " . $db->getTable('tbl_ministerios') . " ON tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id
      WHERE tbl_ministerios_proyectos.tbl_municipio_id = " . $mun . "
      GROUP BY tbl_ministerios.ministerio, tbl_ministerios_proyectos.valor_proyecto, tbl_ministerios_proyectos.proyecto";
    }

    $result = $pdo->query($q);
    $arrpro = array();
    if ($result) {
      foreach ($result as $valor) {
        $arrpro[] = $valor;
      }

      $arrjson = array('output' => array('valid' => true, 'response' => $arrpro));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }


  public static function getProyectosPorministerios($rqst)
  {

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_ministerios.ministerio, Count(tbl_ministerios_proyectos.proyecto) AS CuentaDeproyecto, tbl_ministerios_proyectos.valor_proyecto AS valor,  tbl_ministerios.id, tbl_ministerios_proyectos.fecha_entrega
      FROM " . $db->getTable('tbl_ministerios_proyectos') . "
      INNER JOIN " . $db->getTable('tbl_ministerios') . " ON tbl_ministerios_proyectos.tbl_ministerios_id = tbl_ministerios.id
      GROUP BY tbl_ministerios.ministerio, tbl_ministerios_proyectos.valor_proyecto";
    $result = $pdo->query($q);
    $arr = array();
    $arrTemporal = array();

    if ($result) {
      foreach ($result as $valor) {

        $tbl_ministerios_id = $valor['tbl_ministerios_id'];
        $arrTemporal['tbl_ministerios_id'] = $tbl_ministerios_id;
        $arrTemporal['ministerio'] = $valor['ministerio'];
        $arrTemporal['valor'] = $valor['valor'];




        $arr[] = $arrTemporal;
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

  public static function save($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_ministerios_id = isset($rqst['tbl_ministerios_id']) ? intval($rqst['tbl_ministerios_id']) : 0;
    $proyecto =  isset($rqst['proyecto']) ? ($rqst['proyecto']) : '';

    $tbl_secretarias_id =  isset($rqst['tbl_secretarias_id']) ? ($rqst['tbl_secretarias_id']) : '';
    $date =  isset($rqst['date']) ? ($rqst['date']) : '';
    $provincia =  isset($rqst['provincia']) ? ($rqst['provincia']) : '';
    $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : 0;
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : 0;
    $actores_id = isset($rqst['actores_id']) ? ($rqst['actores_id']) : 0;
    $foto1 = $rqst['iframe1'] ?? '';
    $pdf = $rqst['pdf'] ?? '';

    $aporteMunicipio = isset($rqst['aporteMunicipio']) ? (float) str_replace('.', '', $rqst['aporteMunicipio']) : 0;
    $aporteDepartamento = isset($rqst['aporteDepartamento']) ? (float) str_replace('.', '', $rqst['aporteDepartamento']) : 0;
    $aporteNacion = isset($rqst['aporteNacion']) ? (float) str_replace('.', '', $rqst['aporteNacion']) : 0;
    $aporteOtrosProyectos = isset($rqst['aporteOtrosProyectos']) ? (float) str_replace('.', '', $rqst['aporteOtrosProyectos']) : 0;


    $valor_proyecto = $aporteMunicipio + $aporteDepartamento + $aporteNacion + $aporteOtrosProyectos;
    $observaciones = isset($rqst['observaciones']) && $rqst['observaciones'] != "" ? ($rqst['observaciones']) : null;
    $tbl_usuario_id =  intval($_SESSION['session_user']['id']);

    if ($aporteOtrosProyectos > 0 && $actores_id == 0) {
      return Util::error_general('Por favor, seleccione el actor correspondiente a otros aportes.');
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($id > 0) {
      //actualiza la informacion
      $q = "SELECT id FROM " . $db->getTable('tbl_ministerios_proyectos') . " WHERE id = " . $id;
      $result = $pdo->query($q);
      if ($result) {
        $table = $db->getTable('tbl_ministerios_proyectos');
        $arrfieldscomma = array(
          'proyecto' => $proyecto,
          'aporteMunicipio' => $aporteMunicipio,
          'aporteDepartamento' => $aporteDepartamento,
          'aporteNacion' => $aporteNacion,
          'aporteOtrosProyectos' => $aporteOtrosProyectos,
          'tbl_secretarias_id' => $tbl_secretarias_id,
          'valor_proyecto' => $valor_proyecto,
          'observaciones' => $observaciones,
          'actores_id' => $actores_id,
        );

        $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
        $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);

        print_r($q);
        exit();

        $result = $pdo->query($q);
        if (!$result) {
          $arrjson = Util::error_general(' Al actualizar los datos de proyectos....');
        } else {
          $qInsert = "INSERT INTO " . $db->getTable('tbl_ministerios_proyectos_x_observaciones') . " (dtcreate, tbl_proyecto_id, observaciones, tbl_usuario_id) VALUES ( " . Util::date_now_server() . ", :tbl_proyecto_id, :observaciones, :tbl_usuario_id)";
          $resultInsert = $pdo->prepare($qInsert);
          $arrparamInsert = array(
            ':tbl_proyecto_id' => $id,
            ':observaciones' => $observaciones,
            ':tbl_usuario_id' => $tbl_usuario_id
          );
          if ($resultInsert->execute($arrparamInsert)) {
            $arrjson = array('output' => array('valid' => true, 'id' => $id));
          } else {
            $arrjson = Util::error_general(' Al actualizar las observaciones de proyectos.');
          }
        }
      } else {
        $arrjson = Util::error_general();
      }
    } else {

      if ($proyecto != "" && !empty($tbl_municipio_id)) {
        $q = "INSERT INTO " . $db->getTable('tbl_ministerios_proyectos') . " (dtcreate, tbl_ministerios_id, tbl_departamento_id, provincia_id, tbl_municipio_id, aporte_municipio, aporte_departamento, aporte_nacion, otro_aportes, proyecto, date, tbl_secretaria_id, valor_proyecto,  observaciones, tbl_usuario_id, actor_id, archivo, pdf)
                VALUES ( " . Util::date_now_server() . ", :tbl_ministerios_id, :tbl_departamento_id, :provincia_id, :tbl_municipio_id, :aporte_municipio, :aporte_departamento, :aporte_nacion, :otro_aportes, :proyecto, :date, :tbl_secretaria_id, :valor_proyecto, :observaciones, :tbl_usuario_id, :actor_id, :archivo, :pdf)";
        $result = $pdo->prepare($q);
        $arrparam = array(
          ':tbl_ministerios_id' => $tbl_ministerios_id> 0 ? $tbl_ministerios_id : null,
          ':tbl_departamento_id' => $tbl_departamento_id,
          ':provincia_id' => $provincia,
          ':tbl_municipio_id' => $tbl_municipio_id,
          ':aporte_municipio' => $aporteMunicipio,
          ':aporte_departamento' => $aporteDepartamento,
          ':aporte_nacion' => $aporteNacion,
          ':otro_aportes' => $aporteOtrosProyectos,
          ':proyecto' => $proyecto,
          ':date' => $date,
          ':tbl_secretaria_id' => $tbl_secretarias_id,
          ':valor_proyecto' => $valor_proyecto,
          ':observaciones' => $observaciones,
          ':tbl_usuario_id' => $tbl_usuario_id,
          ':actor_id' => $actores_id,
          ':archivo' => $foto1,
          ':pdf' => $pdf
        );

        if ($result->execute($arrparam)) {

          $lastInsertId = $pdo->lastInsertId();
          $arrjson = array('output' => array('valid' => true, 'response' => $lastInsertId));

          // Ingreso de observaciones
          if (!empty($observaciones)) {
            $qInsert = "INSERT INTO " . $db->getTable('tbl_ministerios_proyectos_x_observaciones') . " 
              (dtcreate, tbl_proyecto_id, observaciones, tbl_usuario_id) 
              VALUES (" . Util::date_now_server() . ", :tbl_proyecto_id, :observaciones, :tbl_usuario_id)";
            $stmtInsert = $pdo->prepare($qInsert);
            $paramsInsert = [
              ':tbl_proyecto_id' => $lastInsertId,
              ':observaciones' => $observaciones,
              ':tbl_usuario_id' => $tbl_usuario_id
            ];
            if (!$stmtInsert->execute($paramsInsert)) {
              $arrjson = Util::error_general('Error al guardar las observaciones del proyecto.');
            }
          }


          // Notificación a la secretaría al registrar un nuevo proyecto
          $nombre_secretaria = '';
          $query = "SELECT secretaria FROM " . $db->getTable('tbl_secretarias') . " WHERE id = :id";
          $stmt = $pdo->prepare($query);
          if ($stmt->execute([':id' => $tbl_secretarias_id])) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
              $nombre_secretaria = $row['secretaria'];
            }
          }

          // Insertar notificación solo si se obtuvo el nombre de la secretaría
          if ($nombre_secretaria !== '') {
            $qNotif = "INSERT INTO " . $db->getTable('tbl_notificaciones_secretaria') . " 
                (codigo_municipio, tbl_ministerios_proyecto_id, tbl_secretaria_id, nombre_secretria, titulo, mensaje, leido, dtcreate)
                VALUES (:codigo_municipio, :tbl_ministerios_proyecto_id, :secretaria_id, :nombre_secretaria, :titulo, :mensaje, 'no', NOW())";
            $stmtNotif = $pdo->prepare($qNotif);
            $stmtNotif->execute([
              ':codigo_municipio' => $tbl_municipio_id,
              ':tbl_ministerios_proyecto_id' => $lastInsertId,
              ':secretaria_id' => $tbl_secretarias_id,
              ':nombre_secretaria' => $nombre_secretaria,
              ':titulo' => 'Nuevo Proyecto Registrado',
              ':mensaje' => 'Se ha registrado un nuevo proyecto: ' . $proyecto . ' de la secretaría ' . $nombre_secretaria . '.',
            ]);
          }
        } else {
          $arrjson = Util::error_general(' Al guardar los datos de proyectos');
        }
      }
    }
    $db->closeConect();
    return $arrjson;
  }

  /**
   * obtiene todos los proyectos con paginación, búsqueda y filtros
   * @param mixed $rqst
   * @return array{data: array, draw: int, recordsFiltered: int, recordsTotal: int|array{output: array}}
   */
  public static function getAllproyectos($rqst)
  {
    $data = $rqst ?? [];

    $draw = $data['draw'] ?? 1;
    $start = $data['start'] ?? 0;
    $length = $data['length'] ?? 10;
    $searchValue = $data['search']['value'] ?? '';

    try {

      session_start();
      require_once 'SessionData.php';
      require_once 'DbConection.php';
      require_once 'Util.php';


      $db = new DbConection();
      $pdo = $db->openConect();

      $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
      $secretariaUsuarioLogueado = SessionData::getSecretaria();
      $userType = SessionData::getUserType();

      $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
      $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
      $isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

      $fromClause = "FROM " . $db->getTable('tbl_ministerios_proyectos') . " AS proyectos
      JOIN " . $db->getTable('tbl_departamentos') . " AS departamentos ON proyectos.tbl_departamento_id = departamentos.codigo_departamento
      JOIN " . $db->getTable('tbl_secretarias') . " AS secretaria ON proyectos.tbl_secretaria_id = secretaria.id
      LEFT JOIN " . $db->getTable('tbl_actores_mapa') . " AS actor ON proyectos.actor_id = actor.id
      JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS municipios ON proyectos.tbl_municipio_id = municipios.codigo_muncipio";

      $where = [];
      $params = [];

      if ($isAlcalde) {
        $where[] = "proyectos.tbl_municipio_id = :municipio";
        $params[':municipio'] = $municipioUsuarioLogueado;
      } elseif ($isSecretario) {
        $where[] = "proyectos.tbl_secretaria_id = :secretaria";
        $params[':secretaria'] = $secretariaUsuarioLogueado;
      }

      if (!empty($searchValue)) {
        $where[] = "(
                    municipios.municipio LIKE :search OR
                    departamentos.departamento LIKE :search OR
                    secretaria.secretaria LIKE :search OR
                    proyectos.observaciones LIKE :search OR
                    proyectos.proyecto LIKE :search OR
                    actor.nombre LIKE :search
                  )";
        $params[':search'] = "%" . $searchValue . "%";
      }

      $whereClause = count($where) > 0 ? ' WHERE ' . implode(' AND ', $where) : '';

      $whereTotal = [];
      $paramsTotal = [];

      if ($isAlcalde) {
        $whereTotal[] = "proyectos.tbl_municipio_id = :municipio";
        $paramsTotal[':municipio'] = $municipioUsuarioLogueado;
      } elseif ($isSecretario) {
        $whereTotal[] = "proyectos.tbl_secretaria_id = :secretaria";
        $paramsTotal[':secretaria'] = $secretariaUsuarioLogueado;
      }

      $whereTotalClause = count($whereTotal) > 0 ? ' WHERE ' . implode(' AND ', $whereTotal) : '';

      $sqlTotal = "SELECT COUNT(*) $fromClause $whereTotalClause";
      $stmtTotal = $pdo->prepare($sqlTotal);
      $stmtTotal->execute($paramsTotal);
      $recordsTotal = $stmtTotal->fetchColumn();

      $sqlFiltered = "SELECT COUNT(*) $fromClause $whereClause";
      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params);
      $recordsFiltered = $stmtFiltered->fetchColumn();

      $selectClause = "SELECT proyectos.*,
                              departamentos.departamento,
                              municipios.municipio,
                              municipios.codigo_muncipio AS municipio_id,
                              actor.nombre,
                              secretaria.secretaria";
      $orderClause = " ORDER BY proyectos.dtcreate DESC";
      $limitClause = " LIMIT :start, :length";

      $sqlData = "$selectClause $fromClause $whereClause $orderClause $limitClause";

      $stmt = $pdo->prepare($sqlData);

      foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
      }
      $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
      $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);

      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
        $contenido = '';

        if (!empty($row['archivo'])) {
          $contenido .= '<img src="' . htmlspecialchars($row['archivo'], ENT_QUOTES, 'UTF-8') . '" width="40" height="40" class="rounded-circle" />';
        }

        if (!empty($row['pdf'])) {
          $pdf = htmlspecialchars($row['pdf'], ENT_QUOTES, 'UTF-8');
          $contenido .= '<a style="font-size:24px;cursor:pointer;" onclick="mostrarArchivoModal(\'' . $pdf . '\')">Archivo</a>';
        }

        $data[] = [
          'id' => $row['id'],
          'date' => $row['date'],
          'estado' => $row['estado'],
          'fecha_actualizacion' => $row['fecha_leido'],
          'proyecto' => $row['proyecto'],
          'municipio' => $row['municipio'],
          'valor_proyecto' => $row['valor_proyecto'],
          'secretaria' => $row['secretaria'],
          'archivos' => $contenido,
        ];
      }

      // Respuesta JSON final para DataTables
      $arrjson = [
        'draw' => intval($draw),
        'recordsTotal' => intval($recordsTotal),
        'recordsFiltered' => intval($recordsFiltered),
        'data' => $data,
      ];
    } catch (Exception $e) {
      $arrjson = Util::error_general($e->getMessage());
    }

    $db->closeConect();
    return $arrjson;
  }

  public static function editProyecto($data)
  {
    try {

      session_start();
      require_once 'SessionData.php';
      require_once 'DbConection.php';
      require_once 'Util.php';

      $db = new DbConection();
      $pdo = $db->openConect();
      $id = isset($data) ? intval($data) : 0;

      $q = "SELECT
              proyectos.*,
              departamentos.departamento,
              municipios.municipio,
              municipios.codigo_muncipio AS municipio_id,
              actor.nombre,
              secretaria.secretaria 
            FROM
              " . $db->getTable('tbl_ministerios_proyectos') . " AS proyectos
              JOIN " . $db->getTable('tbl_departamentos') . " AS departamentos ON proyectos.tbl_departamento_id = departamentos.codigo_departamento
              JOIN " . $db->getTable('tbl_secretarias') . " AS secretaria ON proyectos.tbl_secretaria_id = secretaria.id
              LEFT JOIN " . $db->getTable('tbl_actores_mapa') . " AS actor ON proyectos.actor_id = actor.id
              JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS municipios ON proyectos.tbl_municipio_id = municipios.codigo_muncipio 
            WHERE
              proyectos.id = :id;";

      $stmt = $pdo->prepare($q);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      $observaciones = [];
      if ($result) {
        $q2 = "SELECT * FROM " . $db->getTable('tbl_ministerios_proyectos_x_observaciones') . " WHERE tbl_proyecto_id = :id ORDER BY id DESC";
        $stmt2 = $pdo->prepare($q2);
        $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt2->execute();
        $observaciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);
      }

      if ($result) {
        $result['observaciones'] = $observaciones;
        return ['state' => true, 'data' => $result];
      } else {
        return ['state' => false, 'error' => 'Proyecto no encontrado'];
      }
    } catch (PDOException $th) {
      return
        ['state' => false, 'error' => $th->getMessage()];
    }
  }

  public static function deleteProyecto($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    session_start();
    require_once 'SessionData.php';
    require_once 'DbConection.php';
    require_once 'Util.php';

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($id > 0) {
      $q = "DELETE FROM " . $db->getTable('tbl_ministerios_proyectos') . " WHERE id = :id";
      $stmt = $pdo->prepare($q);
      if ($stmt->execute([':id' => $id])) {
        return array('output' => array('valid' => true, 'response' => 'Proyecto eliminado correctamente.'));
      } else {
        return Util::error_general('Error al eliminar el proyecto.');
      }
    } else {
      return Util::error_general('ID de proyecto no válido.');
    }
  }

  public static function deleteProyectoVersionNormal($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    if ($id <= 0) {
      return Util::error_general('ID de proyecto no válido.');
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    try {
      $pdo->beginTransaction();

      // Eliminar notificaciones relacionadas primero (por integridad referencial)
      $qDeleteNotif = "DELETE FROM " . $db->getTable('tbl_notificaciones_secretaria') . " WHERE tbl_ministerios_proyecto_id = :id";
      $stmtNotif = $pdo->prepare($qDeleteNotif);
      $stmtNotif->execute([':id' => $id]);

      // Eliminar el proyecto
      $qDeleteProyecto = "DELETE FROM " . $db->getTable('tbl_ministerios_proyectos') . " WHERE id = :id";
      $stmtProyecto = $pdo->prepare($qDeleteProyecto);
      $success = $stmtProyecto->execute([':id' => $id]);

      if ($success) {
        $pdo->commit();
        return array('output' => array('valid' => true, 'response' => 'Proyecto eliminado correctamente.'));
      } else {
        $pdo->rollBack();
        return Util::error_general('Error al eliminar el proyecto.');
      }
    } catch (Exception $e) {
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      return Util::error_general('Error al eliminar el proyecto: ' . $e->getMessage());
    } finally {
      $db->closeConect();
    }
  }

  public static function editarInformacionProyecto($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    if ($id <= 0) {
      return Util::error_general('ID de proyecto no válido.');
    }

    // Limpiar y convertir los montos eliminando puntos
    $aporte_municipio = isset($rqst['aporte_municipio']) ? (float) $rqst['aporte_municipio'] : 0;
    $aporte_departamento = isset($rqst['aporte_departamento']) ? (float) $rqst['aporte_departamento'] : 0;
    $aporte_nacion = isset($rqst['aporte_nacion']) ? (float) $rqst['aporte_nacion'] : 0;
    $otro_aportes = isset($rqst['otro_aportes']) ? (float) $rqst['otro_aportes'] : 0;

    // Calcular valor_proyecto
    $valor_proyecto = floatval($aporte_nacion) + floatval($aporte_departamento) + floatval($otro_aportes) + floatval($aporte_municipio);

    // Limpiar otros campos
    $observaciones = isset($rqst['observaciones']) ? trim($rqst['observaciones']) : '';
    $secretaria = isset($rqst['secretaria']) ? intval($rqst['secretaria']) : 0;
    $actores_id = isset($rqst['actor_id']) ? intval($rqst['actor_id']) : 0;

    $tbl_usuario_id = intval($_SESSION['session_user']['id']);
    $userType = SessionData::getUserType();

    $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob());
    $isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

    $db = new DbConection();
    $pdo = $db->openConect();

    try {

    if ($secretaria == 0) {
      throw new Exception('La secretaría es un campo obligatorio.');
    }

    if ($otro_aportes > 0 && $actores_id == 0) {
      throw new Exception('Por favor, seleccione el actor correspondiente a otros aportes.');
    }

      $q = "UPDATE " . $db->getTable('tbl_ministerios_proyectos') . "
              SET 
                aporte_nacion = :aporte_nacion,
                aporte_departamento = :aporte_departamento,
                aporte_municipio = :aporte_municipio,
                otro_aportes = :otro_aportes,
                valor_proyecto = :valor_proyecto,
                observaciones = :observaciones,
                tbl_secretaria_id = :secretaria,
                actor_id = :actor_id
              WHERE id = :id";

      $stmt = $pdo->prepare($q);

      $stmt->execute([
        ':aporte_nacion' => $aporte_nacion,
        ':aporte_departamento' => $aporte_departamento,
        ':aporte_municipio' => $aporte_municipio,
        ':otro_aportes' => $otro_aportes,
        ':valor_proyecto' => $valor_proyecto,
        ':observaciones' => $observaciones,
        ':secretaria' => $secretaria,
        ':actor_id' => $actores_id,
        ':id' => $id
      ]);


      // Ingreso de observaciones

      if (!empty($observaciones)) {
        $qInsert = "INSERT INTO " . $db->getTable('tbl_ministerios_proyectos_x_observaciones') . " 
          (dtcreate, tbl_proyecto_id, observaciones, tbl_usuario_id) 
          VALUES (" . Util::date_now_server() . ", :tbl_proyecto_id, :observaciones, :tbl_usuario_id)";
        $stmtInsert = $pdo->prepare($qInsert);
        $paramsInsert = [
          ':tbl_proyecto_id' => $id,
          ':observaciones' => $observaciones,
          ':tbl_usuario_id' => $tbl_usuario_id
        ];
        if (!$stmtInsert->execute($paramsInsert)) {
          return Util::error_general('Error al guardar las observaciones del proyecto.');
        }
      }

      // Solo secretario/auxiliar marca como leído
      $leido = 'No leído';
      if ($isAlcalde) {
        $leido = 'Actualizado por alcaldía';
      } elseif ($isSecretario) {
        $leido = 'Actualizado por secretaria';
      } elseif ($isAdmin) {
        $leido = 'Leído';
      }

      if ($isAlcalde || $isSecretario || $isAdmin) {
        $updateProyecto = "UPDATE " . $db->getTable('tbl_ministerios_proyectos') . " SET leido = 'si', estado = :estado, fecha_leido = NOW(), tbl_usuario_id_leido = " . $tbl_usuario_id . " WHERE id = :id";
        $pdo->prepare($updateProyecto)->execute([':id' => $id, ':estado' => $leido]);


        // Notificación a la secretaría al registrar un nuevo proyecto
          $nombre_secretaria = '';
          $query = "SELECT secretaria FROM " . $db->getTable('tbl_secretarias') . " WHERE id = :id";
          $stmt = $pdo->prepare($query);
          if ($stmt->execute([':id' => $secretaria])) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
              $nombre_secretaria = $row['secretaria'];
            }
          }

        $updateNotif = "UPDATE " . $db->getTable('tbl_notificaciones_secretaria') . " 
        SET leido = 'si', tbl_secretaria_id = $secretaria , nombre_secretria = '$nombre_secretaria',
        titulo = 'Proyecto Actualizado'
        WHERE tbl_ministerios_proyecto_id = :id";
        $pdo->prepare($updateNotif)->execute([':id' => $id]);
      }

      return array('output' => array('valid' => true, 'message' => 'Proyecto actualizado correctamente.'));
    } catch (Exception $e) {
      return Util::error_general('Error actualizando proyecto: ' . $e->getMessage());
    }
  }
}
