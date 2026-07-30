<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
include './admin/classes/Proyectos.php';
$modulo = 'Banco Proyectos';


// Información de secretarias
$arr = Proyectos::getAllproyectosxsecre($_REQUEST);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$arrData = $arr;

?>

<link href="assets/css/detalle_proyectos_secretaria_gob360.css" rel="stylesheet">

<body class="gob360-project-detail">
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
                                <h5 class="m-b-10">Detalle de Proyectos</h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">Proyectos Secretarías /
                                        Seguimiento Proyectos
                                        / Detalle Proyectos Secretarias</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <section class="g360-project-detail-hero" aria-label="Detalle de proyectos por secretaría GOB360">
                <div class="g360-project-detail-hero__grid">
                    <div>
                        <img src="assets/img/gob360l.png" alt="Logo GOB360" class="g360-project-detail-hero__logo">
                    </div>

                    <div>
                        <div class="g360-project-detail-hero__eyebrow">
                            <i class="feather icon-folder"></i>
                            Seguimiento institucional de proyectos
                        </div>

                        <h1 class="g360-project-detail-hero__title">Detalle de Proyectos</h1>

                        <p class="g360-project-detail-hero__description">
                            Consulta los proyectos asociados a la secretaría, revisa su valor,
                            municipio, fecha de entrega, estado y niveles de ejecución física y financiera.
                        </p>

                        <div class="g360-project-detail-hero__chips">
                            <span class="g360-chip g360-chip--success">
                                <i class="feather icon-check-circle"></i>
                                Seguimiento consolidado
                            </span>
                            <span class="g360-chip">
                                <i class="feather icon-trending-up"></i>
                                Avance de ejecución
                            </span>
                            <span class="g360-chip">
                                <i class="feather icon-dollar-sign"></i>
                                Control financiero
                            </span>
                        </div>
                    </div>

                    <div class="g360-project-detail-hero__visual" aria-hidden="true">
                        <div class="g360-mini-card"><i class="feather icon-map-pin"></i><span>Municipio</span></div>
                        <div class="g360-mini-card"><i class="feather icon-calendar"></i><span>Entrega</span></div>
                        <div class="g360-mini-card"><i class="feather icon-activity"></i><span>Ejecución</span></div>
                        <div class="g360-mini-card"><i class="feather icon-dollar-sign"></i><span>Financiero</span></div>
                    </div>
                </div>
            </section>

            <!-- [ Main Content ] start -->
            <div class="row">

                <!-- prject ,team member start -->
                <div class="col-xl-12 col-md-12">
                    <div class="card g360-project-detail-card">
                        <div class="card-header">
                            <div>
                                <h5>Proyectos asociados a la secretaría</h5>
                                <p>Consulta el detalle y abre cada proyecto para revisar su información completa.</p>
                            </div>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> Expandir</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                                        <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Eliminar</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="col-lg-12">
                                <div class="card-body table-border-style">

                                    <div class="g360-detail-guide">
                                        <div class="g360-detail-guide__content">
                                            <span class="g360-detail-guide__icon">
                                                <i class="feather icon-info"></i>
                                            </span>
                                            <div>
                                                <h6>Seguimiento por proyecto</h6>
                                                <p>Usa el botón de visualización para abrir el detalle individual de cada registro.</p>
                                            </div>
                                        </div>
                                        <span class="g360-detail-guide__badge">
                                            <i class="feather icon-eye"></i>
                                            Acceso al expediente
                                        </span>
                                    </div>

                                    <!-- Tabla de datos -->
                                    <div class="table-responsive">
                                        <table id="dynamictable" class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Ver detalle</th>
                                                    <th>Item</th>
                                                    <th>Municipio</th>
                                                    <th>Secretaría</th>
                                                    <th>Valor Proyecto</th>
                                                    <th>Nombre Proyecto</th>
                                                    <th>Valor Proyecto</th>
                                                    <th>Fecha Entrega</th>
                                                    <th>Estado</th>
                                                    <th>Ejecución física</th>
                                                    <th>Ejecución financiera</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $c = count($arr);
                                                if ($isvalid) {
                                                    for ($i = 0; $i < $c; $i++) {
                                                ?>
                                                        <tr>
                                                            <td>
                                                                <button type="button" id="<?php echo $arr[$i]['id']; ?>" title="Ver detalle del proyecto"
                                                                    onclick="location.href='detalle_proyectos_Secretarias.php?id=<?php echo $arr[$i]['id']; ?>&nombre=<?php echo $arr[$i]['nombre']; ?>'"
                                                                    class="btn btn-sm btn-outline-success">
                                                                    <i data-feather="eye" width="16" height="16"></i>
                                                                </button>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($arr[$i]['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($arr[$i]['municipio']); ?></td>
                                                            <td><?php echo htmlspecialchars($arr[$i]['secretaria']); ?></td>
                                                            <td>$ <?php echo number_format($arr[$i]['valor_proyecto'], 2); ?></td>
                                                            <td>
                                                                <?php
                                                                $nombreProyecto = htmlspecialchars($arr[$i]['proyecto']);
                                                                $idProyecto = $arr[$i]['id'];
                                                                $corto = mb_strimwidth($nombreProyecto, 0, 50, '...');
                                                                ?>
                                                                <span><?php echo $corto; ?></span>
                                                                <?php if (strlen($nombreProyecto) > 50): ?>
                                                                    <button class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#modalProyecto_<?php echo $idProyecto; ?>">
                                                                        Ver más
                                                                    </button>

                                                                    <!-- Modal -->
                                                                    <div class="modal fade" id="modalProyecto_<?php echo $idProyecto; ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel_<?php echo $idProyecto; ?>" aria-hidden="true">
                                                                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header text-center w-100">
                                                                                    <h5 class="modal-title mx-auto" id="modalLabel_<?php echo $idProyecto; ?>">Nombre completo del proyecto</h5>
                                                                                    <button type="button" class="close position-absolute" style="right: 1rem;" data-dismiss="modal" aria-label="Cerrar">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body text-center">
                                                                                    <div style="white-space: normal; word-wrap: break-word; word-break: break-word; font-size: 1rem;">
                                                                                        <?php echo nl2br($nombreProyecto); ?>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer justify-content-center">
                                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                <?php endif; ?>
                                                            </td>

                                                            <td><?php echo number_format($arr[$i]['valor_proyecto']); ?></td>
                                                            <td><?php echo htmlspecialchars($arr[$i]['fecha_entrega']); ?></td>
                                                            <td><?php echo htmlspecialchars($arr[$i]['estado']); ?></td>
                                                            <td>
                                                                <div class="progress progress-lg">
                                                                    <div class="progress-bar progress-bar-danger linevertical"
                                                                        style="width: <?php echo round($arr[$i]['porcentaje_ejecucion'], 0); ?>%">
                                                                        <?php echo round($arr[$i]['porcentaje_ejecucion'], 0); ?>%
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="porcentaje-financiero">
                                                                <span class="badge bg-primary">
                                                                    <?php echo round($arr[$i]['porcentaje_financiero'], 0); ?>%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                }
                                                ?>
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
     
        <?php include 'admin/include/gerenic_script.php'; ?>

        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>

        <?php include './admin/include/generic_dataTables.php'; ?>

</body>

</html>