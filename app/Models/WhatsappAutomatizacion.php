<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clase WhatsappAutomatizacion
 *
 * Representa una automatización de mensajes de WhatsApp dentro del sistema.
 * Permite configurar mensajes automáticos según condiciones específicas, tipo de audiencia y estado.
 *
 * @package App\Models
 */
class WhatsappAutomatizacion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'whatsapp_automatizaciones';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',               // Nombre descriptivo de la automatización
        'descripcion',          // Descripción detallada de la automatización
        'tipo',                 // Tipo de mensaje (recordatorio, bienvenida, seguimiento, etc.)
        'condicion',            // Condición para ejecutar el mensaje (almacenada como array)
        'audiencia',            // Público objetivo de la automatización
        'mensaje',              // Contenido del mensaje a enviar
        'estado',               // Estado de la automatización (activa, pausada, inactiva)
        'limite_envios',        // Indica si hay límite de envíos (boolean)
        'max_envios_paciente',  // Máximo de envíos por paciente
        'ejecutada',            // Cantidad de veces ejecutada
        'exitosas',             // Cantidad de envíos exitosos
        'fallidas',             // Cantidad de envíos fallidos
        'ultimo_ejecutado',     // Fecha y hora de la última ejecución
        'creado_por'            // Usuario que creó la automatización
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'condicion' => 'array',             // Convierte a array para condiciones complejas
        'limite_envios' => 'boolean',       // Valor booleano
        'max_envios_paciente' => 'integer', // Número máximo de envíos
        'ejecutada' => 'integer',           // Conteo de ejecuciones
        'exitosas' => 'integer',            // Conteo de ejecuciones exitosas
        'fallidas' => 'integer',            // Conteo de ejecuciones fallidas
        'ultimo_ejecutado' => 'datetime'    // Fecha y hora de la última ejecución
    ];

    // ===================== RELACIONES =====================

    /**
     * Relación con el usuario que creó la automatización.
     *
     * @return BelongsTo
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    // ===================== SCOPES =====================

    /**
     * Scope para obtener automatizaciones activas.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    /**
     * Scope para filtrar por tipo de automatización.
     *
     * @param $query
     * @param string $tipo
     * @return mixed
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para obtener automatizaciones listas para ejecutar.
     *
     * @param $query
     * @return mixed
     */
    public function scopeParaEjecutar($query)
    {
        return $query->where('estado', 'activa');
    }

    // ===================== MÉTODOS AUXILIARES =====================

    /**
     * Activa la automatización.
     *
     * @return void
     */
    public function activar(): void
    {
        $this->update(['estado' => 'activa']);
    }

    /**
     * Pausa la automatización.
     *
     * @return void
     */
    public function pausar(): void
    {
        $this->update(['estado' => 'pausada']);
    }

    /**
     * Desactiva la automatización.
     *
     * @return void
     */
    public function desactivar(): void
    {
        $this->update(['estado' => 'inactiva']);
    }

    /**
     * Registra la ejecución de la automatización.
     *
     * @param bool $exitosa
     * @param string|null $error
     * @return void
     */
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

    /**
     * Calcula la tasa de éxito de la automatización en porcentaje.
     *
     * @return float
     */
    public function getTasaExitoAttribute(): float
    {
        if ($this->ejecutada === 0) {
            return 0;
        }

        return round(($this->exitosas / $this->ejecutada) * 100, 2);
    }

    /**
     * Devuelve la condición en texto legible.
     *
     * @return string
     */
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

    /**
     * Devuelve un color representativo según el tipo de automatización.
     *
     * @return string
     */
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

    /**
     * Devuelve un texto representativo según el tipo de automatización.
     *
     * @return string
     */
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

    /**
     * Devuelve un color representativo según el estado de la automatización.
     *
     * @return string
     */
    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'activa' => 'green',
            'pausada' => 'yellow',
            'inactiva' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Reemplaza variables en el mensaje por los valores proporcionados.
     *
     * @param array $valores
     * @return string
     */
    public function reemplazarVariables(array $valores): string
    {
        $mensaje = $this->mensaje;

        foreach ($valores as $variable => $valor) {
            $mensaje = str_replace($variable, $valor, $mensaje);
        }

        return $mensaje;
    }
}
