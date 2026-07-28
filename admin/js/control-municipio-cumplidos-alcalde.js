/**
 * Control de Compromisos Cumplidos para Alcalde
 * Adaptado para usar tbl_compromisos_alcalde con vereda en lugar de provincia
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

// Cargar tabla de compromisos cumplidos
function cargarCompromiso() {
  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  // Obtener filtro de municipio si es usuario municipal
  const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
  const municipioUsuario = $('#municipioUsuario').val();

  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip",
    processing: true,
    serverSide: true,
    responsive: true,
    destroy: true,
    columnDefs: [{ targets: ["_all"], className: "mdc-data-table__cell" }],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        // Si es usuario municipal, aplicar filtro automáticamente
        if (isUsuarioMunicipal && municipioUsuario) {
          d.tbl_municipio_id = municipioUsuario;
        }

        return JSON.stringify({
          method: "getAllCompromisosCumplidos",
          data: d,
        });
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
      { data: "vereda", defaultContent: "Sin vereda" }, // VEREDA en lugar de provincia
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
function renderCompromisoBtn(data, type, row) {
  if (type === "display") {
    const texto = (data ?? "").toString().trim();
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
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "getIndicadoresCompromisosSecretaria" }),
    dataType: "json",
    contentType: "application/json",
    success: function (response) {
      const contenedor = $("#indicadoresContainer");
      contenedor.empty();

      if (response.state && response.data && response.data.length) {
        const secretarias = [...new Set(response.data.map((i) => i.secretaria))];
        const estados = [...new Set(response.data.map((i) => i.estado))];

        const colorByEstado = {
          cumplido: "rgba(46, 204, 113, 0.7)",
          "sin cumplir": "rgba(231, 76, 60, 0.7)",
          "en tramite": "rgba(241, 196, 15, 0.7)",
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
            backgroundColor: colorByEstado[estadoKey] || "rgba(52, 152, 219, 0.7)",
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
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    contentType: "application/json",
    dataType: "json",
    data: JSON.stringify({
      method: "getAllCompromisos",
      data: { id: parseInt(id) }
    }),
    success: function (response) {
      if (response.output && response.output.valid && response.output.response.length > 0) {
        const item = response.output.response[0];
        let detalle = "";

        detalle += item.compromiso_pactado ? ("Compromiso pactado:\n" + item.compromiso_pactado + "\n\n") : "";
        detalle += item.compromisos ? ("Compromiso detallado:\n" + item.compromisos + "\n\n") : "";
        detalle += item.respuesta ? ("Respuesta:\n" + item.respuesta + "\n\n") : "";
        detalle += item.consecuencia ? ("Consecuencia:\n" + item.consecuencia + "\n\n") : "";

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

function verDetalleCompromiso(id) {
  verCompromiso(id);
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

// Cargar veredas según el municipio seleccionado
function cargarVeredas(municipioId) {
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
        const $select = $("#veredaFiltro");
        $select.empty();
        $select.append('<option value="">Todas</option>');

        const veredas = response.output?.response || [];
        veredas.forEach(function (vereda) {
          $select.append(
            `<option value="${vereda.id}">${vereda.nombre_vereda}</option>`
          );
        });
        resolve();
      },
      error: function() {
        $("#veredaFiltro").empty().append('<option value="">Error al cargar veredas</option>');
        resolve();
      }
    });
  });
}

function editaCompromiso(id) {
  // Esta función se implementará similar a la de control-compromisos-alcalde.js
  // Por ahora redirigimos a la página de edición
  window.location.href = `cuadro-control-compromisos_alcalde.php#edit-${id}`;
}

function filtrarTabla() {
  const vereda = document.getElementById("veredaFiltro").value;
  const municipio = document.getElementById("municipioFiltro").value;
  const secretariaId = document.getElementById("secretariaIdFiltro").value;
  const componente = document.getElementById("componenteFiltro").value;

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
      url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        return JSON.stringify({
          method: "getAllCompromisosCumplidos",
          data: {
            length: d.length,
            start: d.start,
            draw: d.draw,
            secretaria: secretariaId,
            componente: componente,
            municipio: municipio,
            vereda: vereda, // VEREDA en lugar de provincia
          },
        });
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
      { data: "vereda", defaultContent: "Sin vereda" }, // VEREDA
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
