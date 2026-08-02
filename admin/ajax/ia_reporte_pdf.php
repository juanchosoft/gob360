<?php
/**
 * Descarga de informes PDF generados por ALMA.
 * No sirve archivos estáticos: verifica sesión, permiso y propiedad contra BD
 * antes de transmitir los bytes (mismo patrón que ia_tts.php).
 *
 * GET params:
 *   id  int  ID del informe en tbl_ia_reportes
 */

session_start();

header('Cache-Control: no-store, no-cache');

// ── Sesión ────────────────────────────────────────────────────────────────────
if (empty($_SESSION['session_user'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Sesión expirada.']);
    exit;
}

// ── Permiso ───────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../classes/SessionData.php';
if (!SessionData::hasPermission('asistente_ia.pdf.use')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Sin permiso para descargar informes PDF.']);
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'id inválido.']);
    exit;
}

// ── Cargar dependencias ───────────────────────────────────────────────────────
require_once __DIR__ . '/../classes/DbConection.php';
require_once __DIR__ . '/../classes/Util.php';
require_once __DIR__ . '/../classes/ia/IaReporte.php';

// ── Obtener ruta y transmitir ─────────────────────────────────────────────────
try {
    // obtenerRuta() verifica propiedad: solo devuelve informes del usuario en sesión
    $ruta = IaReporte::obtenerRuta($id);
} catch (RuntimeException $e) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

if (!is_file($ruta)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'El archivo del informe ya no está disponible.']);
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="informe.pdf"');
header('Content-Length: ' . filesize($ruta));
header('X-Content-Type-Options: nosniff');
readfile($ruta);
