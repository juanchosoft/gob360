<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/SecretariasMunicipios.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Departamento.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

$userType = SessionData::getUserType();
$isAdmin  = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

// Tipos municipal
$tiposUsuarioMunicipal = ['Alcalde','Auxiliar_Alcalde','Secretario_Despacho','Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);

if (!$isAdmin && !$isUsuarioMunicipal) {
  require 'permiso_denegado.php';
  exit;
}

// Municipio usuario
$municipioUsuario = '';
$codigoDepartamentoUsuario = '';
if ($isUsuarioMunicipal) {
  $municipioUsuario = SessionData::getCodigoMunicipio();
  $codigoDepartamentoUsuario = Util::getDepartamentoPrincipal();
}

// Departamentos
$arrDep  = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep  = $arrDep['output']['response'];

$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
  $optionDep .= "<option value='".$val['codigo_departamento']."'>".$val['codigo_departamento']." - ".$val['departamento']."</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  
  <link rel="stylesheet" href="assets/css/secretarias_municipales_gob360_premium.css">
</head>

<body class="gob360-municipal-secretariats-page">
  <!-- Preloader -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-municipal-secretariats-hero" aria-label="Secretarías municipales GOB360">
        <div class="g360-municipal-secretariats-hero__grid">

          <aside class="g360-municipal-secretariats-brand">
            <span class="g360-municipal-secretariats-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-municipal-secretariats-brand__logo"
            >

            <span class="g360-municipal-secretariats-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-municipal-secretariats-brand__status">
              <span></span>
              Articulación municipal activa
            </div>
          </aside>

          <div class="g360-municipal-secretariats-hero__content">
            <div class="g360-municipal-secretariats-hero__top">
              <div>
                <div class="g360-municipal-secretariats-hero__eyebrow">
                  <i class="feather icon-home"></i>
                  Configuración territorial
                </div>

                <h1 class="g360-municipal-secretariats-hero__title">
                  Secretarías Municipales
                </h1>

                <p class="g360-municipal-secretariats-hero__description">
                  Administra las dependencias municipales, sus responsables,
                  correos institucionales y estado de habilitación para fortalecer
                  la articulación entre alcaldías y GOB360.
                </p>
              </div>

              <div class="g360-municipal-secretariats-hero__actions">
                <?php if ($create): ?>
                  <button
                    type="button"
                    class="g360-hero-button g360-hero-button--primary"
                    id="btnNuevaSecretariaHero"
                    onclick="abrirSecretariaMunicipalDesdeHero()"
                  >
                    <i class="feather icon-plus-circle"></i>
                    Nueva secretaría
                  </button>
                <?php endif; ?>

                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="window.location.reload()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar
                </button>

                <div class="g360-municipal-secretariats-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-municipal-secretariats-summary">
              <article>
                <span class="g360-municipal-secretariats-summary__icon">
                  <i class="feather icon-home"></i>
                </span>

                <div>
                  <small>Ámbito</small>
                  <strong>Municipal</strong>
                  <p>Dependencias de alcaldías</p>
                </div>
              </article>

              <article>
                <span class="g360-municipal-secretariats-summary__icon g360-municipal-secretariats-summary__icon--territory">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Territorio activo</small>
                  <strong>
                    <?= $isUsuarioMunicipal && $municipioUsuario !== '' ? htmlspecialchars((string)$municipioUsuario, ENT_QUOTES, 'UTF-8') : 'Todos' ?>
                  </strong>
                  <p><?= $isUsuarioMunicipal ? 'Municipio asignado al usuario' : 'Cobertura administrativa' ?></p>
                </div>
              </article>

              <article>
                <span class="g360-municipal-secretariats-summary__icon g360-municipal-secretariats-summary__icon--create">
                  <i class="feather icon-plus-square"></i>
                </span>

                <div>
                  <small>Creación</small>
                  <strong><?= $create ? 'Habilitada' : 'Restringida' ?></strong>
                  <p>Según permisos de la sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-municipal-secretariats-summary__icon g360-municipal-secretariats-summary__icon--profile">
                  <i class="feather icon-shield"></i>
                </span>

                <div>
                  <small>Perfil activo</small>
                  <strong><?= $isAdmin ? 'Administrador' : 'Municipal' ?></strong>
                  <p><?= htmlspecialchars((string)$userType, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </article>
            </div>

            <div class="g360-municipal-secretariats-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-map"></i>
                Departamento y municipio
              </span>

              <span>
                <i class="feather icon-briefcase"></i>
                Dependencias
              </span>

              <span>
                <i class="feather icon-user"></i>
                Responsables
              </span>

              <span>
                <i class="feather icon-mail"></i>
                Correos institucionales
              </span>

              <span>
                <i class="feather icon-check-circle"></i>
                Estado de habilitación
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="card g360-municipal-secretariats-card">
        <div class="card-header">
          <div class="g360-card-heading">
            <span class="g360-card-heading__icon">
              <i class="feather icon-list"></i>
            </span>

            <div>
              <span class="g360-card-heading__eyebrow">Directorio territorial</span>
              <h5 class="mb-0">Listado de secretarías municipales</h5>
              <p>
                Consulta municipio, dependencia, responsable, correo y estado
                de cada registro institucional.
              </p>
            </div>
          </div>

          <div class="g360-card-header-actions">
            <?php if ($create): ?>
              <button
                type="button"
                class="btn btn-primary"
                id="btnNuevaSecretaria"
                data-toggle="modal"
                data-target="#newModalSecretaria"
              >
                <i class="feather icon-plus"></i>
                Nueva secretaría
              </button>
            <?php endif; ?>

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

        <div class="card-body table-border-style">

          <div class="g360-municipal-secretariats-tools">
            <div class="g360-municipal-secretariats-search">
              <span class="g360-municipal-secretariats-search__icon">
                <i class="feather icon-search"></i>
              </span>

              <div>
                <label for="customSearch">Búsqueda rápida</label>
                <input
                  type="search"
                  id="customSearch"
                  class="form-control"
                  placeholder="Buscar municipio, secretaría, responsable o correo..."
                >
              </div>
            </div>

            <div class="g360-municipal-secretariats-tools__info">
              <i class="feather icon-info"></i>

              <span>
                Los usuarios municipales visualizan y administran la información
                correspondiente a su territorio asignado.
              </span>
            </div>
          </div>

          <div class="table-responsive tabla-informacion tabla-scroll g360-municipal-secretariats-table">
            <table class="table table-hover mb-0" id="dynamictable" aria-label="Directorio de secretarías municipales">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Editar</th>
                  <th>Municipio</th>
                  <th>Secretaría</th>
                  <th>Secretario</th>
                  <th>Correo</th>
                  <th>Habilitado</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>

      <!-- ✅ MODAL -->
      <div class="modal fade" id="newModalSecretaria" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
          <div class="modal-content g360-municipal-secretariat-modal">

            <div class="modal-header">
              <div class="g360-modal-heading">
                <span class="g360-modal-heading__icon">
                  <i class="feather icon-home"></i>
                </span>

                <div>
                  <small>Registro territorial</small>
                  <h5 class="modal-title">Secretaría municipal</h5>
                </div>
              </div>

              <button
                type="button"
                class="close text-white"
                data-dismiss="modal"
                aria-label="Cerrar"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body">
              <div class="g360-municipal-secretariat-modal__intro">
                <span>
                  <i class="feather icon-shield"></i>
                </span>

                <div>
                  <strong>Registro institucional controlado</strong>
                  <p>
                    Define la ubicación territorial, la dependencia, su responsable,
                    correo oficial y estado de habilitación.
                  </p>
                </div>
              </div>

              <form id="formNewSecretaria" autocomplete="off">
                <input type="hidden" id="editId" name="editId">

                <div class="g360-modal-section-heading">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-map"></i>
                  </span>

                  <div>
                    <small>Ubicación territorial</small>
                    <h6>Departamento y municipio</h6>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                    <select class="form-control" id="tbl_departamento_id" name="tbl_departamento_id" required onchange="DEPARTAMENTO.getMunicipios()">
                      <?php echo $optionDep; ?>
                    </select>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                    <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" required>
                      <option value="">Seleccione un municipio</option>
                    </select>
                  </div>
                </div>

                <div class="g360-modal-section-heading g360-modal-section-heading--institution">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-briefcase"></i>
                  </span>

                  <div>
                    <small>Dependencia municipal</small>
                    <h6>Secretaría y responsable</h6>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="newSecretaria">Secretaría <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newSecretaria" name="newSecretaria" required>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="newSecretario">Secretario <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newSecretario" name="newSecretario" required>
                  </div>
                </div>

                <div class="g360-modal-section-heading g360-modal-section-heading--contact">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-mail"></i>
                  </span>

                  <div>
                    <small>Contacto y disponibilidad</small>
                    <h6>Correo institucional y estado</h6>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="newEmail">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="newEmail" name="newEmail" required>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="newHabilitado">Habilitado <span class="text-danger">*</span></label>
                    <select class="form-control" id="newHabilitado" name="newHabilitado" required>
                      <option value="Seleccione">Seleccione</option>
                      <option value="si">Sí</option>
                      <option value="no">No</option>
                    </select>
                  </div>
                </div>

              </form>
            </div>

            <div class="modal-footer">
              <div class="g360-modal-footer-message">
                <i class="feather icon-lock"></i>
                El registro quedará vinculado al municipio seleccionado.
              </div>

              <div class="g360-modal-footer-actions">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                  Cancelar
                </button>

                <button
                  type="button"
                  id="btnSaveSecretaria"
                  class="btn btn-primary"
                  onclick="saveNewSecretaria();"
                >
                  <i class="feather icon-save"></i>
                  Guardar secretaría
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Variables de sesión -->
      <input type="hidden" id="municipioUsuario" value="<?php echo $municipioUsuario; ?>">
      <input type="hidden" id="tipoUsuario" value="<?php echo $userType; ?>">
      <input type="hidden" id="isAdmin" value="<?php echo $isAdmin ? '1' : '0'; ?>">

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
  
  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>

  <script src="<?php echo Util::versionar('./admin/js/departamento.js'); ?>"></script>
  <script src="<?php echo Util::versionar('./admin/js/secretarias_municipios.js'); ?>"></script>


  <script>
    function abrirSecretariaMunicipalDesdeHero() {
      const botonOriginal = document.getElementById('btnNuevaSecretaria');

      if (botonOriginal) {
        botonOriginal.click();
        return;
      }

      if ($('#newModalSecretaria').length) {
        try {
          $('#newModalSecretaria').modal('show');
        } catch (e) {}
      }

      if (typeof ingresarSecretaria === 'function') {
        try {
          ingresarSecretaria();
        } catch (e) {}
      }
    }

    // ✅ Fallback por si el data-toggle no engancha por algún conflicto:
    $(document).on('click', '#btnNuevaSecretaria', function(){
      if ($('#newModalSecretaria').length) {
        try { $('#newModalSecretaria').modal('show'); } catch(e) {}
      }
      if (typeof ingresarSecretaria === 'function') {
        try { ingresarSecretaria(); } catch(e) {}
      }
    });
  </script>

</body>
</html>
