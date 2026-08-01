<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

requireAnyPermission(['configuracion.roles.view', 'configuracion.roles.manage']);
$canManage = SessionData::hasPermission('configuracion.roles.manage');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Roles y Permisos</title>

  
  <link rel="stylesheet" href="assets/css/roles_permisos_gob360_premium.css">
</head>

<body class="gob360-roles-page">
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-roles-hero" aria-label="Roles y permisos GOB360">
        <div class="g360-roles-hero__grid">
          <aside class="g360-roles-brand">
            <span class="g360-roles-brand__eyebrow">Plataforma institucional</span>
            <img src="assets/img/gob360l.png" alt="Logo GOB360" class="g360-roles-brand__logo">
            <span class="g360-roles-brand__caption">Gestión pública inteligente y territorial</span>
            <div class="g360-roles-brand__status"><span></span> Seguridad RBAC activa</div>
          </aside>

          <div class="g360-roles-hero__content">
            <div class="g360-roles-hero__top">
              <div>
                <div class="g360-roles-hero__eyebrow"><i class="feather icon-shield"></i> Configuración de seguridad</div>
                <h1 class="g360-roles-hero__title">Roles y Permisos</h1>
                <p class="g360-roles-hero__description">Administra perfiles institucionales y define qué módulos, acciones y funciones puede utilizar cada rol dentro de GOB360.</p>
              </div>
              <div class="g360-roles-hero__actions">
                <?php if ($canManage): ?>
                  <button type="button" class="g360-hero-button" onclick="nuevoRol()"><i class="feather icon-plus-circle"></i> Crear nuevo rol</button>
                <?php endif; ?>
                <div><?php include './admin/include/btn_back.php'; ?></div>
              </div>
            </div>

            <div class="g360-roles-summary">
              <article><span class="g360-roles-summary__icon"><i class="feather icon-shield"></i></span><div><small>Modelo de acceso</small><strong>RBAC</strong><p>Control basado en roles</p></div></article>
              <article><span class="g360-roles-summary__icon is-blue"><i class="feather icon-edit-3"></i></span><div><small>Administración</small><strong><?= $canManage ? 'Habilitada' : 'Consulta' ?></strong><p>Según permisos de la sesión</p></div></article>
              <article><span class="g360-roles-summary__icon is-green"><i class="feather icon-key"></i></span><div><small>Asignación</small><strong>Por módulo</strong><p>Permisos agrupados y trazables</p></div></article>
              <article><span class="g360-roles-summary__icon is-yellow"><i class="feather icon-users"></i></span><div><small>Aplicación</small><strong>Usuarios</strong><p>Roles vinculados a cuentas</p></div></article>
            </div>

            <div class="g360-roles-capabilities">
              <span><i class="feather icon-lock"></i> Acceso protegido</span>
              <span><i class="feather icon-grid"></i> Permisos por módulo</span>
              <span><i class="feather icon-tag"></i> Roles del sistema</span>
              <span><i class="feather icon-user-check"></i> Roles personalizados</span>
            </div>
          </div>
        </div>
      </section>

      <div class="row">
        <div class="col-sm-12">
          <div class="card g360-roles-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <div class="g360-card-heading"><span class="g360-card-heading__icon"><i class="feather icon-list"></i></span><div><span class="g360-card-heading__eyebrow">Directorio de seguridad</span><h5 class="mb-0">Listado de roles</h5><p>Consulta roles del sistema, perfiles personalizados, permisos asociados y usuarios vinculados.</p></div></div>
              <div class="d-flex align-items-center gap-2 ml-auto">
                <?php if ($canManage): ?>
                <button type="button" class="btn btn-primary btn-sm g360-new-role-button" onclick="nuevoRol()">
                  <i class="feather icon-plus"></i> Nuevo rol
                </button>
                <?php endif; ?>
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
                      <a href="#!" onclick="cargarRoles(); return false;"><i class="feather icon-refresh-cw"></i> Recargar</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body table-border-style">
              <div class="g360-roles-tools">
                <div class="g360-roles-search"><span class="g360-roles-search__icon"><i class="feather icon-search"></i></span><div><label for="customSearchRoles">Búsqueda rápida</label><input type="text" id="customSearchRoles" class="form-control" placeholder="Buscar por nombre, clave o descripción..."></div></div>
                <div class="g360-roles-tools__info"><i class="feather icon-info"></i><span>Los roles del sistema están protegidos. Los roles personalizados pueden administrarse según su autorización.</span></div>
              </div>

              <div class="table-responsive tabla-informacion g360-roles-table">
                <table class="table table-hover mb-0" id="tablaRoles">
                  <thead>
                    <tr>
                      <th>Acciones</th>
                      <th>Nombre</th>
                      <th>Clave (key)</th>
                      <th>Tipo</th>
                      <th>Permisos</th>
                      <th>Usuarios</th>
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
  </div>

  <!-- Modal rol -->
  <div class="modal fade" id="modalRol" tabindex="-1" role="dialog" aria-labelledby="modalRolTitulo" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content g360-role-modal">
        <div class="modal-header"><div class="g360-modal-heading"><span class="g360-modal-heading__icon"><i class="feather icon-shield"></i></span><div><small>Configuración RBAC</small><h5 class="modal-title" id="modalRolTitulo">Rol</h5></div></div><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
        <div class="modal-body">
          <input type="hidden" id="roleId" value="0">
          <div class="g360-role-modal__intro"><span><i class="feather icon-lock"></i></span><div><strong>Definición del perfil institucional</strong><p>Registra el nombre y la clave técnica del rol, luego selecciona los permisos que estarán habilitados para sus usuarios.</p></div></div>
          <div class="g360-modal-section-heading"><span class="g360-modal-section-heading__icon"><i class="feather icon-tag"></i></span><div><small>Identificación</small><h6>Información del rol</h6></div></div>
          <div class="form-row mb-3">
            <div class="form-group col-md-4">
              <label for="roleName" class="form-label">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="roleName" maxlength="120">
            </div>
            <div class="form-group col-md-4">
              <label for="roleKey" class="form-label">Clave (role_key) <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="roleKey" maxlength="80" placeholder="ej: coordinador_pae">
            </div>
            <div class="form-group col-md-4">
              <label for="roleDescription" class="form-label">Descripción</label>
              <input type="text" class="form-control" id="roleDescription" maxlength="255">
            </div>
          </div>
          <div class="g360-permissions-header"><div class="g360-modal-section-heading g360-modal-section-heading--permissions"><span class="g360-modal-section-heading__icon"><i class="feather icon-key"></i></span><div><small>Autorizaciones</small><h6>Permisos del rol</h6></div></div><?php if ($canManage): ?><div class="g360-permissions-header__actions"><button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleTodosPermisos(true)"><i class="feather icon-check-square"></i> Marcar todos</button><button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleTodosPermisos(false)"><i class="feather icon-square"></i> Desmarcar todos</button></div><?php endif; ?></div>
          <div id="permisosContainer" class="g360-permissions-container"></div>
        </div>
        <div class="modal-footer"><div class="g360-modal-footer-message"><i class="feather icon-shield"></i> Los cambios afectarán a los usuarios que utilicen este rol.</div><div class="g360-modal-footer-actions"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><?php if ($canManage): ?><button type="button" class="btn btn-primary" onclick="guardarRol()"><i class="feather icon-save"></i> Guardar rol</button><?php endif; ?></div></div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  

  <script>
    window.ROLES_PERMISOS = { canManage: <?= $canManage ? 'true' : 'false' ?> };
  </script>
  <script src="<?php echo Util::versionar('admin/js/roles_permisos.js'); ?>"></script>
</body>
</html>
