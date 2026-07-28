<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
$permissions = PagePermissions::crudForCurrentPage();
include './admin/classes/Colombia.php';
include './admin/classes/Departamento.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Pilar.php';
include './admin/classes/Puntaje.php';
include './admin/classes/Area.php';
require './admin/include/georeferenciacion.php';
include './admin/classes/Actores.php';
include './admin/classes/CompromisosFactorPilar.php';
// Permisos
if (!$view) {
    require 'permiso_denegado.php';
    exit;
} */

$codigoTodos = Util::codigoTodos();
$departamentoEstatico = Util::getDepartamentoPrincipal();

$userType = SessionData::getUserType();
$municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
$isAlcaldeOAuxiliarAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());

$municipioURL = trim($_REQUEST['mun'] ?? '');
$pilarURL = trim($_REQUEST['pilar'] ?? '');

$departamento = $departamentoEstatico;

if ($isAlcaldeOAuxiliarAlcalde && !empty($municipioUsuarioLogueado)) {
    // municipio de consulta de sesion
    $municipio = $municipioUsuarioLogueado;
} elseif (!empty($municipioURL) && !empty($pilarURL)) {
    $municipio = $municipioURL;
} else {
    $municipio = Util::getCodigoMunicipioPrincipal();
    $pilarURL = Util::getIdentificadorPilarPrincipal();
?>
    <script type='text/javascript'>
        alert('Información de municipio/pilar no es correcta. Redirigiendo a vista principal.');
        window.location =
            'municipios.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&dep=<?php echo $departamentoEstatico; ?>&pilar=<?php echo Util::getIdentificadorPilarPrincipal(); ?>';
    </script>
<?php
    exit();
}

// Si no hay pilar en la URL, mostrar "Todos" por defecto al llegar por primera vez
$pilar = $pilarURL ?: $codigoTodos;
$municipioIdFinal = $municipio;

$arr = ['codigo_departamento' => $departamento, 'codigo_municipio' => $municipioIdFinal, 'pilar' => $pilar];

if ($pilar == $codigoTodos) {
    $data = Colombia::calcularColorPorMunicipioTodosPilares($arr);
} else {
    $data = Colombia::calcularColorPorMunicipioByPilarId($arr);
}

$isvalid = $arr['output']['valid'] ?? false;
$municipiosDepartamento = $data['output']['response'] ?? null;

$municipio = Ciudad::getInformacionCiudad(array('codigo_muncipio' => $municipioIdFinal));
$informacionMunicipio = $municipio['output']['response'][0] ?? null;
$nombreMunicipio = isset($informacionMunicipio['municipio']) ? ($informacionMunicipio['municipio']) : '';
$latitud = isset($informacionMunicipio['latitud']) ? ($informacionMunicipio['latitud']) : '';
$longitud = isset($informacionMunicipio['longitud']) ? ($informacionMunicipio['longitud']) : '';

if ($pilar == $codigoTodos) {
    $dataConsolidado = Colombia::consultarConsolidadTodosLosPilaresFactores($arr);
} else {
    $dataConsolidado = Colombia::consultarConsolidadPilaresFactores($arr);
}
$isvalidConsolidado = $dataConsolidado['output']['valid'] ?? false;
$responseConsolidadoPilares = $dataConsolidado['output']['response'] ?? null;
$tabs = $dataConsolidado['output']['tabs'] ?? null;

//Valor actual
$dataConsolidadoActual = null;
$responseConsolidadoPilaresActual = null;

if ($municipioIdFinal && $departamento && $pilar) {
    if ($pilar == $codigoTodos) {
        $dataConsolidadoActual = Colombia::consultarConsolidadPilaresFactoresActuales($arr);
    } else {
        $dataConsolidadoActual = Colombia::consultarConsolidadPilaresFactoresActuales($arr);
    }

    if (($dataConsolidadoActual['output']['valid'] ?? false)) {
        $responseConsolidadoPilaresActual = $dataConsolidadoActual['output']['response'] ?? null;
    }
}

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];

function limpiarVereda($nombre)
{
    $nombre = strtoupper(trim($nombre));
    $correcciones = [
        'GUAUSA' => 'GAUSA',
    ];
    return $correcciones[$nombre] ?? $nombre;
}

$arrDep = $arrDep['output']['response'];
$optionDep = $departamentoEstatico;
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == $departamentoEstatico ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de Actores
$responseActors = Actores::getAll(null);
if (!empty($responseActors['output']['valid'])) {
    $arrActores = $responseActors['output']['response'];
    $optionActores = array_reduce($arrActores, function ($carry, $val) {
        return $carry . "<option value='{$val['id']}'>{$val['nombre']}</option>";
    }, '');
} else {
    $optionActores = '';
}

// Información de compromisos
$responseCompromisosFactores = CompromisosFactorPilar::getCompromisosFactores([
    'pilarId' => $pilar,
    'municipioId' => $municipioIdFinal
]);

$compromosisoIsValid = $responseCompromisosFactores['output']['valid'];
$responseCompromisos = $responseCompromisosFactores['output']['response'];

