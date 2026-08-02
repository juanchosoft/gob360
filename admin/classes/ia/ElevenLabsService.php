<?php

/**
 * Servicio ElevenLabs: STT (Scribe v1) y TTS (eleven_flash_v2_5).
 * Usa cURL directamente (sin Guzzle ni librerías HTTP adicionales).
 */
final class ElevenLabsService
{
    /**
     * Ruta al CA bundle (necesario en Windows/WAMP donde curl.cainfo está vacío).
     */
    private static function caBundle(): ?string
    {
        static $path = false;
        if ($path !== false) {
            return $path;
        }
        $path = null;
        if (class_exists('Composer\CaBundle\CaBundle')) {
            $bundle = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
            if (is_string($bundle) && is_file($bundle)) {
                $path = $bundle;
            }
        }
        return $path;
    }

    private static function cfg(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $ini    = parse_ini_file(__DIR__ . '/../../../config.ini', true);
        $cache  = [
            'api_key'   => trim($ini['elevenlabs']['api_key']  ?? '', '"'),
            'voice_id'  => trim($ini['elevenlabs']['voice_id'] ?? '', '"'),
            'tts_model' => trim($ini['elevenlabs']['tts_model'] ?? 'eleven_flash_v2_5', '"'),
            'stt_model' => trim($ini['elevenlabs']['stt_model'] ?? 'scribe_v1', '"'),
        ];
        return $cache;
    }

    /**
     * Transcribe un archivo de audio a texto usando ElevenLabs Scribe (STT).
     *
     * @param  string $rutaAudio Ruta absoluta al archivo de audio temporal
     * @return string Texto transcrito
     * @throws RuntimeException si la API falla o no detecta habla
     */
    public static function transcribir(string $rutaAudio): string
    {
        $cfg = self::cfg();

        $ch = curl_init('https://api.elevenlabs.io/v1/speech-to-text');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'xi-api-key: ' . $cfg['api_key'],
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => [
                'model_id'      => $cfg['stt_model'],
                'language_code' => 'es',
                'file'          => new CURLFile($rutaAudio),
            ],
        ]);
        if (($ca = self::caBundle()) !== null) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca);
        }

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new RuntimeException('Error de red al transcribir: ' . $err);
        }
        if ($status !== 200) {
            $detail = json_decode($body, true)['detail']['message'] ?? ('HTTP ' . $status);
            throw new RuntimeException('ElevenLabs STT: ' . $detail);
        }

        $texto = trim(json_decode($body, true)['text'] ?? '');
        if ($texto === '') {
            throw new RuntimeException('No se detectó habla en el audio. Intenta hablar más cerca del micrófono.');
        }

        return $texto;
    }

    /**
     * Sintetiza texto a voz y devuelve el binario MP3 (TTS).
     *
     * @param  string $texto Texto limpio (sin markdown), ya procesado con limpiarMarkdown()
     * @return string Binario del audio MP3
     * @throws RuntimeException si la API falla
     */
    public static function sintetizar(string $texto): string
    {
        $cfg = self::cfg();
        $url = 'https://api.elevenlabs.io/v1/text-to-speech/' . rawurlencode($cfg['voice_id'])
             . '?output_format=mp3_44100_128';

        $payload = json_encode([
            'text'     => mb_substr($texto, 0, 2500),
            'model_id' => $cfg['tts_model'],
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'xi-api-key: ' . $cfg['api_key'],
                'Content-Type: application/json',
                'Accept: audio/mpeg',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);
        if (($ca = self::caBundle()) !== null) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca);
        }

        $audio  = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new RuntimeException('Error de red al sintetizar voz: ' . $err);
        }
        if ($status !== 200) {
            throw new RuntimeException('ElevenLabs TTS error HTTP ' . $status);
        }

        return $audio;
    }

    /**
     * Elimina formato Markdown para que el TTS no lea los símbolos literalmente.
     */
    public static function limpiarMarkdown(string $texto): string
    {
        // Bloques de código (``` ... ```)
        $texto = preg_replace('/```[\s\S]*?```/', '', $texto);
        // Código inline (`texto`)
        $texto = preg_replace('/`(.+?)`/', '$1', $texto);
        // Encabezados (# ## ###)
        $texto = preg_replace('/^#{1,6}\s+/m', '', $texto);
        // Negrita y cursiva (**texto**, *texto*, __texto__, _texto_)
        $texto = preg_replace('/\*{1,3}(.+?)\*{1,3}/s', '$1', $texto);
        $texto = preg_replace('/_{1,2}(.+?)_{1,2}/s', '$1', $texto);
        // Links [texto](url) → solo el texto
        $texto = preg_replace('/\[(.+?)\]\(.+?\)/', '$1', $texto);
        // Listas (guiones, asteriscos, números)
        $texto = preg_replace('/^[-*+]\s+/m', '', $texto);
        $texto = preg_replace('/^\d+\.\s+/m', '', $texto);
        // Líneas horizontales
        $texto = preg_replace('/^[-*_]{3,}\s*$/m', '', $texto);
        // Múltiples líneas en blanco → una
        $texto = preg_replace('/\n{3,}/', "\n\n", $texto);

        return trim($texto);
    }
}
