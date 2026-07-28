<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="consolidado.xls"');
header('Cache-Control: max-age=0');

include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Colombia.php';

$rqst = [
    'codigo_departamento' => $_GET['codigo_departamento'] ?? 0,
    'codigo_municipio' => $_GET['codigo_municipio'] ?? 0,
    'pilar' => $_GET['pilar'] ?? 0,
    'secretaria' => $_GET['secretaria'] ?? 0
];

$dataConsolidado = Colombia::consultarConsolidadPilaresFactoreslistadoGeneral($rqst);
$isvalidConsolidado = isset($dataConsolidado['output']) ? $dataConsolidado['output']['valid'] : false;
$listado = isset($dataConsolidado['output']) ? $dataConsolidado['output']['response'] : [];

if ($isvalidConsolidado && !empty($listado)) {
    $filename = "Listado_factores_generales_" . date('Ymd') . ".xls";
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Disposition: attachment;filename=$filename");
    header("Content-Transfer-Encoding: binary ");
    ?>

  <table style="font-family: Arial, sans-serif;">
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr><td colspan="4" style="background-color: #1a5a32; color: #ffffff; padding: 10px;"><b>INFORME</b></td></tr>
    <tr><td colspan="4">&nbsp;</td></tr>
    <tr><td colspan="4" style="font-size: 16px;"><b>EXPORTADO DESDE SISTEMA - GOBERNACION</b></td></tr>
    <tr><td colspan="4"><b>FECHA: <?php echo date('d/m/Y'); ?></b></td></tr>
    <tr><td colspan="4">&nbsp;</td></tr>
  </table>

  <table border='1' style="font-family: Arial, sans-serif; border-collapse: collapse; width: 100%;">
    <?php
    $resultado = '';
    $resultado .= '<tr style="background-color: #1a5a32; color: #ffffff; font-weight: bold;">';
    $resultado .= "<td style='padding: 8px;'><b>Municipio</b></td>";
    $resultado .= "<td style='padding: 8px;'><b>Factor</b></td>";
    $resultado .= "<td style='padding: 8px;'><b>Cantidad</b></td>";
    $resultado .= "<td style='padding: 8px;'><b>Unidad de medida</b></td>";
    $resultado .= '</tr>';
    foreach ($listado as $value) {
        $resultado .= '<tr style="background-color: #f2f2f2;">';
        $resultado .= "<td style='padding: 8px;'>" . ($value['municipio']) . "</td>";
        $resultado .= "<td style='padding: 8px;'>" . ($value['factor']) . "</td>";
        $resultado .= "<td style='padding: 8px;'>" . ($value['total_cantidad']) . "</td>";
        $resultado .= "<td style='padding: 8px;'>" . ($value['tipo_medicion']) . "</td>";
        $resultado .= '</tr>';
    }
    echo utf8_decode($resultado);
    ?>
  </table>
  <?php
} else {
    echo "No hay datos para descargar.";
}