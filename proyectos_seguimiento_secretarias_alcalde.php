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

<body class="gob360-municipal-project-bank-page">
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
  <link rel="stylesheet" href="assets/css/proyectos_secretarias_alcaldia_gob360_premium.css">


  <!-- [ Main Content ] start -->
  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-project-bank-hero" aria-label="Banco de proyectos municipal GOB360">
        <div class="g360-project-bank-hero__grid">

          <aside class="g360-project-bank-brand">
            <span class="g360-project-bank-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-project-bank-brand__logo"
            >

            <span class="g360-project-bank-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-project-bank-brand__status">
              <span></span>
              Banco de proyectos activo
            </div>
          </aside>

          <div class="g360-project-bank-hero__content">
            <div class="g360-project-bank-hero__top">
              <div>
                <div class="g360-project-bank-hero__eyebrow">
                  <i class="feather icon-folder"></i>
                  Alcaldía municipal
                </div>

                <h1 class="g360-project-bank-hero__title">
                  Proyectos por Secretaría
                </h1>

                <p class="g360-project-bank-hero__description">
                  Consulta el consolidado de proyectos por dependencia municipal,
                  identifica la inversión acumulada y accede al detalle de cada
                  secretaría desde el banco de proyectos de GOB360.
                </p>
              </div>

              <div class="g360-project-bank-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="window.location.reload()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar información
                </button>

                <div class="g360-project-bank-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-project-bank-summary">
              <article>
                <span class="g360-project-bank-summary__icon">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Municipio</small>
                  <strong>
                    <?= !empty($nombreMunicipio) ? htmlspecialchars($nombreMunicipio, ENT_QUOTES, 'UTF-8') : 'No identificado' ?>
                  </strong>
                  <p>Alcaldía asociada a la sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-project-bank-summary__icon g360-project-bank-summary__icon--secretariats">
                  <i class="feather icon-layers"></i>
                </span>

                <div>
                  <small>Secretarías con proyectos</small>
                  <strong><?= number_format((int)$totalSecretarias, 0, ',', '.') ?></strong>
                  <p>Registros habilitados para mostrar</p>
                </div>
              </article>

              <article>
                <span class="g360-project-bank-summary__icon g360-project-bank-summary__icon--investment">
                  <i class="feather icon-dollar-sign"></i>
                </span>

                <div>
                  <small>Inversión consolidada</small>
                  <strong>$<?= number_format((float)$totalProyectosCOP, 0, ',', '.') ?></strong>
                  <p>Suma de proyectos en pesos colombianos</p>
                </div>
              </article>

              <article>
                <span class="g360-project-bank-summary__icon g360-project-bank-summary__icon--detail">
                  <i class="feather icon-eye"></i>
                </span>

                <div>
                  <small>Navegación</small>
                  <strong>Detalle</strong>
                  <p>Acceso por cada secretaría municipal</p>
                </div>
              </article>
            </div>

            <div class="g360-project-bank-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-briefcase"></i>
                Dependencias municipales
              </span>

              <span>
                <i class="feather icon-folder"></i>
                Banco de proyectos
              </span>

              <span>
                <i class="feather icon-dollar-sign"></i>
                Inversión consolidada
              </span>

              <span>
                <i class="feather icon-search"></i>
                Consulta rápida
              </span>

              <span>
                <i class="feather icon-arrow-right-circle"></i>
                Acceso al detalle
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-xl-12 col-md-12">
          <div class="card g360-project-bank-card">
            <div class="card-header">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-briefcase"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">
                    Directorio de inversión municipal
                  </span>

                  <h5>
                    Detalle de proyectos por secretaría
                  </h5>

                  <p>
                    Selecciona una dependencia para consultar sus proyectos,
                    valores y seguimiento detallado.
                  </p>
                </div>
              </div>

              <div class="g360-card-header-actions">
                <span class="g360-record-status">
                  <span></span>
                  <?= number_format((int)$totalSecretarias, 0, ',', '.') ?> dependencias
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

              <section class="g360-project-bank-tools" aria-label="Resumen y búsqueda de proyectos">
                <div class="g360-project-bank-tools__summary">
                  <span class="g360-project-bank-tools__icon">
                    <i class="feather icon-info"></i>
                  </span>

                  <div>
                    <small>Consolidado municipal</small>
                    <strong>
                      <?= number_format((int)$totalSecretarias, 0, ',', '.') ?>
                      secretarías ·
                      $<?= number_format((float)$totalProyectosCOP, 0, ',', '.') ?>
                    </strong>

                    <p>
                      Los valores corresponden únicamente a dependencias marcadas
                      para visualizarse.
                    </p>
                  </div>
                </div>

                <div class="g360-project-bank-search">
                  <span class="g360-project-bank-search__icon">
                    <i class="feather icon-search"></i>
                  </span>

                  <div>
                    <label for="customSearch">Búsqueda rápida</label>
                    <input
                      type="search"
                      id="customSearch"
                      class="form-control"
                      placeholder="Buscar secretaría o valor de proyectos..."
                    >
                  </div>
                </div>
              </section>

              <div class="table-responsive g360-project-bank-table">
                <table id="dynamictable" class="table table-hover mb-0" aria-label="Proyectos por secretaría municipal">
                  <thead>
                    <tr>
                      <th style="width:95px;">Detalle</th>
                      <th>Secretaría</th>
                      <th style="width:240px;">Inversión en proyectos</th>
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
                                  <span>Ver</span>
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
          const customSearch = document.getElementById('customSearch');

          if(search && !search.getAttribute('placeholder')){
            search.setAttribute('placeholder', 'Buscar secretaría…');
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
