<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reconocimiento de Voz IA</title>
  <script type="text/javascript" src="admin/js/recorder.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/mattdiamond/Recorderjs@master/dist/recorder.js"></script>
  <style>
    body {   font-family: "IBM Plex Sans", sans-serif !important;
  font-optical-sizing: auto;
     padding: 2em; text-align: center; 
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      flex-direction: column;
     }
   
    .texto-arriba {
      font-size: 1.2rem;
      margin-bottom: 15px;
      text-align: center;
      color: #333;
    }

    #botonGrabar {
      background-color: #000;
      color: #fff;
      font-size: 1.1rem;
      font-weight: 600;
      border: none;
      padding: 15px 25px;
      border-radius: 50px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    #botonGrabar:hover {
      background-color: #222;
      transform: scale(1.05);
    }

    #audioRespuesta {
      margin-top: 20px;
      width: 300px;
    }
  </style>
</head>
<body>
  <img src="assets/img/gob_negro.png" alt="">

  <div class="texto-arriba">Presiona, habla, suelta... <br> el sistema capta todo automáticamente.</div>

  <button onclick="iniciarGrabacion()" id="botonGrabar">🎤 Iniciar Grabación</button>

  <audio id="audioRespuesta" controls></audio>

  <script>
    let gumStream;
    let recorder;
    let input;
    let audioContext;
    let silenceTimer;
    let lastVolume = 0;

    function iniciarGrabacion() {
      document.getElementById("botonGrabar").disabled = true;

      navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        gumStream = stream;
        input = audioContext.createMediaStreamSource(stream);

        recorder = new Recorder(input, { numChannels: 1 });
        recorder.record();
        console.log("Grabación iniciada");

        detectarSilencio(input);
      }).catch(function(err) {
        console.error("No se pudo acceder al micrófono:", err);
      });
    }

    function detectarSilencio(source) {
      const analyser = audioContext.createAnalyser();
      analyser.fftSize = 2048;
      source.connect(analyser);

      const data = new Uint8Array(analyser.fftSize);

      function analizar() {
        analyser.getByteTimeDomainData(data);
        let max = 0;
        for (let i = 0; i < data.length; i++) {
          const volumen = Math.abs(data[i] - 128);
          if (volumen > max) max = volumen;
        }

        if (max < 5 && lastVolume >= 5) {
          clearTimeout(silenceTimer);
          silenceTimer = setTimeout(() => detenerGrabacion(), 1500);
        } else if (max >= 5) {
          clearTimeout(silenceTimer);
        }
        lastVolume = max;
        requestAnimationFrame(analizar);
      }

      analizar();
    }

   function detenerGrabacion() {
  recorder.stop();
  gumStream.getAudioTracks()[0].stop();

  recorder.exportWAV(function(blob) {
    const formData = new FormData();
    formData.append("audio", blob, "grabacion.wav");

    fetch("procesar_audio.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.text())
    .then(text => {
      console.log("Respuesta cruda:", text);

      if (text.trim() === "") {
        alert("No se recibió respuesta");
        document.getElementById("botonGrabar").disabled = false;
        return;
      }

      try {
        // 🧠 Buscamos solo el JSON dentro del texto completo
        const jsonMatch = text.match(/\{.*"audio"\s*:\s*".*?"\s*\}/s);

        if (!jsonMatch) {
          throw new Error("No se encontró JSON válido en la respuesta.");
        }

        const data = JSON.parse(jsonMatch[0]);

        if (data.audio) {
          const audioElement = document.getElementById("audioRespuesta");
          audioElement.src = data.audio;
          audioElement.play();
        } else {
          alert(data.error || "Error desconocido.");
        }
      } catch (err) {
        console.error("Error al interpretar JSON:", err);
        console.log("Contenido recibido:", text);
        alert("Respuesta inválida del servidor.");
      }
    })
    .catch(error => {
      console.error("Error al enviar el audio:", error);
      alert("Error al enviar el audio.");
    });

    document.getElementById("botonGrabar").disabled = false;
  });
}

  </script>
</body>
</html>
