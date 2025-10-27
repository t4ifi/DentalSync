<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappConversacion;
use App\Models\WhatsappMensaje;
use App\Models\Paciente;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 * CONTROLADOR WEBHOOK WHATSAPP - DENTALSYNC
 * ============================================================================
 *
 * Maneja los webhooks de WhatsApp Business API para recibir mensajes,
 * actualizaciones de estado y otros eventos.
 *
 * FUNCIONALIDADES:
 * - Verificación de webhook
 * - Procesamiento de mensajes entrantes
 * - Actualización de estados de mensaje
 * - Gestión automática de conversaciones
 *
 * @package App\Http\Controllers\Api
 * @author Andrés Núñez
 * @version 1.0
 * @since 2025-10-09
 */
class WhatsappWebhookController extends Controller
{
    private WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Verificar webhook para WhatsApp
     * GET /api/webhook/whatsapp
     */
    public function verify(Request $request)
    {
        $verifyToken = config('whatsapp.webhook_verify_token');
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::info('WhatsApp Webhook Verification Request', [
            'mode' => $mode,
            'token' => $token,
            'challenge' => $challenge,
            'expected_token' => $verifyToken
        ]);

        if ($mode && $token && $mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp Webhook verified successfully');
            return response($challenge, 200, ['Content-Type' => 'text/plain']);
        }

        Log::warning('WhatsApp Webhook verification failed', [
            'provided_token' => $token,
            'expected_token' => $verifyToken
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Manejar eventos de webhook
     * POST /api/webhook/whatsapp
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        
        Log::info('WhatsApp Webhook Event Received', ['data' => $data]);

        try {
            if (isset($data['entry'])) {
                foreach ($data['entry'] as $entry) {
                    if (isset($entry['changes'])) {
                        foreach ($entry['changes'] as $change) {
                            $this->processChange($change);
                        }
                    }
                }
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp webhook', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            // Siempre retornar 200 para evitar que WhatsApp reintente
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }

    /**
     * Procesar cambio individual del webhook
     */
    private function processChange(array $change): void
    {
        $field = $change['field'] ?? '';
        $value = $change['value'] ?? [];

        switch ($field) {
            case 'messages':
                $this->processMessages($value);
                break;
                
            case 'message_template_status_update':
                $this->processTemplateStatusUpdate($value);
                break;
                
            default:
                Log::info('Unknown webhook field', ['field' => $field, 'value' => $value]);
        }
    }

    /**
     * Procesar mensajes entrantes y actualizaciones de estado
     */
    private function processMessages(array $value): void
    {
        // Procesar mensajes entrantes
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->processIncomingMessage($message);
            }
        }

        // Procesar actualizaciones de estado
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->processMessageStatus($status);
            }
        }
    }

