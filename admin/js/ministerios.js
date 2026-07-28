$(function () {
  cargaData();
  function cargaData() {
    tablaMinisterios = $("#dynamictable").DataTable({
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
        url: "./admin/controllers/ministeriosCtrl.php",
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
                  onclick="ingresarMinisterio();"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-file-plus"></i>
            </button>`;
          },
        },
        {
          data: "id",
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-transparent" 
                  onclick="editMinisterio('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
          },
        },
        {
          data: "ministerio",
        },
        {
          data: "ministro",
          render: function (data, type, row) {
            if (!data || data.trim() === "") {
              return "<i class='text-muted'>Sin asignar</i>";
            }
            return data;
          },
        },
        {
          data: "correo",
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
        if (tablaMinisterios) {
          tablaMinisterios.search(this.value).draw();
        }
      });
  }
});

function ingresarMinisterio() {
  $("#btnGuardarMinisterio").text("Guardar");
  $("#formMinisterio")[0].reset();
  $("#modalMinisterio").modal("show");

  $("#formMinisterio").data("modo", "crear");
}

function editMinisterio(id) {
  $.ajax({
    url: "./admin/controllers/ministeriosCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "getMinisterio",
      data: { id: id },
    }),
    dataType: "json",
    success: function (response) {
      if (response.state && response.data.length > 0) {
        const ministerio = response.data[0];

        $("#ministerio").val(ministerio.ministerio);
        $("#ministro").val(ministerio.ministro);
        $("#correo").val(ministerio.correo);
        $("#id").val(ministerio.id);

        $("#btnGuardarMinisterio").text("Actualizar");
        $("#formMinisterio").data("modo", "editar");

        $("#modalMinisterio").modal("show");
      }
    },
  });
}

$("#btnGuardarMinisterio").on("click", function () {
  const modo = $("#formMinisterio").data("modo");
  const method = modo === "editar" ? "updateMinisterio" : "createMinisterio";

  const id = $("#id").val();
  const ministerio = $("#ministerio").val().trim();
  const ministro = $("#ministro").val().trim();
  const correo = $("#correo").val().trim();

  const errores = [];

  // Validaciones
  if (!ministerio) errores.push("El campo Ministerio es obligatorio.");
  if (!ministro) errores.push("El campo Ministro es obligatorio.");
  if (!correo) {
    errores.push("El campo Correo es obligatorio.");
  } else {
    const correoValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!correoValido.test(correo)) {
      errores.push("El correo electrónico no tiene un formato válido.");
    }
  }

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
    ministerio: ministerio,
    ministro: ministro,
    correo: correo,
  };

  $.ajax({
    url: "./admin/controllers/ministeriosCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: method,
      data: data,
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        $("#modalMinisterio").modal("hide");
        Swal.fire(
          "Éxito",
          modo === "editar"
            ? "Actualizado correctamente"
            : "Guardado correctamente",
          "success"
        );
        tablaMinisterios.ajax.reload(null, false);
      } else {
        Swal.fire("Error", response.message || "No se pudo guardar", "error");
      }
    },
    error: function () {
      Swal.fire("Error", "Hubo un problema con la solicitud.", "error");
    },
  });
});
