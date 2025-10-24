#!/bin/bash

##############################################################################
# Script de Despliegue/Actualización - DentalSync
# Versión: 1.0.0
# Descripción: Despliega actualizaciones de la aplicación
##############################################################################

set -e

# Configuración
APP_DIR="/var/www/dentalsync"
BACKUP_SCRIPT="/var/www/dentalsync/Docs/DespliegueAPP/scripts/backup.sh"

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
echo -e "${BLUE}══════════════════════════════════════════${NC}"
echo -e "${BLUE}  DentalSync - Despliegue de Actualización${NC}"
echo -e "${BLUE}══════════════════════════════════════════${NC}"
echo ""

# Paso 1: Hacer backup antes de actualizar
print_info "Paso 1/8: Creando backup de seguridad..."
if [ -f "$BACKUP_SCRIPT" ]; then
    bash "$BACKUP_SCRIPT"
else
    print_warning "No se encontró el script de backup, continuando sin backup..."
fi

# Paso 2: Activar modo mantenimiento
print_info "Paso 2/8: Activando modo mantenimiento..."
cd "$APP_DIR"
php artisan down --message="Actualización en progreso" --retry=60
print_success "Modo mantenimiento activado"

# Paso 3: Obtener últimos cambios (si es desde Git)
print_info "Paso 3/8: Obteniendo últimos cambios..."
# Descomentar si usas Git:
# git pull origin main
print_info "Cambios obtenidos (manual o Git)"

# Paso 4: Actualizar dependencias PHP
print_info "Paso 4/8: Actualizando dependencias PHP..."
composer install --no-dev --optimize-autoloader --no-interaction
print_success "Dependencias PHP actualizadas"

# Paso 5: Actualizar dependencias Node.js
print_info "Paso 5/8: Actualizando dependencias Node.js..."
npm install
npm run build
print_success "Assets compilados"

# Paso 6: Ejecutar migraciones
print_info "Paso 6/8: Ejecutando migraciones de base de datos..."
php artisan migrate --force
print_success "Migraciones ejecutadas"

# Paso 7: Limpiar y optimizar
print_info "Paso 7/8: Limpiando caché y optimizando..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Aplicación optimizada"

# Paso 8: Desactivar modo mantenimiento
print_info "Paso 8/8: Desactivando modo mantenimiento..."
php artisan up
print_success "Modo mantenimiento desactivado"

# Reiniciar servicios
print_info "Reiniciando servicios..."
systemctl reload php8.2-fpm
systemctl reload nginx
print_success "Servicios reiniciados"

# Resumen
echo ""
print_success "¡Despliegue completado exitosamente!"
echo ""
print_info "Verifica la aplicación en tu navegador"
echo ""
