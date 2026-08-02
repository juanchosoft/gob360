<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

requirePermission('asistente_ia.logs.view');

include './admin/classes/DbConection.php';
include './admin/classes/Util.php';

// Cargar logs desde la BD
$db  = new DbConection();
$pdo = $db->openConect();

$st = $pdo->prepare(
    "SELECT
        l.id,
        l.created_at,
        CONCAT(u.nombre, ' ', u.apellido) AS usuario,
        u.tipo                             AS rol,
        l.tool_nombre,
        l.filas_devueltas,
        l.duracion_ms,
        l.exito,
        l.error
     FROM tbl_ia_tool_logs l
     LEFT JOIN tbl_usuarios u ON u.id = l.tbl_usuario_id
     ORDER BY l.created_at DESC
     LIMIT 500"
);
$st->execute();
$logs = $st->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas rápidas
$stStats = $pdo->query(
    "SELECT
        COUNT(*)                            AS total_llamadas,
        SUM(exito)                          AS exitosas,
        COUNT(*) - SUM(exito)              AS fallidas,
        ROUND(AVG(duracion_ms))             AS duracion_promedio_ms,
        COUNT(DISTINCT tbl_usuario_id)      AS usuarios_distintos
     FROM tbl_ia_tool_logs
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
);
$stats = $stStats->fetch(PDO::FETCH_ASSOC);

$db->closeConect();
?>

<body class="navbar-fixed pcoded-navbar-position">
<?php include './admin/include/navbar.php'; ?>
<?php include './admin/include/header.php'; ?>

<div class="pcoded-main-container">
  <div class="pcoded-content">
    <div class="page-header">
      <div class="page-block">
        <div class="row align-items-center">
          <div class="col-md-12">
            <ul class="breadcrumb p-0 m-0">
              <li class="breadcrumb-item">Inicio</li>
              <li class="breadcrumb-item active">Asistente IA – Logs de Auditoría</li>
            </ul>
          </div>
          <div class="col-md-12">
            <div class="page-header-title">
              <h5 class="m-b-10"><i class="fas fa-robot text-success mr-2"></i>Asistente IA – Logs de Auditoría</h5>
              <p class="text-muted m-0">Registro de herramientas ejecutadas por el asistente (últimos 500 · últimos 7 días en estadísticas)</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
      <div class="col-md-2 col-sm-4">
        <div class="card text-center">
          <div class="card-body py-3">
            <h4 class="text-primary"><?= number_format((int)($stats['total_llamadas'] ?? 0)) ?></h4>
            <small class="text-muted">Llamadas (7 días)</small>
          </div>
        </div>
      </div>
      <div class="col-md-2 col-sm-4">
        <div class="card text-center">
          <div class="card-body py-3">
            <h4 class="text-success"><?= number_format((int)($stats['exitosas'] ?? 0)) ?></h4>
            <small class="text-muted">Exitosas</small>
          </div>
        </div>
      </div>
      <div class="col-md-2 col-sm-4">
        <div class="card text-center">
          <div class="card-body py-3">
            <h4 class="text-danger"><?= number_format((int)($stats['fallidas'] ?? 0)) ?></h4>
            <small class="text-muted">Fallidas</small>
          </div>
        </div>
      </div>
      <div class="col-md-2 col-sm-4">
        <div class="card text-center">
          <div class="card-body py-3">
            <h4 class="text-warning"><?= number_format((int)($stats['duracion_promedio_ms'] ?? 0)) ?> ms</h4>
            <small class="text-muted">Duración promedio</small>
          </div>
        </div>
      </div>
      <div class="col-md-2 col-sm-4">
        <div class="card text-center">
          <div class="card-body py-3">
            <h4 class="text-info"><?= number_format((int)($stats['usuarios_distintos'] ?? 0)) ?></h4>
            <small class="text-muted">Usuarios únicos</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de logs -->
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-list mr-2"></i>Detalle de Llamadas</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="tbl-logs" class="table table-sm table-hover" style="width:100%">
            <thead class="thead-dark">
              <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Herramienta</th>
                <th>Filas</th>
                <th>Duración</th>
                <th>Estado</th>
                <th>Error</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
              <tr>
                <td><?= (int)$log['id'] ?></td>
                <td><?= htmlspecialchars($log['created_at'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['usuario'] ?? '—') ?></td>
                <td><small class="badge badge-secondary"><?= htmlspecialchars($log['rol'] ?? '') ?></small></td>
                <td><code class="text-success"><?= htmlspecialchars($log['tool_nombre'] ?? '') ?></code></td>
                <td class="text-center"><?= (int)$log['filas_devueltas'] ?></td>
                <td class="text-center"><?= (int)$log['duracion_ms'] ?> ms</td>
                <td class="text-center">
                  <?php if ($log['exito']): ?>
                    <span class="badge badge-success">OK</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Error</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($log['error'])): ?>
                    <span class="text-danger" style="font-size:12px" title="<?= htmlspecialchars($log['error']) ?>">
                      <?= htmlspecialchars(mb_substr($log['error'], 0, 60)) ?><?= mb_strlen($log['error']) > 60 ? '…' : '' ?>
                    </span>
                  <?php else: ?>—<?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /pcoded-content -->
</div><!-- /pcoded-main-container -->

<?php include 'admin/include/gerenic_script.php'; ?>
<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script src="assets/js/pcoded.min.js"></script>

<script>
$(document).ready(function () {
  $('#tbl-logs').DataTable({
    order: [[0, 'desc']],
    pageLength: 25,
    language: {
      url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
    },
    columnDefs: [
      { targets: [5, 6, 7], className: 'text-center' },
    ]
  });
});
</script>
</body>
</html>
