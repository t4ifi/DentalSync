<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * ============================================================================
 * MIDDLEWARE DE RATE LIMITING PARA API
 * ============================================================================
 *
 * Este middleware limita la cantidad de peticiones permitidas por IP o usuario.
 * En desarrollo, puede estar deshabilitado para facilitar pruebas.
 *
 * CARACTERÍSTICAS:
 * - Limita la cantidad de requests por minuto
 * - Permite deshabilitar en modo desarrollo
 * - Devuelve error 429 si se excede el límite
 *
 * @package App\Http\Middleware
 * @author DentalSync Development Team
 * @version 2.0
 * @since 2025-09-04
 */
class RateLimitingMiddleware
{
    /**
     * Maneja el rate limiting de la petición entrante.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $key = 'api'): Response
    {
        // Deshabilitar rate limiting en desarrollo
        if (config('app.env') === 'local' || config('app.debug') === true) {
            \Log::info('🔓 Modo desarrollo: Saltando rate limiting', [
                'route' => $request->path(),
                'method' => $request->method(),
                'key' => $key
            ]);
            return $next($request);
        }
        
        // Configuración específica por tipo de operación
        $config = $this->getRateLimitConfig($key);
        $maxAttempts = $config['max_attempts'];
        $decayMinutes = $config['decay_minutes'];
        
        // Crear una clave única por IP y ruta
        $rateLimitKey = $key . '|' . $request->ip() . '|' . $request->path();
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            // Log del intento de rate limiting
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'user_agent' => $request->userAgent(),
                'key' => $key,
                'max_attempts' => $maxAttempts,
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Demasiadas solicitudes. Intente más tarde.',
                'retry_after' => RateLimiter::availableIn($rateLimitKey)
            ], 429);
        }
        
        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);
        
        $response = $next($request);
        
        // Agregar headers informativos sobre rate limiting
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $maxAttempts - RateLimiter::attempts($rateLimitKey));
        
        return $response;
    }
    
    /**
     * Obtener configuración de rate limiting por tipo
     */
    private function getRateLimitConfig(string $key): array
    {
        $configMap = [
            'login' => 'login_attempts',
            'api' => 'api_requests',
            'payment' => 'payment_operations',
            'admin' => 'admin_operations',
        ];
        
        $configKey = $configMap[$key] ?? 'api_requests';
        
        return config("security.rate_limiting.{$configKey}", [
            'max_attempts' => 100,
            'decay_minutes' => 1
        ]);
    }
}
