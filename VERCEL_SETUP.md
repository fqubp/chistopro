# Vercel setup для chistopro

## 1. Импорт проекта

1. Vercel → New Project → Import `fqubp/chistopro`.
2. В форме:
   - Application Preset: **Other**
   - Root Directory: **./**
   - Build Command: *(пусто)*
   - Output Directory: *(пусто)*
   - Install Command: *(пусто)*

## 2. Environment Variables

Добавь:

- `APP_ENV=production`
- `DB_HOST=...`
- `DB_PORT=3306`
- `DB_NAME=...`
- `DB_USER=...`
- `DB_PASSWORD=...`
- `ADMIN_PASSWORD_HASH=...`
- `NOTIFY_EMAIL=...`

## 3. Deploy

Нажми **Deploy**.

## 4. Проверка

- Открывается главная `/`
- Открывается `/services.php`
- Отправка формы работает
- Вход в `/admin/login.php` работает

## 5. Важно

На Vercel локальная папка `uploads/` не подходит для постоянного хранения пользовательских файлов.
Для продакшена подключи внешнее хранилище.
