let compromiso = null;
let chartIndicadores = null;

$(function () {
  // Carga la tabla al cargar la página
  cargaData();

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
function cargaData() {
  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  compromiso = $("#dynamictable").DataTable({
    order: [[8, "desc"]],
    dom: "lrtip",
    processing: true,
    serverSide: true,
    responsive: true,
    destroy: true,
    columnDefs: [
      {
        targets: ["_all"],
        className: "mdc-data-table__cell",
      },
    ],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        return JSON.stringify({
          method: "getAllVisitas",
          data: d,
        });
      },
    },
    columns: [
      {
        data: "compromisos",
        render: renderCompromisoBtn,
      },
      {
        data: "tipo_visita",
      },
      {
        data: "descripcion_hecho",
        visible: false,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          if (type === "display") {
            const safeData = (data || "").replace(/"/g, "&quot;");
            return `
                      <button class="btn btn-sm btn-link text-primary ver-compromiso" 
                              data-contenido="${safeData}">
                        Ver
                      </button>`;
          }
          return data;
        },
      },
      {
        data: "consecuencia",
        visible: false,
        render: function (data, type, row) {
          if (type === "display") {
            const safeData = (data || "").replace(/"/g, "&quot;");
            return `
                      <button class="btn btn-sm btn-link text-primary ver-compromiso" 
                              data-contenido="${safeData}">
                        Ver
                      </button>`;
          }
          return data;
        },
      },
      { data: "provincia" },
      { data: "municipio" },
      {
        data: "foto",
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          if (type !== "display" || !data)
            return `<span class="text-muted">Sin adjunto</span>`;

          const imagenMatch = data.match(
            /<img\s+[^>]*src="([^"]+\.(jpg|jpeg|png|gif|webp))"[^>]*>/i
          );
          const imagenSrc = imagenMatch ? imagenMatch[1] : null;

          const pdfMatch = data.match(/mostrarArchivoModal\('([^']+\.pdf)'\)/i);
          const pdfSrc = pdfMatch ? pdfMatch[1] : null;

          if (!imagenSrc && !pdfSrc) {
            return `<span class="text-muted">Sin adjunto</span>`;
          }

          return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('${
            imagenSrc || ""
          }', '${pdfSrc || ""}')"></i>`;
        },
      },
      { data: "date" },
      {
        data: "id",
        orderable: true,
        /* orderable: false, */
        searchable: false,
        render: function (data, type, row) {
          return `<button class="btn btn-sm btn-transparent" 
                    onclick="editaVisita('${data}');"
                    title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
        },
      },
      {
        data: "id",
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return `
                    <form action="reporte-visita.php" method="POST" style="display:inline;">
                        <input type="hidden" name="reporte" value="1">
                        <input type="hidden" name="id" value="${data}">
                        <button type="submit" class="btn btn-sm btn-success4" title="Ver">
                        <i class="feather icon-external-link"></i>
                        </button>
                    </form>`;
        },
      },
    ],
  });
}

// Render botón "Ver" para compromisos
function renderCompromisoBtn(data, type) {
  if (type === "display") {
    const seguro = data.replace(/"/g, "&quot;").replace(/'/g, "\\'");
    return `<button class="btn btn-sm btn-link text-primary" onclick="verCompromiso('${seguro}')">Ver</button>`;
  }
  return data;
}

function indicadores() {
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "indicadoresTipoVisita" }),
    dataType: "json",
    contentType: "application/json",
    success: function (response) {
      console.log(response, " res");
      const contenedor = $("#indicadoresContainer");
      contenedor.empty();

      if (response && response.data.length) {
        const labels = response.data.map((item) => item.tipo_visita);
        const data = response.data.map((item) => item.total);

        const colors = [
          "#007bff",
          "#dc3545",
          "#ffc107",
          "#28a745",
          "#6f42c1",
          "#20c997",
          "#fd7e14",
          "#17a2b8",
          "#343a40",
        ];

        contenedor.append(`
          <div class="mb-4">
            <h5 class="mb-3">Resumen por tipo de visita</h5>
            <canvas id="graficoTipoVisita" height="120"></canvas>
          </div>
        `);

        const ctx = document
          .getElementById("graficoTipoVisita")
          .getContext("2d");
        new Chart(ctx, {
          type: "pie",
          data: {
            labels: labels,
            datasets: [
              {
                label: "Total de Visitas",
                data: data,
                backgroundColor: colors,
                borderColor: "#333",
                borderWidth: 1,
              },
            ],
          },
          options: {
            responsive: true,
            plugins: {
              legend: { display: false },
              title: { display: false },
              tooltip: {
                mode: "index",
                intersect: false,
              },
            },
            scales: {
              x: {
                ticks: {
                  autoSkip: false,
                  maxRotation: 45,
                  minRotation: 0,
                  font: { size: 10 },
                },
              },
              y: {
                beginAtZero: true,
                ticks: { precision: 0 },
              },
            },
          },
        });
      } else {
        contenedor.append(
          '<p class="text-muted">No hay datos disponibles.</p>'
        );
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

function editaVisita(data) {
  ciudades();
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "getVisitaId", data: data }),
    dataType: "json",
    contentType: "application/json",
    success: async function (response) {
      console.log(response);
      $("#modalEditVisita").remove();

      // Crear el modal
      const modalHTML = `
        <div class="modal fade" id="modalEditVisita" tabindex="-1" role="dialog" aria-labelledby="modalEditVisitaLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditVisitaLabel">Editar Visita</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

              <div class="modal-body" style="padding:20px">
                <form id="formEditVisita">

                  <input type="hidden" id="idVisitaEditar" name="idVisitaEditar" value="${
                    response.data[0].id || ""
                  }">
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="fecha">Fecha</label>
                      <input type="date" class="form-control" id="fecha" value="${
                        response.data[0].date || ""
                      }">
                    </div>

                    <div class="form-group col-md-6">
                      <label for="provincia">Provincia <span class="text-danger">*</span></label>
                      <select class="form-control" id="provincia" name="provincia">
                        <option value="Seleccione">Seleccione</option>
                        <option value="Soto Norte">Soto Norte</option>
                        <option value="Guanentá">Guanentá</option>
                        <option value="García Rovira">García Rovira</option>
                        <option value="Comunera">Comunera</option>
                        <option value="Vélez">Vélez</option>
                        <option value="Metropolitana">Metropolitana</option>
                        <option value="Yariguíes">Yariguíes</option>
                      </select>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="municipio">Municipio</label>
                      <select class="form-control" id="municipio" name="municipio"></select>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="tipo_visita">Tipo de Visita</label>
                      <select class="form-control" id="tipo_visita" name="tipo_visita">
                        <option value="Seleccione">Seleccione</option>
                        <option value="Reunión">Reunión</option>
                        <option value="Ruta 25">Ruta 25</option>
                        <option value="Brigada Civico Social">Brigada Cívico Social</option>
                        <option value="Consejo de Seguridad">Consejo de Seguridad</option>
                        <option value="Concejos y/o Juntas Directivas">Concejos y/o Juntas Directivas</option>
                        <option value="Inauguración de festividades">Inauguración de festividades</option>
                        <option value="Seguimiento de Obras">Seguimiento de Obras</option>
                        <option value="Seguimiento de Planes, Programas y Proyectos">Seguimiento de Planes, Programas y Proyectos</option>
                      </select>
                    </div>

                    <div class="form-group col-md-12">
                      <label for="compromisos">Detalles / Visitas</label>
                      <textarea class="form-control" id="compromisos" rows="4">${
                        response.data[0].compromisos || ""
                      }</textarea>
                    </div>

                    <div id="previewImage" class="mt-2">
                      ${
                        response.data[0].foto
                          ? `<img src="${response.data[0].foto}" class="img-fluid mb-2" style="max-height: 200px;">`
                          : `<p class="text-muted">No hay imagen disponible.</p>`
                      }
                    </div>

                    <div class="form-group col-md-12">
                        <label for="img">Subir imagen</label>
                        <input type="file" class="form-control-file" id="img" accept="image/*">
                        <div id="previewImage" class="mt-2">
                        </div>
                    </div>

                  </div>
                </form>
              </div>

              <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" onclick="actualizarVisita()" class="btn btn-primary" id="btnGuardarVisita">Actualizar control visita</button>
              </div>
            </div>
          </div>
        </div>
      `;

      // Insertar el modal en el DOM y mostrarlo
      $("body").append(modalHTML);
      $("#modalEditVisita").modal("show");

      await ciudades();

      $("#provincia").val(response.data[0].provincia);
      $("#municipio").val(response.data[0].tbl_municipio_id);
      $("#tipo_visita").val(response.data[0].tipo_visita);
    },
  });
}

function actualizarVisita() {
  $("#btnGuardarVisita").prop("disabled", true);
  UTIL.cursorBusy();
  const formData = new FormData();

  formData.append("method", "actualizarVisita");
  formData.append("id", $("#idVisitaEditar").val());
  formData.append("date", $("#fecha").val());
  formData.append("municipio", $("#municipio").val());
  formData.append("provincia", $("#provincia").val());
  formData.append("tipo_visita", $("#tipo_visita").val());
  formData.append("compromisos", $("#compromisos").val());

  const imageFile = $("#img")[0].files[0];
  if (imageFile) {
    formData.append("imagen", imageFile);
  }

  $.ajax({
    url: "./admin/controllers/compromisoMunicipioCtrl.php",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "¡Bien!",
          text: response.message,
          confirmButtonColor: "#28a745",
        }).then(() => {
          UTIL.cursorNormal();
          cargaData();
          $("#formEditVisita")[0].reset();
          $("#previewImage").html("");
          $("#btnGuardarVisita").prop("disabled", false);
          $("#modalEditVisita").modal("hide");
        });
      } else {
        $("#btnGuardarVisita").prop("disabled", false);
        Swal.fire({
          icon: "error",
          title: "¡Error!",
          text: response.message,
          confirmButtonColor: "#28a745",
        });
      }
    },
    error: function () {},
  });
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

        response.data.forEach(function (ciudad) {
          $select.append(
            `<option value="${ciudad.codigo_muncipio}">${ciudad.nombre_mapa}</option>`
          );
        });
        resolve();
      },
    });
  });
}
