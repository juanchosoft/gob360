<!-- jQuery -->
<script src="./plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="./plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="./plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<!-- <script src="./plugins/sparklines/sparkline.js"></script> -->
<!-- JQVMap -->
<script src="./plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="./plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="./plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="./plugins/moment/moment.min.js"></script>
<script src="./plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="./plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="./plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="./plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>

<!--  Plugin for Sweet Alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!--Js de nosotros que hemos agregado -->
<script type="text/javascript" src="admin/js/lib/util.js"></script>
<script type="text/javascript" src="admin/js/jquery/solonumeros.js"></script>
<script type="text/javascript" src="admin/js/jquery/numeral.min.2.0.6.js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script>
  feather.replace();
</script>


<!-- Select2 -->
<script src="./plugins/select2/js/select2.full.min.js"></script>


<!-- Select2 CDN duplicado removido: ya se carga la version local arriba -->

<?php
// ── Asistente IA Claude – Inyección condicional ──────────────────────────────
// Verificar sesión y permiso directamente desde la sesión (SessionData puede no estar cargada)
$_ia_tipo         = $_SESSION['session_user']['tipo'] ?? '';
$_ia_permission_keys = $_SESSION['session_user']['permission_keys'] ?? [];
$_ia_puede_chat   = $_ia_tipo === 'SuperAdministrador'
                 || in_array('asistente_ia.chat.use', (array) $_ia_permission_keys, true);
$_ia_puede_voz    = $_ia_tipo === 'SuperAdministrador'
                 || in_array('asistente_ia.voz.use', (array) $_ia_permission_keys, true);

if (isset($_SESSION['session_user']) && $_ia_puede_chat && empty($ocultarWidgetIa)):
    require_once __DIR__ . '/../classes/ia/IaScope.php';
    $_ia_bienvenida = IaScope::mensajeBienvenida();
?>
<link rel="stylesheet" href="assets/css/ia-widget.css?v=<?= filemtime(__DIR__ . '/../../assets/css/ia-widget.css') ?>">

<!-- Widget HTML -->
<button id="ia-fab" title="Abrir ALMA">
  <i class="feather icon-cpu"></i>
</button>

<div id="ia-widget" class="ia-hidden">
  <!-- Sidebar de historial -->
  <div id="ia-sidebar">
    <div id="ia-sidebar-header">
      <div class="ia-logo">A</div>
      <span>ALMA</span>
    </div>
    <button id="ia-btn-nueva">
      <i class="feather icon-plus"></i> Nueva conversación
    </button>
    <div id="ia-conv-list"></div>
  </div>

  <!-- Panel de chat principal -->
  <div id="ia-main">
    <div id="ia-main-header">
      <span id="ia-conv-titulo">Nueva conversación</span>
      <div id="ia-header-actions">
        <div id="ia-mode-toggle">
          <button class="ia-mode-btn ia-mode-active" data-mode="texto">
            <i class="feather icon-type"></i> Texto
          </button>
          <?php if ($_ia_puede_voz): ?>
          <button class="ia-mode-btn" data-mode="voz">
            <i class="feather icon-mic"></i> En vivo
          </button>
          <?php endif; ?>
        </div>
        <button id="ia-btn-close" title="Cerrar">&times;</button>
      </div>
    </div>

    <div id="ia-messages">
      <div id="ia-bienvenida">
        <div class="ia-bienvenida-icono"><i class="feather icon-cpu"></i></div>
        <h3><?= htmlspecialchars($_ia_bienvenida['titulo']) ?></h3>
        <p><?= $_ia_bienvenida['texto'] ?></p>
      </div>
    </div>

    <div id="ia-input-area">
      <div id="ia-input-row">
        <textarea id="ia-input" rows="1" placeholder="Escribe tu pregunta…"></textarea>
        <button id="ia-btn-send" title="Enviar"><i class="feather icon-navigation"></i></button>
        <button id="ia-btn-mic" title="Grabar audio" style="display:none">
          <i class="feather icon-mic"></i>
        </button>
      </div>
      <div id="ia-voz-estado"></div>
    </div>
  </div>
</div>

<script>
window.IA_WIDGET_CFG = {
  voz: <?= $_ia_puede_voz ? 'true' : 'false' ?>,
  bienvenidaTitulo: <?= json_encode($_ia_bienvenida['titulo'], JSON_UNESCAPED_UNICODE) ?>,
  bienvenidaTexto: <?= json_encode($_ia_bienvenida['texto'], JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="assets/js/ia-widget.js?v=<?= filemtime(__DIR__ . '/../../assets/js/ia-widget.js') ?>"></script>
<?php endif; ?>
