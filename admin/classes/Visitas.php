<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Visitas
{

  public function __construct() {}

  public static function getAll($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? $rqst['tipo'] : '';
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? $rqst['tbl_municipio_id'] : '';

    $tipoUsuario = SessionData::getUserType();
    $codigoMunicipio = SessionData::getCodigoMunicipio();

    $db = new DbConection();
    $pdo = $db->openConect();

    // Base query
    $q = "SELECT v.*, 
                     c.municipio AS municipio, 
                     s.secretaria
              FROM " . $db->getTable('tbl_visitas') . " AS v
              INNER JOIN " . $db->getTable('tbl_ciudades') . " AS c
                  ON v.tbl_municipio_id = c.codigo_muncipio
              LEFT JOIN " . $db->getTable('tbl_secretarias') . " AS s
                  ON v.tbl_secretarias_id = s.id
              WHERE 1=1";

    // Add filters dynamically
    $params = [];
    if ($id > 0) {
      $q .= " AND v.id = :id";
      $params[':id'] = $id;
    }

    if (!empty($tbl_municipio_id)) {
      $q .= " AND v.tbl_municipio_id = :tbl_municipio_id";
      $params[':tbl_municipio_id'] = $tbl_municipio_id;
    }

    if (!empty($tipo)) {
      $q .= " AND v.tipo = :tipo";
      $params[':tipo'] = $tipo;
    }

    // Si es alcalde o auxiliar de alcalde
    if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario) {
      $q .= " AND c.codigo_muncipio = :codigo_muncipio";
      $params[':codigo_muncipio'] = $codigoMunicipio;
    }


    $q .= " ORDER BY v.date DESC";



    // Prepare and execute query
    $stmt = $pdo->prepare($q);
    $stmt->execute($params);

    // Fetch results
    $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $db->closeConect();

    if ($arr) {
      return [
        'output' => [
          'valid' => true,
          'response' => $arr
        ]
      ];
    } else {
      return Util::error_no_result();
    }
  }

 
