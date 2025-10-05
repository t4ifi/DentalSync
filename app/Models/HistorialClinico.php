<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialClinico extends Model
{
    use HasFactory;

    protected $table = 'historial_clinico';

    protected $fillable = [
        'fecha_visita',
        'tratamiento',
        'observaciones',
        'paciente_id',
        'tratamiento_id'
    ];

    protected $casts = [
        'fecha_visita' => 'date',
    ];

    // Relación con el paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id', 'id');
    }

    // Relación con el tratamiento
    public function tratamientoRegistro()
    {
        return $this->belongsTo(Tratamiento::class, 'tratamiento_id');
    }
}
