# 🚀 DentalSync - Guía de Inicio Rápido

## ⚡ Setup en 5 Minutos

### Paso 1: Limpiar y Construir
```bash
# Ejecutar desde el directorio raíz del proyecto
docker compose -f Docker/docker-compose.dev.yml down -v
docker system prune -f
docker compose -f Docker/docker-compose.dev.yml up --build -d
```

### Paso 2: Configurar Laravel
```bash
# Entrar al contenedor
docker exec -it dentalsync-dev bash

# Configurar aplicación
cp .env.example .env
sed -i 's/DB_HOST=.*/DB_HOST=database/' .env
php artisan key:generate
php artisan config:clear
php artisan migrate
chmod -R 775 storage bootstrap/cache
```

### Paso 3: Compilar Assets
```bash
# Dentro del contenedor (omitir npm config si da error de permisos)
npm install
npm run build
```

### Paso 4: Iniciar Servidor Laravel
```bash
# Iniciar servidor en background (dentro del contenedor)
php artisan serve --host=0.0.0.0 --port=8000 &
```

### Paso 5: Crear Usuarios
```bash
# Abrir Tinker
php artisan tinker

# Ejecutar uno por uno:
use App\Models\Usuario;
Usuario::create(['usuario' => 'dentista', 'nombre' => 'Dr. Juan Pérez', 'password_hash' => bcrypt('dentista123'), 'rol' => 'dentista', 'activo' => true]);
Usuario::create(['usuario' => 'recepcionista', 'nombre' => 'María González', 'password_hash' => bcrypt('recepcion123'), 'rol' => 'recepcionista', 'activo' => true]);
exit
```

### Paso 6: Verificar
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

## � Problema Común: APP_KEY

Si encuentras el error `RuntimeException: Unsupported cipher or incorrect key length`, es porque la `APP_KEY` en el archivo `.env` no es válida.

### Solución Rápida
```bash
docker exec -it dentalsync-dev bash -c "php artisan key:generate && php artisan config:clear"
```

La aplicación debería funcionar inmediatamente después de ejecutar este comando.

## �🚨 Si Algo Falla

### Reset Completo (Un Solo Comando)
```bash
docker compose -f Docker/docker-compose.dev.yml down -v && \
docker system prune -f && \
docker compose -f Docker/docker-compose.dev.yml up --build -d && \
sleep 15 && \
docker exec -d dentalsync-dev bash -c "
cp .env.example .env &&
sed -i 's/DB_HOST=.*/DB_HOST=database/' .env &&
php artisan key:generate &&
php artisan config:clear &&
php artisan migrate &&
chmod -R 775 storage bootstrap/cache &&
npm install &&
npm run build &&
php artisan serve --host=0.0.0.0 --port=8000
"
```

### Errores Comunes
- **Error 500**: Ejecutar `php artisan key:generate && php artisan config:clear`
- **Cipher Error**: La APP_KEY no está configurada correctamente, ejecutar `php artisan key:generate`
- **DB Connection**: Verificar `DB_HOST=database` en `.env`
- **Permisos**: Ejecutar `chmod -R 775 storage bootstrap/cache`
- **Puerto ocupado**: Cambiar puerto en `docker-compose.dev.yml`
- **No conecta**: Asegurar que el servidor Laravel esté corriendo: `php artisan serve --host=0.0.0.0 --port=8000`

### Comandos Útiles
```bash
# Iniciar servidor Laravel en background
docker exec -d dentalsync-dev php artisan serve --host=0.0.0.0 --port=8000

# Ver logs del servidor Laravel
docker exec -it dentalsync-dev tail -f storage/logs/laravel.log

# Parar servidor Laravel
docker exec -it dentalsync-dev pkill -f "artisan serve"
```

## ✅ Verificación Rápida
```bash
docker ps | grep dentalsync           # 2 contenedores corriendo
curl -I http://localhost:8000         # HTTP/1.1 200 OK
```

---
**Documentación actualizada:** 8 de octubre de 2025