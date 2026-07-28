/**
 * Control de Compromisos para Alcalde
 * Utiliza tbl_compromisos_alcalde con vereda en lugar de provincia
 */

let compromiso = null;
let chartIndicadores = null;

$(function () {
  // Carga la tabla al cargar la página
  cargarCompromiso();
  secretarias();
  ciudades();

  // Filtro de búsqueda
  $("#customSearch").on("keyup", function () {
    if (compromiso) {
      compromiso.search(this.value).draw();
    }
  });

  // Cambiar municipio para cargar veredas
  $("#municipioFiltro").on("change", function () {
    const municipioId = $(this).val();
    if (municipioId) {
      cargarVeredas(municipioId);
    } else {
      $("#veredaFiltro").empty().append('<option value="">Seleccione primero un municipio</option>');
    }
  });
});

// Cargar tabla de compromisos
function cargarCompromiso() {
  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  // Obtener filtro de municipio si es usuario municipal
  const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
  const municipioUsuario = $('#municipioUsuario').val();

  console.log('🔍 Cargar Compromiso - Debug:');
  console.log('  isUsuarioMunicipal:', isUsuarioMunicipal);
  console.log('  municipioUsuario:', municipioUsuario);

  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip",
    processing: true,
    serverSide: false, // Cambiar a false para usar datos locales
    responsive: true,
    destroy: true,
    rowCallback: function (row, data) {
      if (data.cumplimiento && data.cumplimiento.toLowerCase().includes("cumplido")) {
        $(row).addClass("d-none");
      }
    },
    columnDefs: [{ targets: ["_all"], className: "mdc-data-table__cell" }],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        const filtros = {};

        // Si es usuario municipal, aplicar filtro automáticamente
        if (isUsuarioMunicipal && municipioUsuario) {
          filtros.tbl_municipio_id = municipioUsuario;
        }

        const payload = {
          method: "getAllCompromisos",
          data: filtros,
        };

        console.log('📤 Enviando al servidor:', payload);

        return JSON.stringify(payload);
      },
      dataSrc: function (json) {
        console.log('📥 Respuesta del servidor:', json);
        if (json.output && json.output.valid) {
          console.log('  Total de registros:', json.output.response.length);
          return json.output.response;
        }
        console.log('⚠️ No hay datos válidos en la respuesta');
        return [];
      },
    },
    columns: [
      { data: "id" },
      { data: "secretaria" },
      { data: "compromisos", visible: true, render: renderCompromisoBtn },
      { data: "compromiso_pactado", visible: false, render: renderCompromisoBtn },
      { data: "consecuencia", visible: false, render: renderCompromisoBtn },
      { data: "respuesta", visible: false, render: renderCompromisoBtn },
      {
        data: "cumplimiento",
        render: function (data, type, row) {
          if (!data)
            return `<span style="background:#6c757d;color:#fff;padding:4px 10px;border-radius:4px;font-weight:600;">SIN ESTADO</span>`;

          const estado = data.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
          let bg = "#6c757d";
          let color = "#fff";

          if (estado.includes("cumplido")) {
            bg = "#28a745";
          } else if (estado.includes("tramite")) {
            bg = "#ffc107";
            color = "#212529";
          } else if (estado.includes("sin cumplir")) {
            bg = "#dc3545";
          }

          return `<span style="
            background-color:${bg} !important;
            font-size:13px;
            color:${color} !important;
            padding:4px 10px;
            border-radius:4px;
            font-weight:600;
            display:inline-block;
            text-align:center;
            min-width:90px;">
            ${data.toUpperCase()}
          </span>`;
        },
      },
      { data: "municipio" },
      { data: "vereda", defaultContent: "Sin vereda" }, // CAMBIO: provincia → vereda
      { data: "componente" },
      { data: "tipo_ejecucion" },
      {
        data: "img",
        orderable: false,
        searchable: false,
        render: function (data, type) {
          if (type !== "display" || !data)
            return `<span class="text-muted">Sin adjunto</span>`;
          return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('assets/img/admin/${data}', '')"></i>`;
        },
      },
      {
        data: "date",
        render: function (data) {
          if (!data) return "";
          return new Date(data).toLocaleDateString('es-CO');
        }
      },
      {
        data: "id",
        render: function (data, type, row) {
          if (row.cumplimiento == "Cumplido") {
            return "";
          }
          return `<button class="btn btn-sm btn-transparent" onclick="editaCompromiso('${data}');"><i class="feather icon-edit"></i></button>`;
        },
      },
      {
        data: "id",
        render: function (data) {
          return `<button class="btn btn-sm btn-success4" onclick="verDetalleCompromiso(${data})">
            <i class="feather icon-eye"></i>
          </button>`;
        },
      },
    ],
  });
}

