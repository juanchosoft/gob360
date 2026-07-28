$(document).ready(init);
var q;
function init() {
  q = {};
  MINISTERIOSPRO.getSecretaria();
  MINISTERIOSPRO.cargaData();
  //toggleSecretariaSelect();
  $("#aporteOtrosProyectos").trigger("input");
}
//let proyectos = null;
var MINISTERIOSPRO = {
  getSecretaria: function () {
    q = {};
    q.op = "secretariaget";
    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid && Array.isArray(data.output.response)) {
          const $select = $("#tbl_secretarias_id");
          $select.empty();
          $select.append(`<option value="">Seleccione una secretaria</option>`);
          data.output.response.forEach((item) => {
            $select.append(
              `<option value="${item.id}">${item.secretaria}</option>`
            );
          });
        } else {
          console.warn("Respuesta inválida o vacía:", data);
        }
      },
      error: function (err) {
        console.error("Error al cargar secretarias:", err);
      },
    });
  },
  cargaData: function () {
    /* if ($.fn.DataTable.isDataTable("#dynamictable")) {
      proyectos.destroy();
    } */
    proyectos = $("#dynamictable").DataTable({
      dom: "lrtip",
      processing: true,
      serverSide: true,
      responsive: true,
      columnDefs: [
        {
          targets: ["_all"],
          className: "mdc-data-table__cell",
        },
      ],
      ajax: {
        url: "./admin/controllers/alcaldiaCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: function (d) {
          return JSON.stringify({
            method: "getAllproyectos",
            data: d,
          });
        },
      },
      columns: [
        {
          data: "id",
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-primary editar-informacion" 
                  onclick="MINISTERIOSPRO.editProyecto('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
          },
        },
        {
          data: "date",
        },
        {
          data: "estado",
          render: function (data, type, row) {
            if (type !== "display") return data;

            if (data === "No leído") {
              return `<span class="badge badge-warning">No leído</span>`;
            } else {
              return `<span class="badge bg-success">${data}</span>`;
            }
          },
        },
        {
          data: "fecha_actualizacion",
        },
        {
          data: "proyecto",
        },
        {
          data: "municipio",
        },
        {
          data: "valor_proyecto",
        },
        {
          data: "secretaria",
        },
        {
          data: "archivos",
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            if (type !== "display" || !data)
              return `<span class="text-muted">Sin adjunto</span>`;

            const imagenMatch = data.match(
              /<img\s+[^>]*src="([^"]+\.(jpg|jpeg|png|gif|webp))"[^>]*>/i
            );
            const imagenSrc = imagenMatch ? imagenMatch[1] : null;

            const pdfMatch = data.match(
              /mostrarArchivoModal\('([^']+\.pdf)'\)/i
            );
            const pdfSrc = pdfMatch ? pdfMatch[1] : null;

            if (!imagenSrc && !pdfSrc) {
              return `<span class="text-muted">Sin adjunto</span>`;
            }

            return `<i class="feather icon-eye" style="font-size: 20px; cursor: pointer;" onclick="MINISTERIOSPRO.mostrarAdjuntosModal('${
              imagenSrc || ""
            }', '${pdfSrc || ""}')"></i>`;
          },
        },
      ],
      createdRow: function (row, data, dataIndex) {
        $(row).attr("id", "fila_" + data.id);
      },
    });
    $("#customSearch")
      .off()
      .on("keyup", function () {
        if (proyectos) {
          proyectos.search(this.value).draw();
        }
      });
  },
  mostrarAdjuntosModal: function (imagenSrc, pdfSrc) {
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
  },
  editProyecto: function (id) {
    $.ajax({
      url: "./admin/controllers/alcaldiaCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        method: "editProyecto",
        data: id,
      }),
      success: function (response) {
        if (response.state) {
          console.log(response.data);
          const proyecto = response.data;
          console.log(proyecto.id);
          $("#modalId").val(proyecto.id).prop("readonly", true);
          $("#date").val(proyecto.date).prop("readonly", true);
          $("#provincia").val(proyecto.provincia_id).prop("disabled", true);
          $("#tbl_municipio_id").val(proyecto.municipio_id).prop("disabled", true);
          $("#municipio").val(proyecto.municipio).prop("disabled", true);
          $("#proyecto").val(proyecto.proyecto);
          $("#tbl_secretarias_id").val(proyecto.tbl_secretaria_id);
          $("#modalAporteMunicipio").val(proyecto.aporte_municipio);
          $("#modalAporteDepartamento").val(proyecto.aporte_departamento);
          $("#modalNacion").val(proyecto.aporte_nacion);
          $("#modalOtrosAportes").val(proyecto.otro_aportes);
          $("#valor_proyecto").val(proyecto.valor_proyecto);
          $("#observaciones").val("");
          $("#actormostrar").val(proyecto.actor).prop("readonly", true);
          if (
            Array.isArray(proyecto.observaciones) &&
            proyecto.observaciones.length > 0
          ) {
            let tabla = `
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Observación</th>
                </tr>
              </thead>
              <tbody>
          `;
            proyecto.observaciones.forEach((obs) => {
              tabla += `
              <tr>
                <td>${obs.dtcreate ? obs.dtcreate.split(" ")[0] : ""}</td>
                <td>${
                  obs.observaciones
                    ? $("<div>").text(obs.observaciones).html()
                    : ""
                }</td>
              </tr>
            `;
            });
            tabla += `
              </tbody>
            </table>
          `;
            $("#contenedorObservaciones").html(tabla);
          } else {
            $("#contenedorObservaciones").html(
              '<br><div class="alert alert-info">Sin observaciones</div>'
            );
          }

          $("#modalFormularioProyectos").modal();

          getActores();
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en la solicitud:", error);
      },
    });
  },
  showProyecto: function (id) {
    q = {};
    q.op = "leerproyectoyactualizarestado";
    q.id = id;
    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "GET",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        q = {};
        UTIL.cursorNormal();
        if (data.output.valid) {
          if (
            !data.output.response ||
            !Array.isArray(data.output.response) ||
            data.output.response.length === 0
          ) {
            UTIL.mostrarMensajeError(
              "No se encontró información del proyecto."
            );
            return;
          }
          const proyecto = data.output.response[0];
          $("#modalId").val(proyecto.id).prop("readonly", true);
          $("#date").val(proyecto.date).prop("readonly", true);
          $("#provincia").val(proyecto.provincia_id).prop("disabled", true);
          $("#tbl_municipio_id").val(proyecto.municipio).prop("disabled", true);
          $("#proyecto").val(proyecto.proyecto);
          $("#tbl_secretarias_id").val(proyecto.tbl_secretaria_id);
          $("#modalAporteMunicipio").val(proyecto.aporte_municipio);
          $("#modalAporteDepartamento").val(proyecto.aporte_departamento);
          $("#modalNacion").val(proyecto.aporte_nacion);
          $("#modalOtrosAportes").val(proyecto.otro_aportes);
          $("#valor_proyecto").val(proyecto.valor_proyecto);
          $("#observaciones").val("");
          $("#actormostrar").val(proyecto.actor).prop("readonly", true);

          // Tabla de observaciones
          if (
            Array.isArray(proyecto.observaciones) &&
            proyecto.observaciones.length > 0
          ) {
            let tabla = `
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Observación</th>
                </tr>
              </thead>
              <tbody>
          `;
            proyecto.observaciones.forEach((obs) => {
              tabla += `
              <tr>
                <td>${obs.dtcreate ? obs.dtcreate.split(" ")[0] : ""}</td>
                <td>${
                  obs.observaciones
                    ? $("<div>").text(obs.observaciones).html()
                    : ""
                }</td>
              </tr>
            `;
            });
            tabla += `
              </tbody>
            </table>
          `;
            $("#contenedorObservaciones").html(tabla);
          } else {
            $("#contenedorObservaciones").html(
              '<div class="alert alert-info">Sin observaciones</div>'
            );
          }

          $("#modalFormularioProyectos").modal();
        } else {
          UTIL.mostrarMensajeError(data.output.response.content);
        }
      },
    });
  },
  saveActualizarEstado: function (id, estado) {
    q = {};
    q.op = "actuaizarestadoproyecto";
    q.idEditar = id;
    q.estado = estado;
    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        q = {};
        UTIL.cursorNormal();
        if (data.output.valid) {
          location.reload();
        } else {
          UTIL.mostrarMensajeError(data.output.response.content);
        }
      },
    });
    UTIL.cursorNormal();
    if (data.output.valid) {
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },
  validateData: function () {
    var bValid = true;
    var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
    if ($("#date").val() == "" || $("#proyecto").val() == null) {
      UTIL.mostrarMensajeValidacion(msj);
      bValid = false;
      return;
    }
    if (bValid) {
      MINISTERIOSPRO.saveInformacion();
    }
  },

  clearForm: function () {
    $("#date").val("");
    $("#proyecto").val("");
    $("#observaciones").val("");
    $("#tbl_municipio_id").val("");
    $("#aporteMunicipio").val("");
    $("#aporteDepartamento").val("");
    $("#aporteNacion").val("");
    $("#aporteOtrosProyectos").val("");
    $("#tbl_secretarias_id").val("");
    $("#actores_id").val("");
  },
  saveInformacion: function () {
    if (
      $("#tbl_secretarias_id").val() == "" ||
      $("#tbl_secretarias_id").val() == null
    ) {
      Swal.fire({
        title: "Debes seleccionar una secretaria",
        text: "Por favor, selecciona una secretaria para continuar.",
        icon: "warning",
        confirmButtonText: "Aceptar",
      });
      return;
    }
    const otrosAportes = $("#aporteOtrosProyectos")
      .val()
      .replaceAll(".", "")
      .trim();

    if (
      otrosAportes !== "" &&
      otrosAportes !== "0" &&
      otrosAportes !== "0.00"
    ) {
      const actor = $("#actores_id").val();

      if (!actor || actor === "") {
        Swal.fire({
          title: "Debe seleccionar un actor",
          text: "Por favor, seleccione el actor correspondiente a otros aportes.",
          icon: "warning",
          confirmButtonText: "Aceptar",
        });
        return;
      }
    }

    if ($("#observaciones").val() == "" || $("#observaciones").val() == null) {
      Swal.fire({
        title: "Debe ingresar observaciones",
        text: "Por favor, ingresa una observación para continuar.",
        icon: "warning",
        confirmButtonText: "Aceptar",
      });
      return;
    }

    Swal.fire({
      title: "Estás seguro ingresar la información?",
      text: "¿Desea continuar?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "Cancelar!",
      closeOnConfirm: false,
    }).then((result) => {
      if (result.value) {
        const iframe1 = $("#ifm1").attr("data-url") || null;
        const ifmPdf = $("#ifmPdf").attr("data-url") || null;
        q = {};
        q.op = "ingresoproyectosmin_save";
        q.id = $("#id").val();
        q.iframe1 = iframe1;
        q.pdf = ifmPdf;
        //q.tbl_ministerios_id = $("#tbl_ministerios_id").val();
        q.proyecto = $("#proyecto").val();
        q.date = $("#date").val();
        q.provincia = $("#provincia").val();
        q.tbl_departamento_id = $("#tbl_departamento_id").val();
        q.tbl_municipio_id = $("#tbl_municipio_id").val();
        q.aporteMunicipio = $("#aporteMunicipio").val();
        q.aporteDepartamento = $("#aporteDepartamento").val();
        q.aporteNacion = $("#aporteNacion").val();
        q.aporteOtrosProyectos = $("#aporteOtrosProyectos").val();
        q.tbl_secretarias_id = $("#tbl_secretarias_id").val();
        q.valor_proyecto = $("#valor_proyecto").val();
        q.observaciones = $("#observaciones").val();
        q.actores_id = $("#actores_id").val();
        UTIL.cursorBusy();
        $.ajax({
          data: q,
          type: "POST",
          dataType: "json",
          url: "admin/ajax/rqst.php",
          success: function (data) {
            q = {};
            UTIL.cursorNormal();
            if (data.output.valid) {
              UTIL.mostrarMensajeExitoso("Información guardada correctamente");
              setTimeout(function () {
                window.location = "proyectos_alcaldias.php";
              }, 1000);
            } else {
              UTIL.mostrarMensajeError(data.output.response.content);
            }
          },
        });
      }
    });
  },
};

