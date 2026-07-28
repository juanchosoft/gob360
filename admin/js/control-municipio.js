let compromiso = null;
let chartIndicadores = null;

$(function () {
  // Carga la tabla al cargar la página (con timeout para evitar conflictos DOM)
  setTimeout(cargarCompromiso, 50);

  secretarias();

  ciudades();

  // Filtro de búsqueda
  $("#customSearch").on("keyup", function () {
    if (compromiso) {
      compromiso.search(this.value).draw();
    }
  });

  // Cambiar de secretaría y cargar gráfico
  $("#selectSecretaria").on("change", function () {
    const secretariaId = $(this).val();
    if (secretariaId) indicadores(secretariaId);
  });
});

// Cargar tabla de compromisos
function cargarCompromiso() {
  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  try {
  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip",
    processing: true,
    serverSide: true,
    responsive: false,
    destroy: true,
    ajax: {
      url: "./admin/controllers/compromisoMunicipioCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        return JSON.stringify({
          method: "getAllCompromise",
          data: d,
        });
      },
    },
    columns: [
      { data: "id" },
      { data: "secretaria" },
      { data: "compromisopac", visible: false, render: renderCompromisoBtn },
      { data: "consecuencia", visible: false, render: renderCompromisoBtn },
      { data: "respuesta", visible: false, render: renderCompromisoBtn },
      {
        data: "estado",
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
          } else if (estado.includes("espera")) {
            bg = "#17a2b8";
          } else if (estado.includes("sin cumplir") || estado.includes("por cumplir")) {
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
      { data: "provincia" },
      { data: "componente" },
      { data: "tipo_ejecucion" },
      {
        data: "foto",
        orderable: false,
        searchable: false,
        render: function (data, type) {
          if (type !== "display" || !data)
            return `<span class="text-muted">Sin adjunto</span>`;

          const imagenSrc = (data.match(/<img[^>]*src="([^"]+)"/i) || [])[1];
          const pdfSrc = (data.match(
            /mostrarArchivoModal\('([^']+\.pdf)'\)/i
          ) || [])[1];

          if (!imagenSrc && !pdfSrc) {
            return `<span class="text-muted">Sin adjunto</span>`;
          }

          return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('${
            imagenSrc || ""
          }', '${pdfSrc || ""}')"></i>`;
        },
      },
      { data: "date", render: function (d) { return d ? d.split(" ")[0] : ""; } },
      {
  data: "id",
  render: function (data, type, row) {
    

    if (row.estado && row.estado.toLowerCase().includes("cumplido")) {
      return "";
    }

    return `<button class="btn btn-sm btn-transparent" onclick="editaCompromiso('${data}');"><i class="feather icon-edit"></i></button>`;
  },
},

      {
        data: "id",
        render: function (data) {
          let html = `<form action="reporte-compromiso.php" method="POST" style="display:inline;" target="_blank">
            <input type="hidden" name="reporte" value="1">
            <input type="hidden" name="id" value="${data}">
            <button type="submit" class="btn btn-sm btn-success4">
              <i class="feather icon-eye"></i>
            </button></form>`;
          if (typeof isAdminOrSuper !== 'undefined' && isAdminOrSuper) {
            html += `<button class="btn btn-sm btn-outline-info ml-1" onclick="verHistorial(${data})" title="Ver historial de cambios">
              <i class="feather icon-clock"></i>
            </button>`;
          }
          return html;
        },
      },
      {
        data: "id",
        visible: typeof isAdminOrSuper !== 'undefined' && isAdminOrSuper,
        orderable: false,
        searchable: false,
        render: function (data) {
          return `<button class="btn btn-sm btn-danger" onclick="eliminarCompromiso(${data})" title="Eliminar compromiso">
            <i class="feather icon-trash-2"></i>
          </button>`;
        },
      },
    ],
  });
  } catch (e) { console.warn('cargarCompromiso DataTable:', e); }
}

