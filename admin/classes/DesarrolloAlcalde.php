<?php

/**
 * Clase DesarrolloAlcalde
 * Gestiona el Plan de Desarrollo del municipio del Alcalde
 * Trabaja con tbl_plandesarrollo_alcalde (tabla exclusiva para alcaldes)
 * @author Adaptado para sistema Alcalde
 */
class DesarrolloAlcalde
{
    /**
     * Obtiene todos los registros del Plan de Desarrollo del municipio del Alcalde
     * @param array $rqst Parámetros de consulta
     * @return array JSON con registros del plan de desarrollo
     */
    public static function getAll($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once 'SessionData.php';
        $municipioAlcalde = SessionData::getCodigoMunicipio();
        $rolUsuario = SessionData::getUserType();
        $esAdmin = in_array($rolUsuario, ['SuperAdministrador', 'Administrador']);

        // Si no hay municipio y no es admin, retornar array vacío
        if (empty($municipioAlcalde) && !$esAdmin) {
            return array('output' => array('valid' => true, 'response' => []));
        }

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $secretaria_id = isset($rqst['secretaria_id']) ? intval($rqst['secretaria_id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $where_clauses = [];

        // Solo filtrar por municipio si NO es admin
        if (!$esAdmin) {
            $where_clauses[] = "pd.tbl_municipio_id = :municipio_id";
        }

        // Filtro por ID específico
        if ($id > 0) {
            $where_clauses[] = "pd.id = :id";
        }

        // Filtro por secretaría
        if ($secretaria_id > 0) {
            $where_clauses[] = "s.id = :secretaria_id";
        }

        $where = !empty($where_clauses) ? ' WHERE ' . implode(' AND ', $where_clauses) : '';

        $q = "SELECT pd.*, s.secretaria
              FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . " pd
              LEFT JOIN " . $db->getTable('tbl_secretarias_municipios') . " s ON pd.tbl_secretaria_id = s.id" .
              $where .
              " ORDER BY pd.id DESC";
        $stmt = $pdo->prepare($q);

        if (!$esAdmin) {
            $stmt->bindValue(':municipio_id', $municipioAlcalde);
        }

        if ($id > 0) {
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        }

        if ($secretaria_id > 0) {
            $stmt->bindValue(':secretaria_id', $secretaria_id, PDO::PARAM_INT);
        }

        $stmt->execute();

        $arr = array();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $arr = $result;
            $arrjson = array('output' => array('valid' => true, 'response' => $arr));
        } else {
            $arrjson = array('output' => array('valid' => true, 'response' => []));
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Metas del PDD alcalde filtradas por código de municipio (para formularios de creación).
     */
    public static function getByMunicipio($rqst)
    {
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? trim((string) $rqst['codigo_municipio']) : '';
        if ($codigoMunicipio === '' && isset($rqst['tbl_municipio_id'])) {
            $codigoMunicipio = trim((string) $rqst['tbl_municipio_id']);
        }
        if ($codigoMunicipio === '') {
            return ['output' => ['valid' => true, 'response' => []]];
        }

        $db = new DbConection();
        $pdo = $db->openConect();
        try {
            $q = "SELECT pd.id, pd.eje_estrategico, pd.sector_pdd, pd.sector_catalogo,
                         pd.producto_bien_servicio, pd.tbl_secretaria_id, pd.tbl_municipio_id,
                         s.secretaria
                  FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . " pd
                  LEFT JOIN " . $db->getTable('tbl_secretarias_municipios') . " s
                    ON pd.tbl_secretaria_id = s.id
                  WHERE CAST(pd.tbl_municipio_id AS CHAR) = :municipio_id
                  ORDER BY pd.eje_estrategico ASC, pd.sector_pdd ASC, pd.id DESC";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':municipio_id', $codigoMunicipio, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            return ['output' => ['valid' => true, 'response' => $rows]];
        } catch (Throwable $e) {
            return Util::error_general('Error al obtener metas del plan: ' . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }

public static function processExcelFile($rqst, $files) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once 'SessionData.php';
    $municipioAlcalde = SessionData::getCodigoMunicipio();
    $rolUsuario = SessionData::getUserType();

    // Determinar si el usuario es administrador (no requiere municipio asociado)
    $esAdmin = in_array($rolUsuario, ['SuperAdministrador', 'Administrador']);

    try {
        $db = new DbConection();
        $pdo = $db->openConect();

        // VALIDACIÓN A: Municipio de la sesión existe en BD (solo para roles NO administrativos)
        if (!$esAdmin) {
            $queryMunicipio = "SELECT COUNT(*) FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio = :codigo";
            $stmtMunicipio = $pdo->prepare($queryMunicipio);
            $stmtMunicipio->bindValue(':codigo', $municipioAlcalde, PDO::PARAM_STR);
            $stmtMunicipio->execute();

            if ($stmtMunicipio->fetchColumn() == 0) {
                return array('output' => array('valid' => false, 'message' => 'El municipio asociado a tu cuenta no existe.'));
            }
        }

        // VALIDACIÓN B: Archivo válido
        if (!isset($files['file']) || $files['file']['error'] != 0) {
            return array('output' => array('valid' => false, 'message' => 'No se pudo cargar el archivo.'));
        }

        $vendorPath = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($vendorPath)) {
            return array('output' => array('valid' => false, 'message' => 'Librería PhpSpreadsheet no encontrada.'));
        }
        require_once $vendorPath;

        // Carga de Excel
        $file = $files['file']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        $headers = $data[0] ?? [];
        $hasCodigoMunicipio = false;

        if (isset($headers[0]) && (stripos($headers[0], 'CODIGO') !== false || stripos($headers[0], 'MUNICIPIO') !== false)) {
            $hasCodigoMunicipio = true;
        }

        // VALIDACIÓN C: Coherencia de códigos de municipio en el Excel
        if ($hasCodigoMunicipio) {
            $municipioSet = [];
            for ($i = 1; $i < count($data); $i++) {
                $val = isset($data[$i][0]) ? trim($data[$i][0]) : '';
                if ($val !== '') $municipioSet[] = $val;
            }
            $uniqueMunicipios = array_unique($municipioSet);

            if (count($uniqueMunicipios) > 1) {
                return array('output' => array('valid' => false, 'message' => 'El Excel contiene múltiples códigos de municipio: ' . implode(', ', $uniqueMunicipios)));
            }

            // Para admins: tomar el municipio del Excel; para otros: validar coincidencia
            if ($esAdmin) {
                // El admin usa el código de municipio que viene en el Excel
                if (!empty($uniqueMunicipios)) {
                    $municipioAlcalde = array_values($uniqueMunicipios)[0];
                } else {
                    return array('output' => array('valid' => false, 'message' => 'El Excel no contiene un código de municipio válido.'));
                }

                // Verificar que el municipio del Excel existe en la BD
                $queryMunExcel = "SELECT COUNT(*) FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE codigo_muncipio = :codigo";
                $stmtMunExcel = $pdo->prepare($queryMunExcel);
                $stmtMunExcel->bindValue(':codigo', $municipioAlcalde, PDO::PARAM_STR);
                $stmtMunExcel->execute();
                if ($stmtMunExcel->fetchColumn() == 0) {
                    return array('output' => array('valid' => false, 'message' => "El código de municipio '{$municipioAlcalde}' del Excel no existe en la base de datos."));
                }
            } else {
                if (!empty($uniqueMunicipios) && $uniqueMunicipios[0] != $municipioAlcalde) {
                    return array('output' => array('valid' => false, 'message' => "El código del municipio en Excel ({$uniqueMunicipios[0]}) no coincide con su cuenta ({$municipioAlcalde})."));
                }
            }
        } else if ($esAdmin) {
            // Admin subió un Excel SIN columna de código de municipio
            return array('output' => array('valid' => false, 'message' => 'Como administrador, el archivo Excel debe incluir la columna "CÓDIGO MUNICIPIO" para identificar el municipio destino.'));
        }

        // Obtener secretarías válidas para este municipio
        $querySecretarias = "SELECT id FROM " . $db->getTable('tbl_secretarias_municipios') . " WHERE codigo_municipio = :municipio";
        $stmtSecretarias = $pdo->prepare($querySecretarias);
        $stmtSecretarias->bindValue(':municipio', $municipioAlcalde, PDO::PARAM_STR);
        $stmtSecretarias->execute();
        $validSecretarias = $stmtSecretarias->fetchAll(PDO::FETCH_COLUMN);

        $inserted = 0;
        $skipped = 0;
        $errors = [];

        // Si replace_mode=1, eliminar registros existentes del municipio antes de insertar
/*         $replaceMode = !empty($rqst['replace_mode']) && $rqst['replace_mode'] == '1';
        if ($replaceMode) {
            $stmtDel = $pdo->prepare(
                "DELETE FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . " WHERE tbl_municipio_id = :mun"
            );
            $stmtDel->bindValue(':mun', $municipioAlcalde, PDO::PARAM_STR);
            $stmtDel->execute();
        } */

        // 2. Procesamiento de filas reales
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];

            // Ignorar filas totalmente vacías
            $filaLimpia = array_filter($row, function($v) { return $v !== null && trim($v) !== ''; });
            if (empty($filaLimpia)) continue;

            $offset = $hasCodigoMunicipio ? 1 : 0;
            $ejeEstrategico    = $row[0 + $offset] ?? '';
            $sectorPdd         = $row[1 + $offset] ?? '';
            $secretariaIdExcel = $row[10 + $offset] ?? null;

            // Validar si es fila de ejemplo
            if ((empty($ejeEstrategico) && empty($sectorPdd)) || stripos($ejeEstrategico, 'Ejemplo') !== false) {
                $skipped++;
                continue;
            }

            // Validar Secretaría
            if ($secretariaIdExcel !== null && $secretariaIdExcel !== '' && !in_array($secretariaIdExcel, $validSecretarias)) {
                $errors[] = "Fila " . ($i + 1) . ": Secretaría ID '{$secretariaIdExcel}' no válida para su municipio.";
                $skipped++;
                continue;
            }

            try {
                $q = "INSERT INTO " . $db->getTable('tbl_plandesarrollo_alcalde') . "  
                    (eje_estrategico, sector_pdd, sector_catalogo, producto_bien_servicio,
                    anio_2024, avance_2024, avance_2025, anio_2025, anio_2026, anio_2027,
                    tbl_secretaria_id, tbl_municipio_id, created_at)
                    VALUES (:eje, :sector_pdd, :sector_catalogo, :producto,
                            :a24, :av24, :av25, :a25, :a26, :a27, :sec, :mun, NOW())";

                $stmt = $pdo->prepare($q);
                $stmt->bindValue(':eje', $ejeEstrategico, PDO::PARAM_STR);
                $stmt->bindValue(':sector_pdd', $sectorPdd, PDO::PARAM_STR);
                $stmt->bindValue(':sector_catalogo', $row[2 + $offset] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':producto', $row[3 + $offset] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':a24', $row[4 + $offset] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':av24', $row[5 + $offset] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':av25', $row[6 + $offset] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':a25', $row[7 + $offset] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':a26', $row[8 + $offset] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':a27', $row[9 + $offset] ?? '', PDO::PARAM_STR);
                $stmt->bindValue(':sec', is_numeric($secretariaIdExcel) ? intval($secretariaIdExcel) : null, PDO::PARAM_INT);
                $stmt->bindValue(':mun', $municipioAlcalde, PDO::PARAM_STR);

                if ($stmt->execute()) $inserted++;
                else $errors[] = "Fila " . ($i + 1) . ": Error en BD.";

            } catch (Exception $e) {
                $errors[] = "Fila " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        $db->closeConect();

        return array(
            'output' => array(
                'valid' => true,
                'message' => "Procesamiento completado.",
                'inserted' => $inserted,
                'skipped' => $skipped,
                'errors' => $errors
            )
        );

    } catch (Exception $e) {
        return array('output' => array('valid' => false, 'message' => "Error crítico: " . $e->getMessage()));
    }
}


    /**
      * Descarga la plantilla de Excel para Plan de Desarrollo
      * Carga la plantilla estática original y agrega hoja de secretarías
      * municipales filtrada según el rol del usuario
      * @return void
      */
    public static function downloadTemplate()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once 'SessionData.php';
        require_once 'DbConection.php';

        $vendorPath = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($vendorPath)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'PhpSpreadsheet no está instalado. Ejecuta "composer install".']);
            exit;
        }
        require_once $vendorPath;

