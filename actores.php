<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

require './admin/include/generic_classes.php';
include './admin/classes/Actores.php';

// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Departamento.php';

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

$arr = Actores::getAll(null);

$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$modulo = 'Actores';


?>

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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Mapa De Actores</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Configuración Acción Unificada / Mapa De
                                        Actores</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Creación de Actores en la Región</h5>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i
                                                        class="feather icon-maximize"></i> maximize</span><span
                                                    style="display:none"><i class="feather icon-minimize"></i>
                                                    Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                        class="feather icon-minus"></i> collapse</span><span
                                                    style="display:none"><i class="feather icon-plus"></i>
                                                    expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i
                                                    class="feather icon-refresh-cw"></i> reload</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i
                                                    class="feather icon-trash"></i> remove</a></li>
                                    </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-sm-12">
                            <form id="formuactores" role="form" autocomplete="false">
                                <input type="hidden" name="op" id="op" />
                                <input type="hidden" name="id" id="id" />
                                <input type="hidden" name="codigo_departamento" id="codigo_departamento"
                                    value="68" />
                                <div class="row">

                                    <div class="form-group col-md-4" style="display: none;">
                                        <label for="validationCustom01">Departamento<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" style="width: 100%;"
                                            onchange="DEPARTAMENTO.getMunicipios();"
                                            id="tbl_departamento_id" name="tbl_departamento_id" disabled>
                                            <?php echo $optionDep; ?>
                                        </select>

                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom01">Alcaldía<span
                                                class="text-danger mb-1">*</span></label>
                                        <select class="form-control" style="width: 100%;"
                                            id="tbl_municipio_id" name="tbl_municipio_id[]"> </select>

                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="floating-label" for="Email">Nombre</label>
                                            <input type="text" class="form-control" id="nombre"
                                                name="nombre" aria-describedby="emailHelp" value="">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="inputState">Pertenece a:</label>
                                        <select id="pertenece" name="pertenece" class="form-control">
                                            <option selected>Seleccione</option>
                                            <option value="Empresa Privada">Empresa Privada</option>
                                            <option value="Gobernacion">Gobernacion</option>
                                            <option value="Alcaldia">Alcaldia</option>
                                            <option value="Gobierno Nacional">Gobierno Nacional</option>
                                            <option value="Policia Nacional">Policia Nacional</option>
                                            <option value="Ejercíto Nacional">Ejercíto Nacional</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mt-3" style="gap: 6px;">
                                    <button style="border-radius: 10px;" type="button" onclick="UTIL.clearForm('formuactores');"
                                        class="btn btn-danger">Cancelar</button>
                                    <button style="border-radius: 10px;" type="button" class="btn btn-primary"
                                        onclick="ACTORES.validateData();">Guardar</button>
                                </div>

                            </form>
                            <!-- </div>
                                </div> -->
                        </div>
                        </div></div>
                        <div class="table-responsive tabla-informacion tabla-scroll">
                        <table id="dynamictable" class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Editar</th>
                                    <th>Nombre</th>
                                    <th>Pertenece a</th>
                                    <th>Alcaldía</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($isvalid && count($arr) > 0): ?>
                                    <?php foreach ($arr as $item): ?>
                                        <tr>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" title="Editar"
                                                    onclick="ACTORES.editData(<?= htmlspecialchars($item['id']) ?>)">
                                                    <i class="feather icon-edit"></i>
                                                </button>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($item['pertenece'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['municipio'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        </div><!-- table-responsive -->
                    </div>
                </div>
            </div>
            <!-- [ sample-page ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- Required Js -->
    <!-- [ Main Content ] end -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <?php include './admin/include/generic_dataTables.php'; ?>
    <style>
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
      .btn-primary:hover{ filter: brightness(1.1); }
      .btn-secondary{
        background: rgba(255,255,255,.06) !important;
        color: rgba(255,255,255,.86) !important;
        border-color: rgba(255,255,255,.12) !important;
      }
      .btn-secondary:hover{ background: rgba(255,255,255,.12) !important; color: #fff !important; }
      .btn-danger{
        border-color: rgba(255,91,122,.45) !important;
        background: rgba(255,91,122,.15) !important;
        color: #ff5b7a !important;
      }
      .btn-danger:hover{ background: rgba(255,91,122,.30) !important; color: #fff !important; }
      .btn-sm{
        border-radius: 10px !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
      }
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
    <script type="text/javascript" src="admin/js/actores.js"></script>
    <!-- carga con el departamento santander -->
    <script>
        setTimeout(function() {
            $("#tbl_departamento_id").val('68')
        }, 500);
        setTimeout(function() {
            DEPARTAMENTO.getMunicipios();
        }, 1000);
    </script>
</body>

</html>