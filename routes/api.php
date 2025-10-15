<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PlacaController;
use App\Http\Controllers\TratamientoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Api\WhatsappConversacionController;
use App\Http\Controllers\Api\WhatsappPlantillaController;

// ========================================
// RUTAS PÚBLICAS (Con rate limiting estricto)
// ========================================
Route::middleware(['rate.limit:login'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// ========================================
// RUTAS PROTEGIDAS (Requieren autenticación + protecciones CSRF)
// ========================================
Route::middleware(['auth.api', 'rate.limit:api'])->group(function () {
    
    // Rutas de solo lectura (sin CSRF)
    Route::get('/me', [AuthController::class, 'me']);
    
    // Rutas de citas - Temporalmente sin CSRF para desarrollo
    Route::get('/citas', [CitaController::class, 'index']);
    Route::post('/citas', [CitaController::class, 'store']);
    Route::put('/citas/{id}', [CitaController::class, 'update']);
    Route::delete('/citas/{id}', [CitaController::class, 'destroy']);
    
    // Rutas de pacientes - Temporalmente sin CSRF para desarrollo
    Route::get('/pacientes', [PacienteController::class, 'index']);
    Route::get('/pacientes/{id}', [PacienteController::class, 'show']);
    Route::put('/pacientes/{id}', [PacienteController::class, 'update']);
    Route::post('/pacientes', [PacienteController::class, 'store']);

    // Rutas para placas dentales
    Route::get('/placas', [PlacaController::class, 'index']);
    Route::get('/placas/{id}', [PlacaController::class, 'show']);
    Route::middleware(['csrf.api'])->group(function () {
        Route::post('/placas', [PlacaController::class, 'store']);
        Route::put('/placas/{id}', [PlacaController::class, 'update']);
        Route::delete('/placas/{id}', [PlacaController::class, 'destroy']);
    });

    // Rutas para tratamientos
    Route::get('/tratamientos/pacientes', [TratamientoController::class, 'getPacientes']);
    Route::get('/tratamientos/paciente/{pacienteId}', [TratamientoController::class, 'getTratamientosPaciente']);
    Route::get('/tratamientos/historial/{pacienteId}', [TratamientoController::class, 'getHistorialClinico']);
    // Temporalmente sin CSRF para desarrollo
    Route::post('/tratamientos', [TratamientoController::class, 'store']);
    Route::post('/tratamientos/{id}/observacion', [TratamientoController::class, 'addObservacion']);
    Route::put('/tratamientos/{id}/finalizar', [TratamientoController::class, 'finalizar']);

    // Rutas de autenticación
    Route::middleware(['csrf.api'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Rutas para sistema de pagos (temporalmente sin protecciones extras)
    Route::prefix('pagos')->group(function () {
        // Solo lectura sin CSRF
        Route::get('/pacientes', [PagoController::class, 'getPacientes']);
        Route::get('/resumen', [PagoController::class, 'getResumenPagos']);
        Route::get('/paciente/{pacienteId}', [PagoController::class, 'verPagosPaciente']);
        Route::get('/cuotas/{pagoId}', [PagoController::class, 'getCuotasPago']);
        
        // Operaciones críticas - temporalmente sin CSRF para desarrollo
        Route::post('/init-session', [PagoController::class, 'initSession']);
        Route::post('/registrar', [PagoController::class, 'registrarPago']);
        Route::post('/cuota', [PagoController::class, 'registrarPagoCuota']);
    });

    // Rutas para WhatsApp
    Route::prefix('whatsapp')->group(function () {
        // Solo lectura
        Route::get('/conversaciones', [WhatsappConversacionController::class, 'index']);
        Route::get('/conversaciones/{conversacion}/mensajes', [WhatsappConversacionController::class, 'mensajes']);
        Route::get('/conversaciones/estadisticas', [WhatsappConversacionController::class, 'estadisticas']);
        Route::get('/plantillas', [WhatsappPlantillaController::class, 'index']);
        Route::get('/plantillas/{plantilla}', [WhatsappPlantillaController::class, 'show']);
        Route::get('/plantillas/categorias/list', [WhatsappPlantillaController::class, 'categorias']);
        Route::get('/plantillas/estadisticas/resumen', [WhatsappPlantillaController::class, 'estadisticas']);
        
        // Operaciones de modificación con CSRF
        Route::middleware(['csrf.api'])->group(function () {
            Route::post('/conversaciones', [WhatsappConversacionController::class, 'store']);
            Route::post('/conversaciones/{conversacion}/mensajes', [WhatsappConversacionController::class, 'enviarMensaje']);
            Route::put('/conversaciones/{conversacion}/estado', [WhatsappConversacionController::class, 'actualizarEstado']);
            
            Route::post('/plantillas', [WhatsappPlantillaController::class, 'store']);
            Route::put('/plantillas/{plantilla}', [WhatsappPlantillaController::class, 'update']);
            Route::delete('/plantillas/{plantilla}', [WhatsappPlantillaController::class, 'destroy']);
            Route::post('/plantillas/{plantilla}/duplicar', [WhatsappPlantillaController::class, 'duplicar']);
            Route::put('/plantillas/{plantilla}/toggle', [WhatsappPlantillaController::class, 'toggleEstado']);
            Route::post('/plantillas/{plantilla}/usar', [WhatsappPlantillaController::class, 'incrementarUsos']);
        });
    });

    // Rutas para gestión de usuarios (máxima seguridad)
    Route::prefix('usuarios')->middleware(['csrf.api', 'rate.limit:admin'])->group(function () {
        Route::get('/', [UsuarioController::class, 'index']);
        Route::post('/', [UsuarioController::class, 'store']);
        Route::get('/{id}', [UsuarioController::class, 'show']);
        Route::put('/{id}', [UsuarioController::class, 'update']);
        Route::delete('/{id}', [UsuarioController::class, 'destroy']);
        Route::post('/{id}/toggle-status', [UsuarioController::class, 'toggleStatus']);
        Route::get('/estadisticas/resumen', [UsuarioController::class, 'getEstadisticas']);
    });

    // Ruta de debug para verificar sesión (solo desarrollo)
    Route::get('/debug/session', function () {
        return response()->json([
            'session_id' => session()->getId(),
            'user' => session('user'),
            'all_session' => session()->all(),
            'has_user_key' => session()->has('user'),
            'php_session_id' => session_id()
        ]);
    });
});

// ========================================
// RUTAS WEBHOOK WHATSAPP (Sin autenticación para webhooks externos)
// ========================================
Route::get('/webhook/whatsapp', [App\Http\Controllers\Api\WhatsappWebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [App\Http\Controllers\Api\WhatsappWebhookController::class, 'handle']);
