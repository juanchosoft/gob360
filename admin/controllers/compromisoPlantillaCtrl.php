<?php
session_start();
require_once __DIR__ . '/../include/controller_boot.php';
ControllerGate::authorizeScript(__FILE__, '_entrypoint');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../include/generic_classes.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

if (!isset($_SESSION['session_user'])) {
    http_response_code(403);
    exit('Acceso denegado');
}

$headers = [
    'ID',
    'Secretaría',
    'Fecha',
    'Provincia',
    'Municipio',
    'Estado',
    'Compromiso Pactado',
    'Respuesta',
    'Componente',
    'Tipo Ejecución',
    'Observaciones',
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
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

$exampleStyle = [
    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8EDF7']],
    'font'    => ['italic' => true, 'color' => ['argb' => 'FF555555']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
];

$ejemplos = [
    'ID'                 => 123,
    'Secretaría'         => 'Secretaría de Educación',
    'Fecha'              => date('Y-m-d'),
    'Provincia'          => 'Soto Norte',
    'Municipio'          => 'Bucaramanga',
    'Estado'             => 'Cumplido',
    'Compromiso Pactado' => 'Realizar mantenimiento de la infraestructura educativa',
    'Respuesta'          => 'Se realizó el mantenimiento en un 80%',
    'Componente'         => 'Infraestructura',
    'Tipo Ejecución'     => 'INVERSIÓN',
    'Observaciones'      => 'Observaciones opcionales',
];

$col = 1;
foreach ($headers as $header) {
    $sheet->getCellByColumnAndRow($col, 2)->setValue($ejemplos[$header] ?? '');
    $col++;
}
$sheet->getStyle('A2:' . $sheet->getCellByColumnAndRow($col - 1, 2)->getCoordinate())->applyFromArray($exampleStyle);

$rows = [
    ['col' => 'A', 'label' => 'ID'],
    ['col' => 'B', 'label' => 'Secretaría'],
    ['col' => 'C', 'label' => 'Fecha'],
    ['col' => 'D', 'label' => 'Provincia'],
    ['col' => 'E', 'label' => 'Municipio'],
    ['col' => 'F', 'label' => 'Estado'],
    ['col' => 'G', 'label' => 'Compromiso Pactado'],
    ['col' => 'H', 'label' => 'Respuesta'],
    ['col' => 'I', 'label' => 'Componente'],
    ['col' => 'J', 'label' => 'Tipo Ejecución'],
    ['col' => 'K', 'label' => 'Observaciones'],
];

$sheetInst = $spreadsheet->createSheet();
$sheetInst->setTitle('Instrucciones');

$sheetInst->getColumnDimension('A')->setWidth(6);
$sheetInst->getColumnDimension('B')->setWidth(80);

$sheetInst->getCellByColumnAndRow(1, 1)->setValue('');
$sheetInst->getCellByColumnAndRow(2, 1)->setValue('INSTRUCCIONES PARA LLENAR LA PLANTILLA');
$sheetInst->getStyle('B1')->getFont()->setBold(true)->setSize(14);
$sheetInst->getStyle('B1')->getFont()->getColor()->setARGB('FF20427F');
$sheetInst->getRowDimension(1)->setRowHeight(30);

$sheetInst->getCellByColumnAndRow(1, 3)->setValue('');
$sheetInst->getCellByColumnAndRow(2, 3)->setValue('IMPORTANTE: El ID debe existir en la base de datos como un compromiso registrado. Para crear nuevos compromisos use el formulario del sistema, no esta plantilla.');
$sheetInst->getStyle('B3')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));

$sheetInst->getCellByColumnAndRow(1, 5)->setValue('');
$sheetInst->getCellByColumnAndRow(2, 5)->setValue('Descripción de columnas:');
$sheetInst->getStyle('B5')->getFont()->setBold(true)->setSize(12);

$sheetInst->getCellByColumnAndRow(1, 6)->setValue('');
$sheetInst->getCellByColumnAndRow(2, 6)->setValue('Los valores permitidos en cada columna son:');
$sheetInst->getStyle('B6')->getFont()->setItalic(true);

