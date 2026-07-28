<?php
    include './admin/include/head.php';
    require './admin/include/generic_classes.php';

    // Leer parámetros GET (vienen del botón "Ver Panel" del dashboard)
    $vigencia   = isset($_GET['vigencia'])    ? preg_replace('/[^0-9]/', '', $_GET['vigencia'])         : date('Y');
    $filtroMun  = isset($_GET['municipio'])   ? htmlspecialchars(strip_tags($_GET['municipio']))         : 'Todos';
    $filtroDep  = isset($_GET['dependencia']) ? htmlspecialchars(strip_tags($_GET['dependencia']))       : 'Todas';
    $provincia  = isset($_GET['provincia'])   ? htmlspecialchars(strip_tags($_GET['provincia']))         : 'Todas';

    // Indicar al JS si debe auto-buscar al cargar (hay parámetros en la URL)
    $autoBuscar = (!empty($_GET['vigencia']) || !empty($_GET['municipio']) || !empty($_GET['dependencia']) || !empty($_GET['provincia']));

    $totalProyectos       = 0;
    $valorTotal           = 0;
    $valorEjecutado       = 0;
    $aporteGobernacion    = 0;
    $aporteMunicipal      = 0;
    $aporteNacional       = 0;
    $promedioAvanceFisico = 0;
    $promedioAvanceFinan  = 0;
    $estados              = [];
    $provincias           = [];
    $proyectos            = [];
    $resumenData          = null;
    $errorApi             = '';
?>

