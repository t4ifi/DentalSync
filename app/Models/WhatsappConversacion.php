<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Clase WhatsappConversacion
 *
 * Representa una conversación de WhatsApp con un paciente dentro del sistema.
 * Permite manejar el estado de la conversación, mensajes no leídos, metadatos y relaciones con mensajes y paciente.
 *
 * @package App\Models
 */
class WhatsappConversacion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'whatsapp_conversaciones';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'paciente_id',             // ID del paciente asociado
        'telefono',                // Número de teléfono del contacto
        'nombre_contacto',         // Nombre del contacto
        'estado',                  // Estado de la conversación (activa, pausada, cerrada, bloqueada)
        'ultimo_mensaje_fecha',    // Fecha y hora del último mensaje
        'ultimo_mensaje_texto',    // Texto del último mensaje
        'ultimo_mensaje_propio',   // Indica si el último mensaje fue enviado por el sistema
        'mensajes_no_leidos',      // Conteo de mensajes no leídos
        'metadata'                 // Datos adicionales en formato JSON/array
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ultimo_mensaje_fecha' => 'datetime',
        'ultimo_mensaje_propio' => 'boolean',
        'mensajes_no_leidos' => 'integer',
        'metadata' => 'array'
    ];

    /**
     * Campos que se tratarán como fechas (Carbon instances).
     *
     * @var array<int, string>
     */
    protected $dates = [
        'ultimo_mensaje_fecha',
        'created_at',
        'updated_at'
    ];

    // ===================== RELACIONES =====================

    /**
     * Relación con el paciente asociado a la conversación.
     *
     * @return BelongsTo
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relación con los mensajes de la conversación.
     * Ordena los mensajes de más recientes a más antiguos.
     *
     * @return HasMany
     */
    public function mensajes(): HasMany
    {
        return $this->hasMany(WhatsappMensaje::class, 'conversacion_id')
                    ->orderBy('fecha_envio', 'desc');
    }

    /**
     * Relación con los mensajes recientes de la conversación (limitado a 50).
     *
     * @return HasMany
     */
    public function mensajesRecientes(): HasMany
    {
        return $this->hasMany(WhatsappMensaje::class, 'conversacion_id')
                    ->orderBy('fecha_envio', 'desc')
                    ->limit(50);
    }

    // ===================== SCOPES =====================

    /**
     * Scope para obtener conversaciones activas.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    /**
     * Scope para obtener conversaciones con mensajes no leídos.
     *
     * @param $query
     * @return mixed
     */
    public function scopeConMensajesNoLeidos($query)
    {
        return $query->where('mensajes_no_leidos', '>', 0);
    }

    /**
     * Scope para ordenar conversaciones por actividad (último mensaje).
     *
     * @param $query
     * @return mixed
     */
    public function scopeOrdenadaPorActividad($query)
    {
        return $query->orderBy('ultimo_mensaje_fecha', 'desc');
    }

    // ===================== MÉTODOS AUXILIARES =====================

    /**
     * Marca todos los mensajes de la conversación como leídos.
     *
     * @return void
     */
    public function marcarComoLeida(): void
    {
        $this->update(['mensajes_no_leidos' => 0]);
    }

    /**
     * Actualiza los datos del último mensaje en la conversación.
     * Incrementa el contador de mensajes no leídos si el mensaje no fue propio.
     *
     * @param WhatsappMensaje $mensaje
     * @return void
     */
    public function actualizarUltimoMensaje(WhatsappMensaje $mensaje): void
    {
        $this->update([
            'ultimo_mensaje_fecha' => $mensaje->fecha_envio,
            'ultimo_mensaje_texto' => $mensaje->contenido,
            'ultimo_mensaje_propio' => $mensaje->es_propio,
        ]);

        if (!$mensaje->es_propio) {
            $this->increment('mensajes_no_leidos');
        }
    }

    /**
     * Devuelve un color representativo según el estado de la conversación.
     *
     * @return string
     */
    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'activa' => 'green',
            'pausada' => 'yellow',
            'cerrada' => 'gray',
            'bloqueada' => 'red',
            default => 'gray'
        };
    }
}
