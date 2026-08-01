<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';
include './admin/classes/ComponenteMunicipios.php';
include './admin/classes/Ciudad.php';
include './admin/classes/Departamento.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

$userType = SessionData::getUserType();
$isAdmin  = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

// Tipos de usuario municipal
$tiposUsuarioMunicipal = ['Alcalde','Auxiliar_Alcalde','Secretario_Despacho','Auxiliar'];
$isUsuarioMunicipal = in_array($userType, $tiposUsuarioMunicipal);

if (!$isAdmin && !$isUsuarioMunicipal) {
  require 'permiso_denegado.php';
  exit;
}

// Municipio usuario
$municipioUsuario = '';
$codigoDepartamentoUsuario = '';
if ($isUsuarioMunicipal) {
  $municipioUsuario = SessionData::getCodigoMunicipio();
  $codigoDepartamentoUsuario = Util::getDepartamentoPrincipal();
}

// Departamentos
$arrDep  = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'];
$arrDep  = $arrDep['output']['response'];

$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
  $optionDep .= "<option value='".$val['codigo_departamento']."'>".$val['codigo_departamento']." - ".$val['departamento']."</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="./admin/js/datatables/jquery.dataTables.min.css" rel="stylesheet" />
  
  <link rel="stylesheet" href="assets/css/componentes_municipales_gob360_premium.css">
</head>

