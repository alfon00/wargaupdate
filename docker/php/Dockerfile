FROM node:22-bookworm-slim AS node

FROM php:8.4-fpm-bookworm

COPY --from=node /usr/local/ /usr/local/

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    imagemagick \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libcairo2-dev \
    libpango1.0-dev \
    libjpeg-dev \
    libgif-dev \
    librsvg2-dev \
    zip \
    unzip \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && sed -i 's/rights="none" pattern="PDF"/rights="read|write" pattern="PDF"/' /etc/ImageMagick-6/policy.xml

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci \
    && cd node_modules/canvas && npx --yes node-gyp rebuild
