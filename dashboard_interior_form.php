<?php
require './admin/include/generic_classes.php';
requirePermission('interior.formulario.view');

$view = SessionData::hasPermission('interior.formulario.view');
$create = SessionData::hasPermission('interior.formulario.create');
$edit = SessionData::hasPermission('interior.formulario.update');

date_default_timezone_set('America/Bogota');

include './admin/include/head.php';
?>
<body class="gob360-security-admin">
<!-- Bootstrap Icons (no incluido en head.php) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/formulario_estadisticas_seguridad_gob360.css">

  
<!-- Loader (intacto) -->
<div class="loader-bg">
  <div class="loader-track"><div class="loader-fill"></div></div>
</div>

<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>

<div class="pcoded-main-container">
  <div class="pcoded-wrapper">
    <div class="pcoded-content">
      <div class="pcoded-inner-content">
        <div class="main-body">
          <div class="page-wrapper">

            <div class="page-header">
              <div class="page-block">
                <div class="row align-items-center">
                  <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center g360-page-heading">
                      <div>
                        <h5 class="m-b-10">
                          <i class="feather icon-shield"></i>
                          Administración de Estadísticas de Seguridad
                        </h5>
                        <p>Configuración de boletines, metas, gráficos y datos comparativos.</p>
                      </div>

                      <?php include './admin/include/btn_back.php'; ?>
                    </div>
                    <ul class="breadcrumb">
                      <li class="breadcrumb-item title"><a href="index.html"><i class="feather icon-home"></i></a></li>
                      <li class="breadcrumb-item title"><a href="#!">Administración</a></li>
                      <li class="breadcrumb-item title"><a href="#!">Estadísticas de seguridad</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <section class="g360-security-hero" aria-label="Administración de estadísticas de seguridad GOB360">
              <div class="g360-security-hero__grid">

                <aside class="g360-security-brand">
                  <span class="g360-security-brand__eyebrow">
                    Plataforma institucional
                  </span>

                  <img
                    src="assets/img/gob360l.png"
                    alt="Logo GOB360"
                    class="g360-security-brand__logo"
                  >

                  <span class="g360-security-brand__caption">
                    Gestión pública inteligente y territorial
                  </span>

                  <div class="g360-security-brand__status">
                    <span></span>
                    Módulo habilitado
                  </div>
                </aside>

                <div class="g360-security-hero__content">
                  <div class="g360-security-hero__eyebrow">
                    <i class="feather icon-shield"></i>
                    Secretaría del Interior
                  </div>

                  <h1 class="g360-security-hero__title">
                    Centro de Estadísticas de Seguridad
                  </h1>

                  <p class="g360-security-hero__description">
                    Administra los datos globales y boletines diarios que alimentan
                    el dashboard institucional de seguridad. Configura metas,
                    compara periodos, actualiza gráficos y genera reportes PDF.
                  </p>

                  <div class="g360-security-capabilities">
                    <article>
                      <i class="bi bi-newspaper"></i>
                      <span>Boletines</span>
                    </article>

                    <article>
                      <i class="bi bi-bullseye"></i>
                      <span>Metas</span>
                    </article>

                    <article>
                      <i class="bi bi-bar-chart-line"></i>
                      <span>Gráficos</span>
                    </article>

                    <article>
                      <i class="bi bi-file-earmark-pdf"></i>
                      <span>Reportes</span>
                    </article>
                  </div>

                  <div class="g360-security-hero__meta">
                    <span>
                      <i class="bi bi-calendar3"></i>
                      Vigencia <?php echo date('Y'); ?>
                    </span>

                    <span>
                      <i class="bi bi-person-check"></i>
                      <?php echo $edit ? 'Edición autorizada' : 'Consulta habilitada'; ?>
                    </span>

                    <span>
                      <i class="bi bi-database-check"></i>
                      Datos institucionales
                    </span>
                  </div>
                </div>

              </div>
            </section>

            <div class="card card-pro g360-security-editor-card mb-3">
              <div class="card-header">
                <div class="g360-card-heading">
                  <span class="g360-card-heading__icon">
                    <i class="bi bi-pencil-square"></i>
                  </span>

                  <div>
                    <span class="g360-card-heading__eyebrow">Editor institucional</span>
                    <h5>Formulario de Estadísticas de Seguridad</h5>

                    <?php if($edit): ?>
                    <span class="boletin-badge" id="card_boletin_status">
                      <i class="bi bi-newspaper"></i>
                      <span id="card_boletin_label">Datos globales por año</span>
                    </span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="g360-card-actions">
                  <a class="btn btn-sm btn-light btn-pro" id="btnDescargarPDF" target="_blank" href="admin/ajax/dash_interior_pdf.php" style="display:none" data-boletin-id="0">
                    <i class="bi bi-download"></i> PDF
                  </a>
                  <?php if($edit): ?>
                  <button class="btn btn-sm btn-outline-light btn-pro" id="btnActivarBoletin" type="button" style="display:none">
                    <i class="bi bi-star-fill"></i> Activar
                  </button>
                  <button class="btn btn-sm btn-outline-light btn-pro" id="btnAbrirMeta" type="button">
                    <i class="bi bi-sliders"></i> Configurar Meta
                  </button>
                  <?php endif; ?>
                  <span class="badge-soft"><i class="bi bi-bar-chart"></i> Gráfico + valores</span>
                </div>
              </div>

              <div class="card-body">

                <div class="g360-editor-intro">
                  <span class="g360-editor-intro__icon">
                    <i class="bi bi-sliders2-vertical"></i>
                  </span>

                  <div>
                    <h6>Configura la fuente de información</h6>
                    <p>
                      Selecciona año, boletín y gráfico. Luego carga los valores,
                      realiza los cambios y guarda la información.
                    </p>
                  </div>
                </div>

                <div class="g360-security-filters">
                <div class="form-row">
                  <div class="form-group col-md-2">
                    <label>Año</label>
                    <select class="custom-select" id="anio">
                      <?php $currentYear = (int)date('Y'); for($y = $currentYear - 2; $y <= $currentYear; $y++): ?>
                      <option value="<?=$y?>" <?=$y===$currentYear?'selected':''?>><?=$y?></option>
                      <?php endfor; ?>
                    </select>
                    <div class="small-muted mt-1">
                      <span id="anio_label">Selecciona año para datos globales</span>
                      <span id="anio_boletin_hint" style="display:none;color:#00e5ff">Usa año del boletín</span>
                    </div>
                  </div>

                  <div class="form-group col-md-4">
                    <label>
                      Boletín
                      <?php if($edit): ?>
                      <button class="btn btn-sm btn-success btn-pro g360-new-bulletin-btn" id="btnNuevoBoletin" type="button" title="Crear nuevo boletín diario">
                        <i class="bi bi-plus-circle"></i> Nuevo
                      </button>
                      <?php endif; ?>
                    </label>
                    <select class="custom-select" id="boletin_select">
                      <option value="">-- Datos globales por año --</option>
                    </select>
                  </div>

                  <div class="form-group col-md-4">
                    <label>Gráfico</label>
                    <select class="custom-select" id="card_key" name="card_key"></select>
                    <div class="small-muted mt-1" id="card_sub"></div>
                  </div>

                  <div class="form-group col-md-2 d-flex align-items-end">
                    <button class="btn btn-info btn-pro btn-block" id="btnCargar" type="button">
                      <i class="bi bi-arrow-repeat"></i> Recargar
                    </button>
                  </div>
                </div>
                </div>

                <div class="g360-editor-workspace">
                  <div class="g360-editor-workspace__header">
                    <div>
                      <span>Área de edición</span>
                      <h6>Valores y configuración del gráfico</h6>
                    </div>

                    <span class="g360-editor-workspace__badge">
                      <i class="bi bi-graph-up-arrow"></i>
                      Vista editable
                    </span>
                  </div>

                  <div id="editor" class="g360-editor-container"></div>
                </div>

                <?php if($edit): ?>
                  <div class="g360-save-bar">
                    <div class="g360-save-bar__message">
                      <i class="bi bi-shield-check"></i>
                      <span>
                        Guarda los cambios para actualizar el dashboard institucional.
                      </span>
                    </div>

                    <button class="btn btn-success btn-pro" id="btnGuardar" type="button">
                      <i class="bi bi-save2"></i>
                      Guardar valores
                    </button>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning mt-3">No tienes permiso de edición.</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="card card-pro g360-result-card">
              <div class="card-header">
                <div class="g360-card-heading">
                  <span class="g360-card-heading__icon g360-card-heading__icon--success">
                    <i class="bi bi-eye"></i>
                  </span>

                  <div>
                    <span class="g360-card-heading__eyebrow">Vista pública</span>
                    <h5>Revisar resultado del Dashboard</h5>
                  </div>
                </div>

                <a href="dash_interior.php" class="btn btn-outline-light btn-pro">
                  <i class="bi bi-bar-chart-line-fill"></i>
                  Abrir dashboard
                </a>
              </div>

              <div class="card-body">
                <div class="g360-result-steps">
                  <article>
                    <span>1</span>
                    <div>
                      <strong>Guarda los valores</strong>
                      <p>Confirma los cambios realizados en el formulario.</p>
                    </div>
                  </article>

                  <i class="bi bi-arrow-right"></i>

                  <article>
                    <span>2</span>
                    <div>
                      <strong>Abre el dashboard</strong>
                      <p>Consulta la visualización institucional actualizada.</p>
                    </div>
                  </article>

                  <i class="bi bi-arrow-right"></i>

                  <article>
                    <span>3</span>
                    <div>
                      <strong>Recarga la vista</strong>
                      <p>Verifica los gráficos, metas y valores publicados.</p>
                    </div>
                  </article>
                </div>
              </div>
            </div>

            <?php include 'admin/include/footer.php'; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- =====================================================
     MODAL: Configurar Meta (tbl_dash_interior_meta)
