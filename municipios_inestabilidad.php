<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/FactoresInestabilidadGeneral.php';
include './admin/classes/FactoresInestabilidadGobernacion.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Actores.php';
include './admin/classes/Departamento.php';
include './admin/classes/Empresas.php';
include './admin/classes/EmpresaFactor.php';
include './admin/classes/ActualizacionInformacion.php';

requirePermission('accion_unificada.municipios.view');

$efView = SessionData::hasPermission('accion_unificada.empresa_factor.view');
$efCreate = SessionData::hasPermission('accion_unificada.empresa_factor.create');
$efUpdate = SessionData::hasPermission('accion_unificada.empresa_factor.update');
$efDelete = SessionData::hasPermission('accion_unificada.empresa_factor.delete');

$userType = SessionData::getUserType();
$municipioId = trim($_REQUEST['mun'] ?? '');
$inestabilidadId = isset($_REQUEST['inestabilidad']) ? intval($_REQUEST['inestabilidad']) : 10000;
$codigoTodos = 10000;

if (empty($municipioId)) {
    echo "<script>alert('Municipio no especificado'); window.location='factores_inestabilidad_general.php?inestabilidad=$inestabilidadId';</script>";
    exit;
}

$municipioInfo = Ciudad::getInformacionCiudad(array('codigo_muncipio' => $municipioId));
$informacionMunicipio = $municipioInfo['output']['response'][0] ?? null;
$nombreMunicipio = $informacionMunicipio['municipio'] ?? '';
$viewBoxActual = !empty($informacionMunicipio['viewbox_svg']) ? $informacionMunicipio['viewbox_svg'] : '0 45 1518.36 900';

$responseInest = FactoresInestabilidadGobernacion::getAll(null);

$arrParams = [
    'codigo_municipio' => $municipioId,
    'inestabilidadId' => $inestabilidadId,
];
$dataInicial = FactoresInestabilidadGeneral::calcularColorVeredasInicial($arrParams);
$dataActual = FactoresInestabilidadGeneral::calcularColorVeredasActual($arrParams);
$veredasInicial = $dataInicial['output']['valid'] ? $dataInicial['output']['response'] : [];
$veredasActual = $dataActual['output']['valid'] ? $dataActual['output']['response'] : [];

$dataConsInicial = FactoresInestabilidadGeneral::consultarConsolidadMunicipioInicial($arrParams);
$dataConsActual = FactoresInestabilidadGeneral::consultarConsolidadMunicipioActual($arrParams);
$responseConsolidado = $dataConsInicial['output']['valid'] ? $dataConsInicial['output']['response'] : [];
$responseConsolidadoActual = $dataConsActual['output']['valid'] ? $dataConsActual['output']['response'] : [];
$tabs = $dataConsInicial['output']['tabs'] ?? [];

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

$etiquetasColorInicial = FactoresInestabilidadGeneral::etiquetasPorColorDesdeBadges($badgeRangesInicial);
$etiquetasColorFinal = FactoresInestabilidadGeneral::etiquetasPorColorDesdeBadges($badgeRangesFinal);
$resumenColoresInicial = FactoresInestabilidadGeneral::resumenColoresMapa($veredasInicial, 'color_calculado', $etiquetasColorInicial);
$resumenColoresActual = FactoresInestabilidadGeneral::resumenColoresMapa($veredasActual, 'color_calculado', $etiquetasColorFinal);

$puntajeMunicipioInicial = 0.0;
$puntajeMunicipioActual = 0.0;
foreach ($veredasInicial as $vereda) {
    $puntajeMunicipioInicial += (float) ($vereda['cantidad'] ?? 0);
}
foreach ($veredasActual as $vereda) {
    $puntajeMunicipioActual += (float) ($vereda['cantidad'] ?? 0);
}
$colorMunicipioInicial = FactoresInestabilidadGeneral::resolverColorPuntaje($puntajeMunicipioInicial, $puntajesInicial);
$colorMunicipioActual = FactoresInestabilidadGeneral::resolverColorPuntaje($puntajeMunicipioActual, $puntajesFinal);

$tablaPuntajesVeredas = [];
foreach ($veredasInicial as $vereda) {
    $id = $vereda['id'] ?? 0;
    if ($id <= 0) {
        continue;
    }
    $tablaPuntajesVeredas[$id] = [
        'id' => $id,
        'nombre_vereda' => $vereda['nombre_vereda'] ?? '',
        'puntaje_inicial' => (float) ($vereda['cantidad'] ?? 0),
        'color_inicial' => $vereda['color_calculado'] ?? Util::getColorNeutroMapa(),
        'puntaje_actual' => 0.0,
        'color_actual' => Util::getColorNeutroMapa(),
    ];
}
foreach ($veredasActual as $vereda) {
    $id = $vereda['id'] ?? 0;
    if ($id <= 0) {
        continue;
    }
    if (!isset($tablaPuntajesVeredas[$id])) {
        $tablaPuntajesVeredas[$id] = [
            'id' => $id,
            'nombre_vereda' => $vereda['nombre_vereda'] ?? '',
            'puntaje_inicial' => 0.0,
            'color_inicial' => Util::getColorNeutroMapa(),
            'puntaje_actual' => 0.0,
            'color_actual' => Util::getColorNeutroMapa(),
        ];
    }
    $tablaPuntajesVeredas[$id]['puntaje_actual'] = (float) ($vereda['cantidad'] ?? 0);
    $tablaPuntajesVeredas[$id]['color_actual'] = $vereda['color_calculado'] ?? Util::getColorNeutroMapa();
    if (empty($tablaPuntajesVeredas[$id]['nombre_vereda'])) {
        $tablaPuntajesVeredas[$id]['nombre_vereda'] = $vereda['nombre_vereda'] ?? '';
    }
}
usort($tablaPuntajesVeredas, fn($a, $b) => strcasecmp($a['nombre_vereda'], $b['nombre_vereda']));

$empresasMunicipioResponse = Empresas::getByCodigoMunicipio($municipioId);
$empresasMunicipio = ($empresasMunicipioResponse['output']['valid'] ?? false)
    ? ($empresasMunicipioResponse['output']['response'] ?? [])
    : [];

$efCounts = EmpresaFactor::countsByMunicipio((int) $municipioId);
$efCountByFactor = $efCounts['by_factor'] ?? [];
$efCountByEmpresa = $efCounts['by_empresa'] ?? [];

$factoresConActualizacionResp = ActualizacionInformacion::getFactoresConActualizacionPorMunicipio([
    'codigo_municipio' => (int) $municipioId,
    'inestabilidadId' => $inestabilidadId,
]);
$factoresConActualizacion = ($factoresConActualizacionResp['output']['valid'] ?? false)
    ? ($factoresConActualizacionResp['output']['response'] ?? [])
    : [];

