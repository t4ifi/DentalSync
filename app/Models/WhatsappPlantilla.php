<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappPlantilla extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_plantillas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'contenido',
        'activa',
        'usos',
        'variables_detectadas',
        'creado_por'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'usos' => 'integer',
        'variables_detectadas' => 'array'
    ];

    // Relaciones
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeMasUsadas($query)
    {
        return $query->orderBy('usos', 'desc');
    }

    // Métodos auxiliares
    public function incrementarUsos(): void
    {
        $this->increment('usos');
    }

    public function detectarVariables(): array
    {
        preg_match_all('/{([^}]+)}/', $this->contenido, $matches);
        $variables = array_unique($matches[0]);
        
        $this->update(['variables_detectadas' => $variables]);
        
        return $variables;
    }

    public function reemplazarVariables(array $valores): string
    {
        $contenido = $this->contenido;
        
        foreach ($valores as $variable => $valor) {
            $contenido = str_replace($variable, $valor, $contenido);
        }
        
        return $contenido;
    }

    public function getVistaPrevia(int $limite = 100): string
    {
        if (strlen($this->contenido) > $limite) {
            return substr($this->contenido, 0, $limite) . '...';
        }
        
        return $this->contenido;
    }

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
