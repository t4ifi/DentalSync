<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * ============================================================================
 * SERVICIO WHATSAPP - DENTALSYNC
 * ============================================================================
 *
 * Servicio para integración con WhatsApp Business API.
 * Maneja envío de mensajes, webhooks y gestión de medios.
 *
 * FUNCIONALIDADES:
 * - Envío de mensajes de texto
 * - Envío de medios (imágenes, documentos, audio, video)
 * - Plantillas de mensajes
 * - Gestión de estados de mensaje
 * - Rate limiting y retry logic
 *
 * @package App\Services
 * @author Andrés Núñez
 * @version 1.0
 * @since 2025-10-09
 */
class WhatsAppService
{
    private Client $client;
    private string $token;
    private string $phoneNumberId;
    private string $apiUrl;
    private int $timeout;

    public function __construct()
    {
        $this->client = new Client();
        $this->token = config('whatsapp.token');
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->apiUrl = config('whatsapp.api_url');
        $this->timeout = config('whatsapp.timeout', 30);
    }

    /**
     * Verificar si la configuración de WhatsApp está completa
     */
    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->phoneNumberId);
    }

    /**
     * Enviar mensaje de texto
     */
    public function sendTextMessage(string $to, string $message): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('WhatsApp no está configurado. Verifica las credenciales en .env');
        }

        $to = $this->formatPhoneNumber($to);
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message
            ]
        ];

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Enviar mensaje con plantilla
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('WhatsApp no está configurado. Verifica las credenciales en .env');
        }

        $to = $this->formatPhoneNumber($to);
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => 'es'
                ]
            ]
        ];

        if (!empty($parameters)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => $parameters
                ]
            ];
        }

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Enviar imagen
     */
    public function sendImage(string $to, string $imageUrl, string $caption = ''): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('WhatsApp no está configurado. Verifica las credenciales en .env');
        }

        $to = $this->formatPhoneNumber($to);
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'image',
            'image' => [
                'link' => $imageUrl
            ]
        ];

        if (!empty($caption)) {
            $payload['image']['caption'] = $caption;
        }

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Enviar documento
     */
    public function sendDocument(string $to, string $documentUrl, string $filename = '', string $caption = ''): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('WhatsApp no está configurado. Verifica las credenciales en .env');
        }

        $to = $this->formatPhoneNumber($to);
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'link' => $documentUrl
            ]
        ];

        if (!empty($filename)) {
            $payload['document']['filename'] = $filename;
        }

        if (!empty($caption)) {
            $payload['document']['caption'] = $caption;
        }

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Marcar mensaje como leído
     */
    public function markAsRead(string $messageId): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('WhatsApp no está configurado. Verifica las credenciales en .env');
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Obtener información del número de teléfono
     */
    public function getPhoneNumberInfo(): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('WhatsApp no está configurado. Verifica las credenciales en .env');
        }

        return $this->makeApiCall('', [], 'GET');
    }

    /**
     * Formatear número de teléfono para WhatsApp
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remover espacios, guiones y paréntesis
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Si empieza con +, removerlo
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }
        
        // Si es número uruguayo y no tiene código de país, agregarlo
        if (strlen($phone) === 8 && in_array(substr($phone, 0, 1), ['9', '2'])) {
            $phone = '598' . $phone;
        }
        
        return $phone;
    }

    /**
     * Realizar llamada a la API de WhatsApp
     */
    private function makeApiCall(string $endpoint, array $payload, string $method = 'POST'): array
    {
        $url = $this->buildApiUrl($endpoint);
        
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
            'timeout' => $this->timeout,
        ];

        if ($method === 'POST' && !empty($payload)) {
            $options['json'] = $payload;
        }

        try {
            Log::info('WhatsApp API Request', [
                'url' => $url,
                'method' => $method,
                'payload' => $payload
            ]);

            $response = $this->client->request($method, $url, $options);
            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info('WhatsApp API Response', [
                'status' => $response->getStatusCode(),
                'data' => $responseData
            ]);

            return $responseData;

        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            $errorResponse = null;

            if ($e->hasResponse()) {
                $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorMessage = $errorResponse['error']['message'] ?? $errorMessage;
            }

            Log::error('WhatsApp API Error', [
                'url' => $url,
                'method' => $method,
                'error' => $errorMessage,
                'response' => $errorResponse,
                'payload' => $payload
            ]);

            throw new \Exception("Error de WhatsApp API: {$errorMessage}", $e->getCode());
        }
    }

    /**
     * Construir URL completa de la API
     */
    private function buildApiUrl(string $endpoint): string
    {
        $baseUrl = $this->apiUrl . '/' . $this->phoneNumberId;
        
        if (empty($endpoint)) {
            return $baseUrl;
        }
        
        return $baseUrl . '/' . $endpoint;
    }

    /**
     * Verificar si un número es válido para WhatsApp
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        $formatted = $this->formatPhoneNumber($phone);
        
        // Debe tener al menos 10 dígitos y máximo 15
        return preg_match('/^\d{10,15}$/', $formatted);
    }

    /**
     * Obtener límites de rate limiting desde cache
     */
    public function getRateLimits(): array
    {
        return Cache::get('whatsapp_rate_limits', [
            'messages_per_second' => 20,
            'messages_per_minute' => 1000,
            'messages_per_day' => 100000
        ]);
    }

    /**
     * Validar que el mensaje cumpla con las restricciones
     */
    public function validateMessage(string $message): bool
    {
        $maxLength = config('whatsapp.max_message_length', 4096);
        return strlen($message) <= $maxLength;
    }
}