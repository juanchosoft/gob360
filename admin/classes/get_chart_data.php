<?php

chdir(__DIR__ . '/../../');

require 'admin/include/generic_classes.php'; 
require __DIR__ . '/ApiPolicia.php';



header('Content-Type: application/json'); 


$data = [
    'categoria' => $_GET['categoria'] ?? 'hurtos',
    'anio1' => $_GET['anio1'] ?? $_GET['anio'] ?? (date('Y') - 1), 
    'anio2' => $_GET['anio2'] ?? null, 
]; 

$categoria = $data['categoria'];
$api = new ApiPolicia();

$highcharts_data = [];
$series_data = [];
$categories = [];
$title = '';
$chart_type = 'column'; 
$result = ['valid' => false, 'data' => []]; 


if ($categoria === 'homicidios') {

    if ($data['anio2'] !== null) { 

        $result = $api->cargaGraficoHomicidiosComparativo($data); 

        if ($result['valid'] && !empty($result['data'])) {
            $series_data = $result['data'] ?? []; 
            $categories  = $result['categories'] ?? [];
            $title       = $result['title'] ?? "Comparativa de Homicidios por Género ({$data['anio1']} vs {$data['anio2']})";
            $chart_type  = $result['chart_type'] ?? 'column'; 
        } else {
            $title_base = $result['title'] ?? "Sin datos de Homicidios comparativos.";
            $title = $title_base;
            $series_data = [];
            $categories = [];
            $result['valid'] = true; 
        }
        
    } else {
        // --- CASO 2: HOMICIDIOS DE UN SOLO AÑO 
 
        $data['anio'] = $data['anio1']; 

        $result = $api->cargaGraficoHomicidiosDona($data);
        $chart_type = 'bar'; 

        if ($result['valid'] && !empty($result['data'])) {

            $totalCasos = 0;
            $categories_homicidios = [];
            $data_values_homicidios = []; 

            foreach ($result['data'] as $item) {
                $hechos = (int)($item['total_hechos'] ?? 0);
                $totalCasos += $hechos;

                $categories_homicidios[] = ucfirst(strtolower($item['sexo'] ?? 'Sin Dato'));
                $data_values_homicidios[] = $hechos;
            }

            $series_data = [[
                'name' => 'Casos Homicidios ' . $data['anio'],
                'data' => $data_values_homicidios,
                'type' => 'bar' 
            ]];

            $categories = $categories_homicidios;
            $title = 'Distribución de Homicidios por Género (Total: ' . $totalCasos . ')';
            $result['valid'] = true;

        } else {
            $title = $result['title'] ?? 'Sin datos de Homicidios por género.'; 
            $highcharts_data = [];
            $result['valid'] = true;
        }
    }


}elseif ($categoria === 'hurto_dona_comparativo' && $data['anio2'] !== null) {
    
    $result = $api->cargaGraficoHurtoComparativo($data); 

    if ($result['valid'] && !empty($result['data'])) {
        
        $series_data = $result['data'] ?? []; 
        $categories  = $result['categories'] ?? [];
        $title       = $result['title'] ?? 'Comparativa de Hurtos por Tipo';
        $chart_type  = $result['chart_type'] ?? 'bar'; 
        
    } else {
        // En caso de error o sin datos
        $title = $result['title'] ?? "Sin datos de Hurtos comparativos para {$data['anio1']} y {$data['anio2']}.";
        
        if (isset($result['debug_url']) && is_array($result['debug_url'])) {
             $url_list = implode(', ', $result['debug_url']);
             $title = $title_base . " (DEBUG URLs: $url_list)";
        } else {
             $title = $title_base;
        }        
        
        $series_data = [];
        $categories = [];
        $result['valid'] = true; 
    }


}elseif ($categoria === 'amenaza_dona_comparativo' && $data['anio2'] !== null) { 

    $result = $api->cargaGraficoAmenazasComparativo($data); 

    if ($result['valid'] && !empty($result['data'])) {
        
        $series_data = $result['data'] ?? []; 
        $categories  = $result['categories'] ?? [];
        $title       = $result['title'] ?? 'Comparativa de Amenazas por Medio';
        $chart_type  = $result['chart_type'] ?? 'bar';
        
    } else {
        $title_base = $result['title'] ?? "Sin datos de Amenazas comparativos.";
        
        if (isset($result['debug_url']) && is_array($result['debug_url'])) {
             $url_list = implode(', ', $result['debug_url']);
             $title = $title_base . " (DEBUG URLs: $url_list)";
        } else {
             $title = $title_base;
        }        
        
        $series_data = [];
        $categories = [];
        $result['valid'] = true; 
    }


}elseif($categoria === 'hurto_dona'){

    $chart_type = 'pie';
    $data['anio'] = $_GET['anio'] ?? (date('Y') - 1); 
    $result = $api->cargaGraficoHurtoDona($data); 
    
    if ($result['valid'] && !empty($result['data'])) {
        $totalCasos = 0;
        foreach ($result['data'] as $item) {

            $hechos = (int)($item['total_hechos'] ?? 0); 
            $totalCasos += $hechos;
            
            $highcharts_data[] = [
                'name' => ucfirst(strtolower($item['tipo_de_hurto'] ?? 'Sin Dato')),
                'y'    => $hechos
            ];
        }
        $title = 'Distribución de Hurtos por Tipo (' . ($result['query_year'] ?? 'Año Reciente') . ')';
    } else {
        $title = $result['title'] ?? 'Sin datos de Hurtos por Tipo.';
        $highcharts_data = [];
        $result['valid'] = true; 
    }

}elseif ($categoria === 'amenaza_dona') {

    $data['anio'] = $_GET['anio'] ?? (date('Y') - 1); 
    
    $result = $api->cargaGraficoAmenazasDona($data); 
    
    if ($result['valid'] && !empty($result['data'])) {
        $totalCasos = 0;
        $categories_amenaza = []; 
        $data_values_amenaza = []; 

        
        foreach ($result['data'] as $item) {
            $hechos = (int)($item['total_hechos'] ?? 0); 
            $totalCasos += $hechos;

            $categories_amenaza[] = ucfirst(strtolower($item['armas_medios'] ?? 'Sin Dato'));
            $data_values_amenaza[] = $hechos;

        }

        $series_data = [[
            'name' => 'Casos ' . ($result['query_year'] ?? 'Año Reciente'),
            'data' => $data_values_amenaza,
            'type' => 'bar' 
        ]];
        $categories = $categories_amenaza; 

        $title = 'Distribución de Amenazas por Armas/Medios (' . ($result['query_year'] ?? 'Año Reciente') . ')';

        $chart_type = $result['chart_type'] ?? 'bar'; 


    } else {
        $title = $result['title'] ?? 'Sin datos de Amenazas por Armas/Medios.';
        $highcharts_data = [];
        $result['valid'] = true; 
        $chart_type = $result['chart_type'] ?? 'bar';

    }

}elseif ($categoria === 'desaparecidos_desp') {


    $data['anio1'] = $data['anio1'] ?? (date('Y') - 1); 
    $data['anio2'] = $data['anio2'] ?? null; 

    $result = $api->cargaGraficoDesaparecidosDesp($data); 

    if ($result['valid'] && !empty($result['dataSeries'])) {
        
        $series_data = $result['dataSeries'] ?? []; 
        $categories  = $result['categories'] ?? [];
        $title       = $result['title'] ?? 'Desaparecidos';
        $chart_type  = $result['chart_type'] ?? 'column';

    } else {
        $title = $result['title'] ?? 'Sin datos de Desaparecidos.';
        $series_data = [];
        $categories = [];
        $chart_type = 'column'; 
        $result['valid'] = true; 
    }

}else { 
    
    $result = $api->cargaCategoriaGrafico($data); 

    if ($result['valid'] && !empty($result['dataSeries'])) {
        
        $series_data = $result['dataSeries'];
        $categories      = $result['categories'] ?? [];
        $title           = $result['title'] ?? 'Estadísticas de ' . ucfirst($categoria);
        $chart_type      = $result['chart_type'] ?? 'column';
        
    } elseif (!$result['valid']) {
        
        $title = $result['title'] ?? 'Error en la API: ' . ($result['error'] ?? 'Desconocido');
        $series_data = []; 
        $categories = [];      
        if (isset($result['debug_url'])) {
             $title .= " (URL: " . $result['debug_url'] . ")";
        }

    } else {
        $title = 'Sin datos para ' . ucfirst($categoria);
        $series_data = [];
        $categories = [];
    }
}


echo json_encode([


    'data' => $chart_type === 'pie' ? $highcharts_data : [], 
    'dataSeries' => $series_data,    

    'title' => $title,
    'categories' => $categories, 
    'valid' => $result['valid'] ?? false,
    'chart_type' => $chart_type, 
    'debug_urls' => $result['debug_urls'] ?? null, 

]);
?>