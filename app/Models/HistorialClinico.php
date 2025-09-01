<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase HistorialClinico
 *
 * Representa el historial clínico de un paciente.
 * Cada registro incluye la fecha de visita, el tratamiento realizado, observaciones y referencias al paciente y al tratamiento.
 *
 * @package App\Models
 */
class HistorialClinico extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'historial_clinico';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fecha_visita',     // Fecha en que se realizó la visita
        'tratamiento',      // Nombre o descripción del tratamiento aplicado
        'observaciones',    // Observaciones del profesional sobre la visita/tratamiento
        'paciente_id',      // Relación con el paciente
        'tratamiento_id'    // Relación con el tratamiento registrado
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_visita' => 'date', // Convierte a instancia de Carbon (solo fecha)
    ];

    /**
     * Relación con el modelo Paciente.
     * Cada historial clínico pertenece a un paciente específico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id', 'id');
    }

    /**
     * Relación con el modelo Tratamiento.
     * Indica el tratamiento registrado en este historial clínico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tratamientoRegistro()
    {
        return $this->belongsTo(Tratamiento::class, 'tratamiento_id');
    }
}
