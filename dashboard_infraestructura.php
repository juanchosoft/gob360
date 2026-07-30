<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$db  = new DbConection();
$pdo = $db->openConect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function tableName($db, $name)
{
    try {
        return $db->getTable($name);
    } catch (Throwable $e) {
        return $name;
    }
}

$tblIndicadores = tableName($db, 'tbl_infra_indicadores');
$tblInversion   = tableName($db, 'tbl_infra_inversion');
$tblProyectos   = tableName($db, 'tbl_infra_proyectos_estrategicos');
$tblRegistros   = tableName($db, 'tbl_infra_registros');

function moneyCOP($value)
{
    if ($value === null || $value === '') {
        return '$0';
    }
    return '$' . number_format((float)$value, 0, ',', '.');
}

function numFormat($value, $decimals = 0)
{
    if ($value === null || $value === '') {
        return '0';
    }
    return number_format((float)$value, $decimals, ',', '.');
}

function safe($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function percentValue($value)
{
    return number_format(((float)$value) * 100, 1, ',', '.') . '%';
}

/* ============================================================
   CREACION DE TABLAS
============================================================ */

$pdo->exec("
CREATE TABLE IF NOT EXISTS {$tblIndicadores} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    titulo VARCHAR(180) NOT NULL,
    valor_numerico DECIMAL(20,4) NULL,
    valor_texto VARCHAR(120) NULL,
    unidad VARCHAR(80) NULL,
    descripcion TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS {$tblInversion} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bloque VARCHAR(160) NOT NULL,
    categoria VARCHAR(220) NOT NULL,
    medida_label VARCHAR(80) NULL,
    medida_valor DECIMAL(20,4) NULL,
    municipios INT NULL,
    recurso_total DECIMAL(20,2) NULL,
    recurso_2024 DECIMAL(20,2) NULL,
    recurso_2025 DECIMAL(20,2) NULL,
    observacion VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS {$tblProyectos} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(180) NOT NULL,
    estado VARCHAR(80) NOT NULL DEFAULT 'En estructuracion',
    porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
    municipio VARCHAR(160) NULL,
    responsable VARCHAR(160) NULL,
    valor DECIMAL(20,2) NULL,
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS {$tblRegistros} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(120) NOT NULL,
    categoria VARCHAR(180) NOT NULL,
    nombre VARCHAR(220) NOT NULL,
    municipio VARCHAR(160) NULL,
    valor DECIMAL(20,2) NULL,
    avance DECIMAL(5,2) NULL,
    fecha_inicio DATE NULL,
    fecha_corte DATE NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/* ============================================================
   SEED INICIAL SEGUN EXCEL
============================================================ */

$countIndicadores = (int)$pdo->query("SELECT COUNT(*) FROM {$tblIndicadores}")->fetchColumn();

if ($countIndicadores === 0) {
    $indicadores = [
        ['proyectos_radicados_2024', 'Proyectos radicados 2024', 204, null, 'registros', 'Número de proyectos radicados durante la vigencia 2024.'],
        ['proyectos_radicados_2025', 'Proyectos radicados 2025', 268, null, 'registros', 'Número de proyectos radicados durante la vigencia 2025.'],
        ['proyectos_radicados_2026', 'Proyectos radicados 2026', 91, null, 'registros', 'Número de proyectos radicados durante la vigencia 2026.'],
        ['proyectos_radicados_total', 'Total proyectos radicados', 563, null, 'registros', 'Total consolidado de proyectos radicados.'],

        ['proyectos_viabilizados_2024', 'Proyectos viabilizados 2024', 77, null, 'registros', 'Número de proyectos viabilizados durante la vigencia 2024.'],
        ['proyectos_viabilizados_2025', 'Proyectos viabilizados 2025', 187, null, 'registros', 'Número de proyectos viabilizados durante la vigencia 2025.'],
        ['proyectos_viabilizados_2026', 'Proyectos viabilizados 2026', 56, null, 'registros', 'Número de proyectos viabilizados durante la vigencia 2026.'],
        ['proyectos_viabilizados_total', 'Total proyectos viabilizados', 320, null, 'registros', 'Total consolidado de proyectos viabilizados.'],

        ['contratos_ejecucion_marzo_2026', 'Contratos en ejecución a marzo 2026', 417, null, 'contratos', 'Contratos en ejecución reportados a marzo de 2026.'],
        ['seguimiento_plan_desarrollo', 'Seguimiento Plan de Desarrollo', 0.4461, null, 'porcentaje', 'Avance del seguimiento del Plan de Desarrollo a marzo de 2026.'],

        ['fallecidos_2025', 'Personas fallecidas 2025', 95, null, 'personas', 'Cifra de seguridad vial reportada para 2025.'],
        ['fallecidos_2026', 'Personas fallecidas 2026', 118, null, 'personas', 'Cifra de seguridad vial reportada para 2026.'],
        ['lesionados_2026', 'Personas lesionadas 2026', 287, null, 'personas', 'Número de personas lesionadas reportadas para 2026.'],
        ['lesionados_hombres_2026', 'Porcentaje hombres lesionados 2026', 0.61, null, 'porcentaje', 'Distribución porcentual de hombres lesionados.'],
        ['lesionados_mujeres_2026', 'Porcentaje mujeres lesionadas 2026', 0.39, null, 'porcentaje', 'Distribución porcentual de mujeres lesionadas.'],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO {$tblIndicadores}
        (codigo, titulo, valor_numerico, valor_texto, unidad, descripcion)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($indicadores as $row) {
        $stmt->execute($row);
    }
}

$countInversion = (int)$pdo->query("SELECT COUNT(*) FROM {$tblInversion}")->fetchColumn();

if ($countInversion === 0) {
    $inversiones = [
        ['Inversión en vías del departamento', 'Vía primaria', 'KMS', 3.7, 5, 33722812963.72, 15361406481.86, 18361406481.86, null],
        ['Inversión en vías del departamento', 'Vía secundaria', 'KMS', 621.4, 44, 190280132163.08, 71477733553.90, 118802398609.18, null],
        ['Inversión en vías del departamento', 'Vía terciaria', 'KMS', 3011.37, 50, 53855795790.04, 12933438169.43, 40922357620.61, null],
        ['Inversión en vías del departamento', 'Vía urbana', 'KMS', 2.88, 5, 2216608211.26, 0, 2216608211.26, null],

        ['Inversión en puentes del departamento', 'Puentes en vía secundaria intervenidos', 'Cantidad', 5, 5, 3611257061, 3497555951, 113701110, null],
        ['Inversión en puentes del departamento', 'Puentes en vía terciaria intervenidos', 'Cantidad', 2, 2, 4708701621.68, 2867796069.34, 1840905552.34, null],

        ['Inversión área mineroenergética', 'Electrificación', 'Beneficiarios', 3304, 13, 29160201926, 18460383274.92, 10699818651.08, null],
        ['Inversión área mineroenergética', 'Gasificación', 'Beneficiarios', 3780, 9, 4486755151, 0, 4486755151, null],

        ['Inversión en maquinaria', 'Maquinaria amarilla', 'Kits', 6, null, 33000000000, null, null, '33 mil millones'],

        ['Inversión estudios y diseños', 'Agua', 'Cantidad', 16, null, 1423125822.59, null, 1423125822.59, null],
        ['Inversión estudios y diseños', 'Equipamiento', 'Cantidad', 2, null, 7867330537.76, 157262182.62, 7710068355.14, null],
        ['Inversión estudios y diseños', 'Vías', 'Cantidad', 6, null, 2848257263.88, 1810842797, 1037414466.88, null],
        ['Inversión estudios y diseños', 'Aeropuerto', 'Cantidad', 1, null, null, null, null, 'Gestión'],

        ['Inversión cultural', 'Infraestructura deportiva', 'Cantidad', 5, 5, 17462245204.33, 6931939061.71, 10530306142.62, null],
        ['Inversión cultural', 'Parques intervenidos', 'Cantidad', 4, 4, 725576445.14, 0, 725576445.14, null],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO {$tblInversion}
        (bloque, categoria, medida_label, medida_valor, municipios, recurso_total, recurso_2024, recurso_2025, observacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($inversiones as $row) {
        $stmt->execute($row);
    }
}

$countProyectos = (int)$pdo->query("SELECT COUNT(*) FROM {$tblProyectos}")->fetchColumn();

if ($countProyectos === 0) {
    $proyectos = [
        ['Anillo vial', 'En seguimiento', 35, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico de conectividad territorial.'],
        ['CEO', 'En estructuración', 20, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico priorizado para seguimiento institucional.'],
        ['Maquinaria', 'En ejecución', 55, null, 'Secretaría de Infraestructura', 33000000000, null, null, 'Fortalecimiento del banco de maquinaria amarilla.'],
        ['Topocoro', 'En formulación', 18, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico asociado a desarrollo territorial.'],
        ['Carmen Yarima', 'En seguimiento', 30, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico con impacto regional.'],
        ['Onzama', 'En formulación', 15, null, 'Secretaría de Infraestructura', null, null, null, 'Proyecto estratégico priorizado.'],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO {$tblProyectos}
        (nombre, estado, porcentaje, municipio, responsable, valor, fecha_inicio, fecha_fin, descripcion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($proyectos as $row) {
        $stmt->execute($row);
    }
}

/* ============================================================
   GUARDAR FORMULARIO
============================================================ */

$alertMessage = '';
$alertType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_registro_infra'])) {
    $tipo         = trim($_POST['tipo'] ?? '');
    $categoria    = trim($_POST['categoria'] ?? '');
    $nombre       = trim($_POST['nombre'] ?? '');
    $municipio    = trim($_POST['municipio'] ?? '');
    $valor        = str_replace(['.', ',', '$', ' '], ['', '.', '', ''], $_POST['valor'] ?? '');
    $avance       = str_replace(',', '.', $_POST['avance'] ?? '');
    $fechaInicio  = $_POST['fecha_inicio'] ?? null;
    $fechaCorte   = $_POST['fecha_corte'] ?? null;
    $descripcion  = trim($_POST['descripcion'] ?? '');

    if ($tipo !== '' && $categoria !== '' && $nombre !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO {$tblRegistros}
            (tipo, categoria, nombre, municipio, valor, avance, fecha_inicio, fecha_corte, descripcion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $tipo,
            $categoria,
            $nombre,
            $municipio,
            $valor !== '' ? $valor : null,
            $avance !== '' ? $avance : null,
            $fechaInicio ?: null,
            $fechaCorte ?: null,
            $descripcion
        ]);

        $alertMessage = 'Registro guardado correctamente para la Secretaría de Infraestructura.';
        $alertType = 'success';
    } else {
        $alertMessage = 'Debe completar tipo, categoría y nombre del registro.';
        $alertType = 'error';
    }
}

/* ============================================================
   FILTROS
============================================================ */

$fechaDesde = $_GET['fecha_desde'] ?? '';
$fechaHasta = $_GET['fecha_hasta'] ?? '';

$whereRegistros = [];
$paramsRegistros = [];

if ($fechaDesde !== '') {
    $whereRegistros[] = "DATE(created_at) >= ?";
    $paramsRegistros[] = $fechaDesde;
}

if ($fechaHasta !== '') {
    $whereRegistros[] = "DATE(created_at) <= ?";
    $paramsRegistros[] = $fechaHasta;
}

$whereSQL = count($whereRegistros) ? 'WHERE ' . implode(' AND ', $whereRegistros) : '';

/* ============================================================
   CONSULTAS DASHBOARD
============================================================ */

$indicadoresRaw = $pdo->query("SELECT * FROM {$tblIndicadores}")->fetchAll(PDO::FETCH_ASSOC);
$indicadores = [];

foreach ($indicadoresRaw as $item) {
    $indicadores[$item['codigo']] = $item;
}

$totalInversionBase = (float)$pdo->query("SELECT COALESCE(SUM(recurso_total),0) FROM {$tblInversion}")->fetchColumn();

$stmtTotalReg = $pdo->prepare("SELECT COUNT(*) FROM {$tblRegistros} {$whereSQL}");
$stmtTotalReg->execute($paramsRegistros);
$totalRegistrosUsuario = (int)$stmtTotalReg->fetchColumn();

$stmtValorReg = $pdo->prepare("SELECT COALESCE(SUM(valor),0) FROM {$tblRegistros} {$whereSQL}");
$stmtValorReg->execute($paramsRegistros);
$totalValorRegistrosUsuario = (float)$stmtValorReg->fetchColumn();

$totalGeneral = $totalInversionBase + $totalValorRegistrosUsuario;

$bloques = $pdo->query("
    SELECT 
        bloque,
        COUNT(*) AS total_items,
        COALESCE(SUM(recurso_total),0) AS total_recurso,
        COALESCE(SUM(recurso_2024),0) AS total_2024,
        COALESCE(SUM(recurso_2025),0) AS total_2025,
        COALESCE(SUM(medida_valor),0) AS total_medida,
        COALESCE(SUM(municipios),0) AS total_municipios
    FROM {$tblInversion}
    GROUP BY bloque
    ORDER BY total_recurso DESC
")->fetchAll(PDO::FETCH_ASSOC);

$inversionDetalle = $pdo->query("
    SELECT *
    FROM {$tblInversion}
    ORDER BY bloque ASC, recurso_total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$proyectos = $pdo->query("
    SELECT *
    FROM {$tblProyectos}
    ORDER BY porcentaje DESC, nombre ASC
")->fetchAll(PDO::FETCH_ASSOC);

$stmtUltimos = $pdo->prepare("
    SELECT *
    FROM {$tblRegistros}
    {$whereSQL}
    ORDER BY created_at DESC
    LIMIT 10
");
$stmtUltimos->execute($paramsRegistros);
$ultimosRegistros = $stmtUltimos->fetchAll(PDO::FETCH_ASSOC);

$chartBloques = [];
$chartValores = [];
$chartCategorias = [];
$chartCategoriaValores = [];

foreach ($bloques as $b) {
    $chartBloques[] = $b['bloque'];
    $chartValores[] = (float)$b['total_recurso'];
}

foreach ($inversionDetalle as $d) {
    $chartCategorias[] = $d['categoria'];
    $chartCategoriaValores[] = (float)$d['recurso_total'];
}

$fechaActualizacion = date('Y-m-d H:i');
?>

<link href="assets/css/dashboard_infraestructura_gob360.css" rel="stylesheet">

<body class="dashboard-body gob360-infra-dashboard">
    <div class="loader-bg" id="pageLoader">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="dashboard-wrap">

                <section class="hero-panel">
                    <div class="g360-infra-hero-grid">

                        <div class="g360-infra-brand">
                            <img
                                src="assets/img/gob360l.png"
                                alt="Logo GOB360"
                                class="g360-infra-logo"
                            >
                            <p>Gestión pública inteligente y territorial</p>
                        </div>

                        <div class="g360-infra-hero-content">
                            <div class="top-badges">
                                <span class="badge-chip">
                                    <i class="feather icon-briefcase"></i>
                                    Secretaría de Infraestructura
                                </span>

                                <span class="badge-chip">
                                    <i class="feather icon-map-pin"></i>
                                    Santander
                                </span>

                                <span class="badge-chip">
                                    <i class="feather icon-clock"></i>
                                    Actualizado: <?php echo safe($fechaActualizacion); ?>
                                </span>
                            </div>

                            <div class="hero-content">
                                <div>
                                    <span class="g360-infra-eyebrow">
                                        <i class="feather icon-trending-up"></i>
                                        Analítica e inversión territorial
                                    </span>

                                    <h1 class="hero-title">
                                        Dashboard de Infraestructura
                                    </h1>

                                    <p class="hero-subtitle">
                                        Visualiza proyectos radicados y viabilizados,
                                        contratos en ejecución, inversiones por líneas
                                        estratégicas, seguridad vial y proyectos priorizados.
                                    </p>
                                </div>
                            </div>

                            <div class="hero-actions">
                                <button
                                    type="button"
                                    class="hero-btn primary"
                                    onclick="scrollToSection('seccionDashboard')"
                                >
                                    <i class="feather icon-bar-chart-2"></i>
                                    Dashboard
                                </button>

                                <button
                                    type="button"
                                    class="hero-btn success"
                                    onclick="scrollToSection('seccionFormulario')"
                                >
                                    <i class="feather icon-plus-circle"></i>
                                    Registrar
                                </button>

                                <button
                                    type="button"
                                    class="hero-btn warning"
                                    onclick="scrollToSection('seccionProyectos')"
                                >
                                    <i class="feather icon-map"></i>
                                    Proyectos
                                </button>

                                <button
                                    type="button"
                                    class="hero-btn dark"
                                    id="modoTVBtn"
                                >
                                    <i class="feather icon-monitor"></i>
                                    Modo TV
                                </button>
                            </div>

                            <form class="filter-panel" method="GET">
                                <div class="g360-filter-heading">
                                    <span>
                                        <i class="feather icon-filter"></i>
                                    </span>

                                    <div>
                                        <strong>Filtro temporal</strong>
                                        <small>Aplica el rango a los registros agregados por los usuarios.</small>
                                    </div>
                                </div>

                                <div class="filter-grid">
                                    <div class="filter-group">
                                        <label for="fecha_desde">Fecha desde</label>
                                        <input
                                            type="date"
                                            id="fecha_desde"
                                            name="fecha_desde"
                                            class="filter-input"
                                            value="<?php echo safe($fechaDesde); ?>"
                                        >
                                    </div>

                                    <div class="filter-group">
                                        <label for="fecha_hasta">Fecha hasta</label>
                                        <input
                                            type="date"
                                            id="fecha_hasta"
                                            name="fecha_hasta"
                                            class="filter-input"
                                            value="<?php echo safe($fechaHasta); ?>"
                                        >
                                    </div>

                                    <button type="submit" class="filter-btn apply">
                                        <i class="feather icon-search"></i>
                                        Aplicar
                                    </button>

                                    <a
                                        href="<?php echo safe(basename($_SERVER['PHP_SELF'])); ?>"
                                        class="filter-btn clear"
                                    >
                                        <i class="feather icon-x-circle"></i>
                                        Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>

                    </div>
                </section>

                <section id="seccionDashboard" class="summary-grid">
                    <div class="summary-card">
                        <div class="label">Proyectos radicados</div>
                        <div class="value"><?php echo numFormat($indicadores['proyectos_radicados_total']['valor_numerico'] ?? 0); ?></div>
                        <div class="sub">Total consolidado 2024, 2025 y 2026.</div>
                    </div>

                    <div class="summary-card">
                        <div class="label">Proyectos viabilizados</div>
                        <div class="value"><?php echo numFormat($indicadores['proyectos_viabilizados_total']['valor_numerico'] ?? 0); ?></div>
                        <div class="sub">Proyectos con viabilidad reportada.</div>
                    </div>

                    <div class="summary-card">
                        <div class="label">Contratos en ejecución</div>
                        <div class="value"><?php echo numFormat($indicadores['contratos_ejecucion_marzo_2026']['valor_numerico'] ?? 0); ?></div>
                        <div class="sub">Corte a marzo de 2026.</div>
                    </div>

                    <div class="summary-card">
                        <div class="label">Inversión consolidada</div>
                        <div class="value"><?php echo moneyCOP($totalGeneral); ?></div>
                        <div class="sub">Incluye base Excel y registros nuevos filtrados.</div>
                    </div>
                </section>

                <section class="dashboard-grid">
                    <?php
                    $icons = ['🛣️', '🌉', '⚡', '🚜', '📐', '🏟️'];
                    $i = 0;
                    foreach ($bloques as $bloque):
                        $icon = $icons[$i % count($icons)];
                        $i++;
                    ?>
                        <article class="metric-card">
                            <div class="metric-top">
                                <div>
                                    <div class="metric-label">Línea estratégica</div>
                                    <h3 class="metric-title"><?php echo safe($bloque['bloque']); ?></h3>
                                </div>
                                <div class="metric-icon"><?php echo $icon; ?></div>
                            </div>

                            <div class="metric-number"><?php echo numFormat($bloque['total_items']); ?></div>
                            <p class="metric-money"><?php echo moneyCOP($bloque['total_recurso']); ?></p>

                            <div class="metric-bottom">
                                <span class="metric-status">● Operativo</span>
                                <span class="metric-mini">
                                    <?php echo numFormat($bloque['total_municipios']); ?> municipios
                                </span>
                            </div>

                            <button
                                type="button"
                                class="metric-detail-btn"
                                onclick="openInfraModal('<?php echo safe($bloque['bloque']); ?>')">
                                <i class="feather icon-file-text"></i> Ver detalle de inversión
                            </button>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="two-col">
                    <div class="section-card">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">Inversión por línea estratégica</h2>
                                <p class="section-desc">Distribución de recursos reportados para infraestructura.</p>
                            </div>
                            <span class="section-tag">Recurso total</span>
                        </div>

                        <div class="chart-wrap" id="chartBloques"></div>
                    </div>

                    <div class="section-card">
                        <div class="section-header">
                            <div>
                                <h2 class="section-title">Plan de Desarrollo</h2>
                                <p class="section-desc">Avance consolidado a marzo de 2026.</p>
                            </div>
                            <span class="section-tag"><?php echo percentValue($indicadores['seguimiento_plan_desarrollo']['valor_numerico'] ?? 0); ?></span>
                        </div>

                        <div class="summary-card" style="margin-bottom:14px;">
                            <div class="label">Avance</div>
                            <div class="value"><?php echo percentValue($indicadores['seguimiento_plan_desarrollo']['valor_numerico'] ?? 0); ?></div>
                            <div class="sub">Seguimiento Plan de Desarrollo Secretaría de Infraestructura.</div>
                        </div>

                        <div class="summary-card">
                            <div class="label">Seguridad vial 2026</div>
                            <div class="value"><?php echo numFormat($indicadores['lesionados_2026']['valor_numerico'] ?? 0); ?></div>
                            <div class="sub">
                                Lesionados: hombres <?php echo percentValue($indicadores['lesionados_hombres_2026']['valor_numerico'] ?? 0); ?> /
                                mujeres <?php echo percentValue($indicadores['lesionados_mujeres_2026']['valor_numerico'] ?? 0); ?>.
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Detalle de inversión por categoría</h2>
                            <p class="section-desc">Ranking por categoría, recursos y cobertura municipal.</p>
                        </div>
                        <span class="section-tag"><?php echo count($inversionDetalle); ?> registros base</span>
                    </div>

                    <div class="chart-wrap" id="chartCategorias"></div>
                </section>

                <section id="seccionProyectos" class="section-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Proyectos estratégicos priorizados</h2>
                            <p class="section-desc">
                                Seguimiento de avance, responsable, municipio e inversión de los proyectos estratégicos.
                            </p>
                        </div>

                        <span class="section-tag">
                            <?php echo count($proyectos); ?> proyectos
                        </span>
                    </div>

                    <?php if (count($proyectos) > 0): ?>
                        <div class="projects-grid">
                            <?php foreach ($proyectos as $proyecto): ?>
                                <?php
                                $avanceProyecto = max(0, min(100, (float)$proyecto['porcentaje']));
                                ?>
                                <article class="project-card">
                                    <div class="g360-project-top">
                                        <span class="g360-project-icon">
                                            <i class="feather icon-map"></i>
                                        </span>

                                        <span class="pill">
                                            <?php echo safe($proyecto['estado']); ?>
                                        </span>
                                    </div>

                                    <h3 class="project-title">
                                        <?php echo safe($proyecto['nombre']); ?>
                                    </h3>

                                    <div class="project-meta">
                                        <span>
                                            <i class="feather icon-user"></i>
                                            <?php echo safe($proyecto['responsable'] ?: 'Sin responsable'); ?>
                                        </span>

                                        <span>
                                            <i class="feather icon-map-pin"></i>
                                            <?php echo safe($proyecto['municipio'] ?: 'Cobertura departamental'); ?>
                                        </span>

                                        <span>
                                            <i class="feather icon-dollar-sign"></i>
                                            <?php echo $proyecto['valor'] !== null ? moneyCOP($proyecto['valor']) : 'Valor por definir'; ?>
                                        </span>
                                    </div>

                                    <div class="g360-project-progress-info">
                                        <span>Avance del proyecto</span>
                                        <strong><?php echo numFormat($avanceProyecto, 1); ?>%</strong>
                                    </div>

                                    <div class="progress-line">
                                        <div
                                            class="progress-fill"
                                            style="width:<?php echo $avanceProyecto; ?>%;"
                                        ></div>
                                    </div>

                                    <?php if (!empty($proyecto['descripcion'])): ?>
                                        <p class="g360-project-description">
                                            <?php echo safe($proyecto['descripcion']); ?>
                                        </p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            No hay proyectos estratégicos registrados.
                        </div>
                    <?php endif; ?>
                </section>

                <section id="seccionFormulario" class="section-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Formulario de registro infraestructura</h2>
                            <p class="section-desc">Registra nuevos proyectos, avances, inversiones, contratos o reportes de seguimiento.</p>
                        </div>
                        <span class="section-tag">Registro dinámico</span>
                    </div>

                    <form method="POST" class="form-grid" autocomplete="off">
                        <input type="hidden" name="guardar_registro_infra" value="1">

                        <div class="form-group">
                            <label>Tipo de registro</label>
                            <select name="tipo" class="form-control-wow" required>
                                <option value="">Seleccione</option>
                                <option value="Proyecto">Proyecto</option>
                                <option value="Contrato">Contrato</option>
                                <option value="Inversión">Inversión</option>
                                <option value="Seguimiento">Seguimiento</option>
                                <option value="Seguridad vial">Seguridad vial</option>
                                <option value="Estudios y diseños">Estudios y diseños</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="categoria" class="form-control-wow" required>
                                <option value="">Seleccione</option>
                                <option value="Vías">Vías</option>
                                <option value="Puentes">Puentes</option>
                                <option value="Mineroenergética">Mineroenergética</option>
                                <option value="Maquinaria">Maquinaria</option>
                                <option value="Estudios y diseños">Estudios y diseños</option>
                                <option value="Infraestructura deportiva">Infraestructura deportiva</option>
                                <option value="Parques">Parques</option>
                                <option value="Proyecto estratégico">Proyecto estratégico</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nombre del registro</label>
                            <input type="text" name="nombre" class="form-control-wow" placeholder="Ej: Mejoramiento vía terciaria" required>
                        </div>

                        <div class="form-group">
                            <label>Municipio</label>
                            <input type="text" name="municipio" class="form-control-wow" placeholder="Ej: Bucaramanga">
                        </div>

                        <div class="form-group">
                            <label>Valor inversión</label>
                            <input type="text" name="valor" class="form-control-wow money-input" placeholder="Ej: 150000000">
                        </div>

                        <div class="form-group">
                            <label>Avance %</label>
                            <input type="number" name="avance" class="form-control-wow" min="0" max="100" step="0.01" placeholder="Ej: 45">
                        </div>

                        <div class="form-group">
                            <label>Fecha inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control-wow">
                        </div>

                        <div class="form-group">
                            <label>Fecha corte / cumplimiento</label>
                            <input type="date" name="fecha_corte" class="form-control-wow">
                        </div>

                        <div class="form-group">
                            <label>Acción</label>
                            <button type="submit" class="submit-wow"><i class="feather icon-save"></i> Guardar registro</button>
                        </div>

                        <div class="form-group full">
                            <label>Descripción / observaciones</label>
                            <textarea name="descripcion" class="form-control-wow" placeholder="Describe el avance, estado, impacto, gestión realizada o información relevante."></textarea>
                        </div>
                    </form>
                </section>

                <section class="section-card">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">Últimos registros cargados</h2>
                            <p class="section-desc">Registros agregados desde el formulario de infraestructura.</p>
                        </div>
                        <span class="section-tag"><?php echo numFormat($totalRegistrosUsuario); ?> registros filtrados</span>
                    </div>

                    <?php if (count($ultimosRegistros) > 0): ?>
                        <div class="table-responsive-wow">
                            <table class="table-wow">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Categoría</th>
                                        <th>Nombre</th>
                                        <th>Municipio</th>
                                        <th>Valor</th>
                                        <th>Avance</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimosRegistros as $r): ?>
                                        <tr>
                                            <td><?php echo safe(date('Y-m-d', strtotime($r['created_at']))); ?></td>
                                            <td><span class="pill"><?php echo safe($r['tipo']); ?></span></td>
                                            <td><?php echo safe($r['categoria']); ?></td>
                                            <td><strong style="color:#fff;"><?php echo safe($r['nombre']); ?></strong></td>
                                            <td><?php echo safe($r['municipio'] ?: 'No registra'); ?></td>
                                            <td><?php echo moneyCOP($r['valor']); ?></td>
                                            <td><?php echo $r['avance'] !== null ? numFormat($r['avance'], 1) . '%' : '0%'; ?></td>
                                            <td><?php echo safe($r['descripcion']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            Aún no hay registros nuevos cargados con los filtros actuales.
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </div>
    </div>

    <div class="modal-infra" id="modalInfra">
        <div class="modal-box-infra">
            <div class="modal-head-infra">
                <div>
                    <h3 class="modal-title-infra" id="modalInfraTitle">Detalle inversión</h3>
                    <div class="modal-sub-infra">Información consolidada según línea estratégica.</div>
                </div>
                <button type="button" class="modal-close-infra" onclick="closeInfraModal()">×</button>
            </div>

            <div class="modal-body-infra">
                <div class="detail-list" id="modalInfraBody"></div>
            </div>
        </div>
    </div>

    <?php
    if (file_exists('./admin/include/gerenic_footer.php')) {
        include './admin/include/gerenic_footer.php';
    }

    if (file_exists('./admin/include/gerenic_script.php')) {
        include './admin/include/gerenic_script.php';
    }
    ?>

    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const infraDetalle = <?php echo json_encode($inversionDetalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const chartBloquesLabels = <?php echo json_encode($chartBloques, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const chartBloquesValores = <?php echo json_encode($chartValores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const chartCategoriasLabels = <?php echo json_encode($chartCategorias, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const chartCategoriasValores = <?php echo json_encode($chartCategoriaValores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        function formatCOP(value) {
            value = Number(value || 0);
            return '$' + value.toLocaleString('es-CO', {
                maximumFractionDigits: 0
            });
        }

        function scrollToSection(id) {
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function openInfraModal(bloque) {
            const modal = document.getElementById('modalInfra');
            const title = document.getElementById('modalInfraTitle');
            const body = document.getElementById('modalInfraBody');

            title.textContent = bloque;
            const items = infraDetalle.filter(item => item.bloque === bloque);

            if (!items.length) {
                body.innerHTML = `<div class="empty-state">No hay información registrada para esta línea.</div>`;
            } else {
                body.innerHTML = items.map(item => `
                    <article class="detail-item">
                        <h4 class="detail-title">${item.categoria || 'Sin categoría'}</h4>

                        <div class="detail-grid">
                            <div class="detail-kpi">
                                <span>${item.medida_label || 'Medida'}</span>
                                <strong>${Number(item.medida_valor || 0).toLocaleString('es-CO')}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Municipios</span>
                                <strong>${Number(item.municipios || 0).toLocaleString('es-CO')}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Recurso total</span>
                                <strong>${formatCOP(item.recurso_total)}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Observación</span>
                                <strong>${item.observacion || 'Sin observación'}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Recurso 2024</span>
                                <strong>${formatCOP(item.recurso_2024)}</strong>
                            </div>

                            <div class="detail-kpi">
                                <span>Recurso 2025</span>
                                <strong>${formatCOP(item.recurso_2025)}</strong>
                            </div>
                        </div>
                    </article>
                `).join('');
            }

            modal.classList.add('active');
        }

        function closeInfraModal() {
            document.getElementById('modalInfra').classList.remove('active');
        }

        document.getElementById('modalInfra').addEventListener('click', function(e) {
            if (e.target === this) {
                closeInfraModal();
            }
        });

        document.getElementById('modoTVBtn').addEventListener('click', function() {
            const elem = document.documentElement;
            if (!document.fullscreenElement) {
                elem.requestFullscreen?.();
            } else {
                document.exitFullscreen?.();
            }
        });

        document.querySelectorAll('.money-input').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^\d]/g, '');
            });
        });

        window.addEventListener('load', function() {
            setTimeout(function() {
                const loader = document.getElementById('pageLoader');
                if (loader) loader.classList.add('hidden');
            }, 450);
        });

       if (typeof Highcharts !== 'undefined') {

    Highcharts.setOptions({
        chart: {
            backgroundColor: 'transparent',
            style: {
                fontFamily: 'Inter, Arial, sans-serif'
            }
        },
        title: {
            style: {
                color: '#100735',
                fontWeight: '900'
            }
        },
        subtitle: {
            style: {
                color: '#100735'
            }
        },
        xAxis: {
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '700',
                    textOutline: 'none'
                }
            },
            title: {
                style: {
                    color: '#100735',
                    fontWeight: '800'
                }
            },
            lineColor: 'rgba(255,255,255,.22)',
            tickColor: 'rgba(255,255,255,.22)',
            gridLineColor: 'rgba(255,255,255,.08)'
        },
        yAxis: {
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '700',
                    textOutline: 'none'
                }
            },
            title: {
                style: {
                    color: '#100735',
                    fontWeight: '800'
                }
            },
            gridLineColor: 'rgba(255,255,255,.10)'
        },
        legend: {
            itemStyle: {
                color: '#100735',
                fontWeight: '800'
            },
            itemHoverStyle: {
                color: '#100735'
            }
        },
        tooltip: {
            backgroundColor: 'rgba(15,23,42,.98)',
            borderColor: 'rgba(56,189,248,.45)',
            borderRadius: 14,
            shadow: true,
            style: {
                color: '#100735',
                fontSize: '13px'
            }
        },
        credits: {
            enabled: false
        }
    });

    Highcharts.chart('chartBloques', {
        chart: {
            type: 'column',
            backgroundColor: 'transparent'
        },
        title: {
            text: null
        },
        xAxis: {
            categories: chartBloquesLabels,
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '800',
                    textOutline: 'none'
                }
            }
        },
        yAxis: {
            title: {
                text: 'Recursos',
                style: {
                    color: '#100735',
                    fontSize: '13px',
                    fontWeight: '900'
                }
            },
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '800',
                    textOutline: 'none'
                },
                formatter: function() {
                    return '$' + Highcharts.numberFormat(this.value / 1000000000, 0) + ' MM';
                }
            }
        },
        legend: {
            enabled: false
        },
        tooltip: {
            formatter: function() {
                return `
                    <div style="color:#fff;">
                        <b style="font-size:14px;">${this.x}</b><br>
                        Recurso: <b style="color:#38bdf8;">${formatCOP(this.y)}</b>
                    </div>
                `;
            }
        },
        plotOptions: {
            column: {
                borderRadius: 10,
                borderWidth: 0,
                colorByPoint: true,
                dataLabels: {
                    enabled: true,
                    inside: false,
                    style: {
                        color: '#100735',
                        fontSize: '11px',
                        fontWeight: '900',
                        textOutline: '2px contrast'
                    },
                    formatter: function() {
                        return '$' + Highcharts.numberFormat(this.y / 1000000000, 0) + ' MM';
                    }
                }
            }
        },
        series: [{
            name: 'Recurso',
            data: chartBloquesValores
        }]
    });

    Highcharts.chart('chartCategorias', {
        chart: {
            type: 'bar',
            backgroundColor: 'transparent',
            spacingLeft: 10,
            spacingRight: 25,
            spacingTop: 10,
            spacingBottom: 18
        },
        title: {
            text: null
        },
        xAxis: {
            categories: chartCategoriasLabels,
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '900',
                    textOutline: 'none'
                }
            },
            lineColor: 'rgba(255,255,255,.25)',
            tickColor: 'rgba(255,255,255,.25)'
        },
        yAxis: {
            title: {
                text: 'Recurso total',
                style: {
                    color: '#100735',
                    fontSize: '13px',
                    fontWeight: '900'
                }
            },
            labels: {
                style: {
                    color: '#100735',
                    fontSize: '12px',
                    fontWeight: '800',
                    textOutline: 'none'
                },
                formatter: function() {
                    return '$' + Highcharts.numberFormat(this.value / 1000000000, 0) + ' MM';
                }
            },
            gridLineColor: 'rgba(255,255,255,.12)'
        },
        legend: {
            enabled: false
        },
        tooltip: {
            formatter: function() {
                return `
                    <div style="color:#100735;">
                        <b style="font-size:14px;">${this.x}</b><br>
                        Recurso: <b style="color:#100735;">${formatCOP(this.y)}</b>
                    </div>
                `;
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 8,
                borderWidth: 0,
                colorByPoint: true,
                dataLabels: {
                    enabled: true,
                    align: 'right',
                    inside: false,
                    style: {
                        color: '#100735',
                        fontSize: '11px',
                        fontWeight: '900',
                        textOutline: '2px contrast'
                    },
                    formatter: function() {
                        return '$' + Highcharts.numberFormat(this.y / 1000000000, 0) + ' MM';
                    }
                }
            }
        },
        series: [{
            name: 'Recurso',
            data: chartCategoriasValores
        }]
    });
}

        <?php if ($alertMessage !== ''): ?>
        Swal.fire({
            icon: '<?php echo $alertType; ?>',
            title: '<?php echo $alertType === 'success' ? 'Registro exitoso' : 'Atención'; ?>',
            text: '<?php echo safe($alertMessage); ?>',
            background: '#071426',
            color: '#ffffff',
            confirmButtonColor: '#26d8ff',
            confirmButtonText: 'Entendido'
        });
        <?php endif; ?>
    </script>
</body>
</html>