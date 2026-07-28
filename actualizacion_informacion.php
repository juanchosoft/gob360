<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);
include './admin/include/head.php';
include './admin/include/dark_theme.php';
include './admin/classes/Departamento.php';
include './admin/classes/Factores.php';
include './admin/classes/Actores.php';
include './admin/classes/ActualizacionInformacion.php'; 
require './admin/include/generic_classes.php';


// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de Factores
$arrFactores = Factores::getAll(null);
$isvalid = $arrFactores['output']['valid'];
$arrFactores = $arrFactores['output']['response'];
$optionFactores = '<option value="seleccione">Seleccione</option>';
foreach ($arrFactores as $val) {
    $optionFactores .= "<option value='" . $val['id'] . "'>" . $val['tipo'] . "</option>";
}
// Información de Actores
$arrActores = Actores::getAll(null);
$isvalid = $arrActores['output']['valid'];
$arrActores = $arrActores['output']['response'];
$optionActores = '<option value="seleccione">Seleccione</option>';
foreach ($arrActores as $val) {
    $optionActores .= "<option value='" . $val['id'] . "'>" . $val['nombre'] . "</option>";
}


// =================================================================
//pinta el nuevo mapa que compara con secretaria
// =================================================================

function determineMapColorPHP($cantidadNueva, $secretariaId) {
    $quantity = (int)$cantidadNueva; 

    if (!is_numeric($cantidadNueva) || $quantity < 0) {
        return "#CCCCCC"; 
    }
    
    if ($secretariaId === '10') { 
        if ($quantity > 200) return "#0000FF"; // Azul 
        return "#ADD8E6"; // Azul Claro
    } else {
        // Lógica general (Semáforo)
        if ($quantity === 0) {
            return "#00FF00"; // Verde
        } elseif ($quantity <= 50) {
            return "#FFFF00"; // Amarillo
        } elseif ($quantity <= 150) {
            return "#FFA500"; // Naranja
        } else {
            return "#FF0000"; // Rojo
        }
    }
}


$defaultDepId = Util::getDepartamentoPrincipal();
$defaultFactorId = $arrFactores[0]['id'] ?? 1; 
$defaultSecretariaId = Util::getSecretariaPrincipal() ?? '1'; 

$responseMapa = ActualizacionInformacion::obtenerDatosMapaInicial($defaultDepId, $defaultFactorId);
$datosMapa = [];

if ($responseMapa['output']['valid']) {
    foreach ($responseMapa['output']['data'] as $data) {
        $datosMapa[$data['codigo_municipio']] = $data['valor_total'];
    }
}

if (isset($santander) && is_array($santander)) {
    foreach ($santander as $key => &$value) { 
        $municipio_codigo = $value['codigo_muncipio'];
        
        $valor_actual = $datosMapa[$municipio_codigo] ?? 0;
        
        $nuevo_color = determineMapColorPHP($valor_actual, $defaultSecretariaId);
        
        $value['color'] = $nuevo_color;
    }
}
// =================================================================
// fin del nuevo mapa
// =================================================================


?>
<style>
/* ============================
   ACCIÓN UNIFICADA - ACTUALIZACIÓN INFO (SaaS Pro)
   Solo UI - No toca back
============================ */
:root{
  --au-primary:#1f4b72;
  --au-primary2:#2b6aa3;
  --au-bg:#070A12;
  --au-card:rgba(255,255,255,.06);
  --au-text:rgba(255,255,255,.92);
  --au-muted:rgba(255,255,255,.66);
  --au-border:rgba(255,255,255,.10);
  --au-shadow:0 20px 60px rgba(0,0,0,.35);
  --au-shadow-soft:0 14px 40px rgba(0,0,0,.25);
  --au-radius-xl:22px;
  --au-radius-lg:18px;
  --au-radius-md:14px;
}

