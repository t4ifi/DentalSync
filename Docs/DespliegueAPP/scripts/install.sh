#!/bin/bash

##############################################################################
# Script de Instalación - DentalSync
# Versión: 1.0.0
# Descripción: Instala dependencias para desarrollo de DentalSync
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
║         DentalSync - Instalador v1.0              ║
║         Sistema de Gestión Dental                 ║
║                                                    ║
╚════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Preguntas de configuración
print_header "Configuración de Base de Datos"

read -p "Nombre de la base de datos [dentalsync]: " DB_NAME
DB_NAME=${DB_NAME:-dentalsync}
read -p "Usuario de la base de datos [dentalsync_user]: " DB_USER
DB_USER=${DB_USER:-dentalsync_user}
read -sp "Contraseña de la base de datos: " DB_PASSWORD
echo ""
read -sp "Contraseña root de MySQL/MariaDB: " MYSQL_ROOT_PASSWORD
echo ""

# Confirmación
echo ""
print_warning "Resumen de configuración:"
echo "  - Base de datos: $DB_NAME"
echo "  - Usuario BD: $DB_USER"
echo ""
read -p "¿Continuar con la instalación? (s/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    print_error "Instalación cancelada"
    exit 1
fi

# Paso 1: Actualizar sistema
print_header "Paso 1/7: Actualizando el sistema"
apt update && apt upgrade -y
print_success "Sistema actualizado"

# Paso 2: Instalar dependencias básicas
print_header "Paso 2/7: Instalando dependencias básicas"
apt install -y software-properties-common curl wget git unzip
print_success "Dependencias básicas instaladas"

# Paso 3: Instalar PHP 8.2
print_header "Paso 3/7: Instalando PHP 8.2 y extensiones"
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y \
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

php -v
print_success "PHP 8.2 instalado correctamente"

# Paso 4: Instalar Composer
print_header "Paso 4/7: Instalando Composer"
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
composer --version
print_success "Composer instalado"

# Paso 5: Instalar Node.js
print_header "Paso 5/7: Instalando Node.js 20.x LTS"
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
node -v
npm -v
print_success "Node.js instalado"

# Paso 6: Instalar MariaDB
print_header "Paso 6/7: Instalando MariaDB"
apt install -y mariadb-server mariadb-client
systemctl enable mariadb
systemctl start mariadb
print_success "MariaDB instalado"

# Paso 7: Configurar base de datos
print_header "Paso 7/7: Configurando base de datos"
mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
print_success "Base de datos configurada"

# Resumen final
print_header "¡Instalación completada!"
echo ""
print_success "Dependencias instaladas exitosamente"
echo ""
print_info "Software instalado:"
echo "  ✓ PHP 8.2 + extensiones"
echo "  ✓ Composer"
echo "  ✓ Node.js 20.x LTS + NPM"
echo "  ✓ MariaDB"
echo ""
print_info "Base de datos configurada:"
echo "  - Nombre: ${DB_NAME}"
echo "  - Usuario: ${DB_USER}"
echo "  - Host: localhost"
echo "  - Puerto: 3306"
echo ""
print_warning "PRÓXIMOS PASOS:"
echo ""
echo "1. Clona o copia el proyecto DentalSync a tu directorio de trabajo"
echo "   git clone https://github.com/t4ifi/DentalSync.git"
echo "   cd DentalSync"
echo ""
echo "2. Instala las dependencias del proyecto:"
echo "   composer install"
echo "   npm install"
echo ""
echo "3. Configura el archivo .env:"
echo "   cp .env.example .env"
echo "   php artisan key:generate"
echo ""
echo "   Edita .env y configura:"
echo "   DB_DATABASE=${DB_NAME}"
echo "   DB_USERNAME=${DB_USER}"
echo "   DB_PASSWORD=tu_contraseña"
echo ""
echo "4. Ejecuta las migraciones:"
echo "   php artisan migrate"
echo ""
echo "5. Inicia los servidores de desarrollo:"
echo "   Terminal 1: php artisan serve"
echo "   Terminal 2: npm run dev"
echo ""
echo "6. Accede a la aplicación:"
echo "   http://localhost:8000"
echo ""
print_success "¡Listo para desarrollar!"
echo ""
