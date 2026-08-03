<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Desarrollo.php';
include './admin/classes/Secretarias.php';

$modulo = 'ALMA Asistente IA';

// Oculta el widget de chat flotante en esta vista: la interfaz de voz de esta página YA ES
// el asistente ALMA (mismo backend); mostrar ambos a la vez sería redundante. Ver
// admin/include/gerenic_script.php.
$ocultarWidgetIa = true;
?>

<link href="assets/css/metas_plan_desarrollo_gob360_v2.css" rel="stylesheet">
<link href="assets/css/gobia_assistant.css" rel="stylesheet">

<body class="gob360-development-goals gobia-voice-page">
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <main class="gobia-voice-shell" aria-labelledby="gobiaTitle">
        <section class="gobia-hud-panel">
          <header class="gobia-hud-header">
            <div>
              <span class="gobia-eyebrow">
                <i data-feather="cpu"></i>
                Inteligencia institucional
              </span>

              <h1 id="gobiaTitle">ALMA Asistente IA</h1>

              <p>
                Control por voz para consultar, analizar y orientar la gestión
                institucional de GOB360.
              </p>
            </div>

            <div class="gobia-system-state" id="gobiaSystemState">
              <span></span>
              Sistema disponible
            </div>
          </header>

          <div class="gobia-interface-grid">
            <aside class="gobia-telemetry" aria-label="Telemetría de ALMA">
              <article>
                <small>Canal</small>
                <strong>Voz bidireccional</strong>
                <span>Sin ventana de chat</span>
              </article>

              <article>
                <small>Comprensión</small>
                <strong>ElevenLabs Scribe</strong>
                <span>Transcripción del comando</span>
              </article>

              <article>
                <small>Respuesta</small>
                <strong>ElevenLabs Voice</strong>
                <span>Audio institucional</span>
              </article>

              <article>
                <small>Agente</small>
                <strong>Claude + GOB360</strong>
                <span>Sesión diaria por usuario</span>
              </article>
            </aside>

            <section
              class="gobia-face-stage gobia-face-stage--premium"
              aria-label="Interfaz holográfica de ALMA"
            >
              <div class="gobia-hud-grid"></div>
              <div class="gobia-hud-particles"></div>

              <div class="gobia-orbit gobia-orbit--outer"></div>
              <div class="gobia-orbit gobia-orbit--middle"></div>
              <div class="gobia-orbit gobia-orbit--inner"></div>
              <div class="gobia-orbit gobia-orbit--pulse"></div>

              <div class="gobia-side-data gobia-side-data--left">
                <span>VISION AI</span>
                <span>BIO SIGNAL</span>
                <span>VOICE CORE</span>
                <span>ANALYTICS</span>
              </div>

              <div class="gobia-side-data gobia-side-data--right">
                <span>HUD ACTIVE</span>
                <span>VOICE READY</span>
                <span>NLP ONLINE</span>
                <span>SESSION OK</span>
              </div>

              <div
                class="gobia-face-frame gobia-face-frame--premium"
                id="gobiaFaceFrame"
              >
                <div class="gobia-core-light"></div>
                <div class="gobia-face-border"></div>

                <canvas
                  id="gobiaFaceCanvas"
                  class="gobia-face-canvas gobia-face-canvas--premium"
                  aria-label="Rostro femenino holográfico de ALMA"
                ></canvas>

                <div class="gobia-scan-line"></div>
                <div class="gobia-face-glow"></div>
                <div class="gobia-face-reflection"></div>
              </div>

              <div class="gobia-wave-panel gobia-wave-panel--premium">
                <div class="gobia-wave-panel__title">
                  <span>VOICE FREQUENCY</span>
                  <strong>ALMA AUDIO SPECTRUM</strong>
                </div>

                <canvas
                  id="gobiaWaveCanvas"
                  class="gobia-wave-canvas"
                  aria-label="Frecuencia de voz de ALMA"
                ></canvas>
              </div>

              <div class="gobia-status-copy" aria-live="polite">
                <strong id="gobiaStatusTitle">
                  Pulsa el micrófono para comenzar
                </strong>

                <span id="gobiaStatusText">
                  ALMA te saludará según la hora de Colombia y luego activará el micrófono.
                </span>
              </div>

              <div class="gobia-controls">
                <button
                  type="button"
                  id="gobiaMicButton"
                  class="gobia-mic-control gobia-mic-control--premium"
                  aria-pressed="false"
                >
                  <span class="gobia-mic-control__rings"></span>

                  <span class="gobia-mic-control__icon">
                    <i data-feather="mic"></i>
                  </span>

                  <span class="gobia-mic-control__label">
                    Activar micrófono
                  </span>
                </button>

                <label
                  class="gobia-continuous-control"
                  for="gobiaContinuousMode"
                >
                  <input
                    type="checkbox"
                    id="gobiaContinuousMode"
                    checked
                  >

                  <span class="gobia-switch"></span>

                  <span>
                    <strong>Conversación continua</strong>
                    <small>
                      ALMA vuelve a escuchar después de responder
                    </small>
                  </span>
                </label>
              </div>
            </section>

            <aside
              class="gobia-diagnostics"
              aria-label="Diagnóstico de la conversación"
            >
              <article>
                <span><i data-feather="mic"></i></span>
                <div>
                  <small>Micrófono</small>
                  <strong id="gobiaMicDiagnostic">Inactivo</strong>
                </div>
              </article>

              <article>
                <span><i data-feather="radio"></i></span>
                <div>
                  <small>Procesamiento</small>
                  <strong id="gobiaProcessDiagnostic">En espera</strong>
                </div>
              </article>

              <article>
                <span><i data-feather="volume-2"></i></span>
                <div>
                  <small>Salida de voz</small>
                  <strong id="gobiaVoiceDiagnostic">Preparada</strong>
                </div>
              </article>

              <article>
                <span><i data-feather="shield"></i></span>
                <div>
                  <small>Sesión</small>
                  <strong id="gobiaSessionDiagnostic">Protegida</strong>
                </div>
              </article>
            </aside>
          </div>

          <footer class="gobia-hud-footer">
            <span>
              <i data-feather="info"></i>
              Para detener la escucha, vuelve a pulsar el micrófono.
            </span>

            <button
              type="button"
              id="gobiaStopButton"
              class="gobia-stop-button"
              disabled
            >
              <i data-feather="square"></i>
              Detener
            </button>
          </footer>
        </section>
      </main>

      <audio id="gobiaAudioPlayer" preload="auto" hidden></audio>

      <!-- Panel de entrega de informe PDF: oculto hasta que ALMA genera uno en la conversación -->
      <div id="almaPdfPanel" class="alma-pdf-panel" hidden>
        <i data-feather="file-text"></i>
        <span>Tu informe está listo.</span>
        <a id="almaPdfLink" href="#" target="_blank" rel="noopener">Abrir PDF</a>
        <button type="button" id="almaPdfCerrar" aria-label="Cerrar aviso de informe">&times;</button>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>

  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script>
    window.ALMA_VOICE_CONFIG = Object.freeze({
      assistantName: 'ALMA',
    });
  </script>

  <script src="<?php echo Util::versionar('assets/js/gobia_voice_assistant_gob360.js'); ?>"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.feather) {
        window.feather.replace();
      }
    });
  </script>
</body>
</html>
