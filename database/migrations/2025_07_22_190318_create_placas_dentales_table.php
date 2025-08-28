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
        Schema::create('placas_dentales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('lugar', 255);
            $table->string('tipo', 100);
            $table->string('archivo_url', 500)->nullable(); // URL del archivo de la placa
            $table->unsignedBigInteger('paciente_id');
            $table->timestamps();

            $table->foreign('paciente_id')->references('id')->on('pacientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placas_dentales');
    }
};