function debounce(func, wait) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

function formatNumberInput(input) {
  let value = input.value.replace(/\D/g, "");
  if (value) {
    input.value = parseInt(value).toLocaleString("es-CO");
  } else {
    input.value = "";
  }
}

function getCleanNumber(selector) {
  let raw = $(selector).val() || "0";
  let clean = raw.replace(/\./g, "").replace(/,/g, ".");
  return parseFloat(clean) || 0;
}

function calcularTotalInversion() {
  let municipio = getCleanNumber("#aporteMunicipio");
  let departamento = getCleanNumber("#aporteDepartamento");
  let nacion = getCleanNumber("#aporteNacion");
  let otros = getCleanNumber("#aporteOtrosProyectos");

  let total = municipio + departamento + nacion + otros;

  let totalFormateado = total.toLocaleString("es-CO", {
    style: "currency",
    currency: "COP",
  });

  $("#valor_proyecto").val(totalFormateado);

  toggleSecretariaSelectDebounced();
}

function getActores() {
  q = {};
  q.op = "getByAlcaldia";
  q.alcaldia_id = $("#tbl_municipio_id").val();
  UTIL.cursorBusy();
  $.ajax({
    data: q,
    type: "POST",
    dataType: "json",
    url: "admin/ajax/rqst.php",
    success: function (data) {
      UTIL.cursorNormal();
      if (data.output.valid && Array.isArray(data.output.response)) {
        const $select = $("#actores_id");
        $select.empty();

        $select.append(`<option value="">Seleccione el actor</option>`);

        data.output.response.forEach((item) => {
          $select.append(`<option value="${item.id}">${item.nombre}</option>`);
        });
      } else {
        console.warn("Respuesta inválida o vacía:", data);
      }
    },
    error: function (err) {
      console.error("Error al cargar secretarias:", err);
    },
  });
}

