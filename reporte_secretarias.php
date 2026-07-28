<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Proyectos.php';

if (!empty($_GET['reporte']) && isset($_GET['reporte']) && $_GET['reporte'] > 0) {
  $rqst = array('id' => $_GET['reporte']);
  $arr = Proyectos::getAll($rqst);

  $isvalid = $arr['output']['valid'];
  $data = $arr['output']['response'];

  if (count($data) > 0) {

    $data = $data[0];
    $id = $data['id'] ? $data['id'] : '';
    $dtcreate = $data['dtcreate'] ?? '';

    $date = isset($data['date']) && $data['date'] != '' ? $data['date'] : ($data['dtcreate'] ?? '');
    $proyecto =  isset($data['proyecto']) ? ($data['proyecto']) : '';
    $provincia =  isset($data['provincia']) ? ($data['provincia']) : '';
    $secretaria = isset($data['secretaria']) ? ($data['secretaria']) : '';
    $departamento = isset($data['departamento']) ? ($data['departamento']) : '';
    $municipio = isset($data['municipio']) ? ($data['municipio']) : '';
    $valor_proyecto = isset($data['valor_proyecto']) ? ($data['valor_proyecto']) : '';
    $porcentaje_ejecucion = isset($data['porcentaje_ejecucion']) ? ($data['porcentaje_ejecucion']) : '';
    $estado = isset($data['estado']) ? ($data['estado']) : '';
    $observaciones = isset($data['observaciones']) ? ($data['observaciones']) : '';

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
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reporte de Inversión - Secretarías</title>

  <style>
    :root{
      --bg0:#070A12; --bg1:#0B1222;
      --card: rgba(255,255,255,.06);
      --stroke: rgba(255,255,255,.10);
      --stroke2: rgba(255,255,255,.14);
      --txt: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);
      --muted2: rgba(255,255,255,.50);
      --good:#18ff6d; --warn:#ffd166; --bad:#ff5b7a;
      --info:#56ccff; --brand:#4f7cff; --brand2:#9b5cff;
      --shadow: 0 20px 60px rgba(0,0,0,.35);
    }
    body.dashboard-body{
      background: radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%), radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%), radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%), linear-gradient(180deg, var(--bg0), var(--bg1));
      color: var(--txt);
      overflow-x:hidden;
    }
    .pcoded-main-container{ background: transparent !important; }
    .pcoded-content{ padding-bottom: 2rem; }
    .page-header .page-block{
      border: 1px solid var(--stroke);
      background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04));
      border-radius: 20px; padding: 16px 20px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
      position: relative; overflow: hidden;
    }
    .page-header h5{ font-weight: 1000 !important; color: var(--txt); margin:0; }
    .breadcrumb{ background: transparent !important; padding:0; margin:.35rem 0 0 !important; }
    .breadcrumb a, .breadcrumb-item{ color: var(--muted) !important; }
    .breadcrumb-item.active{ color: var(--txt) !important; }
    .panel-card{
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.06);
      border-radius: 18px;
      box-shadow: 0 14px 40px rgba(0,0,0,.25);
      backdrop-filter: blur(10px);
      position: relative; overflow: hidden;
    }
    .panel-card:before{
      content:""; position:absolute; inset:-2px;
      background: radial-gradient(260px 140px at 10% 0%, rgba(79,124,255,.18), transparent 60%), radial-gradient(260px 140px at 100% 20%, rgba(155,92,255,.12), transparent 60%);
      opacity:.85; pointer-events:none;
    }
    .panel-card > *{ position:relative; z-index:1; }
    .panel-card .card-header{
      background: rgba(0,0,0,.15);
      border-bottom: 1px solid var(--stroke);
      padding: 16px 20px;
    }
    .panel-card .card-body{ padding: 20px; }
    .panel-card .card-footer{
      background: rgba(0,0,0,.10);
      border-top: 1px solid var(--stroke);
      padding: 16px 20px;
    }
    .badge-gov{
      display:inline-flex; align-items:center; gap:.45rem;
      padding:.35rem .65rem; border-radius: 999px;
      border:1px solid var(--stroke);
      background: rgba(0,0,0,.20);
      color: var(--muted); font-size: 12px; white-space:nowrap;
    }
    hr{ border-color: var(--stroke); }
    .table-gov{
      width: 100%; border-collapse: separate; border-spacing: 0; color: var(--txt);
    }
    .table-gov thead th{
      background: rgba(0,0,0,.25); color: var(--txt);
      font-weight: 900; font-size: .78rem; text-transform: uppercase;
      letter-spacing: .4px; padding: 10px 12px;
      border-bottom: 2px solid var(--stroke); white-space: nowrap;
    }
    .table-gov tbody td{
      padding: 10px 12px; border-bottom: 1px solid var(--stroke);
      color: var(--txt); vertical-align: middle;
    }
    .table-gov tbody tr td{ background: rgba(0,0,0,.08); }
    .table-gov th:first-child{ border-radius: 10px 0 0 0; }
    .table-gov th:last-child{ border-radius: 0 10px 0 0; }
    .table-responsive-gov{
      overflow-x: auto; border-radius: 12px; border: 1px solid var(--stroke);
    }
  </style>
