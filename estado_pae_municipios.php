<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
//Validación
/* if (!$create) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Departamento.php';
include './admin/classes/IngresoPae.php';


function getUrl()
{
    $port = $_SERVER["SERVER_PORT"];
    $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
    $url = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $nameServer,
        $_SERVER['REQUEST_URI']
    );
    return str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
}


$arrPae = IngresoPae::getIngresoPaeByMunicipioCodigo(["tbl_municipio_id" => $_REQUEST["mun"]]);
$pae = [];

if (is_array($arrPae) && isset($arrPae["output"]["response"])) {
    $pae = $arrPae["output"]["response"];
}



// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
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
                                <h5 class="m-b-10">Caracterización Pae Municipios</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Caracterización Pae Municipios</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <div id="divInformacionGeneral" class="row">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card">


                        <div class="card-body">
                            <div class="col-sm-12">
                                <div class="card-body">
                                    <form id="formusuarios" role="form" autocomplete="false">
                                        <input type="hidden" name="op" id="op" />
                                        <input type="hidden" name="id" id="id" />
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-md-6">

                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Departamento.</label>
                                                        <select class="form-control" style="width: 100%;" disabled
                                                            id="tbl_departamento_id"
                                                            name="tbl_departamento_id"><?php echo $optionDep; ?></select>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating">Municipio.</label>
                                                        <select onchange="ESTADO_MUN_PAE.updateUrlMunicipio(this)"
                                                            class="form-control" style="width: 100%;"
                                                            id="tbl_municipio_id" name="tbl_municipio_id"> </select>
                                                    </div>
                                                </div>
                                            </div>
                                    </form>
                                </div>

                                <!-- Inversión Detallada -->
                                <div class="row justify-content-center">
                                    <div class="col-12 col-lg-10">
                                        <div class="section-block text-center mb-4">
                                            <h3 class="section-title" style="font-size: 16px;">
                                                Caracterización detallada por Sede Educativa
                                            </h3>
                                        </div>

                                        <div id="tablaContenidoPae" class="table-responsive">
                                            <table class="table table-bordered table-hover text-center">
                                                <thead class="thead-dark">
                                                    <tr>
                                                        <th>id</th>
                                                        <th>Ver Detallado</th>
                                                        <th>Provincia</th>
                                                        <th>Municipio</th>
                                                        <th>Vereda</th>
                                                        <th>Nombre Institución</th>
                                                        <th>Nombre Sede Educativa</th>
                                                        <th>Año</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        if (is_array($pae) && !empty($pae)) :
                                                            foreach ($pae as $value) :
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($value['id'] ?? '') ?></td>
                                                                <td>
                                                                    <a href="reporte_pae.php?reporte=<?= htmlspecialchars($value['id'] ?? '') ?>" target="_blank" title="Ver">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                </td>
                                                                <td><?= htmlspecialchars($value['provincia'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($value['municipio'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($value['nombre_vereda'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($value['nombre_institucion'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($value['nombre'] ?? '') ?></td>
                                                                <td><?= htmlspecialchars($value['ano'] ?? '') ?></td>
                                                            </tr>
                                                        <?php
                                                            endforeach;
                                                        else :
                                                        ?>
                                                            <tr>
                                                                <td colspan="8" class="text-center text-muted">No se encontraron registros.</td>
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
                <!-- [ sample-page ] end -->
            </div>
            <!-- [ Main Content ] end -->
        </div>
    </div>
    <!-- [ Main Content ] end -->


    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/estado_municipios_pae.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Morris.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>

    <!-- // Variables para mostrrar en los graficos -->

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