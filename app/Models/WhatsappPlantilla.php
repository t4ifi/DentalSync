<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clase WhatsappPlantilla
 *
 * Representa una plantilla de mensaje de WhatsApp que se puede reutilizar para automatizaciones.
 * Permite definir contenido con variables, categoría, estado de activación y control de usos.
 *
 * @package App\Models
 */
class WhatsappPlantilla extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'whatsapp_plantillas';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',               // Nombre de la plantilla
        'descripcion',          // Descripción opcional
        'categoria',            // Categoría de la plantilla (recordatorio, pago, bienvenida, etc.)
        'contenido',            // Contenido del mensaje con posibles variables {VAR}
        'activa',               // Indica si la plantilla está activa
        'usos',                 // Cantidad de veces que se ha usado
        'variables_detectadas', // Array con variables detectadas en el contenido
        'creado_por'            // ID del usuario que creó la plantilla
    ];

    /**
     * Conversión automática de campos a tipos de dato específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activa' => 'boolean',             // Indica si la plantilla está activa
        'usos' => 'integer',               // Cantidad de usos
        'variables_detectadas' => 'array'  // Variables detectadas en contenido
    ];

    // ===================== RELACIONES =====================

    /**
     * Relación con el usuario que creó la plantilla.
     *
     * @return BelongsTo
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    // ===================== SCOPES =====================

    /**
     * Scope para obtener solo plantillas activas.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope para obtener plantillas por categoría.
     *
     * @param $query
     * @param string $categoria
     * @return mixed
     */
    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope para ordenar plantillas por más usadas.
     *
     * @param $query
     * @return mixed
     */
    public function scopeMasUsadas($query)
    {
        return $query->orderBy('usos', 'desc');
    }

    // ===================== MÉTODOS AUXILIARES =====================

    /**
     * Incrementa el contador de usos de la plantilla.
     *
     * @return void
     */
    public function incrementarUsos(): void
    {
        $this->increment('usos');
    }

    /**
     * Detecta todas las variables presentes en el contenido del mensaje.
     *
     * @return array
     */
    public function detectarVariables(): array
    {
        preg_match_all('/{([^}]+)}/', $this->contenido, $matches);
        $variables = array_unique($matches[0]);

        $this->update(['variables_detectadas' => $variables]);

        return $variables;
    }

    /**
     * Reemplaza las variables detectadas con valores proporcionados.
     *
     * @param array $valores
     * @return string
     */
    public function reemplazarVariables(array $valores): string
    {
        $contenido = $this->contenido;

        foreach ($valores as $variable => $valor) {
            $contenido = str_replace($variable, $valor, $contenido);
        }

        return $contenido;
    }

    /**
     * Devuelve una vista previa del contenido limitado a un número de caracteres.
     *
     * @param int $limite
     * @return string
     */
    public function getVistaPrevia(int $limite = 100): string
    {
        if (strlen($this->contenido) > $limite) {
            return substr($this->contenido, 0, $limite) . '...';
        }

        return $this->contenido;
    }

    /**
     * Devuelve un color representativo según la categoría de la plantilla.
     *
     * @return string
     */
    public function getCategoriaColorAttribute(): string
    {
        return match($this->categoria) {
            'recordatorio' => 'blue',
            'confirmacion' => 'green',
            'pago' => 'yellow',
            'tratamiento' => 'purple',
            'bienvenida' => 'pink',
            'general' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Devuelve un texto legible según la categoría de la plantilla.
     *
     * @return string
     */
    public function getCategoriaTextoAttribute(): string
    {
        return match($this->categoria) {
            'recordatorio' => 'Recordatorios',
            'confirmacion' => 'Confirmaciones',
            'pago' => 'Pagos',
            'tratamiento' => 'Tratamientos',
            'bienvenida' => 'Bienvenida',
            'general' => 'General',
            default => 'General'
        };
    }
}
