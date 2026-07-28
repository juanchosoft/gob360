<?php

include './admin/include/head.php';

require './admin/include/generic_classes.php';

function getUrl()
{
    $port = $_SERVER["SERVER_PORT"];
    $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
    $url = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $nameServer,
        $_SERVER['REQUEST_URI']
    );
    $final =  str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists == !false) {
        $final =  substr($final, 0, $exists);
        return $final;
    } else {
        return $final;
    }
}

require_once './admin/include/generic_classes.php';
include './admin/classes/FactoresInestabilidadGeneral.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
require './admin/classes/Departamento.php';
require './admin/classes/Maing.php';
require './admin/classes/Pilar.php';
require './admin/classes/Mapa.php';
include './admin/classes/FactoresInestabilidadGobernacion.php';
include './admin/db/coloress.php';

$userType = SessionData::getUserType();
if ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde()) {
    header('Location: dashboard.php');
    exit;
}

$arr = Maing::getDataMain(null);
$visitas = $arr['output']['visitas'];
$impactada = $arr['output']['impactada'];
$inversion = $arr['output']['inversion'];

$inestabilidadId = isset($_REQUEST['inestabilidad']) ? intval($_REQUEST['inestabilidad']) : 10000;
$codigoTodos = 10000;

$responseInestabilidad = FactoresInestabilidadGobernacion::getAll(null);
if (!empty($responseInestabilidad['output']['valid'])) {
    $arrInestabilidad = $responseInestabilidad['output']['response'];
    $optionInestabilidad = "<option value='$codigoTodos'" . ($inestabilidadId == $codigoTodos ? " selected" : "") . ">Todos</option>";
    foreach ($arrInestabilidad as $val) {
        $selected = ($val['id'] == $inestabilidadId) ? ' selected' : '';
        $optionInestabilidad .= "<option value='{$val['id']}'{$selected}>" . htmlspecialchars($val['nombre_categoria'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
} else {
    $optionInestabilidad = "<option value='$codigoTodos'" . ($inestabilidadId == $codigoTodos ? " selected" : "") . ">Todos</option>";
}

$arrParams = ['codigo_departamento' => Util::getDepartamentoPrincipal(), 'inestabilidadId' => $inestabilidadId];

$dataInicial = FactoresInestabilidadGeneral::calcularColorMapaInicial($arrParams);
$dataActual = FactoresInestabilidadGeneral::calcularColorMapaActual($arrParams);

$santanderInicial = $dataInicial['output']['valid'] ? $dataInicial['output']['response'] : [];
$santanderActual = $dataActual['output']['valid'] ? $dataActual['output']['response'] : [];

$tablaPuntajesMunicipios = [];
foreach ($santanderInicial as $municipio) {
    if (!is_array($municipio)) {
        continue;
    }
    $codigo = $municipio['codigo_muncipio'] ?? '';
    if ($codigo === '') {
        continue;
    }
    $tablaPuntajesMunicipios[$codigo] = [
        'codigo_muncipio' => $codigo,
        'municipio' => $municipio['municipio'] ?? '',
        'puntaje_inicial' => (float) ($municipio['suma'] ?? 0),
        'color_inicial' => $municipio['color'] ?? Util::getColorNeutroMapa(),
        'puntaje_actual' => 0.0,
        'color_actual' => Util::getColorNeutroMapa(),
    ];
}
foreach ($santanderActual as $municipio) {
    if (!is_array($municipio)) {
        continue;
    }
    $codigo = $municipio['codigo_muncipio'] ?? '';
    if ($codigo === '') {
        continue;
    }
    if (!isset($tablaPuntajesMunicipios[$codigo])) {
        $tablaPuntajesMunicipios[$codigo] = [
            'codigo_muncipio' => $codigo,
            'municipio' => $municipio['municipio'] ?? '',
            'puntaje_inicial' => 0.0,
            'color_inicial' => Util::getColorNeutroMapa(),
            'puntaje_actual' => 0.0,
            'color_actual' => Util::getColorNeutroMapa(),
        ];
    }
    $tablaPuntajesMunicipios[$codigo]['puntaje_actual'] = (float) ($municipio['suma'] ?? 0);
    $tablaPuntajesMunicipios[$codigo]['color_actual'] = $municipio['color'] ?? Util::getColorNeutroMapa();
    if (empty($tablaPuntajesMunicipios[$codigo]['municipio'])) {
        $tablaPuntajesMunicipios[$codigo]['municipio'] = $municipio['municipio'] ?? '';
    }
}
usort($tablaPuntajesMunicipios, function ($a, $b) {
    return strcasecmp($a['municipio'], $b['municipio']);
});

$municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

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
        'text' => $puntaje['name'] == 'Neutro' ? '#0f172a' : '#edf5eb',
        'border' => 'transparent',
        'range' => $puntaje['rango_desde'] . ' - ' . $puntaje['rango_hasta'],
    ];
}

$etiquetasColorInicial = FactoresInestabilidadGeneral::etiquetasPorColorDesdeBadges($badgeRangesInicial);
$etiquetasColorFinal = FactoresInestabilidadGeneral::etiquetasPorColorDesdeBadges($badgeRangesFinal);
$resumenColoresInicial = FactoresInestabilidadGeneral::resumenColoresMapa($santanderInicial, 'color', $etiquetasColorInicial);
$resumenColoresActual = FactoresInestabilidadGeneral::resumenColoresMapa($santanderActual, 'color', $etiquetasColorFinal);

$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar()|| $userType == Util::Auxiliar_secret_gob());
$isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
?>

