$(document).on('ready', init);
var q;

function init() {
    q = {};
}

var return_page = 'ingreso_informacion_votaciones.php';
var DETALLEVOTACIONES = {
    edit: function(id) {
        q = {};
        q.op = "pms_getvotacion";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editHandler);
    },
    editHandler: function(data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#tbl_vereda_id").val(res.tbl_vereda_id);
            $("#direccion").val(res.direccion);
            $("#c1").val(res.c1);
            $("#c2").val(res.c2);
            $("#c3").val(res.c3);
            $("#c4").val(res.c4);
            $("#c5").val(res.c5);
            $("#c6").val(res.c6);
            $("#mesas").val(res.mesas);
            $("#helicoportado").val(res.helicoportado);
            $("#horas_vuelo").val(res.horas_vuelo);
            $("#observaciones").val(res.observaciones);
            $("#direccion").val(res.direccion);
            $("#nombre_puesto").val(res.nombre_puesto);
            $("#hombres").val(res.hombres);
            $("#mujeres").val(res.mujeres);
            $("#total_poblacion").val(res.total_poblacion);
            $("#oficiales").val(res.oficiales);
            $("#suboficiales").val(res.suboficiales);
            $("#soldados").val(res.soldados);
            $("#comandante").val(res.comandante);
            $("#telefono").val(res.telefono);
            $("#total").val(res.total);
            $("#indicativo").val(res.indicativo);
            $("#compania").val(res.compania);
            $("#peloton").val(res.peloton);
            $("#seccion").val(res.seccion);
            $("#escuadra").val(res.escuadra);
            $("#soldados18").val(res.soldados18);
            $("#reserva").val(res.reserva);
            $("#grado").val(res.grado);
            $("#tiempo_ocupacion").val(res.tiempo_ocupacion);
            $("#desplazamiento").val(res.desplazamiento);
            $("#mixto").val(res.mixto);
        } else {
            UTIL.mostrarMensajeError(data.output.response.content);
        }
    },
    validateData: function() {
        var bValid = true;
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        /*if (
            $("#oficiales").val() == "" ||
            $("#suboficiales").val() == "" ||
            $("#soldados").val() == "" ||
            $("#comandante").val() == "" ||
            $("#telefono").val() == "" ||
            $("#helicoportado").val() == "" ||
            $("#observaciones").val() == "" ||
            $("#horas_vuelo").val() == "" ||
            $("#tiempo_ocupacion").val() == "" ||
            $("#indicativo").val() == ""
        ) {
            bValid = false;
            UTIL.mostrarMensajeError(msj);
        }*/
        if (bValid) {
            DETALLEVOTACIONES.savedata();
        }
    },
    savedata: function() {
        q = {};
        q.op = "pms_votacionupdate";
        q.id = $("#id").val();
        q.oficiales = $("#oficiales").val();
        q.suboficiales = $("#suboficiales").val();
        q.soldados = $("#soldados").val();
        q.comandante = $("#comandante").val();
        q.telefono = $("#telefono").val();
        q.total = $("#total").val();
        q.indicativo = $("#indicativo").val();
        q.tiempo_ocupacion = $("#tiempo_ocupacion").val();
        q.tbl_vereda_id = $("#tbl_vereda_id").val();
        q.helicoportado = $("#helicoportado").val();
        q.horas_vuelo = $("#horas_vuelo").val();
        q.observaciones = $("#observaciones").val();
        q.desplazamiento = $("#desplazamiento").val();
        q.mixto = $("#mixto").val();
        q.compania = $("#compania").val();
        q.peloton = $("#peloton").val();
        q.seccion = $("#seccion").val();
        q.escuadra = $("#escuadra").val();
        q.soldados18 = $("#soldados18").val();
        q.reserva = $("#reserva").val();
        q.grado = $("#grado").val();
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
                    UTIL.mostrarMensajeExitoso('Información guardada correctamente');
                    /*                     setTimeout(function() {
                                            window.location = return_page;
                                        }, 1500); */
                } else {
                    UTIL.mostrarMensajeError(data.output.response.content);
                }
            },
        });
    }
};