FROM php:8.4-cli-bookworm AS base

ARG WWWGROUP=1000
ARG WWWUSER=1000
ARG NODE_VERSION=24

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/tmp/composer
ENV APP_HOST=0.0.0.0

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        autoconf \
        build-essential \
        ca-certificates \
        curl \
        git \
        gosu \
        gnupg \
        libcap2-bin \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libsqlite3-dev \
        libssl-dev \
        libzip-dev \
        unzip \
        zip \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_${NODE_VERSION}.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && pecl install redis swoole \
    && docker-php-ext-enable redis swoole \
    && curl -fsSL https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin --filename=composer \
    && npm install -g npm \
    && setcap "cap_net_bind_service=+ep" /usr/local/bin/php \
    && groupadd -g "${WWWGROUP}" app \
    && useradd -u "${WWWUSER}" -g app -ms /bin/bash app \
    && mkdir -p /tmp/composer \
    && apt-get purge -y --auto-remove \
        autoconf \
        build-essential \
        gnupg \
    && rm -rf /var/lib/apt/lists/* /tmp/pear ~/.pearrc

FROM base AS vendor

COPY composer.json composer.lock ./
COPY .env.example ./.env
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan ./

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

FROM base AS frontend

COPY package.json package-lock.json ./
RUN npm ci

COPY public ./public
COPY resources ./resources
COPY vite.config.js ./

RUN npm run build

FROM base AS runtime

COPY --chown=app:app . .
COPY --from=vendor --chown=app:app /var/www/html/vendor ./vendor
COPY --from=frontend --chown=app:app /var/www/html/public/build ./public/build
COPY docker/prod/php.ini /usr/local/etc/php/conf.d/99-prod.ini
COPY docker/prod/start-container /usr/local/bin/start-container

RUN chmod +x /usr/local/bin/start-container \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache \
    && chown -R app:app /var/www/html /tmp/composer \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["start-container"]
