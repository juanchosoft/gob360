<?php
date_default_timezone_set('America/Bogota');
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Votaciones
{

  public function __construct()
  {
  }



  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_votaciones.*, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon
          FROM (" . $db->getTable('tbl_votaciones') . " INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_votaciones.tbl_brigada_id = tbl_brigadas.id) 
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_votaciones.tbl_batallon_id = tbl_batallones.id";


    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_votaciones') . " WHERE id = " . $id;
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

    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
    $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : 0;
    $tbl_vereda_id = isset($rqst['vereda']) ? ($rqst['vereda']) : '';
    $habilitada_para_votar = isset($rqst['habilitada_para_votar']) ? ($rqst['habilitada_para_votar']) : '';

    if ($codigo_departamento != "" && $codigo_muncipio != ""  && $tbl_vereda_id != "") {

      $db = new DbConection();
      $pdo = $db->openConect();

      $q01 = "UPDATE  " . $db->getTable('tbl_vereda') . "  SET habilitada_para_votar = '$habilitada_para_votar'
        WHERE id = $tbl_vereda_id AND departamento_id = '$codigo_departamento'  AND municipio_id = '$codigo_muncipio' ";
      $result = $pdo->query($q01);
      if ($result) {
        $arrjson = array('output' => array('valid' => true));
      } else {
        $arrjson = Util::error_no_result();
      }
      $db->closeConect();
      return $arrjson;
    } else {
      return Util::error_no_result();
    }
  }

  public static function update($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $oficiales = isset($rqst['oficiales']) ? intval($rqst['oficiales']) : 0;
    $suboficiales = isset($rqst['suboficiales']) ? intval($rqst['suboficiales']) : 0;
    $soldados = isset($rqst['soldados']) ? intval($rqst['soldados']) : 0;
    $soldados18 = isset($rqst['soldados18']) ? intval($rqst['soldados18']) : 0;
    $total = $oficiales + $suboficiales + $soldados+ $soldados18;
    $indicativo = isset($rqst['indicativo']) ? ($rqst['indicativo']) : '';
    $tiempo_ocupacion = isset($rqst['tiempo_ocupacion']) ? ($rqst['tiempo_ocupacion']) : '';
    $telefono = isset($rqst['telefono']) ? ($rqst['telefono']) : '';
    $comandante = isset($rqst['comandante']) ? ($rqst['comandante']) : '';
    $helicoportado = isset($rqst['helicoportado']) ? ($rqst['helicoportado']) : '';
    $compania = isset($rqst['compania']) ? ($rqst['compania']) : '';
    $peloton = isset($rqst['peloton']) ? ($rqst['peloton']) : '';
    $cubierto = isset($rqst['cubierto']) ? ($rqst['cubierto']) : '';
    $horas_vuelo = isset($rqst['horas_vuelo']) ? ($rqst['horas_vuelo']) : '';
    $observaciones = isset($rqst['observaciones']) ? ($rqst['observaciones']) : '';
    $mixto = isset($rqst['mixto']) ? ($rqst['mixto']) : 'no';
    $desplazamiento = isset($rqst['desplazamiento']) ? ($rqst['desplazamiento']) : '';
    $latitudu = isset($rqst['latitudu']) ? ($rqst['latitudu']) :'';
    $c1u = isset($rqst['c1u']) ? ($rqst['c1u']) :'';
    $c2u = isset($rqst['c2u']) ? ($rqst['c2u']) :'';
    $c3u = isset($rqst['c3u']) ? ($rqst['c3u']) :'';
    $longitudu = isset($rqst['longitudu']) ? ($rqst['longitudu']) : '';
    $c4u = isset($rqst['c4u']) ? ($rqst['c4u']) :'';
    $c5u = isset($rqst['c5u']) ? ($rqst['c5u']) :'';
    $c6u = isset($rqst['c6u']) ? ($rqst['c6u']) :'';
    $seccion = isset($rqst['seccion']) ? ($rqst['seccion']) :'';
    $escuadra = isset($rqst['escuadra']) ? ($rqst['escuadra']) :'';
    $reserva = isset($rqst['reserva']) ? ($rqst['reserva']) : '';
    $grado = isset($rqst['grado']) ? ($rqst['grado']) : '';

    if ($id > 0) {

      $db = new DbConection();
      $pdo = $db->openConect();
      $q = "SELECT * FROM " . $db->getTable('tbl_votaciones') . " WHERE id = " . $id;


      $result = $pdo->query($q);
      $tbl_batallon_id = 0;
      if ($result) {
        foreach ($result as $valor) {
          $tbl_batallon_id = intval($valor['tbl_batallon_id']);
        }
      }

      // if (intval($_SESSION['session_user']['tbl_batallon_id']) != $tbl_batallon_id) {
      //   $db->closeConect();
      //   return Util::error_general("Su Batallón no corresponde a la información que va a ingresar de votaciones");
      // }

      //actualiza la informacion
      $q0 = "SELECT * FROM " . $db->getTable('tbl_votaciones') . " WHERE id = " . $id;
      $result0 = $pdo->query($q0);
      if ($result0) {
        $table = $db->getTable('tbl_votaciones');
        $arrfieldscomma = array(
          'oficiales' => $oficiales,
          'suboficiales' => $suboficiales,
          'soldados' => $soldados,
          'indicativo' => $indicativo,
          'tiempo_ocupacion' => $tiempo_ocupacion,
          'comandante' => $comandante,
          'telefono' => $telefono,
          'total' => $total,
          'helicoportado' => $helicoportado,
          'horas_vuelo' => $horas_vuelo,
          'observaciones' => $observaciones,
          'mixto' => $mixto,
          'desplazamiento' => $desplazamiento,
          'compania' => $compania,
          'peloton' => $peloton,
          'seccion' => $seccion,
          'escuadra' => $escuadra,
          'soldados18' => $soldados18,
          'reserva' => $reserva,
          'grado' => $grado,
          'cubierto' => $cubierto,
          'latitudu' => $latitudu,
          'c1u' => $c1u,
          'c2u' => $c2u,
          'c3u' => $c3u,
          'longitudu' => $longitudu,
          'c4u' => $c4u,
          'c5u' => $c5u,
          'c6u' => $c6u,
        );
        $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
        $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
        $result = $pdo->query($q);
        if ($result) {
          $arrjson = array('output' => array('valid' => true));
        } else {
          $arrjson = Util::error_general('Actualizando al información de votación');
        }
        $db->closeConect();
        return $arrjson;
      }
    } else {
      return Util::error_no_result();
    }
  }

  public static function getVeredasSinDesplazamiento($rqst)
  {
    
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : "";

    $db = new DbConection();
    $pdo = $db->openConect();



    if ($id > 0) {

      $totalVeredas = 0;
      $promedio = 0;

      if ($tipo == "desplazamiento") {
        // Se consulta la información del estado
        $q = "SELECT  tbl_batallones.id, tbl_brigadas.sigla AS brigada, tbl_votaciones.tiempo_ocupacion, tbl_votaciones.nombre_puesto, tbl_batallones.sigla AS batallon,
        tbl_votaciones.desplazamiento
        FROM (" . $db->getTable('tbl_votaciones') . "  INNER JOIN " . $db->getTable('tbl_batallones') . "   ON tbl_votaciones.tbl_batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_votaciones.tbl_brigada_id = tbl_brigadas.id
        WHERE  tbl_batallones.id = " . $id;
      }

      if ($tipo == "ocupacion") {
        // Se consulta la información del estado
        $q = "SELECT  tbl_batallones.id, tbl_brigadas.sigla AS brigada, tbl_votaciones.tiempo_ocupacion, tbl_votaciones.nombre_puesto, tbl_batallones.sigla AS batallon,
        tbl_votaciones.desplazamiento
        FROM (" . $db->getTable('tbl_votaciones') . "  INNER JOIN " . $db->getTable('tbl_batallones') . "   ON tbl_votaciones.tbl_batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_votaciones.tbl_brigada_id = tbl_brigadas.id
        WHERE  tbl_batallones.id = " . $id . " AND tbl_votaciones.tiempo_ocupacion IS NULL ";
      }
      $result = $pdo->query($q);
      $arr =  array();
      $arrContador =  array();
      $sumaPorcentajes = 0;
      $promedioAMostrar = 0;
      if ($result) {
        foreach ($result as $value) {

          $arrContador[] = $value;

          $arrayTemp = array();
          $arrayTemp['id'] = $value['id'];
          $arrayTemp['brigada'] = $value['brigada'];
          $arrayTemp['tiempo_ocupacion'] = $value['tiempo_ocupacion'];
          $arrayTemp['nombre_puesto'] = $value['nombre_puesto'];
          $arrayTemp['batallon'] = $value['batallon'];
          $arrayTemp['desplazamiento'] = $value['desplazamiento'];

          $tiempoDuracion = 0;
          $diasEntreDosFecha = 0;
          $porcentaje = 0;
          if ($tipo == "desplazamiento" && $value['desplazamiento'] == 'si') {
              $tiempoDuracion = intval( $value['tiempo_ocupacion'] ); //Valor ejemplo 1, 4, 5
              $newDate = date('Y-m-d', strtotime(' - ' . $tiempoDuracion . ' days'));
              $diasEntreDosFecha = Util::getDiasEntreDosFechas(Util::fechaVotacion(), $newDate); // 2022-03-10 - 30
              $porcentaje = ( $tiempoDuracion / $diasEntreDosFecha ) * 100;   
              $sumaPorcentajes += $porcentaje;    
          }

          $cantidadPuestos = count($arrContador);
          if($sumaPorcentajes > 0){
            $promedioAMostrar = ($sumaPorcentajes / $cantidadPuestos) ;
          }

          $arrayTemp['dias'] = $diasEntreDosFecha;
          $arrayTemp['porcentaje'] = round($porcentaje);
          $arrayTemp['cantidadPuesto'] = $cantidadPuestos;
          $arr[] = $arrayTemp;
        }
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'promedioAMostrar' => round($promedioAMostrar, 2)));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

  public static function getVeredasSinDesplazamientoBrigada($rqst)
  {
    
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : "";

    $db = new DbConection();
    $pdo = $db->openConect();



    if ($id > 0) {

      $totalVeredas = 0;
      $promedio = 0;

      if ($tipo == "desplazamiento") {
        // Se consulta la información del estado
        $q = "SELECT  tbl_brigadas.id, tbl_brigadas.sigla AS brigada, tbl_votaciones.tiempo_ocupacion, tbl_votaciones.nombre_puesto, tbl_batallones.sigla AS batallon,
        tbl_votaciones.desplazamiento
        FROM (" . $db->getTable('tbl_votaciones') . "  INNER JOIN " . $db->getTable('tbl_batallones') . "   ON tbl_votaciones.tbl_batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_votaciones.tbl_brigada_id = tbl_brigadas.id
        WHERE  tbl_brigadas.id = " . $id;
      }
      $result = $pdo->query($q);
      $arr =  array();
      $arrContador =  array();
      $sumaPorcentajes = 0;
      $promedioAMostrar = 0;
      if ($result) {
        foreach ($result as $value) {

          $arrContador[] = $value;

          $arrayTemp = array();
          $arrayTemp['id'] = $value['id'];
          $arrayTemp['brigada'] = $value['brigada'];
          $arrayTemp['tiempo_ocupacion'] = $value['tiempo_ocupacion'];
          $arrayTemp['nombre_puesto'] = $value['nombre_puesto'];
          $arrayTemp['batallon'] = $value['batallon'];
          $arrayTemp['desplazamiento'] = $value['desplazamiento'];

          $tiempoDuracion = 0;
          $diasEntreDosFecha = 0;
          $porcentaje = 0;
          if ($tipo == "desplazamiento" && $value['desplazamiento'] == 'si') {
              $tiempoDuracion = intval( $value['tiempo_ocupacion'] ); //Valor ejemplo 1, 4, 5
              $newDate = date('Y-m-d', strtotime(' - ' . $tiempoDuracion . ' days'));
              $diasEntreDosFecha = Util::getDiasEntreDosFechas(Util::fechaVotacion(), $newDate); // 2022-03-10 - 30
              $porcentaje = ( $tiempoDuracion / $diasEntreDosFecha ) * 100;   
              $sumaPorcentajes += $porcentaje;    
          }

          $cantidadPuestos = count($arrContador);
          if($sumaPorcentajes > 0){
            $promedioAMostrar = ($sumaPorcentajes / $cantidadPuestos) ;
          }

          $arrayTemp['dias'] = $diasEntreDosFecha;
          $arrayTemp['porcentaje'] = round($porcentaje);
          $arrayTemp['cantidadPuesto'] = $cantidadPuestos;
          $arr[] = $arrayTemp;
        }
      }
      $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'promedioAMostrar' => round($promedioAMostrar, 2)));
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

}
