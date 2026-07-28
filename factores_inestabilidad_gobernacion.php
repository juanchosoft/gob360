<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

require './admin/include/generic_classes.php';
include './admin/classes/FactoresInestabilidadGobernacion.php';

$userType = SessionData::getUserType();
if ($userType !== Util::SuperAdministrador()) {
    require 'permiso_denegado.php';
}

$arr = FactoresInestabilidadGobernacion::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
?>

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
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Factores de Inestabilidad Gobernación</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#!">Configuración Acción Unificada / Factores de Inestabilidad Gobernación</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Creación de Factores de Inestabilidad</h5>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> maximize</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> remove</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="col-sm-12"></div>
                            <div class="card-body">
                                <form id="formfactores" role="form" autocomplete="false">
                                    <input type="hidden" name="op" id="op" />
                                    <input type="hidden" name="id" id="id" />
                                    <input type="hidden" id="icono_hidden" name="icono_hidden" value="">
                                    <div class="row">
                                        <div class="form-group col-md-3">
                                            <div class="form-group">
                                                <label class="floating-label" for="nombre_categoria">Nombre<span class="text-danger mb-1">*</span></label>
                                                <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" aria-describedby="" value="" placeholder="Ingrese un nombre">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputState">Icono</label>
                                            <div class="custom-file">
                                                <iframe id='ifm1' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                                            </div>
                                            <div id="icono-preview-wrap" style="display:none;margin-top:8px;">
                                                <img id="icono-preview" src="" alt="Icono" style="width:48px;height:48px;border-radius:8px;object-fit:cover;">
                                                <button type="button" onclick="FACTORES_INESTABILIDAD.removeIcono();" style="background:none;border:none;color:#ff5b7a;cursor:pointer;font-size:18px;vertical-align:top;margin-left:6px;" title="Quitar icono">&times;</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="UTIL.clearForm('formfactores');" class="btn btn-danger">Cancelar</button>
                                    <button type="button" class="btn btn-primary" onclick="FACTORES_INESTABILIDAD.save();">Guardar</button>
                                </form>
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive tabla-informacion tabla-scroll">
                            <table id="dynamictable" class="table table-hover mb-0">
                                <thead>
                                    <tr class="border-1">
                                        <th>Editar</th>
                                        <th>Icono</th>
                                        <th>Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($isvalid && !empty($arr)): ?>
                                        <?php foreach ($arr as $item): ?>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" title="Editar"
                                                        onclick="FACTORES_INESTABILIDAD.edit(<?= htmlspecialchars($item['id']) ?>)">
                                                        <i class="feather icon-edit"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <?php if (!empty($item['icono'])): ?>
                                                        <img src="<?= htmlspecialchars($item['icono']) ?>" alt="Icono" width="36" height="36" style="border-radius:6px;object-fit:cover;">
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['nombre_categoria'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No se encontraron registros.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <?php include 'admin/include/gerenic_script.php'; ?>
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
      table.dataTable tbody tr{ background-color: transparent !important; }
      table.dataTable.stripe tbody tr.odd, table.dataTable.display tbody tr.odd{ background-color: rgba(255,255,255,.03) !important; }
      table.dataTable tbody td{ color: rgba(255,255,255,.86) !important; }
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
    </style>
    <script type="text/javascript" src="admin/js/factores_inestabilidad_gobernacion.js"></script>
    <script>
    // Observar cambios en el iframe para capturar data-url post-upload
    document.addEventListener("DOMContentLoaded", function() {
        var ifm = document.getElementById('ifm1');
        if (ifm) {
            var obs = new MutationObserver(function(muts) {
                muts.forEach(function(m) {
                    if (m.type === 'attributes' && m.attributeName === 'data-url') {
                        var url = ifm.getAttribute('data-url');
                        if (url) {
                            document.getElementById('icono_hidden').value = url;
                            var pv = document.getElementById('icono-preview');
                            if (pv) pv.src = url;
                            var pw = document.getElementById('icono-preview-wrap');
                            if (pw) pw.style.display = 'block';
                        }
                    }
                });
            });
            obs.observe(ifm, { attributes: true });
        }
    });
    </script>
</body>
</html>
