<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
include './admin/classes/SecretariasMunicipio.php';
$modulo = 'Banco Proyectos Alcaldía';

// Obtener nombre del municipio del alcalde logueado
$nombreMunicipio = '';
$codigoMunicipio = SessionData::getCodigoMunicipio();
if (!empty($codigoMunicipio)) {
  $db = new DbConection();
  $pdo = $db->openConect();
  $queryMun = "SELECT municipio FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_muncipio = :codigo";
  $stmtMun = $pdo->prepare($queryMun);
  $stmtMun->execute([':codigo' => $codigoMunicipio]);
  $resMun = $stmtMun->fetch(PDO::FETCH_ASSOC);
  if ($resMun) {
    $nombreMunicipio = (string)$resMun['municipio'];
  }
  $db->closeConect();
}

// Información de proyectos por secretaría municipal
error_log("proyecto_x_secretaria_alcalde.php - REQUEST recibido: " . json_encode($_REQUEST));
$arr = SecretariasMunicipio::getAllProyectosxSecre($_REQUEST);
error_log("proyecto_x_secretaria_alcalde.php - Respuesta completa: " . json_encode($arr));
error_log("proyecto_x_secretaria_alcalde.php - Valid: " . ((($arr['output']['valid'] ?? false) === true) ? 'true' : 'false'));
error_log("proyecto_x_secretaria_alcalde.php - Total proyectos: " . count($arr['output']['response'] ?? []));

$isvalid = $arr['output']['valid'] ?? false;
$rows    = $arr['output']['response'] ?? [];
$arrData = $rows;

