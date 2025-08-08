# 📋 Análisis y Levantamiento de Requerimientos del Sistema
**Proyecto:** Sistema de Gestión para Consultorio Odontológico - DentalSync  
**Fecha:** 8 de agosto de 2025  
**Equipo de desarrollo:** NullDevs  
**Versión:** 2.0 (Actualizada)

---

## 🎯 **Objetivo del Sistema**

Desarrollar una aplicación web integral que permita a un consultorio odontológico gestionar pacientes, citas, pagos, tratamientos y comunicación de forma digital, centralizada y segura. El sistema busca optimizar las tareas tanto clínicas como administrativas, facilitando el acceso a la información, mejorando la organización diaria del consultorio y modernizando la comunicación con pacientes.

---

## 👥 **Usuarios del Sistema**

### **🦷 Dentista**
**Rol:** Profesional encargado de los tratamientos y decisiones clínicas.

**Necesidades Identificadas:**
- Acceder al historial clínico completo de cada paciente
- Registrar nuevos tratamientos, consultas y observaciones médicas
- Gestionar placas dentales (subida, visualización, clasificación)
- Consultar agenda de citas del día y confirmar asistencia
- Visualizar estado de pagos y tratamientos pendientes
- Comunicarse con pacientes via WhatsApp para seguimiento
- Generar reportes de tratamientos realizados

### **📋 Recepcionista**
**Rol:** Encargada de la gestión administrativa y atención al cliente.

**Necesidades Identificadas:**
- Registrar pacientes nuevos y mantener datos actualizados
- Gestionar agenda completa (agendar, modificar, cancelar citas)
- Confirmar asistencia a turnos y manejar cambios de último momento
- Procesar pagos y gestionar sistema de cuotas
- Enviar recordatorios y confirmaciones via WhatsApp
- Consultar información básica de pacientes
- Generar reportes financieros y de citas

---

## ⚙️ **Funcionalidades Clave Identificadas**

### **🔐 Sistema de Autenticación**
- Login seguro con roles diferenciados (dentista/recepcionista)
- Gestión de sesiones y control de acceso
- Recuperación de contraseñas
- Auditoría de accesos y actividad

### **🏥 Gestión Integral de Pacientes**
- Registro completo con datos personales y médicos
- Información de contacto de emergencia
- Historial de última visita
- Búsqueda avanzada y filtros
- Gestión de alergias y observaciones médicas

### **📅 Sistema Avanzado de Citas**
- Calendario interactivo con vista diaria/semanal/mensual
- Estados detallados: pendiente, confirmada, atendida, cancelada
- Notificaciones automáticas de recordatorios
- Gestión de horarios disponibles
- Reprogramación flexible de citas

### **🩺 Gestión Clínica Completa**
- Registro detallado de tratamientos realizados
- Historial clínico por paciente
- Observaciones y seguimiento médico
- Planificación de tratamientos futuros
- Integración con sistema de pagos

### **🦷 Sistema de Placas Dentales**
- Subida de placas y radiografías
- Soporte múltiples formatos: JPG, JPEG, PNG, PDF
- Clasificación por tipos: Panorámica, Periapical, Bitewing, Lateral, Oclusal
- Almacenamiento seguro con identificadores únicos
- Visualización integrada en historial clínico

### **💰 Sistema Financiero Avanzado**
- **Tres modalidades de pago:**
  - Pago único completo
  - Cuotas fijas con montos iguales
  - Cuotas variables flexibles
- Dashboard financiero con métricas en tiempo real
- Gestión de cuotas pendientes y vencidas
- Historial completo de pagos por paciente
- Reportes financieros detallados

### **📱 Sistema de Comunicación WhatsApp**
- Gestión de conversaciones directas con pacientes
- Plantillas reutilizables de mensajes
- Envío individual y masivo de comunicaciones
- Automatizaciones basadas en eventos (recordatorios, pagos)
- Integración futura con WhatsApp Business API

