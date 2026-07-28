<?php

include './admin/include/head.php';
require './admin/include/generic_classes.php';

include './admin/classes/VisitasAlcalde.php';

if (!empty($_POST['reporte']) && isset($_POST['reporte']) && $_POST['reporte'] > 0) {
    $id = (int)$_POST['id'];
    $result = VisitasAlcalde::getVisitaId($id);

    if ($result['state'] && count($result['data']) > 0) {
        $data = $result['data'][0];

        $dtcreate     = $data['created_at'] ?? '';
        $municipio    = $data['municipio'] ?? '';
        $compromisos  = $data['compromisos'] ?? '';
        $vereda       = $data['vereda'] ?? '';
        $tipo_visita  = $data['tipo_visita'] ?? '';
        $id           = $data['id'] ?? $id;
        $compromisopac= $data['compromisopac'] ?? '';
        $foto         = $data['foto'] ?? '';
        $imagen2      = $data['imagen2'] ?? ''; // ✅ por si existe en tu data

    } else {
?>
        <script type='text/javascript'>
            alert('Sin resultados');
            window.location = 'cuadro-control-visitas_alcalde.php';
        </script>
<?php
        return;
    }
} else { ?>
    <script type='text/javascript'>
        alert('Debes enviar una reporte para generar el documento');
        window.location = 'cuadro-control-visitas_alcalde.php';
    </script>
<?php
    return;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --au-primary:#20427F;
      --au-primary-dark:#132b52;
      --au-accent:#2e58a8;

      --au-ink:#0f172a;
      --au-muted:#64748b;

      --au-radius-xl:22px;
      --au-radius-lg:16px;

      --au-shadow-soft: 0 10px 30px rgba(0,0,0,.25);
      --au-shadow-mid: 0 18px 45px rgba(0,0,0,.35);

      --border-w: 1px solid rgba(255,255,255,.12);
      --ring: 0 0 0 .25rem rgba(46,88,168,.35);

      --w95: rgba(255,255,255,.95);
      --w90: rgba(255,255,255,.90);
      --w80: rgba(255,255,255,.80);
      --w70: rgba(255,255,255,.70);
      --w60: rgba(255,255,255,.60);

      /* ✅ evita que header fijo tape títulos */
      --safe-top: 96px;
    }

    /* ===== Fondo Dark Gradient ===== */
    body.dashboard-body{
      background:
        radial-gradient(900px 360px at 50% 115%, rgba(12, 35, 39, .95) 0%, rgba(12, 35, 39, 0) 55%),
        linear-gradient(135deg,
          #0b1221 0%,
          #0a1b24 35%,
          #0c2327 50%,
          #0b1321 75%,
          #0a1121 100%
        ) !important;
      color: var(--w90);
    }

    .pcoded-content{
      padding: calc(var(--safe-top) + 16px) 16px 16px !important;
    }
    @media (min-width:768px){
      :root{ --safe-top: 112px; }
      .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; }
    }
    @media (min-width:1200px){
      :root{ --safe-top: 120px; }
      .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; }
    }

    /* ===== Header breadcrumb PRO (dark glass) ===== */
    .page-header .page-block{
      background: rgba(255,255,255,.06) !important;
      border: var(--border-w) !important;
      border-radius: var(--au-radius-xl) !important;
      box-shadow: var(--au-shadow-soft) !important;
      padding: 16px 16px;
      backdrop-filter: blur(10px);
    }
    .page-header h5{
      font-weight: 1000 !important;
      color: var(--w95) !important;
      margin: 0;
    }
    .breadcrumb{
      background: transparent !important;
      padding: 0;
      margin: .35rem 0 0 !important;
    }
    .breadcrumb a,
    .breadcrumb-item,
    .breadcrumb-item a{
      color: var(--w80) !important;
    }
    .breadcrumb-item.active{ color: var(--w60) !important; }

    /* ===== Report Card SaaS (shell dark glass) ===== */
    .report-card{
      background: rgba(255,255,255,.06) !important;
      border: var(--border-w) !important;
      border-radius: var(--au-radius-xl) !important;
      box-shadow: var(--au-shadow-mid) !important;
      overflow:hidden;
      backdrop-filter: blur(10px);
    }

    .report-card .card-header{
      background: rgba(255,255,255,.06) !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
      padding: 16px 18px !important;
      position: relative;
      gap: 14px;
      color: var(--w90);
    }
    .report-card .card-header::before{
      content:"";
      position:absolute;
      top:0;left:0;
      width:100%;
      height:4px;
      background: linear-gradient(90deg, var(--au-primary), rgba(46,88,168,.35));
    }

    .brand-wrap{
      display:flex;
      align-items:center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .acta-title{
      margin:0;
      font-weight: 1000;
      letter-spacing: .5px;
      text-transform: uppercase;
      color: var(--w95) !important;
      font-size: 1.1rem;
    }

    .badge-soft{
      display:inline-flex;
      align-items:center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      border: 1px solid rgba(255,255,255,.14);
      background: rgba(255,255,255,.08);
      color: var(--w90);
      font-weight: 900;
      font-size: .85rem;
      white-space: nowrap;
    }
    .badge-soft i{ color: var(--w95) !important; }

    .report-card .card-body{ padding: 18px !important; }

    /* ===== “Documento” interno (claro para legibilidad/imprimir) ===== */
    .doc-surface{
      background: rgba(255,255,255,.95);
      border-radius: var(--au-radius-xl);
      border: 1px solid rgba(2,6,23,.08);
      box-shadow: 0 18px 45px rgba(0,0,0,.18);
      padding: 16px;
    }

    /* Encabezado Colombia */
    .gov-head{
      border: 1px dashed rgba(2,6,23,.14);
      border-radius: var(--au-radius-lg);
      padding: 14px 14px;
      background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(246,248,252,.92));
      box-shadow: 0 10px 22px rgba(2,6,23,.06);
    }
    .gov-head h5{
      font-weight: 1000;
      margin: 0;
      color: var(--au-ink);
    }
    .gov-meta{
      font-size: .92rem;
      color: var(--au-muted);
      line-height: 1.55;
    }
    .gov-meta strong{ color: var(--au-ink); }

    /* Tabla */
    .table-wrap{
      border: 1px solid rgba(2,6,23,.10);
      border-radius: var(--au-radius-lg);
      overflow:hidden;
      box-shadow: 0 10px 24px rgba(2,6,23,.06);
      background:#fff;
    }
    .table{ margin:0; }
    .table thead th{
      background: linear-gradient(135deg, rgba(32,66,127,.12), rgba(255,255,255,.95));
      color: var(--au-ink);
      font-weight: 1000;
      border-bottom: 1px solid rgba(2,6,23,.10) !important;
      white-space: nowrap;
    }
    .table td{
      color: var(--au-ink);
      vertical-align: top;
      border-color: rgba(2,6,23,.08) !important;
    }

    /* Secciones */
    .section-title{
      font-weight: 1000;
      color: var(--au-ink);
      margin: 0 0 10px 0;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .section-title .dot{
      width:10px; height:10px; border-radius: 999px;
      background: linear-gradient(135deg, var(--au-primary), var(--au-accent));
      box-shadow: 0 8px 18px rgba(32,66,127,.25);
      flex: 0 0 auto;
    }
    .section-card{
      border: 1px solid rgba(2,6,23,.10);
      border-radius: var(--au-radius-lg);
      padding: 14px 14px;
      background: #fff;
      box-shadow: 0 10px 24px rgba(2,6,23,.06);
    }
    .section-card p{
      margin:0;
      color: var(--au-ink);
      line-height: 1.65;
      white-space: pre-wrap;
      word-break: break-word;
    }

    /* Registro Fotográfico */
    .photo-grid{
      display:grid;
      grid-template-columns: 1fr;
      gap: 12px;
    }
    @media (min-width: 768px){
      .photo-grid{ grid-template-columns: 1fr 1fr; }
    }
    .registroFotografico{
      border-radius: var(--au-radius-lg);
      border: 1px solid rgba(2,6,23,.10);
      background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(246,248,252,.92));
      padding: 10px;
      box-shadow: 0 10px 24px rgba(2,6,23,.06);
      text-align:center;
    }
    .registroFotografico img{
      max-width:100%;
      height:auto;
      border-radius: 14px;
      box-shadow: 0 16px 40px rgba(2,6,23,.12);
    }

    /* Footer suave */
    .report-card .card-footer{
      background: rgba(255,255,255,.06) !important;
      border-top: 1px solid rgba(255,255,255,.12) !important;
      color: var(--w70) !important;
      text-align:center;
      padding: 14px 16px;
      backdrop-filter: blur(10px);
    }
    .mini-note{
      font-size: .9rem;
      margin: 0;
    }
    .mini-note strong{ color: var(--w95); }

    html, body{ overflow-x:hidden !important; }
    /* ===== Ajuste fino de tipografía en TD ===== */
