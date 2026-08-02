(() => {
  'use strict';

  const VIEW_CONFIG = Object.freeze({
    userName: String(window.GOBIA_VOICE_CONFIG?.userName || 'Usuario').trim(),
    assistantName: String(window.GOBIA_VOICE_CONFIG?.assistantName || 'ALMA').trim(),
    timeZone: String(window.GOBIA_VOICE_CONFIG?.timeZone || 'America/Bogota').trim(),
  });

  const ASSISTANT_NAME = VIEW_CONFIG.assistantName || 'ALMA';
  const USER_FULL_NAME = VIEW_CONFIG.userName || 'Usuario';
  const USER_FIRST_NAME = getFirstName(USER_FULL_NAME);

  const CONFIG = Object.freeze({
    sttEndpoint: 'admin/api/gobia_elevenlabs_stt.php',
    queryEndpoint: 'admin/api/gobia_voice_query.php',
    ttsEndpoint: 'admin/api/gobia_elevenlabs_tts.php',
    maxRecordingMs: 20000,
    waitForVoiceMs: 7500,
    silenceAfterVoiceMs: 1250,
    voiceThreshold: 0.026,
    delayedNoticeMs: 2500,
  });

  const State = Object.freeze({
    IDLE: 'idle',
    LISTENING: 'listening',
    PROCESSING: 'processing',
    SPEAKING: 'speaking',
    ERROR: 'error',
  });

  const elements = {
    faceCanvas: document.getElementById('gobiaFaceCanvas'),
    waveCanvas: document.getElementById('gobiaWaveCanvas'),
    faceFrame: document.getElementById('gobiaFaceFrame'),
    audio: document.getElementById('gobiaAudioPlayer'),
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

  if (!elements.faceCanvas || !elements.waveCanvas || !elements.micButton) {
    return;
  }

  let currentState = State.IDLE;
  let audioContext = null;
  let ttsAnalyser = null;
  let ttsSource = null;
  let microphoneAnalyser = null;
  let microphoneStream = null;
  let microphoneSource = null;
  let activeAnalyser = null;
  let mediaRecorder = null;
  let recorderChunks = [];
  let recordingStartedAt = 0;
  let voiceStarted = false;
  let silenceStartedAt = 0;
  let recordingMonitorFrame = null;
  let recordingTimeout = null;
  let shouldContinue = false;
  let currentAudioUrl = null;
  let assistantEnergy = 0;
  let userEnergy = 0;
  let hasDeliveredGreeting = false;
  let currentAudioPurpose = 'idle';
  let activeQuerySequence = 0;
  let pendingAnswerAfterNotice = '';
  let delayNoticeFinished = false;

  const sessionId = getOrCreateSessionId();

  elements.sessionDiagnostic.textContent = sessionId.slice(-8).toUpperCase();

  function getOrCreateSessionId() {
    const storageKey = 'gobia_voice_session_id';
    const existing = window.localStorage.getItem(storageKey);

    if (existing) {
      return existing;
    }

    const generated = (
      window.crypto && typeof window.crypto.randomUUID === 'function'
        ? window.crypto.randomUUID()
        : `gobia-${Date.now()}-${Math.random().toString(16).slice(2)}`
    );

    window.localStorage.setItem(storageKey, generated);
    return generated;
  }

  function setState(nextState, title, description) {
    currentState = nextState;

    elements.faceFrame.classList.toggle('is-listening', nextState === State.LISTENING);
    elements.faceFrame.classList.toggle('is-speaking', nextState === State.SPEAKING);

    elements.micButton.classList.toggle('is-listening', nextState === State.LISTENING);
    elements.micButton.classList.toggle('is-processing', nextState === State.PROCESSING);
    elements.micButton.classList.toggle('is-speaking', nextState === State.SPEAKING);
    elements.micButton.setAttribute('aria-pressed', String(nextState === State.LISTENING));

    elements.stopButton.disabled = nextState === State.IDLE || nextState === State.ERROR;
    elements.statusTitle.textContent = title;
    elements.statusText.textContent = description;

    const labels = {
      [State.IDLE]: 'Activar micrófono',
      [State.LISTENING]: 'Escuchando...',
      [State.PROCESSING]: 'Procesando...',
      [State.SPEAKING]: `${ASSISTANT_NAME} está hablando`,
      [State.ERROR]: 'Reintentar',
    };

    elements.micLabel.textContent = labels[nextState] || 'Activar micrófono';

    elements.micDiagnostic.textContent = (
      nextState === State.LISTENING ? 'Escuchando' : 'Inactivo'
    );

    elements.processDiagnostic.textContent = (
      nextState === State.PROCESSING
        ? 'Analizando'
        : nextState === State.SPEAKING
          ? 'Respuesta lista'
          : 'En espera'
    );

    elements.voiceDiagnostic.textContent = (
      nextState === State.SPEAKING ? 'Reproduciendo' : 'Preparada'
    );

    elements.systemState.lastElementChild.textContent = (
      nextState === State.LISTENING
        ? 'Escucha activa'
        : nextState === State.PROCESSING
          ? 'Procesamiento activo'
          : nextState === State.SPEAKING
            ? 'Respuesta por voz'
            : nextState === State.ERROR
              ? 'Revisión requerida'
              : 'Sistema disponible'
    );
  }

  function setCaption() {
    // La interfaz es exclusivamente por voz.
    // Nunca muestra la transcripción ni el texto de la respuesta.
    if (!elements.liveCaption) {
      return;
    }

    elements.liveCaption.hidden = true;
    elements.liveCaption.textContent = '';
    elements.liveCaption.style.display = 'none';
  }

  function getFirstName(value) {
    const normalized = String(value || '')
      .replace(/\s+/g, ' ')
      .trim();

    if (!normalized) {
      return 'Usuario';
    }

    const firstName = normalized.split(' ')[0].toLocaleLowerCase('es-CO');

    return firstName.charAt(0).toLocaleUpperCase('es-CO') + firstName.slice(1);
  }

  function getColombiaHour() {
    try {
      const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: VIEW_CONFIG.timeZone,
        hour: '2-digit',
        hourCycle: 'h23',
      }).formatToParts(new Date());

      const hourPart = parts.find((part) => part.type === 'hour');
      const hour = Number.parseInt(hourPart?.value || '', 10);

      return Number.isFinite(hour) ? hour : new Date().getHours();
    } catch (error) {
      console.warn('[ALMA] No fue posible leer la hora de Colombia.', error);
      return new Date().getHours();
    }
  }

  function getColombiaDayPart() {
    const hour = getColombiaHour();

    if (hour >= 5 && hour < 12) {
      return 'Buenos días';
    }

    if (hour >= 12 && hour < 19) {
      return 'Buenas tardes';
    }

    return 'Buenas noches';
  }

  function buildActivationGreeting() {
    return `${getColombiaDayPart()}, ${USER_FIRST_NAME}. Soy ${ASSISTANT_NAME}. ¿En qué puedo ayudarte?`;
  }

  function buildDelayNotice() {
    return `Ya voy a revisar y te informo, ${USER_FIRST_NAME}.`;
  }

  async function ensureAudioContext() {
    if (!audioContext) {
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;

      if (!AudioContextClass) {
        throw new Error('El navegador no permite analizar audio en tiempo real.');
      }

      audioContext = new AudioContextClass();
    }

    if (audioContext.state === 'suspended') {
      await audioContext.resume();
    }

    if (!ttsAnalyser) {
      ttsAnalyser = audioContext.createAnalyser();
      ttsAnalyser.fftSize = 512;
      ttsAnalyser.smoothingTimeConstant = 0.78;
    }

    if (!ttsSource) {
      ttsSource = audioContext.createMediaElementSource(elements.audio);
      ttsSource.connect(ttsAnalyser);
      ttsAnalyser.connect(audioContext.destination);
    }
  }

  async function ensureMicrophone() {
    if (microphoneStream && microphoneStream.active) {
      return microphoneStream;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      throw new Error('Este navegador no permite acceder al micrófono.');
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

    microphoneAnalyser = audioContext.createAnalyser();
    microphoneAnalyser.fftSize = 512;
    microphoneAnalyser.smoothingTimeConstant = 0.65;

    microphoneSource = audioContext.createMediaStreamSource(microphoneStream);
    microphoneSource.connect(microphoneAnalyser);

    return microphoneStream;
  }

  function chooseAudioMimeType() {
    const candidates = [
      'audio/webm;codecs=opus',
      'audio/webm',
      'audio/mp4',
      'audio/ogg;codecs=opus',
    ];

    return candidates.find((type) => (
      window.MediaRecorder
      && typeof MediaRecorder.isTypeSupported === 'function'
      && MediaRecorder.isTypeSupported(type)
    )) || '';
  }

  async function startListening() {
    if (currentState === State.PROCESSING || currentState === State.SPEAKING) {
      return;
    }

    try {
      await stopPlayback(false);
      await ensureAudioContext();
      const stream = await ensureMicrophone();

      if (!window.MediaRecorder) {
        throw new Error('El navegador no permite grabar audio.');
      }

      recorderChunks = [];
      voiceStarted = false;
      silenceStartedAt = 0;
      recordingStartedAt = performance.now();
      shouldContinue = true;

      const mimeType = chooseAudioMimeType();
      mediaRecorder = mimeType
        ? new MediaRecorder(stream, { mimeType })
        : new MediaRecorder(stream);

      mediaRecorder.addEventListener('dataavailable', (event) => {
        if (event.data && event.data.size > 0) {
          recorderChunks.push(event.data);
        }
      });

      mediaRecorder.addEventListener('stop', handleRecordingStopped, { once: true });
      mediaRecorder.start(250);
      activeAnalyser = microphoneAnalyser;

      setCaption('');
      setState(
        State.LISTENING,
        'Te escucho',
        'Habla con naturalidad. La grabación terminará automáticamente cuando detecte silencio.'
      );

      monitorRecordingLevel();

      recordingTimeout = window.setTimeout(() => {
        stopListening(true);
      }, CONFIG.maxRecordingMs);
    } catch (error) {
      handleError(error, 'No fue posible activar el micrófono.');
    }
  }

  function monitorRecordingLevel() {
    if (!microphoneAnalyser || currentState !== State.LISTENING) {
      return;
    }

    const data = new Uint8Array(microphoneAnalyser.fftSize);
    microphoneAnalyser.getByteTimeDomainData(data);

    let sum = 0;
    for (let index = 0; index < data.length; index += 1) {
      const normalized = (data[index] - 128) / 128;
      sum += normalized * normalized;
    }

    const rms = Math.sqrt(sum / data.length);
    userEnergy = Math.min(1, rms * 9.5);

    const now = performance.now();

    if (rms >= CONFIG.voiceThreshold) {
      voiceStarted = true;
      silenceStartedAt = 0;
    } else if (voiceStarted) {
      if (!silenceStartedAt) {
        silenceStartedAt = now;
      }

      if (now - silenceStartedAt >= CONFIG.silenceAfterVoiceMs) {
        stopListening(true);
        return;
      }
    } else if (now - recordingStartedAt >= CONFIG.waitForVoiceMs) {
      stopListening(false);
      return;
    }

    recordingMonitorFrame = window.requestAnimationFrame(monitorRecordingLevel);
  }

  function stopListening(processAudio = true) {
    shouldContinue = processAudio;

    if (recordingMonitorFrame) {
      window.cancelAnimationFrame(recordingMonitorFrame);
      recordingMonitorFrame = null;
    }

    if (recordingTimeout) {
      window.clearTimeout(recordingTimeout);
      recordingTimeout = null;
    }

    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
      mediaRecorder.stop();
      return;
    }

    if (!processAudio) {
      activeAnalyser = null;
      userEnergy = 0;
      setState(
        State.IDLE,
        'No detecté una instrucción',
        'Pulsa el micrófono e inténtalo nuevamente.'
      );
    }
  }

  async function handleRecordingStopped() {
    activeAnalyser = null;
    userEnergy = 0;

    if (!shouldContinue || !voiceStarted || recorderChunks.length === 0) {
      recorderChunks = [];
      setState(
        State.IDLE,
        'Micrófono detenido',
        'Pulsa el botón cuando quieras realizar una consulta.'
      );
      return;
    }

    const type = mediaRecorder && mediaRecorder.mimeType
      ? mediaRecorder.mimeType
      : 'audio/webm';

    const blob = new Blob(recorderChunks, { type });
    recorderChunks = [];

    try {
      setState(
        State.PROCESSING,
        'Interpretando tu instrucción',
        `${ASSISTANT_NAME} está transcribiendo y analizando el comando de voz.`
      );

      const transcript = await transcribeAudio(blob);

      if (!transcript) {
        throw new Error('No se pudo reconocer una instrucción clara.');
      }

      setCaption('');

      setState(
        State.PROCESSING,
        'Consultando GOB360',
        `${ASSISTANT_NAME} está revisando la información solicitada.`
      );

      const querySequence = ++activeQuerySequence;
      let delayNoticeStarted = false;
      let delayNoticePromise = null;
      delayNoticeFinished = false;
      pendingAnswerAfterNotice = '';

      const delayTimer = window.setTimeout(() => {
        if (
          querySequence !== activeQuerySequence
          || currentState !== State.PROCESSING
        ) {
          return;
        }

        delayNoticeStarted = true;
        delayNoticePromise = speakText(
          buildDelayNotice(),
          'delay_notice'
        ).catch((error) => {
          console.warn('[ALMA] No se pudo reproducir el aviso de espera.', error);
          delayNoticeFinished = true;
        });
      }, CONFIG.delayedNoticeMs);

      let answer;

      try {
        answer = await requestAssistantAnswer(transcript);
      } finally {
        window.clearTimeout(delayTimer);
      }

      if (querySequence !== activeQuerySequence) {
        return;
      }

      if (!answer) {
        throw new Error(`El agente de ${ASSISTANT_NAME} no devolvió una respuesta de voz.`);
      }

      if (delayNoticeStarted && delayNoticePromise) {
        await delayNoticePromise;

        if (querySequence !== activeQuerySequence) {
          return;
        }

        if (delayNoticeFinished) {
          await speakAnswer(answer);
        } else {
          pendingAnswerAfterNotice = answer;
        }

        return;
      }

      await speakAnswer(answer);
    } catch (error) {
      handleError(error, 'No fue posible completar la consulta.');
    }
  }

  async function transcribeAudio(blob) {
    const extension = blob.type.includes('mp4')
      ? 'm4a'
      : blob.type.includes('ogg')
        ? 'ogg'
        : 'webm';

    const data = new FormData();
    data.append('audio', blob, `gobia-command.${extension}`);

    const response = await fetch(CONFIG.sttEndpoint, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
    });

    const payload = await readJsonResponse(response);

    if (!response.ok || !payload.ok) {
      throw new Error(payload.message || 'Error al transcribir el audio.');
    }

    return String(payload.text || '').trim();
  }

  async function requestAssistantAnswer(transcript) {
    const response = await fetch(CONFIG.queryEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        chatInput: transcript,
        sessionId,
        userName: USER_FULL_NAME,
        assistantName: ASSISTANT_NAME,
      }),
    });

    const payload = await readJsonResponse(response);

    if (!response.ok || !payload.ok) {
      throw new Error(payload.message || `Error al consultar el agente de ${ASSISTANT_NAME}.`);
    }

    return String(payload.response || '').trim();
  }

  async function speakText(text, purpose = 'answer') {
    const normalizedText = String(text || '').trim();

    if (!normalizedText) {
      throw new Error('No hay texto para generar la voz.');
    }

    await ensureAudioContext();
    setCaption('');

    currentAudioPurpose = purpose;

    const purposeState = {
      greeting: {
        processingTitle: 'Preparando saludo',
        processingText: `${ASSISTANT_NAME} está preparando el saludo inicial.`,
        speakingTitle: `${ASSISTANT_NAME} te saluda`,
        speakingText: 'Saludo de bienvenida.',
      },
      delay_notice: {
        processingTitle: 'Consultando información',
        processingText: 'La consulta requiere unos segundos adicionales.',
        speakingTitle: 'Revisando la consulta',
        speakingText: 'La búsqueda continúa en proceso.',
      },
      answer: {
        processingTitle: 'Generando respuesta por voz',
        processingText: 'ElevenLabs está preparando la respuesta.',
        speakingTitle: `${ASSISTANT_NAME} responde`,
        speakingText: 'Respuesta por voz.',
      },
    };

    const labels = purposeState[purpose] || purposeState.answer;

    setState(
      State.PROCESSING,
      labels.processingTitle,
      labels.processingText
    );

    const response = await fetch(CONFIG.ttsEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ text: normalizedText }),
    });

    if (!response.ok) {
      let message = `No se pudo generar la voz de ${ASSISTANT_NAME}.`;

      try {
        const errorPayload = await response.json();
        message = errorPayload.message || message;
      } catch (error) {
        // La respuesta puede no ser JSON.
      }

      currentAudioPurpose = 'idle';
      throw new Error(message);
    }

    const audioBlob = await response.blob();

    if (currentAudioUrl) {
      URL.revokeObjectURL(currentAudioUrl);
    }

    currentAudioUrl = URL.createObjectURL(audioBlob);
    elements.audio.src = currentAudioUrl;
    activeAnalyser = ttsAnalyser;

    setState(
      State.SPEAKING,
      labels.speakingTitle,
      labels.speakingText
    );

    await elements.audio.play();
  }

  async function speakAnswer(answer) {
    await speakText(answer, 'answer');
  }

  async function stopPlayback(returnToIdle = true) {
    if (!elements.audio.paused) {
      elements.audio.pause();
      elements.audio.currentTime = 0;
    }

    assistantEnergy = 0;
    activeAnalyser = null;

    if (returnToIdle) {
      setState(
        State.IDLE,
        'Respuesta detenida',
        'Pulsa el micrófono para realizar una nueva consulta.'
      );
    }
  }

  async function stopEverything() {
    shouldContinue = false;
    activeQuerySequence += 1;
    pendingAnswerAfterNotice = '';
    delayNoticeFinished = true;
    currentAudioPurpose = 'idle';

    if (currentState === State.LISTENING) {
      stopListening(false);
      return;
    }

    if (currentState === State.SPEAKING) {
      await stopPlayback(true);
      return;
    }

    setState(
      State.IDLE,
      `${ASSISTANT_NAME} en espera`,
      'Pulsa el micrófono para comenzar una nueva consulta.'
    );
  }

  function handleError(error, friendlyMessage) {
    console.error('[ALMA]', error);
    activeAnalyser = null;
    assistantEnergy = 0;
    userEnergy = 0;

    setState(
      State.ERROR,
      friendlyMessage,
      error instanceof Error ? error.message : 'Ocurrió un error inesperado.'
    );
  }

  async function readJsonResponse(response) {
    const text = await response.text();

    if (!text) {
      return {};
    }

    try {
      return JSON.parse(text);
    } catch (error) {
      return {
        ok: response.ok,
        response: text,
        message: response.ok ? '' : text,
      };
    }
  }

  function truncateText(text, maxLength) {
    const normalized = String(text || '').replace(/\s+/g, ' ').trim();
    return normalized.length > maxLength
      ? `${normalized.slice(0, maxLength - 1)}…`
      : normalized;
  }

  elements.micButton.addEventListener('click', async () => {
    if (currentState === State.LISTENING) {
      stopListening(true);
      return;
    }

    if (currentState === State.SPEAKING) {
      activeQuerySequence += 1;
      pendingAnswerAfterNotice = '';
      delayNoticeFinished = true;
      currentAudioPurpose = 'idle';
      await stopPlayback(false);
      window.setTimeout(startListening, 180);
      return;
    }

    if (currentState === State.PROCESSING) {
      return;
    }

    if (!hasDeliveredGreeting) {
      hasDeliveredGreeting = true;

      try {
        await speakText(buildActivationGreeting(), 'greeting');
      } catch (error) {
        hasDeliveredGreeting = false;
        handleError(error, 'No fue posible reproducir el saludo.');
      }

      return;
    }

    startListening();
  });

  elements.stopButton.addEventListener('click', stopEverything);

  elements.audio.addEventListener('play', () => {
    activeAnalyser = ttsAnalyser;

    const labels = {
      greeting: {
        title: `${ASSISTANT_NAME} te saluda`,
        text: 'Saludo de bienvenida.',
      },
      delay_notice: {
        title: 'Revisando la consulta',
        text: 'La búsqueda continúa en proceso.',
      },
      answer: {
        title: `${ASSISTANT_NAME} responde`,
        text: 'Respuesta por voz.',
      },
    };

    const currentLabels = labels[currentAudioPurpose] || labels.answer;

    setState(
      State.SPEAKING,
      currentLabels.title,
      currentLabels.text
    );
  });

  elements.audio.addEventListener('ended', () => {
    activeAnalyser = null;
    assistantEnergy = 0;

    const completedPurpose = currentAudioPurpose;
    currentAudioPurpose = 'idle';

    if (completedPurpose === 'greeting') {
      setState(
        State.IDLE,
        'Micrófono preparado',
        'Puedes hablar cuando se active la escucha.'
      );

      window.setTimeout(() => {
        if (currentState === State.IDLE) {
          startListening();
        }
      }, 350);

      return;
    }

    if (completedPurpose === 'delay_notice') {
      delayNoticeFinished = true;

      if (pendingAnswerAfterNotice) {
        const answer = pendingAnswerAfterNotice;
        pendingAnswerAfterNotice = '';

        speakAnswer(answer).catch((error) => {
          handleError(error, 'No fue posible reproducir la respuesta.');
        });

        return;
      }

      setState(
        State.PROCESSING,
        'Consultando GOB360',
        `${ASSISTANT_NAME} continúa revisando la información.`
      );

      return;
    }

    if (completedPurpose !== 'answer') {
      setState(
        State.IDLE,
        'Sistema disponible',
        'Pulsa el micrófono para comenzar.'
      );
      return;
    }

    if (elements.continuous.checked) {
      setState(
        State.IDLE,
        'Conversación continua activa',
        `${ASSISTANT_NAME} volverá a escuchar en un momento.`
      );

      window.setTimeout(() => {
        if (currentState === State.IDLE) {
          startListening();
        }
      }, 650);

      return;
    }

    setState(
      State.IDLE,
      'Consulta finalizada',
      'Pulsa el micrófono para realizar otra consulta.'
    );
  });

  elements.audio.addEventListener('error', () => {
    currentAudioPurpose = 'idle';
    delayNoticeFinished = true;

    handleError(
      new Error('El navegador no pudo reproducir el audio generado.'),
      'No fue posible reproducir la respuesta.'
    );
  });

  window.addEventListener('beforeunload', () => {
    if (microphoneStream) {
      microphoneStream.getTracks().forEach((track) => track.stop());
    }

    if (currentAudioUrl) {
      URL.revokeObjectURL(currentAudioUrl);
    }
  });

  // ----------------------------------------------------------
  // ROSTRO FEMENINO BINARIO Y FRECUENCIA DE VOZ
  // ----------------------------------------------------------
  const faceContext = elements.faceCanvas.getContext('2d');
  const waveContext = elements.waveCanvas.getContext('2d');
  const binaryPoints = createBinaryFacePoints();
  const frequencyData = new Uint8Array(256);
  const timeDomainData = new Uint8Array(512);

  function resizeCanvases() {
    resizeCanvas(elements.faceCanvas, faceContext);
    resizeCanvas(elements.waveCanvas, waveContext);
  }

  function resizeCanvas(canvas, context) {
    const rect = canvas.getBoundingClientRect();
    const ratio = Math.min(window.devicePixelRatio || 1, 2);
    const width = Math.max(1, Math.round(rect.width * ratio));
    const height = Math.max(1, Math.round(rect.height * ratio));

    if (canvas.width !== width || canvas.height !== height) {
      canvas.width = width;
      canvas.height = height;
      context.setTransform(ratio, 0, 0, ratio, 0, 0);
    }
  }

  function createBinaryFacePoints() {
    const points = [];

    const addEllipse = (cx, cy, rx, ry, count, type, start = 0, end = Math.PI * 2) => {
      for (let index = 0; index < count; index += 1) {
        const progress = count === 1 ? 0 : index / (count - 1);
        const angle = start + (end - start) * progress;
        points.push({
          x: cx + Math.cos(angle) * rx,
          y: cy + Math.sin(angle) * ry,
          type,
          phase: Math.random() * Math.PI * 2,
          digit: Math.random() > 0.5 ? '1' : '0',
        });
      }
    };

    const addCurve = (curvePoints, samples, type) => {
      for (let segment = 0; segment < curvePoints.length - 1; segment += 1) {
        const start = curvePoints[segment];
        const end = curvePoints[segment + 1];

        for (let index = 0; index < samples; index += 1) {
          const progress = index / samples;
          points.push({
            x: start[0] + (end[0] - start[0]) * progress,
            y: start[1] + (end[1] - start[1]) * progress,
            type,
            phase: Math.random() * Math.PI * 2,
            digit: Math.random() > 0.5 ? '1' : '0',
          });
        }
      }
    };

    // Cabello femenino y contorno facial.
    addEllipse(0.5, 0.49, 0.315, 0.405, 160, 'hair');
    addEllipse(0.5, 0.49, 0.245, 0.335, 125, 'face');
    addCurve([[0.22, 0.25], [0.16, 0.46], [0.18, 0.77], [0.29, 0.91]], 24, 'hair');
    addCurve([[0.78, 0.25], [0.84, 0.46], [0.82, 0.77], [0.71, 0.91]], 24, 'hair');
    addCurve([[0.25, 0.29], [0.37, 0.16], [0.5, 0.12], [0.64, 0.17], [0.76, 0.3]], 21, 'hair');

    // Cejas y ojos.
    addCurve([[0.325, 0.405], [0.38, 0.382], [0.445, 0.397]], 16, 'eyebrow');
    addCurve([[0.555, 0.397], [0.62, 0.382], [0.675, 0.405]], 16, 'eyebrow');
    addEllipse(0.39, 0.445, 0.067, 0.026, 34, 'eye');
    addEllipse(0.61, 0.445, 0.067, 0.026, 34, 'eye');
    addEllipse(0.39, 0.445, 0.014, 0.014, 16, 'iris');
    addEllipse(0.61, 0.445, 0.014, 0.014, 16, 'iris');

    // Nariz y pómulos.
    addCurve([[0.5, 0.455], [0.488, 0.535], [0.472, 0.59], [0.5, 0.608], [0.528, 0.59]], 15, 'nose');
    addCurve([[0.31, 0.54], [0.355, 0.575], [0.405, 0.585]], 14, 'cheek');
    addCurve([[0.595, 0.585], [0.645, 0.575], [0.69, 0.54]], 14, 'cheek');

    // Labios animables.
    addCurve([[0.41, 0.67], [0.46, 0.645], [0.5, 0.655], [0.54, 0.645], [0.59, 0.67]], 15, 'upperLip');
    addCurve([[0.41, 0.67], [0.46, 0.696], [0.5, 0.704], [0.54, 0.696], [0.59, 0.67]], 15, 'lowerLip');

    // Mandíbula y cuello mínimo, sin cuerpo.
    addCurve([[0.31, 0.7], [0.36, 0.79], [0.43, 0.84], [0.5, 0.86], [0.57, 0.84], [0.64, 0.79], [0.69, 0.7]], 19, 'jaw');
    addCurve([[0.43, 0.84], [0.43, 0.91]], 14, 'neck');
    addCurve([[0.57, 0.84], [0.57, 0.91]], 14, 'neck');

    // Partículas internas para dar volumen.
    for (let index = 0; index < 285; index += 1) {
      const angle = Math.random() * Math.PI * 2;
      const radius = Math.sqrt(Math.random());
      const x = 0.5 + Math.cos(angle) * 0.22 * radius;
      const y = 0.505 + Math.sin(angle) * 0.31 * radius;

      points.push({
        x,
        y,
        type: 'skin',
        phase: Math.random() * Math.PI * 2,
        digit: Math.random() > 0.5 ? '1' : '0',
      });
    }

    return points;
  }

  function readAnalyserEnergy() {
    if (!activeAnalyser) {
      assistantEnergy *= 0.91;
      return assistantEnergy;
    }

    activeAnalyser.getByteFrequencyData(frequencyData);
    let sum = 0;
    const limit = Math.min(90, frequencyData.length);

    for (let index = 3; index < limit; index += 1) {
      sum += frequencyData[index];
    }

    const energy = sum / ((limit - 3) * 255);

    if (currentState === State.SPEAKING) {
      assistantEnergy += (energy - assistantEnergy) * 0.34;
      return assistantEnergy;
    }

    if (currentState === State.LISTENING) {
      return Math.max(userEnergy, energy);
    }

    assistantEnergy *= 0.91;
    return assistantEnergy;
  }

  function drawBinaryFace(timestamp) {
    const canvas = elements.faceCanvas;
    const rect = canvas.getBoundingClientRect();
    const width = rect.width;
    const height = rect.height;
    const energy = readAnalyserEnergy();
    const speaking = currentState === State.SPEAKING;
    const listening = currentState === State.LISTENING;

    faceContext.clearRect(0, 0, width, height);

    const centerX = width * 0.5;
    const centerY = height * 0.5;
    const scale = Math.min(width, height);
    const idleMotion = Math.sin(timestamp * 0.0011) * 0.0028;
    const talkMotion = speaking ? Math.sin(timestamp * 0.009) * energy * 0.012 : 0;
    const headTilt = idleMotion + talkMotion;
    const mouthOpen = speaking ? Math.min(0.045, energy * 0.09) : 0;
    const listeningGlow = listening ? 0.14 + userEnergy * 0.34 : 0;

    faceContext.save();
    faceContext.translate(centerX, centerY);
    faceContext.rotate(headTilt);
    faceContext.translate(-centerX, -centerY);

    binaryPoints.forEach((point, index) => {
      let x = point.x;
      let y = point.y;

      if (point.type === 'lowerLip') {
        y += mouthOpen;
      }

      if (point.type === 'upperLip') {
        y -= mouthOpen * 0.23;
      }

      if (point.type === 'jaw' && speaking) {
        y += mouthOpen * 0.22;
      }

      const pulse = speaking
        ? Math.sin(timestamp * 0.012 + point.phase) * energy * 0.0027
        : Math.sin(timestamp * 0.0022 + point.phase) * 0.0008;

      x += pulse;
      y += pulse * 0.7;

      const px = x * width;
      const py = y * height;
      const flicker = 0.66 + Math.sin(timestamp * 0.004 + point.phase) * 0.24;
      const isFeature = ['eye', 'iris', 'eyebrow', 'nose', 'upperLip', 'lowerLip'].includes(point.type);
      const isHair = point.type === 'hair';
      const baseAlpha = isFeature ? 0.96 : isHair ? 0.68 : 0.52;
      const alpha = Math.max(0.17, Math.min(1, baseAlpha * flicker + energy * 0.23 + listeningGlow));
      const fontSize = Math.max(7, scale * (isFeature ? 0.019 : isHair ? 0.016 : 0.0145));

      if (speaking && (index + Math.floor(timestamp / 90)) % 19 === 0) {
        point.digit = point.digit === '0' ? '1' : '0';
      }

      const gradient = faceContext.createLinearGradient(px - 8, py - 8, px + 8, py + 8);
      gradient.addColorStop(0, `rgba(76, 125, 255, ${alpha})`);
      gradient.addColorStop(0.48, `rgba(66, 233, 255, ${alpha})`);
      gradient.addColorStop(1, `rgba(73, 246, 189, ${alpha * 0.92})`);

      faceContext.font = `700 ${fontSize}px ui-monospace, SFMono-Regular, Menlo, Consolas, monospace`;
      faceContext.textAlign = 'center';
      faceContext.textBaseline = 'middle';
      faceContext.fillStyle = gradient;
      faceContext.shadowColor = `rgba(66, 233, 255, ${0.12 + energy * 0.58})`;
      faceContext.shadowBlur = isFeature ? 8 + energy * 18 : 3 + energy * 10;
      faceContext.fillText(point.digit, px, py);
    });

    faceContext.restore();

    // Halo facial que aumenta con la voz.
    const halo = faceContext.createRadialGradient(
      centerX,
      centerY * 0.95,
      scale * 0.15,
      centerX,
      centerY * 0.95,
      scale * 0.48
    );
    halo.addColorStop(0, `rgba(66, 233, 255, ${0.015 + energy * 0.035})`);
    halo.addColorStop(0.7, `rgba(76, 125, 255, ${0.02 + energy * 0.055})`);
    halo.addColorStop(1, 'rgba(0, 0, 0, 0)');
    faceContext.fillStyle = halo;
    faceContext.fillRect(0, 0, width, height);
  }

  function drawWaveform(timestamp) {
    const rect = elements.waveCanvas.getBoundingClientRect();
    const width = rect.width;
    const height = rect.height;
    const centerY = height / 2;

    waveContext.clearRect(0, 0, width, height);

    const background = waveContext.createLinearGradient(0, 0, width, 0);
    background.addColorStop(0, 'rgba(76, 125, 255, 0)');
    background.addColorStop(0.5, 'rgba(66, 233, 255, 0.06)');
    background.addColorStop(1, 'rgba(73, 246, 189, 0)');
    waveContext.fillStyle = background;
    waveContext.fillRect(0, 0, width, height);

    waveContext.strokeStyle = 'rgba(66, 233, 255, 0.1)';
    waveContext.lineWidth = 1;
    for (let row = 1; row < 4; row += 1) {
      const y = (height / 4) * row;
      waveContext.beginPath();
      waveContext.moveTo(0, y);
      waveContext.lineTo(width, y);
      waveContext.stroke();
    }

    let hasSignal = false;

    if (activeAnalyser) {
      activeAnalyser.getByteTimeDomainData(timeDomainData);
      hasSignal = true;
    }

    const gradient = waveContext.createLinearGradient(0, 0, width, 0);
    gradient.addColorStop(0, 'rgba(76, 125, 255, 0.18)');
    gradient.addColorStop(0.28, 'rgba(66, 233, 255, 0.95)');
    gradient.addColorStop(0.72, 'rgba(73, 246, 189, 0.95)');
    gradient.addColorStop(1, 'rgba(168, 140, 255, 0.18)');

    waveContext.beginPath();
    waveContext.lineWidth = 2.2;
    waveContext.strokeStyle = gradient;
    waveContext.shadowColor = 'rgba(66, 233, 255, 0.7)';
    waveContext.shadowBlur = 9;

    const samples = hasSignal ? timeDomainData.length : 170;

    for (let index = 0; index < samples; index += 1) {
      const progress = index / Math.max(1, samples - 1);
      const x = progress * width;
      let amplitude;

      if (hasSignal) {
        amplitude = (timeDomainData[index] - 128) / 128;
      } else {
        amplitude = (
          Math.sin(progress * Math.PI * 7 + timestamp * 0.0025) * 0.08
          + Math.sin(progress * Math.PI * 17 - timestamp * 0.0017) * 0.025
        );
      }

      const stateBoost = currentState === State.SPEAKING
        ? 1.15
        : currentState === State.LISTENING
          ? 0.9
          : 0.42;

      const y = centerY + amplitude * height * 0.39 * stateBoost;

      if (index === 0) {
        waveContext.moveTo(x, y);
      } else {
        waveContext.lineTo(x, y);
      }
    }

    waveContext.stroke();
    waveContext.shadowBlur = 0;

    // Barras de frecuencia inferiores.
    if (activeAnalyser) {
      activeAnalyser.getByteFrequencyData(frequencyData);
    }

    const barCount = 54;
    const gap = 3;
    const barWidth = Math.max(1.4, (width - gap * (barCount - 1)) / barCount);

    for (let index = 0; index < barCount; index += 1) {
      const signal = activeAnalyser
        ? frequencyData[Math.floor(index / barCount * 100)] / 255
        : (Math.sin(timestamp * 0.002 + index * 0.65) + 1) * 0.035;

      const barHeight = Math.max(2, signal * height * 0.29);
      const x = index * (barWidth + gap);

      waveContext.fillStyle = index < barCount / 2
        ? `rgba(66, 233, 255, ${0.22 + signal * 0.72})`
        : `rgba(73, 246, 189, ${0.22 + signal * 0.72})`;

      waveContext.fillRect(x, height - barHeight - 3, barWidth, barHeight);
    }
  }

  function animate(timestamp) {
    resizeCanvases();
    drawBinaryFace(timestamp);
    drawWaveform(timestamp);
    window.requestAnimationFrame(animate);
  }

  window.addEventListener('resize', resizeCanvases);
  setState(
    State.IDLE,
    'Pulsa el micrófono para comenzar',
    'El navegador solicitará permiso para utilizar el micrófono.'
  );
  resizeCanvases();
  window.requestAnimationFrame(animate);
})();
