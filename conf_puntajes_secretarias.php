<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/Secretarias.php';
include './admin/classes/ConfiguracionPuntajeSecretaria.php';

// Permisos
requirePermission('secretarias.config_puntajes.view');
$view = SessionData::hasPermission('secretarias.config_puntajes.view');
$create = SessionData::hasPermission('secretarias.config_puntajes.create');
$edit = SessionData::hasPermission('secretarias.config_puntajes.update');
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    /* ==========================================
       GOVTECH WOW – DARK GLASS (SOLO DISEÑO)
       + TBODY NEGRO / HOVER BLANCO
       + MODAL NEGRO PRO + FIX BS4/BS5
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

    /* Header block premium */
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

    /* Tabs pro */
    .nav-tabs{
      border-bottom: 1px solid var(--stroke) !important;
      gap: 8px;
      flex-wrap: wrap;
    }
    .nav-tabs .nav-link{
      border: 1px solid var(--stroke) !important;
      background: rgba(0,0,0,.18) !important;
      color: var(--muted) !important;
      border-radius: 14px !important;
      font-weight: 900;
      padding: 10px 14px !important;
    }
    .nav-tabs .nav-link.active{
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      border-color: rgba(79,124,255,.45) !important;
      color: #fff !important;
      box-shadow: 0 14px 30px rgba(0,0,0,.25);
    }

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
    .card-body{ padding: 18px !important; }
    @media(min-width:768px){ .card-body{ padding: 22px !important; } }

    /* card option button */
    .btn-group.card-option .btn{
      border-radius: 12px !important;
      border: 1px solid var(--stroke2) !important;
      background: rgba(0,0,0,.20) !important;
      color: var(--txt) !important;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }

    /* Inputs / Selects (NO rompe tu JS) */
    select{ padding: 10px; font-size: 16px; }
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
    .form-control:focus, select.form-control:focus{
      border-color: rgba(79,124,255,.55) !important;
      box-shadow: 0 0 0 .2rem rgba(79,124,255,.18) !important;
      outline: none !important;
    }
    label{ color: rgba(255,255,255,.72) !important; font-weight: 900; }

    /* Buttons */
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
    .btn-secondary{
      background: rgba(255,255,255,.06) !important;
      color: var(--txt) !important;
    }

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

    /* Table wrapper */
    .table-responsive{
      border-radius: 16px;
      border: 1px solid var(--stroke) !important;
      background: rgba(0,0,0,.16);
      overflow:auto;
      margin-top: 14px;
    }
    .table{ margin-bottom: 0 !important; color: var(--txt) !important; }
    .table thead th{
      background: rgba(255,255,255,.06) !important;
      color: rgba(255,255,255,.88) !important;
      border-bottom: 1px solid var(--stroke) !important;
      white-space: nowrap;
    }

    /* ===== TBODY: negro siempre, hover blanco (BLINDADO DataTables) ===== */
    #dynamictable tbody tr{
      background: transparent !important;
      transition: background .15s ease, color .15s ease;
    }
    #dynamictable tbody td{
      color: rgba(255,255,255,.86) !important;
      font-weight: 800;
      border-top: 1px solid rgba(255,255,255,.06) !important;
      vertical-align: middle !important;
    }
    #dynamictable.table-hover tbody tr:hover{
      background: rgba(0,0,0,.60) !important;
    }
    #dynamictable.table-hover tbody tr:hover td{
      color: #ffffff !important;
    }

    /* Datatables controls */
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

    /* Color box pro */
    #colorBox, #colorBoxEdit{
      border: 1px solid rgba(255,255,255,.14) !important;
      border-radius: 12px !important;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }

    /* ===== MODAL PRO (NEGRO) ===== */
    .modal-backdrop{ background:#000 !important; }
    .modal-backdrop.show{ opacity:.90 !important; }

    .modal-dialog.modal-xl{ max-width: 1100px; }

    .modal-content{
      border-radius: 18px !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      background: linear-gradient(135deg, rgba(10,12,18,.96), rgba(0,0,0,.94)) !important;
      color: var(--txt) !important;
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .modal-header{
      background: linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22)) !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
    }
    .modal-title{
      font-weight: 900 !important;
      letter-spacing: .2px;
      color:#fff !important;
      margin:0;
    }
    .close, .close span{
      color:#fff !important;
      opacity: 1 !important;
      text-shadow:none !important;
    }
    .modal-footer{
      border-top: 1px solid rgba(255,255,255,.12) !important;
      background: rgba(0,0,0,.35) !important;
    }

    /* Fix thead bg-light text-dark */
    thead.bg-light.text-dark, thead.bg-light.text-dark th{
      background: rgba(255,255,255,.06) !important;
      color: rgba(255,255,255,.86) !important;
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

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Configuración Puntajes Secretaría</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Configuración Acción Unificada / Configuración Puntajes Secretaría</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12">

          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
                role="tab" aria-controls="home" aria-selected="true">Ingresar configuración puntajes secretaria</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
                role="tab" aria-controls="profile" aria-selected="false" onclick="cargaData()">Listado de configuración puntajes secretaria</button>
            </li>
          </ul>

          <div class="tab-content" id="myTabContent">

            <!-- TAB 1 -->
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
              <br>
              <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                  <h5 class="mb-0 text-center w-100">Ingresar configuración puntajes secretaria</h5>
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

                <div class="card-body m-4">
                  <form id="formupuntajes" role="form" autocomplete="false">
                    <input type="hidden" name="op" id="op" />
                    <input type="hidden" name="idPuntaje" id="idPuntaje" />

                    <div class="form-row py-2">
                      <div class="form-group col-md-3">
                        <div class="form-group">
                          <label class="floating-label" for="secretariaId">Secretaría<span class="text-danger mb-1">*</span></label>
                          <select class="form-control" id="secretariaId" name="secretariaId"></select>
                        </div>
                      </div>

                      <div class="form-group col-md-3">
                        <div class="form-group">
                          <label class="floating-label" for="tipo_medicion">Tipo Medición<span class="text-danger mb-1">*</span></label>
                          <select id="tipo_medicion" name="tipo_medicion" class="form-control">
                            <option selected>Seleccione</option>
                            <option value="Cantidad">Cantidad</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Creación">Creación</option>
                          </select>
                        </div>
                      </div>

                      <div class="form-group col-md-3">
                        <label for="desde">Desde<span class="text-danger mb-1">*</span></label>
                        <input type="text" class="form-control" id="desde" name="desde" onKeyPress="return soloNumeros(event);" value="" required>
                      </div>

                      <div class="form-group col-md-3">
                        <label for="hasta">Hasta<span class="text-danger mb-1">*</span></label>
                        <input type="text" class="form-control" id="hasta" name="hasta" onKeyPress="return soloNumeros(event);" value="" required>
                      </div>
                    </div>

                    <div class="form-row py-2">
                      <div class="form-group col-md-3">
                        <label for="color">Color<span class="text-danger">*</span></label>
                        <div class="input-group" style="margin: 0;">
                          <select id="color" name="color" onchange="updateColorBox()" class="form-control">
                            <option value="">Seleccione</option>
                            <option value="#cd162c">Rojo</option>
                            <option value="#cd7d16">Naranja</option>
                            <option value="#dbd509">Amarillo</option>
                            <option value="#2774f1">Azul</option>
                            <option value="#62af0a">Verde</option>
                          </select>
                          <div class="input-group-append">
                            <div id="colorBox" class="rounded px-3 d-flex align-items-center" style="background-color:#0b0f1a; min-width:60px; height:48px;">
                              &nbsp;
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-row pt-3">
                      <div class="col text-center">
                        <button type="button" onclick="UTIL.clearForm('formupuntajes');" class="btn btn-danger mr-2">Cancelar</button>
                        <button type="button" onclick="save();" class="btn btn-primary">Guardar</button>
                      </div>
                    </div>

                  </form>
                </div>
              </div>
            </div>

            <!-- TAB 2 -->
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
              <br>
              <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                  <h5 class="mb-0 text-center w-100">Listado configuración puntajes secretaria</h5>
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
                        <span class="input-group-text"><i class="feather icon-edit"></i></span>
                      </div>
                    </div>
                  </div>

                  <div class="table-responsive tabla-informacion tabla-scroll">
                    <table class="table table-hover mb-0" id="dynamictable" style="width: 100%;">
                      <thead style="">
                        <tr class="border-1">
                          <th>Editar</th>
                          <th>Secretaría</th>
                          <th>Tipo medición</th>
                          <th>Desde</th>
                          <th>Hasta</th>
                          <th>Color</th>
                        </tr>
                      </thead>
                    </table>
                  </div>

                </div>
              </div>
            </div>

          </div><!-- tab content -->
        </div>
      </div>

    </div>
  </div>

  <!-- modal edit -->
  <div class="modal fade" id="modalEditSecretario" tabindex="-1" role="dialog" aria-labelledby="modalSecretariaLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalSecretariaLabel">Editar Puntaje</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body" style="padding: 15px;">
          <form id="formupuntajesEdit" role="form" autocomplete="off">
            <input type="hidden" name="opEdit" id="opEdit" />
            <input type="hidden" name="idPuntajeEdit" id="idPuntajeEdit" />

            <div class="row">
              <div class="col-sm-3">
                <div class="form-group">
                  <label class="floating-label" for="secretariaIdEdit">Secretaría<span class="text-danger mb-1">*</span></label>
                  <select class="form-control" id="secretariaIdEdit" name="secretariaIdEdit"></select>
                </div>
              </div>

              <div class="col-sm-3">
                <div class="form-group">
                  <label class="floating-label" for="tipo_medicionEdit">Tipo Medición<span class="text-danger mb-1">*</span></label>
                  <select id="tipo_medicionEdit" name="tipo_medicionEdit" class="form-control">
                    <option value="">Seleccione</option>
                    <option value="Cantidad">Cantidad</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Creación">Creación</option>
                  </select>
                </div>
              </div>

              <div class="col-sm-3">
                <label for="desdeEdit">Desde<span class="text-danger mb-1">*</span></label>
                <input type="text" class="form-control" id="desdeEdit" name="desdeEdit" onkeypress="return soloNumeros(event);" required>
              </div>

              <div class="col-sm-3">
                <label for="hastaEdit">Hasta<span class="text-danger mb-1">*</span></label>
                <input type="text" class="form-control" id="hastaEdit" name="hastaEdit" onkeypress="return soloNumeros(event);" required>
              </div>
            </div>

            <div class="form-row py-2">
              <div class="form-group col-md-3">
                <label for="colorEdit">Color<span class="text-danger">*</span></label>
                <div class="input-group" style="margin: 0;">
                  <select id="colorEdit" name="colorEdit" onchange="updateColorBoxEdit()" class="form-control">
                    <option value="">Seleccione</option>
                    <option value="#cd162c">Rojo</option>
                    <option value="#cd7d16">Naranja</option>
                    <option value="#dbd509">Amarillo</option>
                    <option value="#2774f1">Azul</option>
                    <option value="#62af0a">Verde</option>
                  </select>
                  <div class="input-group-append">
                    <div id="colorBoxEdit" class="rounded px-3 d-flex align-items-center" style="background-color:#0b0f1a; min-width:60px; height:48px;">
                      &nbsp;
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </form>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" id="btnGuardarEditar" class="btn btn-primary" onclick="saveEdit();">Actualizar</button>
        </div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script type="text/javascript" src="admin/js/conf_puntajes_secretaria.js"></script>
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
    // ====== COMPATIBILIDAD MODALES BS4/BS5 (NO TOCA TU LÓGICA) ======
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

      // Bridge: data-toggle="modal" data-target="#modalEditSecretario"
      document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-toggle="modal"][data-target]');
        if (!btn) return;

        // Si BS4 existe, no interceptar
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') return;

        e.preventDefault();
        var target = btn.getAttribute('data-target') || '';
        if (target && target.startsWith('#')) target = target.slice(1);
        if (target) showModal(target);
      }, true);

      // Bridge cerrar (data-dismiss="modal")
      document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-dismiss="modal"]');
        if (!btn) return;

        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') return;

        e.preventDefault();
        var modalEl = btn.closest('.modal');
        hideModal(modalEl);
      }, true);

      // Limpia residuos backdrop (por si hay mezclas)
      document.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        var bd = document.querySelector('.modal-backdrop');
        if (bd) bd.remove();
      }, true);
    })();
  </script>
</body>
</html>
