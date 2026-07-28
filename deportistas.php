<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
include './admin/classes/Deportistas.php';
include './admin/classes/Disciplina.php';
include './admin/classes/Ligas.php';

// =========================
// PERMISOS
// =========================
extract(PagePermissions::crudVarsForCurrentPage());

$userType    = SessionData::getUserType();
$secretariaId = SessionData::getSecretaria();
$isAdmin     = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador() || $userType === Util::Gobernador());

if (!$isAdmin && $secretariaId != Util::getSecretariaIdInder()) {
    header('Location: dashboard.php');
    exit;
}

// =========================
// DISCIPLINAS
// =========================
$arrDisciplina = Disciplina::getAll(null);
$arrDisciplina = $arrDisciplina['output']['response'] ?? [];

$option = '<option value="">Seleccione</option>';
foreach ($arrDisciplina as $val) {
    $id         = isset($val['id']) ? (int)$val['id'] : 0;
    $disciplina = isset($val['disciplina']) ? htmlspecialchars($val['disciplina'], ENT_QUOTES, 'UTF-8') : '';
    $option .= "<option value=\"{$id}\">{$disciplina}</option>";
}

// =========================
// LIGAS
// =========================
$arrLiga = Ligas::getAll(null);
$arrLiga = $arrLiga['output']['response'] ?? [];

$optionLiga = '<option value="">Seleccione</option>';
foreach ($arrLiga as $val) {
    $id   = isset($val['id']) ? (int)$val['id'] : 0;
    $liga = isset($val['liga']) ? htmlspecialchars($val['liga'], ENT_QUOTES, 'UTF-8') : '';
    $optionLiga .= "<option value=\"{$id}\">{$liga}</option>";
}
?>

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
  --danger:#ff5b7a;
  --ok:#18ff6d;
  --radius-xl:22px;
  --radius-lg:16px;
  --shadow-soft: 0 14px 40px rgba(0,0,0,.25);
  --shadow-mid: 0 22px 60px rgba(0,0,0,.35);
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

.au-topbar{
  display:flex;
  flex-direction:column;
  gap:10px;
  margin-bottom:18px;
}
@media(min-width:768px){
  .au-topbar{
    flex-direction:row;
    align-items:center;
    justify-content:space-between;
  }
}
.au-title{
  margin:0;
  font-weight:900;
  font-size:1.55rem;
  letter-spacing:.2px;
  color: var(--txt);
}
.au-subtitle{
  margin:4px 0 0;
  color: var(--muted);
  font-size:.92rem;
}

.au-tabs{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  background: rgba(255,255,255,.06);
  border: 1px solid var(--stroke);
  padding:6px;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-soft);
  width: fit-content;
  margin-bottom: 18px;
}
.au-tab-btn{
  border:0 !important;
  border-radius: 14px !important;
  padding: 10px 18px !important;
  font-weight:900;
  color: var(--muted);
  background: transparent;
  cursor:pointer;
  outline:none;
}
.au-tab-btn.active{
  background: linear-gradient(135deg, rgba(79,124,255,.40), rgba(155,92,255,.25)) !important;
  color:#fff !important;
  box-shadow: 0 12px 26px rgba(0,0,0,.30);
  border: 1px solid rgba(255,255,255,.14) !important;
}

.au-tab-pane{
  display:none;
}
.au-tab-pane.active{
  display:block;
}

.card{
  border: 1px solid var(--stroke) !important;
  border-radius: var(--radius-xl) !important;
  background: linear-gradient(135deg, rgba(255,255,255,.09), rgba(255,255,255,.04)) !important;
  box-shadow: var(--shadow-mid);
  overflow:hidden;
  position:relative;
}
.card:before{
  content:"";
  position:absolute;
  inset:-2px;
  background:
    radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.30), transparent 65%),
    radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.22), transparent 65%);
  pointer-events:none;
}
.card > *{
  position:relative;
  z-index:1;
}

.card-header{
  background: rgba(0,0,0,.18) !important;
  border-bottom: 1px solid var(--stroke) !important;
  padding: 18px 22px !important;
}
.card-header h5{
  font-weight:900 !important;
  color: var(--txt) !important;
  margin:0 !important;
}
.card-body{ padding: 22px !important; }

