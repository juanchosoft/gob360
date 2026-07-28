<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Desarrollo.php';
include './admin/classes/Secretarias.php';

$modulo = 'Metas Plan de Desarrollo';

$arr = [];
$filter_params = [];

// filtra POR SECRETARIA
if (isset($_SESSION['session_user']['secretaria'])) {
  $filter_params['secretaria_id'] = intval($_SESSION['session_user']['secretaria']);
}

// filtra POR municipio
$rol_usuario = isset($_SESSION['session_user']['tipo']) ? $_SESSION['session_user']['tipo'] : '';

if ($rol_usuario === 'Secretario_Despacho' || $rol_usuario === 'Alcalde' || $rol_usuario === 'Gobernador') {
  $municipio_id_municipal = isset($_SESSION['session_user']['tbl_municipio_id']) ? intval($_SESSION['session_user']['tbl_municipio_id']) : 0;
  if ($municipio_id_municipal > 0) {
    $filter_params['tbl_municipio_id'] = $municipio_id_municipal;
    unset($filter_params['secretaria_id']);
  }
}

$arr = Desarrollo::getAll($filter_params);
$isvalid = $arr['output']['valid'] ?? false;
$arr = $arr['output']['response'] ?? [];
$arrData = $arr;

// Información de Secretarias
$arrSecretarias = Secretarias::getAll(null);
$arrSecretarias = $arrSecretarias['output']['response'] ?? [];
$option = '<option value="seleccione">Seleccione...</option>';
foreach ($arrSecretarias as $val) {
  $option .= "<option value='" . $val['id'] . "'>" . $val['secretaria'] . "-" . $val['secretaria'] . "</option>";
}
?>

