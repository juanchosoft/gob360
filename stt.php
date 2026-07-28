<?php
require 'vendor/autoload.php';

use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;

$audioFile = 'grabacion_usuario.wav'; // debe ser grabado desde frontend

$client = new SpeechClient();

$config = new RecognitionConfig([
    'encoding' => RecognitionConfig\AudioEncoding::LINEAR16,
    'language_code' => 'es-ES',
]);

$audio = (new RecognitionAudio())->setContent(file_get_contents($audioFile));

$response = $client->recognize($config, $audio);
$textoReconocido = '';

foreach ($response->getResults() as $result) {
    $textoReconocido .= $result->getAlternatives()[0]->getTranscript();
}

echo "Texto reconocido: " . $textoReconocido;


// Simulación de voz reconocida como texto
$voz = strtolower($textoReconocido);

$nombresReconocidos = ["juan", "ana", "carlos"];
$nombreIdentificado = null;

foreach ($nombresReconocidos as $nombre) {
    if (strpos($voz, $nombre) !== false) {
        $nombreIdentificado = ucfirst($nombre);
        break;
    }
}

$respuesta = $nombreIdentificado
    ? "Claro que sí, $nombreIdentificado. El secretario de Interior es el general Óscar Hernández."
    : "¿Me puedes decir tu nombre, por favor?";

file_put_contents("respuesta.txt", $respuesta);