.au-form-grid{
  display:grid;
  grid-template-columns: 1fr;
  gap:14px;
}
@media(min-width:768px){
  .au-form-grid.md-3{ grid-template-columns: repeat(3, 1fr); }
}

.form-control, .form-control-file, select.form-control{
  border-radius: 14px !important;
  padding: 12px 14px !important;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.22) !important;
  color: var(--txt) !important;
  min-height: 44px;
}
.form-control::placeholder{ color: rgba(255,255,255,.55) !important; }
.form-control:focus, select.form-control:focus{
  border-color: rgba(79,124,255,.55) !important;
  box-shadow: 0 0 0 .15rem rgba(79,124,255,.18) !important;
}
label{
  color: rgba(255,255,255,.72) !important;
  font-weight:900;
}

.btn{
  border-radius: 14px !important;
  padding: 10px 22px !important;
  font-weight: 900 !important;
  border: 1px solid var(--stroke2) !important;
  box-shadow: 0 10px 24px rgba(0,0,0,.25);
}
.btn-primary{
  border-color: rgba(79,124,255,.50) !important;
  background: linear-gradient(135deg, rgba(79,124,255,.40), rgba(155,92,255,.25)) !important;
  color:#fff !important;
}
.btn-danger{
  border-color: rgba(255,91,122,.45) !important;
  background: rgba(255,91,122,.14) !important;
  color:#fff !important;
}
.btn-secondary{
  background: rgba(255,255,255,.06) !important;
  color: var(--txt) !important;
}

#customSearch{
  border-radius: 14px 0 0 14px !important;
}

.table-responsive{
  border-radius: 16px;
  border: 1px solid var(--stroke) !important;
  background: rgba(0,0,0,.16);
  overflow:auto;
}
.table{
  color: var(--txt) !important;
  width:100% !important;
}
.table thead th{
  background: rgba(255,255,255,.06) !important;
  color: rgba(255,255,255,.86) !important;
  border-bottom: 1px solid var(--stroke) !important;
  white-space: nowrap;
}
.table tbody td{
  border-top: 1px solid rgba(255,255,255,.06) !important;
  vertical-align: middle !important;
  color: rgba(255,255,255,.86) !important;
}
.table-hover tbody tr:hover{
  background: rgba(255,255,255,.05) !important;
}

.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_length{
  display:none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button{
  color:#fff !important;
}
.dataTables_wrapper .dataTables_info{
  color: rgba(255,255,255,.75) !important;
}

.colorb{ color:rgba(255,255,255,.90) !important; }

.foto-mini{
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,.15);
}
.sin-foto{
  display: inline-block;
  padding: 6px 10px;
  border-radius: 8px;
  background: rgba(255,255,255,.08);
  color: #fff;
  font-size: 12px;
}
/* =========================
   CORRECCIÓN VISUAL TABLA
========================= */

#dynamictable,
#dynamictable_wrapper,
#dynamictable_wrapper .dataTables_scroll,
#dynamictable_wrapper .dataTables_info,
#dynamictable_wrapper .dataTables_paginate,
#dynamictable_wrapper .dataTables_paginate .paginate_button {
    color: #ffffff !important;
}

#dynamictable thead th {
    color: #ffffff !important;
    background: rgba(12, 18, 35, 0.95) !important;
    border-bottom: 1px solid rgba(255,255,255,0.14) !important;
    font-weight: 300 !important;
}

#dynamictable tbody tr {
    background: rgba(255,255,255,0.04) !important;
}

#dynamictable tbody tr:nth-child(even) {
    background: rgba(255,255,255,0.07) !important;
}

#dynamictable tbody tr:hover {
    background: rgba(79,124,255,0.14) !important;
}

#dynamictable tbody td {
    color: #f5f7ff !important;
    font-weight: 200 !important;
    background: transparent !important;
    border-top: 1px solid rgba(255,255,255,0.06) !important;
    vertical-align: middle !important;
    font-size: 14px !important;
}

