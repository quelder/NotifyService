# Laravel Telegram Notification Bot
Сервис уведомлений, который получает задачи из внешнего API и отправляет их пользователям через Telegram-бота.
### Стек технологий
* Laravel 12
* MySQL 
* Telegram Bot API
* Laravel Queues
* Guzzle HTTP
* Docker + Docker Compose
* Ngrok
* Swagger
* PHPUnit

## Бысткий старт (Docker)
### Отредактируйте .env:
```
  APP_URL=http://localhost:8000
  DB_HOST=db
  DB_DATABASE=notification_service
  DB_USERNAME=root
  DB_PASSWORD=root
  TELEGRAM_BOT_TOKEN=your_telegram_token
```
### Запустите проект:
```docker-compose up --build```

При первом запуске Laravel выполнит:
*  Ожидание MySQL
*  Миграции и генерацию очередей
*  Публикацию и генерацию Swagger-документации
*  Запуск очереди (queue:work --daemon)
*  Запуск HTTP-сервера

### Настройка Telegram
1. Создайте бота через @BotFather и получите токен.
2. Убедитесь, что Laravel работает по http://localhost:8000.
3. Установите ngrok и запустите:
    ```
    ngrok http http://localhost:8080
    ```
4. Установите Webhook:
    ```
    curl -X POST "https://api.telegram.org/bot<YOUR_TOKEN>/setWebhook" \
    -d "url=https://<your-ngrok-url>/api/webhook"
    ```

### Команды Telegram
*  /start — подписывает пользователя и сохраняет его telegram_id
*  /stop — отписывает пользователя от уведомлений


## Логика рассылки задач
    docker-compose exec app php artisan notify:tasks

Команда:
*  Загружает задачи с https://jsonplaceholder.typicode.com/todos
*  Фильтрует: completed = false, userId <= 5
*  Отправляет их активным подписчикам (subscribed = true) через очередь

### Очереди
Laravel автоматически запускает `queue:work --daemon` при старте контейнера.
Для ручного запуска/отладки:

    ``` 
    docker-compose exec app php artisan queue:work
    ```

### Тестирование

`docker-compose exec app php artisan test --env=testing`

Тесты включают:
*  Подписку через /start
*  Отписку через /stop
*  Обработку неизвестных команд
*  Проверку хранения Telegram ID

## Важные переменные окружения .env
```
 QUEUE_CONNECTION=database
 TELEGRAM_BOT_TOKEN=your_telegram_bot_token
 DEV_CHAT_ID=your_dev_chat_id
```

## Swagger-документация
Документация API доступна по адресу:
http://localhost:8000/api/documentation

### Пример Webhook-запроса
```
{
	"message": {
	"chat": { "id": "123456" },
	"text": "/start",
	"from": { "first_name": "John" }
	}
}
```
### Ответ в Telegram:
```
📋 Новые задачи:
- delectus aut autem
- quis ut nam facilis et officia qui
- fugiat veniam minus
  ...
```