#dynamictable tbody td{
  font-size: 0.82rem !important;   /* más compacto y elegante */
  line-height: 1.35 !important;    /* mejor lectura */
  padding: 8px 10px !important;    /* reduce altura de filas */
}

/* Texto secundario dentro de TD */
#dynamictable tbody td small,
#dynamictable tbody td .text-muted{
  font-size: 0.75rem !important;
}

/* Íconos dentro de la tabla un poco más contenidos */
#dynamictable tbody td i,
#dynamictable tbody td svg{
  font-size: 0.9rem !important;
}

/* En pantallas grandes aún más pro */
@media (min-width:1200px){
  #dynamictable tbody td{
    font-size: 0.8rem !important;
  }
}

  </style>
</head>

<body class="dashboard-body">
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

              <!-- [ breadcrumb ] start -->
              <div class="page-header mb-3">
                <div class="page-block">
                  <div class="row align-items-center">
                    <div class="col-md-12">
                      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                          <h5 class="m-b-10 mb-0">Reporte de visitas Alcalde</h5>
                          <div style="font-size:.9rem; margin-top:6px; color: var(--w70);">
                            Acta y registro fotográfico de la visita
                          </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <?php include './admin/include/btn_back.php'; ?>
                        </div>
                      </div>
                      <ul class="breadcrumb mt-2">
                        <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Alcalde</a></li>
                        <li class="breadcrumb-item"><a href="#!">Reporte visitas</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              <!-- [ breadcrumb ] end -->

              <!-- [ Report Card ] start -->
              <div class="row">
                <div class="col-12">
                  <div class="card report-card mb-4">

                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                      <div class="brand-wrap">
                        <?php include 'admin/include/generinc_brand_logo.php'; ?>
                        <div class="d-flex flex-column">
                          <h4 class="acta-title">ACTA DE VISITA N° <?php echo htmlspecialchars((string)$id); ?></h4>
                          <?php if (!empty($tipo_visita)): ?>
                            <span class="badge-soft mt-2">
                              <i class="feather icon-activity"></i>
                              <?php echo htmlspecialchars((string)$tipo_visita); ?>
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>

                      <div class="d-flex flex-wrap justify-content-end gap-2">
                        <span class="badge-soft">
                          <i class="feather icon-calendar"></i>
                          <?php echo htmlspecialchars((string)$dtcreate); ?>
                        </span>
                        <?php if (!empty($municipio)): ?>
                          <span class="badge-soft">
                            <i class="feather icon-map-pin"></i>
                            <?php echo htmlspecialchars((string)$municipio); ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="card-body">

                      <!-- ✅ Documento interno claro (legible) -->
                      <div class="doc-surface">

                        <div class="row g-3 mb-4">
                          <div class="col-12 col-lg-7">
                            <div class="gov-head text-center text-lg-start h-100">
                              <h5 class="mb-1">REPÚBLICA DE COLOMBIA</h5>
                              <h5 class="mb-1">DEPARTAMENTO DE SANTANDER</h5>
                              <div class="gov-meta">GOBERNACIÓN DE SANTANDER</div>
                            </div>
                          </div>

                          <div class="col-12 col-lg-5">
                            <div class="gov-head h-100">
                              <div class="gov-meta">
                                <div><strong>Página:</strong> 1 de 1</div>
                                <div><strong>Código:</strong> 005</div>
                                <div><strong>Versión:</strong> 7</div>
                                <div><strong>Fecha de creación:</strong> <?php echo htmlspecialchars((string)$dtcreate); ?></div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="table-wrap mb-4">
                          <div class="table-responsive">
                            <table class="table table-bordered text-center mb-0">
                              <thead>
                                <tr>
                                  <th>Fecha Visita</th>
                                  <th>Vereda</th>
                                  <th>Municipio</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td><?php echo htmlspecialchars((string)$dtcreate); ?></td>
                                  <td><?php echo htmlspecialchars((string)$vereda); ?></td>
                                  <td><?php echo htmlspecialchars((string)$municipio); ?></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>

                        <div class="section-card mb-3">
                          <div class="section-title">
                            <span class="dot"></span>
                            <span>Detalle visita</span>
                          </div>
                          <p class="text-justify"><?php echo htmlspecialchars((string)$compromisos); ?></p>
                        </div>

                        <?php if (!empty($compromisopac)): ?>
                          <div class="section-card mb-3">
                            <div class="section-title">
                              <span class="dot"></span>
                              <span>Compromisos pactados</span>
                            </div>
                            <p class="text-justify"><?php echo htmlspecialchars((string)$compromisopac); ?></p>
                          </div>
                        <?php endif; ?>

                        <?php if (!empty($foto) || !empty($imagen2)): ?>
                          <div class="section-card">
                            <div class="section-title">
                              <span class="dot"></span>
                              <span>Registro Fotográfico</span>
                            </div>

                            <div class="photo-grid">
                              <?php if (!empty($foto)): ?>
                                <div class="registroFotografico">
                                  <img src="<?php echo htmlspecialchars((string)$foto); ?>" alt="Foto 1">
                                </div>
                              <?php endif; ?>

                              <?php if (!empty($imagen2)): ?>
                                <div class="registroFotografico">
                                  <img src="<?php echo htmlspecialchars((string)$imagen2); ?>" alt="Foto 2">
                                </div>
                              <?php endif; ?>
                            </div>
                          </div>
                        <?php endif; ?>

                      </div><!-- /doc-surface -->

                    </div>

                    <div class="card-footer">
                      <p class="mini-note mb-0">
                        Documento generado desde <strong>Acción Unificada</strong> • <?php echo htmlspecialchars((string)$dtcreate); ?>
                      </p>
                    </div>

                  </div>
                </div>
              </div>
              <!-- [ Report Card ] end -->

            </div>
          </div>

          <?php include 'admin/include/gerenic_script.php'; ?>

          <!-- Required Js -->
          <script src="assets/js/vendor-all.min.js"></script>
          <script src="assets/js/plugins/bootstrap.min.js"></script>
          <script src="assets/js/pcoded.min.js"></script>

        </div>
      </div>
    </div>
  </div>

</body>
</html>
