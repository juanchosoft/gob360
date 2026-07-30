<?php
include './admin/include/head.php';
require './admin/include/generic_classes.php';

$db  = new DbConection();
$pdo = $db->openConect();

$tablaInversion = $db->getTable('tbl_inversion_seguridad');

$municipiosMap = [
    '68001' => 'Bucaramanga',
    '68020' => 'Albania',
    '68077' => 'Barbosa',
    '68081' => 'Barichara',
    '68190' => 'California',
    '68276' => 'Floridablanca',
    '68307' => 'Girón',
    '68397' => 'Piedecuesta',
    '68679' => 'San Gil',
    '68861' => 'Socorro'
];

if (!function_exists('riesgoColor')) {
    function riesgoColor($valor)
    {
        $valor = (float)$valor;
        if ($valor > 100000000000) return '#ff5252';
        if ($valor > 20000000000) return '#ffc107';
        return '#00e676';
    }
}

if (!function_exists('pesos')) {
    function pesos($n)
    {
        return '$' . number_format((float)$n, 0, ',', '.');
    }
}

if (!function_exists('normalizarTipoSeccion')) {
    function normalizarTipoSeccion($texto)
    {
        $texto = trim((string)$texto);
        $texto = mb_strtolower($texto, 'UTF-8');

        $reemplazos = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n'
        ];

        $texto = strtr($texto, $reemplazos);
        $texto = preg_replace('/\s+/', ' ', $texto);

        return $texto;
    }
}

