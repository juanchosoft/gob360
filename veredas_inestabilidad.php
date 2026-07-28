<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Vereda.php';
include './admin/classes/FactoresInestabilidadGeneral.php';
include './admin/classes/FactoresInestabilidadGobernacion.php';
include './admin/classes/Ciudad.php';

$inestabilidadId = isset($_REQUEST['inestabilidad']) ? intval($_REQUEST['inestabilidad']) : 10000;
$veredaId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$municipioId = isset($_REQUEST['mun']) ? intval($_REQUEST['mun']) : 0;
$departamento = Util::getDepartamentoPrincipal();

if (!$veredaId || !$municipioId) {
    require 'parametros_no_son_correctos.php';
    exit;
}

$veredaResponse = Vereda::getAll(array('id' => $veredaId));
$informacionVereda = $veredaResponse['output']['response'][0] ?? null;
$nombreVereda = $informacionVereda['nombre_vereda'] ?? '';

$munInfo = Ciudad::getInformacionCiudad(array('codigo_muncipio' => $municipioId));
$nombreMunicipio = $munInfo['output']['response'][0]['municipio'] ?? $municipioId;
$viewBox = $munInfo['output']['response'][0]['viewbox_svg'] ?? '0 45 1518.36 900';

$responseInest = FactoresInestabilidadGobernacion::getAll(null);

$colorDefecto = Util::getColorNeutroMapa();
$mapParams = [
    'codigo_municipio' => $municipioId,
    'inestabilidadId' => $inestabilidadId,
];
$mapDataInicial = FactoresInestabilidadGeneral::calcularColorVeredasInicial($mapParams);
$mapDataActual = FactoresInestabilidadGeneral::calcularColorVeredasActual($mapParams);
$veredasMapaInicial = $mapDataInicial['output']['valid'] ? $mapDataInicial['output']['response'] : [];
$veredasMapaActual = $mapDataActual['output']['valid'] ? $mapDataActual['output']['response'] : [];
$veredasMapa = $veredasMapaInicial;

$puntajeVeredaInicial = 0.0;
$colorVeredaInicial = $colorDefecto;
$puntajeVeredaActual = 0.0;
$colorVeredaActual = $colorDefecto;
foreach ($veredasMapaInicial as $vm) {
    if ((int) ($vm['id'] ?? 0) === $veredaId) {
        $puntajeVeredaInicial = (float) ($vm['cantidad'] ?? 0);
        $colorVeredaInicial = $vm['color_calculado'] ?? $colorDefecto;
        break;
    }
}
foreach ($veredasMapaActual as $vm) {
    if ((int) ($vm['id'] ?? 0) === $veredaId) {
        $puntajeVeredaActual = (float) ($vm['cantidad'] ?? 0);
        $colorVeredaActual = $vm['color_calculado'] ?? $colorDefecto;
        break;
    }
}

$arrParams = ['codigo_municipio' => $municipioId, 'inestabilidadId' => $inestabilidadId, 'veredaId' => $veredaId];
$dataCons = FactoresInestabilidadGeneral::consultarConsolidadMunicipioInicial($arrParams);
$dataConsActual = FactoresInestabilidadGeneral::consultarConsolidadMunicipioActual($arrParams);
$responseConsolidado = $dataCons['output']['valid'] ? $dataCons['output']['response'] : [];
$responseConsolidadoActual = $dataConsActual['output']['valid'] ? $dataConsActual['output']['response'] : [];
$tabs = $dataCons['output']['tabs'] ?? [];

$puntajesInicial = FactoresInestabilidadGeneral::getPuntajes($inestabilidadId, FactoresInestabilidadGeneral::TIPO_PUNTAJE_INICIAL);
$puntajesFinal = FactoresInestabilidadGeneral::getPuntajes($inestabilidadId, FactoresInestabilidadGeneral::TIPO_PUNTAJE_FINAL);

$badgeRangesInicial = [];
foreach ($puntajesInicial as $puntaje) {
    $badgeRangesInicial[$puntaje['name']] = [
        'bg' => $puntaje['color'],
        'text' => $puntaje['name'] == 'Neutro' ? '#0f172a' : '#fff',
        'border' => 'transparent',
        'range' => $puntaje['rango_desde'] . ' - ' . $puntaje['rango_hasta'],
    ];
}

