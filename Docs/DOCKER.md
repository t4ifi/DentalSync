# 🐳 Docker - DentalSync

> **Guía completa para ejecutar DentalSync usando Docker**  
> **Autor:** Andrés Núñez - NullDevs  
> **Versión:** 1.0  

---

## 📋 **Requisitos Previos**

- **Docker:** Versión 20.10 o superior
- **Docker Compose:** Versión 2.0 o superior
- **Sistema Operativo:** Linux, macOS o Windows con WSL2
- **RAM:** Mínimo 2GB disponibles
- **Espacio en disco:** Mínimo 1GB disponibles

---

## 🚀 **Inicio Rápido**

### **1. Clonar el repositorio:**
```bash
git clone https://github.com/t4ifi/DentalSync.git
cd DentalSync
```

### **2. Ejecutar con Docker Compose (Modo Básico):**
```bash
# Construir y ejecutar la aplicación
docker-compose up -d

# Ver logs
docker-compose logs -f app

# Acceder a la aplicación
# http://localhost:8080
```

### **3. Detener la aplicación:**
```bash
docker-compose down
```

---

## 🔧 **Configuraciones de Despliegue**

### **🟢 Modo Desarrollo (SQLite)**
```bash
# Ejecutar solo la aplicación con SQLite
docker-compose up -d

# Aplicación disponible en: http://localhost:8080
```

### **🟡 Modo Producción (MySQL)**
```bash
# Ejecutar con base de datos MySQL
docker-compose --profile mysql up -d

# Configurar variables de entorno en .env:
# DB_CONNECTION=mysql
# DB_HOST=mysql
# DB_DATABASE=dentalsync
# DB_USERNAME=dentalsync
# DB_PASSWORD=dentalsync_password
```

### **🔴 Modo Producción Completo**
```bash
# Ejecutar con MySQL, Redis y Nginx
docker-compose --profile mysql --profile redis --profile nginx up -d

# Aplicación disponible en: http://localhost
# Con SSL: https://localhost
```

---

## 📂 **Estructura de Archivos Docker**

```
├── Dockerfile                 # Imagen principal de la aplicación
├── .dockerignore              # Archivos excluidos del build
├── docker-compose.yml         # Configuración de servicios
└── docker/                    # Configuraciones adicionales
    ├── apache/
    │   └── 000-default.conf    # Configuración Apache
    ├── mysql/
    │   └── init.sql           # Script inicial MySQL
    ├── nginx/
    │   ├── nginx.conf         # Configuración Nginx
    │   └── ssl/               # Certificados SSL
    └── scripts/
        └── init.sh            # Script de inicialización
```

---

## ⚙️ **Variables de Entorno**

### **Variables Principales:**
```env
APP_NAME=DentalSync
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8080

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

LOG_LEVEL=error
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### **Variables para MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=dentalsync
DB_USERNAME=dentalsync
DB_PASSWORD=dentalsync_password
```

### **Variables para Redis:**
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

---

## 🔨 **Comandos Útiles**

### **Gestión de Contenedores:**
```bash
# Ver estado de contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Reiniciar aplicación
docker-compose restart app

# Reconstruir imagen
docker-compose build --no-cache app

# Ejecutar comando dentro del contenedor
docker-compose exec app bash
```

### **Comandos Laravel dentro del contenedor:**
```bash
# Acceder al contenedor
docker-compose exec app bash

# Ejecutar migraciones
php artisan migrate

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Generar clave de aplicación
php artisan key:generate

# Ver rutas
php artisan route:list
```

### **Gestión de Base de Datos:**
```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Ejecutar seeders
docker-compose exec app php artisan db:seed

# Resetear base de datos
docker-compose exec app php artisan migrate:fresh --seed

# Backup de SQLite
docker cp dentalsync-app:/var/www/html/database/database.sqlite ./backup.sqlite
```

---

## 🔍 **Troubleshooting**

### **Problema: Puerto ocupado**
```bash
# Cambiar puerto en docker-compose.yml
ports:
  - "8081:80"  # Cambiar 8080 por 8081
```

### **Problema: Permisos de archivos**
```bash
# Ejecutar dentro del contenedor
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### **Problema: Base de datos no inicializada**
```bash
# Regenerar base de datos
docker-compose exec app rm -f /var/www/html/database/database.sqlite
docker-compose exec app touch /var/www/html/database/database.sqlite
docker-compose exec app php artisan migrate --force
```

### **Problema: Assets no compilados**
```bash
# Reconstruir imagen con assets
docker-compose build --no-cache app
```

---

## 📊 **Monitoreo y Logs**

### **Ver logs de la aplicación:**
```bash
# Logs de Apache
docker-compose exec app tail -f /var/log/apache2/error.log

# Logs de Laravel
docker-compose exec app tail -f storage/logs/laravel.log

# Logs de Docker
docker-compose logs -f app
```

### **Health Check:**
```bash
# Verificar estado de salud
curl http://localhost:8080/up

# Ver estado en Docker
docker-compose ps
```

---

## 🚀 **Despliegue en Producción**

### **1. Configuración de Seguridad:**
```bash
# Generar certificados SSL
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/dentalsync.key \
  -out docker/nginx/ssl/dentalsync.crt
```

### **2. Variables de Entorno de Producción:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
LOG_LEVEL=error
```

### **3. Ejecutar en Producción:**
```bash
# Modo producción completo
docker-compose --profile mysql --profile redis --profile nginx up -d

# Verificar que todos los servicios estén funcionando
docker-compose ps
```

---

## 📞 **Soporte**

**Desarrollador:** Andrés Núñez - NullDevs  
**Repositorio:** https://github.com/t4ifi/DentalSync  
**Documentación:** Ver carpeta `Docs/`  

---

*Esta configuración Docker está optimizada para facilitar el despliegue y desarrollo de DentalSync en cualquier entorno.*
