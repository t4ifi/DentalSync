<?php

/**
 * Script rápido para crear pacientes de prueba
 * Corrige el problema del campo nombre_completo
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Paciente;
use Illuminate\Support\Facades\DB;

// Configurar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🦷 DENTALSYNC - Creando Pacientes de Prueba\n";
echo "==========================================\n\n";

try {
    // Datos de pacientes corregidos
    $pacientes = [
        [
            'nombre_completo' => 'Juan Pérez González',
            'telefono' => '+598 99 444 444',
            'fecha_nacimiento' => '1985-05-15',
            'motivo_consulta' => 'Dolor en muela del juicio',
            'alergias' => 'Penicilina',
            'observaciones' => 'Paciente regular, viene cada 6 meses'
        ],
        [
            'nombre_completo' => 'María Rodríguez Silva',
            'telefono' => '+598 99 555 555',
            'fecha_nacimiento' => '1990-08-22',
            'motivo_consulta' => 'Revisión general',
            'alergias' => 'Ninguna conocida',
            'observaciones' => 'Paciente nueva, derivada por seguro médico'
        ],
        [
            'nombre_completo' => 'Pedro García Martínez',
            'telefono' => '+598 99 666 666',
            'fecha_nacimiento' => '1978-12-03',
            'motivo_consulta' => 'Limpieza dental',
            'alergias' => 'Látex',
            'observaciones' => 'Profesional médico, muy colaborativo'
        ],
        [
            'nombre_completo' => 'Ana Martínez López',
            'telefono' => '+598 99 777 777',
            'fecha_nacimiento' => '1995-03-18',
            'motivo_consulta' => 'Brackets de ortodoncia',
            'alergias' => 'Ninguna',
            'observaciones' => 'Joven universitaria, horarios flexibles'
        ],
        [
            'nombre_completo' => 'Luis Fernández Castro',
            'telefono' => '+598 99 888 888',
            'fecha_nacimiento' => '1982-09-25',
            'motivo_consulta' => 'Blanqueamiento dental',
            'alergias' => 'Anestesia local',
            'observaciones' => 'Ejecutivo, prefiere citas matutinas'
        ]
    ];

    echo "👥 Creando pacientes...\n";
    
    foreach ($pacientes as $pacienteData) {
        // Agregar fecha de última visita
        $pacienteData['ultima_visita'] = now()->subDays(rand(1, 90));
        
        $paciente = Paciente::create($pacienteData);
        echo "   ✅ Paciente creado: {$paciente->nombre_completo}\n";
    }
    
    $total = Paciente::count();
    echo "\n🎉 ¡Completado! Total de pacientes: $total\n";
    echo "\n📋 Para verificar:\n";
    echo "   - Abrir navegador en http://localhost:8000\n";
    echo "   - Iniciar sesión y ir a sección de Pacientes\n";
    echo "   - Los pacientes deberían aparecer en la lista\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📝 Traza: " . $e->getTraceAsString() . "\n";
}