        try {
            $rolUsuario = SessionData::getUserType();
            $esAdmin = in_array($rolUsuario, ['SuperAdministrador', 'Administrador']);
            $municipioUsuario = SessionData::getCodigoMunicipio();

            // Cargar la plantilla estática original para conservar su formato
            $templatePath = __DIR__ . '/../../SharedFiles/plan_alcalde.xlsx';
            if (file_exists($templatePath)) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
            } else {
                // Fallback: crear hoja básica si no existe la plantilla
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Plan Desarrollo Alcalde');

                $headers = [
                    'A1' => 'CÓDIGO MUNICIPIO',
                    'B1' => 'EJE ESTRATÉGICO',
                    'C1' => 'SECTOR PDD',
                    'D1' => 'SECTOR CATÁLOGO DE PRODUCTOS',
                    'E1' => 'PRODUCTO, BIEN O SERVICIO PDD',
                    'F1' => '2024',
                    'G1' => 'AVANCE 2024',
                    'H1' => 'AVANCE 2025',
                    'I1' => '2025',
                    'J1' => '2026',
                    'K1' => '2027',
                    'L1' => 'ID SECRETARÍA'
                ];

                foreach ($headers as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }

                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            }

            // ========== HOJA 2: Secretarías Municipales ==========
            $sheetSec = $spreadsheet->createSheet();
            $sheetSec->setTitle('Secretarías Municipales');

