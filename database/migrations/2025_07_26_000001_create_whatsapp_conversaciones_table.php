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
        Schema::create('whatsapp_conversaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->string('telefono')->index();
            $table->string('nombre_contacto');
            $table->enum('estado', ['activa', 'pausada', 'cerrada', 'bloqueada'])->default('activa');
            $table->timestamp('ultimo_mensaje_fecha')->nullable();
            $table->text('ultimo_mensaje_texto')->nullable();
            $table->boolean('ultimo_mensaje_propio')->default(false);
            $table->integer('mensajes_no_leidos')->default(0);
            $table->json('metadata')->nullable(); // Para datos adicionales como avatar, etc.
            $table->timestamps();

            $table->foreign('paciente_id')->references('id')->on('pacientes')->onDelete('cascade');
            $table->unique(['paciente_id', 'telefono']);
            $table->index(['estado', 'ultimo_mensaje_fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversaciones');
    }
};
