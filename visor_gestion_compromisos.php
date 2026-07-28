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

include './admin/classes/Visitas.php';

$userType = SessionData::getUserType();
$secretariaId = SessionData::getSecretaria();
if ($secretariaId == 0) {
    $secretariaId = Util::getSecretariaPrincipal();
}

$isSecretarioRestringido = (
    $userType === Util::Secretario_Despacho() ||
    $userType === Util::Secretaria_Despacho_Gobernacion() ||
    $userType === Util::Auxiliar() ||
    $userType == Util::Auxiliar_secret_gob()
);

$secretariaIdToFilter = null;
if ($isSecretarioRestringido) {
    $secretariaIdToFilter = $secretariaId;
}

$data = Visitas::getVisorGestionDeCompromiso($secretariaIdToFilter);
$isvalid = $data['output']['valid'];
$response = $data['output']['response'] ?? [];

// Ordenar la calificación de mayor a menor
usort($response, function ($a, $b) {
    $valA = isset($a['calificacion_porcentaje']) ? floatval(str_replace('%', '', $a['calificacion_porcentaje'])) : 0;
    $valB = isset($b['calificacion_porcentaje']) ? floatval(str_replace('%', '', $b['calificacion_porcentaje'])) : 0;
    return $valB <=> $valA;
});

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

?>

