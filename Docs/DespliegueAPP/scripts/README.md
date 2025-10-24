# Scripts de Instalación y Mantenimiento - DentalSync

Este directorio contiene scripts automatizados para facilitar la instalación, configuración y mantenimiento de DentalSync.

## 📋 Scripts Disponibles

### 🚀 install.sh
**Instalación automatizada de dependencias**

Instala todo lo necesario para ejecutar DentalSync:
- PHP 8.2 + extensiones
- Composer
- Node.js 20.x LTS + NPM
- MariaDB
- Configuración de base de datos

**Uso:**
```bash
sudo chmod +x install.sh
sudo ./install.sh
```

---

### 🗄️ setup-database.sh
**Configuración de base de datos**

Crea la base de datos y el usuario de MySQL/MariaDB.

**Uso:**
```bash
sudo chmod +x setup-database.sh
sudo ./setup-database.sh
```

---

### 💾 backup.sh
**Backup automático**

Crea copias de seguridad de:
- Base de datos (comprimida)
- Directorio storage/app (placas dentales)
- Archivo .env

**Uso:**
```bash
chmod +x backup.sh
./backup.sh
```

**Backups guardados en:** `/var/backups/dentalsync/`

**Retención:** 30 días (limpieza automática)

---

### 🔄 deploy.sh
**Actualización de la aplicación**

Actualiza la aplicación de forma segura:
1. Crea backup automático
2. Activa modo mantenimiento
3. Actualiza dependencias (Composer + NPM)
4. Ejecuta migraciones
5. Limpia caché
6. Desactiva modo mantenimiento

**Uso:**
```bash
chmod +x deploy.sh
./deploy.sh
```

---

### ♻️ restore.sh
**Restauración desde backup**

Restaura la aplicación desde un backup previo:
- Selección interactiva de backup
- Restaura base de datos
- Restaura archivos storage
- Opción de restaurar .env

**Uso:**
```bash
sudo chmod +x restore.sh
sudo ./restore.sh
```

---

### 🏥 health-check.sh
**Verificación del sistema**

Verifica el estado de:
- Servicios del sistema (MariaDB)
- Recursos (CPU, memoria, disco)
- Conexión a base de datos
- Archivos de la aplicación
- Logs de errores
- Respuesta HTTP de la aplicación

**Uso:**
```bash
chmod +x health-check.sh
./health-check.sh
```

---

## 🎯 Flujo de Uso Recomendado

### Instalación inicial:
```bash
# 1. Instalar dependencias
sudo ./install.sh

# 2. Clonar el proyecto
cd ~
git clone https://github.com/t4ifi/DentalSync.git
cd DentalSync

# 3. Instalar dependencias del proyecto
composer install
npm install

# 4. Configurar
cp .env.example .env
php artisan key:generate
nano .env  # Editar credenciales de BD

# 5. Migrar BD
php artisan migrate

# 6. Iniciar servidores
php artisan serve    # Terminal 1
npm run dev          # Terminal 2
```

### Mantenimiento regular:
```bash
# Backup diario/semanal
./backup.sh

# Verificar salud del sistema
./health-check.sh

# Actualizar aplicación
./deploy.sh
```

### Recuperación ante desastres:
```bash
# Restaurar desde backup
sudo ./restore.sh
```

---

## ⚙️ Configuración de Backups Automáticos

Para programar backups automáticos con cron:

```bash
# Editar crontab
crontab -e

# Agregar línea para backup diario a las 2:00 AM
0 2 * * * /ruta/a/DentalSync/Docs/DespliegueAPP/scripts/backup.sh >> /var/log/dentalsync-backup.log 2>&1
```

---

## 📝 Notas Importantes

- **Permisos:** Los scripts de instalación y configuración de BD requieren `sudo`
- **Backups:** Se guardan en `/var/backups/dentalsync/` por 30 días
- **Logs:** El script health-check guarda logs en `/var/log/dentalsync-health.log`
- **Desarrollo:** Los scripts están optimizados para desarrollo local con `php artisan serve`

---

## 🔧 Troubleshooting

**Error: "Permission denied"**
```bash
chmod +x *.sh
```

**Error en backup: "directory not found"**
```bash
sudo mkdir -p /var/backups/dentalsync
```

**No se puede conectar a MySQL**
```bash
sudo systemctl start mariadb
sudo ./setup-database.sh
```

---

## 📚 Documentación Adicional

Ver el manual técnico completo:
```
/Docs/DespliegueAPP/MANUAL_TECNICO_DESPLIEGUE.md
```
