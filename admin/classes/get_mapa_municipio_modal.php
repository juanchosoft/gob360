<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * formatea número sin decimales si es entero, con 2 decimales ó
 * punto como separador de miles y coma como separador decimal
 * @param mixed $num 
 * @return string
 */
function format_numero_php($num) {

    if (empty($num) && $num !== 0 && $num !== '0') {
        return "0";
    }
    $numFloat = floatval($num);


    if (abs($numFloat - round($numFloat)) < 0.00001) {
        return number_format($numFloat, 0, ',', '.');
    }

    else {
        return number_format($numFloat, 2, ',', '.');
    }
}

require_once 'SessionData.php';
require_once 'DbConection.php';
require_once 'Util.php';
require_once 'Colombia.php';
require_once 'Ciudad.php';
require_once 'Pilar.php';
require_once 'Secretarias.php'; 


$codigoDepartamento = isset($_GET['codigo_departamento']) ? intval($_GET['codigo_departamento']) : 0;
$codigoMunicipio = isset($_GET['codigo_municipio']) ? intval($_GET['codigo_municipio']) : 0;

$secretariaUnica = isset($_GET['secretaria_unica']) ? intval($_GET['secretaria_unica']) : 0;
$pilarId = isset($_GET['pilar_id']) ? intval($_GET['pilar_id']) : 0; 

$accionActualRaw = isset($_GET['accion']) ? $_GET['accion'] : ''; 
$accionActual = urldecode($accionActualRaw); 

if (empty($accionActual) && !empty($accionActualRaw)) {

    $accionActual = str_replace('%20', ' ', $accionActualRaw); 
}

$municipioInfoData = Ciudad::getInformacionCiudad(array('codigo_muncipio' => $codigoMunicipio)); 

if (!is_array($municipioInfoData) || !isset($municipioInfoData['output']['response'])) {
    $informacionMunicipio = null;
} else {
    $informacionMunicipio = $municipioInfoData['output']['response'][0] ?? null;
}
$viewBoxUniversal = '-1000 -1000 3000 3000';
$arrMapaVeredas = [
    'codigo_departamento' => $codigoDepartamento,
    'codigo_municipio'    => $codigoMunicipio,
    'secretariaId'        => $secretariaUnica,
    'accion'              => $accionActual
];

$accionFactorInicial = $accionActual; 

$dataInicial = Colombia::calcularColorMapaInicialByFactor($arrMapaVeredas);
$veredasMapa1 = $dataInicial['output']['response'] ?? [];
$isValid1 = $dataInicial['output']['valid'] ?? false;

$esFactorInicialVacio = isset($veredasMapa1[0]['valor_factor']) && $veredasMapa1[0]['valor_factor'] == 0;
$esVeredasArrayVacio = empty($veredasMapa1); 

if ($isValid1 && !$esVeredasArrayVacio && $esFactorInicialVacio) {
    
    $arrConsolidadoMunicipal = [
        'municipioId' => $codigoMunicipio,
        'secretariaId' => $secretariaUnica,
    ];
    
    $responseConsolidadoLocal = Secretarias::getFactoresPrincipalesConsolidado($arrConsolidadoMunicipal);
    
    if (isset($responseConsolidadoLocal['output']['valid']) && $responseConsolidadoLocal['output']['valid']) {
        $mejorFactorLocal = $responseConsolidadoLocal['output']['response'][0] ?? null;
        
        if ($mejorFactorLocal) {
            $nuevoFactor = $mejorFactorLocal['factor'] ?? $accionActual;
            
            if ($nuevoFactor != $accionFactorInicial) {
                
                $arrMapaVeredas['accion'] = $nuevoFactor; 

                $dataInicial = Colombia::calcularColorMapaInicialByFactor($arrMapaVeredas);
                $veredasMapa1 = $dataInicial['output']['response'] ?? [];

                $accionActual = $nuevoFactor; 

                $pilarId = intval($mejorFactorLocal['tbl_pilar_id'] ?? $pilarId); 
            }
        }
    }

}

$arrMapaVeredas['accion'] = $accionActual; 
$dataActual = Colombia::calcularColorMapaActualByFactor($arrMapaVeredas);
$veredasMapa2 = $dataActual['output']['response'] ?? [];
$isValid2 = $dataActual['output']['valid'] ?? false;

$esFactorActualVacio = isset($veredasMapa2[0]['valor_factor']) && $veredasMapa2[0]['valor_factor'] == 0;

