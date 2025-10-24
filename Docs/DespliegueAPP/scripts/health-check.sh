#!/bin/bash

##############################################################################
# Script de Monitoreo de Salud - DentalSync
# Versión: 1.0.0
# Descripción: Verifica el estado de todos los servicios y recursos
##############################################################################

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

print_header() {
    echo ""
    echo -e "${BLUE}════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}════════════════════════════════════════${NC}"
}

# Variables de configuración
APP_PATH="/var/www/dentalsync"
LOG_FILE="/var/log/dentalsync-health.log"
ALERT_THRESHOLD_CPU=80
ALERT_THRESHOLD_MEM=85
ALERT_THRESHOLD_DISK=90

# Inicializar variables de estado
ERRORS=0
WARNINGS=0

# Función para registrar en log
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

echo ""
echo -e "${BLUE}╔════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   DentalSync - Verificación de Salud      ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════╝${NC}"
echo ""

# 1. Verificar servicios del sistema
print_header "1. Estado de Servicios"

check_service() {
    SERVICE=$1
    if systemctl is-active --quiet "$SERVICE"; then
        print_success "$SERVICE está activo"
        return 0
    else
        print_error "$SERVICE está inactivo"
        ((ERRORS++))
        log_message "ERROR: Servicio $SERVICE inactivo"
        return 1
    fi
}

check_service "nginx"
check_service "php8.2-fpm"
check_service "mysql" || check_service "mariadb"

# 2. Verificar recursos del sistema
print_header "2. Recursos del Sistema"

# CPU
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')
CPU_USAGE_INT=${CPU_USAGE%.*}
echo -n "CPU: ${CPU_USAGE}% "
if [ "$CPU_USAGE_INT" -lt "$ALERT_THRESHOLD_CPU" ]; then
    print_success "(Normal)"
elif [ "$CPU_USAGE_INT" -lt 95 ]; then
    print_warning "(Alta)"
    ((WARNINGS++))
else
    print_error "(Crítica)"
    ((ERRORS++))
    log_message "WARNING: Uso de CPU crítico: ${CPU_USAGE}%"
fi

# Memoria
MEM_USAGE=$(free | grep Mem | awk '{print ($3/$2) * 100.0}')
MEM_USAGE_INT=${MEM_USAGE%.*}
echo -n "Memoria: ${MEM_USAGE}% "
if [ "$MEM_USAGE_INT" -lt "$ALERT_THRESHOLD_MEM" ]; then
    print_success "(Normal)"
elif [ "$MEM_USAGE_INT" -lt 95 ]; then
    print_warning "(Alta)"
    ((WARNINGS++))
else
    print_error "(Crítica)"
    ((ERRORS++))
    log_message "WARNING: Uso de memoria crítico: ${MEM_USAGE}%"
fi

# Disco
DISK_USAGE=$(df -h / | tail -1 | awk '{print $5}' | sed 's/%//')
echo -n "Disco: ${DISK_USAGE}% "
if [ "$DISK_USAGE" -lt "$ALERT_THRESHOLD_DISK" ]; then
    print_success "(Normal)"
elif [ "$DISK_USAGE" -lt 95 ]; then
    print_warning "(Alto)"
    ((WARNINGS++))
else
    print_error "(Crítico)"
    ((ERRORS++))
    log_message "WARNING: Uso de disco crítico: ${DISK_USAGE}%"
fi

# 3. Verificar conectividad de base de datos
print_header "3. Base de Datos"

if [ -f "$APP_PATH/.env" ]; then
    DB_HOST=$(grep DB_HOST "$APP_PATH/.env" | cut -d '=' -f2)
    DB_PORT=$(grep DB_PORT "$APP_PATH/.env" | cut -d '=' -f2)
    DB_NAME=$(grep DB_DATABASE "$APP_PATH/.env" | cut -d '=' -f2)
    DB_USER=$(grep DB_USERNAME "$APP_PATH/.env" | cut -d '=' -f2)
    DB_PASS=$(grep DB_PASSWORD "$APP_PATH/.env" | cut -d '=' -f2)
    
    if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" > /dev/null 2>&1; then
        print_success "Conexión a base de datos exitosa"
        
        # Contar tablas
        TABLE_COUNT=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES;" | wc -l)
        print_info "Tablas en la base de datos: $((TABLE_COUNT - 1))"
    else
        print_error "No se puede conectar a la base de datos"
        ((ERRORS++))
        log_message "ERROR: Fallo de conexión a base de datos"
    fi
else
    print_warning "Archivo .env no encontrado"
    ((WARNINGS++))
