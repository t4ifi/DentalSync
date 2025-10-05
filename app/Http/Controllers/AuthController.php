<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * ============================================================================
 * CONTROLADOR DE AUTENTICACIÓN - DENTALSYNC
 * ============================================================================
 * 
 * Este controlador maneja toda la lógica de autenticación del sistema DentalSync.
 * Implementa múltiples capas de seguridad y gestión robusta de sesiones.
 * 
 * CARACTERÍSTICAS PRINCIPALES:
 * - Autenticación segura con rate limiting
 * - Gestión de sesiones robusta
 * - Logging de seguridad y auditoría
 * - Protección contra ataques de fuerza bruta
 * - Soporte para múltiples métodos de autenticación
 * 
 * MÉTODOS PÚBLICOS:
 * - login()         : Autenticación de usuarios
 * - logout()        : Cierre de sesión seguro
 * - verificarSesion() : Verificación de estado de sesión
 * - me()            : Información del usuario autenticado
 * 
 * MÉTODOS PRIVADOS:
 * - generateSecureToken()     : Generación de tokens seguros
 * - establecerSesionUsuario() : Configuración de sesión
 * 
 * @package App\Http\Controllers
 * @author DentalSync Development Team
 * @version 2.0
 * @since 2025-09-04
 */
class AuthController extends Controller
{
    /**
     * Autentica a un usuario usando usuario y contraseña.
     * Aplica rate limiting y valida credenciales.
     * Si es exitoso, establece la sesión y retorna datos del usuario.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
            'password' => 'required|string|min:8|max:255',
        ], [
            'usuario.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'usuario.max' => 'El usuario no puede exceder 100 caracteres.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede exceder 255 caracteres.'
        ]);

        // Log de intento de autenticación
        \Log::info('Intento de autenticación', [
            'usuario' => $request->usuario,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);

        // Buscar usuario y verificar que esté activo
        $usuario = Usuario::where('usuario', $request->usuario)
                         ->where('activo', true)
                         ->first();
        
        // Verificar credenciales
        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            // Log de intento fallido con canal de seguridad
            \Log::channel('security')->warning('Failed authentication attempt', [
                'usuario' => $request->usuario,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reason' => !$usuario ? 'user_not_found' : 'invalid_password',
                'timestamp' => now()
            ]);
            
            // Incrementar contador de intentos fallidos
            RateLimiter::hit($key, 300); // Bloquear por 5 minutos
            
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas.',
                'error' => 'INVALID_CREDENTIALS'
            ], 401);
        }

        // Log de login exitoso
        \Log::channel('audit')->info('Successful user authentication', [
            'user_id' => $usuario->id,
            'usuario' => $usuario->usuario,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()
        ]);

        // Limpiar intentos fallidos en login exitoso
        RateLimiter::clear($key);

        // Generar token seguro para la sesión
        $token = $this->generateSecureToken();
        
        // Guardar información del usuario en la sesión con regeneración de ID
        $this->establecerSesionUsuario($usuario, $request);
        
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

    /**
     * Cierra la sesión del usuario actual.
     * Limpia la sesión y el estado de autenticación.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = Auth::user() ?? session('user');
        
        // Log de logout
        if ($user) {
            \Log::channel('audit')->info('User logout', [
                'user_id' => is_array($user) ? $user['id'] : $user->id,
                'usuario' => is_array($user) ? $user['usuario'] : $user->usuario,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);
        }
        
        // Cerrar sesión de Laravel Auth
        Auth::logout();
        
        // Limpiar sesión personalizada
        session()->forget(['user', 'auth_token']);
        
        // Invalidar y regenerar sesión por seguridad
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    /**
     * Genera un token seguro para autenticación de sesión.
     *
     * @return string
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
     * Establece la sesión del usuario de manera robusta y segura.
     * Regenera el ID de sesión y almacena los datos relevantes.
     *
     * @param Usuario $usuario
     * @param Request $request
     * @return void
     */
    private function establecerSesionUsuario(Usuario $usuario, Request $request): void
    {
        // Regenerar ID de sesión por seguridad
        session()->regenerate();
        
        // Limpiar datos de sesión anteriores
        session()->flush();
        
        // Establecer datos de usuario en la sesión
        session([
            'user' => [
                'id' => $usuario->id,
                'usuario' => $usuario->usuario,
                'nombre' => $usuario->nombre,
                'rol' => $usuario->rol,
                'logged_in' => true,
                'login_time' => now(),
                'last_activity' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        ]);
        
        // También establecer Auth de Laravel para compatibilidad
        Auth::loginUsingId($usuario->id);
        
        // Log de sesión establecida
        \Log::info('Sesión de usuario establecida', [
            'user_id' => $usuario->id,
            'session_id' => session()->getId(),
            'ip' => $request->ip()
        ]);
    }

    /**
     * Verifica si la sesión del usuario está   activa y no ha expirado.
     * Actualiza la última actividad si la sesión es válida.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarSesion(Request $request)
    {
        $user = session('user');
        
        if (!$user || !isset($user['logged_in']) || !$user['logged_in']) {
            return response()->json([
                'authenticated' => false,
                'message' => 'No hay sesión activa'
            ], 401);
        }
        
        // Verificar si la sesión ha expirado (más de 1 hora sin actividad)
        $lastActivity = Carbon::parse($user['last_activity']);
        if ($lastActivity->diffInHours(now()) > 1) {
            // Sesión expirada
            session()->forget('user');
            Auth::logout();
            
            return response()->json([
                'authenticated' => false,
                'message' => 'Sesión expirada'
            ], 401);
        }
        
        // Actualizar última actividad
        $user['last_activity'] = now();
        session(['user' => $user]);
        
        // También actualizar en la base de datos
        $usuario = Usuario::find($user['id']);
        if ($usuario) {
            $usuario->updateLastActivity();
        }
        
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user['id'],
                'usuario' => $user['usuario'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol']
            ]
        ]);
    }

    /**
     * Devuelve la información del usuario autenticado actual.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = session('user');
        
        if (!$user || !isset($user['logged_in']) || !$user['logged_in']) {
            return response()->json([
                'error' => 'Usuario no autenticado'
            ], 401);
        }
        
        // Actualizar última actividad
        $user['last_activity'] = now();
        session(['user' => $user]);
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'usuario' => $user['usuario'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol'],
                'login_time' => $user['login_time'],
                'last_activity' => $user['last_activity']
            ]
        ]);
    }
}
