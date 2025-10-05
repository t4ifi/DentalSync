<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'nombre_completo',
        'telefono',
        'fecha_nacimiento',
        'ultima_visita',
        'motivo_consulta',
        'alergias',
        'observaciones'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'ultima_visita' => 'date',
    ];

    // Relaciones
    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'paciente_id');
    }

    public function historialClinico()
    {
        return $this->hasMany(HistorialClinico::class, 'paciente_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'paciente_id');
    }

    public function placasDentales()
    {
        return $this->hasMany(PlacaDental::class, 'paciente_id');
    }
}