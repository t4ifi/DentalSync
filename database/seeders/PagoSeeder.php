<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pago;
use App\Models\DetallePago;
use App\Models\CuotaPago;
use App\Models\Paciente;
use App\Models\Usuario;
use Carbon\Carbon;

class PagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunos pacientes y usuarios existentes
        $pacientes = Paciente::take(3)->get();
        $usuarios = Usuario::take(2)->get();

        if ($pacientes->isEmpty() || $usuarios->isEmpty()) {
            $this->command->info('No hay pacientes o usuarios suficientes para crear pagos de prueba.');
            return;
        }

        foreach ($pacientes as $index => $paciente) {
            // Pago único completado
            $pagoUnico = Pago::create([
                'paciente_id' => $paciente->id,
                'usuario_id' => $usuarios->first()->id,
                'fecha_pago' => Carbon::now()->subDays($index * 5),
                'monto_total' => 150.00,
                'descripcion' => 'Limpieza dental',
                'modalidad_pago' => 'pago_unico',
                'monto_pagado' => 150.00,
                'saldo_restante' => 0.00,
                'total_cuotas' => 1,
                'estado_pago' => 'pagado_completo',
                'observaciones' => 'Pago completo al contado'
            ]);

            // Detalle del pago único
            DetallePago::create([
                'pago_id' => $pagoUnico->id,
                'monto_parcial' => 150.00,
                'fecha_pago' => $pagoUnico->fecha_pago,
                'descripcion' => 'Pago completo en efectivo',
                'tipo_pago' => 'pago_completo',
                'usuario_id' => $usuarios->first()->id
            ]);

            // Pago en cuotas fijas (parcialmente pagado)
            if ($index < 2) {
                $pagoCuotas = Pago::create([
                    'paciente_id' => $paciente->id,
                    'usuario_id' => $usuarios->first()->id,
                    'fecha_pago' => Carbon::now()->subDays($index * 3),
                    'monto_total' => 1200.00,
                    'descripcion' => 'Tratamiento de ortodoncia',
                    'modalidad_pago' => 'cuotas_fijas',
                    'monto_pagado' => 400.00,
                    'saldo_restante' => 800.00,
                    'total_cuotas' => 6,
                    'estado_pago' => 'pagado_parcial',
                    'observaciones' => 'Pago en 6 cuotas de $200 c/u'
                ]);

                // Crear cuotas fijas
                $this->crearCuotasFijas($pagoCuotas, 6);
                
                // Primeros 2 pagos realizados
                for ($i = 1; $i <= 2; $i++) {
                    DetallePago::create([
                        'pago_id' => $pagoCuotas->id,
                        'monto_parcial' => 200.00,
                        'fecha_pago' => Carbon::now()->subDays(($index * 3) - $i),
                        'descripcion' => "Cuota #{$i} de 6",
                        'tipo_pago' => 'cuota_fija',
                        'numero_cuota' => $i,
                        'usuario_id' => $usuarios->random()->id
                    ]);
                    
                    // Marcar cuota como pagada
                    \DB::table('cuotas_pago')
                        ->where('pago_id', $pagoCuotas->id)
                        ->where('numero_cuota', $i)
                        ->update(['estado' => 'pagada']);
                }
            }

            // Pago en cuotas variables (solo para el primer paciente)
            if ($index === 0) {
                $pagoVariable = Pago::create([
                    'paciente_id' => $paciente->id,
                    'usuario_id' => $usuarios->first()->id,
                    'fecha_pago' => Carbon::now()->subDays(10),
                    'monto_total' => 2500.00,
                    'descripcion' => 'Implante dental',
                    'modalidad_pago' => 'cuotas_variables',
                    'monto_pagado' => 1000.00,
                    'saldo_restante' => 1500.00,
                    'total_cuotas' => 4,
                    'estado_pago' => 'pagado_parcial',
                    'observaciones' => 'Pago inicial de $1000, resto en cuotas variables'
                ]);

                // Pago inicial
                DetallePago::create([
                    'pago_id' => $pagoVariable->id,
                    'monto_parcial' => 1000.00,
                    'fecha_pago' => Carbon::now()->subDays(10),
                    'descripcion' => 'Pago inicial del implante',
                    'tipo_pago' => 'pago_variable',
                    'usuario_id' => $usuarios->first()->id
                ]);
            }
        }

        $this->command->info('Datos de prueba para pagos creados exitosamente.');
    }
    
    /**
     * Crear cuotas fijas para un pago
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

            \App\Models\CuotaPago::create([
                'pago_id' => $pago->id,
                'numero_cuota' => $i,
                'monto' => $monto,
                'fecha_vencimiento' => $fechaVencimiento->copy()->addMonths($i - 1),
                'estado' => 'pendiente'
            ]);
        }
    }
}
