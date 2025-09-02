<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla placas_dentales
 * 
 * Esta migración crea la tabla que gestiona las radiografías y placas
 * dentales de los pacientes. Permite almacenar información sobre
 * estudios radiológicos realizados para diagnóstico y seguimiento.
 * 
 * Funcionalidades principales:
 * - Registro de placas radiográficas y estudios dentales
 * - Almacenamiento de metadatos de archivos de imagen
 * - Control de fechas y lugares de realización
 * - Clasificación por tipo de estudio radiológico
 * - Vinculación con el historial clínico del paciente
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-22
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla placas_dentales
     * 
     * Esta tabla almacena información sobre estudios radiológicos
     * dentales realizados a los pacientes del consultorio.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('placas_dentales', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información del estudio radiológico
            $table->date('fecha')
                  ->comment('Fecha en que se realizó la placa o radiografía');
            $table->string('lugar', 255)
                  ->comment('Lugar o centro médico donde se realizó el estudio');
            $table->string('tipo', 100)
                  ->comment('Tipo de placa dental (panorámica, periapical, lateral, etc.)');
            $table->string('archivo_url', 500)
                  ->nullable()
                  ->comment('URL o ruta del archivo de imagen de la placa dental');
            
            // Relaciones con otras tablas
            $table->unsignedBigInteger('paciente_id')
                  ->comment('ID del paciente al que pertenece la placa dental');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade')
                  ->comment('Relación con tabla pacientes');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla placas_dentales
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('placas_dentales');
    }
};
