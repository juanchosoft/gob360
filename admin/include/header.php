<?php
$fotoUsuario = SessionData::getFotoUsuario();
$img = !empty($fotoUsuario)
    ? 'assets/img/admin/' . htmlspecialchars((string)$fotoUsuario, ENT_QUOTES, 'UTF-8')
    : 'assets/img/santander.png';

$nombreUsuario = trim((string)SessionData::getNombreUsuario());
if ($nombreUsuario === '') {
    $nombreUsuario = 'Usuario GOB360';
}

$rolUsuario = trim((string)SessionData::getUserType());
if ($rolUsuario === '') {
    $rolUsuario = 'Usuario';
}

$nombreUsuarioSeguro = htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8');
$rolUsuarioSeguro = htmlspecialchars($rolUsuario, ENT_QUOTES, 'UTF-8');
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700;800;900&family=Tomorrow:wght@500;600;700;800&family=Work+Sans:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet"
>
<link rel="stylesheet" href="assets/css/header_gob360_corregido.css">

<header class="navbar pcoded-header navbar-expand-lg navbar-light header-dark gob360-header">

    <!-- Valores de sesión conservados una sola vez para evitar IDs duplicados -->
    <input
        type="hidden"
        id="municipioUsuario"
        name="municipioUsuario"
        value="<?= htmlspecialchars((string)SessionData::getCodigoMunicipio(), ENT_QUOTES, 'UTF-8') ?>"
    >
    <input
        type="hidden"
        id="tipoUsuario"
        name="tipoUsuario"
        value="<?= $rolUsuarioSeguro ?>"
    >

    <!-- =====================================================
         HEADER MÓVIL Y TABLET
    ====================================================== -->
    <div class="g360-mobile-header">
        <a href="index.php" class="g360-mobile-brand" aria-label="Ir al inicio de GOB360">
            <img
                src="assets/img/gob360l.png"
                alt="Logo GOB360"
                class="g360-mobile-brand__logo"
            >

            <span class="g360-mobile-brand__copy">
                <small>Plataforma institucional</small>
                <strong>Gestión pública inteligente</strong>
            </span>
        </a>

        <div class="g360-mobile-actions">
            <a
                href="#"
                class="g360-mobile-user"
                id="g360-mobile-profile-trigger"
                onclick="PROFILE.editData(<?= (int)SessionData::getUserId() ?>)"
                data-toggle="modal"
                data-target="#exampleModalLive"
                aria-label="Abrir perfil de usuario"
            >
                <img
                    src="<?= $img ?>"
                    alt="Foto de <?= $nombreUsuarioSeguro ?>"
                    width="38"
                    height="38"
                >
            </a>

            <a
                class="g360-mobile-menu"
                href="menu_mobile.php"
                aria-label="Abrir menú principal"
            >
                <span></span>
            </a>
        </div>
    </div>

    <!-- =====================================================
         HEADER DE ESCRITORIO
         No usa navbar-collapse/navbar-nav para evitar conflictos
         con las reglas internas del template PCoded.
    ====================================================== -->
    <div class="g360-desktop-shell">

        <div class="g360-desktop-brand">
            <a
                class="mobile-menu g360-sidebar-toggle"
                id="mobile-collapse"
                href="#!"
                aria-label="Contraer o expandir menú lateral"
            >
                <span></span>
            </a>

            <a
                href="index.php"
                class="b-brand g360-brand-link"
                aria-label="Ir al inicio de GOB360"
            >
                <img
                    src="assets/img/gob360l.png"
                    alt="Logo GOB360"
                    class="g360-brand-link__logo"
                >

                <span class="g360-brand-link__copy">
                    <small>Plataforma institucional</small>
                    <strong>Gestión pública inteligente</strong>
                </span>
            </a>
        </div>

        <div class="g360-desktop-center">
            <div class="g360-header-context">
                <span class="g360-header-context__icon">
                    <i class="feather icon-grid"></i>
                </span>

                <span class="g360-header-context__copy">
                    <small>Centro de gestión territorial</small>
                    <strong>Gobierno 360°</strong>
                </span>
            </div>
        </div>

        <div class="g360-desktop-actions">
            <div class="g360-system-state">
                <span class="g360-system-state__dot"></span>

                <span class="g360-system-state__copy">
                    <small>Estado del sistema</small>
                    <strong>Operativo</strong>
                </span>
            </div>

            <div class="dropdown drp-user g360-user-menu" id="drp-user-menu">
                <a
                    href="#"
                    class="dropdown-toggle g360-user-trigger"
                    id="drp-user-toggle"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    <span class="g360-user-trigger__avatar">
                        <img
                            src="<?= $img ?>"
                            alt="Foto de <?= $nombreUsuarioSeguro ?>"
                            width="43"
                            height="43"
                        >
                        <span></span>
                    </span>

                    <span class="g360-user-trigger__copy">
                        <strong><?= $nombreUsuarioSeguro ?></strong>
                        <small><?= $rolUsuarioSeguro ?></small>
                    </span>

                    <i class="feather icon-chevron-down g360-user-trigger__chevron"></i>
                </a>

                <div
                    class="dropdown-menu dropdown-menu-right profile-notification perfil g360-profile-dropdown"
                    id="drp-user-content"
                >
                    <div class="g360-profile-dropdown__header">
                        <span class="g360-profile-dropdown__avatar">
                            <img
                                src="<?= $img ?>"
                                alt="Foto de <?= $nombreUsuarioSeguro ?>"
                            >
                            <span></span>
                        </span>

                        <div>
                            <small>Sesión activa</small>
                            <strong><?= $nombreUsuarioSeguro ?></strong>
                            <span><?= $rolUsuarioSeguro ?></span>
                        </div>
                    </div>

                    <div class="g360-profile-dropdown__status">
                        <i class="feather icon-shield"></i>
                        Acceso institucional protegido
                    </div>

                    <ul class="pro-body g360-profile-dropdown__menu">
                        <li>
                            <a
                                href="#"
                                onclick="PROFILE.editData(<?= (int)SessionData::getUserId() ?>)"
                                class="dropdown-item"
                                data-toggle="modal"
                                data-target="#exampleModalLive"
                            >
                                <span class="g360-dropdown-icon">
                                    <i class="feather icon-user"></i>
                                </span>

                                <span>
                                    <strong>Mi perfil</strong>
                                    <small>Actualizar información personal</small>
                                </span>
                            </a>
                        </li>

                        <li>
                            <div class="dropdown-item g360-role-item">
                                <span class="g360-dropdown-icon">
                                    <i class="feather icon-briefcase"></i>
                                </span>

                                <span>
                                    <strong>Rol institucional</strong>
                                    <small><?= $rolUsuarioSeguro ?></small>
                                </span>
                            </div>
                        </li>

                        <li class="g360-profile-dropdown__separator"></li>

                        <li>
                            <a href="logout.php" class="dropdown-item g360-logout-item">
                                <span class="g360-dropdown-icon">
                                    <i class="feather icon-log-out"></i>
                                </span>

                                <span>
                                    <strong>Cerrar sesión</strong>
                                    <small>Salir de forma segura</small>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</header>