            // Encabezados de la hoja de secretarías
            $secHeaders = ['A1' => 'ID', 'B1' => 'MUNICIPIO', 'C1' => 'SECRETARÍA', 'D1' => 'SECRETARIO'];
            foreach ($secHeaders as $cell => $value) {
                $sheetSec->setCellValue($cell, $value);
                $sheetSec->getStyle($cell)->getFont()->setBold(true);
                $sheetSec->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheetSec->getStyle($cell)->getFill()->getStartColor()->setARGB('FF20427F');
                $sheetSec->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            }

            // Consultar secretarías según el rol
            $db = new DbConection();
            $pdo = $db->openConect();

            if ($esAdmin) {
                // Admin/SuperAdmin: todas las secretarías habilitadas
                $qSec = "SELECT sm.id, c.municipio, sm.secretaria, sm.secretario
                         FROM " . $db->getTable('tbl_secretarias_municipios') . " sm
                         LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                           ON sm.codigo_municipio COLLATE utf8mb3_unicode_ci = c.codigo_muncipio COLLATE utf8mb3_unicode_ci
                         WHERE sm.habilitado = 'si'
                         ORDER BY c.municipio ASC, sm.secretaria ASC";
                $stmtSec = $pdo->prepare($qSec);
            } else {
                // Alcalde y roles municipales: solo secretarías de su municipio
                $qSec = "SELECT sm.id, c.municipio, sm.secretaria, sm.secretario
                         FROM " . $db->getTable('tbl_secretarias_municipios') . " sm
                         LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c
                           ON sm.codigo_municipio COLLATE utf8mb3_unicode_ci = c.codigo_muncipio COLLATE utf8mb3_unicode_ci
                         WHERE sm.habilitado = 'si'
                           AND sm.codigo_municipio = :municipio
                         ORDER BY sm.secretaria ASC";
                $stmtSec = $pdo->prepare($qSec);
                $stmtSec->bindValue(':municipio', $municipioUsuario, PDO::PARAM_STR);
            }

