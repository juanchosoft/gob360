<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$modulo = 'Hacienda - Carga Masiva';
?>

<body class="">
    <div class="loader-bg">
        <div class="loader-track"><div class="loader-fill"></div></div>
    </div>
    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

<style>
  :root{
    --nav-blue:#20427F;
    --nav-blue-2:#132b52;
    --nav-blue-3:#2e58a8;
    --bg:#f6f8fc;
    --card:#ffffff;
    --ink:#0f172a;
    --muted:#64748b;
    --line:rgba(15,23,42,.10);
    --radius-xl:22px;
    --radius-lg:16px;
    --radius-md:12px;
    --shadow-soft:0 12px 30px rgba(2,6,23,.10);
    --shadow-mid:0 18px 40px rgba(2,6,23,.14);
    --ring:0 0 0 4px rgba(46,88,168,.16);
  }
  body{ background: var(--bg) !important; }
  .pcoded-main-container{ background: transparent !important; }
  .pcoded-content{ padding-top: 18px !important; }

  .page-block{
    background: linear-gradient(135deg, rgba(32,66,127,.10), rgba(255,255,255,.72));
    border: 1px solid rgba(255,255,255,.70);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-soft);
    padding: 16px 18px;
    backdrop-filter: blur(10px);
  }
  .page-block h5{ font-weight: 900 !important; letter-spacing: .2px; color: var(--ink); }
  .breadcrumb{ margin-top: 6px !important; margin-bottom: 0 !important; background: transparent !important; padding: 0 !important; }
  .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }
  .breadcrumb .breadcrumb-item{ font-size: 13px; }

  .card{ border: 1px solid var(--line) !important; border-radius: var(--radius-xl) !important; box-shadow: var(--shadow-soft); overflow: hidden; background: var(--card); }
  .card-header{ border-bottom: 1px solid rgba(255,255,255,.10) !important; padding: 14px 16px !important; background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important; color: #fff !important; }
  .card-header h5{ color:#fff !important; margin:0 !important; font-weight: 900 !important; }
  .card-body{ background: linear-gradient(180deg, rgba(32,66,127,.04), rgba(255,255,255,1)); }

  label.form-label, .form-group label{ font-weight: 800; color: var(--ink); margin-bottom: 8px; }
  .form-control{ border-radius: 14px !important; border: 1px solid rgba(15,23,42,.14) !important; padding: 12px 12px !important; height: auto !important; box-shadow: none !important; transition: .18s ease; background: #fff !important; }
  .form-control:focus{ border-color: rgba(46,88,168,.55) !important; box-shadow: var(--ring) !important; }

  /* Zona de carga */
  .drop-zone{
    border: 2.5px dashed rgba(32,66,127,.30);
    border-radius: var(--radius-lg);
    background: rgba(32,66,127,.04);
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: .2s ease;
    position: relative;
  }
  .drop-zone:hover, .drop-zone.drag-over{
    border-color: var(--nav-blue);
    background: rgba(32,66,127,.08);
  }
  .drop-zone input[type="file"]{ display: none; }
  .drop-zone .dz-icon{ font-size: 48px; color: var(--nav-blue); opacity: .55; }
  .drop-zone .dz-text{ color: var(--muted); font-size: 14px; margin-top: 10px; }
  .drop-zone .dz-filename{ font-weight: 800; color: var(--nav-blue); margin-top: 8px; font-size: 13px; }

  /* Botones */
  .btn-hz-primary{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-3));
    color: #fff !important;
    border: none;
    border-radius: 14px;
    padding: 11px 22px;
    font-weight: 800;
    font-size: 14px;
    transition: .18s ease;
    box-shadow: 0 4px 14px rgba(32,66,127,.30);
  }
  .btn-hz-primary:hover{ transform: translateY(-1px); box-shadow: 0 8px 20px rgba(32,66,127,.38); }
  .btn-hz-primary:disabled{ opacity: .55; transform: none; }

  .btn-hz-outline{
    background: #fff;
    color: var(--nav-blue) !important;
    border: 2px solid var(--nav-blue);
    border-radius: 14px;
    padding: 10px 22px;
    font-weight: 800;
    font-size: 14px;
    transition: .18s ease;
  }
  .btn-hz-outline:hover{ background: rgba(32,66,127,.06); }

  /* Tabla resultados */
  .result-table{ font-size: 13px; }
  .result-table th{ background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)); color: #fff; font-weight: 800; }
  .result-table .badge-ok{ background: #d1fae5; color: #065f46; border-radius: 999px; padding: 3px 10px; font-size: 12px; font-weight: 700; }
  .result-table .badge-err{ background: #fee2e2; color: #991b1b; border-radius: 999px; padding: 3px 10px; font-size: 12px; font-weight: 700; }

  /* Resumen */
  .summary-box{
    border-radius: var(--radius-lg);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
  }
  .summary-box.ok{ background: #d1fae5; border: 1.5px solid #6ee7b7; }
  .summary-box.warn{ background: #fef9c3; border: 1.5px solid #fde047; }
  .summary-box.err{ background: #fee2e2; border: 1.5px solid #fca5a5; }
  .summary-box .sb-num{ font-size: 28px; font-weight: 900; }
  .summary-box .sb-label{ font-size: 13px; color: var(--muted); font-weight: 600; }

  .spinner-border-sm{ width: 1rem; height: 1rem; }

  /* Info steps */
  .step-badge{
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--nav-blue);
    color: #fff;
    font-weight: 900;
    font-size: 13px;
    flex-shrink: 0;
  }
  .step-row{ display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
  .step-row p{ margin: 0; font-size: 13px; color: var(--muted); line-height: 1.5; }
</style>

<div class="pcoded-main-container">
  <div class="pcoded-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-md-12">
            <div class="page-header-title">
              <h5 class="m-b-10">Carga Masiva — Hacienda</h5>
            </div>
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
              <li class="breadcrumb-item"><a href="hacienda.php">Hacienda</a></li>
              <li class="breadcrumb-item active">Carga Masiva</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row">

      <!-- Columna izquierda: instrucciones + formulario -->
      <div class="col-lg-5 col-xl-4">

        <!-- Pasos -->
        <div class="card mb-3">
          <div class="card-header">
            <h5><i class="feather icon-info mr-2"></i> ¿Cómo funciona?</h5>
          </div>
          <div class="card-body p-3">
            <div class="step-row">
              <span class="step-badge">1</span>
              <p>Selecciona el <strong>tipo de acción</strong> que deseas cargar.</p>
            </div>
            <div class="step-row">
              <span class="step-badge">2</span>
              <p>Descarga la <strong>plantilla Excel</strong> generada para esa acción. Contiene la hoja <em>Datos</em> con los campos correctos y una hoja <em>Municipios</em> con los nombres válidos.</p>
            </div>
            <div class="step-row">
              <span class="step-badge">3</span>
              <p>Completa la plantilla respetando el formato de fecha <strong>YYYY-MM-DD</strong> y los nombres exactos de municipio de la hoja de referencia.</p>
            </div>
            <div class="step-row">
              <span class="step-badge">4</span>
              <p>Sube el archivo y revisa el resultado. Las filas con error <strong>no se insertan</strong>; las correctas sí.</p>
            </div>
          </div>
        </div>

        <!-- Formulario -->
        <div class="card">
          <div class="card-header">
            <h5><i class="feather icon-upload-cloud mr-2"></i> Cargar Archivo</h5>
          </div>
          <div class="card-body p-3">
            <div class="form-group mb-3">
              <label class="form-label">Tipo de Acción <span class="text-danger">*</span></label>
              <select id="selectAccion" class="form-control">
                <option value="">— Selecciona una acción —</option>
                <option value="GOA Aprehensiones de Licores">GOA Aprehensiones de Licores</option>
                <option value="GOA Aprehensión de Cigarrillos">GOA Aprehensión de Cigarrillos</option>
                <option value="GOA Aprehensión de Cervezas">GOA Aprehensión de Cervezas</option>
                <option value="GOA Aprehensión de Tabaco y Otros">GOA Aprehensión de Tabaco y Otros</option>
                <option value="GOA Juridico">GOA Jurídico</option>
              </select>
            </div>

            <div class="mb-3" id="wrapPlantilla" style="display:none;">
              <a id="btnDescarga" href="#" target="_blank" class="btn-hz-outline d-block text-center text-decoration-none py-2">
                <i class="feather icon-download mr-1"></i> Descargar Plantilla Excel
              </a>
            </div>

            <div class="form-group mb-3" id="wrapUpload" style="display:none;">
              <label class="form-label">Archivo Excel (.xlsx)</label>
              <div class="drop-zone" id="dropZone">
                <input type="file" id="fileInput" accept=".xlsx">
                <div class="dz-icon"><i class="feather icon-file-text"></i></div>
                <div class="dz-text">Arrastra tu archivo aquí o <strong>haz clic</strong> para seleccionar</div>
                <div class="dz-filename" id="dzFilename"></div>
              </div>
            </div>

            <button id="btnSubir" class="btn-hz-primary w-100 mt-1" disabled>
              <span id="btnSubirText"><i class="feather icon-upload mr-1"></i> Procesar Carga</span>
              <span id="btnSubirSpinner" style="display:none;">
                <span class="spinner-border spinner-border-sm mr-1"></span> Procesando...
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- Columna derecha: resultados -->
      <div class="col-lg-7 col-xl-8">
        <div class="card" id="cardResultados" style="display:none;">
          <div class="card-header">
            <h5><i class="feather icon-check-circle mr-2"></i> Resultado de la Carga</h5>
          </div>
          <div class="card-body p-3">

            <!-- Resumen numérico -->
            <div class="d-flex gap-3 flex-wrap mb-3" id="summaryWrap"></div>

            <!-- Tabla de errores -->
            <div id="erroresWrap" style="display:none;">
              <h6 class="font-weight-bold mb-2" style="color:#991b1b;">
                <i class="feather icon-alert-triangle mr-1"></i> Filas con error
              </h6>
              <div class="table-responsive">
                <table class="table result-table table-bordered">
                  <thead>
                    <tr>
                      <th style="width:80px;">Fila</th>
                      <th>Mensaje</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyErrores"></tbody>
                </table>
              </div>
            </div>

            <div id="sinErrores" style="display:none;" class="text-center py-3">
              <i class="feather icon-check-circle" style="font-size:48px;color:#10b981;"></i>
              <p class="mt-2 mb-0 font-weight-bold" style="color:#065f46;">¡Todos los registros se cargaron correctamente!</p>
            </div>

          </div>
        </div>

        <!-- Estado vacío -->
        <div class="card" id="cardVacio">
          <div class="card-body text-center py-5">
            <i class="feather icon-layers" style="font-size:64px;color:rgba(32,66,127,.18);"></i>
            <p class="mt-3 mb-0" style="color:var(--muted);font-size:15px;">Selecciona una acción y sube tu archivo para ver los resultados aquí.</p>
          </div>
        </div>
      </div>

    </div><!-- /row -->
  </div>
</div>

<?php include './admin/include/footer.php'; ?>

    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="<?php echo Util::versionar('./admin/js/hacienda_carga_masiva.js'); ?>"></script>

</body>
</html>