<body class="gob360-compliance-viewer">
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

    <link href="assets/css/visor_gestion_cumplimiento_gob360_final.css" rel="stylesheet">

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">

                            <!-- [ breadcrumb ] start -->
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Visor De Gestión De Cumplimiento</h5>
                                                <?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Mapa visitas</a></li>
                                                <li class="breadcrumb-item"><a href="#!">Visor De Gestión De Cumplimiento</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ breadcrumb ] end -->

                            <!-- [ Main Content ] start -->

                            <!-- HERO VISUAL GOB360 -->
                            <section class="g360-viewer-hero" aria-label="Visor de gestión de cumplimiento GOB360">
                                <div class="g360-viewer-hero__grid">

                                    <div>
                                        <img
                                            src="assets/img/gob360l.png"
                                            alt="Logo GOB360"
                                            class="g360-viewer-hero__logo"
                                        >
                                    </div>

                                    <div>
                                        <div class="g360-viewer-hero__eyebrow">
                                            <i class="feather icon-award"></i>
                                            Evaluación institucional
                                        </div>

                                        <h1 class="g360-viewer-hero__title">
                                            Visor de gestión de cumplimiento
                                        </h1>

                                        <p class="g360-viewer-hero__description">
                                            Compara el desempeño de las entidades según sus compromisos
                                            en trámite, cumplidos, sin cumplir y en espera, conservando
                                            el orden dinámico definido por la calificación.
                                        </p>

                                        <div class="g360-viewer-hero__chips">
                                            <span class="g360-chip g360-chip--success">
                                                <i class="feather icon-check-circle"></i>
                                                Datos consolidados
                                            </span>

                                            <span class="g360-chip">
                                                <i class="feather icon-bar-chart-2"></i>
                                                Orden por calificación
                                            </span>

                                            <span class="g360-chip">
                                                <i class="feather icon-shield"></i>
                                                Acceso según perfil
                                            </span>
                                        </div>
                                    </div>

                                    <div class="g360-viewer-hero__visual" aria-hidden="true">
                                        <div class="g360-mini-card">
                                            <i class="feather icon-briefcase"></i>
                                            <span>Entidades</span>
                                        </div>

                                        <div class="g360-mini-card">
                                            <i class="feather icon-check-square"></i>
                                            <span>Cumplidos</span>
                                        </div>

                                        <div class="g360-mini-card">
                                            <i class="feather icon-clock"></i>
                                            <span>En trámite</span>
                                        </div>

                                        <div class="g360-mini-card">
                                            <i class="feather icon-percent"></i>
                                            <span>Calificación</span>
                                        </div>
                                    </div>

                                </div>
                            </section>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card saas-card g360-viewer-card">
                                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                                            <div>
                                                <h5>Comparativo institucional de cumplimiento</h5>
                                                <p>Resultados consolidados y ordenados por calificación.</p>
                                            </div>

                                            <div class="card-header-right ml-auto">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="feather icon-more-horizontal"></i>
                                                    </button>
                                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                                        <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span></a></li>
                                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> Expandir</span></a></li>
                                                        <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                                                        <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Eliminar</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body">

                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0 tabla-compacta">
                                                    <thead class="bg-light text-dark">
                                                        <tr class="border-1">
                                                            <th class="td-center">No.</th>
                                                            <th>ENTIDAD</th>
                                                            <th class="td-center">TOTAL</th>
                                                            <th class="td-center th-tramite">EN TRÁMITE</th>
                                                            <th class="td-center th-cumplido">CUMPLIDO</th>
                                                            <th class="td-center th-sincumplir">SIN CUMPLIR</th>
                                                            <th class="td-center th-espera">EN ESPERA</th>
                                                            <th class="td-center">CALIFICACIÓN</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (is_array($response) && !empty($response)): ?>
                                                            <?php
                                                              $rowNumber = 1;
                                                              $sumTotal = 0; $sumTramite = 0; $sumCumplido = 0; $sumSinCumplir = 0; $sumEspera = 0;
                                                            ?>
                                                            <?php foreach ($response as $item): ?>
                                                                <?php
                                                                  $colorCal = $item['color_calificacion'] ?? '#e5e7eb';
                                                                  $calTxt   = $item['calificacion_porcentaje'] ?? '';
                                                                  $sumTotal    += intval($item['total_compromisos'] ?? 0);
                                                                  $sumTramite  += intval($item['en_tramite'] ?? 0);
                                                                  $sumCumplido += intval($item['cumplido'] ?? 0);
                                                                  $sumSinCumplir += intval($item['sin_cumplir'] ?? 0);
                                                                  $sumEspera   += intval($item['en_espera'] ?? 0);
                                                                ?>
                                                                <tr>
                                                                    <td class="td-center"><?php echo $rowNumber++; ?></td>

                                                                    <td>
                                                                        <strong><?php echo h($item['entidad'] ?? ''); ?></strong>
                                                                    </td>

                                                                    <td class="td-center"><?php echo h($item['total_compromisos'] ?? '0'); ?></td>

                                                                    <td class="td-center">
                                                                        <span class="chip"><span class="dot warn"></span><?php echo h($item['en_tramite'] ?? '0'); ?></span>
                                                                    </td>

                                                                    <td class="td-center">
                                                                        <span class="chip"><span class="dot ok"></span><?php echo h($item['cumplido'] ?? '0'); ?></span>
                                                                    </td>

                                                                    <td class="td-center">
                                                                        <span class="chip"><span class="dot bad"></span><?php echo h($item['sin_cumplir'] ?? '0'); ?></span>
                                                                    </td>

                                                                    <td class="td-center">
                                                                        <span class="chip"><span class="dot wait"></span><?php echo h($item['en_espera'] ?? '0'); ?></span>
                                                                    </td>

                                                                    <td class="td-center">
                                                                        <span class="score-pill" style="background-color: <?php echo h($colorCal); ?>;">
                                                                            <?php echo h($calTxt); ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <!-- Total row -->
                                                            <tr style="border-top:2px solid #20427F; background:rgba(32,66,127,.06); font-weight:700;">
                                                                <td class="td-center" colspan="2">TOTAL</td>
                                                                <td class="td-center"><?php echo $sumTotal; ?></td>
                                                                <td class="td-center"><span class="chip"><span class="dot warn"></span><?php echo $sumTramite; ?></span></td>
                                                                <td class="td-center"><span class="chip"><span class="dot ok"></span><?php echo $sumCumplido; ?></span></td>
                                                                <td class="td-center"><span class="chip"><span class="dot bad"></span><?php echo $sumSinCumplir; ?></span></td>
                                                                <td class="td-center"><span class="chip"><span class="dot wait"></span><?php echo $sumEspera; ?></span></td>
                                                                <td></td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <tr>
                                                                <td colspan="8" class="td-center">No hay datos de compromisos disponibles.</td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div><!-- card-body -->
                                    </div><!-- card -->
                                </div>
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'admin/include/footer.php'; ?>
    <?php include 'admin/include/gerenic_script.php'; ?>

    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- prism Js -->
    <script src="assets/js/plugins/prism.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>

    <?php include './admin/include/generic_dataTables.php'; ?>

</body>
</html>
