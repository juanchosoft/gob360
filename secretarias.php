<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/Secretarias.php';

// Permisos
requirePermission('configuracion.secretarias.view');
$view = SessionData::hasPermission('configuracion.secretarias.view');
$create = SessionData::hasPermission('configuracion.secretarias.create');
$edit = SessionData::hasPermission('configuracion.secretarias.update');
$permits = SessionData::hasPermission('configuracion.secretarias.update');
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  
  <link rel="stylesheet" href="assets/css/secretarias_gob360_premium.css">
</head>

<body class="gob360-secretariats-page">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-secretariats-hero" aria-label="Secretarías y dependencias GOB360">
        <div class="g360-secretariats-hero__grid">

          <aside class="g360-secretariats-brand">
            <span class="g360-secretariats-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-secretariats-brand__logo"
            >

            <span class="g360-secretariats-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-secretariats-brand__status">
              <span></span>
              Directorio institucional activo
            </div>
          </aside>

          <div class="g360-secretariats-hero__content">
            <div class="g360-secretariats-hero__top">
              <div>
                <div class="g360-secretariats-hero__eyebrow">
                  <i class="feather icon-briefcase"></i>
                  Configuración institucional
                </div>

                <h1 class="g360-secretariats-hero__title">
                  Secretarías y Dependencias
                </h1>

                <p class="g360-secretariats-hero__description">
                  Administra el directorio institucional, responsables, correos
                  oficiales y estado de habilitación de las secretarías vinculadas
                  a GOB360.
                </p>
              </div>

              <div class="g360-secretariats-hero__actions">
                <?php if ($create): ?>
                  <button
                    type="button"
                    class="g360-hero-button g360-hero-button--primary"
                    data-toggle="modal"
                    data-target="#newModalSecretaria"
                  >
                    <i class="feather icon-plus-circle"></i>
                    Nueva secretaría
                  </button>
                <?php endif; ?>

                <div class="g360-secretariats-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-secretariats-summary">
              <article>
                <span class="g360-secretariats-summary__icon">
                  <i class="feather icon-briefcase"></i>
                </span>

                <div>
                  <small>Módulo</small>
                  <strong>Secretarías</strong>
                  <p>Directorio institucional</p>
                </div>
              </article>

              <article>
                <span class="g360-secretariats-summary__icon g360-secretariats-summary__icon--create">
                  <i class="feather icon-user-plus"></i>
                </span>

                <div>
                  <small>Creación</small>
                  <strong><?= $create ? 'Habilitada' : 'Restringida' ?></strong>
                  <p>Según permisos de la sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-secretariats-summary__icon g360-secretariats-summary__icon--edit">
                  <i class="feather icon-edit-3"></i>
                </span>

                <div>
                  <small>Actualización</small>
                  <strong><?= $edit ? 'Habilitada' : 'Consulta' ?></strong>
                  <p>Edición de registros</p>
                </div>
              </article>

              <article>
                <span class="g360-secretariats-summary__icon g360-secretariats-summary__icon--admin">
                  <i class="feather icon-shield"></i>
                </span>

                <div>
                  <small>Perfil</small>
                  <strong><?= $isAdmin ? 'Administrador' : 'Institucional' ?></strong>
                  <p><?= htmlspecialchars((string)$userType, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </article>
            </div>

            <div class="g360-secretariats-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-user"></i>
                Responsables
              </span>

              <span>
                <i class="feather icon-mail"></i>
                Correos oficiales
              </span>

              <span>
                <i class="feather icon-check-circle"></i>
                Estado de habilitación
              </span>

              <span>
                <i class="feather icon-edit"></i>
                Actualización
              </span>

              <span>
                <i class="feather icon-lock"></i>
                Acceso autorizado
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="contenedor">
        <div class="contenido">
          <div class="card g360-secretariats-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-list"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">Directorio institucional</span>
                  <h5 class="mb-0">Listado de secretarías</h5>
                  <p>
                    Consulta responsables, correos oficiales y estado de cada
                    dependencia registrada.
                  </p>
                </div>
              </div>

              <?php if ($create): ?>
                <button
                  type="button"
                  class="btn btn-primary g360-new-secretariat-button"
                  data-toggle="modal"
                  data-target="#newModalSecretaria"
                >
                  <i class="feather icon-plus"></i>
                  Agregar secretaría
                </button>
              <?php endif; ?>

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
              <div class="g360-secretariats-tools">
                <div class="g360-secretariats-search">
                  <span class="g360-secretariats-search__icon">
                    <i class="feather icon-search"></i>
                  </span>

                  <div>
                    <label for="customSearch">Búsqueda rápida</label>
                    <input
                      type="text"
                      id="customSearch"
                      class="form-control"
                      placeholder="Buscar secretaría, responsable, correo o estado..."
                    >
                  </div>
                </div>

                <div class="g360-secretariats-tools__info">
                  <i class="feather icon-info"></i>

                  <span>
                    El estado de habilitación determina si la dependencia puede
                    utilizarse en los módulos relacionados de la plataforma.
                  </span>
                </div>
              </div>

              <div class="table-responsive tabla-informacion tabla-scroll g360-secretariats-table">
                <table class="table table-hover mb-0" id="dynamictable" aria-label="Directorio de secretarías">
                  <thead style="">
                    <tr>
                      <th>Editar</th>
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
        </div>
      </div>

      <!-- Modal Nueva Secretaría -->
      <div class="modal fade" id="newModalSecretaria" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
          <div class="modal-content g360-secretariat-modal">
            <div class="modal-header">
              <div class="g360-modal-heading">
                <span class="g360-modal-heading__icon">
                  <i class="feather icon-briefcase"></i>
                </span>

                <div>
                  <small>Nuevo registro institucional</small>
                  <h5 class="modal-title">Ingresar nueva secretaría</h5>
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
              <div class="g360-secretariat-modal__intro">
                <span>
                  <i class="feather icon-shield"></i>
                </span>

                <div>
                  <strong>Registro institucional controlado</strong>
                  <p>
                    Complete los datos de la dependencia, su responsable,
                    correo oficial y estado de disponibilidad.
                  </p>
                </div>
              </div>

              <form id="formNewSecretaria" autocomplete="off">

                <div class="g360-modal-section-heading">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-edit-3"></i>
                  </span>

                  <div>
                    <small>Información básica</small>
                    <h6>Identificación de la dependencia</h6>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="newSecretaria">Secretaria <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newSecretaria" name="newSecretaria" required>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="newSecretario">Secretario <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newSecretario" name="newSecretario" required>
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
                El registro quedará disponible según el estado seleccionado.
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

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script src="<?php echo Util::versionar('./admin/js/secretarias.js'); ?>"></script>
  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
                



  <script>
    // ====== COMPATIBILIDAD MODALES BS4/BS5 (NO TOCA TU LÓGICA) ======
    (function () {
      function byId(id){ return document.getElementById(id); }

      function showModal(id){
        var el = byId(id);
        if (!el) return;
        // Bootstrap 5
        if (window.bootstrap && window.bootstrap.Modal) {
          window.bootstrap.Modal.getOrCreateInstance(el).show();
          return;
        }
        // Bootstrap 4 (jQuery)
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
          jQuery(el).modal('show');
        }
      }

      function hideModal(el){
        if (!el) return;
        if (window.bootstrap && window.bootstrap.Modal) {
          window.bootstrap.Modal.getOrCreateInstance(el).hide();
          return;
        }
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
          jQuery(el).modal('hide');
        }
      }

      // Bridge: si data-toggle no funciona (por BS5), lo hacemos funcionar
      document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-toggle="modal"][data-target]');
        if (!btn) return;

        // Si es BS4 puro, dejalo actuar
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') return;

        e.preventDefault();
        var target = btn.getAttribute('data-target') || '';
        if (target && target.startsWith('#')) target = target.slice(1);
        if (target) showModal(target);
      }, true);

      // Bridge para cerrar con data-dismiss si no existe jQuery modal
      document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-dismiss="modal"]');
        if (!btn) return;

        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') return;

        e.preventDefault();
        var modalEl = btn.closest('.modal');
        hideModal(modalEl);
      }, true);

      // Limpia backdrop si alguna plantilla deja residuos
      document.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        var bd = document.querySelector('.modal-backdrop');
        if (bd) bd.remove();
      }, true);
    })();
  </script>

</body>
</html>
