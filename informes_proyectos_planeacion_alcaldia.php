<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
require_once './admin/classes/Authorization.php';
require_once './admin/classes/Ingreso_proyectos_secretarias.php';

if (!Authorization::can('proyectos.alcaldias.planeacion.informes')) {
    echo "<script>alert('Sin permiso de informes'); location.href='proyectos_planeacion_alcaldia.php';</script>";
    return;
}

function h($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$codigo_municipio_usuario = $_SESSION['session_user']['tbl_municipio_id'] ?? '';
$canManage = Authorization::can('proyectos.alcaldias.planeacion.manage')
    || Authorization::can('secretarias.proyectos.approve');
$canViewAllAlcaldia = Authorization::can('proyectos.alcaldias.planeacion.view_all_alcaldia');
$canAssign = Authorization::can('proyectos.alcaldias.planeacion.assign');

$mostrarFiltroMunicipio = Proyectos_Secretarias::isVistaDepartamental();
$mostrarFiltroUsuarios = $mostrarFiltroMunicipio || $canManage || $canViewAllAlcaldia || $canAssign
    || Authorization::can('proyectos.alcaldias.planeacion.informes');

$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
$hasta = $_GET['hasta'] ?? date('Y-m-d');
$filtroMunicipio = $_GET['filtro_municipio'] ?? '';
$filtroUsuarios = isset($_GET['filtro_usuarios']) ? (array)$_GET['filtro_usuarios'] : [];
$filtroUsuarios = array_values(array_filter(array_map('intval', $filtroUsuarios)));

$stats = Proyectos_Secretarias::getInformesGestion([
    'fecha_desde' => $desde,
    'fecha_hasta' => $hasta,
    'municipio_id' => $filtroMunicipio,
    'usuario_ids' => $filtroUsuarios,
]);
$r = $stats['output']['response'] ?? [];
$kpis = $r['kpis'] ?? [];
$porUsuario = $r['por_usuario'] ?? [];
$acciones = $r['acciones'] ?? [];
$tendencia = $r['tendencia'] ?? [];
$detalle = $r['detalle'] ?? [];
$scope = $r['scope'] ?? '';

// Opciones municipio (solo vista departamental)
$optionFiltroMunicipios = "<option value=''>Todos los municipios</option>";
if ($mostrarFiltroMunicipio) {
    $dbMun = new DbConection();
    $pdoMun = $dbMun->openConect();
    try {
        $depto = Util::getDepartamentoPrincipal();
        $stMun = $pdoMun->prepare(
            "SELECT codigo_muncipio, municipio FROM " . $dbMun->getTable('tbl_ciudades_accion_unificada') . "
             WHERE codigo_departamento = :d ORDER BY municipio"
        );
        $stMun->execute([':d' => $depto]);
        foreach ($stMun->fetchAll(PDO::FETCH_ASSOC) as $m) {
            $sel = ((string)$filtroMunicipio === (string)$m['codigo_muncipio']) ? 'selected' : '';
            $optionFiltroMunicipios .= "<option $sel value='".h($m['codigo_muncipio'])."'>".h($m['municipio'])."</option>";
        }
    } catch (Throwable $e) {
        // silencioso
    }
    $dbMun->closeConect();
}

// Opciones usuarios
$optionFiltroUsuarios = '';
if ($mostrarFiltroUsuarios) {
    $usuariosFiltroResp = Proyectos_Secretarias::getUsuariosFiltroListado([
        'municipio_id' => $mostrarFiltroMunicipio ? $filtroMunicipio : ($codigo_municipio_usuario ?: ''),
    ]);
    $usuariosFiltro = ($usuariosFiltroResp['output']['valid'] ?? false) ? ($usuariosFiltroResp['output']['response'] ?? []) : [];
    foreach ($usuariosFiltro as $u) {
        $uid = (int)($u['id'] ?? 0);
        $label = trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''));
        if ($label === '') $label = $u['nickname'] ?? ('Usuario #' . $uid);
        if (!empty($u['nombre_municipio']) && $mostrarFiltroMunicipio) {
            $label .= ' — ' . $u['nombre_municipio'];
        }
        $sel = in_array($uid, $filtroUsuarios, true) ? 'selected' : '';
        $optionFiltroUsuarios .= "<option $sel value='{$uid}'>".h($label)."</option>";
    }
}
?>
<link rel="stylesheet" href="assets/css/informes_gestion_planeacion_gob360.css">

