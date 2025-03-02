# Установка

### 1. Загрузите файлы.

Если вы используете [Composer](https://getcomposer.org), вы можете выполнить следующую
команду:

```bash
composer require flightphp/core
```

ИЛИ вы можете [скачать](https://github.com/flightphp/core/archive/master.zip)
их напрямую и извлечь их в ваш веб-директорию.

### 2. Настройте ваш веб-сервер.

Для *Apache*, отредактируйте ваш файл `.htaccess` следующим образом:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Примечание**: Если вам нужно использовать flight в подкаталоге, добавьте строку
> `RewriteBase /subdir/` сразу после `RewriteEngine On`.
> **Примечание**: Если вы хотите защитить все файлы сервера, такие как базу данных или файл окружения.
> Поместите это в ваш файл `.htaccess`:

```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

Для *Nginx*, добавьте следующее в ваше объявление сервера:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

### 3. Создайте ваш файл `index.php`.

Сначала подключите фреймворк.

```php
require 'flight/Flight.php';
```

Если вы используете Composer, запустите автозагрузчик вместо этого.

```php
require 'vendor/autoload.php';
```

Затем определите маршрут и назначьте функцию для обработки запроса.

```php
Flight::route('/', function () {
  echo 'hello world!';
});
```

Наконец, запустите фреймворк.

```php
Flight::start();
```