// Información de Pilares
$response = Pilar::getAll(null);
if (!empty($response['output']['valid'])) {
    $arrPilar = $response['output']['response'];

    $optionPilar = "<option value='$codigoTodos'" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";

    $optionPilar .= array_reduce($arrPilar, function ($carry, $val) use ($pilar) {
        $selected = ($val['id'] == $pilar) ? ' selected' : '';
        return $carry . "<option value='{$val['id']}'{$selected}>{$val['nombre']}</option>";
    }, '');
} else {
    $optionPilar = "<option value='$codigoTodos '" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<body class="">
    <style>
        /* ==========================================================
           MUNICIPIOS - UI UPGRADE PRO SaaS (RESPONSIVE)
           NO se cambian IDs, ni clases, ni JS
        ========================================================== */

        :root {
            --au-bg-0: #070A12;
            --au-bg-1: #0B1020;
            --au-card: rgba(255, 255, 255, .06);
            --au-card-2: rgba(255, 255, 255, .085);
            --au-stroke: rgba(255, 255, 255, .10);
            --au-text: rgba(255, 255, 255, .92);
            --au-muted: rgba(255, 255, 255, .68);
            --au-primary: #2E6BFF;
            --au-primary-2: #19D3FF;
            --au-success: #18ff6d;
            --au-shadow: 0 18px 55px rgba(0, 0, 0, .55);
            --au-radius: 18px;
            --au-radius-lg: 22px;
        }

        body {
            background:
                radial-gradient(1200px 900px at 12% 8%, rgba(46, 107, 255, .28), transparent 55%),
                radial-gradient(900px 700px at 88% 18%, rgba(25, 211, 255, .20), transparent 50%),
                radial-gradient(700px 600px at 65% 86%, rgba(24, 255, 109, .10), transparent 55%),
                linear-gradient(180deg, var(--au-bg-0), var(--au-bg-1));
            color: var(--au-text);
        }

        .card {
            border: 1px solid var(--au-stroke) !important;
            background: linear-gradient(180deg, var(--au-card), rgba(255, 255, 255, .035)) !important;
            box-shadow: var(--au-shadow);
            border-radius: var(--au-radius-lg) !important;
        }

        .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, .09) !important;
            background: linear-gradient(90deg, rgba(46, 107, 255, .20), rgba(25, 211, 255, .12), rgba(255, 255, 255, .02)) !important;
            padding: 1rem 1.15rem !important;
            border-radius: calc(var(--au-radius-lg) - 1px) calc(var(--au-radius-lg) - 1px) 0 0 !important;
        }

        .card-body {
            padding: 1.05rem !important;
        }

        .card-header h5,
        .card-header h4,
        .card-header h3 {
            color: #fff !important;
            opacity: 1 !important;
            text-shadow: 0 2px 10px rgba(0, 0, 0, .55);
            margin: 0 !important;
            font-weight: 900 !important;
        }

        .breadcrumb {
            background: transparent !important;
        }

        .breadcrumb-item a {
            color: var(--au-muted) !important;
        }

        .breadcrumb-item:last-child a,
        .breadcrumb-item.active {
            color: #fff !important;
            font-weight: 700;
        }

        /* =======================
           MAPA - RESPONSIVE REAL
        ======================= */
        #contenido-mapa {
            position: relative;
            border-radius: 18px;
            background:
                radial-gradient(600px 360px at 50% 35%, rgba(255, 255, 255, .07), transparent 60%),
                linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .02));
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .15);
            padding: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #contenido-mapa svg {
            width: 100% !important;
            height: auto !important;
            min-height: 520px !important;
            max-height: 750px !important;
            max-width: 100% !important;
            display: block;
            filter: drop-shadow(0 18px 25px rgba(0, 0, 0, .45));
        }

        @media (min-width: 1400px) {
            #contenido-mapa svg {
                min-height: 620px !important;
                max-height: 900px !important;
            }
        }

        @media (max-width: 767px) {
            #contenido-mapa svg {
                min-height: 320px !important;
                max-height: 450px !important;
            }
        }

        @media (max-width: 767px) {
            #contenido-mapa {
                padding: .5rem !important;
                max-height: 380px;
            }

            #contenido-mapa svg {
                max-height: 340px !important;
            }
        }

        #contenido-mapa polygon,
        #contenido-mapa path {
            transition: filter .18s ease, transform .18s ease, opacity .18s ease;
            cursor: pointer;
        }

        #contenido-mapa polygon:hover,
        #contenido-mapa path:hover {
            filter: brightness(1.08) saturate(1.08);
            transform: translateY(-0.4px);
        }

        /* =======================
           BOTON GMAPS PRO
        ======================= */
        .btn-gmaps {
            background: rgba(255, 255, 255, .10) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            color: #fff !important;
            padding: 8px 16px !important;
            border-radius: 999px !important;
            font-weight: 800 !important;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 14px 40px rgba(0, 0, 0, .25);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
        }

        .btn-gmaps:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 18px 55px rgba(46, 107, 255, .25);
        }

        .gmaps-icon {
            font-size: 18px;
            color: #fff !important;
            text-shadow: 0 0 1px rgba(255, 255, 255, 0.8);
        }

        /* Header del mapa: distribución equitativa */
        .card-header.d-flex.justify-content-center.align-items-center.gap-3.flex-wrap {
            justify-content: space-between !important;
            gap: .75rem !important;
        }

        .card-header.d-flex.justify-content-center.align-items-center.gap-3.flex-wrap h5 {
            flex: 1 1 auto;
            text-align: left;
        }

        @media (max-width: 767px) {
            .card-header.d-flex.justify-content-center.align-items-center.gap-3.flex-wrap {
                flex-direction: column;
                align-items: stretch !important;
            }

            .btn-gmaps {
                width: 100% !important;
                justify-content: center !important;
            }
        }

        /* =======================
           INFO MUNICIPIO
        ======================= */
        .municipio-info-block {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem;
            font-size: .95rem;
        }

        .municipio-info-block>div {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 14px;
            padding: .75rem .85rem;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .08);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .municipio-info-block>div:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, .25);
        }

        .municipio-info-block strong {
            color: #fff;
            font-size: .95rem;
            font-weight: 900;
        }

        .municipio-info-block span {
            color: rgba(255, 255, 255, .88);
        }

        @media (max-width: 1200px) {
            .municipio-info-block {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .municipio-info-block {
                grid-template-columns: 1fr;
            }
        }

        /* =======================
           SELECTS PRO (mejor toque / legibilidad)
        ======================= */
        .form-group h5 {
            color: #fff !important;
            font-weight: 900 !important;
            text-shadow: 0 1px 8px rgba(0, 0, 0, .45);
            margin-bottom: .45rem !important;
        }

        .form-control {
            border-radius: 14px !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            background: rgba(255, 255, 255, 0.62) !important;
            color: #0f0f0f !important;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .15);
        }

        select.form-control {
            height: 46px !important;
            padding: 10px 44px 10px 14px !important;
            font-weight: 800;
            letter-spacing: .15px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, rgba(255, 255, 255, .9) 50%),
                linear-gradient(135deg, rgba(255, 255, 255, .9) 50%, transparent 50%);
            background-position:
                calc(100% - 18px) 19px,
                calc(100% - 12px) 19px;
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            cursor: pointer;
        }

        select.form-control:disabled {
            background-color: rgba(255, 255, 255, 0.62) !important;
            color: #0f0f0f !important;
            opacity: 1 !important;
            -webkit-text-fill-color: #0f0f0f !important;
            cursor: not-allowed;
        }

        select.form-control:focus {
            outline: none !important;
            border-color: rgba(46, 107, 255, .75) !important;
            box-shadow: 0 0 0 4px rgba(46, 107, 255, .22) !important;
        }

        select.form-control option {
            color: #111;
        }

        /* Tu layout de selects (conservado + refinado) */
        .row-movil {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .row-movil .form-group {
            width: 100%;
            max-width: 380px;
            text-align: left;
        }

        @media (min-width: 992px) {
            .row-movil {
                justify-content: center !important;
            }

            .row-movil .form-group {
                flex: 1 1 45% !important;
                max-width: 46%;
            }
        }

        /* =======================
           TABS PILARES - Tarjetas uniformes
        ======================= */
        #myTab.nav-tabs {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            padding: 0.5rem 0 1.25rem 0;
            margin-bottom: 1rem;
            border-bottom: 2px solid rgba(255,255,255,.10) !important;
            gap: 0.6rem;
            align-items: stretch;
        }

        #myTab.nav-tabs .nav-item {
            position: relative;
            flex: 1 1 80px;
            max-width: 110px;
        }

        #myTab.nav-tabs .nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 0.8rem 0.5rem 0.7rem;
            width: 100%;
            height: 100%;
            min-height: 95px;
            cursor: pointer;
            background: rgba(255,255,255,.08) !important;
            backdrop-filter: blur(8px);
            border-radius: 14px !important;
            border: 1.5px solid rgba(255,255,255,.14) !important;
            box-shadow: 0 3px 10px rgba(0,0,0,.20);
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
            gap: 8px;
            color: rgba(255,255,255,.65) !important;
            text-decoration: none !important;
            white-space: normal !important;
        }

        #myTab.nav-tabs .nav-link .tab-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            background: rgba(255,255,255,.10);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .18s ease, box-shadow .18s ease;
        }

        #myTab.nav-tabs .nav-link .tab-icon-wrap img {
            width: 28px;
            height: 28px;
            object-fit: contain;
            filter: brightness(0.85) saturate(0.80);
            transition: filter .18s ease, transform .18s ease;
            display: block;
        }

        #myTab.nav-tabs .tab-label {
            font-size: 0.65rem;
            font-weight: 600;
            line-height: 1.3;
            text-align: center;
            word-break: break-word;
            white-space: normal;
            width: 100%;
            color: inherit;
            display: block;
        }

        /* — Hover — */
        #myTab.nav-tabs .nav-link:hover {
            transform: translateY(-3px);
            background: rgba(255,255,255,.15) !important;
            border-color: rgba(255,255,255,.28) !important;
            color: #fff !important;
            box-shadow: 0 8px 20px rgba(0,0,0,.28);
        }

        #myTab.nav-tabs .nav-link:hover .tab-icon-wrap {
            background: rgba(255,255,255,.18);
        }

        #myTab.nav-tabs .nav-link:hover .tab-icon-wrap img {
            filter: brightness(1.05) saturate(1.1);
            transform: scale(1.08);
        }

        /* — Activo — */
        #myTab.nav-tabs .nav-link.active {
            background: linear-gradient(155deg, rgba(46,107,255,.90) 0%, rgba(25,211,255,.55) 100%) !important;
            color: #fff !important;
            border-color: rgba(25,211,255,.50) !important;
            box-shadow: 0 6px 22px rgba(46,107,255,.45), inset 0 1px 0 rgba(255,255,255,.22);
            transform: translateY(-3px);
        }

        #myTab.nav-tabs .nav-link.active .tab-icon-wrap {
            background: rgba(255,255,255,.22);
            box-shadow: 0 0 14px rgba(25,211,255,.45);
        }

        #myTab.nav-tabs .nav-link.active .tab-icon-wrap img {
            filter: brightness(1.2) saturate(1.3) drop-shadow(0 0 5px rgba(25,211,255,.60));
            transform: scale(1.06);
        }

        /* Punto indicador bajo tab activo */
        #myTab.nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 8px;
            height: 8px;
            background: rgba(25,211,255,.95);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(25,211,255,.75);
        }

        .tab-content {
            padding: .75rem .25rem !important;
            animation: fadeIn 0.3s ease-in-out;
            background: transparent !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Overrides for forced dark texts */
        .text-dark {
            color: #fff !important;
        }

        /* Botón compromiso */
        .btn-primary1 {
            border: 0 !important;
            background: linear-gradient(135deg, var(--au-primary), var(--au-primary-2)) !important;
            color: #fff !important;
            padding: .45rem .7rem !important;
            border-radius: 999px !important;
            box-shadow: 0 14px 40px rgba(46, 107, 255, .20);
        }

        /* =======================
           COMPROMISOS TABLE (mismo diseño que consolidado)
        ======================= */
        .table-veredas {
            background: #ffffff !important;
            border-collapse: separate;
            border-spacing: 0 6px;
            font-size: 12px;
            width: 100%;
        }

        .table-veredas thead th {
            background: #1f3a56 !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            font-size: 12px;
            letter-spacing: .3px;
            text-transform: uppercase;
            padding: 10px 8px !important;
            border: 0 !important;
        }

        .table-veredas tbody tr {
            background: #ffffff;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .08);
            border-radius: 12px;
        }

        .table-veredas tbody tr:hover {
            background: #f4f7fb !important;
        }

        .table-veredas tbody td {
            color: #0f172a !important;
            font-weight: 700;
            font-size: 12px;
            padding: 10px 8px !important;
            border: 0 !important;
            vertical-align: middle;
            background: #ffffff !important;
        }

        .table-veredas tbody tr:nth-child(even) td {
            background: #f4f7fb !important;
        }

        .table-veredas .text-muted {
            color: #4b5563 !important;
        }

        /* =======================
           MODALES PRO
        ======================= */
        .modal-content {
            border-radius: 20px !important;
            border: 1px solid rgba(255, 255, 255, .14) !important;
            background: linear-gradient(180deg, rgba(16, 20, 36, .92), rgba(10, 12, 24, .96)) !important;
            box-shadow: 0 30px 90px rgba(0, 0, 0, .65);
            overflow: hidden;
        }

        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, .10) !important;
            background: linear-gradient(90deg, rgba(46, 107, 255, .22), rgba(25, 211, 255, .14), rgba(255, 255, 255, .02)) !important;
        }

        .modal-title {
            color: #fff !important;
            font-weight: 900 !important;
        }

        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, .08) !important;
        }

        .btn.btn-secondary,
        .btn.btn-danger,
        .btn.btn-primary {
            border-radius: 999px !important;
        }

        .btn.btn-primary {
            border: 0 !important;
            font-weight: 900 !important;
            background: linear-gradient(135deg, var(--au-primary), var(--au-primary-2)) !important;
        }

        /* InfoWindow mini */
        .infowindow-mini {
            max-width: 220px;
            font-size: 12px;
            padding: 5px;
            color: #111111 !important;
            background: #ffffff !important;
        }

        .infowindow-mini h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #111111 !important;
        }

        .infowindow-mini p {
            margin: 2px 0;
            color: #111111 !important;
        }

        .infowindow-mini strong {
            color: #111111 !important;
        }

        #map {
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, .10);
            overflow: hidden;
        }
