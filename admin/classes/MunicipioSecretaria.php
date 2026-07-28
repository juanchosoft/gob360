<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class MunicipioSecretaria
{



  public static function getProyectosPorSecretariaByMunicipioId($rqst)
  {
    $municipioId = isset($rqst['municipioId']) ? intval($rqst['municipioId']) : 0;
    $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    // Consulta principal
    $queryProyectos = "
    SELECT 
        tbl_proyectos.id AS tbl_proyecto_id,
        tbl_secretarias.secretaria, 
        tbl_proyectos.tbl_secretarias_id, 
        tbl_proyectos.valor_proyecto, 
        tbl_proyectos.proyecto, 
        tbl_proyectos.porcentaje_ejecucion, 
        tbl_proyectos.fecha_entrega, 
        tbl_proyectos.estado,
        tbl_proyectos.observaciones
    FROM 
        " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos
    INNER JOIN 
        " . $db->getTable('tbl_secretarias') . " AS tbl_secretarias 
    ON 
        tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id 
    WHERE 
        tbl_proyectos.tbl_municipio_id = $municipioId AND tbl_secretarias.id = $secretariaId
    GROUP BY 
        tbl_secretarias.secretaria, 
        tbl_proyectos.valor_proyecto, 
        tbl_proyectos.proyecto, 
        tbl_proyectos.porcentaje_ejecucion, 
        tbl_proyectos.fecha_entrega
    ";

    // Consulta para obtener las secretarías con sus IDs
    $querySecretarias = "
    SELECT DISTINCT 
        tbl_secretarias.id AS tbl_secretarias_id, 
        tbl_secretarias.secretaria 
    FROM 
        " . $db->getTable('tbl_secretarias') . " AS tbl_secretarias
    INNER JOIN 
        " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos
    ON 
        tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
    WHERE 
        tbl_secretarias.id = $secretariaId
    ";

    // Ejecutar consulta principal
    $resultProyectos = $pdo->query($queryProyectos);
    $proyectos = [];
    if ($resultProyectos) {
      foreach ($resultProyectos as $valor) {
        $proyectos[] = $valor;
      }
    }

    // Ejecutar consulta de secretarías
    $resultSecretarias = $pdo->query($querySecretarias);
    $secretarias = [];
    if ($resultSecretarias) {
      foreach ($resultSecretarias as $valor) {
        $secretarias[] = [
          'id' => $valor['tbl_secretarias_id'],
          'nombre' => $valor['secretaria']
        ];
      }
    }

    // Construir la respuesta
    if (!empty($proyectos)) {
      $arrjson = [
        'output' => [
          'valid' => true,
          'response' => [
            'proyectos' => $proyectos,
            'secretarias' => $secretarias,
          ]
        ]
      ];
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

  /**
   * Método para obtener proyectos de ALCALDÍAS por secretaría y municipio
   * Incluye filtro opcional por vereda
   * @param array $rqst - municipioId, secretariaId, veredaId (opcional)
   * @return array
   */
  public static function getProyectosAlcaldiaPorSecretaria($rqst)
  {
    $municipioId = isset($rqst['municipioId']) ? intval($rqst['municipioId']) : 0;
    $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;
    $veredaId = isset($rqst['veredaId']) ? intval($rqst['veredaId']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    // Construir condición WHERE - Usar tbl_secretarias_municipios para alcaldías
    $whereConditions = "tbl_proyectos_alcaldias.tbl_municipio_id = $municipioId
                        AND tbl_secretarias_municipios.id = $secretariaId";

    if ($veredaId > 0) {
      $whereConditions .= " AND tbl_proyectos_alcaldias.tbl_vereda_id = $veredaId";
    }

    // Consulta principal con información de vereda - usando tbl_secretarias_municipios
    $queryProyectos = "
    SELECT
        tbl_proyectos_alcaldias.id AS tbl_proyecto_id,
        tbl_secretarias_municipios.secretaria,
        tbl_proyectos_alcaldias.tbl_secretarias_id,
        tbl_proyectos_alcaldias.valor_proyecto,
        tbl_proyectos_alcaldias.proyecto,
        tbl_proyectos_alcaldias.porcentaje_ejecucion,
        tbl_proyectos_alcaldias.fecha_entrega,
        tbl_proyectos_alcaldias.estado,
        tbl_proyectos_alcaldias.observaciones,
        tbl_vereda.nombre_vereda,
        tbl_vereda.id AS vereda_id
    FROM
        " . $db->getTable('tbl_proyectos_alcaldias') . " AS tbl_proyectos_alcaldias
    INNER JOIN
        " . $db->getTable('tbl_secretarias_municipios') . " AS tbl_secretarias_municipios
    ON
        tbl_proyectos_alcaldias.tbl_secretarias_id = tbl_secretarias_municipios.id
    LEFT JOIN
        " . $db->getTable('tbl_vereda') . " AS tbl_vereda
    ON
        tbl_proyectos_alcaldias.tbl_vereda_id = tbl_vereda.id
    WHERE
        $whereConditions
    ORDER BY
        tbl_vereda.nombre_vereda ASC,
        tbl_proyectos_alcaldias.proyecto ASC
    ";

    // Ejecutar consulta principal
    $resultProyectos = $pdo->query($queryProyectos);
    $proyectos = [];
    if ($resultProyectos) {
      foreach ($resultProyectos as $valor) {
        $proyectos[] = $valor;
      }
    }

    // Obtener información de la secretaría municipal
    $querySecretaria = "SELECT id, secretaria FROM " . $db->getTable('tbl_secretarias_municipios') . " WHERE id = $secretariaId";
    $resultSecretaria = $pdo->query($querySecretaria);
    $secretaria = $resultSecretaria ? $resultSecretaria->fetch(PDO::FETCH_ASSOC) : null;

    // Obtener información de la vereda si se filtró
    $veredaInfo = null;
    if ($veredaId > 0) {
      $queryVereda = "SELECT id, nombre_vereda FROM " . $db->getTable('tbl_vereda') . " WHERE id = $veredaId";
      $resultVereda = $pdo->query($queryVereda);
      $veredaInfo = $resultVereda ? $resultVereda->fetch(PDO::FETCH_ASSOC) : null;
    }

    // Construir la respuesta
    if (!empty($proyectos)) {
      $arrjson = [
        'output' => [
          'valid' => true,
          'response' => [
            'proyectos' => $proyectos,
            'secretaria' => $secretaria,
            'vereda' => $veredaInfo,
            'total_proyectos' => count($proyectos)
          ]
        ]
      ];
    } else {
      $arrjson = [
        'output' => [
          'valid' => true,
          'response' => [
            'proyectos' => [],
            'secretaria' => $secretaria,
            'vereda' => $veredaInfo,
            'total_proyectos' => 0
          ]
        ]
      ];
    }
    $db->closeConect();
    return $arrjson;
  }

  /**
   * Metodo para mostrar la informción de los proyectos por secretaria en municipios_secretarias_tic
   */
  public static function getProyectosPorSecretariaByMunicipioIdBySecretariaId($rqst)
  {
    $municipioId = isset($rqst['municipioId']) ? intval($rqst['municipioId']) : 0;
    $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    // Consulta principal
    $queryProyectos = "
    SELECT 
        tbl_proyectos.id AS tbl_proyecto_id,
        tbl_secretarias.secretaria, 
        tbl_proyectos.tbl_secretarias_id, 
        tbl_proyectos.valor_proyecto, 
        tbl_proyectos.proyecto, 
        tbl_proyectos.porcentaje_ejecucion, 
        tbl_proyectos.fecha_entrega, 
        tbl_proyectos.estado 
    FROM 
        " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos
    INNER JOIN 
        " . $db->getTable('tbl_secretarias') . " AS tbl_secretarias 
    ON 
        tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id 
    WHERE 
        tbl_proyectos.tbl_municipio_id = $municipioId AND tbl_secretarias.id = $secretariaId
    GROUP BY 
        tbl_secretarias.secretaria, 
        tbl_proyectos.valor_proyecto, 
        tbl_proyectos.proyecto, 
        tbl_proyectos.porcentaje_ejecucion, 
        tbl_proyectos.fecha_entrega
    ";

   
    // Consulta para obtener las secretarías con sus IDs
    $querySecretarias = "
    SELECT DISTINCT 
        tbl_secretarias.id AS tbl_secretarias_id, 
        tbl_secretarias.secretaria 
    FROM 
        " . $db->getTable('tbl_secretarias') . " AS tbl_secretarias
    INNER JOIN 
        " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos
    ON 
        tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
    WHERE 
        tbl_proyectos.tbl_municipio_id = $municipioId AND tbl_secretarias.id = $secretariaId
    ";

    // Ejecutar consulta principal
    $resultProyectos = $pdo->query($queryProyectos);
    $proyectos = [];
    if ($resultProyectos) {
      foreach ($resultProyectos as $valor) {
        $proyectos[] = $valor;
      }
    }

    // Ejecutar consulta de secretarías
    $resultSecretarias = $pdo->query($querySecretarias);
    $secretarias = [];
    if ($resultSecretarias) {
      foreach ($resultSecretarias as $valor) {
        $secretarias[] = [
          'id' => $valor['tbl_secretarias_id'],
          'nombre' => $valor['secretaria']
        ];
      }
    }

    // Construir la respuesta
    if (!empty($proyectos)) {
      $arrjson = [
        'output' => [
          'valid' => true,
          'response' => [
            'proyectos' => $proyectos,
            'secretarias' => $secretarias,
          ]
        ]
      ];
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }
}
