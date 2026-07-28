<?php
include './admin/include/head.php';
require './admin/include/georeferenciacion.php';
require './admin/include/generic_classes.php';
include './admin/classes/Colombia.php';
include './admin/classes/Departamento.php';
include './admin/classes/Vereda.php';
include './admin/classes/Pilar.php';
include './admin/classes/Area.php';
include './admin/classes/Actores.php';
include './admin/classes/CompromisosFactorPilar.php';


$codigoTodos = Util::codigoTodos();
// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

if (
    isset($_REQUEST['id'], $_REQUEST['dep'], $_REQUEST['mun'], $_REQUEST['pilar']) &&
    !empty(trim($_REQUEST['id'])) &&
    !empty(trim($_REQUEST['dep'])) &&
    !empty(trim($_REQUEST['pilar'])) &&
    !empty(trim($_REQUEST['mun']))
) {
    $vereda = trim($_REQUEST['id']);
    $municipio = trim($_REQUEST['mun']);

    $departamento = trim($_REQUEST['dep']);
    $pilar = trim($_REQUEST['pilar']);

    // Validar si el municipio y la vereda es válido cuando es un ALCALDE
    $municipioUsuarioLogueado = SessionData::getCodigoMunicipio();
    // Validacion por Alcalde
    /*     if (SessionData::getUserType() ===  Util::Alcalde()) {
        if ($municipioUsuarioLogueado != $municipio) { ?>
            <script type='text/javascript'>
                alert('No tiene permisos para ver este municipio y/o vereda.');
                window.location =
                    'municipios.php?mun=<?php echo SessionData::getCodigoMunicipio(); ?>&dep=<?php echo Util::getDepartamentoPrincipal(); ?>&pilar=<?php echo Util::getIdentificadorPilarPrincipal(); ?>';
                exit();
            </script>
<?php
        }
    } */

    // Obtener información de mapa
    $arr = ['codigo_departamento' => Util::getDepartamentoPrincipal(), 'codigo_municipio' => $municipio, 'veredaId' => $vereda, 'pilar' => $pilar, 'veredaId' => $vereda];




    if ($pilar == $codigoTodos) {
        $data = Colombia::calcularColorPorVeredaByTodosLosPilares($arr);
    } else {
        $data = Colombia::calcularColorPorVeredaByPilarId($arr);
    }


    $isvalid = true;

    $veredaMapa = $data['output']['response'] ?? null;
    $cantidadResultadoVereda = $veredaMapa[0]['cantidad_mostrar'] ?? 0;

    // Informacion de Veredas    
    $veredaResponse = Vereda::getAll(array('id' => $vereda));
    $informacionVereda = $veredaResponse['output']['response'][0] ?? null;
    $nombreVereda = isset($informacionVereda['nombre_vereda']) ? ($informacionVereda['nombre_vereda']) : '';


    if ($pilar == $codigoTodos) {
        $dataConsolidado = Colombia::consultarConsolidadTodosLosPilaresFactoresByVeredaId($arr);
    } else {
        // Información de consolidado por municipio de pilar, factor, eje
        $dataConsolidado = Colombia::consultarConsolidadPilaresFactoresByVeredaId($arr);
    }

    $isvalidConsolidado = $dataConsolidado['output']['valid'] ?? false;
    $responseConsolidadoPilares = $dataConsolidado['output']['response'] ?? null;
$tabs = $dataConsolidado['output']['pilares'] ?? [];
} else {
    require 'parametros_no_son_correctos.php';
}

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}


// Información de Pilares
$response = Pilar::getAll(null);
if (!empty($response['output']['valid'])) {
    $arrPilar = $response['output']['response'];

    // Agregar opción "Todos" al inicio
    $optionPilar = "<option value='$codigoTodos'" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";

    // Generar las demás opciones
    $optionPilar .= array_reduce($arrPilar, function ($carry, $val) use ($pilar) {
        $selected = ($val['id'] == $pilar) ? ' selected' : '';
        return $carry . "<option value='{$val['id']}'{$selected}>{$val['nombre']}</option>";
    }, '');
} else {
    // Solo la opción "Todos" si no hay datos
    $optionPilar = "<option value='$codigoTodos '" . ($pilar == $codigoTodos ? " selected" : "") . ">Todos</option>";
}

