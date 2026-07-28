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

include './admin/classes/VisitasgAspas.php';
include './admin/classes/Visitasbuc.php';
include './admin/classes/Departamento.php';
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

// Validar los parámetros "mun" y "dep"
if (isset($_REQUEST['mun']) && !empty(trim($_REQUEST['mun']))) {

    $municipio = $_REQUEST["mun"];
    $arrVisitas = VisitasgAspas::getAll(["tbl_municipio_id" => $municipio]);
    $visitas = $arrVisitas["output"]["response"];

    $arrVisitasbuc = Visitasbuc::getAll(["tbl_municipio_id" => $municipio]);
    $visitasbuc = $arrVisitasbuc["output"]["response"];

    $arrCompromisos = Compromisos::getAll(["tbl_municipio_id" => $municipio]);
    $compromisos = $arrCompromisos["output"]["response"];

    $arrProyectos = Proyectos::getInversiontotal(["tbl_municipio_id" => $municipio]);
    $proyectos = $arrProyectos["output"]["response"];

    // Información de Departamentos
    $arrDep = Departamento::getAll(null);
    $isvalid = $arrDep['output']['valid'];
    $arrDep = $arrDep['output']['response'];
    $optionDep = Util::getDepartamentoPrincipal();
    foreach ($arrDep as $val) {
        $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
    }

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
    $arrSecre      = $datosSecre['output']['response'];

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
        <div class="pcoded-content">

            <!-- HERO -->
        <div class="gs2-hero">
  <div class="gs2-hero__bg"></div>
  <div class="gs2-hero__content">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="gs2-kicker">
          <span class="gs2-dot"></span>
          <span>Detalle por municipio</span>
        </div>

         <h2 class="gs2-title mb-1">Estado Municipios</h2>
        <div class="gs2-subtitle">Explora actividades, visitas y detalle por municipio con una vista más clara y premium.</div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <?php include './admin/include/btn_back.php'; ?>
      </div>
    </div>

    <div class="gs2-breadcrumb mt-3">
      <a href="index.html"><i class="feather icon-home"></i></a>
      <span class="sep">/</span>
      <span>Estado Municipios</span>
    </div>
  </div>
</div>

            <!-- CONTENT -->
            <div id="divInformacionGeneral" class="row">
                <div class="col-sm-12">
                    <div class="card gs2-card">
                        <div class="card-header gs2-card-header">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="gs2-badge"><i class="feather icon-sliders"></i></div>
                                    <div>
                                        <h5 class="mb-0 colorb">Filtros</h5>
                                        <small class="text-muted colorb">Departamento y municipio</small>
                                    </div>
                                </div>

                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="feather icon-more-horizontal"></i>
                                        </button>
                                        <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                            <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> maximize</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                            <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                            <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                                            <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> remove</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form id="formusuarios" role="form" autocomplete="false">
                                <input type="hidden" name="op" id="op" />
                                <input type="hidden" name="id" id="id" />

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group gs2-form-group">
                                            <label class="gs2-label">Departamento</label>
                                            <select class="form-control gs2-select gs2-select-sm" style="width: 100%;" disabled
                                                id="tbl_departamento_id" name="tbl_departamento_id"><?php echo $optionDep; ?></select>
                                            <small class="gs2-help">Se fija automáticamente según configuración.</small>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group gs2-form-group">
                                            <label class="gs2-label">Municipio</label>
                                            <select onchange="ESTADO_MUN_GESTORA.updateUrlMunicipio(this)" class="form-control gs2-select gs2-select-sm" style="width: 100%;"
                                                id="tbl_municipio_id" name="tbl_municipio_id"></select>
                                            <small class="gs2-help">Selecciona un municipio para cargar el detalle.</small>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- MAPA BUCARAMANGA -->
                            <div class="section-block mt-2">
                                <?php if (isset($_REQUEST["mun"]) && $_REQUEST["mun"] == '68001'): ?>
                                    <h3 class="section-title gs2-section-title">Mapa Bucaramanga</h3>
                                <?php endif; ?>
                            </div>

                            <?php if (isset($_REQUEST["mun"]) && $_REQUEST["mun"] == '68001'): ?>
                                <div class="gs2-map-card">
                                    <img src="assets/img/bucaramangaok.png" alt="Mapa Bucaramanga" class="gs2-map-img">
                                </div>
                            <?php endif; ?>

                            <!-- ACTIVIDADES -->
                            <div class="section-block mt-4 colorb">
                                <h3 class="section-title text-center gs2-section-title colorb" style="font-size: 16px">
                                    Actividades Gestión Social
                                </h3>
                            </div>

                            <div class="table-responsive gs2-table-wrap">
                                <table class="table table-bordered table-hover m-0 gs2-table">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width:120px;">Ver</th>
                                            <th>Fecha</th>
                                            <th>Provincia</th>
                                            <th>Población Impactada</th>
                                            <th>Motivo Actividad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($isvalid) : ?>
                                            <?php foreach ($visitas as $item) : ?>
                                                <tr>
                                                    <td>
                                                        <form action="reporte_visitag_aspas.php" method="POST" target="_blank" style="display:inline;">
                                                            <input type="hidden" id="reporte" name="reporte" value="<?= htmlspecialchars($item['id']); ?>">
                                                            <button type="submit" class="btn btn-sm btn-primary gs2-btn-sm" title="Ver">
                                                                <i class="feather icon-eye"></i>
                                                                <span class="d-none d-md-inline">Ver</span>
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td><span class="gs2-chip"><?= htmlspecialchars($item["date"]); ?></span></td>
                                                    <td><?= htmlspecialchars($item["provincia"]); ?></td>
                                                    <td><span class="gs2-chip gs2-chip-green"><?= htmlspecialchars($item["poblacion"]); ?></span></td>
                                                    <td class="gs2-td-wrap"><?= htmlspecialchars($item["desc_actividad"]); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div><!-- /card-body -->
                    </div><!-- /card -->
                </div><!-- /col -->
            </div><!-- /row -->

        </div><!-- /content -->
    </div><!-- /main-container -->

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

    <!-- // Variables para mostrrar en los graficos -->
    <script>
        const TOTAL_EJECUTADO = <?= $totalEjecutado ?>;
        const TOTAL_POR_EJECUTAR = <?= 100 - $totalEjecutado ?>;
        const LABELS_SECRETARIA = <?php echo json_encode($arrSecre["labels"]) ?>;
        const DATA_SECRETARIA = <?php echo json_encode($arrSecre["data"]) ?>;

        // Agregamos la información al select
        const params = UTIL.getParamsFromUrlDepartamentoMunicipio();
        selectedMunicipio = params.mun;
        DEPARTAMENTO.getMunicipiosByDepartamentoIdV2SeteraCodigoMunicipio(UTIL.getDepartamentoPrincipal(), params.mun);

        $(function() {
            "use strict";
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
                labelColor: '#5969ff',
                colors: ['#5969ff', '#ff407b'],
                formatter: function(x) {
                    return x + "%"
                }
            });

            var ctx = document.getElementById("chartjs_bar_horizontal").getContext('2d');

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
                            position: 'top'
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
                                callback: function(value) {
                                    return '$ ' + value.toLocaleString();
                                }
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: 12,
                                    family: 'Arial'
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

</body>

</html>

<style>
    /* =========================================================
   ✅ ESTILO EXACTO COMO TU IMAGEN (Acción Unificada)
   Hero azul + gradiente hacia morado + glass + contraste
========================================================= */

:root{
  --au-bg0:#0b1220;
  --au-bg1:#0e1830;

  --au-hero-a:#1e3a57;     /* azul header vibe */
  --au-hero-b:#1f3f5d;     /* azul profundo */
  --au-hero-c:#2f3f6e;     /* morado/azul derecha */
  --au-hero-d:#17283f;     /* sombra interna */

  --au-card: rgba(255,255,255,.06);
  --au-card2: rgba(255,255,255,.04);
  --au-border: rgba(255,255,255,.10);

  --au-text: rgba(255,255,255,.92);
  --au-muted: rgba(255,255,255,.68);

  --au-shadow: 0 18px 55px rgba(0,0,0,.38);
  --au-radius: 18px;
}

/* Fondo general como tu captura */
.pcoded-main-container{
  background:
    radial-gradient(900px 600px at 20% 10%, rgba(120,88,255,.18), transparent 55%),
    radial-gradient(900px 600px at 85% 18%, rgba(0,187,255,.14), transparent 55%),
    linear-gradient(180deg, var(--au-bg0), var(--au-bg1));
  min-height:100vh;
}

/* ================= HERO (como tu imagen) ================= */
.gs2-hero{
  position: relative;
  border-radius: 22px;
  overflow: hidden;
  margin: 12px 0 18px;
  box-shadow: var(--au-shadow);
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.05);
}

