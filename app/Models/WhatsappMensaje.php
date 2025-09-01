<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clase WhatsappMensaje
 *
 * Representa un mensaje individual dentro de una conversación de WhatsApp.
 * Permite manejar contenido, estado, fechas de envío/entrega/lectura y metadatos adicionales.
 *
 * @package App\Models
 */
class WhatsappMensaje extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'whatsapp_mensajes';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversacion_id',     // ID de la conversación a la que pertenece
        'mensaje_whatsapp_id', // ID del mensaje en WhatsApp (externo)
        'contenido',           // Contenido del mensaje
        'es_propio',           // Si el mensaje fue enviado por el sistema
        'estado',              // Estado del mensaje (enviando, enviado, entregado, leido, error)
        'tipo',                // Tipo de mensaje (texto, multimedia, etc.)
        'metadata',            // Datos adicionales en formato JSON/array
        'fecha_envio',         // Fecha y hora de envío
        'fecha_entregado',     // Fecha y hora de entrega
        'fecha_leido',         // Fecha y hora de lectura
        'error_mensaje'        // Mensaje de error en caso de fallo
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'es_propio' => 'boolean',           // Indica si el mensaje fue propio
        'metadata' => 'array',              // Metadatos como array
        'fecha_envio' => 'datetime',        // Conversión a Carbon
        'fecha_entregado' => 'datetime',
        'fecha_leido' => 'datetime'
    ];

    /**
     * Campos que se tratarán como fechas (Carbon instances).
     *
     * @var array<int, string>
     */
    protected $dates = [
        'fecha_envio',
        'fecha_entregado',
        'fecha_leido',
        'created_at',
        'updated_at'
    ];

    // ===================== RELACIONES =====================

    /**
     * Relación con la conversación a la que pertenece el mensaje.
     *
     * @return BelongsTo
     */
    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversacion::class, 'conversacion_id');
    }

    // ===================== SCOPES =====================

    /**
     * Scope para obtener solo los mensajes enviados por el sistema.
     *
     * @param $query
     * @return mixed
     */
    public function scopeEnviados($query)
    {
        return $query->where('es_propio', true);
    }

    /**
     * Scope para obtener solo los mensajes recibidos del paciente.
     *
     * @param $query
     * @return mixed
     */
    public function scopeRecibidos($query)
    {
        return $query->where('es_propio', false);
    }

    /**
     * Scope para obtener mensajes exitosos (enviado, entregado, leido).
     *
     * @param $query
     * @return mixed
     */
    public function scopeExitosos($query)
    {
        return $query->whereIn('estado', ['enviado', 'entregado', 'leido']);
    }

    /**
     * Scope para obtener mensajes con error.
     *
     * @param $query
     * @return mixed
     */
    public function scopeConError($query)
    {
        return $query->where('estado', 'error');
    }

    /**
     * Scope para obtener mensajes enviados hoy.
     *
     * @param $query
     * @return mixed
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_envio', today());
    }

    // ===================== MÉTODOS AUXILIARES =====================

    /**
     * Actualiza el estado del mensaje y registra fechas o errores según corresponda.
     *
     * @param string $nuevoEstado
     * @param array $metadata
     * @return void
     */
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

    /**
     * Devuelve un icono representativo según el estado del mensaje.
     *
     * @return string
     */
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

    /**
     * Devuelve un color representativo según el estado del mensaje.
     *
     * @return string
     */
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

    /**
     * Determina si el mensaje fue enviado en los últimos 5 minutos.
     *
     * @return bool
     */
    public function esReciente(): bool
    {
        return $this->fecha_envio->diffInMinutes(now()) <= 5;
    }
}