<style>
    :root {
        --nav-blue: #20427F;
        --nav-blue-2: #132b52;
        --nav-blue-3: #2e58a8;
        --ink: #0f172a;
        --muted: #64748b;
        --line: rgba(2,6,23,.10);
        --bg: #f7f9fc;
        --radius-xl: 18px;
        --radius-md: 12px;
        --shadow-soft: 0 10px 28px rgba(2,6,23,.10);
        --shadow-mid: 0 16px 42px rgba(2,6,23,.16);
    }

    body { background: radial-gradient(1200px 600px at 20% -10%, rgba(32,66,127,.14), transparent 55%),
                      radial-gradient(900px 500px at 90% 0%, rgba(46,88,168,.10), transparent 60%),
                      var(--bg) !important; }

    .card {
        border-radius: var(--radius-xl) !important;
        border: 1px solid var(--line) !important;
        box-shadow: var(--shadow-soft) !important;
        overflow: hidden;
        background: #fff;
    }
    .card:hover { box-shadow: var(--shadow-mid) !important; }

    .card-header {
        background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(19,43,82,.06)) !important;
        border-bottom: 1px solid rgba(2,6,23,.10) !important;
        font-weight: 1000;
    }
    .card-header h6 { font-weight: 1000 !important; margin: 0; color: var(--ink); }

    .form-control {
        border-radius: var(--radius-md) !important;
        border: 1px solid rgba(15,23,42,.16) !important;
        font-weight: 800 !important;
        min-height: 44px;
    }
    .form-control:focus {
        border-color: rgba(32,66,127,.55) !important;
        box-shadow: 0 0 0 .2rem rgba(32,66,127,.18) !important;
    }

    .btn.btn-primary {
        background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
        border: none !important;
        font-weight: 900 !important;
        border-radius: 14px !important;
        box-shadow: 0 14px 28px rgba(32,66,127,.25);
        min-height: 44px;
    }
    .btn.btn-outline-secondary { border-radius: 14px !important; min-height: 44px; font-weight: 900 !important; }

    .kpi-grid {
        display:grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }
    @media (max-width: 1200px){ .kpi-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
    @media (max-width: 576px){ .kpi-grid{ grid-template-columns: 1fr; } }

    .kpi {
        border: 1px solid rgba(2,6,23,.08);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff, #fbfdff);
        box-shadow: 0 12px 24px rgba(2,6,23,.08);
        padding: 12px 14px;
        min-height: 92px;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
    }
    .kpi .label { color: #0d1925; font-weight: 800; font-size: .82rem; }
    .kpi .value { font-weight: 1000; letter-spacing: .2px; font-size: 1.28rem; color: var(--ink); }
    .kpi .tag { font-size: .78rem; font-weight: 900; color: rgba(32,66,127,.95); }

    .progress { height: 12px; border-radius: 999px; overflow: hidden; background: rgba(2,6,23,.08); }
    .progress-bar { font-weight: 900; }

    #dashLoader {
        position: fixed; inset: 0;
        background: rgba(255,255,255,0.75);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(6px);
    }
    #dashLoader .spinner-border { width: 3rem; height: 3rem; color: var(--nav-blue); }

    .panel-title {
        font-weight: 1000;
        margin: 0;
        color: var(--ink);
    }
    /* ===================== KPI VISIBILIDAD + LOOK PRO ===================== */
.kpi{
  position:relative;
  overflow:hidden;
}
.kpi:before{
  content:"";
  position:absolute; inset:-2px;
  background:
    radial-gradient(360px 120px at 10% 0%, rgba(32,66,127,.10), transparent 70%),
    radial-gradient(280px 120px at 90% 10%, rgba(46,88,168,.10), transparent 72%);
  pointer-events:none;
}
.kpi > *{ position:relative; z-index:1; }

.kpi .value{ color: var(--ink) !important; text-shadow: 0 1px 0 rgba(255,255,255,.65); }
.kpi .value.text-success{ color:#16a34a !important; }
.kpi .value.text-warning{ color:#d97706 !important; }
.kpi .value b{ color: var(--ink) !important; }

/* PROGRESS KPI: más alto, texto legible, no pegado */
.kpi .progress{
  height: 16px !important;
  background: rgba(2,6,23,.08) !important;
  border-radius: 999px !important;
  box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
}
.kpi .progress-bar{
  border-radius: 999px !important;
  font-size: .72rem !important;
  line-height: 16px !important;
  color:#fff !important;
  background: rgb(7, 19, 75) !important;
  font-weight: 900 !important;
  letter-spacing: .2px;
  text-shadow: 0 1px 0 rgba(0,0,0,.25);
  padding: 0 8px;
  min-width: 36px;
}

/* Etiquetas Avance: más visibles */
#kpiAvanceFisicoTxt, #kpiAvanceFinancieroTxt{
  color: var(--ink) !important;
  font-weight: 1000 !important;
}
/* ===================== ESTADOS (LISTA) COMPACTA + SCROLL PRO ===================== */
#contenedorEstados{
  max-height: 560px; /* igual que tabla */
  overflow:auto;
  padding: 10px !important;
}
#contenedorEstados::-webkit-scrollbar{ width:6px; }
#contenedorEstados::-webkit-scrollbar-thumb{ background: rgba(32,66,127,.28); border-radius: 10px; }

.estado-card-click{
  border-radius: 12px !important;
  padding: 10px 12px !important;
  background: rgba(15,23,42,.04) !important;
  border: 1px solid rgba(2,6,23,.08) !important;
  transition: .18s ease;
}
.estado-card-click:hover{
  transform: translateY(-1px);
  background: rgba(32,66,127,.06) !important;
  box-shadow: 0 10px 22px rgba(2,6,23,.08);
}
.estado-card-click span:first-child{
  font-weight: 900;
  font-size: .80rem !important;
  color: var(--ink);
}
.estado-card-click .badge{
  font-weight: 1000;
  border-radius: 999px;
}
/* ===================== TABLA PRO: MÁS ESPACIO + LECTURA ===================== */
#tablaProyectos{
  font-size: .86rem;
}
#tablaProyectos td, #tablaProyectos th{
  vertical-align: middle !important;
}
.dataTables_wrapper .dataTables_filter input{
  border-radius: 12px !important;
  border: 1px solid rgba(15,23,42,.16) !important;
  padding: 8px 10px !important;
  outline:none;
}
.table-responsive{
  border-radius: 14px;
}
.proj-full{ white-space: normal; }
.proj-toggle{ cursor:pointer; }
.badge-light-primary{
    color:#fff;
    background: rgb(14, 50, 117) !important;
}
.mr-1{
     color:#fff !important;;
    background: rgb(14, 50, 117) !important;
}
/* ===== Tabla PRO sin scroll horizontal ===== */
.table-responsive { overflow-x: hidden !important; } /* evita scroll derecha */
#tablaProyectos { width:100% !important; table-layout: fixed; }
#tablaProyectos th, #tablaProyectos td { vertical-align: middle; }
#tablaProyectos th { white-space: nowrap; }
#tablaProyectos td { white-space: normal; word-break: break-word; }

