<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Agregar campos de seguridad a tabla usuarios
 * 
 * Esta migración mejora la seguridad del sistema agregando campos para
 * el control de acceso, seguimiento de sesiones y prevención de ataques
 * de fuerza bruta en el sistema de autenticación.
 * 
 * Funcionalidades de seguridad agregadas:
 * - Registro de último acceso al sistema
 * - Seguimiento de IP del último acceso
 * - Control de intentos fallidos de login
 * - Sistema de bloqueo temporal por seguridad
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-28
 * @version 1.1
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para agregar campos de seguridad
     * 
     * Mejora el sistema de autenticación con funciones de seguridad
     * avanzadas para proteger el acceso al consultorio dental.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Campo para registrar último acceso exitoso
            $table->timestamp('ultimo_acceso')
                  ->nullable()
                  ->after('activo')
                  ->comment('Fecha y hora del último acceso exitoso al sistema');
            
            // Campo para registrar IP del último acceso
            $table->string('ip_ultimo_acceso', 45)
                  ->nullable()
                  ->after('ultimo_acceso')
                  ->comment('Dirección IP del último acceso (soporta IPv4 e IPv6)');
            
            // Campo para contar intentos fallidos de login
            $table->integer('intentos_fallidos')
                  ->default(0)
                  ->after('ip_ultimo_acceso')
                  ->comment('Número de intentos fallidos consecutivos de autenticación');
            
            // Campo para bloqueo temporal por seguridad
            $table->timestamp('bloqueado_hasta')
                  ->nullable()
                  ->after('intentos_fallidos')
                  ->comment('Fecha hasta la cual el usuario está bloqueado por seguridad');
        });
    }

    /**
     * Revertir las migraciones eliminando los campos de seguridad
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Eliminar todos los campos de seguridad agregados
            $table->dropColumn([
                'ultimo_acceso',
                'ip_ultimo_acceso', 
                'intentos_fallidos',
                'bloqueado_hasta'
            ]);
        });
    }
};
