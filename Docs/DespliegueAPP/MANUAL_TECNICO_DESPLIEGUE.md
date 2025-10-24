# 📘 Manual Técnico de Despliegue - DentalSync

**Versión:** 1.0.0  
**Fecha:** 24 de octubre de 2025  
**Sistema:** DentalSync - Sistema de Gestión Dental  

---

## 📋 Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Requerimientos del Sistema](#requerimientos-del-sistema)
3. [Arquitectura de la Aplicación](#arquitectura)
4. [Instalación Paso a Paso](#instalacion)
5. [Configuración](#configuracion)
6. [Scripts de Despliegue Automatizado](#scripts)
7. [Verificación y Testing](#verificacion)
8. [Troubleshooting](#troubleshooting)
9. [Mantenimiento](#mantenimiento)
10. [Seguridad](#seguridad)

---

## 📊 Resumen Ejecutivo {#resumen-ejecutivo}

DentalSync es un sistema integral de gestión dental desarrollado con:
- **Backend:** Laravel 12 + PHP 8.2
- **Frontend:** Vue.js 3.4.21 + Vite 5.0
- **Base de Datos:** MySQL/MariaDB 10.6+
- **Servidor Web:** Apache/Nginx

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

#### Servidor de Producción (Mínimo)
- **CPU:** 2 cores (2.0 GHz+)
- **RAM:** 4 GB
- **Almacenamiento:** 20 GB SSD
- **Ancho de banda:** 100 Mbps

#### Servidor de Producción (Recomendado)
- **CPU:** 4 cores (2.5 GHz+)
- **RAM:** 8 GB
- **Almacenamiento:** 50 GB SSD
- **Ancho de banda:** 1 Gbps

#### Desarrollo Local
- **CPU:** 2 cores
- **RAM:** 4 GB
- **Almacenamiento:** 10 GB

### Requerimientos de Software

#### Sistema Operativo
- ✅ **Ubuntu 22.04 LTS** (Recomendado)
- ✅ Ubuntu 20.04 LTS
- ✅ Debian 11/12
- ✅ CentOS 8/9
- ✅ macOS 12+ (Desarrollo)
- ✅ Windows 10/11 + WSL2 (Desarrollo)

#### Stack de Servidor

##### PHP
```bash
Versión requerida: PHP 8.2+
Extensiones necesarias:
- php8.2-cli
- php8.2-fpm
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
  
Opción 2: Nginx 1.18+
  - Con soporte FastCGI
```

#### Herramientas Adicionales
```bash
- Git 2.30+
- SSL/TLS Certificates (Let's Encrypt recomendado)
- Supervisor (para colas de Laravel)
- Redis (opcional, para cache y sesiones)
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
│   ├── app/                # Subidas de usuarios
│   ├── logs/               # Logs de aplicación
│   └── framework/          # Cache y sesiones
│
├── Docker/                  # Configuración Docker
│   ├── docker-compose.dev.yml
│   └── Dockerfile.dev
│
└── Docs/                    # Documentación
    ├── DespliegueAPP/      # Este documento
    └── ...
```

### Diagrama de Flujo de Datos

```
Usuario → Nginx/Apache → PHP-FPM → Laravel Router
                                        ↓
                         ┌──────────────┼──────────────┐
                         ↓              ↓              ↓
                    Middleware    Controllers     API Routes
                         ↓              ↓              ↓
                    Auth/CSRF      Models        Database
                                      ↓
                                   MySQL/MariaDB
```

### Puertos Utilizados

| Servicio | Puerto | Descripción |
|----------|--------|-------------|
| HTTP | 80 | Tráfico web no seguro (redirige a 443) |
| HTTPS | 443 | Tráfico web seguro (SSL/TLS) |
| MySQL | 3306 | Base de datos |
| Vite Dev | 5173 | Servidor de desarrollo (solo local) |
| PHP-FPM | 9000 | FastCGI Process Manager |

---

## 🚀 Instalación Paso a Paso {#instalacion}

### Método 1: Instalación Manual (Producción)

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
    php8.2-fpm \
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

#### Paso 5: Instalar y configurar MySQL/MariaDB

```bash
# Opción A: MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Opción B: MariaDB (Recomendado)
sudo apt install -y mariadb-server
sudo mysql_secure_installation
```

**Configurar base de datos:**

```bash
sudo mysql -u root -p

# Dentro de MySQL
CREATE DATABASE dentalsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dentalsync_user'@'localhost' IDENTIFIED BY 'PASSWORD_SEGURA_AQUI';
GRANT ALL PRIVILEGES ON dentalsync.* TO 'dentalsync_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Paso 6: Instalar Nginx

```bash
sudo apt install -y nginx

# Habilitar e iniciar Nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

#### Paso 7: Clonar el repositorio

```bash
# Crear directorio de aplicaciones
sudo mkdir -p /var/www

# Clonar proyecto
cd /var/www
sudo git clone https://github.com/t4ifi/DentalSync.git dentalsync

# Cambiar propietario
sudo chown -R $USER:www-data /var/www/dentalsync
```

#### Paso 8: Instalar dependencias del proyecto

```bash
cd /var/www/dentalsync

# Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# Instalar dependencias Node.js
npm install

# Compilar assets para producción
npm run build
```

#### Paso 9: Configurar variables de entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar configuración
nano .env
```

**Configuración mínima requerida en `.env`:**

```env
APP_NAME=DentalSync
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dentalsync
DB_USERNAME=dentalsync_user
DB_PASSWORD=PASSWORD_SEGURA_AQUI

SESSION_DRIVER=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=stack
LOG_LEVEL=error
```

#### Paso 10: Generar clave de aplicación

```bash
php artisan key:generate
```

#### Paso 11: Ejecutar migraciones

```bash
# Ejecutar migraciones
php artisan migrate --force

# (Opcional) Ejecutar seeders para datos de prueba
php artisan db:seed
```

#### Paso 12: Configurar permisos

```bash
# Permisos para storage y cache
sudo chown -R www-data:www-data /var/www/dentalsync/storage
sudo chown -R www-data:www-data /var/www/dentalsync/bootstrap/cache

sudo chmod -R 775 /var/www/dentalsync/storage
sudo chmod -R 775 /var/www/dentalsync/bootstrap/cache
```

#### Paso 13: Configurar Nginx

```bash
sudo nano /etc/nginx/sites-available/dentalsync
```

**Contenido del archivo:**

```nginx
server {
    listen 80;
    server_name tu-dominio.com www.tu-dominio.com;
    
    # Redirigir a HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tu-dominio.com www.tu-dominio.com;
    
    root /var/www/dentalsync/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/tu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tu-dominio.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Logs
    access_log /var/log/nginx/dentalsync-access.log;
    error_log /var/log/nginx/dentalsync-error.log;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml|svg|woff|woff2|ttf)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

**Habilitar sitio:**

```bash
sudo ln -s /etc/nginx/sites-available/dentalsync /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Paso 14: Instalar SSL con Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com

# Verificar renovación automática
sudo certbot renew --dry-run
```

---

### Método 2: Instalación con Docker (Desarrollo)

```bash
cd /ruta/a/DentalSync

# Usar Docker Compose
docker-compose -f Docker/docker-compose.dev.yml up -d

# Ejecutar migraciones dentro del contenedor
docker exec -it dentalsync-app php artisan migrate

# Acceder a la aplicación
# http://localhost:8000
```

---

## ⚙️ Configuración {#configuracion}

### Variables de Entorno Importantes

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `APP_NAME` | Nombre de la aplicación | DentalSync |
| `APP_ENV` | Entorno de ejecución | production |
| `APP_DEBUG` | Modo debug (false en prod) | false |
| `APP_URL` | URL base de la app | https://dental.com |
| `DB_*` | Configuración de base de datos | Ver .env.example |
| `MAIL_*` | Configuración de correo | Ver .env.example |
| `SESSION_DRIVER` | Driver de sesiones | file / redis |
| `QUEUE_CONNECTION` | Driver de colas | sync / database |

### Configuración de PHP

**Archivo:** `/etc/php/8.2/fpm/php.ini`

```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 60
date.timezone = America/Montevideo
```

**Reiniciar PHP-FPM:**

```bash
sudo systemctl restart php8.2-fpm
```

### Optimización de Laravel

```bash
# Cache de configuración
php artisan config:cache

# Cache de rutas
php artisan route:cache

# Cache de vistas
php artisan view:cache

# Optimizar autoloader
composer dump-autoload -o
```

---

## 🤖 Scripts de Despliegue Automatizado {#scripts}

Los scripts están ubicados en: `/Docs/DespliegueAPP/scripts/`

### Scripts disponibles:

1. **`install.sh`** - Instalación completa automatizada
2. **`setup-database.sh`** - Configuración de base de datos
3. **`deploy.sh`** - Despliegue de actualizaciones
4. **`backup.sh`** - Backup de BD y archivos
5. **`restore.sh`** - Restaurar desde backup

**Uso:**

```bash
cd /var/www/dentalsync/Docs/DespliegueAPP/scripts

# Dar permisos de ejecución
chmod +x *.sh

# Ejecutar instalación
sudo ./install.sh
```

---

## ✅ Verificación y Testing {#verificacion}

### Checklist de Verificación Post-Instalación

```bash
# 1. Verificar servicios activos
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql

# 2. Verificar permisos
ls -la /var/www/dentalsync/storage
ls -la /var/www/dentalsync/bootstrap/cache

# 3. Verificar logs
tail -f /var/www/dentalsync/storage/logs/laravel.log
tail -f /var/log/nginx/dentalsync-error.log

# 4. Verificar conexión a BD
php artisan tinker
>>> DB::connection()->getPdo();

# 5. Test de rutas
php artisan route:list
```

### Tests Funcionales

```bash
# Ejecutar tests
php artisan test

# Test de conexión API
curl -I https://tu-dominio.com/api/login
```

### Verificación de Seguridad

```bash
# Verificar headers de seguridad
curl -I https://tu-dominio.com

# Verificar SSL
openssl s_client -connect tu-dominio.com:443 -servername tu-dominio.com
```

---

## 🔧 Troubleshooting {#troubleshooting}

### Problemas Comunes

#### Error 500 - Internal Server Error

**Causa:** Permisos incorrectos o error en código

**Solución:**
```bash
# Revisar logs
tail -100 /var/www/dentalsync/storage/logs/laravel.log

# Corregir permisos
sudo chown -R www-data:www-data /var/www/dentalsync/storage
sudo chmod -R 775 /var/www/dentalsync/storage
```

#### Error de conexión a la base de datos

**Solución:**
```bash
# Verificar servicio MySQL
sudo systemctl status mysql

# Verificar credenciales en .env
cat /var/www/dentalsync/.env | grep DB_

# Test de conexión
php artisan tinker
>>> DB::connection()->getPdo();
```

#### Assets no cargan (404)

**Solución:**
```bash
# Recompilar assets
cd /var/www/dentalsync
npm run build

# Limpiar cache
php artisan cache:clear
php artisan view:clear
```

#### Sesiones no persisten

**Solución:**
```bash
# Verificar permisos de storage
ls -la /var/www/dentalsync/storage/framework/sessions

# Regenerar clave de app
php artisan key:generate
```

---

## 🔄 Mantenimiento {#mantenimiento}

### Tareas Diarias

```bash
# Monitorear logs
tail -f /var/www/dentalsync/storage/logs/laravel.log

# Verificar espacio en disco
df -h
```

### Tareas Semanales

```bash
# Backup de base de datos
./Docs/DespliegueAPP/scripts/backup.sh

# Limpiar logs antiguos
find /var/www/dentalsync/storage/logs -name "*.log" -mtime +30 -delete

# Actualizar certificados SSL
sudo certbot renew
```

### Tareas Mensuales

```bash
# Actualizar dependencias
composer update
npm update

# Optimizar base de datos
php artisan db:optimize

# Revisar actualizaciones del sistema
sudo apt update && sudo apt upgrade
```

### Actualización de la Aplicación

```bash
# Ejecutar script de deploy
cd /var/www/dentalsync/Docs/DespliegueAPP/scripts
sudo ./deploy.sh
```

---

## 🔒 Seguridad {#seguridad}

### Checklist de Seguridad

- [ ] SSL/TLS habilitado (HTTPS)
- [ ] Firewall configurado (UFW/iptables)
- [ ] APP_DEBUG=false en producción
- [ ] Contraseñas seguras en .env
- [ ] Backups automáticos configurados
- [ ] Headers de seguridad en Nginx
- [ ] CSRF protection habilitado
- [ ] Rate limiting en API
- [ ] Logs de auditoría activos

### Configurar Firewall

```bash
# Habilitar UFW
sudo ufw enable

# Permitir SSH
sudo ufw allow 22/tcp

# Permitir HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Verificar estado
sudo ufw status
```

### Hardening de MySQL

```bash
# Configurar en /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
bind-address = 127.0.0.1
max_connections = 100
wait_timeout = 300
```

### Monitoreo

**Instalar herramientas de monitoreo:**

```bash
# Htop para monitoreo de recursos
sudo apt install htop

# Netdata para dashboard completo
bash <(curl -Ss https://my-netdata.io/kickstart.sh)
```

---

## 📞 Soporte

**Documentación adicional:**
- `/Docs/` - Documentación del proyecto
- `README.md` - Guía rápida

**Contacto:**
- Email: soporte@dentalsync.com
- GitHub: https://github.com/t4ifi/DentalSync

---

**Fin del Manual Técnico de Despliegue**

*Última actualización: 24/10/2025*
