<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Agregar columnas adicionales a tabla pagos (validación)
 * 
 * Esta migración es una verificación de seguridad que agrega campos al
 * sistema de pagos solo si no existen previamente. Evita errores de
 * duplicación de columnas en migraciones anteriores.
 * 
 * Funcionalidad:
 * - Verificación de existencia de columnas antes de agregar
 * - Soporte para modalidades de pago avanzadas
 * - Control de saldos y estados de pago
 * - Manejo de cuotas y observaciones
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-28
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones con verificación de columnas existentes
     * 
     * Agrega campos al sistema de pagos solo si no existen previamente,
     * evitando conflictos con migraciones anteriores.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Verificar y agregar monto pagado si no existe
            if (!Schema::hasColumn('pagos', 'monto_pagado')) {
                $table->decimal('monto_pagado', 10, 2)
                      ->default(0)
                      ->after('monto_total')
                      ->comment('Monto total pagado hasta el momento');
            }
            
            // Verificar y agregar saldo restante si no existe
            if (!Schema::hasColumn('pagos', 'saldo_restante')) {
                $table->decimal('saldo_restante', 10, 2)
                      ->default(0)
                      ->after('monto_pagado')
                      ->comment('Saldo pendiente por pagar');
            }
            
            // Verificar y agregar modalidad de pago si no existe
            if (!Schema::hasColumn('pagos', 'modalidad_pago')) {
                $table->enum('modalidad_pago', ['pago_unico', 'cuotas_fijas', 'cuotas_variables'])
                      ->default('pago_unico')
                      ->after('descripcion')
                      ->comment('Modalidad de pago seleccionada');
            }
            
            // Verificar y agregar total de cuotas si no existe
            if (!Schema::hasColumn('pagos', 'total_cuotas')) {
                $table->integer('total_cuotas')
                      ->nullable()
                      ->after('modalidad_pago')
                      ->comment('Número total de cuotas planificadas');
            }
            
            // Verificar y agregar estado de pago si no existe
            if (!Schema::hasColumn('pagos', 'estado_pago')) {
                $table->enum('estado_pago', ['pendiente', 'pagado_parcial', 'pagado_completo'])
                      ->default('pendiente')
                      ->after('total_cuotas')
                      ->comment('Estado actual del pago');
            }
            
            // Verificar y agregar observaciones si no existe
            if (!Schema::hasColumn('pagos', 'observaciones')) {
                $table->text('observaciones')
                      ->nullable()
                      ->after('estado_pago')
                      ->comment('Observaciones adicionales del pago');
            }
        });
    }

    /**
     * Revertir las migraciones eliminando las columnas agregadas
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Eliminar columnas agregadas (solo si existen)
            if (Schema::hasColumn('pagos', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
            if (Schema::hasColumn('pagos', 'estado_pago')) {
                $table->dropColumn('estado_pago');
            }
            if (Schema::hasColumn('pagos', 'total_cuotas')) {
                $table->dropColumn('total_cuotas');
            }
            if (Schema::hasColumn('pagos', 'modalidad_pago')) {
                $table->dropColumn('modalidad_pago');
            }
            if (Schema::hasColumn('pagos', 'saldo_restante')) {
                $table->dropColumn('saldo_restante');
            }
            if (Schema::hasColumn('pagos', 'monto_pagado')) {
                $table->dropColumn('monto_pagado');
            }
        });
    }
};