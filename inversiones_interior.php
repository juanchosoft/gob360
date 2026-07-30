<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';
require './admin/include/generic_classes.php';

// Permisos RBAC
extract(PagePermissions::crudVarsForCurrentPage());

// Solo Admin, Gobernador y Secretaría de Interior (id=2)
$userTypeInt = SessionData::getUserType();
$secretariaIdInt = SessionData::getSecretaria();
$isAdminInt = ($userTypeInt === Util::Administrador() || $userTypeInt === Util::SuperAdministrador() || $userTypeInt === Util::Gobernador());
if (!$isAdminInt && $secretariaIdInt != Util::getSecretariaIdInterior()) {
    header('Location: dashboard.php');
    exit;
}

include './admin/classes/Departamento.php';
include './admin/classes/Provincias.php';
include './admin/classes/Ciudad.php';

$provArr = Provincias::getProvinciasByDepartamento(Util::getDepartamentoPrincipal());
$provinciasJSON = '[]';
if (!empty($provArr['output']['valid'])) {
    $provinciasJSON = json_encode($provArr['output']['response']);
}

$modulo = 'Registro Visitas';



// Información de Departamentos
$arrDep = Departamento::getAll(null);
$isvalid = $arrDep['output']['valid'] ?? false;
$arrDep = $arrDep['output']['response'] ?? [];
$optionDep = Util::getDepartamentoPrincipal();
foreach ($arrDep as $val) {
    $optionDep .= "<option value='" . htmlspecialchars($val['codigo_departamento']) . "'>" .
        htmlspecialchars($val['codigo_departamento']) . " - " . htmlspecialchars($val['departamento']) . "</option>";
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<link rel="stylesheet" href="assets/css/inversiones_seguridad_gob360.css">

<body class="gob360-security-investments">
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

            <section class="g360-investments-hero" aria-label="Inversiones en Seguridad GOB360">
                <div class="g360-investments-hero__grid">

                    <aside class="g360-investments-brand">
                        <span class="g360-investments-brand__eyebrow">
                            Plataforma institucional
                        </span>

                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-investments-brand__logo"
                        >

                        <span class="g360-investments-brand__caption">
                            Gestión pública inteligente y territorial
                        </span>

                        <div class="g360-investments-brand__status">
                            <span></span>
                            Módulo operativo
                        </div>
                    </aside>

                    <div class="g360-investments-hero__content">
                        <div class="g360-investments-hero__top">
                            <div>
                                <div class="g360-investments-hero__eyebrow">
                                    <i class="feather icon-shield"></i>
                                    Secretaría del Interior
                                </div>

                                <h1 class="g360-investments-hero__title">
                                    Inversiones en Seguridad
                                </h1>

                                <p class="g360-investments-hero__description">
                                    Registra y administra contratos destinados a movilidad,
                                    tecnología, infraestructura, intendencia, convenios,
                                    recompensas y proyectos estratégicos de seguridad.
                                </p>
                            </div>

                            <div class="g360-investments-back">
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                        </div>

                        <div class="g360-investments-features" aria-hidden="true">
                            <article>
                                <i class="feather icon-file-text"></i>
                                <span>Contratos</span>
                            </article>

                            <article>
                                <i class="feather icon-map-pin"></i>
                                <span>Territorio</span>
                            </article>

                            <article>
                                <i class="feather icon-users"></i>
                                <span>Instituciones</span>
                            </article>

                            <article>
                                <i class="feather icon-dollar-sign"></i>
                                <span>Inversión</span>
                            </article>
                        </div>

                        <div class="g360-investments-hero__meta">
                            <span>
                                <i class="feather icon-check-circle"></i>
                                Registro institucional
                            </span>

                            <span>
                                <i class="feather icon-database"></i>
                                Gestión centralizada
                            </span>

                            <span>
                                <i class="feather icon-image"></i>
                                Evidencia documental
                            </span>
                        </div>
                    </div>

                </div>
            </section>
                <div>
                    <?php include './admin/include/btn_back.php'; ?>
                </div>
            </div>

            <!-- NAV TABS -->
            <div class="g360-investments-navigation">
                <div>
                    <span class="g360-investments-navigation__eyebrow">Gestión contractual</span>
                    <h2>Registro y consulta de inversiones</h2>
                    <p>Selecciona la operación que deseas realizar.</p>
                </div>

                <ul class="nav nav-tabs au-tabs" id="inversionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-form-btn" data-toggle="tab" data-target="#tab-form"
                        type="button" role="tab">
                            <i class="feather icon-file-plus"></i>
                            Ingresar contrato
                        </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-lista-btn" data-toggle="tab" data-target="#tab-lista"
                        type="button" role="tab" onclick="cargarTablaInversiones()">
                            <i class="feather icon-list"></i>
                            Listado de contratos
                        </button>
                </li>
                </ul>
            </div>

            <div class="tab-content" id="inversionTabContent">

                <!-- TAB FORMULARIO -->
                <div class="tab-pane fade show active" id="tab-form" role="tabpanel">
                    <div class="card mt-3 g360-investment-card g360-investment-card--form">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <div class="g360-card-heading">
                                <span class="g360-card-heading__icon">
                                    <i class="feather icon-file-plus"></i>
                                </span>

                                <div>
                                    <span class="g360-card-heading__eyebrow">Nuevo registro</span>
                                    <h5>Formulario de Registro de Contratos</h5>
                                    <p>Completa la información contractual, territorial y financiera.</p>
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
                        <div class="card-body">
                            <form class="needs-validation" novalidate id="ingresoVisita" enctype="multipart/form-data">

                                <div class="g360-form-section">
                                    <span class="g360-form-section__icon">
                                        <i class="feather icon-file-text"></i>
                                    </span>
                                    <div>
                                        <h6>Información contractual</h6>
                                        <p>Fecha, tipo de inversión y denominación del contrato.</p>
                                    </div>
                                </div>

                                <div class="au-form-grid md-3">
                                    <div class="form-group">
                                        <label>Fecha <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Tipo Inversión <span class="text-danger">*</span></label>
                                        <select name="tipo_seccion" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            <option value="movilidad">Movilidad</option>
                                            <option value="tecnologia">Tecnología</option>
                                            <option value="proyectos">Proyectos Estratégicos</option>
                                            <option value="intendencia">Intendencia</option>
                                            <option value="infraestructura">Infraestructura</option>
                                            <option value="pagos">Pagos Recompensas</option>
                                            <option value="convenios">Convenios</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Contrato <span class="text-danger">*</span></label>
                                        <input type="text" name="titulo" class="form-control" required>
                                    </div>
                                </div>

                                <div class="g360-form-section">
                                    <span class="g360-form-section__icon g360-form-section__icon--success">
                                        <i class="feather icon-briefcase"></i>
                                    </span>
                                    <div>
                                        <h6>Beneficiario y presupuesto</h6>
                                        <p>Institución beneficiada, dependencia responsable y valor del contrato.</p>
                                    </div>
                                </div>

                                <div class="au-form-grid md-3">
                                    <div class="form-group">
                                        <label>Institución Beneficiada <span class="text-danger">*</span></label>
                                        <select id="institucion" name="institucion" class="form-control" required>
                                            <option value="">-- Seleccionar --</option>
                                            <option value="POLICIA MEBUC">POLICIA MEBUC</option>
                                            <option value="POLICIA DESAN">POLICIA DESAN</option>
                                            <option value="POLICIA DEMAM">POLICIA DEMAM</option>
                                            <option value="EJERCITO NACIONAL">EJERCITO NACIONAL</option>
                                            <option value="ARMADA NACIONAL">ARMADA NACIONAL</option>
                                            <option value="FISCALIA">FISCALIA</option>
                                            <option value="MIGRACION COLOMBIA">MIGRACION COLOMBIA</option>
                                            <option value="INPEC">INPEC</option>
                                            <option value="UNP">UNP</option>
                                            <option value="DEPARTAMENTO DE SANTANDER">DEPARTAMENTO DE SANTANDER</option>
                                            <option value="OTRO">OTRO</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Dirección <span class="text-danger">*</span></label>
                                        <select id="direccion" name="direccion" class="form-control" required>
                                            <option value="Dirección de Seguridad y Convivencia">Dirección de Seguridad y Convivencia</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Valor ($) <span class="text-danger">*</span></label>
                                        <input type="text" id="valor" name="valor" class="form-control" required>
                                    </div>
                                </div>

                                <div class="g360-form-section">
                                    <span class="g360-form-section__icon g360-form-section__icon--territory">
                                        <i class="feather icon-map"></i>
                                    </span>
                                    <div>
                                        <h6>Cobertura territorial</h6>
                                        <p>Selecciona una o varias provincias y los municipios beneficiados.</p>
                                    </div>
                                </div>

                                <div class="form-group mt-3 g360-territory-field">
                                    <label>Provincia / Municipio <span class="text-danger">*</span></label>
                                    <div id="municipios-container" class="g360-municipality-builder">
                                        <div class="row-municipio g360-municipality-row">
                                            <div class="g360-municipality-toolbar">
                                                <select class="form-control provincia-select">
                                                    <option value="">-- Provincia --</option>
                                                </select>
                                                <button type="button" class="btn btn-success btn-sm btn-add-municipio" title="Agregar otra provincia">
                                                    <i class="feather icon-plus"></i> Agregar
                                                </button>
                                            </div>
                                            <select class="municipio-select-hidden" name="municipios[]" multiple style="display:none;"></select>
                                            <div class="municipio-pills d-flex flex-wrap"></div>
                                        </div>
                                    </div>
                                    <small class="text-muted">Selecciona una provincia, luego haz clic en los municipios para seleccionarlos. Usa "Agregar" para añadir más provincias.</small>
                                </div>

                                <div class="g360-form-section">
                                    <span class="g360-form-section__icon g360-form-section__icon--image">
                                        <i class="feather icon-image"></i>
                                    </span>
                                    <div>
                                        <h6>Descripción y evidencia</h6>
                                        <p>Agrega información complementaria y una imagen del contrato.</p>
                                    </div>
                                </div>

                                <div class="au-form-grid md-3 mt-3">
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <textarea name="descripcion" rows="3" class="form-control"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Imagen</label>
                                        <input type="file" id="imagen" name="imagen" class="form-control">
                                        <div id="previewImagen" class="mt-2"></div>
                                    </div>
                                </div>

                                <div class="g360-save-bar">
                                    <div class="g360-save-bar__message">
                                        <i class="feather icon-shield"></i>
                                        <span>
                                            Verifica los datos antes de registrar la inversión.
                                        </span>
                                    </div>

                                    <button type="submit" class="btn btn-pro">
                                        <i class="feather icon-save"></i>
                                        Guardar inversión
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB LISTADO -->
                <div class="tab-pane fade" id="tab-lista" role="tabpanel">
                    <div class="card mt-3 g360-investment-card g360-investment-card--list">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <div class="g360-card-heading">
                                <span class="g360-card-heading__icon g360-card-heading__icon--list">
                                    <i class="feather icon-list"></i>
                                </span>

                                <div>
                                    <span class="g360-card-heading__eyebrow">Consulta institucional</span>
                                    <h5>Listado de Contratos</h5>
                                    <p>Consulta, edita y administra las inversiones registradas.</p>
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
                        <div class="card-body p-0">
                            <div class="table-wrap">
                                <div class="table-shell">
                                    <div class="table-shell__top">
                                        <div>
                                            <div class="table-shell__eyebrow">Contratos registrados</div>
                                            <h3 class="table-shell__title">Listado de Contratos</h3>
                                            <div class="table-shell__subtitle">Consulta, edita y administra los contratos de inversión en seguridad.</div>
                                        </div>
                                        <div class="table-shell__badge">
                                                <i class="feather icon-shield"></i>
                                                GOB360
                                            </div>
                                    </div>
                                    <div class="table-shell__body">
                                        <div class="table-responsive">
                                            <table id="dynamictable" class="table table-hover mb-0 w-100">
                                                <thead>
                                                    <tr>
                                                        <th><i class="feather icon-settings"></i></th>
                                                        <th>#</th>
                                                        <th>Fecha</th>
                                                        <th>Tipo</th>
                                                        <th>Contrato</th>
                                                        <th>Institución</th>
                                                        <th>Municipio</th>
                                                        <th>Dirección</th>
                                                        <th>Valor ($)</th>
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

            </div><!-- /tab-content -->

        </div>
    </div>

    <!-- MODAL VER INVERSIÓN -->
    <div class="modal fade" id="modalVerInversion" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered g360-investment-modal-dialog" role="document">
            <div class="modal-content g360-investment-modal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="g360-modal-title-icon">
                            <i class="feather icon-eye"></i>
                        </span>
                        Detalle del Contrato
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="modalVerBody"></div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR INVERSIÓN -->
    <div class="modal fade" id="modalEditarInversion" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered g360-investment-modal-dialog g360-investment-modal-dialog--xl" role="document">
            <div class="modal-content g360-investment-modal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="g360-modal-title-icon">
                            <i class="feather icon-edit"></i>
                        </span>
                        Editar Contrato
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarInversion" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="au-form-grid md-3">
                            <div class="form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" id="edit_fecha" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Tipo Inversión <span class="text-danger">*</span></label>
                                <select name="tipo_seccion" id="edit_tipo_seccion" class="form-control" required>
                                    <option value="">Seleccione</option>
                                    <option value="movilidad">Movilidad</option>
                                    <option value="tecnologia">Tecnología</option>
                                    <option value="proyectos">Proyectos Estratégicos</option>
                                    <option value="intendencia">Intendencia</option>
                                    <option value="infraestructura">Infraestructura</option>
                                    <option value="pagos">Pagos Recompensas</option>
                                    <option value="convenios">Convenios</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Contrato <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                            </div>
                        </div>

                        <div class="au-form-grid md-3">
                            <div class="form-group">
                                <label>Institución <span class="text-danger">*</span></label>
                                <select name="institucion" id="edit_institucion" class="form-control" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="POLICIA MEBUC">POLICIA MEBUC</option>
                                    <option value="POLICIA DESAN">POLICIA DESAN</option>
                                    <option value="POLICIA DEMAM">POLICIA DEMAM</option>
                                    <option value="EJERCITO NACIONAL">EJERCITO NACIONAL</option>
                                    <option value="ARMADA NACIONAL">ARMADA NACIONAL</option>
                                    <option value="FISCALIA">FISCALIA</option>
                                    <option value="MIGRACION COLOMBIA">MIGRACION COLOMBIA</option>
                                    <option value="INPEC">INPEC</option>
                                    <option value="UNP">UNP</option>
                                    <option value="DEPARTAMENTO DE SANTANDER">DEPARTAMENTO DE SANTANDER</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Provincia / Municipio <span class="text-danger">*</span></label>
                                <div id="edit-municipios-container" class="g360-municipality-builder">
                                    <div class="row-municipio g360-municipality-row">
                                        <div class="g360-municipality-toolbar">
                                            <select class="form-control provincia-select">
                                                <option value="">-- Provincia --</option>
                                            </select>
                                            <button type="button" class="btn btn-success btn-sm btn-add-municipio" title="Agregar otra provincia">
                                                <i class="feather icon-plus"></i> Agregar
                                            </button>
                                        </div>
                                        <select class="municipio-select-hidden" name="municipios[]" multiple style="display:none;"></select>
                                        <div class="municipio-pills d-flex flex-wrap"></div>
                                    </div>
                                </div>
                                <small class="text-muted">Selecciona una provincia, luego haz clic en los municipios para seleccionarlos.</small>
                            </div>
                            <div class="form-group">
                                <label>Dirección <span class="text-danger">*</span></label>
                                <select name="direccion" id="edit_direccion" class="form-control" required>
                                    <option value="Dirección de Seguridad y Convivencia">Dirección de Seguridad y Convivencia</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Valor ($)</label>
                                <input type="text" name="valor" id="edit_valor" class="form-control">
                            </div>
                        </div>

                        <div class="au-form-grid md-3">
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" name="cantidad" id="edit_cantidad" class="form-control" min="0">
                            </div>
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" id="edit_descripcion" rows="5" class="form-control" style="min-height: 120px; resize: vertical;"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Imagen <small class="text-muted">(vacío = conservar actual)</small></label>
                                <input type="file" name="imagen" id="edit_imagen" class="form-control">
                                <div id="edit_previewImagen" class="mt-2"></div>
                            </div>
                        </div>

                        <div class="pt-2 text-right">
                            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Js -->
<!-- Required Js -->
<?php include 'admin/include/gerenic_script.php'; ?>

<script src="assets/js/vendor-all.min.js"></script>
<script src="assets/js/plugins/bootstrap.min.js"></script>
<script type="text/javascript" src="admin/js/departamento.js"></script>
<script src="assets/js/pcoded.min.js"></script>

<script>
var PROVINCIAS_DATA = <?= $provinciasJSON ?>;
</script>

<!-- Select2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- DataTables CSS/JS -->
<link rel="stylesheet" href="./admin/js/datatables/jquery.dataTables.min.css">
<script src="./admin/js/datatables/jquery.dataTables.min.js"></script>



<script src="<?php echo Util::versionar('./admin/js/inversiones.js'); ?>"></script>

</body>

</html>