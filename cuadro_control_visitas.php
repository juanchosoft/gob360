<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
//Validación
/* if (!$view) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Visitas.php';


//Información de Vistas
$arr = Visitas::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$modulo = 'Registro Visitas';

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
    <style>
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        #dynamictable {
            width: 100% !important;
            table-layout: auto;
            white-space: normal;
        }

        html,
        body {
            overflow-x: hidden !important;
        }

        #dynamictable td {
            white-space: normal !important;
            word-break: break-word !important;
            max-width: 300px;
            vertical-align: top;
        }
    </style>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">Cuadro Detalle Visitas en los municipios</h5>
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Registro de visitas / Cuadro de Control
                                        Visitas</a></li>
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
                            <h5>Listado de Visitas</h5>
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
                            <div class="col-lg-12">

                                <div class="card-body table-border-style">
                                    <!-- Tabla de datos -->
                                    <div class="table-responsive">
                                        <table id="dynamictable" class="table table-striped table-bordered">
                                            <thead class="bg-light text-dark">
                                                <tr class="border-1">
                                                    <th>Id</th>
                                                    <th>Ver</th>
                                                    <th>Foto</th>
                                                    <th>Fecha</th>
                                                    <th>Provincia</th>
                                                    <th>Municipio</th>
                                                    <th>Motivo Visita</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($isvalid): ?>
                                                    <?php
                                                    $imgBasePath = "assets/img/admin/";
                                                    foreach ($arr as $item):
                                                        $img = !empty($item["img"]) ? $imgBasePath . htmlspecialchars($item["img"]) : 'dist/img/santander.png';
                                                    ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['id']); ?></td>
                                                            <td>
                                                                <form action="reporte_visita.php" method="POST">
                                                                    <input type="hidden" name="reporte" value="<?php echo htmlspecialchars($item['id']); ?>">
                                                                    <button type="submit" class="btn btn-sm btn-primary" title="Ver">
                                                                        <i class="feather icon-eye"></i>


                                                                    </button>
                                                                </form>
                                                            </td>
                                                            <td class="text-primary">
                                                                <img
                                                                    width="60"
                                                                    height="60"
                                                                    src="<?php echo $img; ?>"
                                                                    alt="Imagen líder"
                                                                    data-toggle="modal"
                                                                    data-target="#imageModal<?php echo $item['id']; ?>"
                                                                    style="cursor: pointer;">

                                                                <!-- Modal -->
                                                                <div class="modal fade" id="imageModal<?php echo $item['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="imageModalLabel<?php echo $item['id']; ?>">
                                                                                    Foto del municipio de <?php echo htmlspecialchars($item['municipio']); ?>, provincia de <?php echo htmlspecialchars($item['provincia']); ?>
                                                                                </h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body text-center">
                                                                                <img src="<?php echo $img; ?>" alt="Imagen líder" class="img-fluid">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>

                                                            <td><?php echo htmlspecialchars($item['date']); ?></td>
                                                            <td><?php echo htmlspecialchars($item['provincia']); ?></td>
                                                            <td><?php echo htmlspecialchars($item['municipio']); ?></td>
                                                            <td><?php echo htmlspecialchars($item['compromisos']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============================================================== -->
                        <!-- end campaign activities   -->
                        <!-- ============================================================== -->
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- footer -->
                <!-- ============================================================== -->

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
    <script type="text/javascript" src="admin/js/departamento.js"></script>
    <script type="text/javascript" src="admin/js/detalle_visitas.js"></script>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <?php include './admin/include/generic_dataTables.php'; ?>

</body>

</html>