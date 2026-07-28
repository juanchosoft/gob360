$(function () {
  init();
  function init() {
    secretarias().then(() => {
      const filtros = obtenerFiltros();
      graficoSeguimiento(filtros);
      porcentaje(filtros);
      dataMapa(filtros);
    });
  }

  // Cierra el modal con el ID 'archivoModal'
  $("#archivoModal").modal("hide");

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
            const $select = $("#tbl_secretarias_id");
            const $select1 = $("#tbl_secretarias_id_modal");
            $select.empty();
            $select1.empty();

            $select.append('<option value="" selected>Todas</option>');
            $select1.append('<option value="" selected>Todas</option>');

            if (response.output && response.output.valid) {
              response.output.response.forEach(function (secretaria) {
                $select.append(
                  `<option value="${secretaria.id}">${secretaria.secretaria}</option>`
                );
                $select1.append(
                  `<option value="${secretaria.id}">${secretaria.secretaria}</option>`
                );
              });
            }
            resolve();
          },
          error: function() {
            console.error("Error cargando secretarias municipales");
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
            const $select = $("#tbl_secretarias_id");
            const $select1 = $("#tbl_secretarias_id_modal");
            $select.empty();
            $select1.empty();

            $select.append('<option value="" selected>Todas</option>');
            $select1.append('<option value="" selected>Todas</option>');

            if (response && response.data) {
              response.data.forEach(function (ciudad) {
                $select.append(
                  `<option value="${ciudad.id}">${ciudad.secretaria}</option>`
                );
                $select1.append(
                  `<option value="${ciudad.id}">${ciudad.secretaria}</option>`
                );
              });
            }
            resolve();
          },
          error: function (xhr, status, error) {
            console.error("Error cargando secretarias:", error);
            resolve();
          }
        });
      }
    });
  }

  function pintarMapa(municipiosConCompromiso) {
    const colorConCompromiso = "#08306b";
    const colorSinCompromiso = "#f7fbff";

    const textoConCompromiso = "#08306b";
    const textoSinCompromiso = "#cccccc";

    const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';

    if (isUsuarioMunicipal) {
      // Pintar mapa de VEREDAS para usuarios Alcalde
      const veredasActivas = new Set(
        municipiosConCompromiso.map((m) =>
          (m.vereda || "").toUpperCase().replace(/-/g, " ").trim()
        )
      );

      const elementos = document.querySelectorAll("path.vereda-click, polygon.vereda-click");

      elementos.forEach((el) => {
        const nombreVereda = el.getAttribute("data-name");
        const id = (nombreVereda || "")
          .toUpperCase()
          .replace(/-/g, " ")
          .trim();

        const tieneCompromiso = veredasActivas.has(id);

        el.setAttribute(
          "fill",
          tieneCompromiso ? colorConCompromiso : colorSinCompromiso
        );
      });
    } else {
      // Pintar mapa de MUNICIPIOS para SuperAdmin
      const municipiosActivos = new Set(
        municipiosConCompromiso.map((m) =>
          (m.municipio || "").toUpperCase().replace(/-/g, " ").trim()
        )
      );

      const elementos = document.querySelectorAll("path.municipios");

      elementos.forEach((el) => {
        const nombreMunicipio = el.getAttribute("data-name");
        const id = (nombreMunicipio || "")
          .toUpperCase()
          .replace(/-/g, " ")
          .trim();

        const tieneCompromiso = municipiosActivos.has(id);

        el.setAttribute(
          "fill",
          tieneCompromiso ? colorConCompromiso : colorSinCompromiso
        );
      });

      document.querySelectorAll("svg text").forEach((textEl) => {
        const nombre = (textEl.textContent || "").toUpperCase().trim();
        const tieneCompromiso = municipiosActivos.has(nombre);

        textEl.setAttribute(
          "fill",
          tieneCompromiso ? textoConCompromiso : textoSinCompromiso
        );
      });
    }
  }

  function obtenerFiltros() {
    const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
    const municipioUsuario = $('#municipioUsuario').val();

    const filtros = {
      idSecretaria: $("#tbl_secretarias_id").val(),
      componente: $("#componente").val(),
      tipo_ejecucion: $("#tipo_ejecucion").val(),
    };

    // Si es usuario municipal, agregar filtro de municipio
    if (isUsuarioMunicipal && municipioUsuario) {
      filtros.codigo_municipio = municipioUsuario;
    }

    return filtros;
  }

  $("#tbl_secretarias_id, #componente, #tipo_ejecucion").on(
    "change",
    function () {
      const filtros = obtenerFiltros();
      $("select").prop("disabled", true);

      Promise.all([
        graficoSeguimiento(filtros),
        porcentaje(filtros),
        dataMapa(filtros),
      ]).then(() => {
        $("select").prop("disabled", false);
      });
    }
  );

  function graficoSeguimiento(filtros) {
    return new Promise((resolve) => {
      $.ajax({
        url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
        type: "POST",
        data: JSON.stringify({
          method: "graficoSeguimiento",
          ...filtros,
        }),
        dataType: "json",
        contentType: "application/json",
        success: function (response) {
          if (!response.state || !response.data) {
            console.error("Datos no válidos para el gráfico:", response);
            resolve();
            return;
          }

          const veredas = response.data.map((item) => item.vereda || "Sin vereda");
          const totales = response.data.map((item) => item.total);

          const ctx = document
            .getElementById("graficoVeredas")
            .getContext("2d");

          if (window.graficoVeredaChart) {
            window.graficoVeredaChart.destroy();
          }

          const data = {
            labels: veredas,
            datasets: [
              {
                label: "Compromisos",
                data: totales,
                backgroundColor: function (context) {
                  const chart = context.chart;
                  const { ctx, chartArea } = chart;
                  if (!chartArea) return null;

                  const gradient = ctx.createLinearGradient(
                    chartArea.left,
                    0,
                    chartArea.right,
                    0
                  );
                  gradient.addColorStop(0, "#b0d1f3");
                  gradient.addColorStop(1, "#0d5fa7");
                  return gradient;
                },
                borderWidth: 1,
                borderRadius: 4,
                barThickness: 20,
              },
            ],
          };

          const options = {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              x: {
                beginAtZero: true,
                ticks: { color: "#000" },
                grid: { display: false },
              },
              y: {
                ticks: { color: "#000" },
                grid: { display: false },
              },
            },
            plugins: {
              legend: { display: false },
              tooltip: { enabled: true },
            },
          };

          window.graficoVeredaChart = new Chart(ctx, {
            type: "bar",
            data: data,
            options: options,
          });

          resolve();
        },
        error: function (xhr, status, error) {
          console.error("Error en graficoSeguimiento:", error);
          resolve();
        }
      });
    });
  }


 function porcentaje(filtros) {
  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
      type: "POST",
      data: JSON.stringify({
        method: "porcentaje",
        ...filtros,
      }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const d = response.data || {};

        const cumplidos = parseInt(d.cumplidos) || 0;
        const tramite = parseInt(d.en_tramite) || 0;
        const porCumplir = parseInt(d.por_cumplir) || 0;
        const sinCumplir = parseInt(d.sin_cumplir) || 0;
        const enEspera = parseInt(d.en_espera) || 0;
        const total = parseInt(d.total_compromisos) || 0;
        const totalVeredas = parseInt(d.total_veredas) || 0;
        const totalMunicipios = parseInt(d.total_municipios) || 0;


        const metaOficial = parseInt(d.meta_oficial) || 1;

        const totalSinCumplir = porCumplir + sinCumplir;
        const pc = total ? ((cumplidos / total) * 100).toFixed(0) : 0;
        const pt = total ? ((tramite / total) * 100).toFixed(0) : 0;
        const ps = total ? ((totalSinCumplir / total) * 100).toFixed(0) : 0;
        const pespera = total ? ((enEspera / total) * 100).toFixed(0) : 0;
        const nivelCumplimiento = total
          ? ((cumplidos / total) * 100).toFixed(2)
          : 0;
        const metaCompromisos = metaOficial;
        const porcentajeTotal = metaCompromisos
          ? ((total / metaOficial) * 100).toFixed(0)
          : 0;

        $("#total-compromisos").text(total);
        $("#compromisos-cumplidos").text(cumplidos);
        $("#porcentaje-cumplidos").text(`Cumplidos (${pc}%)`);
        $("#compromisos-tramite").text(tramite);
        $("#porcentaje-tramite").text(`En trámite (${pt}%)`);
        $("#compromisos-sincumplir").text(totalSinCumplir);
        $("#porcentaje-sincumplir").text(`Sin cumplir (${ps}%)`);
        $("#compromisos-enEspera").text(enEspera);
        $("#porcentaje-enEspera").text(`En Espera (${pespera}%)`);
        $("#total-veredas").text(totalVeredas);
        $("#total-municipios").text(totalMunicipios);

        animateCounter("nivel-cumplimiento", parseFloat(nivelCumplimiento));
        animateCounter(
          "porcentaje-total-compromisos",
          parseFloat(porcentajeTotal)
        );

        resolve();
      },
      error: function (xhr, status, error) {
        console.error("Error en porcentaje():", error);
        resolve();
      },
    });
  });
}



  function dataMapa(filtros) {
    return new Promise((resolve) => {
      $.ajax({
        url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
        type: "POST",
        data: JSON.stringify({
          method: "dataMapa",
          ...filtros,
        }),
        dataType: "json",
        contentType: "application/json",
        success: function (response) {
          if (response && response.data) {
            pintarMapa(response.data);
          } else {
            console.warn("📍 dataMapa - No hay datos en la respuesta");
          }
          resolve();
        },
        error: function (xhr, status, error) {
          console.error("❌ Error en dataMapa:", error);
          console.error("❌ Status:", status);
          console.error("❌ Response:", xhr.responseText);
          resolve();
        }
      });
    });
  }

  function animateCounter(element, finalValue, suffix = "%", duration = 1000) {
    const el = document.getElementById(element);
    if (!el) return;

    let start = 0;
    const increment = finalValue / (duration / 16);

    function update() {
      start += increment;
      if (start < finalValue) {
        el.textContent = `${start.toFixed(2)}${suffix}`;
        requestAnimationFrame(update);
      } else {
        el.textContent = `${finalValue.toFixed(2)}${suffix}`;
        if (element === "nivel-cumplimiento") {
          actualizarColorCardNivelCumplimiento();
        }
      }
    }

    update();

  }

  // Función para actualizar el color del div
  function actualizarColorCardNivelCumplimiento() {
    // Obtiene el elemento del título con el porcentaje
    const nivelCumplimientoElement =
      document.getElementById("nivel-cumplimiento");
    // Obtiene el div 'card' completo
    const cardCumplimiento = document.getElementById("card-cumplimiento");

    // Convierte el texto del porcentaje a un número con decimales
    const nivelCumplimiento = parseFloat(nivelCumplimientoElement.textContent);

    // Elimina todas las clases de color para evitar conflictos
    cardCumplimiento.classList.remove("bg-rojo", "bg-amarillo", "bg-verde");

    // Aplica la lógica para decidir el color
    if (nivelCumplimiento < 50) {
      // Si es menor a 50 (49.9 o menos), es rojo
      cardCumplimiento.classList.add("bg-rojo");
    } else if (nivelCumplimiento >= 50 && nivelCumplimiento < 100) {
      // Si está entre 50 y 99.9, es amarillo
      cardCumplimiento.classList.add("bg-amarillo");
    } else if (nivelCumplimiento >= 100) {
      // Si es 100 o más, es verde
      cardCumplimiento.classList.add("bg-verde");
    }
  }

  // Manejador de clicks para MUNICIPIOS (SuperAdmin)
  document.querySelectorAll("path.municipios").forEach((path) => {
    path.addEventListener("click", () => {
      const municipioId = path.getAttribute("data-id");
      const secretariaId = document.getElementById("tbl_secretarias_id").value;
      const componente = document.getElementById("componente").value;
      const tipo_ejecucion = document.getElementById("tipo_ejecucion").value;

      $("#modalMunicipio").modal("show");
      cargarVeredasModal();

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
              method: "dataPorMunicipioSecretaria",
              draw: d.draw,
              start: d.start,
              length: d.length,
              search: d.search,
              order: d.order,
              data: {
                municipio: municipioId,
                secretaria: secretariaId,
                componente: componente,
                tipo_ejecucion: tipo_ejecucion,
                vereda: $("#veredaFiltro").val()
              },
            });
          },
        },
        columns: [
          { data: "id_compromiso" },
          { data: "secretaria" },
          {
            data: "compromisos",
            visible: false,
            render: renderCompromisoBtn,
          },
          // { data: "compromisopac", render: renderCompromisoBtn },
          {
            data: "consecuencia",
            visible: false,
            render: renderCompromisoBtn,
          },
          {
            data: "respuesta",
            visible: false,
            render: renderCompromisoBtn,
          },
          {
  data: "estado",
  render: function (data) {
    if (!data)
      return `<span style="background:#6c757d;color:#fff;padding:4px 10px;border-radius:4px;font-weight:600;">SIN ESTADO</span>`;

    // Convertir a minúsculas, quitar tildes y espacios extra
    const estado = data
      .toString()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();

    let bg = "#6c757d"; // gris por defecto
    let color = "#fff"; // texto blanco

    // Comparación insensible a mayúsculas/tildes
    if (estado.includes("cumplido")) {
      bg = "#28a745"; // verde
    } else if (estado.includes("tramite")) {
      bg = "#ffc107"; // amarillo
      color = "#212529"; // texto oscuro
    } else if (estado.includes("sin cumplir") || estado.includes("por cumplir")) {
      bg = "#dc3545"; // rojo
      color = "#fff";
    }

    return `<span style="
      background-color:${bg};
      color:${color};
      padding:4px 10px;
      border-radius:4px;
      font-weight:600;
      display:inline-block;
      text-align:center;
      min-width:90px;">
      ${data.toUpperCase()}
    </span>`;
  },
}
,

          { data: "municipio" },
          { data: "vereda" },
          { data: "componente" },
          {
            data: "foto",
            orderable: false,
            searchable: false,
            render: function (data, type) {
              if (type !== "display" || !data)
                return `<span class="text-muted">Sin adjunto</span>`;

              const imagenSrc = (data.match(/<img[^>]*src="([^"]+)"/i) ||
                [])[1];
              const pdfSrc = (data.match(
                /mostrarArchivoModal\('([^']+\.pdf)'\)/i
              ) || [])[1];

              if (!imagenSrc && !pdfSrc)
                return `<span class="text-muted">Sin adjunto</span>`;

              return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('${
                imagenSrc || ""
              }', '${pdfSrc || ""}')"></i>`;
            },
          },
          { data: "date" },
          {
            data: "created_at",
            render: function (data) {
              return data || "-";
            },
          },
          {
            data: "id_compromiso",
            render: function (data) {
              return `<form action="reporte-compromiso-alcalde.php" method="POST" style="display:inline;" target="_blank">
              <input type="hidden" name="reporte" value="1">
              <input type="hidden" name="id" value="${data}">
              <button type="submit" class="btn btn-sm btn-success4">
                <i class="feather icon-eye"></i>
              </button>
            </form>`;
            },
          },
        ],
      });
    });
  });

  // Manejador de clicks para VEREDAS (Alcalde)
  document.querySelectorAll("path.vereda-click, polygon.vereda-click").forEach((element) => {
    element.addEventListener("click", () => {
      const veredaId = element.getAttribute("data-id");
      const veredaNombre = element.getAttribute("data-name");
      const secretariaId = document.getElementById("tbl_secretarias_id").value;
      const componente = document.getElementById("componente").value;
      const tipo_ejecucion = document.getElementById("tipo_ejecucion").value;
      const municipioUsuario = $('#municipioUsuario').val();

      $("#modalMunicipio").modal("show");
      cargarVeredasModal();

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
              method: "dataPorMunicipioSecretaria",
              draw: d.draw,
              start: d.start,
              length: d.length,
              search: d.search,
              order: d.order,
              data: {
                municipio: municipioUsuario,
                secretaria: secretariaId,
                componente: componente,
                tipo_ejecucion: tipo_ejecucion,
                vereda: veredaId
              },
            });
          },
        },
        columns: [
          { data: "id_compromiso" },
          { data: "secretaria" },
          {
            data: "compromisos",
            visible: false,
            render: renderCompromisoBtn,
          },
          {
            data: "consecuencia",
            visible: false,
            render: renderCompromisoBtn,
          },
          {
            data: "respuesta",
            visible: false,
            render: renderCompromisoBtn,
          },
          {
            data: "estado",
            render: function (data) {
              if (!data)
                return `<span style="background:#6c757d;color:#fff;padding:4px 10px;border-radius:4px;font-weight:600;">SIN ESTADO</span>`;

              const estado = data
                .toString()
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .trim();

              let bg = "#6c757d";
              let color = "#fff";

              if (estado.includes("cumplido")) {
                bg = "#28a745";
              } else if (estado.includes("tramite")) {
                bg = "#ffc107";
                color = "#212529";
              } else if (estado.includes("sin cumplir") || estado.includes("por cumplir")) {
                bg = "#dc3545";
                color = "#fff";
              }

              return `<span style="
                background-color:${bg};
                color:${color};
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
          { data: "vereda" },
          { data: "componente" },
          {
            data: "foto",
            orderable: false,
            searchable: false,
            render: function (data, type) {
              if (type !== "display" || !data)
                return `<span class="text-muted">Sin adjunto</span>`;

              const imagenSrc = (data.match(/<img[^>]*src="([^"]+)"/i) ||
                [])[1];
              const pdfSrc = (data.match(
                /mostrarArchivoModal\('([^']+\.pdf)'\)/i
              ) || [])[1];

              if (!imagenSrc && !pdfSrc)
                return `<span class="text-muted">Sin adjunto</span>`;

              return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('${
                imagenSrc || ""
              }', '${pdfSrc || ""}')"></i>`;
            },
          },
          { data: "date" },
          {
            data: "created_at",
            render: function (data) {
              return data || "-";
            },
          },
          {
            data: "id_compromiso",
            render: function (data) {
              return `<form action="reporte-compromiso-alcalde.php" method="POST" style="display:inline;" target="_blank">
                <input type="hidden" name="reporte" value="1">
                <input type="hidden" name="id" value="${data}">
                <button type="submit" class="btn btn-sm btn-success4">
                  <i class="feather icon-eye"></i>
                </button>
              </form>`;
            },
          },
        ],
      });
    });
  });

  // sessionStorage.setItem("municipio_id_abierto", municipioId);
});

