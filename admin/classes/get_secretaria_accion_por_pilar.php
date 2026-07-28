<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function format_numero_php($num) {
    if ($num === null || $num === '') return "0";
    $numFloat = floatval($num);
    if (abs($numFloat - round($numFloat)) < 0.00001) {
        return number_format($numFloat, 0, ',', '.');
    }
    return number_format($numFloat, 2, ',', '.');
}

require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
require_once 'Colombia.php';
require_once 'Ciudad.php';  
require_once 'Pilar.php';
require_once 'Secretarias.php'; 

$codigoDepartamento = intval($_GET['codigo_departamento'] ?? 0);
$codigoMunicipio    = intval($_GET['codigo_municipio'] ?? 0);
$pilarId            = intval($_GET['pilar_id'] ?? 0);

if ($codigoDepartamento === 0 || $codigoMunicipio === 0 || $pilarId === 0) {
    echo '<div class="alert alert-warning">Faltan parámetros obligatorios.</div>';
    exit;
}

$municipioInfoData = Ciudad::getInformacionCiudad(['codigo_muncipio' => $codigoMunicipio]);
$informacionMunicipio = $municipioInfoData['output']['response'][0] ?? null;

$viewBoxActual = !empty($informacionMunicipio['viewbox_svg'])
    ? $informacionMunicipio['viewbox_svg']
    : '0 45 1518.36 900';

$dataInicial = Colombia::calcularColorInicialPorMunicipioByPilarId([
    'codigo_departamento' => $codigoDepartamento,
    'codigo_municipio'    => $codigoMunicipio,
    'pilar'               => $pilarId
]);

$veredasMapa1 = $dataInicial['output']['response'] ?? [];
$isValid1     = $dataInicial['output']['valid'] ?? false;

$dataActual = Colombia::calcularColorPorMunicipioByPilarId([
    'codigo_departamento' => $codigoDepartamento,
    'codigo_municipio'    => $codigoMunicipio,
    'pilar'               => $pilarId
]);

$veredasMapa2 = $dataActual['output']['response'] ?? [];
$isValid2     = $dataActual['output']['valid'] ?? false;

$arrConsolidado = [
    'municipioId' => $codigoMunicipio,
    'pilarId'     => $pilarId,
];

$responseConsolidado = Secretarias::getConsolidadoFactoresPorPilar($arrConsolidado);

$datosTablaConsolidado = [];

if (
    is_array($responseConsolidado) &&
    isset($responseConsolidado['output']['response']) &&
    is_array($responseConsolidado['output']['response'])
) {
    foreach ($responseConsolidado['output']['response'] as $dato) {

        $inicial = format_numero_php($dato['total_cantidad_inicial'] ?? 0);
        $actual  = format_numero_php($dato['total_cantidad_actual'] ?? 0);
        $hayCambio = ($inicial !== $actual);

        $datosTablaConsolidado[] = [
            'icono'   => !empty($dato['icono']) ? $dato['icono'] : null,
            'factor'  => htmlspecialchars($dato['factor'] ?? 'Factor'),
            'inicial' => $inicial,
            'actual'  => $actual,
            'cambio'  => $hayCambio,
            'unidad'  => htmlspecialchars($dato['tipo_medicion'] ?? 'Unidad'),
        ];
    }
}

$hayDatosReales = false;

foreach ($datosTablaConsolidado as $d) {
    if (
        $d['inicial'] !== "0" ||
        $d['actual'] !== "0" ||
        !empty($d['icono'])
    ) {
        $hayDatosReales = true;
        break;
    }
}