<style>
:root{
  --dep-bg:#0b1120;
  --dep-card:#10172b;
  --dep-border:rgba(255,255,255,.08);
  --dep-text:rgba(255,255,255,.90);
  --dep-muted:rgba(255,255,255,.55);
  --dep-primary:#4f7cff;
  --dep-accent:#9b5cff;
  --dep-shadow:0 18px 40px rgba(0,0,0,.45);
  --dep-radius:20px;
  --dep-radius-sm:14px;
}
body{ background:var(--dep-bg) !important; }
.pcoded-main-container .pcoded-wrapper,
.pcoded-main-container .pcoded-content,
.pcoded-main-container .pcoded-inner-content,
.pcoded-main-container .main-body,
.pcoded-main-container .page-wrapper{ background:transparent !important; }
.card{
  background:var(--dep-card) !important;
  border:1px solid var(--dep-border) !important;
  border-radius:var(--dep-radius) !important;
  box-shadow:var(--dep-shadow) !important;
  backdrop-filter:blur(12px);
}
.card-header{
  background:transparent !important;
  border-bottom:1px solid var(--dep-border) !important;
  padding:1.2rem 1.5rem !important;
}
.card-header h5{
  color:var(--dep-text) !important;
  font-weight:800 !important;
  letter-spacing:.02em;
}
.card-body{ padding:1.5rem !important; }

.page-header .page-block h5{ color:var(--dep-text) !important; font-weight:900; }
.breadcrumb-item, .breadcrumb-item a{ color:var(--dep-muted) !important; }
.breadcrumb-item.active{ color:var(--dep-text) !important; }