// Render botón "Ver" para compromisos
function renderCompromisoBtn(data, type, row) {
  if (type === "display") {
    const id = row?.id ?? "";
    const seguro = (data ?? "").toString().replace(/"/g, "&quot;").replace(/'/g, "\\'");
    return `<form action="reporte-compromiso.php" method="POST" style="display:inline;" target="_blank">
      <input type="hidden" name="reporte" value="1">
      <input type="hidden" name="id" value="${id}">
      <button type="submit" class="btn btn-sm btn-link text-primary p-0">Ver</button>
    </form>`;
  }
  return data ?? "";
}

function indicadores() {
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "indicadores" }),
    dataType: "json",
    contentType: "application/json",
    success: function (response) {
      const contenedor = $("#indicadoresContainer");
      contenedor.empty();

      if (response.state && response.data.length) {
        const secretarias = [
          ...new Set(response.data.map((i) => i.secretaria)),
        ];
        const estados = [...new Set(response.data.map((i) => i.estado))];

        const colorByEstado = {
          cumplido: "rgba(46, 204, 113, 0.7)", // verde claro
          "sin cumplir": "rgba(231, 76, 60, 0.7)", // rojo claro
          "en tramite": "rgba(241, 196, 15, 0.7)", // amarillo claro
        };

        const datasets = estados.map((estado) => {
          const estadoKey = estado
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase();

          const data = secretarias.map((secretaria) => {
            const found = response.data.find(
              (i) => i.secretaria === secretaria && i.estado === estado
            );
            return found ? found.total : 0;
          });

          return {
            label: estado,
            data: data,
            backgroundColor:
              colorByEstado[estadoKey] || "rgba(52, 152, 219, 0.7)", // azul por defecto
            borderRadius: 6,
            borderSkipped: false,
            barPercentage: 0.7,
            categoryPercentage: 0.6,
          };
        });

        contenedor.append(`
          <div class="mb-4">
            <h5 class="mb-3">Resumen de Estados por Secretaría</h5>
            <canvas id="graficoGeneral" height="120"></canvas>
          </div>
        `);

        const ctx = document.getElementById("graficoGeneral").getContext("2d");
        new Chart(ctx, {
          type: "bar",
          data: {
            labels: secretarias,
            datasets: datasets,
          },
          options: {
            responsive: true,
            animation: {
              duration: 800,
              easing: "easeOutBounce",
            },
            plugins: {
              legend: { position: "top" },
              tooltip: {
                mode: "index",
                intersect: false,
              },
              title: { display: false },
            },
            scales: {
              x: {
                stacked: false,
                ticks: {
                  autoSkip: false,
                  maxRotation: 45,
                  minRotation: 0,
                  font: { size: 11 },
                },
              },
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0,
                  stepSize: 1,
                },
              },
            },
          },
        });
      } else {
        contenedor.append('<p class="text-muted">No hay datos</p>');
      }
    },
    error: function (xhr, status, error) {
      console.error("Error en indicadores:", status);
    },
  });
}

// Ver compromiso en modal
function verCompromiso(data) {
  $("#contenidoCompromiso").text(data);
  $("#modalCompromiso").modal("show");
}

// Ver historial de trazabilidad
function verHistorial(id) {
  $("#modalHistorial .modal-title").text('Historial de cambios - Compromiso #' + id);
  $("#contenidoHistorial").html('<div class="text-center"><i class="feather icon-loader spinner-border"></i> Cargando...</div>');
  $("#modalHistorial").modal("show");

  $.ajax({
    url: "./admin/controllers/compromisoMunicipioCtrl.php",
    type: "POST",
    contentType: "application/json",
    dataType: "json",
    data: JSON.stringify({ method: "getCompromisoHistorial", data: { id } })
  }).done(function(res){
    if(!res || !res.ok || !res.historial || !res.historial.length){
      $("#contenidoHistorial").html('<p class="text-muted text-center mb-0">No hay cambios registrados en este compromiso.</p>');
      return;
    }

    let html = `<div class="au-hist-wrap">`;

    res.historial.forEach(function(h){
      const campo = (h.campo || '').replace(/_/g, ' ');
      const usuario = ((h.usuario_nombre || '') + ' ' + (h.usuario_apellido || '')).trim() || 'Sistema';
      const nickname = h.usuario_nickname || '';
      const valorAnt = h.valor_anterior || '';
      const valorNew = h.valor_nuevo || '';
      const fecha = h.created_at || '';
      if (!valorAnt && !valorNew) return;

      html += `
        <article class="au-hist-card">
          <header class="au-hist-card__head">
            <div>
              <span class="au-hist-chip">${escHtml(campo)}</span>
            </div>
            <div class="au-hist-meta">
              <span><i class="feather icon-user"></i> ${escHtml(usuario)}${nickname ? ' <small class="text-muted">@' + escHtml(nickname) + '</small>' : ''}</span>
              <span><i class="feather icon-clock"></i> ${escHtml(fecha)}</span>
            </div>
          </header>
          <div class="au-hist-card__body">
            <div class="au-hist-col">
              <div class="au-hist-label">Valor anterior</div>
              <div class="au-hist-text">${valorAnt ? escHtml(valorAnt) : '<span class="text-muted">—</span>'}</div>
            </div>
            <div class="au-hist-col au-hist-col--new">
              <div class="au-hist-label">Valor nuevo</div>
              <div class="au-hist-text">${valorNew ? escHtml(valorNew) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>
        </article>`;
    });

    html += `</div>`;
    $("#contenidoHistorial").html(html);
  }).fail(function(){
    $("#contenidoHistorial").html('<p class="text-danger text-center mb-0">Error al cargar el historial.</p>');
  });
}

function escHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, function(m){
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
  });
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
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({ method: "ciudades", data: 68 }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#municipio");
        $select.empty();
        $select.append('<option value="">Seleccione</option>');


        const $selectMunicipioFiltro = $("#municipioFiltro");
        $selectMunicipioFiltro.empty();
        $selectMunicipioFiltro.append('<option value="" selected>Todos</option>');

        response.data.forEach(function (ciudad) {
          $select.append(
            `<option value="${ciudad.codigo_muncipio}">${ciudad.nombre_mapa}</option>`
          );
          $selectMunicipioFiltro.append(
            `<option value="${ciudad.codigo_muncipio}">${ciudad.municipio}</option>`
          );
        });
        resolve();
      },
    });
  });
}

function secretarias() {
  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({ method: "secretaria" }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#tbl_secretarias_id");
        $select.empty();
        $select.append('<option value="">Seleccione</option>');


        const $selectFiltroTabla = $("#secretariaIdFiltro");
        $selectFiltroTabla.empty();

        // Si es secretario de gobernación, NO mostrar opción "Todas"
        if (typeof esSecretarioGobernacion === 'undefined' || !esSecretarioGobernacion) {
          $selectFiltroTabla.append('<option value="" selected>Todas</option>');
        }

        response.data.forEach(function (ciudad) {
          $select.append(
            `<option value="${ciudad.id}">${ciudad.secretaria}</option>`
          );
          $selectFiltroTabla.append(
            `<option value="${ciudad.id}">${ciudad.secretaria}</option>`
          );
        });

        // Si es secretario de gobernación: fijar y bloquear su secretaría en el filtro
        if (typeof esSecretarioGobernacion !== 'undefined' && esSecretarioGobernacion && secretariaUsuarioId) {
          $selectFiltroTabla.val(String(secretariaUsuarioId)).prop('disabled', true);
        }

        resolve();
      },
    });
  });
}

