<?php
session_start();
require_once __DIR__ . '/../include/controller_boot.php';
ControllerGate::authorizeScript(__FILE__, '_entrypoint');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../include/generic_classes.php';
require_once __DIR__ . '/../classes/Hacienda.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

if (!isset($_SESSION['session_user'])) {
    http_response_code(403);
    exit('Acceso denegado');
}

$accion = $_GET['accion'] ?? '';

$columnasEspecificas = [
    'Capacitacion Fiscal y Financiera' => [
        'Personas Capacitadas',
    ],
    'Impuesto Vehicular Recaudado' => [
        'Valor Trámite Impuesto Vehicular',
        'Valor Recaudo Impuesto Vehicular',
        'Cantidad Operativos',
        'Cantidad Emplazados',
        'Cantidad Placas Consultadas',
        'Cantidad Campañas Sensibilización',
    ],
    'Recaudo del impuesto al consumo' => [
        'Valor Nacional',
        'Valor Importado',
    ],
    'Recaudo del impuesto de registro' => [
        'Valor Trámite',
        'Valor Recaudo',
    ],
    'Impuesto Estampillas Recaudado' => [
        'Estampilla',
        'Valor Estampilla',
    ],
    'GOA Aprehensiones de Licores' => [
        'Cantidad Aprehendida', 'Avalúo Comercial', 'Nombre Establecimiento',
        'Tipo Establecimiento', 'Establecimiento', 'Dirección', 'Barrio', 'Administrador',
    ],
    'GOA Aprehensión de Cigarrillos' => [
        'Cantidad Aprehendida', 'Avalúo Comercial', 'Nombre Establecimiento',
        'Tipo Establecimiento', 'Establecimiento', 'Dirección', 'Barrio', 'Administrador',
    ],
    'GOA Aprehensión de Cervezas' => [
        'Cantidad Aprehendida', 'Avalúo Comercial', 'Nombre Establecimiento',
        'Tipo Establecimiento', 'Establecimiento', 'Dirección', 'Barrio', 'Administrador',
    ],
    'GOA Aprehensión de Tabaco y Otros' => [
        'Cantidad Aprehendida', 'Avalúo Comercial', 'Nombre Establecimiento',
        'Tipo Establecimiento', 'Establecimiento', 'Dirección', 'Barrio', 'Administrador',
    ],
    'Registro de Visitas a Establecimientos Comerciales' => [
        'Cantidad Visitas al Municipio', 'Nombre Establecimiento', 'Tipo Establecimiento',
        'Establecimiento', 'Dirección', 'Barrio', 'Administrador',
    ],
    'GOA Juridico' => [
        'Custodia Valor Total', 'Custodia Cantidad Procesos', 'Custodia Cantidad Unidades',
        'Destrucción Cantidad Unidades', 'Destrucción Valor Total',
        'Observaciones',
    ],
    'Registro de Capacitaciones del GOA' => [
        'Tipo Capacitación GOA',
        'Número Asistentes',
    ],
];

if (!array_key_exists($accion, $columnasEspecificas)) {
    http_response_code(400);
    exit('Acción no válida: ' . htmlspecialchars($accion));
}

$accionesSinMunicipio = ['Impuesto Vehicular Recaudado', 'Impuesto Estampillas Recaudado'];

// Acciones que usan columnas Establecimiento / Tipo Establecimiento con dropdown
$accionesConEstablecimiento = [
    'GOA Aprehensiones de Licores',
    'GOA Aprehensión de Cigarrillos',
    'GOA Aprehensión de Cervezas',
    'GOA Aprehensión de Tabaco y Otros',
    'Registro de Visitas a Establecimientos Comerciales',
];

