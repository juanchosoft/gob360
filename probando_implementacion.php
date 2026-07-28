<?php

require './admin/include/generic_classes.php';

// Conexion de la Bd
$db = new DbConection();
$pdo = $db->openConect();

$departamentoPrincipal = Util::getDepartamentoPrincipal();

function normalizeText($text)
{
    $text = strtolower($text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-z0-9\s]/', '', $text);
    return $text;
}

// Información de secretarías y secretarios
// Obtener secretarías y secretarios en un solo paso y mapear a minúsculas
$mapaSecretarias = [];
$query = "SELECT secretaria, secretario FROM " . $db->getTable('tbl_secretarias');
foreach ($pdo->query($query) as $fila) {
    $mapaSecretarias[strtolower(trim($fila['secretaria']))] = $fila['secretario'];
}

// Detectar secretaría mencionada en el texto
$secretariaDetectada = null;
foreach ($mapaSecretarias as $nombreSecretaria => $datoSecretario) {
    if (stripos($textoReconocido, $nombreSecretaria) !== false) {
        $secretariaDetectada = $nombreSecretaria;
        break;
    }
}


if ($secretariaDetectada) {
    // Obtener la información del secretario y tipo de cargo (si existe)
    $infoCompleta = $mapaSecretarias[$secretariaDetectada];

    // Extraer nombre y tipo de cargo si está entre paréntesis
    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $infoCompleta, $matches)) {
        $nombreSecretario = trim($matches[1]);
        $tipoCargo = strtolower(trim($matches[2]));
    } else {
        $nombreSecretario = trim($infoCompleta);
        $tipoCargo = 'secretario';
    }

    // Definir el texto del cargo
    $cargoTexto = ($tipoCargo === 'encargado') ? 'el encargado' : 'el secretario';

    // Construir la respuesta de forma más clara y segura
    $nombreSecretariaFormateada = ucfirst($secretariaDetectada);
    $respuesta = $nombre
        ? "Claro que sí, $nombre. $cargoTexto de $nombreSecretariaFormateada es $nombreSecretario."
        : "$cargoTexto de $nombreSecretariaFormateada es $nombreSecretario.";
} else {

    // Si no se detectó una secretaría, responder según el tema mencionado en el texto
    if (stripos($textoReconocido, 'producto') !== false || stripos($textoReconocido, 'servicio') !== false) {

        // Consultar productos o servicios registrados
        $stmt = $pdo->query("SELECT producto_servicio_pdd FROM " . $db->getTable('tbl_plandesarrollo'));
        $productos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $total = count($productos);
        $resumen = "Hay $total productos registrados. Algunos son: " . implode(", ", array_slice($productos, 0, 3)) . ".";
        $respuesta = $nombre ? "Claro que sí, $nombre. $resumen" : $resumen;
    } elseif (stripos($textoReconocido, 'secretario') !== false || stripos($textoReconocido, 'secretaria') !== false) {

        // Listar las secretarías y sus secretarios
        $secretariasList = array_map(function ($sec, $secName) {
            return ucfirst($sec) . ": " . $secName;
        }, array_keys($mapaSecretarias), $mapaSecretarias);
        $respuesta = "Las secretarías y sus secretarios son: " . implode("; ", array_slice($secretariasList, 0, 5)) . ".";
    } else
    if (
        stripos($textoReconocido, 'factor') !== false ||
        stripos($textoReconocido, 'factor inestabilidad') !== false ||
        stripos($textoReconocido, 'factor inestabilidad del municipio') !== false ||
        stripos($textoReconocido, 'total de carencias de ') !== false ||
        stripos($textoReconocido, 'cantidad de carencias de ') !== false ||
        stripos($textoReconocido, 'cual es el indice de ') !== false ||
        stripos($textoReconocido, 'porcentaje de') !== false ||
        stripos($textoReconocido, 'carencia') !== false ||
        stripos($textoReconocido, 'carencias') !== false ||
        stripos($textoReconocido, 'falta de') !== false ||
        stripos($textoReconocido, 'metros') !== false ||
        stripos($textoReconocido, 'unidad') !== false ||
        stripos($textoReconocido, 'unidades') !== false ||
        stripos($textoReconocido, 'porcentaje') !== false ||
        stripos($textoReconocido, 'factores de inestabilidad') !== false
    ) {

        // "¿Cuántos [tipo_medicion] de [factor] le hacen falta al municipio de [municipio]?"

        // Normalizo el texto reconocido
        $textoNormalizado = normalizeText($textoReconocido);

        $factorDetectado = null;
        $tipoMedicionDetectado = null;
        $municipioPregunta = null;
        $factorEncontrado = null;
        $esPreguntaSobreFactor = false;

        // 1. Buscar si hay algún factor en el texto
        $stmt = $pdo->prepare("SELECT tipo, tipo_medicion FROM " . $db->getTable('tbl_factores'));
        $stmt->execute();
        $factores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($factores as $factor) {
            $tipoNormalizado = normalizeText(trim($factor['tipo']));
            if (!empty($tipoNormalizado) && stripos($textoNormalizado, $tipoNormalizado) !== false) {
                $factorDetectado = $factor['tipo'];
                $tipoMedicionDetectado = $factor['tipo_medicion'];
                $esPreguntaSobreFactor = true;
                break;
            }
        }

        // 2. Buscar si hay algún municipio en el texto
        $stmt = $pdo->prepare("SELECT DISTINCT municipio FROM " . $db->getTable('tbl_ciudades_accion_unificada'));
        $stmt->execute();
        $municipios = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($municipios as $municipio) {
            if (stripos($textoNormalizado, normalizeText($municipio)) !== false) {
                $municipioPregunta = $municipio;
                break;
            }
        }

        // 3. Si se detectó municipio y factor, hacemos la consulta
        if ($municipioPregunta && $factorDetectado) {
            $stmt = $pdo->prepare("
        SELECT 
            tbl_ciudades_accion_unificada.municipio AS nombre,
            tbl_factores.tipo,
            tbl_factores.tipo_medicion,
            SUM(tbl_ingreso_informacion.valor) AS total
        FROM " . $db->getTable('tbl_ciudades_accion_unificada') . "
        INNER JOIN " . $db->getTable('tbl_vereda') . "
            ON tbl_ciudades_accion_unificada.codigo_muncipio = tbl_vereda.municipio_id
        INNER JOIN " . $db->getTable('tbl_ingreso_informacion') . "
            ON tbl_vereda.id = tbl_ingreso_informacion.tbl_vereda_id
        INNER JOIN " . $db->getTable('tbl_factores') . "
            ON tbl_ingreso_informacion.tbl_factor_id = tbl_factores.id
        WHERE tbl_ciudades_accion_unificada.municipio = :municipio
            AND tbl_factores.tipo = :tipo
        GROUP BY tbl_ciudades_accion_unificada.municipio, tbl_factores.tipo
    ");
            $stmt->execute([
                ':municipio' => $municipioPregunta,
                ':tipo' => $factorDetectado
            ]);
            $factorEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // 4. Armar la respuesta
        if ($municipioPregunta && $factorDetectado) {
            if ($factorEncontrado) {
                $respuesta = "Claro, en el municipio de {$municipioPregunta} faltan " .
                    number_format($factorEncontrado['total']) . " " .
                    $factorEncontrado['tipo_medicion'] . " en " .
                    strtolower($factorEncontrado['tipo']) . ".";
            } else {
                $respuesta = "Lo siento, no encontré información para el factor '{$factorDetectado}' en el municipio de {$municipioPregunta}.";
            }
        } else {
            $respuesta = "Por favor, formula tu pregunta mencionando un municipio y un factor específico para poder ayudarte.";
        }

        $data['respuesta'] = $respuesta;
    } else {
        // Respuesta por defecto si no se reconoce el tema
        $respuesta = "No tengo información específica sobre ese tema. ¿Puedes ser más específico?";
    }
}
