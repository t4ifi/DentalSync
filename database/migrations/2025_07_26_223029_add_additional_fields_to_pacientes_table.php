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
        Schema::table('pacientes', function (Blueprint $table) {
            // Información de contacto adicional
            $table->string('email', 100)->nullable()->after('telefono');
            
            // Dirección
            $table->string('direccion', 500)->nullable()->after('email');
            $table->string('ciudad', 100)->nullable()->after('direccion');
            $table->string('departamento', 100)->nullable()->after('ciudad');
            
            // Contacto de emergencia
            $table->string('contacto_emergencia_nombre', 255)->nullable()->after('departamento');
            $table->string('contacto_emergencia_telefono', 20)->nullable()->after('contacto_emergencia_nombre');
            $table->string('contacto_emergencia_relacion', 50)->nullable()->after('contacto_emergencia_telefono');
            
            // Información médica
            $table->text('motivo_consulta')->nullable()->after('contacto_emergencia_relacion');
            $table->text('alergias')->nullable()->after('motivo_consulta');
            $table->text('observaciones')->nullable()->after('alergias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'direccion',
                'ciudad',
                'departamento',
                'contacto_emergencia_nombre',
                'contacto_emergencia_telefono',
                'contacto_emergencia_relacion',
                'motivo_consulta',
                'alergias',
                'observaciones'
            ]);
        });
    }
};