.gs2-hero__bg{
  position:absolute;
  inset:0;
  background:
    radial-gradient(900px 520px at 18% 18%, rgba(0,187,255,.16), transparent 62%),
    radial-gradient(900px 520px at 82% 18%, rgba(120,88,255,.18), transparent 62%),
    linear-gradient(135deg, rgba(32,62,92,.92), rgba(47,63,110,.82));
  filter: saturate(1.1) contrast(1.05);
}

.gs2-hero__content{
  position:relative;
  padding: 18px 18px 16px;
  color: var(--au-text);
}

.gs2-kicker{
  display:inline-flex;
  align-items:center;
  gap:8px;
  font-weight:700;
  font-size:12px;
  letter-spacing:.3px;
  text-transform:uppercase;
  color: rgba(255,255,255,.70);
  margin-bottom: 6px;
}

.gs2-dot{
  width: 8px; height: 8px; border-radius: 999px;
  background: linear-gradient(135deg, #22c1ff, #7b61ff);
  box-shadow: 0 0 0 4px rgba(255,255,255,.08);
}

/* ✅ Título como tu screenshot: gris claro sólido (no gradient) */
.gs2-title{
  margin:0;
  font-weight: 900;
  letter-spacing: .2px;
  color: rgba(226,232,240,.95); /* gris claro */
  text-shadow: 0 10px 26px rgba(0,0,0,.35);
}

.gs2-subtitle{
  color: rgba(255,255,255,.72);
  font-size: 13px;
  margin-top: 2px;
}

/* Breadcrumb estilo captura */
.gs2-breadcrumb{
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap: 10px;
  font-size: 12px;
  color: rgba(255,255,255,.70);
}
.gs2-breadcrumb a{
  color: rgba(255,255,255,.85);
  text-decoration:none;
}
.gs2-breadcrumb .sep{ opacity:.55; }

/* ================= CARD (filtros) ================= */
.gs2-card{
  background: var(--au-card);
  border: 1px solid var(--au-border);
  border-radius: var(--au-radius);
  box-shadow: var(--au-shadow);
  overflow:hidden;
}

.gs2-card-header{
  background:
    radial-gradient(700px 240px at 10% 30%, rgba(0,187,255,.10), transparent 60%),
    linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
  border-bottom: 1px solid rgba(255,255,255,.08);
}

/* Badge en header */
.gs2-badge{
  width: 36px; height: 36px;
  border-radius: 12px;
  display:grid; place-items:center;
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.08);
}
.gs2-badge i{ color: rgba(255,255,255,.92); }