    /**
     * Procesar mensaje entrante
     */
    private function processIncomingMessage(array $message): void
    {
        $from = $message['from'];
        $messageId = $message['id'];
        $timestamp = $message['timestamp'] ?? time();

        // Determinar tipo y contenido del mensaje
        $messageType = $this->getMessageType($message);
        $content = $this->getMessageContent($message, $messageType);

        Log::info('Processing incoming message', [
            'from' => $from,
            'message_id' => $messageId,
            'type' => $messageType,
            'content' => $content
        ]);

        try {
            // Buscar o crear conversación
            $conversacion = $this->findOrCreateConversation($from);

            // Verificar si el mensaje ya existe (prevenir duplicados)
            $existingMessage = WhatsappMensaje::where('mensaje_whatsapp_id', $messageId)->first();
            if ($existingMessage) {
                Log::info('Message already exists, skipping', ['message_id' => $messageId]);
                return;
            }

            // Crear mensaje
            $whatsappMessage = WhatsappMensaje::create([
                'conversacion_id' => $conversacion->id,
                'mensaje_whatsapp_id' => $messageId,
                'contenido' => $content,
                'es_propio' => false,
                'estado' => 'recibido',
                'tipo' => $messageType,
                'fecha_envio' => now()->createFromTimestamp($timestamp),
                'metadata' => json_encode($message)
            ]);

            // Actualizar conversación
            $conversacion->update([
                'ultimo_mensaje_fecha' => now()->createFromTimestamp($timestamp),
                'ultimo_mensaje_texto' => $content,
                'ultimo_mensaje_propio' => false,
                'mensajes_no_leidos' => $conversacion->mensajes_no_leidos + 1,
                'estado' => 'activa'
            ]);

            // Marcar mensaje como leído automáticamente
            try {
                $this->whatsappService->markAsRead($messageId);
            } catch (\Exception $e) {
                Log::warning('Failed to mark message as read', [
                    'message_id' => $messageId,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Message processed successfully', [
                'message_id' => $messageId,
                'conversation_id' => $conversacion->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing incoming message', [
                'from' => $from,
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar actualización de estado de mensaje
     */
    private function processMessageStatus(array $status): void
    {
        $messageId = $status['id'];
        $newStatus = $status['status']; // sent, delivered, read, failed
        $timestamp = $status['timestamp'] ?? time();

        Log::info('Processing message status update', [
            'message_id' => $messageId,
            'status' => $newStatus,
            'timestamp' => $timestamp
        ]);

        try {
            $message = WhatsappMensaje::where('mensaje_whatsapp_id', $messageId)->first();
            
            if (!$message) {
                Log::warning('Message not found for status update', ['message_id' => $messageId]);
                return;
            }

            // Mapear estados de WhatsApp a estados internos
            $internalStatus = $this->mapWhatsAppStatus($newStatus);
            
            $updateData = ['estado' => $internalStatus];
            
            // Actualizar campos de fecha según el estado
            switch ($newStatus) {
                case 'sent':
                    // Ya se establece en fecha_envio al crear
                    break;
                case 'delivered':
                    $updateData['fecha_entregado'] = now()->createFromTimestamp($timestamp);
                    break;
                case 'read':
                    $updateData['fecha_leido'] = now()->createFromTimestamp($timestamp);
                    break;
                case 'failed':
                    $updateData['error_mensaje'] = $status['errors'][0]['title'] ?? 'Error desconocido';
                    break;
            }

            $message->update($updateData);

            Log::info('Message status updated successfully', [
                'message_id' => $messageId,
                'old_status' => $message->estado,
                'new_status' => $internalStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating message status', [
                'message_id' => $messageId,
                'status' => $newStatus,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar actualización de estado de plantilla
     */
    private function processTemplateStatusUpdate(array $value): void
    {
        Log::info('Template status update received', ['data' => $value]);
        // Implementar si es necesario para plantillas
    }

    /**
     * Buscar o crear conversación
     */
    private function findOrCreateConversation(string $phoneNumber): WhatsappConversacion
    {
        $conversacion = WhatsappConversacion::where('telefono', $phoneNumber)->first();
        
        if (!$conversacion) {
            // Buscar paciente por teléfono
            $paciente = $this->findOrCreatePaciente($phoneNumber);
            
            $conversacion = WhatsappConversacion::create([
                'paciente_id' => $paciente->id,
                'telefono' => $phoneNumber,
                'nombre_contacto' => $paciente->nombre_completo,
                'estado' => 'activa',
                'mensajes_no_leidos' => 0
            ]);

            Log::info('New conversation created', [
                'conversation_id' => $conversacion->id,
                'phone' => $phoneNumber,
                'patient_id' => $paciente->id
            ]);
        }

        return $conversacion;
    }

    /**
     * Buscar o crear paciente por teléfono
     */
    private function findOrCreatePaciente(string $telefono): Paciente
    {
        // Formatear teléfono para búsqueda
        $telefonoFormateado = preg_replace('/[\s\-\(\)]/', '', $telefono);
        
        // Buscar por diferentes formatos de teléfono
        $paciente = Paciente::where('telefono', $telefono)
                           ->orWhere('telefono', $telefonoFormateado)
                           ->orWhere('telefono', 'like', '%' . substr($telefonoFormateado, -8))
                           ->first();
        
        if (!$paciente) {
            $paciente = Paciente::create([
                'nombre_completo' => 'Contacto WhatsApp - ' . $telefono,
                'telefono' => $telefono,
            ]);

            Log::info('New patient created from WhatsApp', [
                'patient_id' => $paciente->id,
                'phone' => $telefono
            ]);
        }

        return $paciente;
    }

    /**
     * Determinar tipo de mensaje
     */
    private function getMessageType(array $message): string
    {
        if (isset($message['text'])) return 'texto';
        if (isset($message['image'])) return 'imagen';
        if (isset($message['document'])) return 'documento';
        if (isset($message['audio'])) return 'audio';
        if (isset($message['video'])) return 'video';
        if (isset($message['voice'])) return 'audio';
        if (isset($message['sticker'])) return 'sticker';
        if (isset($message['location'])) return 'ubicacion';
        if (isset($message['contacts'])) return 'contacto';
        
        return 'desconocido';
    }

    /**
     * Extraer contenido del mensaje según su tipo
     */
    private function getMessageContent(array $message, string $type): string
    {
        switch ($type) {
            case 'texto':
                return $message['text']['body'] ?? '';
                
            case 'imagen':
                $caption = $message['image']['caption'] ?? '';
                return $caption ?: '[Imagen]';
                
            case 'documento':
                $filename = $message['document']['filename'] ?? 'documento';
                $caption = $message['document']['caption'] ?? '';
                return $caption ? "{$filename}: {$caption}" : "[Documento: {$filename}]";
                
            case 'audio':
            case 'voice':
                return '[Mensaje de voz]';
                
            case 'video':
                $caption = $message['video']['caption'] ?? '';
                return $caption ?: '[Video]';
                
            case 'sticker':
                return '[Sticker]';
                
            case 'ubicacion':
                $lat = $message['location']['latitude'] ?? '';
                $lng = $message['location']['longitude'] ?? '';
                return "[Ubicación: {$lat}, {$lng}]";
                
            case 'contacto':
                return '[Contacto compartido]';
                
            default:
                return '[Mensaje no soportado]';
        }
    }

    /**
     * Mapear estado de WhatsApp a estado interno
     */
    private function mapWhatsAppStatus(string $whatsappStatus): string
    {
        return match($whatsappStatus) {
            'sent' => 'enviado',
            'delivered' => 'entregado',
            'read' => 'leido',
            'failed' => 'error',
            default => 'enviando'
        };
    }
}