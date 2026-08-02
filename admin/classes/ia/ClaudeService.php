<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Anthropic\Client;
use Anthropic\RequestOptions;
use Anthropic\Core\Exceptions\RateLimitException;
use Anthropic\Core\Exceptions\APIException;
use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Wrapper único del SDK de Anthropic.
 * Toda llamada a Claude pasa por aquí.
 */
final class ClaudeService
{
    private Client $client;
    private string $model;
    private int $maxTokens;

    public function __construct()
    {
        $config = parse_ini_file(__DIR__ . '/../../../config.ini', true);
        $cfg = $config['anthropic'] ?? [];

        // CA bundle explícito: en Windows/WAMP curl.cainfo suele estar vacío.
        // Timeout de 120s: con thinking adaptativo, una sola llamada puede superar los 60s.
        $caBundle = CaBundle::getSystemCaRootBundlePath();
        $guzzle = new GuzzleClient([
            'verify'  => $caBundle ?: true,
            'timeout' => 120,
        ]);

        $this->client    = new Client(
            apiKey: $cfg['api_key'] ?? '',
            requestOptions: RequestOptions::with(transporter: $guzzle),
        );
        $this->model     = $cfg['model'] ?? 'claude-sonnet-4-6';
        $this->maxTokens = (int) ($cfg['max_tokens'] ?? 16000);
    }

    /**
     * Envía un turno de mensajes a Claude y devuelve la respuesta.
     *
     * @param  array $system   Bloques de system prompt (con cache_control en el estable)
     * @param  array $messages Historial de mensajes [role, content]
     * @param  array $tools    Definiciones de herramientas con 'inputSchema' (camelCase PHP → input_schema wire)
     * @return \Anthropic\Messages\Message
     * @throws RuntimeException con mensaje en español
     */
    public function crearMensaje(array $system, array $messages, array $tools)
    {
        try {
            // Header para interleaved thinking (necesario para thinking + tool-use simultáneo)
            $requestOptions = RequestOptions::with()->withExtraHeaders([
                'anthropic-beta' => 'interleaved-thinking-2025-05-14',
            ]);

            $response = $this->client->messages->create(
                maxTokens: $this->maxTokens,
                messages: $messages,
                model: $this->model,
                system: $system,
                thinking: ['type' => 'adaptive'],
                tools: !empty($tools) ? $tools : null,
                requestOptions: $requestOptions,
            );

            // Registrar cache hits en error_log para verificación
            $cacheRead  = $response->usage->cacheReadInputTokens  ?? 0;
            $cacheWrite = $response->usage->cacheCreationInputTokens ?? 0;
            if ($cacheWrite > 0 || $cacheRead > 0) {
                error_log("[IA] cache_write={$cacheWrite} cache_read={$cacheRead} model={$this->model}");
            }

            return $response;
        } catch (RateLimitException $e) {
            error_log('[IA] RateLimitException: ' . $e->getMessage());
            throw new RuntimeException('Límite de solicitudes alcanzado. Intenta en unos minutos.');
        } catch (APIException $e) {
            error_log('[IA] APIException (code=' . $e->getCode() . '): ' . $e->getMessage()
                . ' | previous=' . ($e->getPrevious() ? get_class($e->getPrevious()) . ': ' . $e->getPrevious()->getMessage() : 'ninguna'));
            $msg = $e->getMessage();
            if (str_contains($msg, 'api_key')) {
                throw new RuntimeException('Error de configuración del asistente IA. Contacta al administrador.');
            }
            if (stripos($msg, 'timeout') !== false || stripos($msg, 'timed out') !== false) {
                throw new RuntimeException('La consulta tardó demasiado en procesarse. Intenta con una pregunta más específica.');
            }
            throw new RuntimeException('Error al comunicarse con el asistente IA. Intenta de nuevo.');
        } catch (\Throwable $e) {
            error_log('[IA] ' . get_class($e) . ': ' . $e->getMessage());
            throw new RuntimeException('Error inesperado en el asistente IA. Intenta de nuevo.');
        }
    }

    public function getModel(): string
    {
        return $this->model;
    }
}
