<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$modulo = 'Abogado IA Asesor de Despacho';

date_default_timezone_set('America/Bogota');

function buscarNombreSesion($array) {
  $keys = ['nombre', 'name', 'user_name', 'usuario', 'username', 'nombres', 'fullname'];

  foreach ($keys as $key) {
    if (!empty($array[$key]) && is_string($array[$key])) {
      return trim($array[$key]);
    }
  }

  foreach ($array as $value) {
    if (is_array($value)) {
      $nombre = buscarNombreSesion($value);
      if ($nombre !== '') return $nombre;
    } elseif (is_object($value)) {
      $nombre = buscarNombreSesion(get_object_vars($value));
      if ($nombre !== '') return $nombre;
    }
  }

  return '';
}

$nombreUsuario = buscarNombreSesion($_SESSION ?? []);
if ($nombreUsuario === '') {
  $nombreUsuario = 'Usuario';
}

$hora = (int) date('H');

if ($hora >= 5 && $hora < 12) {
  $saludo = 'Buenos días';
} elseif ($hora >= 12 && $hora < 19) {
  $saludo = 'Buenas tardes';
} else {
  $saludo = 'Buenas noches';
}

$mensajeInicial = $saludo . ' ' . $nombreUsuario . ', ¿en qué te puedo ayudar?';

