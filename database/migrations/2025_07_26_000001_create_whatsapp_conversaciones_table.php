<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla whatsapp_conversaciones
 * 
 * Esta migración crea la tabla que gestiona las conversaciones de WhatsApp
 * entre el consultorio dental y los pacientes. Permite mantener un historial
 * organizado de comunicaciones para seguimiento y atención al cliente.
 * 
 * Estados de conversación:
 * - activa: Conversación en curso con intercambio reciente
 * - pausada: Conversación temporal en pausa
 * - cerrada: Conversación finalizada
 * - bloqueada: Contacto bloqueado por el consultorio
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla whatsapp_conversaciones
     * 
     * Esta tabla centraliza las conversaciones de WhatsApp con pacientes,
     * proporcionando control y seguimiento de comunicaciones.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('whatsapp_conversaciones', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información del contacto
            $table->unsignedBigInteger('paciente_id')
                  ->comment('ID del paciente asociado a la conversación');
            $table->string('telefono')
                  ->index()
                  ->comment('Número de teléfono de WhatsApp del paciente');
            $table->string('nombre_contacto')
                  ->comment('Nombre del contacto como aparece en WhatsApp');
            
            // Estado y control de la conversación
            $table->enum('estado', ['activa', 'pausada', 'cerrada', 'bloqueada'])
                  ->default('activa')
                  ->comment('Estado actual de la conversación');
            
            // Información del último mensaje
            $table->timestamp('ultimo_mensaje_fecha')
                  ->nullable()
                  ->comment('Fecha del último mensaje intercambiado');
            $table->text('ultimo_mensaje_texto')
                  ->nullable()
                  ->comment('Texto del último mensaje para vista previa');
            $table->boolean('ultimo_mensaje_propio')
                  ->default(false)
                  ->comment('Indica si el último mensaje fue enviado por el consultorio');
            
            // Control de mensajes
            $table->integer('mensajes_no_leidos')
                  ->default(0)
                  ->comment('Cantidad de mensajes no leídos del paciente');
            $table->json('metadata')
                  ->nullable()
                  ->comment('Datos adicionales JSON (avatar, configuraciones, etc.)');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas e índices
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade')
                  ->comment('Relación con tabla pacientes');
            
            $table->unique(['paciente_id', 'telefono'], 'idx_paciente_telefono_unique');
            $table->index(['estado', 'ultimo_mensaje_fecha'], 'idx_estado_ultimo_mensaje');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla whatsapp_conversaciones
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversaciones');
    }
};