### **👨‍💻 Administración de Usuarios**
- Gestión completa de usuarios del sistema
- Control granular de permisos por rol
- Estados de usuario (activo/inactivo)
- Encriptación de contraseñas
- Auditoría de actividades

---

## 🔒 **Matriz de Permisos por Rol**

### **🦷 Dentista - Permisos Clínicos**

| Módulo | Crear | Leer | Actualizar | Eliminar | Observaciones |
|--------|-------|------|------------|----------|---------------|
| **Pacientes** | ❌ | ✅ | ❌ | ❌ | Solo consulta para atención |
| **Citas** | ❌ | ✅ | ✅ | ❌ | Ver agenda, confirmar asistencia |
| **Tratamientos** | ✅ | ✅ | ✅ | ✅ | Control total clínico |
| **Historial Clínico** | ✅ | ✅ | ✅ | ❌ | Registro médico completo |
| **Placas Dentales** | ✅ | ✅ | ✅ | ✅ | Gestión de archivos médicos |
| **Pagos** | ❌ | ✅ | ❌ | ❌ | Solo consulta de estados |
| **WhatsApp** | ✅ | ✅ | ✅ | ❌ | Comunicación con pacientes |
| **Usuarios** | ❌ | ❌ | ❌ | ❌ | Sin acceso administrativo |

### **📋 Recepcionista - Permisos Administrativos**

| Módulo | Crear | Leer | Actualizar | Eliminar | Observaciones |
|--------|-------|------|------------|----------|---------------|
| **Pacientes** | ✅ | ✅ | ✅ | ❌ | Gestión completa de datos |
| **Citas** | ✅ | ✅ | ✅ | ✅ | Control total de agenda |
| **Tratamientos** | ❌ | ✅ | ❌ | ❌ | Solo consulta básica |
| **Historial Clínico** | ❌ | ❌ | ❌ | ❌ | Sin acceso médico |
| **Placas Dentales** | ❌ | ✅ | ❌ | ❌ | Solo visualización |
| **Pagos** | ✅ | ✅ | ✅ | ✅ | Control financiero completo |
| **WhatsApp** | ✅ | ✅ | ✅ | ✅ | Comunicación administrativa |
| **Usuarios** | ❌ | ❌ | ❌ | ❌ | Sin acceso administrativo |

---

## 🎯 **Requerimientos Funcionales Detallados**

### **RF001 - Autenticación de Usuarios**
- El sistema debe permitir login con usuario y contraseña
- Debe validar credenciales contra base de datos encriptada
- Debe mantener sesión activa durante el uso
- Debe permitir logout seguro
- Debe registrar intentos de acceso fallidos

### **RF002 - Gestión de Pacientes**
- El sistema debe permitir registro de pacientes con datos completos
- Debe validar formato de teléfono, email y documentos
- Debe permitir búsqueda por nombre, teléfono o documento
- Debe mantener historial de última visita automáticamente
- Debe gestionar contactos de emergencia

### **RF003 - Sistema de Citas**
- El sistema debe mostrar calendario interactivo
- Debe prevenir solapamiento de citas en mismo horario
- Debe permitir estados: pendiente, confirmada, atendida, cancelada
- Debe enviar notificaciones automáticas de recordatorio
- Debe permitir reprogramación con historial de cambios

### **RF004 - Gestión Clínica**
- El sistema debe permitir registro de tratamientos por dentista
- Debe asociar tratamientos automáticamente con pacientes
- Debe mantener historial clínico inmutable
- Debe permitir observaciones y seguimiento médico
- Debe generar reportes de tratamientos

### **RF005 - Sistema de Pagos**
- El sistema debe soportar tres modalidades de pago
- Debe calcular cuotas automáticamente según modalidad
- Debe generar recordatorios de pagos vencidos
- Debe mantener historial completo de transacciones
- Debe generar reportes financieros

