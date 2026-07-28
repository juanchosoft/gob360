$(document).ready(initingresoinformacion);

function initingresoinformacion() {
  INGRESO_INFORMACION.cargaData();
}

const INGRESO_INFORMACION = {
  cargaData: function () {
    tablaListInfo = $("#dynamictable").DataTable({
      processing: true,
      serverSide: true,
      ordering: false,
      responsive: true,
      dom: "lrtip",
      columnDefs: [
        {
          targets: ["_all"],
          className: "mdc-data-table__cell",
          orderable: false,
          targets: [0, 1, 10],
        },
      ],
      ajax: {
        url: "./admin/controllers/ingresoInformacionCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: function (d) {
          return JSON.stringify({
            method: "load",
            data: d,
          });
        },
      },
      columns: [
        {
          data: "id",
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-primary editar-informacion"
                    title="Editar" data-toggle="modal"
                    data-target="#modalEditarInformacion"
                    data-id="${data}"
                    data-longitud="${row.longitud}"
                    data-latitud="${row.latitud}"
                    data-fecha="${row.dtcreate}"
                    data-departamento="${row.departamento}"
                    data-municipio="${row.municipio}"
                    data-vereda="${row.vereda}"
                    data-factor="${row.factor}"
                    data-valor="${row.valor}"
                    data-observaciones="${row.observaciones}"
                    data-icono="${row.icono}">
                    <i class="feather icon-edit"></i>
                </button>`;
          },
        },
        {
          data: "id",
          render: function (data) {
            return `<button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                        onclick="INGRESO_INFORMACION.delete(${data})">
                        <i class="feather icon-trash"></i>
                    </button>`;
          },
        },
        {
          data: "dtcreate",
        },
        {
          data: "departamento",
        },
        {
          data: "municipio",
        },
        {
          data: "vereda",
        },
        {
          data: "latitud",
          visible: false,
        },
        {
          data: "longitud",
          visible: false,
        },
        {
          data: "factor",
        },
        {
          data: "valor",
        },
        {
          data: "observaciones",
          render: function (data) {
            return `<button type="button" class="btn btn-sm btn-info" title="Ver Observaciones"
                         onclick="verObservaciones('${data}')">
                         <i class="feather icon-eye"></i>
                     </button>`;
          },
        },
        {
          data: null,
          render: function (row) {
            const fotos = [row.foto1, row.foto2, row.foto3, row.foto4].filter(
              Boolean
            ); // solo imágenes existentes

            if (fotos.length > 0) {
              const fotosJson = JSON.stringify(fotos);
              return `<button type="button" class="btn btn-sm btn-primary" title="Ver imágenes"
      onclick='mostrarImagenes(${fotosJson})'>
      <i class="feather icon-image"></i>
    </button>`;
            }
            return `<button type="button" class="btn btn-sm btn-danger" title="No tiene Imágenes">
                        <i class="feather icon-slash"></i>
                    </button>`;
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
        if (tablaListInfo) {
          tablaListInfo.search(this.value).draw();
        }
      });
  },
  openImage: function (src) {
    // Abre la imagen en una nueva ventana o pestaña
    window.open(src, "_blank");
  },
  delete: function (id) {
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
        q.op = "deleteingresoinformacion";
        q.id = id;
        UTIL.cursorBusy();
        $.ajax({
          data: q,
          type: "GET",
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
  },
  save() {
    const msj =
      "Falta ingresar información obligatoria, marcada con asterisco.";

    // Validar campos obligatorios
    const camposRequeridos = [
      "#tbl_departamento_id",
      "#tbl_municipio_id",
      "#tbl_vereda_id",
      "#factorId",
      "#valor",
    ];

    if (!this.validarCampos(camposRequeridos)) {
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }

    const iframe1 = $("#ifm1").attr("data-url") || null;
    const iframe2 = $("#ifm2").attr("data-url") || null;
    const iframe3 = $("#ifm3").attr("data-url") || null;
    const iframe4 = $("#ifm4").attr("data-url") || null;

    // Crear objeto con datos
    const datos = {
      op: "ingresoinformacionsave",
      id: $("#id").val(),
      codDepartamento_id: $("#tbl_departamento_id").val(),
      codMunicipio_id: $("#tbl_municipio_id").val(),
      vereda_id: $("#tbl_vereda_id").val(),
      factorId: $("#factorId").val(),
      longitud: $("#longitud").val(),
      observaciones: $("#observaciones").val(),
      latitud: $("#latitud").val(),
      valor: $("#valor").val(),
      foto1: iframe1,
      foto2: iframe2,
      foto3: iframe3,
      foto4: iframe4,
    };

    // Llamada AJAX
    UTIL.callAjaxRqstPOST(datos, INGRESO_INFORMACION.savehandler);
  },

  savehandler(data) {
    UTIL.cursorNormal();

    if (data.output.valid) {
      UTIL.mostrarMensajeExitoso("Información guardada correctamente");
      setTimeout(() => {
        window.location = "";
      }, 1000);
    } else {
      UTIL.mostrarMensajeError(
        data.output.response.content || "Error al guardar la información"
      );
    }
  },

  showInfoGetFactores: function () {
    let id = $("#factorId").val();
    if (id > 0) {
      q = {};
      q.op = "getFactores";
      q.id = $("#factorId").val();
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
            let res = data.output.response[0];
            $("#eje").val(res.eje);
            $("#pilar").val(res.pilar);
            $("#area").val(res.area);
            $("#tipo_medicion").val(res.tipo_medicion);

            $("#divInformacion").show();
          } else {
            $("#divInformacion").hide();
          }
        },
      });
    } else {
      $("#divInformacion").hide();
      $("#eje").val("");
      $("#pilar").val("");
      $("#area").val("");
      $("#tipo_medicion").val("");
    }
  },
  // Función auxiliar para validar campos
  validarCampos(campos) {
    for (const campo of campos) {
      if ($(campo).val() === "") {
        return false;
      }
    }
    return true;
  },
};
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
  let nuevoValor = $("#modalValor").val();
  let modalLogitud = $("#modalLogitud").val();
  let modalLatitud = $("#modalLatitud").val();

  if (!id || isNaN(nuevoValor)) {
    mostrarAlertaModal(
      "danger",
      "❌ ID no válido o valor incorrecto.",
      "alerta-modal"
    );
    return;
  }

  if (!id || modalLogitud == "" || modalLatitud == "") {
    mostrarAlertaModal(
      "danger",
      "❌  Latitud y/o Longitud son campos obligatorios. ",
      "alerta-modal"
    );
    return;
  }
  const datos = {
    op: "editarInformacion",
    id: id,
    valor: nuevoValor,
    longitud: modalLogitud,
    latitud: modalLatitud,
  };
  $.ajax({
    url: "admin/ajax/rqst.php",
    type: "POST",
    data: datos,
    dataType: "json",
    success: function (response) {
      if (response.output.valid) {
        mostrarAlertaModal(
          "success",
          "✅ Información actualizada correctamente. Espere porfavor...",
          "alerta-modal"
        );
        // Esperar un segundo y recargar la página para reflejar los cambios
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        mostrarAlertaModal(
          "danger",
          "❌ Error al actualizar la información.",
          "alerta-modal"
        );
      }
    },
    error: function (xhr, status, error) {
      mostrarAlertaModal(
        "danger",
        "❌ Error editando la información.",
        "alerta-modal"
      );
    },
  });
}

function mostrarImagenes(fotos) {
  const container = document.getElementById("imageContainer");
  container.innerHTML = "";

  fotos.forEach((url) => {
    const col = document.createElement("div");
    col.className = "col-6";

    const link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.rel = "noopener noreferrer"; // Por seguridad

    const img = document.createElement("img");
    img.src = url;
    img.alt = "Imagen";
    img.className = "img-fluid rounded border";
    img.style.maxHeight = "200px";
    img.style.objectFit = "cover";

    link.appendChild(img);
    col.appendChild(link);
    container.appendChild(col);
  });

  const modal = new bootstrap.Modal(document.getElementById("imageModal"));
  modal.show();
}

function verObservaciones(observacion) {
  // Insertar texto en el modal
  const body = document.getElementById("modalObservacionesBody");
  body.textContent = observacion || "Sin observaciones";

  // Mostrar el modal usando Bootstrap 5
  const modal = new bootstrap.Modal(
    document.getElementById("modalObservaciones")
  );
  modal.show();
}

$(document).on("click", ".editar-informacion", function () {
  const button = $(this);

  const id = button.data("id");
  const latitud = button.data("latitud");
  const longitud = button.data("longitud");
  const fecha = button.data("fecha");
  const departamento = button.data("departamento");
  const municipio = button.data("municipio");
  const vereda = button.data("vereda");
  const factor = button.data("factor");
  const valor = button.data("valor");
  const observaciones = button.data("observaciones");
const icono = button.data("icono");
$("#modalIconoFactor").val(icono);

  const modal = $("#modalEditarInformacion");

  modal.find('input[name="id"]').val(id);
  modal.find('input[name="modalLatitud"]').val(latitud);
  modal.find('input[name="modalLogitud"]').val(longitud);
  modal.find('input[name="modalFecha"]').val(fecha);
  modal.find('input[name="modalDepartamento"]').val(departamento);
  modal.find('input[name="modalMunicipio"]').val(municipio);
  modal.find('input[name="modalVereda"]').val(vereda);
  modal.find('input[name="modalFactor"]').val(factor);
  modal.find('input[name="modalValor"]').val(valor);
  modal.find('input[name="observaciones"]').val(observaciones);

  modal.modal("show");
});

$(document).ready(function () {
  if ($("#modalEdicion").hasClass("show")) {
    $("#modalEdicion").modal("hide");
    $(".modal-backdrop").remove();
    $("body").removeClass("modal-open");
  }
});
