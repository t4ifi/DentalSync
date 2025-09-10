# DentalSync - Docker Setup

## Configuración Minimalista

Este proyecto incluye una configuración Docker simple con:
- Laravel development server (puerto 8000)
- MariaDB 10.11
- Vue.js compilado estáticamente

## Iniciar la aplicación

```bash
# Construir y ejecutar
docker-compose up -d

# Ver logs
docker-compose logs -f

# Acceder a la aplicación
http://localhost:8000
```

## Base de datos

- **Host**: mariadb
- **Puerto**: 3306  
- **Base de datos**: dentalsync
- **Usuario**: dentalsync
- **Contraseña**: password

## Comandos útiles

```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Crear clave de aplicación  
docker-compose exec app php artisan key:generate

# Acceder al contenedor
docker-compose exec app bash

# Parar contenedores
docker-compose down
```