/* Labels con contraste */
.gs2-label{
  font-weight: 800;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .25px;
  color: rgba(255,255,255,.72);
  margin-bottom: 6px;
}
.gs2-help{
  display:block;
  font-size: 12px;
  color: rgba(255,255,255,.62);
  margin-top: 6px;
}

/* ================= SELECTS MÁS PEQUEÑOS + PRO ================= */
.gs2-select,
.gs2-select-sm{
  width: 100%;
  background: rgba(10,16,30,.45) !important;
  border: 1px solid rgba(255,255,255,.14) !important;
  color: rgba(255,255,255,.92) !important;

  border-radius: 14px !important;
  height: 40px !important;             /* ✅ compacto */
  padding: 6px 12px !important;        /* ✅ compacto */
  font-size: 13px !important;

  box-shadow: 0 12px 26px rgba(0,0,0,.18);
  transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
}

.gs2-select:focus,
.gs2-select-sm:focus{
  outline:none !important;
  border-color: rgba(0,187,255,.55) !important;
  box-shadow: 0 0 0 4px rgba(0,187,255,.12), 0 14px 26px rgba(0,0,0,.20);
  transform: translateY(-1px);
}

/* En muchos navegadores, el dropdown nativo sale sobre blanco:
   esto asegura que las opciones se lean bien */