function getActoresEdit() {
  return new Promise((resolve, reject) => {
    const q = {
      op: "getByAlcaldia",
      alcaldia_id: $("#tbl_municipio_id").val(),
    };

    UTIL.cursorBusy();

    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();

        const $select = $("#actores_id");
        $select.empty();
        $select.append(`<option value="">Seleccione el actor</option>`);

        if (data.output.valid && Array.isArray(data.output.response)) {
          data.output.response.forEach((item) => {
            $select.append(
              `<option value="${item.id}">${item.nombre}</option>`
            );
          });
          resolve(); // 👈 IMPORTANTE: resolvemos cuando se cargan
        } else {
          console.warn("Respuesta inválida o vacía:", data);
          resolve(); // aún resolvemos para no trabar la cadena
        }
      },
      error: function (err) {
        UTIL.cursorNormal();
        console.error("Error al cargar actores:", err);
        reject(err); // En caso de error real
      },
    });
  });
}

/* function getSecretaria() {
  q = {};
  q.op = "secretariaget";
  UTIL.cursorBusy();
  $.ajax({
    data: q,
    type: "POST",
    dataType: "json",
    url: "admin/ajax/rqst.php",
    success: function (data) {
      UTIL.cursorNormal();
      if (data.output.valid && Array.isArray(data.output.response)) {
        const $select = $("#tbl_secretarias_id");
        $select.empty();
        $select.append(`<option value="">Seleccione una secretaria</option>`);
        data.output.response.forEach((item) => {
          $select.append(
            `<option value="${item.id}">${item.secretaria}</option>`
          );
        });
      } else {
        console.warn("Respuesta inválida o vacía:", data);
      }
    },
    error: function (err) {
      console.error("Error al cargar secretarias:", err);
    },
  });
} */

