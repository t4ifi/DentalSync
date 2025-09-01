<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Clase Usuario
 *
 * Representa un usuario del sistema, distinto del modelo `User` de autenticación de Laravel.
 * Se utiliza para manejar roles internos, estado activo y credenciales hash.
 *
 * @package App\Models
 */
class Usuario extends Model
{
    /**
     * Nombre de la tabla asociada en la base de datos.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'usuario',       // Nombre de usuario único
        'nombre',        // Nombre completo
        'rol',           // Rol del usuario (ej: dentista, recepcionista)
        'password_hash', // Contraseña en formato hash
        'activo'         // Estado del usuario (activo/inactivo)
    ];

    /**
     * Indica si el modelo debe mantener automáticamente los timestamps `created_at` y `updated_at`.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Atributos que deben ocultarse durante la serialización (ej: JSON).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash', // Para no exponer la contraseña
    ];
}