/*
  WEBHOOKS N8N
  IMPORTANTE:
  - El webhook de analizar documento debe estar Published / Active.
  - El documento Word se genera directamente desde el navegador, no desde n8n.
*/
$webhookAnalizar = 'https://n8n.spidersoftwareia.com/webhook/abogado-ia-analizar-documento';
$webhookChat = 'https://n8n.spidersoftwareia.com/webhook/a7c47f76-1678-423a-bf78-ce1fce27aaa2/chat';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <?php include './admin/include/generic_head.php'; ?>

  <link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet">

  <style>
    :root {
      --nav-blue: #20427F;
      --nav-blue-2: #132b52;
      --bg0: #0b1220;
      --bg1: #0e1830;
      --shadow: 0 18px 55px rgba(0,0,0,.38);
    }

    html,
    body {
      min-height: 100%;
      overflow-x: hidden;
    }

    .loader-bg {
      display: none !important;
    }

    .pcoded-main-container {
      background:
        radial-gradient(900px 600px at 18% 10%, rgba(120,88,255,.18), transparent 55%),
        radial-gradient(900px 600px at 85% 18%, rgba(0,187,255,.14), transparent 55%),
        linear-gradient(180deg, var(--bg0), var(--bg1));
      min-height: 100vh;
    }

    .pcoded-content {
      padding-top: 10px;
    }

    .au-hero {
      position: relative;
      border-radius: 18px;
      overflow: hidden;
      margin: 8px 0 12px;
      box-shadow: var(--shadow);
      border: 1px solid rgba(255,255,255,.08);
      background: rgba(255,255,255,.05);
    }

    .au-hero__bg {
      position: absolute;
      inset: 0;
      background:
        radial-gradient(900px 520px at 18% 18%, rgba(0,187,255,.16), transparent 62%),
        radial-gradient(900px 520px at 82% 18%, rgba(120,88,255,.18), transparent 62%),
        linear-gradient(135deg, rgba(32,62,92,.92), rgba(47,63,110,.82));
    }

    .au-hero__content {
      position: relative;
      padding: 14px 16px;
      color: #fff;
    }

    .au-kicker {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-weight: 900;
      font-size: 11px;
      text-transform: uppercase;
      color: rgba(255,255,255,.72);
    }

    .au-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: linear-gradient(135deg, #22c1ff, #7b61ff);
    }

    .au-title {
      margin: 4px 0 0;
      font-weight: 1000;
      color: rgba(226,232,240,.96);
    }

    .au-subtitle {
      color: rgba(255,255,255,.72);
      font-size: 12px;
      margin-top: 2px;
    }

    .au-layout {
      align-items: stretch;
    }

    .document-panel {
      height: 100%;
      min-height: 500px;
      padding: 16px;
      border-radius: 16px;
      background: #ffffff;
      box-shadow: 0 18px 45px rgba(0,0,0,.14);
      border: 1px solid rgba(15,23,42,.08);
    }

    .document-panel__header h5 {
      margin: 0;
      color: #20427F;
      font-weight: 900;
    }

    .document-panel__header p {
      margin: 4px 0 12px;
      color: #64748b;
      font-size: 13px;
    }

    .document-panel label {
      font-weight: 800;
      color: #0f172a;
      font-size: 13px;
      margin-bottom: 6px;
    }

    .document-panel textarea,
    .document-panel input[type="file"] {
      border-radius: 12px;
      border: 1px solid rgba(15,23,42,.18);
      font-size: 13px;
    }

    .document-panel textarea {
      min-height: 86px;
      resize: vertical;
    }

    .document-panel .btn {
      border-radius: 12px;
      font-weight: 900;
    }

    .document-status {
      margin-top: 12px;
      font-size: 13px;
      color: #20427F;
      font-weight: 800;
      word-break: break-word;
    }

    .document-result {
      margin-top: 16px;
      padding: 14px;
      border-radius: 14px;
      background: #f8fafc;
      border: 1px solid rgba(15,23,42,.08);
      max-height: 380px;
      overflow-y: auto;
    }

    .document-result h5 {
      color: #20427F;
      font-weight: 900;
      margin-bottom: 10px;
    }

    #conceptoDocumento {
      white-space: pre-wrap;
      color: #111827;
      line-height: 1.55;
      font-size: 13px;
      word-break: break-word;
    }

    .document-actions {
      margin-top: 16px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }

    .document-actions p {
      width: 100%;
      margin-bottom: 4px;
      color: #0f172a;
    }

    .chat-page-wrapper {
      width: 100%;
      height: calc(100vh - 220px);
      min-height: 500px;
      position: relative;
      overflow: hidden;
      border-radius: 16px;
      background: #fff;
      box-shadow: 0 22px 60px rgba(0,0,0,.18);
      border: 1px solid rgba(15,23,42,.08);
    }

    #n8n-chat {
      width: 100% !important;
      height: 100% !important;
      min-height: 500px !important;
      display: block !important;
      position: relative !important;
      background: #ffffff !important;
      overflow: hidden !important;
    }

    #n8n-chat > div {
      width: 100% !important;
      height: 100% !important;
    }

    #n8n-chat .chat-window,
    #n8n-chat [class*="chat-window"] {
      position: absolute !important;
      inset: 0 !important;
      display: flex !important;
      flex-direction: column !important;
      width: 100% !important;
      height: 100% !important;
      max-width: none !important;
      max-height: none !important;
      opacity: 1 !important;
      visibility: visible !important;
      border: 0 !important;
      border-radius: 16px !important;
      box-shadow: none !important;
      overflow: hidden !important;
      background: #fff !important;
    }

    .chat-window-toggle,
    .chat-toggle,
    button[aria-label="Open chat"],
    button[aria-label="Toggle chat"],
    button[title="Open chat"],
    button[title="Toggle chat"] {
      display: none !important;
    }

    #n8n-chat .chat-header {
      flex: 0 0 56px !important;
      height: 56px !important;
      min-height: 56px !important;
      background: linear-gradient(135deg, #20427F, #132b52) !important;
      border-bottom: 1px solid rgba(255,255,255,.12) !important;
      padding: 17px 18px !important;
    }

    #n8n-chat .chat-header h1,
    #n8n-chat .chat-header p,
    #n8n-chat .chat-header button,
    #n8n-chat .chat-close-button {
      display: none !important;
    }

    #n8n-chat .chat-header::before {
      content: "Asistente IA";
      color: #fff;
      font-size: 15px;
      font-weight: 900;
    }

    #n8n-chat .chat-messages-list,
    #n8n-chat [class*="messages"] {
      flex: 1 1 auto !important;
      min-height: 0 !important;
      overflow-y: auto !important;
      padding: 18px !important;
      background: #f8fafc !important;
    }

    #n8n-chat .chat-message {
      max-width: 78% !important;
      border-radius: 14px !important;
      font-size: 14px !important;
    }

    #n8n-chat .chat-footer,
    #n8n-chat [class*="footer"] {
      display: flex !important;
      flex: 0 0 auto !important;
      visibility: visible !important;
      opacity: 1 !important;
      background: #fff !important;
      border-top: 1px solid rgba(15,23,42,.10) !important;
      padding: 12px 16px !important;
      min-height: 76px !important;
    }

    #n8n-chat .chat-input,
    #n8n-chat [class*="chat-input"],
    #n8n-chat form {
      display: flex !important;
      visibility: visible !important;
      opacity: 1 !important;
      width: 100% !important;
    }

    #n8n-chat textarea,
    #n8n-chat input[type="text"],
    #n8n-chat input {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      width: 100% !important;
      min-height: 44px !important;
      border-radius: 14px !important;
      border: 1px solid rgba(15,23,42,.16) !important;
      background: #fff !important;
      color: #0f172a !important;
      font-size: 14px !important;
    }

    #n8n-chat button[type="submit"],
    #n8n-chat .chat-input button {
      display: flex !important;
      visibility: visible !important;
      opacity: 1 !important;
      align-items: center !important;
      justify-content: center !important;
      border-radius: 14px !important;
      background: linear-gradient(135deg, #20427F, #132b52) !important;
      border: none !important;
    }

    #n8n-chat .chat-powered-by {
      display: none !important;
    }

    @media (max-width: 991px) {
      .document-panel {
        min-height: auto;
        margin-bottom: 14px;
      }

      .chat-page-wrapper {
        height: calc(100vh - 160px);
        min-height: 420px;
      }

      #n8n-chat {
        min-height: 420px !important;
      }

      #n8n-chat .chat-message {
        max-width: 90% !important;
        font-size: 13px !important;
      }
    }

    @media (max-width: 576px) {
      .chat-page-wrapper {
        height: calc(100vh - 145px);
        min-height: 390px;
      }

      #n8n-chat {
        min-height: 390px !important;
      }

      #n8n-chat .chat-header {
        flex: 0 0 50px !important;
        height: 50px !important;
        min-height: 50px !important;
      }

      #n8n-chat .chat-footer {
        min-height: 72px !important;
        padding: 10px !important;
      }

      .document-actions .btn {
        width: 100%;
      }
    }
  </style>
