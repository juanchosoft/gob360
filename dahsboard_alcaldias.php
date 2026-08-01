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
$completados = $totalCompromisosCumplidos;
$pctCumpl = $proyectos['total'] > 0 ? round(($completados / $proyectos['total']) * 100) : 0;
$pctEjecucion = $proyectos['total'] > 0
    ? round(($proyectos['por_estado']['ejecucion'] / $proyectos['total']) * 100)
    : 0;
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
  <link rel="stylesheet" href="assets/css/dashboard_alcaldia_gob360_premium.css">

</head>
<body class="gob360-mayor-dashboard-page">
  <div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>
  <div class="pcoded-main-container">
    <div class="pcoded-content">
      <section class="g360-mayor-hero" aria-label="Dashboard municipal GOB360">
        <div class="g360-mayor-hero__grid">

          <aside class="g360-mayor-brand">
            <span class="g360-mayor-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-mayor-brand__logo"
            >

            <span class="g360-mayor-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-mayor-brand__status">
              <span></span>
              Información municipal en línea
            </div>
          </aside>

          <div class="g360-mayor-hero__content">
            <div class="g360-mayor-hero__top">
              <div>
                <div class="g360-mayor-hero__eyebrow">
                  <i class="feather icon-monitor"></i>
                  Acción Unificada Municipal
                </div>

                <h1 class="g360-mayor-hero__title">
                  Dashboard de <?php echo safe($nombreMunicipio); ?>
                </h1>

                <p class="g360-mayor-hero__description">
                  Consolida proyectos, inversión, secretarías, visitas,
                  compromisos y cobertura territorial para apoyar la toma
                  de decisiones de la administración municipal.
                </p>
              </div>

              <div class="g360-mayor-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="window.location.reload()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar tablero
                </button>

                <div class="g360-mayor-date">
                  <i class="feather icon-calendar"></i>
                  <?php echo date('d/m/Y H:i'); ?> · Colombia
                </div>
              </div>
            </div>

            <div class="g360-mayor-summary">
              <article>
                <span class="g360-mayor-summary__icon">
                  <i class="feather icon-folder"></i>
                </span>

                <div>
                  <small>Proyectos</small>
                  <strong><?php echo number_format($proyectos['total']); ?></strong>
                  <p>Registros municipales consolidados</p>
                </div>
              </article>

              <article>
                <span class="g360-mayor-summary__icon g360-mayor-summary__icon--investment">
                  <i class="feather icon-dollar-sign"></i>
                </span>

                <div>
                  <small>Inversión</small>
                  <strong>$<?php echo number_format($valorInversion / 1000000, 1, ',', '.'); ?>M</strong>
                  <p>Valor total en millones de pesos</p>
                </div>
              </article>

              <article>
                <span class="g360-mayor-summary__icon g360-mayor-summary__icon--secretariats">
                  <i class="feather icon-briefcase"></i>
                </span>

                <div>
                  <small>Secretarías</small>
                  <strong><?php echo number_format($totalSecretarias); ?></strong>
                  <p>Dependencias municipales registradas</p>
                </div>
              </article>

              <article>
                <span class="g360-mayor-summary__icon g360-mayor-summary__icon--visits">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Visitas</small>
                  <strong><?php echo number_format($visitas['total']); ?></strong>
                  <p><?php echo number_format($visitas['veredas_visitadas']); ?> veredas cubiertas</p>
                </div>
              </article>
            </div>

            <div class="g360-mayor-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-bar-chart-2"></i>
                Analítica de proyectos
              </span>

              <span>
                <i class="feather icon-pie-chart"></i>
                Distribución por secretaría
              </span>

              <span>
                <i class="feather icon-map"></i>
                Cobertura territorial
              </span>

              <span>
                <i class="feather icon-activity"></i>
                Balance estratégico
              </span>

              <span>
                <i class="feather icon-alert-triangle"></i>
                Alertas ejecutivas
              </span>
            </div>
          </div>

        </div>
      </section>

      <section class="au-grid md-4 g360-kpi-grid" aria-label="Indicadores principales">
        <div class="au-card g360-kpi-card g360-kpi-card--investment">
          <div class="au-card-h"><div><p class="t">Inversión Total</p><p class="s">Valor consolidado</p></div><span class="g360-card-symbol"><i class="feather icon-dollar-sign"></i></span></div>
          <div class="au-card-b kpi">
            <div class="kpi-row"><div class="label">Total proyectos</div><div class="trend <?php echo $proyectos['total'] > 0 ? 'up' : ''; ?>"><?php echo $proyectos['total'] > 0 ? '▲' : ''; ?> <?php echo number_format($proyectos['total']); ?></div></div>
            <div class="value">$<?php echo number_format($valorInversion / 1000000, 1, ',', '.'); ?>M</div>
            <div class="kpi-line"><span style="width:78%"></span></div>
            <div class="hint"><?php echo number_format($proyectos['total']); ?> proyectos registrados</div>
          </div>
        </div>
        <div class="au-card g360-kpi-card g360-kpi-card--execution">
          <div class="au-card-h"><div><p class="t">Proyectos en Ejecución</p><p class="s">Total general</p></div><span class="g360-card-symbol"><i class="feather icon-activity"></i></span></div>
          <div class="au-card-b kpi">
            <div class="kpi-row"><div class="label">En ejecución</div><div class="trend <?php echo $proyectos['por_estado']['ejecucion'] > 0 ? 'up' : ''; ?>"><?php echo $proyectos['por_estado']['ejecucion'] > 0 ? '▲' : ''; ?> <?php echo $proyectos['por_estado']['ejecucion']; ?></div></div>
            <div class="value"><?php echo number_format($proyectos['por_estado']['ejecucion']); ?></div>
            <div class="kpi-line"><span style="width:64%"></span></div>
            <div class="hint"><?php echo $pctEjecucion; ?>% del total</div>
          </div>
        </div>
        <div class="au-card g360-kpi-card g360-kpi-card--completed">
          <div class="au-card-h"><div><p class="t">Completados</p><p class="s">Terminados + Entregados</p></div><span class="g360-card-symbol"><i class="feather icon-check-circle"></i></span></div>
          <div class="au-card-b kpi">
            <div class="kpi-row"><div class="label">Total</div><div class="trend <?php echo $completados > 0 ? 'up' : ''; ?>"><?php echo $completados > 0 ? '▲' : ''; ?> <?php echo number_format($completados); ?></div></div>
            <div class="value"><?php echo number_format($completados); ?></div>
            
            <div class="kpi-line"><span style="width:<?php echo $pctCumpl; ?>%"></span></div>
            <div class="hint">Cumplimiento: <?php echo $pctCumpl; ?>%</div>
          </div>
        </div>
        <div class="au-card g360-kpi-card g360-kpi-card--visits" id="cardVisitas">
          <div class="au-card-h"><div><p class="t">Visitas y Cobertura</p><p class="s">Veredas visitadas — <span style="color:#60a5fa;font-weight:900;">🖱️ clic para ver detalle</span></p></div><span class="g360-card-symbol"><i class="feather icon-map-pin"></i></span></div>
          <div class="au-card-b kpi">
            <div class="kpi-row"><div class="label">Visitas / Veredas</div><div class="trend <?php echo $visitas['total'] > 0 ? 'up' : ''; ?>"><?php echo $visitas['total'] > 0 ? '▲' : ''; ?> <?php echo number_format($visitas['total']); ?></div></div>
            <div class="value"><?php echo number_format($visitas['veredas_visitadas']); ?><span style="font-size:1rem;color:var(--muted);">/<?php echo number_format($visitas['veredas_totales']); ?></span></div>
            <div class="kpi-line"><span style="width:<?php echo $visitas['veredas_totales'] > 0 ? round(($visitas['veredas_visitadas'] / $visitas['veredas_totales']) * 100) : 0; ?>%"></span></div>
            <div class="hint"><?php echo $visitas['veredas_restantes']; ?> veredas pendientes</div>
          </div>
        </div>
      </section>

      <section class="au-section au-grid md-2 g360-dashboard-row g360-dashboard-row--top">
        <div class="au-card g360-chart-card">
          <div class="au-card-h"><div><p class="t">Top Proyectos por Inversión</p><p class="s">Los 5 proyectos de mayor valor</p></div><span class="g360-chart-type"><i class="feather icon-award"></i> Top 5</span></div>
          <div class="au-card-b"><div class="chart-wrap g360-chart-wrap--top-projects"><canvas id="chartTopProyectos"></canvas></div></div>
        </div>
        <div class="au-card g360-chart-card">
          <div class="au-card-h"><div><p class="t">Top Secretarías con mejor Inversión</p><p class="s">Distribución por secretaría</p></div><span class="g360-chart-type"><i class="feather icon-pie-chart"></i> Distribución</span></div>
          <div class="au-card-b">
            <div class="chart-wrap g360-chart-wrap--ranking" style="--chart-dynamic-height:<?php echo max(240, count($ranking) * 60); ?>px;">
              <canvas id="chartTopSec"></canvas>
            </div>
          </div>
        </div>
      </section>

      <section class="au-section g360-dashboard-section">
        <div class="au-card g360-chart-card">
          <div class="au-card-h"><div><p class="t">Proyectos por Secretaría</p><p class="s">Total de proyectos por secretaría municipal</p></div><span class="g360-chart-type"><i class="feather icon-bar-chart-2"></i> Barras</span></div>
          <div class="au-card-b"><div class="chart-wrap"><canvas id="barPlan"></canvas></div></div>
        </div>
      </section>

      <section class="au-section g360-dashboard-section">
        <div class="au-card g360-chart-card">
          <div class="au-card-h"><div><p class="t">Distribución de Proyectos por Secretaría</p><p class="s">Cantidad de proyectos por secretaría municipal — <span style="color:#60a5fa;font-weight:900;">🖱️ clic en la gráfica para ver proyectos</span></p></div><span class="g360-chart-type"><i class="feather icon-pie-chart"></i> Distribución</span></div>
          <div class="au-card-b">
            <div class="g360-pie-layout">
              <div class="chart-wrap g360-chart-wrap--pie"><canvas id="pieSecretarias"></canvas></div>
              <div class="au-breakdown" id="pieBreakdown">
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
      </section>

      <section class="au-section au-grid md-2 g360-dashboard-row">
        <div class="au-card g360-chart-card">
          <div class="au-card-h"><div><p class="t">Balance Estratégico</p><p class="s">Indicadores calculados del municipio</p></div><span class="g360-chart-type"><i class="feather icon-activity"></i> Radar</span></div>
          <div class="au-card-b">
            <div class="chart-wrap g360-chart-wrap--radar"><canvas id="radarBalance"></canvas></div>
            <div class="g360-metrics-grid">
              <div class="g360-metrics-grid__title"><i class="feather icon-activity"></i> Métricas del radar</div>
              <div class="g360-metric-item"><span style="color:#60a5fa;">●</span> Inversión: <?php echo $invPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Valor total en Millones COP</span></div>
              <div class="g360-metric-item"><span style="color:#a78bfa;">●</span> Cobertura: <?php echo $secPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Secretarías con proyectos</span></div>
              <div class="g360-metric-item"><span style="color:#34d399;">●</span> Impacto: <?php echo $impPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Valor promedio por proyecto</span></div>
              <div class="g360-metric-item"><span style="color:#fbbf24;">●</span> Riesgo: <?php echo $riesPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Según compromisos registrados</span></div>
              <div class="g360-metric-item"><span style="color:#fb7185;">●</span> Velocidad: <?php echo $velPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Cantidad de proyectos</span></div>
              <div class="g360-metric-item"><span style="color:#22d3ee;">●</span> Gestión: <?php echo $gesPct; ?><br><span style="font-size:10px;color:#64748b;font-weight:700;">Proyectos en ejecución vs total</span></div>
            </div>
          </div>
        </div>
        <div class="au-card g360-chart-card">
          <div class="au-card-h"><div><p class="t">Cumplimiento Global</p><p class="s">Proyectos completados vs total</p></div><span class="g360-chart-type"><i class="feather icon-disc"></i> Cumplimiento</span></div>
          <div class="au-card-b"><div class="chart-wrap g360-chart-wrap--doughnut"><canvas id="doughMeta"></canvas></div></div>
        </div>
      </section>

      <!-- Inversión por Secretaría (ancho completo) -->
      <section class="au-section g360-dashboard-section">
        <div class="au-card g360-chart-card">
          <div class="au-card-h"><div><p class="t">Inversión por Secretaría</p><p class="s">Valor total en Millones $ por secretaría municipal</p></div><span class="g360-chart-type"><i class="feather icon-bar-chart-2"></i> Barras</span></div>
          <div class="au-card-b"><div class="chart-inv-wrap"><canvas id="chartInvSec"></canvas></div></div>
        </div>
      </section>

      <!-- Resumen Ejecutivo (ancho completo) -->
      <section class="au-section g360-dashboard-section">
        <div class="au-card g360-chart-card">
          <div class="au-card-h">
            <div>
              <p class="t">Resumen Ejecutivo</p>
              <p class="s">Indicadores del municipio</p>
            </div>
            <span class="g360-chart-type"><i class="feather icon-list"></i> Resumen</span>
          </div>
          <div class="au-card-b">
            <div class="table-responsive g360-executive-table">
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
            <div class="g360-level-legend">
              <div class="g360-level-legend__title"><i class="feather icon-info"></i> Leyenda de niveles</div>
              <div class="g360-level-item"><span style="color:#34d399;">●</span> BAJA<br><span style="font-size:10px;color:#64748b;font-weight:700;">Indicador positivo (≥50% o >0)</span></div>
              <div class="g360-level-item"><span style="color:#fbbf24;">●</span> MEDIA<br><span style="font-size:10px;color:#64748b;font-weight:700;">Indicador parcial (≥25%)</span></div>
              <div class="g360-level-item"><span style="color:#ef4444;">●</span> ALTA<br><span style="font-size:10px;color:#64748b;font-weight:700;">Requiere atención (&lt;25%)</span></div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Modal Visitas -->
  <div class="modal fade" id="modalVisitas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content g360-dashboard-modal">
        <div class="modal-header">
          <div class="g360-modal-heading">
            <span class="g360-modal-heading__icon">
              <i class="feather icon-map-pin"></i>
            </span>

            <div>
              <small>Cobertura territorial</small>
              <h5 class="modal-title">Visitas realizadas</h5>
            </div>
          </div>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body" id="modalVisitasBody"></div>
      </div>
    </div>
  </div>

  <!-- Modal Proyectos por Secretaría -->
  <div class="modal fade" id="modalPieSecretarias" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content g360-dashboard-modal">
        <div class="modal-header">
          <div class="g360-modal-heading">
            <span class="g360-modal-heading__icon">
              <i class="feather icon-pie-chart"></i>
            </span>

            <div>
              <small>Proyectos por dependencia</small>
              <h5 class="modal-title" id="modalPieTitle"></h5>
            </div>
          </div>
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
