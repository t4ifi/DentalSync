#!/bin/bash

# 🦷 DentalSync - Script de Configuración Automática
# Versión: 2.0
# Fecha: 7 de octubre de 2025
# Descripción: Script validado que configura el entorno Docker completo

set -e  # Salir si hay errores

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para logging
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar prerrequisitos
check_prerequisites() {
    log "Verificando prerrequisitos..."
    
    if ! command -v docker &> /dev/null; then
        error "Docker no está instalado"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        error "Docker Compose no está instalado"
        exit 1
    fi
    
    if ! docker info &> /dev/null; then
        error "Docker no está ejecutándose"
        exit 1
    fi
    
    success "Prerrequisitos verificados"
}

# Limpiar entorno existente
cleanup_environment() {
    log "Limpiando entorno Docker existente..."
    
    # Detener contenedores relacionados
    docker stop $(docker ps -aq --filter name=dentalsync) 2>/dev/null || true
    
    # Eliminar contenedores
    docker rm $(docker ps -aq --filter name=dentalsync) 2>/dev/null || true
    
    # Limpiar volúmenes y sistema
    docker volume prune -f
    docker system prune -f
    
    success "Entorno limpiado"
}

# Construir e iniciar servicios
build_and_start() {
    log "Construyendo e iniciando servicios Docker..."
    
    docker-compose -f Docker/docker-compose.dev.yml up --build -d
    
    # Esperar a que los servicios estén listos
    log "Esperando a que los servicios estén listos..."
    sleep 15
    
    # Verificar que los contenedores estén ejecutándose
    if ! docker ps | grep -q "dentalsync-dev"; then
        error "El contenedor dentalsync-dev no está ejecutándose"
        exit 1
    fi
    
    if ! docker ps | grep -q "dentalsync-mariadb"; then
        error "El contenedor dentalsync-mariadb no está ejecutándose"
        exit 1
    fi
    
    success "Servicios Docker iniciados correctamente"
}

# Configurar Laravel
configure_laravel() {
    log "Configurando aplicación Laravel..."
    
    # Copiar .env y configurar
    docker exec -it dentalsync-dev cp .env.example .env
    
    # Configurar base de datos en .env
    docker exec -it dentalsync-dev sed -i 's/DB_HOST=.*/DB_HOST=database/' .env
    docker exec -it dentalsync-dev sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mariadb/' .env
    
    # Generar clave de aplicación
    docker exec -it dentalsync-dev php artisan key:generate
    
    # Ejecutar migraciones
    log "Ejecutando migraciones de base de datos..."
    docker exec -it dentalsync-dev php artisan migrate --force
    
    success "Laravel configurado correctamente"
}

# Configurar frontend
configure_frontend() {
    log "Configurando frontend (Node.js y Vite)..."
    
    # Configurar npm para evitar errores de permisos
    docker exec -it dentalsync-dev npm config set cache /tmp/.npm --global
    
    # Instalar dependencias
    docker exec -it dentalsync-dev npm install
    
    # Compilar assets
    docker exec -it dentalsync-dev npm run build
    
    success "Frontend configurado correctamente"
}

# Crear usuarios de prueba
create_test_users() {
    log "Creando usuarios de prueba..."
    
    # Crear archivo temporal con comandos de Tinker
    cat > /tmp/create_users.php << 'EOF'
<?php
use App\Models\Usuario;

try {
    // Usuario dentista
    Usuario::create([
        'usuario' => 'dentista',
        'nombre' => 'Dr. Juan Pérez',
        'password_hash' => bcrypt('dentista123'),
        'rol' => 'dentista',
        'activo' => true
    ]);
    
    // Usuario recepcionista
    Usuario::create([
        'usuario' => 'recepcionista',
        'nombre' => 'María González',
        'password_hash' => bcrypt('recepcion123'),
        'rol' => 'recepcionista',
        'activo' => true
    ]);
    
    echo "Usuarios creados correctamente\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
EOF

    # Copiar archivo al contenedor y ejecutar
    docker cp /tmp/create_users.php dentalsync-dev:/tmp/create_users.php
    docker exec -it dentalsync-dev php artisan tinker /tmp/create_users.php
    
    # Limpiar archivo temporal
    rm /tmp/create_users.php
    docker exec -it dentalsync-dev rm /tmp/create_users.php
    
    success "Usuarios de prueba creados"
}

# Verificar instalación
verify_installation() {
    log "Verificando instalación..."
    
    # Verificar contenedores
    if ! docker ps | grep -q "dentalsync-dev\|dentalsync-mariadb"; then
        error "Los contenedores no están ejecutándose correctamente"
        return 1
    fi
    
    # Verificar conexión a base de datos
    if ! docker exec -it dentalsync-dev mariadb -h database -u dentalsync -ppassword --skip-ssl -e "SELECT 'OK';" &>/dev/null; then
        error "No se puede conectar a la base de datos"
        return 1
    fi
    
    # Verificar assets compilados
    if ! docker exec -it dentalsync-dev test -f public/build/manifest.json; then
        error "Los assets de Vite no están compilados"
        return 1
    fi
    
    # Verificar aplicación web
    if ! curl -s -f http://localhost:8000 > /dev/null; then
        error "La aplicación web no responde en localhost:8000"
        return 1
    fi
    
    success "Instalación verificada correctamente"
}

# Mostrar información final
show_final_info() {
    echo ""
    echo "🦷 ======================================"
    echo "   DentalSync - Configuración Completa"
    echo "======================================"
    echo ""
    echo "✅ Aplicación disponible en: http://localhost:8000"
    echo "✅ Base de datos MariaDB en puerto: 3307"
    echo ""
    echo "👥 Usuarios de prueba creados:"
    echo "   • Usuario: dentista | Contraseña: dentista123"
    echo "   • Usuario: recepcionista | Contraseña: recepcion123"
    echo ""
    echo "🔧 Comandos útiles:"
    echo "   • docker exec -it dentalsync-dev bash"
    echo "   • docker-compose -f Docker/docker-compose.dev.yml logs"
    echo "   • docker-compose -f Docker/docker-compose.dev.yml restart"
    echo ""
    echo "📚 Documentación completa en: Docker/README.md"
    echo ""
}

# Función principal
main() {
    echo "🦷 DentalSync - Configuración Automática del Entorno Docker"
    echo "========================================================="
    echo ""
    
    check_prerequisites
    cleanup_environment
    build_and_start
    configure_laravel
    configure_frontend
    create_test_users
    verify_installation
    show_final_info
    
    success "🎉 ¡Configuración completada exitosamente!"
}

# Manejo de errores
trap 'error "Script interrumpido. Revisa los logs de Docker para más detalles."; exit 1' ERR

# Ejecutar función principal
main "$@"