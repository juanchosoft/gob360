<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Puntaje
{

  public function __construct() {}

  public static function calcularPuntajeDepartamento($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : '';

    if ($codigo_departamento != "") {

      $db = new DbConection();
      $pdo = $db->openConect();

      try {

        $q = "SELECT * FROM " . $db->getTable('tbl_departamentos') . " WHERE codigo_departamento = '$codigo_departamento' ";
        $result = $pdo->query($q);
        $arr = array();
        $id = 0;
        $departamento = "";
        if ($result) {
          foreach ($result as $valor) {
            $id = $valor['id'];
            $departamento = $valor['departamento'];
            $arr[] = $valor;
          }
        }

        $step01 = Estado::getCalcularPuntajesVeredas(array('codigo_departamento' =>  $codigo_departamento)); // 1 VEREDA
        $step02 = Estado::getCalcularPuntajesMunicipioByDepartmentoId(array('codigo_departamento' =>  $codigo_departamento));  // 2 MUNICIPIO
        $step03 = EstadoDepartamento::getPuntajeDepartamento(array('departamento_id' => $id, 'codigo_departamento' =>  $codigo_departamento));  // 3 DEPARTAMENTO

        $arrjson = array('output' => array('valid' => true));
        return $arrjson;
      } catch (Exception $e) {
        $arrjson = Util::error_general("Generando de forma manual los puntajes del departmaneto " . $departamento);
      }

      $db->closeConect();
      return $arrjson;
    } else {
      return Util::error_missing_data();
    }
  }
  public static function calcularPuntajeBrigada($rqst)
  {

    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : '';

    if ($tbl_brigada_id > 0) {

      $db = new DbConection();
      $pdo = $db->openConect();

      try {

        $step01 = EstadoBrigada::getPuntajeByBrigadaId(array('tbl_brigada_id' => $tbl_brigada_id));
        $arrjson = array('output' => array('valid' => true));
        return $arrjson;
      } catch (Exception $e) {
        $arrjson = Util::error_general("Generando de forma manual los puntajes de la brigada seleccionada ");
      }

      $db->closeConect();
      return $arrjson;
    } else {
      return Util::error_missing_data();
    }
  }
}