// Render botón "Ver" para compromisos
function renderCompromisoBtn(data, type) {
  if (type === "display") {
    const seguro = (data ?? "")
      .toString()
      .replace(/"/g, "&quot;")
      .replace(/'/g, "\\'");
    return `<button class="btn btn-sm btn-link text-primary" onclick="verCompromiso('${seguro}')">Ver</button>`;
  }
  return data ?? "";
}

function indicadores() {
  var munId = document.getElementById('municipioUsuario') ? document.getElementById('municipioUsuario').value : '';

  $.ajax({
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "getIndicadoresCompromisosSecretaria", data: { tbl_municipio_id: munId } }),
    dataType: "json",
    contentType: "application/json",
    success: function (response) {
      const contenedor = $("#indicadoresContainer");
      contenedor.empty();

      if (response.output && response.output.valid && response.output.response.length) {
        const data = response.output.response;
        const labels = data.map(item => item.secretaria);
        const cumplidos = data.map(item => parseInt(item.cumplidos));
        const enTramite = data.map(item => parseInt(item.en_tramite));
        const sinCumplir = data.map(item => parseInt(item.sin_cumplir));

        contenedor.append(`
          <div style="max-width:900px;margin:0 auto;">
            <canvas id="graficoGeneral" height="320"></canvas>
          </div>
        `);

        const ctx = document.getElementById("graficoGeneral").getContext("2d");
        new Chart(ctx, {
          type: "bar",
          data: {
            labels: labels,
            datasets: [
              { label: "Cumplidos", data: cumplidos, backgroundColor: "#34d399CC", borderRadius: 6 },
              { label: "En Trámite", data: enTramite, backgroundColor: "#fbbf24CC", borderRadius: 6 },
              { label: "Sin Cumplir", data: sinCumplir, backgroundColor: "#fb7185CC", borderRadius: 6 },
            ],
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                position: "bottom",
                labels: { color: "#e2e8f0", font: { weight: "bold", size: 11 }, boxWidth: 14, padding: 14 }
              }
            },
            scales: {
              x: {
                grid: { color: "rgba(255,255,255,.05)" },
                ticks: { color: "#cbd5e1", font: { weight: "bold", size: 10 }, maxRotation: 45 }
              },
              y: {
                beginAtZero: true,
                grid: { color: "rgba(255,255,255,.05)" },
                ticks: { color: "#94a3b8", font: { weight: "bold", size: 10 }, precision: 0 }
              },
            },
          },
        });
      } else {
        contenedor.append('<p style="color:rgba(255,255,255,.6);font-weight:800;text-align:center;padding:30px;">No hay datos de compromisos para este municipio.</p>');
      }
    },
    error: function () {
      $("#indicadoresContainer").html('<p style="color:rgba(255,255,255,.6);font-weight:800;text-align:center;padding:30px;">Error al cargar indicadores.</p>');
    },
  });
}

// Ver compromiso en modal
function verCompromiso(data) {
  $("#contenidoCompromiso").text(data);
  $("#modalCompromiso").modal("show");
}

// Ver imagen o PDF adjunto
function mostrarAdjuntosModal(imagenSrc, pdfSrc) {
  let html = "";

  if (imagenSrc) {
    html = `<img src="${imagenSrc}" class="img-fluid" style="max-height: 80vh;">`;
  } else if (pdfSrc) {
    html = `<iframe src="${pdfSrc}" width="100%" height="600px" frameborder="0"></iframe>`;
  } else {
    html = `<p class="text-muted">No hay archivo para mostrar.</p>`;
  }

  $("#contenidoAdjunto").html(html);
  $("#modalAdjunto").modal("show");
}

