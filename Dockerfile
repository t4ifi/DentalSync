# ================================
# Dockerfile para DentalSync
# ================================
# Sistema de Gestión Dental completo
# Backend: Laravel 12 + Frontend: Vue.js 3
# Autor: Andrés Núñez - NullDevs
# Versión: 1.0
# ================================

# Usar imagen base oficial de PHP con Apache
FROM php:8.2-apache

# Información del mantenedor
LABEL maintainer="Andrés Núñez - NullDevs"
LABEL description="DentalSync - Sistema de Gestión Dental"
LABEL version="1.0"

# Variables de entorno
ENV DEBIAN_FRONTEND=noninteractive
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV APP_ENV=production
ENV APP_DEBUG=false

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    # Dependencias básicas
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    # Node.js y npm para el frontend
    nodejs \
    npm \
    # Herramientas adicionales
    supervisor \
    cron \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP requeridas por Laravel
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Habilitar mod_rewrite de Apache para Laravel
RUN a2enmod rewrite

# Configurar Apache para DentalSync
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Crear directorio de la aplicación
WORKDIR /var/www/html

# Instalar Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos de dependencias primero (para aprovechar cache de Docker)
COPY composer.json composer.lock package.json package-lock.json* ./

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Instalar dependencias Node.js
RUN npm ci --only=production

# Copiar código fuente de la aplicación
COPY . .

# Crear directorios necesarios para Laravel
RUN mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

# Configurar permisos para Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Crear base de datos SQLite si no existe
RUN touch /var/www/html/database/database.sqlite \
    && chown www-data:www-data /var/www/html/database/database.sqlite \
    && chmod 664 /var/www/html/database/database.sqlite

# Compilar assets del frontend
RUN npm run build

# Copiar configuración personalizada de Apache
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Crear configuración de Apache si no existe el archivo
RUN if [ ! -f /etc/apache2/sites-available/000-default.conf ]; then \
    echo '<VirtualHost *:80>' > /etc/apache2/sites-available/000-default.conf && \
    echo '    ServerAdmin webmaster@dentalsync.local' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    DocumentRoot ${APACHE_DOCUMENT_ROOT}' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    <Directory ${APACHE_DOCUMENT_ROOT}>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '        Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    </Directory>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined' >> /etc/apache2/sites-available/000-default.conf && \
    echo '</VirtualHost>' >> /etc/apache2/sites-available/000-default.conf; \
fi

# Crear script de inicialización
COPY docker/scripts/init.sh /usr/local/bin/init.sh
RUN if [ ! -f /usr/local/bin/init.sh ]; then \
    echo '#!/bin/bash' > /usr/local/bin/init.sh && \
    echo 'set -e' >> /usr/local/bin/init.sh && \
    echo '' >> /usr/local/bin/init.sh && \
    echo '# Configurar aplicación Laravel' >> /usr/local/bin/init.sh && \
    echo 'echo "Inicializando DentalSync..."' >> /usr/local/bin/init.sh && \
    echo '' >> /usr/local/bin/init.sh && \
    echo '# Generar clave de aplicación si no existe' >> /usr/local/bin/init.sh && \
    echo 'if [ ! -f /var/www/html/.env ]; then' >> /usr/local/bin/init.sh && \
    echo '    cp /var/www/html/.env.example /var/www/html/.env' >> /usr/local/bin/init.sh && \
    echo '    php artisan key:generate --no-interaction' >> /usr/local/bin/init.sh && \
    echo 'fi' >> /usr/local/bin/init.sh && \
    echo '' >> /usr/local/bin/init.sh && \
    echo '# Ejecutar migraciones' >> /usr/local/bin/init.sh && \
    echo 'php artisan migrate --force --no-interaction' >> /usr/local/bin/init.sh && \
    echo '' >> /usr/local/bin/init.sh && \
    echo '# Limpiar cache' >> /usr/local/bin/init.sh && \
    echo 'php artisan config:cache' >> /usr/local/bin/init.sh && \
    echo 'php artisan route:cache' >> /usr/local/bin/init.sh && \
    echo 'php artisan view:cache' >> /usr/local/bin/init.sh && \
    echo '' >> /usr/local/bin/init.sh && \
    echo 'echo "DentalSync inicializado correctamente"' >> /usr/local/bin/init.sh && \
    echo '' >> /usr/local/bin/init.sh && \
    echo '# Iniciar Apache' >> /usr/local/bin/init.sh && \
    echo 'exec apache2-foreground' >> /usr/local/bin/init.sh; \
