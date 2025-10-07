# 🚀 DentalSync - Guía de Inicio Rápido

## ⚡ Setup en 5 Minutos

### Paso 1: Limpiar y Construir
```bash
# Ejecutar desde el directorio raíz del proyecto
docker-compose -f Docker/docker-compose.dev.yml down -v
docker system prune -f
docker-compose -f Docker/docker-compose.dev.yml up --build -d
```

### Paso 2: Configurar Laravel
```bash
# Entrar al contenedor
docker exec -it dentalsync-dev bash

# Configurar aplicación
cp .env.example .env
php artisan key:generate
php artisan migrate

# IMPORTANTE: Editar .env y configurar DB_HOST=database
nano .env
```

### Paso 3: Compilar Assets
```bash
# Dentro del contenedor
npm config set cache /tmp/.npm --global
npm install
npm run build
```

### Paso 4: Crear Usuarios
```bash
# Abrir Tinker
php artisan tinker

# Ejecutar uno por uno:
use App\Models\Usuario;
Usuario::create(['usuario' => 'dentista', 'nombre' => 'Dr. Juan Pérez', 'password_hash' => bcrypt('dentista123'), 'rol' => 'dentista', 'activo' => true]);
Usuario::create(['usuario' => 'recepcionista', 'nombre' => 'María González', 'password_hash' => bcrypt('recepcion123'), 'rol' => 'recepcionista', 'activo' => true]);
exit
```

### Paso 5: Verificar
```bash
# Verificar aplicación
curl http://localhost:8000
# Debe retornar código 200

# Abrir en navegador
http://localhost:8000
```

## 🔧 Configuración .env Crítica

```env
DB_CONNECTION=mariadb
DB_HOST=database          # ⚠️ NO usar localhost
DB_PORT=3306
DB_DATABASE=dentalsync
DB_USERNAME=dentalsync
DB_PASSWORD=password
```

## 👥 Usuarios de Prueba

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| `dentista` | `dentista123` | dentista |
| `recepcionista` | `recepcion123` | recepcionista |

## 🚨 Si Algo Falla

### Reset Completo (Un Solo Comando)
```bash
docker-compose -f Docker/docker-compose.dev.yml down -v && \
docker system prune -f && \
docker-compose -f Docker/docker-compose.dev.yml up --build -d && \
sleep 10 && \
docker exec -it dentalsync-dev bash -c "
cp .env.example .env &&
sed -i 's/DB_HOST=.*/DB_HOST=database/' .env &&
php artisan key:generate &&
php artisan migrate &&
npm config set cache /tmp/.npm --global &&
npm install &&
npm run build
"
```

### Errores Comunes
- **Error 500**: Ejecutar `npm run build`
- **DB Connection**: Verificar `DB_HOST=database` en `.env`
- **Puerto ocupado**: Cambiar puerto en `docker-compose.dev.yml`

## ✅ Verificación Rápida
```bash
docker ps | grep dentalsync           # 2 contenedores corriendo
curl -I http://localhost:8000         # HTTP/1.1 200 OK
```

---
**Documentación actualizada:** 7 de octubre de 2025