public static function getAllCompensc($rqst)
{
    $id = isset($rqst['id']) ? (int)$rqst['id'] : 0;

    $params = [];
    $where  = [];

    try {
        $db  = new DbConection();
        $pdo = $db->openConect();

        // Si quieres filtrar por un registro específico (opcional)
        if ($id > 0) {
            $where[] = "id = :id";
            $params[':id'] = $id;
        }

        // Base query (COUNT)
        $query = "
            SELECT COUNT(*) AS total_sin_cumplir
            FROM " . $db->getTable('tbl_visitas') . "
            WHERE tipo_registro = 'COMPROMISO'
              AND estado = 'SIN CUMPLIR'
        ";

        // Aplica filtro por id si llega
        if (!empty($where)) {
            $query .= " AND " . implode(" AND ", $where);
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        // COUNT devuelve una sola fila
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = ($row !== false)
            ? ['output' => ['valid' => true, 'response' => $row]]
            : Util::error_no_result();

    } catch (Exception $e) {
        $response = Util::error_general("Error al obtener compromisos sin cumplir: " . $e->getMessage());
    }

    if (isset($db)) {
        $db->closeConect();
    }

    return $response;
}

public static function getAllTotcompro($rqst)
{
    $id = isset($rqst['id']) ? (int)$rqst['id'] : 0;

    $params = [];
    $where  = [];

    try {
        $db  = new DbConection();
        $pdo = $db->openConect();

        // Si quieres filtrar por un registro específico (opcional)
        if ($id > 0) {
            $where[] = "id = :id";
            $params[':id'] = $id;
        }

        // Base query (COUNT)
        $query = "
            SELECT COUNT(*) AS total_compromisos
            FROM " . $db->getTable('tbl_visitas') . "
            
        ";

        // Aplica filtro por id si llega
        if (!empty($where)) {
            $query .= " AND " . implode(" AND ", $where);
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        // COUNT devuelve una sola fila
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = ($row !== false)
            ? ['output' => ['valid' => true, 'response' => $row]]
            : Util::error_no_result();

    } catch (Exception $e) {
        $response = Util::error_general("Error al obtener compromisos sin cumplir: " . $e->getMessage());
    }

    if (isset($db)) {
        $db->closeConect();
    }

    return $response;
}


  public static function getAllCom($rqst)
  {

    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : '';
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';

    $db = new DbConection();
    $pdo = $db->openConect();


    $q = "SELECT v.*, c.municipio, s.secretaria
    FROM " . $db->getTable('tbl_visitas') . " AS v
    INNER JOIN " . $db->getTable('tbl_ciudades') . " AS c ON v.tbl_municipio_id = c.codigo_muncipio
    LEFT JOIN " . $db->getTable('tbl_secretarias') . " AS s ON v.tbl_secretarias_id = s.id
    WHERE v.tipo='Compromiso'  ORDER BY v.id DESC";



    if ($id > 0) {

      $q = "SELECT v.*, c.municipio, s.secretaria
      FROM " . $db->getTable('tbl_visitas') . " AS v
      INNER JOIN " . $db->getTable('tbl_ciudades') . " AS c ON v.tbl_municipio_id = c.codigo_muncipio
      LEFT JOIN " . $db->getTable('tbl_secretarias') . " AS s ON v.tbl_secretarias_id = s.id
      WHERE v.id = " . $id . " and v.tipo='Compromiso' ";
    }

    if ($tipo != "") {
      $q = "SELECT * FROM " . $db->getTable('tbl_visitas') . " ";
    }
    if ($tbl_municipio_id != "") {
      $q = "SELECT * FROM " . $db->getTable('tbl_visitas') . " WHERE tbl_municipio_id=" . $tbl_municipio_id;
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

  public function getAllCompromiseFiltrosSelectSecretariaSessionUsuario($data)
  {
    // Iniciar sesión si no está iniciada
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $draw = intval($data['draw'] ?? 1);
    $start = intval($data['start'] ?? 0);
    $length = intval($data['length'] ?? 10);
    $orderDir = $data['order'][0]['dir'] ?? 'desc';
    $searchValue = $data['search']['value'] ?? '';

    // Obtener los filtros del frontend
    $idFiltro = intval($data['id'] ?? 0);
    $frontendSecretaria = intval($data['secretaria'] ?? 0);
    $frontendMunicipio = intval($data['municipio'] ?? 0);
    $componente = $data['componente'] ?? '';
    $provincia = $data['provincia'] ?? '';
    $estado = $data['estado'] ?? '';


    // --- Obtener información del usuario de la sesión ---
    $userType = SessionData::getUserType();
    $sessionSecretaria = SessionData::getSecretaria();
    $sessionMunicipio = SessionData::getCodigoMunicipio();

    // --- Definir roles para facilitar la lógica ---
    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
    $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob() || $userType === Util::Secretaria_Despacho_Gobernacion());
    $isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

      // Columnas para ordenamiento dinámico
      $columns = [
        'secretaria.secretaria', // 0
        'visitas.compromisos',   // 1
        'visitas.compromisopac', // 2
        'visitas.consecuencia',  // 3
        'visitas.respuesta',     // 4
        'visitas.estado',        // 5
        'ciudad.municipio',      // 6
        'visitas.provincia',     // 7
        'visitas.img',           // 8
        'visitas.date',          // 9
        'visitas.id',            // 10
        'visitas.componente',    // 11
        'visitas.tipo_ejecucion', // 12
      ];
      $orderColumnIndex = $data['order'][0]['column'] ?? 9; // Default to 'DATE' (index 9)
      $orderBy = $columns[$orderColumnIndex] ?? 'visitas.DATE';

      // --- Construir la cláusula WHERE base (tipo_registro y filtros de rol) ---
      // Se incluye IS NULL para registros con componente='COMPROMISOS NUEVOS' que tienen tipo_registro nulo
      $baseWhere = " (TRIM(UPPER(REPLACE(visitas.tipo_registro, CHAR(160), ''))) = 'COMPROMISO' OR visitas.tipo_registro IS NULL) ";
      $baseWhere .= " AND visitas.estado IS NOT NULL AND TRIM(REPLACE(visitas.estado, CHAR(160), '')) != '' AND TRIM(UPPER(REPLACE(visitas.estado, CHAR(160), ''))) != 'NULL' ";
      $baseParams = []; // Parámetros para baseWhere (solo rol y frontend no-search)

      $isSecretariaFilteredByRole = false;
      $isMunicipioFilteredByRole = false;

      if ($isSecretario && intval($sessionSecretaria) > 0) {
        $baseWhere .= " AND visitas.tbl_secretarias_id = :sessionSecretariaId";
        $baseParams[':sessionSecretariaId'] = $sessionSecretaria;
        $isSecretariaFilteredByRole = true;
      } elseif ($isAlcaldeOAuxiliar) {
        $baseWhere .= " AND visitas.tbl_municipio_id = :sessionMunicipioId";
        $baseParams[':sessionMunicipioId'] = $sessionMunicipio;
        $isMunicipioFilteredByRole = true;
      }
      // Si es Admin, no se añade ninguna condición específica de usuario a baseWhere.

      // --- Añadir filtros del frontend a baseWhere si no están ya cubiertos por el rol ---
      if ($idFiltro > 0) {
        $baseWhere .= " AND visitas.id = :idFiltro";
        $baseParams[':idFiltro'] = $idFiltro;
      }
      if ($frontendMunicipio > 0 && !$isMunicipioFilteredByRole) {
        $baseWhere .= " AND ciudad.codigo_muncipio = :frontendMunicipio";
        $baseParams[':frontendMunicipio'] = $frontendMunicipio;
      }
      if ($frontendSecretaria > 0 && !$isSecretariaFilteredByRole) {
        $baseWhere .= " AND secretaria.id = :frontendSecretaria";
        $baseParams[':frontendSecretaria'] = $frontendSecretaria;
      }
      if (!empty($componente)) {
        $baseWhere .= " AND visitas.componente LIKE :componente";
        $baseParams[':componente'] = "%" . $componente . "%";
      }
      if (!empty($provincia)) {
        $baseWhere .= " AND visitas.provincia = :provincia";
        $baseParams[':provincia'] = $provincia;
      }
      if (!empty($estado)) {
        $baseWhere .= " AND visitas.estado = :estado";
        $baseParams[':estado'] = $estado;
        error_log("Aplicando filtro de estado: " . $estado);
      }


      // --- Construir la cláusula de búsqueda ---
      $searchWhere = "";
      $searchParams = [];
      if (!empty($searchValue)) {
        $searchWhere = " AND (
                  visitas.DATE LIKE :search OR
                  ciudad.municipio LIKE :search OR
                  visitas.provincia LIKE :search OR
                  visitas.estado LIKE :search OR
                  secretaria.secretaria LIKE :search OR
                  visitas.componente LIKE :search OR
                  visitas.tipo_ejecucion LIKE :search OR
                  visitas.compromisos LIKE :search OR
                  visitas.compromisopac LIKE :search
              )";
        $searchParams[':search'] = "%" . $searchValue . "%";
        if (!empty($provincia)) {
            $searchWhere .= " AND visitas.provincia = :provincia";
        }

      }

      // Combinar todos los parámetros para las consultas con búsqueda
      $allParams = array_merge($baseParams, $searchParams);
      if (!empty($provincia)) {
          $allParams[':provincia'] = $provincia;
      }


      // --- Consulta para el total de registros (respetando filtros de rol/frontend, SIN búsqueda) ---
      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
                      LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                      LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                      WHERE " . $baseWhere;
      $stmtTotal = $pdo->prepare($sqlTotal);
      $stmtTotal->execute($baseParams); // Solo baseParams
      $totalRecords = $stmtTotal->fetchColumn();

      // --- Consulta para el total de registros filtrados (respetando todos los filtros, INCLUIDA búsqueda) ---
      $sqlFiltered = "SELECT COUNT(*) FROM $tableCompromisos
                        LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                        LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                        WHERE " . $baseWhere . $searchWhere;
      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($allParams); // Todos los parámetros
      $filteredRecords = $stmtFiltered->fetchColumn();

      // --- Consulta para obtener los datos ---
      $sqlData = "SELECT
                      visitas.DATE,
                      ciudad.municipio,
                      visitas.compromisos,
                      visitas.provincia,
                      visitas.respuesta,
                      secretaria.secretaria,
                      visitas.img,
                      visitas.id,
                      visitas.estado,
                      visitas.consecuencia,
                      visitas.compromisopac,
                      visitas.componente,
                      visitas.tipo_ejecucion
                    FROM $tableCompromisos
                    LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                    LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                    WHERE " . $baseWhere . $searchWhere . "
                    ORDER BY $orderBy $orderDir
                    LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

      // Bindear todos los parámetros combinados
      foreach ($allParams as $key => $value) {
        $stmtData->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
      }

      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
        $imagen = '';
        if (!empty($row['img'])) {
          if (!is_string($row['img'])) {
            $imgData = base64_encode($row['img']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            $imgSrc = ($row['img'] == '4fbe830e44ff09970c4e3f979bad58849a3klsrvh4.png')
              ? 'assets/img/santander.png'
              : 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
          }
          $imagen = '<img src="' . $imgSrc . '" width="100" height="auto" class="rounded-circle" />';
        }

        $data[] = [
          'date' => $row['DATE'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id' => $row['id'],
          'componente' => $row['componente'],
          'tipo_ejecucion' => $row['tipo_ejecucion']
        ];
      }

      return [
        "draw" => $draw,
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      return ['state' => false, 'error' => $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }
 public function getAllCompromisecumplidosFiltrosSelect($data)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $draw = intval($data['draw'] ?? 1);
    $start = intval($data['start'] ?? 0);
    $length = intval($data['length'] ?? 10);
    $orderDir = $data['order'][0]['dir'] ?? 'desc';
    $searchValue = $data['search']['value'] ?? '';

    // Filtros frontend
    $idFiltro = intval($data['id'] ?? 0);
    $frontendSecretaria = intval($data['secretaria'] ?? 0);
    $frontendMunicipio = intval($data['municipio'] ?? 0);
    $componente = $data['componente'] ?? '';
    $provincia = $data['provincia'] ?? '';

    // Datos del usuario
    $userType = SessionData::getUserType();
    $sessionSecretaria = SessionData::getSecretaria();
    $sessionMunicipio = SessionData::getCodigoMunicipio();

    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
    $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob());
    $isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    try {
        $db = new DbConection();
        $pdo = $db->openConect();

        $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
        $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
        $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

        $columns = [
            'secretaria.secretaria',
            'visitas.compromisos',
            'visitas.compromisopac',
            'visitas.consecuencia',
            'visitas.respuesta',
            'visitas.estado',
            'ciudad.municipio',
            'visitas.provincia',
            'visitas.img',
            'visitas.date',
            'visitas.id',
            'visitas.componente',
            'visitas.tipo_ejecucion',
        ];
        $orderColumnIndex = $data['order'][0]['column'] ?? 9;
        $orderBy = $columns[$orderColumnIndex] ?? 'visitas.DATE';

        // ============================
        // WHERE BASE (incluye SOLO CUMPLIDOS)
        // ============================
        $baseWhere = " visitas.tipo_registro='Compromiso'
                       AND LOWER(visitas.estado) LIKE '%cumplido%' ";
        $baseParams = [];

        $secretariaFilteredByRole = false;
        $municipioFilteredByRole = false;

        // Filtros por rol
        if ($isSecretario && $sessionSecretaria !== '000') {
            $baseWhere .= " AND visitas.tbl_secretarias_id = :sessionSecretariaId";
            $baseParams[':sessionSecretariaId'] = $sessionSecretaria;
            $secretariaFilteredByRole = true;
        } elseif ($isAlcaldeOAuxiliar) {
            $baseWhere .= " AND visitas.tbl_municipio_id = :sessionMunicipioId";
            $baseParams[':sessionMunicipioId'] = $sessionMunicipio;
            $municipioFilteredByRole = true;
        }

        // Filtros del frontend
        if ($idFiltro > 0) {
            $baseWhere .= " AND visitas.id = :idFiltro";
            $baseParams[':idFiltro'] = $idFiltro;
        }

        if ($frontendMunicipio > 0 && !$municipioFilteredByRole) {
            $baseWhere .= " AND ciudad.codigo_muncipio = :frontendMunicipio";
            $baseParams[':frontendMunicipio'] = $frontendMunicipio;
        }

        if ($frontendSecretaria > 0 && !$secretariaFilteredByRole) {
            $baseWhere .= " AND secretaria.id = :frontendSecretaria";
            $baseParams[':frontendSecretaria'] = $frontendSecretaria;
        }

        if (!empty($componente)) {
            $baseWhere .= " AND visitas.componente LIKE :componente";
            $baseParams[':componente'] = "%$componente%";
        }

        if (!empty($provincia)) {
            $baseWhere .= " AND visitas.provincia = :provincia";
            $baseParams[':provincia'] = $provincia;
        }

        // ============================
        // BÚSQUEDA
        // ============================
        $searchWhere = "";
        $searchParams = [];

        if (!empty($searchValue)) {
            $searchWhere = " AND (
                visitas.DATE LIKE :search OR
                ciudad.municipio LIKE :search OR
                visitas.provincia LIKE :search OR
                visitas.estado LIKE :search OR
                secretaria.secretaria LIKE :search OR
                visitas.componente LIKE :search OR
                visitas.tipo_ejecucion LIKE :search OR
                visitas.compromisos LIKE :search OR
                visitas.compromisopac LIKE :search
            )";
            $searchParams[':search'] = "%$searchValue%";
        }

        $allParams = array_merge($baseParams, $searchParams);

        // ============================
        // TOTAL
        // ============================
        $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
                     INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                     INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                     WHERE $baseWhere";

        $stmtTotal = $pdo->prepare($sqlTotal);
        $stmtTotal->execute($baseParams);
        $totalRecords = $stmtTotal->fetchColumn();

        // ============================
        // TOTAL FILTRADO
        // ============================
        $sqlFiltered = "SELECT COUNT(*) FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                        WHERE $baseWhere $searchWhere";

        $stmtFiltered = $pdo->prepare($sqlFiltered);
        $stmtFiltered->execute($allParams);
        $filteredRecords = $stmtFiltered->fetchColumn();

        // ============================
        // DATA
        // ============================
        $sqlData = "SELECT
                        visitas.DATE,
                        ciudad.municipio,
                        visitas.compromisos,
                        visitas.provincia,
                        visitas.respuesta,
                        secretaria.secretaria,
                        visitas.img,
                        visitas.id,
                        visitas.estado,
                        visitas.consecuencia,
                        visitas.compromisopac,
                        visitas.componente,
                        visitas.tipo_ejecucion
                    FROM $tableCompromisos
                    LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                    LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                    WHERE $baseWhere $searchWhere
                    ORDER BY $orderBy $orderDir
                    LIMIT :start, :length";

        $stmtData = $pdo->prepare($sqlData);
        $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
        $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

        foreach ($allParams as $key => $value) {
            $stmtData->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmtData->execute();
        $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        // ============================
        // FORMATO PARA DATATABLES
        // ============================
        $dataOutput = [];
        foreach ($result as $row) {
            $imagen = "";
            if (!empty($row['img'])) {
                $imgSrc = ($row['img'] == '4fbe830e44ff09970c4e3f979bad58849a3klsrvh4.png')
                    ? 'assets/img/santander.png'
                    : 'assets/img/admin/' . $row['img'];

                $imagen = '<img src="' . $imgSrc . '" width="100" class="rounded-circle" />';
            }

            $dataOutput[] = [
                'date' => $row['DATE'],
                'municipio' => $row['municipio'],
                'compromisos' => $row['compromisos'],
                'provincia' => $row['provincia'],
                'respuesta' => $row['respuesta'],
                'secretaria' => $row['secretaria'],
                'estado' => $row['estado'],
                'consecuencia' => $row['consecuencia'],
                'compromisopac' => $row['compromisopac'],
                'foto' => $imagen,
                'id' => $row['id'],
                'componente' => $row['componente'],
                'tipo_ejecucion' => $row['tipo_ejecucion']
            ];
        }

        return [
            "draw" => $draw,
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $dataOutput
        ];

    } catch (PDOException $e) {
        return ['state' => false, 'error' => $e->getMessage()];
    } finally {
        $db->closeConect();
    }
}

  public function getAllCompromiseBySecretariaSessionUsuario($data)
  {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $draw = $data['draw'];
    $start = intval($data['start']);
    $length = intval($data['length']);
    $searchValue = $data['search']['value'] ?? '';
    $orderDir = $data['order'][0]['dir'] ?? 'desc';


    $filtroSecretaria = $data['secretaria'] ?? null;
    $filtroMunicipio = $data['municipio'] ?? null;
    $filtroComponente = $data['componente'] ?? null;


    $userType = SessionData::getUserType();
    $secretariaUsuarioLogueado = SessionData::getSecretaria();
    $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();


    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

    $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob() || $userType === Util::Secretaria_Despacho_Gobernacion());
    $isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";


      $columns = [
        'secretaria.secretaria', // 0
        'visitas.compromisos',   // 1
        'visitas.compromisopac', // 2
        'visitas.consecuencia',  // 3
        'visitas.respuesta',     // 4
        'visitas.estado',        // 5
        'ciudad.municipio',      // 6
        'visitas.provincia',     // 7
        'visitas.img',           // 8
        'visitas.DATE',          // 9
        'visitas.id',            // 10
        'visitas.componente',    // 12
        'visitas.tipo_ejecucion', // 13
      ];


      $orderColumnIndex = $data['order'][0]['column'] ?? 9; 
      $orderBy = $columns[$orderColumnIndex] ?? 'visitas.DATE';


      $where = " TRIM(UPPER(REPLACE(visitas.tipo_registro, CHAR(160), ''))) = 'COMPROMISO' ";
      $where .= " AND visitas.estado IS NOT NULL AND TRIM(REPLACE(visitas.estado, CHAR(160), '')) != '' AND TRIM(UPPER(REPLACE(visitas.estado, CHAR(160), ''))) != 'NULL' ";
      $params = [];


      if ($isSecretario && intval($secretariaUsuarioLogueado) > 0) {
        $where .= " AND visitas.tbl_secretarias_id = :rolSecretariaId";
        $params[':rolSecretariaId'] = $secretariaUsuarioLogueado;
      } elseif ($isAlcaldeOAuxiliar) {
        $where .= " AND visitas.tbl_municipio_id = :rolMunicipioId";
        $params[':rolMunicipioId'] = $municipioUsuarioLogueado;
      }

      
      if (!empty($filtroSecretaria)) {
        $where .= " AND visitas.tbl_secretarias_id = :filtroSecretaria";
        $params[':filtroSecretaria'] = $filtroSecretaria;
      }

      if (!empty($filtroMunicipio)) {
        $where .= " AND visitas.tbl_municipio_id = :filtroMunicipio";
        $params[':filtroMunicipio'] = $filtroMunicipio;
      }
      
      if (!empty($filtroComponente)) {
          $where .= " AND visitas.componente = :filtroComponente";
          $params[':filtroComponente'] = $filtroComponente;
      }
      if (!empty($provincia)) {
    $where .= " AND visitas.provincia = :provincia";
    $params[':provincia'] = $provincia;
}

$provincia = $data['provincia'] ?? '';


      if (!empty($searchValue)) {
        $where .= " AND (
            visitas.DATE LIKE :search OR
            ciudad.municipio LIKE :search OR
            visitas.provincia LIKE :search OR
            visitas.estado LIKE :search OR
            secretaria.secretaria LIKE :search OR
            visitas.componente LIKE :search OR
            visitas.tipo_ejecucion LIKE :search OR
            visitas.compromisos LIKE :search OR 
            visitas.compromisopac LIKE :search 
        )";
        $params[':search'] = "%$searchValue%";
      }

      $sqlFiltered = "SELECT COUNT(*) 
                      FROM $tableCompromisos
                      LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                      LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                      WHERE $where";
      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params);
      $filteredRecords = $stmtFiltered->fetchColumn();

      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
                  LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                  LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                  WHERE TRIM(UPPER(REPLACE(visitas.tipo_registro, CHAR(160), ''))) = 'COMPROMISO'
                  AND visitas.estado IS NOT NULL AND TRIM(REPLACE(visitas.estado, CHAR(160), '')) != ''";


      $paramsTotal = []; 

      if ($isSecretario && $secretariaUsuarioLogueado !== '000') {
        $sqlTotal .= " AND visitas.tbl_secretarias_id = :rolSecretariaIdTotal";
        $paramsTotal[':rolSecretariaIdTotal'] = $secretariaUsuarioLogueado;
      } elseif ($isAlcaldeOAuxiliar) {
        $sqlTotal .= " AND visitas.tbl_municipio_id = :rolMunicipioIdTotal";
        $paramsTotal[':rolMunicipioIdTotal'] = $municipioUsuarioLogueado;
      }

      $stmtTotal = $pdo->prepare($sqlTotal);
      $stmtTotal->execute($paramsTotal);
      $totalRecords = $stmtTotal->fetchColumn();


      $sqlData = "SELECT
                visitas.DATE,
                ciudad.municipio,
                visitas.compromisos,
                visitas.provincia,
                visitas.respuesta,
                secretaria.secretaria,
                visitas.img,
                visitas.id,
                visitas.estado,
                visitas.consecuencia,
                visitas.compromisopac,
                visitas.componente,
                visitas.tipo_ejecucion
            FROM $tableCompromisos
            INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
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
          'date' => $row['DATE'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id' => $row['id'],
          'componente' => $row['componente'],
          'tipo_ejecucion' => $row['tipo_ejecucion']
        ];
      }

      return [
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      print_r($e);
      return
        ['state' => false, 'error' => $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }
   public function getAllCompromisecumplidos($data)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $draw = $data['draw'];
    $start = intval($data['start']);
    $length = intval($data['length']);
    $searchValue = $data['search']['value'] ?? '';
    $orderDir = $data['order'][0]['dir'] ?? 'desc';

    $filtroSecretaria = $data['secretaria'] ?? null;
    $filtroMunicipio = $data['municipio'] ?? null;
    $filtroComponente = $data['componente'] ?? null;
    $provincia = $data['provincia'] ?? null;

    $userType = SessionData::getUserType();
    $secretariaUsuarioLogueado = SessionData::getSecretaria();
    $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();

    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
    $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob() || $userType === Util::Secretaria_Despacho_Gobernacion());
    $isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    try {
        $db = new DbConection();
        $pdo = $db->openConect();

        $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
        $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
        $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

        $columns = [
            'secretaria.secretaria', 
            'visitas.compromisos',
            'visitas.compromisopac',
            'visitas.consecuencia',
            'visitas.respuesta',
            'visitas.estado',
            'ciudad.municipio',
            'visitas.provincia',
            'visitas.img',
            'visitas.DATE',
            'visitas.id',
            'visitas.componente',
            'visitas.tipo_ejecucion',
        ];

        $orderColumnIndex = $data['order'][0]['column'] ?? 9;
        $orderBy = $columns[$orderColumnIndex] ?? 'visitas.DATE';

        // ========================
        //   WHERE BASE
        // ========================
        $where = " visitas.tipo_registro = 'Compromiso' 
                   AND LOWER(visitas.estado) LIKE '%cumplido%' ";

        $params = [];

        if ($isSecretario && $secretariaUsuarioLogueado !== '000') {
            $where .= " AND visitas.tbl_secretarias_id = :rolSecretariaId";
            $params[':rolSecretariaId'] = $secretariaUsuarioLogueado;
        } elseif ($isAlcaldeOAuxiliar) {
            $where .= " AND visitas.tbl_municipio_id = :rolMunicipioId";
            $params[':rolMunicipioId'] = $municipioUsuarioLogueado;
        }

        if (!empty($filtroSecretaria)) {
            $where .= " AND visitas.tbl_secretarias_id = :filtroSecretaria";
            $params[':filtroSecretaria'] = $filtroSecretaria;
        }

        if (!empty($filtroMunicipio)) {
            $where .= " AND visitas.tbl_municipio_id = :filtroMunicipio";
            $params[':filtroMunicipio'] = $filtroMunicipio;
        }

        if (!empty($filtroComponente)) {
            $where .= " AND visitas.componente = :filtroComponente";
            $params[':filtroComponente'] = $filtroComponente;
        }

        if (!empty($provincia)) {
            $where .= " AND visitas.provincia = :provincia";
            $params[':provincia'] = $provincia;
        }

        if (!empty($searchValue)) {
            $where .= " AND (
                visitas.DATE LIKE :search OR
                ciudad.municipio LIKE :search OR
                visitas.provincia LIKE :search OR
                visitas.estado LIKE :search OR
                secretaria.secretaria LIKE :search OR
                visitas.componente LIKE :search OR
                visitas.tipo_ejecucion LIKE :search OR
                visitas.compromisos LIKE :search OR 
                visitas.compromisopac LIKE :search 
            )";
            $params[':search'] = "%$searchValue%";
        }

        // ==========================
        //  TOTAL
        // ==========================
        $sqlFiltered = "SELECT COUNT(*)
                      FROM $tableCompromisos
                      LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                      LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                        WHERE $where";
        $stmtFiltered = $pdo->prepare($sqlFiltered);
        $stmtFiltered->execute($params);
        $filteredRecords = $stmtFiltered->fetchColumn();

        $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
                     INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                     INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                     WHERE visitas.tipo_registro='Compromiso'
                     AND LOWER(visitas.estado) LIKE '%cumplido%'";

        $stmtTotal = $pdo->prepare($sqlTotal);
        $stmtTotal->execute();
        $totalRecords = $stmtTotal->fetchColumn();

        // ===========================
        //     DATA
        // ===========================
        $sqlData = "SELECT
                        visitas.DATE,
                        ciudad.municipio,
                        visitas.compromisos,
                        visitas.provincia,
                        visitas.respuesta,
                        secretaria.secretaria,
                        visitas.img,
                        visitas.id,
                        visitas.estado,
                        visitas.consecuencia,
                        visitas.compromisopac,
                        visitas.componente,
                        visitas.tipo_ejecucion
            FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            WHERE $where
            ORDER BY $orderBy $orderDir
            LIMIT :start, :length";

        $stmtData = $pdo->prepare($sqlData);
        $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
        $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            $stmtData->bindValue($key, $value);
        }

        $stmtData->execute();
        $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        $dataResult = [];

        foreach ($result as $row) {
            $imagen = '';

            if (!empty($row['img'])) {
                if ($row['img'] == '4fbe830e44ff09970c4e3f979bad58849a3klsrvh4.png') {
                    $imgSrc = 'assets/img/santander.png';
                } else {
                    $imgSrc = 'assets/img/admin/' . $row['img'];
                }
                $imagen = '<img src="' . $imgSrc . '" width="100" height="auto" class="rounded-circle" />';
            }

            $dataResult[] = [
                'date' => $row['DATE'],
                'municipio' => $row['municipio'],
                'compromisos' => $row['compromisos'],
                'provincia' => $row['provincia'],
                'respuesta' => $row['respuesta'],
                'secretaria' => $row['secretaria'],
                'estado' => $row['estado'],
                'consecuencia' => $row['consecuencia'],
                'compromisopac' => $row['compromisopac'],
                'foto' => $imagen,
                'id' => $row['id'],
                'componente' => $row['componente'],
                'tipo_ejecucion' => $row['tipo_ejecucion']
            ];
        }

        return [
            "draw" => intval($draw),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($filteredRecords),
            "data" => $dataResult
        ];
    } catch (PDOException $e) {
        return ['state' => false, 'error' => $e->getMessage()];
    } finally {
        $db->closeConect();
    }
}

  public function importarCompromisosExcel($rqst, $files)
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $userType = SessionData::getUserType();
    if (!in_array($userType, [Util::Administrador(), Util::SuperAdministrador()], true)) {
      return ['success' => false, 'message' => 'Acceso denegado.'];
    }

    if (!isset($files['excel_file']) || $files['excel_file']['error'] !== UPLOAD_ERR_OK) {
      return ['success' => false, 'message' => 'No se encontró el archivo o hubo un error en la carga.'];
    }

    $uploadedFile = $files['excel_file'];
    $fileName = $uploadedFile['name'];
    $fileTmp = $uploadedFile['tmp_name'];
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($extension !== 'xlsx') {
      return ['success' => false, 'message' => 'Solo se permiten archivos .xlsx.'];
    }

    require_once __DIR__ . '/../../vendor/autoload.php';

    try {
      $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmp);
      $worksheet = $spreadsheet->getActiveSheet();
      $rows = $worksheet->toArray(null, true, true, true);

      if (count($rows) < 2) {
        return ['success' => false, 'message' => 'El archivo no contiene filas de datos.'];
      }

      $normalize = function ($value) {
        $value = mb_strtolower(trim((string)$value));
        $value = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $value);
        return preg_replace('/[^a-z0-9 ]+/', '', $value);
      };

      $headerRow = $rows[1];
      $headerMap = [];
      foreach ($headerRow as $column => $value) {
        $key = $normalize($value);
        if ($key !== '') {
          $headerMap[$key] = $column;
        }
      }

      $requiredHeaders = [
        'id',
        'secretaria',
        'provincia',
        'municipio',
        'estado',
        'compromiso pactado',
        'respuesta',
        'componente',
        'tipo ejecucion',
        'observaciones'
      ];

      $missingHeaders = [];
      foreach ($requiredHeaders as $requiredHeader) {
        if (!isset($headerMap[$requiredHeader])) {
          $missingHeaders[] = $requiredHeader;
        }
      }

      if (!empty($missingHeaders)) {
        return [
          'success' => false,
          'message' => 'Faltan columnas en el encabezado: ' . implode(', ', $missingHeaders)
        ];
      }

      $allowedProvincias = [
        'soto norte' => 'Soto Norte',
        'guanenta' => 'Guanenta',
        'garcia rovira' => 'Garcia Rovira',
        'comunera' => 'Comunera',
        'velez' => 'Velez',
        'metropolitana' => 'Metropolitana',
        'yariguies' => 'Yariguies',
      ];

      $allowedEstados = [
        'cumplido' => 'Cumplido',
        'en tramite' => 'En Trámite',
        'en trámite' => 'En Trámite',
        'sin cumplir' => 'Sin Cumplir',
        'en espera' => 'En Espera',
        'espera' => 'En Espera'
      ];

      $allowedTipos = [
        'inversion' => 'INVERSIÓN',
        'inversión' => 'INVERSIÓN',
        'gestion' => 'GESTIÓN',
        'gestión' => 'GESTIÓN'
      ];

      $isRowBlank = function (array $row) use ($headerMap) {
        foreach ($headerMap as $column) {
          if (trim((string)($row[$column] ?? '')) !== '') {
            return false;
          }
        }
        return true;
      };

      $errors = [];
      $rowsToUpdate = [];

      $db = new DbConection();
      $pdo = $db->openConect();

      $stmtCiudad = $pdo->prepare(
        "SELECT codigo_muncipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " " .
        "WHERE LOWER(TRIM(municipio)) = LOWER(TRIM(:municipio)) " .
        "AND codigo_departamento = 68 LIMIT 1"
      );

      $stmtCiudadLike = $pdo->prepare(
        "SELECT codigo_muncipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " " .
        "WHERE LOWER(municipio) LIKE :pattern " .
        "AND codigo_departamento = 68 LIMIT 1"
      );

      $stmtAllCities = $pdo->prepare(
        "SELECT codigo_muncipio, LOWER(TRIM(municipio)) AS nombre FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " " .
        "WHERE codigo_departamento = 68"
      );
      $stmtAllCities->execute();
      $allCities = $stmtAllCities->fetchAll(PDO::FETCH_ASSOC);

      $stmtSecretaria = $pdo->prepare(
        "SELECT id FROM " . $db->getTable('tbl_secretarias') . " " .
        "WHERE LOWER(TRIM(secretaria)) = LOWER(TRIM(:secretaria)) LIMIT 1"
      );

      $stmtCompromiso = $pdo->prepare(
        "SELECT id, compromisopac, respuesta, descripcion_hecho, date, tbl_municipio_id FROM " . $db->getTable('tbl_visitas') . " " .
        "WHERE id = :id AND tipo_registro = 'Compromiso' LIMIT 1"
      );

      foreach ($rows as $rowIndex => $row) {
        if ($rowIndex === 1) {
          continue;
        }

        if ($isRowBlank($row)) {
          continue;
        }

        $id = trim((string)($row[$headerMap['id']] ?? ''));
        $secretaria = trim((string)($row[$headerMap['secretaria']] ?? ''));
        $fecha = isset($headerMap['fecha']) ? trim((string)($row[$headerMap['fecha']] ?? '')) : '';
        $provincia = trim((string)($row[$headerMap['provincia']] ?? ''));
        $municipio = isset($headerMap['municipio']) ? trim((string)($row[$headerMap['municipio']] ?? '')) : '';
        $estado = trim((string)($row[$headerMap['estado']] ?? ''));
        $compromisopac = trim((string)($row[$headerMap['compromiso pactado']] ?? ''));
        $respuesta = trim((string)($row[$headerMap['respuesta']] ?? ''));
        $componente = mb_strtoupper(trim((string)($row[$headerMap['componente']] ?? '')));
        $tipoEjecucion = trim((string)($row[$headerMap['tipo ejecucion']] ?? ''));
        $descripcionHecho = trim((string)($row[$headerMap['observaciones']] ?? ''));

        $rowErrors = [];
        if ($id === '') {
          $rowErrors[] = "Fila $rowIndex: el campo ID es obligatorio.";
        } elseif (!ctype_digit($id)) {
          $rowErrors[] = "Fila $rowIndex: el campo ID debe ser un número entero.";
        }

        if ($secretaria === '') {
          $rowErrors[] = "Fila $rowIndex: Secretaría es obligatoria.";
        }
        if ($provincia === '') {
          $rowErrors[] = "Fila $rowIndex: Provincia es obligatoria.";
        }
        if ($estado === '') {
          $rowErrors[] = "Fila $rowIndex: Estado es obligatorio.";
        }
        if ($respuesta === '') {
          $respuesta = '';
        }
        if ($componente === '') {
          $rowErrors[] = "Fila $rowIndex: Componente es obligatorio.";
        }
        if ($tipoEjecucion === '') {
          $rowErrors[] = "Fila $rowIndex: Tipo Ejecución es obligatorio.";
        }

        if ($municipio === '') {
          $rowErrors[] = "Fila $rowIndex: Municipio es obligatorio.";
        }

        if (!empty($rowErrors)) {
          $errors = array_merge($errors, $rowErrors);
          continue;
        }

        $normalizedEstado = $normalize($estado);
        $estadoCanonical = $allowedEstados[$normalizedEstado] ?? null;
        if ($estadoCanonical === null) {
          $rowErrors[] = "Fila $rowIndex: Estado inválido. Valores permitidos: Cumplido, En Trámite, Sin Cumplir, En Espera.";
        }

        $normalizedProvincia = $normalize($provincia);
        $provinciaCanonical = $allowedProvincias[$normalizedProvincia] ?? null;
        if ($provinciaCanonical === null) {
          $rowErrors[] = "Fila $rowIndex: Provincia inválida. Valores permitidos: Soto Norte, Guanenta, Garcia Rovira, Comunera, Velez, Metropolitana, Yariguies.";
        }

        $normalizedTipo = $normalize($tipoEjecucion);
        $tipoCanonical = $allowedTipos[$normalizedTipo] ?? null;
        if ($tipoCanonical === null) {
          $rowErrors[] = "Fila $rowIndex: Tipo Ejecución inválido. Valores permitidos: INVERSIÓN, GESTIÓN.";
        }

        if (!empty($rowErrors)) {
          $errors = array_merge($errors, $rowErrors);
          continue;
        }

        $dateObject = null;
        if ($fecha !== '' && isset($headerMap['fecha'])) {
          $fechaClean = preg_replace('/\.\d+$/', '', $fecha);
          $dateObject = \DateTime::createFromFormat('Y-m-d H:i:s', $fechaClean)
            ?: \DateTime::createFromFormat('Y-m-d', $fechaClean)
            ?: \DateTime::createFromFormat('d/m/Y H:i', $fechaClean)
            ?: \DateTime::createFromFormat('d/m/Y', $fechaClean)
            ?: \DateTime::createFromFormat('d-m-Y H:i', $fechaClean)
            ?: \DateTime::createFromFormat('d-m-Y', $fechaClean)
            ?: \DateTime::createFromFormat('Y-m-d H:i:s.v', $fecha);

          if (!$dateObject) {
            $dateCell = $worksheet->getCell($headerMap['fecha'] . $rowIndex);
            $rawValue = $dateCell->getValue();
            if (is_numeric($rawValue)) {
              try {
                $dateObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawValue);
              } catch (\Exception $e) {
                // Not a valid Excel serial date
              }
            }
          }

          if ($dateObject) {
            $dateObject->setTime(0, 0, 0);
          }
        }

        $tblMunicipioId = null;
        $municipioNormalized = trim(preg_replace('/[-\s]+/', ' ', mb_strtolower($municipio)));
        $municipioNormalized = str_replace(
          ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
          ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
          $municipioNormalized
        );
        $stmtCiudad->execute([':municipio' => $municipioNormalized]);
        $ciudad = $stmtCiudad->fetch(PDO::FETCH_ASSOC);

        if (!$ciudad) {
          $likePattern = '%' . str_replace(' ', '%', $municipioNormalized) . '%';
          $stmtCiudadLike->execute([':pattern' => $likePattern]);
          $ciudad = $stmtCiudadLike->fetch(PDO::FETCH_ASSOC);
        }

        if (!$ciudad) {
          $bestMatch = null;
          $bestDist = 3;
          foreach ($allCities as $c) {
            $dist = levenshtein($municipioNormalized, $c['nombre']);
            if ($dist < $bestDist) {
              $bestDist = $dist;
              $bestMatch = $c;
            }
          }
          if ($bestMatch) {
            $ciudad = $bestMatch;
          }
        }

        if ($ciudad) {
          $tblMunicipioId = intval($ciudad['codigo_muncipio']);
        } else {
          $errors[] = "Fila $rowIndex: Municipio '$municipio' no encontrado en la base de datos.";
          continue;
        }

        $stmtSecretaria->execute([':secretaria' => mb_strtolower($secretaria)]);
        $secretariaRow = $stmtSecretaria->fetch(PDO::FETCH_ASSOC);
        if (!$secretariaRow) {
          $errors[] = "Fila $rowIndex: Secretaría '$secretaria' no encontrada.";
          continue;
        }

        $stmtCompromiso->execute([':id' => intval($id)]);
        $compromisoRow = $stmtCompromiso->fetch(PDO::FETCH_ASSOC);
        if (!$compromisoRow) {
          $errors[] = "Fila $rowIndex: ID $id no existe como compromiso en la base de datos.";
          continue;
        }

        $rowsToUpdate[] = [
          'id' => intval($id),
          'tbl_secretarias_id' => intval($secretariaRow['id']),
          'date' => $dateObject ? $dateObject->format('Y-m-d H:i:s') : null,
          'provincia' => $provinciaCanonical,
          'tbl_municipio_id' => $tblMunicipioId,
          'estado' => $estadoCanonical,
          'compromisopac' => $compromisopac !== '' ? $compromisopac : null,
          'respuesta' => $respuesta,
          'componente' => $componente,
          'tipo_ejecucion' => $tipoCanonical,
          'descripcion_hecho' => $descripcionHecho,
        ];
      }

      if (!empty($errors)) {
        return [
          'success' => false,
          'message' => 'Se encontraron errores de validación.',
          'errors' => $errors
        ];
      }

      if (empty($rowsToUpdate)) {
        return ['success' => false, 'message' => 'No se encontraron filas válidas para procesar.'];
      }

      $stmtUpdate = $pdo->prepare(
        "UPDATE " . $db->getTable('tbl_visitas') . " SET
          provincia = :provincia,
          compromisopac = :compromisopac,
          respuesta = :respuesta,
          estado = :estado,
          tbl_secretarias_id = :tbl_secretarias_id,
          tbl_municipio_id = :tbl_municipio_id,
          componente = :componente,
          tipo_ejecucion = :tipo_ejecucion,
          descripcion_hecho = :descripcion_hecho,
          date = :date,
          update_at = NOW()
          WHERE id = :id"
      );

      $updatedCount = 0;
      $userId = SessionData::getUserId();
      $batchSize = 50;
      $rowBatches = array_chunk($rowsToUpdate, $batchSize);

      foreach ($rowBatches as $batch) {
        try {
          $pdo->beginTransaction();

          foreach ($batch as $rowData) {
            $stmtCompromiso->execute([':id' => $rowData['id']]);
            $currentCommitment = $stmtCompromiso->fetch(PDO::FETCH_ASSOC);

            if (!$currentCommitment) {
              $pdo->rollBack();
              return ['success' => false, 'message' => "El compromiso con ID {$rowData['id']} ya no existe."];
            }

            $descripcionHecho = $rowData['descripcion_hecho'];
            $effectiveDate = $rowData['date'];
            $effectiveMunicipioId = $rowData['tbl_municipio_id'];
            $effectiveCompromisopac = $rowData['compromisopac'];

            $stmtUpdate->execute([
              ':provincia' => $rowData['provincia'],
              ':compromisopac' => $effectiveCompromisopac,
              ':respuesta' => $rowData['respuesta'],
              ':estado' => $rowData['estado'],
              ':tbl_secretarias_id' => $rowData['tbl_secretarias_id'],
              ':tbl_municipio_id' => $effectiveMunicipioId,
              ':componente' => $rowData['componente'],
              ':tipo_ejecucion' => $rowData['tipo_ejecucion'],
              ':descripcion_hecho' => $descripcionHecho,
              ':date' => $effectiveDate,
              ':id' => $rowData['id']
            ]);

            self::logCambioCompromiso(
              $rowData['id'],
              intval($userId),
              'compromiso_pactado',
              $currentCommitment['compromisopac'] ?? null,
              $rowData['compromisopac'],
              $pdo
            );

            self::logCambioCompromiso(
              $rowData['id'],
              intval($userId),
              'respuesta',
              $currentCommitment['respuesta'] ?? null,
              $rowData['respuesta'],
              $pdo
            );

            $updatedCount++;
          }

          $pdo->commit();
        } catch (\PDOException $e) {
          if ($pdo->inTransaction()) {
            $pdo->rollBack();
          }
          return ['success' => false, 'message' => "Error al procesar lote: " . $e->getMessage()];
        }
      }

      return [
        'success' => true,
        'message' => "Importación completada. Filas actualizadas: $updatedCount.",
        'updated' => $updatedCount
      ];
    } catch (\Throwable $e) {
      if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
      }
      return ['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()];
    } finally {
      if (isset($db)) {
        $db->closeConect();
      }
    }
  }

  public function getAllCompromise($data)
  {

    $draw = $data['draw'];
    $start = intval($data['start']);
    $length = intval($data['length']);
    $searchValue = $data['search']['value'] ?? '';
    $orderColumn = $data['order'][0]['column'];
    //$orderDir = $data['order'][0]['dir'] ?? 'desc';
    $orderDir =  'desc';

    try {

      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

      $columns = [
        'secretaria.secretaria',
        'visitas.compromisos',
        'visitas.compromisopac',
        'visitas.consecuencia',
        'visitas.respuesta',
        'visitas.estado',
        'ciudad.municipio',
        'visitas.provincia',
        'visitas.img',
        'visitas.DATE',
        'visitas.id',
        'visitas.id',
        'visitas.componente',
        'visitas.tipo_ejecucion',
      ];

      //$orderBy = $columns[$orderColumn] ?? 'visitas.created_at DESC';
      $orderBy = 'visitas.id';
      /* $orderColumnIndex = $data['order'][0]['column'] ?? null;
      $orderDir = strtolower($data['order'][0]['dir'] ?? 'desc');

      if ($orderColumnIndex !== null && isset($columns[$orderColumnIndex])) {
        $orderBy = $columns[$orderColumnIndex] . ' ' . ($orderDir === 'asc' ? 'ASC' : 'DESC');
      } else {
        $orderBy = 'visitas.id DESC'; // Fallback seguro
      } */


      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
             LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
             LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio";
      $totalRecords = $pdo->query($sqlTotal)->fetchColumn();

      $where = " TRIM(UPPER(REPLACE(visitas.tipo_registro, CHAR(160), ''))) = 'COMPROMISO' ";
      $params = [];

      if (!empty($searchValue)) {
        $where .= " AND (
            visitas.DATE LIKE :search OR
            ciudad.municipio LIKE :search OR
            visitas.provincia LIKE :search OR
            visitas.estado LIKE :search OR
            secretaria.secretaria LIKE :search OR
            visitas.componente LIKE :search OR
            visitas.tipo_ejecucion LIKE :search
        )";
        $params[':search'] = "%$searchValue%";
      }

      $sqlFiltered = $sqlTotal . " WHERE $where";
      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params);
      $filteredRecords = $stmtFiltered->fetchColumn();

      $sqlData = "SELECT
                visitas.DATE,
                ciudad.municipio,
                visitas.compromisos,
                visitas.provincia,
                visitas.respuesta,
                secretaria.secretaria,
                visitas.img,
                visitas.id,
                visitas.estado,
                visitas.consecuencia,
                visitas.compromisopac,
                visitas.componente,
                visitas.tipo_ejecucion
            FROM $tableCompromisos
            INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            WHERE $where
            ORDER BY $orderBy $orderDir
            LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

      if (!empty($searchValue)) {
        $stmtData->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
      }

      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
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
        } else {
          $imagen = '';
        }

        $data[] = [
          'date' => $row['DATE'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id' => $row['id'],
          'componente' => $row['componente'],
          'tipo_ejecucion' => $row['tipo_ejecucion']
        ];
      }


      return [
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      return
        ['state' => false, 'error' => $e->getMessage()];
    }
  }

  /**
   * Summary of getAllCompromisosEnEstadoEspera
   * Listado de compromisos en estado 'En Espera'
   * @param mixed $data
   * @return array{data: array, draw: int, recordsFiltered: int, recordsTotal: int|array{error: string, state: bool}}
   */
  public function getAllCompromisosEnEstadoEspera($data)
  {

    $draw = $data['draw'];
    $start = intval($data['start']);
    $length = intval($data['length']);
    $searchValue = $data['search']['value'] ?? '';
    $orderColumn = $data['order'][0]['column'];
    $orderDir =  'desc';

    try {

      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";
      // Nuevas tablas para obtener la última observación y el usuario aprobador
      $tableObservaciones = $db->getTable('tbl_visitas_x_observaciones') . " AS obs_detail";
      $tableUsuarios      = $db->getTable('tbl_usuarios') . " AS aprobador";


      $columns = [
        'secretaria.secretaria',
        'visitas.compromisos',
        'visitas.compromisopac',
        'visitas.consecuencia',
        'visitas.respuesta',
        'visitas.estado',
        'visitas.estado_autorizar',
        'ciudad.municipio',
        'visitas.provincia',
        'visitas.img',
        'visitas.DATE',
        'visitas.id',
        'visitas.id',
        'visitas.componente',
        'visitas.tipo_ejecucion',
        'aprobador.nombre', // Añadir el nombre del aprobador para ordenamiento si es necesario
      ];

      $orderBy = 'visitas.id'; // Puedes ajustar esto para ordenar por el nombre del aprobador si lo deseas

      $where = " visitas.tipo_registro = 'Compromiso' AND visitas.estado = 'En Espera' ";
      $params = [];

      if (!empty($searchValue)) {
        $where .= " AND (
            visitas.DATE LIKE :search OR
            ciudad.municipio LIKE :search OR
            visitas.provincia LIKE :search OR
            visitas.estado LIKE :search OR
            secretaria.secretaria LIKE :search OR
            visitas.componente LIKE :search OR
            visitas.tipo_ejecucion LIKE :search OR
            aprobador.nombre LIKE :search OR
            aprobador.apellido LIKE :search 
        )";
        $params[':search'] = "%$searchValue%";
      }

      // Subconsulta para encontrar la última observación de cada visita
      $latestObservationsSubquery = "(
        SELECT
            tbl_visita_id,
            MAX(id) AS last_obs_id
        FROM
            " . $db->getTable('tbl_visitas_x_observaciones') . "
        GROUP BY
            tbl_visita_id
      ) AS latest_obs";

      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
             INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
             INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
             LEFT JOIN $latestObservationsSubquery ON visitas.id = latest_obs.tbl_visita_id
             LEFT JOIN $tableObservaciones ON latest_obs.last_obs_id = obs_detail.id
             LEFT JOIN $tableUsuarios ON obs_detail.tbl_usuario_id = aprobador.id
             WHERE $where";
      $stmtTotal = $pdo->prepare($sqlTotal);
      $stmtTotal->execute($params);
      $totalRecords = $stmtTotal->fetchColumn();


      $sqlFiltered = $sqlTotal; // sqlFiltered ya incluye el WHERE y las joins
      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params);
      $filteredRecords = $stmtFiltered->fetchColumn();

      $sqlData = "SELECT
                visitas.DATE,
                ciudad.municipio,
                visitas.compromisos,
                visitas.provincia,
                visitas.respuesta,
                secretaria.secretaria,
                visitas.img,
                visitas.id,
                visitas.estado,
                visitas.estado_autorizar,
                visitas.consecuencia,
                visitas.compromisopac,
                visitas.componente,
                visitas.tipo_ejecucion,
                -- aprobador.nombre AS nombre_aprobador_observacion -- Nombre del usuario aprobador
                CONCAT(
                    TRIM(IFNULL(aprobador.nombre, '')), 
                    ' ', 
                    TRIM(IFNULL(aprobador.apellido, ''))
                ) AS nombre_aprobador_observacion
            FROM $tableCompromisos
            INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            LEFT JOIN $latestObservationsSubquery ON visitas.id = latest_obs.tbl_visita_id
            LEFT JOIN $tableObservaciones ON latest_obs.last_obs_id = obs_detail.id
            LEFT JOIN $tableUsuarios ON obs_detail.tbl_usuario_id = aprobador.id
            WHERE $where
            ORDER BY $orderBy $orderDir
            LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

      if (!empty($searchValue)) {
        $stmtData->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
      }

      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

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
          'date' => $row['DATE'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'estado_autorizar' => $row['estado_autorizar'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id' => $row['id'],
          'componente' => $row['componente'],
          'tipo_ejecucion' => $row['tipo_ejecucion'],
          'aprobador_observacion' => $row['nombre_aprobador_observacion']
        ];
      }


      return [
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      return
        ['state' => false, 'error' => $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }

  public function getAllCompromiseFiltrosSelect($data)
  {

    $draw = intval($data['draw'] ?? 1);
    $start = intval($data['start'] ?? 0);
    $length = intval($data['length'] ?? 10);
    $orderDir = 'desc';

    // Obtener los filtros
    $id = intval($data['id'] ?? 0);
    $secretaria = intval($data['secretaria'] ?? 0);
    $municipio = intval($data['municipio'] ?? 0);
    $componente = $data['componente'] ?? '';
    $provincia = $data['provincia'] ?? '';
    $estado = $data['estado'] ?? '';

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades = $db->getTable('tbl_ciudades') . " AS ciudad";

      $where = " WHERE 1=1 AND TRIM(UPPER(REPLACE(visitas.tipo_registro, CHAR(160), ''))) = 'COMPROMISO' ";
      $params = [];

      if ($id > 0) {
        $where .= " AND visitas.id = :id";
        $params[':id'] = $id;
      }
      if ($municipio > 0) {
        $where .= " AND ciudad.codigo_muncipio = :municipio";
        $params[':municipio'] = $municipio;
      }
      if ($secretaria > 0) {
        $where .= " AND secretaria.id = :secretaria";
        $params[':secretaria'] = $secretaria;
      }
      if (!empty($componente)) {
        $where .= " AND visitas.componente LIKE :componente";
        $params[':componente'] = "%" . $componente . "%";
      }
      if (!empty($provincia)) {
        $where .= " AND visitas.provincia = :provincia";
        $params[':provincia'] = $provincia;
      }
      if (!empty($estado)) {
        $where .= " AND visitas.estado = :estado";
        $params[':estado'] = $estado;
      }


      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
                       INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                       INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio";
      $totalRecords = $pdo->query($sqlTotal)->fetchColumn();


      $sqlFiltered = $sqlTotal . $where;

      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params);
      $filteredRecords = $stmtFiltered->fetchColumn();

      $sqlData = "SELECT
                       visitas.DATE,
                       ciudad.municipio,
                       visitas.compromisos,
                       visitas.provincia,
                       visitas.respuesta,
                       secretaria.secretaria,
                       visitas.img,
                       visitas.id,
                       visitas.estado,
                       visitas.consecuencia,
                       visitas.compromisopac,
                       visitas.componente,
                       visitas.tipo_ejecucion
                     FROM $tableCompromisos
                     INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                     INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                     $where
                     ORDER BY visitas.id $orderDir
                     LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

      foreach ($params as $key => $value) {
        if (is_int($value)) {
          $stmtData->bindValue($key, $value, PDO::PARAM_INT);
        } else {
          $stmtData->bindValue($key, $value, PDO::PARAM_STR);
        }
      }

      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
        $imagen = '';
        if (!empty($row['img'])) {
          if (!is_string($row['img'])) {
            $imgData = base64_encode($row['img']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            $imgSrc = ($row['img'] == '4fbe830e44ff09970c4e3f979bad58849a3klsrvh4.png')
              ? 'assets/img/santander.png'
              : 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
          }
          $imagen = '<img src="' . $imgSrc . '" width="100" height="auto" class="rounded-circle" />';
        }

        $data[] = [
          'date' => $row['DATE'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id' => $row['id'],
          'componente' => $row['componente'],
          'tipo_ejecucion' => $row['tipo_ejecucion']
        ];
      }

      return [
        "draw" => $draw,
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      return ['state' => false, 'error' => $e->getMessage()];
    }
  }
  public function getAllCompromiseFiltrosSelectEnEstadoEspera_OLD($data)
  {

    $draw = intval($data['draw'] ?? 1);
    $start = intval($data['start'] ?? 0);
    $length = intval($data['length'] ?? 10);
    $orderDir = 'desc';

    // Obtener los filtros
    $secretaria = intval($data['secretaria'] ?? 0);
    $municipio = intval($data['municipio'] ?? 0);
    $componente = $data['componente'] ?? '';

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades = $db->getTable('tbl_ciudades') . " AS ciudad";

      $where = " WHERE 1=1 AND visitas.tipo_registro = 'Compromiso' AND estado = 'En Espera' ";
      $params = [];

      if ($municipio > 0) {
        $where .= " AND ciudad.codigo_muncipio = :municipio";
        $params[':municipio'] = $municipio;
      }
      if ($secretaria > 0) {
        $where .= " AND secretaria.id = :secretaria";
        $params[':secretaria'] = $secretaria;
      }
      if (!empty($componente)) {
        $where .= " AND visitas.componente LIKE :componente";
        $params[':componente'] = "%" . $componente . "%";
      }


      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
                       INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                       INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio";
      $totalRecords = $pdo->query($sqlTotal)->fetchColumn();


      $sqlFiltered = $sqlTotal . $where;
      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params);
      $filteredRecords = $stmtFiltered->fetchColumn();

      $sqlData = "SELECT
                       visitas.DATE,
                       ciudad.municipio,
                       visitas.compromisos,
                       visitas.provincia,
                       visitas.respuesta,
                       secretaria.secretaria,
                       visitas.img,
                       visitas.id,
                       visitas.estado,
                       visitas.consecuencia,
                       visitas.compromisopac,
                       visitas.componente,
                       visitas.tipo_ejecucion
                     FROM $tableCompromisos
                     INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                     INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
                     $where
                     ORDER BY visitas.id $orderDir
                     LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

      foreach ($params as $key => $value) {
        if (is_int($value)) {
          $stmtData->bindValue($key, $value, PDO::PARAM_INT);
        } else {
          $stmtData->bindValue($key, $value, PDO::PARAM_STR);
        }
      }

      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
        $imagen = '';
        if (!empty($row['img'])) {
          if (!is_string($row['img'])) {
            $imgData = base64_encode($row['img']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            $imgSrc = ($row['img'] == '4fbe830e44ff09970c4e3f979bad58849a3klsrvh4.png')
              ? 'assets/img/santander.png'
              : 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
          }
          $imagen = '<img src="' . $imgSrc . '" width="100" height="auto" class="rounded-circle" />';
        }

        $data[] = [
          'date' => $row['DATE'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id' => $row['id'],
          'componente' => $row['componente'],
          'tipo_ejecucion' => $row['tipo_ejecucion']
        ];
      }

      return [
        "draw" => $draw,
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      print_r($e);
      return ['state' => false, 'error' => $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }

  public function getAllCompromiseFiltrosSelectEnEstadoEspera($data)
  {

    $draw = intval($data['draw'] ?? 1);
    $start = intval($data['start'] ?? 0);
    $length = intval($data['length'] ?? 10);
    $orderDir = $data['order'][0]['dir'] ?? 'desc'; // Por defecto descendente

    // Obtener los filtros
    $idFiltro = intval($data['id'] ?? 0);
    $secretaria = intval($data['secretaria'] ?? 0);
    $municipio = intval($data['municipio'] ?? 0);
    $componente = $data['componente'] ?? '';

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades = $db->getTable('tbl_ciudades') . " AS ciudad";
      // Nuevas tablas para obtener la última observación y el usuario aprobador
      $tableObservaciones = $db->getTable('tbl_visitas_x_observaciones') . " AS obs_detail";
      $tableUsuarios      = $db->getTable('tbl_usuarios') . " AS aprobador";

      // Subconsulta para encontrar la última observación de cada visita
      $latestObservationsSubquery = "(
            SELECT
                tbl_visita_id,
                MAX(id) AS last_obs_id
            FROM
                " . $db->getTable('tbl_visitas_x_observaciones') . "
            GROUP BY
                tbl_visita_id
          ) AS latest_obs";

      // Base de las uniones (JOINs) para todas las consultas
      $baseJoins = "
            INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            LEFT JOIN $latestObservationsSubquery ON visitas.id = latest_obs.tbl_visita_id
            LEFT JOIN $tableObservaciones ON latest_obs.last_obs_id = obs_detail.id
            LEFT JOIN $tableUsuarios ON obs_detail.tbl_usuario_id = aprobador.id
          ";

      // Cláusula WHERE base para los filtros iniciales (sin el search_value)
      $whereBase = " visitas.tipo_registro = 'Compromiso' AND visitas.estado = 'En Espera' ";
      $paramsBase = [];

      if ($idFiltro > 0) {
        $whereBase .= " AND visitas.id = :idFiltro";
        $paramsBase[':idFiltro'] = $idFiltro;
      }
      if ($municipio > 0) {
        $whereBase .= " AND ciudad.codigo_muncipio = :municipio";
        $paramsBase[':municipio'] = $municipio;
      }
      if ($secretaria > 0) {
        $whereBase .= " AND secretaria.id = :secretaria";
        $paramsBase[':secretaria'] = $secretaria;
      }
      if (!empty($componente)) {
        $whereBase .= " AND visitas.componente LIKE :componente";
        $paramsBase[':componente'] = "%" . $componente . "%";
      }

      // Consulta para el total de registros (con filtros, sin búsqueda por searchValue)
      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos $baseJoins WHERE $whereBase";
      $stmtTotal = $pdo->prepare($sqlTotal);
      foreach ($paramsBase as $key => $value) {
        $stmtTotal->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
      }
      $stmtTotal->execute();
      $totalRecords = $stmtTotal->fetchColumn();

      // Construir la cláusula WHERE y los parámetros incluyendo el searchValue
      $whereFiltered = $whereBase;
      $paramsFiltered = $paramsBase; // Copiar los parámetros base
      $searchValue = $data['search']['value'] ?? '';
      if (!empty($searchValue)) {
        $whereFiltered .= " AND (
                visitas.DATE LIKE :search OR
                ciudad.municipio LIKE :search OR
                visitas.provincia LIKE :search OR
                visitas.estado LIKE :search OR
                secretaria.secretaria LIKE :search OR
                visitas.componente LIKE :search OR
                visitas.tipo_ejecucion LIKE :search OR
                aprobador.nombre LIKE :search OR 
                aprobador.apellido LIKE :search 
            )";
        $paramsFiltered[':search'] = "%" . $searchValue . "%";
      }

      // Consulta para el total de registros filtrados (con filtros Y búsqueda por searchValue)
      $sqlFiltered = "SELECT COUNT(*) FROM $tableCompromisos $baseJoins WHERE $whereFiltered";
      $stmtFiltered = $pdo->prepare($sqlFiltered);
      foreach ($paramsFiltered as $key => $value) {
        $stmtFiltered->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
      }
      $stmtFiltered->execute();
      $filteredRecords = $stmtFiltered->fetchColumn();

      // Consulta para obtener los datos con todas las columnas
      $sqlData = "SELECT
                       visitas.DATE,
                       ciudad.municipio,
                       visitas.compromisos,
                       visitas.provincia,
                       visitas.respuesta,
                       secretaria.secretaria,
                       visitas.img,
                       visitas.id,
                       visitas.estado AS estado_autorizar,
                       visitas.consecuencia,
                       visitas.compromisopac,
                       visitas.componente,
                       visitas.tipo_ejecucion,
                      aprobador.nombre AS aprobador_nombre,
                      aprobador.apellido AS aprobador_apellido


                     FROM $tableCompromisos
                     $baseJoins
                     WHERE $whereFiltered
                     ORDER BY visitas.id $orderDir
                     LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

      foreach ($paramsFiltered as $key => $value) {
        $stmtData->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
      }

      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
        $imagen = '';
        if (!empty($row['img'])) {
          if (!is_string($row['img'])) {
            $imgData = base64_encode($row['img']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            $imgSrc = ($row['img'] == '4fbe830e44ff09970c4e3f979bad58849a3klsrvh4.png')
              ? 'assets/img/santander.png'
              : 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
          }
          $imagen = '<img src="' . $imgSrc . '" width="100" height="auto" class="rounded-circle" />';
        }

        $data[] = [
          'date' => $row['DATE'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado_autorizar' => $row['estado_autorizar'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id' => $row['id'],
          'componente' => $row['componente'],
          'tipo_ejecucion' => $row['tipo_ejecucion'],
          // 'aprobador_observacion' => $row['nombre_aprobador_observacion'] // Nuevo atributo
          'aprobador_observacion' => trim($row['aprobador_nombre'] . ' ' . $row['aprobador_apellido'])
        ];
      }

      return [
        "draw" => $draw,
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      error_log("Error en getAllCompromiseFiltrosSelectEnEstadoEspera: " . $e->getMessage()); // Registrar el error
      return ['state' => false, 'error' => $e->getMessage()];
    } finally {
      $db->closeConect();
    }
  }

  public function getAllVisitas($data)
  {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $userType = SessionData::getUserType();
    $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
    $secretariaUsuarioLogueado = SessionData::getSecretaria();
    
    $isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    $isSecretario = (
      $userType === Util::Secretaria_Despacho_Gobernacion() || 
      $userType === Util::Secretario_Despacho()
    );

    $draw = $data['draw'];
    $start = intval($data['start']);
    $length = intval($data['length']);
    $searchValue = $data['search']['value'] ?? '';
    $orderDir = 'desc'; 

    try {

      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

      $orderBy = 'visitas.id'; 


      $where = " visitas.tipo_registro = 'Visita' ";
      $params = []; 

      if ($isAlcaldeOAuxiliar) {
        $where .= " AND visitas.tbl_municipio_id = :municipioId";
        $params[':municipioId'] = $municipioUsuarioLogueado;

      } else if ($isSecretario) { 

        $where .= " AND visitas.tbl_secretarias_id = :secretariaId";
        $params[':secretariaId'] = $secretariaUsuarioLogueado;
      }



      if (!empty($searchValue)) {
        $where .= " AND (
            visitas.DATE LIKE :search OR
            ciudad.municipio LIKE :search OR
            visitas.provincia LIKE :search OR
            visitas.compromisos LIKE :search OR
            visitas.tipo_visita LIKE :search OR
            visitas.consecuencia LIKE :search OR
            visitas.descripcion_hecho LIKE :search
        )";
        $params[':search'] = "%$searchValue%";
      }

      
      $whereTotalBase = " visitas.tipo_registro = 'Visita' ";
      $paramsTotal = [];
      
      if ($isAlcaldeOAuxiliar) {
        $whereTotalBase .= " AND visitas.tbl_municipio_id = :municipioIdTotal";
        $paramsTotal[':municipioIdTotal'] = $municipioUsuarioLogueado;
      } else if ($isSecretario) { 
        $whereTotalBase .= " AND visitas.tbl_secretarias_id = :secretariaIdTotal";
        $paramsTotal[':secretariaIdTotal'] = $secretariaUsuarioLogueado;
      }
      
      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
            INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            WHERE $whereTotalBase"; 

      $stmtTotal = $pdo->prepare($sqlTotal);
      $stmtTotal->execute($paramsTotal);
      $totalRecords = $stmtTotal->fetchColumn();




      $sqlFiltered = "SELECT COUNT(*) FROM $tableCompromisos
            INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            WHERE $where";

      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params); 
      $filteredRecords = $stmtFiltered->fetchColumn();


      $sqlData = "SELECT
                visitas.DATE,
                ciudad.municipio,
                visitas.tipo_visita,
                visitas.provincia,
                visitas.img,
                visitas.id,
                visitas.consecuencia,
                visitas.descripcion_hecho,
                visitas.compromisos
            FROM $tableCompromisos
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
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
          'compromisos'    => $row['compromisos'],
          'tipo_visita'    => $row['tipo_visita'],
          'descripcion_hecho' => $row['descripcion_hecho'],
          'consecuencia'   => $row['consecuencia'],
          'provincia'      => $row['provincia'],
          'municipio'      => $row['municipio'],
          'foto'           => $imagen,
          'date'           => $row['DATE'],
          'id'             => $row['id'],
          'id_reporte'     => $row['id'],
          'secretaria'     => $row['tipo_visita'],
        ];
      }
      

      return [
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      return
        ['state' => false, 'error' => $e->getMessage()];
    } finally {
        $db->closeConect(); 
    }
  }

  public static function getCompromisoId($data)
  {

    try {

      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

      $tableUsuariosName = $db->getTable('tbl_usuarios');
      $id = $data;

      $params = [];

      $sqlData = "SELECT
                visitas.created_at,
                ciudad.municipio,
                visitas.compromisos,
                visitas.provincia,
                visitas.respuesta,
                secretaria.secretaria,
                visitas.img,
                visitas.id,
                visitas.estado,
                visitas.estado_autorizar,
                visitas.consecuencia,
                visitas.compromisopac,
                visitas.date,
                visitas.tbl_municipio_id,
                visitas.tbl_secretarias_id,
                visitas.img2,
                visitas.pdf,
                visitas.pdf2,
                visitas.pdf3,
                visitas.pdf4,
                visitas.url,
                visitas.componente,
                usuario.nombre AS nombre_usuario_creador,
                usuario.apellido AS apellido_usuario_creador,
                usuario.nickname AS nickname_usuario_creador,
                traslado.nombre AS nombre_usuario_traslado,
                traslado.apellido AS apellido_usuario_traslado
            FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            LEFT JOIN $tableUsuariosName AS usuario ON visitas.tbl_usuario_id = usuario.id
            LEFT JOIN $tableUsuariosName AS traslado ON visitas.trasladado_por_usuario_id = traslado.id
            WHERE visitas.tipo_registro = 'Compromiso' AND visitas.id = :id";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':id', $data, PDO::PARAM_INT);
      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
        if (!empty($row['img'])) {
          if (!is_string($row['img'])) {
            $imgData = base64_encode($row['img']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            $imagen = 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
          }
        } else {
          $imagen = '';
        }

        if (!empty($row['img2'])) {
          if (!is_string($row['img2'])) {
            $imgData = base64_encode($row['img2']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            $imagen2 = 'assets/img/admin/' . htmlspecialchars($row['img2'], ENT_QUOTES, 'UTF-8');
          }
        } else {
          $imagen2 = '';
        }

        $pdf = '';
        if (!empty($row['pdf'])) {
          if (!is_string($row['pdf'])) {
            $pdfData = base64_encode($row['pdf']);
            $pdf = '<embed src="data:application/pdf;base64,' . $pdfData . '" type="application/pdf" width="100%" height="500px" />';
          } else {
            $pdfSrc = 'assets/img/admin/' . htmlspecialchars($row['pdf'], ENT_QUOTES, 'UTF-8');
            $pdf = 'assets/img/admin/' . htmlspecialchars($row['pdf'], ENT_QUOTES, 'UTF-8');
          }
        }

        $pdf2 = '';
        if (!empty($row['pdf2'])) {
          $pdf2 = 'assets/img/admin/' . htmlspecialchars($row['pdf2'], ENT_QUOTES, 'UTF-8');
        }

        $pdf3 = '';
        if (!empty($row['pdf3'])) {
          $pdf3 = 'assets/img/admin/' . htmlspecialchars($row['pdf3'], ENT_QUOTES, 'UTF-8');
        }

        $pdf4 = '';
        if (!empty($row['pdf4'])) {
          $pdf4 = 'assets/img/admin/' . htmlspecialchars($row['pdf4'], ENT_QUOTES, 'UTF-8');
        }

        $data[] = [
          'created_at' => $row['created_at'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'estado_autorizar' => $row['estado_autorizar'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'img' => $imagen,
          'imagen2' => $imagen2,
          'pdf' => $pdf,
          'pdf2' => $pdf2,
          'pdf3' => $pdf3,
          'pdf4' => $pdf4,
          'id' => $row['id'],
          'date' => $row['date'],
          'tbl_municipio_id' => $row['tbl_municipio_id'],
          'tbl_secretarias_id' => $row['tbl_secretarias_id'],
          'url' => $row['url'],
          'componente' => $row['componente'],
          'nombre_usuario_creador' => $row['nombre_usuario_creador'],
          'apellido_usuario_creador' => $row['apellido_usuario_creador'],
          'nickname_usuario_creador' => $row['nickname_usuario_creador'] ?? '',
          'nombre_usuario_traslado' => $row['nombre_usuario_traslado'],
          'apellido_usuario_traslado' => $row['apellido_usuario_traslado']
        ];
      }

      $sqlDataObs = "SELECT o.*, u.nombre AS obs_usuario_nombre, u.apellido AS obs_usuario_apellido, u.nickname AS obs_usuario_nickname
                     FROM " . $db->getTable('tbl_visitas_x_observaciones') . " AS o
                     LEFT JOIN " . $db->getTable('tbl_usuarios') . " AS u ON o.tbl_usuario_id = u.id
                     WHERE o.tbl_visita_id = :id
                     ORDER BY o.id DESC";
      $stmtDataObs = $pdo->prepare($sqlDataObs);
      $stmtDataObs->bindValue(':id', $id, PDO::PARAM_INT);
      $stmtDataObs->execute();
      $resultObs = $stmtDataObs->fetchAll(PDO::FETCH_ASSOC);

      // Trazabilidad: historial de cambios en campos clave
      $logRows = [];
      $qLog = "SELECT l.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.nickname AS usuario_nickname
               FROM " . $db->getTable('tbl_visitas_log') . " AS l
               LEFT JOIN " . $db->getTable('tbl_usuarios') . " AS u ON l.tbl_usuario_id = u.id
               WHERE l.tbl_visitas_id = :id
               ORDER BY l.created_at DESC";
      $stmtLog = $pdo->prepare($qLog);
      $stmtLog->bindValue(':id', $id, PDO::PARAM_INT);
      $stmtLog->execute();
      $logRows = $stmtLog->fetchAll(PDO::FETCH_ASSOC);
      foreach ($logRows as &$logRow) {
        if (isset($logRow['created_at'])) {
          $logRow['created_at_raw'] = $logRow['created_at'];
          $logRow['created_at'] = Util::formatFechaVisitasLogBogota($logRow['created_at']);
        }
      }
      unset($logRow);

      return [
        'state' => true,
        "data" => $data,
        "observaciones" => $resultObs,
        "historial" => $logRows,
      ];
    } catch (PDOException $e) {
      return
        ['state' => false, 'error' => $e->getMessage()];
    }
  }
  public static function getVisitaId($data)
  {

    try {

      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

      $where = " visitas.tipo_registro = 'Visita' AND visitas.id = :id";
      $params = [];

      $sqlData = "SELECT
                visitas.created_at,
                ciudad.municipio,
                visitas.compromisos,
                visitas.provincia,
                visitas.img,
                visitas.tipo_visita,
                visitas.id,
                visitas.date,
                visitas.tbl_municipio_id
            FROM $tableCompromisos
            INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            WHERE $where";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':id', $data, PDO::PARAM_INT);
      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
        if (!empty($row['img'])) {
          if (!is_string($row['img'])) {
            $imgData = base64_encode($row['img']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            $imagen = 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
          }
        } else {
          $imagen = '';
        }

        $data[] = [
          'created_at' => $row['created_at'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'foto' => $imagen,
          'tipo_visita' => $row['tipo_visita'],
          'id' => $row['id'],
          'date' => $row['date'],
          'tbl_municipio_id' => $row['tbl_municipio_id']
        ];
      }


      return [
        'state' => true,
        "data" => $data
      ];
    } catch (PDOException $e) {
      return
        ['state' => false, 'error' => $e->getMessage()];
    }
  }

  public function indicadores()
  {
    if (session_status() == PHP_SESSION_NONE) session_start();

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $userType = SessionData::getUserType();
      $secretariaSession = SessionData::getSecretaria();

      $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
      $isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType === Util::Auxiliar_secret_gob() || $userType === Util::Secretaria_Despacho_Gobernacion());

      $where = "a.tipo_registro = 'Compromiso'";
      $params = [];

      if ($isSecretario && $secretariaSession && $secretariaSession != '000') {
        $where .= " AND a.tbl_secretarias_id = :secretariaId";
        $params[':secretariaId'] = $secretariaSession;
      }

      $q = "SELECT
              a.estado,
              b.secretaria,
              COUNT(*) AS total
            FROM
              " . $db->getTable('tbl_visitas') . " a
              INNER JOIN " . $db->getTable('tbl_secretarias') . " b ON a.tbl_secretarias_id = b.id
            WHERE
              $where
            GROUP BY
              a.estado,
              b.secretaria;";

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
        'message' => $th->getMessage()
      ];
    }
  }
  public function indicadoresTipoVisita()
  {
    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $q = "SELECT
              a.tipo_visita,
              COUNT(*) AS total 
            FROM
              " . $db->getTable('tbl_visitas') . " a
              INNER JOIN " . $db->getTable('tbl_secretarias') . " b ON a.tbl_secretarias_id = b.id 
            WHERE
              a.tipo_registro = 'visita' 
            GROUP BY
              a.tipo_visita";

      $stmt = $pdo->prepare($q);
      $stmt->execute();

      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $db->closeConect();

      return [
        'state' => true,
        'data' => $result
      ];
    } catch (PDOException $th) {
      return [
        'state' => false,
        'message' => $th->getMessage()
      ];
    }
  }

  public static function actualizarCompromiso($data, $files)
  {
    if (empty(trim($data['provincia'] ?? '')) || $data['provincia'] === 'Seleccione') {
      return ['state' => false, 'message' => 'El campo Provincia es obligatorio.'];
    }

    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      session_start();
      $tbl_usuario_id =  $_SESSION['session_user']['id'];


      // Validar permisos para modificar campos Estado y Componente
      $userType = SessionData::getUserType();
      $isAdminOrSuper = (
          $userType === Util::SuperAdministrador() ||
          $userType === Util::Administrador()
      );


      // Verificar respuesta para el correo y obtener valores actuales para log
      $q = "SELECT requiere_respuesta, tbl_secretarias_id, tbl_municipio_id, created_at, estado,
                   compromisopac, respuesta, compromisos,
                   pdf, pdf2, pdf3, pdf4
            FROM " . $db->getTable('tbl_visitas') . " WHERE id = :id";
      $res = $pdo->prepare($q);
      $res->execute([':id' => $data['id']]);
      $respuesta = $res->fetch(PDO::FETCH_OBJ);
      $require_respuesta = $respuesta->requiere_respuesta;
      $estadoActual = $respuesta->estado;

      // Guardar valores anteriores para la trazabilidad
      $old_compromiso_pactado = $respuesta->compromisopac;
      $old_respuesta          = $respuesta->respuesta;
      $old_acciones_tomadas   = $respuesta->compromisos;
      $old_pdf                = $respuesta->pdf;
      $old_pdf2               = $respuesta->pdf2;
      $old_pdf3               = $respuesta->pdf3;
      $old_pdf4               = $respuesta->pdf4;

      $tbl_secretarias_id = $respuesta->tbl_secretarias_id;
      $tbl_municipio_id = $respuesta->tbl_municipio_id;

      $q = "SELECT * FROM " . $db->getTable('tbl_secretarias') . " WHERE id = :id";
      $stmt = $pdo->prepare($q);
      $stmt->bindValue(':id', $tbl_secretarias_id, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_OBJ);

      $q = "SELECT codigo_muncipio, nombre_mapa, municipio, correo FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio = :codigo_muncipio";
      $stmtData = $pdo->prepare($q);
      $stmtData->bindValue(':codigo_muncipio', $tbl_municipio_id, PDO::PARAM_INT);
      $stmtData->execute();
      $resultCiudad = $stmtData->fetch(PDO::FETCH_OBJ);

      // Carpeta de destino
      $uploadDir = '../../assets/img/admin/';
      if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      // Subir imagen
      $imgPath = '';
      if (isset($files['newImage']) && $files['newImage']['error'] === UPLOAD_ERR_OK) {
        $imgName = uniqid() . '_' . basename($files['newImage']['name']);
        $imgTarget = $uploadDir . $imgName;
        if (move_uploaded_file($files['newImage']['tmp_name'], $imgTarget)) {
          $imgPath = $imgName;
        }
      }

      // Subir PDF
      $pdfPath = '';
      if (isset($files['newPdf']) && $files['newPdf']['error'] === UPLOAD_ERR_OK) {
        $pdfName = uniqid() . '_' . basename($files['newPdf']['name']);
        $pdfTarget = $uploadDir . $pdfName;
        if (move_uploaded_file($files['newPdf']['tmp_name'], $pdfTarget)) {
          $pdfPath = $pdfName;
        }
      }

      $pdf2Path = '';
      if (isset($files['newPdf2']) && $files['newPdf2']['error'] === UPLOAD_ERR_OK) {
        $pdf2Name = uniqid() . '_' . basename($files['newPdf2']['name']);
        $pdf2Target = $uploadDir . $pdf2Name;
        if (move_uploaded_file($files['newPdf2']['tmp_name'], $pdf2Target)) {
          $pdf2Path = $pdf2Name;
        }
      }

      $pdf3Path = '';
      if (isset($files['newPdf3']) && $files['newPdf3']['error'] === UPLOAD_ERR_OK) {
        $pdf3Name = uniqid() . '_' . basename($files['newPdf3']['name']);
        $pdf3Target = $uploadDir . $pdf3Name;
        if (move_uploaded_file($files['newPdf3']['tmp_name'], $pdf3Target)) {
          $pdf3Path = $pdf3Name;
        }
      }

      $pdf4Path = '';
      if (isset($files['newPdf4']) && $files['newPdf4']['error'] === UPLOAD_ERR_OK) {
        $pdf4Name = uniqid() . '_' . basename($files['newPdf4']['name']);
        $pdf4Target = $uploadDir . $pdf4Name;
        if (move_uploaded_file($files['newPdf4']['tmp_name'], $pdf4Target)) {
          $pdf4Path = $pdf4Name;
        }
      }

      // Si se envía un estado desde el formulario, usarlo; de lo contrario, usar 'En Espera'
      $estado = !empty($data['estado']) ? $data['estado'] : 'En Espera';
      if (!empty($data['acciones_tomadas']) && !empty($data['respuesta'])) {
        // $estado = 'Cumplido';
      }

      // Construir SQL dinámicamente - solo incluir campos restringidos si tiene permisos
      $sql = "UPDATE " . $db->getTable('tbl_visitas') . "
            SET
              compromisos = :acciones_tomadas,
              respuesta = :respuesta,";

      // Solo actualizar estado si tiene permisos
      if ($isAdminOrSuper) {
        $sql .= "
              estado = :estado,";
      }

      $sql .= "
              estado_autorizar = :estado_autorizar,
              compromisopac = :compromisos,
              tbl_secretarias_id = :secretaria,
              tbl_municipio_id = :municipio,
              provincia = :provincia,";

      // Solo actualizar componente si tiene permisos
      if ($isAdminOrSuper) {
        $sql .= "
              componente = :componente,";
      }

      $sql .= "
              update_at = NOW()";

      if (!empty($imgPath)) {
        $sql .= ", img2 = :img2";
      }

      if (!empty($pdfPath)) {
        $sql .= ", pdf = :pdf";
      }

      if (!empty($pdf2Path)) {
        $sql .= ", pdf2 = :pdf2";
      }

      if (!empty($pdf3Path)) {
        $sql .= ", pdf3 = :pdf3";
      }

      if (!empty($pdf4Path)) {
        $sql .= ", pdf4 = :pdf4";
      }

      if (!empty($data['url'])) {
        $sql .= ", url = :url";
      }

      $sql .= " WHERE id = :id";

      $stmt = $pdo->prepare($sql);

      // Bind de parámetros obligatorios
      $stmt->bindValue(':acciones_tomadas', $data['acciones_tomadas']);
      $stmt->bindValue(':respuesta', $data['respuesta']);
      $stmt->bindValue(':estado_autorizar', $estadoActual);

      // Solo hacer bind de campos restringidos si tiene permisos
      if ($isAdminOrSuper) {
        $stmt->bindValue(':estado', $estado);
      }

      $stmt->bindValue(':compromisos', $data['compromisos']);
      $stmt->bindValue(':secretaria', $data['secretaria']);
      $stmt->bindValue(':municipio', $data['municipio']);
      $stmt->bindValue(':provincia', $data['provincia']);

      // Solo hacer bind de componente si tiene permisos
      if ($isAdminOrSuper) {
        $stmt->bindValue(':componente', !empty($data['componente']) ? mb_strtoupper($data['componente']) : '');
      }

      $stmt->bindValue(':id', $data['id']);

      // Bind de campos opcionales
      if (!empty($imgPath)) {
        $stmt->bindValue(':img2', $imgPath);
      }

      if (!empty($pdfPath)) {
        $stmt->bindValue(':pdf', $pdfPath);
      }

      if (!empty($pdf2Path)) {
        $stmt->bindValue(':pdf2', $pdf2Path);
      }

      if (!empty($pdf3Path)) {
        $stmt->bindValue(':pdf3', $pdf3Path);
      }

      if (!empty($pdf4Path)) {
        $stmt->bindValue(':pdf4', $pdf4Path);
      }

      if (!empty($data['url'])) {
        $stmt->bindValue(':url', $data['url']);
      }

      $stmt->execute();

      //Ingreso de las observaciones
      if(!empty($data['observaciones'])){
        $qObservaciones = "INSERT INTO " . $db->getTable('tbl_visitas_x_observaciones') . " (dtcreate, tbl_visita_id, observaciones, tbl_usuario_id) VALUES
        ( " . Util::date_now_server() . ", :tbl_visita_id, :observaciones, :tbl_usuario_id)";
        $resultObservaciones = $pdo->prepare($qObservaciones);
        $arrparam = array(
          ':tbl_visita_id' => $data['id'],
          ':observaciones' => $data['observaciones'],
          ':tbl_usuario_id' => $tbl_usuario_id
        );
        $resultObservaciones->execute($arrparam);
      }



      // Informaciòn para envio de correo con la informacion de la actualizacion
      $correo = $resultCiudad->correo;
      $secretaria = $result->secretaria;
      $ciudad = $resultCiudad->nombre_mapa;
      $fecha = $respuesta->created_at;

      if ($stmt->rowCount()) {
        // Trazabilidad: registrar cambios en campos clave
        self::logCambioCompromiso(
          (int)$data['id'],
          $tbl_usuario_id,
          'compromiso_pactado',
          $old_compromiso_pactado,
          $data['compromisos'] ?? ''
        );
        self::logCambioCompromiso(
          (int)$data['id'],
          $tbl_usuario_id,
          'respuesta',
          $old_respuesta,
          $data['respuesta'] ?? ''
        );
        self::logCambioCompromiso(
          (int)$data['id'],
          $tbl_usuario_id,
          'acciones_tomadas',
          $old_acciones_tomadas,
          $data['acciones_tomadas'] ?? ''
        );
        self::logCambioCompromiso(
          (int)$data['id'],
          $tbl_usuario_id,
          'pdf',
          $old_pdf,
          $pdfPath ?: ($old_pdf ?? '')
        );
        self::logCambioCompromiso(
          (int)$data['id'],
          $tbl_usuario_id,
          'pdf2',
          $old_pdf2,
          $pdf2Path ?: ($old_pdf2 ?? '')
        );
        self::logCambioCompromiso(
          (int)$data['id'],
          $tbl_usuario_id,
          'pdf3',
          $old_pdf3,
          $pdf3Path ?: ($old_pdf3 ?? '')
        );
        self::logCambioCompromiso(
          (int)$data['id'],
          $tbl_usuario_id,
          'pdf4',
          $old_pdf4,
          $pdf4Path ?: ($old_pdf4 ?? '')
        );

        if (!empty($data['acciones_tomadas']) && !empty($data['respuesta']) && $require_respuesta === 'Si') {
          self::correoAlcadia($correo, $secretaria, $ciudad, $fecha);
        }

        return [
          'state' => true,
          'message' => 'Datos actualizados correctamente'
        ];
      } else {
        return [
          'state' => false,
          'message' => 'Ha ocurrido un error actualizando los datos.'
        ];
      }
    } catch (PDOException $th) {
      return [
        'state' => false,
        'message' => $th->getMessage()
      ];
    }
  }


  public function getAllCompromiseForId($data)
  {

    $draw = $data['draw'];
    $start = intval($data['start']);
    $length = intval($data['length']);
    $searchValue = $data['search']['value'] ?? '';
    $orderColumn = $data['order'][0]['column'];
    $orderDir = $data['order'][0]['dir'] ?? 'asc';
    try {

      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

      $columns = [
        'visitas.DATE',
        'ciudad.municipio',
        'visitas.compromisos',
        'visitas.provincia',
        'visitas.respuesta',
        'secretaria.secretaria',
        'visitas.id',
        'visitas.estado',
        'visitas.consecuencia',
        'visitas.compromisopac',
      ];

      $orderBy = $columns[$orderColumn] ?? 'visitas.id DESC';

      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
                       LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
                       LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio";
      $totalRecords = $pdo->query($sqlTotal)->fetchColumn();

      $where = " visitas.tipo_registro = 'Compromiso' ";
      $params = [];

      $where = "visitas.tipo_registro = 'Visita'";

      if (!empty($searchValue)) {
        $where .= " AND (
                          visitas.DATE LIKE :search OR
                          ciudad.municipio LIKE :search OR
                          visitas.provincia LIKE :search OR
                          secretaria.tipo_visita LIKE :search OR
                          secretaria.consecuencia LIKE :search 
                      )";
        $params[':search'] = "%$searchValue%";
      }

      $sqlFiltered = $sqlTotal . " WHERE $where";
      $stmtFiltered = $pdo->prepare($sqlFiltered);
      $stmtFiltered->execute($params);
      $filteredRecords = $stmtFiltered->fetchColumn();

      $sqlData = "SELECT
                visitas.DATE,
                ciudad.municipio,
                visitas.compromisos,
                visitas.provincia,
                visitas.respuesta,
                secretaria.secretaria,
                visitas.img,
                visitas.id,
                visitas.estado,
                visitas.consecuencia,
                visitas.compromisopac
            FROM $tableCompromisos
            INNER JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            INNER JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            WHERE $where
            ORDER BY $orderBy $orderDir
            LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);

      if (!empty($searchValue)) {
        $stmtData->bindValue(':search', "%$searchValue%", PDO::PARAM_STR);
      }

      $stmtData->execute();
      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
        if (!empty($row['img'])) {
          if (!is_string($row['img'])) {
            $imgData = base64_encode($row['img']);
            $imgSrc = 'data:image/jpeg;base64,' . $imgData;
          } else {
            $imgSrc = 'assets/img/admin/' . htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8');
          }
          $imagen = '<img src="' . $imgSrc . '" width="40" height="40" class="rounded-circle" />';
        } else {
          $imagen = '';
        }

        $data[] = [
          'date' => $row['DATE'],
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id' => $row['id']
        ];
      }


      return [
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
      ];
    } catch (PDOException $e) {
      return
        ['state' => false, 'error' => $e->getMessage()];
    }
  }

  public static function save($rqst, $files)
  {
    session_start();

    $date = isset($rqst['date']) ? ($rqst['date']) : '';
    $provincia = isset($rqst['provincia']) ? ($rqst['provincia']) : '';
    $consecuencia =  '';
    $tipo_registro = isset($rqst['tipo_registro']) ? ($rqst['tipo_registro']) : '';
    $compromisos = isset($rqst['compromisos']) ? ($rqst['compromisos']) : '';
    $compromisopac = isset($rqst['compromisopac']) ? ($rqst['compromisopac']) : '';
    $respuesta = '';
    $tbl_secretarias_id = isset($rqst['tbl_secretarias_id']) ? ($rqst['tbl_secretarias_id']) : '';
    $tbl_departamento_id = isset($rqst['tbl_departamento_id']) ? intval($rqst['tbl_departamento_id']) : 0;
    $tbl_municipio_id = isset($rqst['tbl_municipio_id']) ? ($rqst['tbl_municipio_id']) : '';
    $descripcion_hecho = '';
    $tipo_visita = !empty($rqst['tipo_visita']) ? ($rqst['tipo_visita']) : 'N/A';
    $tipo_ejecucion = !empty($rqst['tipo_ejecucion']) ? ($rqst['tipo_ejecucion']) : 'N/A';
    $componente = !empty($rqst['componente']) ? mb_strtoupper($rqst['componente']) : 'N/A';
    $requiere_respuesta = 'No';
    $tbl_usuario_id =  intval($_SESSION['session_user']['id']);

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

    // Si es alcalde o auxiliar de alcalde
    $mensaje = "Debe seleccionar el municipio correspondiente al cual pertenece.";
    if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario || Util::Secretario_Despacho() == $tipoUsuario) {
      if ($tbl_municipio_id !== $codigoMunicipio) {
        return Util::error_general($mensaje);
      }
    }

    if($provincia === 'N/A' || empty($provincia) || $provincia == 'null'){
      return Util::error_general("Debe seleccionar la provincia correspondiente.");
    }

    $db = new DbConection();
    $pdo = $db->openConect();


    if ($date != "" || $provincia != "" || $tbl_departamento_id > 0 || $compromisos != "") {
      $q = "INSERT INTO " . $db->getTable('tbl_visitas') . " (created_at, date, provincia, consecuencia, tipo_registro, estado, compromisos, compromisopac, respuesta, tbl_secretarias_id, tbl_departamento_id, tbl_municipio_id, descripcion_hecho, img, tbl_usuario_id, tipo_visita, requiere_respuesta, componente, tipo_ejecucion) VALUES
                                              ( " . Util::date_now_server() . ", :date, :provincia, :consecuencia, :tipo_registro, :estado, :compromisos, :compromisopac, :respuesta, :tbl_secretarias_id, :tbl_departamento_id, :tbl_municipio_id, :descripcion_hecho, :img, :tbl_usuario_id, :tipo_visita, :requiere_respuesta, :componente, :tipo_ejecucion)";
      $result = $pdo->prepare($q);
      $arrparam = array(
        ':date' => $date,
        ':provincia' => $provincia,
        ':consecuencia' => $consecuencia,
        ':tipo_registro' => $tipo_registro,
        ':estado' => 'Sin Cumplir',
        ':compromisos' => $compromisos,
        ':compromisopac' => $compromisopac,
        ':respuesta' => $respuesta,
        ':tbl_secretarias_id' => $tbl_secretarias_id,
        ':tbl_departamento_id' => $tbl_departamento_id,
        ':tbl_municipio_id' => $tbl_municipio_id,
        ':descripcion_hecho' => $descripcion_hecho,
        ':img' => $imgPath,
        ':tbl_usuario_id' => $tbl_usuario_id,
        ':tipo_visita' => $tipo_visita,
        ':requiere_respuesta' => $requiere_respuesta,
        ':componente' => $componente,
        ':tipo_ejecucion' => $tipo_ejecucion,
      );

      if ($result->execute($arrparam)) {
        // Obtener el ID del registro recién creado
        $lastInsertId = $pdo->lastInsertId();
        $arrjson = ['state' => true, 'message' => 'Registro ingresado correctamente', 'id' => $lastInsertId];

        // Trazabilidad: registrar valores iniciales al crear
        if ($tipo_registro === 'Compromiso') {
          self::logCambioCompromiso((int)$lastInsertId, $tbl_usuario_id, 'compromiso_pactado', null, $compromisopac ?: '');
          self::logCambioCompromiso((int)$lastInsertId, $tbl_usuario_id, 'respuesta',          null, $respuesta ?: '');
          self::logCambioCompromiso((int)$lastInsertId, $tbl_usuario_id, 'acciones_tomadas',   null, $compromisos ?: '');
          self::logCambioCompromiso((int)$lastInsertId, $tbl_usuario_id, 'pdf',               null, '');
          self::logCambioCompromiso((int)$lastInsertId, $tbl_usuario_id, 'pdf2',              null, '');
          self::logCambioCompromiso((int)$lastInsertId, $tbl_usuario_id, 'pdf3',              null, '');
          self::logCambioCompromiso((int)$lastInsertId, $tbl_usuario_id, 'pdf4',              null, '');
        }


        $q = "SELECT * FROM " . $db->getTable('tbl_secretarias') . " WHERE id = :id";
        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':id', $tbl_secretarias_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        $q = "SELECT codigo_muncipio, nombre_mapa, municipio FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_muncipio = :codigo_muncipio";
        $stmtData = $pdo->prepare($q);
        $stmtData->bindValue(':codigo_muncipio', $tbl_municipio_id, PDO::PARAM_INT);
        $stmtData->execute();
        $resultCiudad = $stmtData->fetch(PDO::FETCH_OBJ);

        $correo = $result->correo;
        $secretario = $result->secretario;
        $ciudad = $resultCiudad->nombre_mapa;
        if ($compromisopac) {
          self::correoSecretaria($correo, $secretario, $ciudad);
        }
      } else {
        $arrjson = [
          'state' => false,
          'message' => 'Ha ocurrido un error interno intetalo nuevamente si el problema persiste comunicate con el administrador'
        ];
      }
    } else {
      $arrjson = [
        'state' => false,
        'message' => 'Faltan datos por ingresar'
      ];
    }

    $db->closeConect();
    return $arrjson;
  }

  /**
   * Metodo para actualizar una visita cuadro control de visita
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
    $tipo_visita = !empty($rqst['tipo_visita']) ? trim($rqst['tipo_visita']) : 'N/A';
    $tbl_usuario_id = isset($_SESSION['session_user']['id']) ? intval($_SESSION['session_user']['id']) : 0;

    if ($tbl_usuario_id === 0) {
      return ['state' => false, 'message' => 'ID de usuario no encontrado en la sesión.'];
    }

    if ($id === 0) {
      return ['state' => false, 'message' => 'Debe seleccionar la visita a actualizar.'];
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

    if ($tbl_municipio_id === '') {
      return ['state' => false, 'message' => 'Debe seleccionar el municipio.'];
    }

    $tipoUsuario = SessionData::getUserType();
    $codigoMunicipio = SessionData::getCodigoMunicipio();
    $mensajeRestriccion = "Debe seleccionar el municipio correspondiente al cual pertenece.";

    if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario || Util::Secretario_Despacho() == $tipoUsuario) {
      if ($tbl_municipio_id !== $codigoMunicipio) {
        return Util::error_general($mensajeRestriccion);
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

    $db = new DbConection();
    $pdo = $db->openConect();

    $pdo->beginTransaction();

    try {
      $q = "UPDATE " . $db->getTable('tbl_visitas') . " 
        SET
          date = :date,
          tipo_visita = :tipo_visita,
          compromisos = :compromisos,
          tbl_municipio_id = :tbl_municipio_id,
          tbl_usuario_id = :tbl_usuario_id,
          update_at = NOW(),
          img = :img";
      $q .= " WHERE id = :id";

      $stmt = $pdo->prepare($q);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->bindValue(':date', $date);
      $stmt->bindValue(':compromisos', $compromisos);
      $stmt->bindValue(':tbl_municipio_id', $tbl_municipio_id);
      $stmt->bindValue(':tbl_usuario_id', $tbl_usuario_id, PDO::PARAM_INT);
      $stmt->bindValue(':tipo_visita', $tipo_visita);
      $stmt->bindValue(':img', $imgPath);

      if ($stmt->execute()) {
        $pdo->commit();
        $arrjson = ['state' => true, 'message' => 'Control de visita actualizado correctamente'];
      } else {
        throw new Exception('Error en actualizando el control de visita ' . $id);
      }
    } catch (Exception $e) {
      print_r($e);
      $pdo->rollBack();
      $arrjson = [
        'state' => false,
        'message' => 'Ha ocurrido un error interno al actualizar el control de visita ' . $id
      ];
    } finally {
      $db->closeConect();
    }
    return $arrjson;
  }

  public static function guardarObservacion($rqst)
  {

    session_start();

    $compromisoId = isset($rqst['compromisoId']) ? intval($rqst['compromisoId']) : 0;
    $tbl_municipio_id = isset($rqst['municipio']) ? ($rqst['municipio']) : 0;
    $tbl_secretarias_id = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;
    $observacion = isset($rqst['observacion']) ? ($rqst['observacion']) : '';
    $aprobacion = isset($rqst['aprobacion']) ? ($rqst['aprobacion']) : '';
    $estadoParaAprobar = isset($rqst['estadoParaAprobar']) ? ($rqst['estadoParaAprobar']) : '';
    $estadoModal = isset($rqst['estado']) ? ($rqst['estado']) : '';
    $tbl_usuario_id =  intval($_SESSION['session_user']['id']);


    $userType = SessionData::getUserType();
    $isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
    if (!$isAdmin) {
      return $arrjson = [
        'state' => false,
        'message' => 'No está autorizado para ejecutar este tipo de proceso en el sistema. Comunicate con el administrador'
      ];
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    if ($compromisoId > 0 && $observacion != "" && $tbl_municipio_id > 0 && $tbl_secretarias_id > 0) {
      $q = "INSERT INTO " . $db->getTable('tbl_visitas_x_observaciones') . " (dtcreate, tbl_visita_id, observaciones, tbl_usuario_id) VALUES
                                              ( " . Util::date_now_server() . ", :tbl_visita_id, :observaciones, :tbl_usuario_id)";
      $result = $pdo->prepare($q);
      $arrparam = array(
        ':tbl_visita_id' => $compromisoId,
        ':observaciones' => $observacion,
        ':tbl_usuario_id' => $tbl_usuario_id
      );

      if ($result->execute($arrparam)) {
        $arrjson = ['state' => true, 'message' => 'Observaciones ingresada correctamente'];


        $q = "SELECT * FROM " . $db->getTable('tbl_secretarias') . " WHERE id = :id";
        $stmt = $pdo->prepare($q);
        $stmt->bindValue(':id', $tbl_secretarias_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        $q = "SELECT codigo_muncipio, nombre_mapa, municipio FROM " . $db->getTable('tbl_ciudades') . "
        WHERE codigo_muncipio = :codigo_muncipio";
        $stmtData = $pdo->prepare($q);
        $stmtData->bindValue(':codigo_muncipio', $tbl_municipio_id, PDO::PARAM_INT);
        $stmtData->execute();
        $resultCiudad = $stmtData->fetch(PDO::FETCH_OBJ);

        $correo = $result->correo;
        $secretario = $result->secretario;
        $ciudad = $resultCiudad->nombre_mapa;


        if ($aprobacion == 'si') {
          // Si se envió un estado desde el modal, usarlo; de lo contrario, usar el estadoParaAprobar
          $estadoFinal = !empty($estadoModal) ? $estadoModal : $estadoParaAprobar;

          $sql = "UPDATE " . $db->getTable('tbl_visitas') . "
                    SET
                      estado = ?,
                      estado_autorizar = ?,
                      update_at = NOW()
                    WHERE id = ?";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([$estadoFinal, $estadoFinal, $compromisoId]);
        }

        if ($correo != "" && $secretario != "" && $ciudad != "" && $compromisoId > 0) {
          self::correoCompromisoActualizado($correo, $secretario, $ciudad, $compromisoId);
        }

        return [
          'state' => true,
          'message' => 'Datos actualizados correctamente'
        ];
      } else {
        $arrjson = [
          'state' => false,
          'message' => 'Ha ocurrido un error interno intetalo nuevamente si el problema persiste comunicate con el administrador'
        ];
      }
    } else {
      $arrjson = [
        'state' => false,
        'message' => 'Faltan datos por ingresar'
      ];
    }

    $db->closeConect();
    return $arrjson;
  }

  /* =========================================================
     LOG DE TRAZABILIDAD DE COMPROMISOS
     Registra automáticamente quién modifica campos clave:
     compromiso_pactado (compromisopac), respuesta, acciones_tomadas (compromisos),
     pdf, pdf2, pdf3, pdf4
  ========================================================== */
  private static function logCambioCompromiso(
    int $tbl_visitas_id,
    int $tbl_usuario_id,
    string $campo,
    ?string $valor_anterior,
    ?string $valor_nuevo,
    ?PDO $pdo = null
  ): void {
    if ($valor_anterior === $valor_nuevo) return;

    $db = new DbConection();
    $conn = $pdo ?? $db->openConect();

    $q = "INSERT INTO " . $db->getTable('tbl_visitas_log') . "
          (tbl_visitas_id, tbl_usuario_id, campo, valor_anterior, valor_nuevo, created_at)
          VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($q);
    $stmt->execute([$tbl_visitas_id, $tbl_usuario_id, $campo, $valor_anterior, $valor_nuevo]);
  }

  public static function getCompromisoHistorial(int $id): array
  {
    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $qLog = "SELECT l.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.nickname AS usuario_nickname
               FROM " . $db->getTable('tbl_visitas_log') . " AS l
               LEFT JOIN " . $db->getTable('tbl_usuarios') . " AS u ON l.tbl_usuario_id = u.id
               WHERE l.tbl_visitas_id = :id
               ORDER BY l.created_at DESC";
      $stmtLog = $pdo->prepare($qLog);
      $stmtLog->bindValue(':id', $id, PDO::PARAM_INT);
      $stmtLog->execute();
      $logRows = $stmtLog->fetchAll(PDO::FETCH_ASSOC);
      foreach ($logRows as &$logRow) {
        if (isset($logRow['created_at'])) {
          $logRow['created_at_raw'] = $logRow['created_at'];
          $logRow['created_at'] = Util::formatFechaVisitasLogBogota($logRow['created_at']);
        }
      }
      unset($logRow);

      return ['ok' => true, 'historial' => $logRows];
    } catch (Throwable $e) {
      return ['ok' => false, 'msg' => $e->getMessage()];
    }
  }

