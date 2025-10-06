<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DentalSync</title>
    <!-- Favicon optimizado para diferentes tamaños -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('diente-favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('diente-favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('diente-favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('diente-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Console Filter for Clean Development -->
    <script>
    (function() {
        'use strict';
        
        // Solo aplicar filtros en desarrollo
        if (!window.location.href.includes('localhost') && !window.location.href.includes('127.0.0.1')) {
            return;
        }
        
        // Guardar métodos originales
        const originalLog = console.log;
        const originalWarn = console.warn;
        const originalError = console.error;
        
        // Patrones a filtrar (extensiones del navegador)
        const blockedPatterns = [
            /WebAssembly is supported/,
            /Retrieving application id/,
            /WASM SDK loaded/,
            /Unable to fetch ServerConfig from.*bitwarden/,
            /State version:/,
            /Migrator.*should migrate/,
            /Using WebPush for server notifications/,
            /Issue with web push, falling back to SignalR/,
            /Environment did not respond in time/,
            /SignalR.*WebSocket connected to.*bitwarden/,
            /SignalR.*Using HubProtocol/,
            /SignalR.*Connection disconnected/,
            /Unchecked runtime\.lastError.*back\/forward cache/,
            /CipherService.*decrypt complete took/,
            /SearchService.*index complete took/,
            /Cannot find menu item with id/,
            /background\.js:/,
            /chrome-extension:/,
            /moz-extension:/
        ];
        
        function shouldBlock(message) {
            const messageStr = String(message);
            return blockedPatterns.some(pattern => pattern.test(messageStr));
        }
        
        // Filtrar console.log
        console.log = function(...args) {
            if (!args.some(shouldBlock)) {
                originalLog.apply(console, args);
            }
        };
        
        // Filtrar console.warn
        console.warn = function(...args) {
            if (!args.some(shouldBlock)) {
                originalWarn.apply(console, args);
            }
        };
        
        // Filtrar console.error
        console.error = function(...args) {
            if (!args.some(shouldBlock)) {
                originalError.apply(console, args);
            }
        };
        
        // Limpiar consola y mostrar mensaje de DentalSync
        setTimeout(() => {
            console.clear();
            console.log('%c🦷 DentalSync - Consola Limpia', 'color: #0ea5e9; font-size: 16px; font-weight: bold; background: #f0f9ff; padding: 8px; border-radius: 4px;');
            console.log('%cSistema listo para desarrollo', 'color: #10b981; font-size: 12px;');
        }, 100);
        
    })();
    </script>
</head>
<body class="bg-gray-100">
    <div id="app"></div>
</body>
</html>
