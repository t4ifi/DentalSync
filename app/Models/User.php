<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Clase User
 *
 * Representa un usuario del sistema, que puede ser un dentista, recepcionista u otro rol autorizado.
 * Extiende de Authenticatable para manejar la autenticación y notificaciones de Laravel.
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    /** 
     * Trait para fábricas de modelo y notificaciones.
     * @use HasFactory
     * @use Notifiable
     */
    use HasFactory, Notifiable;

    /**
     * Atributos que se pueden asignar masivamente (mass assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',      // Nombre completo del usuario
        'email',     // Correo electrónico del usuario
        'password',  // Contraseña del usuario
    ];

    /**
     * Atributos que deben ocultarse durante la serialización (ej: JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',        // Para no exponer la contraseña
        'remember_token',  // Token de "recordarme" de Laravel
    ];

    /**
     * Conversión automática de atributos a tipos específicos.
     * Por ejemplo, el email verificado como datetime y el password como hashed.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Convierte la fecha de verificación a Carbon
            'password' => 'hashed',            // Hash automático de la contraseña
        ];
    }
}
