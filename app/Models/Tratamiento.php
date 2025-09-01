<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase Tratamiento
 *
 * Representa un tratamiento realizado a un paciente dentro del sistema de gestión odontológica.
 * Incluye información sobre la descripción, fecha de inicio, estado y relaciones con paciente, usuario y historial clínico.
 *
 * @package App\Models
 */
class Tratamiento extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'tratamientos';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descripcion',   // Descripción del tratamiento
        'fecha_inicio',  // Fecha de inicio del tratamiento
        'estado',        // Estado del tratamiento (ej: en progreso, finalizado, pendiente)
        'paciente_id',   // ID del paciente que recibe el tratamiento
        'usuario_id'     // ID del usuario (dentista) que realiza el tratamiento
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_inicio' => 'date', // Convierte a instancia de Carbon (solo fecha)
    ];

    /**
     * Relación con el modelo Paciente.
     * Cada tratamiento pertenece a un paciente específico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id', 'id');
    }

    /**
     * Relación con el modelo Usuario (dentista).
     * Indica qué usuario realizó el tratamiento.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    /**
     * Relación con el historial clínico.
     * Un tratamiento puede tener múltiples registros en el historial clínico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function historialClinico()
    {
        return $this->hasMany(HistorialClinico::class, 'tratamiento_id');
    }
}
