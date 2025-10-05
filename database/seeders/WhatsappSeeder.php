<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\WhatsappConversacion;
use App\Models\WhatsappMensaje;
use App\Models\WhatsappPlantilla;

class WhatsappSeeder extends Seeder
{
    public function run()
    {
        // Verificar que existan pacientes
        $pacientes = Paciente::limit(3)->get();
        
        if ($pacientes->count() == 0) {
            // Crear algunos pacientes de prueba
            $pacientes = collect([
                Paciente::create([
                    'nombre_completo' => 'María González',
                    'telefono' => '+57 300 123 4567',
                    'fecha_nacimiento' => '1985-03-15',
                    'motivo_consulta' => 'Limpieza dental',
                    'alergias' => 'Ninguna',
                    'email' => 'maria@email.com',
                    'contacto_emergencia' => 'Juan González - 301 555 1234'
                ]),
                Paciente::create([
                    'nombre_completo' => 'Juan Pérez',
                    'telefono' => '+57 301 987 6543',
                    'fecha_nacimiento' => '1990-07-22',
                    'motivo_consulta' => 'Dolor de muela',
                    'alergias' => 'Penicilina',
                    'email' => 'juan@email.com',
                    'contacto_emergencia' => 'Ana Pérez - 302 555 5678'
                ]),
                Paciente::create([
                    'nombre_completo' => 'Ana Martínez',
                    'telefono' => '+57 302 456 7890',
                    'fecha_nacimiento' => '1988-11-10',
                    'motivo_consulta' => 'Ortodoncia',
                    'alergias' => 'Ninguna',
                    'email' => 'ana@email.com',
                    'contacto_emergencia' => 'Carlos Martínez - 303 555 9012'
                ])
            ]);
        }

        // Crear conversaciones de WhatsApp
        foreach ($pacientes as $paciente) {
            $conversacion = WhatsappConversacion::create([
                'paciente_id' => $paciente->id,
                'telefono' => $paciente->telefono,
                'nombre_contacto' => $paciente->nombre_completo,
                'estado' => 'activa',
                'ultimo_mensaje_texto' => 'Hola, ¿cómo estás?',
                'ultimo_mensaje_fecha' => now()->subMinutes(rand(5, 60)),
                'ultimo_mensaje_propio' => false,
                'mensajes_no_leidos' => rand(0, 3)
            ]);

            // Crear algunos mensajes para cada conversación
            WhatsappMensaje::create([
                'conversacion_id' => $conversacion->id,
                'contenido' => '🦷 Hola ' . $paciente->nombre_completo . ', te recordamos tu próxima cita dental.',
                'es_propio' => true,
                'estado' => 'leido',
                'fecha_envio' => now()->subHours(2),
                'metadata' => ['enviado_desde' => 'sistema_automatico']
            ]);

            WhatsappMensaje::create([
                'conversacion_id' => $conversacion->id,
                'contenido' => 'Perfecto, gracias por el recordatorio. ¿A qué hora es?',
                'es_propio' => false,
                'estado' => 'leido',
                'fecha_envio' => now()->subHours(1),
                'metadata' => ['recibido_via' => 'whatsapp_business']
            ]);

            WhatsappMensaje::create([
                'conversacion_id' => $conversacion->id,
                'contenido' => 'Tu cita es a las 10:00 AM. ¡Te esperamos!',
                'es_propio' => true,
                'estado' => 'entregado',
                'fecha_envio' => now()->subMinutes(30),
                'metadata' => ['enviado_desde' => 'panel_admin']
            ]);
        }

        // Crear plantillas de ejemplo
        $plantillas = [
            [
                'nombre' => 'Recordatorio de Cita',
                'categoria' => 'recordatorio',
                'contenido' => '🦷 Hola {nombre}, te recordamos tu cita dental para el {fecha} a las {hora}. ¿Confirmas tu asistencia?',
                'variables_detectadas' => json_encode(['nombre', 'fecha', 'hora']),
                'activa' => true,
                'usos' => 45,
                'creado_por' => 1
            ],
            [
                'nombre' => 'Confirmación de Cita',
                'categoria' => 'confirmacion',
                'contenido' => '✅ Perfecto {nombre}, tu cita está confirmada para {fecha} a las {hora}. ¡Te esperamos en {clinica}!',
                'variables_detectadas' => json_encode(['nombre', 'fecha', 'hora', 'clinica']),
                'activa' => true,
                'usos' => 32,
                'creado_por' => 1
            ],
            [
                'nombre' => 'Recordatorio de Pago',
                'categoria' => 'pago',
                'contenido' => '💰 Hola {nombre}, tienes un saldo pendiente de tu tratamiento dental. ¿Podrías regularizarlo? Cualquier consulta, responde este mensaje.',
                'variables_detectadas' => json_encode(['nombre']),
                'activa' => true,
                'usos' => 18,
                'creado_por' => 1
            ]
        ];

        foreach ($plantillas as $plantillaData) {
            WhatsappPlantilla::create($plantillaData);
        }

        echo "Datos de WhatsApp creados exitosamente:\n";
        echo "- " . $pacientes->count() . " pacientes\n";
        echo "- " . WhatsappConversacion::count() . " conversaciones\n";
        echo "- " . WhatsappMensaje::count() . " mensajes\n";
        echo "- " . WhatsappPlantilla::count() . " plantillas\n";
    }
}
