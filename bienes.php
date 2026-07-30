<?php
include './admin/include/head.php';

require './admin/include/generic_classes.php';
include './admin/classes/Bienes.php';
include './admin/classes/Secretarias.php';
include './admin/classes/Departamento.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());
/* if (!$view) {
    require 'permiso_denegado.php';
} */
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob());
$isAdministrativo = ( intval(Util::getSecretariaAdministrativa()) === intval(SessionData::getSecretaria()));

if(!$isAdmin){
    if (!$isAdministrativo) {
        require 'permiso_denegado.php';
        exit;
    }
}

// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option " . ($val["codigo_departamento"] == Util::getDepartamentoPrincipal() ? "selected" : "") . " value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

// Informacion de Bienes
$arr = Bienes::getAll(null);
$isvalidBienes = $arr['output']['valid'];
$arr = $arr['output']['response'];

// Informacion de los pilares
$arrSecret = Secretarias::getAll(null);
$isvalid = $arrSecret['output']['valid'];
$arrSecret = $arrSecret['output']['response'];
// Mostrar solo la secretaría con id=3 Administrativa
$optionSecretarias = "";
foreach ($arrSecret as $val) {
    if ($val['id'] == Util::getSecretariaAdministrativa()) {
        $optionSecretarias = "<option selected value='" . $val['id'] . "'>" . $val['secretaria'] . "</option>";
        break;
    }
}
?>


<link href="assets/css/gestion_bienes_administrativos_gob360.css" rel="stylesheet">

