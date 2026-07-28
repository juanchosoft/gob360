<?php
// Obteniendo la fecha actual con hora, minutos y segundos en PHP
$fechaActual = date('d-m-Y H:i:s');
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Maing
{
  public function __construct() {}

    /**
     * Metodo para recuperar todos los registros
     * @return array 
     */
    public static function getDataMain($rqst)
    {
      $db = new DbConection();
      $pdo = $db->openConect();

      $modulo = is_array($rqst) ? ($rqst['modulo'] ?? '') : '';
      if ($modulo === 'aspas') {
        $filtroTipo = "tipo_actividad = 'aspas'";
      } else {
        // gestora / primera_dama / default
        $filtroTipo = "(tipo_actividad = 'primera_dama' OR tipo_actividad IS NULL OR tipo_actividad = '')";
      }

      // Inicialización de variables
      $lideres = 0;
      $visitas = 0;
      $municipios = 0;
      $veredas = 0;
      $impactada = 0;
      $provincia = 0;
      $inversion = 0;
      $visitasmun = 0;
      $valorproyectos = 0;

      // Consulta 1: Población impactada
      $q1 = "SELECT SUM(poblacion) AS impactada FROM " . $db->getTable('tbl_gestora') . " WHERE {$filtroTipo}";
      $impactada = $pdo->query($q1)->fetchColumn();

      // Consulta 2: Total de visitas realizadas
      $q2 = "SELECT COUNT(id) AS cuenta_visitas FROM " . $db->getTable('tbl_gestora') . " WHERE {$filtroTipo}";
      $visitas = $pdo->query($q2)->fetchColumn();
      $compromiso = $visitas;

      // Consulta 3: Total de municipios
      $q3 = "SELECT COUNT(DISTINCT tbl_municipio_id) AS total_municipios FROM " . $db->getTable('tbl_visitas');
      $municipios = $pdo->query($q3)->fetchColumn();

      // Consulta 4: Total de provincias
      $q4 = "SELECT COUNT(DISTINCT provincia) AS total_provincias FROM " . $db->getTable('tbl_visitas');
      $provincia = $pdo->query($q4)->fetchColumn();

      // Consulta 5: Inversión por secretarías
      $q5 = "SELECT SUM(tbl_proyectos.valor_proyecto) AS inversionsec
            FROM " . $db->getTable('tbl_proyectos') . " 
            INNER JOIN " . $db->getTable('tbl_secretarias') . " 
            ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id";
      $inversionsec = $pdo->query($q5)->fetchColumn();

      // Consulta 6: Inversión gestora
      $q6 = "SELECT SUM(inversion) AS inversion FROM " . $db->getTable('tbl_gestora') . " WHERE {$filtroTipo}";
      $inversion = $pdo->query($q6)->fetchColumn();

      // Cálculo de porcentajes
      $porcentaje_veredas = ($veredas * 100 / 34792);
      $porcentaje_municipios = ($municipios * 100 / 1103);

      $arrjson = array(
        'output' => array(
          'valid' => true,
          'impactada' => $impactada,
          'visitas' => $visitas,
          'visitasmun' => $visitasmun,
          'provincia' => $provincia,
          'inversion' => $inversion,
          'valorproyectos' => $valorproyectos,
          'compromiso' => $compromiso,
          'municipios' => $municipios,
          'veredas' => $veredas,
          'porcentaje_veredas' => $porcentaje_veredas,
          'porcentaje_municipios' => $porcentaje_municipios
        )
      );

      $db->closeConect();
      return $arrjson;
    }

    /**
     * Metodo que obtiene el total de visitas por mes del departamento
     */
    public static function getTotalVisitasPorMesAMunicipios($rqst)
    {
      $departamentoCodigo = 68;

      $db = new DbConection();
      $pdo = $db->openConect();

      // Consultas número de visitas por mes del departamento
      $q = "SELECT  DATE_FORMAT(v.date, '%M') AS mes, COUNT(v.id) AS total_visitas
      FROM " . $db->getTable('tbl_visitas') . " v
      INNER JOIN " . $db->getTable('tbl_ciudades') . " c 
      ON c.codigo_muncipio = v.tbl_municipio_id
      WHERE c.codigo_departamento = :departamentoCodigo
      GROUP BY YEAR(v.date), MONTH(v.date)
      ORDER BY YEAR(v.date), MONTH(v.date) ASC";
      $stmt = $pdo->prepare($q);
      $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
      $stmt->execute();
      $arrTotalVisitasPorMesAMunicipios = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $arrjson = array(
        'output' => array(
          'valid' => true,
          'response' => $arrTotalVisitasPorMesAMunicipios,
        )
      );

      $db->closeConect();
      return $arrjson;
    }

    /**
     * Metodo para sacar el total de visitas por provincia
     */
    public static function getTotalVisitasPorProvincias($rqst)
    {
      $departamentoCodigo = 68;

      $db = new DbConection();
      $pdo = $db->openConect();

      // Consultas número de visitas por mes del departamento
      $q = "SELECT provincia, COUNT(*) AS total_visitas
          FROM  " . $db->getTable('tbl_visitas') . "
          WHERE tbl_departamento_id = :departamentoCodigo
          GROUP BY 
              provincia
          ORDER BY 
              provincia";
      $stmt = $pdo->prepare($q);
      $stmt->bindParam(':departamentoCodigo', $departamentoCodigo, PDO::PARAM_STR);
      $stmt->execute();
      $arrTotalVisitasProvincia = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $arrjson = array(
        'output' => array(
          'valid' => true,
          'response' => $arrTotalVisitasProvincia,
        )
      );

      $db->closeConect();
      return $arrjson;
    }
}
