/**
 * GOBIA Asistente IA - Control exclusivamente por voz.
 * Compatible con la vista gobia_asistente_voz_gob360.php.
 *
 * Flujo:
 * 1. Captura el micrófono con MediaRecorder.
 * 2. Envía el audio a ElevenLabs STT.
 * 3. Envía la transcripción al agente n8n mediante el proxy PHP.
 * 4. Solicita la voz a ElevenLabs TTS.
 * 5. Reproduce la respuesta con Web Audio y anima el rostro binario.
 */
(() => {
  'use strict';

  const CONFIG = Object.freeze({
    sttEndpoint: 'admin/api/gobia_elevenlabs_stt.php',
    queryEndpoint: 'admin/api/gobia_voice_query.php',
    ttsEndpoint: 'admin/api/gobia_elevenlabs_tts.php',
    language: 'es-CO',
    maxRecordingMs: 25000,
    waitForVoiceMs: 9000,
    silenceAfterVoiceMs: 1350,
    minimumRecordingMs: 650,
    minimumAudioBytes: 1200,
    minimumVoiceThreshold: 0.016,
    continuousRestartMs: 800,
    requestTimeoutMs: 150000,
  });

  const State = Object.freeze({
    IDLE: 'idle',
    LISTENING: 'listening',
    PROCESSING: 'processing',
    SPEAKING: 'speaking',
    ERROR: 'error',
  });

  function boot() {
    const ui = getUi();

    if (!ui.faceCanvas || !ui.waveCanvas || !ui.micButton) {
      console.error('[GOBIA] Faltan elementos obligatorios en la vista.');
      return;
    }

    const faceContext = ui.faceCanvas.getContext('2d');
    const waveContext = ui.waveCanvas.getContext('2d');

    if (!faceContext || !waveContext) {
      console.error('[GOBIA] El navegador no permite dibujar los canvas.');
      return;
    }

    const runtime = createRuntime(ui, faceContext, waveContext);
    runtime.init();
  }

  function getUi() {
    return {
      faceCanvas: document.getElementById('gobiaFaceCanvas'),
      waveCanvas: document.getElementById('gobiaWaveCanvas'),
      faceFrame: document.getElementById('gobiaFaceFrame'),
      audioFallback: document.getElementById('gobiaAudioPlayer'),
      micButton: document.getElementById('gobiaMicButton'),
      micLabel: document.querySelector('.gobia-mic-control__label'),
      stopButton: document.getElementById('gobiaStopButton'),
      continuous: document.getElementById('gobiaContinuousMode'),
      statusTitle: document.getElementById('gobiaStatusTitle'),
      statusText: document.getElementById('gobiaStatusText'),
      liveCaption: document.getElementById('gobiaLiveCaption'),
      systemState: document.getElementById('gobiaSystemState'),
      micDiagnostic: document.getElementById('gobiaMicDiagnostic'),
      processDiagnostic: document.getElementById('gobiaProcessDiagnostic'),
      voiceDiagnostic: document.getElementById('gobiaVoiceDiagnostic'),
      sessionDiagnostic: document.getElementById('gobiaSessionDiagnostic'),
    };
  }

  function createRuntime(ui, faceContext, waveContext) {
    let state = State.IDLE;
    let audioContext = null;
    let microphoneStream = null;
    let microphoneSource = null;
    let microphoneAnalyser = null;
    let outputAnalyser = null;
    let outputSource = null;
    let outputEndResolver = null;
    let fallbackMediaSource = null;
    let fallbackEndResolver = null;
    let fallbackObjectUrl = null;
    let activeAnalyser = null;

    let recorder = null;
    let chunks = [];
    let recorderMimeType = '';
    let recordingShouldProcess = false;
    let recordingStartedAt = 0;
    let voiceDetected = false;
    let silenceStartedAt = 0;
    let noiseFloor = 0.006;
    let monitorFrame = 0;
    let recordingTimer = 0;
    let restartTimer = 0;

    let activeController = null;
    let operationId = 0;
    let manuallyStopped = false;
    let assistantEnergy = 0;
    let microphoneEnergy = 0;

    const sessionId = getSessionId();
    const facePoints = buildFemaleBinaryFace();
    const hudParticles = buildHudParticles();
    const frequencyData = new Uint8Array(256);
    const timeDomainData = new Uint8Array(512);

    function init() {
      setText(ui.sessionDiagnostic, sessionId.slice(-8).toUpperCase());
      bindEvents();
      resizeCanvases();
      setState(
        State.IDLE,
        'Pulsa el micrófono para comenzar',
        'GOBIA funciona únicamente por voz. Habla después de activar el micrófono.'
      );
      requestAnimationFrame(animate);

      if (window.feather && typeof window.feather.replace === 'function') {
        window.feather.replace();
      }
    }

    function bindEvents() {
      ui.micButton.addEventListener('click', onMicClick);

      if (ui.stopButton) {
        ui.stopButton.addEventListener('click', stopEverything);
      }

      if (ui.audioFallback) {
        ui.audioFallback.addEventListener('ended', finishFallbackPlayback);
        ui.audioFallback.addEventListener('error', () => {
          handleError(
            new Error('El navegador no pudo reproducir el audio recibido.'),
            'No fue posible reproducir la respuesta.'
          );
        });
      }

      window.addEventListener('resize', resizeCanvases, { passive: true });
      window.addEventListener('beforeunload', cleanup);
      document.addEventListener('visibilitychange', () => {
        if (document.hidden && state === State.LISTENING) {
          stopListening(false);
        }
      });
    }

    async function onMicClick() {
      try {
        await unlockAudio();

        if (state === State.LISTENING) {
          stopListening(true);
          return;
        }

        if (state === State.PROCESSING) {
          cancelActiveRequest();
          setState(
            State.IDLE,
            'Consulta cancelada',
            'Pulsa el micrófono para realizar una nueva instrucción.'
          );
          return;
        }

        if (state === State.SPEAKING) {
          operationId += 1;
          stopOutput();
          window.setTimeout(() => startListening(), 140);
          return;
        }

        await startListening();
      } catch (error) {
        handleError(error, 'No fue posible activar el asistente de voz.');
      }
    }

    async function unlockAudio() {
      await ensureAudioContext();

      if (audioContext.state === 'suspended') {
        await audioContext.resume();
      }

      // Un buffer silencioso desbloquea Web Audio en navegadores móviles.
      const buffer = audioContext.createBuffer(1, 1, audioContext.sampleRate);
      const source = audioContext.createBufferSource();
      source.buffer = buffer;
      source.connect(audioContext.destination);
      source.start(0);
    }

    async function ensureAudioContext() {
      if (!audioContext) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;

        if (!AudioContextClass) {
          throw new Error('Este navegador no admite Web Audio.');
        }

        audioContext = new AudioContextClass();
        outputAnalyser = audioContext.createAnalyser();
        outputAnalyser.fftSize = 512;
        outputAnalyser.smoothingTimeConstant = 0.76;
        outputAnalyser.connect(audioContext.destination);
      }

      if (audioContext.state === 'suspended') {
        await audioContext.resume();
      }

      return audioContext;
    }

    async function ensureMicrophone() {
      const usableStream = microphoneStream
        && microphoneStream.getAudioTracks().some((track) => track.readyState === 'live');

      if (usableStream) {
        return microphoneStream;
      }

      if (!window.isSecureContext && location.hostname !== 'localhost') {
        throw new Error('El micrófono requiere que la página se abra mediante HTTPS.');
      }

      if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
        throw new Error('El navegador no permite acceder al micrófono.');
      }

      microphoneStream = await navigator.mediaDevices.getUserMedia({
        audio: {
          echoCancellation: true,
          noiseSuppression: true,
          autoGainControl: true,
          channelCount: 1,
        },
        video: false,
      });

      await ensureAudioContext();

      if (microphoneSource) {
        try {
          microphoneSource.disconnect();
        } catch (_) {
          // Ya estaba desconectado.
        }
      }

      microphoneAnalyser = audioContext.createAnalyser();
      microphoneAnalyser.fftSize = 512;
      microphoneAnalyser.smoothingTimeConstant = 0.62;
      microphoneSource = audioContext.createMediaStreamSource(microphoneStream);
      microphoneSource.connect(microphoneAnalyser);

      return microphoneStream;
    }

    async function startListening() {
      if (state === State.LISTENING || state === State.PROCESSING) {
        return;
      }

      manuallyStopped = false;
      clearRestartTimer();
      cancelActiveRequest();
      stopOutput();

      if (!window.MediaRecorder) {
        throw new Error('Este navegador no admite grabación de audio.');
      }

      const stream = await ensureMicrophone();
      const mimeType = chooseMimeType();

      chunks = [];
      recordingShouldProcess = false;
      voiceDetected = false;
      silenceStartedAt = 0;
      recordingStartedAt = performance.now();
      noiseFloor = 0.006;
      recorderMimeType = mimeType || 'audio/webm';

      try {
        recorder = mimeType
          ? new MediaRecorder(stream, { mimeType, audioBitsPerSecond: 96000 })
          : new MediaRecorder(stream);
      } catch (_) {
        recorder = new MediaRecorder(stream);
        recorderMimeType = recorder.mimeType || 'audio/webm';
      }

      recorder.addEventListener('dataavailable', (event) => {
        if (event.data && event.data.size > 0) {
          chunks.push(event.data);
        }
      });

      recorder.addEventListener('error', (event) => {
        handleError(
          event.error || new Error('Error de grabación.'),
          'El micrófono dejó de responder.'
        );
      });

      recorder.addEventListener('stop', onRecorderStopped, { once: true });
      recorder.start(200);
      activeAnalyser = microphoneAnalyser;
      setCaption('');
      setState(
        State.LISTENING,
        'Te escucho',
        'Habla con naturalidad. GOBIA enviará la instrucción cuando detecte silencio.'
      );

      monitorMicrophone();
      recordingTimer = window.setTimeout(
        () => stopListening(true),
        CONFIG.maxRecordingMs
      );
    }

    function chooseMimeType() {
      const types = [
        'audio/webm;codecs=opus',
        'audio/webm',
        'audio/ogg;codecs=opus',
        'audio/mp4',
      ];

      if (typeof MediaRecorder.isTypeSupported !== 'function') {
        return '';
      }

      return types.find((type) => MediaRecorder.isTypeSupported(type)) || '';
    }

    function monitorMicrophone() {
      if (state !== State.LISTENING || !microphoneAnalyser) {
        return;
      }

      const data = new Uint8Array(microphoneAnalyser.fftSize);
      microphoneAnalyser.getByteTimeDomainData(data);

      let total = 0;
      for (let index = 0; index < data.length; index += 1) {
        const sample = (data[index] - 128) / 128;
        total += sample * sample;
      }

      const rms = Math.sqrt(total / data.length);
      microphoneEnergy += (Math.min(1, rms * 11) - microphoneEnergy) * 0.32;

      const now = performance.now();
      const elapsed = now - recordingStartedAt;

      if (!voiceDetected && elapsed < 450) {
        noiseFloor = noiseFloor * 0.86 + rms * 0.14;
      }

      const threshold = Math.max(
        CONFIG.minimumVoiceThreshold,
        noiseFloor * 2.65
      );

      if (rms >= threshold) {
        voiceDetected = true;
        silenceStartedAt = 0;
      } else if (voiceDetected && elapsed >= CONFIG.minimumRecordingMs) {
        if (!silenceStartedAt) {
          silenceStartedAt = now;
        }

        if (now - silenceStartedAt >= CONFIG.silenceAfterVoiceMs) {
          stopListening(true);
          return;
        }
      } else if (!voiceDetected && elapsed >= CONFIG.waitForVoiceMs) {
        stopListening(false);
        return;
      }

      monitorFrame = requestAnimationFrame(monitorMicrophone);
    }

    function stopListening(processAudio) {
      clearRecordingTimers();

      if (!processAudio) {
        manuallyStopped = true;
      }

      if (recorder && recorder.state !== 'inactive') {
        try {
          recorder.requestData();
        } catch (_) {
          // Algunos navegadores no implementan requestData correctamente.
        }
        recordingShouldProcess = Boolean(processAudio);
        recorder.stop();
        return;
      }

      activeAnalyser = null;
      microphoneEnergy = 0;
      setState(
        State.IDLE,
        processAudio ? 'Procesando audio' : 'Micrófono detenido',
        processAudio
          ? 'Preparando la instrucción de voz.'
          : 'Pulsa el micrófono cuando quieras intentarlo nuevamente.'
      );
    }

    async function onRecorderStopped() {
      const shouldProcess = recordingShouldProcess;
      const elapsed = performance.now() - recordingStartedAt;
      const type = recorderMimeType || recorder?.mimeType || 'audio/webm';
      const audioBlob = new Blob(chunks, { type });

      chunks = [];
      activeAnalyser = null;
      microphoneEnergy = 0;
      recorder = null;
      recordingShouldProcess = false;

      if (
        !shouldProcess
        || manuallyStopped
        || !voiceDetected
        || elapsed < CONFIG.minimumRecordingMs
        || audioBlob.size < CONFIG.minimumAudioBytes
      ) {
        setState(
          State.IDLE,
          voiceDetected ? 'Micrófono detenido' : 'No detecté una instrucción',
          'Pulsa el micrófono, espera un instante y habla con claridad.'
        );
        return;
      }

      const thisOperation = ++operationId;

      try {
        setState(
          State.PROCESSING,
          'Interpretando tu instrucción',
          'ElevenLabs está transcribiendo el comando de voz.'
        );

        const transcript = await transcribe(audioBlob, thisOperation);
        assertCurrentOperation(thisOperation);

        if (!transcript) {
          throw new Error('No se reconoció una instrucción clara.');
        }

        setCaption(`Escuché: “${truncate(transcript, 180)}”`);
        setState(
          State.PROCESSING,
          'Consultando GOB360',
          'El agente GOBIA está preparando la respuesta institucional.'
        );

        const answer = await askAgent(transcript, thisOperation);
        assertCurrentOperation(thisOperation);

        if (!answer) {
          throw new Error('El agente no devolvió una respuesta utilizable.');
        }

        setCaption(`GOBIA: ${truncate(answer, 240)}`);
        await speak(answer, thisOperation);
        assertCurrentOperation(thisOperation);

        finishConversation();
      } catch (error) {
        if (isAbortError(error) || thisOperation !== operationId) {
          return;
        }
        handleError(error, 'No fue posible completar la consulta.');
      }
    }

    async function transcribe(blob, thisOperation) {
      const extension = extensionForMime(blob.type);
      const body = new FormData();
      body.append('audio', blob, `gobia-command.${extension}`);

      const response = await request(
        CONFIG.sttEndpoint,
        {
          method: 'POST',
          body,
          credentials: 'same-origin',
        },
        thisOperation
      );

      const payload = await readJson(response);

      if (!response.ok || payload.ok !== true) {
        throw new Error(payload.message || 'ElevenLabs no pudo transcribir el audio.');
      }

      return String(payload.text || '').trim();
    }

    async function askAgent(transcript, thisOperation) {
      const response = await request(
        CONFIG.queryEndpoint,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            action: 'sendMessage',
            chatInput: transcript,
            sessionId,
            language: CONFIG.language,
          }),
        },
        thisOperation
      );

      const payload = await readJson(response);

      if (!response.ok || payload.ok !== true) {
        throw new Error(payload.message || 'El agente GOBIA no respondió correctamente.');
      }

      return String(payload.response || '').trim();
    }

    async function speak(text, thisOperation) {
      setState(
        State.PROCESSING,
        'Generando respuesta por voz',
        'ElevenLabs está preparando la voz de GOBIA.'
      );

      const response = await request(
        CONFIG.ttsEndpoint,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ text }),
        },
        thisOperation
      );

      if (!response.ok) {
        const payload = await readJson(response);
        throw new Error(payload.message || 'ElevenLabs no pudo generar la voz.');
      }

      const audioBufferBytes = await response.arrayBuffer();
      assertCurrentOperation(thisOperation);

      if (!audioBufferBytes.byteLength) {
        throw new Error('La respuesta de voz llegó vacía.');
      }

      await playAudioBuffer(audioBufferBytes, thisOperation);
    }

    async function playAudioBuffer(bytes, thisOperation) {
      const context = await ensureAudioContext();
      stopOutput();

      try {
        const decoded = await context.decodeAudioData(bytes.slice(0));
        assertCurrentOperation(thisOperation);

        outputSource = context.createBufferSource();
        outputSource.buffer = decoded;
        outputSource.connect(outputAnalyser);
        activeAnalyser = outputAnalyser;

        setState(
          State.SPEAKING,
          'GOBIA responde',
          'La respuesta se reproduce mediante la voz de ElevenLabs.'
        );

        await new Promise((resolve, reject) => {
          let finished = false;

          const finish = () => {
            if (finished) return;
            finished = true;
            outputEndResolver = null;
            outputSource = null;
            activeAnalyser = null;
            assistantEnergy = 0;
            resolve();
          };

          outputEndResolver = finish;
          outputSource.onended = finish;

          try {
            outputSource.start(0);
          } catch (error) {
            outputEndResolver = null;
            reject(error);
          }
        });
      } catch (decodeError) {
        // Respaldo para navegadores que no decodifican MP3 mediante decodeAudioData.
        await playWithAudioElement(bytes, thisOperation, decodeError);
      }
    }

    async function playWithAudioElement(bytes, thisOperation, decodeError) {
      if (!ui.audioFallback) {
        throw decodeError;
      }

      if (fallbackObjectUrl) {
        URL.revokeObjectURL(fallbackObjectUrl);
      }

      fallbackObjectUrl = URL.createObjectURL(
        new Blob([bytes], { type: 'audio/mpeg' })
      );

      ui.audioFallback.src = fallbackObjectUrl;

      await ensureAudioContext();
      if (!fallbackMediaSource) {
        fallbackMediaSource = audioContext.createMediaElementSource(ui.audioFallback);
        fallbackMediaSource.connect(outputAnalyser);
      }
      activeAnalyser = outputAnalyser;

      setState(
        State.SPEAKING,
        'GOBIA responde',
        'La respuesta se reproduce mediante la voz de ElevenLabs.'
      );

      await new Promise((resolve, reject) => {
        let finished = false;

        const cleanupListeners = () => {
          ui.audioFallback.removeEventListener('ended', onEnded);
          ui.audioFallback.removeEventListener('error', onError);
        };
        const finish = () => {
          if (finished) return;
          finished = true;
          fallbackEndResolver = null;
          cleanupListeners();
          activeAnalyser = null;
          resolve();
        };
        const onEnded = () => finish();
        const onError = () => {
          if (finished) return;
          finished = true;
          fallbackEndResolver = null;
          cleanupListeners();
          reject(new Error('El navegador no pudo reproducir el audio generado.'));
        };

        fallbackEndResolver = finish;
        ui.audioFallback.addEventListener('ended', onEnded, { once: true });
        ui.audioFallback.addEventListener('error', onError, { once: true });

        ui.audioFallback.play().catch((error) => {
          if (finished) return;
          finished = true;
          fallbackEndResolver = null;
          cleanupListeners();
          reject(error);
        });
      });

      assertCurrentOperation(thisOperation);
    }

    function finishFallbackPlayback() {
      activeAnalyser = null;
      assistantEnergy = 0;
    }

    function finishConversation() {
      activeAnalyser = null;
      assistantEnergy = 0;

      if (ui.continuous && ui.continuous.checked && !manuallyStopped) {
        setState(
          State.IDLE,
          'Conversación continua activa',
          'GOBIA volverá a escuchar en un momento.'
        );

        restartTimer = window.setTimeout(() => {
          if (state === State.IDLE && !manuallyStopped) {
            startListening().catch((error) => {
              handleError(error, 'No fue posible reactivar el micrófono.');
            });
          }
        }, CONFIG.continuousRestartMs);
        return;
      }

      setState(
        State.IDLE,
        'Consulta finalizada',
        'Pulsa el micrófono para realizar otra consulta.'
      );
    }

    async function request(path, options, thisOperation) {
      assertCurrentOperation(thisOperation);
      cancelActiveRequest();

      activeController = new AbortController();
      const timeout = window.setTimeout(
        () => activeController?.abort(),
        CONFIG.requestTimeoutMs
      );

      try {
        return await fetch(new URL(path, document.baseURI), {
          ...options,
          signal: activeController.signal,
          cache: 'no-store',
        });
      } finally {
        window.clearTimeout(timeout);
        activeController = null;
      }
    }

    function cancelActiveRequest() {
      if (activeController) {
        activeController.abort();
        activeController = null;
      }
    }

    function assertCurrentOperation(thisOperation) {
      if (thisOperation !== operationId) {
        throw new DOMException('Operación cancelada.', 'AbortError');
      }
    }

    function stopOutput() {
      if (outputSource) {
        try {
          outputSource.stop(0);
          outputSource.disconnect();
        } catch (_) {
          // La fuente ya finalizó.
        }
      }

      if (outputEndResolver) {
        outputEndResolver();
      }
      outputSource = null;

      if (ui.audioFallback && !ui.audioFallback.paused) {
        ui.audioFallback.pause();
        ui.audioFallback.currentTime = 0;
      }
      if (fallbackEndResolver) {
        fallbackEndResolver();
      }

      activeAnalyser = null;
      assistantEnergy = 0;
    }

    function stopEverything() {
      manuallyStopped = true;
      operationId += 1;
      cancelActiveRequest();
      clearRestartTimer();

      if (state === State.LISTENING) {
        stopListening(false);
      }

      stopOutput();
      setCaption('');
      setState(
        State.IDLE,
        'GOBIA en espera',
        'Pulsa el micrófono para iniciar una nueva consulta.'
      );
    }

    function clearRecordingTimers() {
      if (monitorFrame) {
        cancelAnimationFrame(monitorFrame);
        monitorFrame = 0;
      }

      if (recordingTimer) {
        window.clearTimeout(recordingTimer);
        recordingTimer = 0;
      }
    }

    function clearRestartTimer() {
      if (restartTimer) {
        window.clearTimeout(restartTimer);
        restartTimer = 0;
      }
    }

    function setState(nextState, title, description) {
      state = nextState;

      toggleClass(ui.faceFrame, 'is-listening', nextState === State.LISTENING);
      toggleClass(ui.faceFrame, 'is-speaking', nextState === State.SPEAKING);
      toggleClass(ui.micButton, 'is-listening', nextState === State.LISTENING);
      toggleClass(ui.micButton, 'is-processing', nextState === State.PROCESSING);
      toggleClass(ui.micButton, 'is-speaking', nextState === State.SPEAKING);

      ui.micButton.setAttribute(
        'aria-pressed',
        String(nextState === State.LISTENING)
      );
      ui.micButton.disabled = nextState === State.PROCESSING;

      if (ui.stopButton) {
        ui.stopButton.disabled = nextState === State.IDLE;
      }

      setText(ui.statusTitle, title);
      setText(ui.statusText, description);

      const buttonLabels = {
        [State.IDLE]: 'Activar micrófono',
        [State.LISTENING]: 'Enviar instrucción',
        [State.PROCESSING]: 'Procesando...',
        [State.SPEAKING]: 'Interrumpir y hablar',
        [State.ERROR]: 'Reintentar',
      };
      setText(ui.micLabel, buttonLabels[nextState] || 'Activar micrófono');

      setText(
        ui.micDiagnostic,
        nextState === State.LISTENING ? 'Escuchando' : 'Inactivo'
      );
      setText(
        ui.processDiagnostic,
        nextState === State.PROCESSING
          ? 'Analizando'
          : nextState === State.SPEAKING
            ? 'Respuesta lista'
            : 'En espera'
      );
      setText(
        ui.voiceDiagnostic,
        nextState === State.SPEAKING ? 'Reproduciendo' : 'Preparada'
      );

      const systemLabels = {
        [State.IDLE]: 'Sistema disponible',
        [State.LISTENING]: 'Escucha activa',
        [State.PROCESSING]: 'Procesamiento activo',
        [State.SPEAKING]: 'Respuesta por voz',
        [State.ERROR]: 'Revisión requerida',
      };
      setSystemState(systemLabels[nextState] || 'Sistema disponible');
    }

    function setSystemState(label) {
      if (!ui.systemState) return;

      let dot = ui.systemState.querySelector('span');
      if (!dot) {
        dot = document.createElement('span');
      }

      ui.systemState.replaceChildren(dot, document.createTextNode(` ${label}`));
    }

    function setCaption(text) {
      if (!ui.liveCaption) return;

      const clean = String(text || '').trim();
      ui.liveCaption.hidden = clean === '';
      ui.liveCaption.textContent = clean;
    }

    function handleError(error, title) {
      if (isAbortError(error)) return;

      console.error('[GOBIA]', error);
      clearRecordingTimers();
      clearRestartTimer();
      stopOutput();
      activeAnalyser = null;
      microphoneEnergy = 0;
      assistantEnergy = 0;

      setState(
        State.ERROR,
        title,
        friendlyError(error)
      );
    }

    function cleanup() {
      manuallyStopped = true;
      operationId += 1;
      cancelActiveRequest();
      clearRecordingTimers();
      clearRestartTimer();
      stopOutput();

      if (microphoneStream) {
        microphoneStream.getTracks().forEach((track) => track.stop());
      }

      if (microphoneSource) {
        try {
          microphoneSource.disconnect();
        } catch (_) {
          // Ya estaba desconectado.
        }
      }

      if (fallbackObjectUrl) {
        URL.revokeObjectURL(fallbackObjectUrl);
      }
    }

    // ----------------------------------------------------------
    // INTERFAZ HOLOGRÁFICA DE GOBIA
    // Rostro femenino compuesto por números binarios y HUD
    // reactivo al micrófono y a la voz de ElevenLabs.
    // ----------------------------------------------------------
    function buildFemaleBinaryFace() {
      const points = [];
      let seed = 927451;

      const random = () => {
        seed = (seed * 1664525 + 1013904223) >>> 0;
        return seed / 4294967296;
      };

      const pushPoint = (x, y, type, options = {}) => {
        points.push({
          x,
          y,
          type,
          phase: random() * Math.PI * 2,
          digit: random() > 0.5 ? '1' : '0',
          size: options.size ?? (0.82 + random() * 0.42),
          depth: options.depth ?? random(),
          brightness: options.brightness ?? (0.68 + random() * 0.32),
        });
      };

      const ellipse = (
        cx,
        cy,
        rx,
        ry,
        count,
        type,
        start = 0,
        end = Math.PI * 2,
        options = {}
      ) => {
        for (let index = 0; index < count; index += 1) {
          const ratio = count <= 1 ? 0 : index / (count - 1);
          const angle = start + (end - start) * ratio;
          pushPoint(
            cx + Math.cos(angle) * rx,
            cy + Math.sin(angle) * ry,
            type,
            options
          );
        }
      };

      const curve = (anchors, samples, type, options = {}) => {
        for (let segment = 0; segment < anchors.length - 1; segment += 1) {
          const start = anchors[segment];
          const end = anchors[segment + 1];

          for (let index = 0; index < samples; index += 1) {
            const ratio = index / samples;
            const eased = ratio * ratio * (3 - 2 * ratio);

            pushPoint(
              start[0] + (end[0] - start[0]) * eased,
              start[1] + (end[1] - start[1]) * eased,
              type,
              options
            );
          }
        }

        const last = anchors[anchors.length - 1];
        pushPoint(last[0], last[1], type, options);
      };

      // Contorno exterior y cabello holográfico.
      ellipse(0.5, 0.492, 0.315, 0.410, 190, 'hair', 0, Math.PI * 2, {
        size: 0.96,
        brightness: 0.76,
      });

      curve(
        [
          [0.245, 0.285],
          [0.190, 0.380],
          [0.165, 0.545],
          [0.185, 0.730],
          [0.265, 0.905],
        ],
        29,
        'hair',
        { size: 0.95 }
      );

      curve(
        [
          [0.755, 0.285],
          [0.810, 0.380],
          [0.835, 0.545],
          [0.815, 0.730],
          [0.735, 0.905],
        ],
        29,
        'hair',
        { size: 0.95 }
      );

      curve(
        [
          [0.245, 0.300],
          [0.330, 0.185],
          [0.430, 0.130],
          [0.500, 0.118],
          [0.585, 0.140],
          [0.680, 0.205],
          [0.755, 0.305],
        ],
        24,
        'hair',
        { size: 1.02, brightness: 0.86 }
      );

      // Rostro, mejillas y mandíbula.
      ellipse(0.5, 0.492, 0.245, 0.337, 150, 'face', 0, Math.PI * 2, {
        size: 0.98,
        brightness: 0.88,
      });

      curve(
        [
          [0.305, 0.600],
          [0.325, 0.715],
          [0.390, 0.810],
          [0.455, 0.855],
          [0.500, 0.870],
          [0.545, 0.855],
          [0.610, 0.810],
          [0.675, 0.715],
          [0.695, 0.600],
        ],
        18,
        'jaw',
        { size: 1.03, brightness: 0.93 }
      );

      // Cejas.
      curve(
        [[0.326, 0.400], [0.380, 0.373], [0.438, 0.392]],
        22,
        'eyebrow',
        { size: 1.08, brightness: 1 }
      );

      curve(
        [[0.562, 0.392], [0.620, 0.373], [0.674, 0.400]],
        22,
        'eyebrow',
        { size: 1.08, brightness: 1 }
      );

      // Ojos e iris.
      ellipse(0.392, 0.467, 0.074, 0.028, 34, 'eye', 0, Math.PI * 2, {
        size: 1.12,
        brightness: 1,
      });

      ellipse(0.608, 0.467, 0.074, 0.028, 34, 'eye', 0, Math.PI * 2, {
        size: 1.12,
        brightness: 1,
      });

      ellipse(0.392, 0.467, 0.018, 0.018, 18, 'iris', 0, Math.PI * 2, {
        size: 1.18,
        brightness: 1,
      });

      ellipse(0.608, 0.467, 0.018, 0.018, 18, 'iris', 0, Math.PI * 2, {
        size: 1.18,
        brightness: 1,
      });

      // Nariz.
      curve(
        [
          [0.500, 0.482],
          [0.487, 0.535],
          [0.475, 0.585],
          [0.487, 0.612],
          [0.500, 0.618],
        ],
        20,
        'nose',
        { size: 0.98, brightness: 0.91 }
      );

      curve(
        [[0.500, 0.618], [0.528, 0.612], [0.542, 0.603]],
        13,
        'nose',
        { size: 0.98, brightness: 0.91 }
      );

      // Labios.
      curve(
        [
          [0.405, 0.674],
          [0.447, 0.650],
          [0.485, 0.658],
          [0.500, 0.666],
          [0.515, 0.658],
          [0.553, 0.650],
          [0.595, 0.674],
        ],
        19,
        'upperLip',
        { size: 1.10, brightness: 1 }
      );

      curve(
        [
          [0.405, 0.674],
          [0.450, 0.700],
          [0.485, 0.710],
          [0.500, 0.712],
          [0.515, 0.710],
          [0.550, 0.700],
          [0.595, 0.674],
        ],
        19,
        'lowerLip',
        { size: 1.10, brightness: 1 }
      );

      // Cuello digital.
      curve(
        [[0.405, 0.815], [0.400, 0.910], [0.370, 0.980]],
        24,
        'neck',
        { size: 0.90, brightness: 0.68 }
      );

      curve(
        [[0.595, 0.815], [0.600, 0.910], [0.630, 0.980]],
        24,
        'neck',
        { size: 0.90, brightness: 0.68 }
      );

      // Volumen interior del rostro con distribución elíptica.
      for (let index = 0; index < 440; index += 1) {
        const angle = random() * Math.PI * 2;
        const radius = Math.sqrt(random());
        const x = 0.5 + Math.cos(angle) * 0.216 * radius;
        const y = 0.505 + Math.sin(angle) * 0.302 * radius;

        // Deja una zona ligeramente más limpia alrededor de ojos y boca.
        const leftEyeDistance = Math.hypot((x - 0.392) / 0.095, (y - 0.467) / 0.055);
        const rightEyeDistance = Math.hypot((x - 0.608) / 0.095, (y - 0.467) / 0.055);
        const mouthDistance = Math.hypot((x - 0.5) / 0.13, (y - 0.682) / 0.075);

        if (
          leftEyeDistance < 1
          || rightEyeDistance < 1
          || mouthDistance < 0.82
        ) {
          continue;
        }

        pushPoint(x, y, 'skin', {
          size: 0.70 + random() * 0.42,
          brightness: 0.48 + random() * 0.44,
          depth: random(),
        });
      }

      // Líneas tecnológicas laterales integradas al rostro.
      for (let row = 0; row < 8; row += 1) {
        const y = 0.315 + row * 0.065;
        curve(
          [[0.265, y], [0.300, y + 0.004], [0.330, y]],
          8,
          'interface',
          { size: 0.82, brightness: 0.72 }
        );
        curve(
          [[0.735, y], [0.700, y + 0.004], [0.670, y]],
          8,
          'interface',
          { size: 0.82, brightness: 0.72 }
        );
      }

      return points;
    }

    function buildHudParticles() {
      const particles = [];
      let seed = 745631;

      const random = () => {
        seed = (seed * 1103515245 + 12345) >>> 0;
        return seed / 4294967296;
      };

      for (let index = 0; index < 92; index += 1) {
        particles.push({
          x: random(),
          y: random(),
          radius: 0.55 + random() * 1.55,
          phase: random() * Math.PI * 2,
          speed: 0.12 + random() * 0.42,
          alpha: 0.12 + random() * 0.42,
          digit: random() > 0.5 ? '1' : '0',
        });
      }

      return particles;
    }

    function animate(timestamp) {
      resizeCanvases();
      drawFace(timestamp);
      drawWave(timestamp);
      requestAnimationFrame(animate);
    }

    function resizeCanvases() {
      resizeCanvas(ui.faceCanvas, faceContext);
      resizeCanvas(ui.waveCanvas, waveContext);
    }

    function resizeCanvas(canvas, context) {
      const rect = canvas.getBoundingClientRect();
      const ratio = Math.min(window.devicePixelRatio || 1, 2);
      const cssWidth = Math.max(1, rect.width);
      const cssHeight = Math.max(1, rect.height);
      const realWidth = Math.round(cssWidth * ratio);
      const realHeight = Math.round(cssHeight * ratio);

      if (canvas.width !== realWidth || canvas.height !== realHeight) {
        canvas.width = realWidth;
        canvas.height = realHeight;
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
      }
    }

    function readEnergy() {
      if (!activeAnalyser) {
        assistantEnergy *= 0.90;
        return state === State.LISTENING
          ? microphoneEnergy
          : assistantEnergy;
      }

      activeAnalyser.getByteFrequencyData(frequencyData);
      const limit = Math.min(96, frequencyData.length);
      let total = 0;

      for (let index = 3; index < limit; index += 1) {
        total += frequencyData[index];
      }

      const measured = total / Math.max(1, (limit - 3) * 255);

      if (state === State.SPEAKING) {
        assistantEnergy += (measured - assistantEnergy) * 0.35;
        return assistantEnergy;
      }

      if (state === State.LISTENING) {
        return Math.max(microphoneEnergy, measured);
      }

      assistantEnergy *= 0.90;
      return assistantEnergy;
    }

    function drawSegmentedRing(
      context,
      centerX,
      centerY,
      radius,
      rotation,
      segmentCount,
      visibleRatio,
      color,
      lineWidth
    ) {
      context.save();
      context.translate(centerX, centerY);
      context.rotate(rotation);
      context.strokeStyle = color;
      context.lineWidth = lineWidth;
      context.lineCap = 'round';

      const segmentAngle = (Math.PI * 2) / segmentCount;
      const visibleAngle = segmentAngle * visibleRatio;

      for (let index = 0; index < segmentCount; index += 1) {
        const startAngle = index * segmentAngle;
        context.beginPath();
        context.arc(0, 0, radius, startAngle, startAngle + visibleAngle);
        context.stroke();
      }

      context.restore();
    }

    function drawHudCorners(context, width, height, energy, timestamp) {
      const margin = Math.max(14, Math.min(width, height) * 0.045);
      const corner = Math.max(20, Math.min(width, height) * 0.07);
      const alpha = 0.16 + energy * 0.34;

      context.save();
      context.strokeStyle = `rgba(66,233,255,${alpha})`;
      context.lineWidth = 1.15;
      context.shadowColor = 'rgba(66,233,255,0.35)';
      context.shadowBlur = 6;

      const drawCorner = (x, y, horizontalDirection, verticalDirection) => {
        context.beginPath();
        context.moveTo(x + horizontalDirection * corner, y);
        context.lineTo(x, y);
        context.lineTo(x, y + verticalDirection * corner);
        context.stroke();
      };

      drawCorner(margin, margin, 1, 1);
      drawCorner(width - margin, margin, -1, 1);
      drawCorner(margin, height - margin, 1, -1);
      drawCorner(width - margin, height - margin, -1, -1);

      const pulse = 0.42 + Math.sin(timestamp * 0.0035) * 0.18;
      context.fillStyle = `rgba(73,246,189,${pulse})`;
      context.shadowBlur = 10;

      [
        [margin, margin],
        [width - margin, margin],
        [margin, height - margin],
        [width - margin, height - margin],
      ].forEach(([x, y]) => {
        context.beginPath();
        context.arc(x, y, 2.2, 0, Math.PI * 2);
        context.fill();
      });

      context.restore();
    }

    function drawHudGrid(context, width, height, energy) {
      const gridSize = Math.max(24, Math.min(width, height) * 0.065);

      context.save();
      context.lineWidth = 1;
      context.strokeStyle = `rgba(66,233,255,${0.025 + energy * 0.035})`;

      for (let x = 0; x <= width; x += gridSize) {
        context.beginPath();
        context.moveTo(x, 0);
        context.lineTo(x, height);
        context.stroke();
      }

      for (let y = 0; y <= height; y += gridSize) {
        context.beginPath();
        context.moveTo(0, y);
        context.lineTo(width, y);
        context.stroke();
      }

      context.restore();
    }

    function drawHudParticles(context, width, height, timestamp, energy) {
      context.save();

      hudParticles.forEach((particle, index) => {
        const drift = ((timestamp * 0.000015 * particle.speed) + particle.y) % 1;
        const x = particle.x * width
          + Math.sin(timestamp * 0.00045 + particle.phase) * 8;
        const y = drift * height;
        const pulse = 0.56
          + Math.sin(timestamp * 0.002 + particle.phase) * 0.32;
        const alpha = clamp(
          particle.alpha * pulse + energy * 0.12,
          0.04,
          0.62
        );

        if (index % 3 === 0) {
          context.font = `${7 + particle.radius * 2}px ui-monospace, SFMono-Regular, Menlo, Consolas, monospace`;
          context.textAlign = 'center';
          context.textBaseline = 'middle';
          context.fillStyle = `rgba(66,233,255,${alpha})`;
          context.fillText(particle.digit, x, y);
        } else {
          context.fillStyle = index % 2 === 0
            ? `rgba(66,233,255,${alpha})`
            : `rgba(73,246,189,${alpha * 0.88})`;
          context.beginPath();
          context.arc(x, y, particle.radius, 0, Math.PI * 2);
          context.fill();
        }
      });

      context.restore();
    }

    function drawFaceWireframe(
      context,
      width,
      height,
      timestamp,
      energy,
      mouthOpen,
      blinkAmount
    ) {
      const point = (x, y) => [x * width, y * height];
      const drawCurve = (coordinates, color, lineWidth, glow = 0) => {
        if (!coordinates.length) return;

        context.save();
        context.beginPath();

        coordinates.forEach((coordinate, index) => {
          const [x, y] = point(coordinate[0], coordinate[1]);
          if (index === 0) context.moveTo(x, y);
          else context.lineTo(x, y);
        });

        context.strokeStyle = color;
        context.lineWidth = lineWidth;
        context.lineJoin = 'round';
        context.lineCap = 'round';
        context.shadowColor = color;
        context.shadowBlur = glow;
        context.stroke();
        context.restore();
      };

      drawCurve(
        [
          [0.500, 0.155],
          [0.405, 0.170],
          [0.330, 0.225],
          [0.285, 0.330],
          [0.270, 0.505],
          [0.300, 0.665],
          [0.365, 0.790],
          [0.435, 0.850 + mouthOpen * 0.10],
          [0.500, 0.875 + mouthOpen * 0.14],
          [0.565, 0.850 + mouthOpen * 0.10],
          [0.635, 0.790],
          [0.700, 0.665],
          [0.730, 0.505],
          [0.715, 0.330],
          [0.670, 0.225],
          [0.595, 0.170],
          [0.500, 0.155],
        ],
        `rgba(66,233,255,${0.18 + energy * 0.38})`,
        1.25,
        7 + energy * 12
      );

      const eyeHeight = 0.022 * (1 - blinkAmount * 0.92);

      drawCurve(
        [
          [0.323, 0.466],
          [0.360, 0.448 - eyeHeight],
          [0.405, 0.449 - eyeHeight * 0.35],
          [0.451, 0.466],
          [0.405, 0.472 + eyeHeight * 0.20],
          [0.360, 0.472 + eyeHeight * 0.45],
          [0.323, 0.466],
        ],
        `rgba(165,250,255,${0.55 + energy * 0.35})`,
        1.45,
        9 + energy * 15
      );

      drawCurve(
        [
          [0.549, 0.466],
          [0.595, 0.449 - eyeHeight * 0.35],
          [0.640, 0.448 - eyeHeight],
          [0.677, 0.466],
          [0.640, 0.472 + eyeHeight * 0.45],
          [0.595, 0.472 + eyeHeight * 0.20],
          [0.549, 0.466],
        ],
        `rgba(165,250,255,${0.55 + energy * 0.35})`,
        1.45,
        9 + energy * 15
      );

      drawCurve(
        [
          [0.500, 0.490],
          [0.486, 0.555],
          [0.477, 0.600],
          [0.500, 0.618],
          [0.533, 0.608],
        ],
        `rgba(66,233,255,${0.21 + energy * 0.30})`,
        1.05,
        4
      );

      const mouthY = 0.675;
      drawCurve(
        [
          [0.405, mouthY],
          [0.452, mouthY - 0.018],
          [0.500, mouthY - 0.006],
          [0.548, mouthY - 0.018],
          [0.595, mouthY],
        ],
        `rgba(114,245,255,${0.56 + energy * 0.38})`,
        1.35,
        8 + energy * 13
      );

      drawCurve(
        [
          [0.405, mouthY],
          [0.452, mouthY + 0.017 + mouthOpen],
          [0.500, mouthY + 0.025 + mouthOpen * 1.12],
          [0.548, mouthY + 0.017 + mouthOpen],
          [0.595, mouthY],
        ],
        `rgba(73,246,189,${0.44 + energy * 0.40})`,
        1.35,
        8 + energy * 13
      );

      // Iris y núcleo ocular.
      const irisPulse = 2.1 + energy * 3.6;
      context.save();
      context.fillStyle = `rgba(218,255,255,${0.76 + energy * 0.22})`;
      context.shadowColor = 'rgba(66,233,255,0.95)';
      context.shadowBlur = 12 + energy * 20;

      [[0.392, 0.466], [0.608, 0.466]].forEach(([x, y]) => {
        context.beginPath();
        context.arc(
          x * width,
          y * height,
          Math.max(1.6, irisPulse),
          0,
          Math.PI * 2
        );
        context.fill();
      });

      context.restore();

      // Líneas de análisis facial.
      context.save();
      context.strokeStyle = `rgba(76,125,255,${0.10 + energy * 0.20})`;
      context.lineWidth = 0.8;

      for (let row = 0; row < 6; row += 1) {
        const y = (0.345 + row * 0.075) * height;
        const leftStart = 0.250 * width;
        const leftEnd = 0.315 * width;
        const rightStart = 0.685 * width;
        const rightEnd = 0.750 * width;

        context.beginPath();
        context.moveTo(leftStart, y);
        context.lineTo(leftEnd, y + Math.sin(timestamp * 0.003 + row) * 1.4);
        context.stroke();

        context.beginPath();
        context.moveTo(rightStart, y);
        context.lineTo(rightEnd, y + Math.sin(timestamp * 0.003 + row) * 1.4);
        context.stroke();
      }

      context.restore();
    }

    function drawFace(timestamp) {
      const width = ui.faceCanvas.getBoundingClientRect().width;
      const height = ui.faceCanvas.getBoundingClientRect().height;
      const energy = readEnergy();
      const speaking = state === State.SPEAKING;
      const listening = state === State.LISTENING;
      const processing = state === State.PROCESSING;
      const centerX = width / 2;
      const centerY = height / 2;
      const scale = Math.min(width, height);

      faceContext.clearRect(0, 0, width, height);

      const background = faceContext.createRadialGradient(
        centerX,
        centerY * 0.94,
        scale * 0.04,
        centerX,
        centerY * 0.94,
        scale * 0.62
      );
      background.addColorStop(
        0,
        `rgba(66,233,255,${0.075 + energy * 0.12})`
      );
      background.addColorStop(
        0.48,
        `rgba(76,125,255,${0.035 + energy * 0.07})`
      );
      background.addColorStop(1, 'rgba(0,0,0,0)');

      faceContext.fillStyle = background;
      faceContext.fillRect(0, 0, width, height);

      drawHudGrid(faceContext, width, height, energy);
      drawHudParticles(faceContext, width, height, timestamp, energy);
      drawHudCorners(faceContext, width, height, energy, timestamp);

      // Aros holográficos segmentados.
      const baseRadius = scale * 0.365;
      drawSegmentedRing(
        faceContext,
        centerX,
        centerY * 0.96,
        baseRadius,
        timestamp * 0.00013,
        24,
        0.57,
        `rgba(66,233,255,${0.12 + energy * 0.26})`,
        1.25
      );

      drawSegmentedRing(
        faceContext,
        centerX,
        centerY * 0.96,
        baseRadius * 1.12,
        -timestamp * 0.00009,
        38,
        0.42,
        `rgba(76,125,255,${0.10 + energy * 0.20})`,
        1
      );

      drawSegmentedRing(
        faceContext,
        centerX,
        centerY * 0.96,
        baseRadius * 1.24,
        timestamp * 0.00006,
        52,
        0.24,
        `rgba(73,246,189,${0.07 + energy * 0.17})`,
        0.85
      );

      // Indicadores laterales tipo HUD.
      faceContext.save();
      faceContext.font = '700 8px ui-monospace, SFMono-Regular, Menlo, Consolas, monospace';
      faceContext.textBaseline = 'middle';
      faceContext.fillStyle = `rgba(164,246,255,${0.45 + energy * 0.24})`;
      faceContext.shadowColor = 'rgba(66,233,255,0.35)';
      faceContext.shadowBlur = 4;

      const stateLabel = speaking
        ? 'VOICE OUTPUT'
        : listening
          ? 'VOICE INPUT'
          : processing
            ? 'NEURAL PROCESS'
            : 'SYSTEM READY';

      faceContext.textAlign = 'left';
      faceContext.fillText(stateLabel, 18, 22);
      faceContext.fillText(
        `ENERGY ${String(Math.round(energy * 100)).padStart(3, '0')}%`,
        18,
        36
      );

      faceContext.textAlign = 'right';
      faceContext.fillText('GOBIA // HUD', width - 18, 22);
      faceContext.fillText(
        `FRAME ${String(Math.floor(timestamp / 16) % 10000).padStart(4, '0')}`,
        width - 18,
        36
      );
      faceContext.restore();

      const idleTilt = Math.sin(timestamp * 0.00115) * 0.0026;
      const speechTilt = speaking
        ? Math.sin(timestamp * 0.0085) * energy * 0.012
        : listening
          ? Math.sin(timestamp * 0.0034) * microphoneEnergy * 0.005
          : 0;

      const mouthOpen = speaking
        ? Math.min(0.058, 0.006 + energy * 0.115)
        : 0;

      const headLift = speaking
        ? Math.sin(timestamp * 0.0105) * energy * scale * 0.0045
        : Math.sin(timestamp * 0.0017) * scale * 0.0016;

      const blinkCycle = (timestamp * 0.001) % 6.25;
      const blinkAmount = blinkCycle > 5.78
        ? Math.sin(((blinkCycle - 5.78) / 0.47) * Math.PI)
        : 0;

      faceContext.save();
      faceContext.translate(centerX, centerY + headLift);
      faceContext.rotate(idleTilt + speechTilt);
      faceContext.translate(-centerX, -centerY);

      // Halo de rostro.
      const faceHalo = faceContext.createRadialGradient(
        centerX,
        centerY,
        scale * 0.04,
        centerX,
        centerY,
        scale * 0.37
      );
      faceHalo.addColorStop(
        0,
        `rgba(66,233,255,${0.065 + energy * 0.16})`
      );
      faceHalo.addColorStop(
        0.55,
        `rgba(76,125,255,${0.025 + energy * 0.09})`
      );
      faceHalo.addColorStop(1, 'rgba(0,0,0,0)');
      faceContext.fillStyle = faceHalo;
      faceContext.fillRect(0, 0, width, height);

      facePoints.forEach((point, index) => {
        let x = point.x;
        let y = point.y;

        if (point.type === 'lowerLip') {
          y += mouthOpen;
        }

        if (point.type === 'upperLip') {
          y -= mouthOpen * 0.15;
        }

        if (point.type === 'jaw') {
          y += mouthOpen * 0.22;
        }

        if (
          point.type === 'skin'
          && point.y > 0.635
          && Math.abs(point.x - 0.5) < 0.145
        ) {
          y += mouthOpen * 0.10;
        }

        const pulse = speaking
          ? Math.sin(timestamp * 0.012 + point.phase) * energy * 0.0030
          : listening
            ? Math.sin(timestamp * 0.006 + point.phase) * microphoneEnergy * 0.0017
            : Math.sin(timestamp * 0.0021 + point.phase) * 0.00072;

        const depthShift = (point.depth - 0.5) * energy * 0.0015;
        x += pulse + depthShift;
        y += pulse * 0.70;

        const px = x * width;
        const py = y * height;

        const featureTypes = [
          'eye',
          'iris',
          'eyebrow',
          'nose',
          'upperLip',
          'lowerLip',
          'jaw',
        ];

        const feature = featureTypes.includes(point.type);
        const hair = point.type === 'hair';
        const interfacePoint = point.type === 'interface';
        const neck = point.type === 'neck';

        const flicker = 0.76
          + Math.sin(timestamp * 0.004 + point.phase) * 0.17;

        const baseAlpha = feature
          ? 0.98
          : interfacePoint
            ? 0.67
            : hair
              ? 0.68
              : neck
                ? 0.48
                : 0.47 + point.depth * 0.18;

        const stateBoost = speaking
          ? energy * 0.29
          : listening
            ? microphoneEnergy * 0.25
            : processing
              ? 0.08 + Math.sin(timestamp * 0.006 + point.phase) * 0.06
              : 0;

        const alpha = clamp(
          baseAlpha * flicker * point.brightness + stateBoost,
          0.10,
          1
        );

        const fontScale = feature
          ? 0.0195
          : hair
            ? 0.0160
            : interfacePoint
              ? 0.0130
              : neck
                ? 0.0132
                : 0.0142;

        const fontSize = Math.max(
          6.5,
          scale * fontScale * point.size
        );

        if (
          (speaking || listening || processing)
          && (index + Math.floor(timestamp / 78)) % 19 === 0
        ) {
          point.digit = point.digit === '0' ? '1' : '0';
        }

        let red = 66;
        let green = 233;
        let blue = 255;

        if (hair || neck) {
          red = 76;
          green = 125;
          blue = 255;
        } else if (
          point.type === 'lowerLip'
          || point.type === 'upperLip'
          || point.type === 'jaw'
        ) {
          red = 73;
          green = 246;
          blue = 189;
        } else if (interfacePoint) {
          red = 168;
          green = 140;
          blue = 255;
        }

        faceContext.font = `700 ${fontSize}px ui-monospace, SFMono-Regular, Menlo, Consolas, monospace`;
        faceContext.textAlign = 'center';
        faceContext.textBaseline = 'middle';
        faceContext.fillStyle = `rgba(${red},${green},${blue},${alpha})`;
        faceContext.shadowColor = `rgba(${red},${green},${blue},${0.12 + energy * 0.56})`;
        faceContext.shadowBlur = feature
          ? 8 + energy * 18
          : 2.5 + energy * 9;

        // Simula el parpadeo reduciendo la altura visual del ojo.
        if (
          (point.type === 'eye' || point.type === 'iris')
          && blinkAmount > 0.02
        ) {
          faceContext.save();
          faceContext.translate(px, py);
          faceContext.scale(1, Math.max(0.08, 1 - blinkAmount * 0.92));
          faceContext.fillText(point.digit, 0, 0);
          faceContext.restore();
        } else {
          faceContext.fillText(point.digit, px, py);
        }
      });

      drawFaceWireframe(
        faceContext,
        width,
        height,
        timestamp,
        energy,
        mouthOpen,
        blinkAmount
      );

      faceContext.restore();
      faceContext.shadowBlur = 0;

      // Línea de escaneo.
      const scanProgress = (timestamp * 0.000115) % 1;
      const scanY = height * (0.12 + scanProgress * 0.76);
      const scanGradient = faceContext.createLinearGradient(
        0,
        scanY,
        width,
        scanY
      );
      scanGradient.addColorStop(0, 'rgba(66,233,255,0)');
      scanGradient.addColorStop(
        0.18,
        `rgba(66,233,255,${0.08 + energy * 0.12})`
      );
      scanGradient.addColorStop(
        0.50,
        `rgba(213,255,255,${0.28 + energy * 0.34})`
      );
      scanGradient.addColorStop(
        0.82,
        `rgba(73,246,189,${0.08 + energy * 0.12})`
      );
      scanGradient.addColorStop(1, 'rgba(73,246,189,0)');

      faceContext.save();
      faceContext.strokeStyle = scanGradient;
      faceContext.lineWidth = 1.1;
      faceContext.shadowColor = 'rgba(66,233,255,0.65)';
      faceContext.shadowBlur = 8;
      faceContext.beginPath();
      faceContext.moveTo(width * 0.11, scanY);
      faceContext.lineTo(width * 0.89, scanY);
      faceContext.stroke();
      faceContext.restore();

      // Núcleo inferior reactivo, estilo reactor holográfico.
      const coreX = centerX;
      const coreY = height * 0.915;
      const coreRadius = Math.max(4, scale * (0.012 + energy * 0.010));
      const coreGradient = faceContext.createRadialGradient(
        coreX,
        coreY,
        0,
        coreX,
        coreY,
        coreRadius * 4.2
      );
      coreGradient.addColorStop(
        0,
        `rgba(236,255,255,${0.86 + energy * 0.12})`
      );
      coreGradient.addColorStop(
        0.24,
        `rgba(66,233,255,${0.58 + energy * 0.28})`
      );
      coreGradient.addColorStop(
        0.58,
        `rgba(76,125,255,${0.19 + energy * 0.20})`
      );
      coreGradient.addColorStop(1, 'rgba(0,0,0,0)');

      faceContext.fillStyle = coreGradient;
      faceContext.beginPath();
      faceContext.arc(coreX, coreY, coreRadius * 4.2, 0, Math.PI * 2);
      faceContext.fill();

      drawSegmentedRing(
        faceContext,
        coreX,
        coreY,
        coreRadius * 2.65,
        timestamp * 0.0012,
        12,
        0.52,
        `rgba(213,255,255,${0.30 + energy * 0.48})`,
        1
      );
    }

    function drawWave(timestamp) {
      const width = ui.waveCanvas.getBoundingClientRect().width;
      const height = ui.waveCanvas.getBoundingClientRect().height;
      const centerY = height * 0.48;

      waveContext.clearRect(0, 0, width, height);

      const background = waveContext.createLinearGradient(0, 0, width, 0);
      background.addColorStop(0, 'rgba(76,125,255,0)');
      background.addColorStop(0.24, 'rgba(66,233,255,0.055)');
      background.addColorStop(0.50, 'rgba(66,233,255,0.090)');
      background.addColorStop(0.76, 'rgba(73,246,189,0.055)');
      background.addColorStop(1, 'rgba(73,246,189,0)');
      waveContext.fillStyle = background;
      waveContext.fillRect(0, 0, width, height);

      // Retícula técnica.
      waveContext.save();
      waveContext.strokeStyle = 'rgba(66,233,255,0.075)';
      waveContext.lineWidth = 1;

      for (let row = 1; row < 5; row += 1) {
        const y = (height / 5) * row;
        waveContext.beginPath();
        waveContext.moveTo(0, y);
        waveContext.lineTo(width, y);
        waveContext.stroke();
      }

      for (let column = 1; column < 12; column += 1) {
        const x = (width / 12) * column;
        waveContext.beginPath();
        waveContext.moveTo(x, 0);
        waveContext.lineTo(x, height);
        waveContext.stroke();
      }

      waveContext.restore();

      let hasSignal = false;

      if (activeAnalyser) {
        activeAnalyser.getByteTimeDomainData(timeDomainData);
        activeAnalyser.getByteFrequencyData(frequencyData);
        hasSignal = true;
      }

      const stateEnergy = readEnergy();
      const stateBoost = state === State.SPEAKING
        ? 1.20
        : state === State.LISTENING
          ? 0.96
          : state === State.PROCESSING
            ? 0.58
            : 0.34;

      const lineGradient = waveContext.createLinearGradient(0, 0, width, 0);
      lineGradient.addColorStop(0, 'rgba(76,125,255,0.12)');
      lineGradient.addColorStop(0.22, 'rgba(66,233,255,0.94)');
      lineGradient.addColorStop(0.50, 'rgba(218,255,255,1)');
      lineGradient.addColorStop(0.78, 'rgba(73,246,189,0.94)');
      lineGradient.addColorStop(1, 'rgba(168,140,255,0.12)');

      const samples = hasSignal ? timeDomainData.length : 220;

      // Sombra secundaria.
      waveContext.beginPath();
      waveContext.lineWidth = 5.5;
      waveContext.strokeStyle = `rgba(66,233,255,${0.035 + stateEnergy * 0.12})`;
      waveContext.shadowColor = 'rgba(66,233,255,0.25)';
      waveContext.shadowBlur = 14;

      for (let index = 0; index < samples; index += 1) {
        const progress = index / Math.max(1, samples - 1);
        const x = progress * width;

        const amplitude = hasSignal
          ? (timeDomainData[index] - 128) / 128
          : (
            Math.sin(progress * Math.PI * 8 + timestamp * 0.0023) * 0.075
            + Math.sin(progress * Math.PI * 19 - timestamp * 0.0015) * 0.026
          );

        const envelope = Math.sin(progress * Math.PI);
        const y = centerY
          + amplitude * height * 0.37 * stateBoost * (0.42 + envelope * 0.58);

        if (index === 0) waveContext.moveTo(x, y);
        else waveContext.lineTo(x, y);
      }

      waveContext.stroke();

      // Línea principal.
      waveContext.beginPath();
      waveContext.lineWidth = 2.1;
      waveContext.strokeStyle = lineGradient;
      waveContext.shadowColor = 'rgba(66,233,255,0.80)';
      waveContext.shadowBlur = 9;

      for (let index = 0; index < samples; index += 1) {
        const progress = index / Math.max(1, samples - 1);
        const x = progress * width;

        const amplitude = hasSignal
          ? (timeDomainData[index] - 128) / 128
          : (
            Math.sin(progress * Math.PI * 8 + timestamp * 0.0023) * 0.075
            + Math.sin(progress * Math.PI * 19 - timestamp * 0.0015) * 0.026
          );

        const envelope = Math.sin(progress * Math.PI);
        const y = centerY
          + amplitude * height * 0.37 * stateBoost * (0.42 + envelope * 0.58);

        if (index === 0) waveContext.moveTo(x, y);
        else waveContext.lineTo(x, y);
      }

      waveContext.stroke();
      waveContext.shadowBlur = 0;

      // Reflejo inferior de la onda.
      waveContext.save();
      waveContext.globalAlpha = 0.18;
      waveContext.scale(1, -0.44);
      waveContext.translate(0, -height * 1.76);
      waveContext.beginPath();
      waveContext.lineWidth = 1.25;
      waveContext.strokeStyle = lineGradient;

      for (let index = 0; index < samples; index += 1) {
        const progress = index / Math.max(1, samples - 1);
        const x = progress * width;

        const amplitude = hasSignal
          ? (timeDomainData[index] - 128) / 128
          : Math.sin(progress * Math.PI * 8 + timestamp * 0.0023) * 0.075;

        const y = centerY + amplitude * height * 0.31 * stateBoost;

        if (index === 0) waveContext.moveTo(x, y);
        else waveContext.lineTo(x, y);
      }

      waveContext.stroke();
      waveContext.restore();

      // Barras de espectro.
      const bars = 64;
      const gap = 2.4;
      const barWidth = Math.max(
        1.2,
        (width - gap * (bars - 1)) / bars
      );

      for (let index = 0; index < bars; index += 1) {
        const sourceIndex = Math.min(
          frequencyData.length - 1,
          Math.floor((index / bars) * 118)
        );

        const signal = hasSignal
          ? frequencyData[sourceIndex] / 255
          : (
            (Math.sin(timestamp * 0.0022 + index * 0.57) + 1)
            * 0.028
          );

        const centerDistance = Math.abs(index - (bars - 1) / 2)
          / ((bars - 1) / 2);
        const envelope = 0.48 + (1 - centerDistance) * 0.52;
        const barHeight = Math.max(
          1.5,
          signal * height * 0.25 * envelope
        );

        const x = index * (barWidth + gap);
        const alpha = 0.17 + signal * 0.76;

        waveContext.fillStyle = index < bars / 2
          ? `rgba(66,233,255,${alpha})`
          : `rgba(73,246,189,${alpha})`;

        waveContext.fillRect(
          x,
          height - barHeight - 3,
          barWidth,
          barHeight
        );
      }

      // Marcador central.
      waveContext.save();
      waveContext.strokeStyle = `rgba(218,255,255,${0.20 + stateEnergy * 0.36})`;
      waveContext.lineWidth = 1;
      waveContext.beginPath();
      waveContext.moveTo(width / 2, 0);
      waveContext.lineTo(width / 2, height);
      waveContext.stroke();

      waveContext.fillStyle = `rgba(218,255,255,${0.48 + stateEnergy * 0.35})`;
      waveContext.font = '700 8px ui-monospace, SFMono-Regular, Menlo, Consolas, monospace';
      waveContext.textAlign = 'center';
      waveContext.textBaseline = 'top';
      waveContext.fillText(
        state === State.SPEAKING
          ? 'VOICE OUTPUT'
          : state === State.LISTENING
            ? 'VOICE INPUT'
            : state === State.PROCESSING
              ? 'ANALYZING'
              : 'STANDBY',
        width / 2,
        4
      );
      waveContext.restore();
    }

    return { init };
  }

  function getSessionId() {
    const key = 'gobia_voice_session_id';

    try {
      const existing = window.localStorage.getItem(key);
      if (existing) return existing;

      const generated = window.crypto?.randomUUID
        ? window.crypto.randomUUID()
        : `gobia-${Date.now()}-${Math.random().toString(16).slice(2)}`;
      window.localStorage.setItem(key, generated);
      return generated;
    } catch (_) {
      return `gobia-${Date.now()}-${Math.random().toString(16).slice(2)}`;
    }
  }

  function extensionForMime(mime) {
    const normalized = String(mime || '').toLowerCase();
    if (normalized.includes('mp4')) return 'm4a';
    if (normalized.includes('ogg')) return 'ogg';
    if (normalized.includes('mpeg')) return 'mp3';
    if (normalized.includes('wav')) return 'wav';
    return 'webm';
  }

  async function readJson(response) {
    const text = await response.text();
    if (!text) return {};

    try {
      return JSON.parse(text);
    } catch (_) {
      return {
        ok: response.ok,
        response: text,
        message: response.ok ? '' : text,
      };
    }
  }
  

  function friendlyError(error) {
    if (!(error instanceof Error)) {
      return 'Ocurrió un error inesperado.';
    }

    if (error.name === 'NotAllowedError') {
      return 'Debes permitir el acceso al micrófono en el navegador.';
    }

    if (error.name === 'NotFoundError') {
      return 'No se encontró un micrófono disponible.';
    }

    if (error.name === 'NotReadableError') {
      return 'El micrófono está siendo utilizado por otra aplicación.';
    }

    if (error.name === 'AbortError') {
      return 'La operación fue cancelada.';
    }

    return error.message || 'Ocurrió un error inesperado.';
  }

  function isAbortError(error) {
    return error instanceof DOMException && error.name === 'AbortError';
  }

  function truncate(value, limit) {
    const text = String(value || '').replace(/\s+/g, ' ').trim();
    return text.length > limit
      ? `${text.slice(0, Math.max(0, limit - 1))}…`
      : text;
  }

  function clamp(value, minimum, maximum) {
    return Math.min(maximum, Math.max(minimum, value));
  }

  function setText(element, value) {
    if (element) element.textContent = String(value ?? '');
  }

  function toggleClass(element, className, enabled) {
    if (element) element.classList.toggle(className, enabled);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
})();
