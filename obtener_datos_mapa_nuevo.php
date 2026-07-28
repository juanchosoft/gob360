<?php
include '../include/generic_classes.php'; 
include '../classes/ActualizacionInformacion.php'; 
include '../db/coloress.php'; 


function determineMapColorPHP($cantidadNueva, $secretariaId) {
    $quantity = (int)$cantidadNueva; 
    if (!is_numeric($cantidadNueva) || $quantity < 0) {
        return "#CCCCCC"; // Gris por defecto
    }
    

    if ($secretariaId === Util::getSecretariaIdHacienda()) { 
        if ($quantity > 200) return "#0000FF"; // Azul Fuerte
        return "#ADD8E6"; // Azul Claro
    } else {
        // Lógica general (Semáforo)
        if ($quantity === 0) {
            return "#00FF00"; // Verde
        } elseif ($quantity <= 50) {
            return "#FFFF00"; // Amarillo
        } elseif ($quantity <= 150) {
            return "#FFA500"; // Naranja
        } else {
            return "#FF0000"; // Rojo
        }
    }
}


$factorId = $_REQUEST['factorId'] ?? 1; // Usar valor por defecto si no viene
$departamentoId = $_REQUEST['departamentoId'] ?? Util::getDepartamentoPrincipal();
$secretariaId = $_REQUEST['secretariaId'] ?? Util::getSecretariaPrincipal();



$responseMapa = ActualizacionInformacion::obtenerDatosMapaInicial($departamentoId, $factorId);
$datosMapaDB = [];

if ($responseMapa['output']['valid']) {
    foreach ($responseMapa['output']['data'] as $data) {
        $datosMapaDB[$data['codigo_municipio']] = $data['valor_total'];
    }
}


$coloresMunicipios = [];
if (isset($santander) && is_array($santander)) {
    foreach ($santander as $key => $value) { 
        $municipio_codigo = $value['codigo_muncipio'];
        $valor_actual = $datosMapaDB[$municipio_codigo] ?? 0;
        
        $nuevo_color = determineMapColorPHP($valor_actual, $secretariaId);
        

        $coloresMunicipios[] = [
            'codigo' => $municipio_codigo,
            'nombre_mapa' => strtoupper($value['nombre_mapa']),
            'color' => $nuevo_color
        ];
    }
}


header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $coloresMunicipios]);

?>