            $stmtSec->execute();
            $secretarias = $stmtSec->fetchAll(PDO::FETCH_ASSOC);
            $db->closeConect();

            // Llenar datos de secretarías
            $row = 2;
            foreach ($secretarias as $sec) {
                $sheetSec->setCellValue('A' . $row, $sec['id']);
                $sheetSec->setCellValue('B' . $row, $sec['municipio'] ?? '');
                $sheetSec->setCellValue('C' . $row, $sec['secretaria'] ?? '');
                $sheetSec->setCellValue('D' . $row, $sec['secretario'] ?? '');
                $row++;
            }

            // Nota informativa en la hoja
            $notaRow = $row + 1;
            $sheetSec->setCellValue('A' . $notaRow, 'Use el valor de la columna ID en la columna "ID SECRETARÍA" de la hoja principal.');
            $sheetSec->getStyle('A' . $notaRow)->getFont()->setItalic(true);
            $sheetSec->getStyle('A' . $notaRow)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF666666'));
            $sheetSec->mergeCells('A' . $notaRow . ':D' . $notaRow);

            foreach (range('A', 'D') as $column) {
                $sheetSec->getColumnDimension($column)->setAutoSize(true);
            }

            // Activar la primera hoja al abrir el Excel
            $spreadsheet->setActiveSheetIndex(0);

