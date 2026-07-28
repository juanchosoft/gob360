$(function () {
  cargaData();
  function cargaData() {
    window.dtSecretarias = $("#dynamictable").DataTable({
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
        url: "./admin/controllers/secretariaCtrl.php",
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
            return `<button class="btn btn-sm btn-transparent editar-informacion" 
                  onclick="editSecretaria('${data}');"
                  title="Editar" data-toggle="modal">
              <i class="feather icon-edit"></i>
            </button>`;
          },
        },
        {
          data: "secretaria",
        },
        {
          data: "secretario",
        },
        {
          data: "correo",
        },
        {
          data: "mostrar",
        },
      ],
    });
    $("#customSearch")
      .off()
      .on("keyup", function () {
        if (window.dtSecretarias) {
          window.dtSecretarias.search(this.value).draw();
        }
      });
  }
});

function ingresarSecretaria() {
  $("#newModalSecretaria").modal("show");
}

function saveNewSecretaria() {
  const secretaria = $("#newSecretaria").val().trim();
  const secretario = $("#newSecretario").val().trim();
  const correo = $("#newEmail").val().trim();
  const mostrar = $("#newHabilitado").val();

  if (!secretaria) {
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre de la secretaría.",
    });
    $("#newSecretaria").focus();
    return;
  }

  if (!secretario) {
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre del secretario.",
    });
    $("#newSecretario").focus();
    return;
  }

  if (!correo) {
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el correo electrónico.",
    });
    $("#newEmail").focus();
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(correo)) {
    Swal.fire({
      icon: "error",
      title: "Correo inválido",
      text: "Por favor ingresa un correo electrónico válido.",
    });
    $("#newEmail").focus();
    return;
  }

  if (mostrar === "Seleccione") {
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona si está habilitado.",
    });
    $("#newHabilitado").focus();
    return;
  }

  const data = {
    secretaria,
    secretario,
    correo,
    mostrar,
  };

  $.ajax({
    url: "./admin/controllers/secretariaCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "newSecretaria",
      data: data,
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "¡Registrado!",
          text:
            response.message || "La secretaría se ha guardado correctamente.",
          confirmButtonColor: "#3085d6",
          confirmButtonText: "Aceptar",
        });

        $("#formNewSecretaria")[0].reset();
        $("#newModalSecretaria").modal("hide");

        if (window.dtSecretarias) {
          window.dtSecretarias.ajax.reload();
        }
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al guardar",
          text:
            response.message || "Ocurrió un problema al guardar la secretaría.",
          confirmButtonColor: "#d33",
          confirmButtonText: "Cerrar",
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: "No se pudo procesar la solicitud.",
      });
    },
  });
}

function editSecretaria(id) {
  $.ajax({
    url: "./admin/controllers/secretariaCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "editSecretaria",
      data: id,
    }),
    dataType: "json",
    success: function (response) {
      console.log(response.data[0]);
      if (response.state) {
        const secretaria = response.data[0];
        $("#newModalSecretaria").modal("show");
        $("#editId").val(secretaria.id);
        $("#newSecretaria").val(secretaria.secretaria);
        $("#newSecretario").val(secretaria.secretario);
        $("#newEmail").val(secretaria.correo);
        $("#newHabilitado").val(secretaria.mostrar);

        $("#btnSaveSecretaria")
          .text("Actualizar")
          .attr("onclick", `updateSecretaria(${secretaria.id});`);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al editar la secretaría",
          text:
            response.message || "Ocurrió un problema al editar la secretaría.",
          confirmButtonColor: "#d33",
          confirmButtonText: "Cerrar",
        });
      }
    },
  });
}

