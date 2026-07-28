<?php
ob_clean();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
putenv("GOOGLE_APPLICATION_CREDENTIALS=" . __DIR__ . '/credenciales/clave.json');
if (!file_exists(__DIR__ . '/credenciales/clave.json')) {
    echo json_encode(['error' => 'La clave de Google no se encuentra en la ruta especificada.']);
    exit;
}

if (!isset($_FILES['audio'])) {
    echo json_encode(['error' => 'Archivo de audio no recibido.']);
    exit;
}

move_uploaded_file($_FILES['audio']['tmp_name'], 'grabacion.wav');

if (filesize('grabacion.wav') < 2000) {
    echo json_encode(['error' => 'El archivo de audio está vacío o corrupto.']);
    exit;
}

file_put_contents('debug_header.txt', bin2hex(file_get_contents('grabacion.wav')));

try {
    $archivoConvertido = 'grabacion.wav';

    $speech = new SpeechClient([
        'credentials' => json_decode(file_get_contents($_ENV['GOOGLE_APPLICATION_CREDENTIALS']), true)
    ]);

    $config = new RecognitionConfig([
        'encoding' => AudioEncoding::LINEAR16,
        'language_code' => 'es-ES'
    ]);

    $audio = (new RecognitionAudio())->setContent(file_get_contents($archivoConvertido));
    $response = $speech->recognize($config, $audio);
    $speech->close();

    file_put_contents('debug_response.json', $response->serializeToJsonString());

    $results = $response->getResults();
    if (empty($results)) {
        echo json_encode(['error' => 'La API no devolvió resultados. ¿El audio tiene voz clara y en español?']);
        exit;
    }

    $textoReconocido = '';
    foreach ($results as $result) {
        $alternatives = $result->getAlternatives();
        if (count($alternatives) > 0) {
            $textoReconocido .= $alternatives[0]->getTranscript();
        }
    }

    if (trim($textoReconocido) === '') {
        echo json_encode(['error' => 'No se pudo transcribir el audio. Verifica que se haya grabado claramente.']);
        exit;
    }

    include 'listado_preguntas_ai_mejorado.php';
    $respuesta = $respuesta ?? 'Lo siento, no pude encontrar una respuesta adecuada.';

    $ttsClient = new TextToSpeechClient([
        'credentials' => json_decode(file_get_contents($_ENV['GOOGLE_APPLICATION_CREDENTIALS']), true)
    ]);


		$inputText = (new SynthesisInput())->setText($respuesta);
		$voice = (new VoiceSelectionParams())
    		->setLanguageCode('es-ES')
    		//->setName('es-ES-Wavenet-A')
            ->setName('es-ES-Standard-A')  // o B, C, D (puedes probar)
    		->setSsmlGender(SsmlVoiceGender::FEMALE);

    $audioConfig = (new AudioConfig())->setAudioEncoding(AudioEncoding::MP3);
    $ttsResponse = $ttsClient->synthesizeSpeech($inputText, $voice, $audioConfig);
    $ttsClient->close();

    file_put_contents('respuesta.mp3', $ttsResponse->getAudioContent());
    echo json_encode(['audio' => 'respuesta.mp3']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en el procesamiento: ' . $e->getMessage()]);
    exit;
}
