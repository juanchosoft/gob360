<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
// Permisos
/* extract(PagePermissions::crudVarsForCurrentPage());
if (!$view) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Secretarias.php';
$modulo = 'Dashboard Secretarías';

// Informacion de la secretaria del usuario logueado
$secretariaId = SessionData::getSecretaria();

// Informacion de los proyectos en ejecucion por seretaria Id
//$responseDashboardSecretariaGraficas = Secretarias::getDashboardSecretariaGraficas(NULL);
$responseDashboardSecretariaGraficas = Secretarias::getDashboardSecretariaGraficasPorSecretariaUsuarioLogueado(NULL);
$responseGrafica =  $responseDashboardSecretariaGraficas['output']['response'] ? $responseDashboardSecretariaGraficas['output']['response'] : [];

// Hacienda 
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

$mostraDiv ="none";
if($secretariaId  == Util::getSecretariaIdHacienda()){
    $mostraDiv ="block";
}
if($isAdmin){
    $mostraDiv ="block";
}

$responseDashboardSecretariaGraficasHacienda = Secretarias::getDashboardSecretariaGraficasHacienda(NULL);
$responseGraficaHacienda =  $responseDashboardSecretariaGraficasHacienda['output']['response'];
$estampillas =  $responseDashboardSecretariaGraficasHacienda['output']['estampillas'];
$dataValores =  $responseDashboardSecretariaGraficasHacienda['output']['dataValores'];
?>
<script>
    const secretariasData = <?= json_encode($responseGrafica) ?>;
    const secretariasDataHacienda = <?= json_encode($responseGraficaHacienda) ?>;
    const dataValoresHacienda = <?= json_encode($dataValores) ?>;
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

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
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">

                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="m-b-10">DashBoard Secretarías </h5>
<?php include './admin/include/btn_back.php'; ?>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#!">DashBoard Secretarías / Seguimiento</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div style="gap:5px" class="card-header d-flex justify-content-center align-items-center">
                            <i class="bi bi-bar-chart-fill text-primary me-2" style="font-size: 1.6rem;"></i>
                            <h4 class="mb-0 fw-bold" style="font-size: 1.5rem;">DashBoard Secretarías</h4>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($responseGrafica as $item): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header text-center">
                                                <!-- Título grande -->
                                                <h4 class="fw-bold mb-1" style="font-size: 1.4rem;">
                                                    <?= htmlspecialchars($item['secretaria']) ?>
                                                </h4>

                                                <!-- Botón pequeño con estilo de cuadrito y texto verde -->
                                                <?php if (!empty($item['url'])): ?>
                                                    <a href="<?= htmlspecialchars($item['url']) ?>" class="btn btn-outline-success btn-sm mt-1">
                                                        <i class="bi bi-arrow-right-circle"></i> Ver más
                                                    </a>
                                                <?php endif; ?>

                                                <!-- Última actualización -->
                                                <?php if (!empty($item['ultima_fecha'])): ?>
                                                    <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 5px;">
                                                        <i class="bi bi-calendar-check" style="font-size: 0.9rem; color: #000;"></i>
                                                        <span class="ms-2 text-dark" style="font-size: 0.75rem;">
                                                            Última actualización: <strong><?= htmlspecialchars($item['ultima_fecha']) ?></strong>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="card-body">
                                                <div id="chart_<?= $item['secretaria_id'] ?>" style="width:100%"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="col-md-12 mb-4" style="display: <?= $mostraDiv ?>;">
                                    <div class="card">
                                        <div class="card-header text-center">
                                            <!-- Título grande -->
                                            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">Hacienda</h4>

                                            <!-- Botón Ver más -->
                                            <a href="hacienda.php?depto_id=21" class="btn btn-outline-success btn-sm mt-2">
                                                <i class="bi bi-arrow-right-circle"></i> Ver más
                                            </a>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div id="grafica_cantidad" style="width:100%; height: 450px;"></div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div id="grafica_valores" style="width:100%; height: 450px;"></div>
                                                </div>
                                            </div>
                                             <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row g-3 mt-4">
                                                        <!-- Consolidado de Estampillas -->
                                                        <div class="col-12">
                                                                <div class="card-body">
                                                                    <h5 class="card-title text-center">Recaudo Estampillas 2025</h5>

                                                                    <div class="table-responsive">
                                                                        <table class="table table-striped table-bordered table-sm">
                                                                            <thead class="thead-dark">
                                                                                <tr>
                                                                                    <th>Estampilla</th>
                                                                                    <th class="text-end">Enero</th>
                                                                                    <th class="text-end">Febrero</th>
                                                                                    <th class="text-end">Marzo</th>
                                                                                    <th class="text-end">Abril</th>
                                                                                    <th class="text-end">Mayo</th>
                                                                                    <th class="text-end">Junio</th>
                                                                                    <th class="text-end">Julio</th>
                                                                                    <th class="text-end">Agosto</th>
                                                                                    <th class="text-end">Septiembre</th>
                                                                                    <th class="text-end">Octubre</th>
                                                                                    <th class="text-end">Noviembre</th>
                                                                                    <th class="text-end">Diciembre</th>
                                                                                    <th class="text-end">Total Anual</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php
                                                                                // Asegúrate de que $estampillas esté definida y sea un array
                                                                                if (isset($estampillas) && is_array($estampillas) && !empty($estampillas)) {
                                                                                    foreach ($estampillas as $estampillaData) {
                                                                                        // Determina si es la fila TOTAL para aplicar un estilo diferente
                                                                                        $isTotalRow = (isset($estampillaData['estampilla']) && $estampillaData['estampilla'] === 'TOTAL');
                                                                                        $rowClass = $isTotalRow ? 'table-primary fw-bold' : ''; // Clase Bootstrap para resaltar la fila total
                                                                                ?>
                                                                                        <tr class="<?= htmlspecialchars($rowClass) ?>">
                                                                                            <td><?= htmlspecialchars($estampillaData['estampilla'] ?? '') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Enero'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Febrero'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Marzo'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Abril'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Mayo'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Junio'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Julio'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Agosto'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Septiembre'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Octubre'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Noviembre'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Diciembre'] ?? 0, 2, ',', '.') ?></td>
                                                                                            <td class="text-end">$<?= number_format($estampillaData['Total_Anual_Estampilla'] ?? 0, 2, ',', '.') ?></td>
                                                                                        </tr>
                                                                                <?php
                                                                                    }
                                                                                } else {
                                                                                    // Mensaje si no hay datos
                                                                                ?>
                                                                                    <tr>
                                                                                        <td colspan="14" class="text-center">No hay datos de estampillas disponibles para mostrar.</td>
                                                                                    </tr>
                                                                                <?php
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
                                </div>


                            </div> <!-- end row -->
                        </div> <!-- end card-body -->
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="assets/js/plugins/apexcharts.min.js"></script>
    <?php include 'admin/include/footer.php'; ?>
    <?php include 'admin/include/gerenic_script.php'; ?>
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script type="text/javascript" src="admin/js/dash_secretarias.js"></script>
</body>

</html>