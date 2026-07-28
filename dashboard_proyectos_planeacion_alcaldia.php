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
<body>
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <style>
    :root{
      --radius-xl:22px; --shadow-mid: 0 22px 70px rgba(0,0,0,.34); --safe-top: 96px;
    }
    .pcoded-content{ padding: calc(var(--safe-top) + 16px) 16px 18px !important; }
    @media(min-width:768px){ :root{ --safe-top: 112px; } .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; } }
    @media(min-width:1200px){ :root{ --safe-top: 120px; } .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; } }

    .btn-brutal{
      border-radius:14px !important; padding:.62rem 1.05rem !important;
      font-weight:1000 !important; display:inline-flex; align-items:center; gap:8px;
      box-shadow:0 14px 34px rgba(0,0,0,.25); color:#fff !important;
    }
    .btn-brutal.btn-sm{ padding:.32rem .55rem !important; border-radius:10px !important; font-size:11px !important; }
    .btn-primary.btn-brutal{ background:linear-gradient(135deg,#3b82f6,#4f46e5) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-info.btn-brutal{ background:linear-gradient(135deg,#38bdf8,#0ea5e9) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-secondary.btn-brutal{ background:rgba(255,255,255,.09) !important; border:1px solid rgba(255,255,255,.17) !important; }

    .table-wrap{ display:flex; justify-content:center; padding:8px 0 2px; }
    .table-shell{
      width:min(100%,1520px);
      background: rgba(255,255,255,.06) !important;
      border-radius:24px; overflow:hidden;
      border:1px solid rgba(255,255,255,.12);
      box-shadow:var(--shadow-mid);
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

    .kpi-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:16px; }
    .kpi-card{
      border-radius:18px; padding:1rem 1.1rem;
      background:rgba(0,0,0,.16); border:1px solid rgba(255,255,255,.10);
    }
    .kpi-label{ font-size:.72rem; opacity:.7; font-weight:1000; text-transform:uppercase; letter-spacing:.08em; color:#fff; }
    .kpi-value{ font-size:1.6rem; font-weight:1000; margin-top:.25rem; color:#fff; }

    .form-section{
      background: rgba(255,255,255,.06) !important;
      border-radius: 22px !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      padding: 16px !important;
      margin-bottom: 14px;
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
      color:#fff !important; background: linear-gradient(135deg, #203e5c, #2f3f6e) !important;
      text-transform:uppercase; font-size:10px !important; text-align:center; padding:8px 6px !important;
      border-color:rgba(255,255,255,.06) !important;
    }
    .hist-table tbody td{
      color:rgba(255,255,255,.86) !important; border-top:1px solid rgba(255,255,255,.06) !important;
      vertical-align:middle; padding:8px 6px !important; font-weight:700 !important;
    }
    .hist-table tbody tr:nth-child(even) td{ background:rgba(255,255,255,.03) !important; }
    .help-muted{ color: rgba(255,255,255,.55) !important; font-size:.82rem !important; font-weight:800 !important; }
  </style>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h5 class="m-b-10">Dashboard Planeación Alcaldía</h5>
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
                <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="table-wrap">
        <div class="table-shell">
          <div class="table-shell__top">
            <div>
              <div class="table-shell__eyebrow">Indicadores</div>
              <h3 class="table-shell__title">Proyectos Planeación Alcaldía</h3>
              <div class="table-shell__subtitle">
                Alcance: <?= h($s['scope'] ?? '') ?>
                <?php if (($s['scope'] ?? '') === 'all'): ?> · vista global (todos los municipios)<?php endif; ?>
              </div>
            </div>
          </div>
          <div class="table-shell__body">

            <div class="kpi-grid">
              <div class="kpi-card"><div class="kpi-label">Total</div><div class="kpi-value"><?= $total ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Enviado</div><div class="kpi-value" style="color:#fbbf24"><?= $enviados ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Aprobado</div><div class="kpi-value" style="color:#34d399"><?= $aprobados ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Rechazado</div><div class="kpi-value" style="color:#ef4444"><?= $rechazados ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Valor total</div><div class="kpi-value" style="font-size:1.15rem">$ <?= number_format($valor, 0, ',', '.') ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Sin gestión &gt; <?= (int)($s['dias_umbral'] ?? 7) ?>d</div><div class="kpi-value"><?= (int)($s['sin_gestion'] ?? 0) ?></div></div>
              <div class="kpi-card"><div class="kpi-label">Reenviados</div><div class="kpi-value"><?= (int)($s['reenvios'] ?? 0) ?></div></div>
            </div>

            <div class="row">
              <div class="col-lg-6">
                <div class="form-section">
                  <h6>Mayor retraso (Enviado)</h6>
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
                <div class="form-section">
                  <h6>Por municipio</h6>
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
