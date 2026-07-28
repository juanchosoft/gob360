<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
require './admin/classes/DashboardAlcalde.php';

function safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$userType = SessionData::getUserType();
$tiposUsuarioMunicipal = ['Alcalde', 'Auxiliar_Alcalde', 'Secretario_Despacho', 'Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);
$codigoMunicipio = $isUsuarioMunicipal ? SessionData::getCodigoMunicipio() : '';

$nombreMunicipio = DashboardAlcalde::getNombreMunicipio($codigoMunicipio);
$todasSecretarias = DashboardAlcalde::getTodasSecretarias($codigoMunicipio);
$totalSecretarias = count($todasSecretarias);
$proyectos = DashboardAlcalde::getResumenProyectos($codigoMunicipio);
$topSecretarias = DashboardAlcalde::getTopSecretariasInversion($codigoMunicipio, 5);
$visitas = DashboardAlcalde::getResumenVisitas($codigoMunicipio);
$totalCompromisos = DashboardAlcalde::getTotalCompromisos($codigoMunicipio);
$plan = DashboardAlcalde::getResumenPlanDesarrollo($codigoMunicipio);
$componentes = DashboardAlcalde::getComponentes($codigoMunicipio);
$proyectosPorSecretaria = DashboardAlcalde::getProyectosPorSecretaria($codigoMunicipio);

$topProyectos = DashboardAlcalde::getTopProyectos($codigoMunicipio, 5);
$proyectosConSecretaria = DashboardAlcalde::getProyectosConSecretaria($codigoMunicipio);
$visitasList = DashboardAlcalde::getVisitasList($codigoMunicipio);

$pieLabels = array_map(fn($s) => $s['secretaria'], $proyectosPorSecretaria);
$pieValues = array_map(fn($s) => (int)$s['total_proyectos'], $proyectosPorSecretaria);

// Color fijo por secretaría (basado en $todasSecretarias para consistencia)
$paletaSec = ['#60A5FA','#A78BFA','#34D399','#FBBF24','#FB7185','#22D3EE','#F472B6','#93C5FD','#F97316','#84CC16','#22C55E','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
$secColorMap = [];
foreach ($todasSecretarias as $i => $sec) {
    $nombre = $sec['nombre'] ?? $sec['secretaria'] ?? '';
    if ($nombre) $secColorMap[$nombre] = $paletaSec[$i % count($paletaSec)];
}

$ranking = [];
foreach ($topSecretarias as $i => $s) {
    $nombre = $s['nombre'] ?? $s['secretaria'] ?? 'Secretaría';
    $ranking[] = ['name' => $nombre, 'score' => round(($s['valor_total'] ?? 0) / 1000000, 1)];
}


$topProyectosLabels = array_map(fn($p) => mb_strimwidth($p['proyecto'] ?? 'Sin nombre', 0, 55, '...'), $topProyectos);
$topProyectosValores = array_map(fn($p) => round(($p['valor_proyecto'] ?? 0) / 1000000, 2), $topProyectos);

$barLabels = array_map(fn($s) => $s['secretaria'], $proyectosPorSecretaria);
$barValues = array_map(fn($s) => (int)$s['total_proyectos'], $proyectosPorSecretaria);

$radarLabels = ['Inversión','Cobertura','Impacto','Riesgo','Velocidad','Gestión'];
$invPct = min(100, round(($proyectos['valor_total'] / 100000000000) * 100));
$secPct = $totalSecretarias > 0 ? round((count($proyectosPorSecretaria) / $totalSecretarias) * 100) : 0;
$impPct = $proyectos['total'] > 0 ? min(100, round(($proyectos['valor_total'] / $proyectos['total']) / 1000000000 * 2)) : 0;
$riesPct = $totalCompromisos > 5 ? 80 : ($totalCompromisos > 0 ? 50 : 30);
$velPct = $proyectos['total'] > 20 ? 85 : ($proyectos['total'] > 10 ? 65 : 40);
$gesPct = $proyectos['total'] > 0 && $proyectos['por_estado']['ejecucion'] > 0 ? 70 : 45;
$radarValues = [$invPct, $secPct, $impPct, $riesPct, $velPct, $gesPct];

$invSecLabels = array_map(fn($s) => $s['secretaria'], $proyectosPorSecretaria);
$invSecValores = array_map(fn($s) => round(($s['valor_total'] ?? 0) / 1000000, 2), $proyectosPorSecretaria);

$totalCompromisosPactados = $proyectos['total'];
$totalCompromisosCumplidos = $proyectos['por_estado']['terminados'] + $proyectos['por_estado']['entregados'];
$valorInversion = $proyectos['valor_total'];
$alertasTempranas = 0;

$invM = round($proyectos['valor_total'] / 1000000, 2);
$veredasPct = $visitas['veredas_totales'] > 0 ? round(($visitas['veredas_visitadas'] / $visitas['veredas_totales']) * 100) : 0;
$tablaAlertas = [
    ['tipo' => 'Inversión', 'detalle' => '$' . number_format($invM, 2, ',', '.') . 'M en ' . number_format($proyectos['total']) . ' proyectos', 'nivel' => $proyectos['total'] > 0 ? 'BAJA' : 'MEDIA', 'estado' => $proyectos['total'] > 0 ? 'OK' : 'Revisar'],
    ['tipo' => 'Proyectos x Sec.', 'detalle' => count($proyectosPorSecretaria) . ' secretarías con proyectos activos', 'nivel' => count($proyectosPorSecretaria) > 0 ? 'BAJA' : 'MEDIA', 'estado' => count($proyectosPorSecretaria) > 0 ? 'OK' : 'Revisar'],
    ['tipo' => 'Compromisos', 'detalle' => number_format($totalCompromisos) . ' compromisos registrados', 'nivel' => $totalCompromisos > 0 ? 'BAJA' : 'MEDIA', 'estado' => $totalCompromisos > 0 ? 'En seguimiento' : 'Sin datos'],
    ['tipo' => 'Veredas', 'detalle' => number_format($visitas['veredas_visitadas']) . ' de ' . number_format($visitas['veredas_totales']) . ' veredas visitadas (' . $veredasPct . '%)', 'nivel' => $veredasPct >= 50 ? 'BAJA' : ($veredasPct >= 25 ? 'MEDIA' : 'ALTA'), 'estado' => $veredasPct >= 50 ? 'Cubierto' : ($veredasPct >= 25 ? 'En progreso' : 'Pendiente')],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    :root{
      --ink:#EAF0FF; --muted:rgba(234,240,255,.72);
      --glass: rgba(255,255,255,.08); --glass2: rgba(255,255,255,.06);
      --stroke: rgba(255,255,255,.12); --stroke2: rgba(255,255,255,.08);
      --radius-xl:22px; --radius-lg:16px;
      --shadow-soft: 0 14px 40px rgba(0,0,0,.28); --shadow-mid: 0 22px 70px rgba(0,0,0,.34);
      --safe-top: 96px;
      --brand:#60A5FA; --brand2:#A78BFA; --ok:#34D399; --warn:#FBBF24; --danger:#FB7185; --cyan:#22D3EE;
    }
    body{
      margin:0; color: var(--ink);
      background: radial-gradient(1200px 600px at 10% 10%, rgba(96,165,250,.22), transparent 55%),
                  radial-gradient(900px 500px at 90% 15%, rgba(167,139,250,.18), transparent 55%),
                  radial-gradient(900px 520px at 70% 90%, rgba(52,211,153,.14), transparent 55%),
                  linear-gradient(135deg, #081226 0%, #0B1B38 45%, #070B16 100%);
      min-height:100vh;
    }
    .pcoded-content{ padding: calc(var(--safe-top) + 16px) 16px 18px !important; }
    @media(min-width:768px){ :root{ --safe-top: 112px; } .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; } }
    @media(min-width:1200px){ :root{ --safe-top: 120px; } .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; } }
    .au-topbar{ display:flex; flex-direction:column; gap:12px; margin-bottom:16px; }
    @media(min-width:768px){ .au-topbar{ flex-direction:row; align-items:center; justify-content:space-between; } }
    .au-title{ margin:0; font-weight:1000; font-size:1.62rem; letter-spacing:.2px; line-height:1.15; color: var(--ink); text-shadow: 0 18px 55px rgba(0,0,0,.38); }
    .au-subtitle{ margin:6px 0 0; color: var(--muted); font-size:.96rem; max-width: 980px; }
    .au-badge{ padding:8px 12px; border-radius:999px; font-size:.80rem; font-weight:900; color:rgba(255,255,255,.92); background:linear-gradient(135deg,rgba(96,165,250,.20),rgba(167,139,250,.16)); border:1px solid rgba(255,255,255,.16); box-shadow:0 10px 30px rgba(0,0,0,.22); white-space:nowrap; backdrop-filter:blur(10px); }
    .au-chips{ display:flex; flex-wrap:wrap; gap:10px; margin-top:10px; }
    .chip{ padding:8px 10px; border-radius:999px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); color:rgba(255,255,255,.86); font-weight:800; font-size:.80rem; backdrop-filter:blur(10px); }
    .chip b{ color:#fff; }
    .au-grid{ display:grid; gap:16px; }
    @media(min-width:768px){ .au-grid.md-4{ grid-template-columns:repeat(4,1fr); } .au-grid.md-2{ grid-template-columns:repeat(2,1fr); } .au-grid.md-3{ grid-template-columns:repeat(3,1fr); } }
    .au-card{ background:linear-gradient(180deg,rgba(255,255,255,.10),rgba(255,255,255,.06)); border:1px solid rgba(255,255,255,.12); border-radius:var(--radius-xl); box-shadow:var(--shadow-mid); overflow:hidden; backdrop-filter:blur(12px); position:relative; }
    .au-card:before{ content:""; position:absolute; inset:-1px; background:radial-gradient(520px 160px at 10% 0%,rgba(96,165,250,.22),transparent 60%),radial-gradient(520px 160px at 90% 0%,rgba(167,139,250,.18),transparent 60%); pointer-events:none; opacity:.75; }
    .au-card > *{ position:relative; }
    .au-card-h{ padding:16px 18px; border-bottom:1px solid rgba(255,255,255,.10); display:flex; align-items:flex-start; justify-content:space-between; gap:12px; background:linear-gradient(180deg,rgba(0,0,0,.20),transparent); }
    .au-card-h .t{ font-weight:1000; margin:0; color:#fff; font-size:1.02rem; letter-spacing:.15px; }
    .au-card-h .s{ margin:4px 0 0; color:rgba(255,255,255,.68); font-size:.85rem; }
    .au-card-b{ padding:16px 18px; }
    .kpi{ display:flex; flex-direction:column; gap:8px; }
    .kpi .label{ color:rgba(255,255,255,.72); font-weight:800; font-size:.86rem; }
    .kpi .value{ font-size:1.55rem; font-weight:1000; color:#fff; line-height:1.05; letter-spacing:.2px; text-shadow:0 10px 30px rgba(0,0,0,.30); }
    .kpi .hint{ color:rgba(255,255,255,.62); font-size:.80rem; }
    .kpi-row{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .trend{ font-weight:1000; font-size:.82rem; padding:6px 10px; border-radius:999px; border:1px solid rgba(255,255,255,.14); background:rgba(255,255,255,.06); white-space:nowrap; }
    .trend.up{ color:rgba(52,211,153,.98); }
    .trend.down{ color:rgba(251,113,133,.98); }
    .kpi-line{ height:10px; border-radius:999px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.10); overflow:hidden; margin-top:2px; }
    .kpi-line > span{ display:block; height:100%; width:64%; background:linear-gradient(90deg,rgba(96,165,250,.92),rgba(167,139,250,.92)); border-radius:999px; box-shadow:0 8px 24px rgba(96,165,250,.25); }
    .chart-wrap{ position:relative; width:100%; height:390px; }
    @media(max-width:576px){ .chart-wrap{ height:300px; } }
    .chart-sm{ height:180px; }
    @media(max-width:576px){ .chart-sm{ height:160px; } }
    .au-section{ margin-top:16px; }
    .soft-divider{ height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent); margin:16px 0 6px; }
    .au-breakdown{ margin-top:16px; display:grid; gap:12px; }
    @media(min-width:768px){ .au-breakdown{ grid-template-columns:repeat(2,1fr); } }
    @media(min-width:1200px){ .au-breakdown{ grid-template-columns:repeat(2,1fr); } }
    .bd-item{ display:flex; align-items:center; justify-content:space-between; gap:14px; padding:14px 16px; border-radius:16px; border:1px solid rgba(255,255,255,.10); background:rgba(255,255,255,.06); backdrop-filter:blur(10px); }
    .bd-left{ display:flex; align-items:center; gap:10px; min-width:0; }
    .bd-dot{ width:12px; height:12px; border-radius:999px; flex:0 0 auto; box-shadow:0 8px 18px rgba(0,0,0,.25); }
    .bd-name{ font-weight:900; font-size:.88rem; color:rgba(255,255,255,.92); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:260px; }
    .bd-right{ text-align:right; flex:0 0 auto; }
    .bd-p{ font-weight:1000; color:#fff; line-height:1.1; }
    .bd-v{ font-size:.80rem; color:rgba(255,255,255,.66); }

    .au-table{ width:100%; border-collapse:separate; border-spacing:0 10px; }
    .au-table th{ text-align:left; color:rgba(255,255,255,.72); font-size:.82rem; font-weight:900; padding:0 10px; }
    .au-table td{ padding:12px 12px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); color:#fff; font-weight:800; }
    .au-table tr td:first-child{ border-radius:14px 0 0 14px; }
    .au-table tr td:last-child{ border-radius:0 14px 14px 0; }
    .tag{ padding:6px 10px; border-radius:999px; font-weight:1000; font-size:.78rem; border:1px solid rgba(255,255,255,.14); display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.06); white-space:nowrap; }
    .tag.high{ color:#ef4444; background:rgba(239,68,68,.15); border-color:rgba(239,68,68,.25); }
    .tag.med{ color:#fbbf24; background:rgba(251,191,36,.15); border-color:rgba(251,191,36,.25); }
    .tag.low{ color:#34d399; background:rgba(52,211,153,.15); border-color:rgba(52,211,153,.25); }
    .chart-inv-wrap{ position:relative; width:100%; height:420px; }
    @media(max-width:768px){ .chart-inv-wrap{ height:340px; } }

    /* Modal oscuro */
    #modalPieSecretarias .modal-content{
      background: linear-gradient(135deg, #0f172a, #1e293b) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      border-radius: 22px !important;
      box-shadow: 0 22px 70px rgba(0,0,0,.5);
    }
    #modalPieSecretarias .modal-header{
      border-bottom: 1px solid rgba(255,255,255,.10) !important;
      padding: 18px 22px;
    }
    #modalPieSecretarias .modal-header .close{ color:#fff !important; opacity:.7; font-size:28px; }
    #modalPieSecretarias .modal-header .close:hover{ opacity:1; }
    #modalPieSecretarias .modal-body{ padding:0 22px 18px !important; }
    #modalPieSecretarias .modal-body table{
      width:100%; color:#e2e8f0; border-collapse:collapse;
    }
    #modalPieSecretarias .modal-body table th{
      text-align:left; padding:10px 8px; font-size:12px; font-weight:900;
      color:#94a3b8; text-transform:uppercase; letter-spacing:.5px;
      border-bottom:1px solid rgba(255,255,255,.08);
    }
    #modalPieSecretarias .modal-body table td{
      padding:10px 8px; font-size:13px; font-weight:700;
      border-bottom:1px solid rgba(255,255,255,.05);
    }
    #modalPieSecretarias .modal-body table tr:last-child td{ border-bottom:none; }
    #modalPieSecretarias .modal-backdrop{ background:rgba(0,0,0,.7); }
    #modalVisitas .modal-content{ background:linear-gradient(135deg,#0f172a,#1e293b) !important; border:1px solid rgba(255,255,255,.12) !important; border-radius:22px !important; box-shadow:0 22px 70px rgba(0,0,0,.5); }
    #modalVisitas .modal-header{ border-bottom:1px solid rgba(255,255,255,.10) !important; padding:18px 22px; }
    #modalVisitas .modal-header .close{ color:#fff !important; opacity:.7; font-size:28px; }
    #modalVisitas .modal-body{ padding:0 22px 18px !important; }
    #modalVisitas .modal-body table{ width:100%; color:#e2e8f0; border-collapse:collapse; }
    #modalVisitas .modal-body table th{ text-align:left; padding:10px 8px; font-size:12px; font-weight:900; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid rgba(255,255,255,.08); }
    #modalVisitas .modal-body table td{ padding:10px 8px; font-size:13px; font-weight:700; border-bottom:1px solid rgba(255,255,255,.05); }
    #modalVisitas .modal-body table tr:last-child td{ border-bottom:none; }
  </style>
</head>
<body>
  <div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>
  <div class="pcoded-main-container">
    <div class="pcoded-content">
      <div class="au-topbar">
        <div>
          <h1 class="au-title">Dashboard · Acción Unificada Municipio de <?php echo safe($nombreMunicipio); ?></h1>
          <div class="au-subtitle">Vista ejecutiva con datos en vivo del municipio.</div>
          <div class="au-chips">
            <div class="chip">📋 <b><?php echo number_format($proyectos['total']); ?></b> proyectos</div>
            <div class="chip">🏛️ <b><?php echo $totalSecretarias; ?></b> secretarías</div>
            <div class="chip">👥 <b><?php echo number_format($visitas['total']); ?></b> visitas</div>
          </div>
        </div>
        <div class="au-badge"><?php echo date('d/m/Y H:i'); ?> · Colombia</div>
      </div>

      <div class="au-grid md-4">
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Inversión Total</p><p class="s">Valor consolidado</p></div><span class="au-badge">COP</span></div>
          <div class="au-card-b kpi">
            <div class="kpi-row"><div class="label">Total proyectos</div><div class="trend <?php echo $proyectos['total'] > 0 ? 'up' : ''; ?>"><?php echo $proyectos['total'] > 0 ? '▲' : ''; ?> <?php echo number_format($proyectos['total']); ?></div></div>
            <div class="value">$<?php echo number_format($valorInversion / 1000000, 1, ',', '.'); ?>M</div>
            <div class="kpi-line"><span style="width:78%"></span></div>
            <div class="hint"><?php echo number_format($proyectos['total']); ?> proyectos registrados</div>
          </div>
        </div>
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Proyectos en Ejecución</p><p class="s">Total general</p></div><span class="au-badge">Total</span></div>
          <div class="au-card-b kpi">
            <div class="kpi-row"><div class="label">En ejecución</div><div class="trend <?php echo $proyectos['por_estado']['ejecucion'] > 0 ? 'up' : ''; ?>"><?php echo $proyectos['por_estado']['ejecucion'] > 0 ? '▲' : ''; ?> <?php echo $proyectos['por_estado']['ejecucion']; ?></div></div>
            <div class="value"><?php echo number_format($proyectos['por_estado']['ejecucion']); ?></div>
            <div class="kpi-line"><span style="width:64%"></span></div>
            <div class="hint"><?php echo $proyectos['total'] > 0 ? round(($proyectos['por_estado']['ejecucion'] / $proyectos['total']) * 100) : 0; ?>% del total</div>
          </div>
        </div>
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Completados</p><p class="s">Terminados + Entregados</p></div><span class="au-badge">OK</span></div>
          <div class="au-card-b kpi">
            <div class="kpi-row"><div class="label">Total</div><div class="trend <?php echo $completados > 0 ? 'up' : ''; ?>"><?php echo $completados > 0 ? '▲' : ''; ?> <?php echo number_format($completados); ?></div></div>
            <div class="value"><?php echo number_format($completados); ?></div>
            <?php $completados = $proyectos['por_estado']['terminados'] + $proyectos['por_estado']['entregados']; $pctCumpl = $proyectos['total'] > 0 ? round(($completados / $proyectos['total']) * 100) : 0; ?>
            <div class="kpi-line"><span style="width:<?php echo $pctCumpl; ?>%"></span></div>
            <div class="hint">Cumplimiento: <?php echo $pctCumpl; ?>%</div>
          </div>
        </div>
        <div class="au-card" id="cardVisitas" style="cursor:pointer;">
          <div class="au-card-h"><div><p class="t">Visitas y Cobertura</p><p class="s">Veredas visitadas — <span style="color:#60a5fa;font-weight:900;">🖱️ clic para ver detalle</span></p></div><span class="au-badge">📍</span></div>
          <div class="au-card-b kpi">
            <div class="kpi-row"><div class="label">Visitas / Veredas</div><div class="trend <?php echo $visitas['total'] > 0 ? 'up' : ''; ?>"><?php echo $visitas['total'] > 0 ? '▲' : ''; ?> <?php echo number_format($visitas['total']); ?></div></div>
            <div class="value"><?php echo number_format($visitas['veredas_visitadas']); ?><span style="font-size:1rem;color:var(--muted);">/<?php echo number_format($visitas['veredas_totales']); ?></span></div>
            <div class="kpi-line"><span style="width:<?php echo $visitas['veredas_totales'] > 0 ? round(($visitas['veredas_visitadas'] / $visitas['veredas_totales']) * 100) : 0; ?>%"></span></div>
            <div class="hint"><?php echo $visitas['veredas_restantes']; ?> veredas pendientes</div>
          </div>
        </div>
      </div>

      <div class="au-section au-grid md-2">
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Top Proyectos por Inversión</p><p class="s">Los 5 proyectos de mayor valor</p></div><span class="au-badge">Top 5</span></div>
          <div class="au-card-b"><div class="chart-wrap" style="height:320px;"><canvas id="chartTopProyectos"></canvas></div></div>
        </div>
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Top Secretarías con mejor Inversión</p><p class="s">Distribución por secretaría</p></div><span class="au-badge">Torta</span></div>
          <div class="au-card-b">
            <div class="chart-wrap" style="height:<?php echo max(200, count($ranking) * 60); ?>px;">
              <canvas id="chartTopSec"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="au-section">
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Proyectos por Secretaría</p><p class="s">Total de proyectos por secretaría municipal</p></div><span class="au-badge">Barras</span></div>
          <div class="au-card-b"><div class="chart-wrap"><canvas id="barPlan"></canvas></div></div>
        </div>
      </div>

      <div class="au-section">
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Distribución de Proyectos por Secretaría</p><p class="s">Cantidad de proyectos por secretaría municipal — <span style="color:#60a5fa;font-weight:900;">🖱️ clic en la gráfica para ver proyectos</span></p></div><span class="au-badge">Torta</span></div>
          <div class="au-card-b">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">
              <div class="chart-wrap" style="height:340px;"><canvas id="pieSecretarias" style="cursor:pointer;"></canvas></div>
              <div class="au-breakdown" id="pieBreakdown" style="margin-top:0;">
                <?php for($i=0; $i<count($pieLabels); $i++): ?>
                  <div class="bd-item">
                    <div class="bd-left"><span class="bd-dot" id="dot_<?php echo $i; ?>"></span><div class="bd-name" title="<?php echo safe($pieLabels[$i]); ?>"><?php echo safe($pieLabels[$i]); ?></div></div>
                    <div class="bd-right">
                      <div class="bd-p"><?php echo number_format($pieValues[$i], 0, ',', '.'); ?></div>
                      <div class="bd-v">proyectos</div>
                    </div>
                  </div>
                <?php endfor; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="au-section au-grid md-2">
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Balance Estratégico</p><p class="s">Indicadores calculados del municipio</p></div><span class="au-badge">Radar</span></div>
          <div class="au-card-b">
            <div class="chart-wrap" style="height:340px;"><canvas id="radarBalance"></canvas></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px;margin-top:14px;padding:14px;border-radius:14px;background:rgba(0,0,0,.25);">
              <div style="font-size:11px;font-weight:900;color:#94a3b8;text-transform:uppercase;grid-column:1/-1;margin-bottom:2px;">📊 Métricas del Radar</div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#60a5fa;">●</span> Inversión: <?php echo $invPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Valor total en Millones COP</span></div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#a78bfa;">●</span> Cobertura: <?php echo $secPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Secretarías con proyectos</span></div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#34d399;">●</span> Impacto: <?php echo $impPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Valor promedio por proyecto</span></div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#fbbf24;">●</span> Riesgo: <?php echo $riesPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Según compromisos registrados</span></div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#fb7185;">●</span> Velocidad: <?php echo $velPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Cantidad de proyectos</span></div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#22d3ee;">●</span> Gestión: <?php echo $gesPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Proyectos en ejecución vs total</span></div>
            </div>
          </div>
        </div>
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Cumplimiento Global</p><p class="s">Proyectos completados vs total</p></div><span class="au-badge">Doughnut</span></div>
          <div class="au-card-b"><div class="chart-wrap" style="height:340px;"><canvas id="doughMeta"></canvas></div></div>
        </div>
      </div>

      <!-- Inversión por Secretaría (ancho completo) -->
      <div class="au-section">
        <div class="au-card">
          <div class="au-card-h"><div><p class="t">Inversión por Secretaría</p><p class="s">Valor total en Millones $ por secretaría municipal</p></div><span class="au-badge">Barras</span></div>
          <div class="au-card-b"><div class="chart-inv-wrap"><canvas id="chartInvSec"></canvas></div></div>
        </div>
      </div>

      <!-- Resumen Ejecutivo (ancho completo) -->
      <div class="au-section">
        <div class="au-card">
          <div class="au-card-h">
            <div>
              <p class="t">Resumen Ejecutivo</p>
              <p class="s">Indicadores del municipio</p>
            </div>
            <span class="au-badge">Tabla</span>
          </div>
          <div class="au-card-b">
            <div class="table-responsive" style="border-radius:16px;">
              <table class="au-table">
                <thead><tr><th style="padding-left:12px;">Indicador</th><th>Detalle</th><th>Nivel</th><th style="padding-right:12px;">Estado</th></tr></thead>
                <tbody>
                <?php foreach($tablaAlertas as $a):
                  $lvl = strtoupper($a['nivel']);
                  $cls = ($lvl==='ALTA') ? 'high' : (($lvl==='MEDIA') ? 'med' : 'low');
                ?>
                  <tr>
                    <td style="width:140px;"><?php echo safe($a['tipo']); ?></td>
                    <td><?php echo safe($a['detalle']); ?></td>
                    <td style="width:120px;"><span class="tag <?php echo $cls; ?>"><?php echo ($lvl==='ALTA'?'⚠️':''); ?><?php echo ($lvl==='MEDIA'?'📌':''); ?><?php echo ($lvl==='BAJA'?'✅':''); ?> <?php echo safe($lvl); ?></span></td>
                    <td style="width:130px;"><?php echo safe($a['estado']); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;margin-top:14px;padding:14px;border-radius:14px;background:rgba(0,0,0,.25);">
              <div style="font-size:11px;font-weight:900;color:#94a3b8;text-transform:uppercase;grid-column:1/-1;margin-bottom:2px;">📋 Leyenda de Niveles</div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#34d399;">●</span> BAJA<br><span style="font-size:10px;color:#64748b;font-weight:700;">Indicador positivo (≥50% o >0)</span></div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#fbbf24;">●</span> MEDIA<br><span style="font-size:10px;color:#64748b;font-weight:700;">Indicador parcial (≥25%)</span></div>
              <div style="font-size:11px;font-weight:800;line-height:1.4;"><span style="color:#ef4444;">●</span> ALTA<br><span style="font-size:10px;color:#64748b;font-weight:700;">Requiere atención (&lt;25%)</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Visitas -->
  <div class="modal fade" id="modalVisitas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" style="color:#fff; font-weight:1000; font-size:18px;">📍 Visitas Realizadas</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" id="modalVisitasBody"></div>
      </div>
    </div>
  </div>

  <!-- Modal Proyectos por Secretaría -->
  <div class="modal fade" id="modalPieSecretarias" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalPieTitle" style="color:#fff; font-weight:1000; font-size:18px;"></h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" id="modalPieBody"></div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <script>
    window.dashData = {
      pieLabels: <?php echo json_encode($pieLabels, JSON_UNESCAPED_UNICODE); ?>,
      pieValues: <?php echo json_encode($pieValues); ?>,
      barLabels: <?php echo json_encode($barLabels, JSON_UNESCAPED_UNICODE); ?>,
      barValues: <?php echo json_encode($barValues); ?>,
      topProyectosLabels: <?php echo json_encode($topProyectosLabels, JSON_UNESCAPED_UNICODE); ?>,
      topProyectosValores: <?php echo json_encode($topProyectosValores); ?>,
      radarLabels: <?php echo json_encode($radarLabels, JSON_UNESCAPED_UNICODE); ?>,
      radarValues: <?php echo json_encode($radarValues); ?>,
      invSecLabels: <?php echo json_encode($invSecLabels, JSON_UNESCAPED_UNICODE); ?>,
      invSecValores: <?php echo json_encode($invSecValores); ?>,
      totalPactados: <?php echo (int)$totalCompromisosPactados; ?>,
      totalCumplidos: <?php echo (int)$totalCompromisosCumplidos; ?>,
      ranking: <?php echo json_encode($ranking); ?>,
      secColorMap: <?php echo json_encode($secColorMap); ?>,
      proyectosConSec: <?php echo json_encode($proyectosConSecretaria, JSON_UNESCAPED_UNICODE); ?>,
      visitasList: <?php echo json_encode($visitasList, JSON_UNESCAPED_UNICODE); ?>
    };
  </script>
  <script src="<?php echo Util::versionar('./admin/js/dahsboard_alcaldias.js'); ?>"></script>

  <script>
    $(window).on('load', function() { $('.loader-bg').fadeOut('slow', function() { $(this).remove(); }); });
    setTimeout(function() { $('.loader-bg').fadeOut('slow', function() { $(this).remove(); }); }, 2000);
  </script>
</body>
</html>