</head>

<body>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <div class="row">
        <div class="col-12">

          <div class="au-hero">
            <div class="au-hero__bg"></div>
            <div class="au-hero__content">
              <div class="au-kicker">
                <span class="au-dot"></span>
                Acción Unificada
              </div>

              <h3 class="au-title">
                <?php echo htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8'); ?>
              </h3>

              <div class="au-subtitle">
                Asesor virtual especializado en y asesoría jurídica, con conocimientos orientados a los procesos, normas y procedimientos del Estado.
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="row au-layout">
        <div class="col-lg-4 col-md-12 mb-3">
          <div class="document-panel">
            <div class="document-panel__header">
              <h5>Analizar documento jurídico</h5>
              <p>Sube PDF, Word o imagen escaneada para que el Abogado IA emita un concepto jurídico.</p>
            </div>

            <form id="formDocumento" enctype="multipart/form-data" method="post">
              <div class="form-group">
                <label for="consultaDocumento">Consulta o instrucción</label>
                <textarea
                  id="consultaDocumento"
                  name="consulta"
                  class="form-control"
                  rows="3"
                  placeholder="Ejemplo: Revisa este contrato estatal y dime si hay incumplimiento, riesgos y ruta jurídica."
                ></textarea>
              </div>

              <div class="form-group">
                <label for="archivoDocumento">Documento</label>
                <input
                  type="file"
                  id="archivoDocumento"
                  name="documento"
                  class="form-control"
                  accept=".pdf,application/pdf"
                  required
                >
                <small class="text-muted">
                  Formatos permitidos: PDF Tamaño máximo: 20 MB.
                </small>
              </div>

              <button type="submit" class="btn btn-primary btn-block">
                Analizar documento
              </button>
            </form>

            <div id="estadoDocumento" class="document-status"></div>

            <div id="resultadoDocumento" class="document-result" style="display:none;">
              <h5 id="tituloResultadoDocumento">Concepto jurídico</h5>
              <div id="conceptoDocumento"></div>

              <div class="document-actions" id="accionesDocumento" style="display:none;">
                <p><strong>¿Deseas generar este concepto en documento?</strong></p>

                <button type="button" id="btnSoloRespuesta" class="btn btn-secondary">
                  No, solo respuesta
                </button>

                <button type="button" id="btnGenerarDocumento" class="btn btn-success">
                  Sí, generar documento
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-8 col-md-12 mb-3">
          <div class="chat-page-wrapper">
            <div id="n8n-chat"></div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    const WEBHOOK_ANALIZAR = <?php echo json_encode($webhookAnalizar, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    let ultimoConcepto = null;

    const formDocumento = document.getElementById('formDocumento');
    const estadoDocumento = document.getElementById('estadoDocumento');
    const resultadoDocumento = document.getElementById('resultadoDocumento');
    const conceptoDocumento = document.getElementById('conceptoDocumento');
    const tituloResultadoDocumento = document.getElementById('tituloResultadoDocumento');
    const accionesDocumento = document.getElementById('accionesDocumento');
    const btnSoloRespuesta = document.getElementById('btnSoloRespuesta');
    const btnGenerarDocumento = document.getElementById('btnGenerarDocumento');
    const archivoDocumento = document.getElementById('archivoDocumento');
    const consultaDocumento = document.getElementById('consultaDocumento');

    function obtenerTextoConcepto(data) {
      if (!data) return '';

      if (typeof data === 'string') {
        return data;
      }

      return (
        data.concepto ||
        data.concepto_final ||
        data.analisis_juridico ||
        data.resumen ||
        data.respuesta ||
        data.output ||
        data.text ||
        data.message ||
        data.error ||
        data.mensaje ||
        JSON.stringify(data, null, 2)
      );
    }

    function obtenerExtension(nombreArchivo) {
      return String(nombreArchivo || '')
        .split('.')
        .pop()
        .toLowerCase()
        .trim();
    }

    function validarArchivo(archivo) {
      const maxSize = 20 * 1024 * 1024;

      const extensionesPermitidas = [
        'pdf',
        'doc',
        'docx',
        'jpg',
        'jpeg',
        'png'
      ];

      const mimePermitidos = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png'
      ];

      const extension = obtenerExtension(archivo.name);
      const mime = String(archivo.type || '').toLowerCase();

      if (!extensionesPermitidas.includes(extension)) {
        return {
          ok: false,
          mensaje: 'Formato no permitido. Solo se permiten PDF, DOC, DOCX, JPG, JPEG y PNG.'
        };
      }

      if (mime && !mimePermitidos.includes(mime)) {
        return {
          ok: false,
          mensaje: 'Tipo de archivo no permitido. Verifica que sea PDF, Word o imagen JPG/PNG.'
        };
      }

      if (archivo.size > maxSize) {
        return {
          ok: false,
          mensaje: 'El archivo supera los 20 MB.'
        };
      }

      return {
        ok: true,
        extension,
        mime
      };
    }

    function bloquearFormulario(bloquear) {
      const boton = formDocumento.querySelector('button[type="submit"]');

      if (boton) {
        boton.disabled = bloquear;
        boton.textContent = bloquear ? 'Analizando...' : 'Analizar documento';
      }

      if (archivoDocumento) archivoDocumento.disabled = bloquear;
      if (consultaDocumento) consultaDocumento.disabled = bloquear;
    }

    function mostrarResultado(texto, esError = false) {
      resultadoDocumento.style.display = 'block';
      conceptoDocumento.textContent = texto || '';
      tituloResultadoDocumento.textContent = esError ? 'Error del análisis' : 'Concepto jurídico';
      accionesDocumento.style.display = esError ? 'none' : 'flex';
    }

    function mostrarError(mensaje) {
      ultimoConcepto = null;
      estadoDocumento.textContent = 'Ocurrió un problema al analizar el documento.';
      mostrarResultado(mensaje, true);
    }

    function escapeHtmlDocumento(value = '') {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function textoPlanoDocumento(value) {
      if (value === undefined || value === null) return '';

      if (Array.isArray(value)) {
        return value
          .map(item => String(item ?? '').trim())
          .filter(Boolean)
          .join('\n');
      }

      if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
      }

      return String(value)
        .replace(/\r/g, '')
        .replace(/\n{3,}/g, '\n\n')
        .trim();
    }

    function parrafoDocumento(value, fallback = '') {
      const text = textoPlanoDocumento(value) || fallback;
      return escapeHtmlDocumento(text).replace(/\n/g, '<br>');
    }

    function listaDocumento(value) {
      const arr = Array.isArray(value)
        ? value
        : String(value || '')
            .split(/\n|;/)
            .map(v => v.trim())
            .filter(Boolean);

      if (!arr.length) {
        return '<p>No se identificaron elementos específicos con la información suministrada.</p>';
      }

      return `
        <ul>
          ${arr.map(item => `<li>${escapeHtmlDocumento(item)}</li>`).join('')}
        </ul>
      `;
    }

    function descargarWordDesdeConcepto(data) {
      const fecha = new Date().toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });

      const resumen = textoPlanoDocumento(data.resumen);
      const problema = textoPlanoDocumento(data.problema_juridico);
      const analisis = textoPlanoDocumento(data.analisis_juridico);
      const riesgos = data.riesgos || [];
      const recomendaciones = data.recomendaciones || [];
      const pruebas = data.pruebas_necesarias || [];
      const ruta = textoPlanoDocumento(data.ruta_accion);

      const concepto = textoPlanoDocumento(
        data.concepto_final ||
        data.concepto ||
        data.respuesta ||
        data.output ||
        data.text ||
        obtenerTextoConcepto(data)
      );

      const html = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Concepto Jurídico</title>
  <style>
    @page {
      margin: 2.5cm;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 12pt;
      color: #111827;
      line-height: 1.55;
    }

    h1 {
      color: #20427F;
      text-align: center;
      font-size: 18pt;
      margin-bottom: 26px;
      text-transform: uppercase;
    }

    h2 {
      color: #20427F;
      font-size: 13.5pt;
      border-bottom: 1px solid #d1d5db;
      padding-bottom: 5px;
      margin-top: 22px;
    }

    p {
      text-align: justify;
      margin: 8px 0 12px 0;
    }

    ul {
      margin-top: 6px;
      margin-bottom: 12px;
    }

    li {
      margin-bottom: 6px;
      text-align: justify;
    }

    .fecha {
      text-align: right;
      margin-bottom: 24px;
      font-size: 11pt;
    }

    .asunto {
      margin-bottom: 22px;
      padding: 12px;
      background: #f3f6fb;
      border-left: 4px solid #20427F;
    }

    .nota {
      font-size: 10pt;
      color: #555;
      margin-top: 30px;
      border-top: 1px solid #ddd;
      padding-top: 10px;
      text-align: justify;
    }

    .firma {
      margin-top: 45px;
    }
  </style>
