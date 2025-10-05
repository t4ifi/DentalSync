<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappConversacion;
use App\Models\WhatsappMensaje;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WhatsappConversacionController extends Controller
{
    /**
     * Obtener todas las conversaciones
     */
    public function index(Request $request): JsonResponse
    {
        $query = WhatsappConversacion::with(['paciente'])
            ->ordenadaPorActividad();

        // Filtros
        if ($request->filled('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre_contacto', 'like', "%{$busqueda}%")
                  ->orWhere('telefono', 'like', "%{$busqueda}%")
                  ->orWhereHas('paciente', function($pq) use ($busqueda) {
                      $pq->where('nombre_completo', 'like', "%{$busqueda}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $conversaciones = $query->get()->map(function($conversacion) {
            return [
                'id' => $conversacion->id,
                'paciente_id' => $conversacion->paciente_id,
                'nombre' => $conversacion->nombre_contacto,
                'telefono' => $conversacion->telefono,
                'estado' => $conversacion->estado,
                'ultimoMensaje' => [
                    'texto' => $conversacion->ultimo_mensaje_texto,
                    'timestamp' => $conversacion->ultimo_mensaje_fecha?->toISOString(),
                    'esPropio' => $conversacion->ultimo_mensaje_propio
                ],
                'mensajesNoLeidos' => $conversacion->mensajes_no_leidos,
                'fechaCreacion' => $conversacion->created_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $conversaciones
        ]);
    }

    /**
     * Obtener mensajes de una conversación
     */
    public function mensajes(WhatsappConversacion $conversacion): JsonResponse
    {
        $mensajes = $conversacion->mensajes()
            ->orderBy('fecha_envio', 'asc')
            ->get()
            ->map(function($mensaje) {
                return [
                    'id' => $mensaje->id,
                    'texto' => $mensaje->contenido,
                    'timestamp' => $mensaje->fecha_envio->toISOString(),
                    'esPropio' => $mensaje->es_propio,
                    'estado' => $mensaje->estado,
                    'tipo' => $mensaje->tipo,
                    'metadata' => $mensaje->metadata
                ];
            });

        // Marcar conversación como leída
        $conversacion->marcarComoLeida();

        return response()->json([
            'success' => true,
            'data' => $mensajes
        ]);
    }

    /**
     * Enviar mensaje
     */
    public function enviarMensaje(Request $request, WhatsappConversacion $conversacion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mensaje' => 'required|string|max:1000',
            'tipo' => 'sometimes|in:texto,imagen,documento,audio,video'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Crear mensaje en la base de datos
            $mensaje = $conversacion->mensajes()->create([
                'contenido' => $request->mensaje,
                'es_propio' => true,
                'estado' => 'enviando',
                'tipo' => $request->tipo ?? 'texto',
                'fecha_envio' => now(),
                'metadata' => $request->metadata ?? []
            ]);

            // Actualizar última actividad de la conversación
            $conversacion->actualizarUltimoMensaje($mensaje);

            // TODO: Aquí integrar con WhatsApp Business API o Baileys
            // Por ahora simular envío exitoso
            $mensaje->update([
                'estado' => 'enviado',
                'mensaje_whatsapp_id' => 'sim_' . uniqid()
            ]);

            // Simular progresión de estados en desarrollo
            if (config('app.debug')) {
                // Entregado después de 2 segundos
                dispatch(function() use ($mensaje) {
                    sleep(2);
                    $mensaje->actualizarEstado('entregado');
                })->afterResponse();

                // Leído después de 5-15 segundos
                dispatch(function() use ($mensaje) {
                    sleep(rand(5, 15));
                    $mensaje->actualizarEstado('leido');
                })->afterResponse();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'messageId' => $mensaje->id,
                    'whatsappId' => $mensaje->mensaje_whatsapp_id,
                    'estado' => $mensaje->estado,
                    'timestamp' => $mensaje->fecha_envio->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva conversación
     */
    public function crear(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'mensaje' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $paciente = Paciente::findOrFail($request->paciente_id);

            // Verificar si ya existe una conversación activa
            $conversacionExistente = WhatsappConversacion::where('paciente_id', $request->paciente_id)
                ->where('telefono', $paciente->telefono)
                ->first();

            if ($conversacionExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una conversación activa con este paciente'
                ], 422);
            }

            // Crear nueva conversación
            $conversacion = WhatsappConversacion::create([
                'paciente_id' => $paciente->id,
                'telefono' => $paciente->telefono,
                'nombre_contacto' => $paciente->nombre_completo,
                'estado' => 'activa'
            ]);

            // Enviar mensaje inicial
            $mensaje = $conversacion->mensajes()->create([
                'contenido' => $request->mensaje,
                'es_propio' => true,
                'estado' => 'enviando',
                'fecha_envio' => now()
            ]);

            $conversacion->actualizarUltimoMensaje($mensaje);

            // TODO: Integrar con WhatsApp API
            $mensaje->update([
                'estado' => 'enviado',
                'mensaje_whatsapp_id' => 'sim_' . uniqid()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'conversacion' => [
                        'id' => $conversacion->id,
                        'nombre' => $conversacion->nombre_contacto,
                        'telefono' => $conversacion->telefono,
                        'estado' => $conversacion->estado
                    ],
                    'mensaje' => [
                        'id' => $mensaje->id,
                        'estado' => $mensaje->estado
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear conversación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado de conversación
     */
    public function actualizarEstado(Request $request, WhatsappConversacion $conversacion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:activa,pausada,cerrada,bloqueada'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $conversacion->update(['estado' => $request->estado]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
    }

    /**
     * Estadísticas de conversaciones
     */
    public function estadisticas(): JsonResponse
    {
        $stats = [
            'total' => WhatsappConversacion::count(),
            'activas' => WhatsappConversacion::activas()->count(),
            'conMensajesNoLeidos' => WhatsappConversacion::conMensajesNoLeidos()->count(),
            'mensajesHoy' => WhatsappMensaje::hoy()->count(),
            'mensajesEnviados' => WhatsappMensaje::enviados()->count(),
            'mensajesRecibidos' => WhatsappMensaje::recibidos()->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
