<?php

/**
 * Rutas API para DentalSync
 * 
 * Este archivo contiene todas las rutas de la API del sistema de gestión
 * dental DentalSync. Las rutas están organizadas por módulos funcionales.
 * 
 * Prefijo automático: /api
 * Middleware aplicado: 'api'
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-09-09
 * @version 1.0
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí puedes registrar las rutas de API para tu aplicación. Estas rutas
| son cargadas por el RouteServiceProvider dentro de un grupo que tiene
| asignado el middleware "api".
|
*/

// Ruta de información de usuario autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/
// Estas rutas serán definidas cuando se implemente el sistema de autenticación
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Rutas de Gestión de Pacientes
|--------------------------------------------------------------------------
*/
// Route::apiResource('pacientes', PacienteController::class);

/*
|--------------------------------------------------------------------------
| Rutas de Gestión de Citas
|--------------------------------------------------------------------------
*/
// Route::apiResource('citas', CitaController::class);

/*
|--------------------------------------------------------------------------
| Rutas de Gestión de Tratamientos
|--------------------------------------------------------------------------
*/
// Route::apiResource('tratamientos', TratamientoController::class);

/*
|--------------------------------------------------------------------------
| Rutas de Gestión de Pagos
|--------------------------------------------------------------------------
*/
// Route::apiResource('pagos', PagoController::class);

/*
|--------------------------------------------------------------------------
| Rutas de Gestión de Placas Dentales
|--------------------------------------------------------------------------
*/
// Route::apiResource('placas', PlacaController::class);

/*
|--------------------------------------------------------------------------
| Rutas de WhatsApp (futuro módulo)
|--------------------------------------------------------------------------
*/
// Route::prefix('whatsapp')->group(function () {
//     Route::get('/conversaciones', [WhatsAppController::class, 'conversaciones']);
//     Route::post('/enviar', [WhatsAppController::class, 'enviarMensaje']);
// });
