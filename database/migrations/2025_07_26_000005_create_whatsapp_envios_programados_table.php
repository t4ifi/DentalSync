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
        Schema::create('whatsapp_envios_programados', function (Blueprint $table) {
            $table->id();
            $table->string('telefono');
            $table->text('mensaje');
            $table->timestamp('fecha_programada');
            $table->enum('estado', ['pendiente', 'enviado', 'error', 'cancelado'])->default('pendiente');
            $table->enum('tipo_envio', ['individual', 'masivo'])->default('individual');
            $table->unsignedBigInteger('automatizacion_id')->nullable();
            $table->json('destinatarios')->nullable(); // Para envíos masivos
            $table->text('error_mensaje')->nullable();
            $table->timestamp('fecha_envio')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();

            $table->foreign('automatizacion_id')->references('id')->on('whatsapp_automatizaciones')->onDelete('set null');
            $table->foreign('creado_por')->references('id')->on('usuarios')->onDelete('set null');
            $table->index(['estado', 'fecha_programada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_envios_programados');
    }
};
