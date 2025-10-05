<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cuotas_pago', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pago_id');
            $table->integer('numero_cuota');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['pendiente', 'pagada'])->default('pendiente');
            $table->timestamps();

            $table->foreign('pago_id')->references('id')->on('pagos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas_pago');
    }
};