<body class="gob360-municipal-components-page">
  <!-- Preloader -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  <?php include './admin/include/navbar.php'; ?>
  <?php include './admin/include/header.php'; ?>

  <div class="pcoded-main-container">
    <div class="pcoded-content">

      <section class="g360-municipal-components-hero" aria-label="Componentes municipales GOB360">
        <div class="g360-municipal-components-hero__grid">

          <aside class="g360-municipal-components-brand">
            <span class="g360-municipal-components-brand__eyebrow">
              Plataforma institucional
            </span>

            <img
              src="assets/img/gob360l.png"
              alt="Logo GOB360"
              class="g360-municipal-components-brand__logo"
            >

            <span class="g360-municipal-components-brand__caption">
              Gestión pública inteligente y territorial
            </span>

            <div class="g360-municipal-components-brand__status">
              <span></span>
              Configuración municipal activa
            </div>
          </aside>

          <div class="g360-municipal-components-hero__content">
            <div class="g360-municipal-components-hero__top">
              <div>
                <div class="g360-municipal-components-hero__eyebrow">
                  <i class="feather icon-grid"></i>
                  Configuración territorial
                </div>

                <h1 class="g360-municipal-components-hero__title">
                  Componentes Municipales
                </h1>

                <p class="g360-municipal-components-hero__description">
                  Administra los componentes institucionales asociados a cada
                  municipio y controla su disponibilidad para los procesos,
                  formularios y módulos territoriales de GOB360.
                </p>
              </div>

              <div class="g360-municipal-components-hero__actions">
                <?php if ($create): ?>
                  <button
                    type="button"
                    class="g360-hero-button g360-hero-button--primary"
                    id="btnNuevoComponenteHero"
                    onclick="abrirComponenteMunicipalDesdeHero()"
                  >
                    <i class="feather icon-plus-circle"></i>
                    Nuevo componente
                  </button>
                <?php endif; ?>

                <button
                  type="button"
                  class="g360-hero-button g360-hero-button--secondary"
                  onclick="window.location.reload()"
                >
                  <i class="feather icon-refresh-cw"></i>
                  Actualizar
                </button>

                <div class="g360-municipal-components-back">
                  <?php include './admin/include/btn_back.php'; ?>
                </div>
              </div>
            </div>

            <div class="g360-municipal-components-summary">
              <article>
                <span class="g360-municipal-components-summary__icon">
                  <i class="feather icon-grid"></i>
                </span>

                <div>
                  <small>Módulo</small>
                  <strong>Componentes</strong>
                  <p>Configuración por municipio</p>
                </div>
              </article>

              <article>
                <span class="g360-municipal-components-summary__icon g360-municipal-components-summary__icon--territory">
                  <i class="feather icon-map-pin"></i>
                </span>

                <div>
                  <small>Territorio activo</small>
                  <strong>
                    <?= $isUsuarioMunicipal && $municipioUsuario !== '' ? htmlspecialchars((string)$municipioUsuario, ENT_QUOTES, 'UTF-8') : 'Todos' ?>
                  </strong>
                  <p><?= $isUsuarioMunicipal ? 'Municipio asignado al usuario' : 'Cobertura administrativa' ?></p>
                </div>
              </article>

              <article>
                <span class="g360-municipal-components-summary__icon g360-municipal-components-summary__icon--create">
                  <i class="feather icon-plus-square"></i>
                </span>

                <div>
                  <small>Creación</small>
                  <strong><?= $create ? 'Habilitada' : 'Restringida' ?></strong>
                  <p>Según permisos de la sesión</p>
                </div>
              </article>

              <article>
                <span class="g360-municipal-components-summary__icon g360-municipal-components-summary__icon--profile">
                  <i class="feather icon-shield"></i>
                </span>

                <div>
                  <small>Perfil activo</small>
                  <strong><?= $isAdmin ? 'Administrador' : 'Municipal' ?></strong>
                  <p><?= htmlspecialchars((string)$userType, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </article>
            </div>

            <div class="g360-municipal-components-capabilities" aria-hidden="true">
              <span>
                <i class="feather icon-map"></i>
                Departamento y municipio
              </span>

              <span>
                <i class="feather icon-layers"></i>
                Componentes territoriales
              </span>

              <span>
                <i class="feather icon-check-circle"></i>
                Estado de habilitación
              </span>

              <span>
                <i class="feather icon-edit-3"></i>
                Creación y edición
              </span>

              <span>
                <i class="feather icon-lock"></i>
                Control por perfil
              </span>
            </div>
          </div>

        </div>
      </section>

      <!-- Card principal -->
      <div class="card g360-municipal-components-card">
        <div class="card-header">
          <div class="g360-card-heading">
            <span class="g360-card-heading__icon">
              <i class="feather icon-list"></i>
            </span>

            <div>
              <span class="g360-card-heading__eyebrow">Matriz territorial</span>
              <h5 class="mb-0">Listado de componentes municipales</h5>
              <p>
                Consulta el nombre del componente, municipio asociado y estado
                de habilitación de cada registro.
              </p>
            </div>
          </div>

          <div class="g360-card-header-actions">
            <?php if ($create): ?>
              <button
                type="button"
                class="btn btn-primary"
                id="btnNuevoComponente"
                data-toggle="modal"
                data-target="#newModalComponente"
              >
                <i class="feather icon-plus"></i>
                Nuevo componente
              </button>
            <?php endif; ?>

            <div class="card-header-right">
              <div class="btn-group card-option">
                <button
                  type="button"
                  class="btn dropdown-toggle btn-icon"
                  data-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
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
        </div>

        <div class="card-body table-border-style">

          <div class="g360-municipal-components-tools">
            <div class="g360-municipal-components-search">
              <span class="g360-municipal-components-search__icon">
                <i class="feather icon-search"></i>
              </span>

              <div>
                <label for="customSearch">Búsqueda rápida</label>
                <input
                  type="search"
                  id="customSearch"
                  class="form-control"
                  placeholder="Buscar componente, municipio o estado..."
                >
              </div>
            </div>

            <div class="g360-municipal-components-tools__info">
              <i class="feather icon-info"></i>

              <span>
                Los componentes permiten clasificar información y procesos
                específicos dentro de cada municipio.
              </span>
            </div>
          </div>

          <!-- tabla -->
          <div class="table-responsive tabla-informacion tabla-scroll g360-municipal-components-table">
            <table class="table table-hover mb-0" id="tableComponentes" aria-label="Listado de componentes municipales">
              <thead>
                <tr>
                  <th>Editar</th>
                  <th>Nombre Componente</th>
                  <th>Municipio</th>
                  <th>Habilitado</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>

      <!-- ✅ MODAL -->
      <div class="modal fade" id="newModalComponente" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
          <div class="modal-content g360-municipal-component-modal">

            <div class="modal-header">
              <div class="g360-modal-heading">
                <span class="g360-modal-heading__icon">
                  <i class="feather icon-grid"></i>
                </span>

                <div>
                  <small>Configuración territorial</small>
                  <h5 class="modal-title">Componente municipal</h5>
                </div>
              </div>

              <button
                type="button"
                class="close text-white"
                data-dismiss="modal"
                aria-label="Cerrar"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body">
              <div class="g360-municipal-component-modal__intro">
                <span>
                  <i class="feather icon-shield"></i>
                </span>

                <div>
                  <strong>Registro municipal controlado</strong>
                  <p>
                    Selecciona la ubicación territorial, asigna un nombre claro
                    al componente y define si estará habilitado.
                  </p>
                </div>
              </div>

              <form id="formNewComponente" autocomplete="off">
                <input type="hidden" id="editId" name="editId">

                <div class="g360-modal-section-heading">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-map"></i>
                  </span>

                  <div>
                    <small>Ubicación territorial</small>
                    <h6>Departamento y municipio</h6>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="tbl_departamento_id">Departamento <span class="text-danger">*</span></label>
                    <select class="form-control" id="tbl_departamento_id" name="tbl_departamento_id" required onchange="DEPARTAMENTO.getMunicipios()">
                      <?php echo $optionDep; ?>
                    </select>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="tbl_municipio_id">Municipio <span class="text-danger">*</span></label>
                    <select class="form-control" id="tbl_municipio_id" name="tbl_municipio_id" required>
                      <option value="">Seleccione un municipio</option>
                    </select>
                  </div>
                </div>

                <div class="g360-modal-section-heading g360-modal-section-heading--component">
                  <span class="g360-modal-section-heading__icon">
                    <i class="feather icon-layers"></i>
                  </span>

                  <div>
                    <small>Configuración</small>
                    <h6>Nombre y disponibilidad del componente</h6>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-8">
                    <label for="newComponente">Nombre Componente <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newComponente" name="newComponente" required>
                  </div>

                  <div class="form-group col-md-4">
                    <label for="newHabilitado">Habilitado <span class="text-danger">*</span></label>
                    <select class="form-control" id="newHabilitado" name="newHabilitado" required>
                      <option value="si" selected>Sí</option>
                      <option value="no">No</option>
                    </select>
                  </div>
                </div>

              </form>
            </div>

            <div class="modal-footer">
              <div class="g360-modal-footer-message">
                <i class="feather icon-lock"></i>
                El componente quedará vinculado al municipio seleccionado.
              </div>

              <div class="g360-modal-footer-actions">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                  Cancelar
                </button>

                <button
                  type="button"
                  id="btnSaveComponente"
                  class="btn btn-primary"
                  onclick="saveNewComponente();"
                >
                  <i class="feather icon-save"></i>
                  Guardar componente
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Variables de sesión -->
      <input type="hidden" id="municipioUsuario" value="<?php echo $municipioUsuario; ?>">
      <input type="hidden" id="tipoUsuario" value="<?php echo $userType; ?>">
      <input type="hidden" id="isAdmin" value="<?php echo $isAdmin ? '1' : '0'; ?>">

    </div>
  </div>

  <?php include 'admin/include/gerenic_script.php'; ?>

  <!-- ✅ IMPORTANTE: orden correcto -->
  <script src="assets/js/vendor-all.min.js"></script>
  <script src="assets/js/plugins/bootstrap.min.js"></script>
  <script src="assets/js/pcoded.min.js"></script>

  <script type="text/javascript" src="admin/js/departamento.js"></script>
  <script type="text/javascript" src="admin/js/componente-municipios.js"></script>

  <script type="text/javascript" src="./admin/js/datatables/jquery.dataTables.min.js"></script>

  <script>
    function abrirComponenteMunicipalDesdeHero() {
      const botonOriginal = document.getElementById('btnNuevoComponente');

      if (botonOriginal) {
        botonOriginal.click();
        return;
      }

      if ($('#newModalComponente').length) {
        try {
          $('#newModalComponente').modal('show');
        } catch (e) {}
      }

      if (typeof ingresarComponente === 'function') {
        try {
          ingresarComponente();
        } catch (e) {}
      }
    }

    // ✅ Fallback: si por algún conflicto el data-toggle no engancha
    $(document).on('click', '#btnNuevoComponente', function () {
      if ($('#newModalComponente').length) {
        try { $('#newModalComponente').modal('show'); } catch(e) {}
      }
      if (typeof ingresarComponente === 'function') {
        try { ingresarComponente(); } catch(e) {}
      }
    });
  </script>

</body>
</html>
