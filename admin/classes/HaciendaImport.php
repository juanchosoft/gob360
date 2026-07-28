<?php

class HaciendaImport
{
    // Mapa de encabezados Excel → campo BD
    private static $headerMap = [
        'Fecha (YYYY-MM-DD)'               => 'date',
        'Municipio'                         => 'municipio_nombre',
        'Objeto'                            => 'objeto',
        'Provincia'                         => 'provincia',
        'Observaciones'                     => 'observaciones',
        'Personas Capacitadas'              => 'cantidad_personas',
        'Valor Trámite Impuesto Vehicular'  => 'valor_tramite_impuesto_vehicular',
        'Valor Recaudo Impuesto Vehicular'  => 'valor_recaudo_impuesto_vehicular',
        'Cantidad Operativos'               => 'vehicular_cantidad_operativos',
        'Cantidad Emplazados'               => 'vehicular_cantidad_emplazados',
        'Cantidad Placas Consultadas'       => 'vehicular_cantidad_placas_consultadas',
        'Cantidad Campañas Sensibilización' => 'vehicular_cantidad_campanas_sensibilizacion',
        'Valor Nacional'                    => 'valor_nacional',
        'Valor Importado'                   => 'valor_importado',
        'Valor Trámite'                     => 'valor_tramite',
        'Valor Recaudo'                     => 'valor_recaudo',
        'Estampilla'                        => 'estampilla',
        'Valor Estampilla'                  => 'valor_estampilla',
        'Cantidad Aprehendida'              => 'cantidad_aprehendida',
        'Avalúo Comercial'                  => 'avaluo_comercial',
        'Nombre Establecimiento'            => 'nombre_establecimiento',
        'Tipo Establecimiento'              => 'tipoe',
        'Establecimiento'                   => 'establecimiento',
        'Dirección'                         => 'direccion',
        'Barrio'                            => 'barrio',
        'Administrador'                     => 'administrador',
        'Cantidad Visitas al Municipio'     => 'cantidad_visitas_al_municipio',
        'Custodia Valor Total'              => 'goa_juridico_custodia_valor_total',
        'Custodia Cantidad Procesos'        => 'goa_juridico_custodia_cantidad_procesos',
        'Custodia Cantidad Unidades'        => 'goa_juridico_custodia_cantidad_unidades',
        'Destrucción Cantidad Unidades'     => 'goa_juridico_destruccion_cantidad_unidades',
        'Destrucción Valor Total'           => 'goa_juridico_destruccion_valor_total',
        'Tipo Capacitación GOA'             => 'tipo_capacitacion_goa',
        'Número Asistentes'                 => 'numero_asistentes',
    ];

    /**
     * Procesa el archivo Excel y guarda los registros en tbl_hacienda.
     * @param string $filePath  Ruta temporal del archivo subido
     * @param string $accion    Tipo de acción seleccionado
     * @param int    $userId    ID del usuario en sesión
     * @return array            ['inserted' => n, 'errors' => [['fila' => n, 'mensaje' => '...'], ...]]
     */
    public static function processExcel(string $filePath, string $accion, int $userId): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet   = $spreadsheet->getActiveSheet();
        $data        = $worksheet->toArray(null, true, true, true);

        if (empty($data)) {
            return ['inserted' => 0, 'errors' => [['fila' => '-', 'mensaje' => 'El archivo está vacío.']]];
        }

        $rawHeaders   = array_shift($data);
        $excelHeaders = array_map('trim', $rawHeaders);

        $colMap = [];
        foreach ($excelHeaders as $colLetter => $label) {
            if (isset(self::$headerMap[$label])) {
                $colMap[$colLetter] = self::$headerMap[$label];
            }
        }

        $db  = new DbConection();
        $pdo = $db->openConect();

