$(function () {
  cargaData();
  function cargaData() {
    // Obtener municipio del usuario para filtrar (si es Alcalde)
    const municipioUsuario = $("#municipioUsuario").val();

    componenteMunicipal = $("#tableComponentes").DataTable({
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
        url: "./admin/controllers/componenteMunicipiosCtrl.php",
        type: "POST",
        contentType: "application/json",
        data: function (d) {
          // Agregar filtro de municipio si es Alcalde
          d.municipio_filtro = municipioUsuario;
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
            return `<button class="btn btn-sm btn-transparent editar-informacion"
                  onclick="editComponente('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
          },
        },
        {
          data: "nombre_componente",
        },
        {
          data: "municipio",
          render: function (data, type, row) {
            return data || row.nombre_mapa || 'N/A';
          },
        },
        {
          data: "habilitado",
        },
      ],
    });
    $("#customSearch")
      .off()
      .on("keyup", function () {
        if (componenteMunicipal) {
          componenteMunicipal.search(this.value).draw();
        }
      });
  }
});

function ingresarComponente() {
  $("#formNewComponente")[0].reset();
  $("#editId").val("");
  $(".modal-title").text("Ingresar Nuevo Componente Municipal");
  $("#btnSaveComponente")
    .text("Guardar")
    .attr("onclick", "saveNewComponente();");

  // Obtener datos del usuario
  const isAdmin = $("#isAdmin").val() === "1";
  const municipioUsuario = $("#municipioUsuario").val();

  // Si es Alcalde, pre-seleccionar su municipio y deshabilitar los campos
  if (!isAdmin && municipioUsuario) {
    // Obtener el departamento (68 - Santander)
    const codigoDepartamento = $("#tbl_departamento_id").val();
    $("#tbl_departamento_id").val(codigoDepartamento).prop("disabled", true);

    // Cargar municipios y luego seleccionar el del alcalde
    var q = {};
    q.op = "ciudadget";
    q.codigo_departamento = codigoDepartamento;

    $.ajax({
      url: "./admin/ajax/rqst.php",
      type: "POST",
      data: q,
      dataType: "json",
      success: function(data) {
        if (data.output.valid) {
          let info = '<option value="seleccione">Seleccione</option>';
          data.output.response.forEach(function(municipio) {
            const selected = municipio.codigo_muncipio === municipioUsuario ? 'selected' : '';
            info += `<option value="${municipio.codigo_muncipio}" ${selected}>${municipio.codigo_muncipio} - ${municipio.municipio}</option>`;
          });
          $("#tbl_municipio_id").html(info);
          $("#tbl_municipio_id").val(municipioUsuario).prop("disabled", true);

          // Abrir modal después de cargar los municipios
          $("#newModalComponente").modal("show");
        }
      }
    });
  } else {
    // Si es Admin, permitir seleccionar cualquier municipio
    $("#tbl_departamento_id").prop("disabled", false);
    $("#tbl_municipio_id").prop("disabled", false);

    $("#newModalComponente").modal("show");

    // Cargar municipios del departamento seleccionado por defecto DESPUÉS de mostrar el modal
    $("#newModalComponente").on('shown.bs.modal', function() {
      if (typeof DEPARTAMENTO !== 'undefined' && typeof DEPARTAMENTO.getMunicipios === 'function') {
        DEPARTAMENTO.getMunicipios();
      }
    });
  }
}

function saveNewComponente() {
  // Habilitar temporalmente los campos para poder leer sus valores
  const deptDisabled = $("#tbl_departamento_id").prop("disabled");
  const munDisabled = $("#tbl_municipio_id").prop("disabled");

  $("#tbl_departamento_id").prop("disabled", false);
  $("#tbl_municipio_id").prop("disabled", false);

  const nombre_componente = $("#newComponente").val().trim();
  const codigo_departamento = $("#tbl_departamento_id").val();
  const codigo_municipio = $("#tbl_municipio_id").val();
  const habilitado = $("#newHabilitado").val();

  if (!codigo_departamento || codigo_departamento === "Seleccione" || codigo_departamento === "seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona el departamento.",
    });
    $("#tbl_departamento_id").focus();
    return;
  }

  if (!codigo_municipio || codigo_municipio === "" || codigo_municipio === "seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona el municipio.",
    });
    $("#tbl_municipio_id").focus();
    return;
  }

  if (!nombre_componente) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre del componente.",
    });
    $("#newComponente").focus();
    return;
  }

  if (habilitado === "Seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona si está habilitado.",
    });
    $("#newHabilitado").focus();
    return;
  }

  const data = {
    nombre_componente,
    codigo_departamento,
    codigo_municipio,
    habilitado: habilitado,
  };

  $.ajax({
    url: "./admin/controllers/componenteMunicipiosCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "newComponente",
      data: data,
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "¡Registrado!",
          text:
            response.message || "El componente se ha guardado correctamente.",
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Aceptar",
        });

        $("#formNewComponente")[0].reset();
        $("#newModalComponente").modal("hide");

        // Restaurar estado de los campos antes de cerrar
        $("#tbl_departamento_id").prop("disabled", false);
        $("#tbl_municipio_id").prop("disabled", false);

        if (typeof componenteMunicipal !== "undefined") {
          componenteMunicipal.ajax.reload();
        }
      } else {
        // Restaurar estado de los campos en caso de error
        $("#tbl_departamento_id").prop("disabled", deptDisabled);
        $("#tbl_municipio_id").prop("disabled", munDisabled);

        Swal.fire({
          icon: "error",
          title: "Error al guardar",
          text:
            response.message || "Ocurrió un problema al guardar el componente.",
          confirmButtonColor: "#d33",
          confirmButtonText: "Cerrar",
        });
      }
    },
    error: function () {
      // Restaurar estado de los campos en caso de error
      $("#tbl_departamento_id").prop("disabled", deptDisabled);
      $("#tbl_municipio_id").prop("disabled", munDisabled);

      Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: "No se pudo procesar la solicitud.",
      });
    },
  });
}

function editComponente(id) {
  $.ajax({
    url: "./admin/controllers/componenteMunicipiosCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "editComponente",
      data: id,
    }),
    dataType: "json",
    success: function (response) {
      console.log(response.data[0]);
      if (response.state) {
        const componente = response.data[0];
        $(".modal-title").text("Editar Componente Municipal");
        $("#editId").val(componente.id);

        // Cargar departamento primero
        $("#tbl_departamento_id").val(componente.codigo_departamento);

        // Llenar los demás campos primero
        $("#newComponente").val(componente.nombre_componente);
        $("#newHabilitado").val(componente.habilitado);

        $("#btnSaveComponente")
          .text("Actualizar")
          .attr("onclick", `updateComponente(${componente.id});`);

        // Cargar municipios usando AJAX y luego seleccionar el municipio
        var q = {};
        q.op = "ciudadget";
        q.codigo_departamento = componente.codigo_departamento;

        $.ajax({
          url: "./admin/ajax/rqst.php",
          type: "POST",
          data: q,
          dataType: "json",
          success: function(data) {
            if (data.output.valid) {
              let info = '<option value="seleccione">Seleccione</option>';
              data.output.response.forEach(function(municipio) {
                const selected = municipio.codigo_muncipio === componente.codigo_municipio ? 'selected' : '';
                info += `<option value="${municipio.codigo_muncipio}" ${selected}>${municipio.codigo_muncipio} - ${municipio.municipio}</option>`;
              });
              $("#tbl_municipio_id").html(info);

              // Verificar si es Alcalde - si lo es, deshabilitar campos de departamento y municipio
              const isAdmin = $("#isAdmin").val() === "1";
              const municipioUsuario = $("#municipioUsuario").val();

              if (!isAdmin && municipioUsuario) {
                $("#tbl_departamento_id").prop("disabled", true);
                $("#tbl_municipio_id").prop("disabled", true);
              } else {
                $("#tbl_departamento_id").prop("disabled", false);
                $("#tbl_municipio_id").prop("disabled", false);
              }

              // Abrir el modal DESPUÉS de cargar los municipios
              $("#newModalComponente").modal("show");
            } else {
              $("#newModalComponente").modal("show");
            }
          },
          error: function() {
            $("#newModalComponente").modal("show");
          }
        });

      } else {
        Swal.fire({
          icon: "error",
          title: "Error al editar el componente",
          text:
            response.message || "Ocurrió un problema al editar el componente.",
          confirmButtonColor: "#d33",
          confirmButtonText: "Cerrar",
        });
      }
    },
  });
}

function updateComponente(id) {
  // Habilitar temporalmente los campos para poder leer sus valores
  const deptDisabled = $("#tbl_departamento_id").prop("disabled");
  const munDisabled = $("#tbl_municipio_id").prop("disabled");

  $("#tbl_departamento_id").prop("disabled", false);
  $("#tbl_municipio_id").prop("disabled", false);

  const nombre_componente = $("#newComponente").val().trim();
  const codigo_departamento = $("#tbl_departamento_id").val();
  const codigo_municipio = $("#tbl_municipio_id").val();
  const habilitado = $("#newHabilitado").val();

  if (!codigo_departamento || codigo_departamento === "Seleccione" || codigo_departamento === "seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona el departamento.",
    });
    $("#tbl_departamento_id").focus();
    return;
  }

  if (!codigo_municipio || codigo_municipio === "" || codigo_municipio === "seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona el municipio.",
    });
    $("#tbl_municipio_id").focus();
    return;
  }

  if (!nombre_componente) {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre del componente.",
    });
    $("#newComponente").focus();
    return;
  }

  if (habilitado === "Seleccione") {
    $("#tbl_departamento_id").prop("disabled", deptDisabled);
    $("#tbl_municipio_id").prop("disabled", munDisabled);
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona si está habilitado.",
    });
    $("#newHabilitado").focus();
    return;
  }

  $.ajax({
    url: "./admin/controllers/componenteMunicipiosCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "updateComponente",
      data: {
        id,
        nombre_componente,
        codigo_departamento,
        codigo_municipio,
        habilitado: habilitado,
      },
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "Actualizado",
          text: "El componente fue actualizado correctamente.",
          timer: 2000,
          showConfirmButton: false,
        });

        $("#formNewComponente")[0].reset();
        $("#newModalComponente").modal("hide");

        // Restaurar estado de los campos antes de cerrar
        $("#tbl_departamento_id").prop("disabled", false);
        $("#tbl_municipio_id").prop("disabled", false);

        if (typeof componenteMunicipal !== "undefined") {
          componenteMunicipal.ajax.reload();
        }

        $("#btnSaveComponente")
          .text("Guardar")
          .attr("onclick", "saveNewComponente();");
      } else {
        // Restaurar estado de los campos en caso de error
        $("#tbl_departamento_id").prop("disabled", deptDisabled);
        $("#tbl_municipio_id").prop("disabled", munDisabled);

        Swal.fire({
          icon: "error",
          title: "Error",
          text: response.message || "No se pudo actualizar el componente.",
        });
      }
    },
    error: function () {
      // Restaurar estado de los campos en caso de error
      $("#tbl_departamento_id").prop("disabled", deptDisabled);
      $("#tbl_municipio_id").prop("disabled", munDisabled);

      Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: "No se pudo procesar la solicitud.",
      });
    },
  });
}
