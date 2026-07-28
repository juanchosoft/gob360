<?php

// Incluir archivos necesarios
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Hacienda.php';

$accion = $_GET['accion'] ?? '';

// --- Lógica para obtener los datos según la acción ---
$arr = Hacienda::getInformacionPorAccion(array('accion' => $accion));

// Mapa para obtener la clave de datos correcta en el array de respuesta
$dataKeyMap = [
    'Operativos Contrabando cigarrillos' => 'cigarrillos',
    'Operativos Contrabando tabaco' => 'tabaco',
    'Operativos Contrabando licores' => 'licores',
    'Operativos Contrabando cerveza' => 'cerveza',
    'GOA Aprehensiones de Licores' => 'GOALicores',
    'GOA Aprehensión de Cigarrillos' => 'GOACigarrillos',
    'GOA Aprehensión de Cervezas' => 'GOACervezas',
    'GOA Aprehensión de Tabaco y Otros' => 'GOATabaco',
    'Registro de Visitas a Establecimientos Comerciales' => 'registroVisitas',
    'GOA Juridico' => 'GOAJuridico',
    'Impuesto Vehicular Recaudado' => 'vehicularRecaudado',
];

$dataKey = $dataKeyMap[$accion] ?? null;
$datos = ($dataKey && isset($arr['output'][$dataKey])) ? $arr['output'][$dataKey] : [];


// --- Configuración de Columnas Dinámicas ---
// Define las columnas específicas (header, key en el array de datos, formato)
$columnMap = [
    // --- Operativos Contrabando (Incautación/Valor) ---
    'Operativos Contrabando cigarrillos' => [
        ['header' => 'Tipo Cigarrillo', 'key' => 'tipo_cigarrillo', 'format' => 'texto'],
        ['header' => 'Cantidad Incautada', 'key' => 'total_incautacion', 'format' => 'numero'],
        ['header' => 'Valor Incautado', 'key' => 'total_valor', 'format' => 'moneda'],
    ],
    'Operativos Contrabando tabaco' => [
        ['header' => 'Tipo Tabaco', 'key' => 'tipo_tabaco', 'format' => 'texto'],
        ['header' => 'Cantidad Incautada', 'key' => 'total_incautacion', 'format' => 'numero'],
        ['header' => 'Valor Incautado', 'key' => 'total_valor', 'format' => 'moneda'],
    ],
    'Operativos Contrabando licores' => [
        ['header' => 'Tipo Licor', 'key' => 'tipo', 'format' => 'texto'],
        ['header' => 'Cantidad Incautada', 'key' => 'total_incautacion_licores', 'format' => 'numero'],
        ['header' => 'Valor Incautado', 'key' => 'total_valor_licores', 'format' => 'moneda'],
    ],
    'Operativos Contrabando cerveza' => [
        ['header' => 'Tipo Cerveza', 'key' => 'tipo', 'format' => 'texto'],
        ['header' => 'Cantidad Incautada', 'key' => 'total_incautacion_cerveza', 'format' => 'numero'],
        ['header' => 'Valor Incautado', 'key' => 'total_valor_cerveza', 'format' => 'moneda'],
    ],

    // --- GOA Aprehensiones (Cantidad Aprehendida/Avalúo Comercial) ---
    'GOA Aprehensiones de Licores' => [
        ['header' => 'Acción', 'key' => 'accion', 'format' => 'texto'],
        ['header' => 'Cantidad Aprehendida', 'key' => 'cantidad_aprehendida', 'format' => 'numero'],
        ['header' => 'Avalúo Comercial', 'key' => 'avaluo_comercial', 'format' => 'moneda'],
    ],
    'GOA Aprehensión de Cigarrillos' => [
        ['header' => 'Acción', 'key' => 'accion', 'format' => 'texto'],
        ['header' => 'Cantidad Aprehendida', 'key' => 'cantidad_aprehendida', 'format' => 'numero'],
        ['header' => 'Avalúo Comercial', 'key' => 'avaluo_comercial', 'format' => 'moneda'],
    ],
    'GOA Aprehensión de Cervezas' => [
        ['header' => 'Acción', 'key' => 'accion', 'format' => 'texto'],
        ['header' => 'Cantidad Aprehendida', 'key' => 'cantidad_aprehendida', 'format' => 'numero'],
        ['header' => 'Avalúo Comercial', 'key' => 'avaluo_comercial', 'format' => 'moneda'],
    ],
    'GOA Aprehensión de Tabaco y Otros' => [
        ['header' => 'Acción', 'key' => 'accion', 'format' => 'texto'],
        ['header' => 'Cantidad Aprehendida', 'key' => 'cantidad_aprehendida', 'format' => 'numero'],
        ['header' => 'Avalúo Comercial', 'key' => 'avaluo_comercial', 'format' => 'moneda'],
    ],

    // --- Registro de Visitas ---
    'Registro de Visitas a Establecimientos Comerciales' => [
        ['header' => 'Acción', 'key' => 'accion', 'format' => 'texto'],
        ['header' => 'Cantidad Visitas', 'key' => 'cantidad_visitas_al_municipio', 'format' => 'numero'],
    ],

    // --- GOA Jurídico ---
    'GOA Juridico' => [
        ['header' => 'Acción', 'key' => 'accion', 'format' => 'texto'],
        ['header' => 'Custodia – Valor Total', 'key' => 'goa_juridico_custodia_valor_total', 'format' => 'moneda'],
        ['header' => 'Custodia – Cant. Procesos', 'key' => 'goa_juridico_custodia_cantidad_procesos', 'format' => 'numero'],
        ['header' => 'Custodia – Cant. Unidades', 'key' => 'goa_juridico_custodia_cantidad_unidades', 'format' => 'numero'],
        ['header' => 'Destrucción – Cant. Unidades', 'key' => 'goa_juridico_destruccion_cantidad_unidades', 'format' => 'numero'],
        ['header' => 'Destrucción – Valor Total', 'key' => 'goa_juridico_destruccion_valor_total', 'format' => 'moneda'],
    ],

    // --- Impuesto Vehicular Recaudado ---
    'Impuesto Vehicular Recaudado' => [
        ['header' => 'Acción', 'key' => 'accion', 'format' => 'texto'],
        ['header' => 'Valor Recaudado Trámites', 'key' => 'valor_tramite_impuesto_vehicular', 'format' => 'moneda'],
        ['header' => 'Valor Recaudado Impuesto Vehicular', 'key' => 'valor_recaudo_impuesto_vehicular', 'format' => 'moneda'],
        ['header' => 'Cant. Operativos', 'key' => 'vehicular_cantidad_operativos', 'format' => 'numero'],
        ['header' => 'Cant. Emplazados', 'key' => 'vehicular_cantidad_emplazados', 'format' => 'numero'],
        ['header' => 'Cant. Placas Consultadas', 'key' => 'vehicular_cantidad_placas_consultadas', 'format' => 'numero'],
        ['header' => 'Cant. Campañas Sensibilización', 'key' => 'vehicular_cantidad_campanas_sensibilizacion', 'format' => 'numero'],
    ],
];

