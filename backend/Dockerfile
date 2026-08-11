FROM php:8.3-fpm-alpine

WORKDIR /var/www/backend

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    unzip \
    zip \
  && docker-php-ext-install \
    intl \
    mbstring \
    pdo_mysql \
    zip

COPY --from=composer:2.7.2 /usr/bin/composer /usr/bin/composer

COPY . .

EXPOSE 9000

CMD ["php-fpm"]
