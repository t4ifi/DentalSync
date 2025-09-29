#!/usr/bin/env php
<?php

/**
 * Script para crear pacientes en DentalSync
 * Uso: php crear-paciente.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\Paciente;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "\n🦷 ========== DENTALSYNC - CREAR PACIENTE ==========\n\n";

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

try {
    // Información básica
    $nombre = leerInput("👤 Nombre completo: ");
    $email = leerInput("📧 Email (opcional): ", false);
    
    // Verificar email único si se proporciona
    if (!empty($email) && Paciente::where('email', $email)->exists()) {
        echo "❌ Error: Ya existe un paciente con este email.\n";
        exit(1);
    }
    
    $telefono = leerInput("📱 Teléfono: ");
    $cedula = leerInput("🆔 Cédula: ");
    
    // Verificar cédula única
    if (Paciente::where('cedula', $cedula)->exists()) {
        echo "❌ Error: Ya existe un paciente con esta cédula.\n";
        exit(1);
    }
    
    // Fecha de nacimiento
    do {
        $fechaNacimiento = leerInput("🎂 Fecha de nacimiento (YYYY-MM-DD): ");
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
            try {
                $fecha = new DateTime($fechaNacimiento);
                break;
            } catch (Exception $e) {
                echo "❌ Fecha inválida. Use formato YYYY-MM-DD.\n";
            }
        } else {
            echo "❌ Formato inválido. Use YYYY-MM-DD.\n";
        }
    } while (true);
    
    // Género
    echo "\n👥 Género:\n";
    echo "1. Masculino\n";
    echo "2. Femenino\n";
    echo "3. Otro\n";
    
    do {
        $generoOpcion = leerInput("Selecciona género (1-3): ");
        $generos = [
            '1' => 'masculino',
            '2' => 'femenino',
            '3' => 'otro'
        ];
        
        if (isset($generos[$generoOpcion])) {
            $genero = $generos[$generoOpcion];
            break;
        }
        echo "❌ Opción inválida. Selecciona 1, 2 o 3.\n";
    } while (true);
    
    // Información adicional
    $direccion = leerInput("🏠 Dirección: ");
    $ocupacion = leerInput("💼 Ocupación (opcional): ", false);
    $contactoEmergencia = leerInput("🚨 Contacto de emergencia (opcional): ", false);
    $telefonoEmergencia = leerInput("📞 Teléfono de emergencia (opcional): ", false);
    
    // Información médica
    echo "\n🏥 Información médica:\n";
    $alergias = leerInput("💊 Alergias conocidas (opcional): ", false);
    $medicamentos = leerInput("💉 Medicamentos actuales (opcional): ", false);
    $condicionesMedicas = leerInput("🩺 Condiciones médicas (opcional): ", false);
    
    // Mutualista
    $mutualista = leerInput("🏥 Mutualista/Seguro médico (opcional): ", false);
    $numeroSocio = leerInput("🆔 Número de socio (opcional): ", false);
    
    // Crear paciente
    echo "\n📝 Creando paciente...\n";
    
    $paciente = Paciente::create([
        'nombre' => $nombre,
        'email' => $email ?: null,
        'telefono' => $telefono,
        'cedula' => $cedula,
        'fecha_nacimiento' => $fechaNacimiento,
        'genero' => $genero,
        'direccion' => $direccion,
        'ocupacion' => $ocupacion ?: null,
        'contacto_emergencia' => $contactoEmergencia ?: null,
        'telefono_emergencia' => $telefonoEmergencia ?: null,
        'alergias' => $alergias ?: null,
        'medicamentos_actuales' => $medicamentos ?: null,
        'condiciones_medicas' => $condicionesMedicas ?: null,
        'mutualista' => $mutualista ?: null,
        'numero_socio' => $numeroSocio ?: null,
        'estado' => 'activo',
        'fecha_registro' => now()
    ]);
    
    // Calcular edad
    $edad = (new DateTime())->diff(new DateTime($fechaNacimiento))->y;
    
    echo "✅ ¡Paciente creado exitosamente!\n\n";
    echo "👤 Información del paciente:\n";
    echo "   ID: {$paciente->id}\n";
    echo "   Nombre: {$paciente->nombre}\n";
    echo "   Email: " . ($paciente->email ?: 'No especificado') . "\n";
    echo "   Teléfono: {$paciente->telefono}\n";
    echo "   Cédula: {$paciente->cedula}\n";
    echo "   Edad: {$edad} años\n";
    echo "   Género: " . ucfirst($paciente->genero) . "\n";
    echo "   Dirección: {$paciente->direccion}\n";
    if ($paciente->ocupacion) {
        echo "   Ocupación: {$paciente->ocupacion}\n";
    }
    if ($paciente->mutualista) {
        echo "   Mutualista: {$paciente->mutualista}\n";
    }
    if ($paciente->alergias) {
        echo "   Alergias: {$paciente->alergias}\n";
    }
    echo "   Estado: " . ucfirst($paciente->estado) . "\n";
    echo "   Fecha registro: {$paciente->fecha_registro}\n\n";
    
    echo "🎉 El paciente está ahora registrado en DentalSync.\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}