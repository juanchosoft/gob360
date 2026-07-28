<?php

include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/CompromisoMunicipioAlcalde.php';

function getUrl()
{
    $port = $_SERVER["SERVER_PORT"];
    $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
    $url = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $nameServer,
        $_SERVER['REQUEST_URI']
    );
    $final = str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists !== false) {
        $final = substr($final, 0, $exists);
    }
    return $final;
}

$userType = SessionData::getUserType();
$tiposUsuarioMunicipal = ['Alcalde', 'Auxiliar_Alcalde', 'Secretario_Despacho', 'Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);
$codigo_municipio = $isUsuarioMunicipal ? SessionData::getCodigoMunicipio() : null;

$data = CompromisoMunicipioAlcalde::getVisorGestionDeCompromisoPorAlcaldia($codigo_municipio);
$response = $data['output']['response'] ?? [];

// Ordenar calificación desc
usort($response, function ($a, $b) {
    $valA = isset($a['calificacion_porcentaje']) ? floatval(str_replace('%', '', $a['calificacion_porcentaje'])) : 0;
    $valB = isset($b['calificacion_porcentaje']) ? floatval(str_replace('%', '', $b['calificacion_porcentaje'])) : 0;
    return $valB <=> $valA;
});

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

      --au-border:1px solid rgba(255,255,255,.12);
      --au-border-soft:1px solid rgba(2,6,23,.10);

      --shadow-soft: 0 10px 30px rgba(0,0,0,.25);
      --shadow-mid:  0 18px 45px rgba(0,0,0,.35);

      --ring: 0 0 0 .25rem rgba(46,88,168,.35);

      /* ✅ evita que header fijo tape títulos */
      --safe-top: 96px;

      /* tonos suaves headers estado */
      --tone-warning: rgba(245,158,11,.18);
      --tone-success: rgba(16,185,129,.18);
      --tone-danger:  rgba(239,68,68,.16);

      --bar-warning: rgba(245,158,11,.85);
      --bar-success: rgba(16,185,129,.85);
      --bar-danger:  rgba(239,68,68,.85);

      /* medallas */
      --gold:   rgba(245, 190, 60, .95);
      --silver: rgba(203, 213, 225, .95);
      --bronze: rgba(205, 127, 50, .95);
    }

    /* ✅ Fondo premium (como el gradiente que mostraste) */
    body.dashboard-premium{
      background:
        radial-gradient(900px 360px at 50% 115%, rgba(12, 35, 39, .95) 0%, rgba(12, 35, 39, 0) 55%),
        radial-gradient(700px 320px at 15% 15%, rgba(32,66,127,.35) 0%, rgba(32,66,127,0) 60%),
        radial-gradient(700px 320px at 85% 10%, rgba(46,88,168,.22) 0%, rgba(46,88,168,0) 55%),
        linear-gradient(135deg,
          #0b1221 0%,
          #0a1b24 35%,
          #0c2327 50%,
          #0b1321 75%,
          #0a1121 100%
        ) !important;
      color: rgba(255,255,255,.92);
    }

    html, body{ overflow-x:hidden !important; }

    /* ✅ Corrige solape por header fijo */
    .pcoded-content{
      padding: calc(var(--safe-top) + 16px) 16px 18px !important;
    }
    @media (min-width:768px){
      :root{ --safe-top: 112px; }
      .pcoded-content{ padding: calc(var(--safe-top) + 18px) 24px 24px !important; }
    }
    @media (min-width:1200px){
      :root{ --safe-top: 120px; }
      .pcoded-content{ padding: calc(var(--safe-top) + 22px) 42px 34px !important; }
    }

    /* ===== breadcrumb dark glass ===== */
    .page-header .page-block{
      background: rgba(255,255,255,.06) !important;
      border: var(--au-border) !important;
      border-radius: var(--au-radius-xl) !important;
      box-shadow: var(--shadow-soft) !important;
      padding: 16px 16px;
      backdrop-filter: blur(10px);
    }
    .page-header h5{
      font-weight: 1000 !important;
      color: rgba(255,255,255,.96) !important;
      margin: 0;
    }
    .breadcrumb{
      background: transparent !important;
      padding: 0;
      margin: .35rem 0 0 !important;
    }
    .breadcrumb a,
    .breadcrumb-item,
    .breadcrumb-item a{ color: rgba(255,255,255,.78) !important; }
    .breadcrumb-item.active{ color: rgba(255,255,255,.55) !important; }

    /* ===== Card glass premium ===== */
    .card{
      background: rgba(255,255,255,.06) !important;
      border: var(--au-border) !important;
      border-radius: var(--au-radius-xl) !important;
      box-shadow: var(--shadow-mid) !important;
      overflow:hidden;
      backdrop-filter: blur(10px);
    }
    .card-header{
      background: rgba(255,255,255,.06) !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
      position: relative;
      font-weight: 1000;
    }
    .card-header::before{
      content:"";
      position:absolute;
      top:0; left:0;
      height:4px; width:100%;
      background: linear-gradient(90deg, var(--au-primary), rgba(46,88,168,.35));
    }
    .card-header h5{ color: rgba(255,255,255,.96) !important; }

    /* menu 3 puntos */
    .card-header-right .btn.btn-icon{
      background: rgba(255,255,255,.08) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      color: rgba(255,255,255,.92) !important;
      border-radius: 12px !important;
    }
    .dropdown-menu{
      background: rgba(10,17,33,.96) !important;
      border: 1px solid rgba(255,255,255,.12) !important;
      box-shadow: 0 18px 45px rgba(0,0,0,.45) !important;
    }
    .dropdown-item,
    .dropdown-item a,
    .dropdown-menu a{ color: rgba(255,255,255,.92) !important; }
    .dropdown-item:hover{ background: rgba(255,255,255,.08) !important; }

    /* ===== ✅ TABLA: fondo claro para contraste perfecto ===== */
    .table-wrap{
      background: rgba(255,255,255,.96);
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 18px 45px rgba(0,0,0,.25);
    }

    .table-responsive{ overflow-x:auto; width:100%; }
    .table{
      background: transparent !important;
      margin: 0;
    }

    /* ✅ texto negro total dentro de tabla */
    .table, .table *,
    .table td, .table th{
      color: var(--au-ink) !important;
    }
    .table a, .table a *{
      color: var(--au-ink) !important;
    }
    .table i, .table svg, .table .feather{
      color: var(--au-ink) !important;
      fill: var(--au-ink) !important;
      stroke: var(--au-ink) !important;
    }

    .table thead th{
      position: relative;
      background: linear-gradient(135deg, rgba(32,66,127,.12), rgba(255,255,255,.96)) !important;
      font-weight: 1000;
      border-bottom: 1px solid rgba(2,6,23,.10) !important;
      white-space: nowrap;
      text-align:center;
      vertical-align: middle;
      padding: 14px 10px 12px;
      font-size: .90rem;
    }

    /* headers por estado */
    .th-tramite{
      background: linear-gradient(135deg, var(--tone-warning), rgba(255,255,255,.98)) !important;
    }
    .th-tramite::before{
      content:"";
      position:absolute; top:0; left:0;
      height:4px; width:100%;
      background: var(--bar-warning);
    }
    .th-cumplido{
      background: linear-gradient(135deg, var(--tone-success), rgba(255,255,255,.98)) !important;
    }
    .th-cumplido::before{
      content:"";
      position:absolute; top:0; left:0;
      height:4px; width:100%;
      background: var(--bar-success);
    }
    .th-sincumplir{
      background: linear-gradient(135deg, var(--tone-danger), rgba(255,255,255,.98)) !important;
    }
    .th-sincumplir::before{
      content:"";
      position:absolute; top:0; left:0;
      height:4px; width:100%;
      background: var(--bar-danger);
    }

    /* ✅ TD más pequeños */
    .table tbody td{
      vertical-align: middle;
      border-top: 1px solid rgba(2,6,23,.06) !important;
      font-size: .88rem;
      line-height: 1.25;
      padding: .72rem .65rem;
      background: transparent !important;
    }

    .table-hover tbody tr:hover{
      background: rgba(32,66,127,.06) !important;
    }

    /* entidad */
    .entidad{ font-weight: 900; }

    /* ranking + medallas */
    .rank-badge{
      width: 36px;
      height: 36px;
      border-radius: 999px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      font-weight: 1000;
      background: rgba(32,66,127,.10);
      border: 1px solid rgba(32,66,127,.18);
      color: var(--au-primary) !important;
      font-size: .85rem;
      position: relative;
    }
    .rank-badge.medal{
      color: #0b1221 !important;
      border: 1px solid rgba(2,6,23,.14);
      box-shadow: 0 12px 24px rgba(2,6,23,.12);
    }
    .rank-badge.medal::after{
      content: "★";
      position:absolute;
      bottom: -10px;
      font-size: 12px;
      line-height: 1;
      color: rgba(2,6,23,.55);
      filter: drop-shadow(0 6px 10px rgba(0,0,0,.20));
    }
    .medal-gold{ background: linear-gradient(135deg, var(--gold), rgba(255,255,255,.65)); }
    .medal-silver{ background: linear-gradient(135deg, var(--silver), rgba(255,255,255,.75)); }
    .medal-bronze{ background: linear-gradient(135deg, var(--bronze), rgba(255,255,255,.75)); }

    /* calificación: chip + barra progreso */
    .score-wrap{
      min-width: 190px;
      display:flex;
      flex-direction: column;
      gap: 6px;
      align-items: center;
      justify-content:center;
    }
    .score-chip{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      min-width: 92px;
      padding: .34rem .58rem;
      border-radius: 999px;
      font-weight: 1000;
      letter-spacing: .2px;
      border: 1px solid rgba(2,6,23,.10);
      box-shadow: 0 8px 18px rgba(2,6,23,.10);
      white-space: nowrap;
      font-size: .85rem;
    }
    .score-bar{
      width: 180px;
      height: 10px;
      background: rgba(2,6,23,.10);
      border-radius: 999px;
      overflow: hidden;
      border: 1px solid rgba(2,6,23,.10);
    }
    .score-bar > span{
      display:block;
      height: 100%;
      width: var(--pct, 0%);
      border-radius: 999px;
      background: linear-gradient(90deg, rgba(32,66,127,.35), rgba(32,66,127,.85));
    }

    /* texto de empty */
    .empty-row{
      text-align:center;
      padding:18px !important;
      color: var(--au-muted) !important;
      font-weight: 900;
    }

    @media (max-width: 576px){
      .table thead th{ font-size: .88rem; }
      .table tbody td{ font-size: .84rem; }
      .score-wrap{ min-width: 150px; }
      .score-bar{ width: 150px; }
    }
  </style>
