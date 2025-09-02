<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Actualización de tabla pagos para sistema de pagos avanzado
 * 
 * Esta migración mejora la tabla pagos añadiendo funcionalidades avanzadas
 * para el manejo de diferentes modalidades de pago, control de saldos y
 * seguimiento detallado del estado financiero.
 * 
 * Modalidades de pago agregadas:
 * - pago_unico: Pago completo del tratamiento de una vez
 * - cuotas_fijas: Pagos divididos en cuotas iguales
 * - cuotas_variables: Pagos divididos en cuotas de diferentes montos
 * 
 * Estados de pago:
 * - pendiente: Sin pagos realizados
 * - pagado_parcial: Pagos parciales realizados
 * - pagado_completo: Tratamiento completamente pagado
 * - vencido: Pagos con fechas vencidas
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 2.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para actualizar la tabla pagos
     * 
     * Agrega campos avanzados para el control financiero detallado
     * y diferentes modalidades de pago en el consultorio.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Agregar nuevos campos para el sistema de pagos avanzado
            $table->enum('modalidad_pago', ['pago_unico', 'cuotas_fijas', 'cuotas_variables'])
                  ->default('pago_unico')
                  ->after('descripcion')
                  ->comment('Modalidad de pago seleccionada para el tratamiento');
            
            $table->decimal('monto_pagado', 10, 2)
                  ->default(0)
                  ->after('monto_total')
                  ->comment('Monto total pagado hasta el momento');
            
            $table->decimal('saldo_restante', 10, 2)
                  ->default(0)
                  ->after('monto_pagado')
                  ->comment('Saldo pendiente por pagar');
            
            $table->integer('total_cuotas')
                  ->nullable()
                  ->after('saldo_restante')
                  ->comment('Número total de cuotas cuando aplica modalidad de cuotas');
            
            $table->enum('estado_pago', ['pendiente', 'pagado_parcial', 'pagado_completo', 'vencido'])
                  ->default('pendiente')
                  ->after('total_cuotas')
                  ->comment('Estado actual del pago del tratamiento');
            
            $table->text('observaciones')
                  ->nullable()
                  ->after('estado_pago')
                  ->comment('Observaciones adicionales sobre el pago');
            
            // Índices para optimizar consultas frecuentes
            $table->index('modalidad_pago', 'idx_pagos_modalidad');
            $table->index('estado_pago', 'idx_pagos_estado');
        });
    }

    /**
     * Revertir las migraciones eliminando los campos agregados
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Eliminar índices primero
            $table->dropIndex('idx_pagos_modalidad');
            $table->dropIndex('idx_pagos_estado');
            
            // Eliminar columnas agregadas
            $table->dropColumn([
                'modalidad_pago',
                'monto_pagado', 
                'saldo_restante',
                'total_cuotas',
                'estado_pago',
                'observaciones'
            ]);
        });
    }
};
