<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

$permissions = PagePermissions::crudForCurrentPage();

include './admin/classes/Visitasg.php';
include './admin/classes/Departamento.php';
include './admin/classes/Secretarias.php';
include './admin/classes/Acciong.php';
include './admin/classes/Linea.php';
include './admin/classes/Estrategia.php';

$codigoDepartamento = Util::getDepartamentoPrincipal(); // siempre Santander (68)

$lineas = Linea::getAll(null);
$lineasResponse = $lineas['output']['response'] ?? [];
$optionLineas = '';
foreach ($lineasResponse as $linea) {
    $optionLineas .= "<option value='" . $linea['id'] . "'>" . htmlspecialchars($linea['nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
}

$estrategias = Estrategia::getAll(null);
$estrategiasResponse = $estrategias['output']['response'] ?? [];
$optionEstrategias = '';
foreach ($estrategiasResponse as $estrategia) {
    $optionEstrategias .= "<option value='" . $estrategia['id'] . "'>" . htmlspecialchars($estrategia['nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro Actividades — Red de Valor Social</title>
  <link href="assets/css/registro_actividades_red_valor_gob360_actualizado.css" rel="stylesheet">
</head>
<body class="gob360-activity-registration">
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
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="m-b-10">Registro de actividades</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Gestión Social</a></li>
                <li class="breadcrumb-item"><a href="#!">Registro actividades</a></li>
              </ul>
              <p class="au-hint mb-0 mt-2">Ingreso de visitas y actividades de Red de Valor Social (1 y 2).</p>
            </div>
          </div>
        </div>
      </div>

      <!-- HERO VISUAL GOB360 -->
      <section class="g360-form-hero" aria-label="Registro de actividades GOB360">
        <div class="g360-form-hero__grid">

          <div>
            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-form-hero__logo"
            >
          </div>

          <div>
            <div class="g360-form-hero__eyebrow">
              <i class="feather icon-heart"></i>
              Gestión social territorial
            </div>

            <h1 class="g360-form-hero__title">
              Registro de actividades
            </h1>

            <p class="g360-form-hero__description">
              Registra las actividades de la Red de Valor Social, su ubicación,
              población impactada, inversión, clasificación institucional y
              evidencias fotográficas desde un formulario unificado.
            </p>

            <div class="g360-form-hero__chips">
              <span class="g360-chip g360-chip--success">
                <i class="feather icon-check-circle"></i>
                Formulario operativo
              </span>

              <span class="g360-chip">
                <i class="feather icon-map-pin"></i>
                Departamento de Santander
              </span>

              <span class="g360-chip">
                <i class="feather icon-camera"></i>
                Cuatro evidencias
              </span>
            </div>
          </div>

          <div class="g360-form-hero__visual" aria-hidden="true">
            <div class="g360-mini-card">
              <i class="feather icon-calendar"></i>
              <span>Actividad</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-map"></i>
              <span>Territorio</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-users"></i>
              <span>Impacto</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-camera"></i>
              <span>Evidencias</span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-sm-12">
          <div class="card g360-form-card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <div>
                <h5><i class="feather icon-file-text mr-2"></i>Formulario de actividad</h5>
                <p>Completa la información territorial, social y documental del registro.</p>
              </div>
            </div>

            <div class="card-body">
              <form id="formvisitas" autocomplete="off">
                <input type="hidden" id="id" name="id" value="">

                <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="<?php echo htmlspecialchars($codigoDepartamento, ENT_QUOTES, 'UTF-8'); ?>">

                <section class="g360-form-section">
                  <div class="g360-form-section__header">
                    <span class="g360-form-section__number">01</span>
                    <div>
                      <h3 class="g360-form-section__title">Información territorial</h3>
                      <p class="g360-form-section__subtitle">Tipo, fecha, municipio, provincia y población impactada.</p>
                    </div>
                  </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="tipo_actividad">Tipo de actividad <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipo_actividad" name="tipo_actividad" required>
                      <option value="">Seleccione</option>
                      <option value="primera_dama">Red de Valor Social 1</option>
                      <option value="aspas">Red de Valor Social 2</option>
                    </select>
                    <div class="helper-muted">Define si el registro es Primera Dama o ASPAS</div>
                  </div>

                  <div class="form-group col-md-4">
                    <label for="date">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date" required>
                  </div>

                  <div class="form-group col-md-4">
                    <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                    <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" onchange="DEPARTAMENTO.getVeredasByMunicipioId();"></select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="provincia">Provincia</label>
                    <select class="form-control" id="provincia" name="provincia">
                      <option value="Seleccione">Seleccione</option>
                      <option value="Soto_Norte">Soto Norte</option>
                      <option value="Guanenta">Guanentá</option>
                      <option value="Garcia_Rovira">García Rovira</option>
                      <option value="Comunera">Comunera</option>
                      <option value="Velez">Velez</option>
                      <option value="Metropolitana">Metropolitana</option>
                      <option value="Yariguíes">Yariguíes</option>
                    </select>
                  </div>

                  <div class="form-group col-md-4">
                    <label for="poblacion">Población Impactada</label>
                    <input type="text" class="form-control" id="poblacion" name="poblacion" placeholder="">
                    <div class="helper-muted">Ej: 350 personas</div>
                  </div>
                </div>

                </section>

                <section class="g360-form-section">
                  <div class="g360-form-section__header">
                    <span class="g360-form-section__number">02</span>
                    <div>
                      <h3 class="g360-form-section__title">Impacto y descripción</h3>
                      <p class="g360-form-section__subtitle">Inversión estimada y detalle de la actividad realizada.</p>
                    </div>
                  </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="inversion">Inversión Estimada</label>
                    <input type="text" onKeyPress="return soloNumeros(event);" class="form-control" id="inversion" name="inversion" placeholder="">
                    <div class="helper-muted">Solo números (sin puntos ni comas)</div>
                  </div>

                  <div class="form-group col-md-8">
                    <label for="desc_actividad">Descripción Actividad</label>
                    <textarea class="form-control" id="desc_actividad" name="desc_actividad" rows="2" placeholder="Ingrese el motivo de la Actividad"></textarea>
                  </div>
                </div>

                </section>

                <section class="g360-form-section">
                  <div class="g360-form-section__header">
                    <span class="g360-form-section__number">03</span>
                    <div>
                      <h3 class="g360-form-section__title">Clasificación institucional</h3>
                      <p class="g360-form-section__subtitle">Línea, estrategia, campaña y enlace mediático.</p>
                    </div>
                  </div>

                <div class="form-row">
                  <div class="form-group col-md-3">
                    <label for="tbl_linea_id">Línea</label>
                    <select class="form-control" id="tbl_linea_id" name="tbl_linea_id">
                      <option value="">Seleccione</option>
                      <?php echo $optionLineas; ?>
                    </select>
                  </div>

                  <div class="form-group col-md-3">
                    <label for="tbl_estrategia_id">Estrategia</label>
                    <select class="form-control" id="tbl_estrategia_id" name="tbl_estrategia_id">
                      <option value="">Seleccione</option>
                      <?php echo $optionEstrategias; ?>
                    </select>
                  </div>

                  <div class="form-group col-md-3">
                    <label for="campana">Nombre</label>
                    <select class="form-control" id="campana" name="campana">
                      <option value="Seleccione">Seleccione</option>
                      <option value="Niños al estadio">Niños al estadio</option>
                      <option value="Niños al cine">Niños al cine</option>
                      <option value="Niños al teatro">Niños al teatro</option>
                      <option value="Es tiempo de aprender">Es tiempo de aprender</option>
                      <option value="Niños al estadio - Optometría">Niños al estadio - Optometría</option>
                      <option value="Metale mente">Metale mente</option>
                    </select>
                  </div>

                  <div class="form-group col-md-3">
                    <label for="link">Link Mediático</label>
                    <input type="text" class="form-control" id="link" name="link" placeholder="Ingrese link">
                  </div>
                </div>

                </section>

                <section class="g360-form-section">
                  <div class="g360-form-section__header">
                    <span class="g360-form-section__number">04</span>
                    <div>
                      <h3 class="g360-form-section__title">Evidencias fotográficas</h3>
                      <p class="g360-form-section__subtitle">Adjunta hasta cuatro imágenes de soporte de la actividad.</p>
                    </div>
                  </div>

                <div class="form-row">
                  <div class="form-group col-md-3">
                    <label>Foto 1</label>
                    <div class="upload-box"><iframe id="ifm1" name="ifm" src="upload.php" scrolling="no" frameborder="0"></iframe></div>
                  </div>
                  <div class="form-group col-md-3">
                    <label>Foto 2</label>
                    <div class="upload-box"><iframe id="ifm2" name="ifm" src="upload.php" scrolling="no" frameborder="0"></iframe></div>
                  </div>
                  <div class="form-group col-md-3">
                    <label>Foto 3</label>
                    <div class="upload-box"><iframe id="ifm3" name="ifm" src="upload.php" scrolling="no" frameborder="0"></iframe></div>
                  </div>
                  <div class="form-group col-md-3">
                    <label>Foto 4</label>
                    <div class="upload-box"><iframe id="ifm4" name="ifm" src="upload.php" scrolling="no" frameborder="0"></iframe></div>
                  </div>
                </div>

                </section>

                <div class="form-actions">
                  <div class="bar">
                    <button type="button" onclick="UTIL.clearForm('formvisitas');" class="btn btn-danger">
                      <i class="feather icon-x-circle"></i>
                      Cancelar
                    </button>

                    <button class="btn btn-primary" type="button" onclick="VISITASG.validateData();">
                      <i class="feather icon-save"></i>
                      Guardar actividad
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <script src="admin/js/departamentoDama.js"></script>
  <script src="<?php echo Util::versionar('admin/js/detalle_visitasg.js'); ?>"></script>
  <script>
    DEPARTAMENTO.getMunicipios();
    try {
      $('#tipo_actividad, #tbl_municipio_id, #provincia, #tbl_linea_id, #tbl_estrategia_id, #campana')
        .select2({ theme: 'bootstrap4', width: '100%' });
    } catch (e) {}
  </script>
</body>
</html>
