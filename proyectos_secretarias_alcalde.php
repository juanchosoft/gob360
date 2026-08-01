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

<body class="gob360-municipal-project-detail-page">
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
  <link rel="stylesheet" href="assets/css/detalle_proyectos_secretaria_alcaldia_gob360_premium.css">


  <!-- [ Main Content ] start -->
  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-project-detail-hero" aria-label="Detalle de proyectos municipales GOB360">
        <div class="g360-project-detail-hero__grid">

          <aside class="g360-project-detail-brand">
            <span class="g360-project-detail-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-project-detail-brand__logo"
            >

            <span class="g360-project-detail-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-project-detail-brand__status">
              <span></span>
              Seguimiento de proyectos activo
            </div>
          </aside>

          <div class="g360-project-detail-hero__content">
            <div class="g360-project-detail-hero__top">
              <div>
                <div class="g360-project-detail-hero__eyebrow">
                  <i class="feather icon-activity"></i>
                  Banco de proyectos municipal
                </div>

                <h1 class="g360-project-detail-hero__title">
                  Detalle de Proyectos
                </h1>

                <p class="g360-project-detail-hero__description">
                  Consulta el estado, inversión, fecha de entrega y porcentajes
                  de ejecución física y financiera de los proyectos asociados
                  a la secretaría municipal seleccionada.
                </p>
              </div>

              <div class="g360-project-detail-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="window.location.reload()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar seguimiento
                </button>

                <div class="g360-project-detail-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-project-detail-summary">
              <article>
                <span class="g360-project-detail-summary__icon">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Municipio</small>
                  <strong>
                    <?= !empty($nombreMunicipio) ? h($nombreMunicipio) : 'No identificado' ?>
                  </strong>
                  <p>Alcaldía asociada a la sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-project-detail-summary__icon g360-project-detail-summary__icon--projects">
                  <i class="feather icon-file-text"></i>
                </span>

                <div>
                  <small>Total proyectos</small>
                  <strong><?= number_format((int)$totalProyectos, 0, ',', '.') ?></strong>
                  <p>Registros de la secretaría</p>
                </div>
              </article>

              <article>
                <span class="g360-project-detail-summary__icon g360-project-detail-summary__icon--investment">
                  <i class="feather icon-dollar-sign"></i>
                </span>

                <div>
                  <small>Inversión acumulada</small>
                  <strong>$<?= number_format((float)$sumaCOP, 0, ',', '.') ?></strong>
                  <p>Valor total de los proyectos</p>
                </div>
              </article>

              <article>
                <span class="g360-project-detail-summary__icon g360-project-detail-summary__icon--progress">
                  <i class="feather icon-trending-up"></i>
                </span>

                <div>
                  <small>Avance promedio</small>
                  <strong><?= (int)round($promFis) ?>% / <?= (int)round($promFin) ?>%</strong>
                  <p>Físico / financiero</p>
                </div>
              </article>
            </div>

            <div class="g360-project-detail-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-briefcase"></i>
                Secretaría municipal
              </span>

              <span>
                <i class="feather icon-dollar-sign"></i>
                Valor del proyecto
              </span>

              <span>
                <i class="feather icon-calendar"></i>
                Fecha de entrega
              </span>

              <span>
                <i class="feather icon-activity"></i>
                Avance físico
              </span>

              <span>
                <i class="feather icon-pie-chart"></i>
                Avance financiero
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-xl-12 col-md-12">
          <div class="card g360-project-detail-card">
            <div class="card-header">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-activity"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">
                    Matriz de seguimiento municipal
                  </span>

                  <h5>
                    Proyectos de la secretaría seleccionada
                  </h5>

                  <p>
                    Revisa inversión, estado, entrega y ejecución de cada proyecto.
                  </p>
                </div>
              </div>

              <div class="g360-card-header-actions">
                <span class="g360-record-status">
                  <span></span>
                  <?= number_format((int)$totalProyectos, 0, ',', '.') ?> proyectos
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
                        <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                      </li>

                      <li class="dropdown-item close-card">
                        <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-body">

              <section class="g360-project-detail-tools" aria-label="Herramientas de seguimiento">
                <div class="g360-project-detail-tools__summary">
                  <span class="g360-project-detail-tools__icon">
                    <i class="feather icon-info"></i>
                  </span>

                  <div>
                    <small>Resumen de la selección</small>
                    <strong>
                      <?= number_format((int)$totalProyectos, 0, ',', '.') ?> proyectos ·
                      $<?= number_format((float)$sumaCOP, 0, ',', '.') ?>
                    </strong>

                    <p>
                      Promedio físico <?= (int)round($promFis) ?>% y financiero
                      <?= (int)round($promFin) ?>%.
                    </p>
                  </div>
                </div>

                <div class="g360-project-detail-search">
                  <span class="g360-project-detail-search__icon">
                    <i class="feather icon-search"></i>
                  </span>

                  <div>
                    <label for="customSearch">Búsqueda rápida</label>
                    <input
                      type="search"
                      id="customSearch"
                      class="form-control"
                      placeholder="Buscar proyecto, secretaría, municipio o estado..."
                    >
                  </div>
                </div>
              </section>

              <div class="table-responsive g360-project-detail-table">
                <table id="dynamictable" class="table table-hover mb-0" aria-label="Detalle de proyectos municipales">
                  <thead>
                    <tr>
                      <th style="width:100px;">Detalle</th>
                      <th style="width:90px;">Item</th>
                      <th>Municipio</th>
                      <th>Secretaría</th>
                      <th>Nombre Proyecto</th>
                      <th style="width:180px;">Inversión</th>
                      <th style="width:140px;">Fecha Entrega</th>
                      <th style="width:170px;">Estado</th>
                      <th style="width:210px;">Avance físico</th>
                      <th style="width:170px;">Avance financiero</th>
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
                              <span>Ver</span>
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
                                  <div class="modal-content g360-project-name-modal">
                                    <div class="modal-header">
                                      <div class="g360-modal-heading">
                                        <span class="g360-modal-heading__icon">
                                          <i class="feather icon-file-text"></i>
                                        </span>

                                        <div>
                                          <small>Información completa</small>
                                          <h5 class="modal-title" id="modalLabel_<?php echo h($modalId); ?>">
                                            Nombre del proyecto
                                          </h5>
                                        </div>
                                      </div>

                                      <button
                                        type="button"
                                        class="close"
                                        data-dismiss="modal"
                                        aria-label="Cerrar"
                                      >
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="modal-body">
                                      <div class="g360-project-name-modal__content">
                                        <i class="feather icon-align-left"></i>
                                        <p><?php echo nl2br($nombreProyectoSafe); ?></p>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <div class="g360-modal-footer-message">
                                        <i class="feather icon-info"></i>
                                        Nombre completo registrado en el banco de proyectos.
                                      </div>

                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        Cerrar
                                      </button>
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
                            <span class="g360-financial-badge">
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
          const customSearch = document.getElementById('customSearch');

          if(search && !search.getAttribute('placeholder')){
            search.setAttribute('placeholder', 'Buscar proyecto, secretaría o estado…');
          }

          if(customSearch){
            customSearch.addEventListener('input', function(){
              const value = this.value || '';

              if(window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#dynamictable')){
                $('#dynamictable').DataTable().search(value).draw();
                return;
              }

              if(search){
                search.value = value;
                search.dispatchEvent(new Event('keyup', { bubbles: true }));
              }
            });
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
