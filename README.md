# chistopro

Сайт клининговой компании на PHP + MySQL.

## Локальный запуск

1. Скопируй `.env.example` в `.env` и заполни значения.
2. Создай БД и импортируй `database/schema.sql`.
3. Запусти PHP-сервер (или открой проект в Apache/Nginx):
   ```bash
   php -S localhost:8080
   ```

## Переменные окружения

- `APP_ENV` — `production` или `local`.
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` — доступ к MySQL.
- `ADMIN_PASSWORD_HASH` — хэш пароля админки.
- `NOTIFY_EMAIL` — email для уведомлений о новых заявках.

### Генерация хэша пароля админки

```bash
php -r "echo password_hash('CHANGE_THIS_ADMIN_PASSWORD', PASSWORD_DEFAULT) . PHP_EOL;"
```

## Деплой (shared hosting)

1. Загрузи файлы проекта на хостинг.
2. Создай `.env` на сервере по образцу `.env.example`.
3. Создай БД и импортируй `database/schema.sql`.
4. Убедись, что папка `uploads/` доступна на запись.
5. Включи SSL (Let's Encrypt) и проверь формы/админку.

## Безопасность

- Не коммить `.env`.
- Не используй простой пароль админки.
- В `uploads/.htaccess` запрещено выполнение PHP-файлов.


## Хостинг (Timeweb)

Подробная пошаговая инструкция: `DEPLOY_TIMEWEB.md`.
