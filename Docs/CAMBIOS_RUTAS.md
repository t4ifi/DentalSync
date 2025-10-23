# 🔄 Reorganización de Rutas del Sistema

**Fecha**: 23 de octubre de 2025  
**Motivo**: Mejorar la estructura semántica de las URLs y organización del sistema

---

## 📋 Resumen de Cambios

Se reorganizaron las rutas del frontend para que sean más lógicas y representen mejor la estructura del sistema. Anteriormente, todas las rutas de pacientes estaban bajo `/citas/*`, lo cual no tenía sentido semántico.

---

## 🔀 Mapeo de Rutas Antiguas → Nuevas

### **Pacientes**

| Ruta Antigua | Ruta Nueva | Descripción |
|--------------|------------|-------------|
| `/citas/ver-pacientes` | `/pacientes/ver` | Lista de pacientes |
| `/citas/crear-paciente` | `/pacientes/crear` | Crear nuevo paciente |
| `/citas/editar-pacientes` | `/pacientes/editar` | Editar paciente existente |
| `/citas/editar-paciente` | `/pacientes/editar` | Editar paciente (eliminado, consolidado) |

### **Citas**

| Ruta Antigua | Ruta Nueva | Descripción |
|--------------|------------|-------------|
| `/citas/calendario` | `/citas/calendario` | ✅ Sin cambios |
| `/citas/agendar` | `/citas/agendar` | ✅ Sin cambios |

### **Placas, Tratamientos, etc.**

Estas rutas ya estaban correctamente organizadas y **no se modificaron**:
- `/placas/*` 
- `/tratamientos/*`
- `/pagos/*`
- `/usuarios/*`
- `/whatsapp/*`
- `/mensajes/*`

---

## 📂 Nueva Estructura de Rutas

```
/
├── /login                        # Inicio de sesión
│
├── /citas                        # Gestión de Citas
│   ├── /citas/calendario         # Ver calendario y citas
│   └── /citas/agendar            # Agendar nueva cita
│
├── /pacientes                    # Gestión de Pacientes
│   ├── /pacientes/ver            # Ver lista de pacientes
│   ├── /pacientes/crear          # Crear nuevo paciente
│   └── /pacientes/editar         # Editar paciente
│
├── /tratamientos                 # Gestión de Tratamientos (dentista)
│   ├── /tratamientos/registrar   # Registrar tratamiento
│   └── /tratamientos/ver         # Ver tratamientos
│
├── /placas                       # Gestión de Placas Dentales (dentista)
│   ├── /placas/subir             # Subir nueva placa
│   ├── /placas/ver               # Ver placas existentes
│   └── /placas/eliminar          # Eliminar placas
│
├── /pagos                        # Gestión de Pagos
│   └── /pagos/gestion            # Gestión de pagos y cuotas
│
├── /usuarios                     # Gestión de Usuarios
│   ├── /usuarios/ver             # Ver usuarios
│   ├── /usuarios/crear           # Crear usuario
│   ├── /usuarios/editar-lista    # Lista de usuarios para editar
│   └── /usuarios/editar/:id      # Editar usuario específico
│
├── /mensajes                     # Sistema de Mensajes Internos
│   ├── /mensajes/bandeja         # Bandeja de entrada
│   ├── /mensajes/nuevo           # Enviar nuevo mensaje
│   └── /mensajes/enviados        # Mensajes enviados
│
└── /whatsapp                     # WhatsApp Business Integration
    ├── /whatsapp/conversaciones  # Ver conversaciones
    ├── /whatsapp/enviar          # Enviar mensaje
    ├── /whatsapp/templates       # Plantillas de mensajes
    └── /whatsapp/automaticos     # Mensajes automáticos
```

---

## 🎯 Redirecciones Actualizadas

### Después del Login

| Rol | Ruta de Redirección |
|-----|---------------------|
| **Recepcionista** | `/pacientes/ver` (antes: `/citas/calendario`) |
| **Dentista** | `/citas/calendario` (sin cambios) |

### Redirecciones de Panel

- `/panel-recepcionista` → `/pacientes/ver`
- `/panel-dentista` → `/citas/calendario`

---

## 🔧 Archivos Modificados

### 1. **router.js**
- Reestructuración completa de rutas bajo `/pacientes`
- Eliminación de rutas duplicadas (`editar-paciente` consolidado con `editar-pacientes`)
- Actualización de redirecciones en `beforeEach`

### 2. **Dashboard.vue**
- Actualización de todos los `router-link` en el menú lateral
- Modificación de la función `activeGroup()` computed property
- Actualización de la función `syncMenuWithRoute()` para reconocer las nuevas rutas

---

## ✅ Ventajas de la Nueva Estructura

1. **Semántica clara**: Las URLs reflejan exactamente lo que representan
2. **Organización lógica**: Cada módulo tiene su propio namespace
3. **Escalabilidad**: Fácil agregar nuevas rutas bajo cada módulo
4. **Mejor UX**: URLs más legibles y comprensibles
5. **Mantenibilidad**: Código más limpio y fácil de mantener

---

## 🧪 Testing Requerido

Después de estos cambios, verificar:

- ✅ Login y redirección según rol
- ✅ Navegación del menú lateral funciona correctamente
- ✅ Los links activos se marcan correctamente
- ✅ Las rutas protegidas siguen requiriendo autenticación
- ✅ Todos los componentes se cargan en sus nuevas rutas
- ✅ No hay enlaces rotos en la aplicación

---

## 📝 Notas de Migración

**Para el equipo de desarrollo:**

Si tienen código personalizado o enlaces hardcodeados a las rutas antiguas:

```javascript
// ❌ ANTIGUO
this.$router.push('/citas/ver-pacientes')
this.$router.push('/citas/crear-paciente')
this.$router.push('/citas/editar-pacientes')

// ✅ NUEVO
this.$router.push('/pacientes/ver')
this.$router.push('/pacientes/crear')
this.$router.push('/pacientes/editar')
```

---

*Documentación generada para el proyecto DentalSync*  
*Última actualización: 23/10/2025*
