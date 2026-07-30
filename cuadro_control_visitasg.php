<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

/* if (!$view) { require 'permiso_denegado.php'; } */

include './admin/classes/Visitasg.php';
include './admin/classes/Linea.php';
include './admin/classes/Estrategia.php';

// Ambos tipos (Red 1 y Red 2) desde tbl_gestora
$arr = Visitasg::getAll(null);
$isvalid = $arr['output']['valid'] ?? false;
$arr = $arr['output']['response'] ?? [];

$labelsTipo = [
  Visitasg::TIPO_PRIMERA_DAMA => 'Red de Valor Social 1',
  Visitasg::TIPO_ASPAS => 'Red de Valor Social 2',
];

$codigoDepartamento = Util::getDepartamentoPrincipal(); // siempre Santander (68)

// Líneas
$lineas = Linea::getAll(null);
$lineasResponse = $lineas['output']['response'] ?? [];
$optionLineas = "";
foreach ($lineasResponse as $linea) {
  $optionLineas .= "<option value='" . $linea['id'] . "'>" . htmlspecialchars($linea['nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
}

// Estrategias
$estrategias = Estrategia::getAll(null);
$estrategiasResponse = $estrategias['output']['response'] ?? [];
$optionEstrategias = "";
foreach ($estrategiasResponse as $estrategia) {
  $optionEstrategias .= "<option value='" . $estrategia['id'] . "'>" . htmlspecialchars($estrategia['nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cuadro control — Red de Valor Social</title>
  <link href="assets/css/cuadro_control_red_valor_social_gob360.css" rel="stylesheet">
</head>

<body class="gob360-social-control">
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-b-10">Cuadro detalle visitas gestora social</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Gestión Social</a></li>
                <li class="breadcrumb-item"><a href="#!">Cuadro control actividades</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- HERO VISUAL GOB360 -->
      <section class="g360-control-hero" aria-label="Control de actividades de la Red de Valor Social">
        <div class="g360-control-hero__grid">

          <div>
            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-control-hero__logo"
            >
          </div>

          <div>
            <div class="g360-control-hero__eyebrow">
              <i class="feather icon-activity"></i>
              Gestión social territorial
            </div>

            <h1 class="g360-control-hero__title">
              Control de actividades
            </h1>

            <p class="g360-control-hero__description">
              Consulta, filtra, revisa y edita las actividades de la Red de Valor
              Social 1 y 2, incluyendo territorio, población impactada, inversión,
              estrategias, enlaces y evidencias fotográficas.
            </p>

            <div class="g360-control-hero__chips">
              <span class="g360-chip g360-chip--success">
                <i class="feather icon-check-circle"></i>
                Registros consolidados
              </span>

              <span class="g360-chip">
                <i class="feather icon-filter"></i>
                Filtro por red
              </span>

              <span class="g360-chip">
                <i class="feather icon-edit"></i>
                Edición integrada
              </span>
            </div>
          </div>

          <div class="g360-control-hero__visual" aria-hidden="true">
            <div class="g360-mini-card">
              <i class="feather icon-list"></i>
              <span>Registros</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-map-pin"></i>
              <span>Territorio</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-users"></i>
              <span>Impacto</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-image"></i>
              <span>Evidencias</span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-sm-12">
          <div class="card g360-control-card">
            <div class="card-header d-flex align-items-center justify-content-between">
              <div>
                <h5><i class="feather icon-list mr-2"></i>Tabla de actividades</h5>
                <p>Consulta y administra los registros de las dos redes de valor social.</p>
              </div>
            </div>

            <div class="card-body">
              <div class="gs-filter-bar">
                <div class="gs-filter-label"><i class="feather icon-filter"></i> Vista</div>
                <div>
                  <button type="button" class="btn btn-sm gs-filter-btn" data-view="primera_dama">Ver Red de Valor Social 1</button>
                  <button type="button" class="btn btn-sm gs-filter-btn" data-view="aspas">Ver Red de Valor Social 2</button>
                  <button type="button" class="btn btn-sm gs-filter-btn active" data-view="ambos">Red de Valor Social 1 y 2</button>
                </div>
              </div>

              <div class="table-responsive">
                <table id="dynamictable" class="table table-hover table-bordered" style="width:100%">
                  <thead>
                    <tr>
                      <th>Ver</th>
                      <th>Tipo</th>
                      <th>Provincia</th>
                      <th>Municipio</th>
                      <th>Población Impactada</th>
                      <th>Inversión</th>
                      <th>Linea</th>
                      <th>Estrategia</th>
                      <th>Nombre</th>
                      <th>Actividad</th>
                      <th>Fecha</th>
                      <th>Link</th>
                      <th>Imagen</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($isvalid && !empty($arr)): ?>
                      <?php foreach ($arr as $item): ?>
                        <?php
                          $tipoItem = $item['tipo_actividad'] ?? Visitasg::TIPO_PRIMERA_DAMA;
                          $labelTipo = $labelsTipo[$tipoItem] ?? $tipoItem;
                          $badgeClass = ($tipoItem === Visitasg::TIPO_ASPAS) ? 'badge-tipo-2' : 'badge-tipo-1';
                        ?>
                        <tr data-tipo="<?= htmlspecialchars($tipoItem, ENT_QUOTES, 'UTF-8'); ?>">
                          <td style="min-width:88px">
                            <form action="reporte_visitag.php" method="POST" target="_blank" style="display:inline;">
                              <input type="hidden" name="reporte" value="<?= htmlspecialchars($item['id']); ?>">
                              <button type="submit" class="btn btn-sm btn-primary" title="Ver">
                                <i class="feather icon-eye"></i>
                              </button>
                              <button type="button" class="btn btn-sm btn-warning mt-2" title="Editar"
                                onclick="VISITASG.editData(<?= (int)$item['id'] ?>)">
                                <i class="feather icon-edit"></i>
                              </button>
                            </form>
                          </td>
                          <td>
                            <span class="badge-tipo <?= $badgeClass ?>"><?= htmlspecialchars($labelTipo, ENT_QUOTES, 'UTF-8'); ?></span>
                          </td>
                          <td><?= htmlspecialchars($item['provincia'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['municipio'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['poblacion'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['inversion'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['linea_nombre'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['estrategia_nombre'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['campana'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['actividad'] ?? ''); ?></td>
                          <td><?= htmlspecialchars($item['date'] ?? ''); ?></td>
                          <td style="text-align:center; min-width:72px">
                            <?php if (!empty($item['link'])): ?>
                              <button type="button" class="btn btn-sm btn-danger" title="Abrir link"
                                onclick="window.open('<?= htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8'); ?>', '_blank')">
                                <i class="feather icon-external-link"></i>
                              </button>
                            <?php endif; ?>
                          </td>
                          <td style="min-width:92px">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                              <?php if (!empty($item["foto$i"])): ?>
                                <a href="<?= htmlspecialchars($item["foto$i"], ENT_QUOTES, 'UTF-8') ?>" target="_blank" title="Imagen <?= $i ?>" style="margin-right:8px;">
                                  <i class="feather icon-image"></i>
                                </a>
                              <?php endif; ?>
                            <?php endfor; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="13" class="text-center">No hay datos disponibles</td>
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

  <!-- Modal Editar -->
  <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <form id="editForm" class="w-100">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel" style="display:flex; align-items:center; gap:10px;">
              <i class="fas fa-edit"></i> Editar Visita
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body" style="padding: 18px;">
            <input type="hidden" id="id" name="id">
            <input type="hidden" id="date" name="date">
            <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="<?php echo htmlspecialchars($codigoDepartamento, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label for="tipo_actividad"><i class="fas fa-layer-group"></i> Tipo de actividad <span class="text-danger">*</span></label>
                <select class="form-control" id="tipo_actividad" name="tipo_actividad" required>
                  <option value="">Seleccione</option>
                  <option value="primera_dama">Red de Valor Social 1</option>
                  <option value="aspas">Red de Valor Social 2</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="tbl_municipio_id"><i class="fas fa-map-pin"></i> Municipio</label>
                <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" onchange="DEPARTAMENTO.getVeredasByMunicipioId();"></select>
              </div>

              <div class="col-md-6">
                <label for="provincia"><i class="fas fa-map"></i> Provincia</label>
                <select class="form-control" id="provincia" name="provincia">
                  <option value="Seleccione">Seleccione</option>
                  <option value="Soto_Norte">Soto Norte</option>
                  <option value="Guanenta">Guanentá</option>
                  <option value="Garcia_Rovira">García Rovira</option>
                  <option value="Comunera">Comunera</option>
                  <option value="Velez">Velez</option>
                  <option value="Metropolitana">Metropolitana</option>
                  <option value="Yariguíes">Yariguíes</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="poblacion"><i class="fas fa-users"></i> Población Impactada</label>
                <input type="text" class="form-control" id="poblacion" name="poblacion">
              </div>

              <div class="col-md-12">
                <label for="desc_actividad"><i class="fas fa-align-left"></i> Descripción Actividad</label>
                <textarea class="form-control" id="desc_actividad" name="desc_actividad" rows="4"></textarea>
              </div>

              <div class="col-md-6">
                <label for="inversion"><i class="fas fa-dollar-sign"></i> Inversión Estimada</label>
                <input type="text" class="form-control" id="inversion" name="inversion">
              </div>

              <div class="col-md-3">
                <label for="tbl_linea_id"><i class="fas fa-stream"></i> Línea</label>
                <select class="form-control" id="tbl_linea_id" name="tbl_linea_id">
                  <option value="">Seleccione</option>
                  <?php echo $optionLineas; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label for="tbl_estrategia_id"><i class="fas fa-lightbulb"></i> Estrategia</label>
                <select class="form-control" id="tbl_estrategia_id" name="tbl_estrategia_id">
                  <option value="">Seleccione</option>
                  <?php echo $optionEstrategias; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label for="campana"><i class="fas fa-bullhorn"></i> Nombre</label>
                <select class="form-control" id="campana" name="campana">
                  <option value="Seleccione">Seleccione</option>
                  <option value="Niños al estadio">Niños al estadio</option>
                  <option value="Niños al cine">Niños al cine</option>
                  <option value="Niños al teatro">Niños al teatro</option>
                  <option value="Es tiempo de aprender">Es tiempo de aprender</option>
                  <option value="Niños al estadio - Optometría">Niños al estadio - Optometría</option>
                  <option value="Metale mente">Metale mente</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="actividad"><i class="fas fa-tasks"></i> Actividad</label>
                <input type="text" class="form-control" id="actividad" name="actividad">
              </div>

              <div class="col-md-12">
                <label for="link"><i class="fas fa-link"></i> Link Mediático</label>
                <input type="text" class="form-control" id="link" name="link">
              </div>

              <div class="col-12" id="seccion-fotos-actuales" style="display:none;">
                <div class="section-label-muted">
                  <i class="feather icon-image"></i> Fotos actuales
                </div>
                <div class="row g-2" id="grid-fotos-actuales"></div>
              </div>

              <div class="col-12">
                <div class="row g-3">
                  <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="col-md-6">
                      <div class="upload-card">
                        <div class="preview">
                          <img id="preview-foto<?= $i ?>" src="" alt="Foto <?= $i ?>" style="display:none;">
                          <div>
                            <div style="font-weight:900">Editar foto <?= $i ?></div>
                            <div style="font-size:12px; color: rgba(255,255,255,.55);">Sube la imagen y se actualizará el registro</div>
                          </div>
                        </div>
                        <iframe id="ifm<?= $i ?>" name="ifm<?= $i ?>" src="upload.php?foto=foto<?= $i ?>" scrolling="no" frameborder="0"></iframe>
                      </div>
                    </div>
                  <?php endfor; ?>
                </div>
              </div>
            </div>

            <div class="modal-actions">
              <div class="bar">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                  <i class="feather icon-x-circle mr-1"></i>
                  Cerrar
                </button>

                <button type="button" class="btn btn-primary" onclick="VISITASG.saveData();">
                  <i class="feather icon-save mr-1"></i>
                  Guardar cambios
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
  <script src="assets/js/plugins/prism.js"></script>
  <script src="<?php echo Util::versionar('./admin/js/departamentoDama.js'); ?>"></script>
  <script>
    setTimeout(function() {
      DEPARTAMENTO.getMunicipios();
    }, 1000);
  </script>
  <?php include './admin/include/generic_dataTables.php'; ?>
  <script src="<?php echo Util::versionar('./admin/js/cuadro_control_visitasg.js'); ?>"></script>
</body>
</html>
