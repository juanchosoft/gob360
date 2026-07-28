$(document).on("ready", init);
var q;
var socData;
var econoData;
var armData;

//Initialize Select2 Elements
$('.select2').select2();
$("#tbl_vereda_id").select2({
    multiple: true,
});

function init() {
    q = {};
}
var INFO = {
    getFactSociales: function() {
        q = {};
        q.op = "socialget";
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
                    var ava = data.output.response;
                    $("#social").modal();
                    var chks = "";
                    for (var i in ava) {
                        var nombre = $("#formTipo").val() == "crear" ? ava[i].nombre + " - " + ava[i].tipo : ' Construcción ' + ava[i].tipo;

                        chks += "<tr>";
                        chks += "<td>";
                        chks += '<div class="form-check">';
                        chks += '<label class="form-check-label">';
                        chks +=
                            '<input class="form-check-input" type="checkbox" value="' +
                            ava[i].id +
                            '" name="chkSocial" id="chkSocial' +
                            ava[i].id +
                            '">';
                        chks += '<span class="form-check-sign">';
                        chks += '<span class="check"></span>';
                        chks += "</span>";
                        chks += "</label>";
                        chks += "</div>";
                        chks += "</td>";
                        chks += "<td>" + nombre + "</td>";
                        chks += "<td>";
                        chks +=
                            '<input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="Cantidad"  name="cant_social_' +
                            ava[i].id +
                            '" id="cant_social_' +
                            ava[i].id +
                            '">';
                        chks += "</td>";
                        chks += "</tr>";
                    }
                    $("#factoresSociales").empty();
                    $("#factoresSociales").append(chks);

                    //Validamos si tenemos elementos preseleccionados
                    var ass = socData;
                    $("#formsocial :input").each(function() {
                        var p = $(this).attr("id");
                        for (var j in ass) {
                            var idchk = "chkSocial" + ass[j].id;
                            if (p == idchk) {
                                $(this).attr("checked", "true");
                                $("#cant_social_" + ass[j].id).val(ass[j].cantidad);
                            }
                        }
                    });
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    getFactEconomicos: function() {
        q = {};
        q.op = "economicoget";
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
                    var ava = data.output.response;
                    $("#economica").modal();
                    var chks = "";
                    for (var i in ava) {
                        var nombre = $("#formTipo").val() == "crear" ? ava[i].nombre + " - " + ava[i].tipo : ' Destruidos ' + ava[i].tipo;

                        chks += "<tr>";
                        chks += "<td>";
                        chks += '<div class="form-check">';
                        chks += '<label class="form-check-label">';
                        chks +=
                            '<input class="form-check-input" type="checkbox" value="' +
                            ava[i].id +
                            '" name="chkEcon' +
                            ava[i].id +
                            '" id="chkEcon' +
                            ava[i].id +
                            '">';
                        chks += '<span class="form-check-sign">';
                        chks += '<span class="check"></span>';
                        chks += "</span>";
                        chks += "</label>";
                        chks += "</div>";
                        chks += "</td>";
                        chks += "<td>" + nombre + "</td>";
                        chks += "<td>";
                        chks +=
                            '<input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="Cantidad"  name="cant_econom_' +
                            ava[i].id +
                            '" id="cant_econom_' +
                            ava[i].id +
                            '">';
                        chks += "</td>";
                        chks += "</tr>";
                    }
                    $("#factoresEconomicos").empty();
                    $("#factoresEconomicos").append(chks);

                    //Validamos si tenemos elementos preseleccionados
                    var ass = econoData;
                    $("#formeconomica :input").each(function() {
                        var p = $(this).attr("id");
                        for (var j in ass) {
                            var idchk = "chkEcon" + ass[j].id;
                            if (p == idchk) {
                                $(this).attr("checked", "true");
                                $("#cant_econom_" + ass[j].id).val(ass[j].cantidad);
                            }
                        }
                    });
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    getFacArmados: function() {
        q = {};
        q.op = "armadaget";
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
                    var ava = data.output.response;
                    $("#armada").modal();
                    var chks = "";
                    for (var i in ava) {
                        chks += "<tr>";
                        chks += "<td>";
                        chks += '<div class="form-check">';
                        chks += '<label class="form-check-label">';
                        chks +=
                            '<input class="form-check-input" type="checkbox" value="' +
                            ava[i].id +
                            '" name="chkArm' +
                            ava[i].id +
                            '" id="chkArm' +
                            ava[i].id +
                            '">';
                        chks += '<span class="form-check-sign">';
                        chks += '<span class="check"></span>';
                        chks += "</span>";
                        chks += "</label>";
                        chks += "</div>";
                        chks += "</td>";
                        chks += "<td>" + ava[i].nombre + " - " + ava[i].comision + ava[i].frente + "</td>";
                        if ($("#formTipo").val() == "crear") {
                            chks += "<td>";
                            chks +=
                                '<input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="Cant"  name="cant_armado_' +
                                ava[i].id +
                                '" id="cant_armado_' +
                                ava[i].id +
                                '">';
                            chks += "</td>";
                        }

                        //Actualización
                        if ($("#formTipo").val() == "actualizar") {
                            chks += '<td><input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="Cant"  name="bajas_' + ava[i].id + '" id="bajas_' + ava[i].id + '"></td>';
                            chks += '<td><input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="Cant"  name="capturas_' + ava[i].id + '" id="capturas_' + ava[i].id + '"></td>';
                            chks += '<td><input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="Cant"  name="rat_bajas_' + ava[i].id + '" id="rat_bajas_' + ava[i].id + '"></td>';
                            chks += '<td><input onKeyPress="return soloNumeros(event);" type="text" class="form-control" placeholder="Cant"  name="rat_capturas_' + ava[i].id + '" id="rat_capturas_' + ava[i].id + '"></td>';
                        }
                        chks += "</tr>";
                    }
                    $("#factoresArmado").empty();
                    $("#factoresArmado").append(chks);
                    //Validamos si tenemos elementos preseleccionados
                    var ass = armData;
                    $("#formarmada :input").each(function() {
                        var p = $(this).attr("id");
                        for (var j in ass) {
                            var idchk = "chkArm" + ass[j].id;
                            if (p == idchk) {
                                $(this).attr("checked", "true");
                                //Actualización
                                if ($("#formTipo").val() == "actualizar") {
                                    $("#bajas_" + ass[j].id).val(ass[j].bajas);
                                    $("#capturas_" + ass[j].id).val(ass[j].capturas);
                                    $("#rat_bajas_" + ass[j].id).val(ass[j].rat_bajas);
                                    $("#rat_capturas_" + ass[j].id).val(ass[j].rat_capturas);
                                } else {
                                    $("#cant_armado_" + ass[j].id).val(ass[j].cantidad);
                                }
                            }
                        }
                    });
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    /**
     * Seleccionar todos los checbox segun el factor de inestabilidad
     */
    checkAll: function(formulario, nombreCheckbox) {
        if ($(`#${nombreCheckbox}`).is(":checked")) {
            $(`#${formulario} :input`).each(function() {
                this.checked = true;
            });
        } else {
            $(`#${formulario} :input`).each(function() {
                this.checked = false;
            });
        }
    },
    /**
     * @param nombreCampo Nombre del campo
     * @param formulario Nombre del formulario
     * @param nombreChk Nombre del check seleccionado
     */
    saveFactores: function(nomCampoCantidad, formulario, factor) {
        var inputs = document
            .getElementById(formulario)
            .getElementsByTagName("input"); // get element by tag name

        var modal = "";
        switch (factor) {
            case "social":
                socData = {};
                modal = "social";
                break;
            case "economico":
                econoData = {};
                modal = "economica";
                break;
            case "armado":
                armData = {};
                modal = "armada";
                break;
        }

        for (var i in inputs) {
            if (inputs[i].type == "checkbox") {
                if ($("#" + inputs[i].id).is(":checked")) {
                    var id = $("#" + inputs[i].id).val();
                    var cantidad = $(`#${nomCampoCantidad}${id}`).val();
                    if (cantidad != "") {
                        if (factor == "social") {
                            socData[id] = {
                                id: id,
                                cantidad: cantidad,
                            };
                        }
                        if (factor == "economico") {
                            econoData[id] = {
                                id: id,
                                cantidad: cantidad,
                            };
                        }
                        if (factor == "armado") {
                            armData[id] = {
                                id: id,
                                cantidad: cantidad,
                            };
                        }
                    } else {
                        UTIL.mostrarMensajeValidacion(
                            "EL campo cantidad de los elementos seleccionados no pueden estar vacio."
                        );
                        return;
                    }
                }
            }
        }
        $("#" + modal).modal("hide");
    },
    /**
     *  Metodo para guardar los datos de cantidades del modulo de actualización
     */
    saveFactoresActualizacion: function(nomCampoCantidad, formulario, factor) {
        var inputs = document
            .getElementById(formulario)
            .getElementsByTagName("input"); // get element by tag name

        var modal = "";
        switch (factor) {
            case "social":
                socData = {};
                modal = "social";
                break;
            case "economico":
                econoData = {};
                modal = "economica";
                break;
            case "armado":
                armData = {};
                modal = "armada";
                break;
        }

        for (var i in inputs) {
            if (inputs[i].type == "checkbox") {
                if ($("#" + inputs[i].id).is(":checked")) {

                    var id = $("#" + inputs[i].id).val();
                    var cantidad = $(`#${nomCampoCantidad}${id}`).val();

                    if (cantidad != "" || factor == "social" || factor == "economico") {
                        if (factor == "social") {
                            socData[id] = {
                                id: id,
                                cantidad: cantidad
                            };
                        }
                        if (factor == "economico") {
                            econoData[id] = {
                                id: id,
                                cantidad: cantidad
                            };
                        }
                    } else {
                        UTIL.mostrarMensajeValidacion(
                            "EL campo cantidad de los elementos seleccionados no pueden estar vacio."
                        );
                        return;
                    }

                    var bajas = $("#bajas_" + id).val();
                    var capturas = $("#capturas_" + id).val();
                    var rat_bajas = $("#rat_bajas_" + id).val();
                    var rat_capturas = $("#rat_capturas_" + id).val();

                    if (factor === "armado") {
                        if (bajas == "") {
                            UTIL.mostrarMensajeValidacion(
                                "La cantidad de bajas de los elementos seleccionados no pueden estar vacio."
                            );
                            return;
                        }
                        if (capturas == "") {
                            UTIL.mostrarMensajeValidacion(
                                "La cantidad de capturas de los elementos seleccionados no pueden estar vacio."
                            );
                            return;
                        }
                        if (rat_bajas == "") {
                            UTIL.mostrarMensajeValidacion(
                                "La cantidad de RAT Bajas de los elementos seleccionados no pueden estar vacio."
                            );
                            return;
                        }
                        if (rat_bajas == "") {
                            UTIL.mostrarMensajeValidacion(
                                "La cantidad de RAT Capturas de los elementos seleccionados no pueden estar vacio."
                            );
                            return;
                        }
                        armData[id] = {
                            id: id,
                            bajas: bajas,
                            capturas: capturas,
                            rat_bajas: rat_bajas,
                            rat_capturas: rat_capturas
                        };
                    }
                }
            }
        }
        $("#" + modal).modal("hide");
    },
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#tbl_departamento_id").val() == "" ||
            $("#tbl_departamento_id").val() == null ||
            $("#tbl_municipio_id").val() == "" ||
            $("#tbl_municipio_id").val() == null ||
            $("#tbl_vereda_id").val() == null ||
            $("#hr1").val() == null ||
            $("#hr1").val() == "" ||
            $("#fecha_hr1").val() == null ||
            $("#fecha_hr1").val() == ""
        ) {
            UTIL.mostrarMensajeValidacion(msj);
            bValid = false;
            return;
        }
        if (bValid) {
            INFO.saveInformacion();
        }
    },
    getVeredasByMunicipioId: function() {
        q = {};
        q.op = "veredaget";
        q.municipio_id = $("#tbl_municipio_id").val();
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
                    var res = data.output.response;
                    var info = '';
                    for (var j in res) {
                        info += "<option value='" + res[j].id + "'>" + res[j].nombre_vereda + "</option>";
                    }
                    $("#tbl_vereda_id").empty().append(info);
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    },
    saveInformacion: function() {
        Swal.fire({
            title: 'Estás seguro ingresar la información?',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: `Guardar`,
        }).then((result) => {
            if (result.value) {
                q = {};
                q.op = $("#formTipo").val() === "actualizar" ? 'updateinfo' : 'saveinfo';
                q.tbl_municipio_id = $("#tbl_municipio_id").val();
                q.tbl_vereda_id = $("#tbl_vereda_id").val();
                q.tbl_departamento_id = $("#tbl_departamento_id").val(); // Codigo
                q.observacionesSoci = $("#observacionesSoci").val();
                q.observacionesEcon = $("#observacionesEcon").val();
                q.observacionesArm = $("#observacionesArm").val();
                q.factoresSociales = socData;
                q.factoresEcon = econoData;
                q.factoresArmad = armData;

                var formData = new FormData();
                formData.append("op", q.op);
                formData.append("tbl_municipio_id", q.tbl_municipio_id);
                formData.append("tbl_vereda_id", q.tbl_vereda_id);
                formData.append("tbl_departamento_id", q.tbl_departamento_id);
                formData.append("tbl_departamento_id_jurisdiccion", $("#tbl_departamento_id_jurisdiccion").val());
                formData.append("tbl_municipio_id_jurisdiccion", $("#tbl_municipio_id_jurisdiccion").val());
                formData.append("tbl_vereda_id_jurisdiccion", $("#tbl_vereda_id_jurisdiccion").val());
                formData.append("coordenadas", $("#coordenadas").val());
                formData.append("resultado_jurisdiccion", $("#resultado_jurisdiccion").val());

                formData.append("observacionesSoci", q.observacionesSoci);
                formData.append("observacionesArm", q.observacionesArm);
                formData.append("observacionesEcon", q.observacionesEcon);

                formData.append("hr1", $("#hr1").val());
                formData.append("fecha_hr1", $("#fecha_hr1").val());
                formData.append("hr2", $("#hr2").val());
                formData.append("fecha_hr2", $("#fecha_hr2").val());
                formData.append("hr3", $("#hr3").val());
                formData.append("fecha_hr3", $("#fecha_hr3").val());
                formData.append("hr4", $("#hr4").val());
                formData.append("fecha_hr4", $("#fecha_hr4").val());
                formData.append("hr5", $("#hr5").val());
                formData.append("fecha_hr5", $("#fecha_hr5").val());

                var eco = [];
                if (econoData != undefined) {
                    Object.values(econoData).forEach(val => {
                        eco.push(JSON.stringify(val));
                    });
                }
                formData.append("factoresEcon", '[' + eco + ']');

                var soc = [];
                if (socData != undefined) {
                    Object.values(socData).forEach(val => {
                        soc.push(JSON.stringify(val));
                    });
                }
                formData.append("factoresSociales", '[' + soc + ']');

                var arm = [];
                if (armData != undefined) {
                    Object.values(armData).forEach(val => {
                        arm.push(JSON.stringify(val));
                    });
                }
                formData.append("factoresArmad", '[' + arm + ']');

                if (eco.length == 0 && arm.length == 0 && soc.length == 0) {
                    UTIL.mostrarMensajeError("Debe ingresar información en los factores de social, económico y/o armado");
                    return;
                }

                if ($("#formTipo").val() == 'actualizar') {
                    var docSocial = $('#docSocial')[0].files;
                    var docEconomico = $('#docEconomico')[0].files;
                    var docArmado = $('#docArmado')[0].files;

                    var imgSocial = $('#imgSocial')[0].files;
                    var imgEco = $('#imgEco')[0].files;
                    var imgArm = $('#imgArm')[0].files;

                    if (docSocial.length > 0) {
                        formData.append("docSocial", docSocial[0]);
                    }
                    if (docEconomico.length > 0) {
                        formData.append("docEconomico", docEconomico[0]);
                    }
                    if (docArmado.length > 0) {
                        formData.append("docArmado", docArmado[0]);
                    }
                    if (imgSocial.length > 0) {
                        formData.append("imgSocial", imgSocial[0]);
                    }
                    if (imgEco.length > 0) {
                        formData.append("imgEco", imgEco[0]);
                    }
                    if (imgArm.length > 0) {
                        formData.append("imgArm", imgArm[0]);
                    }
                }

                UTIL.cursorBusy();
                $.ajax({
                    data: formData,
                    type: "POST",
                    contentType: false,
                    processData: false,
                    url: "admin/ajax/rqst.php",
                    success: function(data) {
                        data = JSON.parse(data);
                        q = {};
                        UTIL.cursorNormal();
                        if (data.output.valid) {
                            UTIL.mostrarMensajeExitoso('Información Ingresada correctamente');
                            INFO.resetForm();
                        } else {
                            UTIL.mostrarMensajeError(data.output.response.content);
                        }
                    },
                });
            }
        });
    },
    resetForm: function() {
        socData = {};
        econoData = {};
        armData = {};
        $("#observacionesSoci").val("");
        $("#observacionesEcon").val("");
        $("#observacionesArm").val("");

        document.getElementById("docSocial").value = null;
        document.getElementById("docEconomico").value = null;
        document.getElementById("docArmado").value = null;
        document.getElementById("imgSocial").value = null;
        document.getElementById("imgEco").value = null;
        document.getElementById("imgArm").value = null;
    }
};