# Установка

## Загрузите файлы.

Если вы используете [Composer](https://getcomposer.org), вы можете выполнить следующую
команду:

```bash
composer require flightphp/core
```

ИЛИ вы можете [загрузить файлы](https://github.com/flightphp/core/archive/master.zip) 
непосредственно и извлечь их в ваш каталог веб-сайта.

## Настройте ваш веб-сервер.

### Apache
Для Apache отредактируйте свой файл `.htaccess` следующим образом:

```apacheconf
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

> **Примечание**: Если вы хотите использовать flight в подкаталоге, добавьте строку
> `RewriteBase /subdir/` сразу после `RewriteEngine On`.

> **Примечание**: Если вы хотите защитить все файлы сервера, такие как файл db или env.
> Поместите это в свой файл `.htaccess`:

```apacheconf
RewriteEngine On
RewriteRule ^(.*)$ index.php
```

### Nginx

Для Nginx добавьте следующее в ваше объявление сервера:

```nginx
server {
  location / {
    try_files $uri $uri/ /index.php;
  }
}
```

## Создайте ваш файл `index.php`.

```php
<?php

// Если вы используете Composer, требуется автозагрузчик.
require 'vendor/autoload.php';
// если вы не используете Composer, загрузите фреймворк непосредственно
// require 'flight/Flight.php';

// Затем определите маршрут и назначьте функцию для обработки запроса.
Flight::route('/', function () {
  echo 'hello world!';
});

// Наконец, запустите фреймворк.
Flight::start();
```