$opcionesEstablecimiento = [
    'Bodega', 'Comercializadora', 'Dulcería', 'Bar/Discoteca', 'Tienda',
    'Distribuidora', 'Supermercado/Fruver', 'Restaurante/Comidas', 'Cafetería',
    'Autoservicio', 'Estanco/Licorera', 'Panadería/Pastelería', 'Variedades/Abarrotes',
    'Quiosco/Caseta', 'Otro', 'Hotel/Hospedaje', 'Miscelánea', 'Granero',
    'Farmacía/Droguería', 'Balneario', 'Billar', 'Agencia', 'Asadero',
];

$opcionesTipoEstablecimiento = ['Establecimiento comercial'];

$headers = ['Fecha (YYYY-MM-DD)'];
if (!in_array($accion, $accionesSinMunicipio)) {
    $headers[] = 'Municipio';
}
// GOA Juridico ya incluye Observaciones en sus columnas específicas; el resto las lleva antes
$colsEspecificas = $columnasEspecificas[$accion];
if ($accion === 'GOA Juridico') {
    $headers = array_merge($headers, ['Objeto', 'Provincia'], $colsEspecificas);
} else {
    $headers = array_merge($headers, ['Objeto', 'Provincia', 'Observaciones'], $colsEspecificas);
}

$municipios = !in_array($accion, $accionesSinMunicipio) ? Hacienda::getMunicipiosPlantilla() : [];
$usaEstablecimiento = in_array($accion, $accionesConEstablecimiento);

$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle('Datos');

$headerStyle = [
    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF20427F']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]],
];

$col = 1;
foreach ($headers as $header) {
    $sheet->getCellByColumnAndRow($col, 1)->setValue($header);
    $sheet->getColumnDimensionByColumn($col)->setWidth(22);
    $col++;
}
$sheet->getStyle('A1:' . $sheet->getCellByColumnAndRow($col - 1, 1)->getCoordinate())->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(30);

$ejemplos = [
    'Fecha (YYYY-MM-DD)'               => date('Y-m-d'),
    'Municipio'                         => 'Bucaramanga',
    'Objeto'                            => 'Descripción del objeto',
    'Provincia'                         => 'Soto',
    'Observaciones'                     => 'Observaciones opcionales',
    'Personas Capacitadas'              => '50',
    'Valor Trámite Impuesto Vehicular'  => '1000000',
    'Valor Recaudo Impuesto Vehicular'  => '5000000',
    'Cantidad Operativos'               => '3',
    'Cantidad Emplazados'               => '10',
    'Cantidad Placas Consultadas'       => '200',
    'Cantidad Campañas Sensibilización' => '2',
    'Valor Nacional'                    => '3000000',
    'Valor Importado'                   => '1500000',
    'Valor Trámite'                     => '500000',
    'Valor Recaudo'                     => '2000000',
    'Estampilla'                        => 'Universidad Industrial de Santander',
    'Valor Estampilla'                  => '800000',
    'Cantidad Aprehendida'              => '120',
    'Avalúo Comercial'                  => '4500000',
    'Nombre Establecimiento'            => 'Tienda El Progreso',
    'Tipo Establecimiento'              => 'Establecimiento comercial',
    'Establecimiento'                   => 'Tienda',
    'Dirección'                         => 'Calle 10 # 5-20',
    'Barrio'                            => 'Centro',
    'Administrador'                     => 'Juan Pérez',
    'Cantidad Visitas al Municipio'     => '5',
    'Custodia Valor Total'              => '3000000',
    'Custodia Cantidad Procesos'        => '4',
    'Custodia Cantidad Unidades'        => '80',
    'Destrucción Cantidad Unidades'     => '50',
    'Destrucción Valor Total'           => '1200000',
    'Tipo Capacitación GOA'             => 'Normatividad fiscal',
    'Número Asistentes'                 => '30',
];

