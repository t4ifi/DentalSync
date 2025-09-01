<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase CuotaPago
 *
 * Representa una cuota asociada a un pago dentro del sistema de gestión odontológica.
 * Un pago puede dividirse en varias cuotas, cada una con su propio número, monto, fecha de vencimiento y estado.
 *
 * @package App\Models
 */
class CuotaPago extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'cuotas_pago';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pago_id',           // ID del pago al que pertenece esta cuota
        'numero_cuota',      // Número de la cuota (ejemplo: 1, 2, 3…)
        'monto',             // Monto correspondiente a esta cuota
        'fecha_vencimiento', // Fecha límite para el pago de la cuota
        'estado'             // Estado actual (ej: pendiente, pagada, vencida)
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_vencimiento' => 'date',        // Convierte a instancia de Carbon (solo fecha, sin hora)
        'monto' => 'decimal:2'                // Maneja el monto con 2 decimales
    ];

    /**
     * Relación con el modelo Pago.
     * Una cuota pertenece a un único pago.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }
}
