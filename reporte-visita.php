<?php

include './admin/include/head.php';
require './admin/include/generic_classes.php';

include './admin/classes/Visitas.php';

if (!empty($_POST['reporte']) && isset($_POST['reporte']) && $_POST['reporte'] > 0) {
    $id = (int)$_POST['id'];
    $result = Visitas::getVisitaId($id);

    if ($result['state'] && count($result['data']) > 0) {
        $data = $result['data'][0]; // ahora sí tomas el primer registro

        $dtcreate = $data['created_at'] ?? '';
        $municipio = $data['municipio'] ?? '';
        $compromisos = $data['compromisos'] ?? '';
        $provincia = $data['provincia'] ?? '';
        $tipo_visita = $data['tipo_visita'] ?? '';
        $id = $data['id'] ?? '';
        $compromisopac = $data['compromisopac'] ?? '';
        $foto = $data['foto'] ?? '';

        // Mostrar por ejemplo
        /*         echo "<h3>Detalle de la visita</h3>";
        echo "<p><strong>Fecha:</strong> $dtcreate</p>";
        echo "<p><strong>Municipio:</strong> $municipio</p>";
        echo "<p><strong>Provincia:</strong> $provincia</p>";
        echo "<p><strong>Tipo de visita:</strong> $tipo_visita</p>";
        echo "<p><strong>Compromisos:</strong> $compromisos</p>";
        echo "<p><strong>ID:</strong> $id</p>"; */
    } else {
?>
        <script type='text/javascript'>
            alert('Sin resultados');
            window.location = 'detalle_visitas.php';
        </script>
    <?php
        return;
    }
} else { ?>
    <script type='text/javascript'>
        alert('Debes enviar una reporte para generar el documento');
        window.location = 'detalle_visitas.php';
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
                                                <h5 class="m-b-10">Reporte de visitas a territorios</h5>
<?php include './admin/include/btn_back.php'; ?>
                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Primera dama</a></li>
                                                <li class="breadcrumb-item"><a href="#!">Reporte visitas</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ breadcrumb ] end -->
                            <!-- [ Main Content ] start -->
                            <div class="col-md-12 col-xl-12">
                                <div class="card shadow-lg border-0 mb-4">
                                    <div class="card-header bg-light p-4 d-flex justify-content-between align-items-center">
                                        <?php include 'admin/include/generinc_brand_logo.php'; ?>
                                        <div>
                                            <h4 class="mb-0 font-weight-bold text-uppercase text-primary">
                                                ACTA DE VISITA N° <?php echo $id; ?>
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
                                                <div><strong>Fecha de creación:</strong> <?php echo $dtcreate; ?></div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mb-4">
                                            <table class="table table-bordered text-center">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Fecha Visita</th>
                                                        <th>Provincia</th>
                                                        <th>Municipio</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><?php echo $dtcreate; ?></td>
                                                        <td><?php echo $provincia; ?></td>
                                                        <td><?php echo $municipio; ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="m-4">
                                            <h5 class="text-dark font-weight-bold">Detalle visita:</h5>
                                            <p class="text-justify"><?php echo $compromisos; ?></p>
                                        </div>

                                        <?php if (!empty($foto) || !empty($imagen2)): ?>
                                            <div class="m-4">
                                                <h5 class="text-dark font-weight-bold">Registro Fotográfico</h5>

                                                <?php if (!empty($foto)): ?>
                                                    <div class="registroFotografico border rounded p-2 text-center mb-3">
                                                        <img src="<?php echo htmlspecialchars($foto); ?>" width="400" height="auto" alt="Foto">
                                                    </div>
                                                <?php endif; ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-footer bg-white text-center text-muted">

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


</body>

</html>