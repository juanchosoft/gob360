<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$userType = SessionData::getUserType();
$isAlcaldeOAuxiliar = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
$isAdmin = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador() || $userType == Util::Gobernador());
$isSecretario = ($userType === Util::Secretario_Despacho() || $userType === Util::Secretaria_Despacho_Gobernacion() || $userType === Util::Auxiliar() || $userType == Util::Auxiliar_secret_gob());
$isAlcalde = ($userType === Util::Alcalde() || $userType === Util::Auxiliar_Alcalde());
$municipioUsuarioLogueado = SessionData::getCodigoMunicipio();

// Informacion de la secretaria del usuario logueado
$secretariaId = SessionData::getSecretaria();
if ($secretariaId == 0) {
  $secretariaId = Util::getSecretariaPrincipal();
}

// Fallback seguro (en tu navbar aparece pero no siempre está declarado)
$isGestorSocial = isset($isGestorSocial) ? (bool)$isGestorSocial : false;

// Imagen usuario
$img = !empty(SessionData::getFotoUsuario())
  ? "assets/img/admin/" . htmlspecialchars(SessionData::getFotoUsuario())
  : 'assets/img/santander.png';

// Dashboard según rol (igual que navbar)
$dashboardUrl = 'dashboard.php';
if ($isAlcalde) $dashboardUrl = 'dahsboard_alcaldias.php';
if ($isSecretario) $dashboardUrl = 'dash_secretarias.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Menú</title>

  <style>
    :root{
      --nav-blue:#20427F;
      --nav-blue-2:#132b52;
      --nav-blue-3:#2e58a8;

      --bg:#0b1220;
      --card: rgba(255,255,255,.08);
      --card2: rgba(255,255,255,.06);
      --line: rgba(255,255,255,.12);
      --text: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.65);

      --radius-xl: 18px;
      --radius-lg: 14px;
      --radius-md: 12px;

      --shadow: 0 18px 50px rgba(0,0,0,.35);
      --shadow2: 0 12px 26px rgba(0,0,0,.25);
    }

    body{
      background:
        radial-gradient(900px 420px at 15% 10%, rgba(46,88,168,.30), transparent 60%),
        radial-gradient(900px 420px at 85% 20%, rgba(110,84,255,.18), transparent 55%),
        linear-gradient(135deg, #060a12, #0b1220 42%, #0a162e);
      min-height: 100vh;
      color: var(--text);
      overflow-x: hidden;
    }

    /* Solo aplica a móvil/tablet */
    @media (min-width: 1221px){
      body{ background:#fff; }
      .mobile-wrap{ display:none !important; }
    }

    .mobile-wrap{
      padding: 14px 12px 18px;
      max-width: 980px;
      margin: 0 auto;
    }

    /* Topbar */
    .topbar{
      position: sticky;
      top: 0;
      z-index: 999;
      padding: 10px 0 12px;
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      background: linear-gradient(180deg, rgba(6,10,18,.75), rgba(6,10,18,.35));
      border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .topbar-inner{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
    }

    .btn-pill{
      border: 1px solid rgba(255,255,255,.14);
      border-radius: 999px;
      padding: 9px 12px;
      color: var(--text);
      background: rgba(255,255,255,.06);
      box-shadow: 0 12px 26px rgba(0,0,0,.22);
      display:inline-flex;
      align-items:center;
      gap:8px;
      font-weight: 900;
      letter-spacing: .2px;
      text-decoration:none !important;
    }
    .btn-pill:active{ transform: translateY(1px); }

    .brand{
      display:flex;
      align-items:center;
      gap:10px;
      min-width: 0;
    }
    .brand-badge{
      width: 36px;
      height: 36px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
      box-shadow: 0 14px 40px rgba(32,66,127,.25);
      display:flex;
      align-items:center;
      justify-content:center;
      border: 1px solid rgba(255,255,255,.10);
      flex: 0 0 auto;
    }
    .brand-title{
      font-weight: 1000;
      letter-spacing: .2px;
      line-height: 1.1;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 52vw;
    }
    .brand-sub{
      color: var(--muted);
      font-weight: 800;
      font-size: .80rem;
      margin-top: 2px;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 52vw;
    }

    /* Perfil */
    .profile-card{
      margin-top: 14px;
      border-radius: 22px;
      border: 1px solid rgba(255,255,255,.12);
      background: linear-gradient(135deg, rgba(255,255,255,.10), rgba(255,255,255,.05));
      box-shadow: var(--shadow);
      overflow: hidden;
      position: relative;
    }
    .profile-card:before{
      content:"";
      position:absolute;
      inset:-2px;
      background:
        radial-gradient(500px 220px at 20% 10%, rgba(46,88,168,.40), transparent 70%),
        radial-gradient(500px 220px at 80% 25%, rgba(110,84,255,.22), transparent 65%);
      opacity: .65;
      pointer-events:none;
    }

    .profile-inner{
      position: relative;
      padding: 14px 14px 12px;
      display:flex;
      align-items:center;
      gap:12px;
    }
    .avatar{
      width: 52px; height: 52px;
      border-radius: 18px;
      overflow:hidden;
      border: 1px solid rgba(255,255,255,.18);
      box-shadow: 0 12px 26px rgba(0,0,0,.25);
      flex: 0 0 auto;
    }
    .avatar img{ width:100%; height:100%; object-fit: cover; }

    .who{ min-width: 0; flex: 1; }
    .who .name{
      font-weight: 1000;
      letter-spacing: .2px;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
    }
    .who .role{
      color: rgba(255,255,255,.75);
      font-weight: 900;
      font-size: .82rem;
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
    }

    .quick-actions{
      position: relative;
      padding: 10px 14px 14px;
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:10px;
    }
    .qbtn{
      border-radius: 16px;
      border: 1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.06);
      color: var(--text);
      text-decoration:none !important;
      padding: 10px 12px;
      font-weight: 1000;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      box-shadow: var(--shadow2);
    }
    .qbtn.primary{
      background: linear-gradient(135deg, var(--nav-blue), var(--nav-blue-2));
      border: 1px solid rgba(255,255,255,.10);
    }
    .qbtn.danger{
      background: linear-gradient(135deg, rgba(220,38,38,.92), rgba(153,27,27,.92));
      border: 1px solid rgba(255,255,255,.10);
    }

    /* Buscador */
    .search-wrap{
      margin-top: 14px;
      border-radius: 18px;
      border: 1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.06);
      box-shadow: var(--shadow2);
      padding: 10px 12px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .search-wrap i{ color: rgba(255,255,255,.78); }
    .search-input{
      width:100%;
      background: transparent;
      border: none;
      outline: none;
      color: var(--text);
      font-weight: 900;
    }
    .search-input::placeholder{ color: rgba(255,255,255,.55); font-weight: 800; }

    /* Secciones */
    .section{
      margin-top: 14px;
      border-radius: 22px;
      border: 1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.06);
      box-shadow: var(--shadow2);
      overflow:hidden;
    }
    .section-h{
      padding: 12px 14px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      cursor:pointer;
      user-select:none;
      background: linear-gradient(135deg, rgba(32,66,127,.18), rgba(19,43,82,.10));
      border-bottom: 1px solid rgba(255,255,255,.10);
    }
    .section-h .title{
      font-weight: 1000;
      letter-spacing: .25px;
      font-size: .92rem;
      display:flex;
      align-items:center;
      gap:10px;
      margin:0;
    }
    .section-body{ padding: 10px 10px 12px; }

    .link-item{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      padding: 11px 12px;
      border-radius: 16px;
      border: 1px solid rgba(255,255,255,.10);
      background: rgba(255,255,255,.05);
      text-decoration:none !important;
      color: var(--text) !important;
      margin-bottom: 10px;
      font-weight: 950;
    }
    .link-item small{
      color: rgba(255,255,255,.68);
      font-weight: 850;
      display:block;
      margin-top: 2px;
    }
    .link-left{
      display:flex;
      align-items:center;
      gap:10px;
      min-width:0;
    }
    .link-left .txt{ min-width:0; }
    .link-left .txt div{
      white-space: nowrap;
      overflow:hidden;
      text-overflow: ellipsis;
      max-width: 64vw;
    }
    .chev{ opacity:.8; }
    .hidden{ display:none !important; }

    @media (min-width: 768px) and (max-width: 1220px){
      .mobile-wrap{ padding: 18px 16px 20px; }
      .quick-actions{ grid-template-columns: 1fr 1fr 1fr; }
      .brand-title{ max-width: 58vw; }
      .brand-sub{ max-width: 58vw; }
      .link-left .txt div{ max-width: 72vw; }
    }
    /* =====================================================
   MOBILE MENU – ULTRA COMPACT TOP CARD (WOW)
   Objetivo: que NO se coma el menú
===================================================== */

/* Card superior: reduce altura y limita crecimiento */
.mobile-profile-card{
  padding: 10px 12px !important;
  border-radius: 18px !important;
  min-height: 120px !important;
  max-height: 160px !important;     /* CLAVE: evita bloque gigante */
  overflow: hidden !important;      /* recorta lo sobrante */
}

/* Header dentro del card (avatar + nombre + rol) */
.mobile-profile-card .profile-header{
  display: flex !important;
  align-items: center !important;
  gap: 10px !important;
  margin-bottom: 10px !important;
}

/* Avatar mini */
.mobile-profile-card .profile-avatar,
.mobile-profile-card .profile-img img,
.mobile-profile-card img.rounded-circle{
  width: 38px !important;
  height: 38px !important;
  border-radius: 999px !important;
}

/* Nombre / rol compactos */
.mobile-profile-card .profile-name,
.mobile-profile-card h6{
  font-size: 13px !important;
  line-height: 1.05 !important;
  margin: 0 !important;
  font-weight: 900 !important;
}
.mobile-profile-card .profile-role,
.mobile-profile-card span{
  font-size: 10px !important;
  line-height: 1.05 !important;
  margin: 2px 0 0 !important;
  opacity: .78 !important;
}

/* Acciones: en una sola fila (2x2 pero súper compactas) */
.mobile-profile-actions{
  display: grid !important;
  grid-template-columns: repeat(2, 1fr) !important;
  gap: 7px !important;
  margin-top: 6px !important;
}

/* Botones ultra compactos */
.mobile-profile-actions .btn{
  padding: 7px 8px !important;
  font-size: 11px !important;
  border-radius: 12px !important;
  min-height: 34px !important;
  line-height: 1 !important;
}

/* Iconos en botones */
.mobile-profile-actions .btn i{
  font-size: 13px !important;
  margin-right: 6px !important;
}

/* Botón salir: no tan alto */
.mobile-profile-actions .btn-danger{
  padding: 7px 8px !important;
}

/* El card NO debe tener espacio muerto */
.mobile-profile-wrapper{
  margin-bottom: 10px !important;
}

/* Buscador más pequeño */
.mobile-search{
  margin-top: 10px !important;
}
.mobile-search input{
  height: 38px !important;
  font-size: 12px !important;
  border-radius: 14px !important;
  padding: 10px 12px !important;
}

/* Si tienes un bloque vacío dentro del card (como el panel grande),
   lo apagamos para que no “infle” */
.mobile-profile-card .profile-big-panel,
.mobile-profile-card .profile-empty,
.mobile-profile-card .profile-panel,
.mobile-profile-card .large-panel{
  display: none !important;
}

/* Extra: que el menú arranque más arriba (más visible) */
.mobile-accordion,
.mobile-menu-sections{
  margin-top: 10px !important;
}
/* =========================================
   MENU MOBILE – PROFILE CARD ULTRA COMPACT
   PÉGALO AL FINAL DEL CSS EN menu_mobile.php
========================================= */

/* Ajusta estos selectores si tu card tiene otro id/clase:
   - .profile-card / .user-card / .card-profile
   Dejo varios para que agarre sí o sí.
*/
@media (max-width: 768px){

  /* 1) Reduce la tarjeta completa */
  .profile-card,
  .user-card,
  .card-profile,
  .mobile-profile-card,
  .card.profile-card {
    padding: 12px 12px !important;
    border-radius: 18px !important;
    min-height: unset !important;
    height: auto !important;
  }

  /* 2) Quita el “espacio muerto” grande (el bloque vacío) */
  .profile-card .profile-bg,
  .user-card .profile-bg,
  .card-profile .profile-bg,
  .profile-card .profile-fill,
  .user-card .profile-fill,
  .card-profile .profile-fill,
  .profile-card .profile-empty,
  .user-card .profile-empty,
  .card-profile .profile-empty,
  .profile-card .big-blank,
  .user-card .big-blank,
  .card-profile .big-blank {
    display: none !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* 3) Encabezado del perfil: avatar + nombre en compacto */
  .profile-card .profile-head,
  .user-card .profile-head,
  .card-profile .profile-head,
  .profile-card .user-head,
  .user-card .user-head,
  .card-profile .user-head {
    gap: 10px !important;
    margin-bottom: 10px !important;
  }

  /* Avatar más pequeño */
  .profile-card img,
  .user-card img,
  .card-profile img {
    width: 44px !important;
    height: 44px !important;
    border-radius: 999px !important;
  }

  /* Nombre y rol más pequeños */
  .profile-card h6,
  .user-card h6,
  .card-profile h6 {
    font-size: 14px !important;
    line-height: 1.15 !important;
    margin: 0 !important;
  }

  .profile-card .role,
  .user-card .role,
  .card-profile .role,
  .profile-card small,
  .user-card small,
  .card-profile small {
    font-size: 12px !important;
    opacity: .85 !important;
  }

  /* 4) Botones en modo mini */
  .profile-card .btn,
  .user-card .btn,
  .card-profile .btn,
  .profile-card a.btn,
  .user-card a.btn,
  .card-profile a.btn {
    padding: 8px 10px !important;
    font-size: 12px !important;
    border-radius: 14px !important;
    min-height: 36px !important;
  }

  /* Iconos dentro del botón */
  .profile-card .btn i,
  .user-card .btn i,
  .card-profile .btn i {
    font-size: 14px !important;
    margin-right: 6px !important;
  }

  /* 5) Si tienes grid de botones 2x2, reduce gaps */
  .profile-card .profile-actions,
  .user-card .profile-actions,
  .card-profile .profile-actions,
  .profile-card .action-grid,
  .user-card .action-grid,
  .card-profile .action-grid {
    gap: 8px !important;
    margin-top: 8px !important;
  }

  /* 6) Evita que la card empuje todo hacia abajo */
  .profile-card,
  .user-card,
  .card-profile {
    margin-bottom: 10px !important;
  }
}


  </style>
</head>

<body>
  <div class="mobile-wrap">

    <div class="topbar">
      <div class="topbar-inner">
        <a class="btn-pill" href="javascript:history.back()">
          <i class="feather icon-arrow-left"></i> Atrás
        </a>

        <div class="brand">
          <div class="brand-badge">
            <i class="feather icon-menu" style="color:#fff;"></i>
          </div>
          <div style="min-width:0;">
            <div class="brand-title">Acción Unificada</div>
            <div class="brand-sub">Menú móvil</div>
          </div>
        </div>

        <a class="btn-pill" href="<?php echo htmlspecialchars($dashboardUrl); ?>">
          <i class="feather icon-home"></i>
        </a>
      </div>
    </div>

    <div class="profile-card">
      <div class="profile-inner">
        <div class="avatar"><img src="<?php echo htmlspecialchars($img); ?>" alt="user"></div>
        <div class="who">
          <div class="name"><?php echo htmlspecialchars(SessionData::getNombreUsuario()); ?></div>
          <div class="role"><?php echo htmlspecialchars(SessionData::getUserType()); ?></div>
        </div>
      </div>
      <div class="quick-actions">
        <a class="qbtn primary" href="<?php echo htmlspecialchars($dashboardUrl); ?>"><i class="feather icon-activity"></i> Dashboard</a>
        <a class="qbtn" href="index.php"><i class="feather icon-globe"></i> Inicio</a>
        <a class="qbtn" href="perfil.php"><i class="feather icon-user"></i> Perfil</a>
        <a class="qbtn danger" href="logout.php"><i class="feather icon-log-out"></i> Salir</a>
      </div>
    </div>

    <div class="search-wrap">
      <i class="feather icon-search"></i>
      <input id="menuSearch" class="search-input" type="text" placeholder="Buscar… (ej: policía, compromisos, proyectos, secretarías)">
    </div>

    <!-- ========= NAVEGACIÓN ========= -->
    <div class="section" data-section>
      <div class="section-h" data-toggle-section>
        <h6 class="title"><i class="feather icon-compass"></i> Navegación</h6>
        <i class="feather icon-chevron-down"></i>
      </div>
      <div class="section-body" data-body>
        <a class="link-item" href="<?php echo htmlspecialchars($dashboardUrl); ?>" data-search="dashboard inicio home">
          <div class="link-left"><i class="feather icon-home"></i><div class="txt"><div>Dashboard</div><small>Panel principal</small></div></div>
          <i class="feather icon-chevron-right chev"></i>
        </a>
      </div>
    </div>

    <!-- ========= CONFIG GENERAL (ADMIN) ========= -->
    <?php if ($isAdmin): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-settings"></i> Configuración General</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="configuracion.php" data-search="configuracion general"><div class="link-left"><i class="feather icon-sliders"></i><div class="txt"><div>Configuración</div><small>Parámetros</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="usuarios.php" data-search="usuarios"><div class="link-left"><i class="feather icon-users"></i><div class="txt"><div>Usuarios</div><small>Gestión</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="linea.php" data-search="lineas gestion social"><div class="link-left"><i class="feather icon-list"></i><div class="txt"><div>Lineas Gestión social</div><small>Catálogo</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="estrategia.php" data-search="estrategias"><div class="link-left"><i class="feather icon-target"></i><div class="txt"><div>Estrategias</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="acciong.php" data-search="acciones gestion social"><div class="link-left"><i class="feather icon-check-circle"></i><div class="txt"><div>Acciones Gestión social</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="usuarios_session.php" data-search="sesion usuarios"><div class="link-left"><i class="feather icon-clock"></i><div class="txt"><div>Sesión Usuarios</div><small>Control</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="secretarias.php" data-search="secretarias entidades"><div class="link-left"><i class="feather icon-briefcase"></i><div class="txt"><div>Secretarias y Entidades</div><small>Catálogo</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="ministerios.php" data-search="ministerios entidades"><div class="link-left"><i class="feather icon-layers"></i><div class="txt"><div>Ministerios y Entidades</div><small>Catálogo</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="conf_puntajes.php" data-search="config puntajes"><div class="link-left"><i class="feather icon-award"></i><div class="txt"><div>Config puntajes</div><small>Scoring</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="conf_puntajes_secretarias.php" data-search="puntajes secretaria"><div class="link-left"><i class="feather icon-hash"></i><div class="txt"><div>Config puntajes secretaría</div><small>Scoring por dependencia</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= REGISTRO VISITAS ALCALDE (ADMIN O ALCALDE) ========= -->
    <?php if ($isAdmin || $isAlcalde): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-map"></i> Registro Visitas Alcalde</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="mapa_visitas_alcalde.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" data-search="mapa visitas alcalde">
            <div class="link-left"><i class="feather icon-map-pin"></i><div class="txt"><div>Mapa Visitas</div><small>Mapa visita Alcalde</small></div></div>
            <i class="feather icon-chevron-right chev"></i>
          </a>
          <a class="link-item" href="informacion_visitas_alcalde.php" data-search="ingreso visitas alcalde"><div class="link-left"><i class="feather icon-edit"></i><div class="txt"><div>Ingreso Visitas</div><small>Registro</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="cuadro-control-visitas_alcalde.php" data-search="cuadro control visitas alcalde"><div class="link-left"><i class="feather icon-clipboard"></i><div class="txt"><div>Cuadro Control Visitas</div><small>Seguimiento</small></div></div><i class="feather icon-chevron-right chev"></i></a>

          <a class="link-item" href="cuadro-control-compromisos_alcalde.php" data-search="control compromisos alcalde"><div class="link-left"><i class="feather icon-check-square"></i><div class="txt"><div>Control compromisos</div><small>Gestión</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="cuadro-control-compromisos-cumplidos_alcalde.php" data-search="compromisos cumplidos alcalde"><div class="link-left"><i class="feather icon-shield"></i><div class="txt"><div>Compromisos Cumplidos</div><small>Histórico</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="cuadro-control-compromisos-aprobacion_alcalde.php" data-search="aprobacion compromisos alcalde"><div class="link-left"><i class="feather icon-thumbs-up"></i><div class="txt"><div>Aprobación compromisos</div><small>Validación</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="gestion-cumplimiento_alcalde.php" data-search="gestion cumplimiento alcalde"><div class="link-left"><i class="feather icon-trending-up"></i><div class="txt"><div>Gestión cumplimiento</div><small>KPIs</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="visor_gestion_compromisos_alcalde.php" data-search="visor gestion compromisos alcalde"><div class="link-left"><i class="feather icon-eye"></i><div class="txt"><div>Visor Gestión compromisos</div><small>Vista analítica</small></div></div><i class="feather icon-chevron-right chev"></i></a>

          <a class="link-item" href="secretarias_municipios.php" data-search="secretarias municipales configuracion sistema"><div class="link-left"><i class="feather icon-tool"></i><div class="txt"><div>Secretarías Municipales</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="componente_municipios.php" data-search="componentes municipales configuracion sistema"><div class="link-left"><i class="feather icon-grid"></i><div class="txt"><div>Componentes Municipales</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= REGISTRO VISITAS GOBERNADOR (ADMIN O SECRETARIO) ========= -->
    <?php if ($isAdmin || $isSecretario): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-flag"></i> Registro Visitas Gobernador</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="mapa_visitas_gobernador.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" data-search="mapa visitas gobernador"><div class="link-left"><i class="feather icon-map-pin"></i><div class="txt"><div>Mapa Visitas</div><small>Mapa visita gobernador</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="informacion_visitas.php" data-search="ingreso visitas gobernador"><div class="link-left"><i class="feather icon-edit"></i><div class="txt"><div>Ingreso Visitas</div><small>Registro</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="cuadro-control-visitas.php" data-search="cuadro control visitas gobernador"><div class="link-left"><i class="feather icon-clipboard"></i><div class="txt"><div>Cuadro Control Visitas</div><small>Seguimiento</small></div></div><i class="feather icon-chevron-right chev"></i></a>

          <a class="link-item" href="cuadro-control-compromisos.php" data-search="control compromisos gobernador"><div class="link-left"><i class="feather icon-check-square"></i><div class="txt"><div>Control compromisos</div><small>Gestión</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="cuadro-control-compromisos-cumplidos.php" data-search="compromisos cumplidos gobernador"><div class="link-left"><i class="feather icon-shield"></i><div class="txt"><div>Compromisos Cumplidos</div><small>Histórico</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="cuadro-control-compromisos-aprobacion.php" data-search="aprobacion compromisos gobernador"><div class="link-left"><i class="feather icon-thumbs-up"></i><div class="txt"><div>Aprobación compromisos</div><small>Validación</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="gestion-cumplimiento.php" data-search="gestion cumplimiento gobernador"><div class="link-left"><i class="feather icon-trending-up"></i><div class="txt"><div>Gestión cumplimiento</div><small>KPIs</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="visor_gestion_compromisos.php" data-search="visor gestion compromisos gobernador"><div class="link-left"><i class="feather icon-eye"></i><div class="txt"><div>Visor Gestión compromisos</div><small>Vista analítica</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= GESTIÓN SOCIAL (ADMIN) ========= -->
    <?php if ($isAdmin): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-heart"></i> Gestión social</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="gestora_social.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" data-search="gestora social actividades">
            <div class="link-left"><i class="feather icon-calendar"></i><div class="txt"><div>Actividades</div><small>Gestora social</small></div></div>
            <i class="feather icon-chevron-right chev"></i>
          </a>
          <a class="link-item" href="visitasgestora.php" data-search="registro actividades gestora"><div class="link-left"><i class="feather icon-edit"></i><div class="txt"><div>Registro Actividades</div><small>Ingreso</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="cuadro_control_visitasg.php" data-search="cuadro control actividades gestora"><div class="link-left"><i class="feather icon-clipboard"></i><div class="txt"><div>Cuadro Control Actividades</div><small>Seguimiento</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= GESTIÓN SOCIAL2 (si aplica) ========= -->
    <?php if ($isGestorSocial): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-heart"></i> Gestión Social2</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="aspasactividades.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" data-search="aspas actividades"><div class="link-left"><i class="feather icon-calendar"></i><div class="txt"><div>Actividades</div><small>Gestión social</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="visitasaspas.php" data-search="registro actividades aspas"><div class="link-left"><i class="feather icon-edit"></i><div class="txt"><div>Registro Actividades</div><small>Ingreso</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="cuadro_control_visitasaspas.php" data-search="cuadro control aspas"><div class="link-left"><i class="feather icon-clipboard"></i><div class="txt"><div>Cuadro Control Actividades</div><small>Seguimiento</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= MAPA COMPARATIVO + PLAN DESARROLLO (ADMIN/SECRETARIO) ========= -->
    <?php if ($isAdmin || $isSecretario): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-layers"></i> Planeación</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="mapa_comparativo.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" data-search="mapa comparativo"><div class="link-left"><i class="feather icon-map"></i><div class="txt"><div>Mapa Comparativo</div><small>Comparación territorial</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="plan_desarrollo.php" data-search="plan desarrollo metas"><div class="link-left"><i class="feather icon-flag"></i><div class="txt"><div>Plan de Desarrollo</div><small>Metas</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= PLAN DESARROLLO ALCALDIAS (ADMIN/ALCALDE) ========= -->
    <?php if ($isAdmin || $isAlcalde): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-flag"></i> Plan Desarrollo Alcaldía</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="plan_desarrollo_alcalde.php" data-search="plan desarrollo alcaldia metas"><div class="link-left"><i class="feather icon-flag"></i><div class="txt"><div>Metas</div><small>Plan de desarrollo</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= PLANEACIÓN ALCALDIA (ADMIN/ALCALDE) ========= -->
    <?php if ($isAdmin || $isAlcalde): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-file-text"></i> Proyectos Planeación</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="proyectos_planeacion_alcaldia.php" data-search="proyectos planeacion ingreso proyectos"><div class="link-left"><i class="feather icon-edit"></i><div class="txt"><div>Ingreso Proyectos</div><small>Planeación Alcaldía</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= SECRETARIAS (ADMIN O SECRETARIO) con mismas restricciones ========= -->
    <?php if ($isAdmin || $isSecretario): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-folder"></i> Información Secretarias</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>

          <?php if ($isAdmin || $isAlcalde): ?>
            <?php if ($secretariaId != Util::getSecretariaIdHacienda()): ?>
              <a class="link-item" href="secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>" data-search="resumen secretarias"><div class="link-left"><i class="feather icon-bar-chart-2"></i><div class="txt"><div>Resumen Secretarias</div><small>Vista por dependencia</small></div></div><i class="feather icon-chevron-right chev"></i></a>
              <a class="link-item" href="comparativo_secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>" data-search="comparativo secretarias"><div class="link-left"><i class="feather icon-trending-up"></i><div class="txt"><div>Comparativo secretarías</div><small>Comparación</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <?php endif; ?>

            <?php if ($secretariaId == Util::getSecretariaIdHacienda()): ?>
              <a class="link-item" href="secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo Util::getSecretariaIdHacienda(); ?>&accion=Operativos+Contrabando+licores" data-search="hacienda contrabando licores resumen"><div class="link-left"><i class="feather icon-shield"></i><div class="txt"><div>Resumen Secretarias</div><small>Operativos contrabando licores</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <?php endif; ?>

            <a class="link-item" href="ingreso_pae.php" data-search="pae ingreso informacion"><div class="link-left"><i class="feather icon-book"></i><div class="txt"><div>Información Pae</div><small>Registro</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="hacienda.php" data-search="informacion hacienda"><div class="link-left"><i class="feather icon-dollar-sign"></i><div class="txt"><div>Información Hacienda</div><small>Detalle</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="bienes.php" data-search="informacion administrativa bienes"><div class="link-left"><i class="feather icon-archive"></i><div class="txt"><div>Información Administrativa</div><small>Bienes</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="pae_dash.php?mun=<?php echo 'todos'; ?>" data-search="dashboard pae"><div class="link-left"><i class="feather icon-pie-chart"></i><div class="txt"><div>Dashboard Pae</div><small>Analítica</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="tic.php" data-search="informacion tic"><div class="link-left"><i class="feather icon-cpu"></i><div class="txt"><div>Información Tic</div><small>Detalle</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="tic_dash.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&opcion=<?php echo Util::getOpcionPrincipalTIC(); ?>" data-search="dashboard tic"><div class="link-left"><i class="feather icon-monitor"></i><div class="txt"><div>Dashboard Tic</div><small>Analítica</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="proyectos_secretarias.php" data-search="ingreso proyectos secretarias"><div class="link-left"><i class="feather icon-file-plus"></i><div class="txt"><div>Ingreso Proyectos Secretarias</div><small>Registro</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="proyectos_seguimiento_secretarias.php" data-search="seguimiento proyectos secretarias"><div class="link-left"><i class="feather icon-check-circle"></i><div class="txt"><div>Seguimiento Proyectos Secretarias</div><small>Control</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="dash_secretarias.php" data-search="dashboard secretarias"><div class="link-left"><i class="feather icon-activity"></i><div class="txt"><div>Dashboard Secretarias</div><small>KPIs</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="dash_adminitrativa.php" data-search="dashboard administrativa"><div class="link-left"><i class="feather icon-grid"></i><div class="txt"><div>Dashboard Administrativa</div><small>KPIs</small></div></div><i class="feather icon-chevron-right chev"></i></a>

          <?php else: ?>
            <!-- SECRETARIO: mismas restricciones por ID -->
            <?php if ($secretariaId != Util::getSecretariaIdHacienda()): ?>
              <a class="link-item" href="secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>" data-search="resumen secretarias"><div class="link-left"><i class="feather icon-bar-chart-2"></i><div class="txt"><div>Resumen Secretarias</div><small>Vista por dependencia</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <?php endif; ?>

            <?php if ($secretariaId == Util::getSecretariaIdHacienda()): ?>
              <a class="link-item" href="secretaria.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>&accion=Operativos+Contrabando+licores" data-search="hacienda contrabando licores resumen"><div class="link-left"><i class="feather icon-shield"></i><div class="txt"><div>Resumen Secretarias</div><small>Operativos contrabando licores</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <?php endif; ?>

            <?php if ($secretariaId == Util::getSecretariaIdEducacion()): ?>
              <a class="link-item" href="ingreso_pae.php" data-search="pae ingreso informacion"><div class="link-left"><i class="feather icon-book"></i><div class="txt"><div>Información Pae</div><small>Registro</small></div></div><i class="feather icon-chevron-right chev"></i></a>
              <a class="link-item" href="pae_dash.php?mun=<?php echo 'todos'; ?>" data-search="dashboard pae"><div class="link-left"><i class="feather icon-pie-chart"></i><div class="txt"><div>Dashboard Pae</div><small>Analítica</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <?php endif; ?>

            <?php if ($secretariaId == Util::getSecretariaIdHacienda()): ?>
              <a class="link-item" href="hacienda.php" data-search="informacion hacienda"><div class="link-left"><i class="feather icon-dollar-sign"></i><div class="txt"><div>Información Hacienda</div><small>Detalle</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <?php endif; ?>

            <?php if ($secretariaId == Util::getSecretariaIdAdministrativa()): ?>
              <a class="link-item" href="bienes.php" data-search="informacion administrativa bienes"><div class="link-left"><i class="feather icon-archive"></i><div class="txt"><div>Información Administrativa</div><small>Bienes</small></div></div><i class="feather icon-chevron-right chev"></i></a>
              <a class="link-item" href="dash_adminitrativa.php" data-search="dashboard administrativa"><div class="link-left"><i class="feather icon-grid"></i><div class="txt"><div>Dashboard Administrativa</div><small>KPIs</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <?php endif; ?>

            <?php if ($secretariaId == Util::getSecretariaIdTIC()): ?>
              <a class="link-item" href="tic.php" data-search="informacion tic"><div class="link-left"><i class="feather icon-cpu"></i><div class="txt"><div>Información Tic</div><small>Detalle</small></div></div><i class="feather icon-chevron-right chev"></i></a>
              <a class="link-item" href="tic_dash.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&opcion=<?php echo Util::getOpcionPrincipalTIC(); ?>" data-search="dashboard tic"><div class="link-left"><i class="feather icon-monitor"></i><div class="txt"><div>Dashboard Tic</div><small>Analítica</small></div></div><i class="feather icon-chevron-right chev"></i></a>
              <a class="link-item" href="proyectos_secretarias.php" data-search="ingreso proyectos secretarias"><div class="link-left"><i class="feather icon-file-plus"></i><div class="txt"><div>Ingreso Proyectos Secretarias</div><small>Registro</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <?php endif; ?>

            <a class="link-item" href="proyectos_seguimiento_secretarias.php" data-search="seguimiento proyectos secretarias"><div class="link-left"><i class="feather icon-check-circle"></i><div class="txt"><div>Seguimiento Proyectos Secretarias</div><small>Control</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="dash_secretarias.php" data-search="dashboard secretarias"><div class="link-left"><i class="feather icon-activity"></i><div class="txt"><div>Dashboard Secretarias</div><small>KPIs</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <?php endif; ?>

        </div>
      </div>
    <?php endif; ?>

    <!-- ========= SECRETARIAS ALCALDIAS (ADMIN/ALCALDE/SECRETARIO) ========= -->
    <?php if ($isAdmin || $isSecretario || $isAlcalde): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-folder"></i> Secretarias Alcaldias</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <?php if ($isAdmin || $isAlcalde): ?>
            <a class="link-item" href="secretaria_alcalde.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>&secretaria=<?php echo $secretariaId; ?>" data-search="secretaria alcalde resumen"><div class="link-left"><i class="feather icon-bar-chart-2"></i><div class="txt"><div>Resumen Secretarias</div><small>Alcaldías</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="proyectos_secretarias_alcalde.php" data-search="ingreso proyectos secretarias alcalde"><div class="link-left"><i class="feather icon-file-plus"></i><div class="txt"><div>Ingreso Proyectos Secretarias</div><small>Alcaldías</small></div></div><i class="feather icon-chevron-right chev"></i></a>
            <a class="link-item" href="proyectos_seguimiento_secretarias_alcalde.php" data-search="seguimiento proyectos alcaldias"><div class="link-left"><i class="feather icon-check-circle"></i><div class="txt"><div>Seguimiento Proyectos Alcaldías</div><small>Control</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= ALCALDIAS (ADMIN/ALCALDE/SECRETARIO) ========= -->
    <?php if ($isAdmin || $isAlcalde || $isSecretario): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-clipboard"></i> Alcaldías</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>

          <a class="link-item" href="resumenalcaldias.php?<?php
            if ($isAlcalde) {
              echo 'mun=' . urlencode($municipioUsuarioLogueado);
            } else {
              echo 'secretaria=' . urlencode(Util::getSecretariaPrincipal());
            }
          ?>" data-search="resumen alcaldias">
            <div class="link-left"><i class="feather icon-bar-chart-2"></i><div class="txt"><div>Resumen alcaldías</div><small>Vista general</small></div></div>
            <i class="feather icon-chevron-right chev"></i>
          </a>

          <a class="link-item" href="proyectos_alcaldias.php" data-search="ingreso proyectos alcaldias"><div class="link-left"><i class="feather icon-file-plus"></i><div class="txt"><div>Ingreso Proyectos</div><small>Alcaldías</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="proyectos_seguimiento_alcaldias.php" data-search="seguimiento proyectos alcaldias"><div class="link-left"><i class="feather icon-check-circle"></i><div class="txt"><div>Seguimiento Proyectos</div><small>Control</small></div></div><i class="feather icon-chevron-right chev"></i></a>

        </div>
      </div>
    <?php endif; ?>

    <!-- ========= ACCIÓN UNIFICADA (ADMIN/SECRETARIO) ========= -->
    <?php if ($isAdmin || $isSecretario): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-globe"></i> Acción Unificada</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="departamentos.php?pilar=10000" data-search="estado departamento accion unificada"><div class="link-left"><i class="feather icon-bar-chart-2"></i><div class="txt"><div>Estado Departamento</div><small>Indicadores</small></div></div><i class="feather icon-chevron-right chev"></i></a>

          <a class="link-item" href="municipios.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&dep=<?php echo Util::getDepartamentoPrincipal(); ?>&pilar=<?php echo Util::codigoTodos(); ?>" data-search="estado municipios accion unificada"><div class="link-left"><i class="feather icon-map-pin"></i><div class="txt"><div>Estado Municipios</div><small>Detalle</small></div></div><i class="feather icon-chevron-right chev"></i></a>

          <a class="link-item" href="consolidado_ciudades.php" data-search="estadisticas bd registros bases de datos"><div class="link-left"><i class="feather icon-database"></i><div class="txt"><div>Estadísticas BD</div><small>Registros</small></div></div><i class="feather icon-chevron-right chev"></i></a>

          <a class="link-item" href="veredas_criticas.php" data-search="veredas criticas"><div class="link-left"><i class="feather icon-alert-triangle"></i><div class="txt"><div>Veredas Criticas</div><small>Riesgos</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="avances.php#!" data-search="avances"><div class="link-left"><i class="feather icon-trending-up"></i><div class="txt"><div>Avances</div><small>Seguimiento</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="imagenes.php" data-search="imagenes"><div class="link-left"><i class="feather icon-image"></i><div class="txt"><div>Imágenes</div><small>Galería</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="accionunificada.php" data-search="accion unificada"><div class="link-left"><i class="feather icon-activity"></i><div class="txt"><div>Acción unificada</div><small>Vista</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="informes.php" data-search="informes"><div class="link-left"><i class="feather icon-file-text"></i><div class="txt"><div>Informes</div><small>Reportes</small></div></div><i class="feather icon-chevron-right chev"></i></a>

          <a class="link-item" href="listado_factores_generales.php?mun=<?php echo Util::getCodigoMunicipioPrincipal(); ?>&dep=<?php echo Util::getDepartamentoPrincipal(); ?>&pilar=<?php echo Util::codigoTodos(); ?>&secretaria=<?php echo Util::codigoTodos(); ?>" data-search="factores generales listado"><div class="link-left"><i class="feather icon-list"></i><div class="txt"><div>Estado Listado Factores Generales</div><small>Listado</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= CONFIG ACCIÓN UNIFICADA (ADMIN) ========= -->
    <?php if ($isAdmin): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-refresh-ccw"></i> Configuración Acción Unificada</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="areas.php" data-search="areas ingreso"><div class="link-left"><i class="feather icon-plus"></i><div class="txt"><div>Ingreso Áreas</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="ingreso_factores.php" data-search="factores ingreso"><div class="link-left"><i class="feather icon-plus"></i><div class="txt"><div>Ingreso Factores</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="actores.php" data-search="actores ingreso"><div class="link-left"><i class="feather icon-plus"></i><div class="txt"><div>Ingreso Actores</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="ingreso_informacion.php" data-search="ingreso informacion"><div class="link-left"><i class="feather icon-edit"></i><div class="txt"><div>Ingreso Información</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="ingreso_informacion_listado.php" data-search="listado ingreso informacion"><div class="link-left"><i class="feather icon-list"></i><div class="txt"><div>Listado Ingreso Información</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="actualizacion_informacion.php" data-search="actualizacion informacion"><div class="link-left"><i class="feather icon-refresh-cw"></i><div class="txt"><div>Actualización Información</div><small>Configuración</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= ESTADÍSTICA Comportamiento DELICTIVA (ADMIN/SECRETARIO) ========= -->
    <?php if ($isAdmin || $isSecretario): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-shield"></i> Comportamiento Delictiva</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="informacion-policia.php" data-search="informes policia"><div class="link-left"><i class="feather icon-file-text"></i><div class="txt"><div>Informes Policía</div><small>Tablas</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="graficos-policia.php" data-search="graficos policia"><div class="link-left"><i class="feather icon-pie-chart"></i><div class="txt"><div>Gráficos Policía</div><small>Visualizaciones</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <!-- ========= PROYECTOS ESTRATÉGICOS (ADMIN/SECRETARIO) ========= -->
    <?php if ($isAdmin || $isSecretario): ?>
      <div class="section" data-section>
        <div class="section-h" data-toggle-section>
          <h6 class="title"><i class="feather icon-crosshair"></i> Proyectos Estratégicos</h6>
          <i class="feather icon-chevron-down"></i>
        </div>
        <div class="section-body" data-body>
          <a class="link-item" href="secretaria_estrategicos.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" data-search="estrategicos departamento"><div class="link-left"><i class="feather icon-map"></i><div class="txt"><div>Departamento</div><small>Vista</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="ingreso_estrategicos.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" data-search="ingreso estrategicos"><div class="link-left"><i class="feather icon-edit-2"></i><div class="txt"><div>Ingreso Información</div><small>Registro</small></div></div><i class="feather icon-chevron-right chev"></i></a>
          <a class="link-item" href="actualizacion_estrategicos.php?depto_id=<?php echo Util::getIdentificadorDepartamentoPrincipal(); ?>" data-search="actualizacion estrategicos"><div class="link-left"><i class="feather icon-refresh-cw"></i><div class="txt"><div>Actualización Información</div><small>Mantenimiento</small></div></div><i class="feather icon-chevron-right chev"></i></a>
        </div>
      </div>
    <?php endif; ?>

    <div style="height:10px;"></div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script>
    // Acordeones
    (function(){
      const toggles = document.querySelectorAll('[data-toggle-section]');
      toggles.forEach(t => {
        t.addEventListener('click', () => {
          const section = t.closest('[data-section]');
          const body = section.querySelector('[data-body]');

          const isHidden = body.classList.contains('hidden');

          // Cierra otros
          document.querySelectorAll('[data-body]').forEach(b => { if (b !== body) b.classList.add('hidden'); });

          // Abre/cierra actual
          body.classList.toggle('hidden', !isHidden);

          // Ajusta flechas
          toggles.forEach(x=>{
            const ic = x.querySelector('i.feather:last-child');
            if(ic) ic.className = 'feather icon-chevron-down';
          });
          const ic2 = t.querySelector('i.feather:last-child');
          if(ic2) ic2.className = isHidden ? 'feather icon-chevron-up' : 'feather icon-chevron-down';
        });
      });

      // Abre primera sección
      const firstBody = document.querySelector('[data-section] [data-body]');
      if(firstBody) firstBody.classList.remove('hidden');
      const firstI = document.querySelector('[data-section] [data-toggle-section] i.feather:last-child');
      if(firstI) firstI.className = 'feather icon-chevron-up';
    })();

    // Buscador
    (function(){
      const input = document.getElementById('menuSearch');
      const links = Array.from(document.querySelectorAll('.link-item'));
      const sections = Array.from(document.querySelectorAll('[data-section]'));

      function normalize(s){ return (s||'').toLowerCase().trim(); }

      input.addEventListener('input', () => {
        const q = normalize(input.value);
        if(!q){
          links.forEach(a => a.classList.remove('hidden'));
          sections.forEach(sec => sec.classList.remove('hidden'));
          return;
        }

        sections.forEach(sec => sec.classList.add('hidden'));

        links.forEach(a => {
          const hay = normalize(a.getAttribute('data-search') + ' ' + a.innerText);
          const ok = hay.includes(q);
          a.classList.toggle('hidden', !ok);

          if(ok){
            const sec = a.closest('[data-section]');
            if(sec) sec.classList.remove('hidden');

            const body = sec.querySelector('[data-body]');
            if(body) body.classList.remove('hidden');

            const ic = sec.querySelector('[data-toggle-section] i.feather:last-child');
            if(ic) ic.className = 'feather icon-chevron-up';
          }
        });
      });
    })();

    document.addEventListener("DOMContentLoaded", () => {
      if (window.feather && typeof window.feather.replace === "function") feather.replace();
    });
  </script>
</body>
</html>
