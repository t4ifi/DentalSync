#!/bin/bash

##############################################################################
# Script de Backup Automatizado - DentalSync
# Versión: 1.0.0
# Descripción: Realiza backup de base de datos y archivos
##############################################################################

set -e

# Configuración
APP_DIR="/var/www/dentalsync"
BACKUP_DIR="/var/backups/dentalsync"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=30  # Días que se mantendrán los backups

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Verificar que se ejecuta como root
if [ "$EUID" -ne 0 ]; then 
    print_error "Este script debe ejecutarse como root (sudo)"
    exit 1
fi

print_info "Iniciando backup de DentalSync - $(date)"

# Crear directorio de backups si no existe
mkdir -p "$BACKUP_DIR"

# Cargar variables de entorno
if [ -f "$APP_DIR/.env" ]; then
    export $(grep -v '^#' "$APP_DIR/.env" | xargs)
else
    print_error "No se encontró el archivo .env"
    exit 1
fi

# Backup de base de datos
print_info "Realizando backup de base de datos..."
mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" | gzip > "$BACKUP_DIR/db_${TIMESTAMP}.sql.gz"
print_success "Backup de BD completado: db_${TIMESTAMP}.sql.gz"

# Backup de archivos
print_info "Realizando backup de archivos..."

# Storage (placas dentales, documentos)
tar -czf "$BACKUP_DIR/storage_${TIMESTAMP}.tar.gz" -C "$APP_DIR" storage/app
print_success "Backup de storage completado: storage_${TIMESTAMP}.tar.gz"

# Configuración
cp "$APP_DIR/.env" "$BACKUP_DIR/env_${TIMESTAMP}.backup"
print_success "Backup de configuración completado: env_${TIMESTAMP}.backup"

# Limpiar backups antiguos
print_info "Limpiando backups antiguos (mayores a $RETENTION_DAYS días)..."
find "$BACKUP_DIR" -type f -name "db_*.sql.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -type f -name "storage_*.tar.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -type f -name "env_*.backup" -mtime +$RETENTION_DAYS -delete
print_success "Limpieza completada"

# Resumen
print_success "¡Backup completado exitosamente!"
echo ""
print_info "Archivos generados:"
echo "  - Base de datos: $BACKUP_DIR/db_${TIMESTAMP}.sql.gz"
echo "  - Storage: $BACKUP_DIR/storage_${TIMESTAMP}.tar.gz"
echo "  - Configuración: $BACKUP_DIR/env_${TIMESTAMP}.backup"
echo ""
print_info "Espacio utilizado:"
du -sh "$BACKUP_DIR"
echo ""
