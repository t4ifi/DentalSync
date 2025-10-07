#!/bin/bash

# ============================================================================
# DENTALSYNC - SCRIPT DE SOLUCIÓN DE PROBLEMAS MARIADB
# Soluciona problemas comunes de configuración de MariaDB
# ============================================================================

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${CYAN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║           🔧  SOLUCIÓN PROBLEMAS MARIADB                  ║"
echo "║            Solucionando problemas comunes...              ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Función para limpiar y recrear contenedores
reset_containers() {
    echo -e "${BLUE}🔄 Limpiando contenedores y volúmenes...${NC}"
    
    # Detener contenedores
    docker compose -f Docker/docker-compose.dev.yml down -v
    
    # Eliminar volúmenes de MariaDB
    docker volume rm dentalsync_mariadb_data 2>/dev/null || true
    
    # Limpiar imágenes no utilizadas
    docker system prune -f
    
    echo -e "${GREEN}✅ Contenedores y volúmenes limpiados${NC}"
}

# Función para recrear la base de datos
recreate_database() {
    echo -e "${BLUE}🗄️ Recreando base de datos...${NC}"
    
    # Esperar a que MariaDB esté listo
    echo -e "${YELLOW}⏳ Esperando a que MariaDB esté listo...${NC}"
    sleep 10
    
    # Crear la base de datos con charset correcto
    docker exec dentalsync-mariadb mariadb -u root -prootpassword -e "
        DROP DATABASE IF EXISTS dentalsync;
        CREATE DATABASE dentalsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        GRANT ALL PRIVILEGES ON dentalsync.* TO 'dentalsync'@'%';
        FLUSH PRIVILEGES;
    "
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Base de datos recreada exitosamente${NC}"
    else
        echo -e "${RED}❌ Error al recrear la base de datos${NC}"
        return 1
    fi
}

# Función para ejecutar migraciones
run_migrations() {
    echo -e "${BLUE}📊 Ejecutando migraciones de Laravel...${NC}"
    
    # Esperar un poco más para asegurar la conexión
    sleep 5
    
    # Ejecutar migraciones
    docker exec dentalsync-dev php artisan migrate:fresh --seed
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Migraciones ejecutadas exitosamente${NC}"
    else
        echo -e "${RED}❌ Error al ejecutar migraciones${NC}"
        echo -e "${YELLOW}💡 Verificando configuración de Laravel...${NC}"
        docker exec dentalsync-dev php artisan config:clear
        docker exec dentalsync-dev php artisan cache:clear
        return 1
    fi
}

# Función para verificar permisos de usuario
fix_user_permissions() {
    echo -e "${BLUE}👤 Verificando permisos de usuario MariaDB...${NC}"
    
    docker exec dentalsync-mariadb mariadb -u root -prootpassword -e "
        CREATE USER IF NOT EXISTS 'dentalsync'@'%' IDENTIFIED BY 'password';
        GRANT ALL PRIVILEGES ON dentalsync.* TO 'dentalsync'@'%';
        GRANT ALL PRIVILEGES ON dentalsync.* TO 'dentalsync'@'localhost';
        FLUSH PRIVILEGES;
        SHOW GRANTS FOR 'dentalsync'@'%';
    "
    
    echo -e "${GREEN}✅ Permisos de usuario verificados${NC}"
}

# Función para probar conexión
test_connection() {
    echo -e "${BLUE}🔍 Probando conexión desde diferentes puntos...${NC}"
    
    # Probar desde dentro del contenedor MariaDB
    echo -e "${CYAN}   Probando conexión local en MariaDB...${NC}"
    if docker exec dentalsync-mariadb mariadb -u dentalsync -ppassword -e "SELECT 'Conexión OK' as status;"; then
        echo -e "${GREEN}   ✅ Conexión local OK${NC}"
    else
        echo -e "${RED}   ❌ Conexión local falló${NC}"
    fi
    
    # Probar desde el contenedor de la aplicación
    echo -e "${CYAN}   Probando conexión desde app...${NC}"
    if docker exec dentalsync-dev mariadb -h database -u dentalsync -ppassword -e "SELECT 'Conexión OK' as status;"; then
        echo -e "${GREEN}   ✅ Conexión desde app OK${NC}"
    else
        echo -e "${RED}   ❌ Conexión desde app falló${NC}"
    fi
    
    # Probar conexión de Laravel
    echo -e "${CYAN}   Probando conexión Laravel...${NC}"
    if docker exec dentalsync-dev php artisan migrate:status; then
        echo -e "${GREEN}   ✅ Conexión Laravel OK${NC}"
    else
        echo -e "${RED}   ❌ Conexión Laravel falló${NC}"
    fi
}

# Función para mostrar logs útiles
show_logs() {
    echo -e "${BLUE}📋 Logs recientes de MariaDB:${NC}"
    docker logs --tail 20 dentalsync-mariadb
    
    echo -e "${BLUE}📋 Logs recientes de la aplicación:${NC}"
    docker logs --tail 10 dentalsync-dev
}

# Función principal de diagnóstico
diagnose_and_fix() {
    echo -e "${BLUE}🔍 Diagnosticando problemas...${NC}"
    
    # Verificar si los contenedores están ejecutándose
    if ! docker ps | grep -q "dentalsync-mariadb"; then
        echo -e "${YELLOW}⚠️  MariaDB no está ejecutándose${NC}"
        return 1
    fi
    
    if ! docker ps | grep -q "dentalsync-dev"; then
        echo -e "${YELLOW}⚠️  Aplicación no está ejecutándose${NC}"
        return 1
    fi
    
    # Probar conexión básica
    if docker exec dentalsync-mariadb mariadb -u root -prootpassword -e "SELECT 1;" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ MariaDB responde correctamente${NC}"
    else
        echo -e "${RED}❌ MariaDB no responde${NC}"
        return 1
    fi
    
    # Verificar base de datos
    if ! docker exec dentalsync-mariadb mariadb -u dentalsync -ppassword -e "USE dentalsync;" > /dev/null 2>&1; then
        echo -e "${YELLOW}⚠️  Base de datos no existe o no es accesible${NC}"
        recreate_database
        fix_user_permissions
    fi
    
    # Verificar migraciones
    if ! docker exec dentalsync-dev php artisan migrate:status > /dev/null 2>&1; then
        echo -e "${YELLOW}⚠️  Laravel no puede conectarse${NC}"
        fix_user_permissions
        docker exec dentalsync-dev php artisan config:clear
        sleep 2
    fi
    
    return 0
}

# ============================================================================
# MENÚ INTERACTIVO
# ============================================================================

show_menu() {
    echo -e "${BLUE}🔧 Selecciona una opción:${NC}"
    echo "1. Diagnóstico completo y reparación automática"
    echo "2. Recrear contenedores y base de datos (LIMPIA TODO)"
    echo "3. Solo recrear base de datos"
    echo "4. Ejecutar migraciones"
    echo "5. Verificar permisos de usuario"
    echo "6. Probar conexiones"
    echo "7. Mostrar logs"
    echo "8. Salir"
    echo ""
    read -p "Opción: " choice
}

# ============================================================================
# EJECUCIÓN PRINCIPAL
# ============================================================================

while true; do
    show_menu
    
    case $choice in
        1)
            echo -e "${BLUE}🚀 Ejecutando diagnóstico completo...${NC}"
            if diagnose_and_fix; then
                test_connection
                echo -e "${GREEN}✅ Diagnóstico completado${NC}"
            else
                echo -e "${RED}❌ Se encontraron problemas${NC}"
                show_logs
            fi
            echo ""
            ;;
        2)
            echo -e "${YELLOW}⚠️  ADVERTENCIA: Esto eliminará todos los datos${NC}"
            read -p "¿Continuar? (y/N): " confirm
            if [[ $confirm == "y" || $confirm == "Y" ]]; then
                reset_containers
                echo -e "${BLUE}🚀 Iniciando contenedores...${NC}"
                docker compose -f Docker/docker-compose.dev.yml up -d
                sleep 15
                recreate_database
                fix_user_permissions
                run_migrations
                test_connection
            fi
            echo ""
            ;;
        3)
            recreate_database
            fix_user_permissions
            echo ""
            ;;
        4)
            run_migrations
            echo ""
            ;;
        5)
            fix_user_permissions
            echo ""
            ;;
        6)
            test_connection
            echo ""
            ;;
        7)
            show_logs
            echo ""
            ;;
        8)
            echo -e "${GREEN}👋 ¡Hasta luego!${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}❌ Opción inválida${NC}"
            echo ""
            ;;
    esac
done