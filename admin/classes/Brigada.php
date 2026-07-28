<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Brigada
{

  public function __construct()
  {
  }

  public static function getAll($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT * FROM " . $db->getTable('tbl_brigadas');
    if ($id > 0) {
      $q = "SELECT * FROM " . $db->getTable('tbl_brigadas') . " WHERE id = " . $id;
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
    $nombre = isset($rqst['nombre']) ? ($rqst['nombre']) : '';
    $sigla =  isset($rqst['sigla']) ? ($rqst['sigla']) : '';
    $ubicacion = isset($rqst['ubicacion']) ? ($rqst['ubicacion']) : '';
    $direccion = isset($rqst['direccion']) ? ($rqst['direccion']) : '';
    $telefono = isset($rqst['telefono']) ? ($rqst['telefono']) : '';
    $responsable = isset($rqst['responsable']) ? ($rqst['responsable']) : '';
    $email = isset($rqst['email']) ? ($rqst['email']) : '';
    $comandante = isset($rqst['comandante']) ? ($rqst['comandante']) : '';
    // $tbl_usuario_id = isset($rqst['tbl_usuario_id']) ? ($rqst['tbl_usuario_id']) : '';
    $tbl_usuario_id = 1;

    if (!Util::validate_email($email)) {
      return Util::error_general('El email no es correcto');
    }

    $db = new DbConection();
    $pdo = $db->openConect();


    if ($id > 0) {
      //actualiza la informacion
      $q = "SELECT id FROM " . $db->getTable('tbl_brigadas') . " WHERE id = " . $id;
      $result = $pdo->query($q);
      if ($result) {
        $table = $db->getTable('tbl_brigadas');
        $arrfieldscomma = array(
          'nombre' => $nombre,
          'sigla' => $sigla,
          'ubicacion' => $ubicacion,
          'direccion' => $direccion,
          'telefono' => $telefono,
          'responsable' => $responsable,
          'email' => $email,
          'comandante' => $comandante,
          'tbl_usuario_id' => $tbl_usuario_id
        );
        $arrfieldsnocomma = array('dtcreate' => Util::date_now_server());
        $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
        $result = $pdo->query($q);
        if (!$result) {
          $arrjson = Util::error_general('Actualizando los datos de brigada');
        } else {
          $arrjson = array('output' => array('valid' => true, 'id' => $id));
        }
      } else {
        $arrjson = Util::error_general();
      }
    } else {
      if ($nombre != "" ||  $responsable != "" || $comandante != "" || $sigla != "" || $ubicacion != "") {
        $q = "INSERT INTO " . $db->getTable('tbl_brigadas') . " (created_at, nombre, sigla, ubicacion, direccion, telefono, responsable, email, comandante, tbl_usuario_id)
                VALUES ( " . Util::date_now_server() . ", :nombre, :sigla, :ubicacion, :direccion, :telefono, :responsable, :email, :comandante, :tbl_usuario_id)";
        $result = $pdo->prepare($q);
        $arrparam = array(
          ':nombre' => $nombre,
          ':sigla' => $sigla,
          ':ubicacion' => $ubicacion,
          ':direccion' => $direccion,
          ':telefono' => $telefono,
          ':responsable' => $responsable,
          ':email' => $email,
          ':comandante' => $comandante,
          ':tbl_usuario_id' => $tbl_usuario_id
        );
        if ($result->execute($arrparam)) {
          $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
        } else {
          $arrjson = Util::error_general(' Al guardar los datos de brigada');
        }
      } else {
        $arrjson = Util::error_missing_data();
      }
    }
    $db->closeConect();
    return $arrjson;
  }

  /**
   * Metodo para obtener el numero de colores de toda la division (Septima)
   */
  public static function getNumeroDeColoresPorDivision($rqst)
  {

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_vereda.color, COUNT(tbl_vereda.color) AS cuenta
        FROM " . $db->getTable('tbl_vereda') . "
        INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id
        WHERE 
        tbl_vereda.color = '#387905' OR 
        tbl_vereda.color = '#0041FE' OR 
        tbl_vereda.color = '#FEE300' OR 
        tbl_vereda.color = '#F2860D' OR 
        tbl_vereda.color = '#FC0707'  
        AND tbl_vereda.tbl_brigada_id IN (1,2,3,4,5,6,7)
        GROUP BY tbl_vereda.color ORDER BY cuenta ASC";
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

  /**
   * Obtener las veredas por color
   */
  public static function getVeredasPorColorSeptimaBrigada($rqst)
  {

    $color = isset($rqst['color']) ? ($rqst['color']) : '';
    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_vereda.nombre_vereda, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.color
        FROM ((( " . $db->getTable('tbl_vereda') . " INNER JOIN  " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
        INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id)
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio)
        INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
        WHERE tbl_vereda.color = '$color' AND tbl_vereda.tbl_brigada_id IN (1,2,3,4,5,6,7)
        GROUP BY tbl_vereda.id
        ORDER BY tbl_ciudades.municipio ASC";

    // Veredas por color y por brigada Id 
    if ($tbl_brigada_id > 0  && $color != "") {
      $q = "SELECT tbl_vereda.nombre_vereda, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.color
          FROM ((( " . $db->getTable('tbl_vereda') . " INNER JOIN  " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
          INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id)
          INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio)
          INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
          WHERE tbl_vereda.color = '$color' AND tbl_vereda.tbl_brigada_id = $tbl_brigada_id GROUP BY tbl_vereda.id ORDER BY tbl_ciudades.municipio ASC";
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

  /**
   * Obtener las veredas por color AÑO 2021
   */
  public static function getVeredasPorColorSeptimaBrigada2021($rqst)
  {

    $color = isset($rqst['color']) ? ($rqst['color']) : '';
    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_vereda.nombre_vereda, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.color2021
      FROM ((( " . $db->getTable('tbl_vereda') . " INNER JOIN  " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
      INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id)
      INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio)
      INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
      WHERE tbl_vereda.color2021 = '$color' AND tbl_vereda.tbl_brigada_id IN (1,2,3,4,5,6,7)
      GROUP BY tbl_vereda.id
      ORDER BY tbl_ciudades.municipio ASC";

    // Veredas por color y por brigada Id 
    if ($tbl_brigada_id > 0  && $color != "") {
      $q = "SELECT tbl_vereda.nombre_vereda, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.color2021
        FROM ((( " . $db->getTable('tbl_vereda') . " INNER JOIN  " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
        INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id)
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio)
        INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
        WHERE tbl_vereda.color2021 = '$color' AND tbl_vereda.tbl_brigada_id = $tbl_brigada_id GROUP BY tbl_vereda.id ORDER BY tbl_ciudades.municipio ASC";
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


  /**
   * Metodo para obtener ls veredad por un color en especifico AÑO ACTUAL
   */
  public static function getVeredasPorColorSeptimaBrigadaDepartamentoId($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
    $color = isset($rqst['color']) ? ($rqst['color']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_vereda.nombre_vereda, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.color
      FROM ((( " . $db->getTable('tbl_vereda') . " INNER JOIN  " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
      INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id)
      INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio)
      INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
      WHERE
      tbl_vereda.departamento_id = $codigo_departamento AND
      tbl_vereda.color = '$color'
      GROUP BY tbl_vereda.id
      ORDER BY tbl_ciudades.municipio ASC";
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

  /**
   * Metodo para obtener ls veredad por un color en especifico AÑO 2021
   */
  public static function getVeredasPorColorSeptimaBrigadaDepartamentoId2021($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
    $color = isset($rqst['color']) ? ($rqst['color']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_vereda.nombre_vereda, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.color2021
      FROM ((( " . $db->getTable('tbl_vereda') . " INNER JOIN  " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
      INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id)
      INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio)
      INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
      WHERE
      tbl_vereda.departamento_id = $codigo_departamento AND
      tbl_vereda.color2021 = '$color'
      GROUP BY tbl_vereda.id
      ORDER BY tbl_ciudades.municipio ASC";
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


  public static function getAllEstadoVotacionesBrigada($rqst){

    include 'Votaciones.php';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT * FROM " . $db->getTable('tbl_brigadas');
    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
        foreach ($result as $valor) {

                // Se consulta la información del estado
                $qEstado = "SELECT  tbl_brigadas.id, tbl_batallones.sigla AS batallon, tbl_votaciones.tiempo_ocupacion, tbl_votaciones.nombre_puesto, tbl_brigadas.sigla,
                tbl_votaciones.desplazamiento, tbl_brigadas.nombre
                FROM (" . $db->getTable('tbl_votaciones') . "  INNER JOIN " . $db->getTable('tbl_batallones') . "   ON tbl_votaciones.tbl_batallon_id = tbl_batallones.id) 
                INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_votaciones.tbl_brigada_id = tbl_brigadas.id
                WHERE  tbl_brigadas.id = " . $valor['id'];
                $resultEstado = $pdo->query($qEstado);
                $totalRegistros = 0;
                $totalLlenos = 0;
                $totalLlenosDesplazamiento = 0;
                $promedio = 0;
                $promedioDesplazamiento = 0;
                if ($resultEstado) {
                    foreach ($resultEstado as $value) {
                        $totalRegistros += 1;
                        if ( $value['tiempo_ocupacion'] != ""){
                            $totalLlenos += 1;
                        }                            
                        if ( $value['desplazamiento'] != ""){
                            $totalLlenosDesplazamiento += 1;
                        }                            
                    }
                    if ( $totalLlenos >0 ) {
                        $promedio = ceil(( $totalLlenos * 100 ) / $totalRegistros);

                        $promedioDesplazamiento = ceil(( $totalLlenosDesplazamiento * 100 ) / $totalRegistros);
                    }
                }

                $arrTemp = array();
                $arrTemp['id'] = $valor['id'];
                $arrTemp['sigla'] = $valor['sigla'];
                $arrTemp['nombre'] = $valor['nombre'];
                $arrTemp['porcentaje'] = $promedio;
                $arrTemp['porcentajeDesplazamiento'] = $promedioDesplazamiento;
                


                $responseVotaciones = Votaciones::getVeredasSinDesplazamientoBrigada(array('id' =>$valor['id'], 'tipo' => 'desplazamiento'));
                $isvalid = $responseVotaciones['output']['valid'];
                $porcentajeAMostrar = 0;
                if($isvalid){
                  $porcentajeAMostrar = $responseVotaciones['output']['promedioAMostrar'];
                }
                $arrTemp['porcentajeDesplazamientoV2'] = $porcentajeAMostrar;

                $arr[] = $arrTemp;
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
    } else {
        $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
}

}
