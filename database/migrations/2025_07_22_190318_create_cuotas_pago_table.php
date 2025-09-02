<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla cuotas_pago
 * 
 * Esta migración crea la tabla para gestionar las cuotas individuales de pagos
 * que se realizan en modalidad de cuotas fijas. Cada cuota tiene su propio
 * número, monto, fecha de vencimiento y estado de pago.
 * 
 * Relaciones:
 * - Pertenece a la tabla 'pagos' (FK: pago_id)
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-22
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla cuotas_pago
     * 
     * Esta tabla almacena las cuotas individuales de pagos fraccionados,
     * permitiendo un control detallado de cada cuota con su estado y vencimiento.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('cuotas_pago', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Relación con la tabla de pagos principales
            $table->unsignedBigInteger('pago_id')->comment('ID del pago al que pertenece esta cuota');
            
            // Información específica de la cuota
            $table->integer('numero_cuota')->comment('Número secuencial de la cuota (1, 2, 3...)');
            $table->decimal('monto', 10, 2)->comment('Monto específico de esta cuota en pesos uruguayos');
            $table->date('fecha_vencimiento')->comment('Fecha límite para el pago de esta cuota');
            
            // Estado de la cuota
            $table->enum('estado', ['pendiente', 'pagada'])
                  ->default('pendiente')
                  ->comment('Estado actual de la cuota: pendiente o pagada');
            
            // Timestamps automáticos de Laravel
            $table->timestamps();

            // Definición de claves foráneas con restricciones
            $table->foreign('pago_id')
                  ->references('id')
                  ->on('pagos')
                  ->onDelete('cascade')
                  ->comment('FK: Elimina cuotas si se elimina el pago principal');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla cuotas_pago
     * 
     * Este método se ejecuta cuando se hace rollback de la migración,
     * eliminando completamente la tabla y todas sus restricciones.
     * 
     * @return void
     */
    public function down(): void
    {
        // Eliminar la tabla cuotas_pago y todas sus restricciones FK
        Schema::dropIfExists('cuotas_pago');
    }
};