if (!function_exists('obtenerColumnaFechaDisponible')) {
    function obtenerColumnaFechaDisponible(PDO $pdo, string $tabla): ?string
    {
        $candidatas = ['created_at', 'fecha', 'fecha_registro', 'dtcreate', 'created_date', 'fecha_creacion'];

        try {
            $stmt = $pdo->query("DESCRIBE {$tabla}");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $nombres = array_map(static function ($col) {
                return $col['Field'] ?? '';
            }, $columnas);

            foreach ($candidatas as $candidata) {
                if (in_array($candidata, $nombres, true)) {
                    return $candidata;
                }
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }
}

/* =========================================
   FILTRO FECHAS
========================================= */
$dateColumn = obtenerColumnaFechaDisponible($pdo, $tablaInversion);

$fechaDesde = isset($_GET['fecha_desde']) ? trim((string)$_GET['fecha_desde']) : '';
$fechaHasta = isset($_GET['fecha_hasta']) ? trim((string)$_GET['fecha_hasta']) : '';

$whereParts = [];
$params = [];

if ($dateColumn !== null) {
    if ($fechaDesde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
        $whereParts[] = "DATE({$dateColumn}) >= :fecha_desde";
        $params[':fecha_desde'] = $fechaDesde;
    }

    if ($fechaHasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
        $whereParts[] = "DATE({$dateColumn}) <= :fecha_hasta";
        $params[':fecha_hasta'] = $fechaHasta;
    }
}

$whereSql = !empty($whereParts) ? ' WHERE ' . implode(' AND ', $whereParts) : '';

/* =========================
   CONSULTA GENERAL
========================= */
$sql = "
    SELECT 
        tipo_seccion,
        COUNT(*) AS total_registros,
        COALESCE(SUM(valor), 0) AS total_valor
    FROM {$tablaInversion}
    {$whereSql}
    GROUP BY tipo_seccion
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   MUNICIPIOS GRAFICA
========================= */
$whereMunicipios = [];
$paramsMunicipios = [];

if ($dateColumn !== null) {
    if ($fechaDesde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
        $whereMunicipios[] = "DATE({$dateColumn}) >= :fecha_desde";
        $paramsMunicipios[':fecha_desde'] = $fechaDesde;
    }

    if ($fechaHasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
        $whereMunicipios[] = "DATE({$dateColumn}) <= :fecha_hasta";
        $paramsMunicipios[':fecha_hasta'] = $fechaHasta;
    }
}

$whereMunicipios[] = "i.municipio IS NOT NULL";
$whereMunicipios[] = "TRIM(i.municipio) != ''";

$sqlM = "
    SELECT 
        i.municipio,
        COALESCE(SUM(i.valor), 0) AS total,
        COALESCE(NULLIF(TRIM(cau.municipio), ''), NULLIF(TRIM(c.municipio), ''), i.municipio) AS nombre_municipio
    FROM {$tablaInversion} i
    LEFT JOIN " . $db->getTable('tbl_ciudades_accion_unificada') . " cau ON CAST(i.municipio AS CHAR) = CAST(cau.codigo_muncipio AS CHAR)
    LEFT JOIN " . $db->getTable('tbl_ciudades') . " c ON CAST(i.municipio AS CHAR) = CAST(c.codigo_muncipio AS CHAR)
    WHERE " . implode(' AND ', $whereMunicipios) . "
    GROUP BY i.municipio
    ORDER BY total DESC
    LIMIT 10
";
$stmtM = $pdo->prepare($sqlM);
$stmtM->execute($paramsMunicipios);
$dataM = $stmtM->fetchAll(PDO::FETCH_ASSOC);

$dataM = is_array($dataM) ? array_values(array_filter($dataM, function ($m) {
    return isset($m['municipio']) && trim((string)$m['municipio']) !== '';
})) : [];

/* =========================
   INSTITUCIONES GRAFICA
========================= */
require_once './admin/classes/Inversion.php';
$_instResult = Inversion::getByInstitucion([
    'fecha_desde' => $fechaDesde,
    'fecha_hasta' => $fechaHasta,
]);
$dataInst = $_instResult['output']['valid'] ? ($_instResult['output']['response'] ?? []) : [];

$_provResult = Inversion::getByProvincia([
    'fecha_desde' => $fechaDesde,
    'fecha_hasta' => $fechaHasta,
]);
$dataProv = $_provResult['output']['valid'] ? ($_provResult['output']['response'] ?? []) : [];


/* =========================
   CONSULTA TITULOS POR SECCION
========================= */
/* =========================
   CONSULTA TITULO Y DESCRIPCION POR SECCION
========================= */
$detallesPorTipo = [];

try {
    $sqlDetalles = "
        SELECT 
            tipo_seccion,
            titulo,
            descripcion,
            COUNT(*) AS total_items,
            COALESCE(SUM(valor), 0) AS total_valor
        FROM {$tablaInversion}
        {$whereSql}
        WHERE titulo IS NOT NULL
          AND TRIM(titulo) != ''
        GROUP BY tipo_seccion, titulo, descripcion
        ORDER BY tipo_seccion ASC, total_valor DESC, titulo ASC
    ";

    if (!empty($whereParts)) {
        $sqlDetalles = "
            SELECT 
                tipo_seccion,
                titulo,
                descripcion,
                COUNT(*) AS total_items,
                COALESCE(SUM(valor), 0) AS total_valor
            FROM {$tablaInversion}
            {$whereSql}
              AND titulo IS NOT NULL
              AND TRIM(titulo) != ''
            GROUP BY tipo_seccion, titulo, descripcion
            ORDER BY tipo_seccion ASC, total_valor DESC, titulo ASC
        ";
    }

    $stmtDetalles = $pdo->prepare($sqlDetalles);
    $stmtDetalles->execute($params);
    $rowsDetalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rowsDetalles as $rowDetalle) {
        $tipoNormalizado = normalizarTipoSeccion($rowDetalle['tipo_seccion'] ?? '');

        if (!isset($detallesPorTipo[$tipoNormalizado])) {
            $detallesPorTipo[$tipoNormalizado] = [];
        }

        $detallesPorTipo[$tipoNormalizado][] = [
            'titulo'      => (string)($rowDetalle['titulo'] ?? ''),
            'descripcion' => (string)($rowDetalle['descripcion'] ?? ''),
            'total_items' => (int)($rowDetalle['total_items'] ?? 0),
            'total_valor' => (float)($rowDetalle['total_valor'] ?? 0),
        ];
    }
} catch (Throwable $e) {
    $detallesPorTipo = [];
}

/* =========================
   ORGANIZAR DATA
========================= */
$datos = [
    'movilidad'       => ['total' => 0, 'valor' => 0],
    'tecnologia'      => ['total' => 0, 'valor' => 0],
    'proyectos'       => ['total' => 0, 'valor' => 0],
    'intendencia'     => ['total' => 0, 'valor' => 0],
    'infraestructura' => ['total' => 0, 'valor' => 0],
    'convenios'       => ['total' => 0, 'valor' => 0],
    'pagos'           => ['total' => 0, 'valor' => 0],
];

$totalGlobal = 0;
$valorGlobal = 0;

foreach ($data as $row) {
    $tipoOriginal = $row['tipo_seccion'] ?? '';
    $tipo = normalizarTipoSeccion($tipoOriginal);

    $totalFila = (int)($row['total_registros'] ?? 0);
    $valorFila = (float)($row['total_valor'] ?? 0);

    $totalGlobal += $totalFila;
    $valorGlobal += $valorFila;

    if (isset($datos[$tipo])) {
        $datos[$tipo]['total'] += $totalFila;
        $datos[$tipo]['valor'] += $valorFila;
    }
}


$fechaActualizacion = date('Y-m-d H:i');
?>

<link rel="stylesheet" href="assets/css/dashboard_inversion_seguridad_gob360.css">

<body class="dashboard-body gob360-investment-dashboard">
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

                <section class="hero-panel g360-investment-hero" aria-label="Resultados de inversión en seguridad GOB360">
                    <div class="g360-investment-hero__grid">

                        <aside class="g360-investment-brand">
                            <span class="g360-investment-brand__eyebrow">
                                Plataforma institucional
                            </span>

                            <img
                                src="assets/img/gob360l.png"
                                alt="Logo GOB360"
                                class="g360-investment-brand__logo"
                            >

                            <span class="g360-investment-brand__caption">
                                Gestión pública inteligente y territorial
                            </span>

                            <div class="g360-investment-brand__status">
                                <span></span>
                                Analítica disponible
                            </div>
                        </aside>

                        <div class="g360-investment-hero__content">
                            <div class="top-badges">
                                <span class="badge-chip">
                                    <i class="feather icon-shield"></i>
                                    Secretaría del Interior
                                </span>

                                <span class="badge-chip">
                                    <i class="feather icon-map-pin"></i>
                                    Santander · Seguridad Territorial
                                </span>

                                <span class="badge-chip">
                                    <i class="feather icon-clock"></i>
                                    Actualizado: <?= htmlspecialchars($fechaActualizacion, ENT_QUOTES, 'UTF-8') ?>
                                </span>

                                <?php if ($dateColumn !== null): ?>
                                    <span class="badge-chip">
                                        <i class="feather icon-calendar"></i>
                                        Fecha: <?= htmlspecialchars($dateColumn, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="hero-content">
                                <div>
                                    <div class="g360-investment-hero__eyebrow">
                                        <i class="feather icon-trending-up"></i>
                                        Inteligencia financiera y territorial
                                    </div>

                                    <h1 class="hero-title">
                                        Resultados de Inversión en Seguridad
                                    </h1>

                                    <p class="hero-subtitle">
                                        Consolida la inversión, los contratos, las instituciones
                                        beneficiadas, las líneas estratégicas y la distribución
                                        territorial para facilitar la toma de decisiones.
                                    </p>
                                </div>

                                <div class="hero-actions" aria-hidden="true">
                                    <span class="g360-hero-action">
                                        <i class="feather icon-bar-chart-2"></i>
                                        Analítica
                                    </span>

                                    <span class="g360-hero-action">
                                        <i class="feather icon-dollar-sign"></i>
                                        Inversión
                                    </span>

                                    <span class="g360-hero-action">
                                        <i class="feather icon-map"></i>
                                        Territorio
                                    </span>
                                </div>
                            </div>

                            <div class="filter-panel">
                                <div class="g360-filter-heading">
                                    <span class="g360-filter-heading__icon">
                                        <i class="feather icon-filter"></i>
                                    </span>

                                    <div>
                                        <h6>Filtro temporal</h6>
                                        <p>Aplica un rango de fechas a todos los indicadores y gráficos.</p>
                                    </div>
                                </div>

                                <form method="GET" class="filter-grid">
                                    <div class="filter-group">
                                        <label>Columna de fecha detectada</label>
                                        <input
                                            type="text"
                                            class="filter-input"
                                            value="<?= $dateColumn !== null ? htmlspecialchars($dateColumn, ENT_QUOTES, 'UTF-8') : 'No se detectó columna de fecha' ?>"
                                            readonly
                                        >
                                    </div>

                                    <div class="filter-group">
                                        <label for="fecha_desde">Fecha desde</label>
                                        <input
                                            type="date"
                                            id="fecha_desde"
                                            name="fecha_desde"
                                            class="filter-input"
                                            value="<?= htmlspecialchars($fechaDesde, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $dateColumn === null ? 'disabled' : '' ?>
                                        >
                                    </div>

                                    <div class="filter-group">
                                        <label for="fecha_hasta">Fecha hasta</label>
                                        <input
                                            type="date"
                                            id="fecha_hasta"
                                            name="fecha_hasta"
                                            class="filter-input"
                                            value="<?= htmlspecialchars($fechaHasta, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $dateColumn === null ? 'disabled' : '' ?>
                                        >
                                    </div>

                                    <div class="filter-actions">
                                        <button
                                            type="submit"
                                            class="filter-btn apply"
                                            <?= $dateColumn === null ? 'disabled style="opacity:.5;cursor:not-allowed;"' : '' ?>
                                        >
                                            <i class="feather icon-search"></i>
                                            Aplicar
                                        </button>

                                        <a
                                            href="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="filter-btn clear"
                                        >
                                            <i class="feather icon-x-circle"></i>
                                            Limpiar
                                        </a>
                                    </div>

                                    <div class="filter-note">
                                        <?php if ($dateColumn === null): ?>
                                            No se encontró una columna de fecha compatible; el filtro temporal está deshabilitado.
                                        <?php else: ?>
                                            El rango seleccionado actualiza el consolidado, los rankings y las gráficas.
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                            <?php if ($valorGlobal > 100000000000): ?>
                                <div class="alerta-banner alerta-alta">
                                    <span class="g360-alert-icon">
                                        <i class="feather icon-alert-triangle"></i>
                                    </span>

                                    <div>
                                        <strong>Alerta de inversión prioritaria</strong>
                                        <p>El valor consolidado supera los $100.000 millones.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </section>

                <section class="g360-dashboard-section g360-summary-section">
                    <header class="g360-dashboard-section__header">
                        <div class="g360-dashboard-section__heading">
                            <span class="g360-dashboard-section__icon">
                                <i class="feather icon-activity"></i>
                            </span>

                            <div>
                                <span class="g360-dashboard-section__eyebrow">
                                    Resumen ejecutivo
                                </span>
                                <h2>Consolidado de inversión en seguridad</h2>
                                <p>Indicadores generales según el rango de fechas seleccionado.</p>
                            </div>
                        </div>

                        <span class="g360-dashboard-section__badge">
                            <i class="feather icon-database"></i>
                            Datos institucionales
                        </span>
                    </header>

                    <div class="summary-grid">
                    <div class="summary-card">
                        <div class="g360-summary-icon">
                            <i class="feather icon-folder"></i>
                        </div>
                        <div class="label">Proyectos Totales</div>
                        <div class="value counter" data-value="<?= (int)$totalGlobal ?>">0</div>
                        <div class="sub">Total consolidado de registros en seguridad</div>
                    </div>

                    <div class="summary-card">
                        <div class="g360-summary-icon g360-summary-icon--money">
                            <i class="feather icon-dollar-sign"></i>
                        </div>
                        <div class="label">Valor Global</div>
                        <div class="value" style="font-size:28px;">
                            <span class="counter-money" data-value="<?= (float)$valorGlobal ?>">$0</span>
                        </div>
                        <div class="sub">Monto total ejecutado en todas las líneas</div>
                    </div>

                    <div class="summary-card">
                        <div class="g360-summary-icon g360-summary-icon--institution">
                            <i class="feather icon-briefcase"></i>
                        </div>
                        <div class="label">Instituciones beneficiadas</div>
                        <div class="value"><?= number_format(count($dataInst), 0, ',', '.') ?></div>
                        <div class="sub">Entidades con inversión registrada</div>
                    </div>

                    <div class="summary-card">
                        <div class="g360-summary-icon g360-summary-icon--lines">
                            <i class="feather icon-layers"></i>
                        </div>
                        <div class="label">Líneas activas</div>
                        <div class="value">
                            <?= count(array_filter($datos, static function ($item) {
                                return (int)($item['total'] ?? 0) > 0;
                            })) ?>
                        </div>
                        <div class="sub">Componentes estratégicos con registros</div>
                    </div>
                </div>
                </section>

                <?php
                $iconos = [
                    'movilidad'       => '<i class="feather icon-truck"></i>',
                    'tecnologia'      => '<i class="feather icon-monitor"></i>',
                    'proyectos'       => '<i class="feather icon-folder"></i>',
                    'intendencia'     => '<i class="feather icon-shield"></i>',
                    'infraestructura' => '<i class="feather icon-home"></i>',
                    'convenios'       => '<i class="feather icon-link-2"></i>',
                    'pagos'           => '<i class="feather icon-credit-card"></i>'
                ];

                $cardTemplate = function ($tipo, $val) use ($iconos, $detallesPorTipo) {
                    $detallesDeTipo = $detallesPorTipo[$tipo] ?? [];
                    $cantidadTitulos = count($detallesDeTipo);
                    ?>
                    <div class="metric-card">
                        <div class="metric-top">
                            <div>
                                <div class="metric-label">Tipo de sección</div>
                                <h3 class="metric-title"><?= htmlspecialchars(ucfirst($tipo), ENT_QUOTES, 'UTF-8') ?></h3>
                            </div>
                            <div class="metric-icon"><?= $iconos[$tipo] ?? '📌' ?></div>
                        </div>

                        <div class="metric-number counter" data-value="<?= (int)$val['total'] ?>">0</div>
                        <div class="metric-mini">Registros cargados</div>

                        <div class="metric-money">
                            <span class="counter-money" data-value="<?= (float)$val['valor'] ?>">$0</span>
                        </div>

                        <div class="metric-bottom">
                            <span class="metric-status">● Operativo</span>
                            <span class="metric-mini"><?= $cantidadTitulos ?> Descripcion</span>
                        </div>

                        <div class="metric-actions">
                            <button 
                                type="button"
                                class="metric-detail-btn"
                                onclick="abrirModalTitulos('<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>')">
                                <i class="feather icon-file-text"></i>
                                Ver detalle de inversión
                            </button>
                        </div>
                    </div>
                <?php }; ?>

                <section class="g360-dashboard-section g360-strategic-section">
                    <header class="g360-dashboard-section__header">
                        <div class="g360-dashboard-section__heading">
                            <span class="g360-dashboard-section__icon g360-dashboard-section__icon--success">
                                <i class="feather icon-layers"></i>
                            </span>

                            <div>
                                <span class="g360-dashboard-section__eyebrow">
                                    Distribución estratégica
                                </span>
                                <h2>Inversión por línea de seguridad</h2>
                                <p>Registros, montos y detalle contractual por cada componente.</p>
                            </div>
                        </div>

                        <span class="g360-dashboard-section__badge">
                            <i class="feather icon-grid"></i>
                            7 líneas
                        </span>
                    </header>

                <div class="row">
                    <?php foreach (['movilidad','tecnologia','proyectos','intendencia','infraestructura','convenios'] as $tipo): ?>
                        <div class="col-xl-4 col-md-6">
                            <?php $cardTemplate($tipo, $datos[$tipo]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row mt-4">
                    <div class="col-xl-4 col-md-6">
                        <?php $cardTemplate('pagos', $datos['pagos']); ?>
                    </div>
                    <?php if (!empty($dataInst)): 
                        $totalInst = count($dataInst);
                        $topInst = $dataInst[0]['institucion'] ?? '—';
                        $topInstValor = (float)($dataInst[0]['total_valor'] ?? 0);
                    ?>
                    <div class="col-xl-8 col-md-6">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="metric-card g360-mini-metric text-center">
                                    <div class="metric-label" style="font-size:11px;">Instituciones</div>
                                    <div style="font-size:28px;font-weight:1100;color:#fff;margin-top:4px;"><?= $totalInst ?></div>
                                    <div class="metric-mini" style="margin-top:4px;">Beneficiadas</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="metric-card g360-mini-metric text-center">
                                    <div class="metric-label" style="font-size:11px;">Inversión Total</div>
                                    <div style="font-size:20px;font-weight:1100;color:#34d399;margin-top:4px;">$<?= number_format(array_sum(array_column($dataInst, 'total_valor')) > 1000000000 ? array_sum(array_column($dataInst, 'total_valor')) / 1000000000 : array_sum(array_column($dataInst, 'total_valor')) / 1000000, 1, ',', '.') ?><?= array_sum(array_column($dataInst, 'total_valor')) > 1000000000 ? 'B' : 'M' ?></div>
                                    <div class="metric-mini" style="margin-top:4px;">Consolidado</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="metric-card g360-mini-metric text-center">
                                    <div class="metric-label" style="font-size:11px;">Top Institución</div>
                                    <div style="font-size:13px;font-weight:1100;color:#60a5fa;margin-top:4px;word-break:break-word;"><?= htmlspecialchars($topInst, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="metric-mini" style="margin-top:4px;">$<?= number_format($topInstValor > 1000000000 ? $topInstValor / 1000000000 : $topInstValor / 1000000, 1, ',', '.') ?><?= $topInstValor > 1000000000 ? 'B' : 'M' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                </section>

                <?php if (!empty($dataInst)): ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <section class="g360-dashboard-section g360-chart-section">
                            <div class="metric-card g360-chart-card g360-chart-card--wide">
                            <div class="metric-top mb-3">
                                <div>
                                    <div class="metric-label">Gráfica</div>
                                    <h3 class="metric-title">Inversión por Institución Beneficiada</h3>
                                </div>
                                <div class="metric-icon">🏛️</div>
                            </div>
                            <div id="chartInstituciones" class="g360-chart-container g360-chart-container--wide"></div>
                        </div>
                    </div>
                </div>
                </section>
                <?php endif; ?>

                <?php if (!empty($dataProv)): ?>
                <section class="g360-dashboard-section g360-chart-section">
                    <header class="g360-dashboard-section__header">
                        <div class="g360-dashboard-section__heading">
                            <span class="g360-dashboard-section__icon g360-dashboard-section__icon--territory">
                                <i class="feather icon-map"></i>
                            </span>

                            <div>
                                <span class="g360-dashboard-section__eyebrow">
                                    Analítica territorial
                                </span>
                                <h2>Distribución provincial y municipal</h2>
                                <p>Compara la inversión consolidada entre provincias y municipios.</p>
                            </div>
                        </div>
                    </header>

                <div class="row mt-2">
                    <div class="col-12 col-xl-6">
                        <div class="metric-card g360-chart-card">
                            <div class="metric-top mb-3">
                                <div>
                                    <div class="metric-label">Gráfica</div>
                                    <h3 class="metric-title">Inversión por Provincia</h3>
                                </div>
                                <div class="metric-icon">🗺️</div>
                            </div>
                            <div id="chartProvincia" class="g360-chart-container"></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="metric-card g360-chart-card">
                            <div class="metric-top mb-3">
                                <div>
                                    <div class="metric-label">Gráfica</div>
                                    <h3 class="metric-title">Top Municipios por Inversión</h3>
                                </div>
                                <div class="metric-icon">🏙️</div>
                            </div>
                            <div id="chartMunicipios" class="g360-chart-container"></div>
                        </div>
                    </div>
                </div>
                </section>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <div class="modal-overlay-titulos" id="modalTitulosOverlay">
        <div class="modal-titulos-box g360-investment-detail-modal">
            <div class="modal-titulos-header">
                <div>
                    <div class="g360-modal-heading">
                        <span class="g360-modal-heading__icon">
                            <i class="feather icon-file-text"></i>
                        </span>
                        <h3 class="modal-titulos-title" id="modalTitulosTitulo">Títulos relacionados</h3>
                    </div>
                    <div class="modal-titulos-sub" id="modalTitulosSub">Consulta detallada por tipo de sección</div>
                </div>
                <button type="button" class="modal-close-btn" onclick="cerrarModalTitulos()">×</button>
            </div>
            <div class="modal-titulos-body" id="modalTitulosBody">
                <div class="titulo-empty">Cargando información...</div>
            </div>
        </div>
    </div>

    <button id="modoTVBtn" class="g360-tv-button" onclick="activarModoTV()">
        <i class="feather icon-monitor"></i>
        Modo TV
    </button>

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


    <script>

        const detallesPorTipo = <?= json_encode($detallesPorTipo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        function ocultarLoader() {
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.add('hidden');
                setTimeout(() => {
                    loader.style.display = 'none';
                    if (loader.parentNode) {
                        loader.parentNode.removeChild(loader);
                    }
                }, 500);
            }
        }

        function abrirModalTitulos(tipo) {
    const overlay = document.getElementById('modalTitulosOverlay');
    const body = document.getElementById('modalTitulosBody');
    const title = document.getElementById('modalTitulosTitulo');
    const sub = document.getElementById('modalTitulosSub');

    const tipoNormalizado = String(tipo || '').trim().toLowerCase();
    const items = detallesPorTipo[tipoNormalizado] || [];

    title.textContent = 'Detalle de ' + (tipo.charAt(0).toUpperCase() + tipo.slice(1));
    sub.textContent = 'Se encontraron ' + items.length + ' registro(s) asociados con el filtro actual.';

    if (!items.length) {
        body.innerHTML = '<div class="titulo-empty">No hay información relacionada para esta sección con el filtro actual.</div>';
    } else {
        let html = '<div class="titulo-list">';

        items.forEach(item => {
            html += `
                <div class="titulo-item">
                    <div class="titulo-item-top">
                        <h4 class="titulo-item-title">${escapeHtml(item.titulo || '')}</h4>
                        <div class="titulo-badges">
                            <span class="titulo-badge">Registros: ${Number(item.total_items || 0).toLocaleString('es-CO')}</span>
                            <span class="titulo-badge">Valor: $${Number(item.total_valor || 0).toLocaleString('es-CO')}</span>
                        </div>
                    </div>
                    <div class="titulo-item-desc">
                        ${item.descripcion && item.descripcion.trim() !== '' 
                            ? escapeHtml(item.descripcion) 
                            : '<span class="titulo-desc-empty">Sin descripción registrada.</span>'}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        body.innerHTML = html;
    }

    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

        function cerrarModalTitulos() {
            const overlay = document.getElementById('modalTitulosOverlay');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }


        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.progress-fill').forEach(el => {
                const width = el.style.width;
                el.style.width = '0%';
                setTimeout(() => {
                    el.style.width = width;
                }, 250);
            });

            document.querySelectorAll('.counter').forEach(el => {
                const value = Number(el.dataset.value || 0);
                let count = 0;
                const step = value > 0 ? value / 80 : 1;

                const interval = setInterval(() => {
                    count += step;
                    if (count >= value) {
                        count = value;
                        clearInterval(interval);
                    }
                    el.innerText = Math.floor(count).toLocaleString('es-CO');
                }, 20);
            });

            document.querySelectorAll('.counter-money').forEach(el => {
                const value = Number(el.dataset.value || 0);
                let count = 0;
                const step = value > 0 ? value / 80 : 1;

                const interval = setInterval(() => {
                    count += step;
                    if (count >= value) {
                        count = value;
                        clearInterval(interval);
                    }
                    el.innerText = '$' + Math.floor(count).toLocaleString('es-CO');
                }, 20);
            });

            const alerta = document.querySelector('.alerta-alta');
            if (alerta) {
                try {
                    const audio = new Audio('https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg');
                    audio.volume = 0.25;
                    setTimeout(() => {
                        audio.play().catch(() => {});
                    }, 1200);
                } catch (e) {}
            }

            const modalOverlay = document.getElementById('modalTitulosOverlay');
            if (modalOverlay) {
                modalOverlay.addEventListener('click', function (e) {
                    if (e.target === modalOverlay) {
                        cerrarModalTitulos();
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    cerrarModalTitulos();
                }
            });

            setTimeout(ocultarLoader, 700);
        });

        window.addEventListener('load', function () {
            ocultarLoader();
        });

        setTimeout(function () {
            ocultarLoader();
        }, 2500);

        setInterval(() => {
            document.body.style.boxShadow = 'inset 0 0 90px rgba(59,130,246,0.06)';
            setTimeout(() => {
                document.body.style.boxShadow = 'none';
            }, 350);
        }, 4500);

        function activarModoTV() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        }

        window.__instData = <?= json_encode(
            array_map(function ($r) {
                return [
                    'name'  => (string)($r['institucion'] ?? ''),
                    'y'     => (int)($r['total_registros'] ?? 0),
                    'valor' => (float)($r['total_valor'] ?? 0),
                ];
            }, $dataInst),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?>;
        window.__instTotalValor = <?= (float)$valorGlobal ?>;
        window.__provData = <?= json_encode($dataProv, JSON_UNESCAPED_UNICODE) ?>;
        window.__munData = <?= json_encode($dataM, JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="admin/js/inversiones.js?v=<?= filemtime('admin/js/inversiones.js') ?>"></script>
</body>
</html>