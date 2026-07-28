var tablaEmpresas;

$(function () {
  cargaData();

  $("#filtroMunicipio").on("change", function () {
    cargaData();
  });
});

function cargaData() {
  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  tablaEmpresas = $("#dynamictable").DataTable({
    order: [[2, "asc"]],
    dom: "lrtip",
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: "./admin/controllers/empresasCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        d.municipio_filtro = $("#filtroMunicipio").val() || 0;
        return JSON.stringify({
          method: "load",
          data: d,
        });
      },
    },
    columns: [
      {
        data: "id",
        orderable: false,
        render: function (data) {
          return `<button class="btn btn-sm btn-transparent" onclick="editEmpresa('${data}');" title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>
            <button class="btn btn-sm btn-transparent text-danger" onclick="eliminarEmpresa('${data}');" title="Eliminar">
              <i class="feather icon-trash-2"></i>
            </button>`;
        },
      },
      { data: "municipio", defaultContent: "" },
      { data: "nombre_empresa", defaultContent: "" },
      { data: "nit", defaultContent: "—" },
      { data: "nombre_contacto", defaultContent: "" },
      { data: "telefono_contacto", defaultContent: "" },
      {
        data: "email_contacto",
        defaultContent: "—",
        render: function (data) {
          return data || "—";
        },
      },
      {
        data: "dt_create",
        defaultContent: "",
        render: function (data) {
          if (!data) return "";
          return data.substring(0, 16).replace("T", " ");
        },
      },
    ],
  });

  $("#customSearch")
    .off()
    .on("keyup", function () {
      if (tablaEmpresas) {
        tablaEmpresas.search(this.value).draw();
      }
    });

  $("#dynamictable_length select").addClass(
    "form-control form-control-sm w-auto d-inline-block mx-2"
  );
}

function sincronizarMunicipioFormulario() {
  var municipio = $("#tbl_municipio_id").val();
  if (municipio) {
    $("#codigo_muncipio").val(municipio);
  }
}

function save() {
  sincronizarMunicipioFormulario();

  const msj = "Falta ingresar información obligatoria, marcada con asterisco.";
  const camposRequeridos = [
    "#tbl_municipio_id",
    "#nombre_empresa",
    "#nombre_contacto",
    "#telefono_contacto",
  ];

  if (!UTIL.validarCampos(camposRequeridos)) {
    UTIL.mostrarMensajeValidacion(msj);
    return;
  }

  const datos = {
    op: "empresassave",
    id: $("#idEmpresa").val(),
    codigo_muncipio: $("#codigo_muncipio").val(),
    nombre_empresa: $("#nombre_empresa").val().trim(),
    nit: $("#nit").val().trim(),
    nombre_contacto: $("#nombre_contacto").val().trim(),
    telefono_contacto: $("#telefono_contacto").val().trim(),
    email_contacto: $("#email_contacto").val().trim(),
  };

  UTIL.callAjaxRqstPOST(datos, saveHandler);
}

function editEmpresa(id) {
  UTIL.cursorBusy();

  $.ajax({
    url: "admin/controllers/empresasCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "editEmpresa", data: id }),
    contentType: "application/json",
    dataType: "json",
    success: function (data) {
      UTIL.cursorNormal();

      if (data.state) {
        const res = data.data[0];
        $("#idEmpresaEdit").val(res.id);
        $("#nombre_empresaEdit").val(res.nombre_empresa || "");
        $("#nitEdit").val(res.nit || "");
        $("#nombre_contactoEdit").val(res.nombre_contacto || "");
        $("#telefono_contactoEdit").val(res.telefono_contacto || "");
        $("#email_contactoEdit").val(res.email_contacto || "");
        $("#tbl_municipio_idEdit").val(res.codigo_muncipio);
        $("#modalEmpresa").modal("show");
      } else {
        UTIL.mostrarMensajeError(data.message || "No se pudo cargar la empresa.");
      }
    },
    error: function () {
      UTIL.cursorNormal();
      UTIL.mostrarMensajeError("Error al obtener la empresa.");
    },
  });
}

function editSave() {
  const msj = "Falta ingresar información obligatoria, marcada con asterisco.";
  const camposRequeridos = [
    "#tbl_municipio_idEdit",
    "#nombre_empresaEdit",
    "#nombre_contactoEdit",
    "#telefono_contactoEdit",
  ];

  if (!UTIL.validarCampos(camposRequeridos)) {
    UTIL.mostrarMensajeValidacion(msj);
    return;
  }

  const datos = {
    op: "empresassave",
    id: $("#idEmpresaEdit").val(),
    codigo_muncipio: $("#tbl_municipio_idEdit").val(),
    nombre_empresa: $("#nombre_empresaEdit").val().trim(),
    nit: $("#nitEdit").val().trim(),
    nombre_contacto: $("#nombre_contactoEdit").val().trim(),
    telefono_contacto: $("#telefono_contactoEdit").val().trim(),
    email_contacto: $("#email_contactoEdit").val().trim(),
  };

  UTIL.callAjaxRqstPOST(datos, function (data) {
    saveHandler(data);
    if (data.output && data.output.valid) {
      $("#modalEmpresa").modal("hide");
    }
  });
}

function eliminarEmpresa(id) {
  Swal.fire({
    title: "¿Eliminar empresa?",
    text: "Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonText: "Cancelar",
    confirmButtonText: "Eliminar",
  }).then(function (result) {
    if (!result.isConfirmed) {
      return;
    }

    UTIL.callAjaxRqstPOST({ op: "empresasdelete", id: id }, function (data) {
      if (data.output && data.output.valid) {
        UTIL.mostrarMensajeExitoso("Empresa eliminada correctamente");
        cargaData();
      } else {
        UTIL.mostrarMensajeError(
          (data.output && data.output.response && data.output.response.content) ||
            "No fue posible eliminar la empresa."
        );
      }
    });
  });
}

function saveHandler(data) {
  UTIL.cursorNormal();

  if (data.output && data.output.valid) {
    UTIL.mostrarMensajeExitoso("Información guardada correctamente");
    UTIL.clearForm("formEmpresa");
    UTIL.clearForm("formEmpresaEdit");
    $("#idEmpresa").val("");
    cargaData();
  } else {
    UTIL.mostrarMensajeError(
      (data.output && data.output.response && data.output.response.content) ||
        data.output.response ||
        "Error al guardar la información"
    );
  }
}

function copiarMunicipiosAlModal() {
  var $origen = $("#tbl_municipio_id option").clone();
  $("#tbl_municipio_idEdit").empty().append($origen);
}
