function btnSaveLoad() {
    $("#btn_save").html('Saving ...');
    $("#btn_save").attr("disabled", true);
}

function btnSave() {
    $("#btn_save").html('Save');
    $("#btn_save").attr("disabled", false);
}

$(document).ready(function() {

    $(".textoGlo").keypress(function(key) {
        if ((key.charCode < 97 || key.charCode > 122) &&
            (key.charCode < 65 || key.charCode > 90) &&
            (key.charCode != 45) &&
            (key.charCode != 241) &&
            (key.charCode != 209) &&
            (key.charCode != 32)
        )
            return false;
    });
    $(".numeroDni").keypress(function(key) {
        if ((key.charCode < 48 || key.charCode > 57))
            return false;
    });
    $('.numeroDni').on('keydown keypress', function(e) {
        if (e.key.length === 1) {
            if ($(this).val().length < 8 && !isNaN(parseFloat(e.key))) {
                $(this).val($(this).val() + e.key);
            }
            return false;
        }
    });

    $("#frm_foto").unbind('submit').bind('submit', function(event) {
        var id = $('#id').val();
        var zone = $('#zone').val();
        var actividades = $('#actividades').val();
        var observaciones = $('#observaciones').val();
        var tbl_requerimiento_id = $('#tbl_unidad_id').val(); 

        var $canvas      = document.getElementById('canvas');
        var $video      = document.getElementById('video');

        var contexto    = $canvas.getContext("2d");
        $canvas.width   = $video.videoWidth;
        $canvas.height  = $video.videoHeight;
        contexto.drawImage($video, 0, 0, $canvas.width, $canvas.height);
     
        var foto = $canvas.toDataURL();
        var info = foto.split(",", 2);
        $.ajax({
            type: "POST",
            url: "admin/js/camara/save_photo.php",
            data: {
                foto: foto,
                id: id,
                zone: zone,
                actividades: actividades,
                observaciones: observaciones,
                tbl_requerimiento_id: tbl_requerimiento_id,
            },
            dataType: 'json',
            beforeSend: function() {
                btnSaveLoad();
            },
            success: function(response) {
                btnSave();
                if (response.success == true) {
                    swal("MENSAJE", response.messages, "success");
                    $("#frm_foto")[0].reset();
                    $("#radiosfoto").click();
                } else {
                    swal("MENSAJE", response.messages, "error");
                }
            }
        });

        return false;

    });


});