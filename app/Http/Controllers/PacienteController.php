<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;

/**
 * ============================================================================
 * CONTROLADOR DE PACIENTES - DENTALSYNC
 * ============================================================================
 * 
 * Este controlador gestiona todas las operaciones CRUD relacionadas con pacientes
 * en el sistema DentalSync. Proporciona endpoints para crear, leer, actualizar
 * y eliminar registros de pacientes.
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * - Gestión completa de pacientes (CRUD)
 * - Validación robusta de datos de entrada
 * - Logging de operaciones para auditoría
 * - Manejo de errores con respuestas JSON consistentes
 * - Búsqueda y filtrado de pacientes
 * 
 * ENDPOINTS DISPONIBLES:
 * - GET    /api/pacientes      : Lista todos los pacientes
 * - GET    /api/pacientes/{id} : Obtiene un paciente específico
 * - POST   /api/pacientes      : Crea un nuevo paciente
 * - PUT    /api/pacientes/{id} : Actualiza un paciente existente
 * 
 * VALIDACIONES IMPLEMENTADAS:
 * - Nombre completo: requerido, máximo 255 caracteres
 * - Teléfono: requerido para registro, máximo 20 caracteres
 * - Fecha de nacimiento: fecha válida, anterior a hoy
 * - Motivo de consulta: requerido para registro, máximo 1000 caracteres
 * - Alergias y observaciones: opcionales, máximo 1000 caracteres
 * 
 * @package App\Http\Controllers
 * @author DentalSync Development Team
 * @version 2.1
 * @since 2025-09-04
 */
