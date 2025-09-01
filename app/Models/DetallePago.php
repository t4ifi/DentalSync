<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase DetallePago
 *
 * Representa un registro individual de pago dentro de un pago general.
 * Permite detallar pagos parciales, el tipo de pago, quién lo registró y en qué fecha se realizó.
 *
 * @package App\Models
 */
class DetallePago extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'detalle_pagos';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pago_id',        // ID del pago al que pertenece este detalle
        'fecha_pago',     // Fecha en que se realizó el pago parcial
        'monto_parcial',  // Monto pagado en esta transacción
        'descripcion',    // Descripción del pago (ej: abono, ajuste, etc.)
        'tipo_pago',      // Tipo de pago (ej: efectivo, tarjeta, transferencia)
        'numero_cuota',   // Número de la cuota a la que corresponde (si aplica)
        'usuario_id'      // Usuario que registró el pago
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_pago' => 'date',           // Convierte a instancia de Carbon (solo fecha)
        'monto_parcial' => 'decimal:2'    // Maneja el monto con 2 decimales
    ];

    /**
     * Relación con el modelo Pago.
     * Cada detalle pertenece a un pago general.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    /**
     * Relación con el modelo Usuario.
     * Indica qué usuario registró este detalle de pago.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
