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
        Schema::table('usuarios', function (Blueprint $table) {
            $table->timestamp('ultimo_acceso')->nullable()->after('activo');
            $table->string('ip_ultimo_acceso', 45)->nullable()->after('ultimo_acceso');
            $table->integer('intentos_fallidos')->default(0)->after('ip_ultimo_acceso');
            $table->timestamp('bloqueado_hasta')->nullable()->after('intentos_fallidos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'ultimo_acceso',
                'ip_ultimo_acceso', 
                'intentos_fallidos',
                'bloqueado_hasta'
            ]);
        });
    }
};
