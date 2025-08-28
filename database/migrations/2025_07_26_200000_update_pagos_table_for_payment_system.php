<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Agregar nuevos campos para el sistema de pagos
            $table->enum('modalidad_pago', ['pago_unico', 'cuotas_fijas', 'cuotas_variables'])->default('pago_unico')->after('descripcion');
            $table->decimal('monto_pagado', 10, 2)->default(0)->after('monto_total');
            $table->decimal('saldo_restante', 10, 2)->default(0)->after('monto_pagado');
            $table->integer('total_cuotas')->nullable()->after('saldo_restante');
            $table->enum('estado_pago', ['pendiente', 'pagado_parcial', 'pagado_completo', 'vencido'])->default('pendiente')->after('total_cuotas');
            $table->text('observaciones')->nullable()->after('estado_pago');
            
            // Índices para optimizar consultas
            $table->index('modalidad_pago');
            $table->index('estado_pago');
        });
    }

    public function down()
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['modalidad_pago']);
            $table->dropIndex(['estado_pago']);
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
