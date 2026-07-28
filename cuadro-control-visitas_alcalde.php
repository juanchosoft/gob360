<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    .nav-tabs{
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
      gap: 10px;
      margin-bottom: 14px;
    }
    .nav-tabs .nav-link{
      border: 1px solid rgba(255,255,255,.14) !important;
      border-bottom: none !important;
      border-radius: 999px !important;
      padding: 10px 14px !important;
      font-weight: 950;
      color: rgba(255,255,255,.7) !important;
      background: rgba(255,255,255,.06) !important;
      transition: all .18s ease;
      white-space: nowrap;
    }
    .nav-tabs .nav-link.active{
      color: #fff !important;
      background: rgba(32,66,127,.25) !important;
      border-color: rgba(46,88,168,.35) !important;
      box-shadow: 0 10px 22px rgba(32,66,127,.18);
    }
    @media (max-width:576px){
      .nav-tabs{ overflow-x:auto; flex-wrap: nowrap; padding-bottom: 6px; }
      .nav-tabs .nav-link{ font-size: 13px; padding: 9px 12px !important; }
    }
    .au-search{
      max-width: 620px;
      margin: 0 auto 14px auto;
    }
    .au-search .input-group{
      border-radius: 999px; overflow: hidden;
      box-shadow: 0 10px 24px rgba(0,0,0,.30);
      border: 1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.06);
      backdrop-filter: blur(10px);
    }
    .au-search input{
      border: none !important; height: 44px; padding-left: 16px;
      background: transparent !important; color: #fff !important;
    }
    .au-search input::placeholder{ color: rgba(255,255,255,.55) !important; }
    .au-search input:focus{ box-shadow:none !important; }
    .au-search .input-group-text{
      border: none !important;
      background: rgba(32,66,127,.30) !important;
      color: #fff !important;
      width: 52px; justify-content:center;
    }
    .au-search .input-group-text .feather{ color: #fff !important; }
    #dynamictable{ width:100% !important; table-layout:auto; white-space:normal; border-collapse:separate; border-spacing:0; }
    #dynamictable thead th{
      position:sticky; top:0; z-index:2;
      background: rgba(255,255,255,.08) !important;
      color: #fff !important; font-weight:1000;
      border-bottom: 1px solid rgba(255,255,255,.14) !important;
      padding: 12px 12px !important; white-space:nowrap;
    }
    #dynamictable tbody tr{ background: transparent !important; }
    #dynamictable td{
      color: rgba(255,255,255,.86) !important;
      white-space:normal !important; word-break:break-word !important;
      max-width:320px; vertical-align:top;
      padding: 12px 12px !important;
      border-bottom: 1px solid rgba(255,255,255,.08) !important;
      background: transparent !important;
    }
    #dynamictable tbody tr:hover{ background: rgba(255,255,255,.06) !important; }
    #dynamictable .feather{ color: rgba(255,255,255,.8) !important; }
    #dynamictable .btn .feather{ color: #fff !important; }
    #dynamictable .btn-transparent{ color: rgba(255,255,255,.8) !important; background: transparent !important; border: none !important; }
    #dynamictable .btn-transparent:hover{ color: #fff !important; background: rgba(255,255,255,.1) !important; }
    #dynamictable .btn-success4{ color: #fff !important; background: rgba(52,211,153,.2) !important; border: 1px solid rgba(52,211,153,.3) !important; border-radius: 8px !important; }
    #dynamictable .btn-success4:hover{ background: rgba(52,211,153,.35) !important; }
    #dynamictable .btn-link.text-primary{ color: #60a5fa !important; padding: 0 !important; border: none !important; font-weight: 800 !important; }
    #dynamictable .btn-link.text-primary:hover{ color: #93c5fd !important; text-decoration: underline !important; }
    #dynamictable span.text-muted{ color: rgba(255,255,255,.5) !important; }
    div.dataTables_wrapper .dataTables_filter{ display:none !important; }
    div.dataTables_wrapper .dataTables_length select{
      border-radius:12px; border:1px solid rgba(255,255,255,.14);
      padding:6px 10px; background:rgba(10,17,33,.55); color:#fff;
    }
    .au-ind-card{
      border-radius:22px; box-shadow:0 10px 30px rgba(0,0,0,.25);
      border:1px solid rgba(255,255,255,.12); overflow:hidden;
      background:rgba(255,255,255,.06); backdrop-filter:blur(10px);
    }
    .au-ind-card .card-header{
      background:linear-gradient(135deg, #20427F, #132b52); color:#fff;
      border-bottom:none; padding:14px 16px;
    }
    .au-ind-card .card-header h5{ font-weight:1000; margin:0; }
    .modal{ z-index: 20060 !important; }
    .modal-backdrop{ z-index: 20040 !important; }
    html, body{ overflow-x:hidden !important; }
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

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- [ breadcrumb ] start -->
      <div class="page-header mb-3">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                  <h5 class="m-b-10 mb-0">Cuadro visitas municipios - Alcalde</h5>
                  <div style="font-size:.9rem; margin-top:6px;">
                    Registro de visitas Alcalde / Cuadro Control municipios
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
              <ul class="breadcrumb mt-2">
                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Registro de visitas Alcalde / Cuadro Control municipios</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <div class="row">
        <div class="col-12">
          <div class="card au-card">

            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
              <h5 class="title">Listado de Visitas Alcalde</h5>

              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> maximize</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                    <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                    <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                    <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> remove</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body">

              <ul class="nav nav-tabs justify-content-center" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
                          role="tab" aria-controls="home" aria-selected="true" onclick="cargaData()">
                    Visitas
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
                          role="tab" aria-controls="profile" aria-selected="false" onclick="indicadores()">
                    Indicadores por tipo visita
                  </button>
                </li>
              </ul>

              <div class="tab-content" id="myTabContent">
                <!-- TAB VISITAS -->
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                  <div class="au-search">
                    <div class="input-group input-primary">
                      <input type="text" id="customSearch" class="form-control" placeholder="Buscar en visitas (municipio, vereda, tipo, fecha...)">
                      <div class="input-group-append">
                        <span class="input-group-text"><i class="feather icon-search"></i></span>
                      </div>
                    </div>
                  </div>

                  <div class="table-responsive tabla-informacion tabla-scroll">
                    <table class="table table-hover mb-0" id="dynamictable">
                      <thead>
                        <tr class="border-1">
                          <th>Detalles.</th>
                          <th>Tipo visita</th>
                          <th>Descripción hecho</th>
                          <th>Consecuencia</th>
                          <th>Vereda</th>
                          <th>Municipio</th>
                          <th>Imagen</th>
                          <th>Fecha</th>
                          <th>Editar</th>
                          <th>Ver</th>
                        </tr>
                      </thead>
                    </table>
                  </div>

                </div>

                <!-- TAB INDICADORES -->
                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                  <div class="card au-ind-card mt-3">
                    <div class="card-header text-center">
                      <h5 class="mb-0">Indicadores por tipo visita</h5>
                    </div>
                    <div class="card-body">
                      <div class="row justify-content-center">
                        <div class="col-12 col-lg-10">
                          <div id="indicadoresContainer" class="mt-3"></div>
                        </div>
                      </div>
                      <!-- aquí se carga tu gráfico dinámico -->
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Modal compromiso -->
          <div class="modal fade" id="modalCompromiso" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Detalle del Compromiso</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                  <p id="contenidoCompromiso" style="white-space: pre-wrap; margin:0;"></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal para adjuntos -->
          <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="modalAdjuntoLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Adjunto</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body text-center" id="contenidoAdjunto" style="padding: 20px;"></div>
              </div>
            </div>
          </div>

          <!-- [ Footer Content ] start -->
          <?php include 'admin/include/footer.php'; ?>
          <!-- [ Footer Content ] end -->

        </div>
      </div>

    </div>
  </div>

  <?php
  $userCodigoMunicipio = SessionData::getCodigoMunicipio();
  $userNombreMunicipio = '';
  if (!empty($userCodigoMunicipio)) {
      $db = new DbConection();
      $pdo = $db->openConect();
      $stmt = $pdo->prepare("SELECT municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada') . " WHERE CAST(codigo_muncipio AS CHAR) = :c LIMIT 1");
      $stmt->execute([':c' => (string)$userCodigoMunicipio]);
      $res = $stmt->fetch(PDO::FETCH_ASSOC);
      $userNombreMunicipio = $res ? (string)$res['municipio'] : '';
      $db->closeConect();
  }
  ?>
  <?php include 'admin/include/gerenic_script.php'; ?>
  <script>
    window.userMunicipioCodigo = '<?php echo $userCodigoMunicipio; ?>';
    window.userMunicipioNombre = '<?php echo $userNombreMunicipio; ?>';
  </script>
  <script type="text/javascript" src="admin/js/control-visitas-alcalde.js"></script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />

</body>
</html>
