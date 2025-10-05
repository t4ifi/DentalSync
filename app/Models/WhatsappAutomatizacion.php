<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappAutomatizacion extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_automatizaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'condicion',
        'audiencia',
        'mensaje',
        'estado',
        'limite_envios',
        'max_envios_paciente',
        'ejecutada',
        'exitosas',
        'fallidas',
        'ultimo_ejecutado',
        'creado_por'
    ];

    protected $casts = [
        'condicion' => 'array',
        'limite_envios' => 'boolean',
        'max_envios_paciente' => 'integer',
        'ejecutada' => 'integer',
        'exitosas' => 'integer',
        'fallidas' => 'integer',
        'ultimo_ejecutado' => 'datetime'
    ];

    // Relaciones
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeParaEjecutar($query)
    {
        return $query->where('estado', 'activa');
    }

    // Métodos auxiliares
    public function activar(): void
    {
        $this->update(['estado' => 'activa']);
    }

    public function pausar(): void
    {
        $this->update(['estado' => 'pausada']);
    }

    public function desactivar(): void
    {
        $this->update(['estado' => 'inactiva']);
    }

    public function registrarEjecucion(bool $exitosa = true, string $error = null): void
    {
        $this->increment('ejecutada');
        
        if ($exitosa) {
            $this->increment('exitosas');
        } else {
            $this->increment('fallidas');
        }
        
        $this->update(['ultimo_ejecutado' => now()]);
    }

    public function getTasaExitoAttribute(): float
    {
        if ($this->ejecutada === 0) {
            return 0;
        }
        
        return round(($this->exitosas / $this->ejecutada) * 100, 2);
    }

    public function getCondicionTextoAttribute(): string
    {
        $condicion = $this->condicion;
        
        return match($condicion['tipo']) {
            'antes_cita' => "{$condicion['valor']} {$condicion['unidad']} antes de la cita",
            'despues_cita' => "{$condicion['valor']} {$condicion['unidad']} después de la cita",
            'nuevo_paciente' => 'Al registrar nuevo paciente',
            'cumpleanos' => 'En el cumpleaños del paciente',
            'pago_vencido' => 'Cuando un pago está vencido',
            default => 'Condición personalizada'
        };
    }

    public function getTipoColorAttribute(): string
    {
        return match($this->tipo) {
            'recordatorio' => 'blue',
            'seguimiento' => 'purple',
            'bienvenida' => 'pink',
            'cumpleanos' => 'orange',
            'pago' => 'yellow',
            default => 'gray'
        };
    }

    public function getTipoTextoAttribute(): string
    {
        return match($this->tipo) {
            'recordatorio' => 'Recordatorio',
            'seguimiento' => 'Seguimiento',
            'bienvenida' => 'Bienvenida',
            'cumpleanos' => 'Cumpleaños',
            'pago' => 'Pago',
            default => 'General'
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'activa' => 'green',
            'pausada' => 'yellow',
            'inactiva' => 'gray',
            default => 'gray'
        };
    }

    public function reemplazarVariables(array $valores): string
    {
        $mensaje = $this->mensaje;
        
        foreach ($valores as $variable => $valor) {
            $mensaje = str_replace($variable, $valor, $mensaje);
        }
        
        return $mensaje;
    }
}
