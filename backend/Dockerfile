FROM php:8.3-cli

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install apcu \
    && docker-php-ext-enable apcu

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias
RUN composer install --no-interaction --optimize-autoloader

# Exponer puerto
EXPOSE 8000

# Comando para iniciar el servidor
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
