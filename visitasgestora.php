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
  <style>
    :root{
      --bg0:#070A12;
      --bg1:#0B1222;
      --stroke: rgba(255,255,255,.10);
      --stroke2: rgba(255,255,255,.14);
      --txt: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);
      --brand:#4f7cff;
      --brand2:#9b5cff;
      --r-xl:18px;
      --shadow: 0 20px 60px rgba(0,0,0,.35);
      --shadow2: 0 14px 40px rgba(0,0,0,.25);
    }

    body{
      background:
        radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
        radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
        radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
        linear-gradient(180deg, var(--bg0), var(--bg1)) !important;
      color: var(--txt);
      overflow-x:hidden;
    }

    .pcoded-main-container{ background: transparent !important; }
    .pcoded-content{ padding: 16px 16px !important; }
    @media(min-width:768px){ .pcoded-content{ padding: 24px 24px !important; } }
    @media(min-width:1200px){ .pcoded-content{ padding: 34px 42px !important; } }

    .page-header .page-block{
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.05);
      border-radius: 16px;
      padding: 14px 14px;
      box-shadow: var(--shadow2);
      overflow:hidden;
      position: relative;
    }
    .page-header .page-block:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.25), transparent 65%),
        radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.18), transparent 65%);
      pointer-events:none;
    }
    .page-header .page-block > *{ position:relative; z-index:1; }
    .page-header h5, .breadcrumb .breadcrumb-item, .breadcrumb .breadcrumb-item a{
      color: var(--txt) !important;
    }
    .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }
    .au-hint{ color: var(--muted); font-size: 13px; margin: 0; }

    .card{
      border: 1px solid var(--stroke) !important;
      border-radius: var(--r-xl) !important;
      background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04)) !important;
      box-shadow: var(--shadow);
      overflow: hidden;
      position: relative;
    }
    .card:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.35), transparent 65%),
        radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.25), transparent 65%);
      pointer-events:none;
    }
    .card > *{ position:relative; z-index:1; }
    .card-header{
      background: rgba(0,0,0,.14) !important;
      border-bottom: 1px solid var(--stroke) !important;
      padding: 18px 18px !important;
    }
    .card-header h5{
      font-weight: 900 !important;
      letter-spacing: .2px;
      color: var(--txt) !important;
      margin:0 !important;
    }
    .card-body{ padding: 18px !important; }
    @media(min-width:768px){ .card-body{ padding: 22px !important; } }

    .form-control, select.form-control, textarea.form-control{
      border-radius: 14px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      background: rgba(0,0,0,.28) !important;
      color: var(--txt) !important;
      padding: 12px 14px !important;
      min-height: 46px;
      box-shadow:none !important;
    }
    .form-control::placeholder{ color: rgba(255,255,255,.50) !important; }
    .form-control:focus, select.form-control:focus, textarea.form-control:focus{
      border-color: rgba(79,124,255,.55) !important;
      box-shadow: 0 0 0 .2rem rgba(79,124,255,.18) !important;
      outline: none !important;
    }
    select.form-control option{ color:#0B1B38; background:#fff; }
    label{ color: rgba(255,255,255,.72) !important; font-weight: 900; }
    .helper-muted{ color: var(--muted); font-size: 12px; margin-top: 6px; }
    textarea.form-control{ min-height: 90px; resize: vertical; }

    .btn{
      border-radius: 14px !important;
      padding: 10px 22px !important;
      font-weight: 900 !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }
    .btn-primary{
      border-color: rgba(79,124,255,.45) !important;
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      color:#fff !important;
    }
    .btn-danger{
      border-color: rgba(255,91,122,.45) !important;
      background: linear-gradient(135deg, rgba(255,91,122,.22), rgba(0,0,0,.22)) !important;
      color:#fff !important;
    }

    .upload-box{
      border: 1px dashed rgba(255,255,255,.22);
      border-radius: 14px;
      background: rgba(0,0,0,.22);
      padding: 10px;
    }
    .upload-box iframe{
      width: 100% !important;
      height: 62px !important;
      border-radius: 12px;
      overflow: hidden;
      background: rgba(255,255,255,.06);
    }

    .form-actions{
      position: sticky;
      bottom: 0;
      z-index: 20;
      background: linear-gradient(180deg, rgba(7,10,18,0), rgba(7,10,18,.95) 30%);
      padding-top: 14px;
      margin-top: 10px;
    }
    .form-actions .bar{
      background: rgba(0,0,0,.45);
      border: 1px solid var(--stroke);
      border-radius: 16px;
      box-shadow: var(--shadow2);
      padding: 10px;
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      align-items: center;
    }

    .select2-container--bootstrap4 .select2-selection{
      border-radius: 14px !important;
      min-height: 46px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      background: rgba(0,0,0,.28) !important;
      padding: 8px 10px !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered{
      color: var(--txt) !important;
      line-height: 28px !important;
    }
    .select2-container--bootstrap4.select2-container--focus .select2-selection{
      border-color: rgba(79,124,255,.55) !important;
      box-shadow: 0 0 0 .2rem rgba(79,124,255,.18) !important;
    }
    .select2-dropdown{
      background: #0B1222 !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      color: var(--txt) !important;
    }
    .select2-results__option{ color: var(--txt) !important; }
    .select2-results__option--highlighted{
      background: rgba(79,124,255,.35) !important;
      color: #fff !important;
    }

    @media (max-width: 768px){
      .form-actions .bar{ justify-content: space-between; }
      .form-actions .btn{ width: 49%; }
    }
  </style>
</head>
<body class="">
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

      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <h5><i class="feather icon-file-text"></i> Formulario detalle</h5>
            </div>

            <div class="card-body">
              <form id="formvisitas" autocomplete="off">
                <input type="hidden" id="id" name="id" value="">

                <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="<?php echo htmlspecialchars($codigoDepartamento, ENT_QUOTES, 'UTF-8'); ?>">

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

                <div class="form-actions">
                  <div class="bar">
                    <button type="button" onclick="UTIL.clearForm('formvisitas');" class="btn btn-danger">Cancelar</button>
                    <button class="btn btn-primary" type="button" onclick="VISITASG.validateData();">Guardar</button>
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