### **RF006 - Comunicación WhatsApp**
- El sistema debe permitir envío de mensajes individuales
- Debe soportar plantillas reutilizables con variables
- Debe automatizar envíos basados en eventos del sistema
- Debe mantener historial de conversaciones
- Debe integrar con WhatsApp Business API (futuro)

---

## 🔧 **Requerimientos No Funcionales**

### **RNF001 - Seguridad**
- Encriptación de contraseñas con bcrypt
- Protección contra ataques CSRF y XSS
- Validación de permisos en cada operación
- Auditoría de actividades críticas
- Backup automático de datos

### **RNF002 - Rendimiento**
- Tiempo de respuesta menor a 3 segundos
- Soporte para 50 usuarios concurrentes
- Optimización de consultas de base de datos
- Cache de datos frecuentemente consultados
- Compresión de imágenes de placas dentales

### **RNF003 - Usabilidad**
- Interfaz responsiva para dispositivos móviles y desktop
- Navegación intuitiva con máximo 3 clicks por función
- Mensajes de error claros y orientadores
- Estados de carga visibles durante operaciones
- Confirmaciones para acciones críticas

### **RNF004 - Compatibilidad**
- Soporte para navegadores modernos (Chrome, Firefox, Safari, Edge)
- Compatible con Windows, macOS y Linux
- Diseño responsivo para tablets y smartphones
- Soporte para formatos de imagen estándar
- API RESTful para integraciones futuras

---

## 📊 **Métricas de Éxito**

### **Técnicas**
- ⚡ Tiempo de carga inicial < 3 segundos
- 🔒 0 vulnerabilidades críticas de seguridad
- 📊 Disponibilidad del sistema > 99%
- 🚀 Tiempo de respuesta API < 500ms

### **Funcionales**
- 👥 Reducción del 50% en tiempo de gestión de citas
- 💰 Control financiero completo con reportes automatizados
- 📱 Comunicación efectiva con pacientes via WhatsApp
- 🦷 Digitalización completa del historial clínico

### **De Usuario**
- 😊 Satisfacción de usuarios > 85%
- 📈 Adopción del sistema > 90% en primer mes
- ⏱️ Reducción de errores administrativos > 70%
- 🎯 Cumplimiento de citas > 80%

---

## 🚀 **Cronograma de Implementación**

### **Fase 1: Infraestructura Base (2 semanas)**
- Configuración del entorno de desarrollo
- Implementación del sistema de autenticación
- Estructura base de la base de datos
- Interfaces básicas de usuario

### **Fase 2: Módulos Core (3 semanas)**
- Gestión completa de pacientes
- Sistema de citas con calendario
- Gestión básica de usuarios y permisos

### **Fase 3: Funcionalidades Clínicas (2 semanas)**
- Sistema de tratamientos e historial clínico
- Gestión de placas dentales
- Reportes médicos básicos

### **Fase 4: Sistema Financiero (2 semanas)**
- Implementación de pagos y cuotas
- Dashboard financiero
- Reportes de pagos y estadísticas

### **Fase 5: Comunicación y Optimización (1 semana)**
- Sistema WhatsApp básico
- Optimizaciones de rendimiento
- Testing y corrección de errores

---

## 📋 **Entregables del Proyecto**

### **Documentación**
- Manual de usuario por rol
- Documentación técnica de APIs
- Guía de instalación y configuración
- Manual de mantenimiento

### **Software**
- Aplicación web completa y funcional
- Base de datos configurada con datos de prueba
- Scripts de backup y restauración
- Código fuente documentado

### **Testing**
- Plan de pruebas ejecutado
- Casos de prueba documentados
- Reporte de testing y correcciones
- Validación de requerimientos

---

**📅 Documento actualizado:** Agosto 8, 2025  
**👥 Equipo responsable:** NullDevs  
**🎓 Proyecto de Egreso:** 3ro Bachillerato Tecnológico - Informática