#dynamictable tbody td a,
#dynamictable tbody td span,
#dynamictable tbody td div {
    color: #f5f7ff !important;
}

#dynamictable_wrapper .dataTables_info {
    color: rgba(255,255,255,0.82) !important;
    font-weight: 600 !important;
}

#dynamictable_wrapper .dataTables_paginate .paginate_button {
    color: #ffffff !important;
    background: transparent !important;
    border: 1px solid transparent !important;
}

#dynamictable_wrapper .dataTables_paginate .paginate_button.current,
#dynamictable_wrapper .dataTables_paginate .paginate_button.current:hover {
    color: #ffffff !important;
    background: linear-gradient(135deg, rgba(79,124,255,.45), rgba(155,92,255,.30)) !important;
    border: 1px solid rgba(255,255,255,.14) !important;
    border-radius: 10px !important;
}

#dynamictable_wrapper .dataTables_paginate .paginate_button:hover {
    color: #ffffff !important;
    background: rgba(255,255,255,0.08) !important;
    border: 1px solid rgba(255,255,255,.10) !important;
    border-radius: 10px !important;
}

#dynamictable_wrapper .dataTables_empty {
    color: #ffffff !important;
    font-weight: 600 !important;
    text-align: center !important;
}

/* Campo de búsqueda */
#customSearch {
    color: #ffffff !important;
    background: rgba(8, 12, 28, 0.88) !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
}

#customSearch::placeholder {
    color: rgba(255,255,255,0.60) !important;
}

/* Si DataTables mete fondo blanco interno */
table.dataTable tbody tr,
table.dataTable tbody td,
table.dataTable.display tbody tr,
table.dataTable.stripe tbody tr.odd,
table.dataTable.stripe tbody tr.even {
    background-color: transparent !important;
    color: #f5f7ff !important;
}
</style>

<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>

<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>