public function ejecutarTrasladoPorCompetencia($rqst)
{
    session_start();

    $compromisoId = intval($rqst['compromiso_original_id'] ?? 0);
    $secretariasDestino = isset($rqst['secretarias_destino']) ? (array)$rqst['secretarias_destino'] : [];
    $usuario_traslado_id = $_SESSION['session_user']['id'] ?? 0;

    if ($compromisoId === 0 || empty($secretariasDestino)) {
        return ['output' => ['valid' => false, 'error' => 'Datos insuficientes']];
    }

    $db = new DbConection();
    $pdo = $db->openConect();
    $pdo->beginTransaction();

    try {

        // --- 1. Traer registro completo original ---
        $sqlGet = "SELECT * FROM " . $db->getTable('tbl_visitas') . " WHERE id = :id";
        $stmt = $pdo->prepare($sqlGet);
        $stmt->execute([':id' => $compromisoId]);
        $original = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$original) {
            $pdo->rollBack();
            return ['output' => ['valid' => false, 'error' => 'Compromiso no encontrado']];
        }

        // --- 2. Primera secretaría → actualizar original ---
        $primera = array_shift($secretariasDestino);

        $pdo->prepare("
            UPDATE " . $db->getTable('tbl_visitas') . "
            SET 
                tbl_secretarias_id = :sec,
                trasladado_por_usuario_id = :user,
                update_at = NOW()
            WHERE id = :id
        ")->execute([
            ':sec' => $primera,
            ':user' => $usuario_traslado_id,
            ':id' => $compromisoId
        ]);

        // --- 3. Las demás secretarías → crear copias EXACTAS ---
        $registros = 0;

        foreach ($secretariasDestino as $sec) {

            // Copiar TODAS menos el ID
            $nuevo = $original;
            unset($nuevo['id']);

            // Campos que sí deben cambiar
            $nuevo['tbl_secretarias_id'] = $sec;
            $nuevo['tbl_usuario_id'] = $usuario_traslado_id;
            $nuevo['compromiso_origen_id'] = $compromisoId;
            $nuevo['trasladado_por_usuario_id'] = $usuario_traslado_id;
            $nuevo['created_at'] = date('Y-m-d H:i:s');

            // conservar estado original:
            $nuevo['estado'] = $original['estado'];
            $nuevo['estado_autorizar'] = $original['estado_autorizar'];

            // --- 4. Construir INSERT dinámico ---
            $cols = implode(",", array_keys($nuevo));
            $phs = ":" . implode(",:", array_keys($nuevo));

            $sqlInsert = "
                INSERT INTO " . $db->getTable('tbl_visitas') . "
                ($cols)
                VALUES ($phs)
            ";

            $pdo->prepare($sqlInsert)->execute($nuevo);
            $registros++;
        }

        $pdo->commit();

        return [
            'output' => [
                'valid' => true,
                'response' => [
                    'actualizado_original' => true,
                    'registros_creados' => $registros
                ]
            ]
        ];

    } catch (PDOException $e) {

        $pdo->rollBack();
        return ['output' => [
            'valid' => false,
            'error' => $e->getMessage()
        ]];

    } finally {
        $db->closeConect();
    }
}



  private static function correoSecretaria($correo, $secretario, $ciudad)
  {

    require '../../vendor/autoload.php';
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'email@spidersoftware.co';
    $mail->Password   = 'Marti3933++$$++';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;


    $mail->setFrom('email@spidersoftware.co', 'Nuevo compromiso');
    $mail->addAddress($correo, $secretario);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->Subject = "Nuevo compromiso - Gobernación de Santander";

    $mail->Body = '
                            <!DOCTYPE html>
                            <html lang="es">
                            <head>
                                <meta charset="UTF-8">
                                <style>
                                .container {
                                    max-width: 600px;
                                    margin: auto;
                                    font-family: "Segoe UI", sans-serif;
                                    border: 1px solid #e0e0e0;
                                    border-radius: 8px;
                                    padding: 20px;
                                    background-color: #ffffff;
                                    color: #333;
                                }
                                .header {
                                    background-color: #234162;
                                    color: white;
                                    padding: 15px;
                                    text-align: center;
                                    border-radius: 8px 8px 0 0;
                                }
                                .header h1 {
                                    margin: 0;
                                    font-size: 20px;
                                }
                                .content {
                                    padding: 20px 10px;
                                }
                                .content p {
                                    line-height: 1.6;
                                }
                                .btn {
                                    display: inline-block;
                                    margin-top: 20px;
                                    padding: 12px 20px;
                                    background-color: #00796b;
                                    color: white;
                                    text-decoration: none;
                                    border-radius: 6px;
                                    font-weight: bold;
                                }
                                .footer {
                                    margin-top: 30px;
                                    font-size: 12px;
                                    text-align: center;
                                    color: #888;
                                }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                <div class="header">
                                    <h1>Gobernación de Santander</h1>
                                </div>
                                <div class="content">
                                    <p>Cordial saludo</p><br>

                                    <p>El señor Gobernador acaba de pactar un compromiso en el municipio ' . htmlspecialchars($ciudad) . '  correspondiente a su Secretaría. Agradecemos revisar la plataforma y realizar el seguimiento respectivo.</p> <br>
                                   
                                    <p>Cordialmente,<br>Equipo de soporte - Accion Unificada, <br> Gobernación de Santander</p>
                                </div>
                                <div class="footer">
                                    Este mensaje fue enviado automáticamente. Por favor, no respondas a este correo.
                                </div>
                                </div>
                            </body>
                            </html>';


    $sent = $mail->send();
    if (!$sent) {
      return [
        'state' => false,
        'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo
      ];
    }
  }

  private static function correoCompromisoActualizado($correo, $secretario, $ciudad, $item)
  {

    require '../../vendor/autoload.php';
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'email@spidersoftware.co';
    $mail->Password   = 'Marti3933++$$++';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;


    $mail->setFrom('email@spidersoftware.co', 'Compromiso Actualizado');
    $mail->addAddress($correo, $secretario);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->Subject = "Actualización de Compromiso - Gobernación de Santander";

    $mail->Body = '
                            <!DOCTYPE html>
                            <html lang="es">
                            <head>
                                <meta charset="UTF-8">
                                <style>
                                .container {
                                    max-width: 600px;
                                    margin: auto;
                                    font-family: "Segoe UI", sans-serif;
                                    border: 1px solid #e0e0e0;
                                    border-radius: 8px;
                                    padding: 20px;
                                    background-color: #ffffff;
                                    color: #333;
                                }
                                .header {
                                    background-color: #234162;
                                    color: white;
                                    padding: 15px;
                                    text-align: center;
                                    border-radius: 8px 8px 0 0;
                                }
                                .header h1 {
                                    margin: 0;
                                    font-size: 20px;
                                }
                                .content {
                                    padding: 20px 10px;
                                }
                                .content p {
                                    line-height: 1.6;
                                }
                                .btn {
                                    display: inline-block;
                                    margin-top: 20px;
                                    padding: 12px 20px;
                                    background-color: #00796b;
                                    color: white;
                                    text-decoration: none;
                                    border-radius: 6px;
                                    font-weight: bold;
                                }
                                .footer {
                                    margin-top: 30px;
                                    font-size: 12px;
                                    text-align: center;
                                    color: #888;
                                }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                <div class="header">
                                    <h1>Gobernación de Santander</h1>
                                </div>
                                <div class="content">
                                    <p>Cordial saludo</p><br>

                                    <p>Se ha actualizado el compromiso con Item ' . htmlspecialchars($item) . ' en el municipio ' . htmlspecialchars($ciudad) . '  correspondiente a su Secretaría. Agradecemos revisar la plataforma y realizar el seguimiento respectivo.</p> <br>

                                    <p>Cordialmente,<br>Equipo de soporte - Accion Unificada, <br> Gobernación de Santander</p>
                                </div>
                                <div class="footer">
                                    Este mensaje fue enviado automáticamente. Por favor, no respondas a este correo.
                                </div>
                                </div>
                            </body>
                            </html>';


    $sent = $mail->send();
    if (!$sent) {
      return [
        'state' => false,
        'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo
      ];
    }
  }

  private static function correoAlcadia($correo, $secretaria, $ciudad, $fecha)
  {

    require '../../vendor/autoload.php';
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'email@spidersoftware.co';
    $mail->Password   = 'Marti3933++$$++';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;


    $mail->setFrom('email@spidersoftware.co', 'Nuevo compromiso');
    $mail->addAddress($correo, $secretaria);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->Subject = "Nuevo compromiso - Gobernación de Santander";

    $mail->Body = '
                    <!DOCTYPE html>
                    <html lang="es">
                    <head>
                        <meta charset="UTF-8">
                        <style>
                        .container {
                            max-width: 600px;
                            margin: auto;
                            font-family: "Segoe UI", sans-serif;
                            border: 1px solid #e0e0e0;
                            border-radius: 8px;
                            padding: 20px;
                            background-color: #ffffff;
                            color: #333;
                        }
                        .header {
                            background-color: #234162;
                            color: white;
                            padding: 15px;
                            text-align: center;
                            border-radius: 8px 8px 0 0;
                        }
                        .header h1 {
                            margin: 0;
                            font-size: 20px;
                        }
                        .content {
                            padding: 20px 10px;
                        }
                        .content p {
                            line-height: 1.6;
                        }
                        .btn {
                            display: inline-block;
                            margin-top: 20px;
                            padding: 12px 20px;
                            background-color: #00796b;
                            color: white;
                            text-decoration: none;
                            border-radius: 6px;
                            font-weight: bold;
                        }
                        .footer {
                            margin-top: 30px;
                            font-size: 12px;
                            text-align: center;
                            color: #888;
                        }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                        <div class="header">
                            <h1>Gobernación de Santander</h1>
                        </div>
                        <div class="content">
                            <p>Estimado señor Alcalde de ' . htmlspecialchars($ciudad) . '</p><br>

                            <p>Nos permitimos informarle que la Secretaría ' . htmlspecialchars($secretaria) . ' ya ha dado respuesta al compromiso adquirido por el señor Gobernador el día ' . htmlspecialchars($fecha) . '.
                            Puede consultar el estado y la respuesta correspondiente ingresando a la plataforma de Acción Unificada, en la sección de compromisos.
                            .</p> <br>

                            <p>Quedamos atentos a cualquier inquietud adicional.</p><br>
                            
                            <p>Cordialmente,<br>Equipo de soporte - Accion Unificada, <br> Gobernación de Santander</p>
                        </div>
                        <div class="footer">
                            Este mensaje fue enviado automáticamente. Por favor, no respondas a este correo.
                        </div>
                        </div>
                    </body>
                    </html>';


    $sent = $mail->send();
    if (!$sent) {
      return [
        'state' => false,
        'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo
      ];
    }
  }

  public static function graficoSeguimiento($idSecretaria = null, $componente = null, $tipo_ejecucion = null)
  {
    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $where = "TRIM(UPPER(REPLACE(a.tipo_registro, CHAR(160), ''))) = 'COMPROMISO'";

      if ($idSecretaria !== "" && $idSecretaria !== null) {
        $where .= " AND a.tbl_secretarias_id = :idSecretaria";
      }
      if ($componente !== "" && $componente !== null) {
        $where .= " AND a.componente = :componente";
      }
      if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
        $where .= " AND a.tipo_ejecucion = :tipo_ejecucion";
      }

      $q = "SELECT 
              provincia,
              COUNT(*) AS total
          FROM 
              " . $db->getTable('tbl_visitas') . " a
          WHERE $where
          GROUP BY 
              provincia
          ORDER BY 
              total DESC;";

      $stmt = $pdo->prepare($q);

      if ($idSecretaria !== "" && $idSecretaria !== null) {
        $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
      }
      if ($componente !== "" && $componente !== null) {
        $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
      }
      if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
        $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
      }

      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $q2 = "SELECT 
              estado,
              COUNT(*) AS total
          FROM 
              " . $db->getTable('tbl_visitas') . " a
          WHERE 
              $where
          GROUP BY 
              estado;";

      $stmt = $pdo->prepare($q2);

      if ($idSecretaria !== "" && $idSecretaria !== null) {
        $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
      }
      if ($componente !== "" && $componente !== null) {
        $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
      }
      if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
        $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
      }

      $stmt->execute();
      $porcentaje = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return [
        'state' => true,
        'data' => $result,
        'porcentaje' => $porcentaje
      ];
    } catch (PDOException $th) {
      return [
        'state' => false,
        'message' => $th->getMessage()
      ];
    }
  }


