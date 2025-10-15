<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappConversacion;
use App\Models\WhatsappMensaje;
use App\Models\Paciente;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class WhatsappConversacionController extends Controller
{
    private WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }
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
            'mensaje' => 'required|string|max:4096',
            'tipo' => 'sometimes|in:texto,imagen,documento,audio,video'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar si WhatsApp está configurado
            if (!$this->whatsappService->isConfigured()) {
                Log::warning('WhatsApp no configurado, simulando envío');
                
                // Modo simulación para desarrollo
                $mensaje = $conversacion->mensajes()->create([
                    'contenido' => $request->mensaje,
                    'es_propio' => true,
                    'estado' => 'enviado',
                    'tipo' => $request->tipo ?? 'texto',
                    'fecha_envio' => now(),
                    'mensaje_whatsapp_id' => 'sim_' . uniqid(),
                    'metadata' => json_encode(['simulado' => true])
                ]);

                $conversacion->actualizarUltimoMensaje($mensaje);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'messageId' => $mensaje->id,
                        'whatsappId' => $mensaje->mensaje_whatsapp_id,
                        'estado' => $mensaje->estado,
                        'timestamp' => $mensaje->fecha_envio->toISOString(),
                        'simulado' => true
                    ]
                ]);
            }

            // Validar mensaje
            if (!$this->whatsappService->validateMessage($request->mensaje)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El mensaje excede la longitud máxima permitida'
                ], 422);
            }

            // Validar número de teléfono
            if (!$this->whatsappService->isValidPhoneNumber($conversacion->telefono)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Número de teléfono inválido'
                ], 422);
            }

            // Crear mensaje en estado enviando
            $mensaje = $conversacion->mensajes()->create([
                'contenido' => $request->mensaje,
                'es_propio' => true,
                'estado' => 'enviando',
                'tipo' => $request->tipo ?? 'texto',
                'fecha_envio' => now(),
                'metadata' => $request->metadata ?? []
            ]);

            // Enviar via WhatsApp API
            $whatsappResponse = $this->whatsappService->sendTextMessage(
                $conversacion->telefono,
                $request->mensaje
            );

            // Actualizar mensaje con respuesta de WhatsApp
            $whatsappMessageId = $whatsappResponse['messages'][0]['id'] ?? null;
            
            $mensaje->update([
                'estado' => 'enviado',
                'mensaje_whatsapp_id' => $whatsappMessageId,
                'metadata' => json_encode(array_merge(
                    $request->metadata ?? [],
                    ['whatsapp_response' => $whatsappResponse]
                ))
            ]);

            // Actualizar conversación
            $conversacion->actualizarUltimoMensaje($mensaje);

            Log::info('WhatsApp message sent successfully', [
                'conversation_id' => $conversacion->id,
                'message_id' => $mensaje->id,
                'whatsapp_id' => $whatsappMessageId
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'messageId' => $mensaje->id,
                    'whatsappId' => $whatsappMessageId,
                    'estado' => $mensaje->estado,
                    'timestamp' => $mensaje->fecha_envio->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp message', [
                'conversation_id' => $conversacion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Si ya se creó el mensaje, marcarlo como error
            if (isset($mensaje)) {
                $mensaje->update([
                    'estado' => 'error',
                    'error_mensaje' => $e->getMessage()
                ]);
            }

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