$("#customSearch")
  .off()
  .on("keyup", function () {
    if (compromiso) {
      compromiso.search(this.value).draw();
    }
  });

$(document).on("click", ".btn-ver-compromiso", function () {
  const texto = $(this).data("compromiso");
  $("#contenidoCompromiso").text(texto || "Sin información");
  $("#modalCompromiso").modal("show");
});

function cerrarModalCompromiso() {
  const modal = bootstrap.Modal.getInstance(
    document.getElementById("modalCompromiso")
  );
  if (modal) {
    modal.hide();
  }
}
function cerrarModalmodalMunicipio() {
  sessionStorage.removeItem("id_modal");

  const modal = bootstrap.Modal.getInstance(
    document.getElementById("modalMunicipio")
  );
  if (modal) {
    modal.hide();
  }
}

function cerrarModalArchivo() {
  const modal = bootstrap.Modal.getInstance(
    document.getElementById("archivoModal")
  );
  if (modal) {
    modal.hide();
  }
}

// function renderCompromisoBtn(data, type) {
//   if (type === "display") {
//     const seguro = $("<div>")
//       .text(data ?? "")
//       .html(); // Escapar HTML seguro
//     return `<button class="btn btn-sm btn-link text-primary btn-ver-compromiso" data-compromiso="${seguro}">Ver</button>`;
//   }
//   return data ?? "";
// }
function renderCompromisoBtn(data, type) {
  if (type === "display") {
    const seguro = $("<div>")
      .text(data ?? "")
      .html(); // Escapar HTML seguro
    return `<button class="btn btn-sm btn-link text-primary btn-ver-compromiso" data-compromiso="${seguro}">Ver</button>`;
  }
  return data ?? "";
}
let ultimoEstado = "";

