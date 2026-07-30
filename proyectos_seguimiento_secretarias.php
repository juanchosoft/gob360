<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

$modulo = 'Banco Proyectos';

include './admin/classes/Proyectos.php';
include './admin/classes/Departamento.php';
include './admin/classes/Secretarias.php';

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Información de secretarias
$arr = Secretarias::getAllproyectos(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$optionSec = "";
foreach ($arr as $val) {
    $optionSec .= "<option value='" . $val['id'] . "'>" . $val['secretaria'] . " </option>";
}
?>

<link href="assets/css/proyectos_secretarias_gob360.css" rel="stylesheet">

<body class="gob360-project-bank">
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
                                <h5 class="m-b-10"><i data-feather="folder"></i> Banco de Proyectos</h5>
<?php include './admin/include/btn_back.php'; ?>

                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Banco de Proyectos / Seguimiento por secretaría</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- HERO VISUAL GOB360 -->
            <section class="g360-project-hero" aria-label="Banco de proyectos GOB360">
                <div class="g360-project-hero__grid">

                    <div>
                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-project-hero__logo"
                        >
                    </div>

                    <div>
                        <div class="g360-project-hero__eyebrow">
                            <i class="feather icon-folder"></i>
                            Planeación e inversión institucional
                        </div>

                        <h1 class="g360-project-hero__title">
                            Banco de Proyectos
                        </h1>

                        <p class="g360-project-hero__description">
                            Consulta el valor acumulado de los proyectos por
                            secretaría y accede al detalle individual para revisar
                            su información, seguimiento y ejecución institucional.
                        </p>

                        <div class="g360-project-hero__chips">
                            <span class="g360-chip g360-chip--success">
                                <i class="feather icon-check-circle"></i>
                                Información consolidada
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-briefcase"></i>
                                Detalle por secretaría
                            </span>

                            <span class="g360-chip">
                                <i class="feather icon-dollar-sign"></i>
                                Seguimiento financiero
                            </span>
                        </div>
                    </div>

                    <div class="g360-project-hero__visual" aria-hidden="true">
                        <div class="g360-mini-card">
                            <i class="feather icon-folder"></i>
                            <span>Proyectos</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-briefcase"></i>
                            <span>Secretarías</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-eye"></i>
                            <span>Detalle</span>
                        </div>

                        <div class="g360-mini-card">
                            <i class="feather icon-trending-up"></i>
                            <span>Seguimiento</span>
                        </div>
                    </div>

                </div>
            </section>

            <!-- [ Main Content ] start -->
            <!-- prject ,team member start -->
            <div class="col-xl-12 col-md-12">
                <div class="card g360-project-card">
                    <div class="card-header">
                        <div>
                            <h5>Consolidado de proyectos por secretaría</h5>
                            <p>Selecciona una secretaría para consultar su banco de proyectos.</p>
                        </div>
                        <div class="card-header-right">
                            <div class="btn-group card-option">
                                <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="feather icon-more-horizontal"></i>
                                </button>
                                <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                    <li class="dropdown-item full-card"><a href="#!"><span><i
                                                    class="feather icon-maximize"></i> Maximizar</span><span
                                                style="display:none"><i class="feather icon-minimize"></i>
                                                Restore</span></a></li>
                                    <li class="dropdown-item minimize-card"><a href="#!"><span><i
                                                    class="feather icon-minus"></i> Colapsar</span><span
                                                style="display:none"><i class="feather icon-plus"></i> Expandir</span></a>
                                    </li>
                                    <li class="dropdown-item reload-card"><a href="#!"><i
                                                class="feather icon-refresh-cw"></i> Recargar</a></li>
                                    <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i>
                                            remove</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12">
                            <div class="card-body table-border-style espacio">

                                <div class="g360-project-guide">
                                    <div class="g360-project-guide__text">
                                        <span class="g360-project-guide__icon">
                                            <i class="feather icon-info"></i>
                                        </span>

                                        <div>
                                            <h6>Consulta consolidada</h6>
                                            <p>El valor mostrado corresponde a la suma de los proyectos asociados a cada secretaría.</p>
                                        </div>
                                    </div>

                                    <span class="g360-project-guide__badge">
                                        <i class="feather icon-eye"></i>
                                        Acceso al detalle
                                    </span>
                                </div>

                                <!-- Tabla de datos -->
                                <div class="table-responsive">
                                    <table id="dynamictable" class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Ver</th>
                                                <th>Secretaría</th>
                                                <th>Valor acumulado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($isvalid && !empty($arr)) : ?>
                                                <?php foreach ($arr as $item) : ?>
                                                    <?php if ($item['mostrar'] == 'si') : ?>
                                                        <tr>
                                                            <td>
                                                                <form action="proyecto_x_secretaria.php" method="post"
                                                                    style="display:inline;">
                                                                    <input type="hidden" name="id"
                                                                        value="<?= htmlspecialchars($item['tbl_secretarias_id']); ?>">
                                                                    <input type="hidden" name="secretaria"
                                                                        value="<?= htmlspecialchars($item['tbl_secretarias_id']); ?>">
                                                                    <button type="submit" class="btn btn-sm btn-primary"
                                                                        title="Ver proyectos de la secretaría"
                                                                        aria-label="Ver proyectos de la secretaría">
                                                                        <i class="feather icon-eye"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                            <td><?= htmlspecialchars($item['secretaria']); ?></td>
                                                            <td>$ <?= number_format($item['sumaproyectos'], 2); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
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
    </div>
    </div>
    </div>

    <!-- Required Js -->
    <?php include 'admin/include/gerenic_script.php'; ?>



    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/proyectos.js"></script>


    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <?php include './admin/include/generic_dataTables.php'; ?>
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