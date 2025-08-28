<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detalle_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->onDelete('cascade');
            $table->date('fecha_pago');
            $table->decimal('monto_parcial', 10, 2);
            $table->text('descripcion')->nullable();
            $table->enum('tipo_pago', ['cuota_fija', 'pago_variable', 'pago_completo'])->default('pago_completo');
            $table->integer('numero_cuota')->nullable(); // Para cuotas fijas
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade'); // Quien registró
            $table->timestamps();
            
            // Índices
            $table->index(['pago_id', 'fecha_pago']);
            $table->index(['tipo_pago']);
            $table->index(['numero_cuota']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_pagos');
    }
};
