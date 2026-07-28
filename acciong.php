<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

require './admin/include/generic_classes.php';
include './admin/classes/Ministerios.php';

// Permisos
requirePermission('configuracion.acciones_gestion.view');
$view = SessionData::hasPermission('configuracion.acciones_gestion.view');
$create = SessionData::hasPermission('configuracion.acciones_gestion.create');
$edit = SessionData::hasPermission('configuracion.acciones_gestion.update');
$permits = SessionData::hasPermission('configuracion.acciones_gestion.delete');
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

//Información de Ministerios
$arr = Ministerios::getAll(null);
$isvalid = $arr['output']['valid'] ?? false;
$arr = $arr['output']['response'] ?? [];
$modulo = 'Ministerios';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<style>
/* ==========================================
   GOVTECH WOW – DARK GLASS (SOLO DISEÑO)
========================================== */
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
  --r-lg:16px;

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

/* breadcrumb readable */
.page-header h5, .breadcrumb .breadcrumb-item, .breadcrumb .breadcrumb-item a{
  color: var(--txt) !important;
}
.breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }

/* TOP header premium */
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

/* Card pro */
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

/* card option button */
.btn-group.card-option .btn{
  border-radius: 12px !important;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.20) !important;
  color: var(--txt) !important;
  box-shadow: 0 10px 24px rgba(0,0,0,.25);
}

.card-body{ padding: 18px !important; }
@media(min-width:768px){ .card-body{ padding: 22px !important; } }

/* Search */
#customSearch{
  border-radius: 14px 0 0 14px !important;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.22) !important;
  color: var(--txt) !important;
  min-height: 44px;
}
#customSearch::placeholder{ color: rgba(255,255,255,.50) !important; }

.buscador-2 .input-group-text{
  border-radius: 0 14px 14px 0 !important;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.30) !important;
  color: var(--txt) !important;
  min-height: 44px;
}

/* Table */
.table-responsive{
  border-radius: 16px;
  border: 1px solid var(--stroke) !important;
  background: rgba(0,0,0,.16);
  overflow:auto;
  margin-top: 14px;
}
.table{
  color: var(--txt) !important;
  margin-bottom: 0 !important;
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

/* Fix: thead bg-light text-dark */
thead.bg-light.text-dark, thead.bg-light.text-dark th{
  background: rgba(255,255,255,.06) !important;
  color: rgba(255,255,255,.86) !important;
}

/* DataTables controls */
.dataTables_wrapper .dataTables_filter label,
.dataTables_wrapper .dataTables_length label,
.dataTables_wrapper .dataTables_info{
  color: var(--muted) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button{
  color: var(--txt) !important;
  border-radius: 12px !important;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.20) !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current{
  background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
  border-color: rgba(79,124,255,.45) !important;
}

/* Modal PRO */
.modal-content{
  border-radius: 18px !important;
  border: 1px solid var(--stroke) !important;
   background: rgb(0, 0, 0) !important;
  color: var(--txt) !important;
  box-shadow: var(--shadow);
}
.modal-header{
  border-bottom: 1px solid var(--stroke) !important;
  background: rgba(0,0,0,.18) !important;
}
.modal-title{
  font-weight: 900 !important;
  letter-spacing: .2px;
  color: var(--txt) !important;
}
.modal-footer{
  border-top: 1px solid var(--stroke) !important;
  background: rgba(0,0,0,.14) !important;
}
.close, .close span{
  color:#fff !important;
  opacity:1 !important;
  text-shadow:none !important;
}

/* Modal inputs */
.form-control, textarea.form-control{
  border-radius: 14px !important;
  border: 1px solid var(--stroke2) !important;
  background: rgba(0,0,0,.22) !important;
  color: var(--txt) !important;
  padding: 12px 14px !important;
  min-height: 44px;
  box-shadow:none !important;
}
label{
  color: rgba(255,255,255,.70) !important;
  font-weight: 900;
}

/* Buttons */
.btn{
  border-radius: 14px !important;
  padding: 10px 22px !important;
  font-weight: 900 !important;
  border: 1px solid var(--stroke2) !important;
  box-shadow: 0 10px 24px rgba(0,0,0,.25);
}
.btn-primary{
  border-color: rgba(79,124,255,.45) !important;
  background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
  color:#fff !important;
}
.btn-secondary{
  background: rgba(255,255,255,.06) !important;
  color: var(--txt) !important;
}
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

  <!-- [ navigation menu ] start -->
  <?php include './admin/include/navbar.php'; ?>
  <!-- [ navigation menu ] end -->

  <!-- [ Header ] start -->
  <?php include './admin/include/header.php'; ?>
  <!-- [ Header ] end -->

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Tipos de acción</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Configuración General / Tipo de acciones</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="contenedor">
        <div class="contenido">
          <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <h5 class="mb-0 text-center w-100">Ingreso y listado de acciones</h5>

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
              <div class="navbar-form buscador-2">
                <div class="input-group input-primary">
                  <input type="text" id="customSearch" class="form-control" placeholder="Buscar">
                  <div class="input-group-append">
                    <span class="input-group-text">
                      <i class="feather icon-edit"></i>
                    </span>
                  </div>
                </div>
              </div>

              <div class="table-responsive tabla-informacion tabla-scroll">
                <table class="table table-hover mb-0" id="dynamictable">
                  <thead style="">
                    <tr class="border-1">
                      <th>Ingresar</th>
                      <th>Editar</th>
                      <th>Tipo de acción</th>
                    </tr>
                  </thead>
                </table>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <div class="modal fade" id="modalAccionG" tabindex="-1" role="dialog" aria-labelledby="modalAccionGLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
          <form id="formAccionG" class="w-100" autocomplete="off">
            <input type="hidden" name="id" id="ministerioId" />
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalAccionGLabel">Tipos de acciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

              <div class="modal-body" style="padding: 15px;">
                <div class="form-row">
                  <div class="form-group col-md-12">
                    <label for="accion">Tipo de acción <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="accion" name="accion" required />
                    <input type="hidden" class="form-control" id="id" name="id" />
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarAccionG" class="btn btn-primary">Guardar</button>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script type="text/javascript" src="admin/js/acciong.js"></script>
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


</body>
</html>
