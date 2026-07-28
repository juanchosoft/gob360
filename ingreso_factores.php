<?php

include './admin/include/head.php';
include './admin/include/dark_theme.php';

require './admin/include/generic_classes.php';
include './admin/classes/Ejes.php';
include './admin/classes/Factores.php';
include './admin/classes/FactoresInestabilidadGobernacion.php';
include './admin/classes/Secretarias.php';


// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

// Información de Factores
$arr = Factores::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];

//Información de Ejes
$arrEjes = Ejes::getAll(null);
$isvalidEje = $arrEjes['output']['valid'];
$arrEjes = $arrEjes['output']['response'];
$optionEjes = '<option value="seleccione">Seleccione...</option>';
foreach ($arrEjes as $val) {
    $optionEjes .= "<option value='" . $val['id'] . "'>" . $val['nombre'] . "</option>";
}

//Información de Factores Inestabilidad Gobernación
$arrInestabilidad = FactoresInestabilidadGobernacion::getAll(null);
$isvalidInestabilidad = $arrInestabilidad['output']['valid'];
$arrInestabilidad = $arrInestabilidad['output']['response'];
$optionInestabilidad = '<option value="seleccione">Seleccione...</option>';
if ($isvalidInestabilidad && !empty($arrInestabilidad)) {
    foreach ($arrInestabilidad as $val) {
        $icon = !empty($val['icono']) ? htmlspecialchars($val['icono']) : '';
        $optionInestabilidad .= "<option value='" . $val['id'] . "' data-icon='" . $icon . "'>" . htmlspecialchars($val['nombre_categoria'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
}

//Información de Secretarías
$arrSecretarias = Secretarias::getAll(null);
$isvalidSecretarias = $arrSecretarias['output']['valid'];
$arrSecretarias = $arrSecretarias['output']['response'];
$optionSecretarias = '<option value="seleccione">Seleccione...</option>';
if ($isvalidSecretarias && !empty($arrSecretarias)) {
    foreach ($arrSecretarias as $val) {
        $optionSecretarias .= "<option value='" . $val['id'] . "'>" . htmlspecialchars($val['secretaria'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
}
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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Mapa De Factores</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Configuración Acción Unificada / Mapa De
                                        Factores</a></li>
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
                            <h5>Creación de Factores</h5>
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
                            </div>
                            <div class="card-body">
                                <form id="formfactores" role="form" autocomplete="false">
                                    <input type="hidden" name="op" id="op" />
                                    <input type="hidden" name="id" id="id" />
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="floating-label" for="Eje">Eje<span
                                                        class="text-danger mb-1">*</span></label>
                                                <select class="form-control" id="ejeId" name="ejeId"
                                                    onchange="INGRESO_FACTORES.getPilarByEjeId();">
                                                    <?php echo $optionEjes; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="floating-label" for="tbl_factor_inestabilidad_id">Factor Inestabilidad Gobernación</label>
                                                <select class="form-control" id="tbl_factor_inestabilidad_id" name="tbl_factor_inestabilidad_id">
                                                    <?php echo $optionInestabilidad; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="floating-label" for="tbl_secretaria_id">Secretaría</label>
                                                <select class="form-control" id="tbl_secretaria_id" name="tbl_secretaria_id">
                                                    <?php echo $optionSecretarias; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputState">Pilar<span
                                                    class="text-danger mb-1">*</span></label>
                                            <select class="form-control" id="pilarId" name="pilarId"
                                                onchange="INGRESO_FACTORES.getAreaByPilarId();"></select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputState">Area<span
                                                    class="text-danger mb-1">*</span></label>
                                            <select class="form-control" id="areaId" name="areaId">
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputState">Indicador<span
                                                    class="text-danger mb-1">*</span></label>
                                            <input type="text" class="form-control" id="tipo" name="tipo"
                                                aria-describedby="" value="">
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label class="floating-label" for="Tipo">Tipo Medición<span
                                                        class="text-danger mb-1">*</span></label>
                                                <select id="tipo_medicion" name="tipo_medicion"
                                                    class="form-control">
                                                    <option value="Unidad">Unidad</option>
                                                    <option value="Metros">Metros</option>
                                                    <option value="Kilometros">Kilometros</option>
                                                    <option value="Km2">Km2</option>
                                                    <option value="Hectareas">Hectareas</option>
                                                    <option value="Porcentaje">Porcentaje</option>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputState">Puntaje<span
                                                    class="text-danger mb-1">*</span></label>
                                            <input type="text" class="form-control" id="puntaje"
                                                onKeyPress="return soloNumeros(event);" name="puntaje"
                                                aria-describedby="" value="">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputState">Icono<span
                                                    class="text-danger mb-1">*</span></label>
                                            <div class="custom-file">
                                                <iframe id='ifm1' name='ifm' src="upload.php" width="200"
                                                    height="60" scrolling="no" frameborder="0"></iframe>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="UTIL.clearForm('formfactores');"
                                        class="btn  btn-danger">Cancelar</button>
                                    <button type="button" onclick="INGRESO_FACTORES.save();"
                                        class="btn btn-primary">Guardar</button>
                                </form>
                            </div>
                            <div>
                                <hr>

                                <?php if ($userType === Util::SuperAdministrador()): ?>
                                <!-- Actualización Masiva Inestabilidad -->
                                <div class="row align-items-center mb-3" style="padding: 10px;">
                                    <div class="col-md-2">
                                        <strong>Actualización Masiva:</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="massInestabilidadId">
                                            <option value="seleccione">Seleccione Factor Inestabilidad...</option>
                                            <?php
                                            if ($isvalidInestabilidad && !empty($arrInestabilidad)) {
                                                foreach ($arrInestabilidad as $val) {
                                                    echo "<option value='" . $val['id'] . "'>" . htmlspecialchars($val['nombre_categoria'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-primary" onclick="INGRESO_FACTORES.massUpdateInestabilidad();">
                                            <i class="feather icon-refresh-ccw"></i> Asignar Inestabilidad
                                        </button>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control" id="massSecretariaId">
                                            <option value="seleccione">Seleccione Secretaría...</option>
                                            <?php
                                            if ($isvalidSecretarias && !empty($arrSecretarias)) {
                                                foreach ($arrSecretarias as $val) {
                                                    echo "<option value='" . $val['id'] . "'>" . htmlspecialchars($val['secretaria'], ENT_QUOTES, 'UTF-8') . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-primary" onclick="INGRESO_FACTORES.massUpdateSecretaria();">
                                            <i class="feather icon-refresh-ccw"></i> Asignar Secretaría
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="table-responsive tabla-informacion tabla-scroll">
                                    <table id="dynamictable" class="table table-hover mb-0">
                                        <thead>
                                            <tr class="border-1">
                                                <?php if ($userType === Util::SuperAdministrador()): ?>
                                                <th class="col-check"><input type="checkbox" id="selectAll"></th>
                                                <?php endif; ?>
                                                <th class="col-actions">Editar</th>
                                                <th class="col-actions">Eliminar</th>
                                                <th>Icono</th>
                                                <th>Factor Inestabilidad</th>
                                                <th>Secretaría</th>
                                                <th>Indicador</th>
                                                <th>Puntaje</th>
                                                <th>Eje</th>
                                                <th>Pilar</th>
                                                <th>Área</th>
                                                <th>Tipo medición</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($isvalid && !empty($arr)): ?>
                                                <?php foreach ($arr as $item): ?>
                                                    <tr>
                                                        <?php if ($userType === Util::SuperAdministrador()): ?>
                                                        <td class="col-check">
                                                            <input type="checkbox" class="factor-checkbox" value="<?= htmlspecialchars($item['id']) ?>">
                                                        </td>
                                                        <?php endif; ?>
                                                        <td class="col-actions">
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                title="Editar"
                                                                onclick="INGRESO_FACTORES.edit(<?= htmlspecialchars($item['id']) ?>)">
                                                                <i class="feather icon-edit"></i>
                                                            </button>
                                                        </td>
                                                        <td class="col-actions">
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                title="Eliminar"
                                                                onclick="INGRESO_FACTORES.deletedata(<?= htmlspecialchars($item['id']) ?>)">
                                                                <i class="feather icon-trash"></i>
                                                            </button>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $imgIcono = !empty($item["icono"]) ? htmlspecialchars($item["icono"]) : 'assets/img/santander.png';
                                                            ?>
                                                            <img width="25" height="25" src="<?= $imgIcono ?>" alt="Icono">
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($item['inestabilidad_icono'])): ?>
                                                                <img src="<?= htmlspecialchars($item['inestabilidad_icono']) ?>" alt="" width="20" height="20" style="border-radius:4px;vertical-align:middle;margin-right:4px;">
                                                            <?php endif; ?>
                                                            <?php echo htmlspecialchars($item['inestabilidad'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['secretaria_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['tipo'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['puntaje'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['eje'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['pilar'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['area'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['tipo_medicion'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </td>
                                                        
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="<?php echo ($userType === Util::SuperAdministrador()) ? 12 : 11; ?>" class="text-center">No se encontraron registros.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ sample-page ] end -->
                </div>
                <!-- [ Main Content ] end -->
            </div>
        </div>
        <!-- [ Main Content ] end -->
        <?php include 'admin/include/gerenic_script.php'; ?>
        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>

        <?php include './admin/include/generic_dataTables.php'; ?>
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
      .col-check{ width: 40px; text-align: center; }
      .col-actions{ width: 50px; text-align: center; }
      .table-compact th, .table-compact td{
        padding: 6px 8px !important;
        white-space: nowrap;
        font-size: 13px !important;
      }
      #dynamictable{
        table-layout: auto;
        width: 100% !important;
      }
      #dynamictable th, #dynamictable td{
        padding: 6px 8px !important;
        font-size: 13px !important;
      }
      #dynamictable .btn-sm{
        padding: 4px 8px !important;
        font-size: 11px !important;
      }
    </style>
        <script src="<?php echo Util::versionar('./admin/js/ingreso_factores.js'); ?>"></script>
        <script>
            setTimeout(function() {
                INGRESO_FACTORES.getPilarByEjeId();
            }, 1000);
            // Select2 para Factor Inestabilidad con iconos
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#tbl_factor_inestabilidad_id').select2({
                    templateResult: function(opt) {
                        if (!opt.id) return opt.text;
                        var icon = $(opt.element).data('icon');
                        if (icon) {
                            return $('<span><img src="' + icon + '" style="width:20px;height:20px;border-radius:4px;margin-right:8px;vertical-align:middle;"> ' + opt.text + '</span>');
                        }
                        return opt.text;
                    },
                    templateSelection: function(opt) {
                        if (!opt.id) return opt.text;
                        var icon = $(opt.element).data('icon');
                        if (icon) {
                            return $('<span><img src="' + icon + '" style="width:18px;height:18px;border-radius:4px;margin-right:6px;vertical-align:middle;"> ' + opt.text + '</span>');
                        }
                        return opt.text;
                    }
                });
            }
        </script>
        <style>
        .select2-container--default .select2-selection--single {
            background: rgba(255,255,255,.06) !important;
            border: 1px solid rgba(255,255,255,.12) !important;
            border-radius: 14px !important;
            height: auto !important;
            padding: 6px 10px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: rgba(255,255,255,.86) !important;
            line-height: normal !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { top: 8px !important; }
        .select2-dropdown {
            background: #10172b !important;
            border: 1px solid rgba(255,255,255,.12) !important;
        }
        .select2-results__option {
            color: rgba(255,255,255,.86) !important;
        }
        .select2-results__option--highlighted {
            background: rgba(79,124,255,.25) !important;
        }
        </style>
</body>

</html>