public static function porcentaje($idSecretaria = null, $componente = null, $tipo_ejecucion = null)
{
    try {
        $db = new DbConection();
        $pdo = $db->openConect();

        $filtros = "";
        if (!empty($idSecretaria)) {
            $filtros .= " AND v.tbl_secretarias_id = :idSecretaria";
        }
        if (!empty($componente)) {
            $filtros .= " AND v.componente = :componente";
        }
        if (!empty($tipo_ejecucion)) {
            $filtros .= " AND v.tipo_ejecucion = :tipo_ejecucion";
        }
        
        // --- FILTRO BASE CONSISTENTE PARA TODOS LOS CONTEOS ---
        // Se asume que solo se debe contar TIPO_REGISTRO = 'COMPROMISO'
        $baseTipoRegistro = " AND TRIM(UPPER(REPLACE(v.tipo_registro, CHAR(160), ''))) = 'COMPROMISO'";
        // ----------------------------------------------------

        $estadoNormalized = "TRIM(UPPER(REPLACE(v.estado, CHAR(160), '')))";
        $estadoNormalizedLen = "TRIM(REPLACE(v.estado, CHAR(160), ''))";

        // CONSULTA ÚNICA Y CONSOLIDADA (Reemplaza las 5 consultas anteriores)
        $qConsolidado = "
            SELECT 
                COUNT(v.id) AS total_compromisos,
                SUM(CASE WHEN $estadoNormalized = 'CUMPLIDO' THEN 1 ELSE 0 END) AS cumplidos,
                SUM(CASE WHEN $estadoNormalized IN ('EN TRÁMITE', 'EN TRAMITE') THEN 1 ELSE 0 END) AS en_tramite,
                SUM(CASE WHEN $estadoNormalized = 'EN ESPERA' THEN 1 ELSE 0 END) AS en_espera,
                SUM(CASE 
                    WHEN $estadoNormalized IN ('SIN CUMPLIR', 'POR CUMPLIR')
                    OR v.estado IS NULL 
                    OR $estadoNormalizedLen = ''
                    THEN 1 
                    ELSE 0 
                END) AS sin_cumplir
            FROM " . $db->getTable('tbl_visitas') . " v
            WHERE 1=1
            AND v.estado IS NOT NULL
            AND TRIM(REPLACE(v.estado, CHAR(160), '')) != '' AND TRIM(UPPER(REPLACE(v.estado, CHAR(160), ''))) != 'NULL'
            $baseTipoRegistro
            $filtros
        ";
        
        $stmt = $pdo->prepare($qConsolidado);
        if (!empty($idSecretaria)) $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
        if (!empty($componente)) $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
        if (!empty($tipo_ejecucion)) $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
        $stmt->execute();
        $conteo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Asignación de resultados
        $totalCompromisos = (int)$conteo['total_compromisos'];
        $cumplidos = (int)$conteo['cumplidos'];
        $enTramite = (int)$conteo['en_tramite'];
        $enEspera = (int)$conteo['en_espera'];
        $sinCumplir = (int)$conteo['sin_cumplir'];

        $sinEstados = "AND v.estado IS NOT NULL AND TRIM(REPLACE(v.estado, CHAR(160), '')) != '' AND TRIM(UPPER(REPLACE(v.estado, CHAR(160), ''))) != 'NULL'";

        $qMetaGlobal = "
            SELECT 
                COUNT(v.id) AS meta_global
            FROM " . $db->getTable('tbl_visitas') . " v
            WHERE 1=1
            $sinEstados
            $baseTipoRegistro
        ";

        $stmtMeta = $pdo->prepare($qMetaGlobal);
        $stmtMeta->execute();
        $meta = $stmtMeta->fetch(PDO::FETCH_ASSOC);

        $metaOficial = (int)$meta['meta_global'];
        if ($metaOficial < 1) $metaOficial = 1; 


        // PROVINCIAS Y MUNICIPIOS REALES (Mantenemos esta consulta separada)
        $qUbicaciones = "
            SELECT 
                COUNT(DISTINCT NULLIF(TRIM(c.subregion), '')) AS total_provincias,
                COUNT(DISTINCT NULLIF(TRIM(c.municipio), '')) AS total_municipios
            FROM " . $db->getTable('tbl_visitas') . " v
            INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c 
                ON v.tbl_municipio_id = c.codigo_muncipio
            WHERE 1=1 
            $sinEstados
            $baseTipoRegistro
            $filtros
        ";
        $stmt = $pdo->prepare($qUbicaciones);
        if (!empty($idSecretaria)) $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
        if (!empty($componente)) $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
        if (!empty($tipo_ejecucion)) $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
        $stmt->execute();
        $ubicaciones = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalProvincias = (int)$ubicaciones['total_provincias'];
        $totalMunicipios = (int)$ubicaciones['total_municipios'];

        return [
            'state' => true,
            'data' => [
                'total_compromisos' => $totalCompromisos,
                'cumplidos'         => $cumplidos,
                'en_tramite'        => $enTramite,
                'en_espera'         => $enEspera,
                'sin_cumplir'       => $sinCumplir,
                'total_provincias'  => $totalProvincias,
                'total_municipios'  => $totalMunicipios,
                'meta_oficial'      => $metaOficial

            ]
        ];
    } catch (PDOException $th) {
        return [
            'state' => false,
            'message' => $th->getMessage()
        ];
    }
}


  /**
   * Obtiene la informacion para el visor de gestion de compromisos
   *
   * @param int|null $secretariaIdToFilter El ID de la secretaría a filtrar (solo para secretarios).
   * @return array
   */
  public static function getVisorGestionDeCompromiso($secretariaIdToFilter = null)
  {
    try {
      $db = new DbConection();
      // Asumimos que openConect() no aplica filtros de usuario para esta vista,
      // ya que estamos implementando el filtro aquí.
      $pdo = $db->openConect();

      // 💥 1. Preparar la variable de filtro 💥
      $secretariaIdFilter = intval($secretariaIdToFilter);
      
      $tableVisitas = $db->getTable('tbl_visitas') . " AS a";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS s";

      $where = "TRIM(UPPER(REPLACE(a.tipo_registro, CHAR(160), ''))) = 'COMPROMISO'";
      $where .= " AND a.estado IS NOT NULL";
      $where .= " AND TRIM(REPLACE(a.estado, CHAR(160), '')) != ''";
      $where .= " AND TRIM(UPPER(REPLACE(a.estado, CHAR(160), ''))) != 'NULL'";
      
      // 💥 2. Añadir la condición WHERE si hay un filtro (Solo para Secretarios) 💥
      $bindParams = [];
      if ($secretariaIdFilter > 0) {
          // Si recibimos un ID válido, filtramos por la secretaría que registró la visita.
          $where .= " AND a.tbl_secretarias_id = :secretariaIdFilter"; 
          $bindParams[':secretariaIdFilter'] = $secretariaIdFilter;
      }

      $eNorm = "TRIM(UPPER(REPLACE(a.estado, CHAR(160), '')))";

      // La query base permanece igual, pero usa la variable $where
      $q = "SELECT
                s.secretaria AS entidad,
                COUNT(a.id) AS total_compromisos,
                SUM(CASE WHEN $eNorm IN ('EN TRÁMITE', 'EN TRAMITE') THEN 1 ELSE 0 END) AS en_tramite,
                SUM(CASE WHEN $eNorm = 'CUMPLIDO' THEN 1 ELSE 0 END) AS cumplido,
                SUM(CASE WHEN $eNorm IN ('SIN CUMPLIR', 'POR CUMPLIR') THEN 1 ELSE 0 END) AS sin_cumplir,
                SUM(CASE WHEN $eNorm = 'EN ESPERA' THEN 1 ELSE 0 END) AS en_espera,
                -- Cálculo de calificación (porcentaje de cumplimiento)
                (CASE 
                    WHEN COUNT(a.id) > 0 THEN (SUM(CASE WHEN $eNorm = 'CUMPLIDO' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id))
                    ELSE 0.0
                END) AS calificacion_porcentaje
            FROM
                {$tableVisitas}
            LEFT JOIN
                {$tableSecretarias} ON a.tbl_secretarias_id = s.id
            WHERE
                {$where}
            GROUP BY
                s.secretaria, s.id
            ORDER BY
                total_compromisos DESC;";

      $stmt = $pdo->prepare($q);
      
      // 💥 3. Bindear los parámetros si existen 💥
      if (!empty($bindParams)) {
          foreach ($bindParams as $key => $value) {
              $stmt->bindValue($key, $value, PDO::PARAM_INT);
          }
      }
      
      $stmt->execute();
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($results as &$row) {
        $calificacionNumerica = round((float)$row['calificacion_porcentaje'], 2);
        $row['calificacion_porcentaje'] = $calificacionNumerica . '%';

        // Lógica de colores (sin cambios)
        if ($calificacionNumerica < 50) {
          $row['color_calificacion']  = '#DC143C'; 
        } else if ($calificacionNumerica >= 50 && $calificacionNumerica < 100) {
          $row['color_calificacion']  = '#f1c40f'; 
        } else {
          $row['color_calificacion']  = '#2ecc71'; 
        }
      }
      return array('output' => array('valid' => true, 'response' => $results));
    } catch (PDOException $th) {
      return Util::error_no_result();
    } finally {
      $db->closeConect();
    }
  }

  public static function dataMapa($idSecretaria = null, $componente = null, $tipo_ejecucion = null)
  {
    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $where = "TRIM(UPPER(REPLACE(a.tipo_registro, CHAR(160), ''))) = 'COMPROMISO'";
      if ($idSecretaria !== "" && $idSecretaria !== null) {
        $where .= " AND a.tbl_secretarias_id = :idSecretaria";
      }
      if ($componente !== "" && $componente !== null) {
        $where .= " AND a.componente = :componente";
      }
      if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
        $where .= " AND a.tipo_ejecucion = :tipo_ejecucion";
      }

      $q = "SELECT DISTINCT
              a.tbl_municipio_id,
              b.municipio 
            FROM
              " . $db->getTable('tbl_visitas') . " a
              INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " b ON a.tbl_municipio_id = b.codigo_muncipio 
            WHERE
              $where";
      $stmt = $pdo->prepare($q);
      if ($idSecretaria !== "" && $idSecretaria !== null) {
        $stmt->bindValue(':idSecretaria', (int)$idSecretaria, PDO::PARAM_INT);
      }
      if ($componente !== "" && $componente !== null) {
        $stmt->bindValue(':componente', $componente, PDO::PARAM_STR);
      }
      if ($tipo_ejecucion !== "" && $tipo_ejecucion !== null) {
        $stmt->bindValue(':tipo_ejecucion', $tipo_ejecucion, PDO::PARAM_STR);
      }
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      return [
        'state' => true,
        'data' => $result
      ];
    } catch (PDOException $th) {
      return [
        'state' => false,
        'message' => $th->getMessage()
      ];
    }
  }