?>
<div class="row">
    <!-- COLUMNA 1: MAPA INICIAL -->
    <div class="col-md-6">
        <div class="bloque-mapa">
            <div class="mapa-svg">
                <h5 class="fw-bold text-primary text-center">Mapa Inicial</h5>

                <?php if ($isValid1 && !empty($veredasMapa1)): ?>
                <svg 
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="<?= htmlspecialchars($viewBoxActual) ?>"
                    style="width:100%; height:auto;"
                    preserveAspectRatio="xMidYMid meet">
                    <?php foreach ($veredasMapa1 as $vereda): ?>
                        <?php $color1 = $vereda['color_calculado'] ?? '#9E9E9E'; ?>
                        <g id="VEREDA_MAPA1_<?= $vereda['id'] ?>"
                            onclick="window.location.href='veredas.php?id=<?= $vereda['id'] ?>&mun=<?= $codigoMunicipio ?>&dep=<?= $codigoDepartamento ?>&pilar=<?= $pilarId ?>'"
                            style="cursor:pointer">
                                <?php if (!empty($vereda['points'])): ?>
                                    <polygon 
                                        points="<?= strtoupper($vereda['points']) ?>" 
                                        fill="<?= $color1 ?>"
                                        fill-rule="evenodd"
                                        stroke-miterlimit="10" 
                                        stroke-width="0.1px"
                                    />
                                <?php elseif (!empty($vereda['path'])): ?>
                                    <path 
                                        d="<?= $vereda['path'] ?>" 
                                        fill="<?= $color1 ?>"
                                        stroke="#000" 
                                        stroke-miterlimit="10" 
                                        stroke-width="0.1px"
                                    />
                                <?php endif; ?>
                                <?= !empty($vereda['tspan']) ? $vereda['tspan'] : "" ?>
                            </g>
                    <?php endforeach; ?>
                </svg>
                <?php else: ?>
                <div class="alert alert-info mt-5">No se encontró información SVG (Inicial).</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- COLUMNA 2: MAPA ACTUAL -->
    <div class="col-md-6">
        <div class="bloque-mapa">
            <div class="mapa-svg">
                <h5 class="fw-bold text-success text-center">Mapa Actual</h5>
                <?php if ($isValid2 && !empty($veredasMapa2)): ?>
                <svg 
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="<?= htmlspecialchars($viewBoxActual) ?>"
                    style="width:100%; height:auto;"
                    preserveAspectRatio="xMidYMid meet">
                    <!-- Render SVG actual -->
                    <?php foreach ($veredasMapa2 as $vereda): ?>
                        <?php $color2 = $vereda['color_calculado'] ?? '#1ABC9C'; ?>
                        <g id="VEREDA_MAPA2_<?= $vereda['id'] ?>"
                            onclick="window.location.href='veredas.php?id=<?= $vereda['id'] ?>&mun=<?= $codigoMunicipio ?>&dep=<?= $codigoDepartamento ?>&pilar=<?= $pilarId ?>'"
                            style="cursor:pointer">
                                <?php if (!empty($vereda['points'])): ?>
                                    <polygon 
                                        points="<?= strtoupper($vereda['points']) ?>" 
                                        fill="<?= $color2 ?>"
                                        fill-rule="evenodd"
                                        stroke-miterlimit="10" 
                                        stroke-width="0.1px"
                                    />
                                <?php elseif (!empty($vereda['path'])): ?>
                                    <path 
                                        d="<?= $vereda['path'] ?>" 
                                        fill="<?= $color2 ?>"
                                        stroke="#000" 
                                        stroke-miterlimit="10" 
                                        stroke-width="0.1px"
                                    />
                                <?php endif; ?>
                                <?= !empty($vereda['tspan']) ? $vereda['tspan'] : "" ?>
                            </g>
                    <?php endforeach; ?>
                </svg>
                <?php else: ?>
                    <div class="alert alert-info mt-5">No se encontró información SVG (Actual).</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-12 d-flex justify-content-center">

<?php if (empty($datosTablaConsolidado) || !$hayDatosReales): ?>

            <!-- MENSAJE SIN DATOS -->
            <div style="
                width:100%;
                max-width:800px;
                padding:25px;
                text-align:center;
                background:#f8f9fa;
                border:1px solid #ddd;
                border-radius:10px;
                font-size:16px;
                color:#555;
            ">
                No hay información para este pilar y este municipio.<br>
                <strong>Consulta nuevamente.</strong>
            </div>

        <?php else: ?>

            <!-- TABLA CUANDO SÍ HAY DATOS -->
            <div class="tabla-resumen" 
                 style="
                    padding:20px;
                    margin-top:10px;
                    width: 100%;
                    max-width: 980px;
                    margin-left:auto;
                    margin-right:auto;
                 ">

                <table class="table table-bordered table-sm"
                    style="
                        width:100%;
                        margin:0 auto;
                        border:1px solid #bfbfbf;
                        border-radius:8px;
                        overflow:hidden;
                        table-layout: fixed;
                        text-align:center;
                    ">
                    
                    <thead style="background:#f4f4f4;">
                        <tr>
                            <th style="font-size:12px; width:80px; text-align:center;">Icono</th>
                            <th style="font-size:12px; text-align:center;">Factor</th>
                            <th style="font-size:12px; width:100px; text-align:center;">Inicial</th>
                            <th style="font-size:12px; width:100px; text-align:center;">Actual</th>
                            <th style="font-size:12px; width:100px; text-align:center;">Unidad</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($datosTablaConsolidado as $item): ?>
                        <tr>

                            <td style="padding:8px; vertical-align:middle; text-align:center;">
                                <?php if ($item['icono']): ?>
                                    <img src="<?= $item['icono'] ?>" width="26" height="26" alt="icono">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>

                            <td style="
                                font-size:12px; padding:8px;
                                word-wrap:break-word; white-space:normal; overflow-wrap:break-word;
                                text-align:center; vertical-align:middle;
                            ">
                                <?= $item['factor'] ?>
                            </td>

                            <td style="font-size:12px; padding:8px;"><?= $item['inicial'] ?></td>

                            <td style="font-size:12px; padding:8px; text-align:center;">
                                <?php if ($item['cambio']): ?>
                                    <span style="
                                        display:inline-block;
                                        background:#007bff;
                                        color:#fff;
                                        padding:3px 10px;
                                        border-radius:6px;
                                        font-weight:bold;
                                        font-size:11px;
                                    ">
                                        <?= $item['actual'] ?>
                                    </span>
                                <?php else: ?>
                                    <?= $item['actual'] ?>
                                <?php endif; ?>
                            </td>


                            <td style="font-size:12px; padding:8px;"><?= $item['unidad'] ?></td>

                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const viewBoxUniversal = '-1000 -1000 3000 3000';
    const nombreMunicipio = '<?= htmlspecialchars($informacionMunicipio['municipio'] ?? '') ?>';


    const applyViewBoxCorrection = (containerId) => {
        const svgContainer = document.getElementById(containerId);
        if (svgContainer) {
            const svgElement = svgContainer.querySelector('svg');
            if (svgElement) {
            svgElement.style.height = '100%';
            }
        }
    };
    

    applyViewBoxCorrection('mapa-veredas-modal-inicial');
    applyViewBoxCorrection('mapa-veredas-modal-actual');
    
});
</script>