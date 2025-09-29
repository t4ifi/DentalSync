#!/bin/bash

# ========================================
# DENTALSYNC - SCRIPT DE GESTIÓN DE DATOS
# Menú principal para gestionar la base de datos
# ========================================

echo "🦷 ========== DENTALSYNC - GESTIÓN DE DATOS =========="
echo ""

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Función para mostrar el menú
mostrar_menu() {
    echo -e "${CYAN}📋 ¿Qué deseas hacer?${NC}"
    echo ""
    echo -e "${BLUE}1.${NC} 👨‍⚕️ Crear usuario (admin, doctor, recepcionista)"
    echo -e "${BLUE}2.${NC} 👥 Crear paciente"
    echo -e "${BLUE}3.${NC} 🎲 Crear datos de prueba completos"
    echo -e "${BLUE}4.${NC} 📋 Listar datos existentes"
    echo -e "${BLUE}5.${NC} 🗑️  Limpiar base de datos"
    echo -e "${BLUE}6.${NC} 🔄 Ejecutar migraciones"
    echo -e "${BLUE}0.${NC} 🚪 Salir"
    echo ""
}

# Función para limpiar la base de datos
limpiar_base_datos() {
    echo -e "${YELLOW}⚠️  ADVERTENCIA: Esto eliminará TODOS los datos de la base de datos.${NC}"
    echo -n "¿Estás seguro? (escribe 'SI' para confirmar): "
    read confirmacion
    
    if [ "$confirmacion" = "SI" ]; then
        echo -e "${RED}🗑️  Limpiando base de datos...${NC}"
        php ../artisan migrate:fresh
        echo -e "${GREEN}✅ Base de datos limpiada exitosamente.${NC}"
    else
        echo -e "${BLUE}❌ Operación cancelada.${NC}"
    fi
}

# Función para ejecutar migraciones
ejecutar_migraciones() {
    echo -e "${BLUE}🔄 Ejecutando migraciones...${NC}"
    php ../artisan migrate
    echo -e "${GREEN}✅ Migraciones ejecutadas.${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "crear-usuario.php" ]; then
    echo -e "${RED}❌ Error: Ejecuta este script desde el directorio /scripts${NC}"
    echo -e "${YELLOW}💡 Usa: cd scripts && ./menu.sh${NC}"
    exit 1
fi

# Loop principal del menú
while true; do
    mostrar_menu
    echo -n "Selecciona una opción (0-6): "
    read opcion
    
    echo ""
    
    case $opcion in
        1)
            echo -e "${GREEN}👨‍⚕️ Creando usuario...${NC}"
            echo ""
            php crear-usuario.php
            ;;
        2)
            echo -e "${GREEN}👥 Creando paciente...${NC}"
            echo ""
            php crear-paciente.php
            ;;
        3)
            echo -e "${GREEN}🎲 Creando datos de prueba...${NC}"
            echo ""
            php crear-datos-prueba.php
            ;;
        4)
            echo -e "${GREEN}📋 Listando datos...${NC}"
            echo ""
            php listar-datos.php
            ;;
        5)
            limpiar_base_datos
            ;;
        6)
            ejecutar_migraciones
            ;;
        0)
            echo -e "${GREEN}🚪 ¡Hasta luego!${NC}"
            echo ""
            exit 0
            ;;
        *)
            echo -e "${RED}❌ Opción inválida. Selecciona 0-6.${NC}"
            ;;
    esac
    
    echo ""
    echo -e "${CYAN}Presiona Enter para continuar...${NC}"
    read
    clear
done