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
        Schema::create('whatsapp_plantillas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('categoria', ['recordatorio', 'confirmacion', 'pago', 'tratamiento', 'bienvenida', 'general'])->default('general');
            $table->text('contenido');
            $table->boolean('activa')->default(true);
            $table->integer('usos')->default(0);
            $table->json('variables_detectadas')->nullable(); // Array de variables como {nombre}, {fecha}, etc.
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();

            $table->foreign('creado_por')->references('id')->on('usuarios')->onDelete('set null');
            $table->index(['categoria', 'activa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_plantillas');
    }
};