$badgeRangesFinal = [];
foreach ($puntajesFinal as $puntaje) {
    $badgeRangesFinal[$puntaje['name']] = [
        'bg' => $puntaje['color'],
        'text' => $puntaje['name'] == 'Neutro' ? '#0f172a' : '#fff',
        'border' => 'transparent',
        'range' => $puntaje['rango_desde'] . ' - ' . $puntaje['rango_hasta'],
    ];
}

$optionInest = "<option value='10000'" . ($inestabilidadId == 10000 ? " selected" : "") . ">Todos</option>";
if (!empty($responseInest['output']['valid'])) {
    foreach ($responseInest['output']['response'] as $val) {
        $selected = ($val['id'] == $inestabilidadId) ? ' selected' : '';
        $optionInest .= "<option value='{$val['id']}'{$selected}>" . htmlspecialchars($val['nombre_categoria'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
}

function nf($n) { return number_format($n, 0, '.', ','); }
function nf_puntaje($n) { return number_format((float) $n, 2, '.', ','); }
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{--bg:#070A12;--card:rgba(255,255,255,.06);--stroke:rgba(255,255,255,.10);--text:rgba(255,255,255,.92);--muted:rgba(255,255,255,.68);--radius:22px;}
body{background:radial-gradient(1200px 900px at 12% 8%,rgba(46,107,255,.28),transparent 55%),linear-gradient(180deg,#070A12,#0B1020);color:var(--text);}
.card{border:1px solid var(--stroke)!important;background:linear-gradient(180deg,var(--card),rgba(255,255,255,.035))!important;box-shadow:0 18px 55px rgba(0,0,0,.55);border-radius:var(--radius)!important;}
.card-header{border-bottom:1px solid rgba(255,255,255,.09)!important;background:linear-gradient(90deg,rgba(46,107,255,.20),rgba(25,211,255,.12),rgba(255,255,255,.02))!important;padding:1rem 1.15rem!important;}
.card-header h5{color:#fff!important;font-weight:900!important;margin:0!important;}
.card-body{padding:1.05rem!important;}
.breadcrumb-item a{color:var(--muted)!important;}
.form-control,select.form-control{background:rgba(255,255,255,.06)!important;border:1px solid var(--stroke)!important;color:var(--text)!important;border-radius:14px!important;padding:10px 14px!important;font-weight:600!important;}
.btn-primary{background:linear-gradient(135deg,rgba(79,124,255,.35),rgba(155,92,255,.22))!important;border:1px solid rgba(79,124,255,.45)!important;color:#fff!important;border-radius:14px!important;font-weight:800!important;}
#contenido-mapa-inicial svg,#contenido-mapa-actual svg{width:100%;height:auto;max-height:340px;}
#contenido-mapa-inicial polygon,#contenido-mapa-inicial path,#contenido-mapa-actual polygon,#contenido-mapa-actual path{transition:opacity .2s;cursor:pointer;}
#contenido-mapa-inicial polygon:hover,#contenido-mapa-inicial path:hover,#contenido-mapa-actual polygon:hover,#contenido-mapa-actual path:hover{opacity:.75;filter:brightness(1.2);}
.vereda-info-block{display:flex;flex-wrap:wrap;gap:12px;padding:6px 0;}
.vereda-info-item{flex:1;min-width:140px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:12px 14px;text-align:center;}
.vereda-info-item strong{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:4px;}
.vereda-info-item span{font-size:13px;font-weight:700;color:#fff!important;}
.vereda-info-item .puntaje-valor{display:inline-flex;align-items:center;gap:8px;font-size:16px;font-weight:900;color:#fff!important;}
.vereda-info-item .color-dot{width:12px;height:12px;border-radius:50%;border:1px solid rgba(255,255,255,.35);flex-shrink:0;}
.nav-tabs .nav-link{background:rgba(255,255,255,.04)!important;border:1px solid rgba(255,255,255,.08)!important;color:var(--muted)!important;border-radius:12px 12px 0 0!important;padding:10px 18px;font-weight:700;margin-right:4px;}
.nav-tabs .nav-link.active{background:rgba(46,107,255,.20)!important;border-color:rgba(46,107,255,.35)!important;color:#fff!important;}
.table-responsive.tabla-informacion{border:1px solid rgba(255,255,255,.08);border-radius:14px;overflow-x:auto;margin-top:12px;}
.table{width:100%;border-collapse:collapse;}
.table th{padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff!important;text-align:center;border:none!important;background:rgba(255,255,255,.04)!important;border-bottom:2px solid rgba(255,255,255,.10)!important;}
.table td{padding:8px 4px;vertical-align:middle;text-align:center;color:#ffffff!important;border-bottom:1px solid rgba(255,255,255,.06)!important;font-size:12px;}
.map-tooltip{position:fixed;z-index:9999;pointer-events:none;background:rgba(0,0,0,.88);color:#fff;padding:8px 16px;border-radius:10px;font-size:18px;font-weight:800;border:1px solid rgba(255,255,255,.15);box-shadow:0 8px 30px rgba(0,0,0,.5);display:none;white-space:nowrap;}
.map-tooltip small{font-weight:400;font-size:13px;opacity:.6;margin-left:8px;}
</style>
<body class="">
    <div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Vereda: <?= htmlspecialchars($nombreVereda) ?></h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="factores_inestabilidad_general.php?inestabilidad=<?= $inestabilidadId ?>">Mapa Factores Inestabilidad</a></li>
                                                <li class="breadcrumb-item"><a href="municipios_inestabilidad.php?mun=<?= $municipioId ?>&inestabilidad=<?= $inestabilidadId ?>">Municipio</a></li>
                                                <li class="breadcrumb-item active"><?= htmlspecialchars($nombreVereda) ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-end">
                                                <div class="col-md-3">
                                                    <div class="form-group mb-0">
                                                        <label class="floating-label">Vereda</label>
                                                        <select class="form-control" onchange="window.location.href='veredas_inestabilidad.php?id='+this.value+'&mun=<?= $municipioId ?>&inestabilidad=<?= $inestabilidadId ?>'">
                                                            <?php
                                                            $veredaOptions = '';
                                                            foreach ($veredasMapa as $vm) {
                                                                $sel = ($vm['id'] == $veredaId) ? ' selected' : '';
                                                                $veredaOptions .= "<option value='{$vm['id']}'{$sel}>" . htmlspecialchars($vm['nombre_vereda'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                            }
                                                            echo $veredaOptions;
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mb-0">
                                                        <label class="floating-label">Factor Inestabilidad</label>
                                                        <select class="form-control" onchange="window.location.href='veredas_inestabilidad.php?id=<?= $veredaId ?>&mun=<?= $municipioId ?>&inestabilidad='+this.value"><?= $optionInest ?></select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <a href="municipios_inestabilidad.php?mun=<?= $municipioId ?>&inestabilidad=<?= $inestabilidadId ?>" class="btn btn-primary px-4 py-2"><i class="bi bi-arrow-left"></i> Volver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header"><h5 class="mb-0"><i class="bi bi-map-fill"></i> Mapa Inicial</h5></div>
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap gap-2 justify-content-center mb-2">
                                                <?php foreach ($badgeRangesInicial as $label => $cfg): ?>
                                                    <?php if ($label != 'Neutro'): ?>
                                                    <span class="badge rounded-pill px-3 py-2" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['text'] ?>;border:1px solid <?= $cfg['border'] ?>;font-weight:800;"><?= $label ?></span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <div id="contenido-mapa-inicial">
                                                <?php $v = $informacionVereda; ?>
                                                <svg viewBox="<?= htmlspecialchars($viewBox) ?>" xmlns="http://www.w3.org/2000/svg" stroke-width="1.2px" stroke="#fff" preserveAspectRatio="xMidYMid meet" style="width:100%;height:auto;">
                                                    <g>
                                                        <?php if (!empty($v['points'])): ?>
                                                            <polygon points="<?= strtoupper($v['points']) ?>" fill="<?= strtolower($colorVeredaInicial) ?>" fill-rule="evenodd" stroke-miterlimit="10" stroke-width="0.1px" data-cantidad="<?= nf_puntaje($puntajeVeredaInicial) ?>" title="<?= htmlspecialchars(strtoupper(str_replace('-', ' ', $v['nombre_vereda'])) . ' (' . nf_puntaje($puntajeVeredaInicial) . ')', ENT_QUOTES, 'UTF-8') ?>" />
                                                        <?php elseif (!empty($v['path'])): ?>
                                                            <path d="<?= $v['path'] ?>" style="fill:<?= strtolower($colorVeredaInicial) ?>;" stroke="#f3c5c5" stroke-miterlimit="10" stroke-width="3px" data-cantidad="<?= nf_puntaje($puntajeVeredaInicial) ?>" title="<?= htmlspecialchars(strtoupper(str_replace('-', ' ', $v['nombre_vereda'])) . ' (' . nf_puntaje($puntajeVeredaInicial) . ')', ENT_QUOTES, 'UTF-8') ?>" />
                                                        <?php endif; ?>
                                                        <?php if (!empty($v['tspan'])): ?>
                                                            <?= str_replace('<tspan', '<tspan style="fill:black;font-family:IBM Plex Sans;stroke-width:0.1px;"', $v['tspan']) ?>
                                                        <?php endif; ?>
                                                    </g>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><i class="bi bi-map-fill"></i> Mapa Actual</h5>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick="mostrarInformacionPilarByMunicipioVereda(0, <?= $veredaId ?>)"
                                                data-toggle="modal" data-target="#modalGeocalizacion">
                                                <i class="bi bi-geo-alt-fill me-1"></i> Geolocalización
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap gap-2 justify-content-center mb-2">
                                                <?php foreach ($badgeRangesFinal as $label => $cfg): ?>
                                                    <?php if ($label != 'Neutro'): ?>
                                                    <span class="badge rounded-pill px-3 py-2" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['text'] ?>;border:1px solid <?= $cfg['border'] ?>;font-weight:800;"><?= $label ?></span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <div id="contenido-mapa-actual">
                                                <svg viewBox="<?= htmlspecialchars($viewBox) ?>" xmlns="http://www.w3.org/2000/svg" stroke-width="1.2px" stroke="#fff" preserveAspectRatio="xMidYMid meet" style="width:100%;height:auto;">
                                                    <g>
                                                        <?php if (!empty($v['points'])): ?>
                                                            <polygon points="<?= strtoupper($v['points']) ?>" fill="<?= strtolower($colorVeredaActual) ?>" fill-rule="evenodd" stroke-miterlimit="10" stroke-width="0.1px" data-cantidad="<?= nf_puntaje($puntajeVeredaActual) ?>" title="<?= htmlspecialchars(strtoupper(str_replace('-', ' ', $v['nombre_vereda'])) . ' (' . nf_puntaje($puntajeVeredaActual) . ')', ENT_QUOTES, 'UTF-8') ?>" />
                                                        <?php elseif (!empty($v['path'])): ?>
                                                            <path d="<?= $v['path'] ?>" style="fill:<?= strtolower($colorVeredaActual) ?>;" stroke="#f3c5c5" stroke-miterlimit="10" stroke-width="3px" data-cantidad="<?= nf_puntaje($puntajeVeredaActual) ?>" title="<?= htmlspecialchars(strtoupper(str_replace('-', ' ', $v['nombre_vereda'])) . ' (' . nf_puntaje($puntajeVeredaActual) . ')', ENT_QUOTES, 'UTF-8') ?>" />
                                                        <?php endif; ?>
                                                        <?php if (!empty($v['tspan'])): ?>
                                                            <?= str_replace('<tspan', '<tspan style="fill:black;font-family:IBM Plex Sans;stroke-width:0.1px;"', $v['tspan']) ?>
                                                        <?php endif; ?>
                                                    </g>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header"><h5><i class="bi bi-info-circle-fill"></i> <?= htmlspecialchars($nombreVereda) ?></h5></div>
                                        <div class="card-body">
                                            <div class="vereda-info-block">
                                                <div class="vereda-info-item"><strong>Vereda</strong><span><?= htmlspecialchars($nombreVereda) ?></span></div>
                                                <div class="vereda-info-item"><strong>Municipio</strong><span><?= htmlspecialchars($nombreMunicipio) ?></span></div>
                                                <div class="vereda-info-item"><strong>Código</strong><span><?= htmlspecialchars($informacionVereda['codigo_vereda'] ?? '—') ?></span></div>
                                                <div class="vereda-info-item">
                                                    <strong>Puntaje Inicial</strong>
                                                    <span class="puntaje-valor">
                                                        <span class="color-dot" style="background:<?= htmlspecialchars($colorVeredaInicial, ENT_QUOTES, 'UTF-8') ?>;"></span>
                                                        <?= nf_puntaje($puntajeVeredaInicial) ?>
                                                    </span>
                                                </div>
                                                <div class="vereda-info-item">
                                                    <strong>Puntaje Actual</strong>
                                                    <span class="puntaje-valor">
                                                        <span class="color-dot" style="background:<?= htmlspecialchars($colorVeredaActual, ENT_QUOTES, 'UTF-8') ?>;"></span>
                                                        <?= nf_puntaje($puntajeVeredaActual) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="tbl_municipio_id" value="<?= $municipioId ?>">
                            <input type="hidden" id="pilarId" value="10000">
                            <input type="hidden" id="latitud" value="<?= htmlspecialchars($munInfo['output']['response'][0]['latitud'] ?? '') ?>">
                            <input type="hidden" id="longitud" value="<?= htmlspecialchars($munInfo['output']['response'][0]['longitud'] ?? '') ?>">

                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header"><h5><i class="bi bi-bar-chart-fill"></i> Avance por Factor Inestabilidad</h5></div>
                                        <div class="card-body" id="divConsolidado">
                                            <?php if (!empty($tabs)): ?>
                                                <ul class="nav nav-tabs mb-3" id="myTab" role="tablist" style="border-bottom:1px solid rgba(255,255,255,.08);gap:4px;">
                                                    <?php foreach ($tabs as $index => $tab): ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link <?= $index === 0 ? 'active' : '' ?>"
                                                                id="tab-<?= $tab['id'] ?>" data-toggle="pill"
                                                                href="#content-<?= $tab['id'] ?>" role="tab"
                                                                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
                                                                style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.70);border-radius:12px 12px 0 0;padding:10px 20px;font-weight:700;margin-right:4px;transition:all .2s;">
                                                                <span class="tab-icon-wrap"><img src="<?= htmlspecialchars($tab['icono'] ?? 'assets/iconos/gobierno.png') ?>" width="22" height="22" style="border-radius:4px;vertical-align:middle;margin-right:6px;"></span>
                                                                <span class="tab-label"><?= htmlspecialchars($tab['nombre']) ?></span>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <div class="tab-content" id="myTabContent">
                                                    <?php foreach ($tabs as $index => $tab): ?>
                                                        <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>"
                                                            id="content-<?= $tab['id'] ?>" role="tabpanel"
                                                            aria-labelledby="tab-<?= $tab['id'] ?>">
                                                            <?php
                                                            $areaInicial = array_filter($responseConsolidado, fn($i) => ($i['inestabilidad_id'] ?? 0) == $tab['id']);
                                                            $areaActual = array_filter($responseConsolidadoActual, fn($i) => ($i['inestabilidad_id'] ?? 0) == $tab['id']);

                                                            $agrupado = [];
                                                            foreach ($areaInicial as $item) {
                                                                 $clave = strtoupper(trim($item['factor'])) . '|' . strtoupper(trim($item['tipo_medicion']));
                                                                 $cant = floatval($item['total_cantidad']);
                                                                 if (!isset($agrupado[$clave])) {
                                                                     $agrupado[$clave] = [
                                                                         'factor' => strtoupper(trim($item['factor'])),
                                                                         'tipo_medicion' => strtoupper(trim($item['tipo_medicion'])),
                                                                         'total_cantidad_inicial' => $cant,
                                                                         'total_cantidad_actual' => 0.0,
                                                                         'icono' => $item['icono'] ?? '',
                                                                         'tbl_factor_id' => intval($item['tbl_factor_id']),
                                                                         'pilar' => $item['pilar'] ?? '',
                                                                         'puntaje' => $item['puntaje'] ?? ''
                                                                     ];
                                                                 } else { $agrupado[$clave]['total_cantidad_inicial'] += $cant; }
                                                            }
                                                            foreach ($areaActual as $item) {
                                                                 $clave = strtoupper(trim($item['factor'])) . '|' . strtoupper(trim($item['tipo_medicion']));
                                                                 $cant = floatval($item['total_cantidad_actual']);
                                                                 if (isset($agrupado[$clave])) { $agrupado[$clave]['total_cantidad_actual'] += $cant; }
                                                                 else {
                                                                     $agrupado[$clave] = [
                                                                         'factor' => strtoupper(trim($item['factor'])),
                                                                         'tipo_medicion' => strtoupper(trim($item['tipo_medicion'])),
                                                                         'total_cantidad_inicial' => 0.0,
                                                                         'total_cantidad_actual' => $cant,
                                                                         'icono' => $item['icono'] ?? '',
                                                                         'tbl_factor_id' => intval($item['tbl_factor_id']),
                                                                         'pilar' => $item['pilar'] ?? '',
                                                                         'puntaje' => $item['puntaje'] ?? ''
                                                                     ];
                                                                 }
                                                            }
                                                            ?>
                                                            <?php if (!empty($agrupado)): ?>
                                                                 <div style="border:1px solid rgba(255,255,255,.08);border-radius:14px;margin-top:12px;overflow-x:auto;">
                                                                     <table class="table table-hover mb-0" style="width:100%;border-collapse:collapse;">
                                                                         <thead>
                                                                             <tr style="background:rgba(255,255,255,.04);border-bottom:2px solid rgba(255,255,255,.10);">
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:36px;">Ícono</th>
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:left;border:none;">Factor</th>
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:105px;">Cant. Inicial</th>
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:105px;">Cant. Actual</th>
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:60px;">Unidad</th>
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:50px;">Geo</th>
                                                                             </tr></thead>
                                                                         <tbody>
                                                                             <?php foreach ($agrupado as $d): ?>
                                                                                 <tr style="background:transparent;border-bottom:1px solid rgba(255,255,255,.06);">
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;"><img src="<?= htmlspecialchars($d['icono']) ?>" alt="" width="26" style="border-radius:6px;"></td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:left;font-size:12px;font-weight:600;color:#ffffff;">
    <?= htmlspecialchars($d['factor']) ?>
    <br><small style="font-weight:400;color:rgba(255,255,255,.55);">Puntaje: <?= htmlspecialchars($d['puntaje']) ?></small>
</td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;"><span style="font-size:11px;font-weight:600;color:#ffffff;background:rgba(46,125,50,.15);padding:3px 10px;border-radius:6px;display:inline-block;border:1px solid rgba(46,125,50,.25);"><?= nf($d['total_cantidad_inicial']) ?></span></td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;"><span style="font-size:11px;font-weight:700;color:#ffffff;background:rgba(30,136,229,.15);padding:3px 10px;border-radius:6px;display:inline-block;border:1px solid rgba(30,136,229,.25);"><?= nf($d['total_cantidad_actual']) ?></span></td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;font-size:11px;color:#ffffff;font-weight:600;"><?= htmlspecialchars($d['tipo_medicion']) ?></td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;">
                                                                                         <button style="background:rgba(26,188,156,.15);border:1px solid rgba(26,188,156,.25);border-radius:6px;padding:3px 6px;border:none;cursor:pointer;line-height:1;"
                                                                                              onclick="mostrarInformacionPilarByMunicipioVereda(<?= $d['tbl_factor_id'] ?>, <?= $veredaId ?>)">
                                                                                             <img src="assets/iconos/geo.png" alt="Geo" width="16" style="filter:brightness(0) invert(1);">
                                                                                         </button>
                                                                                     </td>
                                                                                 </tr>
                                                                             <?php endforeach; ?>
                                                                         </tbody>
                                                                     </table>
                                                                 </div>
                                                            <?php else: ?>
                                                                <p class="text-center text-muted p-4">No hay datos disponibles para esta categoría.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center text-muted p-4"><i class="bi bi-inbox" style="font-size:2rem;"></i><p class="mt-2">No hay información disponible para esta vereda y factor de inestabilidad.</p></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Geolocalización -->
    <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl centered" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Geolocalización: <span id="nombrePilar"></span></h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body"><div id="map" style="height:600px;width:100%;border-radius:14px;overflow:hidden;"></div></div>
            </div>
        </div>
    </div>

    <div id="mapTooltip" class="map-tooltip"></div>
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script type="text/javascript" src="admin/js/mapa_municipio_geo.js"></script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>
    <script>
        function mostrarInformacionPilarByMunicipioVereda(factor_id, vereda_id) {
            var municipio = <?= $municipioId ?>;
            var q = { op: "getmapapilaresbymunicipioId", pilarId: 10000, codigoMunicipio: municipio, factor_id: factor_id, veredaId: vereda_id };
            UTIL.cursorBusy();
            $.ajax({
                data: q, type: "GET", dataType: "json", url: "admin/ajax/rqst.php",
                success: function(data) {
                    UTIL.cursorNormal();
                    if (data.output && data.output.valid) {
                        informacionMapaFactores = data.output.response || [];
                        if (informacionMapaFactores.length > 0) { $("#nombrePilar").empty().append(informacionMapaFactores[0]["pilar"]); }
                        $("#modalGeocalizacion").one("shown.bs.modal", function() { resetMapa(); initMap(); });
                        $("#modalGeocalizacion").modal("show");
                    } else {
                        UTIL.mostrarMensajeError(data.output.response ? data.output.response.content : "Sin datos");
                    }
                },
                error: function() { UTIL.cursorNormal(); UTIL.mostrarMensajeError("Error de conexión"); }
            });
        }
        function mostrarAlerta(tipo, mensaje) {
            if (tipo === "error") { alert("❌ " + mensaje); }
        }
        document.addEventListener("DOMContentLoaded", function() {
            $('#modalGeocalizacion').on('shown.bs.modal', function() {
                setTimeout(function() {
                    if (typeof markers !== 'undefined' && markers.length > 0 && typeof map !== 'undefined' && map) {
                        const bounds = new google.maps.LatLngBounds();
                        markers.forEach(function(m) { if (m && m.getPosition) bounds.extend(m.getPosition()); });
                        if (!bounds.isEmpty()) map.fitBounds(bounds);
                        if (map.getZoom() > 16) map.setZoom(14);
                    }
                }, 500);
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
            tabLinks.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    tabLinks.forEach(l => l.classList.remove('active'));
                    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show', 'active'));
                    this.classList.add('active');
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) target.classList.add('show', 'active');
                });
            });
        });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var tip = document.getElementById("mapTooltip");
        function showTip(e, text) {
            tip.innerHTML = text;
            tip.style.display = "block";
            moveTip(e);
        }
        function moveTip(e) {
            var x = e.clientX + 16, y = e.clientY + 16;
            if (x + 250 > window.innerWidth) x = e.clientX - 250;
            tip.style.left = x + "px";
            tip.style.top = y + "px";
        }
        function hideTip() { tip.style.display = "none"; }
        document.querySelectorAll("#contenido-mapa-inicial polygon, #contenido-mapa-inicial path, #contenido-mapa-actual polygon, #contenido-mapa-actual path").forEach(function(el) {
            el.addEventListener("mouseenter", function(e) {
                var t = this.getAttribute("title") || this.getAttribute("data-name") || "Vereda";
                var c = this.getAttribute("data-cantidad");
                showTip(e, c ? t + "<small>punt: " + c + "</small>" : t);
            });
            el.addEventListener("mousemove", moveTip);
            el.addEventListener("mouseleave", hideTip);
        });
    });
    </script>
</body>
</html>
