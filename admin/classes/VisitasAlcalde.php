<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Clase para gestionar las visitas y compromisos del Alcalde
 * Utiliza la tabla tbl_visitas_alcalde que incluye tbl_vereda_id obligatorio
 * @author SPIDERSOFTWARE
 */
class VisitasAlcalde
{

  public function __construct() {}

  /**
   * Obtener todas las visitas/compromisos del alcalde
   */
  /**
   * Obtener todas las visitas del alcalde para DataTables
   */
  public static function getAll($data)
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $userType = SessionData::getUserType();
    $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();

    $isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    $draw = $data['draw'];
    $start = intval($data['start']);
    $length = intval($data['length']);
    $searchValue = $data['search']['value'] ?? '';
    $orderDir = 'desc';

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $tableVisitas = $db->getTable('tbl_visitas_alcalde') . " AS visitas";
      $tableCiudades = $db->getTable('tbl_ciudades') . " AS ciudad";
      $tableVeredas = $db->getTable('tbl_vereda') . " AS veredas";

      $orderBy = 'visitas.id';

      $where = " visitas.tipo_registro = 'Visita' ";
      $params = [];

      // Si es alcalde o auxiliar de alcalde, filtrar por su municipio
      if ($isAlcaldeOAuxiliar) {
        $where .= " AND visitas.tbl_municipio_id = :municipioId";
        $params[':municipioId'] = $municipioUsuarioLogueado;
      }