//modal de la tabla de Compromisos por Municipio y Secretaría de gestion-cumplimiento.php
function filtrarPorEstado(estado) {
  ultimoEstado = estado;

  if (ultimoEstado == "todos") {
    filtrarPorEstadoTodos();
    return;
  }

  // Obtener filtros desde sessionStorage o desde los selects visibles
  const secretariaId =
    sessionStorage.getItem("secretaria_modal") ||
    document.getElementById("tbl_secretarias_id_modal")?.value ||
    document.getElementById("tbl_secretarias_id")?.value ||
    "";

  const componente =
    sessionStorage.getItem("componente_modal") ||
    document.getElementById("componente_modal")?.value ||
    document.getElementById("componente")?.value ||
    "";

  const tipo_ejecucion =
    sessionStorage.getItem("tipo_ejecucion_modal") ||
    document.getElementById("tipo_ejecucion")?.value ||
    "";

  // IMPORTANTE: Si es usuario Alcalde, SIEMPRE usar su municipio
  const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
  const municipioUsuario = $('#municipioUsuario').val();

  const municipio = isUsuarioMunicipal && municipioUsuario
    ? municipioUsuario
    : (document.getElementById("tbl_municipio_id_modal")?.value ||
       sessionStorage.getItem("municipio_id_modal") ||
       document.getElementById("tbl_municipio_id")?.value ||
       "");
const vereda = $("#veredaFiltro").val() || "";


  // Setear visualmente los valores en los selects (por si fueron cargados por sessionStorage)
  $("#tbl_secretarias_id_modal").val(secretariaId);
  $("#componente_modal").val(componente);
  $("#tipo_ejecucion").val(tipo_ejecucion);
  $("#tbl_municipio_id").val(municipio);

  // Si es usuario Alcalde, asegurarse de que el select de municipio tenga su municipio
  if (isUsuarioMunicipal && municipioUsuario) {
    // Esperar a que se carguen los municipios y luego setear el valor
    setTimeout(() => {
      $("#tbl_municipio_id").val(municipioUsuario);
      cargarVeredasModal(); // Cargar veredas del municipio
    }, 500);
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
          method: "dataPorMunicipioSecretaria",
          draw: d.draw,
          start: d.start,
          length: d.length,
          search: d.search,
          order: d.order,
          data: {
            estado: estado,
            secretaria: secretariaId,
            componente: componente,
            tipo_ejecucion: tipo_ejecucion,
            municipio: municipio,
            vereda: vereda
          },
        });
      },
    },
    initComplete: function () {
      $("#modalMunicipio").modal("show");

      // Si hay un id_compromiso guardado, resaltar su fila
      const idCompromiso = sessionStorage.getItem("id_compromiso_modal");
      if (idCompromiso) {
        const highlight = setInterval(() => {
          const $fila = $("#dynamictable tbody tr").filter(function () {
            return $(this).find("td").first().text().trim() === idCompromiso;
          });

          if ($fila.length > 0) {
            $fila.addClass("bg-warning-subtle");
            $fila[0].scrollIntoView({ behavior: "smooth", block: "center" });
            clearInterval(highlight);
          }
        }, 300);
      }

      // Limpiar sessionStorage después de usar
      sessionStorage.removeItem("volver_modal");
      sessionStorage.removeItem("municipio_id_modal");
      sessionStorage.removeItem("secretaria_modal");
      sessionStorage.removeItem("componente_modal");
      sessionStorage.removeItem("tipo_ejecucion_modal");
      sessionStorage.removeItem("estado_modal");
      sessionStorage.removeItem("id_compromiso_modal");
    },
    columns: [
      { data: "id_compromiso" },
      { data: "secretaria" },
      { data: "compromisos", visible: false, render: renderCompromisoBtn },
      // { data: "compromisopac", render: renderCompromisoBtn },
      { data: "consecuencia", render: renderCompromisoBtn },
      { data: "respuesta", visible: false, render: renderCompromisoBtn },
     {
  data: "estado",
  render: function (data) {
    if (!data)
      return `<span style="background:#6c757d;color:#fff;padding:4px 10px;border-radius:4px;font-weight:600;">SIN ESTADO</span>`;

    // Convertir a minúsculas, quitar tildes y espacios extra
    const estado = data
      .toString()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();

    let bg = "#6c757d"; // gris por defecto
    let color = "#fff"; // texto blanco

    // Comparación insensible a mayúsculas/tildes
    if (estado.includes("cumplido")) {
      bg = "#28a745"; // verde
    } else if (estado.includes("tramite")) {
      bg = "#ffc107"; // amarillo
      color = "#212529"; // texto oscuro
    } else if (estado.includes("sin cumplir") || estado.includes("por cumplir")) {
      bg = "#dc3545"; // rojo
      color = "#fff";
    }

    return `<span style="
      background-color:${bg};
      color:${color};
      padding:4px 10px;
      border-radius:4px;
      font-weight:600;
      display:inline-block;
      text-align:center;
      min-width:90px;">
      ${data.toUpperCase()}
    </span>`;
  },
}

,

      { data: "municipio" },
      { data: "vereda" },
      { data: "componente" },
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

          if (!imagenSrc && !pdfSrc)
            return `<span class="text-muted">Sin adjunto</span>`;

          return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('${
            imagenSrc || ""
          }', '${pdfSrc || ""}')"></i>`;
        },
      },
      { data: "date" },
      {
        data: "update_at",
        render: function (data) {
          return data && data !== ""
            ? data
            : '<span class="text-muted">No hay avance</span>';
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          const compromisoId = row?.id_compromiso || row?.id;

          if (!compromisoId) {
            return `<span class="text-muted">Sin ID</span>`;
          }

          return `<form action="reporte-compromiso-alcalde.php" method="POST" style="display:inline;" target="_blank">
              <input type="hidden" name="reporte" value="1">
              <input type="hidden" name="id" value="${compromisoId}">
              <button type="submit" class="btn btn-sm btn-success4" title="Ver compromiso">
                <i class="feather icon-eye"></i>
              </button>
            </form>`;
        },
      },
    ],
  });

  $("#modalMunicipio").modal("show");
}

