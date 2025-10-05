<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacaDental extends Model
{
    use HasFactory;

    protected $table = 'placas_dentales';
    
    protected $fillable = [
        'fecha',
        'lugar',
        'tipo',
        'paciente_id',
        'archivo_url'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
