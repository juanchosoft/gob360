<?php

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Vereda
{

  public function __construct() {}

  /**
   * Metodo para actualizar la descripcion de la vereda
   */
  public static function updateDescripcionVereda($rqst)
  {
    $codigo_departamento = isset($rqst['codigo_departamento']) ? ($rqst['codigo_departamento']) : '';
    $codigo_muncipio = isset($rqst['codigo_muncipio']) ? ($rqst['codigo_muncipio']) : '';
    $nombre_vereda = isset($rqst['nombre_vereda']) ? ($rqst['nombre_vereda']) : '';
    $observaciones = isset($rqst['observaciones']) ? ($rqst['observaciones']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    $q1 = "UPDATE  " . $db->getTable('tbl_vereda') . "
      SET observaciones='" . $observaciones . "'
      WHERE departamento_id = $codigo_departamento AND municipio_id = $codigo_muncipio  AND nombre_vereda = '$nombre_vereda' LIMIT 1 ";

    $result = $pdo->query($q1);

    $arrjson = array('output' => array('valid' => true, 'response' => $nombre_vereda));
    $db->closeConect();
    return $arrjson;
  }

  public static function getVeredasInfo($rqst)
  {
    $db = new DbConection();
    $pdo = $db->openConect();

    $q = "SELECT tbl_batallones.sigla AS batallon, tbl_brigadas.sigla AS brigada, tbl_departamentos.departamento, tbl_ciudades.municipio, tbl_vereda.nombre_vereda, tbl_vereda.id
        FROM (((" . $db->getTable('tbl_vereda') . " 
        INNER JOIN " . $db->getTable('tbl_brigadas') . "   ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id) 
        INNER JOIN " . $db->getTable('tbl_batallones') . "   ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_departamentos') . "   ON tbl_vereda.departamento_id = tbl_departamentos.codigo_departamento) 
        INNER JOIN " . $db->getTable('tbl_ciudades') . "   ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio ";
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
   * Obtener la informacion de las veredas
   */
  public static function getAll($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $municipio_id = isset($rqst['municipio_id']) ? intval($rqst['municipio_id']) : 0;

    $db = new DbConection();
    $pdo = $db->openConect();

    try {
      // Construir la consulta base
      $query = "SELECT * FROM " . $db->getTable('tbl_vereda');
      $params = [];

      // Agregar condiciones WHERE si es necesario
      if ($id > 0) {
        $query .= " WHERE id = :id";
        $params[':id'] = $id;
      } elseif ($municipio_id > 0) {
        $query .= " WHERE municipio_id = :municipio_id";
        $params[':municipio_id'] = $municipio_id;
      }

      // Agregar ORDER BY con el nombre correcto de la columna
      $query .= " ORDER BY nombre_vereda ASC"; // Asegúrate de que esta es la columna correcta

      // Preparar y ejecutar la consulta
      $stmt = $pdo->prepare($query);
      $stmt->execute($params);
      $veredas = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Si se solicita información del municipio
      $municipioData = [];
      if ($municipio_id > 0) {
        $stmtMunicipio = $pdo->prepare("SELECT * FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio = :municipio_id");
        $stmtMunicipio->execute([':municipio_id' => $municipio_id]);
        $municipioData = $stmtMunicipio->fetchAll(PDO::FETCH_ASSOC);
      }

      // Construir la respuesta
      $response = [
        'output' => [
          'valid' => true,
          'response' => $veredas,
          'municipio' => $municipioData
        ]
      ];
    } catch (Exception $e) {
      $response = Util::error_general($e->getMessage());
    } finally {
      $db->closeConect();
    }

    return $response;
  }
 public static function guardarCompromiso($rqst)
  {
    if (empty($rqst['cantidad']) || empty($rqst['actor'])) {
      return ['output' => ['valid' => false, 'response' => '❌ Faltan datos obligatorios']];
    }

    $cantidad = intval($rqst['cantidad']);
    $actor = intval($rqst['actor']);
    $observaciones = isset($rqst['observaciones']) ? trim($rqst['observaciones']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();

    try {
      $query = "INSERT INTO tbl_compromisos_pilares_factores (cantidad, tbl_actor_id, observaciones) 
                VALUES (:cantidad, :actor, :observaciones)";
      $stmt = $pdo->prepare($query);
      $stmt->execute([
        ':cantidad' => $cantidad,
        ':actor' => $actor,
        ':observaciones' => $observaciones
      ]);

      $idInsertado = $pdo->lastInsertId(); 

      return [
        'output' => [
          'valid' => true,
          'response' => '✅ Compromiso guardado correctamente',
          'id' => $idInsertado
        ]
      ];
    } catch (Exception $e) {
      return ['output' => ['valid' => false, 'response' => "❌ Error en la base de datos: " . $e->getMessage()]];
    } finally {
      $db->closeConect();
    }
  }



  /**
   * Informacion de las veredas que no tienen datos ingresados
   */
  public static function getVeredasSinDatos($rqst)
  {

    $social = isset($rqst['social']) ? ($rqst['social']) : 'no';
    $economico = isset($rqst['economico']) ? ($rqst['economico']) : 'no';
    $armado = isset($rqst['armado']) ? ($rqst['armado']) : 'no';

    $db = new DbConection();
    $pdo = $db->openConect();

    //Social
    if ($social === 'si') {
      $q = "SELECT tbl_vereda.id, tbl_vereda.nombre_vereda, tbl_vereda.codigo_vereda, tbl_ciudades.municipio
          FROM " . $db->getTable('tbl_vereda') . "," . $db->getTable('tbl_ciudades') . "
          WHERE tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio AND
          tbl_vereda.id NOT IN (SELECT vereda_id FROM " . $db->getTable('tbl_resultados_social') . "  )";
    }

    //Economico
    if ($economico === 'si') {
      $q = "SELECT tbl_vereda.id, tbl_vereda.nombre_vereda, tbl_vereda.codigo_vereda, tbl_ciudades.municipio
          FROM " . $db->getTable('tbl_vereda') . "," . $db->getTable('tbl_ciudades') . "
          WHERE tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio AND
          tbl_vereda.id NOT IN (SELECT vereda_id FROM " . $db->getTable('tbl_resultados_economico') . "  )";
    }

    //Armado
    if ($armado === 'si') {
      $q = "SELECT tbl_vereda.id, tbl_vereda.nombre_vereda, tbl_vereda.codigo_vereda, tbl_ciudades.municipio
          FROM " . $db->getTable('tbl_vereda') . "," . $db->getTable('tbl_ciudades') . "
          WHERE tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio AND
          tbl_vereda.id NOT IN (SELECT vereda_id FROM " . $db->getTable('tbl_resultados_armado') . "  )";
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
   * Veredas críticas: puntaje inicial en rangos Medio, Alto o Crítico del factor seleccionado.
   */
  public static function veredasCriticasCONSULTA($rqst)
  {
    require_once __DIR__ . '/FactoresInestabilidadGeneral.php';

    $codigoDepartamento = isset($rqst['departamento']) ? intval($rqst['departamento']) : 0;
    $codigoMunicipio = isset($rqst['municipio']) ? intval($rqst['municipio']) : 0;
    $inestabilidadId = isset($rqst['inestabilidad']) ? intval($rqst['inestabilidad']) : 0;

    if (empty($codigoDepartamento) || empty($codigoMunicipio) || empty($inestabilidadId)) {
      return [
        'output' => [
          'valid' => false,
          'response' => [
            'code' => '103',
            'content' => 'Faltan datos que son requeridos.',
          ],
        ],
      ];
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    try {
      $stmtMun = $pdo->prepare(
        'SELECT municipio FROM ' . $db->getTable('tbl_ciudades') . '
         WHERE codigo_departamento = :depto AND codigo_muncipio = :mun LIMIT 1'
      );
      $stmtMun->bindValue(':depto', $codigoDepartamento, PDO::PARAM_INT);
      $stmtMun->bindValue(':mun', $codigoMunicipio, PDO::PARAM_INT);
      $stmtMun->execute();
      $filaMun = $stmtMun->fetch(PDO::FETCH_ASSOC);
      $nombreMunicipio = $filaMun['municipio'] ?? '';

      $puntajesInicial = FactoresInestabilidadGeneral::getPuntajes(
        $inestabilidadId,
        FactoresInestabilidadGeneral::TIPO_PUNTAJE_INICIAL
      );
      $rangosCriticos = self::rangosCriticosInicialDesdePuntajes($puntajesInicial);

      if (empty($rangosCriticos)) {
        return Util::error_general('No hay rangos Medio, Alto o Crítico configurados para este factor.');
      }

      $mapParams = [
        'codigo_municipio' => $codigoMunicipio,
        'inestabilidadId' => $inestabilidadId,
      ];
      $dataInicial = FactoresInestabilidadGeneral::calcularColorVeredasInicial($mapParams);
      $dataActual = FactoresInestabilidadGeneral::calcularColorVeredasActual($mapParams);
      $veredasInicial = $dataInicial['output']['valid'] ? $dataInicial['output']['response'] : [];
      $veredasActual = $dataActual['output']['valid'] ? $dataActual['output']['response'] : [];

      $porId = [];
      foreach ($veredasInicial as $vereda) {
        $id = intval($vereda['id'] ?? 0);
        if ($id <= 0) {
          continue;
        }
        $porId[$id] = [
          'id' => $id,
          'nombre_vereda' => $vereda['nombre_vereda'] ?? '',
          'municipio' => $nombreMunicipio,
          'puntaje_inicial' => round((float) ($vereda['cantidad'] ?? 0), 2),
          'color_inicial' => $vereda['color_calculado'] ?? Util::getColorNeutroMapa(),
          'puntaje_actual' => 0.0,
          'color_actual' => Util::getColorNeutroMapa(),
        ];
      }
      foreach ($veredasActual as $vereda) {
        $id = intval($vereda['id'] ?? 0);
        if ($id <= 0) {
          continue;
        }
        if (!isset($porId[$id])) {
          $porId[$id] = [
            'id' => $id,
            'nombre_vereda' => $vereda['nombre_vereda'] ?? '',
            'municipio' => $nombreMunicipio,
            'puntaje_inicial' => 0.0,
            'color_inicial' => Util::getColorNeutroMapa(),
            'puntaje_actual' => 0.0,
            'color_actual' => Util::getColorNeutroMapa(),
          ];
        }
        $porId[$id]['puntaje_actual'] = round((float) ($vereda['cantidad'] ?? 0), 2);
        $porId[$id]['color_actual'] = $vereda['color_calculado'] ?? Util::getColorNeutroMapa();
        if ($porId[$id]['nombre_vereda'] === '') {
          $porId[$id]['nombre_vereda'] = $vereda['nombre_vereda'] ?? '';
        }
      }

      $resultado = [];
      foreach ($porId as $vereda) {
        if (self::puntajeEnRangosCriticos((float) $vereda['puntaje_inicial'], $rangosCriticos)) {
          $resultado[] = $vereda;
        }
      }

      usort($resultado, fn($a, $b) => strcasecmp($a['nombre_vereda'], $b['nombre_vereda']));

      return ['output' => ['valid' => true, 'response' => $resultado]];
    } catch (Exception $e) {
      return Util::error_general('Error consultando veredas críticas: ' . $e->getMessage());
    } finally {
      $db->closeConect();
    }
  }

  private static function normalizarNombreRangoPuntaje(string $nombre): string
  {
    $nombre = strtolower(trim($nombre));
    $nombre = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $nombre);
    return $nombre;
  }

  private static function rangosCriticosInicialDesdePuntajes(array $puntajes): array
  {
    $nombresPermitidos = ['medio', 'alto', 'critico'];
    $rangos = [];

    foreach ($puntajes as $puntaje) {
      $nombre = self::normalizarNombreRangoPuntaje($puntaje['name'] ?? '');
      if (!in_array($nombre, $nombresPermitidos, true)) {
        continue;
      }
      $rangos[] = [
        'name' => $puntaje['name'] ?? '',
        'desde' => (float) ($puntaje['rango_desde'] ?? 0),
        'hasta' => (float) ($puntaje['rango_hasta'] ?? 0),
      ];
    }

    return $rangos;
  }

  private static function puntajeEnRangosCriticos(float $puntaje, array $rangos): bool
  {
    foreach ($rangos as $rango) {
      if ($puntaje >= $rango['desde'] && $puntaje <= $rango['hasta']) {
        return true;
      }
    }
    return false;
  }
  // ─────────────────────────────────────────────────────────────────────────
  // MÓDULO GESTIÓN VEREDAS (departamento 68 – Santander)
  // ─────────────────────────────────────────────────────────────────────────

  /**
   * Retorna todos los municipios de Santander (departamento_id = 68)
   * para poblar el select de filtro.
   */
  public static function getMunicipiosSantander()
  {
    $db = new DbConection();
    $pdo = $db->openConect();
    try {
      $stmt = $pdo->prepare(
        "SELECT codigo_muncipio AS id, municipio
         FROM " . $db->getTable('tbl_ciudades') . "
         WHERE codigo_departamento = '68'
         ORDER BY municipio ASC"
      );
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return ['output' => ['valid' => true, 'response' => $data]];
    } catch (Exception $e) {
      return Util::error_general($e->getMessage());
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Lista paginada de veredas de Santander con filtro opcional por municipio
   * y búsqueda por nombre / código.
   * Devuelve formato DataTables server-side.
   */
  public static function getVeredasAdmin($rqst)
  {
    $draw       = intval($rqst['draw']   ?? 1);
    $start      = intval($rqst['start']  ?? 0);
    $length     = intval($rqst['length'] ?? 10);
    $search     = trim($rqst['search']   ?? '');
    $municipioId = intval($rqst['municipio_id'] ?? 0);

    $db  = new DbConection();
    $pdo = $db->openConect();
    try {
      $tblV = $db->getTable('tbl_vereda');
      $tblC = $db->getTable('tbl_ciudades');

      $where  = "v.departamento_id = 68";
      $params = [];

      if ($municipioId > 0) {
        $where .= " AND v.municipio_id = :mun";
        $params[':mun'] = $municipioId;
      }

      if ($search !== '') {
        $where .= " AND (v.nombre_vereda LIKE :q OR v.codigo_vereda LIKE :q)";
        $params[':q'] = "%{$search}%";
      }

      // Total sin paginación
      $stmtTotal = $pdo->prepare(
        "SELECT COUNT(*) FROM $tblV v WHERE $where"
      );
      $stmtTotal->execute($params);
      $total = (int)$stmtTotal->fetchColumn();

      // Datos paginados
      $sql = "SELECT v.id, v.codigo_vereda, v.nombre_vereda,
                     v.municipio_id, c.municipio,
                     v.hombres, v.mujeres, v.total,
                     v.habilitada_para_votar, v.observaciones
              FROM $tblV v
              LEFT JOIN $tblC c ON c.codigo_muncipio = v.municipio_id
              WHERE $where
              ORDER BY c.municipio ASC, v.nombre_vereda ASC
              LIMIT :limit OFFSET :offset";

      $stmt = $pdo->prepare($sql);
      foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
      }
      $stmt->bindValue(':limit',  $length, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $start,  PDO::PARAM_INT);
      $stmt->execute();
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return [
        'draw'            => $draw,
        'recordsTotal'    => $total,
        'recordsFiltered' => $total,
        'data'            => $rows,
      ];
    } catch (Exception $e) {
      return ['draw' => $draw, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Retorna una sola vereda por ID (para formulario de edición).
   */
  public static function getVeredaById($rqst)
  {
    $id = intval($rqst['id'] ?? 0);
    if ($id <= 0) {
      return ['output' => ['valid' => false, 'response' => 'ID inválido']];
    }
    $db  = new DbConection();
    $pdo = $db->openConect();
    try {
      $stmt = $pdo->prepare(
        "SELECT v.*, c.municipio
         FROM " . $db->getTable('tbl_vereda') . " v
         LEFT JOIN " . $db->getTable('tbl_ciudades') . " c ON c.codigo_muncipio = v.municipio_id
         WHERE v.id = :id LIMIT 1"
      );
      $stmt->execute([':id' => $id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$row) {
        return ['output' => ['valid' => false, 'response' => 'Vereda no encontrada']];
      }
      return ['output' => ['valid' => true, 'response' => $row]];
    } catch (Exception $e) {
      return Util::error_general($e->getMessage());
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Genera el siguiente código de vereda para un municipio dado.
   * Formato: {codigo_municipio}{consecutivo 3 dígitos con cero a la izquierda}
   * Ejemplo: municipio 68572 con 8 veredas → 68572009
   */
  public static function generarCodigoVereda($pdo, $tblVereda, $municipioId)
  {
    $stmt = $pdo->prepare(
      "SELECT MAX(CAST(SUBSTRING(codigo_vereda, LENGTH(:mun) + 1) AS UNSIGNED)) AS ultimo
       FROM $tblVereda
       WHERE municipio_id = :mun AND codigo_vereda LIKE :like"
    );
    $like = $municipioId . '%';
    $stmt->execute([':mun' => $municipioId, ':like' => $like]);
    $ultimo      = (int)($stmt->fetchColumn() ?? 0);
    $siguiente   = $ultimo + 1;
    return $municipioId . str_pad($siguiente, 3, '0', STR_PAD_LEFT);
  }

  /**
   * Devuelve el siguiente código que se asignaría a una vereda del municipio dado.
   * Usado por el frontend para mostrarlo antes de guardar.
   */
  public static function previewCodigoVereda($rqst)
  {
    $municipioId = intval($rqst['municipio_id'] ?? 0);
    if ($municipioId <= 0) {
      return ['output' => ['valid' => false, 'response' => 'Municipio inválido']];
    }
    $db  = new DbConection();
    $pdo = $db->openConect();
    try {
      $codigo = self::generarCodigoVereda($pdo, $db->getTable('tbl_vereda'), $municipioId);
      return ['output' => ['valid' => true, 'response' => $codigo]];
    } catch (Exception $e) {
      return Util::error_general($e->getMessage());
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Inserta una nueva vereda en Santander.
   * El código se genera automáticamente como consecutivo del municipio.
   */
  public static function saveVereda($rqst)
  {
    $campos = ['nombre_vereda', 'municipio_id'];
    foreach ($campos as $c) {
      if (empty(trim($rqst[$c] ?? ''))) {
        return ['output' => ['valid' => false, 'response' => "El campo '{$c}' es obligatorio."]];
      }
    }

    $db  = new DbConection();
    $pdo = $db->openConect();
    try {
      $municipioId = intval($rqst['municipio_id']);
      $tblVereda   = $db->getTable('tbl_vereda');

      // Generar código consecutivo automático para el municipio
      $codigoVereda = self::generarCodigoVereda($pdo, $tblVereda, $municipioId);

      // La tabla no tiene AUTO_INCREMENT: calculamos el siguiente ID manualmente
      $maxStmt = $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM $tblVereda");
      $nuevoId = (int)$maxStmt->fetchColumn();

      $stmt = $pdo->prepare(
        "INSERT INTO $tblVereda
           (id, departamento_id, municipio_id, codigo_vereda, nombre_vereda,
            hombres, mujeres, total, habilitada_para_votar, observaciones,
            tbl_brigada_id, tbl_batallon_id, divipola, puntaje, puntaje2021,
            porcentaje_participacion, carpeta_svg, nombre_svg)
         VALUES
           (:id, 68, :municipio_id, :codigo_vereda, :nombre_vereda,
            :hombres, :mujeres, :total, :habilitada_para_votar, :observaciones,
            0, 0, 0, 0, 0, 0, '', '')"
      );
      $stmt->execute([
        ':id'                    => $nuevoId,
        ':municipio_id'          => $municipioId,
        ':codigo_vereda'         => $codigoVereda,
        ':nombre_vereda'         => strtoupper(trim($rqst['nombre_vereda'])),
        ':hombres'               => intval($rqst['hombres']               ?? 0),
        ':mujeres'               => intval($rqst['mujeres']               ?? 0),
        ':total'                 => intval($rqst['total']                 ?? 0),
        ':habilitada_para_votar' => strtoupper(trim($rqst['habilitada_para_votar'] ?? '')),
        ':observaciones'         => trim($rqst['observaciones']           ?? ''),
      ]);
      return ['output' => ['valid' => true, 'response' => "Vereda creada con código $codigoVereda."]];
    } catch (Exception $e) {
      return Util::error_general($e->getMessage());
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Actualiza los campos editables de una vereda existente.
   */
  public static function updateVereda($rqst)
  {
    $id = intval($rqst['id'] ?? 0);
    if ($id <= 0) {
      return ['output' => ['valid' => false, 'response' => 'ID inválido']];
    }

    $db  = new DbConection();
    $pdo = $db->openConect();
    try {
      // Validar unicidad del código de vereda excluyendo el registro actual
      $chk = $pdo->prepare(
        "SELECT id FROM " . $db->getTable('tbl_vereda') . "
         WHERE codigo_vereda = :codigo AND id != :id LIMIT 1"
      );
      $chk->execute([':codigo' => trim($rqst['codigo_vereda']), ':id' => $id]);
      if ($chk->fetch()) {
        return ['output' => ['valid' => false, 'response' => 'El código de vereda ya está en uso por otra vereda.']];
      }

      $stmt = $pdo->prepare(
        "UPDATE " . $db->getTable('tbl_vereda') . "
         SET municipio_id          = :municipio_id,
             codigo_vereda         = :codigo_vereda,
             nombre_vereda         = :nombre_vereda,
             hombres               = :hombres,
             mujeres               = :mujeres,
             total                 = :total,
             habilitada_para_votar = :habilitada_para_votar,
             observaciones         = :observaciones
         WHERE id = :id AND departamento_id = 68"
      );
      $stmt->execute([
        ':id'                  => $id,
        ':municipio_id'        => intval($rqst['municipio_id']),
        ':codigo_vereda'       => trim($rqst['codigo_vereda']),
        ':nombre_vereda'       => strtoupper(trim($rqst['nombre_vereda'])),
        ':hombres'             => intval($rqst['hombres']             ?? 0),
        ':mujeres'             => intval($rqst['mujeres']             ?? 0),
        ':total'               => intval($rqst['total']               ?? 0),
        ':habilitada_para_votar' => strtoupper(trim($rqst['habilitada_para_votar'] ?? '')),
        ':observaciones'       => trim($rqst['observaciones']         ?? ''),
      ]);
      return ['output' => ['valid' => true, 'response' => 'Vereda actualizada correctamente.']];
    } catch (Exception $e) {
      return Util::error_general($e->getMessage());
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Elimina una vereda (solo si pertenece a Santander, como doble seguro).
   */
  public static function deleteVereda($rqst)
  {
    $id = intval($rqst['id'] ?? 0);
    if ($id <= 0) {
      return ['output' => ['valid' => false, 'response' => 'ID inválido']];
    }
    $db  = new DbConection();
    $pdo = $db->openConect();
    try {
      $stmt = $pdo->prepare(
        "DELETE FROM " . $db->getTable('tbl_vereda') . "
         WHERE id = :id AND departamento_id = 68"
      );
      $stmt->execute([':id' => $id]);
      return ['output' => ['valid' => true, 'response' => 'Vereda eliminada correctamente.']];
    } catch (Exception $e) {
      return Util::error_general($e->getMessage());
    } finally {
      $db->closeConect();
    }
  }

  // ─────────────────────────────────────────────────────────────────────────

  public static function getFactoresPorVereda($veredaId)
  {
    $db = new DbConection();

    try {
      $q = "SELECT 
                  f.tipo AS factor,
                  i.valor AS cantidad,
                  f.tipo_medicion AS unidad_medida
                FROM " . $db->getTable('tbl_ingreso_informacion') . " i
                INNER JOIN " . $db->getTable('tbl_factores') . " f
                ON i.tbl_factor_id = f.id
                WHERE i.tbl_vereda_id = :veredaId";

      $stmt = $db->openConect()->prepare($q);
      $stmt->bindParam(':veredaId', $veredaId, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);



      if (empty($result)) {
        return array('output' => array('valid' => false, 'error' => 'No se encontraron datos.'));
      }


      return array('output' => array('valid' => true, 'response' => $result));
    } catch (Exception $e) {
      return array('output' => array('valid' => false, 'error' => $e->getMessage()));
    } finally {
      $db->closeConect();
    }
  }


  /**
   * Metodo para obtener las veredas 5 más criticas por batallon o brigada
   */
  public static function getVeredasCriticasByBatallonIdOrByBrigadaId($rqst)
  {

    include 'Estado.php';

    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : 0;
    $tbl_batallon_id = isset($rqst['tbl_batallon_id']) ? intval($rqst['tbl_batallon_id']) : 0;
    $limit = isset($rqst['limit']) ? intval($rqst['limit']) : 8;
    $filtro = isset($rqst['filtro']) ? ($rqst['filtro']) : '';
    $puntaje = 200;
    $q = "";

    if ($tbl_brigada_id == 0 && $tbl_batallon_id  == 0) {
      return Util::error_no_result();
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    // Filtro por brigada
    if ($tbl_brigada_id  > 0) {
      $q = "SELECT tbl_ciudades.id AS tbl_ciudad_id,
        tbl_vereda.id AS tbl_vereda_id,
        tbl_vereda.nombre_vereda,
        tbl_vereda.carpeta_svg,
        tbl_vereda.nombre_svg,
        tbl_ciudades.municipio,
        tbl_ciudades.codigo_muncipio,
        tbl_ciudades.codigo_departamento,
        tbl_brigadas.id as tbl_brigada_id,
        tbl_brigadas.sigla AS brigada, 
        tbl_batallones.sigla AS batallon, 
        tbl_vereda.color, 
        tbl_vereda.puntaje
        FROM ((" . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio) 
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id 
        WHERE tbl_brigadas.id =  $tbl_brigada_id AND tbl_vereda.puntaje >= $puntaje ORDER BY tbl_vereda.puntaje DESC LIMIT $limit";
    }

    // Filtro por Batallon
    if ($tbl_batallon_id  > 0) {

      $q = "SELECT tbl_ciudades.id AS tbl_ciudad_id,
        tbl_vereda.id AS tbl_vereda_id,
        tbl_vereda.nombre_vereda,
        tbl_vereda.carpeta_svg,
        tbl_vereda.nombre_svg,
        tbl_ciudades.municipio,
        tbl_ciudades.codigo_muncipio,
        tbl_ciudades.codigo_departamento,
        tbl_brigadas.id as tbl_brigada_id,
        tbl_brigadas.sigla AS brigada, 
        tbl_batallones.sigla AS batallon, 
        tbl_vereda.color, 
        tbl_vereda.puntaje
        FROM ((" . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio) 
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id 
        WHERE tbl_batallones.id = $tbl_batallon_id AND tbl_vereda.puntaje >= $puntaje ORDER BY tbl_vereda.puntaje DESC LIMIT $limit";
    }
    $result = $pdo->query($q);
    $arr = array();
    $arrEstadoVeredasCriticas = array();
    if ($result) {
      foreach ($result as $valor) {
        $arrTemp = array();
        $arrTemp['tbl_vereda_id'] = $valor['tbl_vereda_id'];
        $arrTemp['tbl_ciudad_id'] = $valor['tbl_ciudad_id'];
        $arrTemp['nombre_vereda'] = $valor['nombre_vereda'];
        $arrTemp['carpeta_svg'] = $valor['carpeta_svg'];
        $arrTemp['nombre_svg'] = $valor['nombre_svg'];
        $arrTemp['municipio'] = $valor['municipio'];
        $arrTemp['codigo_muncipio'] = $valor['codigo_muncipio'];
        $arrTemp['codigo_departamento'] = $valor['codigo_departamento'];
        $arrTemp['tbl_brigada_id'] = $valor['tbl_brigada_id'];
        $arrTemp['brigada'] = $valor['brigada'];
        $arrTemp['batallon'] = $valor['batallon'];
        $arrTemp['color'] = $valor['color'];
        $arrTemp['puntaje'] = $valor['puntaje'];
        $arrTemp['clase'] = Util::getClasePorColor($valor['color']);
        $arr[] = $arrTemp;

        //Consultamos la informacion del estado de la VEREDA
        $codigo_departamento = $valor['codigo_departamento'];
        $codigo_muncipio = $valor['codigo_muncipio'];
        $nombre_vereda = $valor['nombre_vereda'];
        $rqstVereda =  array('codigo_departamento' => $codigo_departamento, 'codigo_muncipio' => $codigo_muncipio, 'vereda' => $nombre_vereda);
        $resultVereda = Estado::getEstadoFactorArmadoSocialEcon($rqstVereda);
        if ($resultVereda) {
          $arrEstadoVeredasCriticas[] = $resultVereda['output'];
        }
      }
      if (count($arr) > 0) {
        $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'filtro' => $filtro, 'estados' => $arrEstadoVeredasCriticas));
      } else {
        $arrjson = Util::error_no_result();
      }
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }

  /**
   * Metodo para mostrar la información de las veredas seleccionadas en Modulo de "Veredas Críticas - Selección personalizada"
   */
  public static function getVeredasSeleccionadasCriticasByBatallonIdOrByBrigadaId($rqst)
  {
    include 'Estado.php';

    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : 0;
    $tbl_batallon_id = isset($rqst['tbl_batallon_id']) ? intval($rqst['tbl_batallon_id']) : 0;
    $filtro = isset($rqst['filtro']) ? ($rqst['filtro']) : '';
    $tbl_vereda_ids = isset($rqst['tbl_vereda_id']) ? ($rqst['tbl_vereda_id']) : '';

    $arr = array();
    $arrEstadoVeredasCriticas = array();
    $q = "";

    if ($tbl_brigada_id == 0 && $tbl_batallon_id  == 0) {
      return Util::error_no_result();
    }

    if ($tbl_vereda_ids == "") {
      return Util::info_general('Debe selecionar al menos una vereda');
    }
    $arrchkVeredaId = explode(',', $tbl_vereda_ids);
    $contador = count($arrchkVeredaId);

    $db = new DbConection();
    $pdo = $db->openConect();


    for ($i = 0; $i < $contador; $i++) {

      if (intval($arrchkVeredaId[$i]) > 0) {
        $q = "SELECT tbl_ciudades.id AS tbl_ciudad_id,
          tbl_vereda.id AS tbl_vereda_id,
          tbl_vereda.nombre_vereda,
          tbl_vereda.carpeta_svg,
          tbl_vereda.nombre_svg,
          tbl_ciudades.municipio,
          tbl_ciudades.codigo_muncipio,
          tbl_ciudades.codigo_departamento,
          tbl_brigadas.id as tbl_brigada_id,
          tbl_brigadas.sigla AS brigada, 
          tbl_batallones.sigla AS batallon, 
          tbl_batallones.id AS tbl_batallon_id, 
          tbl_vereda.color, 
          tbl_vereda.puntaje
          FROM ((" . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio) 
          INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id) 
          INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id
          WHERE tbl_vereda.id = $arrchkVeredaId[$i]";

        $result = $pdo->query($q);
        if ($result) {
          foreach ($result as $valor) {
            $arrTemp = array();
            $arrTemp['tbl_vereda_id'] = $valor['tbl_vereda_id'];
            $arrTemp['tbl_ciudad_id'] = $valor['tbl_ciudad_id'];
            $arrTemp['nombre_vereda'] = $valor['nombre_vereda'];
            $arrTemp['carpeta_svg'] = $valor['carpeta_svg'];
            $arrTemp['nombre_svg'] = $valor['nombre_svg'];
            $arrTemp['municipio'] = $valor['municipio'];
            $arrTemp['codigo_muncipio'] = $valor['codigo_muncipio'];
            $arrTemp['codigo_departamento'] = $valor['codigo_departamento'];
            $arrTemp['tbl_brigada_id'] = $valor['tbl_brigada_id'];
            $arrTemp['brigada'] = $valor['brigada'];
            $arrTemp['batallon'] = $valor['batallon'];
            $arrTemp['color'] = $valor['color'];
            $arrTemp['puntaje'] = $valor['puntaje'];
            $arrTemp['clase'] = Util::getClasePorColor($valor['color']);
            $arr[] = $arrTemp;

            //Consultamos la informacion del estado de la VEREDA
            $codigo_departamento = $valor['codigo_departamento'];
            $codigo_muncipio = $valor['codigo_muncipio'];
            $nombre_vereda = $valor['nombre_vereda'];
            $rqstVereda =  array('codigo_departamento' => $codigo_departamento, 'codigo_muncipio' => $codigo_muncipio, 'vereda' => $nombre_vereda);
            $resultVereda = Estado::getEstadoFactorArmadoSocialEcon($rqstVereda);
            if ($resultVereda) {
              $arrEstadoVeredasCriticas[] = $resultVereda['output'];
            }
          }
        }
      }
    }

    if (count($arr) > 0) {
      $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'filtro' => $filtro, 'estados' => $arrEstadoVeredasCriticas));
    } else {
      $arrjson = Util::error_no_result();
    }

    $db->closeConect();
    return $arrjson;
  }

  public static function getSoloInformacionVeredasCriticasV2($rqst)
  {

    include 'Estado.php';

    $tbl_brigada_id = isset($rqst['tbl_brigada_id']) ? intval($rqst['tbl_brigada_id']) : 0;
    $tbl_batallon_id = isset($rqst['tbl_batallon_id']) ? intval($rqst['tbl_batallon_id']) : 0;
    $limit = isset($rqst['limit']) ? intval($rqst['limit']) : 5;
    $filtro = isset($rqst['filtro']) ? ($rqst['filtro']) : '';
    $puntaje = 0;
    $q = "";

    if ($tbl_brigada_id == 0 && $tbl_batallon_id  == 0) {
      return Util::error_no_result();
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    // Filtro por brigada
    if ($tbl_brigada_id  > 0) {
      $q = "SELECT tbl_brigadas.sigla AS brigada, 
        tbl_batallones.id AS batallon_id, 
        tbl_brigadas.id AS brigada_id, 
        tbl_batallones.sigla AS batallon, 
        tbl_ciudades.municipio, 
        tbl_vereda.id AS tbl_vereda_id, 
        tbl_vereda.nombre_vereda, 
        tbl_vereda.nombre_svg, 
        tbl_vereda.carpeta_svg, 
        tbl_ciudades.codigo_departamento, 
        tbl_vereda.color, 
        tbl_vereda.puntaje
        FROM ((" . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id) 
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id
        WHERE tbl_brigadas.id = $tbl_brigada_id 
        ORDER BY tbl_ciudades.municipio, tbl_vereda.nombre_vereda ASC LIMIT $limit";
    }

    // Filtro por Batallon
    if ($tbl_batallon_id  > 0) {
      $q = "SELECT tbl_brigadas.sigla AS brigada, 
        tbl_batallones.id AS batallon_id, 
        tbl_brigadas.id AS brigada_id, 
        tbl_batallones.sigla AS batallon, 
        tbl_ciudades.municipio, 
        tbl_vereda.id AS tbl_vereda_id, 
        tbl_vereda.nombre_vereda, 
        tbl_vereda.nombre_svg, 
        tbl_vereda.carpeta_svg, 
        tbl_ciudades.codigo_departamento, 
        tbl_vereda.color, 
        tbl_vereda.puntaje
        FROM ((" . $db->getTable('tbl_vereda') . " INNER JOIN " . $db->getTable('tbl_ciudades') . " ON tbl_vereda.municipio_id = tbl_ciudades.codigo_muncipio) 
        INNER JOIN " . $db->getTable('tbl_brigadas') . " ON tbl_vereda.tbl_brigada_id = tbl_brigadas.id) 
        INNER JOIN " . $db->getTable('tbl_batallones') . " ON tbl_vereda.tbl_batallon_id = tbl_batallones.id
        WHERE tbl_batallones.id = $tbl_batallon_id 
        ORDER BY tbl_ciudades.municipio, tbl_vereda.nombre_vereda ASC LIMIT $limit";
    }
    $result = $pdo->query($q);
    $arr = array();
    if ($result) {
      foreach ($result as $valor) {
        $arr[] = $valor;
      }
      if (count($arr) > 0) {
        $arrjson = array('output' => array('valid' => true, 'response' => $arr, 'filtro' => $filtro));
      } else {
        $arrjson = Util::error_no_result();
      }
    } else {
      $arrjson = Util::error_no_result();
    }
    $db->closeConect();
    return $arrjson;
  }
}
