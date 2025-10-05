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

    /**
     * Relación con el pago principal
     */
    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    /**
     * Scope para cuotas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para cuotas pagadas
     */
    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    /**
     * Scope para cuotas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('estado', 'pendiente')
                    ->where('fecha_vencimiento', '<', now());
    }

    /**
     * Marcar cuota como pagada
     */
    public function marcarComoPagada()
    {
        $this->update(['estado' => 'pagada']);
    }

    /**
     * Verificar si la cuota está vencida
     */
    public function estaVencida()
    {
        return $this->estado === 'pendiente' && $this->fecha_vencimiento < now();
    }
}
