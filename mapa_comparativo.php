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
    $final = str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists !== false) {
        $final = substr($final, 0, $exists);
    }
    return $final;
}

// ------------------------- MAPA 1: GestoraSocial -------------------------
include './admin/classes/Ciudad.php';
include './admin/classes/Estado.php';
include './admin/classes/Departamento.php';
include './admin/db/coloresg.php';
include './admin/classes/Maing.php';
include './admin/classes/Detalle.php';
include './admin/classes/Cuenta.php';
include './admin/classes/Cuentapro.php';
include './admin/classes/Secreinversion.php';
include './admin/classes/Munnovisitados.php';
include './admin/classes/GestoraSocial.php';
include './admin/classes/Colombia.php';
$permissions = PagePermissions::crudForCurrentPage();

/* if (!$permissions['view']) {
    require_once 'permiso_denegado.php';
    exit;
} */

// Obtener datos de GESTORA SOCIAL
$datosGestora = Maing::getDataMain(['modulo' => 'gestora']);
$validGestora = $datosGestora['output']['valid'];

$visitasGestora   = $validGestora ? intval($datosGestora['output']['visitas']) : 0;
$impactadaGestora = $validGestora ? intval($datosGestora['output']['impactada']) : 0;
$inversionGestora = $validGestora ? floatval($datosGestora['output']['inversion']) : 0;

// Obtener datos de ASPAS
$datosAspas = Maing::getDataMain(['modulo' => 'aspas']);
$validAspas = $datosAspas['output']['valid'];

$visitasAspas   = $validAspas ? intval($datosAspas['output']['visitas']) : 0;
$impactadaAspas = $validAspas ? intval($datosAspas['output']['impactada']) : 0;
$inversionAspas = $validAspas ? floatval($datosAspas['output']['inversion']) : 0;

// Sumar ambos
$visitas   = $visitasGestora + $visitasAspas;
$impactada = $impactadaGestora + $impactadaAspas;
$inversion = $inversionGestora + $inversionAspas;

$arrgestora = array('codigo' => Util::getDepartamentoPrincipal());
$datagestora = Colombia::getInformacionParaMapaGestoraSocial($arrgestora);
$isvalid = $datagestora['output']['valid'];
$santandergestora =  $datagestora['output']['response'];

// ------------------------- MAPA 2: GestoraSocialAspas -------------------------
include './admin/classes/GestoraSocialAspas.php';

$arraspas = array('codigo' => Util::getDepartamentoPrincipal());
$dataaspas = Colombia::getInformacionParaMapaGestoraSocialAspas($arraspas);
$isvalidaspas = $dataaspas['output']['valid'];
$santanderaspas =  $dataaspas['output']['response'];

?>

<!-- jQuery (deja SOLO esta versión) -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

<script src="https://cdn.datatables.net/select/2.0.0/js/select.bootstrap4.min.js"></script>