function editaCompromiso(data) {
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "getCompromisoId", data: data }),
    dataType: "json",
    contentType: "application/json",
    success: async function (response) {
      if (!response || !response.data || !response.data.length) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se encontró el compromiso.' });
        return;
      }
      const compromisoId = response.data[0].id;
      $("#modalEditVisita").remove();

      let accionesTomadasHTML = "";
      if (
        response.data[0].respuesta &&
        response.data[0].respuesta.trim() !== ""
      ) {
        accionesTomadasHTML = `
                              <div class="form-group col-md-4">
                                <label for="acciones_tomadas">Observaciones ( Fin de la acción )</label>
                                <textarea class="form-control" id="acciones_tomadas" placeholder="Ingrese fin de acciones tomadas aquí" rows="3">${response.data[0].compromisos || ""
          }</textarea>
                              </div>
                            `;
      }
      const mostrarBotonActualizar = response.data[0].estado !== "Cumplido";

      const modalHTML = `
        <div class="modal fade" id="modalEditVisita" tabindex="-1" role="dialog" aria-labelledby="modalEditVisitaLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content shadow">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditVisitaLabel">Editar Compromiso #${compromisoId}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

              <div class="modal-body" style="padding:20px">
                <form id="formEditVisita">
                  <div class="form-row">
                    <input type="hidden" id="id" value="${
                      response.data[0].id || ""
                    }">
                    <input type="hidden" id="estado" value="${
                      response.data[0].estado || ""
                    }">

                    <div class="form-group col-md-4">
                      <label for="fecha">Fecha</label>
                      <input type="date" class="form-control" id="fecha" readonly value="${
                        response.data[0].date ? response.data[0].date.split(' ')[0] : ""
                      }">
                    </div>

                    <div class="form-group col-md-4">
                      <label for="provincia">Provincia <span class="text-danger">*</span></label>
                      <select class="form-control" id="provincia" name="provincia">
                        <option value="Seleccione">Seleccione</option>
                        <option value="Soto Norte">Soto Norte</option>
                        <option value="Guanenta">Guanentá</option>
                        <option value="Garcia Rovira">García Rovira</option>
                        <option value="Comunera">Comunera</option>
                        <option value="Velez">Vélez</option>
                        <option value="Metropolitana">Metropolitana</option>
                        <option value="Yariguies">Yariguíes</option>
                      </select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="municipio">Municipio</label>
                      <select class="form-control" id="municipio" name="municipio"></select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="tbl_secretarias_id">Secretaría</label>
                      <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id"></select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="compromisos">Compromiso pactado</label>
                      <textarea class="form-control" id="compromisos" rows="3" ${typeof isAdminOrSuper !== 'undefined' && !isAdminOrSuper ? 'readonly style="background-color:#e9ecef;cursor:not-allowed;"' : ''}>${
                        response.data[0].compromisopac || ""
                      }</textarea>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="respuesta">Respuesta</label>
                      <textarea class="form-control" id="respuesta" rows="3">${
                        response.data[0].respuesta || ""
                      }</textarea>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="observaciones">Acciones Tomadas en el proceso</label>
                      <textarea class="form-control" id="observaciones" placeholder="Ingrese nuevas observaciones aquí" rows="3"></textarea>
                    </div>

                    ${accionesTomadasHTML}

                    <div class="form-group col-md-4">
                      <label for="url">LINK (opcional)</label>
                      <input type="text" class="form-control" id="url" value="${
                        response.data[0].url || ""
                      }">
                    </div>

                    <div class="form-group col-md-4" id="componente-container" style="${typeof isAdminOrSuper !== 'undefined' && !isAdminOrSuper ? 'display:none;' : ''}">
                      <label for="componente">Componente <span class="text-danger">*</span></label>
                      <select class="form-control" id="componente" name="componente" ${typeof isAdminOrSuper !== 'undefined' && !isAdminOrSuper ? 'disabled' : ''}>
                        <option value="">Seleccione</option>
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
                        <option value="COMPROMISOS NUEVOS">COMPROMISOS NUEVOS</option>
                      </select>
                    </div>

                    <div class="form-group col-md-4" id="estado-container" style="${typeof isAdminOrSuper !== 'undefined' && !isAdminOrSuper ? 'display:none;' : ''}">
                      <label for="estado_editar">Estado <span class="text-danger">*</span></label>
                      <select class="form-control" id="estado_editar" name="estado_editar" ${typeof isAdminOrSuper !== 'undefined' && !isAdminOrSuper ? 'disabled' : ''}>
                        <option value="">Seleccione</option>
                        <option value="Sin Cumplir">SIN CUMPLIR</option>
                        <option value="Cumplido">CUMPLIDO</option>
                        <option value="En Trámite">EN TRÁMITE</option>
                      </select>
                    </div>

                    <!-- Imagen -->
                    <div class="form-group col-md-3">
                      <label for="img">Imagen</label>
                      <input type="file" class="form-control-file" id="img" accept="image/*">
                      <div id="previewImage" class="mt-2">
                        ${
                          response.data[0].img
                            ? `<img src="${response.data[0].img}" class="img-fluid mb-2" style="max-height: 200px;">`
                            : `<p class="text-muted">No hay imagen disponible.</p>`
                        }
                      </div>
                    </div>

                    <!-- subir Imagen -->
                    <div class="form-group col-md-3">
                      <label for="newImage">Subir imagen</label>
                      <input type="file" class="form-control-file" id="newImage" accept="image/*">
                      <div id="previewImage1" class="mt-2">
                        ${
                          response.data[0].imagen2
                            ? `<img src="${response.data[0].imagen2}" class="img-fluid mb-2" style="max-height: 200px;">`
                            : `<p class="text-muted">No hay imagen disponible.</p>`
                        }
                      </div>
                    </div>

                    <!-- PDF 1 -->
                    <div class="form-group col-md-2">
                      <label for="newPdf">Subir PDF 1</label>
                      <input type="file" class="form-control-file" id="newPdf" accept="application/pdf">
                      <div id="previewPdf" class="mt-2">
                        ${
                          response.data[0].pdf
                            ? `<a href="${response.data[0].pdf}" target="_blank" class="btn btn-outline-primary btn-sm">Ver PDF</a>`
                            : `<p class="text-muted">No hay PDF disponible.</p>`
                        }
                      </div>
                    </div>

                    <!-- PDF 2 -->
                    <div class="form-group col-md-2">
                      <label for="newPdf2">Subir PDF 2</label>
                      <input type="file" class="form-control-file" id="newPdf2" accept="application/pdf">
                      <div id="previewPdf2" class="mt-2">
                        ${
                          response.data[0].pdf2
                            ? `<a href="${response.data[0].pdf2}" target="_blank" class="btn btn-outline-primary btn-sm">Ver PDF 2</a>`
                            : `<p class="text-muted">No hay PDF disponible.</p>`
                        }
                      </div>
                    </div>

                    <!-- PDF 3 -->
                    <div class="form-group col-md-2">
                      <label for="newPdf3">Subir PDF 3</label>
                      <input type="file" class="form-control-file" id="newPdf3" accept="application/pdf">
                      <div id="previewPdf3" class="mt-2">
                        ${
                          response.data[0].pdf3
                            ? `<a href="${response.data[0].pdf3}" target="_blank" class="btn btn-outline-primary btn-sm">Ver PDF 3</a>`
                            : `<p class="text-muted">No hay PDF disponible.</p>`
                        }
                      </div>
                    </div>

                    <!-- PDF 4 -->
                    <div class="form-group col-md-2">
                      <label for="newPdf4">Subir PDF 4</label>
                      <input type="file" class="form-control-file" id="newPdf4" accept="application/pdf">
                      <div id="previewPdf4" class="mt-2">
                        ${
                          response.data[0].pdf4
                            ? `<a href="${response.data[0].pdf4}" target="_blank" class="btn btn-outline-primary btn-sm">Ver PDF 4</a>`
                            : `<p class="text-muted">No hay PDF disponible.</p>`
                        }
                      </div>
                    </div>

                  </div>
                </form>
              </div>

              <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                ${
                  mostrarBotonActualizar
                    ? `<button type="button" class="btn btn-primary" onclick="actualizarCompromiso(${compromisoId})">Actualizar</button>`
                    : ""
                }
              </div>
            </div>
          </div>
        </div>
      `;

      $("body").append(modalHTML);
      $("#modalEditVisita").modal("show");

      // Preview de imagen
      $("#newImage").on("change", function () {
        const file = this.files[0];
        if (file && file.type.startsWith("image/")) {
          const reader = new FileReader();
          reader.onload = function (e) {
            $("#previewImage1").html(
              `<img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">`
            );
          };
          reader.readAsDataURL(file);
        } else {
          $("#previewImage1").html(
            `<p class="text-danger">Archivo no válido</p>`
          );
        }
      });

      $("#img").on("change", function () {
        const file = this.files[0];
        if (file && file.type.startsWith("image/")) {
          const reader = new FileReader();
          reader.onload = function (e) {
            $("#previewImage").html(
              `<img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">`
            );
          };
          reader.readAsDataURL(file);
        } else {
          $("#previewImage").html(
            `<p class="text-danger">Archivo no válido</p>`
          );
        }
      });

      // Preview de PDF
      $("#newPdf").on("change", function () {
        const file = this.files[0];
        if (file && file.type === "application/pdf") {
          $("#previewPdf").html(
            `<p><i class="feather icon-file-text"></i> ${file.name}</p>`
          );
        } else {
          $("#previewPdf").html(`<p class="text-danger">Archivo no válido</p>`);
        }
      });

      $("#newPdf2").on("change", function () {
        const file = this.files[0];
        if (file && file.type === "application/pdf") {
          $("#previewPdf2").html(
            `<p><i class="feather icon-file-text"></i> ${file.name}</p>`
          );
        } else {
          $("#previewPdf2").html(`<p class="text-danger">Archivo no válido</p>`);
        }
      });

      $("#newPdf3").on("change", function () {
        const file = this.files[0];
        if (file && file.type === "application/pdf") {
          $("#previewPdf3").html(
            `<p><i class="feather icon-file-text"></i> ${file.name}</p>`
          );
        } else {
          $("#previewPdf3").html(`<p class="text-danger">Archivo no válido</p>`);
        }
      });

      $("#newPdf4").on("change", function () {
        const file = this.files[0];
        if (file && file.type === "application/pdf") {
          $("#previewPdf4").html(
            `<p><i class="feather icon-file-text"></i> ${file.name}</p>`
          );
        } else {
          $("#previewPdf4").html(`<p class="text-danger">Archivo no válido</p>`);
        }
      });

      await ciudades();
      await secretarias();

      $("#provincia").val(response.data[0].provincia);
      $("#municipio").val(response.data[0].tbl_municipio_id);
      $("#tbl_secretarias_id").val(response.data[0].tbl_secretarias_id);
      $("#estado").val(response.data[0].estado);
      $("#componente").val(response.data[0].componente);
      $("#estado_editar").val(response.data[0].estado);
    },
  });
}

