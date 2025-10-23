# 🔒 Seguridad de la API - DentalSync

## 📋 Problema Detectado (23 de octubre 2025)

### ⚠️ Vulnerabilidad Crítica

**Antes de la corrección:**
- Las rutas API permitían acceso **sin autenticación** en modo desarrollo
- Cualquier persona podía acceder a datos sensibles sin iniciar sesión
- Ejemplo: `http://localhost:8000/api/pagos/paciente/3` mostraba todos los pagos del paciente

**Datos expuestos:**
- ❌ Información personal de pacientes (nombre, teléfono, fecha de nacimiento)
- ❌ Historial de pagos y deudas
- ❌ Tratamientos médicos
- ❌ Citas y motivos de consulta
- ❌ Placas dentales e imágenes
- ❌ Historial clínico completo

---

## ✅ Solución Implementada

### Cambios en el Middleware de Autenticación

**Archivo modificado:** `app/Http/Middleware/AuthenticateApiSimple.php`

#### Antes (❌ Inseguro):

```php
public function handle(Request $request, Closure $next): Response
{
    // Para desarrollo: permitir todas las rutas sin autenticación estricta
    if (config('app.env') === 'local' || config('app.debug') === true) {
        \Log::info('🔓 Modo desarrollo: Permitiendo acceso sin autenticación');
        return $next($request);  // ⚠️ PELIGRO: Acceso libre
    }
    
    // Solo verificaba autenticación en producción
    if ($this->isAuthenticated($request)) {
        return $next($request);
    }
    
    return response()->json(['error' => 'Autenticación requerida'], 401);
}
```

#### Después (✅ Seguro):

```php
public function handle(Request $request, Closure $next): Response
{
    // Rutas permitidas sin autenticación en desarrollo (solo debug)
    $debugRoutes = [
        'api/debug/session',
    ];

    // ✅ SIEMPRE verificar autenticación primero
    if ($this->isAuthenticated($request)) {
        return $next($request);
    }

    // Solo rutas de debug específicas sin autenticación
    if (config('app.debug') === true && in_array($request->path(), $debugRoutes)) {
        return $next($request);
    }

    // ⛔ Denegar acceso no autenticado
    return response()->json([
        'error' => 'No autenticado. Por favor inicia sesión.',
        'message' => 'Autenticación requerida para acceder a este recurso',
        'code' => 'AUTHENTICATION_REQUIRED'
    ], 401);
}
```

---

## 🛡️ Protecciones Implementadas

### 1. Autenticación Obligatoria

**Todos los endpoints API ahora requieren:**

✅ Usuario autenticado con sesión válida  
✅ Token de autenticación Bearer (si aplica)  
✅ Sesión activa en Laravel Auth  

### 2. Métodos de Autenticación Soportados

#### a) Sesión Personalizada (Actual)
```javascript
// Frontend: Login exitoso guarda en sesión
sessionStorage.setItem('user', JSON.stringify(userData))

// Backend: Valida sesión
$sessionUser = session('user');
if ($sessionUser && $sessionUser['logged_in'] === true) {
    // Autenticado ✅
}
```

