<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';
include './admin/classes/Desarrollo.php';
include './admin/classes/Secretarias.php';
$modulo = 'ALMA Asistente IA';
$ocultarWidgetIa = true;
?>
<link href="<?php echo Util::versionar('assets/css/metas_plan_desarrollo_gob360_v2.css'); ?>" rel="stylesheet">
<link href="<?php echo Util::versionar('assets/css/gobia_assistant.css'); ?>" rel="stylesheet">
<body class="gob360-development-goals gobia-voice-page">
<div class="loader-bg"><div class="loader-track"><div class="loader-fill"></div></div></div>
<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>
<div class="pcoded-main-container"><div class="pcoded-content">
<main class="gobia-voice-shell stark-interface" aria-labelledby="gobiaTitle">
<section class="gobia-hud-panel stark-console" data-hud-mode="idle">
  <header class="gobia-hud-header">
    <div>
      <span class="gobia-eyebrow"><i data-feather="cpu"></i> Inteligencia institucional · núcleo cognitivo</span>
      <h1 id="gobiaTitle">ALMA Asistente IA</h1>
      <p>Control por voz para consultar, analizar y orientar la gestión institucional de GOB360.</p>
    </div>
    <div class="gobia-system-state" id="gobiaSystemState"><span></span><b id="gobiaHudMode">SISTEMA EN ESPERA</b></div>
  </header>

  <div class="gobia-interface-grid stark-console__grid">
    <aside class="stark-search-rail" aria-label="Radar de búsqueda institucional">
      <div class="stark-rail__caption"><b>SCAN</b><span>RADAR DE DATOS</span><em>360°</em></div>
      <section class="stark-radar-station">
        <div class="stark-radar-station__head"><span>INSTITUTIONAL SEARCH</span><b>TRACKING</b></div>
        <canvas id="gobiaRadarCanvas" aria-label="Radar animado de búsqueda"></canvas>
        <div class="stark-radar-coordinates"><span>LAT 04.7110</span><span>LON -74.0721</span></div>
        <div class="stark-radar-target stark-radar-target--one"></div>
        <div class="stark-radar-target stark-radar-target--two"></div>
        <div class="stark-radar-target stark-radar-target--three"></div>
      </section>
      <section class="stark-binary-terminal">
        <header><span>DATA STREAM</span><b id="starkPacketRate">000 PKT/S</b></header>
        <div id="starkBinaryStream" class="stark-binary-stream" aria-hidden="true"></div>
        <footer><span>GOB360 CORE</span><i></i><span>ENCRYPTED</span></footer>
      </section>
    </aside>

    <section class="gobia-face-stage gobia-face-stage--premium stark-core" aria-label="Interfaz holográfica de ALMA">
      <div class="stark-core__topline"><span>ALMA // COGNITIVE ENGINE</span><b>ONLINE</b><span>GOB360 // CO</span></div>
      <div class="gobia-hud-grid"></div><div class="gobia-hud-particles"></div>
      <div class="gobia-orbit gobia-orbit--outer"></div><div class="gobia-orbit gobia-orbit--middle"></div>
      <div class="gobia-orbit gobia-orbit--inner"></div><div class="gobia-orbit gobia-orbit--pulse"></div>
      <div class="gobia-side-data gobia-side-data--left"><span>VISION AI</span><span>BIO SIGNAL</span><span>VOICE CORE</span><span>ANALYTICS</span></div>
      <div class="gobia-side-data gobia-side-data--right"><span>HUD ACTIVE</span><span>VOICE READY</span><span>NLP ONLINE</span><span>SESSION OK</span></div>
      <div class="gobia-face-frame gobia-face-frame--premium" id="gobiaFaceFrame">
        <div class="gobia-core-light"></div><div class="gobia-face-border"></div>
        <canvas id="gobiaFaceCanvas" class="gobia-face-canvas gobia-face-canvas--premium" aria-label="Rostro holográfico de ALMA"></canvas>
        <div class="gobia-scan-line"></div><div class="gobia-face-glow"></div><div class="gobia-face-reflection"></div>
      </div>

      <div class="stark-levels stark-levels--user" aria-hidden="true"><b>IN</b><div><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></div>
      <div class="stark-levels stark-levels--alma" aria-hidden="true"><b>AI</b><div><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></div>

      <div class="gobia-wave-deck" aria-label="Espectros de voz separados">
        <div class="gobia-wave-panel gobia-wave-panel--user">
          <div class="gobia-wave-panel__title"><span>ENTRADA · USUARIO</span><strong>VOICE INPUT</strong></div>
          <canvas id="gobiaUserWaveCanvas" class="gobia-wave-canvas" aria-label="Frecuencia de voz del usuario"></canvas>
        </div>
        <div class="gobia-wave-panel gobia-wave-panel--alma">
          <div class="gobia-wave-panel__title"><span>SALIDA · ALMA</span><strong>AI VOICE SPECTRUM</strong></div>
          <canvas id="gobiaWaveCanvas" class="gobia-wave-canvas" aria-label="Frecuencia de voz de ALMA"></canvas>
        </div>
      </div>

      <div class="gobia-data-strip stark-analysis" aria-hidden="true">
        <div class="gobia-search-feed"><span>Canal institucional sincronizado</span><span>Índices semánticos disponibles</span><span>Verificando fuentes y contexto</span><span>Motor analítico preparado</span><span>Telemetría de sesión estable</span></div>
      </div>

      <div class="gobia-status-copy" aria-live="polite">
        <strong id="gobiaStatusTitle">Pulsa el micrófono para comenzar</strong>
        <span id="gobiaStatusText">ALMA te saludará según la hora de Colombia y luego activará el micrófono.</span>
      </div>
      <div class="gobia-controls">
        <button type="button" id="gobiaMicButton" class="gobia-mic-control gobia-mic-control--premium" aria-pressed="false">
          <span class="gobia-mic-control__rings"></span><span class="gobia-mic-control__icon"><i data-feather="mic"></i></span><span class="gobia-mic-control__label">Activar micrófono</span>
        </button>
        <label class="gobia-continuous-control" for="gobiaContinuousMode">
          <input type="checkbox" id="gobiaContinuousMode" checked><span class="gobia-switch"></span>
          <span><strong>Conversación continua</strong><small>ALMA vuelve a escuchar después de responder</small></span>
        </label>
      </div>
    </section>

    <aside class="gobia-diagnostics stark-rail stark-rail--right" aria-label="Diagnóstico de la conversación">
      <div class="stark-rail__caption"><b>LIVE</b><span>DIAGNÓSTICO</span><em>04</em></div>
      <article><span><i data-feather="mic"></i></span><div><small>Micrófono</small><strong id="gobiaMicDiagnostic">Inactivo</strong></div></article>
      <article><span><i data-feather="radio"></i></span><div><small>Procesamiento</small><strong id="gobiaProcessDiagnostic">En espera</strong></div></article>
      <article><span><i data-feather="volume-2"></i></span><div><small>Salida de voz</small><strong id="gobiaVoiceDiagnostic">Preparada</strong></div></article>
      <article><span><i data-feather="shield"></i></span><div><small>Sesión</small><strong id="gobiaSessionDiagnostic">Protegida</strong></div></article>
    </aside>
  </div>
  <footer class="gobia-hud-footer">
    <span><i data-feather="info"></i> Para detener la escucha, vuelve a pulsar el micrófono.</span>
    <button type="button" id="gobiaStopButton" class="gobia-stop-button" disabled><i data-feather="square"></i> Detener</button>
  </footer>
