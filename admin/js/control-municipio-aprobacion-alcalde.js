let compromiso = null;
let chartIndicadores = null;
let listaSecretarias = []; 

$(function () {
  cargarCompromiso();

  secretarias();

  ciudades();

  // Cargar veredas cuando cambie el municipio
  $("#municipioFiltro").on("change", function () {
    const municipioId = $(this).val();
    if (municipioId) {
      cargarVeredas(municipioId);
    } else {
      $("#veredaFiltro").empty().append('<option value="">Seleccione primero un municipio</option>');
    }
  });

  $("#customSearch").on("keyup", function () {
    if (compromiso) {
      compromiso.search(this.value).draw();
    }
  });

  $("#selectSecretaria").on("change", function () {
    const secretariaId = $(this).val();
    if (secretariaId) indicadores(secretariaId);
  });


  cargarListaSecretariasParaTraslado();

  $('#btnAddSecretaria').on('click', function() {
    $('#contenedor-secretarias-destino').append(generarSecretariaSelect());
  });

  $('#contenedor-secretarias-destino').on('click', '.btn-remove-secretaria', function() {
      if ($('.row-secretaria-destino').length > 1) {
        $(this).closest('.row-secretaria-destino').remove();
      } else {
        Swal.fire('Advertencia', 'Debe haber al menos una Secretaría de destino.', 'warning');
      }
  });

  $('#btnEjecutarTraslado').on('click', ejecutarTraslado);

});



function cargarListaSecretariasParaTraslado() {
    $.ajax({
        url: "./admin/controllers/utilsCtrl.php",
        type: "POST",
        data: JSON.stringify({ method: "secretaria" }),
        dataType: "json",
        contentType: "application/json",
        success: function (response) {
            if (response.data && Array.isArray(response.data)) {
                listaSecretarias = response.data.map(sec => ({
                    id: sec.id,
                    nombre: sec.secretaria
                }));
            }
        }
    });
}


function generarSecretariaSelect() {
    let options = '<option value="">Seleccione Secretaría Destino</option>';
    listaSecretarias.forEach(sec => {
        options += `<option value="${sec.id}">${sec.nombre}</option>`;
    });
    
    return `
        <div class="form-group row row-secretaria-destino mb-2">
            <label class="col-sm-4 col-form-label">Secretaría Destino:</label>
            <div class="col-sm-6">
                <select class="form-control select-secretaria-destino" name="secretaria_destino[]">
                    ${options}
                </select>
            </div>
            <div class="col-sm-2 d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-danger btn-remove-secretaria" title="Quitar Secretaría">
                    <i class="feather icon-minus"></i>
                </button>
            </div>
        </div>
    `;
}


function renderTrasladoBtn(data, type, row) {
    if (type === "display") {
        return `<button class="btn btn-sm btn-warning text-white" 
            onclick="abrirModalTraslado(
                '${row.id}', 
                '${row.compromisos}', 
                '${row.secretaria}',
                '${row.componente}'
            )">
            <i class="feather icon-share"></i> Traslado
        </button>`;
    }
    return data ?? "";
}