/* ==========================================================
   TABLA CONSOLIDADO – TEXTO NEGRO, COMPACTA Y CONTUNDENTE
========================================================== */

/* Base tabla */
#divConsolidado table{
  background:#ffffff !important;
  border-collapse: separate;
  border-spacing: 0 6px; /* aire entre filas */
  font-size: 12px;       /* más pequeña */
}

/* Header */
#divConsolidado table thead th{
  background:#1f3a56 !important;
  color:#ffffff !important;
  font-weight:900 !important;
  font-size:12px;
  letter-spacing:.3px;
  text-transform: uppercase;
  padding:10px 8px !important;
  border:0 !important;
}

/* Filas */
#divConsolidado table tbody tr{
  background:#ffffff;
  box-shadow: 0 6px 14px rgba(0,0,0,.08);
  border-radius:12px;
}

/* Hover firme */
#divConsolidado table tbody tr:hover{
  background:#f4f7fb !important;
}

/* Celdas */
#divConsolidado table td{
  color:#0f172a !important;   /* NEGRO real */
  font-weight:700;            /* más contundente */
  font-size:12px;
  padding:10px 8px !important;
  border:0 !important;
  vertical-align: middle;
}

/* Columna Factor (texto largo) */
#divConsolidado table td:nth-child(2){
  font-weight:800;
  font-size:12px;
  color:#020617 !important;
  line-height:1.25;
}

