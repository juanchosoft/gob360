<?php

class ApiPolicia
{
    public function cargaHurto($data)
    {
        try {
            $endpoint = "https://www.datos.gov.co/resource/6sqw-8cg5.json";

            $draw = intval($data['draw'] ?? 1);
            $start = intval($data['start'] ?? 0);
            $length = intval($data['length'] ?? 10);
            $searchValue = trim($data['search']['value'] ?? '');
            $orderColumnIndex = $data['order'][0]['column'] ?? 0;
            $orderDirection = $data['order'][0]['dir'] ?? 'asc';

            $columns = [
                "municipio",
                "codigo_dane",
                "armas_medios",
                "fecha_hecho",
                "genero",
                "grupo_etario",
                "tipo_de_hurto",
                "cantidad"
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? "fecha_hecho";

            $params = [
                "\$select" => implode(',', array_merge(['departamento'], $columns)),
                "\$order" => "$orderColumn $orderDirection",
                "\$limit" => $length,
                "\$offset" => $start,
            ];

            $where = "departamento='SANTANDER'";

            // Búsqueda
            if (!empty($searchValue)) {
                $searchValue = strtoupper($searchValue);
                $searchConditions = [];

                foreach (['municipio', 'genero', 'grupo_etario', 'tipo_de_hurto'] as $col) {
                    $searchConditions[] = "upper($col) like '%$searchValue%'";
                }

                $where .= " AND (" . implode(" OR ", $searchConditions) . ")";
            }

            $params["\$where"] = $where;

            $url = $endpoint . '?' . http_build_query($params);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('cURL error: ' . curl_error($ch));
            }

            curl_close($ch);

            $apiData = json_decode($response, true);

            $recordsFiltered = 2000;
            $recordsTotal = 2000;

            if (count($apiData) < $length) {
                $recordsFiltered = $start + count($apiData);
                $recordsTotal = $recordsFiltered;
            }

            return [
                "draw" => $draw,
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $apiData,
            ];
        } catch (PDOException $e) {
            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => $e->getMessage()
            ];
        }
    }

    public function cargaCategoria($data)
    {
        try {
            $mapaRecursos = [
                'hurtos'           => '6sqw-8cg5.json',
                'amenazas'         => 'meew-mguv.json',
                'desplazamientos'  => 'api/views/uqfr-drs5/query.json',
                'homicidios'       => 'm8fd-ahd9.json',
                'secuestros'       => 'd7zw-hpf4.json',
            ];

            $columnasPorCategoria = [
                'hurtos' => [
                    "municipio", "codigo_dane", "armas_medios", "fecha_hecho", "genero", "grupo_etario", "tipo_de_hurto", "cantidad"
                ],
                'amenazas' => [
                    "municipio", "codigo_dane", "armas_medios", "fecha_hecho", "genero", "grupo_etario", "cantidad"
                ],
                'homicidios' => [
                    "fecha_hecho", "municipio", "zona", "sexo", "cantidad"
                ],
                'desplazamientos' => [
                    "a_o", "mes", "edad", "genero", "zona", "discapacidad"
                ],
                'secuestros' => [
                    "fecha_hecho", "municipio", "tipo_delito", "cantidad"
                ],
            ];


            $camposClave = [
                'hurtos' => ['municipio' => 'municipio', 'fecha' => 'fecha_hecho'],
                'amenazas' => ['municipio' => 'municipio', 'fecha' => 'fecha_hecho'],
                'homicidios' => ['municipio' => 'municipio', 'fecha' => 'fecha_hecho'],
                'secuestros' => ['municipio' => 'municipio', 'fecha' => 'fecha_hecho'],
                'desplazamientos' => ['municipio' => 'municipio_hecho', 'fecha' => 'a_o'], 
            ];


            $headersPorCategoria = [
                'hurtos' => [
                    'Municipio', 'Código Dane', 'Armas Medios', 'Fecha Hecho', 'Género', 'Grupo Etario', 'Tipo de Hurto', 'Cantidad'
                ],
                'amenazas' => [
                    'Municipio', 'Código Dane', 'Armas Medios', 'Fecha Hecho', 'Género', 'Grupo Etario', 'Cantidad'
                ],
                'homicidios' => [
                    "Fecha Hecho", "Municipio", "Zona", "Sexo", "Cantidad"
                ],
                'desplazamientos' => [
                    "Año", "Mes", "Edad", "Género", "Zona", "Discapacidad"
                ],
                'secuestros' => [
                    "Fecha Hecho", "Municipio","Tipo Delito", "Cantidad"
                ]
            ];

            $columnasTextoPorCategoria = [
                'hurtos' => [
                    "municipio", "armas_medios", "genero", "grupo_etario", "tipo_de_hurto",
                ],
                'amenazas' => [
                    "municipio", "armas_medios", "genero", "grupo_etario",
                ],
                'homicidios' => [
                    "municipio", 
                    "zona",
                    "sexo",
                ],
                'desplazamientos' => [
                    "mes", "genero", "zona", "discapacidad"
                ],
                'secuestros' => [
                    "departamento", "municipio", "tipo_delito",
                ],
            ];

            $categoria = $data['categoria'] ?? 'hurtos';
            if (!isset($mapaRecursos[$categoria])) {
                throw new Exception("Categoría inválida: $categoria");
            }

            $endpoint = "https://www.datos.gov.co/resource/" . $mapaRecursos[$categoria];
            $columns = $columnasPorCategoria[$categoria];
            $headers = $headersPorCategoria[$categoria];
            $campos = $camposClave[$categoria];


            $municipioFiltro = $data['municipio'] ?? '';
            $fechaInicioFiltro = $data['fechaInicio'] ?? '';
            $fechaFinFiltro = $data['fechaFin'] ?? '';


            $draw = intval($data['draw'] ?? 1);
            $start = intval($data['start'] ?? 0);
            $length = intval($data['length'] ?? 10);
            $searchValue = trim($data['search']['value'] ?? '');
            $orderColumnIndex = $data['order'][0]['column'] ?? 0;
            $orderDirection = $data['order'][0]['dir'] ?? 'asc';

            $orderColumn = $columns[$orderColumnIndex] ?? $columns[0];


            $where = []; 
            $currentYear = date('Y');

            switch ($categoria) {
                case 'hurtos':
                case 'amenazas':
                    $where[] = "departamento='SANTANDER'";
                    $where[] = "substring({$campos['fecha']}, 7, 4) = '$currentYear'";
                    break;

                case 'secuestros':
                    $where[] = "cod_depto = '68'"; 
                    //$where[] = "date_extract_y({$campos['fecha']}) = '$currentYear'"; 

                    break;

                case 'desplazamientos':
                    $where[] = "cod_municipio between 68000 and 68999";
                    $where[] = "a_o = '2023'"; 
                    break;


                case 'homicidios':
                    $where[] = "departamento='SANTANDER'";
                    $where[] = "date_extract_y({$campos['fecha']}) = '$currentYear'";
                    break;


                default:
                    break; 
            }


            if (!empty($municipioFiltro)) {
                $where[] = "{$campos['municipio']} = '" . $municipioFiltro . "'";
            }


            if (!empty($fechaInicioFiltro) && !empty($fechaFinFiltro)) {
                
                $where = array_filter($where, function($cond)  use ($categoria){

                    return strpos($cond, 'substring(') === false && strpos($cond, 'a_o =') === false;
                });
                
                $fechaInicioSoQL = $fechaInicioFiltro . "T00:00:00.000";
                $fechaFinSoQL = $fechaFinFiltro . "T23:59:59.000";

                if ($categoria === 'desplazamientos') {
                    $anoInicio = substr($fechaInicioFiltro, 0, 4);
                    $anoFin = substr($fechaFinFiltro, 0, 4);

                    if ($anoInicio === $anoFin) {
                        $where[] = "a_o = '$anoInicio'";
                    }else{
                        $where[] = "a_o between '$anoInicio' and '$anoFin'";

                    }

                } else {
                    $where[] = "{$campos['fecha']} between '{$fechaInicioSoQL}' and '{$fechaFinSoQL}'";
                }
            }


            if (!empty($searchValue)) {
                $searchValue = strtoupper($searchValue);
                $searchConditions = [];

                $columnasTexto = $columnasTextoPorCategoria[$categoria] ?? [];

                foreach ($columnasTexto as $col) {
                    $searchConditions[] = "upper($col) like '%$searchValue%'";
                }

                if (!empty($searchConditions)) {

                    $where[] = "(" . implode(" OR ", $searchConditions) . ")";
                }

            }


            $whereString = implode(" AND ", $where);
            if (empty($whereString)) {
                $whereString = "1=1";
            }


            $selectCols = $columns;
 
            if (in_array($categoria, ['hurtos', 'amenazas', 'homicidios'])) {

                if (!in_array('departamento', $selectCols)) {
                    $selectCols = array_merge(['departamento'], $columns);
                }
            }

            $params = [
                "\$select" => implode(',', $selectCols),
                "\$order"  => "$orderColumn $orderDirection",
                "\$limit"  => $length,
                "\$offset" => $start,
                "\$where"  => $whereString,
            ];

            $url = $endpoint . '?' . http_build_query($params);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                throw new Exception('Error obteniendo datos');
            }

            $apiData = json_decode($response, true);
            if (!is_array($apiData)) {
                throw new Exception('Respuesta no válida de la API');
            }

            // 3. Petición para el count
            $totalCount = 2000;
            if ($start === 0 || $start > 0 && count($apiData) === 0) {
                $countParams = [
                    "\$select" => "count(*)",
                    "\$where" => $whereString,
                ];
                $urlCount = $endpoint . '?' . http_build_query($countParams);

                $chCount = curl_init($urlCount);
                curl_setopt($chCount, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chCount, CURLOPT_SSL_VERIFYPEER, false);
                $countResponse = curl_exec($chCount);
                curl_close($chCount);

                $countData = json_decode($countResponse, true);
                $totalCount = isset($countData[0]['count']) ? intval($countData[0]['count']) : count($apiData);
            }else{
                $totalCount = $data['recordsTotal'] ?? 2000;

            }

            return [
                "draw" => $draw,
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $totalCount,
                "data" => $apiData,
                "columns" => $columns,
                "headers" => $headers,
            ];
        } catch (Exception $e) {
            return [
                "draw" => intval($data['draw'] ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => $e->getMessage(),
                "columns" => $columnasPorCategoria[$categoria] ?? [], 
                "headers" => $headersPorCategoria[$categoria] ?? [], 

            ];
        }
    }

    // public function cargaCategoriaGrafico($data)
    // {
    //     try {
    //         $mapaRecursos = [
    //             'hurtos'           => '6sqw-8cg5.json',
    //             'amenazas'         => 'meew-mguv.json',
    //             'desplazamientos'  => 'uqfr-drs5.json',
    //             'homicidios'       => 'm8fd-ahd9.json',
    //             'secuestros'       => 'd7zw-hpf4.json',
    //         ];

    //         $columnasPorCategoria = [
    //             'hurtos' => [
    //                 "municipio",
    //                 "codigo_dane",
    //                 "armas_medios",
    //                 "fecha_hecho",
    //                 "genero",
    //                 "grupo_etario",
    //                 "tipo_de_hurto",
    //                 "cantidad"
    //             ],
    //             'amenazas' => [
    //                 "municipio",
    //                 "codigo_dane",
    //                 "armas_medios",
    //                 "fecha_hecho",
    //                 "genero",
    //                 "grupo_etario",
    //                 "cantidad"
    //             ],
    //             'homicidios' => [
    //                 "fecha_hecho",
    //                 "municipio",
    //                 "zona",
    //                 "sexo",
    //                 "cantidad"
    //             ],
    //             'desplazamientos' => [
    //                 "a_o",
    //                 "mes",
    //                 "edad",
    //                 "genero",
    //                 "zona",
    //                 "discapacidad"
    //             ],
    //             'secuestros' => [
    //                 "fecha_hecho",
    //                 "cod_depto",
    //                 "departamento",
    //                 "cod_muni",
    //                 "municipio",
    //                 "tipo_delito",
    //                 "cantidad"
    //             ],

    //         ];

    //         $headersPorCategoria = [
    //             'hurtos' => [
    //                 'Municipio',
    //                 'Código Dane',
    //                 'Armas Medios',
    //                 'Fecha Hecho',
    //                 'Género',
    //                 'Grupo Etario',
    //                 'Tipo de Hurto',
    //                 'Cantidad'
    //             ],
    //             'amenazas' => [
    //                 'Municipio',
    //                 'Código Dane',
    //                 'Armas Medios',
    //                 'Fecha Hecho',
    //                 'Género',
    //                 'Grupo Etario',
    //                 'Cantidad'
    //             ],
    //             'homicidios' => [
    //                 "Municipio",
    //                 "Fecha Hecho",
    //                 "Zona",
    //                 "Sexo",
    //                 "Cantidad"
    //             ],
    //             'desplazamientos' => [
    //                 "Año",
    //                 "Mes",
    //                 "Edad",
    //                 "Género",
    //                 "Zona",
    //                 "Discapacidad"
    //             ],
    //             'secuestros' => [
    //                 "Fecha Hecho",
    //                 "cod_depto",
    //                 "Departamento",
    //                 "Cod Municipio",
    //                 "Municipio",
    //                 "Tipo Delito",
    //                 "Cantidad"
    //             ]
    //         ];

    //         $columnasTextoPorCategoria = [
    //             'hurtos' => [
    //                 "municipio",
    //                 "armas_medios",
    //                 "genero",
    //                 "grupo_etario",
    //                 "tipo_de_hurto",
    //             ],
    //             'amenazas' => [
    //                 "municipio",
    //                 "armas_medios",
    //                 "genero",
    //                 "grupo_etario",
    //             ],
    //             'homicidios' => [
    //                 "Municipio",
    //                 "Fecha Hecho",
    //                 "Zona",
    //                 "Sexo",
    //             ],
    //             'desplazamientos' => [
    //                 "Mes",
    //                 "Género",
    //                 "Zona",
    //                 "Discapacidad"
    //             ],
    //             'secuestros' => [
    //                 "fecha_hecho",
    //                 "cod_depto",
    //                 "departamento",
    //                 "municipio",
    //                 "tipo_delito",
    //             ],
    //         ];

    //         $categoria = $data['categoria'] ?? 'hurtos';
    //         if (!isset($mapaRecursos[$categoria])) {
    //             throw new Exception("Categoría inválida: $categoria");
    //         }

    //         $endpoint = "https://www.datos.gov.co/resource/" . $mapaRecursos[$categoria];
    //         $columns = $columnasPorCategoria[$categoria];
    //         $headers = $headersPorCategoria[$categoria];

    //         $searchValue = trim($data['search']['value'] ?? '');
    //         $orderColumnIndex = $data['order'][0]['column'] ?? 0;
    //         $orderDirection = $data['order'][0]['dir'] ?? 'asc';
    //         $orderColumn = $columns[$orderColumnIndex] ?? $columns[0];

    //         $where = "";
    //         $currentYear = date('Y');

    //         switch ($categoria) {
    //             case 'hurtos':
    //             case 'amenazas':
    //                 $where = "departamento='SANTANDER' AND substring(fecha_hecho, 7, 4) = '$currentYear'";
    //                 break;
    //             case 'secuestros':
    //                 $where = "cod_depto='68' AND substring(fecha_hecho, 7, 4) = '$currentYear'";
    //                 break;
    //             case 'desplazamientos':
    //                 $where = "cod_municipio between 68000 and 68999";
    //                 $where[] = "a_o = '$currentYear'"; 

    //                 break;
    //             default:
    //                 $where = "1=1";
    //         }

    //         if (!empty($searchValue)) {
    //             $searchValue = strtoupper($searchValue);
    //             $searchConditions = [];

    //             $columnasTexto = $columnasTextoPorCategoria[$categoria] ?? [];
    //             foreach ($columnasTexto as $col) {
    //                 $searchConditions[] = "upper($col) like '%$searchValue%'";
    //             }

    //             if (!empty($searchConditions)) {
    //                 $where .= " AND (" . implode(" OR ", $searchConditions) . ")";
    //             }
    //         }

    //         // Sin paginación: omitimos limit y offset
    //         $params = [
    //             "\$select" => implode(',', array_merge(['departamento'], $columns)),
    //             "\$order"  => "$orderColumn $orderDirection",
    //             "\$where"  => $where,
    //         ];

    //         $url = $endpoint . '?' . http_build_query($params);

    //         $ch = curl_init($url);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //         $response = curl_exec($ch);
    //         curl_close($ch);

    //         if ($response === false) {
    //             throw new Exception('Error obteniendo datos');
    //         }

    //         $apiData = json_decode($response, true);
    //         if (!is_array($apiData)) {
    //             throw new Exception('Respuesta no válida de la API');
    //         }

    //         return [
    //             "draw" => 1,
    //             "recordsTotal" => count($apiData),
    //             "recordsFiltered" => count($apiData),
    //             "data" => $apiData,
    //             "columns" => $columns,
    //             "headers" => $headers,
    //         ];
    //     } catch (Exception $e) {
    //         return [
    //             "draw" => 1,
    //             "recordsTotal" => 0,
    //             "recordsFiltered" => 0,
    //             "data" => [],
    //             "error" => $e->getMessage()
    //         ];
    //     }
    // }

    
    public function cargaGraficoHomicidiosDona($data)
    {
        try {
            $endpoint = "https://www.datos.gov.co/resource/m8fd-ahd9.json";
            

            //$testYear = 2025; 
            $queryYear = $data['anio'] ?? (date('Y') - 1); // <<-- Agregar esta línea

            $groupCol = 'sexo'; 
            $countCol = 'SUM(cantidad) AS total_hechos'; 

            
            $where = []; 
            
            //$where[] = "departamento='SANTANDER'";
            $where[] = "cod_depto='68'"; 

            $where[] = "fecha_hecho between '$queryYear-01-01T00:00:00.000' and '$queryYear-12-31T23:59:59.999'"; 
            
            $whereString = implode(" AND ", $where);
            
            $groupCol = 'sexo'; 
            $countCol = 'SUM(cantidad) AS total_hechos'; 
            
            $params = [
                "\$select" => "$groupCol, $countCol", 
                "\$where"  => $whereString,
                "\$group"  => $groupCol,             
                "\$limit"  => 10,           
                "\$order"  => "total_hechos DESC",
            ];

            $url = $endpoint . '?' . http_build_query($params);
            // ----------------------------------------------------
            // EJECUCIÓN DE cURL
            // ----------------------------------------------------
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('cURL error: ' . curl_error($ch));
            }
            curl_close($ch);
            // ----------------------------------------------------

            $apiData = json_decode($response, true);
            $title = "Homicidios por Género (API $queryYear)";

            if (!is_array($apiData)|| empty($apiData)) {
                    return [
                    "valid" => false, 
                    "data" => [],
                    "title" => "Error de formato o conexión API. URL: " . $url,
                    "chart_type" => "pie"

                ];
            }
            
            if (empty($apiData)) {
                    return [
                    "valid" => true,
                    "data" => [],
                    "title" => "Sin datos para la consulta del año $queryYear en Santander.". $url,
                ];
            }

            return [
                "valid" => true,
                "data" => $apiData,
                "group_col" => $groupCol, 
                "categoria" => "homicidios_dona_sexo",
                "title" => $title,
            ];

        } catch (Exception $e) {
            return [
                "valid" => false,
                "error" => "Error al obtener datos: " . $e->getMessage(),
                "data" => [],
            ];
        }
    }


    // public function cargaCategoriaGrafico($data)
    //             {
    //                 try {
    //                     $mapaRecursos = [
    //                         'hurtos'           => '6sqw-8cg5.json',
    //                         'amenazas'         => 'meew-mguv.json',
    //                         'desplazamientos'  => 'api/v3/views/uqfr-drs5/query.json',
    //                         'homicidios'       => 'm8fd-ahd9.json',
    //                         'secuestros'       => 'd7zw-hpf4.json',
    //                     ];

    //                     $camposClave = [
    //                         'hurtos'          => ['fecha' => 'fecha_hecho'],
    //                         'amenazas'        => ['fecha' => 'fecha_hecho'],
    //                         'homicidios'      => ['fecha' => 'fecha_hecho'],
    //                         'secuestros'      => ['fecha' => 'fecha_hecho'],
    //                         'desplazamientos' => ['fecha' => 'a_o'], 
    //                     ];

    //                     $categoria = $data['categoria'] ?? 'hurtos';

    //                     $anio1 = $data['anio1'] ?? (date('Y') - 1); 
    //                     $anio2 = $data['anio2'] ?? null; 

    //                     if (!isset($mapaRecursos[$categoria])) {
    //                         throw new Exception("Categoría inválida: $categoria");
    //                     }

    //                     //$endpoint = "https://www.datos.gov.co/resource/" . $mapaRecursos[$categoria];
    //                     $endpointBase = "https://www.datos.gov.co/";
    //                     if ($categoria === 'desplazamientos') {
    //                         // Si es desplazamientos, usa la URL de query (views)
    //                         $endpoint = $endpointBase . $mapaRecursos[$categoria]; 
    //                     } else {
    //                         // Para todas las demás, usa el formato /resource/
    //                         $endpoint = $endpointBase . "resource/" . $mapaRecursos[$categoria]; 
    //                     }
    //                     $campos = $camposClave[$categoria];
                        
    //                     $where = []; 

    //                     $anio = $data['anio'] ?? (date('Y')-1); 


    //                     //$currentYear = date('Y')-1; 
    //                     $groupCol = '';
    //                     $countCol = 'count(*) AS total_hechos'; 
                        
    //                     $fechaCol = $campos['fecha'];
    //                     $jsGroupColName = 'mes_num'; 


    //                     switch ($categoria) {
    //                         case 'hurtos':
    //                         case 'amenazas':

    //                             $groupCol = "substring($fechaCol, 4, 2)"; 
    //                             $where[] = "departamento='SANTANDER'";
                                
    //                             $where[] = "substring($fechaCol, 7, 4) = '$anio'"; 
                                
    //                             $countCol = 'SUM(cantidad) AS total_hechos'; 
    //                             $jsGroupColName = 'mes_num'; 

    //                             break;


    //                         case 'homicidios':

    //                             $groupCol = "date_extract_m($fechaCol)"; 

    //                             $where[] = "substring(municipio, 1, 2) = '68'"; 
    //                             $where[] = "date_extract_y($fechaCol) = '$anio'"; 

    //                             $countCol = 'SUM(cantidad) AS total_hechos'; 
    //                             $jsGroupColName = 'mes_num'; 
    //                             break; 


    //                         case 'secuestros':
                                
    //                             $groupCol = "date_extract_m($fechaCol)"; 
    //                             $where[] = "cod_depto='68'";
                                
    //                             // Filtro por Departamento/DANE
    //                             // if ($categoria === 'homicidios') {
    //                             //     $where[] = "substring(municipio, 1, 2) = '68'"; 
    //                             // } elseif ($categoria === 'secuestros') {
    //                             //     $where[] = "cod_depto='68'";
    //                             // } else { 
    //                             //     $where[] = "departamento='SANTANDER'";
    //                             // }
                                
    //                             $where[] = "date_extract_y($fechaCol) = '$anio'"; 
                                
    //                             $countCol = 'SUM(cantidad) AS total_hechos'; 
    //                             $jsGroupColName = 'mes_num'; 
                                
    //                             break;

    //                         case 'desplazamientos':
    //                             $groupCol = 'genero'; 
    //                             //$where[] = "cod_municipio between 68000 and 68999";
    //                             //$where[] = "cod_municipio = 76111";

    //                             //$testYear = 2016; // Prueba con 2023
    //                             $where[] = "a_o = '$anio'";

    //                             $jsGroupColName = 'genero'; 
    //                             $countCol = 'count(*) AS total_hechos'; 

    //                             break;
                                
    //                         default:
    //                             $groupCol = 'municipio'; 
    //                             $where[] = "1=1";
    //                             $jsGroupColName = 'municipio'; 
    //                     }

    //                     $whereString = implode(" AND ", $where);
    //                     if (empty($whereString)) {
    //                         $whereString = "1=1";
    //                     }
                        
    //                     // PARÁMETROS CRÍTICOS PARA EL GRÁFICO
    //                     $params = [
    //                         "\$select" => "$groupCol AS $jsGroupColName, $countCol", 
    //                         "\$where"  => $whereString,
    //                         "\$group"  => $groupCol,             
    //                         "\$limit"  => 50,                    
                            
    //                         "\$order"  => "$jsGroupColName ASC",   
    //                     ];

    //                     $url = $endpoint . '?' . http_build_query($params);

    //                     // Ejecución de cURL
    //                     $ch = curl_init($url);
    //                     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //                     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //                     $response = curl_exec($ch);

    //                     if (curl_errno($ch)) {
    //                         throw new Exception('cURL error: ' . curl_error($ch));
    //                     }

    //                     curl_close($ch);

    //                     $apiData = json_decode($response, true);
    //                     // if (!is_array($apiData)) {
    //                     //     // Devolvemos la URL para diagnóstico final si sigue fallando
    //                     //     throw new Exception('La API devolvió un error o respuesta vacía. URL: ' . $url);
    //                     // }

    //                     if (!is_array($apiData) || empty($apiData)) {
    //                         $title_error = "Sin datos disponibles para " . ucfirst($categoria) . " en el año " . $anio;
                            
    //                         return [
    //                             "valid" => false,
    //                             "error" => $title_error, 
    //                             "title" => $title_error,
    //                             "data" => [],
    //                             "categories" => [], // Aseguramos que categories esté vacío
    //                             "debug_url" => $url,
    //                         ];

    //                     }


    //                     $chartType = ($categoria === 'desplazamientos') ? 'pie' : 'column';
    //                     $titulosMapa = [
    //                         'hurtos' => 'Hurtos Totales',
    //                         'amenazas' => 'Amenazas',
    //                         'homicidios' => 'Homicidios',
    //                         'secuestros' => 'Secuestros',
    //                         'desplazamientos' => 'Desplazamientos por Género',
    //                     ];

    //                     $title = "Estadísticas de " . ($titulosMapa[$categoria] ?? ucfirst($categoria)) . " ($anio)";

    //                     $dataFormatted = [];
    //                     $categories = [];

    //                     if ($chartType === 'column') {
    //                         $monthMap = [
    //                             '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', 
    //                             '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', 
    //                             '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'
    //                         ];
    //                         $tempData = [];
    //                         foreach ($apiData as $item) {
    //                             // Aseguramos que el mes tenga dos dígitos (ej: "1" se convierte en "01")
    //                             $mes = str_pad($item[$jsGroupColName], 2, '0', STR_PAD_LEFT); 
    //                             $tempData[$mes] = intval($item['total_hechos']);
    //                         }
                            
    //                         // Rellenamos los 12 meses, poniendo 0 si no hay datos
    //                         foreach ($monthMap as $mesNum => $mesNombre) {
    //                             $dataFormatted[] = $tempData[$mesNum] ?? 0;
    //                             $categories[] = $mesNombre;
    //                         }
    //                     } else {
    //                         // Formato para Pie (Ej: Desplazamientos)
    //                         foreach ($apiData as $item) {
    //                             $dataFormatted[] = [
    //                                 'name' => $item[$jsGroupColName],
    //                                 'y'    => intval($item['total_hechos'])
    //                             ];
    //                         }
    //                     }

                                                
    //                     return [
    //                         "valid" => true,
    //                         "data" => $dataFormatted, 
    //                         "categories" => $categories,
    //                         "title" => $title, 
    //                         "chart_type" => $chartType,
    //                         "debug_url" => $url,
    //                     ];

    //                     // return [
    //                     //     "valid" => true,
    //                     //     "data" => $apiData,
    //                     //     "group_col" => $jsGroupColName, 
    //                     //     "categoria" => $categoria,
    //                     //     "debug_url" => $url, 

    //                     // ];

    //                 } catch (Exception $e) {
    //                     return [
    //                         "valid" => false,
    //                         "error" => "Error al obtener datos: " . $e->getMessage(),
    //                         "data" => [],
    //                     ];
    //                 }
    //             }



    public function cargaCategoriaGrafico($data)
        {
            try {
                $mapaRecursos = [
                    'hurtos'           => '6sqw-8cg5.json',
                    'amenazas'         => 'meew-mguv.json',
                    'desplazamientos'  => 'api/v3/views/uqfr-drs5/query.json',
                    'homicidios'       => 'm8fd-ahd9.json',
                    'secuestros'       => 'd7zw-hpf4.json',
                ];

                $camposClave = [
                    'hurtos'          => ['fecha' => 'fecha_hecho'],
                    'amenazas'        => ['fecha' => 'fecha_hecho'],
                    'homicidios'      => ['fecha' => 'fecha_hecho'],
                    'secuestros'      => ['fecha' => 'fecha_hecho'],
                    'desplazamientos' => ['fecha' => 'a_o'], 
                ];

                $categoria = $data['categoria'] ?? 'hurtos';
                
                //OBTENER 2 AÑOS
                $anio1 = $data['anio1'] ?? (date('Y') - 1); 
                $anio2 = $data['anio2'] ?? null; 
                
                if (!isset($mapaRecursos[$categoria])) {
                    throw new Exception("Categoría inválida: $categoria");
                }

                $endpointBase = "https://www.datos.gov.co/";
                $campos = $camposClave[$categoria];
                $fechaCol = $campos['fecha'];
                $jsGroupColName = 'mes_num';
                $groupCol = '';
                $countCol = '';

                // DEFINIR TIPO DE GRÁFICO Y AÑOS A CONSULTAR
                $chartType = ($categoria === 'desplazamientos') ? 'pie' : 'column';
                $aniosAConsultar = [$anio1];

                if ($chartType === 'column' && $anio2 && $anio2 !== $anio1) {
                    $aniosAConsultar[] = $anio2;
                }
                
                $allApiData = [];
                $allUrls = [];

                // BUCLE DE CONSULTA (UNO POR CADA AÑO)
                foreach ($aniosAConsultar as $anio) {
                    
                    $where = []; 

                    if ($categoria === 'desplazamientos') {
                        $endpoint = $endpointBase . $mapaRecursos[$categoria];
                    } else {
                        $endpoint = $endpointBase . "resource/" . $mapaRecursos[$categoria]; 
                    }

                    //por categoría
                    switch ($categoria) {
                        case 'hurtos':
                        case 'amenazas':
                            $groupCol = "substring($fechaCol, 4, 2)"; 
                            $where[] = "departamento='SANTANDER'";
                            $where[] = "substring($fechaCol, 7, 4) = '$anio'"; // Filtro de año dinámico
                            $countCol = 'SUM(cantidad) AS total_hechos'; 
                            break;
                        case 'homicidios':
                            $groupCol = "date_extract_m($fechaCol)"; 
                            $where[] = "substring(municipio, 1, 2) = '68'"; 
                            $where[] = "date_extract_y($fechaCol) = '$anio'"; // Filtro de año dinámico
                            $countCol = 'SUM(cantidad) AS total_hechos'; 
                            break; 
                        case 'secuestros':
                            $groupCol = "date_extract_m($fechaCol)"; 
                            $where[] = "cod_depto='68'";
                            $where[] = "date_extract_y($fechaCol) = '$anio'"; // Filtro de año dinámico
                            $countCol = 'SUM(cantidad) AS total_hechos'; 
                            break;
                        case 'desplazamientos':
                            $groupCol = 'genero'; 
                            $where[] = "a_o = '$anio'"; // Filtro de año dinámico
                            $jsGroupColName = 'genero'; 
                            $countCol = 'count(*) AS total_hechos'; 
                            break;
                    }

                    $whereString = implode(" AND ", $where);
                    
                    $params = [
                        "\$select" => "$groupCol AS $jsGroupColName, $countCol", 
                        "\$where"  => $whereString,
                        "\$group"  => $groupCol,             
                        "\$limit"  => 50,                    
                        "\$order"  => "$jsGroupColName ASC",   
                    ];

                    $url = $endpoint . '?' . http_build_query($params);
                    $allUrls[$anio] = $url; // Guardar URL para debug

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $response = curl_exec($ch);

                    if (curl_errno($ch)) {
                        throw new Exception('cURL error: ' . curl_error($ch));
                    }
                    curl_close($ch);

                    $allApiData[$anio] = json_decode($response, true);
                }
                
                // MANEJO DE ERRORES (Si no hay datos en AÑO 1, AÑO 2 y no es PIE)
                $hasData = array_reduce($allApiData, function($carry, $item) {
                    return $carry || (is_array($item) && !empty($item));
                }, false);

                if (!$hasData) {
                    $aniosStr = implode(' y ', $aniosAConsultar);
                    $title_error = "Sin datos disponibles para " . ucfirst($categoria) . " en los años " . $aniosStr;
                    
                    return [
                        "valid" => false,
                        "title" => $title_error,
                        "dataSeries" => [], // Asegurar el formato
                        "categories" => [], 
                        "chart_type" => $chartType,
                        "debug_url" => $allUrls,
                    ];
                }


                // FORMATO DE SERIES Y CATEGORÍAS
                $dataSeries = [];
                $categories = [];

                if ($chartType === 'column') {
                    $monthMap = [
                        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', 
                        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', 
                        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'
                    ];
                    $categories = array_values($monthMap);
                    
                    foreach ($aniosAConsultar as $anio) {
                        $apiData = $allApiData[$anio] ?? [];
                        $tempData = [];
                        
                        foreach ($apiData as $item) {
                            $mes = str_pad($item[$jsGroupColName], 2, '0', STR_PAD_LEFT); 
                            $tempData[$mes] = intval($item['total_hechos']);
                        }
                        
                        $dataFormatted = [];
                        foreach (array_keys($monthMap) as $mesNum) {
                            $dataFormatted[] = $tempData[$mesNum] ?? 0;
                        }

                        $dataSeries[] = [
                            'name' => "Año $anio",
                            'data' => $dataFormatted,
                        ];
                    }

                    $title = "Comparativa de " . ucfirst($categoria) . " ($anio1 vs " . ($anio2 ?? 'N/A') . ")";

                } else { 
                    $apiData = $allApiData[$anio1] ?? [];
                    foreach ($apiData as $item) {
                        $dataSeries[] = [
                            'name' => $item[$jsGroupColName],
                            'y'    => intval($item['total_hechos'])
                        ];
                    }
                    $title = "Distribución de " . ucfirst($categoria) . " ($anio1)";
                }
                
                // return
                return [
                    "valid" => true,
                    "dataSeries" => $dataSeries,
                    "categories" => $categories, 
                    "title" => $title, 
                    "chart_type" => $chartType,
                    "debug_url" => $allUrls,
                ];

            } catch (Exception $e) {
                return [
                    "valid" => false,
                    "title" => "Error al obtener datos: " . $e->getMessage(),
                    "dataSeries" => [],
                ];
            }
        }

    public function cargaGraficoHurtoDona($data)
        {
            try {
                $endpoint = "https://www.datos.gov.co/resource/6sqw-8cg5.json";
                //$testYear = 2025;
                $anio = $data['anio'] ?? (date('Y')-1); 
                $groupCol = 'tipo_de_hurto';
                
                $where = []; 
                $where[] = "substring(fecha_hecho, 7, 4) = '$anio'"; 
                $where[] = "departamento='SANTANDER'"; 
                
                $whereClause = implode(" AND ", $where);
                
                $select = "COUNT(cantidad) AS total_hechos, $groupCol"; 
                $groupBy = $groupCol;
                $limit = 50000;

                $url = $endpoint . "?\$select=" . urlencode($select) . 
                    "&\$where=" . urlencode($whereClause) . 
                    "&\$group=" . urlencode($groupBy) . 
                    "&\$limit=" . $limit;
                    

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                curl_close($ch);

                $apiData = json_decode($response, true);
                
                if (!is_array($apiData) || empty($apiData)) {
                    return [
                        "valid" => true,
                        "data" => [],
                        "title" => "Sin datos para el año $anio. URL: " . $url,
                        "query_year" => $anio,
                    ];
                }
                
                return [
                    "valid" => true,
                    "data" => $apiData,
                    "group_col" => $groupCol, 
                    "query_year" => $anio,
                ];

            } catch (\Throwable $th) {
                return [
                    "valid" => false,
                    "data" => [],
                    "title" => "Error API o Sin Datos: " . $th->getMessage(),
                ];
            }
        }


    public function cargaGraficoAmenazasDona($data)
    {
        try {
            $endpoint = "https://www.datos.gov.co/resource/meew-mguv.json"; // Amenazas
            $queryYear = $data['anio'] ?? (date('Y') - 1); 

            $groupCol = 'armas_medios'; 
            $countCol = 'SUM(cantidad) AS total_hechos'; 
            
            $where = []; 
            $where[] = "departamento='SANTANDER'"; 

            $where[] = "substring(fecha_hecho, 7, 4) = '$queryYear'"; 
            
            $whereString = implode(" AND ", $where);
            
            $params = [
                "\$select" => "$groupCol, $countCol", 
                "\$where"  => $whereString,
                "\$group"  => $groupCol,             
                "\$limit"  => 20,
                "\$order"  => "total_hechos DESC",
            ];

            $url = $endpoint . '?' . http_build_query($params);


            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('cURL error: ' . curl_error($ch));
            }
            curl_close($ch);


            $apiData = json_decode($response, true);
            
            if (!is_array($apiData) || empty($apiData)) {

                return [
                    "valid" => true, 
                    "data" => [],
                    "title" => "Sin datos de Amenazas por Armas/Medios para el año $queryYear en Santander.",
                    "query_year" => $queryYear
                ];
            }

            return [
                "valid" => true,
                "data" => $apiData,
                "group_col" => $groupCol, 
                "categoria" => "amenaza_dona_armas",
                "query_year" => $queryYear,
                "chart_type" => "bar" 

            ];

        } catch (Exception $e) {
            return [
                "valid" => false,
                "error" => "Error al obtener datos de Amenazas: " . $e->getMessage(),
                "data" => [],
            ];
        }
    }



    public function cargaGraficoHurtoComparativo(array $data)
    {
        try {
            $endpoint = "https://www.datos.gov.co/resource/6sqw-8cg5.json"; // Hurtos
            $anio1 = $data['anio1'] ?? (date('Y') - 1);
            $anio2 = $data['anio2'] ?? (date('Y') - 2); 

            $aniosAConsultar = [$anio1, $anio2];
            $allApiData = [];
            $allUrls = [];
            $groupCol = 'tipo_de_hurto';
            $countCol = 'SUM(cantidad) AS total_hechos';

            // 1. BUCLE DE CONSULTA 
            foreach ($aniosAConsultar as $anio) {
                
                $where = []; 
                $where[] = "departamento='SANTANDER'";
                $where[] = "substring(fecha_hecho, 7, 4) = '$anio'"; // Filtro de año
                $whereString = implode(" AND ", $where);
                
                $params = [
                    "\$select" => "$groupCol, $countCol", 
                    "\$where"  => $whereString,
                    "\$group"  => $groupCol,             
                    "\$limit"  => 50,                    
                    "\$order"  => "total_hechos DESC",   
                ];

                $url = $endpoint . '?' . http_build_query($params);
                $allUrls[$anio] = $url;

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                curl_close($ch);

                if (curl_errno($ch)) {
                    throw new Exception("cURL error para el año $anio: " . curl_error($ch));
                }

                $apiData = json_decode($response, true);

                if (!is_array($apiData) || (isset($apiData[0]['error']))) {
                    $apiData = []; 
                }
                $allApiData[$anio] = $apiData;
            }
            
            // 2. COMBINACIÓN Y FORMATEO DE DATOS
            
            $allCategories = [];
            $dataFormatted = [];
            
            foreach ($aniosAConsultar as $anio) {
                $dataFormatted[$anio] = [];
                foreach ($allApiData[$anio] as $item) {
                    $category = $item[$groupCol];
                    $total = intval($item['total_hechos']);
                    
                    $allCategories[$category] = true; 
                    $dataFormatted[$anio][$category] = $total;
                }
            }

            $categories = array_keys($allCategories);
            sort($categories);
            
            $series_data = [];

            foreach ($aniosAConsultar as $anio) {
                $data_for_series = [];
                foreach ($categories as $category) {
                    $data_for_series[] = $dataFormatted[$anio][$category] ?? 0;
                }
                
                $series_data[] = [
                    'name' => (string)$anio,
                    'data' => $data_for_series,
                ];
            }
            
            $title = "Hurtos por Tipo: Comparativa {$anio1} vs {$anio2}";

            $totalItems = array_reduce($series_data, function($carry, $series) {
                return $carry + array_sum($series['data']);
            }, 0);

            if ($totalItems === 0) {
                 $title = "Sin datos de Hurtos comparativos para {$anio1} y {$anio2} en SANTANDER.";
                 $series_data = [];
                 $categories = [];
            }
            
            return [
                "valid" => true,
                "data" => $series_data, 
                "categories" => $categories,
                "chart_type" => "bar",
                "title" => $title,
                "debug_url" => $allUrls, 

            ];

        } catch (Exception $e) {
            return [
                "valid" => false,
                "title" => "Error al obtener datos comparativos: " . $e->getMessage(),
                "data" => [],
            ];
        }
    }
        

    public function cargaGraficoAmenazasComparativo(array $data)
    {
        try {
            $endpoint = "https://www.datos.gov.co/resource/meew-mguv.json"; 
            $anio1 = $data['anio1'] ?? (date('Y') - 1);
            $anio2 = $data['anio2'] ?? (date('Y') - 2); 

            $aniosAConsultar = [$anio1, $anio2];
            $allApiData = [];
            $allUrls = [];
            
            $groupCol = 'armas_medios'; 
            $countCol = 'SUM(cantidad) AS total_hechos';

            foreach ($aniosAConsultar as $anio) {
                $where = []; 
                $where[] = "departamento='SANTANDER'";
                $where[] = "substring(fecha_hecho, 7, 4) = '$anio'"; 
                $whereString = implode(" AND ", $where);
                
                $params = [
                    "\$select" => "$groupCol, $countCol", 
                    "\$where"  => $whereString,
                    "\$group"  => $groupCol,             
                    "\$limit"  => 50,                    
                    "\$order"  => "total_hechos DESC",   
                ];

                $url = $endpoint . '?' . http_build_query($params);
                $allUrls[$anio] = $url;

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                curl_close($ch);

                if (curl_errno($ch)) {
                    throw new Exception("cURL error para el año $anio: " . curl_error($ch));
                }

                $apiData = json_decode($response, true);

                if (!is_array($apiData) || (isset($apiData[0]['error']))) {
                    $apiData = []; 
                }
                $allApiData[$anio] = $apiData;
            }
            
            // 2. COMBINACIÓN Y FORMATEO DE DATOS
            $allCategories = [];
            $dataFormatted = [];
            
            foreach ($aniosAConsultar as $anio) {
                $dataFormatted[$anio] = [];
                foreach ($allApiData[$anio] as $item) {
                    $category = $item[$groupCol];
                    $total = intval($item['total_hechos']);
                    
                    $allCategories[$category] = true; 
                    $dataFormatted[$anio][$category] = $total;
                }
            }

            $categories = array_keys($allCategories);
            sort($categories);
            
            $series_data = [];

            foreach ($aniosAConsultar as $anio) {
                $data_for_series = [];
                foreach ($categories as $category) {
                    $data_for_series[] = $dataFormatted[$anio][$category] ?? 0;
                }
                
                $series_data[] = [
                    'name' => (string)$anio,
                    'data' => $data_for_series,
                ];
            }
            
            $title = "Amenazas por Medio: Comparativa {$anio1} vs {$anio2}"; 

            $totalItems = array_reduce($series_data, function($carry, $series) {
                return $carry + array_sum($series['data']);
            }, 0);

            if ($totalItems === 0) {
                $title = "Sin datos de Amenazas comparativos para {$anio1} y {$anio2} en SANTANDER.";
                $series_data = [];
                $categories = [];
            }
            
            return [
                "valid" => true,
                "data" => $series_data, 
                "categories" => $categories,
                "chart_type" => "bar",
                "title" => $title,
                "debug_url" => $allUrls, 
            ];

        } catch (Exception $e) {
            return [
                "valid" => false,
                "title" => "Error al obtener datos comparativos de Amenazas: " . $e->getMessage(),
                "data" => [],
            ];
        }
    }


    public function cargaGraficoHomicidiosComparativo($data)
    {
        $anio1 = $data['anio1'];
        $anio2 = $data['anio2'];

        $data_anio1 = $this->cargaGraficoHomicidiosDona(['anio' => $anio1]);

        $data_anio2 = $this->cargaGraficoHomicidiosDona(['anio' => $anio2]);

        if (!$data_anio1['valid'] || !$data_anio2['valid']) {
            return [
                "valid" => false, 
                "data" => [],
                "title" => "Error al cargar datos comparativos de Homicidios.",
            ];
        }


        $map_genero = [];
        $all_categories = [];
        
        // Mapear Año 1
        foreach ($data_anio1['data'] as $item) {
            $genero = ucfirst(strtolower($item['sexo'] ?? 'Sin Dato'));
            $map_genero[$genero][$anio1] = (int)($item['total_hechos'] ?? 0);
            if (!in_array($genero, $all_categories)) $all_categories[] = $genero;
        }

        // Mapear Año 2
        foreach ($data_anio2['data'] as $item) {
            $genero = ucfirst(strtolower($item['sexo'] ?? 'Sin Dato'));
            $map_genero[$genero][$anio2] = (int)($item['total_hechos'] ?? 0);
            if (!in_array($genero, $all_categories)) $all_categories[] = $genero;
        }
        
        // Si no hay categorías, no hay datos válidos
        if (empty($all_categories)) {
            return [
                "valid" => true,
                "data" => [],
                "title" => "Sin datos válidos para la comparación de Homicidios.",
            ];
        }

        
        $series_data = [];
        $data_series_anio1 = [];
        $data_series_anio2 = [];

        foreach ($all_categories as $genero) {

            $data_series_anio1[] = $map_genero[$genero][$anio1] ?? 0;
            $data_series_anio2[] = $map_genero[$genero][$anio2] ?? 0;
        }

        $series_data[] = [
            'name' => 'Casos Homicidios - ' . $anio1,
            'data' => $data_series_anio1,
            'type' => 'column'
        ];
        $series_data[] = [
            'name' => 'Casos Homicidios - ' . $anio2,
            'data' => $data_series_anio2,
            'type' => 'column'
        ];

        return [
            "valid" => true,
            "data" => $series_data,
            "categories" => $all_categories, 
            "title" => "Comparativa de Homicidios por Género: $anio1 vs $anio2",
            "chart_type" => "column",
        ];
    }


    private function _consultaDesaparecidosDespPorAnio($anio, $endpoint = "https://www.datos.gov.co/resource/8hqm-7fdt.json")
    {
        try {

            $yearCol = 'a_o_de_la_desaparicion'; 
            
            $groupCol = 'mes_de_la_desaparicion'; 
            $countCol = 'COUNT(id) AS total_hechos'; 
             
            $where = []; 
            
            $where[] = "codigo_dane_departamento='68'"; 
            $where[] = "$yearCol = '$anio'"; 

            $whereString = implode(" AND ", $where);
            
            $params = [
                "\$select" => "$groupCol, $countCol", 
                "\$where"  => $whereString,
                "\$group"  => $groupCol,             
                "\$limit"  => 50,
            ];

            $url = $endpoint . '?' . http_build_query($params);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            $apiData = json_decode($response, true);
            
            return [
                "valid" => true,
                "data" => is_array($apiData) ? $apiData : [],
                "query_year" => $anio,
                "debug_url" => $url
            ];

        } catch (Exception $e) {
            return [
                "valid" => false,
                "title" => "Error al consultar API Desaparecidos año $anio: " . $e->getMessage(),
                "data" => [],
            ];
        }
    }


    public function cargaGraficoDesaparecidosDesp($data)
    {
        $anio1 = $data['anio1'] ?? (date('Y') - 1);
        $anio2 = $data['anio2'] ?? null;
        $aniosAConsultar = $anio2 !== null ? [$anio1, $anio2] : [$anio1];
        
        $allData = [];
        $allCategories = [];
        $allUrls = []; 


        foreach ($aniosAConsultar as $anio) {
            $result = $this->_consultaDesaparecidosDespPorAnio($anio);

            if (!$result['valid']) {
                return $result;
            }
            $allUrls[$anio] = $result['debug_url'] ?? 'N/A';
            $data_for_processing = $result['data'] ?? [];

            if (is_array($data_for_processing)) { 
                foreach ($result['data']  as $item) {
                    if (!is_array($item)) continue; 
                    
                    $mes = $item['mes_de_la_desaparicion'];
                    $allCategories[$mes] = true;
                }
                $allData[$anio] = $data_for_processing;
            } else {
                $allData[$anio] = [];
            }

        }
        
        $categories = array_keys($allCategories);
        sort($categories); 
        
        if (empty($categories) && $anio2 !== null) {
            $title = "Sin datos de Desaparecidos para la comparación $anio1 vs $anio2.";
            return ["valid" => true, "dataSeries" => [], "categories" => [], "title" => $title];
        }

        $series_data = [];

        foreach ($aniosAConsultar as $anio) {
            $data_for_series = array_fill_keys($categories, 0); 
            
            foreach ($allData[$anio] as $item) {

                if (!is_array($item)) {
                    continue; 
                }

                $data_for_series[$item['mes_de_la_desaparicion']] = (int)($item['total_hechos'] ?? 0);
            }
            
            $series_data[] = [
                'name' => 'Casos Desaparecidos - ' . $anio,
                'data' => array_values($data_for_series), 
                'type' => 'column'
            ];
        }
        
        $title = $anio2 !== null 
                ? "Desaparecidos: Comparativa $anio1 vs $anio2" 
                : "Desaparecidos por Desplazamiento en Santander - $anio1";

        return [
            "valid" => true,
            "dataSeries" => $series_data,
            "categories" => $categories, 
            "title" => $title,
            "chart_type" => "column",
            "debug_urls" => $allUrls,

        ];
    }
    