<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ navigation menu ] start -->
    <?php include './admin/include/navbar.php'; ?>
    <!-- [ navigation menu ] end -->

    <!-- [ Header ] start -->
    <?php include './admin/include/header.php'; ?>
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">

                            <!-- HERO + breadcrumb PRO -->
                            <div class="gs-hero">
                                <div class="gs-hero__bg"></div>
                                <div class="gs-hero__content">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div>
                                            <div class="gs-kicker">
                                                <span class="gs-dot"></span>
                                                <span>Panel Ejecutivo • Gobierno / Social</span>
                                            </div>
                                            <h2 class="gs-title mb-1">Dashboard Gestión Social</h2>
                                            <div class="gs-subtitle">Visualiza visitas, población impactada e inversión. Explora mapas por red de valor.</div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <?php include './admin/include/btn_back.php'; ?>
                                        </div>
                                    </div>

                                    <div class="gs-breadcrumb mt-3">
                                        <a href="index.html"><i class="feather icon-home"></i></a>
                                        <span class="sep">/</span>
                                        <span>Gestión Social</span>
                                        <span class="sep">/</span>
                                        <span class="active">Actividades</span>
                                    </div>
                                </div>
                            </div>

                            <!-- FILTRO RED -->
                            <div class="gs-filter-bar mb-3">
                                <div class="gs-filter-label"><i class="feather icon-filter"></i> Vista</div>
                                <div class="btn-group gs-filter-group" role="group" aria-label="Filtro red de valor">
                                    <button type="button" class="btn gs-filter-btn" data-view="primera_dama">Ver Red de Valor Social 1</button>
                                    <button type="button" class="btn gs-filter-btn" data-view="aspas">Ver Red de Valor Social 2</button>
                                    <button type="button" class="btn gs-filter-btn active" data-view="ambos">Red de Valor Social 1 y 2</button>
                                </div>
                            </div>

                            <!-- KPI CARDS PRO -->
                            <div class="gs-kpis" id="gsKpis">
                                <div class="gs-kpi-card" data-kpi="primera_dama">
                                    <div class="gs-kpi-icon gs-blue">
                                        <i class="feather icon-map-pin"></i>
                                    </div>
                                    <div class="gs-kpi-meta">
                                        <div class="gs-kpi-label">Total Visitas Departamento</div>
                                        <div class="gs-kpi-value"><?php echo number_format($visitasGestora, 0, '.', ','); ?></div>
                                        <div class="gs-kpi-hint">Red de Valor Social 1</div>
                                    </div>
                                </div>

                                <div class="gs-kpi-card" data-kpi="primera_dama">
                                    <div class="gs-kpi-icon gs-red">
                                        <i class="feather icon-users"></i>
                                    </div>
                                    <div class="gs-kpi-meta">
                                        <div class="gs-kpi-label">Total Población Impactada</div>
                                        <div class="gs-kpi-value"><?php echo number_format($impactadaGestora, 0, '.', ','); ?></div>
                                        <div class="gs-kpi-hint">Red de Valor Social 1</div>
                                    </div>
                                </div>

                                <div class="gs-kpi-card" data-kpi="aspas">
                                    <div class="gs-kpi-icon gs-purple">
                                        <i class="feather icon-map-pin"></i>
                                    </div>
                                    <div class="gs-kpi-meta">
                                        <div class="gs-kpi-label">Total Visitas Departamento</div>
                                        <div class="gs-kpi-value"><?php echo number_format($visitasAspas, 0, '.', ','); ?></div>
                                        <div class="gs-kpi-hint">Red de Valor Social 2</div>
                                    </div>
                                </div>

                                <div class="gs-kpi-card" data-kpi="aspas">
                                    <div class="gs-kpi-icon gs-green">
                                        <i class="feather icon-users"></i>
                                    </div>
                                    <div class="gs-kpi-meta">
                                        <div class="gs-kpi-label">Total Población Impactada</div>
                                        <div class="gs-kpi-value"><?php echo number_format($impactadaAspas, 0, '.', ','); ?></div>
                                        <div class="gs-kpi-hint">Red de Valor Social 2</div>
                                    </div>
                                </div>
                            </div>

                            <!-- MAPAS -->
                            <div class="row">
                                <!-- MAPA 1 -->
                                <div class="col-sm-6 gs-map-col" data-map="primera_dama">
                                    <div class="card gs-card">
                                        <div class="card-header gs-card-header">
                                            <div class="d-flex align-items-center justify-content-between w-100">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="gs-card-badge gs-blue"><i class="feather icon-map"></i></div>
                                                    <div>
                                                        <h5 class="mb-0 color">Red de Valor Social 1</h5>
                                                    </div>
                                                </div>

                                                <div class="card-header-right">
                                                    <div class="btn-group card-option">
                                                        <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="feather icon-more-horizontal"></i>
                                                        </button>
                                                        <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                                            <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                                            <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                                            <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                                                            <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Remover</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gs-map-wrap">
                                            <div class="gs-map-toolbar">
                                                <div class="gs-pill"><i class="feather icon-mouse-pointer"></i> Click en un municipio para ver detalle</div>
                                                <div class="gs-pill"><i class="feather icon-info"></i> Colores según participación</div>
                                            </div>

                                            <div id="contenido-mapa-gestora" class="gs-map">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="20 40 1100 1200">
                                                    <?php foreach ($santandergestora as $key => $value) : ?>
                                                        <g id="<?php echo strtoupper($value['path']); ?>">
                                                            <path
                                                                id="<?php echo strtoupper($value['path']); ?>"
                                                                d="<?php echo $value['d']; ?>"
                                                                fill="<?php echo getColorByNumGestoraSocial($value["num_val"]) ?>"
                                                                class="municipios mapaClick <?php echo getClasePorcentaje($value['porcentaje_participacion']); ?>"
                                                                data-url="<?php echo getUrl() . 'estado_municipios_gestora.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] . '&tipo=primera_dama'; ?>"
                                                                data-name="<?php echo strtolower($value['municipio']); ?>"
                                                                title="<?php echo strtoupper(str_replace("-", " ", $value['nombre_mapa'])); ?>"
                                                                stroke="#000"
                                                                stroke-miterlimit="10"
                                                                stroke-width="0.1px"></path>
                                                            <text transform="translate(264.48 382.8)" font-family="IBM Plex Sans" font-size="10" font-weight="500"></text>
                                                        </g>
                                                    <?php endforeach; ?>

                                                    <?php require_once 'nombres_mapa_santander.php' ?>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- MAPA 2 -->
                                <div class="col-sm-6 gs-map-col" data-map="aspas">
                                    <div class="card gs-card">
                                        <div class="card-header gs-card-header">
                                            <div class="d-flex align-items-center justify-content-between w-100">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="gs-card-badge gs-purple"><i class="feather icon-map"></i></div>
                                                    <div>
                                                        <h5 class="mb-0 color">Red de Valor Social 2</h5>
                                                    </div>
                                                </div>

                                                <div class="card-header-right">
                                                    <div class="btn-group card-option">
                                                        <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="feather icon-more-horizontal"></i>
                                                        </button>
                                                        <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                                            <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                                            <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                                            <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                                                            <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Remover</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="gs-map-wrap">
                                            <div class="gs-map-toolbar">
                                                <div class="gs-pill"><i class="feather icon-mouse-pointer"></i> Click en un municipio para ver detalle</div>
                                                <div class="gs-pill"><i class="feather icon-shield"></i> </div>
                                            </div>

                                            <div id="contenido-mapa-aspas" class="gs-map">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="20 40 1100 1200">
                                                    <?php foreach ($santanderaspas as $key => $value) : ?>
                                                        <g id="<?php echo strtoupper($value['path']); ?>">
                                                            <path
                                                                id="<?php echo strtoupper($value['path']); ?>"
                                                                d="<?php echo $value['d']; ?>"
                                                                fill="<?php echo getColorByNumGestoraSocial($value["num_val"]) ?>"
                                                                class="municipios mapaClick <?php echo getClasePorcentaje($value['porcentaje_participacion']); ?>"
                                                                data-url="<?php echo getUrl() . 'estado_municipios_gestora.php?mun=' . $value['codigo_muncipio'] . '&dep=' . $value['codigo_departamento'] . '&tipo=aspas'; ?>"
                                                                data-name="<?php echo strtolower($value['municipio']); ?>"
                                                                title="<?php echo strtoupper(str_replace("-", " ", $value['nombre_mapa'])); ?>"
                                                                stroke="#000"
                                                                stroke-miterlimit="10"
                                                                stroke-width="0.1px"></path>
                                                            <text transform="translate(264.48 382.8)" font-family="IBM Plex Sans" font-size="10" font-weight="500"></text>
                                                        </g>
                                                    <?php endforeach; ?>

                                                    <?php include 'nombres_mapa_santander.php' ?>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- /row -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET["route_map"])) : ?>
        <?php endif ?>

        <?php include 'admin/include/footer.php'; ?>
        <?php include 'admin/include/gerenic_script.php'; ?>

        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>

        <!-- prism Js -->
        <script src="assets/js/plugins/prism.js"></script>
        <script src="assets/js/plugins/apexcharts.min.js"></script>

        <script src="admin/js/gestora_social.js"></script>
        <script src="admin/js/gestora_social_aspas.js"></script>

        <style>
            /* Evita que el hover de tu CSS anterior “mate” el nuevo UI */
            .santander.munis path:hover,
            .santander.munis polygon:hover {
                transform: none !important;
                filter: none !important;
                stroke: none !important;
                fill: inherit !important;
                pointer-events: auto !important;
            }
        </style>

        <!-- Bootstrap 5 bundle (lo dejas porque ya lo estabas usando para tooltip) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
            crossorigin="anonymous"></script>

        <script>
            // Tooltips a imágenes (igual que tu lógica, solo seguro)
            $("img").each(function(index, el) {
                $(this).attr("data-bs-toggle", "tooltip");
                $(this).attr("data-bs-placement", "left");
                tooltip = new bootstrap.Tooltip($(this)[0], {})
            });

            // Click mapa -> navega a detalle (igual que tu lógica)
            $(".mapaClick").on("click", function() {
                location.href = $(this).data("url");
            });

            // Filtro Red 1 / Red 2 / Ambos (default: ambos)
            function applyRedView(view) {
                var show1 = (view === 'primera_dama' || view === 'ambos');
                var show2 = (view === 'aspas' || view === 'ambos');

                $('[data-kpi="primera_dama"]').toggle(show1);
                $('[data-kpi="aspas"]').toggle(show2);
                $('[data-map="primera_dama"]').toggle(show1);
                $('[data-map="aspas"]').toggle(show2);

                // Si solo una red, el mapa ocupa todo el ancho
                $('[data-map]').toggleClass('col-sm-12', view !== 'ambos');
                $('[data-map]').toggleClass('col-sm-6', view === 'ambos');

                $('.gs-filter-btn').removeClass('active');
                $('.gs-filter-btn[data-view="' + view + '"]').addClass('active');
            }

            $('.gs-filter-btn').on('click', function() {
                applyRedView($(this).data('view'));
            });

            applyRedView('ambos');
        </script>
