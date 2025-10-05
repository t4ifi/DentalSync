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
            // Log de datos recibidos para debugging
            \Log::info('Datos recibidos para registrar tratamiento:', [
                'all_data' => $request->all(),
                'user_session' => session('user'),
                'ip' => $request->ip()
            ]);

            $validatedData =            $request->validate([
                'paciente_id' => 'required|integer|exists:pacientes,id',
                'descripcion' => 'required|string|min:5|max:1000|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.,\-_()]+$/',
                'fecha_inicio' => 'required|date|before_or_equal:today',
                'observaciones' => 'nullable|string|max:1000|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.,\-_()]+$/'
            ], [
                'descripcion.min' => 'La descripción debe tener al menos 5 caracteres',
                'descripcion.regex' => 'La descripción contiene caracteres no válidos',
                'fecha_inicio.before_or_equal' => 'La fecha de inicio no puede ser futura',
                'observaciones.regex' => 'Las observaciones contienen caracteres no válidos'
            ]);

            // Obtener usuario automáticamente
            try {
                $usuarioId = $this->obtenerUsuarioAutomatico();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error del sistema: ' . $e->getMessage()
                ], 500);
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
            \Log::warning('Error de validación en registro de tratamiento:', [
                'errors' => $e->errors(),
                'input_data' => $request->all(),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error interno en registro de tratamiento:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
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

    /**
     * Obtener usuario automáticamente con sistema de fallback inteligente
     */
    private function obtenerUsuarioAutomatico()
    {
        // Intentar obtener usuario de la sesión primero
        $usuario = session('user');
        
        if ($usuario) {
            \Log::info('Usuario autenticado encontrado: ' . $usuario['nombre'] . ' (ID: ' . $usuario['id'] . ')');
            return $usuario['id'];
        }
        
        \Log::info('No hay sesión activa, buscando usuario automáticamente...');
        
        // Prioridad 1: Buscar dentistas activos
        $dentista = DB::table('usuarios')
            ->where('rol', 'dentista')
            ->where('activo', true)
            ->orderBy('id', 'asc')
            ->first();
        
        if ($dentista) {
            \Log::info('Usando dentista automático: ' . $dentista->nombre . ' (ID: ' . $dentista->id . ')');
            return $dentista->id;
        }
        
        // Prioridad 2: Buscar cualquier usuario activo
        $usuarioGeneral = DB::table('usuarios')
            ->where('activo', true)
            ->orderBy('id', 'asc')
            ->first();
        
        if ($usuarioGeneral) {
            \Log::info('Usando usuario general automático: ' . $usuarioGeneral->nombre . ' (ID: ' . $usuarioGeneral->id . ')');
            return $usuarioGeneral->id;
        }
        
        // Último recurso: Buscar cualquier usuario
        $cualquierUsuario = DB::table('usuarios')
            ->orderBy('id', 'asc')
            ->first();
        
        if ($cualquierUsuario) {
            \Log::warning('Usando último recurso - usuario: ' . $cualquierUsuario->nombre . ' (ID: ' . $cualquierUsuario->id . ')');
            return $cualquierUsuario->id;
        }
        
        // No hay usuarios en el sistema
        \Log::error('No se encontraron usuarios en el sistema');
        throw new \Exception('No hay usuarios disponibles en el sistema');
    }
}
