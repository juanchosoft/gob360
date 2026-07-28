<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

extract(PagePermissions::crudVarsForCurrentPage());
include './admin/classes/VisitasAlcalde.php';
include './admin/classes/CompromisoMunicipioAlcalde.php';
include './admin/classes/Departamento.php';
include './admin/classes/ProyectosAlcalde.php';

$municipioId = $_REQUEST["mun"] ?? '';
$departamentoId = $_REQUEST["dep"] ?? '68';
$municipioIdInt = intval($municipioId);

$arrVisitas = VisitasAlcalde::getVisitasPorMunicipio(["tbl_municipio_id" => $municipioIdInt]);
$visitas = $arrVisitas["output"]["response"] ?? [];

$arrCompromisos = CompromisoMunicipioAlcalde::getAll(["tbl_municipio_id" => $municipioId]);
$compromisos = $arrCompromisos["output"]["response"] ?? [];

$arrDep = Departamento::getAll(null);
$arrDepList = $arrDep['output']['response'];
$optionDep = '';
foreach ($arrDepList as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == $departamentoId ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

$arrDepSelected = Departamento::getAll(["codigo_departamento" => $departamentoId]);
$departamento = $arrDepSelected['output']['response'][0] ?? [];

include './admin/classes/Ciudad.php';
$arrMun = Ciudad::getAll(["codigo_muncipio" => $municipioId]);
$municipio = $arrMun['output']['response'][0] ?? [];

$colorFila = [
    'Cumplido' => '#1a5c2a',
    'En Trámite' => '#5c4a1a',
    'Sin Cumplir' => '#5c1a1a'
];

$proyectosParams = ["mun" => $municipioIdInt];

$arr = ProyectosAlcalde::getAllproyectosxsecre($proyectosParams);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'] ?? [];
$arrData = $arr;

$totalEjecutado = 0;
if (!empty($arrData)) {
    foreach ($arrData as $key => $value) {
        $totalEjecutado += is_null($value["porcentaje_ejecucion"]) ? 0 : doubleval($value["porcentaje_ejecucion"]);
    }
    $totalEjecutado = $totalEjecutado == 0 ? 0 : round($totalEjecutado / count($arrData), 2);
}

$datosSecre = ProyectosAlcalde::getInversionBySecre($proyectosParams);
$arrSecre = $datosSecre['output']['response'] ?? ['labels' => [], 'data' => []];

$arrTotal = ProyectosAlcalde::getInversiontotal($proyectosParams);
$arrTotal = $arrTotal['output']['response'] ?? [];
$arrTotalData = $arrTotal;

$total_invertido = 0;
if (!empty($arrTotalData)) {
    foreach ($arrTotalData as $key => $value) {
        $total_invertido += is_null($value["SumaDevalor_proyecto"]) ? 0 : doubleval($value["SumaDevalor_proyecto"]);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Estado Municipios - Alcalde</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <style>
    :root{
      --bg0:#070A12;
      --bg1:#0B1222;
      --card: rgba(255,255,255,.06);
      --stroke: rgba(255,255,255,.10);
      --stroke2: rgba(255,255,255,.14);
      --txt: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);
      --muted2: rgba(255,255,255,.50);
      --good:#18ff6d;
      --warn:#ffd166;
      --bad:#ff5b7a;
      --info:#56ccff;
      --brand:#4f7cff;
      --brand2:#9b5cff;
      --shadow: 0 20px 60px rgba(0,0,0,.35);
    }

    body.dashboard-body{
      background:
        radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
        radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
        radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
        linear-gradient(180deg, var(--bg0), var(--bg1));
      color: var(--txt);
      overflow-x:hidden;
    }

    .pcoded-main-container{ background: transparent !important; }
    .pcoded-content{ padding-bottom: 2rem; }

    .page-header .page-block{
      border: 1px solid var(--stroke);
      background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.04));
      border-radius: 20px;
      padding: 16px 20px;
      box-shadow: var(--shadow);
      backdrop-filter: blur(10px);
      position: relative;
      overflow: hidden;
    }
    .page-header h5{ font-weight: 1000 !important; color: var(--txt); margin:0; }
    .breadcrumb{ background: transparent !important; padding:0; margin:.35rem 0 0 !important; }
    .breadcrumb a, .breadcrumb-item{ color: var(--muted) !important; }
    .breadcrumb-item.active{ color: var(--txt) !important; }

    .panel-card{
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.06);
      border-radius: 18px;
      padding: 14px;
      box-shadow: 0 14px 40px rgba(0,0,0,.25);
      backdrop-filter: blur(10px);
      position: relative;
      overflow: hidden;
    }
    .panel-card:before{
      content:"";
      position:absolute; inset:-2px;
      background:
        radial-gradient(260px 140px at 10% 0%, rgba(79,124,255,.18), transparent 60%),
        radial-gradient(260px 140px at 100% 20%, rgba(155,92,255,.12), transparent 60%);
      opacity:.85;
      pointer-events:none;
    }
    .panel-card > *{ position:relative; z-index:1; }

    .panel-title{
      display:flex; align-items:center; justify-content:space-between; gap:10px;
      margin-bottom: 10px;
      font-weight: 950;
      color: var(--txt);
    }

    .chip{
      display:inline-flex; align-items:center; gap:.45rem;
      padding:.35rem .65rem; border-radius: 999px;
      border:1px solid var(--stroke);
      background: rgba(0,0,0,.20);
      color: var(--muted); font-size: 12px; white-space:nowrap;
    }
    .chip b{ color: var(--txt); font-weight: 800; }

    label{
      font-weight: 800;
      color: rgba(255,255,255,.8) !important;
      margin-bottom: 6px;
      font-size: 13px;
    }

    .form-control, select.form-control, input.form-control{
      border-radius: 14px !important;
      border: 1px solid var(--stroke) !important;
      min-height: 44px;
      box-shadow: none !important;
      background: rgba(10,17,33,.55) !important;
      color: #fff !important;
    }
    .form-control:focus{
      border-color: rgba(79,124,255,.45) !important;
      box-shadow: 0 0 0 .20rem rgba(79,124,255,.22) !important;
    }

    .badge-gov{
      display:inline-flex; align-items:center; gap:.45rem;
      padding:.35rem .65rem; border-radius: 999px;
      border:1px solid var(--stroke);
      background: rgba(0,0,0,.20);
      color: var(--muted); font-size: 12px; white-space:nowrap;
    }

    .btn-wow{
      border:1px solid var(--stroke2);
      background: rgba(255,255,255,.06);
      color: var(--txt);
      border-radius: 12px;
      padding: .6rem .85rem;
      font-weight: 900;
      transition: .2s ease;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }
    .btn-wow:hover{ transform: translateY(-1px); background: rgba(255,255,255,.10); color: var(--txt); }

    hr{ border-color: var(--stroke); }

    /* ============================
       TABLAS oscuras como dashboard
       ============================ */
    .table-gov{
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      color: var(--txt);
    }
    .table-gov thead th{
      background: rgba(0,0,0,.25);
      color: var(--txt);
      font-weight: 900;
      font-size: .78rem;
      text-transform: uppercase;
      letter-spacing: .4px;
      padding: 10px 12px;
      border-bottom: 2px solid var(--stroke);
      white-space: nowrap;
    }
    .table-gov tbody td{
      padding: 10px 12px;
      border-bottom: 1px solid var(--stroke);
      color: var(--txt);
      vertical-align: middle;
      white-space: nowrap;
    }
    .table-gov tbody tr:nth-of-type(odd) td{
      background: rgba(255,255,255,.02);
    }
    .table-gov tbody tr:nth-of-type(even) td{
      background: rgba(0,0,0,.08);
    }
    .table-gov tbody tr:hover td{
      background: rgba(79,124,255,.10);
    }
    .table-gov th:first-child{ border-radius: 10px 0 0 0; }
    .table-gov th:last-child{ border-radius: 0 10px 0 0; }
    .table-gov td a{ color: var(--info); }

    .table-responsive-gov{
      overflow-x: auto;
      border-radius: 12px;
      border: 1px solid var(--stroke);
    }

    /* Charts */
    .chart-wrap{ position: relative; height: 280px; }
    @media (max-width: 991px){ .chart-wrap{ height: 260px; } }
    @media (max-width: 575px){ .chart-wrap{ height: 220px; } }

    /* Flex sections */
    .flex-section{
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
    }
    .flex-section > *{
      flex: 1;
      min-width: 300px;
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
              <h5 class="m-b-10 mb-0">Estado Municipios - Alcalde</h5>
              <div style="color:var(--muted);font-size:13px;margin-top:4px">Información consolidada del municipio</div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge-gov"><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y'); ?></span>
              <?php include './admin/include/btn_back.php'; ?>
            </div>
          </div>
          <ul class="breadcrumb mt-2">
            <li class="breadcrumb-item"><a href="index.php"><i class="bi bi-house"></i></a></li>
            <li class="breadcrumb-item"><a href="mapa_visitas_alcalde.php?depto_id=21">Dashboard Alcalde</a></li>
            <li class="breadcrumb-item active">Estado Municipios</li>
          </ul>
        </div>
      </div>

      <!-- FILTROS -->
      <div class="panel-card" style="margin-bottom:16px">
        <div class="panel-title">Filtros de consulta</div>
        <form id="formusuarios" role="form" autocomplete="false">
          <input type="hidden" name="op" id="op" />
          <input type="hidden" name="id" id="id" />
          <div style="display:flex;flex-wrap:wrap;gap:16px">
            <?php
            $userType = SessionData::getUserType();
            $isUsuarioMunicipal = in_array($userType, ['Alcalde', 'Auxiliar']);
            $disabledAttr = $isUsuarioMunicipal ? 'disabled' : '';
            ?>
            <div style="flex:1;min-width:200px">
              <label>Departamento</label>
              <select class="form-control" id="tbl_departamento_id" <?php echo $disabledAttr; ?> name="tbl_departamento_id"><?php echo $optionDep; ?></select>
            </div>
            <div style="flex:1;min-width:200px">
              <label>Municipio</label>
              <select onchange="ESTADO_MUN_ALCALDE.updateUrlMunicipio(this)" class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" <?php echo $disabledAttr; ?>></select>
            </div>
          </div>
        </form>
      </div>

      <!-- VISITAS REALIZADAS -->
      <div class="panel-card" style="margin-bottom:16px">
        <div class="panel-title">
          <span><i class="bi bi-calendar-check" style="margin-right:6px;color:var(--info)"></i> Visitas Realizadas</span>
          <span class="chip"><b><?= count($visitas) ?></b> registros</span>
        </div>
        <div class="table-responsive-gov">
          <table class="table-gov">
            <thead>
              <tr>
                <th>Imagen</th>
                <th>Fecha</th>
                <th>Vereda</th>
                <th>Tipo Visita</th>
                <th>Motivo Visita</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($visitas)): ?>
                <?php foreach ($visitas as $value) :
                  $img = empty($value["img"]) ? 'dist/img/logorelsinf.png' : "assets/img/admin/usuarios/" . $value["img"];
                ?>
                  <tr>
                    <td>
                      <img src="<?php echo $img; ?>" alt="user" class="rounded-circle"
                        style="width:32px;height:32px;object-fit:cover;cursor:pointer"
                        onclick="$('#imageModal<?php echo $value['id']; ?>').modal('show')">
                    </td>
                    <td><?php echo htmlspecialchars($value["date"] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($value["vereda"] ?? 'Sin vereda'); ?></td>
                    <td><?php echo htmlspecialchars($value["tipo_visita"] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars(!empty($value["descripcion_hecho"]) ? $value["descripcion_hecho"] : ($value["compromisos"] ?? '')); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px"><i class="bi bi-inbox"></i> No hay visitas registradas para este municipio</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- MODALES DE IMAGEN -->
      <?php if (!empty($visitas)): ?>
        <?php foreach ($visitas as $value) :
          $img = empty($value["img"]) ? 'dist/img/logorelsinf.png' : "assets/img/admin/usuarios/" . $value["img"];
        ?>
        <div class="modal fade" id="imageModal<?php echo $value['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="background:rgba(10,17,33,.98);border:1px solid var(--stroke);border-radius:16px">
              <div class="modal-header" style="border-bottom:1px solid var(--stroke)">
                <h5 class="modal-title" style="color:var(--txt);font-weight:1000">Imagen de Visita</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;text-shadow:none">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body text-center">
                <img src="<?php echo $img; ?>" alt="user" class="img-fluid" style="border-radius:8px">
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- COMPROMISOS PACTADOS -->
      <div class="panel-card" style="margin-bottom:16px">
        <div class="panel-title">
          <span><i class="bi bi-file-text" style="margin-right:6px;color:var(--warn)"></i> Compromisos Pactados en el Municipio</span>
          <span class="chip"><b><?= count($compromisos) ?></b> registros</span>
        </div>
        <div class="table-responsive-gov">
          <table class="table-gov">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Vereda</th>
                <th>Compromiso</th>
                <th>Estado</th>
                <th>Respuesta</th>
                <th>Secretaría</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($compromisos)): ?>
                <?php foreach ($compromisos as $value) : ?>
                  <tr style="background-color: <?= htmlspecialchars($colorFila[$value['cumplimiento']] ?? 'transparent') ?>; color: #fff;">
                    <td><?= htmlspecialchars($value["date"] ?? '') ?></td>
                    <td><?= htmlspecialchars($value["vereda"] ?? 'Sin vereda') ?></td>
                    <td><?= htmlspecialchars($value["compromisos"] ?? '') ?></td>
                    <td><?= htmlspecialchars($value["cumplimiento"] ?? '') ?></td>
                    <td><?= htmlspecialchars($value["respuesta"] ?? '') ?></td>
                    <td><?= htmlspecialchars($value["secretaria"] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px"><i class="bi bi-inbox"></i> No hay compromisos registrados para este municipio</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- GRÁFICAS -->
      <div class="flex-section" style="margin-bottom:16px">
        <div class="panel-card">
          <div class="panel-title" style="justify-content:center">
            <span>Estado de proyectos en general</span>
          </div>
          <div class="chart-wrap" style="display:flex;align-items:center;justify-content:center">
            <div id="gender_donut" style="width:100%;height:230px"></div>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:12px;padding:0 8px">
            <div>
              <div style="font-weight:1000;font-size:20px;color:var(--txt)"><?= htmlspecialchars($totalEjecutado) ?>%</div>
              <div style="font-size:12px;color:var(--muted)">Ejecutado</div>
            </div>
            <div style="text-align:right">
              <div style="font-weight:1000;font-size:20px;color:var(--txt)"><?= htmlspecialchars(100 - $totalEjecutado) ?>%</div>
              <div style="font-size:12px;color:var(--muted)">Faltante</div>
            </div>
          </div>
        </div>
        <div class="panel-card">
          <div class="panel-title" style="justify-content:center">
            <span>Inversión por Secretarías</span>
          </div>
          <div class="chart-wrap" style="display:flex;align-items:center;justify-content:center">
            <canvas id="chartjs_bar_horizontal" style="max-width:100%;max-height:240px"></canvas>
          </div>
        </div>
      </div>

      <!-- INVERSIÓN DETALLADA -->
      <div class="panel-card">
        <div class="panel-title">
          <span><i class="bi bi-pie-chart" style="margin-right:6px;color:var(--good)"></i> Inversión detallada por Secretaría</span>
        </div>
        <div style="text-align:center;margin-bottom:12px;padding:8px;border:1px solid var(--stroke);border-radius:12px;background:rgba(0,0,0,.10)">
          <span style="font-weight:950;color:var(--txt);font-size:15px">
            Valor total inversión: <?= htmlspecialchars('$ ' . number_format($total_invertido, 2, ',', '.')) ?>
          </span>
        </div>
        <div class="table-responsive-gov">
          <table class="table-gov">
            <thead>
              <tr>
                <th>Ver Detallado</th>
                <th>Secretaría</th>
                <th>Valor Proyecto</th>
                <th>Nombre Proyecto</th>
                <th>% Ejecución</th>
                <th>Fecha Entrega</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($isvalid && !empty($arrData)) : ?>
                <?php foreach ($arrData as $item) : ?>
                  <tr>
                    <td><a href="reporte_secretarias.php?reporte=<?= htmlspecialchars($item['id']) ?>" target="_blank" title="Ver"><i class="bi bi-eye"></i></a></td>
                    <td><?= htmlspecialchars($item['secretaria'] ?? '') ?></td>
                    <td><?= htmlspecialchars('$ ' . number_format($item['valor_proyecto'] ?? 0, 2, ',', '.')) ?></td>
                    <td><?= htmlspecialchars($item['proyecto'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['porcentaje_ejecucion'] ?? '0') ?>%</td>
                    <td><?= htmlspecialchars($item['fecha_entrega'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['estado'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px"><i class="bi bi-inbox"></i> No hay proyectos registrados para este municipio</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /pcoded-content -->
  </div><!-- /pcoded-main-container -->

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script type="text/javascript" src="admin/js/departamento.js"></script>
  <script type="text/javascript" src="admin/js/estado_municipios_alcalde.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

  <script>
    const TOTAL_EJECUTADO = <?= json_encode($totalEjecutado) ?>;
    const TOTAL_POR_EJECUTAR = <?= json_encode(100 - $totalEjecutado) ?>;
    const LABELS_SECRETARIA = <?= json_encode($arrSecre["labels"] ?? []) ?>;
    const DATA_SECRETARIA = <?= json_encode($arrSecre["data"] ?? []) ?>;

    $(function() {
      Morris.Donut({
        element: 'gender_donut',
        data: [
          { value: parseFloat(TOTAL_EJECUTADO), label: 'Ejecutado' },
          { value: parseFloat(TOTAL_POR_EJECUTAR), label: 'Por Ejecutar' }
        ],
        labelColor: 'rgba(255,255,255,.90)',
        colors: ['#4f7cff', '#ff5b7a'],
        formatter: function(x) { return x + "%" }
      });

      var ctx = document.getElementById("chartjs_bar_horizontal").getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: LABELS_SECRETARIA,
          datasets: [{
            label: 'Total Invertido',
            data: DATA_SECRETARIA,
            backgroundColor: ['rgba(79,124,255,.35)', 'rgba(155,92,255,.35)', 'rgba(24,255,109,.25)', 'rgba(255,209,102,.25)', 'rgba(86,204,255,.25)', 'rgba(255,91,122,.25)'],
            borderColor: ['#4f7cff', '#9b5cff', '#18ff6d', '#ffd166', '#56ccff', '#ff5b7a'],
            borderWidth: 1
          }]
        },
        options: {
          indexAxis: 'y', responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { position: 'top', labels: { color: 'rgba(255,255,255,.85)' } },
            title: { display: false }
          },
          scales: {
            x: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,.70)' }, grid: { color: 'rgba(255,255,255,.08)' } },
            y: { ticks: { color: 'rgba(255,255,255,.85)' }, grid: { color: 'rgba(255,255,255,.08)' } }
          }
        }
      });
    });
  </script>

  <script>
    const params = UTIL.getParamsFromUrlDepartamentoMunicipio();
    selectedMunicipio = params.mun;
    DEPARTAMENTO.getMunicipiosByDepartamentoIdV2SeteraCodigoMunicipio(UTIL.getDepartamentoPrincipal(), params.mun);

    function loadContentidoMapa() {
      const currentUrl = new URL(window.location.href);
      $.ajax({
        url: currentUrl.toString(),
        type: "GET",
        success: function(response) {
          const updatedContent = $(response).find("#divEstadoMunicipio").html();
          $("#divEstadoMunicipio").html(updatedContent);
        },
        error: function(error) {
          console.error("Error al cargar contenido:", error);
        }
      });
    }
  </script>

</body>
</html>
