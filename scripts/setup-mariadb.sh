#!/bin/bash

# ========================================
# 🗄️ SCRIPT DE CONFIGURACIÓN MARIADB - DENTALSYNC
# ========================================
# 
# Este script configura la base de datos MariaDB para DentalSync
# Autor: NullDevs - Andrés Núñez
# Fecha: Octubre 2025
# Versión: 1.0
#

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # Sin color

# Configuración por defecto
DB_NAME="dentalsync"
DB_USER="root"
DB_HOST="127.0.0.1"
DB_PORT="3306"

echo -e "${BLUE}🦷 DentalSync - Configuración de MariaDB${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Verificar si MariaDB está instalado
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}❌ MariaDB/MySQL no está instalado${NC}"
    echo -e "${YELLOW}Por favor instala MariaDB primero:${NC}"
    echo -e "  Ubuntu/Debian: sudo apt install mariadb-server"
    echo -e "  Arch Linux: sudo pacman -S mariadb"
    echo -e "  macOS: brew install mariadb"
    exit 1
fi

echo -e "${GREEN}✅ MariaDB encontrado${NC}"

# Verificar si MariaDB está corriendo
if ! systemctl is-active --quiet mariadb 2>/dev/null && ! systemctl is-active --quiet mysql 2>/dev/null; then
    echo -e "${YELLOW}⚠️  MariaDB no está corriendo. Intentando iniciar...${NC}"
    
    if systemctl start mariadb 2>/dev/null || systemctl start mysql 2>/dev/null; then
        echo -e "${GREEN}✅ MariaDB iniciado correctamente${NC}"
    else
        echo -e "${RED}❌ No se pudo iniciar MariaDB${NC}"
        echo -e "${YELLOW}Inicia MariaDB manualmente:${NC}"
        echo -e "  sudo systemctl start mariadb"
        exit 1
    fi
else
    echo -e "${GREEN}✅ MariaDB está corriendo${NC}"
fi

# Solicitar credenciales de root
echo ""
echo -e "${BLUE}Configuración de base de datos:${NC}"
read -p "Nombre de la base de datos [$DB_NAME]: " input_db_name
DB_NAME=${input_db_name:-$DB_NAME}

read -p "Usuario de MariaDB [$DB_USER]: " input_db_user  
DB_USER=${input_db_user:-$DB_USER}

read -p "Host de MariaDB [$DB_HOST]: " input_db_host
DB_HOST=${input_db_host:-$DB_HOST}

read -p "Puerto de MariaDB [$DB_PORT]: " input_db_port
DB_PORT=${input_db_port:-$DB_PORT}

echo -e "${YELLOW}Ingresa la contraseña de root de MariaDB:${NC}"
read -s DB_ROOT_PASSWORD

echo ""
echo -e "${BLUE}Configurando base de datos...${NC}"

# Crear base de datos si no existe
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Base de datos '$DB_NAME' creada/verificada${NC}"
else
    echo -e "${RED}❌ Error al crear la base de datos${NC}"
    echo -e "${YELLOW}Verifica las credenciales e intenta nuevamente${NC}"
    exit 1
fi

# Verificar conexión a la base de datos
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_ROOT_PASSWORD" "$DB_NAME" -e "SELECT 1;" >/dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Conexión a la base de datos exitosa${NC}"
else
    echo -e "${RED}❌ Error al conectar con la base de datos${NC}"
    exit 1
fi

# Actualizar archivo .env
echo ""
echo -e "${BLUE}Actualizando configuración de Laravel...${NC}"

if [ -f ".env" ]; then
    # Crear backup del .env
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    
    # Actualizar configuración de base de datos
    sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=mariadb/" .env
    sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i "s/^DB_PORT=.*/DB_PORT=$DB_PORT/" .env
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    
    # Solicitar contraseña de la aplicación
    echo -e "${YELLOW}Ingresa la contraseña que usará la aplicación para conectar a MariaDB:${NC}"
    read -s APP_DB_PASSWORD
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$APP_DB_PASSWORD/" .env
    
    echo -e "${GREEN}✅ Archivo .env actualizado${NC}"
else
    echo -e "${YELLOW}⚠️  Archivo .env no encontrado${NC}"
    echo -e "${BLUE}Copiando desde .env.example...${NC}"
    
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✅ Archivo .env creado desde .env.example${NC}"
        
        # Configurar .env
        sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=mariadb/" .env
        sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" .env
        sed -i "s/^DB_PORT=.*/DB_PORT=$DB_PORT/" .env
        sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
        sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
        
        echo -e "${YELLOW}Ingresa la contraseña para la aplicación:${NC}"
        read -s APP_DB_PASSWORD
        sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$APP_DB_PASSWORD/" .env
        
        echo -e "${GREEN}✅ Configuración aplicada al .env${NC}"
    else
        echo -e "${RED}❌ No se encontró .env.example${NC}"
        exit 1
    fi
fi

# Ejecutar migraciones
echo ""
echo -e "${BLUE}Ejecutando migraciones de Laravel...${NC}"

if php artisan migrate --force; then
    echo -e "${GREEN}✅ Migraciones ejecutadas correctamente${NC}"
else
    echo -e "${RED}❌ Error al ejecutar migraciones${NC}"
    echo -e "${YELLOW}Intenta ejecutar manualmente: php artisan migrate${NC}"
fi

# Verificar tablas creadas
echo ""
echo -e "${BLUE}Verificando tablas creadas...${NC}"

TABLES=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_ROOT_PASSWORD" "$DB_NAME" -e "SHOW TABLES;" -s 2>/dev/null | wc -l)

if [ "$TABLES" -gt 0 ]; then
    echo -e "${GREEN}✅ $TABLES tablas encontradas en la base de datos${NC}"
    
    # Mostrar algunas tablas principales
    echo -e "${BLUE}Tablas principales:${NC}"
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_ROOT_PASSWORD" "$DB_NAME" -e "
    SELECT TABLE_NAME as 'Tabla', TABLE_ROWS as 'Filas' 
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = '$DB_NAME' 
    AND TABLE_NAME IN ('usuarios', 'pacientes', 'citas', 'tratamientos', 'pagos', 'placas_dentales')
    ORDER BY TABLE_NAME;" 2>/dev/null
else
    echo -e "${YELLOW}⚠️  No se encontraron tablas. Las migraciones podrían no haberse ejecutado correctamente${NC}"
fi

echo ""
echo -e "${GREEN}🎉 ¡Configuración de MariaDB completada!${NC}"
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✅ Base de datos: $DB_NAME${NC}"
echo -e "${GREEN}✅ Host: $DB_HOST:$DB_PORT${NC}"
echo -e "${GREEN}✅ Usuario: $DB_USER${NC}"
echo -e "${GREEN}✅ Archivo .env actualizado${NC}"
echo -e "${GREEN}✅ Migraciones ejecutadas${NC}"
echo ""
echo -e "${BLUE}Próximos pasos:${NC}"
echo -e "1. Ejecutar: ${YELLOW}php artisan serve${NC}"
echo -e "2. Crear datos de prueba: ${YELLOW}cd scripts && php crear-datos-prueba.php${NC}"
echo -e "3. Abrir navegador en: ${YELLOW}http://localhost:8000${NC}"
echo ""