.gs2-select option,
.gs2-select-sm option{
  color:#111 !important;
}

/* ================= TABLE (igual pro) ================= */
.gs2-table-wrap{
  border-radius: 16px;
  border: 1px solid rgba(255,255,255,.10);
  overflow: hidden;
  box-shadow: 0 14px 30px rgba(0,0,0,.16);
  background: rgba(255,255,255,.04);
}

.gs2-table{
  width:100%;
  min-width: 960px;
  margin:0 !important;
  color: rgba(255,255,255,.92);
}

.gs2-table thead th{
  position: sticky;
  top: 0;
  z-index: 2;
  background: rgba(10,16,30,.55) !important; /* ✅ header tabla oscuro */
  color: rgba(255,255,255,.86) !important;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .25px;
  border-color: rgba(255,255,255,.10) !important;
  padding: 12px;
}

.gs2-table td{
  border-color: rgba(255,255,255,.10) !important;
  padding: 12px;
  vertical-align: middle;
}

.gs2-table tbody tr{
  transition: background .15s ease;
}
.gs2-table tbody tr:hover{
  background: rgba(0,187,255,.07);
}

.gs2-td-wrap{
  white-space: normal !important;
  word-wrap: break-word;
  max-width: 520px;
}

/* Chips */
.gs2-chip{
  display:inline-flex;
  align-items:center;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(255,255,255,.06);
  color: rgba(255,255,255,.88);
  font-size: 12px;
  font-weight: 800;
}

.gs2-chip-green{
  background: rgba(45,206,137,.14);
  border-color: rgba(45,206,137,.22);
}

/* Botón mini pro */
.gs2-btn-sm{
  border-radius: 12px !important;
  padding: 7px 10px !important;
  font-weight: 900 !important;
  display:inline-flex;
  gap: 8px;
  align-items:center;
}
/* =========================================================
   ✅ FIX TABLA – TEXTO NEGRO + CENTRADA + LIMPIA
========================================================= */

/* Contenedor centrado */
.gs2-table-wrap{
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

/* Tabla centrada */
.gs2-table{
    width: 95% !important;
    max-width: 1200px;
    margin: 0 auto !important;
    background: #ffffff !important;   /* fondo blanco limpio */
    color: #111827 !important;        /* TEXTO NEGRO REAL */
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 18px 45px rgba(0,0,0,.25);
}

/* Header oscuro estilo corporativo */
.gs2-table thead th{
    background: #2f4e6f !important;   /* azul header estilo Acción Unificada */
    color: #ffffff !important;        /* texto blanco */
    font-weight: 700;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: .4px;
    border: none !important;
    padding: 14px 12px;
    text-align: center;
}

/* Celdas */
.gs2-table td{
    color: #111827 !important;   /* negro */
    font-size: 14px;
    border-color: #e5e7eb !important;
    padding: 16px 14px;
    vertical-align: middle;
    background: #ffffff !important;
}

/* Filas alternas suaves */
.gs2-table tbody tr:nth-child(even){
    background: #f9fafb !important;
}

/* Hover elegante */
.gs2-table tbody tr:hover{
    background: #eef4ff !important;
    transition: background .2s ease;
}

/* Columna motivo actividad con buen ancho */
.gs2-td-wrap{
    white-space: normal !important;
    word-wrap: break-word;
    max-width: 500px;
}

/* Chips con buen contraste */
.gs2-chip{
    background: #e6f0ff !important;
    border: 1px solid #c7dbff !important;
    color: #1e3a8a !important;
    font-weight: 700;
}

/* Botón Ver más limpio */
.gs2-btn-sm{
    background: #0c2957 !important;
    border: none !important;
    color: #ffffff !important;
    font-weight: 700;
    padding: 8px 14px !important;
    border-radius: 8px !important;
}

.gs2-btn-sm:hover{
    background: #2563eb !important;
}

/* Responsive */
@media (max-width: 992px){
    .gs2-table{
        width: 100% !important;
    }
}
.colorb:{
   color: #ffffff !important;
}
</style>