function h($v){
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// KPIs (solo UI, no toca backend)
$totalProyectos = 0;
$sumaCOP = 0.0;
$promFis = 0.0; $cntFis = 0;
$promFin = 0.0; $cntFin = 0;

if ($isvalid && !empty($rows)) {
  foreach ($rows as $r) {
    $totalProyectos++;
    $sumaCOP += (float)($r['valor_proyecto'] ?? 0);

    if (isset($r['porcentaje_ejecucion']) && $r['porcentaje_ejecucion'] !== '') {
      $promFis += (float)$r['porcentaje_ejecucion'];
      $cntFis++;
    }
    if (isset($r['porcentaje_financiero']) && $r['porcentaje_financiero'] !== '') {
      $promFin += (float)$r['porcentaje_financiero'];
      $cntFin++;
    }
  }
}
$promFis = $cntFis ? ($promFis / $cntFis) : 0;
$promFin = $cntFin ? ($promFin / $cntFin) : 0;
?>

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
      --radius-xl: 20px;
      --radius-lg: 16px;
      --radius-md: 12px;

      --shadow-soft: 0 10px 28px rgba(2, 6, 23, .10);
      --shadow-mid:  0 16px 42px rgba(2, 6, 23, .14);

      --ink: #0f172a;
      --muted: #64748b;
      --line: rgba(2, 6, 23, .10);

      --brand-1: #0d6efd;
      --brand-2: #2e58a8;

      --success-1:#16a34a;
      --success-2:#065f46;

      --warn-1:#f59e0b;
      --warn-2:#92400e;

      --danger-1:#ef4444;
      --danger-2:#991b1b;

      --bg-soft: #f6f8fc;
    }

    /* Card brutal */
    .card{
      border-radius: var(--radius-xl);
      border: 1px solid var(--line);
      box-shadow: var(--shadow-soft);
      overflow: hidden;
      background: #fff;
    }
    .card-header{
      background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
      border-bottom: 1px solid var(--line);
      position: relative;
    }
    .card-header:before{
      content:'';
      position:absolute;
      inset:0;
      background: radial-gradient(900px 120px at 10% 0%, rgba(13,110,253,.16), transparent 60%);
      pointer-events:none;
    }
    .card-header h5{
      margin: 0;
      font-weight: 1100;
      color: var(--ink);
      letter-spacing: .2px;
      display:flex;
      align-items:center;
      gap:10px;
    }

    .page-header .breadcrumb{
      background: transparent;
    }

    /* Header title row */
    .header-stack{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }
    .header-left{
      min-width: 260px;
    }
    .header-sub{
      color: var(--muted);
      font-weight: 800;
      font-size: .9rem;
      margin-top: 6px;
    }

    /* Chips */
    .chip{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 12px;
      border-radius: 999px;
      border: 1px solid rgba(15,23,42,.10);
      background: rgba(248,250,252,.95);
      font-weight: 1000;
      color: var(--ink);
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      white-space: nowrap;
    }
    .chip .muted{ color: var(--muted); font-weight: 900; }

    /* KPIs */
    .kpi-wrap{
      display:grid;
      grid-template-columns: repeat(4, minmax(0,1fr));
      gap: 12px;
      margin: 14px 0 16px;
    }
    .kpi{
      border: 1px solid rgba(15,23,42,.10);
      border-radius: var(--radius-lg);
      box-shadow: 0 12px 26px rgba(2,6,23,.07);
      padding: 12px 14px;
      background: #fff;
      position: relative;
      overflow: hidden;
    }
    .kpi:before{
      content:'';
      position:absolute;
      inset:0;
      background: radial-gradient(900px 120px at 10% 0%, rgba(13,110,253,.12), transparent 60%);
      pointer-events:none;
    }
    .kpi .label{
      color: var(--muted);
      font-weight: 1000;
      font-size: .78rem;
      margin-bottom: 6px;
      display:flex;
      align-items:center;
      gap:8px;
      position: relative;
      z-index: 1;
    }
    .kpi .value{
      color: var(--ink);
      font-weight: 1200;
      font-size: 1.2rem;
      letter-spacing: .2px;
      position: relative;
      z-index: 1;
      line-height: 1.15;
    }
    .kpi .hint{
      color: var(--muted);
      font-weight: 800;
      font-size: .76rem;
      margin-top: 6px;
      position: relative;
      z-index: 1;
    }

    /* Table shell */
    .table-shell{
      border-radius: var(--radius-lg);
      border: 1px solid rgba(15,23,42,.10);
      overflow: hidden;
      box-shadow: 0 14px 34px rgba(2,6,23,.08);
      background: #fff;
    }
    .table-responsive{
      margin: 0;
    }

    #dynamictable{
      width: 100% !important;
      margin: 0 !important;
    }
    #dynamictable thead th{
      background: linear-gradient(135deg, rgba(13,110,253,.12), rgba(46,88,168,.08));
      color: var(--ink);
      font-weight: 1200;
      border-bottom: 1px solid rgba(15,23,42,.10) !important;
      vertical-align: middle;
      white-space: nowrap;
      padding-top: 14px;
      padding-bottom: 14px;
    }
    #dynamictable tbody td{
      vertical-align: middle;
      font-weight: 750;
      color: var(--ink);
      border-top: 1px solid rgba(15,23,42,.06);
      background: #fff;
    }
    #dynamictable tbody tr:hover td{
      background: rgba(13,110,253,.03);
    }

    /* Actions button (brutal) */
    .btn-eye{
      border-radius: 14px !important;
      padding: .42rem .6rem !important;
      font-weight: 1000 !important;
      box-shadow: 0 14px 34px rgba(2,6,23,.16);
      background: linear-gradient(135deg, var(--brand-1), var(--brand-2)) !important;
      border: 1px solid rgba(255,255,255,.18) !important;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      min-width: 42px;
    }
    .btn-eye:hover{ transform: translateY(-1px); filter: brightness(1.03); }
    .btn-eye:active{ transform: translateY(0px); }

    /* Money pill */
    .money-pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      border-radius: 999px;
      padding: 6px 10px;
      border: 1px solid rgba(22,163,74,.18);
      background: rgba(22,163,74,.08);
      color: #064e3b;
      font-weight: 1200;
      letter-spacing: .2px;
      white-space: nowrap;
    }

    /* Estado pill */
    .state-pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight: 1100;
      border: 1px solid rgba(15,23,42,.10);
      background: #f8fafc;
      color: #0f172a;
      white-space: nowrap;
    }
    .dot{
      width:10px; height:10px; border-radius:999px;
      background:#94a3b8;
      box-shadow: 0 0 0 3px rgba(148,163,184,.20);
    }
    .dot.ok{ background: var(--success-1); box-shadow: 0 0 0 3px rgba(22,163,74,.20); }
    .dot.warn{ background: var(--warn-1); box-shadow: 0 0 0 3px rgba(245,158,11,.22); }
    .dot.bad{ background: var(--danger-1); box-shadow: 0 0 0 3px rgba(239,68,68,.22); }

    /* Progress pro */
    .progress{
      height: 16px;
      border-radius: 999px;
      background: rgba(15,23,42,.08);
      overflow: hidden;
      box-shadow: inset 0 2px 6px rgba(2,6,23,.10);
      margin-bottom: 0;
    }
    .progress-bar{
      font-weight: 1100;
      font-size: .74rem;
      line-height: 16px;
      border-radius: 999px;
      padding: 0 8px;
      white-space: nowrap;
    }
    .bar-ok{ background: linear-gradient(135deg, var(--success-1), var(--success-2)); }
    .bar-warn{ background: linear-gradient(135deg, var(--warn-1), var(--warn-2)); }
    .bar-bad{ background: linear-gradient(135deg, var(--danger-1), var(--danger-2)); }

    /* Modal pro */
    .modal-content{
      border-radius: 18px;
      border: 1px solid rgba(15,23,42,.10);
      box-shadow: 0 18px 44px rgba(2,6,23,.18);
      overflow: hidden;
    }
    .modal-header{
      background: linear-gradient(135deg, rgba(13,110,253,.12), rgba(46,88,168,.08));
      border-bottom: 1px solid rgba(15,23,42,.10);
    }
    .modal-title{
      font-weight: 1200;
      color: var(--ink);
      letter-spacing: .2px;
    }
    .modal-body{
      color: var(--ink);
      font-weight: 750;
    }

    /* Empty state */
    .empty-state{
      padding: 26px 16px;
      text-align: center;
      color: var(--muted);
      background: linear-gradient(180deg, rgba(13,110,253,.04), #fff);
    }
    .empty-state p{ margin: 10px 0 0; font-weight: 950; }

    /* DataTables tweaks */
    .dataTables_wrapper .dataTables_filter input{
      border-radius: 12px !important;
      border: 1px solid rgba(15,23,42,.14) !important;
      padding: .45rem .7rem !important;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      font-weight: 800;
      outline: none !important;
    }
    .dataTables_wrapper .dataTables_length select{
      border-radius: 12px !important;
      border: 1px solid rgba(15,23,42,.14) !important;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      font-weight: 800;
      outline: none !important;
    }

    @media (max-width: 992px){
      .kpi-wrap{ grid-template-columns: 1fr; }
      .header-stack{ gap: 10px; }
    }
    @media (max-width: 576px){
      #dynamictable thead th,
      #dynamictable tbody td{ white-space: nowrap; }
    }
  </style>

  <!-- [ Main Content ] start -->
  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10"><i data-feather="layers"></i> Detalle Proyectos Secretarías Alcaldía</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Proyectos Secretarías / Seguimiento Proyectos / Detalle Proyectos Secretarías</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <div class="row">
        <div class="col-xl-12 col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="header-stack w-100">
                <div class="header-left">
                  <h5>
                    <i data-feather="folder"></i>
                    Detalle Proyectos por Secretarías<?php echo !empty($nombreMunicipio) ? ' - ' . h($nombreMunicipio) : ''; ?>
                  </h5>
                  <div class="header-sub">
                    Vista de control: proyectos, valores y avance físico/financiero.
                  </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap" style="position:relative; z-index:1;">
                  <span class="chip">
                    <i data-feather="map-pin"></i>
                    <span class="muted">Municipio:</span>
                    <?php echo !empty($nombreMunicipio) ? h($nombreMunicipio) : '—'; ?>
                  </span>
                  <span class="chip">
                    <i data-feather="file-text"></i>
                    <span class="muted">Proyectos:</span>
                    <?php echo (int)$totalProyectos; ?>
                  </span>
                  <span class="chip">
                    <i data-feather="dollar-sign"></i>
                    <span class="muted">Suma:</span>
                    <?php echo '$ ' . number_format((float)$sumaCOP, 0, ',', '.'); ?>
                  </span>
                </div>

                <div class="card-header-right" style="position:relative; z-index:1;">
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
            </div>

            <div class="card-body">

              <!-- KPIs -->
              <div class="kpi-wrap">
                <div class="kpi">
                  <div class="label"><i data-feather="trending-up"></i> Promedio avance físico</div>
                  <div class="value"><?php echo (int)round($promFis); ?>%</div>
                  <div class="hint">Promedio de ejecución de obra</div>
                </div>
                <div class="kpi">
                  <div class="label"><i data-feather="activity"></i> Promedio avance financiero</div>
                  <div class="value"><?php echo (int)round($promFin); ?>%</div>
                  <div class="hint">Promedio de ejecución financiera</div>
                </div>
                <div class="kpi">
                  <div class="label"><i data-feather="shield"></i> Estado general</div>
                  <div class="value"><?php echo ($totalProyectos > 0) ? 'En seguimiento' : 'Sin registros'; ?></div>
                  <div class="hint">Indicador visual para la alcaldía</div>
                </div>
                <div class="kpi">
                  <div class="label"><i data-feather="search"></i> Búsqueda rápida</div>
                  <div class="value">Filtra por nombre/secretaría</div>
                  <div class="hint">Usa el buscador de la tabla</div>
                </div>
              </div>

              <!-- Tabla -->
              <div class="table-shell">
                <div class="table-responsive">
                  <table id="dynamictable" class="table table-hover mb-0">
                    <thead>
                      <tr>
                        <th style="width:90px;">Acciones</th>
                        <th style="width:90px;">Item</th>
                        <th>Municipio</th>
                        <th>Secretaría</th>
                        <th>Nombre Proyecto</th>
                        <th style="width:170px;">Valor Proyecto</th>
                        <th style="width:150px;">Fecha Entrega</th>
                        <th style="width:190px;">Estado</th>
                        <th style="width:220px;">% Ejecución</th>
                        <th style="width:180px;">% Financiero</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $c = count($rows);
                      if ($isvalid && $c > 0) {
                        for ($i = 0; $i < $c; $i++) {

                          $idProyecto = $rows[$i]['id'] ?? '';
                          $municipio  = $rows[$i]['municipio'] ?? '';
                          $secretaria = $rows[$i]['secretaria'] ?? '';

                          $nombreProyectoRaw = (string)($rows[$i]['proyecto'] ?? '');
                          $nombreProyectoSafe = h($nombreProyectoRaw);

                          $corto = mb_strimwidth($nombreProyectoRaw, 0, 60, '...');
                          $cortoSafe = h($corto);

                          $valor = (float)($rows[$i]['valor_proyecto'] ?? 0);
                          $fechaEntrega = $rows[$i]['fecha_entrega'] ?? 'N/A';
                          $estado = $rows[$i]['estado'] ?? 'En Formulación';

                          $pe = (float)($rows[$i]['porcentaje_ejecucion'] ?? 0);
                          $pf = (float)($rows[$i]['porcentaje_financiero'] ?? 0);

                          $peInt = (int)max(0, min(100, round($pe)));
                          $pfInt = (int)max(0, min(100, round($pf)));

                          $barClass = ($peInt >= 70) ? 'bar-ok' : (($peInt >= 35) ? 'bar-warn' : 'bar-bad');

                          $estadoLower = mb_strtolower((string)$estado);
                          $dotClass = 'warn';
                          if (strpos($estadoLower, 'termin') !== false || strpos($estadoLower, 'finaliz') !== false || strpos($estadoLower, 'entreg') !== false || strpos($estadoLower, 'liquid') !== false) $dotClass = 'ok';
                          if (strpos($estadoLower, 'suspend') !== false || strpos($estadoLower, 'desist') !== false) $dotClass = 'bad';

                          $modalId = 'modalProyecto_' . preg_replace('/\D+/', '', (string)$idProyecto);
                      ?>
                          <tr>
                            <td>
                              <button
                                type="button"
                                id="<?php echo h($idProyecto); ?>"
                                title="Ver detalle"
                                onclick="location.href='detalle_proyectos_alcaldias.php?id=<?php echo urlencode((string)$idProyecto); ?>&nombre=<?php echo urlencode($nombreProyectoRaw); ?>'"
                                class="btn btn-sm btn-eye">
                                <i data-feather="eye" width="16" height="16"></i>
                              </button>
                            </td>

                            <td><?php echo h($idProyecto); ?></td>
                            <td><?php echo h($municipio); ?></td>
                            <td><?php echo h($secretaria); ?></td>

                            <td>
                              <span><?php echo $cortoSafe; ?></span>

                              <?php if (mb_strlen($nombreProyectoRaw) > 60): ?>
                                <button class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#<?php echo h($modalId); ?>">
                                  Ver más
                                </button>

                                <div class="modal fade" id="<?php echo h($modalId); ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel_<?php echo h($modalId); ?>" aria-hidden="true">
                                  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                      <div class="modal-header text-center w-100">
                                        <h5 class="modal-title mx-auto" id="modalLabel_<?php echo h($modalId); ?>">Nombre del Proyecto</h5>
                                        <button type="button" class="close position-absolute" style="right: 1rem;" data-dismiss="modal" aria-label="Cerrar">
                                          <span aria-hidden="true">&times;</span>
                                        </button>
                                      </div>
                                      <div class="modal-body text-center">
                                        <div style="white-space: normal; word-wrap: break-word; word-break: break-word; font-size: 1rem;">
                                          <?php echo nl2br($nombreProyectoSafe); ?>
                                        </div>
                                      </div>
                                      <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              <?php endif; ?>
                            </td>

                            <td>
                              <span class="money-pill" data-money="<?php echo h((string)$valor); ?>">
                                <i data-feather="dollar-sign" width="16" height="16"></i>
                                <span class="money-text"><?php echo '$ ' . number_format($valor, 0, ',', '.'); ?></span>
                              </span>
                            </td>

                            <td><?php echo h($fechaEntrega); ?></td>

                            <td>
                              <span class="state-pill">
                                <span class="dot <?php echo h($dotClass); ?>"></span>
                                <?php echo h($estado); ?>
                              </span>
                            </td>

                            <td>
                              <div class="progress" title="<?php echo $peInt; ?>%">
                                <div class="progress-bar <?php echo h($barClass); ?>" style="width: <?php echo $peInt; ?>%">
                                  <?php echo $peInt; ?>%
                                </div>
                              </div>
                            </td>

                            <td class="porcentaje-financiero">
                              <span class="badge bg-primary" style="font-weight:1100;border-radius:999px;padding:7px 10px;">
                                <?php echo $pfInt; ?>%
                              </span>
                            </td>
                          </tr>
                      <?php
                        }
                      } else {
                      ?>
                        <tr>
                          <td colspan="10" class="empty-state">
                            <i class="feather icon-inbox" style="font-size: 28px;"></i>
                            <p class="mt-2">No hay proyectos para esta secretaría</p>
                          </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <!-- /Tabla -->

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
  <?php include './admin/include/generic_dataTables.php'; ?>

  <script>
    // Feather icons + UX DataTables + formato COP seguro
    (function(){
      function formatCOP(n){
        try{
          const x = Math.round(Number(n) || 0);
          return '$ ' + x.toLocaleString('es-CO');
        }catch(e){
          return '$ 0';
        }
      }

      document.addEventListener('DOMContentLoaded', function(){
        if (window.feather) window.feather.replace({ width: 16, height: 16 });

        // placeholder buscador
        setTimeout(function(){
          const input = document.querySelector('.dataTables_filter input');
          if(input && !input.getAttribute('placeholder')){
            input.setAttribute('placeholder', 'Buscar por proyecto, secretaría, estado…');
          }
        }, 450);

        // normaliza money pill (por si vienen decimales desde backend)
        document.querySelectorAll('[data-money]').forEach(function(el){
          const raw = el.getAttribute('data-money');
          const txt = el.querySelector('.money-text');
          if(txt) txt.textContent = formatCOP(raw);
        });
      });
    })();
  </script>

</body>
</html>