.form-control, select.form-control{
  background:rgba(255,255,255,.06) !important;
  border:1px solid rgba(255,255,255,.12) !important;
  color:var(--dep-text) !important;
  border-radius:var(--dep-radius-sm) !important;
  padding:10px 14px !important;
  font-weight:600 !important;
}
.form-control:focus{
  border-color:var(--dep-primary) !important;
  box-shadow:0 0 0 3px rgba(79,124,255,.15) !important;
}
.form-control option{ background:#10172b !important; color:#fff !important; }
label{ color:var(--dep-text) !important; font-weight:700 !important; }

.btn-primary{
  background:linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
  border:1px solid rgba(79,124,255,.45) !important;
  color:#fff !important;
  border-radius:var(--dep-radius-sm) !important;
  font-weight:800 !important;
  transition:all .2s;
}
.btn-primary:hover{ filter:brightness(1.15); }

.cuerpoMapa svg{ width:100%; height:auto; }
.municipios{ transition:opacity .2s, filter .2s; cursor:pointer; }
.municipios:hover{ opacity:.75; filter:brightness(1.2); }

.mapa-resumen-colores{
  background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.08);
  border-radius:12px;
  padding:12px 14px;
  margin-top:14px;
  margin-bottom:0;
}
.mapa-resumen-colores-total{
  font-size:12px;
  font-weight:800;
  text-transform:uppercase;
  letter-spacing:.05em;
  color:var(--dep-muted);
  margin-bottom:10px;
}
.mapa-resumen-colores-grid{
  display:flex;
  flex-wrap:wrap;
  gap:8px 14px;
}
.mapa-resumen-colores-item{
  display:inline-flex;
  align-items:center;
  gap:8px;
  font-size:13px;
  color:var(--dep-text);
  background:rgba(255,255,255,.03);
  border:1px solid rgba(255,255,255,.06);
  border-radius:999px;
  padding:6px 12px;
}
.mapa-resumen-dot{
  width:12px;
  height:12px;
  border-radius:50%;
  border:1px solid rgba(255,255,255,.35);
  flex-shrink:0;
}
.mapa-resumen-etiqueta{font-weight:700;}
.mapa-resumen-valor{font-weight:800;}
.mapa-resumen-valor small{font-weight:600;color:var(--dep-muted);}

.tab-icon-wrap{ width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; margin-right:6px; }
.tab-icon-wrap img{ max-width:100%; max-height:100%; border-radius:6px; }

#divConsolidado .table th, #divConsolidado .table td{
  color:var(--dep-text) !important;
  vertical-align:middle;
}
.table-responsive.tabla-informacion{
  border:1px solid rgba(255,255,255,.08);
  border-radius:14px;
  overflow:hidden;
  margin-top:12px;
}
.table-responsive.tabla-informacion .table th{
  background:rgba(255,255,255,.04)!important;
  border-bottom:2px solid rgba(255,255,255,.10)!important;
  color:rgba(255,255,255,.60)!important;
  font-size:12px;
  font-weight:800;
  text-transform:uppercase;
  letter-spacing:.06em;
  text-align:center;
  padding:14px 10px;
  border:none!important;
}
.table-responsive.tabla-informacion .table td{
  padding:12px 10px;
  vertical-align:middle;
  text-align:center;
  color:#ffffff!important;
  border-bottom:1px solid rgba(255,255,255,.06)!important;
}
.table-responsive.tabla-informacion .table tbody tr:hover{
  background:rgba(255,255,255,.04)!important;
}
.table-responsive.tabla-puntajes-municipios{
  background:#ffffff;
  border:1px solid rgba(15,23,42,.12);
  border-radius:14px;
  overflow:hidden;
  margin-top:12px;
}
.table-responsive.tabla-puntajes-municipios .table{
  background:#ffffff;
  margin-bottom:0;
}
.table-responsive.tabla-puntajes-municipios .table th{
  background:#f1f5f9!important;
  border-bottom:2px solid #e2e8f0!important;
  color:#334155!important;
  font-size:12px;
  font-weight:800;
  text-transform:uppercase;
  letter-spacing:.06em;
  text-align:center;
  padding:14px 10px;
  border:none!important;
}
.table-responsive.tabla-puntajes-municipios .table td{
  padding:12px 10px;
  vertical-align:middle;
  text-align:center;
  color:#0f172a!important;
  border-bottom:1px solid #e2e8f0!important;
  background:#ffffff!important;
}
.table-responsive.tabla-puntajes-municipios .table tbody tr:hover{
  background:#f8fafc!important;
}
.tabla-puntajes-municipios .municipio-link{
  color:#0f172a!important;
  font-weight:700;
  text-decoration:none;
}
.tabla-puntajes-municipios .municipio-link:hover{
  color:#1d4ed8!important;
  text-decoration:none;
}
.tabla-puntajes-municipios .puntaje-badge{
  font-size:13px;
  font-weight:700;
  color:#0f172a;
  padding:6px 16px;
  border-radius:8px;
  display:inline-flex;
  align-items:center;
  gap:8px;
  border:1px solid rgba(15,23,42,.12);
  min-width:110px;
  justify-content:center;
  background:#f8fafc;
}
.tabla-puntajes-municipios .puntaje-badge .color-dot{
  width:12px;
  height:12px;
  border-radius:50%;
  border:1px solid rgba(15,23,42,.25);
  flex-shrink:0;
}
.card-header-detalle-puntajes{
  display:flex;
  justify-content:space-between;
  align-items:center;
  flex-wrap:wrap;
  gap:12px;
}
.btn-toggle-detalle-puntajes{
  background:rgba(79,124,255,.18)!important;
  border:1px solid rgba(79,124,255,.45)!important;
  color:#fff!important;
  border-radius:10px!important;
  font-weight:700!important;
  font-size:13px!important;
  padding:8px 14px!important;
}
.btn-toggle-detalle-puntajes:hover{filter:brightness(1.12);}
.detalle-puntajes-resumen{
  color:var(--dep-muted)!important;
  font-size:14px;
  margin:0;
}
.detalle-puntajes-wrap .tabla-puntajes-municipios{
  margin-top:12px;
}
.puntaje-badge{
  font-size:13px;
  font-weight:700;
  color:#ffffff;
  padding:6px 16px;
  border-radius:8px;
  display:inline-flex;
  align-items:center;
  gap:8px;
  border:1px solid rgba(255,255,255,.12);
  min-width:110px;
  justify-content:center;
}
.puntaje-badge .color-dot{
  width:12px;
  height:12px;
  border-radius:50%;
  border:1px solid rgba(255,255,255,.35);
  flex-shrink:0;
}
.municipio-link:hover{
  color:var(--dep-primary)!important;
  text-decoration:none;
}

