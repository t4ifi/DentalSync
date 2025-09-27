# 🦷 DentalSync - Guía Completa del Dev Container

Esta guía te ayudará a configurar y usar el entorno de desarrollo completo de DentalSync usando Docker y VS Code Dev Containers.

## 📋 Tabla de Contenidos

- [🚀 Inicio Rápido](#-inicio-rápido)
- [🏗️ Arquitectura del Entorno](#️-arquitectura-del-entorno)
- [📦 Servicios Incluidos](#-servicios-incluidos)
- [⚙️ Configuración Detallada](#️-configuración-detallada)
- [🔧 Scripts Disponibles](#-scripts-disponibles)
- [💡 Uso con VS Code](#-uso-con-vs-code)
- [🗄️ Base de Datos](#️-base-de-datos)
- [🐛 Troubleshooting](#-troubleshooting)
- [📚 Comandos Útiles](#-comandos-útiles)

---

## 🚀 Inicio Rápido

### Prerrequisitos

- [Docker](https://www.docker.com/get-started) (versión 20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) (versión 2.0+)
- [VS Code](https://code.visualstudio.com/) con extensión [Dev Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)

### Opción 1: Con VS Code (Recomendado)

1. **Abrir el proyecto en VS Code:**
   ```bash
   code /path/to/DentalSync
   ```

2. **Abrir en Dev Container:**
   - Presiona `Ctrl+Shift+P` (o `Cmd+Shift+P` en Mac)
   - Busca "Dev Containers: Reopen in Container"
   - Selecciona la opción y espera a que se construya el contenedor

3. **¡Listo!** El entorno se configurará automáticamente.

### Opción 2: Con Scripts (Manual)

1. **Iniciar el entorno:**
   ```bash
   ./Docker/scripts/start-dev.sh
   ```

2. **Entrar al contenedor:**
   ```bash
   docker exec -it dentalsync-dev bash
   ```

3. **Configurar el proyecto (dentro del contenedor):**
   ```bash
   /home/developer/scripts/setup-project.sh
   ```

---

## 🏗️ Arquitectura del Entorno

```
┌─────────────────────────────────────────────────────────────┐
│                    DentalSync Dev Environment               │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Laravel   │  │    Vue.js   │  │   MariaDB   │         │
│  │  (Port 8000)│  │ (Port 5173) │  │ (Port 3306) │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐                          │
│  │    Redis    │  │   Mailpit   │                          │
│  │ (Port 6379) │  │ (Port 8025) │                          │
│  └─────────────┘  └─────────────┘                          │
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
- **Puerto:** 3306
- **Usuario:** `dentalsync` | **Contraseña:** `password`
- **Base de datos:** `dentalsync`

### 🚀 Cache (dentalsync-redis)
- **Imagen:** Redis 7 Alpine
- **Puerto:** 6379
- **Persistencia:** Habilitada con AOF

### 📧 Correo (dentalsync-mailpit)
- **Imagen:** Mailpit latest
- **Puerto SMTP:** 1025
- **Interfaz Web:** 8025
- **Uso:** Captura todos los emails enviados por la aplicación

---

## ⚙️ Configuración Detallada

### Estructura de Archivos

```
Docker/
├── Dockerfile.dev              # Imagen de desarrollo
├── docker-compose.dev.yml      # Orquestación de servicios
├── mariadb/
│   └── mariadb.cnf            # Configuración de MariaDB
└── scripts/
    ├── start-dev.sh           # Inicio rápido
    ├── stop-dev.sh            # Detener servicios
    └── dev-tools.sh           # Herramientas de desarrollo

.devcontainer/
└── devcontainer.json          # Configuración de VS Code
```

### Variables de Entorno

El contenedor utiliza las siguientes variables de entorno:

```bash
# Aplicación
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite  # Por defecto para desarrollo rápido
DB_DATABASE=/workspace/database/database.sqlite

# Para usar MariaDB (opcional)
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=dentalsync
DB_USERNAME=dentalsync
DB_PASSWORD=password

# Vite
VITE_DEV_SERVER_URL=http://localhost:5173
```

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
   - **Port:** `3306`
   - **Username:** `dentalsync`
   - **Password:** `password`
   - **Database:** `dentalsync`

---

## 🐛 Troubleshooting

### Problemas Comunes

#### 1. Docker no inicia
```bash
# Verificar que Docker esté ejecutándose
docker info

# En Linux, añadir usuario al grupo docker
sudo usermod -aG docker $USER
# Luego logout/login
```

#### 2. Puerto 8000 ya en uso
```bash
# Encontrar el proceso usando el puerto
lsof -i :8000

# Cambiar el puerto en docker-compose.dev.yml
ports:
  - "8080:8000"  # Cambiar 8000 por 8080
```

#### 3. Permisos de archivos (Linux)
```bash
# Dentro del contenedor
sudo chown -R developer:developer /workspace
```

#### 4. Dependencias no se instalan
```bash
# Limpiar caché y reinstalar
docker-compose -f Docker/docker-compose.dev.yml down -v
docker-compose -f Docker/docker-compose.dev.yml up --build
```

#### 5. MariaDB no arranca
```bash
# Ver logs del contenedor
docker logs dentalsync-mariadb

# Limpiar volumen de MariaDB
docker volume rm dentalsync_mariadb_data
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

## 📚 Comandos Útiles

### Docker

```bash
# Construir contenedores
docker-compose -f Docker/docker-compose.dev.yml build

# Iniciar servicios
docker-compose -f Docker/docker-compose.dev.yml up -d

# Detener servicios
docker-compose -f Docker/docker-compose.dev.yml down

# Ver estado de contenedores
docker-compose -f Docker/docker-compose.dev.yml ps

# Limpiar todo (incluyendo volúmenes)
docker-compose -f Docker/docker-compose.dev.yml down -v
docker system prune -f
```

### Laravel (dentro del contenedor)

```bash
# Configuración inicial
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve --host=0.0.0.0

# Desarrollo
php artisan migrate:fresh --seed
php artisan tinker
php artisan route:list
php artisan config:clear
```

### Frontend (dentro del contenedor)

```bash
# Instalación
npm install

# Desarrollo
npm run dev

# Producción
npm run build

# Actualizar dependencias
npm update
```

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

## 📞 Soporte

Si encuentras problemas:

1. **Revisa los logs:** `docker-compose logs`
2. **Verifica los servicios:** `docker-compose ps`
3. **Reinicia limpio:** `./Docker/scripts/stop-dev.sh` y `./Docker/scripts/start-dev.sh`
4. **Documenta el problema:** Incluye logs y pasos para reproducir

---

## 📄 Licencia

Este entorno de desarrollo es parte del proyecto DentalSync.

---

**¡Feliz desarrollo! 🦷✨**