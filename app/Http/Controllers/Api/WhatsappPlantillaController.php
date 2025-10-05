<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappPlantilla;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WhatsappPlantillaController extends Controller
{
    /**
     * Obtener todas las plantillas
     */
    public function index(Request $request): JsonResponse
    {
        $query = WhatsappPlantilla::query();

        // Filtros
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('contenido', 'like', "%{$busqueda}%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('estado')) {
            $activa = $request->estado === 'activa';
            $query->where('activa', $activa);
        }

        $plantillas = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function($plantilla) {
                return [
                    'id' => $plantilla->id,
                    'nombre' => $plantilla->nombre,
                    'descripcion' => $plantilla->descripcion,
                    'categoria' => $plantilla->categoria,
                    'contenido' => $plantilla->contenido,
                    'activa' => $plantilla->activa,
                    'usos' => $plantilla->usos,
                    'variables_detectadas' => $plantilla->variables_detectadas,
                    'fechaCreacion' => $plantilla->created_at->format('Y-m-d'),
                    'categoria_color' => $plantilla->categoria_color,
                    'categoria_texto' => $plantilla->categoria_texto
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $plantillas
        ]);
    }

    /**
     * Crear nueva plantilla
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:whatsapp_plantillas,nombre',
            'descripcion' => 'nullable|string|max:500',
            'categoria' => 'required|in:recordatorio,confirmacion,pago,tratamiento,bienvenida,general',
            'contenido' => 'required|string|max:1000',
            'activa' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $plantilla = WhatsappPlantilla::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'categoria' => $request->categoria,
                'contenido' => $request->contenido,
                'activa' => $request->activa ?? true,
                'creado_por' => Auth::id()
            ]);

            // Detectar variables automáticamente
            $plantilla->detectarVariables();

            return response()->json([
                'success' => true,
                'data' => $plantilla->fresh(),
                'message' => 'Plantilla creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener plantilla específica
     */
    public function show(WhatsappPlantilla $plantilla): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $plantilla->id,
                'nombre' => $plantilla->nombre,
                'descripcion' => $plantilla->descripcion,
                'categoria' => $plantilla->categoria,
                'contenido' => $plantilla->contenido,
                'activa' => $plantilla->activa,
                'usos' => $plantilla->usos,
                'variables_detectadas' => $plantilla->variables_detectadas,
                'fechaCreacion' => $plantilla->created_at->format('Y-m-d'),
                'categoria_color' => $plantilla->categoria_color,
                'categoria_texto' => $plantilla->categoria_texto
            ]
        ]);
    }

    /**
     * Actualizar plantilla
     */
    public function update(Request $request, WhatsappPlantilla $plantilla): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:whatsapp_plantillas,nombre,' . $plantilla->id,
            'descripcion' => 'nullable|string|max:500',
            'categoria' => 'required|in:recordatorio,confirmacion,pago,tratamiento,bienvenida,general',
            'contenido' => 'required|string|max:1000',
            'activa' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $plantilla->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'categoria' => $request->categoria,
                'contenido' => $request->contenido,
                'activa' => $request->activa ?? $plantilla->activa
            ]);

            // Detectar variables automáticamente
            $plantilla->detectarVariables();

            return response()->json([
                'success' => true,
                'data' => $plantilla->fresh(),
                'message' => 'Plantilla actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar plantilla
     */
    public function destroy(WhatsappPlantilla $plantilla): JsonResponse
    {
        try {
            $plantilla->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plantilla eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar plantilla
     */
    public function duplicar(WhatsappPlantilla $plantilla): JsonResponse
    {
        try {
            $nuevaPlantilla = $plantilla->replicate();
            $nuevaPlantilla->nombre = $plantilla->nombre . ' (Copia)';
            $nuevaPlantilla->usos = 0;
            $nuevaPlantilla->creado_por = Auth::id();
            $nuevaPlantilla->save();

            return response()->json([
                'success' => true,
                'data' => $nuevaPlantilla,
                'message' => 'Plantilla duplicada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de plantilla
     */
    public function toggleEstado(WhatsappPlantilla $plantilla): JsonResponse
    {
        try {
            $plantilla->update(['activa' => !$plantilla->activa]);

            $estado = $plantilla->activa ? 'activada' : 'desactivada';

            return response()->json([
                'success' => true,
                'data' => ['activa' => $plantilla->activa],
                'message' => "Plantilla {$estado} exitosamente"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Incrementar contador de usos
     */
    public function incrementarUsos(WhatsappPlantilla $plantilla): JsonResponse
    {
        try {
            $plantilla->incrementarUsos();

            return response()->json([
                'success' => true,
                'data' => ['usos' => $plantilla->usos]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al incrementar usos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener categorías disponibles
     */
    public function categorias(): JsonResponse
    {
        $categorias = [
            ['id' => 'recordatorio', 'nombre' => 'Recordatorios'],
            ['id' => 'confirmacion', 'nombre' => 'Confirmaciones'],
            ['id' => 'pago', 'nombre' => 'Pagos'],
            ['id' => 'tratamiento', 'nombre' => 'Tratamientos'],
            ['id' => 'bienvenida', 'nombre' => 'Bienvenida'],
            ['id' => 'general', 'nombre' => 'General']
        ];

        return response()->json([
            'success' => true,
            'data' => $categorias
        ]);
    }

    /**
     * Obtener estadísticas de plantillas
     */
    public function estadisticas(): JsonResponse
    {
        $stats = [
            'total' => WhatsappPlantilla::count(),
            'activas' => WhatsappPlantilla::activas()->count(),
            'inactivas' => WhatsappPlantilla::where('activa', false)->count(),
            'mas_usada' => WhatsappPlantilla::masUsadas()->first()?->nombre ?? 'N/A',
            'usos_totales' => WhatsappPlantilla::sum('usos')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