/** Factores únicos del consolidado del municipio (para selects del modal). */
$factoresParaAsociar = [];
foreach (array_merge($responseConsolidado, $responseConsolidadoActual) as $itemCons) {
    $fid = (int) ($itemCons['tbl_factor_id'] ?? 0);
    if ($fid <= 0 || isset($factoresParaAsociar[$fid])) {
        continue;
    }
    $factoresParaAsociar[$fid] = [
        'id' => $fid,
        'nombre' => strtoupper(trim((string) ($itemCons['factor'] ?? 'Factor #' . $fid))),
    ];
}
usort($factoresParaAsociar, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));
$factoresParaAsociar = array_values($factoresParaAsociar);

$optionInest = "<option value='10000'" . ($inestabilidadId == 10000 ? " selected" : "") . ">Todos</option>";
if (!empty($responseInest['output']['valid'])) {
    foreach ($responseInest['output']['response'] as $val) {
        $selected = ($val['id'] == $inestabilidadId) ? ' selected' : '';
        $optionInest .= "<option value='{$val['id']}'{$selected}>" . htmlspecialchars($val['nombre_categoria'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
}

$responseActors = Actores::getAll(null);
$optionActores = '';
if (!empty($responseActors['output']['valid'])) {
    $optionActores = array_reduce($responseActors['output']['response'], fn($c, $v) => $c . "<option value='{$v['id']}'>{$v['nombre']}</option>", '');
}

$departamentoObj = new Departamento();
$municipiosList = $departamentoObj->getMunicipiosByDeptoId(Util::getDepartamentoPrincipal());
$optionMunicipios = '';
if ($municipiosList['output']['valid'] ?? false) {
    foreach ($municipiosList['output']['response'] as $m) {
        $sel = ($m['codigo_muncipio'] == $municipioId) ? ' selected' : '';
        $optionMunicipios .= "<option value='" . $m['codigo_muncipio'] . "'{$sel}>" . htmlspecialchars($m['municipio'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
}

function number_format_mun($num) { return number_format($num, 0, '.', ','); }
function number_format_puntaje($num) { return number_format((float) $num, 2, '.', ','); }

$hombres = (int) ($informacionMunicipio['hombres'] ?? 0);
$mujeres = (int) ($informacionMunicipio['mujeres'] ?? 0);
$poblacionTotal = $hombres + $mujeres;

$totalVeredasMunicipio = count($tablaPuntajesVeredas);
$totalEmpresasMunicipio = count($empresasMunicipio);
$totalFactoresMunicipio = count($factoresParaAsociar);
$variacionPuntajeMunicipio = $puntajeMunicipioActual - $puntajeMunicipioInicial;

$nombreFactorActual = 'Todos los factores';
if (
    $inestabilidadId !== $codigoTodos
    && !empty($responseInest['output']['valid'])
) {
    foreach ($responseInest['output']['response'] as $factorActual) {
        if ((int) ($factorActual['id'] ?? 0) === $inestabilidadId) {
            $nombreFactorActual = (string) (
                $factorActual['nombre_categoria']
                ?? 'Factor seleccionado'
            );
            break;
        }
    }
}

$diferencias = [];
foreach ($veredasInicial as $veredaInicialComparacion) {
    foreach ($veredasActual as $veredaActualComparacion) {
        if (
            ($veredaInicialComparacion['id'] ?? null)
            === ($veredaActualComparacion['id'] ?? null)
            && round(
                (float) ($veredaInicialComparacion['cantidad'] ?? 0),
                2
            ) !== round(
                (float) ($veredaActualComparacion['cantidad'] ?? 0),
                2
            )
        ) {
            $diferencias[] = [
                'nombre' => $veredaInicialComparacion['nombre_vereda'] ?? '',
                'inicial' => number_format_puntaje(
                    $veredaInicialComparacion['cantidad'] ?? 0
                ),
                'actual' => number_format_puntaje(
                    $veredaActualComparacion['cantidad'] ?? 0
                ),
            ];
        }
    }
}

$totalVeredasConCambios = count($diferencias);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/municipios_inestabilidad_gob360_premium.css">
<body class="gob360-instability-page">
    <div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <section class="g360-instability-hero" aria-label="Análisis territorial de factores de inestabilidad">
                                <div class="g360-instability-hero__grid">

                                    <aside class="g360-instability-brand">
                                        <span class="g360-instability-brand__eyebrow">
                                            Plataforma institucional
                                        </span>

                                        <img
                                            src="assets/img/gob360l.png"
                                            alt="Logo GOB360"
                                            class="g360-instability-brand__logo"
                                        >

                                        <span class="g360-instability-brand__caption">
                                            Gestión pública inteligente y territorial
                                        </span>

                                        <div class="g360-instability-brand__status">
                                            <span></span>
                                            Comparativo territorial activo
                                        </div>
                                    </aside>

                                    <div class="g360-instability-hero__content">
                                        <div class="g360-instability-hero__top">
                                            <div>
                                                <div class="g360-instability-hero__eyebrow">
                                                    <i class="feather icon-map"></i>
                                                    Análisis municipal por veredas
                                                </div>

                                                <h1 class="g360-instability-hero__title">
                                                    Factores de Inestabilidad
                                                </h1>

                                                <p class="g360-instability-hero__description">
                                                    Compara la línea base y el estado actual de
                                                    <?= htmlspecialchars(
                                                        $nombreMunicipio,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>, identifica cambios por vereda y consulta
                                                    factores, empresas, actualizaciones,
                                                    geolocalización y compromisos institucionales.
                                                </p>
                                            </div>

                                            <div class="g360-instability-hero__actions">
                                                <button
                                                    type="button"
                                                    class="g360-hero-button"
                                                    onclick="window.location.reload()"
                                                >
                                                    <i class="feather icon-refresh-cw"></i>
                                                    Actualizar análisis
                                                </button>

                                                <div class="g360-instability-back">
                                                    <?php include './admin/include/btn_back.php'; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="g360-instability-summary">
                                            <article>
                                                <span class="g360-instability-summary__icon">
                                                    <i class="feather icon-users"></i>
                                                </span>

                                                <div>
                                                    <small>Población</small>
                                                    <strong>
                                                        <?= number_format_mun($poblacionTotal) ?>
                                                    </strong>
                                                    <p>
                                                        H: <?= number_format_mun($hombres) ?>
                                                        · M: <?= number_format_mun($mujeres) ?>
                                                    </p>
                                                </div>
                                            </article>

                                            <article>
                                                <span class="g360-instability-summary__icon g360-instability-summary__icon--territory">
                                                    <i class="feather icon-map-pin"></i>
                                                </span>

                                                <div>
                                                    <small>Veredas analizadas</small>
                                                    <strong>
                                                        <?= number_format_mun($totalVeredasMunicipio) ?>
                                                    </strong>
                                                    <p>Comparación inicial y actual</p>
                                                </div>
                                            </article>

                                            <article>
                                                <span class="g360-instability-summary__icon g360-instability-summary__icon--initial">
                                                    <i class="feather icon-clock"></i>
                                                </span>

                                                <div>
                                                    <small>Puntaje inicial</small>
                                                    <strong>
                                                        <?= number_format_puntaje($puntajeMunicipioInicial) ?>
                                                    </strong>
                                                    <p>Línea base municipal</p>
                                                </div>
                                            </article>

                                            <article>
                                                <span class="g360-instability-summary__icon g360-instability-summary__icon--current">
                                                    <i class="feather icon-activity"></i>
                                                </span>

                                                <div>
                                                    <small>Puntaje actual</small>
                                                    <strong>
                                                        <?= number_format_puntaje($puntajeMunicipioActual) ?>
                                                    </strong>
                                                    <p>
                                                        Variación:
                                                        <?= $variacionPuntajeMunicipio >= 0 ? '+' : '' ?>
                                                        <?= number_format_puntaje($variacionPuntajeMunicipio) ?>
                                                    </p>
                                                </div>
                                            </article>
                                        </div>

                                        <div class="g360-instability-capabilities" aria-hidden="true">
                                            <span>
                                                <i class="feather icon-filter"></i>
                                                <?= htmlspecialchars(
                                                    $nombreFactorActual,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </span>

                                            <span>
                                                <i class="feather icon-briefcase"></i>
                                                <?= number_format_mun($totalEmpresasMunicipio) ?>
                                                empresas
                                            </span>

                                            <span>
                                                <i class="feather icon-grid"></i>
                                                <?= number_format_mun($totalFactoresMunicipio) ?>
                                                factores
                                            </span>

                                            <span>
                                                <i class="feather icon-trending-up"></i>
                                                <?= number_format_mun($totalVeredasConCambios) ?>
                                                veredas con cambios
                                            </span>

                                            <span>
                                                <i class="feather icon-layers"></i>
                                                Mapas comparativos SVG
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            </section>

                            <section class="g360-territory-filter" aria-label="Filtros territoriales">
                                <div class="g360-territory-filter__heading">
                                    <span class="g360-territory-filter__icon">
                                        <i class="feather icon-filter"></i>
                                    </span>

                                    <div>
                                        <small>Consulta territorial</small>
                                        <h5>Selecciona municipio y factor</h5>
                                        <p>
                                            Cambia el territorio o la categoría para
                                            reconstruir los mapas y consolidados.
                                        </p>
                                    </div>

                                    <a
                                        href="factores_inestabilidad_general.php?inestabilidad=<?= $inestabilidadId ?>"
                                        class="g360-filter-back-button"
                                    >
                                        <i class="bi bi-arrow-left"></i>
                                        Volver al mapa general
                                    </a>
                                </div>

                                <div class="row align-items-end">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="municipioSelector">
                                                Municipio
                                            </label>

                                            <div class="g360-input-shell">
                                                <span>
                                                    <i class="feather icon-map-pin"></i>
                                                </span>

                                                <select
                                                    id="municipioSelector"
                                                    class="form-control"
                                                    onchange="window.location.href='municipios_inestabilidad.php?mun='+this.value+'&inestabilidad=<?= $inestabilidadId ?>'"
                                                >
                                                    <?= $optionMunicipios ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="factorInestabilidadSelector">
                                                Factor de inestabilidad
                                            </label>

                                            <div class="g360-input-shell">
                                                <span>
                                                    <i class="feather icon-grid"></i>
                                                </span>

                                                <select
                                                    id="factorInestabilidadSelector"
                                                    class="form-control"
                                                    onchange="window.location.href='municipios_inestabilidad.php?mun=<?= $municipioId ?>&inestabilidad='+this.value"
                                                >
                                                    <?= $optionInest ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="card g360-municipality-card">
                                        <div class="card-header">
                                            <div class="g360-section-heading">
                                                <span>
                                                    <i class="feather icon-info"></i>
                                                </span>

                                                <div>
                                                    <small>Ficha territorial</small>
                                                    <h5>
                                                        <?= htmlspecialchars(
                                                            $nombreMunicipio,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </h5>
                                                    <p>
                                                        Información institucional y puntajes
                                                        consolidados del municipio.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="municipio-info-block">
                                                <div class="municipio-info-item">
                                                    <strong>Alcalde</strong>
                                                    <span><?= htmlspecialchars($informacionMunicipio['nombre_alcalde'] ?? 'No disponible') ?></span>
                                                </div>
                                                <div class="municipio-info-item">
                                                    <strong>Partido</strong>
                                                    <span><?= htmlspecialchars($informacionMunicipio['partido'] ?? 'No disponible') ?></span>
                                                </div>
                                                <div class="municipio-info-item">
                                                    <strong>Población</strong>
                                                    <span style="font-size:18px;font-weight:900;">
                                                        <?php
                                                        $hombres = intval($informacionMunicipio['hombres'] ?? 0);
                                                        $mujeres = intval($informacionMunicipio['mujeres'] ?? 0);
                                                        echo number_format_mun($hombres + $mujeres);
                                                        ?>
                                                    </span>
                                                    <small style="display:block;font-size:12px;color:var(--au-muted);margin-top:2px;">
                                                        <i class="bi bi-gender-male text-primary"></i> H: <?= number_format_mun($hombres) ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="bi bi-gender-female text-danger"></i> M: <?= number_format_mun($mujeres) ?>
                                                    </small>
                                                </div>
                                                <div class="municipio-info-item">
                                                    <strong>Puntaje Inicial</strong>
                                                    <span class="puntaje-valor">
                                                        <span class="color-dot" style="background:<?= htmlspecialchars($colorMunicipioInicial, ENT_QUOTES, 'UTF-8') ?>;"></span>
                                                        <?= number_format_puntaje($puntajeMunicipioInicial) ?>
                                                    </span>
                                                </div>
                                                <div class="municipio-info-item">
                                                    <strong>Puntaje Actual</strong>
                                                    <span class="puntaje-valor">
                                                        <span class="color-dot" style="background:<?= htmlspecialchars($colorMunicipioActual, ENT_QUOTES, 'UTF-8') ?>;"></span>
                                                        <?= number_format_puntaje($puntajeMunicipioActual) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row maps-grid">
                                <div class="col-lg-6 mb-4">
                                    <div class="card h-100 w-100 map-card g360-map-card g360-map-card--initial">
                                        <div class="card-header">
                                            <div class="g360-map-heading">
                                                <span>
                                                    <i class="feather icon-clock"></i>
                                                </span>

                                                <div>
                                                    <small>Línea base territorial</small>
                                                    <h5>Mapa inicial</h5>
                                                </div>

                                                <b>INICIAL</b>
                                            </div>
                                        </div>
                                        <div class="card-body map-body g360-map-body">
                                            <div class="d-flex flex-wrap gap-2 justify-content-center mb-3 g360-map-legend">
                                                <?php foreach ($badgeRangesInicial as $label => $cfg): ?>
                                                    <?php if ($label != 'Neutro'): ?>
                                                    <span class="badge rounded-pill px-3 py-2" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['text'] ?>;border:1px solid <?= $cfg['border'] ?>;font-weight:800;display:inline-flex;flex-direction:column;align-items:center;line-height:1.3;">
                                                        <?= $label ?>
                                                      
                                                    </span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="map-frame g360-map-frame">
                                                <div id="contenido-mapa-inicial" class="cuerpoMapa w-100 g360-map-stage">
                                                    <svg viewBox="<?= htmlspecialchars($viewBoxActual) ?>" xmlns="http://www.w3.org/2000/svg" stroke-width="1.2px" stroke="#fff" preserveAspectRatio="xMidYMid meet">
                                                        <?php foreach ($veredasInicial as $v): ?>
                                                            <?php
                                                            $cantidadVereda = (float) ($v['cantidad'] ?? 0);
                                                            $tituloVereda = strtoupper(str_replace("-", " ", $v['nombre_vereda'])) . ' (' . number_format_puntaje($cantidadVereda) . ')';
                                                            ?>
                                                            <g id="<?= $v['nombre_svg'] ?>">
                                                                <?php if (!empty($v['points'])): ?>
                                                                     <polygon points="<?= strtoupper($v['points']) ?>" fill="<?= strtolower($v['color_calculado']) ?>" fill-rule="evenodd" stroke-miterlimit="10" stroke-width="0.1px" data-cantidad="<?= number_format_puntaje($cantidadVereda) ?>" title="<?= htmlspecialchars($tituloVereda, ENT_QUOTES, 'UTF-8') ?>" onclick="window.location.href='veredas_inestabilidad.php?id=<?= $v['id'] ?>&mun=<?= $municipioId ?>&inestabilidad=<?= $inestabilidadId ?>'" style="cursor:pointer;" />
                                                                 <?php elseif (!empty($v['path'])): ?>
                                                                     <path d="<?= $v['path'] ?>" title="<?= htmlspecialchars($tituloVereda, ENT_QUOTES, 'UTF-8') ?>" style="fill:<?= strtolower($v['color_calculado']) ?>;cursor:pointer;" stroke-miterlimit="10" stroke-width="0.1px" data-cantidad="<?= number_format_puntaje($cantidadVereda) ?>" onclick="window.location.href='veredas_inestabilidad.php?id=<?= $v['id'] ?>&mun=<?= $municipioId ?>&inestabilidad=<?= $inestabilidadId ?>'" />
                                                                 <?php endif; ?>
                                                             </g>
                                                         <?php endforeach; ?>
                                                         <?php foreach ($veredasInicial as $v): ?><?= str_replace('<tspan','<tspan style="fill:black;font-family:IBM Plex Sans;stroke-width:0.1px;"',$v['tspan'] ?? '') ?><?php endforeach; ?>
                                                     </svg>
                                                 </div>
                                             </div>
                                            <?= FactoresInestabilidadGeneral::renderResumenColoresMapa($resumenColoresInicial, 'veredas') ?>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="col-lg-6 mb-4">
                                     <div class="card h-100 w-100 map-card g360-map-card g360-map-card--current">
                                         <div class="card-header">
                                            <div class="g360-map-heading">
                                                <span>
                                                    <i class="feather icon-activity"></i>
                                                </span>

                                                <div>
                                                    <small>Estado territorial vigente</small>
                                                    <h5>Mapa actual</h5>
                                                </div>

                                                <b>ACTUAL</b>
                                            </div>
                                        </div>
                                         <div class="card-body map-body g360-map-body">
                                            <div class="d-flex flex-wrap gap-2 justify-content-center mb-3 g360-map-legend">
                                                <?php foreach ($badgeRangesFinal as $label => $cfg): ?>
                                                    <?php if ($label != 'Neutro'): ?>
                                                    <span class="badge rounded-pill px-3 py-2" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['text'] ?>;border:1px solid <?= $cfg['border'] ?>;font-weight:800;display:inline-flex;flex-direction:column;align-items:center;line-height:1.3;">
                                                        <?= $label ?>
                                                   
                                                    </span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="map-frame g360-map-frame">
                                                 <div id="contenido-mapa-actual" class="cuerpoMapa w-100 g360-map-stage">
                                                     <svg viewBox="<?= htmlspecialchars($viewBoxActual) ?>" xmlns="http://www.w3.org/2000/svg" stroke-width="1.2px" stroke="#fff" preserveAspectRatio="xMidYMid meet">
                                                         <?php foreach ($veredasActual as $v): ?>
                                                            <?php
                                                            $cantidadVereda = (float) ($v['cantidad'] ?? 0);
                                                            $tituloVereda = strtoupper(str_replace("-", " ", $v['nombre_vereda'])) . ' (' . number_format_puntaje($cantidadVereda) . ')';
                                                            ?>
                                                             <g id="<?= $v['nombre_svg'] ?>">
                                                                 <?php if (!empty($v['points'])): ?>
                                                                     <polygon points="<?= strtoupper($v['points']) ?>" fill="<?= strtolower($v['color_calculado']) ?>" fill-rule="evenodd" stroke-miterlimit="10" stroke-width="0.1px" data-cantidad="<?= number_format_puntaje($cantidadVereda) ?>" title="<?= htmlspecialchars($tituloVereda, ENT_QUOTES, 'UTF-8') ?>" onclick="window.location.href='veredas_inestabilidad.php?id=<?= $v['id'] ?>&mun=<?= $municipioId ?>&inestabilidad=<?= $inestabilidadId ?>'" style="cursor:pointer;" />
                                                                 <?php elseif (!empty($v['path'])): ?>
                                                                     <path d="<?= $v['path'] ?>" title="<?= htmlspecialchars($tituloVereda, ENT_QUOTES, 'UTF-8') ?>" style="fill:<?= strtolower($v['color_calculado']) ?>;cursor:pointer;" stroke-miterlimit="10" stroke-width="0.1px" data-cantidad="<?= number_format_puntaje($cantidadVereda) ?>" onclick="window.location.href='veredas_inestabilidad.php?id=<?= $v['id'] ?>&mun=<?= $municipioId ?>&inestabilidad=<?= $inestabilidadId ?>'" />
                                                                <?php endif; ?>
                                                            </g>
                                                        <?php endforeach; ?>
                                                        <?php foreach ($veredasActual as $v): ?><?= str_replace('<tspan','<tspan style="fill:black;font-family:IBM Plex Sans;stroke-width:0.1px;"',$v['tspan'] ?? '') ?><?php endforeach; ?>
                                                    </svg>
                                                </div>
                                            </div>
                                            <?= FactoresInestabilidadGeneral::renderResumenColoresMapa($resumenColoresActual, 'veredas') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card g360-detail-card g360-score-detail-card">
                                        <div class="card-header card-header-detalle-puntajes">
                                            <h5 class="m-b-0"><i class="bi bi-table"></i> Totalizado de Puntajes por Vereda</h5>
                                            <?php if (!empty($tablaPuntajesVeredas)): ?>
                                                <button type="button" class="btn btn-sm btn-toggle-detalle-puntajes" onclick="toggleDetallePuntajes('detallePuntajesVeredas', this)" aria-expanded="false" aria-controls="detallePuntajesVeredas">
                                                    <i class="bi bi-eye"></i> Ver detalle
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($tablaPuntajesVeredas)): ?>
                                                <div class="text-center text-muted p-4">No hay datos de puntaje para este municipio.</div>
                                            <?php else: ?>
                                                <p class="detalle-puntajes-resumen" id="resumenPuntajesVeredas">
                                                    <?= count($tablaPuntajesVeredas) ?> veredas registradas. Pulse «Ver detalle» para desplegar la tabla completa.
                                                </p>
                                                <div id="detallePuntajesVeredas" class="detalle-puntajes-wrap" style="display:none;">
                                                <div class="table-responsive tabla-puntajes-veredas">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th style="text-align:left;">Vereda</th>
                                                                <th>Puntaje Inicial</th>
                                                                <th>Puntaje Actual</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($tablaPuntajesVeredas as $fila): ?>
                                                                <?php
                                                                $urlVereda = 'veredas_inestabilidad.php?id=' . urlencode($fila['id'])
                                                                    . '&mun=' . urlencode($municipioId)
                                                                    . '&inestabilidad=' . $inestabilidadId;
                                                                ?>
                                                                <tr>
                                                                    <td style="text-align:left;">
                                                                        <a href="<?= htmlspecialchars($urlVereda, ENT_QUOTES, 'UTF-8') ?>" class="vereda-link">
                                                                            <?= htmlspecialchars($fila['nombre_vereda'], ENT_QUOTES, 'UTF-8') ?>
                                                                        </a>
                                                                    </td>
                                                                    <td>
                                                                        <span class="puntaje-badge">
                                                                            <span class="color-dot" style="background:<?= htmlspecialchars($fila['color_inicial'], ENT_QUOTES, 'UTF-8') ?>;"></span>
                                                                            <?= number_format_puntaje($fila['puntaje_inicial']) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="puntaje-badge">
                                                                            <span class="color-dot" style="background:<?= htmlspecialchars($fila['color_actual'], ENT_QUOTES, 'UTF-8') ?>;"></span>
                                                                            <?= number_format_puntaje($fila['puntaje_actual']) ?>
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

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card g360-detail-card g360-company-card">
                                        <div class="card-header card-header-detalle-puntajes">
                                            <h5 class="m-b-0"><i class="bi bi-building"></i> Empresas adscritas al municipio</h5>
                                            <?php if (!empty($empresasMunicipio)): ?>
                                                <button type="button" class="btn btn-sm btn-toggle-detalle-puntajes" onclick="toggleDetallePuntajes('detalleEmpresasMunicipio', this)" aria-expanded="false" aria-controls="detalleEmpresasMunicipio">
                                                    <i class="bi bi-eye"></i> Ver detalle
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($empresasMunicipio)): ?>
                                                <div class="text-center text-muted p-4">No hay empresas registradas para este municipio.</div>
                                            <?php else: ?>
                                                <p class="detalle-puntajes-resumen" id="resumenEmpresasMunicipio">
                                                    <?= count($empresasMunicipio) ?> empresa(s) registrada(s). Pulse «Ver detalle» para desplegar el listado completo.
                                                </p>
                                                <div id="detalleEmpresasMunicipio" class="detalle-puntajes-wrap" style="display:none;">
                                                    <div style="border:1px solid rgba(255,255,255,.08);border-radius:14px;margin-top:12px;overflow-x:auto;background:transparent;">
                                                        <table style="width:100%;border-collapse:collapse;background:transparent;margin:0;">
                                                            <thead>
                                                                <tr style="background:rgba(255,255,255,.04);border-bottom:2px solid rgba(255,255,255,.10);">
                                                                    <th style="padding:10px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:left;border:none;">Empresa</th>
                                                                    <th style="padding:10px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:110px;">NIT</th>
                                                                    <th style="padding:10px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:left;border:none;">Contacto</th>
                                                                    <th style="padding:10px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:120px;">Teléfono</th>
                                                                    <th style="padding:10px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:left;border:none;">Email</th>
                                                                    <?php if ($efView || $efCreate): ?>
                                                                    <th style="padding:10px 8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:140px;">Factores</th>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($empresasMunicipio as $empresa):
                                                                    $empId = (int) ($empresa['id'] ?? 0);
                                                                    $empNombre = (string) ($empresa['nombre_empresa'] ?? '');
                                                                    $empFactoresN = (int) ($efCountByEmpresa[$empId] ?? 0);
                                                                ?>
                                                                    <tr style="background:transparent;border-bottom:1px solid rgba(255,255,255,.06);">
                                                                        <td style="padding:10px 8px;vertical-align:middle;text-align:left;font-size:12px;font-weight:600;color:#ffffff;line-height:1.35;word-break:break-word;overflow-wrap:anywhere;"><?= htmlspecialchars($empNombre, ENT_QUOTES, 'UTF-8') ?></td>
                                                                        <td style="padding:10px 8px;vertical-align:middle;text-align:center;font-size:12px;font-weight:600;color:#ffffff;"><?= htmlspecialchars(!empty($empresa['nit']) ? $empresa['nit'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
                                                                        <td style="padding:10px 8px;vertical-align:middle;text-align:left;font-size:12px;font-weight:600;color:#ffffff;word-break:break-word;"><?= htmlspecialchars($empresa['nombre_contacto'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                                        <td style="padding:10px 8px;vertical-align:middle;text-align:center;font-size:12px;font-weight:600;color:#ffffff;"><?= htmlspecialchars($empresa['telefono_contacto'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                                        <td style="padding:10px 8px;vertical-align:middle;text-align:left;font-size:12px;font-weight:600;color:#ffffff;word-break:break-word;"><?= htmlspecialchars(!empty($empresa['email_contacto']) ? $empresa['email_contacto'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
                                                                        <?php if ($efView || $efCreate): ?>
                                                                        <td style="padding:10px 8px;vertical-align:middle;text-align:center;white-space:nowrap;">
                                                                            <?php if ($efView): ?>
                                                                            <button type="button" class="btn btn-sm btn-outline-info ef-ver-factores text-white"
                                                                                data-empresa-id="<?= $empId ?>"
                                                                                data-empresa-nombre="<?= htmlspecialchars($empNombre, ENT_QUOTES, 'UTF-8') ?>"
                                                                                title="Ver factores asociados"
                                                                                style="margin:0 2px;">
                                                                                <i class="bi bi-diagram-3"></i>
                                                                                <?php if ($empFactoresN > 0): ?><span class="badge badge-light"><?= $empFactoresN ?></span><?php endif; ?>
                                                                            </button>
                                                                            <?php endif; ?>
                                                                            <?php if ($efCreate): ?>
                                                                            <button type="button" class="btn btn-sm btn-success ef-asociar-empresa text-white"
                                                                                data-empresa-id="<?= $empId ?>"
                                                                                data-empresa-nombre="<?= htmlspecialchars($empNombre, ENT_QUOTES, 'UTF-8') ?>"
                                                                                title="Asociar a un factor"
                                                                                style="margin:0 2px;">
                                                                                <i class="bi bi-plus-lg"></i>
                                                                            </button>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <?php endif; ?>
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

                            
                            <?php if (!empty($diferencias)): ?>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card g360-changes-card">
                                        <div class="card-body py-2">
                                            <strong style="color:#ffc107;">Veredas con cambios detectados (Inicial → Actual):</strong>
                                            <?php foreach ($diferencias as $d): ?>
                                                <span class="badge rounded-pill px-3 py-2 me-2" style="background:rgba(255,255,255,.08);color:#fff;font-weight:700;">
                                                    <?= htmlspecialchars($d['nombre']) ?>: <?= $d['inicial'] ?> → <?= $d['actual'] ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="card g360-detail-card g360-factor-progress-card">
                                        <div class="card-header">
                                            <div class="g360-section-heading">
                                                <span>
                                                    <i class="feather icon-bar-chart-2"></i>
                                                </span>

                                                <div>
                                                    <small>Consolidado comparativo</small>
                                                    <h5>Avance por factor de inestabilidad</h5>
                                                    <p>
                                                        Contrasta cantidades iniciales y actuales,
                                                        geolocalización, empresas y actualizaciones.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body" id="divConsolidado">
                                            <?php if (!empty($tabs)): ?>
                                                <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                                                    <?php foreach ($tabs as $index => $tab): ?>
                                                        <li class="nav-item">
                                                            <a class="nav-link <?= $index === 0 ? 'active' : '' ?>"
                                                                id="tab-<?= $tab['id'] ?>" data-toggle="pill"
                                                                href="#content-<?= $tab['id'] ?>" role="tab"
                                                                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                                                <span class="tab-icon-wrap"><img src="<?= htmlspecialchars($tab['icono'] ?? 'assets/iconos/gobierno.png') ?>" alt="<?= htmlspecialchars($tab['nombre']) ?>" width="24" height="24"></span>
                                                                <span class="tab-label"><?= htmlspecialchars($tab['nombre']) ?></span>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <div class="tab-content">
                                                    <?php foreach ($tabs as $index => $tab): ?>
                                                        <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>"
                                                            id="content-<?= $tab['id'] ?>" role="tabpanel">
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
                                                                        'pilar' => $item['pilar'] ?? ''
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
                                                                        'pilar' => $item['pilar'] ?? ''
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
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:140px;">Cant. Actual</th>
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:60px;">Unidad</th>
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:50px;">Geo</th>
                                                                                 <?php if ($efView || $efCreate): ?>
                                                                                 <th style="padding:10px 6px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#ffffff;text-align:center;border:none;width:120px;">Empresas</th>
                                                                                 <?php endif; ?>
                                                                             </tr></thead>
                                                                         <tbody>
                                                                             <?php foreach ($agrupado as $d):
                                                                                 $factorIdRow = (int) $d['tbl_factor_id'];
                                                                                 $factorNombreRow = (string) $d['factor'];
                                                                                 $empAsocN = (int) ($efCountByFactor[$factorIdRow] ?? 0);
                                                                                 $actN = (int) ($factoresConActualizacion[$factorIdRow] ?? 0);
                                                                             ?>
                                                                                 <tr style="background:transparent;border-bottom:1px solid rgba(255,255,255,.06);">
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;"><img src="<?= htmlspecialchars($d['icono']) ?>" alt="" width="26" style="border-radius:6px;"></td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:left;font-size:12px;font-weight:600;color:#ffffff;"><?= htmlspecialchars($d['factor']) ?></td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;"><span style="font-size:11px;font-weight:600;color:#ffffff;background:rgba(46,125,50,.15);padding:3px 10px;border-radius:6px;display:inline-block;border:1px solid rgba(46,125,50,.25);"><?= number_format_mun($d['total_cantidad_inicial']) ?></span></td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;">
                                                                                         <span style="display:inline-flex;align-items:center;gap:6px;justify-content:center;flex-wrap:wrap;">
                                                                                             <span style="font-size:11px;font-weight:700;color:#ffffff;background:rgba(30,136,229,.15);padding:3px 10px;border-radius:6px;display:inline-block;border:1px solid rgba(30,136,229,.25);"><?= number_format_mun($d['total_cantidad_actual']) ?></span>
                                                                                             <?php if ($actN > 0): ?>
                                                                                             <button type="button"
                                                                                                 class="btn-act-detalle"
                                                                                                 title="Ver detalle de actualizaciones"
                                                                                                 onclick="verDetalleActualizacionFactor(<?= $factorIdRow ?>, <?= htmlspecialchars(json_encode($factorNombreRow), ENT_QUOTES, 'UTF-8') ?>)">
                                                                                                 <i class="bi bi-info-circle"></i>
                                                                                                 <?php if ($actN > 1): ?><span class="badge badge-light" style="margin-left:2px;"><?= $actN ?></span><?php endif; ?>
                                                                                             </button>
                                                                                             <?php endif; ?>
                                                                                         </span>
                                                                                     </td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;font-size:11px;color:#ffffff;font-weight:600;"><?= htmlspecialchars($d['tipo_medicion']) ?></td>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;">
                                                                                         <button style="background:rgba(26,188,156,.15);border:1px solid rgba(26,188,156,.25);border-radius:6px;padding:3px 6px;border:none;cursor:pointer;line-height:1;"
                                                                                             onclick="mostrarInformacionPilarByMunicipio(<?= $factorIdRow ?>)">
                                                                                             <img src="assets/iconos/geo.png" alt="Geo" width="16" style="filter:brightness(0) invert(1);">
                                                                                         </button>
                                                                                     </td>
                                                                                     <?php if ($efView || $efCreate): ?>
                                                                                     <td style="padding:8px 4px;vertical-align:middle;text-align:center;" class="text-nowrap">
                                                                                         <?php if ($efView): ?>
                                                                                         <button type="button" class="btn btn-sm btn-outline-info ef-ver-empresas text-white"
                                                                                             data-factor-id="<?= $factorIdRow ?>"
                                                                                             data-factor-nombre="<?= htmlspecialchars($factorNombreRow, ENT_QUOTES, 'UTF-8') ?>"
                                                                                             title="Ver empresas asociadas">
                                                                                             <i class="bi bi-building"></i>
                                                                                             <?php if ($empAsocN > 0): ?><span class="badge badge-light"><?= $empAsocN ?></span><?php endif; ?>
                                                                                         </button>
                                                                                         <?php endif; ?>
                                                                                         <?php if ($efCreate): ?>
                                                                                         <button type="button" class="btn btn-sm btn-success ef-asociar-factor text-white"
                                                                                             data-factor-id="<?= $factorIdRow ?>"
                                                                                             data-factor-nombre="<?= htmlspecialchars($factorNombreRow, ENT_QUOTES, 'UTF-8') ?>"
                                                                                             title="Asociar empresa">
                                                                                             <i class="bi bi-plus-lg"></i>
                                                                                         </button>
                                                                                         <?php endif; ?>
                                                                                     </td>
                                                                                     <?php endif; ?>
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
                                                <div class="text-center text-muted p-4"><i class="bi bi-inbox" style="font-size:2rem;"></i><p class="mt-2">No hay información disponible para este municipio y factor de inestabilidad.</p></div>
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

    <!-- Modal detalle actualizaciones del factor -->
    <div class="modal fade" id="modalDetalleActualizacion" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content g360-instability-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloDetalleActualizacion">Detalle de actualización</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="bodyDetalleActualizacion"><div class="act-empty">Cargando…</div></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal foto ampliada -->
    <div class="modal fade" id="modalFotoActualizacion" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content g360-instability-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Foto</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <img id="imgFotoActualizacion" src="" alt="Foto actualización">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Geolocalización -->
    <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl centered" role="document">
            <div class="modal-content g360-instability-modal">
                <div class="modal-header"><h5 class="modal-title">Geolocalización: <span id="nombrePilar"></span></h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body"><div id="map" style="height:600px;width:100%;border-radius:14px;overflow:hidden;"></div></div>
            </div>
        </div>
    </div>

    <!-- Modal Compromiso -->
    <div class="modal fade" id="modalSeleccionar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content g360-instability-modal">
                <div class="modal-header g360-modal-header-success">
                    <h5 class="modal-title w-100 text-center"><i class="feather icon-edit"></i> Asignar Compromiso</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div id="alertaCompromiso" class="w-100 text-center p-2" style="display:none;"></div>
                <div class="modal-body g360-commitment-modal-body">
                    <form id="formCompromiso">
                        <input type="hidden" id="factorIdModal" name="factorIdModal">
                        <input type="hidden" id="veredaId" name="veredaId" value="0">
                        <input type="hidden" id="municipioId" name="municipioId" value="<?= $municipioId ?>">
                        <input type="hidden" id="departamentoId" name="departamentoId" value="<?= Util::getDepartamentoPrincipal() ?>">
                        <input type="hidden" id="tbl_municipio_id" name="tbl_municipio_id" value="<?= $municipioId ?>">
                        <input type="hidden" id="pilarId" name="pilarId" value="10000">
                        <input type="hidden" id="latitud" value="<?= htmlspecialchars($informacionMunicipio['latitud'] ?? '') ?>">
                        <input type="hidden" id="longitud" value="<?= htmlspecialchars($informacionMunicipio['longitud'] ?? '') ?>">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="cantidadActual">Cantidad Actual</label>
                                <input type="number" class="form-control text-center" id="cantidadActual" name="cantidadActual" disabled>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="cantidadCompromiso">Cantidad</label>
                                <input type="number" class="form-control text-center" id="cantidadCompromiso" name="cantidadCompromiso" placeholder="Ingrese la cantidad">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="actoresId">Seleccione un Actor</label>
                                <select class="form-control text-center" id="actoresId" name="actoresId"><?= $optionActores ?></select>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="observacionesCompromiso">Observaciones</label>
                                <textarea class="form-control text-center" id="observacionesCompromiso" name="observacionesCompromiso" rows="2" placeholder="Ingrese las observaciones"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarCompromisoManual();">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal formulario asociación Empresa ↔ Factor -->
    <div class="modal fade" id="modalEmpresaFactorForm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content g360-instability-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="efModalTitle">Asociar empresa a factor</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ef_id" value="">
                    <input type="hidden" id="ef_mode" value="from_factor">
                    <input type="hidden" id="ef_codigo_muncipio" value="<?= htmlspecialchars((string) $municipioId, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="ef_empresa_id_fixed" value="">
                    <p class="ef-hint mb-0" id="ef_contexto"></p>

                    <div class="form-group" id="ef_wrap_factor_fixed">
                        <span class="ef-field-label">Factor</span>
                        <div class="ef-readonly-box" id="ef_factor_label"></div>
                    </div>
                    <div class="form-group" id="ef_wrap_factor_select" style="display:none;">
                        <label class="ef-field-label" for="ef_factor_id_select">Factor <span class="text-danger">*</span></label>
                        <select class="form-control" id="ef_factor_id_select" style="width:100%"></select>
                    </div>

                    <div class="form-group" id="ef_wrap_empresa_select">
                        <label class="ef-field-label" for="ef_empresa_id">Empresa del municipio <span class="text-danger">*</span></label>
                        <select class="form-control" id="ef_empresa_id" style="width:100%"></select>
                    </div>
                    <div class="form-group" id="ef_wrap_empresa_fixed" style="display:none;">
                        <span class="ef-field-label">Empresa</span>
                        <div class="ef-readonly-box" id="ef_empresa_label"></div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="ef-field-label" for="ef_compromiso">
                            Compromiso <span style="font-weight:500;opacity:.7;">(opcional, máx. 500)</span>
                        </label>
                        <textarea class="form-control" id="ef_compromiso" rows="3" maxlength="500"
                            placeholder="Ej.: apadrinar jornada de limpieza, aportar insumos, etc."></textarea>
                        <small class="text-muted d-block mt-1" style="font-size:11px;">Texto breve de a qué se compromete la empresa con este factor.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEmpresaFactor">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal listado asociaciones (ver / editar / eliminar) -->
    <div class="modal fade" id="modalEmpresaFactorLista" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content g360-instability-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="efListaTitulo">Asociaciones</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="efListaContext">
                    <div id="efListaBody"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="mapTooltip" class="map-tooltip"></div>
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script type="text/javascript" src="admin/js/municipios.js"></script>
    <script type="text/javascript" src="admin/js/mapa_municipio_geo.js"></script>
    <script>
      window.EF_MUNICIPIO = <?= json_encode((string) $municipioId) ?>;
      window.EF_PERMS = {
        view: <?= $efView ? 'true' : 'false' ?>,
        create: <?= $efCreate ? 'true' : 'false' ?>,
        update: <?= $efUpdate ? 'true' : 'false' ?>,
        delete: <?= $efDelete ? 'true' : 'false' ?>
      };
      window.EF_EMPRESAS = <?= json_encode(array_map(static function ($e) {
          return [
              'id' => (int) ($e['id'] ?? 0),
              'nombre_empresa' => (string) ($e['nombre_empresa'] ?? ''),
              'nit' => (string) ($e['nit'] ?? ''),
          ];
      }, $empresasMunicipio), JSON_UNESCAPED_UNICODE) ?>;
      window.EF_FACTORES = <?= json_encode($factoresParaAsociar, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="<?= Util::versionar('./admin/js/municipios_empresa_factor.js') ?>"></script>
    <script>
    (function () {
      var MUN = <?= json_encode((string) $municipioId) ?>;

      function esc(s) {
        return String(s == null ? '' : s)
          .replace(/&/g, '&amp;').replace(/</g, '&lt;')
          .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
      }

      function showBsModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
          jQuery(el).modal('show');
          return;
        }
        if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function') {
          bootstrap.Modal.getOrCreateInstance(el).show();
        }
      }

      window.verDetalleActualizacionFactor = function (factorId, factorNombre) {
        jQuery('#tituloDetalleActualizacion').text('Actualizaciones — ' + (factorNombre || 'Factor'));
        jQuery('#bodyDetalleActualizacion').html('<div class="act-empty">Cargando…</div>');
        showBsModal('modalDetalleActualizacion');

        jQuery.ajax({
          url: 'admin/ajax/rqst.php',
          type: 'POST',
          dataType: 'json',
          data: {
            op: 'factor_actualizacion_detalle',
            codigo_municipio: MUN,
            tbl_factor_id: factorId
          }
        }).done(function (res) {
          var rows = (res && res.output && res.output.valid) ? (res.output.response || []) : [];
          if (!rows.length) {
            jQuery('#bodyDetalleActualizacion').html('<div class="act-empty">No hay actualizaciones registradas para este factor.</div>');
            return;
          }

          var html = '';
          rows.forEach(function (r) {
            var obs = (r.observaciones_actualizacion || '').trim();
            html += '<div class="act-card">';
            html += '<div class="act-meta">';
            if (r.dtcreate) html += '<span><strong>Fecha:</strong> ' + esc(r.dtcreate) + '</span>';
            if (r.nombre_vereda) html += '<span><strong>Vereda:</strong> ' + esc(r.nombre_vereda) + '</span>';
            if (r.nombre_actor) html += '<span><strong>Actor:</strong> ' + esc(r.nombre_actor) + '</span>';
            html += '</div>';
            html += '<div class="act-obs">' + (obs ? esc(obs) : '<span style="opacity:.55">Sin observaciones de actualización.</span>') + '</div>';

            var fotos = r.fotos || [];
            if (fotos.length) {
              html += '<div class="act-fotos">';
              fotos.forEach(function (src) {
                html += '<img src="' + esc(src) + '" alt="Foto" onclick="verFotoActualizacion(this.src)" loading="lazy">';
              });
              html += '</div>';
            }
            html += '</div>';
          });
          jQuery('#bodyDetalleActualizacion').html(html);
        }).fail(function () {
          jQuery('#bodyDetalleActualizacion').html('<div class="act-empty">Error al cargar el detalle.</div>');
        });
      };

      window.verFotoActualizacion = function (src) {
        jQuery('#imgFotoActualizacion').attr('src', src || '');
        showBsModal('modalFotoActualizacion');
      };
    })();
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>

    <script>
        function toggleDetallePuntajes(containerId, btn) {
            var container = document.getElementById(containerId);
            if (!container || !btn) return;
            var resumenMap = {
                detallePuntajesMunicipios: 'resumenPuntajesMunicipios',
                detallePuntajesVeredas: 'resumenPuntajesVeredas',
                detalleEmpresasMunicipio: 'resumenEmpresasMunicipio'
            };
            var resumen = document.getElementById(resumenMap[containerId] || '');
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

        function mostrarAlerta(tipo, mensaje) {
            const alerta = $("#alertaCompromiso");
            if (tipo === "error") {
                alerta.removeClass("bg-success").addClass("bg-danger text-white");
            } else {
                alerta.removeClass("bg-danger").addClass("bg-success text-white");
            }
            alerta.html(mensaje).fadeIn();
            setTimeout(() => { alerta.fadeOut(); }, 3000);
        }
        function guardarCompromisoManual() {
            const cantidad = $("#cantidadCompromiso").val();
            const actor = $("#actoresId").val();
            const observaciones = $("#observacionesCompromiso").val();
            const factorId = $("#factorIdModal").val();
            const cantidadActual = $("#cantidadActual").val();
            const tbl_vereda_id = $("#veredaId").val() || 0;
            const codigo_municipio = $("#municipioId").val();
            const codigo_departamento = $("#departamentoId").val();
            if (!factorId) { mostrarAlerta("error", "❌ No se encontró un Factor válido."); return; }
            if (!cantidad || isNaN(cantidad) || cantidad <= 0) { mostrarAlerta("error", "❌ Debes ingresar una cantidad válida."); return; }
            if (!cantidadActual || isNaN(cantidadActual) || cantidadActual <= 0) { mostrarAlerta("error", "❌ Debes ingresar una cantidad válida."); return; }
            if (!actor || actor === "") { mostrarAlerta("error", "❌ Debes seleccionar un actor."); return; }
            $.ajax({
                url: "admin/ajax/rqst.php",
                type: "POST",
                data: { op: "guardarCompromiso", tbl_vereda_id: tbl_vereda_id, codigo_municipio: codigo_municipio, codigo_departamento: codigo_departamento, factorId: factorId, cantidadActual: cantidadActual, cantidad: cantidad, actor: actor, observaciones: observaciones || "" },
                dataType: "json",
                success: function(response) {
                    if (response.output && response.output.valid) {
                        mostrarAlerta("success", "✅ Compromiso guardado correctamente.");
                        setTimeout(function() {
                            $("#modalSeleccionar").modal("hide");
                            $(".modal-backdrop").remove();
                            $("body").removeClass("modal-open");
                            setTimeout(function() { location.reload(); }, 2000);
                        }, 2000);
                    } else {
                        mostrarAlerta("error", response.output.response ? (response.output.response.content || "Error") : "Error");
                    }
                },
                error: function() { mostrarAlerta("error", "❌ Error de comunicación."); }
            });
        }
        document.addEventListener("DOMContentLoaded", function() {
            try { if (typeof MUNICIPIO !== 'undefined') MUNICIPIO.init(); } catch(e) { console.log("MUNICIPIO.init skipped"); }
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
            $('#modalGeocalizacion').on('shown.bs.modal', function() {
                if (typeof markers !== 'undefined' && markers.length > 0 && typeof map !== 'undefined' && map) {
                    setTimeout(function() {
                        var bounds = new google.maps.LatLngBounds();
                        markers.forEach(function(m) { if (m && m.getPosition) bounds.extend(m.getPosition()); });
                        if (!bounds.isEmpty()) map.fitBounds(bounds);
                        if (map.getZoom() > 16) map.setZoom(14);
                    }, 800);
                }
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
                var t = this.getAttribute("title") || "";
                var c = this.getAttribute("data-cantidad") || "";
                showTip(e, t + "<small>punt: " + c + "</small>");
            });
            el.addEventListener("mousemove", moveTip);
            el.addEventListener("mouseleave", hideTip);
        });
    });
    </script>
</body>
</html>
