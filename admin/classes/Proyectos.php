<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Proyectos
{

  public static function getAll($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();
    $observaciones = [];
    $resultados = [];

    try {
      if ($id > 0) {
        // Consulta específica por ID
        $query = "
                SELECT 
                    p.*, 
                    c.municipio, 
                    s.secretaria
                FROM " . $db->getTable('tbl_proyectos') . " p
                INNER JOIN " . $db->getTable('tbl_ciudades') . " c 
                    ON p.tbl_municipio_id = c.codigo_muncipio
                INNER JOIN " . $db->getTable('tbl_secretarias') . " s 
                    ON p.tbl_secretarias_id = s.id
                WHERE p.id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $id]);

        // Obtener observaciones para el proyecto
        $observaciones = Proyectos::getObservacionesByProyectoId($id);
      } elseif ($tbl_secretarias_id > 0) {
        // Consulta por secretaría
        $query = "
                SELECT 
                    p.*, 
                    s.secretaria as secretarias
                FROM " . $db->getTable('tbl_proyectos') . " p
                INNER JOIN " . $db->getTable('tbl_secretarias') . " s 
                    ON p.tbl_secretarias_id = s.id
                WHERE p.tbl_secretarias_id = :tbl_secretarias_id";

        $stmt = $pdo->prepare($query);
        $stmt->execute([':tbl_secretarias_id' => $tbl_secretarias_id]);
      } else {
        // Consulta general
        $query = "SELECT * FROM " . $db->getTable('tbl_proyectos');
        $stmt = $pdo->query($query);
      }

      // Obtener resultados
      $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Crear respuesta
      if (!empty($resultados)) {
        return [
          'output' => [
            'valid' => true,
            'response' => $resultados,
            'observaciones' => $observaciones
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



  public static function getAllobser($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_proyectos.estado, tbl_proyectos_x_observaciones.observaciones, tbl_proyectos.dtcreate, tbl_proyectos.id
    FROM " . $db->getTable('tbl_proyectos') . " 
    INNER JOIN " . $db->getTable('tbl_proyectos_x_observaciones') . "  ON tbl_proyectos.id = tbl_proyectos_x_observaciones.tbl_proyecto_id
    GROUP BY tbl_proyectos.estado, tbl_proyectos_x_observaciones.observaciones, tbl_proyectos.dtcreate";


    if ($id > 0) {

      $q = "SELECT tbl_proyectos.estado, tbl_proyectos_x_observaciones.observaciones, tbl_proyectos.dtcreate, tbl_proyectos.id
      FROM " . $db->getTable('tbl_proyectos') . " 
      INNER JOIN " . $db->getTable('tbl_proyectos_x_observaciones') . "  ON tbl_proyectos.id = tbl_proyectos_x_observaciones.tbl_proyecto_id
      GROUP BY tbl_proyectos.estado, tbl_proyectos_x_observaciones.observaciones, tbl_proyectos.dtcreate
      WHERE tbl_proyectos.id = " . $id;
    }

    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {
        $arr[] = $valor;
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }


  public static function getAllobserAlcaldia($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_proyectos.estado, tbl_proyectos_x_observaciones.observaciones, tbl_proyectos.dtcreate, tbl_proyectos.id
    FROM " . $db->getTable('tbl_proyectos') . " 
    INNER JOIN " . $db->getTable('tbl_proyectos_x_observaciones') . "  ON tbl_proyectos.id = tbl_proyectos_x_observaciones.tbl_proyecto_id
    GROUP BY tbl_proyectos.estado, tbl_proyectos_x_observaciones.observaciones, tbl_proyectos.dtcreate";


    if ($id > 0) {

      $q = "SELECT tbl_proyectos.estado, tbl_proyectos_x_observaciones.observaciones, tbl_proyectos.dtcreate, tbl_proyectos.id
      FROM " . $db->getTable('tbl_proyectos') . " 
      INNER JOIN " . $db->getTable('tbl_proyectos_x_observaciones') . "  ON tbl_proyectos.id = tbl_proyectos_x_observaciones.tbl_proyecto_id
      GROUP BY tbl_proyectos.estado, tbl_proyectos_x_observaciones.observaciones, tbl_proyectos.dtcreate
      WHERE tbl_proyectos.id = " . $id;
    }

    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {
        $arr[] = $valor;
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }


  public static function getAllobservacion($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();
    $observaciones = array();

    $q = "SELECT * FROM " . $db->getTable('tbl_proyectos');
    if ($id > 0) {
      $q = "SELECT tbl_proyectos.*, tbl_secretarias.secretaria as secretaria 
      FROM " . $db->getTable('tbl_proyectos') . "," . $db->getTable('tbl_secretarias') . " 
      WHERE tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id AND
      tbl_proyectos.id = " . $id;

      $observaciones = Proyectos::getObservacionesByProyectoId($id);
    } elseif ($tbl_secretarias_id > 0) {
      $q = "SELECT tbl_proyectos.*, tbl_secretarias.secretaria as secretarias 
      FROM " . $db->getTable('tbl_proyectos') . "," . $db->getTable('tbl_secretarias') . " 
      WHERE tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id AND
      tbl_proyectos.tbl_secretarias_id = " . $tbl_secretarias_id;
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

      $q = "SELECT * FROM " . $db->getTable('tbl_proyectos_x_observaciones') . " WHERE tbl_proyecto_id = " . $id;
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


  public static function getInversiontotal($rqst)
  {
    $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();


    $q = "SELECT tbl_proyectos.tbl_municipio_id, Sum(tbl_proyectos.valor_proyecto) AS SumaDevalor_proyecto
    FROM  " . $db->getTable('tbl_proyectos') . " 
     WHERE tbl_proyectos.tbl_municipio_id = " . $mun . "
    GROUP BY tbl_proyectos.tbl_municipio_id";

    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {
        $arr[] = $valor;
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }







  public static function getInversionBySecre($rqst)
  {
    $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT secretaria, SUM(tbl_proyectos.valor_proyecto) total FROM " . $db->getTable('tbl_secretarias') . "
    LEFT JOIN " . $db->getTable('tbl_proyectos') . " ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
    WHERE tbl_proyectos.tbl_municipio_id = " . $mun . "
    GROUP BY tbl_secretarias.id";

    $result = $pdo->query($q);
    $arrpro = array();
    $labels = [];
    $data = [];
    if ($result) {

      foreach ($result as $valor) {
        $labels[] = $valor["secretaria"];
        $data[] = intval($valor["total"]);
      }
    }

    $arrjson = array('output' => array('valid' => true, 'response' => compact("labels", "data")));
    $db->closeConect();
    return $arrjson;
  }

  public static function getAllproyectosxsecre($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $mun = isset($rqst['mun']) ? intval($rqst['mun']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_ciudades_accion_unificada.municipio, tbl_proyectos.porcentaje_financiero, tbl_proyectos.id,tbl_secretarias.secretaria, tbl_proyectos.tbl_secretarias_id, tbl_proyectos.valor_proyecto, tbl_proyectos.proyecto, tbl_proyectos.porcentaje_ejecucion, tbl_proyectos.fecha_entrega, tbl_proyectos.estado
    FROM " . $db->getTable('tbl_proyectos') . "
    INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
    INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
    GROUP BY tbl_secretarias.secretaria, tbl_proyectos.valor_proyecto, tbl_proyectos.proyecto, tbl_proyectos.porcentaje_ejecucion, tbl_proyectos.fecha_entrega";


    if ($id > 0) {
      $q = "SELECT tbl_ciudades_accion_unificada.municipio, tbl_proyectos.porcentaje_financiero, tbl_proyectos.id,tbl_secretarias.secretaria, tbl_proyectos.tbl_secretarias_id, tbl_proyectos.valor_proyecto, tbl_proyectos.proyecto, tbl_proyectos.porcentaje_ejecucion, tbl_proyectos.fecha_entrega, tbl_proyectos.estado
      FROM " . $db->getTable('tbl_proyectos') . "
      INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
      INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
      WHERE tbl_secretarias.id = " . $id . "
      GROUP BY tbl_secretarias.secretaria, tbl_proyectos.valor_proyecto, tbl_proyectos.proyecto, tbl_proyectos.porcentaje_ejecucion, tbl_proyectos.fecha_entrega";
    }

    if ($mun > 0) {
      $q = "SELECT tbl_proyectos.id,tbl_secretarias.secretaria, tbl_proyectos.tbl_secretarias_id, tbl_proyectos.valor_proyecto, tbl_proyectos.proyecto, tbl_proyectos.porcentaje_ejecucion, tbl_proyectos.fecha_entrega, tbl_proyectos.estado
      FROM " . $db->getTable('tbl_proyectos') . "
      INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
      WHERE tbl_proyectos.tbl_municipio_id = " . $mun . "
      GROUP BY tbl_secretarias.secretaria, tbl_proyectos.valor_proyecto, tbl_proyectos.proyecto, tbl_proyectos.porcentaje_ejecucion, tbl_proyectos.fecha_entrega";
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


  public static function getProyectosPorsecretarias($rqst)
  {

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_secretarias.secretaria, Count(tbl_proyectos.proyecto) AS CuentaDeproyecto, tbl_proyectos.valor_proyecto AS valor, tbl_proyectos.porcentaje_ejecucion, tbl_secretarias.id, tbl_proyectos.fecha_entrega
      FROM " . $db->getTable('tbl_proyectos') . "
      INNER JOIN " . $db->getTable('tbl_secretarias') . " ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
      GROUP BY tbl_secretarias.secretaria, tbl_proyectos.valor_proyecto, tbl_proyectos.porcentaje_ejecucion, tbl_proyectos.fecha_entrega";
    $result = $pdo->query($q);
    $arr = array();
    $arrTemporal = array();

    if ($result) {
      foreach ($result as $valor) {

        $tbl_secretarias_id = $valor['tbl_secretarias_id'];
        $arrTemporal['tbl_secretarias_id'] = $tbl_secretarias_id;
        $arrTemporal['secretaria'] = $valor['secretaria'];
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
    $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
    $proyecto =  isset($rqst['proyecto']) ? ($rqst['proyecto']) : '';
    $date =  isset($rqst['date']) ? ($rqst['date']) : '';
    $provincia =  isset($rqst['provincia']) ? ($rqst['provincia']) : '';
    $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : 0;
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : [];
    $valor_proyecto = isset($rqst['valor_proyecto']) ? floatval($rqst['valor_proyecto']) : 0;
    $nit = isset($rqst['nit']) && $rqst['nit'] != "" ? ($rqst['nit']) : null;
    $contratista = isset($rqst['contratista']) && $rqst['contratista'] != "" ? ($rqst['contratista']) : null;
    $interventoria = isset($rqst['interventoria']) && $rqst['interventoria'] != ""  ? ($rqst['interventoria']) : null;
    $adicion = isset($rqst['adicion']) && $rqst['adicion'] != "" ? ($rqst['adicion']) : null;
    $date_inicio = isset($rqst['date_inicio']) && $rqst['date_inicio'] != "" ? ($rqst['date_inicio']) : null;
    $dias_prorroga = isset($rqst['dias_prorroga']) && $rqst['dias_prorroga'] != "" ? ($rqst['dias_prorroga']) : null;
    $date_prorroga = isset($rqst['date_prorroga']) && $rqst['date_prorroga'] != "" ? ($rqst['date_prorroga']) : null;
    $plazo_construccion = isset($rqst['plazo_construccion']) && $rqst['plazo_construccion'] != "" ? ($rqst['plazo_construccion']) : null;
    $fecha_entrega = isset($rqst['fecha_entrega']) && $rqst['fecha_entrega'] != "" ? ($rqst['fecha_entrega']) : null;
    $estado = isset($rqst['estado']) && $rqst['estado'] != "" ? ($rqst['estado']) : null;
    $porcentaje_ejecucion = isset($rqst['porcentaje_ejecucion']) && $rqst['porcentaje_ejecucion'] != "" ? ($rqst['porcentaje_ejecucion']) : null;
    $observaciones = isset($rqst['observaciones']) && $rqst['observaciones'] != "" ? ($rqst['observaciones']) : null;
    $tbl_usuario_id =  intval($_SESSION['session_user']['id']);
    $entidad_otros = isset($rqst['entidad_otros']) && $rqst['entidad_otros'] != "" ? ($rqst['entidad_otros']) : null;
    $otros_aportes = isset($rqst['otros_aportes']) && $rqst['otros_aportes'] != "" ? ($rqst['otros_aportes']) : null;

    $aporte_municipio = isset($rqst['aporte_municipio']) ? floatval($rqst['aporte_municipio']) : 0;
    $aporte_gobernacion = isset($rqst['aporte_gobernacion']) ? floatval($rqst['aporte_gobernacion']) : 0;
    $aporte_nacion = isset($rqst['aporte_nacion']) ? floatval($rqst['aporte_nacion']) : 0;
    $porcentaje_financiero = isset($rqst['porcentaje_financiero']) ? floatval($rqst['porcentaje_financiero']) : 0;

    $tipoUsuario = SessionData::getUserType();
    $codigoMunicipio = SessionData::getCodigoMunicipio();

    // Si es alcalde o auxiliar de alcalde
    $mensaje = "Debe seleccionar el municipio correspondiente al cual pertenece.";
    foreach ($tbl_municipio_id as $key => $muni_id) {
      if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario || Util::Secretario_Despacho() == $tipoUsuario) {
        if (intval($muni_id) !== intval($codigoMunicipio)) {
          return Util::error_general($mensaje);
        }
      }
    }

    if (Util::Secretario_Despacho() == $tipoUsuario) {
      if (SessionData::getSecretaria()  !== $tbl_secretarias_id) {
        return Util::error_general('Debe ingresar solo información de la secretaria o despacho al que pertenece.');
      }
    }

    if($id == 0 && $tbl_secretarias_id == 0){
      return Util::error_general('Debe seleccionar la secretaria o despacho al que pertenece.');
    }

    if($aporte_municipio == 0 || $aporte_gobernacion == 0 || $aporte_nacion == 0){
        return Util::error_general('Debe ingresar un aporte financiero, de municipio, gobernación y nación.');
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($id > 0) {

      //actualiza la informacion
      $q = "SELECT id FROM " . $db->getTable('tbl_proyectos') . " WHERE id = " . $id;
      $result = $pdo->query($q);
      if ($result) {
        $table = $db->getTable('tbl_proyectos');
        $arrfieldscomma = array(
          'proyecto' => $proyecto,
          // 'valor_proyecto' => $valor_proyecto,
          'plazo_construccion' => $plazo_construccion,
          'fecha_entrega' => $fecha_entrega,
          'estado' => $estado,
          'porcentaje_ejecucion' => $porcentaje_ejecucion,
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
          'porcentaje_financiero' => $porcentaje_financiero
        );

        $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
        $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);

        $result = $pdo->query($q);
        if (!$result) {
          $arrjson = Util::error_general(' Al actualizar los datos de proyectos....');
        } else {
          // Verifica si hay información en 'observaciones'
          if (!empty($observaciones)) {
            $qInsert = "INSERT INTO " . $db->getTable('tbl_proyectos_x_observaciones') . " (dtcreate, tbl_proyecto_id, observaciones, tbl_usuario_id) VALUES ( " . Util::date_now_server() . ", :tbl_proyecto_id, :observaciones, :tbl_usuario_id)";
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
          } else {
            // Si no hay observaciones, se retorna como éxito sin insertar nada adicional
            $arrjson = array('output' => array('valid' => true, 'id' => $id));
          }
        }
      } else {
        $arrjson = Util::error_general();
      }
    } else {
      if ($proyecto != "" && is_array($tbl_municipio_id) && !empty($tbl_municipio_id) && $date_inicio != "" && $fecha_entrega != "") {

        foreach ($tbl_municipio_id as $key => $muni_id) {
          $q = "INSERT INTO " . $db->getTable('tbl_proyectos') . " (dtcreate, tbl_secretarias_id, tbl_departamento_id, provincia, tbl_municipio_id, proyecto, date, otros_aportes, entidad_otros, valor_proyecto, contratista, nit, interventoria, adicion, date_inicio,  dias_prorroga, plazo_construccion, fecha_entrega, estado, date_prorroga, porcentaje_ejecucion, observaciones, tbl_usuario_id, aporte_municipio, aporte_gobernacion, aporte_nacion, porcentaje_financiero)
                VALUES ( " . Util::date_now_server() . ", :tbl_secretarias_id, :tbl_departamento_id, :provincia, :tbl_municipio_id,  :proyecto, :date, :otros_aportes, :entidad_otros, :valor_proyecto,  :contratista, :nit, :interventoria, :adicion, :date_inicio, :dias_prorroga, :plazo_construccion, :fecha_entrega, :estado, :date_prorroga, :porcentaje_ejecucion, :observaciones, :tbl_usuario_id, :aporte_municipio, :aporte_gobernacion, :aporte_nacion, :porcentaje_financiero)";
          $result = $pdo->prepare($q);
          $arrparam = array(
            ':tbl_secretarias_id' => $tbl_secretarias_id,
            ':proyecto' => $proyecto,
            ':date' => $date,
            ':provincia' => $provincia,
            ':tbl_departamento_id' => $tbl_departamento_id,
            ':tbl_municipio_id' => $muni_id,
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
            ':observaciones' => $observaciones,
            ':tbl_usuario_id' => $tbl_usuario_id,

            ':aporte_municipio' => $aporte_municipio,
            ':aporte_gobernacion' => $aporte_gobernacion,
            ':aporte_nacion' => $aporte_nacion,
            ':porcentaje_financiero' => $porcentaje_financiero
          );
          if ($result->execute($arrparam)) {
            $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
          } else {
            $arrjson = Util::error_general(' Al guardar los datos de proyectos');
          }
        }
      } else {
        $arrjson = Util::error_missing_data();
      }
    }
    $db->closeConect();
    return $arrjson;
  }
}
