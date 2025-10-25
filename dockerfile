# Usa una imagen oficial de PHP con Composer
FROM php:8.2-cli

# Instala dependencias necesarias para PostgreSQL
RUN apt-get update && apt-get install -y git unzip libpq-dev && docker-php-ext-install pdo pdo_pgsql

# Define el directorio de trabajo
WORKDIR /app

# Copia todo tu proyecto dentro del contenedor
COPY . /app

# Instala Composer (si no viene incluido)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Instala tus dependencias del proyecto
RUN composer install --no-dev --optimize-autoloader

# Expone el puerto 8000 para que Render lo use
EXPOSE 8000

# Comando que Render ejecutará para iniciar tu app
CMD ["php", "run.php"]
