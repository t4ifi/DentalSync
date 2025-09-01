<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';

    protected $fillable = [
        'descripcion',
        'fecha_inicio',
        'estado',
        'paciente_id',
        'usuario_id'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
    ];

    // Relación con el paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id', 'id');
    }

    // Relación con el usuario (dentista)
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    // Relación con el historial clínico
    public function historialClinico()
    {
        return $this->hasMany(HistorialClinico::class, 'tratamiento_id');
    }
}
