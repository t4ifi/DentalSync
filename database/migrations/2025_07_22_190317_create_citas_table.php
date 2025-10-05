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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha');
            $table->text('motivo');
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'atendida'])->default('pendiente');
            $table->dateTime('fecha_atendida')->nullable();
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();

            $table->foreign('paciente_id')->references('id')->on('pacientes')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