if ($isValid2 && !$esVeredasArrayVacio && $esFactorActualVacio) {
    
    
    if (!isset($responseConsolidadoLocal) || !$responseConsolidadoLocal['output']['valid']) {
    $arrConsolidadoMunicipal = ['municipioId' => $codigoMunicipio, 'secretariaId' => $secretariaUnica];
    $responseConsolidadoLocal = Secretarias::getFactoresPrincipalesConsolidado($arrConsolidadoMunicipal);
    }
    
    if (isset($responseConsolidadoLocal['output']['valid']) && $responseConsolidadoLocal['output']['valid']) {
        $mejorFactorLocal = $responseConsolidadoLocal['output']['response'][0] ?? null;
        
        if ($mejorFactorLocal) {
            $nuevoFactor = $mejorFactorLocal['factor'] ?? $accionActual;

            
            if ($nuevoFactor != $accionActual) {
                
                $arrMapaVeredas['accion'] = $nuevoFactor; 

                $dataActual = Colombia::calcularColorMapaActualByFactor($arrMapaVeredas);
                $veredasMapa2 = $dataActual['output']['response'] ?? [];

                $accionActual = $nuevoFactor; 
                $pilarId = intval($mejorFactorLocal['tbl_pilar_id'] ?? $pilarId); 
                //
            }
        }
    }
}

$veredasCount = count($veredasMapa1); 


$arrConsolidado = [
    'municipioId' => $codigoMunicipio,
    'pilarId' => $pilarId, 
];

$datosTablaConsolidadoRaw = [];
$datosTablaConsolidado = []; 

$responseConsolidado = Secretarias::getConsolidadoFactoresPorPilar($arrConsolidado);


if (
    is_array($responseConsolidado) && 
    isset($responseConsolidado['output']) &&
    isset($responseConsolidado['output']['response']) && 
    is_array($responseConsolidado['output']['response'])
) {
    
    $datosTablaConsolidadoRaw = $responseConsolidado['output']['response'];

    foreach ($datosTablaConsolidadoRaw as $dato) {
        
        $inicialRaw = $dato['total_cantidad'] ?? null;
        $actualRaw = $dato['total_cantidad_actual'] ?? null;
        $unidadRaw = $dato['tipo_medicion'] ?? null;
        $factorRaw = $dato['factor'] ?? null;

        $inicialDisplay = (!empty($inicialRaw) || $inicialRaw === 0 || $inicialRaw === '0') 
        ? format_numero_php($inicialRaw) 
        : '0';

        $actualDisplay = (!empty($actualRaw) || $actualRaw === 0 || $actualRaw === '0') 
        ? format_numero_php($actualRaw) 
        : '0';

        $factorDisplay = !empty($factorRaw) ? htmlspecialchars($factorRaw) : 'Sin factor actualizado';
        $unidadDisplay = !empty($unidadRaw) ? htmlspecialchars($unidadRaw) : 'Unidad'; 

        $datosTablaConsolidado[] = [
            'factor' => $factorDisplay, 
            'inicial' => $inicialDisplay, 
            'actual' => $actualDisplay, 
            'unidad' => $unidadDisplay, 
        ];
    }

} 
$viewBoxActual = !empty($informacionMunicipio['viewbox_svg']) ? $informacionMunicipio['viewbox_svg'] : '-100 45 1518.36 900';

?>


<div >
<style>
.mapa-dual-container{
display:flex;
gap:20px;
padding:10px 0;
}
.mapa-box{
flex:1;
padding:10px;
border:1px solid #ddd;
border-radius:12px;
background:#fff;
box-shadow:0 1px 6px rgba(0,0,0,.08);
}
.mapa-box h5{
text-align:center;
margin:0 0 8px;
}
.mapa-box svg{
width:100%;
height:400px;
border-radius:8px;
}
.mapa-box svg text,
.mapa-box svg tspan{
fill:#000;
stroke:none;
}
</style>

<div class="mapa-dual-container">
<?php
$urlVereda = "municipios_secretaria_informacion.php?mun={$codigoMunicipio}&dep={$codigoDepartamento}&secretaria={$secretariaUnica}";
?>
<!-- MAPA INICIAL -->
<div class="mapa-box">
    <h5 class="text-primary">Mapa Inicial</h5>

    <?php if ($isValid1 && !empty($veredasMapa1)): ?>
        <svg xmlns="http://www.w3.org/2000/svg"
        id="mapa-veredas-modal-inicial"
        viewBox="<?= htmlspecialchars($viewBoxActual) ?>"
        preserveAspectRatio="xMidYMid meet">

        <?php foreach ($veredasMapa1 as $v): ?>
            <?php 
                $color_inicial = $v["color_calculado"] ?? "#BFBFBF"; 
                $veredaId = htmlspecialchars($v["vereda_id"]);
            ?>
            
            <g id="ini_<?= $veredaId ?>">

                <?php if (!empty($v["points"])): ?>
                    <polygon 
                        points="<?= strtoupper(htmlspecialchars($v["points"])) ?>"
                        fill="<?= htmlspecialchars($color_inicial) ?>"
                        class="veredaClick veredaLink"
                        stroke="#000" stroke-width="0.25"
                        onclick="location.href='<?= $urlVereda ?>'"
                        style="cursor:pointer"
                    />
                <?php elseif (!empty($v["path"])): ?>
                    <path 
                        d="<?= htmlspecialchars($v["path"]) ?>"
                        fill="<?= htmlspecialchars($color_inicial) ?>"
                        class="veredaClick veredaLink"
                        stroke="#000" stroke-width="0.25"
                        onclick="location.href='<?= $urlVereda ?>'"
                        style="cursor:pointer"
                    />
                <?php endif; ?>

                <?= !empty($v["tspan"]) ? $v["tspan"] : "" ?>
            </g>

        <?php endforeach; ?>

        </svg>
        <?php
