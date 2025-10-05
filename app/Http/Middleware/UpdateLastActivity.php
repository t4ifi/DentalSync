<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Solo actualizar en peticiones exitosas
        if ($response->getStatusCode() < 400) {
            $this->updateUserActivity($request);
        }
        
        return $response;
    }
    
    /**
     * Actualizar la última actividad del usuario
     */
    private function updateUserActivity(Request $request): void
    {
        // Actualizar en Laravel Auth si está disponible
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && method_exists($user, 'update')) {
                try {
                    $user->update(['ultimo_acceso' => now()]);
                } catch (\Exception $e) {
                    // Silenciar errores de actualización
                }
            }
        }
        
        // Actualizar en sesión personalizada
        $sessionUser = session('user');
        if ($sessionUser && isset($sessionUser['logged_in']) && $sessionUser['logged_in'] === true) {
            $sessionUser['last_activity'] = now();
            session(['user' => $sessionUser]);
        }
    }
}