      // Búsqueda
      if (!empty($searchValue)) {
        $where .= " AND (
            visitas.date LIKE :search OR
            ciudad.municipio LIKE :search OR
            veredas.nombre_vereda LIKE :search OR
            visitas.compromisos LIKE :search OR
            visitas.tipo_visita LIKE :search OR
            visitas.consecuencia LIKE :search OR
            visitas.descripcion_hecho LIKE :search
        )";
        $params[':search'] = "%$searchValue%";
      }

      // Total de registros base
      $whereTotalBase = " visitas.tipo_registro = 'Visita' ";
      $paramsTotal = [];

      if ($isAlcaldeOAuxiliar) {
        $whereTotalBase .= " AND visitas.tbl_municipio_id = :municipioIdTotal";
        $paramsTotal[':municipioIdTotal'] = $municipioUsuarioLogueado;
      }

      $sqlTotal = "SELECT COUNT(*) FROM $tableVisitas
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            INNER JOIN $tableVeredas ON visitas.tbl_vereda_id = veredas.id
            WHERE $whereTotalBase";

      $stmtTotal = $pdo->prepare($sqlTotal);
      $stmtTotal->execute($paramsTotal);
      $totalRecords = $stmtTotal->fetchColumn();

      // Total de registros filtrados
      $sqlFiltered = "SELECT COUNT(*) FROM $tableVisitas
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            INNER JOIN $tableVeredas ON visitas.tbl_vereda_id = veredas.id
            WHERE $where";

      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params);
      $filteredRecords = $stmtFiltered->fetchColumn();

      // Datos paginados
      $sqlData = "SELECT
                visitas.date,
                ciudad.municipio,
                veredas.nombre_vereda AS vereda,
                visitas.tipo_visita,
                visitas.img,
                visitas.id,
                visitas.consecuencia,
                visitas.descripcion_hecho,
                visitas.compromisos
            FROM $tableVisitas
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            INNER JOIN $tableVeredas ON visitas.tbl_vereda_id = veredas.id
            WHERE $where
            ORDER BY $orderBy $orderDir
            LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

      foreach ($params as $key => $value) {
        $stmtData->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
      }

      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      // Formatear datos
      $data = [];
      foreach ($result as $row) {
        $imagen = '';
        if (!empty($row['img'])) {
          if (!is_string($row['img'])) {
            $imgData = base64_encode($row['img']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            if ($row['img'] == '4fbe830e44ff09970c4e3f979bad58849a3klsrvh4.png') {
              $imgSrc = 'assets/img/santander.png';
            } else {
              $imgSrc = 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
            }
          }
          $imagen = '<img src="' . $imgSrc . '" width="100" height="auto" class="rounded-circle" />';
        }

        $data[] = [
          'compromisos'       => $row['compromisos'],
          'tipo_visita'       => $row['tipo_visita'],
          'descripcion_hecho' => $row['descripcion_hecho'],
          'consecuencia'      => $row['consecuencia'],
          'vereda'            => $row['vereda'], // VEREDA en lugar de provincia
          'municipio'         => $row['municipio'],
          'foto'              => $imagen,
          'date'              => $row['date'],
          'id'                => $row['id'],
          'id_reporte'        => $row['id'],
          'secretaria'        => $row['tipo_visita'],
        ];
      }

      return [
        "draw"            => intval($draw),
        "recordsTotal"    => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data"            => $data
      ];
    } catch (PDOException $e) {
      return ['state' => false, 'error' => $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Guardar nueva visita o compromiso del alcalde
   */
  public static function save($rqst, $files)
  {
    // Iniciar sesión si no está activa
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    try {
      // Validar que exista sesión de usuario
      if (!isset($_SESSION['session_user']['id'])) {
        return [
          'state' => false,
          'message' => 'Sesión de usuario no encontrada'
        ];
      }

      $date = isset($rqst['date']) ? trim($rqst['date']) : '';
      $tbl_vereda_id = isset($rqst['tbl_vereda_id']) ? intval($rqst['tbl_vereda_id']) : 0;
      $consecuencia =  '';
      $tipo_registro = isset($rqst['tipo_registro']) ? trim($rqst['tipo_registro']) : '';
      $compromisos = isset($rqst['compromisos']) ? trim($rqst['compromisos']) : '';
      $compromisopac = isset($rqst['compromisopac']) ? trim($rqst['compromisopac']) : '';
      $respuesta = '';
      $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
      $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? trim($rqst['tbl_departamento_id']) : '';
      $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? trim($rqst['tbl_municipio_id']) : '';
      $descripcion_hecho = '';
      $tipo_visita = !empty($rqst['tipo_visita']) ? trim($rqst['tipo_visita']) : 'N/A';
      $tipo_ejecucion = !empty($rqst['tipo_ejecucion']) ? trim($rqst['tipo_ejecucion']) : 'N/A';
      $componente = !empty($rqst['componente']) ? trim($rqst['componente']) : 'N/A';
      $requiere_respuesta = !empty($rqst['requiere_respuesta']) ? trim($rqst['requiere_respuesta']) : 'No';
      $tbl_usuario_id = intval($_SESSION['session_user']['id']);

      // Validaciones comunes
      if (empty($date)) {
        return ['state' => false, 'message' => 'Debe ingresar una fecha'];
      }

      if (empty($tipo_registro)) {
        return ['state' => false, 'message' => 'Debe seleccionar el tipo de registro'];
      }

      if ($tbl_vereda_id <= 0) {
        return ['state' => false, 'message' => 'Debe seleccionar una vereda'];
      }

      if (empty($tbl_departamento_id)) {
        return ['state' => false, 'message' => 'Debe seleccionar un departamento'];
      }

      if (empty($tbl_municipio_id)) {
        return ['state' => false, 'message' => 'Debe seleccionar un municipio'];
      }

      // Validaciones específicas por tipo de registro
      if ($tipo_registro === 'Visita') {
        // Para visitas, validar detalles y tipo de visita
        if (empty($compromisos)) {
          return ['state' => false, 'message' => 'Debe ingresar los detalles de la visita'];
        }
        if ($tipo_visita === 'N/A' || empty($tipo_visita)) {
          return ['state' => false, 'message' => 'Debe seleccionar el tipo de visita'];
        }
        // Secretaría va como null para visitas
        $tbl_secretarias_id = null;
      } elseif ($tipo_registro === 'Compromiso') {
        // Para compromisos, validar campos específicos
        if (empty($compromisopac)) {
          return ['state' => false, 'message' => 'Debe ingresar los compromisos pactados'];
        }
        if ($componente === 'N/A' || empty($componente)) {
          return ['state' => false, 'message' => 'Debe seleccionar el componente'];
        }
        if ($tipo_ejecucion === 'N/A' || empty($tipo_ejecucion)) {
          return ['state' => false, 'message' => 'Debe seleccionar el tipo de ejecución'];
        }
        if ($tbl_secretarias_id <= 0) {
          return ['state' => false, 'message' => 'Debe seleccionar la secretaría o dependencia encargada'];
        }
      }

      // Carpeta de destino
      $uploadDir = '../../assets/img/admin/';
      if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Subir imagen
      $imgPath = '';
      if (isset($files['imagen']) && $files['imagen']['error'] === UPLOAD_ERR_OK) {
        $imgName = uniqid() . '_' . basename($files['imagen']['name']);
        $imgTarget = $uploadDir . $imgName;
        if (move_uploaded_file($files['imagen']['tmp_name'], $imgTarget)) {
          $imgPath = $imgName;
        }
      }

      $tipoUsuario = SessionData::getUserType();
      $codigoMunicipio = SessionData::getCodigoMunicipio();

      // Si es alcalde o auxiliar de alcalde, debe corresponder a su municipio
      if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario) {
        if ($tbl_municipio_id != $codigoMunicipio) {
          return [
            'state' => false,
            'message' => 'Debe seleccionar el municipio correspondiente al cual pertenece'
          ];
        }
      }

      $db = new DbConection();
      $pdo = $db->openConect();

      // Iniciar transacción
      $pdo->beginTransaction();

      // Si es compromiso, guardar en tbl_compromisos_alcalde
      if ($tipo_registro === 'Compromiso') {
        $q = "INSERT INTO " . $db->getTable('tbl_compromisos_alcalde') . "
              (date, tbl_municipio_id, tbl_vereda_id, tbl_secretarias_id, compromisos,
               compromiso_pactado, consecuencia, respuesta, cumplimiento, componente,
               tipo_ejecucion, img, estado, estado_autorizar, updated_at)
              VALUES
              (:date, :tbl_municipio_id, :tbl_vereda_id, :tbl_secretarias_id, :compromisos,
               :compromiso_pactado, :consecuencia, :respuesta, :cumplimiento, :componente,
               :tipo_ejecucion, :img, :estado, :estado_autorizar, NOW())";

        $stmt = $pdo->prepare($q);

        $arrparam = array(
          ':date' => $date,
          ':tbl_municipio_id' => $tbl_municipio_id,
          ':tbl_vereda_id' => $tbl_vereda_id,
          ':tbl_secretarias_id' => $tbl_secretarias_id,
          ':compromisos' => $compromisopac, // En compromisos va el texto del compromiso pactado
          ':compromiso_pactado' => $compromisopac,
          ':consecuencia' => $consecuencia,
          ':respuesta' => $respuesta,
          ':cumplimiento' => 'Sin Cumplir',
          ':componente' => $componente,
          ':tipo_ejecucion' => $tipo_ejecucion,
          ':img' => $imgPath,
          ':estado' => 'En Espera', // Los compromisos inician en estado En Espera para aprobación
          ':estado_autorizar' => 'Sin Cumplir', // Estado inicial que pasará cuando se apruebe
        );
      } else {
        // Si es visita, guardar en tbl_visitas_alcalde
        $q = "INSERT INTO " . $db->getTable('tbl_visitas_alcalde') . "
              (created_at, date, consecuencia, tipo_registro, estado, compromisos, compromisopac,
               respuesta, tbl_secretarias_id, tbl_departamento_id, tbl_municipio_id, tbl_vereda_id,
               descripcion_hecho, img, tbl_usuario_id, tipo_visita, requiere_respuesta,
               componente, tipo_ejecucion)
              VALUES
              (" . Util::date_now_server() . ", :date, :consecuencia, :tipo_registro, :estado,
               :compromisos, :compromisopac, :respuesta, :tbl_secretarias_id, :tbl_departamento_id,
               :tbl_municipio_id, :tbl_vereda_id, :descripcion_hecho, :img, :tbl_usuario_id,
               :tipo_visita, :requiere_respuesta, :componente, :tipo_ejecucion)";

        $stmt = $pdo->prepare($q);

        $arrparam = array(
          ':date' => $date,
          ':consecuencia' => $consecuencia,
          ':tipo_registro' => $tipo_registro,
          ':estado' => 'Sin Cumplir',
          ':compromisos' => $compromisos,
          ':compromisopac' => $compromisopac,
          ':respuesta' => $respuesta,
          ':tbl_secretarias_id' => $tbl_secretarias_id,
          ':tbl_departamento_id' => $tbl_departamento_id,
          ':tbl_municipio_id' => $tbl_municipio_id,
          ':tbl_vereda_id' => $tbl_vereda_id,
          ':descripcion_hecho' => $descripcion_hecho,
          ':img' => $imgPath,
          ':tbl_usuario_id' => $tbl_usuario_id,
          ':tipo_visita' => $tipo_visita,
          ':requiere_respuesta' => $requiere_respuesta,
          ':componente' => $componente,
          ':tipo_ejecucion' => $tipo_ejecucion,
        );
      }

      if ($stmt->execute($arrparam)) {
        // Commit de la transacción
        $pdo->commit();

        $mensaje = $tipo_registro === 'Compromiso'
          ? 'Compromiso ingresado correctamente y enviado a aprobación'
          : 'Visita registrada correctamente';

        $arrjson = ['state' => true, 'message' => $mensaje];

        // Enviar correo si tiene compromisopac y secretaría
        if ($tipo_registro === 'Compromiso' && !empty($compromisopac) && $tbl_secretarias_id > 0) {
          try {
            $q = "SELECT * FROM " . $db->getTable('tbl_secretarias') . " WHERE id = :id";
            $stmtSec = $pdo->prepare($q);
            $stmtSec->bindValue(':id', $tbl_secretarias_id, PDO::PARAM_INT);
            $stmtSec->execute();
            $resultSec = $stmtSec->fetch(PDO::FETCH_OBJ);

            if ($resultSec) {
              $q = "SELECT codigo_muncipio, nombre_mapa, municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio = :codigo_muncipio";
              $stmtData = $pdo->prepare($q);
              $stmtData->bindValue(':codigo_muncipio', $tbl_municipio_id);
              $stmtData->execute();
              $resultCiudad = $stmtData->fetch(PDO::FETCH_OBJ);

              // Aquí se puede implementar el envío de correo
              // self::correoSecretaria($resultSec->correo, $resultSec->secretario, $resultCiudad->nombre_mapa);
            }
          } catch (Exception $e) {
            // Error en envío de correo no debe afectar el guardado
            error_log("Error enviando correo: " . $e->getMessage());
          }
        }
      } else {
        $pdo->rollBack();
        $errorInfo = $stmt->errorInfo();
        return [
          'state' => false,
          'message' => 'Error al guardar: ' . $errorInfo[2]
        ];
      }

      $db->closeConect();
      return $arrjson;

    } catch (PDOException $e) {
      if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
      }
      if (isset($db)) {
        $db->closeConect();
      }
      return [
        'state' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
      ];
    } catch (Exception $e) {
      if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
      }
      if (isset($db)) {
        $db->closeConect();
      }
      return [
        'state' => false,
        'message' => 'Error: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Actualizar visita del alcalde
   */
  public static function actualizarVisita($rqst, $files)
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $date = isset($rqst['date']) ? trim($rqst['date']) : '';
    $compromisos = isset($rqst['compromisos']) ? trim($rqst['compromisos']) : '';
    $tbl_municipio_id = isset($rqst['municipio']) ? trim($rqst['municipio']) : '';
    $tbl_vereda_id = isset($rqst['vereda']) ? intval($rqst['vereda']) : 0;
    $tipo_visita = !empty($rqst['tipo_visita']) ? trim($rqst['tipo_visita']) : 'N/A';
    $tbl_usuario_id = isset($_SESSION['session_user']['id']) ? intval($_SESSION['session_user']['id']) : 0;

    if ($tbl_usuario_id === 0) {
      return ['state' => false, 'message' => 'ID de usuario no encontrado en la sesión.'];
    }

    if ($id === 0) {
      return ['state' => false, 'message' => 'Debe seleccionar la visita a actualizar.'];
    }

    if ($tbl_vereda_id === 0) {
      return ['state' => false, 'message' => 'Debe seleccionar una vereda.'];
    }

    if ($tipo_visita === 'N/A') {
      return ['state' => false, 'message' => 'Debe seleccionar el tipo de visita.'];
    }

    if ($date === '') {
      return ['state' => false, 'message' => 'Debe ingresar la fecha.'];
    }

    if ($compromisos === '') {
      return ['state' => false, 'message' => 'Debe ingresar los compromisos.'];
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    $tableVisitas = $db->getTable('tbl_visitas_alcalde');

    try {
      // Actualizar campos básicos
      $q = "UPDATE " . $tableVisitas . "
            SET date = :date,
                tbl_municipio_id = :tbl_municipio_id,
                tbl_vereda_id = :tbl_vereda_id,
                tipo_visita = :tipo_visita,
                compromisos = :compromisos,
                update_at = NOW()
            WHERE id = :id";

      $stmt = $pdo->prepare($q);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->bindValue(':date', $date, PDO::PARAM_STR);
      $stmt->bindValue(':tbl_municipio_id', $tbl_municipio_id, PDO::PARAM_INT);
      $stmt->bindValue(':tbl_vereda_id', $tbl_vereda_id, PDO::PARAM_INT);
      $stmt->bindValue(':tipo_visita', $tipo_visita, PDO::PARAM_STR);
      $stmt->bindValue(':compromisos', $compromisos, PDO::PARAM_STR);

      if ($stmt->execute()) {
        // Manejar actualización de imagen si se envió
        if (isset($files['imagen']) && $files['imagen']['error'] === UPLOAD_ERR_OK) {
          $uploadDir = '../../assets/img/admin/';
          if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
          }

          $imgName = uniqid() . '_' . basename($files['imagen']['name']);
          $imgTarget = $uploadDir . $imgName;

          if (move_uploaded_file($files['imagen']['tmp_name'], $imgTarget)) {
            $qImg = "UPDATE " . $tableVisitas . " SET img = :img WHERE id = :id";
            $stmtImg = $pdo->prepare($qImg);
            $stmtImg->bindValue(':img', $imgName, PDO::PARAM_STR);
            $stmtImg->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtImg->execute();
          }
        }

        return ['state' => true, 'message' => 'Visita actualizada correctamente.'];
      } else {
        return ['state' => false, 'message' => 'Error al actualizar la visita.'];
      }
    } catch (PDOException $e) {
      return ['state' => false, 'message' => 'Error: ' . $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Actualizar compromiso del alcalde
   */
  public static function actualizarCompromiso($rqst, $files)
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $date = isset($rqst['date']) ? trim($rqst['date']) : '';
    $compromisos = isset($rqst['compromisos']) ? trim($rqst['compromisos']) : '';
    $compromisopac = isset($rqst['compromisopac']) ? trim($rqst['compromisopac']) : '';
    $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? intval($rqst['tbl_secretarias_id']) : 0;
    $estado = isset($rqst['estado']) ? trim($rqst['estado']) : '';
    $respuesta = isset($rqst['respuesta']) ? trim($rqst['respuesta']) : '';
    $tbl_municipio_id = isset($rqst['municipio']) ? ($rqst['municipio']) : 0;
    $tbl_vereda_id = isset($rqst['vereda']) ? intval($rqst['vereda']) : 0;
    $tipo_visita = !empty($rqst['tipo_visita']) ? trim($rqst['tipo_visita']) : 'N/A';
    $componente = !empty($rqst['componente']) ? trim($rqst['componente']) : 'N/A';
    $tipo_ejecucion = !empty($rqst['tipo_ejecucion']) ? trim($rqst['tipo_ejecucion']) : 'N/A';

    if ($id === 0) {
      return ['state' => false, 'message' => 'Debe seleccionar el compromiso a actualizar.'];
    }

    if ($tbl_vereda_id == 0) {
      return ['state' => false, 'message' => 'Debe seleccionar una vereda.'];
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    $tableVisitas = $db->getTable('tbl_visitas_alcalde');

    try {
      $q = "UPDATE " . $tableVisitas . "
            SET date = :date,
                tbl_municipio_id = :tbl_municipio_id,
                tbl_vereda_id = :tbl_vereda_id,
                tipo_visita = :tipo_visita,
                compromisos = :compromisos,
                compromisopac = :compromisopac,
                tbl_secretarias_id = :tbl_secretarias_id,
                estado = :estado,
                respuesta = :respuesta,
                componente = :componente,
                tipo_ejecucion = :tipo_ejecucion,
                update_at = NOW()
            WHERE id = :id";

      $stmt = $pdo->prepare($q);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->bindValue(':date', $date, PDO::PARAM_STR);
      $stmt->bindValue(':tbl_municipio_id', $tbl_municipio_id, PDO::PARAM_INT);
      $stmt->bindValue(':tbl_vereda_id', $tbl_vereda_id, PDO::PARAM_INT);
      $stmt->bindValue(':tipo_visita', $tipo_visita, PDO::PARAM_STR);
      $stmt->bindValue(':compromisos', $compromisos, PDO::PARAM_STR);
      $stmt->bindValue(':compromisopac', $compromisopac, PDO::PARAM_STR);
      $stmt->bindValue(':tbl_secretarias_id', $tbl_secretarias_id, PDO::PARAM_INT);
      $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
      $stmt->bindValue(':respuesta', $respuesta, PDO::PARAM_STR);
      $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
      $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);

      if ($stmt->execute()) {
        // Manejar actualización de imagen si se envió
        if (isset($files['imagen']) && $files['imagen']['error'] === UPLOAD_ERR_OK) {
          $uploadDir = '../../assets/img/admin/';
          $imgName = uniqid() . '_' . basename($files['imagen']['name']);
          $imgTarget = $uploadDir . $imgName;

          if (move_uploaded_file($files['imagen']['tmp_name'], $imgTarget)) {
            $qImg = "UPDATE " . $tableVisitas . " SET img = :img WHERE id = :id";
            $stmtImg = $pdo->prepare($qImg);
            $stmtImg->bindValue(':img', $imgName, PDO::PARAM_STR);
            $stmtImg->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtImg->execute();
          }
        }

        return ['state' => true, 'message' => 'Compromiso actualizado correctamente.'];
      } else {
        return ['state' => false, 'message' => 'Error al actualizar el compromiso.'];
      }
    } catch (PDOException $e) {
      return ['state' => false, 'message' => 'Error: ' . $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }

  /**
   * Obtener visita por ID
   */
  public static function getVisitaId($data)
  {

    $id = is_array($data) && isset($data['id']) ? intval($data['id']) : intval($data);

    if ($id === 0) {
      return ['state' => false, 'message' => 'ID no válido', 'data' => []];
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    $tableVisitas = $db->getTable('tbl_visitas_alcalde');
    $tableCiudades = $db->getTable('tbl_ciudades');
    $tableVereda = $db->getTable('tbl_vereda');
    $tableSecretarias = $db->getTable('tbl_secretarias');

    $q = "SELECT v.*,
                 c.municipio AS municipio,
                 vr.nombre_vereda AS vereda,
                 s.secretaria
          FROM " . $tableVisitas . " AS v
          INNER JOIN " . $tableCiudades . " AS c ON v.tbl_municipio_id = c.codigo_muncipio
          INNER JOIN " . $tableVereda . " AS vr ON v.tbl_vereda_id = vr.id
          LEFT JOIN " . $tableSecretarias . " AS s ON v.tbl_secretarias_id = s.id
          WHERE v.id = :id";

    $stmt = $pdo->prepare($q);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $db->closeConect();

    if ($result) {
      // Formatear imagen si existe
      if (!empty($result['img'])) {
        if ($result['img'] == '4fbe830e44ff09970c4e3f979bad58849a3klsrvh4.png') {
          $result['foto'] = 'assets/img/santander.png';
        } else {
          $result['foto'] = 'assets/img/admin/' . $result['img'];
        }
      } else {
        $result['foto'] = '';
      }

      return ['state' => true, 'data' => [$result]];
    } else {
      return ['state' => false, 'message' => 'No se encontró el registro', 'data' => []];
    }
  }

  /**
   * Obtener compromiso por ID
   */
  public static function getCompromisoId($data)
  {
    return self::getVisitaId($data);
  }

  /**
   * Obtener indicadores por tipo de visita
   */
  public static function indicadoresTipoVisita($municipioId = null)
  {
    if (empty($municipioId)) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $municipioId = SessionData::getCodigoMunicipio();
    }
    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "SELECT
              a.tipo_visita,
              COUNT(*) AS total
            FROM
              " . $db->getTable('tbl_visitas_alcalde') . " a
            WHERE
              a.tipo_registro = 'Visita'";

      $params = [];
      if (!empty($municipioId)) {
          $q .= " AND CAST(a.tbl_municipio_id AS CHAR) = :municipio";
          $params[':municipio'] = (string)$municipioId;
      }

      $q .= " GROUP BY a.tipo_visita ORDER BY total DESC";

      $stmt = $pdo->prepare($q);
      $stmt->execute($params);

      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $db->closeConect();

      return [
        'state' => true,
        'data' => $result
      ];
    } catch (PDOException $th) {
      return [
        'state' => false,
        'message' => $th->getMessage(),
        'data' => []
      ];
    }
  }

  /**
   * Obtiene visitas simples por municipio para estado_municipios_alcalde.php
   * @param array $data Array con tbl_municipio_id
   * @return array Respuesta con visitas del municipio
   */
  public static function getVisitasPorMunicipio($data)
  {
    // Convertir a INT para asegurar compatibilidad con la columna INT de la BD
    $municipioId = isset($data['tbl_municipio_id']) ? intval($data['tbl_municipio_id']) : 0;

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "SELECT
              v.*,
              ver.nombre_vereda AS vereda,
              c.municipio
            FROM " . $db->getTable('tbl_visitas_alcalde') . " v
            LEFT JOIN " . $db->getTable('tbl_vereda') . " ver ON v.tbl_vereda_id = ver.id
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
              ON v.tbl_municipio_id = CAST(c.codigo_muncipio AS UNSIGNED)
            WHERE v.tbl_municipio_id = :municipioId
              AND v.tipo_registro = 'Visita'
            ORDER BY v.date DESC";

      $stmt = $pdo->prepare($q);
      $stmt->bindParam(':municipioId', $municipioId, PDO::PARAM_INT);
      $stmt->execute();

      $visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $db->closeConect();

      return [
        'output' => [
          'valid' => true,
          'response' => $visitas
        ]
      ];
    } catch (PDOException $th) {
      return [
        'output' => [
          'valid' => false,
          'response' => [],
          'message' => $th->getMessage()
        ]
      ];
    }
  }
}