function actualizarCompromiso(id) {
  // Validación de campos obligatorios
  if (typeof isAdminOrSuper !== 'undefined' && isAdminOrSuper) {
    const componente = $("#componente").val();
    const estadoEditar = $("#estado_editar").val();

    if (!componente || componente.trim() === "") {
      Swal.fire({ icon: "warning", title: "Campo requerido", text: "El campo Componente es obligatorio.", confirmButtonColor: "#f0ad4e" });
      return;
    }
    if (!estadoEditar || estadoEditar.trim() === "") {
      Swal.fire({ icon: "warning", title: "Campo requerido", text: "El campo Estado es obligatorio.", confirmButtonColor: "#f0ad4e" });
      return;
    }
  }

  const formData = new FormData();

  // Campos básicos
  formData.append("method", "actualizarCompromiso");
  formData.append("id", id);
  formData.append("fecha", $("#fecha").val());
  formData.append("provincia", $("#provincia").val());
  formData.append("municipio", $("#municipio").val());
  formData.append("secretaria", $("#tbl_secretarias_id").val());
  formData.append("compromisos", $("#compromisos").val());

  // Solo enviar estado y componente si el usuario es Admin o SuperAdmin
  if (typeof isAdminOrSuper !== 'undefined' && isAdminOrSuper) {
    formData.append("estado", $("#estado_editar").val());
    formData.append("componente", $("#componente").val());
  }

  formData.append("respuesta", $("#respuesta").val());
  formData.append("observaciones", $("#observaciones").val());
  formData.append(
    "acciones_tomadas",
    $("#acciones_tomadas").length ? $("#acciones_tomadas").val() : ""
  );

  // Nuevos campos
  formData.append("url", $("#url").val());
  formData.append("img_actual", $("#img").val()); // Imagen actual guardada
  formData.append("pdf_actual", $("#pdf").val()); // PDF actual guardado

  // Archivos seleccionados (imagen y PDF)
  const newImage = $("#newImage")[0].files[0];
  if (newImage) {
    formData.append("newImage", newImage);
  }

  const newPdf = $("#newPdf")[0].files[0];
  if (newPdf) {
    formData.append("newPdf", newPdf);
  }

  const newPdf2 = $("#newPdf2")[0].files[0];
  if (newPdf2) {
    formData.append("newPdf2", newPdf2);
  }

  const newPdf3 = $("#newPdf3")[0].files[0];
  if (newPdf3) {
    formData.append("newPdf3", newPdf3);
  }

  const newPdf4 = $("#newPdf4")[0].files[0];
  if (newPdf4) {
    formData.append("newPdf4", newPdf4);
  }

  $.ajax({
    url: "./admin/controllers/compromisoMunicipioCtrl.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "¡Actualizado!",
          text: response.message || "Compromiso actualizado correctamente.",
          confirmButtonColor: "#28a745",
        });

        // Guardar los filtros actuales antes de recargar
        guardarFiltrosActuales();

        // Recargar la tabla
        cargarCompromiso();

        // Restaurar los filtros después de un pequeño delay
        setTimeout(function() {
          restaurarFiltrosGuardados();
        }, 500);

        $("#modalEditVisita").modal("hide");
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al actualizar",
          text: response.message || "Ocurrió un problema al actualizar.",
          confirmButtonColor: "#d33",
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de red",
        text: "No se pudo comunicar con el servidor.",
        confirmButtonColor: "#d33",
      });
    },
  });
}


