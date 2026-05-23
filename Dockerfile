FROM dunglas/frankenphp:latest-php8.3-bookworm

RUN install-php-extensions pdo_pgsql intl zip opcache gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y vim && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

ENV APP_ENV=prod
ENV APP_SECRET=changeme
ENV DATABASE_URL="postgresql://postgres:postgres@database:5432/crm_cabinet?serverVersion=18&charset=utf8"

RUN php bin/console cache:clear --env=prod
RUN chmod -R 777 var
