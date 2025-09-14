# 🐳 DentalSync - Documentación Docker

## 📋 Descripción General

DentalSync incluye una configuración Docker completa para facilitar el desarrollo y despliegue del sistema de gestión dental. Esta documentación explica todos los archivos Docker disponibles y cómo utilizarlos.

## 📁 Archivos Docker Disponibles

### 1. `dockerfile` - Configuración Minimalista
**Propósito**: Entorno de desarrollo rápido y simple.

**Características**:
- ✅ PHP 8.2-FPM
- ✅ Extensiones PHP esenciales (MySQL, GD, BCMath, etc.)
- ✅ Node.js y npm para compilar assets
- ✅ Composer para dependencias PHP
- ✅ Servidor de desarrollo Laravel (`php artisan serve`)
- ✅ Puerto 8000 expuesto

**Uso recomendado**: Desarrollo local rápido.

### 2. `Dockerfile.new` - Configuración Completa de Producción
**Propósito**: Entorno de producción robusto y optimizado.

**Características**:
- ✅ PHP 8.2-FPM optimizado
- ✅ Nginx como servidor web
- ✅ Supervisor para gestión de procesos
- ✅ Usuario dedicado para seguridad
- ✅ OPcache habilitado para rendimiento
- ✅ Configuración de producción
- ✅ Build optimizado de assets
- ✅ Puerto 80 expuesto

**Uso recomendado**: Despliegue en producción.

### 3. `docker-compose.yml` - Orquestación Simple
**Servicios incluidos**:
- **app**: Aplicación Laravel DentalSync
- **mariadb**: Base de datos MariaDB 10.11

### 4. `.env.docker` - Variables de Entorno para Docker
Configuración específica para el entorno dockerizado.

## 🚀 Guías de Uso

### Desarrollo Local (Configuración Minimalista)

```bash
# 1. Construir y levantar servicios
docker-compose up -d

# 2. Generar clave de aplicación (primera vez)
docker-compose exec app php artisan key:generate

# 3. Ejecutar migraciones (primera vez)
docker-compose exec app php artisan migrate

# 4. Instalar dependencias de frontend (primera vez)
docker-compose exec app npm install
docker-compose exec app npm run dev

# 5. Acceder a la aplicación
# http://localhost:8000
```

### Producción (Dockerfile.new)

```bash
# 1. Construir imagen de producción
docker build -f Docker/Dockerfile.new -t dentalsync:prod .

# 2. Crear red Docker
docker network create dentalsync-network

# 3. Levantar base de datos
docker run -d \
  --name dentalsync-db \
  --network dentalsync-network \
  -e MYSQL_DATABASE=dentalsync \
  -e MYSQL_USER=dentalsync \
  -e MYSQL_PASSWORD=strongpassword \
  -e MYSQL_ROOT_PASSWORD=rootstrongpassword \
  -v dentalsync-db-data:/var/lib/mysql \
  mariadb:10.11

# 4. Levantar aplicación
docker run -d \
  --name dentalsync-app \
  --network dentalsync-network \
  -p 80:80 \
  -e DB_HOST=dentalsync-db \
  -e DB_PASSWORD=strongpassword \
  dentalsync:prod
```

## 🔧 Comandos Útiles

### Gestión de Contenedores

```bash
# Ver estado de servicios
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f app
docker-compose logs -f mariadb

# Reiniciar servicios
docker-compose restart

# Parar servicios
docker-compose down

# Parar y eliminar volúmenes
docker-compose down -v
```

### Comandos de Laravel en Docker

```bash
# Acceder al contenedor
docker-compose exec app bash

# Ejecutar comandos Artisan
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:list

# Instalar dependencias
docker-compose exec app composer install
docker-compose exec app npm install

# Compilar assets
docker-compose exec app npm run dev
docker-compose exec app npm run build
```

### Gestión de Base de Datos

```bash
# Acceder a MySQL/MariaDB
docker-compose exec mariadb mysql -u dentalsync -p dentalsync

# Backup de base de datos
docker-compose exec mariadb mysqldump -u dentalsync -p dentalsync > backup.sql

# Restaurar backup
docker-compose exec -T mariadb mysql -u dentalsync -p dentalsync < backup.sql

# Ver logs de la base de datos
docker-compose logs mariadb
```

## 🗄️ Configuración de Base de Datos

### Desarrollo (docker-compose.yml)
```env
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=dentalsync
DB_USERNAME=dentalsync
DB_PASSWORD=password
DB_ROOT_PASSWORD=rootpassword
```

### Producción (Recomendado)
```env
DB_HOST=dentalsync-db
DB_PORT=3306
DB_DATABASE=dentalsync
DB_USERNAME=dentalsync
DB_PASSWORD=your-strong-password
DB_ROOT_PASSWORD=your-root-strong-password
```

## 🔒 Configuración de Seguridad

### Para Producción
1. **Cambiar contraseñas por defecto**
2. **Usar variables de entorno seguras**
3. **Configurar firewall apropiado**
4. **Habilitar SSL/HTTPS**
5. **Limitar acceso a puertos de base de datos**

```bash
# Ejemplo con variables de entorno seguras
docker run -d \
  --name dentalsync-app \
  --network dentalsync-network \
  -p 443:80 \
  -e DB_PASSWORD="${DB_PASSWORD}" \
  -e APP_KEY="${APP_KEY}" \
  dentalsync:prod
```

## 🐛 Solución de Problemas

### Problemas Comunes

**1. Error de conexión a base de datos**
```bash
# Verificar que MariaDB esté corriendo
docker-compose ps

# Verificar logs de la base de datos
docker-compose logs mariadb

# Reiniciar servicios
docker-compose restart
```

**2. Permisos de archivos**
```bash
# Corregir permisos de storage
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

**3. Assets no compilados**
```bash
# Recompilar assets
docker-compose exec app npm run dev
```

**4. Caché de configuración**
```bash
# Limpiar todas las cachés
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Logs y Debugging

```bash
# Ver todos los logs
docker-compose logs

# Logs con timestamps
docker-compose logs -t

# Seguir logs en tiempo real
docker-compose logs -f --tail=100

# Logs de un contenedor específico
docker logs dentalsync-app
```

## 📊 Monitoreo y Rendimiento

### Verificar Recursos
```bash
# Uso de recursos de contenedores
docker stats

# Información detallada de un contenedor
docker inspect dentalsync-app

# Procesos corriendo en un contenedor
docker-compose exec app ps aux
```

### Optimización de Rendimiento
```bash
# Limpiar imágenes no utilizadas
docker image prune -f

# Limpiar contenedores detenidos
docker container prune -f

# Limpiar volúmenes no utilizados
docker volume prune -f

# Limpieza completa del sistema
docker system prune -a -f
```

## 🚧 Notas de Desarrollo

- **Puerto 8000**: Aplicación en desarrollo
- **Puerto 3306**: Base de datos MariaDB (acceso directo)
- **Puerto 80**: Aplicación en producción (Dockerfile.new)

## 📞 Soporte

Para problemas específicos con Docker, revisar:
1. Logs de contenedores
2. Configuración de red
3. Variables de entorno
4. Permisos de archivos

---
**Autor**: Andrés Núñez - NullDevs  
**Versión**: 2.0  
**Actualizado**: Septiembre 2025
