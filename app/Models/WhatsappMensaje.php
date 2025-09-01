<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMensaje extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_mensajes';

    protected $fillable = [
        'conversacion_id',
        'mensaje_whatsapp_id',
        'contenido',
        'es_propio',
        'estado',
        'tipo',
        'metadata',
        'fecha_envio',
        'fecha_entregado',
        'fecha_leido',
        'error_mensaje'
    ];

    protected $casts = [
        'es_propio' => 'boolean',
        'metadata' => 'array',
        'fecha_envio' => 'datetime',
        'fecha_entregado' => 'datetime',
        'fecha_leido' => 'datetime'
    ];

    protected $dates = [
        'fecha_envio',
        'fecha_entregado',
        'fecha_leido',
        'created_at',
        'updated_at'
    ];

    // Relaciones
    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversacion::class, 'conversacion_id');
    }

    // Scopes
    public function scopeEnviados($query)
    {
        return $query->where('es_propio', true);
    }

    public function scopeRecibidos($query)
    {
        return $query->where('es_propio', false);
    }

    public function scopeExitosos($query)
    {
        return $query->whereIn('estado', ['enviado', 'entregado', 'leido']);
    }

    public function scopeConError($query)
    {
        return $query->where('estado', 'error');
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_envio', today());
    }

    // Métodos auxiliares
    public function actualizarEstado(string $nuevoEstado, array $metadata = []): void
    {
        $updateData = ['estado' => $nuevoEstado];
        
        switch ($nuevoEstado) {
            case 'entregado':
                $updateData['fecha_entregado'] = now();
                break;
            case 'leido':
                $updateData['fecha_leido'] = now();
                break;
            case 'error':
                if (isset($metadata['error'])) {
                    $updateData['error_mensaje'] = $metadata['error'];
                }
                break;
        }

        if (!empty($metadata)) {
            $updateData['metadata'] = array_merge($this->metadata ?? [], $metadata);
        }

        $this->update($updateData);
    }

    public function getEstadoIconoAttribute(): string
    {
        return match($this->estado) {
            'enviando' => 'bx bx-time',
            'enviado' => 'bx bx-check',
            'entregado' => 'bx bx-check-double',
            'leido' => 'bx bx-check-double text-blue-500',
            'error' => 'bx bx-x text-red-500',
            default => 'bx bx-time'
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'enviando' => 'gray',
            'enviado' => 'gray',
            'entregado' => 'gray',
            'leido' => 'blue',
            'error' => 'red',
            default => 'gray'
        };
    }

    public function esReciente(): bool
    {
        return $this->fecha_envio->diffInMinutes(now()) <= 5;
    }
}
