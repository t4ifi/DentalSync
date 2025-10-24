#!/bin/bash

##############################################################################
# Script de Instalación Automatizada - DentalSync
# Versión: 1.0.0
# Descripción: Instala y configura DentalSync en un servidor Ubuntu/Debian
##############################################################################

set -e  # Salir si hay algún error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # Sin color

# Funciones de utilidad
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_header() {
    echo ""
    echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════${NC}"
    echo ""
}

# Verificar que se ejecuta como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script debe ejecutarse como root (sudo)"
    exit 1
fi

# Banner
clear
echo -e "${BLUE}"
cat << "EOF"
╔════════════════════════════════════════════════════╗
║                                                    ║
║           DentalSync - Instalador v1.0            ║
║          Sistema de Gestión Dental                ║
║                                                    ║
╚════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Preguntas de configuración
print_header "Configuración Inicial"

read -p "Nombre de la base de datos [dentalsync]: " DB_NAME
DB_NAME=${DB_NAME:-dentalsync}
read -p "Usuario de la base de datos [dentalsync_user]: " DB_USER
DB_USER=${DB_USER:-dentalsync_user}
read -sp "Contraseña de la base de datos: " DB_PASSWORD
echo ""
read -sp "Contraseña root de MySQL: " MYSQL_ROOT_PASSWORD
echo ""
read -p "Puerto para la aplicación [8000]: " APP_PORT
APP_PORT=${APP_PORT:-8000}

# Confirmación
echo ""
print_warning "Resumen de configuración:"
echo "  - Base de datos: $DB_NAME"
echo "  - Usuario BD: $DB_USER"
echo "  - Puerto aplicación: $APP_PORT"
echo ""
read -p "¿Continuar con la instalación? (s/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    print_error "Instalación cancelada"
    exit 1
fi

# Paso 1: Actualizar sistema
print_header "Paso 1/12: Actualizando el sistema"
apt update && apt upgrade -y
print_success "Sistema actualizado"

# Paso 2: Instalar dependencias básicas
print_header "Paso 2/12: Instalando dependencias básicas"
apt install -y software-properties-common curl wget git unzip
print_success "Dependencias básicas instaladas"

# Paso 3: Instalar PHP 8.2
print_header "Paso 3/12: Instalando PHP 8.2 y extensiones"
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y \
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

php -v
print_success "PHP 8.2 instalado correctamente"

# Paso 4: Instalar Composer
print_header "Paso 4/12: Instalando Composer"
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
composer --version
print_success "Composer instalado"

# Paso 5: Instalar Node.js
print_header "Paso 5/12: Instalando Node.js 20.x LTS"
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
node -v
npm -v
print_success "Node.js instalado"

# Paso 6: Instalar MariaDB
print_header "Paso 6/12: Instalando MariaDB"
apt install -y mariadb-server mariadb-client
systemctl enable mariadb
systemctl start mariadb
print_success "MariaDB instalado"

# Paso 7: Configurar base de datos
print_header "Paso 7/12: Configurando base de datos"
mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
print_success "Base de datos configurada"

# Paso 8: Instalar Nginx
print_header "Paso 8/12: Instalando Nginx"
apt install -y nginx
systemctl enable nginx
systemctl start nginx
print_success "Nginx instalado"

# Paso 9: Clonar/copiar proyecto
print_header "Paso 9/12: Configurando proyecto"
mkdir -p /var/www
cd /var/www

if [ -d "dentalsync" ]; then
    print_warning "El directorio /var/www/dentalsync ya existe"
    read -p "¿Desea eliminarlo y reinstalar? (s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        rm -rf dentalsync
    else
        print_error "Instalación cancelada"
        exit 1
    fi
fi

# Aquí puedes clonar desde Git o copiar archivos
print_info "Copiando archivos del proyecto..."
# Si tienes el proyecto en GitHub:
# git clone https://github.com/t4ifi/DentalSync.git dentalsync

# Por ahora asumimos que los archivos ya están presentes
if [ ! -d "dentalsync" ]; then
    print_error "No se encontró el directorio del proyecto"
    print_info "Por favor, coloca los archivos en /var/www/dentalsync antes de continuar"
    exit 1
fi

cd dentalsync
print_success "Proyecto configurado"

# Paso 10: Instalar dependencias
print_header "Paso 10/12: Instalando dependencias del proyecto"
print_info "Instalando dependencias PHP con Composer..."
composer install --no-dev --optimize-autoloader

print_info "Instalando dependencias Node.js..."
npm install

print_info "Compilando assets para producción..."
npm run build

print_success "Dependencias instaladas"

# Paso 11: Configurar Laravel
print_header "Paso 11/12: Configurando Laravel"

# Copiar .env si no existe
if [ ! -f ".env" ]; then
    cp .env.example .env
    print_success "Archivo .env creado"
fi

# Actualizar .env
sed -i "s|APP_NAME=.*|APP_NAME=DentalSync|" .env
sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|APP_URL=.*|APP_URL=http://localhost:${APP_PORT}|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

# Generar clave de aplicación
php artisan key:generate --force
print_success "Clave de aplicación generada"

# Ejecutar migraciones
print_info "Ejecutando migraciones de base de datos..."
php artisan migrate --force
print_success "Migraciones ejecutadas"

# Configurar permisos
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
print_success "Permisos configurados"

# Optimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Laravel optimizado"

# Paso 12: Configurar Nginx
print_header "Paso 12/12: Configurando Nginx"

cat > /etc/nginx/sites-available/dentalsync <<EOF
server {
    listen 80;
    server_name localhost;
    root /var/www/dentalsync/public;
    index index.php;

    access_log /var/log/nginx/dentalsync-access.log;
    error_log /var/log/nginx/dentalsync-error.log;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/dentalsync /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl reload nginx
print_success "Nginx configurado"

# Resumen final
print_header "¡Instalación completada!"
echo ""
print_success "DentalSync ha sido instalado exitosamente"
echo ""
print_info "Detalles de la instalación:"
echo "  - URL: http://localhost"
echo "  - Puerto: 80 (Nginx)"
echo "  - Base de datos: ${DB_NAME}"
echo "  - Usuario BD: ${DB_USER}"
echo "  - Directorio: /var/www/dentalsync"
echo ""
print_info "Comandos útiles:"
echo "  - Ver logs: tail -f /var/log/nginx/dentalsync-error.log"
echo "  - Reiniciar Nginx: systemctl restart nginx"
echo "  - Reiniciar PHP-FPM: systemctl restart php8.2-fpm"
echo ""
print_warning "Nota: Para desarrollo local, también puedes usar:"
echo "  cd /var/www/dentalsync && php artisan serve --port=${APP_PORT}"
echo ""
print_info "Próximos pasos:"
echo "  1. Accede a http://localhost (o http://IP-DEL-SERVIDOR)"
echo "  2. Crea el primer usuario administrador"
echo "  3. Configura el sistema según tus necesidades"
echo ""
print_warning "Recuerda:"
echo "  - Cambia las contraseñas por defecto"
echo "  - Configura backups automáticos (usa /Docs/DespliegueAPP/scripts/backup.sh)"
echo "  - Revisa los logs regularmente"
echo ""
print_success "¡Gracias por usar DentalSync!"
echo ""
