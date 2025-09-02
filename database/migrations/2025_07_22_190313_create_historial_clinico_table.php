<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla historial_clinico
 * 
 * Esta migración crea la tabla que almacena el historial médico-dental
 * de cada paciente. Registra todas las visitas, tratamientos realizados
 * y observaciones clínicas importantes.
 * 
 * Funcionalidades principales:
 * - Registro cronológico de visitas médicas
 * - Documentación de tratamientos aplicados
 * - Observaciones y notas clínicas del dentista
 * - Vinculación con pacientes y tratamientos específicos
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-22
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla historial_clinico
     * 
     * Esta tabla mantiene el registro médico completo de cada paciente,
     * permitiendo el seguimiento histórico de su atención dental.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('historial_clinico', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información de la visita médica
            $table->date('fecha_visita')
                  ->comment('Fecha en que se realizó la consulta o procedimiento');
            $table->text('tratamiento')
                  ->comment('Descripción del tratamiento o procedimiento realizado');
            $table->text('observaciones')
                  ->nullable()
                  ->comment('Observaciones clínicas adicionales del dentista');
            
            // Relaciones con otras tablas
            $table->unsignedBigInteger('paciente_id')
                  ->comment('ID del paciente al que pertenece este registro');
            $table->unsignedBigInteger('tratamiento_id')
                  ->nullable()
                  ->comment('ID del tratamiento relacionado (opcional)');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade')
                  ->comment('Relación con tabla pacientes');
            $table->foreign('tratamiento_id')
                  ->references('id')
                  ->on('tratamientos')
                  ->onDelete('set null')
                  ->comment('Relación opcional con tabla tratamientos');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla historial_clinico
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_clinico');
    }
};