/* Fondo */
body{
    background:
      radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
      radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
      radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
      linear-gradient(180deg, var(--au-bg), #0B1222) !important;
    color: #fff !important;
    overflow-x:hidden;
  }

/* Card general */
.card{
  border-radius: var(--au-radius-xl) !important;
  border: 1px solid var(--au-border) !important;
  background: var(--au-card) !important;
  box-shadow: var(--au-shadow) !important;
  overflow:hidden;
}
.card-header{
  background: linear-gradient(135deg, rgba(79,124,255,.12), rgba(255,255,255,.04)) !important;
  border-bottom: 1px solid rgba(255,255,255,.08) !important;
  padding: 16px 18px !important;
}
.card-header h5{
  margin:0 !important;
  font-weight: 950 !important;
  letter-spacing:.2px;
  color: var(--au-text) !important;
}
.card-body{ padding: 18px !important; }

label{
  font-weight: 850 !important;
  font-size: 13px !important;
  color: var(--au-text) !important;
  margin-bottom: 6px !important;
}

/* Inputs */
.form-control{
  height: 46px !important;
  border-radius: var(--au-radius-md) !important;
  border: 1px solid rgba(255,255,255,.12) !important;
  background:rgba(255,255,255,.06) !important;
  box-shadow: 0 6px 14px rgba(0,0,0,.20) !important;
}
.form-control:focus{
  border-color: rgba(79,124,255,.45) !important;
  box-shadow: 0 0 0 4px rgba(79,124,255,.14) !important;
}

/* ====== Secciones ====== */
.au-section-title{
  font-weight: 950;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing:.35px;
  color: #fff !important;
  display:flex;
  align-items:center;
  gap:10px;
  justify-content:center;
  margin: 6px 0 14px;
}
.au-section-title:before,
.au-section-title:after{
  content:"";
  height: 2px;
  width: 70px;
  background: linear-gradient(90deg, rgba(79,124,255,.55), rgba(79,124,255,.15));
  border-radius: 2px;
}

/* Bloque ubicación centrado */
.au-mini-row{
  display:flex;
  gap: 16px;
  justify-content:center;
  flex-wrap: wrap;
}
.au-mini{
  width: min(340px, 100%);
}
.au-mini .form-control{ height: 44px !important; }

/* Tabla */
.au-table-wrap{
  border: 1px solid rgba(255,255,255,.10);
  border-radius: var(--au-radius-lg);
  background:transparent;
  box-shadow: var(--au-shadow-soft);
  overflow:hidden;
}
.au-table-head{
  padding: 12px 14px;
  background: rgba(255,255,255,.04);
  border-bottom: 1px solid rgba(255,255,255,.10);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
}
.au-table-head .title{
  font-weight: 950;
  color: #fff !important;
}
#tablaRegistrosAislada{ margin-bottom:0;
}
#tablaRegistrosAislada thead th{
  background: rgba(255,255,255,.04) !important;
  color: rgba(255,255,255,.88) !important;
  border-bottom: 1px solid rgba(255,255,255,.10) !important;
  white-space: nowrap;
  font-weight: 1000 !important;
  vertical-align: middle;
  padding: 10px 10px;
}
#tablaRegistrosAislada tbody td{
  color: rgba(255,255,255,.86) !important;
  border-top: 1px solid rgba(255,255,255,.06) !important;
  vertical-align: middle !important;
  padding: 9px 10px;
}
#tablaRegistrosAislada tbody tr{ background: transparent !important;
}
#tablaRegistrosAislada tbody tr:hover{ background: rgba(255,255,255,.05) !important;
  letter-spacing:.2px;
}
#tablaRegistrosAislada tbody tr.selected-row{
  background: rgba(31,111,235,.25) !important;
  font-weight: 900;
  color: #fff !important;
}
.au-table-head .hint{
  color: rgba(255,255,255,.66) !important;
  font-weight: 700;
  font-size: 12px;
}



/* Scroll bonito */
.au-scroll{
  max-height: 320px;
  overflow:auto;
}
.au-scroll::-webkit-scrollbar{ height: 10px; width: 10px; }
.au-scroll::-webkit-scrollbar-thumb{ background: rgba(15,23,42,.18); border-radius: 10px; }
.au-scroll::-webkit-scrollbar-track{ background: rgba(15,23,42,.06); border-radius: 10px; }

/* Modal: coherente con la plantilla */
#modalActualizacion .modal-header{
  background: linear-gradient(135deg, var(--au-primary2), var(--au-primary)) !important;
}
#modalActualizacion .modal-content{
  border-radius: 22px !important;
  overflow:hidden;
  border: 0 !important;
}
#modalActualizacion .card{
  box-shadow: var(--au-shadow-soft) !important;
  border-radius: 18px !important;
}
#modalActualizacion .alert{
  border-radius: 16px !important;
}

