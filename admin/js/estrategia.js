$(function () {
  cargaData();
  function cargaData() {
    secretaria = $("#dynamictable").DataTable({
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
        url: "./admin/controllers/estrategiaCtrl.php",
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
                  onclick="ingresarEstrategia();"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-file-plus"></i>
            </button>`;
          },
        },
        {
          data: "id",
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-transparent" 
                  onclick="editEstrategia('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
          },
        },
        {
          data: "nombre",
        },
        {
          data: "descripcion",
        },
      ],
    });
    $("#customSearch")
      .off()
      .on("keyup", function () {
        if (secretaria) {
          secretaria.search(this.value).draw();
        }
      });
  }
});

function guardarEstrategia() {
  const estrategiaId = $("#estrategia_id").val().trim();
  const q = {
    id: estrategiaId,
    nombre: $("#nombre").val().trim(),
    descripcion: $("#descripcion").val().trim(),
  };

  if (!q.nombre || !q.descripcion) {
    Swal.fire("Error", "Por favor completa todos los campos", "warning");
    return;
  }
  console.log(estrategiaId, " aca");
  const metodo = estrategiaId ? "updateEstrategia" : "newEstrategia";

  $.ajax({
    url: "./admin/controllers/estrategiaCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: metodo, data: q }),
    contentType: "application/json",
    dataType: "json",
    success: function (response) {
      if (response.state) {
        $("#modalEstrategia").modal("hide");
        Swal.fire({
          icon: "success",
          title: estrategiaId
            ? "¡Estrategia actualizada!"
            : "¡Estrategia creada!",
          text: response.message,
        }).then(() => {
          $("#dynamictable").DataTable().ajax.reload();
          $("#formEstrategia")[0].reset();
        });
      } else {
        Swal.fire("Error", response.message, "error");
      }
    },
  });
}

function ingresarEstrategia() {
  $("#formEstrategia")[0].reset();
  $("#estrategia_id").val("");
  $("#modalEstrategiaLabel").text("Nueva Estrategia");
  $("#btnGuardarEstrategia").text("Guardar");
  $("#modalEstrategia").modal("show");
}

function editEstrategia(id) {
  $("#estrategia_id").val(id);
  $.ajax({
    url: "./admin/controllers/estrategiaCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "editEstrategia", data: { id } }),
    contentType: "application/json",
    dataType: "json",
    success: function (response) {
      if (response.state) {
        $("#nombre").val(response.data.nombre);
        $("#descripcion").val(response.data.descripcion);
        $("#modalEstrategiaLabel").text("Editar Estrategia");
        $("#btnGuardarEstrategia").text("Actualizar");
        $("#modalEstrategia").modal("show");
      } else {
        Swal.fire("Error", response.message, "error");
      }
    },
  });
}
