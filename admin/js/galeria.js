const optionsDropify = {
    messages: {
        'default': 'Seleccione o arrastre el archivo',
        'replace': 'Seleccione o arrastre el archivo',
        'remove':  'Remover',
        'error':   'Error al cargar el archivo'
    },
    error: {
        'fileSize': 'El archivo supera el tamaño permitido de: ({{ value }}).',
        'fileExtension': 'El formato del archivo seleccionado no es permitido (Permitidos: {{ value }} ).'
    }
};


$("#addGalery").click(function(event) {
    $("#dropifyImagenId").attr("required",'required');
    $("#dropifyImagenId").dropify(optionsDropify);
    $("#titulo").val("");
    $("#descripcion").val("");
    $("#idImagen").val("");
    $("#modalAdminImage").modal("show");
});

$("#gestionForm").submit(function(event) {
    event.preventDefault();

    var formData            = new FormData($('#gestionForm')[0]);
    formData.append("op","gestionarimagen_save")
    $.ajax({
        type: 'POST',
        url: "admin/ajax/rqst.php",
        data: formData,
        contentType: false,
        cache: false,
        processData:false,
        dataType: 'json',
        beforeSend: function(){
            UTIL.cursorBusy();
        },
        success: function(data){
            UTIL.cursorNormal();
            if (data.output.valid) {
                UTIL.mostrarMensajeExitoso('Información guardada correctamente');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                UTIL.mostrarMensajeError(data.output.response.content);
            }
        }
    });

});

$(".deleteImg").click(function(event) {
    event.preventDefault();
    var id = $(this).data("id");
    Swal.fire({
      title: '¿Deseas eliminar esta imagen?',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      cancelButtonText: 'Cancelar',
    }).then((result) => {

        if (result.hasOwnProperty('value')) {
            q       = {};
            q.op    = "pms_deleteimage";
            q.id    = id;
            
            $.ajax({
                type: 'POST',
                url: "admin/ajax/rqst.php",
                data: q,
                beforeSend: function(){
                    UTIL.cursorBusy();
                },
                success: function(result){
                    var data = JSON.parse(result);
                    UTIL.cursorNormal();
                    if (data.output.valid) {
                        UTIL.mostrarMensajeExitoso('Información guardada correctamente');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        UTIL.mostrarMensajeError(data.output.response.content);
                    }
                }
            });

        }
    })
});


$(".editImg").click(function(event) {
    event.preventDefault();
    const id = $(this).data("id");
    q       = {};
    q.op    = "pms_showimage";
    q.id    = id;
    $.ajax({
        type: 'POST',
        url: "admin/ajax/rqst.php",
        data: q,
        dataType: 'json',
        beforeSend: function(){
            UTIL.cursorBusy();
        },
        success: function(data){
            console.log(data.output.response[0]);
            UTIL.cursorNormal();
            if (data.output.valid) {

                var url = window.location.origin + window.location.pathname;

                url = url.replace("galeria.php", "")

                $("#dropifyImagenId").removeAttr("required");
                $("#dropifyImagenId").attr("data-default-file",url + "assets/img/gallery/"+data.output.response[0].imagen);
                $("#titulo").val(data.output.response[0].titulo);
                $("#descripcion").val(data.output.response[0].descripcion);
                $("#idImagen").val(id);
                $("#dropifyImagenId").dropify(optionsDropify);
                $("#modalAdminImage").modal("show");
            } else {
                UTIL.mostrarMensajeError(data.output.response.content);
            }
        }
    });
});