/* Responsive */
@media(max-width:768px){
  .au-table-head{ flex-direction: column; align-items:flex-start; }
  .au-section-title:before,.au-section-title:after{ width: 40px; }
}
</style>
<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ navigation menu ] start -->
    <?php
    include './admin/include/navbar.php';
    ?>
    <!-- [ navigation menu ] end -->
    <!-- [ Header ] start -->
    <?php
    include './admin/include/header.php';
    ?>
    <!-- [ Header ] end -->

    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Actualización Información</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Configuración Acción Unificada / Actualización
                                        Información</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h5 style="font-size:25px;">
                            Factores de Inestabilidad Actualización
                        </h5>
                        <div class="card-header-right">
                            </div>
                    </div>
                    <div class="card-body">
                        <div class="col-sm-12">
                            <div class="card-body m-4">
                                
                                <form id="formactualizarinformacion" autocomplete="off">
  <input type="hidden" id="registroSeleccionadoInput" name="registroSeleccionado">

  <input type="hidden" name="op" id="op" />
  <input type="hidden" name="id" id="id" />
  <input type="hidden" name="filtro" id="filtro" value="vereda" />
  <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="si" />

  <!-- Departamento oculto (igual que tu lógica) -->
  <div class="form-group" style="display:none">
    <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
    <select
      onchange="DEPARTAMENTO.getMunicipios(); ACTUALIZACION_INFORMACION.obtenerRegistrosExistentes();"
      class="form-control"
      id="tbl_departamento_id"
      name="tbl_departamento_id">
      <?php echo $optionDep; ?>
    </select>
  </div>

  <!-- UBICACIÓN -->
  <div class="au-section-title">Ubicación del Registro</div>

  <div class="au-mini-row mb-3">
    <div class="au-mini">
      <div class="form-group">
        <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
        <select
          class="form-control"
          id="tbl_municipio_id"
          name="tbl_municipio_id"
          onchange="DEPARTAMENTO.getVeredasByMunicipioId(); ACTUALIZACION_INFORMACION.obtenerRegistrosExistentes();">
        </select>
      </div>
    </div>

    <div class="au-mini">
      <div class="form-group">
        <label for="tbl_vereda_id">Vereda <span class="text-danger">*</span></label>
        <select
          class="form-control"
          id="tbl_vereda_id"
          name="tbl_vereda_id"
          onchange="ACTUALIZACION_INFORMACION.obtenerRegistrosExistentes();">
        </select>
      </div>
    </div>
  </div>

  <!-- TABLA -->
  <div class="au-section-title">Registros Disponibles</div>

  <div class="au-table-wrap">
    <div class="au-table-head">
      <div class="title">Listado de factores por ubicación</div>
      <div class="hint">Selecciona una fila para abrir el modal de actualización</div>
    </div>

    <div class="au-scroll p-2">
      <table class="table table-hover" id="tablaRegistrosAislada">
        <thead>
          <tr>
            <th style="width: 10%;">ID</th>
            <th style="width: 45%;">Factor</th>
            <th style="width: 15%;">Valor Inicial</th>
            <th style="width: 15%;">Valor Actual</th>
            <th style="width: 15%;">Unidad</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</form>
                                        </div>
                                        </div> </div> </div> </div> </div> </div> </div> </div> </div> </div> 

<?php include 'admin/include/gerenic_script.php'; ?>
<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/pcoded.min.js"></script>

<script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
<script src="<?php echo Util::versionar('./admin/js/actualizacion_informacion.js'); ?>"></script>


<style>
#tablaRegistrosAislada tbody tr.selected-row { 
    background-color: rgba(31,111,235,.25) !important; 
    font-weight: bold; color: rgba(255,255,255,.92) !important;
}
</style>

<script>
    setTimeout(function() {
        DEPARTAMENTO.getMunicipios();
    }, 1000);
</script>

