<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

//Validación
/* if (!$view) {
    require 'permiso_denegado.php';
} */

include './admin/classes/Departamento.php';
include './admin/classes/Provincias.php';
include './admin/classes/Secretarias.php';
include './admin/classes/ComponenteMunicipios.php';
include './admin/classes/SecretariasMunicipios.php';

$codigoMunicipio = SessionData::getCodigoMunicipio();
$componentesArr = ComponenteMunicipios::getComponentesPorMunicipio($codigoMunicipio)['output']['response'] ?? [];
$optionComponentes = '<option value="">Seleccione</option>';
foreach ($componentesArr as $comp) {
    $nombre = is_string($comp) ? $comp : ($comp['nombre_componente'] ?? '');
    if (!empty($nombre)) {
        $optionComponentes .= '<option value="' . htmlspecialchars($nombre) . '">' . htmlspecialchars($nombre) . '</option>';
    }
}

$modulo = 'Registro Visitas';

// Secretarías del municipio del usuario
$optionSec = '<option value="">Seleccione</option>';
if (!empty($codigoMunicipio)) {
    $arrSecMun = SecretariasMunicipios::getByMunicipio(['codigo_municipio' => $codigoMunicipio]);
    $secRows = $arrSecMun['output']['response'] ?? [];
    foreach ($secRows as $s) {
        $optionSec .= '<option value="' . $s['id'] . '">' . htmlspecialchars($s['secretaria']) . '</option>';
    }
}

// Información de Departamentos
$arrDep   = Departamento::getAll(null);
$isvalid  = $arrDep['output']['valid'];
$arrDep   = $arrDep['output']['response'];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
  $optionDep .= "<option value='" . $val['codigo_departamento'] . "'>" . $val['codigo_departamento'] . " - " . $val['departamento'] . "</option>";
}

$totalComponentesMunicipales = count($componentesArr);
$totalSecretariasMunicipales = count($secRows ?? []);
$municipioSesion = !empty($codigoMunicipio)
    ? (string) $codigoMunicipio
    : 'Sin asignar';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/registro_visitas_alcalde_gob360_premium.css">

</head>

