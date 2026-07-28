<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/SecretariasMunicipios.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Departamento.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

$userType = SessionData::getUserType();
$isAdmin  = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

// Tipos municipal
$tiposUsuarioMunicipal = ['Alcalde','Auxiliar_Alcalde','Secretario_Despacho','Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);

if (!$isAdmin && !$isUsuarioMunicipal) {
  require 'permiso_denegado.php';
  exit;
}

// Municipio usuario
$municipioUsuario = '';
$codigoDepartamentoUsuario = '';
if ($isUsuarioMunicipal) {
  $municipioUsuario = SessionData::getCodigoMunicipio();
  $codigoDepartamentoUsuario = Util::getDepartamentoPrincipal();
}

// Departamentos
$arrDep  = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep  = $arrDep['output']['response'];

$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
  $optionDep .= "<option value='".$val['codigo_departamento']."'>".$val['codigo_departamento']." - ".$val['departamento']."</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <style>
    :root{
      --au-primary:#20427F; --au-primary-dark:#132b52; --au-accent:#2e58a8;
      --au-radius-xl:22px; --au-radius-lg:16px;
      --safe-top: 96px;
    }
    html, body{ overflow-x:hidden !important; }

    .header-row{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; width:100%; }
    .header-title{ flex:1 1 auto; min-width:240px; }
    .header-actions{ display:flex; align-items:center; gap:10px; margin-left:auto; flex:0 0 auto; }

    .btn-saas{
      background:linear-gradient(135deg,#3b82f6,#4f46e5) !important;
      border:1px solid rgba(255,255,255,.14) !important;
      border-radius:999px !important; padding:.56rem 1.0rem !important;
      font-weight:1000 !important; letter-spacing:.2px;
      display:flex; align-items:center; gap:.55rem;
      transition:transform .15s ease,filter .2s ease;
      color:#fff !important; white-space:nowrap;
      box-shadow:0 12px 22px rgba(19,43,82,.22);
    }
    .btn-saas:hover{ transform:translateY(-1px); filter:brightness(1.03); color:#fff !important; }
    .btn-saas:active{ transform:translateY(0); }

    .search-wrap{
      background:rgba(255,255,255,.06); backdrop-filter:blur(10px);
      border:1px solid rgba(255,255,255,.18); border-radius:14px;
      padding:6px 8px; width:320px; max-width:100%; margin:0 0 14px 0;
      box-shadow:0 14px 30px rgba(0,0,0,.18);
    }
    .search-wrap .input-group-text{ background:transparent !important; border:none !important; color:rgba(255,255,255,.7) !important; padding:0 10px 0 6px; }
    #customSearch{ border:none !important; outline:none !important; box-shadow:none !important; padding:.45rem .25rem; font-weight:800; font-size:14px; color:#fff !important; background:transparent !important; }
    #customSearch::placeholder{ color:rgba(255,255,255,.5); }

    @media(max-width:576px){ .header-actions{ width:100%; justify-content:flex-end; } .search-wrap{ width:100%; } }

    .table-responsive{ overflow-x:auto; }
    #dynamictable{ width:100% !important; table-layout:auto; white-space:normal; border-collapse:separate; border-spacing:0; }
    #dynamictable thead th{
      position:sticky; top:0; z-index:2;
      background: rgba(255,255,255,.08) !important;
      color: #fff !important; font-weight:1000;
      border-bottom: 1px solid rgba(255,255,255,.14) !important;
      padding: 12px 12px !important; white-space:nowrap;
    }
    #dynamictable tbody tr{ background: transparent !important; }
    #dynamictable td{
      color: rgba(255,255,255,.86) !important;
      white-space:normal !important; word-break:break-word !important;
      max-width:320px; vertical-align:top;
      padding: 12px 12px !important;
      border-bottom: 1px solid rgba(255,255,255,.08) !important;
      background: transparent !important;
    }
    #dynamictable tbody tr:hover{ background: rgba(255,255,255,.06) !important; }
    #dynamictable .feather{ color: rgba(255,255,255,.8) !important; }
    #dynamictable .btn .feather{ color: #fff !important; }
    #dynamictable .btn-transparent{ color: rgba(255,255,255,.8) !important; background: transparent !important; border: none !important; }
    #dynamictable .btn-transparent:hover{ color: #fff !important; background: rgba(255,255,255,.1) !important; }
    #dynamictable span.text-muted{ color: rgba(255,255,255,.5) !important; }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info{ color:rgba(255,255,255,.7) !important; font-weight:700 !important; }
    .dataTables_wrapper .dataTables_length select{
      border-radius:12px; border:1px solid rgba(255,255,255,.14);
      padding:6px 10px; background:rgba(10,17,33,.55); color:#fff;
    }
    .dataTables_wrapper .dataTables_paginate{ margin-top:10px; }
    .dataTables_wrapper .dataTables_paginate .paginate_button{
      border-radius:999px !important; border:1px solid rgba(255,255,255,.18) !important;
      margin:0 3px !important; background:rgba(255,255,255,.10) !important;
      color:#fff !important; font-weight:800 !important; padding:0.4em 0.9em !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover{ background:rgba(255,255,255,.18) !important; color:#fff !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current{
      background:linear-gradient(135deg,#3b82f6,#4f46e5) !important;
      border-color:rgba(255,255,255,.25) !important; color:#fff !important; font-weight:900 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
      opacity:.35 !important; cursor:not-allowed !important;
      background:transparent !important; border-color:transparent !important;
    }
  </style>
</head>

<body class="">
  <!-- Preloader -->
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
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Secretarías Municipales</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Configuración Sistema</a></li>
                <li class="breadcrumb-item"><a href="#!">Secretarías Municipales</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="header-row">
            <div class="header-title">
              <h5 class="mb-0">Ingreso y listado de secretarías municipales</h5>
            </div>

            <div class="header-actions">
              <button
                type="button"
                class="btn btn-saas btn-sm"
                id="btnNuevaSecretaria"
                data-toggle="modal"
                data-target="#newModalSecretaria"
              >
                <i class="feather icon-plus"></i> Nueva Secretaría
              </button>

              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span></a></li>
                    <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> Expandir</span></a></li>
                    <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                    <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Eliminar</a></li>
                  </ul>
                </div>
              </div>

            </div>
          </div>
        </div>

        <div class="card-body table-border-style">

          <div class="search-wrap">
            <div class="input-group">
              <span class="input-group-text"><i class="feather icon-search"></i></span>
              <input type="text" id="customSearch" class="form-control" placeholder="Buscar...">
            </div>
          </div>

          <div class="table-responsive tabla-informacion tabla-scroll">
            <table class="table table-hover mb-0" id="dynamictable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Editar</th>
                  <th>Municipio</th>
                  <th>Secretaría</th>
                  <th>Secretario</th>
                  <th>Correo</th>
                  <th>Habilitado</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>

      <!-- ✅ MODAL -->
      <div class="modal fade" id="newModalSecretaria" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
          <div class="modal-content">

            <div class="modal-header">
              <h5 class="modal-title">Ingresar Nueva Secretaría Municipal</h5>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body p-4">
              <form id="formNewSecretaria" autocomplete="off">
                <input type="hidden" id="editId" name="editId">

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                    <select class="form-control" id="tbl_departamento_id" name="tbl_departamento_id" required onchange="DEPARTAMENTO.getMunicipios()">
                      <?php echo $optionDep; ?>
                    </select>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                    <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" required>
                      <option value="">Seleccione un municipio</option>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="newSecretaria">Secretaría <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newSecretaria" name="newSecretaria" required>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="newSecretario">Secretario <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newSecretario" name="newSecretario" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="newEmail">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="newEmail" name="newEmail" required>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="newHabilitado">Habilitado <span class="text-danger">*</span></label>
                    <select class="form-control" id="newHabilitado" name="newHabilitado" required>
                      <option value="Seleccione">Seleccione</option>
                      <option value="si">Sí</option>
                      <option value="no">No</option>
                    </select>
                  </div>
                </div>

              </form>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="button" id="btnSaveSecretaria" class="btn btn-saas" onclick="saveNewSecretaria();">Guardar</button>
            </div>

          </div>
        </div>
      </div>

      <!-- Variables de sesión -->
      <input type="hidden" id="municipioUsuario" value="<?php echo $municipioUsuario; ?>">
      <input type="hidden" id="tipoUsuario" value="<?php echo $userType; ?>">
      <input type="hidden" id="isAdmin" value="<?php echo $isAdmin ? '1' : '0'; ?>">

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

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
  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>

  <script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
  <script src="<?php echo Util::versionar('./admin/js/secretarias_municipios.js'); ?>"></script>


  <script>
    // ✅ Fallback por si el data-toggle no engancha por algún conflicto:
    $(document).on('click', '#btnNuevaSecretaria', function(){
      if ($('#newModalSecretaria').length) {
        try { $('#newModalSecretaria').modal('show'); } catch(e) {}
      }
      if (typeof ingresarSecretaria === 'function') {
        try { ingresarSecretaria(); } catch(e) {}
      }
    });
  </script>

</body>
</html>
