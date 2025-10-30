#!/usr/bin/env php
<?php

/**
 * ============================================================================
 * SCRIPT: CREAR CITAS DE PRUEBA - DENTALSYNC
 * ============================================================================
 * 
 * Este script permite crear múltiples citas para una fecha específica.
 * Útil para probar el sistema de notificaciones y gestión de citas.
 * 
 * @author Andrés Núñez
 * @version 1.0
 * @since 2025-10-30
 */

// Cargar autoload de Composer y bootstrap de Laravel
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// ========================================
// FUNCIONES AUXILIARES
// ========================================

function mostrarTitulo($texto) {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║ " . str_pad($texto, 64) . " ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n";
    echo "\n";
}

function mostrarExito($mensaje) {
    echo "✅ " . $mensaje . "\n";
}

function mostrarError($mensaje) {
    echo "❌ ERROR: " . $mensaje . "\n";
}

function mostrarInfo($mensaje) {
    echo "ℹ️  " . $mensaje . "\n";
}

function solicitarFecha() {
    echo "📅 Ingresa la fecha para las citas (formato: YYYY-MM-DD):\n";
    echo "   Ejemplos: 2025-11-15, 2025-12-01\n";
    echo "   [Presiona Enter para usar HOY]: ";
    $fecha = trim(fgets(STDIN));
    
    if (empty($fecha)) {
        return Carbon::today();
    }
    
    try {
        return Carbon::parse($fecha);
    } catch (\Exception $e) {
        mostrarError("Fecha inválida. Usando fecha de hoy.");
        return Carbon::today();
    }
}

function solicitarCantidad() {
    echo "\n🔢 ¿Cuántas citas deseas crear? [1-20]: ";
    $cantidad = trim(fgets(STDIN));
    
    $cantidad = intval($cantidad);
    
    if ($cantidad < 1 || $cantidad > 20) {
        mostrarInfo("Usando cantidad por defecto: 5 citas");
        return 5;
    }
    
    return $cantidad;
}

function obtenerPacientesAleatorios($cantidad) {
    $pacientes = Paciente::inRandomOrder()->limit($cantidad)->get();
    
    if ($pacientes->count() < $cantidad) {
        mostrarError("No hay suficientes pacientes en la base de datos.");
        mostrarInfo("Pacientes disponibles: " . $pacientes->count());
        mostrarInfo("Necesitas crear más pacientes primero (opción 3 del menú)");
        return null;
    }
    
    return $pacientes;
}

function obtenerDentistaAleatorio() {
    $dentista = Usuario::where('rol', 'dentista')->where('activo', true)->inRandomOrder()->first();
    
    if (!$dentista) {
        mostrarError("No hay dentistas activos en el sistema.");
        mostrarInfo("Necesitas crear un dentista primero (opción 2 del menú)");
        return null;
    }
    
    return $dentista;
}

// Motivos de consulta variados
$motivosConsulta = [
    'Consulta general',
    'Limpieza dental',
    'Extracción de muela',
    'Control de ortodoncia',
    'Blanqueamiento dental',
    'Endodoncia',
    'Implante dental',
    'Colocación de corona',
    'Tratamiento de caries',
    'Revisión de prótesis',
    'Dolor dental',
    'Urgencia - Dolor agudo',
    'Control post-operatorio',
    'Ajuste de brackets',
    'Radiografía dental',
    'Valoración para implantes',
    'Sellado de fisuras',
    'Tratamiento de encías',
    'Consulta estética',
    'Retiro de puntos'
];

$estadosCitas = [
    'pendiente' => 0.5,   // 50% pendientes
    'confirmada' => 0.4,  // 40% confirmadas
    'atendida' => 0.1     // 10% atendidas
];

// Horarios disponibles (de 8:00 a 18:00, cada 30 minutos)
function generarHorariosDelDia($fecha, $cantidad) {
    $horarios = [];
    $horaInicio = 8;
    $horaFin = 18;
    
    // Generar slots de 30 minutos
    for ($hora = $horaInicio; $hora < $horaFin; $hora++) {
        foreach ([0, 30] as $minuto) {
            $horarios[] = $fecha->copy()->setTime($hora, $minuto);
        }
    }
    
    // Mezclar y tomar solo la cantidad necesaria
    shuffle($horarios);
    return array_slice($horarios, 0, $cantidad);
}

function seleccionarEstadoAleatorio($probabilidades) {
    $rand = mt_rand() / mt_getrandmax();
    $acumulado = 0;
    
    foreach ($probabilidades as $estado => $prob) {
        $acumulado += $prob;
        if ($rand <= $acumulado) {
            return $estado;
        }
    }
    
    return 'pendiente';
}

