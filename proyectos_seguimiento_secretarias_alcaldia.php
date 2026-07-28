<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';

$modulo = 'Banco Proyectos Alcaldía';

include './admin/classes/Proyectos.php';
include './admin/classes/Secretarias.php';


// Información de secretarias
$arr = Secretarias::getAllproyectos(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
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
                                <h5 class="m-b-10"><i data-feather="folder"></i> Proyectos Secretarías Alcaldia</h5>
<?php include './admin/include/btn_back.php'; ?>

                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Proyectos Secretaría / Seguimiento
                                        Proyectos Alcaldia</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <!-- prject ,team member start -->
            <div class="col-xl-12 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Detalle Proyectos Secretarías</h5>
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
                                                style="display:none"><i class="feather icon-plus"></i> expand</span></a>
                                    </li>
                                    <li class="dropdown-item reload-card"><a href="#!"><i
                                                class="feather icon-refresh-cw"></i> reload</a></li>
                                    <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i>
                                            remove</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-12">
                            <div class="card-body table-border-style espacio">
                                <!-- Tabla de datos -->
                                <div class="table-responsive">
                                    <table id="dynamictable" class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Ver</th>
                                                <th>Secretaría</th>
                                                <th>Suma Proyectos</th>
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
                                                                        title="Ver">
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