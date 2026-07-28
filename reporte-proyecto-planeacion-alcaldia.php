<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
require_once './admin/classes/Authorization.php';
require_once './admin/classes/Ingreso_proyectos_secretarias.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$modo = ($_GET['modo'] ?? '') === 'gestionar' ? 'gestionar' : 'detalle';

if ($id <= 0) {
    echo "<script>alert('Proyecto no especificado'); location.href='proyectos_planeacion_alcaldia.php';</script>";
    return;
}

$guard = Proyectos_Secretarias::assertPuedeVerProyecto($id);
if (!$guard['ok']) {
    echo "<script>alert(" . json_encode($guard['message']) . "); location.href='proyectos_planeacion_alcaldia.php';</script>";
    return;
}

if (!Authorization::can('proyectos.alcaldias.planeacion.detail')
    && !Authorization::can('proyectos.alcaldias.planeacion.view')
    && !Authorization::can('secretarias.proyectos.view')) {
    echo "<script>alert('Sin permiso para ver detalle'); location.href='proyectos_planeacion_alcaldia.php';</script>";
    return;
}

$det = Proyectos_Secretarias::getDetallesProyecto($id);
if (empty($det['output']['valid'])) {
    echo "<script>alert('Proyecto no encontrado'); location.href='proyectos_planeacion_alcaldia.php';</script>";
    return;
}
$p = $det['output']['response'];
$logsResp = Proyectos_Secretarias::obtenerLogsProyecto($id);
$logs = $logsResp['output']['response'] ?? [];
$adjuntosGestion = Proyectos_Secretarias::getGestionAdjuntos($id);

$estado = $p['estado_proyecto'] ?? '';
$canManage = Authorization::can('proyectos.alcaldias.planeacion.manage')
    || Authorization::can('secretarias.proyectos.approve');
$canReopen = Authorization::can('proyectos.alcaldias.planeacion.reopen');
$mostrarGestion = ($modo === 'gestionar' && $canManage && $estado === 'Enviado');

