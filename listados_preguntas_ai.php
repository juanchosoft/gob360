<?php

use GPBMetadata\Google\Api\Consumer;

require './admin/include/generic_classes.php';
include './admin/classes/ConsultasIA.php';
include './admin/classes/MainPae.php';

$db = new DbConection();
$pdo = $db->openConect();

// Mapear secretarías
$mapaSecretarias = [];
$query = "SELECT secretaria, secretario FROM " . $db->getTable('tbl_secretarias');
foreach ($pdo->query($query) as $fila) {
    $mapaSecretarias[strtolower(trim($fila['secretaria']))] = $fila['secretario'];
}

// Paso 1: Detectar si hay una secretaría mencionada
$secretariaDetectada = null;
foreach ($mapaSecretarias as $nombreSecretaria => $datoSecretario) {
    if (stripos($textoReconocido, $nombreSecretaria) !== false) {
        $secretariaDetectada = $nombreSecretaria;
        break;
    }
}

$nombre = $_SESSION['nombre_usuario'] ?? null;

function detectarNombreDesdeTexto($texto)
{
    $patrones = [
        '/(?:me llamo|mi nombre es|soy|claro que sí,? mi nombre es|claro que sí,? me llamo|claro mi nombre es|claro que mi, nombre es)\s+([a-záéíóúñ]+(?:\s+[a-záéíóúñ]+)?)/i',
        '/([a-záéíóúñ]+(?:\s+[a-záéíóúñ]+)?)\s+es mi nombre/i'
    ];

    foreach ($patrones as $patron) {
        if (preg_match($patron, $texto, $match)) {
            return ucwords(strtolower(trim($match[1])));
        }
    }

    return null;
}

// 🔁 Si el usuario dice "olvida mi nombre", lo olvidamos y reiniciamos el flujo
if (stripos($textoReconocido, 'olvida mi nombre') !== false) {
    unset($_SESSION['nombre_usuario']);
    $respuesta =  "De acuerdo, ya olvidé tu nombre. ¿Cómo te llamas?";
    return $respuesta;
}

// 🧠 Si no conocemos su nombre todavía
if (!$nombre) {
    $nombreDetectado = detectarNombreDesdeTexto($textoReconocido);

    if ($nombreDetectado) {
        $nombre = $nombreDetectado;
        $_SESSION['nombre_usuario'] = $nombre;
        $respuesta = "Eres muy amable, encantado de conocerte, $nombre. ¿En qué puedo ayudarte el día de hoy?";
    } else {
        $respuesta = "Primero necesito saber cuál es su nombre. ¿Cómo te llamas?";
    }

    return $respuesta;
}

if (ConsultasIA::detectarFraseClaveResumenSecretariaContratosYProyecto($textoReconocido)) {

    // Tiene prioridad si hay contexto de contratos/proyectos, inversion 
    $respuesta = ConsultasIA::obtenerRespuestaDeProyectoYSecretarias($db, $pdo, $textoReconocido);
} elseif (ConsultasIA::detectarFraseClavePAE($textoReconocido)) {
    // Tiene prioridad si hay contexto de PAE
    $respuesta = ConsultasIA::obtenerRespuestaPAE($db, $pdo, $textoReconocido);
} elseif (stripos($textoReconocido, 'producto') !== false || stripos($textoReconocido, 'servicio') !== false) {
    // Información de productos o servicios
    $stmt = $pdo->query("SELECT producto_servicio_pdd FROM " . $db->getTable('tbl_plandesarrollo'));
    $productos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $total = count($productos);
    $resumen = "Hay $total productos registrados. Algunos son: " . implode(", ", array_slice($productos, 0, 3)) . ".";

    $respuesta = $nombre ? "Claro que sí, $nombre. $resumen" : $resumen;
} elseif ($secretariaDetectada) {

    // Obtener información del secretario
    $infoCompleta = $mapaSecretarias[$secretariaDetectada];

    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $infoCompleta, $matches)) {
        $nombreSecretario = trim($matches[1]);
        $tipoCargo = strtolower(trim($matches[2]));
    } else {
        $nombreSecretario = trim($infoCompleta);
        $tipoCargo = 'secretario';
    }

    $cargoTexto = ($tipoCargo === 'encargado') ? 'el encargado' : 'el secretario';
    $nombreSecretariaFormateada = ucfirst($secretariaDetectada);

    $respuesta = $nombre
        ? "Claro que sí, $nombre. $cargoTexto de $nombreSecretariaFormateada es $nombreSecretario."
        : "$cargoTexto de $nombreSecretariaFormateada es $nombreSecretario.";
} elseif (ConsultasIA::detectarFraseSecretarioSecretaria($textoReconocido)) {
    // Listado general de secretarías y sus secretarios

    if (isset($mapaSecretarias) && is_array($mapaSecretarias) && !empty($mapaSecretarias)) {
        $secretariasList = array_map(function ($sec, $secName) {
            return ucfirst($sec) . ": " . $secName;
        }, array_keys($mapaSecretarias), $mapaSecretarias);
        $respuesta = "Claro que sí $nombre,  acontinuación te daré información de algunas secretarías, y sus secretarios, los cuales son: " . implode("; ", array_slice($secretariasList, 0, 7)) . ".";
    } else {
        $respuesta = "Disculpame $nombre, No se encontraron datos de secretarías para mencionar.";
    }
} elseif (ConsultasIA::detectarFraseClaveFactoresDeInestabilidad($db, $pdo, $textoReconocido)) {

    $respuesta = ConsultasIA::obtenerRespuestaFactoresMunicipio($db, $pdo, $textoReconocido);
} else {
    $respuesta = " $nombre No tengo información específica sobre ese tema. ¿Puedes ser más específico?";
}


// Ejemplos de preguntas que podrían ser respondidas por este script:


// ¿Quién es el secretario de la Secretaría de Salud?
// Dame un resumen de PAE del municipio de Aguada
// Cuántos proyectos tiene la Secretaría del Interior en la provincia metropolitana
// ¿Qué productos y servicios ofrece la Secretaría de Desarrollo Económico?
// Listado general de secretarías y sus secretarios   ok
// la secretarías y sus secretarios     ok
// cual es el secretario del Interior?    ok
// Dime el listado de secretarios y sus secretarías  ok
// cuántos proyectos tiene la Secretaría del Interior en la provincia metropolitana
// cómo se llama el secretario de Educación?


// olvida mi nombre