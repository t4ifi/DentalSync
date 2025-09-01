<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    /**
     * Listar todas las citas, opcionalmente filtradas por fecha
     *
     * @param \Illuminate\Http\Request $request Puede incluir parámetro 'fecha' (YYYY-MM-DD)
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $fecha = $request->query('fecha'); // Filtrar por fecha si se pasa
            
            // Consulta con JOIN a pacientes y usuarios para obtener datos completos
            $query = DB::table('citas')
                ->leftJoin('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
                ->leftJoin('usuarios', 'citas.usuario_id', '=', 'usuarios.id')
                ->select(
                    'citas.id',
                    'citas.fecha',
                    'citas.motivo',
                    'citas.estado',
                    'citas.fecha_atendida',
                    'citas.paciente_id',
                    'citas.usuario_id',
                    'pacientes.nombre_completo',
                    'usuarios.nombre as usuario_nombre',
                    'citas.created_at',
                    'citas.updated_at'
                );
            
            if ($fecha) {
                $query->whereDate('citas.fecha', $fecha);
            }
            
            $citas = $query->orderBy('citas.fecha')->get();
            
            return response()->json($citas);
        } catch (\Exception $e) {
            \Log::error('Error al obtener citas:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar estado o fecha atendida de una cita
     *
     * @param \Illuminate\Http\Request $request Datos a actualizar ('estado', 'fecha_atendida')
     * @param int $id ID de la cita
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->only(['estado', 'fecha_atendida']);
            $updateData = ['updated_at' => now()];
            
            if (isset($data['estado'])) {
                $updateData['estado'] = $data['estado'];
                if ($data['estado'] === 'atendida') {
                    $updateData['fecha_atendida'] = now();
                }
            }
            
            // Actualizar registro en DB
            DB::table('citas')->where('id', $id)->update($updateData);
            
            // Obtener cita actualizada con información de paciente y usuario
            $cita = DB::table('citas')
                ->leftJoin('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
                ->leftJoin('usuarios', 'citas.usuario_id', '=', 'usuarios.id')
                ->select(
                    'citas.id',
                    'citas.fecha',
                    'citas.motivo',
                    'citas.estado',
                    'citas.fecha_atendida',
                    'citas.paciente_id',
                    'citas.usuario_id',
                    'pacientes.nombre_completo',
                    'usuarios.nombre as usuario_nombre',
                    'citas.created_at',
                    'citas.updated_at'
                )
                ->where('citas.id', $id)
                ->first();
            
            return response()->json(['success' => true, 'cita' => $cita]);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar cita:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Crear una nueva cita
     *
     * @param \Illuminate\Http\Request $request Datos de la cita ('fecha', 'motivo', 'nombre_completo', 'estado')
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validación de datos
            $validated = $request->validate([
                'fecha' => 'required|date',
                'motivo' => 'required|string',
                'nombre_completo' => 'required|string',
                'estado' => 'string|in:pendiente,confirmada,cancelada,atendida',
            ]);

            // Buscar paciente por nombre o crear uno nuevo básico
            $paciente = DB::table('pacientes')->where('nombre_completo', $validated['nombre_completo'])->first();
            $pacienteId = $paciente ? $paciente->id : DB::table('pacientes')->insertGetId([
                'nombre_completo' => $validated['nombre_completo'],
                'telefono' => null,
                'fecha_nacimiento' => null,
                'ultima_visita' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insertar nueva cita
            $citaId = DB::table('citas')->insertGetId([
                'fecha' => $validated['fecha'],
                'motivo' => $validated['motivo'],
                'estado' => $validated['estado'] ?? 'pendiente',
                'paciente_id' => $pacienteId,
                'usuario_id' => 3, // dentista por defecto
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Recuperar cita creada con detalles completos
            $cita = DB::table('citas')
                ->leftJoin('pacientes', 'citas.paciente_id', '=', 'pacientes.id')
                ->leftJoin('usuarios', 'citas.usuario_id', '=', 'usuarios.id')
                ->select(
                    'citas.id',
                    'citas.fecha',
                    'citas.motivo',
                    'citas.estado',
                    'citas.fecha_atendida',
                    'citas.paciente_id',
                    'citas.usuario_id',
                    'pacientes.nombre_completo',
                    'usuarios.nombre as usuario_nombre',
                    'citas.created_at',
                    'citas.updated_at'
                )
                ->where('citas.id', $citaId)
                ->first();

            return response()->json(['success' => true, 'cita' => $cita]);
        } catch (\Exception $e) {
            \Log::error('Error al crear cita:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar una cita por ID
     *
     * @param int $id ID de la cita a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::table('citas')->where('id', $id)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar cita:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }
}
