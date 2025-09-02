<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tablas de cache del sistema
 * 
 * Esta migración crea las tablas necesarias para el sistema de cache de Laravel
 * utilizado en DentalSync. El cache mejora el rendimiento del sistema almacenando
 * temporalmente datos frecuentemente accedidos en la base de datos.
 * 
 * Tablas creadas:
 * - cache: Almacena los datos en cache con claves únicas
 * - cache_locks: Gestiona bloqueos para prevenir condiciones de carrera
 * 
 * Funcionalidades:
 * - Mejora del rendimiento de consultas frecuentes
 * - Almacenamiento temporal de sesiones de usuario
 * - Cache de configuraciones del sistema
 * - Prevención de condiciones de carrera con bloqueos
 * 
 * @author Laravel Framework / Adaptado por Andrés Núñez - NullDevs
 * @created 2001-01-01 (Migración base de Laravel)
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear las tablas de cache
     * 
     * Crea las tablas necesarias para el funcionamiento del sistema
     * de cache de Laravel en el proyecto DentalSync.
     * 
     * @return void
     */
    public function up(): void
    {
        // Tabla principal de cache
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')
                  ->primary()
                  ->comment('Clave única identificadora del elemento en cache');
            $table->mediumText('value')
                  ->comment('Valor serializado almacenado en cache');
            $table->integer('expiration')
                  ->comment('Timestamp de expiración del elemento en cache');
        });

        // Tabla de bloqueos de cache para concurrencia
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')
                  ->primary()
                  ->comment('Clave del bloqueo para prevenir condiciones de carrera');
            $table->string('owner')
                  ->comment('Identificador del propietario del bloqueo');
            $table->integer('expiration')
                  ->comment('Timestamp de expiración del bloqueo');
        });
    }

    /**
     * Revertir las migraciones eliminando las tablas de cache
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};