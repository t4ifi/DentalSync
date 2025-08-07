# 🦷 DentalSync

## 🎓 Proyecto de Egreso - 3ro de Bachillerato

### 📋 Descripción

**DentalSync** es un sistema integral de gestión para consultorios dentales que será desarrollado como proyecto de egreso de 3ro de bachillerato por el equipo **NullDevs**. El sistema se construirá con **Laravel 12** y **Vue.js 3**, y permitirá a los dentistas gestionar pacientes, citas, tratamientos y más, con una interfaz moderna y funcional.

---

## 👥 Equipo NullDevs

| Rol | Integrante | Responsabilidades |
|-----|------------|-------------------|
| 🚀 | **Andrés Núñez** | Programador Full Stack & Líder del Proyecto |
| 💻 | **Lázaro Coronel** | Programador Full Stack |
| 🗄️ | **Adrián Martínez** | Encargado de Base de Datos |
| 📝 | **Florencia Passo** | Documentadora |
| 📋 | **Alison Silveira** | Documentadora |

---

## 🎯 Contexto Académico

- **Institución:** Bachillerato Tecnológico
- **Nivel:** 3er Año
- **Especialización:** Informática
- **Período:** 2025
- **Objetivo:** Desarrollar un sistema integral para gestión dental como proyecto de egreso

---

## 🛠️ Stack Tecnológico

### Backend
- **Laravel 12** - Framework PHP moderno
- **PHP 8.4+** - Lenguaje de programación principal
- **MySQL/MariaDB** - Sistema de gestión de base de datos
- **API RESTful** - Arquitectura de servicios web

### Frontend
- **Vue.js 3** - Framework JavaScript reactivo
- **Composition API** - Patrón moderno de Vue.js
- **Vue Router** - Enrutamiento del lado del cliente
- **Tailwind CSS** - Framework CSS de utilidades
- **BoxIcons** - Librería de iconos

### Herramientas de Desarrollo
- **Vite** - Bundler y servidor de desarrollo
- **Composer** - Gestión de dependencias PHP
- **NPM** - Gestión de dependencias JavaScript
- **Laravel Artisan** - CLI de Laravel

---

## ✨ Funcionalidades Planificadas

### 🏥 Gestión de Pacientes
- **Registro completo** de información personal y médica
- **Historial clínico** detallado por paciente
- **Búsqueda y filtrado** avanzado de pacientes
- **Validaciones** robustas en frontend y backend
- **API RESTful** para operaciones CRUD

### 📅 Sistema de Citas
- **Calendario interactivo** para programar citas
- **Estados de citas** (confirmada, pendiente, completada, cancelada)
- **Notificaciones** automáticas de recordatorios
- **Gestión de horarios** disponibles
- **Integración** con sistema de pacientes

### 🩺 Sistema de Tratamientos
- **Registro de tratamientos** realizados
- **Historial clínico** por paciente
- **Observaciones** y seguimiento médico
- **Planificación** de tratamientos futuros
- **Integración** con sistema de pagos

### 🦷 Gestión de Placas Dentales
- **Subida de placas** y radiografías
- **Soporte múltiples formatos**: JPG, JPEG, PNG, PDF
- **5 tipos de placas**: Panorámica, Periapical, Bitewing, Lateral, Oclusal
- **Almacenamiento seguro** con identificadores únicos
- **Asociación automática** con pacientes
- **Visualización** integrada en el historial

### 💰 Sistema de Pagos
- **3 modalidades de pago**:
  - 💰 **Pago Único**: Tratamientos pagados completamente
  - 📊 **Cuotas Fijas**: División en cuotas iguales
  - 🔄 **Cuotas Variables**: Pagos flexibles
- **Dashboard financiero** con métricas en tiempo real
- **Historial de pagos** por paciente
- **Gestión de cuotas** pendientes
- **Reportes financieros** detallados

### 👨‍💻 Sistema de Usuarios
- **Gestión completa de usuarios** (CRUD)
- **2 roles diferenciados**: Dentista y Recepcionista
- **Permisos granulares** por rol
- **Gestión de contraseñas** con encriptación
- **Estados de usuario** (activo/inactivo)
- **Interfaz moderna** con filtros y estadísticas

### 📱 Sistema de Integración WhatsApp
- **Gestión de conversaciones** con pacientes
- **Envío y recepción de mensajes** desde la app
- **Plantillas de mensajes** reutilizables
- **Automatizaciones básicas** (recordatorios, pagos, etc.)
- **Integración futura con WhatsApp Business API**

---

## 🎯 Objetivos del Proyecto

### Objetivos Principales
1. **Digitalizar** la gestión de consultorios dentales
2. **Centralizar** la información de pacientes y tratamientos
3. **Automatizar** procesos administrativos
4. **Mejorar** la experiencia tanto de profesionales como pacientes
5. **Implementar** un sistema seguro y confiable

### Objetivos Académicos
1. **Aplicar** conocimientos de programación full-stack
2. **Demostrar** competencias en frameworks modernos
3. **Implementar** buenas prácticas de desarrollo
4. **Trabajar** en equipo de manera colaborativa
5. **Documentar** el proceso de desarrollo

---

## 🚀 Instalación y Configuración

### Prerrequisitos

- **PHP 8.4+** con extensiones: `mbstring`, `openssl`, `pdo`, `mysql`
- **Composer 2.8+**
- **Node.js 18+** y **NPM**
- **MySQL/MariaDB 10.6+**

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   cd DentalSync
   ```

2. **Instalar dependencias PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias JavaScript**
   ```bash
   npm install
   ```

4. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos**
   - Editar `.env` con credenciales de base de datos
   - Ejecutar migraciones:
   ```bash
   php artisan migrate
   ```

6. **Cargar datos de prueba**
   ```bash
   php artisan db:seed
   ```

7. **Configurar storage**
   ```bash
   php artisan storage:link
   ```

8. **Compilar assets**
   ```bash
   npm run build
   ```

9. **Iniciar servidor de desarrollo**
   ```bash
   php artisan serve
   npm run dev
   ```

---

## 📋 Plan de Desarrollo

### Fase 1: Configuración Inicial
- Configuración del entorno de desarrollo
- Estructura base del proyecto Laravel y Vue.js
- Configuración de base de datos
- Sistema de autenticación básico

### Fase 2: Módulos Principales
- Gestión de pacientes
- Sistema de citas
- Gestión de usuarios y roles

### Fase 3: Funcionalidades Avanzadas
- Sistema de tratamientos
- Gestión de placas dentales
- Sistema de pagos

### Fase 4: Optimización y Testing
- Pruebas de funcionalidad
- Optimización de rendimiento
- Documentación final
- Preparación para presentación

---

## 📞 Contacto

**Equipo NullDevs**  
Bachillerato Tecnológico - Informática  
Año: 2025

---

*Proyecto desarrollado con ❤️ por el equipo NullDevs como proyecto de egreso de 3ro de Bachillerato*
