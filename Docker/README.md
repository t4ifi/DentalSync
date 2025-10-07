# 🦷 DentalSync - Guía Completa del Entorno Docker

Esta guía te ayudará a configurar y usar el entorno de desarrollo completo de DentalSync usando Docker. Documentación actualizada con procedimientos validados en producción.

## 📋 Tabla de Contenidos

- [🚀 Inicio Rápido](#-inicio-rápido)
- [🏗️ Arquitectura del Entorno](#️-arquitectura-del-entorno)
- [📦 Servicios Incluidos](#-servicios-incluidos)
- [⚙️ Configuración Paso a Paso](#️-configuración-paso-a-paso)
- [🔧 Scripts y Comandos](#-scripts-y-comandos)
- [� Creación de Usuarios](#-creación-de-usuarios)
- [🗄️ Base de Datos](#️-base-de-datos)
- [🐛 Troubleshooting](#-troubleshooting)
- [📚 Comandos de Referencia](#-comandos-de-referencia)
- [🔍 Verificación del Sistema](#-verificación-del-sistema)

---

## 🚀 Inicio Rápido

### ✅ Prerrequisitos

- [Docker](https://www.docker.com/get-started) (versión 20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) (versión 2.0+)
- Git configurado
- Puerto 8000 y 3307 disponibles

### 🐳 Proceso de Configuración Completo

1. **Limpiar entorno Docker existente (si existe):**
   ```bash
   # Detener todos los contenedores relacionados
   docker stop $(docker ps -aq --filter name=dentalsync) 2>/dev/null
   
   # Eliminar contenedores
   docker rm $(docker ps -aq --filter name=dentalsync) 2>/dev/null
   
   # Eliminar volúmenes
   docker volume prune -f
   
   # Limpiar sistema Docker
   docker system prune -f
   ```

2. **Construir e iniciar servicios:**
   ```bash
   cd /path/to/DentalSync
   docker-compose -f Docker/docker-compose.dev.yml up --build -d
   ```

3. **Verificar que los contenedores estén ejecutándose:**
   ```bash
   docker ps
   # Debe mostrar: dentalsync-dev y dentalsync-mariadb
   ```

4. **Configurar la aplicación Laravel (dentro del contenedor):**
   ```bash
   # Entrar al contenedor
   docker exec -it dentalsync-dev bash
   
   # Copiar archivo de configuración
   cp .env.example .env
   
   # Generar clave de aplicación
   php artisan key:generate
   
   # Ejecutar migraciones
   php artisan migrate
   
   # Instalar dependencias de Node.js
   npm install
   
   # Compilar assets
   npm run build
   ```

5. **Acceder a la aplicación:**
   - Abrir navegador en: http://localhost:8000
   - ✅ Deberías ver la interfaz de login de DentalSync

---

## 🏗️ Arquitectura del Entorno

```
┌─────────────────────────────────────────────────────────────┐
│                    DentalSync Dev Environment               │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Laravel   │  │    Vue.js   │  │   MariaDB   │         │
│  │  (Port 8000)│  │ (Port 5173) │  │ (Port 3307) │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Volúmenes Persistentes                │   │
│  │  • Código fuente (bind mount)                      │   │
│  │  • Datos de MariaDB                                │   │
│  │  • Caché de Composer y npm                         │   │
│  │  • Historial de bash                               │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 Servicios Incluidos

### 🚀 Aplicación Principal (dentalsync-dev)
- **Base:** PHP 8.2-FPM con todas las extensiones necesarias
- **Includes:** Node.js 20, Composer, npm, herramientas de desarrollo
- **Puerto:** 8000 (Laravel) + 5173 (Vite)

### 🗄️ Base de Datos (dentalsync-mariadb)
- **Imagen:** MariaDB 11.2
- **Puerto:** 3307 (host) → 3306 (contenedor)
- **Usuario:** `dentalsync` | **Contraseña:** `password`
- **Base de datos:** `dentalsync`
- **Host interno:** `database` (para conexiones desde el contenedor app)
- **Configuración:** UTF8MB4, collation unicode_ci optimizada

---

## ⚙️ Configuración Paso a Paso

### 📁 Estructura de Archivos Docker

```
Docker/
├── Dockerfile.dev              # Imagen PHP 8.2 + Node.js 20
├── docker-compose.dev.yml      # Orquestación completa
├── mariadb/
│   └── mariadb.cnf            # Configuración MariaDB optimizada
└── scripts/                   # Scripts de automatización
```

### 🔧 Configuración del Archivo .env

**IMPORTANTE:** La configuración `.env` debe ser editada **dentro del contenedor Docker**, no en tu máquina local.

```bash
# Entrar al contenedor
docker exec -it dentalsync-dev bash

# Editar .env dentro del contenedor
nano .env
```

**Configuración .env requerida:**

```env
# Aplicación Laravel
APP_NAME=DentalSync
APP_ENV=local
APP_KEY=base64:... # Se genera con php artisan key:generate
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Base de datos MariaDB (configuración para Docker)
DB_CONNECTION=mariadb
DB_HOST=database                # ⚠️ IMPORTANTE: usar 'database', no 'localhost'
DB_PORT=3306
DB_DATABASE=dentalsync
DB_USERNAME=dentalsync
DB_PASSWORD=password

# Configuración de sesiones
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database

# Configuración Vite para desarrollo
VITE_APP_NAME="${APP_NAME}"
```

### 🚨 Errores Comunes de Configuración

1. **Error "getaddrinfo for database failed":**
   - ❌ `DB_HOST=localhost`
   - ✅ `DB_HOST=database`

2. **Error "Vite manifest not found":**
   - Ejecutar: `npm run build` dentro del contenedor

3. **Permisos de npm:**
   - Ejecutar: `npm config set cache /tmp/.npm --global`

---

## 🔧 Scripts Disponibles

### `/Docker/scripts/start-dev.sh`
Inicia todo el entorno de desarrollo con un solo comando.

```bash
./Docker/scripts/start-dev.sh
```

### `/Docker/scripts/stop-dev.sh`
Detiene todos los servicios de forma segura.

```bash
./Docker/scripts/stop-dev.sh
```

### `/Docker/scripts/dev-tools.sh`
Herramientas avanzadas de desarrollo (usar dentro del contenedor).

```bash
# Mostrar ayuda
./dev-tools.sh help

# Configurar proyecto completo
./dev-tools.sh setup

# Iniciar desarrollo completo (Laravel + Vite)
./dev-tools.sh dev

# Ver estado del proyecto
./dev-tools.sh status

# Reset completo de base de datos
./dev-tools.sh fresh
```

### Scripts del Contenedor

Estos scripts están disponibles dentro del contenedor en `/home/developer/scripts/`:

- `install-deps.sh` - Instala dependencias de PHP y Node.js
- `setup-project.sh` - Configura el proyecto Laravel completo

---

## 💡 Uso con VS Code

### Extensiones Incluidas

El dev container instala automáticamente estas extensiones:

**PHP & Laravel:**
- Intelephense (autocompletado PHP)
- Laravel Extra Intellisense
- Laravel Blade syntax
- PHP Debug (Xdebug)

**Vue.js & Frontend:**
- Volar (Vue 3 support)
- Tailwind CSS IntelliSense
- Prettier (formateo de código)
- ESLint

**Herramientas:**
- GitLens
- Docker extension
- Database Client
- Material Icon Theme

### Configuraciones Automáticas

- **Formateo:** Se ejecuta automáticamente al guardar
- **Debugging:** Xdebug configurado para PHP
- **Terminal:** Bash con aliases útiles
- **Emmet:** Habilitado para archivos Blade y Vue

### Atajos Útiles (dentro del contenedor)

```bash
# Aliases disponibles
serve    # php artisan serve --host=0.0.0.0 --port=8000
tinker   # php artisan tinker
migrate  # php artisan migrate
fresh    # php artisan migrate:fresh --seed
dev      # npm run dev
build    # npm run build
```

---

## 👥 Creación de Usuarios

### 🔐 Estructura de Usuarios

El sistema DentalSync utiliza la tabla `usuarios` con los siguientes campos:
- `usuario` - Identificador único para login (no email)
- `nombre` - Nombre completo del usuario
- `password_hash` - Contraseña encriptada
- `rol` - Tipo de usuario (`dentista` o `recepcionista`)
- `activo` - Estado del usuario (true/false)

### 📝 Crear Usuarios Manualmente

**1. Entrar al contenedor y abrir Tinker:**
```bash
docker exec -it dentalsync-dev bash
php artisan tinker
```

**2. Importar el modelo Usuario:**
```php
use App\Models\Usuario;
```

**3. Crear usuario dentista:**
```php
Usuario::create(['usuario' => 'dentista', 'nombre' => 'Dr. Juan Pérez', 'password_hash' => bcrypt('dentista123'), 'rol' => 'dentista', 'activo' => true]);
```

**4. Crear usuario recepcionista:**
```php
Usuario::create(['usuario' => 'recepcionista', 'nombre' => 'María González', 'password_hash' => bcrypt('recepcion123'), 'rol' => 'recepcionista', 'activo' => true]);
```

**5. Verificar usuarios creados:**
```php
Usuario::all();
```

**6. Salir de Tinker:**
```php
exit
```

### ✅ Usuarios de Prueba por Defecto

Después de ejecutar los comandos anteriores, tendrás estos usuarios disponibles:

| Usuario | Contraseña | Rol | Nombre |
|---------|------------|-----|--------|
| `dentista` | `dentista123` | dentista | Dr. Juan Pérez |
| `recepcionista` | `recepcion123` | recepcionista | María González |

### 🔒 Proceso de Login

1. Ir a http://localhost:8000
2. Usar uno de los usuarios de arriba
3. El sistema redirigirá según el rol del usuario

### ⚠️ Notas Importantes sobre Usuarios

- **NO usar etiquetas `<?php` en Tinker** - ya estás en entorno PHP
- **Campo `usuario`** es obligatorio y único (no email)
- **Campo `password_hash`** debe usarse en lugar de `password`
- **Ejecutar comandos uno por uno** en Tinker, no en bloque

---

## 🗄️ Base de Datos

### SQLite (Por Defecto)
Para desarrollo rápido, se usa SQLite:
- **Archivo:** `database/database.sqlite`
- **Sin configuración adicional necesaria**
- **Ideal para:** Desarrollo local, pruebas rápidas

### MariaDB (Opcional)
Para desarrollo más avanzado o similitud con producción:

1. **Actualizar `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=database
   DB_PORT=3306
   DB_DATABASE=dentalsync
   DB_USERNAME=dentalsync
   DB_PASSWORD=password
   ```

2. **Migrar datos:**
   ```bash
   php artisan migrate:fresh --seed
   ```

### Conexión desde VS Code

Con la extensión Database Client:
1. Abrir Command Palette (`Ctrl+Shift+P`)
2. Buscar "Database: Add Connection"
3. Seleccionar MySQL/MariaDB
4. Configurar:
   - **Host:** `localhost`
   - **Port:** `3307`
   - **Username:** `dentalsync`
   - **Password:** `password`
   - **Database:** `dentalsync`

---

## 🐛 Troubleshooting

### 🚨 Problemas Comunes y Soluciones Validadas

#### 1. ❌ Error "Vite manifest.json not found"

**Síntomas:** Página blanca o error 500 al acceder a localhost:8000

**Solución:**
```bash
# Entrar al contenedor
docker exec -it dentalsync-dev bash

# Compilar assets
npm run build

# Verificar que el archivo existe
ls -la public/build/manifest.json
```

#### 2. ❌ Error "getaddrinfo for database failed"

**Síntomas:** Error de conexión a base de datos en Tinker o migraciones

**Causa:** `.env` configurado incorrectamente

**Solución:**
```bash
# Entrar al contenedor
docker exec -it dentalsync-dev bash

# Verificar configuración de DB
php artisan config:show database.connections.mariadb

# Debe mostrar DB_HOST=database, NO localhost
```

#### 3. ❌ Error "Parse error unexpected '<'" en Tinker

**Causa:** Usar `<?php` en Tinker

**Solución:**
```php
# ❌ INCORRECTO
<?php use App\Models\Usuario;

# ✅ CORRECTO
use App\Models\Usuario;
```

#### 4. ❌ Puerto 8000 ya en uso

**Solución:**
```bash
# Verificar qué proceso usa el puerto
lsof -i :8000

# O cambiar puerto en docker-compose.dev.yml
ports:
  - "8080:8000"
```

#### 5. ❌ Permisos de npm (EACCES)

**Solución:**
```bash
# Dentro del contenedor
npm config set cache /tmp/.npm --global
npm install
```

#### 6. ❌ Contenedores no inician

**Solución:**
```bash
# Reset completo
docker-compose -f Docker/docker-compose.dev.yml down -v
docker system prune -f
docker-compose -f Docker/docker-compose.dev.yml up --build -d
```

#### 7. ❌ MariaDB no conecta

**Diagnóstico:**
```bash
# Verificar que MariaDB esté ejecutándose
docker ps | grep mariadb

# Ver logs de MariaDB
docker logs dentalsync-mariadb

# Probar conexión desde el contenedor
docker exec -it dentalsync-dev mariadb -h database -u dentalsync -ppassword --skip-ssl -e "SELECT 'OK';"
```

### Logs y Debugging

```bash
# Ver logs de todos los servicios
docker-compose -f Docker/docker-compose.dev.yml logs

# Ver logs de un servicio específico
docker-compose -f Docker/docker-compose.dev.yml logs app

# Ver logs en tiempo real
docker-compose -f Docker/docker-compose.dev.yml logs -f

# Entrar al contenedor para debugging
docker exec -it dentalsync-dev bash
```

---

## 📚 Comandos de Referencia

### 🐳 Docker - Comandos Esenciales

```bash
# 🚀 INICIO COMPLETO (desde cero)
docker-compose -f Docker/docker-compose.dev.yml down -v
docker system prune -f
docker-compose -f Docker/docker-compose.dev.yml up --build -d

# 📊 MONITOREO
docker ps                                           # Ver contenedores activos
docker logs dentalsync-dev                          # Logs de la aplicación
docker logs dentalsync-mariadb                      # Logs de la base de datos
docker-compose -f Docker/docker-compose.dev.yml ps # Estado de servicios

# 🔧 MANTENIMIENTO
docker exec -it dentalsync-dev bash                 # Entrar al contenedor
docker-compose -f Docker/docker-compose.dev.yml restart  # Reiniciar servicios
docker-compose -f Docker/docker-compose.dev.yml down     # Detener servicios
```

### 🦷 Laravel - Comandos Validados

```bash
# 🏗️ CONFIGURACIÓN INICIAL (dentro del contenedor)
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000

# 🔍 DIAGNÓSTICO
php artisan config:show database.connections.mariadb
php artisan migrate:status
php artisan route:list
php artisan config:clear && php artisan cache:clear

# 👥 GESTIÓN DE USUARIOS
php artisan tinker
# Dentro de Tinker:
# use App\Models\Usuario;
# Usuario::all();
# Usuario::create([...]);
```

### 🎨 Frontend - Assets y Vite

```bash
# 📦 INSTALACIÓN Y BUILD (dentro del contenedor)
npm config set cache /tmp/.npm --global  # Evitar errores de permisos
npm install
npm run build                             # OBLIGATORIO para producción

# 🔧 DESARROLLO (opcional)
npm run dev                               # Desarrollo con hot reload

# ✅ VERIFICACIÓN
ls -la public/build/manifest.json         # Verificar que el build funcionó
```

### 🗄️ Base de Datos - Comandos Directos

```bash
# 🔌 CONEXIÓN DIRECTA (desde contenedor app)
mariadb -h database -u dentalsync -ppassword --skip-ssl dentalsync

# 📊 QUERIES ÚTILES
mariadb -h database -u dentalsync -ppassword --skip-ssl -e "SHOW DATABASES;"
mariadb -h database -u dentalsync -ppassword --skip-ssl -e "USE dentalsync; SHOW TABLES;"
mariadb -h database -u dentalsync -ppassword --skip-ssl -e "USE dentalsync; SELECT * FROM usuarios;"

# 🔄 RESET DE BASE DE DATOS
php artisan migrate:fresh
```

---

## 🔍 Verificación del Sistema

### ✅ Lista de Verificación Completa

Ejecuta estos comandos para verificar que todo funciona correctamente:

```bash
# 1. Verificar contenedores activos
docker ps | grep dentalsync
# Debe mostrar: dentalsync-dev y dentalsync-mariadb

# 2. Verificar conectividad de base de datos
docker exec -it dentalsync-dev mariadb -h database -u dentalsync -ppassword --skip-ssl -e "SELECT 'DB OK';"
# Debe mostrar: DB OK

# 3. Verificar configuración Laravel
docker exec -it dentalsync-dev php artisan config:show database.connections.mariadb
# DB_HOST debe ser 'database'

# 4. Verificar migraciones
docker exec -it dentalsync-dev php artisan migrate:status
# Todas las migraciones deben mostrar [1] Ran

# 5. Verificar assets compilados
docker exec -it dentalsync-dev ls -la public/build/manifest.json
# Debe existir el archivo

# 6. Verificar aplicación web
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000
# Debe retornar: 200
```

### 🎯 Resultados Esperados

Si todo está configurado correctamente:

- ✅ Contenedores `dentalsync-dev` y `dentalsync-mariadb` ejecutándose
- ✅ Base de datos conectada y migraciones ejecutadas
- ✅ Assets de Vite compilados en `public/build/`
- ✅ Aplicación accesible en http://localhost:8000
- ✅ Interfaz de login visible y funcional
- ✅ Usuarios de prueba creados y operativos

### 🚨 Indicadores de Problemas

- ❌ Error 500: Verificar `npm run build` y configuración `.env`
- ❌ Página blanca: Verificar assets de Vite
- ❌ Error DB: Verificar `DB_HOST=database` en `.env`
- ❌ Puerto ocupado: Cambiar puerto en `docker-compose.dev.yml`

---

## 🔧 Personalización

### Agregar Extensiones de VS Code

Edita `.devcontainer/devcontainer.json`:

```json
"extensions": [
  "existente.extension",
  "nueva.extension"
]
```

### Modificar Configuración de PHP

Edita `Docker/Dockerfile.dev` y agrega:

```dockerfile
RUN echo "nueva_config = valor" >> /usr/local/etc/php/php.ini
```

### Agregar Nuevos Servicios

Edita `Docker/docker-compose.dev.yml`:

```yaml
services:
  nuevo-servicio:
    image: imagen:tag
    ports:
      - "puerto:puerto"
    networks:
      - dentalsync-network
```

---

## 🚀 Tips de Productividad

1. **Usa los aliases:** `serve`, `tinker`, `dev`, `build`
2. **Hot reload:** Vite recarga automáticamente los cambios
3. **Debugging:** Xdebug está configurado para VS Code
4. **Base de datos:** Usa SQLite para desarrollo rápido
5. **Git:** Configuración se hereda del host
6. **Terminal múltiple:** Abre varias terminales en VS Code

---

---

## � Procedimiento de Reset Completo

Si algo sale mal, usa este procedimiento de reset validado:

```bash
# 1. Detener y limpiar todo
docker-compose -f Docker/docker-compose.dev.yml down -v
docker system prune -f

# 2. Construir e iniciar servicios
docker-compose -f Docker/docker-compose.dev.yml up --build -d

# 3. Configurar aplicación
docker exec -it dentalsync-dev bash -c "
cp .env.example .env &&
php artisan key:generate &&
php artisan migrate &&
npm config set cache /tmp/.npm --global &&
npm install &&
npm run build
"

# 4. Crear usuarios de prueba
docker exec -it dentalsync-dev php artisan tinker --execute="
use App\Models\Usuario;
Usuario::create(['usuario' => 'dentista', 'nombre' => 'Dr. Juan Pérez', 'password_hash' => bcrypt('dentista123'), 'rol' => 'dentista', 'activo' => true]);
Usuario::create(['usuario' => 'recepcionista', 'nombre' => 'María González', 'password_hash' => bcrypt('recepcion123'), 'rol' => 'recepcionista', 'activo' => true]);
"

# 5. Verificar
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000
```

---

## 📞 Soporte y Documentación

### 🆘 Si Encuentras Problemas

1. **Ejecuta diagnóstico completo:**
   ```bash
   # Verificar sistema Docker
   docker info
   docker ps -a | grep dentalsync
   
   # Verificar logs
   docker logs dentalsync-dev --tail 50
   docker logs dentalsync-mariadb --tail 50
   
   # Verificar aplicación
   docker exec -it dentalsync-dev php artisan config:show database
   ```

2. **Revisa la sección [Troubleshooting](#-troubleshooting)**

3. **Usa el procedimiento de reset completo**

4. **Documenta el problema con:**
   - Comando que causó el error
   - Mensaje de error completo
   - Output de `docker ps` y `docker logs`
   - Sistema operativo y versión de Docker

### 📋 Información del Sistema

- **Laravel:** 12.26.3
- **PHP:** 8.2.29
- **Node.js:** 20.19.5
- **MariaDB:** 11.2
- **Vue.js:** 3.x
- **Vite:** Configurado para development y production

### 🔗 Enlaces Útiles

- [Docker Documentation](https://docs.docker.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [MariaDB Documentation](https://mariadb.org/documentation/)

---

## 📄 Licencia

Este entorno de desarrollo es parte del proyecto DentalSync.

**Documentación actualizada:** 7 de octubre de 2025  
**Versión:** 2.0 - Procedimientos validados en producción  

---

**¡Feliz desarrollo! 🦷✨**

### 📝 Changelog de esta Documentación

- **v2.0 (Oct 2025):** Documentación completa con procedimientos validados
- **v1.0:** Documentación inicial con VS Code Dev Containers