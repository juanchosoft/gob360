<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

requirePermission('configuracion.veredas.manage');
?>
<link rel="stylesheet" href="assets/css/gestion_veredas_gob360_premium.css">
<?php
?>

<body class="dashboard-premium gob360-villages-page">

  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>
  <!-- [ Pre-loader ] End -->

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

    

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-villages-hero" aria-label="Gestión territorial de veredas GOB360">
        <div class="g360-villages-hero__grid">

          <aside class="g360-villages-brand">
            <span class="g360-villages-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-villages-brand__logo"
            >

            <span class="g360-villages-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-villages-brand__status">
              <span></span>
              Gestión territorial activa
            </div>
          </aside>

          <div class="g360-villages-hero__content">
            <div class="g360-villages-hero__top">
              <div>
                <div class="g360-villages-hero__eyebrow">
                  <i class="feather icon-map"></i>
                  Configuración territorial
                </div>

                <h1 class="g360-villages-hero__title">
                  Gestión de Veredas
                </h1>

                <p class="g360-villages-hero__description">
                  Administra la información territorial de las veredas de Santander,
                  consulta su municipio, población, código oficial, estado electoral
                  y observaciones institucionales.
                </p>
              </div>

              <div class="g360-villages-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--primary"
                  onclick="abrirModalNueva()"
                >
                  <i class="feather icon-plus-circle"></i>
                  Nueva vereda
                </button>

                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="cargarTabla()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar
                </button>

                <div class="g360-villages-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-villages-summary">
              <article>
                <span class="g360-villages-summary__icon">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Cobertura</small>
                  <strong>Santander</strong>
                  <p>Gestión de veredas por municipio</p>
                </div>
              </article>

              <article>
                <span class="g360-villages-summary__icon g360-villages-summary__icon--population">
                  <i class="feather icon-users"></i>
                </span>

                <div>
                  <small>Información</small>
                  <strong>Población</strong>
                  <p>Hombres, mujeres y total</p>
                </div>
              </article>

              <article>
                <span class="g360-villages-summary__icon g360-villages-summary__icon--code">
                  <i class="feather icon-hash"></i>
                </span>

                <div>
                  <small>Identificación</small>
                  <strong>Código</strong>
                  <p>Generación automática por municipio</p>
                </div>
              </article>

              <article>
                <span class="g360-villages-summary__icon g360-villages-summary__icon--security">
                  <i class="feather icon-shield"></i>
                </span>

                <div>
                  <small>Administración</small>
                  <strong>Autorizada</strong>
                  <p>Permiso de gestión territorial</p>
                </div>
              </article>
            </div>

            <div class="g360-villages-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-search"></i>
                Consulta territorial
              </span>

              <span>
                <i class="feather icon-edit-3"></i>
                Creación y edición
              </span>

              <span>
                <i class="feather icon-users"></i>
                Información poblacional
              </span>

              <span>
                <i class="feather icon-check-circle"></i>
                Estado electoral
              </span>

              <span>
                <i class="feather icon-file-text"></i>
                Observaciones
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="row">
        <div class="col-12">
          <div class="card saas-card g360-villages-card">

            <div class="card-header">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">Directorio territorial</span>
                  <h5>Veredas del departamento de Santander</h5>
                  <p>
                    Filtra por municipio, consulta información poblacional
                    y actualiza los registros territoriales.
                  </p>
                </div>
              </div>

              <div class="g360-card-header-actions">
                <button
                  type="button"
                  class="btn btn-secondary"
                  onclick="limpiarFiltros()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Limpiar filtros
                </button>

                <button
                  type="button"
                  class="btn btn-primary"
                  onclick="abrirModalNueva()"
                >
                  <i class="feather icon-plus"></i>
                  Nueva vereda
                </button>
              </div>
            </div>

            <div class="card-body">

              <!-- Filtros -->
              <section class="filter-panel g360-villages-filters" aria-label="Filtros territoriales">
                <div class="g360-villages-filters__heading">
                  <span class="g360-villages-filters__heading-icon">
                    <i class="feather icon-filter"></i>
                  </span>

                  <div>
                    <small>Consulta territorial</small>
                    <h6>Filtrar veredas</h6>
                    <p>Combina municipio, nombre o código para localizar registros.</p>
                  </div>
                </div>

                <div class="g360-villages-filters__grid">
                  <div class="g360-villages-filter-field">
                    <label for="filtroMunicipio">
                      <i class="feather icon-map"></i>
                      Municipio
                    </label>

                    <select
                      id="filtroMunicipio"
                      class="form-control"
                      onchange="cargarTabla()"
                    >
                      <option value="">Todos los municipios</option>
                    </select>
                  </div>

                  <div class="g360-villages-filter-field">
                    <label for="filtroBusqueda">
                      <i class="feather icon-search"></i>
                      Nombre o código
                    </label>

                    <input
                      type="search"
                      id="filtroBusqueda"
                      class="form-control"
                      placeholder="Ejemplo: LA HONDA o 68001001"
                      oninput="cargarTabla()"
                    >
                  </div>

                  <div class="g360-villages-filter-actions">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      onclick="limpiarFiltros()"
                    >
                      <i class="feather icon-x"></i>
                      Limpiar
                    </button>

                    <button
                      type="button"
                      class="btn btn-primary"
                      onclick="cargarTabla()"
                    >
                      <i class="feather icon-search"></i>
                      Consultar
                    </button>
                  </div>
                </div>
              </section>

              <!-- Tabla -->
              <div class="table-responsive tabla-informacion tabla-scroll g360-villages-table">
                <table class="table table-hover mb-0" id="tblVeredas" aria-label="Listado de veredas de Santander">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Código</th>
                      <th>Nombre vereda</th>
                      <th>Municipio</th>
                      <th>Hombres</th>
                      <th>Mujeres</th>
                      <th>Total</th>
                      <th>Habilit. votar</th>
                      <th>Observaciones</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody id="cuerpoTabla">
                    <tr><td colspan="10" class="text-center py-4 text-muted">Cargando datos…</td></tr>
                  </tbody>
                </table>
              </div>

              <!-- Paginación -->
              <div class="g360-villages-pagination" id="paginacionWrapper">
                <span id="infoRegistros" class="text-white" style="font-size:.88rem;font-weight:700;"></span>
                <div id="paginacion" class="d-flex gap-1 flex-wrap"></div>
              </div>

            </div><!-- card-body -->
          </div><!-- saas-card -->
        </div>
      </div>

    </div><!-- pcoded-content -->

    <!-- ── Modal Nueva / Editar Vereda ─────────────────────────────────── -->
    <div class="modal fade" id="modalVereda" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content g360-village-modal">
          <div class="modal-header">
            <div class="g360-modal-heading">
              <span class="g360-modal-heading__icon">
                <i class="feather icon-map-pin"></i>
              </span>

              <div>
                <small>Registro territorial</small>
                <h5 class="modal-title" id="tituloModal">Nueva Vereda</h5>
              </div>
            </div>

            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="g360-village-modal__intro">
              <span>
                <i class="feather icon-shield"></i>
              </span>

              <div>
                <strong>Información territorial controlada</strong>
                <p>
                  Selecciona el municipio, registra el nombre y completa los
                  datos poblacionales y electorales de la vereda.
                </p>
              </div>
            </div>

            <input type="hidden" id="veredaId">

            <div class="g360-modal-section-heading">
              <span class="g360-modal-section-heading__icon">
                <i class="feather icon-map"></i>
              </span>

              <div>
                <small>Ubicación</small>
                <h6>Municipio, código y nombre</h6>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label for="inputMunicipio">Municipio <span class="text-danger">*</span></label>
                <select id="inputMunicipio" class="form-control" onchange="previewCodigo()">
                  <option value="">Seleccione…</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>Código vereda</label>
                <div id="codigoPreview" class="form-control"
                  style="background:#f0f4ff !important; color:#20427F !important; font-weight:800; letter-spacing:1px;">
                  Se genera automáticamente
                </div>
                <!-- En edición se muestra el código actual (solo lectura) -->
                <input type="hidden" id="inputCodigo">
              </div>
              <div class="col-md-12">
                <label for="inputNombre">Nombre vereda <span class="text-danger">*</span></label>
                <input type="text" id="inputNombre" class="form-control" placeholder="Ej: LA HONDA"
                  style="text-transform:uppercase;">
              </div>
              <div class="col-12">
                <div class="g360-modal-section-heading g360-modal-section-heading--population">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-users"></i>
                  </span>

                  <div>
                    <small>Población</small>
                    <h6>Distribución de habitantes</h6>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <label for="inputHombres">Hombres</label>
                <input type="number" id="inputHombres" class="form-control" min="0" value="0">
              </div>
              <div class="col-md-4">
                <label for="inputMujeres">Mujeres</label>
                <input type="number" id="inputMujeres" class="form-control" min="0" value="0">
              </div>
              <div class="col-md-4">
                <label for="inputTotal">Total habitantes</label>
                <input type="number" id="inputTotal" class="form-control" min="0" value="0">
              </div>
              <div class="col-12">
                <div class="g360-modal-section-heading g360-modal-section-heading--status">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-check-circle"></i>
                  </span>

                  <div>
                    <small>Estado y observaciones</small>
                    <h6>Disponibilidad electoral e información adicional</h6>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <label for="inputHabilitada">Habilitada para votar</label>
                <select id="inputHabilitada" class="form-control">
                  <option value="">Sin definir</option>
                  <option value="ACTIVO">ACTIVO</option>
                  <option value="INACTIVO">INACTIVO</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="inputObservaciones">Observaciones</label>
                <input type="text" id="inputObservaciones" class="form-control" placeholder="Opcional…">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="g360-modal-footer-message">
              <i class="feather icon-lock"></i>
              El código se genera automáticamente al crear una nueva vereda.
            </div>

            <div class="g360-modal-footer-actions">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">
                Cancelar
              </button>

              <button
                type="button"
                class="btn btn-primary"
                onclick="guardarVereda()"
              >
                <i class="feather icon-save"></i>
                Guardar vereda
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ─────────────────────────────────────────────────────────────────── -->

    <?php include 'admin/include/footer.php'; ?>
  </div><!-- pcoded-main-container -->

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet">
                



  <script>
    // ── Configuración global ──────────────────────────────────────────────
    const AJAX = 'admin/ajax/rqst.php';
    let paginaActual = 0;
    let registrosPorPagina = 10;
    let totalRegistros = 0;
    let debounceTimer = null;

    // ── Inicialización ────────────────────────────────────────────────────
    $(function () {
      cargarMunicipios();
      cargarTabla();
    });

    // ── Cargar municipios de Santander ────────────────────────────────────
    function cargarMunicipios() {
      $.post(AJAX, { op: 'veredas_municipios_santander' }, function (res) {
        if (!res.output || !res.output.valid) return;
        const opts = res.output.response.map(m =>
          `<option value="${m.id}">${m.municipio}</option>`
        ).join('');
        $('#filtroMunicipio').append(opts);
        $('#inputMunicipio').append(opts);
      }, 'json');
    }

    // ── Cargar tabla (paginación manual) ──────────────────────────────────
    function cargarTabla(resetPagina = true) {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () {
        if (resetPagina) paginaActual = 0;

        const municipioId = $('#filtroMunicipio').val();
        const busqueda    = $('#filtroBusqueda').val().trim();

        $.post(AJAX, {
          op:           'veredas_get_admin',
          municipio_id: municipioId,
          search:       busqueda,
          start:        paginaActual * registrosPorPagina,
          length:       registrosPorPagina,
          draw:         1,
        }, function (res) {
          renderTabla(res);
          renderPaginacion(res.recordsTotal);
        }, 'json');
      }, 300);
    }

    // ── Render filas ──────────────────────────────────────────────────────
    function renderTabla(res) {
      const tbody = $('#cuerpoTabla');
      tbody.empty();

      if (!res.data || res.data.length === 0) {
        tbody.html('<tr><td colspan="10" class="text-center py-4 text-muted">Sin resultados</td></tr>');
        return;
      }

      totalRegistros = res.recordsTotal;
      const inicio   = paginaActual * registrosPorPagina;

      res.data.forEach(function (v, i) {
        const badgeVotar = v.habilitada_para_votar === 'ACTIVO'
          ? `<span class="badge-activo">ACTIVO</span>`
          : (v.habilitada_para_votar === 'INACTIVO'
              ? `<span class="badge-inactivo">INACTIVO</span>`
              : `<span class="badge-null">—</span>`);

        tbody.append(`
          <tr>
            <td>${inicio + i + 1}</td>
            <td>${escHtml(v.codigo_vereda)}</td>
            <td>${escHtml(v.nombre_vereda)}</td>
            <td>${escHtml(v.municipio ?? '—')}</td>
            <td>${v.hombres ?? '—'}</td>
            <td>${v.mujeres ?? '—'}</td>
            <td>${v.total   ?? '—'}</td>
            <td>${badgeVotar}</td>
            <td style="max-width:200px;white-space:normal;">${escHtml(v.observaciones ?? '')}</td>
            <td>
              <button class="btn btn-sm btn-warning" onclick="editarVereda(${v.id})"
                title="Editar" style="border-radius:8px;">
                <i class="feather icon-edit-2"></i>
              </button>
            </td>
          </tr>`);
      });
    }

    // ── Paginación ────────────────────────────────────────────────────────
    function renderPaginacion(total) {
      totalRegistros = total;
      const totalPaginas = Math.ceil(total / registrosPorPagina);
      const inicio = paginaActual * registrosPorPagina + 1;
      const fin    = Math.min(inicio + registrosPorPagina - 1, total);

      $('#infoRegistros').text(
        total > 0
          ? `Mostrando ${inicio} – ${fin} de ${total} registros`
          : 'Sin registros'
      );

      const pag = $('#paginacion').empty();
      if (totalPaginas <= 1) return;

      const btnCls = 'btn btn-sm';
      // Anterior
      pag.append(
        `<button class="${btnCls} ${paginaActual === 0 ? 'btn-secondary disabled' : 'btn-outline-light'}"
          onclick="irPagina(${paginaActual - 1})">‹ Anterior</button>`
      );
      // Páginas
      for (let p = 0; p < totalPaginas; p++) {
        if (totalPaginas > 7 && Math.abs(p - paginaActual) > 2 && p !== 0 && p !== totalPaginas - 1) {
          if (p === 1 || p === totalPaginas - 2) {
            pag.append(`<button class="${btnCls} btn-secondary disabled">…</button>`);
          }
          continue;
        }
        pag.append(
          `<button class="${btnCls} ${p === paginaActual ? 'btn-primary' : 'btn-outline-light'}"
            onclick="irPagina(${p})" style="min-width:36px;">${p + 1}</button>`
        );
      }
      // Siguiente
      pag.append(
        `<button class="${btnCls} ${paginaActual >= totalPaginas - 1 ? 'btn-secondary disabled' : 'btn-outline-light'}"
          onclick="irPagina(${paginaActual + 1})">Siguiente ›</button>`
      );
    }

    function irPagina(p) {
      const totalPaginas = Math.ceil(totalRegistros / registrosPorPagina);
      if (p < 0 || p >= totalPaginas) return;
      paginaActual = p;
      cargarTabla(false);
    }

    // ── Limpiar filtros ───────────────────────────────────────────────────
    function limpiarFiltros() {
      $('#filtroMunicipio').val('');
      $('#filtroBusqueda').val('');
      cargarTabla();
    }

    // ── Preview código automático al cambiar municipio ───────────────────
    function previewCodigo() {
      const mun = $('#inputMunicipio').val();
      const esEdicion = $('#veredaId').val() !== '';
      if (esEdicion || !mun) return; // en edición no se recalcula

      $.post(AJAX, { op: 'vereda_preview_codigo', municipio_id: mun }, function (res) {
        if (res.output?.valid) {
          $('#codigoPreview').text(res.output.response);
        }
      }, 'json');
    }

    // ── Modal nueva vereda ────────────────────────────────────────────────
    function abrirModalNueva() {
      $('#tituloModal').text('Nueva Vereda');
      $('#veredaId').val('');
      $('#inputMunicipio').val('');
      $('#inputCodigo').val('');
      $('#codigoPreview').text('Seleccione un municipio…');
      $('#inputNombre').val('');
      $('#inputHombres').val(0);
      $('#inputMujeres').val(0);
      $('#inputTotal').val(0);
      $('#inputHabilitada').val('');
      $('#inputObservaciones').val('');
      $('#modalVereda').modal('show');
    }

    // ── Editar vereda ─────────────────────────────────────────────────────
    function editarVereda(id) {
      $.post(AJAX, { op: 'vereda_get_by_id', id: id }, function (res) {
        if (!res.output || !res.output.valid) {
          Swal.fire('Error', res.output?.response ?? 'No se pudo cargar la vereda.', 'error');
          return;
        }
        const v = res.output.response;
        $('#tituloModal').text('Editar Vereda');
        $('#veredaId').val(v.id);
        $('#inputMunicipio').val(v.municipio_id);
        $('#inputCodigo').val(v.codigo_vereda);
        $('#codigoPreview').text(v.codigo_vereda);
        $('#inputNombre').val(v.nombre_vereda);
        $('#inputHombres').val(v.hombres ?? 0);
        $('#inputMujeres').val(v.mujeres ?? 0);
        $('#inputTotal').val(v.total ?? 0);
        $('#inputHabilitada').val(v.habilitada_para_votar ?? '');
        $('#inputObservaciones').val(v.observaciones ?? '');
        $('#modalVereda').modal('show');
      }, 'json');
    }

    // ── Guardar (crear o actualizar) ──────────────────────────────────────
    function guardarVereda() {
      const id           = $('#veredaId').val();
      const municipio_id = $('#inputMunicipio').val();
      const nombre       = $('#inputNombre').val().trim().toUpperCase();

      if (!municipio_id) { Swal.fire('Atención', 'Seleccione un municipio.', 'warning'); return; }
      if (!nombre)        { Swal.fire('Atención', 'El nombre de la vereda es obligatorio.', 'warning'); return; }

      const esEdicion = id !== '';
      const params = {
        op:                   esEdicion ? 'vereda_update' : 'vereda_save',
        id:                   id,
        municipio_id:         municipio_id,
        codigo_vereda:        $('#inputCodigo').val(), // solo usado en edición
        nombre_vereda:        nombre,
        hombres:              $('#inputHombres').val() || 0,
        mujeres:              $('#inputMujeres').val() || 0,
        total:                $('#inputTotal').val() || 0,
        habilitada_para_votar: $('#inputHabilitada').val(),
        observaciones:        $('#inputObservaciones').val().trim(),
      };

      $.post(AJAX, params, function (res) {
        if (res.output?.valid) {
          $('#modalVereda').modal('hide');
          Swal.fire('Éxito', res.output.response, 'success');
          cargarTabla();
        } else {
          Swal.fire('Error', res.output?.response ?? 'Error al guardar.', 'error');
        }
      }, 'json');
    }

    // ── Utilidad: escapar HTML ────────────────────────────────────────────
    function escHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
    }
  </script>

</body>
</html>