</section>
</main>
<audio id="gobiaAudioPlayer" preload="auto" hidden></audio>
<div id="almaPdfPanel" class="alma-pdf-panel" hidden role="status" aria-live="polite">
  <button type="button" id="almaPdfCerrar" class="alma-pdf-panel__close" aria-label="Cerrar aviso de informe">&times;</button>
  <div class="alma-pdf-panel__row">
    <div class="alma-pdf-panel__icon"><i data-feather="file-text"></i></div>
    <div class="alma-pdf-panel__body">
      <strong>Informe generado</strong>
      <span>Tu PDF ya está listo para descargar.</span>
    </div>
  </div>
  <a id="almaPdfLink" class="alma-pdf-panel__action" href="#" target="_blank" rel="noopener"><i data-feather="download"></i> Abrir PDF</a>
</div>
</div></div>
<?php include 'admin/include/gerenic_script.php'; ?>
<script src="assets/js/vendor-all.min.js"></script><script src="assets/js/plugins/bootstrap.min.js"></script><script src="assets/js/pcoded.min.js"></script>
<script>window.ALMA_VOICE_CONFIG=Object.freeze({assistantName:'ALMA'});</script>
<script src="<?php echo Util::versionar('assets/js/gobia_voice_assistant_gob360.js'); ?>"></script>
<script src="<?php echo Util::versionar('assets/js/gobia_hud_visuals.js'); ?>"></script>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.feather)window.feather.replace();});</script>
</body></html>
