$(function () {
  cargaData();
  function cargaData() {
    tablaAccionG = $("#dynamictable").DataTable({
      order: [[0, "desc"]],
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
        url: "./admin/controllers/accionGCtrl.php",
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
            return `<button class="btn btn-sm btn-transparent" 
                  onclick="ingresarAccionG();"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-file-plus"></i>
            </button>`;
          },
        },
        {
          data: "id",
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-transparent" 
                  onclick="editAccionG('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
          },
        },
        {
          data: "accion",
        },
      ],
    });
    $("#customSearch")
      .off()
      .on("keyup", function () {
        if (tablaAccionG) {
          tablaAccionG.search(this.value).draw();
        }
      });
  }
});

function ingresarAccionG() {
  $("#btnGuardarAccionG").text("Guardar");
  $("#formAccionG")[0].reset();
  $("#modalAccionG").modal("show");

  $("#formAccionG").data("modo", "crear");
}

function editAccionG(id) {
  $.ajax({
    url: "./admin/controllers/accionGCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "getAccionG",
      data: { id: id },
    }),
    dataType: "json",
    success: function (response) {
      if (response.state && response.data.length > 0) {
        const acciong = response.data[0];

        $("#accion").val(acciong.accion);

        $("#id").val(acciong.id);

        $("#btnGuardarAccionG").text("Actualizar");
        $("#formAccionG").data("modo", "editar");

        $("#modalAccionG").modal("show");
      }
    },
  });
}

$("#btnGuardarAccionG").on("click", function () {
  const modo = $("#formAccionG").data("modo");
  const method = modo === "editar" ? "updateAccionG" : "createAccionG";

  const id = $("#id").val();
  const accion = $("#accion").val().trim();

  const errores = [];

  // Validaciones
  if (!accion) errores.push("El campo Tipo de acción es obligatorio.");

  if (errores.length > 0) {
    Swal.fire({
      icon: "error",
      title: "Errores en el formulario",
      html: errores.map((e) => `<div>${e}</div>`).join(""),
      confirmButtonText: "Entendido",
    });
    return;
  }

  const data = {
    id: id,
    accion: accion,
  };

  $.ajax({
    url: "./admin/controllers/accionGCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: method,
      data: data,
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        $("#modalAccionG").modal("hide");
        Swal.fire(
          "Éxito",
          modo === "editar"
            ? "Actualizado correctamente"
            : "Guardado correctamente",
          "success"
        );
        tablaAccionG.ajax.reload(null, false);
      } else {
        Swal.fire("Error", response.message || "No se pudo guardar", "error");
      }
    },
    error: function () {
      Swal.fire("Error", "Hubo un problema con la solicitud.", "error");
    },
  });
});
