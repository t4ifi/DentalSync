#!/usr/bin/env php
<?php

/**
 * Script para crear usuarios en DentalSync
 * Uso: php crear-usuario.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "\n🦷 ========== DENTALSYNC - CREAR USUARIO ==========\n\n";

// Función para leer input
function leerInput($mensaje, $requerido = true) {
    do {
        echo $mensaje;
        $input = trim(fgets(STDIN));
        if (!$requerido || !empty($input)) {
            return $input;
        }
        echo "❌ Este campo es requerido.\n";
    } while (true);
}

// Función para leer contraseña sin mostrarla
function leerPassword($mensaje) {
    echo $mensaje;
    
    // En sistemas Unix/Linux/Mac
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
    } else {
        // En Windows
        $password = trim(fgets(STDIN));
    }
    
    return $password;
}

try {
    // Recopilar información del usuario
    $nombre = leerInput("👤 Nombre completo: ");
    $usuario = leerInput("� Usuario (para login): ");
    
    // Verificar si el usuario ya existe
    if (Usuario::where('usuario', $usuario)->exists()) {
        echo "❌ Error: Ya existe un usuario con este nombre de usuario.\n";
        exit(1);
    }
    
    // Seleccionar rol
    echo "\n👨‍⚕️ Seleccionar rol:\n";
    echo "1. Dentista\n";
    echo "2. Recepcionista\n";
    
    do {
        $rolOpcion = leerInput("Selecciona el rol (1-2): ");
        $roles = [
            '1' => 'dentista',
            '2' => 'recepcionista'
        ];
        
        if (isset($roles[$rolOpcion])) {
            $rol = $roles[$rolOpcion];
            break;
        }
        echo "❌ Opción inválida. Selecciona 1 o 2.\n";
    } while (true);
    
    // Los roles no tienen especialidad en esta tabla
    
    // Contraseña
    do {
        $password = leerPassword("\n🔐 Contraseña (mínimo 8 caracteres): ");
        if (strlen($password) >= 8) {
            break;
        }
        echo "❌ La contraseña debe tener al menos 8 caracteres.\n";
    } while (true);
    
    $passwordConfirm = leerPassword("🔐 Confirmar contraseña: ");
    
    if ($password !== $passwordConfirm) {
        echo "❌ Error: Las contraseñas no coinciden.\n";
        exit(1);
    }
    
    // Crear usuario
    echo "\n📝 Creando usuario...\n";
    
    $usuarioCreado = Usuario::create([
        'usuario' => $usuario,
        'nombre' => $nombre,
        'rol' => $rol,
        'password_hash' => Hash::make($password),
        'activo' => true,
        'ultimo_acceso' => null,
        'intentos_fallidos' => 0,
        'bloqueado_hasta' => null
    ]);
    
    echo "✅ ¡Usuario creado exitosamente!\n\n";
    echo "👤 Información del usuario:\n";
    echo "   ID: {$usuarioCreado->id}\n";
    echo "   Usuario: {$usuarioCreado->usuario}\n";
    echo "   Nombre: {$usuarioCreado->nombre}\n";
    echo "   Rol: " . ucfirst($usuarioCreado->rol) . "\n";
    echo "   Estado: " . ($usuarioCreado->activo ? 'Activo' : 'Inactivo') . "\n";
    echo "   Fecha registro: {$usuarioCreado->created_at}\n\n";
    
    echo "🎉 El usuario puede ahora iniciar sesión en DentalSync.\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}