/* =======================
   BADGES DE CANTIDADES
======================= */

/* Cantidad inicial */
#divConsolidado table td span[style*="background-color: #f1f8e9"]{
  background:#eaf7ef !important;
  color:#065f46 !important;
  font-weight:900;
  font-size:11px;
  padding:4px 10px !important;
  border-radius:999px;
}

/* Cantidad actual */
#divConsolidado table td span[style*="background-color: #a2ded0"]{
  background:#99f6e4 !important;
  color:#064e3b !important;
  font-weight:900;
  font-size:11px;
  padding:4px 12px !important;
  border-radius:999px;
  box-shadow:none !important;
}

/* Unidad */
#divConsolidado table td .text-muted{
  color:#334155 !important;
  font-weight:800;
  font-size:11px;
}

/* =======================
   ICONOS (Geo + Editar)
======================= */

/* Botón Geo */
#divConsolidado table td button.btn-outline-primary{
  background:#ecfeff !important;
  border:1px solid #22d3ee !important;
  border-radius:10px;
  padding:4px 6px;
}
#divConsolidado table td button.btn-outline-primary img{
  width:20px;
}

/* Botón editar */
#divConsolidado table td .btn-primary1{
  background:#2563eb !important;
  color:#fff !important;
  width:34px;
  height:34px;
  border-radius:50% !important;
  display:flex;
  align-items:center;
  justify-content:center;
  box-shadow:0 6px 14px rgba(37,99,235,.35);
}

