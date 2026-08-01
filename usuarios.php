<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

require './admin/include/generic_classes.php';
include './admin/classes/Usuario.php';
include './admin/classes/Secretarias.php';
include './admin/classes/Departamento.php';

// Permisos
requirePermission('configuracion.usuarios.view');
$view = SessionData::hasPermission('configuracion.usuarios.view');
$create = SessionData::hasPermission('configuracion.usuarios.create');
$edit = SessionData::hasPermission('configuracion.usuarios.update');
$permits = SessionData::hasPermission('configuracion.usuarios.manage');

$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

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




  <link rel="stylesheet" href="assets/css/usuarios_gob360_premium.css">
</head>

<body class="gob360-users-page">
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

    <section class="g360-users-hero" aria-label="Administración de usuarios GOB360">
      <div class="g360-users-hero__grid">

        <aside class="g360-users-brand">
          <span class="g360-users-brand__eyebrow">
            Plataforma institucional
          </span>

          <img
            src="assets/img/gob360l.png"
            alt="Logo GOB360"
            class="g360-users-brand__logo"
          >

          <span class="g360-users-brand__caption">
            Gestión pública inteligente y territorial
          </span>

          <div class="g360-users-brand__status">
            <span></span>
            Administración protegida
          </div>
        </aside>

        <div class="g360-users-hero__content">
          <div class="g360-users-hero__top">
            <div>
              <div class="g360-users-hero__eyebrow">
                <i class="feather icon-users"></i>
                Configuración general
              </div>

              <h1 class="g360-users-hero__title">
                Administración de Usuarios
              </h1>

              <p class="g360-users-hero__description">
                Crea, consulta y actualiza usuarios institucionales, asigna su tipo,
                dependencia y municipio, y supervisa registros eliminados o duplicados
                según las autorizaciones del perfil activo.
              </p>
            </div>

            <div class="g360-users-hero__actions">
              <?php if ($permits): ?>
                <a href="roles_permisos.php" class="g360-hero-button g360-hero-button--primary">
                  <i class="feather icon-shield"></i>
                  Roles y permisos
                </a>
              <?php endif; ?>

              <div class="g360-users-back">
                <?php include './admin/include/btn_back.php'; ?>
              </div>
            </div>
          </div>

          <div class="g360-users-summary">
            <article>
              <span class="g360-users-summary__icon">
                <i class="feather icon-user-check"></i>
              </span>

              <div>
                <small>Perfil activo</small>
                <strong><?= htmlspecialchars((string)$userType, ENT_QUOTES, 'UTF-8') ?></strong>
                <p>Rol aplicado a la sesión actual</p>
              </div>
            </article>

            <article>
              <span class="g360-users-summary__icon g360-users-summary__icon--permissions">
                <i class="feather icon-key"></i>
              </span>

              <div>
                <small>Permisos de gestión</small>
                <strong>
                  <?= ($create || $edit || $permits) ? 'Habilitados' : 'Consulta' ?>
                </strong>
                <p>Creación, edición y administración</p>
              </div>
            </article>

            <article>
              <span class="g360-users-summary__icon g360-users-summary__icon--secretaries">
                <i class="feather icon-briefcase"></i>
              </span>

              <div>
                <small>Dependencias</small>
                <strong><?= number_format(count($arrSecretarias), 0, ',', '.') ?></strong>
                <p>Secretarías disponibles</p>
              </div>
            </article>

            <article>
              <span class="g360-users-summary__icon g360-users-summary__icon--territory">
                <i class="feather icon-map-pin"></i>
              </span>

              <div>
                <small>Cobertura territorial</small>
                <strong><?= number_format(count($arrDep), 0, ',', '.') ?></strong>
                <p>Departamentos configurados</p>
              </div>
            </article>
          </div>

          <div class="g360-users-capabilities" aria-hidden="true">
            <span>
              <i class="feather icon-user-plus"></i>
              Creación de usuarios
            </span>

            <span>
              <i class="feather icon-edit-3"></i>
              Actualización
            </span>

            <span>
              <i class="feather icon-lock"></i>
              Seguridad de acceso
            </span>

            <span>
              <i class="feather icon-archive"></i>
              Auditoría
            </span>

            <span>
              <i class="feather icon-database"></i>
              Control de duplicados
            </span>
          </div>
        </div>

      </div>
    </section>

    <ul class="nav nav-tabs au-tabs" id="myTab" role="tablist">
      <?php if ($create): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $create ? 'active' : '' ?>" id="home-tab" data-toggle="tab" data-target="#home" type="button"
          role="tab" aria-controls="home" aria-selected="<?= $create ? 'true' : 'false' ?>">
          <i class="feather icon-user-plus"></i>
          <span>Ingresar usuario</span>
        </button>
      </li>
      <?php endif; ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $create ? '' : 'active' ?>" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
          role="tab" aria-controls="profile" aria-selected="<?= $create ? 'false' : 'true' ?>" onclick="USUARIO.cargaData()">
          <i class="feather icon-list"></i>
          <span>Listado de usuarios</span>
        </button>
      </li>
      <?php if ($userType === 'SuperAdministrador'): ?>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="deleted-tab" data-toggle="tab" data-target="#deleted" type="button"
          role="tab" aria-controls="deleted" aria-selected="false" onclick="USUARIO.cargaDeleted()">
          <i class="feather icon-trash-2"></i>
          <span>Eliminados</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="duplicated-tab" data-toggle="tab" data-target="#duplicated" type="button"
          role="tab" aria-controls="duplicated" aria-selected="false" onclick="USUARIO.cargaDuplicados()">
          <i class="feather icon-copy"></i>
          <span>Duplicados</span>
        </button>
      </li>
      <?php endif; ?>
    </ul>

    <div class="tab-content g360-users-tabs-content" id="myTabContent">

      <?php if ($create): ?>
      <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
        <div class="card mt-3 g360-user-card g360-user-card--create">
          <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <div class="g360-card-heading">
              <span class="g360-card-heading__icon">
                <i class="feather icon-user-plus"></i>
              </span>

              <div>
                <span class="g360-card-heading__eyebrow">Nuevo registro</span>
                <h5 class="mb-0">Formulario de usuario</h5>
                <p>
                  Registra la identidad, vinculación institucional y credenciales
                  de acceso del nuevo usuario.
                </p>
              </div>
            </div>

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
            <div class="g360-users-alert mb-3" role="alert">
              <span class="g360-users-alert__icon">
                <i class="feather icon-info"></i>
              </span>

              <div>
                <strong>Asignación mediante roles institucionales</strong>
                <p>
                  Los permisos se aplican según el tipo de usuario.
                  Para una configuración personalizada, utilice
                  <a href="roles_permisos.php" class="alert-link">Roles y Permisos</a>.
                </p>
              </div>
            </div>

        <form id="formusuarios" role="form" autocomplete="off" enctype="multipart/form-data">
              <input type="hidden" name="op" id="op" />
              <input type="hidden" name="id" id="id" />

              <div class="g360-form-section-heading">
                <span class="g360-form-section-heading__icon">
                  <i class="feather icon-user"></i>
                </span>

                <div>
                  <span>Identificación</span>
                  <h6>Datos personales y tipo de usuario</h6>
                  <p>Información básica para identificar el nuevo registro.</p>
                </div>
              </div>

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
                  <label for="tipo">Tipo de Usuario <span class="text-danger">*</span></label>
                  <select class="form-control" id="tipo" name="tipo">
                    <option value="">Seleccione</option>
                    <option value="SuperAdministrador">Super Administrador</option>
                    <option value="Gobernador">Gobernador</option>
                    <option value="Secretaria_Despacho_Gobernacion">Secretario despacho Gobernación</option>
                    <option value="Auxiliar_secret_gob">Auxiliar Secretario Gobernación</option>
                    <option value="Alcalde">Alcalde</option>
                    <option value="Auxiliar_Alcalde">Auxiliar Alcalde</option>
                    <option value="Secretario_Despacho">Secretario Despacho Alcalde</option>
                    <option value="Auxiliar">Auxiliar Secretario Despacho Alcalde</option>
                    <option value="Administrador">Administrador</option>
                  </select>
                </div>
              </div>

              <div class="g360-form-section-heading">
                <span class="g360-form-section-heading__icon g360-form-section-heading__icon--institution">
                  <i class="feather icon-briefcase"></i>
                </span>

                <div>
                  <span>Vinculación institucional</span>
                  <h6>Dependencia y territorio</h6>
                  <p>Relaciona al usuario con su secretaría, entidad o alcaldía.</p>
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

              <div class="g360-form-section-heading">
                <span class="g360-form-section-heading__icon g360-form-section-heading__icon--access">
                  <i class="feather icon-at-sign"></i>
                </span>

                <div>
                  <span>Cuenta institucional</span>
                  <h6>Usuario, correo y estado</h6>
                  <p>Define las credenciales visibles y si la cuenta queda habilitada.</p>
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

              <div class="g360-form-section-heading">
                <span class="g360-form-section-heading__icon g360-form-section-heading__icon--security">
                  <i class="feather icon-lock"></i>
                </span>

                <div>
                  <span>Seguridad</span>
                  <h6>Contraseña e imagen del perfil</h6>
                  <p>Configura una contraseña segura y una fotografía opcional.</p>
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

              <div class="g360-user-save-bar">
                <div class="g360-user-save-bar__message">
                  <i class="feather icon-shield"></i>
                  <span>
                    Verifica tipo de usuario, dependencia, territorio y credenciales
                    antes de guardar.
                  </span>
                </div>

                <div class="g360-user-save-bar__actions">
                  <button
                    type="button"
                    onclick="UTIL.clearForm('formusuarios');"
                    class="btn btn-danger"
                  >
                    <i class="feather icon-x"></i>
                    Cancelar
                  </button>

                  <button
                    type="button"
                    id="createUser"
                    onclick="USUARIO.validateData();"
                    class="btn btn-primary"
                  >
                    <i class="feather icon-save"></i>
                    Guardar usuario
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($userType === 'SuperAdministrador'): ?>
      <div class="tab-pane fade" id="deleted" role="tabpanel" aria-labelledby="deleted-tab">
        <div class="card mt-3 g360-user-card g360-user-card--audit">
          <div class="card-header">
            <div class="g360-card-heading">
              <span class="g360-card-heading__icon g360-card-heading__icon--deleted">
                <i class="feather icon-trash-2"></i>
              </span>

              <div>
                <span class="g360-card-heading__eyebrow">Auditoría de seguridad</span>
                <h5 class="mb-0">Usuarios eliminados</h5>
                <p>Consulta quién eliminó cada registro y la fecha de la acción.</p>
              </div>
            </div>
          </div>
          <div class="card-body table-border-style">
            <div class="table-responsive tabla-informacion tabla-scroll g360-users-table">
              <table class="table table-hover mb-0" id="dynamictable-deleted">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Tipo</th>
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
        <div class="card mt-3 g360-user-card g360-user-card--duplicates">
          <div class="card-header">
            <div class="g360-card-heading">
              <span class="g360-card-heading__icon g360-card-heading__icon--duplicated">
                <i class="feather icon-copy"></i>
              </span>

              <div>
                <span class="g360-card-heading__eyebrow">Control de calidad</span>
                <h5 class="mb-0">Usuarios duplicados</h5>
                <p>Identifica registros que comparten el mismo nombre de usuario.</p>
              </div>
            </div>
          </div>
          <div class="card-body table-border-style">
            <div class="table-responsive tabla-informacion tabla-scroll g360-users-table">
              <table class="table table-hover mb-0" id="dynamictable-duplicated">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Tipo</th>
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
        <div class="card mt-3 g360-user-card g360-user-card--list">
          <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <div class="g360-card-heading">
              <span class="g360-card-heading__icon g360-card-heading__icon--list">
                <i class="feather icon-list"></i>
              </span>

              <div>
                <span class="g360-card-heading__eyebrow">Directorio institucional</span>
                <h5 class="mb-0">Listado de usuarios</h5>
                <p>Consulta, filtra y gestiona las cuentas registradas en la plataforma.</p>
              </div>
            </div>

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
            <div class="g360-users-search">
              <span class="g360-users-search__icon">
                <i class="feather icon-search"></i>
              </span>

              <div>
                <label for="customSearch">Búsqueda rápida</label>
                <input
                  type="text"
                  id="customSearch"
                  class="form-control"
                  placeholder="Buscar por nombre, usuario, tipo o dependencia..."
                >
              </div>
            </div>

            <div class="table-responsive tabla-informacion tabla-scroll g360-users-table g360-users-table--main">
              <table class="table table-hover mb-0" id="dynamictable">
                <thead>
                  <tr class="border-1">
                    <th>Editar</th>
                    <th>ID</th>
                    <th>Fecha creación</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Tipo</th>
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
    <div class="modal-content text-center g360-image-modal">
      <div class="modal-body">
        <img id="imagenGrande" src="" class="img-fluid rounded" alt="Foto">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content g360-edit-user-modal">
      <div class="modal-header">
        <div class="g360-modal-heading">
          <span class="g360-modal-heading__icon">
            <i class="feather icon-edit-3"></i>
          </span>

          <div>
            <small>Actualización de cuenta</small>
            <h5 class="modal-title" id="exampleModalLongTitle">Editar usuario</h5>
          </div>
        </div>

        <button
          onclick="UTIL.clearForm('formpermission');"
          type="button"
          class="close text-white"
          data-dismiss="modal"
          aria-label="Cerrar"
        >
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body p-4">
        <div class="g360-edit-modal-intro">
          <span>
            <i class="feather icon-shield"></i>
          </span>

          <div>
            <strong>Actualización controlada</strong>
            <p>
              Modifica la información necesaria. Deja las contraseñas vacías
              para conservar las credenciales actuales.
            </p>
          </div>
        </div>

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
              <label for="editTipo">Tipo de Usuario <span class="text-danger">*</span></label>
              <select class="form-control" id="editTipo" name="editTipo">
                <option value="">Seleccione</option>
                <option value="SuperAdministrador">Super Administrador</option>
                <option value="Gobernador">Gobernador</option>
                <option value="Secretaria_Despacho_Gobernacion">Secretario despacho Gobernación</option>
                <option value="Auxiliar_secret_gob">Auxiliar Secretario Gobernación</option>
                <option value="Alcalde">Alcalde</option>
                <option value="Auxiliar_Alcalde">Auxiliar Alcalde</option>
                <option value="Secretario_Despacho">Secretario Despacho Alcalde</option>
                <option value="Auxiliar">Auxiliar Secretario Despacho Alcalde</option>
                <option value="Administrador">Administrador</option>
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

      <div class="modal-footer">
        <div class="g360-modal-footer-message">
          <i class="feather icon-lock"></i>
          Los cambios quedarán registrados en el sistema.
        </div>

        <div class="g360-modal-footer-actions">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            Cancelar
          </button>

          <button
            type="button"
            id="btnGuardarEditar"
            class="btn btn-primary"
            onclick="USUARIO.editUserSave();"
          >
            <i class="feather icon-save"></i>
            Actualizar usuario
          </button>
        </div>
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