function ciudades() {
  return new Promise((resolve) => {
    const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
    const municipioUsuario = $('#municipioUsuario').val();

    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({ method: "ciudades", data: 68 }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $selectMunicipioFiltro = $("#municipioFiltro");
        $selectMunicipioFiltro.empty();

        // Si es usuario municipal, solo mostrar su municipio
        if (isUsuarioMunicipal && municipioUsuario) {
          response.data.forEach(function (ciudad) {
            if (ciudad.codigo_muncipio === municipioUsuario) {
              $selectMunicipioFiltro.append(
                `<option value="${ciudad.codigo_muncipio}" selected>${ciudad.municipio}</option>`
              );
            }
          });
          // Deshabilitar el select para que no pueda cambiarlo
          $selectMunicipioFiltro.prop('disabled', true);

          // Cargar veredas automáticamente del municipio del usuario
          cargarVeredas(municipioUsuario).then(() => {
            resolve();
          });
        } else {
          // Admin: mostrar todos los municipios
          $selectMunicipioFiltro.append('<option value="" selected>Todos</option>');
          response.data.forEach(function (ciudad) {
            $selectMunicipioFiltro.append(
              `<option value="${ciudad.codigo_muncipio}">${ciudad.municipio}</option>`
            );
          });
          resolve();
        }
      },
    });
  });
}

// NUEVA FUNCIÓN: Cargar veredas por municipio
function cargarVeredas(municipioId) {
  if (!municipioId) {
    $("#veredaFiltro").empty().append('<option value="">Seleccione</option>');
    return Promise.resolve();
  }

  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({
        method: "getVeredasByMunicipioId",
        data: { municipio_id: municipioId }
      }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#veredaFiltro");
        $select.empty();
        $select.append('<option value="">Todas</option>');

        const veredas = response.output?.response || [];

        if (veredas.length > 0) {
          veredas.forEach(function (vereda) {
            $select.append(
              `<option value="${vereda.id}">${vereda.nombre_vereda}</option>`
            );
          });
        }
        resolve();
      },
      error: function() {
        $("#veredaFiltro").empty().append('<option value="">Error al cargar veredas</option>');
        resolve();
      }
    });
  });
}

function secretarias() {
  return new Promise((resolve) => {
    const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
    const municipioUsuario = $('#municipioUsuario').val();

    // Si es usuario municipal, cargar solo secretarías de su municipio
    if (isUsuarioMunicipal && municipioUsuario) {
      $.ajax({
        url: "./admin/ajax/rqst.php",
        type: "POST",
        data: { op: "secretariasmunicipalespormunicipio", codigo_municipio: municipioUsuario },
        dataType: "json",
        success: function (response) {
          const $selectFiltroTabla = $("#secretariaIdFiltro");
          $selectFiltroTabla.empty();
          $selectFiltroTabla.append('<option value="" selected>Todas</option>');

          if (response.output && response.output.valid) {
            response.output.response.forEach(function (secretaria) {
              $selectFiltroTabla.append(
                `<option value="${secretaria.id}">${secretaria.secretaria}</option>`
              );
            });
          }
          resolve();
        },
        error: function() {
          $("#secretariaIdFiltro").empty().append('<option value="">Error al cargar secretarías</option>');
          resolve();
        }
      });
    } else {
      // Admin: cargar todas las secretarías
      $.ajax({
        url: "./admin/controllers/utilsCtrl.php",
        type: "POST",
        data: JSON.stringify({ method: "secretaria" }),
        dataType: "json",
        contentType: "application/json",
        success: function (response) {
          const $selectFiltroTabla = $("#secretariaIdFiltro");
          $selectFiltroTabla.empty();
          $selectFiltroTabla.append('<option value="" selected>Todas</option>');

          response.data.forEach(function (secretaria) {
            $selectFiltroTabla.append(
              `<option value="${secretaria.id}">${secretaria.secretaria}</option>`
            );
          });
          resolve();
        },
      });
    }
  });
}

