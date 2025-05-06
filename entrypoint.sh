#!/bin/bash

# Проверка значения DB_HOST
echo "Using DB_HOST: $DB_HOST"

# Ожидаем, пока MySQL будет готов
echo "Waiting for MySQL to be ready..."
until mysqladmin ping -h"$DB_HOST" --silent; do
  echo "Waiting for MySQL to be ready..."
  sleep 5  # Увеличиваем интервал
done


# Выполняем команды artisan
echo "Running migrations..."
php artisan migrate
php artisan queue:table
php artisan migrate

# Генерация документации Swagger
php artisan vendor:publish --tag=l5-swagger
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

echo "Starting the queue worker..."
php artisan queue:work --daemon &  # Работает в фоновом режиме

# Запуск приложения
echo "Starting the application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan serve --host=0.0.0.0 --port=8000