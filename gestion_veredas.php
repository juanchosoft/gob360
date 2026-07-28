<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

requirePermission('configuracion.veredas.manage');
?>

<body class="dashboard-premium">

  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track"><div class="loader-fill"></div></div>
  </div>
  <!-- [ Pre-loader ] End -->

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

    <style>
    /* ===============================
       GESTIÓN VEREDAS – GOVTECH WOW
    ================================ */
    :root{
      --bg0:#070A12;
      --bg1:#0B1222;
      --stroke: rgba(255,255,255,.10);
      --stroke2: rgba(255,255,255,.14);
      --txt: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);
      --brand:#4f7cff;
      --brand2:#9b5cff;
      --danger:#ff5b7a;
      --ok:#18ff6d;
      --radius-xl:22px;
      --radius-lg:16px;
      --shadow-soft: 0 14px 40px rgba(0,0,0,.25);
      --shadow-mid: 0 22px 60px rgba(0,0,0,.35);
      --safe-top: 96px;
    }

    body{
      background:
        radial-gradient(900px 420px at 10% 10%, rgba(79,124,255,.28), transparent 60%),
        radial-gradient(900px 420px at 80% 20%, rgba(155,92,255,.22), transparent 60%),
        radial-gradient(900px 520px at 50% 100%, rgba(24,255,109,.10), transparent 60%),
        linear-gradient(180deg, var(--bg0), var(--bg1)) !important;
      color: var(--txt);
      overflow-x:hidden;
    }

    .pcoded-main-container{ background: transparent !important; }
    .pcoded-main-container .pcoded-content { padding: calc(var(--safe-top) + 16px) 16px 18px !important; }
    @media(min-width:768px)  { :root { --safe-top: 112px; } .pcoded-main-container .pcoded-content { padding: calc(var(--safe-top) + 18px) 24px 24px !important; } }
    @media(min-width:1200px) { :root { --safe-top: 120px; } .pcoded-main-container .pcoded-content { padding: calc(var(--safe-top) + 22px) 42px 34px !important; max-width: 1400px; margin: 0 auto; } }

    /* page header */
    .page-header .page-block{
      border:1px solid var(--stroke);
      background: rgba(255,255,255,.05);
      border-radius: var(--radius-lg);
      padding: 14px 14px;
      box-shadow: var(--shadow-soft);
      overflow:hidden;
      position: relative;
    }
    .page-header .page-block:before{
      content:""; position:absolute; inset:-2px;
      background: radial-gradient(320px 180px at 10% 10%, rgba(79,124,255,.25), transparent 65%), radial-gradient(320px 180px at 90% 20%, rgba(155,92,255,.18), transparent 65%);
      pointer-events:none;
    }
    .page-header .page-block > *{ position:relative; z-index:1; }
    .page-header h5, .breadcrumb .breadcrumb-item, .breadcrumb .breadcrumb-item a{
      color: var(--txt) !important;
    }
    .breadcrumb .breadcrumb-item a{ color: var(--muted) !important; }

    /* card */
    .saas-card {
      border: 1px solid var(--stroke) !important;
      border-radius: var(--radius-xl) !important;
      background: linear-gradient(135deg, rgba(255,255,255,.09), rgba(255,255,255,.04)) !important;
      box-shadow: var(--shadow-mid);
      overflow:hidden;
      position:relative;
    }
    .saas-card:before{
      content:""; position:absolute; inset:-2px;
      background: radial-gradient(340px 200px at 4% 6%, rgba(79,124,255,.16), transparent 64%), radial-gradient(360px 200px at 96% 8%, rgba(155,92,255,.10), transparent 64%);
      pointer-events:none;
    }
    .saas-card > *{ position:relative; z-index:1; }
    .saas-card .card-header{
      background: linear-gradient(135deg, rgba(255,255,255,.06), rgba(255,255,255,.04)) !important;
      border-bottom: 1px solid var(--stroke);
      padding: 14px 16px;
    }
    .saas-card .card-header h5{ color: var(--txt) !important; font-weight: 1000 !important; margin: 0; }
    .saas-card .card-body{ padding: 16px; }

    /* filter panel */
    .filter-panel{
      border: 1px solid var(--stroke);
      border-radius: var(--radius-lg);
      background: rgba(255,255,255,.04);
      box-shadow: var(--shadow-soft);
      padding: 16px;
      margin-bottom: 18px;
    }
    .filter-panel label{ font-weight: 800; color: var(--txt) !important; margin-bottom: 5px; }

    /* form controls */
    .form-control{
      border: 1px solid var(--stroke) !important;
      border-radius: 14px !important;
      padding: 10px 12px;
      font-weight: 700;
      color: var(--txt) !important;
      background: rgba(255,255,255,.06) !important;
    }
    .form-control::placeholder{ color: rgba(255,255,255,.50) !important; }
    .form-control:focus{
      border-color: var(--brand) !important;
      box-shadow: 0 0 0 .2rem rgba(79,124,255,.18) !important;
    }

    /* buttons */
    .btn{
      border-radius: 14px !important;
      padding: 10px 22px !important;
      font-weight: 900 !important;
      border: 1px solid rgba(255,255,255,.14) !important;
      box-shadow: 0 10px 24px rgba(0,0,0,.25);
    }
    .btn-light{ background: rgba(255,255,255,.12) !important; color: #fff !important; }
    .btn-light:hover{ background: rgba(255,255,255,.18) !important; color: #fff !important; }
    .btn-secondary{ background: rgba(255,255,255,.08) !important; color: var(--txt) !important; border-color: rgba(255,255,255,.12) !important; }

    /* table */
    .table-responsive{
      border: 1px solid var(--stroke);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-soft);
      overflow-x: auto;
      width: 100%;
      background: transparent;
    }
    #tblVeredas{ margin-bottom: 0; }
    #tblVeredas thead th{
      background: rgba(255,255,255,.04) !important;
      color: var(--txt) !important;
      border-bottom: 1px solid var(--stroke) !important;
      white-space: nowrap;
      font-weight: 1000 !important;
      vertical-align: middle;
      font-size: .92rem;
      padding: 10px 10px;
    }
    #tblVeredas tbody td{
      color: var(--txt) !important;
      font-weight: 700;
      border-top: 1px solid var(--stroke) !important;
      vertical-align: middle !important;
      padding: 9px 10px;
    }
    #tblVeredas tbody tr{ background: transparent !important; }
    #tblVeredas tbody tr:hover{ background: rgba(255,255,255,.05) !important; }

    /* custom pagination */
    #infoRegistros{ font-size:.88rem; font-weight:700; color: var(--txt); }
    #paginacion .btn-outline-light{
      background: rgba(255,255,255,.06) !important;
      border: 1px solid rgba(255,255,255,.10) !important;
      color: var(--txt) !important;
      border-radius: 8px !important;
      padding: 4px 10px !important;
      font-size: 12px !important;
      font-weight: 700 !important;
      box-shadow: none !important;
    }
    #paginacion .btn-outline-light:hover{
      background: rgba(255,255,255,.12) !important;
      border-color: rgba(255,255,255,.20) !important;
      color: #fff !important;
    }
    #paginacion .btn-primary{
      background: rgba(31,111,235,.35) !important;
      border: 1px solid rgba(31,111,235,.50) !important;
      color: #fff !important;
      border-radius: 8px !important;
      padding: 4px 10px !important;
      font-size: 12px !important;
      font-weight: 700 !important;
      box-shadow: none !important;
    }
    #paginacion .btn-secondary.disabled,
    #paginacion .btn-secondary:disabled{
      background: transparent !important;
      border: 1px solid transparent !important;
      color: rgba(255,255,255,.30) !important;
      opacity: 1 !important;
      box-shadow: none !important;
    }

    /* badges */
    .badge-activo   { background: #28a745; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: .8rem; font-weight: 700; }
    .badge-inactivo { background: #6c757d; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: .8rem; font-weight: 700; }
    .badge-null     { background: rgba(255,255,255,.10); color: rgba(255,255,255,.60); padding: 3px 10px; border-radius: 20px; font-size: .8rem; font-weight: 700; }

    /* modal */
    .modal-content{ border-radius: var(--radius-xl) !important; overflow: hidden; }
    .modal-header{ background: linear-gradient(135deg, rgba(79,124,255,.40), rgba(155,92,255,.25)) !important; border-bottom: 1px solid rgba(255,255,255,.14) !important; }
    .modal-header h5{ color: #fff !important; }
    .modal .close{ color: #fff !important; opacity: .95; text-shadow: none; }
    .modal-body{ background: transparent !important; color: var(--txt) !important; padding: 20px !important; }
    .modal-footer{ background: transparent !important; border-top: 1px solid var(--stroke) !important; }
    .modal-body label{ font-weight: 800; color: var(--txt); }
  </style>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <!-- Breadcrumb -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h5 class="mb-0">Gestión de Veredas — Santander</h5>
                <?php include './admin/include/btn_back.php'; ?>
              </div>
              <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html"><i class="feather icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="#!">Configuración / Veredas</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card saas-card">

            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h5><i class="feather icon-map-pin me-2"></i> Veredas · Departamento de Santander</h5>
              <button class="btn btn-sm btn-light" onclick="abrirModalNueva()"
                style="border-radius:10px; font-weight:700; padding:6px 16px;">
                <i class="feather icon-plus me-1"></i> Nueva vereda
              </button>
            </div>

            <div class="card-body">

              <!-- Filtros -->
              <div class="filter-panel">
                <div class="row g-3 align-items-end">
                  <div class="col-12 col-md-4">
                    <label for="filtroMunicipio">Filtrar por Municipio</label>
                    <select id="filtroMunicipio" class="form-control" onchange="cargarTabla()">
                      <option value="">Todos los municipios</option>
                    </select>
                  </div>
                  <div class="col-12 col-md-4">
                    <label for="filtroBusqueda">Buscar por nombre o código</label>
                    <input type="text" id="filtroBusqueda" class="form-control"
                      placeholder="Ej: LA HONDA o 68001001" oninput="cargarTabla()">
                  </div>
                  <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()"
                      style="border-radius:14px; font-weight:700;">
                      <i class="feather icon-refresh-cw me-1"></i> Limpiar
                    </button>
                  </div>
                </div>
              </div>

              <!-- Tabla -->
              <div class="table-responsive tabla-informacion tabla-scroll">
                <table class="table table-hover mb-0" id="tblVeredas">
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
              <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2" id="paginacionWrapper">
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
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="tituloModal">Nueva Vereda</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="veredaId">
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
            <button type="button" class="btn btn-secondary" data-dismiss="modal"
              style="border-radius:12px;font-weight:700;">Cancelar</button>
            <button type="button" class="btn btn-primary" onclick="guardarVereda()"
              style="border-radius:12px;font-weight:700;background:var(--au-primary);border-color:var(--au-primary);">
              <i class="feather icon-save me-1"></i> Guardar
            </button>
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
                <style>
      table.dataTable tbody tr{
        background-color: transparent !important;
      }
      table.dataTable.stripe tbody tr.odd,
      table.dataTable.display tbody tr.odd{
        background-color: rgba(255,255,255,.03) !important;
      }
      table.dataTable tbody td{
        color: rgba(255,255,255,.86) !important;
      }
      table.dataTable tbody td a{
        color: rgba(255,255,255,.86) !important;
      }
      table.dataTable tbody td i.feather,
      table.dataTable tbody td i.bi{
        color: rgba(255,255,255,.86) !important;
      }
      #tblVeredas td i.feather{
        color: rgba(255,255,255,.86) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button{
        color: rgba(255,255,255,.86) !important;
        background: rgba(255,255,255,.06) !important;
        border: 1px solid rgba(255,255,255,.10) !important;
        border-radius: 8px !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.current,
      .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
        color: #fff !important;
        background: rgba(31,111,235,.35) !important;
        border: 1px solid rgba(31,111,235,.50) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        color: #fff !important;
        background: rgba(255,255,255,.12) !important;
        border: 1px solid rgba(255,255,255,.20) !important;
      }
      .dataTables_wrapper .dataTables_paginate .paginate_button.disabled{
        color: rgba(255,255,255,.30) !important;
        background: transparent !important;
        border: 1px solid transparent !important;
      }
      .dataTables_wrapper .dataTables_info,
      .dataTables_wrapper .dataTables_length label{
        color: #fff !important;
      }
      table.dataTable tbody tr.selected{
        background-color: rgba(31,111,235,.25) !important;
      }
    </style>



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
