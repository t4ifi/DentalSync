<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla detalle_pagos
 * 
 * Esta migración crea la tabla que gestiona los detalles específicos de
 * cada pago realizado. Permite dividir los pagos en cuotas, pagos parciales
 * y llevar un control detallado del sistema financiero del consultorio.
 * 
 * Tipos de pago admitidos:
 * - cuota_fija: Pagos divididos en cuotas fijas mensuales
 * - pago_variable: Pagos de montos variables sin periodicidad fija
 * - pago_completo: Pago total del tratamiento de una sola vez
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla detalle_pagos
     * 
     * Esta tabla proporciona un desglose detallado de cada pago,
     * permitiendo el manejo de cuotas y pagos parciales.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('detalle_pagos', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Relación con pago principal
            $table->foreignId('pago_id')
                  ->constrained('pagos')
                  ->onDelete('cascade')
                  ->comment('ID del pago principal al que pertenece este detalle');
            
            // Información específica del detalle de pago
            $table->date('fecha_pago')
                  ->comment('Fecha específica en que se realizó este pago parcial');
            $table->decimal('monto_parcial', 10, 2)
                  ->comment('Monto específico de este pago parcial');
            $table->text('descripcion')
                  ->nullable()
                  ->comment('Descripción adicional del pago parcial');
            $table->enum('tipo_pago', ['cuota_fija', 'pago_variable', 'pago_completo'])
                  ->default('pago_completo')
                  ->comment('Tipo de pago realizado');
            $table->integer('numero_cuota')
                  ->nullable()
                  ->comment('Número de cuota cuando el tipo es cuota_fija');
            
            // Usuario que registró el pago
            $table->foreignId('usuario_id')
                  ->constrained('usuarios')
                  ->onDelete('cascade')
                  ->comment('ID del usuario que registró este detalle de pago');
            
            // Campos de auditoría
            $table->timestamps();
            
            // Índices para optimización de consultas
            $table->index(['pago_id', 'fecha_pago'], 'idx_detalle_pago_fecha');
            $table->index(['tipo_pago'], 'idx_detalle_tipo_pago');
            $table->index(['numero_cuota'], 'idx_detalle_numero_cuota');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla detalle_pagos
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pagos');
    }
};
