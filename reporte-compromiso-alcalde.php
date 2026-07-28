<?php

include './admin/include/head.php';
require './admin/include/generic_classes.php';

include './admin/classes/CompromisoMunicipioAlcalde.php';

if (!empty($_POST['reporte']) && isset($_POST['reporte']) && $_POST['reporte'] > 0) {
    $id = (int) $_POST['id'];
    $result = CompromisoMunicipioAlcalde::getCompromisoId(['id' => $id]);

    // Observaciones
    $observacionesData = [];
    if ($result['state'] && isset($result['observaciones']) && count($result['observaciones']) > 0) {
        $observacionesData = $result['observaciones'];
    }

    if ($result['state'] && isset($result['data']) && count($result['data']) > 0) {
        $data = $result['data'][0]; // Tomar el primer registro

        $created_at = $data['created_at'] ?? '';
        $municipio = $data['municipio'] ?? '';
        $compromisos = $data['compromisos'] ?? '';
        $vereda = $data['vereda'] ?? 'Sin vereda';
        $id = $data['id'] ?? '';
        $compromisopac = $data['compromiso_pactado'] ?? '';
        $foto = $data['img'] ?? '';
        $estado = $data['cumplimiento'] ?? '';
        $respuesta = $data['respuesta'] ?? '';
        $consecuencia = $data['consecuencia'] ?? '';
        $componente = $data['componente'] ?? '';
        $tipo_ejecucion = $data['tipo_ejecucion'] ?? '';
        $secretaria = $data['secretaria'] ?? '';
        $date = $data['date'] ?? '';

    } else {
        ?>
        <script type='text/javascript'>
            alert('Sin resultados');
            window.close();
        </script>
        <?php
        return;
    }
} else { ?>
    <script type='text/javascript'>
        alert('Debes enviar un reporte para generar el documento');
        window.close();
    </script>
    <?php
    return;
}
?>

<style>
    .registroFotografico img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>

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


    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ breadcrumb ] start -->
                            <div class="page-header">
                                <div class="page-block">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="m-b-10">Reporte de compromiso del Alcalde</h5>
                                                <?php include './admin/include/btn_back.php'; ?>


                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Alcalde</a></li>
                                                <li class="breadcrumb-item"><a href="#!">Reporte compromiso</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ breadcrumb ] end -->
                            <!-- [ Main Content ] start -->
                            <div class="col-md-12 col-xl-12">

                                <div class="card shadow-lg border-0 mb-4">
                                    <div
                                        class="card-header bg-light p-4 d-flex justify-content-between align-items-center">
                                        <?php include 'admin/include/generinc_brand_logo.php'; ?>

                                        <div>
                                            <h4 class="mb-0 font-weight-bold text-uppercase text-primary">
                                                ACTA DE COMPROMISO N°: <?php echo $id; ?>
                                            </h4>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row mb-4 text-center">
                                            <div class="col-sm-6">
                                                <h5 class="text-dark font-weight-bold mb-1">REPÚBLICA DE COLOMBIA</h5>
                                                <h5 class="mb-1">DEPARTAMENTO DE SANTANDER</h5>
                                                <h5 class="mb-1 text-muted">GOBERNACIÓN DE SANTANDER</h5>
                                            </div>
                                            <div class="col-sm-6 text-sm-right text-center mt-3 mt-sm-0">
                                                <div><strong>Página:</strong> 1 de 1</div>
                                                <div><strong>Código:</strong> 005</div>
                                                <div><strong>Versión:</strong> 7</div>
                                                <div><strong>Fecha de creación:</strong> <?php echo $created_at; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered">
                                                <thead class="thead-light text-center">
                                                    <tr>
                                                        <th>Fecha de Visita</th>
                                                        <th>Vereda</th>
                                                        <th>Municipio</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-center">
                                                    <tr>
                                                        <td><?php echo $date; ?></td>
                                                        <td><?php echo $vereda; ?></td>
                                                        <td><?php echo $municipio; ?></td>
                                                        <td>
                                                            <span class="badge
        <?php
        echo $estado === 'Cumplido' ? 'badge-success' : ($estado === 'En Trámite' ? 'badge-warning text-dark' : 'badge-danger');
        ?>">
                                                                <?php echo $estado; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered">
                                                <thead class="thead-light text-center">
                                                    <tr>
                                                        <th>Secretaría</th>
                                                        <th>Componente</th>
                                                        <th>Tipo Ejecución</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-center">
                                                    <tr>
                                                        <td><?php echo $secretaria; ?></td>
                                                        <td><?php echo $componente; ?></td>
                                                        <td><?php echo $tipo_ejecucion; ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="m-4">
                                            <div class="row">
                                                <!-- Columna izquierda centrada -->
                                                <div
                                                    class="col-md-12 mb-4 d-flex flex-column align-items-center text-center">
                                                    <?php if (!empty($compromisos)): ?>
                                                        <h5 class="text-primary font-weight-bold">Compromiso:</h5>
                                                        <p class="text-justify">
                                                            <?php echo !is_null($compromisos) ? $compromisos : '<span class="text-muted">Sin información</span>'; ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($compromisopac)): ?>
                                                        <h5 class="text-primary font-weight-bold">Compromiso Pactado:</h5>
                                                        <p class="text-justify"><?php echo $compromisopac; ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($consecuencia)): ?>
                                                        <h5 class="text-primary font-weight-bold">Consecuencia:</h5>
                                                        <p class="text-justify">
                                                            <?php echo !is_null($consecuencia) ? $consecuencia : '<span class="text-muted">Sin consecuencia</span>'; ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($respuesta)): ?>
                                                        <h5 class="text-primary font-weight-bold">Respuesta:</h5>
                                                        <p class="text-justify">
                                                            <?php echo !is_null($respuesta) ? $respuesta : '<span class="text-muted">Sin respuesta</span>'; ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- Tabla de Observaciones -->
                                        <?php if (!empty($observacionesData)): ?>
                                            <div class="m-4">
                                                <div class="row">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead class="thead-light text-center">
                                                                <tr>
                                                                    <th>Fecha</th>
                                                                    <th>Observación</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($observacionesData as $observacion): ?>
                                                                    <tr>
                                                                        <td class="text-center">
                                                                            <?php echo isset($observacion['fecha']) ? ($observacion['fecha']) : ''; ?>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <?php echo isset($observacion['observacion']) ? ($observacion['observacion']) : ''; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($foto)): ?>
                                            <div class="m-4">
                                                <h5 class="text-dark font-weight-bold">Registro Fotográfico</h5>

                                                <div class="registroFotografico border rounded p-2 text-center mb-3">
                                                    <img src="assets/img/admin/<?php echo htmlspecialchars($foto); ?>" width="400"
                                                        height="auto" alt="Foto">
                                                </div>

                                            </div>
                                        <?php endif; ?>

                                    </div>

                                    <div class="card-footer bg-white text-center text-muted">
                                        <small>Generado automáticamente por el sistema de compromisos - Gobernación de
                                            Santander</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'admin/include/gerenic_script.php'; ?>
        <script></script>
        <!-- Required Js -->
        <script src="assets/js/vendor-all.min.js"></script>
        <script src="assets/js/plugins/bootstrap.min.js"></script>
        <script src="assets/js/pcoded.min.js"></script>

        <!-- prism Js -->
        <script src="assets/js/plugins/prism.js"></script>

        <!-- Apex Chart -->
        <script src="assets/js/plugins/apexcharts.min.js"></script>

</body>

</html>
