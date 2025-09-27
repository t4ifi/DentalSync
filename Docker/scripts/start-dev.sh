#!/bin/bash

# ============================================================================
# DENTALSYNC - SCRIPT DE INICIO RÁPIDO
# Levanta todo el entorno de desarrollo con un solo comando
# ============================================================================

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${CYAN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║            🦷 DENTALSYNC - INICIO RÁPIDO                  ║"
echo "║         Levantando entorno de desarrollo...               ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Verificar que Docker esté ejecutándose
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Docker no está ejecutándose. Por favor, inicia Docker primero.${NC}"
    exit 1
fi

# Construir y levantar contenedores
echo -e "${BLUE}🏗️  Construyendo e iniciando contenedores...${NC}"
docker compose -f ../docker-compose.dev.yml up -d --build

# Esperar a que los contenedores estén listos
echo -e "${BLUE}⏳ Esperando a que los servicios estén listos...${NC}"
sleep 10

# Mostrar estado de los contenedores
echo -e "${BLUE}📊 Estado de los contenedores:${NC}"
docker compose -f ../docker-compose.dev.yml ps

echo -e "${GREEN}✅ ¡Entorno de desarrollo listo!${NC}"
echo ""
echo -e "${CYAN}🌐 Servicios disponibles:${NC}"
echo -e "  📱 Aplicación: http://localhost:8000"
echo -e "  ⚡ Vite Dev: http://localhost:5173"
echo -e "  🗄️  MariaDB: localhost:3307"
echo ""
echo -e "${CYAN}🔧 Para entrar al contenedor:${NC}"
echo -e "  docker exec -it dentalsync-dev bash"
echo ""
echo -e "${CYAN}🛑 Para detener todo:${NC}"
echo -e "  docker compose -f ../docker-compose.dev.yml down"