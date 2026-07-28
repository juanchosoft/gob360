$(document).on("ready", init);

function init() {}

function formatearPuntaje(valor) {
  return Number(valor || 0).toLocaleString("es-CO", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function mensajeInicialTabla() {
  return `
    <tr id="mensajeInicial">
      <td style="font-size:15px;color:var(--muted2) !important" colspan="5" class="text-center">
        Seleccione el factor de inestabilidad y el municipio para listar las veredas con puntaje inicial en Medio, Alto o Crítico.
      </td>
    </tr>`;
}

function limpiarTabla() {
  $("#tablaVeredas tbody").html(mensajeInicialTabla());
}

function buscarVeredasCriticas() {
  var departamento = $("#tbl_departamento_id").val();
  var municipio = $("#tbl_municipio_id").val();
  var inestabilidad = $("#inestabilidadId").val();

  if (!departamento || !municipio || !inestabilidad) {
    return;
  }

  document.body.style.cursor = "wait";

  $.ajax({
    url: "admin/ajax/rqst.php",
    type: "GET",
    data: {
      op: "getVeredasCriticas",
      departamento: departamento,
      municipio: municipio,
      inestabilidad: inestabilidad,
    },
    dataType: "json",
    success: function (response) {
      if (response.output && response.output.valid) {
        actualizarTabla(response.output.response, inestabilidad, municipio);
      } else {
        var mensaje =
          (response.output &&
            response.output.response &&
            response.output.response.content) ||
          "No se encontraron veredas críticas.";
        $("#tablaVeredas tbody").html(
          '<tr><td colspan="5" class="text-center">' + mensaje + "</td></tr>"
        );
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "No fue posible consultar las veredas críticas.",
        confirmButtonColor: "#d33",
      });
    },
    complete: function () {
      document.body.style.cursor = "default";
    },
  });
}

$(document).ready(function () {
  $("#tbl_municipio_id").on("change", function () {
    if ($(this).val()) {
      buscarVeredasCriticas();
    } else {
      limpiarTabla();
    }
  });
});

$("#btnSeleccionar").click(function () {
  var departamento = $("#tbl_departamento_id").val();
  var municipio = $("#tbl_municipio_id").val();
  var inestabilidad = $("#inestabilidadId").val();

  if (!departamento || !municipio || !inestabilidad) {
    Swal.fire({
      icon: "error",
      title: "Faltan datos requeridos",
      text: "Seleccione el factor de inestabilidad y el municipio antes de continuar.",
      confirmButtonText: "Entendido",
      confirmButtonColor: "#d33",
    });
    return;
  }

  buscarVeredasCriticas();
});

function actualizarTabla(data, inestabilidadId, municipioId) {
  var tbody = $("#tablaVeredas tbody");
  tbody.empty();

  if (!data || data.length === 0) {
    tbody.append(
      '<tr><td colspan="5" class="text-center">No hay veredas con puntaje inicial en Medio, Alto o Crítico para este municipio.</td></tr>'
    );
    return;
  }

  data.forEach(function (vereda) {
    var urlDetalle =
      "veredas_inestabilidad.php?id=" +
      encodeURIComponent(vereda.id) +
      "&mun=" +
      encodeURIComponent(municipioId) +
      "&inestabilidad=" +
      encodeURIComponent(inestabilidadId);

    var fila = `
      <tr>
        <td style="text-align:left">
          <a href="${urlDetalle}" class="vereda-link">${vereda.nombre_vereda}</a>
        </td>
        <td style="text-align:center">${vereda.municipio || ""}</td>
        <td style="text-align:center">
          <span class="puntaje-badge">
            <span class="color-dot" style="background:${vereda.color_inicial || "#ccc"};"></span>
            ${formatearPuntaje(vereda.puntaje_inicial)}
          </span>
        </td>
        <td style="text-align:center">
          <span class="puntaje-badge">
            <span class="color-dot" style="background:${vereda.color_actual || "#ccc"};"></span>
            ${formatearPuntaje(vereda.puntaje_actual)}
          </span>
        </td>
        <td style="text-align:center">
          <a href="${urlDetalle}" class="btn btn-sm btn-success" title="Ver detalle">
            <i class="feather icon-eye"></i>
          </a>
        </td>
      </tr>`;
    tbody.append(fila);
  });
}