function updateSecretaria(id) {
  const secretaria = $("#newSecretaria").val().trim();
  const secretario = $("#newSecretario").val().trim();
  const correo = $("#newEmail").val().trim();
  const mostrar = $("#newHabilitado").val();

  if (!secretaria) {
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre de la secretaría.",
    });
    $("#newSecretaria").focus();
    return;
  }

  if (!secretario) {
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el nombre del secretario.",
    });
    $("#newSecretario").focus();
    return;
  }

  if (!correo) {
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor ingresa el correo electrónico.",
    });
    $("#newEmail").focus();
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(correo)) {
    Swal.fire({
      icon: "error",
      title: "Correo inválido",
      text: "Por favor ingresa un correo electrónico válido.",
    });
    $("#newEmail").focus();
    return;
  }

  if (mostrar === "Seleccione") {
    Swal.fire({
      icon: "warning",
      title: "Campo requerido",
      text: "Por favor selecciona si está habilitado.",
    });
    $("#newHabilitado").focus();
    return;
  }

  $.ajax({
    url: "./admin/controllers/secretariaCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "updateSecretaria",
      data: {
        id,
        secretaria,
        secretario,
        correo,
        mostrar,
      },
    }),
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "Actualizado",
          text: "La secretaría fue actualizada correctamente.",
          timer: 2000,
          showConfirmButton: false,
        });

        $("#formNewSecretaria")[0].reset();
        $("#newModalSecretaria").modal("hide");

        if (window.dtSecretarias) {
          window.dtSecretarias.ajax.reload();
        }

        $("#btnSaveSecretaria")
          .text("Guardar")
          .attr("onclick", "saveNewSecretaria();");
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: response.message || "No se pudo actualizar la secretaría.",
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: "No se pudo procesar la solicitud.",
      });
    },
  });
}

$(document).on('ready', init);

var q;
let isUpdating = false; 
let updateHistoryDebounce;
const capacidadFiscalYFinanciera = "Capacitacion Fiscal y Financiera";

function init() {
    q = {};

    const accionFromUrl = getParamFromUrl("accion");
    if (accionFromUrl) {
        updateSelectWithoutTrigger("#accionHacienda", decodeURIComponent(accionFromUrl));
    }
}


function getParamFromUrl(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

function updateSelectWithoutTrigger(selectId, value) {
    const selectElement = $(selectId);
    isUpdating = true;
    selectElement.off("change");
    selectElement.val(value);
    selectElement.trigger("change");
    setTimeout(() => {
        isUpdating = false;
    }, 300);
}

//  Cuando cambia la secretaría
function updateUrlSecretaria(item) {
    if (isUpdating) return;

    const selectedSecretaria = item.value || 2;
    const currentUrl = new URL(window.location.href);

    updateSelectWithoutTrigger("#secretariaId", selectedSecretaria);

    clearTimeout(updateHistoryDebounce);
    updateHistoryDebounce = setTimeout(() => {
        currentUrl.searchParams.set('secretaria', selectedSecretaria);

        if (parseInt(selectedSecretaria) === parseInt(UTIL.getItemHacienda())) {
          let accionHacienda = $("#accionHacienda").val();
          if (!accionHacienda) {
            accionHacienda = "Operativos Contrabando licores";
          }
          currentUrl.searchParams.set("accion", accionHacienda);
        } else {
          currentUrl.searchParams.delete("accion");
        }

        window.history.pushState({}, '', currentUrl);

        $.ajax({
            url: currentUrl.toString(),
            type: "GET",
            success: function (response) {
                actualizarDivs(response);
                initTabEvents();
            },
            error: function (error) {
                console.error("Error al cargar contenido:", error);
            }
        });
    }, 500);
}


function updateUrlAccionHacienda(item) {
    if (isUpdating) return;

    const selectedAccion = item.value || capacidadFiscalYFinanciera;
    const currentUrl = new URL(window.location.href);

    clearTimeout(updateHistoryDebounce);
    updateHistoryDebounce = setTimeout(() => {
        currentUrl.searchParams.set('accion', selectedAccion);
        window.history.pushState({}, '', currentUrl);

        $.ajax({
            url: currentUrl.toString(),
            type: "GET",
            success: function (response) {
                actualizarDivs(response);
                initTabEvents();

                updateSelectWithoutTrigger("#accionHacienda", selectedAccion);
            },
            error: function (error) {
                console.error("Error al cargar contenido:", error);
            }
        });
    }, 500);
}

function actualizarDivs(response) {
    const updatedContent = $(response).find("#contenido-mapa").html();
    const divPuntajes = $(response).find("#divPuntajes").html();
    const DivTotalEjecucionSecretarias = $(response).find("#DivTotalEjecucionSecretarias").html();
    const divHacienda = $(response).find("#divHacienda").html();
    const InformacionGeneral = $(response).find("#InformacionGeneral").html();

    $("#contenido-mapa").html(updatedContent);
    $("#divPuntajes").html(divPuntajes);
    $("#DivTotalEjecucionSecretarias").html(DivTotalEjecucionSecretarias);
    $("#divHacienda").html(divHacienda);
    $("#InformacionGeneral").html(InformacionGeneral);
}

function initTabEvents() {
    document.querySelectorAll('.tab-list .tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab-list .tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
        });
    });
}


