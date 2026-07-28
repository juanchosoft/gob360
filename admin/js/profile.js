$(document).on('ready', initusuario);
var q;

function initusuario() {
    q = {};
}
var PROFILE = {
    editData: function (id) {
        q = {};
        q.op = "pms_usrget";
        q.id = id;
        UTIL.callAjaxRqstPOST(q, this.editDataHandler);
    },
    editDataHandler: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            var res = data.output.response[0];
            $("#id").val(res.id);
            $("#nombre_perfil").val(res.nombre);
            $("#apellido_perfil").val(res.apellido);
            $("#nickname_perfil").val(res.nickname);
            $("#nickname2_perfil").val(res.nickname);
            $("#hashpass_perfil").val("");
            $("#hashpass1_perfil").val("");
        } else {
            $("#mensajes").empty().append(data.output.response.content);
        }
    },
    validateData: function () {
        var bValid = true;
        $("#mensajes").empty().append('');
        var msj = "Falta ingresar información obligatoria, marcada con asterisco.";
        if (
            $("#nombre_perfil").val() == "" ||
            $("#nickname_perfil").val() == "" ||
            $("#apellido_perfil").val() == ""
        ) {
            $("#mensajes").empty().append(msj);
            bValid = false;
            return;
        }
        if ($("#hashpass_perfil").val() == "" && $("#id").val() == "") {
            bValid = false;
            $("#mensajes").empty().append('Ingrese su contraseña');
            return;
        }
        if ($("#hashpass1_perfil").val() == "" && $("#id").val() == "") {
            bValid = false;
            $("#mensajes").empty().append('Debe confirmar su contraseña');
            return;
        }
        //Validamos el email que sea válido
        if ($("#nickname_perfil").val() != "") {
            var nickname = UTIL.isEmail($("#nickname_perfil").val());
            if (!nickname) {
                $("#mensajes").empty().append('El nombre de usuario debe ser un email válido.');
                bValid = false;
                return;
            }
        }
        if (bValid) {
            PROFILE.savedata();
        }
    },
    savedata: function () {
        var hashpass = $("#hashpass_perfil").val();
        var hashpass1 = $("#hashpass1_perfil").val();
        $("#mensajes").empty().append('');
        if (hashpass.length > 1) {
            if (hashpass == hashpass1) {
                $("#hashpass_perfil").val(hex_md5(hashpass));
                $("#hashpass1_perfil").val(hex_md5(hashpass1));
            } else {
                $("#mensajes").empty().append("Las contraseñas no coninciden. Intentelo de nuevo.");
                return;
            }
        }
        var nickname = $("#nickname_perfil").val();
        var nickname2 = $("#nickname2_perfil").val();
        if (nickname.length > 0 && nickname != nickname2) {
            //se verifica que el nombre de usuario este disponible si se ingresa nuevamente
            q = {};
            q.op = "pms_usravailable";
            q.nickname = $("#nickname_perfil").val();
            q.id = $("#id").val();
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
                        PROFILE.sendDataSave();
                    } else {
                        $("#mensajes").empty().append("El Usuario *" + $("#nickname_perfil").val() + "* ya existe, utilice uno nuevo.");
                        $("#hashpass_perfil").val("");
                        $("#hashpass1_perfil").val("");
                    }
                },
            });
        } else {
            PROFILE.sendDataSave();
        }
    },
    sendDataSave() {
        q = {};
        q.op = "acttualizarPerfil";
        q.id = $("#id").val();
        q.nombre = $("#nombre_perfil").val();
        q.apellido = $("#apellido_perfil").val();
        q.nickname = $("#nickname_perfil").val();
        q.nickname2 = $("#nickname2_perfil").val();
        q.hashpass = $("#hashpass_perfil").val();
        q.hashpass1 = $("#hashpass1_perfil").val();
        UTIL.callAjaxRqstPOST(q, PROFILE.savedatahandler);
    },
    savedatahandler: function (data) {
        UTIL.cursorNormal();
        if (data.output.valid) {
            location.reload(true);
        } else {
            $("#mensajes").empty().append(data.output.response.content);
        }
    }
};