// Filtrar tabla con veredas
function filtrarTabla() {
  const veredaId = document.getElementById("veredaFiltro") ? document.getElementById("veredaFiltro").value : "";
  const municipio = document.getElementById("municipioFiltro").value;
  const secretariaId = document.getElementById("secretariaIdFiltro").value;
  const componente = document.getElementById("componenteFiltro").value;
  const estado = document.getElementById("estadoFiltro") ? document.getElementById("estadoFiltro").value : "";

  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip",
    processing: true,
    serverSide: false,
    responsive: true,
    destroy: true,
    rowCallback: function (row, data) {
      if (data.cumplimiento && data.cumplimiento.toLowerCase().includes("cumplido")) {
        $(row).addClass("d-none");
      }
    },
    columnDefs: [{ targets: ["_all"], className: "mdc-data-table__cell" }],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        const filtros = {};
        if (secretariaId) filtros.tbl_secretarias_id = secretariaId;
        if (componente) filtros.componente = componente;
        if (municipio) filtros.tbl_municipio_id = municipio;
        if (veredaId) filtros.tbl_vereda_id = veredaId;
        if (estado) filtros.cumplimiento = estado;

        return JSON.stringify({
          method: "getAllCompromisos",
          data: filtros,
        });
      },
      dataSrc: function (json) {
        if (json.output && json.output.valid) {
          return json.output.response;
        }
        return [];
      },
    },
    columns: [
      { data: "id" },
      { data: "secretaria" },
      { data: "compromisos", visible: true, render: renderCompromisoBtn },
      { data: "compromiso_pactado", visible: false, render: renderCompromisoBtn },
      { data: "consecuencia", visible: false, render: renderCompromisoBtn },
      { data: "respuesta", visible: false, render: renderCompromisoBtn },
      {
        data: "cumplimiento",
        render: function (data) {
          if (!data)
            return `<span style="background:#6c757d;color:#fff;padding:4px 10px;border-radius:4px;font-weight:600;">SIN ESTADO</span>`;

          const estado = data.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
          let bg = "#6c757d";
          let color = "#fff";

          if (estado.includes("cumplido")) {
            bg = "#28a745";
          } else if (estado.includes("tramite")) {
            bg = "#ffc107";
            color = "#212529";
          } else if (estado.includes("sin cumplir")) {
            bg = "#dc3545";
          }

          return `<span style="
            background-color:${bg} !important;
            font-size:13px;
            color:${color} !important;
            padding:4px 10px;
            border-radius:4px;
            font-weight:600;
            display:inline-block;
            text-align:center;
            min-width:90px;">
            ${data.toUpperCase()}
          </span>`;
        },
      },
      { data: "municipio" },
      { data: "vereda", defaultContent: "Sin vereda" },
      { data: "componente" },
      { data: "tipo_ejecucion" },
      {
        data: "img",
        orderable: false,
        searchable: false,
        render: function (data, type) {
          if (type !== "display" || !data)
            return `<span class="text-muted">Sin adjunto</span>`;
          return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('assets/img/admin/${data}', '')"></i>`;
        },
      },
      {
        data: "date",
        render: function (data) {
          if (!data) return "";
          return new Date(data).toLocaleDateString('es-CO');
        }
      },
      {
        data: "id",
        render: function (data, type, row) {
          if (row.cumplimiento == "Cumplido") {
            return "";
          }
          return `<button class="btn btn-sm btn-transparent" onclick="editaCompromiso('${data}');"><i class="feather icon-edit"></i></button>`;
        },
      },
      {
        data: "id",
        render: function (data) {
          return `<button class="btn btn-sm btn-success4" onclick="verDetalleCompromiso(${data})">
            <i class="feather icon-eye"></i>
          </button>`;
        },
      },
    ],
  });
}

