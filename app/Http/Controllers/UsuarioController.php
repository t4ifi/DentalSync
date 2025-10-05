<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $usuarios = Usuario::orderBy('created_at', 'desc')->get();
            
            // Formatear usuarios para el frontend
            $usuarios = $usuarios->map(function($usuario) {
                return [
                    'id' => $usuario->id,
                    'usuario' => $usuario->usuario,
                    'nombre' => $usuario->nombre,
                    'rol' => $usuario->rol,
                    'activo' => $usuario->activo,
                    'created_at' => $usuario->created_at,
                    'updated_at' => $usuario->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $usuarios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'usuario' => 'required|string|max:100|unique:usuarios,usuario',
                'nombre' => 'required|string|max:255',
                'rol' => 'required|in:dentista,recepcionista',
                'password' => 'required|string|min:6',
                'activo' => 'boolean'
            ], [
                'usuario.required' => 'El nombre de usuario es obligatorio',
                'usuario.unique' => 'Este nombre de usuario ya está en uso',
                'nombre.required' => 'El nombre completo es obligatorio',
                'rol.required' => 'Debe seleccionar un rol',
                'rol.in' => 'El rol debe ser dentista o recepcionista',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres'
            ]);

            $usuario = Usuario::create([
                'usuario' => $request->usuario,
                'nombre' => $request->nombre,
                'rol' => $request->rol,
                'password_hash' => Hash::make($request->password),
                'activo' => $request->activo ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => [
                    'id' => $usuario->id,
                    'usuario' => $usuario->usuario,
                    'nombre' => $usuario->nombre,
                    'rol' => $usuario->rol,
                    'activo' => $usuario->activo,
                    'created_at' => $usuario->created_at
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $usuario->id,
                    'usuario' => $usuario->usuario,
                    'nombre' => $usuario->nombre,
                    'rol' => $usuario->rol,
                    'activo' => $usuario->activo,
                    'created_at' => $usuario->created_at,
                    'updated_at' => $usuario->updated_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            $request->validate([
                'usuario' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('usuarios')->ignore($usuario->id)
                ],
                'nombre' => 'required|string|max:255',
                'rol' => 'required|in:dentista,recepcionista',
                'password' => 'nullable|string|min:6',
                'activo' => 'boolean'
            ], [
                'usuario.required' => 'El nombre de usuario es obligatorio',
                'usuario.unique' => 'Este nombre de usuario ya está en uso',
                'nombre.required' => 'El nombre completo es obligatorio',
                'rol.required' => 'Debe seleccionar un rol',
                'rol.in' => 'El rol debe ser dentista o recepcionista',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres'
            ]);

            $dataToUpdate = [
                'usuario' => $request->usuario,
                'nombre' => $request->nombre,
                'rol' => $request->rol,
                'activo' => $request->activo ?? $usuario->activo
            ];

            // Solo actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $dataToUpdate['password_hash'] = Hash::make($request->password);
            }

            $usuario->update($dataToUpdate);

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => [
                    'id' => $usuario->id,
                    'usuario' => $usuario->usuario,
                    'nombre' => $usuario->nombre,
                    'rol' => $usuario->rol,
                    'activo' => $usuario->activo,
                    'updated_at' => $usuario->updated_at
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            
            // No permitir eliminar si es el último usuario activo
            $usuariosActivos = Usuario::where('activo', true)->count();
            if ($usuariosActivos === 1 && $usuario->activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el último usuario activo del sistema'
                ], 400);
            }

            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            
            // No permitir desactivar si es el último usuario activo
            if ($usuario->activo) {
                $usuariosActivos = Usuario::where('activo', true)->count();
                if ($usuariosActivos === 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede desactivar el último usuario activo del sistema'
                    ], 400);
                }
            }

            $usuario->activo = !$usuario->activo;
            $usuario->save();

            return response()->json([
                'success' => true,
                'message' => $usuario->activo ? 'Usuario activado' : 'Usuario desactivado',
                'data' => [
                    'id' => $usuario->id,
                    'activo' => $usuario->activo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics about users
     */
    public function statistics()
    {
        try {
            $totalUsuarios = Usuario::count();
            $usuariosActivos = Usuario::where('activo', true)->count();
            $usuariosInactivos = Usuario::where('activo', false)->count();
            $dentistas = Usuario::where('rol', 'dentista')->count();
            $recepcionistas = Usuario::where('rol', 'recepcionista')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalUsuarios,
                    'activos' => $usuariosActivos,
                    'inactivos' => $usuariosInactivos,
                    'dentistas' => $dentistas,
                    'recepcionistas' => $recepcionistas
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics resumen - alias for statistics()
     */
    public function getEstadisticas()
    {
        return $this->statistics();
    }
}
