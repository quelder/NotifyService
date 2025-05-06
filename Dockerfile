FROM php:8.2-cli

# Установим зависимости
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev \
    libzip-dev libcurl4-openssl-dev \
    libmariadb-dev \
    mariadb-client \
    && docker-php-ext-install pdo_mysql

# Установим Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install

# Добавляем файл entrypoint.sh
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

# Используем скрипт для старта
CMD ["entrypoint.sh"]