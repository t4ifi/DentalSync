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
            // Eliminar campos innecesarios que no se usarán
            $table->dropColumn([
                'email',
                'direccion',
                'ciudad',
                'departamento',
                'contacto_emergencia_nombre',
                'contacto_emergencia_telefono',
                'contacto_emergencia_relacion'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Restaurar campos en caso de rollback
            $table->string('email', 100)->nullable()->after('telefono');
            $table->string('direccion', 500)->nullable()->after('email');
            $table->string('ciudad', 100)->nullable()->after('direccion');
            $table->string('departamento', 100)->nullable()->after('ciudad');
            $table->string('contacto_emergencia_nombre', 255)->nullable()->after('departamento');
            $table->string('contacto_emergencia_telefono', 20)->nullable()->after('contacto_emergencia_nombre');
            $table->string('contacto_emergencia_relacion', 50)->nullable()->after('contacto_emergencia_telefono');
        });
    }
};
