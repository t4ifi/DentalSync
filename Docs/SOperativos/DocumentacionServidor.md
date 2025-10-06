# 🖥️ DOCUMENTACIÓN DE SISTEMAS OPERATIVOS - DENTALSYNC

**Documentación Técnica del Servidor**  
*Proyecto: Sistema de Gestión para Consultorio Odontológico*  
*Equipo: NullDevs*  
*Fecha: 6 de octubre de 2025*

---

## 📋 ÍNDICE

1. [Sistemas Operativos](#sistemas-operativos)
2. [Sistema Operativo del Servidor](#sistema-operativo-del-servidor)
3. [Instalación y Configuración del Servidor](#instalación-y-configuración-del-servidor)
4. [Scripts de Mantenimiento del Servidor](#scripts-de-mantenimiento-del-servidor)

---

## 🖥️ SISTEMAS OPERATIVOS

### **Sistemas Operativos Soportados**

DentalSync ha sido diseñado para ser compatible con múltiples sistemas operativos, garantizando flexibilidad en el despliegue según las necesidades del consultorio.

#### **🐧 Linux (Recomendado para Producción)**
- **Ubuntu Server 22.04 LTS** ⭐ *Recomendado*
- **Ubuntu Server 20.04 LTS**
- **Debian 11 (Bullseye)**
- **CentOS 8 / Rocky Linux 8**
- **Red Hat Enterprise Linux 8**

**Características:**
- ✅ Máxima estabilidad y seguridad
- ✅ Excelente rendimiento para servidores web
- ✅ Soporte completo para PHP 8.2+ y MariaDB
- ✅ Actualizaciones de seguridad regulares
- ✅ Menor consumo de recursos

#### **🪟 Windows Server**
- **Windows Server 2022** 
- **Windows Server 2019**
- **Windows Server 2016**

**Características:**
- ✅ Familiar para administradores Windows
- ✅ Integración con Active Directory
- ✅ Soporte nativo para IIS
- ⚠️ Mayor consumo de recursos
- ⚠️ Licencias comerciales requeridas

#### **🍎 macOS (Desarrollo)**
- **macOS Monterey 12+**
- **macOS Big Sur 11+**

**Características:**
- ✅ Ideal para desarrollo y testing
- ✅ Excelente para equipos de desarrollo Mac
- ❌ No recomendado para producción
- ❌ Limitaciones de licencia para servidores

### **Requisitos Mínimos del Sistema**

#### **Hardware Mínimo:**
```
Procesador: 2 núcleos @ 2.0 GHz
Memoria RAM: 4 GB
Almacenamiento: 20 GB SSD
Red: 100 Mbps
```

#### **Hardware Recomendado:**
```
Procesador: 4 núcleos @ 2.5 GHz o superior
Memoria RAM: 8 GB o superior
Almacenamiento: 50 GB SSD NVMe
Red: 1 Gbps
Backup: Almacenamiento redundante
```

#### **Software Base Requerido:**
- **PHP 8.2** o superior
- **MariaDB 10.6** o superior
- **Nginx 1.20** o **Apache 2.4**
- **Composer 2.8**
- **Node.js 18** (para compilación de assets)

---

## 🖥️ SISTEMA OPERATIVO DEL SERVIDOR

### **Ubuntu Server 22.04 LTS - Configuración Recomendada**

#### **🔧 Especificaciones Técnicas**

```yaml
Sistema Operativo: Ubuntu Server 22.04.3 LTS
Kernel: Linux 5.15.0 o superior
Arquitectura: x86_64 (AMD64)
Tiempo de Soporte: Hasta abril 2027 (LTS)
Actualizaciones de Seguridad: Hasta abril 2032
```

#### **📦 Paquetes del Sistema**

**Paquetes Base Requeridos:**
```bash
# Sistema base
apt-transport-https
ca-certificates
curl
gnupg
lsb-release
software-properties-common
unzip
zip

# Herramientas de red
net-tools
ufw
fail2ban
```

**Stack de Aplicación:**
```bash
# Servidor Web
nginx
nginx-extras

# Base de Datos
mariadb-server
mariadb-client

# PHP y Extensiones
php8.2-fpm
php8.2-mysql
php8.2-xml
php8.2-mbstring
php8.2-curl
php8.2-zip
php8.2-intl
php8.2-gd
php8.2-bcmath
php8.2-soap
```

#### **🛡️ Configuración de Seguridad del SO**

**Firewall (UFW):**
```bash
# Configuración básica del firewall
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw allow 3306/tcp  # MariaDB (solo si necesario)
ufw enable
```

**Fail2Ban:**
```bash
# /etc/fail2ban/jail.local
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[sshd]
enabled = true
port = 22
filter = sshd
logpath = /var/log/auth.log

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
```

**Actualizaciones Automáticas:**
```bash
# /etc/apt/apt.conf.d/50unattended-upgrades
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
    "${distro_id}ESMApps:${distro_codename}-apps-security";
};

Unattended-Upgrade::AutoFixInterruptedDpkg "true";
Unattended-Upgrade::MinimalSteps "true";
Unattended-Upgrade::Remove-Unused-Dependencies "true";
Unattended-Upgrade::Automatic-Reboot "false";
```

### **🐳 Alternativa con Docker**

#### **Docker Compose Production:**
```yaml
version: '3.8'
services:
  app:
    build: .
    container_name: dentalsync-app
    restart: unless-stopped
    environment:
      - APP_ENV=production
      - DB_HOST=mariadb
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    depends_on:
      - mariadb
      - redis
    networks:
      - dentalsync-network

  nginx:
    image: nginx:alpine
    container_name: dentalsync-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - dentalsync-network

  mariadb:
    image: mariadb:10.11
    container_name: dentalsync-db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mariadb_data:/var/lib/mysql
      - ./mariadb.cnf:/etc/mysql/conf.d/custom.cnf
    networks:
      - dentalsync-network

  redis:
    image: redis:7-alpine
    container_name: dentalsync-redis
    restart: unless-stopped
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    networks:
      - dentalsync-network

volumes:
  mariadb_data:
  redis_data:

networks:
  dentalsync-network:
    driver: bridge
```

---

## ⚙️ INSTALACIÓN Y CONFIGURACIÓN DEL SERVIDOR

### **🚀 Instalación Automatizada**

#### **Script de Instalación Completa**

```bash
#!/bin/bash
# install-dentalsync-server.sh
# Instalación automatizada de DentalSync en Ubuntu Server 22.04

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🦷 DentalSync - Instalación del Servidor${NC}"
echo -e "${BLUE}=====================================N}"

# Verificar que se ejecute como root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script debe ejecutarse como root${NC}"
   exit 1
fi

# Actualizar sistema
echo -e "${YELLOW}📦 Actualizando sistema...${NC}"
apt update && apt upgrade -y

# Instalar dependencias base
echo -e "${YELLOW}📦 Instalando dependencias base...${NC}"
apt install -y software-properties-common apt-transport-https ca-certificates \
    curl gnupg lsb-release unzip zip git

# Instalar PHP 8.2
echo -e "${YELLOW}🐘 Instalando PHP 8.2...${NC}"
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-intl php8.2-gd php8.2-bcmath php8.2-soap

# Instalar Composer
echo -e "${YELLOW}🎼 Instalando Composer...${NC}"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar MariaDB
echo -e "${YELLOW}🗄️ Instalando MariaDB...${NC}"
apt install -y mariadb-server mariadb-client

# Instalar Nginx
echo -e "${YELLOW}🌐 Instalando Nginx...${NC}"
apt install -y nginx

# Instalar Node.js
echo -e "${YELLOW}📦 Instalando Node.js...${NC}"
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Configurar Firewall
echo -e "${YELLOW}🛡️ Configurando firewall...${NC}"
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# Instalar y configurar Fail2Ban
echo -e "${YELLOW}🛡️ Configurando Fail2Ban...${NC}"
apt install -y fail2ban
systemctl enable fail2ban
systemctl start fail2ban

echo -e "${GREEN}✅ Instalación del servidor completada${NC}"
echo -e "${BLUE}📋 Próximos pasos:${NC}"
echo -e "1. Configurar MariaDB: mysql_secure_installation"
echo -e "2. Clonar repositorio de DentalSync"
echo -e "3. Configurar Nginx virtual host"
echo -e "4. Configurar SSL con Let's Encrypt"
```

### **📁 Estructura de Directorios del Servidor**

```
/var/www/
├── dentalsync/                 # Aplicación principal
│   ├── app/                   # Código de la aplicación
│   ├── public/                # Punto de entrada web
│   ├── storage/               # Almacenamiento de archivos
│   │   ├── app/
│   │   ├── logs/
│   │   └── framework/
│   ├── bootstrap/cache/       # Cache de arranque
│   └── .env                   # Configuración
├── backups/                   # Respaldos
│   ├── database/
│   ├── files/
│   └── logs/
└── ssl/                       # Certificados SSL
    ├── certs/
    └── private/
```

### **🌐 Configuración de Nginx**

#### **Virtual Host para DentalSync:**

```nginx
# /etc/nginx/sites-available/dentalsync
server {
    listen 80;
    listen [::]:80;
    server_name tu-dominio.com www.tu-dominio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name tu-dominio.com www.tu-dominio.com;

    root /var/www/dentalsync/public;
    index index.php index.html index.htm;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/tu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tu-dominio.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # File Upload Size
    client_max_body_size 20M;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Laravel Routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security for storage and bootstrap cache
    location ~ ^/(storage|bootstrap/cache) {
        deny all;
    }

    # Logs
    access_log /var/log/nginx/dentalsync_access.log;
    error_log /var/log/nginx/dentalsync_error.log;
}
```

### **🗄️ Configuración de MariaDB**

#### **Archivo de Configuración Optimizado:**

```ini
# /etc/mysql/mariadb.conf.d/50-server.cnf
[server]
[mysqld]
user = mysql
pid-file = /run/mysqld/mysqld.pid
socket = /run/mysqld/mysqld.sock
basedir = /usr
datadir = /var/lib/mysql
tmpdir = /tmp
lc-messages-dir = /usr/share/mysql

# Performance Tuning
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection Settings
max_connections = 200
connect_timeout = 10
wait_timeout = 28800
interactive_timeout = 28800

# Query Cache (Disabled in MariaDB 10.6+)
query_cache_type = 0
query_cache_size = 0

# Logging
log_error = /var/log/mysql/error.log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Character Set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

[mysql]
default-character-set = utf8mb4

[client]
default-character-set = utf8mb4
```

### **🔐 Configuración SSL con Let's Encrypt**

```bash
# Instalar Certbot
apt install -y certbot python3-certbot-nginx

# Obtener certificado SSL
certbot --nginx -d tu-dominio.com -d www.tu-dominio.com

# Configurar renovación automática
echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
```

---

## 🛠️ SCRIPTS DE MANTENIMIENTO DEL SERVIDOR

### **📝 Script de Backup Automatizado**

```bash
#!/bin/bash
# backup-dentalsync.sh
# Script de respaldo automatizado para DentalSync

# Configuración
BACKUP_DIR="/var/www/backups"
APP_DIR="/var/www/dentalsync"
DB_NAME="dentalsync"
DB_USER="backup_user"
DB_PASS="backup_password"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Crear directorio de backup
mkdir -p $BACKUP_DIR/{database,files,logs}

echo "🔄 Iniciando backup de DentalSync - $DATE"

# Backup de base de datos
echo "📊 Respaldando base de datos..."
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/database/dentalsync_$DATE.sql.gz

# Backup de archivos
echo "📁 Respaldando archivos de la aplicación..."
tar -czf $BACKUP_DIR/files/dentalsync_files_$DATE.tar.gz -C $APP_DIR storage public/placas

# Backup de logs
echo "📋 Respaldando logs..."
tar -czf $BACKUP_DIR/logs/dentalsync_logs_$DATE.tar.gz /var/log/nginx/dentalsync_*.log $APP_DIR/storage/logs

# Limpiar backups antiguos
echo "🧹 Limpiando backups antiguos..."
find $BACKUP_DIR -type f -mtime +$RETENTION_DAYS -delete

# Verificar backups
echo "✅ Verificando integridad de backups..."
gzip -t $BACKUP_DIR/database/dentalsync_$DATE.sql.gz
tar -tzf $BACKUP_DIR/files/dentalsync_files_$DATE.tar.gz > /dev/null
tar -tzf $BACKUP_DIR/logs/dentalsync_logs_$DATE.tar.gz > /dev/null

echo "✅ Backup completado exitosamente: $DATE"

# Enviar notificación (opcional)
# curl -X POST -H 'Content-type: application/json' \
#   --data '{"text":"✅ Backup de DentalSync completado: '$DATE'"}' \
#   YOUR_WEBHOOK_URL
```

### **📊 Script de Monitoreo del Sistema**

```bash
#!/bin/bash
# monitor-dentalsync.sh
# Script de monitoreo del sistema DentalSync

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🦷 DentalSync - Monitor del Sistema${NC}"
echo -e "${BLUE}===================================${NC}"

# Verificar servicios
echo -e "\n${YELLOW}📊 Estado de Servicios:${NC}"

services=("nginx" "php8.2-fpm" "mariadb" "fail2ban")
for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        echo -e "  ✅ $service: ${GREEN}Activo${NC}"
    else
        echo -e "  ❌ $service: ${RED}Inactivo${NC}"
    fi
done

# Verificar uso de recursos
echo -e "\n${YELLOW}💻 Uso de Recursos:${NC}"
echo -e "  🖥️  CPU: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"
echo -e "  🧠 RAM: $(free | grep Mem | awk '{printf("%.1f%%", $3/$2 * 100.0)}')"
echo -e "  💾 Disco: $(df -h / | awk 'NR==2{printf "%s", $5}')"

# Verificar conexiones a la base de datos
echo -e "\n${YELLOW}🗄️ Base de Datos:${NC}"
DB_CONNECTIONS=$(mysql -e "SHOW STATUS LIKE 'Threads_connected';" | awk 'NR==2{print $2}')
echo -e "  🔗 Conexiones activas: $DB_CONNECTIONS"

# Verificar logs de error
echo -e "\n${YELLOW}📋 Últimos Errores:${NC}"
ERROR_COUNT=$(tail -n 100 /var/log/nginx/dentalsync_error.log 2>/dev/null | wc -l)
if [ $ERROR_COUNT -gt 0 ]; then
    echo -e "  ⚠️  Errores en Nginx: $ERROR_COUNT (últimas 100 líneas)"
else
    echo -e "  ✅ Sin errores recientes en Nginx"
fi

# Verificar espacio en disco
echo -e "\n${YELLOW}💾 Espacio en Disco:${NC}"
df -h | grep -E "(Filesystem|/dev/)" | awk '{print "  📁 "$1": "$4" libre de "$2" ("$5" usado)"}'

# Verificar certificados SSL
echo -e "\n${YELLOW}🔐 Certificados SSL:${NC}"
if [ -f /etc/letsencrypt/live/*/cert.pem ]; then
    SSL_EXPIRES=$(openssl x509 -enddate -noout -in /etc/letsencrypt/live/*/cert.pem | cut -d= -f2)
    echo -e "  📅 Expira: $SSL_EXPIRES"
else
    echo -e "  ⚠️  No se encontraron certificados SSL"
fi

echo -e "\n${GREEN}✅ Monitoreo completado${NC}"
```

### **🔄 Script de Actualización del Sistema**

```bash
#!/bin/bash
# update-dentalsync.sh
# Script de actualización automatizada

echo "🔄 Actualizando DentalSync..."

# Cambiar al directorio de la aplicación
cd /var/www/dentalsync

# Activar modo de mantenimiento
php artisan down --message="Actualizando sistema, regresamos pronto"

# Hacer backup antes de actualizar
./backup-dentalsync.sh

# Actualizar código desde Git (si aplica)
git pull origin main

# Actualizar dependencias de Composer
composer install --no-dev --optimize-autoloader

# Actualizar dependencias de Node.js
npm ci --production

# Compilar assets
npm run build

# Ejecutar migraciones
php artisan migrate --force

# Limpiar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimizar Composer
composer dump-autoload --optimize

# Reiniciar servicios
systemctl reload php8.2-fpm
systemctl reload nginx

# Desactivar modo de mantenimiento
php artisan up

echo "✅ Actualización completada"
```

### **🕐 Configuración de Cron Jobs**

```bash
# /etc/crontab
# Cron jobs para DentalSync

# Backup diario a las 2:00 AM
0 2 * * * root /var/www/backups/backup-dentalsync.sh

# Monitoreo cada 5 minutos
*/5 * * * * root /usr/local/bin/monitor-dentalsync.sh

# Limpiar logs cada domingo
0 3 * * 0 root find /var/log -name "*.log" -mtime +7 -exec rm {} \;

# Optimizar base de datos cada semana
0 1 * * 1 root mysqlcheck -o --all-databases -u root -p

# Renovar certificados SSL (automático con certbot)  
0 12 * * * root /usr/bin/certbot renew --quiet

# Limpiar cache de Laravel diariamente
0 4 * * * www-data cd /var/www/dentalsync && php artisan cache:clear
```

### **📈 Script de Optimización de Rendimiento**

```bash
#!/bin/bash
# optimize-dentalsync.sh
# Optimización de rendimiento del servidor

echo "⚡ Optimizando rendimiento de DentalSync..."

# Optimizar PHP-FPM
echo "🐘 Optimizando PHP-FPM..."
systemctl reload php8.2-fpm

# Optimizar Nginx
echo "🌐 Optimizando Nginx..."
nginx -s reload

# Optimizar MariaDB
echo "🗄️ Optimizando MariaDB..."
mysql -e "FLUSH TABLES; OPTIMIZE TABLE dentalsync.*;"

# Limpiar cachés del sistema
echo "🧹 Limpiando cachés..."
sync && echo 3 > /proc/sys/vm/drop_caches

# Optimizar Laravel
cd /var/www/dentalsync
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "✅ Optimización completada"
```

---

## 📊 MÉTRICAS Y MONITOREO

### **📈 Indicadores Clave de Rendimiento (KPIs)**

```yaml
Disponibilidad del Sistema: > 99.5%
Tiempo de Respuesta Promedio: < 200ms
Uso de CPU: < 70%
Uso de RAM: < 80%
Espacio en Disco: > 20% libre
Conexiones Simultáneas: < 150
Tiempo de Backup: < 30 minutos
```

### **🚨 Alertas Automáticas**

```bash
# Configuración de alertas críticas
if [ $(df / | tail -1 | awk '{print $5}' | sed 's/%//') -gt 90 ]; then
    echo "🚨 ALERTA: Disco casi lleno" | mail -s "DentalSync Alert" admin@consultorio.com
fi

if [ $(free | grep Mem | awk '{printf("%.0f", $3/$2 * 100.0)}') -gt 90 ]; then
    echo "🚨 ALERTA: Memoria RAM casi llena" | mail -s "DentalSync Alert" admin@consultorio.com
fi
```

---

## 🎯 CONCLUSIÓN

Esta documentación proporciona una guía completa para la administración del servidor de DentalSync, cubriendo desde la instalación inicial hasta el mantenimiento avanzado. Los scripts automatizados garantizan un funcionamiento óptimo y seguro del sistema.

### **📋 Checklist de Implementación**

- ✅ **Sistema Operativo**: Ubuntu Server 22.04 LTS instalado
- ✅ **Stack LEMP**: Linux + Nginx + MariaDB + PHP configurado  
- ✅ **Seguridad**: Firewall + Fail2Ban + SSL habilitados
- ✅ **Backups**: Scripts automatizados configurados
- ✅ **Monitoreo**: Scripts de monitoreo en funcionamiento
- ✅ **Mantenimiento**: Cron jobs configurados
- ✅ **Optimización**: Scripts de rendimiento disponibles

### **🚀 Próximos Pasos**

1. **Implementar monitoreo avanzado** con Prometheus + Grafana
2. **Configurar alta disponibilidad** con load balancer
3. **Implementar CI/CD** para despliegues automatizados
4. **Configurar replicación** de base de datos
5. **Establecer plan de recuperación** ante desastres

---

*Documentación creada por: **Andrés Núñez - NullDevs***  
*Fecha: 6 de octubre de 2025*  
*Versión: 1.0*