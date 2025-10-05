<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlacaDental;
use App\Models\Paciente;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlacaController extends Controller
{
    public function index(Request $request)
    {
        $query = PlacaDental::with('paciente');
        
        // Filtros opcionales
        if ($request->has('paciente_id') && $request->paciente_id) {
            $query->where('paciente_id', $request->paciente_id);
        }
        
        if ($request->has('tipo') && $request->tipo) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        
        $placas = $query->orderBy('fecha', 'desc')->get();
        
        // Mapear para incluir información del paciente y URL del archivo
        $placas = $placas->map(function($placa) {
            return [
                'id' => $placa->id,
                'fecha' => $placa->fecha,
                'lugar' => $placa->lugar,
                'tipo' => $placa->tipo,
                'paciente_id' => $placa->paciente_id,
                'paciente_nombre' => $placa->paciente ? $placa->paciente->nombre_completo : null,
                'archivo_url' => $placa->archivo_url ? Storage::url($placa->archivo_url) : null,
                'created_at' => $placa->created_at,
                'updated_at' => $placa->updated_at,
            ];
        });
        
        return response()->json($placas);
    }

    public function show($id)
    {
        $placa = PlacaDental::with('paciente')->findOrFail($id);
        
        return response()->json([
            'id' => $placa->id,
            'fecha' => $placa->fecha,
            'lugar' => $placa->lugar,
            'tipo' => $placa->tipo,
            'paciente_id' => $placa->paciente_id,
            'paciente_nombre' => $placa->paciente ? $placa->paciente->nombre_completo : null,
            'archivo_url' => $placa->archivo_url ? Storage::url($placa->archivo_url) : null,
            'created_at' => $placa->created_at,
            'updated_at' => $placa->updated_at,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha' => 'required|date',
            'lugar' => 'required|string|max:255',
            'tipo' => 'required|in:panoramica,periapical,bitewing,lateral,oclusal',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB máximo
        ]);

        try {
            // Subir archivo
            $archivo = $request->file('archivo');
            $nombreArchivo = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
            $rutaArchivo = $archivo->storeAs('placas_dentales', $nombreArchivo, 'public');

            // Crear registro en base de datos
            $placa = PlacaDental::create([
                'paciente_id' => $request->paciente_id,
                'fecha' => $request->fecha,
                'lugar' => $request->lugar,
                'tipo' => $request->tipo,
                'archivo_url' => $rutaArchivo,
            ]);

            $placa->load('paciente');

            return response()->json([
                'success' => true,
                'message' => 'Placa dental subida correctamente',
                'placa' => [
                    'id' => $placa->id,
                    'fecha' => $placa->fecha,
                    'lugar' => $placa->lugar,
                    'tipo' => $placa->tipo,
                    'paciente_id' => $placa->paciente_id,
                    'paciente_nombre' => $placa->paciente ? $placa->paciente->nombre_completo : null,
                    'archivo_url' => Storage::url($placa->archivo_url),
                    'created_at' => $placa->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('PlacaController@store - Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al subir la placa dental: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $placa = PlacaDental::findOrFail($id);
        
        $request->validate([
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'fecha' => 'sometimes|date',
            'lugar' => 'sometimes|string|max:255',
            'tipo' => 'sometimes|in:panoramica,periapical,bitewing,lateral,oclusal',
            'archivo' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        try {
            // Actualizar campos básicos
            if ($request->has('paciente_id')) {
                $placa->paciente_id = $request->paciente_id;
            }
            if ($request->has('fecha')) {
                $placa->fecha = $request->fecha;
            }
            if ($request->has('lugar')) {
                $placa->lugar = $request->lugar;
            }
            if ($request->has('tipo')) {
                $placa->tipo = $request->tipo;
            }

            // Si hay nuevo archivo, reemplazar el anterior
            if ($request->hasFile('archivo')) {
                // Eliminar archivo anterior
                if ($placa->archivo_url) {
                    Storage::disk('public')->delete($placa->archivo_url);
                }

                // Subir nuevo archivo
                $archivo = $request->file('archivo');
                $nombreArchivo = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
                $rutaArchivo = $archivo->storeAs('placas_dentales', $nombreArchivo, 'public');
                $placa->archivo_url = $rutaArchivo;
            }

            $placa->save();
            $placa->load('paciente');

            return response()->json([
                'success' => true,
                'message' => 'Placa dental actualizada correctamente',
                'placa' => [
                    'id' => $placa->id,
                    'fecha' => $placa->fecha,
                    'lugar' => $placa->lugar,
                    'tipo' => $placa->tipo,
                    'paciente_id' => $placa->paciente_id,
                    'paciente_nombre' => $placa->paciente ? $placa->paciente->nombre_completo : null,
                    'archivo_url' => Storage::url($placa->archivo_url),
                    'updated_at' => $placa->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la placa dental: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $placa = PlacaDental::findOrFail($id);
            
            // Eliminar archivo del storage
            if ($placa->archivo_url) {
                Storage::disk('public')->delete($placa->archivo_url);
            }
            
            // Eliminar registro de la base de datos
            $placa->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Placa dental eliminada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la placa dental: ' . $e->getMessage()
            ], 500);
        }
    }
}
