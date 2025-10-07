#!/bin/bash

# ============================================================================
# DENTALSYNC - SCRIPT DE DETENCIÓN
# Detiene todos los servicios de desarrollo de forma segura
# ============================================================================

# Colores
RED='\033[0;31m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${CYAN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║            🛑 DENTALSYNC - DETENIENDO SERVICIOS           ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Detener servicios
echo -e "${YELLOW}🛑 Deteniendo servicios de desarrollo...${NC}"
docker compose -f ../docker-compose.dev.yml down

echo -e "${YELLOW}🧹 ¿Quieres limpiar volúmenes también? (y/N)${NC}"
read -r response
if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
    echo -e "${YELLOW}🗑️  Limpiando volúmenes...${NC}"
    docker compose -f ../docker-compose.dev.yml down -v
    docker system prune -f
    echo -e "${CYAN}✅ Volúmenes limpiados${NC}"
fi

echo -e "${CYAN}✅ ¡Servicios detenidos correctamente!${NC}"