public function dataPorMunicipioSecretaria($data)
{
    $draw = intval($data['draw'] ?? 1);
    $start = intval($data['start'] ?? 0);
    $length = intval($data['length'] ?? 10);
    $searchValue = $data['search']['value'] ?? '';
    $orderColumn = $data['order'][0]['column'] ?? 0;
    $orderDir = 'desc';

    $municipioId = trim($data['data']['municipio'] ?? '');
    $secretariaId = trim($data['data']['secretaria'] ?? '');
    $componente = trim($data['data']['componente'] ?? '');
    $tipo_ejecucion = trim($data['data']['tipo_ejecucion'] ?? '');
    $estado = trim($data['data']['estado'] ?? '');

    try {
        $db = new DbConection();
        $pdo = $db->openConect();

        $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
        $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
        $tableCiudades = $db->getTable('tbl_ciudades_accion_unificada') . " AS ciudad";

        if (!empty($estado)) {
            $where = "
                REPLACE(UPPER(TRIM(REPLACE(visitas.tipo_registro, CHAR(160), ''))), 'Á', 'A')
                IN ('COMPROMISO','SOLICITUD')
            ";
        } else {
            $where = "
                REPLACE(UPPER(TRIM(REPLACE(visitas.tipo_registro, CHAR(160), ''))), 'Á', 'A') = 'COMPROMISO'
            ";
        }

        $params = [];

        if ($municipioId !== '') {
            $where .= " AND ciudad.codigo_muncipio = :municipio";
            $params[':municipio'] = $municipioId;
        }

        if ($secretariaId !== '') {
            $where .= " AND visitas.tbl_secretarias_id = :secretaria";
            $params[':secretaria'] = $secretariaId;
        }

        if ($componente !== '') {
            $where .= " AND visitas.componente = :componente";
            $params[':componente'] = $componente;
        }

        if ($tipo_ejecucion !== '') {
            $where .= " AND visitas.tipo_ejecucion = :tipo_ejecucion";
            $params[':tipo_ejecucion'] = $tipo_ejecucion;
        }
if (!empty($data['data']['provincia'])) {
    $provincia = trim($data['data']['provincia']);
    $where .= " AND visitas.provincia = :provincia";
    $params[':provincia'] = $provincia;
}

        if (!empty($estado)) {
            if (strtoupper($estado) === 'SIN CUMPLIR') {
                $where .= "
                    AND (
                        REPLACE(UPPER(TRIM(REPLACE(visitas.estado, CHAR(160), ''))), 'Á', 'A') COLLATE utf8_spanish2_ci LIKE 'SIN CUMPLIR%'
                        OR REPLACE(UPPER(TRIM(REPLACE(visitas.estado, CHAR(160), ''))), 'Á', 'A') COLLATE utf8_spanish2_ci LIKE 'POR CUMPLIR%'
                    )
                ";
            } else {
                $where .= "
                    AND REPLACE(UPPER(TRIM(REPLACE(visitas.estado, CHAR(160), ''))), 'Á', 'A') COLLATE utf8_spanish2_ci
                    LIKE CONCAT(REPLACE(UPPER(TRIM(REPLACE(:estado, CHAR(160), ''))), 'Á', 'A'), '%')
                ";
                $params[':estado'] = $estado;
            }
        }

        if (!empty($searchValue)) {
            $where .= " AND (
                visitas.date LIKE :search OR
                ciudad.municipio LIKE :search OR
                visitas.provincia LIKE :search OR
                secretaria.secretaria LIKE :search OR
                visitas.componente LIKE :search OR
                visitas.created_at LIKE :search
            )";
            $params[':search'] = "%$searchValue%";
        }

        $sqlFiltered = "SELECT COUNT(*) FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            WHERE $where";

        $stmtFiltered = $pdo->prepare($sqlFiltered);
        foreach ($params as $key => $val) {
            $stmtFiltered->bindValue($key, $val);
        }
        $stmtFiltered->execute();
        $recordsFiltered = $stmtFiltered->fetchColumn();

        $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio";
        $recordsTotal = $pdo->query($sqlTotal)->fetchColumn();

        $sqlData = "SELECT
            visitas.date,
            visitas.update_at,
            ciudad.municipio,
            visitas.compromisos,
            visitas.provincia,
            visitas.respuesta,
            secretaria.secretaria,
            visitas.img,
            visitas.id,
            visitas.estado,
            visitas.consecuencia,
            visitas.compromisopac,
            visitas.componente
        FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
        WHERE $where
        ORDER BY visitas.id $orderDir
        LIMIT :start, :length";

        $stmtData = $pdo->prepare($sqlData);
        foreach ($params as $key => $val) {
            $stmtData->bindValue($key, $val);
        }
        $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
        $stmtData->bindValue(':length', $length, PDO::PARAM_INT);
        $stmtData->execute();

        $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($result as $row) {
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
            } else {
                $imagen = '';
            }

            $data[] = [
                'date' => $row['date'],
                'created_at' => date('Y-m-d', strtotime($row['update_at'])),
                'municipio' => $row['municipio'],
                'compromisos' => $row['compromisos'],
                'provincia' => $row['provincia'],
                'respuesta' => $row['respuesta'],
                'secretaria' => $row['secretaria'],
                'estado' => $row['estado'],
                'consecuencia' => $row['consecuencia'],
                'compromisopac' => $row['compromisopac'],
                'foto' => $imagen,
                'id_compromiso' => $row['id'],
                'componente' => $row['componente']
            ];
        }

        return [
            "draw" => $draw,
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $data
        ];
    } catch (PDOException $e) {
        return ['state' => false, 'error' => $e->getMessage()];
    }
}

  public function dataPorMunicipioSecretariaTodosLosEstados($data)
  {
    $draw = intval($data['draw'] ?? 1);
    $start = intval($data['start'] ?? 0);
    $length = intval($data['length'] ?? 10);
    $searchValue = $data['search']['value'] ?? '';
    $orderColumn = $data['order'][0]['column'] ?? 0;
    $orderDir = 'desc'; // puedes ajustar si usas dynamic order

    $municipioId = trim($data['data']['municipio'] ?? '');
    $secretariaId = trim($data['data']['secretaria'] ?? '');
    $componente = trim($data['data']['componente'] ?? '');
    $tipo_ejecucion = trim($data['data']['tipo_ejecucion'] ?? '');
    $estado = trim($data['data']['estado'] ?? '');


    try {
      $db = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

      $where = "TRIM(UPPER(REPLACE(visitas.tipo_registro, CHAR(160), ''))) = 'COMPROMISO'";

      $params = [];



      if (isset($municipioId) && $municipioId !== '') {
        $where .= " AND ciudad.codigo_muncipio = :municipio";
        $params[':municipio'] = $municipioId;
      }

      if (isset($secretariaId) && $secretariaId !== '') {
        $where .= " AND visitas.tbl_secretarias_id = :secretaria";
        $params[':secretaria'] = $secretariaId;
      }
      if (isset($componente) && $componente !== '') {
        $where .= " AND visitas.componente = :componente";
        $params[':componente'] = $componente;
      }
      if (isset($tipo_ejecucion) && $tipo_ejecucion !== '') {
        $where .= " AND visitas.tipo_ejecucion = :tipo_ejecucion";
        $params[':tipo_ejecucion'] = $tipo_ejecucion;
      }
if (!empty($data['data']['provincia'])) {
    $provincia = trim($data['data']['provincia']);
    $where .= " AND visitas.provincia = :provincia";
    $params[':provincia'] = $provincia;
}


      if (!empty($searchValue)) {
        $where .= " AND (
                visitas.date LIKE :search OR
                ciudad.municipio LIKE :search OR
                visitas.provincia LIKE :search OR
                secretaria.secretaria LIKE :search  OR
                visitas.componente LIKE :search
                visitas.created_at LIKE :search
            )";
        $params[':search'] = "%$searchValue%";
      }

      // Total filtrado
      $sqlFiltered = "SELECT COUNT(*) FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
            WHERE $where";

      $stmtFiltered = $pdo->prepare($sqlFiltered);
      foreach ($params as $key => $val) {
        $stmtFiltered->bindValue($key, $val);
      }
      $stmtFiltered->execute();
      $recordsFiltered = $stmtFiltered->fetchColumn();

      // Total sin filtros
      $sqlTotal = "SELECT COUNT(*) FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio";
      $recordsTotal = $pdo->query($sqlTotal)->fetchColumn();

      // Datos paginados
      $sqlData = "SELECT
            visitas.date,
            visitas.update_at,
            ciudad.municipio,
            visitas.compromisos,
            visitas.provincia,
            visitas.respuesta,
            secretaria.secretaria,
            visitas.img,
            visitas.id,
            visitas.estado,
            visitas.consecuencia,
            visitas.compromisopac,
            visitas.componente
        FROM $tableCompromisos
            LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
            LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
        WHERE $where
        ORDER BY visitas.id $orderDir
        LIMIT :start, :length";

      $stmtData = $pdo->prepare($sqlData);
      foreach ($params as $key => $val) {
        $stmtData->bindValue($key, $val);
      }
      $stmtData->bindValue(':start', $start, PDO::PARAM_INT);
      $stmtData->bindValue(':length', $length, PDO::PARAM_INT);
      $stmtData->execute();

      $result = $stmtData->fetchAll(PDO::FETCH_ASSOC);

      $data = [];
      foreach ($result as $row) {
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
        } else {
          $imagen = '';
        }

        $data[] = [
          'date' => $row['date'],
          'created_at' => date('Y-m-d', strtotime($row['update_at'])),
          'municipio' => $row['municipio'],
          'compromisos' => $row['compromisos'],
          'provincia' => $row['provincia'],
          'respuesta' => $row['respuesta'],
          'secretaria' => $row['secretaria'],
          'estado' => $row['estado'],
          'consecuencia' => $row['consecuencia'],
          'compromisopac' => $row['compromisopac'],
          'foto' => $imagen,
          'id_compromiso' => $row['id'],
          'componente' => $row['componente']
        ];
      }

      return [
        "draw" => $draw,
        "recordsTotal" => intval($recordsTotal),
        "recordsFiltered" => intval($recordsFiltered),
        "data" => $data
      ];
    } catch (PDOException $e) {
      return ['state' => false, 'error' => $e->getMessage()];
    }
  }

  /**
   * Exporta todos los compromisos a un archivo Excel (CSV con BOM UTF-8).
   * Respeta los mismos filtros y permisos de sesión que la tabla principal.
   *
   * @param array $data Filtros opcionales: id, secretaria, municipio, componente, provincia, estado
   * @return void Envía el archivo directamente al navegador
   */
  public function exportarCompromisosExcel($data)
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    $idFiltro         = intval($data['id'] ?? 0);
    $frontendSecretaria = intval($data['secretaria'] ?? 0);
    $frontendMunicipio  = intval($data['municipio'] ?? 0);
    $componente       = $data['componente'] ?? '';
    $provincia        = $data['provincia'] ?? '';
    $estado           = $data['estado'] ?? '';

    // Permisos de sesión
    $userType          = SessionData::getUserType();
    $sessionSecretaria = SessionData::getSecretaria();
    $sessionMunicipio  = SessionData::getCodigoMunicipio();

    $isAdmin             = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
    $isSecretario        = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob() || $userType === Util::Secretaria_Despacho_Gobernacion());
    $isAlcaldeOAuxiliar  = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

    try {
      $db  = new DbConection();
      $pdo = $db->openConect();

      $tableCompromisos = $db->getTable('tbl_visitas') . " AS visitas";
      $tableSecretarias = $db->getTable('tbl_secretarias') . " AS secretaria";
      $tableCiudades    = $db->getTable('tbl_ciudades') . " AS ciudad";

      // Construir WHERE con filtros de rol
      $where  = " TRIM(UPPER(REPLACE(visitas.tipo_registro, CHAR(160), ''))) = 'COMPROMISO' ";
      $where .= " AND visitas.estado IS NOT NULL AND TRIM(REPLACE(visitas.estado, CHAR(160), '')) != '' AND TRIM(UPPER(REPLACE(visitas.estado, CHAR(160), ''))) != 'NULL' ";
      $params = [];

      if ($isSecretario && $sessionSecretaria !== '000') {
        $where .= " AND visitas.tbl_secretarias_id = :sessionSecretariaId";
        $params[':sessionSecretariaId'] = $sessionSecretaria;
      } elseif ($isAlcaldeOAuxiliar) {
        $where .= " AND visitas.tbl_municipio_id = :sessionMunicipioId";
        $params[':sessionMunicipioId'] = $sessionMunicipio;
      }

      // Filtros del frontend
      if ($idFiltro > 0) {
        $where .= " AND visitas.id = :idFiltro";
        $params[':idFiltro'] = $idFiltro;
      }
      if ($frontendMunicipio > 0) {
        $where .= " AND ciudad.codigo_muncipio = :frontendMunicipio";
        $params[':frontendMunicipio'] = $frontendMunicipio;
      }
      if ($frontendSecretaria > 0) {
        $where .= " AND secretaria.id = :frontendSecretaria";
        $params[':frontendSecretaria'] = $frontendSecretaria;
      }
      if (!empty($componente)) {
        $where .= " AND visitas.componente LIKE :componente";
        $params[':componente'] = "%" . $componente . "%";
      }
      if (!empty($provincia)) {
        $where .= " AND visitas.provincia = :provincia";
        $params[':provincia'] = $provincia;
      }
      if (!empty($estado)) {
        $where .= " AND visitas.estado = :estado";
        $params[':estado'] = $estado;
      }

      // Consulta sin LIMIT para obtener todos los registros
      $tableObservaciones = $db->getTable('tbl_visitas_x_observaciones') . " AS obs";
      $sql = "SELECT
                visitas.id,
                secretaria.secretaria,
                visitas.created_at,
                visitas.DATE,
                visitas.provincia,
                ciudad.municipio,
                visitas.estado,
                visitas.compromisopac,
                visitas.respuesta,
                visitas.consecuencia,
                visitas.componente,
                visitas.tipo_ejecucion,
                visitas.url,
                visitas.pdf,
                GROUP_CONCAT(
                  CONCAT(obs.dtcreate, ' - ', obs.observaciones)
                  ORDER BY obs.id DESC
                  SEPARATOR ' | '
                ) AS observaciones
              FROM $tableCompromisos
              LEFT JOIN $tableSecretarias ON visitas.tbl_secretarias_id = secretaria.id
              LEFT JOIN $tableCiudades ON visitas.tbl_municipio_id = ciudad.codigo_muncipio
              LEFT JOIN $tableObservaciones ON obs.tbl_visita_id = visitas.id
              WHERE $where
              GROUP BY visitas.id
              ORDER BY visitas.id DESC";

      $stmt = $pdo->prepare($sql);
      foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
      }
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $db->closeConect();

      // Generar archivo XLSX real con PhpSpreadsheet
      $vendorPath = __DIR__ . '/../../vendor/autoload.php';
      if (!file_exists($vendorPath)) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Error: Librería PhpSpreadsheet no encontrada.';
        exit;
      }
      require_once $vendorPath;

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Compromisos');

      $headers = [
        'ID',
        'Secretaría',
        'Fecha de Creación',
        'Fecha',
        'Provincia',
        'Municipio',
        'Estado',
        'Compromiso Pactado',
        'Respuesta',
        'Consecuencia',
        'Componente',
        'Tipo Ejecución',
        'URL',
        'PDF',
        'Observaciones'
      ];

      foreach ($headers as $colIdx => $header) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
        $sheet->setCellValue($colLetter . '1', $header);
        $sheet->getStyle($colLetter . '1')
          ->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($colLetter . '1')
          ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setARGB('FF20427F');
        $sheet->getStyle($colLetter . '1')
          ->getAlignment()
          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
          ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
      }
      $sheet->getRowDimension('1')->setRowHeight(22);

      $rowNum = 2;
      foreach ($result as $row) {
        $cols = [
          $row['id'],
          $row['secretaria'],
          $row['created_at'],
          $row['DATE'],
          $row['provincia'],
          $row['municipio'],
          $row['estado'],
          $row['compromisopac'],
          $row['respuesta'],
          $row['consecuencia'],
          $row['componente'],
          $row['tipo_ejecucion'],
          $row['url'],
          $row['pdf'],
          $row['observaciones']
        ];
        foreach ($cols as $colIdx => $value) {
          $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
          $sheet->setCellValue($colLetter . $rowNum, $value);
          $sheet->getStyle($colLetter . $rowNum)
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
            ->setWrapText(true);
          $sheet->getStyle($colLetter . $rowNum)
            ->getBorders()->getBottom()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setARGB('FFD3D3D3');
        }
        $rowNum++;
      }

      foreach ($headers as $colIdx => $header) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
        $maxData = mb_strlen($header, 'UTF-8') + 2;
        $checkLimit = min($rowNum - 1, 51);
        for ($r = 2; $r < $checkLimit; $r++) {
          $val = $sheet->getCell($colLetter . $r)->getValue();
          if ($val) {
            $w = min(mb_strlen((string)$val, 'UTF-8'), 55);
            if ($w > $maxData) $maxData = $w;
          }
        }
        $sheet->getColumnDimension($colLetter)->setWidth(min($maxData + 2, 60));
      }

      $filename = 'compromisos_' . date('Y-m-d_His') . '.xlsx';
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
      exit;

    } catch (PDOException $e) {
      $db->closeConect();
      header('Content-Type: application/json');
      echo json_encode(['state' => false, 'error' => $e->getMessage()]);
      exit;
    }
  }

  public static function deleteCompromiso($rqst)
  {
    $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
    if ($id <= 0) {
      return ['state' => false, 'message' => 'ID no válido'];
    }

    $db = new DbConection();
    $pdo = $db->openConect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
      $q = "DELETE FROM " . $db->getTable('tbl_visitas') . " WHERE id = :id";
      $stmt = $pdo->prepare($q);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
      if ($stmt->rowCount() > 0) {
        $arrjson = ['state' => true, 'message' => 'Compromiso eliminado correctamente'];
      } else {
        $arrjson = ['state' => false, 'message' => 'No se encontró el compromiso con ID ' . $id];
      }
    } catch (PDOException $e) {
      $arrjson = ['state' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
    }

    $db->closeConect();
    return $arrjson;
  }
}
