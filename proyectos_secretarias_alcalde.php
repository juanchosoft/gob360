<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

include './admin/classes/SecretariasMunicipio.php';
$modulo = 'Banco Proyectos Alcaldía';

// ================================
// Obtener nombre del municipio del alcalde logueado
// ================================
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

// ================================
// Información de proyectos por secretaría municipal
// ================================
error_log("proyecto_x_secretaria_alcalde.php - REQUEST recibido: " . json_encode($_REQUEST));

$arr = SecretariasMunicipio::getAllProyectosxSecre($_REQUEST);

error_log("proyecto_x_secretaria_alcalde.php - Respuesta completa: " . json_encode($arr));
error_log("proyecto_x_secretaria_alcalde.php - Valid: " . (($arr['output']['valid'] ?? false) ? 'true' : 'false'));
error_log("proyecto_x_secretaria_alcalde.php - Total proyectos: " . count($arr['output']['response'] ?? []));

$isvalid = $arr['output']['valid'] ?? false;
$rows    = $arr['output']['response'] ?? [];
$arrData = $rows;

// ================================
// KPIs (solo UI, no toca backend)
// ================================
$totalProyectos = 0;
$sumaCOP = 0.0;
$promFis = 0.0;
$promFin = 0.0;
$cntFis = 0;
$cntFin = 0;

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

