<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

$modulo = 'Banco Proyectos Alcaldía Municipal';

include './admin/classes/SecretariasMunicipio.php';

// ================================
// Nombre del municipio del alcalde logueado
// ================================
$nombreMunicipio = '';
$codigoMunicipio = SessionData::getCodigoMunicipio();

if (!empty($codigoMunicipio)) {
  $db = new DbConection();
  $pdo = $db->openConect();

  $queryMun = "SELECT municipio FROM " . $db->getTable('tbl_ciudades') . " WHERE codigo_muncipio = :codigo";
  $stmtMun  = $pdo->prepare($queryMun);
  $stmtMun->execute([':codigo' => $codigoMunicipio]);

  $resMun = $stmtMun->fetch(PDO::FETCH_ASSOC);
  if ($resMun) {
    $nombreMunicipio = (string)$resMun['municipio'];
  }
  $db->closeConect();
}

// ================================
// Secretarías municipales con proyectos
// ================================
$arr = SecretariasMunicipio::getAllProyectos(null);
$isvalid = $arr['output']['valid'] ?? false;
$rows    = $arr['output']['response'] ?? [];

// KPIs rápidos (sin tocar backend)
$totalSecretarias = 0;
$totalProyectosCOP = 0.0;

if ($isvalid && !empty($rows)) {
  foreach ($rows as $r) {
    if (($r['mostrar'] ?? '') === 'si') {
      $totalSecretarias++;
      $totalProyectosCOP += (float)($r['sumaproyectos'] ?? 0);
    }
  }
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
      --success:#16a34a;
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
      font-weight: 900;
      color: var(--ink);
      letter-spacing: .2px;
      display: flex;
      align-items: center;
      gap: 10px;
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
      grid-template-columns: repeat(3, minmax(0,1fr));
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
      font-weight: 800;
      font-size: .82rem;
      margin-bottom: 6px;
    }
    .kpi .value{
      color: var(--ink);
      font-weight: 1000;
      font-size: 1.25rem;
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

    /* Tabla pro + DataTables */
    .table-responsive{
      border-radius: var(--radius-lg);
      border: 1px solid rgba(15,23,42,.10);
      overflow: hidden;
      box-shadow: 0 12px 26px rgba(2,6,23,.06);
      background: #fff;
    }

    table.dataTable,
    #dynamictable{
      margin: 0 !important;
      width: 100% !important;
    }

    #dynamictable thead th{
      background: linear-gradient(135deg, rgba(13,110,253,.10), rgba(46,88,168,.08));
      color: #0f172a;
      font-weight: 1000;
      border-bottom: 1px solid rgba(15,23,42,.10) !important;
      vertical-align: middle;
    }

    #dynamictable tbody td{
      vertical-align: middle;
      font-weight: 700;
      color: #0f172a;
      border-top: 1px solid rgba(15,23,42,.06);
      background: #fff;
    }

    #dynamictable tbody tr{
      transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
    }
    #dynamictable tbody tr:hover{
      background: rgba(13,110,253,.03);
    }

    /* Botón Ver brutal */
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

    /* Badge dinero */
    .money-badge{
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border-radius: 999px;
      padding: 6px 10px;
      border: 1px solid rgba(22,163,74,.18);
      background: rgba(22,163,74,.08);
      color: #064e3b;
      font-weight: 1000;
      letter-spacing: .2px;
      white-space: nowrap;
    }
    .money-badge i{ color: var(--success); }

    /* DataTables (estilito sin romper include) */
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

    /* Mensaje vacío pro */
    .empty-state{
      padding: 26px 16px;
      text-align: center;
      color: var(--muted);
      background: linear-gradient(180deg, rgba(13,110,253,.04), #fff);
    }
    .empty-state i{
      font-size: 34px;
      color: rgba(13,110,253,.55);
    }
    .empty-state p{
      margin: 10px 0 0;
      font-weight: 800;
    }

    /* Ajustes en mobile */
    @media (max-width: 576px){
      #dynamictable thead th:nth-child(3),
      #dynamictable tbody td:nth-child(3){
        white-space: nowrap;
      }
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
                <h5 class="m-b-10">
                  <i data-feather="folder"></i> Proyectos Secretarías Alcaldía
                </h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="index.php"><i class="feather icon-home"></i></a>
                </li>
                <li class="breadcrumb-item">
                  <a href="#!">Proyectos Secretaría / Seguimiento Proyectos Alcaldía</a>
                </li>
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
                  Detalle Proyectos Secretarías<?php echo !empty($nombreMunicipio) ? ' - ' . htmlspecialchars($nombreMunicipio, ENT_QUOTES, 'UTF-8') : ''; ?>
                </h5>
                <div class="subtitle-muted">
                  Consulta rápida por secretaría: ver el detalle y la suma total de proyectos por dependencia.
                </div>
              </div>

              <div class="card-header-right">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card">
                      <a href="#!">
                        <span><i class="feather icon-maximize"></i> maximize</span>
                        <span style="display:none"><i class="feather icon-minimize"></i> Restore</span>
                      </a>
                    </li>
                    <li class="dropdown-item minimize-card">
                      <a href="#!">
                        <span><i class="feather icon-minus"></i> collapse</span>
                        <span style="display:none"><i class="feather icon-plus"></i> expand</span>
                      </a>
                    </li>
                    <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                    <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> remove</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body">

              <!-- KPIs (solo UI) -->
              <div class="kpi-wrap">
                <div class="kpi">
                  <div class="label"><i class="feather icon-map-pin"></i> Municipio</div>
                  <div class="value">
                    <?php echo !empty($nombreMunicipio) ? htmlspecialchars($nombreMunicipio, ENT_QUOTES, 'UTF-8') : '—'; ?>
                  </div>
                  <div class="hint">Alcaldía logueada</div>
                </div>

                <div class="kpi">
                  <div class="label"><i class="feather icon-layers"></i> Secretarías con proyectos</div>
                  <div class="value"><?php echo (int)$totalSecretarias; ?></div>
                  <div class="hint">Filtradas por “mostrar = si”</div>
                </div>

                <div class="kpi">
                  <div class="label"><i class="feather icon-dollar-sign"></i> Total (suma de proyectos)</div>
                  <div class="value">
                    <?php echo '$ ' . number_format((float)$totalProyectosCOP, 0, ',', '.'); ?>
                  </div>
                  <div class="hint">COP (sin decimales)</div>
                </div>
              </div>

              <div class="table-responsive">
                <table id="dynamictable" class="table table-hover mb-0">
                  <thead>
                    <tr>
                      <th style="width:80px;">Ver</th>
                      <th>Secretaría</th>
                      <th style="width:220px;">Suma Proyectos</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($isvalid && !empty($rows)) : ?>
                      <?php foreach ($rows as $item) : ?>
                        <?php if (($item['mostrar'] ?? '') === 'si') : ?>
                          <tr>
                            <td>
                              <form action="proyecto_x_secretaria_alcalde.php" method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['tbl_secretarias_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="secretaria" value="<?php echo htmlspecialchars($item['tbl_secretarias_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-sm btn-eye" title="Ver detalle">
                                  <i class="feather icon-eye"></i>
                                </button>
                              </form>
                            </td>

                            <td><?php echo htmlspecialchars($item['secretaria'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>

                            <td>
                              <span class="money-badge" data-money="<?php echo htmlspecialchars((string)($item['sumaproyectos'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="feather icon-trending-up"></i>
                                <span class="money-text">
                                  <?php
                                    $val = (float)($item['sumaproyectos'] ?? 0);
                                    echo '$ ' . number_format($val, 0, ',', '.');
                                  ?>
                                </span>
                              </span>
                            </td>
                          </tr>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="3" class="empty-state">
                          <i class="feather icon-inbox"></i>
                          <p>No hay proyectos registrados para las secretarías municipales</p>
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

  <!-- Required Js -->
  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <?php include './admin/include/generic_dataTables.php'; ?>

  <script>
    // Feather icons
    document.addEventListener('DOMContentLoaded', function(){
      if (window.feather) {
        window.feather.replace({ width: 16, height: 16 });
      }
    });
  </script>

  <script>
    // DataTables: sin cambiar tu include, solo ajustamos UX
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
        // 🔥 Si tu include ya inicializa DataTables, este bloque NO lo rompe.
        // Solo agrega un placeholder al buscador cuando aparezca.
        setTimeout(function(){
          const search = document.querySelector('.dataTables_filter input');
          if(search && !search.getAttribute('placeholder')){
            search.setAttribute('placeholder', 'Buscar secretaría…');
          }
        }, 400);

        // Normaliza valores (por si vienen con decimales raros)
        document.querySelectorAll('[data-money]').forEach(function(el){
          const raw = el.getAttribute('data-money');
          const moneyText = el.querySelector('.money-text');
          if(moneyText){
            moneyText.textContent = formatCOP(raw);
          }
        });
      });
    })();
  </script>

</body>
</html>