window.abrirModalTraslado = function(compromisoId, nombreCompromiso, secretariaActualNombre, componenteNombre) {
    
    $('#modalTrasladoCompetencia').data('compromiso-id', compromisoId);
    

    $('#nombreCompromisoTraslado').text(nombreCompromiso + ' (' + componenteNombre + ')');
    $('#secretariaInicialTraslado').text(secretariaActualNombre);
    $('#logCompromisoOriginal').remove();
    $('#logCompromisoTraslado').remove(); 

    $.ajax({
        url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
        type: "POST",
        data: JSON.stringify({ method: "getCompromisoId", data: compromisoId }),
        dataType: "json",
        contentType: "application/json",
        success: function (response) {
            if (response.data && response.data.length > 0) {
                const data = response.data[0];
                const secretariaOriginalId = data.tbl_secretarias_id;

                const nombreCreador = `${data.nombre_usuario_creador || 'N/A'} ${data.apellido_usuario_creador || ''}`.trim();
                const fechaCreacion = data.created_at ? new Date(data.created_at).toLocaleDateString() : 'N/A';

                const nombreTraslado = `${data.nombre_usuario_traslado || ''} ${data.apellido_usuario_traslado || ''}`.trim();

                let logHTML = `
                    <div id="logCompromisoOriginal" class="alert alert-info py-2 my-2 small">
                        <p class="m-0">
                            <strong>Creado originalmente por:</strong> 
                            <span id="usuarioCreadorOriginal">${nombreCreador}</span>
                        </p>
                        <p class="m-0">
                            <strong>Registrado el:</strong> 
                            <span id="fechaCreacionOriginal">${fechaCreacion}</span>
                        </p>
                    </div>
                `;


                if (nombreTraslado) {
                    logHTML += `
                        <div id="logCompromisoTraslado" class="alert alert-warning py-2 my-2 small">
                            <p class="m-0">
                                <strong>Trasladado por:</strong> 
                                <span>${nombreTraslado}</span>
                            </p>
                        </div>
                    `;
                }

                $('#secretariaInicialTraslado').closest('p').after(logHTML);
                
                $('#modalTrasladoCompetencia').data('secretaria-original-id', secretariaOriginalId);

                $('#contenedor-secretarias-destino').empty();
                $('#contenedor-secretarias-destino').append(generarSecretariaSelect());
                
                $('#modalTrasladoCompetencia').modal('show');
            } else {
                Swal.fire('Error', 'No se pudo cargar la información completa del compromiso.', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error de red al cargar detalles del compromiso.', 'error');
        }
    });
};

function ejecutarTraslado() {
    const compromisoId = $('#modalTrasladoCompetencia').data('compromiso-id');
    const secretariaOriginalId = $('#modalTrasladoCompetencia').data('secretaria-original-id');
    const secretariasDestino = [];
    
    $('.select-secretaria-destino').each(function() {
        const secId = $(this).val();
        if (secId) {
            secretariasDestino.push(secId);
        }
    });

    const secretariasValidas = secretariasDestino.filter(id => id && id != secretariaOriginalId);


    if (secretariasValidas.length === 0) {
        Swal.fire('Advertencia', 'Debe seleccionar al menos una Secretaría de destino válida y diferente a la original.', 'warning');
        return;
    }
    

    const $btn = $('#btnEjecutarTraslado');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Ejecutando...');

    $.ajax({
        url: './admin/controllers/compromisoMunicipioAlcaldeCtrl.php',
        type: 'POST',
        data: JSON.stringify({
            method: 'ejecutarTrasladoPorCompetencia', 
            data: {
                compromiso_original_id: compromisoId,
                secretarias_destino: secretariasValidas
            }
        }),
        dataType: 'json',
        contentType: 'application/json',
        success: function(response) {
            if (response.output && response.output.valid) {
                Swal.fire({
                    icon: "success",
                    title: "¡Traslado Exitoso!",
                    text: `Traslado(s) ejecutado(s) con éxito. Se crearon ${response.output.response.registros_creados} nuevos compromisos.`,
                    confirmButtonColor: "#28a745",
                });
                $('#modalTrasladoCompetencia').modal('hide');
                if (compromiso) {
                    compromiso.ajax.reload(); 
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al trasladar",
                    text: response.output.error || "Ocurrió un problema al ejecutar el traslado.",
                    confirmButtonColor: "#d33",
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error de AJAX:', xhr.responseText);
            Swal.fire({
                icon: "error",
                title: "Error de red",
                text: "No se pudo comunicar con el servidor o hubo un error interno.",
                confirmButtonColor: "#d33",
            });
        },
        complete: function() {
            $btn.prop('disabled', false).text('Ejecutar Traslado(s)');
        }
    });
}

function cargarCompromiso() {
  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  let columnsDefinition = [
    { data: "id" },
    { data: "secretaria" },
    { data: "compromisos", visible: false, render: renderCompromisoBtn },
    { data: "consecuencia", visible: false, render: renderCompromisoBtn },
    { data: "respuesta", visible: false, render: renderCompromisoBtn },
    {
      data: "estado_autorizar",
      render: function (data, type, row) {
        // Si el dato es null o vacío, mostrar "Sin Cumplir" por defecto
        let estadoTexto = data || "Sin Cumplir";
        let color = "#6c757d";
        let texto = estadoTexto.toUpperCase();

        if (texto.includes("CUMPLIDO")) color = "#28a745";
        else if (texto.includes("TRÁMITE")) color = "#ffc107";
        else if (texto.includes("SIN CUMPLIR")) color = "#dc3545";

        return `<span style="
          background-color:${color};
          color:white;
          padding:5px 10px;
          border-radius:6px;
          font-weight:500;
          font-size:13px;
          display:inline-block;
          text-transform:capitalize;
        ">${estadoTexto}</span>`;
      }
    },
    { data: "municipio" },
    { data: "vereda", defaultContent: "Sin vereda" },
    { data: "componente" },
    { data: "tipo_ejecucion" },
    { data: "date" },
    {
      data: "id",
      render: function (data) {
        return `<button class="btn btn-sm btn-transparent" onclick="validarCompromiso('${data}');"><i class="feather icon-edit"></i></button>`;
      },
    },
    {
      data: "id",
      render: function (data) {
        return `<form action="reporte-compromiso-alcalde.php" method="POST" style="display:inline;">
          <input type="hidden" name="reporte" value="1">
          <input type="hidden" name="id" value="${data}">
          <button type="submit" class="btn btn-sm btn-success4">
            <i class="feather icon-eye"></i>
          </button></form>`;
      },
    },
    { data: "aprobador_observacion" },
  ];
  
  if (typeof IS_ADMIN_USER !== 'undefined' && IS_ADMIN_USER === true) {
      columnsDefinition.push({ data: "id", render: renderTrasladoBtn });
  }


  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip",
    processing: true,
    serverSide: true,
    responsive: true,
    destroy: true,
    columnDefs: [{ targets: ["_all"], className: "mdc-data-table__cell" }],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        return JSON.stringify({
          method: "getAllCompromiseEnEspera",
          data: d,
        });
      },
    },
    columns: columnsDefinition,
  });
}


