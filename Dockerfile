FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install -j$(nproc) pdo_pgsql mbstring zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci \
    && npm run build \
    && chmod +x docker/entrypoint.sh

ENV APP_ENV=production
ENV LOG_CHANNEL=stderr

EXPOSE 10000

ENTRYPOINT ["docker/entrypoint.sh"]
