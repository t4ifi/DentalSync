<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para integración con WhatsApp Business API.
    | Obtén estas credenciales desde Meta for Developers.
    |
    */

    'token' => env('WHATSAPP_TOKEN', ''),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', 'dentalsync_webhook_2025'),
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),

    /*
    |--------------------------------------------------------------------------
    | Configuración adicional
    |--------------------------------------------------------------------------
    */
    
    'timeout' => env('WHATSAPP_TIMEOUT', 30),
    'max_message_length' => 4096,
    'supported_media_types' => [
        'image' => ['jpg', 'jpeg', 'png', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'txt'],
        'audio' => ['mp3', 'wav', 'ogg'],
        'video' => ['mp4', '3gp', 'mov']
    ],
];