// Render botón "Ver" para compromisos
function renderCompromisoBtn(data, type) {
  if (type === "display") {
    const seguro = (data ?? "")
      .toString()
      .replace(/"/g, "&quot;")
      .replace(/'/g, "\\'");
    return `<button class="btn btn-sm btn-link text-primary" onclick="verCompromiso('${seguro}')">Ver</button>`;
  }
  return data ?? "";
}

// Agregar Observaciones
function validarCompromiso(data) {
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "getCompromisoId", data: data }),
    dataType: "json",
    contentType: "application/json",
    success: async function (response) {
      console.log(response);

      // Mostrar información del compromiso en el modal
      mostrarInformacionCompromiso(response.data[0]);

      // Mostrar el modal
      $("#modalCompromisoObservaciones").modal("show");
    },
  });
}


function mostrarInformacionCompromiso(compromiso) {
  let compromisoActualId = compromiso.id;
  let municipioCodigo = compromiso.tbl_municipio_id;
  let secretariaIdObs = compromiso.tbl_secretarias_id;

  let estadoParaAutorizar = "";
  if (compromiso.estado_autorizar == "Sin Cumplir") {
    estadoParaAutorizar = "En Trámite";
  }
  if (compromiso.estado_autorizar == "En Trámite") {
    estadoParaAutorizar = "Cumplido";
  }

  $("#observacionCompromiso").val("");
  $("#idCompromisoGuardarObser").val(compromisoActualId);
  $("#municipioCodigo").val(municipioCodigo);
  $("#secretariaIdObs").val(secretariaIdObs);
  $("#estadoParaAprobar").val(estadoParaAutorizar);

  let infoHTML = `
    <div class="card mb-3" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);">
      <div class="card-header" style="background:rgba(255,255,255,.08);border-bottom:1px solid rgba(255,255,255,.10);">
        <h6 class="mb-0" style="color:var(--w95, rgba(255,255,255,.95));"><i class="fas fa-info-circle"></i> Información del Compromiso</h6>
      </div>
      <div class="card-body" style="color:var(--w90, rgba(255,255,255,.9));">
        <div class="row">
          <div class="col-md-6">
            <p style="color:var(--w90, rgba(255,255,255,.9));"><strong>Secretaría:</strong> ${compromiso.secretaria || 'N/A'}</p>
            <p style="color:var(--w90, rgba(255,255,255,.9));"><strong>Municipio:</strong> ${compromiso.municipio || 'N/A'}</p>
            <p style="color:var(--w90, rgba(255,255,255,.9));"><strong>Componente:</strong> ${compromiso.componente || 'N/A'}</p>
            <p style="color:var(--w90, rgba(255,255,255,.9));"><strong>Estado a validar:</strong> <span class="badge badge-${getEstadoBadgeClass(estadoParaAutorizar)}">${estadoParaAutorizar || 'N/A'}</span></p>
          </div>
          <div class="col-md-6">
            <p style="color:var(--w90, rgba(255,255,255,.9));"><strong>Compromiso:</strong></p>
            <div class="border p-2 rounded" style="max-height:100px;overflow-y:auto;background:rgba(10,17,33,.55);color:var(--w90,rgba(255,255,255,.9));">
              ${compromiso.compromisos || 'N/A'}
            </div>
            <p class="mt-2" style="color:var(--w90, rgba(255,255,255,.9));"><strong>Compromiso Pactado:</strong></p>
            <div class="border p-2 rounded" style="max-height:100px;overflow-y:auto;background:rgba(10,17,33,.55);color:var(--w90,rgba(255,255,255,.9));">
              ${compromiso.compromisopac || 'N/A'}
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  $("#contenidoCompromiso").html(infoHTML);
}

// Función para obtener la clase del badge según el estado
function getEstadoBadgeClass(estado) {
  switch (estado) {
    case 'En Espera':
      return 'warning';
    case 'Aprobado':
      return 'success';
    case 'Rechazado':
      return 'danger';
    case 'Pendiente':
      return 'info';
    default:
      return 'secondary';
  }
}


// Función para guardar la observación
function guardarObservacion() {
  let observacion = $("#observacionCompromiso").val();
  let compromisoActualId = $("#idCompromisoGuardarObser").val();
  let municipio = $("#municipioCodigo").val();
  let secretariaIdObs = $("#secretariaIdObs").val();
  let aprobacion = $("#aprobacion").val();
  let estadoParaAprobar = $("#estadoParaAprobar").val();
  if (!observacion) {
    Swal.fire({
      icon: 'warning',
      title: 'Campo requerido',
      text: 'Por favor ingrese una observación antes de guardar.'
    });
    return;
  }

  if (!compromisoActualId) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se ha identificado el compromiso.'
    });
    return;
  }

  if (!municipio) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se ha identificado el municipio.'
    });
    return;
  }

  if (!secretariaIdObs) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se ha identificado la secretaría.'
    });
    return;
  }


  const data = {
    'id': compromisoActualId,
    'observacion': observacion,
    'aprobacion': aprobacion,
    'estadoParaAprobar': estadoParaAprobar,
  };

  $.ajax({
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      method: "aprobarCompromiso",
      data: data,
    }),
    dataType: "json",
    success: function (response) {
      if (response.output?.valid) {
        Swal.fire({
          icon: "success",
          title: "¡Actualizado!",
          text: response.output?.response || "Compromiso actualizado correctamente.",
          confirmButtonColor: "#28a745",
        });
        cargarCompromiso();
        $("#modalCompromisoObservaciones").modal("hide");
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al actualizar",
          text: response.output?.response || response.message || "Ocurrió un problema al actualizar.",
          confirmButtonColor: "#d33",
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de red",
        text: "No se pudo comunicar con el servidor.",
        confirmButtonColor: "#d33",
      });
    },
  });
}

// Ver compromiso en modal
function verCompromiso(data) {
  $("#contenidoCompromiso").text(data);
  $("#modalCompromiso").modal("show");
}

// Ver imagen o PDF adjunto
function mostrarAdjuntosModal(imagenSrc, pdfSrc) {
  let html = "";

  if (imagenSrc) {
    html = `<img src="${imagenSrc}" class="img-fluid" style="max-height: 80vh;">`;
  } else if (pdfSrc) {
    html = `<iframe src="${pdfSrc}" width="100%" height="600px" frameborder="0"></iframe>`;
  } else {
    html = `<p class="text-muted">No hay archivo para mostrar.</p>`;
  }

  $("#contenidoAdjunto").html(html);
  $("#modalAdjunto").modal("show");
}

function ciudades() {
  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({ method: "ciudades", data: 68 }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#municipio");
        $select.empty();
        $select.append('<option value="">Seleccione</option>');


        const $selectMunicipioFiltro = $("#municipioFiltro");
        $selectMunicipioFiltro.empty();
        $selectMunicipioFiltro.append('<option value="" selected>Todos</option>');

        response.data.forEach(function (ciudad) {
          $select.append(
            `<option value="${ciudad.codigo_muncipio}">${ciudad.nombre_mapa}</option>`
          );
          $selectMunicipioFiltro.append(
            `<option value="${ciudad.codigo_muncipio}">${ciudad.municipio}</option>`
          );
        });
        resolve();
      },
    });
  });
}

// Función para cargar veredas según el municipio seleccionado
function cargarVeredas(municipioId) {
  return new Promise((resolve) => {
    if (!municipioId) {
      $("#veredaFiltro").empty().append('<option value="">Seleccione primero un municipio</option>');
      resolve();
      return;
    }

    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({
        method: "getVeredasByMunicipioId",
        data: { municipio_id: municipioId },
      }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#veredaFiltro");
        $select.empty();
        $select.append('<option value="">Todas</option>');

        const veredas = response.output?.response || response.data || [];
        if (veredas.length > 0) {
          veredas.forEach(function (vereda) {
            $select.append(
              `<option value="${vereda.id}">${vereda.nombre_vereda || vereda.vereda}</option>`
            );
          });
        }
        resolve();
      },
      error: function () {
        $("#veredaFiltro").empty().append('<option value="">Error al cargar veredas</option>');
        resolve();
      },
    });
  });
}

function secretarias() {
  return new Promise((resolve) => {
    $.ajax({
      url: "./admin/controllers/utilsCtrl.php",
      type: "POST",
      data: JSON.stringify({ method: "secretaria" }),
      dataType: "json",
      contentType: "application/json",
      success: function (response) {
        const $select = $("#tbl_secretarias_id");
        $select.empty();
        $select.append('<option value="">Seleccione</option>');


        const $selectFiltroTabla = $("#secretariaIdFiltro");
        $selectFiltroTabla.empty();
        $selectFiltroTabla.append('<option value="" selected>Todas</option>');


        response.data.forEach(function (ciudad) {
          $select.append(
            `<option value="${ciudad.id}">${ciudad.secretaria}</option>`
          );
          $selectFiltroTabla.append(
            `<option value="${ciudad.id}">${ciudad.secretaria}</option>`
          );
        });
        resolve();
      },
    });
  });
}

function editaCompromiso(data) {
  $.ajax({
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    data: JSON.stringify({ method: "getCompromisoId", data: data }),
    dataType: "json",
    contentType: "application/json",
    success: async function (response) {
      console.log(response);
      const compromisoId = response.data[0].id;
      $("#modalEditVisita").remove();

      let accionesTomadasHTML = "";
      if (
        response.data[0].respuesta &&
        response.data[0].respuesta.trim() !== ""
      ) {
        accionesTomadasHTML = `
                              <div class="form-group col-md-4">
                                <label for="acciones_tomadas">Acciones Tomadas</label>
                                <textarea class="form-control" id="acciones_tomadas" rows="3">${response.data[0].compromisos || ""
          }</textarea>
                              </div>
                            `;
      }
      const mostrarBotonActualizar = response.data[0].estado !== "Cumplido";

      const modalHTML = `
        <div class="modal fade" id="modalEditVisita" tabindex="-1" role="dialog" aria-labelledby="modalEditVisitaLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content shadow">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditVisitaLabel">Editar Compromiso</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

              <div class="modal-body" style="padding:20px">
                <form id="formEditVisita">
                  <div class="form-row">
                    <input type="hidden" id="id" value="${response.data[0].id || ""
        }">

                    <div class="form-group col-md-4">
                      <label for="fecha">Fecha</label>
                      <input type="date" class="form-control" id="fecha" readonly value="${response.data[0].date || ""
        }">
                    </div>

                    <div class="form-group col-md-4">
                      <label for="provincia">Provincia <span class="text-danger">*</span></label>
                      <select class="form-control" id="provincia" name="provincia">
                        <option value="Seleccione">Seleccione</option>
                        <option value="Soto Norte">Soto Norte</option>
                        <option value="Guanentá">Guanentá</option>
                        <option value="García Rovira">García Rovira</option>
                        <option value="Comunera">Comunera</option>
                        <option value="Vélez">Vélez</option>
                        <option value="Metropolitana">Metropolitana</option>
                        <option value="Yariguíes">Yariguíes</option>
                      </select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="municipio">Municipio</label>
                      <select class="form-control" id="municipio" name="municipio"></select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="tbl_secretarias_id">Secretaría</label>
                      <select class="form-control" id="tbl_secretarias_id" name="tbl_secretarias_id"></select>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="compromisos">Compromiso pactado</label>
                      <textarea class="form-control" id="compromisos" rows="3">${response.data[0].compromisopac || ""
        }</textarea>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="respuesta">Respuesta</label>
                      <textarea class="form-control" id="respuesta" rows="3">${response.data[0].respuesta || ""
        }</textarea>
                    </div>

                    ${accionesTomadasHTML}

                    <div class="form-group col-md-4">
                      <label for="url">LINK (opcional)</label>
                      <input type="text" class="form-control" id="url" value="${response.data[0].url || ""
        }">
                    </div>

                    <!-- Imagen -->
                    <div class="form-group col-md-3">
                      <label for="img">Imagen</label>
                      <input type="file" class="form-control-file" id="img" accept="image/*">
                      <div id="previewImage" class="mt-2">
                        ${response.data[0].img
          ? `<img src="${response.data[0].img}" class="img-fluid mb-2" style="max-height: 200px;">`
          : `<p class="text-muted">No hay imagen disponible.</p>`
        }
                      </div>
                    </div>

                    <!-- subir Imagen -->
                    <div class="form-group col-md-3">
                      <label for="newImage">Subir imagen</label>
                      <input type="file" class="form-control-file" id="newImage" accept="image/*">
                      <div id="previewImage1" class="mt-2">
                        ${response.data[0].imagen2
          ? `<img src="${response.data[0].imagen2}" class="img-fluid mb-2" style="max-height: 200px;">`
          : `<p class="text-muted">No hay imagen disponible.</p>`
        }
                      </div>
                    </div>

                    <!-- PDF -->
                    <div class="form-group col-md-2">
                      <label for="newPdf">Subir PDF</label>
                      <input type="file" class="form-control-file" id="newPdf" accept="application/pdf">
                      <div id="previewPdf" class="mt-2">
                        ${response.data[0].pdf
          ? `<a href="${response.data[0].pdf}" target="_blank" class="btn btn-outline-primary btn-sm">Ver PDF</a>`
          : `<p class="text-muted">No hay PDF disponible.</p>`
        }
                      </div>
                    </div>

                  </div>
                </form>
              </div>

              <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                ${mostrarBotonActualizar
          ? `<button type="button" class="btn btn-primary" onclick="actualizarCompromiso(${compromisoId})">Actualizar</button>`
          : ""
        }
              </div>
            </div>
          </div>
        </div>
      `;

      $("body").append(modalHTML);
      $("#modalEditVisita").modal("show");

      // Preview de imagen
      $("#newImage").on("change", function () {
        const file = this.files[0];
        if (file && file.type.startsWith("image/")) {
          const reader = new FileReader();
          reader.onload = function (e) {
            $("#previewImage1").html(
              `<img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">`
            );
          };
          reader.readAsDataURL(file);
        } else {
          $("#previewImage1").html(
            `<p class="text-danger">Archivo no válido</p>`
          );
        }
      });

      $("#img").on("change", function () {
        const file = this.files[0];
        if (file && file.type.startsWith("image/")) {
          const reader = new FileReader();
          reader.onload = function (e) {
            $("#previewImage").html(
              `<img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">`
            );
          };
          reader.readAsDataURL(file);
        } else {
          $("#previewImage").html(
            `<p class="text-danger">Archivo no válido</p>`
          );
        }
      });

      // Preview de PDF
      $("#newPdf").on("change", function () {
        const file = this.files[0];
        if (file && file.type === "application/pdf") {
          $("#previewPdf").html(
            `<p><i class="feather icon-file-text"></i> ${file.name}</p>`
          );
        } else {
          $("#previewPdf").html(`<p class="text-danger">Archivo no válido</p>`);
        }
      });

      await ciudades();
      await secretarias();

      $("#provincia").val(response.data[0].provincia);
      $("#municipio").val(response.data[0].tbl_municipio_id);
      $("#tbl_secretarias_id").val(response.data[0].tbl_secretarias_id);
      $("#estado").val(response.data[0].estado);
    },
  });
}

