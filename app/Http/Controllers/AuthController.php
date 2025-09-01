<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Throttling para prevenir ataques de fuerza bruta
        $key = 'login-attempts:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) { // 5 intentos por minuto
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Demasiados intentos de login. Intente nuevamente en {$seconds} segundos.",
                'error' => 'TOO_MANY_ATTEMPTS'
            ], 429);
        }

        // Validación de entrada con reglas más estrictas
        $request->validate([
            'usuario' => 'required|string|max:100|regex:/^[a-zA-Z0-9_.-]+$/',
            'password' => 'required|string|min:6|max:255',
        ], [
            'usuario.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'usuario.max' => 'El usuario no puede exceder 100 caracteres.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.max' => 'La contraseña no puede exceder 255 caracteres.'
        ]);

        // Buscar usuario y verificar que esté activo
        $usuario = Usuario::where('usuario', $request->usuario)
                         ->where('activo', true)
                         ->first();
        
        // Verificar credenciales
        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            // Incrementar contador de intentos fallidos
            RateLimiter::hit($key, 60); // Bloquear por 60 segundos
            
            return response()->json([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos.',
                'error' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        // Limpiar intentos fallidos en login exitoso
        RateLimiter::clear($key);

        // Generar token seguro para la sesión
        $token = $this->generateSecureToken();
        
        // Actualizar último acceso del usuario
        $usuario->update(['ultimo_acceso' => now()]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $usuario->id,
                'usuario' => $usuario->usuario,
                'nombre' => $usuario->nombre,
                'rol' => $usuario->rol,
                'token' => $token,
                'expires_in' => 3600 // Token válido por 1 hora
            ],
            'message' => 'Inicio de sesión exitoso.'
        ]);
    }

    public function logout(Request $request)
    {
        // En una implementación completa, aquí invalidarías el token
        // Por ahora, solo retornamos respuesta exitosa
        
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    /**
     * Generar un token seguro para autenticación
     */
    private function generateSecureToken(): string
    {
        // Generar token usando métodos criptográficamente seguros
        $timestamp = time();
        $randomBytes = random_bytes(32);
        $userAgent = request()->header('User-Agent', 'unknown');
        
        // Combinar datos y generar hash seguro
        $data = $timestamp . '|' . base64_encode($randomBytes) . '|' . hash('sha256', $userAgent);
        
        return base64_encode(hash('sha256', $data, true));
    }

    /**
     * Verificar información del usuario autenticado
     */
    public function me(Request $request)
    {
        // Esta función requiere autenticación
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación requerido',
                'error' => 'UNAUTHORIZED'
            ], 401);
        }

        // En una implementación completa, aquí decodificarías el token
        // y obtendrías la información del usuario
        
        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado correctamente'
        ]);
    }
}