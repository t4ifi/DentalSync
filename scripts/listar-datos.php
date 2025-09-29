#!/usr/bin/env php
<?php

/**
 * Script para listar y gestionar datos de DentalSync
 * Uso: php listar-datos.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Cita;
use App\Models\Pago;

// Inicializar Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "\n🦷 ========== DENTALSYNC - LISTAR DATOS ==========\n\n";

// Función para leer input
function leerInput($mensaje) {
    echo $mensaje;
    return trim(fgets(STDIN));
}

// Menú principal
function mostrarMenu() {
    echo "📋 ¿Qué deseas ver?\n";
    echo "1. 👨‍⚕️ Usuarios\n";
    echo "2. 👥 Pacientes\n";
    echo "3. 🦷 Tratamientos\n";
    echo "4. 📅 Citas\n";
    echo "5. 💰 Pagos\n";
    echo "6. 📊 Estadísticas generales\n";
    echo "0. 🚪 Salir\n\n";
}

try {
    while (true) {
        mostrarMenu();
        $opcion = leerInput("Selecciona una opción (0-6): ");
        
        switch ($opcion) {
            case '1':
                // Listar usuarios
                echo "\n👨‍⚕️ USUARIOS REGISTRADOS:\n";
                echo str_repeat("=", 80) . "\n";
                
                $usuarios = Usuario::orderBy('created_at', 'desc')->get();
                
                if ($usuarios->isEmpty()) {
                    echo "❌ No hay usuarios registrados.\n\n";
                } else {
                    foreach ($usuarios as $usuario) {
                        echo "ID: {$usuario->id}\n";
                        echo "Usuario: {$usuario->usuario}\n";
                        echo "Nombre: {$usuario->nombre}\n";
                        echo "Rol: " . ucfirst($usuario->rol) . "\n";
                        echo "Estado: " . ($usuario->activo ? 'Activo' : 'Inactivo') . "\n";
                        echo "Último acceso: " . ($usuario->ultimo_acceso ?: 'Nunca') . "\n";
                        echo "Registro: {$usuario->created_at}\n";
                        echo str_repeat("-", 40) . "\n";
                    }
                    echo "Total: {$usuarios->count()} usuarios\n\n";
                }
                break;
                
            case '2':
                // Listar pacientes
                echo "\n👥 PACIENTES REGISTRADOS:\n";
                echo str_repeat("=", 80) . "\n";
                
                $pacientes = Paciente::orderBy('created_at', 'desc')->get();
                
                if ($pacientes->isEmpty()) {
                    echo "❌ No hay pacientes registrados.\n\n";
                } else {
                    foreach ($pacientes as $paciente) {
                        $edad = $paciente->fecha_nacimiento ? 
                            (new DateTime())->diff(new DateTime($paciente->fecha_nacimiento))->y : 'N/A';
                        
                        echo "ID: {$paciente->id}\n";
                        echo "Nombre: {$paciente->nombre}\n";
                        echo "Email: " . ($paciente->email ?: 'No especificado') . "\n";
                        echo "Teléfono: {$paciente->telefono}\n";
                        echo "Cédula: {$paciente->cedula}\n";
                        echo "Edad: {$edad} años\n";
                        echo "Género: " . ucfirst($paciente->genero) . "\n";
                        echo "Dirección: {$paciente->direccion}\n";
                        if ($paciente->mutualista) {
                            echo "Mutualista: {$paciente->mutualista}\n";
                        }
                        echo "Estado: " . ucfirst($paciente->estado) . "\n";
                        echo "Registro: {$paciente->fecha_registro}\n";
                        echo str_repeat("-", 40) . "\n";
                    }
                    echo "Total: {$pacientes->count()} pacientes\n\n";
                }
                break;
                
            case '3':
                // Listar tratamientos
                echo "\n🦷 TRATAMIENTOS DISPONIBLES:\n";
                echo str_repeat("=", 80) . "\n";
                
                $tratamientos = Tratamiento::orderBy('categoria')->orderBy('nombre')->get();
                
                if ($tratamientos->isEmpty()) {
                    echo "❌ No hay tratamientos registrados.\n\n";
                } else {
                    $categoriaActual = '';
                    foreach ($tratamientos as $tratamiento) {
                        if ($tratamiento->categoria !== $categoriaActual) {
                            $categoriaActual = $tratamiento->categoria;
                            echo "\n📂 CATEGORÍA: {$categoriaActual}\n";
                            echo str_repeat("-", 40) . "\n";
                        }
                        
                        echo "ID: {$tratamiento->id}\n";
                        echo "Nombre: {$tratamiento->nombre}\n";
                        echo "Descripción: {$tratamiento->descripcion}\n";
                        echo "Precio: \${$tratamiento->precio}\n";
                        echo "Duración: {$tratamiento->duracion_estimada} minutos\n";
                        echo "Estado: " . ucfirst($tratamiento->estado) . "\n";
                        echo str_repeat("·", 30) . "\n";
                    }
                    echo "\nTotal: {$tratamientos->count()} tratamientos\n\n";
                }
                break;
                
            case '4':
                // Listar citas
                echo "\n📅 CITAS REGISTRADAS:\n";
                echo str_repeat("=", 80) . "\n";
                
                $citas = Cita::with(['paciente', 'usuario', 'tratamiento'])
                           ->orderBy('fecha_hora', 'desc')
                           ->limit(20)
                           ->get();
                
                if ($citas->isEmpty()) {
                    echo "❌ No hay citas registradas.\n\n";
                } else {
                    foreach ($citas as $cita) {
                        echo "ID: {$cita->id}\n";
                        echo "Paciente: " . ($cita->paciente->nombre ?? 'N/A') . "\n";
                        echo "Doctor: " . ($cita->usuario->nombre ?? 'N/A') . "\n";
                        echo "Tratamiento: " . ($cita->tratamiento->nombre ?? 'N/A') . "\n";
                        echo "Fecha: {$cita->fecha_hora}\n";
                        echo "Estado: " . ucfirst($cita->estado) . "\n";
                        if ($cita->notas) {
                            echo "Notas: {$cita->notas}\n";
                        }
                        echo str_repeat("-", 40) . "\n";
                    }
                    echo "Mostrando últimas 20 citas\n\n";
                }
                break;
                
            case '5':
                // Listar pagos
                echo "\n💰 PAGOS REGISTRADOS:\n";
                echo str_repeat("=", 80) . "\n";
                
                $pagos = Pago::with(['paciente'])
                            ->orderBy('fecha_pago', 'desc')
                            ->limit(20)
                            ->get();
                
                if ($pagos->isEmpty()) {
                    echo "❌ No hay pagos registrados.\n\n";
                } else {
                    $totalPagos = 0;
                    foreach ($pagos as $pago) {
                        echo "ID: {$pago->id}\n";
                        echo "Paciente: " . ($pago->paciente->nombre ?? 'N/A') . "\n";
                        echo "Monto: \${$pago->monto}\n";
                        echo "Método: " . ucfirst($pago->metodo_pago) . "\n";
                        echo "Estado: " . ucfirst($pago->estado) . "\n";
                        echo "Fecha: {$pago->fecha_pago}\n";
                        if ($pago->descripcion) {
                            echo "Descripción: {$pago->descripcion}\n";
                        }
                        echo str_repeat("-", 40) . "\n";
                        $totalPagos += $pago->monto;
                    }
                    echo "Total mostrado: \${$totalPagos}\n";
                    echo "Mostrando últimos 20 pagos\n\n";
                }
                break;
                
            case '6':
                // Estadísticas generales
                echo "\n📊 ESTADÍSTICAS GENERALES:\n";
                echo str_repeat("=", 80) . "\n";
                
                $totalUsuarios = Usuario::count();
                $totalPacientes = Paciente::count();
                $totalTratamientos = Tratamiento::count();
                $totalCitas = Cita::count();
                $totalPagos = Pago::count();
                
                echo "👨‍⚕️ Total Usuarios: {$totalUsuarios}\n";
                echo "   • Dentistas: " . Usuario::where('rol', 'dentista')->count() . "\n";
                echo "   • Recepcionistas: " . Usuario::where('rol', 'recepcionista')->count() . "\n";
                echo "   • Activos: " . Usuario::where('activo', true)->count() . "\n";
                echo "   • Inactivos: " . Usuario::where('activo', false)->count() . "\n\n";
                
                echo "👥 Total Pacientes: {$totalPacientes}\n";
                echo "   • Activos: " . Paciente::where('estado', 'activo')->count() . "\n";
                echo "   • Inactivos: " . Paciente::where('estado', 'inactivo')->count() . "\n\n";
                
                echo "🦷 Total Tratamientos: {$totalTratamientos}\n";
                echo "📅 Total Citas: {$totalCitas}\n";
                
                if ($totalCitas > 0) {
                    echo "   • Programadas: " . Cita::where('estado', 'programada')->count() . "\n";
                    echo "   • Completadas: " . Cita::where('estado', 'completada')->count() . "\n";
                    echo "   • Canceladas: " . Cita::where('estado', 'cancelada')->count() . "\n";
                }
                
                echo "\n💰 Total Pagos: {$totalPagos}\n";
                
                if ($totalPagos > 0) {
                    $sumaPagos = Pago::sum('monto');
                    echo "   • Monto total: \${$sumaPagos}\n";
                    echo "   • Pagos completados: " . Pago::where('estado', 'completado')->count() . "\n";
                    echo "   • Pagos pendientes: " . Pago::where('estado', 'pendiente')->count() . "\n";
                }
                
                echo "\n";
                break;
                
            case '0':
                echo "🚪 ¡Hasta luego!\n\n";
                exit(0);
                
            default:
                echo "❌ Opción inválida. Selecciona 0-6.\n\n";
                break;
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}