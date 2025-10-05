<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad - DentalSync
    |--------------------------------------------------------------------------
    */

    // Configuración de Rate Limiting
    'rate_limiting' => [
        'login_attempts' => [
            'max_attempts' => 5,
            'decay_minutes' => 5,
        ],
        'api_requests' => [
            'max_attempts' => 100,
            'decay_minutes' => 1,
        ],
        'payment_operations' => [
            'max_attempts' => 20,
            'decay_minutes' => 1,
        ],
        'admin_operations' => [
            'max_attempts' => 30,
            'decay_minutes' => 1,
        ],
    ],

    // Configuración de contraseñas
    'passwords' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
        'expiry_days' => 90,
    ],

    // Configuración de sesiones
    'sessions' => [
        'timeout_minutes' => 60,
        'encrypt' => true,
        'secure_only' => env('APP_ENV') === 'production',
        'http_only' => true,
        'same_site' => 'strict',
    ],

    // Configuración de logs de seguridad
    'logging' => [
        'log_login_attempts' => true,
        'log_failed_requests' => true,
        'log_unauthorized_access' => true,
        'sensitive_data_masking' => true,
    ],

    // Validación de entrada
    'input_validation' => [
        'max_string_length' => 1000,
        'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png'],
        'max_file_size' => 5120, // KB
        'sanitize_html' => true,
    ],

    // Headers de seguridad
    'security_headers' => [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'content_security_policy' => env('APP_ENV') === 'production' 
            ? "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;"
            : "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: ws: wss:; script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* http://127.0.0.1:* http://[::1]:* ws://localhost:* ws://127.0.0.1:* ws://[::1]:*; style-src 'self' 'unsafe-inline' http://localhost:* http://127.0.0.1:* http://[::1]:*; img-src 'self' data: https: http: blob:; font-src 'self' data: http: https:; connect-src 'self' ws://localhost:* ws://127.0.0.1:* ws://[::1]:* http://localhost:* http://127.0.0.1:* http://[::1]:*;",
    ],

    // Configuración de encriptación
    'encryption' => [
        'algorithm' => 'AES-256-CBC',
        'key_rotation_days' => 30,
    ],

    // Configuración de auditoría
    'audit' => [
        'enabled' => true,
        'log_user_actions' => true,
        'retention_days' => 365,
    ],
];