// Editar compromiso
function editaCompromiso(id) {
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    data: JSON.stringify({
      method: "getAllCompromisos",
      data: { id: parseInt(id) }
    }),
    dataType: "json",
    contentType: "application/json",
    success: async function (response) {
      if (!response.output || !response.output.valid || !response.output.response.length) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudo cargar el compromiso",
        });
        return;
      }

      const compromiso = response.output.response[0];

      // Remover modal si existe
      $("#modalEditCompromiso").remove();

      const modalHTML = `
        <div class="modal fade" id="modalEditCompromiso" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content shadow">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Editar Compromiso</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                  <span>&times;</span>
                </button>
              </div>

              <div class="modal-body" style="padding:20px">
                <form id="formEditCompromiso">
                  <input type="hidden" id="edit_id" value="${compromiso.id}">

                  <div class="form-row">
                    <div class="form-group col-md-4">
                      <label for="edit_fecha">Fecha</label>
                      <input type="date" class="form-control" id="edit_fecha" readonly
                        value="${compromiso.date ? compromiso.date.split(' ')[0] : ''}">
                    </div>

                    <div class="form-group col-md-4">
                      <label for="edit_municipio">Municipio</label>
                      <input type="text" class="form-control" id="edit_municipio" readonly
                        value="${window.munNombre || ''}" style="background:rgba(255,255,255,.06);color:rgba(255,255,255,.7);cursor:not-allowed;">
                      <input type="hidden" id="edit_municipio_id" value="${compromiso.tbl_municipio_id || ''}">
                    </div>

                    <div class="form-group col-md-4">
                      <label for="edit_vereda">Vereda</label>
                      <select class="form-control" id="edit_vereda">
                        <option value="">Seleccione primero un municipio</option>
                      </select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="edit_secretaria">Secretaría <span class="text-danger">*</span></label>
                      <select class="form-control" id="edit_secretaria" required>
                        <option value="">Seleccione</option>
                      </select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="edit_componente">Componente</label>
                      <select class="form-control" id="edit_componente">
                        <option value="">Seleccione</option>
                      </select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="edit_tipo_ejecucion">Tipo Ejecución</label>
                      <select class="form-control" id="edit_tipo_ejecucion">
                        <option value="INVERSIÓN" ${compromiso.tipo_ejecucion === 'INVERSIÓN' ? 'selected' : ''}>INVERSIÓN</option>
                        <option value="GESTIÓN" ${compromiso.tipo_ejecucion === 'GESTIÓN' ? 'selected' : ''}>GESTIÓN</option>
                      </select>
                    </div>

                    <div class="form-group col-md-12">
                      <label for="edit_compromisos">Compromiso <span class="text-danger">*</span></label>
                      <textarea class="form-control" id="edit_compromisos" rows="3" required>${compromiso.compromisos || ''}</textarea>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="edit_compromiso_pactado">Compromiso Pactado</label>
                      <textarea class="form-control" id="edit_compromiso_pactado" rows="3">${compromiso.compromiso_pactado || ''}</textarea>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="edit_consecuencia">Consecuencia</label>
                      <textarea class="form-control" id="edit_consecuencia" rows="3">${compromiso.consecuencia || ''}</textarea>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="edit_respuesta">Respuesta</label>
                      <textarea class="form-control" id="edit_respuesta" rows="3">${compromiso.respuesta || ''}</textarea>
                    </div>

                    <div class="form-group col-md-3">
                      <label for="edit_cumplimiento">Estado Cumplimiento</label>
                      <select class="form-control" id="edit_cumplimiento">
                        <option value="En Trámite" ${compromiso.cumplimiento === 'En Trámite' ? 'selected' : ''}>En Trámite</option>
                        <option value="Cumplido" ${compromiso.cumplimiento === 'Cumplido' ? 'selected' : ''}>Cumplido</option>
                        <option value="Sin Cumplir" ${compromiso.cumplimiento === 'Sin Cumplir' ? 'selected' : ''}>Sin Cumplir</option>
                      </select>
                    </div>
                    <div class="form-group col-md-3">
                      <label for="edit_estado">Estado Aprobación</label>
                      <select class="form-control" id="edit_estado">
                        <option value="">— Sin cambio —</option>
                        <option value="En Espera" ${compromiso.estado === 'En Espera' ? 'selected' : ''}>En Espera</option>
                        <option value="Aprobado" ${compromiso.estado === 'Aprobado' ? 'selected' : ''}>Aprobado</option>
                        <option value="Rechazado" ${compromiso.estado === 'Rechazado' ? 'selected' : ''}>Rechazado</option>
                      </select>
                      <small class="text-muted" style="color:rgba(255,255,255,.55) !important;">Poner "En Espera" para que aparezca en aprobación</small>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="edit_imagen">Imagen Actual</label>
                      <div id="previewImagenActual" class="mt-2">
                        ${compromiso.img ?
                          `<img src="assets/img/admin/${compromiso.img}" class="img-fluid mb-2" style="max-height: 200px;">` :
                          `<p class="text-muted">No hay imagen</p>`
                        }
                      </div>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="edit_nueva_imagen">Nueva Imagen (opcional)</label>
                      <input type="file" class="form-control-file" id="edit_nueva_imagen" accept="image/*">
                      <div id="previewNuevaImagen" class="mt-2"></div>
                    </div>
                  </div>
                </form>
              </div>

              <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarEdicionCompromiso()">
                  <i class="feather icon-save"></i> Guardar Cambios
                </button>
              </div>
            </div>
          </div>
        </div>
      `;

      $("body").append(modalHTML);
      $("#modalEditCompromiso").modal("show");

      // Cargar datos y establecer valores
      await cargarSecretariasEdit();
      $("#edit_secretaria").val(compromiso.tbl_secretarias_id);

      await cargarComponentesEdit();
      $("#edit_componente").val(compromiso.componente);

      // Cargar veredas del municipio
      if (compromiso.tbl_municipio_id) {
        await cargarVeredasEdit(compromiso.tbl_municipio_id);
        $("#edit_vereda").val(compromiso.tbl_vereda_id);
      }

      // Preview de nueva imagen
      $("#edit_nueva_imagen").on("change", function () {
        const file = this.files[0];
        if (file && file.type.startsWith("image/")) {
          const reader = new FileReader();
          reader.onload = function (e) {
            $("#previewNuevaImagen").html(
              `<img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">`
            );
          };
          reader.readAsDataURL(file);
        }
      });

      // Cambio de municipio recarga veredas
      $("#edit_municipio").on("change", function () {
        const municipioId = $(this).val();
        if (municipioId) {
          cargarVeredasEdit(municipioId);
        }
      });
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de red",
        text: "No se pudo comunicar con el servidor.",
      });
    },
  });
}