// Endpoint de la API
//$endpoint = "https://www.datos.gov.co/resource/meew-mguv.json";
//hurtos
//$endpoint = "https://www.datos.gov.co/resource/6sqw-8cg5.json";

// Parámetros de consulta SoQL
/* $params = [
    "\$select" => "departamento,municipio,codigo_dane,armas_medios,fecha_hecho,genero,grupo_etario,tipo_de_hurto,cantidad",
    "\$where"  => "departamento='SANTANDER'",
    "\$order"  => "fecha_hecho DESC",
    "\$limit"  => 10
];

$url = $endpoint . '?' . http_build_query($params);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // si tienes problemas con HTTPS

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error de cURL: ' . curl_error($ch);
    curl_close($ch);
    exit;
}
curl_close($ch);

$data = json_decode($response, true); */


// Mostrar en una tabla HTML
/* echo "<table border='1' cellpadding='5'>";
echo "<tr>
       <td>departamento</td>
       <td>municipio</td>
       <td>codigo_dane</td>
       <td>armas_medios</td>
       <td>fecha_hecho</td>
       <td>genero</td>
       <td>grupo_etario</td>
       <td>tipo_de_hurto</td>
       <td>cantidad</td>
      </tr>";

foreach ($data as $row) {
    echo "<tr>
            <td>" . htmlspecialchars($row['departamento'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['municipio'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['codigo_dane'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['armas_medios'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['fecha_hecho'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['genero'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['grupo_etario'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['tipo_de_hurto'] ?? '-') . "</td>
            <td>" . htmlspecialchars($row['cantidad'] ?? '-') . "</td>
          </tr>";
}
echo "</table>"; */

//Megaman5000*1980

//ID de la clave API
//4gsh35cxo4lqlcu0bw2w6xdnq

//Clave secreta API
//5stfdqabhpl3psiz5qtk5tdrvo4jsfa5eahljz2lsns02oved0

}