/* Íconos principales (columna ícono) */
#divConsolidado table td img[alt="Icono"]{
  width:28px;
  height:28px;
  background:#f8fafc;
  border-radius:10px;
  padding:4px;
  box-shadow:0 6px 12px rgba(0,0,0,.12);
}

/* =======================
   RESPONSIVE: SIN SCROLL
======================= */
#divConsolidado .table-responsive{
  overflow-x:hidden !important;
}

@media (max-width: 992px){
  #divConsolidado table{
    font-size:11px;
  }
  #divConsolidado table td{
    padding:8px 6px !important;
  }
}

    </style>

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Municipio</h5>
                            </div>
                            <ul class="breadcrumb" style="background-color: transparent !important;">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Avances por Tiempo</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 1: MAPA + INFO / SELECTS (SIN SCROLL) -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
                            <div>
                                <h5 class="mb-0 text-dark">Mapa del municipio</h5>
                                <small class="text-muted" style="font-size:.78rem;">
                                    <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($nombreMunicipio) ?>
                                </small>
                            </div>
                            <button type="button" class="btn btn-gmaps btn-sm"
                                onclick="getAllByMunicipio(document.getElementById('tbl_municipio_id').value)"
                                data-toggle="modal" data-target="#modalGeocalizacion">
                                <i class="bi bi-geo-alt-fill me-1 gmaps-icon"></i> Geolocalización
                            </button>
                        </div>

                        <div id="contenido-mapa" style="width: 100%; overflow: visible; text-align: center; padding: 0; box-sizing: border-box;">
                            <?php include './admin/classes/lupa.php'; ?>
                            <Tooltip id="my-tooltip" />

                            <?php
                            $viewBoxActual = !empty($informacionMunicipio['viewbox_svg']) ? $informacionMunicipio['viewbox_svg'] : '0 45 1518.36 900';
                            ?>

                            <svg id="b"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="<?= htmlspecialchars($viewBoxActual) ?>"
                                stroke-width="1.2px"
                                stroke="#fff"
                                style="width: 100%;"
                                preserveAspectRatio="xMidYMid meet">

                                <?php foreach ($municipiosDepartamento as $value): ?>
                                    <g id="<?= $value['nombre_svg'] ?>">
                                        <?php if (!empty($value['points'])): ?>
                                            <polygon points="<?= strtoupper($value['points']) ?>"
                                                fill="<?= strtolower($value['color_calculado']) ?>"
                                                fill-rule="evenodd"
                                                data-name="<?= strtolower($value['nombre_vereda']) ?>"
                                                data-tooltip-id="my-tooltip"
                                                data-tippy-content="<?= strtolower($value['nombre_vereda']) ?>"
                                                onClick="handlePolygonClick(this)"
                                                data-url="veredas.php?id=<?= $value['id'] ?>&mun=<?= $value['municipio_id'] ?>&dep=<?= $value['departamento_id'] ?>&pilar=<?= $pilar ?>"
                                                stroke-miterlimit="10" stroke-width="0.1px" />
                                        <?php elseif (!empty($value['path'])): ?>
                                            <path d="<?= $value['path'] ?>"
                                                title="<?= strtoupper(str_replace("-", " ", $value['nombre_vereda'])) ?>"
                                                style="fill:<?= strtolower($value['color_calculado']) ?>;"
                                                data-tooltip-id="my-tooltip"
                                                data-tippy-content="<?= strtolower($value['nombre_vereda']) ?>"
                                                onClick="handlePolygonClick(this)"
                                                data-url="veredas.php?id=<?= $value['id'] ?>&mun=<?= $value['municipio_id'] ?>&dep=<?= $value['departamento_id'] ?>&pilar=<?= $pilar ?>"
                                                stroke-miterlimit="10" stroke-width="0.1px" />
                                        <?php endif; ?>
                                    </g>
                                <?php endforeach; ?>

                                <?php foreach ($municipiosDepartamento as $value2): ?>
                                    <?php
                                    echo str_replace(
                                        '<tspan',
                                        '<tspan style="fill: black; font-family:IBM Plex Sans; stroke-width: 0.1px;"',
                                        $value2['tspan']
                                    );
                                    ?>
                                <?php endforeach; ?>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center gap-2 py-2">
                            <i class="bi bi-info-circle-fill" style="font-size:16px; color:#1A73E8;"></i>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size:.9rem;">Información del municipio</h6>
                        </div>
                        <div class="card-body d-flex flex-column gap-2 py-2">
                            <div class="municipio-info-block text-start">
                                <div>
                                    <strong>Alcalde:</strong><br>
                                    <span id="info-alcalde"><?php echo htmlspecialchars($informacionMunicipio['nombre_alcalde'] ?? 'No disponible'); ?></span>
                                </div>
                                <div>
                                    <strong>Partido:</strong><br>
                                    <span id="info-partido"><?php echo htmlspecialchars($informacionMunicipio['partido'] ?? 'No disponible'); ?></span>
                                </div>
                                <div style="text-align:center;">
                                    <strong>Población:</strong><br>
                                    <span id="info-poblacion" style="font-size:1.3rem; font-weight:900;">
                                        <?php
                                        $hombres = intval($informacionMunicipio['hombres'] ?? 0);
                                        $mujeres = intval($informacionMunicipio['mujeres'] ?? 0);
                                        echo number_format($hombres + $mujeres);
                                        ?>
                                    </span>
                                    <small class="text-muted d-block" style="font-size:.85rem;">
                                        <i class="bi bi-gender-male text-primary"></i> H: <span id="info-hombres"><?php echo number_format($hombres); ?></span>
                                        &nbsp;|&nbsp;
                                        <i class="bi bi-gender-female text-danger"></i> M: <span id="info-mujeres"><?php echo number_format($mujeres); ?></span>
                                    </small>
                                </div>
                            </div>

                            <div class="movil-select">
                                <input type="hidden" name="op" id="op" />
                                <input type="hidden" name="id" id="id" />
                                <input type="hidden" name="filtro" id="filtro" value="no" />
                                <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="no" />

                                <div id="cardHeaderCompleto">
                                    <input type="hidden" name="latitud" id="latitud" value="<?php echo $latitud ?>" />
                                    <input type="hidden" name="longitud" id="longitud" value="<?php echo $longitud ?>" />
                                </div>

                                <div class="d-flex flex-column gap-3">
                                    <div style="display:none">
                                        <select onchange="DEPARTAMENTO.getMunicipios()" class="form-control"
                                            id="tbl_departamento_id" name="tbl_departamento_id">
                                            <?php echo $optionDep; ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="fw-bold text-white mb-1" style="font-size:.85rem;">Municipio</label>
                                        <select onchange="MUNICIPIO.updateUrlMunicipio(this)"
                                            class="form-control"
                                            id="tbl_municipio_id" name="tbl_municipio_id"
                                            <?php echo $isAlcaldeOAuxiliarAlcalde ? 'disabled title="No puede cambiar el municipio"' : ''; ?>>
                                            <option value="<?= $municipioIdFinal ?>" selected>
                                                <?= $municipioIdFinal ?> - <?= $nombreMunicipio ?>
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="fw-bold text-white mb-1" style="font-size:.85rem;">Acción Unificada por pilares</label>
                                        <select class="form-control" id="pilarId" name="pilarId"
                                            onchange="MUNICIPIO.updateUrlPilar(this)">
                                            <?php echo $optionPilar; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 3: CONSOLIDADO TABS -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-bar-chart-fill" style="font-size:20px; color:#1A73E8;"></i>
                            <h4 class="mb-0 fw-bold text-dark" style="font-size:20px">Avance por Pilares</h4>
                        </div>
                        <div class="card-body" id="divConsolidado">
                            <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                                <?php foreach ($tabs as $index => $tab): ?>
                                    <?php if ($tab['enable'] === 'si'):
                                        $img = !empty($tab["icono"]) ?  htmlspecialchars($tab["icono"]) : 'assets/iconos/gobierno.png';
                                    ?>
                                        <li class="nav-item">
                                            <a class="nav-link <?= $index === 0 ? 'active' : '' ?>"
                                                id="tab-<?= htmlspecialchars($tab['id']) ?>" data-toggle="pill"
                                                href="#content-<?= htmlspecialchars($tab['id']) ?>" role="tab"
                                                aria-controls="content-<?= htmlspecialchars($tab['id']) ?>"
                                                aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                                <span class="tab-icon-wrap">
                                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($tab['nombre']) ?>">
                                                </span>
                                                <span class="tab-label"><?= htmlspecialchars($tab['nombre']) ?></span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>

                            <div class="tab-content" id="myTabContent">
                                <?php foreach ($tabs as $index => $tab): ?>
                                    <?php if ($tab['enable'] === 'si'): ?>
                                        <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>"
                                            id="content-<?= htmlspecialchars($tab['id']) ?>" role="tabpanel"
                                            aria-labelledby="tab-<?= htmlspecialchars($tab['id']) ?>">

                                            <?php
                                            $areaDataInicial = array_filter(
                                                $responseConsolidadoPilares,
                                                fn($item) => $item['area_id'] == $tab['id']
                                            );

                                            $agrupadoPorFactor = [];
                                            foreach ($areaDataInicial as $item) {
                                                $factor = strtoupper(trim($item['factor']));
                                                $medicion = strtoupper(trim($item['tipo_medicion']));
                                                $clave = $factor . '|' . $medicion;

                                                $cantidadInicial = floatval($item['total_cantidad']);

                                                if (!isset($agrupadoPorFactor[$clave])) {
                                                    $agrupadoPorFactor[$clave] = [
                                                        'factor' => $factor,
                                                        'tipo_medicion' => $medicion,
                                                        'total_cantidad_inicial' => $cantidadInicial,
                                                        'total_cantidad_actual' => 0.0,
                                                        'icono' => $item['icono'],
                                                        'tbl_factor_id' => intval($item['tbl_factor_id']),
                                                        'pilar' => $item['pilar']
                                                    ];
                                                } else {
                                                    $agrupadoPorFactor[$clave]['total_cantidad_inicial'] += $cantidadInicial;
                                                }
                                            }

                                            if (!empty($responseConsolidadoPilaresActual)) {
                                                $areaDataActual = array_filter(
                                                    $responseConsolidadoPilaresActual,
                                                    fn($item) => $item['area_id'] == $tab['id']
                                                );

                                                foreach ($areaDataActual as $itemActual) {
                                                    $factor = strtoupper(trim($itemActual['factor']));
                                                    $medicion = strtoupper(trim($itemActual['tipo_medicion']));
                                                    $clave = $factor . '|' . $medicion;

                                                    $cantidadActual = floatval($itemActual['total_cantidad_actual']);

                                                    if (isset($agrupadoPorFactor[$clave])) {
                                                        $agrupadoPorFactor[$clave]['total_cantidad_actual'] += $cantidadActual;
                                                    } else {
                                                        $agrupadoPorFactor[$clave] = [
                                                            'factor' => $factor,
                                                            'tipo_medicion' => $medicion,
                                                            'total_cantidad_inicial' => 0.0,
                                                            'total_cantidad_actual' => $cantidadActual,
                                                            'icono' => $itemActual['icono'],
                                                            'tbl_factor_id' => intval($itemActual['tbl_factor_id']),
                                                            'pilar' => $itemActual['pilar']
                                                        ];
                                                    }
                                                }
                                            }
                                            ?>

                                            <?php if (!empty($agrupadoPorFactor)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered align-middle text-center" style="width: 100%">
                                                        <thead class="thead-dark bg-primary text-white">
                                                            <tr>
                                                                <th>Ícono</th>
                                                                <th style="min-width: 150px;">Factor</th>
                                                                <th style="min-width: 100px;">Cantidad Inicial</th>
                                                                <th style="min-width: 100px;">Cantidad Actual</th>
                                                                <th>Unidad</th>
                                                                <th>Geo</th>
                                                                <th>Compromiso</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($agrupadoPorFactor as $data):
                                                                $cantidadCompromiso = floatval($data['total_cantidad_actual']);
                                                            ?>
                                                                <tr>
                                                                    <td><img src="<?= htmlspecialchars($data['icono']) ?>" alt="Icono" width="32px"></td>
                                                                    <td class="text-start fw-semibold text-dark" style="font-size: 12px; word-break: break-word; white-space: normal;font-weight: bold;">
                                                                        <?= htmlspecialchars($data['factor']) ?>
                                                                    </td>

                                                                    <td>
                                                                        <span style="font-size: 12px; font-weight: 500; color: #145a32; background-color: #f1f8e9; padding: 6px 14px; border-radius: 8px; display: inline-block;">
                                                                            <?= htmlspecialchars(number_format($data['total_cantidad_inicial'])) ?>
                                                                        </span>
                                                                    </td>

                                                                    <td>
                                                                        <span style="font-size: 12px; font-weight: bold; color: #0e6655; background-color: #a2ded0; padding: 6px 14px; border-radius: 12px; box-shadow: 0 0 6px rgba(26, 188, 156, 0.4); display: inline-block; min-width: 60px;">
                                                                            <?= htmlspecialchars(number_format($data['total_cantidad_actual'])) ?>
                                                                        </span>
                                                                    </td>

                                                                    <td><span class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($data['tipo_medicion']) ?></span></td>
                                                                    <td>
                                                                        <button style="color: #1abc9c; background: #cff8ff; border-radius: 5px;" type="button" class="btn btn-outline-primary btn-sm"
                                                                            onclick="mostrarInformacionPilarByMunicipio('<?= $data['tbl_factor_id'] ?>')">
                                                                            <img src="assets/iconos/geo.png" alt="Geo" width="25px">
                                                                        </button>
                                                                    </td>
                                                                    <td>
                                                                        <button class="btn btn-primary1 select-veredas" style="border-radius: 20px !important;" type="button" data-toggle="modal"
                                                                            data-target="#modalSeleccionar"
                                                                            data-pilar="<?= htmlspecialchars($data['pilar']) ?>"
                                                                            data-cantidad="<?= htmlspecialchars($cantidadCompromiso) ?>"
                                                                            onclick="MUNICIPIO.abrirModalCompromiso(<?= htmlspecialchars($data['tbl_factor_id']) ?>, <?= htmlspecialchars($cantidadCompromiso) ?>)">
                                                                            <i class="feather icon-edit"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <p>No hay datos disponibles para esta área.</p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 4: COMPROMISOS -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card w-100">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="feather icon-edit" style="font-size:20px;"></i>
                            <h4 class="mb-0 fw-bold" style="font-size:20px">Compromisos</h4>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table-veredas">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Observaciones</th>
                                            <th>Cantidad en su momento</th>
                                            <th>Cantidad compromiso</th>
                                            <th>Actor</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyCompromisos">
                                        <?php if ($compromosisoIsValid && !empty($responseCompromisos)): ?>
                                            <?php foreach ($responseCompromisos as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['dtcreate']) ?></td>
                                                    <td><?= htmlspecialchars($item['observaciones']) ?></td>
                                                    <td><?= htmlspecialchars($item['cantidad_instante']) ?></td>
                                                    <td><?= htmlspecialchars($item['cantidad']) ?></td>
                                                    <td><?= htmlspecialchars($item['actor']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No se encontraron registros.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Asignar Compromiso -->
            <div class="modal fade" id="modalSeleccionar" tabindex="-1" role="dialog" aria-labelledby="modalSeleccionarLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title w-100 text-center" id="modalSeleccionarLabel">
                                <i class="feather icon-edit"></i> Asignar Compromiso
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div id="alertaCompromiso" class="w-100 text-center p-2" style="display: none;"></div>

                        <div class="modal-body " style="padding: 45px;">
                            <form id="formCompromiso">
                                <input type="hidden" id="factorIdModal" name="factorIdModal">
                                <input type="hidden" id="veredaId" name="veredaId" value="<?php echo $vereda; ?>">
                                <input type="hidden" id="municipioId" name="municipioId" value="<?php echo $municipio; ?>">
                                <input type="hidden" id="departamentoId" name="departamentoId" value="<?php echo $departamento; ?>">

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
                                        <select class="form-control text-center" id="actoresId" name="actoresId">
                                            <?php echo $optionActores; ?>
                                        </select>
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
                            <button type="button" class="btn btn-primary" onclick="MUNICIPIO.guardarCompromiso();">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- modal de geocalizacion -->
            <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog"
                aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
                <div class="modal-dialog modal-xl centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización : <span id="nombrePilar"></span></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="map" style="height: 600px; width: 100%;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

        <?php include 'admin/include/gerenic_script.php'; ?>

        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>
        <script type="text/javascript" src="admin/js/departamento.js"></script>
        <script type="text/javascript" src="admin/js/municipios.js"></script>

        <script>
            MUNICIPIO.init();

            function handlePolygonClick(element) {
                const url = element.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                } else {
                    console.error('No se encontró una URL válida.');
                }
            }

            document.addEventListener("DOMContentLoaded", function() {
                const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
                tabLinks.forEach(tab => {
                    tab.addEventListener('click', function(event) {
                        event.preventDefault();
                        tabLinks.forEach(link => link.classList.remove('active'));
                        const tabPanes = document.querySelectorAll('.tab-pane');
                        tabPanes.forEach(pane => pane.classList.remove('show', 'active'));
                        this.classList.add('active');
                        const targetPane = document.querySelector(this.getAttribute('href'));
                        if (targetPane) targetPane.classList.add('show', 'active');
                    });
                });
            });
        </script>

        <!-- Google Maps JavaScript API -->
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
        </script>
        <script src="<?php echo Util::versionar('./admin/js/mapa_municipio_geo.js'); ?>"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                validarConsolidado();
            });

            function validarConsolidado() {
                setTimeout(() => {

                    const $div = $("#divConsolidado");

                    const hayTabs = $div.find("ul.nav-tabs li .nav-link").length > 0;
                    const hayContenidoTabs = $div.find(".tab-pane").length > 0;

                    const filasValidas = $div.find("table tbody tr").filter(function() {
                        return $(this).text().trim() !== "";
                    }).length > 0;

                    const textoPlano = $div.text().trim();
                    const contenidoVacio = textoPlano === "" || textoPlano.length < 15;

                    if (!hayTabs || !hayContenidoTabs || !filasValidas || contenidoVacio) {

                        $div.html(`
                            <div style="
                                padding:20px;
                                text-align:center;
                                background:#fff3cd;
                                border:1px solid #ffeeba;
                                color:#856404;
                                font-weight:600;
                                border-radius:8px;
                                margin-top:15px;">
                                NO HAY INFORMACIÓN DISPONIBLE PARA ESTE MUNICIPIO Y PILAR SELECCIONADO.
                            </div>
                        `);
                    }

                }, 300);
            }
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {

                const urlMunicipio = "<?= $municipioIdFinal ?>";
                const urlPilar = "<?= $pilar ?>";

                function fixMunicipio() {
                    const $sel = $("#tbl_municipio_id");
                    $sel.off("change");
                    $sel.val(urlMunicipio);
                    $sel.on("change", function() {
                        MUNICIPIO.updateUrlMunicipio(this);
                    });
                }

                function fixPilar() {
                    const $sel = $("#pilarId");
                    $sel.off("change");
                    $sel.val(urlPilar);
                    $sel.on("change", function() {
                        MUNICIPIO.updateUrlPilar(this);
                    });
                }

                let intentos = 0;
                const fixInterval = setInterval(() => {

                    intentos++;

                    if ($("#tbl_municipio_id option").length > 0) {
                        fixMunicipio();
                    }

                    if ($("#pilarId option").length > 0) {
                        fixPilar();
                    }

                    if (intentos > 20) clearInterval(fixInterval);

                }, 100);

            });
        </script>

</body>

</html>
