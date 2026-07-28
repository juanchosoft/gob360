<?php

include './admin/include/head.php';
require './admin/include/generic_classes.php';

include './admin/classes/Visitas.php';

if (!empty($_POST['reporte']) && isset($_POST['reporte']) && $_POST['reporte'] > 0) {
    $id = (int) $_POST['id'];
    $result = Visitas::getCompromisoId($id);

    // Observaciones
    $observacionesData = [];
    if ($result['state'] && count($result['observaciones']) > 0) {
        $observacionesData = $result['observaciones'];
    }

    if ($result['state'] && count($result['data']) > 0) {
        $data = $result['data'][0]; // ahora sí tomas el primer registro

        $created_at = $data['created_at'] ?? '';
        $municipio = $data['municipio'] ?? '';
        $compromisos = $data['compromisos'] ?? '';
        $provincia = $data['provincia'] ?? '';
        $tipo_visita = $data['tipo_visita'] ?? '';
        $id = $data['id'] ?? '';
        $compromisopac = $data['compromisopac'] ?? '';
        $foto = $data['img'] ?? '';
        $imagen2 = $data['imagen2'] ?? '';
        $pdf = $data['pdf'] ?? '';
        $pdf2 = $data['pdf2'] ?? '';
        $pdf3 = $data['pdf3'] ?? '';
        $pdf4 = $data['pdf4'] ?? '';
        $estado = $data['estado'] ?? '';
        $respuesta = $data['respuesta'] ?? '';
        $nombre_usuario_creador  = ($data['nombre_usuario_creador'] ?? '') . ' ' . ($data['apellido_usuario_creador'] ?? '');
        $nickname_usuario_creador = $data['nickname_usuario_creador'] ?? '';
        $nombre_usuario_traslado = ($data['nombre_usuario_traslado'] ?? '') . ' ' . ($data['apellido_usuario_traslado'] ?? '');

        // Trazabilidad: extraer última entrada de cada campo del historial
        $historial = $result['historial'] ?? [];
        $ultimo_campo = [];
        foreach ($historial as $h) {
            $campo = $h['campo'] ?? '';
            if ($campo && !isset($ultimo_campo[$campo])) {
                $ultimo_campo[$campo] = $h; // primera aparición = más reciente (ORDER BY DESC)
            }
        }
        $info_compromiso_pactado = $ultimo_campo['compromiso_pactado'] ?? null;
        $info_respuesta          = $ultimo_campo['respuesta'] ?? null;
        $info_acciones_tomadas   = $ultimo_campo['acciones_tomadas'] ?? null;
        $info_pdf   = $ultimo_campo['pdf']   ?? null;
        $info_pdf2  = $ultimo_campo['pdf2']  ?? null;
        $info_pdf3  = $ultimo_campo['pdf3']  ?? null;
        $info_pdf4  = $ultimo_campo['pdf4']  ?? null;
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
    .rc-text-block {
        text-align: justify;
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere;
        max-width: 100%;
    }
    .rc-obs-wrap {
        max-width: 100%;
        overflow-x: auto;
    }
    .rc-obs-table {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0;
    }
    .rc-obs-table th:nth-child(1),
    .rc-obs-table td:nth-child(1) { width: 18%; }
    .rc-obs-table th:nth-child(2),
    .rc-obs-table td:nth-child(2) { width: 54%; }
    .rc-obs-table th:nth-child(3),
    .rc-obs-table td:nth-child(3) { width: 28%; }
    .rc-obs-table td {
        vertical-align: top;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .rc-obs-text {
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere;
        max-height: 220px;
        overflow-y: auto;
        text-align: left;
        font-size: 13px;
        line-height: 1.4;
    }
    .rc-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 14px;
        justify-content: center;
        max-width: 100%;
    }
    .rc-meta span {
        min-width: 0;
        word-break: break-word;
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
                                                <h5 class="m-b-10">Reporte de compromiso a territorios</h5>
                                                <?php include './admin/include/btn_back.php'; ?>


                                            </div>
                                            <ul class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="index.html"><i
                                                            class="feather icon-home"></i></a></li>
                                                <li class="breadcrumb-item"><a href="#!">Primera dama</a></li>
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
                                                        <th>Provincia</th>
                                                        <th>Municipio</th>
                                                        <th>Estado</th>
                                                        <th>Creado por</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-center">
                                                    <tr>
                                                        <td><?php echo $created_at; ?></td>
                                                        <td><?php echo $provincia; ?></td>
                                                        <td><?php echo $municipio; ?></td>
                                                        <td>
                                                            <span class="badge 
        <?php
        echo $estado === 'Cumplido' ? 'badge-success' : ($estado === 'En Trámite' ? 'badge-warning text-dark' : 'badge-danger');
        ?>">
                                                                <?php echo $estado; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small>
                                                                <i class="feather icon-user"></i>
                                                                <?php echo htmlspecialchars($nombre_usuario_creador ?: 'Sistema'); ?>
                                                                <?php if ($nickname_usuario_creador): ?>
                                                                    <br><span class="text-muted"><i class="feather icon-at-sign"></i> <?php echo htmlspecialchars($nickname_usuario_creador); ?></span>
                                                                <?php endif; ?>
                                                            </small>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="m-4">
                                            <div class="row">
                                                <!-- Columna izquierda centrada -->
                                                <div
                                                    class="col-md-12 mb-4 d-flex flex-column align-items-center text-center">
                                                    
                                                    <?php if (!empty($compromisopac)): ?>
                                                        <h5 class="text-primary font-weight-bold">Compromiso Pactado</h5>
                                                        <p class="rc-text-block"><?php echo htmlspecialchars($compromisopac); ?></p>
                                                        <?php if ($info_compromiso_pactado): ?>
                                                            <small class="text-muted d-block mb-3 rc-meta">
                                                                <span><i class="feather icon-user"></i>
                                                                <?php echo htmlspecialchars($info_compromiso_pactado['usuario_nombre'] . ' ' . $info_compromiso_pactado['usuario_apellido']); ?></span>
                                                                <span><i class="feather icon-at-sign"></i>
                                                                <?php echo htmlspecialchars($info_compromiso_pactado['usuario_nickname'] ?? ''); ?></span>
                                                                <span><i class="feather icon-clock"></i>
                                                                <?php echo htmlspecialchars($info_compromiso_pactado['created_at'] ?? ''); ?></span>
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <?php if (!empty($compromisos)): ?>
                                                        <h5 class="text-primary font-weight-bold">Compromisos</h5>
                                                        <p class="rc-text-block">
                                                            <?php echo !is_null($compromisos) ? htmlspecialchars($compromisos) : '<span class="text-muted">Sin información</span>'; ?>
                                                        </p>
                                                        <?php if ($info_acciones_tomadas): ?>
                                                            <small class="text-muted d-block mb-3 rc-meta">
                                                                <span><i class="feather icon-user"></i>
                                                                <?php echo htmlspecialchars($info_acciones_tomadas['usuario_nombre'] . ' ' . $info_acciones_tomadas['usuario_apellido']); ?></span>
                                                                <span><i class="feather icon-at-sign"></i>
                                                                <?php echo htmlspecialchars($info_acciones_tomadas['usuario_nickname'] ?? ''); ?></span>
                                                                <span><i class="feather icon-clock"></i>
                                                                <?php echo htmlspecialchars($info_acciones_tomadas['created_at'] ?? ''); ?></span>
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($respuesta)): ?>
                                                        <h5 class="text-primary font-weight-bold">Respuesta</h5>
                                                        <p class="rc-text-block">
                                                            <?php echo !is_null($respuesta) ? htmlspecialchars($respuesta) : '<span class="text-muted">Sin respuesta</span>'; ?>
                                                        </p>
                                                        <?php if ($info_respuesta): ?>
                                                            <small class="text-muted d-block mb-3 rc-meta">
                                                                <span><i class="feather icon-user"></i>
                                                                <?php echo htmlspecialchars($info_respuesta['usuario_nombre'] . ' ' . $info_respuesta['usuario_apellido']); ?></span>
                                                                <span><i class="feather icon-at-sign"></i>
                                                                <?php echo htmlspecialchars($info_respuesta['usuario_nickname'] ?? ''); ?></span>
                                                                <span><i class="feather icon-clock"></i>
                                                                <?php echo htmlspecialchars($info_respuesta['created_at'] ?? ''); ?></span>
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                </div>

                                            </div>
                                        </div>

                                        <!-- Tabla de Observaciones -->
                                        <?php if (!empty($observacionesData)): ?>
                                            
                                            <div class="m-4">
                                                <div class="rc-obs-wrap">
                                                        <h5 class="text-primary font-weight-bold text-center">Acciones Tomadas en el proceso</h5>
                                                        <table class="table table-bordered table-hover rc-obs-table">
                                                            <thead class="thead-light text-center">
                                                                <tr>
                                                                    <th>Fecha</th>
                                                                    <th>Observación</th>
                                                                    <th>Registrado por</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($observacionesData as $observacion): ?>
                                                                    <tr>
                                                                        <td class="text-center">
                                                                            <?php echo htmlspecialchars($observacion['dtcreate'] ?? ''); ?>
                                                                        </td>
                                                                        <td>
                                                                            <div class="rc-obs-text">
                                                                                <?php echo htmlspecialchars($observacion['observaciones'] ?? ''); ?>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <small>
                                                                                <?php
                                                                                $obsNombre = trim(($observacion['obs_usuario_nombre'] ?? '') . ' ' . ($observacion['obs_usuario_apellido'] ?? ''));
                                                                                echo htmlspecialchars($obsNombre ?: 'Sistema');
                                                                                ?>
                                                                                <?php if (!empty($observacion['obs_usuario_nickname'])): ?>
                                                                                    <br><span class="text-muted"><i class="feather icon-at-sign"></i> <?php echo htmlspecialchars($observacion['obs_usuario_nickname']); ?></span>
                                                                                <?php endif; ?>
                                                                            </small>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($foto) || !empty($imagen2)): ?>
                                            <div class="m-4">
                                                <h5 class="text-dark font-weight-bold">Registro Fotográfico</h5>

                                                <?php if (!empty($foto)): ?>
                                                    <div class="registroFotografico border rounded p-2 text-center mb-3">
                                                        <img src="<?php echo htmlspecialchars($foto); ?>" width="400"
                                                            height="auto" alt="Foto">
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($imagen2)): ?>
                                                    <div class="registroFotografico border rounded p-2 text-center">
                                                        <img src="<?php echo htmlspecialchars($imagen2); ?>" width="400"
                                                            height="auto" alt="Imagen 2">
                                                    </div>
                                                <?php endif; ?>

                                            </div>
                                        <?php endif; ?>


                                        <?php if (!empty($pdf)): ?>
                                            <div class="m-4">
                                                <h5 class="text-dark font-weight-bold">Documento PDF 1</h5>
                                                <?php if ($info_pdf): ?>
                                                    <small class="text-muted d-block mb-2">
                                                        <i class="feather icon-user"></i>
                                                        <?php echo htmlspecialchars($info_pdf['usuario_nombre'] . ' ' . $info_pdf['usuario_apellido']); ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="feather icon-at-sign"></i>
                                                        <?php echo htmlspecialchars($info_pdf['usuario_nickname'] ?? ''); ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="feather icon-clock"></i>
                                                        <?php echo $info_pdf['created_at']; ?>
                                                    </small>
                                                <?php endif; ?>
                                                <div class="registroFotografico border rounded p-2 text-center mb-3">
                                                    <embed src="<?php echo htmlspecialchars($pdf); ?>"
                                                        type="application/pdf" width="100%" height="600px">
                                                </div>

                                                <div class="text-center">
                                                    <a href="<?php echo htmlspecialchars($pdf); ?>" target="_blank"
                                                        class="btn btn-outline-primary">
                                                        Abrir en nueva pestaña
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($pdf2)): ?>
                                            <div class="m-4">
                                                <h5 class="text-dark font-weight-bold">Documento PDF 2</h5>
                                                <?php if ($info_pdf2): ?>
                                                    <small class="text-muted d-block mb-2">
                                                        <i class="feather icon-user"></i>
                                                        <?php echo htmlspecialchars($info_pdf2['usuario_nombre'] . ' ' . $info_pdf2['usuario_apellido']); ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="feather icon-at-sign"></i>
                                                        <?php echo htmlspecialchars($info_pdf2['usuario_nickname'] ?? ''); ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="feather icon-clock"></i>
                                                        <?php echo $info_pdf2['created_at']; ?>
                                                    </small>
                                                <?php endif; ?>
                                                <div class="registroFotografico border rounded p-2 text-center mb-3">
                                                    <embed src="<?php echo htmlspecialchars($pdf2); ?>"
                                                        type="application/pdf" width="100%" height="600px">
                                                </div>

                                                <div class="text-center">
                                                    <a href="<?php echo htmlspecialchars($pdf2); ?>" target="_blank"
                                                        class="btn btn-outline-primary">
                                                        Abrir en nueva pestaña
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($pdf3)): ?>
                                            <div class="m-4">
                                                <h5 class="text-dark font-weight-bold">Documento PDF 3</h5>
                                                <?php if ($info_pdf3): ?>
                                                    <small class="text-muted d-block mb-2">
                                                        <i class="feather icon-user"></i>
                                                        <?php echo htmlspecialchars($info_pdf3['usuario_nombre'] . ' ' . $info_pdf3['usuario_apellido']); ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="feather icon-at-sign"></i>
                                                        <?php echo htmlspecialchars($info_pdf3['usuario_nickname'] ?? ''); ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="feather icon-clock"></i>
                                                        <?php echo $info_pdf3['created_at']; ?>
                                                    </small>
                                                <?php endif; ?>
                                                <div class="registroFotografico border rounded p-2 text-center mb-3">
                                                    <embed src="<?php echo htmlspecialchars($pdf3); ?>"
                                                        type="application/pdf" width="100%" height="600px">
                                                </div>

                                                <div class="text-center">
                                                    <a href="<?php echo htmlspecialchars($pdf3); ?>" target="_blank"
                                                        class="btn btn-outline-primary">
                                                        Abrir en nueva pestaña
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($pdf4)): ?>
                                            <div class="m-4">
                                                <h5 class="text-dark font-weight-bold">Documento PDF 4</h5>
                                                <?php if ($info_pdf4): ?>
                                                    <small class="text-muted d-block mb-2">
                                                        <i class="feather icon-user"></i>
                                                        <?php echo htmlspecialchars($info_pdf4['usuario_nombre'] . ' ' . $info_pdf4['usuario_apellido']); ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="feather icon-at-sign"></i>
                                                        <?php echo htmlspecialchars($info_pdf4['usuario_nickname'] ?? ''); ?>
                                                        &nbsp;|&nbsp;
                                                        <i class="feather icon-clock"></i>
                                                        <?php echo $info_pdf4['created_at']; ?>
                                                    </small>
                                                <?php endif; ?>
                                                <div class="registroFotografico border rounded p-2 text-center mb-3">
                                                    <embed src="<?php echo htmlspecialchars($pdf4); ?>"
                                                        type="application/pdf" width="100%" height="600px">
                                                </div>

                                                <div class="text-center">
                                                    <a href="<?php echo htmlspecialchars($pdf4); ?>" target="_blank"
                                                        class="btn btn-outline-primary">
                                                        Abrir en nueva pestaña
                                                    </a>
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
        <script>
            document.querySelector('.btn-back-saas').onclick = function(e) {
                e.preventDefault();
                window.close();
            };
        </script>

</body>

</html>