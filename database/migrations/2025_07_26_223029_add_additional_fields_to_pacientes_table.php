<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Agregar campos adicionales a tabla pacientes
 * 
 * Esta migración amplía la tabla pacientes con información más detallada
 * necesaria para una gestión completa del consultorio dental, incluyendo
 * datos de contacto, dirección, contactos de emergencia e información médica.
 * 
 * Campos agregados:
 * - Información de contacto: email
 * - Dirección completa: dirección, ciudad, departamento
 * - Contacto de emergencia: nombre, teléfono, relación
 * - Información médica: motivo consulta, alergias, observaciones
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-26
 * @version 1.1
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para agregar campos adicionales a pacientes
     * 
     * Expande la información del paciente para un manejo más completo
     * del historial y datos de contacto en el consultorio dental.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Información de contacto adicional
            $table->string('email', 100)
                  ->nullable()
                  ->after('telefono')
                  ->comment('Correo electrónico del paciente para comunicaciones');
            
            // Información de dirección completa
            $table->string('direccion', 500)
                  ->nullable()
                  ->after('email')
                  ->comment('Dirección completa de residencia del paciente');
            $table->string('ciudad', 100)
                  ->nullable()
                  ->after('direccion')
                  ->comment('Ciudad de residencia del paciente');
            $table->string('departamento', 100)
                  ->nullable()
                  ->after('ciudad')
                  ->comment('Departamento o estado de residencia');
            
            // Contacto de emergencia
            $table->string('contacto_emergencia_nombre', 255)
                  ->nullable()
                  ->after('departamento')
                  ->comment('Nombre completo del contacto de emergencia');
            $table->string('contacto_emergencia_telefono', 20)
                  ->nullable()
                  ->after('contacto_emergencia_nombre')
                  ->comment('Teléfono del contacto de emergencia');
            $table->string('contacto_emergencia_relacion', 50)
                  ->nullable()
                  ->after('contacto_emergencia_telefono')
                  ->comment('Relación del contacto de emergencia con el paciente');
            
            // Información médica adicional
            $table->text('motivo_consulta')
                  ->nullable()
                  ->after('contacto_emergencia_relacion')
                  ->comment('Motivo principal de la consulta dental');
            $table->text('alergias')
                  ->nullable()
                  ->after('motivo_consulta')
                  ->comment('Alergias conocidas del paciente');
            $table->text('observaciones')
                  ->nullable()
                  ->after('alergias')
                  ->comment('Observaciones adicionales sobre el paciente');
        });
    }

    /**
     * Revertir las migraciones eliminando los campos adicionales
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            // Eliminar todos los campos agregados
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
