$(document).on("ready", initactualizarinformacion);

function initactualizarinformacion() {
  ACTUALIZACION_INFORMACION.init();
}

const ACTUALIZACION_INFORMACION = {
  registroSeleccionado: null, 
  registroSeleccionadoCantidadActual: 0, 
  
  $filaSeleccionada: null,

  init() {
    $("#factorId").on("change", function () {
      ACTUALIZACION_INFORMACION.obtenerRegistrosExistentes(true);
    });

    $("#tbl_departamento_id, #tbl_municipio_id, #tbl_vereda_id").on(
      "change",
      function () {
        ACTUALIZACION_INFORMACION.obtenerRegistrosExistentes();
      }
    );
  },

  obtenerRegistrosExistentes(forzarConsulta = false) {
    const codDepartamento_id = $("#tbl_departamento_id").val();
    const codMunicipio_id = $("#tbl_municipio_id").val();
    const vereda_id = $("#tbl_vereda_id").val();
    const factorId = $("#factorId").val();
    
    const ID_TABLA_PRINCIPAL = "tablaRegistrosAislada";

    const tabla = document.getElementById(ID_TABLA_PRINCIPAL);
    if (!tabla) {
        console.error("Error crítico: No se encontró la tabla con ID:", ID_TABLA_PRINCIPAL);
        return; 
    }
    const tbody = tabla.querySelector("tbody");

    if (!tbody) {
        console.error("Error crítico: No se encontró el tbody dentro de la tabla:", ID_TABLA_PRINCIPAL);
        return;
    }

    if (!codMunicipio_id || codMunicipio_id === 'seleccione' || !vereda_id || vereda_id === 'seleccione') {
      console.warn("Faltan datos (Municipio o Vereda) para la consulta.");
      
      tbody.innerHTML = "<tr><td colspan='5' class='text-center'>Seleccione una Vereda.</td></tr>";
      return; 
    }
    
    console.log("Ejecutando consulta para factorId:", factorId);

    const datos = {
      op: "obtener_registros_existentes",
      codDepartamento_id: codDepartamento_id,
      codMunicipio_id: codMunicipio_id,
      tbl_vereda_id: vereda_id,
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
          ACTUALIZACION_INFORMACION.actualizarTablaRegistros(data.output.data, ID_TABLA_PRINCIPAL); 
        } else {
          console.error("Error:", data.output.error);
        }
      })
      .catch((error) => console.error("Error:", error));
  },
   
  actualizarTablaRegistros(data, idTabla) {
    let tabla = document.getElementById(idTabla);
    let tbody = tabla.querySelector("tbody");
    tbody.innerHTML = "";

    const $registroSeleccionadoInput = $("#registroSeleccionadoInput"); 
    

    $registroSeleccionadoInput.val(""); 
    this.registroSeleccionado = null;
    this.registroSeleccionadoCantidadActual = 0;
    this.$filaSeleccionada = null; 
    $("#cantidad_nueva").val(""); 
    
    if (data.length === 0) {

        tbody.innerHTML = "<tr><td colspan='5' class='text-center'>No hay registros disponibles.</td></tr>"; 
        return;
    }
    
    data.forEach((registro, index) => {
      try{
        let row = document.createElement("tr");
        row.style.cursor = "pointer";

        row.dataset.valorOriginal = registro.valor || "0"; 


        let td1_id = document.createElement("td");
        td1_id.textContent = registro.id || ""; 
        row.appendChild(td1_id);


        let td2_factor = document.createElement("td");
        td2_factor.textContent = registro.nombre_factor || "N/A";
        row.appendChild(td2_factor);


        let td3_valor = document.createElement("td");
        td3_valor.textContent = formatNumero(registro.valor_inicial || "0");
        row.appendChild(td3_valor);


        let td4_valor_actual = document.createElement("td");
        td4_valor_actual.textContent = formatNumero(registro.valor || "0"); 
        td4_valor_actual.classList.add('valor-actual-display');
        row.appendChild(td4_valor_actual);


        let td5_unidad = document.createElement("td");
        td5_unidad.textContent = registro.tipo_medicion || "N/A";
        row.appendChild(td5_unidad);
        
        

        row.addEventListener("click", function () {

            document.querySelectorAll("#" + idTabla + " tbody tr").forEach((tr) => {
              tr.classList.remove("selected-row");
            });

            this.classList.add("selected-row");

            const registroId = registro.id; 


            const valorInicialCrudo = registro.valor_inicial || "0"; 
            const valorActualCrudo = registro.valor || "0";
            


            let valorLimpioParaComparacion = parseFloat(valorActualCrudo); 
            let valorFormateado = formatNumero(valorActualCrudo);
            

            ACTUALIZACION_INFORMACION.$filaSeleccionada = $(this); 
            ACTUALIZACION_INFORMACION.registroSeleccionado = registroId;

            ACTUALIZACION_INFORMACION.registroSeleccionadoCantidadActual = valorLimpioParaComparacion; 
            $registroSeleccionadoInput.val(registroId); 
            
            $("#cantidad_nueva").val(valorFormateado);



            // config modal
            $("#modal-factor-nombre").text(registro.nombre_factor || "N/A");
            $("#modal-valor-inicial").text(formatNumero(valorInicialCrudo || "0"));
            $("#modal-valor-actual").text(formatNumero(valorActualCrudo || "0")); 
            $("#modal-unidad-medida").text(registro.tipo_medicion || "N/A");

            $("#modalActualizacion").modal('show');

        });

        tbody.appendChild(row);
        

        if (index === 0) {
            //row.click();
            row.classList.add("selected-row");// seleciona el primero
        }
      }  catch (e) {
        console.error("Error al procesar el registro:", registro, "Error:", e);
      }
    });

    $("#divInformacion").show(); 
  },

  save() {
    console.log("Guardando información...");

    const msj = "Falta ingresar información obligatoria, marcada con asterisco.";

    const camposRequeridos = [
      "#tbl_departamento_id",
      "#tbl_municipio_id",
      "#tbl_vereda_id",
      "#cantidad_nueva",
      "#accion_realizada",
    ];

    if (!this.validarCampos(camposRequeridos)) {
      UTIL.mostrarMensajeValidacion(msj);
      return;
    }
    if (!this.registroSeleccionado || !this.$filaSeleccionada) { 
      UTIL.mostrarMensajeError(
        "Debe seleccionar un registro (fila de la tabla) antes de guardar."
      );
      return;
    }
    

    const valorInput = $("#cantidad_nueva").val().toString();
    
    let valorLimpio = valorInput.replace(/\./g, ''); 
    
    valorLimpio = valorLimpio.replace(/,/g, '.'); 
    
    const valorActualizacionCrudo = parseFloat(valorLimpio);

    const iframe1 = $("#ifm1").attr("data-url") || null;
    const iframe2 = $("#ifm2").attr("data-url") || null;
    const iframe3 = $("#ifm3").attr("data-url") || null;
    const iframe4 = $("#ifm4").attr("data-url") || null;


    let actoresIdValue = $("#actoresId").val();
    if (!actoresIdValue || actoresIdValue === 'seleccione') {
        actoresIdValue = '1';
    }


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
      valor_actualizacion: valorActualizacionCrudo,
      valor_actual: this.registroSeleccionadoCantidadActual, 
      foto1: iframe1,
      foto2: iframe2,
      foto3: iframe3,
      foto4: iframe4,
    };


    if (parseFloat(datos.valor_actualizacion) > parseFloat(datos.valor_actual)) {
      UTIL.mostrarMensajeError(
        // "El valor de actualización (" + formatNumero(datos.valor_actualizacion) + ") no puede ser mayor al valor inicial (" + formatNumero(datos.valor_actual) + ")"
        "El valor de actualización (" + formatNumeroLocal(datos.valor_actualizacion) + ") no puede ser mayor al valor inicial (" + formatNumeroLocal(datos.valor_actual) + ")"
      );
      return;
    }
    

     if (parseFloat(datos.valor_actualizacion) === parseFloat(datos.valor_actual)) {
      UTIL.mostrarMensajeError(
        "El valor de actualización es el mismo que el valor inicial. Debe ingresar un valor diferente para guardar."
      );
      return;
    }


    UTIL.callAjaxRqstPOST(datos, ACTUALIZACION_INFORMACION.savehandler);
  },

  savehandler(data) {
    UTIL.cursorNormal();
    
    if (data.output.valid) {
      UTIL.mostrarMensajeExitoso("Información guardada correctamente");
      // 1. CERRAR EL MODAL
      $("#modalActualizacion").modal('hide'); 
      
      const nuevoValor = $("#cantidad_nueva").val().toString().replace(/\./g, '').replace(/,/g, '.');
      const nuevoValorFormateado = formatNumero(nuevoValor);
      
      if (ACTUALIZACION_INFORMACION.$filaSeleccionada) {
          const $celdaValorActual = ACTUALIZACION_INFORMACION.$filaSeleccionada.find('.valor-actual-display');
          
          if ($celdaValorActual.length) {

              $celdaValorActual.text(nuevoValorFormateado);
              
              ACTUALIZACION_INFORMACION.registroSeleccionadoCantidadActual = nuevoValor;
              
              $("#cantidad_nueva").val(""); 
              $("#accion_realizada").val(""); 
              

              $("#ifm1, #ifm2, #ifm3, #ifm4")
                  .removeAttr("data-url") 
                  .each(function() {
                      $(this).attr("src", "upload.php"); 
                  });

          } else {
              console.error("Error: No se encontró la celda de visualización de Valor Actual.");
          }
      } else {
          console.warn("Advertencia: No hay fila seleccionada, recargando página.");
          setTimeout(() => {
            window.location.reload(); 
          }, 1000);
      }
      
    } else {
      UTIL.mostrarMensajeError(data.output.response.content);
    }
  },

  validarCampos(campos) {
    for (const campo of campos) {
      if ($(campo).val() === "" || $(campo).val() === "seleccione") { 
        return false;
      }
    }
    return true;
  },
};

function formatNumeroLocal(num) {
  if (num === null || num === undefined || num === '') return "0,00"; 

  let numFloat = parseFloat(num);
  if (isNaN(numFloat)) return "0,00"; 
  
  
  let formatted;
  if (numFloat % 1 === 0) {
      formatted = numFloat.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  } else {
      formatted = numFloat.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); 
      formatted = formatted.replace('.', ','); 
  }
  return formatted;
}

function formatNumero(num) { 
  if (num === null || num === undefined || num === '') return "0,00"; 

  let numStr = num.toString().replace(/,/g, ''); 
  
  let numFloat = parseFloat(numStr);
  if (isNaN(numFloat)) return "0,00"; 
  
  let formatted;
  
  formatted = numFloat.toLocaleString('es-CO', { 
    minimumFractionDigits: (numFloat % 1 === 0) ? 0 : 2, 
    maximumFractionDigits: (numFloat % 1 === 0) ? 0 : 2 
  });
  
  return formatted; 
}
