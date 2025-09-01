<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $fillable = [
        'usuario', 'nombre', 'rol', 'password_hash', 'activo'
    ];
    public $timestamps = true;
    protected $hidden = ['password_hash'];
}