            // Descargar
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="plantilla_plan_desarrollo_alcalde.xlsx"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (Exception $e) {
            error_log("Error generando plantilla: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error generando plantilla: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Obtiene datos paginados para DataTables server-side
     */
    public static function getAllServerSide($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once 'SessionData.php';
        $municipioAlcalde = SessionData::getCodigoMunicipio();
        $rolUsuario = SessionData::getUserType();
        $esAdmin = in_array($rolUsuario, ['SuperAdministrador', 'Administrador']);

        $db = new DbConection();
        $pdo = $db->openConect();

        $draw = isset($rqst['draw']) ? intval($rqst['draw']) : 1;
        $start = isset($rqst['start']) ? intval($rqst['start']) : 0;
        $length = isset($rqst['length']) ? intval($rqst['length']) : 10;
        $searchValue = isset($rqst['search']['value']) ? trim($rqst['search']['value']) : '';
        $orderColumn = isset($rqst['order'][0]['column']) ? intval($rqst['order'][0]['column']) : 0;
        $orderDir = isset($rqst['order'][0]['dir']) && strtolower($rqst['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';

        $filtroSectorPDD = isset($rqst['filtroSectorPDD']) ? trim($rqst['filtroSectorPDD']) : '';
        $filtroSectorCatalogo = isset($rqst['filtroSectorCatalogo']) ? trim($rqst['filtroSectorCatalogo']) : '';

        $orderColumns = [
            0 => 'pd.id',
            1 => 'pd.eje_estrategico',
            2 => 'pd.sector_pdd',
            3 => 'pd.sector_catalogo',
            4 => 'pd.producto_bien_servicio',
            5 => 'pd.anio_2024',
            6 => 'pd.avance_2024',
            7 => 'pd.avance_2025',
            8 => 'pd.anio_2025',
            9 => 'pd.anio_2026',
            10 => 'pd.anio_2027',
            11 => 's.secretaria'
        ];

        $offset = $esAdmin ? 1 : 0;
        $dataIndex = max(0, $orderColumn - $offset);
        $orderField = isset($orderColumns[$dataIndex]) ? $orderColumns[$dataIndex] : 'pd.id';

        $tableName = $db->getTable('tbl_plandesarrollo_alcalde');
        $secretariasTable = $db->getTable('tbl_secretarias_municipios');

        $where = [];
        $params = [];

        if (!$esAdmin) {
            $where[] = "pd.tbl_municipio_id = :municipio";
            $params[':municipio'] = $municipioAlcalde;
        }

        if (!empty($searchValue)) {
            $where[] = "(pd.eje_estrategico LIKE :search OR pd.sector_pdd LIKE :search2 OR pd.sector_catalogo LIKE :search3 OR pd.producto_bien_servicio LIKE :search4 OR s.secretaria LIKE :search5)";
            $like = "%{$searchValue}%";
            $params[':search'] = $like;
            $params[':search2'] = $like;
            $params[':search3'] = $like;
            $params[':search4'] = $like;
            $params[':search5'] = $like;
        }

        if (!empty($filtroSectorPDD)) {
            $where[] = "pd.sector_pdd = :sectorPDD";
            $params[':sectorPDD'] = $filtroSectorPDD;
        }

        if (!empty($filtroSectorCatalogo)) {
            $where[] = "pd.sector_catalogo = :sectorCatalogo";
            $params[':sectorCatalogo'] = $filtroSectorCatalogo;
        }

        $whereClause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

        $totalQ = "SELECT COUNT(*) FROM {$tableName} pd";
        $totalParams = [];

        if (!$esAdmin) {
            $totalQ .= " WHERE pd.tbl_municipio_id = :municipio_total";
            $totalParams[':municipio_total'] = $municipioAlcalde;
        }

        $stmtTotal = $pdo->prepare($totalQ);
        $stmtTotal->execute($totalParams);
        $recordsTotal = intval($stmtTotal->fetchColumn());

        $countQ = "SELECT COUNT(*) FROM {$tableName} pd LEFT JOIN {$secretariasTable} s ON pd.tbl_secretaria_id = s.id{$whereClause}";
        $stmt = $pdo->prepare($countQ);
        $stmt->execute($params);
        $recordsFiltered = intval($stmt->fetchColumn());

        $dataQ = "SELECT pd.*, s.secretaria
                  FROM {$tableName} pd
                  LEFT JOIN {$secretariasTable} s ON pd.tbl_secretaria_id = s.id
                  {$whereClause}
                  ORDER BY {$orderField} {$orderDir}
                  LIMIT :start, :length";
        $stmt = $pdo->prepare($dataQ);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':start', intval($start), PDO::PARAM_INT);
        $stmt->bindValue(':length', intval($length), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $db->closeConect();

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows ?: []
        ];
    }

    /**
     * Elimina múltiples registros del Plan de Desarrollo (solo Admin/SuperAdmin)
     * @param array $rqst Parámetros con array de IDs
     * @return array JSON con resultado
     */
    public static function deleteMultiple($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once 'SessionData.php';
        $rolUsuario = SessionData::getUserType();
        $esAdmin = in_array($rolUsuario, ['SuperAdministrador', 'Administrador']);

        if (!$esAdmin) {
            return array('output' => array('valid' => false, 'message' => 'No tienes permisos para eliminar registros'));
        }

        $ids = isset($rqst['ids']) ? $rqst['ids'] : [];
        if (empty($ids) || !is_array($ids)) {
            return array('output' => array('valid' => false, 'message' => 'No se proporcionaron IDs válidos'));
        }

        // Sanitizar: solo enteros positivos
        $ids = array_filter(array_map('intval', $ids), function($v) { return $v > 0; });
        $ids = array_values($ids);

        if (empty($ids)) {
            return array('output' => array('valid' => false, 'message' => 'No se proporcionaron IDs válidos'));
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $q = "DELETE FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . " WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($q);

        if ($stmt->execute($ids)) {
            $deleted = $stmt->rowCount();
            $arrjson = array('output' => array('valid' => true, 'message' => "Se eliminaron $deleted registro(s) correctamente", 'deleted' => $deleted));
        } else {
            $arrjson = array('output' => array('valid' => false, 'message' => 'Error al eliminar los registros'));
        }

        $db->closeConect();
        return $arrjson;
    }

    /**
     * Elimina un registro del Plan de Desarrollo
     * @param array $rqst Parámetros con el ID a eliminar
     * @return array JSON con resultado
     */
    public static function delete($rqst)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once 'SessionData.php';
        $municipioAlcalde = SessionData::getCodigoMunicipio();
        $rolUsuario = SessionData::getUserType();
        $esAdmin = in_array($rolUsuario, ['SuperAdministrador', 'Administrador']);

        if (empty($municipioAlcalde) && !$esAdmin) {
            return array('output' => array('valid' => false, 'message' => 'No se pudo obtener el municipio del alcalde'));
        }

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        if ($id <= 0) {
            return array('output' => array('valid' => false, 'message' => 'ID no válido'));
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        // Verificar que el registro existe (admins) o que pertenece al municipio del alcalde
        if ($esAdmin) {
            $qCheck = "SELECT id FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . "
                       WHERE id = :id";
            $stmtCheck = $pdo->prepare($qCheck);
            $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
        } else {
            $qCheck = "SELECT id FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . "
                       WHERE id = :id AND tbl_municipio_id = :municipio_id";
            $stmtCheck = $pdo->prepare($qCheck);
            $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtCheck->bindValue(':municipio_id', $municipioAlcalde);
        }
        $stmtCheck->execute();

        if ($stmtCheck->fetchColumn() === false) {
            $db->closeConect();
            return array('output' => array('valid' => false, 'message' => 'Registro no encontrado o no autorizado'));
        }

        // Eliminar el registro
        if ($esAdmin) {
            $q = "DELETE FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . "
                  WHERE id = :id";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        } else {
            $q = "DELETE FROM " . $db->getTable('tbl_plandesarrollo_alcalde') . "
                  WHERE id = :id AND tbl_municipio_id = :municipio_id";
            $stmt = $pdo->prepare($q);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':municipio_id', $municipioAlcalde);
        }

        if ($stmt->execute()) {
            $arrjson = array('output' => array('valid' => true, 'message' => 'Registro eliminado correctamente'));
        } else {
            $arrjson = array('output' => array('valid' => false, 'message' => 'Error al eliminar el registro'));
        }

        $db->closeConect();
        return $arrjson;
    }
}
