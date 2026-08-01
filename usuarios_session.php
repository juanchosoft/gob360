<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

// Permisos
requirePermission('configuracion.sesiones.view');
$view = SessionData::hasPermission('configuracion.sesiones.view');
$create = false;
$edit = false;
$permits = false;

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  
  <link rel="stylesheet" href="assets/css/sesiones_usuarios_gob360_premium.css">
</head>

<body class="gob360-sessions-page">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-sessions-hero" aria-label="Auditoría de sesiones GOB360">
        <div class="g360-sessions-hero__grid">

          <aside class="g360-sessions-brand">
            <span class="g360-sessions-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-sessions-brand__logo"
            >

            <span class="g360-sessions-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-sessions-brand__status">
              <span></span>
              Auditoría activa
            </div>
          </aside>

          <div class="g360-sessions-hero__content">
            <div class="g360-sessions-hero__top">
              <div>
                <div class="g360-sessions-hero__eyebrow">
                  <i class="feather icon-shield"></i>
                  Configuración y seguridad
                </div>

                <h1 class="g360-sessions-hero__title">
                  Auditoría de Sesiones
                </h1>

                <p class="g360-sessions-hero__description">
                  Consulta la trazabilidad de los accesos al sistema, incluyendo
                  usuario, fecha, dirección IP y navegador utilizado durante cada
                  inicio de sesión.
                </p>
              </div>

              <div class="g360-sessions-hero__actions">
                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="window.location.reload()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar registros
                </button>

                <div class="g360-sessions-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-sessions-summary">
              <article>
                <span class="g360-sessions-summary__icon">
                  <i class="feather icon-log-in"></i>
                </span>

                <div>
                  <small>Tipo de registro</small>
                  <strong>Accesos</strong>
                  <p>Eventos de inicio de sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-sessions-summary__icon g360-sessions-summary__icon--users">
                  <i class="feather icon-users"></i>
                </span>

                <div>
                  <small>Identificación</small>
                  <strong>Usuarios</strong>
                  <p>ID, nickname y nombre completo</p>
                </div>
              </article>

              <article>
                <span class="g360-sessions-summary__icon g360-sessions-summary__icon--network">
                  <i class="feather icon-wifi"></i>
                </span>

                <div>
                  <small>Información técnica</small>
                  <strong>IP</strong>
                  <p>Origen de la conexión</p>
                </div>
              </article>

              <article>
                <span class="g360-sessions-summary__icon g360-sessions-summary__icon--browser">
                  <i class="feather icon-monitor"></i>
                </span>

                <div>
                  <small>Dispositivo</small>
                  <strong>Navegador</strong>
                  <p>Agente utilizado en el acceso</p>
                </div>
              </article>
            </div>

            <div class="g360-sessions-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-clock"></i>
                Fecha y hora
              </span>

              <span>
                <i class="feather icon-user-check"></i>
                Identidad de usuario
              </span>

              <span>
                <i class="feather icon-globe"></i>
                Dirección IP
              </span>

              <span>
                <i class="feather icon-monitor"></i>
                Navegador
              </span>

              <span>
                <i class="feather icon-lock"></i>
                Acceso autorizado
              </span>
            </div>
          </div>

        </div>
      </section>

      <div class="contenedor">
        <div class="contenido">
          <div class="card table-card-u g360-sessions-card">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-3">
              <div class="g360-card-heading">
                <span class="g360-card-heading__icon">
                  <i class="feather icon-activity"></i>
                </span>

                <div>
                  <span class="g360-card-heading__eyebrow">Trazabilidad de seguridad</span>
                  <h5 class="mb-0">Información de inicio de sesión</h5>
                  <p>
                    Consulta y filtra los accesos registrados en la plataforma.
                  </p>
                </div>
              </div>

              <div class="card-header-right ml-auto">
                <div class="btn-group card-option">
                  <button type="button" class="btn dropdown-toggle btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="feather icon-more-horizontal"></i>
                  </button>
                  <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                    <li class="dropdown-item full-card">
                      <a href="#!">
                        <span><i class="feather icon-maximize"></i> Maximizar</span>
                        <span style="display:none"><i class="feather icon-minimize"></i> Restaurar</span>
                      </a>
                    </li>
                    <li class="dropdown-item minimize-card">
                      <a href="#!">
                        <span><i class="feather icon-minus"></i> Colapsar</span>
                        <span style="display:none"><i class="feather icon-plus"></i> Expandir</span>
                      </a>
                    </li>
                    <li class="dropdown-item reload-card">
                      <a href="#!"><i class="feather icon-refresh-cw"></i> Recargar</a>
                    </li>
                    <li class="dropdown-item close-card">
                      <a href="#!"><i class="feather icon-trash"></i> Eliminar</a>
                    </li>
                  </ul>
                </div>
              </div>

            </div>

            <div class="card-body table-border-style">
              <div class="g360-sessions-tools">
                <div class="g360-sessions-search">
                  <span class="g360-sessions-search__icon">
                    <i class="feather icon-search"></i>
                  </span>

                  <div>
                    <label for="customSearch">Búsqueda rápida</label>
                    <input
                      type="text"
                      id="customSearch"
                      class="form-control"
                      placeholder="Buscar por usuario, nickname, IP o navegador..."
                    >
                  </div>
                </div>

                <div class="g360-sessions-tools__info">
                  <i class="feather icon-info"></i>

                  <span>
                    Los registros mostrados corresponden a eventos almacenados
                    por el sistema de autenticación.
                  </span>
                </div>
              </div>

              <div class="table-responsive tabla-informacion tabla-scroll g360-sessions-table">
                <table class="table table-hover mb-0" id="dynamictable" aria-label="Registro de inicios de sesión">
                  <thead>
                    <tr class="border-1">
                      <th>Usuario id</th>
                      <th>Fecha</th>
                      <th>Nickname</th>
                      <th>Usuario</th>
                      <th>Ip</th>
                      <th>Navegador</th>
                    </tr>
                  </thead>
                </table>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>
  <script type="text/javascript" src="admin/js/session-usuario.js"></script>
  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>
  <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
</body>
</html>