<body class="gob360-assets-management">

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

  <!-- [ Main Content ] start -->
  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
                <div>
                  <h5 class="m-b-10 mb-1">Gestión de bienes administrativos</h5>
                  <div class="text-muted" style="font-weight:700; font-size:12px;">
                    Registro, inventario, responsables, evidencias y geolocalización institucional
                  </div>
                </div>
                <div class="ml-auto">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>

              <ul class="breadcrumb mt-2">
                <li class="breadcrumb-item">
                  <a href="index.php"><i class="feather icon-home"></i></a>
                </li>
                <li class="breadcrumb-item">
                  <a href="#!">Secretaría Administrativa / Gestión de bienes</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- HERO VISUAL GOB360 -->
      <section class="g360-assets-hero" aria-label="Gestión de bienes administrativos GOB360">
        <div class="g360-assets-hero__grid">

          <div>
            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-assets-hero__logo"
            >
          </div>

          <div>
            <div class="g360-assets-hero__eyebrow">
              <i class="feather icon-archive"></i>
              Gestión administrativa institucional
            </div>

            <h1 class="g360-assets-hero__title">
              Control de bienes administrativos
            </h1>

            <p class="g360-assets-hero__description">
              Registra, ubica y consulta los bienes de la entidad mediante
              información de control, responsable, costo, dependencia,
              evidencias fotográficas y geolocalización.
            </p>

            <div class="g360-assets-hero__chips">
              <span class="g360-chip g360-chip--success">
                <i class="feather icon-check-circle"></i>
                Registro centralizado
              </span>

              <span class="g360-chip">
                <i class="feather icon-map-pin"></i>
                Geolocalización
              </span>

              <span class="g360-chip">
                <i class="feather icon-image"></i>
                Evidencias fotográficas
              </span>
            </div>
          </div>

          <div class="g360-assets-hero__visual" aria-hidden="true">
            <div class="g360-mini-card">
              <i class="feather icon-tag"></i>
              <span>Control</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-dollar-sign"></i>
              <span>Valor</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-user-check"></i>
              <span>Responsable</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-map"></i>
              <span>Ubicación</span>
            </div>
          </div>

        </div>
      </section>

      <!-- Tabs -->
      <ul class="nav nav-tabs g360-assets-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
            role="tab" aria-controls="home" aria-selected="true" onclick="emptyDataForm();">
            <i class="feather icon-plus-circle"></i>
            Registrar bien
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
            role="tab" aria-controls="profile" aria-selected="false">
            <i class="feather icon-list"></i>
            Consultar bienes
          </button>
        </li>
      </ul>

      <div class="tab-content" id="myTabContent">
        <!-- TAB 1 -->
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
          <div class="card mt-3 g360-assets-card g360-assets-form-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <div>
                <h5>Registro de información administrativa</h5>
                <p>Completa la identificación, ubicación, responsable y evidencias del bien.</p>
              </div>

              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                  </ul>
                </div>
              </div>
            </div>

            <div class="card-body m-4">
              <form id="formbienes" role="form" autocomplete="false" enctype="multipart/form-data">
                <input type="hidden" name="op" id="op" />
                <input type="hidden" name="idBienes" id="idBienes" />

                <div class="soft-panel mb-3">
                  <div class="row">
                    <div class="col-12" style="font-weight:900;color:var(--ink);">
                      <i class="feather icon-tag mr-2"></i>Identificación y control
                      <div class="text-muted">Código interno, calcomanía, costo y ubicación institucional.</div>
                    </div>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="codigo_control">Código de Control <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="codigo_control" name="codigo_control" placeholder="Ej: ADM-000123" value="" required>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="calcomania">Calcomanía <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="calcomania" name="calcomania" placeholder="Ej: CAL-98765" value="" required>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="costo_unitario">Costo Unitario <span class="text-danger">*</span></label>
                    <input onKeyPress="return soloNumeros(event);" type="text" class="form-control" id="costo_unitario" name="costo_unitario" placeholder="Ej: 1500000" value="" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                    <select readonly class="form-control ocultar-select" style="width: 100%;" onchange="DEPARTAMENTO.getMunicipios();" id="tbl_departamento_id" name="tbl_departamento_id">
                      <?php echo $optionDep; ?>
                    </select>
                  </div>

                  <div class="form-group col-md-4">
                    <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                    <select class="form-control" style="width: 100%;" onchange="DEPARTAMENTO.getInformacionDeMunicipioByIdMunicipio(this.value);" id="tbl_municipio_id" name="tbl_municipio_id"></select>
                  </div>

                  <div class="form-group col-md-4">
                    <label for="secretaria">Secretaría <span class="text-danger">*</span></label>
                    <select readonly class="form-control" id="secretaria" name="secretaria">
                      <?php echo $optionSecretarias; ?>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="longitud">Longitud <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="longitud" name="longitud" readonly placeholder="Se autocompleta con el municipio" value="">
                  </div>
                  <div class="form-group col-md-4">
                    <label for="latitud">Latitud <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="latitud" name="latitud" readonly placeholder="Se autocompleta con el municipio" value="">
                  </div>

                  <div class="form-group col-md-4 d-flex align-items-end">
                    <button type="button" class="geo-tile" onclick="abrirModal();" title="Abrir geolocalización">
                      <img src="assets/images/geoloca.png" alt="Geolocalización">
                      <div class="text-left">
                        <span>Geolocalizar</span><br>
                        <span class="geo-sub">Mapa • capas • coordenadas</span>
                      </div>
                    </button>
                  </div>
                </div>

                <div class="soft-panel mb-3">
                  <div class="row">
                    <div class="col-12" style="font-weight:900;color:var(--ink);">
                      <i class="feather icon-user-check mr-2"></i>Responsable y descripción
                      <div class="text-muted">Asigna la dependencia, identificación, responsable y detalle del artículo.</div>
                    </div>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="dependencia">Dependencia <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="dependencia" name="dependencia" placeholder="Ej: Archivo, Jurídica, Sistemas..." value="">
                  </div>
                  <div class="form-group col-md-4">
                    <label for="cedula_o_nit">Cédula o Nit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="cedula_o_nit" name="cedula_o_nit" placeholder="Ej: 900123456-7" value="">
                  </div>
                  <div class="form-group col-md-4">
                    <label for="responsable">Responsable <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="responsable" name="responsable" placeholder="Nombre completo" value="">
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-12">
                    <label for="nombre_articulo">Nombre del Artículo <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="nombre_articulo" name="nombre_articulo" placeholder="Describe el artículo de forma clara..." rows="3" required></textarea>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-12">
                    <label for="observacion">Observación</label>
                    <textarea class="form-control" id="observacion" name="observacion" placeholder="Observaciones adicionales (opcional)..." rows="3"></textarea>
                  </div>
                </div>

                <div class="soft-panel mb-3">
                  <div class="row">
                    <div class="col-12" style="font-weight:900;color:var(--ink);">
                      <i class="feather icon-camera mr-2"></i>Evidencias fotográficas
                      <div class="text-muted">Adjunta hasta cuatro imágenes que permitan verificar el estado del bien.</div>
                    </div>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-3">
                    <label for="">Foto 1</label>
                    <div class="controls">
                      <iframe id='ifm1' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="form-group col-md-3">
                    <label for="">Foto 2</label>
                    <div class="controls">
                      <iframe id='ifm2' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="form-group col-md-3">
                    <label for="">Foto 3</label>
                    <div class="controls">
                      <iframe id='ifm3' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                  <div class="form-group col-md-3">
                    <label for="">Foto 4</label>
                    <div class="controls">
                      <iframe id='ifm4' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                    </div>
                  </div>
                </div>

                <div class="form-row pt-3 g360-form-actions">
                  <div class="col text-center">
                    <button type="button" onclick="UTIL.clearForm('formbienes');" class="btn btn-danger mr-2">
                      <i class="feather icon-x-circle"></i>
                      Cancelar
                    </button>

                    <button type="button" id="createBienes" onclick="BIENES.validateData();" class="btn btn-primary">
                      <i class="feather icon-save"></i>
                      Guardar bien
                    </button>
                  </div>
                </div>

              </form>
            </div>
          </div>
        </div>

        <!-- TAB 2 -->
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
          <div class="card mt-3 g360-assets-card g360-assets-list-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <div>
                <h5>Inventario de bienes administrativos</h5>
                <p>Consulta los registros, fotografías y abre cada elemento para su edición.</p>
              </div>

              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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

            <div class="card-body table-border-style">
              <div class="table-responsive tabla-informacion tabla-scroll">
                <table id="dynamictable" class="table table-striped table-bordered" style="width:100%">
                  <thead>
                    <tr class="border-1">
                      <th style="min-width:90px;">Acciones</th>
                      <th>Municipio</th>
                      <th>Secretaría</th>
                      <th>Código de Control</th>
                      <th>Calcomanía</th>
                      <th style="min-width:260px;">Nombre del Artículo</th>
                      <th>Costo Unitario</th>
                      <th>Dependencia</th>
                      <th>Cédula o Nit</th>
                      <th>Responsable</th>
                      <th style="min-width:110px;">Fotos</th>
                    </tr>
                  </thead>
                  <tbody class="list">
                    <?php if ($isvalidBienes && count($arr) > 0): ?>
                      <?php foreach ($arr as $item): ?>
                        <tr>
                          <td>
                            <button type="button" class="btn btn-primary btn-ico" title="Editar"
                              onclick="BIENES.editData(<?= htmlspecialchars($item['id']) ?>)">
                              <i class="feather icon-edit"></i>
                            </button>
                          </td>
                          <td><?= htmlspecialchars($item['nombre_municipio']) ?></td>
                          <td><?= htmlspecialchars($item['nombre_secretaria']) ?></td>
                          <td><?= htmlspecialchars($item['codigo_control']) ?></td>
                          <td><?= htmlspecialchars($item['calcomania']) ?></td>
                          <td><?= htmlspecialchars($item['nombre_articulo']) ?></td>
                          <td><?= htmlspecialchars(number_format($item['costo_unitario'], 2)) ?></td>
                          <td><?= htmlspecialchars($item['dependencia']) ?></td>
                          <td><?= htmlspecialchars($item['cedula_o_nit']) ?></td>
                          <td><?= htmlspecialchars($item['responsable']) ?></td>
                          <td class="photo-links">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                              <?php if (!empty($item["img$i"])): ?>
                                <a href="<?= htmlspecialchars($item["img$i"]) ?>" target="_blank" title="Imagen <?= $i ?>">
                                  <i class="feather icon-image"></i>
                                </a>
                              <?php endif; ?>
                            <?php endfor; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- Modal de geocalizacion de bienes -->
    <div class="card-body">
      <div id="modalGeocalizacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalGeocalizacionTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalGeocalizacionTitle"><i class="feather icon-map-pin mr-2"></i>Geolocalización del bien</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div id="map" style="height: 550px; width: 100%; border-radius: 18px; overflow:hidden; border:1px solid rgba(15,23,42,.10);"></div>
              <div class="controls">
                <label><input type="checkbox" id="trafficLayerToggle"> Capa de Tráfico</label>
                <label><input type="checkbox" id="transitLayerToggle"> Capa de Transporte Público</label>
                <label><input type="checkbox" id="bicycleLayerToggle"> Capa de Bicicleta</label>
                <label><input type="checkbox" id="terrainToggle"> Mostrar Terreno</label>
              </div>
              <div class="coordinates">
                <strong>Latitud:</strong> <span id="lat">N/A</span> &nbsp;|&nbsp;
                <strong>Longitud:</strong> <span id="lng">N/A</span>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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

  <!-- Google Maps JavaScript API -->
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAbqZVEnSpIPF5dKe89pju2rmQaM58wvY0&callback=initMap"></script>

  <script type="text/javascript" src="admin/js/departamento.js"></script>
  <script type="text/javascript" src="admin/js/bienes.js"></script>
  <script type="text/javascript" src="admin/js/geocalizacion_bienes.js"></script>

  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />

  <script>
    setTimeout(function() {
      DEPARTAMENTO.getMunicipios();
    }, 100);
  </script>

  <?php include './admin/include/generic_dataTables.php'; ?>
</body>
</html>
