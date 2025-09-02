<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Remover campos no utilizados de tabla pacientes
 * 
 * Esta migración limpia la tabla pacientes eliminando campos que fueron
 * agregados previamente pero que no se utilizarán en la versión final
 * del sistema. Mantiene solo la información esencial del paciente.
 * 
 * Campos eliminados:
 * - email: Se determinó innecesario para el flujo del consultorio
 * - direccion, ciudad, departamento: Información de ubicación no requerida
 * - contacto_emergencia_*: Datos de emergencia simplificados
 * 
 * Se mantienen: motivo_consulta, alergias, observaciones (información médica)
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 1.2
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para limpiar campos innecesarios
     * 
     * Elimina campos de contacto y ubicación que no son utilizados
     * en el flujo operativo del consultorio dental.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Eliminar campos innecesarios que no se usarán en producción
            $table->dropColumn([
                'email',                        // Comunicación no requerida por email
                'direccion',                    // Dirección física innecesaria
                'ciudad',                       // Información de ubicación simplificada
                'departamento',                 // No requerido para el flujo actual
                'contacto_emergencia_nombre',   // Contacto de emergencia simplificado
                'contacto_emergencia_telefono', // No necesario para el consultorio
                'contacto_emergencia_relacion'  // Relación del contacto innecesaria
            ]);
        });
    }

    /**
     * Revertir las migraciones restaurando los campos eliminados
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Restaurar campos en caso de rollback (solo para desarrollo)
            $table->string('email', 100)
                  ->nullable()
                  ->after('telefono')
                  ->comment('Correo electrónico del paciente');
            $table->string('direccion', 500)
                  ->nullable()
                  ->after('email')
                  ->comment('Dirección completa de residencia');
            $table->string('ciudad', 100)
                  ->nullable()
                  ->after('direccion')
                  ->comment('Ciudad de residencia');
            $table->string('departamento', 100)
                  ->nullable()
                  ->after('ciudad')
                  ->comment('Departamento o estado');
            $table->string('contacto_emergencia_nombre', 255)
                  ->nullable()
                  ->after('departamento')
                  ->comment('Nombre del contacto de emergencia');
            $table->string('contacto_emergencia_telefono', 20)
                  ->nullable()
                  ->after('contacto_emergencia_nombre')
                  ->comment('Teléfono del contacto de emergencia');
            $table->string('contacto_emergencia_relacion', 50)
                  ->nullable()
                  ->after('contacto_emergencia_telefono')
                  ->comment('Relación con el paciente');
        });
    }
};
