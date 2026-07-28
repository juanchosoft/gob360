<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class MainTic
{
  public function __construct() {}

  /**
   * Obtiene información consolidada para el panel principal de visualización TIC.
   *
   * Esta función recopila y retorna datos agregados a nivel de municipio relacionados con:
   * - Robótica educativa.
   * - Computadores por institución.
   * - Computadores por alumno.
   * - Laboratorios de innovación.
   * - Inversión total por proyectos TIC según la secretaría correspondiente.
   * - Valor total de proyectos TIC del municipio o departamento.
   * 
   * Dependiendo del parámetro 'opción', la función calcula un indicador específico para mostrar en el mapa:
   * - `robotica`: Total de robótica por municipio.
   * - `computadores_institucion`: Total de computadores por institución.
   * - `computador_alumno`: Total de computadores por alumno.
   * - `laboratorio_innovacion`: Total de laboratorios de innovación.
   * - `todos`: Suma de todos los anteriores.
   * - `contratos`: Cuenta la cantidad de contratos (proyectos) por municipio y suma el valor total de los proyectos.
   *
   * @param array $rqst Arreglo asociativo con los siguientes posibles parámetros:
   *                    - 'codigoMunicipio': Código del municipio específico o 'todos' para incluir todos los municipios.
   *                    - 'opcion': Tipo de dato a mostrar en el mapa ('robotica', 'computadores_institucion', 'computador_alumno', 
   *                                 'laboratorio_innovacion', 'todos', 'contratos').
   *
   * @return array Retorna un arreglo JSON con:
   *               - 'robotica': Total de robótica.
   *               - 'institucion': Total de computadores por institución.
   *               - 'alumno': Total de computadores por alumno.
   *               - 'laboratorio': Total de laboratorios de innovación.
   *               - 'inversionsec': Inversión total de la secretaría filtrada.
   *               - 'valorproyectos': Valor total de los proyectos.
   *               - 'response': Datos agregados por municipio según la opción seleccionada.
   */
  public static function getDataMain($rqst)
  {
    $secretariaId = Util::getSecretariaIdTIC();
    $departamentoId = Util::getDepartamentoPrincipal();
    $codigoMunicipio = isset($rqst['codigoMunicipio']) ? ($rqst['codigoMunicipio']) : '';
    $opcion = isset($rqst['opcion']) ? trim($rqst['opcion']) : 'robotica';

    if ($codigoMunicipio == "" || $codigoMunicipio == 'seleccione') {
      return Util::error_missing_data();
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    $robotica = 0;
    $inversionsec = 0;
    $valorproyectos = 0;
    $institucion = 0;
    $alumno = 0;
    $laboratorio = 0;
    $inversionsec = 0;
    $valorproyectos = 0;

    $tablaMunicipios = $db->getTable('tbl_ciudades_accion_unificada');

    if ($codigoMunicipio == "todos") {
      $join = "INNER JOIN $tablaMunicipios m ON p.tbl_municipio_id = m.codigo_muncipio";
      $where = "m.codigo_departamento = '$departamentoId'";
    } else {
      $codigoMunicipio = (int)$codigoMunicipio;
      $join = "";
      $where = "p.tbl_municipio_id = $codigoMunicipio";
    }

    // Consulta 1: Total de robotica
    $q1 = "SELECT SUM(robotica) AS total_robotica
              FROM " . $db->getTable('tbl_pctic') . " p $join WHERE $where";
    $robotica = $pdo->query($q1)->fetchColumn();

    // Consulta 2: Total de computadores institucion
    $q2 = "SELECT SUM(computadores_institucion) AS total_institucion FROM " . $db->getTable('tbl_pctic') . " p $join WHERE $where";
    $institucion = $pdo->query($q2)->fetchColumn();

    // Consulta 3: Total de computador_alumno
    $q3 = "SELECT SUM(computador_alumno) AS total_alumno FROM " . $db->getTable('tbl_pctic') . " p $join WHERE $where";
    $alumno = $pdo->query($q3)->fetchColumn();

    // Consulta 2: Total de laboratorio_innovacion
    $q4 = "SELECT SUM(laboratorio_innovacion) AS total_laboratorio FROM " . $db->getTable('tbl_pctic') . " p $join WHERE $where";
    $laboratorio = $pdo->query($q4)->fetchColumn();

    // Consulta 5: Inversión por secretarías
    $q5 = "SELECT SUM(tbl_proyectos.valor_proyecto) AS inversionsec 
          FROM " . $db->getTable('tbl_proyectos') . " 
          INNER JOIN " . $db->getTable('tbl_secretarias') . " 
          ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id 
          WHERE " . ($codigoMunicipio == "todos"
      ? "tbl_proyectos.tbl_departamento_id = '$departamentoId'"
      : "tbl_proyectos.tbl_municipio_id = $codigoMunicipio");
    $inversionsec = $pdo->query($q5)->fetchColumn();

    // Consulta 6: Valor total de proyectos
    $q6 = "SELECT SUM(valor_proyecto) AS valorproyectos FROM " . $db->getTable('tbl_proyectos') . "  
      WHERE " . ($codigoMunicipio == "todos"
      ? "tbl_proyectos.tbl_departamento_id = '$departamentoId'"
      : "tbl_proyectos.tbl_municipio_id = $codigoMunicipio");
    $valorproyectos = $pdo->query($q6)->fetchColumn();

    // Determinar el campo a sumar según la opción recibida
    $campoCantidad = match ($opcion) {
      'robotica' => 'IFNULL(SUM(tbl_pctic.robotica), 0)',
      'computadores_institucion' => 'IFNULL(SUM(tbl_pctic.computadores_institucion), 0)',
      'computador_alumno' => 'IFNULL(SUM(tbl_pctic.computador_alumno), 0)',
      'laboratorio_innovacion' => 'IFNULL(SUM(tbl_pctic.laboratorio_innovacion), 0)',
      'todos' => 'IFNULL(SUM(tbl_pctic.robotica), 0) + 
      IFNULL(SUM(tbl_pctic.computadores_institucion), 0) + 
      IFNULL(SUM(tbl_pctic.computador_alumno), 0) + 
      IFNULL(SUM(tbl_pctic.laboratorio_innovacion), 0)',
      default => '0'
    };

    $queryMapa = "SELECT
          tbl_ciudades_accion_unificada.*,
          IFNULL(SUM(tbl_pctic.robotica), 0) AS total_robotica,
          IFNULL(SUM(tbl_pctic.computadores_institucion), 0) AS total_computadores_institucion,
          IFNULL(SUM(tbl_pctic.computador_alumno), 0) AS total_computador_alumno,
          IFNULL(SUM(tbl_pctic.laboratorio_innovacion), 0) AS total_laboratorio_innovacion,
          $campoCantidad AS cantidad_mostrar
        FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
        LEFT JOIN " . $db->getTable('tbl_pctic') . "
          ON tbl_pctic.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
        WHERE tbl_ciudades_accion_unificada.codigo_departamento = :departamentoId
        GROUP BY tbl_ciudades_accion_unificada.codigo_muncipio";

    $stmt = $pdo->prepare($queryMapa);
    $stmt->bindParam(':departamentoId', $departamentoId);
    $stmt->execute();
    $informacionMapa = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($opcion == 'contratos') {
      $queryConteoProyectos = " SELECT 
        tbl_ciudades_accion_unificada.*,
        COUNT(CASE WHEN tbl_secretarias.id = :secretariaId THEN tbl_proyectos.id END) AS cantidad_mostrar,
        SUM(CASE WHEN tbl_secretarias.id = :secretariaId THEN IFNULL(tbl_proyectos.valor_proyecto, 0) ELSE 0 END) AS total_valor_proyectos
    FROM  " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada
    LEFT JOIN 
        " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos 
        ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
    LEFT JOIN 
       " . $db->getTable('tbl_secretarias') . " AS tbl_secretarias 
        ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
    WHERE 
        tbl_ciudades_accion_unificada.codigo_departamento = :departamentoId
    GROUP BY tbl_ciudades_accion_unificada.codigo_muncipio
    ORDER BY cantidad_mostrar DESC;
  ";
      $stmtProyectos = $pdo->prepare($queryConteoProyectos);
      $stmtProyectos->bindParam(':secretariaId', $secretariaId, PDO::PARAM_INT);
      $stmtProyectos->bindParam(':departamentoId', $departamentoId, PDO::PARAM_STR);
      $stmtProyectos->execute();
      $informacionMapa = $stmtProyectos->fetchAll(PDO::FETCH_ASSOC);
    }

    $arrjson = array(
      'output' => array(
        'valid' => true,
        'robotica' => $robotica,
        'institucion' => $institucion,
        'alumno' => $alumno,
        'laboratorio' => $laboratorio,
        'inversionsec' => $inversionsec,
        'valorproyectos' => $valorproyectos,
        'response' => $informacionMapa
      )
    );

    $db->closeConect();
    return $arrjson;
  }
}
