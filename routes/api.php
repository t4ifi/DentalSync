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
// RUTAS PÚBLICAS
// ========================================
Route::middleware(['rate.limit:login'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});
