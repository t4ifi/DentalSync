<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla whatsapp_mensajes
 * 
 * Esta migración crea la tabla que almacena todos los mensajes individuales
 * de WhatsApp intercambiados en las conversaciones del consultorio dental.
 * Proporciona seguimiento detallado de entrega, lectura y estado de mensajes.
 * 
 * Estados de mensaje:
 * - enviando: Mensaje en proceso de envío
 * - enviado: Mensaje enviado exitosamente
 * - entregado: Mensaje entregado al destinatario
 * - leido: Mensaje leído por el destinatario
 * - error: Error en el envío del mensaje
 * 
 * Tipos de mensaje soportados:
 * - texto: Mensajes de texto plano
 * - imagen: Archivos de imagen
 * - documento: Documentos y archivos
 * - audio: Mensajes de voz o audio
 * - video: Archivos de video
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla whatsapp_mensajes
     * 
     * Esta tabla registra todos los mensajes individuales de WhatsApp
     * con seguimiento completo de estado y metadatos.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('whatsapp_mensajes', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Relación con conversación
            $table->unsignedBigInteger('conversacion_id')
                  ->comment('ID de la conversación a la que pertenece el mensaje');
            
            // Identificación del mensaje en WhatsApp
            $table->string('mensaje_whatsapp_id')
                  ->nullable()
                  ->index()
                  ->comment('ID único del mensaje en la API de WhatsApp');
            
            // Contenido del mensaje
            $table->text('contenido')
                  ->comment('Contenido del mensaje (texto, URL de archivo, etc.)');
            
            // Dirección y estado del mensaje
            $table->boolean('es_propio')
                  ->default(true)
                  ->comment('Indica si el mensaje fue enviado por el consultorio (true) o recibido (false)');
            $table->enum('estado', ['enviando', 'enviado', 'entregado', 'leido', 'error'])
                  ->default('enviando')
                  ->comment('Estado actual del mensaje en WhatsApp');
            $table->enum('tipo', ['texto', 'imagen', 'documento', 'audio', 'video'])
                  ->default('texto')
                  ->comment('Tipo de contenido del mensaje');
            
            // Metadatos y archivos adjuntos
            $table->json('metadata')
                  ->nullable()
                  ->comment('Datos adicionales JSON (archivos adjuntos, coordenadas, etc.)');
            
            // Control de fechas de seguimiento
            $table->timestamp('fecha_envio')
                  ->comment('Fecha y hora de envío del mensaje');
            $table->timestamp('fecha_entregado')
                  ->nullable()
                  ->comment('Fecha y hora de entrega confirmada');
            $table->timestamp('fecha_leido')
                  ->nullable()
                  ->comment('Fecha y hora de lectura confirmada');
            
            // Manejo de errores
            $table->text('error_mensaje')
                  ->nullable()
                  ->comment('Descripción del error si el mensaje falló');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas e índices
            $table->foreign('conversacion_id')
                  ->references('id')
                  ->on('whatsapp_conversaciones')
                  ->onDelete('cascade')
                  ->comment('Relación con tabla whatsapp_conversaciones');
            
            $table->index(['conversacion_id', 'fecha_envio'], 'idx_conversacion_fecha');
            $table->index(['es_propio', 'estado'], 'idx_propio_estado');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla whatsapp_mensajes
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_mensajes');
    }
};