$exampleStyle = [
    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8EDF7']],
    'font'    => ['italic' => true, 'color' => ['argb' => 'FF555555']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
];

$col = 1;
foreach ($headers as $header) {
    $sheet->getCellByColumnAndRow($col, 2)->setValue($ejemplos[$header] ?? '');
    $col++;
}
$sheet->getStyle('A2:' . $sheet->getCellByColumnAndRow($col - 1, 2)->getCoordinate())->applyFromArray($exampleStyle);

// ── Hoja Municipios ──────────────────────────────────────────────────────────
if (!empty($municipios)) {
    $sheetMun = $spreadsheet->createSheet();
    $sheetMun->setTitle('Municipios');
    $sheetMun->getCellByColumnAndRow(1, 1)->setValue('Municipio');
    $sheetMun->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF20427F']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheetMun->getColumnDimension('A')->setWidth(30);
    $row = 2;
    foreach ($municipios as $mun) {
        $sheetMun->getCellByColumnAndRow(1, $row)->setValue($mun['municipio']);
        $row++;
    }
}

// ── Hoja Referencias (Establecimiento / Tipo Establecimiento) ─────────────────
$sheetRef = null;
if ($usaEstablecimiento) {
    $sheetRef = $spreadsheet->createSheet();
    $sheetRef->setTitle('Referencias');

    $refHeaderStyle = [
        'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF20427F']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ];

    // Columna A: Establecimiento
    $sheetRef->getCellByColumnAndRow(1, 1)->setValue('Establecimiento');
    $sheetRef->getStyle('A1')->applyFromArray($refHeaderStyle);
    $sheetRef->getColumnDimension('A')->setWidth(28);
    foreach ($opcionesEstablecimiento as $i => $op) {
        $sheetRef->getCellByColumnAndRow(1, $i + 2)->setValue($op);
    }

    // Columna B: Tipo Establecimiento
    $sheetRef->getCellByColumnAndRow(2, 1)->setValue('Tipo Establecimiento');
    $sheetRef->getStyle('B1')->applyFromArray($refHeaderStyle);
    $sheetRef->getColumnDimension('B')->setWidth(26);
    foreach ($opcionesTipoEstablecimiento as $i => $op) {
        $sheetRef->getCellByColumnAndRow(2, $i + 2)->setValue($op);
    }
}

$spreadsheet->setActiveSheetIndex(0);

// ── Data Validation en hoja Datos ────────────────────────────────────────────
if ($usaEstablecimiento && $sheetRef !== null) {
    // Detectar índices de columna para Establecimiento y Tipo Establecimiento
    $colEstab     = null;
    $colTipoEstab = null;
    foreach ($headers as $idx => $h) {
        if ($h === 'Establecimiento')    $colEstab     = $idx + 1; // 1-based
        if ($h === 'Tipo Establecimiento') $colTipoEstab = $idx + 1;
    }

    $maxRefEstab = count($opcionesEstablecimiento) + 1;  // fila 2 a N+1
    $maxRefTipo  = count($opcionesTipoEstablecimiento) + 1;

    $dataRows = 3;   // filas de datos (fila 2 = ejemplo, filas 3-502 = usuario)
    $maxRow   = 502;

    if ($colEstab !== null) {
        $colLetraEstab = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colEstab);
        for ($r = $dataRows; $r <= $maxRow; $r++) {
            $validation = $sheet->getCell($colLetraEstab . $r)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(false);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Valor inválido');
            $validation->setError('Selecciona un valor de la lista Referencias!A.');
            $validation->setFormula1('Referencias!$A$2:$A$' . $maxRefEstab);
        }
    }

    if ($colTipoEstab !== null) {
        $colLetraTipo = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colTipoEstab);
        for ($r = $dataRows; $r <= $maxRow; $r++) {
            $validation = $sheet->getCell($colLetraTipo . $r)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(false);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Valor inválido');
            $validation->setError('Selecciona un valor de la lista Referencias!B.');
            $validation->setFormula1('Referencias!$B$2:$B$' . $maxRefTipo);
        }
    }
}

$nombreArchivo = 'plantilla_hacienda_' . strtolower(
    str_replace([' ', '/', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], ['_', '_', 'a', 'e', 'i', 'o', 'u', 'n'], $accion)
) . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

(new Xlsx($spreadsheet))->save('php://output');
exit;