<style>
  :root{
    --nav-blue:#20427F;
    --nav-blue-2:#132b52;
    --nav-blue-3:#2e58a8;

    --bg0:#0b1220;
    --bg1:#0e1830;

    --card: rgba(255,255,255,.06);
    --card2: rgba(255,255,255,.08);
    --line: rgba(255,255,255,.10);

    --paper:#ffffff;
    --ink:#0f172a;
    --muted:#94a3b8;
    --muted2:#cbd5e1;

    --shadow: 0 18px 55px rgba(0,0,0,.38);
    --shadow2: 0 12px 30px rgba(0,0,0,.22);
    --radius:18px;

    --focus: rgba(34,193,255,.18);
  }

  /* Fondo SaaS */
  .pcoded-main-container{
    background:
      radial-gradient(900px 600px at 18% 10%, rgba(120,88,255,.18), transparent 55%),
      radial-gradient(900px 600px at 85% 18%, rgba(0,187,255,.14), transparent 55%),
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
      radial-gradient(900px 520px at 18% 18%, rgba(0,187,255,.16), transparent 62%),
      radial-gradient(900px 520px at 82% 18%, rgba(120,88,255,.18), transparent 62%),
      linear-gradient(135deg, rgba(32,62,92,.92), rgba(47,63,110,.82));
    filter: saturate(1.1) contrast(1.05);
  }
  .au-hero__content{
    position: relative;
    padding: 18px 18px 16px;
    color: rgba(255,255,255,.92);
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

  /* Cards glass */
  .card{
    border-radius: var(--radius);
    border: 1px solid var(--line);
    box-shadow: var(--shadow2);
    overflow: hidden;
    background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.05));
    backdrop-filter: blur(10px);
  }
  .card-header{
    background: linear-gradient(135deg, rgba(32,66,127,.25), rgba(19,43,82,.18));
    border-bottom: 1px solid rgba(255,255,255,.10);
    font-weight: 1000;
    color: rgba(255,255,255,.92);
  }
  .card-header h5{ color: rgba(255,255,255,.92); }

  /* Mensajes */
  #message-container .alert{
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.14);
    box-shadow: var(--shadow2);
    backdrop-filter: blur(10px);
  }

  /* Upload card estilo */
  .upload-card{
    border-radius: 18px;
    border: 1px dashed rgba(255,255,255,.22);
    background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.05));
  }
  .upload-card .card-header{
    padding: 18px !important;
  }
  .upload-card .card-body{
    padding: 18px !important;
  }
  .upload-card .form-label{
    color: rgba(255,255,255,.88);
    font-weight: 1000;
  }
  .upload-card .form-control{
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(2,6,23,.30);
    color: rgba(255,255,255,.92);
  }
  .upload-card .form-control::file-selector-button{
    border: 0;
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
    color:#fff;
    font-weight: 900;
    border-radius: 12px;
  }
  .upload-card .form-control:focus{
    outline: none !important;
    box-shadow: 0 0 0 .2rem var(--focus) !important;
    border-color: rgba(34,193,255,.45) !important;
  }

  /* Botones */
  .btn{
    border-radius: 14px !important;
    font-weight: 1000 !important;
    letter-spacing: .2px;
  }
  .btn-primary{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    border: none !important;
    box-shadow: 0 10px 22px rgba(32,66,127,.20);
  }
  .btn-primary:hover{ transform: translateY(-1px); filter: brightness(1.04); }
  .btn-secondary{
    border: 1px solid rgba(255,255,255,.18) !important;
    background: rgba(255,255,255,.08) !important;
    color: rgba(255,255,255,.92) !important;
    box-shadow: 0 10px 22px rgba(2,6,23,.06);
  }

  /* ===== TABLA: SIEMPRE NEGRO SOBRE BLANCO ===== */
  .table-wrap{
    display:flex;
    justify-content:center;
  }

  .table-shell{
    width: 100%;
    max-width: 1320px;
    background: var(--paper);
    border-radius: 18px;
    overflow:hidden;
    border: 1px solid rgba(15,23,42,.10);
    box-shadow: 0 26px 70px rgba(0,0,0,.22);
  }

  table.dataTable,
  #dynamictable{
    font-size: 12.5px !important;
    color: #0f172a !important;
    background: #fff !important;
    margin:0 !important;
  }

  #dynamictable thead th{
    font-size: 11.5px !important;
    text-transform: uppercase;
    letter-spacing: .28px;
    white-space: nowrap;
    color: #ffffff !important;
    background: linear-gradient(135deg, #203e5c, #2f3f6e) !important;
    border-bottom: 1px solid rgba(15,23,42,.10) !important;
  }

  #dynamictable tbody td{
    vertical-align: top;
    white-space: normal;
    word-break: break-word;
    color: #0f172a !important;
    background: #fff !important;
    border-top: 1px solid rgba(15,23,42,.08) !important;
  }

  #dynamictable tbody tr:nth-child(even) td{
    background:#f8fafc !important;
  }

  #dynamictable.table-sm > :not(caption) > * > *{
    padding: .50rem .55rem;
  }

  /* DataTables UI */
  .dataTables_wrapper .dataTables_filter input,
  .dataTables_wrapper .dataTables_length select{
    border-radius: 12px !important;
    border: 1px solid rgba(15,23,42,.16) !important;
    padding: 6px 10px !important;
    font-size: 12px !important;
    outline: none !important;
  }
  .dataTables_wrapper .dataTables_filter input:focus,
  .dataTables_wrapper .dataTables_length select:focus{
    box-shadow: 0 0 0 .2rem rgba(32,66,127,.14) !important;
    border-color: rgba(32,66,127,.35) !important;
  }
  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_paginate{
    font-size: 12px;
    color:#334155;
    padding: 10px 12px;
  }
  .dataTables_wrapper .paginate_button{
    border-radius: 10px !important;
  }

  /* Input Avance pro */
  .avance-input{
    border-radius: 14px !important;
    border: 1px solid rgba(15,23,42,.16) !important;
    padding: 8px 10px !important;
    font-size: 12.5px !important;
    min-width: 110px;
    transition: box-shadow .18s ease, border-color .18s ease, transform .18s ease;
    background: #fff;
    color:#0f172a;
  }
  .avance-input:focus{
    border-color: rgba(32,66,127,.55) !important;
    box-shadow: 0 0 0 .2rem rgba(32,66,127,.16) !important;
    outline: none !important;
  }

  /* Truncado pro */
  td.truncado{
    max-width: 280px;
    font-size: 12.5px;
    vertical-align: top;
  }
  .clamp-2{
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  td.truncado a{
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    color: #1e40af;
    font-weight: 1000;
    font-size: 12px;
    text-decoration: none;
  }
  td.truncado a:hover{ text-decoration: underline; }

  /* Modal texto completo */
  #modalTextoCompleto .modal-content{
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.10);
    box-shadow: var(--shadow);
  }
  #modalTextoCompleto .modal-header{
    background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2)) !important;
    color: #fff !important;
    border-bottom: 1px solid rgba(255,255,255,.12);
  }
  #modalTextoCompleto .close span{ color:#fff; opacity:.9; }

  /* Breadcrumb contrast */
  .page-header .breadcrumb,
  .page-header .breadcrumb a{ color: rgba(255,255,255,.75) !important; }
  .page-header h5{ color: rgba(255,255,255,.92) !important; }

  @media (max-width: 768px){
    td.truncado{ max-width: 210px; }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const message = "<?php echo isset($_SESSION['message']) ? addslashes($_SESSION['message']) : ''; ?>";

    if (message) {
      const container = document.getElementById('message-container');
      let alertClass = 'alert-danger';
      if (message.includes('Éxito')) alertClass = 'alert-success';

      container.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
          <strong>Mensaje:</strong> ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;
    }
  });
</script>

