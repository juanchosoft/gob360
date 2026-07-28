<?php

include './admin/include/head.php';
require './admin/include/generic_classes.php';

include './admin/classes/VisitasgAspas.php';
include './admin/classes/Linea.php';
include './admin/classes/Estrategia.php';

if (!empty($_POST['reporte']) && isset($_POST['reporte']) && $_POST['reporte'] > 0) {
    $rqst = array('id' => intval($_POST['reporte']));
    $arr = VisitasgAspas::getAll($rqst);

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
        $estrategia = $data['estrategia_nombre'] ?? ($data['estrategia'] ?? '');
        $linea = $data['linea_nombre'] ?? ($data['linea'] ?? '');
        $campana = $data['campana'] ?? '';
        $link = $data['link'] ?? '';
        $foto1 = $data['foto1'] ?? null;
        $foto2 = $data['foto2'] ?? null;
        $foto3 = $data['foto3'] ?? null;
        $foto4 = $data['foto4'] ?? null;
    } else {
        echo "<script>
            alert('Sin resultados');
            window.location = 'cuadro_control_visitasaspas.php';
        </script>";
        return;
    }
} else {
    echo "<script>
        alert('Debes enviar un reporte válido para generar el documento');
        window.location = 'cuadro_control_visitasaspas.php';
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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">

                            <!-- HERO PRO (como tu línea azul) -->
                            <div class="au-hero">
                                <div class="au-hero__bg"></div>
                                <div class="au-hero__content">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div>
                                            <div class="au-kicker">
                                                <span class="au-dot"></span>
                                                <span>REPORTE / ACTA • ASPAS</span>
                                            </div>
                                            <h2 class="au-title mb-1">Reporte de Gestiones Aspas</h2>
                                            <div class="au-subtitle">Acta detallada con información, trazabilidad y registro fotográfico.</div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <?php include './admin/include/btn_back.php'; ?>
                                        </div>
                                    </div>

                                    <div class="au-breadcrumb mt-3">
                                        <a href="index.html"><i class="feather icon-home"></i></a>
                                        <span class="sep">/</span>
                                        <span>Primera dama - Aspas</span>
                                        <span class="sep">/</span>
                                        <span class="active">Actividades</span>
                                    </div>
                                </div>
                            </div>

                            <!-- CONTENIDO CENTRADO -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="au-sheet-wrap">
                                        <div class="au-sheet">

                                            <!-- Encabezado institucional -->
                                            <div class="au-sheet-head">
                                                <div class="au-brand">
                                                    <img src="assets/img/logosf.png" alt="Logo Gobernación" class="au-logo">
                                                    <div class="au-brand-text">
                                                        <div class="t1"><strong>REPÚBLICA DE COLOMBIA</strong></div>
                                                        <div class="t2"><strong>DEPARTAMENTO DE SANTANDER</strong></div>
                                                        <div class="t3">GOBERNACIÓN DE SANTANDER</div>
                                                    </div>
                                                </div>

                                                <div class="au-meta">
                                                    <div class="au-meta-card">
                                                        <div class="k">Acta</div>
                                                        <div class="v">N° <?php echo htmlspecialchars($id); ?></div>
                                                    </div>
                                                    <div class="au-meta-card">
                                                        <div class="k">Fecha visita</div>
                                                        <div class="v"><?php echo htmlspecialchars($date); ?></div>
                                                    </div>
                                                    <div class="au-meta-card">
                                                        <div class="k">Creado</div>
                                                        <div class="v"><?php echo htmlspecialchars($dtcreate); ?></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Título acta -->
                                            <div class="au-sheet-title">
                                                <h3 class="m-0">ACTA DE GESTIÓN ASPAS</h3>
                                                <div class="au-sheet-sub">Documento interno • Uso institucional</div>
                                            </div>

                                            <!-- Tabla resumen -->
                                            <div class="au-table-wrap">
                                                <table class="au-table">
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
                                            </div>

                                            <!-- Bloques de datos -->
                                            <div class="au-grid">
                                                <div class="au-block">
                                                    <div class="au-block-title"><i class="feather icon-layers"></i> Información general</div>
                                                    <div class="au-kv">
                                                        <div class="k">Línea</div>
                                                        <div class="v"><?php echo htmlspecialchars($linea); ?></div>

                                                        <div class="k">Estrategia</div>
                                                        <div class="v"><?php echo htmlspecialchars($estrategia); ?></div>

                                                        <div class="k">Campaña</div>
                                                        <div class="v"><?php echo htmlspecialchars($campana); ?></div>

                                                        <div class="k">Población impactada</div>
                                                        <div class="v"><span class="au-badge"><?php echo htmlspecialchars($poblacion); ?></span></div>
                                                    </div>
                                                </div>

                                                <div class="au-block">
                                                    <div class="au-block-title"><i class="feather icon-link-2"></i> Enlace relacionado</div>
                                                    <?php if (!empty($link)): ?>
                                                        <a class="au-link" href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener">
                                                            <?php echo htmlspecialchars($link); ?>
                                                            <i class="feather icon-external-link"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <div class="au-empty">Sin enlace asociado.</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Descripción actividad -->
                                            <div class="au-block au-block--full">
                                                <div class="au-block-title"><i class="feather icon-file-text"></i> Detalle de la actividad</div>
                                                <div class="au-text">
                                                    <?php echo nl2br(htmlspecialchars($desc_actividad)); ?>
                                                </div>
                                            </div>

                                            <!-- Registro Fotográfico -->
                                            <div class="au-section">
                                                <div class="au-section-title">
                                                    <div class="au-section-left">
                                                        <div class="au-section-icon"><i class="feather icon-camera"></i></div>
                                                        <div>
                                                            <div class="t">Registro Fotográfico</div>
                                                            <div class="s">Evidencia visual relacionada con la visita</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="au-gallery">
                                                    <?php if (!empty($foto1)): ?>
                                                        <a class="au-photo" href="<?php echo htmlspecialchars($foto1); ?>" target="_blank" rel="noopener">
                                                            <img src="<?php echo htmlspecialchars($foto1); ?>" alt="Foto 1">
                                                            <span class="cap">Foto 1</span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (!empty($foto2)): ?>
                                                        <a class="au-photo" href="<?php echo htmlspecialchars($foto2); ?>" target="_blank" rel="noopener">
                                                            <img src="<?php echo htmlspecialchars($foto2); ?>" alt="Foto 2">
                                                            <span class="cap">Foto 2</span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (!empty($foto3)): ?>
                                                        <a class="au-photo" href="<?php echo htmlspecialchars($foto3); ?>" target="_blank" rel="noopener">
                                                            <img src="<?php echo htmlspecialchars($foto3); ?>" alt="Foto 3">
                                                            <span class="cap">Foto 3</span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (!empty($foto4)): ?>
                                                        <a class="au-photo" href="<?php echo htmlspecialchars($foto4); ?>" target="_blank" rel="noopener">
                                                            <img src="<?php echo htmlspecialchars($foto4); ?>" alt="Foto 4">
                                                            <span class="cap">Foto 4</span>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (empty($foto1) && empty($foto2) && empty($foto3) && empty($foto4)): ?>
                                                        <div class="au-empty au-empty--center">
                                                            <i class="feather icon-image"></i>
                                                            <div>No hay fotos registradas para esta actividad.</div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Footer del acta -->
                                            <div class="au-sheet-footer">
                                                <div class="au-foot-left">
                                                    <strong>Gobernación de Santander</strong> • Sistema Acción Unificada
                                                </div>
                                                <div class="au-foot-right">
                                                    Código: 005 • Versión: 7 • Pág. 1 de 1
                                                </div>
                                            </div>

                                        </div><!-- /sheet -->
                                    </div><!-- /wrap -->
                                </div>
                            </div>

                        </div><!-- /page-wrapper -->
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

<style>
/* =========================================================
   ✅ ACTA PREMIUM SAAS (MISMA LÍNEA AZUL DE TU SISTEMA)
========================================================= */
:root{
  --au-bg0:#0b1220;
  --au-bg1:#0e1830;

  --au-hero-a: rgba(32,62,92,.92);
  --au-hero-b: rgba(47,63,110,.82);

  --au-card: rgba(255,255,255,.06);
  --au-border: rgba(255,255,255,.10);
  --au-text: rgba(255,255,255,.92);
  --au-muted: rgba(255,255,255,.70);

  --sheet-bg:#ffffff;
  --sheet-text:#111827;
  --sheet-muted:#6b7280;
  --sheet-border:#e5e7eb;

  --shadow: 0 18px 55px rgba(0,0,0,.38);
  --r: 18px;
}

/* Fondo general */
.pcoded-main-container{
  background:
    radial-gradient(900px 600px at 20% 10%, rgba(120,88,255,.18), transparent 55%),
    radial-gradient(900px 600px at 85% 18%, rgba(0,187,255,.14), transparent 55%),
    linear-gradient(180deg, var(--au-bg0), var(--au-bg1));
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
    radial-gradient(900px 520px at 18% 18%, rgba(0,187,255,.16), transparent 62%),
    radial-gradient(900px 520px at 82% 18%, rgba(120,88,255,.18), transparent 62%),
    linear-gradient(135deg, var(--au-hero-a), var(--au-hero-b));
  filter: saturate(1.1) contrast(1.05);
}
.au-hero__content{ position:relative; padding: 18px 18px 16px; color: var(--au-text); }

.au-kicker{
  display:inline-flex; align-items:center; gap:8px;
  font-weight:800; font-size:12px; letter-spacing:.3px;
  text-transform:uppercase; color: rgba(255,255,255,.72);
  margin-bottom: 6px;
}
.au-dot{
  width: 8px; height:8px; border-radius:999px;
  background: linear-gradient(135deg, #22c1ff, #7b61ff);
  box-shadow: 0 0 0 4px rgba(255,255,255,.08);
}
.au-title{
  margin:0;
  font-weight: 900;
  letter-spacing: .2px;
  color: rgba(226,232,240,.95);
  text-shadow: 0 10px 26px rgba(0,0,0,.35);
}
.au-subtitle{ color: rgba(255,255,255,.72); font-size: 13px; margin-top:2px; }

.au-breadcrumb{
  display:flex; align-items:center; flex-wrap:wrap;
  gap:10px; font-size: 12px; color: rgba(255,255,255,.70);
}
.au-breadcrumb a{ color: rgba(255,255,255,.85); text-decoration:none; }
.au-breadcrumb .sep{ opacity:.55; }
.au-breadcrumb .active{ color: rgba(255,255,255,.90); font-weight: 700; }

/* WRAP CENTRADO */
.au-sheet-wrap{
  display:flex;
  justify-content:center;
  padding: 10px 0 30px;
}

/* HOJA (ACTA) */
.au-sheet{
  width: 100%;
  max-width: 1100px;
  background: var(--sheet-bg);
  color: var(--sheet-text);
  border-radius: 18px;
  border: 1px solid var(--sheet-border);
  box-shadow: 0 22px 60px rgba(0,0,0,.30);
  overflow: hidden;
}

/* Cabeza hoja */
.au-sheet-head{
  display:flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 14px;
  padding: 18px 18px;
  background: linear-gradient(180deg, #f8fafc, #ffffff);
  border-bottom: 1px solid var(--sheet-border);
}
.au-brand{ display:flex; gap: 14px; align-items:center; }
.au-logo{ width: 120px; max-width: 120px; height: auto; object-fit: contain; }
.au-brand-text .t1, .au-brand-text .t2{ font-size: 13px; line-height: 1.2; }
.au-brand-text .t3{ font-size: 12px; color: var(--sheet-muted); margin-top: 2px; }

.au-meta{ display:flex; gap: 10px; flex-wrap: wrap; justify-content:flex-end; }
.au-meta-card{
  min-width: 140px;
  padding: 10px 12px;
  border-radius: 14px;
  border: 1px solid var(--sheet-border);
  background: #fff;
}
.au-meta-card .k{ font-size: 11px; color: var(--sheet-muted); text-transform: uppercase; letter-spacing:.25px; font-weight:800; }
.au-meta-card .v{ font-size: 13px; font-weight: 900; color: #0f172a; margin-top: 2px; }

/* Título */
.au-sheet-title{
  padding: 14px 18px;
  border-bottom: 1px solid var(--sheet-border);
  display:flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 10px;
}
.au-sheet-title h3{
  font-weight: 1000;
  letter-spacing: .3px;
  font-size: 16px;
}
.au-sheet-sub{
  font-size: 12px;
  color: var(--sheet-muted);
}

/* Tabla */
.au-table-wrap{ padding: 14px 18px 6px; }
.au-table{
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  overflow: hidden;
  border-radius: 14px;
  border: 1px solid var(--sheet-border);
}
.au-table thead th{
  background: #2f4e6f;
  color: #fff;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .3px;
  padding: 12px;
  text-align: center;
  border-right: 1px solid rgba(255,255,255,.10);
}
.au-table thead th:last-child{ border-right: none; }
.au-table tbody td{
  padding: 12px;
  text-align: center;
  border-top: 1px solid var(--sheet-border);
  color: #111827;
  font-weight: 700;
  background: #fff;
}

/* Grilla info */
.au-grid{
  display:grid;
  grid-template-columns: 1.3fr .7fr;
  gap: 14px;
  padding: 10px 18px 16px;
}
.au-block{
  border: 1px solid var(--sheet-border);
  border-radius: 16px;
  background: #fff;
  padding: 14px;
}
.au-block--full{ margin: 0 18px 16px; }

.au-block-title{
  display:flex; align-items:center; gap: 10px;
  font-weight: 1000;
  font-size: 13px;
  letter-spacing: .2px;
  color: #0f172a;
  margin-bottom: 10px;
}
.au-block-title i{ color:#2f4e6f; }

.au-kv{
  display:grid;
  grid-template-columns: 170px 1fr;
  gap: 8px 12px;
}
.au-kv .k{
  font-size: 12px;
  color: var(--sheet-muted);
  text-transform: uppercase;
  letter-spacing:.25px;
  font-weight: 900;
}
.au-kv .v{
  font-size: 13px;
  color: #111827;
  font-weight: 800;
}

.au-badge{
  display:inline-flex;
  align-items:center;
  padding: 6px 10px;
  border-radius: 999px;
  background: #e6f0ff;
  border: 1px solid #c7dbff;
  color: #1e3a8a;
  font-weight: 900;
}

/* Link */
.au-link{
  display:flex;
  align-items:center;
  justify-content: space-between;
  gap: 10px;
  width: 100%;
  padding: 10px 12px;
  border-radius: 14px;
  border: 1px solid var(--sheet-border);
  background: #f8fafc;
  color: #0f172a;
  text-decoration: none;
  font-weight: 800;
  word-break: break-all;
}
.au-link:hover{ background:#eef4ff; }

/* Texto largo */
.au-text{
  color:#111827;
  font-size: 13.5px;
  line-height: 1.65;
  text-align: justify;
}

/* Sección fotos */
.au-section{ padding: 0 18px 18px; }
.au-section-title{
  display:flex;
  justify-content: space-between;
  align-items:center;
  margin: 6px 0 12px;
}
.au-section-left{ display:flex; gap: 12px; align-items:center; }
.au-section-icon{
  width: 42px; height: 42px;
  border-radius: 14px;
  display:grid; place-items:center;
  background: #2f4e6f;
  color:#fff;
}
.au-section-left .t{ font-weight: 1000; color:#0f172a; }
.au-section-left .s{ font-size: 12px; color: var(--sheet-muted); }

/* Galería */
.au-gallery{
  display:grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}
.au-photo{
  position:relative;
  display:block;
  border-radius: 16px;
  overflow:hidden;
  border: 1px solid var(--sheet-border);
  background: #fff;
  box-shadow: 0 14px 30px rgba(0,0,0,.12);
  text-decoration:none;
}
.au-photo img{
  width:100%;
  height: 320px;
  object-fit: cover;
  display:block;
}
.au-photo .cap{
  position:absolute;
  left: 10px;
  bottom: 10px;
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(15,23,42,.80);
  color:#fff;
  font-size: 12px;
  font-weight: 900;
  backdrop-filter: blur(8px);
}
.au-photo:hover img{ transform: scale(1.02); transition: transform .25s ease; }

/* Empty */
.au-empty{
  padding: 12px;
  border-radius: 14px;
  border: 1px dashed var(--sheet-border);
  color: var(--sheet-muted);
  background: #fff;
}
.au-empty--center{
  grid-column: 1 / -1;
  text-align:center;
  padding: 22px;
}
.au-empty--center i{
  font-size: 22px;
  display:block;
  margin-bottom: 8px;
  color:#2f4e6f;
}

/* Footer acta */
.au-sheet-footer{
  display:flex;
  justify-content: space-between;
  align-items:center;
  gap: 10px;
  padding: 12px 18px;
  border-top: 1px solid var(--sheet-border);
  background: #f8fafc;
  color: var(--sheet-muted);
  font-size: 12px;
  font-weight: 700;
}

/* Responsive */
@media (max-width: 992px){
  .au-sheet-head{ flex-direction: column; align-items:flex-start; }
  .au-meta{ justify-content:flex-start; }
  .au-grid{ grid-template-columns: 1fr; }
  .au-gallery{ grid-template-columns: 1fr; }
  .au-photo img{ height: 280px; }
  .au-kv{ grid-template-columns: 1fr; }
}
</style>