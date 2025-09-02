<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Creación de tabla pagos
 * 
 * Esta migración crea la tabla principal que gestiona los pagos realizados
 * por los pacientes en el consultorio dental. Registra información financiera
 * básica y mantiene el control contable del negocio.
 * 
 * Funcionalidades principales:
 * - Registro de pagos de pacientes
 * - Control financiero y contable
 * - Seguimiento de fechas de pago
 * - Descripción de servicios pagados
 * - Vinculación con pacientes y usuarios registradores
 * 
 * @author Andrés Núñez - NullDevs
 * @created 2025-07-22
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Ejecutar las migraciones para crear la tabla pagos
     * 
     * Esta tabla centraliza la información financiera de los pagos
     * realizados por los pacientes del consultorio dental.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            // Clave primaria auto-incremental
            $table->id();
            
            // Información del pago
            $table->date('fecha_pago')
                  ->comment('Fecha en que se realizó el pago');
            $table->decimal('monto_total', 10, 2)
                  ->comment('Monto total del pago en moneda local (precisión: 2 decimales)');
            $table->text('descripcion')
                  ->comment('Descripción del servicio o tratamiento pagado');
            
            // Relaciones con otras tablas
            $table->unsignedBigInteger('paciente_id')
                  ->comment('ID del paciente que realizó el pago');
            $table->unsignedBigInteger('usuario_id')
                  ->comment('ID del usuario que registró el pago');
            
            // Campos de auditoría
            $table->timestamps();

            // Definición de claves foráneas
            $table->foreign('paciente_id')
                  ->references('id')
                  ->on('pacientes')
                  ->onDelete('cascade')
                  ->comment('Relación con tabla pacientes');
            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('usuarios')
                  ->onDelete('cascade')
                  ->comment('Relación con tabla usuarios (registrador del pago)');
        });
    }

    /**
     * Revertir las migraciones eliminando la tabla pagos
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
