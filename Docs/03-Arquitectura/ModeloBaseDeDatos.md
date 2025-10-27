# Modelo de Base de Datos – Sistema de Gestión de Consultorio Odontológico
**Autor: Andrés Núñez**

## Tablas Principales

### Tabla: usuarios
Almacena los usuarios del sistema (dentista y recepcionista).
```sql
CREATE TABLE usuarios (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    rol ENUM('dentista', 'recepcionista') NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso TIMESTAMP NULL,
    ip_ultimo_acceso VARCHAR(45) NULL,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Tabla: pacientes
Información básica y extendida de los pacientes.
```sql
CREATE TABLE pacientes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    fecha_nacimiento DATE NULL,
    direccion VARCHAR(500) NULL,
    ciudad VARCHAR(100) NULL,
    departamento VARCHAR(100) NULL,
    contacto_emergencia_nombre VARCHAR(255) NULL,
    contacto_emergencia_telefono VARCHAR(20) NULL,
    contacto_emergencia_relacion VARCHAR(50) NULL,
    motivo_consulta TEXT NULL,
    alergias TEXT NULL,
    observaciones TEXT NULL,
    ultima_visita DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Tabla: tratamientos
Registra los tratamientos realizados.
```sql
CREATE TABLE tratamientos (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    descripcion TEXT NOT NULL,
    fecha_inicio DATE NOT NULL,
    estado ENUM('activo', 'finalizado') NOT NULL DEFAULT 'activo',
    paciente_id BIGINT NOT NULL,
    usuario_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

### Tabla: historial_clinico
Historial de procedimientos y observaciones.
```sql
CREATE TABLE historial_clinico (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fecha_visita DATE NOT NULL,
    tratamiento TEXT NOT NULL,
    observaciones TEXT NULL,
    paciente_id BIGINT NOT NULL,
    tratamiento_id BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (tratamiento_id) REFERENCES tratamientos(id) ON DELETE SET NULL
);
```

### Tabla: citas
Control de turnos.
```sql
CREATE TABLE citas (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL,
    motivo TEXT NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada', 'atendida') NOT NULL DEFAULT 'pendiente',
    fecha_atendida DATETIME NULL,
    paciente_id BIGINT NOT NULL,
    usuario_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

### Tabla: pagos
Pagos realizados por los pacientes con sistema avanzado de gestión.
```sql
CREATE TABLE pagos (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fecha_pago DATE NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    descripcion TEXT NOT NULL,
    modalidad_pago ENUM('pago_unico', 'cuotas_fijas', 'cuotas_variables') DEFAULT 'pago_unico',
    monto_pagado DECIMAL(10,2) DEFAULT 0,
    saldo_restante DECIMAL(10,2) DEFAULT 0,
    total_cuotas INT NULL,
    estado_pago ENUM('pendiente', 'pagado_parcial', 'pagado_completo', 'vencido') DEFAULT 'pendiente',
    observaciones TEXT NULL,
    paciente_id BIGINT NOT NULL,
    usuario_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX modalidad_pago_index (modalidad_pago),
    INDEX estado_pago_index (estado_pago),
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

### Tabla: cuotas_pago
Desglose de pagos en cuotas.
```sql
CREATE TABLE cuotas_pago (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    pago_id BIGINT NOT NULL,
    numero_cuota INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    estado ENUM('pendiente', 'pagada') NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE CASCADE
);
```

### Tabla: placas_dentales
Registra placas realizadas con gestión de archivos.
```sql
CREATE TABLE placas_dentales (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    lugar VARCHAR(255) NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    archivo_url VARCHAR(500) NULL,
    paciente_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);
```

### Tabla: detalle_pagos
Detalles específicos de cada pago realizado.
```sql
CREATE TABLE detalle_pagos (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    pago_id BIGINT NOT NULL,
    fecha_detalle_pago DATE NOT NULL,
    monto_pagado DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque') NOT NULL,
    referencia VARCHAR(255) NULL,
    observaciones TEXT NULL,
    usuario_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (pago_id) REFERENCES pagos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

## Tablas del Sistema

### Tabla: sessions
Gestión de sesiones de Laravel.
```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL
);
```

### Tabla: cache
Sistema de caché de Laravel.
```sql
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
);
```

### Tabla: cache_locks
Bloqueos del sistema de caché.
```sql
CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);
```

## Tablas de WhatsApp Integration

### Tabla: whatsapp_conversaciones
```sql
CREATE TABLE whatsapp_conversaciones (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    paciente_id BIGINT NOT NULL,
    numero_telefono VARCHAR(20) NOT NULL,
    estado ENUM('activa', 'cerrada') DEFAULT 'activa',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);
```

### Tabla: whatsapp_mensajes
```sql
CREATE TABLE whatsapp_mensajes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    conversacion_id BIGINT NOT NULL,
    tipo ENUM('enviado', 'recibido') NOT NULL,
    contenido TEXT NOT NULL,
    timestamp_whatsapp TIMESTAMP NOT NULL,
    estado ENUM('enviado', 'entregado', 'leido', 'fallido') DEFAULT 'enviado',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (conversacion_id) REFERENCES whatsapp_conversaciones(id) ON DELETE CASCADE
);
```

### Tabla: whatsapp_plantillas
```sql
CREATE TABLE whatsapp_plantillas (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    variables JSON NULL,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Tabla: whatsapp_automatizaciones
```sql
CREATE TABLE whatsapp_automatizaciones (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    evento ENUM('cita_creada', 'cita_recordatorio', 'pago_vencido') NOT NULL,
    plantilla_id BIGINT NOT NULL,
    tiempo_anticipacion INT NULL,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (plantilla_id) REFERENCES whatsapp_plantillas(id) ON DELETE CASCADE
);
```

### Tabla: whatsapp_envios_programados
```sql
CREATE TABLE whatsapp_envios_programados (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    paciente_id BIGINT NOT NULL,
    plantilla_id BIGINT NOT NULL,
    fecha_programada TIMESTAMP NOT NULL,
    estado ENUM('programado', 'enviado', 'fallido') DEFAULT 'programado',
    intentos INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (plantilla_id) REFERENCES whatsapp_plantillas(id) ON DELETE CASCADE
);
```

## 📋 Explicación del Modelo de Base de Datos

### 🎯 Filosofía de Diseño

Este modelo de base de datos ha sido diseñado específicamente para un **sistema integral de gestión odontológica** con enfoque en la **escalabilidad**, **seguridad** y **funcionalidad avanzada**. El diseño sigue las mejores prácticas de Laravel y se adapta a las necesidades reales de un consultorio dental moderno.

### 🏗️ Arquitectura Relacional

#### **Núcleo Principal (Core Tables)**
- **`usuarios`** - Centro del sistema de autenticación y autorización
- **`pacientes`** - Entidad principal que conecta con todos los módulos
- **`citas`** - Gestión temporal y organizacional del consultorio

#### **Módulos Clínicos**
- **`tratamientos`** + **`historial_clinico`** - Gestión médica completa
- **`placas_dentales`** - Manejo de archivos multimedia especializados

#### **Módulo Financiero Avanzado**
- **`pagos`** + **`cuotas_pago`** + **`detalle_pagos`** - Sistema de 3 capas para gestión financiera completa

#### **Módulo de Comunicación**
- **5 tablas WhatsApp** - Sistema completo de mensajería automatizada

### 🔧 Decisiones Técnicas Clave

#### **1. Uso de BIGINT para IDs**
```sql
id BIGINT AUTO_INCREMENT PRIMARY KEY
```
**¿Por qué?** Laravel 8+ usa BIGINT por defecto. Soporta hasta 9.2 quintillones de registros, ideal para escalabilidad a largo plazo.

#### **2. Timestamps Automáticos**
```sql
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL
```
**¿Por qué?** Auditoría automática de Laravel. Esencial para trazabilidad y debugging en producción.

#### **3. ENUMs Específicos**
```sql
rol ENUM('dentista', 'recepcionista')
estado_pago ENUM('pendiente', 'pagado_parcial', 'pagado_completo', 'vencido')
```
**¿Por qué?** Control estricto de valores válidos a nivel de base de datos. Mejor rendimiento que JOINs con tablas de catálogo.

#### **4. Campos de Seguridad en Usuarios**
```sql
intentos_fallidos INT DEFAULT 0,
bloqueado_hasta TIMESTAMP NULL,
ip_ultimo_acceso VARCHAR(45) NULL
```
**¿Por qué?** Protección contra ataques de fuerza bruta y auditoría de accesos. IPv6 ready con VARCHAR(45).

#### **5. Sistema de Pagos Multi-Modal**
```sql
modalidad_pago ENUM('pago_unico', 'cuotas_fijas', 'cuotas_variables')
```
**¿Por qué?** Flexibilidad financiera total. Permite desde pagos únicos hasta planes de financiamiento complejos.

### 💡 Innovaciones del Modelo

#### **1. Gestión Avanzada de Contactos de Emergencia**
```sql
contacto_emergencia_nombre VARCHAR(255) NULL,
contacto_emergencia_telefono VARCHAR(20) NULL,
contacto_emergencia_relacion VARCHAR(50) NULL
```
**Justificación:** En emergencias médicas odontológicas, tener contactos inmediatos es crucial.

#### **2. Sistema de Archivos para Placas**
```sql
archivo_url VARCHAR(500) NULL
```
**Justificación:** Soporte para URLs largas de servicios cloud (AWS S3, Google Cloud) y paths locales extensos.

#### **3. WhatsApp Business Integration**
```sql
timestamp_whatsapp TIMESTAMP NOT NULL,
variables JSON NULL
```
**Justificación:** Sincronización exacta con WhatsApp Business API y soporte para plantillas dinámicas.

#### **4. Detalles de Pago Granulares**
```sql
metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque')
```
**Justificación:** Auditoría completa de transacciones para reportes contables y conciliación bancaria.

### 🔄 Relaciones y Integridad

#### **Cascadas Inteligentes**
- **ON DELETE CASCADE:** Cuando se elimina un paciente, se eliminan automáticamente sus citas, pagos y tratamientos
- **ON DELETE SET NULL:** Los historiales clínicos mantienen la información médica aunque se elimine el tratamiento asociado

#### **Índices Estratégicos**
```sql
INDEX modalidad_pago_index (modalidad_pago),
INDEX estado_pago_index (estado_pago)
```
**Justificación:** Optimización para reportes financieros frecuentes y dashboards en tiempo real.

### 🚀 Preparación para el Futuro

#### **Campos JSON para Metadatos**
```sql
variables JSON NULL
```
**Justificación:** Flexibilidad para agregar nuevos campos sin modificar estructura. Ideal para configuraciones dinámicas.

#### **Campos de Versioning Implícito**
Los `updated_at` permiten detectar cambios y implementar sistemas de sincronización entre dispositivos.

#### **Soporte Multi-Clínica**
La estructura permite extender fácilmente a múltiples clínicas agregando un campo `clinica_id` a las tablas principales.

### 📊 Métricas de Diseño

- **Total de Tablas:** 16 (9 principales + 3 sistema + 5 WhatsApp)
- **Relaciones Definidas:** 15 foreign keys
- **Campos con Índices:** 4 campos optimizados
- **Tipos de Datos:** 8 tipos diferentes (BIGINT, VARCHAR, TEXT, ENUM, DECIMAL, BOOLEAN, TIMESTAMP, JSON)
- **Campos de Auditoría:** 32 timestamps automáticos

### 🎯 Conclusión

Este modelo representa un **equilibrio perfecto** entre **funcionalidad**, **rendimiento** y **escalabilidad**. Está diseñado para soportar desde un consultorio individual hasta una cadena de clínicas dentales, con capacidades de comunicación modernas y gestión financiera avanzada.

**El resultado:** Una base de datos robusta que crece con el negocio y se adapta a las necesidades cambiantes del sector odontológico moderno.

---
*Documentación técnica generada por el equipo **NullDevs** - Proyecto DentalSync 2025*