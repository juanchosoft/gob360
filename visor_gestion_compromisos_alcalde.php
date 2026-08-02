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

$totalEntidades = is_array($response) ? count($response) : 0;
$totalCompromisosGlobal = 0;
$totalEnTramiteGlobal = 0;
$totalCumplidosGlobal = 0;
$totalSinCumplirGlobal = 0;
$sumaCalificaciones = 0.0;

foreach ($response as $itemResumen) {
    $totalCompromisosGlobal += (int) ($itemResumen['total_compromisos'] ?? 0);
    $totalEnTramiteGlobal += (int) ($itemResumen['en_tramite'] ?? 0);
    $totalCumplidosGlobal += (int) ($itemResumen['cumplido'] ?? 0);
    $totalSinCumplirGlobal += (int) ($itemResumen['sin_cumplir'] ?? 0);

    $calificacionResumen = isset($itemResumen['calificacion_porcentaje'])
        ? (float) str_replace('%', '', (string) $itemResumen['calificacion_porcentaje'])
        : 0.0;

    $sumaCalificaciones += $calificacionResumen;
}

$promedioCalificacion = $totalEntidades > 0
    ? $sumaCalificaciones / $totalEntidades
    : 0.0;

$porcentajeCumplimientoGlobal = $totalCompromisosGlobal > 0
    ? ($totalCumplidosGlobal / $totalCompromisosGlobal) * 100
    : 0.0;

$porcentajeTramiteGlobal = $totalCompromisosGlobal > 0
    ? ($totalEnTramiteGlobal / $totalCompromisosGlobal) * 100
    : 0.0;

$porcentajeSinCumplirGlobal = $totalCompromisosGlobal > 0
    ? ($totalSinCumplirGlobal / $totalCompromisosGlobal) * 100
    : 0.0;

$entidadLider = $totalEntidades > 0
    ? (string) ($response[0]['entidad'] ?? 'Sin información')
    : 'Sin información';

$calificacionLider = $totalEntidades > 0
    ? (string) ($response[0]['calificacion_porcentaje'] ?? '0%')
    : '0%';

$ambitoVisor = $isUsuarioMunicipal
    ? 'Gestión municipal'
    : 'Cobertura general';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  
  <link rel="stylesheet" href="assets/css/visor_gestion_cumplimiento_alcalde_gob360_premium.css">

</head>

