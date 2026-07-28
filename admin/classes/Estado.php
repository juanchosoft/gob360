<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Estado
{

  public function __construct()
  {
  }

  public static function getVeredasByColor($nameValue)
  {
    $nameValue = strtolower(str_replace("í", "", $nameValue));
    $db = new DbConection();
    $pdo = $db->openConect();
    $q = "SELECT tbl_vereda.nombre_vereda, tbl_vereda.puntaje,tbl_ciudades.municipio,tbl_ciudades.codigo_muncipio, tbl_departamentos.departamento,tbl_brigadas.nombre brigada, tbl_batallones.sigla batallon FROM " . $db->getTable('tbl_vereda') . "
      INNER JOIN " . $db->getTable('tbl_ciudades') . " on TRIM(LEADING '0' FROM tbl_ciudades.codigo_muncipio) = tbl_vereda.municipio_id
       INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_ciudades.codigo_departamento = tbl_departamentos.codigo_departamento
      JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_brigadas.id = tbl_vereda.tbl_brigada_id
      JOIN " . $db->getTable('tbl_batallones') . " ON tbl_batallones.id = tbl_vereda.tbl_batallon_id
      WHERE puntaje BETWEEN (SELECT puntaje_ini FROM " . $db->getTable("tbl_valores") . " WHERE LOWER(nivel) = '$nameValue' ) AND (SELECT puntaje_fin FROM " . $db->getTable("tbl_valores") . " WHERE LOWER(nivel) = '$nameValue' );";

    $result = $pdo->query($q);
    $veredas = array();

    if ($result) {
      foreach ($result as $valor) {
        $veredas[] = $valor;
      }
    }
    return $veredas;
  }

  public static function getDataMunicipioEcono($municipioId)
  {
    $db = new DbConection();
    $pdo = $db->openConect();

    /**========================================================================
     *                 Información de los factores economicos
     *========================================================================**/
    $qE = "SELECT * FROM " . $db->getTable('tbl_economico');
    $resultE = $pdo->query($qE);
    $arrE = array();
    if ($resultE) {
      foreach ($resultE as $valorE) {
        $arrE[] = $valorE;
      }
    }

    $q2 = "SELECT tbl_economico.id as tbl_economico_id, tbl_batallones.sigla, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda,
        tbl_economico.factor, tbl_economico.puntaje, tbl_economico.tipo, tbl_resultados_x_tbl_economico.cantidad, tbl_resultados_economico.observaciones
        FROM ((((((" . $db->getTable('tbl_economico') . "
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
        INNER JOIN  " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id)
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_economico.batallon_id = tbl_batallones.id)
        INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_resultados_economico.brigada_id = tbl_brigadas.id)
        INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_economico.departamento_id = tbl_departamentos.id)
        INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_economico.vereda_id = tbl_vereda.id)
        INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_economico.municipio_id = tbl_ciudades.id
        WHERE tbl_ciudades.id = '$municipioId'";



    $result2 = $pdo->query($q2);
    $arrResultadoEcono = array();
    $puntajeEconomico = 0;
    if ($result2) {
      foreach ($result2 as $valor2) {

        if (floatval($valor2['cantidad']) > 0) {
          $arrResultadoEcono[] = $valor2;
          $puntajeEconomico += $valor2['puntaje'];
        }
      }
    }

    // Obtengo los factores que están en el resultado solo ECONOMICO
    $unique_array = [];
    foreach ($arrResultadoEcono as $element) {
      $hash = $element['factor'];
      $unique_array[$hash] = $element['factor'];
    }
    $resultFactUnicos = array_values($unique_array);

    // Recorremos los datos para validar cuantos hay de cada factor
    $arrFactorAgrupado =  array();
    $puntajeFinalVereda = 0;
    $puntajeEconomicoFinal = 0;

    $puntajeFactorEconNAR = 0;
    $puntajeFactorEconMINA = 0;
    $puntajeFactorEconSEC = 0;

    foreach ($resultFactUnicos as $element) {

      $arrTemporal =  array();
      $cantidadFactorEcon =  0;
      $puntajeFactorEcon = 0;
      foreach ($arrResultadoEcono as $value) {

        if ($element == $value['factor']) {
          $cantidadFactorEcon += 1;
          $puntajeFactorEcon += $value['puntaje'];

          // Si hay más de 2 elementos de tipo NAR, MINA, SEC se pone su valor máximo
          if ($cantidadFactorEcon >= 2) {
            if ($element == 'NAR') {
              $puntajeFactorEconNAR = 200; // Valor maximo del tipo NAR en el factor Economico
            }
            if ($element == 'MINA') {
              $puntajeFactorEconMINA = 200;  // Valor maximo del tipo MINA en el factor Economico
            }
            if ($element == 'SEC') {
              $puntajeFactorEconSEC = 100; // Valor maximo del tipo SEC en el factor Economico
            }
          }
        }
      }
      $puntajeEconomicoFinal += $puntajeFactorEcon;

      $arrTemporal['factor'] = $element;
      $arrTemporal['cantidad'] =  $cantidadFactorEcon;
      $arrTemporal['puntaje'] =  $puntajeFactorEcon;
      $arrFactorAgrupado[] = $arrTemporal;
    }

    $puntajeEconomico = floatval($puntajeFactorEconNAR) + floatval($puntajeFactorEconMINA) + floatval($puntajeFactorEconSEC);
    return Util::calcularPuntajeByFactor(array('factor' => 'economico', 'puntaje' => round($puntajeEconomico, 2)));
  }

  public static function getDataMunicipioArm($municipioId)
  {
    $db = new DbConection();
    $pdo = $db->openConect();
    //Factor Armado
    $q = "SELECT tbl_armado.id as tbl_armado_id, tbl_batallones.nombre, tbl_batallones.sigla, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_armado.nombre, tbl_armado.comision, tbl_armado.frente, tbl_armado.hombres, tbl_resultados_x_tbl_armado.cantidad, tbl_resultados_armado.observaciones, tbl_armado.puntaje
        FROM (((((" . $db->getTable('tbl_armado') . "
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . "  ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id)
        INNER JOIN " . $db->getTable('tbl_resultados_armado') . "  ON tbl_resultados_x_tbl_armado.tbl_resultados_armado_id = tbl_resultados_armado.id)
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_armado.batallon_id = tbl_batallones.id)
        INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_armado.vereda_id = tbl_vereda.id)
        INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_armado.municipio_id = tbl_ciudades.id)
        INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_armado.departamento_id = tbl_departamentos.id
        WHERE tbl_ciudades.visible = '1' AND
        tbl_ciudades.id = '$municipioId'";

    $result = $pdo->query($q);
    $arrResultadosArmado = array();
    $puntajeArmado = 0;
    if ($result) {
      foreach ($result as $valor) {
        if (floatval($valor['cantidad']) > 0) {
          $arrResultadosArmado[] = $valor;
          $puntajeArmado += $valor['puntaje'];
        }
      }
    }

    // Se consulta la cantidad de veredas para calcular el puntaje
    $unique_array_municipios = [];
    foreach ($arrResultadosArmado as $element) {
      $hash = $element['nombre_vereda'];
      $unique_array_municipios[$hash] = $element['nombre_vereda'];
    }
    $veredasDelMunicipio = array_values($unique_array_municipios);
    $cantidadVeredasFactArmado = count($veredasDelMunicipio);
    if ($cantidadVeredasFactArmado > 0) {
      $promedio = $puntajeArmado / $cantidadVeredasFactArmado;
    } else {
      $promedio = $puntajeArmado;
    }
    return Util::calcularPuntajeByFactor(array('factor' => 'armado', 'puntaje' => round($promedio, 2)));
  }

  public static function getDataMunicipioArm2($municipioId, $codeDept, $fecha = null)
  {
    $db = new DbConection();
    $pdo = $db->openConect();

    $tbl_vereda = is_null($fecha) ? "tbl_vereda" : "tbl_vereda_puntos";

    $q    = "SELECT SUM(puntaje_arm) total FROM " . $db->getTable("$tbl_vereda") . "
            INNER JOIN " . $db->getTable('tbl_ciudades') . " ON ${tbl_vereda}.municipio_id = tbl_ciudades.codigo_muncipio
            WHERE tbl_ciudades.id = '$municipioId';";

    if (!is_null($fecha)) {
      $q .= " AND fecha = '$fecha'";
    }
    $result = $pdo->query($q);

    foreach ($result as $key => $value) {
      return Util::calcularPuntajeByFactor(array('factor' => 'armado', 'puntaje' => round($value["total"], 2)));
    }
  }

  public static function getDataAllMunis($fecha, $mun_id = null, $set_cron = false)
  {
    $db = new DbConection();
    $pdo = $db->openConect();

    $dateNow = date("Y-m-d");

    if (!is_null($fecha) && $dateNow != $fecha) {
      $finalArr      = [];
      $qMunisHistory = "SELECT tbl_ciudades_puntos.id,tbl_ciudades_puntos.puntaje FROM " . $db->getTable('tbl_ciudades_puntos') . " WHERE tbl_ciudades_puntos.visible = '1' AND fecha='$fecha';";
      $resultGeneral = $pdo->query($qMunisHistory);
      foreach ($resultGeneral as $key => $value) {
        $color = Util::getColorByPuntaje($value["puntaje"]);
        $finalArr[$value["id"]] = ["color" => $color, "puntaje" => $value["puntaje"]];
      }
      if (empty($finalArr)) {
        return Estado::getDataAllMunis($dateNow);
      }
      return $finalArr;
    }

    $qMun       = "SELECT tbl_ciudades.id FROM " . $db->getTable('tbl_ciudades') . " WHERE tbl_ciudades.visible = '1'";
    $qMun       = !is_null($mun_id) ? $qMun . " AND tbl_ciudades.id=" . $mun_id : $qMun;

    $resultMun  = $pdo->query($qMun);
    $arrIds     = [];

    foreach ($resultMun as $key => $value) {
      $arrIds[] = $value["id"];
    }

    foreach ($arrIds as $key => $value) {
      $arrMunis[$value] = Estado::getDataMunicipioArm($value);
    }


    //Factor Social
    $q1 = "SELECT tbl_sociales.id as tbl_sociales_id, sum(tbl_sociales.puntaje) total, tbl_ciudades.municipio, tbl_ciudades.id, count(tbl_vereda.id) countSoc
      FROM (" . $db->getTable('tbl_sociales') . "
      INNER JOIN (" . $db->getTable('tbl_resultados_x_tbl_social') . "
      INNER JOIN (((" . $db->getTable('tbl_resultados_social') . "
      INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_social.batallon_id = tbl_batallones.id)
      INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_social.municipio_id = tbl_ciudades.id)
      INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_social.vereda_id = tbl_vereda.id) ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id) ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
      INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_social.departamento_id = tbl_departamentos.id
      WHERE tbl_ciudades.visible = '1' AND tbl_resultados_x_tbl_social.cantidad > 0";

    $q1   =  !is_null($mun_id) ? $q1 . " AND tbl_ciudades.id=" . $mun_id : $q1;
    $q1   .= " GROUP BY tbl_ciudades.id,tbl_sociales.id";

    $result1 = $pdo->query($q1);
    $arrResultadosSoc = array();
    $totalesSocial = [];

    if ($result1) {
      foreach ($result1 as $key => $value) {
        $arrResultadosSoc[$value["id"]] = Util::calcularPuntajeByFactor(array('factor' => 'social', 'puntaje' => round($value["total"] / $value["countSoc"], 2)));
      }
    }

    if ($arrIds) {
      foreach ($arrIds as $key => $value) {
        $arrResultadoEcono[$value] = Estado::getDataMunicipioEcono($value);
      }
    }

    $finalArr = [];
    $sqlUpdate = "";
    foreach ($arrIds as $key => $value) {

      $armado = isset($arrMunis[$value]) ? $arrMunis[$value] : 0;
      $econo  = isset($arrResultadoEcono[$value]) ? $arrResultadoEcono[$value] : 0;
      $social = isset($arrResultadosSoc[$value]) ? $arrResultadosSoc[$value] : 0;

      $puntajeMunicipioFinal = floatval($armado) + floatval($econo) + floatval($social);

      $puntajeMunicipioFinal = $puntajeMunicipioFinal > 1000 ? 1000 : $puntajeMunicipioFinal;
      $color = Util::getColorByPuntaje($puntajeMunicipioFinal);
      $finalArr[$value] = ["color" => $color, "puntaje" => $puntajeMunicipioFinal];
      $sqlUpdate .= "UPDATE " . $db->getTable('tbl_ciudades') . " SET puntaje='$puntajeMunicipioFinal', puntaje_arm='$armado',puntaje_eco='$econo',puntaje_soc='$social' WHERE id = $value;";
    }

    if ($set_cron) {
      $pdo->query($sqlUpdate);
      return true;
    }

    return $finalArr;
  }


  /**
   * Función que devuelve el sql de update para actualizar el puntaje de una vereda
   */

  public static function setVereda($idVereda, $update = false)
  {
    $db    = new DbConection();
    $pdo   = $db->openConect();
    $idMun = null;

    $q = "SELECT tbl_batallones.nombre, tbl_batallones.sigla, tbl_departamentos.departamento,tbl_ciudades.id as idMun, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_armado.nombre, tbl_armado.comision, tbl_armado.frente, tbl_armado.hombres, tbl_resultados_x_tbl_armado.cantidad, SUM(tbl_armado.puntaje) total,tbl_vereda.id
              FROM (((((" . $db->getTable('tbl_armado') . "
              INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . "  ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id)
              INNER JOIN " . $db->getTable('tbl_resultados_armado') . "  ON tbl_resultados_x_tbl_armado.tbl_resultados_armado_id = tbl_resultados_armado.id)
              INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_armado.batallon_id = tbl_batallones.id)
              INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_armado.vereda_id = tbl_vereda.id)
              INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_armado.municipio_id = tbl_ciudades.id)
              INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_armado.departamento_id = tbl_departamentos.id
              WHERE  tbl_vereda.id  = '$idVereda' GROUP BY tbl_vereda.id";


    $result = $pdo->query($q);
    $arrArmado = array();
    $resultVeredas[$idVereda] = ["id" => $idVereda];

    if ($result) {
      foreach ($result as $valor) {
        $arrArmado[$valor["id"]] = Util::calcularPuntajeByFactor(array('factor' => 'armado', 'puntaje' => $valor["total"]));
        $idMun                   = $valor["idMun"];
      }
    }

    //SocialVeredas
    $q1 = "SELECT tbl_batallones.nombre, tbl_batallones.sigla, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_sociales.nombre, tbl_sociales.tipo, SUM(tbl_sociales.puntaje) total, tbl_vereda.id, tbl_resultados_x_tbl_social.cantidad
      FROM (" . $db->getTable('tbl_sociales') . "
      INNER JOIN (" . $db->getTable('tbl_resultados_x_tbl_social') . "
      INNER JOIN (((" . $db->getTable('tbl_resultados_social') . "
      INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_social.batallon_id = tbl_batallones.id)
      INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_social.municipio_id = tbl_ciudades.id)
      INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_social.vereda_id = tbl_vereda.id) ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id) ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
      INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_social.departamento_id = tbl_departamentos.id
      WHERE  tbl_vereda.id  = '$idVereda' GROUP BY tbl_vereda.id";



    $result1    = $pdo->query($q1);
    $arrSocial  = array();

    if ($result1) {
      foreach ($result1 as $valor1) {
        $arrSocial[$valor1["id"]] = Util::calcularPuntajeByFactor(array('factor' => 'social', 'puntaje' => $valor1["total"]));
      }
    }

    // Factor Economico
    $q2 = "SELECT tbl_economico.id as tbl_economico_id, tbl_batallones.sigla, tbl_departamentos.departamento,
      tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_vereda.id, tbl_economico.factor, sum(tbl_economico.puntaje) total , tbl_economico.tipo, tbl_resultados_x_tbl_economico.cantidad
      FROM ((((((" . $db->getTable('tbl_economico') . "
      INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
      INNER JOIN  " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id)
      INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_economico.batallon_id = tbl_batallones.id)
      INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_resultados_economico.brigada_id = tbl_brigadas.id)
      INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_economico.departamento_id = tbl_departamentos.id)
      INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_economico.vereda_id = tbl_vereda.id)
      INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_economico.municipio_id = tbl_ciudades.id
      WHERE tbl_vereda.id  = '$idVereda'  GROUP BY tbl_vereda.id";

    $result2 = $pdo->query($q2);
    $arrEcono = array();
    if ($result2) {
      foreach ($result2 as $valor2) {
        $arrEcono[$valor2["id"]] = Util::calcularPuntajeByFactor(array('factor' => 'economico', 'puntaje' => $valor2["total"]));
      }
    }


    $finalArr = [];
    foreach ($resultVeredas as $key => $value) {

      $armado = isset($arrArmado[$value["id"]]) ? $arrArmado[$value["id"]] : 0;
      $econo  = isset($arrSocial[$value["id"]]) ? $arrSocial[$value["id"]] : 0;
      $social = isset($arrEcono[$value["id"]]) ? $arrEcono[$value["id"]] : 0;

      $puntajeVeredaFinal = floatval($armado) + floatval($econo) + floatval($social);

      $puntajeVeredaFinal = $puntajeVeredaFinal > 1000 ? 1000 : $puntajeVeredaFinal;
      $query = "UPDATE " . $db->getTable("tbl_vereda") . " SET puntaje = '$puntajeVeredaFinal', puntaje_soc='$social', puntaje_arm='$armado',puntaje_eco='$econo'  WHERE id = " . $value["id"] . ";";

      if ($update) {
        $pdo->query($query);
        Estado::getDataAllMunis(null, $idMun, true);
      } else {
        return $query;
      }
    }
  }

  /**
   * Informacion deL puntaje y de color de la  ------------------------------------------------- VEREDA --------------------------------------------------
   */
  public static function getEstadoFactorArmadoSocialEcon($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
    $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : 0;
    $tbl_vereda_id = isset($rqst['vereda']) ? ($rqst['vereda']) : '';

    if ($codigo_departamento != "" && $codigo_muncipio != ""  && $tbl_vereda_id != "") {

      $db = new DbConection();
      $pdo = $db->openConect();

      $q01 = "SELECT id, puntaje,  color, observaciones, nombre_vereda FROM " . $db->getTable('tbl_vereda') . "  WHERE nombre_vereda = '$tbl_vereda_id'  AND departamento_id = '$codigo_departamento'  AND municipio_id = '$codigo_muncipio' ";
      $result01 = $pdo->query($q01);
      $colorVereda = "";
      $observaciones = "";
      $puntajaVereda = 0;
      $nombre_vereda = "";
      if ($result01) {
        foreach ($result01 as $valor01) {
          $colorVereda = $valor01['color'];
          $puntajaVereda = $valor01['puntaje'];
          $observaciones = $valor01['observaciones'];
          $nombre_vereda = $valor01['nombre_vereda'];
        }
      }

      $q0 = "SELECT * FROM " . $db->getTable('tbl_ciudades') . "  WHERE codigo_muncipio = '$codigo_muncipio'";
      $result0 = $pdo->query($q0);
      $tbl_municipio_id = 0;
      $nombre_municipio = 0;
      if ($result0) {
        foreach ($result0 as $valor0) {
          $tbl_municipio_id = $valor0['id'];
          $nombre_municipio = $valor0['municipio'];
        }
      }


      $q00 = "SELECT tbl_vereda.id, tbl_brigadas.sigla as brigada, tbl_batallones.sigla as batallon FROM " . $db->getTable('tbl_vereda') . ", " . $db->getTable('tbl_brigadas') . "  ," . $db->getTable('tbl_batallones') . "
        WHERE
        tbl_vereda.tbl_brigada_id = tbl_brigadas.id AND
        tbl_vereda.tbl_batallon_id = tbl_batallones.id AND
        tbl_vereda.nombre_vereda = '$tbl_vereda_id' AND tbl_vereda.municipio_id = $codigo_muncipio LIMIT 1 ";
      $result00 = $pdo->query($q00);
      $vereda_id = 0;
      $batallon = "";
      $brigada = "";
      if ($result00) {
        foreach ($result00 as $valor00) {
          $vereda_id = $valor00['id'];
          $batallon = $valor00['batallon'];
          $brigada = $valor00['brigada'];
        }
      }


      /**========================================================================
       * !                             FINALES INFORMACION
       *========================================================================**/


      $qFinalEconomico_02_05_2022_2 = "SELECT tbl_economico.id AS tbl_economico_id, tbl_economico.tipo, tbl_economico.puntaje, tbl_economico.factor,
        SUM(tbl_resultados_x_tbl_economico.cantidad) AS anterior, tbl_resultados_economico.vereda_id, tbl_resultados_economico.municipio_id
        FROM (" . $db->getTable('tbl_economico') . "   
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
        INNER JOIN " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id
        WHERE tbl_resultados_economico.vereda_id = $vereda_id AND
        tbl_resultados_economico.municipio_id =  $tbl_municipio_id
        GROUP BY tbl_economico.tipo";



      $qFinalEconomico = "SELECT tbl_economico.id AS tbl_economico_id, tbl_economico.tipo, tbl_economico.puntaje, tbl_economico.factor,
        SUM(tbl_resultados_x_tbl_economico.cantidad) AS anterior, tbl_resultados_economico.vereda_id, tbl_resultados_economico.municipio_id 
        FROM (" . $db->getTable('tbl_economico') . "  
        INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . " ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id) 
        INNER JOIN " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id 
        WHERE tbl_resultados_economico.vereda_id = $vereda_id AND
        tbl_resultados_economico.municipio_id = $tbl_municipio_id 
        GROUP BY tbl_economico.tipo";

      $resultFinalEcon = $pdo->query($qFinalEconomico);
      $arrFinalEconomico = array();
      $arrFinalEconomicoTemporal = array();
      $puntajeEconomicoFinalAcumulador = 0;
      $puntajeFactorEcon = 0;
      $contadorNAR = 0;
      $contadorSEC = 0;
      $contadorMINA = 0;
      if ($resultFinalEcon) {
        foreach ($resultFinalEcon as $valor) {

          $id = $valor['tbl_economico_id'];
          $factor = $valor['factor'];

          // Verificamos que hay en Actualmente
          $qFinalEconomicoActual______ = "SELECT tbl_economico.id, tbl_economico.nombre, tbl_economico.tipo, tbl_resultados_economico_final.vereda_id,
                  tbl_resultados_economico_final.municipio_id, tbl_resultados_economico_final.resultado AS actual
                  FROM " . $db->getTable('tbl_economico') . "
                  INNER JOIN " . $db->getTable('tbl_resultados_economico_final') . "  ON tbl_economico.id = tbl_resultados_economico_final.tbl_economico_id
                  WHERE
                  tbl_resultados_economico_final.vereda_id = $vereda_id AND
                  tbl_resultados_economico_final.municipio_id = $tbl_municipio_id AND
                  tbl_economico.id = $id GROUP BY tbl_economico.tipo";


          $qFinalEconomicoActual_02_05_2022 = "SELECT tbl_economico.id, tbl_economico.nombre, tbl_economico.tipo, 
          tbl_resultados_economico_actualizacion.vereda_id, tbl_resultados_economico_actualizacion.municipio_id, 
          tbl_resultados_x_tbl_economico_actualizacion.cantidad AS actual
          FROM (" . $db->getTable('tbl_resultados_economico_actualizacion') . "  
          INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico_actualizacion') . "  ON tbl_resultados_economico_actualizacion.id = tbl_resultados_x_tbl_economico_actualizacion.tbl_resultados_economico_id) 
          INNER JOIN " . $db->getTable('tbl_economico') . " ON tbl_resultados_x_tbl_economico_actualizacion.tbl_economico_id = tbl_economico.id
          WHERE  tbl_resultados_economico_actualizacion.vereda_id = $vereda_id and
          tbl_resultados_economico_actualizacion.municipio_id =  $tbl_municipio_id GROUP BY tbl_economico.tipo";

          $qFinalEconomicoActual_02_05_2022_2 = " SELECT tbl_economico.id, tbl_economico.nombre, tbl_economico.tipo, tbl_resultados_economico_final.resultado AS actual,
          tbl_resultados_economico_final.vereda_id, tbl_resultados_economico_final.municipio_id
          FROM ejec.tbl_economico INNER JOIN ejec.tbl_resultados_economico_final  ON tbl_economico.id = tbl_resultados_economico_final.tbl_economico_id
          WHERE tbl_resultados_economico_final.vereda_id = $vereda_id AND
          tbl_resultados_economico_final.municipio_id =$tbl_municipio_id AND
          tbl_economico.id = $id GROUP BY tbl_economico.tipo";


          $qFinalEconomicoActual = "SELECT tbl_economico.id, tbl_resultados_economico_final.resultado AS actual,tbl_economico.tipo,
                tbl_resultados_economico_final.vereda_id, tbl_resultados_economico_final.municipio_id
                FROM " . $db->getTable('tbl_economico') . " 
                INNER JOIN " . $db->getTable('tbl_resultados_economico_final') . "   ON tbl_economico.id = tbl_resultados_economico_final.tbl_economico_id
                WHERE tbl_resultados_economico_final.vereda_id = $vereda_id AND
                tbl_resultados_economico_final.municipio_id = $tbl_municipio_id AND
                tbl_economico.id = $id ";
          $resultActualEcon = $pdo->query($qFinalEconomicoActual);
          $actual =  floatval($valor['anterior']);
          if ($resultActualEcon) {
            foreach ($resultActualEcon as $valor1) {
              $actual = floatval($valor1['actual']);
            }
          }

          // if( $actual > 0 ){
          $arrFinalEconomicoTemporal['tbl_economico_id'] = $id;
          $arrFinalEconomicoTemporal['puntaje'] = $valor['puntaje'];
          $arrFinalEconomicoTemporal['tipo'] = $valor['tipo'];
          $arrFinalEconomicoTemporal['anterior'] =  round($valor['anterior'], 2);
          $arrFinalEconomicoTemporal['actual'] =  round($actual, 2);
          $arrFinalEconomico[] = $arrFinalEconomicoTemporal;
          // }
        }
      }





      if (count($arrFinalEconomico) > 0) {

        foreach ($arrFinalEconomico as $value) {

          $idEcono = $value["tbl_economico_id"];
          if ($value["actual"] > 0) {
            $qFinalEconomicoActualValidacion = "SELECT tbl_economico.factor, tbl_economico.tipo, tbl_economico.nombre, sum(tbl_economico.puntaje) as actual, tbl_resultados_economico.municipio_id          
                  FROM (" . $db->getTable('tbl_economico') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id) 
                  INNER JOIN " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id
                  WHERE  
                  tbl_resultados_economico.vereda_id = $vereda_id AND
                  tbl_resultados_economico.municipio_id = $tbl_municipio_id AND
                  tbl_economico.id =  $idEcono AND
                  tbl_economico.factor IN( 'MINA', 'SEC', 'NAR' )
                  GROUP BY tbl_economico.factor";

            $resultActualEconValidacion = $pdo->query($qFinalEconomicoActualValidacion);
            $arrayValidacion  = array();
            $puntajeFactorEcon = 0;
            if ($resultActualEconValidacion) {
              foreach ($resultActualEconValidacion as $valor2) {
                if ($valor2['actual'] >= 2) {
                  if ($valor2['factor'] == 'NAR') {
                    $puntajeFactorEcon = 200;
                  }
                  if ($valor2['factor'] == 'MINA') {
                    $puntajeFactorEcon = 100;
                  }
                  if ($valor2['factor'] == 'SEC') {
                    $puntajeFactorEcon = 100;
                  }
                } else {
                  $puntajeFactorEcon = $valor2['puntaje'];
                }

                $puntajeEconomicoFinalAcumulador += $puntajeFactorEcon;
              }
            }
          }
        }
      }


      $puntajeEconomicoFinal = Util::calcularPuntajeByFactor(array('factor' => 'economico', 'puntaje' => $puntajeEconomicoFinalAcumulador));
      /*         print_r($puntajeEconomicoFinal);
        exit();  */

      $qFinalSocial = "SELECT tbl_sociales.id AS tbl_social_id, tbl_sociales.tipo, tbl_sociales.puntaje, SUM(tbl_resultados_x_tbl_social.cantidad) AS anterior, tbl_resultados_social.vereda_id, tbl_resultados_social.municipio_id
        FROM (" . $db->getTable('tbl_sociales') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_social') . "  ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
        INNER JOIN " . $db->getTable('tbl_resultados_social') . "  ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id
        WHERE tbl_resultados_social.vereda_id =  $vereda_id AND
        tbl_resultados_social.municipio_id =  $tbl_municipio_id
        GROUP BY tbl_sociales.tipo ";
      $resultFinalSocial = $pdo->query($qFinalSocial);
      $arrFinalSocial = array();
      $arrFinalSocialTemporal = array();
      $acumuladorFinalSocial = 0;
      if ($resultFinalSocial) {
        foreach ($resultFinalSocial as $valor) {
          $id = $valor['tbl_social_id'];

          // Verificamos que hay en Actualmente
          $qFinalSocialActual = "SELECT tbl_sociales.id, tbl_resultados_social_final.resultado AS actual,
                tbl_resultados_social_final.vereda_id, tbl_resultados_social_final.municipio_id
                FROM " . $db->getTable('tbl_sociales') . " INNER JOIN " . $db->getTable('tbl_resultados_social_final') . "  ON tbl_sociales.id = tbl_resultados_social_final.tbl_social_id
                WHERE tbl_resultados_social_final.vereda_id = $vereda_id AND
                tbl_resultados_social_final.municipio_id = $tbl_municipio_id AND
                tbl_sociales.id = $id ";
          /* 
                echo "-----";
                print_r($qFinalSocialActual);
                echo "-----";
                exit(); 
 */
          $resultActualSocial = $pdo->query($qFinalSocialActual);
          $actual =  $valor['anterior'];
          if ($resultActualSocial) {
            foreach ($resultActualSocial as $valor1) {
              $actual = floatval($valor1['actual']);
            }
          }
          if ($actual > 0) {
            $acumuladorFinalSocial += $valor['puntaje'];
          }
          $arrFinalSocialTemporal['tbl_social_id'] = $id;
          $arrFinalSocialTemporal['puntaje'] = $valor['puntaje'];
          $arrFinalSocialTemporal['tipo'] = $valor['tipo'];
          $arrFinalSocialTemporal['anterior'] =  $valor['anterior'];
          $arrFinalSocialTemporal['actual'] =  $actual;
          $arrFinalSocial[] = $arrFinalSocialTemporal;
          /* echo "-----";
                print_r($actual);
                echo "-----";
                exit(); */
        }
      }
      $puntajeSocialFinal = Util::calcularPuntajeByFactor(array('factor' => 'social', 'puntaje' => $acumuladorFinalSocial));

      /**========================================================================
       * !                     Factor Armado
       *========================================================================**/
      $qFinalArmado = "SELECT tbl_armado.id AS tbl_armado_id, tbl_armado.nombre, tbl_armado.puntaje, tbl_armado.frente, tbl_resultados_armado.vereda_id, tbl_resultados_armado.municipio_id,
        SUM(tbl_resultados_x_tbl_armado.cantidad) AS anterior
        FROM ( " . $db->getTable('tbl_armado') . "  INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . "   ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id)
        INNER JOIN " . $db->getTable('tbl_resultados_armado') . "  ON tbl_resultados_x_tbl_armado.tbl_resultados_armado_id = tbl_resultados_armado.id
        WHERE tbl_resultados_armado.vereda_id = $vereda_id and
        tbl_resultados_armado.municipio_id = $tbl_municipio_id
        GROUP BY tbl_armado.nombre";
      $resultFinalArmado = $pdo->query($qFinalArmado);
      $arrFinalArmado = array();
      $arrFinalArmadoTemporal = array();
      $acumuladorFinalArmado = 0;
      if ($resultFinalArmado) {
        foreach ($resultFinalArmado as $valor) {

          $id = $valor['tbl_armado_id'];

          // Verificamos que hay en Actualmente
          $qFinalArmadoActual = "SELECT tbl_armado.id, tbl_armado.nombre, tbl_armado.comision, tbl_resultados_armado_final.resultado AS actual, tbl_resultados_armado_final.vereda_id, tbl_resultados_armado_final.municipio_id
                FROM " . $db->getTable('tbl_armado') . " INNER JOIN " . $db->getTable('tbl_resultados_armado_final') . "  ON tbl_armado.id = tbl_resultados_armado_final.tbl_armado_id
                WHERE
                tbl_resultados_armado_final.vereda_id = $vereda_id AND
                tbl_resultados_armado_final.municipio_id = $tbl_municipio_id AND
                tbl_armado.id = $id";
          $resultActualArmado = $pdo->query($qFinalArmadoActual);
          $actual =  $valor['anterior'];
          if ($resultActualArmado) {
            foreach ($resultActualArmado as $valor1) {
              $actual = floatval($valor1['actual']);
            }
          }
          if ($actual > 0) {
            $acumuladorFinalArmado += $valor['puntaje'];
          }

          if ($valor['anterior'] > 0 && $actual > 0) {
            $arrFinalArmadoTemporal['tbl_armado_id'] = $id;
            $arrFinalArmadoTemporal['puntaje'] = $valor['puntaje'];
            $arrFinalArmadoTemporal['nombre'] = $valor['nombre'];
            $arrFinalArmadoTemporal['frente'] = $valor['frente'];
            $arrFinalArmadoTemporal['anterior'] =  $valor['anterior'];
            $arrFinalArmadoTemporal['actual'] =  $actual;
            $arrFinalArmado[] = $arrFinalArmadoTemporal;
          }
        }
      }
      $puntajeArmadoFinal = Util::calcularPuntajeByFactor(array('factor' => 'armado', 'puntaje' => $acumuladorFinalArmado));
      /**========================================================================
       * !                      FIN  --- FINALES INFORMACION ---
       *========================================================================**/


      /**========================================================================
       * !                     Informacion Final de la Vereda
       *========================================================================**/
      $puntajeVeredaFinal = floatval($puntajeEconomicoFinal) + floatval($puntajeArmadoFinal) + floatval($puntajeSocialFinal);
      $puntajeVeredaFinal = $puntajeVeredaFinal > 1000 ? 1000 : $puntajeVeredaFinal;
      $color = Util::getColorByPuntaje($puntajeVeredaFinal);

      /**========================================================================
       * !                     Informacion de los Actores
       *========================================================================**/
      $qactores = "SELECT tbl_vereda.id, tbl_vereda.nombre_vereda, tbl_actores.actor, tbl_sociales.tipo, tbl_sociales.nombre 
        FROM (" . $db->getTable('tbl_vereda') . "  INNER JOIN " . $db->getTable('tbl_actores') . "  ON tbl_vereda.id = tbl_actores.vereda_id) 
        INNER JOIN " . $db->getTable('tbl_sociales') . "   ON tbl_actores.tbl_social_id = tbl_sociales.id 
        WHERE tbl_vereda.id = $vereda_id";
      $resultActores = $pdo->query($qactores);

      $actoresArr =  array();
      if ($resultActores) {
        foreach ($resultActores as $valorAct) {
          $actoresArr[] = $valorAct;
        }
      }

      /* echo "economico";
print_r($puntajeEconomicoFinal);
echo "<br>";
echo "puntajeArmadoFinal";
echo "<br>";
print_r($puntajeArmadoFinal);
echo "<br>";
echo "puntajeSocialFinal";
echo "<br>";
print_r($puntajeSocialFinal);
echo "<br>";
print_r($color);
echo "<br>";
exit();     */

      // Se consulta la información de actores Asignados MAPA DE ACTORES COMPROMETIDOS EN EJECUTAR LAS CARENCIAS SOCIALES
      $q1 = "SELECT * FROM " . $db->getTable('tbl_actores') . "  WHERE municipio_id = $codigo_muncipio";
      $arrassigned = array();
      $result1 = $pdo->query($q1);
      foreach ($result1 as $valor2) {
        $arrassigned[] = $valor2;
      }

      // Se consulta los mapa de actores version 2.0
      $qActoresDinamicos = "SELECT tbl_ingreso_actores.* , tbl_actores_mapa.nombre 
        FROM " . $db->getTable('tbl_ingreso_actores') . "," . $db->getTable('tbl_ciudades') . "," . $db->getTable('tbl_actores_mapa') . " 
        WHERE tbl_ingreso_actores.municipio_id = tbl_ciudades.codigo_muncipio AND tbl_actores_mapa.id = tbl_ingreso_actores.actor_id AND
        tbl_ingreso_actores.municipio_id = $codigo_muncipio GROUP BY actor_id";
      $arrActoresDinamicos = array();
      $resultActorDinamicos = $pdo->query($qActoresDinamicos);
      foreach ($resultActorDinamicos as $valor3) {
        $arrActoresDinamicos[] = $valor3;
      }

      $arrjson = array('output' => array(
        'valid' => true,
        'puntaje' => $puntajeVeredaFinal,
        'color' => $color,
        'armadoResultadoFinal' => $arrFinalArmado,
        'socialResultadoFinal' => $arrFinalSocial,
        'economicoResultadoFinal' => $arrFinalEconomico,
        'nombre_vereda' => $nombre_vereda,
        'nombre_municipio' => $nombre_municipio,
        'batallon' => $batallon,
        'brigada' => $brigada,
        'actores' => $actoresArr,
        'mapa_actores_asignados' => $arrassigned,
        'observaciones' => $observaciones,
        'actoresDinamicos' => $arrActoresDinamicos,
        'puntajeEconomico' => $puntajeEconomicoFinal,
        'puntajeArmado' => $puntajeArmadoFinal,
        'puntajeSocial' => $puntajeSocialFinal,
      ));

      $db->closeConect();
      return $arrjson;
    } else {
      return  Util::error_missing_data();
    }
  }

  /**
   * Metodo para obtener el MAPA y calcular los puntajes y colres segun sea el caso, Municipio o Veredas
   */
  public static function getDataVeredas($codeDept, $codeCity, $idVereda = null, $puntajeCalculado = null)
  {

    $db = new DbConection();
    $pdo = $db->openConect();

    // Por municipio
    $consultarPorVeredasById = "no";

    $qVeredas = "SELECT
      tbl_vereda.id as tbl_vereda_id,
      tbl_vereda.nombre_vereda,
      tbl_vereda.carpeta_svg,
      tbl_vereda.id,
      tbl_vereda.nombre_svg,
      tbl_vereda.puntaje,
      tbl_vereda.color,
      tbl_vereda.porcentaje_participacion,
      tbl_vereda.municipio_id as codigo_municipio
      FROM " . $db->getTable('tbl_vereda') . "
      INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_vereda.municipio_id =  TRIM(LEADING '0' FROM tbl_ciudades.codigo_muncipio)
      WHERE tbl_ciudades.codigo_muncipio = '$codeCity' AND tbl_ciudades.codigo_departamento = '$codeDept'";





    // Cuando necesita verificar el puntaje de la vereda agrega esta condicion
    if (!is_null($idVereda)) {
      $qVeredas .= " AND tbl_vereda.nombre_vereda  = '$idVereda'";
      $consultarPorVeredasById = "si";
    }
    $resultVeredas = $pdo->query($qVeredas);



    $arr = [];
    $finalArr = [];
    $numeroVeredas = [];
    $numeroVeredas2021 = [];

    if ($consultarPorVeredasById == 'si') {
      // For para obtener información de cada Municipio
      foreach ($resultVeredas as $key => $value) {
        $value["info_apoyo"] = Estado::getInfoVereda($value["tbl_vereda_id"]);
        $arr[] = $value;
      }
    } else {

      // For para obtener información de cada vereda
      foreach ($resultVeredas as $key => $value) {
        $color = $value['color'];
        $arr[] = $value;
        $numeroVeredas = Util::numeroVeredasByMunicipioId(array('codigo_municipio' => $value['codigo_municipio']));
        $numeroVeredas2021 = Util::numeroVeredasByMunicipioId2021(array('codigo_municipio' => $value['codigo_municipio']));
      }
    }
    $finalArr['data'] = $arr;
    $finalArr['cantidades'] = $numeroVeredas;
    $finalArr['cantidades2021'] = $numeroVeredas2021;
    return $finalArr;
  }



  public static function getInfoVereda($vereda_id)
  {
    $db = new DbConection();
    $pdo = $db->openConect();

    $campo = $vereda_id == 1 ? "tbl_barrio_id" : "vereda_id";

    $qApoyoCiudad ="SELECT Count(comentarios.id) AS cantidad, 
      ( SELECT count(inclusion) FROM ".$db->getTable('comentarios')." cmt WHERE inclusion = 1 and cmt.$campo = comentarios.$campo) apoyo_inclusion,
      ( SELECT count(ambiente) FROM ".$db->getTable('comentarios')." cmt WHERE ambiente = 1 and cmt.$campo = comentarios.$campo) apoyo_ambiente,
      ( SELECT count(seguridad) FROM ".$db->getTable('comentarios')." cmt WHERE seguridad = 1 and cmt.$campo = comentarios.$campo) apoyo_seguridad,
      ( SELECT count(agricultura) FROM ".$db->getTable('comentarios')." cmt WHERE agricultura = 1 and cmt.$campo = comentarios.$campo) apoyo_agricultura,
      ( SELECT count(economia) FROM ".$db->getTable('comentarios')." cmt WHERE economia = 1 and cmt.$campo = comentarios.$campo) apoyo_economia,
      ( SELECT count(salud) FROM ".$db->getTable('comentarios')." cmt WHERE salud = 1 and cmt.$campo = comentarios.$campo) apoyo_salud,
      ( SELECT count(infraestructura) FROM ".$db->getTable('comentarios')." cmt WHERE infraestructura = 1 and cmt.$campo = comentarios.$campo) apoyo_infraestructura,
      ( SELECT count(politica) FROM ".$db->getTable('comentarios')." cmt WHERE politica = 1 and cmt.$campo = comentarios.$campo) apoyo_politica,
      ( SELECT count(corrupcion) FROM ".$db->getTable('comentarios')." cmt WHERE corrupcion = 1 and cmt.$campo = comentarios.$campo) apoyo_corrupcion,
      ( SELECT count(comunicaciones) FROM ".$db->getTable('comentarios')." cmt WHERE comunicaciones = 1 and cmt.$campo = comentarios.$campo) apoyo_comunicaciones,
      ( SELECT count(educacion) FROM ".$db->getTable('comentarios')." cmt WHERE educacion = 1 and cmt.$campo = comentarios.$campo) apoyo_educacion,
      ( SELECT count(familia) FROM ".$db->getTable('comentarios')." cmt WHERE familia = 1 and cmt.$campo = comentarios.$campo) apoyo_familia,
      ( SELECT count(recreacion) FROM ".$db->getTable('comentarios')." cmt WHERE recreacion = 1 and cmt.$campo = comentarios.$campo) apoyo_recreacion
      FROM " . $db->getTable('comentarios') . " 
      WHERE comentarios.$campo = $vereda_id ";
      $resultApoyoCiudad = $pdo->query($qApoyoCiudad);
      $arrCiudadInfoApoyo = array();
      if ($resultApoyoCiudad) {
        foreach ($resultApoyoCiudad as $valor) {
          $db->closeConect();
          return $valor;
        }
      }

    $db->closeConect();
    return [];
  }

  /**
   * Metodo para realizar las consultas por el departamento y municipio
   */




  /**
   * Metodo para realizar las consultas por el departamento y municipio
   */
  public static function getEstadoFactorArmadoSocialEconByMunicipio($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;
    $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : 0;

    $filtro_fechas = isset($rqst['filtro_fechas']) ? ($rqst['filtro_fechas']) : 'no';
    $fecha_inicio = isset($rqst['fecha_inicio']) ? ($rqst['fecha_inicio']) : '';
    $fecha_fin = isset($rqst['fecha_fin']) ? ($rqst['fecha_fin']) : '';

    if ($codigo_departamento != "" && $codigo_muncipio != "") {

      if ($filtro_fechas == 'si') {
        if ($fecha_inicio === "" || $fecha_fin === "") {
          return  Util::error_missing_data();
        }
      }

      $db = new DbConection();
      $pdo = $db->openConect();

      /**========================================================================
       *                 Información de los factores armado
       *========================================================================**/
      $qA = "SELECT * FROM " . $db->getTable('tbl_armado');
      $resultArm = $pdo->query($qA);
      $arrArm = array();
      if ($resultArm) {
        foreach ($resultArm as $valorA) {
          $arrArm[] = $valorA;
        }
      }

      //Factor Armado
      $q = "SELECT tbl_armado.id as tbl_armado_id, tbl_batallones.nombre, tbl_batallones.sigla, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_armado.nombre, tbl_armado.comision, tbl_armado.frente, tbl_armado.hombres, tbl_resultados_x_tbl_armado.cantidad, tbl_resultados_armado.observaciones, tbl_armado.puntaje
          FROM (((((" . $db->getTable('tbl_armado') . "
          INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_armado') . "  ON tbl_armado.id = tbl_resultados_x_tbl_armado.tbl_armado_id)
          INNER JOIN " . $db->getTable('tbl_resultados_armado') . "  ON tbl_resultados_x_tbl_armado.tbl_resultados_armado_id = tbl_resultados_armado.id)
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_armado.batallon_id = tbl_batallones.id)
          INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_armado.vereda_id = tbl_vereda.id)
          INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_armado.municipio_id = tbl_ciudades.id)
          INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_armado.departamento_id = tbl_departamentos.id
          WHERE tbl_departamentos.codigo_departamento = '$codigo_departamento' AND
          tbl_ciudades.codigo_muncipio = '$codigo_muncipio'";

      if ($filtro_fechas == 'si') {
        $q .= " AND tbl_resultados_armado.created_at >= '$fecha_inicio 00:00:01' AND  tbl_resultados_armado.created_at <= '$fecha_fin 23:59:59' ";
      }

      $result = $pdo->query($q);
      $arrResultadosArmado = array();
      $puntajeArmado = 0;
      if ($result) {
        foreach ($result as $valor) {
          if (floatval($valor['cantidad']) > 0) {
            $arrResultadosArmado[] = $valor;
            $puntajeArmado += $valor['puntaje'];
          }
        }
      }

      // Se consulta la cantidad de veredas para calcular el puntaje
      $unique_array_municipios = [];
      foreach ($arrResultadosArmado as $element) {
        $hash = $element['nombre_vereda'];
        $unique_array_municipios[$hash] = $element['nombre_vereda'];
      }
      $veredasDelMunicipio = array_values($unique_array_municipios);
      $cantidadVeredasFactArmado = count($veredasDelMunicipio);
      if ($cantidadVeredasFactArmado > 0) {
        $promedio = $puntajeArmado / $cantidadVeredasFactArmado;
      } else {
        $promedio = $puntajeArmado;
      }
      $puntajeArmadoFinal = Util::calcularPuntajeByFactor(array('factor' => 'armado', 'puntaje' => round($promedio, 2)));

      // Consolidado
      $arrConsolidadoArm = array();
      foreach ($arrArm as $val0Arm) {

        $cantidad = 0;
        $comision = "";
        $arrTemp = array();
        foreach ($arrResultadosArmado as $val) {
          if ($val0Arm['id'] == $val['tbl_armado_id']) {
            $cantidad =  $val['cantidad'];
            $comision =  $val['comision'];
          }
        }
        if (floatval($cantidad) > 0) {
          $arrTemp['comision'] = $val0Arm['comision'];;
          $arrTemp['factor_armado'] = $val0Arm['nombre'];
          $arrTemp['tipo'] = $val0Arm['tipo'];
          $arrTemp['id'] = $val0Arm['id'];
          $arrTemp['cantidad'] = $cantidad;
          $arrConsolidadoArm[] = $arrTemp;
        }
      }

      /**========================================================================
       *                 Información de los factores sociales
       *========================================================================**/
      $qS = "SELECT * FROM " . $db->getTable('tbl_sociales');
      $resultSoc = $pdo->query($qS);
      $arrSoc = array();
      if ($resultSoc) {
        foreach ($resultSoc as $valorS) {
          $arrSoc[] = $valorS;
        }
      }

      //Factor Social
      $q1 = "SELECT tbl_sociales.id as tbl_sociales_id, tbl_batallones.nombre, tbl_batallones.sigla, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_sociales.nombre, tbl_sociales.tipo, tbl_sociales.puntaje, tbl_resultados_x_tbl_social.cantidad, tbl_resultados_social.observaciones
          FROM (" . $db->getTable('tbl_sociales') . "
          INNER JOIN (" . $db->getTable('tbl_resultados_x_tbl_social') . "
          INNER JOIN (((" . $db->getTable('tbl_resultados_social') . "
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_social.batallon_id = tbl_batallones.id)
          INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_social.municipio_id = tbl_ciudades.id)
          INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_social.vereda_id = tbl_vereda.id) ON tbl_resultados_x_tbl_social.tbl_resultados_social_id = tbl_resultados_social.id) ON tbl_sociales.id = tbl_resultados_x_tbl_social.tbl_social_id)
          INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_social.departamento_id = tbl_departamentos.id
          WHERE tbl_departamentos.codigo_departamento = '$codigo_departamento' AND
          tbl_ciudades.codigo_muncipio = '$codigo_muncipio'";

      if ($filtro_fechas == 'si') {
        $q .= " AND tbl_resultados_social.created_at >= '$fecha_inicio 00:00:01' AND  tbl_resultados_social.created_at <= '$fecha_fin 23:59:59' ";
      }

      $result1 = $pdo->query($q1);
      $arrResultadosSoc = array();
      $puntajeSocial = 0;
      if ($result1) {
        foreach ($result1 as $valor1) {

          if (floatval($valor1['cantidad']) > 0) {
            $arrResultadosSoc[] = $valor1;
            $puntajeSocial += $valor1['puntaje'];
          }
        }
      }
      $cantidadVeredasFactSocial = count($arrResultadosSoc);
      if ($cantidadVeredasFactSocial > 0) {
        $promedioSocial = $puntajeSocial / $cantidadVeredasFactSocial;
      } else {
        $promedioSocial = $puntajeSocial;
      }
      $puntajeSocialFinal = Util::calcularPuntajeByFactor(array('factor' => 'social', 'puntaje' => $promedioSocial));

      // Consolidado Sociales
      $arrConsolidadoSoc = array();
      foreach ($arrSoc as $val0) {

        $cantidad = 0;
        $arrTemp = array();
        foreach ($arrResultadosSoc as $val) {
          if ($val0['id'] == $val['tbl_sociales_id']) {
            $cantidad +=  $val['cantidad'];
          }
        }
        if (floatval($cantidad) > 0) {
          $arrTemp['factor_social'] = $val0['nombre'];
          $arrTemp['tipo'] = $val0['tipo'];
          $arrTemp['id'] = $val0['id'];
          $arrTemp['cantidad'] = $cantidad;
          $arrConsolidadoSoc[] = $arrTemp;
        }
      }

      /**========================================================================
       *                 Información de los factores economicos
       *========================================================================**/
      $qE = "SELECT * FROM " . $db->getTable('tbl_economico');
      $resultE = $pdo->query($qE);
      $arrE = array();
      if ($resultE) {
        foreach ($resultE as $valorE) {
          $arrE[] = $valorE;
        }
      }

      $q2 = "SELECT tbl_economico.id as tbl_economico_id, tbl_batallones.sigla, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda,
          tbl_economico.factor, tbl_economico.puntaje, tbl_economico.tipo, tbl_resultados_x_tbl_economico.cantidad, tbl_resultados_economico.observaciones
          FROM ((((((" . $db->getTable('tbl_economico') . "
          INNER JOIN " . $db->getTable('tbl_resultados_x_tbl_economico') . "  ON tbl_economico.id = tbl_resultados_x_tbl_economico.tbl_economico_id)
          INNER JOIN  " . $db->getTable('tbl_resultados_economico') . "  ON tbl_resultados_x_tbl_economico.tbl_resultados_economico_id = tbl_resultados_economico.id)
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_resultados_economico.batallon_id = tbl_batallones.id)
          INNER JOIN " . $db->getTable('tbl_brigadas') . "  ON tbl_resultados_economico.brigada_id = tbl_brigadas.id)
          INNER JOIN " . $db->getTable('tbl_departamentos') . " ON tbl_resultados_economico.departamento_id = tbl_departamentos.id)
          INNER JOIN " . $db->getTable('tbl_vereda') . " ON tbl_resultados_economico.vereda_id = tbl_vereda.id)
          INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_resultados_economico.municipio_id = tbl_ciudades.id
          WHERE tbl_departamentos.codigo_departamento = '$codigo_departamento' AND
          tbl_ciudades.codigo_muncipio = '$codigo_muncipio'";
      if ($filtro_fechas == 'si') {
        $q .= " AND tbl_resultados_economico.created_at >= '$fecha_inicio 00:00:01' AND  tbl_resultados_economico.created_at <= '$fecha_fin 23:59:59' ";
      }

      $result2 = $pdo->query($q2);
      $arrResultadoEcono = array();
      $puntajeEconomico = 0;
      if ($result2) {
        foreach ($result2 as $valor2) {

          if (floatval($valor2['cantidad']) > 0) {
            $arrResultadoEcono[] = $valor2;
            $puntajeEconomico += $valor2['puntaje'];
          }
        }
      }

      // Obtengo los factores que están en el resultado solo ECONOMICO
      $unique_array = [];
      foreach ($arrResultadoEcono as $element) {
        $hash = $element['factor'];
        $unique_array[$hash] = $element['factor'];
      }
      $resultFactUnicos = array_values($unique_array);

      // Recorremos los datos para validar cuantos hay de cada factor
      $arrFactorAgrupado =  array();
      $puntajeFinalVereda = 0;
      $puntajeEconomicoFinal = 0;

      $puntajeFactorEconNAR = 0;
      $puntajeFactorEconMINA = 0;
      $puntajeFactorEconSEC = 0;

      foreach ($resultFactUnicos as $element) {

        $arrTemporal =  array();
        $cantidadFactorEcon =  0;
        $puntajeFactorEcon = 0;
        foreach ($arrResultadoEcono as $value) {

          if ($element == $value['factor']) {
            $cantidadFactorEcon += 1;
            $puntajeFactorEcon += $value['puntaje'];

            // Si hay más de 2 elementos de tipo NAR, MINA, SEC se pone su valor máximo
            if ($cantidadFactorEcon >= 2) {
              if ($element == 'NAR') {
                $puntajeFactorEconNAR = 200; // Valor maximo del tipo NAR en el factor Economico
              }
              if ($element == 'MINA') {
                $puntajeFactorEconMINA = 200;  // Valor maximo del tipo MINA en el factor Economico
              }
              if ($element == 'SEC') {
                $puntajeFactorEconSEC = 100; // Valor maximo del tipo SEC en el factor Economico
              }
            }
          }
        }
        $puntajeEconomicoFinal += $puntajeFactorEcon;

        $arrTemporal['factor'] = $element;
        $arrTemporal['cantidad'] =  $cantidadFactorEcon;
        $arrTemporal['puntaje'] =  $puntajeFactorEcon;
        $arrFactorAgrupado[] = $arrTemporal;
      }

      $puntajeEconomico = floatval($puntajeFactorEconNAR) + floatval($puntajeFactorEconMINA) + floatval($puntajeFactorEconSEC);
      $puntajeEconomicoFinal = Util::calcularPuntajeByFactor(array('factor' => 'economico', 'puntaje' => round($puntajeEconomico, 2)));

      // Calculos de resultado Final del municipio
      $puntajeMunicipioFinal = floatval($puntajeEconomicoFinal) + floatval($puntajeArmadoFinal) + floatval($puntajeSocialFinal);
      $puntajeMunicipioFinal = $puntajeMunicipioFinal > 1000 ? 1000 : $puntajeMunicipioFinal;
      $color = Util::getColorByPuntaje($puntajeMunicipioFinal);


      // Consolidado Economico
      $arrConsolidadoEco = array();
      foreach ($arrE as $val0) {

        $cantidad = 0;
        $arrTemp = array();
        foreach ($arrResultadoEcono as $val) {
          if ($val0['id'] == $val['tbl_economico_id']) {
            $cantidad +=  $val['cantidad'];
          }
        }
        if (floatval($cantidad) > 0) {
          $arrTemp['factor_economico'] = $val0['nombre'];
          $arrTemp['tipo'] = $val0['tipo'];
          $arrTemp['id'] = $val0['id'];
          $arrTemp['cantidad'] = $cantidad;
          $arrConsolidadoEco[] = $arrTemp;
        }
      }
      /**========================================================================
       *           FIN      Información de los factores economicos
       *========================================================================**/


      $arrjson = array('output' => array(
        'valid' => true,
        'armado' => $arrResultadosArmado,
        'social' => $arrResultadosSoc,
        'economico' => $arrResultadoEcono,
        'consolidadoEconomico' => $arrConsolidadoEco,
        'consolidadoSocial' => $arrConsolidadoSoc,
        'consolidadoArmado' => $arrConsolidadoArm,
        'puntaje' => $puntajeMunicipioFinal,
        'color' => $color
      ));

      $db->closeConect();
      return $arrjson;
    } else {
      return  Util::error_missing_data();
    }
  }

  /**
   * Metodo para calcular los puntajes y color del  ---------------------------------------- MUNICIPIO - -----------------------------------------
   */
  public static function getEstadoFactorArmadoSocialEconByMunicipioNUEVO($rqst)
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
      $arrCiudadInfo = array();
      if ($result0) {
        foreach ($result0 as $valor0) {
          $puntajeMunicipio = $valor0['puntaje'];
          $colorMunicipio = $valor0['color'];
          $tbl_municipio_id = $valor0['id'];

          $arrCiudadInfo[] = $valor0;
        }
      }

      // Se consulta aque batallon y brigada pertenece
      $qBriBat = "SELECT tbl_batallones.sigla as batallon, tbl_brigadas.sigla AS brigada, tbl_ciudades.municipio, tbl_ciudades.id
        FROM ( " . $db->getTable('tbl_batallones') . "  INNER JOIN (" . $db->getTable('tbl_brigadas') . "  INNER JOIN " . $db->getTable('tbl_vereda') . "  ON tbl_brigadas.id = tbl_vereda.tbl_brigada_id) ON tbl_batallones.id = tbl_vereda.tbl_batallon_id)
        INNER JOIN " . $db->getTable('tbl_ciudades') . "   ON tbl_vereda.divipola = tbl_ciudades.codigo_muncipio
        WHERE tbl_ciudades.codigo_muncipio = '$codigo_muncipio' LIMIT 1";
      $resultBrigBat = $pdo->query($qBriBat);
      $brigada = "";
      $batallon = "";
      if ($resultBrigBat) {
        foreach ($resultBrigBat as $valorBriBat) {
          $brigada = $valorBriBat['brigada'];
          $batallon = $valorBriBat['batallon'];
        }
      }

      // Apoyo de la ciudad
      // Apoyo de la ciudad
      $qApoyoCiudad ="SELECT tbl_ciudades.id, tbl_ciudades.municipio, Count(comentarios.id) AS cantidad, 
      ( SELECT count(inclusion) FROM ".$db->getTable('comentarios')." cmt WHERE inclusion = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_inclusion,
      ( SELECT count(ambiente) FROM ".$db->getTable('comentarios')." cmt WHERE ambiente = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_ambiente,
      ( SELECT count(seguridad) FROM ".$db->getTable('comentarios')." cmt WHERE seguridad = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_seguridad,
      ( SELECT count(agricultura) FROM ".$db->getTable('comentarios')." cmt WHERE agricultura = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_agricultura,
      ( SELECT count(economia) FROM ".$db->getTable('comentarios')." cmt WHERE economia = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_economia,
      ( SELECT count(salud) FROM ".$db->getTable('comentarios')." cmt WHERE salud = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_salud,
      ( SELECT count(infraestructura) FROM ".$db->getTable('comentarios')." cmt WHERE infraestructura = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_infraestructura,
      ( SELECT count(politica) FROM ".$db->getTable('comentarios')." cmt WHERE politica = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_politica,
      ( SELECT count(corrupcion) FROM ".$db->getTable('comentarios')." cmt WHERE corrupcion = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_corrupcion,
      ( SELECT count(comunicaciones) FROM ".$db->getTable('comentarios')." cmt WHERE comunicaciones = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_comunicaciones,
      ( SELECT count(educacion) FROM ".$db->getTable('comentarios')." cmt WHERE educacion = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_educacion,
      ( SELECT count(familia) FROM ".$db->getTable('comentarios')." cmt WHERE familia = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_familia,
      ( SELECT count(recreacion) FROM ".$db->getTable('comentarios')." cmt WHERE recreacion = 1 and cmt.municipio_id = comentarios.municipio_id ) apoyo_recreacion
      FROM " . $db->getTable('comentarios') . "  INNER JOIN " . $db->getTable('tbl_ciudades') . "   ON comentarios.municipio_id = tbl_ciudades.id
      WHERE tbl_ciudades.id = $tbl_municipio_id  GROUP BY tbl_ciudades.id, tbl_ciudades.municipio";
      $resultApoyoCiudad = $pdo->query($qApoyoCiudad);
      $arrCiudadInfoApoyo = array();
      if ($resultApoyoCiudad) {
        foreach ($resultApoyoCiudad as $valor) {
          $arrCiudadInfoApoyo[] = $valor;
        }
      }

      // Apoyo de lideres
      $qApoyoCiudadLideres ="SELECT tbl_lideres.tbl_municipio_id, Count(tbl_lideres.id) AS cantidad
      FROM " . $db->getTable('tbl_lideres') . " WHERE tbl_lideres.tbl_municipio_id = $codigo_muncipio
      GROUP BY tbl_lideres.tbl_municipio_id";
      $resultApoyoCiudadLideres = $pdo->query($qApoyoCiudadLideres);
      $arrCiudadInfoLideres = array();
      if ($resultApoyoCiudadLideres) {
        foreach ($resultApoyoCiudadLideres as $valor) {
          $arrCiudadInfoLideres[] = $valor;
        }
      }
      
      $arrjson = array('output' => array(
        'valid' => true,
        'color' => $color,
        'brigada' => $brigada,
        'batallon' => $batallon,
        'ciudadInfo' => $arrCiudadInfo,
        'ciudadInfoApoyo' => $arrCiudadInfoApoyo,
        'ciudadInfoLideres' => $arrCiudadInfoLideres,
      ));

      $db->closeConect();
      return $arrjson;
      
    } else {
      return  Util::error_missing_data();
    }
  }

  /**
   * Listo los municipios por cada Deprtamento id
   */
  public static function getMunicipiosByDepartamentoId($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    $subregiones = [];

    if ($codigo_departamento != "") {

      $q0 = "SELECT * FROM " . $db->getTable('tbl_ciudades') . "  WHERE codigo_departamento = '$codigo_departamento'";
      $result0 = $pdo->query($q0);

      $tbl_municipio_id = 0;
      $codigo_muncipio = "";
      $municipios = [];
      $cantidades = [];

      $estable = 0;
      $bajo = 0;
      $critico = 0;
      $alto = 0;
      $medio = 0;

      if ($result0) {
        foreach ($result0 as $key => $value) {
          $tbl_municipio_id = $value["id"];
          if (!in_array($value["subregion"], $subregiones)) {
            $subregiones[] = $value["subregion"];
          }
          $codigo_muncipio = $value['codigo_muncipio'];
          $params = array('codigo_departamento' => $codigo_departamento, 'codigo_muncipio' =>  $codigo_muncipio);
          $data = Estado::getEstadoFactorArmadoSocialEconByMunicipioNUEVO($params);

          if ($data['output']['valid']) {
            $value['puntaje'] =  $data['output']['puntaje'];
            $value['color'] = $data['output']['color'];
            $municipios[] = $value;
          }
        }
      }
      $arrjson = array('output' => array('valid' => true, 'municipios' => $municipios, 'subregiones' => $subregiones));

      $db->closeConect();
      return $arrjson;
    } else {
      return  Util::error_missing_data();
    }
  }

  /**
   * Metodo para calcular inicialmente los puntajes de cada VEREDA de cada departamento
   */
  public static function getCalcularPuntajesVeredas($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($codigo_departamento != "") {

      // Consultamos todas las Veredas
      $q = "SELECT tbl_ciudades.codigo_muncipio, tbl_vereda.nombre_vereda, tbl_vereda.id as tbl_vereda_id
          FROM " . $db->getTable('tbl_vereda') . "," . $db->getTable('tbl_ciudades') . "
          WHERE tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio AND codigo_departamento = '$codigo_departamento'  ";

      $resultVeredas = $pdo->query($q);
      $arr = [];
      $tbl_vereda_id = 0;
      foreach ($resultVeredas as $key => $value) {

        $tbl_vereda_id = $value['tbl_vereda_id'];

        $rqst =  array(
          'codigo_departamento' => $codigo_departamento,
          'codigo_muncipio' => $value['codigo_muncipio'],
          'vereda' => $value['nombre_vereda']
        );

        $result = Estado::getEstadoFactorArmadoSocialEcon($rqst);
        $puntajeVereda = 0;
        $color = "";
        $actual = "";
        $anterior = "";

        if ($result  && $result['output']['valid']) {
          $puntajeVereda = $result['output']['puntaje'];
          $color = $result['output']['color'];

          $q1 = "UPDATE  " . $db->getTable('tbl_vereda') . "
                  SET color='" . $color . "' ,
                  puntaje ='" .  $puntajeVereda . "'
                  WHERE id = $tbl_vereda_id ";
          $result = $pdo->query($q1);
        }
      }

      $arrjson = array('output' => array('valid' => true, 'response' => $codigo_departamento));
      $db->closeConect();
      return $arrjson;
    } else {
      return Util::error_no_result();
    }
  }

  /**
   * Metodo para calcular inicialmente los puntajes de cada MUNICIPIO de cada departamento
   */
  public static function getCalcularPuntajesMunicipioByDepartmentoId($rqst)
  {

    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($codigo_departamento != "") {

      // Consultamos todas los municipios del departamento
      $q = "SELECT * FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_departamento = '$codigo_departamento' ";
      $result = $pdo->query($q);
      $arr = [];
      $tbl_ciudad_id  = 0;
      foreach ($result as $key => $value) {

        $tbl_ciudad_id = $value['id'];

        $rqst =  array(
          'codigo_departamento' => $codigo_departamento,
          'codigo_muncipio' => $value['codigo_muncipio']
        );

        $result = Estado::getEstadoFactorArmadoSocialEconByMunicipioNUEVO($rqst);
        $puntajeVereda = 0;
        $color = "";
        $actual = "";
        $anterior = "";

        if ($result  && $result['output']['valid']) {
          $puntajeVereda = $result['output']['puntaje'];
          $color = $result['output']['color'];

          $q1 = "UPDATE  " . $db->getTable('tbl_ciudades') . "
                  SET color='" . $color . "' ,
                  puntaje ='" .  $puntajeVereda . "'
                  WHERE id = $tbl_ciudad_id ";
          $result = $pdo->query($q1);
        }
      }

      $arrjson = array('output' => array('valid' => true, 'response' => $codigo_departamento));
      $db->closeConect();
      return $arrjson;
    } else {
      return Util::error_no_result();
    }
  }

  /**
   * Metodo para calcular puntaje, color de la BRIGADA
   */
  public static function getCalcularPuntajesBrigadaId($rqst)
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

        $tbl_brigada_id = $valor['id'];

        // Consulto los municipios de la brigada
        $q1 = "SELECT id, color, puntaje FROM " . $db->getTable('tbl_vereda') . " WHERE tbl_brigada_id = " . $tbl_brigada_id;
        $result1 = $pdo->query($q1);
        $arr1 = array();
        $puntaje = 0;
        $cantidad = 0;
        $promedio = 0;
        if ($result1) {
          foreach ($result1 as $valor1) {
            if ($valor1['puntaje'] > 0) {
              $arr1[] = $valor1;
              $puntaje += $valor1['puntaje'];
            }
          }

          $cantidad = count($arr1);
          if ($cantidad > 0) {
            $promedio = $puntaje / $cantidad;
          }

          // Actualizamos el color y puntaje de la brigada
          $color = Util::getColorByPuntaje($promedio);
          $q1 = "UPDATE  " . $db->getTable('tbl_brigadas') . "
                    SET color ='" . $color . "' ,
                    puntaje ='" .  $promedio . "'
                    WHERE id = $tbl_brigada_id ";
          $result = $pdo->query($q1);

          $arr[] = $valor;
        } else {
          $arrjson = Util::error_no_result();
        }
      }
      $arrjson = array('output' => array('valid' => true, 'respose' => $arr));
      $db->closeConect();
      return $arrjson;
    }
  }

  /**
   * Metodo que me actualiza los puntajes de VEREDA, MUNICIPIO, BRIGADA , DEPARTAMENTO al Ingreso y Actualizacion de datos
   */
  public static function actualizarPuntajesVerMunicBrigDepart($rqst)
  {
    $codigo_departamento =  isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : '';
    $tbl_departamento_id =  isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : '';
    $tbl_municipio_id =  isset($rqst['tbl_municipio_id']) ? intval($rqst['tbl_municipio_id']) : '';
    $tbl_brigada_id =  isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : '';
    $codigo_muncipio =  isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : '';
    $tbl_vereda_id =  isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : '';
    $nombre_vereda =  isset($rqst['vereda']) ? ($rqst['vereda']) : '';


    // VEREDA
    $rqstVereda =  array(
      'codigo_departamento' => $codigo_departamento,
      'codigo_muncipio' => $codigo_muncipio,
      'vereda' => $nombre_vereda
    );

    $resultVereda = Estado::getEstadoFactorArmadoSocialEcon($rqstVereda);

    if ($resultVereda  && $resultVereda['output']['valid']) {
      $puntajeVereda = $resultVereda['output']['puntaje'];
      $color = $resultVereda['output']['color'];

      $paramsUpdateVereda =  array(
        'color' => $color,
        'puntaje_vereda' => $puntajeVereda,
        'tbl_vereda_id' => $tbl_vereda_id
      );

      $responseUpdateVereda = Util::updatePuntajeColorVereda($paramsUpdateVereda);

      if ($responseUpdateVereda  && $responseUpdateVereda['output']['valid']) {

        // MUNICIPIO
        $rqstMunicipio =  array(
          'codigo_departamento' => $codigo_departamento,
          'codigo_muncipio' => $codigo_muncipio
        );
        $resultMunicipio = Estado::getEstadoFactorArmadoSocialEconByMunicipioNUEVO($rqstMunicipio);
        if ($resultMunicipio  && $resultMunicipio['output']['valid']) {
          $puntaje = $resultMunicipio['output']['puntaje'];
          $color = $resultMunicipio['output']['color'];
          $paramsUpdateMunicipio =  array(
            'color' => $color,
            'puntaje' => $puntaje,
            'tbl_ciudad_id' => $tbl_municipio_id
          );
          $responseUpdateMunicipio = Util::updatePuntajeColorMunicipio($paramsUpdateMunicipio);
          if ($responseUpdateMunicipio  && $responseUpdateMunicipio['output']['valid']) {

            // BRIGADA
            include 'EstadoBrigada.php';
            $rqstBrigada =  array('tbl_brigada_id' => $tbl_brigada_id);
            $resultBrigada = EstadoBrigada::getPuntajeByBrigadaId($rqstBrigada);
            if ($resultBrigada) {

              // DEPARTAMENTO
              $rqstDepartamento =  array('departamento_id' => $tbl_departamento_id, 'codigo_departamento' => $codigo_departamento,);
              include 'EstadoDepartamento.php';
              $resultDepartamento = EstadoDepartamento::getPuntajeDepartamento($rqstDepartamento);
            }
          }
        }
      }
    }
  }
}
