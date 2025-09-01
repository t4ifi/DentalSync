<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'fecha_pago',
        'monto_total',
        'monto_pagado',
        'saldo_restante',
        'descripcion',
        'modalidad_pago',
        'total_cuotas',
        'estado_pago',
        'observaciones',
        'paciente_id',
        'usuario_id'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_restante' => 'decimal:2'
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function detallesPagos()
    {
        return $this->hasMany(DetallePago::class);
    }

    public function cuotasPago()
    {
        return $this->hasMany(CuotaPago::class);
    }

    // Métodos auxiliares
    public function calcularSaldoRestante()
    {
        return $this->monto_total - $this->monto_pagado;
    }

    public function estaPagadoCompleto()
    {
        return $this->monto_pagado >= $this->monto_total;
    }

    public function porcentajePagado()
    {
        if ($this->monto_total == 0) return 0;
        return ($this->monto_pagado / $this->monto_total) * 100;
    }

    public function actualizarEstado()
    {
        $saldo = $this->calcularSaldoRestante();
        
        if ($saldo <= 0) {
            $this->estado_pago = 'pagado_completo';
        } elseif ($this->monto_pagado > 0) {
            $this->estado_pago = 'pagado_parcial';
        } else {
            $this->estado_pago = 'pendiente';
        }
        
        $this->saldo_restante = max(0, $saldo);
        $this->save();
    }
}
