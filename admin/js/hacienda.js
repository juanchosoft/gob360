$(document).on('ready', initHacienda);

let queryParams;
const returnPage = 'hacienda.php';
const capacidadFinanciera = "Capacitacion Fiscal y Financiera";

function initHacienda() {
    queryParams = {};

    const accion = document.getElementById("accion");
    const municipio = document.getElementById("tbl_municipio_id");

    const campos = {
      objeto: document.querySelector(".campo-objeto"),

      tipo: document.querySelector(".campo-tipo"),
      tipoCigarrillo: document.querySelector(".campo-tipo-cigarrillo"),
      tipoTabaco: document.querySelector(".campo-tipo-tabaco"),

      licores: document.querySelector(".campo-licores"),
      valorLicores: document.querySelector(".campo-valor-licores"),

      cigarrillos: document.querySelector(".campo-cigarrillos"),
      valorCigarrillos: document.querySelector(".campo-valor-cigarrillos"),

      tabaco: document.querySelector(".campo-tabaco"),
      valorTabaco: document.querySelector(".campo-valor-tabaco"),

      cerveza: document.querySelector(".campo-cerveza"),
      valorCerveza: document.querySelector(".campo-valor-cerveza"),

      valorNacional: document.querySelector(".campo-valor-nacional"),
      valorImportado: document.querySelector(".campo-valor-importado"),

      valorTramite: document.querySelector(".campo-valor-tramite"),
      valorRecaudo: document.querySelector(".campo-valor-recaudo"),

      valorTramiteImpuestoVehicular: document.querySelector(
        ".campo-valor-tramite-impuesto-vehicular"
      ),
      recaudoImpuestoVehicular: document.querySelector(
        ".campo-valor-recaudo-impuesto-vehicular"
      ),

      // Operativos Vehículos
      vehicularTituloOperativos:     document.querySelector(".campo-vehicular-titulo-operativos"),
      vehicularCantidadOperativos:   document.querySelector(".campo-vehicular-cantidad-operativos"),
      vehicularCantidadEmplazados:   document.querySelector(".campo-vehicular-cantidad-emplazados"),
      vehicularCantidadPlacas:       document.querySelector(".campo-vehicular-cantidad-placas"),
      vehicularCantidadCampanas:     document.querySelector(".campo-vehicular-cantidad-campanas"),

      valorEstampilla: document.querySelector(".campo-valor-estampilla"),
      estampilla: document.querySelector(".campo-estampilla"),

      cantidadPersonas: document.querySelector(".campo-cantidad-personas"),

      // GAO
      cantidadAprehendida: document.querySelector(
        ".campo-cantidad-aprehendida"
      ),
      avaluoComercial: document.querySelector(".campo-avaluo-comercial"),

      cantidadVistasAlMunicipio: document.querySelector(
        ".campo-cantidad-visitas-al-municipio"
      ),

      // Capacitaciones GOA
      tipoCapacitacionGOA: document.querySelector(
        ".campo-tipo-capacitaciones-goa"
      ),
      numeroAsistentes: document.querySelector(".campo-cantidad-asistentes"),

      // GOA Jurídico
      goaJuridicoTituloCustodia:        document.querySelector(".campo-goa-juridico-titulo-custodia"),
      goaJuridicoCustodiaValorTotal:    document.querySelector(".campo-goa-juridico-custodia-valor-total"),
      goaJuridicoCustodiaCantProcesos:  document.querySelector(".campo-goa-juridico-custodia-cantidad-procesos"),
      goaJuridicoCustodiaCantUnidades:  document.querySelector(".campo-goa-juridico-custodia-cantidad-unidades"),
      goaJuridicoTituloDestruccion:     document.querySelector(".campo-goa-juridico-titulo-destruccion"),
      goaJuridicoDestruccionCantUnid:   document.querySelector(".campo-goa-juridico-destruccion-cantidad-unidades"),
      goaJuridicoDestruccionValorTotal: document.querySelector(".campo-goa-juridico-destruccion-valor-total"),

      direccion: document.querySelector(".campo-direccion"),
      barrio: document.querySelector(".campo-barrio"),
      administrador: document.querySelector(".campo-administrador"),
      nombreEstablecimiento: document.querySelector(".campo-nombre-establecimiento"),
      establecimiento: document.querySelector(".campo-establecimiento"),
      tipoEstablecimiento: document.querySelector(".campo-tipo-establecimiento"),
    };

    // Cambiar multiselección según la opción
    accion.addEventListener("change", function () {
        if (accion.value === capacidadFinanciera) {
            municipio.setAttribute("multiple", "multiple");
            municipio.name = "tbl_municipio_id[]";
        } else {
            municipio.removeAttribute("multiple");
            municipio.name = "tbl_municipio_id";
        }
        actualizarVisibilidad();
        updateOptionColors();
    });

    function mostrar(...keys) {
        keys.forEach(k => campos[k]?.classList.remove("d-none"));
    }

    function actualizarVisibilidad() {
        const v = accion.value;
        Object.values(campos).forEach(campo => campo?.classList.add("d-none"));
        $("#divMunicipio").show();

        if (v === capacidadFinanciera) {
            mostrar("cantidadPersonas");
        } else if (v === "Operativos Contrabando licores") {
            $("#TipoTexto").text("Tipo Licor");
            mostrar("tipo", "licores", "valorLicores");
        } else if (v === "Operativos Contrabando cigarrillos") {
            mostrar("tipoCigarrillo", "tipoTabaco", "cigarrillos", "valorCigarrillos", "tabaco", "valorTabaco");
        } else if (v === "Operativos Contrabando cerveza") {
            $("#TipoTexto").text("Tipo Cerveza");
            mostrar("tipo", "cerveza", "valorCerveza");
        } else if (v === "Recaudo del impuesto al consumo") {
            mostrar("valorNacional", "valorImportado");
        } else if (v === "Recaudo del impuesto de registro") {
            mostrar("valorTramite", "valorRecaudo");
        } else if (v === "Impuesto Vehicular Recaudado") {
            $("#divMunicipio").hide();
            mostrar("valorTramiteImpuestoVehicular", "recaudoImpuestoVehicular",
                    "vehicularTituloOperativos", "vehicularCantidadOperativos",
                    "vehicularCantidadEmplazados", "vehicularCantidadPlacas",
                    "vehicularCantidadCampanas");
        } else if (v === "Impuesto Estampillas Recaudado") {
            $("#divMunicipio").hide();
            mostrar("valorEstampilla", "estampilla");
        } else if (
            v === "GOA Aprehensiones de Licores" ||
            v === "GOA Aprehensión de Cigarrillos" ||
            v === "GOA Aprehensión de Cervezas" ||
            v === "GOA Aprehensión de Tabaco y Otros"
        ) {
            mostrar("cantidadAprehendida", "avaluoComercial", "direccion", "barrio", "administrador", "nombreEstablecimiento", "establecimiento", "tipoEstablecimiento");
        } else if (v === "Registro de Visitas a Establecimientos Comerciales") {
            mostrar("cantidadVistasAlMunicipio", "direccion", "barrio", "administrador", "nombreEstablecimiento", "establecimiento", "tipoEstablecimiento");
        } else if (v === "Registro de Capacitaciones del GOA") {
            mostrar("tipoCapacitacionGOA", "numeroAsistentes");
        } else if (v === "GOA Juridico") {
            mostrar("goaJuridicoTituloCustodia", "goaJuridicoCustodiaValorTotal", "goaJuridicoCustodiaCantProcesos", "goaJuridicoCustodiaCantUnidades", "goaJuridicoTituloDestruccion", "goaJuridicoDestruccionCantUnid", "goaJuridicoDestruccionValorTotal");
        }
    }

    function updateOptionColors() {
        const selectedOptions = municipio.selectedOptions;
        const colors = ['#FFD700', '#FF4500', '#32CD32', '#1E90FF', '#FF1493'];

        Array.from(selectedOptions).forEach((option, index) => {
            if (index < colors.length) {
                option.style.backgroundColor = colors[index];
                option.style.color = '#FFFFFF';
            } else {
                option.style.backgroundColor = '#808080';
                option.style.color = '#FFFFFF';
            }
        });

        Array.from(municipio.options).forEach(option => {
            if (!option.selected) {
                option.style.backgroundColor = '';
                option.style.color = '';
            }
        });
    }

    municipio.addEventListener('mousedown', function (e) {
        if (accion.value !== capacidadFinanciera) return;
        e.preventDefault();
        const option = e.target;
        if (option.tagName === 'OPTION') {
            option.selected = !option.selected;
            updateOptionColors();
        }
    });

    document.querySelectorAll('.campo-valor').forEach(input => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\./g, '').replace(/[^0-9]/g, '');
            if (!value) {
                e.target.value = '';
                return;
            }
            e.target.value = new Intl.NumberFormat('es-CO').format(value);
        });

        input.form?.addEventListener('submit', function () {
            input.value = input.value.replace(/\./g, '');
        });
    });

    actualizarVisibilidad();
    updateOptionColors();
}


