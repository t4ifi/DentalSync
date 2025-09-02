<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla whatsapp_envios_programados
 * 
 * Esta migración crea la tabla que gestiona los envíos programados de WhatsApp.
 * Permite al consultorio dental programar mensajes para ser enviados en fechas
 * y horas específicas, tanto individuales como masivos.
 * 
 * Estados de envío:
 * - pendiente: Envío programado en espera
 * - enviado: Mensaje enviado exitosamente
 * - error: Error en el envío del mensaje
 * - cancelado: Envío cancelado por el usuario
 * 
 * Tipos de envío:
 * - individual: Mensaje a un destinatario específico
 * - masivo: Mensaje a múltiples destinatarios
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla whatsapp_envios_programados
     * 
     * Esta tabla permite programar envíos futuros de mensajes de WhatsApp
     * con control de estado y seguimiento de ejecución.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('whatsapp_envios_programados', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información del destinatario y mensaje
            $table->string('telefono')
                  ->comment('Número de teléfono principal del destinatario');
            $table->text('mensaje')
                  ->comment('Contenido del mensaje a enviar');
            
            // Control de programación
            $table->timestamp('fecha_programada')
                  ->comment('Fecha y hora programada para el envío');
            $table->enum('estado', ['pendiente', 'enviado', 'error', 'cancelado'])
                  ->default('pendiente')
                  ->comment('Estado actual del envío programado');
            $table->enum('tipo_envio', ['individual', 'masivo'])
                  ->default('individual')
                  ->comment('Tipo de envío: individual o masivo');
            
            // Relaciones opcionales
            $table->unsignedBigInteger('automatizacion_id')
                  ->nullable()
                  ->comment('ID de la automatización que generó este envío (opcional)');
            
            // Datos para envíos masivos
            $table->json('destinatarios')
                  ->nullable()
                  ->comment('Array JSON de destinatarios para envíos masivos');
            
            // Control de errores y ejecución
            $table->text('error_mensaje')
                  ->nullable()
                  ->comment('Descripción del error si el envío falló');
            $table->timestamp('fecha_envio')
                  ->nullable()
                  ->comment('Fecha y hora real del envío ejecutado');
            
            // Usuario que programó el envío
            $table->unsignedBigInteger('creado_por')
                  ->nullable()
                  ->comment('ID del usuario que programó el envío');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas e índices
            $table->foreign('automatizacion_id')
                  ->references('id')
                  ->on('whatsapp_automatizaciones')
                  ->onDelete('set null')
                  ->comment('Relación opcional con automatizaciones');
            
            $table->foreign('creado_por')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('set null')
                  ->comment('Relación con tabla usuarios');
            
            $table->index(['estado', 'fecha_programada'], 'idx_estado_fecha_programada');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla whatsapp_envios_programados
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_envios_programados');
    }
};
