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

    .btn-group.card-option .btn{
      border-radius: 12px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.20) !important;
      color: var(--txt) !important;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }

    .form-control{
      border-radius: 14px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      background: rgba(0,0,0,.28) !important;
      color: var(--txt) !important;
      padding: 12px 14px !important;
      min-height: 46px;
      box-shadow:none !important;
    }
    .form-control::placeholder{ color: rgba(255,255,255,.50) !important; }
    .form-control:focus{
      border-color: rgba(79,124,255,.55) !important;
      box-shadow: 0 0 0 .2rem rgba(79,124,255,.18) !important;
    }
    label, .form-label{ color: rgba(255,255,255,.72) !important; font-weight: 900; }

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
    .btn-secondary, .btn-outline-secondary{
      background: rgba(255,255,255,.06) !important;
      color: var(--txt) !important;
    }
    .btn-danger{
      border-color: rgba(255,91,122,.45) !important;
      background: linear-gradient(135deg, rgba(255,91,122,.22), rgba(0,0,0,.22)) !important;
      color:#fff !important;
    }
    .btn-sm{ padding: 6px 12px !important; font-size: 12px !important; }

    #customSearchRoles{
      border-radius: 14px 0 0 14px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.22) !important;
      color: var(--txt) !important;
      min-height: 44px;
    }
    .buscador-2 .input-group-text{
      border-radius: 0 14px 14px 0 !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.30) !important;
      color: var(--txt) !important;
      min-height: 44px;
    }

    /* Tabla oscura (alineada con usuarios.php + dark_theme) */
    .table-responsive{
      border-radius: 16px;
      border: 1px solid rgba(255,255,255,.10) !important;
      background: transparent !important;
      overflow: auto;
      margin-top: 14px;
    }
    #tablaRoles,
    #tablaRoles thead,
    #tablaRoles tbody,
    #tablaRoles tbody tr,
    #tablaRoles tbody td,
    #tablaRoles thead th,
    .table-border-style .table,
    .table-border-style .table tbody,
    .table-border-style .table tbody tr,
    .table-border-style .table tbody td{
      background-color: transparent !important;
      background: transparent !important;
    }
    .table{
      margin-bottom: 0 !important;
      color: rgba(255,255,255,.86) !important;
      --bs-table-bg: transparent;
      --bs-table-striped-bg: rgba(255,255,255,.03);
      --bs-table-hover-bg: rgba(79,124,255,.08);
      --bs-table-color: rgba(255,255,255,.86);
      --bs-table-border-color: rgba(255,255,255,.08);
    }
    .table thead th{
      background: rgba(255,255,255,.06) !important;
      color: rgba(255,255,255,.88) !important;
      border-bottom: 1px solid rgba(255,255,255,.10) !important;
      border-top: none !important;
      white-space: nowrap;
      font-weight: 900;
    }
    .table tbody tr{
      background: transparent !important;
      transition: background .15s ease;
    }
    .table tbody tr:nth-child(even){
      background: rgba(255,255,255,.02) !important;
    }
    .table tbody td{
      color: rgba(255,255,255,.86) !important;
      border-top: 1px solid rgba(255,255,255,.06) !important;
      vertical-align: middle !important;
    }
    .table-hover tbody tr:hover,
    .table-hover tbody tr:hover td{
      background: rgba(79,124,255,.08) !important;
      color: #fff !important;
    }
    .table tbody td code{
      color: rgba(167,139,250,.95) !important;
      background: rgba(0,0,0,.35) !important;
      border: 1px solid rgba(255,255,255,.08);
      padding: 2px 6px;
      border-radius: 6px;
    }
    .table tbody td i.feather{ color: rgba(255,255,255,.86) !important; }
    .table .text-muted,
    .table .au-empty-row{
      color: rgba(255,255,255,.55) !important;
    }

    .badge-system{
      background: linear-gradient(135deg, rgba(79,124,255,.55), rgba(155,92,255,.35)) !important;
      color: #fff !important;
      font-weight: 700;
      padding: 6px 10px;
      border-radius: 10px;
    }
    .badge-custom{
      background: rgba(255,255,255,.12) !important;
      color: var(--txt) !important;
      font-weight: 700;
      padding: 6px 10px;
      border-radius: 10px;
    }

    .btn-icon-table{
      border-radius: 10px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.22) !important;
      color: var(--txt) !important;
      padding: 6px 10px !important;
      margin-right: 4px;
    }
    .btn-icon-table:hover{
      background: rgba(79,124,255,.25) !important;
      color: #fff !important;
    }
    .btn-icon-table.text-danger:hover{
      background: rgba(255,91,122,.25) !important;
    }

    /* Modal */
    .modal-backdrop{ background:#000 !important; }
    .modal-backdrop.show{ opacity:.90 !important; }
    .modal-dialog.modal-xl{ max-width: 1100px; }
    .modal-content{
      border-radius: 18px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      background: linear-gradient(135deg, rgba(10,12,18,.96), rgba(0,0,0,.94)) !important;
      color: var(--txt) !important;
      box-shadow: var(--shadow);
    }
    .modal-header{
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
    }
    .modal-title{ font-weight: 900 !important; color:#fff !important; }
    .close, .close span{ color:#fff !important; opacity: 1 !important; text-shadow:none !important; }
    .modal-footer{
      border-top: 1px solid rgba(255,255,255,.12) !important;
      background: rgba(0,0,0,.35) !important;
    }

    /* Permisos en modal */
    .perm-module{
      border: 1px solid var(--stroke);
      border-radius: 12px;
      margin-bottom: 12px;
      overflow: hidden;
      background: rgba(0,0,0,.18);
    }
    .perm-module-header{
      background: rgba(255,255,255,.06);
      padding: 10px 14px;
      font-weight: 900;
      cursor: pointer;
      color: var(--txt);
    }
    .perm-module-body{ padding: 10px 14px; display: none; }
    .perm-module.open .perm-module-body{ display: block; }
    .perm-item{
      display: flex;
      align-items: flex-start;
      gap: 8px;
      padding: 6px 0;
      font-size: 13px;
      color: var(--txt);
    }
    .perm-item code{ font-size: 11px; opacity: .75; color: rgba(155,92,255,.9); }
    .perm-item label{ font-weight: 500 !important; margin: 0; cursor: pointer; }

    .au-hint{
      color: var(--muted);
      font-size: 13px;
      margin: 0;
    }
  </style>
</head>

<body class="">
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
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
                <h5 class="m-b-10">Roles y Permisos</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Configuración / Roles y Permisos</a></li>
              </ul>
              <p class="au-hint mb-0 mt-2">Gestión de roles del sistema y permisos asociados (RBAC).</p>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <h5 class="mb-0">Listado de roles</h5>
              <div class="d-flex align-items-center gap-2 ml-auto">
                <?php if ($canManage): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="nuevoRol()">
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
              <div class="row align-items-end mb-3">
                <div class="col-md-6 col-lg-4">
                  <label for="customSearchRoles">Buscar rol</label>
                  <div class="navbar-form buscador-2">
                    <div class="input-group input-primary">
                      <input type="text" id="customSearchRoles" class="form-control" placeholder="Nombre, clave o descripción">
                      <div class="input-group-append">
                        <span class="input-group-text"><i class="feather icon-search"></i></span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="table-responsive tabla-informacion">
                <table class="table table-hover mb-0" id="tablaRoles" style="width:100%;">
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
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalRolTitulo">Rol</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="padding: 18px;">
          <input type="hidden" id="roleId" value="0">
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
          <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <strong>Permisos del rol</strong>
            <?php if ($canManage): ?>
            <div>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleTodosPermisos(true)">Marcar todos</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleTodosPermisos(false)">Desmarcar todos</button>
            </div>
            <?php endif; ?>
          </div>
          <div id="permisosContainer" style="max-height: 420px; overflow-y: auto;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <?php if ($canManage): ?>
          <button type="button" class="btn btn-primary" onclick="guardarRol()">Guardar</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <style>
    /* Override Bootstrap table (carga después de bootstrap.min.css) */
    #tablaRoles,
    #tablaRoles > :not(caption) > * > *,
    .table-border-style .table,
    .table-border-style .table > :not(caption) > * > * {
      background-color: transparent !important;
      color: rgba(255,255,255,.86) !important;
      border-color: rgba(255,255,255,.08) !important;
      box-shadow: none !important;
    }
    #tablaRoles thead th {
      background: rgba(255,255,255,.06) !important;
      color: #fff !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
    }
    #tablaRoles tbody tr {
      background: transparent !important;
    }
    #tablaRoles tbody tr:nth-child(even) {
      background: rgba(255,255,255,.03) !important;
    }
    #tablaRoles.table-hover tbody tr:hover,
    #tablaRoles.table-hover tbody tr:hover > * {
      background: rgba(79,124,255,.10) !important;
      color: #fff !important;
    }
    #tablaRoles tbody td,
    #tablaRoles tbody td a,
    #tablaRoles tbody td i.feather {
      color: rgba(255,255,255,.86) !important;
    }
    #tablaRoles tbody td code {
      color: rgba(167,139,250,.95) !important;
      background: rgba(0,0,0,.35) !important;
    }
  </style>

  <script>
    window.ROLES_PERMISOS = { canManage: <?= $canManage ? 'true' : 'false' ?> };
  </script>
  <script src="<?php echo Util::versionar('admin/js/roles_permisos.js'); ?>"></script>
</body>
</html>
