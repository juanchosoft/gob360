<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/Configuracion_Puntaje.php';

// Permisos
requirePermission('accion_unificada.config.puntajes.view');
$view = SessionData::hasPermission('accion_unificada.config.puntajes.view');
$create = SessionData::hasPermission('accion_unificada.config.puntajes.create');
$edit = SessionData::hasPermission('accion_unificada.config.puntajes.update');
$userType = SessionData::getUserType();
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  
  <link rel="stylesheet" href="assets/css/configuracion_puntajes_gob360_premium.css">
</head>

<body class="gob360-score-config-page">
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

      <section class="g360-score-hero" aria-label="Configuración de puntajes GOB360">
        <div class="g360-score-hero__grid">

          <aside class="g360-score-brand">
            <span class="g360-score-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-score-brand__logo"
            >

            <span class="g360-score-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-score-brand__status">
              <span></span>
              Motor de puntajes activo
            </div>
          </aside>

          <div class="g360-score-hero__content">
            <div class="g360-score-hero__top">
              <div>
                <div class="g360-score-hero__eyebrow">
                  <i class="feather icon-sliders"></i>
                  Acción Unificada
                </div>

                <h1 class="g360-score-hero__title">
                  Configuración de Puntajes
                </h1>

                <p class="g360-score-hero__description">
                  Define rangos, factores de inestabilidad, tipos de mapa,
                  métodos de medición y colores para representar visualmente
                  el comportamiento territorial dentro de GOB360.
                </p>
              </div>

              <div class="g360-score-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="document.getElementById('profile-tab').click();"
                >
                  <i class="feather icon-list"></i>
                  Ver configuraciones
                </button>

                <div class="g360-score-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-score-summary">
              <article>
                <span class="g360-score-summary__icon">
                  <i class="feather icon-map"></i>
                </span>

                <div>
                  <small>Representación</small>
                  <strong>Mapas</strong>
                  <p>Configuración inicial y final</p>
                </div>
              </article>

              <article>
                <span class="g360-score-summary__icon g360-score-summary__icon--ranges">
                  <i class="feather icon-bar-chart-2"></i>
                </span>

                <div>
                  <small>Clasificación</small>
                  <strong>Rangos</strong>
                  <p>Valores desde y hasta</p>
                </div>
              </article>

              <article>
                <span class="g360-score-summary__icon g360-score-summary__icon--colors">
                  <i class="feather icon-droplet"></i>
                </span>

                <div>
                  <small>Semaforización</small>
                  <strong>Colores</strong>
                  <p>Lectura visual territorial</p>
                </div>
              </article>

              <article>
                <span class="g360-score-summary__icon g360-score-summary__icon--permissions">
                  <i class="feather icon-shield"></i>
                </span>

                <div>
                  <small>Administración</small>
                  <strong><?= ($create || $edit) ? 'Habilitada' : 'Consulta' ?></strong>
                  <p><?= htmlspecialchars((string)$userType, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </article>
            </div>

            <div class="g360-score-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-activity"></i>
                Factores de inestabilidad
              </span>

              <span>
                <i class="feather icon-map-pin"></i>
                Mapa inicial y final
              </span>

              <span>
                <i class="feather icon-hash"></i>
                Rangos numéricos
              </span>

              <span>
                <i class="feather icon-pie-chart"></i>
                Tipos de medición
              </span>

              <span>
                <i class="feather icon-lock"></i>
                Acceso autorizado
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-sm-12">

          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#home" type="button"
                role="tab" aria-controls="home" aria-selected="true">
                  <i class="feather icon-plus-circle"></i>
                  <span>Ingresar configuración</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#profile" type="button"
                role="tab" aria-controls="profile" aria-selected="false" onclick="cargaData()">
                  <i class="feather icon-list"></i>
                  <span>Listado de configuraciones</span>
                </button>
            </li>
          </ul>

          <div class="tab-content g360-score-tabs-content" id="myTabContent">

            <!-- TAB 1 -->
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
              <br>
              <div class="card g360-score-card g360-score-card--create">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                  <div class="g360-card-heading">
                    <span class="g360-card-heading__icon">
                      <i class="feather icon-sliders"></i>
                    </span>

                    <div>
                      <span class="g360-card-heading__eyebrow">Nueva regla territorial</span>
                      <h5 class="mb-0">Ingresar configuración de puntajes</h5>
                      <p>
                        Define la clasificación, el rango y la representación
                        visual que tendrá la configuración.
                      </p>
                    </div>
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

                <div class="card-body">
                  <form id="formupuntajes" role="form" autocomplete="off">
                    <input type="hidden" name="op" id="op" />
                    <input type="hidden" name="idPuntaje" id="idPuntaje" />

                    <div class="g360-form-section-heading">
                      <span class="g360-form-section-heading__icon">
                        <i class="feather icon-tag"></i>
                      </span>

                      <div>
                        <span>Clasificación</span>
                        <h6>Identificación y tipo de configuración</h6>
                        <p>
                          Relaciona la regla con un factor, mapa y método de medición.
                        </p>
                      </div>
                    </div>

                    <div class="form-row py-2">
                      <div class="form-group col-md-3">
                        <label for="name">Nombre<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Ej: Estable, Medio, Crítico" required>
                      </div>

                      <div class="form-group col-md-3">
                        <label for="factorGobernacionId">Factor de inestabilidad<span class="text-danger">*</span></label>
                        <select class="form-control" id="factorGobernacionId" name="factorGobernacionId">
                          <option value="">Seleccione</option>
                        </select>
                      </div>

                      <div class="form-group col-md-3">
                        <label for="tipo">Tipo mapa<span class="text-danger">*</span></label>
                        <select id="tipo" name="tipo" class="form-control">
                          <option value="">Seleccione</option>
                          <option value="1">Inicial</option>
                          <option value="2">Final</option>
                        </select>
                      </div>

                      <div class="form-group col-md-3">
                        <label for="tipo_medicion">Tipo Medición<span class="text-danger">*</span></label>
                        <select id="tipo_medicion" name="tipo_medicion" class="form-control">
                          <option value="">Seleccione</option>
                          <option value="Cantidad">Cantidad</option>
                          <option value="Mantenimiento">Mantenimiento</option>
                          <option value="Creación">Creación</option>
                        </select>
                      </div>
                    </div>

                    <div class="g360-form-section-heading">
                      <span class="g360-form-section-heading__icon g360-form-section-heading__icon--range">
                        <i class="feather icon-bar-chart"></i>
                      </span>

                      <div>
                        <span>Rango y visualización</span>
                        <h6>Valores y color de representación</h6>
                        <p>
                          Establece los límites numéricos y el color asociado.
                        </p>
                      </div>
                    </div>

                    <div class="form-row py-2">
                      <div class="form-group col-md-3">
                        <label for="desde">Desde<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="desde" name="desde" placeholder="Desde" required>
                      </div>

                      <div class="form-group col-md-3">
                        <label for="hasta">Hasta<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="hasta" name="hasta" placeholder="Hasta" required>
                      </div>

                      <div class="form-group col-md-3">
                        <label for="color">Color<span class="text-danger">*</span></label>
                        <div class="input-group" style="margin: 0;">
                          <select id="color" name="color" onchange="updateColorBox()" class="form-control">
                            <option value="">Seleccione</option>
                            <option value="#cd162c">Rojo</option>
                            <option value="#cd7d16">Naranja</option>
                            <option value="#dbd509">Amarillo</option>
                            <option value="#2774f1">Azul</option>
                            <option value="#62af0a">Verde</option>
                            <option value="#ffffff">Blanco</option>
                          </select>
                          <div class="input-group-append">
                            <div id="colorBox" class="rounded px-3 d-flex align-items-center" style="background-color:#0b0f1a; min-width:60px; height:48px;">
                              &nbsp;
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="g360-score-save-bar">
                      <div class="g360-score-save-bar__message">
                        <i class="feather icon-info"></i>
                        <span>
                          Verifica que los rangos no se superpongan y que el color
                          corresponda al nivel territorial configurado.
                        </span>
                      </div>

                      <div class="g360-score-save-bar__actions">
                        <button
                          type="button"
                          onclick="UTIL.clearForm('formupuntajes');"
                          class="btn btn-danger"
                        >
                          <i class="feather icon-x"></i>
                          Cancelar
                        </button>

                        <button
                          type="button"
                          onclick="save();"
                          class="btn btn-primary"
                        >
                          <i class="feather icon-save"></i>
                          Guardar configuración
                        </button>
                      </div>
                    </div>

                  </form>
                </div>
              </div>
            </div>

            <!-- TAB 2 -->
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
              <br>
              <div class="card g360-score-card g360-score-card--list">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
                  <div class="g360-card-heading">
                    <span class="g360-card-heading__icon g360-card-heading__icon--list">
                      <i class="feather icon-list"></i>
                    </span>

                    <div>
                      <span class="g360-card-heading__eyebrow">Matriz de configuración</span>
                      <h5 class="mb-0">Listado de configuraciones</h5>
                      <p>
                        Filtra, consulta y edita las reglas de puntaje disponibles.
                      </p>
                    </div>
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
                  <div class="g360-score-tools">
                    <div class="g360-score-filter">
                      <span class="g360-score-filter__icon">
                        <i class="feather icon-filter"></i>
                      </span>

                      <div>
                        <label for="filtroTipo">Tipo de mapa</label>
                        <select id="filtroTipo" class="form-control">
                          <option value="0">Todos</option>
                          <option value="1">Inicial</option>
                          <option value="2">Final</option>
                        </select>
                      </div>
                    </div>

                    <div class="g360-score-search">
                      <span class="g360-score-search__icon">
                        <i class="feather icon-search"></i>
                      </span>

                      <div>
                        <label for="customSearch">Búsqueda rápida</label>
                        <input
                          type="text"
                          id="customSearch"
                          class="form-control"
                          placeholder="Buscar nombre, factor, medición, rango o color..."
                        >
                      </div>
                    </div>
                  </div>

                  <div class="table-responsive tabla-informacion tabla-scroll g360-score-table">
                    <table class="table table-hover mb-0" id="dynamictable" aria-label="Configuraciones de puntajes">
                      <thead style="">
                        <tr class="border-1">
                          <th>Editar</th>
                          <th>Nombre</th>
                          <th>Factor inestabilidad</th>
                          <th>Tipo mapa</th>
                          <th>Tipo medición</th>
                          <th>Desde</th>
                          <th>Hasta</th>
                          <th>Color</th>
                        </tr>
                      </thead>
                    </table>
                  </div>

                </div>
              </div>
            </div>

          </div><!-- tab content -->
        </div>
      </div>

    </div>
  </div>

  <!-- MODAL -->
  <div class="modal fade" id="modalPuntaje" tabindex="-1" role="dialog" aria-labelledby="modalPuntajeLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content g360-score-modal">
        <div class="modal-header">
          <div class="g360-modal-heading">
            <span class="g360-modal-heading__icon">
              <i class="feather icon-edit-3"></i>
            </span>

            <div>
              <small>Actualización de regla territorial</small>
              <h5 class="modal-title" id="modalPuntajeLabel">
                Editar puntaje
              </h5>
            </div>
          </div>

          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="g360-score-modal__intro">
            <span>
              <i class="feather icon-shield"></i>
            </span>

            <div>
              <strong>Edición controlada</strong>
              <p>
                Actualiza el factor, tipo, rango o color sin modificar los
                identificadores utilizados por la lógica actual.
              </p>
            </div>
          </div>

          <form id="formEdit" role="form" autocomplete="off">
            <input type="hidden" name="op" id="opEdit" />
            <input type="hidden" name="idPuntajeEdit" id="idPuntajeEdit" />

            <div class="g360-modal-section-heading">
              <span class="g360-modal-section-heading__icon">
                <i class="feather icon-sliders"></i>
              </span>

              <div>
                <small>Configuración</small>
                <h6>Datos del puntaje</h6>
              </div>
            </div>

            <div class="form-row py-2">
              <div class="form-group col-md-3">
                <label for="nameEdit">Nombre<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nameEdit" name="nameEdit" required>
              </div>

              <div class="form-group col-md-3">
                <label for="factorGobernacionIdEdit">Factor de inestabilidad<span class="text-danger">*</span></label>
                <select class="form-control" id="factorGobernacionIdEdit" name="factorGobernacionIdEdit">
                  <option value="">Seleccione</option>
                </select>
              </div>

              <div class="form-group col-md-3">
                <label for="tipoEdit">Tipo mapa<span class="text-danger">*</span></label>
                <select id="tipoEdit" name="tipoEdit" class="form-control">
                  <option value="">Seleccione</option>
                  <option value="1">Inicial</option>
                  <option value="2">Final</option>
                </select>
              </div>

              <div class="form-group col-md-3">
                <label for="tipo_medicionEdit">Tipo Medición<span class="text-danger">*</span></label>
                <select id="tipo_medicionEdit" name="tipo_medicionEdit" class="form-control">
                  <option value="">Seleccione</option>
                  <option value="Cantidad">Cantidad</option>
                  <option value="Mantenimiento">Mantenimiento</option>
                  <option value="Creación">Creación</option>
                </select>
              </div>
            </div>

            <div class="form-row py-2">
              <div class="form-group col-md-3">
                <label for="desdeEdit">Desde<span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="desdeEdit" name="desdeEdit" required>
              </div>

              <div class="form-group col-md-3">
                <label for="hastaEdit">Hasta<span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="hastaEdit" name="hastaEdit" required>
              </div>

              <div class="form-group col-md-3">
                <label for="colorEdit">Color<span class="text-danger">*</span></label>
                <div class="input-group" style="margin: 0;">
                  <select id="colorEdit" name="colorEdit" onchange="updateColorBoxEdit()" class="form-control">
                    <option value="">Seleccione</option>
                    <option value="#cd162c">Rojo</option>
                    <option value="#cd7d16">Naranja</option>
                    <option value="#dbd509">Amarillo</option>
                    <option value="#2774f1">Azul</option>
                    <option value="#62af0a">Verde</option>
                    <option value="#ffffff">Blanco</option>
                  </select>
                  <div class="input-group-append">
                    <div id="colorBoxEdit" class="rounded px-3 d-flex align-items-center" style="background-color:#0b0f1a; min-width: 60px; height: 48px;">&nbsp;</div>
                  </div>
                </div>
              </div>
            </div>

          </form>
        </div>

        <div class="modal-footer">
          <div class="g360-modal-footer-message">
            <i class="feather icon-lock"></i>
            La actualización quedará aplicada a esta regla de puntaje.
          </div>

          <div class="g360-modal-footer-actions">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              Cancelar
            </button>

            <button
              type="button"
              id="btnGuardarEditar"
              class="btn btn-primary"
              onclick="editSave();"
            >
              <i class="feather icon-save"></i>
              Actualizar configuración
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script type="text/javascript" src="<?php echo Util::versionar('./admin/js/conf_puntajes.js'); ?>"></script>
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

      // Bridge: data-toggle="modal" data-target="#modalPuntaje"
      document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-toggle="modal"][data-target]');
        if (!btn) return;

        // Si BS4 ya existe, no interceptar
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
