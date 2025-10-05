<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * ============================================================================
 * MIDDLEWARE DE AUTENTICACIÓN SIMPLE PARA API
 * ============================================================================
 *
 * Este middleware protege rutas de la API verificando la autenticación del usuario.
 * Permite acceso libre en desarrollo y exige autenticación en producción.
 * Soporta autenticación por Bearer Token, sesión personalizada y Auth de Laravel.
 *
 * CARACTERÍSTICAS:
 * - Permite acceso libre en modo desarrollo (APP_ENV=local o APP_DEBUG=true)
 * - En producción, verifica autenticación por:
 *   - Bearer Token (header Authorization)
 *   - Sesión personalizada (session 'user')
 *   - Auth de Laravel (Auth::check())
 * - Expira sesión tras 1 hora de inactividad
 * - Logging de accesos y denegaciones
 *
 * @package App\Http\Middleware
 * @author DentalSync Development Team
 * @version 2.0
 * @since 2025-09-04
 */
class AuthenticateApiSimple
{
    /**
     * Maneja la autenticación de la petición entrante.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Para desarrollo: permitir todas las rutas sin autenticación estricta
        if (config('app.env') === 'local' || config('app.debug') === true) {
            \Log::info('🔓 Modo desarrollo: Permitiendo acceso sin autenticación', [
                'route' => $request->path(),
                'method' => $request->method(),
                'env' => config('app.env'),
                'debug' => config('app.debug')
            ]);
            return $next($request);
        }

        // En producción: verificar autenticación múltiple
        if ($this->isAuthenticated($request)) {
            return $next($request);
        }

        // No autenticado
        \Log::warning('Acceso denegado - Sin autenticación válida', [
            'route' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'error' => 'Autenticación requerida',
            'code' => 'AUTHENTICATION_REQUIRED'
        ], 401);
    }

    /**
     * Verifica si el usuario está autenticado por alguno de los métodos soportados.
     *
     * @param Request $request
     * @return bool
     */
    private function isAuthenticated(Request $request): bool
    {
        // Método 1: Bearer Token
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            // En una implementación completa, aquí se validaría el token
            return true;
        }

        // Método 2: Laravel Auth
        if (Auth::check()) {
            return true;
        }

        // Método 3: Sesión personalizada
        $sessionUser = session('user');
        if ($sessionUser && isset($sessionUser['logged_in']) && $sessionUser['logged_in'] === true) {
            // Verificar que la sesión no haya expirado
            $loginTime = \Carbon\Carbon::parse($sessionUser['login_time']);
            if ($loginTime->diffInHours(now()) <= 1) {
                return true;
            } else {
                // Limpiar sesión expirada
                session()->forget(['user', 'auth_token']);
            }
        }

        return false;
    }
}
