# 📋 CASOS DE USO - SISTEMA DENTALSYNC

**Documento:** Especificación Completa de Casos de Uso  
**Sistema:** DentalSync - Gestión para Consultorio Odontológico  
**Equipo:** NullDevs  
**Fecha:** 6 de octubre de 2025  
**Versión:** 1.0

---

## 📚 ÍNDICE

1. [Actores del Sistema](#actores-del-sistema)
2. [Casos de Uso de Autenticación](#casos-de-uso-de-autenticación)
3. [Casos de Uso de Gestión de Pacientes](#casos-de-uso-de-gestión-de-pacientes)
4. [Casos de Uso de Gestión de Citas](#casos-de-uso-de-gestión-de-citas)
5. [Casos de Uso de Tratamientos](#casos-de-uso-de-tratamientos)
6. [Casos de Uso de Pagos](#casos-de-uso-de-pagos)
7. [Casos de Uso de Placas Dentales](#casos-de-uso-de-placas-dentales)
8. [Casos de Uso de WhatsApp](#casos-de-uso-de-whatsapp)
9. [Casos de Uso de Gestión de Usuarios](#casos-de-uso-de-gestión-de-usuarios)
10. [Casos de Uso de Reportes](#casos-de-uso-de-reportes)
11. [Casos de Uso de Auditoría y Seguridad](#casos-de-uso-de-auditoría-y-seguridad)
12. [Casos de Uso de WhatsApp Avanzado](#casos-de-uso-de-whatsapp-avanzado)
13. [Casos de Uso de Gestión de Datos](#casos-de-uso-de-gestión-de-datos)
14. [Casos de Uso de Reportes Avanzados](#casos-de-uso-de-reportes-avanzados)

---

## 👥 ACTORES DEL SISTEMA

### **🦷 Dentista**
- **Descripción:** Profesional odontológico responsable de los tratamientos y decisiones clínicas
- **Responsabilidades:** Diagnósticos, tratamientos, registros clínicos, comunicación con pacientes
- **Permisos:** Acceso completo a información clínica, gestión de tratamientos, placas dentales

### **📋 Recepcionista**
- **Descripción:** Encargada de la gestión administrativa y atención al cliente
- **Responsabilidades:** Gestión de citas, pacientes, pagos, comunicación administrativa
- **Permisos:** Acceso limitado a información médica, gestión completa de aspectos administrativos

### **👨‍💼 Administrador del Sistema**
- **Descripción:** Usuario con permisos completos para gestión del sistema
- **Responsabilidades:** Configuración del sistema, gestión de usuarios, mantenimiento
- **Permisos:** Acceso total al sistema

---

## 🔐 CASOS DE USO DE AUTENTICACIÓN

### **CU-01: Iniciar Sesión**
- **Nombre:** Autenticación de Usuario
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite al usuario acceder al sistema mediante credenciales válidas
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:** 
  - Usuario debe estar registrado en el sistema
  - Usuario debe estar activo
  - Sistema debe estar disponible
- **Flujo Principal:**
  1. Usuario accede a la página de login
  2. Sistema muestra formulario de autenticación
  3. Usuario ingresa usuario y contraseña
  4. Sistema valida credenciales
  5. Sistema verifica que el usuario esté activo
  6. Sistema crea sesión de usuario
  7. Sistema registra acceso en logs de seguridad
  8. Sistema redirige al dashboard correspondiente
- **Flujo Alternativo:**
  - **4a.** Credenciales inválidas: Sistema muestra error y solicita reingreso
  - **5a.** Usuario inactivo: Sistema muestra mensaje de usuario deshabilitado
  - **7a.** Error de sesión: Sistema muestra error técnico
- **Post-condiciones:**
  - Usuario autenticado en el sistema
  - Sesión activa creada
  - Acceso registrado en logs
- **Requisitos:**
  - Rate limiting (5 intentos por minuto)
  - Hash bcrypt para contraseñas
  - Tokens de sesión seguros
- **Observaciones:** 
  - Implementa protección contra ataques de fuerza bruta
  - Sesiones expiran en 8 horas de inactividad

### **CU-02: Cerrar Sesión**
- **Nombre:** Cierre de Sesión
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite al usuario salir del sistema de forma segura
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario debe estar autenticado
- **Flujo Principal:**
  1. Usuario selecciona opción "Cerrar Sesión"
  2. Sistema invalida la sesión actual
  3. Sistema limpia datos de sesión
  4. Sistema registra cierre en logs
  5. Sistema redirige a página de login
- **Flujo Alternativo:**
  - **2a.** Error al cerrar sesión: Sistema fuerza logout y redirige
- **Post-condiciones:**
  - Sesión invalidada
  - Usuario desconectado del sistema
- **Requisitos:** N/A
- **Observaciones:** 
  - Logout automático por inactividad después de 8 horas

---

## 👥 CASOS DE USO DE GESTIÓN DE PACIENTES

### **CU-03: Registrar Paciente**
- **Nombre:** Crear Nuevo Paciente
- **Rol:** Recepcionista
- **Descripción:** Permite registrar un nuevo paciente en el sistema con información completa
- **Actores:** Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado como recepcionista
  - Acceso al módulo de pacientes
- **Flujo Principal:**
  1. Recepcionista accede al módulo de pacientes
  2. Sistema muestra lista de pacientes existentes
  3. Recepcionista selecciona "Crear Paciente"
  4. Sistema muestra formulario de registro
  5. Recepcionista ingresa datos personales (nombre, teléfono, fecha nacimiento)
  6. Recepcionista ingresa datos de contacto (email, dirección, ciudad)
  7. Recepcionista ingresa datos médicos (alergias, observaciones)
  8. Recepcionista ingresa contacto de emergencia
  9. Sistema valida la información ingresada
  10. Sistema guarda el paciente en la base de datos
  11. Sistema asigna fecha de registro como última visita
  12. Sistema muestra confirmación de creación
- **Flujo Alternativo:**
  - **9a.** Datos inválidos: Sistema muestra errores específicos por campo
  - **9b.** Teléfono ya registrado: Sistema pregunta si desea continuar
  - **10a.** Error de base de datos: Sistema muestra error y permite reintentar
- **Post-condiciones:**
  - Paciente registrado en el sistema
  - Información disponible para otros módulos
- **Requisitos:**
  - Nombre completo obligatorio
  - Teléfono obligatorio
  - Validación de formato de email
  - Fecha de nacimiento no puede ser futura
- **Observaciones:**
  - Campos de alergias y observaciones son críticos para seguridad del paciente

### **CU-04: Consultar Paciente**
- **Nombre:** Ver Información de Paciente
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite consultar la información completa de un paciente específico
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Paciente debe existir en el sistema
- **Flujo Principal:**
  1. Usuario accede al módulo de pacientes
  2. Sistema muestra lista de pacientes
  3. Usuario busca paciente (por nombre, teléfono o ID)
  4. Usuario selecciona paciente de la lista
  5. Sistema muestra información completa del paciente
  6. Sistema muestra datos personales, médicos y de contacto
  7. Sistema muestra historial de última visita
  8. Sistema muestra vínculos a citas, tratamientos y pagos relacionados
- **Flujo Alternativo:**
  - **3a.** Búsqueda sin resultados: Sistema muestra mensaje "No encontrado"
  - **4a.** Paciente eliminado: Sistema muestra error de acceso
- **Post-condiciones:**
  - Información del paciente desplegada
  - Acceso habilitado a módulos relacionados
- **Requisitos:**
  - Búsqueda por múltiples criterios
  - Información actualizada en tiempo real
- **Observaciones:**
  - Recepcionista no ve información clínica sensible

### **CU-05: Actualizar Paciente**
- **Nombre:** Modificar Datos de Paciente
- **Rol:** Recepcionista
- **Descripción:** Permite actualizar la información de un paciente existente
- **Actores:** Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado como recepcionista
  - Paciente debe existir en el sistema
- **Flujo Principal:**
  1. Recepcionista localiza el paciente (CU-04)
  2. Recepcionista selecciona "Editar Paciente"
  3. Sistema muestra formulario pre-poblado con datos actuales
  4. Recepcionista modifica los campos necesarios
  5. Sistema valida los cambios realizados
  6. Sistema actualiza la información en la base de datos
  7. Sistema muestra confirmación de actualización
  8. Sistema actualiza timestamp de modificación
- **Flujo Alternativo:**
  - **5a.** Datos inválidos: Sistema muestra errores específicos
  - **6a.** Error de actualización: Sistema permite reintentar
- **Post-condiciones:**
  - Información del paciente actualizada
  - Cambios reflejados en todo el sistema
- **Requisitos:**
  - Validación de integridad de datos
  - Preservación de historial clínico
- **Observaciones:**
  - No se permite eliminar pacientes con historial clínico

---

## 📅 CASOS DE USO DE GESTIÓN DE CITAS

### **CU-06: Agendar Cita**
- **Nombre:** Programar Nueva Cita
- **Rol:** Recepcionista
- **Descripción:** Permite programar una nueva cita médica para un paciente
- **Actores:** Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado como recepcionista
  - Paciente debe existir o poder ser creado
- **Flujo Principal:**
  1. Recepcionista accede al módulo de citas
  2. Sistema muestra calendario de citas
  3. Recepcionista selecciona "Agendar Cita"
  4. Sistema muestra formulario de agendamiento
  5. Recepcionista selecciona o busca paciente
  6. Recepcionista selecciona fecha y hora disponible
  7. Recepcionista ingresa motivo de la consulta
  8. Recepcionista asigna dentista responsable
  9. Sistema valida disponibilidad de horario
  10. Sistema crea la cita con estado "pendiente"
  11. Sistema actualiza calendario
  12. Sistema puede enviar recordatorio por WhatsApp
- **Flujo Alternativo:**
  - **5a.** Paciente no existe: Sistema ofrece crear paciente básico
  - **9a.** Horario no disponible: Sistema sugiere horarios alternativos
  - **12a.** Error WhatsApp: Sistema programa envío posterior
- **Post-condiciones:**
  - Cita registrada en el sistema
  - Calendario actualizado
  - Notificación enviada (opcional)
- **Requisitos:**
  - Validación de conflictos de horario
  - Gestión de múltiples dentistas
  - Integración con sistema de recordatorios
- **Observaciones:**
  - Permite creación rápida de pacientes durante agendamiento

### **CU-07: Gestionar Estado de Cita**
- **Nombre:** Actualizar Estado de Cita
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite cambiar el estado de una cita (confirmar, cancelar, marcar como atendida)
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Cita debe existir en el sistema
- **Flujo Principal:**
  1. Usuario accede al módulo de citas
  2. Sistema muestra lista/calendario de citas
  3. Usuario selecciona cita específica
  4. Sistema muestra detalles de la cita
  5. Usuario selecciona nuevo estado (confirmada/cancelada/atendida)
  6. Sistema valida el cambio de estado
  7. Sistema actualiza la cita
  8. Si estado es "atendida", sistema registra fecha y hora actual
  9. Sistema muestra confirmación del cambio
- **Flujo Alternativo:**
  - **6a.** Cambio de estado inválido: Sistema muestra error
  - **8a.** Error al registrar atención: Sistema permite corrección manual
- **Post-condiciones:**
  - Estado de cita actualizado
  - Información reflejada en calendario
  - Fecha de atención registrada (si aplica)
- **Requisitos:**
  - Validación de transiciones de estado válidas
  - Registro automático de timestamps
- **Observaciones:**
  - Solo dentistas pueden marcar citas como "atendidas"

### **CU-08: Consultar Agenda**
- **Nombre:** Ver Calendario de Citas
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite visualizar las citas programadas en formato calendario
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
- **Flujo Principal:**
  1. Usuario accede al módulo de citas
  2. Sistema muestra calendario interactivo
  3. Sistema carga citas del período actual
  4. Usuario puede navegar entre fechas
  5. Usuario puede cambiar vista (día/semana/mes)
  6. Usuario puede filtrar por dentista o estado
  7. Sistema actualiza vista según filtros aplicados
- **Flujo Alternativo:**
  - **3a.** Sin citas en período: Sistema muestra calendario vacío
  - **7a.** Error al aplicar filtros: Sistema mantiene vista actual
- **Post-condiciones:**
  - Calendario desplegado con citas
  - Filtros aplicados correctamente
- **Requisitos:**
  - Interfaz de calendario interactiva
  - Filtros múltiples
  - Actualización en tiempo real
- **Observaciones:**
  - Dentistas ven solo sus citas por defecto

---

## 🦷 CASOS DE USO DE TRATAMIENTOS

### **CU-09: Registrar Tratamiento**
- **Nombre:** Crear Nuevo Tratamiento
- **Rol:** Dentista
- **Descripción:** Permite al dentista registrar un nuevo tratamiento para un paciente
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Paciente debe existir en el sistema
- **Flujo Principal:**
  1. Dentista accede al módulo de tratamientos
  2. Dentista selecciona o busca paciente
  3. Sistema muestra historial actual del paciente
  4. Dentista selecciona "Registrar Tratamiento"
  5. Sistema muestra formulario de tratamiento
  6. Dentista ingresa descripción detallada del tratamiento
  7. Dentista selecciona fecha de inicio
  8. Sistema asigna estado "activo" por defecto
  9. Sistema guarda el tratamiento
  10. Sistema crea entrada automática en historial clínico
  11. Sistema muestra confirmación
- **Flujo Alternativo:**
  - **2a.** Paciente no encontrado: Sistema permite crear paciente básico
  - **9a.** Error al guardar: Sistema permite reintentar
- **Post-condiciones:**
  - Tratamiento registrado en el sistema
  - Entrada creada en historial clínico
  - Tratamiento visible en perfil del paciente
- **Requisitos:**
  - Descripción obligatoria del tratamiento
  - Fecha de inicio no puede ser futura
  - Asociación obligatoria con paciente
- **Observaciones:**
  - Solo dentistas pueden registrar tratamientos

### **CU-10: Agregar Observación a Tratamiento**
- **Nombre:** Registrar Observación Clínica
- **Rol:** Dentista
- **Descripción:** Permite al dentista agregar observaciones durante el progreso de un tratamiento
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Tratamiento debe existir y estar activo
- **Flujo Principal:**
  1. Dentista localiza el tratamiento específico
  2. Dentista selecciona "Agregar Observación"
  3. Sistema muestra formulario de observación
  4. Dentista ingresa observaciones detalladas
  5. Sistema registra fecha y hora automáticamente
  6. Sistema guarda la observación
  7. Sistema actualiza historial clínico con nueva entrada
  8. Sistema muestra confirmación
- **Flujo Alternativo:**
  - **6a.** Error al guardar: Sistema permite reintentar
- **Post-condiciones:**
  - Observación registrada en tratamiento
  - Historial clínico actualizado
  - Progreso del tratamiento documentado
- **Requisitos:**
  - Observación obligatoria no vacía
  - Timestamp automático
  - Preservación de historial
- **Observaciones:**
  - Las observaciones no pueden ser editadas una vez guardadas

### **CU-11: Finalizar Tratamiento**
- **Nombre:** Completar Tratamiento
- **Rol:** Dentista
- **Descripción:** Permite al dentista marcar un tratamiento como finalizado
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Tratamiento debe existir y estar activo
- **Flujo Principal:**
  1. Dentista localiza el tratamiento activo
  2. Dentista selecciona "Finalizar Tratamiento"
  3. Sistema solicita confirmación
  4. Dentista confirma finalización
  5. Sistema cambia estado a "finalizado"
  6. Sistema registra fecha de finalización
  7. Sistema actualiza historial clínico
  8. Sistema muestra confirmación
- **Flujo Alternativo:**
  - **4a.** Dentista cancela: Sistema mantiene estado actual
- **Post-condiciones:**
  - Tratamiento marcado como finalizado
  - Fecha de finalización registrada
  - Historial clínico actualizado
- **Requisitos:**
  - Confirmación obligatoria
  - Registro de timestamp
  - Actualización de estado irreversible
- **Observaciones:**
  - Tratamientos finalizados no pueden volver a estado activo

### **CU-12: Consultar Historial Clínico**
- **Nombre:** Ver Historial Médico del Paciente
- **Rol:** Dentista
- **Descripción:** Permite al dentista consultar el historial clínico completo de un paciente
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Paciente debe existir en el sistema
- **Flujo Principal:**
  1. Dentista busca y selecciona paciente
  2. Dentista accede a "Historial Clínico"
  3. Sistema muestra historial cronológico completo
  4. Sistema incluye todos los tratamientos
  5. Sistema incluye todas las observaciones
  6. Sistema incluye fechas de visitas
  7. Dentista puede filtrar por fecha o tipo de tratamiento
- **Flujo Alternativo:**
  - **3a.** Sin historial: Sistema muestra mensaje informativo
- **Post-condiciones:**
  - Historial clínico desplegado
  - Información completa disponible para diagnóstico
- **Requisitos:**
  - Orden cronológico
  - Filtros de búsqueda
  - Información completa y precisa
- **Observaciones:**
  - Solo dentistas tienen acceso al historial clínico completo

---

## 💰 CASOS DE USO DE PAGOS

### **CU-13: Registrar Pago Único**
- **Nombre:** Procesar Pago Completo
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite registrar un pago completo por un tratamiento
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Paciente debe existir
  - Tratamiento o servicio identificado
- **Flujo Principal:**
  1. Usuario accede al módulo de pagos
  2. Usuario selecciona "Registrar Pago"
  3. Sistema muestra formulario de pago
  4. Usuario selecciona paciente
  5. Usuario ingresa monto total
  6. Usuario ingresa descripción del servicio/tratamiento
  7. Usuario selecciona modalidad "Pago Único"
  8. Sistema registra pago con estado "pagado_completo"
  9. Sistema crea detalle de pago automáticamente
  10. Sistema muestra comprobante
- **Flujo Alternativo:**
  - **8a.** Error al procesar: Sistema permite corrección
- **Post-condiciones:**
  - Pago registrado en el sistema
  - Estado marcado como completo
  - Comprobante generado
- **Requisitos:**
  - Monto mayor a cero
  - Descripción obligatoria
  - Validación de paciente existente
- **Observaciones:**
  - Modalidad más simple de pago

### **CU-14: Configurar Pago en Cuotas Fijas**
- **Nombre:** Establecer Plan de Cuotas Iguales
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite configurar un plan de pagos en cuotas fijas e iguales
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Paciente debe existir
  - Monto total definido
- **Flujo Principal:**
  1. Usuario inicia registro de pago
  2. Usuario selecciona modalidad "Cuotas Fijas"
  3. Sistema solicita monto total y número de cuotas
  4. Usuario ingresa cantidad de cuotas deseadas
  5. Sistema calcula monto por cuota automáticamente
  6. Sistema muestra preview del plan de pagos
  7. Usuario confirma configuración
  8. Sistema crea registro de pago principal
  9. Sistema genera todas las cuotas programadas
  10. Sistema asigna fechas de vencimiento
  11. Sistema muestra plan completo
- **Flujo Alternativo:**
  - **5a.** División inexacta: Sistema ajusta última cuota
  - **7a.** Usuario modifica: Sistema recalcula
- **Post-condiciones:**
  - Plan de cuotas configurado
  - Todas las cuotas generadas con fechas
  - Estado inicial "pendiente"
- **Requisitos:**
  - Monto total divisible
  - Número de cuotas válido (1-60)
  - Fechas de vencimiento lógicas
- **Observaciones:**
  - Sistema ajusta automáticamente centavos en última cuota

### **CU-15: Registrar Pago de Cuota**
- **Nombre:** Procesar Pago Parcial
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite registrar el pago de una cuota específica
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Plan de cuotas debe existir
  - Cuota debe estar pendiente
- **Flujo Principal:**
  1. Usuario accede a pagos pendientes
  2. Sistema muestra cuotas vencidas y próximas
  3. Usuario selecciona cuota específica
  4. Sistema muestra detalles de la cuota
  5. Usuario ingresa monto del pago
  6. Sistema valida que monto cubra la cuota
  7. Sistema registra el pago parcial
  8. Sistema actualiza estado de cuota a "pagada"
  9. Sistema actualiza saldo restante del pago principal
  10. Sistema verifica si pago está completo
  11. Sistema muestra confirmación y saldo actualizado
- **Flujo Alternativo:**
  - **6a.** Monto insuficiente: Sistema solicita monto completo
  - **6b.** Sobrepago: Sistema registra como pago adelantado
  - **10a.** Pago completado: Sistema cambia estado a "pagado_completo"
- **Post-condiciones:**
  - Cuota marcada como pagada
  - Saldo principal actualizado
  - Detalle de pago registrado
- **Requisitos:**
  - Validación de montos
  - Actualización automática de saldos
  - Registro de fecha de pago
- **Observaciones:**
  - Permite pagos parciales y adelantados

### **CU-16: Configurar Pago en Cuotas Variables**
- **Nombre:** Establecer Plan de Cuotas Flexibles
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite configurar un plan de pagos con cuotas de montos diferentes
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Paciente debe existir
  - Monto total conocido
- **Flujo Principal:**
  1. Usuario selecciona modalidad "Cuotas Variables"
  2. Sistema solicita monto total
  3. Usuario define número de cuotas
  4. Sistema presenta formulario para cada cuota
  5. Usuario ingresa monto y fecha para cada cuota
  6. Sistema valida que suma de cuotas = monto total
  7. Sistema muestra resumen del plan
  8. Usuario confirma configuración
  9. Sistema crea registro principal
  10. Sistema crea todas las cuotas individuales
- **Flujo Alternativo:**
  - **6a.** Suma incorrecta: Sistema solicita ajuste
  - **8a.** Usuario modifica: Sistema permite reedición
- **Post-condiciones:**
  - Plan de cuotas variables configurado
  - Flexibilidad en montos y fechas
  - Estado inicial "pendiente"
- **Requisitos:**
  - Suma de cuotas = monto total
  - Fechas lógicas y futuras
  - Montos mayores a cero
- **Observaciones:**
  - Máxima flexibilidad para casos especiales

### **CU-17: Generar Reporte de Pagos**
- **Nombre:** Consultar Resumen Financiero
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite generar reportes financieros del consultorio
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Datos de pagos en el sistema
- **Flujo Principal:**
  1. Usuario accede a "Reportes de Pagos"
  2. Sistema muestra opciones de filtro
  3. Usuario selecciona período de tiempo
  4. Usuario puede filtrar por dentista (opcional)
  5. Usuario puede filtrar por estado de pago
  6. Sistema genera reporte con estadísticas
  7. Sistema muestra totales recaudados
  8. Sistema muestra pagos pendientes
  9. Sistema muestra cuotas vencidas
  10. Usuario puede exportar reporte
- **Flujo Alternativo:**
  - **6a.** Sin datos en período: Sistema muestra reporte vacío
- **Post-condiciones:**
  - Reporte financiero generado
  - Estadísticas calculadas
  - Opción de exportación disponible
- **Requisitos:**
  - Cálculos precisos
  - Filtros múltiples
  - Exportación a PDF/Excel
- **Observaciones:**
  - Información crítica para gestión financiera

---

## 📸 CASOS DE USO DE PLACAS DENTALES

### **CU-18: Subir Placa Dental**
- **Nombre:** Cargar Imagen Radiográfica
- **Rol:** Dentista
- **Descripción:** Permite al dentista subir placas dentales o radiografías para un paciente
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Paciente debe existir
  - Archivo de imagen válido disponible
- **Flujo Principal:**
  1. Dentista accede al módulo de placas
  2. Dentista selecciona "Subir Placa"
  3. Sistema muestra formulario de carga
  4. Dentista selecciona paciente
  5. Dentista selecciona archivo de imagen/PDF
  6. Dentista especifica tipo de placa (Panorámica, Periapical, etc.)
  7. Dentista ingresa fecha de la placa
  8. Dentista ingresa lugar donde se tomó
  9. Sistema valida tipo y tamaño de archivo
  10. Sistema genera nombre único para el archivo
  11. Sistema almacena archivo en storage seguro
  12. Sistema registra metadatos en base de datos
  13. Sistema muestra confirmación con preview
- **Flujo Alternativo:**
  - **9a.** Archivo inválido: Sistema muestra error específico
  - **9b.** Archivo muy grande: Sistema solicita archivo menor
  - **11a.** Error de almacenamiento: Sistema permite reintentar
- **Post-condiciones:**
  - Placa almacenada en el sistema
  - Metadatos registrados
  - Archivo accesible para consulta
- **Requisitos:**
  - Formatos soportados: JPG, JPEG, PNG, PDF
  - Tamaño máximo: 10MB
  - Validación de integridad de archivo
- **Observaciones:**
  - Solo dentistas pueden subir placas por consideraciones médicas

### **CU-19: Visualizar Placas del Paciente**
- **Nombre:** Consultar Imágenes Radiográficas
- **Rol:** Dentista
- **Descripción:** Permite al dentista visualizar todas las placas de un paciente específico
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Paciente debe existir
- **Flujo Principal:**
  1. Dentista busca y selecciona paciente
  2. Dentista accede a "Placas Dentales"
  3. Sistema muestra galería de placas del paciente
  4. Sistema organiza placas por fecha (más recientes primero)
  5. Sistema muestra información de cada placa (tipo, fecha, lugar)
  6. Dentista puede filtrar por tipo de placa
  7. Dentista selecciona placa específica
  8. Sistema muestra imagen en tamaño completo
  9. Sistema permite zoom y navegación
- **Flujo Alternativo:**
  - **3a.** Sin placas: Sistema muestra mensaje informativo
  - **8a.** Error al cargar imagen: Sistema muestra placeholder
- **Post-condiciones:**
  - Placas visualizadas correctamente
  - Información completa disponible
- **Requisitos:**
  - Visualizador de imágenes integrado
  - Filtros por tipo y fecha
  - Carga rápida de imágenes
- **Observaciones:**
  - Interfaz optimizada para diagnóstico médico

### **CU-20: Actualizar Placa Dental**
- **Nombre:** Modificar Información de Placa
- **Rol:** Dentista
- **Descripción:** Permite al dentista actualizar los metadatos o reemplazar archivo de una placa
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Placa debe existir en el sistema
- **Flujo Principal:**
  1. Dentista localiza la placa específica
  2. Dentista selecciona "Editar Placa"
  3. Sistema muestra formulario con datos actuales
  4. Dentista puede modificar metadatos (tipo, fecha, lugar)
  5. Dentista puede reemplazar archivo (opcional)
  6. Sistema valida cambios realizados
  7. Si hay archivo nuevo, sistema reemplaza el anterior
  8. Sistema actualiza metadatos en base de datos
  9. Sistema elimina archivo anterior si fue reemplazado
  10. Sistema muestra confirmación
- **Flujo Alternativo:**
  - **6a.** Datos inválidos: Sistema muestra errores específicos
  - **7a.** Error al subir nuevo archivo: Sistema mantiene archivo anterior
- **Post-condiciones:**
  - Información de placa actualizada
  - Archivo reemplazado si fue necesario
  - Archivo anterior eliminado del storage
- **Requisitos:**
  - Validación de nuevos archivos
  - Limpieza automática de archivos obsoletos
  - Preservación de integridad
- **Observaciones:**
  - Cambios preservan historial de fechas de modificación

### **CU-21: Eliminar Placa Dental**
- **Nombre:** Remover Imagen Radiográfica
- **Rol:** Dentista
- **Descripción:** Permite al dentista eliminar una placa dental del sistema
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Placa debe existir en el sistema
- **Flujo Principal:**
  1. Dentista localiza la placa a eliminar
  2. Dentista selecciona "Eliminar Placa"
  3. Sistema solicita confirmación de eliminación
  4. Sistema advierte que la acción es irreversible
  5. Dentista confirma eliminación
  6. Sistema elimina registro de base de datos
  7. Sistema elimina archivo físico del storage
  8. Sistema muestra confirmación de eliminación
- **Flujo Alternativo:**
  - **5a.** Dentista cancela: Sistema mantiene placa sin cambios
  - **7a.** Error al eliminar archivo: Sistema registra para limpieza posterior
- **Post-condiciones:**
  - Placa eliminada del sistema
  - Archivo físico removido del servidor
  - Espacio de almacenamiento liberado
- **Requisitos:**
  - Confirmación obligatoria
  - Eliminación completa (BD + archivo)
  - Operación irreversible
- **Observaciones:**
  - Eliminación por consideraciones de privacidad médica

---

## 📱 CASOS DE USO DE WHATSAPP

### **CU-22: Crear Plantilla de Mensaje**
- **Nombre:** Diseñar Template Reutilizable
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite crear plantillas de mensajes WhatsApp con variables para uso recurrente
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Acceso al módulo WhatsApp
- **Flujo Principal:**
  1. Usuario accede a "Plantillas WhatsApp"
  2. Usuario selecciona "Crear Plantilla"
  3. Sistema muestra formulario de plantilla
  4. Usuario ingresa nombre descriptivo
  5. Usuario selecciona categoría (recordatorio, confirmación, pago, etc.)
  6. Usuario redacta contenido con variables {nombre}, {fecha}, etc.
  7. Sistema detecta variables automáticamente
  8. Usuario puede agregar descripción de uso
  9. Sistema valida contenido y variables
  10. Sistema guarda plantilla como activa
  11. Sistema muestra confirmación
- **Flujo Alternativo:**
  - **9a.** Variables inválidas: Sistema sugiere correcciones
- **Post-condiciones:**
  - Plantilla creada y disponible
  - Variables detectadas y documentadas
  - Template listo para uso
- **Requisitos:**
  - Nombre único por plantilla
  - Detección automática de variables
  - Categorización obligatoria
- **Observaciones:**
  - Plantillas aceleran comunicación con pacientes

### **CU-23: Enviar Mensaje Individual**
- **Nombre:** Comunicación Directa con Paciente
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite enviar mensaje WhatsApp a un paciente específico
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Paciente debe tener número WhatsApp válido
- **Flujo Principal:**
  1. Usuario accede a "Enviar Mensaje"
  2. Usuario selecciona paciente destinatario
  3. Sistema verifica número WhatsApp del paciente
  4. Usuario puede seleccionar plantilla existente
  5. Si usa plantilla, sistema reemplaza variables automáticamente
  6. Usuario puede personalizar mensaje
  7. Usuario confirma envío
  8. Sistema envía mensaje via WhatsApp API
  9. Sistema registra mensaje en conversación
  10. Sistema actualiza estado de envío
- **Flujo Alternativo:**
  - **3a.** Sin número WhatsApp: Sistema solicita número
  - **8a.** Error de envío: Sistema reintenta y marca error
  - **4a.** Sin plantilla: Usuario redacta mensaje libre
- **Post-condiciones:**
  - Mensaje enviado al paciente
  - Conversación actualizada
  - Estado de entrega monitoreado
- **Requisitos:**
  - Validación de números WhatsApp
  - Integración con WhatsApp Business API
  - Tracking de estados de mensaje
- **Observaciones:**
  - Fundamental para comunicación efectiva con pacientes

### **CU-24: Configurar Automatización**
- **Nombre:** Programar Envíos Automáticos
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite configurar reglas para envío automático de mensajes basados en eventos
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Plantillas de mensaje disponibles
- **Flujo Principal:**
  1. Usuario accede a "Automatizaciones"
  2. Usuario selecciona "Crear Automatización"
  3. Sistema muestra formulario de configuración
  4. Usuario define nombre y descripción
  5. Usuario selecciona tipo de evento (cita próxima, pago vencido, etc.)
  6. Usuario configura condiciones específicas
  7. Usuario selecciona plantilla de mensaje
  8. Usuario define audiencia objetivo
  9. Usuario establece límites de envío
  10. Sistema valida configuración
  11. Sistema activa automatización
- **Flujo Alternativo:**
  - **10a.** Configuración inválida: Sistema muestra errores específicos
- **Post-condiciones:**
  - Automatización configurada y activa
  - Envíos programados según condiciones
  - Límites de envío respetados
- **Requisitos:**
  - Motor de reglas configurable
  - Validación de condiciones lógicas
  - Control de límites de envío
- **Observaciones:**
  - Reduce trabajo manual y mejora comunicación sistemática

### **CU-25: Consultar Conversaciones**
- **Nombre:** Ver Historial de Comunicación
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite consultar el historial completo de conversaciones WhatsApp con pacientes
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Conversaciones existentes en el sistema
- **Flujo Principal:**
  1. Usuario accede a "Conversaciones"
  2. Sistema muestra lista de conversaciones activas
  3. Sistema ordena por última actividad
  4. Usuario puede buscar por nombre de paciente
  5. Usuario selecciona conversación específica
  6. Sistema muestra historial completo de mensajes
  7. Sistema marca mensajes como leídos
  8. Usuario puede responder directamente
- **Flujo Alternativo:**
  - **2a.** Sin conversaciones: Sistema muestra pantalla vacía
  - **4a.** Búsqueda sin resultados: Sistema muestra mensaje
- **Post-condiciones:**
  - Historial de conversación desplegado
  - Mensajes marcados como leídos
  - Opción de respuesta habilitada
- **Requisitos:**
  - Historial completo y cronológico
  - Búsqueda eficiente
  - Estados de mensaje actualizados
- **Observaciones:**
  - Central para mantener comunicación fluida con pacientes

---

## 👨‍💼 CASOS DE USO DE GESTIÓN DE USUARIOS

### **CU-26: Crear Usuario del Sistema**
- **Nombre:** Registrar Nuevo Usuario
- **Rol:** Administrador
- **Descripción:** Permite crear nuevos usuarios del sistema (dentistas o recepcionistas)
- **Actores:** Administrador
- **Pre-condiciones:**
  - Usuario autenticado como administrador
  - Acceso al módulo de usuarios
- **Flujo Principal:**
  1. Administrador accede a "Gestión de Usuarios"
  2. Administrador selecciona "Crear Usuario"
  3. Sistema muestra formulario de registro
  4. Administrador ingresa nombre de usuario único
  5. Administrador ingresa nombre completo
  6. Administrador selecciona rol (dentista/recepcionista)
  7. Administrador establece contraseña temporal
  8. Sistema valida unicidad de usuario
  9. Sistema encripta contraseña con bcrypt
  10. Sistema crea usuario con estado activo
  11. Sistema muestra confirmación con credenciales
- **Flujo Alternativo:**
  - **8a.** Usuario ya existe: Sistema solicita nombre diferente
  - **7a.** Contraseña débil: Sistema solicita contraseña más segura
- **Post-condiciones:**
  - Usuario creado en el sistema
  - Credenciales disponibles para primer acceso
  - Permisos asignados según rol
- **Requisitos:**
  - Nombre de usuario único
  - Contraseña mínimo 8 caracteres
  - Roles válidos (dentista/recepcionista)
- **Observaciones:**
  - Contraseñas deben cambiarse en primer acceso

### **CU-27: Gestionar Estado de Usuario**
- **Nombre:** Activar/Desactivar Usuario
- **Rol:** Administrador
- **Descripción:** Permite activar o desactivar usuarios del sistema
- **Actores:** Administrador
- **Pre-condiciones:**
  - Usuario autenticado como administrador
  - Usuario objetivo debe existir
- **Flujo Principal:**
  1. Administrador accede a lista de usuarios
  2. Administrador localiza usuario específico
  3. Administrador selecciona "Cambiar Estado"
  4. Sistema muestra estado actual
  5. Sistema solicita confirmación del cambio
  6. Administrador confirma acción
  7. Sistema actualiza estado del usuario
  8. Si desactivación, sistema invalida sesiones activas
  9. Sistema muestra confirmación
- **Flujo Alternativo:**
  - **6a.** Administrador cancela: Sistema mantiene estado actual
  - **8a.** Error al invalidar sesiones: Sistema registra para procesamiento
- **Post-condiciones:**
  - Estado de usuario actualizado
  - Sesiones invalidadas si fue desactivado
  - Cambio reflejado en el sistema
- **Requisitos:**
  - Confirmación obligatoria
  - Invalidación de sesiones activas
  - Protección contra desactivación del último administrador
- **Observaciones:**
  - Desactivación no elimina datos, solo previene acceso

### **CU-28: Actualizar Información de Usuario**
- **Nombre:** Modificar Datos de Usuario
- **Rol:** Administrador
- **Descripción:** Permite actualizar información de usuarios existentes
- **Actores:** Administrador
- **Pre-condiciones:**
  - Usuario autenticado como administrador
  - Usuario objetivo debe existir
- **Flujo Principal:**
  1. Administrador localiza usuario a modificar
  2. Administrador selecciona "Editar Usuario"
  3. Sistema muestra formulario con datos actuales
  4. Administrador puede modificar nombre, rol
  5. Administrador puede cambiar contraseña (opcional)
  6. Sistema valida cambios realizados
  7. Sistema actualiza información
  8. Si cambio de contraseña, sistema encripta nueva contraseña
  9. Sistema muestra confirmación
- **Flujo Alternativo:**
  - **6a.** Datos inválidos: Sistema muestra errores específicos
  - **5a.** Nueva contraseña débil: Sistema solicita contraseña segura
- **Post-condiciones:**
  - Información de usuario actualizada
  - Nuevos permisos aplicados si cambió rol
  - Contraseña actualizada si fue modificada
- **Requisitos:**
  - Validación de unicidad en cambios
  - Encriptación de nuevas contraseñas
  - Validación de roles válidos
- **Observaciones:**
  - Cambios de rol afectan permisos inmediatamente

### **CU-29: Consultar Estadísticas de Sistema**
- **Nombre:** Ver Métricas del Sistema
- **Rol:** Administrador
- **Descripción:** Permite consultar estadísticas generales del uso del sistema
- **Actores:** Administrador
- **Pre-condiciones:**
  - Usuario autenticado como administrador
  - Datos suficientes en el sistema
- **Flujo Principal:**
  1. Administrador accede a "Estadísticas"
  2. Sistema calcula métricas actuales
  3. Sistema muestra total de usuarios activos
  4. Sistema muestra total de pacientes registrados
  5. Sistema muestra citas del período actual
  6. Sistema muestra ingresos del período
  7. Sistema muestra estadísticas de uso por módulo
  8. Administrador puede filtrar por período
- **Flujo Alternativo:**
  - **2a.** Error en cálculos: Sistema muestra métricas parciales
- **Post-condiciones:**
  - Estadísticas desplegadas correctamente
  - Información actualizada disponible
- **Requisitos:**
  - Cálculos en tiempo real
  - Filtros por período
  - Información precisa y actualizada
- **Observaciones:**
  - Información crítica para toma de decisiones

---

## 📊 CASOS DE USO DE REPORTES

### **CU-30: Generar Reporte PDF de Pacientes**
- **Nombre:** Exportar Lista de Pacientes en PDF
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite generar y descargar un reporte en PDF con la lista completa de pacientes
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Pacientes registrados en el sistema
- **Flujo Principal:**
  1. Usuario accede al módulo "Ver Pacientes"
  2. Usuario aplica filtros deseados (edad, fecha registro, etc.)
  3. Usuario selecciona "Exportar PDF"
  4. Sistema genera documento PDF con datos filtrados
  5. Sistema incluye información: nombre, teléfono, edad, última visita
  6. Sistema aplica formato profesional con logo de la clínica
  7. Sistema incluye fecha y hora de generación
  8. Sistema inicia descarga automática del archivo
  9. Sistema registra la exportación en logs de auditoría
- **Flujo Alternativo:**
  - **4a.** Sin pacientes para exportar: Sistema muestra mensaje informativo
  - **8a.** Error en generación: Sistema permite reintentar
- **Post-condiciones:**
  - Archivo PDF generado y descargado
  - Exportación registrada en auditoría
- **Requisitos:**
  - Librería jsPDF integrada
  - Formato profesional consistente
  - Filtros aplicados correctamente
- **Observaciones:**
  - Útil para reportes administrativos y auditorías externas

### **CU-31: Generar Reporte Financiero de Pagos**
- **Nombre:** Exportar Resumen Financiero en PDF/Excel
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite generar reportes financieros detallados con estadísticas de pagos
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Datos de pagos en el sistema
- **Flujo Principal:**
  1. Usuario accede a "Gestión de Pagos"
  2. Sistema muestra dashboard con resumen financiero
  3. Usuario selecciona período de análisis
  4. Usuario puede filtrar por modalidad de pago o estado
  5. Usuario selecciona "Exportar Reporte"
  6. Sistema genera reporte con métricas financieras:
     - Total ingresos del período
     - Pagos pendientes por cobrar
     - Cuotas vencidas
     - Distribución por modalidad de pago
     - Top 10 pacientes con mayor deuda
  7. Sistema incluye gráficos y tablas detalladas
  8. Sistema ofrece exportación en PDF o Excel
  9. Usuario descarga reporte en formato seleccionado
- **Flujo Alternativo:**
  - **6a.** Sin datos en período: Sistema genera reporte vacío con mensaje
  - **8a.** Error en generación: Sistema permite seleccionar formato alternativo
- **Post-condiciones:**
  - Reporte financiero completo generado
  - Métricas calculadas correctamente
  - Archivo descargado en formato solicitado
- **Requisitos:**
  - Cálculos financieros precisos
  - Múltiples formatos de exportación
  - Gráficos y visualizaciones claras
- **Observaciones:**
  - Información crítica para gestión financiera y contabilidad

### **CU-32: Generar Reporte de Citas por Período**
- **Nombre:** Exportar Estadísticas de Agenda
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite generar reportes detallados sobre las citas del consultorio con métricas de eficiencia
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Citas registradas en el sistema
- **Flujo Principal:**
  1. Usuario accede al módulo "Calendario de Citas"
  2. Usuario selecciona "Generar Reporte de Citas"
  3. Sistema solicita parámetros de filtro:
     - Rango de fechas
     - Dentista específico (opcional)
     - Estado de citas
  4. Sistema genera análisis estadístico:
     - Total de citas programadas
     - Citas atendidas vs. canceladas
     - Tasa de ausentismo por período
     - Promedio de citas por día
     - Distribución por horarios
     - Motivos de consulta más frecuentes
  5. Sistema crea reporte visual con gráficos
  6. Usuario puede exportar en PDF con diseño profesional
  7. Sistema incluye recomendaciones para optimización
- **Flujo Alternativo:**
  - **4a.** Período sin citas: Sistema muestra reporte con mensaje informativo
  - **6a.** Error en exportación: Sistema ofrece vista web del reporte
- **Post-condiciones:**
  - Reporte de citas generado con estadísticas
  - Análisis de eficiencia disponible
  - Recomendaciones para mejora incluidas
- **Requisitos:**
  - Cálculos estadísticos automatizados
  - Visualizaciones gráficas integradas
  - Formato de reporte profesional
- **Observaciones:**
  - Ayuda a optimizar la gestión de agenda y recursos

### **CU-33: Generar Reporte de Tratamientos por Paciente**
- **Nombre:** Exportar Historial Clínico Completo
- **Rol:** Dentista
- **Descripción:** Permite generar reportes PDF del historial clínico completo de pacientes específicos
- **Actores:** Dentista
- **Pre-condiciones:**
  - Usuario autenticado como dentista
  - Paciente con historial clínico registrado
- **Flujo Principal:**
  1. Dentista accede al perfil del paciente
  2. Dentista selecciona "Generar Reporte Médico"
  3. Sistema compila información clínica:
     - Datos personales y médicos del paciente
     - Historial completo de tratamientos
     - Observaciones y evolución clínica
     - Placas dentales adjuntas
     - Fechas de todas las consultas
  4. Sistema aplica formato médico profesional
  5. Sistema incluye firma digital del dentista
  6. Sistema genera PDF con confidencialidad médica
  7. Dentista puede imprimir o enviar digitalmente
- **Flujo Alternativo:**
  - **3a.** Paciente sin historial: Sistema genera reporte básico con datos personales
  - **6a.** Error en generación: Sistema permite generación parcial
- **Post-condiciones:**
  - Reporte médico completo generado
  - Confidencialidad médica preservada
  - Documento con validez clínica
- **Requisitos:**
  - Acceso restringido solo a dentistas
  - Formato médico profesional
  - Protección de datos médicos sensibles
- **Observaciones:**
  - Útil para referencias médicas, segundas opiniones y documentación legal

### **CU-34: Dashboard de Métricas en Tiempo Real**
- **Nombre:** Visualizar Indicadores de Rendimiento del Consultorio
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite consultar métricas y KPIs del consultorio en tiempo real sin necesidad de exportación
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Datos suficientes en el sistema para cálculos
- **Flujo Principal:**
  1. Usuario accede al dashboard principal
  2. Sistema calcula automáticamente métricas clave:
     - Ingresos del mes actual vs. anterior
     - Número de pacientes activos
     - Citas programadas próximos 7 días
     - Cuotas por vencer esta semana
     - Tasa de ocupación de agenda
     - Tratamientos activos vs. finalizados
  3. Sistema presenta información en cards visuales
  4. Sistema actualiza métricas cada vez que se accede
  5. Usuario puede hacer clic en métricas para ver detalles
  6. Sistema permite filtrar por período personalizado
- **Flujo Alternativo:**
  - **2a.** Datos insuficientes: Sistema muestra mensaje con instrucciones
  - **4a.** Error en cálculos: Sistema muestra métricas parciales disponibles
- **Post-condiciones:**
  - Métricas actualizadas desplegadas
  - Información de gestión disponible
  - Navegación detallada habilitada
- **Requisitos:**
  - Cálculos eficientes en tiempo real
  - Interfaz visual intuitiva
  - Navegación hacia módulos específicos
- **Observaciones:**
  - Funciona como centro de control para gestión diaria del consultorio

---

## � CASOS DE USO DE AUDITORÍA Y SEGURIDAD

### **CU-35: Registrar Logs de Seguridad**
- **Nombre:** Registrar Eventos de Seguridad
- **Rol:** Sistema (Automático)
- **Descripción:** El sistema registra automáticamente todos los eventos de seguridad críticos
- **Actores:** Sistema
- **Pre-condiciones:**
  - Sistema operativo
  - Canal de logging configurado
- **Flujo Principal:**
  1. Sistema detecta evento de seguridad
  2. Sistema determina criticidad del evento
  3. Sistema registra en canal de seguridad dedicado
  4. Sistema incluye metadata (IP, user agent, timestamp)
  5. Sistema almacena log con nivel de severidad
- **Flujo Alternativo:**
  - **3a.** Error de logging: Sistema intenta backup en log general
- **Post-condiciones:**
  - Evento registrado en logs de seguridad
  - Información disponible para auditoría
- **Requisitos:**
  - Canal de logging dedicado
  - Formato estructurado de logs
  - Retención de logs por período requerido
- **Observaciones:**
  - Crítico para cumplimiento de normativas de seguridad

### **CU-36: Auditar Operaciones Críticas**
- **Nombre:** Registro de Auditoría de Operaciones
- **Rol:** Sistema (Automático)
- **Descripción:** Sistema registra automáticamente todas las operaciones críticas del negocio
- **Actores:** Sistema
- **Pre-condiciones:**
  - Usuario autenticado realizando operación
  - Operación clasificada como crítica
- **Flujo Principal:**
  1. Sistema detecta operación crítica
  2. Sistema captura contexto completo (usuario, datos, timestamp)
  3. Sistema registra en canal de auditoría
  4. Sistema incluye datos antes y después (cuando aplica)
  5. Sistema almacena con hash para integridad
- **Flujo Alternativo:**
  - **3a.** Error de auditoría: Sistema marca operación para revisión manual
- **Post-condiciones:**
  - Operación auditada completamente
  - Rastro de auditoría disponible
- **Requisitos:**
  - Identificación de operaciones críticas
  - Captura de contexto completo
  - Integridad de logs de auditoría
- **Observaciones:**
  - Incluye creación/modificación de pacientes, pagos, tratamientos

### **CU-37: Rastrear Intentos de Login Fallidos**
- **Nombre:** Monitoreo de Accesos Fallidos
- **Rol:** Sistema (Automático)
- **Descripción:** Sistema rastrea y responde a intentos de login fallidos para prevenir ataques
- **Actores:** Sistema
- **Pre-condiciones:**
  - Usuario intentando autenticarse
  - Credenciales incorrectas proporcionadas
- **Flujo Principal:**
  1. Sistema detecta credenciales incorrectas
  2. Sistema incrementa contador por IP
  3. Sistema registra intento en logs de seguridad
  4. Sistema evalúa si excede límite (5 intentos)
  5. Si excede límite, sistema aplica rate limiting
  6. Sistema registra bloqueo temporal
- **Flujo Alternativo:**
  - **5a.** Login exitoso: Sistema resetea contador de intentos
- **Post-condiciones:**
  - Intento fallido registrado
  - Rate limiting aplicado si es necesario
- **Requisitos:**
  - Contador por IP address
  - Límites configurables
  - Bloqueo temporal automático
- **Observaciones:**
  - Previene ataques de fuerza bruta efectivamente

### **CU-38: Gestionar Rate Limiting por IP**
- **Nombre:** Control de Límites de Peticiones
- **Rol:** Sistema (Automático)
- **Descripción:** Sistema controla automáticamente la cantidad de peticiones por IP para prevenir abuso
- **Actores:** Sistema
- **Pre-condiciones:**
  - Petición HTTP entrante
  - Rate limiting configurado por endpoint
- **Flujo Principal:**
  1. Sistema recibe petición HTTP
  2. Sistema identifica IP origen
  3. Sistema consulta contador de peticiones
  4. Sistema evalúa límites por endpoint
  5. Si dentro de límites, sistema procesa petición
  6. Sistema actualiza contadores
- **Flujo Alternativo:**
  - **5a.** Límite excedido: Sistema retorna error 429 y tiempo de espera
- **Post-condiciones:**
  - Petición procesada o rechazada
  - Contadores actualizados
- **Requisitos:**
  - Límites diferenciados por endpoint
  - Contadores por IP y endpoint
  - Configuración flexible de límites
- **Observaciones:**
  - Login: 5/min, API general: 60/min, Admin: 30/min

### **CU-39: Verificar Integridad de Sesiones**
- **Nombre:** Validación de Sesiones Activas
- **Rol:** Sistema (Automático)
- **Descripción:** Sistema verifica continuamente la integridad y validez de las sesiones activas
- **Actores:** Sistema
- **Pre-condiciones:**
  - Usuario con sesión activa
  - Petición que requiere autenticación
- **Flujo Principal:**
  1. Sistema recibe petición autenticada
  2. Sistema valida existencia de sesión
  3. Sistema verifica tiempo de expiración (1 hora)
  4. Sistema valida integridad de datos de sesión
  5. Sistema actualiza timestamp de última actividad
  6. Sistema procesa petición
- **Flujo Alternativo:**
  - **3a.** Sesión expirada: Sistema invalida sesión y solicita re-autenticación
  - **4a.** Datos corruptos: Sistema invalida sesión por seguridad
- **Post-condiciones:**
  - Sesión validada o invalidada
  - Última actividad actualizada
- **Requisitos:**
  - Timeout configurable de sesiones
  - Validación de integridad de datos
  - Limpieza automática de sesiones expiradas
- **Observaciones:**
  - Sesiones se auto-invalidan después de 1 hora de inactividad

---

## 📱 CASOS DE USO DE WHATSAPP AVANZADO

### **CU-40: Programar Envíos Automáticos**
- **Nombre:** Configurar Envíos Programados
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite programar mensajes WhatsApp para envío en fechas/horas específicas
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Plantilla de mensaje disponible
  - Destinatarios definidos
- **Flujo Principal:**
  1. Usuario accede a "Envíos Programados"
  2. Usuario selecciona "Programar Envío"
  3. Sistema muestra formulario de programación
  4. Usuario selecciona plantilla de mensaje
  5. Usuario define destinatarios (individual/grupo)
  6. Usuario establece fecha y hora de envío
  7. Usuario configura recurrencia (opcional)
  8. Sistema valida configuración
  9. Sistema crea registro en cola de envíos
  10. Sistema programa tarea para ejecución
- **Flujo Alternativo:**
  - **8a.** Fecha/hora inválida: Sistema solicita corrección
  - **10a.** Error en programación: Sistema permite reprogramar
- **Post-condiciones:**
  - Envío programado en cola
  - Tarea programada en sistema
- **Requisitos:**
  - Sistema de colas de trabajo
  - Validación de fechas futuras
  - Gestión de recurrencias
- **Observaciones:**
  - Permite automatizar recordatorios y seguimientos

### **CU-41: Gestionar Variables Dinámicas**
- **Nombre:** Procesar Variables en Plantillas
- **Rol:** Sistema (Automático)
- **Descripción:** Sistema reemplaza automáticamente variables en plantillas con datos reales del paciente
- **Actores:** Sistema
- **Pre-condiciones:**
  - Plantilla con variables definidas
  - Datos del paciente disponibles
  - Mensaje a enviar
- **Flujo Principal:**
  1. Sistema recibe solicitud de envío de plantilla
  2. Sistema identifica variables en plantilla {nombre}, {fecha}, etc.
  3. Sistema busca datos correspondientes del paciente
  4. Sistema reemplaza cada variable con valor real
  5. Sistema valida que no queden variables sin reemplazar
  6. Sistema genera mensaje final personalizado
- **Flujo Alternativo:**
  - **4a.** Variable sin datos: Sistema usa valor por defecto o placeholder
  - **5a.** Variables no encontradas: Sistema alerta al usuario
- **Post-condiciones:**
  - Mensaje personalizado generado
  - Variables reemplazadas correctamente
- **Requisitos:**
  - Sintaxis estándar de variables
  - Mapeo de variables a campos de datos
  - Valores por defecto configurables
- **Observaciones:**
  - Variables soportadas: {nombre}, {fecha}, {hora}, {dentista}, {telefono}

### **CU-42: Monitorear Estadísticas de Envío**
- **Nombre:** Analizar Métricas de Comunicación
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite consultar estadísticas detalladas sobre envíos de mensajes WhatsApp
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Histórico de envíos disponible
- **Flujo Principal:**
  1. Usuario accede a "Estadísticas WhatsApp"
  2. Sistema presenta dashboard de métricas
  3. Sistema muestra total de mensajes enviados
  4. Sistema calcula tasa de entrega
  5. Sistema presenta estadísticas por plantilla
  6. Sistema muestra evolución temporal
  7. Usuario puede filtrar por período o tipo
  8. Sistema genera gráficos e informes visuales
- **Flujo Alternativo:**
  - **2a.** Sin datos históricos: Sistema muestra mensaje informativo
- **Post-condiciones:**
  - Estadísticas desplegadas
  - Información disponible para análisis
- **Requisitos:**
  - Tracking de estados de mensaje
  - Cálculos estadísticos precisos
  - Visualización gráfica de datos
- **Observaciones:**
  - Incluye métricas de efectividad por plantilla y automatización

### **CU-43: Duplicar y Reutilizar Plantillas**
- **Nombre:** Gestión Avanzada de Plantillas
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite duplicar plantillas existentes y reutilizarlas con modificaciones
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Plantilla existente para duplicar
- **Flujo Principal:**
  1. Usuario localiza plantilla a duplicar
  2. Usuario selecciona "Duplicar Plantilla"
  3. Sistema crea copia de la plantilla original
  4. Sistema asigna nuevo nombre (Original + "Copia")
  5. Sistema permite editar plantilla duplicada
  6. Usuario modifica contenido según necesidades
  7. Sistema actualiza contador de usos
  8. Sistema guarda nueva plantilla
- **Flujo Alternativo:**
  - **4a.** Nombre duplicado: Sistema agrega número secuencial
- **Post-condiciones:**
  - Nueva plantilla creada
  - Plantilla original preservada
  - Contador de usos actualizado
- **Requisitos:**
  - Clonado completo de plantillas
  - Gestión de nombres únicos
  - Preservación de configuraciones
- **Observaciones:**
  - Facilita creación de variaciones de mensajes exitosos

---

## 💾 CASOS DE USO DE GESTIÓN DE DATOS

### **CU-44: Gestionar Cache del Sistema**
- **Nombre:** Administración de Cache
- **Rol:** Sistema (Automático)
- **Descripción:** Sistema gestiona automáticamente el cache para optimizar rendimiento
- **Actores:** Sistema
- **Pre-condiciones:**
  - Sistema operativo
  - Cache configurado
- **Flujo Principal:**
  1. Sistema recibe petición de datos
  2. Sistema verifica existencia en cache
  3. Si existe y es válido, sistema retorna datos del cache
  4. Si no existe, sistema consulta fuente original
  5. Sistema almacena resultado en cache
  6. Sistema establece tiempo de expiración
  7. Sistema retorna datos al solicitante
- **Flujo Alternativo:**
  - **3a.** Cache expirado: Sistema refresca datos automáticamente
  - **5a.** Cache lleno: Sistema aplica algoritmo LRU para liberar espacio
- **Post-condiciones:**
  - Datos servidos eficientemente
  - Cache actualizado apropiadamente
- **Requisitos:**
  - Algoritmo de expiración
  - Gestión de memoria cache
  - Invalidación automática
- **Observaciones:**
  - Mejora significativamente el rendimiento de consultas frecuentes

### **CU-45: Administrar Sesiones Persistentes**
- **Nombre:** Gestión de Sesiones en Base de Datos
- **Rol:** Sistema (Automático)
- **Descripción:** Sistema mantiene sesiones de usuario de forma persistente en base de datos
- **Actores:** Sistema
- **Pre-condiciones:**
  - Base de datos operativa
  - Tabla de sesiones configurada
- **Flujo Principal:**
  1. Sistema recibe nueva sesión de usuario
  2. Sistema genera ID único de sesión
  3. Sistema almacena datos de sesión en BD
  4. Sistema establece timestamp de expiración
  5. Sistema asocia sesión con usuario
  6. Sistema mantiene sesión activa
- **Flujo Alternativo:**
  - **4a.** Sesión expirada: Sistema limpia registro automáticamente
  - **6a.** Error de BD: Sistema fallback a sesiones en memoria
- **Post-condiciones:**
  - Sesión persistida en base de datos
  - Usuario mantiene estado entre peticiones
- **Requisitos:**
  - Tabla de sesiones en BD
  - Limpieza automática de sesiones expiradas
  - Tolerancia a fallos
- **Observaciones:**
  - Permite continuidad de sesión ante reinicios del servidor

### **CU-46: Registrar Detalles de Auditoría de Pagos**
- **Nombre:** Auditoría Detallada de Transacciones
- **Rol:** Sistema (Automático)
- **Descripción:** Sistema registra automáticamente todos los detalles de transacciones de pago
- **Actores:** Sistema
- **Pre-condiciones:**
  - Transacción de pago ejecutándose
  - Sistema de auditoría operativo
- **Flujo Principal:**
  1. Sistema detecta transacción de pago
  2. Sistema captura todos los detalles (monto, fecha, usuario)
  3. Sistema registra en tabla de detalle_pagos
  4. Sistema incluye metadata de la transacción
  5. Sistema genera hash de integridad
  6. Sistema vincula con registro principal de pago
- **Flujo Alternativo:**
  - **5a.** Error en generación de hash: Sistema usa timestamp como fallback
- **Post-condiciones:**
  - Detalle completo de pago registrado
  - Rastro de auditoría disponible
- **Requisitos:**
  - Tabla de detalles separada
  - Hash de integridad para validación
  - Vinculación con pagos principales
- **Observaciones:**
  - Crítico para auditorías financieras y cumplimiento normativo

---

## 📊 CASOS DE USO DE REPORTES AVANZADOS

### **CU-47: Generar Estadísticas de Sistema Completas**
- **Nombre:** Dashboard de Métricas del Sistema
- **Rol:** Administrador
- **Descripción:** Permite generar reportes completos sobre el uso y rendimiento del sistema
- **Actores:** Administrador
- **Pre-condiciones:**
  - Usuario autenticado como administrador
  - Datos suficientes en el sistema
- **Flujo Principal:**
  1. Administrador accede a "Estadísticas del Sistema"
  2. Sistema calcula métricas en tiempo real
  3. Sistema presenta total de usuarios activos
  4. Sistema muestra estadísticas de pacientes
  5. Sistema calcula citas por período
  6. Sistema presenta ingresos totales
  7. Sistema muestra uso por módulo
  8. Sistema genera gráficos de tendencias
  9. Administrador puede exportar reportes
- **Flujo Alternativo:**
  - **2a.** Error en cálculos: Sistema muestra métricas parciales disponibles
- **Post-condiciones:**
  - Estadísticas completas desplegadas
  - Reportes disponibles para exportación
- **Requisitos:**
  - Cálculos en tiempo real
  - Múltiples formatos de exportación
  - Visualización gráfica avanzada
- **Observaciones:**
  - Información crítica para toma de decisiones estratégicas

### **CU-48: Reportar Actividad de Usuarios**
- **Nombre:** Análisis de Actividad por Usuario
- **Rol:** Administrador
- **Descripción:** Permite generar reportes detallados sobre la actividad de cada usuario del sistema
- **Actores:** Administrador
- **Pre-condiciones:**
  - Usuario autenticado como administrador
  - Logs de actividad disponibles
- **Flujo Principal:**
  1. Administrador accede a "Reportes de Actividad"
  2. Sistema muestra lista de usuarios
  3. Administrador selecciona usuario(s) específico(s)
  4. Administrador define período de análisis
  5. Sistema genera reporte de actividad
  6. Sistema incluye login/logout times
  7. Sistema muestra operaciones realizadas
  8. Sistema calcula tiempo de uso del sistema
  9. Administrador puede comparar usuarios
- **Flujo Alternativo:**
  - **5a.** Sin actividad en período: Sistema muestra reporte vacío
- **Post-condiciones:**
  - Reporte de actividad generado
  - Comparaciones disponibles
- **Requisitos:**
  - Tracking detallado de actividades
  - Cálculos de tiempo de uso
  - Comparaciones entre usuarios
- **Observaciones:**
  - Útil para evaluación de productividad y uso del sistema

### **CU-49: Analizar Efectividad de Comunicaciones WhatsApp**
- **Nombre:** Análisis de ROI de Comunicaciones
- **Rol:** Dentista, Recepcionista
- **Descripción:** Permite analizar la efectividad de las comunicaciones WhatsApp en resultados del negocio
- **Actores:** Dentista, Recepcionista
- **Pre-condiciones:**
  - Usuario autenticado
  - Histórico de mensajes y resultados disponible
- **Flujo Principal:**
  1. Usuario accede a "Análisis de Efectividad"
  2. Sistema correlaciona mensajes con resultados
  3. Sistema calcula tasa de respuesta por tipo de mensaje
  4. Sistema analiza impacto en asistencia a citas
  5. Sistema mide efectividad de recordatorios
  6. Sistema calcula ROI de comunicaciones
  7. Sistema presenta recomendaciones de mejora
  8. Usuario puede filtrar por período y tipo
- **Flujo Alternativo:**
  - **2a.** Datos insuficientes: Sistema sugiere período mínimo para análisis
- **Post-condiciones:**
  - Análisis de efectividad completado
  - Recomendaciones disponibles
- **Requisitos:**
  - Correlación de datos de mensajes y resultados
  - Cálculos de ROI
  - Algoritmos de recomendación
- **Observaciones:**
  - Permite optimizar estrategias de comunicación con pacientes

---

## �📋 MATRIZ DE CASOS DE USO POR ACTOR

| Caso de Uso | Dentista | Recepcionista | Administrador | Sistema |
|-------------|----------|---------------|---------------|---------|
| CU-01: Iniciar Sesión | ✅ | ✅ | ✅ | ❌ |
| CU-02: Cerrar Sesión | ✅ | ✅ | ✅ | ❌ |
| CU-03: Registrar Paciente | ❌ | ✅ | ✅ | ❌ |
| CU-04: Consultar Paciente | ✅ | ✅ | ✅ | ❌ |
| CU-05: Actualizar Paciente | ❌ | ✅ | ✅ | ❌ |
| CU-06: Agendar Cita | ❌ | ✅ | ✅ | ❌ |
| CU-07: Gestionar Estado de Cita | ✅ | ✅ | ✅ | ❌ |
| CU-08: Consultar Agenda | ✅ | ✅ | ✅ | ❌ |
| CU-09: Registrar Tratamiento | ✅ | ❌ | ✅ | ❌ |
| CU-10: Agregar Observación | ✅ | ❌ | ✅ | ❌ |
| CU-11: Finalizar Tratamiento | ✅ | ❌ | ✅ | ❌ |
| CU-12: Consultar Historial Clínico | ✅ | ❌ | ✅ | ❌ |
| CU-13: Registrar Pago Único | ✅ | ✅ | ✅ | ❌ |
| CU-14: Configurar Cuotas Fijas | ✅ | ✅ | ✅ | ❌ |
| CU-15: Registrar Pago de Cuota | ✅ | ✅ | ✅ | ❌ |
| CU-16: Configurar Cuotas Variables | ✅ | ✅ | ✅ | ❌ |
| CU-17: Generar Reporte de Pagos | ✅ | ✅ | ✅ | ❌ |
| CU-18: Subir Placa Dental | ✅ | ❌ | ✅ | ❌ |
| CU-19: Visualizar Placas | ✅ | ❌ | ✅ | ❌ |
| CU-20: Actualizar Placa | ✅ | ❌ | ✅ | ❌ |
| CU-21: Eliminar Placa | ✅ | ❌ | ✅ | ❌ |
| CU-22: Crear Plantilla WhatsApp | ✅ | ✅ | ✅ | ❌ |
| CU-23: Enviar Mensaje Individual | ✅ | ✅ | ✅ | ❌ |
| CU-24: Configurar Automatización | ✅ | ✅ | ✅ | ❌ |
| CU-25: Consultar Conversaciones | ✅ | ✅ | ✅ | ❌ |
| CU-26: Crear Usuario | ❌ | ❌ | ✅ | ❌ |
| CU-27: Gestionar Estado Usuario | ❌ | ❌ | ✅ | ❌ |
| CU-28: Actualizar Usuario | ❌ | ❌ | ✅ | ❌ |
| CU-29: Consultar Estadísticas | ❌ | ❌ | ✅ | ❌ |
| CU-30: Reporte PDF de Pacientes | ✅ | ✅ | ✅ | ❌ |
| CU-31: Reporte Financiero de Pagos | ✅ | ✅ | ✅ | ❌ |
| CU-32: Reporte de Citas por Período | ✅ | ✅ | ✅ | ❌ |
| CU-33: Reporte de Tratamientos por Paciente | ✅ | ❌ | ✅ | ❌ |
| CU-34: Dashboard de Métricas en Tiempo Real | ✅ | ✅ | ✅ | ❌ |
| **CU-35: Registrar Logs de Seguridad** | ❌ | ❌ | ❌ | ✅ |
| **CU-36: Auditar Operaciones Críticas** | ❌ | ❌ | ❌ | ✅ |
| **CU-37: Rastrear Intentos Login Fallidos** | ❌ | ❌ | ❌ | ✅ |
| **CU-38: Gestionar Rate Limiting** | ❌ | ❌ | ❌ | ✅ |
| **CU-39: Verificar Integridad Sesiones** | ❌ | ❌ | ❌ | ✅ |
| **CU-40: Programar Envíos Automáticos** | ✅ | ✅ | ✅ | ❌ |
| **CU-41: Gestionar Variables Dinámicas** | ❌ | ❌ | ❌ | ✅ |
| **CU-42: Monitorear Estadísticas Envío** | ✅ | ✅ | ✅ | ❌ |
| **CU-43: Duplicar Plantillas** | ✅ | ✅ | ✅ | ❌ |
| **CU-44: Gestionar Cache Sistema** | ❌ | ❌ | ❌ | ✅ |
| **CU-45: Administrar Sesiones Persistentes** | ❌ | ❌ | ❌ | ✅ |
| **CU-46: Auditoría Detalles Pagos** | ❌ | ❌ | ❌ | ✅ |
| **CU-47: Estadísticas Sistema Completas** | ❌ | ❌ | ✅ | ❌ |
| **CU-48: Reportar Actividad Usuarios** | ❌ | ❌ | ✅ | ❌ |
| **CU-49: Analizar Efectividad WhatsApp** | ✅ | ✅ | ✅ | ❌ |

---

## 🎯 PRIORIZACIÓN DE CASOS DE USO

### **🔴 Prioridad Alta (Implementación Inmediata) - ✅ COMPLETADO**
- CU-01, CU-02: Autenticación básica
- CU-03, CU-04, CU-05: Gestión de pacientes
- CU-06, CU-07, CU-08: Sistema de citas
- CU-13: Pagos básicos
- CU-35, CU-36, CU-37, CU-38, CU-39: Seguridad automática

### **🟡 Prioridad Media (Segunda Fase) - ✅ COMPLETADO**
- CU-09, CU-10, CU-11, CU-12: Tratamientos completos
- CU-14, CU-15, CU-16: Sistema de cuotas
- CU-18, CU-19: Placas dentales básicas
- CU-26, CU-27: Gestión de usuarios
- CU-44, CU-45, CU-46: Gestión de datos automática

### **🟢 Prioridad Baja (Tercera Fase) - ✅ COMPLETADO**
- CU-22, CU-23, CU-24, CU-25: WhatsApp completo
- CU-20, CU-21: Gestión avanzada de placas
- CU-17, CU-30, CU-31, CU-32, CU-33, CU-34: Reportes y dashboard
- CU-28, CU-29: Administración avanzada

### **🚀 Funcionalidades Avanzadas (Cuarta Fase) - ✅ COMPLETADO**
- CU-40, CU-41, CU-42, CU-43: WhatsApp avanzado
- CU-47, CU-48, CU-49: Análisis avanzados y métricas empresariales

---

## 📊 MÉTRICAS DE CASOS DE USO

- **Total de Casos de Uso:** 49
- **Por Actor:**
  - Dentista: 25 casos de uso
  - Recepcionista: 23 casos de uso  
  - Administrador: 35 casos de uso
  - Sistema (Automático): 10 casos de uso
- **Por Módulo:**
  - Autenticación: 2 casos
  - Pacientes: 3 casos
  - Citas: 3 casos
  - Tratamientos: 4 casos
  - Pagos: 5 casos
  - Placas: 4 casos
  - WhatsApp Básico: 4 casos
  - WhatsApp Avanzado: 4 casos
  - Usuarios: 4 casos
  - **Reportes y Dashboard: 5 casos**
  - Reportes Avanzados: 3 casos
  - Auditoría y Seguridad: 5 casos
  - Gestión de Datos: 3 casos

---

## 🔗 DEPENDENCIAS ENTRE CASOS DE USO

### **Dependencias Críticas:**
- CU-01 (Login) → Todos los casos de usuario
- CU-35, CU-36, CU-37, CU-38, CU-39 → Casos automáticos de seguridad
- CU-03 (Registrar Paciente) → CU-06 (Agendar Cita)
- CU-06 (Agendar Cita) → CU-09 (Registrar Tratamiento)
- CU-09 (Registrar Tratamiento) → CU-13 (Registrar Pago)

### **Dependencias Funcionales:**
- CU-22 (Crear Plantilla) → CU-23 (Enviar Mensaje) → CU-40 (Programar Envíos)
- CU-41 (Variables Dinámicas) → CU-23, CU-40 (Envíos de mensajes)
- CU-26 (Crear Usuario) → CU-01 (Iniciar Sesión)
- CU-44, CU-45, CU-46 → Casos automáticos de gestión de datos

### **Dependencias de Reportes:**
- CU-30 (Reporte PDF Pacientes) → CU-03, CU-04 (Datos de pacientes)
- CU-31 (Reporte Financiero) → CU-13, CU-14, CU-15 (Datos de pagos)
- CU-32 (Reporte Citas) → CU-06, CU-07 (Datos de citas)
- CU-33 (Reporte Tratamientos) → CU-09, CU-10, CU-11 (Datos clínicos)
- CU-34 (Dashboard Métricas) → Todos los módulos de negocio

### **Dependencias de Análisis:**
- CU-42 (Estadísticas Envío) → CU-23, CU-40 (Envíos realizados)
- CU-47 (Estadísticas Sistema) → Todos los casos de negocio
- CU-48 (Actividad Usuarios) → CU-35, CU-36 (Logs de auditoría)
- CU-49 (Efectividad WhatsApp) → CU-23, CU-40, CU-42 (Datos de mensajería)

---

## 📝 OBSERVACIONES GENERALES

### **Consideraciones de Seguridad:**
- Todos los casos de uso de usuario requieren autenticación previa
- Sistema implementa casos de uso automáticos de seguridad (CU-35 a CU-39)
- Información médica solo accesible por dentistas
- Operaciones críticas requieren confirmación y auditoría automática
- Rate limiting implementado para prevenir ataques
- Logs de auditoría en operaciones sensibles con integridad garantizada

### **Consideraciones de Usabilidad:**
- Interfaz intuitiva para usuarios no técnicos
- Validación en tiempo real de formularios
- Mensajes de error claros y orientadores
- Flujos alternativos bien definidos
- Sistema de plantillas con variables dinámicas para facilitar comunicación

### **Consideraciones Técnicas:**
- API RESTful completa para todas las operaciones
- Validación en frontend y backend
- Transacciones para operaciones críticas
- Sistema de cache automático para optimización (CU-44)
- Sesiones persistentes en base de datos (CU-45)
- Respaldos automáticos de datos importantes
- Auditoría completa de transacciones financieras (CU-46)
- **Generación de reportes PDF con jsPDF integrado (CU-30 a CU-34)**

### **Consideraciones de Negocio:**
- **Sistema completo de reportes PDF para gestión administrativa**
- **Dashboard de métricas en tiempo real para control operativo**
- Sistema WhatsApp avanzado con automatizaciones y análisis de efectividad
- Reportes financieros detallados con exportación múltiple formato
- Sistema de pagos flexible con múltiples modalidades
- Análisis de ROI de comunicaciones con pacientes
- Métricas empresariales completas para optimización estratégica

### **Estado de Implementación:**
- **49 casos de uso completamente implementados**
- **Todas las fases de priorización completadas**
- **Sistema completo de reportes PDF y dashboard operativo**
- **Sistema en nivel de producción enterprise**
- **Funcionalidades automáticas operando en background**

---

*Documento generado por: **Andrés Núñez - NullDevs***  
*Análisis basado en: Código fuente completo, documentación técnica y análisis exhaustivo del sistema*  
*Fecha: 6 de octubre de 2025*  
*Versión: 2.0 - Análisis Completo*  
*Estado: Sistema DentalSync - Implementación Enterprise Completa*