function agruparConsolidado(datos) {
    if (!Array.isArray(datos)) return [];

    const mapa = {};

    datos.forEach(d => {

        const key = d.tbl_factor_id;

        const inicial = Number(d.total_cantidad_inicial ?? 0);
        const actual  = Number(d.total_cantidad_actual ?? inicial);

        if (!mapa[key]) {
            mapa[key] = {
                factor: d.factor,
                tipo_medicion: d.tipo_medicion,
                icono: d.icono,
                tbl_factor_id: d.tbl_factor_id,
                total_cantidad_inicial: inicial,
                total_cantidad_actual: actual,
                ultima_modificacion_factor: d.ultima_modificacion_factor
            };
        }
    });

    return Object.values(mapa);
}

function llenarModalConsolidado(datos, nombreMunicipio) {

    const filas = agruparConsolidado(datos);

    let html = `
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle text-center" style="width: 100%">
            <thead class="thead-dark bg-primary text-white">
                <tr>
                    <th>Ícono</th>
                    <th style="min-width:150px;">Factor</th>
                    <th style="min-width:100px;">Cantidad Inicial</th>
                    <th style="min-width:100px;">Cantidad Actual</th>
                    <th>Unidad</th>
                </tr>
            </thead>
            <tbody>
    `;

    filas.forEach(f => {

    const hayCambio = Number(f.total_cantidad_actual) !== Number(f.total_cantidad_inicial);

    const estiloFila = hayCambio 
        ? 'style="background-color: #d4efdf !important;"'  
        : '';

    html += `
        <tr ${estiloFila}>
            <td><img src="${f.icono}" width="32px"></td>

            <td class="text-start fw-semibold text-dark" 
                style="word-break:break-word; font-size:12px;">
                ${f.factor}
            </td>

            <td>
                <span style="font-size:12px; font-weight:500;
                    color:#145a32; background:#f1f8e9;
                    padding:6px 14px; border-radius:8px;">
                    ${Number(f.total_cantidad_inicial).toLocaleString('es-CO')}
                </span>
            </td>

            <td>
                <span style="font-size:12px; font-weight:bold;
                    color:#0e6655; background:#a2ded0;
                    padding:6px 14px; border-radius:12px;
                    box-shadow:0 0 6px rgba(26,188,156,.4);">
                    ${Number(f.total_cantidad_actual).toLocaleString('es-CO')}
                </span>
            </td>

            <td><span class="text-muted" style="font-size:12px;">${f.tipo_medicion}</span></td>
        </tr>
    `;
});

    html += `
            </tbody>
        </table>
    </div>`;

    $('#modalMunicipioNombre').text(nombreMunicipio.toUpperCase());
    $('#modalConsolidadoBody').html(html);
}


// CÓDIGO EN mapa_secretaria.js (mostrarConsolidadoMunicipio)
const RUTA_RQST = './admin/ajax/rqst.php'; 

