<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * ============================================================================
 * MIDDLEWARE DE PROTECCIÓN CSRF PARA API
 * ============================================================================
 *
 * Este middleware protege las rutas de la API contra ataques CSRF.
 * En desarrollo, puede estar deshabilitado para facilitar pruebas.
 *
 * CARACTERÍSTICAS:
 * - Verifica el token CSRF en las peticiones
 * - Permite deshabilitar en modo desarrollo
 *
 * @package App\Http\Middleware
 * @author DentalSync Development Team
 * @version 2.0
 * @since 2025-09-04
 */
class CsrfApiProtection
{
    /**
     * Maneja la protección CSRF en la petición entrante.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Deshabilitar CSRF en desarrollo
        if (config('app.env') === 'local' || config('app.debug') === true) {
            \Log::info('🔓 Modo desarrollo: Saltando verificación CSRF', [
                'route' => $request->path(),
                'method' => $request->method()
            ]);
            return $next($request);
        }
        
        // Solo aplicar CSRF a métodos que modifican datos en producción
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $next($request);
        }
        
        // Verificar si es una petición AJAX/API
        if (!$request->ajax() && !$request->wantsJson()) {
            return $next($request);
        }
        
        // Obtener token CSRF de headers o request
        $token = $request->header('X-CSRF-TOKEN') ?? 
                 $request->header('X-XSRF-TOKEN') ?? 
                 $request->input('_token');
        
        // Verificar si el token está presente
        if (!$token) {
            Log::warning('Missing CSRF token in API request', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Token CSRF requerido para esta operación.',
                'code' => 'CSRF_TOKEN_MISSING'
            ], 403);
        }
        
        // Verificar que el token sea válido
        $sessionToken = session()->token();
        
        // Si no hay token de sesión, intentar regenerarlo
        if (!$sessionToken) {
            session()->regenerateToken();
            $sessionToken = session()->token();
        }
        
        if (!$sessionToken || !hash_equals($sessionToken, $token)) {
            Log::warning('Invalid CSRF token in API request', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $request->method(),
                'provided_token' => substr($token, 0, 10) . '...',
                'session_token_exists' => $sessionToken ? 'yes' : 'no',
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Token CSRF inválido. Por favor, recargue la página.',
                'code' => 'CSRF_TOKEN_INVALID'
            ], 403);
        }
        
        return $next($request);
    }
}