function actualizarCompromiso(id) {
  const formData = new FormData();

  // Campos básicos
  formData.append("method", "actualizarCompromiso");
  formData.append("id", id);
  formData.append("fecha", $("#fecha").val());
  formData.append("provincia", $("#provincia").val());
  formData.append("municipio", $("#municipio").val());
  formData.append("secretaria", $("#tbl_secretarias_id").val());
  formData.append("compromisos", $("#compromisos").val());
  formData.append("estado", $("#estado").val());
  formData.append("respuesta", $("#respuesta").val());
  formData.append(
    "acciones_tomadas",
    $("#acciones_tomadas").length ? $("#acciones_tomadas").val() : ""
  );

  // Nuevos campos
  formData.append("url", $("#url").val());
  formData.append("img_actual", $("#img").val()); // Imagen actual guardada
  formData.append("pdf_actual", $("#pdf").val()); // PDF actual guardado

  // Archivos seleccionados (imagen y PDF)
  const newImage = $("#newImage")[0].files[0];
  if (newImage) {
    formData.append("newImage", newImage);
  }

  const newPdf = $("#newPdf")[0].files[0];
  if (newPdf) {
    formData.append("newPdf", newPdf);
  }

  $.ajax({
    url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.state) {
        Swal.fire({
          icon: "success",
          title: "¡Actualizado!",
          text: response.message || "Compromiso actualizado correctamente.",
          confirmButtonColor: "#28a745",
        });
        cargarCompromiso();
        $("#modalEditVisita").modal("hide");
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al actualizar",
          text: response.message || "Ocurrió un problema al actualizar.",
          confirmButtonColor: "#d33",
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de red",
        text: "No se pudo comunicar con el servidor.",
        confirmButtonColor: "#d33",
      });
    },
  });
}


