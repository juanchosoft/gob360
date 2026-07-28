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
<body>
  <div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <style>
    :root{ --radius-xl:22px; --shadow-mid: 0 22px 70px rgba(0,0,0,.34); --safe-top: 96px; }
    .pcoded-content{ padding: calc(var(--safe-top) + 16px) 16px 18px !important; }
    @media(min-width:768px){ :root{ --safe-top: 112px; } .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; } }
    @media(min-width:1200px){ :root{ --safe-top: 120px; } .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; } }

    .btn-brutal{
      border-radius:14px !important; padding:.55rem 1rem !important; font-weight:1000 !important;
      display:inline-flex; align-items:center; gap:8px; color:#fff !important;
      box-shadow:0 14px 34px rgba(0,0,0,.25);
    }
    .btn-brutal.btn-sm{ padding:.32rem .55rem !important; border-radius:10px !important; font-size:11px !important; }
    .btn-primary.btn-brutal{ background:linear-gradient(135deg,#3b82f6,#4f46e5) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-info.btn-brutal{ background:linear-gradient(135deg,#38bdf8,#0ea5e9) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-secondary.btn-brutal{ background:rgba(255,255,255,.09) !important; border:1px solid rgba(255,255,255,.17) !important; }

    .table-wrap{ display:flex; justify-content:center; padding:8px 0 2px; }
    .table-shell{
      width:min(100%,1520px); background:rgba(255,255,255,.06) !important;
      border-radius:24px; overflow:hidden; border:1px solid rgba(255,255,255,.12); box-shadow:var(--shadow-mid);
    }
    .table-shell__top{
      display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap;
      padding:20px 24px 16px; border-bottom:1px solid rgba(255,255,255,.10); background:rgba(0,0,0,.14);
    }
    .table-shell__eyebrow{
      display:inline-flex; align-items:center; gap:8px; margin-bottom:6px;
      color:rgba(255,255,255,.7); font-size:11px; font-weight:1000; letter-spacing:.14em; text-transform:uppercase;
    }
    .table-shell__eyebrow:before{
      content:""; width:9px; height:9px; border-radius:999px;
      background:linear-gradient(135deg,#22c1ff,#20427F); box-shadow:0 0 0 5px rgba(34,193,255,.12);
    }
    .table-shell__title{ margin:0; color:#fff; font-size:1.3rem; font-weight:1000; }
    .table-shell__subtitle{ margin-top:4px; color:rgba(255,255,255,.6); font-size:.92rem; }
    .table-shell__body{ padding:18px; }

    .kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:16px; }
    .kpi-card{
      border-radius:18px; padding:1rem 1.1rem;
      background:rgba(0,0,0,.16); border:1px solid rgba(255,255,255,.10);
    }
    .kpi-label{ font-size:.72rem; opacity:.7; font-weight:1000; text-transform:uppercase; letter-spacing:.08em; color:#fff; }
    .kpi-value{ font-size:1.55rem; font-weight:1000; margin-top:.25rem; color:#fff; }

    .form-section{
      background: rgba(255,255,255,.06) !important; border-radius:22px !important;
      border:1px solid rgba(255,255,255,.12) !important; padding:16px !important; margin-bottom:14px;
    }
    .form-section h6{
      color:#fff !important; font-weight:1000 !important; letter-spacing:.04em;
      text-transform:uppercase; font-size:.78rem; margin-bottom:12px;
      display:flex; align-items:center; gap:8px;
    }
    .form-section h6:before{
      content:""; width:8px; height:8px; border-radius:999px;
      background:linear-gradient(135deg,#22c1ff,#20427F); box-shadow:0 0 0 4px rgba(34,193,255,.12);
    }
    .hist-wrap{ border-radius:18px; border:1px solid rgba(255,255,255,.10); overflow:auto; }
    .hist-table{ width:100%; margin:0; font-size:12px; }
    .hist-table thead th{
      color:#fff !important; background:linear-gradient(135deg,#203e5c,#2f3f6e) !important;
      text-transform:uppercase; font-size:10px !important; text-align:center; padding:8px 6px !important;
      border-color:rgba(255,255,255,.06) !important;
    }
    .hist-table tbody td{
      color:rgba(255,255,255,.86) !important; border-top:1px solid rgba(255,255,255,.06) !important;
      vertical-align:middle; padding:8px 6px !important; font-weight:700 !important;
    }
    .hist-table tbody tr:nth-child(even) td{ background:rgba(255,255,255,.03) !important; }
    .chart-box{ position:relative; height:260px; }
    .filters-row{ display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
    .filters-row label{ color:rgba(255,255,255,.7); font-weight:900; font-size:.78rem; display:block; margin-bottom:4px; }
    .filters-row .filtro-field{ flex:1 1 180px; min-width:160px; }
    .filters-row .filtro-field--users{ flex:2 1 280px; }
    .filters-row .form-control{
      border-radius:12px !important; border:1px solid rgba(255,255,255,.14) !important;
      background:rgba(255,255,255,.06) !important; color:#fff !important; min-height:40px;
    }
    .filters-row .select2-container{ width:100% !important; }
    .filters-row .select2-container--default .select2-selection--multiple,
    .filters-row .select2-container--default .select2-selection--single{
      border-radius:12px !important; border:1px solid rgba(255,255,255,.14) !important;
      background:rgba(255,255,255,.06) !important; min-height:40px; color:#fff !important;
    }
    .filters-row .select2-container--default .select2-selection--multiple .select2-selection__choice{
      background:rgba(59,130,246,.35) !important; border:1px solid rgba(255,255,255,.18) !important;
      color:#fff !important; border-radius:8px !important; font-weight:700; font-size:11px;
    }
    .filters-row .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
      color:rgba(255,255,255,.85) !important; margin-right:4px;
    }
    .filters-row .select2-container--default .select2-selection--multiple .select2-selection__rendered{ padding:4px 8px; color:#fff; }
    .filters-row .select2-container--default .select2-selection--single .select2-selection__rendered{
      color:#fff !important; line-height:38px; padding-left:12px;
    }
    .filters-row .select2-container--default .select2-selection--single .select2-selection__arrow{ height:38px; }
    .select2-container--open .select2-dropdown{
      background:#1a2332 !important; border:1px solid rgba(255,255,255,.16) !important; color:#fff;
    }
    .select2-container--default .select2-results__option{ color:rgba(255,255,255,.9); }
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option--highlighted[aria-selected=true]{
      background:#3b82f6 !important; color:#fff !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field{
      background:rgba(255,255,255,.08) !important; border:1px solid rgba(255,255,255,.2) !important; color:#fff !important;
    }
    .badge{ border-radius:999px !important; padding:.25rem .5rem !important; font-weight:1000 !important; border:1px solid rgba(255,255,255,.12); font-size:10.5px !important; }
    .badge-warning-soft{ background:rgba(245,158,11,.25) !important; color:#fbbf24 !important; }
    .badge-success-soft{ background:rgba(22,163,74,.20) !important; color:#34d399 !important; }
    .badge-danger-soft{ background:rgba(220,38,38,.20) !important; color:#ef4444 !important; }
    .badge-secondary-soft{ background:rgba(148,163,184,.18) !important; color:#94a3b8 !important; }
  </style>

  <div class="pcoded-main-container">
    <div class="pcoded-content">
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h5 class="m-b-10">Informes de gestión — Planeación</h5>
                <div class="d-flex" style="gap:8px;">
                  <a href="proyectos_planeacion_alcaldia.php" class="btn btn-primary btn-brutal btn-sm">
                    <i class="feather icon-list"></i> Listado
                  </a>
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="proyectos_planeacion_alcaldia.php">Planeación</a></li>
                <li class="breadcrumb-item"><a href="#!">Informes</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="table-wrap">
        <div class="table-shell">
          <div class="table-shell__top">
            <div>
              <div class="table-shell__eyebrow">Fiscalización / supervisión</div>
              <h3 class="table-shell__title">Panel de informes de gestión</h3>
              <div class="table-shell__subtitle">
                Alcance: <?= h($scope) ?>
                · Rango <?= h($desde) ?> → <?= h($hasta) ?>
              </div>
            </div>
          </div>
          <div class="table-shell__body">

            <div class="form-section">
              <h6>Filtros</h6>
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
            </div>

            <div class="kpi-grid">
              <div class="kpi-card"><div class="kpi-label">Proyectos</div><div class="kpi-value"><?= (int)($kpis['total_proyectos'] ?? 0) ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Enviados</div><div class="kpi-value" style="color:#fbbf24"><?= (int)($kpis['enviados'] ?? 0) ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Aprobados</div><div class="kpi-value" style="color:#34d399"><?= (int)($kpis['aprobados'] ?? 0) ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Rechazados</div><div class="kpi-value" style="color:#ef4444"><?= (int)($kpis['rechazados'] ?? 0) ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Gestiones en rango</div><div class="kpi-value"><?= (int)($kpis['gestiones_rango'] ?? 0) ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Asignaciones activas</div><div class="kpi-value"><?= (int)($kpis['asignaciones_activas'] ?? 0) ?></div></div>
            </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="form-section">
                  <h6>Tendencia diaria</h6>
                  <div class="chart-box"><canvas id="chartTendencia"></canvas></div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="form-section">
                  <h6>Acciones en el rango</h6>
                  <div class="chart-box"><canvas id="chartAcciones"></canvas></div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6>Gestión por usuario</h6>
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
                      <tr><td colspan="6" class="text-center" style="color:rgba(255,255,255,.55)">Sin actividad en el rango.</td></tr>
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
            </div>

            <div class="form-section mb-0">
              <h6>Detalle de acciones (últimas 100)</h6>
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
                      <tr><td colspan="5" class="text-center" style="color:rgba(255,255,255,.55)">Sin registros.</td></tr>
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
                          <a href="reporte-proyecto-planeacion-alcaldia.php?id=<?= (int)($d['proyecto_id'] ?? 0) ?>" style="color:#7dd3fc;font-weight:800;">
                            #<?= (int)($d['proyecto_id'] ?? 0) ?> <?= h(mb_strimwidth($d['proyecto'] ?? '', 0, 40, '…')) ?>
                          </a>
                        </td>
                        <td style="white-space:pre-wrap;word-break:break-word;max-width:280px;"><?= h(mb_strimwidth($d['observacion'] ?? '', 0, 120, '…')) ?></td>
                      </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

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
