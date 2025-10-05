<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // En desarrollo, NO aplicar CSP que interfiere con Vite
        if (app()->environment('production')) {
            // En producción, aplicar todos los headers de seguridad
            $this->applyProductionHeaders($response, $request);
        }
        // En desarrollo, no aplicar headers restrictivos
        
        return $response;
    }
    
    /**
     * Aplicar headers completos para producción
     */
    private function applyProductionHeaders(Response $response, Request $request): Response
    {
        $headers = config('security.security_headers', []);
        
        if (isset($headers['x_frame_options'])) {
            $response->headers->set('X-Frame-Options', $headers['x_frame_options']);
        }
        
        if (isset($headers['x_content_type_options'])) {
            $response->headers->set('X-Content-Type-Options', $headers['x_content_type_options']);
        }
        
        if (isset($headers['x_xss_protection'])) {
            $response->headers->set('X-XSS-Protection', $headers['x_xss_protection']);
        }
        
        if (isset($headers['referrer_policy'])) {
            $response->headers->set('Referrer-Policy', $headers['referrer_policy']);
        }
        
        if (isset($headers['content_security_policy'])) {
            $response->headers->set('Content-Security-Policy', $headers['content_security_policy']);
        }
        
        $response->headers->set('X-Powered-By', '');
        $response->headers->set('Server', '');
        
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
        
        if ($this->isSensitiveApiRoute($request)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        }
        
        return $response;
    }
    
    /**
     * Determinar si es una ruta API sensible
     */
    private function isSensitiveApiRoute(Request $request): bool
    {
        $sensitiveRoutes = [
            'api/login',
            'api/logout',
            'api/user',
            'api/pacientes',
            'api/tratamientos',
            'api/pagos',
            'api/citas'
        ];
        
        foreach ($sensitiveRoutes as $route) {
            if (str_contains($request->path(), $route)) {
                return true;
            }
        }
        
        return false;
    }
}
