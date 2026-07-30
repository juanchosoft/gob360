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
<link rel="stylesheet" href="assets/css/detalle_proyecto_planeacion_alcaldia_gob360.css">

<body class="gob360-project-detail">
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-detail-hero" aria-label="Detalle del proyecto de Planeación Municipal">
        <div class="g360-detail-hero__grid">

          <aside class="g360-detail-brand">
            <span class="g360-detail-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-detail-brand__logo"
            >

            <span class="g360-detail-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-detail-brand__status">
              <span></span>
              Expediente disponible
            </div>
          </aside>

          <div class="g360-detail-hero__content">
            <div class="g360-detail-hero__top">
              <div>
                <div class="g360-detail-hero__eyebrow">
                  <i class="feather icon-file-text"></i>
                  Planeación Municipal · Proyecto N.° <?= $id ?>
                </div>

                <h1 class="g360-detail-hero__title">
                  Detalle y Gestión del Proyecto
                </h1>

                <p class="g360-detail-hero__description">
                  Consulta la información radicada, soportes, secretarías,
                  metas del Plan de Desarrollo, notas de gestión e historial
                  completo del proyecto.
                </p>
              </div>

              <div class="g360-detail-hero__actions">
                <?php if ($canManage && $estado === 'Enviado' && !$mostrarGestion): ?>
                  <a
                    class="g360-hero-button g360-hero-button--success"
                    href="reporte-proyecto-planeacion-alcaldia.php?id=<?= $id ?>&modo=gestionar"
                  >
                    <i class="feather icon-check-square"></i>
                    Gestionar
                  </a>
                <?php endif; ?>

                <?php if ($canReopen && $estado === 'Aprobado'): ?>
                  <button
                    type="button"
                    class="g360-hero-button g360-hero-button--warning"
                    id="btnReabrirDetalle"
                  >
                    <i class="feather icon-refresh-cw"></i>
                    Reabrir
                  </button>
                <?php endif; ?>

                <a
                  href="proyectos_planeacion_alcaldia.php"
                  class="g360-hero-button g360-hero-button--secondary"
                >
                  <i class="feather icon-list"></i>
                  Listado
                </a>

                <div class="g360-detail-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-detail-summary">
              <article>
                <span class="g360-detail-summary__icon">
                  <i class="feather icon-activity"></i>
                </span>

                <div>
                  <small>Estado actual</small>
                  <strong><?= h($estado ?: 'Sin estado') ?></strong>
                  <p>Situación registrada en Planeación</p>
                </div>
              </article>

              <article>
                <span class="g360-detail-summary__icon g360-detail-summary__icon--money">
                  <i class="feather icon-dollar-sign"></i>
                </span>

                <div>
                  <small>Valor del proyecto</small>
                  <strong>$ <?= number_format((float)($p['valor_proyecto'] ?? 0), 0, ',', '.') ?></strong>
                  <p>Presupuesto registrado</p>
                </div>
              </article>

              <article>
                <span class="g360-detail-summary__icon g360-detail-summary__icon--territory">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Municipio</small>
                  <strong><?= h($p['municipio'] ?? $p['nombre_municipio'] ?? '—') ?></strong>
                  <p>Territorio beneficiado</p>
                </div>
              </article>

              <article>
                <span class="g360-detail-summary__icon g360-detail-summary__icon--bpin">
                  <i class="feather icon-hash"></i>
                </span>

                <div>
                  <small>Código BPIN</small>
                  <strong><?= h(($p['bpin'] ?? '') !== '' ? $p['bpin'] : 'Pendiente') ?></strong>
                  <p>Obligatorio al aprobar</p>
                </div>
              </article>
            </div>

            <div class="g360-detail-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-target"></i>
                Metas PDD
              </span>

              <span>
                <i class="feather icon-paperclip"></i>
                Adjuntos
              </span>

              <span>
                <i class="feather icon-message-square"></i>
                Notas de gestión
              </span>

              <span>
                <i class="feather icon-clock"></i>
                Historial
              </span>

              <span>
                <i class="feather icon-shield"></i>
                Acceso autorizado
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-sm-12">
          <div class="table-wrap">
            <div class="table-shell g360-project-record">
              <div class="table-shell__top">
                <div>
                  <div class="table-shell__eyebrow">Expediente institucional</div>
                  <h3 class="table-shell__title">Proyecto de Planeación Municipal</h3>
                  <div class="table-shell__subtitle">
                    Información consolidada, soportes, gestión e historial.
                    <?php if ($estado === 'Aprobado'): ?>
                      <span class="badge badge-success-soft ml-1">Cerrado</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="table-shell__badge">
                  <i class="feather icon-file-text"></i>
                  N.° <?= $id ?>
                </div>
              </div>

              <div class="table-shell__body">

                <div class="brand-band g360-record-band">
                  <div class="g360-record-band__identity">
                    <span class="g360-record-band__logo">
                      <img src="assets/img/gob360l.png" alt="GOB360">
                    </span>

                    <div>
                      <span class="g360-record-band__eyebrow">
                        Gestión pública inteligente
                      </span>
                      <h4>Expediente de Planeación Municipal</h4>
                      <div class="sub">
                        Proyecto N.° <?= $id ?> · Santander, Colombia
                      </div>
                    </div>
                  </div>

                  <div class="g360-record-band__status">
                    <span>Estado del proyecto</span>
                    <strong class="badge <?= $badgeCls ?>"><?= h($estado) ?></strong>
                  </div>
                </div>

                <div class="form-section g360-detail-section">
                  <div class="g360-detail-section__header">
                    <span class="g360-detail-section__icon">
                      <i class="feather icon-grid"></i>
                    </span>
                    <div>
                      <span>Información principal</span>
                      <h6>Resumen del proyecto</h6>
                    </div>
                  </div>
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

                <div class="form-section g360-detail-section">
                  <div class="g360-detail-section__header">
                    <span class="g360-detail-section__icon g360-detail-section__icon--project">
                      <i class="feather icon-briefcase"></i>
                    </span>
                    <div>
                      <span>Objeto registrado</span>
                      <h6>Nombre del proyecto</h6>
                    </div>
                  </div>
                  <div class="rc-text-block"><?= h($p['proyecto'] ?? '—') ?></div>
                </div>

                <div class="form-section g360-detail-section">
                  <div class="g360-detail-section__header">
                    <span class="g360-detail-section__icon g360-detail-section__icon--target">
                      <i class="feather icon-target"></i>
                    </span>
                    <div>
                      <span>Alineación institucional</span>
                      <h6>Secretarías y metas del PDD</h6>
                    </div>
                  </div>
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

                <div class="form-section g360-detail-section">
                  <div class="g360-detail-section__header">
                    <span class="g360-detail-section__icon g360-detail-section__icon--notes">
                      <i class="feather icon-align-left"></i>
                    </span>
                    <div>
                      <span>Descripción complementaria</span>
                      <h6>Observaciones</h6>
                    </div>
                  </div>
                  <div class="rc-text-block"><?= h(($p['observaciones'] ?? '') !== '' ? $p['observaciones'] : '—') ?></div>
                </div>

                <?php if (!empty($p['gestion_nota']) || !empty($p['secretario_planeacion'])): ?>
                  <div class="form-section g360-detail-section g360-detail-section--management-note">
                    <div class="g360-detail-section__header">
                      <span class="g360-detail-section__icon g360-detail-section__icon--management">
                        <i class="feather icon-message-square"></i>
                      </span>
                      <div>
                        <span>Seguimiento de Planeación</span>
                        <h6>Última nota de gestión</h6>
                      </div>
                    </div>
                    <div class="rc-text-block"><?= h($p['gestion_nota'] ?? $p['secretario_planeacion'] ?? '') ?></div>
                  </div>
                <?php endif; ?>

                <div class="form-section g360-detail-section">
                  <div class="g360-detail-section__header">
                    <span class="g360-detail-section__icon g360-detail-section__icon--attachments">
                      <i class="feather icon-paperclip"></i>
                    </span>
                    <div>
                      <span>Evidencia documental</span>
                      <h6>Adjuntos del proyecto</h6>
                    </div>
                  </div>
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
                  <div class="form-section g360-detail-section">
                    <div class="g360-detail-section__header">
                      <span class="g360-detail-section__icon g360-detail-section__icon--attachments">
                        <i class="feather icon-folder-plus"></i>
                      </span>
                      <div>
                        <span>Soportes del trámite</span>
                        <h6>Adjuntos de gestión</h6>
                      </div>
                    </div>
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
                  <div class="form-section g360-detail-section g360-management-panel" id="panelGestion">
                    <div class="g360-detail-section__header">
                      <span class="g360-detail-section__icon g360-detail-section__icon--manage">
                        <i class="feather icon-check-square"></i>
                      </span>

                      <div>
                        <span>Decisión institucional</span>
                        <h6>Gestionar proyecto</h6>
                        <p>
                          Al aprobar, el proyecto queda cerrado.
                          El código BPIN es obligatorio únicamente para la aprobación.
                        </p>
                      </div>
                    </div>
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
                        <div class="col-12">
                          <div class="g360-management-actions">
                            <div class="g360-management-actions__message">
                              <i class="feather icon-shield"></i>
                              <span>La decisión quedará registrada en el historial del proyecto.</span>
                            </div>

                            <div class="g360-management-actions__buttons">
                              <a href="reporte-proyecto-planeacion-alcaldia.php?id=<?= $id ?>" class="btn btn-secondary btn-brutal">
                                <i class="feather icon-x"></i>
                                Cancelar
                              </a>

                              <button type="submit" class="btn btn-primary btn-brutal">
                                <i class="feather icon-save"></i>
                                Guardar gestión
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                <?php endif; ?>

                <div class="form-section g360-detail-section g360-detail-section--history mb-0">
                  <div class="g360-detail-section__header">
                    <span class="g360-detail-section__icon g360-detail-section__icon--history">
                      <i class="feather icon-clock"></i>
                    </span>
                    <div>
                      <span>Trazabilidad institucional</span>
                      <h6>Historial de acciones</h6>
                    </div>
                  </div>
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
