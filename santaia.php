<?php
// Configurar la zona horaria
date_default_timezone_set('America/Bogota');
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Allbase Chat Demo</title>
  </head>
  <body>
    <script type="module">
      localStorage.removeItem('conversationId');
      localStorage.removeItem('visitorId');

      const Chatbox = await import(
        'https://cdn.jsdelivr.net/npm/@facu1704/allbase-embeds@latest/dist/chatbox/index.min.js'
      ).then((m) => m.default);

      const widget = await Chatbox.initBubble({
        agentId: 'cmiqe8lp00004rf013gbjtbk5',

        contact: {
          firstName: 'María',
          lastName: 'González',
          email: 'maria@ejemplo.com',
          phoneNumber: '+5493512345678',
          userId: 'demo-user-12sss3',
        },

        initialMessages: [
          '¡Hola María! ¿Cómo estás hoy?',
          '¿En qué puedo ayudarte?',
        ],

        context:
          'El usuario con el que estás hablando es María. Salúdala por su nombre.',
      });

      widget.open();
    </script>
  </body>
</html>
