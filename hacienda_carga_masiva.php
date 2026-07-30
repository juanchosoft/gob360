<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$modulo = 'Hacienda - Carga Masiva';
?>

<link href="assets/css/carga_masiva_hacienda_gob360.css" rel="stylesheet">

<body class="gob360-hacienda-bulk">
    <div class="loader-bg">
        <div class="loader-track"><div class="loader-fill"></div></div>
    </div>
    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>



<div class="pcoded-main-container">
  <div class="pcoded-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-md-12">
            <div class="page-header-title">
              <h5 class="m-b-10">Carga masiva de Hacienda</h5>
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

    <!-- HERO VISUAL GOB360 -->
    <section class="g360-bulk-hero" aria-label="Carga masiva de Hacienda GOB360">
      <div class="g360-bulk-hero__grid">

        <div>
          <img
            src="assets/img/gob360l.png"
            alt="Logo GOB360"
            class="g360-bulk-hero__logo"
          >
        </div>

        <div>
          <div class="g360-bulk-hero__eyebrow">
            <i class="feather icon-upload-cloud"></i>
            Automatización fiscal
          </div>

          <h1 class="g360-bulk-hero__title">
            Carga masiva de Hacienda
          </h1>

          <p class="g360-bulk-hero__description">
            Descarga la plantilla correspondiente, completa los registros y
            procesa el archivo Excel para importar información de operativos
            GOA con validación individual de cada fila.
          </p>

          <div class="g360-bulk-hero__chips">
            <span class="g360-chip g360-chip--success">
              <i class="feather icon-check-circle"></i>
              Validación por registro
            </span>

            <span class="g360-chip">
              <i class="feather icon-file-text"></i>
              Plantilla por acción
            </span>

            <span class="g360-chip">
              <i class="feather icon-alert-triangle"></i>
              Reporte de errores
            </span>
          </div>
        </div>

        <div class="g360-bulk-hero__visual" aria-hidden="true">
          <div class="g360-mini-card">
            <i class="feather icon-list"></i>
            <span>Acción</span>
          </div>

          <div class="g360-mini-card">
            <i class="feather icon-download"></i>
            <span>Plantilla</span>
          </div>

          <div class="g360-mini-card">
            <i class="feather icon-upload"></i>
            <span>Carga</span>
          </div>

          <div class="g360-mini-card">
            <i class="feather icon-check-square"></i>
            <span>Resultado</span>
          </div>
        </div>

      </div>
    </section>

    <div class="row">

      <!-- Columna izquierda: instrucciones + formulario -->
      <div class="col-lg-5 col-xl-4">

        <!-- Pasos -->
        <div class="card mb-3 g360-bulk-card">
          <div class="card-header">
            <h5><i class="feather icon-info mr-2"></i>Proceso de importación</h5>
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
        <div class="card g360-bulk-card">
          <div class="card-header">
            <div>
              <h5><i class="feather icon-upload-cloud mr-2"></i>Cargar archivo Excel</h5>
              <p>Selecciona la acción, descarga la plantilla y procesa el archivo diligenciado.</p>
            </div>
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
                <i class="feather icon-download mr-1"></i> Descargar plantilla Excel
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
              <span id="btnSubirText"><i class="feather icon-upload mr-1"></i> Procesar carga</span>
              <span id="btnSubirSpinner" style="display:none;">
                <span class="spinner-border spinner-border-sm mr-1"></span> Procesando archivo...
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- Columna derecha: resultados -->
      <div class="col-lg-7 col-xl-8">
        <div class="card g360-bulk-card g360-results-card" id="cardResultados" style="display:none;">
          <div class="card-header">
            <div><h5><i class="feather icon-check-circle mr-2"></i>Resultado de la carga</h5><p>Resumen de registros procesados y detalle de validaciones.</p></div>
          </div>
          <div class="card-body p-3">

            <!-- Resumen numérico -->
            <div class="d-flex gap-3 flex-wrap mb-3" id="summaryWrap"></div>

            <!-- Tabla de errores -->
            <div id="erroresWrap" style="display:none;">
              <h6 class="font-weight-bold mb-2" style="color:#991b1b;">
                <i class="feather icon-alert-triangle mr-1"></i> Registros con error
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
        <div class="card g360-bulk-card g360-empty-card" id="cardVacio">
          <div class="card-body text-center py-5">
            <div class="g360-empty-icon"><i class="feather icon-layers"></i></div>
            <p class="mt-3 mb-0" style="color:var(--muted);font-size:15px;">Selecciona una acción, descarga la plantilla y procesa el archivo para visualizar aquí el resultado de la importación.</p>
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