====================================================== -->
<div class="modal fade" id="modalMeta" tabindex="-1" role="dialog" aria-labelledby="modalMetaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered g360-meta-modal-dialog" role="document">
    <div class="modal-content g360-meta-modal">

      <div class="modal-header">
        <h5 class="modal-title font-weight-bold" id="modalMetaLabel">
          <span class="g360-modal-title-icon">
            <i class="bi bi-sliders"></i>
          </span>
          <span id="modalMetaTitle">Configurar Meta del Dashboard</span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-6">
            <label class="font-weight-bold">Año 1 <small class="text-muted">(referencia)</small></label>
            <input type="number" class="form-control" id="meta_anio_1" min="2000" max="2100" placeholder="2025">
          </div>
          <div class="form-group col-6">
            <label class="font-weight-bold">Año 2 <small class="text-muted">(actual)</small></label>
            <input type="number" class="form-control" id="meta_anio_2" min="2000" max="2100" placeholder="2026">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-6" id="meta_fecha_boletin_group" style="display:none">
            <label class="font-weight-bold">Fecha del Boletín</label>
            <input type="date" class="form-control" id="meta_fecha_boletin">
          </div>
          <div class="form-group col-6">
            <label class="font-weight-bold">Fecha de Cierre</label>
            <input type="date" class="form-control" id="meta_fecha_cierre">
          </div>
          <div class="form-group col-6">
            <label class="font-weight-bold">Tasa de Homicidios</label>
            <input type="text" class="form-control" id="meta_tasa_homicidios" placeholder="Ej: 3,4%">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-6">
            <label class="font-weight-bold">Municipios sin Homicidios</label>
            <input type="number" class="form-control" id="meta_municipios_sin_homicidios" min="0" placeholder="Ej: 74">
          </div>
          <div class="form-group col-6">
            <label class="font-weight-bold">Boletín No.</label>
            <input type="number" class="form-control" id="meta_boletin_no" min="1" placeholder="Ej: 5">
          </div>
        </div>

        <div class="form-group">
          <label class="font-weight-bold">Fuente</label>
          <input type="text" class="form-control" id="meta_fuente" placeholder="Ej: SIJIN PONAL">
        </div>

        <div class="form-group">
          <label class="font-weight-bold">Factores de Atención</label>
          <textarea class="form-control" id="meta_nota_html" rows="4" placeholder="Ej: Factor de atención: Policía capturó en Floridablanca..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-pro" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success btn-pro" id="btnGuardarMeta">
          <i class="bi bi-save2"></i> Guardar Meta
        </button>
      </div>

    </div>
  </div>
</div>

<?php include 'admin/include/gerenic_script.php'; ?>
<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/pcoded.min.js"></script>
<!-- Re-adjuntar Bootstrap al jQuery que vendor-all dejó como definitivo -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="<?php echo Util::versionar('./admin/js/dashboard_interior_form.js'); ?>"></script>

</body>
</html>