function filtrarTabla() {
  const provincia = document.getElementById("provinciaFiltro").value;
  const idFiltro = document.getElementById("idFiltro").value;
  const municipio = document.getElementById("municipioFiltro").value;
  const secretariaId = document.getElementById("secretariaIdFiltro").value;
  const componente = document.getElementById("componenteFiltro").value;
  const estado = document.getElementById("estadoFiltro") ? document.getElementById("estadoFiltro").value : "";

  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip2",
    processing: true,
    serverSide: true,
    responsive: false,
    destroy: true,
    columnDefs: [{ targets: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14], className: "mdc-data-table__cell" }],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        const filtros = {
          method: "getAllCompromiseFiltrosSelect",
          data: {
            length: d.length,
            start: d.start,
            draw: d.draw,
            id: idFiltro ? parseInt(idFiltro) : 0,
            secretaria: secretariaId,
            componente: componente,
            municipio: municipio,
            provincia: provincia,
            estado: estado,
          },
        };
        console.log("Filtros enviados:", filtros);
        return JSON.stringify(filtros);
      },
    },
    columns: [
      { data: "id" },
      { data: "secretaria" },
      { data: "compromisopac", visible: false, render: renderCompromisoBtn },
      { data: "consecuencia", visible: false, render: renderCompromisoBtn },
      { data: "respuesta", visible: false, render: renderCompromisoBtn },
      {
        data: "estado",
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
          } else if (estado.includes("espera")) {
            bg = "#17a2b8";
          } else if (estado.includes("sin cumplir") || estado.includes("por cumplir")) {
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
      { data: "provincia" },
      { data: "componente" },
      { data: "tipo_ejecucion" },
      {
        data: "foto",
        orderable: false,
        searchable: false,
        render: function (data, type) {
          if (type !== "display" || !data)
            return `<span class="text-muted">Sin adjunto</span>`;
          const imagenSrc = (data.match(/<img[^>]*src="([^"]+)"/i) || [])[1];
          const pdfSrc = (data.match(
            /mostrarArchivoModal\('([^']+\.pdf)'\)/i
          ) || [])[1];
          if (!imagenSrc && !pdfSrc) {
            return `<span class="text-muted">Sin adjunto</span>`;
          }
          return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('${
            imagenSrc || ""
          }', '${pdfSrc || ""}')"></i>`;
        },
      },
      { data: "date", render: function (d) { return d ? d.split(" ")[0] : ""; } },
{
  data: "id",
  render: function (data, type, row) {
    

    if (row.estado && row.estado.toLowerCase().includes("cumplido")) {
      return "";
    }

    return `<button class="btn btn-sm btn-transparent" onclick="editaCompromiso('${data}');"><i class="feather icon-edit"></i></button>`;
  },
},

      {
        data: "id",
        render: function (data) {
          let html = `<form action="reporte-compromiso.php" method="POST" style="display:inline;" target="_blank">
                      <input type="hidden" name="reporte" value="1">
                      <input type="hidden" name="id" value="${data}">
                      <button type="submit" class="btn btn-sm btn-success4">
                          <i class="feather icon-eye"></i>
                      </button></form>`;
          if (typeof isAdminOrSuper !== 'undefined' && isAdminOrSuper) {
            html += `<button class="btn btn-sm btn-outline-info ml-1" onclick="verHistorial(${data})" title="Ver historial de cambios">
                      <i class="feather icon-clock"></i>
                    </button>`;
          }
          return html;
        },
      },
      {
        data: "id",
        visible: typeof isAdminOrSuper !== 'undefined' && isAdminOrSuper,
        orderable: false,
        searchable: false,
        render: function (data) {
          return `<button class="btn btn-sm btn-danger" onclick="eliminarCompromiso(${data})" title="Eliminar compromiso">
                    <i class="feather icon-trash-2"></i>
                  </button>`;
        },
      },
    ],
  });
}
// ============================================================
// Funciones para guardar y restaurar filtros
// ============================================================

