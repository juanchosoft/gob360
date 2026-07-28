<?php
require './admin/include/generic_classes.php';
requirePermission('interior.formulario.view');

$view = SessionData::hasPermission('interior.formulario.view');
$create = SessionData::hasPermission('interior.formulario.create');
$edit = SessionData::hasPermission('interior.formulario.update');

date_default_timezone_set('America/Bogota');

include './admin/include/head.php';
?>
<body>
<!-- Bootstrap Icons (no incluido en head.php) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
    :root{
      --bg1:#08141b; --bg2:#0f2530; --bg3:#102f3d;
      --stroke:rgba(255,255,255,.12);
      --txt:rgba(255,255,255,.92); --muted:rgba(255,255,255,.70);
      --radius:18px;
      --accent:#ff7a00; --accent2:#00e5ff;
    }
    body{
      background:
        radial-gradient(1200px 600px at 20% 0%, rgba(0,229,255,.10), transparent 65%),
        radial-gradient(900px 500px at 80% 10%, rgba(255,122,0,.10), transparent 60%),
        linear-gradient(135deg, var(--bg1), var(--bg2), var(--bg3));
      color:var(--txt);
    }
    .card-pro{
      background:rgba(255,255,255,.06);
      border:1px solid var(--stroke);
      border-radius:var(--radius);
      box-shadow:0 18px 55px rgba(0,0,0,.40);
      overflow:hidden;
    }
    .card-pro .card-header{
      background:linear-gradient(90deg, rgba(15,45,58,.85), rgba(21,63,82,.65));
      border-bottom:1px solid rgba(255,255,255,.10);
      font-weight:1000;
      letter-spacing:.5px;
      display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;
    }
    .badge-soft{
      padding:7px 10px;border-radius:12px;
      border:1px solid rgba(255,255,255,.14);
      background:rgba(0,0,0,.20);
      font-weight:900;
      color:rgba(255,255,255,.85);
    }
    .boletin-badge{
      padding:6px 14px;border-radius:14px;
      border:1px solid rgba(0,229,255,.35);
      background:rgba(0,229,255,.10);
      font-weight:900;
      color:#00e5ff;
      font-size:13px;
      display:inline-flex;align-items:center;gap:6px;
    }
    .boletin-badge i{ font-size:16px; }
    .form-control,.custom-select{
      background:rgba(0,0,0,.25)!important;
      color:#fff!important;
      border:1px solid rgba(255,255,255,.15)!important;
      border-radius:12px!important;
      font-weight:800;
    }
    .custom-select option{
      color:#111 !important;
      background:#fff !important;
    }

    .small-muted{color:var(--muted);font-weight:700;font-size:12px}
    .btn-pro{border-radius:14px;font-weight:1000;letter-spacing:.3px}
    .kv{
      display:flex;justify-content:space-between;gap:12px;
      border-bottom:1px dashed rgba(255,255,255,.12);
      padding:10px 0;
    }
    .kv:last-child{border-bottom:none}
    .kv b{font-weight:1000}

    .boletin-bar{
      display:flex;align-items:center;gap:12px;flex-wrap:wrap;
      padding:12px 18px;
      background:rgba(0,0,0,.15);
      border:1px solid var(--stroke);
      border-radius:var(--radius);
      margin-bottom:16px;
    }

    .loader-bg{ display:none; }
  </style>
  <style>
  .swal2-popup {
    background: linear-gradient(135deg, #0f2530, #102f3d) !important;
    border: 1px solid rgba(255,255,255,.15) !important;
    border-radius: 18px !important;
    color: #fff !important;
    box-shadow: 0 25px 60px rgba(0,0,0,.6) !important;
  }

  .swal2-title {
    font-weight: 1000 !important;
    letter-spacing: .5px;
  }

  .swal2-confirm {
    background: linear-gradient(90deg, #00e5ff, #00bcd4) !important;
    border: none !important;
    border-radius: 12px !important;
    font-weight: 900 !important;
    padding: 8px 22px !important;
  }

  .swal2-confirm:hover {
    background: linear-gradient(90deg, #00bcd4, #00e5ff) !important;
  }

  .swal2-icon.swal2-success {
    border-color: #33d17a !important;
    color: #33d17a !important;
  }
</style>
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
                    <div class="d-flex justify-content-between align-items-center">
                      <?php include './admin/include/btn_back.php'; ?>
                    </div>
                    <ul class="breadcrumb">
                      <li class="breadcrumb-item title"><a href="index.html"><i class="feather icon-home"></i></a></li>
                      <li class="breadcrumb-item title"><a href="#!">Administración</a></li>
                      <li class="breadcrumb-item title"><a href="#!">Dashboard Interior</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="card card-pro mb-3">
              <div class="card-header">
                <span><i class="bi bi-pencil-square"></i> Formulario Estadistica de Seguridad
                  <?php if($edit): ?>
                  <span style="margin-left:12px;font-size:12px;color:#00e5ff;background:rgba(0,229,255,.12);padding:3px 12px;border-radius:20px;border:1px solid rgba(0,229,255,.25)" id="card_boletin_status">
                    <i class="bi bi-newspaper"></i> <span id="card_boletin_label">Datos globales por año</span>
                  </span>
                  <?php endif; ?>
                </span>
                <div class="d-flex align-items-center gap-2" style="gap:8px">
                  <a class="btn btn-sm btn-light btn-pro" id="btnDescargarPDF" target="_blank" href="admin/ajax/dash_interior_pdf.php" style="display:none" data-boletin-id="0">
                    <i class="bi bi-download"></i> PDF
                  </a>
                  <?php if($edit): ?>
                  <button class="btn btn-sm btn-outline-light btn-pro" id="btnActivarBoletin" type="button" style="display:none">
                    <i class="bi bi-star-fill" style="color:#ffc107"></i> Activar
                  </button>
                  <button class="btn btn-sm btn-outline-light btn-pro" id="btnAbrirMeta" type="button">
                    <i class="bi bi-sliders"></i> Configurar Meta
                  </button>
                  <?php endif; ?>
                  <span class="badge-soft"><i class="bi bi-bar-chart"></i> gráfico + valores</span>
                </div>
              </div>

              <div class="card-body">
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
                      <button class="btn btn-sm btn-success btn-pro" id="btnNuevoBoletin" type="button" style="padding:2px 10px;font-size:11px;margin-left:6px" title="Crear nuevo boletín diario">
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

                <hr style="border-color: rgba(255,255,255,.12)">

                <div id="editor" class="mt-2"></div>

                <?php if($edit): ?>
                  <button class="btn btn-success btn-pro btn-block mt-3" id="btnGuardar" type="button">
                    <i class="bi bi-save2"></i> Guardar Valores
                  </button>
                <?php else: ?>
                  <div class="alert alert-warning mt-3">No tienes permiso de edición.</div>
                <?php endif; ?>
              </div>
            </div>

            <div class="card card-pro">
              <div class="card-header">
                <span><i class="bi bi-eye"></i> Ver resultado</span>
                <a href="dash_interior.php" class="btn btn-outline-light btn-pro">
                  <i class="bi bi-bar-chart-line-fill"></i> Ver Dashboard
                </a>
              </div>
              <div class="card-body small-muted">
                Guarda aquí → abre el dashboard → recarga.
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
  <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:540px">
    <div class="modal-content" style="background:linear-gradient(135deg,#0f2530,#102f3d);border:1px solid rgba(255,255,255,.15);border-radius:18px;color:#fff">

      <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.12)">
        <h5 class="modal-title font-weight-bold" id="modalMetaLabel">
          <i class="bi bi-sliders"></i> <span id="modalMetaTitle">Configurar Meta del Dashboard</span>
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

      <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.12)">
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
