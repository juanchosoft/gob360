$(document).on("ready", init);
var q = {};

function init() {
    q = {};
}

const INGRESOPAE = {
    requiredFields: [
        "date", "provincia", "tbl_departamento_id", "tbl_municipio_id", "tbl_sede_educativa_id","tbl_instituciones_educativas_id",
        "rector", "cc", "tel", "sector", "atencion_mayor_indigena", "complemento_ampm", "complemento_ampm_indus", "preparado_sitio",
        "comida_transportada", "comedor_escolar", "concepto_sanitario", "fecha_expedicion", "cerca_a_contaminacion", "sede_conflicto_armado",
        "frecuencia_conflicto", "espacio_almacenamiento", "material_techo", "material_piso", "material_paredes", "espacio_dedicado",
        "material_techo_preparacion", "material_paredes_preparacion", "congeladores_funcionando", "espacio_consumo", "acceso_electricidad",
        "acceso_agua", "tipo_agua", "tipo_gas", "tiene_recoleccion_basura", "disposicion_derechos_pae", "disposicion_no_organicos_pae",
        "clasificacion_residuos", "neveras_almacenamiento", "cant_neveras", "neveras_funcionando", "tamano_neveras_principales",
        "congeladores", "cantidad_congeladores", "tamano_congelador", "almacena_alto_suelo", "elementos_almacenamiento", "estufa",
        "cant_estufas", "cant_quemadores", "cant_quemadores_buenos", "licuadora", "cant_licuadora", "cant_licuadoras_buenas",
        "cant_licuadoras_ind", "posee_ollas_pae", "posee_cuchillos_pae", "posee_cucharones_pae", "cant_ninos_pae_sentados",
        "cant_platos_pae", "pocillos_pae", "cucharas_pae", "posee_sanitario_exclusivo_pae", "posee_lavamanos_pae", "estado_sede",
        "estado_techo_almacenamiento", "estado_piso_almacenamiento", "estado_paredes_almacenamiento", "estado_techo_preparacion",
        "material_piso_preparacion", "cargue_info", "clasificacion", "sede_alcantarillado", "visita", "ano", "latitud", "longitud",
        "observaciones", "estado_piso_preparacion", "estado_paredes_preparacion", "ninos_focalizados", "tenedores_pae",
        "cant_canecas", "disposicion_desechos_pae"
    ],

    optionalFields: [
        "tbl_user_id", "edit_tbl_user_id", "vereda_id", "fecha_expedicion"
    ],

    validateData: function () {
        let missing = [];

        this.requiredFields.forEach(id => {
            const $el = $("#" + id);
            const value = $el.val();
            if (!value || value === "Seleccione") {
                missing.push(id);
                $el.addClass("is-invalid");
            } else {
                $el.removeClass("is-invalid");
            }
        });

        if (missing.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Te falta llenar un campo obligatorio',
                text: 'Por favor, valida los campos marcados con asterisco.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Entendido'
            });
            return false;
        }

        this.saveData();
    },
    getFotosCargadas: function () {
        // Suponiendo que guardas los nombres separados por coma
        const fotosString = $("#fotos").val();
        if (!fotosString) return [];

        // Devuelve un array de nombres de archivo
        return fotosString.split(",").filter(foto => foto.trim() !== "");
    },
    saveData: function () {
        q = {};
        q.op = 'savepaedan';

        const allFields = [...this.requiredFields, ...this.optionalFields];
        allFields.forEach(id => {
            q[id] = $("#" + id).val();
        });

        // Agregar las imágenes cargadas
        q["fotos"] = fotosTomadas;

                // Botón para guardar la firma
        if (signaturePad.isEmpty()) {
            swal("warning", "Por favor, firma antes de guardar.", "error");
            bValid = false;
            return;
        }

        if (signaturePad.isEmpty()) {
            swal("warning", "Por favor, firma antes de guardar.", "error");
            bValid = false;
            return;
        }
        const dataURL = signaturePad.toDataURL('image/png');
        q.firma = dataURL;

        UTIL.cursorBusy();
        $.ajax({
            data: q,
            type: "POST",
            dataType: "json",
            url: "admin/ajax/rqst.php",
            success: function (data) {
                UTIL.cursorNormal();
                if (data.output.valid) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Información guardada correctamente',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        UTIL.clearForm("formsecretaria");
                        window.location = 'ingreso_pae.php';
                    });
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
            error: function () {
                UTIL.cursorNormal();
                Swal.fire({
                    icon: 'error',
                    title: '❌ Error en el servidor',
                    text: 'No se pudo guardar la información. Inténtalo más tarde.'
                });
            }
        });
    },

    getSedesEducativasByCodigoMunicipio: function (id) {
        q = {};
        q.op = "getSedeEducativaByCodMunicipio";
        q.codigo_muncipio = id;
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
                    let res = data.output.response;
                    var info = '<option value="seleccione">Seleccione...</option>';
                    for (var j in res) {
                        info += "<option value='" + res[j].sede_educativa_id + "' selected>" + res[j].nombre + "</option>";
                    }
                    $("#tbl_sede_educativa_id").empty().append(info);
                    INGRESOPAE.getDatosRectorByInstitucionYSede();

                    INGRESOPAE.getDatosBySedeEducativaId(null);
                } else {
                    $("#tbl_sede_educativa_id").empty().append('');
                    emptyDatosInputs();
                }
            },
        });
    },
    getDatosBySedeEducativaId: function (id) {
        id = id || $("#tbl_sede_educativa_id").val();
        q = {};
        q.op = "getSedeEducativa";
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
                    $("#sector").val(res.sector);
                    $("#rector").val(res.rector);
                    $("#cc").val(res.cc);
                    $("#tel").val(res.tel);
                    $("#visita").val(res.visita);
                    $("#tbl_instituciones_educativas_id").val(res.institucion_id);
                    $("#institucion_educativa").val(res.institucion_id); 
                } else {
                    emptyDatosInputs();
                }
            },
        });
    },

    getDatosByInstitucionEducativaId: function (codigo_municipio) {
        q = {};
        q.op = "getInstitucionesByCodigoMunicipio";
        q.codigo_municipio = codigo_municipio;

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
                    let res = data.output.response;
                    let options = '<option value="">Seleccione una institución</option>';

                    for (let i in res) {
                        options += `<option value="${res[i].institucion_id}">${res[i].nombre}</option>`;
                    }

                    $("#institucion_educativa").empty().append(options);


                    if (res.length > 0) {
                        const primera = res[0];
                        $("#institucion_educativa").val(primera.institucion_id);
                        $("#tbl_instituciones_educativas_id").val(primera.institucion_id);
                        INGRESOPAE.getDatosRectorByInstitucionYSede();

                    } else {
                        $("#tbl_instituciones_educativas_id").val('');
                    }
                } else {
                    $("#institucion_educativa").empty().append('<option value="">No hay instituciones disponibles</option>');
                    $("#tbl_instituciones_educativas_id").val('');
                }
            },
            error: function () {
                UTIL.cursorNormal();
                Swal.fire("Error", "No se pudo cargar las instituciones educativas", "error");
            }
        });
    },
getDatosRectorByInstitucionYSede: function () {
    const institucion_id = $('#institucion_educativa').val();
    const sede_id = $('#tbl_sede_educativa_id').val();

    if (institucion_id && sede_id) {
        $.ajax({
            type: 'POST',
            url: 'admin/ajax/rqst.php', // asegúrate que sea la ruta correcta
            data: {
                op: 'getDatosRectorByInstitucionYSede',
                institucion_id: institucion_id,
                sede_id: sede_id
            },
            dataType: 'json',
            success: function (data) {
                if (data.output && data.output.valid) {
                    const res = data.output.response;
                    $('#rector').val(res.rector || '');
                    $('#cc').val(res.cc || '');
                    $('#tel').val(res.tel || '');
                } else {
                    $('#rector').val('');
                    $('#cc').val('');
                    $('#tel').val('');
                }
            },
            error: function () {
                $('#rector').val('');
                $('#cc').val('');
                $('#tel').val('');
            }
        });
    }
}



};
function emptyDatosInputs() {
    $("#sector").val('');
    $("#rector").val('');
    $("#institucion_educativa").val('');
    $("#cc").val('');
    $("#tel").val('');
}