#### b) Bearer Token (Preparado para futuro)
```http
GET /api/pacientes HTTP/1.1
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

#### c) Laravel Auth
```php
if (Auth::check()) {
    // Autenticado ✅
}
```

### 3. Expiración de Sesión

- **Tiempo de vida:** 1 hora de inactividad
- **Validación:** Cada petición verifica tiempo desde login
- **Auto-logout:** Sesiones expiradas se limpian automáticamente

```php
$loginTime = \Carbon\Carbon::parse($sessionUser['login_time']);
if ($loginTime->diffInHours(now()) <= 1) {
    return true;  // Sesión válida
} else {
    session()->forget(['user', 'auth_token']);  // Limpiar sesión expirada
}
```

---

## 🔐 Endpoints Protegidos

### Todas las rutas bajo `/api/*` requieren autenticación:

| Categoría | Rutas | Datos Sensibles |
|-----------|-------|-----------------|
| **Pacientes** | `/api/pacientes/*` | Nombre, teléfono, fecha nacimiento, historial |
| **Citas** | `/api/citas/*` | Fechas, motivos, estados |
| **Pagos** | `/api/pagos/*` | Montos, deudas, cuotas |
| **Tratamientos** | `/api/tratamientos/*` | Diagnósticos, procedimientos |
| **Placas** | `/api/placas/*` | Imágenes dentales, observaciones |
| **WhatsApp** | `/api/whatsapp/*` | Conversaciones, plantillas |
| **Usuarios** | `/api/usuarios/*` | Credenciales, roles |

### Excepciones (Públicas):

| Ruta | Propósito | Seguridad |
|------|-----------|-----------|
| `/api/login` | Inicio de sesión | Rate limiting estricto |
| `/api/webhook/whatsapp` | Webhook de WhatsApp | Verificación de firma |
| `/api/debug/session` | Debug de sesión | Solo en modo debug |

---

## 🚨 Respuestas de Error

### Sin Autenticación (401)

```json
{
  "error": "No autenticado. Por favor inicia sesión.",
  "message": "Autenticación requerida para acceder a este recurso",
  "code": "AUTHENTICATION_REQUIRED"
}
```

### Sesión Expirada (401)

```json
{
  "error": "Sesión expirada",
  "message": "Tu sesión ha caducado. Por favor inicia sesión nuevamente.",
  "code": "SESSION_EXPIRED"
}
```

---

## 📊 Logging de Seguridad

### Accesos Denegados

```php
\Log::warning('⛔ Acceso denegado - Sin autenticación válida', [
    'route' => 'api/pagos/paciente/3',
    'method' => 'GET',
    'ip' => '192.168.1.100',
    'user_agent' => 'Mozilla/5.0...'
]);
```

**Ubicación de logs:** `storage/logs/laravel.log`

### Monitoreo Recomendado

- **Revisar diariamente:** Intentos de acceso no autorizados
- **Alertas:** Más de 10 intentos fallidos desde la misma IP
- **Bloqueo:** IPs con actividad sospechosa

---

## ✅ Pruebas de Seguridad

### Prueba 1: Acceso sin autenticación

```bash
# Sin sesión activa
curl http://localhost:8000/api/pacientes

# Respuesta esperada: 401 Unauthorized
{
  "error": "No autenticado. Por favor inicia sesión.",
  "code": "AUTHENTICATION_REQUIRED"
}
```

### Prueba 2: Acceso con autenticación válida

```bash
# Con sesión activa (Cookie)
curl -b cookies.txt http://localhost:8000/api/pacientes

# Respuesta esperada: 200 OK
{
  "data": [...]
}
```

### Prueba 3: Sesión expirada

```bash
# Sesión con más de 1 hora de antigüedad
curl -b old_cookies.txt http://localhost:8000/api/pacientes

# Respuesta esperada: 401 Unauthorized
{
  "error": "Sesión expirada",
  "code": "SESSION_EXPIRED"
}
```

---

## 🔧 Configuración Recomendada

### Variables de Entorno (.env)

```env
# Seguridad de sesiones
SESSION_LIFETIME=60
SESSION_EXPIRE_ON_CLOSE=true
SESSION_SECURE_COOKIE=true  # En producción con HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Rate Limiting
API_RATE_LIMIT=60
LOGIN_RATE_LIMIT=5

# Logging
LOG_LEVEL=warning
LOG_CHANNEL=stack
```

### Headers de Seguridad Recomendados

```php
// En app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'DENY');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    return $response;
}
```

---

## 📝 Checklist de Seguridad

### Implementado ✅

- [x] Autenticación obligatoria en todas las rutas API
- [x] Validación de sesión en cada petición
- [x] Expiración automática de sesiones (1 hora)
- [x] Logging de intentos de acceso no autorizados
- [x] Mensajes de error informativos pero seguros
- [x] Rate limiting en login y API

### Recomendaciones Futuras 🔄

- [ ] Implementar tokens JWT para autenticación stateless
- [ ] Agregar autenticación de dos factores (2FA)
- [ ] Implementar refresh tokens
- [ ] Encriptar datos sensibles en la base de datos
- [ ] Auditoría de accesos (quién vio qué y cuándo)
- [ ] Implementar CORS estricto
- [ ] Agregar headers de seguridad HTTP
- [ ] Certificado SSL/TLS en producción
- [ ] Backup automático encriptado
- [ ] Cumplimiento GDPR/leyes de privacidad médica

---

## 🚀 Despliegue en Producción

### Antes de publicar:

1. **Verificar `.env` de producción:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   SESSION_SECURE_COOKIE=true
   ```

2. **Probar autenticación:**
   ```bash
   php artisan test --filter AuthenticationTest
   ```

3. **Revisar logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Configurar HTTPS obligatorio**

5. **Activar firewall de aplicación web (WAF)**

---

## 📞 Contacto

**Equipo de Desarrollo DentalSync**  
Fecha de implementación: 23 de octubre de 2025  
Versión del middleware: 3.0

---

*Este documento debe actualizarse cada vez que se modifiquen las políticas de seguridad de la API.*