$colorNeutro = Util::getColorNeutroMapa();
$veredasPintadas = [];

foreach ($veredasMapa1 as $v) {
    if (!empty($v["color_calculado"]) && $v["color_calculado"] !== $colorNeutro) {
        $veredasPintadas[] = $v["nombre_vereda"];
    }
}
?>

<?php if (!empty($veredasPintadas)): ?>
    <!-- <div class="mt-3 p-2" style="background:#f8f9fa;border-radius:8px;">
        <h6 class="text-dark"><strong>Veredas con registros:</strong></h6>
        <ul style="padding-left:18px;">
            <?php foreach ($veredasPintadas as $nombre): ?>
                <li><?= htmlspecialchars($nombre) ?></li>
            <?php endforeach; ?>
        </ul>
    </div> -->
<?php else: ?>
    <div class="mt-3 text-muted">
        <em>No hay veredas pintadas con este factor.</em>
    </div>
<?php endif; ?>

    <?php else: ?>
        <p class="text-center text-muted">Sin información</p>
    <?php endif; ?>
</div>

<!-- MAPA ACTUAL -->
<div class="mapa-box">
    <h5 class="text-success">Mapa Actual</h5>

    <?php if ($isValid2 && !empty($veredasMapa2)): ?>
        <svg xmlns="http://www.w3.org/2000/svg"
        id="mapa-veredas-modal-actual"
        viewBox="<?= htmlspecialchars($viewBoxActual) ?>"
        preserveAspectRatio="xMidYMid meet">

        <?php foreach ($veredasMapa2 as $v): ?>

            <?php
                $color_actual = $v["color_calculado"] ?? "#1ABC9C";
                $veredaId = htmlspecialchars($v["vereda_id"]);  // ← CORREGIDO
            ?>

            <g id="act_<?= $veredaId ?>">

                <?php if (!empty($v["points"])): ?>
                    <polygon 
                        points="<?= strtoupper(htmlspecialchars($v["points"])) ?>"
                        fill="<?= htmlspecialchars($color_actual) ?>"
                        class="veredaClick veredaLink"
                        stroke="#000" stroke-width="0.25"
                        onclick="location.href='<?= $urlVereda ?>'"
                        style="cursor:pointer" 
                    />

                <?php elseif (!empty($v["path"])): ?>
                    <path 
                        d="<?= htmlspecialchars($v["path"]) ?>"
                        fill="<?= htmlspecialchars($color_actual) ?>"
                        class="veredaClick veredaLink"
                        stroke="#000" stroke-width="0.25"
                        onclick="location.href='<?= $urlVereda ?>'"
                        style="cursor:pointer" 
                    />
                <?php endif; ?>

                <?= !empty($v["tspan"]) ? $v["tspan"] : "" ?>
            </g>

        <?php endforeach; ?>

        </svg>

        <?php
        // Mostrar veredas pintadas
        $colorNeutro = Util::getColorNeutroMapa();
        $veredasPintadasAct = [];

        foreach ($veredasMapa2 as $v) {
            if (!empty($v["color_calculado"]) && $v["color_calculado"] !== $colorNeutro) {
                $veredasPintadasAct[] = $v["nombre_vereda"];
            }
        }
        ?>

        <?php if (!empty($veredasPintadasAct)): ?>
            <!-- <div class="mt-3 p-2" style="background:#f8f9fa;border-radius:8px;">
                <h6 class="text-dark"><strong>Veredas con registros:</strong></h6>
                <ul style="padding-left:18px;">
                    <?php foreach ($veredasPintadasAct as $nombre): ?>
                        <li><?= htmlspecialchars($nombre) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div> -->
        <?php else: ?>
            <div class="mt-3 text-muted">
                <em>Todos los factores de este municipio han sido atendidos y actualmente no tienen factores por resolver</em>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p class="text-center text-muted">Sin información</p>
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