<body class="dashboard-body gob360-mayor-visit-page">
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
    <div class="pcoded-content">

      <section class="g360-visit-hero" aria-label="Registro de visitas y compromisos GOB360">
        <div class="g360-visit-hero__grid">

          <aside class="g360-visit-brand">
            <span class="g360-visit-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-visit-brand__logo"
            >

            <span class="g360-visit-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-visit-brand__status">
              <span></span>
              Trazabilidad territorial activa
            </div>
          </aside>

          <div class="g360-visit-hero__content">
            <div class="g360-visit-hero__top">
              <div>
                <div class="g360-visit-hero__eyebrow">
                  <i class="feather icon-map-pin"></i>
                  Gestión territorial municipal
                </div>

                <h1 class="g360-visit-hero__title">
                  Registro de Visitas y Compromisos
                </h1>

                <p class="g360-visit-hero__description">
                  Registra las actividades del alcalde en el territorio,
                  documenta compromisos, asigna responsables y conserva
                  evidencia fotográfica para su posterior seguimiento.
                </p>
              </div>

              <div class="g360-visit-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="window.location.reload()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Limpiar y actualizar
                </button>

                <div class="g360-visit-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-visit-summary">
              <article>
                <span class="g360-visit-summary__icon">
                  <i class="feather icon-calendar"></i>
                </span>

                <div>
                  <small>Fecha de registro</small>
                  <strong><?php echo date('d/m/Y'); ?></strong>
                  <p>Jornada institucional actual</p>
                </div>
              </article>

              <article>
                <span class="g360-visit-summary__icon g360-visit-summary__icon--territory">
                  <i class="feather icon-map"></i>
                </span>

                <div>
                  <small>Municipio asignado</small>
                  <strong><?= htmlspecialchars($municipioSesion, ENT_QUOTES, 'UTF-8') ?></strong>
                  <p>Territorio asociado a la sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-visit-summary__icon g360-visit-summary__icon--secretariat">
                  <i class="feather icon-briefcase"></i>
                </span>

                <div>
                  <small>Secretarías disponibles</small>
                  <strong><?= number_format($totalSecretariasMunicipales, 0, ',', '.') ?></strong>
                  <p>Dependencias del municipio</p>
                </div>
              </article>

              <article>
                <span class="g360-visit-summary__icon g360-visit-summary__icon--components">
                  <i class="feather icon-grid"></i>
                </span>

                <div>
                  <small>Componentes</small>
                  <strong><?= number_format($totalComponentesMunicipales, 0, ',', '.') ?></strong>
                  <p>Clasificaciones municipales activas</p>
                </div>
              </article>
            </div>

            <div class="g360-visit-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-navigation"></i>
                Ubicación territorial
              </span>

              <span>
                <i class="feather icon-users"></i>
                Dependencia responsable
              </span>

              <span>
                <i class="feather icon-check-square"></i>
                Compromisos pactados
              </span>

              <span>
                <i class="feather icon-camera"></i>
                Evidencia fotográfica
              </span>

              <span>
                <i class="feather icon-shield"></i>
                Trazabilidad institucional
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-12">
          <div class="card au-card position-relative au-accent g360-visit-card">
            <div class="card-header">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-edit-3"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">
                    Nuevo registro territorial
                  </span>

                  <h5 class="title mb-0">
                    Información de la visita o compromiso
                  </h5>

                  <p class="sub mb-0">
                    Completa los campos obligatorios y adjunta la evidencia disponible.
                  </p>
                </div>
              </div>

              <div class="g360-card-header-actions">
                <span class="g360-required-status">
                  <span>*</span>
                  Campos obligatorios
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

              <form
                class="needs-validation au-form-grid"
                novalidate
                id="ingresoVisita"
                autocomplete="off"
                enctype="multipart/form-data"
              >
                <input type="hidden" name="filtro" id="filtro" value="vereda">
                <input type="hidden" name="filtroVeredaById" id="filtroVeredaById" value="si">

                <section class="g360-form-section" aria-labelledby="section-basic-title">
                  <div class="g360-form-section__heading">
                    <span class="g360-form-section__icon">
                      <i class="feather icon-calendar"></i>
                    </span>

                    <div>
                      <small>Paso 1</small>
                      <h6 id="section-basic-title">Información del registro</h6>
                      <p>Define la fecha, el tipo de actividad y el departamento.</p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-12 col-md-6 col-xl-4">
                      <label for="date">
                        Fecha
                        <span class="text-danger">*</span>
                      </label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-calendar"></i>
                        </span>

                        <input
                          type="date"
                          class="form-control"
                          id="date"
                          name="date"
                          required
                        >
                      </div>
                    </div>

                    <div class="form-group col-12 col-md-6 col-xl-4">
                      <label for="tipo_registro">
                        Tipo de registro
                        <span class="text-danger">*</span>
                      </label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-tag"></i>
                        </span>

                        <select
                          class="form-control"
                          id="tipo_registro"
                          name="tipo_registro"
                          required
                        >
                          <option value="">Seleccione</option>
                          <option value="Visita">Visita</option>
                          <option value="Compromiso">Compromiso</option>
                        </select>
                      </div>
                    </div>

                    <div class="form-group col-12 col-md-6 col-xl-4">
                      <label for="tbl_departamento_id">
                        Departamento
                        <span class="text-danger">*</span>
                      </label>

                      <select
                        class="form-control ocultar-select"
                        style="width:100%;"
                        onchange="DEPARTAMENTO.getMunicipios();"
                        id="tbl_departamento_id"
                        name="tbl_departamento_id"
                      >
                        <?php echo $optionDep; ?>
                      </select>

                      <div class="g360-fixed-territory">
                        <span class="g360-fixed-territory__icon">
                          <i class="feather icon-map"></i>
                        </span>

                        <div>
                          <small>Departamento fijo</small>
                          <strong>Santander</strong>
                          <span>Código DANE 68</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </section>

                <section class="g360-form-section" aria-labelledby="section-territory-title">
                  <div class="g360-form-section__heading">
                    <span class="g360-form-section__icon g360-form-section__icon--territory">
                      <i class="feather icon-map-pin"></i>
                    </span>

                    <div>
                      <small>Paso 2</small>
                      <h6 id="section-territory-title">Ubicación territorial</h6>
                      <p>Selecciona el municipio y la vereda donde se desarrolló la actividad.</p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-12 col-md-6">
                      <label for="tbl_municipio_id">
                        Municipio
                        <span class="text-danger">*</span>
                      </label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-home"></i>
                        </span>

                        <select
                          class="form-control"
                          style="width:100%;"
                          onchange="DEPARTAMENTO.getVeredasByMunicipioId(); DEPARTAMENTO.getSecretariasMunicipales();"
                          id="tbl_municipio_id"
                          name="tbl_municipio_id"
                          required
                        ></select>
                      </div>
                    </div>

                    <div class="form-group col-12 col-md-6">
                      <label for="tbl_vereda_id">
                        Vereda
                        <span class="text-danger">*</span>
                      </label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-navigation"></i>
                        </span>

                        <select
                          class="form-control"
                          id="tbl_vereda_id"
                          name="tbl_vereda_id"
                          required
                        ></select>
                      </div>
                    </div>
                  </div>
                </section>

                <section class="g360-form-section" aria-labelledby="section-management-title">
                  <div class="g360-form-section__heading">
                    <span class="g360-form-section__icon g360-form-section__icon--management">
                      <i class="feather icon-briefcase"></i>
                    </span>

                    <div>
                      <small>Paso 3</small>
                      <h6 id="section-management-title">Gestión y responsables</h6>
                      <p>Clasifica la actividad y asigna la dependencia encargada.</p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-12 col-md-6 col-xl-4 campo-visita">
                      <label for="tipo_visita">Tipo de visita</label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-users"></i>
                        </span>

                        <select
                          class="form-control"
                          id="tipo_visita"
                          name="tipo_visita"
                        >
                          <option value="">Seleccione</option>
                          <option value="Reunión">Reunión</option>
                          <option value="Ruta 25">Ruta 25</option>
                          <option value="Brigada Civico Social">Brigada Cívico Social</option>
                          <option value="Consejo de Seguridad">Concejo de Seguridad</option>
                          <option value="Concejos y/o Juntas Directivas">Concejos y/o Juntas Directivas</option>
                          <option value="Inauguración de festividades">Inauguración de festividades</option>
                          <option value="Seguimiento de Obras">Seguimiento de Obras</option>
                          <option value="Seguimiento de Planes, Programas y Proyectos">Seguimiento de Planes, Programas y Proyectos</option>
                        </select>
                      </div>
                    </div>

                    <div class="form-group col-12 col-md-6 col-xl-4">
                      <label for="tbl_secretarias_id">
                        Secretaría o dependencia encargada
                      </label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-briefcase"></i>
                        </span>

                        <select
                          class="form-control"
                          id="tbl_secretarias_id"
                          name="tbl_secretarias_id"
                        >
                          <?php echo $optionSec; ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group col-12 col-md-6 col-xl-4 campo-compromiso">
                      <label for="requiere_respuesta">
                        Requiere respuesta
                        <span class="text-danger">*</span>
                      </label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-message-circle"></i>
                        </span>

                        <select
                          class="form-control"
                          id="requiere_respuesta"
                          name="requiere_respuesta"
                        >
                          <option value="">Seleccione</option>
                          <option value="Si">Sí</option>
                          <option value="No">No</option>
                        </select>
                      </div>
                    </div>

                    <div class="form-group col-12 col-md-6 col-xl-4 campo-compromiso">
                      <label for="componente">
                        Componente
                        <span class="text-danger">*</span>
                      </label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-grid"></i>
                        </span>

                        <select
                          class="form-control"
                          id="componente"
                          name="componente"
                        >
                          <?php echo $optionComponentes; ?>
                        </select>
                      </div>
                    </div>

                    <div class="form-group col-12 col-md-6 col-xl-4 campo-compromiso">
                      <label for="tipo_ejecucion">
                        Tipo de ejecución
                        <span class="text-danger">*</span>
                      </label>

                      <div class="g360-input-shell">
                        <span class="g360-input-shell__icon">
                          <i class="feather icon-trending-up"></i>
                        </span>

                        <select
                          class="form-control"
                          id="tipo_ejecucion"
                          name="tipo_ejecucion"
                        >
                          <option value="">Seleccione</option>
                          <option value="GESTIÓN">GESTIÓN</option>
                          <option value="INVERSIÓN">INVERSIÓN</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </section>

                <section class="g360-form-section" aria-labelledby="section-detail-title">
                  <div class="g360-form-section__heading">
                    <span class="g360-form-section__icon g360-form-section__icon--detail">
                      <i class="feather icon-file-text"></i>
                    </span>

                    <div>
                      <small>Paso 4</small>
                      <h6 id="section-detail-title">Descripción y evidencia</h6>
                      <p>Documenta el resultado de la actividad y adjunta una imagen de soporte.</p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-12 col-lg-6 campo-compromiso">
                      <label for="compromisopac">Compromisos pactados</label>

                      <textarea
                        required
                        placeholder="Ingrese el compromiso pactado durante la reunión"
                        class="form-control"
                        id="compromisopac"
                        name="compromisopac"
                        rows="5"
                      ></textarea>
                    </div>

                    <div class="form-group col-12 col-lg-6 campo-visita">
                      <label for="compromisos">Detalles de la visita</label>

                      <textarea
                        required
                        placeholder="Ingrese los detalles y resultados de la visita"
                        class="form-control"
                        id="compromisos"
                        name="compromisos"
                        rows="5"
                      ></textarea>
                    </div>

                    <div class="form-group col-12">
                      <label for="img">Evidencia fotográfica</label>

                      <div class="g360-upload-zone">
                        <div class="g360-upload-zone__icon">
                          <i class="feather icon-camera"></i>
                        </div>

                        <div class="g360-upload-zone__content">
                          <strong>Selecciona una imagen de soporte</strong>
                          <span>Formatos de imagen compatibles con el dispositivo.</span>

                          <input
                            type="file"
                            class="form-control-file"
                            id="img"
                            accept="image/*"
                          >
                        </div>
                      </div>

                      <div id="previewImage" class="mt-3"></div>
                    </div>
                  </div>
                </section>

                <div class="g360-save-bar">
                  <div class="g360-save-bar__message">
                    <i class="feather icon-shield"></i>

                    <span>
                      Verifica la ubicación, el responsable y la descripción
                      antes de guardar el registro.
                    </span>
                  </div>

                  <button
                    type="button"
                    class="btn btn-au-primary"
                    id="guardaVisita"
                  >
                    <i class="feather icon-save"></i>
                    Guardar registro
                  </button>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <!-- [ Main Content ] end -->

  <!-- Required Js -->
  <?php include 'admin/include/gerenic_script.php'; ?>
  <script type="text/javascript" src="admin/js/detalle_visitas_alcalde.js"></script>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script type="text/javascript" src="admin/js/departamento.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script>
    window.userMunicipio = '<?php echo $codigoMunicipio; ?>';

    setTimeout(function() {
      $('#tbl_departamento_id').val('68');
      DEPARTAMENTO.getMunicipios();
      var checkMun = setInterval(function() {
        if ($('#tbl_municipio_id option').length > 1) {
          clearInterval(checkMun);
          if (window.userMunicipio) {
            $('#tbl_municipio_id').val(window.userMunicipio).trigger('change');
          }
        }
      }, 100);
    }, 100);
  </script>

</body>
</html>