function filtraModal() {
  if (filtrarPorEstadoTodos == "todos") {
    filtrarPorEstadoTodos("todos");
  } else {
    filtrarPorEstado(ultimoEstado);
  }
}

function filtrarPorEstadoTodos() {
  ultimoEstado = "todos";

  // Obtener filtros desde sessionStorage o desde los selects visibles
  const secretariaId =
    sessionStorage.getItem("secretaria_modal") ||
    document.getElementById("tbl_secretarias_id_modal")?.value ||
    document.getElementById("tbl_secretarias_id")?.value ||
    "";

  const componente =
    sessionStorage.getItem("componente_modal") ||
    document.getElementById("componente_modal")?.value ||
    document.getElementById("componente")?.value ||
    "";

  const tipo_ejecucion =
    sessionStorage.getItem("tipo_ejecucion_modal") ||
    document.getElementById("tipo_ejecucion")?.value ||
    "";

  // IMPORTANTE: Si es usuario Alcalde, SIEMPRE usar su municipio
  const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
  const municipioUsuario = $('#municipioUsuario').val();

  const municipio = isUsuarioMunicipal && municipioUsuario
    ? municipioUsuario
    : (document.getElementById("tbl_municipio_id_modal")?.value ||
       sessionStorage.getItem("municipio_id_modal") ||
       document.getElementById("tbl_municipio_id")?.value ||
       "");
const vereda = $("#veredaFiltro").val() || "";


  // Setear visualmente los valores en los selects (por si fueron cargados por sessionStorage)
  $("#tbl_secretarias_id_modal").val(secretariaId);
  $("#componente_modal").val(componente);
  $("#tipo_ejecucion").val(tipo_ejecucion);
  $("#tbl_municipio_id").val(municipio);

  // Si es usuario Alcalde, asegurarse de que el select de municipio tenga su municipio
  if (isUsuarioMunicipal && municipioUsuario) {
    // Esperar a que se carguen los municipios y luego setear el valor
    setTimeout(() => {
      $("#tbl_municipio_id").val(municipioUsuario);
      cargarVeredasModal(); // Cargar veredas del municipio
    }, 500);
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
          method: "dataPorMunicipioSecretariaTodosLosEstados",
          draw: d.draw,
          start: d.start,
          length: d.length,
          search: d.search,
          order: d.order,
          data: {
            estado: "todos",
            secretaria: secretariaId,
            componente: componente,
            tipo_ejecucion: tipo_ejecucion,
            municipio: municipio,
            vereda: vereda
          },
        });
      },
    },
    initComplete: function () {
      $("#modalMunicipio").modal("show");

      // Si hay un id_compromiso guardado, resaltar su fila
      const idCompromiso = sessionStorage.getItem("id_compromiso_modal");
      if (idCompromiso) {
        const highlight = setInterval(() => {
          const $fila = $("#dynamictable tbody tr").filter(function () {
            return $(this).find("td").first().text().trim() === idCompromiso;
          });

          if ($fila.length > 0) {
            $fila.addClass("bg-warning-subtle");
            $fila[0].scrollIntoView({ behavior: "smooth", block: "center" });
            clearInterval(highlight);
          }
        }, 300);
      }

      // Limpiar sessionStorage después de usar
      sessionStorage.removeItem("volver_modal");
      sessionStorage.removeItem("municipio_id_modal");
      sessionStorage.removeItem("secretaria_modal");
      sessionStorage.removeItem("componente_modal");
      sessionStorage.removeItem("tipo_ejecucion_modal");
      sessionStorage.removeItem("estado_modal");
      sessionStorage.removeItem("id_compromiso_modal");
    },
    columns: [
      { data: "id_compromiso" },
      { data: "secretaria" },
      { data: "compromisos", visible: false, render: renderCompromisoBtn },
      // { data: "compromisopac", render: renderCompromisoBtn },
      { data: "consecuencia", render: renderCompromisoBtn },
      { data: "respuesta", visible: false, render: renderCompromisoBtn },
      {
  data: "estado",
  render: function (data) {
    if (!data)
      return `<span style="background:#6c757d;color:#fff;padding:4px 10px;border-radius:4px;font-weight:600;">SIN ESTADO</span>`;

    // Convertir a minúsculas, quitar tildes y espacios extra
    const estado = data
      .toString()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .trim();

    let bg = "#6c757d"; // gris por defecto
    let color = "#fff"; // texto blanco

    // Comparación insensible a mayúsculas/tildes
    if (estado.includes("cumplido")) {
      bg = "#28a745"; // verde
    } else if (estado.includes("tramite")) {
      bg = "#ffc107"; // amarillo
      color = "#212529"; // texto oscuro
    } else if (estado.includes("sin cumplir") || estado.includes("por cumplir")) {
      bg = "#dc3545"; // rojo
      color = "#fff";
    }

    return `<span style="
      background-color:${bg};
      color:${color};
      padding:4px 10px;
      border-radius:4px;
      font-weight:600;
      display:inline-block;
      text-align:center;
      min-width:90px;">
      ${data.toUpperCase()}
    </span>`;
  },
}
,

      { data: "municipio" },
      { data: "vereda" },
      { data: "componente" },
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

          if (!imagenSrc && !pdfSrc)
            return `<span class="text-muted">Sin adjunto</span>`;

          return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="mostrarAdjuntosModal('${
            imagenSrc || ""
          }', '${pdfSrc || ""}')"></i>`;
        },
      },
      { data: "date" },
      {
        data: "update_at",
        render: function (data) {
          return data && data !== ""
            ? data
            : '<span class="text-muted">No hay avance</span>';
        },
      },
      {
        data: null,
        render: function (data, type, row) {
          const compromisoId = row?.id_compromiso || row?.id;

          if (!compromisoId) {
            return `<span class="text-muted">Sin ID</span>`;
          }

          return `<form action="reporte-compromiso-alcalde.php" method="POST" style="display:inline;" target="_blank">
              <input type="hidden" name="reporte" value="1">
              <input type="hidden" name="id" value="${compromisoId}">
              <button type="submit" class="btn btn-sm btn-success4" title="Ver compromiso">
                <i class="feather icon-eye"></i>
              </button>
            </form>`;
        },
      },
    ],
  });

  $("#modalMunicipio").modal("show");
}

