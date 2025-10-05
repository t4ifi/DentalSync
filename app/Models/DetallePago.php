<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePago extends Model
{
    use HasFactory;

    protected $table = 'detalle_pagos';

    protected $fillable = [
        'pago_id',
        'fecha_pago',
        'monto_parcial',
        'descripcion',
        'tipo_pago',
        'numero_cuota',
        'usuario_id'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto_parcial' => 'decimal:2'
    ];

    // Relaciones
    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
