<!-- ESTE ES EL MAPA DE LOS MUNICIPIOS DENTRO DEL MAPA PARA LA GESTORA SOCIAL SE DEBE MODIFICAR SEGUN LO REQUIERAN LOS DATOS DE GESTORA SOCIAL-->
<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

//Validación
/* if (!$create) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Visitasg.php';
include './admin/classes/Visitasbuc.php';
include './admin/classes/Proyectos.php';
include './admin/classes/Compromisos.php';

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
    return str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
};

// Tipo de red: primera_dama | aspas | ambos (default ambos si no viene)
$tipoRaw = isset($_REQUEST['tipo']) ? strtolower(trim((string) $_REQUEST['tipo'])) : 'ambos';
$tiposValidos = ['primera_dama', 'aspas', 'ambos'];
$tipoFiltro = in_array($tipoRaw, $tiposValidos, true) ? $tipoRaw : 'ambos';

$labelsTipo = [
    'primera_dama' => 'Red de Valor Social 1',
    'aspas' => 'Red de Valor Social 2',
    'ambos' => 'Red de Valor Social 1 y 2',
];
$labelTipoActual = $labelsTipo[$tipoFiltro];

// Validar los parámetros "mun" y "dep"
if (isset($_REQUEST['mun']) && !empty(trim($_REQUEST['mun']))) {

    $municipio = $_REQUEST["mun"];

    $paramsVisitas = ["tbl_municipio_id" => $municipio];
    if ($tipoFiltro === 'primera_dama' || $tipoFiltro === 'aspas') {
        $paramsVisitas['tipo_actividad'] = $tipoFiltro;
    }
    $arrVisitas = Visitasg::getAll($paramsVisitas);
    $isvalidVisitas = !empty($arrVisitas['output']['valid']);
    $visitas = $isvalidVisitas ? $arrVisitas["output"]["response"] : [];

    $arrVisitasbuc = Visitasbuc::getAll(["tbl_municipio_id" => $municipio]);
    $visitasbuc = $arrVisitasbuc["output"]["response"];

    $arrCompromisos = Compromisos::getAll(["tbl_municipio_id" => $municipio]);
    $compromisos = $arrCompromisos["output"]["response"];

    $arrProyectos = Proyectos::getInversiontotal(["tbl_municipio_id" => $municipio]);
    $proyectos = $arrProyectos["output"]["response"];

    // Departamento fijo: Santander (68)
    $codigoDepartamento = Util::getDepartamentoPrincipal();

    // Información de compromisos
    $arrCom = Compromisos::getAll($_REQUEST);
    $isvalid = $arrCom['output']['valid'];
    $compromiso = $arrCom['output']['response'];

    // Información de secretarias
    $arr = Proyectos::getAllproyectosxsecre($_REQUEST);
    $isvalid = $arr['output']['valid'];
    $arr = $arr['output']['response'];
    $arrData = $arr;

    $totalEjecutado = 0;
    if (!empty($arrData)) {
        foreach ($arrData as $key => $value) {
            $totalEjecutado += is_null($value["porcentaje_ejecucion"]) ? 0 : doubleval($value["porcentaje_ejecucion"]);
        }
        $totalEjecutado = $totalEjecutado == 0 ? 0 : round($totalEjecutado / count($arrData), 2);
    }

    $datosSecre = Proyectos::getInversionBySecre($_REQUEST);
    $isvalidSecre = $datosSecre['output']['valid'];
    $arrSecre = $datosSecre['output']['response'];

    // =======================PROYECTOS========================
    $arrTotal = Proyectos::getInversiontotal($_REQUEST);
    $isvalid = $arrTotal['output']['valid'];
    $arrTotal = $arrTotal['output']['response'];
    $arrTotalData = $arrTotal;

    $total_invertido = 0;
    if (!empty($arrTotalData)) {
        foreach ($arrTotalData as $key => $value) {
            $total_invertido += is_null($value["SumaDevalor_proyecto"]) ? 0 : doubleval($value["SumaDevalor_proyecto"]);
        }
        $total_invertido = $total_invertido == 0 ? 0 : round($total_invertido / count($arrTotalData), 2);
    }
} else {
?>
    <script type='text/javascript'>
        alert('Información enviada no es correcta');
        window.location = 'gestora_social.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>';
    </script>
<?php
}
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <!-- =========================
         ✅ ESTILO BRUTAL DARK SAAS
         ========================= -->
    <style>
        :root {
            --au-bg0: #0b1220;
            --au-bg1: #0e1830;
            --au-hero-a: rgba(32, 62, 92, .92);
            --au-hero-b: rgba(47, 63, 110, .82);
            --au-card: rgba(255, 255, 255, .06);
            --au-border: rgba(255, 255, 255, .10);
            --au-text: rgba(255, 255, 255, .92);
            --au-muted: rgba(255, 255, 255, .70);
            --shadow: 0 18px 55px rgba(0, 0, 0, .38);
            --shadow2: 0 12px 30px rgba(0, 0, 0, .22);
            --r: 18px;
        }

        .pcoded-main-container {
            background:
                radial-gradient(900px 600px at 20% 10%, rgba(120, 88, 255, .18), transparent 55%),
                radial-gradient(900px 600px at 85% 18%, rgba(0, 187, 255, .14), transparent 55%),
                linear-gradient(180deg, var(--au-bg0), var(--au-bg1));
            min-height: 100vh;
        }

        /* HERO */
        .au-hero {
            position: relative;
            border-radius: 22px;
            overflow: hidden;
            margin: 12px 0 18px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, .08);
            background: rgba(255, 255, 255, .05);
        }

        .au-hero__bg {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(900px 520px at 18% 18%, rgba(0, 187, 255, .16), transparent 62%),
                radial-gradient(900px 520px at 82% 18%, rgba(120, 88, 255, .18), transparent 62%),
                linear-gradient(135deg, var(--au-hero-a), var(--au-hero-b));
            filter: saturate(1.1) contrast(1.05);
        }

        .au-hero__content {
            position: relative;
            padding: 18px 18px 16px;
            color: var(--au-text);
        }

        .au-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 900;
            font-size: 12px;
            letter-spacing: .3px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .72);
            margin-bottom: 6px;
        }

        .au-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(135deg, #22c1ff, #7b61ff);
            box-shadow: 0 0 0 4px rgba(255, 255, 255, .08);
        }

        .au-title {
            margin: 0;
            font-weight: 1000;
            letter-spacing: .2px;
            color: rgba(226, 232, 240, .95);
            text-shadow: 0 10px 26px rgba(0, 0, 0, .35);
        }

        .au-subtitle {
            color: rgba(255, 255, 255, .72);
            font-size: 13px;
            margin-top: 2px;
        }

        /* Card glass */
        .card {
            border-radius: var(--r);
            border: 1px solid var(--au-border);
            background: var(--au-card);
            box-shadow: var(--shadow2);
            overflow: hidden;
        }

        .card-header {
            background:
                radial-gradient(700px 240px at 10% 30%, rgba(0, 187, 255, .10), transparent 60%),
                linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .02));
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            font-weight: 1000;
            color: rgba(255, 255, 255, .92);
        }

        .card-header h5 {
            color: rgba(255, 255, 255, .95) !important;
            margin: 0;
        }

        /* Selects compactos PRO */
        label.bmd-label-floating {
            color: rgba(255, 255, 255, .78) !important;
            font-weight: 800;
            letter-spacing: .2px;
            font-size: 12px;
            text-transform: uppercase;
        }

        select.form-control {
            height: 42px !important;
            padding: 8px 12px !important;
            border-radius: 12px !important;
            border: 1px solid rgba(255, 255, 255, .12) !important;
            background: rgba(0, 0, 0, .18) !important;
            color: rgba(255, 255, 255, .92) !important;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .04);
        }

        select.form-control:focus {
            outline: none !important;
            box-shadow: 0 0 0 4px rgba(0, 187, 255, .12) !important;
            border-color: rgba(0, 187, 255, .35) !important;
        }

        /* Section title */
        .section-title {
            color: rgba(255, 255, 255, .92);
            font-weight: 1000;
            letter-spacing: .3px;
        }

        /* Tabla centrada + texto negro (porque fondo es blanco) */
        .gs2-table-wrap {
            display: flex;
            justify-content: center;
            padding: 12px 0 6px;
        }

        .gs2-table {
            width: 95% !important;
            max-width: 1200px;
            background: #ffffff !important;
            color: #111827 !important;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .25);
            margin: 0 auto !important;
        }

        .gs2-table thead th {
            background: #2f4e6f !important;
            color: #ffffff !important;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .35px;
            border: none !important;
            padding: 14px 12px;
            text-align: center;
        }

        .gs2-table td {
            color: #111827 !important;
            font-size: 14px;
            border-color: #e5e7eb !important;
            padding: 16px 14px !important;
            vertical-align: middle;
            background: #ffffff !important;
        }

        .gs2-table tbody tr:nth-child(even) td {
            background: #f9fafb !important;
        }

        .gs2-table tbody tr:hover td {
            background: #eef4ff !important;
        }

        /* Motivo actividad con buen ancho */
        .gs2-td-wrap {
            white-space: normal !important;
            word-wrap: break-word;
            max-width: 520px;
        }

        /* Botón Ver PRO */
        .gs2-btn {
            background: #3b82f6 !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 900;
            padding: 8px 14px !important;
            border-radius: 10px !important;
        }

        .gs2-btn:hover {
            background: #2563eb !important;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .gs2-table {
                width: 100% !important;
            }
        }

        /* ✅ Chart.js en dark (texto visible) */
        canvas {
            max-width: 100% !important;
        }
    </style>

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!-- HERO -->
            <div class="au-hero">
                <div class="au-hero__bg"></div>
                <div class="au-hero__content">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="au-kicker"><span class="au-dot"></span><span>PANEL EJECUTIVO • <?php echo strtoupper(htmlspecialchars($labelTipoActual, ENT_QUOTES, 'UTF-8')); ?></span></div>
                            <h2 class="au-title mb-1">Estado Municipios</h2>
                            <div class="au-subtitle">Filtra por municipio y tipo de red; revisa actividades con acceso al reporte detallado.</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php include './admin/include/btn_back.php'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- [ Main Content ] start -->
            <div id="divInformacionGeneral" class="row">
                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Municipio y tipo de red</h5>
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

                        <div class="card-body">
                            <div class="col-sm-12">
                                <form id="formusuarios" role="form" autocomplete="false">
                                    <input type="hidden" name="op" id="op" />
                                    <input type="hidden" name="id" id="id" />

                                    <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="<?php echo htmlspecialchars($codigoDepartamento, ENT_QUOTES, 'UTF-8'); ?>">

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="bmd-label-floating">Municipio</label>
                                            <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id"
                                                onchange="ESTADO_MUN_GESTORA.updateUrlMunicipio(this)">
                                            </select>
                                            <small style="color: rgba(255,255,255,.65);">Selecciona un municipio para cargar el detalle.</small>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label class="bmd-label-floating">Tipo de red</label>
                                            <select class="form-control" id="tipo_actividad" name="tipo_actividad"
                                                onchange="ESTADO_MUN_GESTORA.updateUrlTipo(this)">
                                                <option value="primera_dama" <?php echo $tipoFiltro === 'primera_dama' ? 'selected' : ''; ?>>Red de Valor Social 1</option>
                                                <option value="aspas" <?php echo $tipoFiltro === 'aspas' ? 'selected' : ''; ?>>Red de Valor Social 2</option>
                                                <option value="ambos" <?php echo $tipoFiltro === 'ambos' ? 'selected' : ''; ?>>Red de Valor Social 1 y 2</option>
                                            </select>
                                            <small style="color: rgba(255,255,255,.65);">Filtra actividades por tipo de red.</small>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Mapa Bucaramanga -->
                            <?php if (isset($_REQUEST["mun"]) && $_REQUEST["mun"] == '68001'): ?>
                                <div class="section-block mt-2">
                                    <h3 class="section-title">Mapa Bucaramanga</h3>
                                </div>
                                <div class="container">
                                    <img src="assets/img/bucaramangaok.png" alt="" class="mapa">
                                </div>
                            <?php endif; ?>

                            <!-- Tabla actividades -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="section-block">
                                        <h3 class="section-title text-center" style="font-size: 16px">Actividades — <?php echo htmlspecialchars($labelTipoActual, ENT_QUOTES, 'UTF-8'); ?></h3>
                                    </div>

                                    <div class="gs2-table-wrap">
                                        <div class="table-responsive" style="width:100%;">
                                            <table class="table table-bordered table-hover gs2-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width:130px;">Ver Detallado</th>
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">Provincia</th>
                                                        <th scope="col">Población Impactada</th>
                                                        <th scope="col">Tipo</th>
                                                        <th scope="col">Motivo Actividad</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($isvalidVisitas) : ?>
                                                        <?php foreach ($visitas as $item) : ?>
                                                            <?php
                                                            $tipoItem = $item['tipo_actividad'] ?? '';
                                                            $labelItem = $labelsTipo[$tipoItem] ?? $tipoItem;
                                                            ?>
                                                            <tr>
                                                                <td class="text-center">
                                                                    <form action="reporte_visitag.php" method="POST" target="_blank" style="display:inline;">
                                                                        <input type="hidden" id="reporte" name="reporte" value="<?= htmlspecialchars($item['id']); ?>">
                                                                        <button type="submit" class="btn btn-sm gs2-btn" title="Ver">
                                                                            <i class="feather icon-eye"></i> Ver
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                                <td class="text-center"><?php echo htmlspecialchars($item["date"]); ?></td>
                                                                <td class="text-center"><?php echo htmlspecialchars($item["provincia"]); ?></td>
                                                                <td class="text-center"><?php echo htmlspecialchars($item["poblacion"]); ?></td>
                                                                <td class="text-center"><?php echo htmlspecialchars($labelItem, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                <td class="gs2-td-wrap"><?php echo htmlspecialchars($item["desc_actividad"]); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center">No hay actividades registradas para este filtro.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div><!-- /card-body -->
                    </div>
                </div>
            </div>
            <!-- [ sample-page ] end -->
        </div>
    </div>

    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/estado_municipios_gestora.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Morris.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>

    <script>
        const TOTAL_EJECUTADO = <?= $totalEjecutado ?>;
        const TOTAL_POR_EJECUTAR = <?= 100 - $totalEjecutado ?>;
        const LABELS_SECRETARIA = <?php echo json_encode($arrSecre["labels"]) ?>;
        const DATA_SECRETARIA = <?php echo json_encode($arrSecre["data"]) ?>;

        const params = UTIL.getParamsFromUrlDepartamentoMunicipio();
        selectedMunicipio = params.mun;
        DEPARTAMENTO.getMunicipiosByDepartamentoIdV2SeteraCodigoMunicipio(UTIL.getDepartamentoPrincipal(), params.mun);

        $(function() {
            "use strict";

            // Morris donut (si lo usas en esta vista, si no existe el div no pasa nada)
            if (document.getElementById('gender_donut')) {
                Morris.Donut({
                    element: 'gender_donut',
                    data: [{
                            value: parseFloat(TOTAL_EJECUTADO),
                            label: 'Ejecutado'
                        },
                        {
                            value: parseFloat(TOTAL_POR_EJECUTAR),
                            label: 'Por Ejecutar'
                        }
                    ],
                    labelColor: 'rgba(255,255,255,.85)',
                    colors: ['#22c1ff', '#ff407b'],
                    formatter: function(x) {
                        return x + "%"
                    }
                });
            }

            // Chart.js (si existe el canvas)
            const el = document.getElementById("chartjs_bar_horizontal");
            if (el) {
                const ctx = el.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: LABELS_SECRETARIA,
                        datasets: [{
                            label: 'Total Invertido',
                            data: DATA_SECRETARIA,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { color: 'rgba(255,255,255,.85)' }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '$ ' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    color: 'rgba(255,255,255,.75)',
                                    callback: function(value) {
                                        return '$ ' + value.toLocaleString();
                                    }
                                },
                                grid: { color: 'rgba(255,255,255,.10)' }
                            },
                            y: {
                                ticks: { color: 'rgba(255,255,255,.80)' },
                                grid: { color: 'rgba(255,255,255,.06)' }
                            }
                        }
                    }
                });
            }
        });
    </script>

</body>
</html>