/**
 * ALMA – Widget flotante
 * Requiere jQuery (ya cargado por gerenic_script.php).
 */
(function ($) {
  'use strict';

  /* ── Estado ── */
  var estado = {
    conversacionId: 0,
    modo:           'texto',   // 'texto' | 'voz'
    enviando:       false,
    gravando:       false,
    mediaRecorder:  null,
    audioChunks:    [],
  };

  /* ── Audio activo (TTS) ── */
  var audioActual = null;

  /* ── Endpoints ── */
  var URL_CHAT      = 'admin/ajax/ia_chat.php';
  var URL_HISTORIAL = 'admin/ajax/ia_historial.php';
  var URL_STT       = 'admin/ajax/ia_stt.php';
  var URL_TTS       = 'admin/ajax/ia_tts.php';

  /* ── Inicialización ── */
  $(document).ready(function () {
    cargarHistorial();
    bindEventos();
  });

  /* ── Binding de eventos ── */
  function bindEventos() {
    $('#ia-fab').on('click', abrirWidget);
    $('#ia-btn-close').on('click', cerrarWidget);
    $('#ia-btn-nueva').on('click', nuevaConversacion);

    $('#ia-btn-send').on('click', enviarMensaje);
    $('#ia-input').on('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        enviarMensaje();
      }
    });

    $('#ia-input').on('input', function () {
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    $('.ia-mode-btn').on('click', function () {
      setModo($(this).data('mode'));
    });

    $('#ia-btn-mic').on('click', toggleGrabacion);
  }

  /* ── Abrir / cerrar ── */
  function abrirWidget() {
    $('#ia-fab').addClass('ia-hidden');
    $('#ia-widget').removeClass('ia-hidden');
    $('#ia-input').focus();
  }

  function cerrarWidget() {
    $('#ia-widget').addClass('ia-hidden');
    $('#ia-fab').removeClass('ia-hidden');
    if (estado.gravando) pararGrabacion();
    pararAudio();
  }

  /* ── Historial de conversaciones ── */
  function cargarHistorial() {
    $.post(URL_HISTORIAL, { op: 'listar' }, function (data) {
      if (!data.output || !data.output.valid) return;
      var lista = data.output.response || [];
      var $list = $('#ia-conv-list').empty();
      if (lista.length === 0) {
        $list.append('<div style="padding:12px 14px;color:rgba(255,255,255,.55);font-size:12px">Sin conversaciones aún</div>');
        return;
      }
      lista.forEach(function (conv) {
        var $item = $('<div>')
          .addClass('ia-conv-item')
          .attr('data-id', conv.id)
          .text(conv.titulo || 'Nueva conversación');
        if (parseInt(conv.id) === estado.conversacionId) {
          $item.addClass('ia-active');
        }
        $item.on('click', function () { cargarConversacion(parseInt(conv.id)); });
        $list.append($item);
      });
    }, 'json').fail(function () { /* historial no crítico */ });
  }

  function cargarConversacion(convId) {
    $.post(URL_HISTORIAL, { op: 'cargar', conversacion_id: convId }, function (data) {
      if (!data.output || !data.output.valid) {
        mostrarError(data.output ? data.output.response : 'Error al cargar conversación.');
        return;
      }
      estado.conversacionId = convId;
      var mensajes = data.output.response || [];

      $('#ia-messages').empty();
      if (mensajes.length === 0) {
        mostrarBienvenida();
      } else {
        mensajes.forEach(function (m) {
          agregarBurbuja(m.rol === 'user' ? 'user' : 'ia', m.contenido, m.id);
        });
        scrollAbajo();
      }

      $('.ia-conv-item').removeClass('ia-active');
      $('.ia-conv-item[data-id="' + convId + '"]').addClass('ia-active');

      var titulo = $('.ia-conv-item[data-id="' + convId + '"]').text();
      $('#ia-conv-titulo').text(titulo || 'Conversación');
    }, 'json');
  }

  function nuevaConversacion() {
    estado.conversacionId = 0;
    $('#ia-messages').empty();
    mostrarBienvenida();
    $('#ia-conv-titulo').text('Nueva conversación');
    $('.ia-conv-item').removeClass('ia-active');
    $('#ia-input').val('').trigger('input').focus();
    pararAudio();
  }

  /* ── Envío de mensaje (texto) ── */
  function enviarMensaje() {
    if (estado.enviando) return;
    var texto = $('#ia-input').val().trim();
    if (!texto) return;

    agregarBurbuja('user', texto);
    $('#ia-input').val('').css('height', 'auto');
    mostrarTyping();
    setEnviando(true);

    $.post(URL_CHAT, {
      mensaje:         texto,
      conversacion_id: estado.conversacionId,
      origen:          'texto',
    }, function (data) {
      quitarTyping();
      setEnviando(false);

      if (!data.output || !data.output.valid) {
        mostrarError(data.output ? data.output.response : 'Error inesperado.');
        return;
      }
      estado.conversacionId = data.output.conversacion_id || estado.conversacionId;
      var mensajeId = data.output.mensaje_id || 0;
      agregarBurbuja('ia', data.output.response, mensajeId);

      if (estado.conversacionId > 0) {
        $('#ia-conv-titulo').text(texto.substring(0, 60) + (texto.length > 60 ? '…' : ''));
      }
      cargarHistorial();
    }, 'json').fail(function () {
      quitarTyping();
      setEnviando(false);
      mostrarError('Error de conexión. Verifica tu acceso a internet.');
    });
  }

  /* ── Burbujas de chat ── */
  /**
   * @param {string} tipo       'user' | 'ia'
   * @param {string} texto      Contenido del mensaje
   * @param {number} mensajeId  ID en BD (solo para mensajes 'ia', habilita botón TTS)
   */
  function agregarBurbuja(tipo, texto, mensajeId) {
    var $bienvenida = $('#ia-bienvenida');
    if ($bienvenida.length) $bienvenida.remove();

    var $msg = $('<div>').addClass('ia-msg');
    if (tipo === 'user') {
      $msg.addClass('ia-msg-user').text(texto);
    } else {
      $msg.addClass('ia-msg-ia');
      var $label   = $('<span>').addClass('ia-msg-label').text('ALMA');
      var $content = $('<div>').html(markdownSimple(texto));
      $msg.append($label).append($content);

      // Botón de reproducción TTS (solo si hay mensajeId y el usuario tiene permiso de voz)
      if (mensajeId && window.IA_WIDGET_CFG && window.IA_WIDGET_CFG.voz) {
        var $playBtn = $('<button>')
          .addClass('ia-play-btn')
          .attr('title', 'Escuchar respuesta')
          .html('<i class="feather icon-volume-2"></i>')
          .on('click', function () {
            reproducirTTS(mensajeId, $playBtn);
          });
        $msg.append($playBtn);
      }
    }
    $('#ia-messages').append($msg);
    scrollAbajo();
  }

  function mostrarTyping() {
    var $typing = $('<div>')
      .attr('id', 'ia-typing')
      .addClass('ia-typing')
      .append('<span></span><span></span><span></span>');
    $('#ia-messages').append($typing);
    scrollAbajo();
  }
  function quitarTyping() { $('#ia-typing').remove(); }

  function mostrarError(msg) {
    var $err = $('<div>')
      .addClass('ia-msg ia-msg-ia')
      .css('border', '1px solid rgba(198,40,40,.35)')
      .html('<span style="color:#c62828"><i class="feather icon-alert-triangle"></i> ' + escHtml(msg) + '</span>');
    $('#ia-messages').append($err);
    scrollAbajo();
  }

  function mostrarBienvenida() {
    if ($('#ia-bienvenida').length) return;
    var cfg = window.IA_WIDGET_CFG || {};
    var titulo = cfg.bienvenidaTitulo || 'ALMA';
    var texto  = cfg.bienvenidaTexto || 'Haz una pregunta sobre compromisos, proyectos o indicadores.';
    var $bv = $('<div>').attr('id', 'ia-bienvenida').addClass('ia-bienvenida');
    $bv.html(
      '<div class="ia-bienvenida-icono"><i class="feather icon-cpu"></i></div>' +
      '<h3>' + escHtml(titulo) + '</h3>' +
      '<p>' + texto + '</p>'
    );
    $('#ia-messages').append($bv);
  }

  /* ── Modo texto / voz ── */
  function setModo(modo) {
    estado.modo = modo;
    $('.ia-mode-btn').removeClass('ia-mode-active');
    $('.ia-mode-btn[data-mode="' + modo + '"]').addClass('ia-mode-active');

    if (modo === 'voz') {
      $('#ia-btn-mic').show();
      $('#ia-btn-send').hide();
      $('#ia-voz-estado').text('Presiona el micrófono para hablar').show();
    } else {
      $('#ia-btn-mic').hide();
      $('#ia-btn-send').show();
      $('#ia-voz-estado').hide().text('');
      if (estado.gravando) pararGrabacion();
      pararAudio();
    }
  }

  /* ── Grabación de voz ── */
  function toggleGrabacion() {
    if (estado.gravando) {
      pararGrabacion();
    } else {
      iniciarGrabacion();
    }
  }

  function iniciarGrabacion() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      mostrarError('Tu navegador no soporta grabación de audio.');
      return;
    }

    // Determinar MIME soportado (webm en Chrome/Edge, mp4 en Safari)
    var mimeType = 'audio/webm';
    if (!MediaRecorder.isTypeSupported('audio/webm') && MediaRecorder.isTypeSupported('audio/mp4')) {
      mimeType = 'audio/mp4';
    }

    navigator.mediaDevices.getUserMedia({ audio: true })
      .then(function (stream) {
        estado.audioChunks  = [];
        estado.mediaRecorder = new MediaRecorder(stream, { mimeType: mimeType });
        estado.mediaRecorder.ondataavailable = function (e) {
          if (e.data.size > 0) estado.audioChunks.push(e.data);
        };
        estado.mediaRecorder.onstop = procesarAudio;
        estado.mediaRecorder.start(250);
        estado.gravando = true;
        $('#ia-btn-mic').addClass('ia-recording');
        $('#ia-voz-estado').text('Grabando… toca para detener');
      })
      .catch(function () {
        mostrarError('No se pudo acceder al micrófono. Verifica los permisos del navegador.');
      });
  }

  function pararGrabacion() {
    if (estado.mediaRecorder && estado.gravando) {
      estado.mediaRecorder.stop();
      estado.mediaRecorder.stream.getTracks().forEach(function (t) { t.stop(); });
      estado.gravando = false;
      $('#ia-btn-mic').removeClass('ia-recording');
      $('#ia-voz-estado').text('Procesando audio…');
    }
  }

  function procesarAudio() {
    var mimeType = estado.mediaRecorder ? estado.mediaRecorder.mimeType : 'audio/webm';
    var blob = new Blob(estado.audioChunks, { type: mimeType });
    var ext  = mimeType.includes('mp4') ? 'mp4' : 'webm';

    var fd = new FormData();
    fd.append('audio', blob, 'grabacion.' + ext);
    fd.append('conversacion_id', estado.conversacionId);

    setEnviando(true);
    mostrarTyping();

    $.ajax({
      url:         URL_STT,
      type:        'POST',
      data:        fd,
      processData: false,
      contentType: false,
      dataType:    'json',
      success: function (data) {
        quitarTyping();
        setEnviando(false);

        if (!data.output || !data.output.valid) {
          $('#ia-voz-estado').text('Presiona el micrófono para hablar');
          mostrarError(data.output ? data.output.response : 'Error en reconocimiento de voz.');
          return;
        }

        estado.conversacionId = data.output.conversacion_id || estado.conversacionId;
        var mensajeId = data.output.mensaje_id || 0;

        agregarBurbuja('user', data.output.transcripcion || '');
        agregarBurbuja('ia',   data.output.respuesta     || '', mensajeId);

        // Auto-reproducir TTS en modo voz
        if (mensajeId && estado.modo === 'voz') {
          $('#ia-voz-estado').text('Reproduciendo respuesta…');
          reproducirTTS(mensajeId, null, function () {
            $('#ia-voz-estado').text('Presiona el micrófono para hablar');
          });
        } else {
          $('#ia-voz-estado').text('Presiona el micrófono para hablar');
        }

        cargarHistorial();
      },
      error: function () {
        quitarTyping();
        setEnviando(false);
        $('#ia-voz-estado').text('Presiona el micrófono para hablar');
        mostrarError('Error de conexión al procesar el audio.');
      },
    });
  }

  /* ── TTS (síntesis de voz) ── */
  /**
   * Solicita el audio de un mensaje al servidor y lo reproduce.
   *
   * @param {number}   mensajeId  ID del mensaje en BD
   * @param {jQuery}   $btn       Botón que inició la reproducción (opcional, para estado visual)
   * @param {Function} onEnd      Callback al terminar (opcional)
   */
  function reproducirTTS(mensajeId, $btn, onEnd) {
    pararAudio();

    if ($btn) {
      $btn.addClass('ia-play-activo').html('<i class="feather icon-stop-circle"></i>');
      $btn.off('click').on('click', function () {
        pararAudio();
        $btn.removeClass('ia-play-activo').html('<i class="feather icon-volume-2"></i>');
        $btn.off('click').on('click', function () { reproducirTTS(mensajeId, $btn); });
      });
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', URL_TTS, true);
    xhr.responseType = 'blob';
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function () {
      if (xhr.status === 200) {
        var url = URL.createObjectURL(xhr.response);
        audioActual = new Audio(url);
        audioActual.onended = function () {
          URL.revokeObjectURL(url);
          audioActual = null;
          if ($btn) {
            $btn.removeClass('ia-play-activo').html('<i class="feather icon-volume-2"></i>');
            $btn.off('click').on('click', function () { reproducirTTS(mensajeId, $btn); });
          }
          if (typeof onEnd === 'function') onEnd();
        };
        audioActual.play().catch(function () {
          /* autoplay bloqueado por el navegador — ignorar silenciosamente */
          if ($btn) $btn.removeClass('ia-play-activo').html('<i class="feather icon-volume-2"></i>');
          if (typeof onEnd === 'function') onEnd();
        });
      } else {
        if ($btn) $btn.removeClass('ia-play-activo').html('<i class="feather icon-volume-2"></i>');
        if (typeof onEnd === 'function') onEnd();
      }
    };

    xhr.onerror = function () {
      if ($btn) $btn.removeClass('ia-play-activo').html('<i class="feather icon-volume-2"></i>');
      if (typeof onEnd === 'function') onEnd();
    };

    xhr.send('mensaje_id=' + encodeURIComponent(mensajeId));
  }

  function pararAudio() {
    if (audioActual) {
      audioActual.pause();
      audioActual.src = '';
      audioActual = null;
    }
    // Resetear todos los botones de play
    $('.ia-play-btn.ia-play-activo')
      .removeClass('ia-play-activo')
      .html('<i class="feather icon-volume-2"></i>');
  }

  /* ── Helpers ── */
  function setEnviando(v) {
    estado.enviando = v;
    $('#ia-btn-send').prop('disabled', v);
    $('#ia-input').prop('disabled', v);
    $('#ia-btn-mic').prop('disabled', v);
  }

  function scrollAbajo() {
    var $m = $('#ia-messages');
    $m.scrollTop($m[0].scrollHeight);
  }

  function escHtml(str) {
    return $('<div>').text(str).html();
  }

  /**
   * Parser Markdown mínimo:
   * **negrita**, `código`, - listas, ## encabezados, párrafos separados por \n\n
   */
  function markdownSimple(texto) {
    var s = escHtml(texto);

    // URLs sueltas (ej. enlace de descarga de un informe PDF)
    s = s.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');

    // Encabezados ##
    s = s.replace(/^#{1,3} (.+)$/gm, '<strong>$1</strong>');

    // Negrita **texto**
    s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

    // Cursiva *texto*
    s = s.replace(/\*(.+?)\*/g, '<em>$1</em>');

    // Código inline `texto`
    s = s.replace(/`([^`]+)`/g, '<code>$1</code>');

    // Listas con guión "- item"
    s = s.replace(/^- (.+)$/gm, '<li>$1</li>');
    s = s.replace(/(<li>[\s\S]+?<\/li>)/g, '<ul>$1</ul>');
    s = s.replace(/<\/ul>\n?<ul>/g, '');

    // Listas numeradas "1. item"
    s = s.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');

    // Párrafos (doble salto de línea)
    s = s.replace(/\n\n+/g, '</p><p>');
    s = s.replace(/\n/g, '<br>');
    s = '<p>' + s + '</p>';

    // Limpiar p vacíos
    s = s.replace(/<p>\s*<\/p>/g, '');

    return s;
  }

})(jQuery);
