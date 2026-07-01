# FoodMarket - Dockerfile для PHP (Symfony) приложения
# Используем официальный PHP образ с необходимыми расширениями

FROM php:8.1-fpm-alpine

# Устанавливаем системные зависимости
RUN apk add --no-cache \
    postgresql-dev \
    git \
    zip \
    unzip \
    curl \
    libzip-dev \
    && docker-php-ext-install pdo_pgsql opcache zip

# Устанавливаем Composer (менеджер зависимостей PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройки PHP
RUN echo "date.timezone = UTC" > /usr/local/etc/php/conf.d/foodmarket.ini && \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/foodmarket.ini && \
    echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/foodmarket.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/foodmarket.ini

# Рабочая директория
WORKDIR /app

# Копируем файлы проекта
COPY . .

# Устанавливаем зависимости
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Права на кэш, логи и будущие JWT ключи
# Создаём директории от www-data, чтобы php-fpm мог писать
RUN mkdir -p config/jwt var/cache var/log var/cache/dev/doctrine/orm/Proxies && \
    chown -R www-data:www-data config/jwt var/cache var/log && \
    chmod -R 775 config/jwt var/cache var/log

EXPOSE 9000

CMD ["php-fpm"]