<script>
(function () {
    'use strict';

    function closeUserMenu(toggle, menu) {
        menu.classList.remove('show');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('g360-profile-open');
    }

    function openUserMenu(toggle, menu) {
        document.querySelectorAll('.dropdown-menu.show').forEach(function (element) {
            if (element !== menu) {
                element.classList.remove('show');
            }
        });

        menu.classList.add('show');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('g360-profile-open');
    }

    function initDrpUser() {
        var container = document.getElementById('drp-user-menu');
        var toggle = document.getElementById('drp-user-toggle');
        var menu = document.getElementById('drp-user-content');

        if (!container || !toggle || !menu) {
            return;
        }

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (menu.classList.contains('show')) {
                closeUserMenu(toggle, menu);
                return;
            }

            openUserMenu(toggle, menu);
        });


        document.addEventListener('click', function (event) {
            if (!container.contains(event.target)) {
                closeUserMenu(toggle, menu);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeUserMenu(toggle, menu);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth < 1200 && menu.classList.contains('show')) {
                menu.classList.add('g360-profile-dropdown--mobile');
            } else {
                menu.classList.remove('g360-profile-dropdown--mobile');
            }
        });

        menu.querySelectorAll('a.dropdown-item').forEach(function (item) {
            item.addEventListener('click', function () {
                closeUserMenu(toggle, menu);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDrpUser);
    } else {
        initDrpUser();
    }
})();
</script>

<!-- =========================================================
     MODAL DE PERFIL
========================================================== -->
<div
    id="exampleModalLive"
    class="modal fade g360-profile-modal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="exampleModalLiveLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <div class="g360-profile-modal__heading">
                    <span class="g360-profile-modal__icon">
                        <i class="feather icon-user"></i>
                    </span>

                    <div>
                        <small>Configuración de cuenta</small>
                        <h5 class="modal-title" id="exampleModalLiveLabel">
                            Actualizar perfil
                        </h5>
                    </div>
                </div>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Cerrar"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="g360-profile-modal__intro">
                    <span class="g360-profile-modal__avatar">
                        <img
                            src="<?= $img ?>"
                            alt="Foto de <?= $nombreUsuarioSeguro ?>"
                        >
                    </span>

                    <div>
                        <strong><?= $nombreUsuarioSeguro ?></strong>
                        <span><?= $rolUsuarioSeguro ?></span>
                        <p>
                            Actualiza tu información personal, usuario, fotografía
                            o contraseña institucional.
                        </p>
                    </div>
                </div>

                <form id="formusuarios" role="form" autocomplete="off">
                    <input type="hidden" name="op" id="op">
                    <input type="hidden" name="id" id="id">

                    <div id="mensajes" class="g360-profile-modal__messages"></div>

                    <div class="g360-profile-form-section">
                        <div class="g360-profile-form-section__heading">
                            <span>
                                <i class="feather icon-id-card"></i>
                            </span>

                            <div>
                                <small>Información personal</small>
                                <h6>Datos del usuario</h6>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="nombre_perfil">
                                    Nombres completos
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombre_perfil"
                                    name="nombre_perfil"
                                    placeholder="Ingrese nombres"
                                    required
                                >
                            </div>

                            <div class="form-group col-md-4">
                                <label for="apellido_perfil">
                                    Apellidos
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="apellido_perfil"
                                    name="apellido_perfil"
                                    placeholder="Ingrese apellidos"
                                    required
                                >
                            </div>

                            <div class="form-group col-md-4">
                                <label for="ifm">Fotografía</label>

                                <div class="g360-upload-frame">
                                    <iframe
                                        id="ifm"
                                        name="ifm"
                                        src="upload.php"
                                        title="Cargar fotografía de perfil"
                                        scrolling="no"
                                        frameborder="0"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="g360-profile-form-section">
                        <div class="g360-profile-form-section__heading">
                            <span>
                                <i class="feather icon-shield"></i>
                            </span>

                            <div>
                                <small>Seguridad</small>
                                <h6>Credenciales de acceso</h6>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="nickname_perfil">
                                    Usuario
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="nickname_perfil"
                                    name="nickname_perfil"
                                    placeholder="Ingrese un usuario válido"
                                    required
                                >
                            </div>

                            <div class="form-group col-md-4">
                                <label for="hashpass_perfil">
                                    Contraseña
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="password"
                                    autocomplete="new-password"
                                    class="form-control"
                                    id="hashpass_perfil"
                                    name="hashpass_perfil"
                                    placeholder="Ingrese una contraseña"
                                    required
                                >
                            </div>

                            <div class="form-group col-md-4">
                                <label for="hashpass1_perfil">
                                    Confirmar contraseña
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="password"
                                    autocomplete="new-password"
                                    class="form-control"
                                    id="hashpass1_perfil"
                                    name="hashpass1_perfil"
                                    placeholder="Repita la contraseña"
                                    required
                                >
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <div class="g360-profile-modal__footer-message">
                    <i class="feather icon-lock"></i>
                    Tus cambios se guardarán de forma segura.
                </div>

                <div class="g360-profile-modal__footer-actions">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        onclick="PROFILE.validateData();"
                        class="btn btn-primary"
                    >
                        <i class="feather icon-save"></i>
                        Actualizar datos
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<?php $nameCompleto = SessionData::getNombreUsuario(); ?>
<?php $fechaHoraActual = (new DateTime())->format('Y-m-d H:i:s'); ?>
