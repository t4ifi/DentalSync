<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappConversacion extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_conversaciones';

    protected $fillable = [
        'paciente_id',
        'telefono',
        'nombre_contacto',
        'estado',
        'ultimo_mensaje_fecha',
        'ultimo_mensaje_texto',
        'ultimo_mensaje_propio',
        'mensajes_no_leidos',
        'metadata'
    ];

    protected $casts = [
        'ultimo_mensaje_fecha' => 'datetime',
        'ultimo_mensaje_propio' => 'boolean',
        'mensajes_no_leidos' => 'integer',
        'metadata' => 'array'
    ];

    protected $dates = [
        'ultimo_mensaje_fecha',
        'created_at',
        'updated_at'
    ];

    // Relaciones
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(WhatsappMensaje::class, 'conversacion_id')
                    ->orderBy('fecha_envio', 'desc');
    }

    public function mensajesRecientes(): HasMany
    {
        return $this->hasMany(WhatsappMensaje::class, 'conversacion_id')
                    ->orderBy('fecha_envio', 'desc')
                    ->limit(50);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopeConMensajesNoLeidos($query)
    {
        return $query->where('mensajes_no_leidos', '>', 0);
    }

    public function scopeOrdenadaPorActividad($query)
    {
        return $query->orderBy('ultimo_mensaje_fecha', 'desc');
    }

    // Métodos auxiliares
    public function marcarComoLeida(): void
    {
        $this->update(['mensajes_no_leidos' => 0]);
    }

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
