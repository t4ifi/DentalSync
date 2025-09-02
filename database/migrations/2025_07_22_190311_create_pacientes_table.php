<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla pacientes
 * 
 * Esta migración crea la tabla principal de pacientes del consultorio dental.
 * Almacena la información básica de identificación y contacto de cada paciente.
 * 
 * Nota: Esta es la versión inicial de la tabla. Campos adicionales como
 * motivo_consulta, alergias y observaciones se agregan en migraciones posteriores.
 * 
 * Relaciones futuras:
 * - Uno a muchos con citas, tratamientos, pagos, placas dentales
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-22
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla pacientes
     * 
     * Esta tabla almacena la información básica de todos los pacientes
     * del consultorio dental, incluyendo datos de contacto y fechas importantes.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información personal básica
            $table->string('nombre_completo', 255)
                  ->comment('Nombre completo del paciente');
            $table->string('telefono', 20)
                  ->nullable()
                  ->comment('Número de teléfono de contacto principal');
            
            // Fechas importantes
            $table->date('fecha_nacimiento')
                  ->nullable()
                  ->comment('Fecha de nacimiento para cálculo de edad');
            $table->date('ultima_visita')
                  ->nullable()
                  ->comment('Fecha de la última consulta o tratamiento');
            
            // Timestamps automáticos de Laravel
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla pacientes
     * 
     * ATENCIÓN: Este rollback eliminará todos los pacientes del sistema
     * y puede afectar múltiples tablas relacionadas (citas, tratamientos, etc.).
     * 
     * @return void
     */
    public function down(): void
    {
        // Eliminar la tabla pacientes y todas sus restricciones
        Schema::dropIfExists('pacientes');
    }
};
