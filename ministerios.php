<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

require './admin/include/generic_classes.php';
include './admin/classes/Ministerios.php';

// Permisos
requirePermission('configuracion.ministerios.view');
$view = SessionData::hasPermission('configuracion.ministerios.view');
$create = SessionData::hasPermission('configuracion.ministerios.create');
$edit = SessionData::hasPermission('configuracion.ministerios.update');
$permits = SessionData::hasPermission('configuracion.ministerios.update');
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

//Información de Ministerios
$arr = Ministerios::getAll(null);
$isvalid = $arr['output']['valid'];
$arr = $arr['output']['response'];
$modulo = 'Ministerios';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  
  <link rel="stylesheet" href="assets/css/ministerios_entidades_gob360_premium.css">
</head>

<body class="gob360-ministries-page">
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

      <section class="g360-ministries-hero" aria-label="Ministerios y entidades GOB360">
        <div class="g360-ministries-hero__grid">

          <aside class="g360-ministries-brand">
            <span class="g360-ministries-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-ministries-brand__logo"
            >

            <span class="g360-ministries-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-ministries-brand__status">
              <span></span>
              Directorio interinstitucional activo
            </div>
          </aside>

          <div class="g360-ministries-hero__content">
            <div class="g360-ministries-hero__top">
              <div>
                <div class="g360-ministries-hero__eyebrow">
                  <i class="feather icon-layers"></i>
                  Configuración institucional
                </div>

                <h1 class="g360-ministries-hero__title">
                  Ministerios y Entidades
                </h1>

                <p class="g360-ministries-hero__description">
                  Administra las entidades de articulación nacional, sus
                  responsables y correos institucionales para fortalecer la
                  coordinación interinstitucional dentro de GOB360.
                </p>
              </div>

              <div class="g360-ministries-hero__actions">
                <?php if ($create): ?>
                  <button
                    type="button"
                    class="g360-hero-button g360-hero-button--primary"
                    data-toggle="modal"
                    data-target="#modalMinisterio"
                  >
                    <i class="feather icon-plus-circle"></i>
                    Nueva entidad
                  </button>
                <?php endif; ?>

                <div class="g360-ministries-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-ministries-summary">
              <article>
                <span class="g360-ministries-summary__icon">
                  <i class="feather icon-layers"></i>
                </span>

                <div>
                  <small>Módulo</small>
                  <strong>Entidades</strong>
                  <p>Directorio interinstitucional</p>
                </div>
              </article>

              <article>
                <span class="g360-ministries-summary__icon g360-ministries-summary__icon--records">
                  <i class="feather icon-database"></i>
                </span>

                <div>
                  <small>Registros cargados</small>
                  <strong><?= number_format(is_array($arr) ? count($arr) : 0, 0, ',', '.') ?></strong>
                  <p>Ministerios y entidades disponibles</p>
                </div>
              </article>

              <article>
                <span class="g360-ministries-summary__icon g360-ministries-summary__icon--create">
                  <i class="feather icon-plus-square"></i>
                </span>

                <div>
                  <small>Creación</small>
                  <strong><?= $create ? 'Habilitada' : 'Restringida' ?></strong>
                  <p>Según permisos de la sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-ministries-summary__icon g360-ministries-summary__icon--edit">
                  <i class="feather icon-edit-3"></i>
                </span>

                <div>
                  <small>Actualización</small>
                  <strong><?= $edit ? 'Habilitada' : 'Consulta' ?></strong>
                  <p><?= htmlspecialchars((string)$userType, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </article>
            </div>

            <div class="g360-ministries-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-briefcase"></i>
                Entidades nacionales
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
                <i class="feather icon-edit"></i>
                Actualización
              </span>

              <span>
                <i class="feather icon-shield"></i>
                Acceso autorizado
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="contenedor">
        <div class="contenido">
          <div class="card g360-ministries-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-list"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">Directorio interinstitucional</span>
                  <h5 class="mb-0">Listado de ministerios y entidades</h5>
                  <p>
                    Consulta responsables, correos oficiales y administra los
                    registros disponibles.
                  </p>
                </div>
              </div>

              <?php if ($create): ?>
                <button
                  type="button"
                  class="btn btn-primary g360-new-ministry-button"
                  data-toggle="modal"
                  data-target="#modalMinisterio"
                  onclick="document.getElementById('formMinisterio').reset(); document.getElementById('ministerioId').value=''; document.getElementById('id').value='';"
                >
                  <i class="feather icon-plus"></i>
                  Agregar entidad
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
              <div class="g360-ministries-tools">
                <div class="g360-ministries-search">
                  <span class="g360-ministries-search__icon">
                    <i class="feather icon-search"></i>
                  </span>

                  <div>
                    <label for="customSearch">Búsqueda rápida</label>
                    <input
                      type="text"
                      id="customSearch"
                      class="form-control"
                      placeholder="Buscar ministerio, entidad, responsable o correo..."
                    >
                  </div>
                </div>

                <div class="g360-ministries-tools__info">
                  <i class="feather icon-info"></i>

                  <span>
                    Utiliza este directorio para identificar entidades y
                    responsables vinculados a procesos de articulación pública.
                  </span>
                </div>
              </div>

              <div class="table-responsive tabla-informacion tabla-scroll g360-ministries-table">
                <table class="table table-hover mb-0" id="dynamictable" aria-label="Directorio de ministerios y entidades">
                  <thead style="">
                    <tr class="border-1">
                      <th>Ingresar</th>
                      <th>Editar</th>
                      <th>Ministerio o entidad</th>
                      <th>Responsable</th>
                      <th>Correo institucional</th>
                    </tr>
                  </thead>
                </table>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Modal para Ingresar/Editar Ministerio -->
      <div class="modal fade" id="modalMinisterio" tabindex="-1" role="dialog" aria-labelledby="modalMinisterioLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
          <form id="formMinisterio" class="w-100" autocomplete="off">
            <input type="hidden" name="id" id="ministerioId" />
            <div class="modal-content g360-ministry-modal">
              <div class="modal-header">
                <div class="g360-modal-heading">
                  <span class="g360-modal-heading__icon">
                    <i class="feather icon-layers"></i>
                  </span>

                  <div>
                    <small>Registro interinstitucional</small>
                    <h5 class="modal-title" id="modalMinisterioLabel">
                      Ministerio o entidad
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
                <div class="g360-ministry-modal__intro">
                  <span>
                    <i class="feather icon-shield"></i>
                  </span>

                  <div>
                    <strong>Información institucional</strong>
                    <p>
                      Registre el nombre de la entidad, su responsable principal
                      y el correo oficial de contacto.
                    </p>
                  </div>
                </div>

                <div class="g360-modal-section-heading">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-edit-3"></i>
                  </span>

                  <div>
                    <small>Datos básicos</small>
                    <h6>Identificación de la entidad</h6>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="ministerio">Nombre del Ministerio <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="ministerio" name="ministerio" required />
                  </div>
                  <div class="form-group col-md-6">
                    <label for="ministro">Nombre del Ministro <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="ministro" name="ministro" required />
                  </div>
                </div>

                <div class="form-group">
                  <label for="correo">Correo Electrónico</label>
                  <input type="email" class="form-control" id="correo" name="correo" />
                  <input type="hidden" class="form-control" id="id" name="id" />
                </div>
              </div>

              <div class="modal-footer">
                <div class="g360-modal-footer-message">
                  <i class="feather icon-lock"></i>
                  Los cambios quedarán registrados en el directorio institucional.
                </div>

                <div class="g360-modal-footer-actions">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                  </button>

                  <button
                    type="button"
                    id="btnGuardarMinisterio"
                    class="btn btn-primary"
                  >
                    <i class="feather icon-save"></i>
                    Guardar entidad
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/ministerios.js"></script>
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

      // Bridge: data-toggle="modal" data-target="#modalMinisterio"
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

      // Bridge cerrar (data-dismiss="modal")
      document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-dismiss="modal"]');
        if (!btn) return;

        if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') return;

        e.preventDefault();
        var modalEl = btn.closest('.modal');
        hideModal(modalEl);
      }, true);

      // Limpia residuos backdrop
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
