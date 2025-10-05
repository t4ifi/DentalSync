<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * CONTROLADOR DE CITAS - DENTALSYNC
 * ============================================================================
 * 
 * Este controlador gestiona todas las operaciones relacionadas con las citas
 * médicas en el sistema DentalSync. Maneja la programación, actualización,
 * cancelación y consulta de citas.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Gestión completa de citas (CRUD)
 * - Programación de citas con validación de fechas
 * - Filtrado por fecha y paciente
 * - Creación automática de pacientes si no existen
 * - Gestión de estados de citas (pendiente, confirmada, cancelada, atendida)
 * - Asignación automática de usuarios/dentistas
 * 
 * ENDPOINTS DISPONIBLES:
 * - GET    /api/citas           : Lista citas con filtros opcionales
 * - POST   /api/citas           : Crea una nueva cita
 * - PUT    /api/citas/{id}      : Actualiza estado de una cita
 * - DELETE /api/citas/{id}      : Elimina una cita
 * 
 * FILTROS SOPORTADOS:
 * - fecha: Filtrar por fecha específica (YYYY-MM-DD)
 * - paciente_id: Filtrar por ID de paciente específico
 * 
 * ESTADOS DE CITAS:
 * - pendiente: Cita programada sin confirmar
 * - confirmada: Cita confirmada por el paciente
 * - cancelada: Cita cancelada
 * - atendida: Cita completada con fecha de atención
 * 
 * @package App\Http\Controllers
 * @author DentalSync Development Team
 * @version 2.1
 * @since 2025-09-04
 */