function getSecretariaEdit() {
  return new Promise((resolve, reject) => {
    const q = { op: "secretariaget" };
    UTIL.cursorBusy();

    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid && Array.isArray(data.output.response)) {
          const $select = $("#tbl_secretarias_id");
          $select.empty();
          console.log("Secretarias cargadas:", data.output.response);

          $select.append(`<option value="">Seleccione una secretaria</option>`);

          data.output.response.forEach((item) => {
            $select.append(
              `<option value="${item.id}">${item.secretaria}</option>`
            );
          });

          resolve(); // ← ¡resolvemos la promesa aquí!
        } else {
          console.warn("Respuesta inválida o vacía:", data);
          reject("Respuesta inválida");
        }
      },
      error: function (err) {
        console.error("Error al cargar secretarias:", err);
        reject(err);
      },
    });
  });
}

const toggleSecretariaSelectDebounced = debounce(function () {
  let otros = getCleanNumber("#aporteOtrosProyectos");
  if (otros > 0) {
    getActores();
    $("#container_actores").show();
  } else {
    $("#container_actores").hide();
    $("#actores_id").val("");
    $("#actores_id").val("");
  }
}, 200);

$(document).on(
  "input",
  "#aporteMunicipio, #aporteDepartamento, #aporteNacion, #aporteOtrosProyectos",
  function () {
    formatNumberInput(this);
    calcularTotalInversion();
  }
);