function filtrarTablaEnEspera() {
  const municipio = document.getElementById("municipioFiltro").value;
  const secretariaId = document.getElementById("secretariaIdFiltro").value;
  const componente = document.getElementById("componenteFiltro").value;
  const vereda = document.getElementById("veredaFiltro").value;

  if ($.fn.DataTable.isDataTable("#dynamictable")) {
    $("#dynamictable").DataTable().destroy();
  }

  compromiso = $("#dynamictable").DataTable({
    dom: "lrtip2",
    processing: true,
    serverSide: true,
    responsive: true,
    destroy: true,
    columnDefs: [{ targets: ["_all"], className: "mdc-data-table__cell" }],
    ajax: {
      url: "./admin/controllers/compromisoMunicipioAlcaldeCtrl.php",
      type: "POST",
      contentType: "application/json",
      data: function (d) {
        return JSON.stringify({
          method: "getAllCompromiseFiltrosSelectEnEstadoEspera",
          data: {
            length: d.length,
            start: d.start,
            draw: d.draw,
            secretaria: secretariaId,
            componente: componente,
            municipio: municipio,
            vereda: vereda,
          },
        });
      },
    },
    columns: [
      { data: "id" },
      { data: "secretaria" },
      { data: "compromisos", visible: false, render: renderCompromisoBtn },
      { data: "consecuencia", visible: false, render: renderCompromisoBtn },
      { data: "respuesta", visible: false, render: renderCompromisoBtn },
     {
        data: "estado_autorizar", 
        render: function (data, type, row) {
          let color = "#6c757d"; 
          let texto = (data || "").toUpperCase();

          if (!data || texto.trim() === '') {
            texto = "SIN CUMPLIR"; 
            color = "#dc3545"; 
          }

          else if (texto.includes("CUMPLIDO")) color = "#28a745";
          else if (texto.includes("TRÁMITE")) color = "#ffc107";   
          else if (texto.includes("SIN CUMPLIR")) color = "#dc3545"; 

          return `<span style="
            background-color:${color};
            color:white;
            padding:5px 10px;
            border-radius:6px;
            font-weight:500;
            font-size:13px;
            display:inline-block;
            text-transform:capitalize;
          ">${data}</span>`;
        }
      },

      { data: "municipio" },
      { data: "vereda", defaultContent: "Sin vereda" },
      { data: "componente" },
      { data: "tipo_ejecucion" },
      { data: "date" },
      {
        data: "id",
        render: function (data) {
          return `<button class="btn btn-sm btn-transparent" onclick="validarCompromiso('${data}');"><i class="feather icon-edit"></i></button>`;
        },
      },
      {
        data: "id",
        render: function (data) {
          return `<form action="reporte-compromiso-alcalde.php" method="POST" style="display:inline;">
            <input type="hidden" name="reporte" value="1">
            <input type="hidden" name="id" value="${data}">
            <button type="submit" class="btn btn-sm btn-success4">
              <i class="feather icon-eye"></i>
            </button></form>`;
        },
      },
      { data: "aprobador_observacion" },
      { data: "id", render: renderTrasladoBtn }
    ],
  });
}

// Evento para el botón de guardar observación
$(document).ready(function () {
  // Evento click para guardar observación
  $(document).on('click', '#btnGuardarObservacion', function () {
    guardarObservacion();
  });

  // Limpiar campos cuando se cierre el modal
  $('#modalCompromisoObservaciones').on('hidden.bs.modal', function () {
    $("#observacionCompromiso").val("");
    $("#idCompromisoGuardarObser").val("");
    $("#municipioCodigo").val("");
    $("#secretariaIdObs").val("");
  });
});