#myTabDep .nav-link{
  background:rgba(255,255,255,.04) !important;
  border:1px solid rgba(255,255,255,.08) !important;
  color:var(--dep-muted) !important;
  border-radius:12px !important;
  margin-right:6px;
  padding:10px 16px;
  font-weight:700;
  transition:all .2s;
}
#myTabDep .nav-link.active{
  background:linear-gradient(135deg, rgba(79,124,255,.25), rgba(155,92,255,.15)) !important;
  border-color:rgba(79,124,255,.40) !important;
  color:#fff !important;
}
.nav-tabs .nav-link{ border:1px solid transparent; }
#myTabDep .nav-link.active,
#myTabDep .nav-link:hover{ background:rgba(79,124,255,.20) !important; border-color:rgba(79,124,255,.35) !important; color:#fff !important; }
#myTabDep .nav-link:hover{ background:rgba(255,255,255,.08) !important; }

.modal-content{
  background:var(--dep-card) !important;
  border:1px solid var(--dep-border) !important;
  border-radius:var(--dep-radius) !important;
}
.modal-header{ border-bottom:1px solid var(--dep-border) !important; }
.modal-header h5{ color:var(--dep-text) !important; }
.modal-header .close{ color:var(--dep-text) !important; }
.map-tooltip{position:fixed;z-index:9999;pointer-events:none;background:rgba(0,0,0,.88);color:#fff;padding:8px 16px;border-radius:10px;font-size:18px;font-weight:800;border:1px solid rgba(255,255,255,.15);box-shadow:0 8px 30px rgba(0,0,0,.5);display:none;white-space:nowrap;}
.map-tooltip small{font-weight:400;font-size:13px;opacity:.6;margin-left:8px;}
</style>

<body class="">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
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
                                                <h5 class="m-b-10">Mapa Factores Inestabilidad</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Acción Unificada Santander</a></li>
                                                <li class="breadcrumb-item"><a href="#!">Mapa Factores Inestabilidad</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row align-items-end">
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label class="floating-label" for="inestabilidadId">Factor Inestabilidad <span class="text-danger mb-1">*</span></label>
                                                        <select class="form-control" id="inestabilidadId" name="inestabilidadId" onchange="updateUrlInestabilidad(this)">
                                                            <?php echo $optionInestabilidad ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row maps-grid">
                                <div class="col-lg-6 mb-4">
                                    <div class="card h-100 w-100 map-card">
                                        <div class="card-header">
                                            <h5 class="m-b-0"><i class="bi bi-map-fill"></i> Mapa Inicial</h5>
                                        </div>
                                        <div class="card-body map-body">
                                            <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
                                                <?php foreach ($badgeRangesInicial as $label => $cfg): ?>
                                                    <?php if ($label != 'Neutro'): ?>
                                                    <span class="badge rounded-pill px-3 py-2" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['text'] ?>;border:1px solid <?= $cfg['border'] ?>;font-weight:800;display:inline-flex;flex-direction:column;align-items:center;line-height:1.3;">
                                                        <?= $label ?>
                                                   
                                                    </span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="map-frame">
                                                <div id="contenido-mapa-inicial" class="cuerpoMapa w-100">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="-90 10 1400 1400.68" width="100%" height="auto">
                                                        <?php if (!empty($santanderInicial)): ?>
                                                            <?php foreach ($santanderInicial as $value): ?>
                                                                <?php if (is_array($value)): ?>
                                                                    <g id="INI_<?= strtoupper($value['path']) ?>">
                                                                        <path id="INI_<?= strtoupper($value['path']) ?>"
                                                                            d="<?= $value['d'] ?>"
                                                                            fill="<?= $value['color'] ?>"
                                                                            class="municipios mapaClick"
                                                            data-url="municipios_inestabilidad.php?mun=<?= $value['codigo_muncipio'] ?>&dep=<?= $value['codigo_departamento'] ?>&inestabilidad=<?= $inestabilidadId ?>"
                                                            data-name="<?= strtolower($value['municipio']) ?>"
                                                            title="<?= strtoupper(str_replace("-", " ", $value['nombre_mapa'])) ?>"
                                                            stroke="#000" stroke-miterlimit="10" stroke-width="0.1px">
                                                        </path>
                                                    </g>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php require_once 'nombres_mapa_santander.php' ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <?= FactoresInestabilidadGeneral::renderResumenColoresMapa($resumenColoresInicial, 'municipios') ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-4">
                                    <div class="card h-100 w-100 map-card">
                                        <div class="card-header">
                                            <h5 class="m-b-0"><i class="bi bi-map-fill"></i> Mapa Actual</h5>
                                        </div>
                                        <div class="card-body map-body">
                                            <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
                                                <?php foreach ($badgeRangesFinal as $label => $cfg): ?>
                                                    <?php if ($label != 'Neutro'): ?>
                                                    <span class="badge rounded-pill px-3 py-2" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['text'] ?>;border:1px solid <?= $cfg['border'] ?>;font-weight:800;display:inline-flex;flex-direction:column;align-items:center;line-height:1.3;">
                                                        <?= $label ?>
                                                      
                                                    </span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="map-frame">
                                                <div id="contenido-mapa-actual" class="cuerpoMapa w-100">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="-90 10 1400 1400.68" width="100%" height="auto">
                                                        <?php if (!empty($santanderActual)): ?>
                                                            <?php foreach ($santanderActual as $value): ?>
                                                                <?php if (is_array($value)): ?>
                                                                    <g id="ACT_<?= strtoupper($value['path']) ?>">
                                                                        <path id="ACT_<?= strtoupper($value['path']) ?>"
                                                                            d="<?= $value['d'] ?>"
                                                                            fill="<?= $value['color'] ?>"
                                                                            class="municipios mapaClick"
                                                                            data-url="municipios_inestabilidad.php?mun=<?= $value['codigo_muncipio'] ?>&dep=<?= $value['codigo_departamento'] ?>&inestabilidad=<?= $inestabilidadId ?>"
                                                                            data-name="<?= strtolower($value['municipio']) ?>"
                                                                            title="<?= strtoupper(str_replace("-", " ", $value['nombre_mapa'])) ?>"
                                                                            stroke="#000" stroke-miterlimit="10" stroke-width="0.1px">
                                                                        </path>
                                                                    </g>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                        <?php require 'nombres_mapa_santander.php' ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <?= FactoresInestabilidadGeneral::renderResumenColoresMapa($resumenColoresActual, 'municipios') ?>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header card-header-detalle-puntajes">
                                            <h5 class="m-b-0"><i class="bi bi-table"></i> Totalizado de Puntajes por Municipio</h5>
                                            <?php if (!empty($tablaPuntajesMunicipios)): ?>
                                                <button type="button" class="btn btn-sm btn-toggle-detalle-puntajes" onclick="toggleDetallePuntajes('detallePuntajesMunicipios', this)" aria-expanded="false" aria-controls="detallePuntajesMunicipios">
                                                    <i class="bi bi-eye"></i> Ver detalle
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($tablaPuntajesMunicipios)): ?>
                                                <div class="text-center text-muted p-4">
                                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                    <p class="mt-2 mb-0">No hay datos de puntaje para este factor de inestabilidad</p>
                                                </div>
                                            <?php else: ?>
                                                <p class="detalle-puntajes-resumen" id="resumenPuntajesMunicipios">
                                                    <?= count($tablaPuntajesMunicipios) ?> municipios registrados. Pulse «Ver detalle» para desplegar la tabla completa.
                                                </p>
                                                <div id="detallePuntajesMunicipios" class="detalle-puntajes-wrap" style="display:none;">
                                                <div class="table-responsive tabla-puntajes-municipios">
                                                    <table class="table table-hover mb-0" id="tablaPuntajesMunicipios">
                                                        <thead>
                                                            <tr>
                                                                <th style="text-align:left;">Municipio</th>
                                                                <th>Puntaje Inicial</th>
                                                                <th>Puntaje Actual</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($tablaPuntajesMunicipios as $fila): ?>
                                                                <?php
                                                                $urlMunicipio = 'municipios_inestabilidad.php?mun='
                                                                    . urlencode($fila['codigo_muncipio'])
                                                                    . '&dep=' . Util::getDepartamentoPrincipal()
                                                                    . '&inestabilidad=' . $inestabilidadId;
                                                                $puntajeInicialFmt = number_format($fila['puntaje_inicial'], 2, '.', ',');
                                                                $puntajeActualFmt = number_format($fila['puntaje_actual'], 2, '.', ',');
                                                                ?>
                                                                <tr>
                                                                    <td style="text-align:left;">
                                                                        <a href="<?= htmlspecialchars($urlMunicipio, ENT_QUOTES, 'UTF-8') ?>" class="municipio-link">
                                                                            <?= htmlspecialchars($fila['municipio'], ENT_QUOTES, 'UTF-8') ?>
                                                                        </a>
                                                                    </td>
                                                                    <td>
                                                                        <span class="puntaje-badge">
                                                                            <span class="color-dot" style="background:<?= htmlspecialchars($fila['color_inicial'], ENT_QUOTES, 'UTF-8') ?>;"></span>
                                                                            <?= $puntajeInicialFmt ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="puntaje-badge">
                                                                            <span class="color-dot" style="background:<?= htmlspecialchars($fila['color_actual'], ENT_QUOTES, 'UTF-8') ?>;"></span>
                                                                            <?= $puntajeActualFmt ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Consolidado Departamental - Factores por Inestabilidad</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="divConsolidado"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
                        aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="mun" class="form-label">Municipio</label>
                                            <select id="mun" class="form-control"></select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="factor" class="form-label">Factor</label>
                                            <select id="factor" class="form-control"></select>
                                        </div>
                                    </div>
                                    <div id="map" style="height: 400px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="mapTooltip" class="map-tooltip"></div>
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>
    <script src="<?php echo Util::versionar('./admin/js/factores_inestabilidad_general.js'); ?>"></script>

    <script>
        function toggleDetallePuntajes(containerId, btn) {
            var container = document.getElementById(containerId);
            if (!container || !btn) return;
            var resumenId = containerId === 'detallePuntajesMunicipios' ? 'resumenPuntajesMunicipios' : 'resumenPuntajesVeredas';
            var resumen = document.getElementById(resumenId);
            var visible = container.style.display !== 'none';
            container.style.display = visible ? 'none' : 'block';
            btn.setAttribute('aria-expanded', visible ? 'false' : 'true');
            btn.innerHTML = visible
                ? '<i class="bi bi-eye"></i> Ver detalle'
                : '<i class="bi bi-eye-slash"></i> Ocultar detalle';
            if (resumen) {
                resumen.style.display = visible ? 'block' : 'none';
            }
        }

        function renderConsolidadoTable(data) {
            const tabs = data.tabs || [];
            const response = data.response || [];
            const responseActual = data.responseActual || [];

            if (!data.valid || tabs.length === 0) {
                return '<div class="text-center text-muted p-4"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">No hay datos disponibles para este factor de inestabilidad</p></div>';
            }

            let tabsHtml = '<ul class="nav nav-tabs mb-3" id="myTabDep" role="tablist" style="border-bottom: 1px solid rgba(255,255,255,.08); gap: 4px;">';
            let contentHtml = '<div class="tab-content" id="myTabDepContent">';

            tabs.forEach((tab, index) => {
                const isActive = index === 0 ? 'active' : '';
                const showActive = index === 0 ? 'show active' : '';

                tabsHtml += `
                    <li class="nav-item">
                        <a class="nav-link ${isActive}"
                            id="tab-dep-${tab.id}" data-toggle="tab"
                            href="#content-dep-${tab.id}" role="tab"
                            aria-controls="content-dep-${tab.id}"
                            aria-selected="${index === 0 ? 'true' : 'false'}"
                            style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.70);border-radius:12px 12px 0 0;padding:10px 20px;font-weight:700;margin-right:4px;transition:all .2s;">
                            <span class="tab-icon-wrap"><img src="${tab.icono || 'assets/iconos/gobierno.png'}" alt="${tab.nombre}" width="24" height="24"></span>
                            <span class="tab-label">${tab.nombre}</span>
                        </a>
                    </li>
                `;

                const areaDataInicial = response.filter(item => item.inestabilidad_id == tab.id);
                const areaDataActual = responseActual.filter(item => item.inestabilidad_id == tab.id);

                const agrupado = {};
                areaDataInicial.forEach(item => {
                    const factor = (item.factor || '').toUpperCase().trim();
                    const medicion = (item.tipo_medicion || '').toUpperCase().trim();
                    const clave = factor + '|' + medicion;
                    const cantidadInicial = parseFloat(item.total_cantidad) || 0;

                    if (!agrupado[clave]) {
                        agrupado[clave] = {
                            factor: factor,
                            tipo_medicion: medicion,
                            total_cantidad_inicial: cantidadInicial,
                            total_cantidad_actual: 0,
                            icono: item.icono || ''
                        };
                    } else {
                        agrupado[clave].total_cantidad_inicial += cantidadInicial;
                    }
                });

                areaDataActual.forEach(item => {
                    const factor = (item.factor || '').toUpperCase().trim();
                    const medicion = (item.tipo_medicion || '').toUpperCase().trim();
                    const clave = factor + '|' + medicion;
                    const cantidadActual = parseFloat(item.total_cantidad_actual) || 0;

                    if (agrupado[clave]) {
                        agrupado[clave].total_cantidad_actual += cantidadActual;
                    } else {
                        agrupado[clave] = {
                            factor: factor,
                            tipo_medicion: medicion,
                            total_cantidad_inicial: 0,
                            total_cantidad_actual: cantidadActual,
                            icono: item.icono || ''
                        };
                    }
                });

                let tableRows = '';
                Object.values(agrupado).forEach(d => {
                    tableRows += `
                        <tr style="background:transparent;border-bottom:1px solid rgba(255,255,255,.06);transition:background .15s;">
                            <td style="padding:12px 10px;vertical-align:middle;text-align:center;">
                                <img src="${d.icono}" alt="" width="36" style="border-radius:8px;">
                            </td>
                            <td style="padding:12px 10px;vertical-align:middle;text-align:left;font-size:13px;font-weight:700;color:rgba(255,255,255,.90);">
                                ${d.factor}
                            </td>
                            <td style="padding:12px 10px;vertical-align:middle;text-align:center;">
                                <span style="font-size:13px;font-weight:600;color:#ffffff;background:rgba(46,125,50,.15);padding:6px 16px;border-radius:8px;display:inline-block;border:1px solid rgba(46,125,50,.25);">
                                    ${number_format(d.total_cantidad_inicial)}
                                </span>
                            </td>
                            <td style="padding:12px 10px;vertical-align:middle;text-align:center;">
                                <span style="font-size:13px;font-weight:700;color:#ffffff;background:rgba(30,136,229,.15);padding:6px 16px;border-radius:8px;display:inline-block;border:1px solid rgba(30,136,229,.25);">
                                    ${number_format(d.total_cantidad_actual)}
                                </span>
                            </td>
                            <td style="padding:12px 10px;vertical-align:middle;text-align:center;font-size:12px;color:rgba(255,255,255,.50);font-weight:600;">
                                ${d.tipo_medicion}
                            </td>
                        </tr>
                    `;
                });

                const tableContent = tableRows
                    ? `<div class="table-responsive tabla-informacion tabla-scroll" style="border:1px solid rgba(255,255,255,.08);border-radius:14px;overflow:hidden;margin-top:12px;">
                        <table class="table table-hover mb-0" style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:rgba(255,255,255,.04);border-bottom:2px solid rgba(255,255,255,.10);">
                                    <th style="padding:14px 10px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.60);text-align:center;border:none;">Ícono</th>
                                    <th style="padding:14px 10px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.60);text-align:left;border:none;">Indicador</th>
                                    <th style="padding:14px 10px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.60);text-align:center;border:none;">Cantidad Inicial</th>
                                    <th style="padding:14px 10px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.60);text-align:center;border:none;">Cantidad Actual</th>
                                    <th style="padding:14px 10px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.60);text-align:center;border:none;">Unidad</th>
                                </tr>
                            </thead>
                            <tbody>${tableRows}</tbody>
                        </table>
                       </div>`
                    : '<div class="text-center text-muted p-4"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">No hay datos disponibles</p></div>';

                contentHtml += `
                    <div class="tab-pane fade ${showActive}"
                        id="content-dep-${tab.id}" role="tabpanel"
                        aria-labelledby="tab-dep-${tab.id}">
                        ${tableContent}
                    </div>
                `;
            });

            tabsHtml += '</ul>';
            contentHtml += '</div>';

            return tabsHtml + contentHtml;
        }

        function number_format(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function cargarConsolidado(inestabilidadId) {
            const container = document.getElementById('divConsolidado');
            if (!container) return;

            container.innerHTML = '<div class="text-center p-4"><i class="fa fa-spinner fa-spin" style="font-size: 2rem;"></i><p class="mt-2">Cargando datos...</p></div>';

            fetch('admin/ajax/rqst.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'op=get_consolidado_inestabilidad_departamental&inestabilidadId=' + inestabilidadId
            })
            .then(response => response.json())
            .then(data => {
                if (data.valid) {
                    container.innerHTML = renderConsolidadoTable(data);
                    initTabsEvents();
                } else {
                    container.innerHTML = '<div class="text-center text-muted p-4"><i class="bi bi-inbox" style="font-size: 2rem;"></i><p class="mt-2">Error al cargar los datos</p></div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                container.innerHTML = '<div class="text-center text-muted p-4"><i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i><p class="mt-2">Error de conexión</p></div>';
            });
        }

        function initTabsEvents() {
            $('#myTabDep a[data-toggle="tab"]').off('click').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });
        }

        document.getElementById("inestabilidadId").addEventListener("change", function() {
            var selectedValue = this.value;
            cargarConsolidado(selectedValue);
        });

        $(document).ready(function() {
            var inestabilidadInicial = document.getElementById("inestabilidadId").value;
            setTimeout(function() {
                cargarConsolidado(inestabilidadInicial);
            }, 1000);
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
        document.querySelectorAll("#contenido-mapa-inicial path[title], #contenido-mapa-actual path[title]").forEach(function(el) {
            el.addEventListener("mouseenter", function(e) {
                showTip(e, this.getAttribute("title"));
            });
            el.addEventListener("mousemove", moveTip);
            el.addEventListener("mouseleave", hideTip);
        });
    });
    </script>
</body>
</html>
