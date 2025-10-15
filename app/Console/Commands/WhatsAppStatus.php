<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WhatsAppStatus extends Command
{
    protected $signature = 'whatsapp:status';
    
    protected $description = 'Verificar el estado de configuración de WhatsApp Business API';

    public function handle()
    {
        $this->info('🦷 DentalSync - Verificación de WhatsApp Business API');
        $this->line('================================================');

        $whatsappService = new WhatsAppService();

        // Verificar configuración básica
        $this->line('📋 Verificando configuración...');
        
        $token = config('whatsapp.token');
        $phoneId = config('whatsapp.phone_number_id');
        $webhookToken = config('whatsapp.webhook_verify_token');
        
        if (empty($token)) {
            $this->error('❌ WHATSAPP_TOKEN no configurado en .env');
        } else {
            $this->info('✅ Token configurado (' . substr($token, 0, 10) . '...)');
        }
        
        if (empty($phoneId)) {
            $this->error('❌ WHATSAPP_PHONE_NUMBER_ID no configurado en .env');
        } else {
            $this->info('✅ Phone Number ID configurado (' . $phoneId . ')');
        }
        
        if (empty($webhookToken)) {
            $this->warn('⚠️ WHATSAPP_WEBHOOK_VERIFY_TOKEN no configurado');
        } else {
            $this->info('✅ Webhook verify token configurado');
        }

        // Verificar conectividad
        if ($whatsappService->isConfigured()) {
            $this->line('');
            $this->line('🌐 Verificando conectividad con WhatsApp API...');
            
            try {
                $phoneInfo = $whatsappService->getPhoneNumberInfo();
                $this->info('✅ Conectividad exitosa');
                $this->line('📞 Información del número:');
                $this->line('   Display: ' . ($phoneInfo['display_phone_number'] ?? 'N/A'));
                $this->line('   Status: ' . ($phoneInfo['code_verification_status'] ?? 'N/A'));
            } catch (\Exception $e) {
                $this->error('❌ Error de conectividad: ' . $e->getMessage());
            }
        } else {
            $this->error('❌ WhatsApp no está configurado correctamente');
        }

        // Verificar rutas
        $this->line('');
        $this->line('🛣️ Verificando rutas...');
        
        $baseUrl = config('app.url');
        $webhookUrl = $baseUrl . '/api/webhook/whatsapp';
        
        $this->info('✅ Webhook URL: ' . $webhookUrl);
        $this->line('   Configura esta URL en Meta for Developers');

        // Verificar base de datos
        $this->line('');
        $this->line('🗄️ Verificando tablas de base de datos...');
        
        try {
            $conversaciones = \DB::table('whatsapp_conversaciones')->count();
            $mensajes = \DB::table('whatsapp_mensajes')->count();
            $plantillas = \DB::table('whatsapp_plantillas')->count();
            
            $this->info('✅ Conversaciones: ' . $conversaciones);
            $this->info('✅ Mensajes: ' . $mensajes);
            $this->info('✅ Plantillas: ' . $plantillas);
        } catch (\Exception $e) {
            $this->error('❌ Error en base de datos: ' . $e->getMessage());
            $this->line('   Ejecuta: php artisan migrate');
        }

        // Resumen final
        $this->line('');
        $this->line('📋 RESUMEN:');
        
        if ($whatsappService->isConfigured()) {
            $this->info('🎉 WhatsApp está configurado y listo para usar');
            $this->line('');
            $this->line('📝 Próximos pasos:');
            $this->line('   1. Configura el webhook en Meta for Developers');
            $this->line('   2. Verifica tu número de teléfono');
            $this->line('   3. Prueba enviando un mensaje desde la interfaz');
        } else {
            $this->error('⚠️ WhatsApp necesita configuración');
            $this->line('');
            $this->line('📝 Para configurar:');
            $this->line('   1. Lee el archivo WHATSAPP_SETUP.md');
            $this->line('   2. Obtén credenciales de Meta for Developers');
            $this->line('   3. Agrega WHATSAPP_TOKEN y WHATSAPP_PHONE_NUMBER_ID al .env');
            $this->line('   4. Ejecuta: php artisan config:clear');
            $this->line('   5. Ejecuta nuevamente: php artisan whatsapp:status');
        }

        return Command::SUCCESS;
    }
}