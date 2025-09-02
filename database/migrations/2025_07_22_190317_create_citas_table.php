<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla citas
 * 
 * Esta migración crea la tabla que gestiona el sistema de citas del consultorio
 * dental. Permite agendar, programar y controlar el estado de las consultas
 * médicas de los pacientes.
 * 
 * Estados de cita disponibles:
 * - pendiente: Cita agendada pero no confirmada
 * - confirmada: Cita confirmada por el paciente
 * - cancelada: Cita cancelada por cualquier motivo
 * - atendida: Cita que ya fue realizada
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-22
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla citas
     * 
     * Esta tabla gestiona la agenda médica del consultorio, permitiendo
     * el control completo de las citas de los pacientes.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información de la cita
            $table->dateTime('fecha')
                  ->comment('Fecha y hora programada para la cita');
            $table->text('motivo')
                  ->comment('Motivo o descripción de la consulta');
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'atendida'])
                  ->default('pendiente')
                  ->comment('Estado actual de la cita');
            $table->dateTime('fecha_atendida')
                  ->nullable()
                  ->comment('Fecha y hora real en que se atendió la cita');
            
            // Relaciones con otras tablas
            $table->unsignedBigInteger('paciente_id')
                  ->comment('ID del paciente que tiene la cita');
            $table->unsignedBigInteger('usuario_id')
                  ->comment('ID del dentista asignado a la cita');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade')
                  ->comment('Relación con tabla pacientes');
            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('cascade')
                  ->comment('Relación con tabla usuarios (dentistas)');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla citas
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
