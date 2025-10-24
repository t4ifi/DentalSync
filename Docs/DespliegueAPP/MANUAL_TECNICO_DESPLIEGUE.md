# 📘 Manual Técnico - DentalSync

**Versión:** 1.0.0  
**Fecha:** 24 de octubre de 2025  
**Sistema:** DentalSync - Sistema de Gestión Dental  

---

## 📋 Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Requerimientos del Sistema](#requerimientos-del-sistema)
3. [Arquitectura de la Aplicación](#arquitectura)
4. [Instalación](#instalacion)
5. [Configuración](#configuracion)
6. [Scripts Automatizados](#scripts)
7. [Uso en Desarrollo](#desarrollo)
8. [Troubleshooting](#troubleshooting)
9. [Mantenimiento](#mantenimiento)

---

## 📊 Resumen Ejecutivo {#resumen-ejecutivo}

DentalSync es un sistema integral de gestión dental desarrollado con:
- **Backend:** Laravel 12 + PHP 8.2
- **Frontend:** Vue.js 3.4.21 + Vite 5.0
- **Base de Datos:** MySQL/MariaDB 10.6+
- **Servidor:** Laravel Development Server + Vite Dev Server

**Características principales:**
- ✅ Gestión de pacientes y citas
- ✅ Sistema de pagos y cuotas
- ✅ Placas dentales con almacenamiento
- ✅ Integración WhatsApp Business API
- ✅ Gestión de usuarios con roles (Dentista/Recepcionista)
- ✅ Sistema de autenticación seguro
- ✅ Responsive design (móvil/tablet/desktop)

---

## 💻 Requerimientos del Sistema {#requerimientos-del-sistema}

### Requerimientos de Hardware

#### Desarrollo Local
- **CPU:** 2 cores
- **RAM:** 4 GB
- **Almacenamiento:** 10 GB

### Requerimientos de Software

#### Sistema Operativo
- ✅ **Ubuntu 22.04 LTS** (Recomendado)
- ✅ Ubuntu 20.04 LTS
- ✅ Debian 11/12
- ✅ macOS 12+ (Desarrollo)
- ✅ Windows 10/11 + WSL2 (Desarrollo)

#### Stack Requerido

##### PHP
```bash
Versión requerida: PHP 8.2+
Extensiones necesarias:
- php8.2-cli
- php8.2-mysql
- php8.2-xml
- php8.2-mbstring
- php8.2-curl
- php8.2-zip
- php8.2-gd
- php8.2-intl
- php8.2-bcmath
```

##### Composer
```bash
Versión: 2.6.0+
```

##### Node.js & NPM
```bash
Node.js: 20.x LTS
npm: 10.x+
```

##### Base de Datos
```bash
MySQL: 8.0+ (Recomendado)
MariaDB: 10.6+
```

##### Servidor Web
```bash
Opción 1: Apache 2.4+
  - mod_rewrite habilitado
  - mod_ssl habilitado
  
##### MariaDB/MySQL
```bash
MySQL: 8.0+
MariaDB: 10.6+ (Recomendado)
```

---

## 🏗️ Arquitectura de la Aplicación {#arquitectura}

### Estructura del Proyecto

```
DentalSync/
├── app/                        # Lógica del backend (Laravel)
│   ├── Http/
│   │   ├── Controllers/       # Controladores API
│   │   └── Middleware/        # Middlewares de seguridad
│   └── Models/                # Modelos Eloquent
│
├── config/                    # Configuraciones Laravel
│   ├── app.php
│   ├── database.php
│   └── services.php
│
├── database/
│   ├── migrations/           # Migraciones de BD
│   └── seeders/              # Datos iniciales
│
├── public/                   # Punto de entrada web
│   ├── index.php
│   └── js/                   # Assets compilados
│
├── resources/
│   ├── js/                   # Frontend Vue.js
│   │   ├── components/
│   │   ├── services/
│   │   └── router.js
│   └── views/
│
├── routes/
│   ├── api.php              # Rutas API
│   └── web.php              # Rutas web
│
├── storage/                 # Archivos generados
│   ├── app/                # Subidas de usuarios (placas dentales)
│   ├── logs/               # Logs de aplicación
│   └── framework/          # Cache y sesiones
│
└── Docs/                    # Documentación
    ├── DespliegueAPP/      # Scripts y manuales
    └── ...
```

### Flujo de la Aplicación

```
Usuario → Vue.js (Frontend)
              ↓
    HTTP Request (Axios)
              ↓
    Laravel Router (api.php)
              ↓
    Middleware (Auth/CSRF)
              ↓
    Controllers
              ↓
    Models (Eloquent)
              ↓
    MariaDB/MySQL
```

### Puertos Utilizados

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| Laravel Dev | 8000 | Servidor de desarrollo Laravel |
| Vite Dev | 5173 | Hot Module Replacement (HMR) |
| MySQL | 3306 | Base de datos |

---

## 🚀 Instalación {#instalacion}

### Método 1: Script Automatizado (Recomendado)

El script automatizado instala todas las dependencias necesarias:

```bash
cd /ruta/a/DentalSync/Docs/DespliegueAPP/scripts
sudo chmod +x install.sh
sudo ./install.sh
```

**El script instalará:**
- PHP 8.2 + extensiones necesarias
- Composer 2.6+
- Node.js 20.x LTS + NPM
- MariaDB
- Configuración de base de datos

### Método 2: Instalación Manual

#### Paso 1: Actualizar el sistema

```bash
sudo apt update && sudo apt upgrade -y
```

#### Paso 2: Instalar PHP 8.2 y extensiones

```bash
# Agregar repositorio de PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instalar PHP y extensiones
sudo apt install -y \
    php8.2 \
    php8.2-cli \
    php8.2-mysql \
    php8.2-xml \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-intl \
    php8.2-bcmath

# Verificar instalación
php -v
```

#### Paso 3: Instalar Composer

```bash
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Verificar instalación
composer --version
```

#### Paso 4: Instalar Node.js y NPM

```bash
# Instalar Node.js 20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verificar instalación
node -v
npm -v
```

#### Paso 5: Instalar MariaDB

```bash
sudo apt install -y mariadb-server
sudo systemctl enable mariadb
sudo systemctl start mariadb
sudo mysql_secure_installation
```

**Configurar base de datos:**

```bash
sudo mysql -u root -p

# Dentro de MySQL
CREATE DATABASE dentalsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dentalsync_user'@'localhost' IDENTIFIED BY 'tu_contraseña';
GRANT ALL PRIVILEGES ON dentalsync.* TO 'dentalsync_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Paso 6: Clonar el proyecto

```bash
# Clonar desde GitHub (o copiar archivos)
git clone https://github.com/t4ifi/DentalSync.git
cd DentalSync
```

#### Paso 7: Instalar dependencias

```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias Node.js
npm install
```

#### Paso 8: Configurar el proyecto

```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Editar .env con tus credenciales
nano .env
```

**Configuración básica de `.env`:**

```env
APP_NAME=DentalSync
APP_ENV=local
APP_KEY=base64:... (generada automáticamente)
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dentalsync
DB_USERNAME=dentalsync_user
DB_PASSWORD=tu_contraseña
```

#### Paso 9: Migrar base de datos

```bash
php artisan migrate
```

#### Paso 10: Configurar permisos

```bash
chmod -R 775 storage bootstrap/cache
```

---

## 💻 Uso en Desarrollo {#desarrollo}

### Iniciar la aplicación

Una vez instaladas las dependencias y configurada la base de datos:

**Terminal 1 - Servidor Backend:**
```bash
php artisan serve
```

**Terminal 2 - Servidor Frontend:**
```bash
npm run dev
```

**Acceder a la aplicación:**
```
http://localhost:8000
```

### Comandos útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rutas disponibles
php artisan route:list

# Ejecutar migraciones
php artisan migrate

# Rollback migraciones
php artisan migrate:rollback

# Refrescar migraciones (CUIDADO: borra datos)
php artisan migrate:fresh

# Ver logs en tiempo real
tail -f storage/logs/laravel.log
```

---

## ⚙️ Configuración {#configuracion}

### Archivo .env

El archivo `.env` contiene todas las configuraciones importantes:

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `APP_NAME` | Nombre de la aplicación | DentalSync |
| `APP_ENV` | Entorno de ejecución | local / production |
| `APP_DEBUG` | Modo debug | true / false |
| `APP_URL` | URL base de la app | http://localhost:8000 |
| `DB_CONNECTION` | Tipo de BD | mysql |
| `DB_HOST` | Host de BD | 127.0.0.1 |
| `DB_PORT` | Puerto de BD | 3306 |
| `DB_DATABASE` | Nombre de BD | dentalsync |
| `DB_USERNAME` | Usuario de BD | dentalsync_user |
| `DB_PASSWORD` | Contraseña de BD | tu_contraseña |

### Configuración de PHP (Opcional)

Para archivos de placas dentales más grandes, ajustar:

**Archivo:** `/etc/php/8.2/cli/php.ini`

```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 60
date.timezone = America/Montevideo
```

---

## 🤖 Scripts Automatizados {#scripts}

Los scripts están ubicados en: `/Docs/DespliegueAPP/scripts/`

### Scripts disponibles:

| Script | Descripción | Uso |
|--------|-------------|-----|
| `install.sh` | Instalación automatizada de dependencias | `sudo ./install.sh` |
| `setup-database.sh` | Configurar base de datos | `sudo ./setup-database.sh` |
| `backup.sh` | Backup de BD y archivos | `./backup.sh` |
| `deploy.sh` | Actualizar aplicación | `./deploy.sh` |
| `restore.sh` | Restaurar desde backup | `sudo ./restore.sh` |
| `health-check.sh` | Verificar estado del sistema | `./health-check.sh` |

**Uso básico:**

```bash
cd /ruta/a/DentalSync/Docs/DespliegueAPP/scripts

# Dar permisos de ejecución
chmod +x *.sh

# Ejecutar instalación (solo la primera vez)
sudo ./install.sh

# Hacer backup
./backup.sh

# Verificar salud del sistema
./health-check.sh
```

---

## ✅ Verificación {#verificacion}

### Checklist Post-Instalación

**1. Verificar servicios:**
```bash
# MariaDB activo
sudo systemctl status mariadb

# PHP instalado
php -v
```

**2. Verificar conexión a BD:**
```bash
php artisan migrate:status
```

**3. Verificar permisos:**
```bash
ls -la storage
ls -la bootstrap/cache
```

**4. Acceder a la aplicación:**
```
http://localhost:8000
```

**5. Verificar logs:**
```bash
tail -f storage/logs/laravel.log
```

---

## 🔧 Troubleshooting {#troubleshooting}

### Problemas Comunes

#### Error: "No application encryption key has been specified"

**Solución:**
```bash
php artisan key:generate
```

#### Error de conexión a la base de datos

**Síntomas:**
- SQLSTATE[HY000] [2002] Connection refused
- SQLSTATE[HY000] [1045] Access denied

**Solución:**
```bash
# 1. Verificar que MariaDB esté corriendo
sudo systemctl status mariadb
sudo systemctl start mariadb

# 2. Verificar credenciales en .env
cat .env | grep DB_

# 3. Probar conexión manualmente
mysql -u dentalsync_user -p dentalsync
```

#### Error: "Permission denied" en storage/

**Solución:**
```bash
chmod -R 775 storage bootstrap/cache
```

#### Vite no conecta (HMR)

**Síntomas:**
- Assets no cargan en desarrollo
- Error de conexión a localhost:5173

**Solución:**
```bash
# Detener proceso
# Limpiar y reinstalar
rm -rf node_modules package-lock.json
npm install
npm run dev
```

#### Migraciones fallan

**Solución:**
```bash
# Ver estado
php artisan migrate:status

# Hacer rollback y reintentar
php artisan migrate:rollback
php artisan migrate
```

php artisan migrate
```

---

## 🔄 Mantenimiento {#mantenimiento}

### Tareas de Mantenimiento

**Backups regulares:**
```bash
# Hacer backup de BD y archivos
cd Docs/DespliegueAPP/scripts
./backup.sh
```

**Limpieza de logs:**
```bash
# Limpiar logs antiguos (más de 30 días)
find storage/logs -name "*.log" -mtime +30 -delete
```

**Monitoreo de logs:**
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver errores recientes
tail -100 storage/logs/laravel.log | grep ERROR
```

**Optimización:**
```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar (en producción)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Actualizar la Aplicación

**Desde Git:**
```bash
git pull origin main
composer install
npm install
npm run build
php artisan migrate
php artisan cache:clear
```

**O usar el script:**
```bash
cd Docs/DespliegueAPP/scripts
./deploy.sh
```

### Verificar Salud del Sistema

```bash
cd Docs/DespliegueAPP/scripts
./health-check.sh
```

---

## 📝 Notas Adicionales

### Estructura de la Base de Datos

Tablas principales:
- `usuarios` - Usuarios del sistema (dentista/recepcionista)
- `pacientes` - Datos de pacientes
- `citas` - Citas programadas
- `tratamientos` - Tratamientos realizados
- `pagos` - Pagos y cuotas
- `placas_dentales` - Archivos de placas dentales
- `whatsapp_*` - Sistema de WhatsApp

### Datos de Prueba

Para crear datos de prueba:
```bash
cd scripts
php crear-datos-prueba.php
```

### Configuración de WhatsApp

La integración con WhatsApp requiere configuración adicional en `.env`:
```env
WHATSAPP_API_URL=tu_url_api
WHATSAPP_TOKEN=tu_token
```

---

## 📞 Soporte

**Documentación adicional:**
- `/Docs/` - Documentación completa del proyecto
- `README.md` - Guía rápida de inicio

**Repositorio:**
- GitHub: https://github.com/t4ifi/DentalSync

---

**Fin del Manual Técnico de Despliegue**

*Última actualización: 24/10/2025*