function h($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
function docUrl($path) {
    if (!$path) {
        return '';
    }
    $fn = basename($path);
    if (strpos($path, 'proyectos_planeacion_gestion') !== false) {
        return 'uploads/proyectos_planeacion_gestion/' . $fn;
    }
    return 'uploads/proyectos_secretarias/' . $fn;
}

$badgeCls = 'badge-secondary-soft';
if ($estado === 'Enviado') {
    $badgeCls = 'badge-warning-soft';
} elseif ($estado === 'Rechazado') {
    $badgeCls = 'badge-danger-soft';
} elseif ($estado === 'Aprobado') {
    $badgeCls = 'badge-success-soft';
}

$secretarias = array_filter(array_map('trim', explode(',', (string)($p['secretaria'] ?? $p['nombre_secretaria'] ?? ''))));
$metas = array_filter(array_map('trim', explode(',', (string)($p['meta_relacionada'] ?? $p['nombre_meta'] ?? ''))));
?>
<body>
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <style>
    :root{
      --nav-blue:#20427F; --nav-blue-2:#132b52; --nav-blue-3:#2e58a8;
      --radius-xl:22px; --radius-lg:16px; --radius-md:14px;
      --shadow-soft: 0 14px 40px rgba(0,0,0,.28); --shadow-mid: 0 22px 70px rgba(0,0,0,.34);
      --safe-top: 96px;
    }
    html, body{ overflow-x: hidden !important; }
    .pcoded-content{ padding: calc(var(--safe-top) + 16px) 16px 18px !important; }
    @media(min-width:768px){ :root{ --safe-top: 112px; } .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; } }
    @media(min-width:1200px){ :root{ --safe-top: 120px; } .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; } }

    .btn-brutal{
      border-radius:14px !important; padding:.62rem 1.05rem !important;
      font-weight:1000 !important; letter-spacing:.2px;
      box-shadow:0 14px 34px rgba(0,0,0,.25);
      transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
      display:inline-flex; align-items:center; gap:8px; white-space:nowrap;
      color:#fff !important;
    }
    .btn-brutal.btn-sm{ padding:.32rem .55rem !important; border-radius:10px !important; gap:4px; font-size:11px !important; box-shadow:0 8px 18px rgba(0,0,0,.18); }
    .btn-brutal:hover{ transform:translateY(-1px); filter:brightness(1.04); box-shadow:0 18px 40px rgba(0,0,0,.28); }
    .btn-primary.btn-brutal{ background:linear-gradient(135deg,#3b82f6,#4f46e5) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-danger.btn-brutal{ background:linear-gradient(135deg,#ef4444,#b91c1c) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-secondary.btn-brutal{ background:rgba(255,255,255,.09) !important; border:1px solid rgba(255,255,255,.17) !important; }
    .btn-info.btn-brutal{ background:linear-gradient(135deg,#38bdf8,#0ea5e9) !important; border:1px solid rgba(255,255,255,.14) !important; }
    .btn-warning.btn-brutal{ background:linear-gradient(135deg,#f6c23e,#f59e0b) !important; border:1px solid rgba(255,255,255,.14) !important; color:#111827 !important; }
    .btn-success.btn-brutal{ background:linear-gradient(135deg,#0f766e,#16a34a) !important; border:1px solid rgba(255,255,255,.14) !important; }

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
      padding:20px 24px 16px;
      border-bottom:1px solid rgba(255,255,255,.10);
      background:rgba(0,0,0,.14);
    }
    .table-shell__eyebrow{
      display:inline-flex; align-items:center; gap:8px; margin-bottom:6px;
      color:rgba(255,255,255,.7); font-size:11px; font-weight:1000;
      letter-spacing:.14em; text-transform:uppercase;
    }
    .table-shell__eyebrow:before{
      content:""; width:9px; height:9px; border-radius:999px;
      background:linear-gradient(135deg,#22c1ff,#20427F);
      box-shadow:0 0 0 5px rgba(34,193,255,.12);
    }
    .table-shell__title{ margin:0; color:#fff; font-size:1.3rem; font-weight:1000; letter-spacing:-.02em; }
    .table-shell__subtitle{ margin-top:4px; color:rgba(255,255,255,.6); font-size:.92rem; line-height:1.45; }
    .table-shell__badge{
      display:inline-flex; align-items:center; justify-content:center;
      min-width:92px; padding:.7rem 1rem; border-radius:16px;
      background:linear-gradient(135deg,#203e5c,#2f3f6e); color:#fff;
      font-size:.78rem; font-weight:1000; letter-spacing:.06em; text-transform:uppercase;
      box-shadow:0 16px 36px rgba(32,62,92,.20);
    }
    .table-shell__body{ padding:18px 18px 22px; }

    .form-section{
      background: rgba(255,255,255,.06) !important;
      border-radius: var(--radius-xl) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      padding: 20px !important;
      margin-bottom: 16px;
    }
    .form-section h6{
      color:#fff !important; font-weight:1000 !important; letter-spacing:.04em;
      text-transform:uppercase; font-size:.78rem; margin-bottom:12px;
      display:flex; align-items:center; gap:8px;
    }
    .form-section h6:before{
      content:""; width:8px; height:8px; border-radius:999px;
      background:linear-gradient(135deg,#22c1ff,#20427F);
      box-shadow:0 0 0 4px rgba(34,193,255,.12);
    }
    .meta-grid{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap:12px;
    }
    .meta-tile{
      background:rgba(0,0,0,.16);
      border:1px solid rgba(255,255,255,.10);
      border-radius:16px;
      padding:12px 14px;
    }
    .meta-tile__label{
      color:rgba(255,255,255,.55); font-size:10px; font-weight:1000;
      letter-spacing:.12em; text-transform:uppercase; margin-bottom:4px;
    }
    .meta-tile__value{
      color:#fff; font-weight:800; font-size:.95rem; word-break:break-word;
    }
    .rc-text-block{
      white-space:pre-wrap; word-break:break-word; overflow-wrap:anywhere;
      color:rgba(255,255,255,.88); font-weight:700; line-height:1.5;
      background:rgba(0,0,0,.14);
      border:1px solid rgba(255,255,255,.08);
      border-radius:14px;
      padding:14px 16px;
    }
    .brand-band{
      display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;
      padding:16px 18px; margin-bottom:16px;
      border-radius:18px;
      background: linear-gradient(135deg, rgba(32,62,92,.55), rgba(47,63,110,.35));
      border:1px solid rgba(255,255,255,.12);
    }
    .brand-band__titles{ text-align:right; }
    .brand-band__titles h4{
      margin:0; color:#fff; font-weight:1000; letter-spacing:.04em; text-transform:uppercase; font-size:1.05rem;
    }
    .brand-band__titles .sub{
      color:rgba(255,255,255,.65); font-size:.8rem; font-weight:800; margin-top:4px;
    }

    .badge{ border-radius:999px !important; padding:.25rem .5rem !important; font-weight:1000 !important; letter-spacing:.2px; border:1px solid rgba(255,255,255,.12); font-size:10.5px !important; }
    .badge-warning-soft{ background:rgba(245,158,11,.25) !important; color:#fbbf24 !important; }
    .badge-success-soft{ background:rgba(22,163,74,.20) !important; color:#34d399 !important; }
    .badge-danger-soft{ background:rgba(220,38,38,.20) !important; color:#ef4444 !important; }
    .badge-secondary-soft{ background:rgba(148,163,184,.18) !important; color:#94a3b8 !important; }

    .sec-pill{
      display:inline-flex; align-items:center; gap:5px; padding:3px 7px;
      border-radius:8px; font-weight:800 !important; font-size:10.5px !important;
      border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.06);
      color:rgba(255,255,255,.86); white-space:nowrap; margin:0 4px 4px 0;
    }
    .sec-dot{ flex-shrink:0; width:6px; height:6px; border-radius:50%; background:linear-gradient(135deg,#60a5fa,#4f46e5); }
    .meta-pill{ background:rgba(52,211,153,.12); border-color:rgba(52,211,153,.20); color:#34d399; }
    .meta-dot{ flex-shrink:0; width:6px; height:6px; border-radius:50%; background:linear-gradient(135deg,#34d399,#15803d); }

    .pdf-pill{
      display:inline-flex; align-items:center; gap:5px; padding:6px 10px; border-radius:10px;
      border:1px solid rgba(239,68,68,.25); background:rgba(239,68,68,.10);
      color:#fca5a5; font-weight:700; font-size:11px;
      text-decoration:none; margin:0 6px 6px 0; transition:background .15s;
    }
    .pdf-pill:hover{ background:rgba(239,68,68,.18); color:#fca5a5; text-decoration:none; }
    .pdf-pill .pdf-icon{
      flex-shrink:0; width:18px; height:18px;
      background:linear-gradient(135deg,#ef4444,#b91c1c); border-radius:4px;
      display:flex; align-items:center; justify-content:center;
      color:#fff; font-size:.65rem;
    }
    .foto-pill{
      border-color:rgba(56,189,248,.30); background:rgba(56,189,248,.12); color:#7dd3fc;
    }
    .foto-pill .pdf-icon{ background:linear-gradient(135deg,#38bdf8,#0ea5e9); }

    .log-entry{
      background:rgba(255,255,255,.06) !important;
      border:1px solid rgba(255,255,255,.10) !important;
      border-radius:14px !important;
      padding:12px 14px;
      margin-bottom:10px;
    }
    .log-entry__head{
      display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:8px; margin-bottom:6px;
    }
    .log-entry__obs{
      color:rgba(255,255,255,.82); font-weight:700; font-size:.9rem;
      white-space:pre-wrap; word-break:break-word;
    }
    .log-entry__meta{ color:rgba(255,255,255,.55); font-size:11px; font-weight:800; }

    .hist-table{ width:100%; margin:0; font-size:12px; }
    .hist-table thead th{
      color:#fff !important;
      background: linear-gradient(135deg, #203e5c, #2f3f6e) !important;
      text-transform:uppercase; letter-spacing:.1px;
      font-size:10px !important; white-space:nowrap;
      text-align:center; vertical-align:middle !important;
      padding:8px 6px !important;
      border-color:rgba(255,255,255,.06) !important;
    }
    .hist-table tbody td{
      color:rgba(255,255,255,.86) !important;
      background:transparent !important;
      border-top:1px solid rgba(255,255,255,.06) !important;
      vertical-align:top; padding:8px 6px !important;
      font-weight:700 !important;
    }
    .hist-table tbody tr:nth-child(even) td{ background:rgba(255,255,255,.03) !important; }
    .hist-wrap{
      border-radius:18px; border:1px solid rgba(255,255,255,.10); overflow:auto;
    }

    .help-muted{ color: rgba(255,255,255,.6) !important; font-size:.82rem !important; font-weight:800 !important; margin-top:.35rem !important; }
    .file-pro{ padding:.65rem .75rem; border-radius:var(--radius-md); border:1px dashed rgba(255,255,255,.22); background:rgba(255,255,255,.06); }
    .label-strong{ color:rgba(255,255,255,.78) !important; font-weight:900 !important; font-size:.82rem; margin-bottom:.35rem; display:block; }

    .foto-preview{
      max-width:100%; max-height:320px; border-radius:16px;
      border:1px solid rgba(255,255,255,.12);
      box-shadow:0 14px 34px rgba(0,0,0,.28);
    }

    @media (max-width:576px){
      .table-shell__top{ padding:16px; }
      .table-shell__body{ padding:12px; }
      .brand-band__titles{ text-align:left; }
      .btn-brutal{ width:100% !important; justify-content:center !important; }
      .header-actions{ width:100%; flex-direction:column; }
    }
  </style>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                <h5 class="m-b-10">Detalle Proyecto Planeación</h5>
                <div class="d-flex align-items-center header-actions" style="gap:8px; flex-wrap:wrap;">
                  <?php if ($canManage && $estado === 'Enviado' && !$mostrarGestion): ?>
                    <a class="btn btn-success btn-brutal btn-sm" href="reporte-proyecto-planeacion-alcaldia.php?id=<?= $id ?>&modo=gestionar">
                      <i class="feather icon-check-square"></i> Gestionar
                    </a>
                  <?php endif; ?>
                  <?php if ($canReopen && $estado === 'Aprobado'): ?>
                    <button type="button" class="btn btn-warning btn-brutal btn-sm" id="btnReabrirDetalle">
                      <i class="feather icon-refresh-cw"></i> Reabrir
                    </button>
                  <?php endif; ?>
                  <a href="proyectos_planeacion_alcaldia.php" class="btn btn-secondary btn-brutal btn-sm">
                    <i class="feather icon-list"></i> Listado
                  </a>
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="proyectos_planeacion_alcaldia.php">Proyectos Planeación</a></li>
                <li class="breadcrumb-item"><a href="#!">Detalle #<?= $id ?></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12">
          <div class="table-wrap">
            <div class="table-shell">
              <div class="table-shell__top">
                <div>
                  <div class="table-shell__eyebrow">Banco de proyectos</div>
                  <h3 class="table-shell__title">Proyecto de Planeación Alcaldía</h3>
                  <div class="table-shell__subtitle">
                    Consulta datos, adjuntos e historial.
                    <?php if ($estado === 'Aprobado'): ?>
                      <span class="badge badge-success-soft ml-1">Cerrado</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="table-shell__badge">N° <?= $id ?></div>
              </div>

              <div class="table-shell__body">

                <div class="brand-band">
                  <div><?php include 'admin/include/generinc_brand_logo.php'; ?></div>
                  <div class="brand-band__titles">
                    <h4>Gobernación de Santander</h4>
                    <div class="sub">República de Colombia · Planeación Alcaldía</div>
                    <div class="mt-2">
                      <span class="badge <?= $badgeCls ?>"><?= h($estado) ?></span>
                    </div>
                  </div>
                </div>

                <div class="form-section">
                  <h6>Resumen</h6>
                  <div class="meta-grid">
                    <div class="meta-tile">
                      <div class="meta-tile__label">Fecha</div>
                      <div class="meta-tile__value"><?= h($p['fecha'] ?? '—') ?></div>
                    </div>
                    <div class="meta-tile">
                      <div class="meta-tile__label">Municipio</div>
                      <div class="meta-tile__value"><?= h($p['municipio'] ?? $p['nombre_municipio'] ?? '—') ?></div>
                    </div>
                    <div class="meta-tile">
                      <div class="meta-tile__label">Valor</div>
                      <div class="meta-tile__value">$ <?= number_format((float)($p['valor_proyecto'] ?? 0), 0, ',', '.') ?></div>
                    </div>
                    <div class="meta-tile">
                      <div class="meta-tile__label">BPIN</div>
                      <div class="meta-tile__value"><?= h(($p['bpin'] ?? '') !== '' ? $p['bpin'] : '—') ?></div>
                    </div>
                  </div>
                </div>

                <div class="form-section">
                  <h6>Proyecto</h6>
                  <div class="rc-text-block"><?= h($p['proyecto'] ?? '—') ?></div>
                </div>

                <div class="form-section">
                  <h6>Secretarías y metas PDD</h6>
                  <div class="mb-2">
                    <?php if (empty($secretarias)): ?>
                      <span class="help-muted">Sin secretarías</span>
                    <?php else: foreach ($secretarias as $sec): ?>
                      <span class="sec-pill"><span class="sec-dot"></span><?= h($sec) ?></span>
                    <?php endforeach; endif; ?>
                  </div>
                  <div>
                    <?php if (empty($metas)): ?>
                      <span class="help-muted">Sin metas</span>
                    <?php else: foreach ($metas as $meta): ?>
                      <span class="sec-pill meta-pill"><span class="meta-dot"></span><?= h($meta) ?></span>
                    <?php endforeach; endif; ?>
                  </div>
                </div>

                <div class="form-section">
                  <h6>Observaciones</h6>
                  <div class="rc-text-block"><?= h(($p['observaciones'] ?? '') !== '' ? $p['observaciones'] : '—') ?></div>
                </div>

                <?php if (!empty($p['gestion_nota']) || !empty($p['secretario_planeacion'])): ?>
                  <div class="form-section">
                    <h6>Última nota de gestión</h6>
                    <div class="rc-text-block"><?= h($p['gestion_nota'] ?? $p['secretario_planeacion'] ?? '') ?></div>
                  </div>
                <?php endif; ?>

                <div class="form-section">
                  <h6>Adjuntos del proyecto</h6>
                  <div class="d-flex flex-wrap align-items-start">
                    <?php if (!empty($p['foto2'])): ?>
                      <div class="w-100 mb-3">
                        <img class="foto-preview" src="<?= h(docUrl($p['foto2'])) ?>" alt="Foto del proyecto">
                      </div>
                      <a class="pdf-pill foto-pill" target="_blank" href="<?= h(docUrl($p['foto2'])) ?>">
                        <span class="pdf-icon"><i class="feather icon-image"></i></span> Ver foto
                      </a>
                    <?php endif; ?>
                    <?php
                      $docKeys = ['documento2','documento3','documento4','documento5','documento6'];
                      $docNum = 1;
                      $hasDocs = false;
                      foreach ($docKeys as $dk):
                        if (empty($p[$dk])) continue;
                        $hasDocs = true;
                        $fn = basename($p[$dk]);
                    ?>
                      <a class="pdf-pill" target="_blank" href="<?= h(docUrl($p[$dk])) ?>" title="<?= h($fn) ?>">
                        <span class="pdf-icon"><i class="feather icon-file-text"></i></span> PDF <?= $docNum ?>
                      </a>
                    <?php $docNum++; endforeach; ?>
                    <?php if (empty($p['foto2']) && !$hasDocs): ?>
                      <span class="help-muted">Sin adjuntos</span>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if (!empty($adjuntosGestion)): ?>
                  <div class="form-section">
                    <h6>Adjuntos de gestión</h6>
                    <div class="d-flex flex-wrap">
                      <?php foreach ($adjuntosGestion as $adj): ?>
                        <a class="pdf-pill foto-pill" target="_blank" href="<?= h($adj['ruta']) ?>">
                          <span class="pdf-icon"><i class="feather icon-paperclip"></i></span>
                          <?= h($adj['nombre_original'] ?: basename($adj['ruta'])) ?>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if ($mostrarGestion): ?>
                  <div class="form-section" id="panelGestion">
                    <h6>Gestionar proyecto</h6>
                    <p class="help-muted mb-3">Al aprobar, el proyecto queda cerrado. El BPIN es obligatorio solo en aprobación.</p>
                    <form id="formGestionProyecto" enctype="multipart/form-data">
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="label-strong">Decisión *</label>
                          <select name="decision" id="gestion_decision" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="Aprobar">Aprobar (cierra el proyecto)</option>
                            <option value="Rechazar">Rechazar</option>
                          </select>
                        </div>
                        <div class="col-md-6 mb-3" id="wrapBpin" style="display:none;">
                          <label class="label-strong">Código BPIN *</label>
                          <input type="text" name="bpin" id="gestion_bpin" class="form-control" maxlength="80" placeholder="Ingrese BPIN">
                        </div>
                        <div class="col-12 mb-3">
                          <label class="label-strong">Nota de gestión *</label>
                          <textarea name="nota" id="gestion_nota" class="form-control" rows="4" required placeholder="Escriba la nota de gestión"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                          <label class="label-strong">Adjuntos (PDF / imagen)</label>
                          <div class="file-pro">
                            <input type="file" name="gestion_adjuntos[]" class="form-control-file" multiple accept=".pdf,image/*">
                          </div>
                          <div class="help-muted">Puede adjuntar uno o varios archivos de soporte.</div>
                        </div>
                        <div class="col-12 d-flex flex-wrap" style="gap:8px;">
                          <button type="submit" class="btn btn-primary btn-brutal">
                            <i class="feather icon-save"></i> Guardar gestión
                          </button>
                          <a href="reporte-proyecto-planeacion-alcaldia.php?id=<?= $id ?>" class="btn btn-secondary btn-brutal">
                            Cancelar
                          </a>
                        </div>
                      </div>
                    </form>
                  </div>
                <?php endif; ?>

                <div class="form-section mb-0">
                  <h6>Historial de acciones</h6>
                  <?php if (empty($logs)): ?>
                    <div class="help-muted">Sin historial registrado.</div>
                  <?php else: ?>
                    <div class="d-none d-md-block hist-wrap">
                      <table class="hist-table">
                        <thead>
                          <tr>
                            <th>Fecha</th>
                            <th>Acción</th>
                            <th>Usuario</th>
                            <th>Observación</th>
                            <th>Doc</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($logs as $log):
                            $logBadge = 'badge-secondary-soft';
                            $acc = $log['accion'] ?? '';
                            if ($acc === 'Rechazado') $logBadge = 'badge-danger-soft';
                            elseif ($acc === 'Aprobado') $logBadge = 'badge-success-soft';
                            elseif ($acc === 'Enviado' || $acc === 'Reenviado/Editado' || $acc === 'Reabierto' || $acc === 'Creado') $logBadge = 'badge-warning-soft';
                          ?>
                            <tr>
                              <td class="text-center" style="white-space:nowrap;"><?= h($log['dtcreated'] ?? '') ?></td>
                              <td class="text-center"><span class="badge <?= $logBadge ?>"><?= h($acc) ?></span></td>
                              <td><?= h($log['usuario'] ?? '—') ?></td>
                              <td style="white-space:pre-wrap; word-break:break-word;"><?= h($log['observacion'] ?? '') ?></td>
                              <td class="text-center">
                                <?php if (!empty($log['documento_ruta'])): ?>
                                  <a class="pdf-pill" style="margin:0;" target="_blank" href="<?= h(docUrl($log['documento_ruta'])) ?>">
                                    <span class="pdf-icon"><i class="feather icon-download"></i></span>
                                  </a>
                                <?php else: ?>—<?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>

                    <div class="d-md-none">
                      <?php foreach ($logs as $log):
                        $logBadge = 'badge-secondary-soft';
                        $acc = $log['accion'] ?? '';
                        if ($acc === 'Rechazado') $logBadge = 'badge-danger-soft';
                        elseif ($acc === 'Aprobado') $logBadge = 'badge-success-soft';
                        elseif ($acc === 'Enviado' || $acc === 'Reenviado/Editado' || $acc === 'Reabierto' || $acc === 'Creado') $logBadge = 'badge-warning-soft';
                      ?>
                        <div class="log-entry">
                          <div class="log-entry__head">
                            <span class="badge <?= $logBadge ?>"><?= h($acc) ?></span>
                            <span class="log-entry__meta"><?= h($log['dtcreated'] ?? '') ?></span>
                          </div>
                          <div class="log-entry__obs"><?= h($log['observacion'] ?? '') ?></div>
                          <div class="log-entry__meta mt-1">
                            <i class="feather icon-user"></i> <?= h($log['usuario'] ?? '—') ?>
                            <?php if (!empty($log['documento_ruta'])): ?>
                              · <a href="<?= h(docUrl($log['documento_ruta'])) ?>" target="_blank">Ver doc</a>
                            <?php endif; ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
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
  <script>
    $('#gestion_decision').on('change', function () {
      $('#wrapBpin').toggle($(this).val() === 'Aprobar');
    });

    $('#formGestionProyecto').on('submit', function (e) {
      e.preventDefault();
      var decision = $('#gestion_decision').val();
      var nota = ($('#gestion_nota').val() || '').trim();
      var bpin = ($('#gestion_bpin').val() || '').trim();
      if (!decision) { Swal.fire('Atención', 'Seleccione una decisión', 'warning'); return; }
      if (!nota) { Swal.fire('Atención', 'La nota es obligatoria', 'warning'); return; }
      if (decision === 'Aprobar' && !bpin) { Swal.fire('Atención', 'BPIN obligatorio al aprobar', 'warning'); return; }

      var fd = new FormData(this);
      fd.append('op', 'gestionar_proyecto_planeacion');

      $.ajax({
        url: 'admin/ajax/rqst.php',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (resp) {
          if (resp.output && resp.output.valid) {
            Swal.fire('OK', resp.output.response.content || 'Guardado', 'success').then(function () {
              location.href = 'reporte-proyecto-planeacion-alcaldia.php?id=<?= $id ?>';
            });
          } else {
            Swal.fire('Error', (resp.output && resp.output.response && resp.output.response.content) || 'Error', 'error');
          }
        },
        error: function () { Swal.fire('Error', 'No se pudo conectar', 'error'); }
      });
    });

    $('#btnReabrirDetalle').on('click', function () {
      Swal.fire({
        title: 'Reabrir proyecto',
        input: 'textarea',
        inputValue: 'Proyecto reabierto para nueva gestión.',
        showCancelButton: true,
        confirmButtonText: 'Reabrir',
        cancelButtonText: 'Cancelar'
      }).then(function (r) {
        if (!r.isConfirmed) return;
        $.post('admin/ajax/rqst.php', {
          op: 'reabrir_proyecto_planeacion',
          id: <?= $id ?>,
          nota: r.value
        }, null, 'json').done(function (resp) {
          if (resp.output && resp.output.valid) {
            Swal.fire('OK', resp.output.response.content, 'success').then(function () { location.reload(); });
          } else {
            Swal.fire('Error', (resp.output && resp.output.response && resp.output.response.content) || 'Error', 'error');
          }
        });
      });
    });
  </script>
</body>
</html>
