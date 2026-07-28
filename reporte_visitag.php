<?php

include './admin/include/head.php';

require './admin/include/generic_classes.php';
include './admin/classes/Visitasg.php';
include './admin/classes/Linea.php';
include './admin/classes/Estrategia.php';

if (!empty($_POST['reporte']) && isset($_POST['reporte']) && $_POST['reporte'] > 0) {
    $rqst = array('id' => intval($_POST['reporte']));
    $arr = Visitasg::getAll($rqst);

    $isvalid = $arr['output']['valid'];
    $data = $arr['output']['response'];
    if (!empty($data)) {
        $data = $data[0];
        $id = $data['id'] ?? '';
        $dtcreate = $data['created_at'] ?? '';
        $date = $data['date'] ?? '';
        $item = $data['item'] ?? '';
        $provincia = $data['provincia'] ?? '';
        $poblacion = $data['poblacion'] ?? '';
        $departamento = $data['departamento'] ?? '';
        $municipio = $data['municipio'] ?? '';
        $desc_actividad = $data['desc_actividad'] ?? '';
        $estrategia = $data['estrategia'] ?? '';
        $linea = $data['linea'] ?? '';
        $linea = $data['linea_nombre'] ?? '';
        $estrategia = $data['estrategia_nombre'] ?? '';
        $campana = $data['campana'] ?? '';
        $link = $data['link'] ?? '';
        $foto1 = $data['foto1'] ?? null;
        $foto2 = $data['foto2'] ?? null;
        $foto3 = $data['foto3'] ?? null;
        $foto4 = $data['foto4'] ?? null;
    } else {
        echo "<script>
            alert('Sin resultados');
            window.location = 'cuadro_control_visitasg.php';
        </script>";
        return;
    }
} else {
    echo "<script>
        alert('Debes enviar un reporte válido para generar el documento');
        window.location = 'cuadro_control_visitasg.php';
    </script>";
    return;
}
?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<!-- Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<body class="">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ navigation menu ] start -->
    <?php include './admin/include/navbar.php'; ?>
    <!-- [ navigation menu ] end -->

    <!-- [ Header ] start -->
    <?php include './admin/include/header.php'; ?>
    <!-- [ Header ] end -->

    <style>
        :root{
            --bg0:#0b1220;
            --bg1:#0e1830;
            --heroA: rgba(32,62,92,.92);
            --heroB: rgba(47,63,110,.82);
            --text: rgba(255,255,255,.92);
            --muted: rgba(255,255,255,.72);
            --card: rgba(255,255,255,.06);
            --border: rgba(255,255,255,.10);
            --shadow: 0 18px 55px rgba(0,0,0,.38);
            --shadow2: 0 12px 30px rgba(0,0,0,.22);
            --r: 18px;
        }

        .pcoded-main-container{
            background:
                radial-gradient(900px 600px at 20% 10%, rgba(120, 88, 255, .18), transparent 55%),
                radial-gradient(900px 600px at 85% 18%, rgba(0, 187, 255, .14), transparent 55%),
                linear-gradient(180deg, var(--bg0), var(--bg1));
            min-height: 100vh;
        }

        /* HERO */
        .au-hero{
            position: relative;
            border-radius: 22px;
            overflow: hidden;
            margin: 12px 0 18px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.05);
        }
        .au-hero__bg{
            position:absolute; inset:0;
            background:
                radial-gradient(900px 520px at 18% 18%, rgba(0, 187, 255, .16), transparent 62%),
                radial-gradient(900px 520px at 82% 18%, rgba(120, 88, 255, .18), transparent 62%),
                linear-gradient(135deg, var(--heroA), var(--heroB));
            filter: saturate(1.1) contrast(1.05);
        }
        .au-hero__content{
            position: relative;
            padding: 18px 18px 16px;
            color: var(--text);
        }
        .au-kicker{
            display:inline-flex; align-items:center; gap:8px;
            font-weight: 900; font-size: 12px;
            letter-spacing: .3px; text-transform: uppercase;
            color: rgba(255,255,255,.72);
            margin-bottom: 6px;
        }
        .au-dot{
            width: 8px; height: 8px; border-radius: 999px;
            background: linear-gradient(135deg, #22c1ff, #7b61ff);
            box-shadow: 0 0 0 4px rgba(255,255,255,.08);
        }
        .au-title{
            margin:0;
            font-weight: 1000;
            letter-spacing: .2px;
            color: rgba(226,232,240,.96);
            text-shadow: 0 10px 26px rgba(0,0,0,.35);
        }
        .au-subtitle{
            color: rgba(255,255,255,.72);
            font-size: 13px;
            margin-top: 2px;
        }

        /* CONTENEDOR HOJA */
        .paper-wrap{
            display:flex;
            justify-content:center;
            padding: 8px 0 28px;
        }

        /* HOJA ACTA */
        .acta-pro{
            width: 100%;
            max-width: 980px;
            background: #ffffff;
            color: #111827;
            border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0,0,0,.35);
            overflow: hidden;
            border: 1px solid rgba(15,23,42,.10);
        }

        /* BARRA TOP HOJA */
        .acta-pro__top{
            background: linear-gradient(135deg, #203e5c, #2f3f6e);
            color: #fff;
            padding: 18px 22px;
            position: relative;
        }
        .acta-pro__top::after{
            content:"";
            position:absolute; inset:0;
            background: radial-gradient(700px 220px at 15% 25%, rgba(0,187,255,.18), transparent 60%);
            pointer-events:none;
        }
        .acta-pro__top-inner{
            position:relative;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .acta-badge{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding: 9px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.20);
            background: rgba(255,255,255,.10);
            font-weight: 900;
            letter-spacing: .3px;
            font-size: 12px;
        }

        .acta-title{
            margin: 10px 0 0;
            font-weight: 1000;
            letter-spacing: .3px;
            font-size: 18px;
        }
        .acta-sub{
            margin: 4px 0 0;
            color: rgba(255,255,255,.82);
            font-size: 12.5px;
        }

        /* CUERPO HOJA */
        .acta-pro__body{
            padding: 18px 22px 22px;
        }

        /* BLOQUES INFO */
        .grid-info{
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 14px;
        }
        @media(max-width: 768px){
            .grid-info{ grid-template-columns: 1fr; }
        }

        .info-card{
            border: 1px solid rgba(15,23,42,.10);
            background: #f8fafc;
            border-radius: 16px;
            padding: 12px 14px;
        }
        .info-card .k{
            font-weight: 900;
            font-size: 12px;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: .25px;
            margin-bottom: 6px;
        }
        .info-card .v{
            font-weight: 800;
            font-size: 13px;
            color:#0f172a;
        }

        /* TABLA PRINCIPAL */
        .tabla-visita{
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 14px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(15,23,42,.10);
        }
        .tabla-visita thead th{
            background: #2f4e6f;
            color: #fff;
            padding: 12px 10px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .35px;
            text-align:center;
            border: none;
        }
        .tabla-visita tbody td{
            padding: 13px 10px;
            font-weight: 800;
            color:#0f172a;
            background:#fff;
            text-align:center;
            border-top: 1px solid rgba(15,23,42,.08);
        }
        .tabla-visita tbody tr:nth-child(even) td{
            background:#f9fafb;
        }

        /* SECCIÓN DETALLE */
        .section-h{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 10px;
            margin-top: 16px;
            margin-bottom: 10px;
        }
        .section-h h3{
            margin:0;
            font-weight: 1000;
            font-size: 14px;
            color:#0f172a;
            letter-spacing:.2px;
        }
        .pill{
            display:inline-flex;
            align-items:center;
            padding: 6px 10px;
            border-radius: 999px;
            background:#eef2ff;
            color:#1e3a8a;
            font-weight: 900;
            font-size: 12px;
            border: 1px solid rgba(30,58,138,.15);
        }

        .block{
            border: 1px solid rgba(15,23,42,.10);
            border-radius: 16px;
            padding: 14px 14px;
            background: #ffffff;
        }
        .row-kv{
            display:grid;
            grid-template-columns: 220px 1fr;
            gap: 10px;
            padding: 8px 0;
            border-top: 1px dashed rgba(15,23,42,.12);
        }
        .row-kv:first-child{ border-top: none; padding-top: 0; }
        @media(max-width: 768px){
            .row-kv{ grid-template-columns: 1fr; }
        }
        .row-kv .k{
            font-weight: 1000;
            color:#334155;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .25px;
        }
        .row-kv .v{
            font-weight: 800;
            color:#0f172a;
            font-size: 13px;
            word-break: break-word;
        }
        .row-kv a{ font-weight: 1000; }

        /* GALERÍA */
        .galeria{
            margin-top: 12px;
            display:grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        @media(max-width: 768px){
            .galeria{ grid-template-columns: 1fr; }
        }
        .galeria .photo{
            border-radius: 16px;
            overflow:hidden;
            border: 1px solid rgba(15,23,42,.12);
            box-shadow: 0 14px 30px rgba(0,0,0,.12);
            background: #fff;
        }
        .galeria img{
            width: 100%;
            height: 360px;
            object-fit: cover;
            display:block;
        }
        .photo-cap{
            padding: 10px 12px;
            font-weight: 900;
            font-size: 12px;
            color:#0f172a;
            background: #f8fafc;
            border-top: 1px solid rgba(15,23,42,.08);
            text-transform: uppercase;
            letter-spacing: .25px;
        }

        /* Footer hoja */
        .paper-foot{
            display:flex;
            justify-content:space-between;
            gap: 10px;
            margin-top: 14px;
            color:#64748b;
            font-size: 12px;
            font-weight: 800;
            border-top: 1px solid rgba(15,23,42,.10);
            padding-top: 12px;
            flex-wrap: wrap;
        }
    </style>

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">

                            <!-- HERO HEADER -->
                            <div class="au-hero">
                                <div class="au-hero__bg"></div>
                                <div class="au-hero__content">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div>
                                            <div class="au-kicker"><span class="au-dot"></span><span>PRIMERA DAMA • REPORTE</span></div>
                                            <h2 class="au-title mb-1">Reporte de Visitas Primera Dama</h2>
                                            <div class="au-subtitle">Documento oficial con datos de la actividad y registro fotográfico.</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php include './admin/include/btn_back.php'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="paper-wrap">
                                <div class="acta-pro">

                                    <!-- TOP OFICIAL -->
                                    <div class="acta-pro__top">
                                        <div class="acta-pro__top-inner">
                                            <div class="acta-badge">
                                                <i class="feather icon-file-text"></i>
                                                ACTA OFICIAL • VISITA
                                            </div>
                                            <div class="acta-badge">
                                                <i class="feather icon-hash"></i>
                                                N° <?php echo htmlspecialchars($id); ?>
                                            </div>
                                        </div>
                                        <div style="margin-top: 10px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                                            <div>
                                                <div class="acta-title">Gobernación de Santander • Primera Dama</div>
                                                <div class="acta-sub">República de Colombia • Departamento de Santander</div>
                                            </div>
                                            <div style="display:flex; gap:10px; align-items:center;">
                                                <span class="pill" style="background: rgba(255,255,255,.12); color:#fff; border-color: rgba(255,255,255,.25);">
                                                    <i class="feather icon-calendar" style="margin-right:6px;"></i>
                                                    <?php echo htmlspecialchars($date); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="acta-pro__body">

                                        <!-- ENCABEZADO CON LOGO -->
                                        <div style="text-align:center; margin-bottom: 10px;">
                                            <?php include 'admin/include/generinc_brand_logo.php'; ?>
                                        </div>

                                        <!-- BLOQUES INFO -->
                                        <div class="grid-info">
                                            <div class="info-card">
                                                <div class="k">Control del documento</div>
                                                <div class="v">Código: 005 • Versión: 7 • Pág. 1 de 1</div>
                                            </div>
                                            <div class="info-card">
                                                <div class="k">Fechas</div>
                                                <div class="v">Visita: <?php echo htmlspecialchars($date); ?> • Creación: <?php echo htmlspecialchars($dtcreate); ?></div>
                                            </div>
                                        </div>

                                        <!-- TABLA RESUMEN -->
                                        <table class="tabla-visita">
                                            <thead>
                                                <tr>
                                                    <th>Fecha visita</th>
                                                    <th>Provincia</th>
                                                    <th>Municipio</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($date); ?></td>
                                                    <td><?php echo htmlspecialchars($provincia); ?></td>
                                                    <td><?php echo htmlspecialchars($municipio); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <!-- DETALLE -->
                                        <div class="section-h">
                                            <h3>Detalle de la actividad</h3>
                                            <span class="pill"><i class="feather icon-users" style="margin-right:6px;"></i> Población: <?php echo htmlspecialchars($poblacion); ?></span>
                                        </div>

                                        <div class="block">
                                            <div class="row-kv">
                                                <div class="k">Línea</div>
                                                <div class="v"><?php echo htmlspecialchars($linea); ?></div>
                                            </div>
                                            <div class="row-kv">
                                                <div class="k">Campaña</div>
                                                <div class="v"><?php echo htmlspecialchars($campana); ?></div>
                                            </div>
                                            <div class="row-kv">
                                                <div class="k">Estrategia</div>
                                                <div class="v"><?php echo htmlspecialchars($estrategia); ?></div>
                                            </div>
                                            <div class="row-kv">
                                                <div class="k">Link relacionado</div>
                                                <div class="v">
                                                    <?php if(!empty($link)): ?>
                                                        <a href="<?php echo htmlspecialchars($link); ?>" target="_blank"><?php echo htmlspecialchars($link); ?></a>
                                                    <?php else: ?>
                                                        <span style="color:#64748b;">No registra</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="row-kv">
                                                <div class="k">Descripción</div>
                                                <div class="v"><?php echo nl2br(htmlspecialchars($desc_actividad)); ?></div>
                                            </div>
                                        </div>

                                        <!-- GALERÍA -->
                                        <div class="section-h">
                                            <h3>Registro fotográfico</h3>
                                            <span class="pill"><i class="feather icon-image" style="margin-right:6px;"></i> Evidencias</span>
                                        </div>

                                        <div class="galeria">
                                            <?php if (!empty($foto1)): ?>
                                                <div class="photo">
                                                    <img src="<?php echo htmlspecialchars($foto1); ?>" alt="Foto 1">
                                                    <div class="photo-cap">Foto 1</div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($foto2)): ?>
                                                <div class="photo">
                                                    <img src="<?php echo htmlspecialchars($foto2); ?>" alt="Foto 2">
                                                    <div class="photo-cap">Foto 2</div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($foto3)): ?>
                                                <div class="photo">
                                                    <img src="<?php echo htmlspecialchars($foto3); ?>" alt="Foto 3">
                                                    <div class="photo-cap">Foto 3</div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($foto4)): ?>
                                                <div class="photo">
                                                    <img src="<?php echo htmlspecialchars($foto4); ?>" alt="Foto 4">
                                                    <div class="photo-cap">Foto 4</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="paper-foot">
                                            <div>Documento generado desde Acción Unificada • Primera Dama</div>
                                            <div>Acta N° <?php echo htmlspecialchars($id); ?> • <?php echo htmlspecialchars($municipio); ?></div>
                                        </div>

                                    </div><!-- body -->
                                </div><!-- acta -->
                            </div><!-- wrap -->

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

    <!-- prism Js -->
    <script src="assets/js/plugins/prism.js"></script>
    <script src="assets/js/plugins/apexcharts.min.js"></script>

    <script src="admin/js/gestora_social.js"></script>

</body>
</html>