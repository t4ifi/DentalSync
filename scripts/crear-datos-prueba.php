#!/usr/bin/env php
<?php

/**
 * Script para crear datos de prueba en DentalSync
 * Uso: php crear-datos-prueba.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Tratamiento;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "\n🦷 ========== DENTALSYNC - CREAR DATOS DE PRUEBA ==========\n\n";

try {
    echo "📝 Creando datos de prueba...\n\n";
    
    // ========== USUARIOS ==========
    echo "👨‍⚕️ Creando usuarios...\n";
    
    // Dentista principal
    $dentista = Usuario::create([
        'usuario' => 'dentista1',
        'nombre' => 'Dr. Carlos Rodríguez',
        'rol' => 'dentista',
        'password_hash' => Hash::make('dentista123'),
        'activo' => true
    ]);
    echo "   ✅ Dentista creado: {$dentista->usuario} / dentista123\n";
    
    // Recepcionista
    $recepcionista = Usuario::create([
        'usuario' => 'recepcion1',
        'nombre' => 'María González',
        'rol' => 'recepcionista',
        'password_hash' => Hash::make('recepcion123'),
        'activo' => true
    ]);
    echo "   ✅ Recepcionista creado: {$recepcionista->usuario} / recepcion123\n";
    
    // ========== PACIENTES ==========
    echo "\n👥 Creando pacientes...\n";
    
    $pacientes = [
        [
            'nombre' => 'Juan Pérez',
            'email' => 'juan.perez@email.com',
            'telefono' => '+598 99 444 444',
            'cedula' => '11111111',
            'fecha_nacimiento' => '1985-05-15',
            'genero' => 'masculino',
            'direccion' => 'Av. 18 de Julio 1234, Montevideo',
            'ocupacion' => 'Ingeniero',
            'mutualista' => 'ASSE',
            'alergias' => 'Penicilina'
        ],
        [
            'nombre' => 'María Rodríguez',
            'email' => 'maria.rodriguez@email.com',
            'telefono' => '+598 99 555 555',
            'cedula' => '22222222',
            'fecha_nacimiento' => '1990-08-22',
            'genero' => 'femenino',
            'direccion' => 'Bvar. Artigas 567, Montevideo',
            'ocupacion' => 'Profesora',
            'mutualista' => 'Médica Uruguaya',
            'condiciones_medicas' => 'Hipertensión'
        ],
        [
            'nombre' => 'Pedro García',
            'email' => 'pedro.garcia@email.com',
            'telefono' => '+598 99 666 666',
            'cedula' => '33333333',
            'fecha_nacimiento' => '1978-12-03',
            'genero' => 'masculino',
            'direccion' => 'Paysandú 890, Montevideo',
            'ocupacion' => 'Contador',
            'mutualista' => 'CASMU'
        ],
        [
            'nombre' => 'Ana Martínez',
            'email' => 'ana.martinez@email.com',
            'telefono' => '+598 99 777 777',
            'cedula' => '44444444',
            'fecha_nacimiento' => '1995-03-18',
            'genero' => 'femenino',
            'direccion' => 'Pocitos 1122, Montevideo',
            'ocupacion' => 'Diseñadora',
            'mutualista' => 'SMI',
            'alergias' => 'Látex'
        ],
        [
            'nombre' => 'Luis Fernández',
            'email' => 'luis.fernandez@email.com',
            'telefono' => '+598 99 888 888',
            'cedula' => '55555555',
            'fecha_nacimiento' => '1982-09-25',
            'genero' => 'masculino',
            'direccion' => 'Malvín 445, Montevideo',
            'ocupacion' => 'Abogado',
            'mutualista' => 'ASSE'
        ]
    ];
    
    foreach ($pacientes as $pacienteData) {
        $pacienteData['estado'] = 'activo';
        $pacienteData['fecha_registro'] = now();
        
        $paciente = Paciente::create($pacienteData);
        echo "   ✅ Paciente creado: {$paciente->nombre}\n";
    }
    
    // ========== TRATAMIENTOS ==========
    echo "\n🦷 Creando tratamientos...\n";
    
    $tratamientos = [
        [
            'nombre' => 'Limpieza Dental',
            'descripcion' => 'Limpieza profesional de dientes y encías',
            'precio' => 1500.00,
            'duracion_estimada' => 60,
            'categoria' => 'Preventivo'
        ],
        [
            'nombre' => 'Empaste Composite',
            'descripcion' => 'Restauración dental con material composite',
            'precio' => 2500.00,
            'duracion_estimada' => 90,
            'categoria' => 'Restaurativo'
        ],
        [
            'nombre' => 'Extracción Simple',
            'descripcion' => 'Extracción de pieza dental simple',
            'precio' => 2000.00,
            'duracion_estimada' => 45,
            'categoria' => 'Cirugía'
        ],
        [
            'nombre' => 'Corona Dental',
            'descripcion' => 'Colocación de corona cerámica',
            'precio' => 8000.00,
            'duracion_estimada' => 120,
            'categoria' => 'Prótesis'
        ],
        [
            'nombre' => 'Blanqueamiento',
            'descripcion' => 'Blanqueamiento dental profesional',
            'precio' => 5000.00,
            'duracion_estimada' => 90,
            'categoria' => 'Estético'
        ],
        [
            'nombre' => 'Endodoncia',
            'descripcion' => 'Tratamiento de conducto radicular',
            'precio' => 6000.00,
            'duracion_estimada' => 150,
            'categoria' => 'Endodoncia'
        ],
        [
            'nombre' => 'Implante Dental',
            'descripcion' => 'Colocación de implante de titanio',
            'precio' => 15000.00,
            'duracion_estimada' => 180,
            'categoria' => 'Implantología'
        ],
        [
            'nombre' => 'Ortodoncia (mensual)',
            'descripcion' => 'Control mensual de ortodoncia',
            'precio' => 3000.00,
            'duracion_estimada' => 60,
            'categoria' => 'Ortodoncia'
        ]
    ];
    
    foreach ($tratamientos as $tratamientoData) {
        $tratamientoData['estado'] = 'activo';
        $tratamientoData['fecha_creacion'] = now();
        
        $tratamiento = Tratamiento::create($tratamientoData);
        echo "   ✅ Tratamiento creado: {$tratamiento->nombre} - \${$tratamiento->precio}\n";
    }
    
    echo "\n🎉 ¡Datos de prueba creados exitosamente!\n\n";
    
    echo "📋 RESUMEN DE DATOS CREADOS:\n";
    echo "========================================\n";
    echo "👨‍⚕️ USUARIOS:\n";
    echo "   • Dentista: dentista1 / dentista123\n";
    echo "   • Recepcionista: recepcion1 / recepcion123\n\n";
    
    echo "👥 PACIENTES: " . count($pacientes) . " pacientes creados\n\n";
    
    echo "🦷 TRATAMIENTOS: " . count($tratamientos) . " tratamientos creados\n\n";
    
    echo "🌐 Puedes ahora iniciar sesión en DentalSync con cualquiera de los usuarios creados.\n";
    echo "🎯 URL: http://localhost:8000\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📝 Traza: " . $e->getTraceAsString() . "\n";
    exit(1);
}