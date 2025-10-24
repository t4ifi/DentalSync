#!/bin/bash

##############################################################################
# Script de Configuración de Base de Datos - DentalSync
# Versión: 1.0.0
# Descripción: Configura la base de datos MySQL/MariaDB
##############################################################################

set -e

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Verificar que se ejecuta como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script debe ejecutarse como root (sudo)"
    exit 1
fi

echo ""
echo -e "${BLUE}════════════════════════════════════════════${NC}"
echo -e "${BLUE}  DentalSync - Configuración de Base de Datos${NC}"
echo -e "${BLUE}════════════════════════════════════════════${NC}"
echo ""

# Solicitar información
read -p "Contraseña root de MySQL/MariaDB: " -s MYSQL_ROOT_PASSWORD
echo ""
read -p "Nombre de la base de datos [dentalsync]: " DB_NAME
DB_NAME=${DB_NAME:-dentalsync}
read -p "Usuario de la base de datos [dentalsync_user]: " DB_USER
DB_USER=${DB_USER:-dentalsync_user}
read -sp "Contraseña del usuario de BD: " DB_PASSWORD
echo ""
echo ""

print_info "Configuración seleccionada:"
echo "  - Base de datos: $DB_NAME"
echo "  - Usuario: $DB_USER"
echo ""

# Verificar conexión a MySQL
print_info "Verificando conexión a MySQL/MariaDB..."
if ! mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SELECT 1;" > /dev/null 2>&1; then
    print_error "No se pudo conectar a MySQL. Verifica la contraseña root."
    exit 1
fi
print_success "Conexión exitosa"

# Crear base de datos
print_info "Creando base de datos..."
mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
MYSQL_SCRIPT
print_success "Base de datos '${DB_NAME}' creada"

# Crear usuario
print_info "Creando usuario de base de datos..."
mysql -u root -p"$MYSQL_ROOT_PASSWORD" <<MYSQL_SCRIPT
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT
print_success "Usuario '${DB_USER}' creado con permisos"

# Verificar que el usuario puede conectarse
print_info "Verificando permisos del usuario..."
if ! mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SELECT 1;" > /dev/null 2>&1; then
    print_error "El usuario no puede conectarse a la base de datos"
    exit 1
fi
print_success "Permisos verificados correctamente"

# Mostrar información de tablas (si existen)
TABLE_COUNT=$(mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SHOW TABLES;" | wc -l)
if [ $TABLE_COUNT -gt 1 ]; then
    print_info "Tablas existentes en la base de datos:"
    mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SHOW TABLES;"
fi

# Resumen
echo ""
print_success "¡Configuración de base de datos completada!"
echo ""
print_info "Credenciales de conexión:"
echo "  Host: localhost"
echo "  Puerto: 3306"
echo "  Base de datos: $DB_NAME"
echo "  Usuario: $DB_USER"
echo "  Contraseña: ********"
echo ""
print_warning "Recuerda actualizar el archivo .env con estas credenciales:"
echo ""
echo "DB_CONNECTION=mysql"
echo "DB_HOST=127.0.0.1"
echo "DB_PORT=3306"
echo "DB_DATABASE=$DB_NAME"
echo "DB_USERNAME=$DB_USER"
echo "DB_PASSWORD=tu_contraseña"
echo ""