function mostrarConsolidadoMunicipio(municipioId, secretariaId, accion) {
    $('#modalMunicipioNombre').text('Cargando...');
    $('#modalConsolidadoBody').html('<p class="text-center text-info"><i class="feather icon-refresh-cw icon-spin"></i> Cargando datos de ejecución...</p>');
    $('#modalConsolidado').modal('show'); 

    $.ajax({
        url: RUTA_RQST, 
        method: 'POST',
        data: {
            op: 'getConsolidadoFactores', 
            municipioId: municipioId,
            secretariaId: secretariaId,
            accion: accion
        },
        dataType: 'json',
        success: function(response) {
            
            let respuestaReal = response;
            if (response.output) {
                respuestaReal = response.output;
            }
            
            const datosConsolidados = Array.isArray(respuestaReal.response) 
                                        ? respuestaReal.response 
                                        : []; 
            
            if (respuestaReal.valid) {
                llenarModalConsolidado(datosConsolidados, respuestaReal.nombreMunicipio); 
            } else {
                $('#modalMunicipioNombre').text(respuestaReal.nombreMunicipio || 'Error de Consulta');
                $('#modalConsolidadoBody').html('<p class="alert alert-danger text-center">Sin datos para la consulta. Intenta nuevamente.</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error, xhr.responseText);
            $('#modalMunicipioNombre').text('Error de Conexión');
            $('#modalConsolidadoBody').html('<p class="alert alert-danger text-center">Error al contactar al servidor. Revise la ruta de rqst.php.</p>');
        }
    });
}


function mostrarProyectos(estado, secretaria, provincia) {
    console.log(`Llamada a mostrarProyectos. Estado: ${estado}, Secretaria: ${secretaria}, Provincia: ${provincia}`);
  
    const MODAL_ID = '#modalListaProyectos';

    $(MODAL_ID).modal('show');

    $(MODAL_ID + 'Label').text(`Proyectos en estado: ${estado}`);

    $('#modal-body-content').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div><p class="mt-2">Cargando proyectos...</p></div>');


    const RUTA_BACKEND = 'admin/classes/fetch_proyectos_por_estado.php'; 
    
    fetch(RUTA_BACKEND, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `estado=${encodeURIComponent(estado)}&secretaria=${encodeURIComponent(secretaria)}&provincia=${encodeURIComponent(provincia)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} en la ruta ${RUTA_BACKEND}`);
        }
        return response.text();
    })
    .then(html => {
        $('#modal-body-content').html(html);
    })
    .catch(error => {
        console.error('Error al cargar proyectos:', error);
        $('#modal-body-content').html(`<div class="alert alert-danger" role="alert">
            <strong>Error de Carga:</strong> ${error.message}. Por favor, revisa la consola (F12 > Console > Red).
        </div>`);
    });
}


function obtenerDatosDeTarjeta(elemento) {
    
    const secretaria = $('#secretariaId').val(); 
    const provincia = $('#provinciaSelect').val() || 'Todos'; 

    const estado = elemento.data('estado'); 

    const estadoTexto = elemento.find('h5.mb-0').text().trim(); 


    const mapaEstados = {
        "Proyectos Terminados": "Terminado", 
        "En Ejecución": "Ejecucion", 
        "En Contratación": "En Contratacion", 
        "En Formulación": "En Formulacion",
    };

    const estadoFinal = mapaEstados[estadoTexto] || estadoTexto; 

    if (!estadoFinal) {
        console.error("Error: No se pudo determinar el estado del proyecto al hacer clic.");
        return null;
    }

    return { estado: estadoFinal, secretaria: secretaria, provincia: provincia };
}



$(document).ready(function() {
    
    function manejarClicMapa(element) {
        const munId = element.data('municipio-id');
        const secId = element.data('secretaria-id');
        const accion = element.data('accion');
        
        if (munId && secId && typeof mostrarConsolidadoMunicipio === 'function') {
            mostrarConsolidadoMunicipio(munId, secId, accion);
        } else {
            console.error("Error al hacer clic en municipio: Faltan datos o la función AJAX no está disponible.");
        }
    }
    
    $('#contenido-mapa').on('click', '.municipios', function(e) {
        e.preventDefault(); 
        manejarClicMapa($(this));
    });

    $('#contenido-mapa-nuevo').on('click', '.municipios-nuevo', function(e) {
        e.preventDefault();
        manejarClicMapa($(this));
    });

    $('body').on('click', '.card-estado-proyecto', function(e) {
            e.preventDefault();

            const datos = obtenerDatosDeTarjeta($(this));
            
            if (datos) {
                mostrarProyectos(datos.estado, datos.secretaria, datos.provincia);
            }
        });

});