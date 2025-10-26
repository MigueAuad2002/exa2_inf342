#IMAGEN OFICIAL DE PHP COMPOSER
FROM php:8.2-cli

#INSTALAR DEPENDENCIAS
RUN apt-get update && apt-get install -y git unzip libpq-dev && docker-php-ext-install pdo pdo_pgsql

# Define el directorio de trabajo
WORKDIR /app

#COPIA TODO EL PROYECTO DENTRO DEL CONTENEDOR
COPY . /app

#INSTALA COMPOSER SI NO VIENE INCLUIDO EN RENDER
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

#INSTALAR DEPENDENCIAS DEL PROYECTO
RUN composer install --no-dev --optimize-autoloader

#EXPONE EL PUERTO 8000 PARA QUE RENDER LO USE
EXPOSE 8000

#COMANDO QUE USARA RENDER PARA LEVANTAR LA APP
CMD ["php", "run.php"]