<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        // Crear usuarios de ejemplo
        $usuarios = [
            [
                'usuario' => 'admin',
                'nombre' => 'Administrador del Sistema',
                'rol' => 'dentista',
                'password_hash' => Hash::make('admin123'),
                'activo' => true
            ],
            [
                'usuario' => 'dr.martinez',
                'nombre' => 'Dr. Carlos Martínez',
                'rol' => 'dentista',
                'password_hash' => Hash::make('dentista123'),
                'activo' => true
            ],
            [
                'usuario' => 'dra.lopez',
                'nombre' => 'Dra. María López',
                'rol' => 'dentista',
                'password_hash' => Hash::make('dentista123'),
                'activo' => true
            ],
            [
                'usuario' => 'recepcion1',
                'nombre' => 'Ana García',
                'rol' => 'recepcionista',
                'password_hash' => Hash::make('recepcion123'),
                'activo' => true
            ],
            [
                'usuario' => 'recepcion2',
                'nombre' => 'Laura Rodríguez',
                'rol' => 'recepcionista',
                'password_hash' => Hash::make('recepcion123'),
                'activo' => true
            ],
            [
                'usuario' => 'dr.inactivo',
                'nombre' => 'Dr. Usuario Inactivo',
                'rol' => 'dentista',
                'password_hash' => Hash::make('inactivo123'),
                'activo' => false
            ]
        ];

        foreach ($usuarios as $userData) {
            // Solo crear si no existe
            if (!Usuario::where('usuario', $userData['usuario'])->exists()) {
                Usuario::create($userData);
                $this->command->info("✅ Usuario '{$userData['usuario']}' creado");
            } else {
                $this->command->warn("⚠️ Usuario '{$userData['usuario']}' ya existe, omitido");
            }
        }

        $this->command->info('✅ 6 usuarios de ejemplo creados exitosamente');
    }
}
