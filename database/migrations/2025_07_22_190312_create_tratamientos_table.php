<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla tratamientos
 * 
 * Esta migración crea la tabla que almacena los tratamientos dentales
 * asignados a los pacientes. Cada tratamiento está vinculado a un paciente
 * específico y al dentista responsable del mismo.
 * 
 * Funcionalidades principales:
 * - Registro de tratamientos dentales planificados
 * - Seguimiento del estado del tratamiento (activo/finalizado)
 * - Relación con pacientes y dentistas responsables
 * - Control de fechas de inicio de tratamiento
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-22
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla tratamientos
     * 
     * Esta tabla gestiona los planes de tratamiento dental asignados
     * a cada paciente, permitiendo el seguimiento del progreso médico.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información del tratamiento
            $table->text('descripcion')
                  ->comment('Descripción detallada del tratamiento dental planificado');
            $table->date('fecha_inicio')
                  ->comment('Fecha de inicio del tratamiento');
            $table->enum('estado', ['activo', 'finalizado'])
                  ->default('activo')
                  ->comment('Estado actual del tratamiento (activo: en proceso, finalizado: completado)');
            
            // Relaciones con otras tablas
            $table->unsignedBigInteger('paciente_id')
                  ->comment('ID del paciente que recibe el tratamiento');
            $table->unsignedBigInteger('usuario_id')
                  ->comment('ID del dentista responsable del tratamiento');
            
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
     * Revertir las migraciones eliminando la tabla tratamientos
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};
