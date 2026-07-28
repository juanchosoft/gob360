<?php
require 'vendor/autoload.php';

use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;

$text = file_get_contents("respuesta.txt");

$client = new TextToSpeechClient();

$input_text = (new SynthesisInput())->setText($text);
$voice = (new VoiceSelectionParams())->setLanguageCode('es-ES')->setSsmlGender(1); // femenino
$audioConfig = (new AudioConfig())->setAudioEncoding(1); // MP3

$response = $client->synthesizeSpeech($input_text, $voice, $audioConfig);

file_put_contents('respuesta.mp3', $response->getAudioContent());

echo "Audio generado con éxito.";
