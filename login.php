<?php
session_start();
$mensaje = '';

include './admin/classes/SessionData.php';
include './admin/classes/DbConection.php';
include './admin/classes/Util.php';
include './admin/classes/Usuario.php';

$departamentoPrincipal = Util::getIdentificadorDepartamentoPrincipal();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$rqst = $_REQUEST;
$op = $rqst['op'] ?? '';

if ($op === 'pms_usrlogin') {
    $nickname = trim($rqst['nickname'] ?? '');
    $hashpass = $rqst['hashpass'] ?? '';

    if (!empty($nickname) && !empty($hashpass)) {
        /* Se conserva MD5 para no romper la autenticación existente. */
        $hashpass = md5($hashpass);
        $arr = ['nickname' => $nickname, 'hashpass' => $hashpass];
        $res = Usuario::login($arr);
        $isvalid = $res['output']['valid'];

        if ($isvalid) {
            $_SESSION['session_user'] = $res['output']['response'][0];
            $_SESSION['session_user']['permisos'] = $res['output']['permisos'];

            if (isset($res['output']['permission_keys'])) {
                $_SESSION['session_user']['permission_keys'] = $res['output']['permission_keys'];
            }

            $route = $res['output']['route'];
            $userType = SessionData::getUserType();

            $isSecretario = (
                $userType === Util::Secretario_Despacho() ||
                $userType === Util::Auxiliar() ||
                $userType == Util::Auxiliar_secret_gob()
            );

            $isAlcalde = (
                $userType === Util::Alcalde() ||
                $userType === Util::Auxiliar_Alcalde()
            );

            if ($isAlcalde) {
                header('Location: dahsboard_alcaldias.php');
            } elseif ($isSecretario) {
                header('Location: ' . ($route ?: 'dash_secretarias.php'));
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }

        $mensaje = $res['output']['response']['content'] ?? 'Usuario o contraseña incorrectos.';
    } else {
        $mensaje = 'Todos los campos son obligatorios.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#050810">
    <meta name="color-scheme" content="dark">

    <title>GOB360 | Inicio de sesión</title>

    <!-- Dependencias existentes del proyecto -->
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
    <link href="../assets/vendor/fonts/circular-std/style.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/libs/css/style.css">
    <link rel="stylesheet" href="../assets/libs/css/stylenew.css">
    <link rel="stylesheet" href="../assets/vendor/fonts/fontawesome/css/fontawesome-all.css">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/custom-styles.css">

    <!-- Cargar siempre al final para que prevalezca el nuevo diseño -->
    <link rel="stylesheet" href="assets/css/gob360-ui.css?v=1.0.0">
</head>

<body class="gob360-auth">
    <div class="gob360-background" aria-hidden="true">
        <span class="gob360-orb gob360-orb--one"></span>
        <span class="gob360-orb gob360-orb--two"></span>
    </div>

    <main class="gob360-auth-page">
        <section class="gob360-auth-shell" aria-label="Acceso a GOB360">

            <!-- Panel institucional y visual -->
            <aside class="gob360-hero">
                <div class="gob360-brand">
                      <div>
                        <p class="gob360-brand__eyebrow">Plataforma institucional</p>
                         <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="gob360-brand__logo"
                        >
                        <p class="gob360-brand__caption">Gestión pública inteligente y territorial</p>
                    </div>
                </div>

                <div class="gob360-hero__content">
                    <div class="gob360-kicker">
                        <span class="gob360-kicker__dot"></span>
                        Sistema disponible y protegido
                    </div>

                    <h2 class="gob360-hero__title">
                        Información estratégica en una
                        <span class="gob360-gradient-text">visión de 360°.</span>
                    </h2>

                    <p class="gob360-hero__description">
                        Centraliza indicadores, compromisos, proyectos y seguimiento territorial
                        en una experiencia visual diseñada para facilitar decisiones oportunas.
                    </p>
                </div>

                <div class="gob360-preview" aria-hidden="true">
                    <div class="gob360-preview__header">
                        <p class="gob360-preview__title">Resumen de gestión territorial</p>
                        <span class="gob360-preview__period">Actualización continua</span>
                    </div>

                    <div class="gob360-preview__grid">
                        <div class="gob360-chart">
                            <span class="gob360-chart__bar" style="--bar-height: 42%;"></span>
                            <span class="gob360-chart__bar" style="--bar-height: 65%;"></span>
                            <span class="gob360-chart__bar" style="--bar-height: 54%;"></span>
                            <span class="gob360-chart__bar" style="--bar-height: 82%;"></span>
                            <span class="gob360-chart__bar" style="--bar-height: 68%;"></span>
                            <span class="gob360-chart__bar" style="--bar-height: 92%;"></span>
                            <span class="gob360-chart__bar" style="--bar-height: 76%;"></span>
                        </div>

                        <div class="gob360-metrics">
                            <article class="gob360-metric">
                                <p class="gob360-metric__label">Cobertura</p>
                                <p class="gob360-metric__value">Territorial</p>
                                <span class="gob360-metric__trend">Seguimiento integrado</span>
                            </article>

                            <article class="gob360-metric">
                                <p class="gob360-metric__label">Estado</p>
                                <p class="gob360-metric__value">En línea</p>
                                <span class="gob360-metric__trend">Operación segura</span>
                            </article>
                        </div>
                    </div>
                </div>

                <div class="gob360-hero__footer">
                    <span>© <?= date('Y'); ?> GOB360</span>
                    <span>Desarrollado por Spidersoftware SAS</span>
                </div>
            </aside>

            <!-- Panel de inicio de sesión -->
            <div class="gob360-login">
                <div class="gob360-login__inner">
                    <div class="gob360-login__top">
                        <div class="gob360-status">
                            <span class="gob360-status__dot"></span>
                            Acceso institucional
                        </div>
                        <span class="gob360-login__help">Soporte seguro</span>
                    </div>

                    <h2 class="gob360-login__title">Bienvenido de nuevo</h2>
                    <p class="gob360-login__subtitle">
                        Ingresa tus credenciales para acceder al panel de gestión.
                    </p>

                    <form
                        class="gob360-form"
                        method="POST"
                        action="login.php"
                        autocomplete="on"
                        onsubmit="guardarPreferenciaUsuario();"
                    >
                        <input type="hidden" name="op" value="pms_usrlogin">

                        <div class="gob360-field">
                            <label class="gob360-label" for="nickname">Usuario</label>
                            <div class="gob360-input-wrap">
                                <svg class="gob360-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M20 21a8 8 0 1 0-16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                <input
                                    type="text"
                                    class="gob360-input"
                                    id="nickname"
                                    name="nickname"
                                    placeholder="Escribe tu usuario"
                                    autocomplete="username"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    required
                                >
                            </div>
                        </div>

                        <div class="gob360-field">
                            <label class="gob360-label" for="hashpass">Contraseña</label>
                            <div class="gob360-input-wrap">
                                <svg class="gob360-input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M7.5 10V7.8A4.5 4.5 0 0 1 12 3.3a4.5 4.5 0 0 1 4.5 4.5V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.5 10h11a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-11a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                <input
                                    type="password"
                                    class="gob360-input"
                                    id="hashpass"
                                    name="hashpass"
                                    placeholder="Escribe tu contraseña"
                                    autocomplete="current-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="gob360-password-toggle"
                                    id="btnTogglePass"
                                    aria-label="Mostrar contraseña"
                                    aria-pressed="false"
                                >
                                    <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                    <svg id="eyeClosed" viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden>
                                        <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M5.2 7.2C3 9 2 12 2 12s3.5 7 10 7c1.9 0 3.6-.5 5-1.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9.9 5.1C10.6 5 11.3 5 12 5c6.5 0 10 7 10 7s-1.3 2.7-3.8 4.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="gob360-form-options">
                            <label class="gob360-check" for="rememberUser">
                                <input type="checkbox" id="rememberUser" checked>
                                Recordar mi usuario
                            </label>

                            <a class="gob360-link" href="restablecer-contrasena.html">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>

                        <button type="submit" class="gob360-submit">
                            <span>Ingresar a GOB360</span>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div class="gob360-security-note">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 3 5 6v5c0 4.7 2.9 8.2 7 10 4.1-1.8 7-5.3 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="m9.5 12 1.7 1.7 3.5-3.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>
                                Tus credenciales son de uso personal. El sistema registra accesos para proteger la información institucional.
                            </span>
                        </div>
                    </form>

                    <div class="gob360-login__footer">
                        Plataforma optimizada para computador, tablet y celular.
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (!empty($mensaje)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'No fue posible ingresar',
                    text: <?= json_encode($mensaje, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
                    confirmButtonText: 'Intentar nuevamente',
                    customClass: {
                        popup: 'gob360-alert'
                    }
                });
            });
        </script>
    <?php endif; ?>

    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.getElementById('btnTogglePass');
            const passwordInput = document.getElementById('hashpass');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');
            const userInput = document.getElementById('nickname');
            const rememberUser = document.getElementById('rememberUser');

            if (toggleButton && passwordInput) {
                toggleButton.addEventListener('click', function () {
                    const showingPassword = passwordInput.type === 'text';
                    passwordInput.type = showingPassword ? 'password' : 'text';
                    toggleButton.setAttribute('aria-pressed', String(!showingPassword));
                    toggleButton.setAttribute(
                        'aria-label',
                        showingPassword ? 'Mostrar contraseña' : 'Ocultar contraseña'
                    );

                    if (eyeOpen && eyeClosed) {
                        eyeOpen.hidden = !showingPassword;
                        eyeClosed.hidden = showingPassword;
                    }
                });
            }

            const savedUser = localStorage.getItem('gob360_remember_user');
            if (savedUser && userInput) {
                userInput.value = savedUser;
                if (rememberUser) rememberUser.checked = true;
                passwordInput?.focus();
            } else {
                userInput?.focus();
            }
        });

        function guardarPreferenciaUsuario() {
            const rememberUser = document.getElementById('rememberUser');
            const userInput = document.getElementById('nickname');

            if (!rememberUser || !userInput) return;

            if (rememberUser.checked) {
                localStorage.setItem('gob360_remember_user', userInput.value.trim());
            } else {
                localStorage.removeItem('gob360_remember_user');
            }
        }
    </script>
</body>
</html>
