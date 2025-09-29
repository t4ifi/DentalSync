# 📁 Scripts de Gestión de DentalSync

Esta carpeta contiene scripts para gestionar datos de la base de datos de DentalSync de forma interactiva.

## 🚀 Uso Rápido

### **Menú Principal:**
```bash
cd scripts
./menu.sh
```

### **Scripts Individuales:**
```bash
# Crear usuario
php crear-usuario.php

# Crear paciente  
php crear-paciente.php

# Crear datos de prueba
php crear-datos-prueba.php

# Listar datos existentes
php listar-datos.php
```

## 📋 Scripts Disponibles

### 1. **`menu.sh`** - Menú Principal
Script interactivo que permite acceder a todas las funcionalidades:
- ✅ Crear usuarios (admin, doctor, recepcionista, asistente)
- ✅ Crear pacientes
- ✅ Crear datos de prueba completos
- ✅ Listar todos los datos
- ✅ Limpiar base de datos
- ✅ Ejecutar migraciones

### 2. **`crear-usuario.php`** - Crear Usuarios
Crea usuarios del sistema con diferentes roles:
- **Administrador**: Acceso completo al sistema
- **Doctor**: Gestión de pacientes y tratamientos
- **Recepcionista**: Gestión de citas y pacientes
- **Asistente**: Asistencia en consultas

**Funcionalidades:**
- ✅ Validación de email único
- ✅ Validación de cédula única
- ✅ Contraseñas seguras (mínimo 8 caracteres)
- ✅ Especialidades para doctores
- ✅ Campos de seguridad automáticos

### 3. **`crear-paciente.php`** - Crear Pacientes
Registra nuevos pacientes con información completa:
- **Información básica**: Nombre, email, teléfono, cédula
- **Información personal**: Fecha nacimiento, género, dirección
- **Información médica**: Alergias, medicamentos, condiciones
- **Contacto emergencia**: Información de emergencia
- **Mutualista**: Información del seguro médico

### 4. **`crear-datos-prueba.php`** - Datos de Prueba
Crea un conjunto completo de datos para testing:

**Usuarios creados:**
- `admin@dentalsync.com` / `admin123`
- `doctor@dentalsync.com` / `doctor123`  
- `recepcion@dentalsync.com` / `recepcion123`

**5 Pacientes de ejemplo** con datos realistas

**8 Tratamientos dentales:**
- Limpieza Dental ($1,500)
- Empaste Composite ($2,500)
- Extracción Simple ($2,000)
- Corona Dental ($8,000)
- Blanqueamiento ($5,000)
- Endodoncia ($6,000)
- Implante Dental ($15,000)
- Ortodoncia mensual ($3,000)

### 5. **`listar-datos.php`** - Explorador de Datos
Sistema interactivo para ver todos los datos:
- **👨‍⚕️ Usuarios**: Lista todos los usuarios con roles
- **👥 Pacientes**: Lista pacientes con edad calculada
- **🦷 Tratamientos**: Organizados por categorías
- **📅 Citas**: Últimas 20 citas con relaciones
- **💰 Pagos**: Últimos 20 pagos con totales
- **📊 Estadísticas**: Resumen general del sistema

## 🔧 Requisitos

- **PHP 8.2+** con extensiones Laravel
- **Base de datos** configurada (SQLite o MariaDB)
- **Conexión** a la base de datos funcionando
- **Migraciones** ejecutadas

## 💡 Ejemplos de Uso

### **Configuración inicial completa:**
```bash
cd scripts
./menu.sh
# Seleccionar opción 3: Crear datos de prueba
# Seleccionar opción 4: Listar datos para verificar
```

### **Crear usuario administrador:**
```bash
php crear-usuario.php
# Seguir el asistente interactivo
```

### **Ver estadísticas del sistema:**
```bash
php listar-datos.php
# Seleccionar opción 6: Estadísticas generales
```

## 🚨 Notas Importantes

### **Seguridad:**
- Las contraseñas se almacenan hasheadas con bcrypt
- Validación de datos únicos (email, cédula)
- Campos de seguridad automáticos para usuarios

### **Base de Datos:**
- Scripts verifican que las migraciones estén ejecutadas
- Manejo de errores de conexión
- Transacciones para operaciones complejas

### **Interactividad:**
- Menús coloridos y fáciles de usar
- Validación de entrada en tiempo real
- Mensajes de error descriptivos
- Confirmaciones para operaciones destructivas

## 🔍 Troubleshooting

### **Error: "Could not open input file"**
```bash
# Asegúrate de estar en el directorio correcto
cd /ruta/a/DentalSync/scripts
```

### **Error: "Base table or view not found"**
```bash
# Ejecutar migraciones primero
./menu.sh
# Seleccionar opción 6: Ejecutar migraciones
```

### **Error de conexión a base de datos**
```bash
# Verificar .env en directorio raíz
# Verificar que MariaDB esté corriendo
docker ps | grep mariadb
```

## 📈 Flujo Recomendado

1. **Configurar base de datos** (migraciones)
2. **Crear datos de prueba** (usuarios, pacientes, tratamientos)
3. **Verificar datos** (listar para confirmar)
4. **Crear usuarios reales** según necesidades
5. **Usar sistema web** con datos poblados

---

**¡Feliz gestión de datos! 🦷✨**