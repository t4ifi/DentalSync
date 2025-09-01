<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase Paciente
 *
 * Representa a un paciente dentro del sistema de gestión odontológica.
 * Contiene información personal, historial de visitas, consultas, tratamientos, pagos y placas dentales.
 *
 * @package App\Models
 */
class Paciente extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'pacientes';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre_completo',   // Nombre completo del paciente
        'telefono',          // Número de contacto del paciente
        'fecha_nacimiento',  // Fecha de nacimiento
        'ultima_visita',     // Fecha de la última visita realizada
        'motivo_consulta',   // Motivo de la consulta inicial o actual
        'alergias',          // Alergias conocidas
        'observaciones'      // Observaciones adicionales del paciente
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_nacimiento' => 'date', // Convierte a instancia de Carbon (solo fecha)
        'ultima_visita' => 'date',    // Convierte a instancia de Carbon (solo fecha)
    ];

    /**
     * Relación con los tratamientos.
     * Un paciente puede tener múltiples tratamientos registrados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'paciente_id');
    }

    /**
     * Relación con el historial clínico.
     * Un paciente puede tener múltiples registros de historial clínico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function historialClinico()
    {
        return $this->hasMany(HistorialClinico::class, 'paciente_id');
    }

    /**
     * Relación con las citas.
     * Un paciente puede tener múltiples citas registradas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citas()
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    /**
     * Relación con los pagos.
     * Un paciente puede tener múltiples pagos asociados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'paciente_id');
    }

    /**
     * Relación con las placas dentales.
     * Un paciente puede tener múltiples placas dentales registradas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function placasDentales()
    {
        return $this->hasMany(PlacaDental::class, 'paciente_id');
    }
}
