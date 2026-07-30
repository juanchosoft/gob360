<?php
include './admin/include/head.php';
include './admin/include/dark_theme.php';

function getUrl()
{
    $port = $_SERVER["SERVER_PORT"];
    $nameServer = $port != "80" ? $_SERVER['SERVER_NAME'] . ":" . $port : $_SERVER['SERVER_NAME'];
    $url = sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $nameServer,
        $_SERVER['REQUEST_URI']
    );
    $final =  str_replace(basename($_SERVER["SCRIPT_FILENAME"], '.php') . ".php", "", $url);
    $exists = strpos($final, "?");
    if ($exists == !false) { // (dejo igual tu lógica)
        $final =  substr($final, 0, $exists);
        return $final;
    } else {
        return $final;
    }
}

require_once './admin/include/generic_classes.php';
include './admin/classes/DesarrolloAlcalde.php';

// Obtener permisos
$permissions = PagePermissions::crudForCurrentPage();

$modulo = 'Metas Plan de Desarrollo - Alcalde';

// filtra POR municipio
$rol_usuario = SessionData::getUserType();
$esAdminDelete = in_array($rol_usuario, ['SuperAdministrador', 'Administrador']);

// Obtener sectores distintos para filtros
try {
    $dbSectores = new DbConection();
    $pdoS = $dbSectores->openConect();
    $tableS = $dbSectores->getTable('tbl_plandesarrollo_alcalde');
    $sectoresPDD = $pdoS->query("SELECT DISTINCT sector_pdd FROM {$tableS} WHERE sector_pdd IS NOT NULL AND sector_pdd != '' ORDER BY sector_pdd")->fetchAll(PDO::FETCH_COLUMN);
    $sectoresCatalogo = $pdoS->query("SELECT DISTINCT sector_catalogo FROM {$tableS} WHERE sector_catalogo IS NOT NULL AND sector_catalogo != '' ORDER BY sector_catalogo")->fetchAll(PDO::FETCH_COLUMN);
    $dbSectores->closeConect();
} catch (Exception $e) {
    $sectoresPDD = [];
    $sectoresCatalogo = [];
}
?>

<link rel="stylesheet" href="assets/css/metas_plan_desarrollo_alcalde_gob360.css">