<body class="gob360-planning-reports">
  <div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  

  <div class="pcoded-main-container">
    <div class="pcoded-content">
      <section class="g360-reports-hero" aria-label="Informes de gestión de Planeación Municipal">
        <div class="g360-reports-hero__grid">

          <aside class="g360-reports-brand">
            <span class="g360-reports-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-reports-brand__logo"
            >

            <span class="g360-reports-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-reports-brand__status">
              <span></span>
              Informes habilitados
            </div>
          </aside>

          <div class="g360-reports-hero__content">
            <div class="g360-reports-hero__top">
              <div>
                <div class="g360-reports-hero__eyebrow">
                  <i class="feather icon-pie-chart"></i>
                  Planeación Municipal
                </div>

                <h1 class="g360-reports-hero__title">
                  Informes de Gestión
                </h1>

                <p class="g360-reports-hero__description">
                  Analiza el comportamiento de los proyectos, las decisiones tomadas,
                  la actividad por usuario y la trazabilidad institucional dentro del
                  rango de fechas seleccionado.
                </p>
              </div>

              <div class="g360-reports-hero__actions">
                <a
                  href="proyectos_planeacion_alcaldia.php"
                  class="g360-hero-button g360-hero-button--primary"
                >
                  <i class="feather icon-list"></i>
                  Ver proyectos
                </a>

                <div class="g360-reports-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-reports-summary">
              <article>
                <span class="g360-reports-summary__icon">
                  <i class="feather icon-folder"></i>
                </span>

                <div>
                  <small>Proyectos</small>
                  <strong><?= number_format((int)($kpis['total_proyectos'] ?? 0), 0, ',', '.') ?></strong>
                  <p>Incluidos en el informe</p>
                </div>
              </article>

              <article>
                <span class="g360-reports-summary__icon g360-reports-summary__icon--management">
                  <i class="feather icon-activity"></i>
                </span>

                <div>
                  <small>Gestiones realizadas</small>
                  <strong><?= number_format((int)($kpis['gestiones_rango'] ?? 0), 0, ',', '.') ?></strong>
                  <p>Actuaciones dentro del rango</p>
                </div>
              </article>

              <article>
                <span class="g360-reports-summary__icon g360-reports-summary__icon--approved">
                  <i class="feather icon-check-circle"></i>
                </span>

                <div>
                  <small>Aprobados</small>
                  <strong><?= number_format((int)($kpis['aprobados'] ?? 0), 0, ',', '.') ?></strong>
                  <p>Decisiones favorables</p>
                </div>
              </article>

              <article>
                <span class="g360-reports-summary__icon g360-reports-summary__icon--assignments">
                  <i class="feather icon-users"></i>
                </span>

                <div>
                  <small>Asignaciones activas</small>
                  <strong><?= number_format((int)($kpis['asignaciones_activas'] ?? 0), 0, ',', '.') ?></strong>
                  <p>Usuarios vinculados a proyectos</p>
                </div>
              </article>
            </div>

            <div class="g360-reports-capabilities">
              <span>
                <i class="feather icon-calendar"></i>
                <?= h($desde) ?> → <?= h($hasta) ?>
              </span>

              <span>
                <i class="feather icon-globe"></i>
                Alcance: <?= h($scope ?: 'Sin definir') ?>
              </span>

              <span>
                <i class="feather icon-users"></i>
                <?= number_format(count($porUsuario), 0, ',', '.') ?> usuarios con actividad
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
        <div class="table-shell g360-reports-workspace">
          <div class="table-shell__top">
            <div>
              <div class="table-shell__eyebrow">Analítica y supervisión</div>
              <h3 class="table-shell__title">Panel ejecutivo de gestión</h3>
              <div class="table-shell__subtitle">
                Indicadores, tendencias, desempeño de usuarios y trazabilidad.
              </div>
            </div>

            <div class="table-shell__badge">
              <i class="feather icon-shield"></i>
              GOB360
            </div>
          </div>

          <div class="table-shell__body">

            <section class="form-section g360-report-section g360-filter-section">
              <div class="g360-section-heading">
                <span class="g360-section-heading__icon">
                  <i class="feather icon-filter"></i>
                </span>

                <div>
                  <span>Consulta segmentada</span>
                  <h4>Filtros del informe</h4>
                  <p>Selecciona fechas, municipio y usuarios para actualizar todos los indicadores.</p>
                </div>
              </div>

              <form method="get" action="informes_proyectos_planeacion_alcaldia.php" id="formFiltrosInformes" class="filters-row">
                <div class="filtro-field">
                  <label for="desde">Desde</label>
                  <input type="date" name="desde" id="desde" class="form-control" value="<?= h($desde) ?>">
                </div>
                <div class="filtro-field">
                  <label for="hasta">Hasta</label>
                  <input type="date" name="hasta" id="hasta" class="form-control" value="<?= h($hasta) ?>">
                </div>
                <?php if ($mostrarFiltroMunicipio): ?>
                <div class="filtro-field">
                  <label for="filtro_municipio">Municipio</label>
                  <select name="filtro_municipio" id="filtro_municipio" class="form-control">
                    <?= $optionFiltroMunicipios ?>
                  </select>
                </div>
                <?php endif; ?>
                <?php if ($mostrarFiltroUsuarios): ?>
                <div class="filtro-field filtro-field--users">
                  <label for="filtro_usuarios">Usuario(s)</label>
                  <select name="filtro_usuarios[]" id="filtro_usuarios" class="form-control" multiple="multiple"
                          data-placeholder="Todos los usuarios">
                    <?= $optionFiltroUsuarios ?>
                  </select>
                </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-info btn-brutal btn-sm">
                  <i class="feather icon-filter"></i> Aplicar
                </button>
                <a href="informes_proyectos_planeacion_alcaldia.php" class="btn btn-secondary btn-brutal btn-sm">
                  <i class="feather icon-x"></i> Limpiar
                </a>
              </form>
            </section>

            <section class="g360-report-section g360-kpi-section">
              <div class="g360-section-heading">
                <span class="g360-section-heading__icon g360-section-heading__icon--kpi">
                  <i class="feather icon-activity"></i>
                </span>

                <div>
                  <span>Resumen operativo</span>
                  <h4>Indicadores del periodo</h4>
                  <p>Estado de proyectos, decisiones, gestiones y asignaciones.</p>
                </div>
              </div>

            <div class="kpi-grid">
              <article class="kpi-card">
                <span class="g360-kpi-icon">
                  <i class="feather icon-folder"></i>
                </span>
                <div class="kpi-label">Proyectos</div>
                <div class="kpi-value"><?= number_format((int)($kpis['total_proyectos'] ?? 0), 0, ',', '.') ?></div>
                <div class="g360-kpi-note">Incluidos en el rango</div>
              </article>

              <article class="kpi-card g360-kpi-card--pending">
                <span class="g360-kpi-icon">
                  <i class="feather icon-send"></i>
                </span>
                <div class="kpi-label">Enviados</div>
                <div class="kpi-value"><?= number_format((int)($kpis['enviados'] ?? 0), 0, ',', '.') ?></div>
                <div class="g360-kpi-note">Pendientes de decisión</div>
              </article>

              <article class="kpi-card g360-kpi-card--approved">
                <span class="g360-kpi-icon">
                  <i class="feather icon-check-circle"></i>
                </span>
                <div class="kpi-label">Aprobados</div>
                <div class="kpi-value"><?= number_format((int)($kpis['aprobados'] ?? 0), 0, ',', '.') ?></div>
                <div class="g360-kpi-note">Gestión favorable</div>
              </article>

              <article class="kpi-card g360-kpi-card--rejected">
                <span class="g360-kpi-icon">
                  <i class="feather icon-x-circle"></i>
                </span>
                <div class="kpi-label">Rechazados</div>
                <div class="kpi-value"><?= number_format((int)($kpis['rechazados'] ?? 0), 0, ',', '.') ?></div>
                <div class="g360-kpi-note">Requieren ajustes</div>
              </article>

              <article class="kpi-card g360-kpi-card--management">
                <span class="g360-kpi-icon">
                  <i class="feather icon-edit-3"></i>
                </span>
                <div class="kpi-label">Gestiones en rango</div>
                <div class="kpi-value"><?= number_format((int)($kpis['gestiones_rango'] ?? 0), 0, ',', '.') ?></div>
                <div class="g360-kpi-note">Actuaciones registradas</div>
              </article>

              <article class="kpi-card g360-kpi-card--assignments">
                <span class="g360-kpi-icon">
                  <i class="feather icon-users"></i>
                </span>
                <div class="kpi-label">Asignaciones activas</div>
                <div class="kpi-value"><?= number_format((int)($kpis['asignaciones_activas'] ?? 0), 0, ',', '.') ?></div>
                <div class="g360-kpi-note">Usuarios vinculados</div>
              </article>
            </div>
            </section>

            <section class="g360-report-section g360-charts-section">
              <div class="g360-section-heading">
                <span class="g360-section-heading__icon g360-section-heading__icon--charts">
                  <i class="feather icon-bar-chart-2"></i>
                </span>

                <div>
                  <span>Comportamiento temporal</span>
                  <h4>Tendencia y distribución de acciones</h4>
                  <p>Visualiza la evolución diaria y el peso de cada actuación institucional.</p>
                </div>
              </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="form-section g360-chart-panel">
                  <div class="g360-panel-heading">
                    <span class="g360-panel-heading__icon">
                      <i class="feather icon-trending-up"></i>
                    </span>

                    <div>
                      <span>Serie temporal</span>
                      <h6>Tendencia diaria</h6>
                      <p>Creados, aprobados y rechazados por fecha.</p>
                    </div>
                  </div>

                  <div class="chart-box">
                    <canvas id="chartTendencia"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-section g360-chart-panel">
                  <div class="g360-panel-heading">
                    <span class="g360-panel-heading__icon g360-panel-heading__icon--actions">
                      <i class="feather icon-pie-chart"></i>
                    </span>

                    <div>
                      <span>Distribución</span>
                      <h6>Acciones en el rango</h6>
                      <p>Participación de cada tipo de actuación registrada.</p>
                    </div>
                  </div>

                  <div class="chart-box">
                    <canvas id="chartAcciones"></canvas>
                  </div>
                </div>
              </div>
            </div>
            </section>

            <section class="form-section g360-report-section g360-users-section">
              <div class="g360-section-heading">
                <span class="g360-section-heading__icon g360-section-heading__icon--users">
                  <i class="feather icon-users"></i>
                </span>

                <div>
                  <span>Desempeño institucional</span>
                  <h4>Gestión por usuario</h4>
                  <p>Compara aprobaciones, rechazos, gestiones y acciones realizadas.</p>
                </div>
              </div>
              <div class="hist-wrap">
                <table class="hist-table">
                  <thead>
                    <tr>
                      <th>Usuario</th>
                      <th>Email</th>
                      <th>Aprobados</th>
                      <th>Rechazados</th>
                      <th>Gestiones</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($porUsuario)): ?>
                      <tr><td colspan="6" class="text-center help-muted">Sin actividad en el rango.</td></tr>
                    <?php else: foreach ($porUsuario as $u): ?>
                      <tr>
                        <td><?= h(trim($u['usuario'] ?? '') ?: 'Sistema') ?></td>
                        <td><?= h($u['nickname'] ?? '') ?></td>
                        <td class="text-center"><?= (int)($u['aprobados'] ?? 0) ?></td>
                        <td class="text-center"><?= (int)($u['rechazados'] ?? 0) ?></td>
                        <td class="text-center"><?= (int)($u['gestiones'] ?? 0) ?></td>
                        <td class="text-center"><?= (int)($u['acciones_total'] ?? 0) ?></td>
                      </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                </table>
              </div>
            </section>

            <section class="form-section g360-report-section g360-detail-section mb-0">
              <div class="g360-section-heading">
                <span class="g360-section-heading__icon g360-section-heading__icon--detail">
                  <i class="feather icon-clock"></i>
                </span>

                <div>
                  <span>Trazabilidad institucional</span>
                  <h4>Detalle de acciones</h4>
                  <p>Últimas 100 actuaciones registradas dentro del alcance consultado.</p>
                </div>
              </div>
              <div class="hist-wrap">
                <table class="hist-table">
                  <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Acción</th>
                      <th>Usuario</th>
                      <th>Proyecto</th>
                      <th>Observación</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($detalle)): ?>
                      <tr><td colspan="5" class="text-center help-muted">Sin registros.</td></tr>
                    <?php else: foreach ($detalle as $d):
                      $acc = $d['accion'] ?? '';
                      $badge = 'badge-secondary-soft';
                      if ($acc === 'Aprobado') $badge = 'badge-success-soft';
                      elseif ($acc === 'Rechazado') $badge = 'badge-danger-soft';
                      elseif (in_array($acc, ['Creado','Reenviado/Editado','Reabierto','Asignacion'], true)) $badge = 'badge-warning-soft';
                    ?>
                      <tr>
                        <td style="white-space:nowrap"><?= h($d['dtcreated'] ?? '') ?></td>
                        <td class="text-center"><span class="badge <?= $badge ?>"><?= h($acc) ?></span></td>
                        <td><?= h($d['usuario'] ?? '') ?></td>
                        <td>
                          <a href="reporte-proyecto-planeacion-alcaldia.php?id=<?= (int)($d['proyecto_id'] ?? 0) ?>" class="g360-project-link">
                            #<?= (int)($d['proyecto_id'] ?? 0) ?> <?= h(mb_strimwidth($d['proyecto'] ?? '', 0, 40, '…')) ?>
                          </a>
                        </td>
                        <td style="white-space:pre-wrap;word-break:break-word;max-width:280px;"><?= h(mb_strimwidth($d['observacion'] ?? '', 0, 120, '…')) ?></td>
                      </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                </table>
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    const mostrarFiltroMunicipio = <?= json_encode((bool)$mostrarFiltroMunicipio) ?>;
    const mostrarFiltroUsuarios = <?= json_encode((bool)$mostrarFiltroUsuarios) ?>;
    const tendencia = <?= json_encode($tendencia, JSON_UNESCAPED_UNICODE) ?>;
    const acciones = <?= json_encode($acciones, JSON_UNESCAPED_UNICODE) ?>;

    function initFiltrosInformesPlaneacion() {
      if (!mostrarFiltroUsuarios || typeof $.fn.select2 !== 'function') return;
      var $usuarios = $('#filtro_usuarios');
      if (!$usuarios.length) return;

      $usuarios.select2({
        width: '100%',
        placeholder: $usuarios.data('placeholder') || 'Todos los usuarios',
        allowClear: true,
        closeOnSelect: false
      });

      if (mostrarFiltroMunicipio) {
        $('#filtro_municipio').select2({
          width: '100%',
          placeholder: 'Todos los municipios',
          allowClear: true
        });

        $('#filtro_municipio').off('change.informesFiltro').on('change.informesFiltro', function () {
          var mun = $(this).val() || '';
          $.ajax({
            url: 'admin/ajax/rqst.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'planeacion_usuarios_filtro', municipio_id: mun },
            success: function (resp) {
              var rows = (resp && resp.output && resp.output.valid) ? (resp.output.response || []) : [];
              var selected = $usuarios.val() || [];
              $usuarios.empty();
              rows.forEach(function (u) {
                var uid = String(u.id);
                var label = ((u.nombre || '') + ' ' + (u.apellido || '')).trim();
                if (!label) label = u.nickname || ('Usuario #' + uid);
                if (u.nombre_municipio) label += ' — ' + u.nombre_municipio;
                $usuarios.append(new Option(label, uid, false, selected.indexOf(uid) !== -1));
              });
              $usuarios.trigger('change');
            }
          });
        });
      }
    }

    $(function () {
      initFiltrosInformesPlaneacion();
    });

    const labelsT = tendencia.map(r => r.dia);
    new Chart(document.getElementById('chartTendencia'), {
      type: 'line',
      data: {
        labels: labelsT,
        datasets: [
          { label: 'Aprobados', data: tendencia.map(r => +r.aprobados || 0), borderColor: '#34d399', backgroundColor: 'rgba(52,211,153,.15)', tension: .35, fill: true },
          { label: 'Rechazados', data: tendencia.map(r => +r.rechazados || 0), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.12)', tension: .35, fill: true },
          { label: 'Creados', data: tendencia.map(r => +r.creados || 0), borderColor: '#60a5fa', backgroundColor: 'rgba(96,165,250,.12)', tension: .35, fill: true }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#fff', font: { weight: '700' } } } },
        scales: {
          x: { ticks: { color: 'rgba(255,255,255,.65)' }, grid: { color: 'rgba(255,255,255,.06)' } },
          y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,.65)' }, grid: { color: 'rgba(255,255,255,.06)' } }
        }
      }
    });

    new Chart(document.getElementById('chartAcciones'), {
      type: 'doughnut',
      data: {
        labels: acciones.map(a => a.accion),
        datasets: [{
          data: acciones.map(a => +a.total || 0),
          backgroundColor: ['#60a5fa','#34d399','#ef4444','#fbbf24','#a78bfa','#22d3ee','#fb7185','#94a3b8']
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { color: '#fff', font: { weight: '700' } } } }
      }
    });
  </script>
</body>
</html>
