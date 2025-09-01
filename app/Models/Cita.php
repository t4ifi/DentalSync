<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase Cita
 *
 * Representa una cita en el sistema de gestión odontológica.
 * Cada cita está asociada a un paciente y a un usuario (dentista o recepcionista).
 * Contiene información sobre la fecha, motivo, estado y si fue atendida.
 *
 * @package App\Models
 */
class Cita extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'citas';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fecha',            // Fecha programada de la cita
        'motivo',           // Motivo de la cita (ej: control, tratamiento, urgencia, etc.)
        'estado',           // Estado actual (ej: pendiente, confirmada, cancelada, atendida)
        'fecha_atendida',   // Fecha en la que efectivamente fue atendida (puede ser null si no se atendió aún)
        'paciente_id',      // Relación con el paciente
        'usuario_id'        // Relación con el usuario (dentista o recepcionista que registró la cita)
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'datetime',          // Convierte automáticamente a instancia de Carbon (fecha/hora)
        'fecha_atendida' => 'datetime', // Convierte automáticamente a instancia de Carbon (fecha/hora)
    ];

    /**
     * Relación con el modelo Paciente.
     * Una cita pertenece a un único paciente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    /**
     * Relación con el modelo Usuario.
     * Una cita pertenece a un único usuario (dentista o recepcionista).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
