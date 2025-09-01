<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;
    protected $table = 'citas';
    protected $fillable = [
        'fecha', 'motivo', 'estado', 'fecha_atendida', 'paciente_id', 'usuario_id'
    ];
    protected $casts = [
        'fecha' => 'datetime',
        'fecha_atendida' => 'datetime',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