        $stmtMun = $pdo->query("SELECT codigo_muncipio, municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " ORDER BY municipio ASC");
        $munRows = $stmtMun->fetchAll(PDO::FETCH_ASSOC);
        $municipiosMap = [];
        foreach ($munRows as $m) {
            $municipiosMap[mb_strtolower(trim($m['municipio']))] = (int)$m['codigo_muncipio'];
        }

        $accionesSinMunicipio = ['Impuesto Vehicular Recaudado', 'Impuesto Estampillas Recaudado'];
        $tbl_departamento_id  = Util::getDepartamentoPrincipal();

        // FASE 1: validar todas las filas antes de tocar la BD
        $errors     = [];
        $rowsBatch  = [];
        $filaExcel  = 2;

        foreach ($data as $row) {
            if (count(array_filter(array_map('trim', $row))) === 0) {
                $filaExcel++;
                continue;
            }

            $fields = [];
            foreach ($colMap as $colLetter => $fieldName) {
                $fields[$fieldName] = isset($row[$colLetter]) ? trim((string)$row[$colLetter]) : '';
            }

            $tbl_municipio_id = 0;
            if (!in_array($accion, $accionesSinMunicipio)) {
                $munNombre = mb_strtolower($fields['municipio_nombre'] ?? '');
                if (empty($munNombre)) {
                    $errors[] = ['fila' => $filaExcel, 'mensaje' => 'El municipio es requerido.'];
                    $filaExcel++;
                    continue;
                }
                if (!isset($municipiosMap[$munNombre])) {
                    $errors[] = ['fila' => $filaExcel, 'mensaje' => "Municipio '{$fields['municipio_nombre']}' no encontrado."];
                    $filaExcel++;
                    continue;
                }
                $tbl_municipio_id = $municipiosMap[$munNombre];
            }

            $date = $fields['date'] ?? '';
            if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $errors[] = ['fila' => $filaExcel, 'mensaje' => "Fecha inválida: '$date'. Formato requerido: YYYY-MM-DD."];
                $filaExcel++;
                continue;
            }

            $validacion = self::validarCamposAccion($accion, $fields, $filaExcel);
            if ($validacion !== null) {
                $errors[] = $validacion;
                $filaExcel++;
                continue;
            }

            $rowsBatch[] = [
                'accion'                                     => $accion,
                'date'                                       => $date,
                'objeto'                                     => $fields['objeto'] ?? '',
                'provincia'                                  => $fields['provincia'] ?? '',
                'observaciones'                              => $fields['observaciones'] ?? '',
                'secretaria'                                 => 'Hacienda',
                'tbl_municipio_id'                           => $tbl_municipio_id,
                'tbl_departamento_id'                        => $tbl_departamento_id,
                'tbl_usuario_id'                             => $userId,
                'cantidad'                                   => 0,
                'valor_total'                                => 0,
                'foto'                                       => '',
                'tipo'                                       => '',
                'tipo_cigarrillo'                            => '',
                'tipo_tabaco'                                => '',
                'incautacion_licores'                        => 0,
                'valor_licores'                              => 0,
                'incautacion_cigarrillos'                    => 0,
                'valor_cigarrillos'                          => 0,
                'incautacion_tabaco'                         => 0,
                'valor_tabaco'                               => 0,
                'incautacion_cerveza'                        => 0,
                'valor_cerveza'                              => 0,
                'cantidad_personas'                          => (int)($fields['cantidad_personas'] ?? 0),
                'valor_importado'                            => (float)($fields['valor_importado'] ?? 0),
                'valor_nacional'                             => (float)($fields['valor_nacional'] ?? 0),
                'valor_tramite'                              => (float)($fields['valor_tramite'] ?? 0),
                'valor_recaudo'                              => (float)($fields['valor_recaudo'] ?? 0),
                'valor_tramite_impuesto_vehicular'           => (float)($fields['valor_tramite_impuesto_vehicular'] ?? 0),
                'valor_recaudo_impuesto_vehicular'           => (float)($fields['valor_recaudo_impuesto_vehicular'] ?? 0),
                'estampilla'                                 => $fields['estampilla'] ?? '',
                'valor_estampilla'                           => (float)($fields['valor_estampilla'] ?? 0),
                'cantidad_aprehendida'                       => (float)($fields['cantidad_aprehendida'] ?? 0),
                'avaluo_comercial'                           => (float)($fields['avaluo_comercial'] ?? 0),
                'cantidad_visitas_al_municipio'              => (int)($fields['cantidad_visitas_al_municipio'] ?? 0),
                'tipo_capacitacion_goa'                      => $fields['tipo_capacitacion_goa'] ?? '',
                'numero_asistentes'                          => (int)($fields['numero_asistentes'] ?? 0),
                'barrio'                                     => $fields['barrio'] ?? '',
                'direccion'                                  => $fields['direccion'] ?? '',
                'administrador'                              => $fields['administrador'] ?? '',
                'nombre_establecimiento'                     => $fields['nombre_establecimiento'] ?? '',
                'tipoe'                                      => $fields['tipoe'] ?? '',
                'establecimiento'                            => $fields['establecimiento'] ?? '',
                'goa_juridico_custodia_valor_total'          => (float)($fields['goa_juridico_custodia_valor_total'] ?? 0),
                'goa_juridico_custodia_cantidad_procesos'    => (int)($fields['goa_juridico_custodia_cantidad_procesos'] ?? 0),
                'goa_juridico_custodia_cantidad_unidades'    => (int)($fields['goa_juridico_custodia_cantidad_unidades'] ?? 0),
                'goa_juridico_destruccion_cantidad_unidades' => (int)($fields['goa_juridico_destruccion_cantidad_unidades'] ?? 0),
                'goa_juridico_destruccion_valor_total'       => (float)($fields['goa_juridico_destruccion_valor_total'] ?? 0),
                'vehicular_cantidad_operativos'              => (int)($fields['vehicular_cantidad_operativos'] ?? 0),
                'vehicular_cantidad_emplazados'              => (int)($fields['vehicular_cantidad_emplazados'] ?? 0),
                'vehicular_cantidad_placas_consultadas'      => (int)($fields['vehicular_cantidad_placas_consultadas'] ?? 0),
                'vehicular_cantidad_campanas_sensibilizacion'=> (int)($fields['vehicular_cantidad_campanas_sensibilizacion'] ?? 0),
            ];

            $filaExcel++;
        }

