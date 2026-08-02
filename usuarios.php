<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

require './admin/include/generic_classes.php';
include './admin/classes/Usuario.php';
include './admin/classes/Secretarias.php';
include './admin/classes/Departamento.php';
require_once './admin/classes/Role.php';

// Permisos
requirePermission('configuracion.usuarios.view');
$view = SessionData::hasPermission('configuracion.usuarios.view');
$create = SessionData::hasPermission('configuracion.usuarios.create');
$edit = SessionData::hasPermission('configuracion.usuarios.update');
$permits = SessionData::hasPermission('configuracion.usuarios.manage');

$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

// Roles (select único — plan roles/permisos)
$optionRoles = Role::buildUsuarioRoleOptionsHtml();

// Secretarias
$arrSecretarias = Secretarias::getAll(null);
$arrSecretarias = $arrSecretarias['output']['response'] ?? [];
$option = '<option value="Seleccione" selected>Seleccione</option>';
foreach ($arrSecretarias as $val) {
    $option .= "<option value='" . $val['id'] . "'>" . $val['secretaria'] . "</option>";
}

// Departamentos
$arrDep = Departamento::getAll(null);
$arrDep = $arrDep['output']['response'] ?? [];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") .
        " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
/* ===============================
   ACCIÓN UNIFICADA – GOVTECH WOW
   (SOLO VISTA, NO TOCA LÓGICA)
================================ */

/* ====== THEME DARK GLASS ====== */
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

/* fondo */
body{
  background:
    radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
    radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
    radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
    linear-gradient(180deg, var(--bg0), var(--bg1)) !important;
  color: var(--txt);
  overflow-x:hidden;
}

/* spacing */
.pcoded-main-container{ background: transparent !important; }
.pcoded-content{ padding: 16px 16px !important; }
@media(min-width:768px){ .pcoded-content{ padding: 24px 24px !important; } }
@media(min-width:1200px){ .pcoded-content{ padding: 34px 42px !important; } }

/* TOPBAR */
.au-topbar{
  display:flex; flex-direction:column; gap:10px;
  margin-bottom:18px;
  padding-top: 20px;
}
@media(min-width:768px){
  .au-topbar{ flex-direction:row; align-items:center; justify-content:space-between; }
}
.au-title{
  margin:0;
  font-weight:900;
  font-size:1.55rem;
  letter-spacing:.2px;
  color: #fff !important;
}
.au-subtitle{
  margin:4px 0 0;
  color: rgba(255,255,255,.70);
  font-size:.92rem;
}

/* tabs */
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
.au-tabs .nav-link{
  border:0 !important;
  border-radius: 14px !important;
  padding: 10px 18px !important;
  font-weight:900;
  color: var(--muted);
  background: transparent;
}
.au-tabs .nav-link.active{
  background: linear-gradient(135deg, rgba(79,124,255,.40), rgba(155,92,255,.25)) !important;
  color:#fff !important;
  box-shadow: 0 12px 26px rgba(0,0,0,.30);
  border: 1px solid rgba(255,255,255,.14) !important;
}

/* cards */
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
  position:absolute; inset:-2px;
  background:
    radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.30), transparent 65%),
    radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.22), transparent 65%);
  pointer-events:none;
}
.card > *{ position:relative; z-index:1; }

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

/* form grid */
.au-form-grid{
  display:grid;
  grid-template-columns: 1fr;
  gap: 14px;
}
@media(min-width:768px){
  .au-form-grid.md-3{ grid-template-columns: repeat(3, 1fr); }
}

/* inputs */
.form-control, .form-control-file, select.form-control{
  border-radius: 14px !important;
  padding: 12px 14px !important;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.22) !important;
  color: var(--txt) !important;
  min-height: 44px;
}
/* Evita opciones ilegibles (texto blanco sobre blanco) en el desplegable nativo */
select.form-control option{
  color: #0B1B38 !important;
  background: #fff !important;
}
.form-control::placeholder{ color: rgba(255,255,255,.55) !important; }
.form-control:focus, select.form-control:focus{
  border-color: rgba(79,124,255,.55) !important;
  box-shadow: 0 0 0 .15rem rgba(79,124,255,.18) !important;
}
label{
  color: rgba(255,255,255,.72) !important;
  font-weight: 900;
}