/**
 * Guarda los valores actuales de los filtros en localStorage
 */
function guardarFiltrosActuales() {
  const filtros = {
    provinciaFiltro: document.getElementById("provinciaFiltro") ? document.getElementById("provinciaFiltro").value : "",
    idFiltro: document.getElementById("idFiltro") ? document.getElementById("idFiltro").value : "",
    municipioFiltro: document.getElementById("municipioFiltro") ? document.getElementById("municipioFiltro").value : "",
    secretariaIdFiltro: document.getElementById("secretariaIdFiltro") ? document.getElementById("secretariaIdFiltro").value : "",
    componenteFiltro: document.getElementById("componenteFiltro") ? document.getElementById("componenteFiltro").value : "",
    estadoFiltro: document.getElementById("estadoFiltro") ? document.getElementById("estadoFiltro").value : ""
  };
  
  localStorage.setItem('filtrosCompromiso', JSON.stringify(filtros));
  console.log('Filtros guardados:', filtros);
}

/**
 * Restaura los filtros guardados y recarga la tabla con esos filtros
 */
function restaurarFiltrosGuardados() {
  const filtrosGuardados = localStorage.getItem('filtrosCompromiso');
  
  if (filtrosGuardados) {
    try {
      const filtros = JSON.parse(filtrosGuardados);
      
      // Restaurar los valores en los campos de filtro
      if (document.getElementById("provinciaFiltro")) {
        document.getElementById("provinciaFiltro").value = filtros.provinciaFiltro || "";
      }
      if (document.getElementById("idFiltro")) {
        document.getElementById("idFiltro").value = filtros.idFiltro || "";
      }
      if (document.getElementById("municipioFiltro")) {
        document.getElementById("municipioFiltro").value = filtros.municipioFiltro || "";
      }
      if (document.getElementById("secretariaIdFiltro")) {
        document.getElementById("secretariaIdFiltro").value = filtros.secretariaIdFiltro || "";
      }
      if (document.getElementById("componenteFiltro")) {
        document.getElementById("componenteFiltro").value = filtros.componenteFiltro || "";
      }
      if (document.getElementById("estadoFiltro")) {
        document.getElementById("estadoFiltro").value = filtros.estadoFiltro || "";
      }
      
      console.log('Filtros restaurados:', filtros);
      
      // Verificar si hay algún filtro activo
      const hayFiltrosActivos = Object.values(filtros).some(valor => valor !== "");
      
      if (hayFiltrosActivos) {
        // Aplicar los filtros
        filtrarTabla();
      }
      
      // Limpiar el localStorage después de restaurar
      localStorage.removeItem('filtrosCompromiso');
      
    } catch (error) {
      console.error('Error al restaurar filtros:', error);
      localStorage.removeItem('filtrosCompromiso');
    }
  }
}

