<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/Departamento.php';

$arrDep = Departamento::getAll(null);
$arrDep = $arrDep['output']['response'] ?? [];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val['codigo_departamento'] == Util::getDepartamentoPrincipal() ? 'selected' : '') . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . ' - ' . $val['departamento'] . '</option>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --bg0:#070A12; --bg1:#0B1222; --stroke:rgba(255,255,255,.10); --stroke2:rgba(255,255,255,.14);
      --txt:rgba(255,255,255,.92); --muted:rgba(255,255,255,.66);
      --shadow:0 20px 60px rgba(0,0,0,.35); --r-xl:18px;
    }
    body{
      background:
        radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
        radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
        linear-gradient(180deg, var(--bg0), var(--bg1)) !important;
      color:var(--txt); overflow-x:hidden;
    }
    .pcoded-main-container{background:transparent!important;}
    .page-header .page-block{
      border:1px solid var(--stroke); background:rgba(255,255,255,.05);
      border-radius:16px; padding:14px; box-shadow:0 14px 40px rgba(0,0,0,.25);
    }
    .page-header h5,.breadcrumb .breadcrumb-item,.breadcrumb .breadcrumb-item a{color:var(--txt)!important;}
    .breadcrumb .breadcrumb-item a{color:var(--muted)!important;}
    .nav-tabs{border-bottom:1px solid var(--stroke)!important; gap:8px; flex-wrap:wrap;}
    .nav-tabs .nav-link{
      border:1px solid var(--stroke)!important; background:rgba(0,0,0,.18)!important;
      color:var(--muted)!important; border-radius:14px!important; font-weight:900; padding:10px 14px!important;
    }
    .nav-tabs .nav-link.active{
      background:linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22))!important;
      border-color:rgba(79,124,255,.45)!important; color:#fff!important;
    }
    .card{
      border:1px solid var(--stroke)!important; border-radius:var(--r-xl)!important;
      background:linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04))!important;
      box-shadow:var(--shadow); overflow:hidden;
    }
    .card-header{background:rgba(0,0,0,.14)!important; border-bottom:1px solid var(--stroke)!important;}
    .card-header h5{color:var(--txt)!important; font-weight:900!important; margin:0!important;}
    .form-control,select.form-control{
      border-radius:14px!important; border:1px solid rgba(255,255,255,.14)!important;
      background:rgba(0,0,0,.28)!important; color:var(--txt)!important;
      padding:12px 14px!important; min-height:46px;
    }
    label{color:rgba(255,255,255,.72)!important; font-weight:900;}
    .btn{border-radius:14px!important; font-weight:900!important;}
    .btn-primary{
      border-color:rgba(79,124,255,.45)!important;
      background:linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22))!important; color:#fff!important;
    }
    .btn-danger{
      border-color:rgba(255,91,122,.45)!important;
      background:linear-gradient(135deg, rgba(255,91,122,.22), rgba(0,0,0,.22))!important; color:#fff!important;
    }
    .btn-secondary{background:rgba(255,255,255,.06)!important; color:var(--txt)!important;}
  #customSearch{
      border-radius:14px 0 0 14px!important; border:1px solid var(--stroke2)!important;
      background:rgba(0,0,0,.22)!important; color:var(--txt)!important; min-height:44px;
    }
    .buscador-2 .input-group-text{
      border-radius:0 14px 14px 0!important; border:1px solid var(--stroke2)!important;
      background:rgba(0,0,0,.30)!important; color:var(--txt)!important;
    }
    .table-responsive{border-radius:16px; border:1px solid var(--stroke)!important; background:rgba(0,0,0,.16); overflow:auto; margin-top:14px;}
    .table{color:var(--txt)!important; margin-bottom:0!important;}
    .table thead th{background:rgba(255,255,255,.06)!important; color:rgba(255,255,255,.88)!important; border-bottom:1px solid var(--stroke)!important; white-space:nowrap;}
    .modal-content{
      border-radius:18px!important; border:1px solid rgba(255,255,255,.14)!important;
      background:linear-gradient(135deg, rgba(10,12,18,.96), rgba(0,0,0,.94))!important; color:var(--txt)!important;
    }
    .modal-header{
      background:linear-gradient(135deg, rgba(79,124,255,.35), rgba(155,92,255,.22))!important;
      border-bottom:1px solid rgba(255,255,255,.12)!important;
    }
    .modal-title,.close,.close span{color:#fff!important; opacity:1!important;}
    .modal-footer{border-top:1px solid rgba(255,255,255,.12)!important; background:rgba(0,0,0,.35)!important;}
    table.dataTable tbody tr{background:rgba(255,255,255,.03)!important;}
    table.dataTable tbody td{color:rgba(255,255,255,.86)!important;}
    .dataTables_wrapper .dataTables_info,.dataTables_wrapper .dataTables_length label{color:#fff!important;}
  </style>
</head>
<body class="">
  <div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Acción Unificada — Empresas por Municipio</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Acción Unificada / Empresas</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-12">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
              <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button" role="tab">Registrar empresa</button>
            </li>
            <li class="nav-item">
              <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button" role="tab" onclick="cargaData()">Listado de empresas</button>
            </li>
          </ul>

          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel">
              <br>
              <div class="card">
                <div class="card-header"><h5 class="text-center w-100 mb-0">Ingresar empresa</h5></div>
                <div class="card-body m-4">
                  <form id="formEmpresa" role="form" autocomplete="off">
                    <input type="hidden" id="idEmpresa" name="idEmpresa" value="">
                    <input type="hidden" id="codigo_muncipio" name="codigo_muncipio" value="">
                    <select class="d-none" id="tbl_departamento_id" onchange="DEPARTAMENTO.getMunicipios();"><?php echo $optionDep; ?></select>

                    <div class="form-row py-2">
                      <div class="form-group col-md-4">
                        <label for="tbl_municipio_id">Municipio<span class="text-danger">*</span></label>
                        <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" onchange="sincronizarMunicipioFormulario();"></select>
                      </div>
                      <div class="form-group col-md-4">
                        <label for="nombre_empresa">Nombre empresa<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" placeholder="Nombre de la empresa" required>
                      </div>
                      <div class="form-group col-md-4">
                        <label for="nit">NIT</label>
                        <input type="text" class="form-control" id="nit" name="nit" placeholder="Opcional">
                      </div>
                    </div>

                    <div class="form-row py-2">
                      <div class="form-group col-md-4">
                        <label for="nombre_contacto">Nombre contacto<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre_contacto" name="nombre_contacto" placeholder="Persona de contacto" required>
                      </div>
                      <div class="form-group col-md-4">
                        <label for="telefono_contacto">Teléfono contacto<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" placeholder="Teléfono" required>
                      </div>
                      <div class="form-group col-md-4">
                        <label for="email_contacto">Email contacto</label>
                        <input type="email" class="form-control" id="email_contacto" name="email_contacto" placeholder="Opcional">
                      </div>
                    </div>

                    <div class="form-row pt-3">
                      <div class="col text-center">
                        <button type="button" onclick="UTIL.clearForm('formEmpresa'); $('#idEmpresa').val('');" class="btn btn-danger mr-2">Cancelar</button>
                        <button type="button" onclick="save();" class="btn btn-primary">Guardar</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="profile" role="tabpanel">
              <br>
              <div class="card">
                <div class="card-header"><h5 class="text-center w-100 mb-0">Listado de empresas</h5></div>
                <div class="card-body table-border-style">
                  <div class="row align-items-end mb-3">
                    <div class="col-md-4">
                      <label for="filtroMunicipio">Filtrar por municipio</label>
                      <select id="filtroMunicipio" class="form-control">
                        <option value="0">Todos los municipios</option>
                      </select>
                    </div>
                    <div class="col-md-8">
                      <div class="navbar-form buscador-2">
                        <div class="input-group input-primary">
                          <input type="text" id="customSearch" class="form-control" placeholder="Buscar empresa, NIT, contacto...">
                          <div class="input-group-append">
                            <span class="input-group-text"><i class="feather icon-search"></i></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="table-responsive">
                    <table class="table table-hover mb-0" id="dynamictable" style="width:100%;">
                      <thead>
                        <tr>
                          <th>Acciones</th>
                          <th>Municipio</th>
                          <th>Empresa</th>
                          <th>NIT</th>
                          <th>Contacto</th>
                          <th>Teléfono</th>
                          <th>Email</th>
                          <th>Fecha registro</th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalEmpresa" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar empresa</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <form id="formEmpresaEdit" autocomplete="off">
            <input type="hidden" id="idEmpresaEdit" name="idEmpresaEdit">
            <div class="form-row py-2">
              <div class="form-group col-md-4">
                <label for="tbl_municipio_idEdit">Municipio<span class="text-danger">*</span></label>
                <select class="form-control" id="tbl_municipio_idEdit" name="tbl_municipio_idEdit"></select>
              </div>
              <div class="form-group col-md-4">
                <label for="nombre_empresaEdit">Nombre empresa<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_empresaEdit" required>
              </div>
              <div class="form-group col-md-4">
                <label for="nitEdit">NIT</label>
                <input type="text" class="form-control" id="nitEdit">
              </div>
            </div>
            <div class="form-row py-2">
              <div class="form-group col-md-4">
                <label for="nombre_contactoEdit">Nombre contacto<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre_contactoEdit" required>
              </div>
              <div class="form-group col-md-4">
                <label for="telefono_contactoEdit">Teléfono contacto<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="telefono_contactoEdit" required>
              </div>
              <div class="form-group col-md-4">
                <label for="email_contactoEdit">Email contacto</label>
                <input type="email" class="form-control" id="email_contactoEdit">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="editSave();">Actualizar</button>
        </div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/departamento.js"></script>
  <script type="text/javascript" src="<?php echo Util::versionar('./admin/js/accion_unificada.js'); ?>"></script>
  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
  <script>
    function poblarFiltroMunicipios() {
      var $filtro = $("#filtroMunicipio");
      var valorActual = $filtro.val() || "0";
      $filtro.find("option:not(:first)").remove();
      $("#tbl_municipio_id option").each(function () {
        var val = $(this).val();
        var txt = $(this).text();
        if (val) {
          $filtro.append($("<option>").val(val).text(txt));
        }
      });
      $filtro.val(valorActual);
      copiarMunicipiosAlModal();
    }

    setTimeout(function () {
      DEPARTAMENTO.getMunicipios();
      setTimeout(function () {
        poblarFiltroMunicipios();
        sincronizarMunicipioFormulario();
      }, 600);
    }, 400);
  </script>
</body>
</html>
