$(document).on("ready", initingresofactores);

function initingresofactores() {
    INGRESO_FACTORES.init();
}

const INGRESO_FACTORES = {
    init: function () {
        this.setupEventHandlers();
    },

    setupEventHandlers: function () {
        $("#ejeId").on("change", this.getPilarByEjeId);
        $("#pilarId").on("change", this.getAreaByPilarId);
        $("#selectAll").on("change", this.toggleSelectAll);
    },

    toggleSelectAll: function () {
        const checked = $("#selectAll").is(":checked");
        $(".factor-checkbox").prop("checked", checked);
    },

    deletedata: function(id) {
        Swal.fire({
            title: "Va a eliminar información de forma irreversible!",
            text: "¿Desea continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "Cancelar!",
            closeOnConfirm: false,
        }).then((result) => {
            if (result.value) {
                q = {};
                q.op = "getFactoresDelete";
                q.id = id;
                UTIL.cursorBusy();
                $.ajax({
                    data: q,
                    type: "POST",
                    dataType: "json",
                    url: "admin/ajax/rqst.php",
                    success: function(data) {
                        q = {};
                        UTIL.cursorNormal();
                        if (data.output.valid) {
                            UTIL.mostrarMensajeExitoso('Información eliminada correctamente');
                            setTimeout(function() {
                                window.location = 'ingreso_factores.php';
                            }, 1000);
                        } else {
                            swal("warning", data.output.response.content, "error");
                        }
                    },
                });
            }
        });
    },
    edit: function (id) {
        q = {};
        q.op = "getFactores";
        q.id = id;
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
                    let res = data.output.response[0];
                    $("#ejeId").val(res.tbl_eje_id);
                    INGRESO_FACTORES.getPilarByEjeId();
                    $("#id").val(res.id);
                    $("#tipo").val(res.tipo);
                    $("#tipo_medicion").val(res.tipo_medicion);
                    $("#puntaje").val(res.puntaje);

                    setTimeout(function () {
                        $("#pilarId").val(res.tec_pilar_id);
                        INGRESO_FACTORES.getAreaByPilarId();
                    }, 1000);

                    setTimeout(function () {
                        $("#areaId").val(res.tec_area_id);
                        $("#tbl_factor_inestabilidad_id").val(res.tbl_factor_inestabilidad_id || '');
                        $("#tbl_secretaria_id").val(res.tbl_secretaria_id || '');
                    }, 1500);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },

    getPilarByEjeId: function () {
        const ejeId = $("#ejeId").val();
        const $pilarId = $("#pilarId");
        const $areaId = $("#areaId");

        if (ejeId <= 0 || ejeId === "seleccione") {
            $pilarId.empty().prop("disabled", true);
            $areaId.empty().prop("disabled", true);
            return;
        }

        UTIL.cursorBusy();
        $.ajax({
            url: "admin/ajax/rqst.php",
            type: "POST",
            data: { op: "getPilar", ejeId },
            dataType: "json",
            success: function (response) {
                UTIL.cursorNormal();
                $pilarId.empty();

                if (response.output.valid && response.output.response.length > 0) {
                    const defaultOption = `<option value="">Seleccione...</option>`;
                    const options = response.output.response
                        .map(item => `<option value="${item.id}">${item.nombre}</option>`)
                        .join("");
                    $pilarId.html(defaultOption + options).prop("disabled", false);
                    $areaId.empty().prop("disabled", true);
                } else {
                    $pilarId.html('<option value="">Seleccione...</option>').prop("disabled", true);
                    $areaId.empty().prop("disabled", true);
                }
            },
            error: function () {
                UTIL.cursorNormal();
                $pilarId.empty().prop("disabled", true);
                $areaId.empty().prop("disabled", true);
            },
        });
    },

    getAreaByPilarId: function () {
        const pilarId = $("#pilarId").val();
        const $areaId = $("#areaId");

        if (pilarId <= 0 || pilarId === "seleccione") {
            $areaId.empty().prop("disabled", true);
            return;
        }

        UTIL.cursorBusy();
        $.ajax({
            url: "admin/ajax/rqst.php",
            type: "POST",
            data: { op: "getArea", pilarId },
            dataType: "json",
            success: function (response) {
                UTIL.cursorNormal();
                $areaId.empty();

                if (response.output.valid && response.output.response.length > 0) {
                    const options = response.output.response
                        .map(item => `<option value="${item.id}">${item.nombre}</option>`)
                        .join("");
                    $areaId.append(options).prop("disabled", false);
                } else {
                    $areaId.prop("disabled", true);
                }
            },
            error: function () {
                UTIL.cursorNormal();
                $areaId.empty().prop("disabled", true);
            },
        });
    },
    save() {
        const msj = "Falta ingresar información obligatoria, marcada con asterisco.";

        // Validar campos obligatorios
        const camposRequeridos = ["#ejeId", "#areaId", "#pilarId", "#tipo", "#tipo_medicion"];
        if (!this.validarCampos(camposRequeridos)) {
            UTIL.mostrarMensajeValidacion(msj);
            return;
        }
        const ifm = $("#ifm1").attr("data-url") || null;

        // Crear objeto con datos
        const datos = {
            op: "factoressave",
            id: $("#id").val(),
            ejeId: $("#ejeId").val(),
            pilarId: $("#pilarId").val(),
            areaId: $("#areaId").val(),
            tipo: $("#tipo").val(),
            tipo_medicion: $("#tipo_medicion").val(),
            puntaje: $("#puntaje").val(),
            icono: ifm,
            tbl_factor_inestabilidad_id: $("#tbl_factor_inestabilidad_id").val(),
            tbl_secretaria_id: $("#tbl_secretaria_id").val()
        };

        // Llamada AJAX
        UTIL.callAjaxRqstPOST(datos, INGRESO_FACTORES.savehandler);
    },

    savehandler(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            UTIL.clearForm('formfactores');
            UTIL.mostrarMensajeExitoso('Información guardada correctamente');
            setTimeout(() => {
                window.location = 'ingreso_factores.php';
            }, 1000);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content || 'Error al guardar la información');
        }
    },

    massUpdateInestabilidad() {
        const selected = [];
        $(".factor-checkbox:checked").each(function () {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            UTIL.mostrarMensajeValidacion("Debe seleccionar al menos un factor.");
            return;
        }

        const inestabilidadId = $("#massInestabilidadId").val();
        if (!inestabilidadId || inestabilidadId === "seleccione") {
            UTIL.mostrarMensajeValidacion("Debe seleccionar un Factor de Inestabilidad.");
            return;
        }

        Swal.fire({
            title: "Actualización masiva",
            text: "Va a asignar el Factor de Inestabilidad a " + selected.length + " factor(es). ¿Desea continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.value) {
                const datos = {
                    op: "factoresMassUpdateInestabilidad",
                    ids: selected,
                    tbl_factor_inestabilidad_id: inestabilidadId
                };
                UTIL.callAjaxRqstPOST(datos, function (data) {
                    UTIL.cursorNormal();
                    if (data.output.valid) {
                        UTIL.mostrarMensajeExitoso(data.output.updated + ' factor(es) actualizados correctamente');
                        setTimeout(() => {
                            window.location = 'ingreso_factores.php';
                        }, 1000);
                    } else {
                        UTIL.mostrarMensajeError(data.output.response.content || 'Error en la actualización masiva');
                    }
                });
            }
        });
    },

    massUpdateSecretaria() {
        const selected = [];
        $(".factor-checkbox:checked").each(function () {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            UTIL.mostrarMensajeValidacion("Debe seleccionar al menos un factor.");
            return;
        }

        const secretariaId = $("#massSecretariaId").val();
        if (!secretariaId || secretariaId === "seleccione") {
            UTIL.mostrarMensajeValidacion("Debe seleccionar una Secretaría.");
            return;
        }

        Swal.fire({
            title: "Actualización masiva",
            text: "Va a asignar la Secretaría a " + selected.length + " factor(es). ¿Desea continuar?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Si",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.value) {
                const datos = {
                    op: "factoresMassUpdateSecretaria",
                    ids: selected,
                    tbl_secretaria_id: secretariaId
                };
                UTIL.callAjaxRqstPOST(datos, function (data) {
                    UTIL.cursorNormal();
                    if (data.output.valid) {
                        UTIL.mostrarMensajeExitoso(data.output.updated + ' factor(es) actualizados correctamente');
                        setTimeout(() => {
                            window.location = 'ingreso_factores.php';
                        }, 1000);
                    } else {
                        UTIL.mostrarMensajeError(data.output.response.content || 'Error en la actualización masiva');
                    }
                });
            }
        });
    },

    // Función auxiliar para validar campos
    validarCampos(campos) {
        for (const campo of campos) {
            if ($(campo).val() === "") {
                return false;
            }
        }
        return true;
    }

};
