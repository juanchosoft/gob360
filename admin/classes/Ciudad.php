<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Ciudad
{

  public function __construct() {}

  public static function getInformacionCiudad($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $codigo_departamento = isset($rqst['codigo_departamento']) ? $rqst['codigo_departamento'] : 0;
    $codigo_muncipio = isset($rqst['codigo_muncipio']) ? $rqst['codigo_muncipio'] : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    // Consulta base
    $q = "SELECT * FROM " . $db->getTable('tbl_ciudades_accion_unificada');
    $conditions = [];
    $params = [];

    // Construcción dinámica de condiciones
    if ($id > 0) {
      $conditions[] = "id = :id";
      $params[':id'] = $id;
    }
    if ($codigo_departamento > 0) {
      $conditions[] = "codigo_departamento = :codigo_departamento";
      $params[':codigo_departamento'] = $codigo_departamento;
    }
    if ($codigo_muncipio > 0) {
      $conditions[] = "codigo_muncipio = :codigo_muncipio";
      $params[':codigo_muncipio'] = $codigo_muncipio;
    }

    // Si hay condiciones, agrégalas
    if (!empty($conditions)) {
      $q .= " WHERE " . implode(" AND ", $conditions);
    }

    // Agregar ordenamiento
    $q .= " ORDER BY municipio ASC";

    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare($q);
    $stmt->execute($params);

    // Recoger resultados
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($arr)) {
      $arrjson = ['output' => ['valid' => true, 'response' => $arr]];
    } else {
      $arrjson = Util::error_no_result();
    }

    $db->closeConect();
    return $arrjson;
  }



  /**
   * Metodo para obtener la informacion de los apotos totales por departamento, y poblacion habilitada para votar
   * @param string $
   */
  public static function getApoyoByCodigoDepartamento($rqst)
  {
    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;

    $arrApoyoLideres = array();
    $arrApoyoDepartamento = array();
    $arrPoblacionHabilitadaDepartamento = array();

    // Apoyo de lideres y apoyo del departamento 
    if ($codigo_departamento > 0) {

      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "SELECT * FROM " . $db->getTable('tbl_departamentos') . " WHERE codigo_departamento = '$codigo_departamento' ";
      $result = $pdo->query($q);
      $tbl_departamento_id = 0;
      if ($result) {
        foreach ($result as $valor) {
          $tbl_departamento_id = $valor['id'];
        }
      }


      // Apoyo de Lideres por departamento
      $qApoyoLideres = "SELECT tbl_lideres.tbl_departamento_id, Count(tbl_lideres.id) AS cantidad
        FROM " . $db->getTable('tbl_lideres') . "
        WHERE tbl_lideres.tbl_departamento_id =  $codigo_departamento
        GROUP BY tbl_lideres.tbl_departamento_id";





      $resultApoyoLideres = $pdo->query($qApoyoLideres);
      if ($resultApoyoLideres) {
        foreach ($resultApoyoLideres as $valorLider) {
          $arrApoyoLideres[] = $valorLider;
        }
      }

      // Apoyo por departamento
      $qApoyoDepartamento = "SELECT Count(comentarios.id) AS cantidad, comentarios.departamento_id
        FROM " . $db->getTable('comentarios') . " 
        WHERE comentarios.departamento_id = $tbl_departamento_id
        GROUP BY comentarios.departamento_id";


      $resultApoyoDep = $pdo->query($qApoyoDepartamento);
      if ($resultApoyoDep) {
        foreach ($resultApoyoDep as $valorApoyoDep) {
          $arrApoyoDepartamento[] = $valorApoyoDep;
        }
      }

      // Poblacion Habilitada para votar
      $qPoblacionHabilitada = " SELECT tbl_ciudades.codigo_departamento, 
        Sum(tbl_ciudades.hombres) AS hombres, 
        Sum(tbl_ciudades.mujeres) AS mujeres, 
        Sum(tbl_ciudades.total) AS total
        FROM " . $db->getTable('tbl_ciudades') . "  
        WHERE tbl_ciudades.codigo_departamento = '$codigo_departamento'
        GROUP BY tbl_ciudades.codigo_departamento";
      $resultPoblacionHabilitada = $pdo->query($qPoblacionHabilitada);
      if ($resultPoblacionHabilitada) {

        foreach ($resultPoblacionHabilitada as $valorPobHabilitada) {
          $arrPoblacionHabilitadaDepartamento[] = $valorPobHabilitada;
        }
      }

      $arrjson = array(
        'output' => array(
          'valid' => true,
          'apoyoDeLideres' => $arrApoyoLideres,
          'apoyoDep' => $arrApoyoDepartamento,
          'poblacionDepHabilitada' => $arrPoblacionHabilitadaDepartamento
        )
      );

      $db->closeConect();
      return $arrjson;
    } else {
      return Util::error_missing_data();
    }
  }

  public static function getAll($rqst)
{
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
    $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : 0;
    $provincia = isset($rqst['provincia']) ? ($rqst['provincia']) : ''; // NUEVO PARÁMETRO

    $db = new DbConection();
    $pdo = $db->openConect();

    // Construcción dinámica de la consulta
    $q = "SELECT * FROM " . $db->getTable('tbl_ciudades_accion_unificada');
    // Siempre excluir registros sin código de municipio válido
    $conditions = ["codigo_muncipio IS NOT NULL", "codigo_muncipio != 0"];

    if ($id > 0) {
        $conditions[] = "id = " . $id;
    }

    if ($codigo_departamento > 0) {
        $conditions[] = "codigo_departamento = " . $codigo_departamento;
    }

    if ($codigo_muncipio > 0) {
        $conditions[] = "codigo_muncipio = " . $codigo_muncipio;
    }

    // NUEVO: Filtrar por provincia si se especifica
    if (!empty($provincia)) {
        $conditions[] = "subregion = '" . $provincia . "'";
    }

    // Agregar condiciones
    $q .= " WHERE " . implode(" AND ", $conditions);

    // Ordenamiento
    $q .= " ORDER BY municipio ASC";

    // Debug: Ver la consulta generada
    // error_log("Consulta SQL: " . $q);

    $result = $pdo->query($q);
    $arr = array();

    if ($result) {
        foreach ($result as $valor) {
            $codeCity = $valor['codigo_muncipio'];
            $colorCalculadoDelMunicipio = "";

            $arrTemp = array();
            $arrTemp['id'] = $valor['id'];
            $arrTemp['codigo_departamento'] = $valor['codigo_departamento'];
            $arrTemp['codigo_muncipio'] = $valor['codigo_muncipio'];
            $arrTemp['subregion'] = $valor['subregion'];
            $arrTemp['porcentaje_participacion'] = $valor['porcentaje_participacion'];
            $arrTemp['aap'] = $valor['aap'];
            $arrTemp['pdet'] = $valor['pdet'];
            $arrTemp['zf'] = $valor['zf'];
            $arrTemp['visible'] = $valor['visible'];
            $arrTemp['tbl_batallon_id'] = $valor['tbl_batallon_id'];
            $arrTemp['municipio'] = $valor['municipio'];
            $arrTemp['puntaje'] = $valor['puntaje'];
            $arrTemp['color'] = $valor['color'];
            $arrTemp['carpeta_mapa'] = $valor['carpeta_mapa'];
            $arrTemp['carpeta_svg'] = $valor['carpeta_svg'];
            $arrTemp['nombre_mapa'] = $valor['nombre_mapa'];
            $arrTemp['latitud'] = $valor['latitud'];
            $arrTemp['longitud'] = $valor['longitud'];
            $arrTemp['color_calculado_de_municipio'] = $colorCalculadoDelMunicipio;
            $arr[] = $arrTemp;
        }
        $arrjson = array('output' => array('valid' => true, 'response' => $arr));
    } else {
        $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
}


  /**
   * Metodo obtener las veredas por color de un Municipio año actual
   */
  public static function getVeredasPorColorCiudadId($rqst)
  {

    $codigo_municipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
    $color = isset($rqst['color']) ? ($rqst['color']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_vereda.nombre_vereda, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.color
        FROM ((( " . $db->getTable('tbl_vereda') . " INNER JOIN  " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
        INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id)
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio)
        INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
        WHERE tbl_vereda.municipio_id = '$codigo_municipio' AND tbl_vereda.color = '$color'  GROUP BY tbl_vereda.id  ORDER BY tbl_ciudades.municipio ASC";
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
   * Metodo obtener las veredas por color de un Municipio año 2021
   */
  public static function getVeredasPorColorCiudadId2021($rqst)
  {

    $codigo_municipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
    $color = isset($rqst['color']) ? ($rqst['color']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_vereda.nombre_vereda, tbl_brigadas.sigla AS brigada, tbl_batallones.sigla AS batallon, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.color2021
        FROM ((( " . $db->getTable('tbl_vereda') . " INNER JOIN  " . $db->getTable('tbl_brigadas') . "  ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id)
        INNER JOIN " . $db->getTable('tbl_batallones') . "  ON tbl_vereda.tbl_batallon_id = tbl_batallones.id)
        INNER JOIN " . $db->getTable('tbl_ciudades') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio)
        INNER JOIN " . $db->getTable('tbl_departamentos') . "  ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
        WHERE tbl_vereda.municipio_id = '$codigo_municipio' AND tbl_vereda.color2021 = '$color'  GROUP BY tbl_vereda.id  ORDER BY tbl_ciudades.municipio ASC";
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


  public static function getFactoresInestabilidadPorCiudad($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
    $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : 0;

    if ($codigo_departamento != "" && $codigo_muncipio != "") {

      $db = new DbConection();
      $pdo = $db->openConect();


      $q0 = "SELECT * FROM " . $db->getTable('tbl_ciudades') . "  WHERE codigo_muncipio = '$codigo_muncipio'";
      $result0 = $pdo->query($q0);
      $puntajeMunicipio = 0;
      $colorMunicipio = "";
      $color = "";
      if ($result0) {
        foreach ($result0 as $valor0) {
          $puntajeMunicipio = $valor0['puntaje'];
          $colorMunicipio = $valor0['color'];
          $tbl_municipio_id = $valor0['id'];
        }
      }


      $q = "SELECT tbl_departamentos.departamento,
          tbl_ciudades.municipio, 
          tbl_ciudades.codigo_muncipio, 
          tbl_vereda.departamento_id, 
          tbl_batallones.sigla AS batallon, 
          tbl_vereda.nombre_vereda, tbl_vereda.id AS tbl_vereda_id
          FROM (((" . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) ) 
          INNER JOIN  " . $db->getTable('tbl_ciudades ') . "  ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio) 
          INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_vereda.departamento_id = tbl_departamentos.codigo_departamento
          WHERE tbl_ciudades.codigo_muncipio = '$codigo_muncipio' ";

      $result = $pdo->query($q);
      $arr = array();
      if ($result) {
        foreach ($result as $value) {

          $rqst =  array(
            'codigo_departamento' => $value['departamento_id'],
            'codigo_muncipio' => $value['codigo_muncipio'],
            'vereda' => $value['nombre_vereda']
          );
          $response = Estado::getEstadoFactorArmadoSocialEcon($rqst);
          if ($response  && $response['output']['valid']) {
            $arrjson = array(
              'puntaje' => $response['output']['puntaje'],
              'armadoResultadoFinal' => $response['output']['armadoResultadoFinal'],
              'socialResultadoFinal' => $response['output']['socialResultadoFinal'],
              'economicoResultadoFinal' => $response['output']['economicoResultadoFinal'],
              'batallon' => $response['output']['batallon'],
              'brigada' => $response['output']['brigada'],
              'nombre_vereda' => $response['output']['nombre_vereda'],
              'nombre_municipio' => $response['output']['nombre_municipio'],
            );
            $arr[] =  $arrjson;
          }
        }
      } else {
        $db->closeConect();
        return Util::error_no_result();
      }
      $db->closeConect();
      return $arr;
    } else {
      return Util::error_no_result();
    }
  }
}
