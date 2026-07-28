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

  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip",
    processing: true,
    serverSide: true,
    responsive: true,
    destroy: true,


    columnDefs: [{ targets: ["_all"], className: "mdc-data-table__cell" }],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        return JSON.stringify({
          method: "getAllCompromisecumplidos",
          data: d,
        });
      },
    },
    columns: [
      { data: "id" },
      { data: "secretaria" },
      { data: "compromisos", visible: true, render: renderCompromisoBtn },
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
            bg = "#6c757d"; 
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
          // Verificar si el estado es "En Espera"
          if (row.estado == "En Espera") {
            return ""; // No mostrar el botón
          }

          return `<button class="btn btn-sm btn-transparent" onclick="editaCompromiso('${data}');"><i class="feather icon-edit"></i></button>`;
        },
      },
      {
        data: "id",
        render: function (data) {
          return `<form action="reporte-compromiso.php" method="POST" target="_blank" style="display:inline;">
            <input type="hidden" name="reporte" value="1">
            <input type="hidden" name="id" value="${data}">
            <button type="submit" class="btn btn-sm btn-success4">
              <i class="feather icon-eye"></i>
            </button></form>`;
        },
      },
    ],
  });
}

// Render botón "Ver" para compromisos
function renderCompromisoBtn(data, type, row) {
    if (type === "display") {

        const texto = (data ?? "").toString().trim();

        // Texto truncado para no romper la tabla
        const preview = texto.length > 60 
            ? texto.substring(0, 60) + "..." 
            : texto;

        return `
            <div>
                <span>${preview}</span><br>
                <button class="btn btn-sm btn-link text-primary"
                    onclick="verCompromiso(${row.id})">Ver</button>
            </div>
        `;
    }
    return data;
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
function verCompromiso(id) {
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioCtrl.php",
    type: "POST",
    contentType: "application/json",
    dataType: "json",
    data: JSON.stringify({
      method: "getCompromisoId",
      data: id
    }),
    success: function (response) {
      if (response.state && response.data.length > 0) {

        const item = response.data[0];

        let detalle = "";

        // Armar información completa
        detalle += item.compromisopac ? ("Compromiso pactado:\n" + item.compromisopac + "\n\n") : "";
        detalle += item.compromisos ? ("Compromiso detallado:\n" + item.compromisos + "\n\n") : "";
        detalle += item.respuesta ? ("Respuesta:\n" + item.respuesta + "\n\n") : "";
        detalle += item.consecuencia ? ("Consecuencia:\n" + item.consecuencia + "\n\n") : "";
        detalle += item.observaciones ? ("Observaciones:\n" + item.observaciones) : "";

        if (detalle.trim() === "") {
          detalle = "No hay detalles para mostrar.";
        }

        $("#contenidoCompromiso").text(detalle);
      } else {
        $("#contenidoCompromiso").text("No hay detalles para mostrar.");
      }

      $("#modalCompromiso").modal("show");
    },
    error: function () {
      $("#contenidoCompromiso").text("Error al obtener el detalle.");
      $("#modalCompromiso").modal("show");
    },
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
      console.log(response);
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
                <h5 class="modal-title" id="modalEditVisitaLabel">Editar Compromiso</h5>
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
                        response.data[0].date || ""
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
                      <textarea class="form-control" id="compromisos" rows="3">${
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
    },
  });
}

function actualizarCompromiso(id) {
  const formData = new FormData();

  // Campos básicos
  formData.append("method", "actualizarCompromiso");
  formData.append("id", id);
  formData.append("fecha", $("#fecha").val());
  formData.append("provincia", $("#provincia").val());
  formData.append("municipio", $("#municipio").val());
  formData.append("secretaria", $("#tbl_secretarias_id").val());
  formData.append("compromisos", $("#compromisos").val());
  formData.append("estado", $("#estado").val());
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
        cargarCompromiso();
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

  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip2",
    processing: true,
    serverSide: true,
    responsive: true,
    destroy: true,

    columnDefs: [{ targets: ["_all"], className: "mdc-data-table__cell" }],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        return JSON.stringify({
          method: "getAllCompromisecumplidosFiltrosSelect",
          data: {
            length: d.length,
            start: d.start,
            draw: d.draw,
            id: idFiltro,
            secretaria: secretariaId,
            componente: componente,
            municipio: municipio,
            provincia: provincia,

          },
        });
      },
    },
    columns: [
      { data: "id" },
      { data: "secretaria" },
      { data: "compromisos", visible: true, render: renderCompromisoBtn },
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
            bg = "#6c757d"; 
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
          // Verificar si el estado es "En Espera"
          if (row.estado == "En Espera") {
            return ""; // No mostrar el botón
          }

          return `<button class="btn btn-sm btn-transparent" onclick="editaCompromiso('${data}');"><i class="feather icon-edit"></i></button>`;
        },
      },
      {
        data: "id",
        render: function (data) {
          return `<form action="reporte-compromiso.php" method="POST" target="_blank" style="display:inline;">
                      <input type="hidden" name="reporte" value="1">
                      <input type="hidden" name="id" value="${data}">
                      <button type="submit" class="btn btn-sm btn-success4">
                          <i class="feather icon-eye"></i>
                      </button></form>`;
        },
      },
    ],
  });
}