</head>
<body>

  <div class="fecha">Colombia, ${escapeHtmlDocumento(fecha)}</div>

  <h1>Concepto Jurídico</h1>

  <div class="asunto">
    <strong>Asunto:</strong> Concepto jurídico generado a partir del análisis documental suministrado.
  </div>

  <h2>1. Resumen del caso</h2>
  <p>${parrafoDocumento(resumen, 'No se suministró un resumen específico del caso.')}</p>

  <h2>2. Problema jurídico</h2>
  <p>${parrafoDocumento(problema, 'No se identificó un problema jurídico específico con la información suministrada.')}</p>

  <h2>3. Análisis jurídico</h2>
  <p>${parrafoDocumento(analisis || concepto, 'No se generó análisis jurídico específico.')}</p>

  <h2>4. Riesgos identificados</h2>
  ${listaDocumento(riesgos)}

  <h2>5. Recomendaciones</h2>
  ${listaDocumento(recomendaciones)}

  <h2>6. Documentos o pruebas necesarias</h2>
  ${listaDocumento(pruebas)}

  <h2>7. Ruta de acción sugerida</h2>
  <p>${parrafoDocumento(ruta, 'No se definió una ruta de acción específica.')}</p>

  <h2>8. Concepto final</h2>
  <p>${parrafoDocumento(concepto || analisis || resumen, 'No se generó concepto final.')}</p>

  <div class="firma">
    <p>Atentamente,</p>
    <br><br>
    <p>
      <strong>Abogado IA</strong><br>
      Especialista en Derecho Público, Derecho Administrativo,<br>
      Contratación Estatal y Responsabilidad del Estado.
    </p>
  </div>

  <p class="nota">
    Este documento corresponde a una orientación jurídica técnica generada con apoyo de IA.
    No reemplaza la revisión integral, personalizada y definitiva de un abogado con acceso completo
    a los documentos, pruebas, antecedentes y términos procesales del caso.
  </p>

