<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla whatsapp_automatizaciones
 * 
 * Esta migración crea la tabla que gestiona las automatizaciones de WhatsApp
 * del consultorio dental. Permite programar envíos automáticos de mensajes
 * basados en diferentes condiciones y eventos del sistema.
 * 
 * Tipos de automatización:
 * - recordatorio: Recordatorios automáticos de citas
 * - seguimiento: Seguimiento post-tratamiento
 * - bienvenida: Mensajes automáticos a nuevos pacientes
 * - cumpleanos: Felicitaciones de cumpleaños
 * - pago: Recordatorios de pagos pendientes
 * 
 * Audiencias objetivo:
 * - todos: Todos los pacientes que cumplan la condición
 * - nuevos: Solo pacientes nuevos
 * - recurrentes: Pacientes con múltiples visitas
 * - activos: Pacientes con actividad reciente
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla whatsapp_automatizaciones
     * 
     * Esta tabla permite configurar envíos automáticos de mensajes
     * de WhatsApp basados en eventos y condiciones del consultorio.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('whatsapp_automatizaciones', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información básica de la automatización
            $table->string('nombre')
                  ->comment('Nombre descriptivo de la automatización');
            $table->text('descripcion')
                  ->nullable()
                  ->comment('Descripción del propósito de la automatización');
            $table->enum('tipo', ['recordatorio', 'seguimiento', 'bienvenida', 'cumpleanos', 'pago'])
                  ->default('recordatorio')
                  ->comment('Tipo de automatización a ejecutar');
            
            // Configuración de condiciones
            $table->json('condicion')
                  ->comment('Condiciones JSON para activar la automatización (ej: {tipo: "antes_cita", valor: 24, unidad: "horas"})');
            $table->enum('audiencia', ['todos', 'nuevos', 'recurrentes', 'activos'])
                  ->default('todos')
                  ->comment('Audiencia objetivo para la automatización');
            
            // Contenido del mensaje
            $table->text('mensaje')
                  ->comment('Mensaje que se enviará automáticamente');
            
            // Control de estado y límites
            $table->enum('estado', ['activa', 'inactiva', 'pausada'])
                  ->default('activa')
                  ->comment('Estado actual de la automatización');
            $table->boolean('limite_envios')
                  ->default(false)
                  ->comment('Indica si hay límite de envíos por paciente');
            $table->integer('max_envios_paciente')
                  ->default(1)
                  ->comment('Máximo número de envíos por paciente');
            
            // Estadísticas de ejecución
            $table->integer('ejecutada')
                  ->default(0)
                  ->comment('Número total de veces que se ha ejecutado');
            $table->integer('exitosas')
                  ->default(0)
                  ->comment('Número de ejecuciones exitosas');
            $table->integer('fallidas')
                  ->default(0)
                  ->comment('Número de ejecuciones fallidas');
            $table->timestamp('ultimo_ejecutado')
                  ->nullable()
                  ->comment('Fecha de la última ejecución');
            
            // Usuario creador
            $table->unsignedBigInteger('creado_por')
                  ->nullable()
                  ->comment('ID del usuario que creó la automatización');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas e índices
            $table->foreign('creado_por')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('set null')
                  ->comment('Relación con tabla usuarios');
            
            $table->index(['estado', 'tipo'], 'idx_estado_tipo');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla whatsapp_automatizaciones
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_automatizaciones');
    }
};
