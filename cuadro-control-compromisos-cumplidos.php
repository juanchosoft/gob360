<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Provincias.php';

$arrProv = Provincias::getProvinciasByDepartamento('68');
$isvalidProv = $arrProv['output']['valid'] ?? false;
$arrProv = $arrProv['output']['response'] ?? [];
$optionProv = "<option value=''>Seleccione</option>";

if ($isvalidProv && !empty($arrProv)) {
    foreach ($arrProv as $val) {
        $optionProv .= "<option value='" . htmlspecialchars($val['provincia']) . "'>" . htmlspecialchars($val['provincia']) . "</option>";
    }
}

$userType = SessionData::getUserType();
$esSecretarioGobernacion = ($userType === Util::Secretaria_Despacho_Gobernacion() || $userType === Util::Auxiliar_secret_gob());
$secretariaUsuarioId = SessionData::getSecretaria();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<body class="gob360-municipal-control">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <link href="assets/css/cuadro_control_municipios_gob360.css" rel="stylesheet">


  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h5 class="m-b-10 mb-0">Cuadro control municipios</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Registro de compromisos / Cuadro Control municipios</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- HERO VISUAL GOB360 -->
      <section class="g360-control-hero" aria-label="Control municipal GOB360">
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
              <i class="feather icon-map"></i>
              Seguimiento territorial
            </div>

            <h1 class="g360-control-hero__title">
              Control municipal de compromisos
            </h1>

            <p class="g360-control-hero__description">
              Consulta y analiza los compromisos institucionales por secretaría,
              municipio, provincia, componente y tipo de ejecución, conservando
              los filtros, permisos e indicadores existentes.
            </p>

            <div class="g360-control-hero__chips">
              <span class="g360-chip g360-chip--success">
                <i class="feather icon-check-circle"></i>
                Plataforma operativa
              </span>

              <span class="g360-chip">
                <i class="feather icon-map-pin"></i>
                Santander
              </span>

              <span class="g360-chip">
                <i class="feather icon-filter"></i>
                Filtros dinámicos
              </span>
            </div>
          </div>

          <div class="g360-control-hero__visual" aria-hidden="true">
            <div class="g360-mini-card">
              <i class="feather icon-clipboard"></i>
              <span>Compromisos</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-map-pin"></i>
              <span>Municipios</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-bar-chart-2"></i>
              <span>Indicadores</span>
            </div>

            <div class="g360-mini-card">
              <i class="feather icon-search"></i>
              <span>Consulta</span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-12">
          <div class="card saas-card g360-control-card">

            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <h5>Listado de compromisos</h5>
                <p>Consulta territorial e indicadores por secretaría.</p>
              </div>

              <div class="small">
                <i class="feather icon-map-pin mr-1"></i>
                Control municipal
              </div>
            </div>

            <div class="card-body">
              <div class="col-lg-12">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
                      role="tab" aria-controls="home" aria-selected="true" onclick="cargarCompromiso()">
                      Compromisos
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
                      role="tab" aria-controls="profile" aria-selected="false" onclick="indicadores()">
                      Indicadores por secretaría
                    </button>
                  </li>
                </ul>

                <div class="tab-content" id="myTabContent">

                  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                    <div class="filter-panel">
                      <div class="g360-filter-heading">
                        <div class="g360-filter-heading__title">
                          <span class="g360-filter-heading__icon">
                            <i class="feather icon-filter"></i>
                          </span>
                          Filtros de consulta
                        </div>

                        <span class="g360-filter-heading__hint">
                          Los resultados se actualizan con los controles existentes
                        </span>
                      </div>

                      <div class="row g-3">

                        <div class="col-12 col-md-3">
                          <label for="idFiltro">ID del Compromiso</label>
                          <input type="number" name="idFiltro" id="idFiltro" class="form-control"
                            placeholder="Buscar por ID" onkeyup="filtrarTabla()">
                        </div>

                        <div class="col-12 col-md-3">
                          <label for="secretariaIdFiltro">Seleccionar Secretaría</label>
                          <select name="secretariaIdFiltro" id="secretariaIdFiltro" class="form-control"
                            onchange="filtrarTabla()">
                            <option value="">Seleccione</option>
                          </select>
                          <input type="hidden" id="tbl_departamento_id" name="tbl_departamento_id" value="68">
                        </div>

                        <div class="col-12 col-md-3">
                          <label for="municipioFiltro">Seleccionar Municipio</label>
                          <select name="municipioFiltro" id="municipioFiltro" class="form-control"
                            onchange="filtrarTabla()"></select>
                        </div>

                        <div class="col-12 col-md-3">
                          <label for="provinciaFiltro">Seleccionar Provincia</label>
                          <select name="provinciaFiltro" id="provinciaFiltro" class="form-control"
                            onchange="filtrarTabla()">
                            <?php echo $optionProv; ?>
                          </select>
                        </div>

                        <div class="col-12 col-md-4">
                          <label for="componenteFiltro">Componente</label>
                          <select class="form-control" id="componenteFiltro" name="componenteFiltro"
                            onchange="filtrarTabla()">
                            <option value="" selected>Todas</option>
                            <option value="JURÍDICO">JURÍDICO</option>
                            <option value="MEJORAMIENTO SERVICIO DE SALUD">MEJORAMIENTO SERVICIO DE SALUD</option>
                            <option value="INFRAESTRUCTURA HOSPITALARIA">INFRAESTRUCTURA HOSPITALARIA</option>
                            <option value="DOTACIÓN EN SALUD">DOTACIÓN EN SALUD</option>
                            <option value="INFRAESTRUCTURA PARA CULTURA Y TURISMO">INFRAESTRUCTURA PARA CULTURA Y TURISMO</option>
                            <option value="ATENCIÓN POBLACIÓN VULNERABLE">ATENCIÓN POBLACIÓN VULNERABLE</option>
                            <option value="TRANSPORTE ESCOLAR">TRANSPORTE ESCOLAR</option>
                            <option value="INFRAESTRUCTURA EDUCATIVA">INFRAESTRUCTURA EDUCATIVA</option>
                            <option value="VÍAS SECUNDARIAS Y TERCIARIAS">VÍAS SECUNDARIAS Y TERCIARIAS</option>
                            <option value="INFRAESTRUCTURA INSTITUCIONES">INFRAESTRUCTURA INSTITUCIONES</option>
                            <option value="INFRAESTRUCTURA AEROPORTUARIA">INFRAESTRUCTURA AEROPORTUARIA</option>
                            <option value="AGUA POTABLE - ALCANTARILLADO - PTAR">AGUA POTABLE - ALCANTARILLADO - PTAR</option>
                            <option value="PROMOCIÓN DEL TURISMO">PROMOCIÓN DEL TURISMO</option>
                            <option value="MEJORAMIENTO SERVICIO EDUCATIVO">MEJORAMIENTO SERVICIO EDUCATIVO</option>
                            <option value="DOTACIÓN EDUCATIVA">DOTACIÓN EDUCATIVA</option>
                            <option value="PUENTES">PUENTES</option>
                            <option value="FORTALECIMIENTO INSTITUCIONAL">FORTALECIMIENTO INSTITUCIONAL</option>
                            <option value="GESTIÓN DE RIESGO">GESTIÓN DE RIESGO</option>
                            <option value="KIT HERRAMIENTAS">KIT HERRAMIENTAS</option>
                            <option value="PROTECCIÓN MEDIO AMBIENTE">PROTECCIÓN MEDIO AMBIENTE</option>
                            <option value="INSTRUMENTOS MUSICALES">INSTRUMENTOS MUSICALES</option>
                            <option value="MEJORAMIENTO VIVIENDA">MEJORAMIENTO VIVIENDA</option>
                            <option value="ESCENARIOS DEPORTIVOS">ESCENARIOS DEPORTIVOS</option>
                            <option value="TIC">TIC</option>
                            <option value="APOYO AL DEPORTE">APOYO AL DEPORTE</option>
                            <option value="MINERO - ENERGÉTICO">MINERO - ENERGÉTICO</option>
                            <option value="SEGURIDAD Y CONVIVENCIA">SEGURIDAD Y CONVIVENCIA</option>
                            <option value="APOYO AL AGRO">APOYO AL AGRO</option>
                            <option value="ELECTRIFICACIÓN RURAL">ELECTRIFICACIÓN RURAL</option>
                          </select>
                        </div>

                      </div>
                    </div>

                    <div class="card-body table-border-style px-0">
                      <div class="table-responsive tabla-informacion tabla-scroll">
                        <table class="table table-hover mb-0" id="dynamictable">
                          <thead>
                            <tr class="border-1">
                              <th>Item</th>
                              <th>Secretaria</th>
                              <th>Compromiso</th>
                              <th>Compromiso Pact.</th>
                              <th>Consecuencia</th>
                              <th>Respuesta</th>
                              <th>Estado</th>
                              <th>Municipio</th>
                              <th>Provincia</th>
                              <th>Componente</th>
                              <th>Tipo ejec.</th>
                              <th>Imagen</th>
                              <th>Fecha</th>
                              <th>Editar</th>
                              <th>Ver</th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>

                  </div>

                  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="card subcard mt-3">
                      <div class="card-header">
                        <h5 class="mb-0" style="color:rgba(255,255,255,.92); font-weight:1000;">Indicadores por Secretaría</h5>
                      </div>
                      <div class="card-body">
                        <div class="col-sm-12">
                          <div id="indicadoresContainer" class="mt-4 text-center"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div><!-- tab-content -->
              </div>
            </div>

          </div><!-- saas-card -->
        </div>
      </div>

    </div>

    <!-- Modal compromiso -->
    <div class="modal fade" id="modalCompromiso" tabindex="-1" role="dialog" aria-labelledby="modalCompromisoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalle del Compromiso</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding: 20px;">
            <p id="contenidoCompromiso" style="white-space: pre-wrap; margin:0;"></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para adjuntos -->
    <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="modalAdjuntoLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Adjunto</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center" id="contenidoAdjunto" style="padding: 20px;"></div>
        </div>
      </div>
    </div>

    <?php include 'admin/include/footer.php'; ?>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    const esSecretarioGobernacion = <?php echo $esSecretarioGobernacion ? 'true' : 'false'; ?>;
    const secretariaUsuarioId = <?php echo intval($secretariaUsuarioId); ?>;
  </script>

  <script type="text/javascript" src="<?php echo Util::versionar('./admin/js/control-municipio-cumplidos.js'); ?>"></script>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
</body>
</html>
