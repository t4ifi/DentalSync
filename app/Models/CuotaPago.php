<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuotaPago extends Model
{
    use HasFactory;

    protected $table = 'cuotas_pago';

    protected $fillable = [
        'pago_id',
        'numero_cuota',
        'monto',
        'fecha_vencimiento',
        'estado'
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'monto' => 'decimal:2'
    ];

    // Relaciones
    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }
}