// Información de Actores
$responseActors = Actores::getAll(null);
if (!empty($responseActors['output']['valid'])) {
    $arrActores = $responseActors['output']['response'];
    // Generar las opciones en un solo paso
    $optionActores = array_reduce($arrActores, function ($carry, $val) {
        return $carry . "<option value='{$val['id']}'>{$val['nombre']}</option>";
    }, '');
} else {
    $optionActores = '';
}

// Información de compromisos
$parametrosCompromisoPilarId = array('pilarId' => $pilar, 'veredaId' => $vereda);
$responseCompromisosFactores = CompromisosFactorPilar::getCompromisosFactores($parametrosCompromisoPilarId);
$compromosisoIsValid = $responseCompromisosFactores['output']['valid'];
$responseCompromisos = $responseCompromisosFactores['output']['response'];
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<body class="">

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php
    include './admin/include/navbar.php';
    ?>

    <?php
    include './admin/include/header.php';
    ?>

    <style>
        /* ==========================================================
           VEREDAS - UI UPGRADE PRO SaaS (igual a municipios)
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
           MAPA
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
           BOTON GMAPS
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
           INFO VEREDA BLOCK (compacto 3 cols)
        ======================= */
        .municipio-info-block {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .75rem;
            font-size: .95rem;
        }

        .municipio-info-block > div {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .10);
            border-radius: 14px;
            padding: .75rem .85rem;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, .08);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .municipio-info-block > div:hover {
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

        @media (max-width: 576px) {
            .municipio-info-block {
                grid-template-columns: 1fr;
            }
        }

        /* =======================
           SELECTS
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

        /* =======================
           TABS PILARES
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
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .text-dark {
            color: #fff !important;
        }

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
            color: #334155 !important;
            font-weight: 800;
            font-size: 11px;
        }

        /* =======================
           MODALES
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

        .infowindow-mini {
            max-width: 220px;
            font-size: 12px;
            padding: 5px;
        }

        .infowindow-mini h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
        }

        .infowindow-mini p {
            margin: 2px 0;
        }

        #map {
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, .10);
            overflow: hidden;
        }

        /* =======================
           TABLA CONSOLIDADO
        ======================= */
        #divConsolidado table {
            background: #ffffff !important;
            border-collapse: separate;
            border-spacing: 0 6px;
            font-size: 12px;
        }

        #divConsolidado table thead th {
            background: #1f3a56 !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            font-size: 12px;
            letter-spacing: .3px;
            text-transform: uppercase;
            padding: 10px 8px !important;
            border: 0 !important;
        }

        #divConsolidado table tbody tr {
            background: #ffffff;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .08);
            border-radius: 12px;
        }

        #divConsolidado table tbody tr:hover {
            background: #f4f7fb !important;
        }

        #divConsolidado table td {
            color: #0f172a !important;
            font-weight: 700;
            font-size: 12px;
            padding: 10px 8px !important;
            border: 0 !important;
            vertical-align: middle;
        }

        #divConsolidado table td:nth-child(2) {
            font-weight: 800;
            font-size: 12px;
            color: #020617 !important;
            line-height: 1.25;
        }

        #divConsolidado table td span[style*="background-color: #f1f8e9"] {
            background: #eaf7ef !important;
            color: #065f46 !important;
            font-weight: 900;
            font-size: 11px;
            padding: 4px 10px !important;
            border-radius: 999px;
        }

        #divConsolidado table td span[style*="background-color: #a2ded0"] {
            background: #99f6e4 !important;
            color: #064e3b !important;
            font-weight: 900;
            font-size: 11px;
            padding: 4px 12px !important;
            border-radius: 999px;
            box-shadow: none !important;
        }

        #divConsolidado table td .text-muted {
            color: #334155 !important;
            font-weight: 800;
            font-size: 11px;
        }

        #divConsolidado table td button.btn-outline-primary {
            background: #ecfeff !important;
            border: 1px solid #22d3ee !important;
            border-radius: 10px;
            padding: 4px 6px;
        }

        #divConsolidado table td button.btn-outline-primary img {
            width: 20px;
        }

        #divConsolidado table td .btn-primary1 {
            background: #2563eb !important;
            color: #fff !important;
            width: 34px;
            height: 34px;
            border-radius: 50% !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 14px rgba(37, 99, 235, .35);
        }

        #divConsolidado table td img[alt="Icono"] {
            width: 28px;
            height: 28px;
            background: #f8fafc;
            border-radius: 10px;
            padding: 4px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, .12);
        }

        #divConsolidado .table-responsive {
            overflow-x: hidden !important;
        }

        @media (max-width: 992px) {
            #divConsolidado table {
                font-size: 11px;
            }

            #divConsolidado table td {
                padding: 8px 6px !important;
            }
        }
    </style>

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Veredas</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Acción Unificada / Estado Veredas</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 1: MAPA + INFO / SELECTS -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
                            <div>
                                <h5 class="mb-0 text-dark">Mapa de la vereda</h5>
                                <small class="text-muted" style="font-size:.78rem;">
                                    <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($nombreVereda) ?>
                                </small>
                            </div>
                            <button type="button" class="btn btn-gmaps btn-sm"
                                onclick="mostrarInformacionPilarByVereda('', '', 0)"
                                data-toggle="modal" data-target="#modalGeocalizacion">
                                <i class="bi bi-geo-alt-fill me-1 gmaps-icon"></i> Geolocalización
                            </button>
                        </div>
                        <div id="contenido-mapa" style="width:100%; overflow:visible; text-align:center; padding:0; box-sizing:border-box;">
                            <?php
                            $usePath = true;
                            foreach ($veredaMapa as $value) {
                                if (!empty($value['points'])) {
                                    $usePath = false;
                                    break;
                                }
                            }
                            ?>

                            <?php if ($usePath): ?>
                                <svg id="b" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1500 1500"
                                    strokeWidth="1.2px" stroke="#fff" width="100%"
                                    preserveAspectRatio="xMidYMid meet">

                                    <?php foreach ($veredaMapa as $key => $value) : ?>
                                        <g id="<?php echo $value['name']; ?>">
                                            <path d="<?php echo $value['path']; ?>"
                                                title="<?php echo strtoupper(str_replace("-", " ", $value['nombre_vereda'])); ?>"
                                                style="fill:<?php echo $value['color_calculado']; ?>" stroke="#f3c5c5"
                                                data-tooltip-id="my-tooltip"
                                                data-tippy-content="<?php echo strtolower($value['nombre_vereda']); ?>"
                                                onClick="handlePolygonClick(this)"
                                                data-url="<?php echo 'veredas.php?id=' . $value['id'] . '&mun=' . $value['municipio_id'] . '&dep=' . $value['departamento_id']; ?>"
                                                stroke-miterlimit="10" stroke-width="3px" />
                                        </g>
                                    <?php endforeach; ?>

                                    <?php foreach ($veredaMapa as $key => $value2) : ?>
                                        <?php
                                        echo str_replace(
                                            '<tspan',
                                            '<tspan style="fill: black; font-weight: bold; font-size: 13.5px; stroke: black; stroke-width: 0.2px;"',
                                            $value2['tspan']
                                        );
                                        ?>
                                    <?php endforeach; ?>
                                </svg>

                            <?php else: ?>

                                <svg id="b" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1318.36 900"
                                    strokeWidth="1.2px" stroke="#fff" width="100%"
                                    preserveAspectRatio="xMidYMid meet">

                                    <?php foreach ($veredaMapa as $key => $value) : ?>
                                        <g id="<?php echo strtoupper($value['name']); ?>">
                                            <polygon points="<?php echo strtoupper($value['points']); ?>"
                                                fill="<?php echo strtolower($value['color_calculado']); ?>"
                                                fill-rule="evenodd" stroke="#fff"
                                                data-name="<?php echo strtolower($value['nombre_vereda']); ?>"
                                                data-tooltip-id="my-tooltip"
                                                data-tippy-content="<?php echo strtolower($value['nombre_vereda']); ?>"
                                                onClick="handlePolygonClick(this)"
                                                data-url="<?php echo 'veredas.php?id=' . $value['id'] . '&mun=' . $value['municipio_id'] . '&dep=' . $value['departamento_id']; ?>"
                                                stroke-miterlimit="10" stroke-width="2" />
                                        </g>
                                    <?php endforeach; ?>

                                    <?php foreach ($municipiosDepartamento as $key => $value2) : ?>
                                        <?php
                                        echo str_replace(
                                            '<tspan',
                                            '<tspan style="fill: black; font-weight: bold; font-size: 13.5px; stroke: black; stroke-width: 0.2px;"',
                                            $value2['tspan']
                                        );
                                        ?>
                                    <?php endforeach; ?>
                                </svg>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center gap-2 py-2">
                            <i class="bi bi-info-circle-fill" style="font-size:16px; color:#19d3ff;"></i>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size:.9rem;">Información de la Vereda</h6>
                        </div>
                        <div class="card-body d-flex flex-column gap-2 py-2">
                            <div class="municipio-info-block text-start">
                                <div>
                                    <strong>Vereda</strong><br>
                                    <span><?= htmlspecialchars($nombreVereda) ?></span>
                                </div>
                                <div style="text-align:center;">
                                    <strong>Puntaje</strong><br>
                                    <span><?= htmlspecialchars($cantidadResultadoVereda) ?></span>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                <div style="display:none;">
                                    <select disabled class="form-control" id="tbl_departamento_id" name="tbl_departamento_id">
                                        <?php echo $optionDep; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="fw-bold text-white mb-1" style="font-size:.85rem;">Municipio</label>
                                    <select disabled class="form-control" id="tbl_municipio_id" name="tbl_municipio_id"></select>
                                </div>
                                <div>
                                    <label class="fw-bold text-white mb-1" style="font-size:.85rem;">Vereda</label>
                                    <select onchange="VEREDAS.updateUrlVereda(this)" class="form-control" id="tbl_vereda_id" name="tbl_vereda_id"></select>
                                </div>
                                <div>
                                    <label class="fw-bold text-white mb-1" style="font-size:.85rem;">Acción Unificada por pilares</label>
                                    <select class="form-control" id="pilarId" name="pilarId" onchange="VEREDAS.updateUrlPilar(this)">
                                        <?php echo $optionPilar; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 2: FACTORES / CONSOLIDADO -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-bar-chart-fill" style="font-size:20px; color:#19d3ff;"></i>
                            <h4 class="mb-0 fw-bold text-dark" style="font-size:20px">Factores de Inestabilidad</h4>
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
                                                <span class="tab-icon-wrap"><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($tab['nombre']) ?>"></span>
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
                                            $areaData = array_filter(
                                                $responseConsolidadoPilares,
                                                fn($item) => $item['area_id'] == $tab['id']
                                            );

                                            $agrupadoPorFactor = [];
                                            foreach ($areaData as $item) {
                                                $factor = strtoupper(trim($item['factor']));
                                                $medicion = strtoupper(trim($item['tipo_medicion']));
                                                $clave = $factor . '|' . $medicion;

                                                $cantidad_inicial = floatval($item['total_cantidad'] ?? 0);
                                                $cantidad_actual = floatval($item['total_cantidad_actual'] ?? 0);

                                                if (!isset($agrupadoPorFactor[$clave])) {
                                                    $agrupadoPorFactor[$clave] = [
                                                        'factor' => $factor,
                                                        'tipo_medicion' => $medicion,
                                                        'icono' => $item['icono'],
                                                        'factor_id' => intval($item['factor_id']),
                                                        'tbl_factor_id' => intval($item['tbl_factor_id']),
                                                        'pilar' => $item['pilar'],
                                                        'puntaje' => $item['puntaje'],
                                                        'longitud' => $item['longitud'],
                                                        'latitud' => $item['latitud'],
                                                        'total_cantidad' => $cantidad_inicial,
                                                        'total_cantidad_actual' => $cantidad_actual,
                                                    ];
                                                } else {
                                                    $agrupadoPorFactor[$clave]['total_cantidad'] += $cantidad_inicial;
                                                    $agrupadoPorFactor[$clave]['total_cantidad_actual'] += $cantidad_actual;
                                                }
                                            }
                                            ?>

                                            <?php if (!empty($agrupadoPorFactor)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered align-middle text-center" style="width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <th>Ícono</th>
                                                                <th style="min-width: 150px;">Factor</th>
                                                                <th style="min-width: 100px;">Cantidad Inicial</th>
                                                                <th style="min-width: 100px;">Cantidad Actual</th>
                                                                <th>Unidad de Medida</th>
                                                                <th>Geo</th>
                                                                <th>Compromiso</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($agrupadoPorFactor as $data): ?>
                                                                <tr>
                                                                    <td><img src="<?= htmlspecialchars($data['icono']) ?>" alt="Icono" width="32px"></td>
                                                                    <td class="text-start" style="font-size: 12px; word-break: break-word; white-space: normal; font-weight: bold; color: #222;">
                                                                        <?= htmlspecialchars($data['factor']) ?>
                                                                        <br><small class="text-muted" style="font-weight: normal;">Puntaje: <?= htmlspecialchars($data['puntaje']) ?></small>
                                                                    </td>
                                                                    <td>
                                                                        <span style="font-size: 12px; font-weight: 500; color: #145a32; background-color: #f1f8e9; padding: 6px 14px; border-radius: 8px; display: inline-block;">
                                                                            <?= htmlspecialchars(number_format($data['total_cantidad'])) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span style="font-size: 12px; font-weight: bold; color: #0e6655; background-color: #a2ded0; padding: 6px 14px; border-radius: 12px; box-shadow: 0 0 6px rgba(26,188,156,0.4); display: inline-block; min-width: 60px;">
                                                                            <?= htmlspecialchars(number_format($data['total_cantidad_actual'])) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><span class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($data['tipo_medicion']) ?></span></td>
                                                                    <td>
                                                                        <button style="color: #1abc9c; background: #cff8ff; border-radius: 5px;" type="button" class="btn btn-outline-primary btn-sm"
                                                                            onclick="mostrarInformacionPilarByVereda(
                                                                            '<?= htmlspecialchars($data['longitud']) ?>',
                                                                            '<?= htmlspecialchars($data['latitud']) ?>',
                                                                            '<?= htmlspecialchars($data['tbl_factor_id']) ?>')">
                                                                            <img src="assets/iconos/geo.png" alt="Geo" width="25px">
                                                                        </button>
                                                                    </td>
                                                                    <td>
                                                                        <button style="border-radius: 20px !important;" class="btn btn-primary1 select-veredas"
                                                                            type="button" data-toggle="modal" data-target="#modalSeleccionar"
                                                                            data-pilar="<?= htmlspecialchars($data['pilar']) ?>"
                                                                            data-cantidad="<?= htmlspecialchars($data['total_cantidad_actual']) ?>"
                                                                            onclick="VEREDAS.abrirModalCompromiso(
                                                                            <?= htmlspecialchars($data['tbl_factor_id']) ?>,
                                                                            <?= htmlspecialchars($data['total_cantidad_actual']) ?>)">
                                                                            <i class="feather icon-edit"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <p>No hay datos disponible para esta área.</p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 3: COMPROMISOS -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
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
                                                <td colspan="9" class="text-center">No se encontraron registros.</td>
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
                                <i class="feather icon-edit me-2"></i> Asignar Compromiso
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
                                <input type="hidden" id="pilarId" name="pilarId" value="<?php echo $pilar; ?>">

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="cantidadActual">Cantidad Actual</label>
                                        <input type="number" class="form-control input-icon text-center" id="cantidadActual" name="cantidadActual" disabled>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="cantidadCompromiso">Cantidad</label>
                                        <input type="number" class="form-control input-icon text-center" id="cantidadCompromiso" name="cantidadCompromiso" placeholder="Ingrese la cantidad">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="actoresId">Seleccione un Actor</label>
                                        <select class="form-control input-icon text-center" id="actoresId" name="actoresId">
                                            <?php echo $optionActores; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="observacionesCompromiso">Observaciones</label>
                                        <textarea class="form-control input-icon text-center" id="observacionesCompromiso" name="observacionesCompromiso" rows="2" placeholder="Ingrese las observaciones"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                <i class="feather icon-x-circle me-1"></i> Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" onclick="VEREDAS.guardarCompromiso();">
                                <i class="feather icon-save me-1"></i> Guardar
                            </button>
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
                            <h5 class="modal-title" id="modalGeocalizacionTitle">Geolocalización<span id="nombrePilar"></span></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
    </div>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <?php include 'admin/include/gerenic_script.php'; ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
    <script src="<?php echo Util::versionar('./admin/js/veredas.js'); ?>"></script>

    <!-- Google Maps JavaScript API -->
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap">
    </script>
    <script src="<?php echo Util::versionar('./admin/js/mapa_veredas_geo.js'); ?>"></script>

    <script>
        function mostrarAlerta(tipo, mensaje) {
            const alerta = $("#alertaCompromiso");

            if (tipo === "error") {
                alerta.removeClass("bg-success").addClass("bg-danger text-white");
            } else {
                alerta.removeClass("bg-danger").addClass("bg-success text-white");
            }

            alerta.html(mensaje).fadeIn();

            setTimeout(() => {
                alerta.fadeOut();
            }, 3000);
        }
    </script>
    <script>
        setTimeout(() => {
            initMap();
        }, 2000);
    </script>
</body>

</html>