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
        Schema::create('whatsapp_automatizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['recordatorio', 'seguimiento', 'bienvenida', 'cumpleanos', 'pago'])->default('recordatorio');
            $table->json('condicion'); // {tipo: 'antes_cita', valor: 24, unidad: 'horas'}
            $table->enum('audiencia', ['todos', 'nuevos', 'recurrentes', 'activos'])->default('todos');
            $table->text('mensaje');
            $table->enum('estado', ['activa', 'inactiva', 'pausada'])->default('activa');
            $table->boolean('limite_envios')->default(false);
            $table->integer('max_envios_paciente')->default(1);
            $table->integer('ejecutada')->default(0);
            $table->integer('exitosas')->default(0);
            $table->integer('fallidas')->default(0);
            $table->timestamp('ultimo_ejecutado')->nullable();
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();

            $table->foreign('creado_por')->references('id')->on('usuarios')->onDelete('set null');
            $table->index(['estado', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_automatizaciones');
    }
};
