<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pago;
use App\Models\DetallePago;
use App\Models\CuotaPago;
use App\Models\Paciente;
use App\Models\Usuario;

class PagoController extends Controller
{
    /**
     * Obtener el usuario autenticado desde la sesión
     */
    private function getUsuarioAutenticado()
    {
        $usuarioId = session('usuario_id');
        if (!$usuarioId) {
            throw new \Exception('Usuario no autenticado');
        }
        
        $usuario = Usuario::find($usuarioId);
        if (!$usuario) {
            throw new \Exception('Usuario no encontrado');
        }
        
        return $usuario;
    }

    /**
     * Obtener lista de pacientes para pagos (filtrados por dentista si es necesario)
     */
    public function getPacientes()
    {
        try {
            // Intentar obtener el usuario autenticado, pero no es requerido para este endpoint
            try {
                $usuario = $this->getUsuarioAutenticado();
            } catch (\Exception $e) {
                // Si no hay sesión, devolver todos los pacientes
                \Log::info('No hay sesión activa para getPacientes, devolviendo todos los pacientes');
            }
            
            // Por ahora, permitir que todos vean todos los pacientes
            // Los pagos sí se filtrarán por usuario
            $pacientes = DB::table('pacientes')
                ->select('id', 'nombre_completo')
                ->orderBy('nombre_completo')
                ->get();

            return response()->json([
                'success' => true,
                'pacientes' => $pacientes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar pacientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar nuevo pago
     */
    public function registrarPago(Request $request)
    {
        try {
            $request->validate([
                'paciente_id' => 'required|exists:pacientes,id',
                'monto_total' => 'required|numeric|min:0.01',
                'descripcion' => 'required|string|max:500',
                'modalidad_pago' => 'required|in:pago_unico,cuotas_fijas,cuotas_variables',
                'total_cuotas' => 'nullable|integer|min:1|max:60',
                'fecha_pago' => 'required|date',
                'observaciones' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();
            
            // Intentar obtener usuario autenticado, usar fallback si no hay sesión
            try {
                $usuario = $this->getUsuarioAutenticado();
            } catch (\Exception $e) {
                // Si no hay sesión, usar el primer dentista disponible como fallback
                \Log::warning('No hay sesión activa para registrarPago, usando fallback');
                $usuario = Usuario::where('rol', 'dentista')->first();
                if (!$usuario) {
                    throw new \Exception('No hay dentistas disponibles en el sistema');
                }
            }

            // Crear el pago principal
            $pago = new Pago();
            $pago->paciente_id = $request->paciente_id;
            $pago->usuario_id = $usuario->id; // Usar el usuario autenticado o fallback
            $pago->fecha_pago = $request->fecha_pago;
            $pago->monto_total = $request->monto_total;
            $pago->descripcion = $request->descripcion;
            $pago->modalidad_pago = $request->modalidad_pago;
            $pago->observaciones = $request->observaciones;

            // Configurar según modalidad
            switch ($request->modalidad_pago) {
                case 'pago_unico':
                    $pago->monto_pagado = $request->monto_total;
                    $pago->saldo_restante = 0;
                    $pago->estado_pago = 'pagado_completo';
                    break;

                case 'cuotas_fijas':
                    $pago->total_cuotas = $request->total_cuotas;
                    $pago->monto_pagado = 0;
                    $pago->saldo_restante = $request->monto_total;
                    $pago->estado_pago = 'pendiente';
                    break;

                case 'cuotas_variables':
                    $pago->monto_pagado = 0;
                    $pago->saldo_restante = $request->monto_total;
                    $pago->estado_pago = 'pendiente';
                    break;
            }

            $pago->save();

            // Registrar detalle del pago
            if ($request->modalidad_pago === 'pago_unico') {
                DetallePago::create([
                    'pago_id' => $pago->id,
                    'fecha_pago' => $request->fecha_pago,
                    'monto_parcial' => $request->monto_total,
                    'descripcion' => 'Pago único completo',
                    'tipo_pago' => 'pago_completo',
                    'usuario_id' => $usuario->id
                ]);
            }

            // Crear cuotas fijas si corresponde
            if ($request->modalidad_pago === 'cuotas_fijas') {
                $this->crearCuotasFijas($pago, $request->total_cuotas);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente',
                'pago' => $pago->load('paciente')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver pagos de un paciente específico
     */
    public function verPagosPaciente($pacienteId)
    {
        try {
            // Intentar obtener usuario autenticado, pero no es requerido para este endpoint
            $usuario = null;
            try {
                $usuario = $this->getUsuarioAutenticado();
            } catch (\Exception $e) {
                // Si no hay sesión, permitir ver todos los pagos
                \Log::info('No hay sesión activa para verPagosPaciente, mostrando todos los pagos');
            }
            
            $paciente = DB::table('pacientes')
                ->where('id', $pacienteId)
                ->first();

            if (!$paciente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente no encontrado'
                ], 404);
            }

            // Obtener todos los pagos del paciente (filtrados por usuario si es dentista)
            $query = DB::table('pagos as p')
                ->leftJoin('usuarios as u', 'p.usuario_id', '=', 'u.id')
                ->where('p.paciente_id', $pacienteId);
                
            // Si es dentista y hay sesión activa, solo ver sus propios pagos
            if ($usuario && $usuario->rol === 'dentista') {
                $query->where('p.usuario_id', $usuario->id);
            }
            
            $pagos = $query->select(
                    'p.*',
                    'u.nombre as nombre_usuario'
                )
                ->orderBy('p.created_at', 'desc')
                ->get();

            // Obtener detalles de pagos para cada pago
            foreach ($pagos as $pago) {
                $detalles = DB::table('detalle_pagos as dp')
                    ->leftJoin('usuarios as u', 'dp.usuario_id', '=', 'u.id')
                    ->where('dp.pago_id', $pago->id)
                    ->select(
                        'dp.*',
                        'u.nombre as nombre_usuario'
                    )
                    ->orderBy('dp.fecha_pago', 'desc')
                    ->get();

                $pago->detalles_pagos = $detalles;

                // Obtener cuotas si es pago en cuotas fijas
                if ($pago->modalidad_pago === 'cuotas_fijas') {
                    $cuotas = DB::table('cuotas_pago')
                        ->where('pago_id', $pago->id)
                        ->orderBy('numero_cuota')
                        ->get();
                    $pago->cuotas = $cuotas;
                }
            }

            // Calcular totales
            $totales = [
                'monto_total_tratamientos' => $pagos->sum('monto_total'),
                'monto_total_pagado' => $pagos->sum('monto_pagado'),
                'saldo_total_restante' => $pagos->sum('saldo_restante'),
                'tratamientos_activos' => $pagos->where('estado_pago', '!=', 'pagado_completo')->count(),
                'tratamientos_completos' => $pagos->where('estado_pago', 'pagado_completo')->count()
            ];

            return response()->json([
                'success' => true,
                'paciente' => $paciente,
                'pagos' => $pagos,
                'totales' => $totales
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar pago de cuota (para cuotas fijas o variables)
     */
    public function registrarPagoCuota(Request $request)
    {
        try {
            $request->validate([
                'pago_id' => 'required|exists:pagos,id',
                'monto_cuota' => 'required|numeric|min:0.01',
                'fecha_pago' => 'required|date',
                'descripcion' => 'nullable|string|max:500',
                'numero_cuota' => 'nullable|integer' // Para cuotas fijas
            ]);

            DB::beginTransaction();
            
            // Intentar obtener usuario autenticado, usar fallback si no hay sesión
            try {
                $usuario = $this->getUsuarioAutenticado();
            } catch (\Exception $e) {
                // Si no hay sesión, usar el primer dentista disponible como fallback
                \Log::warning('No hay sesión activa para registrarPagoCuota, usando fallback');
                $usuario = Usuario::where('rol', 'dentista')->first();
                if (!$usuario) {
                    throw new \Exception('No hay dentistas disponibles en el sistema');
                }
            }

            $pago = Pago::findOrFail($request->pago_id);

            // Validar que no se exceda el monto total
            $nuevoMontoPagado = $pago->monto_pagado + $request->monto_cuota;
            if ($nuevoMontoPagado > $pago->monto_total) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto excede el saldo restante del tratamiento'
                ], 400);
            }

            // Crear detalle del pago
            $detallePago = DetallePago::create([
                'pago_id' => $pago->id,
                'fecha_pago' => $request->fecha_pago,
                'monto_parcial' => $request->monto_cuota,
                'descripcion' => $request->descripcion ?? 'Pago de cuota',
                'tipo_pago' => $pago->modalidad_pago === 'cuotas_fijas' ? 'cuota_fija' : 'pago_variable',
                'numero_cuota' => $request->numero_cuota,
                'usuario_id' => $usuario->id
            ]);

            // Actualizar el pago principal
            $pago->monto_pagado = $nuevoMontoPagado;
            $pago->actualizarEstado();

            // Si es cuota fija, marcar la cuota como pagada
            if ($pago->modalidad_pago === 'cuotas_fijas' && $request->numero_cuota) {
                DB::table('cuotas_pago')
                    ->where('pago_id', $pago->id)
                    ->where('numero_cuota', $request->numero_cuota)
                    ->update(['estado' => 'pagada']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago de cuota registrado exitosamente',
                'pago' => $pago->fresh(),
                'detalle_pago' => $detallePago
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar pago de cuota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de pagos para dashboard
     */
    public function getResumenPagos()
    {
        try {
            // Intentar obtener el usuario autenticado, pero no es requerido para este endpoint
            $usuario = null;
            try {
                $usuario = $this->getUsuarioAutenticado();
            } catch (\Exception $e) {
                // Si no hay sesión, devolver resumen general
                \Log::info('No hay sesión activa para getResumenPagos, devolviendo resumen general');
            }
            
            // Base queries que se filtrarán por usuario si es dentista
            $pagosPendientesQuery = DB::table('pagos')
                ->where('estado_pago', '!=', 'pagado_completo');
                
            $pagosMesQuery = DB::table('detalle_pagos as dp')
                ->join('pagos as p', 'dp.pago_id', '=', 'p.id')
                ->whereYear('dp.fecha_pago', date('Y'))
                ->whereMonth('dp.fecha_pago', date('m'));
                
            $pacientesDeudaQuery = DB::table('pagos')
                ->where('estado_pago', '!=', 'pagado_completo');
                
            $cuotasVencidasQuery = DB::table('cuotas_pago as cp')
                ->join('pagos as p', 'cp.pago_id', '=', 'p.id')
                ->where('cp.estado', 'pendiente')
                ->where('cp.fecha_vencimiento', '<', date('Y-m-d'));
            
            // Si es dentista, filtrar solo sus datos
            if ($usuario && $usuario->rol === 'dentista') {
                $pagosPendientesQuery->where('usuario_id', $usuario->id);
                $pagosMesQuery->where('p.usuario_id', $usuario->id);
                $pacientesDeudaQuery->where('usuario_id', $usuario->id);
                $cuotasVencidasQuery->where('p.usuario_id', $usuario->id);
            }
            
            $resumen = [
                'total_pagos_pendientes' => $pagosPendientesQuery->sum('saldo_restante'),
                'total_pagos_mes' => $pagosMesQuery->sum('dp.monto_parcial'),
                'pacientes_con_deuda' => $pacientesDeudaQuery->distinct('paciente_id')->count('paciente_id'),
                'cuotas_vencidas' => $cuotasVencidasQuery->count()
            ];

            return response()->json([
                'success' => true,
                'resumen' => $resumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método privado para crear cuotas fijas
     */
    private function crearCuotasFijas($pago, $totalCuotas)
    {
        $montoCuota = round($pago->monto_total / $totalCuotas, 2);
        $fechaVencimiento = now();

        for ($i = 1; $i <= $totalCuotas; $i++) {
            // Ajustar la última cuota para evitar diferencias por redondeo
            $monto = ($i === $totalCuotas) 
                ? $pago->monto_total - ($montoCuota * ($totalCuotas - 1))
                : $montoCuota;

            CuotaPago::create([
                'pago_id' => $pago->id,
                'numero_cuota' => $i,
                'monto' => $monto,
                'fecha_vencimiento' => $fechaVencimiento->copy()->addMonths($i - 1),
                'estado' => 'pendiente'
            ]);
        }
    }

    /**
     * Obtener información detallada de cuotas de un pago específico
     */
    public function getCuotasPago($pagoId)
    {
        try {
            // Intentar obtener usuario autenticado, usar fallback si no hay sesión
            try {
                $usuario = $this->getUsuarioAutenticado();
            } catch (\Exception $e) {
                // Si no hay sesión, usar el primer dentista disponible como fallback
                \Log::warning('No hay sesión activa para getCuotasPago, usando fallback');
                $usuario = Usuario::where('rol', 'dentista')->first();
                if (!$usuario) {
                    throw new \Exception('No hay dentistas disponibles en el sistema');
                }
            }

            $pago = Pago::findOrFail($pagoId);
            
            if ($pago->modalidad_pago !== 'cuotas_fijas') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pago no tiene cuotas fijas'
                ], 400);
            }
            
            $cuotas = DB::table('cuotas_pago')
                ->where('pago_id', $pagoId)
                ->orderBy('numero_cuota')
                ->get();
                
            return response()->json([
                'success' => true,
                'pago' => $pago,
                'cuotas' => $cuotas
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cuotas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inicializar sesión de prueba (temporal)
     */
    public function initSession()
    {
        try {
            // Para pruebas, usar el primer dentista disponible
            $usuario = Usuario::where('rol', 'dentista')->first();
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay dentistas disponibles'
                ], 404);
            }
            
            // Guardar en sesión
            session(['usuario_id' => $usuario->id]);
            
            return response()->json([
                'success' => true,
                'usuario' => $usuario,
                'message' => 'Sesión inicializada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al inicializar sesión: ' . $e->getMessage()
            ], 500);
        }
    }
}
