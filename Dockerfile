FROM php:8.1.23-fpm

RUN  apt-get update && apt-get install -y \
     curl \
     libpng-dev \
     libonig-dev \
     libxml2-dev \
     zip \
     unzip \
     nano \
     git-all \
     libzip-dev \
     supervisor \ 
     python3 \
     python3-venv \
     python3-pip

ENV  VIRTUAL_ENV=/opt/venv

RUN  python3 -m venv $VIRTUAL_ENV

ENV  PATH="$VIRTUAL_ENV/bin:$PATH"

RUN  pip install pypsexec

RUN  apt-get clean && rm -rf /var/lib/apt/lists/*

RUN  docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

ENV  COMPOSER_ALLOW_SUPERUSER=1

RUN  curl -sS https://getcomposer.org/installer​ | php -- \
     --install-dir=/usr/local/bin --filename=composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./composer.* /var/www/html

WORKDIR /var/www/html

COPY . /var/www/html

RUN  chown -R www-data:www-data *

RUN  composer install --ignore-platform-reqs --no-interaction

RUN  php artisan optimize:clear && \
     php artisan cache:clear && \
     php artisan config:clear && \
     php artisan config:cache