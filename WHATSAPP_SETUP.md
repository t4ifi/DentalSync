# 📱 CONFIGURACIÓN WHATSAPP BUSINESS API - DENTALSYNC

**Guía paso a paso para integrar WhatsApp Business API**

---

## 🚀 **PASOS PARA ACTIVAR WHATSAPP**

### **1. Obtener credenciales de Meta**

1. Ve a [Meta for Developers](https://developers.facebook.com/apps/)
2. Crea una nueva App
3. Agrega el producto "WhatsApp Business"
4. En la configuración de WhatsApp:
   - Copia el **Access Token** (token temporal)
   - Copia el **Phone Number ID**
   - Configura el webhook

### **2. Configurar el archivo .env**

Agrega estas líneas a tu archivo `.env`:

```env
# ========================================
# CONFIGURACIÓN DE WHATSAPP BUSINESS API
# ========================================
WHATSAPP_TOKEN=EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_WEBHOOK_VERIFY_TOKEN=dentalsync_webhook_2025
WHATSAPP_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_TIMEOUT=30
```

**Reemplaza:**
- `EAAxxxxxxx...` por tu token real de WhatsApp
- `123456789012345` por tu Phone Number ID real

### **3. Configurar webhook en Meta**

En la configuración de WhatsApp en Meta:

1. **Webhook URL:** `https://tu-dominio.com/api/webhook/whatsapp`
2. **Verify Token:** `dentalsync_webhook_2025` (debe coincidir con .env)
3. **Eventos a suscribir:**
   - `messages` (mensajes entrantes)
   - `message_deliveries` (confirmaciones de entrega)
   - `message_reads` (confirmaciones de lectura)

### **4. Instalar dependencias**

```bash
composer install
```

### **5. Verificar configuración**

Ejecuta este comando para verificar que todo esté configurado:

```bash
php artisan tinker
```

Luego en Tinker:
```php
use App\Services\WhatsAppService;
$service = new WhatsAppService();
$service->isConfigured(); // Debe retornar true
```

---

## 🔧 **CONFIGURACIÓN AVANZADA**

### **Token permanente (Producción)**

Para producción necesitas un token permanente:

1. Ve a "Configuración del sistema" en WhatsApp Business
2. Genera un token permanente
3. Reemplaza el token temporal en `.env`

### **Verificación de número**

Tu número debe estar verificado en WhatsApp Business para enviar mensajes.

### **Plantillas de mensaje**

Para enviar mensajes proactivos (no en respuesta), necesitas plantillas aprobadas por Meta.

---

## 🧪 **TESTING**

### **Modo simulación**

Si no tienes las credenciales configuradas, el sistema funcionará en modo simulación:

- Los mensajes se guardan en la base de datos
- No se envían realmente via WhatsApp
- Aparece una marca "simulado" en las respuestas

### **Testing con credenciales reales**

1. Configura las credenciales como se indicó arriba
2. Usa tu propio número para hacer pruebas
3. Revisa los logs en `storage/logs/laravel.log`

---

## 📋 **CHECKLIST DE CONFIGURACIÓN**

- [ ] ✅ App creada en Meta for Developers
- [ ] ✅ Producto WhatsApp Business agregado
- [ ] ✅ Token copiado al .env
- [ ] ✅ Phone Number ID copiado al .env
- [ ] ✅ Webhook configurado en Meta
- [ ] ✅ URL del webhook apuntando a tu servidor
- [ ] ✅ Verify token coincide entre Meta y .env
- [ ] ✅ Composer install ejecutado
- [ ] ✅ Verificación con Tinker exitosa

---

## 🐛 **TROUBLESHOOTING**

### **Error: "WhatsApp no está configurado"**
- Verifica que `WHATSAPP_TOKEN` y `WHATSAPP_PHONE_NUMBER_ID` estén en .env
- Ejecuta `php artisan config:clear`

### **Error: "Webhook verification failed"**
- Verifica que el verify token coincida entre Meta y .env
- Asegúrate que la URL del webhook sea accesible públicamente

### **Error: "Invalid phone number"**
- Los números deben estar en formato internacional (sin +)
- Ejemplo: 59899123456 (Uruguay), 5491123456789 (Argentina)

### **Mensajes no se entregan**
- Verifica que el número esté registrado en WhatsApp
- Revisa los logs en `storage/logs/laravel.log`
- Confirma que el token no haya expirado

---

## 📞 **FORMATO DE NÚMEROS**

El sistema formatea automáticamente los números:

- **Entrada:** +598 99 123 456
- **Procesado:** 59899123456
- **Uruguayos:** Agrega 598 automáticamente si falta

---

## 🚦 **ESTADOS DE MENSAJE**

1. **enviando** - Creado en BD, enviando a WhatsApp
2. **enviado** - Enviado exitosamente a WhatsApp
3. **entregado** - Entregado al teléfono del destinatario  
4. **leido** - Leído por el destinatario
5. **error** - Error en el envío

---

**¡Una vez configurado, WhatsApp funcionará automáticamente! 🎉**

*Documentación actualizada: Octubre 2025*