// Función para cargar veredas basado en el municipio seleccionado
function cargarVeredasModal() {
    // IMPORTANTE: Si es usuario Alcalde, usar su municipio. Si no, usar el del modal
    const isUsuarioMunicipal = $('#isUsuarioMunicipal').val() === '1';
    const municipioUsuario = $('#municipioUsuario').val();

    const municipioId = isUsuarioMunicipal && municipioUsuario
        ? municipioUsuario
        : ($("#tbl_municipio_id_modal").val() || $("#tbl_municipio_id").val());

    const $selectVereda = $("#veredaFiltro");

    // Limpiar select de veredas
    $selectVereda.empty();
    $selectVereda.append('<option value="">Cargando veredas...</option>');

    if (!municipioId) {
        $selectVereda.empty();
        $selectVereda.append('<option value="">Seleccione primero un municipio</option>');
        return;
    }

    $.ajax({
        url: "./admin/controllers/utilsCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            method: "veredas",
            municipio: municipioId
        }),
        success: function(res) {
            $selectVereda.empty();
            $selectVereda.append(`<option value="">Todas las veredas</option>`);

            if (res.response && res.response.length > 0) {
                res.response.forEach(v => {
                    $selectVereda.append(`<option value="${v.id}">${v.nombre_vereda}</option>`);
                });
            } else {
                $selectVereda.append(`<option value="">Sin veredas disponibles</option>`);
            }
        },
        error: function(e) {
            console.error("Error cargando veredas:", e);
            $selectVereda.empty();
            $selectVereda.append(`<option value="">Error al cargar veredas</option>`);
        }
    });
}


function mostrarAdjuntosModal(imagenSrc, pdfSrc) {
  let contenido = "";

  if (imagenSrc) {
    contenido += `
    <div class="mb-4 text-center">
      <img src="${imagenSrc}" class="img-fluid mb-2" alt="Imagen" style="max-height:300px;" />
      <br />
      <a href="${imagenSrc}" target="_blank" class="btn btn-outline-primary btn-sm">
        Abrir imagen en nueva pestaña
      </a>
    </div>
  `;
  }

  if (pdfSrc) {
    contenido += `
    <div class="mb-4 text-center">
      <embed src="${pdfSrc}" type="application/pdf" width="100%" height="500px" />
      <br />
      <a href="${pdfSrc}" target="_blank" class="btn btn-outline-danger btn-sm mt-2">
        Abrir PDF en nueva pestaña
      </a>
    </div>
  `;
  }

  if (!contenido) {
    contenido = `<p class="text-muted">No hay archivos para mostrar.</p>`;
  }

  document.getElementById("archivoModalBody").innerHTML = contenido;

  const modal = new bootstrap.Modal(document.getElementById("archivoModal"));
  modal.show();
}
