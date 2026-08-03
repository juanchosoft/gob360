(function () {
  'use strict';

  const $ = (selector) => document.querySelector(selector);
  const canvases = new Map();
  let frame = 0;
  let time = 0;
  let lastPacketSecond = -1;

  function buildBinaryStream() {
    const stream = $('#starkBinaryStream');
    if (!stream || stream.childElementCount) return;
    const fragment = document.createDocumentFragment();
    for (let row = 0; row < 28; row++) {
      const line = document.createElement('span');
      let bits = '';
      for (let bit = 0; bit < 42; bit++) bits += ((row * 17 + bit * 31 + (bit >> 1)) % 7) < 3 ? '1' : '0';
      line.textContent = bits;
      fragment.appendChild(line);
    }
    stream.appendChild(fragment);
  }

  function updatePacketRate(mode) {
    const second = Math.floor(time);
    if (second === lastPacketSecond) return;
    lastPacketSecond = second;
    const output = $('#starkPacketRate');
    if (!output) return;
    const base = mode === 'processing' ? 640 : mode === 'listening' ? 280 : mode === 'speaking' ? 410 : 24;
    const variation = (second * 73) % 159;
    output.textContent = String(base + variation).padStart(3, '0') + ' PKT/S';
  }

  function fit(canvas) {
    if (!canvas) return null;
    const ratio = Math.min(window.devicePixelRatio || 1, 2);
    const rect = canvas.getBoundingClientRect();
    const width = Math.max(1, Math.round(rect.width * ratio));
    const height = Math.max(1, Math.round(rect.height * ratio));
    if (canvas.width !== width || canvas.height !== height) {
      canvas.width = width;
      canvas.height = height;
    }
    const context = canvas.getContext('2d');
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
    return { context, width: rect.width, height: rect.height };
  }

  function currentMode() {
    const mic = ($('#gobiaMicDiagnostic')?.textContent || '').toLowerCase();
    const process = ($('#gobiaProcessDiagnostic')?.textContent || '').toLowerCase();
    const voice = ($('#gobiaVoiceDiagnostic')?.textContent || '').toLowerCase();
    const audio = $('#gobiaAudioPlayer');
    if ((audio && !audio.paused) || /reprodu|hablando|generando/.test(voice)) return 'speaking';
    if (/proces|analiz|consult|busc|pens/.test(process)) return 'processing';
    if (/activ|escuch|grab/.test(mic)) return 'listening';
    return 'idle';
  }

  function drawWave(canvas, kind, mode) {
    const fitted = fit(canvas);
    if (!fitted) return;
    const { context: ctx, width: w, height: h } = fitted;
    const active = kind === 'user' ? mode === 'listening' : mode === 'speaking';
    const strength = active ? 1 : mode === 'processing' ? 0.28 : 0.09;
    const color = kind === 'user' ? [83, 224, 255] : [104, 255, 190];
    ctx.clearRect(0, 0, w, h);
    ctx.strokeStyle = `rgba(${color.join(',')},.08)`;
    ctx.lineWidth = 1;
    for (let y = 8; y < h; y += 12) {
      ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke();
    }
    const gradient = ctx.createLinearGradient(0, 0, w, 0);
    gradient.addColorStop(0, `rgba(${color.join(',')},0)`);
    gradient.addColorStop(.16, `rgba(${color.join(',')},.75)`);
    gradient.addColorStop(.5, `rgba(${color.join(',')},1)`);
    gradient.addColorStop(.84, `rgba(${color.join(',')},.75)`);
    gradient.addColorStop(1, `rgba(${color.join(',')},0)`);
    ctx.shadowColor = `rgb(${color.join(',')})`;
    ctx.shadowBlur = active ? 13 : 4;
    ctx.strokeStyle = gradient;
    ctx.lineWidth = active ? 2 : 1;
    ctx.beginPath();
    for (let x = 0; x <= w; x += 2) {
      const envelope = Math.sin((x / w) * Math.PI);
      const carrier = Math.sin(x * .07 + time * (kind === 'user' ? 3.4 : 2.5));
      const detail = Math.sin(x * .19 - time * 4.1) * .34 + Math.sin(x * .035 + time) * .5;
      const jitter = active ? Math.sin(x * .53 + time * 8) * .13 : 0;
      const y = h / 2 + (carrier + detail + jitter) * envelope * h * .25 * strength;
      if (!x) ctx.moveTo(x, y); else ctx.lineTo(x, y);
    }
    ctx.stroke();
    ctx.shadowBlur = 0;
  }

  function drawRadar(canvas, mode) {
    const fitted = fit(canvas);
    if (!fitted) return;
    const { context: ctx, width: w, height: h } = fitted;
    const cx = w / 2, cy = h / 2, radius = Math.min(w, h) * .42;
    ctx.clearRect(0, 0, w, h);
    ctx.strokeStyle = 'rgba(91,224,255,.15)';
    ctx.lineWidth = 1;
    for (let ring = 1; ring <= 4; ring++) {
      ctx.beginPath(); ctx.arc(cx, cy, radius * ring / 4, 0, Math.PI * 2); ctx.stroke();
    }
    for (let line = 0; line < 8; line++) {
      const a = line * Math.PI / 4;
      ctx.beginPath(); ctx.moveTo(cx, cy); ctx.lineTo(cx + Math.cos(a) * radius, cy + Math.sin(a) * radius); ctx.stroke();
    }
    const sweep = time * (mode === 'processing' ? 2.6 : .7);
    const grad = ctx.createRadialGradient(cx, cy, 0, cx, cy, radius);
    grad.addColorStop(0, 'rgba(103,255,192,.5)'); grad.addColorStop(1, 'rgba(103,255,192,0)');
    ctx.fillStyle = grad;
    ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, radius, sweep - .55, sweep); ctx.closePath(); ctx.fill();
    for (let i = 0; i < 9; i++) {
      const a = i * 2.399 + .4;
      const r = radius * (.22 + ((i * 37) % 69) / 100);
      const pulse = .45 + .55 * Math.sin(time * 2 + i);
      ctx.fillStyle = `rgba(104,255,190,${mode === 'processing' ? pulse : .25})`;
      ctx.beginPath(); ctx.arc(cx + Math.cos(a) * r, cy + Math.sin(a) * r, 1.5 + pulse * 1.5, 0, Math.PI * 2); ctx.fill();
    }
  }

  function syncMode(mode) {
    const root = $('.gobia-hud-panel');
    if (!root || root.dataset.hudMode === mode) return;
    root.dataset.hudMode = mode;
    const labels = { idle: 'SISTEMA EN ESPERA', listening: 'CAPTURANDO VOZ', processing: 'CRUZANDO INFORMACIÓN', speaking: 'TRANSMITIENDO RESPUESTA' };
    const modeLabel = $('#gobiaHudMode');
    if (modeLabel) modeLabel.textContent = labels[mode];
  }

  function tick(now) {
    time = now / 1000;
    const mode = currentMode();
    syncMode(mode);
    updatePacketRate(mode);
    drawWave(canvases.get('user'), 'user', mode);
    drawWave(canvases.get('alma'), 'alma', mode);
    drawRadar(canvases.get('radar'), mode);
    frame = requestAnimationFrame(tick);
  }

  function init() {
    buildBinaryStream();
    canvases.set('user', $('#gobiaUserWaveCanvas'));
    canvases.set('alma', $('#gobiaWaveCanvas'));
    canvases.set('radar', $('#gobiaRadarCanvas'));
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(tick);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
