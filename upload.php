<?php
// Si este archivo se abre dentro de un iframe o como página,
// esto ayuda a evitar avisos y problemas de headers.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Archivos</title>

  <!-- (Opcional) Font Awesome: si NO lo usas aquí, lo puedes quitar -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <!-- AjaxUpload (tu uploader actual) -->
  <script src="admin/include/imagen_uploader/js/jquery-1.3.1.min.js"></script>
  <script src="admin/include/imagen_uploader/js/AjaxUpload.2.0.min.js"></script>

  <style>
    /* No dependemos de Bootstrap aquí (por si el iframe no lo carga) */
    body{
      margin:0;
      padding:0;
      background:transparent;
      font-family: Arial, Helvetica, sans-serif;
    }

    /* Botón */
    #upload_button{
      width: 50px;
      height: 40px;
      display: inline-flex;
      justify-content: center;
      align-items: center;
      border-radius: 10px;
      cursor: pointer;
      user-select: none;
      border: 1px solid rgba(0,0,0,.12);
      background: #234162; /* GOV */
      box-shadow: 0 6px 14px rgba(0,0,0,.15);
      transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
    }
    #upload_button:hover{
      transform: translateY(-1px);
      box-shadow: 0 10px 18px rgba(0,0,0,.18);
    }
    #upload_button:active{
      transform: translateY(0);
      opacity:.92;
    }

    #upload_button img{
      width: 120%;
      height: 120%;
      object-fit: contain;
      pointer-events: none;
    }

    /* Estado visual */
    .uploading{
      opacity: .75;
      cursor: not-allowed;
      filter: grayscale(.1);
    }
    .done{
      background: #16a34a;
      border-color: rgba(255,255,255,.25);
    }
  </style>

  <script>
    // Config simple
    var UPLOAD_ACTION = 'upload_images_ajax.php';
    var ALLOWED_EXT   = ['jpg','jpeg','png','bmp','svg'];

    $(function () {
      var $btn = $('#upload_button');

      new AjaxUpload('#upload_button', {
        action: UPLOAD_ACTION,

        onSubmit: function (file, ext) {
          // AjaxUpload retorna ext como array de RegExp, extraer el string
          if (Array.isArray(ext)) { ext = ext[0]; }
          ext = (ext ? String(ext) : '').toLowerCase();

          if (!ext || $.inArray(ext, ALLOWED_EXT) === -1) {
            alert('Error: Solo se permiten archivos: ' + ALLOWED_EXT.join(', '));
            return false;
          }

          // UI state
          $btn.addClass('uploading');
          $btn.attr('title', 'Subiendo archivo...');
          this.disable();
        },

        onComplete: function (file, response) {
          // UI state
          $btn.removeClass('uploading').addClass('done');
          $btn.attr('title', 'Cargado');

          // Bandera para tu flujo
          $('#valor_iframe').val('1');

          // Reactiva el botón
          this.enable();

          // IMPORTANTÍSIMO: Mantengo tu lógica del iframe
          try {
            var iframe = window.frameElement;
            if (iframe) {
              iframe.setAttribute("data-loaded", "true");
              iframe.setAttribute("data-url", response);
            }
          } catch (e) {
            // Si el navegador bloquea frameElement por seguridad, no rompemos nada
            console.log('[upload.php] No se pudo acceder a frameElement:', e);
          }
        }
      });
    });
  </script>
</head>

<body>
  <div id="upload_button" title="Subir imagen">
    <img src="assets/images/iconodescarga.png" alt="Subir imagen">
  </div>

  <input type="hidden" value="0" name="valor_iframe" id="valor_iframe">
</body>
</html>