<?php
require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class Secretarias
{

    public function __construct() {}

    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $where = ["mostrar = 'si'"];
            $params = [];

            if ($id > 0) {
                $where[] = "id = :id";
                $params[':id'] = $id;
            }

            $query = "SELECT * FROM " . $db->getTable('tbl_secretarias') .
                " WHERE " . implode(' AND ', $where) . " and id > 0" .
                " ORDER BY secretaria ASC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = $data
                ? ['output' => ['valid' => true, 'response' => $data]]
                : Util::error_no_result();
        } catch (Exception $e) {
            $response = Util::error_general("Error al obtener secretarías: " . $e->getMessage());
        }

        if (isset($db)) {
            $db->closeConect();
        }

        return $response;
    }

    public static function load($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $draw = $data['draw'] ?? 1;
            $start = $data['start'] ?? 0;
            $length = $data['length'] ?? 10;
            $searchValue = $data['search']['value'] ?? '';
            $orderColumnIndex = $data['order'][0]['column'] ?? 0;
            $orderDirection = $data['order'][0]['dir'] ?? 'asc';

            $columns = [
                'secretaria',
                'secretario',
                'correo',
                'mostrar'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';

            $where = '';
            if (!empty($searchValue)) {
                $where = " WHERE secretaria LIKE :search OR correo LIKE :search OR secretario LIKE :search";
            }

            $table = $db->getTable('tbl_secretarias');

            // Total de registros sin filtro
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();
            $recordsTotal = $stmt->fetchColumn();

            // Total con filtro (si aplica)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table $where");
            if (!empty($searchValue)) {
                $stmt->bindValue(':search', '%' . $searchValue . '%');
            }
            $stmt->execute();
            $recordsFiltered = $stmt->fetchColumn();

            // Consulta de datos con orden y paginación
            $sql = "SELECT * FROM $table $where ORDER BY $orderColumn $orderDirection LIMIT :start, :length";
            $stmt = $pdo->prepare($sql);
            if (!empty($searchValue)) {
                $stmt->bindValue(':search', '%' . $searchValue . '%');
            }
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "draw" => intval($draw),
                "recordsTotal" => intval($recordsTotal),
                "recordsFiltered" => intval($recordsFiltered),
                "data" => $data,
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    public static function newSecretaria($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Validaciones
            $secretaria = trim($data['secretaria'] ?? '');
            $secretario = trim($data['secretario'] ?? '');
            $correo     = trim($data['correo'] ?? '');
            $mostrar    = trim($data['mostrar'] ?? '');

            if (empty($secretaria) || empty($secretario) || empty($correo) || empty($mostrar)) {
                return [
                    'state' => false,
                    'message' => 'Todos los campos son obligatorios.'
                ];
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return [
                    'state' => false,
                    'message' => 'El correo electrónico no es válido.'
                ];
            }

            // Verificar si el correo ya existe (opcional)
            $check = $pdo->prepare("SELECT COUNT(*) FROM " . $db->getTable('tbl_secretarias') . " WHERE correo = :correo");
            $check->bindParam(':correo', $correo);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return [
                    'state' => false,
                    'message' => 'El correo ya está registrado.'
                ];
            }

            // Insertar
            $stmt = $pdo->prepare("INSERT INTO " . $db->getTable('tbl_secretarias') . " (secretaria, secretario, correo, mostrar) VALUES (:secretaria, :secretario, :correo, :mostrar)");
            $stmt->bindParam(':secretaria', $secretaria);
            $stmt->bindParam(':secretario', $secretario);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':mostrar', $mostrar);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Secretaría registrada correctamente.'
            ];
        } catch (PDOException $e) {
            return [
                'state' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public static function editSecretaria($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            $id = $data ?? null;

            $sql = "SELECT * FROM " . $db->getTable('tbl_secretarias') . " 
                WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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

    public static function updateSecretaria($data)
    {
        try {
            $db = new DbConection();
            $pdo = $db->openConect();

            // Validar datos obligatorios
            if (
                empty($data['id']) ||
                empty($data['secretaria']) ||
                empty($data['secretario']) ||
                empty($data['correo']) ||
                empty($data['mostrar']) ||
                $data['mostrar'] === 'Seleccione'
            ) {
                return [
                    'state' => false,
                    'message' => 'Todos los campos son obligatorios.'
                ];
            }

            // Validar formato de correo
            if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'state' => false,
                    'message' => 'El correo electrónico no es válido.'
                ];
            }

            $sql = "UPDATE " . $db->getTable('tbl_secretarias') . " 
                SET secretaria = :secretaria,
                    secretario = :secretario,
                    correo = :correo,
                    mostrar = :mostrar
                WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':secretaria', $data['secretaria']);
            $stmt->bindParam(':secretario', $data['secretario']);
            $stmt->bindParam(':correo', $data['correo']);
            $stmt->bindParam(':mostrar', $data['mostrar']);
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);

            $stmt->execute();

            return [
                'state' => true,
                'message' => 'Secretaría actualizada correctamente.'
            ];
        } catch (PDOException $th) {
            return [
                'state' => false,
                'message' => 'Error de base de datos: ' . $th->getMessage()
            ];
        }
    }



    /**
     * Obtiene información específica de la Secretaría de Hacienda
     * Incluye totales económicos consolidados y conteo de proyectos por estado
     * Informe Secretaria Actividades, secretaria.php
     * Informacion de consolidado por año por estapilla en este caso 2025
     * @param array $rqst
     * @return array
     */
    public static function getInformacionSecretariaHacienda($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        $anio2025 = "2025";

        // Información de Hacienda del Año Actual
        $query = "SELECT
                SUM(incautacion_cigarrillos) AS total_incautacion_cigarrillos,
                SUM(incautacion_cerveza) AS total_incautacion_cervezas,
                SUM(incautacion_licores) AS total_incautacion_licores,
                SUM(incautacion_tabaco) AS total_incautacion_tabaco,

                SUM(COALESCE(incautacion_cigarrillos, 0) + COALESCE(incautacion_cerveza, 0) + COALESCE(incautacion_licores, 0) + COALESCE(incautacion_tabaco, 0)) AS TOTAL_UNIDADES,

                SUM(COALESCE(valor_cigarrillos, 0) + COALESCE(valor_cerveza, 0) + COALESCE(valor_licores, 0) + COALESCE(valor_tabaco, 0)) AS TOTAL_RECAUDO_CIG_CERV_LIC_TABAC,

                SUM(valor_recaudo_impuesto_vehicular) AS TOTAL_RECAUDO_IMPUESTO_VEHICULAR,
                SUM(valor_tramite_impuesto_vehicular) AS TOTAL_TRAMITES_IMPUESTO_VEHICULAR,
                SUM(valor_tramite_impuesto_vehicular + valor_recaudo_impuesto_vehicular) AS IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE,

                SUM(vehicular_cantidad_operativos)               AS TOTAL_VEHICULAR_OPERATIVOS,
                SUM(vehicular_cantidad_emplazados)               AS TOTAL_VEHICULAR_EMPLAZADOS,
                SUM(vehicular_cantidad_placas_consultadas)       AS TOTAL_VEHICULAR_PLACAS_CONSULTADAS,
                SUM(vehicular_cantidad_campanas_sensibilizacion) AS TOTAL_VEHICULAR_CAMPANAS_SENSIBILIZACION,

                SUM(CASE WHEN DATE(date) = CURDATE() - INTERVAL 1 DAY THEN valor_recaudo_impuesto_vehicular ELSE 0 END) AS TOTAL_RECAUDO_IMPUESTO_VEHICULAR_AYER,
                SUM(CASE WHEN DATE(date) = CURDATE() - INTERVAL 1 DAY THEN valor_tramite_impuesto_vehicular ELSE 0 END) AS TOTAL_TRAMITES_IMPUESTO_VEHICULAR_AYER,
                SUM(CASE WHEN DATE(date) = CURDATE() - INTERVAL 1 DAY THEN (valor_tramite_impuesto_vehicular + valor_recaudo_impuesto_vehicular) ELSE 0 END) AS IMPUESTO_VEHICULAR_TOTAL_RECAUDO_Y_TRAMITE_AYER,

                SUM(valor_tramite) AS TOTAL_RECAUDO_IMPUESTO_REGISTRO_TRAMITES,
                SUM(valor_recaudo) AS TOTAL_RECAUDO_IMPUESTO_REGISTRO,
                SUM(valor_tramite + valor_recaudo) AS TOTAL_RECAUDO_IMPUESTO_REGISTRO_Y_TRAMITE,

                SUM(valor_importado) AS TOTAL_RECAUDO_IMPUESTO_CONSUMO_IMPORTANDO,
                SUM(valor_nacional) AS TOTAL_RECAUDO_IMPUESTO_CONSUMO_NACIONAL,
                SUM(valor_nacional + valor_importado) AS TOTAL_RECAUDO_IMPUESTO_CONSUMO_IMPORTANDO_Y_NACIONAL,

                SUM(valor_estampilla) AS TOTAL_RECAUDO_ESTAMPILLA,

                (
                    (SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) THEN valor_recaudo_impuesto_vehicular ELSE 0 END) -
                    SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) - 1 THEN valor_recaudo_impuesto_vehicular ELSE 0 END)) /
                    NULLIF(SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) - 1 THEN valor_recaudo_impuesto_vehicular ELSE 0 END), 0)
                ) * 100 AS VARIACION_RECAUDO_IMPUESTO_VEHICULAR,

                (
                    (SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) THEN valor_tramite_impuesto_vehicular ELSE 0 END) -
                    SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) - 1 THEN valor_tramite_impuesto_vehicular ELSE 0 END)) /
                    NULLIF(SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) - 1 THEN valor_tramite_impuesto_vehicular ELSE 0 END), 0)
                ) * 100 AS VARIACION_TRAMITES_IMPUESTO_VEHICULAR,

                (
                    (SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) THEN valor_importado ELSE 0 END) -
                    SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) - 1 THEN valor_importado ELSE 0 END)) /
                    NULLIF(SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) - 1 THEN valor_importado ELSE 0 END), 0)
                ) * 100 AS VARIACION_RECAUDO_IMPORTADO_PORCENTAJE,

                (
                    (SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) THEN valor_nacional ELSE 0 END) -
                    SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) - 1 THEN valor_nacional ELSE 0 END)) /
                    NULLIF(SUM(CASE WHEN YEAR(date) = YEAR(CURDATE()) - 1 THEN valor_nacional ELSE 0 END), 0)
                ) * 100 AS VARIACION_RECAUDO_NACIONAL_PORCENTAJE

            FROM " . $db->getTable('tbl_hacienda');
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consolidado de las estampillas
        $queryConsolidadoEstampilla = "SELECT
            estampilla,
            Enero,
            Febrero,
            Marzo,
            Abril,
            Mayo,
            Junio,
            Julio,
            Agosto,
            Septiembre,
            Octubre,
            Noviembre,
            Diciembre,
            Total_Anual_Estampilla
        FROM
            (SELECT
                estampilla,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '01' THEN valor_estampilla ELSE 0 END) AS Enero,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '02' THEN valor_estampilla ELSE 0 END) AS Febrero,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '03' THEN valor_estampilla ELSE 0 END) AS Marzo,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '04' THEN valor_estampilla ELSE 0 END) AS Abril,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '05' THEN valor_estampilla ELSE 0 END) AS Mayo,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '06' THEN valor_estampilla ELSE 0 END) AS Junio,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '07' THEN valor_estampilla ELSE 0 END) AS Julio,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '08' THEN valor_estampilla ELSE 0 END) AS Agosto,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '09' THEN valor_estampilla ELSE 0 END) AS Septiembre,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '10' THEN valor_estampilla ELSE 0 END) AS Octubre,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '11' THEN valor_estampilla ELSE 0 END) AS Noviembre,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '12' THEN valor_estampilla ELSE 0 END) AS Diciembre,
                SUM(valor_estampilla) AS Total_Anual_Estampilla
            FROM
                " . $db->getTable('tbl_hacienda') . "
            WHERE
                valor_estampilla IS NOT NULL
                AND valor_estampilla > 0
                AND estampilla != ''
                AND DATE_FORMAT(date, '%Y') = '$anio2025'
            GROUP BY
                estampilla
            ORDER BY
                estampilla ASC
            ) AS subquery_estampillas 
        UNION ALL
        SELECT
            'TOTAL' AS estampilla, -- la fila de total general
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '01' THEN valor_estampilla ELSE 0 END) AS Enero,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '02' THEN valor_estampilla ELSE 0 END) AS Febrero,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '03' THEN valor_estampilla ELSE 0 END) AS Marzo,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '04' THEN valor_estampilla ELSE 0 END) AS Abril,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '05' THEN valor_estampilla ELSE 0 END) AS Mayo,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '06' THEN valor_estampilla ELSE 0 END) AS Junio,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '07' THEN valor_estampilla ELSE 0 END) AS Julio,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '08' THEN valor_estampilla ELSE 0 END) AS Agosto,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '09' THEN valor_estampilla ELSE 0 END) AS Septiembre,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '10' THEN valor_estampilla ELSE 0 END) AS Octubre,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '11' THEN valor_estampilla ELSE 0 END) AS Noviembre,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '12' THEN valor_estampilla ELSE 0 END) AS Diciembre,
            SUM(valor_estampilla) AS Total_Anual_Estampilla
        FROM
             " . $db->getTable('tbl_hacienda') . "
        WHERE
            valor_estampilla IS NOT NULL
            AND valor_estampilla > 0
            AND estampilla != ''
            AND DATE_FORMAT(date, '%Y') = '$anio2025' ";


        $stmtConsolidadoEstampilla = $pdo->prepare($queryConsolidadoEstampilla);
        $stmtConsolidadoEstampilla->execute();
        $estampillas = $stmtConsolidadoEstampilla->fetchAll(PDO::FETCH_ASSOC);


        // Totales por tipo_cigarrillo
        $queryCigarrillo = "SELECT
            tipo_cigarrillo,
            SUM(incautacion_cigarrillos) AS total_incautacion_cigarrillos,
            SUM(valor_cigarrillos) AS total_valor_cigarrillos
        FROM " . $db->getTable('tbl_hacienda') . "
        WHERE tipo_cigarrillo IS NOT NULL AND tipo_cigarrillo != ''
        GROUP BY tipo_cigarrillo
        ORDER BY tipo_cigarrillo";
        $stmtConsolidadoTipoCigarrillo = $pdo->prepare($queryCigarrillo);
        $stmtConsolidadoTipoCigarrillo->execute();
        $cigarrillos = $stmtConsolidadoTipoCigarrillo->fetchAll(PDO::FETCH_ASSOC);

        // Totales por tipo tabaco
        $queryTabaco = "SELECT
            tipo_tabaco,
            SUM(incautacion_tabaco) AS total_incautacion_tabaco,
            SUM(valor_tabaco) AS total_valor_tabaco
        FROM " . $db->getTable('tbl_hacienda') . "
        WHERE tipo_tabaco IS NOT NULL AND tipo_tabaco != ''
        GROUP BY tipo_tabaco
        ORDER BY tipo_tabaco";
        $stmtConsolidadoTabaco = $pdo->prepare($queryTabaco);
        $stmtConsolidadoTabaco->execute();
        $tabaco = $stmtConsolidadoTabaco->fetchAll(PDO::FETCH_ASSOC);


        // Totales por tipo Licores 
        $queryLicores = "SELECT
            tipo,
            SUM(incautacion_licores) AS total_incautacion_licores,
            SUM(valor_licores) AS total_valor_licores
        FROM " . $db->getTable('tbl_hacienda') . "
        WHERE tipo IS NOT NULL AND tipo != ''
        GROUP BY tipo
        ORDER BY tipo";
        $stmtConsolidadoLicores = $pdo->prepare($queryLicores);
        $stmtConsolidadoLicores->execute();
        $licores = $stmtConsolidadoLicores->fetchAll(PDO::FETCH_ASSOC);

        // Totales por tipo cerveza
        $queryCerveza = "SELECT
                tipo,
                SUM(incautacion_cerveza) AS total_incautacion_cerveza,
                SUM(valor_cerveza) AS total_valor_cerveza
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE tipo IS NOT NULL AND tipo != ''
            GROUP BY tipo
            ORDER BY tipo;";
        $stmtConsolidadoCerveza = $pdo->prepare($queryCerveza);
        $stmtConsolidadoCerveza->execute();
        $cerveza = $stmtConsolidadoCerveza->fetchAll(PDO::FETCH_ASSOC);

        // GOA
        // Totales por tipo GOA Aprehensiones de Licores"
        $queryGOALicores = "SELECT
                accion,
                SUM(cantidad_aprehendida) AS cantidad_aprehendida,
                SUM(avaluo_comercial) AS avaluo_comercial
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion IS NOT NULL AND accion = 'GOA Aprehensiones de Licores'
            GROUP BY accion
            ORDER BY accion;";
        $stmtGOALicores = $pdo->prepare($queryGOALicores);
        $stmtGOALicores->execute();
        $GOALicores = $stmtGOALicores->fetchAll(PDO::FETCH_ASSOC);

        // Totales por tipo GOA Aprehensión de Cigarrillos
        $queryGOACigarrillos = "SELECT
                accion,
                SUM(cantidad_aprehendida) AS cantidad_aprehendida,
                SUM(avaluo_comercial) AS avaluo_comercial
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion IS NOT NULL AND accion = 'GOA Aprehensión de Cigarrillos'
            GROUP BY accion
            ORDER BY accion;";

        $stmtGOACigarrillos = $pdo->prepare($queryGOACigarrillos);
        $stmtGOACigarrillos->execute();
        $GOACigarrillos = $stmtGOACigarrillos->fetchAll(PDO::FETCH_ASSOC);

        // Totales por accion GOA Aprehensión de Cervezas
        $queryGOACervezas = "SELECT
                accion,
                SUM(cantidad_aprehendida) AS cantidad_aprehendida,
                SUM(avaluo_comercial) AS avaluo_comercial
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion IS NOT NULL AND accion = 'GOA Aprehensión de Cervezas'
            GROUP BY accion
            ORDER BY accion;";
        $stmtGOACervezas = $pdo->prepare($queryGOACervezas);
        $stmtGOACervezas->execute();
        $GOACervezas = $stmtGOACervezas->fetchAll(PDO::FETCH_ASSOC);

        // Totales por accion GOA Aprehensión de Tabaco y Otros
        $queryGOATabaco = "SELECT
                accion,
                SUM(cantidad_aprehendida) AS cantidad_aprehendida,
                SUM(avaluo_comercial) AS avaluo_comercial
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion IS NOT NULL AND accion = 'GOA Aprehensión de Tabaco y Otros'
            GROUP BY accion
            ORDER BY accion;";
        $stmtGOATabaco = $pdo->prepare($queryGOATabaco);
        $stmtGOATabaco->execute();
        $GOATabaco = $stmtGOATabaco->fetchAll(PDO::FETCH_ASSOC);

        // Totales por accion Registro de Visitas a Establecimientos Comerciales
        $queryRegistroVisitas = "SELECT
                accion,
                SUM(cantidad_visitas_al_municipio) AS cantidad_visitas_al_municipio
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion IS NOT NULL AND accion = 'Registro de Visitas a Establecimientos Comerciales'
            GROUP BY accion
            ORDER BY accion;";
        $stmtRegistroVisitas = $pdo->prepare($queryRegistroVisitas);
        $stmtRegistroVisitas->execute();
        $registroVisitas = $stmtRegistroVisitas->fetchAll(PDO::FETCH_ASSOC);

        // Totales GOA Jurídico
        $queryGOAJuridico = "SELECT
                accion,
                SUM(goa_juridico_custodia_valor_total)          AS goa_juridico_custodia_valor_total,
                SUM(goa_juridico_custodia_cantidad_procesos)    AS goa_juridico_custodia_cantidad_procesos,
                SUM(goa_juridico_custodia_cantidad_unidades)    AS goa_juridico_custodia_cantidad_unidades,
                SUM(goa_juridico_destruccion_cantidad_unidades) AS goa_juridico_destruccion_cantidad_unidades,
                SUM(goa_juridico_destruccion_valor_total)       AS goa_juridico_destruccion_valor_total
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'GOA Juridico'
            GROUP BY accion;";
        $stmtGOAJuridico = $pdo->prepare($queryGOAJuridico);
        $stmtGOAJuridico->execute();
        $GOAJuridico = $stmtGOAJuridico->fetchAll(PDO::FETCH_ASSOC);

        $response = $data ? [
            'output' => ['valid' => true,
            'response' => $data,
            'cigarrillos' => $cigarrillos,
            'tabaco' => $tabaco,
            'licores' => $licores,
            'licores' => $licores,
            'cerveza' => $cerveza,
            'estampillas' => $estampillas,
            'GOALicores' => $GOALicores,
            'GOACigarrillos' => $GOACigarrillos,
            'GOACervezas' => $GOACervezas,
            'GOATabaco' => $GOATabaco,
            'registroVisitas' => $registroVisitas,
            'GOAJuridico' => $GOAJuridico
            ]
        ] : [];

        $db->closeConect();
        return $response;
    }

    /**
     * Obtiene el total de secretarías
     * Incluye totales económicos consolidados y conteo de proyectos por estado
     * Informe Secretaria Actividades, secretaria.php
     * @param array $rqst
     * @return array
     */
   public static function getTotalEjecucionSecretaria($rqst)
{
    $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;

    if ($secretariaId <= 0) {
        return Util::error_missing_data();
    }

    // Si es Secretaria Hacienda
    if ($secretariaId == Util::getSecretariaIdHacienda()) {
        return self::getInformacionSecretariaHacienda($rqst);
    }

    $db = new DbConection();
    $pdo = $db->openConect();

    // Consulta mejorada que cuenta TODOS los estados dinámicamente
    $query = "SELECT 
            tbl_proyectos.provincia, 
            tbl_secretarias.id AS secretaria_id, 
            tbl_secretarias.secretaria,

            SUM(tbl_proyectos.valor_proyecto) AS valor_proyecto_total, 
            SUM(tbl_proyectos.aporte_municipio) AS valor_municipio_total, 
            SUM(tbl_proyectos.aporte_nacion) AS valor_nacion_total, 
            SUM(tbl_proyectos.aporte_gobernacion) AS valor_departamento_total,

            -- Contar por cada estado posible
            SUM(CASE WHEN tbl_proyectos.estado = 'Suspendido' THEN 1 ELSE 0 END) AS suspendido,
            SUM(CASE WHEN tbl_proyectos.estado = 'Terminado' THEN 1 ELSE 0 END) AS terminado,
            SUM(CASE WHEN tbl_proyectos.estado = 'Terminado - NO liquidado' THEN 1 ELSE 0 END) AS terminado_sin_liquidar,
            SUM(CASE WHEN tbl_proyectos.estado = 'Liquidado' THEN 1 ELSE 0 END) AS terminado_liquidado,
            SUM(CASE WHEN tbl_proyectos.estado = 'Ejecutado' THEN 1 ELSE 0 END) AS ejecutado,
            SUM(CASE WHEN tbl_proyectos.estado = 'En Ejecución' THEN 1 ELSE 0 END) AS ejecucion,
            SUM(CASE WHEN tbl_proyectos.estado = 'En Contrataciòn' THEN 1 ELSE 0 END) AS en_contratacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'En Formulación' THEN 1 ELSE 0 END) AS en_formulacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'Entregado' THEN 1 ELSE 0 END) AS entregado,
            SUM(CASE WHEN tbl_proyectos.estado = 'Finalizado' THEN 1 ELSE 0 END) AS finalizado,
            SUM(CASE WHEN tbl_proyectos.estado = 'En Actualización' THEN 1 ELSE 0 END) AS actualizacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'Radicado en Banco de Proyectos' THEN 1 ELSE 0 END) AS radicado_banco,
            SUM(CASE WHEN tbl_proyectos.estado = 'Aprobado Banco de Proyectos' THEN 1 ELSE 0 END) AS aprobado_banco,
            SUM(CASE WHEN tbl_proyectos.estado = 'Estudios Previos' THEN 1 ELSE 0 END) AS estudios_previos,
            SUM(CASE WHEN tbl_proyectos.estado = 'En proceso de Giro' THEN 1 ELSE 0 END) AS proceso_giro,
            SUM(CASE WHEN tbl_proyectos.estado = 'Sin Iniciar' THEN 1 ELSE 0 END) AS sin_iniciar,
            SUM(CASE WHEN tbl_proyectos.estado = 'Pendiente Recursos' THEN 1 ELSE 0 END) AS pendiente_recursos,
            SUM(CASE WHEN tbl_proyectos.estado = 'En liquidacion' THEN 1 ELSE 0 END) AS liquidacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'Proyectos viabilizados en min salud' THEN 1 ELSE 0 END) AS viabilizado_minsalud,
            SUM(CASE WHEN tbl_proyectos.estado = 'Desistio del convenio' THEN 1 ELSE 0 END) AS desistio,
            SUM(CASE WHEN tbl_proyectos.estado = 'Radicación' THEN 1 ELSE 0 END) AS radicacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'Proceso Precontractual' THEN 1 ELSE 0 END) AS precontractual,

            -- Para estados no categorizados específicamente 
            SUM(CASE 
                WHEN LOWER(TRIM(tbl_proyectos.estado)) NOT IN (
                    'suspendido', 
                    'terminado', 
                    'Terminado - NO liquidado', 
                    'Liquidado',
                    'ejecutado', 
                    'en ejecución',
                    'en contratación',
                    'en formulación',
                    'entregado',
                    'finalizado',
                    'En Actualización',
                    'radicado en banco de proyectos', 
                    'aprobado banco de proyectos', 
                    'estudios previos', 
                    'en proceso de giro',
                    'sin iniciar', 
                    'pendiente recursos', 
                    'En liquidacion',
                    'proyectos viabilizados en min salud',
                    'Desistio del convenio',
                    'Radicación',
                    'Proceso Precontractual'
                ) THEN 1 ELSE 0
            END) AS otros_estados

        FROM 
            " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos

        LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada 
            ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio

        INNER JOIN " . $db->getTable('tbl_secretarias') . " AS tbl_secretarias 
            ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id

        WHERE
            tbl_secretarias.id = $secretariaId
        GROUP BY
            tbl_proyectos.provincia,
            tbl_secretarias.id,
            tbl_secretarias.secretaria

        UNION ALL

        SELECT 
            'Todos' AS provincia, 
            tbl_secretarias.id AS secretaria_id, 
            tbl_secretarias.secretaria,

            SUM(tbl_proyectos.valor_proyecto) AS valor_proyecto_total, 
            SUM(tbl_proyectos.aporte_municipio) AS valor_municipio_total, 
            SUM(tbl_proyectos.aporte_nacion) AS valor_nacion_total, 
            SUM(tbl_proyectos.aporte_gobernacion) AS valor_departamento_total,

            -- Mismos conteos para el total general
            SUM(CASE WHEN tbl_proyectos.estado = 'Suspendido' THEN 1 ELSE 0 END) AS suspendido,
            SUM(CASE WHEN tbl_proyectos.estado = 'Terminado' THEN 1 ELSE 0 END) AS terminado,
            SUM(CASE WHEN tbl_proyectos.estado = 'Terminado - NO liquidado' THEN 1 ELSE 0 END) AS terminado_sin_liquidar,
            SUM(CASE WHEN tbl_proyectos.estado = 'Liquidado' THEN 1 ELSE 0 END) AS terminado_liquidado,
            SUM(CASE WHEN tbl_proyectos.estado = 'Ejecutado' THEN 1 ELSE 0 END) AS ejecutado,
            SUM(CASE WHEN tbl_proyectos.estado = 'En Ejecución' THEN 1 ELSE 0 END) AS ejecucion,
            SUM(CASE WHEN tbl_proyectos.estado = 'En Contrataciòn' THEN 1 ELSE 0 END) AS en_contratacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'En Formulación' THEN 1 ELSE 0 END) AS en_formulacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'Entregado' THEN 1 ELSE 0 END) AS entregado,
            SUM(CASE WHEN tbl_proyectos.estado = 'Finalizado' THEN 1 ELSE 0 END) AS finalizado,
            SUM(CASE WHEN tbl_proyectos.estado = 'En Actualización' THEN 1 ELSE 0 END) AS actualizacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'Radicado en Banco de Proyectos' THEN 1 ELSE 0 END) AS radicado_banco,
            SUM(CASE WHEN tbl_proyectos.estado = 'Aprobado Banco de Proyectos' THEN 1 ELSE 0 END) AS aprobado_banco,
            SUM(CASE WHEN tbl_proyectos.estado = 'Estudios Previos' THEN 1 ELSE 0 END) AS estudios_previos,
            SUM(CASE WHEN tbl_proyectos.estado = 'En proceso de Giro' THEN 1 ELSE 0 END) AS proceso_giro,
            SUM(CASE WHEN tbl_proyectos.estado = 'Sin Iniciar' THEN 1 ELSE 0 END) AS sin_iniciar,
            SUM(CASE WHEN tbl_proyectos.estado = 'Pendiente Recursos' THEN 1 ELSE 0 END) AS pendiente_recursos,
            SUM(CASE WHEN tbl_proyectos.estado = 'En liquidacion' THEN 1 ELSE 0 END) AS liquidacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'Proyectos viabilizados en min salud' THEN 1 ELSE 0 END) AS viabilizado_minsalud,
            SUM(CASE WHEN tbl_proyectos.estado = 'Desistio del convenio' THEN 1 ELSE 0 END) AS desistio,
            SUM(CASE WHEN tbl_proyectos.estado = 'Radicación' THEN 1 ELSE 0 END) AS radicacion,
            SUM(CASE WHEN tbl_proyectos.estado = 'Proceso Precontractual' THEN 1 ELSE 0 END) AS precontractual,

            -- Para estados no categorizados
            SUM(CASE 
                WHEN LOWER(TRIM(tbl_proyectos.estado)) NOT IN (
                    'suspendido', 
                    'terminado', 
                    'Terminado - NO liquidado', 
                    'Liquidado',
                    'ejecutado', 
                    'en ejecución',
                    'en contratación',
                    'en formulación',
                    'entregado',
                    'finalizado',
                    'En Actualización',
                    'radicado en banco de proyectos', 
                    'aprobado banco de proyectos', 
                    'estudios previos', 
                    'en proceso de giro',
                    'sin iniciar', 
                    'pendiente recursos', 
                    'En liquidacion',
                    'proyectos viabilizados en min salud',
                    'Desistio del convenio',
                    'Radicación',
                    'Proceso Precontractual'

                ) THEN 1 ELSE 0
            END) AS otros_estados

        FROM 
            " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos

        LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " AS tbl_ciudades_accion_unificada 
            ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio

        INNER JOIN " . $db->getTable('tbl_secretarias') . " AS tbl_secretarias 
            ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id

        LEFT JOIN " . $db->getTable('tbl_proyectos_x_observaciones') . " AS tbl_proyectos_x_observaciones
            ON tbl_proyectos.id = tbl_proyectos_x_observaciones.tbl_proyecto_id
            
        WHERE  
            tbl_secretarias.id = $secretariaId

        GROUP BY 
            tbl_secretarias.id, 
            tbl_secretarias.secretaria
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = $data ? ['output' => ['valid' => true, 'response' => $data]] : [];

    $db->closeConect();
    return $response;
}

    public static function getDashboardSecretariaGraficas($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        // Consulta principal: estados de los proyectos agrupados por secretaría
        $query = " 
        SELECT
            tbl_secretarias.id AS secretaria_id,
            tbl_secretarias.secretaria,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Finalizado' THEN 1 END) AS finalizado,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Suspendido' THEN 1 END) AS suspendido,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Terminado' THEN 1 END) AS terminado,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Ejecutado' THEN 1 END) AS ejecutado,
            COUNT(CASE WHEN tbl_proyectos.estado = 'En Contrataciòn' THEN 1 END) AS en_contratacion,
            COUNT(CASE WHEN tbl_proyectos.estado = 'En Formulación' THEN 1 END) AS en_formulacion,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Entregado' THEN 1 END) AS entregado,
            COUNT(CASE
                WHEN tbl_proyectos.estado NOT IN (
                    'Finalizado', 
                    'Suspendido', 
                    'Terminado', 
                    'Ejecutado', 
                    'En Contrataciòn', 
                    'En Formulación', 
                    'Entregado'
                ) THEN 1 
            END) AS ejecucion
        FROM 
            " . $db->getTable('tbl_proyectos') . "
        INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "  
            ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
        INNER JOIN " . $db->getTable('tbl_secretarias') . "   
            ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
        GROUP BY
            tbl_secretarias.id, 
            tbl_secretarias.secretaria";


        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consulta secundaria: última fecha de observación por secretaría
        $qUltimaFecha = "SELECT 
            p.tbl_secretarias_id as secretaria_id,
            MAX(o.dtcreate) AS ultima_fecha
        FROM 
            " . $db->getTable('tbl_proyectos') . " p
        INNER JOIN 
            " . $db->getTable('tbl_proyectos_x_observaciones') . " o
            ON p.id = o.tbl_proyecto_id
        GROUP BY 
            p.tbl_secretarias_id";

        $stmtUltimaFecha = $pdo->prepare($qUltimaFecha);
        $stmtUltimaFecha->execute();
        $dataUltimaFecha = $stmtUltimaFecha->fetchAll(PDO::FETCH_ASSOC);

        // Mapeo de última fecha por secretaría
        $mapFechas = [];
        foreach ($dataUltimaFecha as $row) {
            $mapFechas[$row['secretaria_id']] = $row['ultima_fecha'];
        }

        // Agregar 'ultima_fecha' al arreglo principal
        foreach ($data as &$item) {
            $id = $item['secretaria_id'];
            $item['ultima_fecha'] = $mapFechas[$id] ?? null;
            switch ($id) {
                case 1:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=1';
                    break;
                case 2:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=2';
                    break;
                case 3:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=3';
                    break;
                case 4:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=4';
                    break;
                case 5:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=5';
                    break;
                case 6:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=6';
                    break;
                case 7:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=7';
                    break;
                case 8:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=8';
                    break;
                case 9:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=9';
                    break;
                case 10:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=10';
                    break;
                case 11:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=11';
                    break;
                case 12:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=12';
                    break;
                case 13:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=13';
                    break;
                case 14:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=14';
                    break;
                case 15:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=15';
                    break;
                case 16:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=16';
                    break;
                default:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=' . $id;
                    break;
            }
        }

        $response = $data
            ? ['output' => ['valid' => true, 'response' => $data]]
            : Util::error_no_result();

        $db->closeConect();
        return $response;
    }

    public static function getDashboardSecretariaGraficasPorSecretariaUsuarioLogueado($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $isSecretario = (SessionData::getUserType() === Util::Secretario_Despacho() || SessionData::getUserType()=== Util::Auxiliar()|| SessionData::getUserType() == Util::Auxiliar_secret_gob());
        $secretariaId = SessionData::getSecretaria();

        // Consulta principal: estados de los proyectos agrupados por secretaría
        $query = "
        SELECT
            tbl_secretarias.id AS secretaria_id,
            tbl_secretarias.secretaria,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Finalizado' THEN 1 END) AS finalizado,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Suspendido' THEN 1 END) AS suspendido,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Terminado' THEN 1 END) AS terminado,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Ejecutado' THEN 1 END) AS ejecutado,
            COUNT(CASE WHEN tbl_proyectos.estado = 'En Contrataciòn' THEN 1 END) AS en_contratacion,
            COUNT(CASE WHEN tbl_proyectos.estado = 'En Formulación' THEN 1 END) AS en_formulacion,
            COUNT(CASE WHEN tbl_proyectos.estado = 'Entregado' THEN 1 END) AS entregado,
            COUNT(CASE
                WHEN tbl_proyectos.estado NOT IN (
                    'Finalizado', 
                    'Suspendido', 
                    'Terminado', 
                    'Ejecutado', 
                    'En Contrataciòn', 
                    'En Formulación', 
                    'Entregado'
                ) THEN 1 
            END) AS ejecucion
        FROM 
            " . $db->getTable('tbl_proyectos') . "
        INNER JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . "  
            ON tbl_proyectos.tbl_municipio_id = tbl_ciudades_accion_unificada.codigo_muncipio
        INNER JOIN " . $db->getTable('tbl_secretarias') . "   
            ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
        ";

        if($isSecretario){
            $query .= " WHERE tbl_secretarias.id = $secretariaId";
        }

        $query .= " GROUP BY
            tbl_secretarias.id,
            tbl_secretarias.secretaria";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consulta secundaria: última fecha de observación por secretaría
        $qUltimaFecha = "SELECT 
            p.tbl_secretarias_id as secretaria_id,
            MAX(o.dtcreate) AS ultima_fecha
        FROM 
            " . $db->getTable('tbl_proyectos') . " p
        INNER JOIN 
            " . $db->getTable('tbl_proyectos_x_observaciones') . " o
            ON p.id = o.tbl_proyecto_id
        GROUP BY 
            p.tbl_secretarias_id";

        $stmtUltimaFecha = $pdo->prepare($qUltimaFecha);
        $stmtUltimaFecha->execute();
        $dataUltimaFecha = $stmtUltimaFecha->fetchAll(PDO::FETCH_ASSOC);

        // Mapeo de última fecha por secretaría
        $mapFechas = [];
        foreach ($dataUltimaFecha as $row) {
            $mapFechas[$row['secretaria_id']] = $row['ultima_fecha'];
        }

        // Agregar 'ultima_fecha' al arreglo principal
        foreach ($data as &$item) {
            $id = $item['secretaria_id'];
            $item['ultima_fecha'] = $mapFechas[$id] ?? null;
            switch ($id) {
                case 1:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=1';
                    break;
                case 2:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=2';
                    break;
                case 3:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=3';
                    break;
                case 4:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=4';
                    break;
                case 5:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=5';
                    break;
                case 6:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=6';
                    break;
                case 7:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=7';
                    break;
                case 8:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=8';
                    break;
                case 9:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=9';
                    break;
                case 10:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=10';
                    break;
                case 11:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=11';
                    break;
                case 12:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=12';
                    break;
                case 13:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=13';
                    break;
                case 14:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=14';
                    break;
                case 15:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=15';
                    break;
                case 16:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=16';
                    break;
                default:
                    $item['url'] = 'secretaria.php?depto_id=21&secretaria=' . $id;
                    break;
            }
        }

        $response = $data
            ? ['output' => ['valid' => true, 'response' => $data]]
            : [];

        $db->closeConect();
        return $response;
    }

    public static function getDashboardSecretariaGraficasHacienda($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();

        $anio2025 = "2025";

        $query = "SELECT 'Capacitación Fiscal y Financiera' AS accion, IFNULL(SUM(cantidad_personas), 0) AS cantidad
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Capacitacion Fiscal y Financiera'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING cantidad > 0

            UNION ALL

            SELECT 'Licores Incautados' AS accion, IFNULL(SUM(incautacion_licores), 0) AS cantidad
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Operativos Contrabando licores'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING cantidad > 0

            UNION ALL

            SELECT 'Cervezas Incautadas' AS accion, IFNULL(SUM(incautacion_cerveza), 0) AS cantidad
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Operativos Contrabando cerveza'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING cantidad > 0

            UNION ALL

            SELECT 'Cigarrillos Incautados' AS accion, IFNULL(SUM(incautacion_cigarrillos), 0) AS cantidad
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Operativos Contrabando cigarrillos'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING cantidad > 0

            UNION ALL

            SELECT 'Tabacos Incautados' AS accion, IFNULL(SUM(incautacion_tabaco), 0) AS cantidad
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Operativos Contrabando cigarrillos'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING cantidad > 0; ";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $queryValores = "
            SELECT 'Valor en Licores Incautados' AS accion, IFNULL(SUM(valor_licores), 0) AS valor
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Operativos Contrabando licores'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING valor > 0

            UNION ALL

            SELECT 'Valor en Cervezas Incautadas' AS accion, IFNULL(SUM(valor_cerveza), 0) AS valor
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Operativos Contrabando cerveza'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING valor > 0

            UNION ALL

            SELECT 'Recaudo en Impuesto Vehicular' AS accion, IFNULL(SUM(valor_recaudo_impuesto_vehicular), 0) AS valor
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Impuesto Vehicular Recaudado'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING valor > 0

            UNION ALL

            SELECT 'Recaudo en Impuesto Vehicular Por Trámite' AS accion, IFNULL(SUM(valor_tramite_impuesto_vehicular), 0) AS valor
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Impuesto Vehicular Recaudado'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING valor > 0

            UNION ALL

            SELECT 'Recaudo en Estampilla' AS accion, IFNULL(SUM(valor_estampilla), 0) AS valor
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Impuesto Estampillas Recaudado'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING valor > 0

            UNION ALL

            SELECT 'Recaudo impuesto al consumo' AS accion, IFNULL(SUM(valor_importado + valor_nacional), 0) AS valor
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Recaudo del impuesto al consumo'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING valor > 0

            UNION ALL

            SELECT 'Valor en Cigarrillos Incautados' AS accion, IFNULL(SUM(valor_cigarrillos), 0) AS valor
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Operativos Contrabando cigarrillos'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING valor > 0

            UNION ALL

            SELECT 'Valor en Tabacos Incautados' AS accion, IFNULL(SUM(valor_tabaco), 0) AS valor
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE accion = 'Operativos Contrabando cigarrillos'
            AND YEAR(dtcreate_at) = YEAR(CURDATE())
            HAVING valor > 0; ";

        $stmtValores = $pdo->prepare($queryValores);
        $stmtValores->execute();
        $dataValores = $stmtValores->fetchAll(PDO::FETCH_ASSOC);


        // Consolidado de las estampillas
        $queryConsolidadoEstampilla = "SELECT
            estampilla,
            Enero,
            Febrero,
            Marzo,
            Abril,
            Mayo,
            Junio,
            Julio,
            Agosto,
            Septiembre,
            Octubre,
            Noviembre,
            Diciembre,
            Total_Anual_Estampilla
        FROM
            (SELECT
                estampilla,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '01' THEN valor_estampilla ELSE 0 END) AS Enero,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '02' THEN valor_estampilla ELSE 0 END) AS Febrero,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '03' THEN valor_estampilla ELSE 0 END) AS Marzo,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '04' THEN valor_estampilla ELSE 0 END) AS Abril,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '05' THEN valor_estampilla ELSE 0 END) AS Mayo,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '06' THEN valor_estampilla ELSE 0 END) AS Junio,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '07' THEN valor_estampilla ELSE 0 END) AS Julio,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '08' THEN valor_estampilla ELSE 0 END) AS Agosto,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '09' THEN valor_estampilla ELSE 0 END) AS Septiembre,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '10' THEN valor_estampilla ELSE 0 END) AS Octubre,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '11' THEN valor_estampilla ELSE 0 END) AS Noviembre,
                SUM(CASE WHEN DATE_FORMAT(date, '%m') = '12' THEN valor_estampilla ELSE 0 END) AS Diciembre,
                SUM(valor_estampilla) AS Total_Anual_Estampilla
            FROM
                " . $db->getTable('tbl_hacienda') . "
            WHERE
                valor_estampilla IS NOT NULL
                AND valor_estampilla > 0
                AND estampilla != ''
                AND DATE_FORMAT(date, '%Y') = '$anio2025'
            GROUP BY
                estampilla
            ORDER BY
                estampilla ASC
            ) AS subquery_estampillas 
        UNION ALL
        SELECT
            'TOTAL' AS estampilla, -- la fila de total general
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '01' THEN valor_estampilla ELSE 0 END) AS Enero,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '02' THEN valor_estampilla ELSE 0 END) AS Febrero,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '03' THEN valor_estampilla ELSE 0 END) AS Marzo,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '04' THEN valor_estampilla ELSE 0 END) AS Abril,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '05' THEN valor_estampilla ELSE 0 END) AS Mayo,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '06' THEN valor_estampilla ELSE 0 END) AS Junio,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '07' THEN valor_estampilla ELSE 0 END) AS Julio,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '08' THEN valor_estampilla ELSE 0 END) AS Agosto,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '09' THEN valor_estampilla ELSE 0 END) AS Septiembre,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '10' THEN valor_estampilla ELSE 0 END) AS Octubre,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '11' THEN valor_estampilla ELSE 0 END) AS Noviembre,
            SUM(CASE WHEN DATE_FORMAT(date, '%m') = '12' THEN valor_estampilla ELSE 0 END) AS Diciembre,
            SUM(valor_estampilla) AS Total_Anual_Estampilla
        FROM
            " . $db->getTable('tbl_hacienda') . "
        WHERE
            valor_estampilla IS NOT NULL
            AND valor_estampilla > 0
            AND estampilla != ''
            AND DATE_FORMAT(date, '%Y') = '$anio2025' ";


        $stmtConsolidadoEstampilla = $pdo->prepare($queryConsolidadoEstampilla);
        $stmtConsolidadoEstampilla->execute();
        $estampillas = $stmtConsolidadoEstampilla->fetchAll(PDO::FETCH_ASSOC);

        $response = $data
            ? ['output' => ['valid' => true, 'response' => $data, 'estampillas' => $estampillas, 'dataValores' => $dataValores]]
            : [];

        $db->closeConect();
        return $response;
    }




    public static function getAllproyectosOLD($rqst)
    {
        // Parámetros de entrada
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tipoUsuario = SessionData::getUserType();
        $codigoMunicipio = SessionData::getCodigoMunicipio();


        // Inicializar conexión
        $db = new DbConection();
        $pdo = $db->openConect();

        // Base de la consulta
        $q = "SELECT tbl_secretarias.id AS tbl_secretarias_id, 
                    tbl_secretarias.secretaria, 
                    SUM(tbl_proyectos.valor_proyecto) AS sumaproyectos
            FROM " . $db->getTable('tbl_proyectos') . " 
            INNER JOIN " . $db->getTable('tbl_secretarias') . " 
            ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
            WHERE 1=1";

        // Parámetros dinámicos
        $params = [];
        if ($id > 0) {
            $q .= " AND tbl_secretarias.id = :id";
            $params[':id'] = $id;
        }

        // Si es alcalde o auxiliar de alcalde
        if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario) {
            $q .= " AND tbl_proyectos.tbl_municipio_id = :codigo_muncipio";
            $params[':codigo_muncipio'] = $codigoMunicipio;
        }
        // Si es secretario de despacho
        if (Util::Secretario_Despacho() == $tipoUsuario) {
            $q .= " AND tbl_secretarias.id = :id";
            $params[':id'] = SessionData::getSecretaria();
        }

        // Agrupar resultados
        $q .= " GROUP BY tbl_secretarias.secretaria";

        // Preparar y ejecutar consulta
        $stmt = $pdo->prepare($q);
        $stmt->execute($params);

        // Obtener resultados
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cerrar conexión
        $db->closeConect();

        // Devolver respuesta
        if ($data) {
            return [
                'output' => [
                    'valid' => true,
                    'response' => $data
                ]
            ];
        } else {
            return Util::error_no_result();
        }
    }

    public static function getAllproyectos($rqst)
    {
        // Parámetros de entrada
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $tipoUsuario = SessionData::getUserType();
        $codigoMunicipio = SessionData::getCodigoMunicipio();

        $isAdmin = ($tipoUsuario === Util::Administrador() || $tipoUsuario === Util::SuperAdministrador());
        $isSecretarioDespacho = ($tipoUsuario === Util::Secretario_Despacho() || $tipoUsuario === Util::Auxiliar()|| $$tipoUsuario == Util::Auxiliar_secret_gob());

        $db = new DbConection();
        $pdo = $db->openConect();


        $q = "SELECT 
                    tbl_secretarias.mostrar,
                    tbl_secretarias.id AS tbl_secretarias_id, 
                    tbl_secretarias.secretaria, 
                    IFNULL(SUM(tbl_proyectos.valor_proyecto), 0) AS sumaproyectos
            FROM " . $db->getTable('tbl_secretarias') . " 
            LEFT JOIN " . $db->getTable('tbl_proyectos') . " 
            ON tbl_proyectos.tbl_secretarias_id = tbl_secretarias.id
            WHERE 1=1";

        // Parámetros dinámicos
        $params = [];

        if ($id > 0) {
            $q .= " AND tbl_secretarias.id = :id";
            $params[':id'] = $id;
        }

        // Si es alcalde o auxiliar de alcalde, filtrar por municipio
        if (Util::Auxiliar_Alcalde() == $tipoUsuario || Util::Alcalde() == $tipoUsuario) {
            $q .= " AND tbl_proyectos.tbl_municipio_id = :codigo_muncipio";
            $params[':codigo_muncipio'] = $codigoMunicipio;
        }

        // Si es secretario de despacho o admin, no se filtra por secretaria
        if ($isAdmin) {
            // No se agrega filtro de `tbl_secretarias.id` si el usuario es secretario o admin
        } else {
            // Si es solo secretario de despacho o auxiliar, se filtra por su secretaria
            if ($isSecretarioDespacho) {
                $q .= " AND tbl_secretarias.id = :id";
                $params[':id'] = SessionData::getSecretaria();
            }
        }

        // Agrupar resultados por secretaria
        $q .= " GROUP BY tbl_secretarias.secretaria";

        // Preparar y ejecutar consulta
        $stmt = $pdo->prepare($q);
        $stmt->execute($params);

        // Obtener resultados
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cerrar conexión
        $db->closeConect();

        // Devolver respuesta
        if ($data) {
            return [
                'output' => [
                    'valid' => true,
                    'response' => $data
                ]
            ];
        } else {
            return Util::error_no_result();
        }
    }

    public static function save($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $secretaria = isset($rqst['secretaria']) ? trim($rqst['secretaria']) : '';
        $secretario = isset($rqst['secretario']) ? trim($rqst['secretario']) : '';
        $correo = isset($rqst['correo']) ? trim($rqst['correo']) : '';
        $tbl_usuario_id = $_SESSION['session_user']['id'] ?? 0;
        $image = isset($_SESSION['file']['nombrearchivo']) ? ($_SESSION['file']['nombrearchivo']) : '';

        if (!Util::validate_email($correo)) {
            return Util::error_general('El email no es correcto');
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                // Verifica si el registro existe antes de actualizar
                $q = "SELECT id FROM " . $db->getTable('tbl_secretarias') . " WHERE id = :id";
                $stmt = $pdo->prepare($q);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $table = $db->getTable('tbl_secretarias');
                    $arrfieldscomma = array(
                        'secretaria' => $secretaria,
                        'secretario' => $secretario,
                        'correo' => $correo,
                        'tbl_usuario_id' => $tbl_usuario_id,
                        'image' => $image
                    );
                    $arrfieldsnocomma = array('update_at' => Util::date_now_server());
                    $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);

                    $result = $pdo->query($q);
                    if (!$result) {
                        throw new Exception('Error actualizando los datos del secraetaría');
                    }

                    $arrjson = array('output' => array('valid' => true, 'id' => $id));
                } else {
                    throw new Exception('El registro no existe');
                }
            } else {
                if (!empty($secretaria)) {
                    // Inserta un nuevo registro
                    $q = "INSERT INTO " . $db->getTable('tbl_secretarias') . " (created_at, secretaria, secretario, correo, tbl_usuario_id, image)
                        VALUES (:created_at, :secretaria, :secretario, :correo, :tbl_usuario_id, :image)";
                    $stmt = $pdo->prepare($q);
                    $stmt->execute([
                        ':created_at' => Util::date_now_server(),
                        ':secretaria' => $secretaria,
                        ':secretario' => $secretario,
                        ':correo' => $correo,
                        ':tbl_usuario_id' => $tbl_usuario_id,
                        ':image' => $image
                    ]);

                    $arrjson = array('output' => array('valid' => true, 'response' => $pdo->lastInsertId()));
                } else {
                    throw new Exception('Faltan datos obligatorios');
                }
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $arrjson = Util::error_general($e->getMessage());
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }

    public static function calcularColorPorSecretaria($rqst)
    {
        $codigoDepartamento = isset($rqst['codigo_departamento']) ? intval($rqst['codigo_departamento']) : 0;
        $codigoMunicipio = isset($rqst['codigo_municipio']) ? intval($rqst['codigo_municipio']) : 0;
        $secretariaId = isset($rqst['secretariaId']) ? intval($rqst['secretariaId']) : 0;

        if ($codigoDepartamento == 0 || $codigoMunicipio == 0 || $secretariaId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();

        try {
            //puntaje por Pilar Id
            $puntajes = Util::sb_db_get("SELECT * FROM " . $db->getTable('tbl_puntajes_secretarias') . " WHERE tbl_secretaria_id = $secretariaId", false);

            $query = "";

            $cantidades = Util::sb_db_get($query, false);

            //color por defecto
            $colorDefecto = Util::getColorNeutroMapa();

            $puntajesVacios = isset($puntajes['output']['response']['code']) && $puntajes['output']['response']['code'] == 104;
            $puntajesValidos = !$puntajesVacios && is_array($puntajes);

            $resultado = [];
            foreach ($cantidades as $cantidad) {

                $color = $colorDefecto;

                if ($puntajesValidos) {
                    foreach ($puntajes as $puntaje) {
                        if ($cantidad['cantidad'] >= $puntaje['rango_desde'] && $cantidad['cantidad'] <= $puntaje['rango_hasta']) {
                            $color = $puntaje['color'];
                            break;
                        }
                    }
                }

                $veredaData = $cantidad;
                $veredaData['color_calculado'] = $color;

                $resultado[] = $veredaData;
            }

            $arrjson = ['output' => ['valid' => true, 'response' => $resultado]];
            return $arrjson;
        } catch (Exception $e) {
            return Util::error_general("Error generando los colores: " . $e->getMessage());
        } finally {
            $db->closeConect();
        }
    }
public static function consultarConsolidadoFactoresPorMunicipioAccion($arr)
{
    $municipioId  = intval($arr['municipioId'] ?? 0);
    $secretariaId = intval($arr['secretariaId'] ?? 0);
    $accion       = trim($arr['accion'] ?? '');

    if ($municipioId == 0 || $secretariaId == 0) {
        return Util::error_missing_data();
    }

    $db  = new DbConection();
    $pdo = $db->openConect(); 

    $tbl_ingreso       = $db->getTable('tbl_ingreso_informacion');
    $tbl_factores      = $db->getTable('tbl_factores');
    $tbl_actualizacion = $db->getTable('tbl_ingreso_informacion_x_actualizacion');

    try {

        // Subquery para obtener SOLO la última actualización por cada ingreso
        $subqueryUltimaAct = "
            SELECT x.tbl_ingreso_informacion_id,
                   x.valor_actualizacion
            FROM {$tbl_actualizacion} x
            INNER JOIN (
                SELECT tbl_ingreso_informacion_id, MAX(dtcreate) AS max_dt
                FROM {$tbl_actualizacion}
                GROUP BY tbl_ingreso_informacion_id
            ) u ON u.tbl_ingreso_informacion_id = x.tbl_ingreso_informacion_id
               AND u.max_dt = x.dtcreate
        ";

        $query = "
            SELECT
                SUM(i.valor_inicial) AS total_cantidad_inicial,

                SUM(
                    CASE 
                        WHEN ua.valor_actualizacion IS NOT NULL 
                             THEN ua.valor_actualizacion
                        ELSE i.valor_inicial
                    END
                ) AS total_cantidad_actual,

                f.tipo AS factor,
                f.tipo_medicion,
                f.icono,
                i.tbl_factor_id,

                MAX(i.fecha_modificacion) AS ultima_modificacion_factor

            FROM {$tbl_ingreso} i
            INNER JOIN {$tbl_factores} f ON f.id = i.tbl_factor_id

            LEFT JOIN ({$subqueryUltimaAct}) ua
              ON ua.tbl_ingreso_informacion_id = i.id

            WHERE 
                i.codigo_municipio = :municipioId
                AND f.tbl_secretaria_id = :secretariaId

            GROUP BY f.tipo, f.tipo_medicion, f.icono, i.tbl_factor_id
            HAVING SUM(i.valor_inicial) > 0

            ORDER BY f.tipo ASC
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':municipioId' => $municipioId,
            ':secretariaId' => $secretariaId
        ]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            return Util::error_no_result();
        }

        return [
            'output' => [
                'valid' => true,
                'response' => $result
            ]
        ];

    } catch (Exception $e) {

        return Util::error_general("Error consultando consolidado factores: " . $e->getMessage());

    } finally {
        $db->closeConect();
    }
}



    /**
     * Consulta el consolidado de factores SIN requerir la 'accion',
     * ideal para la carga inicial. Devuelve todos los factores que tienen actividad
     * para la secretaria/municipio, ordenados por la cantidad total.
     * @param array $arr Contiene 'municipioId', 'secretariaId'.
     * @return array Datos consolidados ordenados por mayor cantidad (el factor principal).
     */
    public static function getFactoresPrincipalesConsolidado($arr)
    {
        $municipioId = isset($arr['municipioId']) ? intval($arr['municipioId']) : 0;
        $secretariaId = isset($arr['secretariaId']) ? intval($arr['secretariaId']) : 0;
        
        // Validación mínima
        if ($municipioId == 0 || $secretariaId == 0) {
            return Util::error_missing_data();
        }

        $db = new DbConection();
        $pdo = $db->openConect(); 

        $tbl_actividades = $db->getTable('tbl_ingreso_informacion'); 
        $tbl_factores = $db->getTable('tbl_factores'); 
        
        
        try {

            $query = "
                SELECT
                    SUM(COALESCE(a.valor_inicial, a.valor)) AS total_cantidad,  
                    SUM(a.valor) AS total_cantidad_actual, 
                    f.tipo AS factor,               
                    f.tipo_medicion,                
                    f.icono,
                    a.tbl_factor_id,
                    f.tec_pilar_id AS tbl_pilar_id, 
                    MAX(a.fecha_modificacion) AS ultima_modificacion, 
                    MAX(a.dtcreate) AS fecha_ingreso 

                FROM 
                    {$tbl_actividades} a 
                INNER JOIN 
                    {$tbl_factores} f ON a.tbl_factor_id = f.id 
                WHERE 
                    a.codigo_municipio = :municipioId AND 
                    f.tbl_secretaria_id = :secretariaId
                GROUP BY
                    f.tipo, f.tipo_medicion, f.icono, a.tbl_factor_id, f.tec_pilar_id
                ORDER BY
                    total_cantidad DESC, f.tipo ASC
            ";

            $parametros = [
                ':municipioId' => $municipioId, 
                ':secretariaId' => $secretariaId,
            ];
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($parametros);
            $result_data = $stmt->fetchAll(PDO::FETCH_ASSOC);


            $respuesta = $result_data 
                ? ['output' => ['valid' => true, 'response' => $result_data]] 
                : Util::error_no_result(); 

            return $respuesta;

        } catch (Exception $e) {
            return Util::error_general("Error consultando factores principales: " . $e->getMessage());
        } finally {
            $db->closeConect(); 
        }
    } 