/* Columna Proyecto: más legible */
#tablaProyectos td:nth-child(2) { line-height: 1.2; }

/* ===== Progress PRO (número visible) ===== */
.progress.pb{
  height: 22px !important;
  border-radius: 999px;
  overflow: hidden;
  background: rgba(2,6,23,.10);
  position: relative;
}
.progress.pb .progress-bar{
  font-weight: 1000;
  font-size: .78rem;
  line-height: 22px;
  text-align: center;
  color: #fff;
  text-shadow: 0 1px 2px rgba(0,0,0,.35);
  border-radius: 999px;
}

/* ===== Badges Estado PRO ===== */
.badge-estado{
  display:inline-flex;
  align-items:center;
  gap:2px;
  padding: .28rem .55rem;
  border-radius: 999px;
  font-size: .50rem;
  color:#fff;
  letter-spacing:.1px;
  box-shadow: 0 10px 18px rgba(2,6,23,.12);
}
.badge-estado .dot{
  width:8px; height:8px; border-radius:999px;
  background: rgba(255,255,255,.9);
  box-shadow: 0 0 0 3px rgba(255,255,255,.18);
}

/* Botón ver más */
.proj-toggle{ cursor:pointer; }
.proj-full{ white-space: normal; }
.table-responsive { overflow-x: hidden !important; }

#tablaProyectos { 
    table-layout: fixed;
    width:100% !important;
}

#tablaProyectos td { 
    white-space: normal; 
    word-break: break-word;
}

/* PROGRESS PRO */
.progress.pb{
  height:22px !important;
  border-radius:999px;
  background:rgba(2,6,23,.08);
}

.progress.pb .progress-bar{

  font-size:.8rem;
  line-height:22px;
  text-align:center;
  color:#fff;
  text-shadow:0 1px 2px rgba(0,0,0,.4);
  border-radius:500px;
}

/* BADGE ESTADO PRO */
.badge-estado{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:6px 12px;
  border-radius:8px; /* ya no redondo */
  font-weight:800;
  font-size:0.78rem;
  color:#fff;
  white-space:nowrap; /* 👈 evita que se parta en varias líneas */
  line-height:1;
  box-shadow:0 4px 10px rgba(0,0,0,.12);
}

.badge-estado .dot{
  width:6px;
  height:6px;
  border-radius:50%;
  background:rgba(255,255,255,.9);
}
#tablaProyectos td:nth-child(7){
  white-space:nowrap;
}

#tablaProyectos td:nth-child(8){
  white-space:nowrap;
}
.text-muted{
    color:rgb(14, 50, 117) !important;
}
</style>

