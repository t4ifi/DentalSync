#!/bin/bash

# ============================================================================
# DENTALSYNC - SCRIPT DE DESARROLLO RÁPIDO
# Automatiza las tareas más comunes de desarrollo
# ============================================================================

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # NoColor

# Función para mostrar banner
show_banner() {
    echo -e "${CYAN}"
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║                    🦷 DENTALSYNC DEV                      ║"
    echo "║                 Herramientas de Desarrollo                ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

# Función para mostrar ayuda
show_help() {
    echo -e "${YELLOW}Uso: dev-tools.sh [comando]${NC}\n"
    echo "Comandos disponibles:"
    echo -e "  ${GREEN}setup${NC}     - Configurar proyecto completo"
    echo -e "  ${GREEN}install${NC}   - Instalar dependencias"
    echo -e "  ${GREEN}serve${NC}     - Iniciar servidor Laravel"
    echo -e "  ${GREEN}dev${NC}       - Iniciar servidor de desarrollo (Laravel + Vite)"
    echo -e "  ${GREEN}build${NC}     - Compilar assets para producción"
    echo -e "  ${GREEN}fresh${NC}     - Reset completo de base de datos"
    echo -e "  ${GREEN}migrate${NC}   - Ejecutar migraciones"
    echo -e "  ${GREEN}seed${NC}      - Ejecutar seeders"
    echo -e "  ${GREEN}test${NC}      - Ejecutar tests"
    echo -e "  ${GREEN}tinker${NC}    - Abrir Laravel Tinker"
    echo -e "  ${GREEN}logs${NC}      - Ver logs de Laravel"
    echo -e "  ${GREEN}clear${NC}     - Limpiar cachés"
    echo -e "  ${GREEN}status${NC}    - Ver estado del proyecto"
    echo -e "  ${GREEN}help${NC}      - Mostrar esta ayuda"
}

# Función para verificar si estamos en el directorio correcto
check_project_root() {
    if [ ! -f "artisan" ]; then
        echo -e "${RED}❌ Error: No se encontró el archivo 'artisan'. Asegúrate de estar en el directorio raíz del proyecto Laravel.${NC}"
        exit 1
    fi
}

# Función para configurar el proyecto
setup_project() {
    echo -e "${BLUE}🚀 Configurando proyecto DentalSync...${NC}"
    
    # Copiar .env si no existe
    if [ ! -f .env ]; then
        cp .env.example .env
        echo -e "${GREEN}✅ Archivo .env creado${NC}"
    else
        echo -e "${YELLOW}⚠️  Archivo .env ya existe${NC}"
    fi
    
    # Generar clave de aplicación
    echo -e "${BLUE}🔑 Generando clave de aplicación...${NC}"
    php artisan key:generate --ansi
    
    # Crear base de datos SQLite si no existe
    if [ ! -f database/database.sqlite ]; then
        touch database/database.sqlite
        echo -e "${GREEN}✅ Base de datos SQLite creada${NC}"
    else
        echo -e "${YELLOW}⚠️  Base de datos SQLite ya existe${NC}"
    fi
    
    # Ejecutar migraciones
    echo -e "${BLUE}🗄️  Ejecutando migraciones...${NC}"
    php artisan migrate --force
    
    # Instalar dependencias
    install_dependencies
    
    echo -e "${GREEN}✅ ¡Proyecto configurado correctamente!${NC}"
    echo -e "${CYAN}💡 Ahora puedes ejecutar: ./dev-tools.sh serve${NC}"
}

# Función para instalar dependencias
install_dependencies() {
    echo -e "${BLUE}📦 Instalando dependencias...${NC}"
    
    echo -e "${BLUE}🔧 Instalando dependencias de PHP...${NC}"
    composer install --no-interaction --prefer-dist --optimize-autoloader
    
    echo -e "${BLUE}📦 Instalando dependencias de Node.js...${NC}"
    npm install
    
    echo -e "${GREEN}✅ Dependencias instaladas correctamente!${NC}"
}

# Función para iniciar servidor Laravel
serve_laravel() {
    echo -e "${BLUE}🚀 Iniciando servidor Laravel...${NC}"
    echo -e "${CYAN}📍 Servidor disponible en: http://localhost:8000${NC}"
    php artisan serve --host=0.0.0.0 --port=8000
}

# Función para desarrollo completo (Laravel + Vite)
serve_dev() {
    echo -e "${BLUE}🚀 Iniciando entorno de desarrollo completo...${NC}"
    echo -e "${CYAN}📍 Laravel: http://localhost:8000${NC}"
    echo -e "${CYAN}📍 Vite: http://localhost:5173${NC}"
    
    # Verificar si npm está disponible
    if ! command -v npm &> /dev/null; then
        echo -e "${RED}❌ npm no está instalado${NC}"
        exit 1
    fi
    
    # Iniciar Laravel en background
    php artisan serve --host=0.0.0.0 --port=8000 &
    LARAVEL_PID=$!
    
    # Iniciar Vite
    npm run dev
    
    # Limpiar proceso de Laravel al salir
    trap "kill $LARAVEL_PID" EXIT
}

# Función para compilar assets
build_assets() {
    echo -e "${BLUE}🏗️  Compilando assets para producción...${NC}"
    npm run build
    echo -e "${GREEN}✅ Assets compilados correctamente!${NC}"
}

# Función para reset de base de datos
fresh_database() {
    echo -e "${YELLOW}⚠️  ¿Estás seguro de que quieres resetear la base de datos? (y/N)${NC}"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        echo -e "${BLUE}🗄️  Reseteando base de datos...${NC}"
        php artisan migrate:fresh --seed --force
        echo -e "${GREEN}✅ Base de datos reseteada correctamente!${NC}"
    else
        echo -e "${CYAN}❌ Operación cancelada${NC}"
    fi
}

# Función para ejecutar migraciones
run_migrations() {
    echo -e "${BLUE}🗄️  Ejecutando migraciones...${NC}"
    php artisan migrate --force
    echo -e "${GREEN}✅ Migraciones ejecutadas correctamente!${NC}"
}

# Función para ejecutar seeders
run_seeders() {
    echo -e "${BLUE}🌱 Ejecutando seeders...${NC}"
    php artisan db:seed --force
    echo -e "${GREEN}✅ Seeders ejecutados correctamente!${NC}"
}

# Función para ejecutar tests
run_tests() {
    echo -e "${BLUE}🧪 Ejecutando tests...${NC}"
    php artisan test
    echo -e "${GREEN}✅ Tests completados!${NC}"
}

# Función para abrir Tinker
open_tinker() {
    echo -e "${BLUE}🔧 Abriendo Laravel Tinker...${NC}"
    echo -e "${CYAN}💡 Tip: Usa 'exit' para salir${NC}"
    php artisan tinker
}

# Función para ver logs
view_logs() {
    echo -e "${BLUE}📋 Mostrando logs de Laravel...${NC}"
    echo -e "${CYAN}💡 Presiona Ctrl+C para salir${NC}"
    tail -f storage/logs/laravel.log
}

# Función para limpiar cachés
clear_caches() {
    echo -e "${BLUE}🧹 Limpiando cachés...${NC}"
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    composer dump-autoload
    echo -e "${GREEN}✅ Cachés limpiados correctamente!${NC}"
}

# Función para mostrar estado del proyecto
show_status() {
    echo -e "${BLUE}📊 Estado del proyecto DentalSync:${NC}\n"
    
    # Información básica
    echo -e "${PURPLE}Información del Sistema:${NC}"
    echo -e "  PHP: $(php --version | head -n1)"
    echo -e "  Composer: $(composer --version --no-ansi | head -n1)"
    if command -v node &> /dev/null; then
        echo -e "  Node.js: $(node --version)"
        echo -e "  npm: $(npm --version)"
    else
        echo -e "  Node.js: ${RED}No instalado${NC}"
    fi
    
    echo ""
    
    # Estado de archivos importantes
    echo -e "${PURPLE}Archivos de Configuración:${NC}"
    [ -f .env ] && echo -e "  .env: ${GREEN}✅ Existe${NC}" || echo -e "  .env: ${RED}❌ No existe${NC}"
    [ -f database/database.sqlite ] && echo -e "  Base de datos: ${GREEN}✅ Existe${NC}" || echo -e "  Base de datos: ${RED}❌ No existe${NC}"
    [ -d vendor ] && echo -e "  Dependencias PHP: ${GREEN}✅ Instaladas${NC}" || echo -e "  Dependencias PHP: ${RED}❌ No instaladas${NC}"
    [ -d node_modules ] && echo -e "  Dependencias Node: ${GREEN}✅ Instaladas${NC}" || echo -e "  Dependencias Node: ${RED}❌ No instaladas${NC}"
    
    echo ""
    
    # Estado de migraciones
    echo -e "${PURPLE}Estado de Migraciones:${NC}"
    php artisan migrate:status 2>/dev/null | head -n 10
    
    echo ""
    
    # Servicios disponibles
    echo -e "${PURPLE}Servicios Disponibles:${NC}"
    echo -e "  Laravel: http://localhost:8000"
    echo -e "  Vite Dev: http://localhost:5173"
    echo -e "  MariaDB: localhost:3306 (user: dentalsync, pass: password)"
    echo -e "  Mailpit: http://localhost:8025"
}

# ============================================================================
# SCRIPT PRINCIPAL
# ============================================================================

# Mostrar banner
show_banner

# Verificar que estemos en el directorio correcto
check_project_root

# Manejar comandos
case "${1:-help}" in
    "setup")
        setup_project
        ;;
    "install")
        install_dependencies
        ;;
    "serve")
        serve_laravel
        ;;
    "dev")
        serve_dev
        ;;
    "build")
        build_assets
        ;;
    "fresh")
        fresh_database
        ;;
    "migrate")
        run_migrations
        ;;
    "seed")
        run_seeders
        ;;
    "test")
        run_tests
        ;;
    "tinker")
        open_tinker
        ;;
    "logs")
        view_logs
        ;;
    "clear")
        clear_caches
        ;;
    "status")
        show_status
        ;;
    "help"|*)
        show_help
        ;;
esac