</head>

<body class="dashboard-premium">
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

              <div class="page-header mb-3">
                <div class="page-block">
                  <div class="row align-items-center">
                    <div class="col-md-12">
                      <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
                        <div>
                          <h5 class="m-b-10 mb-0">Visor De Gestión De Cumplimiento Alcalde</h5>
                          <div style="color: rgba(255,255,255,.75); font-size:.9rem; margin-top:6px;">
                            Ranking de entidades por calificación (%), con totales y estados.
                          </div>
                        </div>
                        <?php include './admin/include/btn_back.php'; ?>
                      </div>

                      <ul class="breadcrumb mt-2">
                        <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Mapa visitas</a></li>
                        <li class="breadcrumb-item"><a href="#!">Visor de Gestión de Cumplimiento</a></li>
                      </ul>

                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12">
                  <div class="card">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                      <h5 class="mb-0 text-center w-100">Visor De Gestión De Cumplimiento Alcalde</h5>

                      <div class="card-header-right ml-auto">
                        <div class="btn-group card-option">
                          <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="feather icon-more-horizontal"></i>
                          </button>
                          <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                            <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> Maximizar</span><span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span></a></li>
                            <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> Colapsar</span><span style="display:none"><i class="feather icon-plus"></i> Expandir</span></a></li>
                            <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a></li>
                            <li class="dropdown-item close-card"><a href="#!"><i class="feather icon-trash"></i> Eliminar</a></li>
                          </ul>
                        </div>
                      </div>

                    </div>

                    <div class="card-body">
                      <div class="table-wrap">
                        <div class="table-responsive">
                          <table class="table table-hover mb-0">
                            <thead>
                              <tr>
                                <th>No.</th>
                                <th>ENTIDAD</th>
                                <th>TOTAL COMPR.</th>
                                <th class="th-tramite">EN TRÁMITE</th>
                                <th class="th-cumplido">CUMPLIDO</th>
                                <th class="th-sincumplir">SIN CUMPLIR</th>
                                <th>CALIFICACIÓN</th>
                              </tr>
                            </thead>

                            <tbody>
                              <?php if (is_array($response) && !empty($response)) : ?>
                                <?php
                                $rowNumber = 1;
                                foreach ($response as $item) :
                                  $califBg = htmlspecialchars($item['color_calificacion'] ?? '#e2e8f0', ENT_QUOTES, 'UTF-8');
                                  $califText = '#0f172a';

                                  // ✅ contraste simple: si es un color oscuro -> texto blanco
                                  $darkColors = ['#dc143c', '#0d5fa7', '#132b52', '#20427f'];
                                  if (in_array(strtolower($califBg), $darkColors, true)) {
                                    $califText = '#ffffff';
                                  }

                                  $pctRaw = isset($item['calificacion_porcentaje']) ? str_replace('%', '', (string)$item['calificacion_porcentaje']) : '0';
                                  $pct = floatval($pctRaw);
                                  if ($pct < 0) $pct = 0;
                                  if ($pct > 100) $pct = 100;

                                  // medallas top 3 (UI-only)
                                  $medalClass = '';
                                  if ($rowNumber === 1) $medalClass = 'medal medal-gold';
                                  if ($rowNumber === 2) $medalClass = 'medal medal-silver';
                                  if ($rowNumber === 3) $medalClass = 'medal medal-bronze';
                                ?>
                                  <tr>
                                    <td style="text-align:center;">
                                      <span class="rank-badge <?php echo $medalClass; ?>">
                                        <?php echo $rowNumber; ?>
                                      </span>
                                    </td>

                                    <td class="entidad">
                                      <?php echo htmlspecialchars($item['entidad'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td style="text-align:center; font-weight:900;">
                                      <?php echo htmlspecialchars($item['total_compromisos'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td style="text-align:center; font-weight:900;">
                                      <?php echo htmlspecialchars($item['en_tramite'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td style="text-align:center; font-weight:900;">
                                      <?php echo htmlspecialchars($item['cumplido'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td style="text-align:center; font-weight:900;">
                                      <?php echo htmlspecialchars($item['sin_cumplir'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td style="text-align:center;">
                                      <div class="score-wrap">
                                        <span class="score-chip" style="background: <?php echo $califBg; ?>; color: <?php echo $califText; ?> !important;">
                                          <?php echo htmlspecialchars($item['calificacion_porcentaje'] ?? '0%', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>

                                        <div class="score-bar" style="--pct: <?php echo $pct; ?>%;">
                                          <span></span>
                                        </div>
                                      </div>
                                    </td>
                                  </tr>
                                <?php
                                  $rowNumber++;
                                endforeach; ?>

                              <?php else : ?>
                                <tr>
                                  <td colspan="7" class="empty-row">
                                    No hay datos de compromisos disponibles.
                                  </td>
                                </tr>
                              <?php endif; ?>
                            </tbody>

                          </table>
                        </div>
                      </div>
                    </div><!-- card-body -->
                  </div><!-- card -->
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/footer.php'; ?>
  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script src="assets/js/plugins/prism.js"></script>
  <script src="assets/js/plugins/apexcharts.min.js"></script>

  <?php include './admin/include/generic_dataTables.php'; ?>
</body>
</html>
