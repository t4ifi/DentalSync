<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase PlacaDental
 *
 * Representa una placa dental de un paciente dentro del sistema.
 * Contiene información sobre la fecha, lugar donde se tomó, tipo de placa y el archivo asociado.
 *
 * @package App\Models
 */
class PlacaDental extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'placas_dentales';
    
    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fecha',        // Fecha en que se tomó la placa dental
        'lugar',        // Lugar donde se realizó la placa (ej: clínica, laboratorio)
        'tipo',         // Tipo de placa (ej: radiografía panorámica, periapical, etc.)
        'paciente_id',  // ID del paciente al que pertenece la placa
        'archivo_url'   // Ruta o URL del archivo digital de la placa
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'date', // Convierte a instancia de Carbon (solo fecha)
    ];

    /**
     * Relación con el modelo Paciente.
     * Cada placa pertenece a un paciente específico.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
