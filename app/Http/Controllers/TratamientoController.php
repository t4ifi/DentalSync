<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use App\Models\HistorialClinico;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TratamientoController extends Controller
{
    /**
     * Obtener todos los pacientes para el selector
     */
    public function getPacientes()
    {
        try {
            $pacientes = DB::table('pacientes')
                ->select('id', 'nombre_completo', 'telefono')
                ->orderBy('nombre_completo')
                ->get();

            return response()->json($pacientes);
        } catch (\Exception $e) {
            \Log::error('Error al cargar pacientes para tratamientos:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al cargar pacientes'], 500);
        }
    }

    /**
     * Obtener los tratamientos de un paciente
     */
    public function getTratamientosPaciente($pacienteId)
    {
        try {
            $tratamientos = DB::table('tratamientos')
                ->leftJoin('usuarios', 'tratamientos.usuario_id', '=', 'usuarios.id')
                ->where('tratamientos.paciente_id', $pacienteId)
                ->select(
                    'tratamientos.id',
                    'tratamientos.descripcion',
                    'tratamientos.fecha_inicio',
                    'tratamientos.estado',
                    'usuarios.nombre as dentista'
                )
                ->orderBy('tratamientos.fecha_inicio', 'desc')
                ->get();

            return response()->json($tratamientos);
        } catch (\Exception $e) {
            \Log::error('Error al cargar tratamientos del paciente:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al cargar tratamientos'], 500);
        }
    }

    /**
     * Registrar un nuevo tratamiento
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'descripcion' => 'required|string|max:1000',
                'fecha_inicio' => 'required|date',
                'observaciones' => 'nullable|string|max:1000'
            ]);

            // Obtener el usuario autenticado de la sesión o usar usuario por defecto
            $usuario = session('user');
            if (!$usuario) {
                // Usar el Dr. Juan Pérez (ID 3) como usuario por defecto
                $usuarioId = 3;
                \Log::info('Usando usuario por defecto: Dr. Juan Pérez (ID: 3)');
            } else {
                $usuarioId = $usuario['id'];
                \Log::info('Usuario autenticado: ' . $usuario['nombre']);
            }

            // Crear el tratamiento usando consulta directa
            $tratamientoId = DB::table('tratamientos')->insertGetId([
                'descripcion' => $request->descripcion,
                'fecha_inicio' => $request->fecha_inicio,
                'estado' => 'activo',
                'paciente_id' => $request->paciente_id,
                'usuario_id' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Si hay observaciones, crear entrada en historial clínico
            if ($request->observaciones) {
                DB::table('historial_clinico')->insert([
                    'fecha_visita' => $request->fecha_inicio,
                    'tratamiento' => $request->descripcion,
                    'observaciones' => $request->observaciones,
                    'paciente_id' => $request->paciente_id,
                    'tratamiento_id' => $tratamientoId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tratamiento registrado exitosamente',
                'tratamiento' => [
                    'id' => $tratamientoId,
                    'descripcion' => $request->descripcion,
                    'fecha_inicio' => $request->fecha_inicio,
                    'estado' => 'activo'
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar observación a un tratamiento existente
     */
    public function addObservacion(Request $request, $tratamientoId)
    {
        try {
            $request->validate([
                'observaciones' => 'required|string|max:1000',
                'fecha_visita' => 'required|date'
            ]);

            $tratamiento = DB::table('tratamientos')->where('id', $tratamientoId)->first();
            
            if (!$tratamiento) {
                return response()->json(['error' => 'Tratamiento no encontrado'], 404);
            }

            DB::table('historial_clinico')->insert([
                'fecha_visita' => $request->fecha_visita,
                'tratamiento' => 'Observación adicional',
                'observaciones' => $request->observaciones,
                'paciente_id' => $tratamiento->paciente_id,
                'tratamiento_id' => $tratamiento->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Observación agregada exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al agregar observación:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Finalizar un tratamiento
     */
    public function finalizar($tratamientoId)
    {
        try {
            $updated = DB::table('tratamientos')
                ->where('id', $tratamientoId)
                ->update([
                    'estado' => 'finalizado',
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json(['error' => 'Tratamiento no encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tratamiento finalizado exitosamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al finalizar tratamiento:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error al finalizar tratamiento'
            ], 500);
        }
    }

    /**
     * Obtener historial clínico de un paciente
     */
    public function getHistorialClinico($pacienteId)
    {
        try {
            $historial = DB::table('historial_clinico')
                ->leftJoin('tratamientos', 'historial_clinico.tratamiento_id', '=', 'tratamientos.id')
                ->where('historial_clinico.paciente_id', $pacienteId)
                ->select(
                    'historial_clinico.id',
                    'historial_clinico.fecha_visita',
                    'historial_clinico.tratamiento',
                    'historial_clinico.observaciones',
                    'tratamientos.estado as tratamiento_estado'
                )
                ->orderBy('historial_clinico.fecha_visita', 'desc')
                ->get();

            return response()->json($historial);
        } catch (\Exception $e) {
            \Log::error('Error al cargar historial clínico:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error al cargar historial clínico'], 500);
        }
    }
}