</head>
<body class="dashboard-body">
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header mb-3">
        <div class="page-block">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h5 class="m-b-10 mb-0">Reporte de Inversión - Secretarías</h5>
              <div style="color:var(--muted);font-size:13px;margin-top:4px">Detalle del proyecto No. <?php echo $id; ?></div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge-gov"><?php echo $date; ?></span>
              <?php include './admin/include/btn_back.php'; ?>
            </div>
          </div>
          <ul class="breadcrumb mt-2">
            <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house"></i></a></li>
            <li class="breadcrumb-item active">Reporte Secretaría</li>
          </ul>
        </div>
      </div>

      <div class="panel-card">
        <div class="card-header p-4">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <?php include 'admin/include/generinc_brand_logo.php'; ?>
            </div>
            <div>
              <h3 class="mb-0" style="color:var(--txt);font-weight:1000;font-size:20px">Detalle Proyecto NO <?php echo $id; ?></h3>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-sm-6">
              <div style="font-weight:800;color:var(--muted);font-size:12px;text-transform:uppercase">REPUBLICA DE COLOMBIA</div>
              <div style="color:var(--txt);font-weight:600">DEPARTAMENTO DE SANTANDER</div>
              <div style="color:var(--txt);font-weight:600">GOBERNACIÓN DE SANTANDER</div>
            </div>
            <div class="col-sm-6" style="text-align:right">
              <div style="color:var(--muted)"><strong>Pág. 1</strong> de 1</div>
              <div style="color:var(--muted)"><strong>Código:</strong> </div>
              <div style="color:var(--muted)"><strong>Versión:</strong> 7</div>
              <div style="color:var(--muted)"><strong>Fecha:</strong> <?php echo $date; ?></div>
            </div>
          </div>
          <div class="table-responsive-gov">
            <table class="table-gov">
              <thead>
                <tr>
                  <th>Fecha visita</th>
                  <th>Provincia</th>
                  <th>Municipio</th>
                  <th>Secretaria o Entidad</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo $date; ?></td>
                  <td><?php echo $provincia; ?></td>
                  <td><?php echo $municipio; ?></td>
                  <td><?php echo $secretaria; ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer">
          <p><strong style="color:var(--txt)">Valor proyecto: </strong><span style="color:var(--txt)"><?php echo '$ ' . number_format( $valor_proyecto, 2, ',', '.'); ?></span></p>
          <p><strong style="color:var(--txt)">Proyecto:</strong> <span style="color:var(--txt)"><?php echo $proyecto; ?></span></p>
          <p><strong style="color:var(--txt)">Estado:</strong> <span style="color:var(--txt)"><?php echo $estado; ?></span></p>
          <p><strong style="color:var(--txt)">Porcentaje Ejecución:</strong> <span style="color:var(--txt)"><?php echo $porcentaje_ejecucion; ?></span></p>
          <p><strong style="color:var(--txt)">Observaciones:</strong> <span style="color:var(--txt)"><?php echo $observaciones; ?></span></p>

          <?php if (!is_null($data["imagen"])): ?>
            <p><strong style="color:var(--txt)">REGISTRO FOTOGRAFICO</strong></p>
            <div class="registroFotografico">
              <img src="assets/img/admin/usuarios<?php echo $data["imagen"] ?>" alt="" width="auto" height="auto" style="max-width:100%;border-radius:12px;border:1px solid var(--stroke)">
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
</body>
</html>