// ========================================
// SCRIPT PRINCIPAL
// ========================================

try {
    mostrarTitulo("🗓️  CREAR CITAS DE PRUEBA - DENTALSYNC");
    
    // Verificar que haya pacientes y dentistas
    $totalPacientes = Paciente::count();
    $totalDentistas = Usuario::where('rol', 'dentista')->where('activo', true)->count();
    
    if ($totalPacientes === 0) {
        mostrarError("No hay pacientes en la base de datos.");
        mostrarInfo("Por favor, crea pacientes primero usando la opción 3 del menú.");
        exit(1);
    }
    
    if ($totalDentistas === 0) {
        mostrarError("No hay dentistas en la base de datos.");
        mostrarInfo("Por favor, crea un dentista usando la opción 2 del menú.");
        exit(1);
    }
    
    mostrarInfo("Pacientes disponibles: $totalPacientes");
    mostrarInfo("Dentistas disponibles: $totalDentistas");
    echo "\n";
    
    // Solicitar información
    $fecha = solicitarFecha();
    $cantidad = solicitarCantidad();
    
    echo "\n";
    mostrarInfo("Fecha seleccionada: " . $fecha->format('Y-m-d (l)'));
    mostrarInfo("Cantidad de citas: $cantidad");
    echo "\n";
    
    // Confirmar
    echo "¿Deseas continuar? (s/n): ";
    $confirmar = trim(fgets(STDIN));
    
    if (strtolower($confirmar) !== 's') {
        mostrarInfo("Operación cancelada.");
        exit(0);
    }
    
    echo "\n";
    mostrarInfo("Creando citas...");
    echo "\n";
    
    // Obtener pacientes aleatorios
    $pacientes = obtenerPacientesAleatorios($cantidad);
    if (!$pacientes) {
        exit(1);
    }
    
    // Obtener dentista
    $dentista = obtenerDentistaAleatorio();
    if (!$dentista) {
        exit(1);
    }
    
    // Generar horarios
    $horarios = generarHorariosDelDia($fecha, $cantidad);
    
    // Crear las citas
    $citasCreadas = [];
    
    DB::beginTransaction();
    
    try {
        foreach ($pacientes as $index => $paciente) {
            $motivo = $motivosConsulta[array_rand($motivosConsulta)];
            $estado = seleccionarEstadoAleatorio($estadosCitas);
            $horario = $horarios[$index];
            
            $cita = Cita::create([
                'paciente_id' => $paciente->id,
                'usuario_id' => $dentista->id,
                'fecha' => $horario,
                'motivo' => $motivo,
                'estado' => $estado,
                'fecha_atendida' => null
            ]);
            
            $citasCreadas[] = $cita;
            
            // Mostrar progreso
            echo sprintf(
                "  ✓ Cita #%d - %s | %s | Paciente: %s | Estado: %s\n",
                $cita->id,
                $horario->format('H:i'),
                $motivo,
                $paciente->nombre_completo,
                ucfirst($estado)
            );
        }
        
        DB::commit();
        
        echo "\n";
        mostrarExito("¡$cantidad citas creadas exitosamente!");
        
        // Resumen
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════════╗\n";
        echo "║                         RESUMEN DE CITAS                         ║\n";
        echo "╚══════════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "📅 Fecha: " . $fecha->format('Y-m-d (l)') . "\n";
        echo "👨‍⚕️ Dentista: " . $dentista->nombre . "\n";
        echo "📊 Total de citas: $cantidad\n";
        echo "\n";
        echo "Estados:\n";
        
        $estadosCount = [
            'pendiente' => 0,
            'confirmada' => 0,
            'cancelada' => 0,
            'atendida' => 0
        ];
        
        foreach ($citasCreadas as $cita) {
            if (isset($estadosCount[$cita->estado])) {
                $estadosCount[$cita->estado]++;
            }
        }
        
        foreach ($estadosCount as $estado => $count) {
            if ($count > 0) {
                echo "  • " . ucfirst($estado) . ": $count\n";
            }
        }
        
        echo "\n";
        mostrarInfo("Las citas ya están disponibles en el sistema.");
        
    } catch (\Exception $e) {
        DB::rollBack();
        mostrarError("Error al crear citas: " . $e->getMessage());
        exit(1);
    }
    
} catch (\Exception $e) {
    mostrarError("Error general: " . $e->getMessage());
    exit(1);
}

echo "\n";
?>
