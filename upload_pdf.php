<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Archivos</title>

    <!-- SUBIR IMAGEN AJAX -->
    <script language="javascript" src="admin/include/imagen_uploader/js/jquery-1.3.1.min.js"></script>
    <script language="javascript" src="admin/include/imagen_uploader/js/AjaxUpload.2.0.min.js"></script>
    <script language="javascript">
        $(document).ready(function() {
            var button = $('#upload_button'),
                interval;
            new AjaxUpload('#upload_button', {
                action: 'upload_images_ajax_pdf.php',
                onSubmit: function(file, ext) {
                    extensiones_permitidas = new Array(".pdf");
                    if (!(ext && /^(pdf)$/.test(ext))) {
                        //Extensiones permitidas
                        alert('Error: Solo se permiten archivos con extenciones ' + extensiones_permitidas);
                        // Cancela upload
                        return false;
                    } else {
                        button.text('Subiendo Archivo...');
                        this.disable();
                    }
                },
                onComplete: function(file, response) {
                    button.text('Cargado.').css({
                        'color': 'green',
                        'font-weight': 'bold',
                        'font-size': '16px'
                    });
                    $('#valor_iframe').val('1');
                    // nable upload button
                    this.enable();
                    // Agrega archivo a la lista
                    $('#lista').appendTo('.files').text(file);

                    var iframe = window.frameElement;
                    iframe.setAttribute("data-loaded", "true");
                    iframe.setAttribute("data-url", response);
                }
            });
        });
    </script>
    <!--     <link href="admin/include/imagen_uploader/style.css" rel="stylesheet" type="text/css" /> -->


    <!-- FIN IMAGEN AJAX -->
</head>

<body>
    <div style="width: 50px; height: 40px;  display: flex; justify-content: center; align-items: center;" id="upload_button" class="btn btn-primary">
        <!-- Cambia la ruta de la imagen según la ruta de tu proyecto -->
        <img src="assets/images/iconodescarga.png" alt="Sube tu imagen" style="width: 120%; height: 120%; object-fit: contain;">
    </div>
    <input type="hidden" value="0" name="valor_iframe" id="valor_iframe" />
</body>



</html>