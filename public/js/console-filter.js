/* ====================================
 * FILTROS DE CONSOLA PARA DENTALSYNC
 * ==================================== */

// Script para filtrar mensajes no deseados en la consola
(function() {
    'use strict';
    
    // Guardar métodos originales de consola
    const originalLog = console.log;
    const originalWarn = console.warn;
    const originalError = console.error;
    
    // Lista de patrones a filtrar
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
        /background\.js:/
    ];
    
    // Función para verificar si un mensaje debe ser bloqueado
    function shouldBlock(message) {
        const messageStr = String(message);
        return blockedPatterns.some(pattern => pattern.test(messageStr));
    }
    
    // Sobrescribir console.log
    console.log = function(...args) {
        if (!args.some(shouldBlock)) {
            originalLog.apply(console, args);
        }
    };
    
    // Sobrescribir console.warn
    console.warn = function(...args) {
        if (!args.some(shouldBlock)) {
            originalWarn.apply(console, args);
        }
    };
    
    // Sobrescribir console.error
    console.error = function(...args) {
        if (!args.some(shouldBlock)) {
            originalError.apply(console, args);
        }
    };
    
    // Limpiar consola al cargar la página
    console.clear();
    
    // Mostrar mensaje de DentalSync limpio
    console.log('%c🦷 DentalSync', 'color: #0ea5e9; font-size: 16px; font-weight: bold;');
    console.log('%cConsola limpia y lista para desarrollo', 'color: #10b981; font-size: 12px;');
    
})();