</body>

</html>

<style>
    /* ===========================
       🔥 UI SAAS / WOW - GESTIÓN SOCIAL
       (solo diseño - no toca backend)
    ============================ */

    :root {
        --gs-bg0: #0b1220;
        --gs-bg1: #0e1830;
        --gs-card: rgba(255, 255, 255, .06);
        --gs-stroke: rgba(255, 255, 255, .10);
        --gs-text: rgba(255, 255, 255, .92);
        --gs-muted: rgba(255, 255, 255, .65);
        --gs-shadow: 0 18px 50px rgba(0, 0, 0, .35);
        --gs-radius: 18px;
    }

    .color{
        color:rgb(255, 255, 255) !important;
}
    /* Fondo general pro */
    .pcoded-main-container {
        background:
            radial-gradient(900px 600px at 15% 10%, rgba(120, 88, 255, .18), transparent 55%),
            radial-gradient(900px 600px at 85% 15%, rgba(0, 187, 255, .14), transparent 55%),
            linear-gradient(180deg, var(--gs-bg0), var(--gs-bg1));
        min-height: 100vh;
    }

    /* HERO */
    .gs-hero {
        position: relative;
        border-radius: 22px;
        overflow: hidden;
        margin: 12px 0 16px;
        box-shadow: var(--gs-shadow);
        border: 1px solid rgba(255, 255, 255, .08);
        background: rgba(255, 255, 255, .06);
    }

    .gs-hero__bg {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(900px 500px at 20% 10%, rgba(0, 187, 255, .22), transparent 60%),
            radial-gradient(900px 500px at 80% 10%, rgba(120, 88, 255, .22), transparent 60%),
            linear-gradient(135deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .02));
        filter: saturate(1.2);
    }

    .gs-hero__content {
        position: relative;
        padding: 18px 18px 16px;
        color: var(--gs-text);
    }

    .gs-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: .3px;
        color: var(--gs-muted);
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .gs-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(0, 187, 255, 1), rgba(120, 88, 255, 1));
        box-shadow: 0 0 0 4px rgba(255, 255, 255, .06);
    }

    .gs-title {
        font-weight: 800;
        letter-spacing: .2px;
        margin: 0;
        color: var(--gs-muted);
    }

    .gs-subtitle {
        color: var(--gs-muted);
        font-size: 13px;
        margin-top: 2px;
    }

    .gs-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 12px;
        color: var(--gs-muted);
    }

    .gs-breadcrumb a {
        color: rgba(255, 255, 255, .85);
        text-decoration: none;
    }

    .gs-breadcrumb .sep {
        opacity: .5;
    }

    .gs-breadcrumb .active {
        color: rgba(255, 255, 255, .95);
        font-weight: 600;
    }

    /* KPIs */
    .gs-kpis {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin: 14px 0 18px;
    }
    @media (min-width: 992px) {
        .gs-kpis {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .gs-filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,.10);
        background: rgba(255,255,255,.05);
        box-shadow: 0 12px 30px rgba(0, 0, 0, .25);
        margin-bottom: 4px;
    }
    .gs-filter-label {
        color: rgba(255,255,255,.78);
        font-weight: 800;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .gs-filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .gs-filter-btn {
        border-radius: 999px !important;
        border: 1px solid rgba(255,255,255,.14) !important;
        background: rgba(0,0,0,.22) !important;
        color: rgba(255,255,255,.86) !important;
        font-weight: 800 !important;
        padding: 8px 14px !important;
        box-shadow: none !important;
    }
    .gs-filter-btn.active,
    .gs-filter-btn:hover {
        background: linear-gradient(135deg, rgba(79,124,255,.40), rgba(155,92,255,.28)) !important;
        border-color: rgba(79,124,255,.50) !important;
        color: #fff !important;
    }

    .gs-kpi-card {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 14px 14px;
        border-radius: var(--gs-radius);
        background: var(--gs-card);
        border: 1px solid var(--gs-stroke);
        box-shadow: 0 12px 30px rgba(0, 0, 0, .25);
        backdrop-filter: blur(10px);
        transition: transform .18s ease, border-color .18s ease;
    }

    .gs-kpi-card:hover {
        transform: translateY(-2px);
        border-color: rgba(255, 255, 255, .18);
    }

    .gs-kpi-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, .14);
        background: rgba(255, 255, 255, .08);
    }

    .gs-kpi-icon i {
        font-size: 18px;
        color: rgba(255, 255, 255, .92);
    }

    .gs-kpi-label {
        font-size: 12px;
        color: rgba(255, 255, 255, .72);
        font-weight: 700;
        letter-spacing: .2px;
        text-transform: uppercase;
    }

    .gs-kpi-value {
        font-size: 22px;
        font-weight: 900;
        color: rgba(255, 255, 255, .96);
        line-height: 1.1;
        margin-top: 2px;
    }

    .gs-kpi-hint {
        font-size: 12px;
        color: rgba(255, 255, 255, .60);
        margin-top: 2px;
    }

    .gs-blue {
        background: linear-gradient(135deg, rgba(0, 187, 255, .18), rgba(255, 255, 255, .06));
    }

    .gs-red {
        background: linear-gradient(135deg, rgba(255, 77, 110, .18), rgba(255, 255, 255, .06));
    }

    .gs-green {
        background: linear-gradient(135deg, rgba(45, 206, 137, .18), rgba(255, 255, 255, .06));
    }

    .gs-purple {
        background: linear-gradient(135deg, rgba(120, 88, 255, .22), rgba(255, 255, 255, .06));
    }

    /* Cards mapas */
    .gs-card {
        background: rgba(255, 255, 255, .05);
        border: 1px solid rgba(255, 255, 255, .10);
        border-radius: 18px;
        box-shadow: var(--gs-shadow);
        overflow: hidden;
    }

    .gs-card-header {
        background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .02));
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .gs-card-badge {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .14);
    }

    .gs-card-badge i {
        color: rgba(255, 255, 255, .92);
        font-size: 16px;
    }

    .gs-map-wrap {
        padding: 12px;
    }

    .gs-map-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .gs-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, .10);
        background: rgba(0, 0, 0, .14);
        color: rgba(255, 255, 255, .80);
        font-size: 12px;
        backdrop-filter: blur(10px);
    }

    .gs-map {
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, .10);
        background:
            radial-gradient(600px 300px at 50% 0%, rgba(255, 255, 255, .08), transparent 60%),
            rgba(0, 0, 0, .16);
        overflow: hidden;
        padding: 10px;
    }

    .gs-map svg {
        width: 100%;
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
        filter: drop-shadow(0 16px 26px rgba(0, 0, 0, .30));
    }

    /* Hover pro para municipios */
    .gs-map .municipios {
        cursor: pointer;
        transition: transform .14s ease, filter .14s ease, stroke-width .14s ease;
        transform-origin: center;
    }

    .gs-map .municipios:hover {
        filter: brightness(1.08) saturate(1.05);
        stroke-width: 0.6px !important;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .gs-kpis {
            grid-template-columns: 1fr;
        }
    }

    /* Compat: tu CSS anterior */
    .content-map {
        background-color: transparent !important;
        padding: 0 !important;
    }

    #mapa {
        background-color: transparent;
        background-repeat: no-repeat;
        background-position: center;
        width: 100%;
        height: auto;
        margin: 0 auto;
        text-align: center;
        padding: 0.1px 0;
    }

    #mapa svg {
        max-width: 950px;
        width: 100%;
    }

    #mapa svg path {
        fill: #fff;
        transition: all .4s;
    }

    #mapa svg path:hover {
        fill: #636363
    }

    #mapa img {
        position: absolute;
    }
</style>