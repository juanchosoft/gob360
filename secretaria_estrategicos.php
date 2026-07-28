<?php
require './admin/include/generic_classes.php';

/*
|--------------------------------------------------------------------------
| PERMISOS
|--------------------------------------------------------------------------
*/

extract(PagePermissions::crudVarsForCurrentPage());

$userType = SessionData::getUserType();
$isAdmin  = ($userType === Util::Administrador() || $userType === Util::SuperAdministrador());

$isUsuarioMunicipal = $isUsuarioMunicipal ?? false;

$isSecretarioGobernacion = ($userType === Util::Secretaria_Despacho_Gobernacion() || $userType === Util::Auxiliar_secret_gob());

if (!$isAdmin && !$isUsuarioMunicipal && !$isSecretarioGobernacion) {
    require 'permiso_denegado.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| CONEXIÓN BD (vía DbConection)
|--------------------------------------------------------------------------
*/

if (!function_exists('seproj_get_pdo')) {
    function seproj_get_pdo(): PDO
    {
        static $pdo = null;
        if ($pdo === null) {
            $db = new DbConection();
            $pdo = $db->openConect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return $pdo;
    }
}

/*
|--------------------------------------------------------------------------
| FUNCIONES PROPIAS DE ESTA VISTA
|--------------------------------------------------------------------------
*/

if (!function_exists('seproj_h')) {
    function seproj_h($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('seproj_money')) {
    function seproj_money($value)
    {
        $value = (float)$value;

        if ($value <= 0) {
            return '$ 0';
        }

        return '$ ' . number_format($value, 0, ',', '.');
    }
}

if (!function_exists('seproj_clean_decimal')) {
    function seproj_clean_decimal($value)
    {
        $value = (string)$value;
        $value = str_replace(['$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float)$value : 0;
    }
}

if (!function_exists('seproj_create_csrf')) {
    function seproj_create_csrf()
    {
        if (empty($_SESSION['seproj_csrf_token'])) {
            $_SESSION['seproj_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['seproj_csrf_token'];
    }
}

if (!function_exists('seproj_verify_csrf')) {
    function seproj_verify_csrf($token)
    {
        return isset($_SESSION['seproj_csrf_token']) && hash_equals($_SESSION['seproj_csrf_token'], (string)$token);
    }
}

if (!function_exists('seproj_db_all')) {
    function seproj_db_all($sql, $params = [])
    {
        $pdo = seproj_get_pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('seproj_db_one')) {
    function seproj_db_one($sql, $params = [])
    {
        $rows = seproj_db_all($sql, $params);
        return $rows[0] ?? null;
    }
}

if (!function_exists('seproj_db_execute')) {
    function seproj_db_execute($sql, $params = [])
    {
        $pdo = seproj_get_pdo();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

if (!function_exists('seproj_get_galeria')) {
    function seproj_get_galeria($proyecto_id)
    {
        return seproj_db_all("
            SELECT id, imagen_url, fecha_avance, orden
            FROM au_proyectos_imagenes
            WHERE proyecto_id = :pid
            ORDER BY orden ASC, id ASC
        ", [':pid' => $proyecto_id]);
    }
}

/*
|--------------------------------------------------------------------------
| CREAR TABLA SI NO EXISTE
|--------------------------------------------------------------------------
*/

seproj_db_execute("
    CREATE TABLE IF NOT EXISTS au_proyectos_estrategicos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(180) NOT NULL,
        subtitulo VARCHAR(220) NULL,
        categoria VARCHAR(80) NULL DEFAULT 'Infraestructura',
        imagen_url VARCHAR(500) NULL,

        valor_proyecto DECIMAL(18,2) NULL DEFAULT 0,
        valor_requerido_nacion DECIMAL(18,2) NULL DEFAULT 0,
        valor_aportado_gobernacion DECIMAL(18,2) NULL DEFAULT 0,
        valor_aportado_nacion DECIMAL(18,2) NULL DEFAULT 0,
        aporte_valorizacion DECIMAL(18,2) NULL DEFAULT 0,

        unidad_valor VARCHAR(80) NULL DEFAULT 'millones de pesos',
        ministerio VARCHAR(180) NULL,
        entidad VARCHAR(180) NULL,

        tarea_clave TEXT NULL,
        poblacion_beneficiada VARCHAR(180) NULL,
        tiempo VARCHAR(180) NULL,
        estado VARCHAR(80) NULL DEFAULT 'En ejecución',
        impacto VARCHAR(80) NULL DEFAULT 'Alto',
        avance DECIMAL(5,2) NULL DEFAULT 0,

        observaciones TEXT NULL,
        color_hex VARCHAR(20) NULL DEFAULT '#1f5aa6',
        activo TINYINT(1) NOT NULL DEFAULT 1,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

seproj_db_execute("
    CREATE TABLE IF NOT EXISTS au_proyectos_imagenes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        proyecto_id INT NOT NULL,
        imagen_url VARCHAR(500) NOT NULL,
        fecha_avance DATE NULL,
        orden INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (proyecto_id) REFERENCES au_proyectos_estrategicos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

/*
|--------------------------------------------------------------------------
| ACTUALIZAR PROYECTO
|--------------------------------------------------------------------------
*/

$csrf = seproj_create_csrf();
$mensajeOk = '';
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_project') {
    try {
        if (!seproj_verify_csrf($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de seguridad inválido. Recarga la página e intenta nuevamente.');
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new Exception('Proyecto inválido.');
        }

        // Procesar imagen principal si se subió un archivo
        $imagenUrl = trim($_POST['imagen_url'] ?? '');
        if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/assets/img/proyectos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['imagen_principal']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                throw new Exception('Formato de imagen principal no permitido.');
            }
            if ($_FILES['imagen_principal']['size'] > 10 * 1024 * 1024) {
                throw new Exception('La imagen principal supera el tamaño máximo (10MB).');
            }
            $imgName = 'proyecto_' . $id . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $uploadDir . $imgName);
            $imagenUrl = 'assets/img/proyectos/' . $imgName;
        }

        seproj_db_execute("
            UPDATE au_proyectos_estrategicos SET
                nombre = :nombre,
                subtitulo = :subtitulo,
                categoria = :categoria,
                imagen_url = :imagen_url,
                valor_proyecto = :valor_proyecto,
                valor_requerido_nacion = :valor_requerido_nacion,
                valor_aportado_gobernacion = :valor_aportado_gobernacion,
                valor_aportado_nacion = :valor_aportado_nacion,
                aporte_valorizacion = :aporte_valorizacion,
                unidad_valor = :unidad_valor,
                ministerio = :ministerio,
                entidad = :entidad,
                tarea_clave = :tarea_clave,
                poblacion_beneficiada = :poblacion_beneficiada,
                tiempo = :tiempo,
                estado = :estado,
                impacto = :impacto,
                avance = :avance,
                observaciones = :observaciones,
                activo = :activo
            WHERE id = :id
            LIMIT 1
        ", [
            ':nombre' => trim($_POST['nombre'] ?? ''),
            ':subtitulo' => trim($_POST['subtitulo'] ?? ''),
            ':categoria' => trim($_POST['categoria'] ?? ''),
            ':imagen_url' => $imagenUrl,
            ':valor_proyecto' => seproj_clean_decimal($_POST['valor_proyecto'] ?? 0),
            ':valor_requerido_nacion' => seproj_clean_decimal($_POST['valor_requerido_nacion'] ?? 0),
            ':valor_aportado_gobernacion' => seproj_clean_decimal($_POST['valor_aportado_gobernacion'] ?? 0),
            ':valor_aportado_nacion' => seproj_clean_decimal($_POST['valor_aportado_nacion'] ?? 0),
            ':aporte_valorizacion' => seproj_clean_decimal($_POST['aporte_valorizacion'] ?? 0),
            ':unidad_valor' => trim($_POST['unidad_valor'] ?? ''),
            ':ministerio' => trim($_POST['ministerio'] ?? ''),
            ':entidad' => trim($_POST['entidad'] ?? ''),
            ':tarea_clave' => trim($_POST['tarea_clave'] ?? ''),
            ':poblacion_beneficiada' => trim($_POST['poblacion_beneficiada'] ?? ''),
            ':tiempo' => trim($_POST['tiempo'] ?? ''),
            ':estado' => trim($_POST['estado'] ?? ''),
            ':impacto' => trim($_POST['impacto'] ?? ''),
            ':avance' => seproj_clean_decimal($_POST['avance'] ?? 0),
            ':observaciones' => trim($_POST['observaciones'] ?? ''),
            ':activo' => isset($_POST['activo']) ? 1 : 0,
            ':id' => $id,
        ]);

        header('Location: ' . $_SERVER['PHP_SELF'] . '?proyecto_id=' . $id . '&ok=1');
        exit;
    } catch (Throwable $e) {
        $mensajeError = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

if (isset($_GET['ok']) && $_GET['ok'] == '1') {
    $mensajeOk = 'Proyecto actualizado correctamente.';
}

/*
|--------------------------------------------------------------------------
| CONSULTAS
|--------------------------------------------------------------------------
*/

$resumen = seproj_db_one("
    SELECT 
        COUNT(*) AS total_proyectos,
        SUM(CASE WHEN estado LIKE '%ejecución%' OR estado LIKE '%ejecucion%' THEN 1 ELSE 0 END) AS en_ejecucion,
        SUM(CASE WHEN impacto LIKE '%alto%' THEN 1 ELSE 0 END) AS impacto_alto,
        SUM(valor_proyecto) AS inversion_total,
        AVG(avance) AS avance_promedio
    FROM au_proyectos_estrategicos
    WHERE activo = 1
");

$proyectos = seproj_db_all("
    SELECT *
    FROM au_proyectos_estrategicos
    WHERE activo = 1
    ORDER BY id ASC
");

$selectedId = isset($_GET['proyecto_id']) ? (int)$_GET['proyecto_id'] : 0;

if ($selectedId <= 0 && !empty($proyectos)) {
    $selectedId = (int)$proyectos[0]['id'];
}

$proyecto = null;

if ($selectedId > 0) {
    $proyecto = seproj_db_one("
        SELECT *
        FROM au_proyectos_estrategicos
        WHERE id = :id
        LIMIT 1
    ", [
        ':id' => $selectedId
    ]);
}

if (!$proyecto && !empty($proyectos)) {
    $proyecto = $proyectos[0];
    $selectedId = (int)$proyecto['id'];
}

$slides = [];
if ($selectedId > 0 && $proyecto) {
    $galeria = seproj_get_galeria($selectedId);
    $slides = $galeria;
    if (!empty($proyecto['imagen_url'])) {
        array_unshift($slides, [
            'id' => 0,
            'imagen_url' => $proyecto['imagen_url'],
            'fecha_avance' => null,
            'orden' => -1
        ]);
    }
}

include './admin/include/head.php';
?>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

<style>
    :root {
        --au-navy: #080d19;
        --au-panel: #111827;
        --au-panel-2: #171f35;
        --au-card: rgba(18, 26, 43, .94);
        --au-card-2: rgba(25, 34, 58, .96);
        --au-blue: #244f97;
        --au-blue-2: #315fc0;
        --au-purple: #6b4bd8;
        --au-green: #20b06f;
        --au-gold: #d7a927;
        --au-red: #e44c61;
        --au-cyan: #39bdf8;
        --au-text: #f4f7fb;
        --au-text-2: #dbe6f5;
        --au-muted: #9eb0c7;
        --au-border: rgba(255,255,255,.12);
        --au-border-strong: rgba(105, 133, 255, .32);
        --au-shadow: 0 20px 60px rgba(0,0,0,.34);
        --au-soft-shadow: 0 12px 28px rgba(0,0,0,.22);
    }

    html, body { overflow-x: hidden; }

    body {
        background:
            radial-gradient(circle at 22% 15%, rgba(32, 176, 111, .18), transparent 23%),
            radial-gradient(circle at 88% 20%, rgba(107, 75, 216, .24), transparent 28%),
            radial-gradient(circle at 55% 90%, rgba(36, 79, 151, .22), transparent 30%),
            linear-gradient(135deg, #070b14 0%, #0b1120 48%, #060914 100%) !important;
        color: var(--au-text);
    }

    .pcoded-content, .pcoded-inner-content, .main-body, .page-wrapper {
        background: transparent !important;
    }

    .se-page {
        padding: 14px 14px 28px;
        color: var(--au-text);
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    .se-toolbar {
        min-height: 76px;
        border-radius: 22px;
        padding: 15px 18px;
        margin-bottom: 12px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 14px;
        align-items: center;
        background:
            linear-gradient(135deg, rgba(15, 23, 42, .98), rgba(17, 24, 39, .94)),
            radial-gradient(circle at 78% 32%, rgba(107,75,216,.32), transparent 29%),
            radial-gradient(circle at 34% 10%, rgba(32,176,111,.22), transparent 28%);
        color: #fff;
        box-shadow: var(--au-shadow);
        overflow: hidden;
        position: relative;
        border: 1px solid var(--au-border);
    }

    .se-toolbar::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 3px;
        background: linear-gradient(90deg, var(--au-blue-2), var(--au-green), var(--au-purple), var(--au-gold));
    }

    .se-toolbar::after {
        content: "";
        position: absolute;
        right: -70px;
        top: -110px;
        width: 240px;
        height: 240px;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        pointer-events: none;
    }

    .se-toolbar-main, .se-toolbar-actions { position: relative; z-index: 2; }

    .se-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 10px;
        border-radius: 999px;
        background: rgba(255,255,255,.09);
        border: 1px solid rgba(255,255,255,.12);
        color: var(--au-text-2);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .se-kicker span {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: var(--au-green);
        box-shadow: 0 0 0 4px rgba(32,176,111,.18);
    }

    .se-toolbar h1 {
        margin: 7px 0 3px;
        color: #fff;
        font-size: clamp(22px, 2.3vw, 34px);
        font-weight: 950;
        letter-spacing: -.8px;
        line-height: 1;
    }

    .se-toolbar p {
        margin: 0;
        color: var(--au-muted);
        font-size: 12px;
        line-height: 1.25;
    }

    .se-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .se-btn {
        border: 0;
        border-radius: 14px;
        min-height: 42px;
        padding: 10px 15px;
        font-weight: 950;
        cursor: pointer;
        transition: .22s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none !important;
        white-space: nowrap;
        line-height: 1;
    }

    .se-btn:hover { transform: translateY(-1px); filter: brightness(1.04); }

    .se-btn-primary {
        color: #fff !important;
        background: linear-gradient(135deg, var(--au-blue-2), var(--au-purple));
        border: 1px solid rgba(255,255,255,.14);
        box-shadow: 0 13px 25px rgba(72, 90, 220, .26);
    }

    .se-btn-blue {
        color: #fff !important;
        background: linear-gradient(135deg, #1f3f84, #225fb5);
        border: 1px solid rgba(255,255,255,.12);
        box-shadow: 0 13px 25px rgba(36, 79, 151, .22);
    }

    .se-btn-soft {
        color: #fff !important;
        background: rgba(255,255,255,.09);
        border: 1px solid rgba(255,255,255,.17);
    }

    .se-filter-line {
        margin-bottom: 12px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: center;
        padding: 10px;
        border-radius: 18px;
        background: rgba(17, 24, 39, .82);
        border: 1px solid var(--au-border);
        box-shadow: var(--au-soft-shadow);
        backdrop-filter: blur(10px);
    }

    .se-select {
        width: 100%;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(8, 13, 25, .78);
        color: #fff !important;
        border-radius: 14px;
        padding: 12px 14px;
        font-weight: 850;
        outline: none;
        min-height: 44px;
    }
    .se-select option { color: #101828; background: #fff; }

    .se-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .se-mini-card {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(19, 27, 45, .96), rgba(31, 38, 64, .92));
        border: 1px solid var(--au-border);
        box-shadow: var(--au-soft-shadow);
        border-radius: 18px;
        padding: 13px 15px;
        min-height: 76px;
    }

    .se-mini-card::after {
        content: "";
        position: absolute;
        width: 95px;
        height: 95px;
        right: -40px;
        bottom: -44px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(49, 95, 192, .22), rgba(107, 75, 216, .20));
    }

    .se-mini-card small {
        display: block;
        color: var(--au-muted);
        font-weight: 900;
        font-size: 11px;
        margin-bottom: 7px;
        position: relative;
        z-index: 2;
    }

    .se-mini-card strong {
        display: block;
        color: #fff;
        font-size: clamp(19px, 2vw, 24px);
        line-height: 1;
        font-weight: 950;
        position: relative;
        z-index: 2;
        letter-spacing: -.3px;
        text-shadow: 0 1px 0 rgba(0,0,0,.18);
    }

    .se-main-grid { display: block; }

    .se-project-sheet {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background:
            radial-gradient(circle at 85% 8%, rgba(107,75,216,.15), transparent 30%),
            radial-gradient(circle at 12% 18%, rgba(32,176,111,.10), transparent 24%),
            linear-gradient(135deg, rgba(15, 23, 42, .98), rgba(21, 30, 50, .96));
        border: 1px solid var(--au-border-strong);
        box-shadow: var(--au-shadow);
        padding: 18px 18px 18px 26px;
        min-height: auto;
    }

    .se-lines {
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 42px;
        pointer-events: none;
    }

    .se-lines span { position: absolute; top: 0; bottom: 0; width: 4px; border-radius: 999px; }
    .se-lines span:nth-child(1) { left: 0; background: var(--au-blue-2); }
    .se-lines span:nth-child(2) { left: 10px; background: var(--au-gold); }
    .se-lines span:nth-child(3) { left: 20px; background: var(--au-green); }
    .se-lines span:nth-child(4) { left: 30px; background: var(--au-cyan); }

    .se-sheet-content {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: minmax(280px, .84fr) minmax(360px, 1.16fr);
        gap: 18px;
        padding-left: 48px;
        align-items: start;
    }

    .se-image-box {
        border-radius: 18px;
        overflow: hidden;
        height: 280px;
        background: linear-gradient(135deg, rgba(36,79,151,.26), rgba(107,75,216,.18));
        border: 1px solid rgba(255,255,255,.12);
        border-bottom: 7px solid var(--au-blue-2);
        box-shadow: 0 12px 28px rgba(0,0,0,.24);
    }

    .se-image-box img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .se-image-placeholder {
        height: 100%;
        display: grid;
        place-items: center;
        color: #ffffff;
        font-size: 46px;
        font-weight: 950;
        letter-spacing: -1px;
        background: linear-gradient(135deg, rgba(36,79,151,.55), rgba(17,24,39,.55));
    }

    .se-title-box {
        margin-top: 17px;
        border-bottom: 1px solid rgba(255,255,255,.16);
        padding-bottom: 12px;
    }

    .se-title-box h2 {
        color: #ffffff;
        font-weight: 950;
        line-height: .98;
        letter-spacing: -1.1px;
        text-transform: uppercase;
        margin: 0;
        font-size: clamp(25px, 3vw, 40px);
    }

    .se-title-box p {
        margin: 7px 0 0;
        color: #b9d3ff;
        font-weight: 900;
        text-transform: uppercase;
        font-size: 15px;
    }

    .se-value { margin-top: 14px; display: grid; grid-template-columns: 42px 1fr; gap: 10px; align-items: center; }

    .se-icon-big {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(255,255,255,.10);
        color: #fff;
        font-size: 23px;
        border: 1px solid rgba(255,255,255,.13);
    }

    .se-value .label { color: var(--au-muted); text-transform: uppercase; font-size: 12px; line-height: 1; font-weight: 950; }
    .se-value .money { margin-top: 2px; color: #fff; font-weight: 950; font-size: clamp(27px, 3.3vw, 44px); line-height: .9; letter-spacing: -1.6px; grid-column: 1 / -1; }
    .se-value .unit { font-size: 12px; line-height: 1; color: var(--au-text-2); font-weight: 800; grid-column: 1 / -1; }

    .se-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border-radius: 999px;
        padding: 8px 11px;
        background: rgba(32,176,111,.14);
        color: #d8ffe9;
        border: 1px solid rgba(32,176,111,.22);
        font-weight: 950;
        font-size: 11px;
        text-transform: uppercase;
        margin-top: 12px;
    }

    .se-progress { height: 10px; background: rgba(255,255,255,.10); border-radius: 999px; overflow: hidden; margin-top: 9px; }
    .se-progress span { display: block; height: 100%; background: linear-gradient(90deg, var(--au-green), var(--au-cyan)); border-radius: 999px; }

    .se-right-title { color: #b9d3ff; font-size: 11px; text-transform: uppercase; font-weight: 950; letter-spacing: .03em; margin: 2px 0 10px; border-bottom: 1px solid rgba(255,255,255,.16); padding-bottom: 9px; }

    .se-info-stack { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }

    .se-info-row {
        min-height: 72px;
        display: grid;
        grid-template-columns: 42px 1fr;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: linear-gradient(135deg, rgba(28, 38, 64, .96), rgba(17, 24, 39, .88));
        border: 1px solid rgba(255,255,255,.11);
        border-radius: 16px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
    }

    .se-info-row.featured { background: linear-gradient(135deg, rgba(36,79,151,.35), rgba(17,24,39,.91)); }
    .se-info-row.wide { grid-column: 1 / -1; }

    .se-info-icon {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: rgba(255,255,255,.10);
        color: #fff;
        font-size: 20px;
        border: 1px solid rgba(255,255,255,.13);
    }

    .se-info-text .mini { display: block; color: var(--au-muted); text-transform: uppercase; font-weight: 950; font-size: 11px; line-height: 1; }
    .se-info-text .big { display: block; color: #ffffff; font-size: clamp(17px, 1.8vw, 23px); font-weight: 950; line-height: 1.02; text-transform: uppercase; overflow-wrap: anywhere; }
    .se-info-text .desc { display: block; color: var(--au-text-2); font-size: 12px; font-weight: 750; line-height: 1.22; margin-top: 4px; overflow-wrap: anywhere; }

    .se-split-values { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 7px; margin-top: 3px; }
    .se-split-values .desc { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.10); border-radius: 12px; padding: 8px; margin: 0; color: var(--au-text-2); }

    .se-projects-strip { margin-top: 12px; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }

    .se-small-project {
        display: block;
        text-decoration: none !important;
        color: inherit;
        border-radius: 17px;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(18, 26, 43, .96), rgba(28, 38, 64, .92));
        border: 1px solid rgba(255,255,255,.12);
        box-shadow: var(--au-soft-shadow);
        transition: .22s ease;
        min-height: 136px;
    }

    .se-small-project:hover { transform: translateY(-2px); color: inherit; border-color: rgba(57,189,248,.45); }
    .se-small-project.active { border-color: var(--au-gold); box-shadow: 0 12px 34px rgba(215,169,39,.18), inset 0 0 0 1px rgba(215,169,39,.28); }

    .se-small-thumb {
        height: 82px;
        background: linear-gradient(135deg, rgba(36,79,151,.28), rgba(107,75,216,.18));
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid rgba(255,255,255,.10);
    }

    .se-small-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .se-small-placeholder {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
        color: #ffffff;
        font-weight: 950;
        font-size: 20px;
        letter-spacing: .08em;
        background: linear-gradient(135deg, rgba(36,79,151,.46), rgba(17,24,39,.52));
    }

    .se-small-body { padding: 9px 10px 10px; }
    .se-small-body strong { display: block; color: #ffffff; font-size: 11px; line-height: 1.08; font-weight: 950; text-transform: uppercase; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .se-small-body span { display: block; color: var(--au-muted); font-size: 10px; font-weight: 800; margin-top: 5px; }

    .se-alert { padding: 12px 15px; border-radius: 15px; margin-bottom: 12px; font-weight: 850; box-shadow: var(--au-soft-shadow); }
    .se-alert-ok { color: #d8ffe9; background: rgba(31, 166, 106, .16); border: 1px solid rgba(31, 166, 106, .28); }
    .se-alert-error { color: #ffd8df; background: rgba(217, 68, 68, .16); border: 1px solid rgba(217, 68, 68, .28); }

    .se-modal .modal-dialog { max-width: 980px; }

    /* ---- Slider de avances ---- */
    .se-galeria { margin-top: 16px; }
    .se-galeria-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .se-galeria-label { font-size: 12px; font-weight: 950; color: var(--au-text-2); text-transform: uppercase; }
    .se-galeria-counter { font-size: 12px; font-weight: 900; color: var(--au-muted); }
    .se-galeria-slider { position: relative; overflow: hidden; border-radius: 14px; background: rgba(0,0,0,.3); border: 1px solid rgba(255,255,255,.10); }
    .se-galeria-track { display: flex; transition: transform .5s cubic-bezier(.25,.46,.45,.94); will-change: transform; }
    .se-galeria-item { flex: 0 0 100%; height: 280px; position: relative; }
    .se-galeria-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .se-galeria-item.broken { min-height: 200px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,.2); }
    .se-galeria-placeholder { color: var(--au-muted); font-size: 14px; padding: 40px; }
    .se-galeria-fecha { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,.82)); color: #fff; padding: 18px 12px 10px; font-size: 14px; font-weight: 900; }
    .se-galeria-nav { display: flex; gap: 8px; margin-top: 10px; justify-content: center; align-items: center; }
    .se-galeria-btn { border: 0; background: rgba(255,255,255,.10); color: #fff; border-radius: 999px; width: 36px; height: 36px; font-size: 16px; cursor: pointer; transition: .2s ease; display: grid; place-items: center; }
    .se-galeria-btn:hover { background: rgba(255,255,255,.20); }
    .se-galeria-dots { display: flex; gap: 6px; margin: 0 12px; }
    .se-galeria-dot { width: 10px; height: 10px; border-radius: 999px; background: rgba(255,255,255,.25); cursor: pointer; transition: .25s ease; border: 0; }
    .se-galeria-dot.active { background: #fff; width: 26px; border-radius: 999px; }

    /* Mini galería en modal */
    .se-galeria-mini { display: flex; flex-wrap: wrap; gap: 10px; }
    .se-galeria-mini-item { position: relative; width: 120px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,.12); background: rgba(0,0,0,.3); }
    .se-galeria-mini-item img { width: 100%; height: 80px; object-fit: cover; display: block; }
    .se-galeria-mini-fecha { display: block; font-size: 10px; color: var(--au-text-2); padding: 4px 6px; text-align: center; font-weight: 800; }
    .se-galeria-mini-del { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border: 0; border-radius: 999px; background: rgba(217,68,68,.88); color: #fff; font-size: 14px; line-height: 1; cursor: pointer; display: grid; place-items: center; }
    .se-modal .modal-content { border: 1px solid rgba(255,255,255,.13); border-radius: 24px; overflow: hidden; box-shadow: 0 24px 80px rgba(0,0,0,.45); background: #101827; color: #fff; }
    .se-modal-head { padding: 18px 22px; color: #fff; background: linear-gradient(135deg, #111827, #244f97); display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; border-bottom: 1px solid rgba(255,255,255,.10); }
    .se-modal-head h3 { margin: 0; color: #fff; font-weight: 950; font-size: 24px; }
    .se-modal-head p { margin: 4px 0 0; color: #b4c8e0; font-size: 13px; }
    .se-modal-close { width: 38px; height: 38px; border: 1px solid rgba(255,255,255,.22); border-radius: 999px; background: rgba(255,255,255,.10); color: #fff; font-size: 24px; line-height: 1; cursor: pointer; }

    .se-form-body { padding: 18px 20px 20px; max-height: 72vh; overflow: auto; background: #101827; color: #fff; }
    .se-form-body label { color: #fff !important; }
    .se-form-body p, .se-form-body .se-form-group { color: #fff; }
    .se-form-group { margin-bottom: 12px; }
    .se-form-group label { font-size: 11px; color: var(--au-text-2); font-weight: 950; margin-bottom: 6px; display: block; text-transform: uppercase; }
    .se-input, .se-textarea, .se-form-select { width: 100%; border: 1px solid rgba(255,255,255,.13); background: rgba(8, 13, 25, .84); border-radius: 13px; padding: 11px 13px; outline: none; color: #fff !important; font-weight: 750; transition: .2s ease; }
    .se-input::placeholder, .se-textarea::placeholder { color: rgba(255,255,255,.5) !important; }
    .se-form-select option { color: #101828; background: #fff; }
    .se-textarea { min-height: 84px; resize: vertical; }
    .se-input:focus, .se-textarea:focus, .se-form-select:focus { border-color: rgba(57,189,248,.65); background: rgba(8,13,25,.98); box-shadow: 0 0 0 4px rgba(57,189,248,.10); color: #fff !important; }
    .se-form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .se-form-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .se-modal-footer { padding-top: 10px; display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }

    /* ---- Upload imagen principal ---- */
    .se-img-upload-area { position: relative; border-radius: 16px; overflow: hidden; border: 2px dashed rgba(255,255,255,.18); background: rgba(8,13,25,.84); cursor: pointer; transition: .2s ease; min-height: 150px; display: flex; align-items: center; justify-content: center; }
    .se-img-upload-area:hover { border-color: rgba(57,189,248,.50); background: rgba(8,13,25,.98); }
    .se-img-upload-preview { width: 100%; height: 100%; position: relative; }
    .se-img-upload-preview img { width: 100%; height: 170px; object-fit: cover; display: block; }
    .se-img-upload-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.55); display: flex; align-items: center; justify-content: center; opacity: 0; transition: .2s ease; border-radius: 0; }
    .se-img-upload-area:hover .se-img-upload-overlay { opacity: 1; }
    .se-img-upload-overlay span { color: #fff; font-weight: 800; font-size: 13px; }
    .se-img-upload-empty { text-align: center; padding: 28px 20px; }
    .se-img-upload-icon { font-size: 34px; display: block; margin-bottom: 6px; }
    .se-img-upload-text { display: block; color: #fff; font-weight: 800; font-size: 14px; }
    .se-img-upload-hint { display: block; color: var(--au-muted); font-size: 11px; margin-top: 4px; font-weight: 700; }
    .se-img-upload-footer { display: flex; align-items: center; gap: 8px; margin-top: 7px; min-height: 30px; }
    .se-img-upload-filename { font-size: 12px; color: var(--au-text-2); font-weight: 800; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .se-img-upload-clear { min-height: 30px !important; font-size: 11px !important; padding: 4px 12px !important; border-radius: 999px !important; }
    .se-img-url-details { margin-top: 8px; }
    .se-img-url-summary { font-size: 12px; color: var(--au-muted); cursor: pointer; font-weight: 700; padding: 4px 0; }
    .se-img-url-summary:hover { color: #fff; }
    .se-img-url-input { margin-top: 6px; }

    @media (max-width: 1360px) {
        .se-sheet-content { grid-template-columns: minmax(270px, .75fr) minmax(350px, 1.25fr); }
        .se-image-box { height: 230px; }
        .se-galeria-item { height: 230px; }
        .se-info-row { min-height: 66px; padding: 9px 10px; }
        .se-title-box h2 { font-size: clamp(22px, 2.5vw, 35px); }
    }

    @media (max-width: 1200px) {
        .se-dashboard-grid { grid-template-columns: repeat(3, 1fr); }
        .se-sheet-content { grid-template-columns: 1fr; }
        .se-image-box { height: 250px; }
        .se-galeria-item { height: 250px; }
        .se-info-stack { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .se-projects-strip { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 850px) {
        .se-page { padding: 10px 8px 24px; }
        .se-toolbar, .se-filter-line { grid-template-columns: 1fr; }
        .se-toolbar-actions { justify-content: flex-start; }
        .se-dashboard-grid, .se-projects-strip { grid-template-columns: repeat(2, 1fr); }
        .se-project-sheet { padding: 16px 12px; border-radius: 20px; }
        .se-lines { left: 10px; }
        .se-sheet-content { padding-left: 36px; gap: 14px; }
        .se-info-stack, .se-form-grid-2, .se-form-grid-3, .se-split-values { grid-template-columns: 1fr; }
        .se-form-body { max-height: 78vh; }
    }

    @media (max-width: 520px) {
        .se-dashboard-grid, .se-projects-strip { grid-template-columns: 1fr; }
        .se-toolbar h1 { font-size: 24px; }
        .se-btn { width: 100%; }
        .se-filter-line .se-btn { width: 100%; }
        .se-image-box { height: 200px; }
        .se-galeria-item { height: 200px; }
        .se-title-box h2 { font-size: 24px; }
        .se-value .money { font-size: 31px; }
        .se-info-row { grid-template-columns: 38px 1fr; }
        .se-info-icon { width: 34px; height: 34px; font-size: 18px; }
    }
</style>

<body class="">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <?php include './admin/include/navbar.php'; ?>
    <?php include './admin/include/header.php'; ?>

    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="se-page">

                                <?php if ($mensajeOk): ?>
                                    <div class="se-alert se-alert-ok"><?= seproj_h($mensajeOk); ?></div>
                                <?php endif; ?>

                                <?php if ($mensajeError): ?>
                                    <div class="se-alert se-alert-error"><?= seproj_h($mensajeError); ?></div>
                                <?php endif; ?>

                                <section class="se-toolbar">
                                    <div class="se-toolbar-main">
                                        <div class="se-kicker"><span></span> Acción Unificada · Infraestructura y Gobernador</div>
                                        <h1>Panel de Proyectos Estratégicos</h1>
                                        <p>Vista ejecutiva compacta, sin formulario lateral permanente y lista para seguimiento institucional.</p>
                                    </div>
                                    <div class="se-toolbar-actions">
                                        <?php if ($proyecto): ?>
                                            <button type="button" class="se-btn se-btn-primary js-open-project-modal" data-toggle="modal" data-target="#modalActualizarProyecto">
                                                ✏️ Actualizar proyecto
                                            </button>
                                        <?php endif; ?>
                                        <a href="javascript:history.back()" class="se-btn se-btn-soft">← Atrás</a>
                                    </div>
                                </section>

                                <form method="GET" class="se-filter-line">
                                    <select name="proyecto_id" id="proyecto_id" class="se-select">
                                        <?php foreach ($proyectos as $p): ?>
                                            <option value="<?= (int)$p['id']; ?>" <?= ((int)$p['id'] === (int)$selectedId) ? 'selected' : ''; ?>>
                                                <?= seproj_h($p['nombre']); ?><?= !empty($p['subtitulo']) ? ' - ' . seproj_h($p['subtitulo']) : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="se-btn se-btn-blue">Ver proyecto</button>
                                </form>

                                <section class="se-dashboard-grid">
                                    <div class="se-mini-card"><small>Total proyectos</small><strong><?= number_format((float)($resumen['total_proyectos'] ?? 0), 0, ',', '.'); ?></strong></div>
                                    <div class="se-mini-card"><small>En ejecución</small><strong><?= number_format((float)($resumen['en_ejecucion'] ?? 0), 0, ',', '.'); ?></strong></div>
                                    <div class="se-mini-card"><small>Impacto alto</small><strong><?= number_format((float)($resumen['impacto_alto'] ?? 0), 0, ',', '.'); ?></strong></div>
                                    <div class="se-mini-card"><small>Inversión total</small><strong><?= seproj_money($resumen['inversion_total'] ?? 0); ?></strong></div>
                                    <div class="se-mini-card"><small>Avance promedio</small><strong><?= number_format((float)($resumen['avance_promedio'] ?? 0), 2, ',', '.'); ?>%</strong></div>
                                </section>

                                <?php if ($proyecto): ?>
                                    <section class="se-main-grid">
                                        <article class="se-project-sheet">
                                            <div class="se-lines"><span></span><span></span><span></span><span></span></div>

                                            <div class="se-sheet-content">
                                                <div>
                                                    <?php if (!empty($slides)): ?>
                                                        <div class="se-galeria">
                                                            <div class="se-galeria-top">
                                                                <span class="se-galeria-label">📸 Avances del proyecto</span>
                                                                <span class="se-galeria-counter"><span id="galeriaIdx">1</span>/<span id="galeriaTotal"><?= count($slides); ?></span></span>
                                                            </div>
                                                            <div class="se-galeria-slider">
                                                                <div class="se-galeria-track" id="galeriaTrack">
                                                                    <?php foreach ($slides as $i => $s): ?>
                                                                        <div class="se-galeria-item">
                                                                            <img src="<?= seproj_h($s['imagen_url']); ?>" alt="Slide <?= $i + 1; ?>" onerror="this.style.display='none';this.parentNode.classList.add('broken');this.parentNode.insertAdjacentHTML('afterbegin','<div class=\'se-galeria-placeholder\'>Sin imagen</div>');">
                                                                            <?php if (!empty($s['fecha_avance'])): ?>
                                                                                <div class="se-galeria-fecha">📅 <?= seproj_h($s['fecha_avance']); ?></div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                            <div class="se-galeria-nav">
                                                                <button type="button" class="se-galeria-btn" id="galeriaPrev">‹</button>
                                                                <div class="se-galeria-dots" id="galeriaDots">
                                                                    <?php foreach ($slides as $i => $s): ?>
                                                                        <button type="button" class="se-galeria-dot <?= $i === 0 ? 'active' : ''; ?>" data-slide="<?= $i; ?>"></button>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <button type="button" class="se-galeria-btn" id="galeriaNext">›</button>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="se-image-box">
                                                            <div class="se-image-placeholder">AU</div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="se-title-box">
                                                        <h2><?= seproj_h($proyecto['nombre']); ?></h2>
                                                        <?php if (!empty($proyecto['subtitulo'])): ?>
                                                            <p><?= seproj_h($proyecto['subtitulo']); ?></p>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="se-value">
                                                        <div class="se-icon-big">💰</div>
                                                        <div class="label">Valor del proyecto</div>
                                                        <div class="money"><?= seproj_money($proyecto['valor_proyecto']); ?></div>
                                                        <div class="unit"><?= seproj_h($proyecto['unidad_valor']); ?></div>
                                                    </div>

                                                    <div class="se-badge-status">Estado: <?= seproj_h($proyecto['estado']); ?></div>
                                                    <div class="se-progress"><span style="width: <?= min(100, max(0, (float)$proyecto['avance'])); ?>%;"></span></div>
                                                </div>

                                                <div>
                                                    <div class="se-right-title">Proyectos Estratégicos Departamento de Santander</div>
                                                    <div class="se-info-stack">

                                                        <?php if ((float)$proyecto['valor_requerido_nacion'] > 0 || (float)$proyecto['valor_aportado_gobernacion'] > 0 || (float)$proyecto['valor_aportado_nacion'] > 0 || (float)$proyecto['aporte_valorizacion'] > 0): ?>
                                                            <div class="se-info-row featured wide">
                                                                <div class="se-info-icon">🏛️</div>
                                                                <div class="se-info-text">
                                                                    <span class="mini">Valores de financiación</span>
                                                                    <div class="se-split-values">
                                                                        <?php if ((float)$proyecto['valor_requerido_nacion'] > 0): ?><span class="desc">Nación:<br><b><?= seproj_money($proyecto['valor_requerido_nacion']); ?></b></span><?php endif; ?>
                                                                        <?php if ((float)$proyecto['valor_aportado_gobernacion'] > 0): ?><span class="desc">Gobernación:<br><b><?= seproj_money($proyecto['valor_aportado_gobernacion']); ?></b></span><?php endif; ?>
                                                                        <?php if ((float)$proyecto['valor_aportado_nacion'] > 0): ?><span class="desc">Aporte Nación:<br><b><?= seproj_money($proyecto['valor_aportado_nacion']); ?></b></span><?php endif; ?>
                                                                        <?php if ((float)$proyecto['aporte_valorizacion'] > 0): ?><span class="desc">Valorización:<br><b><?= seproj_money($proyecto['aporte_valorizacion']); ?></b></span><?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="se-info-row featured wide">
                                                            <div class="se-info-icon">✅</div>
                                                            <div class="se-info-text">
                                                                <span class="mini">Tarea clave</span>
                                                                <span class="desc"><?= nl2br(seproj_h($proyecto['tarea_clave'])); ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="se-info-row featured wide">
                                                            <div class="se-info-icon">🏢</div>
                                                            <div class="se-info-text">
                                                                <span class="mini">Ministerio o entidad nacional</span>
                                                                <span class="big"><?= seproj_h($proyecto['ministerio']); ?></span>
                                                                <?php if (!empty($proyecto['entidad'])): ?><span class="desc">Entidad: <?= seproj_h($proyecto['entidad']); ?></span><?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="se-info-row">
                                                            <div class="se-info-icon">👥</div>
                                                            <div class="se-info-text"><span class="mini">Población beneficiada</span><span class="big"><?= seproj_h($proyecto['poblacion_beneficiada']); ?></span></div>
                                                        </div>

                                                        <div class="se-info-row">
                                                            <div class="se-info-icon">📅</div>
                                                            <div class="se-info-text"><span class="mini">Tiempo</span><span class="big"><?= seproj_h($proyecto['tiempo']); ?></span></div>
                                                        </div>

                                                        <div class="se-info-row">
                                                            <div class="se-info-icon">🏗️</div>
                                                            <div class="se-info-text"><span class="mini">Estado</span><span class="big"><?= seproj_h($proyecto['estado']); ?></span></div>
                                                        </div>

                                                        <div class="se-info-row">
                                                            <div class="se-info-icon">📊</div>
                                                            <div class="se-info-text"><span class="mini">Impacto estratégico</span><span class="big"><?= seproj_h($proyecto['impacto']); ?></span><span class="desc">Avance: <?= number_format((float)$proyecto['avance'], 2, ',', '.'); ?>%</span></div>
                                                        </div>

                                                        <?php if (!empty($proyecto['observaciones'])): ?>
                                                            <div class="se-info-row wide">
                                                                <div class="se-info-icon">📝</div>
                                                                <div class="se-info-text"><span class="mini">Observaciones</span><span class="desc"><?= nl2br(seproj_h($proyecto['observaciones'])); ?></span></div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    </section>

                                    <section class="se-projects-strip">
                                        <?php foreach ($proyectos as $p): ?>
                                            <a class="se-small-project <?= ((int)$p['id'] === (int)$selectedId) ? 'active' : ''; ?>" href="?proyecto_id=<?= (int)$p['id']; ?>">
                                                <div class="se-small-thumb">
                                                    <?php if (!empty($p['imagen_url'])): ?>
                                                        <img src="<?= seproj_h($p['imagen_url']); ?>" alt="<?= seproj_h($p['nombre']); ?>" loading="lazy" onerror="this.parentNode.innerHTML='<div class=&quot;se-small-placeholder&quot;>AU</div>';">
                                                    <?php else: ?>
                                                        <div class="se-small-placeholder">AU</div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="se-small-body">
                                                    <strong><?= seproj_h($p['nombre']); ?></strong>
                                                    <span><?= seproj_money($p['valor_proyecto']); ?> · <?= seproj_h($p['estado']); ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </section>

                                    <div class="modal fade se-modal" id="modalActualizarProyecto" tabindex="-1" role="dialog" aria-labelledby="modalActualizarProyectoTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="se-modal-head">
                                                    <div>
                                                        <h3 id="modalActualizarProyectoTitle">Actualizar proyecto</h3>
                                                        <p>Edita la información, guarda los cambios y vuelve automáticamente a esta vista.</p>
                                                    </div>
                                                    <button type="button" class="se-modal-close" data-dismiss="modal" aria-label="Cerrar">×</button>
                                                </div>

                                                <form method="POST" class="se-form-body" autocomplete="off" enctype="multipart/form-data">
                                                    <input type="hidden" name="action" value="update_project">
                                                    <input type="hidden" name="csrf_token" value="<?= seproj_h($csrf); ?>">
                                                    <input type="hidden" name="id" value="<?= (int)$proyecto['id']; ?>">

                                                    <div class="se-form-group">
                                                        <label>Nombre del proyecto</label>
                                                        <input type="text" name="nombre" class="se-input" value="<?= seproj_h($proyecto['nombre']); ?>" required>
                                                    </div>

                                                    <div class="se-form-group">
                                                        <label>Subtítulo</label>
                                                        <input type="text" name="subtitulo" class="se-input" value="<?= seproj_h($proyecto['subtitulo']); ?>">
                                                    </div>

                                                    <div class="se-form-grid-3">
                                                        <div class="se-form-group"><label>Categoría</label><input type="text" name="categoria" class="se-input" value="<?= seproj_h($proyecto['categoria']); ?>"></div>
                                                        <div class="se-form-group"><label>Unidad de valor</label><input type="text" name="unidad_valor" class="se-input" value="<?= seproj_h($proyecto['unidad_valor']); ?>"></div>
                                                        <div class="se-form-group">
                                                            <label>Imagen principal (portada)</label>
                                                            <div class="se-img-upload" id="imgPrincipalUpload">
                                                                <input type="file" name="imagen_principal" accept="image/*" id="inputImgPrincipal" style="display:none">
                                                                <input type="hidden" name="imagen_url" id="inputImgUrl" value="<?= seproj_h($proyecto['imagen_url']); ?>">
                                                                <div class="se-img-upload-area" id="imgPrincipalArea">
                                                                    <div class="se-img-upload-preview" id="imgPrincipalPreview">
                                                                        <?php if (!empty($proyecto['imagen_url'])): ?>
                                                                            <img src="<?= seproj_h($proyecto['imagen_url']); ?>" alt="" id="imgPrincipalImg">
                                                                            <div class="se-img-upload-overlay"><span>🖱️ Cambiar imagen</span></div>
                                                                        <?php else: ?>
                                                                            <div class="se-img-upload-empty">
                                                                                <span class="se-img-upload-icon">🖼️</span>
                                                                                <span class="se-img-upload-text">Haz clic para subir imagen</span>
                                                                                <span class="se-img-upload-hint">JPG, PNG, GIF, WebP • Máx 10MB</span>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="se-img-upload-footer">
                                                                    <span class="se-img-upload-filename" id="imgPrincipalFileName"></span>
                                                                    <button type="button" class="se-btn se-btn-soft se-img-upload-clear" id="btnClearPrincipalImg" <?= empty($proyecto['imagen_url']) ? 'style="display:none"' : ''; ?>>✕ Quitar</button>
                                                                </div>
                                                                <details class="se-img-url-details">
                                                                    <summary class="se-img-url-summary">O ingresar URL manual</summary>
                                                                    <input type="text" class="se-input se-img-url-input" value="<?= seproj_h($proyecto['imagen_url']); ?>" placeholder="https://ejemplo.com/imagen.jpg" id="inputImgUrlManual">
                                                                </details>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <hr style="border-color:rgba(255,255,255,.12); margin:18px 0;">
                                                    <div class="se-form-group">
                                                        <label style="font-size:14px;">📸 Imágenes de avance</label>
                                                        <p style="color:var(--au-muted); font-size:12px; margin:0 0 10px;">Sube imágenes con fecha de avance para mostrar en la galería del proyecto.</p>
                                                        <div id="seGaleriaActual" class="se-galeria-mini">
                                                            <?php $galeriaEdit = seproj_get_galeria((int)$proyecto['id']); ?>
                                                            <?php if (!empty($galeriaEdit)): ?>
                                                                <?php foreach ($galeriaEdit as $g): ?>
                                                                    <div class="se-galeria-mini-item" data-id="<?= (int)$g['id']; ?>">
                                                                        <img src="<?= seproj_h($g['imagen_url']); ?>" alt="">
                                                                        <span class="se-galeria-mini-fecha"><?= !empty($g['fecha_avance']) ? seproj_h($g['fecha_avance']) : 'Sin fecha'; ?></span>
                                                                        <button type="button" class="se-galeria-mini-del" data-id="<?= (int)$g['id']; ?>" title="Eliminar">×</button>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <p style="color:var(--au-muted); font-size:13px;">No hay imágenes de avance. Sube la primera.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:10px; margin-top:10px; align-items:end;">
                                                            <div>
                                                                <label style="font-size:11px;">Seleccionar imagen</label>
                                                                <div style="display:flex; gap:6px;">
                                                                    <input type="file" id="inputAvanceImg" accept="image/*" style="display:none;">
                                                                    <button type="button" class="se-btn se-btn-soft" onclick="document.getElementById('inputAvanceImg').click();" style="font-size:11px;">📁 Elegir archivo</button>
                                                                    <span id="avanceImgName" style="font-size:11px; color:rgba(255,255,255,.5); align-self:center;">Ninguno</span>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <label style="font-size:11px;">Fecha de avance</label>
                                                                <input type="date" id="inputAvanceFecha" class="se-input" style="color-scheme:dark;">
                                                            </div>
                                                            <button type="button" id="btnSubirAvance" class="se-btn se-btn-primary" style="min-height:44px;">Subir</button>
                                                        </div>
                                                    </div>
                                                    <hr style="border-color:rgba(255,255,255,.12); margin:18px 0;">

                                                    <div class="se-form-grid-3">
                                                        <div class="se-form-group"><label>Valor proyecto</label><input type="text" name="valor_proyecto" class="se-input money-field" value="<?= number_format((float)$proyecto['valor_proyecto'], 0, ',', '.'); ?>"></div>
                                                        <div class="se-form-group"><label>Requerido Nación</label><input type="text" name="valor_requerido_nacion" class="se-input money-field" value="<?= number_format((float)$proyecto['valor_requerido_nacion'], 0, ',', '.'); ?>"></div>
                                                        <div class="se-form-group"><label>Aporte Gobernación</label><input type="text" name="valor_aportado_gobernacion" class="se-input money-field" value="<?= number_format((float)$proyecto['valor_aportado_gobernacion'], 0, ',', '.'); ?>"></div>
                                                    </div>

                                                    <div class="se-form-grid-2">
                                                        <div class="se-form-group"><label>Aporte Nación</label><input type="text" name="valor_aportado_nacion" class="se-input money-field" value="<?= number_format((float)$proyecto['valor_aportado_nacion'], 0, ',', '.'); ?>"></div>
                                                        <div class="se-form-group"><label>Aporte por valorización</label><input type="text" name="aporte_valorizacion" class="se-input money-field" value="<?= number_format((float)$proyecto['aporte_valorizacion'], 0, ',', '.'); ?>"></div>
                                                    </div>

                                                    <div class="se-form-grid-2">
                                                        <div class="se-form-group"><label>Ministerio</label><input type="text" name="ministerio" class="se-input" value="<?= seproj_h($proyecto['ministerio']); ?>"></div>
                                                        <div class="se-form-group"><label>Entidad</label><input type="text" name="entidad" class="se-input" value="<?= seproj_h($proyecto['entidad']); ?>"></div>
                                                    </div>

                                                    <div class="se-form-grid-2">
                                                        <div class="se-form-group"><label>Tarea clave</label><textarea name="tarea_clave" class="se-textarea"><?= seproj_h($proyecto['tarea_clave']); ?></textarea></div>
                                                        <div class="se-form-group"><label>Observaciones</label><textarea name="observaciones" class="se-textarea"><?= seproj_h($proyecto['observaciones']); ?></textarea></div>
                                                    </div>

                                                    <div class="se-form-grid-3">
                                                        <div class="se-form-group"><label>Población beneficiada</label><input type="text" name="poblacion_beneficiada" class="se-input" value="<?= seproj_h($proyecto['poblacion_beneficiada']); ?>"></div>
                                                        <div class="se-form-group"><label>Tiempo</label><input type="text" name="tiempo" class="se-input" value="<?= seproj_h($proyecto['tiempo']); ?>"></div>
                                                        <div class="se-form-group"><label>Avance %</label><input type="text" name="avance" class="se-input decimal-field" value="<?= number_format((float)$proyecto['avance'], 2, ',', '.'); ?>"></div>
                                                    </div>

                                                    <div class="se-form-grid-3">
                                                        <div class="se-form-group">
                                                            <label>Estado</label>
                                                            <select name="estado" class="se-form-select">
                                                                <?php $estados = ['En ejecución', 'Liquidado', 'Suspendido', 'Finalizado', 'En estructuración', 'Pendiente']; foreach ($estados as $estado): ?>
                                                                    <option value="<?= seproj_h($estado); ?>" <?= $proyecto['estado'] === $estado ? 'selected' : ''; ?>><?= seproj_h($estado); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="se-form-group">
                                                            <label>Impacto</label>
                                                            <select name="impacto" class="se-form-select">
                                                                <?php $impactos = ['Alto', 'Medio', 'Bajo']; foreach ($impactos as $impacto): ?>
                                                                    <option value="<?= seproj_h($impacto); ?>" <?= $proyecto['impacto'] === $impacto ? 'selected' : ''; ?>><?= seproj_h($impacto); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="se-form-group">
                                                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; height:100%; padding-top:23px;">
                                                                <input type="checkbox" name="activo" value="1" <?= (int)$proyecto['activo'] === 1 ? 'checked' : ''; ?>> Proyecto activo
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="se-modal-footer">
                                                        <button type="button" class="se-btn se-btn-soft" data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="se-btn se-btn-primary">💾 Guardar cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                <?php else: ?>
                                    <div class="se-alert se-alert-error">No hay proyectos registrados.</div>
                                <?php endif; ?>

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

    <script>
        window.proyectoId = <?= (int)$selectedId; ?>;
    </script>
    <script src="<?php echo Util::versionar('./admin/js/secretaria_estrategicos.js'); ?>"></script>
</body>
</html>
