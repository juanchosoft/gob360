<header class="navbar pcoded-header navbar-expand-lg navbar-light header-dark">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Tomorrow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <?php
    $img = !empty(SessionData::getFotoUsuario())
        ? "assets/img/admin/" . htmlspecialchars(SessionData::getFotoUsuario())
        : 'assets/img/santander.png';
    ?>

    <style>
        .drp-user img.rounded-circle {
            border: 2px solid #fff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            object-fit: cover;
        }

        /* =========================
           MOBILE/TABLET HEADER WOW
           Logo izq / Hamburguesa der
           ========================= */
        .header-mobile-row {
            width: 100%;
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
        }

        .brand-left {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            min-width: 0;
            max-width: 80%;
        }

        .brand-left img {
            height: 40px;
            width: auto;
            max-width: 40px;
            object-fit: contain;
        }

        .brand-left .brand-title {
            color: #fff;
            font-size: 1.05rem;
            font-weight: 800;
            font-family: 'Work Sans', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .burger-right {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .16);
            box-shadow: 0 12px 24px rgba(0, 0, 0, .22);
            text-decoration: none;
            position: relative;
        }

        .burger-right span,
        .burger-right span:before,
        .burger-right span:after {
            content: "";
            display: block;
            width: 18px;
            height: 2px;
            background: rgba(255, 255, 255, .95);
            border-radius: 999px;
            position: relative;
        }

        .burger-right span:before {
            position: absolute;
            top: -6px;
            left: 0;
        }

        .burger-right span:after {
            position: absolute;
            top: 6px;
            left: 0;
        }

        /* SOLO en mobile/tablet (<1200px) */
        @media (max-width: 1199.98px) {
            .header-mobile-row {
                display: flex;
            }

            /* Oculta bloque desktop del template para NO duplicar hamburguesas */
            .pcoded-header .m-header {
                display: none !important;
            }

            /* Oculta colapsable desktop para que en mobile no se renderice doble */
            .pcoded-header .collapse.navbar-collapse {
                display: none !important;
            }
        }

        /* PC >= 1200px: no se ve nada mobile */
        @media (min-width: 1200px) {
            .header-mobile-row {
                display: none !important;
            }
        }
        
    </style>

    <!-- =========================
         MOBILE/TABLET HEADER
         ========================= -->
    <div class="header-mobile-row">
        <!-- Logo izq -->
        <a href="#!" class="brand-left">
            <img src="assets/img/santander.png" alt="Logo">
            <span class="brand-title">Acción Unificada</span>

            <!-- Inputs ocultos (se conservan) -->
            <input type="hidden" id="municipioUsuario" name="municipioUsuario" value="<?= SessionData::getCodigoMunicipio() ?>">
            <input type="hidden" id="tipoUsuario" name="tipoUsuario" value="<?= SessionData::getUserType() ?>">
        </a>

        <!-- Hamburguesa der -> menu_mobile.php -->
        <a class="burger-right" href="menu_mobile.php" aria-label="Menú">
            <span></span>
        </a>
    </div>

    <!-- =========================
         DESKTOP HEADER (TU ORIGINAL)
         ========================= -->
    <div class="m-header d-none d-lg-flex align-items-center" style="margin-left: -30px;">
        <!-- IMPORTANTE: este queda solo para PC -->
        <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>

        <a href="#!" class="b-brand d-flex align-items-center flex-grow-1" style="text-decoration: none; margin-left: 60px;">
            <img src="assets/img/santander.png" alt="Logo"
                style="height: 40px; width: auto; margin-right: 8px; object-fit: contain; max-width: 40px;">

            <span style="white-space: nowrap; font-size: 1.3rem; font-weight: 600; color: white; font-family: 'IBM Plex Sans', sans-serif;">
                Acción Unificada
            </span>

            <input type="hidden" id="municipioUsuario" name="municipioUsuario" value="<?= SessionData::getCodigoMunicipio() ?>">
            <input type="hidden" id="tipoUsuario" name="tipoUsuario" value="<?= SessionData::getUserType() ?>">
        </a>
    </div>

    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"></li>
            <li class="nav-item">
                <div class="dropdown mega-menu">
                    <div class="dropdown-menu profile-notification ">
                        <div class="row no-gutters">
                            <div class="col"></div>
                            <div class="col"></div>
                            <div class="col"></div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li><div></div></li>
            <li>
                <div class="dropdown drp-user" id="drp-user-menu">
                    <a href="#" class="dropdown-toggle d-flex align-items-center" id="drp-user-toggle" aria-expanded="false">
                        <img src="<?= $img ?>" alt="User-Profile" class="rounded-circle" width="40" height="40">
                    </a>

                    <div class="dropdown-menu dropdown-menu-right profile-notification perfil" id="drp-user-content">
                        <div class="pro-head">
                            <img src="<?= $img ?>" class="img-radius" alt="User-Profile-Image">
                        </div>
                        <ul class="pro-body">
                            <li>
                                <a href="#" onclick="PROFILE.editData(<?php echo SessionData::getUserId(); ?>)"
                                    class="dropdown-item" data-toggle="modal" data-target="#exampleModalLive">
                                    <i class="feather icon-user"></i> Perfil
                                </a>
                            </li>
                            <li><a href="#" class="dropdown-item"><i class="feather icon-mail"></i>
                                    Rol: <?= htmlspecialchars(SessionData::getUserType()) ?></a></li>
                            <li><a href="logout.php" class="dropdown-item"><i class="feather icon-lock"></i>
                                    Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</header>

