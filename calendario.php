<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$modulo = 'Calendario institucional';
date_default_timezone_set('America/Bogota');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['calendar_csrf'])) {
    $_SESSION['calendar_csrf'] = bin2hex(random_bytes(24));
}
$googleConnected = !empty($_SESSION['google_calendar_token']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include './admin/include/generic_head.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.css">
    <link rel="stylesheet" href="assets/css/calendario.css">
</head>
<body>
    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <div class="pcoded-content calendario-page">
            <div class="row">
                <div class="col-12">
                    <div class="cal-hero">
                        <div class="cal-hero__bg"></div>
                        <div class="cal-hero__content">
                            <div>
                                <div class="cal-kicker">
                                    <span class="cal-dot"></span>
                                    Agenda GOB360
                                </div>
                                <h3 class="cal-title"><?php echo htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8'); ?></h3>
                                <div class="cal-subtitle">Programe reuniones, compromisos y actividades sincronizadas con Google Calendar.</div>
                            </div>

                            <div class="cal-hero__actions">
                                <?php if ($googleConnected): ?>
                                    <span class="cal-status cal-status--connected">
                                        <i class="feather icon-check-circle"></i>
                                        Google conectado
                                    </span>
                                    <a class="btn btn-light btn-sm" href="google/disconnect.php">
                                        <i class="feather icon-log-out"></i> Desconectar
                                    </a>
                                <?php else: ?>
                                    <a class="btn cal-google-btn" href="google/connect.php">
                                        <span class="cal-google-g">G</span> Conectar Google Calendar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <?php if (!$googleConnected): ?>
                        <div class="card cal-empty-card">
                            <div class="card-body cal-empty">
                                <div class="cal-empty__icon"><i class="feather icon-calendar"></i></div>
                                <h4>Conecte su calendario para comenzar</h4>
                                <p>Los eventos creados o modificados aquí se reflejarán en Google Calendar. Los cambios realizados en Google aparecerán al actualizar esta agenda.</p>
                                <a class="btn cal-google-btn" href="google/connect.php">
                                    <span class="cal-google-g">G</span> Conectar con Google
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card cal-calendar-card">
                            <div class="card-header cal-card-header">
                                <div>
                                    <h5><i class="feather icon-calendar"></i> Agenda de actividades</h5>
                                    <small>Seleccione una fecha para crear un evento o pulse un evento para editarlo.</small>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" id="newEventButton">
                                    <i class="feather icon-plus"></i> Nuevo evento
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="calendar" aria-label="Calendario de eventos"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content cal-modal-content">
                <form id="eventForm">
                    <div class="modal-header cal-modal-header">
                        <div>
                            <small>AGENDA INSTITUCIONAL</small>
                            <h5 class="modal-title" id="eventModalTitle">Nuevo evento</h5>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="eventId">
                        <div class="form-group">
                            <label for="eventTitle">Título <span class="text-danger">*</span></label>
                            <input id="eventTitle" class="form-control" maxlength="250" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="eventStart">Inicio</label>
                                <input id="eventStart" class="form-control" type="datetime-local" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="eventEnd">Final</label>
                                <input id="eventEnd" class="form-control" type="datetime-local" required>
                            </div>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input id="eventAllDay" class="custom-control-input" type="checkbox">
                            <label class="custom-control-label" for="eventAllDay">Evento de todo el día</label>
                        </div>
                        <div class="form-group">
                            <label for="eventLocation">Lugar o enlace</label>
                            <input id="eventLocation" class="form-control" maxlength="1000" placeholder="Sala, municipio o enlace de reunión">
                        </div>
                        <div class="form-group mb-1">
                            <label for="eventDescription">Descripción</label>
                            <textarea id="eventDescription" class="form-control" rows="4" maxlength="8000"></textarea>
                        </div>
                        <div id="formError" class="cal-form-error" role="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button id="deleteEvent" class="btn btn-outline-danger mr-auto" type="button"><i class="feather icon-trash-2"></i> Eliminar</button>
                        <button class="btn btn-light" type="button" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" type="submit"><i class="feather icon-save"></i> Guardar y sincronizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="calendarToast" class="cal-toast" role="status" aria-live="polite"></div>

    <?php
    $footerFile = './admin/include/footer.php';
    if (is_file($footerFile)) {
        include $footerFile;
    }
    ?>

    <script>
        window.CALENDAR_CONFIG = <?php echo json_encode([
            'connected' => $googleConnected,
            'csrf' => $_SESSION['calendar_csrf']
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/locales-all.global.min.js"></script>
    <script src="assets/js/calendario.js"></script>
</body>
</html>
