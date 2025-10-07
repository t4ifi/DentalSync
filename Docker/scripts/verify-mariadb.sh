#!/bin/bash

# ============================================================================
# DENTALSYNC - SCRIPT DE VERIFICACIÓN DE MARIADB
# Verifica que la configuración de MariaDB esté correcta
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
echo "║           🗄️  VERIFICACIÓN DE MARIADB                     ║"
echo "║            Verificando configuración...                   ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Función para verificar conexión a MariaDB
check_mariadb_connection() {
    echo -e "${BLUE}🔍 Verificando conexión a MariaDB...${NC}"
    
    if docker exec dentalsync-mariadb mariadb -u dentalsync -ppassword -e "SELECT VERSION();" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Conexión a MariaDB exitosa${NC}"
        VERSION=$(docker exec dentalsync-mariadb mariadb -u dentalsync -ppassword -e "SELECT VERSION();" -s -N)
        echo -e "${CYAN}   Versión: ${VERSION}${NC}"
        return 0
    else
        echo -e "${RED}❌ Error: No se puede conectar a MariaDB${NC}"
        return 1
    fi
}

# Función para verificar base de datos
check_database() {
    echo -e "${BLUE}🔍 Verificando base de datos 'dentalsync'...${NC}"
    
    if docker exec dentalsync-mariadb mariadb -u dentalsync -ppassword -e "USE dentalsync; SHOW TABLES;" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Base de datos 'dentalsync' existe${NC}"
        TABLES=$(docker exec dentalsync-mariadb mariadb -u dentalsync -ppassword -e "USE dentalsync; SHOW TABLES;" -s -N | wc -l)
        echo -e "${CYAN}   Tablas encontradas: ${TABLES}${NC}"
        return 0
    else
        echo -e "${RED}❌ Error: Base de datos 'dentalsync' no existe${NC}"
        return 1
    fi
}

# Función para verificar configuración de charset
check_charset() {
    echo -e "${BLUE}🔍 Verificando charset y collation...${NC}"
    
    CHARSET=$(docker exec dentalsync-mariadb mariadb -u dentalsync -ppassword -e "SELECT @@character_set_database;" -s -N)
    COLLATION=$(docker exec dentalsync-mariadb mariadb -u dentalsync -ppassword -e "SELECT @@collation_database;" -s -N)
    
    if [[ "$CHARSET" == "utf8mb4" && "$COLLATION" == "utf8mb4_unicode_ci" ]]; then
        echo -e "${GREEN}✅ Charset y collation correctos${NC}"
        echo -e "${CYAN}   Charset: ${CHARSET}${NC}"
        echo -e "${CYAN}   Collation: ${COLLATION}${NC}"
        return 0
    else
        echo -e "${YELLOW}⚠️  Charset o collation no óptimos${NC}"
        echo -e "${CYAN}   Charset actual: ${CHARSET} (recomendado: utf8mb4)${NC}"
        echo -e "${CYAN}   Collation actual: ${COLLATION} (recomendado: utf8mb4_unicode_ci)${NC}"
        return 1
    fi
}

# Función para verificar configuración desde Laravel
check_laravel_connection() {
    echo -e "${BLUE}🔍 Verificando conexión desde Laravel...${NC}"
    
    if docker exec dentalsync-dev php artisan migrate:status > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Laravel puede conectarse a MariaDB${NC}"
        echo -e "${BLUE}📊 Estado de migraciones:${NC}"
        docker exec dentalsync-dev php artisan migrate:status
        return 0
    else
        echo -e "${RED}❌ Error: Laravel no puede conectarse a MariaDB${NC}"
        echo -e "${YELLOW}💡 Verifica las variables de entorno en docker-compose.yml${NC}"
        return 1
    fi
}

# Función para mostrar configuración actual
show_current_config() {
    echo -e "${BLUE}📋 Configuración actual de MariaDB:${NC}"
    echo -e "${CYAN}   Host: database (dentro del contenedor)${NC}"
    echo -e "${CYAN}   Puerto: 3306 (interno) -> 3307 (externo)${NC}"
    echo -e "${CYAN}   Base de datos: dentalsync${NC}"
    echo -e "${CYAN}   Usuario: dentalsync${NC}"
    echo -e "${CYAN}   Contraseña: password${NC}"
}

# Función para mostrar comandos útiles
show_useful_commands() {
    echo -e "${BLUE}🔧 Comandos útiles:${NC}"
    echo -e "${CYAN}   Conectar a MariaDB desde el host:${NC}"
    echo -e "     mariadb -h 127.0.0.1 -P 3307 -u dentalsync -ppassword dentalsync"
    echo ""
    echo -e "${CYAN}   Conectar desde el contenedor de la app:${NC}"
    echo -e "     docker exec -it dentalsync-dev mariadb -h database -u dentalsync -ppassword dentalsync"
    echo ""
    echo -e "${CYAN}   Ejecutar migraciones:${NC}"
    echo -e "     docker exec dentalsync-dev php artisan migrate"
    echo ""
    echo -e "${CYAN}   Ver logs de MariaDB:${NC}"
    echo -e "     docker logs dentalsync-mariadb"
}

# ============================================================================
# EJECUCIÓN PRINCIPAL
# ============================================================================

# Verificar que los contenedores estén ejecutándose
if ! docker ps | grep -q "dentalsync-mariadb"; then
    echo -e "${RED}❌ Error: El contenedor MariaDB no está ejecutándose${NC}"
    echo -e "${YELLOW}💡 Ejecuta: docker compose -f Docker/docker-compose.dev.yml up -d${NC}"
    exit 1
fi

# Ejecutar verificaciones
echo -e "${BLUE}🚀 Iniciando verificaciones...${NC}"
echo ""

show_current_config
echo ""

check_mariadb_connection
echo ""

check_database
echo ""

check_charset
echo ""

check_laravel_connection
echo ""

show_useful_commands
echo ""

echo -e "${GREEN}✅ Verificación completada${NC}"