<script>
(function() {
    function initDrpUser() {
        var toggle = document.getElementById('drp-user-toggle');
        var menu   = document.getElementById('drp-user-content');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var isOpen = menu.classList.contains('show');
            // Cerrar cualquier otro dropdown abierto
            document.querySelectorAll('.dropdown-menu.show').forEach(function(el) {
                el.classList.remove('show');
            });
            if (!isOpen) {
                menu.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');
            } else {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Cerrar al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!document.getElementById('drp-user-menu').contains(e.target)) {
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Cerrar al hacer clic en un item del menú (excepto links externos)
        menu.querySelectorAll('.dropdown-item').forEach(function(item) {
            item.addEventListener('click', function() {
                menu.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
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

<!-- =========================
     MODAL PERFIL (TU ORIGINAL)
     ========================= -->
<div id="exampleModalLive" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLiveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLiveLabel">Perfil</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">

                        <div class="card-header">
                            <h5>Actualizar información de perfil</h5>
                        </div>

                        <div class="card-body">
                            <form id="formusuarios" role="form" autocomplete="false">
                                <input type="hidden" name="op" id="op" />
                                <input type="hidden" name="id" id="id" />

                                <span id="mensajes" class="text-danger mb-1"></span>
                                <br><br>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom03">Nombres Completos<span class="text-danger mb-1">*</span></label>
                                        <input type="text" class="form-control" id="nombre_perfil" name="nombre_perfil" placeholder="Ingrese nombres" value="" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom04">Apellidos<span class="text-danger mb-1">*</span></label>
                                        <input type="text" class="form-control" id="apellido_perfil" name="apellido_perfil" placeholder="Ingrese apellidos" value="" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom05">Foto</label>
                                        <iframe id='ifm' name='ifm' src="upload.php" width="200" height="60" scrolling="no" frameborder="0"></iframe>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom05">Usuario<span class="text-danger mb-1">*</span></label>
                                        <input type="email" class="form-control" id="nickname_perfil" name="nickname_perfil" placeholder="Ingrese un formato de usuario valido" value="" required>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="validationCustom05">Contraseña<span class="text-danger mb-1">*</span></label>
                                        <input type="password" class="form-control" id="hashpass_perfil" name="hashpass_perfil" placeholder="Ingrese una contraseña" value="" required>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="validationCustom05">Repita la Contraseña<span class="text-danger mb-1">*</span></label>
                                        <input type="password" autocomplete="new-password" class="form-control" id="hashpass1_perfil" name="hashpass1_perfil" placeholder="ngrese nuevamente contraseña" value="" required>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="modal-footer">
                            <button type="button" onclick="PROFILE.validateData();" class="btn btn-primary">Actualizar Datos</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php $nameCompleto = SessionData::getNombreUsuario(); ?>
<?php $fechaHoraActual = (new DateTime())->format('Y-m-d H:i:s'); ?>