// Cargar municipios para el modal de edición
function cargarMunicipiosEdit() {
  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({ method: "ciudades", data: 68 }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#edit_municipio");
        $select.empty();
        $select.append('<option value="">Seleccione</option>');
        response.data.forEach(function (ciudad) {
          $select.append(
            `<option value="${ciudad.codigo_muncipio}">${ciudad.municipio}</option>`
          );
        });
        resolve();
      },
    });
  });
}

// Cargar secretarías para el modal de edición
function cargarSecretariasEdit() {
  const munId = document.getElementById('municipioUsuario') ? document.getElementById('municipioUsuario').value : '';
  return new Promise((resolve) => {
    if (!munId) { resolve(); return; }
    $.ajax({
      url: "./admin/ajax/rqst.php",
      type: "POST",
      data: { op: "secretariasmunicipalespormunicipio", codigo_municipio: munId },
      dataType: "json",
      success: function (response) {
        const $select = $("#edit_secretaria");
        $select.empty();
        $select.append('<option value="">Seleccione</option>');
        const rows = response.output && response.output.response ? response.output.response : (response.data || []);
        rows.forEach(function (s) {
          $select.append(`<option value="${s.id}">${s.secretaria}</option>`);
        });
        resolve();
      },
      error: function () { resolve(); }
    });
  });
}

// Cargar componentes del municipio para el modal de edición
function cargarComponentesEdit() {
  const munId = document.getElementById('municipioUsuario') ? document.getElementById('municipioUsuario').value : '';
  return new Promise((resolve) => {
    if (!munId) { resolve(); return; }
    $.ajax({
      url: "./admin/ajax/rqst.php",
      type: "POST",
      data: { op: "getComponentesPorMunicipio", codigo_municipio: munId },
      dataType: "json",
      success: function (response) {
        const $select = $("#edit_componente");
        $select.empty();
        $select.append('<option value="">Seleccione</option>');
        const rows = response.output && response.output.response ? response.output.response : (response.data || []);
        rows.forEach(function (c) {
          var name = typeof c === 'string' ? c : (c.nombre_componente || '');
          if (name) $select.append('<option value="' + name + '">' + name + '</option>');
        });
        resolve();
      },
      error: function () { resolve(); }
    });
  });
}

// Cargar veredas para el modal de edición
function cargarVeredasEdit(municipioId) {
  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({
        method: "getVeredasByMunicipioId",
        data: { municipio_id: municipioId },
      }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#edit_vereda");
        $select.empty();
        $select.append('<option value="">Ninguna</option>');

        const veredas = response.output?.response || [];
        veredas.forEach(function (vereda) {
          $select.append(
            `<option value="${vereda.id}">${vereda.nombre_vereda}</option>`
          );
        });
        resolve();
      },
    });
  });
}