/* ojo password */
.input-group .input-group-text{
  border-radius: 0 14px 14px 0 !important;
  cursor: pointer;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.30) !important;
  color: var(--txt) !important;
}

/* buttons */
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

/* buscador */
#customSearch{ border-radius: 14px 0 0 14px !important; }
.buscador-2 .input-group-text{ border-radius: 0 14px 14px 0 !important; }

/* tabla oscura → dark_theme + DT_OVERRIDE manejan esto */
.table-responsive{
  border-radius: 16px;
  border: 1px solid rgba(255,255,255,.10) !important;
  background: transparent !important;
  overflow:auto;
}
.table{ color: rgba(255,255,255,.86) !important; }
.table thead th{
  background: rgba(255,255,255,.06) !important;
  color: rgba(255,255,255,.88) !important;
  border-bottom: 1px solid rgba(255,255,255,.10) !important;
  white-space: nowrap;
}
.table tbody td{
  border-top: 1px solid rgba(255,255,255,.06) !important;
  vertical-align: middle !important;
  color: rgba(255,255,255,.86) !important;
}
.table-hover tbody tr:hover{ background: rgba(79,124,255,.08) !important; }
.table .btn-sm i{ color: #fff !important; }
.table .btn-sm{
  border-radius: 8px !important;
  padding: 6px 10px !important;
  min-width: 36px;
}
.table .btn-info{ background: #0d6efd !important; border-color: #0d6efd !important; }
.table .btn-warning{ background: #fd7e14 !important; border-color: #fd7e14 !important; }
.table .btn-danger{ background: #dc3545 !important; border-color: #dc3545 !important; }
.table .btn-success{ background: #198754 !important; border-color: #198754 !important; }
.table .btn-info:hover{ background: #0b5ed7 !important; }
.table .btn-warning:hover{ background: #e8590c !important; }
.table .btn-danger:hover{ background: #bb2d3b !important; }
.table .btn-success:hover{ background: #157347 !important; }

/* ====== MODALES NEGROS (TODOS) ====== */
/* Overlay negro más fuerte */
.modal-backdrop.show{
  opacity: .85 !important;
}
.modal-backdrop{
  background: #000 !important;
}

/* Modal base oscuro */
.modal-content{
  border-radius: 18px !important;
  border: 1px solid rgba(255,255,255,.14) !important;
  background: linear-gradient(135deg, rgba(20,24,35,.92), rgba(10,12,18,.92)) !important;
  color: var(--txt) !important;
  box-shadow: var(--shadow-mid);
  overflow:hidden;
}
.modal-header{
  border-bottom: 1px solid rgba(255,255,255,.12) !important;
  background: rgba(0,0,0,.35) !important;
}
.modal-title{
  font-weight: 900 !important;
  letter-spacing:.2px;
  color:#fff !important;
}
.modal-footer{
  border-top: 1px solid rgba(255,255,255,.12) !important;
  background: rgba(0,0,0,.25) !important;
}
.close, .close span{
  color:#fff !important;
  opacity: 1 !important;
  text-shadow:none !important;
}

/* Tu editModal tiene bg-dark y myModalPermisos tiene verde inline: no los tocamos,
   pero garantizamos que el contenedor siga oscuro y legible */
.modal-header.bg-dark{ background: rgba(0,0,0,.55) !important; }

/* Permisos: conserva el verde por inline, solo mejoramos contraste interno */
#myModalPermisos .modal-body{ color: var(--txt) !important; }
#myModalPermisos table{ background: rgba(255,255,255,.06) !important; }
#myModalPermisos thead th{ color: rgba(255,255,255,.90) !important; }

/* Modal imagen: centrado con fondo negro real */
#modalImagen .modal-content{
  background: rgba(0,0,0,.92) !important;
  border: 1px solid rgba(255,255,255,.18) !important;
}
#modalImagen .modal-body{ padding: 18px !important; }
</style>

<style>
  /* preview legacy (se conserva) */
  #preview { max-width: 50%; height: auto; display: none; margin-top: 10px; }
</style>
</head>

<body class="">
<!-- [ Pre-loader ] start -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>
<!-- [ Pre-loader ] End -->

<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>

<div class="pcoded-main-container">
  <div class="pcoded-content">

    <div class="au-topbar">
      <div>
        <h1 class="au-title">Usuarios</h1>
        <div class="au-subtitle">Configuración general · Gestión y administración de usuarios</div>
      </div>
      <div>
        <?php include './admin/include/btn_back.php'; ?>
      </div>
    </div>

    <ul class="nav nav-tabs au-tabs" id="myTab" role="tablist">
      <?php if ($create): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $create ? 'active' : '' ?>" id="home-tab" data-toggle="tab" data-target="#home" type="button"
          role="tab" aria-controls="home" aria-selected="<?= $create ? 'true' : 'false' ?>">Ingresar usuario</button>
      </li>
      <?php endif; ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $create ? '' : 'active' ?>" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
          role="tab" aria-controls="profile" aria-selected="<?= $create ? 'false' : 'true' ?>" onclick="USUARIO.cargaData()">Listado de usuarios</button>
      </li>
      <?php if ($userType === 'SuperAdministrador'): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="deleted-tab" data-toggle="tab" data-target="#deleted" type="button"
          role="tab" aria-controls="deleted" aria-selected="false" onclick="USUARIO.cargaDeleted()">Eliminados</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="duplicated-tab" data-toggle="tab" data-target="#duplicated" type="button"
          role="tab" aria-controls="duplicated" aria-selected="false" onclick="USUARIO.cargaDuplicados()">Duplicados</button>
      </li>
      <?php endif; ?>
    </ul>

    <div class="tab-content" id="myTabContent">

      <?php if ($create): ?>
      <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <div class="card mt-3">
          <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h5 class="mb-0">Formulario de usuario</h5>

            <div class="card-header-right ml-auto">
              <div class="btn-group card-option">
                <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="feather icon-more-horizontal"></i>
                </button>
                <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                  <li class="dropdown-item full-card">
                    <a href="#!">
                      <span><i class="feather icon-maximize"></i> Maximizar</span>
                      <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                    </a>
                  </li>
                  <li class="dropdown-item minimize-card">
                    <a href="#!">
                      <span><i class="feather icon-minus"></i> Colapsar</span>
                      <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                    </a>
                  </li>
                  <li class="dropdown-item reload-card">
                    <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                  </li>
                  <li class="dropdown-item close-card">
                    <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                  </li>
                </ul>
              </div>
            </div>

          </div>

          <div class="card-body">
            <div class="au-alert au-alert-info mb-3" role="alert">
          <i class="feather icon-info"></i>
          Los permisos de acceso se asignan mediante el <strong>rol</strong> del usuario.
          Para personalizar permisos, use <a href="roles_permisos.php" class="alert-link">Roles y Permisos</a>.
        </div>

        <form id="formusuarios" role="form" autocomplete="off" enctype="multipart/form-data">
              <input type="hidden" name="op" id="op" />
              <input type="hidden" name="id" id="id" />

              <div class="au-form-grid md-3">
                <div class="form-group">
                  <label for="nombre">Nombres <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese nombres" value="" required>
                </div>

                <div class="form-group">
                  <label for="apellido">Apellidos <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ingrese apellidos" value="" required>
                </div>

                <div class="form-group">
                  <label for="role_id">Rol <span class="text-danger">*</span></label>
                  <select class="form-control" id="role_id" name="role_id">
                    <?= $optionRoles ?>
                  </select>
                </div>
              </div>

              <div class="au-form-grid md-3">
                <div class="form-group">
                  <label for="tbl_secretarias_id">Secretaria o Dependencia <span class="text-danger">*</span></label>
                  <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id">
                    <?php echo $option; ?>
                  </select>
                </div>

                <div class="form-group" style="display: none;">
                  <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                  <select onchange="DEPARTAMENTO.getMunicipios();" class="form-control" id="tbl_departamento_id" name="tbl_departamento_id">
                    <?php echo $optionDep; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="tbl_municipio_id">Alcaldía <span class="text-danger">*</span></label>
                  <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id"></select>
                </div>
              </div>

              <div class="au-form-grid md-3">
                <div class="form-group">
                  <label for="nickname">Usuario <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="nickname" name="nickname" placeholder="(ej: xxx@correo.com)" value="" required>
                </div>

                <div class="form-group">
                  <label for="email">Correo electrónico <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Ingrese su correo electrónico" value="" required>
                </div>

                <div class="form-group">
                  <label for="habilitado">Habilitado <span class="text-danger">*</span></label>
                  <select class="form-control" id="habilitado" name="habilitado">
                    <option value="">Seleccione</option>
                    <option value="si">Sí</option>
                    <option value="no">No</option>
                  </select>
                </div>
              </div>

              <div class="au-form-grid md-3">
                <div class="form-group">
                  <label for="hashpass">Contraseña <span class="text-danger">*</span></label>
                  <div class="input-group" style="margin: 0;">
                    <input type="password" class="form-control" id="hashpass" name="hashpass" placeholder="Ingrese una contraseña" autocomplete="new-password" required>
                    <div class="input-group-append">
                      <span class="input-group-text" onclick="togglePassword('hashpass', this)">
                        <i class="feather icon-eye"></i>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="hashpass1">Repita la Contraseña <span class="text-danger">*</span></label>
                  <div class="input-group" style="margin: 0;">
                    <input type="password" class="form-control" id="hashpass1" name="hashpass1" placeholder="Ingrese nuevamente la contraseña" autocomplete="new-password" required>
                    <div class="input-group-append">
                      <span class="input-group-text" onclick="togglePassword('hashpass1', this)">
                        <i class="feather icon-eye"></i>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="img">Subir imagen</label>
                  <input type="file" class="form-control-file" id="img" name="img" accept="image/*">
                  <div id="previewImage" class="mt-2"></div>
                </div>
              </div>

              <div class="pt-2 text-center">
                <button type="button" onclick="UTIL.clearForm('formusuarios');" class="btn btn-danger mr-2">Cancelar</button>
                <button type="button" id="createUser" onclick="USUARIO.validateData();" class="btn btn-primary">Guardar</button>
              </div>

            </form>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($userType === 'SuperAdministrador'): ?>
      <div class="tab-pane fade" id="deleted" role="tabpanel" aria-labelledby="deleted-tab">
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="mb-0">Usuarios eliminados</h5>
            <small style="color:var(--muted);">Auditoría de seguridad</small>
          </div>
          <div class="card-body table-border-style">
            <div class="table-responsive tabla-informacion tabla-scroll">
              <table class="table table-hover mb-0" id="dynamictable-deleted">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Eliminado</th>
                    <th>Eliminado por</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="duplicated" role="tabpanel" aria-labelledby="duplicated-tab">
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="mb-0">Usuarios duplicados</h5>
            <small style="color:var(--muted);">Usuarios que comparten el mismo username</small>
          </div>
          <div class="card-body table-border-style">
            <div class="table-responsive tabla-informacion tabla-scroll">
              <table class="table table-hover mb-0" id="dynamictable-duplicated">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Rol</th>
                    <th>Secretaría</th>
                    <th>Habilitado</th>
                    <th>Creado</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="tab-pane fade <?= $create ? '' : 'show active' ?>" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <div class="card mt-3">
          <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h5 class="mb-0">Listado de usuarios</h5>

            <div class="card-header-right ml-auto">
              <div class="btn-group card-option">
                <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="feather icon-more-horizontal"></i>
                </button>
                <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                  <li class="dropdown-item full-card">
                    <a href="#!">
                      <span><i class="feather icon-maximize"></i> Maximizar</span>
                      <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                    </a>
                  </li>
                  <li class="dropdown-item minimize-card">
                    <a href="#!">
                      <span><i class="feather icon-minus"></i> Colapsar</span>
                      <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                    </a>
                  </li>
                  <li class="dropdown-item reload-card">
                    <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                  </li>
                  <li class="dropdown-item close-card">
                    <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                  </li>
                </ul>
              </div>
            </div>

          </div>

          <div class="card-body table-border-style">
            <div class="navbar-form buscador-2 mb-3">
              <div class="input-group input-primary">
                <input type="text" id="customSearch" class="form-control" placeholder="Buscar usuario">
                <div class="input-group-append">
                  <span class="input-group-text">
                    <i class="feather icon-edit"></i>
                  </span>
                </div>
              </div>
            </div>

            <div class="table-responsive tabla-informacion tabla-scroll">
              <table class="table table-hover mb-0" id="dynamictable">
                <thead>
                  <tr class="border-1">
                    <th>Editar</th>
                    <th>ID</th>
                    <th>Fecha creación</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Rol</th>
                    <th>Secretaria o entidad</th>
                    <th>Usuario</th>
                    <th>Habilitado</th>
                    <th>Foto</th>
                  </tr>
                </thead>
              </table>
            </div>

          </div>
        </div>
      </div>

    </div><!-- tab-content -->
  </div>
</div>

<!-- MODALES (solo diseño/compatibilidad) -->
<div class="modal fade" id="modalImagen" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-body">
        <img id="imagenGrande" src="" class="img-fluid rounded" alt="Foto">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="exampleModalLongTitle">Editar Usuario</h5>
        <button onclick="UTIL.clearForm('formpermission');" type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>

      <div class="modal-body p-4">
        <form id="editFormUser" role="form" autocomplete="false" class="w-100">
          <input type="hidden" name="editId" id="editId" />

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="editNombre">Nombres <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editNombre" name="editNombre" placeholder="Ingrese nombres" required>
            </div>

            <div class="form-group col-md-4">
              <label for="editApellido">Apellidos <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editApellido" name="editApellido" placeholder="Ingrese apellidos" required>
            </div>

            <div class="form-group col-md-4">
              <label for="editRole_id">Rol <span class="text-danger">*</span></label>
              <select class="form-control" id="editRole_id" name="editRole_id">
                <?= $optionRoles ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="editTbl_secretarias_id">Secretaría o Dependencia <span class="text-danger">*</span></label>
              <select class="form-control" id="editTbl_secretarias_id" name="editTbl_secretarias_id"></select>
            </div>

            <div class="form-group col-md-4">
              <label for="editEmail">Correo electrónico</label>
              <input type="email" class="form-control" id="editEmail" name="editEmail" placeholder="Correo electrónico" required>
            </div>

            <div class="form-group col-md-4" style="display:none">
              <label for="editTbl_departamento_id">Departamento <span class="text-danger">*</span></label>
              <select onchange="DEPARTAMENTO.getMunicipios();" class="form-control" id="editTbl_departamento_id" name="editTbl_departamento_id"></select>
            </div>

            <div class="form-group col-md-4">
              <label for="editTbl_municipio_id_copy">Alcaldía <span class="text-danger">*</span></label>
              <select class="form-control" id="editTbl_municipio_id_copy" name="editTbl_municipio_id_copy"></select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="editNickname">Usuario <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="editNickname" name="editNickname" placeholder="Formato válido de usuario" required>
            </div>

            <div class="form-group col-md-4">
              <label for="editHashpass">Contraseña <span class="text-danger">*</span></label>
              <div class="input-group w-100" style="margin: 0;">
                <input type="password" class="form-control" id="editHashpass" name="editHashpass" placeholder="Dejar vacío para mantener la actual" autocomplete="new-password">
                <div class="input-group-append">
                  <span class="input-group-text" onclick="togglePassword('editHashpass', this)">
                    <i class="feather icon-eye"></i>
                  </span>
                </div>
              </div>
            </div>

            <div class="form-group col-md-4">
              <label for="editHashpass1">Repita la Contraseña <span class="text-danger">*</span></label>
              <div class="input-group w-100" style="margin: 0;">
                <input type="password" class="form-control" id="editHashpass1" name="editHashpass1" placeholder="Dejar vacío para mantener la actual" autocomplete="new-password">
                <div class="input-group-append">
                  <span class="input-group-text" onclick="togglePassword('editHashpass1', this)">
                    <i class="feather icon-eye"></i>
                  </span>
                </div>
              </div>
            </div>

            <div class="form-group col-md-4">
              <label for="editHabilitado">Habilitado <span class="text-danger">*</span></label>
              <select class="form-control" id="editHabilitado" name="editHabilitado">
                <option value="">Seleccione</option>
                <option value="si">Sí</option>
                <option value="no">No</option>
              </select>
            </div>

            <div class="form-group col-md-4">
              <label for="editImg">Imagen</label>
              <input type="file" class="form-control-file" id="editImg" name="editImg" accept="image/*">
              <div id="editPreviewImage" class="mt-2"></div>
            </div>
          </div>

        </form>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" id="btnGuardarEditar" class="btn btn-primary" onclick="USUARIO.editUserSave();">Actualizar</button>
      </div>
    </div>
  </div>
</div>

<?php include 'admin/include/gerenic_script.php'; ?>

<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/pcoded.min.js"></script>

<script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
<script src="<?php echo Util::versionar('./admin/js/lib/data-md5.js'); ?>"></script>

<script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
<link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
        <style>
      table.dataTable tbody tr{
        background-color: transparent !important;
      }
      table.dataTable.stripe tbody tr.odd,
      table.dataTable.display tbody tr.odd{
        background-color: rgba(255,255,255,.03) !important;
      }
      table.dataTable tbody td{
        color: rgba(255,255,255,.86) !important;
      }
      table.dataTable tbody td a{
        color: rgba(255,255,255,.86) !important;
      }
      table.dataTable tbody td i.feather,
      table.dataTable tbody td i.bi{
        color: rgba(255,255,255,.86) !important;
      }
      #tblVeredas td i.feather{
        color: rgba(255,255,255,.86) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button{
        color: rgba(255,255,255,.86) !important;
        background: rgba(255,255,255,.06) !important;
        border: 1px solid rgba(255,255,255,.10) !important;
        border-radius: 8px !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.current,
      .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
        color: #fff !important;
        background: rgba(31,111,235,.35) !important;
        border: 1px solid rgba(31,111,235,.50) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        color: #fff !important;
        background: rgba(255,255,255,.12) !important;
        border: 1px solid rgba(255,255,255,.20) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
        color: rgba(255,255,255,.30) !important;
        background: transparent !important;
        border: 1px solid transparent !important;
      }
      .dataTables_wrapper .dataTables_info,
      .dataTables_wrapper .dataTables_length label{
        color: #fff !important;
      }
      table.dataTable tbody tr.selected{
        background-color: rgba(31,111,235,.25) !important;
      }
    </style>
<script>
  var currentUserType = '<?= $userType ?>';
  var USER_PERMS = {
    view: <?= $view ? 'true' : 'false' ?>,
    create: <?= $create ? 'true' : 'false' ?>,
    edit: <?= $edit ? 'true' : 'false' ?>,
    manage: <?= $permits ? 'true' : 'false' ?>
  };
</script>
<script src="<?php echo Util::versionar('./admin/js/usuario.js'); ?>"></script>

<script>
  // ====== COMPATIBILIDAD MODALES BS4/BS5 (NO TOCA TU BACK) ======
  (function () {
    function byId(id){ return document.getElementById(id); }

    function showModal(id){
      var el = byId(id);
      if (!el) return;
      // Bootstrap 5
      if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
        return;
      }
      // Bootstrap 4 (jQuery)
      if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
        jQuery(el).modal('show');
      }
    }

    function hideModal(el){
      if (!el) return;
      if (window.bootstrap && window.bootstrap.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).hide();
        return;
      }
      if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
        jQuery(el).modal('hide');
      }
    }

    // Bridge: si data-toggle no funciona (por BS5), lo hacemos funcionar
    document.addEventListener('click', function(e){
      var btn = e.target.closest('[data-toggle="modal"][data-target]');
      if (!btn) return;

      // Si es BS4 puro, dejalo actuar
      if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') return;

      e.preventDefault();
      var target = btn.getAttribute('data-target') || '';
      if (target && target.startsWith('#')) target = target.slice(1);
      if (target) showModal(target);
    }, true);

    // Bridge para cerrar con data-dismiss si no existe jQuery modal
    document.addEventListener('click', function(e){
      var btn = e.target.closest('[data-dismiss="modal"]');
      if (!btn) return;

      // BS4 puro: dejalo actuar
      if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') return;

      e.preventDefault();
      var modalEl = btn.closest('.modal');
      hideModal(modalEl);
    }, true);

    // Evita bugs de scroll al cerrar (algunas plantillas)
    document.addEventListener('hidden.bs.modal', function () {
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
      var bd = document.querySelector('.modal-backdrop');
      if (bd) bd.remove();
    }, true);
  })();

  // Clear form on page load to prevent browser autofill
  $(function() { document.getElementById('formusuarios').reset(); document.getElementById('img').value = ''; });
  function togglePassword(fieldId, el) {
    const input = document.getElementById(fieldId);
    const icon = el.querySelector('i');
    if (!input) return;

    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('icon-eye');
      icon.classList.add('icon-eye-off');
    } else {
      input.type = 'password';
      icon.classList.remove('icon-eye-off');
      icon.classList.add('icon-eye');
    }
  }
</script>

</body>
</html>