fi

RUN chmod +x /usr/local/bin/init.sh

# Crear archivo .env de ejemplo para producción
RUN if [ ! -f /var/www/html/.env.example ]; then \
    echo 'APP_NAME=DentalSync' > /var/www/html/.env.example && \
    echo 'APP_ENV=production' >> /var/www/html/.env.example && \
    echo 'APP_KEY=' >> /var/www/html/.env.example && \
    echo 'APP_DEBUG=false' >> /var/www/html/.env.example && \
    echo 'APP_TIMEZONE=UTC' >> /var/www/html/.env.example && \
    echo 'APP_URL=http://localhost' >> /var/www/html/.env.example && \
    echo '' >> /var/www/html/.env.example && \
    echo 'APP_LOCALE=es' >> /var/www/html/.env.example && \
    echo 'APP_FALLBACK_LOCALE=en' >> /var/www/html/.env.example && \
    echo 'APP_FAKER_LOCALE=es_ES' >> /var/www/html/.env.example && \
    echo '' >> /var/www/html/.env.example && \
    echo 'APP_MAINTENANCE_DRIVER=file' >> /var/www/html/.env.example && \
    echo 'APP_MAINTENANCE_STORE=database' >> /var/www/html/.env.example && \
    echo '' >> /var/www/html/.env.example && \
    echo 'LOG_CHANNEL=stack' >> /var/www/html/.env.example && \
    echo 'LOG_STACK=single' >> /var/www/html/.env.example && \
    echo 'LOG_DEPRECATIONS_CHANNEL=null' >> /var/www/html/.env.example && \
    echo 'LOG_LEVEL=error' >> /var/www/html/.env.example && \
    echo '' >> /var/www/html/.env.example && \
    echo 'DB_CONNECTION=sqlite' >> /var/www/html/.env.example && \
    echo 'DB_DATABASE=/var/www/html/database/database.sqlite' >> /var/www/html/.env.example && \
    echo '' >> /var/www/html/.env.example && \
    echo 'SESSION_DRIVER=database' >> /var/www/html/.env.example && \
    echo 'SESSION_LIFETIME=120' >> /var/www/html/.env.example && \
    echo 'SESSION_ENCRYPT=false' >> /var/www/html/.env.example && \
    echo 'SESSION_PATH=/' >> /var/www/html/.env.example && \
    echo 'SESSION_DOMAIN=null' >> /var/www/html/.env.example && \
    echo '' >> /var/www/html/.env.example && \
    echo 'CACHE_STORE=database' >> /var/www/html/.env.example && \
    echo 'QUEUE_CONNECTION=database' >> /var/www/html/.env.example && \
    echo '' >> /var/www/html/.env.example && \
    echo 'MAIL_MAILER=log' >> /var/www/html/.env.example && \
    echo 'MAIL_HOST=127.0.0.1' >> /var/www/html/.env.example && \
    echo 'MAIL_PORT=2525' >> /var/www/html/.env.example && \
    echo 'MAIL_USERNAME=null' >> /var/www/html/.env.example && \
    echo 'MAIL_PASSWORD=null' >> /var/www/html/.env.example && \
    echo 'MAIL_ENCRYPTION=null' >> /var/www/html/.env.example && \
    echo 'MAIL_FROM_ADDRESS="hello@example.com"' >> /var/www/html/.env.example && \
    echo 'MAIL_FROM_NAME="${APP_NAME}"' >> /var/www/html/.env.example; \
fi

# Exponer puerto 80 para Apache
EXPOSE 80

# Configurar health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# Comando por defecto
CMD ["/usr/local/bin/init.sh"]

# ================================
# Etiquetas adicionales
# ================================
LABEL org.opencontainers.image.title="DentalSync"
LABEL org.opencontainers.image.description="Sistema completo de gestión dental con Laravel y Vue.js"
LABEL org.opencontainers.image.vendor="NullDevs"
LABEL org.opencontainers.image.authors="Andrés Núñez"
LABEL org.opencontainers.image.source="https://github.com/t4ifi/DentalSync"
LABEL org.opencontainers.image.documentation="https://github.com/t4ifi/DentalSync/blob/main/README.md"