</body>
</html>
`;

      const blob = new Blob(['\ufeff', html], {
        type: 'application/msword;charset=utf-8'
      });

      if (!blob || blob.size === 0) {
        throw new Error('No se pudo generar el documento desde el navegador.');
      }

      const url = window.URL.createObjectURL(blob);
      const nombreArchivo = 'concepto-juridico-' + Date.now() + '.doc';

      const a = document.createElement('a');
      a.href = url;
      a.download = nombreArchivo;
      document.body.appendChild(a);
      a.click();
      a.remove();

      window.URL.revokeObjectURL(url);
    }

    formDocumento.addEventListener('submit', async function (event) {
      event.preventDefault();
      event.stopPropagation();

      const archivo = archivoDocumento.files[0];
      const consulta = consultaDocumento.value.trim();

      if (!archivo) {
        alert('Selecciona un documento.');
        return;
      }

      const validacion = validarArchivo(archivo);

      if (!validacion.ok) {
        alert(validacion.mensaje);
        return;
      }

      const formData = new FormData();

      formData.append('documento', archivo);
      formData.append('consulta', consulta || 'Analiza este documento y emite concepto jurídico.');
      formData.append('nombre_archivo', archivo.name);
      formData.append('extension_archivo', validacion.extension);
      formData.append('mime_archivo', validacion.mime || '');

      estadoDocumento.textContent = 'Analizando documento, por favor espera...';
      resultadoDocumento.style.display = 'none';
      conceptoDocumento.textContent = '';
      accionesDocumento.style.display = 'none';
      ultimoConcepto = null;
      bloquearFormulario(true);

      try {
        const response = await fetch(WEBHOOK_ANALIZAR, {
          method: 'POST',
          body: formData
        });

        const rawText = await response.text();

        if (!rawText || rawText.trim() === '') {
          throw new Error('n8n devolvió una respuesta vacía. Revisa que todas las ramas del workflow terminen en Respond to Webhook.');
        }

        let data = null;

        try {
          data = JSON.parse(rawText);
        } catch (e) {
          throw new Error('n8n devolvió una respuesta que no es JSON válido: ' + rawText.substring(0, 700));
        }

        if (!response.ok || data.ok === false) {
          throw new Error(data.error || data.mensaje || 'Error al analizar el documento.');
        }

        ultimoConcepto = data;

        const textoConcepto = obtenerTextoConcepto(data);

        mostrarResultado(textoConcepto, false);
        estadoDocumento.textContent = data.pregunta_documento || 'Análisis finalizado.';

      } catch (error) {
        console.error(error);
        mostrarError(
          error.message ||
          'No se pudo analizar el documento. Revisa el flujo de n8n o el archivo cargado.'
        );
      } finally {
        bloquearFormulario(false);
      }
    });

    btnSoloRespuesta.addEventListener('click', function () {
      estadoDocumento.textContent = 'Listo. El concepto quedó como respuesta en pantalla.';
    });

    btnGenerarDocumento.addEventListener('click', function () {
      if (!ultimoConcepto) {
        alert('Primero analiza un documento.');
        return;
      }

      try {
        estadoDocumento.textContent = 'Generando documento descargable...';
        btnGenerarDocumento.disabled = true;

        descargarWordDesdeConcepto(ultimoConcepto);

        estadoDocumento.textContent = 'Documento generado correctamente.';

      } catch (error) {
        console.error(error);
        estadoDocumento.textContent = error.message || 'No se pudo generar el documento.';
      } finally {
        btnGenerarDocumento.disabled = false;
      }
    });
  </script>

  <script type="module">
    import { createChat } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

    const mensajeInicial = <?php echo json_encode($mensajeInicial, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const webhookChat = <?php echo json_encode($webhookChat, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const chatTarget = document.querySelector('#n8n-chat');

    if (chatTarget) {
      chatTarget.innerHTML = '';

      try {
        createChat({
          webhookUrl: webhookChat,
          target: '#n8n-chat',
          mode: 'fullscreen',
          showWelcomeScreen: false,
          loadPreviousSession: false,
          enableStreaming: false,
          initialMessages: [
            mensajeInicial
          ],
          i18n: {
            es: {
              title: 'Abogado IA',
              subtitle: 'Asistente jurídico',
              footer: '',
              getStarted: 'Iniciar conversación',
              inputPlaceholder: 'Escribe tu consulta jurídica...'
            }
          }
        });

        setTimeout(() => {
          document.querySelectorAll(
            '.chat-window-toggle, .chat-toggle, button[aria-label="Open chat"], button[aria-label="Toggle chat"], button[title="Open chat"], button[title="Toggle chat"]'
          ).forEach(btn => btn.remove());
        }, 1000);

      } catch (error) {
        console.error('Error cargando n8n chat:', error);
        chatTarget.innerHTML = `
          <div style="padding:20px;color:#0f172a;font-family:Arial,sans-serif;">
            <strong>No se pudo cargar el chat.</strong><br>
            Revisa el webhook de chat o la consola del navegador.
          </div>
        `;
      }
    } else {
      console.error('No existe el contenedor #n8n-chat');
    }
  </script>

</body>
</html>