public static function getConsolidadoFactoresPorPilar($arr)
{
    $municipioId = isset($arr['municipioId']) ? intval($arr['municipioId']) : 0;
    $pilarId = isset($arr['pilarId']) ? intval($arr['pilarId']) : 0;

    if ($municipioId == 0) return Util::error_missing_data();

    $db = new DbConection();
    $pdo = $db->openConect();

    $tbl_ingreso       = $db->getTable('tbl_ingreso_informacion');
    $tbl_factores      = $db->getTable('tbl_factores');
    $tbl_actualizacion = $db->getTable('tbl_ingreso_informacion_x_actualizacion');

    $wherePilar = "";
    $params = [':municipioId' => $municipioId];

    if ($pilarId > 0) {
        $wherePilar = " AND f.tec_pilar_id = :pilarId ";
        $params[':pilarId'] = $pilarId;
    }

    try {

        // Última actualización por registro
        $subquery = "
            SELECT t1.tbl_ingreso_informacion_id, t1.valor_actualizacion
            FROM {$tbl_actualizacion} t1
            INNER JOIN (
                SELECT tbl_ingreso_informacion_id, MAX(dtcreate) AS max_dt
                FROM {$tbl_actualizacion}
                GROUP BY tbl_ingreso_informacion_id
            ) t2 ON t1.tbl_ingreso_informacion_id = t2.tbl_ingreso_informacion_id
                 AND t1.dtcreate = t2.max_dt
        ";

        // CONSULTA CORREGIDA
        $query = "
            SELECT
                /* CANTIDAD INICIAL CORRECTA */
                SUM(COALESCE(i.valor_inicial, i.valor, 0)) AS total_cantidad_inicial,

                /* CANTIDAD ACTUAL CORRECTA */
                SUM(COALESCE(a.valor_actualizacion, i.valor_inicial, i.valor, 0)) AS total_cantidad_actual,

                f.tipo AS factor,
                f.tipo_medicion,
                f.icono,
                i.tbl_factor_id
            FROM {$tbl_ingreso} i
            INNER JOIN {$tbl_factores} f ON i.tbl_factor_id = f.id
            LEFT JOIN ({$subquery}) a ON i.id = a.tbl_ingreso_informacion_id
            WHERE i.codigo_municipio = :municipioId
            {$wherePilar}
            GROUP BY f.tipo, f.tipo_medicion, f.icono, i.tbl_factor_id
            ORDER BY total_cantidad_actual DESC
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data
            ? ['output' => ['valid' => true, 'response' => $data]]
            : Util::error_no_result();

    } catch (Exception $e) {
        return Util::error_general("Error consultando factores por Pilar: " . $e->getMessage());
    } finally {
        $db->closeConect();
    }
}

    
    /** Consulta el consolidado de factores dado un Pilar en avances.php
    */
    public static function getFactoresPilarParaMapa($arr)
    {
        $municipioId = isset($arr['municipioId']) ? intval($arr['municipioId']) : 0;
        $pilarId = isset($arr['pilarId']) ? intval($arr['pilarId']) : 0; 
        
        if ($municipioId == 0 || $pilarId == 0) { 
            return Util::error_general("Se requiere Municipio ID y Pilar ID.");
        }

        $db = new DbConection();
        $pdo = $db->openConect(); 


        $tbl_actividades = $db->getTable('tbl_ingreso_informacion'); 
        $tbl_factores = $db->getTable('tbl_factores'); 
        $tbl_actualizaciones = $db->getTable('tbl_ingreso_informacion_x_actualizacion'); 
        
        try {

            $query = "
                SELECT
                    f.tipo AS factor,               
                    
                    SUM(a.valor) AS total_cantidad_inicial,
                    
                    SUM(IFNULL(t_actual.valor_actualizacion, a.valor)) AS total_cantidad_actual, 
                    
                    f.tipo_medicion,                
                    a.tbl_factor_id,
                    f.tec_pilar_id
                    
                FROM 
                    {$tbl_actividades} a
                INNER JOIN 
                    {$tbl_factores} f ON a.tbl_factor_id = f.id
                

                LEFT JOIN " . $tbl_actualizaciones . " t_actual
                    ON t_actual.id = (
                        SELECT id 
                        FROM " . $tbl_actualizaciones . " 
                        WHERE tbl_ingreso_informacion_id = a.id
                        ORDER BY dtcreate DESC, id DESC
                        LIMIT 1
                    )

                WHERE 
                    a.codigo_municipio = :municipioId 
                    AND f.tec_pilar_id = :pilarId
                    
                GROUP BY
                    f.tipo, f.tipo_medicion, a.tbl_factor_id, f.tec_pilar_id
                
                ORDER BY
                    total_cantidad_inicial DESC, f.tipo ASC
            ";

            $parametros = [
                ':municipioId' => $municipioId, 
                ':pilarId' => $pilarId,
            ];
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($parametros);
            $result_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2. Respuesta
            $respuesta = $result_data 
                ? ['output' => ['valid' => true, 'response' => $result_data]] 
                : Util::error_no_result(); 

            return $respuesta;

        } catch (Exception $e) {
            error_log("Error en getFactoresPilarParaMapa: " . $e->getMessage());
            return Util::error_general("Error consultando factores para mapa: " . $e->getMessage());
        } finally {
            $db->closeConect(); 
        }
    }

    /**
     * Obtiene el listado detallado de proyectos según el estado, y lo mete en modal
     * @param string $estado El estado del proyecto a filtrar.
     * @param int $secretariaId El ID de la secretaría.
     * @param string $provincia La provincia a filtrar ('Todos' para todas).
     * @return array La lista de proyectos.
     */
    public static function getListadoProyectosPorEstado($estado, $secretariaId, $provincia)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        
        try {

            $where = "tbl_proyectos.tbl_secretarias_id = :secretariaId AND tbl_proyectos.estado = :estado";
            $params = [
                ':secretariaId' => $secretariaId,
                ':estado' => $estado
            ];

            if ($provincia !== 'Todos') {

                $where .= " AND tbl_proyectos.provincia = :provincia";
                $params[':provincia'] = $provincia;
            }

            $query = "SELECT 
                        tbl_proyectos.id,
                        tbl_proyectos.nombre_proyecto,
                        tbl_proyectos.municipio,
                        tbl_proyectos.valor_proyecto
                      FROM " . $db->getTable('tbl_proyectos') . " AS tbl_proyectos
                      WHERE {$where}
                      ORDER BY tbl_proyectos.nombre_proyecto ASC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'state' => true,
                'data' => $data
            ];

        } catch (PDOException $th) {

            error_log("Error en getListadoProyectosPorEstado: " . $th->getMessage());
            return [
                'state' => false,
                'message' => 'Error de base de datos al obtener el listado: ' . $th->getMessage()
            ];
        } finally {
            if (isset($db)) {
                $db->closeConect();
            }
        }
    }


}
