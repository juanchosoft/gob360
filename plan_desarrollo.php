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

<link href="assets/css/metas_plan_desarrollo_gob360_v2.css" rel="stylesheet">

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
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
      `;
    }
  });
</script>

<body class="gob360-development-goals">
  <!-- Loader -->
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- HERO GOB360 -->
      <section class="g360-goals-hero" aria-label="Metas del Plan de Desarrollo GOB360">
        <div class="g360-goals-hero__grid">

          <div>
            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-goals-hero__logo"
            >
          </div>

          <div>
            <div class="g360-goals-hero__eyebrow">
              <i class="feather icon-target"></i>
              Planeación y seguimiento
            </div>

            <h1 class="g360-goals-hero__title">
              Metas del Plan de Desarrollo
            </h1>

            <p class="g360-goals-hero__description">
              Consulta las metas institucionales, importa la plantilla oficial
              y actualiza el avance 2025 de cada registro según los permisos y
              filtros asociados al usuario.
            </p>

            <div class="g360-goals-hero__chips">
              <span class="g360-chip g360-chip--success">
                <i class="feather icon-check-circle"></i>
                Seguimiento activo
              </span>

              <span class="g360-chip">
                <i class="feather icon-upload-cloud"></i>
                Carga masiva Excel
              </span>

              <span class="g360-chip">
                <i class="feather icon-edit-3"></i>
                Actualización 2025
              </span>
            </div>
          </div>

          <div class="g360-goals-hero__visual" aria-hidden="true">
            <div class="g360-mini-card">
              <i class="feather icon-target"></i>
              <span>Metas</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-upload-cloud"></i>
              <span>Importar</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-edit"></i>
              <span>Avances</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-eye"></i>
              <span>Consultar</span>
            </div>
          </div>

        </div>
      </section>

      <!-- Mensajes -->
      <div class="row">
        <div class="col-12" id="message-container"></div>
      </div>

      <!-- Upload Excel -->
      <div class="row">
        <div class="col-12 col-xl-12">
          <div class="card my-4 upload-card g360-goals-card">
            <div class="card-header">
              <div>
                <h5>Creación de metas del Plan de Desarrollo</h5>
                <p>Carga la plantilla oficial para registrar metas institucionales.</p>
              </div>
            </div>

            <div class="card-body">
              <div class="g360-upload-panel">
                <div class="g360-upload-intro">
                  <span class="g360-upload-icon">
                    <i class="feather icon-file-text"></i>
                  </span>

                  <div>
                    <h6>Importación institucional de metas</h6>
                    <p>Selecciona el archivo Excel y procesa la información con la estructura oficial.</p>
                  </div>
                </div>

              <?php
              if ($rol_usuario == 'Alcalde' || $rol_usuario == 'Auxiliar' || $rol_usuario === 'SuperAdministrador' || $rol_usuario === 'Gobernador' || $rol_usuario === 'Secretario_Gobernacion') {
              ?>
                <form action="procesar_excel.php" method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
                  <div class="col-md-6">
                    <label for="excelFile" class="form-label">Subir archivo de Excel <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="excelFile" name="excelFile" required />
                    <div class="g360-upload-help">
                      Usa la plantilla oficial para evitar errores en la estructura de columnas.
                    </div>
                  </div>

                  <div class="col-md-6 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                      <i class="feather icon-upload-cloud"></i>
                      Subir y procesar
                    </button>

                    <a href="SharedFiles/plan.xlsx" class="btn btn-secondary px-4" download>
                      <i class="feather icon-download"></i>
                      Descargar plantilla
                    </a>
                  </div>
                </form>
              <?php } else { ?>
                <div class="g360-permission-note">
                  <i class="feather icon-info"></i>
                  <span>Tu rol no tiene permisos para carga masiva. Puedes consultar y actualizar avances donde aplique.</span>
                </div>
              <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="row">
        <div class="col-xl-12 col-md-12">
          <div class="card table-card g360-goals-card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <div>
                <h5>Metas del Plan de Desarrollo</h5>
                <p>Consulta el detalle institucional y actualiza el avance 2025 por registro.</p>
              </div>
              <div class="card-header-right">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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