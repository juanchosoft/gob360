
$(document).on("ready", initmunicipio);

let MUN, DEP, PILAR, SECRETARIA;
let updateHistoryDebounce;
let isUpdating = false; // Flag para evitar loop infinito

function initmunicipio() { }

const MUNICIPIO = {
  init: function () {
    console.log("Initializing MUNICIPIO...");
    DEPARTAMENTO.getMunicipiosConDepartamentoPrincipal();

    const params = UTIL.getParamsFromUrlDepartamentoMunicipio();
    MUN = params.mun;
    PILAR = params.pilar;
    SECRETARIA = params.secretaria;

    // Verificar periódicamente si el select tiene opciones cargadas
    const interval = setInterval(() => {
      const selectElement = $("#tbl_municipio_id");
      if (selectElement.find("option").length > 0) {
        clearInterval(interval); // Detener el intervalo al encontrar las opciones

        // Establecer el valor inicial del select sin activar el evento onchange
        MUNICIPIO.updateSelectWithoutTrigger("#tbl_municipio_id", MUN);
      }

      const selectSecretaria = $("#secretariaId");
      if (selectSecretaria.find("option").length > 0) {
        clearInterval(interval);
        MUNICIPIO.updateSelectWithoutTriggerSecretariaAndPilar(
          "#secretariaId",
          SECRETARIA,
          "secretaria"
        );
      }

      const selectPilar = $("#pilarId");
      if (selectPilar.find("option").length > 0) {
        clearInterval(interval);
        MUNICIPIO.updateSelectWithoutTriggerSecretariaAndPilar(
          "#pilarId",
          PILAR,
          "pilar"
        );
      }
    }, 100); // Comprobar cada 100ms

    // Límite máximo de tiempo para evitar bucles infinitos
    setTimeout(() => {
      clearInterval(interval);
      console.warn(
        "El select tbl_municipio_id no cargó las opciones a tiempo."
      );
    }, 5000); // Tiempo máximo de 5 segundos
  },

  updateUrlMunicipio: function (item) {
    if (isUpdating) return; // Prevenir loop infinito

    const selectedMunicipio = item.value || MUN;

    // Validar si el valor seleccionado ya está en la URL
    const currentUrl = new URL(window.location.href);
    const actualMunicipio = currentUrl.searchParams.get("mun");
    if (selectedMunicipio === actualMunicipio) return; // Evitar cambios innecesarios

    // Actualizar el valor del select y evitar loop infinito
    MUNICIPIO.updateSelectWithoutTrigger(
      "#tbl_municipio_id",
      selectedMunicipio
    );

    // Debounce para limitar llamadas a pushState
    clearTimeout(updateHistoryDebounce);
    updateHistoryDebounce = setTimeout(() => {
      currentUrl.searchParams.set("mun", selectedMunicipio);
      window.history.pushState({}, "", currentUrl);

      MUNICIPIO.loadContentidoMapa(currentUrl);
    }, 500);
  },
  updateUrlPilar: function (item, refrescarTablaCompromisos) {
    if (isUpdating) return; // Prevenir loop infinito

    const selectedPilar = item.value || PILAR;

    // Validar si el valor seleccionado ya está en la URL
    const currentUrl = new URL(window.location.href);
    const actualPilar = currentUrl.searchParams.get("pilar");
    if (selectedPilar === actualPilar) return; // Evitar cambios innecesarios

    // Actualizar el valor del select y evitar loop infinito
    MUNICIPIO.updateSelectWithoutTrigger("#pilarId", selectedPilar);

    // Debounce para limitar llamadas a pushState
    clearTimeout(updateHistoryDebounce);
    updateHistoryDebounce = setTimeout(() => {
      currentUrl.searchParams.set("pilar", selectedPilar);
      window.history.pushState({}, "", currentUrl);

      MUNICIPIO.loadContentidoMapa(currentUrl, refrescarTablaCompromisos);
    }, 500);
  },
  updateUrlSecretaria: function (item, refrescarTablaCompromisos) {
    if (isUpdating) return; // Prevenir loop infinito

    const selectedSecretaria = item.value || SECRETARIA;

    // Validar si el valor seleccionado ya está en la URL
    const currentUrl = new URL(window.location.href);
    const actualSecretaria = currentUrl.searchParams.get("secretaria");
    if (selectedSecretaria === actualSecretaria) return; // Evitar cambios innecesarios

    // Actualizar el valor del select y evitar loop infinito
    MUNICIPIO.updateSelectWithoutTrigger("#secretariaId", selectedSecretaria);

    // Debounce para limitar llamadas a pushState
    clearTimeout(updateHistoryDebounce);
    updateHistoryDebounce = setTimeout(() => {
      currentUrl.searchParams.set("secretaria", selectedSecretaria);
      window.history.pushState({}, "", currentUrl);

      MUNICIPIO.loadContentidoMapa(currentUrl, refrescarTablaCompromisos);
    }, 500);
  },
  updateSelectWithoutTrigger: function (selectId, value) {
    const selectElement = $(selectId);

    isUpdating = true; // Activar flag para evitar loop
    selectElement.off("change"); // Desactivar temporalmente eventos onchange
    selectElement.val(value).trigger("change"); // Actualizar valor
    selectElement.on("change", function () {
      MUNICIPIO.updateUrlMunicipio(this); // Restaurar evento
    });

    setTimeout(() => {
      isUpdating = false; // Desactivar flag después de un breve retraso
    }, 300);
  },
  updateSelectWithoutTriggerSecretariaAndPilar: function (selectId, value, opcion) {
    const selectElement = $(selectId);
    isUpdating = true;
    selectElement.off("change");
    selectElement.val(value).trigger("change");
    selectElement.on("change", function () {
      if (opcion === "secretaria") {
        MUNICIPIO.updateUrlSecretaria(this, false);
      } else if (opcion === "pilar") {
        MUNICIPIO.updateUrlPilar(this, false);
      }
    });
    setTimeout(() => {
      isUpdating = false;
    }, 300);
  },
  loadContentidoMapa: function (url, refrescarTablaCompromisos = true) {
    $.ajax({
      url: url.toString(),
      type: "GET",
      success: function (response) {
        const updatedContent = $(response).find("#contenido-mapa").html();
        $("#contenido-mapa").html(updatedContent);

        const divConsolidado = $(response).find("#divConsolidado").html();
        $("#divConsolidado").html(divConsolidado);

        const cardHeaderCompleto = $(response)
          .find("#cardHeaderCompleto")
          .html();
        $("#cardHeaderCompleto").html(cardHeaderCompleto);

        $("#info-alcalde").text($(response).find("#info-alcalde").text());
        $("#info-partido").text($(response).find("#info-partido").text());
        $("#info-poblacion").text($(response).find("#info-poblacion").text());
        $("#info-hombres").text($(response).find("#info-hombres").text());
        $("#info-mujeres").text($(response).find("#info-mujeres").text());

        if (refrescarTablaCompromisos) {
          MUNICIPIO.mostrarTablaCompromisos();
        }
      },
      error: function (error) {
        console.error("Error al cargar contenido:", error);
      },
    });
  },
  abrirModalCompromiso: function (factorId, cantidadActual) {
    $("#factorIdModal").val(factorId);
    $("#cantidadActual").val(cantidadActual);
  },
  guardarCompromiso: function () {
    const cantidad = $("#cantidadCompromiso").val();
    const actor = $("#actoresId").val();
    const observaciones = $("#observacionesCompromiso").val();
    const factorId = $("#factorIdModal").val();
    const cantidadActual = $("#cantidadActual").val();
    const tbl_vereda_id = $("#veredaId").val();
    const codigo_municipio = $("#municipioId").val();
    const codigo_departamento = $("#departamentoId").val();

    if (!factorId) {
      mostrarAlerta("error", "❌ No se encontró un Factor válido.");
      return;
    }
    if (!cantidad || isNaN(cantidad) || cantidad <= 0) {
      mostrarAlerta("error", "❌ Debes ingresar una cantidad válida.");
      return;
    }
    if (!cantidadActual || isNaN(cantidadActual) || cantidadActual <= 0) {
      mostrarAlerta("error", "❌ Debes ingresar una cantidad válida.");
      return;
    }
    if (!actor || actor === "") {
      mostrarAlerta("error", "❌ Debes seleccionar un actor.");
      return;
    }

    const datos = {
      op: "guardarCompromiso",
      tbl_vereda_id: tbl_vereda_id,
      codigo_municipio: codigo_municipio,
      codigo_departamento: codigo_departamento,
      factorId: factorId,
      cantidadActual: cantidadActual,
      cantidad: cantidad,
      actor: actor,
      observaciones: observaciones || "",
    };

    $.ajax({
      url: "admin/ajax/rqst.php",
      type: "POST",
      data: datos,
      dataType: "json",
      success: function (response) {
        if (response.output.valid) {
          // Mostrar mensaje de éxito
          mostrarAlerta("success", "✅ Compromiso guardado correctamente.");

          // Esperar 2 segundos antes de cerrar el modal
          setTimeout(() => {
            $("#modalSeleccionar").modal("hide"); // Cerrar el modal
            $(".modal-backdrop").remove(); // Eliminar el fondo gris
            $("body").removeClass("modal-open"); // Restaurar el scroll de la página

            // Esperar 2 segundos más antes de recargar la página
            setTimeout(() => {
              location.reload();
            }, 2000);
          }, 2000);
        } else {
          mostrarAlerta("error", response.output.response.content);
        }
      },
      error: function () {
        mostrarAlerta("error", "❌ Error en la comunicación con el servidor.");
      },
    });
  },
  mostrarTablaCompromisos: function () {
    const pilar = PILAR;
    const municipio = MUN;
    const departamento = $("#departamentoId").val();

    $.ajax({
      url: "admin/ajax/rqst.php",
      type: "POST",
      data: {
        rqst: "getCompromisosFactores",
        pilarId: pilar,
        municipioId: municipio,
        departamentoId: departamento,
      },
      dataType: "json",
      success: function (response) {
        let html = "";
        if (response.output && response.output.valid) {
          const datos = response.output.response;

          if (datos.length > 0) {
            datos.forEach((item) => {
              html += `<tr>
							<td>${item.actor}</td>
							<td>${item.factor}</td>
							<td>${item.cantidad}</td>
							<td>${item.observaciones ?? ""}</td>
						</tr>`;
            });
          } else {
            html = `<tr><td colspan="4" class="text-center">No hay compromisos registrados aún.</td></tr>`;
          }
        } else {
          html = `<tr><td colspan="4" class="text-center text-danger">Error al cargar compromisos.</td></tr>`;
        }

        $("#tabla-compromisos tbody").html(html);
      },
      error: function () {
        $("#tabla-compromisos tbody").html(
          `<tr><td colspan="4" class="text-center text-danger">Error de conexión.</td></tr>`
        );
      },
    });
  },
};