<body class="">
    <div id="dashLoader">
        <div class="d-flex flex-column align-items-center justify-content-center h-100">
            <div class="spinner-border" role="status"></div>
            <p class="mt-3 font-weight-bold text-dark">Consultando API de Proyectos...</p>
        </div>
    </div>

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:10px;">
                <div>
                    <h4 class="panel-title"><i class="feather icon-bar-chart-2 mr-1"></i> Panel de Proyectos</h4>
                    <div class="text-muted" style="font-weight:700;font-size:.92rem;">
                        KPIs · Estados · Tabla · Gráficos (API Rendición de Cuentas)
                    </div>
                </div>
                <div class="d-flex flex-wrap" style="gap:10px;">
                    <a href="proyectos_rpc_dash.php" class="btn btn-outline-secondary">
                        <i class="feather icon-map"></i> Volver al mapa
                    </a>
                </div>
            </div>

            <!-- Filtros (misma estructura/IDs para NO romper JS) -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="feather icon-search"></i> Consulta de Proyectos</h6>
                    <small class="text-muted font-weight-bold">sigid.santander.gov.co</small>
                </div>
                <div class="card-body">
                    <?php
                    require_once './admin/classes/Colombia.php';
                    $municipiosPanelSelect = Colombia::getMunicipiosSvg('68');
                    usort($municipiosPanelSelect, fn($a, $b) => strcmp($a['municipio'], $b['municipio']));
                    ?>
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label font-weight-bold">Vigencia</label>
                            <select class="form-control" id="selectVigencia">
                                <option value="">-- Todas --</option>
                                <?php for ($y = 2020; $y <= 2026; $y++): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($vigencia == $y ? 'selected' : ''); ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label font-weight-bold">Código BPIN</label>
                            <input type="text" class="form-control" id="inputBpin" placeholder="Ej: 2024004680222" maxlength="20">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">Dependencia</label>
                            <select class="form-control" id="selectDependencia">
                                <option value="Todas">Todas</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label font-weight-bold">Municipio</label>
                            <select class="form-control" id="selectMunicipio">
                                <option value="Todos">Todos</option>
                                <?php foreach ($municipiosPanelSelect as $mun):
                                    if (empty($mun['nombre_api_rpc'])) continue; ?>
                                    <option value="<?php echo htmlspecialchars($mun['nombre_api_rpc']); ?>"
                                        <?php echo ($filtroMun === $mun['nombre_api_rpc'] ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars(ucwords(strtolower($mun['municipio']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label font-weight-bold">Provincia</label>
                            <select class="form-control" id="selectProvincia">
                                <option value="Todas">Todas</option>
                                <?php foreach ($provincias as $prov): ?>
                                    <option value="<?php echo htmlspecialchars($prov); ?>" <?php echo ($provincia === $prov ? 'selected' : ''); ?>>
                                        <?php echo htmlspecialchars($prov); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary btn-block mr-1" id="btnBuscar">
                                <i class="feather icon-search"></i> Buscar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnLimpiar" title="Limpiar filtros">
                                <i class="feather icon-x"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <label class="form-label font-weight-bold">
                                <i class="feather icon-layers"></i> Múltiples BPIN
                                <small class="text-muted font-weight-normal">(opcional - separados por coma, punto y coma o Enter)</small>
                            </label>
                            <textarea class="form-control" id="inputMultiplesBpin" rows="2"
                                      placeholder="2024004680041, 2024004680010, 2024004680006"
                                      style="font-family: monospace; font-size: 0.9rem;"></textarea>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="w-100">
                                <span id="contadorBpin" class="badge badge-light-primary" style="font-size:0.85rem;">0 BPIN ingresados</span>
                                <small class="text-muted d-block mt-1">
                                    Si ingresa BPIN aquí, se consultarán todos. Si también selecciona vigencia, se combinará la búsqueda.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <span id="infoConsulta" class="text-muted" style="font-size:0.82rem;">
                                <i class="feather icon-info"></i> Seleccione vigencia, ingrese BPIN, o combine ambos filtros.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($errorApi)): ?>
                <div class="alert alert-warning" role="alert">
                    <h5><i class="feather icon-alert-triangle"></i> No se pudieron obtener los datos</h5>
                    <p><?php echo htmlspecialchars($errorApi); ?></p>
                </div>
            <?php endif; ?>

            <!-- KPIs PRO -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="feather icon-activity"></i> Indicadores (Resumen)</h6>
                    <span class="badge badge-light-primary" style="font-weight:900;" id="kpiTotalProyectos"><?php echo $totalProyectos; ?></span>
                </div>
                <div class="card-body">
                    <div class="kpi-grid">
                        <div class="kpi">
                            <div class="label">Valor Total Inversión</div>
                               <div class="value text-success" id="kpiValorTotal"><?php echo Util::formatBigMoneyString($valorTotal); ?></div>
                            
                            
                            <div class="tag"><i class="feather icon-trending-up"></i> Total</div>
                        </div>

                        <div class="kpi">
                            <div class="label">Valor Ejecutado</div>
                           <div class="value text-warning" id="kpiValorEjecutado"><?php echo Util::formatBigMoneyString($valorEjecutado); ?></div>
                            <div class="tag"><i class="feather icon-check-circle"></i> Ejecutado</div>
                        </div>

                        <div class="kpi">
                            <div class="label">Aportes (Gob / Mun / Nal)</div>
                            <div class="value" style="font-size:1.02rem;line-height:1.25;">
                                <div class="d-flex justify-content-between"><span class="text-muted">Gobernación</span><b class="text-primary" id="kpiAporteGobernacion"><?php echo Util::formatBigMoneyString($aporteGobernacion); ?></b></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Municipio</span><b class="text-info" id="kpiAporteMunicipal"><?php echo Util::formatBigMoneyString($aporteMunicipal); ?></b></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">Gobierno Nacional</span><b class="text-dark" id="kpiAporteNacional"><?php echo Util::formatBigMoneyString($aporteNacional); ?></b></div>
                            </div>
                            <div class="tag"><i class="feather icon-pie-chart"></i> Fuentes</div>
                        </div>

                        <div class="kpi">
                            <div class="label">Promedios de avance</div>
                            <?php
                                $pctFisico = $promedioAvanceFisico <= 1 ? round($promedioAvanceFisico * 100, 1) : round($promedioAvanceFisico, 1);
                                $pctFinan  = $promedioAvanceFinan  <= 1 ? round($promedioAvanceFinan  * 100, 1) : round($promedioAvanceFinan, 1);
                            ?>
                            <div style="margin-top:6px;">
                                <div class="d-flex justify-content-between" style="font-weight:800;font-size:.82rem;color:var(--muted);">
                                    <span>Avance Físico</span><span id="kpiAvanceFisicoTxt"><?php echo $pctFisico; ?>%</span>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-success" id="kpiAvanceFisico" style="width:<?php echo $pctFisico; ?>%;"><?php echo $pctFisico; ?>%</div>
                                </div>

                                <div class="d-flex justify-content-between mt-2" style="font-weight:800;font-size:.82rem;color:var(--muted);">
                                    <span>Avance Financiero</span><span id="kpiAvanceFinancieroTxt"><?php echo $pctFinan; ?>%</span>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-info" id="kpiAvanceFinanciero" style="width:<?php echo min($pctFinan, 100); ?>%;"><?php echo $pctFinan; ?>%</div>
                                </div>
                            </div>
                            <div class="tag"><i class="feather icon-target"></i> Avances</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estados + Tabla -->
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="feather icon-layers"></i> Estados de Proyectos</h6>
                        </div>
                        <div class="card-body" id="contenedorEstados">
                            <div class="text-center py-4">
                                <i class="feather icon-inbox text-muted" style="font-size:2rem;"></i>
                                <p class="text-muted mt-2 mb-0">Realice una búsqueda para ver estados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6><i class="feather icon-list"></i> Lista de Proyectos</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="max-height: 560px; overflow-x: hidden;">
                                <table id="tablaProyectos" class="table table-hover table-striped table-sm" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>BPIN</th>
                                            <th>Proyecto</th>
                                            <th>Dependencia</th>
                                            <th>Estado</th>
                                            <th>Avance Físico</th>
                                            <th>Avance Financiero</th>
                                            <th>Valor Total</th>
                                            <th>Vigencia</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row mt-4">
                <div class="col-lg-6 col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6><i class="feather icon-pie-chart"></i> Distribución por Estado</h6>
                        </div>
                        <div class="card-body">
                            <div id="chartEstados" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="feather icon-briefcase"></i> Top Dependencias</h6>
                        </div>
                        <div class="card-body">
                            <div id="chartDependencias" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Detalle de Proyecto (se queda en Panel) -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius:18px;overflow:hidden;border:1px solid rgba(15,23,42,.12);box-shadow:0 16px 42px rgba(2,6,23,.18);">
                <div class="modal-header" style="background:linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));color:#fff;">
                    <h5 class="modal-title" id="modalDetalleTitle">
                        <i class="feather icon-file-text"></i> Detalle del Proyecto
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span style="color:#fff;">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalDetalleBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include './admin/include/footer.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- MISMO JS -->
    <script src="admin/js/proyectos_rpc_dash.js"></script>

    <?php if ($autoBuscar): ?>
    <script>
    // Auto-búsqueda al llegar desde el dashboard con parámetros
    $(function () {
        <?php if (!empty($filtroDep) && $filtroDep !== 'Todas'): ?>
        // Dependencia se carga dinámicamente: se aplica después de la primera respuesta
        PROYECTOS_RPC._pendingDependencia = <?php echo json_encode($filtroDep); ?>;
        <?php endif; ?>

        setTimeout(function () {
            if (typeof PROYECTOS_RPC !== 'undefined' && PROYECTOS_RPC.cargarDatos) {
                PROYECTOS_RPC.cargarDatos();
            }
        }, 300);
    });
    </script>
    <?php endif; ?>
</body>
</html>