const HACIENDA = {
    saveData: function () {
        Swal.fire({
            title: '¿Estás seguro de ingresar la información?',
            showCancelButton: true,
            confirmButtonText: 'Sí',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelectorAll('.campo-valor').forEach(input => {
                    input.value = input.value.replace(/\./g, '').replace(/,/g, '');
                });

                const iframe1 = $("#ifm1").attr("data-url") || null;
                const q = {
                  op: "hacienda_save",
                  tbl_municipio_id: $("#tbl_municipio_id").val(),
                  date: $("#date").val(),
                  provincia: $("#provincia").val(),
                  tbl_departamento_id: $("#tbl_departamento_id").val(),
                  secretaria: $("#secretaria").val(),
                  objeto: $("#objeto").val(),
                  accion: $("#accion").val(),
                  cantidad: $("#cantidad").val(),

                  tipo: $("#tipo").val(), // Tipo Nacional o Extrajero (Licores, Cerveza)
                  tipo_cigarrillo: $("#tipo_cigarrillo").val(), // Tipo Nacional o Extrajero (Cigarrillos)
                  tipo_tabaco: $("#tipo_tabaco").val(), // Tipo Nacional o Extrajero (Tabaco)

                  // Licores
                  incautacion_licores: $("#incautacion_licores").val(),
                  valor_licores: $("#valor_licores").val(),

                  // Cigarrillos
                  incautacion_cigarrillos: $("#incautacion_cigarrillos").val(),
                  valor_cigarrillos: $("#valor_cigarrillos").val(),

                  //Tabaco
                  incautacion_tabaco: $("#incautacion_tabaco").val(),
                  valor_tabaco: $("#valor_tabaco").val(),

                  // Cerveza
                  incautacion_cerveza: $("#incautacion_cerveza").val(),
                  valor_cerveza: $("#valor_cerveza").val(),

                  // Recaudo del impuesto al consumo
                  valor_nacional: $("#valor_nacional").val(),
                  valor_importado: $("#valor_importado").val(),

                  //Recaudo del impuesto de registro
                  valor_tramite: $("#valor_tramite").val(),
                  valor_recaudo: $("#valor_recaudo").val(),

                  // Recaudo del impuesto vehicular
                  valor_tramite_impuesto_vehicular: $(
                    "#valor_tramite_impuesto_vehicular"
                  ).val(),
                  valor_recaudo_impuesto_vehicular: $(
                    "#valor_recaudo_impuesto_vehicular"
                  ).val(),

                  // Operativos Vehículos
                  vehicular_cantidad_operativos:              $("#vehicular_cantidad_operativos").val(),
                  vehicular_cantidad_emplazados:              $("#vehicular_cantidad_emplazados").val(),
                  vehicular_cantidad_placas_consultadas:      $("#vehicular_cantidad_placas_consultadas").val(),
                  vehicular_cantidad_campanas_sensibilizacion:$("#vehicular_cantidad_campanas_sensibilizacion").val(),

                  // Impuesto estampillas recaudado
                  estampilla: $("#estampilla").val(),
                  valor_estampilla: $("#valor_estampilla").val(),

                  // Capacitación Fiscal y Financiera
                  cantidad_personas: $("#cantidad_personas").val(),

                  // Cantidad Aprehendida y Avalúo Comercial GAO
                  cantidad_aprehendida: $("#cantidad_aprehendida").val(),
                  avaluo_comercial: $("#avaluo_comercial").val(),

                  // Registro de Visitas a Establecimientos Comerciales
                  cantidad_visitas_al_municipio: $(
                    "#cantidad_visitas_al_municipio"
                  ).val(),

                  // Capacitaciones del GAO
                  tipo_capacitacion_goa: $("#tipo_capacitacion_goa").val(),
                  numero_asistentes: $("#numero_asistentes").val(),

                  // GOA Jurídico
                  goa_juridico_custodia_valor_total:         $("#goa_juridico_custodia_valor_total").val(),
                  goa_juridico_custodia_cantidad_procesos:   $("#goa_juridico_custodia_cantidad_procesos").val(),
                  goa_juridico_custodia_cantidad_unidades:   $("#goa_juridico_custodia_cantidad_unidades").val(),
                  goa_juridico_destruccion_cantidad_unidades:$("#goa_juridico_destruccion_cantidad_unidades").val(),
                  goa_juridico_destruccion_valor_total:      $("#goa_juridico_destruccion_valor_total").val(),

                  observaciones: $("#observaciones").val(),
                  valor_total: $("#valor_total").val(),

                  direccion: $("#direccion").val(),
                  barrio: $("#barrio").val(),
                  administrador: $("#administrador").val(),
                  nombre_establecimiento: $("#nombre_establecimiento").val(),
                  tipoe: $("#tipoe").val(),
                  establecimiento: $("#establecimiento").val(),

                  foto: iframe1,
                };

                UTIL.cursorBusy();

                $.ajax({
                    data: q,
                    type: "POST",
                    dataType: "json",
                    url: "admin/ajax/rqst.php",
                    success: function (data) {
                        UTIL.cursorNormal();

                        if (data.output.valid) {
                            UTIL.mostrarMensajeExitoso("Información guardada correctamente");
                            setTimeout(function () {
                                window.location = returnPage;
                            }, 1000);
                        } else {
                            UTIL.mostrarMensajeError(data.output.response.content);
                        }
                    },
                    error: function (data) {
                        UTIL.cursorNormal();
                        UTIL.mostrarMensajeError(data.output.response.content);
                    }
                });
            }
        });
    },

    getConsolidadoHacienda: function () {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            data: {
                op: 'hacienda_get_consolidado',
                accion: $("#accion").val()
            },
            success: function (data) {
                if (data.output.valid) {
                    const result = data.output.response;
                    // Limpiar el contenedor antes de mostrar la nueva tabla
                    $("#consolidadoHacienda").empty();

                    const accionSeleccionada = $("#accion").val();
                    const accionesPermitidas = [
                        "Impuesto Vehicular Recaudado",
                        "Recaudo del impuesto al consumo",
                        "Recaudo del impuesto de registro",
                        "Impuesto Estampillas Recaudado"
                    ];

                    if (!accionesPermitidas.includes(accionSeleccionada)) {
                        $("#consolidadoHacienda").empty();
                        return;
                    }

                    // Si no hay datos, mostrar mensaje
                    if (!result || result.length === 0) {
                        $("#consolidadoHacienda").html('<div class="alert alert-info">No hay información disponible.</div>');
                        return;
                    }

                    // Filtrar los resultados según la acción seleccionada
                    const filtrados = accionSeleccionada
                        ? result.filter(item => item.accion === accionSeleccionada)
                        : result;

                    const labels = {
                        'Impuesto Vehicular Recaudado': { label1: 'Cantidad De Trámites', label2: 'Valor Recaudo', label3: 'Última Fecha Actualización' },
                        'Recaudo del impuesto al consumo': { label1: 'Valor Importado', label2: 'Valor Nacional', label3: 'Última Fecha Actualización' },
                        'Recaudo del impuesto de registro': { label1: 'Cantidad De Trámites', label2: 'Valor Recaudo', label3: 'Última Fecha Actualización' },
                        'Impuesto Estampillas Recaudado': { label1: 'Valor', label2: '', label3: 'Última Fecha Actualización' },
                    };

                    let html = `
                        <div class="d-flex justify-content-center mt-4">
                            <table class="table table-bordered table-striped align-middle shadow-sm" style="background:#f1c40f; border-radius:8px; overflow:hidden; max-width: 900px;">
                                <thead style="background-color: #234162;">
                                    <tr>
                                        <th style="color: white !important;">Campo 1</th>
                                        <th style="color: white !important;">Campo 2</th>
                                        <th style="color: white !important;">Campo 3</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;


                    filtrados.forEach(item => {
                        let campo1 = '';
                        let campo2 = '';
                        let campo3 = '';
                        let dataLabel = labels[item.accion];

                        if (!dataLabel) return; // Solo mostrar los necesarios

                        switch (item.accion) {
                            case 'Recaudo del impuesto al consumo':
                                campo1 = `<strong>${dataLabel.label1}:</strong> $${Number(item.total_valor_importado).toLocaleString()}`;
                                campo2 = `<strong>${dataLabel.label2}:</strong> $${Number(item.total_valor_nacional).toLocaleString()}`;
                                campo3 = `<strong>${dataLabel.label3}:</strong> ${(item.ultima_fecha_registro)}`;
                                break;
                            case 'Recaudo del impuesto de registro':
                                campo1 = `<strong>${dataLabel.label1}:</strong> ${Number(item.cantidad_tramite_impuesto_registro).toLocaleString()}`;
                                campo2 = `<strong>${dataLabel.label2}:</strong> $${Number(item.total_valor_recaudo_impuesto_registro).toLocaleString()}`;
                                campo3 = `<strong>${dataLabel.label3}:</strong> ${(item.ultima_fecha_registro)}`;
                                break;
                            case 'Impuesto Vehicular Recaudado':
                                campo1 = `<strong>${dataLabel.label1}:</strong> ${Number(item.total_valor_tramite_impuesto_vehicular).toLocaleString()}`;
                                campo2 = `<strong>${dataLabel.label2}:</strong> $${Number(item.total_valor_recaudo_impuesto_vehicular).toLocaleString()}`;
                                campo3 = `<strong>${dataLabel.label3}:</strong> ${(item.ultima_fecha_registro)}`;
                                break;
                            case 'Impuesto Estampillas Recaudado':
                                campo1 = `<strong>${dataLabel.label2}</strong> ${item.estampilla}`;
                                campo2 = `<strong>${dataLabel.label1}:</strong> $${Number(item.total_valor_estampilla).toLocaleString()}`;
                                campo3 = `<strong>${dataLabel.label3}:</strong> ${(item.ultima_fecha_registro)}`;
                                break;
                            default:
                                campo1 = '';
                                campo2 = '';
                                campo3 = '';
                        }

                        html += `
                                <tr>
                                    <td>${campo1}</td>
                                    <td>${campo2}</td>
                                    ${campo3 ? `<td>${campo3}</td>` : '<td></td>'}
                                </tr>
                            `;
                    });

                    html += `
                                    </tbody>
                                </table>
                            </div>
                        `;

                    $("#consolidadoHacienda").html(html);
                } else {
                    $("#consolidadoHacienda").empty();
                    //UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
            error: function (data) {
                $("#consolidadoHacienda").empty();
                //UTIL.mostrarMensajeError(data.output.response.content);
            }
        });
    },
    clearForm: function () {
        // Limpia todos los inputs excepto los de tipo hidden
        $('form :input').not('[type="hidden"]').each(function () {
            if ($(this).is('input') || $(this).is('textarea') || $(this).is('select')) {
                $(this).val('');
            }
        });
    }
};