<body class="dashboard-premium gob360-ranking-page">
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

              <section class="g360-ranking-hero" aria-label="Visor de gestión de cumplimiento del alcalde">
                <div class="g360-ranking-hero__grid">

                  <aside class="g360-ranking-brand">
                    <span class="g360-ranking-brand__eyebrow">
                      Plataforma institucional
                    </span>

                    <img
                      src="assets/img/gob360l.png"
                      alt="Logo GOB360"
                      class="g360-ranking-brand__logo"
                    >

                    <span class="g360-ranking-brand__caption">
                      Gestión pública inteligente y territorial
                    </span>

                    <div class="g360-ranking-brand__status">
                      <span></span>
                      Ranking institucional actualizado
                    </div>
                  </aside>

                  <div class="g360-ranking-hero__content">
                    <div class="g360-ranking-hero__top">
                      <div>
                        <div class="g360-ranking-hero__eyebrow">
                          <i class="feather icon-award"></i>
                          Evaluación institucional
                        </div>

                        <h1 class="g360-ranking-hero__title">
                          Visor de Gestión y Cumplimiento
                        </h1>

                        <p class="g360-ranking-hero__description">
                          Compara el desempeño de las entidades responsables,
                          consulta los compromisos por estado y reconoce las
                          dependencias con mayor calificación institucional.
                        </p>
                      </div>

                      <div class="g360-ranking-hero__actions">
                        <button
                          type="button"
                          class="g360-hero-button"
                          onclick="window.location.reload()"
                        >
                          <i class="feather icon-refresh-cw"></i>
                          Actualizar ranking
                        </button>

                        <div class="g360-ranking-back">
                          <?php include './admin/include/btn_back.php'; ?>
                        </div>
                      </div>
                    </div>

                    <div class="g360-ranking-summary">
                      <article>
                        <span class="g360-ranking-summary__icon">
                          <i class="feather icon-briefcase"></i>
                        </span>

                        <div>
                          <small>Entidades evaluadas</small>
                          <strong><?= number_format($totalEntidades, 0, ',', '.') ?></strong>
                          <p><?= htmlspecialchars($ambitoVisor, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                      </article>

                      <article>
                        <span class="g360-ranking-summary__icon g360-ranking-summary__icon--commitments">
                          <i class="feather icon-check-square"></i>
                        </span>

                        <div>
                          <small>Total de compromisos</small>
                          <strong><?= number_format($totalCompromisosGlobal, 0, ',', '.') ?></strong>
                          <p>Registros consolidados</p>
                        </div>
                      </article>

                      <article>
                        <span class="g360-ranking-summary__icon g360-ranking-summary__icon--average">
                          <i class="feather icon-trending-up"></i>
                        </span>

                        <div>
                          <small>Calificación promedio</small>
                          <strong>
                            <?= number_format($promedioCalificacion, 1, ',', '.') ?>%
                          </strong>
                          <p>Promedio entre las entidades</p>
                        </div>
                      </article>

                      <article>
                        <span class="g360-ranking-summary__icon g360-ranking-summary__icon--leader">
                          <i class="feather icon-star"></i>
                        </span>

                        <div>
                          <small>Entidad líder</small>
                          <strong title="<?= htmlspecialchars($entidadLider, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($entidadLider, ENT_QUOTES, 'UTF-8') ?>
                          </strong>
                          <p>Calificación: <?= htmlspecialchars($calificacionLider, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                      </article>
                    </div>

                    <div class="g360-ranking-capabilities" aria-hidden="true">
                      <span><i class="feather icon-list"></i> Ranking descendente</span>
                      <span><i class="feather icon-clock"></i> En trámite</span>
                      <span><i class="feather icon-check-circle"></i> Cumplidos</span>
                      <span><i class="feather icon-alert-circle"></i> Sin cumplir</span>
                      <span><i class="feather icon-bar-chart-2"></i> Calificación porcentual</span>
                    </div>
                  </div>

                </div>
              </section>

              <div class="row">
                <div class="col-sm-12">
                  <div class="card g360-ranking-card">
                    <div class="card-header">
                      <div class="g360-card-heading">
                        <span class="g360-card-heading__icon">
                          <i class="feather icon-award"></i>
                        </span>

                        <div>
                          <span class="g360-card-heading__eyebrow">
                            Clasificación institucional
                          </span>

                          <h5 class="mb-0">
                            Ranking de entidades por cumplimiento
                          </h5>

                          <p>
                            Ordenado de mayor a menor según la calificación
                            porcentual consolidada.
                          </p>
                        </div>
                      </div>

                      <div class="g360-card-header-actions">
                        <span class="g360-live-status">
                          <span></span>
                          <?= number_format($totalEntidades, 0, ',', '.') ?> entidades
                        </span>

                        <div class="card-header-right">
                          <div class="btn-group card-option">
                            <button
                              type="button"
                              class="btn dropdown-toggle btn-icon"
                              data-toggle="dropdown"
                              aria-haspopup="true"
                              aria-expanded="false"
                            >
                              <i class="feather icon-more-horizontal"></i>
                            </button>

                            <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                              <li class="dropdown-item full-card">
                                <a href="#!">
                                  <span><i class="feather icon-maximize"></i> Maximizar</span>
                                  <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                                </a>
                              </li>

                              <li class="dropdown-item minimize-card">
                                <a href="#!">
                                  <span><i class="feather icon-minus"></i> Colapsar</span>
                                  <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                                </a>
                              </li>

                              <li class="dropdown-item reload-card">
                                <a href="#!">
                                  <i class="feather icon-refresh-cw"></i>
                                  Recargar
                                </a>
                              </li>

                              <li class="dropdown-item close-card">
                                <a href="#!">
                                  <i class="feather icon-trash"></i>
                                  Eliminar
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="card-body">

                      <section class="g360-state-overview" aria-label="Resumen global de compromisos">
                        <article class="g360-state-card g360-state-card--process">
                          <span class="g360-state-card__icon">
                            <i class="feather icon-clock"></i>
                          </span>

                          <div>
                            <small>En trámite</small>
                            <strong><?= number_format($totalEnTramiteGlobal, 0, ',', '.') ?></strong>
                            <p><?= number_format($porcentajeTramiteGlobal, 1, ',', '.') ?>% del total</p>
                          </div>
                        </article>

                        <article class="g360-state-card g360-state-card--completed">
                          <span class="g360-state-card__icon">
                            <i class="feather icon-check-circle"></i>
                          </span>

                          <div>
                            <small>Cumplidos</small>
                            <strong><?= number_format($totalCumplidosGlobal, 0, ',', '.') ?></strong>
                            <p><?= number_format($porcentajeCumplimientoGlobal, 1, ',', '.') ?>% del total</p>
                          </div>
                        </article>

                        <article class="g360-state-card g360-state-card--pending">
                          <span class="g360-state-card__icon">
                            <i class="feather icon-alert-triangle"></i>
                          </span>

                          <div>
                            <small>Sin cumplir</small>
                            <strong><?= number_format($totalSinCumplirGlobal, 0, ',', '.') ?></strong>
                            <p><?= number_format($porcentajeSinCumplirGlobal, 1, ',', '.') ?>% del total</p>
                          </div>
                        </article>
                      </section>

                      <section class="g360-ranking-tools" aria-label="Herramientas del ranking">
                        <div class="g360-ranking-search">
                          <span>
                            <i class="feather icon-search"></i>
                          </span>

                          <div>
                            <label for="customSearch">Buscar entidad</label>
                            <input
                              type="search"
                              id="customSearch"
                              class="form-control"
                              placeholder="Escribe el nombre de una entidad..."
                            >
                          </div>
                        </div>

                        <div class="g360-ranking-tools__note">
                          <i class="feather icon-info"></i>
                          <span>
                            Las medallas identifican las tres entidades con
                            mayor calificación del ranking.
                          </span>
                        </div>
                      </section>

                      <div class="table-wrap g360-ranking-table">
                        <div class="table-responsive">
                          <table class="table table-hover mb-0" id="dynamictable" aria-label="Ranking de entidades por cumplimiento">
                            <thead>
                              <tr>
                                <th>Posición</th>
                                <th>Entidad</th>
                                <th>Total compromisos</th>
                                <th class="th-tramite">En trámite</th>
                                <th class="th-cumplido">Cumplidos</th>
                                <th class="th-sincumplir">Sin cumplir</th>
                                <th>Calificación</th>
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

                                    <td class="g360-cell-status g360-cell-status--process" style="text-align:center; font-weight:900;">
                                      <?php echo htmlspecialchars($item['en_tramite'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td class="g360-cell-status g360-cell-status--completed" style="text-align:center; font-weight:900;">
                                      <?php echo htmlspecialchars($item['cumplido'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <td class="g360-cell-status g360-cell-status--pending" style="text-align:center; font-weight:900;">
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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('customSearch');

      if (!searchInput) {
        return;
      }

      searchInput.addEventListener('input', function () {
        const searchValue = this.value || '';

        if (
          window.jQuery
          && $.fn.DataTable
          && $.fn.DataTable.isDataTable('#dynamictable')
        ) {
          $('#dynamictable').DataTable().search(searchValue).draw();
          return;
        }

        const nativeDataTableSearch = document.querySelector(
          '.dataTables_filter input'
        );

        if (nativeDataTableSearch) {
          nativeDataTableSearch.value = searchValue;
          nativeDataTableSearch.dispatchEvent(
            new Event('keyup', { bubbles: true })
          );
        }
      });
    });
  </script>

</body>
</html>
