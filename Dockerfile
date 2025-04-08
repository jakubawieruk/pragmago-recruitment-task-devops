# ---- Base PHP Image ----
    FROM php:8.1-cli-alpine AS base
    WORKDIR /app
    
    # Install system dependencies for PHP extensions
    RUN apk update && apk add --no-cache \
        linux-headers \
        autoconf \
        openssl-dev \
        g++ \
        make \
        pcre-dev \
        $PHPIZE_DEPS \
        postgresql-dev \
        postgresql-client \
        libzip-dev \
        zlib-dev \
        icu-dev \
        redis \
        && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql \
        zip \
        intl \
        opcache \
        sockets
    RUN pecl install redis-5.3.7 \
        && docker-php-ext-enable redis \
        && apk del $PHPIZE_DEPS \
        && apk del --purge autoconf g++ make
    
    # Install Composer
    COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
    
    # ---- Build Stage ----
    FROM base AS build_stage
    WORKDIR /app

    # Copy application code
    # TODO: Consider copying only necessary files for build context to reduce image size
    COPY . . 

    # Required to install RoadRunner bundle
    RUN composer config extra.symfony.allow-contrib true

    # Install missing RoadRunner bundle
    # !IMPORTANT: This is a temporary workaround until the bundle is available in the main repository
    RUN composer require baldinof/roadrunner-bundle:^2.3

    # update composer.json to use RoadRunner bundle
    RUN composer update

    # Download RoadRunner binary
    # Install dependencies (including dev for RoadRunner binary download)
    # RUN composer install --prefer-dist --no-progress --no-interaction --optimize-autoloader

    RUN composer install --no-dev --no-scripts --prefer-dist --no-progress --no-interaction --optimize-autoloader

    RUN ./vendor/bin/rr get-binary --location /usr/local/bin
    
    # Download RoadRunner binary
    # RUN vendor/bin/rr get-binary
    
    RUN composer dump-autoload --optimize --no-dev --classmap-authoritative && \
        composer check-platform-reqs
    
    # # ---- Production Stage ----
    # FROM base AS production_stage
    # WORKDIR /app
    
    # # Copy necessary artifacts from build stage
    # COPY --from=build_stage /app/vendor ./vendor
    # COPY --from=build_stage /app/public ./public
    # COPY --from=build_stage /app/src ./src
    # COPY --from=build_stage /app/config ./config
    # COPY --from=build_stage /app/bin ./bin
    # COPY --from=build_stage /usr/local/bin/rr /usr/local/bin/rr

    # # If needed by your app
    # # Example, adjust if needed. Better to use runtime env vars
    # COPY --from=build_stage /app/.env ./env 
    # # Copy RoadRunner binary
    # COPY --from=build_stage /app/vendor/bin/rr /usr/local/bin/rr 
    # # Needed for platform check etc.
    # COPY --from=build_stage /app/composer.json /app/composer.lock ./ 
    
    # Expose RoadRunner port
    EXPOSE 8080
    
    # Set environment variables (can be overridden by docker-compose/runtime)
    ENV APP_ENV=prod \
        APP_DEBUG=0
    
    # Entrypoint to start RoadRunner
    CMD ["rr", "serve"]