/**
 * Elimina un compromiso por ID con confirmación SweetAlert2
 */
function eliminarCompromiso(id) {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción eliminará el compromiso permanentemente.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(function (result) {
    if (!result.isConfirmed) return;

    Swal.fire({
      title: 'Eliminando...',
      allowOutsideClick: false,
      didOpen: function () { Swal.showLoading(); }
    });

    $.ajax({
      url: './admin/controllers/compromisoMunicipioCtrl.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ method: 'deleteCompromiso', data: { id: id } }),
      dataType: 'json',
      success: function (response) {
        if (response.state) {
          Swal.fire({
            icon: 'success',
            title: 'Eliminado',
            text: response.message || 'Compromiso eliminado correctamente.',
            timer: 1500
          }).then(function () {
            if (typeof cargarCompromiso === 'function') {
              cargarCompromiso();
            }
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.message || 'Error al eliminar el compromiso.'
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: 'error',
          title: 'Error de red',
          text: 'No se pudo comunicar con el servidor.'
        });
      }
    });
  });
}

/**
 * Descarga los compromisos en formato Excel (CSV) respetando los filtros activos.
 * Envía un formulario POST oculto al controlador para forzar la descarga del archivo.
 */
function descargarExcel() {
  const idFiltro     = document.getElementById("idFiltro") ? document.getElementById("idFiltro").value : "";
  const secretaria   = document.getElementById("secretariaIdFiltro") ? document.getElementById("secretariaIdFiltro").value : "";
  const municipio    = document.getElementById("municipioFiltro") ? document.getElementById("municipioFiltro").value : "";
  const componente   = document.getElementById("componenteFiltro") ? document.getElementById("componenteFiltro").value : "";
  const provincia    = document.getElementById("provinciaFiltro") ? document.getElementById("provinciaFiltro").value : "";
  const estado       = document.getElementById("estadoFiltro") ? document.getElementById("estadoFiltro").value : "";

  // Crear formulario oculto para forzar la descarga del archivo
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "./admin/controllers/compromisoMunicipioCtrl.php";
  form.style.display = "none";

  const campos = {
    method: "exportarCompromisosExcel",
    id: idFiltro,
    secretaria: secretaria,
    municipio: municipio,
    componente: componente,
    provincia: provincia,
    estado: estado
  };

  for (const [nombre, valor] of Object.entries(campos)) {
    const input = document.createElement("input");
    input.type  = "hidden";
    input.name  = nombre;
    input.value = valor;
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}
