<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ============================================================================
 * MODELO USUARIO - DENTALSYNC
 * ============================================================================
 *
 * Representa a los usuarios del sistema DentalSync.
 * Compatible con el sistema de autenticación de Laravel.
 *
 * CARACTERÍSTICAS:
 * - Soporta autenticación y notificaciones
 * - Puede usar soft deletes
 * - Métodos auxiliares para actualizar actividad
 *
 * @package App\Models
 * @author DentalSync Development Team
 * @version 2.0
 * @since 2025-09-04
 */
class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    
    protected $fillable = [
        'usuario', 'nombre', 'rol', 'password_hash', 'activo', 'ultimo_acceso', 'last_activity'
    ];
    
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'ultimo_acceso' => 'datetime',
        'last_activity' => 'datetime',
    ];
    
    // Para compatibilidad con Laravel Auth
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
    
    public function getAuthIdentifierName()
    {
        return 'id';
    }
    
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }
    
    /**
     * Verificar si el usuario está activo
     */
    public function isActive(): bool
    {
        return $this->activo === true;
    }
    
    /**
     * Actualizar última actividad
     *
     * Este método actualiza el campo `last_activity` y `ultimo_acceso` de
     * el usuario a la fecha y hora actuales.
     *
     * @return void
     */
    public function updateLastActivity(): void
    {
        $this->update([
            'last_activity' => now(),
            'ultimo_acceso' => now()
        ]);
    }
    
    /**
     * Verificar si la sesión ha expirado (más de 1 hora sin actividad)
     */
    public function isSessionExpired(): bool
    {
        if (!$this->last_activity) {
            return true;
        }
        
        return Carbon::parse($this->last_activity)->diffInHours(now()) > 1;
    }
}
