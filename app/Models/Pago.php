<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase Pago
 *
 * Representa un pago realizado por un paciente en el sistema de gestión odontológica.
 * Un pago puede estar compuesto por varias cuotas y detalles de pago, y mantiene información sobre
 * el monto total, pagado, saldo restante, modalidad, estado y observaciones.
 *
 * @package App\Models
 */
class Pago extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'pagos';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fecha_pago',       // Fecha en que se registró el pago
        'monto_total',      // Monto total del pago
        'monto_pagado',     // Monto ya pagado
        'saldo_restante',   // Saldo restante por pagar
        'descripcion',      // Descripción general del pago
        'modalidad_pago',   // Modalidad de pago (ej: efectivo, tarjeta, transferencia)
        'total_cuotas',     // Número total de cuotas asociadas
        'estado_pago',      // Estado actual del pago (pendiente, pagado_parcial, pagado_completo)
        'observaciones',    // Observaciones adicionales
        'paciente_id',      // Relación con el paciente que realizó el pago
        'usuario_id'        // Relación con el usuario que registró el pago
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_pago' => 'date',         // Convierte a instancia de Carbon (solo fecha)
        'monto_total' => 'decimal:2',   // Maneja el monto con 2 decimales
        'monto_pagado' => 'decimal:2',  // Maneja el monto con 2 decimales
        'saldo_restante' => 'decimal:2' // Maneja el saldo con 2 decimales
    ];

    /**
     * Relación con el modelo Paciente.
     * Cada pago pertenece a un paciente específico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con el modelo Usuario.
     * Indica qué usuario registró el pago.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Relación con los detalles de pago.
     * Un pago puede tener múltiples registros individuales de pago.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detallesPagos()
    {
        return $this->hasMany(DetallePago::class);
    }

    /**
     * Relación con las cuotas de pago.
     * Un pago puede estar dividido en varias cuotas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cuotasPago()
    {
        return $this->hasMany(CuotaPago::class);
    }

    /**
     * Calcula el saldo restante del pago.
     *
     * @return float
     */
    public function calcularSaldoRestante()
    {
        return $this->monto_total - $this->monto_pagado;
    }

    /**
     * Verifica si el pago ha sido completado en su totalidad.
     *
     * @return bool
     */
    public function estaPagadoCompleto()
    {
        return $this->monto_pagado >= $this->monto_total;
    }

    /**
     * Calcula el porcentaje del pago que ha sido abonado.
     *
     * @return float
     */
    public function porcentajePagado()
    {
        if ($this->monto_total == 0) return 0;
        return ($this->monto_pagado / $this->monto_total) * 100;
    }

    /**
     * Actualiza el estado del pago según el monto abonado.
     * - "pagado_completo" si el saldo es 0 o menor.
     * - "pagado_parcial" si se ha pagado parcialmente.
     * - "pendiente" si no se ha realizado ningún pago.
     *
     * También actualiza el saldo_restante en la base de datos.
     *
     * @return void
     */
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