fi

# 4. Verificar archivos de la aplicación
print_header "4. Archivos de Aplicación"

check_file() {
    if [ -f "$1" ]; then
        print_success "$2 existe"
        return 0
    else
        print_error "$2 no encontrado"
        ((ERRORS++))
        return 1
    fi
}

check_directory() {
    if [ -d "$1" ]; then
        print_success "$2 existe"
        return 0
    else
        print_error "$2 no encontrado"
        ((ERRORS++))
        return 1
    fi
}

check_file "$APP_PATH/.env" "Archivo de configuración (.env)"
check_file "$APP_PATH/composer.json" "Composer.json"
check_file "$APP_PATH/artisan" "Artisan CLI"
check_directory "$APP_PATH/vendor" "Dependencias de Composer"
check_directory "$APP_PATH/node_modules" "Dependencias de Node"

# Verificar permisos
STORAGE_PERMS=$(stat -c "%a" "$APP_PATH/storage" 2>/dev/null)
if [ "$STORAGE_PERMS" = "775" ] || [ "$STORAGE_PERMS" = "777" ]; then
    print_success "Permisos de storage correctos ($STORAGE_PERMS)"
else
    print_warning "Permisos de storage: $STORAGE_PERMS (se recomienda 775)"
    ((WARNINGS++))
fi

# 5. Verificar logs de errores
print_header "5. Logs de Errores Recientes"

LARAVEL_LOG="$APP_PATH/storage/logs/laravel.log"
if [ -f "$LARAVEL_LOG" ]; then
    ERROR_COUNT=$(grep -c "ERROR" "$LARAVEL_LOG" 2>/dev/null || echo "0")
    CRITICAL_COUNT=$(grep -c "CRITICAL" "$LARAVEL_LOG" 2>/dev/null || echo "0")
    
    if [ "$ERROR_COUNT" -eq 0 ] && [ "$CRITICAL_COUNT" -eq 0 ]; then
        print_success "No hay errores críticos en logs"
    else
        print_warning "Errores encontrados: $ERROR_COUNT | Críticos: $CRITICAL_COUNT"
        ((WARNINGS++))
        
        # Mostrar últimos 3 errores
        if [ "$ERROR_COUNT" -gt 0 ]; then
            print_info "Últimos errores:"
            grep "ERROR" "$LARAVEL_LOG" | tail -n 3
        fi
    fi
else
    print_info "No se encontró archivo de log de Laravel"
fi

# 6. Verificar respuesta HTTP
print_header "6. Respuesta de la Aplicación"

if [ -f "$APP_PATH/.env" ]; then
    APP_URL=$(grep APP_URL "$APP_PATH/.env" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
    
    if [ ! -z "$APP_URL" ]; then
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL" 2>/dev/null || echo "000")
        
        case $HTTP_CODE in
            200|302)
                print_success "Aplicación respondiendo correctamente (HTTP $HTTP_CODE)"
                ;;
            000)
                print_error "No se pudo conectar a la aplicación"
                ((ERRORS++))
                log_message "ERROR: Aplicación no responde"
                ;;
            *)
                print_warning "Aplicación respondiendo con código $HTTP_CODE"
                ((WARNINGS++))
                ;;
        esac
    else
        print_info "APP_URL no configurada en .env"
    fi
fi

# 7. Verificar espacio en directorios críticos
print_header "7. Espacio en Directorios"

check_dir_size() {
    if [ -d "$1" ]; then
        SIZE=$(du -sh "$1" 2>/dev/null | cut -f1)
        print_info "$2: $SIZE"
    fi
}

check_dir_size "$APP_PATH/storage/logs" "Logs"
check_dir_size "$APP_PATH/storage/app" "Archivos almacenados"
check_dir_size "$APP_PATH/public" "Archivos públicos"

# Resumen final
echo ""
print_header "Resumen de Verificación"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    print_success "¡Sistema completamente saludable!"
    log_message "INFO: Health check exitoso - Sin errores ni advertencias"
    EXIT_CODE=0
elif [ $ERRORS -eq 0 ]; then
    print_warning "Sistema operativo con $WARNINGS advertencia(s)"
    log_message "INFO: Health check completado - $WARNINGS advertencias"
    EXIT_CODE=0
else
    print_error "Se encontraron $ERRORS error(es) y $WARNINGS advertencia(s)"
    log_message "ERROR: Health check falló - $ERRORS errores, $WARNINGS advertencias"
    EXIT_CODE=1
fi

echo ""
print_info "Reporte guardado en: $LOG_FILE"
echo ""

exit $EXIT_CODE
