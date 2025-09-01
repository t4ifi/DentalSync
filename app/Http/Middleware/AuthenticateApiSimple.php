<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiSimple
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Versión súper simple para testing
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader) {
            return response()->json([
                'error' => 'No Authorization header'
            ], 401);
        }
        
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return response()->json([
                'error' => 'Invalid Authorization format'
            ], 401);
        }
        
        return $next($request);
    }
}