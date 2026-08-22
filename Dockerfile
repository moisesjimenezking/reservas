FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libmemcached-dev \
    zlib1g-dev \
    zip \
    unzip \
    pkg-config \
    && docker-php-ext-install \
        intl \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 8000

CMD if [ ! -d "/var/www/html/vendor" ]; then composer install --no-interaction; fi && \
    php artisan serve --host=0.0.0.0 --port=8000