/* $(document).on("click", ".editar-informacion", function () {
  getSecretariaEdit().then(() => {
    const button = $(this);

    const id = button.data("id");
    const fecha = button.data("fecha");
    const departamento = button.data("departamento");
    const municipio = button.data("municipio");
    const vereda = button.data("vereda");
    const aporte_municipio = button.data("aporte_municipio");
    const aporte_nacion = button.data("aporte_nacion");
    const aporte_departamento = button.data("aporte_departamento");
    const otro_aportes = button.data("otros_aportes");
    const secretaria = button.data("secretaria");
    const observaciones = button.data("observaciones");
    const tbl_municipio_id = button.data("tbl_municipio_id");
    const actor_id = button.data("actor_id");

    const valor =
      parseFloat(aporte_municipio || 0) +
      parseFloat(aporte_nacion || 0) +
      parseFloat(aporte_departamento || 0) +
      parseFloat(otro_aportes || 0);

    const valorFormateado = valor.toLocaleString("es-CO", {
      style: "decimal",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    const modal = $("#modalEditarInformacion");

    modal.find('input[name="id"]').val(id);
    modal.find('input[name="modalFecha"]').val(fecha);
    modal.find('input[name="modalDepartamento"]').val(departamento);
    modal.find('input[name="modalMunicipio"]').val(municipio);
    modal.find('input[name="modalVereda"]').val(vereda);
    modal.find('input[name="modalNacion"]').val(aporte_nacion);
    modal.find('input[name="tbl_municipio_id"]').val(tbl_municipio_id);
    modal
      .find('input[name="modalAporteDepartamento"]')
      .val(aporte_departamento);
    modal.find('input[name="modalAporteMunicipio"]').val(aporte_municipio);
    modal.find('input[name="modalOtrosAportes"]').val(otro_aportes);
    modal.find('select[name="tbl_secretarias_id"]').val(secretaria);

    modal.find('input[name="modalValor"]').val(valorFormateado);
    modal.find('textarea[name="observaciones"]').val(observaciones);
    getActoresEdit().then(() => {
      modal
        .find('select[name="actores_id"]')
        .val(String(actor_id))
        .trigger("change");
      modal.modal("show");
    });
  });
}); */