class PacienteController extends Controller
{
    /**
     * Lista todos los pacientes registrados en el sistema.
     * 
     * Este método obtiene todos los registros de pacientes de la base de datos
     * y los retorna en formato JSON. Incluye manejo de errores y logging.
     * 
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception En caso de error en la base de datos
     * 
     * @api GET /api/pacientes
     * @apiSuccess {Array} data Lista de pacientes
     * @apiError {String} error Mensaje de error en caso de falla
     */
    public function index()
    {
        try {
            // Log de operación para auditoría
            \Log::info('Consultando lista de pacientes', [
                'timestamp' => now(),
                'user_id' => session('user.id'),
                'operation' => 'index_pacientes'
            ]);
            
            // Obtener todos los pacientes de la base de datos
            $pacientes = \DB::table('pacientes')->get();
            
            // Log de resultado exitoso
            \Log::info('Lista de pacientes obtenida exitosamente', [
                'total_pacientes' => $pacientes->count(),
                'timestamp' => now()
            ]);
            
            return response()->json($pacientes);
        } catch (\Exception $e) {
            // Log detallado del error
            \Log::error("Error al obtener pacientes: " . $e->getMessage(), [
                'exception' => $e,
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);
            
            return response()->json(['error' => 'Error al cargar pacientes'], 500);
        }
    }

    /**
     * Obtiene la información detallada de un paciente específico.
     * 
     * Este método busca un paciente por su ID y retorna toda su información.
     * Incluye validación de existencia y manejo detallado de errores.
     * 
     * @param int $id ID del paciente a buscar
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception En caso de error en la base de datos
     * 
     * @api GET /api/pacientes/{id}
     * @apiParam {Number} id ID único del paciente
     * @apiSuccess {Object} data Información completa del paciente
     * @apiError {String} error Mensaje de error (404 si no existe, 500 si error del servidor)
     */
    public function show($id)
    {
        try {
            // Log de la operación de búsqueda
            \Log::info("Buscando paciente con ID: {$id}", [
                'paciente_id' => $id,
                'user_id' => session('user.id'),
                'timestamp' => now(),
                'operation' => 'show_paciente'
            ]);
            
            // Validar que el ID sea numérico
            if (!is_numeric($id)) {
                \Log::warning("ID de paciente inválido: {$id}", [
                    'paciente_id' => $id,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json(['error' => 'ID de paciente inválido'], 400);
            }
            
            // Buscar el paciente en la base de datos
            $paciente = \DB::table('pacientes')->where('id', $id)->first();
            
            // Verificar si el paciente existe
            if (!$paciente) {
                \Log::error("Paciente no encontrado con ID: {$id}", [
                    'paciente_id' => $id,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json(['error' => 'Paciente no encontrado'], 404);
            }
            
            // Log de paciente encontrado exitosamente
            \Log::info("Paciente encontrado: {$paciente->nombre_completo}", [
                'paciente_id' => $id,
                'nombre_completo' => $paciente->nombre_completo,
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json($paciente);
        } catch (\Exception $e) {
            // Log detallado del error
            \Log::error("Error al buscar paciente: {$e->getMessage()}", [
                'paciente_id' => $id,
                'exception' => $e,
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Actualiza la información de un paciente existente.
     * 
     * Este método valida los datos de entrada, busca el paciente por ID
     * y actualiza la información proporcionada. Incluye validación completa
     * de todos los campos y manejo de errores detallado.
     * 
     * @param \Illuminate\Http\Request $request Datos del paciente a actualizar
     * @param int $id ID del paciente a actualizar
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException En caso de datos inválidos
     * @throws \Exception En caso de error en la base de datos
     * 
     * @api PUT /api/pacientes/{id}
     * @apiParam {Number} id ID único del paciente
     * @apiParam {String} nombre_completo Nombre completo del paciente (máx. 255 caracteres)
     * @apiParam {String} [telefono] Número de teléfono (máx. 20 caracteres)
     * @apiParam {Date} [fecha_nacimiento] Fecha de nacimiento (anterior a hoy)
     * @apiParam {Date} [ultima_visita] Fecha de la última visita
     * @apiSuccess {Object} data Información actualizada del paciente
     * @apiError {Object} details Errores de validación específicos
     * @apiError {String} error Mensaje de error general
     */
    public function update(Request $request, $id)
    {
        try {
            // Log del inicio de la operación de actualización
            \Log::info("Iniciando actualización de paciente ID: {$id}", [
                'paciente_id' => $id,
                'user_id' => session('user.id'),
                'timestamp' => now(),
                'operation' => 'update_paciente',
                'input_data' => $request->only(['nombre_completo', 'telefono', 'fecha_nacimiento', 'ultima_visita'])
            ]);
            
            // Validar datos de entrada con reglas específicas
            $validated = $request->validate([
                'nombre_completo' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
                'fecha_nacimiento' => 'nullable|date|before:today',
                'ultima_visita' => 'nullable|date',
            ], [
                // Mensajes de error personalizados en español
                'nombre_completo.required' => 'El nombre completo es obligatorio',
                'nombre_completo.string' => 'El nombre debe ser un texto válido',
                'nombre_completo.max' => 'El nombre no puede tener más de 255 caracteres',
                'nombre_completo.regex' => 'El nombre solo puede contener letras y espacios',
                'telefono.string' => 'El teléfono debe ser un texto válido',
                'telefono.max' => 'El teléfono no puede tener más de 20 caracteres',
                'telefono.regex' => 'El teléfono solo puede contener números, espacios y símbolos (+, -, (), )',
                'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida',
                'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
                'ultima_visita.date' => 'La fecha de última visita debe ser una fecha válida'
            ]);

            // Buscar el paciente a actualizar
            $paciente = Paciente::findOrFail($id);
            
            // Log de paciente encontrado
            \Log::info("Paciente encontrado para actualización: {$paciente->nombre_completo}", [
                'paciente_id' => $id,
                'nombre_actual' => $paciente->nombre_completo,
                'timestamp' => now()
            ]);
            
            // Almacenar datos originales para auditoría
            $datosOriginales = $paciente->toArray();
            
            // Actualizar campos proporcionados
            $paciente->update($validated);
            
            // Obtener el registro actualizado
            $pacienteActualizado = $paciente->fresh();
            
            // Log de actualización exitosa con detalles de cambios
            \Log::info('Paciente actualizado exitosamente', [
                'paciente_id' => $id,
                'user_id' => session('user.id'),
                'datos_originales' => $datosOriginales,
                'datos_nuevos' => $pacienteActualizado->toArray(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente actualizado exitosamente',
                'paciente' => $pacienteActualizado
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log de error de validación
            \Log::warning('Error de validación al actualizar paciente', [
                'paciente_id' => $id,
                'user_id' => session('user.id'),
                'validation_errors' => $e->errors(),
                'input_data' => $request->all(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'error' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            // Log detallado del error del sistema
            \Log::error('Error al actualizar paciente', [
                'paciente_id' => $id,
                'user_id' => session('user.id'),
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'input_data' => $request->all(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'error' => 'Error al actualizar paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea un nuevo registro de paciente en el sistema.
     * 
     * Este método valida todos los datos requeridos para crear un nuevo paciente,
     * incluyendo información personal, contacto y médica básica. Asigna automáticamente
     * la fecha de registro y última visita.
     * 
     * @param \Illuminate\Http\Request $request Datos del nuevo paciente
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException En caso de datos inválidos
     * @throws \Exception En caso de error en la base de datos
     * 
     * @api POST /api/pacientes
     * @apiParam {String} nombre_completo Nombre completo del paciente (requerido, máx. 255 caracteres)
     * @apiParam {String} telefono Número de teléfono (requerido, máx. 20 caracteres)
     * @apiParam {Date} fecha_nacimiento Fecha de nacimiento (requerida, anterior a hoy)
     * @apiParam {String} motivo_consulta Motivo de la consulta inicial (requerido, máx. 1000 caracteres)
     * @apiParam {String} [alergias] Alergias conocidas (opcional, máx. 1000 caracteres)
     * @apiParam {String} [observaciones] Observaciones médicas (opcional, máx. 1000 caracteres)
     * @apiSuccess {Object} paciente Información del paciente creado
     * @apiError {Object} details Errores de validación específicos
     * @apiError {String} message Mensaje de error general
     */
    public function store(Request $request)
    {
        try {
            // Log del inicio de creación de paciente
            \Log::info('Iniciando creación de nuevo paciente', [
                'user_id' => session('user.id'),
                'timestamp' => now(),
                'operation' => 'store_paciente',
                'input_data' => $request->only(['nombre_completo', 'telefono', 'fecha_nacimiento', 'motivo_consulta'])
            ]);
            
            // Validar datos de entrada con reglas exhaustivas
            $validated = $request->validate([
                'nombre_completo' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'telefono' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
                'fecha_nacimiento' => 'required|date|before:today',
                'motivo_consulta' => 'required|string|max:1000',
                'alergias' => 'nullable|string|max:1000',
                'observaciones' => 'nullable|string|max:1000',
            ], [
                // Mensajes personalizados en español
                'nombre_completo.required' => 'El nombre completo es obligatorio',
                'nombre_completo.string' => 'El nombre debe ser un texto válido',
                'nombre_completo.max' => 'El nombre no puede tener más de 255 caracteres',
                'nombre_completo.regex' => 'El nombre solo puede contener letras y espacios',
                'telefono.required' => 'El teléfono es obligatorio',
                'telefono.string' => 'El teléfono debe ser un texto válido',
                'telefono.max' => 'El teléfono no puede tener más de 20 caracteres',
                'telefono.regex' => 'El teléfono solo puede contener números, espacios y símbolos (+, -, (), )',
                'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
                'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida',
                'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
                'motivo_consulta.required' => 'El motivo de consulta es obligatorio',
                'motivo_consulta.max' => 'El motivo de consulta no puede tener más de 1000 caracteres',
                'alergias.max' => 'Las alergias no pueden tener más de 1000 caracteres',
                'observaciones.max' => 'Las observaciones no pueden tener más de 1000 caracteres',
            ]);

            // Agregar campos automáticos del sistema
            $validated['ultima_visita'] = now()->toDateString();
            $validated['fecha_registro'] = now()->toDateString();

            // Verificar si ya existe un paciente con el mismo nombre y teléfono
            $pacienteExistente = Paciente::where('nombre_completo', $validated['nombre_completo'])
                                        ->where('telefono', $validated['telefono'])
                                        ->first();
            
            if ($pacienteExistente) {
                \Log::warning('Intento de crear paciente duplicado', [
                    'nombre_completo' => $validated['nombre_completo'],
                    'telefono' => $validated['telefono'],
                    'paciente_existente_id' => $pacienteExistente->id,
                    'user_id' => session('user.id'),
                    'timestamp' => now()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un paciente con el mismo nombre y teléfono',
                    'paciente_existente' => $pacienteExistente
                ], 409);
            }

            // Crear el nuevo paciente
            $paciente = Paciente::create($validated);
            
            // Registrar en logs de auditoría
            \Log::info('Nuevo paciente creado exitosamente', [
                'paciente_id' => $paciente->id,
                'nombre' => $paciente->nombre_completo,
                'telefono' => $paciente->telefono,
                'motivo_consulta' => $paciente->motivo_consulta,
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente registrado exitosamente',
                'paciente' => $paciente
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log de errores de validación
            \Log::warning('Error de validación al crear paciente', [
                'validation_errors' => $e->errors(),
                'input_data' => $request->all(),
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'details' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            // Log detallado de errores del sistema
            \Log::error('Error al crear paciente', [
                'exception' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'input_data' => $request->all(),
                'user_id' => session('user.id'),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al crear paciente'
            ], 500);
        }
    }
}
