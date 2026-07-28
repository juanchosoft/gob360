<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Filtros
{

  public function __construct()
  {
  }

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT * FROM " . $db->getTable('filtros') . " ORDER BY id DESC";
    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('filtros') . " WHERE id = " . $id;
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

  public static function save($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $nombre_filtro = isset($rqst['nombre_filtro']) ? ($rqst['nombre_filtro']) : '';
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';
    $tbl_departamento_id =  isset($rqst['tbl_departamento_id']) ? ($rqst['tbl_departamento_id']) : '';
    $tbl_municipio_id =  isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : 0;
    $tbl_vereda_id =  isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : 0;
    $tbl_usuario_id =  $_SESSION['session_user']['id'];

    $tbl_departamento_id = intval($tbl_departamento_id);
    $tbl_municipio_id = intval($tbl_municipio_id);
    $tbl_vereda_id = intval($tbl_vereda_id);


    if ($tipo == 'Departamento' && $tbl_departamento_id == "seleccione") {
      return Util::error_general('Debe selecionar un departamento');
    }
    if ($tipo == 'Municipio' && $tbl_departamento_id < 0 || $tbl_municipio_id < 0) {
      return Util::error_general('Debe selecionar un departamento y7o municipio');
    }

    if ($tipo == 'Vereda' && $tbl_departamento_id < 0 || $tbl_municipio_id < 0 || $tbl_vereda_id < 0) {
      return Util::error_general('Debe selecionar un departamento, municipio y/o vereda');
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT id, codigo_departamento, codigo_muncipio 
    FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_muncipio = " . $tbl_municipio_id;
    $result = $pdo->query($q);
    if ($result) {
      foreach ($result as $valor) {
        $tbl_municipio_id = $valor['id']; // Remplazamos el codigo del municpio y ponemos el Id
      }
    }

    $q0 = "SELECT id FROM " . $db->getTable('tbl_departamentos') . " WHERE codigo_departamento = " . $tbl_departamento_id;
    $result0 = $pdo->query($q0);
    if ($result0) {
      foreach ($result0 as $valor0) {
        $tbl_departamento_id = intval($valor0['id']); // Remplazamos el codigo del departamento y ponemos el Id
      }
    }

    if ($tipo == 'Departamento') {
      $tbl_municipio_id = 0;
      $tbl_vereda_id = 0;
    }

    if ($id > 0) {
      $q = "SELECT id FROM " . $db->getTable('filtros') . " WHERE id = " . $id;
      $result = $pdo->query($q);
      if ($result) {
        $table = $db->getTable('filtros');
        $arrfieldscomma = array(
          'tipo' => $tipo,
          'nombre_filtro' => $nombre_filtro,
          'tbl_departamento_id' => $tbl_departamento_id,
          'tbl_municipio_id' => $tbl_municipio_id,
          'tbl_usuario_id' => $tbl_usuario_id
        );
        $arrfieldsnocomma = array('dtcreate_at' => Util::date_now_server());
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
      if ($nombre_filtro != "") {
        $q = "INSERT INTO " . $db->getTable('filtros') . " (dtcreate_at, tipo, nombre_filtro, tbl_departamento_id, tbl_municipio_id, tbl_usuario_id) VALUES ( " . Util::date_now_server() . ", :tipo, :nombre_filtro, :tbl_departamento_id, :tbl_municipio_id, :tbl_usuario_id)";
        $result = $pdo->prepare($q);
        $arrparam = array(
          ':tipo' => $tipo,
          ':nombre_filtro' => $nombre_filtro,
          ':tbl_departamento_id' => $tbl_departamento_id,
          ':tbl_municipio_id' => $tbl_municipio_id,
          ':tbl_usuario_id' => $tbl_usuario_id,
        );
        if ($result->execute($arrparam)) {
          $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
        } else {
          $arrjson = Util::error_general();
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

    $q = "DELETE FROM " . $db->getTable('filtros') . " WHERE id = " . $id;
    $result = $pdo->query($q);
    if ($result) {
      $arrjson = array('output' => array('valid' => true, 'error' => $pdo->errorInfo()));
    } else {
      $arrjson = Util::error_generaldelete();
    }
    $db->closeConect();
    return $arrjson;
  }

  public static function getPersonasByFiltroId($rqst) {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    
    if ($id < 0) {
      return Util::error_general('Debe enviar un indetificador del filtro');
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT * FROM " . $db->getTable('filtros') . " WHERE id = " . $id;

    $select = "id, nombre, telefono ";
    $queryComentarios = "SELECT '$select' FROM " . $db->getTable('comentarios');

    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {

        $arr[] = $valor;

        $departamento_id = $valor['tbl_departamento_id'];
        $municipio_id = $valor['tbl_municipio_id'];
        $vereda_id = $valor['tbl_vereda_id'];

        switch ($valor['tipo']) {

          case 'Departamento':
            $queryComentarios = "SELECT $select FROM " . $db->getTable('comentarios') . " WHERE departamento_id = " . $departamento_id;
            break;

          case 'Municipio':
            $queryComentarios = "SELECT $select FROM " . $db->getTable('comentarios') . " WHERE municipio_id = " . $municipio_id;
            break;

          case 'Vereda':
            $queryComentarios = "SELECT $select FROM " . $db->getTable('comentarios') . " WHERE vereda_id = " . $vereda_id;
            break;
        }
      }

      if(count($arr) == 0){
        $db->closeConect();
        return Util::error_no_result();
      }

      //Ejecutamos el query para mostrar la lista
      $resultComentarios = $pdo->query($queryComentarios);
      $arrPersonas = array();
      if ($resultComentarios) {
        foreach ($resultComentarios as $personas) {
          $arrPersonas[] = $personas;
        } 
      }

      $arrjson = array('output' => array('valid' => true, 'filtro' => $arr, 'response' => $arrPersonas));

    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }
}
