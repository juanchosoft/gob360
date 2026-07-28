/**
 * * @param {string} factorId - El ID del factor seleccionado.
 * @param {string} cantidadNueva - El valor numérico ingresado en el campo.
 * @returns {string} codigo color
 */
function determineMapColor(factorId, cantidadNueva, secretariaId) {
    const quantity = parseInt(cantidadNueva);

    if (isNaN(quantity) || quantity < 0) {
        return "#CCCCCC"; 
    }
    

    console.log("Secretaría ID actual para el color:", secretariaId); 
    
    if (secretariaId === '10') { 
        if (quantity > 200) return "#0000FF";
        return "#ADD8E6"; // Azul Claro
    } else {

        if (quantity === 0) {
            return "#00FF00"; // Verde
        } else if (quantity <= 50) {
            return "#FFFF00"; // Amarillo
        } else if (quantity <= 150) {
            return "#FFA500"; // Naranja
        } else {
            return "#FF0000"; // Rojo
        }
    }

}

function updateMapColorFromForm() {
    const municipioId = $('#tbl_municipio_id').val();
    const factorId = $('#factorId').val();
    const cantidadNueva = $('#cantidad_nueva').val();
    const secretariaId = $('#secretariaId').val(); 



    if (!municipioId || municipioId === 'seleccione') {
        return; 
    }
    
    const newColor = determineMapColor(factorId, cantidadNueva, secretariaId); 
    
    const selector = `#contenido-mapa-nuevo path[data-url*='mun=${municipioId}']`;
    const pathElement = $(selector); 
    
    if (pathElement.length) {
        pathElement.attr('fill', newColor);
        

        pathElement.css('stroke', '#00FFFF').css('stroke-width', '5px'); 
        setTimeout(() => {
             pathElement.css('stroke', '#000').css('stroke-width', '0.3px'); 
        }, 1500);

    } else {
        console.warn(`No se encontró el path del Nuevo Mapa para el Municipio ID: ${municipioId}.`);
    }
}


$(document).on("ready", initactualizarinformacion);

function initactualizarinformacion() {
  ACTUALIZACION_INFORMACION.init();
}

