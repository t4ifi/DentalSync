<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si existe un token Bearer en el header
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !$this->startsWith($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación requerido',
                'error' => 'UNAUTHORIZED'
            ], 401);
        }

        // Extraer el token
        $token = substr($authHeader, 7); // Remover "Bearer "
        
        // Verificar que el token no esté vacío
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación inválido',
                'error' => 'INVALID_TOKEN'
            ], 401);
        }

        // Verificar el token contra la sesión (implementación simple)
        if (!$this->isValidToken($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación expirado o inválido',
                'error' => 'TOKEN_EXPIRED'
            ], 401);
        }

        return $next($request);
    }

    /**
     * Función helper para verificar si un string comienza con otro
     */
    private function startsWith(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * Verificar si el token es válido
     * NOTA: En una implementación completa, esto debería verificar contra
     * una base de datos de tokens o JWT
     */
    private function isValidToken(string $token): bool
    {
        // Por ahora, implementación simple para desarrollo
        // En producción, esto debe ser reemplazado por verificación real de tokens
        
        // Verificar longitud mínima del token
        if (strlen($token) < 32) {
            return false;
        }

        // Verificar formato básico (solo caracteres alfanuméricos y algunos símbolos)
        if (!preg_match('/^[a-zA-Z0-9+\/=]+$/', $token)) {
            return false;
        }

        // TODO: Implementar verificación real contra base de datos o JWT
        // Por ahora, aceptamos cualquier token bien formado para desarrollo
        return true;
    }
}
