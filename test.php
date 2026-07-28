<?php

require __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("❌ El autoload no existe o no se puede acceder");
}



echo "SpeechClient: " . (class_exists(\Google\Cloud\Speech\V1\SpeechClient::class) ? "OK" : "MISSING") . PHP_EOL;
echo "RecognitionConfig: " . (class_exists(\Google\Cloud\Speech\V1\RecognitionConfig::class) ? "OK" : "MISSING") . PHP_EOL;
echo "RecognitionAudio: " . (class_exists(\Google\Cloud\Speech\V1\RecognitionAudio::class) ? "OK" : "MISSING") . PHP_EOL;