$row = 7;
foreach ($rows as $r) {
    $example = $ejemplos[$r['label']] ?? '';
    $sheetInst->getCellByColumnAndRow(1, $row)->setValue($r['col']);
    $sheetInst->getStyle('A' . $row)->getFont()->setBold(true);

    $desc = $r['label'];
    switch ($r['label']) {
        case 'ID':
            $desc .= ': Número entero del ID del compromiso en el sistema (obligatorio).';
            break;
        case 'Secretaría':
            $desc .= ': Nombre de la secretaría responsable (obligatorio).';
            break;
        case 'Fecha':
            $desc .= ': Fecha en formato YYYY-MM-DD (opcional).';
            break;
        case 'Provincia':
            $desc .= ': Nombre de la provincia (obligatorio). Valores permitidos: Soto Norte, Guanentá, García Rovira, Comunera, Vélez, Metropolitana, Yariguíes.';
            break;
        case 'Municipio':
            $desc .= ': Nombre del municipio (obligatorio).';
            break;
        case 'Estado':
            $desc .= ': Estado del compromiso. Valores permitidos: Cumplido, En Trámite, Sin Cumplir, En Espera (obligatorio).';
            break;
        case 'Compromiso Pactado':
            $desc .= ': Descripción del compromiso pactado (opcional).';
            break;
        case 'Respuesta':
            $desc .= ': Descripción de la respuesta o avance (opcional).';
            break;
        case 'Componente':
            $desc .= ': Componente al que pertenece (obligatorio).';
            break;
        case 'Tipo Ejecución':
            $desc .= ': Tipo de ejecución. Valores permitidos: INVERSIÓN, GESTIÓN (obligatorio).';
            break;
        case 'Observaciones':
            $desc .= ': Observaciones adicionales (opcional).';
            break;
    }
    $sheetInst->getCellByColumnAndRow(2, $row)->setValue($desc);
    $row++;
}

$row += 2;
$sheetInst->getCellByColumnAndRow(1, $row)->setValue('');
$sheetInst->getCellByColumnAndRow(2, $row)->setValue('Notas importantes:');
$sheetInst->getStyle('B' . $row)->getFont()->setBold(true)->setSize(12);
$row++;

$notas = [
    '- La fila 1 debe contener los encabezados exactos. No los modifique.',
    '- La fila 2 es un ejemplo. Puede eliminarla o sobrescribirla con datos reales.',
    '- Complete los datos desde la fila 3 en adelante.',
    '- No deje filas vacías entre los datos.',
    '- Guarde el archivo con extensión .xlsx antes de subirlo.',
];
foreach ($notas as $nota) {
    $sheetInst->getCellByColumnAndRow(2, $row)->setValue($nota);
    $row++;
}

// Hoja "Municipios"
$dbMun = new DbConection();
$pdoMun = $dbMun->openConect();
$stmtMun = $pdoMun->query(
    "SELECT subregion, municipio FROM tbl_ciudades_accion_unificada " .
    "WHERE codigo_departamento = 68 ORDER BY subregion, municipio"
);
$municipiosRaw = $stmtMun->fetchAll(\PDO::FETCH_ASSOC);
$dbMun->closeConect();

$municipiosPorProvincia = [];
foreach ($municipiosRaw as $m) {
    $prov = $m['subregion'] ?: 'Sin provincia';
    $municipiosPorProvincia[$prov][] = $m['municipio'];
}

$sheetMun = $spreadsheet->createSheet();
$sheetMun->setTitle('Municipios');
$sheetMun->setSelectedCell('A1');

$colMun = 1;
$provHeaders = [
    'Soto Norte' => 'Soto Norte',
    'Guanentá'   => 'Guanenta',
    'García Rovira' => 'Garcia Rovira',
    'Comunera'    => 'Comunera',
    'Vélez'      => 'Velez',
    'Metropolitana' => 'Metropolitana',
    'Yariguíes'  => 'Yariguies',
];

$provLabelColor = 'FF20427F';
$provinceStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $provLabelColor]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
];

$maxRows = 0;
$colMun = 1;
foreach ($provHeaders as $displayName => $dbName) {
    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colMun);
    $sheetMun->getCellByColumnAndRow($colMun, 1)->setValue($displayName);
    $sheetMun->getStyle($colLetter . '1')->applyFromArray($provinceStyle);
    $sheetMun->getColumnDimension($colLetter)->setWidth(24);

    $municipios = $municipiosPorProvincia[$dbName] ?? [];
    $rowMun = 2;
    foreach ($municipios as $mun) {
        $sheetMun->getCellByColumnAndRow($colMun, $rowMun)->setValue($mun);
        $rowMun++;
    }
    $maxRows = max($maxRows, $rowMun - 1);
    $colMun++;
}

$sheetMun->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colMun - 1) . $maxRows)
    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

$spreadsheet->setActiveSheetIndex(0);

$nombreArchivo = 'plantilla_compromisos.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

(new Xlsx($spreadsheet))->save('php://output');
exit;