function h($v){
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
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
      --radius-xl: 18px;
      --radius-lg: 14px;
      --radius-md: 12px;

      --shadow-soft: 0 10px 28px rgba(2, 6, 23, .10);
      --shadow-mid:  0 14px 34px rgba(2, 6, 23, .12);

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
    }

    /* Card pro */
    .card{
      border-radius: var(--radius-xl);
      border: 1px solid var(--line);
      box-shadow: var(--shadow-soft);
      overflow: hidden;
    }
    .card-header{
      background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
      border-bottom: 1px solid var(--line);
    }
    .card-header h5{
      margin: 0;
      font-weight: 1000;
      color: var(--ink);
      letter-spacing: .2px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .subtitle-muted{
      color: var(--muted);
      font-weight: 700;
      font-size: .9rem;
      margin-top: 4px;
    }

    /* KPIs */
    .kpi-wrap{
      display: grid;
      grid-template-columns: repeat(4, minmax(0,1fr));
      gap: 12px;
      margin-bottom: 14px;
    }
    .kpi{
      border: 1px solid rgba(15,23,42,.10);
      border-radius: var(--radius-lg);
      box-shadow: 0 10px 24px rgba(2,6,23,.06);
      padding: 12px 14px;
      background: #fff;
      position: relative;
      overflow: hidden;
    }
    .kpi:before{
      content:'';
      position:absolute;
      inset:0;
      background: radial-gradient(900px 120px at 10% 0%, rgba(13,110,253,.14), transparent 60%);
      pointer-events:none;
    }
    .kpi .label{
      color: var(--muted);
      font-weight: 900;
      font-size: .8rem;
      margin-bottom: 6px;
      display:flex;
      align-items:center;
      gap:8px;
    }
    .kpi .value{
      color: var(--ink);
      font-weight: 1100;
      font-size: 1.22rem;
      letter-spacing: .2px;
      line-height: 1.15;
    }
    .kpi .hint{
      color: var(--muted);
      font-weight: 700;
      font-size: .78rem;
      margin-top: 6px;
    }
    @media (max-width: 992px){
      .kpi-wrap{ grid-template-columns: 1fr; }
    }

    /* Tabla pro */
    .table-responsive{
      border-radius: var(--radius-lg);
      border: 1px solid rgba(15,23,42,.10);
      overflow: hidden;
      box-shadow: 0 12px 26px rgba(2,6,23,.06);
      background: #fff;
    }
    #dynamictable{
      width: 100% !important;
      margin: 0 !important;
    }
    #dynamictable thead th{
      background: linear-gradient(135deg, rgba(13,110,253,.10), rgba(46,88,168,.08));
      color: #0f172a;
      font-weight: 1100;
      border-bottom: 1px solid rgba(15,23,42,.10) !important;
      vertical-align: middle;
      white-space: nowrap;
    }
    #dynamictable tbody td{
      vertical-align: middle;
      font-weight: 700;
      color: #0f172a;
      border-top: 1px solid rgba(15,23,42,.06);
      background: #fff;
    }
    #dynamictable tbody tr:hover{
      background: rgba(13,110,253,.03);
    }

    /* Botón acciones */
    .btn-eye{
      border-radius: 12px !important;
      padding: .35rem .55rem !important;
      font-weight: 900 !important;
      box-shadow: 0 12px 26px rgba(2,6,23,.14);
      background: linear-gradient(135deg, var(--brand-1), var(--brand-2)) !important;
      border: 1px solid rgba(255,255,255,.18) !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      min-width: 42px;
    }
    .btn-eye:hover{ filter: brightness(1.03); transform: translateY(-1px); }
    .btn-eye:active{ transform: translateY(0px); }

    /* Money pill */
    .money-badge{
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border-radius: 999px;
      padding: 6px 10px;
      border: 1px solid rgba(22,163,74,.18);
      background: rgba(22,163,74,.08);
      color: #064e3b;
      font-weight: 1100;
      letter-spacing: .2px;
      white-space: nowrap;
    }
    .money-badge i{ color: var(--success-1); }

    /* Estado badge */
    .state-pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:6px 10px;
      border-radius:999px;
      font-weight:1000;
      border:1px solid rgba(15,23,42,.10);
      background:#f8fafc;
      color:#0f172a;
      white-space:nowrap;
    }
    .dot{
      width:10px;height:10px;border-radius:999px;
      background:#94a3b8;
      box-shadow: 0 0 0 3px rgba(148,163,184,.20);
    }
    .dot.ok{ background: var(--success-1); box-shadow:0 0 0 3px rgba(22,163,74,.20); }
    .dot.warn{ background: var(--warn-1); box-shadow:0 0 0 3px rgba(245,158,11,.22); }
    .dot.bad{ background: var(--danger-1); box-shadow:0 0 0 3px rgba(239,68,68,.22); }

    /* Progress pro */
    .progress{
      height: 16px;
      border-radius: 999px;
      background: rgba(15,23,42,.08);
      overflow: hidden;
      box-shadow: inset 0 2px 6px rgba(2,6,23,.10);
    }
    .progress-bar{
      font-weight: 1000;
      font-size: .74rem;
      line-height: 16px;
      border-radius: 999px;
      padding-left: 6px;
      padding-right: 6px;
      white-space: nowrap;
    }
    .bar-ok{ background: linear-gradient(135deg, var(--success-1), var(--success-2)); }
    .bar-warn{ background: linear-gradient(135deg, var(--warn-1), var(--warn-2)); }
    .bar-bad{ background: linear-gradient(135deg, var(--danger-1), var(--danger-2)); }

    /* DataTables tweaks */
    .dataTables_wrapper .dataTables_filter input{
      border-radius: 12px !important;
      border: 1px solid rgba(15,23,42,.14) !important;
      padding: .45rem .7rem !important;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      font-weight: 700;
      outline: none !important;
    }
    .dataTables_wrapper .dataTables_length select{
      border-radius: 12px !important;
      border: 1px solid rgba(15,23,42,.14) !important;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      font-weight: 700;
      outline: none !important;
    }

    /* Modal */
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
      font-weight: 1100;
      color: var(--ink);
      letter-spacing: .2px;
    }
    .modal-body{
      color: var(--ink);
      font-weight: 700;
    }

    /* Empty state */
    .empty-state{
      padding: 26px 16px;
      text-align: center;
      color: var(--muted);
      background: linear-gradient(180deg, rgba(13,110,253,.04), #fff);
    }
    .empty-state i{ font-size: 34px; color: rgba(13,110,253,.55); }
    .empty-state p{ margin: 10px 0 0; font-weight: 900; }

    @media (max-width: 576px){
      .card-body{ padding: 14px !important; }
      #dynamictable thead th, #dynamictable tbody td{ white-space: nowrap; }
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
                <h5 class="m-b-10">Detalle Proyectos Secretarías Alcaldía</h5>
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
              <div class="w-100">
                <h5>
                  <i data-feather="layers"></i>
                  Detalle Proyectos por Secretarías<?php echo !empty($nombreMunicipio) ? ' - ' . h($nombreMunicipio) : ''; ?>
                </h5>
                <div class="subtitle-muted">
                  Lista completa de proyectos por secretaría, con avance físico y financiero.
                </div>
              </div>

              <div class="card-header-right">
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

              <!-- KPIs -->
              <div class="kpi-wrap">
                <div class="kpi">
                  <div class="label"><i data-feather="map-pin"></i> Municipio</div>
                  <div class="value"><?php echo !empty($nombreMunicipio) ? h($nombreMunicipio) : '—'; ?></div>
                  <div class="hint">Sesión actual</div>
                </div>

                <div class="kpi">
                  <div class="label"><i data-feather="file-text"></i> Proyectos</div>
                  <div class="value"><?php echo (int)$totalProyectos; ?></div>
                  <div class="hint">Total listados</div>
                </div>

                <div class="kpi">
                  <div class="label"><i data-feather="dollar-sign"></i> Suma valores</div>
                  <div class="value"><?php echo '$ ' . number_format((float)$sumaCOP, 0, ',', '.'); ?></div>
                  <div class="hint">COP (sin decimales)</div>
                </div>

                <div class="kpi">
                  <div class="label"><i data-feather="trending-up"></i> Prom. avance</div>
                  <div class="value"><?php echo (int)round($promFis); ?>% / <?php echo (int)round($promFin); ?>%</div>
                  <div class="hint">Físico / Financiero</div>
                </div>
              </div>

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
                      <th style="width:140px;">Fecha Entrega</th>
                      <th style="width:170px;">Estado</th>
                      <th style="width:210px;">% Ejecución</th>
                      <th style="width:170px;">% Financiero</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($isvalid && !empty($rows)) : ?>
                      <?php foreach ($rows as $r) : ?>
                        <?php
                          $idProyecto = $r['id'] ?? '';
                          $nombreProyecto = (string)($r['proyecto'] ?? '');
                          $nombreProyectoSafe = h($nombreProyecto);

                          $corto = mb_strimwidth($nombreProyecto, 0, 60, '...');
                          $cortoSafe = h($corto);

                          $valor = (float)($r['valor_proyecto'] ?? 0);
                          $fechaEntrega = $r['fecha_entrega'] ?? 'N/A';
                          $estado = $r['estado'] ?? 'En Formulación';

                          $pe = (float)($r['porcentaje_ejecucion'] ?? 0);
                          $pf = (float)($r['porcentaje_financiero'] ?? 0);

                          $peInt = (int)max(0, min(100, round($pe)));
                          $pfInt = (int)max(0, min(100, round($pf)));

                          // Color barra según % físico
                          $barClass = ($peInt >= 70) ? 'bar-ok' : (($peInt >= 35) ? 'bar-warn' : 'bar-bad');

                          // Punto estado según texto (heurística UI)
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
                              onclick="location.href='detalle_proyectos_alcaldias.php?id=<?php echo urlencode((string)$idProyecto); ?>&nombre=<?php echo urlencode($nombreProyecto); ?>'"
                              class="btn btn-sm btn-eye">
                              <i data-feather="eye" width="16" height="16"></i>
                            </button>
                          </td>

                          <td><?php echo h($idProyecto); ?></td>
                          <td><?php echo h($r['municipio'] ?? ''); ?></td>
                          <td><?php echo h($r['secretaria'] ?? ''); ?></td>

                          <td>
                            <span><?php echo $cortoSafe; ?></span>

                            <?php if (mb_strlen($nombreProyecto) > 60): ?>
                              <button class="btn btn-sm btn-link p-0" data-toggle="modal" data-target="#<?php echo h($modalId); ?>">
                                Ver más
                              </button>

                              <!-- Modal -->
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
                            <span class="money-badge" data-money="<?php echo h((string)$valor); ?>">
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

                          <td>
                            <span class="badge bg-primary" style="font-weight:1000;border-radius:999px;padding:7px 10px;">
                              <?php echo $pfInt; ?>%
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="10" class="empty-state">
                          <i data-feather="inbox"></i>
                          <p>No hay proyectos para esta secretaría</p>
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

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
    // Feather icons
    document.addEventListener('DOMContentLoaded', function(){
      if (window.feather) window.feather.replace({ width: 16, height: 16 });
    });
  </script>

  <script>
    // DataTables UX: placeholder del buscador + formato COP seguro
    (function(){
      function formatCOP(num){
        try{
          const n = Math.round(Number(num) || 0);
          return '$ ' + n.toLocaleString('es-CO');
        }catch(e){
          return '$ 0';
        }
      }

      document.addEventListener('DOMContentLoaded', function(){
        setTimeout(function(){
          const search = document.querySelector('.dataTables_filter input');
          if(search && !search.getAttribute('placeholder')){
            search.setAttribute('placeholder', 'Buscar proyecto, secretaría o estado…');
          }
        }, 450);

        document.querySelectorAll('[data-money]').forEach(function(el){
          const raw = el.getAttribute('data-money');
          const moneyText = el.querySelector('.money-text');
          if(moneyText) moneyText.textContent = formatCOP(raw);
        });
      });
    })();
  </script>

</body>
</html>
