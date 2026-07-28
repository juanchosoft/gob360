<?php

class Hacienda
{

    public function __construct() {}

    /**
     * Obtiene todos los registros de Hacienda o uno específico por ID.
     * @param array $rqst Parámetros de la solicitud (puede incluir 'id')
     * @return array Resultado de la consulta
     */
    public static function getAll($rqst)
    {
        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $table = $db->getTable('tbl_hacienda');
            if ($id > 0) {
                $q = "SELECT * FROM $table WHERE id = :id";
                $stmt = $pdo->prepare($q);
                $stmt->execute(['id' => $id]);
            } else {
                $q = "SELECT * FROM $table";
                $stmt = $pdo->query($q);
            }

            $arr = [];
            if ($stmt) {
                foreach ($stmt as $valor) {
                    $arr[] = $valor;
                }
                $arrjson = ['output' => ['valid' => true, 'response' => $arr]];
            } else {
                $arrjson = Util::error_no_result();
            }
        } catch (Exception $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    public static function getAllHaciendaByMunicipio($rqst)
    {
        $codigoMunicipio = isset($rqst['codigoMunicipio']) ? ($rqst['codigoMunicipio']) : 0;
        $accion = isset($rqst['accion']) ? ($rqst['accion']) : 0;

        $db = new DbConection();
        $pdo = $db->openConect();

        $accionesGOAUnificadas = [
            'GOA Aprehensiones de Licores',
            'GOA Aprehensión de Cigarrillos',
            'GOA Aprehensión de Cervezas',
            'GOA Aprehensión de Tabaco y Otros',
        ];

        try {
            $table = $db->getTable('tbl_hacienda');
            if ($codigoMunicipio > 0) {
                if ($accion === 'GOA - Aprehensiones') {
                    $placeholders = implode(',', array_fill(0, count($accionesGOAUnificadas), '?'));
                    $q = "SELECT * FROM $table WHERE tbl_municipio_id = ? AND accion IN ($placeholders) ORDER BY dtcreate_at DESC";
                    $stmt = $pdo->prepare($q);
                    $stmt->execute(array_merge([$codigoMunicipio], $accionesGOAUnificadas));
                } else {
                    $q = "SELECT * FROM $table WHERE tbl_municipio_id = :tbl_municipio_id AND accion = :accion ORDER BY dtcreate_at DESC";
                    $stmt = $pdo->prepare($q);
                    $stmt->execute(['tbl_municipio_id' => $codigoMunicipio, 'accion' => $accion]);
                }
                $arr = [];
                if ($stmt) {
                    foreach ($stmt as $valor) {
                        $arr[] = $valor;
                    }
                    $arrjson = ['output' => ['valid' => true, 'response' => $arr]];
                } else {
                    $arrjson = Util::error_no_result();
                }
            }else{
                $arrjson = Util::error_missing_data();
            }

        } catch (Exception $e) {
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    public static function getConsolidadoHacienda($rqst)
    {

        $db = new DbConection();
        $pdo = $db->openConect();

        $accion = $rqst['accion'] ?? '';
        $year = $rqst['year'] ?? date('Y');
        $groupBy = "accion";
        $selectExtra = "";
        $whereAccion = ""; // Filtro adicional

        if ($accion === 'Impuesto Estampillas Recaudado') {
            $groupBy .= ", estampilla";
            $selectExtra = ", estampilla";
            $whereAccion = "AND accion = 'Impuesto Estampillas Recaudado'";
        } else {
            // Si acción específica, puedes incluir este filtro también
            if (!empty($accion)) {
                $whereAccion = "AND accion = " . $pdo->quote($accion);
            }
        }

        $q = "
            SELECT 
                accion
                $selectExtra,
                SUM(incautacion_licores) AS total_incautacion_licores,
                SUM(valor_licores) AS total_valor_licores,
                SUM(incautacion_cigarrillos) AS total_incautacion_cigarrillos,
                SUM(valor_cigarrillos) AS total_valor_cigarrillos,
                SUM(incautacion_cerveza) AS total_incautacion_cerveza,
                SUM(valor_cerveza) AS total_valor_cerveza,
                SUM(valor_importado) AS total_valor_importado,
                SUM(valor_nacional) AS total_valor_nacional,
                SUM(valor_tramite) AS cantidad_tramite_impuesto_registro,
                SUM(valor_recaudo) AS total_valor_recaudo_impuesto_registro,
                SUM(valor_tramite_impuesto_vehicular) AS total_valor_tramite_impuesto_vehicular,
                SUM(valor_recaudo_impuesto_vehicular) AS total_valor_recaudo_impuesto_vehicular,
                SUM(valor_estampilla) AS total_valor_estampilla,
                SUM(capacitacion_programada) AS total_capacitacion_programada,
                SUM(cantidad_personas) AS total_cantidad_personas,
                MAX(dtcreate_at) AS ultima_fecha_registro
            FROM " . $db->getTable('tbl_hacienda') . "
            WHERE YEAR(dtcreate_at) = :year
            $whereAccion
            GROUP BY $groupBy
        ";

        // Ejecutar con prepare para mayor seguridad
        $stmt = $pdo->prepare($q);
        $stmt->execute(['year' => $year]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $arrjson = ['output' => ['valid' => true, 'response' => $result]];
        } else {
            $arrjson = Util::error_no_result();
        }
        $db->closeConect();
        return $arrjson;
    }

    /**
     * Guarda un registro de Hacienda
     * @param array $rqst Datos del formulario
     * @return array Resultado de la operación
     */
    public static function save($rqst)
    {

        $id = isset($rqst['id']) ? intval($rqst['id']) : 0;
        $valor_total = isset($rqst['valor_total']) ? floatval($rqst['valor_total']) : 0;

        // Puede venir como array o string
        $tbl_municipio_id = $rqst['tbl_municipio_id'] ?? '';

        // Validar campos requeridos
        $date = $rqst['date'] ?? '';
        $objeto = $rqst['objeto'] ?? '';
        $accion = $rqst['accion'] ?? '';
        $provincia = $rqst['provincia'] ?? '';
        $tbl_departamento_id = Util::getDepartamentoPrincipal();
        $secretaria = 'Hacienda';
        $cantidad = $rqst['cantidad'] ?? 0;

        $tipo = isset($rqst['tipo']) ? ($rqst['tipo']) : ''; // Tipo Nacional o Extrajero (Licores, Cerveza)
        $tipo_cigarrillo = isset($rqst['tipo_cigarrillo']) ? ($rqst['tipo_cigarrillo']) : ''; // Tipo Nacional o Extrajero 
        $tipo_tabaco = isset($rqst['tipo_tabaco']) ? ($rqst['tipo_tabaco']) : ''; // Tipo Nacional o Extrajero 

        // Licores
        $incautacion_licores = isset($rqst['incautacion_licores']) ? floatval($rqst['incautacion_licores']) : 0;
        $valor_licores = isset($rqst['valor_licores']) ? floatval($rqst['valor_licores']) : 0;

        // Cigarrillos
        $incautacion_cigarrillos = isset($rqst['incautacion_cigarrillos']) ? floatval($rqst['incautacion_cigarrillos']) : 0;
        $valor_cigarrillos = isset($rqst['valor_cigarrillos']) ? floatval($rqst['valor_cigarrillos']) : 0;

        // Tabaco
        $incautacion_tabaco = isset($rqst['incautacion_tabaco']) ? floatval($rqst['incautacion_tabaco']) : 0;
        $valor_tabaco = isset($rqst['valor_tabaco']) ? floatval($rqst['valor_tabaco']) : 0;

        // Cerveza
        $incautacion_cerveza = isset($rqst['incautacion_cerveza']) ? floatval($rqst['incautacion_cerveza']) : 0;
        $valor_cerveza = isset($rqst['valor_cerveza']) ? floatval($rqst['valor_cerveza']) : 0;

        // Recaudo del impuesto al consumo 
        $valor_importado = isset($rqst['valor_importado']) ? floatval($rqst['valor_importado']) : 0;
        $valor_nacional = isset($rqst['valor_nacional']) ? floatval($rqst['valor_nacional']) : 0;

        // Recaudo del impuesto de registro 
        $valor_tramite = isset($rqst['valor_tramite']) ? floatval($rqst['valor_tramite']) : 0;
        $valor_recaudo = isset($rqst['valor_recaudo']) ? floatval($rqst['valor_recaudo']) : 0;

        // Recaudo del impuesto vehicular
        $valor_tramite_impuesto_vehicular = isset($rqst['valor_tramite_impuesto_vehicular']) ? floatval($rqst['valor_tramite_impuesto_vehicular']) : 0;
        $valor_recaudo_impuesto_vehicular = isset($rqst['valor_recaudo_impuesto_vehicular']) ? floatval($rqst['valor_recaudo_impuesto_vehicular']) : 0;

        $estampilla = isset($rqst['estampilla']) ? ($rqst['estampilla']) : '';
        $valor_estampilla = isset($rqst['valor_estampilla']) ? floatval($rqst['valor_estampilla']) : 0;

        $cantidad_personas = isset($rqst['cantidad_personas']) ? floatval($rqst['cantidad_personas']) : 0;

        // GAO
        $cantidad_aprehendida = isset($rqst['cantidad_aprehendida']) ? floatval($rqst['cantidad_aprehendida']) : 0;
        $avaluo_comercial = isset($rqst['avaluo_comercial']) ? floatval($rqst['avaluo_comercial']) : 0;

        // visitas al municipio
        $cantidad_visitas_al_municipio = isset($rqst['cantidad_visitas_al_municipio']) ? intval($rqst['cantidad_visitas_al_municipio']) : 0;

        // Capacitaciones del GOA
        $tipo_capacitacion_goa = isset($rqst['tipo_capacitacion_goa']) ? ($rqst['tipo_capacitacion_goa']) : '';
        $numero_asistentes = isset($rqst['numero_asistentes']) ? intval($rqst['numero_asistentes']) : 0;


        $observaciones = $rqst['observaciones'] ?? '';
        $tbl_usuario_id = $_SESSION['session_user']['id'];
        $foto = $rqst['foto'] ?? '';

        // Nuevos campos
        $direccion = $rqst['direccion'] ?? '';
        $barrio = $rqst['barrio'] ?? '';
        $administrador = $rqst['administrador'] ?? '';
        $nombre_establecimiento = $rqst['nombre_establecimiento'] ?? '';
        $tipoe = $rqst['tipoe'] ?? '';
        $establecimiento = $rqst['establecimiento'] ?? '';

        // GOA Jurídico
        $goa_juridico_custodia_valor_total          = isset($rqst['goa_juridico_custodia_valor_total'])          ? floatval($rqst['goa_juridico_custodia_valor_total'])  : 0;
        $goa_juridico_custodia_cantidad_procesos    = isset($rqst['goa_juridico_custodia_cantidad_procesos'])    ? intval($rqst['goa_juridico_custodia_cantidad_procesos']) : 0;
        $goa_juridico_custodia_cantidad_unidades    = isset($rqst['goa_juridico_custodia_cantidad_unidades'])    ? intval($rqst['goa_juridico_custodia_cantidad_unidades']) : 0;
        $goa_juridico_destruccion_cantidad_unidades = isset($rqst['goa_juridico_destruccion_cantidad_unidades']) ? intval($rqst['goa_juridico_destruccion_cantidad_unidades']) : 0;
        $goa_juridico_destruccion_valor_total       = isset($rqst['goa_juridico_destruccion_valor_total'])       ? floatval($rqst['goa_juridico_destruccion_valor_total']) : 0;

        // Operativos Vehículos (Impuesto Vehicular Recaudado)
        $vehicular_cantidad_operativos              = isset($rqst['vehicular_cantidad_operativos'])              ? intval($rqst['vehicular_cantidad_operativos'])              : 0;
        $vehicular_cantidad_emplazados              = isset($rqst['vehicular_cantidad_emplazados'])              ? intval($rqst['vehicular_cantidad_emplazados'])              : 0;
        $vehicular_cantidad_placas_consultadas      = isset($rqst['vehicular_cantidad_placas_consultadas'])      ? intval($rqst['vehicular_cantidad_placas_consultadas'])      : 0;
        $vehicular_cantidad_campanas_sensibilizacion= isset($rqst['vehicular_cantidad_campanas_sensibilizacion'])? intval($rqst['vehicular_cantidad_campanas_sensibilizacion']) : 0;

        // Validar campos requeridos
        if ($accion === 'Impuesto Vehicular Recaudado' || $accion === 'Impuesto Estampillas Recaudado') {
            $tipo = '';
            // No validar municipio, establecer como NULL o 0
            $tbl_municipio_id = 0;
        } else {
            if (empty($date) || empty($accion) || empty($tbl_municipio_id)) {
            return Util::error_general(' "Tipo de Acción", "Fecha" y "Municipio" son requeridos.');
            }
        }

        if($accion === 'Operativos Contrabando licores') {
            if (empty($incautacion_licores) || empty($valor_licores) || empty($tipo)) {
                return Util::error_general('"Incautación Licores" , "Valor Incautación Licores" y "Tipo Licor" son requeridos para la acción "Operativos Contrabando licores".');
            }
        }

        if ($accion === 'Operativos Contrabando cigarrillos') {
            $tipo = '';
            if (empty($incautacion_cigarrillos) && empty($valor_cigarrillos) && empty($valor_tabaco) && empty($incautacion_tabaco)) {
                return Util::error_general(' Debe ingresar al menos alguna información para el tipo operación "Operativos Contrabando cigarrillos".');
            }
            // if (empty($incautacion_cigarrillos) || empty($valor_cigarrillos)) {
            //     return Util::error_general(' "Incautación Cigarrillos" y "Valor Cigarrillos" son requeridos para la acción "Operativos Contrabando cigarrillos".');
            // }
        }

        if ($accion === 'Operativos Contrabando cerveza') {
            if (empty($incautacion_cerveza) || empty($valor_cerveza) || empty($tipo)) {
                return Util::error_general(' "Incautación Cerveza" , "Valor Incautación Cerveza", "Tipo Cerveza" son requeridos para la acción "Operativos Contrabando cerveza".');
            }
        }

        if ($accion === 'Recaudo del impuesto al consumo') {
            $tipo = '';
            $tipo_tabaco = '';
            $tipo_cigarrillo = '';
            if (empty($valor_importado) && empty($valor_nacional)) {
                return Util::error_general(' "Valor Importado" y "Valor Nacional" al menos uno debe tener información, para la acción "Recaudo del impuesto al consumo".');
            }
        }

        if ($accion === 'Recaudo del impuesto de registro') {
            $tipo = '';
            if (empty($valor_tramite) || empty($valor_recaudo)) {
                return Util::error_general(' "Valor Tramite" y " Valor Recaudo" son requeridos, para la acción "Recaudo del impuesto de registro".');
            }
        }

        if ($accion !== 'Impuesto Vehicular Recaudado') {
            // Nulificar campos de Operativos Vehículos cuando la acción no corresponde
            $vehicular_cantidad_operativos               = 0;
            $vehicular_cantidad_emplazados               = 0;
            $vehicular_cantidad_placas_consultadas       = 0;
            $vehicular_cantidad_campanas_sensibilizacion = 0;
        }

        if ($accion === 'Impuesto Vehicular Recaudado') {
            $tipo_tabaco = '';
            $tipo_cigarrillo = '';
            $tipo = '';
            if (empty($valor_tramite_impuesto_vehicular) && empty($valor_recaudo_impuesto_vehicular)) {
                return Util::error_general(' "Valor Trámite" y "Valor Recaudo" al menos uno debe tener información, para la acción "Impuesto Vehicular Recaudado".');
            }
        }

        if($accion === 'Impuesto Estampillas Recaudado') {
            $tipo = '';
            $tipo_tabaco = '';
            $tipo_cigarrillo = '';
            if (empty($estampilla) || empty($valor_estampilla)) {
                return Util::error_general(' "Estampilla" y "Valor Estampilla Recaudado" son requeridos, para la acción "Impuesto Estampillas Recaudado".');
            }
        }

        if($accion === 'Capacitacion Fiscal y Financiera') {
            $tipo = '';
            $tipo_tabaco = '';
            $tipo_cigarrillo = '';
            if ( empty($cantidad_personas) ) {
                return Util::error_general(' "Personas Capacitadas" es requerido, para la acción "Capacitacion Fiscal y Financiera".');
            }
        }

        if($accion === 'GOA Aprehensiones de Licores' || $accion === 'GOA Aprehensión de Cigarrillos' || $accion === 'GOA Aprehensión de Cervezas' || $accion === 'GOA Aprehensión de Tabaco y Otros') {
            $tipo = '';
            $tipo_tabaco = '';
            $tipo_cigarrillo = '';
            if (empty($cantidad_aprehendida) || empty($avaluo_comercial)) {
                return Util::error_general('"Cantidad Aprehendida" y "Avalúo Comercial" son requeridos para las acciones GOA.');
            }
        }


        if($accion === 'Registro de Visitas a Establecimientos Comerciales') {
            $tipo = '';
            $tipo_tabaco = '';
            $tipo_cigarrillo = '';
            if (empty($cantidad_visitas_al_municipio)) {
                return Util::error_general('"Cantidad visitas al municipio" es requerido para las acciones GOA, Registro de Visitas a Establecimientos Comerciales.');
            }
        }

        if($accion === 'Registro de Capacitaciones del GOA') {
            $tipo = '';
            $tipo_tabaco = '';
            $tipo_cigarrillo = '';
            if (empty($tipo_capacitacion_goa) || empty($numero_asistentes)) {
                return Util::error_general('"Tipo de capacitación" y "Número de asistentes" son requeridos para la acción "Registro de Capacitaciones del GOA".');
            }
        }

        if ($accion === 'GOA Juridico') {
            // Los campos que no pertenecen a GOA Jurídico deben guardarse como NULL / vacío
            $tipoe                     = null;
            $establecimiento           = null;
            $direccion                 = '';
            $barrio                    = '';
            $administrador             = '';
            $nombre_establecimiento    = '';
            $cantidad_aprehendida      = 0;
            $avaluo_comercial          = 0;
            $cantidad_visitas_al_municipio = 0;
            $tipo_capacitacion_goa     = '';
            $numero_asistentes         = 0;
            $tipo                      = '';
            $tipo_tabaco               = '';
            $tipo_cigarrillo           = '';
            $incautacion_licores       = 0;
            $valor_licores             = 0;
            $incautacion_cigarrillos   = 0;
            $valor_cigarrillos         = 0;
            $incautacion_tabaco        = 0;
            $valor_tabaco              = 0;
            $incautacion_cerveza       = 0;
            $valor_cerveza             = 0;
            $valor_importado           = 0;
            $valor_nacional            = 0;
            $valor_tramite             = 0;
            $valor_recaudo             = 0;
            $valor_tramite_impuesto_vehicular  = 0;
            $valor_recaudo_impuesto_vehicular  = 0;
            $estampilla                = '';
            $valor_estampilla          = 0;
            $cantidad_personas         = 0;
        }

        // Normalizar municipios
        if (is_array($tbl_municipio_id)) {
            $ids_municipios = array_map('intval', $tbl_municipio_id);
        } else {
            $ids_municipios = [intval($tbl_municipio_id)];
        }

        $db = new DbConection();
        $pdo = $db->openConect();

        try {
            $pdo->beginTransaction();

            if ($id > 0) {
                // Actualizar un solo registro
                $q = "SELECT id FROM " . $db->getTable('tbl_hacienda') . " WHERE id = :id";
                $stmtCheck = $pdo->prepare($q);
                $stmtCheck->execute(['id' => $id]);
                if ($stmtCheck->fetch()) {
                    $table = $db->getTable('tbl_hacienda');
                    $arrfieldscomma = [
                        'tbl_municipio_id' => $ids_municipios[0], // Actualizar solo uno
                        'date' => $date,
                        'provincia' => $provincia,
                        'tbl_departamento_id' => $tbl_departamento_id,
                        'secretaria' => $secretaria,
                        'objeto' => $objeto,
                        'accion' => $accion,
                        'cantidad' => $cantidad,
                        'tipo' => $tipo, 
                        'tipo_cigarrillo' => $tipo_cigarrillo, 
                        'tipo_tabaco' => $tipo_tabaco, 
                        'incautacion_licores' => $incautacion_licores,
                        'valor_licores' => $valor_licores,
                        'incautacion_cigarrillos' => $incautacion_cigarrillos,
                        'valor_cigarrillos' => $valor_cigarrillos,
                        'incautacion_tabaco' => $incautacion_tabaco,
                        'valor_tabaco' => $valor_tabaco,
                        'incautacion_cerveza' => $incautacion_cerveza,
                        'valor_cerveza' => $valor_cerveza,
                        'cantidad_personas' => $cantidad_personas,
                        'observaciones' => $observaciones,
                        'tbl_usuario_id' => $tbl_usuario_id,
                        'valor_total' => $valor_total,
                        'foto' => $foto,
                        'valor_importado' => $valor_importado,
                        'valor_nacional' => $valor_nacional,
                        'valor_tramite' => $valor_tramite,
                        'valor_recaudo' => $valor_recaudo,
                        'valor_tramite_impuesto_vehicular' => $valor_tramite_impuesto_vehicular,
                        'valor_recaudo_impuesto_vehicular' => $valor_recaudo_impuesto_vehicular,
                        'estampilla' => $estampilla,
                        'valor_estampilla' => $valor_estampilla,
                        'cantidad_aprehendida' => $cantidad_aprehendida,
                        'avaluo_comercial' => $avaluo_comercial,
                        'cantidad_visitas_al_municipio' => $cantidad_visitas_al_municipio,
                        'tipo_capacitacion_goa' => $tipo_capacitacion_goa,
                        'numero_asistentes' => $numero_asistentes,

                        'barrio' => $barrio,
                        'direccion' => $direccion,
                        'administrador' => $administrador,
                        'nombre_establecimiento' => $nombre_establecimiento,
                        'tipoe' => $tipoe,
                        'establecimiento' => $establecimiento,

                        // GOA Jurídico
                        'goa_juridico_custodia_valor_total'          => $goa_juridico_custodia_valor_total,
                        'goa_juridico_custodia_cantidad_procesos'    => $goa_juridico_custodia_cantidad_procesos,
                        'goa_juridico_custodia_cantidad_unidades'    => $goa_juridico_custodia_cantidad_unidades,
                        'goa_juridico_destruccion_cantidad_unidades' => $goa_juridico_destruccion_cantidad_unidades,
                        'goa_juridico_destruccion_valor_total'       => $goa_juridico_destruccion_valor_total,

                        // Operativos Vehículos
                        'vehicular_cantidad_operativos'               => $vehicular_cantidad_operativos,
                        'vehicular_cantidad_emplazados'               => $vehicular_cantidad_emplazados,
                        'vehicular_cantidad_placas_consultadas'       => $vehicular_cantidad_placas_consultadas,
                        'vehicular_cantidad_campanas_sensibilizacion' => $vehicular_cantidad_campanas_sensibilizacion,
                    ];
                    $arrfieldsnocomma = ['dtcreate_at' => Util::date_now_server()];
                    $q = Util::make_query_update($table, "id = '$id'", $arrfieldscomma, $arrfieldsnocomma);
                    if ($pdo->query($q)) {
                        $pdo->commit();
                        $arrjson = ['output' => ['valid' => true, 'id' => $id]];
                    } else {
                        $pdo->rollBack();
                        $arrjson = Util::error_general('Error actualizando los datos de Hacienda');
                    }
                } else {
                    $pdo->rollBack();
                    $arrjson = Util::error_general('No se encontró el registro con ID ' . $id);
                }
            } else {

                // Insertar uno o varios registros
                $insertedIds = [];
                foreach ($ids_municipios as $municipio_id) {
                    $q = "INSERT INTO " . $db->getTable('tbl_hacienda') . " (
                    dtcreate_at, accion, cantidad, incautacion_licores, valor_licores, incautacion_cigarrillos, valor_cigarrillos, incautacion_cerveza, valor_cerveza,
                    cantidad_personas, observaciones, tbl_municipio_id,
                    date, secretaria, objeto, provincia, tbl_departamento_id, tbl_usuario_id, valor_total, foto, valor_importado, valor_nacional, valor_tramite, valor_recaudo,
                    valor_tramite_impuesto_vehicular, valor_recaudo_impuesto_vehicular, estampilla, valor_estampilla, incautacion_tabaco, valor_tabaco, tipo, tipo_cigarrillo, tipo_tabaco, cantidad_aprehendida, avaluo_comercial, cantidad_visitas_al_municipio, tipo_capacitacion_goa, numero_asistentes, barrio, direccion, administrador, nombre_establecimiento, tipoe, establecimiento,
                    goa_juridico_custodia_valor_total, goa_juridico_custodia_cantidad_procesos, goa_juridico_custodia_cantidad_unidades, goa_juridico_destruccion_cantidad_unidades, goa_juridico_destruccion_valor_total,
                    vehicular_cantidad_operativos, vehicular_cantidad_emplazados, vehicular_cantidad_placas_consultadas, vehicular_cantidad_campanas_sensibilizacion
                ) VALUES (
                    " . Util::date_now_server() . ",
                    :accion, :cantidad, :incautacion_licores, :valor_licores, :incautacion_cigarrillos, :valor_cigarrillos, :incautacion_cerveza, :valor_cerveza,
                    :cantidad_personas, :observaciones, :tbl_municipio_id,
                    :date, :secretaria, :objeto, :provincia, :tbl_departamento_id, :tbl_usuario_id, :valor_total, :foto, :valor_importado, :valor_nacional, :valor_tramite, :valor_recaudo,
                    :valor_tramite_impuesto_vehicular, :valor_recaudo_impuesto_vehicular, :estampilla, :valor_estampilla, :incautacion_tabaco, :valor_tabaco, :tipo, :tipo_cigarrillo, :tipo_tabaco, :cantidad_aprehendida, :avaluo_comercial, :cantidad_visitas_al_municipio, :tipo_capacitacion_goa, :numero_asistentes, :barrio, :direccion, :administrador, :nombre_establecimiento, :tipoe, :establecimiento,
                    :goa_juridico_custodia_valor_total, :goa_juridico_custodia_cantidad_procesos, :goa_juridico_custodia_cantidad_unidades, :goa_juridico_destruccion_cantidad_unidades, :goa_juridico_destruccion_valor_total,
                    :vehicular_cantidad_operativos, :vehicular_cantidad_emplazados, :vehicular_cantidad_placas_consultadas, :vehicular_cantidad_campanas_sensibilizacion
                )";
                    $stmt = $pdo->prepare($q);
                    $params = [
                        'accion' => $accion,
                        'cantidad' => $cantidad,
                        'incautacion_licores' => $incautacion_licores,
                        'valor_licores' => $valor_licores,
                        'incautacion_cigarrillos' => $incautacion_cigarrillos,
                        'valor_cigarrillos' => $valor_cigarrillos,
                        'incautacion_cerveza' => $incautacion_cerveza,
                        'valor_cerveza' => $valor_cerveza,
                        'cantidad_personas' => $cantidad_personas,
                        'observaciones' => $observaciones,
                        'tbl_municipio_id' => $municipio_id,
                        'date' => $date,
                        'secretaria' => $secretaria,
                        'objeto' => $objeto,
                        'provincia' => $provincia,
                        'tbl_departamento_id' => $tbl_departamento_id,
                        'tbl_usuario_id' => $tbl_usuario_id,
                        'valor_total' => $valor_total,
                        'foto' => $foto,
                        'valor_importado' => $valor_importado,
                        'valor_nacional' => $valor_nacional,
                        'valor_tramite' => $valor_tramite,
                        'valor_recaudo' => $valor_recaudo,
                        'valor_tramite_impuesto_vehicular' => $valor_tramite_impuesto_vehicular,
                        'valor_recaudo_impuesto_vehicular' => $valor_recaudo_impuesto_vehicular,
                        'estampilla' => $estampilla,
                        'valor_estampilla' => $valor_estampilla,
                        'incautacion_tabaco' => $incautacion_tabaco,
                        'valor_tabaco' => $valor_tabaco,
                        'tipo' => $tipo,
                        'tipo_cigarrillo' => $tipo_cigarrillo,
                        'tipo_tabaco' => $tipo_tabaco,
                        'cantidad_aprehendida' => $cantidad_aprehendida,
                        'avaluo_comercial' => $avaluo_comercial,
                        'cantidad_visitas_al_municipio' => $cantidad_visitas_al_municipio,
                        'tipo_capacitacion_goa' => $tipo_capacitacion_goa,
                        'numero_asistentes' => $numero_asistentes,

                        'barrio' => $barrio,
                        'direccion' => $direccion,
                        'administrador' => $administrador,
                        'nombre_establecimiento' => $nombre_establecimiento,
                        'tipoe' => $tipoe,
                        'establecimiento' => $establecimiento,

                        // GOA Jurídico
                        'goa_juridico_custodia_valor_total'          => $goa_juridico_custodia_valor_total,
                        'goa_juridico_custodia_cantidad_procesos'    => $goa_juridico_custodia_cantidad_procesos,
                        'goa_juridico_custodia_cantidad_unidades'    => $goa_juridico_custodia_cantidad_unidades,
                        'goa_juridico_destruccion_cantidad_unidades' => $goa_juridico_destruccion_cantidad_unidades,
                        'goa_juridico_destruccion_valor_total'       => $goa_juridico_destruccion_valor_total,

                        // Operativos Vehículos
                        'vehicular_cantidad_operativos'               => $vehicular_cantidad_operativos,
                        'vehicular_cantidad_emplazados'               => $vehicular_cantidad_emplazados,
                        'vehicular_cantidad_placas_consultadas'       => $vehicular_cantidad_placas_consultadas,
                        'vehicular_cantidad_campanas_sensibilizacion' => $vehicular_cantidad_campanas_sensibilizacion,
                    ];
                    if ($stmt->execute($params)) {
                        $insertedIds[] = $pdo->lastInsertId();
                    } else {
                        throw new Exception("Error al guardar datos del municipio ID $municipio_id");
                    }
                }
                $pdo->commit();
                $arrjson = ['output' => ['valid' => true, 'response' => $insertedIds]];
            }
        } catch (Exception $e) {

            print_r($e);
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $arrjson = Util::error_general($e->getMessage());
        }

        $db->closeConect();
        return $arrjson;
    }

    public static function getInformacionPorAccion($rqst)
    {
        $db = new DbConection();
        $pdo = $db->openConect();
        
        $accion = $rqst['accion'] ?? '';
        
        // Configuración de consultas por acción
        $configuracionConsultas = [
            'Operativos Contrabando cigarrillos' => [
                'campos' => [
                    'tipo_cigarrillo as tipo',
                    '(incautacion_cigarrillos) as total_incautacion',
                    '(valor_cigarrillos) as total_valor'
                ],
                'where' => "tipo_cigarrillo IS NOT NULL AND tipo_cigarrillo != ''",
                'groupBy' => 'tipo_cigarrillo',
                'keys' => ['cigarrillos']
            ],
            'Operativos Contrabando tabaco' => [
                'campos' => [
                    'tipo_tabaco as tipo',
                    '(incautacion_tabaco) as total_incautacion',
                    '(valor_tabaco) as total_valor'
                ],
                'where' => "tipo_tabaco IS NOT NULL AND tipo_tabaco != ''",
                'groupBy' => 'tipo_tabaco',
                'keys' => ['tabaco']
            ],
            'Operativos Contrabando licores' => [
                'campos' => [
                    'tipo',
                    '(incautacion_licores) as total_incautacion_licores',
                    '(valor_licores) as total_valor_licores'
                ],
                'where' => "tipo IS NOT NULL AND tipo != ''",
                'groupBy' => 'tipo',
                'keys' => ['licores']
            ],
            'Operativos Contrabando cerveza' => [
                'campos' => [
                    'tipo',
                    '(incautacion_cerveza) as total_incautacion_cerveza',
                    '(valor_cerveza) as total_valor_cerveza'
                ],
                'where' => "tipo IS NOT NULL AND tipo != ''",
                'groupBy' => 'tipo',
                'keys' => ['cerveza']
            ],
            'GOA Aprehensiones de Licores' => [
                'campos' => [
                    'accion',
                    '(cantidad_aprehendida) as cantidad_aprehendida',
                    '(avaluo_comercial) as avaluo_comercial'
                ],
                'where' => "accion = 'GOA Aprehensiones de Licores'",
                'groupBy' => 'accion',
                'keys' => ['GOALicores']
            ],
            'GOA Aprehensión de Cigarrillos' => [
                'campos' => [
                    'accion',
                    '(cantidad_aprehendida) as cantidad_aprehendida',
                    '(avaluo_comercial) as avaluo_comercial'
                ],
                'where' => "accion = 'GOA Aprehensión de Cigarrillos'",
                'groupBy' => 'accion',
                'keys' => ['GOACigarrillos']
            ],
            'GOA Aprehensión de Cervezas' => [
                'campos' => [
                    'accion',
                    '(cantidad_aprehendida) as cantidad_aprehendida',
                    '(avaluo_comercial) as avaluo_comercial'
                ],
                'where' => "accion = 'GOA Aprehensión de Cervezas'",
                'groupBy' => 'accion',
                'keys' => ['GOACervezas']
            ],
            'GOA Aprehensión de Tabaco y Otros' => [
                'campos' => [
                    'accion',
                    '(cantidad_aprehendida) as cantidad_aprehendida',
                    '(avaluo_comercial) as avaluo_comercial'
                ],
                'where' => "accion = 'GOA Aprehensión de Tabaco y Otros'",
                'groupBy' => 'accion',
                'keys' => ['GOATabaco']
            ],
            'Registro de Visitas a Establecimientos Comerciales' => [
                'campos' => [
                    'accion',
                    '(cantidad_visitas_al_municipio) as cantidad_visitas_al_municipio'
                ],
                'where' => "accion = 'Registro de Visitas a Establecimientos Comerciales'",
                'groupBy' => 'accion',
                'keys' => ['registroVisitas']
            ],
            'GOA Juridico' => [
                'campos' => [
                    'accion',
                    'goa_juridico_custodia_valor_total',
                    'goa_juridico_custodia_cantidad_procesos',
                    'goa_juridico_custodia_cantidad_unidades',
                    'goa_juridico_destruccion_cantidad_unidades',
                    'goa_juridico_destruccion_valor_total',
                ],
                'where' => "accion = 'GOA Juridico'",
                'keys' => ['GOAJuridico']
            ],
            'Impuesto Vehicular Recaudado' => [
                'campos' => [
                    'accion',
                    'valor_tramite_impuesto_vehicular',
                    'valor_recaudo_impuesto_vehicular',
                    'vehicular_cantidad_operativos',
                    'vehicular_cantidad_emplazados',
                    'vehicular_cantidad_placas_consultadas',
                    'vehicular_cantidad_campanas_sensibilizacion',
                ],
                'where' => "accion = 'Impuesto Vehicular Recaudado'",
                'keys' => ['vehicularRecaudado']
            ],
        ];
        
        $resultado = [];
        
        // Si hay una acción específica, ejecutar solo esa consulta
        if (!empty($accion) && isset($configuracionConsultas[$accion])) {

            $config = $configuracionConsultas[$accion];
            $datos = self::ejecutarConsultaDetallada($pdo, $db, $config);
            
            foreach ($config['keys'] as $key) {
                $resultado[$key] = $datos;
            }
        } else {
            $resultado = [];
        }
        
        $response = [
            'output' => array_merge(
                ['valid' => true],
                $resultado
            )
        ];

        
        
        $db->closeConect();
        return $response;
    }

    private static function ejecutarConsultaDetallada($pdo, $db, $config)
    {
        $campos = implode(', ', $config['campos']);
        
        $query = "SELECT 
                    {$campos},
                    h.*,
                    c.municipio,
                    c.codigo_muncipio,
                    c.codigo_departamento,
                    CONCAT(u.nombre, ' ', u.apellido) as usuario_completo,
                    u.email as usuario_email
                FROM " . $db->getTable('tbl_hacienda') . " h
                LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " c 
                    ON h.tbl_departamento_id = c.codigo_departamento 
                    AND h.tbl_municipio_id = c.codigo_muncipio
                LEFT JOIN " . $db->getTable('tbl_usuarios') . " u 
                    ON h.tbl_usuario_id = u.id
                WHERE {$config['where']}
                ORDER BY h.dtcreate_at DESC";
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en consulta detallada: " . $e->getMessage());
            return [];
        }
    }

    public static function getMunicipiosPlantilla(): array
    {
        $db  = new DbConection();
        $pdo = $db->openConect();
        $stmt = $pdo->query(
            "SELECT id, municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " ORDER BY municipio ASC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db->closeConect();
        return $rows;
    }

    public static function update($rqst)
    {
        $arrjson = ['output' => ['valid' => false, 'response' => 'Error al actualizar']];

        $id = (int)($rqst['id'] ?? 0);
        if ($id <= 0) {
            $arrjson['output']['response'] = 'ID inválido';
            return $arrjson;
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        try {
            $stmt = $pdo->prepare(
                "UPDATE " . $db->getTable('tbl_hacienda') . " SET
                    date                                         = :date,
                    objeto                                       = :objeto,
                    observaciones                                = :observaciones,
                    barrio                                       = :barrio,
                    direccion                                    = :direccion,
                    administrador                                = :administrador,
                    nombre_establecimiento                       = :nombre_establecimiento,
                    tipoe                                        = :tipoe,
                    establecimiento                              = :establecimiento,
                    cantidad_aprehendida                         = :cantidad_aprehendida,
                    avaluo_comercial                             = :avaluo_comercial,
                    cantidad_visitas_al_municipio                = :cantidad_visitas_al_municipio,
                    cantidad_personas                            = :cantidad_personas,
                    valor_importado                              = :valor_importado,
                    valor_nacional                               = :valor_nacional,
                    valor_tramite                                = :valor_tramite,
                    valor_recaudo                                = :valor_recaudo,
                    valor_tramite_impuesto_vehicular             = :valor_tramite_impuesto_vehicular,
                    valor_recaudo_impuesto_vehicular             = :valor_recaudo_impuesto_vehicular,
                    vehicular_cantidad_operativos                = :vehicular_cantidad_operativos,
                    vehicular_cantidad_emplazados                = :vehicular_cantidad_emplazados,
                    vehicular_cantidad_placas_consultadas        = :vehicular_cantidad_placas_consultadas,
                    vehicular_cantidad_campanas_sensibilizacion  = :vehicular_cantidad_campanas_sensibilizacion,
                    estampilla                                   = :estampilla,
                    valor_estampilla                             = :valor_estampilla,
                    goa_juridico_custodia_valor_total            = :goa_juridico_custodia_valor_total,
                    goa_juridico_custodia_cantidad_procesos      = :goa_juridico_custodia_cantidad_procesos,
                    goa_juridico_custodia_cantidad_unidades      = :goa_juridico_custodia_cantidad_unidades,
                    goa_juridico_destruccion_cantidad_unidades   = :goa_juridico_destruccion_cantidad_unidades,
                    goa_juridico_destruccion_valor_total         = :goa_juridico_destruccion_valor_total,
                    tipo_capacitacion_goa                        = :tipo_capacitacion_goa,
                    numero_asistentes                            = :numero_asistentes
                WHERE id = :id"
            );

            $stmt->execute([
                ':id'                                          => $id,
                ':date'                                        => $rqst['date']                                        ?? '',
                ':objeto'                                      => $rqst['objeto']                                      ?? '',
                ':observaciones'                               => $rqst['observaciones']                               ?? '',
                ':barrio'                                      => $rqst['barrio']                                      ?? '',
                ':direccion'                                   => $rqst['direccion']                                   ?? '',
                ':administrador'                               => $rqst['administrador']                               ?? '',
                ':nombre_establecimiento'                      => $rqst['nombre_establecimiento']                      ?? '',
                ':tipoe'                                       => $rqst['tipoe']                                       ?? '',
                ':establecimiento'                             => $rqst['establecimiento']                             ?? '',
                ':cantidad_aprehendida'                        => $rqst['cantidad_aprehendida']                        ?? 0,
                ':avaluo_comercial'                            => $rqst['avaluo_comercial']                            ?? 0,
                ':cantidad_visitas_al_municipio'               => $rqst['cantidad_visitas_al_municipio']               ?? 0,
                ':cantidad_personas'                           => $rqst['cantidad_personas']                           ?? 0,
                ':valor_importado'                             => $rqst['valor_importado']                             ?? 0,
                ':valor_nacional'                              => $rqst['valor_nacional']                              ?? 0,
                ':valor_tramite'                               => $rqst['valor_tramite']                               ?? 0,
                ':valor_recaudo'                               => $rqst['valor_recaudo']                               ?? 0,
                ':valor_tramite_impuesto_vehicular'            => $rqst['valor_tramite_impuesto_vehicular']            ?? 0,
                ':valor_recaudo_impuesto_vehicular'            => $rqst['valor_recaudo_impuesto_vehicular']            ?? 0,
                ':vehicular_cantidad_operativos'               => $rqst['vehicular_cantidad_operativos']               ?? 0,
                ':vehicular_cantidad_emplazados'               => $rqst['vehicular_cantidad_emplazados'  ]             ?? 0,
                ':vehicular_cantidad_placas_consultadas'       => $rqst['vehicular_cantidad_placas_consultadas']       ?? 0,
                ':vehicular_cantidad_campanas_sensibilizacion' => $rqst['vehicular_cantidad_campanas_sensibilizacion'] ?? 0,
                ':estampilla'                                  => $rqst['estampilla']                                  ?? '',
                ':valor_estampilla'                            => $rqst['valor_estampilla']                            ?? 0,
                ':goa_juridico_custodia_valor_total'           => $rqst['goa_juridico_custodia_valor_total']           ?? 0,
                ':goa_juridico_custodia_cantidad_procesos'     => $rqst['goa_juridico_custodia_cantidad_procesos']     ?? 0,
                ':goa_juridico_custodia_cantidad_unidades'     => $rqst['goa_juridico_custodia_cantidad_unidades']     ?? 0,
                ':goa_juridico_destruccion_cantidad_unidades'  => $rqst['goa_juridico_destruccion_cantidad_unidades']  ?? 0,
                ':goa_juridico_destruccion_valor_total'        => $rqst['goa_juridico_destruccion_valor_total']        ?? 0,
                ':tipo_capacitacion_goa'                       => $rqst['tipo_capacitacion_goa']                       ?? '',
                ':numero_asistentes'                           => $rqst['numero_asistentes']                           ?? 0,
            ]);

            $arrjson['output']['valid']    = true;
            $arrjson['output']['response'] = 'Registro actualizado correctamente';
        } catch (PDOException $e) {
            error_log("Hacienda::update error: " . $e->getMessage());
            $arrjson['output']['response'] = 'Error de base de datos';
        } finally {
            $db->closeConect();
        }

        return $arrjson;
    }
}
