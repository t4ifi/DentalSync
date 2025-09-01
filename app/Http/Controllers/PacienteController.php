<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;

class PacienteController extends Controller
{
    /**
     * Listar todos los pacientes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Obtener todos los registros de pacientes
            $pacientes = \DB::table('pacientes')->get();

            // Retornar los pacientes en formato JSON
            return response()->json($pacientes);
        } catch (\Exception $e) {
            // Registrar el error en logs
            \Log::error("Error al obtener pacientes: " . $e->getMessage());

            // Retornar error al cliente
            return response()->json(['error' => 'Error al cargar pacientes'], 500);
        }
    }

    /**
     * Mostrar información específica de un paciente
     *
     * @param int $id ID del paciente
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            \Log::info("Buscando paciente con ID: {$id}");
            
            // Buscar paciente por ID
            $paciente = \DB::table('pacientes')->where('id', $id)->first();
            
            if (!$paciente) {
                \Log::error("Paciente no encontrado con ID: {$id}");
                return response()->json(['error' => 'Paciente no encontrado'], 404);
            }
            
            \Log::info("Paciente encontrado: {$paciente->nombre_completo}");
            
            return response()->json($paciente);
        } catch (\Exception $e) {
            \Log::error("Error al buscar paciente: {$e->getMessage()}");
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Actualizar información de un paciente existente
     *
     * @param \Illuminate\Http\Request $request Datos del paciente a actualizar
     * @param int $id ID del paciente
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Validar datos de entrada
            $validated = $request->validate([
                'nombre_completo' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'fecha_nacimiento' => 'nullable|date|before:today',
                'ultima_visita' => 'nullable|date',
            ]);

            // Buscar paciente o lanzar excepción si no existe
            $paciente = Paciente::findOrFail($id);
            
            // Actualizar campos permitidos
            $paciente->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente actualizado exitosamente',
                'paciente' => $paciente->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error de validación de datos
            return response()->json([
                'error' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Error interno
            return response()->json([
                'error' => 'Error al actualizar paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo paciente
     *
     * @param \Illuminate\Http\Request $request Datos del nuevo paciente
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validar datos de entrada con mensajes personalizados
            $validated = $request->validate([
                'nombre_completo' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'fecha_nacimiento' => 'required|date|before:today',
                'motivo_consulta' => 'required|string|max:1000',
                'alergias' => 'nullable|string|max:1000',
                'observaciones' => 'nullable|string|max:1000',
            ], [
                'nombre_completo.required' => 'El nombre completo es obligatorio',
                'telefono.required' => 'El teléfono es obligatorio',
                'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
                'motivo_consulta.required' => 'El motivo de consulta es obligatorio',
            ]);

            // Agregar fecha de última visita automáticamente
            $validated['ultima_visita'] = now()->toDateString();

            // Crear el paciente en la base de datos
            $paciente = Paciente::create($validated);
            
            // Registrar creación en logs
            \Log::info('Nuevo paciente creado', [
                'paciente_id' => $paciente->id,
                'nombre' => $paciente->nombre_completo,
                'telefono' => $paciente->telefono,
                'motivo_consulta' => $paciente->motivo_consulta
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente registrado exitosamente',
                'paciente' => $paciente
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear paciente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al crear paciente'
            ], 500);
        }
    }
}
