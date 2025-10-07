# 🔍 DentalSync - Comandos de Verificación

## ✅ Lista de Verificación Post-Instalación

### 1. Verificar Contenedores Docker
```bash
# Comando
docker ps

# Resultado esperado: Debe mostrar 2 contenedores
# - dentalsync-dev (Up)
# - dentalsync-mariadb (Up)
```

### 2. Verificar Conectividad de Base de Datos
```bash
# Comando
docker exec -it dentalsync-dev mariadb -h database -u dentalsync -ppassword --skip-ssl -e "SELECT 'DB Connection OK' as status;"

# Resultado esperado:
# +------------------+
# | status           |
# +------------------+
# | DB Connection OK |
# +------------------+
```

### 3. Verificar Configuración Laravel
```bash
# Comando
docker exec -it dentalsync-dev php artisan config:show database.connections.mariadb

# Resultado esperado:
# host ....................................... database
# port ....................................... 3306
# database ................................... dentalsync
# username ................................... dentalsync
```

### 4. Verificar Migraciones de Base de Datos
```bash
# Comando
docker exec -it dentalsync-dev php artisan migrate:status

# Resultado esperado: Todas las migraciones deben mostrar [1] Ran
```

### 5. Verificar Assets Compilados
```bash
# Comando
docker exec -it dentalsync-dev ls -la public/build/manifest.json

# Resultado esperado: El archivo debe existir con timestamp reciente
```

### 6. Verificar Aplicación Web
```bash
# Comando
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000

# Resultado esperado: 200
```

### 7. Verificar Usuarios Creados
```bash
# Comando
docker exec -it dentalsync-dev php artisan tinker --execute="use App\Models\Usuario; Usuario::all()->toArray();"

# Resultado esperado: Array con 2 usuarios (dentista y recepcionista)
```

## 🚨 Diagnóstico de Problemas

### Si la aplicación retorna Error 500
```bash
# Ver logs de Laravel
docker exec -it dentalsync-dev tail -f storage/logs/laravel.log

# Ver logs del contenedor
docker logs dentalsync-dev --tail 50
```

### Si no se puede conectar a la base de datos
```bash
# Verificar logs de MariaDB
docker logs dentalsync-mariadb --tail 50

# Verificar configuración .env
docker exec -it dentalsync-dev grep DB_ .env
```

### Si los assets no cargan
```bash
# Verificar que Vite compiló correctamente
docker exec -it dentalsync-dev ls -la public/build/

# Re-compilar assets si es necesario
docker exec -it dentalsync-dev npm run build
```

## 📊 Comando de Diagnóstico Completo

Ejecuta este comando para un diagnóstico completo:

```bash
#!/bin/bash
echo "=== DIAGNÓSTICO DENTALSYNC ==="
echo ""

echo "1. Contenedores Docker:"
docker ps | grep dentalsync
echo ""

echo "2. Conectividad DB:"
docker exec -it dentalsync-dev mariadb -h database -u dentalsync -ppassword --skip-ssl -e "SELECT 'OK';" 2>/dev/null || echo "❌ Error de conexión"
echo ""

echo "3. Configuración Laravel:"
docker exec -it dentalsync-dev php artisan config:show database.connections.mariadb | grep host
echo ""

echo "4. Assets compilados:"
docker exec -it dentalsync-dev ls public/build/manifest.json 2>/dev/null && echo "✅ Manifest existe" || echo "❌ Manifest no existe"
echo ""

echo "5. Aplicación web:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Aplicación responde correctamente (HTTP $HTTP_CODE)"
else
    echo "❌ Aplicación no responde (HTTP $HTTP_CODE)"
fi
echo ""

echo "6. Usuarios en base de datos:"
USER_COUNT=$(docker exec -it dentalsync-dev php artisan tinker --execute="use App\Models\Usuario; echo Usuario::count();" 2>/dev/null | tail -1)
echo "Usuarios creados: $USER_COUNT"
echo ""

echo "=== FIN DIAGNÓSTICO ==="
```

## 🔄 Reset Rápido

Si necesitas resetear completamente el entorno:

```bash
# Detener y limpiar
docker-compose -f Docker/docker-compose.dev.yml down -v
docker system prune -f

# Usar el script automático
./Docker/scripts/setup-complete.sh
```

---
**Fecha:** 7 de octubre de 2025  
**Comandos validados en:** Ubuntu/Linux con Docker 24.x