        // Si hay errores de validación, no insertamos nada
        if (!empty($errors)) {
            $db->closeConect();
            return ['inserted' => 0, 'errors' => $errors];
        }

        if (empty($rowsBatch)) {
            $db->closeConect();
            return ['inserted' => 0, 'errors' => [['fila' => '-', 'mensaje' => 'No se encontraron filas de datos.']]];
        }

        // FASE 2: insertar todo en una sola transacción
        $q = "INSERT INTO " . $db->getTable('tbl_hacienda') . " (
                dtcreate_at, accion, cantidad, incautacion_licores, valor_licores,
                incautacion_cigarrillos, valor_cigarrillos, incautacion_cerveza, valor_cerveza,
                cantidad_personas, observaciones, tbl_municipio_id,
                date, secretaria, objeto, provincia, tbl_departamento_id, tbl_usuario_id,
                valor_total, foto, valor_importado, valor_nacional, valor_tramite, valor_recaudo,
                valor_tramite_impuesto_vehicular, valor_recaudo_impuesto_vehicular,
                estampilla, valor_estampilla, incautacion_tabaco, valor_tabaco,
                tipo, tipo_cigarrillo, tipo_tabaco,
                cantidad_aprehendida, avaluo_comercial, cantidad_visitas_al_municipio,
                tipo_capacitacion_goa, numero_asistentes,
                barrio, direccion, administrador, nombre_establecimiento, tipoe, establecimiento,
                goa_juridico_custodia_valor_total, goa_juridico_custodia_cantidad_procesos,
                goa_juridico_custodia_cantidad_unidades, goa_juridico_destruccion_cantidad_unidades,
                goa_juridico_destruccion_valor_total,
                vehicular_cantidad_operativos, vehicular_cantidad_emplazados,
                vehicular_cantidad_placas_consultadas, vehicular_cantidad_campanas_sensibilizacion
            ) VALUES (
                NOW(),
                :accion, :cantidad, :incautacion_licores, :valor_licores,
                :incautacion_cigarrillos, :valor_cigarrillos, :incautacion_cerveza, :valor_cerveza,
                :cantidad_personas, :observaciones, :tbl_municipio_id,
                :date, :secretaria, :objeto, :provincia, :tbl_departamento_id, :tbl_usuario_id,
                :valor_total, :foto, :valor_importado, :valor_nacional, :valor_tramite, :valor_recaudo,
                :valor_tramite_impuesto_vehicular, :valor_recaudo_impuesto_vehicular,
                :estampilla, :valor_estampilla, :incautacion_tabaco, :valor_tabaco,
                :tipo, :tipo_cigarrillo, :tipo_tabaco,
                :cantidad_aprehendida, :avaluo_comercial, :cantidad_visitas_al_municipio,
                :tipo_capacitacion_goa, :numero_asistentes,
                :barrio, :direccion, :administrador, :nombre_establecimiento, :tipoe, :establecimiento,
                :goa_juridico_custodia_valor_total, :goa_juridico_custodia_cantidad_procesos,
                :goa_juridico_custodia_cantidad_unidades, :goa_juridico_destruccion_cantidad_unidades,
                :goa_juridico_destruccion_valor_total,
                :vehicular_cantidad_operativos, :vehicular_cantidad_emplazados,
                :vehicular_cantidad_placas_consultadas, :vehicular_cantidad_campanas_sensibilizacion
            )";

        try {
            $stmt = $pdo->prepare($q);
            $pdo->beginTransaction();

            foreach ($rowsBatch as $params) {
                $stmt->execute($params);
            }

            $pdo->commit();
            $inserted = count($rowsBatch);
        } catch (Exception $e) {
            $pdo->rollBack();
            $db->closeConect();
            return ['inserted' => 0, 'errors' => [['fila' => '-', 'mensaje' => 'Error en BD, se revirtió la carga: ' . $e->getMessage()]]];
        }

        $db->closeConect();
        return ['inserted' => $inserted, 'errors' => []];
    }

    private static function validarCamposAccion(string $accion, array $fields, int $fila): ?array
    {
        switch ($accion) {
            case 'Capacitacion Fiscal y Financiera':
                if (empty($fields['cantidad_personas'])) {
                    return ['fila' => $fila, 'mensaje' => '"Personas Capacitadas" es requerido.'];
                }
                break;

            case 'Impuesto Vehicular Recaudado':
                if (empty($fields['valor_tramite_impuesto_vehicular']) && empty($fields['valor_recaudo_impuesto_vehicular'])) {
                    return ['fila' => $fila, 'mensaje' => '"Valor Trámite" o "Valor Recaudo" son requeridos para Impuesto Vehicular.'];
                }
                break;

            case 'Recaudo del impuesto al consumo':
                if (empty($fields['valor_importado']) && empty($fields['valor_nacional'])) {
                    return ['fila' => $fila, 'mensaje' => '"Valor Nacional" o "Valor Importado" son requeridos.'];
                }
                break;

            case 'Recaudo del impuesto de registro':
                if (empty($fields['valor_tramite']) || empty($fields['valor_recaudo'])) {
                    return ['fila' => $fila, 'mensaje' => '"Valor Trámite" y "Valor Recaudo" son requeridos.'];
                }
                break;

            case 'Impuesto Estampillas Recaudado':
                if (empty($fields['estampilla']) || empty($fields['valor_estampilla'])) {
                    return ['fila' => $fila, 'mensaje' => '"Estampilla" y "Valor Estampilla" son requeridos.'];
                }
                break;

            case 'GOA Aprehensiones de Licores':
            case 'GOA Aprehensión de Cigarrillos':
            case 'GOA Aprehensión de Cervezas':
            case 'GOA Aprehensión de Tabaco y Otros':
                if (empty($fields['cantidad_aprehendida']) || empty($fields['avaluo_comercial'])) {
                    return ['fila' => $fila, 'mensaje' => '"Cantidad Aprehendida" y "Avalúo Comercial" son requeridos.'];
                }
                break;

            case 'Registro de Visitas a Establecimientos Comerciales':
                if (empty($fields['cantidad_visitas_al_municipio'])) {
                    return ['fila' => $fila, 'mensaje' => '"Cantidad Visitas al Municipio" es requerido.'];
                }
                break;

            case 'Registro de Capacitaciones del GOA':
                if (empty($fields['tipo_capacitacion_goa']) || empty($fields['numero_asistentes'])) {
                    return ['fila' => $fila, 'mensaje' => '"Tipo Capacitación GOA" y "Número Asistentes" son requeridos.'];
                }
                break;

            case 'GOA Juridico':
                if (empty($fields['goa_juridico_custodia_valor_total']) && empty($fields['goa_juridico_destruccion_valor_total'])) {
                    return ['fila' => $fila, 'mensaje' => 'Al menos un campo de GOA Jurídico debe tener información.'];
                }
                break;
        }

        return null;
    }
}