// Guardar edición de compromiso
function guardarEdicionCompromiso() {
  const formData = new FormData();

  formData.append("method", "actualizarCompromiso");
  formData.append("id", $("#edit_id").val());
  formData.append("compromisos", $("#edit_compromisos").val());
  formData.append("compromiso_pactado", $("#edit_compromiso_pactado").val());
  formData.append("consecuencia", $("#edit_consecuencia").val());
  formData.append("respuesta", $("#edit_respuesta").val());
  formData.append("cumplimiento", $("#edit_cumplimiento").val());
  formData.append("estado", $("#edit_estado").val());
  formData.append("tbl_municipio_id", $("#edit_municipio_id").val());
  formData.append("tbl_vereda_id", $("#edit_vereda").val() || '');
  formData.append("componente", $("#edit_componente").val());
  formData.append("tipo_ejecucion", $("#edit_tipo_ejecucion").val());
  formData.append("tbl_secretarias_id", $("#edit_secretaria").val());

  const newImage = $("#edit_nueva_imagen")[0].files[0];
  if (newImage) {
    formData.append("imagen", newImage);
  }

  $.ajax({
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.output && response.output.valid) {
        Swal.fire({
          icon: "success",
          title: "¡Actualizado!",
          text: "Compromiso actualizado correctamente.",
          confirmButtonColor: "#28a745",
        });
        cargarCompromiso();
        $("#modalEditCompromiso").modal("hide");
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: response.output?.response || "Ocurrió un problema.",
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de red",
        text: "No se pudo comunicar con el servidor.",
      });
    },
  });
}

// Ver detalle de compromiso
function verDetalleCompromiso(id) {
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    data: JSON.stringify({
      method: "getAllCompromisos",
      data: { id: parseInt(id) }
    }),
    dataType: "json",
    contentType: "application/json",
    success: function (response) {
      if (!response.output || !response.output.valid || !response.output.response.length) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudo cargar el compromiso",
        });
        return;
      }

      const c = response.output.response[0];

      const estadoBadge = c.cumplimiento === 'Cumplido' ?
        '<span class="badge badge-success">Cumplido</span>' :
        c.cumplimiento === 'En Trámite' ?
        '<span class="badge badge-warning">En Trámite</span>' :
        '<span class="badge badge-danger">Sin Cumplir</span>';

      const html = `
        <div class="row">
          <div class="col-md-6">
            <p><strong>Municipio:</strong> ${c.municipio || 'N/A'}</p>
            <p><strong>Vereda:</strong> ${c.vereda || 'N/A'}</p>
            <p><strong>Secretaría:</strong> ${c.secretaria || 'N/A'}</p>
            <p><strong>Componente:</strong> ${c.componente || 'N/A'}</p>
            <p><strong>Tipo Ejecución:</strong> ${c.tipo_ejecucion || 'N/A'}</p>
            <p><strong>Estado:</strong> ${estadoBadge}</p>
            <p><strong>Fecha:</strong> ${c.date ? new Date(c.date).toLocaleDateString('es-CO') : 'N/A'}</p>
          </div>
          <div class="col-md-6">
            ${c.img ? `<img src="assets/img/admin/${c.img}" class="img-fluid" style="max-height: 300px;">` : ''}
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-12">
            <h6 class="font-weight-bold">Compromiso:</h6>
            <p>${c.compromisos || 'N/A'}</p>
          </div>
          ${c.compromiso_pactado ? `
          <div class="col-12">
            <h6 class="font-weight-bold">Compromiso Pactado:</h6>
            <p>${c.compromiso_pactado}</p>
          </div>` : ''}
          ${c.consecuencia ? `
          <div class="col-12">
            <h6 class="font-weight-bold">Consecuencia:</h6>
            <p>${c.consecuencia}</p>
          </div>` : ''}
          ${c.respuesta ? `
          <div class="col-12">
            <h6 class="font-weight-bold">Respuesta:</h6>
            <p>${c.respuesta}</p>
          </div>` : ''}
        </div>
      `;

      Swal.fire({
        title: "Detalle del Compromiso",
        html: html,
        width: '800px',
        confirmButtonText: 'Cerrar',
      });
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de red",
        text: "No se pudo comunicar con el servidor.",
      });
    },
  });
}