// Obtener la configuración de columnas para la acción actual
$currentColumns = $columnMap[$accion] ?? [];

// Definición de las columnas fijas (siempre se incluyen)
$fixedColumns = [
    // Columnas de metadata y comunes
    ['header' => 'ID Registro', 'key' => 'id', 'format' => 'texto'],
    ['header' => 'Fecha Registro', 'key' => 'dtcreate_at', 'format' => 'fecha'],
    ['header' => 'Municipio', 'key' => 'municipio', 'format' => 'texto'],
    ['header' => 'Código Municipio', 'key' => 'codigo_muncipio', 'format' => 'texto'],
    ['header' => 'Código Dpto.', 'key' => 'codigo_departamento', 'format' => 'texto'],
    ['header' => 'Usuario Registro', 'key' => 'usuario_completo', 'format' => 'texto'],
];

$finalColumn = [
    // Columna final
    ['header' => 'Observaciones', 'key' => 'observaciones', 'format' => 'texto'],
];

// Combinar todas las columnas: Fijas + Dinámicas + Final
$allColumns = array_merge($fixedColumns, $currentColumns, $finalColumn);


// Headers para Excel
$fecha_exportacion = date('Y-m-d H:i:s');
$filename = "reporte_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Crear tabla HTML que Excel interpretará
echo "\xEF\xBB\xBF"; // BOM para UTF-8
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte Excel - Hacienda</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th { background-color: #4472C4; color: white; font-weight: bold; padding: 8px; border: 1px solid black; }
        td { padding: 8px; border: 1px solid black; }
        /* Formato de número de Excel (para valores numéricos grandes) */
        .numero { mso-number-format:'\#\,\#\#0'; } 
        /* Formato de moneda de Excel: Usando \$\#\,\#\#0 para asegurar el formato de moneda con el signo $*/
        .moneda { mso-number-format:'\$\#\,\#\#0'; }
        /* Formato de fecha y hora */
        .fecha { mso-number-format:'yyyy\-mm\-dd hh:mm'; }
        .titulo { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .subtitulo { font-size: 16px; margin-top: 0; margin-bottom: 5px; }
        .metadata { font-size: 12px; color: #555; }
    </style>
</head>
<body>
    <!-- Encabezado del Informe -->
    <div class="titulo">Informe de Hacienda</div>
    <div class="subtitulo">Detalle de: <?= htmlspecialchars($accion) ?></div>
    <p class="metadata">Exportado de software **Acción Unificado Santander**.</p>
    <p class="metadata">Fecha de exportación: <?= $fecha_exportacion ?></p>
    
    <?php if (empty($datos)): ?>
        <p>No se encontraron datos para la acción: <?= htmlspecialchars($accion) ?></p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <!-- Generación de Encabezados (Headers) Dinámicos -->
                <?php foreach ($allColumns as $col): ?>
                    <th><?= htmlspecialchars($col['header']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <!-- Generación de Filas de Datos Dinámicas -->
            <?php foreach ($datos as $dato): 
                // Asegurar que la fecha se formatee correctamente para Excel
                $fechaFormato = isset($dato['dtcreate_at']) ? date('Y-m-d H:i:s', strtotime($dato['dtcreate_at'])) : '';
            ?>
            <tr>
                <?php foreach ($allColumns as $col): 
                    $key = $col['key'];
                    $formatClass = $col['format'];
                    // Intentar obtener el valor. Si no existe, usar 'N/A'
                    $value = $dato[$key] ?? 'N/A'; 
                    
                    // Manejar la columna de fecha con el valor formateado
                    if ($key === 'dtcreate_at') {
                        $value = $fechaFormato;
                    }
                ?>
                <td class="<?= $formatClass ?>">
                    <?= htmlspecialchars($value) ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
<?php
exit;
?>
