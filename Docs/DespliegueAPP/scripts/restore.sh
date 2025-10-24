#!/bin/bash

##############################################################################
# Script de Restauración - DentalSync
# Versión: 1.0.0
# Descripción: Restaura la aplicación desde un backup
##############################################################################

set -e

# Configuración
APP_DIR="/var/www/dentalsync"
BACKUP_DIR="/var/backups/dentalsync"

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
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}  DentalSync - Restauración de Backup${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

# Listar backups disponibles
print_info "Backups disponibles de base de datos:"
echo ""
ls -lh "$BACKUP_DIR"/db_*.sql.gz | awk '{print $9, "(" $5 ")"}'
echo ""

# Seleccionar backup
read -p "Ingrese el nombre del archivo de backup de BD a restaurar: " DB_BACKUP_FILE

if [ ! -f "$BACKUP_DIR/$DB_BACKUP_FILE" ]; then
    print_error "El archivo de backup no existe"
    exit 1
fi

# Confirmar restauración
print_warning "¡ADVERTENCIA!"
echo "Esta operación sobrescribirá la base de datos actual"
echo "Archivo seleccionado: $DB_BACKUP_FILE"
echo ""
read -p "¿Está seguro de continuar? (escriba 'SI' para confirmar): " CONFIRM

if [ "$CONFIRM" != "SI" ]; then
    print_error "Restauración cancelada"
    exit 1
fi

# Activar modo mantenimiento
print_info "Activando modo mantenimiento..."
cd "$APP_DIR"
php artisan down
print_success "Modo mantenimiento activado"

# Cargar variables de entorno
if [ -f "$APP_DIR/.env" ]; then
    export $(grep -v '^#' "$APP_DIR/.env" | xargs)
else
    print_error "No se encontró el archivo .env"
    exit 1
fi

# Restaurar base de datos
print_info "Restaurando base de datos..."
gunzip < "$BACKUP_DIR/$DB_BACKUP_FILE" | mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
print_success "Base de datos restaurada"

# Buscar backup de storage correspondiente
TIMESTAMP=$(echo "$DB_BACKUP_FILE" | grep -oP '\d{8}_\d{6}')
STORAGE_BACKUP="storage_${TIMESTAMP}.tar.gz"

if [ -f "$BACKUP_DIR/$STORAGE_BACKUP" ]; then
    print_info "Restaurando archivos de storage..."
    
    # Hacer backup del storage actual
    mv "$APP_DIR/storage/app" "$APP_DIR/storage/app.old"
    
    # Extraer backup
    tar -xzf "$BACKUP_DIR/$STORAGE_BACKUP" -C "$APP_DIR"
    
    # Restaurar permisos
    chown -R www-data:www-data "$APP_DIR/storage/app"
    chmod -R 775 "$APP_DIR/storage/app"
    
    print_success "Archivos de storage restaurados"
else
    print_warning "No se encontró backup de storage correspondiente"
fi

# Buscar backup de configuración
ENV_BACKUP="env_${TIMESTAMP}.backup"

if [ -f "$BACKUP_DIR/$ENV_BACKUP" ]; then
    print_info "Backup de configuración encontrado: $ENV_BACKUP"
    print_warning "¿Desea restaurar también la configuración .env?"
    read -p "(s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        cp "$APP_DIR/.env" "$APP_DIR/.env.before_restore"
        cp "$BACKUP_DIR/$ENV_BACKUP" "$APP_DIR/.env"
        print_success "Configuración restaurada (backup anterior: .env.before_restore)"
    fi
fi

# Limpiar caché
print_info "Limpiando caché..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
print_success "Caché limpiada"

# Desactivar modo mantenimiento
print_info "Desactivando modo mantenimiento..."
php artisan up
print_success "Modo mantenimiento desactivado"

# Resumen
echo ""
print_success "¡Restauración completada exitosamente!"
echo ""
print_info "Detalles:"
echo "  - Base de datos restaurada desde: $DB_BACKUP_FILE"
if [ -f "$BACKUP_DIR/$STORAGE_BACKUP" ]; then
    echo "  - Storage restaurado desde: $STORAGE_BACKUP"
fi
echo ""
print_warning "Recuerda verificar que todo funciona correctamente"
echo ""