$(document).on(
  "input",
  'input[name="modalAporteMunicipio"], input[name="modalNacion"], input[name="modalAporteDepartamento"], input[name="modalOtrosAportes"]',
  function () {
    const modal = $("#modalEditarInformacion");

    const aporte_municipio =
      parseFloat(
        modal.find('input[name="modalAporteMunicipio"]').val().replace(/,/g, "")
      ) || 0;
    const aporte_nacion =
      parseFloat(
        modal.find('input[name="modalNacion"]').val().replace(/,/g, "")
      ) || 0;
    const aporte_departamento =
      parseFloat(
        modal
          .find('input[name="modalAporteDepartamento"]')
          .val()
          .replace(/,/g, "")
      ) || 0;
    const otro_aportes =
      parseFloat(
        modal.find('input[name="modalOtrosAportes"]').val().replace(/,/g, "")
      ) || 0;

    const valor =
      aporte_municipio + aporte_nacion + aporte_departamento + otro_aportes;

    const valorFormateado = valor.toLocaleString("es-CO", {
      style: "decimal",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    modal.find('input[name="modalValor"]').val(valorFormateado);
  }
);

function actualizarValorTotal() {
  const municipio =
    parseFloat(
      $('#modalEditarInformacion input[name="modalAporteMunicipio"]').val()
    ) || 0;
  const nacion =
    parseFloat($('#modalEditarInformacion input[name="modalNacion"]').val()) ||
    0;
  const departamento =
    parseFloat(
      $('#modalEditarInformacion input[name="modalAporteDepartamento"]').val()
    ) || 0;
  const otros =
    parseFloat(
      $('#modalEditarInformacion input[name="modalOtrosAportes"]').val()
    ) || 0;

  const total = municipio + nacion + departamento + otros;
  $('#modalEditarInformacion input[name="modalValor"]').val(total.toFixed(2));
}

$("#modalEditarInformacion").on(
  "input",
  'input[name="modalAporteMunicipio"], input[name="modalNacion"], input[name="modalAporteDepartamento"], input[name="modalOtrosAportes"]',
  actualizarValorTotal
);

function deleteProyecto(id) {
  Swal.fire({
    title: "Está completamente que desea eliminar el registro?",
    text: "¿Desea continuar?",
    type: "warning",
    showCancelButton: true,
    confirmButtonText: "Si",
    cancelButtonText: "Cancelar!",
    closeOnConfirm: false,
  }).then((result) => {
    if (result.value) {
      q = {};
      q.op = "deleteproyecto";
      q.id = id;
      UTIL.cursorBusy();
      $.ajax({
        data: q,
        type: "POST",
        dataType: "json",
        url: "admin/ajax/rqst.php",
        success: function (response) {
          q = {};
          UTIL.cursorNormal();
          if (response.output.valid) {
            UTIL.mostrarMensajeExitoso("Información eliminada correctamente");
            const fila = document.getElementById("fila_" + id);

            if (fila) {
              fila.remove();
            }
          } else {
            UTIL.mostrarMensajeError(response.output.response.content);
          }
        },
      });
    }
  });
}

function verObservaciones(observacion) {
  const body = document.getElementById("modalObservacionesBody");
  body.textContent = observacion || "Sin observaciones";

  const modal = new bootstrap.Modal(
    document.getElementById("modalObservaciones")
  );
  modal.show();
}

function mostrarAlertaModal(tipo, mensaje, contenedor = "alerta-modal") {
  let alertDiv = `
        <div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
  document.getElementById(contenedor).innerHTML = alertDiv;
}

function guardarEdicion() {
  let id = $("#modalId").val();
  let aporte_nacion = $("#modalNacion").val();
  let aporte_departamento = $("#modalAporteDepartamento").val();
  let aporte_municipio = $("#modalAporteMunicipio").val();
  let otro_aportes = $("#modalOtrosAportes").val();
  let secretaria = $("#tbl_secretarias_id").val();
  let observaciones = $("#observaciones").val();
  let actor_id = $("#actores_id").val();

  if (!id) {
    UTIL.mostrarMensajeError("❌ ID no válido o valor incorrecto..");
    return;
  }

  const datos = {
    op: "editarInformacionProyecto",
    id: id,
    aporte_nacion: aporte_nacion,
    aporte_departamento: aporte_departamento,
    aporte_municipio: aporte_municipio,
    otro_aportes: otro_aportes,
    secretaria: secretaria,
    observaciones: observaciones,
    actor_id: actor_id,
  };
  $.ajax({
    url: "admin/ajax/rqst.php",
    type: "POST",
    data: datos,
    dataType: "json",
    success: function (response) {
      console.log("Respuesta de la edición:", response);
      if (response.output.valid) {
        UTIL.mostrarMensajeExitoso(
          "Información actualizada correctamente. Espere porfavor..."
        );
        setTimeout(function () {
          location.reload();
        }, 2000);
      } else {
        UTIL.mostrarMensajeError(response.output.response.content);
      }
    },
    error: function (xhr, status, error) {
      UTIL.mostrarMensajeError("Error editando la información.");
    },
  });
}

// Utilidad para formatear con separador de miles
function formatearMoneda(valor) {
  return parseFloat(valor).toLocaleString("es-CO", {
    style: "currency",
    currency: "COP",
    minimumFractionDigits: 0,
  });
}