<body class="">
  <!-- Loader -->
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- HERO -->
      <div class="au-hero">
        <div class="au-hero__bg"></div>
        <div class="au-hero__content">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <div class="au-kicker"><span class="au-dot"></span><span>PLAN DE DESARROLLO • METAS</span></div>
              <h2 class="au-title mb-1"><i data-feather="target"></i> Metas Plan de Desarrollo</h2>
              <div class="au-subtitle">Carga masiva por Excel y control de avance 2025 por registro (según permisos).</div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <?php include './admin/include/btn_back.php'; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Mensajes -->
      <div class="row">
        <div class="col-12" id="message-container"></div>
      </div>

      <!-- Upload Excel -->
      <div class="row">
        <div class="col-12 col-xl-12">
          <div class="card my-4 upload-card">
            <div class="card-header">
              <h5 class="mb-0" style="font-weight:1000;">Creación de Metas del Plan de Desarrollo</h5>
            </div>

            <div class="card-body">
              <?php
              if ($rol_usuario == 'Alcalde' || $rol_usuario == 'Auxiliar' || $rol_usuario === 'SuperAdministrador' || $rol_usuario === 'Gobernador' || $rol_usuario === 'Secretario_Gobernacion') {
              ?>
                <form action="procesar_excel.php" method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
                  <div class="col-md-6">
                    <label for="excelFile" class="form-label">Subir archivo de Excel <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="excelFile" name="excelFile" required />
                    <div style="color:rgba(255,255,255,.68); font-size:12px; margin-top:6px;">
                      Usa la plantilla oficial para evitar errores de columnas.
                    </div>
                  </div>

                  <div class="col-md-6 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary px-4">Subir Plan y Procesar</button>
                    <a href="SharedFiles/plan.xlsx" class="btn btn-secondary px-4" download>Descargar plantilla</a>
                  </div>
                </form>
              <?php } else { ?>
                <div style="color:rgba(255,255,255,.78); font-size:12.5px;">
                  Tu rol no tiene permisos para carga masiva. Puedes consultar y actualizar avances donde aplique.
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="row">
        <div class="col-xl-12 col-md-12">
          <div class="card table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <h5 class="mb-0" style="font-weight:1000;">Metas Plan de Desarrollo</h5>
              <div class="card-header-right">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
              <div class="table-wrap">
                <div class="table-shell">
                  <div class="table-responsive p-0">
                    <table id="dynamictable" class="table table-hover table-bordered table-sm w-100">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>EJE ESTRATÉGICO</th>
                          <th>SECTOR PDD</th>
                          <th>SECTOR CATALOGO DE PRODUCTOS</th>
                          <th>PRODUCTO, BIEN O SERVICIO PDD</th>
                          <th>SECRETARIA RESPONSABLE</th>
                          <th>DIRECCIÓN RESPONSABLE</th>
                          <th>2024</th>
                          <th>Avance 2025</th>
                          <th>Editar Avance</th>
                          <th>2025</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if ($isvalid): ?>
                          <?php foreach ($arr as $item): ?>
                            <tr>
                              <td><?= htmlspecialchars($item['id']); ?></td>
                              <td><?= htmlspecialchars($item['eje_estrategico']); ?></td>
                              <td><?= htmlspecialchars($item['sector_pdd']); ?></td>
                              <td><?= htmlspecialchars($item['sector_cat_prod']); ?></td>
                              <td class="truncado">
                                <div class="clamp-2">
                                  <?= htmlspecialchars($item['producto_servicio_pdd']); ?>
                                </div>
                                <a href="javascript:void(0);" onclick="mostrarTextoCompleto(`<?= htmlspecialchars($item['producto_servicio_pdd']); ?>`)">
                                  <i class="feather icon-eye"></i> Ver más
                                </a>
                              </td>
                              <td><?= htmlspecialchars($item['secretaria']); ?></td>
                              <td><?= htmlspecialchars($item['direccion_resp']); ?></td>
                              <td><?= htmlspecialchars($item['ps2024']); ?></td>
                              <td><?= htmlspecialchars($item['avance_2025']); ?></td>
                              <td style="min-width:140px;">
                                <input
                                  onKeyPress="return soloNumeros(event);"
                                  type="text"
                                  class="form-control avance-input"
                                  id="avance_2025_<?= htmlspecialchars($item['id']); ?>"
                                  name="avance_2025_<?= htmlspecialchars($item['id']); ?>"
                                  placeholder="0"
                                  onblur="DESARROLLO.updateAvance(<?= htmlspecialchars($item['id']); ?>)">
                              </td>
                              <td><?= htmlspecialchars($item['ps2025']); ?></td>
                            </tr>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div><!-- shell -->
              </div><!-- wrap -->
            </div><!-- body -->
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal texto completo -->
  <div class="modal fade" id="modalTextoCompleto" tabindex="-1" role="dialog" aria-labelledby="textoCompletoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content" style="height: 36vh;">
        <div class="modal-header">
          <h5 class="modal-title" id="textoCompletoLabel">Producto, Bien o Servicio</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="contenidoTextoCompleto" style="white-space: pre-wrap; padding: 12px; overflow:auto; color:#0f172a;"></div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.css">

  <script>
    function mostrarTextoCompleto(texto) {
      document.getElementById('contenidoTextoCompleto').textContent = texto;
      $('#modalTextoCompleto').modal('show');
    }
  </script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <?php include './admin/include/generic_dataTables.php'; ?>
  <script type="text/javascript" src="admin/js/plan_desarrollo.js"></script>
</body>
</html>