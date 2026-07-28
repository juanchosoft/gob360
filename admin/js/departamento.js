$(document).on("ready", init);
var q;
var informacionMunicipio = {};

function init() {
  q = {};
}

var DEPARTAMENTO = {
  onProvinciaChange: function() {
    var provincia = $('#provincia').val();
    var departamentoId = $('#tbl_departamento_id').val();
    
    if (provincia && departamentoId) {
        this.getMunicipiosByProvincia(provincia, departamentoId);
    } else {
        // Si no hay provincia seleccionada, cargar todos los municipios del departamento
        this.getMunicipios();
    }
  },
  
  getMunicipiosByProvincia: function(provincia, departamentoId) {
    q = {};
    q.op = "ciudadget";
    q.codigo_departamento = departamentoId;
    q.provincia = provincia; // Nuevo parámetro para filtrar por provincia
    
    UTIL.cursorBusy();
    $.ajax({
        data: q,
        type: "GET",
        dataType: "json",
        url: "admin/ajax/rqst.php",
        success: function(data) {
            UTIL.cursorNormal();
            DEPARTAMENTO.getMunicipiosByProvinciaHandler(data);
        },
        error: function() {
            UTIL.cursorNormal();
            UTIL.mostrarMensajeError("Error al cargar municipios por provincia");
        }
    });
  },
  
  getMunicipiosByProvinciaHandler: function(data) {
    const depto = $("#tbl_departamento_id").val();
    const municipioDelUsuario = $("#municipioUsuario").val();
    const tipoUsuario = $("#tipoUsuario").val();

    if (data.output.valid) {
        const res = data.output.response;
        let info = `<option value="">Seleccione</option>`;
        const generarOpcion = (municipio, rutaMapa, seleccionado) => {
            return `<option value="${municipio.codigo_muncipio}" data-mapa='${rutaMapa}' ${seleccionado}>${municipio.municipio}</option>`;
        };

        res.forEach((municipio) => {
            let rutaMapa = municipio.carpeta_mapa
                ? municipio.carpeta_mapa.replace(
                      "mapa-veredas/",
                      `mapa-veredas/${depto}/`
                  )
                : null;

            if (
                (tipoUsuario === "Alcalde" || tipoUsuario === "Auxiliar_Alcalde") &&
                municipioDelUsuario &&
                municipioDelUsuario !== municipio.codigo_muncipio
            ) {
                return;
            }

            const seleccionado =
                typeof munSelect !== "undefined" &&
                munSelect === municipio.codigo_muncipio
                    ? "selected"
                    : "";
            info += generarOpcion(municipio, rutaMapa, seleccionado);
        });

        $("#tbl_municipio_id").empty().append(info);

        if ($("#filtro").val() === "vereda") {
            if (
                $("#tbl_municipio_id").val() != "" &&
                $("#tbl_municipio_id").val() != "selecccione" &&
                $("#tbl_municipio_id").val() > 0
            ) {
                DEPARTAMENTO.getVeredasByMunicipioId();
            }
        }
    } else {
        UTIL.mostrarMensajeError(data.output.response.content);
    }
  },

  // ========== FUNCIONES EXISTENTES ==========
  getMunicipiosConParametros: function () {
    if (depSelect != "") {
      $("#tbl_departamento_id").val(depSelect);
    }
    $("#tbl_departamento_id").val(depSelect).trigger("change");

    if (depSelect != "seleccione") {
      q = {};
      q.op = "ciudadget";
      q.codigo_departamento = depSelect;
      UTIL.callAjaxRqstPOST(q, this.getMunicipiosHandler);
    } else {
      $("#tbl_municipio_id").empty().append("");
    }
  },
  
  getMunicipiosConDepartamentoPrincipal: function () {
    q = {};
    q.op = "ciudadget";
    q.codigo_departamento = UTIL.getDepartamentoPrincipal();
    UTIL.callAjaxRqstPOST(q, this.getMunicipiosHandler);
  },
  
  getMunicipios: function () {
    if ($("#tbl_departamento_id").val() != "seleccione") {
      q = {};
      q.op = "ciudadget";
      q.codigo_departamento = $("#tbl_departamento_id").val();
      UTIL.callAjaxRqstPOST(q, this.getMunicipiosHandler);
    } else {
      $("#tbl_municipio_id").empty().append("");
    }
  },
  
  getMunicipiosHandler: function (data) {
    const depto = $("#tbl_departamento_id").val();
    const municipioDelUsuario = $("#municipioUsuario").val(); // Municipio del usuario
    const tipoUsuario = $("#tipoUsuario").val(); // Tipo de usuario

    UTIL.cursorNormal();

    if (data.output.valid) {
      const res = data.output.response;
      let info = `<option value="">Seleccione</option>`;
      const generarOpcion = (municipio, rutaMapa, seleccionado) => {
        return `<option value="${municipio.codigo_muncipio}" data-mapa='${rutaMapa}' ${seleccionado}>${municipio.municipio}</option>`;
      };

      res.forEach((municipio) => {
        let rutaMapa = municipio.carpeta_mapa
          ? municipio.carpeta_mapa.replace(
              "mapa-veredas/",
              `mapa-veredas/${depto}/`
            )
          : null;

        if (
          (tipoUsuario === "Alcalde" || tipoUsuario === "Auxiliar_Alcalde") &&
          municipioDelUsuario &&
          municipioDelUsuario !== municipio.codigo_muncipio
        ) {
          return;
        }

        const seleccionado =
          typeof munSelect !== "undefined" &&
          munSelect === municipio.codigo_muncipio
            ? "selected"
            : "";
        info += generarOpcion(municipio, rutaMapa, seleccionado);
      });

      $("#tbl_municipio_id").empty().append(info);

      if ($("#filtro").val() === "vereda") {
        if (
          $("#tbl_municipio_id").val() != "" &&
          $("#tbl_municipio_id").val() != "selecccione" &&
          $("#tbl_municipio_id").val() > 0
        ) {
          DEPARTAMENTO.getVeredasByMunicipioId();
        }
      }
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },
  
  getMunicipiosConDepartamentoPrincipalV2: function () {
    let q = {
      op: "ciudadget",
      codigo_departamento: UTIL.getDepartamentoPrincipal(),
    };

    UTIL.cursorBusy();

    $.ajax({
      data: q,
      type: "GET",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();

        if (!data.output.valid) {
          UTIL.mostrarMensajeError(data.output.response.content);
          return;
        }

        const res = data.output.response;
        const municipioDelUsuario = $("#municipioUsuario").val();
        const tipoUsuario = $("#tipoUsuario").val();
        let info = "";

        // Verifica si el usuario es Alcalde o Auxiliar_Alcalde
        const esAlcalde =
          tipoUsuario === "Alcalde" || tipoUsuario === "Auxiliar_Alcalde";

        res.forEach((municipio) => {
          if (esAlcalde && municipio.codigo_muncipio !== municipioDelUsuario) {
            return; // Solo muestra su municipio
          }
          info += `<option value="${municipio.codigo_muncipio}">${municipio.municipio}</option>`;
        });

        const $select = $("#tbl_municipio_id");
        $select.empty().append(info);
      },
      error: function () {
        UTIL.cursorNormal();
        UTIL.mostrarMensajeError("Error al obtener municipios.");
      },
    });
  },

  getVeredasByMunicipioIdInformacionMapa: function (
    municipioId,
    veredaIdASetar = 0
  ) {
    q = {};
    q.op = "veredaget";
    q.municipio_id = municipioId;

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
          var info = "";
          var res = data.output.response;
          for (var j in res) {
            info +=
              "<option value='" +
              res[j].id +
              "' selected>" +
              res[j].nombre_vereda +
              "</option>";
          }
          $("#tbl_vereda_id").empty().append(info);

          if (veredaIdASetar && veredaIdASetar > 0) {
            $("#tbl_vereda_id").val(veredaIdASetar).trigger("change");
          }
        } else {
          UTIL.mostrarMensajeError(data.output.response.content);
        }
      },
    });
  },
  getVeredasByMunicipioId: function (changeValue = false) {
    if (changeValue) {
      let urlParts = URLToArray(ACTUAL_URL);
      urlParts.mun = $("#tbl_municipio_id").val();

      var newURL = window.location.href.split("?")[0] + "?" + $.param(urlParts);
      location.href = newURL;
    }

    if ($("#tbl_departamento_id").val() != "seleccione") {
      q = {};
      q.op = "veredaget";
      q.municipio_id = $("#tbl_municipio_id").val();
      UTIL.callAjaxRqstPOST(q, this.getVeredasByMunicipioIdHandler);
    } else {
      $("#tbl_vereda_id").empty().append("");
    }
  },
  getVeredasByMunicipioIdHandler: function (data) {
    informacionMunicipio = {};
    UTIL.cursorNormal();
    if (data.output.valid) {
      var res = data.output.response;
      var info = "";
      informacionMunicipio = data.output.municipio[0];
      info += "<option value=''>Seleccione</option>";
      for (var j in res) {
        if ($("#filtroVeredaById").val() === "si") {
          info +=
            "<option value='" +
            res[j].id +
            "'>" +
            res[j].nombre_vereda +
            "</option>";
        } else {
          if (
            typeof veredaSelect != "undefined" &&
            veredaSelect == res[j].nombre_vereda
          ) {
            info +=
              "<option value='" +
              res[j].nombre_vereda +
              "' selected>" +
              res[j].nombre_vereda +
              "</option>";
          } else {
            info +=
              "<option value='" +
              res[j].nombre_vereda +
              "'>" +
              res[j].nombre_vereda +
              "</option>";
          }
        }
      }
      $("#tbl_vereda_id").empty().append(info);
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },
  getMunicipiosByDepartamentoIdV2SeteraCodigoMunicipio: function (
    departamentoId,
    municipioSetear = 0
  ) {
    UTIL.cursorBusy();
    $.ajax({
      url: "admin/ajax/rqst.php",
      type: "GET",
      dataType: "json",
      data: {
        op: "ciudadget",
        codigo_departamento: departamentoId,
      },
      success: function (data) {
        UTIL.cursorNormal();

        if (!data.output.valid) {
          UTIL.mostrarMensajeError(data.output.response.content);
          return;
        }

        const res = data.output.response;
        const $municipioSelect = $("#tbl_municipio_id");

        // Generar opciones con map y join para mejor rendimiento
        const opciones = res
          .map(
            (m) =>
              `<option value="${m.codigo_muncipio}">${m.municipio}</option>`
          )
          .join("");

        $municipioSelect.empty().append(opciones);

        // Asegurar que el valor se setea correctamente después de actualizar las opciones
        if (municipioSetear > 0) {
          setTimeout(
            () => $municipioSelect.val(municipioSetear).trigger("change"),
            10
          );
        }
      },
    });
  },
  
  getMunicipiosOpcionSelectTodos: function () {
    let q = {
      op: "ciudadget",
      codigo_departamento: UTIL.getDepartamentoPrincipal(),
    };

    UTIL.cursorBusy();

    $.ajax({
      data: q,
      type: "GET",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function (data) {
        UTIL.cursorNormal();

        UTIL.cursorNormal();

        if (!data.output.valid) {
          UTIL.mostrarMensajeError(data.output.response.content);
          return;
        }

        const res = data.output.response;

        const depto = $("#tbl_departamento_id").val();
        const municipioDelUsuario = $("#municipioUsuario").val(); // Municipio del usuario
        const tipoUsuario = $("#tipoUsuario").val(); // Tipo de usuario
        const $municipio = $("#tbl_municipio_id");
        let optionsHtml = "";

        // Si es Secretario_Despacho , SuperAdministrador,  Administrador, agregar la opción "Todos"
        if (
          tipoUsuario === "Secretario_Despacho" ||
          tipoUsuario === "Administrador" ||
          tipoUsuario === "SuperAdministrador"
        ) {
          optionsHtml += `<option value="todos">Todos</option>`;
        }

        const generarOpcion = (municipio, rutaMapa, seleccionado) =>
          `<option value="${municipio.codigo_muncipio}" data-mapa='${rutaMapa}' ${seleccionado}>${municipio.municipio}</option>`;

        res.forEach((municipio) => {
          if (
            (tipoUsuario === "Alcalde" || tipoUsuario === "Auxiliar_Alcalde") &&
            municipioDelUsuario &&
            municipioDelUsuario !== municipio.codigo_muncipio
          ) {
            return; // Saltar municipios que no le corresponden al Alcalde o Auxiliar
          }

          const rutaMapa = municipio.carpeta_mapa
            ? municipio.carpeta_mapa.replace(
                "mapa-veredas/",
                `mapa-veredas/${depto}/`
              )
            : "";
          const seleccionado =
            typeof munSelect !== "undefined" &&
            munSelect === municipio.codigo_muncipio
              ? "selected"
              : "";

          optionsHtml += generarOpcion(municipio, rutaMapa, seleccionado);
        });

        $municipio.empty().append(optionsHtml);
      },
      error: function () {
        UTIL.cursorNormal();
        UTIL.mostrarMensajeError("Error al obtener municipios.");
      },
    });
  },
  getInformacionDeMunicipioByIdMunicipio: function (codigoMunicipio) {
    if ($("#tbl_municipio_id").val() != "seleccione") {
      q = {};
      q.op = "ciudadget";
      q.codigo_muncipio = codigoMunicipio;
      UTIL.callAjaxRqstPOST(q,this.getInformacionDeMunicipioByIdMunicipioHandler);
    } else {
      $("#tbl_municipio_id").empty().append("");
    }
  },
  getInformacionDeMunicipioByIdMunicipioHandler: function (data) {
    informacionMunicipio = {};
    UTIL.cursorNormal();
    if (data.output.valid) {
      informacionMunicipio = data.output.response[0];
    } else {
      informacionMunicipio = {};
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },

  // Cargar secretarías municipales por código de municipio
  getSecretariasMunicipales: function() {
    const codigoMunicipio = $("#tbl_municipio_id").val();

    if (!codigoMunicipio || codigoMunicipio === "" || codigoMunicipio === "seleccione") {
      $("#tbl_secretarias_id").empty().append('<option value="">Seleccione primero un municipio</option>');
      return;
    }

    q = {};
    q.op = "secretariasmunicipalespormunicipio";
    q.codigo_municipio = codigoMunicipio;

    UTIL.cursorBusy();
    $.ajax({
      data: q,
      type: "POST",
      dataType: "json",
      url: "admin/ajax/rqst.php",
      success: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
          const res = data.output.response;
          let info = '<option value="">Seleccione</option>';

          res.forEach((secretaria) => {
            info += `<option value="${secretaria.id}">${secretaria.secretaria}</option>`;
          });

          $("#tbl_secretarias_id").empty().append(info);
        } else {
          $("#tbl_secretarias_id").empty().append('<option value="">No hay secretarías disponibles</option>');
        }
      },
      error: function() {
        UTIL.cursorNormal();
        $("#tbl_secretarias_id").empty().append('<option value="">Error al cargar secretarías</option>');
      }
    });
  }
};