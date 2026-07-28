$(function () {
  cargaData();
  function cargaData() {
    tablaLinea = $("#dynamictable").DataTable({
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
        url: "./admin/controllers/lineaCtrl.php",
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
                  onclick="ingresarLinea();"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-file-plus"></i>
            </button>`;
          },
        },
        {
          data: "id",
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-transparent" 
                  onclick="editLinea('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
          },
        },
        {
          data: "nombre",
          render: function (data, type, row) {
            if (!data || data.trim() === "") {
              return "<i class='text-muted'>Sin asignar</i>";
            }
            return data;
          },
        },
        {
          data: "descripcion",
          render: function (data, type, row) {
            if (!data || data.trim() === "") {
              return "<i class='text-muted'>No registrado</i>";
            }
            return data;
          },
        },
      ],
    });
    $("#customSearch")
      .off()
      .on("keyup", function () {
        if (tablaLinea) {
          tablaLinea.search(this.value).draw();
        }
      });
  }
});

function ingresarLinea() {
  $("#btnGuardarLinea").text("Guardar");
  $("#formLinea")[0].reset();
  $("#modalLinea").modal("show");

  $("#formLinea").data("modo", "crear");
}

function editLinea(id) {
  $.ajax({
    url: "./admin/controllers/lineaCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "getLinea",
      data: { id: id },
    }),
    dataType: "json",
    success: function (response) {
      if (response.state && response.data.length > 0) {
        const ministerio = response.data[0];

        $("#nombre").val(ministerio.nombre);
        $("#descripcion").val(ministerio.descripcion);
        $("#id").val(ministerio.id);

        $("#btnGuardarLinea").text("Actualizar");
        $("#formLinea").data("modo", "editar");

        $("#modalLinea").modal("show");
      }
    },
  });
}

$("#btnGuardarLinea").on("click", function () {
  const modo = $("#formLinea").data("modo");
  const method = modo === "editar" ? "updateLinea" : "createLinea";

  const id = $("#id").val();
  const nombre = $("#nombre").val().trim();
  const descripcion = $("#descripcion").val().trim();

  const errores = [];

  if (!nombre) errores.push("El campo nombre es obligatorio.");
  if (!descripcion) errores.push("El campo descripción es obligatorio.");

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
    nombre: nombre,
    descripcion: descripcion,
  };

  $.ajax({
    url: "./admin/controllers/lineaCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: method,
      data: data,
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        $("#modalLinea").modal("hide");
        Swal.fire(
          "Éxito",
          modo === "editar"
            ? "Actualizado correctamente"
            : "Guardado correctamente",
          "success"
        );
        tablaLinea.ajax.reload(null, false);
      } else {
        Swal.fire("Error", response.message || "No se pudo guardar", "error");
      }
    },
    error: function () {
      Swal.fire("Error", "Hubo un problema con la solicitud.", "error");
    },
  });
});
