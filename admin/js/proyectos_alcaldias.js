/**
 * Módulo para la gestión de Proyectos de Alcaldías
 *
 * Este archivo contiene todas las funciones JavaScript para el manejo
 * de proyectos registrados por las alcaldías municipales.
 *
 * @author SPIDERSOFTWARE
 * @version 1.0
 */

$(document).on("ready", init);
var q;

/**
 * Inicialización del módulo
 */
function init() {
    q = {};
}

/**
 * Objeto principal para la gestión de Proyectos de Alcaldías
 */
var PROYECTOS_ALCALDIAS = {
    /**
     * Valida los datos del formulario antes de guardar
     *
     * @return {void}
     */
    validateData: function () {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";

        // Validación de campos obligatorios
        if (
            $("#date").val() == "" ||
            $("#tbl_secretarias_id").val() == null ||
            $("#tbl_secretarias_id").val() == "" ||
            $("#proyecto").val() == null ||
            $("#proyecto").val() == "" ||
            $("#tbl_municipio_id").val() == null ||
            $("#tbl_municipio_id").val() == "" ||
            $("#valor_proyecto").val() == "" ||
            $("#valor_proyecto").val() == "0" ||
            $("#valor_proyecto").val() == null ||
            $("#date_inicio").val() == "" ||
            $("#fecha_entrega").val() == "" ||
            $("#estado").val() == ""
        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }

        // Validar que se haya seleccionado al menos una vereda
        var veredasSeleccionadas = $("#tbl_vereda_id").val();
        if (!veredasSeleccionadas || veredasSeleccionadas.length === 0) {
            UTIL.mostrarMensajeValidacion("Debe seleccionar al menos una vereda.");
            bValid = false;
            return;
        }

        // Validar que al menos un aporte sea mayor a 0
        var aporteMunicipio = parseFloat($("#aporte_municipio").val()) || 0;
        var aporteGobernacion = parseFloat($("#aporte_gobernacion").val()) || 0;
        var aporteNacion = parseFloat($("#aporte_nacion").val()) || 0;

        if (aporteMunicipio <= 0 && aporteGobernacion <= 0 && aporteNacion <= 0) {
            UTIL.mostrarMensajeValidacion("Debe ingresar al menos un aporte financiero (Municipio, Gobernación o Nación).");
            bValid = false;
            return;
        }

        // Si la validación es exitosa, proceder a guardar
        if (bValid) {
            PROYECTOS_ALCALDIAS.saveInformacion();
        }
    },

    /**
     * Guarda o actualiza la información del proyecto
     *
     * @return {void}
     */
    saveInformacion: function () {
        Swal.fire({
            title: "¿Está seguro de ingresar la información?",
            text: "¿Desea continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false,
        }).then((result) => {
            if (result.value) {
                q = {};
                q.op = 'proyectosalcaldias_save';
                q.id = $("#id").val();
                q.tbl_secretarias_id = $("#tbl_secretarias_id").val();
                q.proyecto = $("#proyecto").val();
                q.date = $("#date").val();
                q.tbl_departamento_id = $("#tbl_departamento_id").val();
                q.tbl_municipio_id = $("#tbl_municipio_id").val();
                q.tbl_vereda_id = $("#tbl_vereda_id").val();
                q.valor_proyecto = $("#valor_proyecto").val();
                q.contratista = $("#contratista").val();
                q.nit = $("#nit").val();
                q.interventoria = $("#interventoria").val();
                q.adicion = $("#adicion").val();
                q.date_inicio = $("#date_inicio").val();
                q.dias_prorroga = $("#dias_prorroga").val();
                q.date_prorroga = $("#date_prorroga").val();
                q.plazo_construccion = $("#plazo_construccion").val();
                q.fecha_entrega = $("#fecha_entrega").val();
                q.estado = $("#estado").val();
                q.porcentaje_ejecucion = $("#porcentaje_ejecucion").val();
                q.porcentaje_financiero = $("#porcentaje_financiero").val();
                q.observaciones = $("#observaciones").val();
                q.entidad_otros = $("#entidad_otros").val();
                q.otros_aportes = $("#otros_aportes").val();
                q.aporte_municipio = $("#aporte_municipio").val();
                q.aporte_gobernacion = $("#aporte_gobernacion").val();
                q.aporte_nacion = $("#aporte_nacion").val();
                q.valor_ejecutado = $("#valor_ejecutado").val();

                UTIL.cursorBusy();

                $.ajax({
                    data: q,
                    type: "POST",
                    dataType: "json",
                    url: "admin/ajax/rqst.php",
                    success: function (data) {
                        q = {};
                        UTIL.cursorNormal();

                        if (data.output.valid) {
                            UTIL.mostrarMensajeExitoso(
                                data.output.message || "Información guardada correctamente"
                            );
                            setTimeout(function () {
                                // Redirigir a la página de listado (ajustar según la ruta correcta)
                                window.location.reload();
                            }, 1000);
                        } else {
                            UTIL.mostrarMensajeError(data.output.response.content);
                        }
                    },
                    error: function (xhr, status, error) {
                        UTIL.cursorNormal();
                        UTIL.mostrarMensajeError("Error al comunicarse con el servidor: " + error);
                    }
                });
            }
        });
    },

    /**
     * Carga los datos de un proyecto para edición
     *
     * @param {number} id - ID del proyecto a editar
     * @return {void}
     */
    loadProyecto: function (id) {
        if (!id || id <= 0) {
            UTIL.mostrarMensajeError("ID de proyecto no válido");
            return;
        }

        q = {};
        q.op = 'proyectosalcaldias_getall';
        q.id = id;

        UTIL.cursorBusy();

        $.ajax({
            data: q,
            type: "POST",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                q = {};
                UTIL.cursorNormal();

                if (data.output.valid && data.output.response.length > 0) {
                    var proyecto = data.output.response[0];

                    // Llenar el formulario con los datos del proyecto
                    $("#id").val(proyecto.id);
                    $("#date").val(proyecto.date);
                    $("#tbl_secretarias_id").val(proyecto.tbl_secretarias_id);
                    $("#proyecto").val(proyecto.proyecto);
                    $("#tbl_departamento_id").val(proyecto.tbl_departamento_id);
                    $("#tbl_municipio_id").val(proyecto.tbl_municipio_id);
                    $("#tbl_vereda_id").val(proyecto.tbl_vereda_id);
                    $("#valor_proyecto").val(proyecto.valor_proyecto);
                    $("#contratista").val(proyecto.contratista);
                    $("#nit").val(proyecto.nit);
                    $("#interventoria").val(proyecto.interventoria);
                    $("#adicion").val(proyecto.adicion);
                    $("#date_inicio").val(proyecto.date_inicio);
                    $("#dias_prorroga").val(proyecto.dias_prorroga);
                    $("#date_prorroga").val(proyecto.date_prorroga);
                    $("#plazo_construccion").val(proyecto.plazo_construccion);
                    $("#fecha_entrega").val(proyecto.fecha_entrega);
                    $("#estado").val(proyecto.estado);
                    $("#porcentaje_ejecucion").val(proyecto.porcentaje_ejecucion);
                    $("#porcentaje_financiero").val(proyecto.porcentaje_financiero);
                    $("#observaciones").val(proyecto.observaciones);
                    $("#entidad_otros").val(proyecto.entidad_otros);
                    $("#otros_aportes").val(proyecto.otros_aportes);
                    $("#aporte_municipio").val(proyecto.aporte_municipio);
                    $("#aporte_gobernacion").val(proyecto.aporte_gobernacion);
                    $("#aporte_nacion").val(proyecto.aporte_nacion);
                    $("#valor_ejecutado").val(proyecto.valor_ejecutado);

                    // Refrescar Select2 si se está usando
                    if ($.fn.select2) {
                        $("#tbl_municipio_id").trigger('change');
                        $("#tbl_vereda_id").trigger('change');
                    }
                } else {
                    UTIL.mostrarMensajeError("No se encontró el proyecto");
                }
            },
            error: function (xhr, status, error) {
                UTIL.cursorNormal();
                UTIL.mostrarMensajeError("Error al cargar el proyecto: " + error);
            }
        });
    },

    /**
     * Elimina un proyecto después de confirmar
     *
     * @param {number} id - ID del proyecto a eliminar
     * @return {void}
     */
    deleteProyecto: function (id) {
        if (!id || id <= 0) {
            UTIL.mostrarMensajeError("ID de proyecto no válido");
            return;
        }

        Swal.fire({
            title: "¿Está seguro de eliminar este proyecto?",
            text: "Esta acción no se puede deshacer",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: '#d33',
            closeOnConfirm: false,
        }).then((result) => {
            if (result.value) {
                q = {};
                q.op = 'proyectosalcaldias_delete';
                q.id = id;

                UTIL.cursorBusy();

                $.ajax({
                    data: q,
                    type: "POST",
                    dataType: "json",
                    url: "admin/ajax/rqst.php",
                    success: function (data) {
                        q = {};
                        UTIL.cursorNormal();

                        if (data.output.valid) {
                            UTIL.mostrarMensajeExitoso(
                                data.output.message || "Proyecto eliminado correctamente"
                            );
                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);
                        } else {
                            UTIL.mostrarMensajeError(data.output.response.content);
                        }
                    },
                    error: function (xhr, status, error) {
                        UTIL.cursorNormal();
                        UTIL.mostrarMensajeError("Error al eliminar el proyecto: " + error);
                    }
                });
            }
        });
    },

    /**
     * Calcula el valor total del proyecto sumando todos los aportes
     *
     * @return {void}
     */
    calcularValorTotal: function () {
        var aporteMunicipio = parseFloat($("#aporte_municipio").val()) || 0;
        var aporteGobernacion = parseFloat($("#aporte_gobernacion").val()) || 0;
        var aporteNacion = parseFloat($("#aporte_nacion").val()) || 0;
        var otrosAportes = parseFloat($("#otros_aportes").val()) || 0;

        var total = aporteMunicipio + aporteGobernacion + aporteNacion + otrosAportes;

        $("#valor_proyecto").val(total.toFixed(2));
    },

    /**
     * Obtiene las veredas basándose en el municipio seleccionado en el formulario
     * (Llamada desde el onChange del select de municipio)
     *
     * @return {void}
     */
    getVeredasByMunicipioId: function () {
        var municipioId = $("#tbl_municipio_id").val();

        if (!municipioId || municipioId === "" || municipioId === "seleccione") {
            $("#tbl_vereda_id").html('<option value="">Seleccione una o varias veredas</option>');
            return;
        }

        q = {};
        q.op = 'veredaget';
        q.municipio_id = municipioId;

        UTIL.cursorBusy();

        $.ajax({
            data: q,
            type: "POST",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                q = {};
                UTIL.cursorNormal();

                if (data.output.valid) {
                    var options = '<option value="">Seleccione una o varias veredas</option>';

                    data.output.response.forEach(function (vereda) {
                        options += '<option value="' + vereda.id + '">' + vereda.nombre_vereda + '</option>';
                    });

                    $("#tbl_vereda_id").html(options);

                    // Refrescar Select2 si se está usando
                    if ($.fn.select2) {
                        $("#tbl_vereda_id").trigger('change');
                    }
                } else {
                    $("#tbl_vereda_id").html('<option value="">No hay veredas disponibles</option>');
                }
            },
            error: function (xhr, status, error) {
                UTIL.cursorNormal();
                console.error("Error al cargar veredas: " + error);
                $("#tbl_vereda_id").html('<option value="">Error al cargar veredas</option>');
            }
        });
    },

    /**
     * Obtiene las veredas de un municipio específico
     *
     * @param {number} municipioId - ID del municipio
     * @return {void}
     */
    getVeredasByMunicipio: function (municipioId) {
        if (!municipioId || municipioId <= 0) {
            $("#tbl_vereda_id").html('<option value="">Seleccione vereda</option>');
            return;
        }

        q = {};
        q.op = 'getveredasbymunicipioalcalde';
        q.tbl_municipio_id = municipioId;

        $.ajax({
            data: q,
            type: "POST",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                q = {};

                if (data.output.valid) {
                    var options = '<option value="">Seleccione vereda</option>';

                    data.output.response.forEach(function (vereda) {
                        options += '<option value="' + vereda.id + '">' + vereda.nombre_vereda + '</option>';
                    });

                    $("#tbl_vereda_id").html(options);

                    // Refrescar Select2 si se está usando
                    if ($.fn.select2) {
                        $("#tbl_vereda_id").trigger('change');
                    }
                } else {
                    $("#tbl_vereda_id").html('<option value="">No hay veredas disponibles</option>');
                }
            },
            error: function (xhr, status, error) {
                console.error("Error al cargar veredas: " + error);
                $("#tbl_vereda_id").html('<option value="">Error al cargar veredas</option>');
            }
        });
    },

    /**
     * Limpia el formulario
     *
     * @return {void}
     */
    limpiarFormulario: function () {
        $("#formsecretaria")[0].reset();
        $("#id").val("");

        // Refrescar Select2 si se está usando
        if ($.fn.select2) {
            $("#tbl_municipio_id").val(null).trigger('change');
            $("#tbl_vereda_id").val(null).trigger('change');
            $("#tbl_secretarias_id").val(null).trigger('change');
        }
    }
};

/**
 * Event listeners para el formulario
 */
$(document).ready(function () {
    // Calcular valor total automáticamente al cambiar aportes
    $("#aporte_municipio, #aporte_gobernacion, #aporte_nacion, #otros_aportes").on("change keyup", function () {
        PROYECTOS_ALCALDIAS.calcularValorTotal();
    });

    // Cargar veredas al cambiar municipio
    $("#tbl_municipio_id").on("change", function () {
        var municipioId = $(this).val();
        if (municipioId) {
            PROYECTOS_ALCALDIAS.getVeredasByMunicipio(municipioId);
        } else {
            $("#tbl_vereda_id").html('<option value="">Seleccione vereda</option>');
        }
    });
});