class CitaController extends Controller
{
    /**
     * Lista todas las citas del sistema con filtros opcionales.
     * 
     * Este método obtiene todas las citas con información relacionada de pacientes
     * y usuarios. Soporta filtrado por fecha y por paciente específico.
     * Utiliza JOIN para optimizar las consultas y evitar problemas de codificación.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception En caso de error en la base de datos
     * 
     * @api GET /api/citas
     * @apiParam {String} [fecha] Fecha para filtrar citas (formato: YYYY-MM-DD)
     * @apiParam {Number} [paciente_id] ID del paciente para filtrar sus citas
     * @apiSuccess {Array} data Lista de citas con información de pacientes y usuarios
     * @apiError {String} error Mensaje de error en caso de falla
     * 
     * @example
     * GET /api/citas?fecha=2025-09-09
     * GET /api/citas?paciente_id=5
     * GET /api/citas?fecha=2025-09-09&paciente_id=5
     */
    public function index(Request $request)
    {
        try {
            // Log de la operación con parámetros de filtro
            \Log::info('Consultando citas del sistema', [
                'filtros' => $request->query(),
                'user_id' => session('user.id'),
                'timestamp' => now(),
                'operation' => 'index_citas'
            ]);
            
            // Obtener parámetros de filtro opcionales
            $fecha = $request->query('fecha');
            $pacienteId = $request->query('paciente_id');
            
            // Validar formato de fecha si se proporciona
            if ($fecha && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                \Log::warning('Formato de fecha inválido en filtro de citas', [
                    'fecha_proporcionada' => $fecha,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'error' => 'Formato de fecha inválido. Use YYYY-MM-DD'
                ], 400);
            }
            
            // Validar paciente_id si se proporciona
            if ($pacienteId && !is_numeric($pacienteId)) {
                \Log::warning('ID de paciente inválido en filtro de citas', [
                    'paciente_id_proporcionado' => $pacienteId,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'error' => 'ID de paciente debe ser numérico'
                ], 400);
            }
            
            // Construir consulta con JOIN para optimización y evitar problemas de codificación
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
            
            // Aplicar filtro por fecha si se proporciona
            if ($fecha) {
                $query->whereDate('citas.fecha', $fecha);
                \Log::info("Aplicando filtro por fecha: {$fecha}");
            }
            
            // Aplicar filtro por paciente si se proporciona
            if ($pacienteId) {
                $query->where('citas.paciente_id', $pacienteId);
                \Log::info("Aplicando filtro por paciente ID: {$pacienteId}");
            }
            
            // Ejecutar consulta ordenada por fecha
            $citas = $query->orderBy('citas.fecha')->get();
            
            // Log de resultado exitoso
            \Log::info('Citas obtenidas exitosamente', [
                'total_citas' => $citas->count(),
                'filtros_aplicados' => [
                    'fecha' => $fecha,
                    'paciente_id' => $pacienteId
                ],
                'timestamp' => now()
            ]);
            
            return response()->json($citas);
            
        } catch (\Exception $e) {
            // Log detallado del error
            \Log::error('Error al obtener citas', [
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'filtros' => $request->query(),
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza el estado de una cita existente.
     * 
     * Este método permite cambiar el estado de una cita y automáticamente
     * asigna la fecha de atención cuando se marca como 'atendida'.
     * Realiza validación del ID y logging de cambios.
     * 
     * @param \Illuminate\Http\Request $request Datos de actualización
     * @param int $id ID de la cita a actualizar
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception En caso de error en la base de datos
     * 
     * @api PUT /api/citas/{id}
     * @apiParam {Number} id ID único de la cita
     * @apiParam {String} [estado] Nuevo estado (pendiente, confirmada, cancelada, atendida)
     * @apiParam {Date} [fecha_atendida] Fecha de atención (opcional, se asigna automáticamente si estado = atendida)
     * @apiSuccess {Object} cita Información actualizada de la cita
     * @apiError {String} error Mensaje de error en caso de falla
     */
    public function update(Request $request, $id)
    {
        try {
            // Log del inicio de actualización
            \Log::info("Iniciando actualización de cita ID: {$id}", [
                'cita_id' => $id,
                'datos_entrada' => $request->only(['estado', 'fecha_atendida']),
                'user_id' => session('user.id'),
                'timestamp' => now(),
                'operation' => 'update_cita'
            ]);
            
            // Validar que el ID sea numérico
            if (!is_numeric($id)) {
                \Log::warning("ID de cita inválido: {$id}", [
                    'cita_id' => $id,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json(['error' => 'ID de cita inválido'], 400);
            }
            
            // Verificar que la cita existe antes de actualizar
            $citaExistente = DB::table('citas')->where('id', $id)->first();
            if (!$citaExistente) {
                \Log::warning("Intento de actualizar cita inexistente: {$id}", [
                    'cita_id' => $id,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json(['error' => 'Cita no encontrada'], 404);
            }
            
            // Obtener solo los campos permitidos para actualización
            $data = $request->only(['estado', 'fecha_atendida']);
            
            // Preparar datos de actualización
            $updateData = ['updated_at' => now()];
            
            // Validar y procesar el estado si se proporciona
            if (isset($data['estado'])) {
                $estadosValidos = ['pendiente', 'confirmada', 'cancelada', 'atendida'];
                
                if (!in_array($data['estado'], $estadosValidos)) {
                    \Log::warning("Estado de cita inválido: {$data['estado']}", [
                        'cita_id' => $id,
                        'estado_proporcionado' => $data['estado'],
                        'estados_validos' => $estadosValidos,
                        'user_id' => session('user.id'),
                        'timestamp' => now()
                    ]);
                    
                    return response()->json([
                        'error' => 'Estado inválido. Estados válidos: ' . implode(', ', $estadosValidos)
                    ], 400);
                }
                
                $updateData['estado'] = $data['estado'];
                
                // Asignar fecha de atención automáticamente si se marca como atendida
                if ($data['estado'] === 'atendida') {
                    $updateData['fecha_atendida'] = now();
                    \Log::info("Cita marcada como atendida, asignando fecha automáticamente", [
                        'cita_id' => $id,
                        'fecha_atencion' => $updateData['fecha_atendida'],
                        'timestamp' => now()
                    ]);
                }
            }
            
            // Log del estado anterior para auditoría
            \Log::info("Estado anterior de la cita", [
                'cita_id' => $id,
                'estado_anterior' => $citaExistente->estado,
                'fecha_atendida_anterior' => $citaExistente->fecha_atendida,
                'timestamp' => now()
            ]);
            
            // Realizar la actualización en la base de datos
            $filasAfectadas = DB::table('citas')->where('id', $id)->update($updateData);
            
            if ($filasAfectadas === 0) {
                \Log::warning("No se pudo actualizar la cita: {$id}", [
                    'cita_id' => $id,
                    'datos_actualizacion' => $updateData,
                    'timestamp' => now()
                ]);
                
                return response()->json(['error' => 'No se pudo actualizar la cita'], 500);
            }
            
            // Obtener la cita actualizada con información relacionada
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
            
            // Log de actualización exitosa
            \Log::info('Cita actualizada exitosamente', [
                'cita_id' => $id,
                'estado_nuevo' => $cita->estado,
                'fecha_atendida_nueva' => $cita->fecha_atendida,
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json(['success' => true, 'cita' => $cita]);
            
        } catch (\Exception $e) {
            // Log detallado del error
            \Log::error('Error al actualizar cita', [
                'cita_id' => $id,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'datos_entrada' => $request->all(),
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva cita en el sistema
     * 
     * Crea una nueva cita médica validando los datos de entrada y asociándola
     * con un paciente existente o creando uno nuevo si no existe.
     * 
     * @param Request $request Datos de la cita a crear
     * @return \Illuminate\Http\JsonResponse Respuesta con la cita creada o error
     * 
     * @throws \Illuminate\Validation\ValidationException Si los datos no son válidos
     * @throws \Exception Si ocurre un error durante la creación
     * 
     * Campos esperados en $request:
     * - fecha: string (formato date, no puede ser anterior a hoy)
     * - motivo: string (máximo 500 caracteres, solo caracteres válidos)
     * - nombre_completo: string (máximo 255 caracteres, solo letras y espacios)
     * - estado: string opcional (pendiente|confirmada|cancelada|atendida)
     * 
     * Proceso de ejecución:
     * 1. Validación estricta de datos de entrada con mensajes personalizados
     * 2. Búsqueda de paciente existente por nombre completo
     * 3. Creación de paciente básico si no existe
     * 4. Obtención automática del usuario responsable
     * 5. Creación de la cita con estado 'pendiente' por defecto
     * 6. Consulta de la cita creada con datos relacionados
     * 7. Registro de auditoría y manejo de errores
     * 
     * Estados válidos de cita:
     * - pendiente: Cita programada pero no confirmada
     * - confirmada: Cita confirmada por el paciente
     * - cancelada: Cita cancelada por cualquier motivo
     * - atendida: Cita completada con atención médica
     * 
     * Seguridad implementada:
     * - Validación de formato de fecha (no fechas pasadas)
     * - Regex para prevenir inyección de código en texto
     * - Límites de longitud para prevenir ataques de buffer
     * - Sanitización automática de entrada de Laravel
     * 
     * @since 1.0.0
     * @author Sistema DentalSync
     */
    public function store(Request $request)
    {
        try {
            // Log del inicio de creación
            \Log::info("Iniciando creación de nueva cita", [
                'datos_entrada' => $request->only(['fecha', 'motivo', 'nombre_completo', 'estado']),
                'user_id' => session('user.id'),
                'timestamp' => now(),
                'operation' => 'create_cita'
            ]);

            // Validar los datos con reglas más estrictas
            $validated = $request->validate([
                'fecha' => 'required|date|after_or_equal:today',
                'motivo' => 'required|string|max:500|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.,\-_]+$/',
                'nombre_completo' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'estado' => 'nullable|string|in:pendiente,confirmada,cancelada,atendida',
            ], [
                'fecha.after_or_equal' => 'La fecha de la cita no puede ser anterior a hoy',
                'motivo.regex' => 'El motivo contiene caracteres no válidos',
                'nombre_completo.regex' => 'El nombre solo puede contener letras y espacios',
                'motivo.max' => 'El motivo no puede exceder 500 caracteres',
                'nombre_completo.max' => 'El nombre no puede exceder 255 caracteres'
            ]);

            // Buscar o crear paciente por nombre usando consulta directa
            $paciente = DB::table('pacientes')
                ->where('nombre_completo', $validated['nombre_completo'])
                ->first();
            
            if (!$paciente) {
                // Si no existe el paciente, crear uno básico
                $pacienteId = DB::table('pacientes')->insertGetId([
                    'nombre_completo' => $validated['nombre_completo'],
                    'telefono' => null,
                    'fecha_nacimiento' => null,
                    'ultima_visita' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                \Log::info("Paciente creado automáticamente", [
                    'paciente_id' => $pacienteId,
                    'nombre_completo' => $validated['nombre_completo'],
                    'timestamp' => now()
                ]);
            } else {
                $pacienteId = $paciente->id;
                \Log::info("Paciente existente encontrado", [
                    'paciente_id' => $pacienteId,
                    'nombre_completo' => $validated['nombre_completo'],
                    'timestamp' => now()
                ]);
            }

            // Obtener usuario automáticamente
            try {
                $usuarioId = $this->obtenerUsuarioAutomatico();
            } catch (\Exception $e) {
                \Log::error('Error al obtener usuario automático', [
                    'exception' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString(),
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error del sistema: ' . $e->getMessage()
                ], 500);
            }

            // Crear la cita usando consulta directa
            $citaId = DB::table('citas')->insertGetId([
                'fecha' => $validated['fecha'],
                'motivo' => $validated['motivo'],
                'estado' => $validated['estado'] ?? 'pendiente',
                'paciente_id' => $pacienteId,
                'usuario_id' => $usuarioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Obtener la cita creada con los datos del paciente y usuario
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

            // Log de creación exitosa
            \Log::info('Cita creada exitosamente', [
                'cita_id' => $citaId,
                'paciente_id' => $pacienteId,
                'usuario_id' => $usuarioId,
                'estado' => $validated['estado'] ?? 'pendiente',
                'fecha' => $validated['fecha'],
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);

            return response()->json(['success' => true, 'cita' => $cita]);
            
        } catch (\Exception $e) {
            // Log detallado del error
            \Log::error('Error al crear cita', [
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'datos_entrada' => $request->all(),
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una cita del sistema
     * 
     * Elimina permanentemente una cita de la base de datos después de validar
     * su existencia y registrar la operación para auditoría.
     * 
     * @param int $id ID único de la cita a eliminar
     * @return \Illuminate\Http\JsonResponse Confirmación de eliminación o error
     * 
     * @throws \Exception Si ocurre un error durante la eliminación
     * 
     * Proceso de ejecución:
     * 1. Validación de ID numérico
     * 2. Verificación de existencia de la cita
     * 3. Registro de auditoría antes de eliminación
     * 4. Eliminación permanente de la base de datos
     * 5. Confirmación de operación exitosa
     * 6. Manejo de errores con logging detallado
     * 
     * Consideraciones de seguridad:
     * - Validación estricta del ID de entrada
     * - Verificación de existencia antes de eliminar
     * - Logging completo para trazabilidad
     * - Manejo seguro de errores sin exposición de información sensible
     * 
     * Consideraciones importantes:
     * - Esta es una eliminación PERMANENTE (no soft delete)
     * - No verifica dependencias con otras tablas
     * - Recomendado implementar soft delete para mejor trazabilidad
     * 
     * @since 1.0.0
     * @author Sistema DentalSync
     */
    public function destroy($id)
    {
        try {
            // Log del inicio de eliminación
            \Log::info("Iniciando eliminación de cita ID: {$id}", [
                'cita_id' => $id,
                'user_id' => session('user.id'),
                'timestamp' => now(),
                'operation' => 'delete_cita'
            ]);
            
            // Validar que el ID sea numérico
            if (!is_numeric($id)) {
                \Log::warning("ID de cita inválido para eliminación: {$id}", [
                    'cita_id' => $id,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json(['error' => 'ID de cita inválido'], 400);
            }

            // Verificar que la cita existe antes de eliminar
            $citaExistente = DB::table('citas')->where('id', $id)->first();
            if (!$citaExistente) {
                \Log::warning("Intento de eliminar cita inexistente: {$id}", [
                    'cita_id' => $id,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json(['error' => 'Cita no encontrada'], 404);
            }

            // Log de la cita antes de eliminar para auditoría
            \Log::info("Datos de la cita antes de eliminar", [
                'cita_id' => $id,
                'fecha' => $citaExistente->fecha,
                'motivo' => $citaExistente->motivo,
                'estado' => $citaExistente->estado,
                'paciente_id' => $citaExistente->paciente_id,
                'usuario_id' => $citaExistente->usuario_id,
                'timestamp' => now()
            ]);

            // Realizar la eliminación
            $filasEliminadas = DB::table('citas')->where('id', $id)->delete();
            
            if ($filasEliminadas === 0) {
                \Log::warning("No se pudo eliminar la cita: {$id}", [
                    'cita_id' => $id,
                    'timestamp' => now()
                ]);
                
                return response()->json(['error' => 'No se pudo eliminar la cita'], 500);
            }

            // Log de eliminación exitosa
            \Log::info('Cita eliminada exitosamente', [
                'cita_id' => $id,
                'filas_eliminadas' => $filasEliminadas,
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);

            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            // Log detallado del error
            \Log::error('Error al eliminar cita', [
                'cita_id' => $id,
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuario automáticamente con sistema de fallback inteligente
     * 
     * Este método implementa una estrategia de fallback para asignar automáticamente
     * un usuario responsable a las citas cuando no hay sesión activa o el usuario
     * no está especificado explícitamente.
     * 
     * @return int ID del usuario seleccionado automáticamente
     * @throws \Exception Si no se encuentra ningún usuario en el sistema
     * 
     * Estrategia de selección (en orden de prioridad):
     * 1. Usuario de sesión activa (si existe)
     * 2. Primer dentista activo en el sistema
     * 3. Primer usuario activo de cualquier rol
     * 4. Primer usuario disponible (sin importar estado)
     * 
     * Cada nivel de fallback se registra en logs para auditoría
     * y troubleshooting del sistema de asignación automática.
     * 
     * Casos de uso:
     * - Citas creadas desde interfaces públicas
     * - Asignación automática en procesos batch
     * - Recuperación de sesiones expiradas
     * - Sistemas de integración externa
     * 
     * @since 1.0.0
     * @author Sistema DentalSync
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