const ACTUALIZACION_INFORMACION = {
  init() {
    $("#factorId").on("change", function () {
      ACTUALIZACION_INFORMACION.obtenerRegistrosExistentes(true);

      updateMapColorFromForm(); 
    });

    $("#tbl_departamento_id, #tbl_municipio_id, #tbl_vereda_id").on(
      "change",
      function () {
        ACTUALIZACION_INFORMACION.obtenerRegistrosExistentes();

        updateMapColorFromForm();
      }
    );
    
    $("#cantidad_nueva").on("input", updateMapColorFromForm);

    $("#secretariaId").on("change", function () {
        updateMapColorFromForm(); 
    });
    
  },

  obtenerRegistrosExistentes(forzarConsulta = false) {
    const codDepartamento_id = $("#tbl_departamento_id").val();
    const codMunicipio_id = $("#tbl_municipio_id").val();
    const vereda_id = $("#tbl_vereda_id").val();
    const factorId = $("#factorId").val();

    if (!codDepartamento_id || !codMunicipio_id || !vereda_id || !factorId) {
      if (!forzarConsulta) {
        console.warn("Faltan datos para la consulta");
        return;
      }
    }

    console.log("Ejecutando consulta para factorId:", factorId);

    const datos = {
      op: "obtener_registros_existentes",
      codDepartamento_id: codDepartamento_id,
      codMunicipio_id: codMunicipio_id,
      vereda_id: vereda_id,
      factorId: factorId,
    };

    fetch("admin/ajax/rqst.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams(datos),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.output.valid) {
          ACTUALIZACION_INFORMACION.actualizarTablaRegistros(data.output.data);
        } else {
          console.error("Error:", data.output.error);
        }
      })
      .catch((error) => console.error("Error:", error));
  },

  actualizarTablaRegistros(data) {
    let tabla = document.getElementById("tablaRegistros");
    let tbody = tabla.querySelector("tbody");
    tbody.innerHTML = "";

    if (data.length === 0) {
      tbody.innerHTML =
        "<tr><td colspan='4'>No hay registros disponibles</td></tr>";
      return;
    }

    data.forEach((registro, index) => {
      let row = document.createElement("tr");
      row.innerHTML = `
                <td class="text-center">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="registroSeleccionado" value="${
                          registro.id
                        }">
                        <span class="checkmark"></span>
                    </label>
                </td>
                
               <td>${formatNumero(registro.valor)}</td>

                <td>${registro.tipo_medicion || "N/A"}</td>
            `;

      let checkbox = row.querySelector("input");

      checkbox.addEventListener("change", function () {
        document.querySelectorAll("#tablaRegistros tbody tr").forEach((tr) => {
          tr.classList.remove("selected-row"); 
          tr.querySelector("input").checked = false;
        });

        this.checked = true; 
        row.classList.add("selected-row"); 
        ACTUALIZACION_INFORMACION.registroSeleccionado = registro.id;
        ACTUALIZACION_INFORMACION.registroSeleccionadoCantidadActual =
          registro.valor;
      });

      tbody.appendChild(row);


      if (index === 0) {
        checkbox.checked = true;
        row.classList.add("selected-row");
        ACTUALIZACION_INFORMACION.registroSeleccionado = registro.id;
        ACTUALIZACION_INFORMACION.registroSeleccionadoCantidadActual =
          registro.valor;
      }
    });
  },

  registroSeleccionado: null, 
  registroSeleccionadoCantidadActual: 0, 
  save() {
    console.log("Guardando información...");

    const msj =
      "Falta ingresar información obligatoria, marcada con asterisco.";


    const camposRequeridos = [
      "#tbl_departamento_id",
      "#tbl_municipio_id",
      "#tbl_vereda_id",
      "#actoresId",
      "#cantidad_nueva",
      "#actoresId",
      "#accion_realizada",
      "#accion_realizada",
    ];

    if (!this.validarCampos(camposRequeridos)) {
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if (!this.registroSeleccionado) {
      UTIL.mostrarMensajeError(
        "Debe seleccionar un registro antes de guardar."
      );
      return;
    }

    const iframe1 = $("#ifm1").attr("data-url") || null;
    const iframe2 = $("#ifm2").attr("data-url") || null;
    const iframe3 = $("#ifm3").attr("data-url") || null;
    const iframe4 = $("#ifm4").attr("data-url") || null;


    const datos = {
      op: "actualizacioninformacionsave",
      id: $("#id").val(),
      tbl_ingreso_informacion_id: this.registroSeleccionado,
      codDepartamento_id: $("#tbl_departamento_id").val(),
      codMunicipio_id: $("#tbl_municipio_id").val(),
      vereda_id: $("#tbl_vereda_id").val(),
      factorId: $("#factorId").val(),
      actoresId: $("#actoresId").val(),
      accion_realizada: $("#accion_realizada").val(),
      valor_actualizacion: $("#cantidad_nueva").val(),
      valor_actual: this.registroSeleccionadoCantidadActual,
      foto1: iframe1,
      foto2: iframe2,
      foto3: iframe3,
      foto4: iframe4,
    };

    if (datos.valor_actualizacion > datos.valor_actual) {
      UTIL.mostrarMensajeError(
        "El valor de actualización no puede ser mayor al valor actual " +
          datos.valor_actual
      );
      return;
    }


    UTIL.callAjaxRqstPOST(datos, ACTUALIZACION_INFORMACION.savehandler);
  },

  savehandler(data) {
    UTIL.cursorNormal();

    if (data.output.valid) {
      updateMapColorFromForm();
      
      UTIL.mostrarMensajeExitoso("Información guardada correctamente. El Nuevo Mapa se ha actualizado visualmente.");
      
      setTimeout(() => {
        window.location = "";
      }, 1000); 
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },

  validarCampos(campos) {
    for (const campo of campos) {
      if ($(campo).val() === "") {
        return false;
      }
    }
    return true;
  },
};

function formatNumero(num) {
  if (!num) return "0";

  num = parseFloat(num);
  if (isNaN(num)) return num;

  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}