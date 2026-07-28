<?php
$data = [
    [
        'lat' => 6.25184,
        'lng' => -75.56359,
        'score' => 10,
        'titulo' => 'Gestión del Riesgo',
        'subtitulo' => 'Deslizamiento de banca',
        'vereda' => 'Vereda Poblanco',
        'municipio' => 'Santa Bárbara',
        'departamento' => 'Antioquia'
    ],
    [
        'lat' => 6.25210,
        'lng' => -75.56400,
        'score' => 50,
        'mensaje' => 'Gestión del Riesgo, deslizamiento de banca, Vereda Poblanco, Santa Bárbara, Antioquia'
    ],
    [
        'lat' => 6.25300,
        'lng' => -75.56550,
        'score' => 90,
        'titulo' => 'Gestión del Riesgo',
        'subtitulo' => 'Deslizamiento de banca',
        'vereda' => 'Vereda Poblanco',
        'municipio' => 'Santa Bárbara',
        'departamento' => 'Antioquia'
    ]
];

header('Content-Type: application/json');
echo json_encode($data);