<style>
#modalActualizacion .modal-body{padding:2rem!important}
#modalActualizacion .card{border:1px solid rgba(255,255,255,.10);border-radius:8px;margin-bottom:1.2rem;padding:1rem;height:100%}
#modalActualizacion .card-header{border-bottom:1px solid rgba(255,255,255,.10);font-weight:600}
#modalActualizacion label{font-weight:500;color:rgba(255,255,255,.72);margin-bottom:.2rem}
#modalActualizacion input[readonly],#modalActualizacion textarea[readonly]{background:rgba(255,255,255,.04);color:rgba(255,255,255,.60)}
#modalActualizacion textarea{resize:vertical}
#modalActualizacion .alert{text-align:center;font-size:1.1rem;padding:1rem 1.5rem;letter-spacing:.3px}
#modalActualizacion .alert span{font-weight:700;margin:0 .3rem}
#modalActualizacion .row{margin-bottom:1rem}
#modalActualizacion .evidence-box{transition:all .2s ease-in-out}
#modalActualizacion .evidence-box:hover{box-shadow:0 0 6px rgba(0,0,0,.15);transform:translateY(-2px)}
@media(max-width:768px){
  #modalActualizacion .form-control.w-75{width:100%!important}
  #modalActualizacion .col-md-6{margin-bottom:1rem}
}
</style>
<div class="modal fade" id="modalActualizacion" tabindex="-1" role="dialog" aria-labelledby="modalActualizacionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content shadow-lg border-0">

      <!-- HEADER -->
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalActualizacionLabel">
          <i class="feather icon-edit mr-2"></i> Actualizar Registro:
          <span id="modal-factor-nombre" class="font-weight-bold text-warning"></span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <!-- VALORES -->
        <div class="alert alert-light border text-center p-3 mb-4" style="font-size:1.1rem;letter-spacing:.3px;">
          <strong>Valor Inicial:</strong> <span id="modal-valor-inicial" class="text-primary font-weight-bold mx-2"></span> |
          <strong>Valor Actual:</strong> <span id="modal-valor-actual" class="text-success font-weight-bold mx-2"></span> |
          <strong>Unidad:</strong> <span id="modal-unidad-medida" class="text-dark font-weight-bold mx-2"></span>
        </div>

        <div class="row align-items-stretch">

          <!-- COLUMNA IZQUIERDA -->
          <div class="col-md-6 d-flex flex-column">
            <div class="card flex-fill mb-3">
              <div class="card-header bg-light text-center py-3">
                <h5 class="mb-0 text-secondary d-flex justify-content-center align-items-center" style="gap:8px;">
                  <i class="feather icon-edit text-primary"></i> Actualizar Información
                </h5>
              </div>

              <div class="card-body">

                <!-- Cantidad Nueva -->
                <div class="text-center mb-4">
                  <h6 class="text-secondary mb-3">Cantidad Nueva <span class="text-danger">*</span></h6>
                  <input type="text" class="form-control text-center font-weight-bold w-75 mx-auto"
                    id="cantidad_nueva" name="cantidad_nueva" placeholder="Ej: 123"
                    onKeyPress="return soloNumeros(event);">
                </div>

                <!-- Acción Realizada -->
                <div class="mt-4">
                  <h6 class="text-secondary mb-2">Acción Realizada <span class="text-danger">*</span></h6>
                  <textarea id="accion_realizada" name="accion_realizada" class="form-control" rows="3"
                    placeholder="Describa las acciones realizadas..."></textarea>
                </div>

              </div>
            </div>
          </div>

          <!-- COLUMNA DERECHA -->
          <div class="col-md-6 d-flex flex-column">
            <div class="card flex-fill mb-3">
              <div class="card-header bg-light text-center py-3">
                <h5 class="mb-0 text-secondary d-flex justify-content-center align-items-center" style="gap:8px;">
                  <i class="feather icon-camera text-primary"></i> Evidencias Fotográficas
                </h5>
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-6 mb-4">
                    <div class="evidence-box p-2 border rounded">
                      <label class="d-block font-weight-bold">Foto 1</label>
                      <iframe id='ifm1' src="upload.php" width="100%" height="80" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="col-6 mb-4">
                    <div class="evidence-box p-2 border rounded">
                      <label class="d-block font-weight-bold">Foto 2</label>
                      <iframe id='ifm2' src="upload.php" width="100%" height="80" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="col-6 mb-4">
                    <div class="evidence-box p-2 border rounded">
                      <label class="d-block font-weight-bold">Foto 3</label>
                      <iframe id='ifm3' src="upload.php" width="100%" height="80" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="col-6 mb-4">
                    <div class="evidence-box p-2 border rounded">
                      <label class="d-block font-weight-bold">Foto 4</label>
                      <iframe id='ifm4' src="upload.php" width="100%" height="80" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary"
          onclick="UTIL.clearForm('formactualizarinformacion', ['#cantidad_nueva', '#accion_realizada', '#ifm1', '#ifm2', '#ifm3', '#ifm4']);"
          data-dismiss="modal">
          <i class="feather icon-x-circle mr-1"></i> Cancelar
        </button>
        <button type="button" onclick="ACTUALIZACION_INFORMACION.save();" class="btn btn-primary">
          <i class="feather icon-check-circle mr-1"></i> Guardar Cambios
        </button>
      </div>

    </div>
  </div>
</div>
</form>



</body>

</html>