<div class="pcoded-main-container">
  <div class="pcoded-content">

    <div class="au-topbar">
      <div>
        <br><br>
        <h1 class="au-title">Deportistas</h1>
        <div class="au-subtitle">Configuración general · Gestión y administración de deportistas</div>
      </div>
      <div>
        <?php include './admin/include/btn_back.php'; ?>
      </div>
    </div>

    <div class="au-tabs" id="customTabs">
      <button type="button" class="au-tab-btn active" data-tab="home">Ingresar Deportistas</button>
      <button type="button" class="au-tab-btn" data-tab="profile">Listado de Deportistas</button>
    </div>

    <div id="home" class="au-tab-pane active">
      <div class="card mt-3">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
          <h5 class="mb-0">Formulario de Deportistas</h5>
        </div>

        <div class="card-body">
          <form id="formdeportista" role="form" autocomplete="off" enctype="multipart/form-data">
            <input type="hidden" name="op" id="op" value="">
            <input type="hidden" name="id" id="id" value="">

            <div class="row">
              <div class="col-md-2 col-6">
                <div class="form-group">
                  <label for="tipo_documento">Tipo Doc <span class="text-danger">*</span></label>
                  <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                    <option value="" selected disabled>Seleccione</option>
                    <option value="TI">TI</option>
                    <option value="CC">CC</option>
                    <option value="CE">CE</option>
                    <option value="PPT">PPT</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3 col-6">
                <div class="form-group">
                  <label for="cc">Cédula <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    id="cc"
                    name="cc"
                    placeholder="Ingrese cédula"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="12"
                    oninput="this.value = this.value.replace(/[^0-9]/g,'');"
                    autocomplete="off"
                    required
                  >
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label for="nombre">Nombres Completos <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    id="nombre"
                    name="nombre"
                    placeholder="Ingrese nombres completos"
                    required
                  >
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label for="tbl_disciplina_id">Disciplina <span class="text-danger">*</span></label>
                  <select class="form-control" id="tbl_disciplina_id" name="tbl_disciplina_id" required>
                    <?php echo $option; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="au-form-grid md-3">
              <div class="form-group">
                <label for="contacto">Número de Contacto <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="contacto"
                  name="contacto"
                  placeholder="Ingrese número sin puntos ni comas"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  oninput="this.value = this.value.replace(/[^0-9]/g,'');"
                  autocomplete="off"
                  required
                >
              </div>

              <div class="form-group">
                <label for="nacimiento">Fecha de Nacimiento <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="nacimiento" name="nacimiento" required>
              </div>

              <div class="form-group">
                <label for="tbl_liga_id">Liga <span class="text-danger">*</span></label>
                <select class="form-control" id="tbl_liga_id" name="tbl_liga_id" required>
                  <?php echo $optionLiga; ?>
                </select>
              </div>
            </div>

            <div class="au-form-grid md-3">
              <div class="form-group">
                <label for="valor_view">Valor <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="valor_view" placeholder="Ingrese valor" inputmode="numeric" autocomplete="off" required>
                <input type="hidden" id="valor" name="valor" value="">
              </div>

              <div class="form-group">
                <label for="plazo">Plazo Meses <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="plazo"
                  name="plazo"
                  placeholder="Ingrese número"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  oninput="this.value = this.value.replace(/[^0-9]/g,'');"
                  autocomplete="off"
                  required
                >
              </div>

              <div class="form-group">
                <label for="tipo_deportista">Tipo Deportista <span class="text-danger">*</span></label>
                <select class="form-control" id="tipo_deportista" name="tipo_deportista" required>
                  <option value="">Seleccione</option>
                  <option value="Deportista Olímpico Nivel 1">Deportista Olímpico Nivel 1</option>
                  <option value="Deportista Olímpico Nivel 2">Deportista Olímpico Nivel 2</option>
                  <option value="Deportista Ciclo Olímpico">Deportista Ciclo Olímpico</option>
                  <option value="Deportista Conjunto Ciclo Olímpico">Deportista Conjunto Ciclo Olímpico</option>
                  <option value="Deportista Multimedallista">Deportista Multimedallista</option>
                  <option value="Deportista Excelencia">Deportista Excelencia</option>
                  <option value="Deportista Plata">Deportista Plata</option>
                  <option value="Deportista Podium">Deportista Podium</option>
                  <option value="Deportista Reserva">Deportista Reserva</option>
                  <option value="Oro Deportes de Conjunto">Oro Deportes de Conjunto</option>
                  <option value="Atleta Guia">Atleta Guia</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="img">Subir imagen</label>
              <input type="file" class="form-control-file" id="img" name="img" accept="image/*">
              <div id="previewImage" class="mt-2"></div>
            </div>

            <div class="pt-2 text-center">
              <button type="button" onclick="UTIL.clearForm('formdeportista');" class="btn btn-danger mr-2">Cancelar</button>
              <button type="button" id="createUser" onclick="DEPORTISTA.validateData();" class="btn btn-primary">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div id="profile" class="au-tab-pane">
      <div class="card mt-3 colorb">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between colorb">
          <h5 class="mb-0 colorb">Listado de Deportistas</h5>
        </div>

        <div class="card-body table-border-style">
          <div class="navbar-form buscador-2 mb-3">
            <div class="input-group input-primary">
              <input type="text" id="customSearch" class="form-control" placeholder="Buscar deportista">
              <div class="input-group-append">
                <span class="input-group-text">
                  <i class="feather icon-search"></i>
                </span>
              </div>
            </div>
          </div>

          <div class="table-responsive tabla-informacion tabla-scroll">
            <table class="table table-hover mb-0" id="dynamictable" style="width:100%;">
              <thead>
                <tr>
                  <th>Nombres</th>
                  <th>Identificación</th>
                  <th>Disciplina</th>
                  <th>Tipo Deportista</th>
                  <th>Liga</th>
                  <th>Valor</th>
                  <th>Plazo</th>
                  <th>Foto</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

<?php include 'admin/include/gerenic_script.php'; ?>

<script type="text/javascript" src="./admin/js/lib/data-md5.js"></script>
<script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
<link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />

<script type="text/javascript" src="admin/js/deportista.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputView = document.getElementById('valor_view');
    const inputReal = document.getElementById('valor');

    function formatearPesos(valor) {
        if (!valor) return '';
        return '$ ' + new Intl.NumberFormat('es-CO').format(valor);
    }

    function limpiarNumero(valor) {
        return String(valor).replace(/\D/g, '');
    }

    if (inputView && inputReal) {
        inputView.addEventListener('input', function () {
            const numeroLimpio = limpiarNumero(this.value);
            inputReal.value = numeroLimpio;
            this.value = numeroLimpio ? formatearPesos(numeroLimpio) : '';
        });

        inputView.addEventListener('paste', function () {
            setTimeout(() => {
                const numeroLimpio = limpiarNumero(inputView.value);
                inputReal.value = numeroLimpio;
                inputView.value = numeroLimpio ? formatearPesos(numeroLimpio) : '';
            }, 0);
        });

        if (inputView.form) {
            inputView.form.addEventListener('submit', function () {
                inputReal.value = limpiarNumero(inputView.value);
            });
        }
    }
});
</script>

<script>
var tablaDeportistas = null;
var tablaInicializada = false;

function abrirTab(tabId) {
    document.querySelectorAll('.au-tab-btn').forEach(function(btn){
        btn.classList.remove('active');
    });

    document.querySelectorAll('.au-tab-pane').forEach(function(pane){
        pane.classList.remove('active');
    });

    var boton = document.querySelector('.au-tab-btn[data-tab="' + tabId + '"]');
    var panel = document.getElementById(tabId);

    if (boton) boton.classList.add('active');
    if (panel) panel.classList.add('active');

    if (tabId === 'profile') {
        inicializarTablaDeportistas();
    }
}

function inicializarTablaDeportistas() {
    if (tablaInicializada && tablaDeportistas) {
        tablaDeportistas.ajax.reload(null, false);
        setTimeout(function () {
            tablaDeportistas.columns.adjust().draw(false);
        }, 200);
        return;
    }

    $.fn.dataTable.ext.errMode = 'none';

    tablaDeportistas = $('#dynamictable').DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        autoWidth: false,
        deferRender: true,
        ajax: {
            url: './admin/ajax/listado_deportistas.php?nocache=' + new Date().getTime(),
            type: 'GET',
            dataType: 'json',
            timeout: 15000,
            dataSrc: function (json) {
                console.log('Respuesta listado_deportistas:', json);

                if (!json) {
                    alert('El servidor no devolvió respuesta.');
                    return [];
                }

                if (json.ok === false) {
                    console.error(json);
                    alert('Error al cargar listado: ' + (json.msg || 'Sin detalle'));
                    return [];
                }

                if (!Array.isArray(json.data)) {
                    console.error('Formato inválido:', json);
                    alert('La respuesta del servidor no tiene formato válido.');
                    return [];
                }

                return json.data;
            },
            error: function (xhr, textStatus, errorThrown) {
                console.error('Error AJAX');
                console.error('status:', xhr.status);
                console.error('textStatus:', textStatus);
                console.error('errorThrown:', errorThrown);
                console.error('responseText:', xhr.responseText);

                alert(
                    'No se pudo cargar el listado.\n' +
                    'Código HTTP: ' + xhr.status + '\n' +
                    'Ruta intentada: ./admin/ajax/listado_deportistas.php'
                );
            }
        },
        columns: [
            { data: 'nombre', defaultContent: '' },
            { data: 'cc', defaultContent: '' },
            { data: 'disciplina', defaultContent: '' },
            { data: 'tipo_deportista', defaultContent: '' },
            { data: 'liga', defaultContent: '' },
            { data: 'valor', defaultContent: '' },
            { data: 'plazo', defaultContent: '' },
            { data: 'foto', defaultContent: '', orderable: false, searchable: false }
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay datos disponibles",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último"
            }
        },
        initComplete: function () {
            tablaInicializada = true;
        }
    });

    $('#customSearch').off('keyup').on('keyup', function () {
        if (tablaDeportistas) {
            tablaDeportistas.search(this.value).draw();
        }
    });
}


document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.au-tab-btn').forEach(function(btn){
        btn.addEventListener('click', function () {
            var tabId = this.getAttribute('data-tab');
            abrirTab(tabId);
        });
    });
});
</script>