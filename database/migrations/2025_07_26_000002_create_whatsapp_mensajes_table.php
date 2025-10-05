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
        Schema::create('whatsapp_mensajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversacion_id');
            $table->string('mensaje_whatsapp_id')->nullable()->index(); // ID del mensaje en WhatsApp
            $table->text('contenido');
            $table->boolean('es_propio')->default(true); // true = enviado por nosotros, false = recibido
            $table->enum('estado', ['enviando', 'enviado', 'entregado', 'leido', 'error'])->default('enviando');
            $table->enum('tipo', ['texto', 'imagen', 'documento', 'audio', 'video'])->default('texto');
            $table->json('metadata')->nullable(); // Para archivos adjuntos, coordenadas, etc.
            $table->timestamp('fecha_envio');
            $table->timestamp('fecha_entregado')->nullable();
            $table->timestamp('fecha_leido')->nullable();
            $table->text('error_mensaje')->nullable();
            $table->timestamps();

            $table->foreign('conversacion_id')->references('id')->on('whatsapp_conversaciones')->onDelete('cascade');
            $table->index(['conversacion_id', 'fecha_envio']);
            $table->index(['es_propio', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_mensajes');
    }
};
