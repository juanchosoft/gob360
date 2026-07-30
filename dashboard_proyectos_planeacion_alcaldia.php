<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
require_once './admin/classes/Authorization.php';
require_once './admin/classes/Ingreso_proyectos_secretarias.php';

if (!Authorization::can('proyectos.alcaldias.planeacion.dashboard')) {
    echo "<script>alert('Sin permiso de dashboard'); location.href='proyectos_planeacion_alcaldia.php';</script>";
    return;
}

$statsResp = Proyectos_Secretarias::getDashboardStats(['dias_sin_gestion' => 7]);
$s = $statsResp['output']['response'] ?? [];
$porEstado = $s['por_estado'] ?? [];
$enviados = (int)($porEstado['Enviado']['total'] ?? 0);
$aprobados = (int)($porEstado['Aprobado']['total'] ?? 0);
$rechazados = (int)($porEstado['Rechazado']['total'] ?? 0);
$total = (int)($s['total'] ?? 0);
$valor = (float)($s['valor_total'] ?? 0);

function h($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<link rel="stylesheet" href="assets/css/dashboard_planeacion_alcaldia_gob360.css">

<body class="gob360-planning-dashboard">
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-planning-hero" aria-label="Dashboard de Planeación Municipal GOB360">
        <div class="g360-planning-hero__grid">

          <aside class="g360-planning-brand">
            <span class="g360-planning-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-planning-brand__logo"
            >

            <span class="g360-planning-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-planning-brand__status">
              <span></span>
              Dashboard operativo
            </div>
          </aside>

          <div class="g360-planning-hero__content">
            <div class="g360-planning-hero__top">
              <div>
                <div class="g360-planning-hero__eyebrow">
                  <i class="feather icon-bar-chart-2"></i>
                  Planeación Municipal
                </div>

                <h1 class="g360-planning-hero__title">
                  Dashboard de Proyectos
                </h1>

                <p class="g360-planning-hero__description">
                  Monitorea proyectos radicados, estados de gestión, inversión
                  consolidada, retrasos, reenvíos y desempeño por municipio desde
                  una vista ejecutiva.
                </p>
              </div>

              <div class="g360-planning-hero__actions">
                <a
                  href="proyectos_planeacion_alcaldia.php"
                  class="g360-hero-button g360-hero-button--primary"
                >
                  <i class="feather icon-list"></i>
                  Ver proyectos
                </a>

                <div class="g360-planning-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-planning-summary">
              <article>
                <span class="g360-planning-summary__icon">
                  <i class="feather icon-folder"></i>
                </span>

                <div>
                  <small>Total de proyectos</small>
                  <strong><?= number_format($total, 0, ',', '.') ?></strong>
                  <p>Proyectos dentro del alcance actual</p>
                </div>
              </article>

              <article>
                <span class="g360-planning-summary__icon g360-planning-summary__icon--money">
                  <i class="feather icon-dollar-sign"></i>
                </span>

                <div>
                  <small>Valor consolidado</small>
                  <strong>$ <?= number_format($valor, 0, ',', '.') ?></strong>
                  <p>Inversión total registrada</p>
                </div>
              </article>

              <article>
                <span class="g360-planning-summary__icon g360-planning-summary__icon--approved">
                  <i class="feather icon-check-circle"></i>
                </span>

                <div>
                  <small>Proyectos aprobados</small>
                  <strong><?= number_format($aprobados, 0, ',', '.') ?></strong>
                  <p>
                    <?= $total > 0 ? number_format(($aprobados / $total) * 100, 1, ',', '.') : '0,0' ?>%
                    del consolidado
                  </p>
                </div>
              </article>

              <article>
                <span class="g360-planning-summary__icon g360-planning-summary__icon--pending">
                  <i class="feather icon-clock"></i>
                </span>

                <div>
                  <small>Pendientes de gestión</small>
                  <strong><?= number_format($enviados, 0, ',', '.') ?></strong>
                  <p>
                    <?= number_format((int)($s['sin_gestion'] ?? 0), 0, ',', '.') ?>
                    superan el umbral
                  </p>
                </div>
              </article>
            </div>

            <div class="g360-planning-capabilities">
              <span>
                <i class="feather icon-globe"></i>
                Alcance: <?= h($s['scope'] ?? 'Sin definir') ?>
              </span>

              <span>
                <i class="feather icon-calendar"></i>
                Umbral: <?= (int)($s['dias_umbral'] ?? 7) ?> días
              </span>

              <span>
                <i class="feather icon-refresh-cw"></i>
                <?= number_format((int)($s['reenvios'] ?? 0), 0, ',', '.') ?> reenvíos
              </span>

              <span>
                <i class="feather icon-shield"></i>
                Acceso autorizado
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="table-wrap">
        <div class="table-shell g360-planning-workspace">
          <div class="table-shell__top">
            <div>
              <div class="table-shell__eyebrow">Analítica institucional</div>
              <h3 class="table-shell__title">Control ejecutivo de proyectos</h3>
              <div class="table-shell__subtitle">
                Indicadores generales y comportamiento territorial.
                <?php if (($s['scope'] ?? '') === 'all'): ?>
                  Vista global habilitada para todos los municipios.
                <?php endif; ?>
              </div>
            </div>

            <div class="table-shell__badge">
              <i class="feather icon-shield"></i>
              GOB360
            </div>
          </div>

          <div class="table-shell__body">

            <section class="g360-kpi-section">
              <div class="g360-section-heading">
                <span class="g360-section-heading__icon">
                  <i class="feather icon-activity"></i>
                </span>

                <div>
                  <span>Resumen operativo</span>
                  <h4>Estado general de los proyectos</h4>
                  <p>Consolidado de radicación, gestión, aprobación y alertas.</p>
                </div>
              </div>

              <div class="kpi-grid">
                <article class="kpi-card">
                  <span class="g360-kpi-icon">
                    <i class="feather icon-folder"></i>
                  </span>
                  <div class="kpi-label">Total</div>
                  <div class="kpi-value"><?= number_format($total, 0, ',', '.') ?></div>
                  <div class="g360-kpi-note">Proyectos registrados</div>
                </article>

                <article class="kpi-card g360-kpi-card--pending">
                  <span class="g360-kpi-icon">
                    <i class="feather icon-send"></i>
                  </span>
                  <div class="kpi-label">Enviados</div>
                  <div class="kpi-value"><?= number_format($enviados, 0, ',', '.') ?></div>
                  <div class="g360-kpi-note">Pendientes de decisión</div>
                </article>

                <article class="kpi-card g360-kpi-card--approved">
                  <span class="g360-kpi-icon">
                    <i class="feather icon-check-circle"></i>
                  </span>
                  <div class="kpi-label">Aprobados</div>
                  <div class="kpi-value"><?= number_format($aprobados, 0, ',', '.') ?></div>
                  <div class="g360-kpi-note">Proyectos cerrados</div>
                </article>

                <article class="kpi-card g360-kpi-card--rejected">
                  <span class="g360-kpi-icon">
                    <i class="feather icon-x-circle"></i>
                  </span>
                  <div class="kpi-label">Rechazados</div>
                  <div class="kpi-value"><?= number_format($rechazados, 0, ',', '.') ?></div>
                  <div class="g360-kpi-note">Requieren ajustes</div>
                </article>

                <article class="kpi-card g360-kpi-card--money">
                  <span class="g360-kpi-icon">
                    <i class="feather icon-dollar-sign"></i>
                  </span>
                  <div class="kpi-label">Valor total</div>
                  <div class="kpi-value g360-kpi-value--money">
                    $ <?= number_format($valor, 0, ',', '.') ?>
                  </div>
                  <div class="g360-kpi-note">Inversión consolidada</div>
                </article>

                <article class="kpi-card g360-kpi-card--alert">
                  <span class="g360-kpi-icon">
                    <i class="feather icon-alert-triangle"></i>
                  </span>
                  <div class="kpi-label">
                    Sin gestión &gt; <?= (int)($s['dias_umbral'] ?? 7) ?> días
                  </div>
                  <div class="kpi-value">
                    <?= number_format((int)($s['sin_gestion'] ?? 0), 0, ',', '.') ?>
                  </div>
                  <div class="g360-kpi-note">Requieren atención</div>
                </article>

                <article class="kpi-card g360-kpi-card--resend">
                  <span class="g360-kpi-icon">
                    <i class="feather icon-refresh-cw"></i>
                  </span>
                  <div class="kpi-label">Reenviados</div>
                  <div class="kpi-value">
                    <?= number_format((int)($s['reenvios'] ?? 0), 0, ',', '.') ?>
                  </div>
                  <div class="g360-kpi-note">Proyectos ajustados</div>
                </article>
              </div>
            </section>

            <section class="g360-analysis-section">
              <div class="g360-section-heading">
                <span class="g360-section-heading__icon g360-section-heading__icon--territory">
                  <i class="feather icon-map"></i>
                </span>

                <div>
                  <span>Análisis territorial</span>
                  <h4>Retrasos y comportamiento municipal</h4>
                  <p>Identifica proyectos críticos y distribución por municipio.</p>
                </div>
              </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="form-section g360-data-panel g360-data-panel--delay">
                  <div class="g360-data-panel__header">
                    <span class="g360-data-panel__icon">
                      <i class="feather icon-clock"></i>
                    </span>

                    <div>
                      <span>Alerta operativa</span>
                      <h6>Mayor retraso en proyectos enviados</h6>
                      <p>Ordena la atención de proyectos que llevan más días sin decisión.</p>
                    </div>
                  </div>
                  <div class="hist-wrap">
                    <table class="hist-table">
                      <thead><tr><th>Proyecto</th><th>Municipio</th><th>Días</th><th></th></tr></thead>
                      <tbody>
                        <?php foreach (($s['retrasos'] ?? []) as $r): ?>
                          <tr>
                            <td><?= h(mb_strimwidth($r['proyecto'] ?? '', 0, 42, '…')) ?></td>
                            <td><?= h($r['municipio'] ?? '') ?></td>
                            <td class="text-center"><?= (int)($r['dias'] ?? 0) ?></td>
                            <td class="text-center">
                              <a class="btn btn-info btn-brutal btn-sm" href="reporte-proyecto-planeacion-alcaldia.php?id=<?= (int)$r['id'] ?>">
                                <i class="feather icon-eye"></i>
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (empty($s['retrasos'])): ?>
                          <tr><td colspan="4" class="help-muted text-center">Sin proyectos pendientes.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-section g360-data-panel g360-data-panel--municipality">
                  <div class="g360-data-panel__header">
                    <span class="g360-data-panel__icon">
                      <i class="feather icon-map-pin"></i>
                    </span>

                    <div>
                      <span>Consolidado territorial</span>
                      <h6>Proyectos por municipio</h6>
                      <p>Compara total, enviados, aprobados y rechazados.</p>
                    </div>
                  </div>
                  <div class="hist-wrap">
                    <table class="hist-table">
                      <thead><tr><th>Municipio</th><th>Total</th><th>Env</th><th>Apr</th><th>Rec</th></tr></thead>
                      <tbody>
                        <?php foreach (($s['por_municipio'] ?? []) as $m): ?>
                          <tr>
                            <td><?= h($m['municipio'] ?? '') ?></td>
                            <td class="text-center"><?= (int)($m['total'] ?? 0) ?></td>
                            <td class="text-center"><?= (int)($m['enviados'] ?? 0) ?></td>
                            <td class="text-center"><?= (int)($m['aprobados'] ?? 0) ?></td>
                            <td class="text-center"><?= (int)($m['rechazados'] ?? 0) ?></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (empty($s['por_municipio'])): ?>
                          <tr><td colspan="5" class="help-muted text-center">Sin datos.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            </section>

          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include './admin/include/footer.php'; ?>
  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
</body>
</html>