<body class="gob360-development-goals">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ navigation menu ] start -->
    <?php include './admin/include/navbar.php'; ?>
    <!-- [ navigation menu ] end -->

    <!-- [ Header ] start -->
    <?php include './admin/include/header.php'; ?>
    <!-- [ Header ] end -->

    <!-- Manejo de mensajes de sesión -->
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['success_message'])) {
        echo '<script>Swal.fire({icon: "success", title: "Éxito", text: "' . addslashes($_SESSION['success_message']) . '"});</script>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<script>Swal.fire({icon: "error", title: "Error", text: "' . addslashes($_SESSION['error_message']) . '"});</script>';
        unset($_SESSION['error_message']);
    }
    ?>

    

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!-- HERO (reemplaza la sensación "plana" del breadcrumb) -->
            <section class="g360-goals-hero" aria-label="Metas del Plan de Desarrollo Municipal GOB360">
                <div class="g360-goals-hero__grid">

                    <aside class="g360-goals-brand">
                        <span class="g360-goals-brand__eyebrow">
                            Plataforma institucional
                        </span>

                        <img
                            src="assets/img/gob360l.png"
                            alt="Logo GOB360"
                            class="g360-goals-brand__logo"
                        >

                        <span class="g360-goals-brand__caption">
                            Gestión pública inteligente y territorial
                        </span>

                        <div class="g360-goals-brand__status">
                            <span></span>
                            Módulo municipal activo
                        </div>
                    </aside>

                    <div class="g360-goals-hero__content">
                        <div class="g360-goals-hero__top">
                            <div>
                                <div class="g360-goals-hero__eyebrow">
                                    <i class="feather icon-target"></i>
                                    Alcaldía · Plan de Desarrollo
                                </div>

                                <h1 class="g360-goals-hero__title">
                                    Metas del Plan de Desarrollo
                                </h1>

                                <p class="g360-goals-hero__description">
                                    Administra las metas municipales mediante carga masiva,
                                    filtros por sector, seguimiento anual, responsables y
                                    control de avances desde una sola vista institucional.
                                </p>
                            </div>

                            <div class="g360-goals-back">
                                <?php include './admin/include/btn_back.php'; ?>
                            </div>
                        </div>

                        <div class="g360-goals-summary">
                            <article>
                                <span class="g360-goals-summary__icon">
                                    <i class="feather icon-layers"></i>
                                </span>
                                <div>
                                    <small>Sectores PDD</small>
                                    <strong><?= number_format(count($sectoresPDD), 0, ',', '.') ?></strong>
                                    <p>Clasificaciones disponibles</p>
                                </div>
                            </article>

                            <article>
                                <span class="g360-goals-summary__icon g360-goals-summary__icon--catalog">
                                    <i class="feather icon-grid"></i>
                                </span>
                                <div>
                                    <small>Sectores catálogo</small>
                                    <strong><?= number_format(count($sectoresCatalogo), 0, ',', '.') ?></strong>
                                    <p>Catálogo de productos</p>
                                </div>
                            </article>

                            <article>
                                <span class="g360-goals-summary__icon g360-goals-summary__icon--role">
                                    <i class="feather icon-user-check"></i>
                                </span>
                                <div>
                                    <small>Perfil activo</small>
                                    <strong><?= htmlspecialchars((string)$rol_usuario, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <p>Permisos según rol institucional</p>
                                </div>
                            </article>
                        </div>

                        <div class="g360-goals-capabilities" aria-hidden="true">
                            <span>
                                <i class="feather icon-upload-cloud"></i>
                                Carga masiva
                            </span>

                            <span>
                                <i class="feather icon-filter"></i>
                                Filtros sectoriales
                            </span>

                            <span>
                                <i class="feather icon-trending-up"></i>
                                Seguimiento anual
                            </span>

                            <span>
                                <i class="feather icon-shield"></i>
                                Control administrativo
                            </span>
                        </div>
                    </div>

                </div>
            </section>

            <!-- Sección de carga de archivo Excel -->
            <div class="row">
                <div class="col-12 col-xl-12">
                    <div class="card excel-shell g360-upload-card mb-4">
                        <div class="card-header">
                            <div class="g360-card-heading">
                                <span class="g360-card-heading__icon">
                                    <i class="feather icon-upload-cloud"></i>
                                </span>

                                <div>
                                    <span class="g360-card-heading__eyebrow">
                                        Importación institucional
                                    </span>
                                    <h5>Carga Masiva de Metas</h5>
                                    <p>Sube la plantilla oficial para registrar o actualizar las metas municipales.</p>
                                </div>
                            </div>

                            <span class="g360-card-status">
                                <span></span>
                                Excel habilitado
                            </span>
                        </div>
                        <div class="card-body">
                            <?php
                            if ($rol_usuario == 'Alcalde' || $rol_usuario == 'Auxiliar' || $rol_usuario == 'Auxiliar_Alcalde' || $rol_usuario === 'SuperAdministrador' || $rol_usuario === 'Gobernador' || $rol_usuario === 'Secretario_Gobernacion') {
                            ?>
                                <form id="formExcelPlan" name="formExcelPlan" method="post" enctype="multipart/form-data"
                                    action="admin/controllers/planDesarrolloAlcaldeCtrl.php?method=uploadExcel">

                                    <div class="g360-upload-intro">
                                        <span class="g360-upload-intro__icon">
                                            <i class="feather icon-file-text"></i>
                                        </span>

                                        <div>
                                            <h6>Selecciona el archivo del Plan de Desarrollo</h6>
                                            <p>
                                                El sistema procesará el archivo y reportará registros insertados,
                                                omitidos o con errores de estructura.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="excelFile" class="form-label">
                                            Subir archivo de Excel <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="file" id="excelFile" name="file" accept=".xlsx,.xls" required />
                                        <div class="g360-field-help">
                                            <i class="feather icon-info"></i>
                                            Usa la plantilla oficial para evitar errores de estructura.
                                        </div>
                                    </div>

<!--                                     <div class="mb-3">
                                        <label for="replace_mode" style="display:flex; align-items:flex-start; gap:10px; background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.35); border-radius:8px; padding:12px 14px; cursor:pointer; margin:0;">
                                            <input type="checkbox" id="replace_mode" name="replace_mode" value="1" style="flex-shrink:0; margin-top:2px; width:16px; height:16px; cursor:pointer;" />
                                            <div>
                                                <div style="color:rgba(255,255,255,.92); font-weight:700; font-size:13px;">
                                                    <i class="feather icon-alert-triangle mr-1" style="color:#f87171;"></i>
                                                    Reemplazar datos existentes
                                                </div>
                                                <div style="color:rgba(255,200,200,.75); font-size:11px; margin-top:3px;">
                                                    Si marcas esta opción, se eliminarán todas las metas actuales del municipio antes de cargar el nuevo archivo.
                                                </div>
                                            </div>
                                        </label>
                                    </div> -->

                                    <div class="g360-upload-actions">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="feather icon-upload-cloud"></i>
                                            Subir y procesar
                                        </button>
                                        <a href="admin/controllers/planDesarrolloAlcaldeCtrl.php?method=downloadTemplate" class="btn btn-secondary px-4">
                                            <i class="feather icon-download"></i>
                                            Descargar plantilla
                                        </a>
                                    </div>
                                </form>
                            <?php } else { ?>
                                <div class="g360-permission-notice">
                                    <span class="g360-permission-notice__icon">
                                        <i class="feather icon-lock"></i>
                                    </span>

                                    <div>
                                        <strong>Carga masiva restringida</strong>
                                        <p>Tu rol puede consultar las metas y aplicar filtros, pero no cargar archivos.</p>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de datos -->
            <div class="row">
                <div class="col-xl-12 col-md-12">
                    <div class="card table-card g360-goals-table-card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="g360-card-heading">
                                <span class="g360-card-heading__icon g360-card-heading__icon--table">
                                    <i class="feather icon-target"></i>
                                </span>

                                <div>
                                    <span class="g360-card-heading__eyebrow">
                                        Seguimiento municipal
                                    </span>
                                    <h5>Metas del Plan de Desarrollo</h5>
                                    <p>Consulta sectores, productos, avances anuales y responsables.</p>
                                </div>
                            </div>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-horizontal"></i>
                                    </button>
                                    <ul class="list-unstyled card-option dropdown-menu dropdown-menu-right">
                                        <li class="dropdown-item full-card"><a href="#!"><span><i class="feather icon-maximize"></i> maximize</span><span style="display:none"><i class="feather icon-minimize"></i> Restore</span></a></li>
                                        <li class="dropdown-item minimize-card"><a href="#!"><span><i class="feather icon-minus"></i> collapse</span><span style="display:none"><i class="feather icon-plus"></i> expand</span></a></li>
                                        <li class="dropdown-item reload-card"><a href="#!"><i class="feather icon-refresh-cw"></i> reload</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            <!-- ✅ FILTROS POR SECTOR (MEJOR DISEÑO, MISMO JS/IDs) -->
                            <div class="filters-wrap mb-3">
                                <div class="filters-title">
                                    <span class="filters-title__icon">
                                        <i class="feather icon-filter"></i>
                                    </span>

                                    <div>
                                        <span class="filters-title__eyebrow">Consulta segmentada</span>
                                        <h6>Filtros por Sector</h6>
                                        <p>
                                            Aplica filtros exactos por <strong>Sector PDD</strong>
                                            y <strong>Sector Catálogo</strong>.
                                        </p>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-md-5">
                                        <div class="filter-card">
                                            <div class="filter-label">Sector PDD</div>
                                            <select id="filtroSectorPDD" class="form-control filter-select">
                                                <option value="">Todos</option>
                                                <?php foreach ($sectoresPDD as $s): ?>
                                                <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-5">
                                        <div class="filter-card">
                                            <div class="filter-label">Sector Catálogo</div>
                                            <select id="filtroSectorCatalogo" class="form-control filter-select">
                                                <option value="">Todos</option>
                                                <?php foreach ($sectoresCatalogo as $s): ?>
                                                <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-2 d-grid">
                                        <button type="button" id="btnLimpiarFiltrosSector" class="btn btn-outline-brutal">
                                            <i class="feather icon-x-circle"></i>
                                            Limpiar
                                        </button>
                                    </div>

                                    <div class="col-12">
                                        <div class="button-stack button-stack--filters mt-2">
                                            <button type="button" id="btnAplicarAmbos" class="btn btn-primary btn-brutal">
                                                <i class="feather icon-check-circle"></i>
                                                Aplicar ambos
                                            </button>
                                            <button type="button" id="btnSoloPDD" class="btn btn-success btn-brutal">
                                                <i class="feather icon-layers"></i>
                                                Solo PDD
                                            </button>
                                            <button type="button" id="btnSoloCatalogo" class="btn btn-info btn-brutal">
                                                <i class="feather icon-grid"></i>
                                                Solo catálogo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TABLA CENTRADA + BLANCA -->
                            <div class="table-wrap">
                                <div class="table-shell">
                                    <div class="table-shell__top">
                                        <div>
                                            <div class="table-shell__eyebrow">Matriz de seguimiento</div>
                                            <h3 class="table-shell__title">Consolidado de metas municipales</h3>
                                            <div class="table-shell__subtitle">
                                                Revisa ejes, sectores, productos, avances por vigencia
                                                y secretarías responsables.
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-shell__badge">
                                                <i class="feather icon-shield"></i>
                                                GOB360
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-shell__body">
                                    <div class="table-responsive table-responsive--premium p-0">
                                        <?php if ($esAdminDelete): ?>
                                        <div class="g360-admin-actions">
                                            <button type="button" id="btnEliminarSeleccionados" class="btn btn-danger btn-sm">
                                                <i class="feather icon-trash-2"></i>
                                                Eliminar seleccionados
                                                <span class="g360-selected-counter" id="contSeleccionados">0</span>
                                            </button>
                                            <small>
                                                Selecciona los registros que deseas retirar de la matriz.
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                        <table id="dynamictable" class="table table-hover table-bordered table-sm w-100">
                                            <thead>
                                                <tr>
                                                    <?php if ($esAdminDelete): ?>
                                                        <th class="g360-checkbox-column">
                                                            <input type="checkbox" id="chkTodos" title="Seleccionar todos">
                                                        </th>
                                                    <?php endif; ?>
                                                    <th>ID</th>
                                                    <th>EJE ESTRATÉGICO</th>
                                                    <th>SECTOR PDD</th>
                                                    <th>SECTOR CATÁLOGO DE PRODUCTOS</th>
                                                    <th>PRODUCTO, BIEN O SERVICIO PDD</th>
                                                    <th>2024</th>
                                                    <th>AVANCE 2024</th>
                                                    <th>AVANCE 2025</th>
                                                    <th>2025</th>
                                                    <th>2026</th>
                                                    <th>2027</th>
                                                    <th>SECRETARÍA RESPONSABLE</th>
                                                    <?php if ($esAdminDelete): ?>
                                                        <th>Acciones</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- card-body -->
                    </div><!-- card -->
                </div>
            </div>

        </div>
    </div>

    <?php include './admin/include/footer.php'; ?>
    <?php include 'admin/include/gerenic_script.php'; ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="./admin/js/datatables/jquery.dataTables.min.css">
    <script src="./admin/js/datatables/jquery.dataTables.min.js"></script>
    

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="<?php echo Util::versionar('./admin/js/control-plan-desarrollo-alcalde.js'); ?>"></script>

    <!-- Filtros: recargan la tabla vía AJAX con los valores seleccionados -->
    <script>
    $(function() {
        var esAdminDel = <?= json_encode($esAdminDelete) ?>;
        var colOffset = esAdminDel ? 1 : 0;

        $(document).on('filtrosCambiados', function() {
            if ($.fn.dataTable && $.fn.dataTable.isDataTable('#dynamictable')) {
                $('#dynamictable').DataTable().ajax.reload();
            }
        });

        function aplicarFiltros() {
            $(document).trigger('filtrosCambiados');
        }

        $('#filtroSectorPDD, #filtroSectorCatalogo').on('change', aplicarFiltros);
        $('#btnAplicarAmbos').on('click', aplicarFiltros);
        $('#btnSoloPDD').on('click', function() {
            $('#filtroSectorCatalogo').val('');
            aplicarFiltros();
        });
        $('#btnSoloCatalogo').on('click', function() {
            $('#filtroSectorPDD').val('');
            aplicarFiltros();
        });
        $('#btnLimpiarFiltrosSector').on('click', function() {
            $('#filtroSectorPDD').val('');
            $('#filtroSectorCatalogo').val('');
            if ($.fn.dataTable && $.fn.dataTable.isDataTable('#dynamictable')) {
                $('#dynamictable').DataTable().search('').ajax.reload();
            }
        });
    });
    </script>

    <script>
        $(document).ready(function() {
            $('#formExcelPlan').on('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Cargando datos...',
                    text: 'Estamos procesando el archivo Excel.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                var formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.output && response.output.valid) {

                            let inserted = response.output.inserted;
                            let skipped = response.output.skipped;
                            let errors = response.output.errors || [];

                            let htmlContent = `Se han insertado <b>${inserted}</b> registros.<br>`;
                            if (skipped > 0) {
                                htmlContent += `<small>Se omitieron ${skipped} filas (ejemplos, incompletas o inválidas).</small><br>`;
                            }
                            if (errors.length > 0) {
                                htmlContent += `<small>Errores encontrados:<br>${errors.join('<br>')}</small>`;
                            }

                            if (errors.length > 0 || skipped > 0) {
                                Swal.fire({
                                    title: '¡Resultados del procesamiento de datos!',
                                    html: htmlContent,
                                    icon: 'warning',
                                    confirmButtonText: 'Aceptar'
                                }).then((result) => {
                                    if (result.isConfirmed) window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: '¡Proceso exitoso!',
                                    html: htmlContent,
                                    icon: 'success',
                                    confirmButtonText: 'Aceptar'
                                }).then((result) => {
                                    if (result.isConfirmed) window.location.reload();
                                });
                            }
                        } else {
                            Swal.fire('Error', (response.output && response.output.message) ? response.output.message : 'Error desconocido al procesar', 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error("Respuesta bruta del servidor:", xhr.responseText);
                        Swal.fire('Error de Formato', 'Error desconocido al procesar...', 'error');
                    }
                });
            });
        });
    </script>

</body>
</html>
