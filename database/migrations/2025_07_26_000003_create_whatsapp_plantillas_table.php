<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla whatsapp_plantillas
 * 
 * Esta migración crea la tabla que gestiona las plantillas de mensajes
 * predefinidas para WhatsApp. Permite al consultorio dental mantener
 * mensajes estandarizados y profesionales para diferentes situaciones.
 * 
 * Categorías de plantillas:
 * - recordatorio: Recordatorios de citas y tratamientos
 * - confirmacion: Confirmaciones de citas y procedimientos
 * - pago: Notificaciones y recordatorios de pago
 * - tratamiento: Información sobre tratamientos dentales
 * - bienvenida: Mensajes de bienvenida para nuevos pacientes
 * - general: Plantillas de uso general
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla whatsapp_plantillas
     * 
     * Esta tabla permite crear y gestionar plantillas reutilizables
     * de mensajes para comunicación profesional con pacientes.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('whatsapp_plantillas', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información básica de la plantilla
            $table->string('nombre')
                  ->comment('Nombre descriptivo de la plantilla');
            $table->text('descripcion')
                  ->nullable()
                  ->comment('Descripción del propósito de la plantilla');
            $table->enum('categoria', ['recordatorio', 'confirmacion', 'pago', 'tratamiento', 'bienvenida', 'general'])
                  ->default('general')
                  ->comment('Categoría de la plantilla para organización');
            
            // Contenido de la plantilla
            $table->text('contenido')
                  ->comment('Texto de la plantilla con variables reemplazables');
            
            // Control de estado y uso
            $table->boolean('activa')
                  ->default(true)
                  ->comment('Indica si la plantilla está disponible para uso');
            $table->integer('usos')
                  ->default(0)
                  ->comment('Contador de veces que se ha utilizado la plantilla');
            
            // Variables dinámicas
            $table->json('variables_detectadas')
                  ->nullable()
                  ->comment('Array JSON de variables detectadas como {nombre}, {fecha}, etc.');
            
            // Usuario creador
            $table->unsignedBigInteger('creado_por')
                  ->nullable()
                  ->comment('ID del usuario que creó la plantilla');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas e índices
            $table->foreign('creado_por')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('set null')
                  ->comment('Relación con tabla usuarios');
            
            $table->index(['categoria', 'activa'], 'idx_categoria_activa');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla whatsapp_plantillas
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_plantillas');
    }
};
