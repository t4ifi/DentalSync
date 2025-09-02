<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla usuarios
 * 
 * Esta migración crea la tabla principal de usuarios del sistema DentalSync.
 * Los usuarios pueden ser dentistas o recepcionistas, cada uno con diferentes
 * niveles de acceso y responsabilidades en el sistema.
 * 
 * Roles disponibles:
 * - dentista: Acceso completo a tratamientos, historiales clínicos y pagos
 * - recepcionista: Acceso a citas, pacientes y gestión administrativa
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-22
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla usuarios
     * 
     * Esta tabla almacena la información de autenticación y perfil
     * de todos los usuarios del sistema médico.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Campos de autenticación
            $table->string('usuario', 100)
                  ->unique()
                  ->comment('Nombre de usuario único para login');
            $table->string('password_hash', 255)
                  ->comment('Contraseña encriptada con hash');
            
            // Información del perfil
            $table->string('nombre', 255)
                  ->comment('Nombre completo del usuario');
            $table->enum('rol', ['dentista', 'recepcionista'])
                  ->comment('Rol del usuario en el sistema médico');
            
            // Estado del usuario
            $table->boolean('activo')
                  ->default(true)
                  ->comment('Indica si el usuario puede acceder al sistema');
            
            // Timestamps automáticos de Laravel
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla usuarios
     * 
     * ATENCIÓN: Este rollback eliminará todos los usuarios del sistema
     * y puede afectar las relaciones con otras tablas.
     * 
     * @return void
     */
    public function down(): void
    {
        // Eliminar la tabla usuarios y todas sus restricciones
        Schema::dropIfExists('usuarios');
    }
};
